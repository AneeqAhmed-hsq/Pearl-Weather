<?php
/**
 * Gutenberg Blocks Helper Functions
 *
 * Provides utility functions for weather blocks including data processing,
 * AQI calculations, sun position, coordinate validation, and formatting.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Blocks/Includes
 * @since      1.0.0
 */

namespace PearlWeather\Blocks\Includes;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PearlWeather\Helpers;

/**
 * Class BlockHelpers
 *
 * Static helper methods for Gutenberg blocks.
 *
 * @since 1.0.0
 */
class BlockHelpers {

    /**
     * Process additional data options and expand special cases.
     *
     * @since 1.0.0
     * @param array $options   Input options array.
     * @param bool  $skip_active Skip active status check.
     * @return array Processed options.
     */
    public static function process_additional_data_options( $options, $skip_active = false ) {
        $processed = array();

        foreach ( $options as $option ) {
            if ( ! $skip_active && empty( $option['isActive'] ) ) {
                continue;
            }

            $value = $option['value'] ?? '';

            // Expand sunrise/sunset into separate items.
            if ( 'sunriseSunset' === $value ) {
                $processed[] = 'sunrise';
                $processed[] = 'sunset';
            } else {
                $processed[] = $value;
            }
        }

        return $processed;
    }

    /**
     * Process forecast data options.
     *
     * @since 1.0.0
     * @param array $options Forecast options array.
     * @return array Active forecast items.
     */
    public static function process_forecast_options( $options ) {
        $active = array();

        foreach ( $options as $option ) {
            if ( ! empty( $option['value'] ) ) {
                $active[] = $option['name'];
            }
        }

        return $active;
    }

    /**
     * Format current weather data for display.
     *
     * @since 1.0.0
     * @param object $data     Weather data object.
     * @param array  $units    Measurement units.
     * @param array  $time     Time settings.
     * @param array  $aqi      Air quality data (optional).
     * @param bool   $is_editor Whether in editor context.
     * @return array Formatted weather data.
     */
    public static function format_current_weather( $data, $units, $time, $aqi = array(), $is_editor = false ) {
        if ( ! is_object( $data ) || ! isset( $data->city ) ) {
            return array();
        }

        // Temperature.
        $temp_scale = self::get_temperature_scale( $units['temperature_scale'], $units['weather_unit'] );
        $temperature = round( $data->temperature->now->value ?? 0 );

        // Wind and visibility.
        $visibility = self::format_visibility( $units['visibility_unit'], $data );
        $pressure   = self::format_pressure( $units['pressure_unit'], $data );
        $wind_speed = self::format_wind_speed( $units['weather_unit'], $units['wind_speed_unit'], $data, false );
        $wind_gust  = self::format_wind_speed( $units['weather_unit'], $units['wind_speed_unit'], $data, true );

        // Time formatting.
        $now = new \DateTime();
        $timezone_offset = $time['time_zone'] ?? 0;
        $weather_timezone = $time['weather_time_zone'] ?? 0;

        $formatted_time = date_i18n( $time['time_format'], strtotime( $now->format( 'Y-m-d H:i:s' ) ) + $timezone_offset );
        $formatted_date = date_i18n( $time['date_format'], strtotime( $now->format( 'Y-m-d H:i:s' ) ) + $timezone_offset );

        // Sun times.
        $sunrise = $data->sun->rise ?? null;
        $sunset  = $data->sun->set ?? null;
        
        $sunrise_time = $sunrise ? gmdate( $time['time_format'], strtotime( $sunrise->format( 'Y-m-d H:i:s' ) ) + $weather_timezone ) : '';
        $sunset_time  = $sunset ? gmdate( $time['time_format'], strtotime( $sunset->format( 'Y-m-d H:i:s' ) ) + $weather_timezone ) : '';

        // Last update.
        $last_update = $data->last_update ?? null;
        $updated_time = $last_update ? gmdate( $time['time_format'], strtotime( $last_update->format( 'Y-m-d H:i:s' ) ) + $weather_timezone ) : '';

        // Sun position angle.
        $sun_angle = self::calculate_sun_angle( array(
            'current_time' => $formatted_time,
            'sunrise'      => $sunrise_time,
            'sunset'       => $sunset_time,
        ) );

        return array(
            'city_id'      => $data->city->id ?? '',
            'city'         => $data->city->name ?? '',
            'country'      => $data->city->country ?? '',
            'temperature'  => $temperature,
            'temp_unit'    => $temp_scale,
            'pressure'     => $pressure,
            'humidity'     => ( $data->humidity->value ?? 0 ) . '%',
            'visibility'   => $visibility,
            'clouds'       => ( $data->clouds->value ?? 0 ) . '%',
            'description'  => $data->weather->description ?? '',
            'icon'         => $data->weather->icon ?? '',
            'time'         => $formatted_time,
            'date'         => $formatted_date,
            'timezone'     => $timezone_offset,
            'sunrise'      => $sunrise_time,
            'sunset'       => $sunset_time,
            'wind'         => $is_editor ? $data->wind : $wind_speed,
            'wind_gust'    => $is_editor ? $data->gusts : $wind_gust,
            'updated_time' => $updated_time,
            'sun_angle'    => $sun_angle,
        );
    }

