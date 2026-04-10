<?php
namespace AtmoPress;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Unified API client supporting OpenWeatherMap and WeatherAPI.com.
 */
class ApiClient {

    private $api_key;
    private $provider;
    private $units;

    public function __construct() {
        $this->api_key  = Settings::get( 'api_key', '' );
        $this->provider = Settings::get( 'api_provider', 'openweathermap' );
        $this->units    = Settings::get( 'units', 'metric' );
    }

    public function has_api_key() {
        return ! empty( $this->api_key );
    }

    /**
     * Fetch current weather + 5-day forecast for a location string.
     *
     * @param  string $location City name or "lat,lon".
     * @param  string $units    'metric' | 'imperial' (overrides setting).
     * @return array|WP_Error
     */
    public function get_weather( $location, $units = null ) {
        if ( ! $this->has_api_key() ) {
            return new \WP_Error( 'no_key', __( 'No API key configured.', 'atmopress-weather' ) );
        }

        $units    = $units ?: $this->units;
        $cache_id = 'weather_' . $this->provider . '_' . $location . '_' . $units;
        $cached   = DataCache::get( $cache_id );

        if ( false !== $cached ) {
            return $cached;
        }

        if ( 'weatherapi' === $this->provider ) {
            $data = $this->fetch_weatherapi( $location, $units );
        } else {
            $data = $this->fetch_openweathermap( $location, $units );
        }

        if ( is_wp_error( $data ) ) {
            return $data;
        }

        DataCache::set( $cache_id, $data );
        return $data;
    }

    /* -----------------------------------------------------------------------
     * OpenWeatherMap
     * ---------------------------------------------------------------------- */

    private function fetch_openweathermap( $location, $units ) {
        $query = $this->build_owm_query( $location );

        $current_url  = 'https://api.openweathermap.org/data/2.5/weather?' . http_build_query( array_merge( $query, array( 'units' => $units ) ) );
        $forecast_url = 'https://api.openweathermap.org/data/2.5/forecast?' . http_build_query( array_merge( $query, array( 'units' => $units, 'cnt' => 40 ) ) );

        $current  = $this->http_get( $current_url );
        $forecast = $this->http_get( $forecast_url );

        if ( is_wp_error( $current ) )  { return $current; }
        if ( is_wp_error( $forecast ) ) { return $forecast; }

        return $this->normalize_owm( $current, $forecast, $units );
    }

    private function build_owm_query( $location ) {
        $base = array( 'appid' => $this->api_key );
        if ( preg_match( '/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/', $location ) ) {
            list( $lat, $lon ) = explode( ',', $location );
            return array_merge( $base, array( 'lat' => (float) $lat, 'lon' => (float) $lon ) );
        }
        return array_merge( $base, array( 'q' => $location ) );
    }

