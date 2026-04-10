<?php
namespace AtmoPress;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Settings {

    private static $cache = null;

    public static function get_all() {
        if ( null === self::$cache ) {
            self::$cache = wp_parse_args(
                (array) get_option( ATMOPRESS_OPT, array() ),
                self::defaults()
            );
        }
        return self::$cache;
    }

    public static function get( $key, $fallback = null ) {
        $all = self::get_all();
        return isset( $all[ $key ] ) ? $all[ $key ] : $fallback;
    }

    public static function save( $data ) {
        self::$cache = null;
        return update_option( ATMOPRESS_OPT, self::sanitize( $data ) );
    }

    public static function defaults() {
        return array(
            'api_key'          => '',
            'api_provider'     => 'openweathermap',
            'units'            => 'metric',
            'cache_minutes'    => 30,
            'default_location' => 'London',
            'show_search'      => true,
            'show_geolocation' => true,
        );
    }

    public static function sanitize( $raw ) {
        $clean = array();
        $clean['api_key']          = sanitize_text_field( $raw['api_key'] ?? '' );
        $clean['api_provider']     = in_array( $raw['api_provider'] ?? '', array( 'openweathermap', 'weatherapi' ), true ) ? $raw['api_provider'] : 'openweathermap';
        $clean['units']            = in_array( $raw['units'] ?? '', array( 'metric', 'imperial' ), true ) ? $raw['units'] : 'metric';
        $clean['cache_minutes']    = max( 0, absint( $raw['cache_minutes'] ?? 30 ) );
        $clean['default_location'] = sanitize_text_field( $raw['default_location'] ?? 'London' );
        $clean['show_search']      = ! empty( $raw['show_search'] );
        $clean['show_geolocation'] = ! empty( $raw['show_geolocation'] );
        return $clean;
    }
}
