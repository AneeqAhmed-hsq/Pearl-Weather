<?php
/**
 * Framework Metabox Options Class
 *
 * Handles WordPress metabox registration and field processing.
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
 * Class MetaboxOptions
 *
 * Manages metabox options in the WordPress admin.
 *
 * @since 1.0.0
 */
class MetaboxOptions extends BaseFramework {

    /**
     * Unique identifier.
     *
     * @var string
     */
    private $unique = '';

    /**
     * Abstract type.
     *
     * @var string
     */
    protected $abstract = 'metabox';

    /**
     * Pre-processed fields.
     *
     * @var array
     */
    private $pre_fields = array();

    /**
     * Sections configuration.
     *
     * @var array
     */
    private $sections = array();

    /**
     * Post types.
     *
     * @var array
     */
    private $post_types = array();

    /**
     * Post formats.
     *
     * @var array
     */
    private $post_formats = array();

    /**
     * Page templates.
     *
     * @var array
     */
    private $page_templates = array();

    /**
     * Default arguments.
     *
     * @var array
     */
    private $default_args = array(
        'title'              => '',
        'post_type'          => 'post',
        'data_type'          => 'serialize',
        'context'            => 'advanced',
        'priority'           => 'default',
        'exclude_post_types' => array(),
        'page_templates'     => array(),
        'post_formats'       => array(),
        'show_reset'         => false,
        'show_restore'       => false,
        'enqueue_webfont'    => true,
        'async_webfont'      => false,
        'output_css'         => true,
        'theme'              => 'dark',
        'preview'            => true,
        'class'              => '',
        'defaults'           => array(),
    );

    /**
     * Constructor.
     *
     * @param string $key    Unique identifier.
     * @param array  $params Configuration parameters.
     */
    public function __construct( $key, $params = array() ) {
        $this->unique = $key;
        $this->args = wp_parse_args( $params['args'], $this->default_args );
        $this->sections = $params['sections'];
        $this->post_types = (array) $this->args['post_type'];
        $this->post_formats = (array) $this->args['post_formats'];
        $this->page_templates = (array) $this->args['page_templates'];
        $this->pre_fields = $this->extract_fields( $this->sections );

        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_box' ) );
        add_action( 'edit_attachment', array( $this, 'save_meta_box' ) );

        // Add metabox classes for conditional display.
        if ( ! empty( $this->page_templates ) || ! empty( $this->post_formats ) || ! empty( $this->args['class'] ) ) {
            foreach ( $this->post_types as $post_type ) {
                add_filter( 'postbox_classes_' . $post_type . '_' . $this->unique, array( $this, 'add_metabox_classes' ) );
            }
        }

        parent::__construct();
    }

    /**
     * Extract all fields from sections.
     *
     * @param array $sections Sections array.
     * @return array
     */
    private function extract_fields( $sections ) {
        $fields = array();

        foreach ( $sections as $section ) {
            if ( ! empty( $section['fields'] ) ) {
                $fields = array_merge( $fields, $section['fields'] );
            }
        }

        return $fields;
    }

    /**
     * Add CSS classes to metabox for conditional display.
     *
     * @param array $classes Existing classes.
     * @return array
     */
    public function add_metabox_classes( $classes ) {
        global $post;

        // Post format classes.
        if ( ! empty( $this->post_formats ) ) {
            $current_format = get_post_format( $post ) ?: 'default';
            $classes[] = 'pw-metabox-post-formats';

            // Convert 'standard' to 'default'.
            if ( in_array( 'standard', $this->post_formats, true ) ) {
                $key = array_search( 'standard', $this->post_formats, true );
                if ( false !== $key ) {
                    $this->post_formats[ $key ] = 'default';
                }
            }

            foreach ( $this->post_formats as $format ) {
                $classes[] = 'pw-metabox-format-' . $format;
            }

            $classes[] = in_array( $current_format, $this->post_formats, true )
                ? 'pw-metabox-show'
                : 'pw-metabox-hide';
        }

        // Page template classes.
        if ( ! empty( $this->page_templates ) ) {
            $current_template = get_page_template_slug( $post ) ?: 'default';
            $classes[] = 'pw-metabox-page-templates';

            foreach ( $this->page_templates as $template ) {
                $slug = preg_replace( '/[^a-zA-Z0-9]+/', '-', strtolower( $template ) );
                $classes[] = 'pw-metabox-template-' . $slug;
            }

            $classes[] = in_array( $current_template, $this->page_templates, true )
                ? 'pw-metabox-show'
                : 'pw-metabox-hide';
        }

        // Custom class.
        if ( ! empty( $this->args['class'] ) ) {
            $classes[] = $this->args['class'];
        }

        return $classes;
    }

