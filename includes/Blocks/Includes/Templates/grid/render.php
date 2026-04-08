<?php
/**
 * Weather Grid Template Main Renderer
 *
 * Main renderer for Grid layouts (Grid One, Grid Two, Grid Three).
 * Processes forecast data and includes the appropriate grid template.
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
 * - $template: Template variant name ('grid-one', 'grid-two', 'grid-three')
 * - $unique_id: Unique block identifier
 */

// Process forecast data options.
$forecast_options = array();
if ( isset( $attributes['forecastData'] ) && is_array( $attributes['forecastData'] ) ) {
    foreach ( $attributes['forecastData'] as $option ) {
        if ( isset( $option['value'] ) && true === $option['value'] ) {
            $forecast_options[] = isset( $option['name'] ) ? sanitize_text_field( $option['name'] ) : '';
        }
    }
}

// Set active forecast (first active option).
$active_forecast = ! empty( $forecast_options ) ? $forecast_options[0] : 'temperature';

// Forecast type.
$forecast_type = isset( $attributes['weatherForecastType'] ) ? sanitize_text_field( $attributes['weatherForecastType'] ) : 'hourly';

// Grid variant.
$grid_variant = isset( $template ) ? $template : 'grid-one';

// CSS classes.
$wrapper_classes = array( 'pw-weather-grid-wrapper', "pw-grid-{$grid_variant}" );