    /**
     * Get temperature scale symbol.
     *
     * @since 1.0.0
     * @param string $scale Temperature scale.
     * @param string $unit  Unit type.
     * @return string
     */
    private static function get_temperature_scale( $scale, $unit ) {
        if ( 'metric' === $scale ) {
            return '°C';
        } elseif ( 'imperial' === $scale ) {
            return '°F';
        }
        return 'K';
    }

    /**
     * Format visibility.
     *
     * @since 1.0.0
     * @param string $unit Visibility unit.
     * @param object $data Weather data.
     * @return string
     */
    private static function format_visibility( $unit, $data ) {
        $visibility = $data->visibility->value ?? 0;
        
        if ( 'km' === $unit && $visibility >= 1000 ) {
            return round( $visibility / 1000, 1 ) . ' km';
        }
        
        return $visibility . ' m';
    }

    /**
     * Format pressure.
     *
     * @since 1.0.0
     * @param string $unit Pressure unit.
     * @param object $data Weather data.
     * @return string
     */
    private static function format_pressure( $unit, $data ) {
        $pressure = $data->pressure->value ?? 0;
        
        if ( 'hpa' === $unit ) {
            return round( $pressure ) . ' hPa';
        }
        
        return round( $pressure ) . ' ' . $unit;
    }

    /**
     * Format wind speed.
     *
     * @since 1.0.0
     * @param string $weather_unit Weather unit.
     * @param string $target_unit  Target unit.
     * @param object $data         Weather data.
     * @param bool   $is_gust      Whether this is gust speed.
     * @return string
     */
    private static function format_wind_speed( $weather_unit, $target_unit, $data, $is_gust = false ) {
        $speed = $is_gust ? ( $data->gusts->value ?? 0 ) : ( $data->wind->speed->value ?? 0 );
        
        if ( 'metric' === $weather_unit ) {
            // m/s to km/h conversion if needed.
            if ( 'kmh' === $target_unit ) {
                $speed = round( $speed * 3.6, 1 );
                return $speed . ' km/h';
            }
            return $speed . ' m/s';
        } else {
            // Imperial: mph.
            if ( 'mph' !== $target_unit ) {
                $speed = round( $speed * 2.23694, 1 );
            }
            return $speed . ' mph';
        }
    }

    /**
     * Calculate sun position angle.
     *
     * @since 1.0.0
     * @param array $data Sun data with current_time, sunrise, sunset.
     * @return int Sun angle in degrees (-100 if invalid).
     */
    public static function calculate_sun_angle( $data ) {
        $current = strtotime( $data['current_time'] ?? '12:00' );
        $sunrise = strtotime( $data['sunrise'] ?? '06:00' );
        $sunset  = strtotime( $data['sunset'] ?? '18:00' );

        $total_day = $sunset - $sunrise;
        
        if ( $total_day <= 0 ) {
            return -100;
        }

        $elapsed = $current - $sunrise;
        $angle = ( $elapsed * 170 ) / $total_day;
        
        return round( $angle ) > 170 ? -100 : round( $angle );
    }

    /**
     * Validate and parse coordinates.
     *
     * @since 1.0.0
     * @param string $coordinate Coordinates string (lat,lng).
     * @return array
     */
    public static function validate_coordinates( $coordinate ) {
        $default = array( 'lat' => 51.509865, 'lon' => -0.118092 );
        
        if ( empty( $coordinate ) ) {
            return array( 'query' => $default, 'error' => null );
        }

        if ( ! preg_match( '/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/', $coordinate ) ) {
            return array(
                'query' => $default,
                'error' => esc_html__( 'Invalid coordinates format. Use: latitude,longitude', 'pearl-weather' ),
            );
        }

        $coords = explode( ',', trim( $coordinate ) );
        
        if ( count( $coords ) !== 2 ) {
            return array(
                'query' => $default,
                'error' => esc_html__( 'Invalid coordinates format.', 'pearl-weather' ),
            );
        }

        $lat = (float) $coords[0];
        $lon = (float) $coords[1];

        if ( $lat < -90 || $lat > 90 || $lon < -180 || $lon > 180 ) {
            return array(
                'query' => $default,
                'error' => esc_html__( 'Coordinates out of range.', 'pearl-weather' ),
            );
        }

        return array(
            'query' => array( 'lat' => $lat, 'lon' => $lon ),
            'error' => null,
        );
    }

