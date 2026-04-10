<?php
namespace AtmoPress;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Bootstrap {

    private static $instance = null;

    public static function init() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_textdomain();
        $this->register_services();
    }

    private function load_textdomain() {
        load_plugin_textdomain( 'atmopress-weather', false, dirname( plugin_basename( ATMOPRESS_FILE ) ) . '/languages' );
    }

    private function register_services() {
        new RestApi();
        new GutenbergBlock();
        new Shortcode();

        if ( is_admin() ) {
            new Admin\AdminPage();
        }

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function enqueue_frontend() {
        wp_enqueue_style(
            'atmopress-frontend',
            ATMOPRESS_ASSETS_URL . 'css/frontend.css',
            array(),
            ATMOPRESS_VERSION
        );

        wp_enqueue_script(
            'atmopress-frontend',
            ATMOPRESS_ASSETS_URL . 'js/frontend.js',
            array( 'jquery' ),
            ATMOPRESS_VERSION,
            true
        );

        wp_localize_script( 'atmopress-frontend', 'AtmoPressData', array(
            'restUrl'  => esc_url_raw( rest_url( 'atmopress/v1/' ) ),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'siteUrl'  => esc_url( get_site_url() ),
            'i18n'     => array(
                'loading'       => __( 'Loading weather…', 'atmopress-weather' ),
                'error'         => __( 'Could not load weather data.', 'atmopress-weather' ),
                'detecting'     => __( 'Detecting location…', 'atmopress-weather' ),
                'noApiKey'      => __( 'API key not configured.', 'atmopress-weather' ),
                'searchPlaceholder' => __( 'Search city…', 'atmopress-weather' ),
            ),
        ) );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'atmopress' ) === false ) {
            return;
        }
        wp_enqueue_style(
            'atmopress-admin',
            ATMOPRESS_ASSETS_URL . 'css/admin.css',
            array(),
            ATMOPRESS_VERSION
        );
        wp_enqueue_script(
            'atmopress-admin',
            ATMOPRESS_ASSETS_URL . 'js/admin.js',
            array( 'jquery' ),
            ATMOPRESS_VERSION,
            true
        );
        wp_localize_script( 'atmopress-admin', 'AtmoPressAdmin', array(
            'restUrl' => esc_url_raw( rest_url( 'atmopress/v1/' ) ),
            'nonce'   => wp_create_nonce( 'wp_rest' ),
            'i18n'    => array(
                'testSuccess' => __( 'API key is valid! Test location loaded.', 'atmopress-weather' ),
                'testFail'    => __( 'API key test failed. Please check your key.', 'atmopress-weather' ),
            ),
        ) );
    }

    public static function on_activate() {
        $defaults = array(
            'api_key'          => '',
            'api_provider'     => 'openweathermap',
            'units'            => 'metric',
            'cache_minutes'    => 30,
            'default_location' => 'London',
            'show_search'      => true,
            'show_geolocation' => true,
        );
        if ( false === get_option( ATMOPRESS_OPT ) ) {
            add_option( ATMOPRESS_OPT, $defaults );
        }
        flush_rewrite_rules();
    }

    public static function on_deactivate() {
        flush_rewrite_rules();
    }
}
