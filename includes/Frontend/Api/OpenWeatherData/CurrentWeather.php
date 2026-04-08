<?php
/**
 * OpenWeatherMap Current Weather Data Processor
 *
 * Processes and structures current weather data from OpenWeatherMap's
 * XML API response into strongly-typed model objects.
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

use PearlWeather\API\Models\City;
use PearlWeather\API\Models\Sun;
use PearlWeather\API\Models\Temperature;
use PearlWeather\API\Models\Unit;
use PearlWeather\API\Models\WeatherCondition;
use PearlWeather\API\Models\Wind;

/**
 * Class CurrentWeather
 *
 * Holds processed current weather data from OpenWeatherMap.
 *
 * @since 1.0.0
 */
class CurrentWeather {

    /**
     * City object.
     *
     * @var City
     */
    private $city;

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
     * Wind gust speed.
     *
     * @var Unit|null
     */
    private $gusts;

    /**
     * Pressure unit.
     *
     * @var Unit
     */
    private $pressure;

    /**
     * Wind object.
     *
     * @var Wind
     */
    private $wind;

    /**
     * Cloud cover unit.
     *
     * @var Unit
     */
    private $clouds;

    /**
     * Visibility unit.
     *
     * @var Unit
     */
    private $visibility;

    /**
     * Sun object.
     *
     * @var Sun
     */
    private $sun;

    /**
     * Weather condition object.
     *
     * @var WeatherCondition
     */
    private $weather;

    /**
     * Last update timestamp.
     *
     * @var \DateTimeInterface
     */
    private $last_update;

    /**
     * Timezone offset.
     *
     * @var int|null
     */
    private $timezone;

    /**
     * Constructor.
     *
     * @param \SimpleXMLElement $data  OpenWeatherMap XML response.
     * @param string            $units Unit system ('metric' or 'imperial').
     */
    public function __construct( \SimpleXMLElement $data, $units = 'metric' ) {
        $this->parse_data( $data, $units );
    }