    /**
     * Get AQI condition descriptions.
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_aqi_descriptions() {
        return array(
            'good'       => __( 'Air quality is excellent. No health concerns.', 'pearl-weather' ),
            'moderate'   => __( 'Air is acceptable. Sensitive individuals should monitor symptoms.', 'pearl-weather' ),
            'poor'       => __( 'Air may cause discomfort. Sensitive groups should reduce outdoor exposure.', 'pearl-weather' ),
            'unhealthy'  => __( 'Health risks increase. Everyone should limit outdoor time.', 'pearl-weather' ),
            'severe'     => __( 'Air is very unhealthy. Avoid outdoor activity.', 'pearl-weather' ),
            'hazardous'  => __( 'Serious health threat. Stay indoors.', 'pearl-weather' ),
        );
    }

    /**
     * Get AQI condition based on index value.
     *
     * @since 1.0.0
     * @param int $aqi AQI value.
     * @return string
     */
    public static function get_aqi_condition( $aqi ) {
        if ( $aqi <= 50 ) {
            return 'good';
        } elseif ( $aqi <= 100 ) {
            return 'moderate';
        } elseif ( $aqi <= 150 ) {
            return 'poor';
        } elseif ( $aqi <= 200 ) {
            return 'unhealthy';
        } elseif ( $aqi <= 250 ) {
            return 'severe';
        }
        return 'hazardous';
    }

    /**
     * Get AQI color for condition.
     *
     * @since 1.0.0
     * @param string $condition AQI condition.
     * @return string
     */
    public static function get_aqi_color( $condition ) {
        $colors = array(
            'good'      => '#00B150',
            'moderate'  => '#EEC631',
            'poor'      => '#EA8B34',
            'unhealthy' => '#E95378',
            'severe'    => '#B33FB9',
            'hazardous' => '#C91F33',
        );
        
        return $colors[ $condition ] ?? '#757575';
    }

    /**
     * Convert hex color to RGB/RGBA.
     *
     * @since 1.0.0
     * @param string   $hex     Hex color code.
     * @param float|null $alpha  Alpha value (0-1).
     * @return string
     */
    public static function hex_to_rgb( $hex, $alpha = null ) {
        $hex = ltrim( $hex, '#' );
        
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );
        
        if ( null !== $alpha ) {
            return "rgba($r, $g, $b, $alpha)";
        }
        
