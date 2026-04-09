<?php
/**
 * Typography Settings Configuration
 *
 * Defines all typography-related settings for the weather widget
 * including fonts, colors, and margins for all text elements.
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
 * Register Typography Settings Section
 */
Framework::create_section(
	'pw_weather_generator',
	array(
		'title'  => __( 'Typography', 'pearl-weather' ),
		'icon'   => '<span><svg width="14" height="14" viewBox="0 0 448 512"><path d="M432 432h-33.32l-135-389.24A16 16 0 0 0 248.55 32h-49.1a16 16 0 0 0-15.12 10.76L49.32 432H16a16 16 0 0 0-16 16v16a16 16 0 0 0 16 16h128a16 16 0 0 0 16-16v-16a16 16 0 0 0-16-16h-35.44l33.31-96h164.26l33.31 96H304a16 16 0 0 0-16 16v16a16 16 0 0 0 16 16h128a16 16 0 0 0 16-16v-16a16 16 0 0 0-16-16zM158.53 288L224 99.31 289.47 288z"/></svg></span>',
		'class'  => 'pw-typography-metabox',
		'fields' => array(
			// Pro Upgrade Notice.
			array(
				'type'    => 'notice',
				'style'   => 'info',
				'class'   => 'pw-typography-pro-notice',
				'content' => sprintf(
					/* translators: %1$s: upgrade link, %2$s: color fields note */
					__( 'The following typography options are %1$sPremium features%2$s except for the %3$sColor and Margin%4$s fields.', 'pearl-weather' ),
					'<a href="https://pearlweather.com/pricing/" target="_blank"><strong>',
					'</strong></a>',
					'<span class="pw-text-color"><strong>',
					'</strong></span>'
				),
			),
			
			// Typography Tabs.
			array(
				'type'  => 'tabbed',
				'class' => 'pw-typography-tabs',
				'tabs'  => array(
					
					// Tab 1: Regional Preferences.
					array(
						'title'  => __( 'Regional Preferences', 'pearl-weather' ),
						'icon'   => '<span><i class="pw-icon-regional"></i></span>',
						'fields' => array(
							array(
								'id'         => 'load_title_font',
								'type'       => 'switcher',
								'title'      => __( 'Load Section Title Font', 'pearl-weather' ),
								'subtitle'   => __( 'Enable Google Font for the section title.', 'pearl-weather' ),
								'class'      => 'pw-first-fields',
								'default'    => false,
								'dependency' => array( 'show_title', '==', 'true' ),
							),
							array(
								'id'         => 'title_typography',
								'type'       => 'typography',
								'class'      => 'pw-title-typography',
								'title'      => __( 'Section Title', 'pearl-weather' ),
								'default'    => array(
									'font-family'    => '',
									'font-style'     => 'normal',
									'text-align'     => 'center',
									'text-transform' => 'capitalize',
									'font-size'      => '27',
									'line-height'    => '28',
									'letter-spacing' => '0',
									'color'          => '#000',
									'margin-top'     => '0',
									'margin-bottom'  => '20',
								),
								'dependency' => array( 'show_title', '==', 'true' ),
							),
							array(
								'id'         => 'title_color',
								'type'       => 'color',
								'title'      => __( 'Title Color', 'pearl-weather' ),
								'default'    => '#000',
								'dependency' => array( 'show_title', '==', 'true' ),
							),
							array(
								'id'         => 'title_margin',
								'type'       => 'spacing',
								'class'      => 'pw-title-margin',
								'title'      => __( 'Title Margin', 'pearl-weather' ),
								'all'        => false,
								'left'       => false,
								'right'      => false,
								'min'        => 0,
								'max'        => 100,
								'units'      => array( 'px' ),
								'default'    => array(
									'top'    => '0',
									'bottom' => '20',
								),
								'dependency' => array( 'show_title', '==', 'true' ),
							),
							array(
								'id'       => 'load_location_font',
								'type'     => 'switcher',
								'title'    => __( 'Load Location Name Font', 'pearl-weather' ),
								'subtitle' => __( 'Enable Google Font for location name.', 'pearl-weather' ),
								'class'    => 'pw-location-font',
								'default'  => false,
							),
							array(
								'id'      => 'location_typography',
								'type'    => 'typography',
								'title'   => __( 'Location Name', 'pearl-weather' ),
								'class'   => 'pw-location-typography',
								'default' => array(
									'font-family'    => '',
									'font-style'     => 'normal',
									'text-align'     => 'center',
									'text-transform' => 'none',
									'font-size'      => '27',
									'line-height'    => '38',
									'letter-spacing' => '0',
									'color'          => '#fff',
									'margin-top'     => '0',
									'margin-bottom'  => '4',
								),
							),
							array(
								'id'      => 'location_color',
								'class'   => 'pw-location-color',
								'type'    => 'color',
								'title'   => __( 'Location Color', 'pearl-weather' ),
								'default' => '#fff',
							),
							array(
								'id'      => 'location_margin',
								'type'    => 'spacing',
								'class'   => 'pw-location-margin',
								'title'   => __( 'Location Margin', 'pearl-weather' ),
								'all'     => false,
								'left'    => false,
								'right'   => false,
								'min'     => 0,
								'max'     => 100,
								'units'   => array( 'px' ),
								'default' => array(
									'top'    => '0',
									'bottom' => '10',
								),
							),
							array(
								'id'       => 'load_datetime_font',
								'type'     => 'switcher',
								'title'    => __( 'Load Date & Time Font', 'pearl-weather' ),
								'subtitle' => __( 'Enable Google Font for date and time.', 'pearl-weather' ),
								'class'    => 'pw-datetime-font',
								'default'  => false,
							),
							array(
								'id'      => 'datetime_typography',
								'type'    => 'typography',
								'title'   => __( 'Date & Time', 'pearl-weather' ),
								'class'   => 'pw-datetime-typography',
								'default' => array(
									'font-family'    => '',
									'font-style'     => 'normal',
									'text-align'     => 'center',
									'text-transform' => 'none',
									'font-size'      => '14',
									'line-height'    => '20',
									'letter-spacing' => '0',
									'color'          => '#fff',
									'margin-top'     => '0',
									'margin-bottom'  => '10',
								),
							),
							array(
								'id'      => 'datetime_color',
								'type'    => 'color',
								'title'   => __( 'Date & Time Color', 'pearl-weather' ),
								'default' => '#fff',
							),
							array(
								'id'      => 'datetime_margin',
								'type'    => 'spacing',
								'class'   => 'pw-datetime-margin',
								'title'   => __( 'Date & Time Margin', 'pearl-weather' ),
								'all'     => false,
								'left'    => false,
								'right'   => false,
								'min'     => 0,
								'max'     => 100,
								'units'   => array( 'px' ),
								'default' => array(
									'top'    => '0',
									'bottom' => '10',
								),
							),
						),
					),
					
					// Tab 2: Current Weather.
					array(
						'title'  => __( 'Current Weather', 'pearl-weather' ),
						'icon'   => '<span><i class="pw-icon-current"></i></span>',
						'fields' => array(
							array(
								'id'       => 'load_temperature_font',
								'type'     => 'switcher',
								'title'    => __( 'Load Temperature Font', 'pearl-weather' ),
								'class'    => 'pw-temp-font',
								'subtitle' => __( 'Enable Google Font for temperature.', 'pearl-weather' ),
								'default'  => false,
							),
							array(
								'id'          => 'temperature_typography',
								'type'        => 'typography',
								'title'       => __( 'Temperature', 'pearl-weather' ),
								'class'       => 'pw-temp-typography',
								'line_height' => false,
								'default'     => array(
									'font-family'    => '',
									'font-style'     => 'normal',
									'text-align'     => 'center',
									'text-transform' => 'none',
									'font-size'      => '48',
									'letter-spacing' => '0',
									'color'          => '#fff',
									'margin-top'     => '0',
									'margin-bottom'  => '0',
								),
							),
							array(
								'id'      => 'temperature_color',
								'type'    => 'color',
								'title'   => __( 'Temperature Color', 'pearl-weather' ),
								'default' => '#fff',
							),
							array(
								'id'      => 'temperature_margin',
								'type'    => 'spacing',
								'class'   => 'pw-temp-margin',
								'title'   => __( 'Temperature Margin', 'pearl-weather' ),
								'all'     => false,
								'left'    => false,
								'right'   => false,
								'min'     => 0,
								'max'     => 100,
								'units'   => array( 'px' ),
								'default' => array(
									'top'    => '0',
									'bottom' => '0',
								),
							),
							array(
								'id'       => 'load_feels_like_font',
								'type'     => 'switcher',
								'title'    => __( 'Load Feels Like Font', 'pearl-weather' ),
								'class'    => 'pw-feels-font',
								'subtitle' => __( 'Enable Google Font for feels like temperature.', 'pearl-weather' ),
								'default'  => false,
							),
							array(
								'id'      => 'feels_like_typography',
								'type'    => 'typography',
								'title'   => __( 'Feels Like', 'pearl-weather' ),
								'class'   => 'pw-feels-typography',
								'default' => array(
									'font-family'    => '',
									'font-style'     => 'normal',
									'text-align'     => 'center',
									'text-transform' => 'none',
									'font-size'      => '14',
									'line-height'    => '20',
									'letter-spacing' => '0',
									'color'          => '#fff',
									'margin-top'     => '10',
									'margin-bottom'  => '0',
								),
							),
							array(
								'id'      => 'feels_like_color',
								'type'    => 'color',
								'title'   => __( 'Feels Like Color', 'pearl-weather' ),
								'default' => '#fff',
							),
							array(
								'id'      => 'feels_like_margin',
								'type'    => 'spacing',
								'class'   => 'pw-feels-margin',
								'title'   => __( 'Feels Like Margin', 'pearl-weather' ),
								'all'     => false,
								'left'    => false,
								'right'   => false,
								'min'     => 0,
								'max'     => 100,
								'units'   => array( 'px' ),
								'default' => array(
									'top'    => '10',
									'bottom' => '0',
								),
							),
							array(
								'id'       => 'load_description_font',
								'type'     => 'switcher',
								'title'    => __( 'Load Weather Description Font', 'pearl-weather' ),
								'subtitle' => __( 'Enable Google Font for weather description.', 'pearl-weather' ),
								'class'    => 'pw-desc-font',
								'default'  => false,
							),
							array(
								'id'      => 'description_typography',
								'type'    => 'typography',
								'title'   => __( 'Weather Description', 'pearl-weather' ),
								'class'   => 'pw-desc-typography',
								'default' => array(
									'font-family'    => '',
									'font-style'     => 'normal',
									'text-align'     => 'center',
									'text-transform' => 'capitalize',
									'font-size'      => '14',
									'line-height'    => '20',
									'letter-spacing' => '0',
									'color'          => '#fff',
									'margin-top'     => '10',
									'margin-bottom'  => '0',
								),
							),
							array(
								'id'      => 'description_color',
								'type'    => 'color',
								'title'   => __( 'Description Color', 'pearl-weather' ),
								'default' => '#fff',
							),
							array(
								'id'      => 'description_margin',
								'type'    => 'spacing',
								'class'   => 'pw-desc-margin',
								'title'   => __( 'Description Margin', 'pearl-weather' ),
								'all'     => false,
								'left'    => false,
								'right'   => false,
								'min'     => 0,
								'max'     => 100,
								'units'   => array( 'px' ),
								'default' => array(
									'top'    => '10',
									'bottom' => '0',
								),
							),
						),
					),
					
					// Tab 3: Additional Data.
					array(
						'title'  => __( 'Additional Data', 'pearl-weather' ),
						'icon'   => '<span><i class="pw-icon-additional"></i></span>',
						'fields' => array(
							array(
								'id'       => 'load_additional_font',
								'type'     => 'switcher',
								'title'    => __( 'Load Additional Data Font', 'pearl-weather' ),
								'subtitle' => __( 'Enable Google Font for additional weather data.', 'pearl-weather' ),
								'class'    => 'pw-additional-font',
								'default'  => false,
							),
							array(
								'id'      => 'additional_typography',
								'type'    => 'typography',
								'title'   => __( 'Additional Data', 'pearl-weather' ),
								'class'   => 'pw-additional-typography',
								'default' => array(
									'font-family'    => '',
									'font-style'     => 'normal',
									'text-align'     => 'center',
									'text-transform' => 'none',
									'font-size'      => '13',
									'line-height'    => '17',
									'letter-spacing' => '0',
									'color'          => '#fff',
								),
							),
							array(
								'id'      => 'additional_color',
								'type'    => 'color',
								'title'   => __( 'Additional Data Color', 'pearl-weather' ),
								'default' => '#fff',
							),
							array(
								'id'      => 'additional_margin',
								'type'    => 'spacing',
								'class'   => 'pw-additional-margin',
								'title'   => __( 'Additional Data Margin', 'pearl-weather' ),
								'all'     => false,
								'left'    => false,
								'right'   => false,
								'min'     => 0,
								'max'     => 100,
								'units'   => array( 'px' ),
								'default' => array(
									'top'    => '16',
									'bottom' => '0',
								),
							),
						),
					),
					
					// Tab 4: Forecast Data.
					array(
						'title'  => __( 'Forecast Data', 'pearl-weather' ),
						'icon'   => '<span><i class="pw-icon-forecast"></i></span>',
						'fields' => array(
							array(
								'id'       => 'load_forecast_font',
								'type'     => 'switcher',
								'title'    => __( 'Load Forecast Font', 'pearl-weather' ),
								'subtitle' => __( 'Enable Google Font for forecast data.', 'pearl-weather' ),
								'class'    => 'pw-forecast-font',
								'default'  => false,
							),
							array(
								'id'      => 'forecast_typography',
								'type'    => 'typography',
								'title'   => __( 'Forecast Data', 'pearl-weather' ),
								'class'   => 'pw-forecast-typography',
								'default' => array(
									'font-family'    => '',
									'font-style'     => 'normal',
									'text-transform' => 'none',
									'font-size'      => '13',
									'letter-spacing' => '0',
									'color'          => '#fff',
									'margin-top'     => '0',
									'margin-bottom'  => '0',
								),
							),
							array(
								'id'      => 'forecast_color',
								'type'    => 'color',
								'title'   => __( 'Forecast Color', 'pearl-weather' ),
								'default' => '#fff',
							),
							array(
								'id'      => 'forecast_margin',
								'type'    => 'spacing',
								'title'   => __( 'Forecast Margin', 'pearl-weather' ),
								'all'     => false,
								'left'    => false,
								'right'   => false,
								'min'     => 0,
								'max'     => 100,
								'units'   => array( 'px' ),
								'default' => array(
									'top'    => '0',
									'bottom' => '0',
								),
							),
						),
					),
					
					// Tab 5: Footer.
					array(
						'title'  => __( 'Footer', 'pearl-weather' ),
						'icon'   => '<span><i class="pw-icon-footer"></i></span>',
						'fields' => array(
							array(
								'id'       => 'load_attribution_font',
								'type'     => 'switcher',
								'title'    => __( 'Load Attribution Font', 'pearl-weather' ),
								'class'    => 'pw-attribution-font',
								'subtitle' => __( 'Enable Google Font for attribution text.', 'pearl-weather' ),
								'default'  => false,
							),
							array(
								'id'      => 'attribution_typography',
								'type'    => 'typography',
								'title'   => __( 'Attribution', 'pearl-weather' ),
								'class'   => 'pw-attribution-typography',
								'default' => array(
									'font-family'    => '',
									'font-style'     => 'normal',
									'text-align'     => 'center',
									'text-transform' => 'none',
									'font-size'      => '11',
									'line-height'    => '26',
									'letter-spacing' => '0',
									'color'          => '#fff',
								),
							),
							array(
								'id'      => 'attribution_color',
								'type'    => 'color',
								'title'   => __( 'Attribution Color', 'pearl-weather' ),
								'default' => '#fff',
							),
						),
					),
				),
			),
		),
	)
);