    private function normalize_owm( $cur, $fcast, $units ) {
        $icon_base = 'https://openweathermap.org/img/wn/';

        $current = array(
            'city'        => $cur['name'] ?? '',
            'country'     => $cur['sys']['country'] ?? '',
            'lat'         => $cur['coord']['lat'] ?? 0,
            'lon'         => $cur['coord']['lon'] ?? 0,
            'temp'        => round( $cur['main']['temp'] ?? 0 ),
            'feels_like'  => round( $cur['main']['feels_like'] ?? 0 ),
            'temp_min'    => round( $cur['main']['temp_min'] ?? 0 ),
            'temp_max'    => round( $cur['main']['temp_max'] ?? 0 ),
            'humidity'    => $cur['main']['humidity'] ?? 0,
            'pressure'    => $cur['main']['pressure'] ?? 0,
            'visibility'  => isset( $cur['visibility'] ) ? round( $cur['visibility'] / 1000, 1 ) : null,
            'wind_speed'  => round( $cur['wind']['speed'] ?? 0, 1 ),
            'wind_deg'    => $cur['wind']['deg'] ?? 0,
            'wind_gust'   => isset( $cur['wind']['gust'] ) ? round( $cur['wind']['gust'], 1 ) : null,
            'clouds'      => $cur['clouds']['all'] ?? 0,
            'condition'   => $cur['weather'][0]['main'] ?? '',
            'description' => ucfirst( $cur['weather'][0]['description'] ?? '' ),
            'icon'        => $icon_base . ( $cur['weather'][0]['icon'] ?? '01d' ) . '@2x.png',
            'icon_code'   => $cur['weather'][0]['icon'] ?? '01d',
            'sunrise'     => $cur['sys']['sunrise'] ?? 0,
            'sunset'      => $cur['sys']['sunset'] ?? 0,
            'timezone'    => $cur['timezone'] ?? 0,
            'timestamp'   => $cur['dt'] ?? time(),
            'units'       => $units,
        );

        $hourly  = array();
        $daily   = array();
        $day_map = array();

        foreach ( ( $fcast['list'] ?? array() ) as $item ) {
            $dt  = $item['dt'];
            $day = gmdate( 'Y-m-d', $dt );

            $hourly[] = array(
                'timestamp'   => $dt,
                'time'        => gmdate( 'H:i', $dt + ( $cur['timezone'] ?? 0 ) ),
                'temp'        => round( $item['main']['temp'] ),
                'feels_like'  => round( $item['main']['feels_like'] ),
                'humidity'    => $item['main']['humidity'],
                'wind_speed'  => round( $item['wind']['speed'], 1 ),
                'condition'   => $item['weather'][0]['main'] ?? '',
                'description' => ucfirst( $item['weather'][0]['description'] ?? '' ),
                'icon'        => $icon_base . ( $item['weather'][0]['icon'] ?? '01d' ) . '.png',
                'icon_code'   => $item['weather'][0]['icon'] ?? '01d',
                'pop'         => round( ( $item['pop'] ?? 0 ) * 100 ),
            );

            if ( ! isset( $day_map[ $day ] ) ) {
                $day_map[ $day ] = array(
                    'date'        => $day,
                    'day_label'   => gmdate( 'D', $dt ),
                    'timestamp'   => $dt,
                    'temps'       => array(),
                    'condition'   => $item['weather'][0]['main'] ?? '',
                    'description' => ucfirst( $item['weather'][0]['description'] ?? '' ),
                    'icon'        => $icon_base . ( $item['weather'][0]['icon'] ?? '01d' ) . '@2x.png',
                    'icon_code'   => $item['weather'][0]['icon'] ?? '01d',
                    'humidity'    => $item['main']['humidity'],
                    'wind_speed'  => round( $item['wind']['speed'], 1 ),
                    'pop'         => round( ( $item['pop'] ?? 0 ) * 100 ),
                );
            }
            $day_map[ $day ]['temps'][] = $item['main']['temp'];
        }

        foreach ( $day_map as $day => $d ) {
            $daily[] = array_merge( $d, array(
                'temp_min' => round( min( $d['temps'] ) ),
                'temp_max' => round( max( $d['temps'] ) ),
            ) );
        }

        return array(
            'current'  => $current,
            'hourly'   => array_slice( $hourly, 0, 24 ),
            'daily'    => array_values( $daily ),
            'provider' => 'openweathermap',
        );
    }

    /* -----------------------------------------------------------------------
     * WeatherAPI.com
     * ---------------------------------------------------------------------- */

    private function fetch_weatherapi( $location, $units ) {
        $url = 'https://api.weatherapi.com/v1/forecast.json?' . http_build_query( array(
            'key'   => $this->api_key,
            'q'     => $location,
            'days'  => 7,
            'aqi'   => 'no',
            'alerts'=> 'no',
        ) );

        $raw = $this->http_get( $url );
        if ( is_wp_error( $raw ) ) {
            return $raw;
        }

        return $this->normalize_weatherapi( $raw, $units );
    }

