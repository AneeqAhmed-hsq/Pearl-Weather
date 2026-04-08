<?php
/**
 * Current Weather Card Template Part
 *
 * Displays a complete current weather card with current conditions
 * and an optional carousel of additional weather data.
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
 * - $forecast_data: Hourly forecast data array
 * - $template: Template variant name
 */

// Display settings.
$show_additional_data = isset( $attributes['displayAdditionalData'] ) ? (bool) $attributes['displayAdditionalData'] : true;
$show_location = isset( $attributes['showLocationName'] ) ? (bool) $attributes['showLocationName'] : true;
$show_datetime = isset( $attributes['showCurrentDate'] ) || isset( $attributes['showCurrentTime'] ) ? true : false;
$show_icon = isset( $attributes['weatherConditionIcon'] ) ? (bool) $attributes['weatherConditionIcon'] : true;
$show_temperature = isset( $attributes['displayTemperature'] ) ? (bool) $attributes['displayTemperature'] : true;
$show_description = isset( $attributes['displayWeatherConditions'] ) ? (bool) $attributes['displayWeatherConditions'] : true;

// Additional data settings.
$additional_data_options = isset( $attributes['additionalDataOptions'] ) && is_array( $attributes['additionalDataOptions'] ) 
    ? $attributes['additionalDataOptions'] 
    : array();

// Process additional data options (expand sunrise/sunset, filter active).
$processed_options = array();
foreach ( $additional_data_options as $option ) {
    if ( isset( $option['isActive'] ) && true === $option['isActive'] ) {
        $value = isset( $option['value'] ) ? $option['value'] : '';
        
        // Expand sunrise/sunset into separate items.
        if ( 'sunriseSunset' === $value ) {
            $processed_options[] = 'sunrise';
            $processed_options[] = 'sunset';
        } else {
            $processed_options[] = $value;
        }
    }
}

// Slider configuration.
$slider_items_per_page = isset( $attributes['additionalCarouselColumns']['device']['Desktop'] ) 
    ? (int) $attributes['additionalCarouselColumns']['device']['Desktop'] 
    : 4;
$slider_gap = isset( $attributes['additionalCarouselHorizontalGap']['device']['Desktop'] ) 
    ? (int) $attributes['additionalCarouselHorizontalGap']['device']['Desktop'] 
    : 8;
$slider_auto_play = isset( $attributes['additionalCarouselAutoPlay'] ) ? (bool) $attributes['additionalCarouselAutoPlay'] : false;
$slider_delay = isset( $attributes['additionalCarouselDelayTime']['value'] ) ? (int) $attributes['additionalCarouselDelayTime']['value'] : 3000;

// Navigation icons.
$nav_icon_type = isset( $attributes['additionalNavigationIcon'] ) ? $attributes['additionalNavigationIcon'] : 'chevron';
$show_navigation = isset( $attributes['enableAdditionalNavIcon'] ) ? (bool) $attributes['enableAdditionalNavIcon'] : true;

// Weather data.
$city_name = isset( $weather_data['city'] ) ? $weather_data['city'] : '';
$country_code = isset( $weather_data['country'] ) ? $weather_data['country'] : '';
$location_display = ! empty( $country_code ) ? $city_name . ', ' . $country_code : $city_name;

$icon_url = isset( $weather_data['icon'] ) ? $weather_data['icon'] : '';
$icon_alt = isset( $weather_data['description'] ) ? $weather_data['description'] : '';

?>

