<?php
/**
 * Additional Data Swiper (Carousel) Layout Renderer
 *
 * Renders additional weather data in a Swiper carousel with
 * responsive breakpoints, autoplay, and navigation controls.
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
 * - $additional_data_options: Array of active data options
 * - $is_swiper_layout: Whether this is a carousel layout
 * - $unique_id: Unique block identifier
 */

// Skip if not a carousel layout.
if ( ! isset( $is_swiper_layout ) || ! $is_swiper_layout ) {
    return;
}

// Carousel settings.
$infinite_loop = isset( $attributes['additionalCarouselInfiniteLoop'] ) ? (bool) $attributes['additionalCarouselInfiniteLoop'] : false;
$autoplay = isset( $attributes['additionalCarouselAutoPlay'] ) ? (bool) $attributes['additionalCarouselAutoPlay'] : false;
$stop_on_hover = isset( $attributes['additionalCarouselStopOnHover'] ) ? (bool) $attributes['additionalCarouselStopOnHover'] : false;
$enable_navigation = isset( $attributes['enableAdditionalNavIcon'] ) ? (bool) $attributes['enableAdditionalNavIcon'] : true;
$navigation_icon = isset( $attributes['additionalNavigationIcon'] ) ? sanitize_text_field( $attributes['additionalNavigationIcon'] ) : 'chevron';

// Carousel timing.
$speed = isset( $attributes['additionalCarouselSpeed']['value'] ) ? (int) $attributes['additionalCarouselSpeed']['value'] : 600;
$speed_unit = isset( $attributes['additionalCarouselSpeed']['unit'] ) ? $attributes['additionalCarouselSpeed']['unit'] : 'ms';
$carousel_speed = 'ms' === $speed_unit ? $speed : $speed * 1000;

$delay = isset( $attributes['additionalCarouselDelayTime']['value'] ) ? (int) $attributes['additionalCarouselDelayTime']['value'] : 3000;
$delay_unit = isset( $attributes['additionalCarouselDelayTime']['unit'] ) ? $attributes['additionalCarouselDelayTime']['unit'] : 'ms';
$carousel_delay = 'ms' === $delay_unit ? $delay : $delay * 1000;

// Responsive breakpoints.
$columns = isset( $attributes['additionalCarouselColumns']['device'] ) ? $attributes['additionalCarouselColumns']['device'] : array(
    'Desktop' => 4,
    'Tablet'  => 3,
    'Mobile'  => 2,
);

$gap = isset( $attributes['additionalDataHorizontalGap']['device'] ) ? $attributes['additionalDataHorizontalGap']['device'] : array(
    'Desktop' => 12,
    'Tablet'  => 10,
    'Mobile'  => 8,
);

// Unique ID for Swiper instance.
$swiper_id = 'pw-swiper-' . wp_unique_id();
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

// Enqueue Swiper assets.
wp_enqueue_script( 'pearl-weather-swiper' );
wp_enqueue_style( 'pearl-weather-swiper' );

// Get unique block ID for scoping.
$block_unique_id = isset( $unique_id ) ? $unique_id : 'pw-block-' . uniqid();

?>

<div class="pw-data-carousel-wrapper" data-block-id="<?php echo esc_attr( $block_unique_id ); ?>">
    
    <!-- Swiper Container -->
    <div id="<?php echo esc_attr( $swiper_id ); ?>" 
         class="pw-data-swiper swiper"
         data-swiper-config='<?php echo esc_attr( wp_json_encode( $swiper_config ) ); ?>'>
        
        <div class="swiper-wrapper">
            <?php foreach ( $additional_data_options as $option ) : ?>
                <?php
                // Get the value for this option.
                $value = isset( $weather_data[ $option ] ) ? $weather_data[ $option ] : '';
                
                // Skip if no value.
                if ( empty( $value ) && '0' !== $value ) {
                    continue;
                }
                
                // Set option for single-weather template.
                $current_option = $option;
                $is_swiper_layout = true;
                ?>
                <div class="swiper-slide">
                    <?php
                    // Include the single weather item template.
                    $single_item_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/additional-data/single-weather.php';
                    if ( file_exists( $single_item_template ) ) {
                        include $single_item_template;
                    } else {
                        // Fallback rendering.
                        ?>
                        <div class="pw-data-item pw-data-<?php echo esc_attr( $option ); ?>">
                            <div class="pw-data-label"><?php echo esc_html( get_additional_data_label( $option ) ); ?></div>
                            <div class="pw-data-value"><?php echo esc_html( $value ); ?></div>
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
                    class="pw-swiper-nav pw-swiper-prev" 
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
                    class="pw-swiper-nav pw-swiper-next" 
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
        
    </div>
    
    <!-- Pagination Dots (optional) -->
    <?php if ( isset( $attributes['additionalCarouselShowDots'] ) && $attributes['additionalCarouselShowDots'] ) : ?>
        <div class="pw-swiper-pagination"></div>
    <?php endif; ?>
    
</div>

<style>
/* Swiper Carousel Styles */
.pw-data-carousel-wrapper {
    position: relative;
    width: 100%;
    margin: 0 auto;
}

/* Swiper Container */
.pw-data-swiper {
    overflow: hidden;
    padding: 4px 0;
}

/* Swiper Slide */
.pw-data-swiper .swiper-slide {
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

/* Hide navigation on mobile if specified */
@media (max-width: 768px) {
    .pw-data-carousel-wrapper.hide-nav-mobile .pw-swiper-nav {
        display: none;
    }
}
</style>

<script>
/**
 * Initialize Swiper carousels.
 * This script initializes all Swiper instances on the page.
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
                    swiper.on('slideChange', function() {
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
                    });
                }
            }
        }
    }
    
    // Initialize all Swiper containers
    const swipers = document.querySelectorAll('.pw-data-swiper');
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