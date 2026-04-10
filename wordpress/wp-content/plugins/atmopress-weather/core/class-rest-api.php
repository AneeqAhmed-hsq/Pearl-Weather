<?php
namespace AtmoPress;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class RestApi {

    const NAMESPACE = 'atmopress/v1';

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        register_rest_route( self::NAMESPACE, '/weather', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_weather' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'location' => array(
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'units'    => array(
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function( $v ) { return in_array( $v, array( 'metric', 'imperial', '' ), true ); },
                ),
                'template' => array(
                    'default'           => 'card',
                    'sanitize_callback' => 'sanitize_key',
                ),
            ),
        ) );

        register_rest_route( self::NAMESPACE, '/render', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'render_widget' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'location' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
                'template' => array( 'default' => 'card', 'sanitize_callback' => 'sanitize_key' ),
                'units'    => array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
                'config'   => array( 'default' => '{}' ),
            ),
        ) );

        register_rest_route( self::NAMESPACE, '/test-api', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'test_api' ),
            'permission_callback' => function() { return current_user_can( 'manage_options' ); },
            'args'                => array(
                'api_key'  => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
                'provider' => array( 'default' => 'openweathermap', 'sanitize_callback' => 'sanitize_key' ),
            ),
        ) );

        register_rest_route( self::NAMESPACE, '/settings', array(
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_settings' ),
                'permission_callback' => function() { return current_user_can( 'manage_options' ); },
            ),
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'save_settings' ),
                'permission_callback' => function() { return current_user_can( 'manage_options' ); },
            ),
        ) );

        register_rest_route( self::NAMESPACE, '/flush-cache', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'flush_cache' ),
            'permission_callback' => function() { return current_user_can( 'manage_options' ); },
        ) );
    }

    public function get_weather( $request ) {
        $location = $request->get_param( 'location' );
        $units    = $request->get_param( 'units' ) ?: Settings::get( 'units', 'metric' );

        $client = new ApiClient();
        if ( ! $client->has_api_key() ) {
            return new \WP_REST_Response( array( 'error' => __( 'API key not configured.', 'atmopress-weather' ) ), 400 );
        }

        $data = $client->get_weather( $location, $units );
        if ( is_wp_error( $data ) ) {
            return new \WP_REST_Response( array( 'error' => $data->get_error_message() ), 502 );
        }

        return rest_ensure_response( $data );
    }

    public function render_widget( $request ) {
        $location = $request->get_param( 'location' );
        $template = $request->get_param( 'template' );
        $units    = $request->get_param( 'units' ) ?: Settings::get( 'units', 'metric' );
        $config   = json_decode( $request->get_param( 'config' ), true ) ?: array();

        $client = new ApiClient();
        if ( ! $client->has_api_key() ) {
            return new \WP_REST_Response( array( 'html' => '<div class="atmopress-no-key">' . esc_html__( 'API key not configured.', 'atmopress-weather' ) . '</div>' ), 200 );
        }

        $data = $client->get_weather( $location, $units );
        if ( is_wp_error( $data ) ) {
            return new \WP_REST_Response( array( 'html' => '<div class="atmopress-error">' . esc_html( $data->get_error_message() ) . '</div>' ), 200 );
        }

        $config['units']    = $units;
        $config['template'] = $template;
        $html = TemplateLoader::render( $template, $data, $config );
        return rest_ensure_response( array( 'html' => $html, 'city' => $data['current']['city'] ?? '' ) );
    }

    public function test_api( $request ) {
        $saved_key      = Settings::get( 'api_key' );
        $saved_provider = Settings::get( 'api_provider' );

        update_option( ATMOPRESS_OPT, array_merge( Settings::get_all(), array(
            'api_key'      => $request->get_param( 'api_key' ),
            'api_provider' => $request->get_param( 'provider' ),
        ) ) );
        Settings::get_all(); // reset cache not needed, let constructor re-read

        $client = new ApiClient();
        $data   = $client->get_weather( 'London', 'metric' );

        update_option( ATMOPRESS_OPT, array_merge( Settings::get_all(), array(
            'api_key'      => $saved_key,
            'api_provider' => $saved_provider,
        ) ) );

        if ( is_wp_error( $data ) ) {
            return new \WP_REST_Response( array( 'ok' => false, 'message' => $data->get_error_message() ), 200 );
        }
        return rest_ensure_response( array( 'ok' => true, 'city' => $data['current']['city'] ?? 'London' ) );
    }

    public function get_settings( $request ) {
        return rest_ensure_response( Settings::get_all() );
    }

    public function save_settings( $request ) {
        $body = $request->get_json_params();
        Settings::save( $body );
        return rest_ensure_response( array( 'saved' => true ) );
    }

    public function flush_cache( $request ) {
        DataCache::flush_all();
        return rest_ensure_response( array( 'flushed' => true ) );
    }
}
