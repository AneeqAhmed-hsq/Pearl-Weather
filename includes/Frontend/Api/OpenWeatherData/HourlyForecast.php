<?php
/**
 * OpenWeatherMap Hourly Forecast Data Processor
 *
 * Processes and structures hourly forecast data from OpenWeatherMap's
 * 3-hour forecast API into strongly-typed model objects.
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

use PearlWeather\API\Models\Temperature;
use PearlWeather\API\Models\Unit;
use PearlWeather\API\Models\WeatherCondition;
use PearlWeather\API\Models\Wind;

/**
 * Class HourlyForecast
 *
 * Holds processed hourly forecast data from OpenWeatherMap.
 *
 * @since 1.0.0
 */
class HourlyForecast {

    /**
     * Temperature object.
     *
     * @var Temperature
     */
    private $temperature;

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
     * Pressure unit.
     *
     * @var Unit
     */
    private $pressure;

    /**
     * Precipitation probability (POP) as a percentage.
     *
     * @var int
     */
    private $precipitation_probability;

    /**
     * Rain chance percentage (alias for precipitation_probability).
     *
     * @var int
     */
    private $rain_chance;

    /**
     * Weather condition object.
     *
     * @var WeatherCondition
     */
    private $weather;

    /**
     * Forecast time.
     *
     * @var \DateTimeInterface
     */
    private $time;

    /**
     * Snow amount (mm/h).
     *
     * @var float
     */
    private $snow;

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
     * Constructor.
     *
     * @param object $data     Forecast data from API.
     * @param string $units    Unit system ('metric' or 'imperial').
     * @param string $api_type API type ('premium_call' or standard).
     */
    public function __construct( $data, $units = 'metric', $api_type = '' ) {
        $this->parse_data( $data, $units, $api_type );
    }

    /**
     * Parse forecast data.
     *
     * @since 1.0.0
     * @param object $data     Forecast data.
     * @param string $units    Unit system.
     * @param string $api_type API type.
     */
    private function parse_data( $data, $units, $api_type ) {
        $utc_tz = new \DateTimeZone( 'UTC' );
        
        // Temperature unit.
        $temp_unit = ( 'metric' === $units ) ? 'celsius' : 'fahrenheit';
        $temp_symbol = ( 'metric' === $units ) ? '°C' : '°F';
        
        // Temperature.
        $current_temp = isset( $data->main->temp ) ? (float) $data->main->temp : 0;
        $min_temp = isset( $data->main->temp_min ) ? (float) $data->main->temp_min : $current_temp;
        $max_temp = isset( $data->main->temp_max ) ? (float) $data->main->temp_max : $current_temp;
        
        $this->temperature = new Temperature(
            new Unit( $current_temp, $temp_unit, Unit::TYPE_TEMPERATURE ),
            new Unit( $min_temp, $temp_unit, Unit::TYPE_TEMPERATURE ),
            new Unit( $max_temp, $temp_unit, Unit::TYPE_TEMPERATURE )
        );
        
        // Humidity.
        $humidity_value = isset( $data->main->humidity ) ? (float) $data->main->humidity : 0;
        $this->humidity = new Unit( $humidity_value, 'percent', Unit::TYPE_PERCENT );
        
        // Pressure.
        $pressure_value = isset( $data->main->pressure ) ? (float) $data->main->pressure : 0;
        $this->pressure = new Unit( $pressure_value, 'hPa', Unit::TYPE_PRESSURE );
        
        // Wind.
        $wind_speed = isset( $data->wind->speed ) ? (float) $data->wind->speed : 0;
        $wind_speed_unit = ( 'metric' === $units ) ? 'm/s' : 'mph';
        $wind_direction = isset( $data->wind->deg ) ? (int) $data->wind->deg : null;
        
        $this->wind = new Wind(
            new Unit( $wind_speed, $wind_speed_unit, Unit::TYPE_WIND ),
            $wind_direction
        );
        
        // Wind gusts.
        if ( isset( $data->wind->gust ) ) {
            $gust_value = (float) $data->wind->gust;
            $this->gusts = new Unit( $gust_value, $wind_speed_unit, Unit::TYPE_WIND );
        }
        
        // Clouds.
        $clouds_value = isset( $data->clouds->all ) ? (float) $data->clouds->all : 0;
        $this->clouds = new Unit( $clouds_value, 'percent', Unit::TYPE_PERCENT );
        
        // Precipitation probability (POP).
        $pop = isset( $data->pop ) ? (float) $data->pop : 0;
        $this->precipitation_probability = (int) round( $pop * 100 );
        $this->rain_chance = $this->precipitation_probability;
        
        // Snow (determine the correct property based on API type).
        $hour_key = ( 'premium_call' === $api_type ) ? '1h' : '3h';
        $snow_amount = 0;
        
        if ( isset( $data->snow->{$hour_key} ) ) {
            $snow_amount = (float) $data->snow->{$hour_key};
        } elseif ( isset( $data->snow ) && is_numeric( $data->snow ) ) {
            $snow_amount = (float) $data->snow;
        }
        
        $this->snow = $snow_amount;
        
        // Weather condition.
        $condition_id = isset( $data->weather[0]->id ) ? (int) $data->weather[0]->id : 800;
        $condition_desc = isset( $data->weather[0]->description ) ? (string) $data->weather[0]->description : '';
        $icon_code = isset( $data->weather[0]->icon ) ? (string) $data->weather[0]->icon : '01d';
        
        $this->weather = new WeatherCondition(
            $condition_id,
            $condition_desc,
            $icon_code,
            $this->get_condition_main( $condition_id )
        );
        
        // Time.
        if ( isset( $data->dt ) ) {
            $this->time = new \DateTimeImmutable( '@' . $data->dt );
            $this->time = $this->time->setTimezone( $utc_tz );
        }
    }

