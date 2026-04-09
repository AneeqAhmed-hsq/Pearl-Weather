<?php
/**
 * Framework Abstract Base Class
 *
 * Provides base functionality for collecting output CSS and Google Fonts
 * from all framework fields.
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
 * Abstract Class BaseFramework
 *
 * @since 1.0.0
 */
abstract class BaseFramework {

    /**
     * Abstract type (options, metabox, customize).
     *
     * @var string
     */
    protected $abstract = '';

    /**
     * Collected CSS output.
     *
     * @var string
     */
    protected $output_css = '';

    /**
     * Collected Google Fonts.
     *
     * @var array
     */
    protected $web_fonts = array();

    /**
     * Font subsets.
     *
     * @var array
     */
    protected $subsets = array();

    /**
     * Field configurations.
     *
     * @var array
     */
    protected $fields = array();

    /**
     * Options values.
     *
     * @var array
     */
    protected $options = array();

    /**
     * Unique identifier.
     *
     * @var string
     */
    protected $unique = '';

    /**
     * Arguments.
     *
     * @var array
     */
    protected $args = array();

    /**
     * Constructor.
     */
    public function __construct() {
        // Collect output CSS and typography.
        if ( ! empty( $this->args['output_css'] ) || ! empty( $this->args['enqueue_webfont'] ) ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'collect_output_css_and_fonts' ), 10 );
        }
    }

    /**
     * Collect output CSS and Google Fonts.
     *
     * @since 1.0.0
     */
    public function collect_output_css_and_fonts() {
        $this->recursive_collect( $this->fields );
    }

    /**
     * Recursively collect CSS and fonts from fields.
     *
     * @since 1.0.0
     * @param array $fields       Fields to process.
     * @param array $parent_field Parent field (for nested fields).
     */
    protected function recursive_collect( $fields = array(), $parent_field = array() ) {
        if ( empty( $fields ) ) {
            return;
        }

        foreach ( $fields as $field ) {
            $field_id = isset( $field['id'] ) ? $field['id'] : '';
            $field_type = isset( $field['type'] ) ? $field['type'] : '';
            $field_output = isset( $field['output'] ) ? $field['output'] : '';
            $needs_processing = ( 'typography' === $field_type || ! empty( $field_output ) );

            if ( empty( $field_type ) || empty( $field_id ) ) {
                // Process nested fields.
                $this->process_nested_fields( $field, $field_type, $field );
                continue;
            }

            // Include field class.
            $this->include_field_class( $field_type );

            $class_name = $this->get_field_class_name( $field_type );

            if ( ! class_exists( $class_name ) ) {
                continue;
            }

            // Get field value.
            $field_value = $this->get_field_value( $field, $parent_field );

            if ( $needs_processing ) {
                $instance = new $class_name( $field, $field_value, $this->unique, 'wp/enqueue', $this );

                // Process Google Fonts.
                if ( 'typography' === $field_type && ! empty( $this->args['enqueue_webfont'] ) && ! empty( $field_value['font-family'] ) ) {
                    $this->process_google_fonts( $instance, $field_value );
                }

                // Process output CSS.
                if ( ! empty( $field_output ) && ! empty( $this->args['output_css'] ) && method_exists( $instance, 'output' ) ) {
                    $this->output_css .= $instance->output();
                }

                unset( $instance );
            }
        }
    }

    /**
     * Process nested fields (fieldset, accordion, tabbed).
     *
     * @since 1.0.0
     * @param array  $field      Field configuration.
     * @param string $field_type Field type.
     * @param array  $parent     Parent field.
     */
    private function process_nested_fields( $field, $field_type, $parent ) {
        if ( 'fieldset' === $field_type && ! empty( $field['fields'] ) ) {
            $this->recursive_collect( $field['fields'], $field );
        }

        if ( 'accordion' === $field_type && ! empty( $field['accordions'] ) ) {
            foreach ( $field['accordions'] as $accordion ) {
                if ( ! empty( $accordion['fields'] ) ) {
                    $this->recursive_collect( $accordion['fields'], $field );
                }
            }
        }

        if ( 'tabbed' === $field_type && ! empty( $field['tabs'] ) ) {
            foreach ( $field['tabs'] as $tab ) {
                if ( ! empty( $tab['fields'] ) ) {
                    $this->recursive_collect( $tab['fields'], $field );
                }
            }
        }
    }

    /**
     * Include field class file.
     *
     * @since 1.0.0
     * @param string $field_type Field type.
     */
    private function include_field_class( $field_type ) {
        $field_file = PEARL_WEATHER_PATH . 'includes/framework/fields/' . $field_type . '.php';
        
        if ( file_exists( $field_file ) ) {
            require_once $field_file;
        }
    }

    /**
     * Get field class name.
     *
     * @since 1.0.0
     * @param string $field_type Field type.
     * @return string
     */
    private function get_field_class_name( $field_type ) {
        $class_map = array(
            'border'      => 'PearlWeather\Framework\Fields\BorderField',
            'checkbox'    => 'PearlWeather\Framework\Fields\CheckboxField',
            'color'       => 'PearlWeather\Framework\Fields\ColorField',
            'color_group' => 'PearlWeather\Framework\Fields\ColorGroupField',
            'image_select'=> 'PearlWeather\Framework\Fields\ImageSelectField',
            'radio'       => 'PearlWeather\Framework\Fields\RadioField',
            'select'      => 'PearlWeather\Framework\Fields\SelectField',
            'slider'      => 'PearlWeather\Framework\Fields\SliderField',
            'spacing'     => 'PearlWeather\Framework\Fields\SpacingField',
            'spinner'     => 'PearlWeather\Framework\Fields\SpinnerField',
            'switcher'    => 'PearlWeather\Framework\Fields\SwitcherField',
            'text'        => 'PearlWeather\Framework\Fields\TextField',
            'textarea'    => 'PearlWeather\Framework\Fields\TextareaField',
            'typography'  => 'PearlWeather\Framework\Fields\TypographyField',
        );

        return isset( $class_map[ $field_type ] ) 
            ? $class_map[ $field_type ] 
            : 'PearlWeather\Framework\Fields\\' . ucfirst( $field_type ) . 'Field';
    }

    /**
     * Get field value based on context.
     *
     * @since 1.0.0
     * @param array $field       Field configuration.
     * @param array $parent_field Parent field.
     * @return mixed
     */
    private function get_field_value( $field, $parent_field ) {
        $field_id = $field['id'];
        $field_type = $field['type'];
        $needs_value = ( 'typography' === $field_type || ! empty( $field['output'] ) );

        if ( ! $needs_value ) {
            return '';
        }

        if ( in_array( $this->abstract, array( 'options', 'customize' ), true ) ) {
            if ( ! empty( $parent_field ) ) {
                return isset( $this->options[ $parent_field['id'] ][ $field_id ] ) 
                    ? $this->options[ $parent_field['id'] ][ $field_id ] 
                    : '';
            }
            return isset( $this->options[ $field_id ] ) ? $this->options[ $field_id ] : '';
        }

        if ( 'metabox' === $this->abstract && is_singular() ) {
            if ( ! empty( $parent_field ) ) {
                $meta_value = $this->get_meta_value( $parent_field );
                return isset( $meta_value[ $field_id ] ) ? $meta_value[ $field_id ] : '';
            }
            return $this->get_meta_value( $field );
        }

        return '';
    }

    /**
     * Process Google Fonts.
     *
     * @since 1.0.0
     * @param object $instance    Field instance.
     * @param array  $field_value Field value.
     */
    private function process_google_fonts( $instance, $field_value ) {
        if ( ! method_exists( $instance, 'enqueue_google_fonts' ) ) {
            return;
        }

        $method = ! empty( $this->args['async_webfont'] ) ? 'async' : 'enqueue';
        $family = $instance->enqueue_google_fonts();

        if ( ! empty( $family ) ) {
            if ( ! isset( $this->web_fonts[ $method ][ $family ] ) ) {
                $this->web_fonts[ $method ][ $family ] = $family;
            }
        }
    }

    /**
     * Get meta value for metabox context.
     *
     * @since 1.0.0
     * @param array $field Field configuration.
     * @return mixed
     */
    protected function get_meta_value( $field ) {
        $field_id = $field['id'];
        $meta_value = get_post_meta( get_the_ID(), $this->unique, true );
        
        return isset( $meta_value[ $field_id ] ) ? $meta_value[ $field_id ] : '';
    }

    /**
     * Get collected CSS.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_output_css() {
        return $this->output_css;
    }

    /**
     * Get collected Google Fonts.
     *
     * @since 1.0.0
     * @return array
     */
    public function get_web_fonts() {
        return $this->web_fonts;
    }

    /**
     * Enqueue Google Fonts.
     *
     * @since 1.0.0
     */
    public function enqueue_google_fonts() {
        if ( empty( $this->web_fonts ) ) {
            return;
        }

        foreach ( $this->web_fonts as $method => $fonts ) {
            if ( 'async' === $method ) {
                // Async loading implementation.
                continue;
            }

            foreach ( $fonts as $family ) {
                $font_url = add_query_arg( array(
                    'family' => urlencode( $family ),
                    'display' => 'swap',
                ), 'https://fonts.googleapis.com/css' );
                
                wp_enqueue_style( 'pw-google-font-' . sanitize_title( $family ), $font_url, array(), null );
            }
        }
    }
}