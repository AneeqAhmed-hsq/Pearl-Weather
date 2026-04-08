<?php
/**
 * Forecast Data Swiper (Carousel) Layout Renderer
 *
 * Renders forecast data in a Swiper carousel with responsive breakpoints,
 * autoplay, navigation controls, and pagination options.
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
 * - $active_forecast_layout: Active layout ('regular' or 'swiper')
 * - $unique_id: Unique block identifier
 * - $data_type: Forecast type ('hourly' or 'daily')
 */

// Skip if this is not a swiper layout.
if ( ! isset( $active_forecast_layout ) || 'swiper' !== $active_forecast_layout ) {
    return;
}

// Get forecast data.
$forecast_items = isset( $each_forecast_array ) ? $each_forecast_array : ( isset( $forecast_data ) ? $forecast_data : array() );

if ( empty( $forecast_items ) ) {
    return;
}

// Carousel settings.
$infinite_loop = isset( $attributes['forecastCarouselInfiniteLoop'] ) ? (bool) $attributes['forecastCarouselInfiniteLoop'] : false;
$autoplay = isset( $attributes['forecastCarouselAutoPlay'] ) ? (bool) $attributes['forecastCarouselAutoPlay'] : false;
$stop_on_hover = isset( $attributes['carouselStopOnHover'] ) ? (bool) $attributes['carouselStopOnHover'] : false;
$enable_navigation = isset( $attributes['showForecastNavIcon'] ) ? (bool) $attributes['showForecastNavIcon'] : true;
$navigation_icon = isset( $attributes['forecastCarouselNavIcon'] ) ? sanitize_text_field( $attributes['forecastCarouselNavIcon'] ) : 'chevron';
$nav_visibility = isset( $attributes['forecastNavigationVisibility'] ) ? sanitize_text_field( $attributes['forecastNavigationVisibility'] ) : 'onHover';
$show_pagination = isset( $attributes['forecastCarouselShowDots'] ) ? (bool) $attributes['forecastCarouselShowDots'] : false;

// Carousel timing.
$speed = isset( $attributes['forecastCarouselSpeed']['value'] ) ? (int) $attributes['forecastCarouselSpeed']['value'] : 600;
$speed_unit = isset( $attributes['forecastCarouselSpeed']['unit'] ) ? $attributes['forecastCarouselSpeed']['unit'] : 'ms';
$carousel_speed = 'ms' === $speed_unit ? $speed : $speed * 1000;

$delay = isset( $attributes['forecastCarouselAutoplayDelay']['value'] ) ? (int) $attributes['forecastCarouselAutoplayDelay']['value'] : 3000;
$delay_unit = isset( $attributes['forecastCarouselAutoplayDelay']['unit'] ) ? $attributes['forecastCarouselAutoplayDelay']['unit'] : 'ms';
$carousel_delay = 'ms' === $delay_unit ? $delay : $delay * 1000;

// Responsive breakpoints.
$columns = isset( $attributes['forecastCarouselColumns']['device'] ) ? $attributes['forecastCarouselColumns']['device'] : array(
    'Desktop' => 5,
    'Tablet'  => 3,
    'Mobile'  => 2,
);

$gap = isset( $attributes['forecastCarouselHorizontalGap']['device'] ) ? $attributes['forecastCarouselHorizontalGap']['device'] : array(
    'Desktop' => 12,
    'Tablet'  => 10,
    'Mobile'  => 8,
);

// Unique ID for Swiper instance.
$swiper_id = 'pw-forecast-swiper-' . wp_unique_id();
$prev_button_id = $swiper_id . '-prev';
$next_button_id = $swiper_id . '-next';

