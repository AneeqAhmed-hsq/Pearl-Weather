<?php
/**
 * Weather API Data Processor
 *
 * Handles API requests for weather data, caching, and data formatting
 * for both OpenWeatherMap and WeatherAPI services.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Blocks
 * @since      1.0.0
 */

namespace PearlWeather\Blocks;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PearlWeather\API\WeatherAPI;
use PearlWeather\Helpers;

/**
 * Class WeatherDataProcessor
 *
 * Processes weather API requests and formats data for display.
 *
 * @since 1.0.0
 */
class WeatherDataProcessor {

    /**
     * Default demo API key for OpenWeatherMap (limited to 20 calls).
     */
    const DEMO_OPENWEATHER_KEY = '';

    /**
     * Default demo API key for WeatherAPI (limited to 20 calls).
     */
    const DEMO_WEATHERAPI_KEY = '';

    /**
     * Maximum demo API calls allowed.
     */
    const MAX_DEMO_CALLS = 20;

    /**
     * Plugin settings.
     *
     * @var array
     */
    private $settings = array();

    /**
     * Block attributes.
     *
     * @var array
     */
    private $attributes = array();

    /**
     * Error message if any.
     *
     * @var string|false
     */
    private $error_message = false;

    /**
     * Weather data object.
     *
     * @var object|null
     */
    private $weather_data = null;

    /**
     * Forecast data.
     *
     * @var array|null
     */
    private $forecast_data = null;

    /**
     * AQI data.
     *
     * @var array|null
     */
    private $aqi_data = null;

    /**
     * Constructor.
     *
     * @param array $attributes Block attributes.
     * @param bool  $skip_cache Whether to skip cache.
     */
    public function __construct( $attributes, $skip_cache = false ) {
        $this->attributes = $attributes;
        $this->settings   = get_option( 'pearl_weather_settings', array() );
        $this->process( $skip_cache );
    }

    /**
     * Main processing method.
     *
     * @param bool $skip_cache Whether to skip cache.
     */
    private function process( $skip_cache = false ) {
        // Check for excluded block types.
        if ( $this->is_excluded_block() ) {
            return;
        }

        // Get API configuration.
        $api_config = $this->get_api_configuration();
        
        if ( ! $api_config['appid'] ) {
            $this->error_message = $this->get_api_key_error_message();
            return;
        }

        // Get location query.
        $query = $this->get_location_query();
        
        if ( ! $query ) {
            return;
        }

        // Fetch weather data.
        $this->fetch_weather_data( $query, $api_config, $skip_cache );
    }

    /**
     * Check if current block type should be excluded.
     *
     * @return bool
     */
    private function is_excluded_block() {
        $block_name = $this->attributes['blockName'] ?? '';
        $excluded_blocks = array( 'owm-map', 'windy-map' );
        return in_array( $block_name, $excluded_blocks, true );
    }

    /**
     * Get API configuration including key and source type.
     *
     * @return array
     */
    private function get_api_configuration() {
        $api_source = $this->attributes['lw_api_type'] ?? 'openweather_api';
        $appid = '';
        $default_calls_key = '';
        $transient_key = '';

        if ( 'openweather_api' === $api_source ) {
            $appid = $this->settings['api_key'] ?? '';
            $default_calls_key = 'pearl_weather_default_openweather_calls';
            $transient_key = 'pearl_weather_data_' . md5( wp_json_encode( $this->attributes ) );
            
            if ( empty( $appid ) ) {
                $default_calls = (int) get_option( $default_calls_key, 0 );
                if ( $default_calls < self::MAX_DEMO_CALLS ) {
                    $appid = self::DEMO_OPENWEATHER_KEY;
                    $this->increment_demo_call_count( $default_calls_key, $default_calls );
                }
            }
        } else {
            $appid = $this->settings['weather_api_key'] ?? '';
            $default_calls_key = 'pearl_weather_default_weatherapi_calls';
            $transient_key = 'pearl_weather_weatherapi_data_' . md5( wp_json_encode( $this->attributes ) );
            
            if ( empty( $appid ) ) {
                $default_calls = (int) get_option( $default_calls_key, 0 );
                if ( $default_calls < self::MAX_DEMO_CALLS ) {
                    $appid = self::DEMO_WEATHERAPI_KEY;
                    $this->increment_demo_call_count( $default_calls_key, $default_calls );
                }
            }
        }

        return array(
            'appid'               => $appid,
            'api_source'          => $api_source,
            'default_calls_key'   => $default_calls_key,
            'transient_key'       => $transient_key,
            'openweather_api_type' => $this->attributes['openweather_api_type'] ?? 'free',
        );
    }

