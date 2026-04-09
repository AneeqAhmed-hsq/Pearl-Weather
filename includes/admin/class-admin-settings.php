<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pearl_Weather_Admin_Settings {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Pearl Weather', 'pearl-weather' ),
            __( 'Pearl Weather', 'pearl-weather' ),
            'manage_options',
            'pearl-weather-settings',
            array( $this, 'render_settings_page' ),
            'dashicons-cloud',
            30
        );
    }

    public function register_settings() {
        register_setting( 'pearl_weather_settings_group', 'pearl_weather_settings', array( $this, 'sanitize_settings' ) );
    }

    public function sanitize_settings( $input ) {
        $sanitized = array();
        if ( isset( $input['api_key'] ) ) {
            $sanitized['api_key'] = sanitize_text_field( $input['api_key'] );
        }
        if ( isset( $input['cache_duration'] ) ) {
            $sanitized['cache_duration'] = absint( $input['cache_duration'] );
        }
        if ( isset( $input['units'] ) && in_array( $input['units'], array( 'metric', 'imperial', 'standard' ), true ) ) {
            $sanitized['units'] = $input['units'];
        }
        if ( isset( $input['default_location'] ) ) {
            $sanitized['default_location'] = sanitize_text_field( $input['default_location'] );
        }
        $sanitized['enable_geolocation'] = ! empty( $input['enable_geolocation'] );
        return $sanitized;
    }

    public function render_settings_page() {
        $settings = get_option( 'pearl_weather_settings', array() );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Pearl Weather Settings', 'pearl-weather' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'pearl_weather_settings_group' ); ?>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'OpenWeatherMap API Key', 'pearl-weather' ); ?></th>
                        <td>
                            <input type="text" name="pearl_weather_settings[api_key]" value="<?php echo esc_attr( isset( $settings['api_key'] ) ? $settings['api_key'] : '' ); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Units', 'pearl-weather' ); ?></th>
                        <td>
                            <select name="pearl_weather_settings[units]">
                                <option value="metric" <?php selected( isset( $settings['units'] ) ? $settings['units'] : 'metric', 'metric' ); ?>><?php esc_html_e( 'Metric (°C)', 'pearl-weather' ); ?></option>
                                <option value="imperial" <?php selected( isset( $settings['units'] ) ? $settings['units'] : 'metric', 'imperial' ); ?>><?php esc_html_e( 'Imperial (°F)', 'pearl-weather' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Cache Duration (seconds)', 'pearl-weather' ); ?></th>
                        <td>
                            <input type="number" name="pearl_weather_settings[cache_duration]" value="<?php echo esc_attr( isset( $settings['cache_duration'] ) ? $settings['cache_duration'] : 600 ); ?>" min="0" />
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Default Location', 'pearl-weather' ); ?></th>
                        <td>
                            <input type="text" name="pearl_weather_settings[default_location]" value="<?php echo esc_attr( isset( $settings['default_location'] ) ? $settings['default_location'] : '' ); ?>" class="regular-text" placeholder="e.g. London, UK" />
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

new Pearl_Weather_Admin_Settings();
