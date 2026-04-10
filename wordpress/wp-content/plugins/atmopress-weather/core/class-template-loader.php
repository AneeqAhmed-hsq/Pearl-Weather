<?php
namespace AtmoPress;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class TemplateLoader {

    public static function registered() {
        return apply_filters( 'atmopress_templates', array(
            'template-1' => array( 'label' => __( 'Classic Card',   'atmopress-weather' ), 'file' => 'template-1.php', 'preview' => 'card' ),
            'template-2' => array( 'label' => __( 'Modern Grid',    'atmopress-weather' ), 'file' => 'template-2.php', 'preview' => 'grid' ),
            'template-3' => array( 'label' => __( 'Minimal Strip',  'atmopress-weather' ), 'file' => 'template-3.php', 'preview' => 'minimal' ),
            'template-4' => array( 'label' => __( 'Dark Immersive', 'atmopress-weather' ), 'file' => 'template-4.php', 'preview' => 'dark' ),
        ) );
    }

    public static function render( $template, $weather, $config = array() ) {
        $config    = wp_parse_args( $config, self::default_config() );
        $templates = self::registered();

        if ( ! isset( $templates[ $template ] ) ) {
            $template = 'template-1';
        }

        $file = ATMOPRESS_TPL_DIR . $templates[ $template ]['file'];

        if ( ! file_exists( $file ) ) {
            return '<p class="atmopress-error">' . esc_html__( 'Template not found.', 'atmopress-weather' ) . '</p>';
        }

        ob_start();
        include $file;
        return ob_get_clean();
    }

    public static function default_config() {
        $s = Settings::get_all();
        return array(
            'template'          => $s['default_template'],
            'location'          => $s['default_location'],
            'units'             => $s['units'],
            'show_search'       => $s['show_search'],
            'show_geolocation'  => $s['show_geolocation'],
            'show_temperature'  => $s['show_temperature'],
            'show_humidity'     => $s['show_humidity'],
            'show_wind'         => $s['show_wind'],
            'show_pressure'     => $s['show_pressure'],
            'show_visibility'   => $s['show_visibility'],
            'show_feels_like'   => $s['show_feels_like'],
            'show_sunrise'      => $s['show_sunrise'],
            'show_hourly'       => $s['show_hourly'],
            'show_daily'        => $s['show_daily'],
            'forecast_days'     => $s['forecast_days'],
            'hourly_count'      => $s['hourly_count'],
            'color_primary'     => $s['color_primary'],
            'color_bg'          => $s['color_bg'],
            'color_text'        => $s['color_text'],
            'color_accent'      => $s['color_accent'],
            'border_radius'     => $s['border_radius'],
            'card_spacing'      => $s['card_spacing'],
            'font_size'         => $s['font_size'],
            'font_family'       => $s['font_family'],
            'icon_style'        => $s['icon_style'],
            'forecast_style'    => $s['forecast_style'],
            'custom_class'      => '',
        );
    }

    public static function widget_id( $prefix = 'atmopress' ) {
        static $counter = 0;
        ++$counter;
        return $prefix . '-' . $counter;
    }

    public static function unit_label( $units ) {
        return 'imperial' === $units ? '°F' : '°C';
    }

    public static function speed_label( $units ) {
        return 'imperial' === $units ? 'mph' : 'm/s';
    }

    public static function wind_direction( $degrees ) {
        $dirs = array( 'N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW' );
        return $dirs[ round( $degrees / 22.5 ) % 16 ];
    }

    public static function format_time( $timestamp, $offset = 0 ) {
        return gmdate( 'g:i A', $timestamp + $offset );
    }

    public static function css_vars( $config ) {
        return sprintf(
            '--ap-primary:%s;--ap-bg:%s;--ap-text:%s;--ap-accent:%s;--ap-radius:%spx;--ap-spacing:%spx;--ap-fsize:%spx;--ap-font:%s;',
            esc_attr( $config['color_primary']  ?? '#2563eb' ),
            esc_attr( $config['color_bg']       ?? '#ffffff' ),
            esc_attr( $config['color_text']     ?? '#1e293b' ),
            esc_attr( $config['color_accent']   ?? '#06b6d4' ),
            absint( $config['border_radius']    ?? 16 ),
            absint( $config['card_spacing']     ?? 16 ),
            absint( $config['font_size']        ?? 14 ),
            esc_attr( $config['font_family']    ?? 'inherit' )
        );
    }
}
