<?php
/**
 * Weather API Manager
 *
 * Handles all API interactions with OpenWeatherMap and WeatherAPI,
 * including current weather, forecast, and air quality data.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/API
 * @since      1.0.0
 */

namespace PearlWeather\API;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WeatherAPI
 *
 * Manages API requests, caching, and response parsing.
 *
 * @since 1.0.0
 */
class WeatherAPI {

    /**
     * OpenWeatherMap API endpoints.
     */
    const OWM_CURRENT_URL = 'https://api.openweathermap.org/data/2.5/weather';
    const OWM_FORECAST_URL = 'https://api.openweathermap.org/data/2.5/forecast';
    const OWM_AIR_POLLUTION_URL = 'https://api.openweathermap.org/data/2.5/air_pollution';

    /**
     * WeatherAPI endpoint.
     */
    const WEATHERAPI_URL = 'https://api.weatherapi.com/v1/forecast.json';

    /**
     * Cache expiration in seconds (default: 10 minutes).
     *
     * @var int
     */
    private $cache_expiration = 600;

    /**
     * API key for OpenWeatherMap.
     *
     * @var string
     */
    private $owm_api_key = '';

    /**
     * API key for WeatherAPI.
     *
     * @var string
     */
    private $weatherapi_key = '';

    /**
     * Constructor.
     */
    public function __construct() {
        $settings = get_option( 'pearl_weather_settings', array() );
        $this->owm_api_key = isset( $settings['api_key'] ) ? $settings['api_key'] : '';
        $this->weatherapi_key = isset( $settings['weather_api_key'] ) ? $settings['weather_api_key'] : '';
        $this->cache_expiration = apply_filters( 'pearl_weather_cache_expiration', $this->cache_expiration );
    }

    /**
     * Get current weather from OpenWeatherMap.
     *
     * @since 1.0.0
     * @param string|array $query      Location query (city name, coordinates, etc.).
     * @param string       $units      Units ('metric' or 'imperial').
     * @param bool         $skip_cache Whether to skip cache.
     * @param string       $lang       Language code.
     * @param string       $api_key    API key (optional).
     * @return array|false Weather data or false on error.
     */
    public function get_current_weather( $query, $units = 'metric', $skip_cache = false, $lang = 'en', $api_key = '' ) {
        $api_key = empty( $api_key ) ? $this->owm_api_key : $api_key;
        
        if ( empty( $api_key ) ) {
            return array(
                'code'    => 401,
                'message' => __( 'API key is required. Please add your OpenWeatherMap API key in settings.', 'pearl-weather' ),
            );
        }

        $url = $this->build_owm_url( self::OWM_CURRENT_URL, $query, $units, $lang, $api_key );
        $cache_key = $this->get_cache_key( 'current', $query, $units, $lang );
        
        return $this->make_request( $url, $cache_key, $skip_cache );
    }

    /**
     * Get hourly forecast from OpenWeatherMap.
     *
     * @since 1.0.0
     * @param string|array $query       Location query.
     * @param string       $units       Units ('metric' or 'imperial').
     * @param int          $hours       Number of forecast hours (max 40).
     * @param bool         $skip_cache  Whether to skip cache.
     * @param string       $lang        Language code.
     * @param string       $api_key     API key (optional).
     * @return array|false Forecast data or false on error.
     */
    public function get_hourly_forecast( $query, $units = 'metric', $hours = 8, $skip_cache = false, $lang = 'en', $api_key = '' ) {
        $api_key = empty( $api_key ) ? $this->owm_api_key : $api_key;
        
        if ( empty( $api_key ) ) {
            return false;
        }

        $url = $this->build_owm_url( self::OWM_FORECAST_URL, $query, $units, $lang, $api_key );
        $cache_key = $this->get_cache_key( 'forecast', $query, $units, $lang );
        
        $data = $this->make_request( $url, $cache_key, $skip_cache );
        
        if ( $data && isset( $data['list'] ) ) {
            // Limit to requested hours (each item is 3-hour interval).
            $items_per_day = 8; // 24 hours / 3 = 8 items per day.
            $limit = ceil( $hours / 3 );
            $data['list'] = array_slice( $data['list'], 0, $limit );
        }
        
        return $data;
    }

