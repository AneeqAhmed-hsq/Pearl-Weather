<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pearl_Weather_Shortcode {

    public function __construct() {
        add_shortcode( 'pearl-weather', array( $this, 'render_shortcode' ) );
        add_shortcode( 'pearl_weather', array( $this, 'render_shortcode' ) );
    }

    public function render_shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'id'       => 0,
                'location' => '',
                'units'    => '',
            ),
            $atts,
            'pearl-weather'
        );

        ob_start();
        $widget_id = absint( $atts['id'] );
        if ( $widget_id > 0 ) {
            $post = get_post( $widget_id );
            if ( $post && 'sp_lw_shortcodes' === $post->post_type ) {
                echo '<div class="pearl-weather-widget" data-id="' . esc_attr( $widget_id ) . '">';
                echo '<p class="pearl-weather-loading">' . esc_html__( 'Loading weather...', 'pearl-weather' ) . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p>' . esc_html__( 'Please specify a valid Pearl Weather widget ID.', 'pearl-weather' ) . '</p>';
        }
        return ob_get_clean();
    }
}

new Pearl_Weather_Shortcode();