    /**
     * Increment demo API call count.
     *
     * @param string $key    Option key.
     * @param int    $calls  Current call count.
     */
    private function increment_demo_call_count( $key, $calls ) {
        update_option( $key, $calls + 1 );
    }

    /**
     * Get API key error message.
     *
     * @return string
     */
    private function get_api_key_error_message() {
        $settings_url = admin_url( 'admin.php?page=pearl-weather-settings' );
        return sprintf(
            /* translators: %s: settings page URL */
            esc_html__( 'Please set your <a href="%s" target="_blank">API key</a> to display weather data.', 'pearl-weather' ),
            esc_url( $settings_url )
        );
    }

    /**
     * Get location query based on search method.
     *
     * @return string|array|null
     */
    private function get_location_query() {
        $weather_by = $this->attributes['searchWeatherBy'] ?? 'city_name';
        
        switch ( $weather_by ) {
            case 'city_name':
                $city_name = $this->attributes['getDataByCityName'] ?? 'London, GB';
                return ! empty( $city_name ) ? trim( $city_name ) : 'London, GB';
                
            case 'city_id':
                $city_id = $this->attributes['getDataByCityID'] ?? '2643743';
                return ! empty( $city_id ) ? str_replace( ' ', '', $city_id ) : '2643743';
                
            case 'latlong':
                $coordinates = $this->attributes['getDataByCoordinates'] ?? '51.509865,-0.118092';
                $parsed = $this->validate_coordinates( $coordinates );
                if ( isset( $parsed['error'] ) ) {
                    $this->error_message = $parsed['error'];
                    return null;
                }
                return $parsed['query'];
                
            case 'zip':
                $zip_code = $this->attributes['getDataByZIPCode'] ?? '77070,US';
                return ! empty( $zip_code ) ? str_replace( ' ', '', $zip_code ) : '77070,US';
                
            default:
                return 'London, GB';
        }
    }

    /**
     * Validate and parse coordinates.
     *
     * @param string $coordinates Coordinates string (lat,lng).
     * @return array
     */
    private function validate_coordinates( $coordinates ) {
        $coordinates = str_replace( ' ', '', $coordinates );
        $parts = explode( ',', $coordinates );
        
        if ( count( $parts ) !== 2 ) {
            return array(
                'error' => esc_html__( 'Invalid coordinates format. Please use lat,lng format.', 'pearl-weather' ),
            );
        }
        
        $lat = (float) $parts[0];
        $lng = (float) $parts[1];
        
        if ( $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 ) {
            return array(
                'error' => esc_html__( 'Invalid coordinates range.', 'pearl-weather' ),
            );
        }
        
        return array(
            'query' => array( 'lat' => $lat, 'lon' => $lng ),
        );
    }

    /**
     * Fetch weather data from API.
     *
     * @param string|array $query      Location query.
     * @param array        $api_config API configuration.
     * @param bool         $skip_cache Whether to skip cache.
     */
    private function fetch_weather_data( $query, $api_config, $skip_cache = false ) {
        $api_source = $api_config['api_source'];
        
        if ( 'openweather_api' === $api_source ) {
            $this->fetch_openweather_data( $query, $api_config, $skip_cache );
        } else {
            $this->fetch_weatherapi_data( $query, $api_config, $skip_cache );
        }
    }

    /**
     * Fetch data from OpenWeatherMap API.
     *
     * @param string|array $query      Location query.
     * @param array        $api_config API configuration.
     * @param bool         $skip_cache Whether to skip cache.
     */
    private function fetch_openweather_data( $query, $api_config, $skip_cache ) {
        $weather_units = $this->attributes['displayTemperatureUnit'] ?? 'metric';
        $language = $this->attributes['splwLanguage'] ?? 'en';
        $appid = $api_config['appid'];
        
        // Get weather data.
        $weather_api = new WeatherAPI();
        $data = $weather_api->get_current_weather( $query, $weather_units, $skip_cache, $language, $appid );
        
        if ( isset( $data['code'] ) && in_array( $data['code'], array( 401, 404 ), true ) ) {
            $this->error_message = $data['message'] ?? esc_html__( 'Weather data unavailable.', 'pearl-weather' );
            return;
        }
        
        // Get forecast data.
        $show_forecast = $this->attributes['displayWeatherForecastData'] ?? true;
        if ( $show_forecast ) {
            $this->fetch_openweather_forecast( $query, $weather_units, $language, $appid, $skip_cache );
        }
        
        // Get AQI data if applicable.
        $show_air_pollution = $this->attributes['displayAirPollutionData'] ?? true;
        $weather_by = $this->attributes['searchWeatherBy'] ?? 'city_name';
        
        if ( $show_air_pollution && 'latlong' === $weather_by && is_array( $query ) ) {
            $this->aqi_data = $weather_api->get_air_quality( $query['lat'], $query['lon'], $appid, $skip_cache );
        }
        
        // Format current weather data.
        $this->weather_data = $this->format_current_weather( $data, $weather_units );
    }

