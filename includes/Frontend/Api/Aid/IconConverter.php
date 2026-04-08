<?php
/**
 * Weather Icon Converter
 *
 * Maps weather condition codes to OpenWeatherMap-style icon identifiers,
 * supporting day/night variations and extensible mapping.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/API/Models
 * @since      1.0.0
 */

namespace PearlWeather\API\Models;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class IconConverter
 *
 * Converts weather condition codes to standardized icon identifiers.
 *
 * @since 1.0.0
 */
class IconConverter {

    /**
     * Weather condition mapping table.
     * Maps condition codes to OWM icon base codes.
     *
     * @var array
     */
    private static $condition_map = array(
        // Clear / Sunny.
        1000 => '01',  // Sunny/Clear sky.
        
        // Partly Cloudy.
        1003 => '02',  // Partly cloudy.
        
        // Cloudy.
        1006 => '03',  // Cloudy.
        1009 => '04',  // Overcast.
        
        // Mist / Fog.
        1030 => '50',  // Mist.
        1135 => '50',  // Fog.
        1147 => '50',  // Freezing fog.
        
        // Drizzle / Light Rain.
        1063 => '09',  // Patchy rain possible.
        1150 => '09',  // Patchy light drizzle.
        1153 => '09',  // Light drizzle.
        1180 => '09',  // Light rain.
        1183 => '09',  // Light rain shower.
        
        // Moderate / Heavy Rain.
        1186 => '10',  // Moderate rain.
        1189 => '10',  // Heavy rain.
        1192 => '10',  // Moderate rain shower.
        1195 => '10',  // Heavy rain shower.
        
        // Freezing Rain.
        1198 => '13',  // Light freezing rain.
        1201 => '13',  // Heavy freezing rain.
        
        // Sleet.
        1066 => '13',  // Patchy sleet possible.
        1210 => '13',  // Light sleet.
        1213 => '13',  // Moderate sleet.
        1216 => '13',  // Heavy sleet.
        1219 => '13',  // Light sleet shower.
        1222 => '13',  // Moderate sleet shower.
        1225 => '13',  // Heavy sleet shower.
        
        // Snow.
        1066 => '13',  // Patchy snow possible.
        1210 => '13',  // Light snow.
        1213 => '13',  // Moderate snow.
        1216 => '13',  // Heavy snow.
        1219 => '13',  // Light snow shower.
        1222 => '13',  // Moderate snow shower.
        1225 => '13',  // Heavy snow shower.
        1237 => '13',  // Ice pellets.
        1249 => '13',  // Light showers of ice pellets.
        1252 => '13',  // Moderate showers of ice pellets.
        1255 => '13',  // Light snow grains.
        1258 => '13',  // Moderate snow grains.
        1261 => '13',  // Light snow showers.
        1264 => '13',  // Heavy snow showers.
        
        // Thunderstorm.
        1087 => '11',  // Thundery outbreaks possible.
        1273 => '11',  // Patchy light rain with thunder.
        1276 => '11',  // Moderate rain with thunder.
        1279 => '11',  // Patchy heavy snow with thunder.
        1282 => '11',  // Heavy snow with thunder.
    );

    /**
     * Default fallback icon.
     *
     * @var string
     */
    private static $default_icon = '01';

    /**
     * Get OpenWeatherMap-compatible icon code.
     *
     * @since 1.0.0
     * @param int  $code       Weather condition code.
     * @param bool $is_daytime Whether it's daytime (true) or nighttime (false).
     * @return string OWM icon code (e.g., '01d', '10n').
     */
    public static function get_owm_icon( $code, $is_daytime = true ) {
        $suffix = $is_daytime ? 'd' : 'n';
        $base_icon = self::get_base_icon( $code );
        
        return $base_icon . $suffix;
    }

    /**
     * Get the base icon code (without day/night suffix).
     *
     * @since 1.0.0
     * @param int $code Weather condition code.
     * @return string
     */
    public static function get_base_icon( $code ) {
        return isset( self::$condition_map[ $code ] ) 
            ? self::$condition_map[ $code ] 
            : self::$default_icon;
    }

    /**
     * Get the full icon code with suffix.
     *
     * @since 1.0.0
     * @param int    $code       Weather condition code.
     * @param string $time_of_day Time of day ('day', 'night', or auto).
     * @return string
     */
    public static function get_icon( $code, $time_of_day = 'auto' ) {
        $is_daytime = self::is_daytime( $time_of_day );
        return self::get_owm_icon( $code, $is_daytime );
    }

    /**
     * Determine if it's daytime.
     *
     * @since 1.0.0
     * @param string $time_of_day Time of day indicator ('day', 'night', 'auto').
     * @return bool
     */
    private static function is_daytime( $time_of_day ) {
        if ( 'day' === $time_of_day ) {
            return true;
        }
        
        if ( 'night' === $time_of_day ) {
            return false;
        }
        
        // Auto-detect based on current time (simplified).
        $hour = (int) current_time( 'G' );
        return ( $hour >= 6 && $hour < 18 );
    }

    /**
     * Get weather condition name from code.
     *
     * @since 1.0.0
     * @param int $code Weather condition code.
     * @return string
     */
    public static function get_condition_name( $code ) {
        $conditions = array(
            1000 => 'Clear sky',
            1003 => 'Partly cloudy',
            1006 => 'Cloudy',
            1009 => 'Overcast',
            1030 => 'Mist',
            1063 => 'Patchy rain possible',
            1066 => 'Patchy snow possible',
            1087 => 'Thundery outbreaks possible',
            1135 => 'Fog',
            1147 => 'Freezing fog',
            1150 => 'Patchy light drizzle',
            1153 => 'Light drizzle',
            1180 => 'Light rain',
            1183 => 'Light rain shower',
            1186 => 'Moderate rain',
            1189 => 'Heavy rain',
            1192 => 'Moderate rain shower',
            1195 => 'Heavy rain shower',
            1198 => 'Light freezing rain',
            1201 => 'Heavy freezing rain',
            1210 => 'Light sleet',
            1213 => 'Moderate sleet',
            1216 => 'Heavy sleet',
            1219 => 'Light sleet shower',
            1222 => 'Moderate sleet shower',
            1225 => 'Heavy sleet shower',
            1237 => 'Ice pellets',
            1249 => 'Light showers of ice pellets',
            1252 => 'Moderate showers of ice pellets',
            1255 => 'Light snow grains',
            1258 => 'Moderate snow grains',
            1261 => 'Light snow showers',
            1264 => 'Heavy snow showers',
            1273 => 'Patchy light rain with thunder',
            1276 => 'Moderate rain with thunder',
            1279 => 'Patchy heavy snow with thunder',
            1282 => 'Heavy snow with thunder',
        );
        
        return isset( $conditions[ $code ] ) ? $conditions[ $code ] : __( 'Unknown', 'pearl-weather' );
    }

    /**
     * Register a custom mapping.
     *
     * @since 1.0.0
     * @param int    $code        Weather condition code.
     * @param string $icon_base   Base icon code (without suffix).
     * @param string $condition_name Optional condition name.
     */
    public static function register_mapping( $code, $icon_base, $condition_name = null ) {
        self::$condition_map[ $code ] = $icon_base;
        
        if ( $condition_name ) {
            add_filter( 'pearl_weather_condition_name', function( $name, $c ) use ( $code, $condition_name ) {
                return $c == $code ? $condition_name : $name;
            }, 10, 2 );
        }
    }

    /**
     * Get all condition mappings.
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_mappings() {
        return self::$condition_map;
    }
}