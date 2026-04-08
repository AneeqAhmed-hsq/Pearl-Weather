<?php
/**
 * Single Forecast Item Renderer
 *
 * Renders a single forecast item including date/time, icon,
 * temperature, and additional weather data.
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
 * - $measurement_units: Measurement units array
 * - $time_settings: Time settings array
 * - $active_forecast_layout: Active layout ('regular' or 'swiper')
 * - $data_type: Forecast type ('hourly' or 'daily')
 * - $is_layout_three: Whether this is layout three variant
 * - $index: Current index in forecast loop
 */

// Format forecast data.
$formatted_forecast = format_single_forecast( $single_forecast, $measurement_units, $time_settings );

// Extract formatted values.
$time = $formatted_forecast['time'] ?? '';
$date = $formatted_forecast['date'] ?? '';
$day = $formatted_forecast['day'] ?? '';
$icon_url = $formatted_forecast['icon_url'] ?? '';
$icon_alt = $formatted_forecast['condition'] ?? '';
$temp = $formatted_forecast['temp'] ?? '';
$temp_min = $formatted_forecast['temp_min'] ?? '';
$temp_max = $formatted_forecast['temp_max'] ?? '';
$temp_unit = $formatted_forecast['temp_unit'] ?? '°C';
$precipitation = $formatted_forecast['precipitation'] ?? '';
$rain_chance = $formatted_forecast['rain_chance'] ?? '';
$wind = $formatted_forecast['wind'] ?? '';
$humidity = $formatted_forecast['humidity'] ?? '';
$pressure = $formatted_forecast['pressure'] ?? '';
$snow = $formatted_forecast['snow'] ?? '';
$uv_index = $formatted_forecast['uv_index'] ?? '';
$clouds = $formatted_forecast['clouds'] ?? '';

// Determine if this is a swiper layout.
$is_swiper = isset( $active_forecast_layout ) && 'swiper' === $active_forecast_layout;

// CSS classes.
$container_classes = array( 'pw-forecast-item' );

if ( $is_swiper ) {
    $container_classes[] = 'swiper-slide';
}

if ( isset( $is_layout_three ) && $is_layout_three ) {
    $container_classes[] = 'pw-forecast-layout-three';
}

if ( 'daily' === $data_type ) {
    $container_classes[] = 'pw-forecast-daily';
} else {
    $container_classes[] = 'pw-forecast-hourly';
}

if ( ! empty( $attributes['forecastItemCustomClass'] ) ) {
    $container_classes[] = sanitize_html_class( $attributes['forecastItemCustomClass'] );
}

