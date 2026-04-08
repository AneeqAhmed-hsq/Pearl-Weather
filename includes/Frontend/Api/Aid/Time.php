<?php
/**
 * Time Period Class
 *
 * Represents a time period for weather forecasts with start, end, and day.
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
 * Class TimePeriod
 *
 * Handles time ranges for forecast periods.
 *
 * @since 1.0.0
 */
class TimePeriod {

    /**
     * Start time of the period.
     *
     * @var \DateTimeInterface
     */
    private $start;

    /**
     * End time of the period.
     *
     * @var \DateTimeInterface
     */
    private $end;

    /**
     * Day date (start of day).
     *
     * @var \DateTimeInterface
     */
    private $day;

    /**
     * Constructor.
     *
     * @param string|\DateTimeInterface $start Start time.
     * @param string|\DateTimeInterface|null $end   End time (optional).
     */
    public function __construct( $start, $end = null ) {
        $utc_timezone = new \DateTimeZone( 'UTC' );
        
        if ( null !== $end ) {
            // Range provided.
            $start_obj = $this->to_datetime( $start, $utc_timezone );
            $end_obj = $this->to_datetime( $end, $utc_timezone );
            $day_obj = new \DateTime( $start_obj->format( 'Y-m-d' ), $utc_timezone );
        } else {
            // Single time - create full day period.
            $start_obj = $this->to_datetime( $start, $utc_timezone );
            $day_obj = new \DateTime( $start_obj->format( 'Y-m-d' ), $utc_timezone );
            $end_obj = clone $start_obj;
            $end_obj = $end_obj->setTime( 23, 59, 59 );
        }

        $this->start = $start_obj;
        $this->end = $end_obj;
        $this->day = $day_obj;
    }

    /**
     * Convert input to DateTime object.
     *
     * @since 1.0.0
     * @param string|\DateTimeInterface $input    Input value.
     * @param \DateTimeZone             $timezone Timezone.
     * @return \DateTimeInterface
     */
    private function to_datetime( $input, \DateTimeZone $timezone ) {
        if ( $input instanceof \DateTimeInterface ) {
            return $input;
        }
        
        return new \DateTime( (string) $input, $timezone );
    }

    /**
     * Get start time.
     *
     * @since 1.0.0
     * @return \DateTimeInterface
     */
    public function get_start() {
        return $this->start;
    }

    /**
     * Get end time.
     *
     * @since 1.0.0
     * @return \DateTimeInterface
     */
    public function get_end() {
        return $this->end;
    }

    /**
     * Get day date.
     *
     * @since 1.0.0
     * @return \DateTimeInterface
     */
    public function get_day() {
        return $this->day;
    }

    /**
     * Get start time formatted.
     *
     * @since 1.0.0
     * @param string $format PHP date format.
     * @return string
     */
    public function get_start_formatted( $format = 'g:i A' ) {
        return $this->start->format( $format );
    }

    /**
     * Get end time formatted.
     *
     * @since 1.0.0
     * @param string $format PHP date format.
     * @return string
     */
    public function get_end_formatted( $format = 'g:i A' ) {
        return $this->end->format( $format );
    }

    /**
     * Get day formatted.
     *
     * @since 1.0.0
     * @param string $format PHP date format.
     * @return string
     */
    public function get_day_formatted( $format = 'M j, Y' ) {
        return $this->day->format( $format );
    }

    /**
     * Get day name.
     *
     * @since 1.0.0
     * @param bool $full Whether to return full name.
     * @return string
     */
    public function get_day_name( $full = false ) {
        $format = $full ? 'l' : 'D';
        return $this->day->format( $format );
    }

    /**
     * Get period duration in seconds.
     *
     * @since 1.0.0
     * @return int
     */
    public function get_duration_seconds() {
        return $this->end->getTimestamp() - $this->start->getTimestamp();
    }

    /**
     * Get period duration in hours.
     *
     * @since 1.0.0
     * @return float
     */
    public function get_duration_hours() {
        return round( $this->get_duration_seconds() / 3600, 1 );
    }

    /**
     * Check if the period contains a specific time.
     *
     * @since 1.0.0
     * @param \DateTimeInterface $time Time to check.
     * @return bool
     */
    public function contains( \DateTimeInterface $time ) {
        return $time >= $this->start && $time <= $this->end;
    }

    /**
     * Check if this is a full day period.
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_full_day() {
        $day_start = clone $this->day;
        $day_end = clone $this->day;
        $day_end = $day_end->setTime( 23, 59, 59 );
        
        return $this->start->format( 'Y-m-d H:i:s' ) === $day_start->format( 'Y-m-d H:i:s' )
            && $this->end->format( 'Y-m-d H:i:s' ) === $day_end->format( 'Y-m-d H:i:s' );
    }

    /**
     * Convert to array.
     *
     * @since 1.0.0
     * @return array
     */
    public function to_array() {
        return array(
            'start' => $this->start->format( 'Y-m-d H:i:s' ),
            'end'   => $this->end->format( 'Y-m-d H:i:s' ),
            'day'   => $this->day->format( 'Y-m-d' ),
            'day_name' => $this->get_day_name(),
            'duration_hours' => $this->get_duration_hours(),
            'is_full_day' => $this->is_full_day(),
        );
    }

    /**
     * Create a time period for a specific day.
     *
     * @since 1.0.0
     * @param string|\DateTimeInterface $date Date.
     * @return self
     */
    public static function for_day( $date ) {
        $utc = new \DateTimeZone( 'UTC' );
        $start = new \DateTime( $date, $utc );
        $start = $start->setTime( 0, 0, 0 );
        
        return new self( $start, null );
    }

    /**
     * Create a time period for a specific hour.
     *
     * @since 1.0.0
     * @param string|\DateTimeInterface $datetime Date and time.
     * @return self
     */
    public static function for_hour( $datetime ) {
        $utc = new \DateTimeZone( 'UTC' );
        $start = new \DateTime( $datetime, $utc );
        $end = clone $start;
        $end = $end->setTime( (int) $end->format( 'H' ), 59, 59 );
        $start = $start->setTime( (int) $start->format( 'H' ), 0, 0 );
        
        return new self( $start, $end );
    }
}