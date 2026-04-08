<?php
/**
 * Weather Condition Icon Template Part
 *
 * Displays the weather condition icon with support for multiple icon sets,
 * lazy loading, size variants, and accessibility attributes.
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
 * - $forecast_data: Forecast data (for forecast icons)
 * - $block_name: Block name
 */

// Check if weather condition icon should be displayed.
$show_icon = isset( $attributes['weatherConditionIcon'] ) ? (bool) $attributes['weatherConditionIcon'] : true;

if ( ! $show_icon ) {
    return;
}

// Get icon code from weather data.
$icon_code = isset( $weather_data['icon'] ) ? $weather_data['icon'] : ( isset( $weather_data['weather_icon'] ) ? $weather_data['weather_icon'] : '' );

// For forecast items, icon might be in a different variable.
if ( empty( $icon_code ) && isset( $forecast_icon ) ) {
    $icon_code = $forecast_icon;
}

// Exit if no icon code.
if ( empty( $icon_code ) ) {
    return;
}

// Icon set selection.
$icon_set = isset( $attributes['weatherConditionIconType'] ) ? sanitize_text_field( $attributes['weatherConditionIconType'] ) : 'icon_set_one';

// Get icon URL (using the renderer's method).
$icon_url = '';
if ( isset( $this ) && method_exists( $this, 'get_icon_url' ) ) {
    $icon_url = $this->get_icon_url( $icon_code, $icon_set );
}

// Fallback: try to get icon URL from helper.
if ( empty( $icon_url ) && function_exists( 'pearl_weather_get_icon_url' ) ) {
    $icon_url = pearl_weather_get_icon_url( $icon_code, $icon_set );
}

// If still no URL, exit.
if ( empty( $icon_url ) ) {
    return;
}

// Icon settings.
$icon_size = isset( $attributes['weatherConditionIconSize'] ) ? (int) $attributes['weatherConditionIconSize'] : 60;
$disable_animation = isset( $attributes['disableWeatherIconAnimation'] ) ? (bool) $attributes['disableWeatherIconAnimation'] : false;
$show_animation = ! $disable_animation;
$icon_shape = isset( $attributes['iconShape'] ) ? sanitize_text_field( $attributes['iconShape'] ) : 'rounded';
$icon_bg = isset( $attributes['iconBackgroundColor'] ) ? sanitize_hex_color( $attributes['iconBackgroundColor'] ) : '';
$icon_padding = isset( $attributes['iconPadding'] ) ? (int) $attributes['iconPadding'] : 0;

// Get alt text from weather description.
$alt_text = isset( $weather_data['description'] ) ? $weather_data['description'] : ( isset( $weather_data['weather_desc'] ) ? $weather_data['weather_desc'] : __( 'Weather condition icon', 'pearl-weather' ) );

// Additional CSS classes.
$wrapper_classes = array( 'pw-weather-icon' );

if ( $show_animation ) {
    $wrapper_classes[] = 'pw-icon-animate';
}

