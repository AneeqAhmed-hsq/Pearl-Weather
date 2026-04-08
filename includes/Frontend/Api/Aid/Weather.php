<?php
/**
 * Weather Condition Class
 *
 * Represents weather condition data including ID, description, and icon.
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
 * Class WeatherCondition
 *
 * Handles weather condition data and icon URLs.
 *
 * @since 1.0.0
 */
class WeatherCondition {

    /**
     * Default OpenWeatherMap icon URL template.
     *
     * @var string
     */
    private static $icon_url_template = 'https://openweathermap.org/img/w/%s.png';

    /**
     * Alternative icon URL template for 2x resolution.
     *
     * @var string
     */
    private static $icon_url_2x_template = 'https://openweathermap.org/img/w/%s@2x.png';

    /**
     * Weather condition ID.
     *
     * @var int
     */
    private $id;

    /**
     * Weather condition description (e.g., "light rain").
     *
     * @var string
     */
    private $description;

    /**
     * Weather condition main category (e.g., "Rain").
     *
     * @var string
     */
    private $main;

    /**
     * Icon code (e.g., "10d").
     *
     * @var string
     */
    private $icon_code;

    /**
     * Constructor.
     *
     * @param int    $id          Weather condition ID.
     * @param string $description Weather description.
     * @param string $icon_code   Icon code.
     * @param string $main        Main weather category (optional).
     */
    public function __construct( $id, $description, $icon_code, $main = '' ) {
        $this->id = (int) $id;
        $this->description = (string) $description;
        $this->icon_code = (string) $icon_code;
        $this->main = (string) $main;
    }

    /**
     * Get weather condition ID.
     *
     * @since 1.0.0
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get weather description.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Get main weather category.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_main() {
        return $this->main;
    }

    /**
     * Get icon code.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_icon_code() {
        return $this->icon_code;
    }

    /**
     * Get icon URL.
     *
     * @since 1.0.0
     * @param bool $retina Whether to return 2x resolution URL.
     * @return string
     */
    public function get_icon_url( $retina = false ) {
        $template = $retina ? self::$icon_url_2x_template : self::$icon_url_template;
        return sprintf( $template, $this->icon_code );
    }

    /**
     * Get the day/night suffix from icon code.
     *
     * @since 1.0.0
     * @return string 'd' for day, 'n' for night.
     */
    public function get_day_night_suffix() {
        return substr( $this->icon_code, -1 );
    }

    /**
     * Check if this is a day icon.
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_day() {
        return 'd' === $this->get_day_night_suffix();
    }

    /**
     * Check if this is a night icon.
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_night() {
        return 'n' === $this->get_day_night_suffix();
    }

    /**
     * Get the base icon code (without day/night suffix).
     *
     * @since 1.0.0
     * @return string
     */
    public function get_base_icon_code() {
        return substr( $this->icon_code, 0, 2 );
    }