<div class="pw-current-weather-card">
    
    <!-- Header Section -->
    <div class="pw-card-header">
        <div class="pw-header-top">
            <span class="pw-card-title">
                <?php esc_html_e( 'Current Weather', 'pearl-weather' ); ?>
            </span>
            
            <?php if ( $show_location && ! empty( $location_display ) ) : ?>
                <div class="pw-location-wrapper">
                    <?php
                    $location_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/location-name.php';
                    if ( file_exists( $location_template ) ) {
                        include $location_template;
                    } else {
                        ?>
                        <div class="pw-location-name">
                            <svg class="pw-location-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                                <circle cx="12" cy="9" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
                            </svg>
                            <span class="pw-city"><?php echo esc_html( $location_display ); ?></span>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ( $show_datetime ) : ?>
            <div class="pw-datetime-wrapper">
                <?php
                $datetime_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/date-time.php';
                if ( file_exists( $datetime_template ) ) {
                    include $datetime_template;
                } else {
                    ?>
                    <div class="pw-datetime">
                        <?php if ( isset( $attributes['showCurrentDate'] ) && $attributes['showCurrentDate'] && ! empty( $weather_data['date'] ) ) : ?>
                            <span class="pw-date"><?php echo esc_html( $weather_data['date'] ); ?></span>
                        <?php endif; ?>
                        <?php if ( isset( $attributes['showCurrentTime'] ) && $attributes['showCurrentTime'] && ! empty( $weather_data['time'] ) ) : ?>
                            <span class="pw-time"><?php echo esc_html( $weather_data['time'] ); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Weather Main Display -->
    <div class="pw-weather-main">
        
        <!-- Left: Weather Icon + Temperature -->
        <div class="pw-weather-primary">
            
            <?php if ( $show_icon && ! empty( $icon_url ) ) : ?>
                <div class="pw-weather-icon">
                    <img src="<?php echo esc_url( $icon_url ); ?>" 
                         alt="<?php echo esc_attr( $icon_alt ); ?>"
                         loading="lazy">
                </div>
            <?php endif; ?>
            
            <?php if ( $show_temperature ) : ?>
                <div class="pw-temperature-wrapper">
                    <?php
                    $temperature_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/current-temperature.php';
                    if ( file_exists( $temperature_template ) ) {
                        include $temperature_template;
                    } else {
                        $temperature = isset( $weather_data['temperature'] ) ? $weather_data['temperature'] : '--';
                        $unit = isset( $weather_data['temp_unit'] ) ? $weather_data['temp_unit'] : '°C';
                        ?>
                        <div class="pw-temperature">
                            <span class="pw-temp-value"><?php echo esc_html( $temperature ); ?></span>
                            <span class="pw-temp-unit"><?php echo esc_html( $unit ); ?></span>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            <?php endif; ?>
            
        </div>
        
        <!-- Right: Weather Description -->
        <?php if ( $show_description ) : ?>
            <div class="pw-weather-description-wrapper">
                <?php
                $description_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/weather-description.php';
                if ( file_exists( $description_template ) ) {
                    include $description_template;
                } else {
                    $description = isset( $weather_data['description'] ) ? $weather_data['description'] : '';
                    if ( ! empty( $description ) ) :
                        ?>
                        <div class="pw-weather-description">
                            <?php echo esc_html( $description ); ?>
                        </div>
                        <?php
                    endif;
                }
                ?>
            </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Additional Data Slider -->
    <?php if ( $show_additional_data && ! empty( $processed_options ) ) : ?>
        <div class="pw-additional-data-slider" 
             data-items="<?php echo esc_attr( $slider_items_per_page ); ?>"
             data-gap="<?php echo esc_attr( $slider_gap ); ?>"
             data-autoplay="<?php echo esc_attr( $slider_auto_play ? 'true' : 'false' ); ?>"
             data-delay="<?php echo esc_attr( $slider_delay ); ?>">
            
            <div class="pw-slider-container">
                
                <!-- Slider Track -->
                <div class="pw-slider-track">
                    <?php foreach ( $processed_options as $option ) : ?>
                        <?php
                        // Get the value for this option.
                        $value = isset( $weather_data[ $option ] ) ? $weather_data[ $option ] : '';
                        
                        // Skip if no value.
                        if ( empty( $value ) && '0' !== $value ) {
                            continue;
                        }
                        
                        // Get label for this option.
                        $label = $this->get_additional_data_label( $option );
                        
                        // Get icon for this option.
                        $icon_svg = $this->get_additional_data_icon( $option );
                        ?>
                        <div class="pw-slider-item" data-option="<?php echo esc_attr( $option ); ?>">
                            <div class="pw-additional-data-card">
                                
                                <!-- Icon -->
                                <?php if ( ! empty( $icon_svg ) ) : ?>
                                    <div class="pw-data-icon">
                                        <?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Label -->
                                <div class="pw-data-label">
                                    <?php echo esc_html( $label ); ?>
                                </div>
                                
                                <!-- Value -->
                                <div class="pw-data-value">
                                    <?php echo esc_html( $value ); ?>
                                </div>
                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Navigation Buttons -->
                <?php if ( $show_navigation ) : ?>
                    <button class="pw-slider-nav pw-slider-prev" aria-label="<?php esc_attr_e( 'Previous', 'pearl-weather' ); ?>">
                        <?php if ( 'chevron' === $nav_icon_type ) : ?>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        <?php else : ?>
                            <span class="pw-nav-arrow pw-prev">&larr;</span>
                        <?php endif; ?>
                    </button>
                    <button class="pw-slider-nav pw-slider-next" aria-label="<?php esc_attr_e( 'Next', 'pearl-weather' ); ?>">
                        <?php if ( 'chevron' === $nav_icon_type ) : ?>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        <?php else : ?>
                            <span class="pw-nav-arrow pw-next">&rarr;</span>
                        <?php endif; ?>
                    </button>
                <?php endif; ?>
                
            </div>
            
            <!-- Dots Pagination (optional) -->
            <div class="pw-slider-dots"></div>
            
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Current Weather Card Styles */
.pw-current-weather-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

/* Card Header */
.pw-card-header {
    margin-bottom: 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    padding-bottom: 12px;
}

.pw-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 8px;
}

.pw-card-title {
    font-size: 14px;
    font-weight: 600;
    color: #757575;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pw-location-name {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
}

/* Weather Main Display */
.pw-weather-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 24px;
}

.pw-weather-primary {
    display: flex;
    align-items: center;
    gap: 16px;
}

.pw-weather-icon img {
    width: 64px;
    height: 64px;
}

.pw-temperature {
    display: flex;
    align-items: baseline;
    gap: 4px;
}

.pw-temp-value {
    font-size: 42px;
    font-weight: 700;
    line-height: 1;
}

.pw-temp-unit {
    font-size: 18px;
    font-weight: 500;
}

.pw-weather-description {
    font-size: 16px;
    font-weight: 500;
    text-transform: capitalize;
    color: #555;
}

/* Additional Data Slider */
.pw-additional-data-slider {
    position: relative;
    margin-top: 8px;
}

.pw-slider-container {
    position: relative;
    overflow: hidden;
}

.pw-slider-track {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    scroll-behavior: smooth;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 4px 0;
}

.pw-slider-track::-webkit-scrollbar {
    display: none;
}

.pw-slider-item {
    flex: 0 0 auto;
    width: calc(25% - 9px);
    min-width: 100px;
}

@media (max-width: 768px) {
    .pw-slider-item {
        width: calc(33.33% - 8px);
    }
}

@media (max-width: 480px) {
    .pw-slider-item {
        width: calc(50% - 6px);
    }
}

.pw-additional-data-card {
    background: rgba(0, 0, 0, 0.02);
    border-radius: 8px;
    padding: 12px;
    text-align: center;
    transition: all 0.3s ease;
}

.pw-additional-data-card:hover {
    background: rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}

.pw-data-icon {
    margin-bottom: 8px;
}

.pw-data-icon svg {
    width: 24px;
    height: 24px;
}

.pw-data-label {
    font-size: 12px;
    color: #757575;
    margin-bottom: 4px;
}

.pw-data-value {
    font-size: 16px;
    font-weight: 600;
}

/* Slider Navigation */
.pw-slider-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    transition: all 0.3s ease;
}

