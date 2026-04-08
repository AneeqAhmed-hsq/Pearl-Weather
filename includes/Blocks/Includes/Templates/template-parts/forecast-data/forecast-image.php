<?php
/**
 * Forecast Weather Icon Template Part
 *
 * Displays weather icon for forecast items (hourly/daily forecast)
 * with optional description text and size customization.
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
 * - $single_forecast: Single forecast item data
 * - $forecast_data: Full forecast data array (for context)
 * - $index: Current index in forecast loop
 * - $show_description: Whether to show description text
 */

// Check if forecast icon should be displayed.
$show_icon = isset( $attributes['forecastDataIcon'] ) ? (bool) $attributes['forecastDataIcon'] : true;

if ( ! $show_icon ) {
    return;
}

// Get icon code from forecast data.
$icon_code = isset( $single_forecast['icon'] ) ? $single_forecast['icon'] : '';

if ( empty( $icon_code ) && isset( $single_forecast['weather_icon'] ) ) {
    $icon_code = $single_forecast['weather_icon'];
}

// Exit if no icon code.
if ( empty( $icon_code ) ) {
    return;
}

// Get icon set.
$icon_set = isset( $attributes['forecastDataIconType'] ) ? sanitize_text_field( $attributes['forecastDataIconType'] ) : 'forecast_icon_set_one';

// Get icon URL.
$icon_url = '';
if ( isset( $this ) && method_exists( $this, 'get_icon_url' ) ) {
    $icon_url = $this->get_icon_url( $icon_code, $icon_set );
}

// Fallback: use helper function.
if ( empty( $icon_url ) && function_exists( 'pearl_weather_get_forecast_icon_url' ) ) {
    $icon_url = pearl_weather_get_forecast_icon_url( $icon_code, $icon_set );
}

// If still no URL, exit.
if ( empty( $icon_url ) ) {
    return;
}

// Icon settings.
$icon_size = isset( $attributes['forecastDataIconSize'] ) 
    ? ( isset( $attributes['forecastDataIconSize']['device']['Desktop'] ) ? (int) $attributes['forecastDataIconSize']['device']['Desktop'] : 48 )
    : 48;

$disable_animation = isset( $attributes['forecastIconAnimationDisable'] ) ? (bool) $attributes['forecastIconAnimationDisable'] : false;
$show_animation = ! $disable_animation;

// Get alt text from forecast description.
$alt_text = isset( $single_forecast['description'] ) ? $single_forecast['description'] : ( isset( $single_forecast['desc'] ) ? $single_forecast['desc'] : '' );

// Show description setting.
$show_desc = isset( $show_description ) ? (bool) $show_description : false;
$forecast_desc = isset( $single_forecast['description'] ) ? $single_forecast['description'] : ( isset( $single_forecast['desc'] ) ? $single_forecast['desc'] : '' );

// Layout orientation.
$is_vertical = isset( $attributes['forecastIconLayout'] ) && 'vertical' === $attributes['forecastIconLayout'];

// Additional CSS classes.
$wrapper_classes = array( 'pw-forecast-icon' );

if ( $show_animation ) {
    $wrapper_classes[] = 'pw-forecast-icon-animate';
}

if ( $is_vertical ) {
    $wrapper_classes[] = 'pw-forecast-icon-vertical';
}

