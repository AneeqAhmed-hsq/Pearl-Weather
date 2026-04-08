<?php
/**
 * Gutenberg Block Initializer
 *
 * Registers the Gutenberg block for inserting weather shortcodes.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Admin/Gutenberg
 * @since      1.0.0
 */

namespace PearlWeather\Admin\Gutenberg;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class GutenbergBlockInit
 *
 * Handles Gutenberg block registration and assets.
 *
 * @since 1.0.0
 */
class GutenbergBlockInit {

    /**
     * Script suffix (minified or not).
     *
     * @var string
     */
    private $script_suffix;

    /**
     * Post type for weather widgets.
     */
    const POST_TYPE = 'pearl_weather_widget';

    /**
     * Block name.
     */
    const BLOCK_NAME = 'pearl-weather/shortcode';

    /**
     * Constructor.
     */
    public function __construct() {
        $this->script_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        
        add_action( 'init', array( $this, 'register_block' ) );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
    }

    /**
     * Get list of available weather widgets.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_widget_list() {
        $widgets = get_posts( array(
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        if ( empty( $widgets ) ) {
            return array();
        }

        return array_map( function( $widget ) {
            return array(
                'id'    => absint( $widget->ID ),
                'title' => esc_html( $widget->post_title ),
            );
        }, $widgets );
    }

    /**
     * Enqueue editor assets for Gutenberg block.
     *
     * @since 1.0.0
     */
    public function enqueue_editor_assets() {
        // Enqueue styles for editor.
        wp_enqueue_style( 'pearl-weather-icons' );
        wp_enqueue_style( 'pearl-weather-public' );
        wp_enqueue_style( 'pearl-weather-admin' );

        // Enqueue scripts.
        wp_enqueue_script(
            'pearl-weather-gutenberg',
            PEARL_WEATHER_ASSETS_URL . 'js/gutenberg-block' . $this->script_suffix . '.js',
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch' ),
            PEARL_WEATHER_VERSION,
            true
        );

        // Localize script with widget data.
        wp_localize_script(
            'pearl-weather-gutenberg',
            'pearlWeatherGutenberg',
            array(
                'widgets'       => $this->get_widget_list(),
                'ajax_url'      => admin_url( 'admin-ajax.php' ),
                'nonce'         => wp_create_nonce( 'pw_gutenberg_nonce' ),
                'create_url'    => admin_url( 'post-new.php?post_type=' . self::POST_TYPE ),
                'edit_url'      => admin_url( 'edit.php?post_type=' . self::POST_TYPE ),
                'plugin_url'    => PEARL_WEATHER_URL,
            )
        );
    }

    /**
     * Register Gutenberg block.
     *
     * @since 1.0.0
     */
    public function register_block() {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        register_block_type( self::BLOCK_NAME, array(
            'attributes'      => array(
                'widgetId' => array(
                    'type'    => 'string',
                    'default' => '',
                ),
                'align'    => array(
                    'type'    => 'string',
                    'default' => 'wide',
                ),
                'preview'  => array(
                    'type'    => 'boolean',
                    'default' => false,
                ),
            ),
            'example'         => array(
                'attributes' => array(
                    'preview' => true,
                ),
            ),
            'supports'        => array(
                'align'  => array( 'wide', 'full' ),
                'html'   => false,
            ),
            'render_callback' => array( $this, 'render_block' ),
        ) );
    }

    /**
     * Render callback for Gutenberg block.
     *
     * @since 1.0.0
     * @param array  $attributes Block attributes.
     * @param string $content    Block content.
     * @return string
     */
    public function render_block( $attributes, $content = '' ) {
        $widget_id = isset( $attributes['widgetId'] ) ? absint( $attributes['widgetId'] ) : 0;
        $align = isset( $attributes['align'] ) ? sanitize_text_field( $attributes['align'] ) : '';
        $is_preview = isset( $attributes['preview'] ) ? (bool) $attributes['preview'] : false;

        if ( empty( $widget_id ) ) {
            return $this->render_empty_state( $align );
        }

        // Verify the widget exists.
        $widget = get_post( $widget_id );
        if ( ! $widget || self::POST_TYPE !== $widget->post_type || 'publish' !== $widget->post_status ) {
            return $this->render_error_state( $align, __( 'Widget not found or not published.', 'pearl-weather' ) );
        }

        $class_names = array( 'wp-block-pearl-weather-shortcode' );
        
        if ( ! empty( $align ) ) {
            $class_names[] = 'align' . $align;
        }

        if ( ! empty( $attributes['className'] ) ) {
            $class_names[] = esc_attr( $attributes['className'] );
        }

        $wrapper_attributes = get_block_wrapper_attributes( array(
            'class' => implode( ' ', $class_names ),
        ) );

        // In preview mode, show a placeholder.
        if ( $is_preview ) {
            return sprintf(
                '<div %s><div class="pw-gutenberg-preview">%s</div></div>',
                $wrapper_attributes,
                esc_html__( 'Weather Widget Preview', 'pearl-weather' )
            );
        }

        // Render the shortcode.
        $shortcode = sprintf( '[pearl-weather id="%d"]', $widget_id );
        
        return sprintf(
            '<div %s>%s</div>',
            $wrapper_attributes,
            do_shortcode( $shortcode )
        );
    }

    /**
     * Render empty state when no widget is selected.
     *
     * @since 1.0.0
     * @param string $align Block alignment.
     * @return string
     */
    private function render_empty_state( $align ) {
        $class_names = array( 'wp-block-pearl-weather-shortcode', 'pw-block-empty' );
        
        if ( ! empty( $align ) ) {
            $class_names[] = 'align' . $align;
        }

        $wrapper_attributes = get_block_wrapper_attributes( array(
            'class' => implode( ' ', $class_names ),
        ) );

        $create_url = admin_url( 'post-new.php?post_type=' . self::POST_TYPE );
        $edit_url = admin_url( 'edit.php?post_type=' . self::POST_TYPE );

        $message = sprintf(
            /* translators: %1$s: link to create a new widget, %2$s: link to edit existing widgets */
            __( 'No weather widget selected. %1$sCreate a new widget%2$s or %3$sselect an existing one%4$s.', 'pearl-weather' ),
            '<a href="' . esc_url( $create_url ) . '">',
            '</a>',
            '<a href="' . esc_url( $edit_url ) . '">',
            '</a>'
        );

        return sprintf(
            '<div %s><div class="pw-block-empty-message">%s</div></div>',
            $wrapper_attributes,
            $message
        );
    }

    /**
     * Render error state.
     *
     * @since 1.0.0
     * @param string $align   Block alignment.
     * @param string $message Error message.
     * @return string
     */
    private function render_error_state( $align, $message ) {
        $class_names = array( 'wp-block-pearl-weather-shortcode', 'pw-block-error' );
        
        if ( ! empty( $align ) ) {
            $class_names[] = 'align' . $align;
        }

        $wrapper_attributes = get_block_wrapper_attributes( array(
            'class' => implode( ' ', $class_names ),
        ) );

        return sprintf(
            '<div %s><div class="pw-block-error-message">%s</div></div>',
            $wrapper_attributes,
            esc_html( $message )
        );
    }
}

// Initialize Gutenberg block.
new GutenbergBlockInit();