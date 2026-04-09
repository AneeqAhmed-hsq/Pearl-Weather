<?php
/**
 * Weather Map Settings Configuration
 *
 * Defines all map-related settings for the weather widget
 * including map preferences, layer controls, and popup data.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Admin/Config
 * @since      1.0.0
 */

namespace PearlWeather\Admin\Config;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PearlWeather\Framework\Framework;

/**
 * Register Map Settings Section
 */
Framework::create_section(
	'pw_weather_generator',
	array(
		'title'  => __( 'Weather Map Settings', 'pearl-weather' ),
		'icon'   => '<span><i class="pw-icon-weather-map"></i></span>',
		'fields' => array(
			// Pro Upgrade Notice.
			array(
				'type'    => 'notice',
				'style'   => 'info',
				'class'   => 'pw-map-pro-notice',
				'content' => sprintf(
					/* translators: %1$s: weather map demo link, %2$s: radar map demo link, %3$s: upgrade link */
					__( 'To unlock live %1$sWeather Maps%2$s and %3$sRadar Maps%4$s to track storms and weather in real-time, %5$sUpgrade to Pro!%6$s', 'pearl-weather' ),
					'<a href="https://pearlweather.com/demos/weather-map/" target="_blank"><strong>',
					'</strong></a>',
					'<a href="https://pearlweather.com/demos/radar-map/" target="_blank"><strong>',
					'</strong></a>',
					'<a href="https://pearlweather.com/pricing/" target="_blank"><strong>',
					'</strong></a>'
				),
			),
			
			// Map Settings Tabs.
			array(
				'type'       => 'tabbed',
				'class'      => 'pw-map-tabs',
				'dependency' => array( 'weather_view', 'any', 'map,tabs,combined,accordion,grid' ),
				'tabs'       => array(
					// Tab 1: Map Preferences.
					array(
						'title'  => __( 'Map Preferences', 'pearl-weather' ),
						'icon'   => '<span><i class="pw-icon-map-preferences"></i></span>',
						'fields' => array(
							array(
								'id'         => 'map_section_title',
								'type'       => 'switcher',
								'title'      => __( 'Weather Map Section Title', 'pearl-weather' ),
								'class'      => 'pw-first-fields',
								'default'    => false,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 75,
							),
							array(
								'id'         => 'map_layer_display_type',
								'type'       => 'button_set',
								'class'      => 'pw-map-display-type',
								'title'      => __( 'Data Layer Display Type', 'pearl-weather' ),
								'title_info' => '<div class="pw-info-label">' . __( 'Display Layers Type', 'pearl-weather' ) . '</div><div class="pw-short-content">' . __( 'Choose visible or collapsible to display map layers.', 'pearl-weather' ) . '</div>',
								'options'    => array(
									'visible'     => __( 'Visible', 'pearl-weather' ),
									'collapsible' => __( 'Collapsible', 'pearl-weather' ),
								),
								'default'    => 'visible',
							),
							array(
								'id'         => 'map_layer_opacity',
								'class'      => 'pw-map-layer-opacity',
								'type'       => 'slider',
								'title'      => __( 'Layers Opacity', 'pearl-weather' ),
								'unit'       => '%',
								'max'        => 100,
								'min'        => 10,
								'default'    => 50,
							),
							array(
								'id'         => 'map_zoom_level',
								'type'       => 'slider',
								'class'      => 'pw-map-zoom-level',
								'title'      => __( 'Zoom Level', 'pearl-weather' ),
								'max'        => 20,
								'min'        => 5,
								'default'    => 8,
							),
							array(
								'id'         => 'map_enable_zoom_scroll',
								'type'       => 'switcher',
								'class'      => 'pw-map-zoom-scroll',
								'title'      => __( 'Zoom Scroll Wheel', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Enabled', 'pearl-weather' ),
								'text_off'   => __( 'Disabled', 'pearl-weather' ),
								'text_width' => 100,
							),
							array(
								'id'         => 'map_show_legends',
								'type'       => 'switcher',
								'class'      => 'pw-map-legends',
								'title'      => __( 'Layer Label Indicator', 'pearl-weather' ),
								'default'    => false,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
						),
					),
					
					// Tab 2: Control Layers.
					array(
						'title'  => __( 'Control Layers', 'pearl-weather' ),
						'icon'   => '<span><i class="pw-icon-control-layer"></i></span>',
						'fields' => array(
							array(
								'id'         => 'map_show_temp_layer',
								'type'       => 'switcher',
								'class'      => 'pw-map-temp-layer',
								'title'      => __( 'Temperature', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_show_precipitation_layer',
								'type'       => 'switcher',
								'class'      => 'pw-map-precipitation-layer',
								'title'      => __( 'Precipitation', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_show_pressure_layer',
								'type'       => 'switcher',
								'class'      => 'pw-map-pressure-layer',
								'title'      => __( 'Pressure', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_show_clouds_layer',
								'type'       => 'switcher',
								'class'      => 'pw-map-clouds-layer',
								'title'      => __( 'Clouds', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_show_wind_layer',
								'type'       => 'switcher',
								'class'      => 'pw-map-wind-layer',
								'title'      => __( 'Wind Speed', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_show_wind_direction_layer',
								'type'       => 'switcher',
								'class'      => 'pw-map-wind-dir-layer',
								'title'      => __( 'Wind Direction', 'pearl-weather' ),
								'default'    => false,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_show_rain_layer',
								'type'       => 'switcher',
								'class'      => 'pw-map-rain-layer',
								'title'      => __( 'Rain Chance', 'pearl-weather' ),
								'default'    => false,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_show_snow_layer',
								'type'       => 'switcher',
								'class'      => 'pw-map-snow-layer',
								'title'      => __( 'Snow', 'pearl-weather' ),
								'default'    => false,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
						),
					),
					
					// Tab 3: Popup Weather Data.
					array(
						'title'  => __( 'Popup Weather Data', 'pearl-weather' ),
						'icon'   => '<span><i class="pw-icon-popup-weather"></i></span>',
						'fields' => array(
							array(
								'id'         => 'map_popup_show_location',
								'type'       => 'switcher',
								'class'      => 'pw-map-popup-location',
								'title'      => __( 'Location', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_popup_show_temp',
								'type'       => 'switcher',
								'class'      => 'pw-map-popup-temp',
								'title'      => __( 'Temperature', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_popup_show_icon',
								'type'       => 'switcher',
								'class'      => 'pw-map-popup-icon',
								'title'      => __( 'Weather Condition Icon', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_popup_show_desc',
								'type'       => 'switcher',
								'class'      => 'pw-map-popup-desc',
								'title'      => __( 'Weather Description', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_popup_show_high_low',
								'type'       => 'switcher',
								'class'      => 'pw-map-popup-highlow',
								'title'      => __( 'High & Low Temperature', 'pearl-weather' ),
								'default'    => false,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_popup_show_clouds',
								'type'       => 'switcher',
								'class'      => 'pw-map-popup-clouds',
								'title'      => __( 'Clouds', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_popup_show_humidity',
								'type'       => 'switcher',
								'class'      => 'pw-map-popup-humidity',
								'title'      => __( 'Humidity', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_popup_show_pressure',
								'type'       => 'switcher',
								'class'      => 'pw-map-popup-pressure',
								'title'      => __( 'Pressure', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_popup_show_wind_speed',
								'type'       => 'switcher',
								'class'      => 'pw-map-popup-wind',
								'title'      => __( 'Wind Speed', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_popup_show_wind_direction',
								'type'       => 'switcher',
								'class'      => 'pw-map-popup-wind-dir',
								'title'      => __( 'Wind Direction', 'pearl-weather' ),
								'default'    => true,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_popup_show_visibility',
								'type'       => 'switcher',
								'class'      => 'pw-map-popup-visibility',
								'title'      => __( 'Visibility', 'pearl-weather' ),
								'default'    => false,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
							array(
								'id'         => 'map_popup_show_datetime',
								'type'       => 'switcher',
								'class'      => 'pw-map-popup-datetime',
								'title'      => __( 'Time & Date', 'pearl-weather' ),
								'default'    => false,
								'text_on'    => __( 'Show', 'pearl-weather' ),
								'text_off'   => __( 'Hide', 'pearl-weather' ),
								'text_width' => 80,
							),
						),
					),
				),
			),
		),
	)
);