    /**
     * Get main weather condition category from condition ID.
     *
     * @since 1.0.0
     * @param int $id Weather condition ID.
     * @return string
     */
    private function get_condition_main( $id ) {
        if ( $id >= 200 && $id < 300 ) return 'Thunderstorm';
        if ( $id >= 300 && $id < 400 ) return 'Drizzle';
        if ( $id >= 500 && $id < 600 ) return 'Rain';
        if ( $id >= 600 && $id < 700 ) return 'Snow';
        if ( $id >= 700 && $id < 800 ) return 'Atmosphere';
        if ( 800 === $id ) return 'Clear';
        if ( $id > 800 && $id < 900 ) return 'Clouds';
        return 'Unknown';
    }

    // Getters...

    public function get_temperature() { return $this->temperature; }
    public function get_humidity() { return $this->humidity; }
    public function get_wind() { return $this->wind; }
    public function get_pressure() { return $this->pressure; }
    public function get_precipitation_probability() { return $this->precipitation_probability; }
    public function get_rain_chance() { return $this->rain_chance; }
    public function get_weather() { return $this->weather; }
    public function get_time() { return $this->time; }
    public function get_snow() { return $this->snow; }
    public function get_gusts() { return $this->gusts; }
    public function get_clouds() { return $this->clouds; }
    public function get_uv_index() { return $this->uv_index; }

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
     * Get formatted rain chance.
     *
     * @return string
     */
    public function get_rain_chance_formatted() {
        return $this->rain_chance . '%';
    }

    /**
     * Get formatted snow amount.
     *
     * @return string
     */
    public function get_snow_formatted() {
        if ( $this->snow <= 0 ) {
            return '0 mm/h';
        }
        return round( $this->snow, 2 ) . ' mm/h';
    }

    /**
     * Convert to array.
     *
     * @since 1.0.0
     * @return array
     */
    public function to_array() {
        return array(
            'time' => $this->get_time_formatted(),
            'timestamp' => $this->time ? $this->time->getTimestamp() : null,
            'temperature' => $this->temperature->to_array(),
            'humidity' => $this->humidity->get_formatted(),
            'pressure' => $this->pressure->get_formatted(),
            'wind' => $this->wind->to_array(),
            'gusts' => $this->gusts ? $this->gusts->get_formatted() : null,
            'clouds' => $this->clouds->get_formatted(),
            'rain_chance' => $this->get_rain_chance_formatted(),
            'snow' => $this->get_snow_formatted(),
            'weather' => $this->weather->to_array(),
        );
    }
}