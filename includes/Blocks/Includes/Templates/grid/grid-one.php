<?php
/**
 * Weather Block Grid One Template
 *
 * Renders a grid layout combining current weather card,
 * weather map, and hourly forecast section.
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
 * - $forecast_data: Forecast data array
 * - $aqi_data: Air quality data array
 * - $template: Template variant name ('grid-one')
 * - $unique_id: Unique block identifier
 */

// Only render for grid-one template.
if ( ! isset( $template ) || 'grid-one' !== $template ) {
    return;
}

// Display settings.
$show_forecast = isset( $attributes['displayWeatherForecastData'] ) ? (bool) $attributes['displayWeatherForecastData'] : true;
$show_map = isset( $attributes['displayWeatherMap'] ) ? (bool) $attributes['displayWeatherMap'] : true;
$forecast_type = isset( $attributes['weatherForecastType'] ) ? sanitize_text_field( $attributes['weatherForecastType'] ) : 'hourly';
$forecast_title = isset( $attributes['hourlyTitle'] ) ? sanitize_text_field( $attributes['hourlyTitle'] ) : __( 'Hourly Forecast', 'pearl-weather' );

// CSS classes.
$wrapper_classes = array( 'pw-weather-grid-one' );

if ( ! empty( $attributes['gridOneCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['gridOneCustomClass'] );
}

// Grid layout settings.
$grid_columns = isset( $attributes['gridOneColumns'] ) ? (int) $attributes['gridOneColumns'] : 2;
$gap = isset( $attributes['gridOneGap'] ) ? (int) $attributes['gridOneGap'] : 20;

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
     style="--pw-grid-gap: <?php echo esc_attr( $gap ); ?>px; --pw-grid-columns: <?php echo esc_attr( $grid_columns ); ?>;">
    
    <!-- Top Row: Current Weather + Map -->
    <div class="pw-grid-one-top-row">
        
        <!-- Current Weather Card -->
        <div class="pw-grid-current-card">
            <?php
            $current_card_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/current-weather-card.php';
            if ( file_exists( $current_card_template ) ) {
                include $current_card_template;
            } else {
                // Fallback current weather display.
                $this->render_current_weather_fallback( $weather_data, $attributes );
            }
            ?>
        </div>
        
        <!-- Weather Map -->
        <?php if ( $show_map ) : ?>
            <div class="pw-grid-map">
                <?php
                $map_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/weather-map.php';
                if ( file_exists( $map_template ) ) {
                    include $map_template;
                } else {
                    // Fallback map placeholder.
                    ?>
                    <div class="pw-map-placeholder">
                        <div class="pw-map-loading">
                            <div class="pw-loading-spinner"></div>
                            <span><?php esc_html_e( 'Loading map...', 'pearl-weather' ); ?></span>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Forecast Section -->
    <?php if ( $show_forecast && ! empty( $forecast_data ) ) : ?>
        <div class="pw-grid-one-forecast-section pw-forecast-container" data-forecast-type="<?php echo esc_attr( $forecast_type ); ?>">
            
            <!-- Forecast Header -->
            <div class="pw-forecast-header-wrapper">
                <h4 class="pw-forecast-title"><?php echo esc_html( $forecast_title ); ?></h4>
                <?php
                // Include forecast header with tabs/select.
                $header_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/forecast-header.php';
                if ( file_exists( $header_template ) ) {
                    include $header_template;
                } else {
                    // Fallback header with tabs.
                    $forecast_options = array( 'temperature', 'humidity', 'wind', 'pressure' );
                    $active_forecast = 'temperature';
                    ?>
                    <div class="pw-forecast-tabs">
                        <?php foreach ( $forecast_options as $option ) : ?>
                            <button class="pw-forecast-tab <?php echo $option === $active_forecast ? 'pw-active' : ''; ?>" 
                                    data-forecast="<?php echo esc_attr( $option ); ?>">
                                <?php echo esc_html( ucfirst( $option ) ); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <?php
                }
                ?>
            </div>
            
            <!-- Forecast Data (Regular Layout) -->
            <div class="pw-forecast-data-wrapper">
                <?php
                $forecast_layout_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/regular-layout.php';
                if ( file_exists( $forecast_layout_template ) ) {
                    // Set required variables for the regular layout template.
                    $active_forecast_layout = 'regular';
                    $each_forecast_array = $forecast_data;
                    $data_type = $forecast_type;
                    include $forecast_layout_template;
                } else {
                    // Fallback forecast display.
                    $this->render_forecast_grid_fallback( $forecast_data, $attributes );
                }
                ?>
            </div>
            
        </div>
    <?php endif; ?>
    
    <!-- Footer -->
    <div class="pw-grid-one-footer">
        <?php
        $footer_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/footer.php';
        if ( file_exists( $footer_template ) ) {
            include $footer_template;
        } else {
            ?>
            <div class="pw-attribution">
                <a href="https://openweathermap.org/" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e( 'Weather data by OpenWeatherMap', 'pearl-weather' ); ?>
                </a>
            </div>
            <?php
        }
        ?>
    </div>
    
</div>

<style>
/* Grid One Styles */
.pw-weather-grid-one {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

/* Top Row */
.pw-grid-one-top-row {
    display: grid;
    grid-template-columns: repeat(var(--pw-grid-columns, 2), 1fr);
    gap: var(--pw-grid-gap, 20px);
    margin-bottom: 30px;
}

/* Current Weather Card */
.pw-grid-current-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

/* Map Container */
.pw-grid-map {
    background: #f5f5f5;
    border-radius: 12px;
    overflow: hidden;
    min-height: 300px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

/* Map Placeholder */
.pw-map-placeholder {
    width: 100%;
    height: 100%;
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

.pw-map-loading {
    text-align: center;
    color: #fff;
}

.pw-loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top-color: var(--pw-primary-color, #f26c0d);
    border-radius: 50%;
    animation: pw-spin 0.8s linear infinite;
    margin: 0 auto 12px;
}

@keyframes pw-spin {
    to { transform: rotate(360deg); }
}

/* Forecast Section */
.pw-grid-one-forecast-section {
    margin-top: 20px;
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

/* Forecast Header */
.pw-forecast-header-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.pw-forecast-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

/* Forecast Tabs */
.pw-forecast-tabs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.pw-forecast-tab {
    background: transparent;
    border: none;
    padding: 6px 16px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    border-radius: 20px;
    transition: all 0.2s ease;
    color: #666;
}

.pw-forecast-tab:hover {
    background: rgba(0, 0, 0, 0.05);
}

.pw-forecast-tab.pw-active {
    background: var(--pw-primary-color, #f26c0d);
    color: #fff;
}

/* Footer */
.pw-grid-one-footer {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    text-align: center;
    font-size: 11px;
    color: #757575;
}

/* Responsive */
@media (max-width: 992px) {
    .pw-grid-one-top-row {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .pw-forecast-header-wrapper {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 768px) {
    .pw-forecast-tabs {
        width: 100%;
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 4px;
    }
    
    .pw-forecast-tab {
        white-space: nowrap;
    }
    
    .pw-grid-one-forecast-section {
        padding: 16px;
    }
}
</style>

<?php
/**
 * Fallback rendering methods for grid-one template.
 */
if ( ! function_exists( 'render_forecast_grid_fallback' ) ) {
    /**
     * Render forecast grid fallback.
     *
     * @param array $forecast_data Forecast data.
     * @param array $attributes    Block attributes.
     */
    function render_forecast_grid_fallback( $forecast_data, $attributes ) {
        if ( empty( $forecast_data ) ) {
            echo '<div class="pw-forecast-empty">' . esc_html__( 'No forecast data available.', 'pearl-weather' ) . '</div>';
            return;
        }
        
        $temp_unit = isset( $attributes['displayTemperatureUnit'] ) && 'imperial' === $attributes['displayTemperatureUnit'] ? '°F' : '°C';
        ?>
        <div class="pw-forecast-grid-fallback">
            <div class="pw-forecast-row">
                <?php foreach ( array_slice( $forecast_data, 0, 8 ) as $item ) : ?>
                    <div class="pw-forecast-cell">
                        <div class="pw-forecast-time"><?php echo esc_html( $item['time'] ?? '' ); ?></div>
                        <?php if ( ! empty( $item['icon'] ) ) : ?>
                            <div class="pw-forecast-icon">
                                <img src="<?php echo esc_url( $item['icon'] ); ?>" alt="" width="40" height="40">
                            </div>
                        <?php endif; ?>
                        <div class="pw-forecast-temp"><?php echo esc_html( $item['temp'] ?? '--' ); ?><?php echo esc_html( $temp_unit ); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <style>
            .pw-forecast-grid-fallback {
                overflow-x: auto;
            }
            .pw-forecast-row {
                display: flex;
                gap: 16px;
                min-width: max-content;
            }
            .pw-forecast-cell {
                text-align: center;
                min-width: 80px;
                padding: 12px;
                background: rgba(0, 0, 0, 0.02);
                border-radius: 8px;
            }
            .pw-forecast-time {
                font-size: 12px;
                margin-bottom: 8px;
            }
            .pw-forecast-temp {
                font-size: 14px;
                font-weight: 600;
                margin-top: 8px;
            }
        </style>
        <?php
    }
}