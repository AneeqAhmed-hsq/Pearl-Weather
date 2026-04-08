<?php
/**
 * Hourly Forecast Data Processor (WeatherAPI)
 *
 * Processes and structures hourly forecast data from WeatherAPI
 * into strongly-typed model objects.
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

use PearlWeather\API\Models\Temperature;
use PearlWeather\API\Models\Unit;
use PearlWeather\API\Models\WeatherCondition;
use PearlWeather\API\Models\Wind;
use PearlWeather\API\Models\IconConverter;

/**
 * Class HourlyForecast
 *
 * Holds processed hourly forecast data from WeatherAPI.
 *
 * @since 1.0.0
 */
class HourlyForecast {

    /**
     * Temperature (for 3-hour forecast, this is Temperature object; for 1-hour, this is Unit).
     *
     * @var Temperature|Unit
     */
    private $temperature;

    /**
     * Feels-like temperature.
     *
     * @var Unit|null
     */
    private $feels_like;

    /**
     * Humidity unit.
     *
     * @var Unit
     */
    private $humidity;

    /**
     * Wind object.
     *
     * @var Wind
     */
    private $wind;

    /**
     * Wind gust speed.
     *
     * @var Unit|null
     */
    private $gusts;

    /**
     * Cloud cover unit.
     *
     * @var Unit
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
     * @var Unit|null
     */
    private $dew_point;

    /**
     * Pressure unit.
     *
     * @var Unit
     */
    private $pressure;

    /**
     * Precipitation amount.
     *
     * @var float
     */
    private $precipitation;

    /**
     * Rain chance percentage.
     *
     * @var int
     */
    private $rain_chance;

    /**
     * Snow amount.
     *
     * @var float
     */
    private $snow;

    /**
     * Weather condition object.
     *
     * @var WeatherCondition
     */
    private $weather;

    /**
     * Time of the forecast.
     *
     * @var \DateTimeInterface
     */
    private $time;

    /**
     * Timestamp of the forecast.
     *
     * @var int
     */
    private $timestamp;

    /**
     * Whether it's daytime.
     *
     * @var bool
     */
    private $is_day;

    /**
     * Forecast type ('one-hour' or 'three-hour').
     *
     * @var string
     */
    private $forecast_type;

    /**
     * Constructor.
     *
     * @param object        $data          Forecast data from API.
     * @param string        $units         Unit system ('metric' or 'imperial').
     * @param \DateTimeZone $location_tz   Location timezone.
     * @param string        $forecast_type Forecast type ('one-hour' or 'three-hour').
     * @param bool          $is_day        Whether it's daytime.
     */
    public function __construct( $data, $units, $location_tz, $forecast_type, $is_day ) {
        $this->forecast_type = $forecast_type;
        $this->is_day = $is_day;
        $this->parse_data( $data, $units, $location_tz );
    }

