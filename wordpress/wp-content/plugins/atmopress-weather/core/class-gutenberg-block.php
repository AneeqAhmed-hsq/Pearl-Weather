<?php
namespace AtmoPress;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class GutenbergBlock {

    public function __construct() {
        add_action( 'init', array( $this, 'register_block' ) );
    }

    public function register_block() {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        wp_register_script(
            'atmopress-block-editor',
            ATMOPRESS_URL . 'block/editor.js',
            array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
            ATMOPRESS_VERSION,
            true
        );

        wp_localize_script( 'atmopress-block-editor', 'AtmoPressBlock', array(
            'templates'  => array_map( function( $t ) { return $t['label']; }, TemplateLoader::registered() ),
            'defaults'   => TemplateLoader::default_config(),
            'restUrl'    => esc_url_raw( rest_url( 'atmopress/v1/' ) ),
            'nonce'      => wp_create_nonce( 'wp_rest' ),
        ) );

        wp_register_style(
            'atmopress-block-editor-style',
            ATMOPRESS_ASSETS_URL . 'css/frontend.css',
            array(),
            ATMOPRESS_VERSION
        );

        register_block_type( 'atmopress/weather-widget', array(
            'editor_script'   => 'atmopress-block-editor',
            'editor_style'    => 'atmopress-block-editor-style',
            'style'           => 'atmopress-frontend',
            'render_callback' => array( $this, 'server_render' ),
            'attributes'      => $this->get_attributes(),
        ) );
    }

    private function get_attributes() {
        $def = TemplateLoader::default_config();
        return array(
            'location'         => array( 'type' => 'string',  'default' => $def['location'] ),
            'template'         => array( 'type' => 'string',  'default' => $def['template'] ),
            'units'            => array( 'type' => 'string',  'default' => $def['units'] ),
            'show_search'      => array( 'type' => 'boolean', 'default' => $def['show_search'] ),
            'show_geolocation' => array( 'type' => 'boolean', 'default' => $def['show_geolocation'] ),
            'show_humidity'    => array( 'type' => 'boolean', 'default' => $def['show_humidity'] ),
            'show_wind'        => array( 'type' => 'boolean', 'default' => $def['show_wind'] ),
            'show_pressure'    => array( 'type' => 'boolean', 'default' => $def['show_pressure'] ),
            'show_visibility'  => array( 'type' => 'boolean', 'default' => $def['show_visibility'] ),
            'show_feels_like'  => array( 'type' => 'boolean', 'default' => $def['show_feels_like'] ),
            'show_sunrise'     => array( 'type' => 'boolean', 'default' => $def['show_sunrise'] ),
            'show_hourly'      => array( 'type' => 'boolean', 'default' => $def['show_hourly'] ),
            'show_daily'       => array( 'type' => 'boolean', 'default' => $def['show_daily'] ),
            'forecast_days'    => array( 'type' => 'integer', 'default' => $def['forecast_days'] ),
            'hourly_count'     => array( 'type' => 'integer', 'default' => $def['hourly_count'] ),
            'color_primary'    => array( 'type' => 'string',  'default' => $def['color_primary'] ),
            'color_bg'         => array( 'type' => 'string',  'default' => $def['color_bg'] ),
            'color_text'       => array( 'type' => 'string',  'default' => $def['color_text'] ),
            'border_radius'    => array( 'type' => 'integer', 'default' => $def['border_radius'] ),
            'font_size'        => array( 'type' => 'integer', 'default' => $def['font_size'] ),
            'custom_class'     => array( 'type' => 'string',  'default' => '' ),
        );
    }

    /**
     * Server-side render for the block.
     * In the editor, JS loads a live preview via REST API.
     * On the frontend, the shortcode-style div is rendered so frontend.js can hydrate it.
     */
    public function server_render( $attrs ) {
        $config = wp_parse_args( $attrs, TemplateLoader::default_config() );

        $widget_id  = TemplateLoader::widget_id( 'ap-block' );
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
