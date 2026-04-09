<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pearl_Weather_Assets_Loader {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'pearl-weather-public',
            PEARL_WEATHER_ASSETS_URL . 'css/style.css',
            array(),
            PEARL_WEATHER_VERSION
        );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'pearl-weather' ) === false ) {
            return;
        }
        wp_enqueue_style(
            'pearl-weather-admin',
            PEARL_WEATHER_ASSETS_URL . 'css/admin.css',
            array(),
            PEARL_WEATHER_VERSION
        );
    }
}

new Pearl_Weather_Assets_Loader();
