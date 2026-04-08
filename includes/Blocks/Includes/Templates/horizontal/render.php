<?php
/**
 * Weather Block Horizontal Template
 *
 * Renders weather data in a horizontal layout with current conditions,
 * additional data panels, and hourly forecast.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Blocks/Templates
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
 * - $forecast_data: Hourly forecast data array
 * - $aqi_data: Air quality data array (if available)
 * - $template: Template variant (horizontal-one, horizontal-two, etc.)
 */

// Template variant (default to 'horizontal-one').
$template_variant = isset( $template ) ? sanitize_text_field( $template ) : 'horizontal-one';

// Display settings.
$show_forecast = isset( $attributes['displayWeatherForecastData'] ) ? (bool) $attributes['displayWeatherForecastData'] : true;
$show_last_update = isset( $attributes['displayDateUpdateTime'] ) ? (bool) $attributes['displayDateUpdateTime'] : true;
$show_attribution = isset( $attributes['displayWeatherAttribution'] ) ? (bool) $attributes['displayWeatherAttribution'] : true;

// Color settings.
$enable_global_style = isset( $attributes['enableTemplateGlobalStyle'] ) ? (bool) $attributes['enableTemplateGlobalStyle'] : false;
$template_primary_color = isset( $attributes['templatePrimaryColor'] ) ? sanitize_hex_color( $attributes['templatePrimaryColor'] ) : '';
$forecast_data_color = isset( $attributes['forecastDataColor'] ) ? sanitize_hex_color( $attributes['forecastDataColor'] ) : '';

// Chart text color (for forecast graphs).
$chart_text_color = $enable_global_style ? $template_primary_color : ( empty( $forecast_data_color ) ? $template_primary_color : $forecast_data_color );

// Process forecast data options.
$forecast_options = array();
if ( isset( $attributes['forecastData'] ) && is_array( $attributes['forecastData'] ) ) {
    foreach ( $attributes['forecastData'] as $option ) {
        if ( isset( $option['value'] ) && true === $option['value'] ) {
            $forecast_options[] = isset( $option['name'] ) ? sanitize_text_field( $option['name'] ) : '';
        }
    }
}
$active_forecast = ! empty( $forecast_options ) ? $forecast_options[0] : 'temperature';

?>

