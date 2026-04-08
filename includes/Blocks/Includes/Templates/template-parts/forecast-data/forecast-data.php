<?php
/**
 * Forecast Data Renderer Template Part
 *
 * Displays weather forecast data (hourly) with support for
 * regular grid and swiper carousel layouts.
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
 * - $weather_data: Current weather data array
 * - $forecast_data: Forecast data array
 * - $template: Template variant name
 * - $block_name: Block name
 */

// Check if forecast data should be displayed.
$show_forecast = isset( $attributes['displayWeatherForecastData'] ) ? (bool) $attributes['displayWeatherForecastData'] : true;

if ( ! $show_forecast || empty( $forecast_data ) ) {
    return;
}

// Process forecast data options.
$forecast_options = array();
if ( isset( $attributes['forecastData'] ) && is_array( $attributes['forecastData'] ) ) {
    foreach ( $attributes['forecastData'] as $option ) {
        if ( isset( $option['value'] ) && true === $option['value'] ) {
            $forecast_options[] = isset( $option['name'] ) ? sanitize_text_field( $option['name'] ) : '';
        }
    }
}

// If no active forecast options, exit.
if ( empty( $forecast_options ) ) {
    return;
}

// Active forecast (first active option).
$active_forecast = $forecast_options[0];

// Forecast type (hourly/daily).
$forecast_type = isset( $attributes['weatherForecastType'] ) ? sanitize_text_field( $attributes['weatherForecastType'] ) : 'hourly';

// Determine layout type based on template.
$templates_for_regular = array( 'vertical-one', 'vertical-three', 'vertical-four', 'grid-card' );
$templates_for_swiper = array( 'horizontal-one', 'horizontal-two', 'tabs-one', 'table-card' );

$active_layout = 'regular';

if ( in_array( $template, $templates_for_swiper, true ) ) {
    $active_layout = 'swiper';
} elseif ( in_array( $template, $templates_for_regular, true ) ) {
    $active_layout = 'regular';
} else {
    // Default based on block name.
    $active_layout = ( 'grid' === $block_name || 'vertical' === $block_name ) ? 'regular' : 'swiper';
}

// Get forecast title.
$forecast_title = isset( $attributes['hourlyTitle'] ) ? sanitize_text_field( $attributes['hourlyTitle'] ) : __( 'Hourly Forecast', 'pearl-weather' );

// Last update settings.
$show_last_update = isset( $attributes['displayDateUpdateTime'] ) ? (bool) $attributes['displayDateUpdateTime'] : false;
$show_update_in_forecast = $show_last_update && in_array( $block_name, array( 'vertical', 'grid' ), true );

// Additional CSS classes.
$wrapper_classes = array( 'pw-forecast-section', 'pw-forecast-type-' . $forecast_type );

