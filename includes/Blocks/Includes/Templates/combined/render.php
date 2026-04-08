<?php
/**
 * Weather Block Combined Template
 *
 * Renders a combined weather layout that merges current weather,
 * hourly forecast, daily forecast, and additional data into
 * a single cohesive display.
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
 * - $forecast_data: Forecast data array (hourly/daily)
 * - $aqi_data: Air quality data array (if available)
 * - $block_name: Block name
 * - $unique_id: Unique block identifier
 */

// Display settings.
$show_current = isset( $attributes['displayCurrentWeather'] ) ? (bool) $attributes['displayCurrentWeather'] : true;
$show_forecast = isset( $attributes['displayWeatherForecastData'] ) ? (bool) $attributes['displayWeatherForecastData'] : true;
$show_additional = isset( $attributes['displayAdditionalData'] ) ? (bool) $attributes['displayAdditionalData'] : true;
$show_aqi = isset( $attributes['showAirQuality'] ) ? (bool) $attributes['showAirQuality'] : false;
$show_sun_info = isset( $attributes['showSunInfo'] ) ? (bool) $attributes['showSunInfo'] : true;
$show_attribution = isset( $attributes['displayWeatherAttribution'] ) ? (bool) $attributes['displayWeatherAttribution'] : true;

// Layout settings.
$combined_layout = isset( $attributes['combinedLayout'] ) ? sanitize_text_field( $attributes['combinedLayout'] ) : 'grid';
$primary_column = isset( $attributes['combinedPrimaryColumn'] ) ? sanitize_text_field( $attributes['combinedPrimaryColumn'] ) : 'current';

// CSS classes.
$wrapper_classes = array( 'pw-weather-combined' );
$wrapper_classes[] = 'pw-combined-layout-' . $combined_layout;