    private function normalize_weatherapi( $raw, $units ) {
        $is_imperial = ( 'imperial' === $units );
        $c  = $raw['current'] ?? array();
        $loc = $raw['location'] ?? array();

        $current = array(
            'city'        => $loc['name'] ?? '',
            'country'     => $loc['country'] ?? '',
            'lat'         => $loc['lat'] ?? 0,
            'lon'         => $loc['lon'] ?? 0,
            'temp'        => round( $is_imperial ? $c['temp_f'] : $c['temp_c'] ),
            'feels_like'  => round( $is_imperial ? $c['feelslike_f'] : $c['feelslike_c'] ),
            'temp_min'    => 0,
            'temp_max'    => 0,
            'humidity'    => $c['humidity'] ?? 0,
            'pressure'    => $c['pressure_mb'] ?? 0,
            'visibility'  => $is_imperial ? ( $c['vis_miles'] ?? 0 ) : ( $c['vis_km'] ?? 0 ),
            'wind_speed'  => round( $is_imperial ? ( $c['wind_mph'] ?? 0 ) : ( $c['wind_kph'] ?? 0 ), 1 ),
            'wind_deg'    => $c['wind_degree'] ?? 0,
            'wind_gust'   => $is_imperial ? ( $c['gust_mph'] ?? 0 ) : ( $c['gust_kph'] ?? 0 ),
            'clouds'      => $c['cloud'] ?? 0,
            'condition'   => $c['condition']['text'] ?? '',
            'description' => $c['condition']['text'] ?? '',
            'icon'        => 'https:' . ( $c['condition']['icon'] ?? '' ),
            'icon_code'   => (string) ( $c['condition']['code'] ?? '1000' ),
            'sunrise'     => 0,
            'sunset'      => 0,
            'timezone'    => 0,
            'timestamp'   => strtotime( $loc['localtime'] ?? 'now' ),
            'units'       => $units,
        );

        $hourly = array();
        $daily  = array();

        foreach ( ( $raw['forecast']['forecastday'] ?? array() ) as $fday ) {
            $d = $fday['day'];
            $daily[] = array(
                'date'        => $fday['date'],
                'day_label'   => gmdate( 'D', strtotime( $fday['date'] ) ),
                'timestamp'   => strtotime( $fday['date'] ),
                'temp_min'    => round( $is_imperial ? $d['mintemp_f'] : $d['mintemp_c'] ),
                'temp_max'    => round( $is_imperial ? $d['maxtemp_f'] : $d['maxtemp_c'] ),
                'condition'   => $d['condition']['text'] ?? '',
                'description' => $d['condition']['text'] ?? '',
                'icon'        => 'https:' . ( $d['condition']['icon'] ?? '' ),
                'icon_code'   => (string) ( $d['condition']['code'] ?? '1000' ),
                'humidity'    => $d['avghumidity'] ?? 0,
                'wind_speed'  => round( $is_imperial ? $d['maxwind_mph'] : $d['maxwind_kph'], 1 ),
                'pop'         => round( ( $d['daily_chance_of_rain'] ?? 0 ) ),
                'sunrise'     => $fday['astro']['sunrise'] ?? '',
                'sunset'      => $fday['astro']['sunset'] ?? '',
            );

            foreach ( ( $fday['hour'] ?? array() ) as $h ) {
                $hourly[] = array(
                    'timestamp'   => $h['time_epoch'],
                    'time'        => substr( $h['time'], -5 ),
                    'temp'        => round( $is_imperial ? $h['temp_f'] : $h['temp_c'] ),
                    'feels_like'  => round( $is_imperial ? $h['feelslike_f'] : $h['feelslike_c'] ),
                    'humidity'    => $h['humidity'],
                    'wind_speed'  => round( $is_imperial ? $h['wind_mph'] : $h['wind_kph'], 1 ),
                    'condition'   => $h['condition']['text'] ?? '',
                    'description' => $h['condition']['text'] ?? '',
                    'icon'        => 'https:' . ( $h['condition']['icon'] ?? '' ),
                    'icon_code'   => (string) ( $h['condition']['code'] ?? '1000' ),
                    'pop'         => $h['chance_of_rain'] ?? 0,
                );
            }
        }

        if ( ! empty( $daily ) ) {
            $current['temp_min'] = $daily[0]['temp_min'];
            $current['temp_max'] = $daily[0]['temp_max'];
            $current['sunrise']  = isset( $raw['forecast']['forecastday'][0]['astro']['sunrise'] ) ? strtotime( $raw['forecast']['forecastday'][0]['astro']['sunrise'] ) : 0;
            $current['sunset']   = isset( $raw['forecast']['forecastday'][0]['astro']['sunset'] )  ? strtotime( $raw['forecast']['forecastday'][0]['astro']['sunset'] )  : 0;
        }

        return array(
            'current'  => $current,
            'hourly'   => array_slice( $hourly, 0, 24 ),
            'daily'    => $daily,
            'provider' => 'weatherapi',
        );
    }

    /* -----------------------------------------------------------------------
     * HTTP helper
     * ---------------------------------------------------------------------- */

    private function http_get( $url ) {
        $response = wp_remote_get( $url, array( 'timeout' => 10, 'sslverify' => true ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            $msg = $body['message'] ?? $body['error']['message'] ?? sprintf( __( 'API error %d', 'atmopress-weather' ), $code );
            return new \WP_Error( 'api_error', $msg );
        }

        return $body;
    }
}
