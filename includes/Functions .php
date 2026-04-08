<?php
/**
 * Helper Functions
 *
 * Core helper functions for the Pearl Weather plugin.
 * Provides reusable utilities and capability management.
 *
 * @package    PearlWeather
 * @since      1.0.0
 */

namespace PearlWeather;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Helpers
 *
 * Static helper functions for the plugin.
 *
 * @since 1.0.0
 */
class Helpers {

    /**
     * Default capability required for plugin admin access.
     *
     * @var string
     */
    const DEFAULT_ADMIN_CAPABILITY = 'manage_options';

    /**
     * Get the capability required to access plugin dashboard.
     *
     * This function returns the user capability needed to view
     * and manage the plugin's admin pages. Can be filtered to
     * allow different user roles access.
     *
     * @since 1.0.0
     *
     * @return string The capability name.
     */
    public static function get_admin_capability() {
        /**
         * Filter the capability required to access Pearl Weather admin pages.
         *
         * @since 1.0.0
         *
         * @param string $capability The capability name. Default 'manage_options'.
         */
        return apply_filters(
            'pearl_weather_admin_capability',
            self::DEFAULT_ADMIN_CAPABILITY
        );
    }

    /**
     * Check if current user has plugin admin access.
     *
     * @since 1.0.0
     *
     * @return bool True if user has access, false otherwise.
     */
    public static function current_user_can_access() {
        $capability = self::get_admin_capability();
        return current_user_can( $capability );
    }

    /**
     * Get the capability required for API operations.
     *
     * @since 1.0.0
     *
     * @return string The capability name.
     */
    public static function get_api_capability() {
        /**
         * Filter the capability required for API operations.
         *
         * @since 1.0.0
         *
         * @param string $capability The capability name. Default 'manage_options'.
         */
        return apply_filters(
            'pearl_weather_api_capability',
            self::DEFAULT_ADMIN_CAPABILITY
        );
    }

    /**
     * Get the capability required for settings management.
     *
     * @since 1.0.0
     *
     * @return string The capability name.
     */
    public static function get_settings_capability() {
        /**
         * Filter the capability required for settings management.
         *
         * @since 1.0.0
         *
         * @param string $capability The capability name. Default 'manage_options'.
         */
        return apply_filters(
            'pearl_weather_settings_capability',
            self::DEFAULT_ADMIN_CAPABILITY
        );
    }

    /**
     * Get default cache expiration time in seconds.
     *
     * @since 1.0.0
     *
     * @return int Cache expiration in seconds.
     */
    public static function get_default_cache_expiration() {
        $settings = get_option( 'pearl_weather_settings', array() );
        
        $cache_time = isset( $settings['cache_duration'] ) 
            ? absint( $settings['cache_duration'] ) 
            : 600; // 10 minutes default.
        
        // Ensure minimum cache time of 5 minutes.
        $cache_time = max( 300, $cache_time );
        
        /**
         * Filter the cache expiration time.
         *
         * @since 1.0.0
         *
         * @param int $cache_time Cache expiration in seconds.
         */
        return apply_filters( 'pearl_weather_cache_expiration', $cache_time );
    }

    /**
     * Get the OpenWeatherMap API URL.
     *
     * @since 1.0.0
     *
     * @return string API base URL.
     */
    public static function get_api_base_url() {
        $api_url = 'https://api.openweathermap.org/data/2.5/';
        
        /**
         * Filter the API base URL.
         *
         * @since 1.0.0
         *
         * @param string $api_url The API base URL.
         */
        return apply_filters( 'pearl_weather_api_base_url', $api_url );
    }

    /**
     * Sanitize a location string.
     *
     * @since 1.0.0
     *
     * @param string $location The location to sanitize.
     * @return string Sanitized location.
     */
    public static function sanitize_location( $location ) {
        // Remove HTML tags and trim.
        $location = wp_strip_all_tags( trim( $location ) );
        
        // Allow letters, numbers, spaces, commas, hyphens, and periods.
        $location = preg_replace( '/[^a-zA-Z0-9\s\,\-\.]/', '', $location );
        
        // Limit length.
        $location = substr( $location, 0, 100 );
        
        return $location;
    }

    /**
     * Get temperature unit symbol.
     *
     * @since 1.0.0
     *
     * @param string $unit The unit ('metric' or 'imperial').
     * @return string Unit symbol.
     */
    public static function get_temperature_symbol( $unit = 'metric' ) {
        $symbols = array(
            'metric'    => '°C',
            'imperial'  => '°F',
            'standard'  => 'K',
        );
        
        $symbol = isset( $symbols[ $unit ] ) ? $symbols[ $unit ] : '°C';
        
        /**
         * Filter the temperature unit symbol.
         *
         * @since 1.0.0
         *
         * @param string $symbol The temperature symbol.
         * @param string $unit   The unit type.
         */
        return apply_filters( 'pearl_weather_temperature_symbol', $symbol, $unit );
    }

