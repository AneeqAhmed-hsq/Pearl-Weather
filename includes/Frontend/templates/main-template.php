<?php
/**
 * Main Weather Template
 *
 * The main template file that assembles all weather widget components
 * into a complete display.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Templates
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template variables:
 * - $shortcode_id: Shortcode/widget ID
 * - $weather_data: Current weather data array
 * - $forecast_data: Forecast data array
 * - $layout: Layout type ('vertical' or 'horizontal')
 * - $show_forecast: Whether to show forecast
 * - $preloader_class: CSS class for preloader
 * - $attributes: Widget attributes/settings
 */

// Check if weather data exists.
if ( empty( $weather_data ) || ! isset( $weather_data['temperature'] ) ) {
    return;
}

// Load additional icons configuration.
$icons_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/additional-icons.php';
if ( file_exists( $icons_template ) ) {
    include $icons_template;
}

// CSS classes.
$wrapper_classes = array(
    'pw-weather-widget',
    'pw-layout-' . $layout,
    'pw-widget-' . $shortcode_id,
);

if ( ! empty( $preloader_class ) ) {
    $wrapper_classes[] = $preloader_class;
}

if ( ! empty( $attributes['widget_custom_class'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['widget_custom_class'] );
}

?>

<div id="pw-weather-<?php echo esc_attr( $shortcode_id ); ?>" 
     class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
     data-widget-id="<?php echo esc_attr( $shortcode_id ); ?>"
     data-layout="<?php echo esc_attr( $layout ); ?>">
    
    <!-- Preloader -->
    <?php
    $preloader_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/preloader.php';
    if ( file_exists( $preloader_template ) ) {
        include $preloader_template;
    }
    ?>
    
    <!-- Section Title -->
    <?php
    $title_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/section-title.php';
    if ( file_exists( $title_template ) ) {
        include $title_template;
    }
    ?>
    
    <!-- Main Weather Card -->
    <div class="pw-weather-card">
        <div class="pw-weather-card-inner">
            
            <?php if ( 'horizontal' === $layout ) : ?>
                <div class="pw-current-data-area">
            <?php endif; ?>
            
            <!-- Header Section -->
            <?php
            $header_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/header.php';
            if ( file_exists( $header_template ) ) {
                include $header_template;
            }
            ?>
            
            <!-- Current Weather Section -->
            <?php
            $current_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/current-weather.php';
            if ( file_exists( $current_template ) ) {
                include $current_template;
            }
            ?>
            
            <?php if ( 'horizontal' === $layout ) : ?>
                </div>
            <?php endif; ?>
            
            <!-- Additional Data Section -->
            <?php
            $additional_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/additional-data.php';
            if ( file_exists( $additional_template ) ) {
                include $additional_template;
            }
            ?>
            
        </div>
        
        <!-- Forecast Section -->
        <?php if ( $show_forecast && ! empty( $forecast_data ) ) : ?>
            <div class="pw-forecast-section">
                <?php
                $forecast_header_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-header.php';
                if ( file_exists( $forecast_header_template ) ) {
                    include $forecast_header_template;
                }
                ?>
                
                <?php
                $forecast_data_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data.php';
                if ( file_exists( $forecast_data_template ) ) {
                    include $forecast_data_template;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Footer Section -->
        <?php
        $footer_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/footer.php';
        if ( file_exists( $footer_template ) ) {
            include $footer_template;
        }
        ?>
        
    </div>
    
</div>

<style>
/* Main Widget Styles */
.pw-weather-widget {
    max-width: 100%;
    margin: 0 auto;
}

/* Layout-specific styles */
.pw-layout-vertical .pw-weather-card {
    max-width: 400px;
    margin: 0 auto;
}

.pw-layout-horizontal .pw-weather-card {
    max-width: 800px;
    margin: 0 auto;
}

/* Weather Card */
.pw-weather-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.pw-weather-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.pw-weather-card-inner {
    padding: 20px;
}

/* Current Data Area (Horizontal Layout) */
.pw-current-data-area {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
}

.pw-current-data-area .pw-current-weather {
    flex: 1;
}

.pw-current-data-area .pw-additional-data {
    flex: 1;
}

/* Forecast Section */
.pw-forecast-section {
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    padding: 20px;
    background: rgba(0, 0, 0, 0.02);
}

/* Responsive */
@media (max-width: 768px) {
    .pw-weather-card-inner {
        padding: 16px;
    }
    
    .pw-current-data-area {
        flex-direction: column;
        gap: 16px;
    }
    
    .pw-forecast-section {
        padding: 16px;
    }
}

/* Loading State */
.pw-weather-widget.pw-loading .pw-weather-card {
    opacity: 0.6;
    pointer-events: none;
}

/* Error State */
.pw-weather-widget.pw-error .pw-weather-card {
    border: 1px solid #dc3545;
    background: #fff5f5;
}

.pw-error-message {
    color: #dc3545;
    padding: 20px;
    text-align: center;
}

/* Animation */
@keyframes pw-fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.pw-weather-widget {
    animation: pw-fade-in 0.4s ease forwards;
}
</style>

<?php
/**
 * Legacy template loading function for backward compatibility.
 */
if ( ! function_exists( 'pw_locate_template' ) ) {
    /**
     * Locate a template file.
     *
     * @param string $template_name Template name.
     * @param string $template_path Template path.
     * @param string $default_path  Default path.
     * @return string
     */
    function pw_locate_template( $template_name, $template_path = '', $default_path = '' ) {
        if ( ! $template_path ) {
            $template_path = 'pearl-weather/templates';
        }
        
        if ( ! $default_path ) {
            $default_path = PEARL_WEATHER_TEMPLATE_PATH;
        }
        
        $template = locate_template( trailingslashit( $template_path ) . $template_name );
        
        if ( ! $template ) {
            $template = $default_path . $template_name;
        }
        
        return $template;
    }
}