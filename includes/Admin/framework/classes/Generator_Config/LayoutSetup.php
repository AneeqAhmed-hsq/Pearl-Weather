<?php
/**
 * Layout Settings Configuration
 *
 * Defines all layout-related settings for the weather widget
 * including layout type selection and template variations.
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

// Set a unique slug-like ID.
$layout_prefix = 'pw_weather_layout';

/**
 * Register Layout Metabox
 */
Framework::create_metabox(
	$layout_prefix,
	array(
		'title'        => __( 'Weather Widget Layout', 'pearl-weather' ),
		'post_type'    => 'pearl_weather_widget',
		'show_restore' => true,
		'preview'      => false,
		'class'        => 'pw-layout-options',
	)
);

/**
 * Register Layout Section
 */
Framework::create_section(
	$layout_prefix,
	array(
		'fields' => array(
			// Header Section with Logo and Support Link.
			array(
				'type'    => 'heading',
				'image'   => esc_url( PEARL_WEATHER_ASSETS_URL ) . 'images/pearl-weather-logo.svg',
				'after'   => '<i class="fa fa-life-ring"></i> ' . __( 'Support', 'pearl-weather' ),
				'link'    => 'https://pearlweather.com/support/',
				'class'   => 'pw-admin-header',
				'version' => PEARL_WEATHER_VERSION,
			),
			
			// Main Layout Selection.
			array(
				'id'      => 'weather_view',
				'type'    => 'image_select',
				'class'   => 'pw-weather-view pw-first-fields',
				'title'   => __( 'Weather Layout', 'pearl-weather' ),
				'options' => array(
					'vertical'   => array(
						'image'           => Framework::get_url( 'assets/images/layouts/vertical.svg' ),
						'name'            => __( 'Vertical Card', 'pearl-weather' ),
						'demo_url'        => 'https://pearlweather.com/demos/vertical-card/',
					),
					'horizontal' => array(
						'image'           => Framework::get_url( 'assets/images/layouts/horizontal.svg' ),
						'name'            => __( 'Horizontal', 'pearl-weather' ),
						'demo_url'        => 'https://pearlweather.com/demos/horizontal/',
					),
					'tabs'       => array(
						'image'           => Framework::get_url( 'assets/images/layouts/tabs.svg' ),
						'name'            => __( 'Tabs', 'pearl-weather' ),
						'demo_url'        => 'https://pearlweather.com/demos/tabs/',
						'pro_only'        => true,
					),
					'table'      => array(
						'image'           => Framework::get_url( 'assets/images/layouts/table.svg' ),
						'name'            => __( 'Table', 'pearl-weather' ),
						'demo_url'        => 'https://pearlweather.com/demos/table/',
						'pro_only'        => true,
					),
					'accordion'  => array(
						'image'           => Framework::get_url( 'assets/images/layouts/accordion.svg' ),
						'name'            => __( 'Accordion', 'pearl-weather' ),
						'demo_url'        => 'https://pearlweather.com/demos/accordion/',
						'pro_only'        => true,
					),
					'grid'       => array(
						'image'           => Framework::get_url( 'assets/images/layouts/grid.svg' ),
						'name'            => __( 'Grid', 'pearl-weather' ),
						'demo_url'        => 'https://pearlweather.com/demos/grid/',
						'pro_only'        => true,
					),
					'combined'   => array(
						'image'           => Framework::get_url( 'assets/images/layouts/combined.svg' ),
						'name'            => __( 'Combined', 'pearl-weather' ),
						'demo_url'        => 'https://pearlweather.com/demos/combined/',
						'pro_only'        => true,
					),
					'map'        => array(
						'image'           => Framework::get_url( 'assets/images/layouts/map.svg' ),
						'name'            => __( 'Weather Map', 'pearl-weather' ),
						'demo_url'        => 'https://pearlweather.com/demos/weather-map/',
						'pro_only'        => true,
					),
				),
				'default' => 'vertical',
			),
			
			// Vertical Layout Templates.
			array(
				'id'         => 'vertical_template',
				'type'       => 'image_select',
				'class'      => 'pw-vertical-template',
				'title'      => __( 'Templates', 'pearl-weather' ),
				'options'    => array(
					'template_one'   => array(
						'image' => Framework::get_url( 'assets/images/layouts/vertical/template-one.svg' ),
						'name'  => __( 'Template One', 'pearl-weather' ),
					),
					'template_two'   => array(
						'image'    => Framework::get_url( 'assets/images/layouts/vertical/template-two.svg' ),
						'name'     => __( 'Template Two', 'pearl-weather' ),
						'pro_only' => true,
					),
					'template_three' => array(
						'image'    => Framework::get_url( 'assets/images/layouts/vertical/template-three.svg' ),
						'name'     => __( 'Template Three', 'pearl-weather' ),
						'pro_only' => true,
					),
					'template_four'  => array(
						'image'    => Framework::get_url( 'assets/images/layouts/vertical/template-four.svg' ),
						'name'     => __( 'Template Four', 'pearl-weather' ),
						'pro_only' => true,
					),
				),
				'default'    => 'template_one',
				'dependency' => array( 'weather_view', '==', 'vertical' ),
			),
			
			// Horizontal Layout Templates.
			array(
				'id'         => 'horizontal_template',
				'type'       => 'image_select',
				'class'      => 'pw-horizontal-template',
				'title'      => __( 'Templates', 'pearl-weather' ),
				'options'    => array(
					'template_one'   => array(
						'image' => Framework::get_url( 'assets/images/layouts/horizontal/template-one.svg' ),
						'name'  => __( 'Template One', 'pearl-weather' ),
					),
					'template_two'   => array(
						'image'    => Framework::get_url( 'assets/images/layouts/horizontal/template-two.svg' ),
						'name'     => __( 'Template Two', 'pearl-weather' ),
						'pro_only' => true,
					),
					'template_three' => array(
						'image'    => Framework::get_url( 'assets/images/layouts/horizontal/template-three.svg' ),
						'name'     => __( 'Template Three', 'pearl-weather' ),
						'pro_only' => true,
					),
				),
				'default'    => 'template_one',
				'dependency' => array( 'weather_view', '==', 'horizontal' ),
			),
			
			// Tabs Layout Templates.
			array(
				'id'         => 'tabs_template',
				'type'       => 'image_select',
				'class'      => 'pw-tabs-template',
				'title'      => __( 'Templates', 'pearl-weather' ),
				'options'    => array(
					'template_one' => array(
						'image'    => Framework::get_url( 'assets/images/layouts/tabs/template-one.svg' ),
						'name'     => __( 'Template One', 'pearl-weather' ),
						'pro_only' => true,
					),
					'template_two' => array(
						'image'    => Framework::get_url( 'assets/images/layouts/tabs/template-two.svg' ),
						'name'     => __( 'Template Two', 'pearl-weather' ),
						'pro_only' => true,
					),
				),
				'default'    => 'template_one',
				'dependency' => array( 'weather_view', '==', 'tabs' ),
			),
			
			// Table Layout Templates.
			array(
				'id'         => 'table_template',
				'type'       => 'image_select',
				'class'      => 'pw-table-template',
				'title'      => __( 'Templates', 'pearl-weather' ),
				'options'    => array(
					'template_one' => array(
						'image'    => Framework::get_url( 'assets/images/layouts/table/template-one.svg' ),
						'name'     => __( 'Template One', 'pearl-weather' ),
						'pro_only' => true,
					),
					'template_two' => array(
						'image'    => Framework::get_url( 'assets/images/layouts/table/template-two.svg' ),
						'name'     => __( 'Template Two', 'pearl-weather' ),
						'pro_only' => true,
					),
				),
				'default'    => 'template_one',
				'dependency' => array( 'weather_view', '==', 'table' ),
			),
			
			// Accordion Layout Templates.
			array(
				'id'         => 'accordion_template',
				'type'       => 'image_select',
				'class'      => 'pw-accordion-template',
				'title'      => __( 'Templates', 'pearl-weather' ),
				'options'    => array(
					'template_one'   => array(
						'image'    => Framework::get_url( 'assets/images/layouts/accordion/template-one.svg' ),
						'name'     => __( 'Template One', 'pearl-weather' ),
						'pro_only' => true,
					),
					'template_two'   => array(
						'image'    => Framework::get_url( 'assets/images/layouts/accordion/template-two.svg' ),
						'name'     => __( 'Template Two', 'pearl-weather' ),
						'pro_only' => true,
					),
					'template_three' => array(
						'image'    => Framework::get_url( 'assets/images/layouts/accordion/template-three.svg' ),
						'name'     => __( 'Template Three', 'pearl-weather' ),
						'pro_only' => true,
					),
				),
				'default'    => 'template_one',
				'dependency' => array( 'weather_view', '==', 'accordion' ),
			),
			
			// Grid Layout Templates.
			array(
				'id'         => 'grid_template',
				'type'       => 'image_select',
				'class'      => 'pw-grid-template',
				'title'      => __( 'Templates', 'pearl-weather' ),
				'options'    => array(
					'template_one'   => array(
						'image'    => Framework::get_url( 'assets/images/layouts/grid/template-one.svg' ),
						'name'     => __( 'Template One', 'pearl-weather' ),
						'pro_only' => true,
					),
					'template_two'   => array(
						'image'    => Framework::get_url( 'assets/images/layouts/grid/template-two.svg' ),
						'name'     => __( 'Template Two', 'pearl-weather' ),
						'pro_only' => true,
					),
					'template_three' => array(
						'image'    => Framework::get_url( 'assets/images/layouts/grid/template-three.svg' ),
						'name'     => __( 'Template Three', 'pearl-weather' ),
						'pro_only' => true,
					),
				),
				'default'    => 'template_one',
				'dependency' => array( 'weather_view', '==', 'grid' ),
			),
			
			// Map Type Selection.
			array(
				'id'         => 'map_type',
				'type'       => 'button_set',
				'class'      => 'pw-map-type',
				'title'      => __( 'Map Type', 'pearl-weather' ),
				'title_info' => sprintf(
					'<div class="pw-info-label">%s</div><div class="pw-short-content">%s</div>',
					__( 'Map Type', 'pearl-weather' ),
					__( 'Choose between OpenWeatherMap or Windy Radar Map.', 'pearl-weather' )
				),
				'options'    => array(
					'openweather' => __( 'Weather Map', 'pearl-weather' ),
					'windy'       => __( 'Radar Map', 'pearl-weather' ),
				),
				'default'    => 'openweather',
				'dependency' => array( 'weather_view', 'any', 'map,combined,grid' ),
			),
			
			// Enable Map in Tabs.
			array(
				'id'         => 'enable_map_in_tabs',
				'class'      => 'pw-enable-map-in-tabs',
				'type'       => 'switcher',
				'title'      => __( 'Enable Weather Map in Tabs', 'pearl-weather' ),
				'default'    => false,
				'text_on'    => __( 'Enabled', 'pearl-weather' ),
				'text_off'   => __( 'Disabled', 'pearl-weather' ),
				'text_width' => 100,
				'dependency' => array( 'weather_view', '==', 'tabs' ),
			),
			
			// Pro Upgrade Notice.
			array(
				'id'      => 'layout_pro_notice',
				'class'   => 'pw-layout-pro-notice',
				'type'    => 'notice',
				'style'   => 'info',
				'content' => sprintf(
					/* translators: %1$s: demo link, %2$s: upgrade link */
					__( 'To create eye-catching %1$sWeather Layouts%2$s with advanced customizations, %3$sUpgrade to Pro!%4$s', 'pearl-weather' ),
					'<a class="pw-demo-link" href="https://pearlweather.com/demos/" target="_blank"><strong>',
					'</strong></a>',
					'<a class="pw-upgrade-link" href="https://pearlweather.com/pricing/" target="_blank"><strong>',
					'</strong></a>'
				),
			),
		),
	)
);