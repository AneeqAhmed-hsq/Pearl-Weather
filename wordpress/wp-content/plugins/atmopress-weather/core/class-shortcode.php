<?php
namespace AtmoPress;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Shortcode {

    public function __construct() {
        add_shortcode( 'atmopress', array( $this, 'render' ) );
        add_shortcode( 'atmopress-weather', array( $this, 'render' ) );
    }

    /**
     * [atmopress location="London" template="card" units="metric"
     *   show_search="true" show_humidity="true" show_wind="true"
     *   show_daily="true" show_hourly="true" forecast_days="7"
     *   color_primary="#2563eb" border_radius="16"]
     */
    public function render( $atts ) {
        $atts = shortcode_atts( array(
            'location'         => Settings::get( 'default_location', 'London' ),
            'template'         => 'card',
            'units'            => Settings::get( 'units', 'metric' ),
            'show_search'      => 'true',
            'show_geolocation' => 'true',
            'show_humidity'    => 'true',
            'show_wind'        => 'true',
            'show_pressure'    => 'true',
            'show_visibility'  => 'true',
            'show_feels_like'  => 'true',
            'show_sunrise'     => 'true',
            'show_hourly'      => 'true',
            'show_daily'       => 'true',
            'forecast_days'    => '7',
            'hourly_count'     => '8',
            'color_primary'    => '#2563eb',
            'color_bg'         => '#ffffff',
            'color_text'       => '#1e293b',
            'border_radius'    => '16',
            'font_size'        => '14',
            'custom_class'     => '',
        ), $atts, 'atmopress' );

        $config = array(
            'template'         => sanitize_key( $atts['template'] ),
            'location'         => sanitize_text_field( $atts['location'] ),
            'units'            => in_array( $atts['units'], array( 'metric', 'imperial' ), true ) ? $atts['units'] : 'metric',
            'show_search'      => filter_var( $atts['show_search'],      FILTER_VALIDATE_BOOLEAN ),
            'show_geolocation' => filter_var( $atts['show_geolocation'], FILTER_VALIDATE_BOOLEAN ),
            'show_humidity'    => filter_var( $atts['show_humidity'],    FILTER_VALIDATE_BOOLEAN ),
            'show_wind'        => filter_var( $atts['show_wind'],        FILTER_VALIDATE_BOOLEAN ),
            'show_pressure'    => filter_var( $atts['show_pressure'],    FILTER_VALIDATE_BOOLEAN ),
            'show_visibility'  => filter_var( $atts['show_visibility'],  FILTER_VALIDATE_BOOLEAN ),
            'show_feels_like'  => filter_var( $atts['show_feels_like'],  FILTER_VALIDATE_BOOLEAN ),
            'show_sunrise'     => filter_var( $atts['show_sunrise'],     FILTER_VALIDATE_BOOLEAN ),
            'show_hourly'      => filter_var( $atts['show_hourly'],      FILTER_VALIDATE_BOOLEAN ),
            'show_daily'       => filter_var( $atts['show_daily'],       FILTER_VALIDATE_BOOLEAN ),
            'forecast_days'    => min( 7, max( 1, (int) $atts['forecast_days'] ) ),
            'hourly_count'     => min( 24, max( 1, (int) $atts['hourly_count'] ) ),
            'color_primary'    => sanitize_hex_color( $atts['color_primary'] ) ?: '#2563eb',
            'color_bg'         => sanitize_hex_color( $atts['color_bg'] )      ?: '#ffffff',
            'color_text'       => sanitize_hex_color( $atts['color_text'] )    ?: '#1e293b',
            'border_radius'    => absint( $atts['border_radius'] ),
            'font_size'        => absint( $atts['font_size'] ),
            'custom_class'     => sanitize_html_class( $atts['custom_class'] ),
        );

        return $this->build_html( $config );
    }

    private function build_html( $config ) {
        $widget_id  = TemplateLoader::widget_id();
        $json_config = esc_attr( wp_json_encode( $config ) );

        $html  = '<div id="' . esc_attr( $widget_id ) . '" ';
        $html .= 'class="atmopress-widget atmopress-widget--' . esc_attr( $config['template'] ) . ' ' . esc_attr( $config['custom_class'] ) . '" ';
        $html .= 'data-config="' . $json_config . '" ';
        $html .= 'style="' . esc_attr( TemplateLoader::css_vars( $config ) ) . '">';
        $html .= '<div class="atmopress-loading"><span class="atmopress-spinner"></span><span>' . esc_html__( 'Loading weather…', 'atmopress-weather' ) . '</span></div>';
        $html .= '</div>';
        return $html;
    }
}