<div class="pw-weather-template-wrapper pw-weather-<?php echo esc_attr( $template_variant ); ?>-wrapper"
     data-chart-color="<?php echo esc_attr( $chart_text_color ); ?>">
    
    <?php switch ( $template_variant ) : 
        
        case 'horizontal-one':
            ?>
            <!-- Horizontal One Layout -->
            <div class="pw-weather-horizontal-top pw-d-flex pw-justify-between pw-w-full">
                
                <!-- Left Column: Current Weather -->
                <div class="pw-weather-horizontal-left-wrapper">
                    <?php
                    // Include current weather template part.
                    $current_weather_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/current-weather.php';
                    if ( file_exists( $current_weather_template ) ) {
                        include $current_weather_template;
                    } else {
                        $this->render_current_weather_fallback( $weather_data, $attributes );
                    }
                    ?>
                </div>
                
                <!-- Right Column: Additional Data -->
                <?php
                $additional_data_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/additional-data.php';
                if ( file_exists( $additional_data_template ) ) {
                    include $additional_data_template;
                } else {
                    $this->render_additional_data_fallback( $weather_data, $attributes );
                }
                ?>
                
            </div>
            
            <!-- Hourly Forecast Section -->
            <?php if ( $show_forecast && ! empty( $forecast_data ) && is_array( $forecast_data ) ) : ?>
                <div class="pw-weather-forecast-section pw-forecast-container" data-forecast-type="hourly">
                    
                    <!-- Forecast Header with Tabs/Select -->
                    <?php
                    $forecast_header_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-header.php';
                    if ( file_exists( $forecast_header_template ) ) {
                        include $forecast_header_template;
                    } else {
                        $this->render_forecast_header_fallback( $forecast_options, $active_forecast );
                    }
                    ?>
                    
                    <!-- Forecast Data Display -->
                    <div class="pw-forecast-data-wrapper" data-active-forecast="<?php echo esc_attr( $active_forecast ); ?>">
                        <?php
                        $forecast_layout_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-regular-layout.php';
                        if ( file_exists( $forecast_layout_template ) ) {
                            include $forecast_layout_template;
                        } else {
                            $this->render_forecast_data_fallback( $forecast_data, $active_forecast, $attributes );
                        }
                        ?>
                    </div>
                    
                </div>
            <?php endif; ?>
            <?php break; ?>
        
        <?php case 'horizontal-two': ?>
            <!-- Horizontal Two Layout (alternate arrangement) -->
            <div class="pw-weather-horizontal-layout-two">
                <div class="pw-weather-main-row pw-d-flex pw-flex-wrap">
                    
                    <!-- Current Weather - Compact -->
                    <div class="pw-current-weather-compact">
                        <?php
                        $compact_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/current-weather-compact.php';
                        if ( file_exists( $compact_template ) ) {
                            include $compact_template;
                        } else {
                            $this->render_compact_weather_fallback( $weather_data, $attributes );
                        }
                        ?>
                    </div>
                    
                    <!-- Additional Data Grid -->
                    <div class="pw-additional-data-grid">
                        <?php
                        $additional_grid_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/additional-data-grid.php';
                        if ( file_exists( $additional_grid_template ) ) {
                            include $additional_grid_template;
                        } else {
                            $this->render_additional_grid_fallback( $weather_data, $attributes );
                        }
                        ?>
                    </div>
                    
                </div>
                
                <!-- Forecast (if enabled) -->
                <?php if ( $show_forecast && ! empty( $forecast_data ) ) : ?>
                    <div class="pw-forecast-horizontal-scroll">
                        <div class="pw-forecast-scroll-container">
                            <?php foreach ( $forecast_data as $hour ) : ?>
                                <div class="pw-forecast-scroll-item">
                                    <div class="pw-forecast-time"><?php echo esc_html( $hour['time'] ?? '' ); ?></div>
                                    <div class="pw-forecast-icon">
                                        <?php if ( ! empty( $hour['icon'] ) ) : ?>
                                            <img src="<?php echo esc_url( $hour['icon'] ); ?>" alt="<?php echo esc_attr( $hour['condition'] ?? '' ); ?>">
                                        <?php endif; ?>
                                    </div>
                                    <div class="pw-forecast-temp"><?php echo esc_html( $hour['temp'] ?? '--' ); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php break; ?>
        
        <?php default: ?>
            <!-- Fallback horizontal layout -->
            <div class="pw-weather-horizontal-fallback">
                <div class="pw-current-weather-simple">
                    <div class="pw-temp-display">
                        <span class="pw-temp-value"><?php echo esc_html( $weather_data['temperature'] ?? '--' ); ?></span>
                        <span class="pw-temp-unit"><?php echo esc_html( $weather_data['temp_unit'] ?? '°C' ); ?></span>
                    </div>
                    <div class="pw-weather-condition"><?php echo esc_html( $weather_data['description'] ?? '' ); ?></div>
                </div>
            </div>
            <?php break; ?>
            
    <?php endswitch; ?>
    
</div>

<!-- Footer Section (Last Updated + Attribution) -->
<div class="pw-weather-footer">
    
    <?php if ( $show_last_update && ! empty( $weather_data['updated_time'] ) ) : ?>
        <div class="pw-last-updated">
            <span class="pw-updated-label"><?php esc_html_e( 'Last updated:', 'pearl-weather' ); ?></span>
            <span class="pw-updated-time"><?php echo esc_html( $weather_data['updated_time'] ); ?></span>
        </div>
    <?php endif; ?>
    
    <?php if ( $show_attribution ) : ?>
        <div class="pw-weather-attribution">
            <a href="https://openweathermap.org/" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e( 'Weather data by OpenWeatherMap', 'pearl-weather' ); ?>
            </a>
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Horizontal Layout Styles */
.pw-weather-horizontal-top {
    gap: 20px;
    flex-wrap: wrap;
}

