<?php
/**
 * WeatherAPI Forecast Data Processor
 *
 * Processes and structures forecast data from WeatherAPI
 * into hourly forecast objects.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/API/WeatherApiData
 * @since      1.0.0
 */

namespace PearlWeather\API\WeatherApiData;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PearlWeather\API\Models\Location;

/**
 * Class ForecastData
 *
 * Holds processed forecast data from WeatherAPI.
 *
 * @since 1.0.0
 */
class ForecastData {

    /**
     * Location object.
     *
     * @var Location
     */
    private $location;

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
     * Timezone string.
     *
     * @var string
     */
    private $timezone;

    /**
     * Daily forecast array.
     *
     * @var array
     */
    private $daily_forecast = array();

    /**
     * Hourly forecast array.
     *
     * @var array
     */
    private $hourly_forecast = array();

    /**
     * Constructor.
     *
     * @param object $data        WeatherAPI response data.
     * @param string $units       Unit system ('metric' or 'imperial').
     * @param int    $hours       Number of forecast hours to include.
     * @param string $hourly_type Forecast type ('one-hour' or 'three-hour').
     */
    public function __construct( $data, $units, $hours, $hourly_type ) {
        $this->parse_data( $data, $units, $hours, $hourly_type );
    }

    /**
     * Parse forecast data.
     *
     * @since 1.0.0
     * @param object $data        WeatherAPI response data.
     * @param string $units       Unit system.
     * @param int    $hours       Number of forecast hours.
     * @param string $hourly_type Forecast type.
     */
    private function parse_data( $data, $units, $hours, $hourly_type ) {
        $utc_tz = new \DateTimeZone( 'UTC' );
        $location_tz = new \DateTimeZone( $data->location->tz_id ?? 'UTC' );
        
        // Location.
        $this->location = new Location(
            $data->location->lat ?? null,
            $data->location->lon ?? null
        );
        
        // Timezone.
        $this->timezone = $data->location->tz_id ?? 'UTC';
        
        // Current UV index and dew point from current data.
        if ( isset( $data->current ) ) {
            $this->uv_index = isset( $data->current->uv ) ? (float) $data->current->uv : null;
        }
        
        $daily_forecasts = $data->forecast->forecastday ?? array();
        $current_timestamp = $data->location->localtime_epoch ?? time();
        
        $stop = false;
        $hourly_counter = 0;
        
        foreach ( $daily_forecasts as $daily_forecast ) {
            $hourly_forecasts = $daily_forecast->hour ?? array();
            
            if ( ! $stop && 'one-hour' === $hourly_type ) {
                $stop = $this->process_one_hour_forecasts(
                    $hourly_forecasts,
                    $current_timestamp,
                    $hours,
                    $units,
                    $location_tz,
                    $hourly_counter,
                    $stop
                );
            }
            
            if ( ! $stop && 'three-hour' === $hourly_type ) {
                $stop = $this->process_three_hour_forecasts(
                    $hourly_forecasts,
                    $current_timestamp,
                    $hours,
                    $units,
                    $location_tz,
                    $hourly_counter,
                    $stop
                );
            }
        }
    }

    /**
     * Process 1-hour forecasts.
     *
     * @since 1.0.0
     * @param array    $hourly_forecasts   Hourly forecast data.
     * @param int      $current_timestamp  Current timestamp.
     * @param int      $hours              Number of hours to include.
     * @param string   $units              Unit system.
     * @param \DateTimeZone $timezone      Timezone.
     * @param int      $hourly_counter     Current counter.
     * @param bool     $stop               Stop flag.
     * @return bool Updated stop flag.
     */
    private function process_one_hour_forecasts( $hourly_forecasts, $current_timestamp, $hours, $units, $timezone, &$hourly_counter, $stop ) {
        foreach ( $hourly_forecasts as $hourly_forecast ) {
            // Skip hours that have already passed.
            $forecast_timestamp = $hourly_forecast->time_epoch ?? 0;
            if ( $forecast_timestamp <= $current_timestamp ) {
                continue;
            }
            
            $is_day = isset( $hourly_forecast->is_day ) ? (bool) $hourly_forecast->is_day : true;
            $hourly_obj = new HourlyForecast( $hourly_forecast, $units, $timezone, 'one-hour', $is_day );
            $this->hourly_forecast[] = $hourly_obj;
            
            $hourly_counter++;
            
            if ( $hourly_counter >= $hours ) {
                return true;
            }
        }
        return $stop;
    }

    /**
     * Process 3-hour average forecasts.
     *
     * @since 1.0.0
     * @param array    $hourly_forecasts   Hourly forecast data.
     * @param int      $current_timestamp  Current timestamp.
     * @param int      $hours              Number of hours to include.
     * @param string   $units              Unit system.
     * @param \DateTimeZone $timezone      Timezone.
     * @param int      $hourly_counter     Current counter.
     * @param bool     $stop               Stop flag.
     * @return bool Updated stop flag.
     */
    private function process_three_hour_forecasts( $hourly_forecasts, $current_timestamp, $hours, $units, $timezone, &$hourly_counter, $stop ) {
        $group = array();
        
        foreach ( $hourly_forecasts as $hourly_forecast ) {
            $forecast_timestamp = $hourly_forecast->time_epoch ?? 0;
            if ( $forecast_timestamp < $current_timestamp ) {
                continue;
            }
            
            $group[] = $hourly_forecast;
            
            // Process every 3-hour group.
            if ( count( $group ) === 3 ) {
                $avg_data = $this->average_hourly_group( $group );
                $is_day = isset( $group[1]->is_day ) ? (bool) $group[1]->is_day : true;
                $hourly_obj = new HourlyForecast( (object) $avg_data, $units, $timezone, 'three-hour', $is_day );
                $this->hourly_forecast[] = $hourly_obj;
                
                $hourly_counter++;
                $group = array();
                
                if ( $hourly_counter >= $hours ) {
                    return true;
                }
            }
        }
        return $stop;
    }

