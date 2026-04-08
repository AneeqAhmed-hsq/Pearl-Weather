<?php
/**
 * Date and Time Template Part
 *
 * Displays current date and/or time with flexible formatting options.
 * Supports custom date/time formats, timezone handling, and responsive layouts.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Blocks/Templates/Parts
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template variables:
 * - $attributes: Block attributes (settings)
 * - $weather_data: Current weather data array
 * - $template: Template variant name
 */

// Display settings.
$show_date = isset( $attributes['showCurrentDate'] ) ? (bool) $attributes['showCurrentDate'] : true;
$show_time = isset( $attributes['showCurrentTime'] ) ? (bool) $attributes['showCurrentTime'] : true;

// Exit if nothing to show.
if ( ! $show_date && ! $show_time ) {
    return;
}

// Get date and time from weather data.
$weather_date = isset( $weather_data['date'] ) ? $weather_data['date'] : '';
$weather_time = isset( $weather_data['time'] ) ? $weather_data['time'] : '';
$timezone = isset( $weather_data['timezone'] ) ? $weather_data['timezone'] : '';

// Custom format settings.
$date_format = isset( $attributes['splwDateFormat'] ) ? sanitize_text_field( $attributes['splwDateFormat'] ) : 'M j, Y';
$custom_date_format = isset( $attributes['splwCustomDateFormat'] ) ? sanitize_text_field( $attributes['splwCustomDateFormat'] ) : 'F j, Y';
$time_format = isset( $attributes['splwTimeFormat'] ) ? sanitize_text_field( $attributes['splwTimeFormat'] ) : 'g:i A';
$timezone_setting = isset( $attributes['splwTimeZone'] ) ? sanitize_text_field( $attributes['splwTimeZone'] ) : 'auto';

// Use custom date format if selected.
if ( 'custom' === $date_format && ! empty( $custom_date_format ) ) {
    $date_format = $custom_date_format;
}

// Layout orientation.
$is_vertical = isset( $attributes['layoutOrientation'] ) && 'vertical' === $attributes['layoutOrientation'];
$show_separator = isset( $attributes['showDateTimeSeparator'] ) ? (bool) $attributes['showDateTimeSeparator'] : true;
$separator = isset( $attributes['dateTimeSeparator'] ) ? sanitize_text_field( $attributes['dateTimeSeparator'] ) : '•';

// Additional CSS classes.
$wrapper_classes = array(
    'pw-datetime',
    $is_vertical ? 'pw-datetime-vertical' : 'pw-datetime-horizontal',
);

