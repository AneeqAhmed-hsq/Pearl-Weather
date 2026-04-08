<?php
/**
 * OpenWeatherMap Forecast Weather Data Processor
 *
 * Processes and structures forecast weather data from OpenWeatherMap's
 * API response into strongly-typed model objects.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/API/OpenWeatherData
 * @since      1.0.0
 */

namespace PearlWeather\API\OpenWeatherData;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PearlWeather\API\Models\Location;
use PearlWeather\API\Models\Unit;

/**
 * Class ForecastWeather
 *
 * Holds processed forecast weather data from OpenWeatherMap.
 *
 * @since 1.0.0
 */
class ForecastWeather {

    /**
     * Location object.
     *
     * @var Location
     */
    private $location;

    /**
     * Wind gust data.
     *
     * @var array|null
     */
    private $gusts;

    /**
     * Cloud cover data.
     *
     * @var array|null
     */
    private $clouds;

    /**
     * UV index.
     *
     * @var float|null
     */
    private $uv_index;

    /**
     * Dew point.
     *
     * @var float|null
     */
    private $dew_point;

    /**
     * Timezone offset.
     *
     * @var int|null
     */
    private $timezone;

    /**
     * Weather alerts.
     *
     * @var array|null
     */
    private $alerts;

    /**
     * Daily forecasts array.
     *
     * @var array
     */
    private $daily_forecasts = array();

    /**
     * Hourly forecasts array.
     *
     * @var HourlyForecast[]
     */
    private $hourly_forecasts = array();

    /**
     * Constructor.
     *
     * @param object $data          OpenWeatherMap forecast response.
     * @param string $units         Unit system ('metric' or 'imperial').
     * @param int    $hours         Number of forecast hours to include.
     * @param int    $days          Number of forecast days to include.
     * @param string $forecast_type Forecast type ('hourly', 'daily', or 'both').
     * @param string $hourly_type   Hourly forecast type ('one-hour' or 'three-hour').
     */
    public function __construct( $data, $units, $hours, $days, $forecast_type, $hourly_type ) {
        $this->parse_data( $data, $units, $hours, $days, $forecast_type, $hourly_type );
    }

    /**
     * Parse forecast data.
     *
     * @since 1.0.0
     * @param object $data          Response data.
     * @param string $units         Unit system.
     * @param int    $hours         Number of hours.
     * @param int    $days          Number of days.
     * @param string $forecast_type Forecast type.
     * @param string $hourly_type   Hourly type.
     */
    private function parse_data( $data, $units, $hours, $days, $forecast_type, $hourly_type ) {
        // Location.
        $lat = isset( $data->city->coord->lat ) ? (float) $data->city->coord->lat : ( isset( $data->lat ) ? (float) $data->lat : null );
        $lon = isset( $data->city->coord->lon ) ? (float) $data->city->coord->lon : ( isset( $data->lon ) ? (float) $data->lon : null );
        $this->location = new Location( $lat, $lon );
        
        // UV Index (from current data if available).
        if ( isset( $data->current->uvi ) ) {
            $this->uv_index = (float) $data->current->uvi;
        }
        
        // Timezone.
        $this->timezone = isset( $data->city->timezone ) ? (int) $data->city->timezone : null;
        
        // Alerts.
        if ( isset( $data->alerts ) && ! empty( $data->alerts ) ) {
            $this->alerts = $this->parse_alerts( $data->alerts );
        }
        
        // Hourly forecasts.
        if ( 'daily' !== $forecast_type && isset( $data->list ) && is_array( $data->list ) ) {
            $this->parse_hourly_forecasts( $data->list, $units, $hours, $hourly_type );
        }
        
        // Daily forecasts.
        if ( 'hourly' !== $forecast_type && isset( $data->daily ) && is_array( $data->daily ) ) {
            $this->parse_daily_forecasts( $data->daily, $units, $days );
        }
    }