    /**
     * Get air quality data from OpenWeatherMap.
     *
     * @since 1.0.0
     * @param float  $lat         Latitude.
     * @param float  $lon         Longitude.
     * @param string $api_key     API key (optional).
     * @param bool   $skip_cache  Whether to skip cache.
     * @return array|false Air quality data or false on error.
     */
    public function get_air_quality( $lat, $lon, $api_key = '', $skip_cache = false ) {
        $api_key = empty( $api_key ) ? $this->owm_api_key : $api_key;
        
        if ( empty( $api_key ) ) {
            return false;
        }

        $url = add_query_arg( array(
            'lat'   => $lat,
            'lon'   => $lon,
            'appid' => $api_key,
        ), self::OWM_AIR_POLLUTION_URL );
        
        $cache_key = $this->get_cache_key( 'aqi', array( 'lat' => $lat, 'lon' => $lon ) );
        
        return $this->make_request( $url, $cache_key, $skip_cache );
    }

    /**
     * Get weather data from WeatherAPI.
     *
     * @since 1.0.0
     * @param string $query       Location query.
     * @param string $units       Units ('metric' or 'imperial').
     * @param int    $days        Number of forecast days (max 3).
     * @param bool   $skip_cache  Whether to skip cache.
     * @param string $lang        Language code.
     * @param string $api_key     API key (optional).
     * @return array|false Weather data or false on error.
     */
    public function get_weatherapi_data( $query, $units = 'metric', $days = 2, $skip_cache = false, $lang = 'en', $api_key = '' ) {
        $api_key = empty( $api_key ) ? $this->weatherapi_key : $api_key;
        
        if ( empty( $api_key ) ) {
            return array(
                'code'    => 401,
                'message' => __( 'API key is required. Please add your WeatherAPI key in settings.', 'pearl-weather' ),
            );
        }

        $url = add_query_arg( array(
            'key'   => $api_key,
            'q'     => $query,
            'days'  => min( $days, 3 ),
            'aqi'   => 'yes',
            'alerts' => 'no',
            'lang'  => $lang,
        ), self::WEATHERAPI_URL );
        
        // Add units parameter.
        if ( 'metric' === $units ) {
            $url .= '&units=metric';
        } elseif ( 'imperial' === $units ) {
            $url .= '&units=imperial';
        }
        
        $cache_key = $this->get_cache_key( 'weatherapi', $query, $units, $lang );
        
        return $this->make_request( $url, $cache_key, $skip_cache );
    }

    /**
     * Build OpenWeatherMap API URL.
     *
     * @since 1.0.0
     * @param string       $base_url Base API URL.
     * @param string|array $query    Location query.
     * @param string       $units    Units.
     * @param string       $lang     Language.
     * @param string       $api_key  API key.
     * @return string
     */
    private function build_owm_url( $base_url, $query, $units, $lang, $api_key ) {
        $params = $this->build_query_params( $query );
        $params['units'] = $units;
        $params['lang'] = $lang;
        $params['appid'] = $api_key;
        
        return add_query_arg( $params, $base_url );
    }

    /**
     * Build query parameters for API request.
     *
     * @since 1.0.0
     * @param string|array $query Location query.
     * @return array
     */
    private function build_query_params( $query ) {
        if ( is_array( $query ) ) {
            if ( isset( $query['lat'] ) && isset( $query['lon'] ) ) {
                return array(
                    'lat' => $query['lat'],
                    'lon' => $query['lon'],
                );
            } elseif ( isset( $query['id'] ) ) {
                return array( 'id' => $query['id'] );
            } elseif ( isset( $query['zip'] ) ) {
                return array( 'zip' => $query['zip'] );
            }
        }
        
        if ( is_numeric( $query ) ) {
            return array( 'id' => $query );
        }
        
        // Check if it's a ZIP code format (e.g., "90210" or "90210,US").
        if ( is_string( $query ) && preg_match( '/^\d{5}(,\w{2})?$/', $query ) ) {
            return array( 'zip' => $query );
        }
        
        return array( 'q' => $query );
    }

