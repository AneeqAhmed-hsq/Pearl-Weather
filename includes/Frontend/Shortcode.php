<?php
/**
 * Shortcode Handler
 *
 * Handles the [pearl-weather] shortcode for rendering weather widgets.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Frontend
 * @since      1.0.0
 */

namespace PearlWeather\Frontend;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PearlWeather\API\WeatherAPI;
use PearlWeather\Helpers;

/**
 * Class ShortcodeHandler
 *
 * Renders weather widgets via shortcode.
 *
 * @since 1.0.0
 */
class ShortcodeHandler {

    /**
     * Weather API instance.
     *
     * @var WeatherAPI
     */
    private $api;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->api = new WeatherAPI();
        add_shortcode( 'pearl-weather', array( $this, 'render' ) );
    }

    /**
     * Render the shortcode.
     *
     * @since 1.0.0
     * @param array  $atts    Shortcode attributes.
     * @param string $content Shortcode content.
     * @return string
     */
    public function render( $atts, $content = '' ) {
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts, 'pearl-weather' );

        $widget_id = absint( $atts['id'] );

        if ( empty( $widget_id ) ) {
            return '<div class="pw-error">' . esc_html__( 'No weather widget ID provided.', 'pearl-weather' ) . '</div>';
        }

        // Verify post type and status.
        $post = get_post( $widget_id );
        if ( ! $post || 'pearl_weather_widget' !== $post->post_type || 'trash' === $post->post_status ) {
            return '<div class="pw-error">' . esc_html__( 'Weather widget not found.', 'pearl-weather' ) . '</div>';
        }

        // Get widget settings.
        $settings = get_post_meta( $widget_id, 'pearl_weather_settings', true );
        $layout = get_post_meta( $widget_id, 'pearl_weather_layout', true );

        if ( empty( $settings ) ) {
            return '<div class="pw-error">' . esc_html__( 'Weather widget settings not found.', 'pearl-weather' ) . '</div>';
        }

        // Register this shortcode on the current page for asset loading.
        AssetsManager::register_shortcode_on_page( $widget_id );

        // Start output buffering.
        ob_start();

        // Render the widget.
        $this->render_widget( $widget_id, $settings, $layout );

        return ob_get_clean();
    }

    /**
     * Render the weather widget.
     *
     * @since 1.0.0
     * @param int   $widget_id Widget ID.
     * @param array $settings  Widget settings.
     * @param array $layout    Layout settings.
     */
    private function render_widget( $widget_id, $settings, $layout ) {
        // Get API key.
        $api_source = isset( $settings['api_source'] ) ? $settings['api_source'] : 'openweather';
        $api_key = $this->get_api_key( $api_source );

        // Check for API key.
        if ( empty( $api_key ) ) {
            $this->render_api_key_error( $widget_id, $api_source );
            return;
        }

        // Get location query.
        $query = $this->build_location_query( $settings );

        if ( empty( $query ) ) {
            $this->render_error( $widget_id, __( 'Invalid location settings.', 'pearl-weather' ) );
            return;
        }

        // Fetch weather data.
        $units = isset( $settings['units'] ) ? $settings['units'] : 'metric';
        $lang = isset( $settings['language'] ) ? $settings['language'] : 'en';
        
        $weather_data = $this->fetch_weather_data( $api_source, $query, $units, $lang, $api_key, $widget_id );

        if ( isset( $weather_data['error'] ) ) {
            $this->render_error( $widget_id, $weather_data['error'] );
            return;
        }

        // Format current weather.
        $current = $this->format_current_weather( $weather_data, $settings );

        // Fetch forecast if enabled.
        $forecast = array();
        $show_forecast = isset( $settings['show_forecast'] ) ? (bool) $settings['show_forecast'] : true;
        
        if ( $show_forecast ) {
            $forecast = $this->fetch_forecast_data( $api_source, $query, $units, $lang, $api_key, $widget_id, $settings );
        }

        // Fetch AQI if enabled.
        $aqi = array();
        $show_aqi = isset( $settings['show_air_quality'] ) ? (bool) $settings['show_air_quality'] : false;
        
        if ( $show_aqi && 'openweather' === $api_source && is_array( $query ) && isset( $query['lat'] ) ) {
            $aqi = $this->api->get_air_quality( $query['lat'], $query['lon'], $api_key );
        }

        // Get template.
        $template = isset( $layout['template'] ) ? $layout['template'] : 'vertical';
        $template_file = $this->locate_template( $template );

        if ( ! $template_file ) {
            $this->render_error( $widget_id, sprintf( __( 'Template "%s" not found.', 'pearl-weather' ), $template ) );
            return;
        }

        // Extract variables for template.
        $unique_id = 'pw-widget-' . $widget_id;
        $attributes = $settings;
        
        // Include template.
        include $template_file;
    }

    /**
     * Get API key for the specified source.
     *
     * @since 1.0.0
     * @param string $source API source ('openweather' or 'weatherapi').
     * @return string
     */
    private function get_api_key( $source ) {
        $settings = get_option( 'pearl_weather_settings', array() );
        
        if ( 'weatherapi' === $source ) {
            return isset( $settings['weather_api_key'] ) ? $settings['weather_api_key'] : '';
        }
        
        return isset( $settings['api_key'] ) ? $settings['api_key'] : '';
    }

    /**
     * Build location query from settings.
     *
     * @since 1.0.0
     * @param array $settings Widget settings.
     * @return string|array
     */
    private function build_location_query( $settings ) {
        $search_by = isset( $settings['search_by'] ) ? $settings['search_by'] : 'city_name';
        
        switch ( $search_by ) {
            case 'city_name':
                $city = isset( $settings['city_name'] ) ? trim( $settings['city_name'] ) : '';
                return ! empty( $city ) ? $city : 'London, GB';
                
            case 'city_id':
                $city_id = isset( $settings['city_id'] ) ? trim( $settings['city_id'] ) : '';
                return ! empty( $city_id ) ? $city_id : '2643743';
                
            case 'coordinates':
                $coords = isset( $settings['coordinates'] ) ? trim( $settings['coordinates'] ) : '';
                if ( ! empty( $coords ) && strpos( $coords, ',' ) !== false ) {
                    $parts = explode( ',', str_replace( ' ', '', $coords ) );
                    if ( count( $parts ) === 2 && is_numeric( $parts[0] ) && is_numeric( $parts[1] ) ) {
                        return array(
                            'lat' => (float) $parts[0],
                            'lon' => (float) $parts[1],
                        );
                    }
                }
                return array( 'lat' => 51.509865, 'lon' => -0.118092 );
                
            case 'zip':
                $zip = isset( $settings['zip_code'] ) ? trim( $settings['zip_code'] ) : '';
                return ! empty( $zip ) ? $zip : '77070,US';
                
            default:
                return 'London, GB';
        }
    }

    /**
     * Fetch weather data from API.
     *
     * @since 1.0.0
     * @param string       $source    API source.
     * @param string|array $query     Location query.
     * @param string       $units     Units.
     * @param string       $lang      Language.
     * @param string       $api_key   API key.
     * @param int          $widget_id Widget ID.
     * @return array
     */
    private function fetch_weather_data( $source, $query, $units, $lang, $api_key, $widget_id ) {
        $skip_cache = is_preview();
        
        if ( 'weatherapi' === $source ) {
            $data = $this->api->get_weatherapi_data( $query, $units, 2, $skip_cache, $lang, $api_key );
            
            if ( isset( $data['code'] ) && $data['code'] !== 200 ) {
                return array( 'error' => $data['message'] ?? __( 'Weather data unavailable.', 'pearl-weather' ) );
            }
            
            return $data;
        }
        
        // OpenWeatherMap.
        $data = $this->api->get_current_weather( $query, $units, $skip_cache, $lang, $api_key );
        
        if ( isset( $data['code'] ) && $data['code'] !== 200 ) {
            $message = $data['message'] ?? __( 'Weather data unavailable.', 'pearl-weather' );
            
            if ( 401 === $data['code'] ) {
                $message = __( 'Invalid API key. Please check your OpenWeatherMap API key.', 'pearl-weather' );
            } elseif ( 404 === $data['code'] ) {
                $message = __( 'Location not found. Please check your location settings.', 'pearl-weather' );
            }
            
            return array( 'error' => $message );
        }
        
        return $data;
    }

    /**
     * Fetch forecast data.
     *
     * @since 1.0.0
     * @param string       $source    API source.
     * @param string|array $query     Location query.
     * @param string       $units     Units.
     * @param string       $lang      Language.
     * @param string       $api_key   API key.
     * @param int          $widget_id Widget ID.
     * @param array        $settings  Widget settings.
     * @return array
     */
    private function fetch_forecast_data( $source, $query, $units, $lang, $api_key, $widget_id, $settings ) {
        $skip_cache = is_preview();
        $hours = isset( $settings['forecast_hours'] ) ? (int) $settings['forecast_hours'] : 8;
        
        if ( 'weatherapi' === $source ) {
            $data = $this->api->get_weatherapi_data( $query, $units, 2, $skip_cache, $lang, $api_key );
            return isset( $data['forecast'] ) ? $data['forecast'] : array();
        }
        
        $data = $this->api->get_hourly_forecast( $query, $units, $hours, $skip_cache, $lang, $api_key );
        return $this->format_forecast_data( $data, $units );
    }

    /**
     * Format current weather data for display.
     *
     * @since 1.0.0
     * @param array $data     Weather data.
     * @param array $settings Widget settings.
     * @return array
     */
    private function format_current_weather( $data, $settings ) {
        $units = isset( $settings['units'] ) ? $settings['units'] : 'metric';
        $temp_unit = 'metric' === $units ? '°C' : '°F';
        $wind_unit = 'metric' === $units ? 'm/s' : 'mph';
        
        $formatted = array(
            'city'          => $data['name'] ?? '',
            'country'       => $data['sys']['country'] ?? '',
            'temperature'   => isset( $data['main']['temp'] ) ? round( $data['main']['temp'] ) : '--',
            'temp_unit'     => $temp_unit,
            'feels_like'    => isset( $data['main']['feels_like'] ) ? round( $data['main']['feels_like'] ) : '',
            'humidity'      => isset( $data['main']['humidity'] ) ? $data['main']['humidity'] . '%' : '',
            'pressure'      => isset( $data['main']['pressure'] ) ? $data['main']['pressure'] . ' hPa' : '',
            'wind'          => isset( $data['wind']['speed'] ) ? $data['wind']['speed'] . ' ' . $wind_unit : '',
            'wind_direction'=> $this->get_wind_direction( $data['wind']['deg'] ?? 0 ),
            'visibility'    => isset( $data['visibility'] ) ? $this->format_visibility( $data['visibility'] ) : '',
            'clouds'        => isset( $data['clouds']['all'] ) ? $data['clouds']['all'] . '%' : '',
            'description'   => $data['weather'][0]['description'] ?? '',
            'icon'          => $data['weather'][0]['icon'] ?? '',
            'sunrise'       => isset( $data['sys']['sunrise'] ) ? date_i18n( 'g:i A', $data['sys']['sunrise'] ) : '',
            'sunset'        => isset( $data['sys']['sunset'] ) ? date_i18n( 'g:i A', $data['sys']['sunset'] ) : '',
            'date'          => date_i18n( 'F j, Y' ),
            'time'          => date_i18n( 'g:i A' ),
        );
        
        return $formatted;
    }

    /**
     * Format forecast data.
     *
     * @since 1.0.0
     * @param array  $data  Forecast data.
     * @param string $units Units.
     * @return array
     */
    private function format_forecast_data( $data, $units ) {
        $forecast = array();
        
        if ( ! isset( $data['list'] ) ) {
            return $forecast;
        }
        
        $temp_unit = 'metric' === $units ? '°C' : '°F';
        
        foreach ( $data['list'] as $item ) {
            $forecast[] = array(
                'time'      => date_i18n( 'g:i A', $item['dt'] ),
                'temp'      => isset( $item['main']['temp'] ) ? round( $item['main']['temp'] ) . $temp_unit : '--',
                'icon'      => $item['weather'][0]['icon'] ?? '',
                'condition' => $item['weather'][0]['description'] ?? '',
                'humidity'  => $item['main']['humidity'] ?? '',
                'wind'      => isset( $item['wind']['speed'] ) ? $item['wind']['speed'] . ' m/s' : '',
            );
        }
        
        return $forecast;
    }

    /**
     * Get wind direction from degrees.
     *
     * @since 1.0.0
     * @param int $degrees Wind direction in degrees.
     * @return string
     */
    private function get_wind_direction( $degrees ) {
        $directions = array( 'N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW' );
        $index = round( $degrees / 22.5 ) % 16;
        return $directions[ $index ];
    }

    /**
     * Format visibility from meters.
     *
     * @since 1.0.0
     * @param int $meters Visibility in meters.
     * @return string
     */
    private function format_visibility( $meters ) {
        if ( $meters >= 1000 ) {
            return round( $meters / 1000, 1 ) . ' km';
        }
        return $meters . ' m';
    }

    /**
     * Locate template file.
     *
     * @since 1.0.0
     * @param string $template Template name.
     * @return string|false
     */
    private function locate_template( $template ) {
        $template_file = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/' . $template . '/render.php';
        
        if ( file_exists( $template_file ) ) {
            return $template_file;
        }
        
        // Fallback to vertical template.
        $fallback = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/vertical/render.php';
        return file_exists( $fallback ) ? $fallback : false;
    }

    /**
     * Render API key error.
     *
     * @since 1.0.0
     * @param int    $widget_id Widget ID.
     * @param string $source    API source.
     */
    private function render_api_key_error( $widget_id, $source ) {
        $settings_url = admin_url( 'admin.php?page=pearl-weather-settings' );
        $source_name = 'openweather' === $source ? 'OpenWeatherMap' : 'WeatherAPI';
        ?>
        <div id="pw-widget-<?php echo esc_attr( $widget_id ); ?>" class="pw-weather-widget pw-error-widget">
            <div class="pw-widget-title"><?php echo esc_html( get_the_title( $widget_id ) ); ?></div>
            <div class="pw-error-message">
                <?php printf(
                    /* translators: %1$s: API source name, %2$s: settings page link */
                    esc_html__( 'Please set your %1$s API key in the %2$s.', 'pearl-weather' ),
                    esc_html( $source_name ),
                    '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'plugin settings', 'pearl-weather' ) . '</a>'
                ); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render generic error.
     *
     * @since 1.0.0
     * @param int    $widget_id Widget ID.
     * @param string $message   Error message.
     */
    private function render_error( $widget_id, $message ) {
        ?>
        <div id="pw-widget-<?php echo esc_attr( $widget_id ); ?>" class="pw-weather-widget pw-error-widget">
            <div class="pw-widget-title"><?php echo esc_html( get_the_title( $widget_id ) ); ?></div>
            <div class="pw-error-message"><?php echo wp_kses_post( $message ); ?></div>
        </div>
        <?php
    }
}

// Initialize shortcode handler.
new ShortcodeHandler();