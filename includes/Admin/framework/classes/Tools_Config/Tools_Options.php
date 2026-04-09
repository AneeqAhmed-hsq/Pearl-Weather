<?php
/**
 * Tools Settings Configuration
 *
 * Defines export and import tools for weather widgets.
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
$prefix = 'pearl_weather_tools';

/**
 * Create Tools Options Page
 */
Framework::create_options(
	$prefix,
	array(
		'menu_title'         => __( 'Tools', 'pearl-weather' ),
		'menu_slug'          => 'pw-tools',
		'menu_parent'        => 'edit.php?post_type=pearl_weather_widget',
		'menu_type'          => 'submenu',
		'show_search'        => false,
		'show_all_options'   => false,
		'show_reset_all'     => false,
		'framework_title'    => __( 'Tools', 'pearl-weather' ),
		'framework_class'    => 'pw-tools-options',
		'show_buttons'       => false, // Hide save button on tools page.
		'theme'              => 'light',
		'show_reset_section' => true,
	)
);

/**
 * Export Section
 */
Framework::create_section(
	$prefix,
	array(
		'title'  => __( 'Export', 'pearl-weather' ),
		'class'  => 'pw-export-section',
		'fields' => array(
			array(
				'id'       => 'export_type',
				'type'     => 'radio',
				'class'    => 'pw-export-type',
				'title'    => __( 'Choose What To Export', 'pearl-weather' ),
				'multiple' => false,
				'options'  => array(
					'all_widgets'      => __( 'All Widgets', 'pearl-weather' ),
					'selected_widgets' => __( 'Selected Widgets', 'pearl-weather' ),
				),
				'default'  => 'all_widgets',
			),
			array(
				'id'          => 'selected_widgets',
				'class'       => 'pw-selected-widgets',
				'type'        => 'select',
				'title'       => ' ',
				'options'     => 'pearl_weather_widget',
				'chosen'      => true,
				'sortable'    => false,
				'multiple'    => true,
				'placeholder' => __( 'Select Widget(s)', 'pearl-weather' ),
				'query_args'  => array(
					'posts_per_page' => -1,
				),
				'dependency'  => array( 'export_type', '==', 'selected_widgets' ),
			),
			array(
				'id'      => 'export_button',
				'class'   => 'pw-export-button',
				'type'    => 'button_set',
				'title'   => ' ',
				'options' => array(
					'' => __( 'Export', 'pearl-weather' ),
				),
			),
		),
	)
);

/**
 * Import Section
 */
Framework::create_section(
	$prefix,
	array(
		'class'  => 'pw-import-section',
		'title'  => __( 'Import', 'pearl-weather' ),
		'fields' => array(
			array(
				'class' => 'pw-import-field',
				'type'  => 'custom_import',
				'title' => __( 'Import JSON File', 'pearl-weather' ),
			),
		),
	)
);