    /**
     * Make an API request with caching.
     *
     * @since 1.0.0
     * @param string $url         Request URL.
     * @param string $cache_key   Cache key.
     * @param bool   $skip_cache  Whether to skip cache.
     * @return array|false
     */
    private function make_request( $url, $cache_key, $skip_cache = false ) {
        // Try to get from cache.
        if ( ! $skip_cache ) {
            $cached = $this->get_transient( $cache_key );
            if ( false !== $cached ) {
                return $cached;
            }
        }
        
        // Make request.
        $response = wp_remote_get( $url, array(
            'timeout'   => 15,
            'sslverify' => true,
        ) );
        
        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        
        if ( 200 !== $status_code ) {
            return $this->parse_error_response( $body, $status_code );
        }
        
        $data = json_decode( $body, true );
        
        if ( ! is_array( $data ) ) {
            return false;
        }
        
        // Cache the response.
        $this->set_transient( $cache_key, $data );
        
        return $data;
    }

    /**
     * Parse error response.
     *
     * @since 1.0.0
     * @param string $body        Response body.
     * @param int    $status_code HTTP status code.
     * @return array
     */
    private function parse_error_response( $body, $status_code ) {
        $error = json_decode( $body, true );
        
        if ( isset( $error['message'] ) ) {
            return array(
                'code'    => $status_code,
                'message' => $error['message'],
            );
        }
        
        return array(
            'code'    => $status_code,
            'message' => sprintf( __( 'API request failed with status code %d', 'pearl-weather' ), $status_code ),
        );
    }

    /**
     * Generate cache key for a request.
     *
     * @since 1.0.0
     * @param string $type  Request type.
     * @param mixed  ...$args Additional arguments.
     * @return string
     */
    private function get_cache_key( $type, ...$args ) {
        $key = 'pw_weather_' . $type . '_' . md5( serialize( $args ) );
        
        if ( is_multisite() ) {
            $key .= '_' . get_current_blog_id();
        }
        
        return $key;
    }

    /**
     * Set transient with multisite support.
     *
     * @since 1.0.0
     * @param string $key   Cache key.
     * @param mixed  $data  Data to cache.
     */
    private function set_transient( $key, $data ) {
        if ( is_multisite() ) {
            set_site_transient( $key, $data, $this->cache_expiration );
        } else {
            set_transient( $key, $data, $this->cache_expiration );
        }
    }

    /**
     * Get transient with multisite support.
     *
     * @since 1.0.0
     * @param string $key Cache key.
     * @return mixed
     */
    private function get_transient( $key ) {
        if ( is_multisite() ) {
            return get_site_transient( $key );
        }
        return get_transient( $key );
    }

    /**
     * Delete transient cache.
     *
     * @since 1.0.0
     * @param string $key Cache key.
     */
    public function delete_cache( $key ) {
        if ( is_multisite() ) {
            delete_site_transient( $key );
        } else {
            delete_transient( $key );
        }
    }

    /**
     * Clear all weather caches.
     *
     * @since 1.0.0
     */
    public function clear_all_cache() {
        global $wpdb;
        
        $pattern = 'pw_weather_%';
        
        if ( is_multisite() ) {
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s",
                '_site_transient_' . $pattern
            ) );
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s",
                '_site_transient_timeout_' . $pattern
            ) );
        } else {
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . $pattern
            ) );
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_timeout_' . $pattern
            ) );
        }
    }
}