<?php
/**
 * Frontend Initialization
 *
 * Handles all frontend functionality for the Pearl Weather plugin,
 * including shortcode registration, asset loading, and HTML optimization.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Frontend
 * @since      1.0.0
 */

namespace PearlWeather;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Frontend
 *
 * Initializes and manages all frontend components of the plugin.
 *
 * @since 1.0.0
 */
class Frontend {

    /**
     * Instance of this class.
     *
     * @var Frontend
     */
    private static $instance = null;

    /**
     * Whether HTML minification is enabled.
     *
     * @var bool
     */
    private $minify_enabled = false;

    /**
     * Get singleton instance.
     *
     * @since 1.0.0
     * @return Frontend
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     * Initializes frontend components.
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->check_minify_settings();
        $this->init_components();
        $this->init_hooks();
    }

    /**
     * Check if HTML minification is enabled from settings.
     *
     * @since 1.0.0
     */
    private function check_minify_settings() {
        $settings = get_option( 'pearl_weather_settings', array() );
        $this->minify_enabled = isset( $settings['enable_html_minify'] ) && true === $settings['enable_html_minify'];
    }

    /**
     * Initialize all frontend components.
     *
     * @since 1.0.0
     */
    private function init_components() {
        // Load shortcode handler.
        if ( class_exists( 'PearlWeather\Frontend\Shortcode' ) ) {
            new Frontend\Shortcode();
        }

        // Load assets loader.
        if ( class_exists( 'PearlWeather\Frontend\AssetsLoader' ) ) {
            new Frontend\AssetsLoader();
        }

        // Load weather display handler.
        if ( class_exists( 'PearlWeather\Frontend\WeatherDisplay' ) ) {
            new Frontend\WeatherDisplay();
        }
    }

    /**
     * Initialize WordPress hooks.
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Apply HTML minification to output buffer if enabled.
        if ( $this->minify_enabled && ! is_admin() ) {
            add_action( 'wp_loaded', array( $this, 'start_output_buffer' ) );
            add_action( 'shutdown', array( $this, 'end_output_buffer' ), 0 );
        }
    }

    /**
     * Start output buffering for HTML minification.
     *
     * @since 1.0.0
     */
    public function start_output_buffer() {
        if ( ! is_admin() && ! defined( 'DOING_AJAX' ) && ! defined( 'DOING_CRON' ) ) {
            ob_start( array( $this, 'minify_html' ) );
        }
    }

    /**
     * End output buffering.
     *
     * @since 1.0.0
     */
    public function end_output_buffer() {
        if ( ob_get_length() > 0 ) {
            ob_end_flush();
        }
    }

    /**
     * Minify HTML output by removing comments, whitespace, and extra spaces.
     *
     * @since 1.0.0
     * @param string $html The HTML content to minify.
     * @return string Minified HTML.
     */
    public function minify_html( $html ) {
        // Don't minify if disabled or in admin area.
        if ( ! $this->minify_enabled || is_admin() ) {
            return $html;
        }

        // Don't minify if it's an AJAX request.
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return $html;
        }

        // Don't minify if it's a cron job.
        if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
            return $html;
        }

        $original_html = $html;

        try {
            // Remove HTML comments (but preserve conditional comments for IE).
            $html = preg_replace( '/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html );

            // Remove line breaks, tabs, and carriage returns.
            $search = array(
                "\r\n", // Windows line breaks.
                "\r",   // Carriage return.
                "\n",   // New line.
                "\t",   // Tabs.
            );
            $html = str_replace( $search, '', $html );

            // Remove multiple spaces.
            $html = preg_replace( '/\s+/', ' ', $html );

            // Remove spaces between HTML tags.
            $html = preg_replace( '/>\s+</', '><', $html );

            // Trim the final output.
            $html = trim( $html );

        } catch ( \Exception $e ) {
            // If anything fails, return original HTML.
            return $original_html;
        }

        /**
         * Filter the minified HTML output.
         *
         * @since 1.0.0
         * @param string $html Minified HTML.
         * @param string $original_html Original HTML before minification.
         */
        return apply_filters( 'pearl_weather_minified_html', $html, $original_html );
    }

    /**
     * Static helper method for minifying HTML.
     * Can be called from anywhere.
     *
     * @since 1.0.0
     * @param string $html The HTML content to minify.
     * @return string Minified HTML.
     */
    public static function minify_output( $html ) {
        $frontend = self::get_instance();
        return $frontend->minify_html( $html );
    }
}

// Initialize the frontend.
if ( ! function_exists( 'pearl_weather_frontend_init' ) ) {
    /**
     * Initialize frontend components.
     *
     * @since 1.0.0
     * @return Frontend
     */
    function pearl_weather_frontend_init() {
        return Frontend::get_instance();
    }
    pearl_weather_frontend_init();
}