<?php
/**
 * Forecast Date/Time Renderer Template Part
 *
 * Displays date/time for forecast items (hourly time or daily date)
 * with support for different template variants and formatting options.
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
 * - $single_forecast: Single forecast item data
 * - $data_type: Forecast type ('hourly' or 'daily')
 * - $template: Template variant name
 * - $is_layout_three: Whether this is layout three variant
 * - $description: Weather description (for layout three)
 * - $forecast_time: Forecast time value
 */

// Get forecast time from data.
$forecast_time = isset( $single_forecast['times'] ) ? $single_forecast['times'] : 
                ( isset( $single_forecast['time'] ) ? $single_forecast['time'] : 
                ( isset( $single_forecast['date'] ) ? $single_forecast['date'] : '' ) );

// Get date for daily forecasts.
$forecast_date = isset( $single_forecast['date'] ) ? $single_forecast['date'] : 
                ( isset( $single_forecast['day'] ) ? $single_forecast['day'] : '' );

// Description for layout three.
$forecast_desc = isset( $description ) ? $description : 
                ( isset( $single_forecast['description'] ) ? $single_forecast['description'] : 
                ( isset( $single_forecast['desc'] ) ? $single_forecast['desc'] : '' ) );

// Template mapping for special variants.
$template_map = array(
    'vertical-one'   => 'template-one',
    'vertical-three' => 'template-three',
    'vertical-two'   => 'template-two',
    'horizontal-one' => 'template-one',
    'tabs-one'       => 'template-one',
    'grid-card'      => 'template-grid',
);

$modified_template = isset( $template_map[ $template ] ) ? $template_map[ $template ] : $template;

// Formatting settings.
$time_format = isset( $attributes['splwTimeFormat'] ) ? sanitize_text_field( $attributes['splwTimeFormat'] ) : 'g:i A';
$date_format = isset( $attributes['splwDateFormat'] ) ? sanitize_text_field( $attributes['splwDateFormat'] ) : 'M j';
$custom_date_format = isset( $attributes['splwCustomDateFormat'] ) ? sanitize_text_field( $attributes['splwCustomDateFormat'] ) : 'F j';

// Use custom format if selected.
if ( 'custom' === $date_format && ! empty( $custom_date_format ) ) {
    $date_format = $custom_date_format;
}

// Additional settings.
$show_day_name = isset( $attributes['forecastShowDayName'] ) ? (bool) $attributes['forecastShowDayName'] : true;
$day_name_length = isset( $attributes['forecastDayNameLength'] ) ? sanitize_text_field( $attributes['forecastDayNameLength'] ) : 'short';
$show_ampm = isset( $attributes['forecastShowAMPM'] ) ? (bool) $attributes['forecastShowAMPM'] : true;

// CSS classes.
$wrapper_classes = array( 'pw-forecast-datetime' );

if ( 'hourly' === $data_type ) {
    $wrapper_classes[] = 'pw-forecast-time-only';
} else {
    $wrapper_classes[] = 'pw-forecast-date-only';
}

if ( isset( $is_layout_three ) && $is_layout_three ) {
    $wrapper_classes[] = 'pw-forecast-layout-three';
}

