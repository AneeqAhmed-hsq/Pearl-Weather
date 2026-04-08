<?php
/**
 * Additional Weather Data Renderer Template Part
 *
 * Displays additional weather data (humidity, pressure, wind, etc.)
 * with support for multiple layout variations including carousel,
 * grid, column layouts, and table layouts.
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
 * - $template: Template variant name
 * - $block_name: Block name
 */

// Check if additional data should be displayed.
$show_additional_data = isset( $attributes['displayAdditionalData'] ) ? (bool) $attributes['displayAdditionalData'] : true;

if ( ! $show_additional_data ) {
    return;
}

// Get additional data options.
$additional_data_options = isset( $attributes['additionalDataOptions'] ) && is_array( $attributes['additionalDataOptions'] ) 
    ? $attributes['additionalDataOptions'] 
    : array();

// Process options (filter active, expand sunrise/sunset).
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

// If no active options, exit.
if ( empty( $processed_options ) ) {
    return;
}

// Layout settings.
$data_layout = isset( $attributes['active_additional_data_layout'] ) ? sanitize_text_field( $attributes['active_additional_data_layout'] ) : 'center';
$layout_style = isset( $attributes['active_additional_data_layout_style'] ) ? sanitize_text_field( $attributes['active_additional_data_layout_style'] ) : 'clean';

// Comport data (pressure, humidity, wind) handling.
$comport_data_keys = array( 'pressure', 'humidity', 'wind' );
$with_comport_data = array_filter( $processed_options, function( $item ) use ( $comport_data_keys ) {
    return ! in_array( $item, $comport_data_keys, true );
} );
$comport_data_only = array_filter( $processed_options, function( $item ) use ( $comport_data_keys ) {
    return in_array( $item, $comport_data_keys, true );
} );

// Check if we should separate comport data.
$display_comport_data = isset( $attributes['displayComportDataPosition'] ) ? (bool) $attributes['displayComportDataPosition'] : false;
$comport_layouts = array( 'center', 'left', 'justified' );
$display_comport_layout = in_array( $data_layout, $comport_layouts, true ) && ! $display_comport_data;

// Use filtered options if separating comport data.
$display_options = $display_comport_layout ? $with_comport_data : $processed_options;

// Determine if colon should be displayed after labels.
$colon_layouts = array( 'center', 'column-two', 'left', 'column-two-justified' );
$colon_templates = array( 'horizontal-one', 'vertical-three', 'vertical-four' );
$show_colon = in_array( $data_layout, $colon_layouts, true ) || in_array( $template, $colon_templates, true );

// Check if this is a carousel layout.
$is_carousel = in_array( $data_layout, array( 'carousel-simple', 'carousel-flat' ), true );

// Check if this is a table layout.
$is_table_layout = in_array( $block_name, array( 'table', 'tabs' ), true );

// Carousel settings.
$carousel_items = isset( $attributes['additionalCarouselColumns']['device']['Desktop'] ) 
    ? (int) $attributes['additionalCarouselColumns']['device']['Desktop'] 
    : 4;
$carousel_gap = isset( $attributes['additionalCarouselHorizontalGap']['device']['Desktop'] ) 
    ? (int) $attributes['additionalCarouselHorizontalGap']['device']['Desktop'] 
    : 12;
$carousel_autoplay = isset( $attributes['additionalCarouselAutoPlay'] ) ? (bool) $attributes['additionalCarouselAutoPlay'] : false;
$carousel_delay = isset( $attributes['additionalCarouselDelayTime']['value'] ) ? (int) $attributes['additionalCarouselDelayTime']['value'] : 3000;

// Additional CSS classes.
$wrapper_classes = array( 'pw-additional-data' );
$wrapper_classes[] = 'pw-data-layout-' . $data_layout;

