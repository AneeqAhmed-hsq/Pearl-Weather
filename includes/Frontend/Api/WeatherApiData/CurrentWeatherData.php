<?php
/**
 * Current Weather Data Processor (WeatherAPI)
 *
 * Processes and structures current weather data from WeatherAPI
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

use PearlWeather\API\Models\City;
use PearlWeather\API\Models\Sun;
use PearlWeather\API\Models\Temperature;
use PearlWeather\API\Models\Unit;
use PearlWeather\API\Models\WeatherCondition;
use PearlWeather\API\Models\Wind;
use PearlWeather\API\Models\IconConverter;

/**
 * Class CurrentWeatherData
 *
 * Holds processed current weather data from WeatherAPI.
 *
 * @since 1.0.0
 */
class CurrentWeatherData {

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
     * Precipitation amount.
     *
     * @var float
     */
    private $precipitation;

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
     * Rain chance percentage.
     *
     * @var int|null
     */
    private $rain_chance;

    /**
     * Snow amount.
     *
     * @var float|null
     */
    private $snow;

    /**
     * Last update timestamp.
     *
     * @var \DateTimeInterface
     */
    private $last_update;

    /**
     * Timezone offset in seconds.
     *
     * @var int
     */
    private $timezone_offset;

    /**
     * Timezone string.
     *
     * @var string
     */
    private $timezone_string;

    /**
     * UV index.
     *
     * @var float|null
     */
    private $uv_index;

    /**
     * Constructor.
     *
     * @param object $data  WeatherAPI response data.
     * @param string $units Unit system ('metric' or 'imperial').
     */
    public function __construct( $data, $units = 'metric' ) {
        $this->parse_data( $data, $units );
    }