.pw-slider-nav:hover {
    background: var(--pw-primary-color, #f26c0d);
    border-color: var(--pw-primary-color, #f26c0d);
    color: #fff;
}

.pw-slider-prev {
    left: -16px;
}

.pw-slider-next {
    right: -16px;
}

/* Slider Dots */
.pw-slider-dots {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 16px;
}

.pw-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ddd;
    cursor: pointer;
    transition: all 0.3s ease;
}

.pw-dot.pw-active {
    width: 24px;
    border-radius: 4px;
    background: var(--pw-primary-color, #f26c0d);
}

/* Responsive */
@media (max-width: 768px) {
    .pw-current-weather-card {
        padding: 16px;
    }
    
    .pw-weather-primary {
        flex-direction: column;
        text-align: center;
    }
    
    .pw-weather-main {
        justify-content: center;
        text-align: center;
    }
    
    .pw-slider-nav {
        display: none;
    }
}
</style>

<script>
// Slider functionality (if not using Swiper)
(function() {
    const slider = document.querySelector('.pw-additional-data-slider');
    if (!slider) return;
    
    const track = slider.querySelector('.pw-slider-track');
    const prevBtn = slider.querySelector('.pw-slider-prev');
    const nextBtn = slider.querySelector('.pw-slider-next');
    
    if (!track) return;
    
    const scrollAmount = () => {
        const item = track.querySelector('.pw-slider-item');
        if (!item) return 200;
        return item.offsetWidth + 12; // item width + gap
    };
    
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            track.scrollBy({ left: -scrollAmount(), behavior: 'smooth' });
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            track.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
        });
    }
})();
</script>