// Inline style for gradient separator (if applicable).
$gradient_style = '';
if ( isset( $position_in_percentage ) && ! empty( $position_in_percentage ) ) {
    $gradient_style = sprintf(
        '--pw-gradient-position: %d%%; --pw-gradient-color: %s;',
        (int) $position_in_percentage,
        isset( $attributes['forecastGradientColor'] ) ? esc_attr( $attributes['forecastGradientColor'] ) : '#f26c0d'
    );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $container_classes ) ); ?>" 
     data-index="<?php echo esc_attr( $index ?? 0 ); ?>"
     data-forecast-type="<?php echo esc_attr( $data_type ?? 'hourly' ); ?>"
     <?php echo ! empty( $gradient_style ) ? 'style="' . esc_attr( $gradient_style ) . '"' : ''; ?>>
    
    <!-- Layout Three: Row Reverse Wrapper -->
    <?php if ( isset( $is_layout_three ) && $is_layout_three ) : ?>
        <div class="pw-forecast-row-reverse">
    <?php endif; ?>
    
    <!-- Date/Time Section -->
    <div class="pw-forecast-datetime-section">
        <?php
        $forecast_time = 'hourly' === $data_type ? $time : $date;
        $description = $formatted_forecast['condition'] ?? '';
        $is_layout_three_flag = isset( $is_layout_three ) ? $is_layout_three : false;
        
        // Include date-time template part.
        $datetime_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/forecast-date-time.php';
        if ( file_exists( $datetime_template ) ) {
            include $datetime_template;
        } else {
            // Fallback date/time display.
            ?>
            <div class="pw-forecast-datetime-fallback">
                <?php if ( 'hourly' === $data_type ) : ?>
                    <span class="pw-forecast-time"><?php echo esc_html( $time ); ?></span>
                <?php else : ?>
                    <span class="pw-forecast-date"><?php echo esc_html( $date ); ?></span>
                    <?php if ( ! empty( $day ) ) : ?>
                        <span class="pw-forecast-day"><?php echo esc_html( $day ); ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php
        }
        ?>
    </div>
    
    <!-- Weather Icon Section -->
    <div class="pw-forecast-icon-section">
        <?php
        $show_description = isset( $is_layout_three ) && $is_layout_three;
        
        // Include forecast-icon template part.
        $icon_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/forecast-icon.php';
        if ( file_exists( $icon_template ) ) {
            include $icon_template;
        } else {
            // Fallback icon display.
            if ( ! empty( $icon_url ) ) :
            ?>
                <div class="pw-forecast-icon-fallback">
                    <img src="<?php echo esc_url( $icon_url ); ?>" 
                         alt="<?php echo esc_attr( $icon_alt ); ?>"
                         loading="lazy"
                         width="40" height="40">
                </div>
            <?php endif;
        }
        ?>
    </div>
    
    <!-- Layout Three: Close Row Reverse Wrapper -->
    <?php if ( isset( $is_layout_three ) && $is_layout_three ) : ?>
        </div>
    <?php endif; ?>
    
    <!-- Forecast Values Section -->
    <div class="pw-forecast-values-section">
        
        <!-- Temperature -->
        <div class="pw-forecast-temperature-wrapper">
            <?php
            // Include forecast-temperature template part.
            $temp_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/forecast-temperature.php';
            if ( file_exists( $temp_template ) ) {
                include $temp_template;
            } else {
                // Fallback temperature display.
                ?>
                <div class="pw-forecast-temperature-fallback">
                    <?php if ( 'daily' === $data_type && ! empty( $temp_min ) && ! empty( $temp_max ) ) : ?>
                        <span class="pw-forecast-temp-min"><?php echo esc_html( $temp_min ); ?></span>
                        <span class="pw-forecast-temp-sep">/</span>
                        <span class="pw-forecast-temp-max"><?php echo esc_html( $temp_max ); ?></span>
                    <?php else : ?>
                        <span class="pw-forecast-temp"><?php echo esc_html( $temp ); ?></span>
                    <?php endif; ?>
                    <span class="pw-forecast-temp-unit"><?php echo esc_html( $temp_unit ); ?></span>
                </div>
                <?php
            }
            ?>
        </div>
        
        <!-- Hidden Values for Live Filtering -->
        <div class="pw-forecast-hidden-values" style="display: none;">
            <span class="pw-forecast-value-precipitation" data-type="precipitation"><?php echo esc_html( $precipitation ); ?></span>
            <span class="pw-forecast-value-rainchance" data-type="rainchance"><?php echo esc_html( $rain_chance ); ?></span>
            <span class="pw-forecast-value-wind" data-type="wind"><?php echo wp_kses_post( $wind ); ?></span>
            <span class="pw-forecast-value-humidity" data-type="humidity"><?php echo esc_html( $humidity ); ?></span>
            <span class="pw-forecast-value-pressure" data-type="pressure"><?php echo esc_html( $pressure ); ?></span>
            <span class="pw-forecast-value-snow" data-type="snow"><?php echo esc_html( $snow ); ?></span>
            <span class="pw-forecast-value-uv-index" data-type="uv_index"><?php echo esc_html( $uv_index ); ?></span>
            <span class="pw-forecast-value-clouds" data-type="clouds"><?php echo esc_html( $clouds ); ?></span>
        </div>
        
    </div>
    
</div>

<style>
/* Single Forecast Item Styles */
.pw-forecast-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px;
    background: rgba(0, 0, 0, 0.02);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.pw-forecast-item:hover {
    background: rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}

/* Swiper Slide Layout */
.swiper-slide .pw-forecast-item {
    flex-direction: column;
    text-align: center;
    height: 100%;
}