if ( ! empty( $attributes['forecastIconCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['forecastIconCustomClass'] );
}

// Inline style for custom size.
$inline_style = '';
if ( $icon_size > 0 ) {
    $inline_style = 'style="--pw-forecast-icon-size: ' . $icon_size . 'px;"';
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" <?php echo $inline_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    
    <!-- Icon Image -->
    <div class="pw-forecast-icon-img-wrapper">
        <img class="pw-forecast-icon-img" 
             src="<?php echo esc_url( $icon_url ); ?>" 
             alt="<?php echo esc_attr( $alt_text ); ?>"
             title="<?php echo esc_attr( $alt_text ); ?>"
             width="<?php echo esc_attr( $icon_size ); ?>"
             height="<?php echo esc_attr( $icon_size ); ?>"
             loading="lazy"
             decoding="async" />
             
        <!-- Optional Badge (e.g., for night icons) -->
        <?php if ( isset( $single_forecast['is_night'] ) && $single_forecast['is_night'] ) : ?>
            <span class="pw-forecast-night-badge" aria-label="<?php esc_attr_e( 'Night time', 'pearl-weather' ); ?>">
                🌙
            </span>
        <?php endif; ?>
    </div>
    
    <!-- Forecast Description (Optional) -->
    <?php if ( $show_desc && ! empty( $forecast_desc ) ) : ?>
        <div class="pw-forecast-description">
            <span class="pw-forecast-desc-text"><?php echo esc_html( ucfirst( $forecast_desc ) ); ?></span>
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Forecast Icon Styles */
.pw-forecast-icon {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

/* Icon Size */
.pw-forecast-icon-img {
    width: var(--pw-forecast-icon-size, 48px);
    height: var(--pw-forecast-icon-size, 48px);
    object-fit: contain;
}

/* Vertical Layout (stacked) */
.pw-forecast-icon-vertical {
    flex-direction: column;
}

/* Horizontal Layout (inline with text) */
.pw-forecast-icon:not(.pw-forecast-icon-vertical) {
    flex-direction: row;
    gap: 8px;
}

/* Animation (Pulse) */
.pw-forecast-icon-animate .pw-forecast-icon-img {
    animation: pw-forecast-icon-pulse 2s ease-in-out infinite;
}

@keyframes pw-forecast-icon-pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

/* Icon Wrapper */
.pw-forecast-icon-img-wrapper {
    position: relative;
    display: inline-flex;
}

/* Night Badge */
.pw-forecast-night-badge {
    position: absolute;
    bottom: -4px;
    right: -8px;
    font-size: 12px;
    background: rgba(0, 0, 0, 0.6);
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
}

/* Forecast Description */
.pw-forecast-description {
    font-size: 12px;
    font-weight: 500;
    text-align: center;
    max-width: 80px;
    white-space: normal;
    word-break: break-word;
}

.pw-forecast-desc-text {
    display: inline-block;
    text-transform: capitalize;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-forecast-icon-img {
        width: calc(var(--pw-forecast-icon-size, 48px) * 0.8);
        height: calc(var(--pw-forecast-icon-size, 48px) * 0.8);
    }
    
    .pw-forecast-description {
        font-size: 10px;
        max-width: 65px;
    }
}

/* Reduced Motion Preference */
@media (prefers-reduced-motion: reduce) {
    .pw-forecast-icon-animate .pw-forecast-icon-img {
        animation: none;
    }
}

/* Hover Effect */
.pw-forecast-icon:hover .pw-forecast-icon-img {
    transform: scale(1.02);
    transition: transform 0.2s ease;
}
</style>

<?php
/**
 * Helper function to get forecast icon URL (if not already defined).
 */
if ( ! function_exists( 'pearl_weather_get_forecast_icon_url' ) ) {
    /**
     * Get forecast icon URL.
     *
     * @param string $icon_code Icon code from API.
     * @param string $icon_set  Icon set name.
     * @return string
     */
    function pearl_weather_get_forecast_icon_url( $icon_code, $icon_set = 'forecast_icon_set_one' ) {
        // Icon set folder mappings.
        $icon_folders = array(
            'forecast_icon_set_one'   => 'weather-icons',
            'forecast_icon_set_two'   => 'weather-static-icons',
            'forecast_icon_set_three' => 'light-line',
            'forecast_icon_set_four'  => 'fill-icon',
            'forecast_icon_set_five'  => 'weather-glassmorphism',
            'forecast_icon_set_six'   => 'animated-line',
            'forecast_icon_set_seven' => 'animated',
            'forecast_icon_set_eight' => 'medium-line',
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