if ( ! empty( $attributes['forecastCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['forecastCustomClass'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" 
     data-forecast-type="<?php echo esc_attr( $forecast_type ); ?>"
     data-active-forecast="<?php echo esc_attr( $active_forecast ); ?>"
     data-layout="<?php echo esc_attr( $active_layout ); ?>">
    
    <!-- Forecast Header (Tabs/Select) -->
    <div class="pw-forecast-header">
        <?php
        $header_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/forecast-header.php';
        if ( file_exists( $header_template ) ) {
            include $header_template;
        } else {
            // Fallback header rendering.
            ?>
            <div class="pw-forecast-title">
                <h4 class="pw-forecast-heading"><?php echo esc_html( $forecast_title ); ?></h4>
            </div>
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
    
    <!-- Forecast Data Display -->
    <div class="pw-forecast-data-container">
        
        <!-- Regular Layout (Grid) -->
        <?php if ( 'regular' === $active_layout ) : ?>
            <div class="pw-forecast-regular-layout">
                <?php
                $regular_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/regular-layout.php';
                if ( file_exists( $regular_template ) ) {
                    include $regular_template;
                } else {
                    // Fallback regular layout rendering.
                    $this->render_forecast_regular_fallback( $forecast_data, $active_forecast, $attributes );
                }
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Swiper Carousel Layout -->
        <?php if ( 'swiper' === $active_layout ) : ?>
            <div class="pw-forecast-swiper-layout">
                <?php
                $swiper_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/swiper-layout.php';
                if ( file_exists( $swiper_template ) ) {
                    include $swiper_template;
                } else {
                    // Fallback swiper layout rendering.
                    $this->render_forecast_swiper_fallback( $forecast_data, $active_forecast, $attributes );
                }
                ?>
            </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Last Updated Time (for specific templates) -->
    <?php if ( $show_update_in_forecast && ! empty( $weather_data['updated_time'] ) ) : ?>
        <div class="pw-forecast-updated">
            <div class="pw-updated-wrapper">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="pw-updated-label"><?php esc_html_e( 'Last updated:', 'pearl-weather' ); ?></span>
                <span class="pw-updated-time"><?php echo esc_html( $weather_data['updated_time'] ); ?></span>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Forecast Section Styles */
.pw-forecast-section {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

/* Forecast Header */
.pw-forecast-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}

.pw-forecast-heading {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
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
    padding: 6px 14px;
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

/* Forecast Updated Time */
.pw-forecast-updated {
    margin-top: 16px;
    text-align: right;
}

.pw-updated-wrapper {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: #757575;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-forecast-header {
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
}
</style>

<?php
/**
 * Fallback rendering methods for forecast data.
 */

if ( ! function_exists( 'render_forecast_regular_fallback' ) ) {
    /**
     * Render forecast regular layout fallback.
     *
     * @param array  $forecast_data   Forecast data.
     * @param string $active_forecast Active forecast type.
     * @param array  $attributes      Block attributes.
     */
    function render_forecast_regular_fallback( $forecast_data, $active_forecast, $attributes ) {
        if ( empty( $forecast_data ) ) {
            echo '<div class="pw-forecast-empty">' . esc_html__( 'No forecast data available.', 'pearl-weather' ) . '</div>';
            return;
        }
        ?>
        <div class="pw-forecast-grid">
            <?php foreach ( $forecast_data as $item ) : ?>
                <div class="pw-forecast-item" data-forecast-type="<?php echo esc_attr( $active_forecast ); ?>">
                    <div class="pw-forecast-time"><?php echo esc_html( $item['time'] ?? '' ); ?></div>
                    <?php if ( ! empty( $item['icon'] ) ) : ?>
                        <div class="pw-forecast-icon">
                            <img src="<?php echo esc_url( $item['icon'] ); ?>" alt="<?php echo esc_attr( $item['condition'] ?? '' ); ?>" loading="lazy">
                        </div>
                    <?php endif; ?>
                    <div class="pw-forecast-value">
                        <?php
                        if ( 'temperature' === $active_forecast ) {
                            echo esc_html( $item['temp'] ?? '--' );
                        } elseif ( 'humidity' === $active_forecast ) {
                            echo esc_html( $item['humidity'] ?? '--' ) . '%';
                        } elseif ( 'wind' === $active_forecast ) {
                            echo esc_html( $item['wind'] ?? '--' );
                        } elseif ( 'pressure' === $active_forecast ) {
                            echo esc_html( $item['pressure'] ?? '--' );
                        } else {
                            echo esc_html( $item[ $active_forecast ] ?? '--' );
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

if ( ! function_exists( 'render_forecast_swiper_fallback' ) ) {
    /**
     * Render forecast swiper layout fallback.
     *
     * @param array  $forecast_data   Forecast data.
     * @param string $active_forecast Active forecast type.
     * @param array  $attributes      Block attributes.
     */
    function render_forecast_swiper_fallback( $forecast_data, $active_forecast, $attributes ) {
        if ( empty( $forecast_data ) ) {
            return;
        }
        
        wp_enqueue_script( 'pearl-weather-swiper' );
        wp_enqueue_style( 'pearl-weather-swiper' );
        
        $swiper_id = 'pw-forecast-swiper-' . uniqid();
        ?>
        <div id="<?php echo esc_attr( $swiper_id ); ?>" class="pw-forecast-swiper swiper">
            <div class="swiper-wrapper">
                <?php foreach ( $forecast_data as $item ) : ?>
                    <div class="swiper-slide">
                        <div class="pw-forecast-item">
                            <div class="pw-forecast-time"><?php echo esc_html( $item['time'] ?? '' ); ?></div>
                            <?php if ( ! empty( $item['icon'] ) ) : ?>
                                <div class="pw-forecast-icon">
                                    <img src="<?php echo esc_url( $item['icon'] ); ?>" alt="<?php echo esc_attr( $item['condition'] ?? '' ); ?>" loading="lazy">
                                </div>
                            <?php endif; ?>
                            <div class="pw-forecast-value">
                                <?php echo esc_html( $item['temp'] ?? '--' ); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="pw-swiper-pagination"></div>
        </div>
        
        <script>
        (function() {
            if (typeof Swiper !== 'undefined') {
                new Swiper('#<?php echo esc_js( $swiper_id ); ?>', {
                    slidesPerView: 'auto',
                    spaceBetween: 12,
                    pagination: { el: '.pw-swiper-pagination', clickable: true },
                    breakpoints: {
                        0: { slidesPerView: 2 },
                        768: { slidesPerView: 3 },
                        1024: { slidesPerView: 5 }
                    }
                });
            }
        })();
        </script>
        <?php
    }
}