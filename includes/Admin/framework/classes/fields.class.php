<?php
/**
 * Framework Fields Abstract Class
 *
 * Abstract base class for all field types providing common functionality.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Framework
 * @since      1.0.0
 */

namespace PearlWeather\Framework;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Abstract Class BaseField
 *
 * Base class for all framework fields.
 *
 * @since 1.0.0
 */
abstract class BaseField extends BaseFramework {

    /**
     * Field configuration.
     *
     * @var array
     */
    protected $field = array();

    /**
     * Field value.
     *
     * @var mixed
     */
    protected $value = '';

    /**
     * Unique identifier.
     *
     * @var string
     */
    protected $unique = '';

    /**
     * Context where field is used.
     *
     * @var string
     */
    protected $where = '';

    /**
     * Parent field (for nested fields).
     *
     * @var mixed
     */
    protected $parent = '';

    /**
     * Constructor.
     *
     * @param array  $field  Field configuration.
     * @param mixed  $value  Field value.
     * @param string $unique Unique identifier.
     * @param string $where  Context.
     * @param mixed  $parent Parent field.
     */
    public function __construct( $field = array(), $value = '', $unique = '', $where = '', $parent = '' ) {
        $this->field = $field;
        $this->value = $value;
        $this->unique = $unique;
        $this->where = $where;
        $this->parent = $parent;
    }

    /**
     * Get the field name attribute.
     *
     * @since 1.0.0
     * @param string $nested_name Nested field name.
     * @return string
     */
    protected function field_name( $nested_name = '' ) {
        $field_id = isset( $this->field['id'] ) ? $this->field['id'] : '';
        $unique_id = ! empty( $this->unique ) ? $this->unique . '[' . $field_id . ']' : $field_id;
        $field_name = isset( $this->field['name'] ) ? $this->field['name'] : $unique_id;

        return $field_name . $nested_name;
    }

    /**
     * Get field attributes as HTML string.
     *
     * @since 1.0.0
     * @param array $custom_atts Custom attributes.
     * @return string
     */
    protected function field_attributes( $custom_atts = array() ) {
        $field_id = isset( $this->field['id'] ) ? $this->field['id'] : '';
        $attributes = isset( $this->field['attributes'] ) ? $this->field['attributes'] : array();

        // Add data-depend-id for conditional logic.
        if ( ! empty( $field_id ) && empty( $attributes['data-depend-id'] ) ) {
            $attributes['data-depend-id'] = $field_id;
        }

        // Add placeholder.
        if ( ! empty( $this->field['placeholder'] ) ) {
            $attributes['placeholder'] = $this->field['placeholder'];
        }

        $attributes = wp_parse_args( $attributes, $custom_atts );
        $atts = '';

        foreach ( $attributes as $key => $value ) {
            if ( 'only-key' === $value ) {
                $atts .= ' ' . esc_attr( $key );
            } else {
                $atts .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
            }
        }

        return $atts;
    }

    /**
     * Render content before the field.
     *
     * @since 1.0.0
     * @return string
     */
    protected function field_before() {
        if ( empty( $this->field['before'] ) ) {
            return '';
        }
        return '<div class="pw-field-before">' . wp_kses_post( $this->field['before'] ) . '</div>';
    }

    /**
     * Render content after the field.
     *
     * @since 1.0.0
     * @return string
     */
    protected function field_after() {
        $output = '';

        if ( ! empty( $this->field['after'] ) ) {
            $output .= '<div class="pw-field-after">' . wp_kses_post( $this->field['after'] ) . '</div>';
        }

        if ( ! empty( $this->field['desc'] ) ) {
            $output .= '<div class="pw-field-desc">' . wp_kses_post( $this->field['desc'] ) . '</div>';
        }

        if ( ! empty( $this->field['help'] ) ) {
            $output .= '<div class="pw-field-help"><span class="pw-help-text">' . wp_kses_post( $this->field['help'] ) . '</span></div>';
        }

        if ( ! empty( $this->field['_error'] ) ) {
            $output .= '<div class="pw-field-error">' . wp_kses_post( $this->field['_error'] ) . '</div>';
        }

        return $output;
    }