// Build Swiper configuration.
$swiper_config = array(
    'loop'          => $infinite_loop,
    'speed'         => $carousel_speed,
    'freeMode'      => true,
    'spaceBetween'  => (int) $gap['Desktop'],
    'slidesPerView' => (int) $columns['Desktop'],
    'navigation'    => $enable_navigation ? array(
        'prevEl' => '#' . $prev_button_id,
        'nextEl' => '#' . $next_button_id,
    ) : false,
    'breakpoints'   => array(
        0 => array(
            'slidesPerView' => isset( $columns['Mobile'] ) ? (int) $columns['Mobile'] : 2,
            'spaceBetween'  => isset( $gap['Mobile'] ) ? (int) $gap['Mobile'] : 8,
        ),
        768 => array(
            'slidesPerView' => isset( $columns['Tablet'] ) ? (int) $columns['Tablet'] : 3,
            'spaceBetween'  => isset( $gap['Tablet'] ) ? (int) $gap['Tablet'] : 10,
        ),
        1024 => array(
            'slidesPerView' => (int) $columns['Desktop'],
            'spaceBetween'  => (int) $gap['Desktop'],
        ),
    ),
);

// Add autoplay configuration if enabled.
if ( $autoplay ) {
    $swiper_config['autoplay'] = array(
        'delay'                => $carousel_delay,
        'pauseOnMouseEnter'    => $stop_on_hover,
        'disableOnInteraction' => false,
    );
}

// Add pagination if enabled.
if ( $show_pagination ) {
    $swiper_config['pagination'] = array(
        'el'          => '#' . $swiper_id . '-pagination',
        'clickable'   => true,
        'dynamicBullets' => true,
    );
}

// Navigation visibility class.
$nav_visibility_class = 'pw-nav-' . $nav_visibility;

// Enqueue Swiper assets.
wp_enqueue_script( 'pearl-weather-swiper' );
wp_enqueue_style( 'pearl-weather-swiper' );

?>

<div class="pw-forecast-carousel-wrapper <?php echo esc_attr( $nav_visibility_class ); ?>" data-block-id="<?php echo esc_attr( $unique_id ?? '' ); ?>">
    
    <!-- Swiper Container -->
    <div id="<?php echo esc_attr( $swiper_id ); ?>" 
         class="pw-forecast-swiper swiper"
         data-swiper-config='<?php echo esc_attr( wp_json_encode( $swiper_config ) ); ?>'>
        
        <div class="swiper-wrapper">
            <?php foreach ( $forecast_items as $index => $single_forecast ) : ?>
                <div class="swiper-slide">
                    <?php
                    // Set up variables for the individual forecast template.
                    $current_forecast = $single_forecast;
                    $forecast_index = $index;
                    $active_forecast_layout = 'swiper';
                    
                    // Include the individual forecast item template.
                    $item_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/render-forecast.php';
                    if ( file_exists( $item_template ) ) {
                        include $item_template;
                    } else {
                        // Fallback rendering.
                        ?>
                        <div class="pw-forecast-item-fallback">
                            <?php if ( isset( $single_forecast['time'] ) ) : ?>
                                <div class="pw-fallback-time"><?php echo esc_html( $single_forecast['time'] ); ?></div>
                            <?php endif; ?>
                            <?php if ( isset( $single_forecast['temp'] ) ) : ?>
                                <div class="pw-fallback-temp"><?php echo esc_html( $single_forecast['temp'] ); ?>°</div>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Navigation Buttons -->
        <?php if ( $enable_navigation ) : ?>
            <button id="<?php echo esc_attr( $prev_button_id ); ?>" 
                    class="pw-swiper-nav pw-swiper-prev pw-forecast-nav" 
                    aria-label="<?php esc_attr_e( 'Previous slide', 'pearl-weather' ); ?>">
                <?php if ( 'chevron' === $navigation_icon ) : ?>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                <?php else : ?>
                    <span class="pw-nav-arrow">&larr;</span>
                <?php endif; ?>
            </button>
            
            <button id="<?php echo esc_attr( $next_button_id ); ?>" 
                    class="pw-swiper-nav pw-swiper-next pw-forecast-nav" 
                    aria-label="<?php esc_attr_e( 'Next slide', 'pearl-weather' ); ?>">
                <?php if ( 'chevron' === $navigation_icon ) : ?>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                <?php else : ?>
                    <span class="pw-nav-arrow">&rarr;</span>
                <?php endif; ?>
            </button>
        <?php endif; ?>
        
        <!-- Pagination Dots -->
        <?php if ( $show_pagination ) : ?>
            <div id="<?php echo esc_attr( $swiper_id ); ?>-pagination" class="pw-swiper-pagination"></div>
        <?php endif; ?>
        
    </div>
    
</div>

<style>
/* Forecast Carousel Styles */
.pw-forecast-carousel-wrapper {
    position: relative;
    width: 100%;
    margin: 16px 0;
}

/* Swiper Container */
.pw-forecast-swiper {
    overflow: hidden;
    padding: 4px 0;
}

/* Swiper Slide */
.pw-forecast-swiper .swiper-slide {
    height: auto;
}

/* Navigation Buttons */
.pw-swiper-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    z-index: 10;
}

