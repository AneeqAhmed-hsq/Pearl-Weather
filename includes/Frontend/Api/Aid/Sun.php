<?php
/**
 * Sun Class
 *
 * Represents sunrise and sunset times for a geographical location.
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
 * Class Sun
 *
 * Handles sunrise and sunset data with timezone support.
 *
 * @since 1.0.0
 */
class Sun {

    /**
     * Sunrise time.
     *
     * @var \DateTimeInterface
     */
    private $sunrise;

    /**
     * Sunset time.
     *
     * @var \DateTimeInterface
     */
    private $sunset;

    /**
     * Constructor.
     *
     * @param \DateTimeInterface $sunrise Sunrise time.
     * @param \DateTimeInterface $sunset  Sunset time.
     * @throws \InvalidArgumentException If sunset is before sunrise.
     */
    public function __construct( \DateTimeInterface $sunrise, \DateTimeInterface $sunset ) {
        if ( $sunset < $sunrise ) {
            throw new \InvalidArgumentException(
                __( 'Sunset cannot be before sunrise.', 'pearl-weather' )
            );
        }
        
        $this->sunrise = $sunrise;
        $this->sunset = $sunset;
    }

    /**
     * Get sunrise time.
     *
     * @since 1.0.0
     * @return \DateTimeInterface
     */
    public function get_sunrise() {
        return $this->sunrise;
    }

    /**
     * Get sunset time.
     *
     * @since 1.0.0
     * @return \DateTimeInterface
     */
    public function get_sunset() {
        return $this->sunset;
    }

    /**
     * Get sunrise time in specified format.
     *
     * @since 1.0.0
     * @param string $format PHP date format.
     * @return string
     */
    public function get_sunrise_formatted( $format = 'g:i A' ) {
        return $this->sunrise->format( $format );
    }

    /**
     * Get sunset time in specified format.
     *
     * @since 1.0.0
     * @param string $format PHP date format.
     * @return string
     */
    public function get_sunset_formatted( $format = 'g:i A' ) {
        return $this->sunset->format( $format );
    }

    /**
     * Get day length (time between sunrise and sunset).
     *
     * @since 1.0.0
     * @return \DateInterval
     */
    public function get_day_length() {
        return $this->sunrise->diff( $this->sunset );
    }

    /**
     * Get day length as formatted string.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_day_length_formatted() {
        $interval = $this->get_day_length();
        
        $parts = array();
        
        if ( $interval->h > 0 ) {
            $parts[] = sprintf( _n( '%d hour', '%d hours', $interval->h, 'pearl-weather' ), $interval->h );
        }
        
        if ( $interval->i > 0 ) {
            $parts[] = sprintf( _n( '%d minute', '%d minutes', $interval->i, 'pearl-weather' ), $interval->i );
        }
        
        return implode( ' ', $parts );
    }

    /**
     * Get day length in seconds.
     *
     * @since 1.0.0
     * @return int
     */
    public function get_day_length_seconds() {
        return $this->sunset->getTimestamp() - $this->sunrise->getTimestamp();
    }

    /**
     * Get day length in hours (decimal).
     *
     * @since 1.0.0
     * @return float
     */
    public function get_day_length_hours() {
        return round( $this->get_day_length_seconds() / 3600, 1 );
    }

    /**
     * Check if current time is between sunrise and sunset.
     *
     * @since 1.0.0
     * @param \DateTimeInterface|null $now Current time (defaults to now).
     * @return bool
     */
    public function is_daytime( \DateTimeInterface $now = null ) {
        if ( null === $now ) {
            $now = new \DateTimeImmutable( 'now', $this->sunrise->getTimezone() );
        }
        
        return $now >= $this->sunrise && $now <= $this->sunset;
    }

    /**
     * Get current sun position (0-100% of day progress).
     *
     * @since 1.0.0
     * @param \DateTimeInterface|null $now Current time (defaults to now).
     * @return float Percentage of day completed (0-100).
     */
    public function get_sun_position_percentage( \DateTimeInterface $now = null ) {
        if ( null === $now ) {
            $now = new \DateTimeImmutable( 'now', $this->sunrise->getTimezone() );
        }
        
        if ( ! $this->is_daytime( $now ) ) {
            return $now < $this->sunrise ? 0 : 100;
        }
        
        $total_seconds = $this->get_day_length_seconds();
        $elapsed_seconds = $now->getTimestamp() - $this->sunrise->getTimestamp();
        
        return round( ( $elapsed_seconds / $total_seconds ) * 100, 1 );
    }

    /**
     * Get sun angle in degrees (0-180).
     *
     * @since 1.0.0
     * @param \DateTimeInterface|null $now Current time (defaults to now).
     * @return int Sun angle in degrees (0-180).
     */
    public function get_sun_angle( \DateTimeInterface $now = null ) {
        if ( null === $now ) {
            $now = new \DateTimeImmutable( 'now', $this->sunrise->getTimezone() );
        }
        
        if ( ! $this->is_daytime( $now ) ) {
            return 0;
        }
        
        $percentage = $this->get_sun_position_percentage( $now );
        return (int) round( ( $percentage / 100 ) * 180 );
    }

    /**
     * Create a Sun instance from Unix timestamps.
     *
     * @since 1.0.0
     * @param int         $sunrise_timestamp Sunrise Unix timestamp.
     * @param int         $sunset_timestamp  Sunset Unix timestamp.
     * @param string|null $timezone          Timezone string.
     * @return self
     */
    public static function from_timestamps( $sunrise_timestamp, $sunset_timestamp, $timezone = null ) {
        $timezone_obj = $timezone ? new \DateTimeZone( $timezone ) : null;
        
        $sunrise = new \DateTimeImmutable( '@' . $sunrise_timestamp );
        $sunset = new \DateTimeImmutable( '@' . $sunset_timestamp );
        
        if ( $timezone_obj ) {
            $sunrise = $sunrise->setTimezone( $timezone_obj );
            $sunset = $sunset->setTimezone( $timezone_obj );
        }
        
        return new self( $sunrise, $sunset );
    }

    /**
     * Convert to array.
     *
     * @since 1.0.0
     * @return array
     */
    public function to_array() {
        return array(
            'sunrise' => $this->sunrise->format( 'Y-m-d H:i:s' ),
            'sunset'  => $this->sunset->format( 'Y-m-d H:i:s' ),
            'day_length_hours' => $this->get_day_length_hours(),
            'sunrise_timestamp' => $this->sunrise->getTimestamp(),
            'sunset_timestamp' => $this->sunset->getTimestamp(),
        );
    }
}