if ( ! empty( $attributes['combinedCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['combinedCustomClass'] );
}

?>

<div id="<?php echo esc_attr( $unique_id ); ?>" class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
    
    <?php if ( 'grid' === $combined_layout ) : ?>
        
        <!-- Grid Layout -->
        <div class="pw-combined-grid">
            
            <!-- Primary Column -->
            <div class="pw-combined-primary">
                <?php if ( 'current' === $primary_column && $show_current ) : ?>
                    <div class="pw-combined-current">
                        <?php
                        $current_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/current-weather.php';
                        if ( file_exists( $current_template ) ) {
                            include $current_template;
                        } else {
                            $this->render_current_weather_fallback( $weather_data, $attributes );
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if ( 'aqi' === $primary_column && $show_aqi && ! empty( $aqi_data ) ) : ?>
                    <div class="pw-combined-aqi">
                        <?php
                        $aqi_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/aqi-minimal/render.php';
                        if ( file_exists( $aqi_template ) ) {
                            include $aqi_template;
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Secondary Column -->
            <div class="pw-combined-secondary">
                
                <?php if ( $show_forecast && ! empty( $forecast_data ) ) : ?>
                    <div class="pw-combined-forecast">
                        <h4 class="pw-section-title"><?php esc_html_e( 'Forecast', 'pearl-weather' ); ?></h4>
                        <div class="pw-forecast-compact">
                            <?php foreach ( array_slice( $forecast_data, 0, 5 ) as $item ) : ?>
                                <div class="pw-forecast-compact-item">
                                    <div class="pw-forecast-time"><?php echo esc_html( $item['time'] ?? '' ); ?></div>
                                    <?php if ( ! empty( $item['icon'] ) ) : ?>
                                        <img src="<?php echo esc_url( $item['icon'] ); ?>" alt="" width="30" height="30">
                                    <?php endif; ?>
                                    <div class="pw-forecast-temp"><?php echo esc_html( $item['temp'] ?? '--' ); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ( $show_additional ) : ?>
                    <div class="pw-combined-additional">
                        <h4 class="pw-section-title"><?php esc_html_e( 'Details', 'pearl-weather' ); ?></h4>
                        <div class="pw-additional-compact">
                            <?php
                            $additional_items = array( 'humidity', 'wind', 'pressure', 'visibility' );
                            foreach ( $additional_items as $item ) :
                                $value = isset( $weather_data[ $item ] ) ? $weather_data[ $item ] : '';
                                if ( empty( $value ) ) continue;
                                $label = get_additional_data_label( $item );
                            ?>
                                <div class="pw-additional-compact-item">
                                    <span class="pw-item-label"><?php echo esc_html( $label ); ?>:</span>
                                    <span class="pw-item-value"><?php echo esc_html( $value ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ( $show_sun_info && ! empty( $weather_data['sunrise'] ) && ! empty( $weather_data['sunset'] ) ) : ?>
                    <div class="pw-combined-sun">
                        <div class="pw-sun-item">
                            <span class="pw-sun-label"><?php esc_html_e( 'Sunrise', 'pearl-weather' ); ?>:</span>
                            <span class="pw-sun-value"><?php echo esc_html( $weather_data['sunrise'] ); ?></span>
                        </div>
                        <div class="pw-sun-item">
                            <span class="pw-sun-label"><?php esc_html_e( 'Sunset', 'pearl-weather' ); ?>:</span>
                            <span class="pw-sun-value"><?php echo esc_html( $weather_data['sunset'] ); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
            
        </div>
        
    <?php elseif ( 'stacked' === $combined_layout ) : ?>
        
        <!-- Stacked Layout -->
        <div class="pw-combined-stacked">
            
            <!-- Current Weather (Full Width) -->
            <?php if ( $show_current ) : ?>
                <div class="pw-stacked-current">
                    <?php
                    $current_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/current-weather.php';
                    if ( file_exists( $current_template ) ) {
                        include $current_template;
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- Two Column Row -->
            <div class="pw-stacked-row">
                
                <!-- Forecast Column -->
                <?php if ( $show_forecast && ! empty( $forecast_data ) ) : ?>
                    <div class="pw-stacked-forecast">
                        <h4 class="pw-section-title"><?php esc_html_e( 'Hourly Forecast', 'pearl-weather' ); ?></h4>
                        <div class="pw-forecast-horizontal-scroll">
                            <?php foreach ( array_slice( $forecast_data, 0, 8 ) as $item ) : ?>
                                <div class="pw-forecast-scroll-item">
                                    <div class="pw-forecast-time"><?php echo esc_html( $item['time'] ?? '' ); ?></div>
                                    <?php if ( ! empty( $item['icon'] ) ) : ?>
                                        <img src="<?php echo esc_url( $item['icon'] ); ?>" alt="" width="35" height="35">
                                    <?php endif; ?>
                                    <div class="pw-forecast-temp"><?php echo esc_html( $item['temp'] ?? '--' ); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Additional Data Column -->
                <?php if ( $show_additional ) : ?>
                    <div class="pw-stacked-additional">
                        <h4 class="pw-section-title"><?php esc_html_e( 'Weather Details', 'pearl-weather' ); ?></h4>
                        <div class="pw-additional-grid">
                            <?php
                            $additional_items = array(
                                'humidity'   => __( 'Humidity', 'pearl-weather' ),
                                'pressure'   => __( 'Pressure', 'pearl-weather' ),
                                'wind'       => __( 'Wind', 'pearl-weather' ),
                                'visibility' => __( 'Visibility', 'pearl-weather' ),
                                'clouds'     => __( 'Clouds', 'pearl-weather' ),
                                'uv_index'   => __( 'UV Index', 'pearl-weather' ),
                            );
                            foreach ( $additional_items as $key => $label ) :
                                $value = isset( $weather_data[ $key ] ) ? $weather_data[ $key ] : '';
                                if ( empty( $value ) ) continue;
                            ?>
                                <div class="pw-additional-grid-item">
                                    <span class="pw-item-label"><?php echo esc_html( $label ); ?></span>
                                    <span class="pw-item-value"><?php echo esc_html( $value ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
            
            <!-- AQI Section (if available) -->
            <?php if ( $show_aqi && ! empty( $aqi_data ) ) : ?>
                <div class="pw-stacked-aqi">
                    <?php
                    $aqi_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/aqi-minimal/summary.php';
                    if ( file_exists( $aqi_template ) ) {
                        include $aqi_template;
                    }
                    ?>
                </div>
            <?php endif; ?>
            
        </div>
        
    <?php elseif ( 'sidebar' === $combined_layout ) : ?>
        
        <!-- Sidebar Layout (Current Weather sidebar + main content) -->
        <div class="pw-combined-sidebar">
            
            <!-- Sidebar -->
            <div class="pw-combined-sidebar-inner">
                <?php if ( $show_current ) : ?>
                    <div class="pw-sidebar-current">
                        <?php
                        $compact_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/current-weather-compact.php';
                        if ( file_exists( $compact_template ) ) {
                            include $compact_template;
                        } else {
                            $this->render_compact_weather_fallback( $weather_data, $attributes );
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if ( $show_sun_info && ! empty( $weather_data['sunrise'] ) ) : ?>
                    <div class="pw-sidebar-sun">
                        <div class="pw-sun-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 16h16M4 20h16M12 4v4m-4-2 4-4 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            <span><?php echo esc_html( $weather_data['sunrise'] ); ?></span>
                        </div>
                        <div class="pw-sun-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 16h16M4 20h16M12 4v4m-4-2 4-4 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            <span><?php echo esc_html( $weather_data['sunset'] ); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Main Content -->
            <div class="pw-combined-main">
                
                <?php if ( $show_forecast && ! empty( $forecast_data ) ) : ?>
                    <div class="pw-main-forecast">
                        <h4 class="pw-section-title"><?php esc_html_e( 'Forecast', 'pearl-weather' ); ?></h4>
                        <div class="pw-forecast-list">
                            <?php foreach ( array_slice( $forecast_data, 0, 6 ) as $item ) : ?>
                                <div class="pw-forecast-list-item">
                                    <span class="pw-forecast-time"><?php echo esc_html( $item['time'] ?? '' ); ?></span>
                                    <?php if ( ! empty( $item['icon'] ) ) : ?>
                                        <img src="<?php echo esc_url( $item['icon'] ); ?>" alt="" width="24" height="24">
                                    <?php endif; ?>
                                    <span class="pw-forecast-temp"><?php echo esc_html( $item['temp'] ?? '--' ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ( $show_additional ) : ?>
                    <div class="pw-main-additional">
                        <div class="pw-additional-row">
                            <?php
                            $additional_items = array( 'humidity', 'wind', 'pressure', 'visibility', 'clouds' );
                            foreach ( $additional_items as $item ) :
                                $value = isset( $weather_data[ $item ] ) ? $weather_data[ $item ] : '';
                                if ( empty( $value ) ) continue;
                                $label = get_additional_data_label( $item );
                            ?>
                                <div class="pw-additional-row-item">
                                    <span class="pw-item-label"><?php echo esc_html( $label ); ?>:</span>
                                    <span class="pw-item-value"><?php echo esc_html( $value ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
            
        </div>
        
    <?php endif; ?>
    
    <!-- Footer -->
    <?php if ( $show_attribution ) : ?>
        <div class="pw-combined-footer">
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
    <?php endif; ?>
    
</div>

<style>
/* Combined Layout Styles */
.pw-weather-combined {
    width: 100%;
    margin: 0 auto;
}

/* Section Title */
.pw-section-title {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 12px 0;
    padding-bottom: 8px;
    border-bottom: 2px solid rgba(0, 0, 0, 0.08);
}

/* Grid Layout */
.pw-combined-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

/* Stacked Layout */
.pw-stacked-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-top: 20px;
}

/* Sidebar Layout */
.pw-combined-sidebar {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 24px;
}

/* Forecast Compact */
.pw-forecast-compact {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.pw-forecast-compact-item {
    text-align: center;
    flex: 1;
    min-width: 60px;
}

.pw-forecast-time {
    font-size: 11px;
    opacity: 0.7;
}

.pw-forecast-temp {
    font-size: 14px;
    font-weight: 600;
}

/* Forecast Horizontal Scroll */
.pw-forecast-horizontal-scroll {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    padding-bottom: 8px;
}

.pw-forecast-scroll-item {
    text-align: center;
    flex: 0 0 auto;
    min-width: 70px;
}

/* Additional Compact */
.pw-additional-compact {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.pw-additional-compact-item {
    display: flex;
    justify-content: space-between;
    flex: 1;
    min-width: 100px;
    padding: 8px;
    background: rgba(0, 0, 0, 0.02);
    border-radius: 6px;
}

/* Additional Grid */
.pw-additional-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.pw-additional-grid-item {
    display: flex;
    justify-content: space-between;
    padding: 8px;
    background: rgba(0, 0, 0, 0.02);
    border-radius: 6px;
}

/* Additional Row */
.pw-additional-row {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.pw-additional-row-item {
    display: flex;
    gap: 8px;
    padding: 8px 12px;
    background: rgba(0, 0, 0, 0.02);
    border-radius: 6px;
}

/* Sun Info */
.pw-combined-sun,
.pw-sidebar-sun {
    display: flex;
    justify-content: space-between;
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

.pw-sun-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
}

/* Forecast List */
.pw-forecast-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.pw-forecast-list-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: rgba(0, 0, 0, 0.02);
    border-radius: 6px;
}

/* Footer */
.pw-combined-footer {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    text-align: center;
    font-size: 11px;
    color: #757575;
}

/* Responsive */
@media (max-width: 992px) {
    .pw-combined-grid,
    .pw-stacked-row,
    .pw-combined-sidebar {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .pw-combined-sidebar {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .pw-additional-grid {
        grid-template-columns: 1fr;
    }
    
    .pw-forecast-compact {
        gap: 8px;
    }
    
    .pw-forecast-compact-item {
        min-width: 50px;
    }
}
</style>