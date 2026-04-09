<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pearl_Weather_Location_Detector {

    public function detect_location_by_ip( $ip = '' ) {
        if ( empty( $ip ) ) {
            $ip = $this->get_user_ip();
        }

        if ( empty( $ip ) || '127.0.0.1' === $ip ) {
            return false;
        }

        $url      = 'https://ipapi.co/' . $ip . '/json/';
        $response = wp_remote_get( $url, array( 'timeout' => 5 ) );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( ! empty( $data['city'] ) ) {
            return array(
                'city'    => sanitize_text_field( $data['city'] ),
                'country' => sanitize_text_field( isset( $data['country_name'] ) ? $data['country_name'] : '' ),
                'lat'     => isset( $data['latitude'] ) ? floatval( $data['latitude'] ) : 0,
                'lon'     => isset( $data['longitude'] ) ? floatval( $data['longitude'] ) : 0,
            );
        }

        return false;
    }

    private function get_user_ip() {
        $keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        );

        foreach ( $keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    return $ip;
                }
            }
        }

        return '';
    }
}
