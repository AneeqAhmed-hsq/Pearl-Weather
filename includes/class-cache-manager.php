<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pearl_Weather_Cache_Manager {

    private $cache_duration;

    public function __construct() {
        $settings             = get_option( 'pearl_weather_settings', array() );
        $this->cache_duration = isset( $settings['cache_duration'] ) ? absint( $settings['cache_duration'] ) : 600;
    }

    public function get( $key ) {
        return get_transient( 'pearl_weather_' . md5( $key ) );
    }

    public function set( $key, $data ) {
        return set_transient( 'pearl_weather_' . md5( $key ), $data, $this->cache_duration );
    }

    public function delete( $key ) {
        return delete_transient( 'pearl_weather_' . md5( $key ) );
    }

    public function flush_all() {
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pearl_weather_%' OR option_name LIKE '_transient_timeout_pearl_weather_%'"
        );
    }
}
