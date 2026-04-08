<?php
/**
 * Legacy Weather Widget (Deprecated)
 *
 * This widget is maintained for backward compatibility with older versions.
 * Users are encouraged to use the new Pearl Weather widget instead.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Admin
 * @since      1.0.0
 * @deprecated 1.0.0 Use WeatherWidget instead.
 */

namespace PearlWeather\Admin;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class LegacyWeatherWidget
 *
 * @deprecated 1.0.0 Use WeatherWidget instead.
 */
class LegacyWeatherWidget extends \WP_Widget {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            'pearl_weather_legacy',
            __( 'Legacy - Pearl Weather (Deprecated)', 'pearl-weather' ),
            array(
                'description' => __( 'This widget is deprecated. Please use the new Pearl Weather widget instead.', 'pearl-weather' ),
                'classname'   => 'widget_pearl_weather_legacy',
            )
        );

        // Display deprecation notice.
        add_action( 'admin_notices', array( $this, 'display_deprecation_notice' ) );
    }

    /**
     * Display deprecation notice in admin.
     *
     * @since 1.0.0
     */
    public function display_deprecation_notice() {
        $screen = get_current_screen();
        
        if ( ! $screen || 'widgets' !== $screen->base ) {
            return;
        }

        ?>
        <div class="notice notice-warning">
            <p>
                <?php
                printf(
                    /* translators: %s: link to new widget */
                    esc_html__( 'The Legacy Pearl Weather widget is deprecated. Please use the new %s widget instead.', 'pearl-weather' ),
                    '<a href="' . esc_url( admin_url( 'widgets.php' ) ) . '">' . esc_html__( 'Pearl Weather', 'pearl-weather' ) . '</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Front-end display of widget.
     *
     * @since 1.0.0
     * @deprecated 1.0.0
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        // Display deprecation notice to admins (frontend).
        if ( current_user_can( 'manage_options' ) ) {
            echo '<div class="pw-deprecation-notice">';
            echo '<p>' . esc_html__( 'This widget is using a deprecated version of Pearl Weather. Please replace it with the new widget.', 'pearl-weather' ) . '</p>';
            echo '</div>';
        }

        // Parse instance values.
        $title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
        $city = isset( $instance['city'] ) ? sanitize_text_field( $instance['city'] ) : 'London';
        $country = isset( $instance['country'] ) ? sanitize_text_field( $instance['country'] ) : 'UK';
        $units = isset( $instance['units'] ) ? sanitize_text_field( $instance['units'] ) : 'c';

        // Get API key from settings.
        $settings = get_option( 'pearl_weather_settings', array() );
        $api_key = isset( $settings['api_key'] ) ? $settings['api_key'] : '';

        $temperature_unit = ( 'c' === $units ) ? 'metric' : 'imperial';
        $location = ! empty( $country ) ? $city . ',' . $country : $city;

        // Generate unique ID for this widget instance.
        $widget_id = 'pw-legacy-' . $this->id;

        // Before widget hook.
        echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        // Title.
        if ( ! empty( $title ) ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        ?>
        <div class="pw-legacy-weather-widget" id="<?php echo esc_attr( $widget_id ); ?>">
            <div class="pw-legacy-weather-display" style="display: none;">
                <div class="pw-legacy-icon">
                    <img src="" alt="<?php esc_attr_e( 'Weather icon', 'pearl-weather' ); ?>" class="pw-weather-icon-img">
                </div>
                <div class="pw-legacy-info">
                    <span class="pw-legacy-condition"></span>
                    <span class="pw-legacy-temperature"></span>
                    <span class="pw-legacy-location"></span>
                    <span class="pw-legacy-date"></span>
                </div>
            </div>
            <div class="pw-legacy-loading">
                <span class="pw-spinner"></span>
                <?php esc_html_e( 'Loading weather data...', 'pearl-weather' ); ?>
            </div>
            <div class="pw-legacy-error" style="display: none;">
                <?php esc_html_e( 'Unable to load weather data.', 'pearl-weather' ); ?>
            </div>
        </div>

        <script type="text/javascript">
        (function() {
            const container = document.getElementById('<?php echo esc_js( $widget_id ); ?>');
            const displayDiv = container.querySelector('.pw-legacy-weather-display');
            const loadingDiv = container.querySelector('.pw-legacy-loading');
            const errorDiv = container.querySelector('.pw-legacy-error');
            
            // Build API URL.
            const apiUrl = 'https://api.openweathermap.org/data/2.5/weather?q=<?php echo esc_js( $location ); ?>&units=<?php echo esc_js( $temperature_unit ); ?>&appid=<?php echo esc_js( $api_key ); ?>';
            
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    loadingDiv.style.display = 'none';
                    
                    if (data.cod !== 200) {
                        throw new Error(data.message);
                    }
                    
                    // Update display.
                    const temp = Math.round(data.main.temp);
                    const unit = '<?php echo esc_js( 'c' === $units ? '°C' : '°F'; ) ?>';
                    const condition = data.weather[0].description;
                    const location = data.name + ', ' + (data.sys.country || '');
                    const date = new Date().toLocaleDateString();
                    const iconCode = data.weather[0].icon;
                    const iconUrl = 'https://openweathermap.org/img/w/' + iconCode + '.png';
                    
                    container.querySelector('.pw-weather-icon-img').src = iconUrl;
                    container.querySelector('.pw-legacy-condition').textContent = condition;
                    container.querySelector('.pw-legacy-temperature').textContent = temp + unit;
                    container.querySelector('.pw-legacy-location').textContent = location;
                    container.querySelector('.pw-legacy-date').textContent = date;
                    
                    displayDiv.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Legacy weather widget error:', error);
                    loadingDiv.style.display = 'none';
                    errorDiv.style.display = 'block';
                });
        })();
        </script>

        <style>
            .pw-legacy-weather-widget {
                font-family: inherit;
            }
            .pw-legacy-weather-display {
                display: flex;
                align-items: center;
                gap: 16px;
                padding: 12px;
                background: #f5f5f5;
                border-radius: 8px;
            }
            .pw-legacy-icon img {
                width: 50px;
                height: 50px;
            }
            .pw-legacy-info {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }
            .pw-legacy-temperature {
                font-size: 20px;
                font-weight: bold;
            }
            .pw-legacy-loading {
                text-align: center;
                padding: 20px;
            }
            .pw-spinner {
                display: inline-block;
                width: 20px;
                height: 20px;
                border: 2px solid #ddd;
                border-top-color: #f26c0d;
                border-radius: 50%;
                animation: pw-spin 0.8s linear infinite;
                margin-right: 8px;
                vertical-align: middle;
            }
            @keyframes pw-spin {
                to { transform: rotate(360deg); }
            }
            .pw-legacy-error {
                color: #dc3545;
                padding: 12px;
                text-align: center;
            }
            .pw-deprecation-notice {
                background: #fff3cd;
                border: 1px solid #ffeeba;
                color: #856404;
                padding: 8px 12px;
                margin-bottom: 12px;
                border-radius: 4px;
                font-size: 12px;
            }
        </style>
        <?php

        // After widget hook.
        echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Back-end widget form.
     *
     * @since 1.0.0
     * @deprecated 1.0.0
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $defaults = array(
            'title'   => '',
            'city'    => 'London',
            'country' => 'UK',
            'units'   => 'c',
        );

        $instance = wp_parse_args( (array) $instance, $defaults );

        ?>
        <div class="pw-legacy-widget-notice">
            <p class="description" style="color: #d63638; margin-bottom: 12px;">
                <?php esc_html_e( 'This widget is deprecated and will be removed in a future version. Please use the new Pearl Weather widget instead.', 'pearl-weather' ); ?>
            </p>
        </div>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php esc_html_e( 'Title:', 'pearl-weather' ); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
                   type="text" 
                   value="<?php echo esc_attr( $instance['title'] ); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'city' ) ); ?>">
                <?php esc_html_e( 'City:', 'pearl-weather' ); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr( $this->get_field_id( 'city' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'city' ) ); ?>" 
                   type="text" 
                   value="<?php echo esc_attr( $instance['city'] ); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'country' ) ); ?>">
                <?php esc_html_e( 'Country Code (e.g., UK, US, DE):', 'pearl-weather' ); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr( $this->get_field_id( 'country' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'country' ) ); ?>" 
                   type="text" 
                   value="<?php echo esc_attr( $instance['country'] ); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'units' ) ); ?>">
                <?php esc_html_e( 'Temperature Units:', 'pearl-weather' ); ?>
            </label>
            <select class="widefat" 
                    id="<?php echo esc_attr( $this->get_field_id( 'units' ) ); ?>" 
                    name="<?php echo esc_attr( $this->get_field_name( 'units' ) ); ?>">
                <option value="c" <?php selected( $instance['units'], 'c' ); ?>><?php esc_html_e( 'Celsius (°C)', 'pearl-weather' ); ?></option>
                <option value="f" <?php selected( $instance['units'], 'f' ); ?>><?php esc_html_e( 'Fahrenheit (°F)', 'pearl-weather' ); ?></option>
            </select>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @since 1.0.0
     * @deprecated 1.0.0
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();

        $instance['title']   = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['city']    = isset( $new_instance['city'] ) ? sanitize_text_field( $new_instance['city'] ) : '';
        $instance['country'] = isset( $new_instance['country'] ) ? sanitize_text_field( $new_instance['country'] ) : '';
        $instance['units']   = isset( $new_instance['units'] ) ? sanitize_text_field( $new_instance['units'] ) : 'c';

        return $instance;
    }
}

// Register the legacy widget.
add_action( 'widgets_init', function() {
    register_widget( 'PearlWeather\Admin\LegacyWeatherWidget' );
} );