<?php
/**
 * Wind Class
 *
 * Represents wind data including speed, direction, and gusts.
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
 * Class Wind
 *
 * Handles wind speed, direction, and gust data.
 *
 * @since 1.0.0
 */
class Wind {

    /**
     * Wind speed.
     *
     * @var Unit
     */
    private $speed;

    /**
     * Wind direction in degrees.
     *
     * @var int|null
     */
    private $direction_degrees;

    /**
     * Wind direction as cardinal point (N, NE, E, etc.).
     *
     * @var string|null
     */
    private $direction_cardinal;

    /**
     * Wind gust speed.
     *
     * @var Unit|null
     */
    private $gust;

    /**
     * Constructor.
     *
     * @param Unit      $speed             Wind speed.
     * @param int|null  $direction_degrees Wind direction in degrees.
     * @param Unit|null $gust              Wind gust speed.
     */
    public function __construct( Unit $speed, $direction_degrees = null, Unit $gust = null ) {
        $this->speed = $speed;
        $this->direction_degrees = null !== $direction_degrees ? (int) $direction_degrees : null;
        $this->direction_cardinal = $this->calculate_cardinal_direction( $this->direction_degrees );
        $this->gust = $gust;
    }

    /**
     * Get wind speed.
     *
     * @since 1.0.0
     * @return Unit
     */
    public function get_speed() {
        return $this->speed;
    }

    /**
     * Get wind speed value.
     *
     * @since 1.0.0
     * @return float
     */
    public function get_speed_value() {
        return $this->speed->get_value();
    }

    /**
     * Get wind speed formatted.
     *
     * @since 1.0.0
     * @param bool $include_unit Whether to include unit.
     * @return string
     */
    public function get_speed_formatted( $include_unit = true ) {
        return $this->speed->get_formatted( $include_unit );
    }

    /**
     * Get wind direction in degrees.
     *
     * @since 1.0.0
     * @return int|null
     */
    public function get_direction_degrees() {
        return $this->direction_degrees;
    }

    /**
     * Get wind direction as cardinal point.
     *
     * @since 1.0.0
     * @return string|null
     */
    public function get_direction_cardinal() {
        return $this->direction_cardinal;
    }

    /**
     * Get wind gust speed.
     *
     * @since 1.0.0
     * @return Unit|null
     */
    public function get_gust() {
        return $this->gust;
    }

    /**
     * Get wind gust speed value.
     *
     * @since 1.0.0
     * @return float|null
     */
    public function get_gust_value() {
        return $this->gust ? $this->gust->get_value() : null;
    }

    /**
     * Get wind gust formatted.
     *
     * @since 1.0.0
     * @param bool $include_unit Whether to include unit.
     * @return string|null
     */
    public function get_gust_formatted( $include_unit = true ) {
        return $this->gust ? $this->gust->get_formatted( $include_unit ) : null;
    }

    /**
     * Calculate cardinal direction from degrees.
     *
     * @since 1.0.0
     * @param int|null $degrees Direction in degrees.
     * @return string|null
     */
    private function calculate_cardinal_direction( $degrees ) {
        if ( null === $degrees ) {
            return null;
        }

        $cardinals = array(
            'N'  => array( 348.75, 360 ),
            'N'  => array( 0, 11.25 ),
            'NNE' => array( 11.25, 33.75 ),
            'NE'  => array( 33.75, 56.25 ),
            'ENE' => array( 56.25, 78.75 ),
            'E'   => array( 78.75, 101.25 ),
            'ESE' => array( 101.25, 123.75 ),
            'SE'  => array( 123.75, 146.25 ),
            'SSE' => array( 146.25, 168.75 ),
            'S'   => array( 168.75, 191.25 ),
            'SSW' => array( 191.25, 213.75 ),
            'SW'  => array( 213.75, 236.25 ),
            'WSW' => array( 236.25, 258.75 ),
            'W'   => array( 258.75, 281.25 ),
            'WNW' => array( 281.25, 303.75 ),
            'NW'  => array( 303.75, 326.25 ),
            'NNW' => array( 326.25, 348.75 ),
        );

        foreach ( $cardinals as $direction => $range ) {
            if ( $degrees >= $range[0] && $degrees < $range[1] ) {
                return $direction;
            }
        }

        return 'N';
    }