    /**
     * Parse forecast data.
     *
     * @since 1.0.0
     * @param object        $data        Forecast data.
     * @param string        $units       Unit system.
     * @param \DateTimeZone $location_tz Location timezone.
     */
    private function parse_data( $data, $units, $location_tz ) {
        $is_metric = ( 'metric' === $units );
        
        // Unit settings.
        $temp_unit_symbol = $is_metric ? '°C' : '°F';
        $temp_unit_name = $is_metric ? 'celsius' : 'fahrenheit';
        $wind_speed_unit = $is_metric ? 'm/s' : 'mph';
        $temp_key = $is_metric ? 'c' : 'f';
        
        // Convert wind speeds.
        if ( $is_metric ) {
            $wind_speed = isset( $data->wind_mph ) ? $data->wind_mph * 0.44704 : 0;
            $wind_gust = isset( $data->gust_mph ) ? $data->gust_mph * 0.44704 : 0;
        } else {
            $wind_speed = isset( $data->wind_mph ) ? $data->wind_mph : 0;
            $wind_gust = isset( $data->gust_mph ) ? $data->gust_mph : 0;
        }
        
        // Parse temperature based on forecast type.
        if ( 'three-hour' === $this->forecast_type ) {
            // 3-hour forecast has min/max temperatures.
            $current_temp = isset( $data->{'temp_' . $temp_key} ) ? (float) $data->{'temp_' . $temp_key} : 0;
            $min_temp = isset( $data->{'min_temp_' . $temp_key} ) ? (float) $data->{'min_temp_' . $temp_key} : $current_temp;
            $max_temp = isset( $data->{'max_temp_' . $temp_key} ) ? (float) $data->{'max_temp_' . $temp_key} : $current_temp;
            
            $this->temperature = new Temperature(
                new Unit( $current_temp, $temp_unit_name, Unit::TYPE_TEMPERATURE ),
                new Unit( $min_temp, $temp_unit_name, Unit::TYPE_TEMPERATURE ),
                new Unit( $max_temp, $temp_unit_name, Unit::TYPE_TEMPERATURE )
            );
        } else {
            // 1-hour forecast has single temperature.
            $temp_value = isset( $data->{'temp_' . $temp_key} ) ? (float) $data->{'temp_' . $temp_key} : 0;
            $this->temperature = new Unit( $temp_value, $temp_unit_name, Unit::TYPE_TEMPERATURE );
        }
        
        // Feels-like temperature.
        if ( isset( $data->{'feelslike_' . $temp_key} ) ) {
            $this->feels_like = new Unit(
                (float) $data->{'feelslike_' . $temp_key},
                $temp_unit_name,
                Unit::TYPE_TEMPERATURE
            );
        }
        
        // Humidity.
        $humidity_value = isset( $data->humidity ) ? (int) $data->humidity : 0;
        $this->humidity = new Unit( $humidity_value, 'percent', Unit::TYPE_PERCENT );
        
        // Pressure.
        $pressure_value = isset( $data->pressure_mb ) ? (float) $data->pressure_mb : 0;
        $this->pressure = new Unit( $pressure_value, 'mb', Unit::TYPE_PRESSURE );
        
        // Wind.
        $wind_direction = isset( $data->wind_degree ) ? (int) $data->wind_degree : null;
        $this->wind = new Wind(
            new Unit( $wind_speed, $wind_speed_unit, Unit::TYPE_WIND ),
            $wind_direction
        );
        
        // Wind gust.
        if ( $wind_gust > 0 ) {
            $this->gusts = new Unit( $wind_gust, $wind_speed_unit, Unit::TYPE_WIND );
        }
        
        // Clouds.
        $clouds_value = isset( $data->cloud ) ? (int) $data->cloud : 0;
        $this->clouds = new Unit( $clouds_value, 'percent', Unit::TYPE_PERCENT );
        
        // Precipitation.
        $this->precipitation = isset( $data->precip_mm ) ? (float) $data->precip_mm : 0;
        
        // Rain chance.
        $this->rain_chance = isset( $data->chance_of_rain ) ? (int) $data->chance_of_rain : 0;
        
        // Snow.
        $this->snow = isset( $data->snow_cm ) ? (float) $data->snow_cm : 0;
        
        // UV Index.
        $this->uv_index = isset( $data->uv ) ? (float) $data->uv : null;
        
        // Dew point.
        if ( isset( $data->dewpoint_c ) && $is_metric ) {
            $this->dew_point = new Unit( (float) $data->dewpoint_c, $temp_unit_name, Unit::TYPE_TEMPERATURE );
        } elseif ( isset( $data->dewpoint_f ) ) {
            $this->dew_point = new Unit( (float) $data->dewpoint_f, $temp_unit_name, Unit::TYPE_TEMPERATURE );
        }
        
        // Weather condition.
        $condition_code = isset( $data->condition->code ) ? (int) $data->condition->code : 1000;
        $condition_text = isset( $data->condition->text ) ? $data->condition->text : '';
        $icon_code = IconConverter::get_owm_icon( $condition_code, $this->is_day );
        
        $this->weather = new WeatherCondition(
            $condition_code,
            $condition_text,
            $icon_code,
            $this->get_condition_main( $condition_code )
        );
        
        // Time.
        if ( isset( $data->time_epoch ) ) {
            $this->timestamp = (int) $data->time_epoch;
            $this->time = new \DateTimeImmutable( '@' . $this->timestamp );
            $this->time = $this->time->setTimezone( $location_tz );
        } elseif ( isset( $data->time ) ) {
            $this->time = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $data->time, $location_tz );
            if ( $this->time ) {
                $this->timestamp = $this->time->getTimestamp();
            }
        }
    }

    /**
     * Get main weather condition category.
     *
     * @since 1.0.0
     * @param int $code Weather condition code.
     * @return string
     */
    private function get_condition_main( $code ) {
        if ( $code >= 200 && $code < 300 ) return 'Thunderstorm';
        if ( $code >= 300 && $code < 400 ) return 'Drizzle';
        if ( $code >= 500 && $code < 600 ) return 'Rain';
        if ( $code >= 600 && $code < 700 ) return 'Snow';
        if ( $code >= 700 && $code < 800 ) return 'Atmosphere';
        if ( 800 === $code ) return 'Clear';
        if ( $code > 800 && $code < 900 ) return 'Clouds';
        return 'Unknown';
    }

    // Getters...

    /**
     * Get temperature.
     *
     * @return Temperature|Unit
     */
    public function get_temperature() { return $this->temperature; }

    /**
     * Get feels-like temperature.
     *
     * @return Unit|null
     */
    public function get_feels_like() { return $this->feels_like; }

    /**
     * Get humidity.
     *
     * @return Unit
     */
    public function get_humidity() { return $this->humidity; }

    /**
     * Get wind.
     *
     * @return Wind
     */
    public function get_wind() { return $this->wind; }

    /**
     * Get wind gusts.
     *
     * @return Unit|null
     */
    public function get_gusts() { return $this->gusts; }

    /**
     * Get clouds.
     *
     * @return Unit
     */
    public function get_clouds() { return $this->clouds; }

    /**
     * Get UV index.
     *
     * @return float|null
     */
    public function get_uv_index() { return $this->uv_index; }

    /**
     * Get dew point.
     *
     * @return Unit|null
     */
    public function get_dew_point() { return $this->dew_point; }

    /**
     * Get pressure.
     *
     * @return Unit
     */
    public function get_pressure() { return $this->pressure; }

    /**
     * Get precipitation.
     *
     * @return float
     */
    public function get_precipitation() { return $this->precipitation; }

    /**
     * Get rain chance.
     *
     * @return int
     */
    public function get_rain_chance() { return $this->rain_chance; }

    /**
     * Get snow amount.
     *
     * @return float
     */
    public function get_snow() { return $this->snow; }

    /**
     * Get weather condition.
     *
     * @return WeatherCondition
     */
    public function get_weather() { return $this->weather; }

    /**
     * Get time.
     *
     * @return \DateTimeInterface|null
     */
    public function get_time() { return $this->time; }

    /**
     * Get timestamp.
     *
     * @return int
     */
    public function get_timestamp() { return $this->timestamp; }

    /**
     * Get formatted time.
     *
     * @param string $format Time format.
     * @return string
     */
    public function get_time_formatted( $format = 'g:i A' ) {
        return $this->time ? $this->time->format( $format ) : '';
    }

    /**
     * Check if it's daytime.
     *
     * @return bool
     */
    public function is_day() { return $this->is_day; }

    /**
     * Get forecast type.
     *
     * @return string
     */
    public function get_forecast_type() { return $this->forecast_type; }

    /**
     * Convert to array.
     *
     * @since 1.0.0
     * @return array
     */
    public function to_array() {
        $data = array(
            'time' => $this->get_time_formatted(),
            'timestamp' => $this->timestamp,
            'is_day' => $this->is_day,
            'humidity' => $this->humidity->get_formatted(),
            'pressure' => $this->pressure->get_formatted(),
            'wind' => $this->wind->to_array(),
            'clouds' => $this->clouds->get_formatted(),
            'precipitation' => $this->precipitation,
            'rain_chance' => $this->rain_chance . '%',
            'snow' => $this->snow,
            'weather' => $this->weather->to_array(),
        );
        
        // Temperature based on forecast type.
        if ( $this->temperature instanceof Temperature ) {
            $data['temperature'] = $this->temperature->to_array();
        } else {
            $data['temperature'] = $this->temperature->get_formatted();
        }
        
        // Optional fields.
        if ( $this->feels_like ) {
            $data['feels_like'] = $this->feels_like->get_formatted();
        }
        if ( $this->gusts ) {
            $data['gusts'] = $this->gusts->get_formatted();
        }
        if ( $this->uv_index ) {
            $data['uv_index'] = $this->uv_index;
        }
        if ( $this->dew_point ) {
            $data['dew_point'] = $this->dew_point->get_formatted();
        }
        
        return $data;
    }
}