<?php
/**
 * Style Settings Configuration
 *
 * Defines all style-related settings for the weather widget
 * including background, padding, border, shadow, and width.
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
 * Register Style Settings Section
 */
Framework::create_section(
	'pw_weather_generator',
	array(
		'title'  => __( 'Style Settings', 'pearl-weather' ),
		'icon'   => '<span><i class="pw-icon-style"></i></span>',
		'class'  => 'pw-style-settings-metabox',
		'fields' => array(
			// Background Type Selection.
			array(
				'id'         => 'background_type',
				'class'      => 'pw-background-type',
				'type'       => 'button_set',
				'title_info' => sprintf(
					'<div class="pw-info-label">%s</div><div class="pw-short-content">%s</div><a class="pw-docs-link" href="https://pearlweather.com/docs/background-type/" target="_blank">%s</a><a class="pw-demo-link" href="https://pearlweather.com/demos/background-type/" target="_blank">%s</a>',
					__( 'Background Type', 'pearl-weather' ),
					__( 'Customize color, weather-based image, or video background for your weather widget.', 'pearl-weather' ),
					__( 'Open Docs', 'pearl-weather' ),
					__( 'Live Demo', 'pearl-weather' )
				),
				'title'      => __( 'Background Type', 'pearl-weather' ),
				'options'    => array(
					'solid'   => __( 'Color', 'pearl-weather' ),
					'image'   => __( 'Weather-based Image', 'pearl-weather' ),
					'video'   => __( 'Video', 'pearl-weather' ),
				),
				'default'    => 'solid',
			),
			
			// Color Type (Solid vs Gradient).
			array(
				'id'         => 'color_type',
				'type'       => 'button_set',
				'title'      => __( 'Color Type', 'pearl-weather' ),
				'class'      => 'pw-color-type pw-first-fields',
				'options'    => array(
					'solid'    => __( 'Solid', 'pearl-weather' ),
					'gradient' => __( 'Gradient', 'pearl-weather' ),
				),
				'title_info' => sprintf(
					'<div class="pw-info-label">%s</div><div class="pw-short-content">%s</div>',
					__( 'Color Type', 'pearl-weather' ),
					__( 'Choose solid for a single color or gradient for a blended color effect.', 'pearl-weather' )
				),
				'default'    => 'solid',
				'dependency' => array( 'background_type', '==', 'solid' ),
			),
			
			// Solid Background Color.
			array(
				'id'         => 'solid_bg_color',
				'type'       => 'color',
				'title'      => __( 'Solid Color', 'pearl-weather' ),
				'default'    => '#f26c0d',
				'dependency' => array( 'background_type|color_type', '==|==', 'solid|solid' ),
			),
			
			// Gradient Background.
			array(
				'id'         => 'gradient_bg',
				'type'       => 'text',
				'title'      => __( 'Gradient', 'pearl-weather' ),
				'default'    => 'linear-gradient(135deg, #f26c0d 0%, #e9510c 100%)',
				'dependency' => array( 'background_type|color_type', '==|==', 'solid|gradient' ),
				'title_info' => sprintf(
					'<div class="pw-info-label">%s</div><div class="pw-short-content">%s</div>',
					__( 'Gradient', 'pearl-weather' ),
					__( 'Enter CSS gradient value for the background.', 'pearl-weather' )
				),
			),
			
			// Content Padding.
			array(
				'id'         => 'content_padding',
				'type'       => 'spacing',
				'class'      => 'pw-content-padding',
				'title'      => __( 'Content Padding', 'pearl-weather' ),
				'all'        => false,
				'min'        => 0,
				'max'        => 100,
				'units'      => array( 'px', '%' ),
				'default'    => array(
					'top'    => '16',
					'right'  => '20',
					'bottom' => '10',
					'left'   => '20',
				),
				'title_info' => '<div class="pw-img-tag"><img src="' . Framework::get_url( 'assets/images/content-padding.svg' ) . '" alt="content-padding"></div><div class="pw-info-label img">' . __( 'Weather Content Padding', 'pearl-weather' ) . '</div>',
			),
			
			// Border Settings.
			array(
				'id'      => 'widget_border',
				'type'    => 'border',
				'title'   => __( 'Border', 'pearl-weather' ),
				'all'     => true,
				'default' => array(
					'all'   => '0',
					'style' => 'solid',
					'color' => '#e2e2e2',
				),
			),
			
			// Border Radius.
			array(
				'id'        => 'border_radius',
				'type'      => 'spacing',
				'title'     => __( 'Radius', 'pearl-weather' ),
				'all'       => true,
				'all_title' => __( 'Radius', 'pearl-weather' ),
				'min'       => 0,
				'max'       => 100,
				'units'     => array( 'px', '%' ),
				'default'   => array(
					'all' => '8',
				),
			),
			
			// Box Shadow Type.
			array(
				'id'         => 'box_shadow_type',
				'type'       => 'button_set',
				'title'      => __( 'Box Shadow', 'pearl-weather' ),
				'options'    => array(
					'none'   => __( 'None', 'pearl-weather' ),
					'outset' => __( 'Outset', 'pearl-weather' ),
					'inset'  => __( 'Inset', 'pearl-weather' ),
				),
				'default'    => 'none',
				'dependency' => array( 'weather_view', 'any', 'vertical,horizontal' ),
			),
			
			// Box Shadow Values.
			array(
				'id'         => 'box_shadow_values',
				'type'       => 'box_shadow',
				'title'      => __( 'Box Shadow Values', 'pearl-weather' ),
				'style'      => false,
				'default'    => array(
					'vertical'   => '4',
					'horizontal' => '4',
					'blur'       => '16',
					'spread'     => '0',
					'color'      => 'rgba(0,0,0,0.30)',
				),
				'dependency' => array( 'weather_view|box_shadow_type', 'any|!=', 'vertical,horizontal|none' ),
			),
			
			// Maximum Width.
			array(
				'id'         => 'max_width',
				'class'      => 'pw-max-width',
				'type'       => 'spacing',
				'title'      => __( 'Widget Maximum Width', 'pearl-weather' ),
				'all'        => true,
				'all_icon'   => '<i class="fas fa-arrows-alt-h"></i>',
				'all_title'  => __( 'Width', 'pearl-weather' ),
				'min'        => 0,
				'max'        => 1920,
				'units'      => array( 'px', '%' ),
				'default'    => array(
					'all' => '400',
				),
				'title_info' => sprintf(
					'<div class="pw-info-label">%s</div><div class="pw-short-content">%s</div><a class="pw-docs-link" href="https://pearlweather.com/docs/max-width/" target="_blank">%s</a>',
					__( 'Widget Maximum Width', 'pearl-weather' ),
					__( 'Set the maximum width of the weather widget to match your content area.', 'pearl-weather' ),
					__( 'Open Docs', 'pearl-weather' )
				),
			),
			
			// Pro Upgrade Notice.
			array(
				'type'    => 'notice',
				'style'   => 'info',
				'class'   => 'pw-notice-padding',
				'content' => sprintf(
					/* translators: %1$s: demo link, %2$s: upgrade link */
					__( 'To craft your desired %1$sWeather View%2$s with advanced customizations, %3$sUpgrade to Pro!%4$s', 'pearl-weather' ),
					'<a class="pw-demo-link" href="https://pearlweather.com/demos/background-type/" target="_blank"><strong>',
					'</strong></a>',
					'<a class="pw-upgrade-link" href="https://pearlweather.com/pricing/" target="_blank"><strong>',
					'</strong></a>'
				),
			),
		),
	)
);