    /**
     * Get wind speed unit.
     *
     * @since 1.0.0
     *
     * @param string $unit The unit ('metric' or 'imperial').
     * @return string Wind speed unit.
     */
    public static function get_wind_speed_unit( $unit = 'metric' ) {
        $units = array(
            'metric'    => 'm/s',
            'imperial'  => 'mph',
            'standard'  => 'm/s',
        );
        
        $wind_unit = isset( $units[ $unit ] ) ? $units[ $unit ] : 'm/s';
        
        /**
         * Filter the wind speed unit.
         *
         * @since 1.0.0
         *
         * @param string $wind_unit The wind speed unit.
         * @param string $unit      The unit type.
         */
        return apply_filters( 'pearl_weather_wind_speed_unit', $wind_unit, $unit );
    }

    /**
     * Get weather icon URL from OpenWeatherMap.
     *
     * @since 1.0.0
     *
     * @param string $icon_code The icon code from API.
     * @return string Full icon URL.
     */
    public static function get_weather_icon_url( $icon_code ) {
        if ( empty( $icon_code ) ) {
            return '';
        }
        
        $base_url = 'https://openweathermap.org/img/w/';
        $icon_url = $base_url . $icon_code . '.png';
        
        /**
         * Filter the weather icon URL.
         *
         * @since 1.0.0
         *
         * @param string $icon_url   The full icon URL.
         * @param string $icon_code  The icon code.
         */
        return apply_filters( 'pearl_weather_icon_url', $icon_url, $icon_code );
    }

    /**
     * Convert wind direction degrees to cardinal direction.
     *
     * @since 1.0.0
     *
     * @param int $degrees Wind direction in degrees.
     * @return string Cardinal direction (N, NE, E, etc.).
     */
    public static function degrees_to_cardinal( $degrees ) {
        $degrees = floatval( $degrees );
        
        $cardinals = array(
            'N'  => array( 348.75, 360 ),
            'N'  => array( 0, 11.25 ),
            'NNE' => array( 11.25, 33.75 ),
            'NE'  => array( 33.75, 56.25 ),
            'ENE' => array( 56.25, 78.75 ),
            'E'   => array( 78.75, 101.25 ),
            'ESE' => array( 101.25, 123.75 ),
            'SE'  => array( 123.75, 146.25 ),
            'SSE' => array( 146.25, 168.75 ),
            'S'   => array( 168.75, 191.25 ),
            'SSW' => array( 191.25, 213.75 ),
            'SW'  => array( 213.75, 236.25 ),
            'WSW' => array( 236.25, 258.75 ),
            'W'   => array( 258.75, 281.25 ),
            'WNW' => array( 281.25, 303.75 ),
            'NW'  => array( 303.75, 326.25 ),
            'NNW' => array( 326.25, 348.75 ),
        );
        
        foreach ( $cardinals as $direction => $range ) {
            if ( $degrees >= $range[0] && $degrees < $range[1] ) {
                return $direction;
            }
        }
        
        return 'N';
    }

    /**
     * Get user's current timezone.
     *
     * @since 1.0.0
     *
     * @return \DateTimeZone User's timezone.
     */
    public static function get_user_timezone() {
        $timezone_string = get_option( 'timezone_string' );
        
        if ( ! empty( $timezone_string ) ) {
            return new \DateTimeZone( $timezone_string );
        }
        
        $offset = get_option( 'gmt_offset' );
        $hours   = (int) $offset;
        $minutes = abs( ( $offset - $hours ) * 60 );
        
        $timezone_name = timezone_name_from_abbr( '', $hours * 3600 + $minutes * 60, false );
        
        if ( false !== $timezone_name ) {
            return new \DateTimeZone( $timezone_name );
        }
        
        return new \DateTimeZone( 'UTC' );
    }
}

// Global helper function for backward compatibility.
if ( ! function_exists( 'pearl_weather_dashboard_capability' ) ) {
    /**
     * Get the capability required to access plugin dashboard.
     * This is a wrapper for backward compatibility.
     *
     * @since 1.0.0
     * @return string
     */
    function pearl_weather_dashboard_capability() {
        return \PearlWeather\Helpers::get_admin_capability();
    }
}