if ( ! empty( $attributes['gridWrapperCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['gridWrapperCustomClass'] );
}

// Grid layout settings.
$layout_style = isset( $attributes['gridLayoutStyle'] ) ? sanitize_text_field( $attributes['gridLayoutStyle'] ) : 'default';
$container_width = isset( $attributes['gridContainerWidth'] ) ? (int) $attributes['gridContainerWidth'] : 1200;
$container_padding = isset( $attributes['gridContainerPadding'] ) ? (int) $attributes['gridContainerPadding'] : 20;

// Background settings.
$bg_color = isset( $attributes['gridBgColor'] ) ? sanitize_hex_color( $attributes['gridBgColor'] ) : '';
$bg_image = isset( $attributes['gridBgImage'] ) ? esc_url_raw( $attributes['gridBgImage'] ) : '';
$bg_overlay = isset( $attributes['gridBgOverlay'] ) ? sanitize_hex_color( $attributes['gridBgOverlay'] ) : '';

// Inline styles.
$inline_styles = array();

if ( ! empty( $bg_color ) ) {
    $inline_styles[] = "background-color: {$bg_color};";
}

if ( ! empty( $bg_image ) ) {
    $inline_styles[] = "background-image: url({$bg_image});";
    $inline_styles[] = "background-size: cover;";
    $inline_styles[] = "background-position: center;";
}

if ( ! empty( $bg_overlay ) ) {
    $inline_styles[] = "position: relative;";
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
     data-grid-variant="<?php echo esc_attr( $grid_variant ); ?>"
     data-forecast-type="<?php echo esc_attr( $forecast_type ); ?>"
     data-active-forecast="<?php echo esc_attr( $active_forecast ); ?>"
     style="<?php echo esc_attr( implode( ' ', $inline_styles ) ); ?>">
    
    <?php if ( ! empty( $bg_overlay ) ) : ?>
        <div class="pw-grid-overlay" style="background: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
    <?php endif; ?>
    
    <div class="pw-grid-container" style="max-width: <?php echo esc_attr( $container_width ); ?>px; padding: <?php echo esc_attr( $container_padding ); ?>px;">
        
        <!-- Current Weather Card -->
        <div class="pw-grid-current-weather">
            <?php
            $current_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/current-weather-card.php';
            if ( file_exists( $current_template ) ) {
                include $current_template;
            } else {
                // Fallback current weather display.
                $this->render_current_weather_fallback( $weather_data, $attributes );
            }
            ?>
        </div>
        
        <!-- Additional Data Section (Grid Two/Three) -->
        <?php if ( in_array( $grid_variant, array( 'grid-two', 'grid-three' ), true ) ) : ?>
            <div class="pw-grid-additional-section">
                <?php
                $additional_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/grid/additional-data.php';
                if ( file_exists( $additional_template ) ) {
                    include $additional_template;
                } else {
                    // Fallback additional data display.
                    $this->render_additional_data_fallback( $weather_data, $attributes );
                }
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Forecast Section -->
        <?php if ( ! empty( $forecast_data ) ) : ?>
            <div class="pw-grid-forecast-section">
                
                <!-- Forecast Header -->
                <div class="pw-forecast-header-wrapper">
                    <h4 class="pw-forecast-title">
                        <?php echo 'hourly' === $forecast_type ? esc_html__( 'Hourly Forecast', 'pearl-weather' ) : esc_html__( 'Daily Forecast', 'pearl-weather' ); ?>
                    </h4>
                    
                    <?php
                    // Include forecast header with tabs/select.
                    $header_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/forecast-header.php';
                    if ( file_exists( $header_template ) ) {
                        include $header_template;
                    } else {
                        // Fallback header with simple tabs.
                        ?>
                        <div class="pw-forecast-tabs">
                            <?php foreach ( $forecast_options as $option ) : ?>
                                <button class="pw-forecast-tab <?php echo $option === $active_forecast ? 'pw-active' : ''; ?>" 
                                        data-forecast="<?php echo esc_attr( $option ); ?>">
                                    <?php echo esc_html( ucfirst( str_replace( '_', ' ', $option ) ) ); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                
                <!-- Forecast Data -->
                <div class="pw-forecast-data-wrapper">
                    <?php
                    // Determine which forecast layout to use.
                    $forecast_layout = 'regular';
                    
                    if ( 'grid-three' === $grid_variant ) {
                        $forecast_layout = 'swiper';
                    }
                    
                    if ( 'regular' === $forecast_layout ) {
                        $layout_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/regular-layout.php';
                    } else {
                        $layout_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/swiper-layout.php';
                    }
                    
                    if ( file_exists( $layout_template ) ) {
                        $active_forecast_layout = $forecast_layout;
                        $each_forecast_array = $forecast_data;
                        $data_type = $forecast_type;
                        include $layout_template;
                    } else {
                        // Fallback forecast display.
                        $this->render_forecast_grid_fallback( $forecast_data, $attributes );
                    }
                    ?>
                </div>
                
            </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="pw-grid-footer">
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
    
</div>

<style>
/* Grid Template Styles */
.pw-weather-grid-wrapper {
    position: relative;
    width: 100%;
}

.pw-grid-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
}

.pw-grid-container {
    position: relative;
    z-index: 2;
    margin: 0 auto;
    width: 100%;
}

/* Grid Variant Specific */
.pw-grid-one .pw-grid-container {
    max-width: 1200px;
}

.pw-grid-two .pw-grid-container,
.pw-grid-three .pw-grid-container {
    max-width: 1400px;
}

/* Current Weather Section */
.pw-grid-current-weather {
    margin-bottom: 24px;
}

/* Additional Data Section */
.pw-grid-additional-section {
    margin-bottom: 24px;
}

/* Forecast Section */
.pw-grid-forecast-section {
    margin-top: 20px;
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
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
.pw-grid-footer {
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    text-align: center;
    font-size: 11px;
    color: #757575;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-grid-container {
        padding: 16px !important;
    }
    
    .pw-forecast-header-wrapper {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .pw-forecast-tabs {
        width: 100%;
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 4px;
    }
    
    .pw-forecast-tab {
        white-space: nowrap;
    }
    
    .pw-grid-forecast-section {
        padding: 16px;
    }
    
    .pw-forecast-title {
        font-size: 16px;
    }
}

/* Grid Three Specific (Carousel) */
.pw-grid-three .pw-forecast-data-wrapper {
    overflow: visible;
}
</style>

<?php
/**
 * Fallback rendering methods for grid template.
 */
if ( ! function_exists( 'render_current_weather_fallback' ) ) {
    function render_current_weather_fallback( $weather_data, $attributes ) {
        $temperature = isset( $weather_data['temperature'] ) ? $weather_data['temperature'] : '--';
        $temp_unit = isset( $weather_data['temp_unit'] ) ? $weather_data['temp_unit'] : '°C';
        $city = isset( $weather_data['city'] ) ? $weather_data['city'] : '';
        $condition = isset( $weather_data['description'] ) ? $weather_data['description'] : '';
        ?>
        <div class="pw-current-fallback">
            <?php if ( ! empty( $city ) ) : ?>
                <div class="pw-location"><?php echo esc_html( $city ); ?></div>
            <?php endif; ?>
            <div class="pw-temperature">
                <span class="pw-temp-value"><?php echo esc_html( $temperature ); ?></span>
                <span class="pw-temp-unit"><?php echo esc_html( $temp_unit ); ?></span>
            </div>
            <?php if ( ! empty( $condition ) ) : ?>
                <div class="pw-condition"><?php echo esc_html( $condition ); ?></div>
            <?php endif; ?>
        </div>
        <style>
            .pw-current-fallback { text-align: center; padding: 20px; }
            .pw-temperature { font-size: 48px; font-weight: 700; }
            .pw-temp-unit { font-size: 20px; }
        </style>
        <?php
    }
}

if ( ! function_exists( 'render_additional_data_fallback' ) ) {
    function render_additional_data_fallback( $weather_data, $attributes ) {
        $items = array( 'humidity', 'wind', 'pressure' );
        ?>
        <div class="pw-additional-fallback">
            <div class="pw-additional-grid">
                <?php foreach ( $items as $item ) : ?>
                    <?php $value = isset( $weather_data[ $item ] ) ? $weather_data[ $item ] : ''; ?>
                    <?php if ( ! empty( $value ) ) : ?>
                        <div class="pw-additional-item">
                            <span class="pw-item-label"><?php echo esc_html( ucfirst( $item ) ); ?>:</span>
                            <span class="pw-item-value"><?php echo esc_html( $value ); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <style>
            .pw-additional-fallback { margin-top: 20px; }
            .pw-additional-grid { display: flex; gap: 20px; flex-wrap: wrap; }
            .pw-additional-item { background: rgba(0,0,0,0.05); padding: 8px 16px; border-radius: 8px; }
        </style>
        <?php
    }
}

if ( ! function_exists( 'render_forecast_grid_fallback' ) ) {
    function render_forecast_grid_fallback( $forecast_data, $attributes ) {
        if ( empty( $forecast_data ) ) return;
        $temp_unit = isset( $attributes['displayTemperatureUnit'] ) && 'imperial' === $attributes['displayTemperatureUnit'] ? '°F' : '°C';
        ?>
        <div class="pw-forecast-fallback">
            <div class="pw-forecast-scroll">
                <?php foreach ( array_slice( $forecast_data, 0, 8 ) as $item ) : ?>
                    <div class="pw-forecast-item">
                        <div class="pw-time"><?php echo esc_html( $item['time'] ?? '' ); ?></div>
                        <?php if ( ! empty( $item['icon'] ) ) : ?>
                            <img src="<?php echo esc_url( $item['icon'] ); ?>" alt="" width="40" height="40">
                        <?php endif; ?>
                        <div class="pw-temp"><?php echo esc_html( $item['temp'] ?? '--' ); ?><?php echo esc_html( $temp_unit ); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <style>
            .pw-forecast-fallback { overflow-x: auto; }
            .pw-forecast-scroll { display: flex; gap: 16px; min-width: max-content; }
            .pw-forecast-item { text-align: center; min-width: 80px; padding: 12px; background: rgba(0,0,0,0.02); border-radius: 8px; }
            .pw-time { font-size: 12px; margin-bottom: 8px; }
            .pw-temp { font-size: 14px; font-weight: 600; margin-top: 8px; }
        </style>
        <?php
    }
}