    /**
     * Parse WeatherAPI data.
     *
     * @since 1.0.0
     * @param object $data  WeatherAPI response data.
     * @param string $units Unit system.
     */
    private function parse_data( $data, $units ) {
        $utc_tz = new \DateTimeZone( 'UTC' );
        $is_metric = ( 'metric' === $units );
        
        // Wind speed unit.
        $wind_unit = $is_metric ? 'm/s' : 'mph';
        
        // Temperature unit.
        $temp_unit = $is_metric ? 'c' : 'f';
        $temp_unit_full = $is_metric ? 'celsius' : 'fahrenheit';
        
        // Convert wind speeds.
        if ( $is_metric ) {
            $wind_speed = isset( $data->current->wind_mph ) ? $data->current->wind_mph * 0.44704 : 0;
            $wind_gust = isset( $data->current->gust_mph ) ? $data->current->gust_mph * 0.44704 : 0;
        } else {
            $wind_speed = isset( $data->current->wind_mph ) ? $data->current->wind_mph : 0;
            $wind_gust = isset( $data->current->gust_mph ) ? $data->current->gust_mph : 0;
        }
        
        // City.
        $country_code = $data->location->country ?? '';
        $this->city = new City(
            $data->location->region ?? 0,
            $data->location->name ?? '',
            $data->location->lat ?? null,
            $data->location->lon ?? null,
            $country_code,
            null,
            $data->location->tz_id ?? null
        );
        
        // Temperature.
        $current_temp = isset( $data->current->{'temp_' . $temp_unit} ) 
            ? (float) $data->current->{'temp_' . $temp_unit} 
            : 0;
        
        $min_temp = isset( $data->forecast->forecastday[0]->day->{'mintemp_' . $temp_unit} )
            ? (float) $data->forecast->forecastday[0]->day->{'mintemp_' . $temp_unit}
            : $current_temp;
        
        $max_temp = isset( $data->forecast->forecastday[0]->day->{'maxtemp_' . $temp_unit} )
            ? (float) $data->forecast->forecastday[0]->day->{'maxtemp_' . $temp_unit}
            : $current_temp;
        
        $this->temperature = new Temperature(
            new Unit( $current_temp, $temp_unit_full, Unit::TYPE_TEMPERATURE ),
            new Unit( $min_temp, $temp_unit_full, Unit::TYPE_TEMPERATURE ),
            new Unit( $max_temp, $temp_unit_full, Unit::TYPE_TEMPERATURE )
        );
        
        // Humidity.
        $this->humidity = new Unit(
            isset( $data->current->humidity ) ? (float) $data->current->humidity : 0,
            'percent',
            Unit::TYPE_PERCENT
        );
        
        // Wind gusts.
        if ( $wind_gust > 0 ) {
            $this->gusts = new Unit( $wind_gust, $wind_unit, Unit::TYPE_WIND );
        }
        
        // Pressure.
        $this->pressure = new Unit(
            isset( $data->current->pressure_mb ) ? (float) $data->current->pressure_mb : 0,
            'mb',
            Unit::TYPE_PRESSURE
        );
        
        // Wind.
        $wind_direction = isset( $data->current->wind_degree ) ? (int) $data->current->wind_degree : null;
        $this->wind = new Wind(
            new Unit( $wind_speed, $wind_unit, Unit::TYPE_WIND ),
            $wind_direction
        );
        
        // Clouds.
        $this->clouds = new Unit(
            isset( $data->current->cloud ) ? (float) $data->current->cloud : 0,
            'percent',
            Unit::TYPE_PERCENT
        );
        
        // Visibility.
        $visibility_km = isset( $data->current->vis_km ) ? (float) $data->current->vis_km : 10;
        $visibility_unit = $is_metric ? 'km' : 'mi';
        $visibility_value = $is_metric ? $visibility_km : $visibility_km * 0.621371;
        
        $this->visibility = new Unit( $visibility_value, $visibility_unit, Unit::TYPE_LENGTH );
        
        // Precipitation.
        $this->precipitation = isset( $data->current->precip_mm ) ? (float) $data->current->precip_mm : 0;
        
        // Rain chance.
        $this->rain_chance = isset( $data->forecast->forecastday[0]->day->daily_chance_of_rain )
            ? (int) $data->forecast->forecastday[0]->day->daily_chance_of_rain
            : null;
        
        // UV Index.
        $this->uv_index = isset( $data->current->uv ) ? (float) $data->current->uv : null;
        
        // Weather condition.
        $condition_code = isset( $data->current->condition->code ) ? (int) $data->current->condition->code : 1000;
        $condition_text = isset( $data->current->condition->text ) ? $data->current->condition->text : '';
        $is_day = isset( $data->current->is_day ) ? (bool) $data->current->is_day : true;
        $icon_code = IconConverter::get_owm_icon( $condition_code, $is_day );
        
        $this->weather = new WeatherCondition(
            $condition_code,
            $condition_text,
            $icon_code,
            $this->get_condition_main( $condition_code )
        );
        
        // Timezone.
        $location_tz = new \DateTimeZone( $data->location->tz_id ?? 'UTC' );
        $this->timezone_string = $data->location->tz_id ?? 'UTC';
        $now = new \DateTime( 'now', $utc_tz );
        $this->timezone_offset = $location_tz->getOffset( $now );
        
        // Sun times.
        $date = $data->forecast->forecastday[0]->date ?? date( 'Y-m-d' );
        $sunrise_str = $data->forecast->forecastday[0]->astro->sunrise ?? '06:00 AM';
        $sunset_str = $data->forecast->forecastday[0]->astro->sunset ?? '06:00 PM';
        
        $sunrise_time = \DateTime::createFromFormat( 'Y-m-d h:i A', "$date $sunrise_str", $location_tz );
        $sunset_time = \DateTime::createFromFormat( 'Y-m-d h:i A', "$date $sunset_str", $location_tz );
        
        if ( ! $sunrise_time || ! $sunset_time ) {
            $sunrise_time = new \DateTime( 'today 06:00:00', $location_tz );
            $sunset_time = new \DateTime( 'today 18:00:00', $location_tz );
        }
        
        $this->sun = new Sun( $sunrise_time, $sunset_time );
        
        // Last update.
        $last_updated = isset( $data->current->last_updated ) 
            ? $data->current->last_updated 
            : date( 'Y-m-d H:i:s' );
        $this->last_update = new \DateTime( $last_updated, $utc_tz );
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

    public function get_city() { return $this->city; }
    public function get_temperature() { return $this->temperature; }
    public function get_humidity() { return $this->humidity; }
    public function get_gusts() { return $this->gusts; }
    public function get_pressure() { return $this->pressure; }
    public function get_wind() { return $this->wind; }
    public function get_clouds() { return $this->clouds; }
    public function get_visibility() { return $this->visibility; }
    public function get_precipitation() { return $this->precipitation; }
    public function get_sun() { return $this->sun; }
    public function get_weather() { return $this->weather; }
    public function get_rain_chance() { return $this->rain_chance; }
    public function get_snow() { return $this->snow; }
    public function get_last_update() { return $this->last_update; }
    public function get_timezone_offset() { return $this->timezone_offset; }
    public function get_timezone_string() { return $this->timezone_string; }
    public function get_uv_index() { return $this->uv_index; }

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
            'clouds' => $this->clouds->get_formatted(),
            'visibility' => $this->visibility->get_formatted(),
            'precipitation' => $this->precipitation,
            'uv_index' => $this->uv_index,
            'rain_chance' => $this->rain_chance,
            'weather' => $this->weather->to_array(),
            'sun' => $this->sun->to_array(),
            'last_update' => $this->last_update->format( 'Y-m-d H:i:s' ),
            'timezone' => $this->timezone_string,
        );
    }
}