<?php
/**
 * Weather Block Vertical Template Renderer
 *
 * Renders weather data in a vertical stacked layout including
 * current weather, additional data, forecast, and footer.
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
 * - $template: Template variant name ('vertical-one', 'vertical-two', etc.)
 * - $unique_id: Unique block identifier
 */

// Display settings.
$show_forecast = isset( $attributes['displayWeatherForecastData'] ) ? (bool) $attributes['displayWeatherForecastData'] : true;
$show_additional = isset( $attributes['displayAdditionalData'] ) ? (bool) $attributes['displayAdditionalData'] : true;
$show_attribution = isset( $attributes['displayWeatherAttribution'] ) ? (bool) $attributes['displayWeatherAttribution'] : true;
$show_last_update = isset( $attributes['displayDateUpdateTime'] ) ? (bool) $attributes['displayDateUpdateTime'] : false;
$show_location = isset( $attributes['showLocationName'] ) ? (bool) $attributes['showLocationName'] : true;
$show_datetime = isset( $attributes['showCurrentDate'] ) || isset( $attributes['showCurrentTime'] ) ? true : false;

// Forecast display style.
$forecast_style = isset( $attributes['forecastDisplayStyle'] ) ? sanitize_text_field( $attributes['forecastDisplayStyle'] ) : 'inline';
$forecast_title = isset( $attributes['hourlyTitle'] ) ? sanitize_text_field( $attributes['hourlyTitle'] ) : __( 'Hourly Forecast', 'pearl-weather' );

// Template variant.
$vertical_variant = isset( $template ) ? $template : 'vertical-one';

// CSS classes.
$wrapper_classes = array( 'pw-weather-vertical-wrapper', "pw-vertical-{$vertical_variant}" );