    /**
     * Parse hourly forecasts.
     *
     * @since 1.0.0
     * @param array  $list        Forecast list data.
     * @param string $units       Unit system.
     * @param int    $hours       Number of hours to include.
     * @param string $hourly_type Hourly type.
     */
    private function parse_hourly_forecasts( $list, $units, $hours, $hourly_type ) {
        $hourly_counter = 0;
        
        // For 3-hour forecasts, OpenWeatherMap returns data every 3 hours.
        $step = ( 'three-hour' === $hourly_type ) ? 1 : 1;
        
        foreach ( $list as $forecast ) {
            // Skip if we've reached the requested number of hours.
            if ( $hourly_counter >= $hours ) {
                break;
            }
            
            $hourly_forecast = new HourlyForecast( $forecast, $units );
            $this->hourly_forecasts[] = $hourly_forecast;
            $hourly_counter++;
        }
    }

    /**
     * Parse daily forecasts.
     *
     * @since 1.0.0
     * @param array  $daily  Daily forecast data.
     * @param string $units  Unit system.
     * @param int    $days   Number of days to include.
     */
    private function parse_daily_forecasts( $daily, $units, $days ) {
        $daily_counter = 0;
        
        foreach ( $daily as $day ) {
            if ( $daily_counter >= $days ) {
                break;
            }
            
            $daily_forecast = new DailyForecast( $day, $units );
            $this->daily_forecasts[] = $daily_forecast;
            $daily_counter++;
        }
    }

    /**
     * Parse weather alerts.
     *
     * @since 1.0.0
     * @param array $alerts Alerts data.
     * @return array
     */
    private function parse_alerts( $alerts ) {
        $parsed_alerts = array();
        
        foreach ( $alerts as $alert ) {
            $parsed_alerts[] = array(
                'sender_name' => isset( $alert->sender_name ) ? (string) $alert->sender_name : '',
                'event'       => isset( $alert->event ) ? (string) $alert->event : '',
                'start'       => isset( $alert->start ) ? (int) $alert->start : 0,
                'end'         => isset( $alert->end ) ? (int) $alert->end : 0,
                'description' => isset( $alert->description ) ? (string) $alert->description : '',
                'tags'        => isset( $alert->tags ) ? (array) $alert->tags : array(),
            );
        }
        
        return $parsed_alerts;
    }

    // Getters...

    /**
     * Get location.
     *
     * @return Location
     */
    public function get_location() {
        return $this->location;
    }

    /**
     * Get UV index.
     *
     * @return float|null
     */
    public function get_uv_index() {
        return $this->uv_index;
    }

    /**
     * Get dew point.
     *
     * @return float|null
     */
    public function get_dew_point() {
        return $this->dew_point;
    }

    /**
     * Get timezone.
     *
     * @return int|null
     */
    public function get_timezone() {
        return $this->timezone;
    }

    /**
     * Get alerts.
     *
     * @return array|null
     */
    public function get_alerts() {
        return $this->alerts;
    }

    /**
     * Get daily forecasts.
     *
     * @return array
     */
    public function get_daily_forecasts() {
        return $this->daily_forecasts;
    }

    /**
     * Get hourly forecasts.
     *
     * @return HourlyForecast[]
     */
    public function get_hourly_forecasts() {
        return $this->hourly_forecasts;
    }

    /**
     * Get number of hourly forecasts.
     *
     * @return int
     */
    public function get_hourly_count() {
        return count( $this->hourly_forecasts );
    }

    /**
     * Get number of daily forecasts.
     *
     * @return int
     */
    public function get_daily_count() {
        return count( $this->daily_forecasts );
    }

    /**
     * Convert to array.
     *
     * @since 1.0.0
     * @return array
     */
    public function to_array() {
        $hourly = array();
        foreach ( $this->hourly_forecasts as $hour ) {
            $hourly[] = $hour->to_array();
        }
        
        $daily = array();
        foreach ( $this->daily_forecasts as $day ) {
            $daily[] = $day->to_array();
        }
        
        return array(
            'location' => $this->location->to_array(),
            'timezone' => $this->timezone,
            'uv_index' => $this->uv_index,
            'alerts' => $this->alerts,
            'hourly_forecast' => $hourly,
            'daily_forecast' => $daily,
            'hourly_count' => $this->get_hourly_count(),
            'daily_count' => $this->get_daily_count(),
        );
    }
}