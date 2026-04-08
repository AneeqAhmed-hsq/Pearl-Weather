<?php
/**
 * City Class
 *
 * Represents a city location with its geographical and demographic data.
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
 * Class City
 *
 * Represents a city with location, population, and timezone data.
 *
 * @since 1.0.0
 */
class City extends Location {

    /**
     * City ID (from OpenWeatherMap).
     *
     * @var int
     */
    private $id;

    /**
     * City name.
     *
     * @var string
     */
    private $name;

    /**
     * Country code (ISO 3166-1 alpha-2).
     *
     * @var string
     */
    private $country;

    /**
     * City population.
     *
     * @var int
     */
    private $population;

    /**
     * Timezone object for the city.
     *
     * @var \DateTimeZone
     */
    private $timezone;

    /**
     * Constructor.
     *
     * @param int         $id              City ID.
     * @param string|null $name            City name.
     * @param float|null  $lat             Latitude.
     * @param float|null  $lon             Longitude.
     * @param string|null $country         Country code.
     * @param int|null    $population      City population.
     * @param int|null    $timezone_offset Timezone offset in seconds from UTC.
     */
    public function __construct( $id, $name = null, $lat = null, $lon = null, $country = null, $population = null, $timezone_offset = null ) {
        $this->id         = (int) $id;
        $this->name       = $name ? (string) $name : null;
        $this->country    = $country ? (string) $country : null;
        $this->population = $population ? (int) $population : null;
        
        if ( null !== $timezone_offset ) {
            $timezone_string = self::offset_to_timezone_string( (int) $timezone_offset );
            $this->timezone = new \DateTimeZone( $timezone_string );
        }

        parent::__construct( $lat, $lon );
    }

    /**
     * Get city ID.
     *
     * @since 1.0.0
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get city name.
     *
     * @since 1.0.0
     * @return string|null
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Get country code.
     *
     * @since 1.0.0
     * @return string|null
     */
    public function get_country() {
        return $this->country;
    }

    /**
     * Get formatted location string (city, country).
     *
     * @since 1.0.0
     * @return string
     */
    public function get_formatted_location() {
        $parts = array();
        
        if ( ! empty( $this->name ) ) {
            $parts[] = $this->name;
        }
        
        if ( ! empty( $this->country ) ) {
            $parts[] = $this->country;
        }
        
        return implode( ', ', $parts );
    }

    /**
     * Get population.
     *
     * @since 1.0.0
     * @return int|null
     */
    public function get_population() {
        return $this->population;
    }

    /**
     * Get timezone object.
     *
     * @since 1.0.0
     * @return \DateTimeZone|null
     */
    public function get_timezone() {
        return $this->timezone;
    }

    /**
     * Get timezone offset in seconds.
     *
     * @since 1.0.0
     * @return int|null
     */
    public function get_timezone_offset() {
        if ( ! $this->timezone ) {
            return null;
        }
        
        $now = new \DateTime( 'now', $this->timezone );
        return $now->getOffset();
    }

    /**
     * Convert timezone offset from seconds to timezone string (e.g., "+0530").
     *
     * @since 1.0.0
     * @param int $offset_seconds Offset in seconds from UTC.
     * @return string
     */
    private static function offset_to_timezone_string( $offset_seconds ) {
        $is_negative = $offset_seconds < 0;
        $abs_offset = abs( $offset_seconds );
        
        $hours = floor( $abs_offset / 3600 );
        $minutes = floor( ( $abs_offset % 3600 ) / 60 );
        
        $sign = $is_negative ? '-' : '+';
        
        return sprintf( '%s%02d%02d', $sign, $hours, $minutes );
    }

    /**
     * Create a City instance from API response data.
     *
     * @since 1.0.0
     * @param array $data City data from API.
     * @return self
     */
    public static function from_api_data( $data ) {
        return new self(
            isset( $data['id'] ) ? $data['id'] : 0,
            isset( $data['name'] ) ? $data['name'] : null,
            isset( $data['coord']['lat'] ) ? $data['coord']['lat'] : null,
            isset( $data['coord']['lon'] ) ? $data['coord']['lon'] : null,
            isset( $data['country'] ) ? $data['country'] : null,
            isset( $data['population'] ) ? $data['population'] : null,
            isset( $data['timezone'] ) ? $data['timezone'] : null
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
            'id'         => $this->id,
            'name'       => $this->name,
            'country'    => $this->country,
            'population' => $this->population,
            'lat'        => $this->get_latitude(),
            'lon'        => $this->get_longitude(),
            'timezone'   => $this->timezone ? $this->timezone->getName() : null,
        );
    }
}