    /**
     * Fetch forecast data from OpenWeatherMap.
     *
     * @param string|array $query         Location query.
     * @param string       $weather_units Temperature units.
     * @param string       $language      Language code.
     * @param string       $appid         API key.
     * @param bool         $skip_cache    Whether to skip cache.
     */
    private function fetch_openweather_forecast( $query, $weather_units, $language, $appid, $skip_cache ) {
        $weather_api = new WeatherAPI();
        $forecast_hours = (int) ( $this->attributes['numberOfForecastHours'] ?? 8 );
        $forecast_hours = min( $forecast_hours, 8 ); // Limit to 8 hours.
        
        $forecast = $weather_api->get_hourly_forecast( $query, $weather_units, $forecast_hours, $skip_cache, $language, $appid );
        
        if ( is_object( $forecast ) && isset( $forecast->hourly_forecast ) ) {
            $this->forecast_data = $forecast->hourly_forecast;
        }
    }

    /**
     * Fetch data from WeatherAPI.
     *
     * @param string|array $query      Location query.
     * @param array        $api_config API configuration.
     * @param bool         $skip_cache Whether to skip cache.
     */
    private function fetch_weatherapi_data( $query, $api_config, $skip_cache ) {
        $weather_units = $this->attributes['displayTemperatureUnit'] ?? 'metric';
        $language = $this->attributes['splwLanguage'] ?? 'en';
        $appid = $api_config['appid'];
        $forecast_hours = (int) ( $this->attributes['numberOfForecastHours'] ?? 8 );
        
        $weather_api = new WeatherAPI();
        $api_query = is_array( $query ) ? implode( ',', $query ) : $query;
        
        $data = $weather_api->get_weatherapi_data( $api_query, $weather_units, $forecast_hours, $skip_cache, $language, $appid );
        
        if ( isset( $data['code'] ) && in_array( $data['code'], array( 1006, 2006, 404 ), true ) ) {
            $this->error_message = $data['message'] ?? esc_html__( 'Weather data unavailable.', 'pearl-weather' );
            return;
        }
        
        $this->weather_data = $this->format_current_weather( $data['current'] ?? array(), $weather_units );
        $this->forecast_data = $data['forecast'] ?? null;
    }

    /**
     * Format current weather data.
     *
     * @param object|array $data          Raw weather data.
     * @param string       $weather_units Temperature units.
     * @return array
     */
    private function format_current_weather( $data, $weather_units ) {
        // Convert to array if object.
        $data = is_object( $data ) ? (array) $data : $data;
        
        $temperature_unit = Helpers::get_temperature_symbol( $weather_units );
        $wind_unit = Helpers::get_wind_speed_unit( $weather_units );
        
        return array(
            'temperature'      => isset( $data['temp'] ) ? round( $data['temp'] ) : ( isset( $data['temp_c'] ) ? round( $data['temp_c'] ) : '--' ),
            'temperature_unit' => $temperature_unit,
            'feels_like'       => isset( $data['feels_like'] ) ? round( $data['feels_like'] ) : ( isset( $data['feelslike_c'] ) ? round( $data['feelslike_c'] ) : '--' ),
            'humidity'         => isset( $data['humidity'] ) ? $data['humidity'] : '--',
            'humidity_unit'    => '%',
            'pressure'         => isset( $data['pressure'] ) ? $data['pressure'] : '--',
            'pressure_unit'    => 'hPa',
            'wind_speed'       => isset( $data['wind_speed'] ) ? $data['wind_speed'] : ( isset( $data['wind_kph'] ) ? round( $data['wind_kph'] / 3.6, 1 ) : '--' ),
            'wind_unit'        => $wind_unit,
            'wind_direction'   => isset( $data['wind_deg'] ) ? Helpers::degrees_to_cardinal( $data['wind_deg'] ) : ( isset( $data['wind_dir'] ) ? $data['wind_dir'] : '--' ),
            'clouds'           => isset( $data['clouds'] ) ? $data['clouds'] : ( isset( $data['cloud'] ) ? $data['cloud'] : '--' ),
            'clouds_unit'      => '%',
            'visibility'       => isset( $data['visibility'] ) ? $this->format_visibility( $data['visibility'] ) : '--',
            'visibility_unit'  => 'km',
            'weather_icon'     => isset( $data['weather'][0]['icon'] ) ? $data['weather'][0]['icon'] : ( isset( $data['condition']['icon'] ) ? $data['condition']['icon'] : '' ),
            'weather_main'     => isset( $data['weather'][0]['main'] ) ? $data['weather'][0]['main'] : ( isset( $data['condition']['text'] ) ? $data['condition']['text'] : '' ),
            'weather_desc'     => isset( $data['weather'][0]['description'] ) ? $data['weather'][0]['description'] : ( isset( $data['condition']['text'] ) ? $data['condition']['text'] : '' ),
            'sunrise'          => isset( $data['sys']['sunrise'] ) ? $data['sys']['sunrise'] : ( isset( $data['astro']['sunrise'] ) ? $data['astro']['sunrise'] : '' ),
            'sunset'           => isset( $data['sys']['sunset'] ) ? $data['sys']['sunset'] : ( isset( $data['astro']['sunset'] ) ? $data['astro']['sunset'] : '' ),
            'country'          => isset( $data['sys']['country'] ) ? $data['sys']['country'] : '',
            'city_name'        => isset( $data['name'] ) ? $data['name'] : ( isset( $data['location']['name'] ) ? $data['location']['name'] : '' ),
        );
    }

