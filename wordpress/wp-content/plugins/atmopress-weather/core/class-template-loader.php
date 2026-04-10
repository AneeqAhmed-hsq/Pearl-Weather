<?php
namespace AtmoPress;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class TemplateLoader {

    /**
     * Available templates registered in the system.
     * Add more entries here to add new templates.
     */
    public static function registered() {
        return apply_filters( 'atmopress_templates', array(
            'card'       => array( 'label' => __( 'Card',       'atmopress-weather' ), 'file' => 'card.php' ),
            'minimal'    => array( 'label' => __( 'Minimal',    'atmopress-weather' ), 'file' => 'minimal.php' ),
            'grid'       => array( 'label' => __( 'Grid',       'atmopress-weather' ), 'file' => 'grid.php' ),
            'horizontal' => array( 'label' => __( 'Horizontal', 'atmopress-weather' ), 'file' => 'horizontal.php' ),
            'forecast'   => array( 'label' => __( 'Forecast',   'atmopress-weather' ), 'file' => 'forecast.php' ),
        ) );
    }

    /**
     * Render a weather template with given data and config.
     *
     * @param  string $template  Template slug.
     * @param  array  $weather   Normalized weather data.
     * @param  array  $config    Widget config from block/shortcode attrs.
     * @return string  HTML output.
     */
    public static function render( $template, $weather, $config = array() ) {
        $config = wp_parse_args( $config, self::default_config() );

        $templates = self::registered();
        if ( ! isset( $templates[ $template ] ) ) {
            $template = 'card';
        }

        $file = ATMOPRESS_TPL_DIR . $templates[ $template ]['file'];

        if ( ! file_exists( $file ) ) {
            return '<p class="atmopress-error">' . esc_html__( 'Template not found.', 'atmopress-weather' ) . '</p>';
        }

        ob_start();
        include $file;
        return ob_get_clean();
    }

    /**
     * Default widget configuration options.
     */
    public static function default_config() {
        return array(
            'template'          => 'card',
            'location'          => Settings::get( 'default_location', 'London' ),
            'units'             => Settings::get( 'units', 'metric' ),
            'show_search'       => true,
            'show_geolocation'  => true,
            'show_humidity'     => true,
            'show_wind'         => true,
            'show_pressure'     => true,
            'show_visibility'   => true,
            'show_feels_like'   => true,
            'show_sunrise'      => true,
            'show_hourly'       => true,
            'show_daily'        => true,
            'forecast_days'     => 7,
            'hourly_count'      => 8,
            'color_primary'     => '#2563eb',
            'color_bg'          => '#ffffff',
            'color_text'        => '#1e293b',
            'border_radius'     => 16,
            'font_size'         => 14,
            'custom_class'      => '',
        );
    }

    /**
     * Generate a unique widget ID for DOM targeting.
     */
    public static function widget_id( $prefix = 'atmopress' ) {
        static $counter = 0;
        ++$counter;
        return $prefix . '-' . $counter;
    }

    /* ------------------------------------------------------------------
     * Template helpers (used inside template files)
     * ------------------------------------------------------------------ */

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
        $radius = absint( $config['border_radius'] );
        $fsize  = absint( $config['font_size'] );
        return sprintf(
            '--ap-primary:%s;--ap-bg:%s;--ap-text:%s;--ap-radius:%spx;--ap-fsize:%spx;',
            esc_attr( $config['color_primary'] ),
            esc_attr( $config['color_bg'] ),
            esc_attr( $config['color_text'] ),
            $radius,
            $fsize
        );
    }
}