if ( ! empty( $attributes['additionalDataCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['additionalDataCustomClass'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ); ) ?>"
     data-layout="<?php echo esc_attr( $data_layout ); ?>"
     data-style="<?php echo esc_attr( $layout_style ); ?>"
     data-show-colon="<?php echo esc_attr( $show_colon ? 'true' : 'false' ); ?>">
    
    <?php if ( $is_table_layout ) : ?>
        <!-- Table Layout -->
        <div class="pw-data-table">
            <div class="pw-data-grid pw-grid-cols-2">
                <?php foreach ( $display_options as $option ) : ?>
                    <?php
                    $value = isset( $weather_data[ $option ] ) ? $weather_data[ $option ] : '';
                    if ( empty( $value ) && '0' !== $value ) {
                        continue;
                    }
                    $label = get_additional_data_label( $option );
                    ?>
                    <div class="pw-data-item" data-option="<?php echo esc_attr( $option ); ?>">
                        <div class="pw-data-label"><?php echo esc_html( $label ); ?>:</div>
                        <div class="pw-data-value"><?php echo esc_html( $value ); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
    <?php elseif ( $is_carousel ) : ?>
        <!-- Carousel Layout -->
        <div class="pw-data-carousel"
             data-items="<?php echo esc_attr( $carousel_items ); ?>"
             data-gap="<?php echo esc_attr( $carousel_gap ); ?>"
             data-autoplay="<?php echo esc_attr( $carousel_autoplay ? 'true' : 'false' ); ?>"
             data-delay="<?php echo esc_attr( $carousel_delay ); ?>">
            
            <div class="pw-carousel-container">
                <div class="pw-carousel-track">
                    <?php foreach ( $display_options as $option ) : ?>
                        <?php
                        $value = isset( $weather_data[ $option ] ) ? $weather_data[ $option ] : '';
                        if ( empty( $value ) && '0' !== $value ) {
                            continue;
                        }
                        $label = get_additional_data_label( $option );
                        $icon = get_additional_data_icon( $option );
                        ?>
                        <div class="pw-carousel-item">
                            <div class="pw-data-card pw-card-<?php echo esc_attr( $layout_style ); ?>">
                                <div class="pw-data-icon"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                                <div class="pw-data-label"><?php echo esc_html( $label ); ?></div>
                                <div class="pw-data-value"><?php echo esc_html( $value ); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Carousel Navigation -->
                <button class="pw-carousel-prev" aria-label="<?php esc_attr_e( 'Previous', 'pearl-weather' ); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button class="pw-carousel-next" aria-label="<?php esc_attr_e( 'Next', 'pearl-weather' ); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            
            <div class="pw-carousel-dots"></div>
        </div>
        
    <?php else : ?>
        <!-- Regular Layout -->
        <div class="pw-data-regular-layout">
            
            <!-- Main Data Items -->
            <div class="pw-data-items pw-data-layout-<?php echo esc_attr( $data_layout ); ?>">
                <?php foreach ( $display_options as $option ) : ?>
                    <?php
                    $value = isset( $weather_data[ $option ] ) ? $weather_data[ $option ] : '';
                    if ( empty( $value ) && '0' !== $value ) {
                        continue;
                    }
                    $label = get_additional_data_label( $option );
                    $icon = get_additional_data_icon( $option );
                    $colon = $show_colon ? ':' : '';
                    ?>
                    <div class="pw-data-item pw-item-<?php echo esc_attr( $option ); ?>" data-option="<?php echo esc_attr( $option ); ?>">
                        <?php if ( 'justified' === $data_layout ) : ?>
                            <div class="pw-data-label-wrapper">
                                <span class="pw-data-label"><?php echo esc_html( $label ); ?><?php echo esc_html( $colon ); ?></span>
                            </div>
                            <div class="pw-data-value-wrapper">
                                <span class="pw-data-value"><?php echo esc_html( $value ); ?></span>
                            </div>
                        <?php else : ?>
                            <div class="pw-data-icon"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                            <div class="pw-data-label"><?php echo esc_html( $label ); ?><?php echo esc_html( $colon ); ?></div>
                            <div class="pw-data-value"><?php echo esc_html( $value ); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Comport Data Section (separated) -->
            <?php if ( $display_comport_layout && ! empty( $comport_data_only ) ) : ?>
                <div class="pw-comport-data-section">
                    <div class="pw-comport-separator"></div>
                    <div class="pw-comport-title"><?php esc_html_e( 'Weather Details', 'pearl-weather' ); ?></div>
                    <div class="pw-data-items pw-data-layout-comport">
                        <?php foreach ( $comport_data_only as $option ) : ?>
                            <?php
                            $value = isset( $weather_data[ $option ] ) ? $weather_data[ $option ] : '';
                            if ( empty( $value ) && '0' !== $value ) {
                                continue;
                            }
                            $label = get_additional_data_label( $option );
                            $icon = get_additional_data_icon( $option );
                            ?>
                            <div class="pw-data-item pw-item-<?php echo esc_attr( $option ); ?>">
                                <div class="pw-data-icon"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                                <div class="pw-data-label"><?php echo esc_html( $label ); ?>:</div>
                                <div class="pw-data-value"><?php echo esc_html( $value ); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Additional Data Styles */
.pw-additional-data {
    margin-top: 16px;
}

/* Data Items Container */
.pw-data-items {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

/* Layout Variations */
.pw-data-layout-center .pw-data-item {
    flex: 1;
    text-align: center;
    justify-content: center;
}

.pw-data-layout-left .pw-data-item {
    flex: 1;
    text-align: left;
}

.pw-data-layout-justified .pw-data-item {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 8px;
}

.pw-data-layout-column-two .pw-data-items {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.pw-data-layout-column-two-justified .pw-data-items {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.pw-data-layout-column-two-justified .pw-data-item {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
}

.pw-data-layout-grid-one .pw-data-items {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
}

.pw-data-layout-grid-two .pw-data-items {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
}

.pw-data-layout-grid-three .pw-data-items {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
}

.pw-data-layout-horizontal-list .pw-data-items {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

/* Data Item */
.pw-data-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pw-data-icon svg {
    width: 20px;
    height: 20px;
}

.pw-data-label {
    font-size: 13px;
    opacity: 0.7;
}

.pw-data-value {
    font-size: 15px;
    font-weight: 600;
}

/* Comport Data Section */
.pw-comport-data-section {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

.pw-comport-title {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.5;
    margin-bottom: 12px;
}

/* Table Layout */
.pw-data-table .pw-data-grid {
    display: grid;
    gap: 12px;
}

.pw-data-table .pw-data-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.pw-data-table .pw-data-item:last-child {
    border-bottom: none;
}

/* Carousel Layout */
.pw-data-carousel {
    position: relative;
}

.pw-carousel-container {
    position: relative;
    overflow: hidden;
}

.pw-carousel-track {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    scroll-behavior: smooth;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 4px 0;
}

.pw-carousel-track::-webkit-scrollbar {
    display: none;
}

.pw-carousel-item {
    flex: 0 0 auto;
    width: calc(25% - 9px);
    min-width: 100px;
}

.pw-data-card {
    background: rgba(0, 0, 0, 0.02);
    border-radius: 8px;
    padding: 12px;
    text-align: center;
    transition: all 0.3s ease;
}

.pw-data-card:hover {
    background: rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}

.pw-carousel-prev,
.pw-carousel-next {
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
}

.pw-carousel-prev {
    left: -16px;
}

.pw-carousel-next {
    right: -16px;
}

.pw-carousel-dots {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 16px;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-data-layout-column-two .pw-data-items,
    .pw-data-layout-column-two-justified .pw-data-items,
    .pw-data-layout-grid-two .pw-data-items,
    .pw-data-layout-grid-three .pw-data-items {
        grid-template-columns: 1fr;
    }
    
    .pw-carousel-item {
        width: calc(50% - 6px);
    }
    
    .pw-carousel-prev,
    .pw-carousel-next {
        display: none;
    }
}
</style>

<?php
/**
 * Carousel JavaScript (if not using Swiper)
 */
if ( $is_carousel ) : ?>
<script>
(function() {
    const carousel = document.querySelector('.pw-data-carousel');
    if (!carousel) return;
    
    const track = carousel.querySelector('.pw-carousel-track');
    const prevBtn = carousel.querySelector('.pw-carousel-prev');
    const nextBtn = carousel.querySelector('.pw-carousel-next');
    
    if (!track) return;
    
    const scrollAmount = () => {
        const item = track.querySelector('.pw-carousel-item');
        if (!item) return 200;
        return item.offsetWidth + 12;
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
<?php endif;