    /**
     * Get a human-readable condition name.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_condition_name() {
        $conditions = array(
            // Thunderstorm.
            200 => 'Thunderstorm with light rain',
            201 => 'Thunderstorm with rain',
            202 => 'Thunderstorm with heavy rain',
            210 => 'Light thunderstorm',
            211 => 'Thunderstorm',
            212 => 'Heavy thunderstorm',
            221 => 'Ragged thunderstorm',
            230 => 'Thunderstorm with light drizzle',
            231 => 'Thunderstorm with drizzle',
            232 => 'Thunderstorm with heavy drizzle',
            
            // Drizzle.
            300 => 'Light intensity drizzle',
            301 => 'Drizzle',
            302 => 'Heavy intensity drizzle',
            310 => 'Light intensity drizzle rain',
            311 => 'Drizzle rain',
            312 => 'Heavy intensity drizzle rain',
            313 => 'Shower rain and drizzle',
            314 => 'Heavy shower rain and drizzle',
            321 => 'Shower drizzle',
            
            // Rain.
            500 => 'Light rain',
            501 => 'Moderate rain',
            502 => 'Heavy intensity rain',
            503 => 'Very heavy rain',
            504 => 'Extreme rain',
            511 => 'Freezing rain',
            520 => 'Light intensity shower rain',
            521 => 'Shower rain',
            522 => 'Heavy intensity shower rain',
            531 => 'Ragged shower rain',
            
            // Snow.
            600 => 'Light snow',
            601 => 'Snow',
            602 => 'Heavy snow',
            611 => 'Sleet',
            612 => 'Light shower sleet',
            613 => 'Shower sleet',
            615 => 'Light rain and snow',
            616 => 'Rain and snow',
            620 => 'Light shower snow',
            621 => 'Shower snow',
            622 => 'Heavy shower snow',
            
            // Atmosphere.
            701 => 'Mist',
            711 => 'Smoke',
            721 => 'Haze',
            731 => 'Sand/dust whirls',
            741 => 'Fog',
            751 => 'Sand',
            761 => 'Dust',
            762 => 'Volcanic ash',
            771 => 'Squalls',
            781 => 'Tornado',
            
            // Clear.
            800 => 'Clear sky',
            
            // Clouds.
            801 => 'Few clouds',
            802 => 'Scattered clouds',
            803 => 'Broken clouds',
            804 => 'Overcast clouds',
        );
        
        return isset( $conditions[ $this->id ] ) 
            ? $conditions[ $this->id ] 
            : $this->description;
    }

    /**
     * Get CSS class for weather condition.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_css_class() {
        $base = $this->get_base_icon_code();
        
        $map = array(
            '01' => 'clear',
            '02' => 'few-clouds',
            '03' => 'scattered-clouds',
            '04' => 'broken-clouds',
            '09' => 'shower-rain',
            '10' => 'rain',
            '11' => 'thunderstorm',
            '13' => 'snow',
            '50' => 'mist',
        );
        
        $class = isset( $map[ $base ] ) ? $map[ $base ] : 'default';
        $suffix = $this->get_day_night_suffix();
        
        return "pw-weather-{$class} pw-weather-{$class}-{$suffix}";
    }

    /**
     * Set the global icon URL template.
     *
     * @since 1.0.0
     * @param string $template URL template with %s placeholder for icon code.
     */
    public static function set_icon_url_template( $template ) {
        self::$icon_url_template = $template;
    }

    /**
     * Set the global 2x icon URL template.
     *
     * @since 1.0.0
     * @param string $template URL template with %s placeholder for icon code.
     */
    public static function set_icon_url_2x_template( $template ) {
        self::$icon_url_2x_template = $template;
    }

    /**
     * Get the current icon URL template.
     *
     * @since 1.0.0
     * @return string
     */
    public static function get_icon_url_template() {
        return self::$icon_url_template;
    }

    /**
     * Create a WeatherCondition instance from API data.
     *
     * @since 1.0.0
     * @param array $data Weather data from API.
     * @return self
     */
    public static function from_api_data( $data ) {
        return new self(
            isset( $data['id'] ) ? $data['id'] : 0,
            isset( $data['description'] ) ? $data['description'] : '',
            isset( $data['icon'] ) ? $data['icon'] : '01d',
            isset( $data['main'] ) ? $data['main'] : ''
        );
    }

    /**
     * Convert to array.
     *
     * @since 1.0.0
     * @return array
     */
    public function to_array() {
        return array(
            'id'          => $this->id,
            'main'        => $this->main,
            'description' => $this->description,
            'icon'        => $this->icon_code,
            'icon_url'    => $this->get_icon_url(),
            'icon_url_2x' => $this->get_icon_url( true ),
            'condition_name' => $this->get_condition_name(),
            'css_class'   => $this->get_css_class(),
            'is_day'      => $this->is_day(),
        );
    }

    /**
     * Convert to string (returns description).
     *
     * @since 1.0.0
     * @return string
     */
    public function __toString() {
        return $this->description;
    }
}