<?php
/**
 * Base Location Class
 *
 * Represents a geographical location with latitude and longitude coordinates.
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
 * Class Location
 *
 * Base class for location-based objects containing coordinates.
 *
 * @since 1.0.0
 */
class Location {

    /**
     * Latitude coordinate.
     *
     * @var float|null
     */
    protected $latitude;

    /**
     * Longitude coordinate.
     *
     * @var float|null
     */
    protected $longitude;

    /**
     * Constructor.
     *
     * @param float|null $lat Latitude.
     * @param float|null $lon Longitude.
     */
    public function __construct( $lat = null, $lon = null ) {
        $this->latitude = $this->validate_coordinate( $lat );
        $this->longitude = $this->validate_coordinate( $lon, -180, 180 );
    }

    /**
     * Validate a coordinate value.
     *
     * @since 1.0.0
     * @param mixed $value   Coordinate value.
     * @param float $min     Minimum allowed value.
     * @param float $max     Maximum allowed value.
     * @return float|null
     */
    private function validate_coordinate( $value, $min = -90, $max = 90 ) {
        if ( null === $value ) {
            return null;
        }
        
        $float_value = (float) $value;
        
        if ( $float_value < $min || $float_value > $max ) {
            return null;
        }
        
        return $float_value;
    }

    /**
     * Get latitude.
     *
     * @since 1.0.0
     * @return float|null
     */
    public function get_latitude() {
        return $this->latitude;
    }

    /**
     * Get longitude.
     *
     * @since 1.0.0
     * @return float|null
     */
    public function get_longitude() {
        return $this->longitude;
    }

    /**
     * Set latitude.
     *
     * @since 1.0.0
     * @param float $lat Latitude.
     * @return self
     */
    public function set_latitude( $lat ) {
        $this->latitude = $this->validate_coordinate( $lat );
        return $this;
    }

    /**
     * Set longitude.
     *
     * @since 1.0.0
     * @param float $lon Longitude.
     * @return self
     */
    public function set_longitude( $lon ) {
        $this->longitude = $this->validate_coordinate( $lon, -180, 180 );
        return $this;
    }

    /**
     * Check if location has valid coordinates.
     *
     * @since 1.0.0
     * @return bool
     */
    public function has_coordinates() {
        return null !== $this->latitude && null !== $this->longitude;
    }

    /**
     * Get coordinates as an array.
     *
     * @since 1.0.0
     * @return array
     */
    public function get_coordinates() {
        return array(
            'lat' => $this->latitude,
            'lon' => $this->longitude,
        );
    }

    /**
     * Get formatted coordinate string.
     *
     * @since 1.0.0
     * @param int $precision Decimal precision.
     * @return string
     */
    public function get_coordinate_string( $precision = 6 ) {
        if ( ! $this->has_coordinates() ) {
            return '';
        }
        
        return sprintf(
            '%.' . $precision . 'f,%.' . $precision . 'f',
            $this->latitude,
            $this->longitude
        );
    }

    /**
     * Calculate distance to another location (Haversine formula).
     *
     * @since 1.0.0
     * @param Location $other Other location.
     * @param string   $unit  Unit ('km' or 'mi').
     * @return float|null
     */
    public function distance_to( Location $other, $unit = 'km' ) {
        if ( ! $this->has_coordinates() || ! $other->has_coordinates() ) {
            return null;
        }
        
        $lat1 = deg2rad( $this->latitude );
        $lon1 = deg2rad( $this->longitude );
        $lat2 = deg2rad( $other->get_latitude() );
        $lon2 = deg2rad( $other->get_longitude() );
        
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
        
        $a = sin( $dlat / 2 ) ** 2 + cos( $lat1 ) * cos( $lat2 ) * sin( $dlon / 2 ) ** 2;
        $c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
        
        $radius = 'mi' === $unit ? 3959 : 6371;
        
        return round( $radius * $c, 2 );
    }

    /**
     * Create a Location instance from an array.
     *
     * @since 1.0.0
     * @param array $data Array with 'lat' and 'lon' keys.
     * @return self
     */
    public static function from_array( $data ) {
        return new self(
            isset( $data['lat'] ) ? $data['lat'] : null,
            isset( $data['lon'] ) ? $data['lon'] : null
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
            'lat' => $this->latitude,
            'lon' => $this->longitude,
        );
    }

    /**
     * Convert to JSON-serializable format.
     *
     * @since 1.0.0
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return $this->to_array();
    }
}