<?php
/**
 * Admin Scripts & Styles Manager
 *
 * Handles enqueuing of CSS and JS files on admin screens,
 * specifically for the weather widget post type.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Admin
 * @since      1.0.0
 */

namespace PearlWeather\Admin;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AdminAssetsManager
 *
 * Manages admin-facing assets.
 *
 * @since 1.0.0
 */
class AdminAssetsManager {

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
     * Constructor.
     */
    public function __construct() {
        $this->script_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Enqueue admin assets.
     *
     * @since 1.0.0
     * @param string $hook Current admin page hook.
     */
    public function enqueue_assets( $hook ) {
        $screen = get_current_screen();
        
        // Only load on our post type screens.
        if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
            return;
        }

        $this->enqueue_styles();
        $this->enqueue_scripts();
    }

    /**
     * Enqueue admin styles.
     *
     * @since 1.0.0
     */
    private function enqueue_styles() {
        // Main admin style.
        wp_enqueue_style(
            'pearl-weather-admin',
            PEARL_WEATHER_ASSETS_URL . 'css/admin' . $this->script_suffix . '.css',
            array(),
            PEARL_WEATHER_VERSION
        );

        // Icon font.
        wp_enqueue_style(
            'pearl-weather-icons',
            PEARL_WEATHER_ASSETS_URL . 'css/icons' . $this->script_suffix . '.css',
            array(),
            PEARL_WEATHER_VERSION
        );

        // Public styles (for preview).
        wp_enqueue_style(
            'pearl-weather-public',
            PEARL_WEATHER_ASSETS_URL . 'css/public' . $this->script_suffix . '.css',
            array(),
            PEARL_WEATHER_VERSION
        );

        // Swiper styles (for preview).
        wp_enqueue_style(
            'pearl-weather-swiper',
            PEARL_WEATHER_ASSETS_URL . 'css/swiper' . $this->script_suffix . '.css',
            array(),
            PEARL_WEATHER_VERSION
        );
    }

    /**
     * Enqueue admin scripts.
     *
     * @since 1.0.0
     */
    private function enqueue_scripts() {
        // Public script (for preview).
        wp_enqueue_script(
            'pearl-weather-public',
            PEARL_WEATHER_ASSETS_URL . 'js/public' . $this->script_suffix . '.js',
            array( 'jquery' ),
            PEARL_WEATHER_VERSION,
            true
        );

        // Swiper script (for preview).
        wp_enqueue_script(
            'pearl-weather-swiper',
            PEARL_WEATHER_ASSETS_URL . 'js/swiper' . $this->script_suffix . '.js',
            array( 'jquery' ),
            PEARL_WEATHER_VERSION,
            true
        );

        // Localize script for admin AJAX.
        wp_localize_script(
            'pearl-weather-public',
            'pearlWeatherAdmin',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'pearl_weather_admin_nonce' ),
                'post_type' => self::POST_TYPE,
            )
        );
    }
}

// Initialize admin assets manager.
new AdminAssetsManager();