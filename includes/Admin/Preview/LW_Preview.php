<?php
/**
 * Admin Preview Handler
 *
 * Handles AJAX-based live preview of weather widgets in the admin area.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Admin/Preview
 * @since      1.0.0
 */

namespace PearlWeather\Admin\Preview;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PearlWeather\Frontend\ShortcodeHandler;
use PearlWeather\Admin\AssetsManager;

/**
 * Class PreviewHandler
 *
 * Manages live preview functionality for weather widgets.
 *
 * @since 1.0.0
 */
class PreviewHandler {

    /**
     * AJAX action name.
     */
    const AJAX_ACTION = 'pearl_weather_preview_widget';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'render_preview' ) );
    }

    /**
     * Render preview via AJAX.
     *
     * @since 1.0.0
     */
    public function render_preview() {
        // Verify nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'pw_preview_nonce' ) ) {
            wp_die( -1, 403 );
        }

        // Check user capability.
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( -1, 403 );
        }

        // Get widget ID.
        $widget_id = isset( $_POST['widget_id'] ) ? absint( $_POST['widget_id'] ) : 0;

        if ( empty( $widget_id ) ) {
            wp_die( esc_html__( 'Invalid widget ID.', 'pearl-weather' ), 400 );
        }

        // Get form data.
        $form_data = isset( $_POST['form_data'] ) ? wp_unslash( $_POST['form_data'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        // Parse form data.
        parse_str( $form_data, $settings );

        // Extract settings.
        $widget_settings = isset( $settings['pearl_weather_settings'] ) ? $settings['pearl_weather_settings'] : array();
        $layout_settings = isset( $settings['pearl_weather_layout'] ) ? $settings['pearl_weather_layout'] : array();

        // Validate layout.
        if ( isset( $layout_settings['weather_view'] ) && ! in_array( $layout_settings['weather_view'], array( 'vertical', 'horizontal' ), true ) ) {
            $layout_settings['weather_view'] = 'vertical';
        }

        // Set preview mode flag.
        add_filter( 'pearl_weather_is_preview', '__return_true' );

        // Enqueue preview styles.
        $this->enqueue_preview_assets();

        // Generate dynamic CSS.
        $dynamic_css = $this->generate_preview_css( $widget_id, $widget_settings );
        if ( ! empty( $dynamic_css ) ) {
            echo '<style id="pw-preview-css">' . wp_strip_all_tags( $dynamic_css ) . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        // Get global settings.
        $global_settings = get_option( 'pearl_weather_settings', array() );

        // Render the widget preview.
        $shortcode_handler = new ShortcodeHandler();
        $shortcode_handler->render_widget( $widget_id, $widget_settings, $layout_settings, $global_settings, true );

        // Remove preview mode flag.
        remove_filter( 'pearl_weather_is_preview', '__return_true' );

        wp_die();
    }

    /**
     * Enqueue preview assets.
     *
     * @since 1.0.0
     */
    private function enqueue_preview_assets() {
        // Enqueue styles.
        wp_enqueue_style( 'pearl-weather-public' );
        wp_enqueue_style( 'pearl-weather-icons' );
        wp_enqueue_style( 'pearl-weather-swiper' );

        // Enqueue scripts.
        wp_enqueue_script( 'pearl-weather-public' );
        wp_enqueue_script( 'pearl-weather-swiper' );
    }

    /**
     * Generate preview-specific CSS.
     *
     * @since 1.0.0
     * @param int   $widget_id Widget ID.
     * @param array $settings  Widget settings.
     * @return string
     */
    private function generate_preview_css( $widget_id, $settings ) {
        $css = '';

        // Container styles.
        $max_width = isset( $settings['max_width'] ) ? (int) $settings['max_width'] : 400;
        $bg_color = isset( $settings['bg_color'] ) ? sanitize_hex_color( $settings['bg_color'] ) : '';
        $border_radius = isset( $settings['border_radius'] ) ? (int) $settings['border_radius'] : 12;

        $css .= "#pw-preview-{$widget_id} .pw-weather-card {\n";
        $css .= "    max-width: {$max_width}px;\n";
        $css .= "    margin: 0 auto;\n";
        
        if ( ! empty( $bg_color ) ) {
            $css .= "    background: {$bg_color};\n";
        }
        
        $css .= "    border-radius: {$border_radius}px;\n";
        $css .= "}\n";

        // Typography styles.
        $title_color = isset( $settings['title_color'] ) ? sanitize_hex_color( $settings['title_color'] ) : '';
        if ( ! empty( $title_color ) ) {
            $css .= "#pw-preview-{$widget_id} .pw-title { color: {$title_color}; }\n";
        }

        $text_color = isset( $settings['text_color'] ) ? sanitize_hex_color( $settings['text_color'] ) : '';
        if ( ! empty( $text_color ) ) {
            $css .= "#pw-preview-{$widget_id} .pw-weather-card { color: {$text_color}; }\n";
        }

        return $css;
    }
}

// Initialize preview handler.
new PreviewHandler();