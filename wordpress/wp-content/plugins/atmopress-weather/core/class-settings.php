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
            // API
            'api_key'             => '',
            'api_provider'        => 'openweathermap',

            // General
            'units'               => 'metric',
            'cache_duration'      => 600,
            'default_location'    => 'London',

            // Display toggles
            'show_search'         => true,
            'show_geolocation'    => true,
            'show_temperature'    => true,
            'show_humidity'       => true,
            'show_wind'           => true,
            'show_pressure'       => true,
            'show_visibility'     => true,
            'show_feels_like'     => true,
            'show_sunrise'        => true,
            'show_hourly'         => true,
            'show_daily'          => true,
            'forecast_days'       => 7,
            'hourly_count'        => 8,

            // Template & Layout
            'default_template'    => 'template-1',
            'default_layout'      => 'card',
            'icon_style'          => 'owm',
            'forecast_style'      => 'list',

            // Style
            'color_primary'       => '#2563eb',
            'color_bg'            => '#ffffff',
            'color_text'          => '#1e293b',
            'color_accent'        => '#06b6d4',
            'border_radius'       => 16,
            'card_spacing'        => 16,
            'font_size'           => 14,
            'font_family'         => 'inherit',

            // Advanced
            'custom_css'          => '',
            'disable_animations'  => false,
            'lazy_load'           => true,
        );
    }

    public static function sanitize( $raw ) {
        $raw   = (array) $raw;
        $clean = array();

        // API
        $clean['api_key']          = sanitize_text_field( $raw['api_key'] ?? '' );
        $clean['api_provider']     = in_array( $raw['api_provider'] ?? '', array( 'openweathermap', 'weatherapi' ), true ) ? $raw['api_provider'] : 'openweathermap';

        // General
        $clean['units']            = in_array( $raw['units'] ?? '', array( 'metric', 'imperial' ), true ) ? $raw['units'] : 'metric';
        $clean['cache_duration']   = max( 0, absint( $raw['cache_duration'] ?? 600 ) );
        $clean['default_location'] = sanitize_text_field( $raw['default_location'] ?? 'London' );

        // Display toggles
        foreach ( array( 'show_search', 'show_geolocation', 'show_temperature', 'show_humidity', 'show_wind', 'show_pressure', 'show_visibility', 'show_feels_like', 'show_sunrise', 'show_hourly', 'show_daily', 'disable_animations', 'lazy_load' ) as $bool ) {
            $clean[ $bool ] = ! empty( $raw[ $bool ] );
        }

        $clean['forecast_days']    = min( 7, max( 1, absint( $raw['forecast_days'] ?? 7 ) ) );
        $clean['hourly_count']     = min( 24, max( 1, absint( $raw['hourly_count'] ?? 8 ) ) );

        // Template & Layout
        $valid_tpl  = array_keys( TemplateLoader::registered() );
        $clean['default_template'] = in_array( $raw['default_template'] ?? '', $valid_tpl, true ) ? $raw['default_template'] : 'template-1';
        $clean['default_layout']   = in_array( $raw['default_layout'] ?? '', array( 'card', 'grid', 'minimal', 'horizontal' ), true ) ? $raw['default_layout'] : 'card';
        $clean['icon_style']       = in_array( $raw['icon_style'] ?? '', array( 'owm', 'flat', 'line' ), true ) ? $raw['icon_style'] : 'owm';
        $clean['forecast_style']   = in_array( $raw['forecast_style'] ?? '', array( 'list', 'grid', 'compact' ), true ) ? $raw['forecast_style'] : 'list';

        // Style
        $clean['color_primary']    = sanitize_hex_color( $raw['color_primary'] ?? '' ) ?: '#2563eb';
        $clean['color_bg']         = sanitize_hex_color( $raw['color_bg'] ?? '' )      ?: '#ffffff';
        $clean['color_text']       = sanitize_hex_color( $raw['color_text'] ?? '' )    ?: '#1e293b';
        $clean['color_accent']     = sanitize_hex_color( $raw['color_accent'] ?? '' )  ?: '#06b6d4';
        $clean['border_radius']    = min( 32, max( 0, absint( $raw['border_radius'] ?? 16 ) ) );
        $clean['card_spacing']     = min( 40, max( 0, absint( $raw['card_spacing'] ?? 16 ) ) );
        $clean['font_size']        = min( 24, max( 10, absint( $raw['font_size'] ?? 14 ) ) );
        $clean['font_family']      = sanitize_text_field( $raw['font_family'] ?? 'inherit' );
        $clean['custom_css']       = wp_strip_all_tags( $raw['custom_css'] ?? '' );

        return $clean;
    }
}