    /**
     * Register metaboxes.
     */
    public function add_meta_boxes() {
        foreach ( $this->post_types as $post_type ) {
            if ( in_array( $post_type, $this->args['exclude_post_types'], true ) ) {
                continue;
            }

            add_meta_box(
                $this->unique,
                $this->args['title'],
                array( $this, 'render_meta_box' ),
                $post_type,
                $this->args['context'],
                $this->args['priority']
            );
        }
    }

    /**
     * Render metabox content.
     *
     * @param \WP_Post $post Current post object.
     */
    public function render_meta_box( $post ) {
        $has_nav = count( $this->sections ) > 1 && 'side' !== $this->args['context'];
        $show_all = ! $has_nav ? ' pw-show-all' : '';
        $theme = $this->args['theme'] ? ' pw-theme-' . $this->args['theme'] : '';

        wp_nonce_field( 'pw_metabox_nonce', 'pw_metabox_nonce_' . $this->unique );

        echo '<div class="pw-metabox' . esc_attr( $theme ) . '">';
        echo '<div class="pw-metabox-wrapper' . esc_attr( $show_all ) . '">';

        if ( $has_nav ) {
            $this->render_navigation();
        }

        echo '<div class="pw-metabox-content">';
        echo '<div class="pw-metabox-sections">';

        foreach ( $this->sections as $index => $section ) {
            $this->render_section( $section, $index, $has_nav );
        }

        echo '</div>';
        echo '</div>';

        if ( $has_nav ) {
            echo '<div class="pw-nav-background"></div>';
        }

        echo '<div class="clear"></div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render navigation tabs.
     */
    private function render_navigation() {
        echo '<div class="pw-metabox-nav">';
        echo '<ul>';

        foreach ( $this->sections as $index => $section ) {
            $icon = ! empty( $section['icon'] ) ? '<i class="' . esc_attr( $section['icon'] ) . '"></i> ' : '';
            echo '<li><a href="#" data-section="' . esc_attr( $this->unique . '_' . $index ) . '">' . $icon . esc_html( $section['title'] ) . '</a></li>';
        }

        echo '</ul>';
        echo '</div>';
    }

    /**
     * Render a single section.
     *
     * @param array $section   Section configuration.
     * @param int   $index     Section index.
     * @param bool  $has_nav   Whether navigation exists.
     */
    private function render_section( $section, $index, $has_nav ) {
        $onload = ! $has_nav ? ' pw-section-onload' : '';
        $class = ! empty( $section['class'] ) ? ' ' . $section['class'] : '';
        $icon = ! empty( $section['icon'] ) ? '<i class="' . esc_attr( $section['icon'] ) . '"></i> ' : '';

        echo '<div id="pw-section-' . esc_attr( $this->unique . '_' . $index ) . '" class="pw-metabox-section' . esc_attr( $onload . $class ) . '">';

        if ( ! empty( $section['title'] ) || ! empty( $section['icon'] ) ) {
            echo '<div class="pw-section-title"><h3>' . $icon . esc_html( $section['title'] ) . '</h3></div>';
        }

        if ( ! empty( $section['fields'] ) ) {
            foreach ( $section['fields'] as $field ) {
                $this->render_field( $field, get_post() );
            }
        } else {
            echo '<div class="pw-no-option">' . esc_html__( 'No data available.', 'pearl-weather' ) . '</div>';
        }

        echo '</div>';
    }

    /**
     * Render a single field.
     *
     * @param array    $field Field configuration.
     * @param \WP_Post $post  Current post object.
     */
    private function render_field( $field, $post ) {
        $field_id = isset( $field['id'] ) ? $field['id'] : '';
        $value = $this->get_field_value( $field, $post );

        // Render field using the framework.
        $this->render_nested_field( $field, $value, $this->unique, 'metabox' );
    }

    /**
     * Get field value from post meta.
     *
     * @param array    $field Field configuration.
     * @param \WP_Post $post  Current post object.
     * @return mixed
     */
    private function get_field_value( $field, $post ) {
        $field_id = $field['id'];

        if ( 'serialize' !== $this->args['data_type'] ) {
            $meta = get_post_meta( $post->ID, $field_id, true );
            $value = $meta !== '' ? $meta : null;
        } else {
            $meta = get_post_meta( $post->ID, $this->unique, true );
            $value = isset( $meta[ $field_id ] ) ? $meta[ $field_id ] : null;
        }

        if ( null === $value ) {
            $value = isset( $field['default'] ) ? $field['default'] : '';
            if ( isset( $this->args['defaults'][ $field_id ] ) ) {
                $value = $this->args['defaults'][ $field_id ];
            }
        }

        return $value;
    }

    /**
     * Save metabox data.
     *
     * @param int $post_id Post ID.
     */
    public function save_meta_box( $post_id ) {
        $nonce_key = 'pw_metabox_nonce_' . $this->unique;
        
        if ( ! isset( $_POST[ $nonce_key ] ) || ! wp_verify_nonce( $_POST[ $nonce_key ], 'pw_metabox_nonce' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $data = array();
        $errors = array();

        $request = isset( $_POST[ $this->unique ] ) ? $_POST[ $this->unique ] : array();

        foreach ( $this->pre_fields as $field ) {
            $this->process_field( $field, $request, $data, $errors );
        }

        $data = apply_filters( "pw_{$this->unique}_save", $data, $post_id, $this );

        if ( empty( $data ) || ! empty( $request['_reset'] ) ) {
            if ( 'serialize' !== $this->args['data_type'] ) {
                foreach ( $data as $key => $value ) {
                    delete_post_meta( $post_id, $key );
                }
            } else {
                delete_post_meta( $post_id, $this->unique );
            }
        } else {
            if ( 'serialize' !== $this->args['data_type'] ) {
                foreach ( $data as $key => $value ) {
                    update_post_meta( $post_id, $key, $value );
                }
            } else {
                update_post_meta( $post_id, $this->unique, $data );
            }

            if ( ! empty( $errors ) ) {
                update_post_meta( $post_id, '_pw_errors_' . $this->unique, $errors );
            }
        }
    }

    /**
     * Process a single field for saving.
     *
     * @param array $field   Field configuration.
     * @param array $request Request data.
     * @param array $data    Data array.
     * @param array $errors  Errors array.
     */
    private function process_field( $field, $request, &$data, &$errors ) {
        if ( empty( $field['id'] ) || ! empty( $field['only_pro'] ) ) {
            return;
        }

        $field_id = $field['id'];
        $field_value = isset( $request[ $field_id ] ) ? $request[ $field_id ] : '';

        // Sanitize.
        if ( isset( $field['sanitize'] ) && is_callable( $field['sanitize'] ) ) {
            $data[ $field_id ] = call_user_func( $field['sanitize'], $field_value );
        } else {
            $data[ $field_id ] = is_array( $field_value )
                ? array_map( 'sanitize_text_field', $field_value )
                : sanitize_text_field( $field_value );
        }

        // Validate.
        if ( isset( $field['validate'] ) && is_callable( $field['validate'] ) ) {
            $validated = call_user_func( $field['validate'], $field_value );

            if ( ! empty( $validated ) ) {
                $errors[ $field_id ] = $validated;
                $data[ $field_id ] = $this->get_field_value( $field, get_post() );
            }
        }
    }
}