    /**
     * Get field data from WordPress entities.
     *
     * @since 1.0.0
     * @param string $type       Data type (post, page, category, user, etc.).
     * @param string $search     Search term for AJAX.
     * @param array  $query_args Additional query arguments.
     * @return array
     */
    public static function get_field_data( $type = '', $search = '', $query_args = array() ) {
        $options = array();

        switch ( $type ) {
            case 'post':
            case 'posts':
            case 'page':
            case 'pages':
                $post_type = ( 'page' === $type || 'pages' === $type ) ? 'page' : 'post';
                $args = array(
                    'post_type'      => $post_type,
                    'post_status'    => 'publish',
                    'posts_per_page' => 25,
                );

                if ( ! empty( $search ) ) {
                    $args['s'] = $search;
                }

                $args = wp_parse_args( $query_args, $args );
                $query = new \WP_Query( $args );

                foreach ( $query->posts as $item ) {
                    $options[ $item->ID ] = $item->post_title;
                }
                break;

            case 'category':
            case 'categories':
            case 'tag':
            case 'tags':
                $taxonomy = ( 'tag' === $type || 'tags' === $type ) ? 'post_tag' : 'category';
                $args = array(
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => false,
                    'number'     => 25,
                );

                if ( ! empty( $search ) ) {
                    $args['search'] = $search;
                }

                $args = wp_parse_args( $query_args, $args );
                $terms = get_terms( $args );

                foreach ( $terms as $term ) {
                    $options[ $term->term_id ] = $term->name;
                }
                break;

            case 'user':
            case 'users':
                $args = array(
                    'number' => 25,
                    'fields' => array( 'ID', 'display_name' ),
                );

                if ( ! empty( $search ) ) {
                    $args['search'] = '*' . $search . '*';
                }

                $args = wp_parse_args( $query_args, $args );
                $users = get_users( $args );

                foreach ( $users as $user ) {
                    $options[ $user->ID ] = $user->display_name;
                }
                break;

            case 'sidebar':
            case 'sidebars':
                global $wp_registered_sidebars;
                foreach ( $wp_registered_sidebars as $sidebar ) {
                    $options[ $sidebar['id'] ] = $sidebar['name'];
                }
                break;

            case 'role':
            case 'roles':
                global $wp_roles;
                foreach ( $wp_roles->roles as $role_key => $role_value ) {
                    $options[ $role_key ] = $role_value['name'];
                }
                break;

            case 'post_type':
            case 'post_types':
                $post_types = get_post_types( array( 'public' => true ), 'objects' );
                foreach ( $post_types as $post_type_obj ) {
                    $options[ $post_type_obj->name ] = $post_type_obj->labels->name;
                }
                break;

            default:
                if ( is_callable( $type ) ) {
                    $options = call_user_func( $type, $search, $query_args );
                }
                break;
        }

        // Format for AJAX search.
        if ( ! empty( $search ) && ! empty( $options ) ) {
            $formatted = array();
            foreach ( $options as $key => $label ) {
                $formatted[] = array(
                    'value' => $key,
                    'text'  => $label,
                );
            }
            $options = $formatted;
        }

        return $options;
    }

    /**
     * Get titles for selected values in AJAX mode.
     *
     * @since 1.0.0
     * @param string $type   Data type.
     * @param array  $values Selected values.
     * @return array
     */
    public static function get_selected_titles( $type, $values ) {
        $options = array();

        if ( empty( $values ) || ! is_array( $values ) ) {
            return $options;
        }

        foreach ( $values as $value ) {
            switch ( $type ) {
                case 'post':
                case 'posts':
                case 'page':
                case 'pages':
                    $title = get_the_title( $value );
                    if ( ! empty( $title ) ) {
                        $options[ $value ] = $title;
                    }
                    break;

                case 'category':
                case 'categories':
                case 'tag':
                case 'tags':
                    $term = get_term( $value );
                    if ( $term && ! is_wp_error( $term ) ) {
                        $options[ $value ] = $term->name;
                    }
                    break;

                case 'user':
                case 'users':
                    $user = get_user_by( 'id', $value );
                    if ( $user ) {
                        $options[ $value ] = $user->display_name;
                    }
                    break;

                default:
                    $options[ $value ] = ucfirst( (string) $value );
                    break;
            }
        }

        return $options;
    }

    /**
     * Render the field (must be implemented by child classes).
     *
     * @since 1.0.0
     */
    abstract public function render();
}