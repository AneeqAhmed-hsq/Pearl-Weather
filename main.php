<?php
/**
 * Pearl Weather
 *
 * @package           PearlWeather
 * @author            Your Name
 * @copyright         2026 Your Company
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Pearl Weather
 * Description:       Pearl Weather is a powerful WordPress weather forecast plugin that displays real-time weather data using OpenWeatherMap API. Supports auto-location detection, manual location search, and responsive weather widgets.
 * Plugin URI:        https://example.com/pearl-weather
 * Author:            Aneeq Ahmed 
 * Author URI:        https://example.com
 * Version:           1.0.0
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pearl-weather
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'PEARL_WEATHER_VERSION', '1.0.0' );
define( 'PEARL_WEATHER_FILE', __FILE__ );
define( 'PEARL_WEATHER_PATH', plugin_dir_path( __FILE__ ) );
define( 'PEARL_WEATHER_URL', plugin_dir_url( __FILE__ ) );
define( 'PEARL_WEATHER_ASSETS_URL', PEARL_WEATHER_URL . 'assets/' );
define( 'PEARL_WEATHER_INCLUDES_PATH', PEARL_WEATHER_PATH . 'includes/' );
define( 'PEARL_WEATHER_BASENAME', plugin_basename( __FILE__ ) );

// Check for Pro version to avoid conflicts.
if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$pro_active = is_plugin_active( 'pearl-weather-pro/pearl-weather-pro.php' ) || 
              is_plugin_active_for_network( 'pearl-weather-pro/pearl-weather-pro.php' );

if ( ! $pro_active ) {
    require_once PEARL_WEATHER_PATH . 'vendor/autoload.php';
}

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
final class Pearl_Weather {

    /**
     * Singleton instance.
     *
     * @var Pearl_Weather
     */
    private static $instance = null;

    /**
     * Plugin version.
     *
     * @var string
     */
    private $version = PEARL_WEATHER_VERSION;

    /**
     * Script suffix (minified or not).
     *
     * @var string
     */
    private $script_suffix = '';

    /**
     * Plugin slug.
     *
     * @var string
     */
    private $plugin_slug = 'pearl-weather';

    /**
     * Get singleton instance.
     *
     * @return Pearl_Weather
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
        $this->set_script_suffix();
        $this->define_constants();
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Set script suffix based on debug mode.
     */
    private function set_script_suffix() {
        $this->script_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
    }

    /**
     * Define additional constants.
     */
    private function define_constants() {
        if ( ! defined( 'PEARL_WEATHER_SLUG' ) ) {
            define( 'PEARL_WEATHER_SLUG', $this->plugin_slug );
        }
        if ( ! defined( 'PEARL_WEATHER_TEMPLATE_PATH' ) ) {
            define( 'PEARL_WEATHER_TEMPLATE_PATH', PEARL_WEATHER_INCLUDES_PATH . 'templates/' );
        }
    }

    /**
     * Load dependencies.
     */
    private function load_dependencies() {
        // Core classes.
        require_once PEARL_WEATHER_INCLUDES_PATH . 'class-api-handler.php';
        require_once PEARL_WEATHER_INCLUDES_PATH . 'class-location-detector.php';
        require_once PEARL_WEATHER_INCLUDES_PATH . 'class-cache-manager.php';
        
        // Admin classes.
        if ( is_admin() ) {
            require_once PEARL_WEATHER_INCLUDES_PATH . 'admin/class-admin-settings.php';
            require_once PEARL_WEATHER_INCLUDES_PATH . 'admin/class-admin-notices.php';
        }
        
        // Frontend classes.
        require_once PEARL_WEATHER_INCLUDES_PATH . 'frontend/class-shortcode.php';
        require_once PEARL_WEATHER_INCLUDES_PATH . 'frontend/class-assets-loader.php';
    }

    /**
     * Initialize WordPress hooks.
     */
    private function init_hooks() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'wp_loaded', array( $this, 'register_scripts_and_styles' ) );
        add_action( 'widgets_init', array( $this, 'register_widgets' ) );
        
        // Activation redirect.
        register_activation_hook( PEARL_WEATHER_FILE, array( $this, 'activate_plugin' ) );
        
        // AJAX handlers.
        add_action( 'wp_ajax_pearl_weather_get_weather', array( $this, 'ajax_get_weather' ) );
        add_action( 'wp_ajax_nopriv_pearl_weather_get_weather', array( $this, 'ajax_get_weather' ) );
        
        // Plugin action links.
        add_filter( 'plugin_action_links_' . PEARL_WEATHER_BASENAME, array( $this, 'add_action_links' ) );
        add_filter( 'plugin_row_meta', array( $this, 'add_row_meta' ), 10, 2 );
    }

    /**
     * Load plugin textdomain.
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'pearl-weather',
            false,
            dirname( PEARL_WEATHER_BASENAME ) . '/languages/'
        );
    }

    /**
     * Register all scripts and styles.
     */
    public function register_scripts_and_styles() {
        // Styles.
        wp_register_style(
            'pearl-weather-public',
            PEARL_WEATHER_ASSETS_URL . 'css/public' . $this->script_suffix . '.css',
            array(),
            $this->version
        );
        
        wp_register_style(
            'pearl-weather-icons',
            PEARL_WEATHER_ASSETS_URL . 'css/icons' . $this->script_suffix . '.css',
            array(),
            $this->version
        );
        
        // Admin styles.
        wp_register_style(
            'pearl-weather-admin',
            PEARL_WEATHER_ASSETS_URL . 'css/admin' . $this->script_suffix . '.css',
            array(),
            $this->version
        );
        
        // Scripts.
        wp_register_script(
            'pearl-weather-public',
            PEARL_WEATHER_ASSETS_URL . 'js/public' . $this->script_suffix . '.js',
            array( 'jquery' ),
            $this->version,
            true
        );
        
        // Localize script with AJAX data.
        wp_localize_script(
            'pearl-weather-public',
            'pearlWeatherAjax',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'pearl_weather_nonce' ),
                'debug'    => defined( 'WP_DEBUG' ) && WP_DEBUG,
            )
        );
    }

    /**
     * Register widgets.
     */
    public function register_widgets() {
        if ( class_exists( 'Pearl_Weather_Widget' ) ) {
            register_widget( 'Pearl_Weather_Widget' );
        }
    }

    /**
     * Plugin activation handler.
     */
    public function activate_plugin() {
        // Set default options.
        $defaults = array(
            'api_key'          => '',
            'cache_duration'   => 600, // 10 minutes in seconds.
            'units'            => 'metric', // metric or imperial.
            'default_location' => '',
            'enable_geolocation' => true,
        );
        
        if ( false === get_option( 'pearl_weather_settings' ) ) {
            add_option( 'pearl_weather_settings', $defaults );
        }
        
        // Redirect to setup wizard.
        if ( ! get_option( 'pearl_weather_setup_completed' ) ) {
            set_transient( 'pearl_weather_activation_redirect', true, 30 );
        }
        
        // Schedule weekly cache cleanup.
        if ( ! wp_next_scheduled( 'pearl_weather_weekly_cleanup' ) ) {
            wp_schedule_event( time(), 'weekly', 'pearl_weather_weekly_cleanup' );
        }
    }

    /**
     * AJAX handler for weather data.
     */
    public function ajax_get_weather() {
        // Verify nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'pearl_weather_nonce' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'pearl-weather' ) ), 403 );
        }
        
        // Sanitize input.
        $location = isset( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : '';
        $widget_id = isset( $_POST['widget_id'] ) ? absint( $_POST['widget_id'] ) : 0;
        
        if ( empty( $location ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Location is required.', 'pearl-weather' ) ) );
        }
        
        // Get weather data via API handler.
        if ( class_exists( 'Pearl_Weather_API_Handler' ) ) {
            $api_handler = new Pearl_Weather_API_Handler();
            $weather_data = $api_handler->get_weather_data( $location, $widget_id );
            
            if ( $weather_data ) {
                wp_send_json_success( $weather_data );
            } else {
                wp_send_json_error( array( 'message' => esc_html__( 'Could not fetch weather data.', 'pearl-weather' ) ) );
            }
        }
        
        wp_send_json_error( array( 'message' => esc_html__( 'API handler not available.', 'pearl-weather' ) ) );
    }

    /**
     * Add plugin action links.
     *
     * @param array $links Existing links.
     * @return array
     */
    public function add_action_links( $links ) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'admin.php?page=pearl-weather-settings' ) ),
            esc_html__( 'Settings', 'pearl-weather' )
        );
        
        $upgrade_link = sprintf(
            '<a href="%s" style="color: #35b747; font-weight: bold;">%s</a>',
            esc_url( 'https://example.com/pearl-weather-pro/' ),
            esc_html__( 'Go Pro', 'pearl-weather' )
        );
        
        array_unshift( $links, $settings_link );
        $links['go_pro'] = $upgrade_link;
        
        return $links;
    }

    /**
     * Add row meta links.
     *
     * @param array  $links Existing links.
     * @param string $file Plugin file.
     * @return array
     */
    public function add_row_meta( $links, $file ) {
        if ( PEARL_WEATHER_BASENAME === $file ) {
            $row_meta = array(
                'docs'    => sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    esc_url( 'https://example.com/docs/' ),
                    esc_html__( 'Documentation', 'pearl-weather' )
                ),
                'support' => sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    esc_url( 'https://example.com/support/' ),
                    esc_html__( 'Support', 'pearl-weather' )
                ),
            );
            $links = array_merge( $links, $row_meta );
        }
        
        return $links;
    }
}

/**
 * Initialize the plugin if Pro version is not active.
 */
if ( ! ( is_plugin_active( 'pearl-weather-pro/pearl-weather-pro.php' ) || 
        is_plugin_active_for_network( 'pearl-weather-pro/pearl-weather-pro.php' ) ) ) {
    
    /**
     * Helper function to initialize plugin.
     *
     * @return Pearl_Weather
     */
    function pearl_weather() {
        return Pearl_Weather::get_instance();
    }
    
    // Start the plugin.
    pearl_weather();
}