    /**
     * Parse XML data.
     *
     * @since 1.0.0
     * @param \SimpleXMLElement $data  XML response.
     * @param string            $units Unit system.
     */
    private function parse_data( \SimpleXMLElement $data, $units ) {
        $utc_tz = new \DateTimeZone( 'UTC' );
        
        // Wind speed unit.
        $wind_speed_unit = ( 'metric' === $units ) ? 'm/s' : 'mph';
        
        // City.
        $city_id = isset( $data->city['id'] ) ? (int) $data->city['id'] : 0;
        $city_name = isset( $data->city['name'] ) ? (string) $data->city['name'] : '';
        $lat = isset( $data->city->coord['lat'] ) ? (float) $data->city->coord['lat'] : null;
        $lon = isset( $data->city->coord['lon'] ) ? (float) $data->city->coord['lon'] : null;
        $country = isset( $data->city->country ) ? (string) $data->city->country : '';
        $timezone_offset = isset( $data->city->timezone ) ? (int) $data->city->timezone : null;
        
        $this->city = new City( $city_id, $city_name, $lat, $lon, $country, null, $timezone_offset );
        
        // Temperature.
        $temp_unit = isset( $data->temperature['unit'] ) ? (string) $data->temperature['unit'] : 'celsius';
        $temp_value = isset( $data->temperature['value'] ) ? (float) $data->temperature['value'] : 0;
        $temp_min = isset( $data->temperature['min'] ) ? (float) $data->temperature['min'] : $temp_value;
        $temp_max = isset( $data->temperature['max'] ) ? (float) $data->temperature['max'] : $temp_value;
        
        $this->temperature = new Temperature(
            new Unit( $temp_value, $temp_unit, Unit::TYPE_TEMPERATURE ),
            new Unit( $temp_min, $temp_unit, Unit::TYPE_TEMPERATURE ),
            new Unit( $temp_max, $temp_unit, Unit::TYPE_TEMPERATURE )
        );
        
        // Humidity.
        $humidity_value = isset( $data->humidity['value'] ) ? (float) $data->humidity['value'] : 0;
        $humidity_unit = isset( $data->humidity['unit'] ) ? (string) $data->humidity['unit'] : 'percent';
        $this->humidity = new Unit( $humidity_value, $humidity_unit, Unit::TYPE_PERCENT );
        
        // Pressure.
        $pressure_value = isset( $data->pressure['value'] ) ? (float) $data->pressure['value'] : 0;
        $this->pressure = new Unit( $pressure_value, 'mb', Unit::TYPE_PRESSURE );
        
        // Wind.
        $wind_speed = isset( $data->wind->speed['value'] ) ? (float) $data->wind->speed['value'] : 0;
        $wind_direction = isset( $data->wind->direction['value'] ) ? (int) $data->wind->direction['value'] : null;
        
        $this->wind = new Wind(
            new Unit( $wind_speed, $wind_speed_unit, Unit::TYPE_WIND ),
            $wind_direction
        );
        
        // Wind gusts.
        if ( isset( $data->wind->gusts['value'] ) ) {
            $gust_value = (float) $data->wind->gusts['value'];
            $gust_unit = isset( $data->wind->speed['unit'] ) ? (string) $data->wind->speed['unit'] : $wind_speed_unit;
            $this->gusts = new Unit( $gust_value, $gust_unit, Unit::TYPE_WIND );
        }
        
        // Clouds.
        $clouds_value = isset( $data->clouds['value'] ) ? (float) $data->clouds['value'] : 0;
        $clouds_name = isset( $data->clouds['name'] ) ? (string) $data->clouds['name'] : '';
        $this->clouds = new Unit( $clouds_value, 'percent', Unit::TYPE_PERCENT, $clouds_name );
        
        // Visibility (convert meters to kilometers).
        $visibility_meters = isset( $data->visibility['value'] ) ? (float) $data->visibility['value'] : 10000;
        $visibility_km = $visibility_meters / 1000;
        $this->visibility = new Unit( $visibility_km, 'km', Unit::TYPE_LENGTH );
        
        // Sun.
        $sunrise_str = isset( $data->city->sun['rise'] ) ? (string) $data->city->sun['rise'] : '';
        $sunset_str = isset( $data->city->sun['set'] ) ? (string) $data->city->sun['set'] : '';
        
        $sunrise = new \DateTime( $sunrise_str, $utc_tz );
        $sunset = new \DateTime( $sunset_str, $utc_tz );
        $this->sun = new Sun( $sunrise, $sunset );
        
        // Weather condition.
        $condition_id = isset( $data->weather['number'] ) ? (int) $data->weather['number'] : 800;
        $condition_desc = isset( $data->weather['value'] ) ? (string) $data->weather['value'] : '';
        $icon_code = isset( $data->weather['icon'] ) ? (string) $data->weather['icon'] : '01d';
        
        $this->weather = new WeatherCondition(
            $condition_id,
            $condition_desc,
            $icon_code,
            $this->get_condition_main( $condition_id )
        );
        
        // Last update.
        $last_update_str = isset( $data->lastupdate['value'] ) ? (string) $data->lastupdate['value'] : '';
        $this->last_update = new \DateTime( $last_update_str, $utc_tz );
        
        // Timezone.
        $this->timezone = isset( $data->city->timezone ) ? (int) $data->city->timezone : null;
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

    public function get_city() { return $this->city; }
    public function get_temperature() { return $this->temperature; }
    public function get_humidity() { return $this->humidity; }
    public function get_gusts() { return $this->gusts; }
    public function get_pressure() { return $this->pressure; }
    public function get_wind() { return $this->wind; }
    public function get_clouds() { return $this->clouds; }
    public function get_visibility() { return $this->visibility; }
    public function get_sun() { return $this->sun; }
    public function get_weather() { return $this->weather; }
    public function get_last_update() { return $this->last_update; }
    public function get_timezone() { return $this->timezone; }

    /**
     * Convert to array.
     *
     * @since 1.0.0
     * @return array
     */
    public function to_array() {
        return array(
            'city' => $this->city->to_array(),
            'temperature' => $this->temperature->to_array(),
            'humidity' => $this->humidity->get_formatted(),
            'pressure' => $this->pressure->get_formatted(),
            'wind' => $this->wind->to_array(),
            'gusts' => $this->gusts ? $this->gusts->get_formatted() : null,
            'clouds' => $this->clouds->get_formatted(),
            'visibility' => $this->visibility->get_formatted(),
            'weather' => $this->weather->to_array(),
            'sun' => $this->sun->to_array(),
            'last_update' => $this->last_update->format( 'Y-m-d H:i:s' ),
            'timezone' => $this->timezone,
        );
    }
}