        return "$r, $g, $b";
    }

    /**
     * Format pollutant symbol with subscript numbers.
     *
     * @since 1.0.0
     * @param string $symbol Pollutant symbol (e.g., "PM2.5").
     * @return string
     */
    public static function format_pollutant_symbol( $symbol ) {
        $subscripts = array(
            '0' => '₀',
            '1' => '₁',
            '2' => '₂',
            '3' => '₃',
            '4' => '₄',
            '5' => '₅',
            '6' => '₆',
            '7' => '₇',
            '8' => '₈',
            '9' => '₉',
        );
        
        return preg_replace_callback( '/\d/', function( $matches ) use ( $subscripts ) {
            return $subscripts[ $matches[0] ];
        }, $symbol );
    }

    /**
     * Get pollutant data with calculated AQI.
     *
     * @since 1.0.0
     * @param float  $value     Pollutant concentration.
     * @param string $pollutant Pollutant type.
     * @return array
     */
    public static function get_pollutant_aqi( $value, $pollutant ) {
        $breakpoints = self::get_pollutant_breakpoints();
        
        if ( ! isset( $breakpoints[ $pollutant ] ) ) {
            return array( 'iaqi' => null, 'condition' => 'unknown' );
        }
        
        $iaqi = null;
        
        foreach ( $breakpoints[ $pollutant ] as $bp ) {
            if ( $value >= $bp['cLow'] && $value <= $bp['cHigh'] ) {
                $iaqi = round(
                    ( ( $bp['iHigh'] - $bp['iLow'] ) / ( $bp['cHigh'] - $bp['cLow'] ) ) *
                    ( $value - $bp['cLow'] ) + $bp['iLow']
                );
                break;
            }
        }
        
        $condition = self::get_aqi_condition( $iaqi );
        $color = self::get_aqi_color( $condition );
        $descriptions = self::get_aqi_descriptions();
        
        return array(
            'iaqi'      => $iaqi,
            'condition' => $condition,
            'color'     => $color,
            'label'     => ucfirst( $condition ),
            'report'    => $descriptions[ $condition ] ?? '',
        );
    }

    /**
     * Get pollutant breakpoints for AQI calculation.
     *
     * @since 1.0.0
     * @return array
     */
    private static function get_pollutant_breakpoints() {
        return array(
            'pm25' => array(
                array( 'cLow' => 0,    'cHigh' => 10,   'iLow' => 0,   'iHigh' => 50 ),
                array( 'cLow' => 10,   'cHigh' => 25,   'iLow' => 51,  'iHigh' => 100 ),
                array( 'cLow' => 25,   'cHigh' => 50,   'iLow' => 101, 'iHigh' => 150 ),
                array( 'cLow' => 50,   'cHigh' => 75,   'iLow' => 151, 'iHigh' => 200 ),
                array( 'cLow' => 75,   'cHigh' => 100,  'iLow' => 201, 'iHigh' => 250 ),
                array( 'cLow' => 100,  'cHigh' => INF,  'iLow' => 251, 'iHigh' => 300 ),
            ),
            'pm10' => array(
                array( 'cLow' => 0,    'cHigh' => 20,   'iLow' => 0,   'iHigh' => 50 ),
                array( 'cLow' => 20,   'cHigh' => 50,   'iLow' => 51,  'iHigh' => 100 ),
                array( 'cLow' => 50,   'cHigh' => 100,  'iLow' => 101, 'iHigh' => 150 ),
                array( 'cLow' => 100,  'cHigh' => 200,  'iLow' => 151, 'iHigh' => 200 ),
                array( 'cLow' => 200,  'cHigh' => 250,  'iLow' => 201, 'iHigh' => 250 ),
                array( 'cLow' => 250,  'cHigh' => INF,  'iLow' => 251, 'iHigh' => 300 ),
            ),
            'no2' => array(
                array( 'cLow' => 0,    'cHigh' => 40,   'iLow' => 0,   'iHigh' => 50 ),
                array( 'cLow' => 40,   'cHigh' => 70,   'iLow' => 51,  'iHigh' => 100 ),
                array( 'cLow' => 70,   'cHigh' => 150,  'iLow' => 101, 'iHigh' => 150 ),
                array( 'cLow' => 150,  'cHigh' => 200,  'iLow' => 151, 'iHigh' => 200 ),
                array( 'cLow' => 200,  'cHigh' => 250,  'iLow' => 201, 'iHigh' => 250 ),
                array( 'cLow' => 250,  'cHigh' => INF,  'iLow' => 251, 'iHigh' => 300 ),
            ),
            'o3' => array(
                array( 'cLow' => 0,    'cHigh' => 60,   'iLow' => 0,   'iHigh' => 50 ),
                array( 'cLow' => 60,   'cHigh' => 100,  'iLow' => 51,  'iHigh' => 100 ),
                array( 'cLow' => 100,  'cHigh' => 140,  'iLow' => 101, 'iHigh' => 150 ),
                array( 'cLow' => 140,  'cHigh' => 180,  'iLow' => 151, 'iHigh' => 200 ),
                array( 'cLow' => 180,  'cHigh' => 220,  'iLow' => 201, 'iHigh' => 250 ),
                array( 'cLow' => 220,  'cHigh' => INF,  'iLow' => 251, 'iHigh' => 300 ),
            ),
            'co' => array(
                array( 'cLow' => 0,     'cHigh' => 4400,  'iLow' => 0,   'iHigh' => 50 ),
                array( 'cLow' => 4400,  'cHigh' => 9400,  'iLow' => 51,  'iHigh' => 100 ),
                array( 'cLow' => 9400,  'cHigh' => 12400, 'iLow' => 101, 'iHigh' => 150 ),
                array( 'cLow' => 12400, 'cHigh' => 15400, 'iLow' => 151, 'iHigh' => 200 ),
                array( 'cLow' => 15400, 'cHigh' => 18000, 'iLow' => 201, 'iHigh' => 250 ),
                array( 'cLow' => 18000, 'cHigh' => INF,   'iLow' => 251, 'iHigh' => 300 ),
            ),
            'so2' => array(
                array( 'cLow' => 0,    'cHigh' => 20,   'iLow' => 0,   'iHigh' => 50 ),
                array( 'cLow' => 20,   'cHigh' => 80,   'iLow' => 51,  'iHigh' => 100 ),
                array( 'cLow' => 80,   'cHigh' => 250,  'iLow' => 101, 'iHigh' => 150 ),
                array( 'cLow' => 250,  'cHigh' => 350,  'iLow' => 151, 'iHigh' => 200 ),
                array( 'cLow' => 350,  'cHigh' => 400,  'iLow' => 201, 'iHigh' => 250 ),
                array( 'cLow' => 400,  'cHigh' => INF,  'iLow' => 251, 'iHigh' => 300 ),
            ),
        );
    }
}