/* Layout Three (Row Reverse) */
.pw-forecast-layout-three .pw-forecast-row-reverse {
    display: flex;
    flex-direction: row-reverse;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.pw-forecast-layout-three .pw-forecast-datetime-section,
.pw-forecast-layout-three .pw-forecast-icon-section {
    flex: 0 0 auto;
}

.pw-forecast-layout-three .pw-forecast-values-section {
    flex: 1;
    text-align: right;
}

/* Daily Forecast Specific */
.pw-forecast-daily {
    flex-direction: column;
    align-items: flex-start;
}

/* Values Section */
.pw-forecast-values-section {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* Temperature Display */
.pw-forecast-temperature-wrapper {
    flex-shrink: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-forecast-item {
        padding: 8px;
        gap: 8px;
    }
    
    .pw-forecast-layout-three .pw-forecast-row-reverse {
        gap: 8px;
    }
}

/* Animation */
.pw-forecast-item {
    animation: pw-forecast-slide-in 0.3s ease forwards;
    opacity: 0;
    transform: translateY(10px);
}

@keyframes pw-forecast-slide-in {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Stagger animation delay */
.pw-forecast-item:nth-child(1) { animation-delay: 0.05s; }
.pw-forecast-item:nth-child(2) { animation-delay: 0.10s; }
.pw-forecast-item:nth-child(3) { animation-delay: 0.15s; }
.pw-forecast-item:nth-child(4) { animation-delay: 0.20s; }
.pw-forecast-item:nth-child(5) { animation-delay: 0.25s; }
.pw-forecast-item:nth-child(6) { animation-delay: 0.30s; }
.pw-forecast-item:nth-child(7) { animation-delay: 0.35s; }
.pw-forecast-item:nth-child(8) { animation-delay: 0.40s; }
</style>

<?php
/**
 * Helper function to format single forecast data.
 */
if ( ! function_exists( 'format_single_forecast' ) ) {
    /**
     * Format a single forecast item for display.
     *
     * @param array $forecast          Raw forecast data.
     * @param array $measurement_units Measurement units.
     * @param array $time_settings     Time settings.
     * @return array
     */
    function format_single_forecast( $forecast, $measurement_units, $time_settings ) {
        $formatted = array();
        
        // Date/Time.
        $formatted['time'] = isset( $forecast['times'] ) ? $forecast['times'] : 
                            ( isset( $forecast['time'] ) ? $forecast['time'] : '' );
        $formatted['date'] = isset( $forecast['date'] ) ? $forecast['date'] : '';
        $formatted['day'] = isset( $forecast['day'] ) ? $forecast['day'] : '';
        
        // Temperature.
        $temp_scale = isset( $measurement_units['temperature_scale'] ) ? $measurement_units['temperature_scale'] : 'metric';
        $formatted['temp_unit'] = 'metric' === $temp_scale ? '°C' : '°F';
        
        $formatted['temp'] = isset( $forecast['temp'] ) ? round( $forecast['temp'] ) : '';
        $formatted['temp_min'] = isset( $forecast['min'] ) ? round( $forecast['min'] ) : 
                                ( isset( $forecast['temp_min'] ) ? round( $forecast['temp_min'] ) : '' );
        $formatted['temp_max'] = isset( $forecast['max'] ) ? round( $forecast['max'] ) : 
                                ( isset( $forecast['temp_max'] ) ? round( $forecast['temp_max'] ) : '' );
        
        // Weather condition.
        $formatted['condition'] = isset( $forecast['desc'] ) ? $forecast['desc'] : 
                                 ( isset( $forecast['description'] ) ? $forecast['description'] : '' );
        
        // Icon.
        $icon_code = isset( $forecast['icon'] ) ? $forecast['icon'] : '';
        if ( ! empty( $icon_code ) && function_exists( 'pearl_weather_get_forecast_icon_url' ) ) {
            $icon_set = isset( $attributes['forecastDataIconType'] ) ? $attributes['forecastDataIconType'] : 'forecast_icon_set_one';
            $formatted['icon_url'] = pearl_weather_get_forecast_icon_url( $icon_code, $icon_set );
        }
        
        // Additional data.
        $formatted['precipitation'] = isset( $forecast['precipitation'] ) ? $forecast['precipitation'] : '';
        $formatted['rain_chance'] = isset( $forecast['rain'] ) ? $forecast['rain'] : '';
        $formatted['wind'] = isset( $forecast['wind'] ) ? $forecast['wind'] : '';
        $formatted['humidity'] = isset( $forecast['humidity'] ) ? $forecast['humidity'] : '';
        $formatted['pressure'] = isset( $forecast['pressure'] ) ? $forecast['pressure'] : '';
        $formatted['snow'] = isset( $forecast['snow'] ) ? $forecast['snow'] : '';
        $formatted['uv_index'] = isset( $forecast['uvi'] ) ? $forecast['uvi'] : ( isset( $forecast['uv_index'] ) ? $forecast['uv_index'] : '' );
        $formatted['clouds'] = isset( $forecast['clouds'] ) ? $forecast['clouds'] : '';
        
        return $formatted;
    }
}