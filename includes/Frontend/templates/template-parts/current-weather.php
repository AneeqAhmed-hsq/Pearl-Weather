<?php
/**
 * Current Weather Template
 *
 * Displays the current weather icon, temperature, and description.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Templates/Parts
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template variables:
 * - $weather_data: Current weather data array
 * - $show_icon: Whether to show weather icon
 * - $show_temperature: Whether to show temperature
 * - $short_description: Whether to show description
 * - $lw_current_icon_type: Icon set type
 * - $splw_meta: Widget meta settings
 * - $weather_icon_size: Icon size in pixels
 */

// Get weather icon URL.
$icon_code = isset( $weather_data['icon'] ) ? $weather_data['icon'] : '01d';
$icon_set = isset( $lw_current_icon_type ) ? $lw_current_icon_type : 'icon_set_one';

// Determine icon URL based on icon set.
if ( 'forecast_icon_set_one' === $icon_set ) {
    $icon_url = PEARL_WEATHER_ASSETS_URL . 'images/icons/weather-icons/' . $icon_code . '.svg';
} else {
    $icon_url = PEARL_WEATHER_ASSETS_URL . 'images/icons/weather-static-icons/' . $icon_code . '.svg';
}

// Allow filtering of icon URL.
$icon_url = apply_filters( 'pearl_weather_current_icon_url', $icon_url, $icon_code, $icon_set );

// Icon size.
$icon_size = isset( $weather_icon_size ) ? (int) $weather_icon_size : 58;

// Temperature value (may contain HTML from Unit class).
$temperature = isset( $weather_data['temp'] ) ? $weather_data['temp'] : ( isset( $weather_data['temperature'] ) ? $weather_data['temperature'] : '--' );

// Description.
$description = isset( $weather_data['desc'] ) ? $weather_data['desc'] : ( isset( $weather_data['description'] ) ? $weather_data['description'] : '' );

// Check display flags.
$show_icon_flag = isset( $show_icon ) ? (bool) $show_icon : true;
$show_temp_flag = isset( $show_temperature ) ? (bool) $show_temperature : true;
$show_desc_flag = isset( $short_description ) ? (bool) $short_description : true;

// CSS classes.
$wrapper_classes = array( 'pw-current-weather' );

if ( ! empty( $splw_meta['current_weather_custom_class'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $splw_meta['current_weather_custom_class'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
    
    <!-- Temperature and Icon Section -->
    <?php if ( $show_icon_flag || $show_temp_flag ) : ?>
        <div class="pw-current-temp-wrapper">
            <div class="pw-current-temp">
                
                <!-- Weather Icon -->
                <?php if ( $show_icon_flag && ! empty( $icon_url ) ) : ?>
                    <img src="<?php echo esc_url( $icon_url ); ?>" 
                         class="pw-weather-icon" 
                         alt="<?php esc_attr_e( 'Weather icon', 'pearl-weather' ); ?>"
                         width="<?php echo esc_attr( $icon_size ); ?>"
                         height="<?php echo esc_attr( $icon_size ); ?>"
                         loading="eager"
                         decoding="async">
                <?php endif; ?>
                
                <!-- Temperature -->
                <?php if ( $show_temp_flag && ! empty( $temperature ) ) : ?>
                    <div class="pw-temperature">
                        <?php echo wp_kses_post( $temperature ); ?>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Weather Description -->
    <?php if ( $show_desc_flag && ! empty( $description ) ) : ?>
        <div class="pw-weather-description">
            <span class="pw-description-text"><?php echo esc_html( $description ); ?></span>
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Current Weather Styles */
.pw-current-weather {
    text-align: center;
    margin-bottom: 16px;
}

.pw-current-temp {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
}

.pw-weather-icon {
    object-fit: contain;
}

.pw-temperature {
    font-size: 48px;
    font-weight: 700;
    line-height: 1.2;
}

.pw-temperature .temperature-scale {
    font-size: 24px;
    font-weight: 500;
}

.pw-weather-description {
    margin-top: 12px;
    font-size: 16px;
    font-weight: 500;
    text-transform: capitalize;
    color: #555;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-temperature {
        font-size: 36px;
    }
    
    .pw-temperature .temperature-scale {
        font-size: 18px;
    }
    
    .pw-weather-description {
        font-size: 14px;
    }
}
</style>