if ( ! empty( $attributes['iconCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['iconCustomClass'] );
}

// Icon shape classes.
$shape_classes = array(
    'rounded' => 'pw-icon-rounded',
    'circle'  => 'pw-icon-circle',
    'square'  => 'pw-icon-square',
    'none'    => '',
);
$shape_class = isset( $shape_classes[ $icon_shape ] ) ? $shape_classes[ $icon_shape ] : '';

if ( ! empty( $shape_class ) ) {
    $wrapper_classes[] = $shape_class;
}

// Inline styles for custom sizing and background.
$inline_styles = array();

if ( $icon_size > 0 ) {
    $inline_styles[] = '--pw-icon-size: ' . $icon_size . 'px';
}

if ( ! empty( $icon_bg ) ) {
    $inline_styles[] = '--pw-icon-bg: ' . $icon_bg;
}

if ( $icon_padding > 0 ) {
    $inline_styles[] = '--pw-icon-padding: ' . $icon_padding . 'px';
}

$style_attr = ! empty( $inline_styles ) ? 'style="' . esc_attr( implode( '; ', $inline_styles ) ) . '"' : '';

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" <?php echo $style_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    
    <img class="pw-weather-icon-img" 
         src="<?php echo esc_url( $icon_url ); ?>" 
         alt="<?php echo esc_attr( $alt_text ); ?>"
         title="<?php echo esc_attr( $alt_text ); ?>"
         width="<?php echo esc_attr( $icon_size ); ?>"
         height="<?php echo esc_attr( $icon_size ); ?>"
         loading="lazy"
         decoding="async" />
         
</div>

<style>
/* Weather Icon Styles */
.pw-weather-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    line-height: 0;
}

/* Icon Size */
.pw-weather-icon-img {
    width: var(--pw-icon-size, 60px);
    height: var(--pw-icon-size, 60px);
    object-fit: contain;
}

/* Icon Shape Variants */
.pw-icon-rounded .pw-weather-icon-img {
    border-radius: 8px;
}

.pw-icon-circle .pw-weather-icon-img {
    border-radius: 50%;
}

.pw-icon-square .pw-weather-icon-img {
    border-radius: 0;
}

/* Icon Background */
.pw-weather-icon {
    background: var(--pw-icon-bg, transparent);
    padding: var(--pw-icon-padding, 0);
    border-radius: inherit;
}

/* Animation (Pulse/Float) */
.pw-icon-animate .pw-weather-icon-img {
    animation: pw-icon-float 3s ease-in-out infinite;
}

@keyframes pw-icon-float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

/* Hover Effect (Optional) */
.pw-weather-icon.pw-icon-hover-scale:hover .pw-weather-icon-img {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-weather-icon-img {
        width: calc(var(--pw-icon-size, 60px) * 0.8);
        height: calc(var(--pw-icon-size, 60px) * 0.8);
    }
}

/* Reduced Motion Preference */
@media (prefers-reduced-motion: reduce) {
    .pw-icon-animate .pw-weather-icon-img {
        animation: none;
    }
}
</style>

<?php
/**
 * Helper function to get icon URL.
 * This would normally be in the main renderer class.
 */

if ( ! function_exists( 'pearl_weather_get_icon_url' ) ) {
    /**
     * Get weather icon URL.
     *
     * @param string $icon_code Icon code from API.
     * @param string $icon_set  Icon set name.
     * @return string
     */
    function pearl_weather_get_icon_url( $icon_code, $icon_set = 'icon_set_one' ) {
        // Icon set folder mappings.
        $icon_folders = array(
            'icon_set_one'   => 'weather-icons',
            'icon_set_two'   => 'weather-static-icons',
            'icon_set_three' => 'light-line',
            'icon_set_four'  => 'fill-icon',
            'icon_set_five'  => 'weather-glassmorphism',
            'icon_set_six'   => 'animated-line',
            'icon_set_seven' => 'animated',
            'icon_set_eight' => 'medium-line',
        );
        
        $folder = isset( $icon_folders[ $icon_set ] ) ? $icon_folders[ $icon_set ] : 'weather-icons';
        
        // Map OpenWeatherMap icon codes to our icon set.
        $base_code = preg_replace( '/[dn]$/', '', $icon_code );
        
        $mapping = array(
            '01' => 'clear-sky',
            '02' => 'few-clouds',
            '03' => 'scattered-clouds',
            '04' => 'broken-clouds',
            '09' => 'shower-rain',
            '10' => 'rain',
            '11' => 'thunderstorm',
            '13' => 'snow',
            '50' => 'mist',
        );
        
        $icon_name = isset( $mapping[ $base_code ] ) ? $mapping[ $base_code ] : 'clear-sky';
        
        return PEARL_WEATHER_ASSETS_URL . 'images/icons/' . $folder . '/' . $icon_name . '.svg';
    }
}

/**
 * JavaScript for icon lazy loading (optional).
 */
if ( isset( $attributes['enableIconLazyLoad'] ) && $attributes['enableIconLazyLoad'] ) : ?>
<script>
(function() {
    if ('IntersectionObserver' in window) {
        const icons = document.querySelectorAll('.pw-weather-icon-img');
        
        const iconObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    observer.unobserve(img);
                }
            });
        });
        
        icons.forEach(icon => {
            iconObserver.observe(icon);
        });
    }
})();
</script>
<?php endif;