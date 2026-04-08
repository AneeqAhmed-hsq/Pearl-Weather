<?php
/**
 * AQI Card Summary Header Template Part
 *
 * Displays the location name and current date/time for the AQI card.
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
 * - $visitors_location: Whether this is visitor's location (auto-detect)
 * - $shortcode_id: Shortcode ID for filtering
 */

// Display settings.
$show_location = isset( $attributes['showLocationName'] ) ? (bool) $attributes['showLocationName'] : true;
$show_date = isset( $attributes['showCurrentDate'] ) ? (bool) $attributes['showCurrentDate'] : true;
$show_time = isset( $attributes['showCurrentTime'] ) ? (bool) $attributes['showCurrentTime'] : true;

// Location name.
$custom_location = isset( $attributes['customCityName'] ) ? sanitize_text_field( $attributes['customCityName'] ) : '';
$city = isset( $weather_data['city'] ) ? $weather_data['city'] : '';
$country = isset( $weather_data['country'] ) ? $weather_data['country'] : '';

// Determine the location name to display.
$location_name = '';

if ( ! empty( $custom_location ) && empty( $visitors_location ) ) {
    // Use custom location name if provided and not auto-detected.
    $location_name = $custom_location;
} elseif ( ! empty( $city ) ) {
    // Use city and country from weather data.
    $location_name = ! empty( $country ) ? $city . ', ' . $country : $city;
} else {
    $location_name = __( 'Location not set', 'pearl-weather' );
}

/**
 * Filter the city name displayed in AQI card.
 *
 * @since 1.0.0
 * @param string $location_name Location name.
 * @param string $shortcode_id  Shortcode ID.
 */
$location_name = apply_filters( 'pearl_weather_aqi_city_name', $location_name, $shortcode_id ?? '' );

// Date and time.
$date = isset( $weather_data['date'] ) ? $weather_data['date'] : '';
$time = isset( $weather_data['time'] ) ? $weather_data['time'] : '';

// Date/time formatting.
$date_format = isset( $attributes['splwDateFormat'] ) ? sanitize_text_field( $attributes['splwDateFormat'] ) : 'M j, Y';
$custom_date_format = isset( $attributes['splwCustomDateFormat'] ) ? sanitize_text_field( $attributes['splwCustomDateFormat'] ) : 'F j, Y';
$time_format = isset( $attributes['splwTimeFormat'] ) ? sanitize_text_field( $attributes['splwTimeFormat'] ) : 'g:i A';

// Use custom date format if selected.
if ( 'custom' === $date_format && ! empty( $custom_date_format ) ) {
    $date_format = $custom_date_format;
}

// Format date if timestamp is available.
if ( ! empty( $date ) && is_numeric( $date ) ) {
    $date = date_i18n( $date_format, $date );
} elseif ( ! empty( $date ) ) {
    $timestamp = strtotime( $date );
    if ( $timestamp ) {
        $date = date_i18n( $date_format, $timestamp );
    }
}

// Format time.
if ( ! empty( $time ) && is_numeric( $time ) ) {
    $time = date_i18n( $time_format, $time );
} elseif ( ! empty( $time ) ) {
    $timestamp = strtotime( $time );
    if ( $timestamp ) {
        $time = date_i18n( $time_format, $timestamp );
    }
}

// CSS classes.
$wrapper_classes = array( 'pw-aqi-location-time' );

if ( ! empty( $attributes['aqiHeaderCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['aqiHeaderCustomClass'] );
}

// Layout orientation.
$layout = isset( $attributes['aqiHeaderLayout'] ) ? sanitize_text_field( $attributes['aqiHeaderLayout'] ) : 'horizontal';

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" 
     data-layout="<?php echo esc_attr( $layout ); ?>">
    
    <!-- Location Name -->
    <?php if ( $show_location && ! empty( $location_name ) ) : ?>
        <div class="pw-aqi-location">
            <svg class="pw-location-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                <circle cx="12" cy="9" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
            </svg>
            <span class="pw-location-name"><?php echo esc_html( $location_name ); ?></span>
            
            <!-- Custom Location Badge -->
            <?php if ( ! empty( $custom_location ) && empty( $visitors_location ) ) : ?>
                <span class="pw-custom-badge" title="<?php esc_attr_e( 'Custom location name', 'pearl-weather' ); ?>">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L15 8.5L22 9.5L17 14L18.5 21L12 17.5L5.5 21L7 14L2 9.5L9 8.5L12 2Z" fill="currentColor"/>
                    </svg>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Date and Time -->
    <?php if ( ( $show_date && ! empty( $date ) ) || ( $show_time && ! empty( $time ) ) ) : ?>
        <div class="pw-aqi-datetime">
            
            <?php if ( $show_time && ! empty( $time ) ) : ?>
                <div class="pw-time-wrapper">
                    <svg class="pw-time-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <span class="pw-time"><?php echo esc_html( $time ); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ( $show_date && ! empty( $date ) ) : ?>
                <?php if ( $show_time && ! empty( $time ) ) : ?>
                    <span class="pw-datetime-separator">•</span>
                <?php endif; ?>
                <div class="pw-date-wrapper">
                    <svg class="pw-date-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M8 2V6M16 2V6M3 10H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <span class="pw-date"><?php echo esc_html( $date ); ?></span>
                </div>
            <?php endif; ?>
            
        </div>
    <?php endif; ?>
    
</div>

<style>
/* AQI Location & Time Styles */
.pw-aqi-location-time {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

/* Vertical Layout */
[data-layout="vertical"] {
    flex-direction: column;
    align-items: flex-start;
}

/* Location Styles */
.pw-aqi-location {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 16px;
    font-weight: 600;
}

.pw-location-icon {
    flex-shrink: 0;
}

.pw-location-name {
    white-space: normal;
    word-break: break-word;
}

/* Custom Badge */
.pw-custom-badge {
    display: inline-flex;
    align-items: center;
    margin-left: 4px;
    color: #f39c12;
    cursor: help;
}

/* Date/Time Styles */
.pw-aqi-datetime {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    font-size: 13px;
    color: #666;
}

.pw-time-wrapper,
.pw-date-wrapper {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.pw-time-icon,
.pw-date-icon {
    opacity: 0.6;
}

.pw-datetime-separator {
    opacity: 0.4;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-aqi-location-time {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .pw-aqi-location {
        font-size: 14px;
    }
    
    .pw-aqi-datetime {
        font-size: 11px;
    }
}

/* Hover Effects */
.pw-aqi-location:hover .pw-location-icon {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}
</style>