.pw-weather-horizontal-left-wrapper {
    flex: 1;
    min-width: 250px;
}

/* Horizontal Two Layout */
.pw-weather-horizontal-layout-two .pw-weather-main-row {
    gap: 20px;
}

.pw-current-weather-compact {
    flex: 1;
    min-width: 200px;
}

.pw-additional-data-grid {
    flex: 2;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 12px;
}

/* Forecast Horizontal Scroll */
.pw-forecast-horizontal-scroll {
    margin-top: 20px;
    overflow-x: auto;
    scrollbar-width: thin;
}

.pw-forecast-scroll-container {
    display: flex;
    gap: 16px;
    padding: 8px 4px;
}

.pw-forecast-scroll-item {
    flex: 0 0 auto;
    text-align: center;
    min-width: 70px;
    padding: 10px;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.02);
}

.pw-forecast-time {
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 8px;
}

.pw-forecast-icon img {
    width: 40px;
    height: 40px;
}

.pw-forecast-temp {
    font-size: 14px;
    font-weight: 600;
    margin-top: 8px;
}

/* Footer Styles */
.pw-weather-footer {
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    font-size: 11px;
    color: #757575;
}

.pw-last-updated {
    display: flex;
    gap: 4px;
}

.pw-weather-attribution a {
    color: #757575;
    text-decoration: none;
}

.pw-weather-attribution a:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-weather-horizontal-top {
        flex-direction: column;
    }
    
    .pw-weather-footer {
        flex-direction: column;
        text-align: center;
    }
    
    .pw-additional-data-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php
/**
 * Fallback rendering methods for when template parts are missing.
 * These are embedded directly in the template for resilience.
 */

if ( ! function_exists( 'render_current_weather_fallback' ) ) {
    /**
     * Render current weather fallback.
     *
     * @param array $weather_data Weather data.
     * @param array $attributes   Block attributes.
     */
    function render_current_weather_fallback( $weather_data, $attributes ) {
        $show_location = isset( $attributes['showLocationName'] ) ? (bool) $attributes['showLocationName'] : true;
        ?>
        <div class="pw-current-weather-fallback">
            <?php if ( $show_location && ! empty( $weather_data['city'] ) ) : ?>
                <div class="pw-location"><?php echo esc_html( $weather_data['city'] ); ?></div>
            <?php endif; ?>
            <div class="pw-temp">
                <span class="pw-temp-value"><?php echo esc_html( $weather_data['temperature'] ?? '--' ); ?></span>
                <span class="pw-temp-unit"><?php echo esc_html( $weather_data['temp_unit'] ?? '°C' ); ?></span>
            </div>
            <div class="pw-condition"><?php echo esc_html( $weather_data['description'] ?? '' ); ?></div>
        </div>
        <?php
    }
}