if ( ! empty( $attributes['dateTimeCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['dateTimeCustomClass'] );
}

// Get weekday name (optional).
$show_weekday = isset( $attributes['showWeekday'] ) ? (bool) $attributes['showWeekday'] : false;
$weekday = '';
if ( $show_weekday && ! empty( $weather_date ) ) {
    $timestamp = strtotime( $weather_date );
    $weekday = date_i18n( 'l', $timestamp );
}

// Get time difference (e.g., "2 hours ago" - optional).
$show_time_diff = isset( $attributes['showTimeDifference'] ) ? (bool) $attributes['showTimeDifference'] : false;
$time_diff = '';
if ( $show_time_diff && ! empty( $weather_time ) ) {
    $time_diff = $this->get_time_difference( $weather_time );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
     data-date-format="<?php echo esc_attr( $date_format ); ?>"
     data-time-format="<?php echo esc_attr( $time_format ); ?>"
     data-timezone="<?php echo esc_attr( $timezone_setting ); ?>">
    
    <!-- Time Display -->
    <?php if ( $show_time && ! empty( $weather_time ) ) : ?>
        <div class="pw-time-wrapper">
            <?php if ( $show_weekday && ! empty( $weekday ) ) : ?>
                <span class="pw-weekday"><?php echo esc_html( $weekday ); ?></span>
                <?php if ( $show_separator && $show_date ) : ?>
                    <span class="pw-separator"><?php echo esc_html( $separator ); ?></span>
                <?php endif; ?>
            <?php endif; ?>
            
            <time class="pw-time" datetime="<?php echo esc_attr( date( 'H:i:s', strtotime( $weather_time ) ) ); ?>">
                <?php echo esc_html( $weather_time ); ?>
            </time>
            
            <?php if ( $show_time_diff && ! empty( $time_diff ) ) : ?>
                <span class="pw-time-diff">(<?php echo esc_html( $time_diff ); ?>)</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Date Display -->
    <?php if ( $show_date && ! empty( $weather_date ) ) : ?>
        <?php if ( $show_time && ! empty( $weather_time ) && $show_separator ) : ?>
            <span class="pw-datetime-separator"><?php echo esc_html( $separator ); ?></span>
        <?php endif; ?>
        
        <div class="pw-date-wrapper">
            <time class="pw-date" datetime="<?php echo esc_attr( date( 'Y-m-d', strtotime( $weather_date ) ) ); ?>">
                <?php echo esc_html( $weather_date ); ?>
            </time>
        </div>
    <?php endif; ?>
    
    <!-- Timezone Display (optional) -->
    <?php if ( isset( $attributes['showTimezone'] ) && $attributes['showTimezone'] && ! empty( $timezone ) ) : ?>
        <div class="pw-timezone-wrapper">
            <span class="pw-timezone-separator"><?php echo esc_html( $separator ); ?></span>
            <span class="pw-timezone"><?php echo esc_html( $timezone ); ?></span>
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Date & Time Styles */
.pw-datetime {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    line-height: 1.4;
    color: #666;
    flex-wrap: wrap;
}

/* Horizontal Layout (default) */
.pw-datetime-horizontal {
    flex-direction: row;
}

/* Vertical Layout (stacked) */
.pw-datetime-vertical {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
}

.pw-datetime-vertical .pw-datetime-separator {
    display: none;
}

/* Individual components */
.pw-time-wrapper,
.pw-date-wrapper {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.pw-weekday {
    font-weight: 500;
    text-transform: capitalize;
}

.pw-time {
    font-weight: 500;
}

.pw-date {
    font-weight: 400;
}

.pw-datetime-separator {
    opacity: 0.5;
    margin: 0 2px;
}

.pw-time-diff {
    font-size: 11px;
    opacity: 0.6;
    margin-left: 4px;
}

.pw-timezone {
    font-size: 11px;
    opacity: 0.6;
    text-transform: uppercase;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-datetime {
        font-size: 12px;
        gap: 4px;
    }
    
    .pw-datetime-horizontal {
        flex-wrap: wrap;
    }
}

/* Hover effect for tooltips (optional) */
.pw-time:hover,
.pw-date:hover {
    cursor: help;
}
</style>

<?php
/**
 * Helper method for time difference calculation.
 * This would normally be in a utilities class.
 */

if ( ! function_exists( 'get_time_difference' ) ) {
    /**
     * Get human-readable time difference.
     *
     * @param string $time Time string.
     * @return string
     */
    function get_time_difference( $time ) {
        $time_timestamp = strtotime( $time );
        $current_timestamp = current_time( 'timestamp' );
        $difference = $current_timestamp - $time_timestamp;
        
        if ( $difference < 0 ) {
            return '';
        }
        
        if ( $difference < 60 ) {
            return sprintf( _n( '%s second ago', '%s seconds ago', $difference, 'pearl-weather' ), $difference );
        }
        
        $minutes = floor( $difference / 60 );
        if ( $minutes < 60 ) {
            return sprintf( _n( '%s minute ago', '%s minutes ago', $minutes, 'pearl-weather' ), $minutes );
        }
        
        $hours = floor( $minutes / 60 );
        if ( $hours < 24 ) {
            return sprintf( _n( '%s hour ago', '%s hours ago', $hours, 'pearl-weather' ), $hours );
        }
        
        $days = floor( $hours / 24 );
        if ( $days < 7 ) {
            return sprintf( _n( '%s day ago', '%s days ago', $days, 'pearl-weather' ), $days );
        }
        
        $weeks = floor( $days / 7 );
        return sprintf( _n( '%s week ago', '%s weeks ago', $weeks, 'pearl-weather' ), $weeks );
    }
}

/**
 * Helper method for timezone offset formatting.
 */
if ( ! function_exists( 'format_timezone_offset' ) ) {
    /**
     * Format timezone offset as string (e.g., "UTC+5").
     *
     * @param int $offset Timezone offset in seconds.
     * @return string
     */
    function format_timezone_offset( $offset ) {
        if ( empty( $offset ) ) {
            return '';
        }
        
        $hours = floor( $offset / 3600 );
        $minutes = abs( ( $offset % 3600 ) / 60 );
        
        $sign = $hours >= 0 ? '+' : '-';
        $abs_hours = abs( $hours );
        
        if ( $minutes > 0 ) {
            return sprintf( 'UTC%s%d:%02d', $sign, $abs_hours, $minutes );
        }
        
        return sprintf( 'UTC%s%d', $sign, $abs_hours );
    }
}

/**
 * JavaScript for dynamic time updates (optional).
 * This allows the time to update in real-time without page refresh.
 */
if ( isset( $attributes['enableLiveTimeUpdate'] ) && $attributes['enableLiveTimeUpdate'] ) : ?>
<script>
(function() {
    const datetimeElement = document.querySelector('.pw-datetime');
    if (!datetimeElement) return;
    
    const timeElement = datetimeElement.querySelector('.pw-time');
    if (!timeElement) return;
    
    function updateTime() {
        const now = new Date();
        const options = { hour: 'numeric', minute: '2-digit', hour12: true };
        const formattedTime = now.toLocaleTimeString(undefined, options);
        if (timeElement) {
            timeElement.textContent = formattedTime;
            timeElement.setAttribute('datetime', now.toISOString());
        }
    }
    
    // Update time every minute
    setInterval(updateTime, 60000);
})();
</script>
<?php endif;