    /**
     * Get arrow icon for wind direction.
     *
     * @since 1.0.0
     * @param int $size Icon size in pixels.
     * @return string
     */
    public function get_direction_arrow( $size = 16 ) {
        if ( null === $this->direction_degrees ) {
            return '';
        }

        $angle = $this->direction_degrees - 90;
        
        return sprintf(
            '<span class="pw-wind-arrow" style="transform: rotate(%ddeg); display: inline-block;">
                <svg width="%d" height="%d" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L12 22M12 2L5 9M12 2L19 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>',
            $angle,
            $size,
            $size
        );
    }

    /**
     * Get Beaufort scale number (0-12) based on wind speed.
     *
     * @since 1.0.0
     * @return int
     */
    public function get_beaufort_scale() {
        $speed_ms = $this->get_speed_value();
        
        // Convert to m/s if needed.
        if ( $this->speed->get_unit() !== 'ms' && $this->speed->get_unit() !== 'm/s' ) {
            $speed_ms = $this->speed->convert_to( 'ms' )->get_value();
        }
        
        if ( $speed_ms < 0.3 ) return 0;
        if ( $speed_ms < 1.6 ) return 1;
        if ( $speed_ms < 3.4 ) return 2;
        if ( $speed_ms < 5.5 ) return 3;
        if ( $speed_ms < 8.0 ) return 4;
        if ( $speed_ms < 10.8 ) return 5;
        if ( $speed_ms < 13.9 ) return 6;
        if ( $speed_ms < 17.2 ) return 7;
        if ( $speed_ms < 20.8 ) return 8;
        if ( $speed_ms < 24.5 ) return 9;
        if ( $speed_ms < 28.5 ) return 10;
        if ( $speed_ms < 32.7 ) return 11;
        return 12;
    }

    /**
     * Get Beaufort scale description.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_beaufort_description() {
        $descriptions = array(
            0 => 'Calm',
            1 => 'Light air',
            2 => 'Light breeze',
            3 => 'Gentle breeze',
            4 => 'Moderate breeze',
            5 => 'Fresh breeze',
            6 => 'Strong breeze',
            7 => 'Near gale',
            8 => 'Gale',
            9 => 'Severe gale',
            10 => 'Storm',
            11 => 'Violent storm',
            12 => 'Hurricane',
        );
        
        $scale = $this->get_beaufort_scale();
        return isset( $descriptions[ $scale ] ) ? $descriptions[ $scale ] : 'Unknown';
    }

    /**
     * Check if wind speed is considered "calm".
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_calm() {
        return $this->get_beaufort_scale() <= 1;
    }

    /**
     * Check if wind speed is considered "strong" (gale force or higher).
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_strong() {
        return $this->get_beaufort_scale() >= 7;
    }

    /**
     * Create a Wind instance from API data.
     *
     * @since 1.0.0
     * @param array $data Wind data from API.
     * @param string $unit_system Unit system ('metric' or 'imperial').
     * @return self
     */
    public static function from_api_data( $data, $unit_system = 'metric' ) {
        $speed_value = isset( $data['speed'] ) ? (float) $data['speed'] : 0;
        $speed_unit = 'metric' === $unit_system ? 'ms' : 'mph';
        
        $speed = new Unit( $speed_value, $speed_unit, Unit::TYPE_WIND );
        $direction = isset( $data['deg'] ) ? (int) $data['deg'] : null;
        
        $gust = null;
        if ( isset( $data['gust'] ) ) {
            $gust = new Unit( (float) $data['gust'], $speed_unit, Unit::TYPE_WIND );
        }
        
        return new self( $speed, $direction, $gust );
    }

    /**
     * Convert to array.
     *
     * @since 1.0.0
     * @return array
     */
    public function to_array() {
        return array(
            'speed' => array(
                'value' => $this->get_speed_value(),
                'formatted' => $this->get_speed_formatted(),
                'unit' => $this->speed->get_unit_symbol(),
            ),
            'direction' => array(
                'degrees' => $this->direction_degrees,
                'cardinal' => $this->direction_cardinal,
                'arrow' => $this->get_direction_arrow(),
            ),
            'gust' => $this->gust ? array(
                'value' => $this->get_gust_value(),
                'formatted' => $this->get_gust_formatted(),
            ) : null,
            'beaufort' => array(
                'scale' => $this->get_beaufort_scale(),
                'description' => $this->get_beaufort_description(),
            ),
            'is_calm' => $this->is_calm(),
            'is_strong' => $this->is_strong(),
        );
    }
}