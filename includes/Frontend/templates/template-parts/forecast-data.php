<?php
/**
 * Forecast Data Template
 *
 * Displays hourly forecast data including time, icons, and
 * multiple weather metrics with tab-based visibility.
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
 * - $forecast_data: Array of forecast data
 * - $hourly_type: Forecast interval ('one-hour' or 'three-hour')
 * - $measurement_units: Measurement units configuration
 * - $time_settings: Time settings
 * - $forecast_icon_type: Icon set type
 */

if ( empty( $forecast_data ) ) {
    return;
}

// Get separator for min/max temperature.
$separator = ( isset( $hourly_type ) && 'three-hour' === $hourly_type ) ? '/' : '';

// CSS classes.
$wrapper_classes = array( 'pw-forecast-container' );
if ( ! empty( $attributes['forecast_custom_class'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['forecast_custom_class'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
    
    <?php foreach ( $forecast_data as $index => $data ) : ?>
        <?php
        // Process forecast data.
        $forecast = self::get_forecast_data( $data, $measurement_units, $time_settings );
        
        // Get icon URL based on icon set.
        $icon_code = isset( $forecast['icon'] ) ? $forecast['icon'] : '01d';
        $icon_set = isset( $forecast_icon_type ) ? $forecast_icon_type : 'forecast_icon_set_one';
        
        if ( 'forecast_icon_set_one' === $icon_set ) {
            $icon_url = PEARL_WEATHER_ASSETS_URL . 'images/icons/weather-icons/' . $icon_code . '.svg';
        } else {
            $icon_url = PEARL_WEATHER_ASSETS_URL . 'images/icons/weather-static-icons/' . $icon_code . '.svg';
        }
        
        $icon_url = apply_filters( 'pearl_weather_forecast_icon_url', $icon_url, $icon_code, $icon_set );
        
        // Forecast values.
        $time = isset( $forecast['times'] ) ? $forecast['times'] : '';
        $min_temp = isset( $forecast['min'] ) ? $forecast['min'] : '';
        $max_temp = isset( $forecast['max'] ) ? $forecast['max'] : '';
        $precipitation = isset( $forecast['precipitation'] ) ? $forecast['precipitation'] : '';
        $rain_chance = isset( $forecast['rain'] ) ? $forecast['rain'] : '';
        $wind = isset( $forecast['wind'] ) ? $forecast['wind'] : '';
        $humidity = isset( $forecast['humidity'] ) ? $forecast['humidity'] : '';
        $pressure = isset( $forecast['pressure'] ) ? $forecast['pressure'] : '';
        $snow = isset( $forecast['snow'] ) ? $forecast['snow'] : '';
        
        // Determine if this is a min/max forecast (3-hour) or single value.
        $has_min_max = ! empty( $max_temp );
        ?>
        
        <!-- Forecast Item -->
        <div class="pw-forecast-item" data-index="<?php echo esc_attr( $index ); ?>">
            
            <!-- Forecast Time -->
            <div class="pw-forecast-time">
                <span class="pw-time-text"><?php echo esc_html( $time ); ?></span>
            </div>
            
            <!-- Forecast Icon -->
            <div class="pw-forecast-icon <?php echo $has_min_max ? 'pw-has-minmax' : ''; ?>">
                <img src="<?php echo esc_url( $icon_url ); ?>" 
                     class="pw-forecast-img" 
                     alt="<?php esc_attr_e( 'Forecast icon', 'pearl-weather' ); ?>"
                     width="50" 
                     height="50"
                     loading="lazy"
                     decoding="async">
            </div>
            
            <!-- Forecast Values (Tab-based) -->
            <div class="pw-forecast-values">
                
                <!-- Temperature (Min/Max or Single) -->
                <span class="pw-forecast-value pw-temp-value active" data-forecast-type="temperature">
                    <?php if ( $has_min_max ) : ?>
                        <span class="pw-min-temp"><?php echo wp_kses_post( $min_temp ); ?></span>
                        <?php if ( ! empty( $separator ) ) : ?>
                            <span class="pw-temp-separator"><?php echo esc_html( $separator ); ?></span>
                        <?php endif; ?>
                        <span class="pw-max-temp"><?php echo wp_kses_post( $max_temp ); ?></span>
                    <?php else : ?>
                        <?php echo wp_kses_post( $min_temp ); ?>
                    <?php endif; ?>
                </span>
                
                <!-- Precipitation -->
                <span class="pw-forecast-value" data-forecast-type="precipitation">
                    <?php echo esc_html( $precipitation ); ?>
                </span>
                
                <!-- Rain Chance -->
                <span class="pw-forecast-value" data-forecast-type="rainchance">
                    <?php echo esc_html( $rain_chance ); ?>
                </span>
                
                <!-- Wind -->
                <span class="pw-forecast-value" data-forecast-type="wind">
                    <?php echo wp_kses_post( $wind ); ?>
                </span>
                
                <!-- Humidity -->
                <span class="pw-forecast-value" data-forecast-type="humidity">
                    <?php echo esc_html( $humidity ); ?>
                </span>
                
                <!-- Pressure -->
                <span class="pw-forecast-value" data-forecast-type="pressure">
                    <?php echo esc_html( $pressure ); ?>
                </span>
                
                <!-- Snow -->
                <span class="pw-forecast-value" data-forecast-type="snow">
                    <?php echo esc_html( $snow ); ?>
                </span>
                
            </div>
            
        </div>
    <?php endforeach; ?>
    
</div>

<style>
/* Forecast Container Styles */
.pw-forecast-container {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 16px;
}

/* Forecast Item */
.pw-forecast-item {
    flex: 1;
    min-width: 100px;
    text-align: center;
    background: rgba(0, 0, 0, 0.02);
    border-radius: 12px;
    padding: 12px 8px;
    transition: all 0.2s ease;
}

.pw-forecast-item:hover {
    background: rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}

/* Forecast Time */
.pw-forecast-time {
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 8px;
    color: #666;
}

/* Forecast Icon */
.pw-forecast-icon {
    margin-bottom: 8px;
}

.pw-forecast-icon img {
    width: 50px;
    height: 50px;
    object-fit: contain;
}

/* Forecast Values */
.pw-forecast-values {
    margin-top: 8px;
}

.pw-forecast-value {
    display: none;
    font-size: 14px;
    font-weight: 600;
}

.pw-forecast-value.active {
    display: block;
}

/* Temperature Specific */
.pw-min-temp,
.pw-max-temp {
    font-weight: 600;
}

.pw-temp-separator {
    margin: 0 2px;
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-forecast-container {
        gap: 12px;
    }
    
    .pw-forecast-item {
        min-width: 80px;
        padding: 8px 6px;
    }
    
    .pw-forecast-icon img {
        width: 40px;
        height: 40px;
    }
    
    .pw-forecast-time {
        font-size: 11px;
    }
    
    .pw-forecast-value {
        font-size: 12px;
    }
}
</style>

<script>
/**
 * Forecast tab switching functionality.
 */
(function() {
    const forecastItems = document.querySelectorAll('.pw-forecast-item');
    if (!forecastItems.length) return;
    
    // Get active forecast type from header or default to temperature
    const header = document.querySelector('.pw-forecast-header');
    let activeType = 'temperature';
    
    if (header) {
        const activeTab = header.querySelector('.pw-forecast-tab.pw-active');
        if (activeTab) {
            activeType = activeTab.getAttribute('data-forecast');
        }
    }
    
    // Set active values for all forecast items
    forecastItems.forEach(item => {
        const values = item.querySelectorAll('.pw-forecast-value');
        values.forEach(value => {
            const type = value.getAttribute('data-forecast-type');
            if (type === activeType) {
                value.classList.add('active');
            } else {
                value.classList.remove('active');
            }
        });
    });
})();
</script>