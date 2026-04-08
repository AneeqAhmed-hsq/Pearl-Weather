<?php
/**
 * Admin Dashboard Page
 *
 * Manages the plugin admin dashboard, settings, and submenu pages.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Admin
 * @since      1.0.0
 */

namespace PearlWeather\Admin;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AdminDashboard
 *
 * Handles the plugin admin dashboard interface.
 *
 * @since 1.0.0
 */
class AdminDashboard {

    /**
     * Post type for weather widgets.
     */
    const POST_TYPE = 'pearl_weather_widget';

    /**
     * Page slug.
     */
    const PAGE_SLUG = 'pw_admin_dashboard';

    /**
     * Recommended plugins list.
     *
     * @var array
     */
    private $recommended_plugins = array(
        'woo-product-slider'             => 'main.php',
        'post-carousel'                  => 'main.php',
        'easy-accordion-free'            => 'plugin-main.php',
        'logo-carousel-free'             => 'main.php',
    );

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_pages' ), 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_pw_update_settings', array( $this, 'ajax_update_settings' ) );
        add_action( 'wp_ajax_pw_clear_cache', array( $this, 'ajax_clear_cache' ) );
        add_action( 'wp_ajax_pw_dismiss_consent', array( $this, 'ajax_dismiss_consent' ) );
        add_action( 'admin_notices', array( $this, 'maybe_show_consent_notice' ) );
    }

    /**
     * Add admin menu pages.
     *
     * @since 1.0.0
     */
    public function add_admin_pages() {
        // Main dashboard page.
        add_submenu_page(
            'edit.php?post_type=' . self::POST_TYPE,
            __( 'Pearl Weather Dashboard', 'pearl-weather' ),
            __( 'Dashboard', 'pearl-weather' ),
            'manage_options',
            self::PAGE_SLUG,
            array( $this, 'render_dashboard_page' )
        );

        // Settings page (hash link to dashboard).
        add_submenu_page(
            'edit.php?post_type=' . self::POST_TYPE,
            __( 'Settings', 'pearl-weather' ),
            __( 'Settings', 'pearl-weather' ),
            'manage_options',
            'edit.php?post_type=' . self::POST_TYPE . '&page=' . self::PAGE_SLUG . '#settings'
        );

        // Upgrade to Pro link.
        add_submenu_page(
            'edit.php?post_type=' . self::POST_TYPE,
            __( 'Upgrade to Pro', 'pearl-weather' ),
            '<span style="color: #f26c0d;">' . __( 'Upgrade to Pro', 'pearl-weather' ) . '</span>',
            'manage_options',
            'pw_upgrade',
            '__return_null'
        );
    }

    /**
     * Enqueue admin assets.
     *
     * @since 1.0.0
     * @param string $hook Current page hook.
     */
    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, self::PAGE_SLUG ) === false ) {
            return;
        }

        wp_enqueue_style( 'pearl-weather-admin' );
        wp_enqueue_script(
            'pearl-weather-dashboard',
            PEARL_WEATHER_ASSETS_URL . 'js/admin-dashboard.js',
            array( 'wp-element', 'wp-components' ),
            PEARL_WEATHER_VERSION,
            true
        );

        wp_localize_script(
            'pearl-weather-dashboard',
            'pwAdmin',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'pw_admin_nonce' ),
                'settings' => get_option( 'pearl_weather_settings', array() ),
                'version'  => PEARL_WEATHER_VERSION,
            )
        );
    }

    /**
     * Render the dashboard page.
     *
     * @since 1.0.0
     */
    public function render_dashboard_page() {
        ?>
        <div class="pw-admin-dashboard">
            <div class="pw-admin-header">
                <div class="pw-admin-header-inner">
                    <div class="pw-logo">
                        <img src="<?php echo esc_url( PEARL_WEATHER_ASSETS_URL . 'images/logo.svg' ); ?>" alt="Pearl Weather">
                        <span class="pw-version"><?php echo esc_html( PEARL_WEATHER_VERSION ); ?></span>
                    </div>
                    <div class="pw-admin-nav">
                        <a href="#getting-started" class="nav-tab nav-tab-active"><?php esc_html_e( 'Getting Started', 'pearl-weather' ); ?></a>
                        <a href="#settings" class="nav-tab"><?php esc_html_e( 'Settings', 'pearl-weather' ); ?></a>
                        <a href="#tools" class="nav-tab"><?php esc_html_e( 'Tools', 'pearl-weather' ); ?></a>
                        <a href="#recommended" class="nav-tab"><?php esc_html_e( 'Recommended Plugins', 'pearl-weather' ); ?></a>
                    </div>
                </div>
            </div>

            <div class="pw-admin-content">
                <div id="getting-started" class="pw-tab-content active">
                    <?php $this->render_getting_started_tab(); ?>
                </div>
                <div id="settings" class="pw-tab-content">
                    <?php $this->render_settings_tab(); ?>
                </div>
                <div id="tools" class="pw-tab-content">
                    <?php $this->render_tools_tab(); ?>
                </div>
                <div id="recommended" class="pw-tab-content">
                    <?php $this->render_recommended_tab(); ?>
                </div>
            </div>
        </div>

        <style>
            .pw-admin-dashboard {
                background: #f1f1f1;
                min-height: 100vh;
            }
            .pw-admin-header {
                background: #fff;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .pw-admin-header-inner {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
            }
            .pw-logo {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .pw-logo img {
                height: 40px;
            }
            .pw-version {
                background: #f0f0f0;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
            }
            .pw-admin-nav {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
            }
            .pw-admin-nav .nav-tab {
                text-decoration: none;
                padding: 8px 16px;
                border-radius: 4px;
            }
            .pw-admin-nav .nav-tab-active {
                background: #f26c0d;
                color: #fff;
                border-color: #f26c0d;
            }
            .pw-admin-content {
                max-width: 1200px;
                margin: 40px auto;
                padding: 0 20px;
            }
            .pw-tab-content {
                display: none;
            }
            .pw-tab-content.active {
                display: block;
            }
            .pw-settings-form {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .pw-settings-field {
                margin-bottom: 20px;
            }
            .pw-settings-field label {
                display: block;
                font-weight: 600;
                margin-bottom: 8px;
            }
            .pw-settings-field input[type="text"],
            .pw-settings-field select {
                width: 100%;
                max-width: 400px;
                padding: 8px 12px;
            }
            .pw-clear-cache-btn {
                background: #dc3545;
                color: #fff;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
            }
            .pw-clear-cache-btn:hover {
                background: #c82333;
            }
        </style>

        <script>
        (function($) {
            // Tab switching
            $('.pw-admin-nav .nav-tab').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                $('.pw-admin-nav .nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.pw-tab-content').removeClass('active');
                $(target).addClass('active');
                
                // Update URL hash
                window.location.hash = target;
            });
            
            // Check hash on load
            if (window.location.hash) {
                $('.pw-admin-nav .nav-tab[href="' + window.location.hash + '"]').trigger('click');
            }
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Render Getting Started tab.
     *
     * @since 1.0.0
     */
    private function render_getting_started_tab() {
        ?>
        <div class="pw-getting-started">
            <div class="pw-welcome-box">
                <h2><?php esc_html_e( 'Welcome to Pearl Weather!', 'pearl-weather' ); ?></h2>
                <p><?php esc_html_e( 'Thank you for installing Pearl Weather. Get started by creating your first weather widget.', 'pearl-weather' ); ?></p>
                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . self::POST_TYPE ) ); ?>" class="button button-primary button-hero">
                    <?php esc_html_e( 'Create Weather Widget', 'pearl-weather' ); ?>
                </a>
            </div>
            
            <div class="pw-help-links">
                <div class="pw-help-card">
                    <h3><?php esc_html_e( '📖 Documentation', 'pearl-weather' ); ?></h3>
                    <p><?php esc_html_e( 'Learn how to use Pearl Weather with our detailed documentation.', 'pearl-weather' ); ?></p>
                    <a href="https://pearlweather.com/docs/" target="_blank" class="button"><?php esc_html_e( 'Read Docs', 'pearl-weather' ); ?></a>
                </div>
                <div class="pw-help-card">
                    <h3><?php esc_html_e( '🎥 Video Tutorials', 'pearl-weather' ); ?></h3>
                    <p><?php esc_html_e( 'Watch our video tutorials to get started quickly.', 'pearl-weather' ); ?></p>
                    <a href="https://www.youtube.com/@PearlWeather" target="_blank" class="button"><?php esc_html_e( 'Watch Videos', 'pearl-weather' ); ?></a>
                </div>
                <div class="pw-help-card">
                    <h3><?php esc_html_e( '💬 Support', 'pearl-weather' ); ?></h3>
                    <p><?php esc_html_e( 'Need help? Contact our support team.', 'pearl-weather' ); ?></p>
                    <a href="https://pearlweather.com/support/" target="_blank" class="button"><?php esc_html_e( 'Get Support', 'pearl-weather' ); ?></a>
                </div>
            </div>
        </div>
        <style>
            .pw-getting-started {
                display: grid;
                gap: 30px;
            }
            .pw-welcome-box {
                background: #fff;
                padding: 40px;
                text-align: center;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .pw-help-links {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
            }
            .pw-help-card {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                text-align: center;
            }
        </style>
        <?php
    }

    /**
     * Render Settings tab.
     *
     * @since 1.0.0
     */
    private function render_settings_tab() {
        $settings = get_option( 'pearl_weather_settings', array() );
        $api_key = isset( $settings['api_key'] ) ? $settings['api_key'] : '';
        $units = isset( $settings['units'] ) ? $settings['units'] : 'metric';
        $cache_duration = isset( $settings['cache_duration'] ) ? $settings['cache_duration'] : 600;
        ?>
        <div class="pw-settings-form">
            <form id="pw-settings-form">
                <div class="pw-settings-field">
                    <label for="api_key"><?php esc_html_e( 'OpenWeatherMap API Key', 'pearl-weather' ); ?></label>
                    <input type="text" id="api_key" name="api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e( 'Enter your OpenWeatherMap API key. Get one from', 'pearl-weather' ); ?> <a href="https://openweathermap.org/api" target="_blank">OpenWeatherMap</a>.</p>
                </div>

                <div class="pw-settings-field">
                    <label for="units"><?php esc_html_e( 'Temperature Units', 'pearl-weather' ); ?></label>
                    <select id="units" name="units">
                        <option value="metric" <?php selected( $units, 'metric' ); ?>><?php esc_html_e( 'Celsius (°C)', 'pearl-weather' ); ?></option>
                        <option value="imperial" <?php selected( $units, 'imperial' ); ?>><?php esc_html_e( 'Fahrenheit (°F)', 'pearl-weather' ); ?></option>
                    </select>
                </div>

                <div class="pw-settings-field">
                    <label for="cache_duration"><?php esc_html_e( 'Cache Duration (seconds)', 'pearl-weather' ); ?></label>
                    <input type="number" id="cache_duration" name="cache_duration" value="<?php echo esc_attr( $cache_duration ); ?>" min="60" step="60">
                    <p class="description"><?php esc_html_e( 'How long to cache weather data. Default is 600 seconds (10 minutes).', 'pearl-weather' ); ?></p>
                </div>

                <div class="pw-settings-field">
                    <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Settings', 'pearl-weather' ); ?></button>
                    <span class="pw-settings-message" style="display:none; margin-left: 10px; color: green;"></span>
                </div>
            </form>
        </div>

        <script>
        (function($) {
            $('#pw-settings-form').on('submit', function(e) {
                e.preventDefault();
                
                $.post(ajaxurl, {
                    action: 'pw_update_settings',
                    nonce: pwAdmin.nonce,
                    settings: $(this).serialize()
                }).done(function(response) {
                    if (response.success) {
                        $('.pw-settings-message').text('Settings saved!').show().fadeOut(3000);
                    } else {
                        alert(response.data.message || 'Error saving settings.');
                    }
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Render Tools tab.
     *
     * @since 1.0.0
     */
    private function render_tools_tab() {
        ?>
        <div class="pw-tools">
            <div class="pw-tool-card">
                <h3><?php esc_html_e( 'Clear Weather Cache', 'pearl-weather' ); ?></h3>
                <p><?php esc_html_e( 'Clear all cached weather data to force fresh data from the API.', 'pearl-weather' ); ?></p>
                <button class="button pw-clear-cache-btn"><?php esc_html_e( 'Clear Cache', 'pearl-weather' ); ?></button>
                <span class="pw-clear-message" style="display:none; margin-left: 10px;"></span>
            </div>

            <div class="pw-tool-card">
                <h3><?php esc_html_e( 'Export/Import Widgets', 'pearl-weather' ); ?></h3>
                <p><?php esc_html_e( 'Export your weather widgets to JSON or import from a backup.', 'pearl-weather' ); ?></p>
                <button class="button pw-export-btn"><?php esc_html_e( 'Export', 'pearl-weather' ); ?></button>
                <button class="button pw-import-btn"><?php esc_html_e( 'Import', 'pearl-weather' ); ?></button>
            </div>
        </div>

        <script>
        (function($) {
            $('.pw-clear-cache-btn').on('click', function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('Clearing...');
                
                $.post(ajaxurl, {
                    action: 'pw_clear_cache',
                    nonce: pwAdmin.nonce
                }).done(function(response) {
                    if (response.success) {
                        $('.pw-clear-message').text('Cache cleared!').show().fadeOut(3000);
                    } else {
                        alert('Error clearing cache.');
                    }
                    $btn.prop('disabled', false).text('Clear Cache');
                });
            });
        })(jQuery);
        </script>
        <style>
            .pw-tools {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
            }
            .pw-tool-card {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
        </style>
        <?php
    }

    /**
     * Render Recommended Plugins tab.
     *
     * @since 1.0.0
     */
    private function render_recommended_tab() {
        $plugins = $this->get_recommended_plugins();
        ?>
        <div class="pw-recommended-plugins">
            <div class="pw-plugins-grid">
                <?php foreach ( $plugins as $plugin ) : ?>
                    <div class="pw-plugin-card">
                        <div class="pw-plugin-icon">
                            <img src="<?php echo esc_url( $plugin['icon'] ); ?>" alt="<?php echo esc_attr( $plugin['name'] ); ?>">
                        </div>
                        <div class="pw-plugin-info">
                            <h3><?php echo esc_html( $plugin['name'] ); ?></h3>
                            <p><?php echo esc_html( $plugin['description'] ); ?></p>
                            <div class="pw-plugin-actions">
                                <?php if ( $this->is_plugin_installed( $plugin['slug'] ) ) : ?>
                                    <?php if ( $this->is_plugin_active( $plugin['slug'] ) ) : ?>
                                        <span class="button button-disabled"><?php esc_html_e( 'Active', 'pearl-weather' ); ?></span>
                                    <?php else : ?>
                                        <a href="<?php echo esc_url( $this->get_activation_url( $plugin['slug'] ) ); ?>" class="button button-primary">
                                            <?php esc_html_e( 'Activate', 'pearl-weather' ); ?>
                                        </a>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <a href="<?php echo esc_url( $this->get_install_url( $plugin['slug'] ) ); ?>" class="button">
                                        <?php esc_html_e( 'Install', 'pearl-weather' ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <style>
            .pw-plugins-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
                gap: 20px;
            }
            .pw-plugin-card {
                background: #fff;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                display: flex;
                padding: 20px;
                gap: 15px;
            }
            .pw-plugin-icon img {
                width: 60px;
                height: 60px;
            }
            .pw-plugin-info {
                flex: 1;
            }
            .pw-plugin-info h3 {
                margin: 0 0 8px 0;
                font-size: 16px;
            }
            .pw-plugin-info p {
                margin: 0 0 12px 0;
                color: #666;
                font-size: 13px;
            }
        </style>
        <?php
    }

    /**
     * Get recommended plugins from WordPress.org.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_recommended_plugins() {
        $cached = get_transient( 'pw_recommended_plugins' );
        
        if ( false !== $cached ) {
            return $cached;
        }

        // Fallback plugins list.
        $plugins = array(
            array(
                'slug'        => 'woo-product-slider',
                'name'        => 'Woo Product Slider',
                'description' => 'Display WooCommerce products in a beautiful slider or grid.',
                'icon'        => 'https://ps.w.org/woo-product-slider/assets/icon-128x128.png',
            ),
            array(
                'slug'        => 'post-carousel',
                'name'        => 'Post Carousel',
                'description' => 'Display posts in a responsive carousel or grid layout.',
                'icon'        => 'https://ps.w.org/post-carousel/assets/icon-128x128.png',
            ),
            array(
                'slug'        => 'easy-accordion-free',
                'name'        => 'Easy Accordion',
                'description' => 'Create responsive accordions and FAQs easily.',
                'icon'        => 'https://ps.w.org/easy-accordion-free/assets/icon-128x128.png',
            ),
            array(
                'slug'        => 'logo-carousel-free',
                'name'        => 'Logo Carousel',
                'description' => 'Display logos in a responsive carousel or grid.',
                'icon'        => 'https://ps.w.org/logo-carousel-free/assets/icon-128x128.png',
            ),
        );

        set_transient( 'pw_recommended_plugins', $plugins, DAY_IN_SECONDS );

        return $plugins;
    }

    /**
     * Check if plugin is installed.
     *
     * @since 1.0.0
     * @param string $slug Plugin slug.
     * @return bool
     */
    private function is_plugin_installed( $slug ) {
        $plugins = get_plugins();
        foreach ( $plugins as $plugin_file => $plugin_data ) {
            if ( strpos( $plugin_file, $slug . '/' ) === 0 ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if plugin is active.
     *
     * @since 1.0.0
     * @param string $slug Plugin slug.
     * @return bool
     */
    private function is_plugin_active( $slug ) {
        return is_plugin_active( $slug . '/' . $slug . '.php' );
    }

    /**
     * Get plugin installation URL.
     *
     * @since 1.0.0
     * @param string $slug Plugin slug.
     * @return string
     */
    private function get_install_url( $slug ) {
        return wp_nonce_url(
            self_admin_url( 'update.php?action=install-plugin&plugin=' . $slug ),
            'install-plugin_' . $slug
        );
    }

    /**
     * Get plugin activation URL.
     *
     * @since 1.0.0
     * @param string $slug Plugin slug.
     * @return string
     */
    private function get_activation_url( $slug ) {
        $plugin_file = $slug . '/' . $slug . '.php';
        return wp_nonce_url(
            admin_url( 'plugins.php?action=activate&plugin=' . $plugin_file ),
            'activate-plugin_' . $plugin_file
        );
    }

    /**
     * AJAX handler for updating settings.
     *
     * @since 1.0.0
     */
    public function ajax_update_settings() {
        if ( ! check_ajax_referer( 'pw_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'pearl-weather' ) ), 403 );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'pearl-weather' ) ), 403 );
        }

        parse_str( $_POST['settings'], $settings );
        
        $new_settings = array(
            'api_key'        => isset( $settings['api_key'] ) ? sanitize_text_field( $settings['api_key'] ) : '',
            'units'          => isset( $settings['units'] ) ? sanitize_text_field( $settings['units'] ) : 'metric',
            'cache_duration' => isset( $settings['cache_duration'] ) ? absint( $settings['cache_duration'] ) : 600,
        );

        update_option( 'pearl_weather_settings', $new_settings );

        wp_send_json_success( array( 'message' => __( 'Settings saved.', 'pearl-weather' ) ) );
    }

    /**
     * AJAX handler for clearing cache.
     *
     * @since 1.0.0
     */
    public function ajax_clear_cache() {
        if ( ! check_ajax_referer( 'pw_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'pearl-weather' ) ), 403 );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'pearl-weather' ) ), 403 );
        }

        global $wpdb;
        
        // Clear all plugin transients.
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pw_%'" );
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pw_%'" );

        wp_send_json_success( array( 'message' => __( 'Cache cleared.', 'pearl-weather' ) ) );
    }

    /**
     * AJAX handler for dismissing consent notice.
     *
     * @since 1.0.0
     */
    public function ajax_dismiss_consent() {
        if ( ! check_ajax_referer( 'pw_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'pearl-weather' ) ), 403 );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'pearl-weather' ) ), 403 );
        }

        update_option( 'pw_consent_notice_dismissed', true );

        wp_send_json_success();
    }

    /**
     * Show consent notice for anonymous data collection.
     *
     * @since 1.0.0
     */
    public function maybe_show_consent_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $dismissed = get_option( 'pw_consent_notice_dismissed', false );
        $allowed = get_option( 'pw_allow_anonymous_data', false );

        if ( $dismissed || $allowed ) {
            return;
        }

        ?>
        <div class="notice notice-info is-dismissible pw-consent-notice">
            <p>
                <?php esc_html_e( 'Help us improve Pearl Weather by allowing anonymous usage data collection. This helps us fix bugs and improve performance.', 'pearl-weather' ); ?>
                <a href="https://pearlweather.com/privacy-policy/" target="_blank"><?php esc_html_e( 'Learn More', 'pearl-weather' ); ?></a>
            </p>
            <p>
                <button class="button button-primary pw-allow-consent"><?php esc_html_e( 'Allow', 'pearl-weather' ); ?></button>
                <button class="button pw-deny-consent"><?php esc_html_e( 'No Thanks', 'pearl-weather' ); ?></button>
            </p>
        </div>

        <script>
        (function($) {
            $('.pw-allow-consent').on('click', function() {
                $.post(ajaxurl, {
                    action: 'pw_allow_consent',
                    nonce: pwAdmin.nonce
                }).done(function() {
                    $('.pw-consent-notice').fadeOut();
                });
            });
            
            $('.pw-deny-consent').on('click', function() {
                $.post(ajaxurl, {
                    action: 'pw_dismiss_consent',
                    nonce: pwAdmin.nonce
                }).done(function() {
                    $('.pw-consent-notice').fadeOut();
                });
            });
        })(jQuery);
        </script>
        <?php
    }
}

// Initialize admin dashboard.
new AdminDashboard();