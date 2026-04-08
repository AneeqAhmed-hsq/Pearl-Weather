<?php
/**
 * Forecast Data Regular Layout Renderer
 *
 * Displays forecast data in a regular grid/flex layout.
 * Loops through forecast items and renders each using the
 * individual forecast item template.
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
 * - $forecast_data: Forecast data array
 * - $each_forecast_array: Forecast data array (alias)
 * - $data_type: Forecast type ('hourly' or 'daily')
 * - $active_forecast_layout: Active layout ('regular' or 'swiper')
 * - $template: Template variant name
 * - $block_name: Block name
 * - $pre_defined_layout: Pre-defined layout (for popups, etc.)
 */

// Skip if this is a swiper layout or no forecast data.
if ( ! isset( $active_forecast_layout ) || 'swiper' === $active_forecast_layout ) {
    return;
}

// Get forecast data.
$forecast_items = isset( $each_forecast_array ) ? $each_forecast_array : ( isset( $forecast_data ) ? $forecast_data : array() );

if ( empty( $forecast_items ) ) {
    return;
}

// Determine forecast layout style.
$is_layout_three = isset( $is_layout_three ) ? (bool) $is_layout_three : false;
$forecast_layout = $is_layout_three ? 'layout-three' : 'normal';
$forecast_layout = isset( $pre_defined_layout ) ? $pre_defined_layout : $forecast_layout;

// Layout classes.
$layout_classes = array( 'pw-forecast-regular-layout' );

if ( 'daily' === $data_type ) {
    $layout_classes[] = 'pw-forecast-daily';
} else {
    $layout_classes[] = 'pw-forecast-hourly';
}

$layout_classes[] = 'pw-forecast-style-' . $forecast_layout;

// Grid settings.
$columns = isset( $attributes['forecastColumns'] ) ? (int) $attributes['forecastColumns'] : 5;
$gap = isset( $attributes['forecastGap'] ) ? (int) $attributes['forecastGap'] : 12;

// Inline styles for grid.
$grid_style = sprintf(
    '--pw-forecast-columns: %d; --pw-forecast-gap: %dpx;',
    $columns,
    $gap
);

// Additional CSS classes.
if ( ! empty( $attributes['forecastRegularCustomClass'] ) ) {
    $layout_classes[] = sanitize_html_class( $attributes['forecastRegularCustomClass'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $layout_classes ) ); ?>" 
     data-forecast-type="<?php echo esc_attr( $data_type ); ?>"
     data-layout="<?php echo esc_attr( $forecast_layout ); ?>"
     style="<?php echo esc_attr( $grid_style ); ?>">
    
    <?php foreach ( $forecast_items as $index => $single_forecast ) : ?>
        <?php
        // Set up variables for the individual forecast template.
        $current_forecast = $single_forecast;
        $forecast_index = $index;
        $is_first = ( 0 === $index );
        $is_last = ( count( $forecast_items ) - 1 === $index );
        
        // Include the individual forecast item template.
        $item_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/render-forecast.php';
        if ( file_exists( $item_template ) ) {
            include $item_template;
        } else {
            // Fallback rendering.
            $this->render_forecast_item_fallback( $single_forecast, $index, $attributes );
        }
        ?>
    <?php endforeach; ?>
    
</div>

<style>
/* Forecast Regular Layout Styles */
.pw-forecast-regular-layout {
    display: grid;
    grid-template-columns: repeat(var(--pw-forecast-columns, 5), 1fr);
    gap: var(--pw-forecast-gap, 12px);
    margin-top: 16px;
}

/* Forecast Item Container (will be styled in render-forecast) */

/* Layout Three (Alternate styling) */
.pw-forecast-style-layout-three .pw-forecast-item {
    text-align: center;
    padding: 12px;
    background: rgba(0, 0, 0, 0.02);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.pw-forecast-style-layout-three .pw-forecast-item:hover {
    background: rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}

/* Responsive Grid */
@media (max-width: 1024px) {
    .pw-forecast-regular-layout {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 768px) {
    .pw-forecast-regular-layout {
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
}

@media (max-width: 480px) {
    .pw-forecast-regular-layout {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
}

/* Horizontal scroll on mobile for many items */
@media (max-width: 768px) {
    .pw-forecast-regular-layout.pw-forecast-hourly {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        gap: 12px;
        padding-bottom: 8px;
    }
    
    .pw-forecast-regular-layout.pw-forecast-hourly .pw-forecast-item {
        flex: 0 0 auto;
        width: 100px;
        scroll-snap-align: start;
    }
    
    .pw-forecast-regular-layout.pw-forecast-hourly::-webkit-scrollbar {
        height: 4px;
    }
    
    .pw-forecast-regular-layout.pw-forecast-hourly::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05);
        border-radius: 2px;
    }
    
    .pw-forecast-regular-layout.pw-forecast-hourly::-webkit-scrollbar-thumb {
        background: var(--pw-primary-color, #f26c0d);
        border-radius: 2px;
    }
}

/* Loading Animation */
.pw-forecast-regular-layout {
    animation: pw-forecast-fade-in 0.4s ease forwards;
}

@keyframes pw-forecast-fade-in {
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
 * Fallback rendering for forecast item (if render-forecast.php is missing).
 */
if ( ! function_exists( 'render_forecast_item_fallback' ) ) {
    /**
     * Render forecast item fallback.
     *
     * @param array $item       Forecast item data.
     * @param int   $index      Item index.
     * @param array $attributes Block attributes.
     */
    function render_forecast_item_fallback( $item, $index, $attributes ) {
        $time = isset( $item['time'] ) ? $item['time'] : '';
        $temp = isset( $item['temp'] ) ? $item['temp'] : ( isset( $item['temperature'] ) ? $item['temperature'] : '--' );
        $icon = isset( $item['icon'] ) ? $item['icon'] : '';
        $condition = isset( $item['condition'] ) ? $item['condition'] : ( isset( $item['description'] ) ? $item['description'] : '' );
        
        // Get icon URL if available.
        $icon_url = '';
        if ( ! empty( $icon ) && function_exists( 'pearl_weather_get_forecast_icon_url' ) ) {
            $icon_set = isset( $attributes['forecastDataIconType'] ) ? $attributes['forecastDataIconType'] : 'forecast_icon_set_one';
            $icon_url = pearl_weather_get_forecast_icon_url( $icon, $icon_set );
        }
        
        ?>
        <div class="pw-forecast-item pw-forecast-item-<?php echo esc_attr( $index ); ?>">
            <div class="pw-forecast-time"><?php echo esc_html( $time ); ?></div>
            
            <?php if ( ! empty( $icon_url ) ) : ?>
                <div class="pw-forecast-icon">
                    <img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $condition ); ?>" loading="lazy">
                </div>
            <?php endif; ?>
            
            <div class="pw-forecast-temperature">
                <span class="pw-forecast-temp"><?php echo esc_html( $temp ); ?></span>
                <span class="pw-forecast-unit">°</span>
            </div>
            
            <?php if ( ! empty( $condition ) ) : ?>
                <div class="pw-forecast-condition"><?php echo esc_html( $condition ); ?></div>
            <?php endif; ?>
        </div>
        <?php
    }
}