<?php
/**
 * Helper methods for additional data.
 * These would normally be in the main renderer class.
 */

if ( ! function_exists( 'get_additional_data_label' ) ) {
    /**
     * Get label for additional data option.
     *
     * @param string $option Option key.
     * @return string
     */
    function get_additional_data_label( $option ) {
        $labels = array(
            'humidity'       => __( 'Humidity', 'pearl-weather' ),
            'pressure'       => __( 'Pressure', 'pearl-weather' ),
            'wind'           => __( 'Wind', 'pearl-weather' ),
            'gust'           => __( 'Wind Gust', 'pearl-weather' ),
            'visibility'     => __( 'Visibility', 'pearl-weather' ),
            'clouds'         => __( 'Clouds', 'pearl-weather' ),
            'uv_index'       => __( 'UV Index', 'pearl-weather' ),
            'dew_point'      => __( 'Dew Point', 'pearl-weather' ),
            'precipitation'  => __( 'Precipitation', 'pearl-weather' ),
            'rain_chance'    => __( 'Rain Chance', 'pearl-weather' ),
            'sunrise'        => __( 'Sunrise', 'pearl-weather' ),
            'sunset'         => __( 'Sunset', 'pearl-weather' ),
            'feels_like'     => __( 'Feels Like', 'pearl-weather' ),
        );
        
        return isset( $labels[ $option ] ) ? $labels[ $option ] : ucfirst( str_replace( '_', ' ', $option ) );
    }
}

if ( ! function_exists( 'get_additional_data_icon' ) ) {
    /**
     * Get SVG icon for additional data option.
     *
     * @param string $option Option key.
     * @return string
     */
    function get_additional_data_icon( $option ) {
        $icons = array(
            'humidity' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M12 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" fill="currentColor"/></svg>',
            'wind' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12h15a3 3 0 1 0-3-3M3 8h9M3 16h12a3 3 0 1 1-3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
            'pressure' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/><path d="M12 8v4l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M12 3v2M12 19v2M3 12H1M23 12h-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
            'visibility' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>',
            'sunrise' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 16h16M4 20h16M12 4v4m-4-2 4-4 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="12" cy="14" r="3" stroke="currentColor" stroke-width="1.5"/></svg>',
            'sunset' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 16h16M4 20h16M12 4v4m-4-2 4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="12" cy="14" r="3" stroke="currentColor" stroke-width="1.5"/></svg>',
        );
        
        return isset( $icons[ $option ] ) ? $icons[ $option ] : '';
    }
}
?>