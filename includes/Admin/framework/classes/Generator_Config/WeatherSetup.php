<?php
/**
 * Weather Settings Configuration
 *
 * Defines all weather-related settings for the weather widget
 * including location selection, measurement units, and pro features.
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

// Set unique slug-like ID.
$widget_prefix = 'pw_weather_generator';

/**
 * Live Preview Metabox
 */
Framework::create_metabox(
	'pw_weather_live_preview',
	array(
		'title'        => __( 'Live Preview', 'pearl-weather' ),
		'post_type'    => 'pearl_weather_widget',
		'show_restore' => false,
		'preview'      => false,
		'context'      => 'normal',
	)
);
Framework::create_section(
	'pw_weather_live_preview',
	array(
		'fields' => array(
			array(
				'type' => 'preview',
			),
		),
	)
);

/**
 * How To Use Metabox (Sidebar)
 */
$display_shortcode = 'pw_shortcode_info';

Framework::create_metabox(
	$display_shortcode,
	array(
		'title'     => __( 'How To Use', 'pearl-weather' ),
		'post_type' => 'pearl_weather_widget',
		'context'   => 'side',
	)
);

Framework::create_section(
	$display_shortcode,
	array(
		'fields' => array(
			array(
				'type'      => 'shortcode',
				'class'     => 'pw-admin-sidebar',
				'shortcode' => 'shortcode',
			),
		),
	)
);

/**
 * Page Builders Info Metabox
 */
Framework::create_metabox(
	'pw_builder_info',
	array(
		'title'        => __( 'Page Builders', 'pearl-weather' ),
		'post_type'    => 'pearl_weather_widget',
		'context'      => 'side',
		'show_restore' => false,
	)
);

Framework::create_section(
	'pw_builder_info',
	array(
		'fields' => array(
			array(
				'type'      => 'shortcode',
				'shortcode' => false,
				'class'     => 'pw-builder-sidebar',
			),
		),
	)
);

/**
 * Pro Notice Metabox
 */
Framework::create_metabox(
	'pw_pro_notice',
	array(
		'title'        => __( 'Unlock Pro Features', 'pearl-weather' ),
		'post_type'    => 'pearl_weather_widget',
		'context'      => 'side',
		'show_restore' => false,
	)
);

Framework::create_section(
	'pw_pro_notice',
	array(
		'fields' => array(
			array(
				'type'      => 'shortcode',
				'shortcode' => 'pro_notice',
				'class'     => 'pw-pro-sidebar',
			),
		),
	)
);

/**
 * Main Weather Settings Metabox
 */
Framework::create_metabox(
	$widget_prefix,
	array(
		'title'        => __( 'Weather Widget Settings', 'pearl-weather' ),
		'post_type'    => 'pearl_weather_widget',
		'show_restore' => true,
		'class'        => 'pw-widget-options',
	)
);

/**
 * Weather Settings Section
 */