    /**
     * Format visibility from meters to kilometers.
     *
     * @param int $visibility_meters Visibility in meters.
     * @return string
     */
    private function format_visibility( $visibility_meters ) {
        if ( $visibility_meters >= 1000 ) {
            return round( $visibility_meters / 1000, 1 );
        }
        return $visibility_meters;
    }

    /**
     * Get weather item label.
     *
     * @param string $key Label key.
     * @return string
     */
    public static function get_item_label( $key ) {
        $labels = array(
            'temperature'             => __( 'Temperature', 'pearl-weather' ),
            'pressure'                => __( 'Pressure', 'pearl-weather' ),
            'humidity'                => __( 'Humidity', 'pearl-weather' ),
            'wind'                    => __( 'Wind', 'pearl-weather' ),
            'wind_speed'              => __( 'Wind Speed', 'pearl-weather' ),
            'wind_direction'          => __( 'Wind Direction', 'pearl-weather' ),
            'precipitation'           => __( 'Precipitation', 'pearl-weather' ),
            'clouds'                  => __( 'Clouds', 'pearl-weather' ),
            'rain_chance'             => __( 'Rain Chance', 'pearl-weather' ),
            'snow'                    => __( 'Snow', 'pearl-weather' ),
            'gust'                    => __( 'Wind Gust', 'pearl-weather' ),
            'uv_index'                => __( 'UV Index', 'pearl-weather' ),
            'dew_point'               => __( 'Dew Point', 'pearl-weather' ),
            'air_quality'             => __( 'Air Quality', 'pearl-weather' ),
            'visibility'              => __( 'Visibility', 'pearl-weather' ),
            'sunrise'                 => __( 'Sunrise', 'pearl-weather' ),
            'sunset'                  => __( 'Sunset', 'pearl-weather' ),
            'moonrise'                => __( 'Moonrise', 'pearl-weather' ),
            'moonset'                 => __( 'Moonset', 'pearl-weather' ),
            'moon_phase'              => __( 'Moon Phase', 'pearl-weather' ),
            'national_alerts'         => __( 'National Alerts', 'pearl-weather' ),
            'date'                    => __( 'Date', 'pearl-weather' ),
            'day'                     => __( 'Day', 'pearl-weather' ),
            'hour'                    => __( 'Hour', 'pearl-weather' ),
            'condition'               => __( 'Condition', 'pearl-weather' ),
            'amount'                  => __( 'Amount', 'pearl-weather' ),
        );
        
        $key = strtolower( str_replace( ' ', '_', $key ) );
        
        return isset( $labels[ $key ] ) ? $labels[ $key ] : ucfirst( str_replace( '_', ' ', $key ) );
    }

    /**
     * Get processed weather data.
     *
     * @return array
     */
    public function get_weather_data() {
        return $this->weather_data;
    }

    /**
     * Get forecast data.
     *
     * @return array|null
     */
    public function get_forecast_data() {
        return $this->forecast_data;
    }

    /**
     * Get AQI data.
     *
     * @return array|null
     */
    public function get_aqi_data() {
        return $this->aqi_data;
    }

    /**
     * Get error message.
     *
     * @return string|false
     */
    public function get_error_message() {
        return $this->error_message;
    }

    /**
     * Check if there was an error.
     *
     * @return bool
     */
    public function has_error() {
        return false !== $this->error_message;
    }
}

// Helper function for template files.
if ( ! function_exists( 'pearl_weather_get_item_label' ) ) {
    /**
     * Get weather item label.
     *
     * @param string $key Label key.
     * @return string
     */
    function pearl_weather_get_item_label( $key ) {
        return WeatherDataProcessor::get_item_label( $key );
    }
}