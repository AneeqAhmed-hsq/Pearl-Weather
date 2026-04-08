<?php
/**
 * Unit Class
 *
 * Represents a measured value with its unit, description, and precision.
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
 * Class Unit
 *
 * Handles measurement units with value, unit type, and formatting.
 *
 * @since 1.0.0
 */
class Unit implements \JsonSerializable {

    /**
     * Unit types.
     */
    const TYPE_TEMPERATURE = 'temperature';
    const TYPE_PRESSURE    = 'pressure';
    const TYPE_WIND        = 'wind';
    const TYPE_LENGTH      = 'length';
    const TYPE_PERCENT     = 'percent';
    const TYPE_GENERIC     = 'generic';

    /**
     * Numeric value.
     *
     * @var float
     */
    private $value;

    /**
     * Unit type (e.g., 'celsius', 'mph', 'hPa').
     *
     * @var string
     */
    private $unit;

    /**
     * Unit type category.
     *
     * @var string
     */
    private $type;

    /**
     * Description of the value.
     *
     * @var string
     */
    private $description;

    /**
     * Measurement precision (decimal places).
     *
     * @var int|null
     */
    private $precision;

    /**
     * Constructor.
     *
     * @param float      $value       Numeric value.
     * @param string     $unit        Unit identifier.
     * @param string     $type        Unit type category.
     * @param string     $description Description.
     * @param int|null   $precision   Decimal precision.
     */
    public function __construct( $value = 0.0, $unit = '', $type = self::TYPE_GENERIC, $description = '', $precision = null ) {
        $this->value = (float) $value;
        $this->unit = (string) $unit;
        $this->type = (string) $type;
        $this->description = (string) $description;
        $this->precision = null !== $precision ? (int) $precision : null;
    }

    /**
     * Get the value.
     *
     * @since 1.0.0
     * @return float
     */
    public function get_value() {
        return $this->value;
    }

    /**
     * Get the unit string.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_unit() {
        return $this->unit;
    }

    /**
     * Get the unit symbol (formatted for display).
     *
     * @since 1.0.0
     * @return string
     */
    public function get_unit_symbol() {
        // Temperature unit conversion.
        if ( 'celsius' === $this->unit || 'metric' === $this->unit ) {
            return '°C';
        }
        
        if ( 'fahrenheit' === $this->unit || 'imperial' === $this->unit ) {
            return '°F';
        }
        
        if ( 'kelvin' === $this->unit ) {
            return 'K';
        }
        
        // Pressure units.
        if ( 'hpa' === $this->unit || 'hPa' === $this->unit ) {
            return 'hPa';
        }
        
        if ( 'mb' === $this->unit ) {
            return 'mb';
        }
        
        if ( 'inhg' === $this->unit ) {
            return 'inHg';
        }
        
        // Wind units.
        if ( 'ms' === $this->unit || 'm/s' === $this->unit ) {
            return 'm/s';
        }
        
        if ( 'kmh' === $this->unit || 'km/h' === $this->unit ) {
            return 'km/h';
        }
        
        if ( 'mph' === $this->unit ) {
            return 'mph';
        }
        
        if ( 'kts' === $this->unit || 'kn' === $this->unit ) {
            return 'kn';
        }
        
        // Percent.
        if ( 'percent' === $this->unit || '%' === $this->unit ) {
            return '%';
        }
        
        return $this->unit;
    }

    /**
     * Get the unit type category.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * Get the description.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Get the precision.
     *
     * @since 1.0.0
     * @return int|null
     */
    public function get_precision() {
        return $this->precision;
    }

    /**
     * Get formatted value with unit.
     *
     * @since 1.0.0
     * @param bool $include_unit Whether to include the unit symbol.
     * @return string
     */
    public function get_formatted( $include_unit = true ) {
        $value = $this->get_rounded_value();
        
        if ( $include_unit && ! empty( $this->get_unit_symbol() ) ) {
            return $value . ' ' . $this->get_unit_symbol();
        }
        
        return (string) $value;
    }

    /**
     * Get rounded value based on precision.
     *
     * @since 1.0.0
     * @return float
     */
    public function get_rounded_value() {
        if ( null !== $this->precision ) {
            return round( $this->value, $this->precision );
        }
        
        // Default rounding based on type.
        switch ( $this->type ) {
            case self::TYPE_TEMPERATURE:
                return round( $this->value );
            case self::TYPE_PRESSURE:
                return round( $this->value );
            case self::TYPE_WIND:
                return round( $this->value, 1 );
            default:
                return round( $this->value, 2 );
        }
    }