    /**
     * Calculate average of a 3-hour group.
     *
     * @since 1.0.0
     * @param array $group Array of 3 hourly forecast objects.
     * @return array Averaged data.
     */
    private function average_hourly_group( $group ) {
        $count = count( $group );
        
        if ( 0 === $count ) {
            return array();
        }
        
        $first = $group[0];
        
        $avg = array(
            'time_epoch'     => (int) round( array_sum( array_column( $group, 'time_epoch' ) ) / $count ),
            'time'           => $first->time ?? '',
            'temp_c'         => 0,
            'temp_f'         => 0,
            'min_temp_c'     => $group[0]->temp_c ?? 0,
            'min_temp_f'     => $group[0]->temp_f ?? 0,
            'max_temp_c'     => $group[0]->temp_c ?? 0,
            'max_temp_f'     => $group[0]->temp_f ?? 0,
            'wind_mph'       => 0,
            'wind_degree'    => 0,
            'wind_dir'       => $group[1]->wind_dir ?? '',
            'pressure_mb'    => 0,
            'precip_mm'      => 0,
            'snow_cm'        => 0,
            'humidity'       => 0,
            'cloud'          => 0,
            'chance_of_rain' => 0,
            'gust_mph'       => 0,
            'condition'      => (object) array(
                'text' => $group[1]->condition->text ?? '',
                'icon' => $group[1]->condition->icon ?? '',
                'code' => $group[1]->condition->code ?? 1000,
            ),
        );
        
        // Accumulate numeric values.
        foreach ( $group as $item ) {
            $avg['temp_c']         += $item->temp_c ?? 0;
            $avg['temp_f']         += $item->temp_f ?? 0;
            $avg['wind_mph']       += $item->wind_mph ?? 0;
            $avg['wind_degree']    += $item->wind_degree ?? 0;
            $avg['pressure_mb']    += $item->pressure_mb ?? 0;
            $avg['precip_mm']      += $item->precip_mm ?? 0;
            $avg['snow_cm']        += $item->snow_cm ?? 0;
            $avg['humidity']       += $item->humidity ?? 0;
            $avg['cloud']          += $item->cloud ?? 0;
            $avg['chance_of_rain'] += $item->chance_of_rain ?? 0;
            $avg['gust_mph']       += $item->gust_mph ?? 0;
            
            // Update min/max.
            $avg['min_temp_c'] = min( $avg['min_temp_c'], $item->temp_c ?? 0 );
            $avg['max_temp_c'] = max( $avg['max_temp_c'], $item->temp_c ?? 0 );
            $avg['min_temp_f'] = min( $avg['min_temp_f'], $item->temp_f ?? 0 );
            $avg['max_temp_f'] = max( $avg['max_temp_f'], $item->temp_f ?? 0 );
        }
        
        // Average numeric values.
        $avg['temp_c']         = round( $avg['temp_c'] / $count, 1 );
        $avg['temp_f']         = round( $avg['temp_f'] / $count, 1 );
        $avg['wind_mph']       = round( $avg['wind_mph'] / $count, 1 );
        $avg['wind_degree']    = round( $avg['wind_degree'] / $count );
        $avg['pressure_mb']    = round( $avg['pressure_mb'] / $count );
        $avg['precip_mm']      = round( $avg['precip_mm'] / $count, 1 );
        $avg['snow_cm']        = round( $avg['snow_cm'] / $count, 1 );
        $avg['humidity']       = round( $avg['humidity'] / $count );
        $avg['cloud']          = round( $avg['cloud'] / $count );
        $avg['chance_of_rain'] = round( $avg['chance_of_rain'] / $count );
        $avg['gust_mph']       = round( $avg['gust_mph'] / $count, 1 );
        
        return $avg;
    }

    // Getters...

    public function get_location() { return $this->location; }
    public function get_uv_index() { return $this->uv_index; }
    public function get_dew_point() { return $this->dew_point; }
    public function get_timezone() { return $this->timezone; }
    public function get_daily_forecast() { return $this->daily_forecast; }
    public function get_hourly_forecast() { return $this->hourly_forecast; }

    /**
     * Get number of hourly forecasts.
     *
     * @since 1.0.0
     * @return int
     */
    public function get_hourly_count() {
        return count( $this->hourly_forecast );
    }

    /**
     * Convert to array.
     *
     * @since 1.0.0
     * @return array
     */
    public function to_array() {
        $hourly = array();
        foreach ( $this->hourly_forecast as $hour ) {
            $hourly[] = $hour->to_array();
        }
        
        return array(
            'location' => $this->location->to_array(),
            'timezone' => $this->timezone,
            'uv_index' => $this->uv_index,
            'dew_point' => $this->dew_point,
            'hourly_forecast' => $hourly,
            'hourly_count' => $this->get_hourly_count(),
        );
    }
}