if ( ! empty( $attributes['forecastDateTimeCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['forecastDateTimeCustomClass'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" 
     data-template="<?php echo esc_attr( $modified_template ); ?>"
     data-type="<?php echo esc_attr( $data_type ); ?>">
    
    <?php if ( 'hourly' === $data_type ) : ?>
        <!-- Hourly Forecast: Time Display -->
        <div class="pw-forecast-time-wrapper">
            <span class="pw-forecast-time">
                <?php 
                // Format time if needed (convert from 24h to 12h if required).
                $display_time = $forecast_time;
                if ( ! $show_ampm && strpos( $forecast_time, 'AM' ) === false && strpos( $forecast_time, 'PM' ) === false ) {
                    // Already in 24h format or no AM/PM.
                    $display_time = $forecast_time;
                } elseif ( ! $show_ampm ) {
                    // Remove AM/PM.
                    $display_time = trim( str_replace( array( 'AM', 'PM', 'am', 'pm' ), '', $forecast_time ) );
                }
                echo esc_html( $display_time );
                ?>
            </span>
            
            <?php if ( $show_ampm && ( strpos( $forecast_time, 'AM' ) !== false || strpos( $forecast_time, 'PM' ) !== false ) ) : ?>
                <span class="pw-forecast-ampm">
                    <?php 
                    $ampm = strpos( $forecast_time, 'AM' ) !== false ? 'AM' : ( strpos( $forecast_time, 'PM' ) !== false ? 'PM' : '' );
                    echo esc_html( $ampm );
                    ?>
                </span>
            <?php endif; ?>
        </div>
        
    <?php else : ?>
        <!-- Daily Forecast: Date Display -->
        <div class="pw-forecast-date-wrapper">
            
            <!-- Day Name -->
            <?php if ( $show_day_name && ! empty( $forecast_date ) ) : ?>
                <?php
                $day_name = '';
                $timestamp = strtotime( $forecast_date );
                if ( $timestamp ) {
                    if ( 'full' === $day_name_length ) {
                        $day_name = date_i18n( 'l', $timestamp );
                    } else {
                        $day_name = date_i18n( 'D', $timestamp );
                    }
                }
                ?>
                <?php if ( ! empty( $day_name ) ) : ?>
                    <span class="pw-forecast-day-name"><?php echo esc_html( $day_name ); ?></span>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Date -->
            <?php if ( ! empty( $forecast_date ) ) : ?>
                <span class="pw-forecast-date">
                    <?php 
                    $timestamp = strtotime( $forecast_date );
                    if ( $timestamp ) {
                        echo esc_html( date_i18n( $date_format, $timestamp ) );
                    } else {
                        echo esc_html( $forecast_date );
                    }
                    ?>
                </span>
            <?php endif; ?>
            
            <!-- Description for Layout Three -->
            <?php if ( isset( $is_layout_three ) && $is_layout_three && ! empty( $forecast_desc ) ) : ?>
                <span class="pw-forecast-description pw-forecast-desc">
                    <?php echo esc_html( ucfirst( $forecast_desc ) ); ?>
                </span>
            <?php endif; ?>
            
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Forecast Date/Time Styles */
.pw-forecast-datetime {
    display: inline-flex;
    align-items: baseline;
    justify-content: center;
}

/* Time Only */
.pw-forecast-time-wrapper {
    display: inline-flex;
    align-items: baseline;
    gap: 2px;
}

.pw-forecast-time {
    font-size: 13px;
    font-weight: 500;
}

.pw-forecast-ampm {
    font-size: 10px;
    opacity: 0.6;
}

/* Date Only */
.pw-forecast-date-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.pw-forecast-day-name {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.7;
}

.pw-forecast-date {
    font-size: 11px;
    opacity: 0.6;
}

/* Layout Three Specific */
.pw-forecast-layout-three .pw-forecast-date-wrapper {
    flex-direction: row;
    gap: 6px;
    flex-wrap: wrap;
    justify-content: center;
}

.pw-forecast-layout-three .pw-forecast-description {
    font-size: 11px;
    font-weight: 500;
    text-transform: capitalize;
    color: var(--pw-primary-color, #f26c0d);
}

/* Responsive */
@media (max-width: 768px) {
    .pw-forecast-time {
        font-size: 11px;
    }
    
    .pw-forecast-day-name {
        font-size: 10px;
    }
    
    .pw-forecast-date {
        font-size: 10px;
    }
}

/* Template-specific adjustments */
[data-template="template-one"] .pw-forecast-date-wrapper {
    align-items: flex-start;
}

[data-template="template-three"] .pw-forecast-date-wrapper {
    align-items: flex-end;
}

[data-template="template-grid"] .pw-forecast-date-wrapper {
    align-items: center;
}
</style>