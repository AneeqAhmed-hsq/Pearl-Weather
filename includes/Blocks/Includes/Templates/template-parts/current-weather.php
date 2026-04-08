<?php
/**
 * Current Weather Template Part
 *
 * Displays current weather information including location, date/time,
 * weather icon, temperature, and conditions.
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

// Display settings.
$show_location = isset( $attributes['showLocationName'] ) ? (bool) $attributes['showLocationName'] : true;
$show_datetime = isset( $attributes['showCurrentDate'] ) || isset( $attributes['showCurrentTime'] ) ? true : false;
$show_icon = isset( $attributes['weatherConditionIcon'] ) ? (bool) $attributes['weatherConditionIcon'] : true;
$show_temperature = isset( $attributes['displayTemperature'] ) ? (bool) $attributes['displayTemperature'] : true;
$show_description = isset( $attributes['displayWeatherConditions'] ) ? (bool) $attributes['displayWeatherConditions'] : true;

// Layout orientation.
$is_vertical = isset( $attributes['layoutOrientation'] ) && 'vertical' === $attributes['layoutOrientation'];

// Get weather data with fallbacks.
$city_name = isset( $weather_data['city'] ) ? $weather_data['city'] : ( isset( $weather_data['city_name'] ) ? $weather_data['city_name'] : '' );
$country_code = isset( $weather_data['country'] ) ? $weather_data['country'] : '';
$location_display = ! empty( $country_code ) ? $city_name . ', ' . $country_code : $city_name;

// Weather icon URL.
$icon_url = isset( $weather_data['icon'] ) ? $weather_data['icon'] : ( isset( $weather_data['weather_icon'] ) ? $weather_data['weather_icon'] : '' );
$icon_alt = isset( $weather_data['description'] ) ? $weather_data['description'] : ( isset( $weather_data['weather_desc'] ) ? $weather_data['weather_desc'] : '' );

// Animation setting.
$disable_animation = isset( $attributes['disableWeatherIconAnimation'] ) ? (bool) $attributes['disableWeatherIconAnimation'] : false;

// Additional CSS classes.
$wrapper_classes = array(
    'pw-current-weather',
    $is_vertical ? 'pw-layout-vertical' : 'pw-layout-horizontal',
);

if ( ! empty( $attributes['currentWeatherCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['currentWeatherCustomClass'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
    
    <!-- Header Section: Location + Date/Time -->
    <div class="pw-current-header">
        
        <!-- Location Name Component -->
        <?php if ( $show_location && ! empty( $location_display ) ) : ?>
            <div class="pw-location-section">
                <?php
                // Try to include the location-name template part.
                $location_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/location-name.php';
                if ( file_exists( $location_template ) ) {
                    include $location_template;
                } else {
                    // Fallback location display.
                    ?>
                    <div class="pw-location-name">
                        <svg class="pw-location-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                            <circle cx="12" cy="9" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
                        </svg>
                        <h3 class="pw-city-name"><?php echo esc_html( $location_display ); ?></h3>
                    </div>
                    <?php
                }
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Date & Time Component -->
        <?php if ( $show_datetime ) : ?>
            <div class="pw-datetime-section">
                <?php
                $datetime_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/date-time.php';
                if ( file_exists( $datetime_template ) ) {
                    include $datetime_template;
                } else {
                    // Fallback datetime display.
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
    
    <!-- Weather Icon and Temperature Section -->
    <div class="pw-weather-main-display">
        
        <!-- Weather Icon Component -->
        <?php if ( $show_icon && ! empty( $icon_url ) ) : ?>
            <div class="pw-weather-icon-section">
                <?php
                $icon_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/weather-icon.php';
                if ( file_exists( $icon_template ) ) {
                    include $icon_template;
                } else {
                    // Fallback icon display.
                    ?>
                    <div class="pw-weather-icon <?php echo $disable_animation ? 'pw-no-animation' : 'pw-animate'; ?>">
                        <img src="<?php echo esc_url( $icon_url ); ?>" 
                             alt="<?php echo esc_attr( $icon_alt ); ?>"
                             loading="lazy">
                    </div>
                    <?php
                }
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Current Temperature Component -->
        <?php if ( $show_temperature ) : ?>
            <div class="pw-temperature-section">
                <?php
                $temperature_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/current-temperature.php';
                if ( file_exists( $temperature_template ) ) {
                    include $temperature_template;
                } else {
                    // Fallback temperature display (will be handled by the temperature template).
                    ?>
                    <div class="pw-temperature-fallback">
                        <?php esc_html_e( 'Temperature template not found', 'pearl-weather' ); ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Weather Description Component -->
    <?php if ( $show_description ) : ?>
        <div class="pw-weather-description-section">
            <?php
            $description_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/weather-description.php';
            if ( file_exists( $description_template ) ) {
                include $description_template;
            } else {
                // Fallback description display.
                $description = isset( $weather_data['description'] ) ? $weather_data['description'] : ( isset( $weather_data['weather_desc'] ) ? $weather_data['weather_desc'] : '' );
                if ( ! empty( $description ) ) :
                    ?>
                    <div class="pw-weather-description">
                        <span class="pw-desc-text"><?php echo esc_html( $description ); ?></span>
                    </div>
                    <?php
                endif;
            }
            ?>
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Current Weather Styles */
.pw-current-weather {
    width: 100%;
}

/* Header Section */
.pw-current-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}

.pw-location-name {
    display: flex;
    align-items: center;
    gap: 6px;
}

.pw-location-icon {
    flex-shrink: 0;
}

.pw-city-name {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
}

.pw-datetime {
    display: flex;
    gap: 8px;
    font-size: 14px;
    opacity: 0.7;
}

/* Main Display (Icon + Temperature) */
.pw-weather-main-display {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

/* Weather Icon */
.pw-weather-icon {
    flex-shrink: 0;
}

.pw-weather-icon img {
    width: 80px;
    height: 80px;
    object-fit: contain;
}

.pw-weather-icon.pw-animate img {
    animation: pw-weatherFloat 3s ease-in-out infinite;
}

@keyframes pw-weatherFloat {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

.pw-weather-icon.pw-no-animation img {
    animation: none;
}

/* Temperature Section */
.pw-temperature-section {
    flex-shrink: 0;
}

/* Layout Variations */
.pw-layout-vertical {
    text-align: center;
}

.pw-layout-vertical .pw-current-header {
    justify-content: center;
    flex-direction: column;
}

.pw-layout-vertical .pw-datetime {
    justify-content: center;
}

.pw-layout-vertical .pw-weather-main-display {
    flex-direction: column;
}

/* Weather Description */
.pw-weather-description-section {
    text-align: center;
}

.pw-weather-description {
    font-size: 16px;
    font-weight: 500;
    text-transform: capitalize;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-current-header {
        flex-direction: column;
        text-align: center;
    }
    
    .pw-city-name {
        font-size: 18px;
    }
    
    .pw-weather-icon img {
        width: 60px;
        height: 60px;
    }
    
    .pw-weather-description {
        font-size: 14px;
    }
}
</style>