if ( ! function_exists( 'render_additional_data_fallback' ) ) {
    /**
     * Render additional data fallback.
     *
     * @param array $weather_data Weather data.
     * @param array $attributes   Block attributes.
     */
    function render_additional_data_fallback( $weather_data, $attributes ) {
        ?>
        <div class="pw-additional-data-fallback pw-d-grid pw-grid-cols-2 pw-gap-10px">
            <?php if ( ! empty( $weather_data['humidity'] ) ) : ?>
                <div class="pw-data-item">
                    <span class="pw-data-label"><?php esc_html_e( 'Humidity', 'pearl-weather' ); ?></span>
                    <span class="pw-data-value"><?php echo esc_html( $weather_data['humidity'] ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( ! empty( $weather_data['wind'] ) ) : ?>
                <div class="pw-data-item">
                    <span class="pw-data-label"><?php esc_html_e( 'Wind', 'pearl-weather' ); ?></span>
                    <span class="pw-data-value"><?php echo esc_html( $weather_data['wind'] ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( ! empty( $weather_data['pressure'] ) ) : ?>
                <div class="pw-data-item">
                    <span class="pw-data-label"><?php esc_html_e( 'Pressure', 'pearl-weather' ); ?></span>
                    <span class="pw-data-value"><?php echo esc_html( $weather_data['pressure'] ); ?></span>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

if ( ! function_exists( 'render_forecast_header_fallback' ) ) {
    /**
     * Render forecast header fallback.
     *
     * @param array  $forecast_options Forecast options.
     * @param string $active_forecast  Active forecast type.
     */
    function render_forecast_header_fallback( $forecast_options, $active_forecast ) {
        if ( empty( $forecast_options ) ) {
            return;
        }
        ?>
        <div class="pw-forecast-header">
            <div class="pw-forecast-tabs">
                <?php foreach ( $forecast_options as $option ) : ?>
                    <button class="pw-forecast-tab <?php echo $option === $active_forecast ? 'pw-active' : ''; ?>" 
                            data-forecast="<?php echo esc_attr( $option ); ?>">
                        <?php echo esc_html( ucfirst( str_replace( '_', ' ', $option ) ) ); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}

if ( ! function_exists( 'render_forecast_data_fallback' ) ) {
    /**
     * Render forecast data fallback.
     *
     * @param array  $forecast_data   Forecast data.
     * @param string $active_forecast Active forecast type.
     * @param array  $attributes      Block attributes.
     */
    function render_forecast_data_fallback( $forecast_data, $active_forecast, $attributes ) {
        if ( empty( $forecast_data ) ) {
            return;
        }
        ?>
        <div class="pw-forecast-data-list">
            <?php foreach ( $forecast_data as $hour ) : ?>
                <div class="pw-forecast-item" data-forecast-type="<?php echo esc_attr( $active_forecast ); ?>">
                    <div class="pw-forecast-time"><?php echo esc_html( $hour['time'] ?? '' ); ?></div>
                    <div class="pw-forecast-value">
                        <?php
                        if ( 'temperature' === $active_forecast ) {
                            echo esc_html( $hour['temp'] ?? '--' );
                        } elseif ( 'humidity' === $active_forecast ) {
                            echo esc_html( $hour['humidity'] ?? '--' ) . '%';
                        } elseif ( 'wind' === $active_forecast ) {
                            echo esc_html( $hour['wind'] ?? '--' );
                        } elseif ( 'pressure' === $active_forecast ) {
                            echo esc_html( $hour['pressure'] ?? '--' );
                        } else {
                            echo esc_html( $hour[ $active_forecast ] ?? '--' );
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

if ( ! function_exists( 'render_compact_weather_fallback' ) ) {
    /**
     * Render compact weather fallback.
     *
     * @param array $weather_data Weather data.
     * @param array $attributes   Block attributes.
     */
    function render_compact_weather_fallback( $weather_data, $attributes ) {
        ?>
        <div class="pw-compact-weather">
            <div class="pw-compact-temp">
                <span class="pw-temp-value"><?php echo esc_html( $weather_data['temperature'] ?? '--' ); ?></span>
                <span class="pw-temp-unit"><?php echo esc_html( $weather_data['temp_unit'] ?? '°C' ); ?></span>
            </div>
            <div class="pw-compact-condition"><?php echo esc_html( $weather_data['description'] ?? '' ); ?></div>
        </div>
        <?php
    }
}

if ( ! function_exists( 'render_additional_grid_fallback' ) ) {
    /**
     * Render additional data grid fallback.
     *
     * @param array $weather_data Weather data.
     * @param array $attributes   Block attributes.
     */
    function render_additional_grid_fallback( $weather_data, $attributes ) {
        $items = array(
            'humidity'   => __( 'Humidity', 'pearl-weather' ),
            'pressure'   => __( 'Pressure', 'pearl-weather' ),
            'wind'       => __( 'Wind', 'pearl-weather' ),
            'visibility' => __( 'Visibility', 'pearl-weather' ),
        );
        ?>
        <div class="pw-additional-grid">
            <?php foreach ( $items as $key => $label ) : ?>
                <?php if ( ! empty( $weather_data[ $key ] ) ) : ?>
                    <div class="pw-grid-item">
                        <span class="pw-item-label"><?php echo esc_html( $label ); ?></span>
                        <span class="pw-item-value"><?php echo esc_html( $weather_data[ $key ] ); ?></span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
    }
}