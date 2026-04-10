<?php
namespace AtmoPress\Admin;

use AtmoPress\Settings;
use AtmoPress\TemplateLoader;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class AdminPage {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
    }

    public function add_menu() {
        add_menu_page(
            __( 'AtmoPress Weather', 'atmopress-weather' ),
            __( 'AtmoPress', 'atmopress-weather' ),
            'manage_options',
            'atmopress-weather',
            array( $this, 'render_page' ),
            'dashicons-cloud',
            58
        );

        add_submenu_page(
            'atmopress-weather',
            __( 'Settings', 'atmopress-weather' ),
            __( 'Settings', 'atmopress-weather' ),
            'manage_options',
            'atmopress-weather',
            array( $this, 'render_page' )
        );

        add_submenu_page(
            'atmopress-weather',
            __( 'Templates & Preview', 'atmopress-weather' ),
            __( 'Templates', 'atmopress-weather' ),
            'manage_options',
            'atmopress-templates',
            array( $this, 'render_templates_page' )
        );

        add_submenu_page(
            'atmopress-weather',
            __( 'Shortcode Generator', 'atmopress-weather' ),
            __( 'Shortcode', 'atmopress-weather' ),
            'manage_options',
            'atmopress-shortcode',
            array( $this, 'render_shortcode_page' )
        );
    }

    public function render_page() {
        $settings   = Settings::get_all();
        $templates  = TemplateLoader::registered();
        $saved      = false;

        if ( isset( $_POST['atmopress_save'] ) && check_admin_referer( 'atmopress_save_settings' ) ) {
            Settings::save( $_POST );
            $settings = Settings::get_all();
            $saved    = true;
        }
        ?>
        <div class="wrap atmopress-admin-wrap">
            <div class="atmopress-admin-header">
                <h1><?php esc_html_e( 'AtmoPress Weather – Settings', 'atmopress-weather' ); ?></h1>
                <p class="atmopress-admin-tagline"><?php esc_html_e( 'Configure your weather plugin below.', 'atmopress-weather' ); ?></p>
            </div>

            <?php if ( $saved ) : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved successfully!', 'atmopress-weather' ); ?></p></div>
            <?php endif; ?>

            <form method="post" action="" class="atmopress-settings-form">
                <?php wp_nonce_field( 'atmopress_save_settings' ); ?>

                <div class="atmopress-settings-grid">

                    <!-- API Settings -->
                    <div class="atmopress-card">
                        <h2 class="atmopress-card-title"><?php esc_html_e( 'API Configuration', 'atmopress-weather' ); ?></h2>

                        <div class="atmopress-field">
                            <label for="api_provider"><?php esc_html_e( 'Weather Provider', 'atmopress-weather' ); ?></label>
                            <select id="api_provider" name="api_provider">
                                <option value="openweathermap" <?php selected( $settings['api_provider'], 'openweathermap' ); ?>><?php esc_html_e( 'OpenWeatherMap (Free)', 'atmopress-weather' ); ?></option>
                                <option value="weatherapi" <?php selected( $settings['api_provider'], 'weatherapi' ); ?>><?php esc_html_e( 'WeatherAPI.com (Free tier)', 'atmopress-weather' ); ?></option>
                            </select>
                            <span class="atmopress-hint"><?php esc_html_e( 'Both providers offer free tiers with generous limits.', 'atmopress-weather' ); ?></span>
                        </div>

                        <div class="atmopress-field">
                            <label for="api_key"><?php esc_html_e( 'API Key', 'atmopress-weather' ); ?></label>
                            <div class="atmopress-key-row">
                                <input type="text" id="api_key" name="api_key" value="<?php echo esc_attr( $settings['api_key'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Paste your API key here', 'atmopress-weather' ); ?>" />
                                <button type="button" id="atmopress-test-key" class="button button-secondary"><?php esc_html_e( 'Test Key', 'atmopress-weather' ); ?></button>
                            </div>
                            <div id="atmopress-test-result"></div>
                        </div>

                        <div class="atmopress-api-links">
                            <a href="https://home.openweathermap.org/api_keys" target="_blank" rel="noopener"><?php esc_html_e( 'Get OpenWeatherMap key →', 'atmopress-weather' ); ?></a>
                            &nbsp;|&nbsp;
                            <a href="https://www.weatherapi.com/my/" target="_blank" rel="noopener"><?php esc_html_e( 'Get WeatherAPI.com key →', 'atmopress-weather' ); ?></a>
                        </div>
                    </div>

                    <!-- General Settings -->
                    <div class="atmopress-card">
                        <h2 class="atmopress-card-title"><?php esc_html_e( 'General Settings', 'atmopress-weather' ); ?></h2>

                        <div class="atmopress-field">
                            <label for="default_location"><?php esc_html_e( 'Default Location', 'atmopress-weather' ); ?></label>
                            <input type="text" id="default_location" name="default_location" value="<?php echo esc_attr( $settings['default_location'] ); ?>" class="regular-text" placeholder="London" />
                        </div>

                        <div class="atmopress-field">
                            <label for="units"><?php esc_html_e( 'Temperature Unit', 'atmopress-weather' ); ?></label>
                            <select id="units" name="units">
                                <option value="metric" <?php selected( $settings['units'], 'metric' ); ?>><?php esc_html_e( 'Metric (°C, m/s)', 'atmopress-weather' ); ?></option>
                                <option value="imperial" <?php selected( $settings['units'], 'imperial' ); ?>><?php esc_html_e( 'Imperial (°F, mph)', 'atmopress-weather' ); ?></option>
                            </select>
                        </div>

                        <div class="atmopress-field">
                            <label for="cache_minutes"><?php esc_html_e( 'Cache Duration (minutes)', 'atmopress-weather' ); ?></label>
                            <input type="number" id="cache_minutes" name="cache_minutes" value="<?php echo esc_attr( $settings['cache_minutes'] ); ?>" min="0" max="1440" step="5" />
                            <span class="atmopress-hint"><?php esc_html_e( 'Set to 0 to disable caching.', 'atmopress-weather' ); ?></span>
                        </div>
                    </div>

                    <!-- Feature Toggles -->
                    <div class="atmopress-card">
                        <h2 class="atmopress-card-title"><?php esc_html_e( 'Feature Toggles', 'atmopress-weather' ); ?></h2>

                        <div class="atmopress-toggle-row">
                            <label>
                                <input type="checkbox" name="show_search" value="1" <?php checked( $settings['show_search'] ); ?> />
                                <?php esc_html_e( 'Show city search bar', 'atmopress-weather' ); ?>
                            </label>
                        </div>
                        <div class="atmopress-toggle-row">
                            <label>
                                <input type="checkbox" name="show_geolocation" value="1" <?php checked( $settings['show_geolocation'] ); ?> />
                                <?php esc_html_e( 'Enable auto-detect location (geolocation)', 'atmopress-weather' ); ?>
                            </label>
                        </div>
                    </div>

                    <!-- Cache Actions -->
                    <div class="atmopress-card">
                        <h2 class="atmopress-card-title"><?php esc_html_e( 'Cache', 'atmopress-weather' ); ?></h2>
                        <button type="button" id="atmopress-flush-cache" class="button button-secondary"><?php esc_html_e( 'Flush Weather Cache', 'atmopress-weather' ); ?></button>
                        <div id="atmopress-flush-result" style="margin-top:8px;"></div>
                    </div>

                </div><!-- /.atmopress-settings-grid -->

                <p class="submit">
                    <input type="hidden" name="atmopress_save" value="1" />
                    <button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Save Settings', 'atmopress-weather' ); ?></button>
                </p>
            </form>
        </div>
        <?php
    }

    public function render_templates_page() {
        $templates = TemplateLoader::registered();
        ?>
        <div class="wrap atmopress-admin-wrap">
            <div class="atmopress-admin-header">
                <h1><?php esc_html_e( 'AtmoPress Weather – Templates', 'atmopress-weather' ); ?></h1>
                <p><?php esc_html_e( 'Browse available templates and copy the shortcode for each.', 'atmopress-weather' ); ?></p>
            </div>
            <div class="atmopress-templates-grid">
                <?php foreach ( $templates as $slug => $tpl ) : ?>
                    <div class="atmopress-template-card">
                        <div class="atmopress-template-preview atmopress-template-preview--<?php echo esc_attr( $slug ); ?>">
                            <div class="atmopress-template-icon dashicons dashicons-cloud"></div>
                            <span class="atmopress-template-name"><?php echo esc_html( $tpl['label'] ); ?></span>
                        </div>
                        <div class="atmopress-template-foot">
                            <code>[atmopress template="<?php echo esc_attr( $slug ); ?>"]</code>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    public function render_shortcode_page() {
        $templates = TemplateLoader::registered();
        ?>
        <div class="wrap atmopress-admin-wrap">
            <div class="atmopress-admin-header">
                <h1><?php esc_html_e( 'Shortcode Generator', 'atmopress-weather' ); ?></h1>
                <p><?php esc_html_e( 'Configure your widget and copy the shortcode.', 'atmopress-weather' ); ?></p>
            </div>

            <div class="atmopress-shortcode-builder">
                <div class="atmopress-sc-controls">
                    <div class="atmopress-field">
                        <label><?php esc_html_e( 'Template', 'atmopress-weather' ); ?></label>
                        <select id="sc-template">
                            <?php foreach ( $templates as $slug => $tpl ) : ?>
                                <option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $tpl['label'] ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="atmopress-field">
                        <label><?php esc_html_e( 'Location', 'atmopress-weather' ); ?></label>
                        <input type="text" id="sc-location" value="London" />
                    </div>
                    <div class="atmopress-field">
                        <label><?php esc_html_e( 'Units', 'atmopress-weather' ); ?></label>
                        <select id="sc-units">
                            <option value="metric"><?php esc_html_e( 'Metric (°C)', 'atmopress-weather' ); ?></option>
                            <option value="imperial"><?php esc_html_e( 'Imperial (°F)', 'atmopress-weather' ); ?></option>
                        </select>
                    </div>
                    <div class="atmopress-field">
                        <label><?php esc_html_e( 'Primary Color', 'atmopress-weather' ); ?></label>
                        <input type="color" id="sc-color" value="#2563eb" />
                    </div>
                    <div class="atmopress-field">
                        <label><?php esc_html_e( 'Forecast Days (1–7)', 'atmopress-weather' ); ?></label>
                        <input type="number" id="sc-days" value="7" min="1" max="7" />
                    </div>
                    <div class="atmopress-toggle-row"><label><input type="checkbox" id="sc-search" checked> <?php esc_html_e( 'Show Search', 'atmopress-weather' ); ?></label></div>
                    <div class="atmopress-toggle-row"><label><input type="checkbox" id="sc-geo" checked> <?php esc_html_e( 'Show Geolocation', 'atmopress-weather' ); ?></label></div>
                    <div class="atmopress-toggle-row"><label><input type="checkbox" id="sc-humidity" checked> <?php esc_html_e( 'Show Humidity', 'atmopress-weather' ); ?></label></div>
                    <div class="atmopress-toggle-row"><label><input type="checkbox" id="sc-wind" checked> <?php esc_html_e( 'Show Wind', 'atmopress-weather' ); ?></label></div>
                    <div class="atmopress-toggle-row"><label><input type="checkbox" id="sc-pressure" checked> <?php esc_html_e( 'Show Pressure', 'atmopress-weather' ); ?></label></div>
                    <div class="atmopress-toggle-row"><label><input type="checkbox" id="sc-hourly" checked> <?php esc_html_e( 'Show Hourly Forecast', 'atmopress-weather' ); ?></label></div>
                    <div class="atmopress-toggle-row"><label><input type="checkbox" id="sc-daily" checked> <?php esc_html_e( 'Show Daily Forecast', 'atmopress-weather' ); ?></label></div>
                </div>

                <div class="atmopress-sc-output">
                    <h3><?php esc_html_e( 'Generated Shortcode', 'atmopress-weather' ); ?></h3>
                    <div class="atmopress-sc-code">
                        <code id="sc-output"></code>
                        <button type="button" id="sc-copy" class="button button-secondary"><?php esc_html_e( 'Copy', 'atmopress-weather' ); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