if ( ! empty( $attributes['verticalCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['verticalCustomClass'] );
}

// Container width and alignment.
$container_width = isset( $attributes['verticalContainerWidth'] ) ? (int) $attributes['verticalContainerWidth'] : 100;
$container_width_unit = isset( $attributes['verticalContainerWidthUnit'] ) ? sanitize_text_field( $attributes['verticalContainerWidthUnit'] ) : '%';
$container_alignment = isset( $attributes['verticalContainerAlignment'] ) ? sanitize_text_field( $attributes['verticalContainerAlignment'] ) : 'center';

// Background settings.
$bg_color = isset( $attributes['verticalBgColor'] ) ? sanitize_hex_color( $attributes['verticalBgColor'] ) : '';
$bg_image = isset( $attributes['verticalBgImage'] ) ? esc_url_raw( $attributes['verticalBgImage'] ) : '';
$bg_overlay = isset( $attributes['verticalBgOverlay'] ) ? sanitize_hex_color( $attributes['verticalBgOverlay'] ) : '';

// Card styling.
$card_radius = isset( $attributes['verticalCardRadius'] ) ? (int) $attributes['verticalCardRadius'] : 12;
$card_padding = isset( $attributes['verticalCardPadding'] ) ? (int) $attributes['verticalCardPadding'] : 20;
$card_shadow = isset( $attributes['verticalCardShadow'] ) ? (bool) $attributes['verticalCardShadow'] : true;

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

$inline_styles[] = "border-radius: {$card_radius}px;";
$inline_styles[] = "padding: {$card_padding}px;";

if ( $card_shadow ) {
    $inline_styles[] = "box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);";
}

$container_styles = array();
$container_styles[] = "width: {$container_width}{$container_width_unit};";
if ( 'center' === $container_alignment ) {
    $container_styles[] = "margin: 0 auto;";
} elseif ( 'left' === $container_alignment ) {
    $container_styles[] = "margin-left: 0; margin-right: auto;";
} elseif ( 'right' === $container_alignment ) {
    $container_styles[] = "margin-left: auto; margin-right: 0;";
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
     style="<?php echo esc_attr( implode( '; ', $container_styles ) ); ?>">
    
    <?php if ( ! empty( $bg_overlay ) ) : ?>
        <div class="pw-vertical-overlay" style="background: <?php echo esc_attr( $bg_overlay ); ?>;"></div>
    <?php endif; ?>
    
    <div class="pw-weather-card" style="<?php echo esc_attr( implode( '; ', $inline_styles ) ); ?>">
        
        <!-- Current Weather Section -->
        <div class="pw-current-weather-section">
            <?php
            $current_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/current-weather.php';
            if ( file_exists( $current_template ) ) {
                include $current_template;
            } else {
                // Fallback current weather display.
                $this->render_current_weather_fallback( $weather_data, $attributes );
            }
            ?>
        </div>
        
        <!-- Additional Data Section -->
        <?php if ( $show_additional ) : ?>
            <div class="pw-additional-data-section">
                <?php
                $additional_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/additional-data/additional-data.php';
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
        <?php if ( $show_forecast && ! empty( $forecast_data ) ) : ?>
            <div class="pw-forecast-section">
                <?php
                if ( 'inline' === $forecast_style ) {
                    $forecast_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/forecast-data.php';
                    if ( file_exists( $forecast_template ) ) {
                        include $forecast_template;
                    } else {
                        // Fallback forecast display.
                        $this->render_forecast_fallback( $forecast_data, $attributes );
                    }
                }
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Last Updated Time -->
        <?php if ( $show_last_update && ! empty( $weather_data['updated_time'] ) ) : ?>
            <div class="pw-last-updated">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <span class="pw-updated-label"><?php esc_html_e( 'Last updated:', 'pearl-weather' ); ?></span>
                <span class="pw-updated-time"><?php echo esc_html( $weather_data['updated_time'] ); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Footer Section -->
        <?php if ( $show_attribution ) : ?>
            <div class="pw-footer-section">
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
    
</div>

<style>
/* Vertical Template Styles */
.pw-weather-vertical-wrapper {
    position: relative;
}

.pw-vertical-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
    border-radius: inherit;
}

.pw-weather-card {
    position: relative;
    z-index: 2;
    background: #fff;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.pw-weather-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

/* Sections */
.pw-current-weather-section {
    margin-bottom: 20px;
}

.pw-additional-data-section {
    margin-bottom: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

.pw-forecast-section {
    margin-bottom: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

/* Last Updated */
.pw-last-updated {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    font-size: 11px;
    color: #757575;
}

/* Footer Section */
.pw-footer-section {
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    text-align: center;
    font-size: 11px;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-weather-card {
        padding: 16px !important;
    }
    
    .pw-last-updated {
        justify-content: center;
    }
}

/* Animation */
.pw-weather-card {
    animation: pw-card-fade-in 0.4s ease forwards;
}

@keyframes pw-card-fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<?php
/**
 * Fallback rendering methods for vertical template.
 */
if ( ! function_exists( 'render_current_weather_fallback' ) ) {
    function render_current_weather_fallback( $weather_data, $attributes ) {
        $temperature = isset( $weather_data['temperature'] ) ? $weather_data['temperature'] : '--';
        $temp_unit = isset( $weather_data['temp_unit'] ) ? $weather_data['temp_unit'] : '°C';
        $city = isset( $weather_data['city'] ) ? $weather_data['city'] : '';
        $condition = isset( $weather_data['description'] ) ? $weather_data['description'] : '';
        $icon_url = isset( $weather_data['icon'] ) ? $weather_data['icon'] : '';
        ?>
        <div class="pw-current-fallback">
            <?php if ( ! empty( $city ) ) : ?>
                <div class="pw-location"><?php echo esc_html( $city ); ?></div>
            <?php endif; ?>
            <div class="pw-weather-main">
                <?php if ( ! empty( $icon_url ) ) : ?>
                    <img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $condition ); ?>" width="50" height="50">
                <?php endif; ?>
                <div class="pw-temperature">
                    <span class="pw-temp-value"><?php echo esc_html( $temperature ); ?></span>
                    <span class="pw-temp-unit"><?php echo esc_html( $temp_unit ); ?></span>
                </div>
            </div>
            <?php if ( ! empty( $condition ) ) : ?>
                <div class="pw-condition"><?php echo esc_html( $condition ); ?></div>
            <?php endif; ?>
        </div>
        <style>
            .pw-current-fallback { text-align: center; }
            .pw-weather-main { display: flex; align-items: center; justify-content: center; gap: 16px; margin: 12px 0; }
            .pw-temperature { display: flex; align-items: baseline; gap: 4px; }
            .pw-temp-value { font-size: 48px; font-weight: 700; }
            .pw-temp-unit { font-size: 20px; }
        </style>
        <?php
    }
}

if ( ! function_exists( 'render_additional_data_fallback' ) ) {
    function render_additional_data_fallback( $weather_data, $attributes ) {
        $items = array(
            'humidity' => __( 'Humidity', 'pearl-weather' ),
            'pressure' => __( 'Pressure', 'pearl-weather' ),
            'wind'     => __( 'Wind', 'pearl-weather' ),
        );
        ?>
        <div class="pw-additional-fallback">
            <div class="pw-additional-grid">
                <?php foreach ( $items as $key => $label ) : ?>
                    <?php $value = isset( $weather_data[ $key ] ) ? $weather_data[ $key ] : ''; ?>
                    <?php if ( ! empty( $value ) ) : ?>
                        <div class="pw-additional-item">
                            <span class="pw-item-label"><?php echo esc_html( $label ); ?>:</span>
                            <span class="pw-item-value"><?php echo esc_html( $value ); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <style>
            .pw-additional-fallback { margin-top: 16px; }
            .pw-additional-grid { display: flex; flex-wrap: wrap; gap: 16px; justify-content: space-between; }
            .pw-additional-item { flex: 1; min-width: 100px; display: flex; justify-content: space-between; padding: 8px; background: rgba(0,0,0,0.02); border-radius: 8px; }
        </style>
        <?php
    }
}

if ( ! function_exists( 'render_forecast_fallback' ) ) {
    function render_forecast_fallback( $forecast_data, $attributes ) {
        if ( empty( $forecast_data ) ) return;
        $temp_unit = isset( $attributes['displayTemperatureUnit'] ) && 'imperial' === $attributes['displayTemperatureUnit'] ? '°F' : '°C';
        ?>
        <div class="pw-forecast-fallback">
            <h4 class="pw-fallback-title"><?php esc_html_e( 'Hourly Forecast', 'pearl-weather' ); ?></h4>
            <div class="pw-forecast-scroll">
                <?php foreach ( array_slice( $forecast_data, 0, 6 ) as $item ) : ?>
                    <div class="pw-forecast-item">
                        <div class="pw-time"><?php echo esc_html( $item['time'] ?? '' ); ?></div>
                        <?php if ( ! empty( $item['icon'] ) ) : ?>
                            <img src="<?php echo esc_url( $item['icon'] ); ?>" alt="" width="35" height="35">
                        <?php endif; ?>
                        <div class="pw-temp"><?php echo esc_html( $item['temp'] ?? '--' ); ?><?php echo esc_html( $temp_unit ); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <style>
            .pw-forecast-fallback { margin-top: 16px; }
            .pw-fallback-title { margin: 0 0 12px 0; font-size: 16px; }
            .pw-forecast-scroll { display: flex; gap: 12px; overflow-x: auto; }
            .pw-forecast-item { text-align: center; min-width: 80px; padding: 10px; background: rgba(0,0,0,0.02); border-radius: 8px; }
            .pw-time { font-size: 12px; margin-bottom: 8px; }
            .pw-temp { font-size: 14px; font-weight: 600; margin-top: 8px; }
        </style>
        <?php
    }
}