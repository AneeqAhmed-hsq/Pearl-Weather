<?php
/**
 * Forecast Temperature Renderer Template Part
 *
 * Displays temperature for forecast items (min/max or current)
 * with support for different separator styles and layouts.
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
 * - $pre_defined_separator: Pre-defined separator (for popups, etc.)
 * - $pre_defined_one_hourly: Pre-defined hourly flag (for popups, etc.)
 */

// Get temperature values.
$min_temp = isset( $single_forecast['min'] ) ? $single_forecast['min'] : ( isset( $single_forecast['temp_min'] ) ? $single_forecast['temp_min'] : '' );
$max_temp = isset( $single_forecast['max'] ) ? $single_forecast['max'] : ( isset( $single_forecast['temp_max'] ) ? $single_forecast['temp_max'] : '' );
$now_temp = isset( $single_forecast['now'] ) ? $single_forecast['now'] : ( isset( $single_forecast['temp'] ) ? $single_forecast['temp'] : '' );

// Determine if this is hourly data with single temperature.
$hourly_forecast_type = isset( $attributes['hourlyForecastType'] ) ? $attributes['hourlyForecastType'] : '3';
$is_one_hourly = ( 'hourly' === $data_type && '1' === $hourly_forecast_type );
$is_one_hourly = isset( $pre_defined_one_hourly ) ? $pre_defined_one_hourly : $is_one_hourly;

// Temperature unit.
$temp_unit = isset( $attributes['displayTemperatureUnit'] ) ? $attributes['displayTemperatureUnit'] : 'metric';
$unit_symbol = 'metric' === $temp_unit ? '°C' : '°F';

// Separator style based on template.
$separator_map = array(
    'vertical-one'   => 'slash',
    'vertical-two'   => 'slash',
    'horizontal-one' => 'slash',
    'vertical-three' => 'vertical-bar',
    'tabs-one'       => 'vertical-bar',
    'table-one'      => 'vertical-bar',
    'grid-card'      => 'gradient',
    'default'        => 'slash',
);

$separator = isset( $separator_map[ $template ] ) ? $separator_map[ $template ] : 'slash';
$temp_separator = isset( $pre_defined_separator ) ? $pre_defined_separator : $separator;

// Additional settings.
$show_unit = isset( $attributes['forecastShowUnit'] ) ? (bool) $attributes['forecastShowUnit'] : true;
$show_icon_on_popup = isset( $is_show_min_max_icon_on_popup ) ? $is_show_min_max_icon_on_popup : false;
$show_both_temps = isset( $attributes['forecastShowBothTemps'] ) ? (bool) $attributes['forecastShowBothTemps'] : true;

// CSS classes.
$wrapper_classes = array( 'pw-forecast-temperature' );

if ( 'gradient' === $temp_separator ) {
    $wrapper_classes[] = 'pw-temp-gradient-layout';
}

// Inline style for gradient colors (if applicable).
$gradient_style = '';
if ( 'gradient' === $temp_separator ) {
    $gradient_color = isset( $attributes['forecastGradientColor'] ) ? sanitize_hex_color( $attributes['forecastGradientColor'] ) : '#f26c0d';
    $gradient_style = 'style="--pw-gradient-color: ' . esc_attr( $gradient_color ) . '"';
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" <?php echo $gradient_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    
    <?php if ( $is_one_hourly ) : ?>
        <!-- Single Temperature (Hourly) -->
        <div class="pw-forecast-single-temp">
            <span class="pw-forecast-temp-value">
                <?php echo esc_html( round( (float) $now_temp ) ); ?>
            </span>
            <?php if ( $show_unit ) : ?>
                <span class="pw-forecast-temp-unit"><?php echo esc_html( $unit_symbol ); ?></span>
            <?php endif; ?>
        </div>
        
    <?php else : ?>
        <!-- Min/Max Temperature (Daily) -->
        <div class="pw-forecast-minmax-temp">
            
            <!-- Min Temperature -->
            <div class="pw-forecast-min-temp">
                <span class="pw-forecast-temp-value">
                    <?php echo esc_html( round( (float) $min_temp ) ); ?>
                </span>
                <?php if ( $show_unit ) : ?>
                    <span class="pw-forecast-temp-unit"><?php echo esc_html( $unit_symbol ); ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Separator -->
            <div class="pw-forecast-temp-separator pw-separator-<?php echo esc_attr( $temp_separator ); ?>">
                <?php if ( 'slash' === $temp_separator ) : ?>
                    <span class="pw-sep-slash">/</span>
                <?php elseif ( 'vertical-bar' === $temp_separator ) : ?>
                    <span class="pw-sep-bar">|</span>
                <?php elseif ( 'gradient' === $temp_separator ) : ?>
                    <span class="pw-sep-gradient">
                        <span class="pw-gradient-line"></span>
                    </span>
                <?php elseif ( 'dash' === $temp_separator ) : ?>
                    <span class="pw-sep-dash">–</span>
                <?php else : ?>
                    <span class="pw-sep-default">/</span>
                <?php endif; ?>
            </div>
            
            <!-- Max Temperature -->
            <div class="pw-forecast-max-temp">
                <span class="pw-forecast-temp-value">
                    <?php echo esc_html( round( (float) $max_temp ) ); ?>
                </span>
                <?php if ( $show_unit ) : ?>
                    <span class="pw-forecast-temp-unit"><?php echo esc_html( $unit_symbol ); ?></span>
                <?php endif; ?>
            </div>
            
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Forecast Temperature Styles */
.pw-forecast-temperature {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Single Temperature */
.pw-forecast-single-temp {
    display: inline-flex;
    align-items: baseline;
    gap: 2px;
}

/* Min/Max Temperature Container */
.pw-forecast-minmax-temp {
    display: inline-flex;
    align-items: baseline;
    gap: 4px;
    flex-wrap: wrap;
    justify-content: center;
}

.pw-forecast-min-temp,
.pw-forecast-max-temp {
    display: inline-flex;
    align-items: baseline;
    gap: 2px;
}

.pw-forecast-temp-value {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.2;
}

.pw-forecast-temp-unit {
    font-size: 10px;
    font-weight: 400;
    opacity: 0.7;
}

/* Separator Styles */
.pw-forecast-temp-separator {
    margin: 0 2px;
    font-weight: 500;
}

/* Slash Separator */
.pw-sep-slash {
    font-size: 12px;
    opacity: 0.5;
}

/* Vertical Bar Separator */
.pw-sep-bar {
    font-size: 12px;
    opacity: 0.5;
}

/* Dash Separator */
.pw-sep-dash {
    font-size: 12px;
    opacity: 0.5;
}

/* Gradient Separator */
.pw-temp-gradient-layout {
    position: relative;
}

.pw-sep-gradient {
    display: inline-flex;
    align-items: center;
    margin: 0 4px;
}

.pw-gradient-line {
    display: inline-block;
    width: 30px;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--pw-gradient-color, #f26c0d), transparent);
    border-radius: 2px;
}

/* Gradient layout specific styling */
.pw-temp-gradient-layout .pw-forecast-minmax-temp {
    gap: 0;
}

.pw-temp-gradient-layout .pw-forecast-min-temp,
.pw-temp-gradient-layout .pw-forecast-max-temp {
    flex: 1;
    justify-content: center;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-forecast-temp-value {
        font-size: 12px;
    }
    
    .pw-forecast-temp-unit {
        font-size: 9px;
    }
    
    .pw-gradient-line {
        width: 20px;
    }
}

/* Animation on load */
.pw-forecast-temperature {
    animation: pw-temp-fade-in 0.3s ease forwards;
}

@keyframes pw-temp-fade-in {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>