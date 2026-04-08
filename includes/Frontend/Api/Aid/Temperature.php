<?php
/**
 * Temperature Class
 *
 * Represents temperature data including current, minimum, and maximum values.
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
 * Class Temperature
 *
 * Handles current, min, and max temperatures with unit conversion.
 *
 * @since 1.0.0
 */
class Temperature {

    /**
     * Current temperature.
     *
     * @var Unit
     */
    private $current;

    /**
     * Minimum temperature.
     *
     * @var Unit
     */
    private $min;

    /**
     * Maximum temperature.
     *
     * @var Unit
     */
    private $max;

    /**
     * Constructor.
     *
     * @param Unit $current Current temperature.
     * @param Unit $min     Minimum temperature.
     * @param Unit $max     Maximum temperature.
     */
    public function __construct( Unit $current, Unit $min, Unit $max ) {
        $this->current = $current;
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * Get current temperature.
     *
     * @since 1.0.0
     * @return Unit
     */
    public function get_current() {
        return $this->current;
    }

    /**
     * Get minimum temperature.
     *
     * @since 1.0.0
     * @return Unit
     */
    public function get_min() {
        return $this->min;
    }

    /**
     * Get maximum temperature.
     *
     * @since 1.0.0
     * @return Unit
     */
    public function get_max() {
        return $this->max;
    }

    /**
     * Get current temperature value.
     *
     * @since 1.0.0
     * @return float|null
     */
    public function get_current_value() {
        return $this->current->get_value();
    }

    /**
     * Get minimum temperature value.
     *
     * @since 1.0.0
     * @return float|null
     */
    public function get_min_value() {
        return $this->min->get_value();
    }

    /**
     * Get maximum temperature value.
     *
     * @since 1.0.0
     * @return float|null
     */
    public function get_max_value() {
        return $this->max->get_value();
    }

    /**
     * Get current temperature formatted.
     *
     * @since 1.0.0
     * @param bool $include_unit Whether to include the unit symbol.
     * @return string
     */
    public function get_current_formatted( $include_unit = true ) {
        return $this->current->get_formatted( $include_unit );
    }

    /**
     * Get temperature range as formatted string (min - max).
     *
     * @since 1.0.0
     * @param string $separator Separator between min and max.
     * @param bool   $include_unit Whether to include unit.
     * @return string
     */
    public function get_range_formatted( $separator = ' - ', $include_unit = true ) {
        $min_formatted = $this->min->get_formatted( false );
        $max_formatted = $this->max->get_formatted( false );
        $unit = $include_unit ? $this->current->get_unit_symbol() : '';
        
        return $min_formatted . $separator . $max_formatted . $unit;
    }

    /**
     * Get temperature unit symbol.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_unit() {
        return $this->current->get_unit_symbol();
    }

    /**
     * Convert all temperatures to a different unit.
     *
     * @since 1.0.0
     * @param string $target_unit Target unit ('celsius', 'fahrenheit', 'kelvin').
     * @return self
     */
    public function convert_to( $target_unit ) {
        return new self(
            $this->current->convert_to( $target_unit ),
            $this->min->convert_to( $target_unit ),
            $this->max->convert_to( $target_unit )
        );
    }

    /**
     * Create a Temperature instance from values.
     *
     * @since 1.0.0
     * @param float  $current Current temperature.
     * @param float  $min     Minimum temperature.
     * @param float  $max     Maximum temperature.
     * @param string $unit    Unit ('celsius', 'fahrenheit', 'kelvin').
     * @return self
     */
    public static function from_values( $current, $min, $max, $unit = 'celsius' ) {
        return new self(
            new Unit( $current, $unit, 'temperature' ),
            new Unit( $min, $unit, 'temperature' ),
            new Unit( $max, $unit, 'temperature' )
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
            'current' => array(
                'value' => $this->get_current_value(),
                'formatted' => $this->get_current_formatted(),
                'unit' => $this->get_unit(),
            ),
            'min' => array(
                'value' => $this->get_min_value(),
                'formatted' => $this->min->get_formatted(),
            ),
            'max' => array(
                'value' => $this->get_max_value(),
                'formatted' => $this->max->get_formatted(),
            ),
            'range' => $this->get_range_formatted(),
        );
    }

    /**
     * Convert to string (returns current temperature).
     *
     * @since 1.0.0
     * @return string
     */
    public function __toString() {
        return $this->get_current_formatted();
    }
}