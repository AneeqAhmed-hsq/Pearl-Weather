<?php
/**
 * Weather Icons Template
 *
 * Registers and maps icon classes for weather data display.
 * Supports multiple icon sets for additional data items.
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
 * - $show_weather_icons: Whether to show icons
 * - $additional_icon_set: Selected icon set
 */

// Enqueue icon font styles.
wp_enqueue_style( 'pearl-weather-icons' );

// Check if icons should be displayed.
$show_icons = isset( $attributes['additionalDataIcon'] ) ? (bool) $attributes['additionalDataIcon'] : true;

if ( ! $show_icons ) {
    return;
}

// Get selected icon set.
$icon_set = isset( $attributes['additionalDataIconType'] ) ? sanitize_text_field( $attributes['additionalDataIconType'] ) : 'icon_set_one';

// Icon set mapping.
$icon_set_map = array(
    'icon_set_one'   => 1,
    'icon_set_two'   => 2,
    'icon_set_three' => 3,
    'icon_set_four'  => 4,
    'icon_set_five'  => 5,
    'icon_set_six'   => 6,
    'icon_set_seven' => 7,
    'icon_set_eight' => 8,
);

$set_number = isset( $icon_set_map[ $icon_set ] ) ? $icon_set_map[ $icon_set ] : 1;

/**
 * Weather Icons Mapping
 * Maps weather data types to their corresponding CSS classes.
 *
 * @global array $pw_weather_icons
 */
$pw_weather_icons = array(
    // Core weather data.
    'humidity'      => 'pw-icon-humidity-' . $set_number,
    'pressure'      => 'pw-icon-pressure-' . $set_number,
    'wind'          => 'pw-icon-wind-' . $set_number,
    'gust'          => 'pw-icon-gust-' . $set_number,
    'clouds'        => 'pw-icon-clouds-' . $set_number,
    'visibility'    => 'pw-icon-visibility-' . $set_number,
    
    // Sun times.
    'sunrise'       => 'pw-icon-sunrise-' . $set_number,
    'sunset'        => 'pw-icon-sunset-' . $set_number,
    'sunrise_time'  => 'pw-icon-sunrise-' . $set_number,
    'sunset_time'   => 'pw-icon-sunset-' . $set_number,
    
    // Temperature and precipitation.
    'temperature'   => 'pw-icon-temperature-' . $set_number,
    'feels_like'    => 'pw-icon-thermometer-' . $set_number,
    'precipitation' => 'pw-icon-precipitation-' . $set_number,
    'rain_chance'   => 'pw-icon-rain-chance-' . $set_number,
    'rainchance'    => 'pw-icon-rain-chance-' . $set_number,
    
    // Additional metrics.
    'uv_index'      => 'pw-icon-uv-index-' . $set_number,
    'dew_point'     => 'pw-icon-dew-point-' . $set_number,
    'snow'          => 'pw-icon-snow-' . $set_number,
    'air_quality'   => 'pw-icon-air-quality-' . $set_number,
    
    // Moon phases.
    'moonrise'      => 'pw-icon-moonrise-' . $set_number,
    'moonset'       => 'pw-icon-moonset-' . $set_number,
    'moon_phase'    => 'pw-icon-moon-phase-' . $set_number,
);

/**
 * Filter the weather icons mapping.
 *
 * @since 1.0.0
 * @param array $pw_weather_icons Icon class mapping.
 * @param int   $set_number       Icon set number.
 */
$pw_weather_icons = apply_filters( 'pearl_weather_icons_map', $pw_weather_icons, $set_number );

// Make icons available to template parts.
if ( ! isset( $GLOBALS['pw_weather_icons'] ) ) {
    $GLOBALS['pw_weather_icons'] = $pw_weather_icons;
}

// Legacy variable for backward compatibility.
$block_weather_icons = $pw_weather_icons;

// Helper function to get icon class.
if ( ! function_exists( 'pearl_weather_get_icon_class' ) ) {
    /**
     * Get icon CSS class for a weather data type.
     *
     * @since 1.0.0
     * @param string $type Weather data type.
     * @return string
     */
    function pearl_weather_get_icon_class( $type ) {
        global $pw_weather_icons;
        
        if ( isset( $pw_weather_icons[ $type ] ) ) {
            return $pw_weather_icons[ $type ];
        }
        
        // Fallback icon.
        return 'pw-icon-default';
    }
}

// Helper function to render icon HTML.
if ( ! function_exists( 'pearl_weather_render_icon' ) ) {
    /**
     * Render icon HTML for a weather data type.
     *
     * @since 1.0.0
     * @param string $type Weather data type.
     * @param array  $args Additional arguments (class, style, etc.).
     * @return string
     */
    function pearl_weather_render_icon( $type, $args = array() ) {
        $defaults = array(
            'class' => '',
            'style' => '',
            'aria_hidden' => 'true',
        );
        
        $args = wp_parse_args( $args, $defaults );
        $icon_class = pearl_weather_get_icon_class( $type );
        
        $classes = array( $icon_class );
        if ( ! empty( $args['class'] ) ) {
            $classes[] = $args['class'];
        }
        
        $class_attr = implode( ' ', array_map( 'sanitize_html_class', $classes ) );
        $style_attr = ! empty( $args['style'] ) ? ' style="' . esc_attr( $args['style'] ) . '"' : '';
        $aria_hidden = $args['aria_hidden'] ? ' aria-hidden="true"' : '';
        
        return sprintf(
            '<i class="%s"%s%s></i>',
            esc_attr( $class_attr ),
            $style_attr,
            $aria_hidden
        );
    }
}

/**
 * Register icon font styles.
 * This would normally be done in the main plugin file.
 */
if ( ! wp_style_is( 'pearl-weather-icons', 'registered' ) ) {
    // Register icon font style.
    wp_register_style(
        'pearl-weather-icons',
        PEARL_WEATHER_ASSETS_URL . 'css/icons.css',
        array(),
        PEARL_WEATHER_VERSION
    );
}