.pw-swiper-nav:hover {
    background: var(--pw-primary-color, #f26c0d);
    border-color: var(--pw-primary-color, #f26c0d);
    color: #fff;
}

.pw-swiper-nav:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.pw-swiper-prev {
    left: -18px;
}

.pw-swiper-next {
    right: -18px;
}

/* Navigation Visibility */
.pw-nav-onHover .pw-swiper-nav {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.pw-nav-onHover:hover .pw-swiper-nav {
    opacity: 1;
}

.pw-nav-always .pw-swiper-nav {
    opacity: 1;
}

.pw-nav-hidden .pw-swiper-nav {
    display: none;
}

/* Pagination Dots */
.pw-swiper-pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 16px;
}

.pw-swiper-pagination .swiper-pagination-bullet {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ccc;
    opacity: 0.6;
    transition: all 0.3s ease;
}

.pw-swiper-pagination .swiper-pagination-bullet-active {
    width: 24px;
    border-radius: 4px;
    background: var(--pw-primary-color, #f26c0d);
    opacity: 1;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-swiper-nav {
        width: 28px;
        height: 28px;
    }
    
    .pw-swiper-prev {
        left: -10px;
    }
    
    .pw-swiper-next {
        right: -10px;
    }
    
    .pw-swiper-nav svg {
        width: 16px;
        height: 16px;
    }
}

/* Hide navigation on mobile for better UX */
@media (max-width: 480px) {
    .pw-swiper-nav {
        display: none;
    }
}
</style>

<script>
/**
 * Initialize Swiper carousels.
 */
(function() {
    function initSwiper(container) {
        if (typeof Swiper === 'undefined') {
            console.warn('Pearl Weather: Swiper library not loaded.');
            return;
        }
        
        const configAttr = container.getAttribute('data-swiper-config');
        if (!configAttr) return;
        
        let config = {};
        try {
            config = JSON.parse(configAttr);
        } catch (e) {
            console.error('Invalid Swiper config:', e);
            return;
        }
        
        // Initialize Swiper
        const swiper = new Swiper(container, config);
        
        // Handle navigation buttons if they exist
        if (config.navigation) {
            const prevBtn = document.querySelector(config.navigation.prevEl);
            const nextBtn = document.querySelector(config.navigation.nextEl);
            
            if (prevBtn && nextBtn) {
                // Disable buttons at edges if not looping
                if (!config.loop) {
                    const updateButtons = () => {
                        if (swiper.isBeginning) {
                            prevBtn.disabled = true;
                        } else {
                            prevBtn.disabled = false;
                        }
                        
                        if (swiper.isEnd) {
                            nextBtn.disabled = true;
                        } else {
                            nextBtn.disabled = false;
                        }
                    };
                    
                    swiper.on('slideChange', updateButtons);
                    updateButtons();
                }
                
                // Add click handlers
                prevBtn.addEventListener('click', () => swiper.slidePrev());
                nextBtn.addEventListener('click', () => swiper.slideNext());
            }
        }
        
        return swiper;
    }
    
    // Initialize all Swiper containers
    const swipers = document.querySelectorAll('.pw-forecast-swiper');
    swipers.forEach(initSwiper);
})();
</script>

<?php
/**
 * Additional CSS for Swiper integration.
 */
if ( ! wp_style_is( 'pearl-weather-swiper', 'registered' ) ) {
    // Register Swiper styles if not already registered.
    wp_register_style(
        'pearl-weather-swiper',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
        array(),
        '11.0.0'
    );
}

if ( ! wp_script_is( 'pearl-weather-swiper', 'registered' ) ) {
    // Register Swiper script if not already registered.
    wp_register_script(
        'pearl-weather-swiper',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
        array(),
        '11.0.0',
        true
    );
}