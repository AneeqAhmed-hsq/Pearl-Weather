<?php
/**
 * Weather Icons Configuration
 *
 * Defines icon classes, labels, and helper functions for weather data display.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Assets
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Weather icons configuration array.
 *
 * Each entry contains:
 * - 'icon'  : CSS class for the icon font
 * - 'label' : Display label for the weather data type
 * - 'unit'  : Optional unit suffix
 *
 * @var array
 */
$pw_weather_icons_config = array(
    'humidity' => array(
        'icon'  => 'pw-icon-humidity',
        'label' => __( 'Humidity', 'pearl-weather' ),
        'unit'  => '%',
    ),
    'pressure' => array(
        'icon'  => 'pw-icon-pressure',
        'label' => __( 'Pressure', 'pearl-weather' ),
        'unit'  => 'hPa',
    ),
    'wind' => array(
        'icon'  => 'pw-icon-wind',
        'label' => __( 'Wind', 'pearl-weather' ),
        'unit'  => '',
    ),
    'wind_gust' => array(
        'icon'  => 'pw-icon-wind-gust',
        'label' => __( 'Wind Gust', 'pearl-weather' ),
        'unit'  => '',
    ),
    'clouds' => array(
        'icon'  => 'pw-icon-clouds',
        'label' => __( 'Clouds', 'pearl-weather' ),
        'unit'  => '%',
    ),
    'visibility' => array(
        'icon'  => 'pw-icon-visibility',
        'label' => __( 'Visibility', 'pearl-weather' ),
        'unit'  => 'km',
    ),
    'sunrise' => array(
        'icon'  => 'pw-icon-sunrise',
        'label' => __( 'Sunrise', 'pearl-weather' ),
        'unit'  => '',
    ),
    'sunset' => array(
        'icon'  => 'pw-icon-sunset',
        'label' => __( 'Sunset', 'pearl-weather' ),
        'unit'  => '',
    ),
    'temperature' => array(
        'icon'  => 'pw-icon-temperature',
        'label' => __( 'Temperature', 'pearl-weather' ),
        'unit'  => '',
    ),
    'feels_like' => array(
        'icon'  => 'pw-icon-thermometer',
        'label' => __( 'Feels Like', 'pearl-weather' ),
        'unit'  => '',
    ),
    'precipitation' => array(
        'icon'  => 'pw-icon-precipitation',
        'label' => __( 'Precipitation', 'pearl-weather' ),
        'unit'  => 'mm',
    ),
    'rain_chance' => array(
        'icon'  => 'pw-icon-rain-chance',
        'label' => __( 'Rain Chance', 'pearl-weather' ),
        'unit'  => '%',
    ),
    'uv_index' => array(
        'icon'  => 'pw-icon-uv-index',
        'label' => __( 'UV Index', 'pearl-weather' ),
        'unit'  => '',
    ),
    'dew_point' => array(
        'icon'  => 'pw-icon-dew-point',
        'label' => __( 'Dew Point', 'pearl-weather' ),
        'unit'  => '°',
    ),
    'air_quality' => array(
        'icon'  => 'pw-icon-air-quality',
        'label' => __( 'Air Quality', 'pearl-weather' ),
        'unit'  => 'AQI',
    ),
);

/**
 * Get weather icon HTML.
 *
 * @since 1.0.0
 * @param string $key        Weather data key.
 * @param array  $attributes Additional attributes (class, style, title).
 * @return string
 */
function pw_get_weather_icon( $key, $attributes = array() ) {
    global $pw_weather_icons_config;
    
    if ( ! isset( $pw_weather_icons_config[ $key ] ) ) {
        return '';
    }
    
    $config = $pw_weather_icons_config[ $key ];
    $icon_class = $config['icon'];
    
    // Add icon set number if available.
    global $pw_icon_set_number;
    if ( ! empty( $pw_icon_set_number ) ) {
        $icon_class .= '-' . $pw_icon_set_number;
    }
    
    $defaults = array(
        'class' => '',
        'style' => '',
        'title' => $config['label'],
    );
    
    $attributes = wp_parse_args( $attributes, $defaults );
    $classes = array( $icon_class );
    
    if ( ! empty( $attributes['class'] ) ) {
        $classes[] = $attributes['class'];
    }
    
    $class_attr = implode( ' ', array_map( 'sanitize_html_class', $classes ) );
    $style_attr = ! empty( $attributes['style'] ) ? ' style="' . esc_attr( $attributes['style'] ) . '"' : '';
    $title_attr = ! empty( $attributes['title'] ) ? ' title="' . esc_attr( $attributes['title'] ) . '"' : '';
    
    return sprintf(
        '<span class="pw-weather-icon-wrapper"%s%s><i class="%s" aria-hidden="true"></i></span>',
        $title_attr,
        $style_attr,
        esc_attr( $class_attr )
    );
}

/**
 * Get weather label.
 *
 * @since 1.0.0
 * @param string $key Weather data key.
 * @param bool   $include_colon Whether to append a colon.
 * @return string
 */
function pw_get_weather_label( $key, $include_colon = false ) {
    global $pw_weather_icons_config;
    
    if ( isset( $pw_weather_icons_config[ $key ]['label'] ) ) {
        $label = $pw_weather_icons_config[ $key ]['label'];
        return $include_colon ? $label . ':' : $label;
    }
    
    return ucfirst( str_replace( '_', ' ', $key ) );
}

/**
 * Get weather unit.
 *
 * @since 1.0.0
 * @param string $key Weather data key.
 * @return string
 */
function pw_get_weather_unit( $key ) {
    global $pw_weather_icons_config;
    
    if ( isset( $pw_weather_icons_config[ $key ]['unit'] ) ) {
        return $pw_weather_icons_config[ $key ]['unit'];
    }
    
    return '';
}

/**
 * Register additional weather icons.
 *
 * @since 1.0.0
 * @param string $key   Weather data key.
 * @param array  $config Icon configuration (icon, label, unit).
 */
function pw_register_weather_icon( $key, $config ) {
    global $pw_weather_icons_config;
    
    if ( ! isset( $pw_weather_icons_config[ $key ] ) ) {
        $pw_weather_icons_config[ $key ] = wp_parse_args( $config, array(
            'icon'  => '',
            'label' => ucfirst( str_replace( '_', ' ', $key ) ),
            'unit'  => '',
        ) );
    }
}

/**
 * Legacy function for backward compatibility.
 *
 * @deprecated 1.0.0 Use pw_get_weather_icon() instead.
 */
function splwp_get_icon( $icon_class, $title = '', $show = false ) {
    if ( ! $show ) {
        return '';
    }
    
    $title_attr = ! empty( $title ) ? ' title="' . esc_attr( $title ) . '"' : '';
    return '<span class="details-icon"' . $title_attr . '><i class="' . esc_attr( $icon_class ) . '"></i></span>';
}

// Make configuration available globally.
if ( ! isset( $GLOBALS['pw_weather_icons_config'] ) ) {
    $GLOBALS['pw_weather_icons_config'] = $pw_weather_icons_config;
}