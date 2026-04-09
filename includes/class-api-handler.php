<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pearl_Weather_API_Handler {

    private $api_key;
    private $settings;

    public function __construct() {
        $this->settings = get_option( 'pearl_weather_settings', array() );
        $this->api_key  = isset( $this->settings['api_key'] ) ? $this->settings['api_key'] : '';
    }

    public function get_weather_data( $location, $widget_id = 0 ) {
        if ( empty( $this->api_key ) ) {
            return false;
        }

        $url = add_query_arg(
            array(
                'q'     => $location,
                'appid' => $this->api_key,
                'units' => isset( $this->settings['units'] ) ? $this->settings['units'] : 'metric',
            ),
            'https://api.openweathermap.org/data/2.5/weather'
        );

        $response = wp_remote_get( $url, array( 'timeout' => 10 ) );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( isset( $data['cod'] ) && 200 === (int) $data['cod'] ) {
            return $data;
        }

        return false;
    }
}