Framework::create_section(
	$widget_prefix,
	array(
		'title'  => __( 'Weather Settings', 'pearl-weather' ),
		'icon'   => '<span><i class="pw-icon-weather-settings"></i></span>',
		'class'  => 'pw-weather-settings-metabox',
		'fields' => array(
			// Location Selection Method.
			array(
				'id'         => 'search_by',
				'type'       => 'button_set',
				'class'      => 'pw-first-fields',
				'title'      => __( 'Location Selection Method', 'pearl-weather' ),
				'title_info' => sprintf(
					'<div class="pw-info-label">%s</div><div class="pw-short-content">%s</div><a class="pw-docs-link" href="https://pearlweather.com/docs/location-selection/" target="_blank">%s</a>',
					__( 'Location Selection', 'pearl-weather' ),
					__( 'Choose how to specify the location for weather display.', 'pearl-weather' ),
					__( 'Open Docs', 'pearl-weather' )
				),
				'options'    => array(
					'city_name' => __( 'City Name', 'pearl-weather' ),
					'city_id'   => __( 'City ID', 'pearl-weather' ),
					'zip'       => __( 'ZIP Code', 'pearl-weather' ),
					'latlong'   => __( 'Coordinates', 'pearl-weather' ),
				),
				'default'    => 'city_name',
			),
			
			// City Name.
			array(
				'id'          => 'city_name',
				'type'        => 'text',
				'class'       => 'pw-text-fields',
				'title'       => __( 'City Name', 'pearl-weather' ),
				'placeholder' => __( 'London, GB', 'pearl-weather' ),
				'desc'        => __( 'Enter city name with country code (e.g., London, GB).', 'pearl-weather' ),
				'dependency'  => array( 'search_by', '==', 'city_name' ),
			),
			
			// City ID.
			array(
				'id'          => 'city_id',
				'type'        => 'text',
				'class'       => 'pw-text-fields',
				'title'       => __( 'City ID', 'pearl-weather' ),
				'title_info'  => sprintf(
					'<div class="pw-info-label">%s</div><div class="pw-short-content">%s</div><a class="pw-docs-link" href="https://pearlweather.com/docs/city-id/" target="_blank">%s</a>',
					__( 'City ID', 'pearl-weather' ),
					__( 'Enter the OpenWeatherMap city ID.', 'pearl-weather' ),
					__( 'Open Docs', 'pearl-weather' )
				),
				'placeholder' => __( '2643743', 'pearl-weather' ),
				'desc'        => sprintf(
					/* translators: %s: link to get city ID */
					__( 'Get your city ID from %s.', 'pearl-weather' ),
					'<a href="https://openweathermap.org/find" target="_blank">' . __( 'OpenWeatherMap', 'pearl-weather' ) . '</a>'
				),
				'dependency'  => array( 'search_by', '==', 'city_id' ),
			),
			
			// ZIP Code.
			array(
				'id'          => 'zip_code',
				'type'        => 'text',
				'class'       => 'pw-text-fields',
				'title'       => __( 'ZIP Code', 'pearl-weather' ),
				'placeholder' => __( '77070, US', 'pearl-weather' ),
				'desc'        => sprintf(
					/* translators: %s: link to instructions */
					__( 'Enter ZIP code with country code. See %s for details.', 'pearl-weather' ),
					'<a href="https://pearlweather.com/docs/zip-code/" target="_blank">' . __( 'instructions', 'pearl-weather' ) . '</a>'
				),
				'dependency'  => array( 'search_by', '==', 'zip' ),
			),
			
			// Coordinates.
			array(
				'id'          => 'coordinates',
				'type'        => 'text',
				'class'       => 'pw-text-fields',
				'title'       => __( 'Coordinates', 'pearl-weather' ),
				'placeholder' => __( '51.509865,-0.118092', 'pearl-weather' ),
				'desc'        => sprintf(
					/* translators: %s: link to get coordinates */
					__( 'Enter latitude and longitude. %s to get coordinates.', 'pearl-weather' ),
					'<a href="https://www.latlong.net/" target="_blank">' . __( 'Click here', 'pearl-weather' ) . '</a>'
				),
				'dependency'  => array( 'search_by', '==', 'latlong' ),
			),
			
			// Custom Location Name.
			array(
				'id'         => 'custom_location',
				'type'       => 'text',
				'title'      => __( 'Custom Location Name', 'pearl-weather' ),
				'title_info' => sprintf(
					'<div class="pw-info-label">%s</div><div class="pw-short-content">%s</div><a class="pw-docs-link" href="https://pearlweather.com/docs/custom-location/" target="_blank">%s</a>',
					__( 'Custom Location Name', 'pearl-weather' ),
					__( 'Override the displayed location name with your own text.', 'pearl-weather' ),
					__( 'Open Docs', 'pearl-weather' )
				),
			),
			
			// Visitor Location Auto-Detect.
			array(
				'id'         => 'auto_detect_location',
				'type'       => 'switcher',
				'class'      => 'pw-auto-location',
				'title'      => __( 'Auto-Detect Visitor Location', 'pearl-weather' ),
				'title_info' => sprintf(
					'<div class="pw-info-label">%s</div><div class="pw-short-content">%s</div><a class="pw-docs-link" href="https://pearlweather.com/docs/auto-detect/" target="_blank">%s</a><a class="pw-demo-link" href="https://pearlweather.com/demos/auto-detect/" target="_blank">%s</a>',
					__( 'Auto-Detect Location', 'pearl-weather' ),
					__( 'Automatically detect and display weather for the visitor\'s location using IP address.', 'pearl-weather' ),
					__( 'Open Docs', 'pearl-weather' ),
					__( 'Live Demo', 'pearl-weather' )
				),
				'text_on'    => __( 'Enabled', 'pearl-weather' ),
				'text_off'   => __( 'Disabled', 'pearl-weather' ),
				'text_width' => 99,
				'default'    => false,
			),
			
			// Custom Weather Search.
			array(
				'id'         => 'enable_weather_search',
				'type'       => 'switcher',
				'title'      => __( 'Custom Weather Search', 'pearl-weather' ),
				'class'      => 'pw-weather-search',
				'title_info' => sprintf(
					'<div class="pw-info-label">%s</div><div class="pw-short-content">%s</div><a class="pw-demo-link" href="https://pearlweather.com/demos/weather-search/" target="_blank">%s</a>',
					__( 'Weather Search', 'pearl-weather' ),
					__( 'Allow visitors to search for weather in other cities.', 'pearl-weather' ),
					__( 'Live Demo', 'pearl-weather' )
				),
				'text_on'    => __( 'Enabled', 'pearl-weather' ),
				'text_off'   => __( 'Disabled', 'pearl-weather' ),
				'text_width' => 99,
				'default'    => false,
			),
			
			// Measurement Units Subheading.
			array(
				'id'    => 'measurement_units_heading',
				'type'  => 'subheading',
				'title' => __( 'Measurement Units', 'pearl-weather' ),
			),
			
			// Temperature Unit.
			array(
				'id'         => 'temperature_unit',
				'class'      => 'pw-temp-unit',
				'type'       => 'select',
				'title'      => __( 'Temperature Unit', 'pearl-weather' ),
				'title_info' => sprintf(
					'<div class="pw-info-label">%s</div><div class="pw-short-content">%s</div><a class="pw-docs-link" href="https://pearlweather.com/docs/temperature-unit/" target="_blank">%s</a>',
					__( 'Temperature Unit', 'pearl-weather' ),
					__( 'Choose the temperature unit for display.', 'pearl-weather' ),
					__( 'Open Docs', 'pearl-weather' )
				),
				'options'    => array(
					'metric'    => __( 'Celsius (°C)', 'pearl-weather' ),
					'imperial'  => __( 'Fahrenheit (°F)', 'pearl-weather' ),
					'both'      => __( 'Both (°C & °F)', 'pearl-weather' ),
				),
				'default'    => 'metric',
			),
			
			// Active Temperature Unit (for both mode).
			array(
				'id'         => 'active_temp_unit',
				'type'       => 'button_set',
				'class'      => 'pw-active-temp-unit',
				'title'      => __( 'Active Temperature Unit', 'pearl-weather' ),
				'title_info' => __( 'Set which unit is active by default when both are displayed.', 'pearl-weather' ),
				'options'    => array(
					'metric'   => __( '°C', 'pearl-weather' ),
					'imperial' => __( '°F', 'pearl-weather' ),
				),
				'default'    => 'metric',
				'dependency' => array( 'temperature_unit', '==', 'both' ),
			),
			
			// Pressure Unit.
			array(
				'id'      => 'pressure_unit',
				'class'   => 'pw-pressure-unit',
				'type'    => 'select',
				'title'   => __( 'Pressure Unit', 'pearl-weather' ),
				'title_info' => __( 'Select the unit for atmospheric pressure display.', 'pearl-weather' ),
				'options' => array(
					'mb'   => __( 'Millibars (mb)', 'pearl-weather' ),
					'hpa'  => __( 'Hectopascals (hPa)', 'pearl-weather' ),
					'inhg' => __( 'Inches of Mercury (inHg)', 'pearl-weather' ),
					'psi'  => __( 'Pounds per Square Inch (psi)', 'pearl-weather' ),
				),
				'default' => 'mb',
			),
			
			// Precipitation Unit.
			array(
				'id'      => 'precipitation_unit',
				'class'   => 'pw-precipitation-unit',
				'type'    => 'select',
				'title'   => __( 'Precipitation Unit', 'pearl-weather' ),
				'options' => array(
					'mm'   => __( 'Millimeters (mm)', 'pearl-weather' ),
					'inch' => __( 'Inches (in)', 'pearl-weather' ),
				),
				'default' => 'mm',
			),
			
			// Wind Speed Unit.
			array(
				'id'      => 'wind_speed_unit',
				'class'   => 'pw-wind-speed-unit',
				'type'    => 'select',
				'title'   => __( 'Wind Speed Unit', 'pearl-weather' ),
				'options' => array(
					'mph' => __( 'Miles per hour (mph)', 'pearl-weather' ),
					'kmh' => __( 'Kilometers per hour (km/h)', 'pearl-weather' ),
					'ms'  => __( 'Meters per second (m/s)', 'pearl-weather' ),
					'kts' => __( 'Knots (kn)', 'pearl-weather' ),
				),
				'default' => 'mph',
			),
			
			// Visibility Unit.
			array(
				'id'      => 'visibility_unit',
				'type'    => 'select',
				'title'   => __( 'Visibility Unit', 'pearl-weather' ),
				'options' => array(
					'km' => __( 'Kilometers', 'pearl-weather' ),
					'mi' => __( 'Miles', 'pearl-weather' ),
				),
				'default' => 'km',
			),
		),
	)
);