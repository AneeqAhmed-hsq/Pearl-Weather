<?php
/**
 * Weather Header Template
 *
 * Displays the weather header including location name,
 * current time, and date.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Templates/Parts
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template variables:
 * - $weather_data: Current weather data array
 * - $custom_name: Custom location name (optional)
 * - $show_location_address: Whether to show location name
 * - $show_time: Whether to show current time
 * - $show_date: Whether to show current date
 * - $splw_meta: Widget meta settings
 */

// Check if any header content should be displayed.
$show_location = isset( $show_location_address ) ? (bool) $show_location_address : true;
$show_time_flag = isset( $show_time ) ? (bool) $show_time : true;
$show_date_flag = isset( $show_date ) ? (bool) $show_date : true;

if ( empty( $weather_data ) || ( ! $show_location && ! $show_time_flag && ! $show_date_flag ) ) {
    return;
}

// Get location name.
$city = isset( $weather_data['city'] ) ? $weather_data['city'] : '';
$country = isset( $weather_data['country'] ) ? $weather_data['country'] : '';
$default_location = ! empty( $city ) && ! empty( $country ) ? $city . ', ' . $country : $city . $country;

$location_name = ! empty( $custom_name ) ? $custom_name : $default_location;

// Get time and date.
$time = isset( $weather_data['time'] ) ? $weather_data['time'] : '';
$date = isset( $weather_data['date'] ) ? $weather_data['date'] : '';

// Separator between time and date.
$separator = isset( $splw_meta['time_date_separator'] ) ? $splw_meta['time_date_separator'] : ', ';

// Layout orientation.
$layout = isset( $splw_meta['header_layout'] ) ? sanitize_text_field( $splw_meta['header_layout'] ) : 'inline';

// CSS classes.
$wrapper_classes = array( 'pw-weather-header' );
$wrapper_classes[] = 'pw-header-layout-' . $layout;

if ( ! empty( $splw_meta['header_custom_class'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $splw_meta['header_custom_class'] );
}

// Location icon setting.
$show_location_icon = isset( $splw_meta['show_location_icon'] ) ? (bool) $splw_meta['show_location_icon'] : true;

// Time/date icon setting.
$show_time_icon = isset( $splw_meta['show_time_icon'] ) ? (bool) $splw_meta['show_time_icon'] : true;

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
    <div class="pw-header-content">
        
        <!-- Location Name -->
        <?php if ( $show_location && ! empty( $location_name ) ) : ?>
            <div class="pw-location-wrapper">
                <?php if ( $show_location_icon ) : ?>
                    <span class="pw-location-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                            <circle cx="12" cy="9" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
                        </svg>
                    </span>
                <?php endif; ?>
                <span class="pw-location-name"><?php echo esc_html( $location_name ); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Date & Time -->
        <?php if ( ( $show_time_flag && ! empty( $time ) ) || ( $show_date_flag && ! empty( $date ) ) ) : ?>
            <div class="pw-datetime-wrapper">
                <?php if ( $show_time_icon ) : ?>
                    <span class="pw-datetime-icon" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </span>
                <?php endif; ?>
                <div class="pw-datetime">
                    <?php if ( $show_time_flag && ! empty( $time ) ) : ?>
                        <span class="pw-time"><?php echo esc_html( $time ); ?></span>
                    <?php endif; ?>
                    <?php if ( $show_time_flag && $show_date_flag && ! empty( $time ) && ! empty( $date ) ) : ?>
                        <span class="pw-datetime-separator"><?php echo esc_html( $separator ); ?></span>
                    <?php endif; ?>
                    <?php if ( $show_date_flag && ! empty( $date ) ) : ?>
                        <span class="pw-date"><?php echo esc_html( $date ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<style>
/* Weather Header Styles */
.pw-weather-header {
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.pw-header-content {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    flex-wrap: wrap;
    gap: 12px;
}

/* Layout: Inline (default) */
.pw-header-layout-inline .pw-header-content {
    flex-direction: row;
}

/* Layout: Stacked (vertical) */
.pw-header-layout-stacked .pw-header-content {
    flex-direction: column;
    align-items: flex-start;
}

/* Location Wrapper */
.pw-location-wrapper {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 18px;
    font-weight: 600;
}

.pw-location-icon {
    display: inline-flex;
    align-items: center;
    color: var(--pw-primary-color, #f26c0d);
}

/* Date/Time Wrapper */
.pw-datetime-wrapper {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #666;
}

.pw-datetime-icon {
    display: inline-flex;
    align-items: center;
    opacity: 0.6;
}

.pw-datetime {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}

.pw-datetime-separator {
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .pw-location-wrapper {
        font-size: 16px;
    }
    
    .pw-datetime-wrapper {
        font-size: 12px;
    }
}
</style>