    /**
     * Convert the unit to a different unit system.
     *
     * @since 1.0.0
     * @param string $target_unit Target unit.
     * @return self
     */
    public function convert_to( $target_unit ) {
        if ( $this->unit === $target_unit ) {
            return $this;
        }
        
        $value = $this->value;
        
        // Temperature conversions.
        if ( self::TYPE_TEMPERATURE === $this->type ) {
            if ( 'celsius' === $this->unit && 'fahrenheit' === $target_unit ) {
                $value = ( $this->value * 9 / 5 ) + 32;
            } elseif ( 'fahrenheit' === $this->unit && 'celsius' === $target_unit ) {
                $value = ( $this->value - 32 ) * 5 / 9;
            } elseif ( 'celsius' === $this->unit && 'kelvin' === $target_unit ) {
                $value = $this->value + 273.15;
            } elseif ( 'kelvin' === $this->unit && 'celsius' === $target_unit ) {
                $value = $this->value - 273.15;
            }
        }
        
        // Wind speed conversions.
        if ( self::TYPE_WIND === $this->type ) {
            $value = $this->convert_wind_speed( $target_unit );
        }
        
        return new self( $value, $target_unit, $this->type, $this->description, $this->precision );
    }

    /**
     * Convert wind speed between units.
     *
     * @since 1.0.0
     * @param string $target_unit Target unit.
     * @return float
     */
    private function convert_wind_speed( $target_unit ) {
        // Convert to m/s first.
        $ms = $this->value;
        
        switch ( $this->unit ) {
            case 'kmh':
            case 'km/h':
                $ms = $this->value / 3.6;
                break;
            case 'mph':
                $ms = $this->value * 0.44704;
                break;
            case 'kts':
            case 'kn':
                $ms = $this->value * 0.514444;
                break;
        }
        
        // Convert from m/s to target.
        switch ( $target_unit ) {
            case 'kmh':
            case 'km/h':
                return $ms * 3.6;
            case 'mph':
                return $ms / 0.44704;
            case 'kts':
            case 'kn':
                return $ms / 0.514444;
            default:
                return $ms;
        }
    }

    /**
     * Create a Unit instance for temperature.
     *
     * @since 1.0.0
     * @param float  $value    Temperature value.
     * @param string $unit     Unit ('celsius', 'fahrenheit', 'kelvin').
     * @param int    $precision Decimal places.
     * @return self
     */
    public static function temperature( $value, $unit = 'celsius', $precision = 0 ) {
        return new self( $value, $unit, self::TYPE_TEMPERATURE, '', $precision );
    }

    /**
     * Create a Unit instance for wind speed.
     *
     * @since 1.0.0
     * @param float  $value    Wind speed.
     * @param string $unit     Unit ('ms', 'kmh', 'mph', 'kts').
     * @param int    $precision Decimal places.
     * @return self
     */
    public static function wind( $value, $unit = 'ms', $precision = 1 ) {
        return new self( $value, $unit, self::TYPE_WIND, '', $precision );
    }

    /**
     * Create a Unit instance for pressure.
     *
     * @since 1.0.0
     * @param float  $value    Pressure.
     * @param string $unit     Unit ('hpa', 'mb', 'inhg').
     * @param int    $precision Decimal places.
     * @return self
     */
    public static function pressure( $value, $unit = 'hpa', $precision = 0 ) {
        return new self( $value, $unit, self::TYPE_PRESSURE, '', $precision );
    }

    /**
     * Create a Unit instance for percentage.
     *
     * @since 1.0.0
     * @param float $value Percentage value (0-100).
     * @return self
     */
    public static function percent( $value ) {
        return new self( $value, 'percent', self::TYPE_PERCENT, '', 0 );
    }

    /**
     * JSON serialize.
     *
     * @since 1.0.0
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return array(
            'value'       => $this->get_value(),
            'rounded'     => $this->get_rounded_value(),
            'formatted'   => $this->get_formatted(),
            'unit'        => $this->get_unit(),
            'unit_symbol' => $this->get_unit_symbol(),
            'type'        => $this->get_type(),
            'description' => $this->get_description(),
            'precision'   => $this->get_precision(),
        );
    }

    /**
     * Convert to string.
     *
     * @since 1.0.0
     * @return string
     */
    public function __toString() {
        return $this->get_formatted();
    }
}