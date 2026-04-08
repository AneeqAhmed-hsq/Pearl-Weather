<?php
/**
 * Elementor Integration for Pearl Weather
 *
 * Registers the weather widget as an Elementor block and manages
 * assets for the Elementor editor and preview modes.
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
 * Class ElementorIntegration
 *
 * Handles Elementor page builder integration.
 *
 * @since 1.0.0
 */
class ElementorIntegration {

    /**
     * Singleton instance.
     *
     * @var ElementorIntegration
     */
    private static $instance = null;

    /**
     * Script suffix (minified or not).
     *
     * @var string
     */
    private $script_suffix = '';

    /**
     * Get singleton instance.
     *
     * @since 1.0.0
     * @return ElementorIntegration
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->script_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks.
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        add_action( 'plugins_loaded', array( $this, 'check_elementor_and_init' ) );
        add_action( 'elementor/preview/enqueue_scripts', array( $this, 'enqueue_preview_scripts' ) );
        add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue_preview_styles' ) );
        add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'enqueue_editor_icon' ) );
    }

    /**
     * Check if Elementor is active and initialize.
     *
     * @since 1.0.0
     */
    public function check_elementor_and_init() {
        if ( did_action( 'elementor/loaded' ) ) {
            add_action( 'elementor/init', array( $this, 'init' ) );
        }
    }

    /**
     * Initialize Elementor integration.
     *
     * @since 1.0.0
     */
    public function init() {
        add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
    }

    /**
     * Register Elementor widgets.
     *
     * @since 1.0.0
     * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
     */
    public function register_widgets( $widgets_manager ) {
        // Load widget class.
        require_once PEARL_WEATHER_INCLUDES_PATH . 'admin/elementor/class-weather-widget.php';

        // Register widget.
        $widgets_manager->register( new \PearlWeather\Admin\Elementor\WeatherWidget() );
    }

    /**
     * Enqueue scripts for Elementor preview mode.
     *
     * @since 1.0.0
     */
    public function enqueue_preview_scripts() {
        wp_enqueue_script( 'pearl-weather-public' );
        wp_enqueue_script( 'pearl-weather-swiper' );
    }

    /**
     * Enqueue styles for Elementor preview mode.
     *
     * @since 1.0.0
     */
    public function enqueue_preview_styles() {
        wp_enqueue_style( 'pearl-weather-icons' );
        wp_enqueue_style( 'pearl-weather-public' );
        wp_enqueue_style( 'pearl-weather-swiper' );
    }

    /**
     * Enqueue icon font for Elementor editor.
     *
     * @since 1.0.0
     */
    public function enqueue_editor_icon() {
        wp_enqueue_style(
            'pearl-weather-elementor-icon',
            PEARL_WEATHER_ASSETS_URL . 'css/elementor-icon.css',
            array(),
            PEARL_WEATHER_VERSION
        );
    }
}

// Initialize Elementor integration.
ElementorIntegration::get_instance();