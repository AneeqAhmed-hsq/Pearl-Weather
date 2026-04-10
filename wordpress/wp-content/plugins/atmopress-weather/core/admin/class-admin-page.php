<?php
namespace AtmoPress\Admin;

use AtmoPress\Settings;
use AtmoPress\TemplateLoader;
use AtmoPress\DataCache;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class AdminPage {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menus' ) );
        add_action( 'admin_notices', array( $this, 'maybe_show_notices' ) );
    }

    /* ───────────────────────────── Menu Registration ─────────────────────────── */

    public function register_menus() {
        add_menu_page(
            __( 'AtmoPress Weather', 'atmopress-weather' ),
            __( 'AtmoPress', 'atmopress-weather' ),
            'manage_options',
            'atmopress-weather',
            array( $this, 'page_getting_started' ),
            $this->menu_icon(),
            58
        );

        $pages = array(
            array( 'atmopress-weather',          __( 'Getting Started', 'atmopress-weather' ), array( $this, 'page_getting_started' ) ),
            array( 'atmopress-blocks',           __( 'Blocks',          'atmopress-weather' ), array( $this, 'page_blocks' ) ),
            array( 'atmopress-saved-templates',  __( 'Saved Templates', 'atmopress-weather' ), array( $this, 'page_saved_templates' ) ),
            array( 'atmopress-settings',         __( 'Settings',        'atmopress-weather' ), array( $this, 'page_settings' ) ),
            array( 'atmopress-manage',           __( 'Manage Weather',  'atmopress-weather' ), array( $this, 'page_manage' ) ),
            array( 'atmopress-add-new',          __( 'Add New Weather', 'atmopress-weather' ), array( $this, 'page_add_new' ) ),
            array( 'atmopress-tools',            __( 'Tools',           'atmopress-weather' ), array( $this, 'page_tools' ) ),
        );

        foreach ( $pages as $p ) {
            add_submenu_page( 'atmopress-weather', $p[1] . ' – AtmoPress', $p[1], 'manage_options', $p[0], $p[2] );
        }

        // Upgrade to Pro – special styling
        add_submenu_page(
            'atmopress-weather',
            __( 'Upgrade to Pro', 'atmopress-weather' ),
            '<span style="color:#f59e0b;font-weight:700;">⚡ ' . __( 'Upgrade to Pro', 'atmopress-weather' ) . '</span>',
            'manage_options',
            'atmopress-upgrade',
            array( $this, 'page_upgrade' )
        );
    }

    private function menu_icon() {
        return 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#a7aaad" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/></svg>' );
    }

    /* ───────────────────────────── Notices ─────────────────────────────────── */

    public function maybe_show_notices() {
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'atmopress' ) === false ) {
            return;
        }
        $api_key = Settings::get( 'api_key' );
        if ( empty( $api_key ) ) {
            echo '<div class="notice notice-warning atmopress-notice is-dismissible"><p>';
            printf(
                wp_kses(
                    __( '<strong>AtmoPress:</strong> No API key configured. <a href="%s">Go to Settings →</a>', 'atmopress-weather' ),
                    array( 'strong' => array(), 'a' => array( 'href' => array() ) )
                ),
                esc_url( admin_url( 'admin.php?page=atmopress-settings' ) )
            );
            echo '</p></div>';
        }
    }

    /* ═══════════════════════════════════════════════════════════════════════════
       PAGES
    ═══════════════════════════════════════════════════════════════════════════ */

    /* ───────────────────────────── Getting Started ─────────────────────────── */

    public function page_getting_started() {
        $api_key = Settings::get( 'api_key' );
        ?>
        <div class="wrap ap-admin">
            <?php $this->page_header( __( 'Getting Started', 'atmopress-weather' ), __( 'Welcome to AtmoPress Weather Plugin', 'atmopress-weather' ) ); ?>

            <div class="ap-steps">
                <div class="ap-step <?php echo $api_key ? 'ap-step--done' : 'ap-step--active'; ?>">
                    <div class="ap-step-num"><?php echo $api_key ? '✓' : '1'; ?></div>
                    <div class="ap-step-body">
                        <h3><?php esc_html_e( 'Configure API Key', 'atmopress-weather' ); ?></h3>
                        <p><?php esc_html_e( 'Get a free API key from OpenWeatherMap or WeatherAPI.com and enter it in Settings.', 'atmopress-weather' ); ?></p>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=atmopress-settings' ) ); ?>" class="button button-primary">
                            <?php echo $api_key ? esc_html__( 'View Settings', 'atmopress-weather' ) : esc_html__( 'Go to Settings', 'atmopress-weather' ); ?>
                        </a>
                    </div>
                </div>
                <div class="ap-step">
                    <div class="ap-step-num">2</div>
                    <div class="ap-step-body">
                        <h3><?php esc_html_e( 'Add a Weather Widget', 'atmopress-weather' ); ?></h3>
                        <p><?php esc_html_e( 'Create your first weather widget with your preferred template and location.', 'atmopress-weather' ); ?></p>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=atmopress-add-new' ) ); ?>" class="button button-secondary">
                            <?php esc_html_e( 'Add New Weather', 'atmopress-weather' ); ?>
                        </a>
                    </div>
                </div>
                <div class="ap-step">
                    <div class="ap-step-num">3</div>
                    <div class="ap-step-body">
                        <h3><?php esc_html_e( 'Embed Anywhere', 'atmopress-weather' ); ?></h3>
                        <p><?php esc_html_e( 'Use the Gutenberg block or shortcode [atmopress] to display weather on any page or post.', 'atmopress-weather' ); ?></p>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=atmopress-blocks' ) ); ?>" class="button button-secondary">
                            <?php esc_html_e( 'View Shortcodes & Blocks', 'atmopress-weather' ); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="ap-grid ap-grid--3 ap-mt">
                <div class="ap-card ap-card--feature">
                    <div class="ap-feature-icon">🌡️</div>
                    <h3><?php esc_html_e( '5 Templates', 'atmopress-weather' ); ?></h3>
                    <p><?php esc_html_e( 'Classic Card, Modern Grid, Minimal Strip, Dark Immersive and more.', 'atmopress-weather' ); ?></p>
                </div>
                <div class="ap-card ap-card--feature">
                    <div class="ap-feature-icon">📊</div>
                    <h3><?php esc_html_e( 'Full Forecast', 'atmopress-weather' ); ?></h3>
                    <p><?php esc_html_e( 'Hourly, 7-day and extended forecasts with wind, humidity, pressure and more.', 'atmopress-weather' ); ?></p>
                </div>
                <div class="ap-card ap-card--feature">
                    <div class="ap-feature-icon">⚡</div>
                    <h3><?php esc_html_e( 'Gutenberg Ready', 'atmopress-weather' ); ?></h3>
                    <p><?php esc_html_e( 'Native block with live preview, full controls and server-side rendering.', 'atmopress-weather' ); ?></p>
                </div>
                <div class="ap-card ap-card--feature">
                    <div class="ap-feature-icon">📍</div>
                    <h3><?php esc_html_e( 'Auto Location', 'atmopress-weather' ); ?></h3>
                    <p><?php esc_html_e( 'Browser geolocation or IP detection. Manual city search built in.', 'atmopress-weather' ); ?></p>
                </div>
                <div class="ap-card ap-card--feature">
                    <div class="ap-feature-icon">🎨</div>
                    <h3><?php esc_html_e( 'Full Customization', 'atmopress-weather' ); ?></h3>
                    <p><?php esc_html_e( 'Colors, typography, border radius, spacing — all controllable per widget.', 'atmopress-weather' ); ?></p>
                </div>
                <div class="ap-card ap-card--feature">
                    <div class="ap-feature-icon">🔌</div>
                    <h3><?php esc_html_e( 'Dual API Support', 'atmopress-weather' ); ?></h3>
                    <p><?php esc_html_e( 'Works with OpenWeatherMap and WeatherAPI.com, both have generous free tiers.', 'atmopress-weather' ); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    /* ───────────────────────────── Blocks Page ─────────────────────────────── */

    public function page_blocks() {
        $templates = TemplateLoader::registered();
        ?>
        <div class="wrap ap-admin">
            <?php $this->page_header( __( 'Blocks & Shortcodes', 'atmopress-weather' ), __( 'Embed weather anywhere using blocks or shortcodes', 'atmopress-weather' ) ); ?>

            <div class="ap-grid ap-grid--2 ap-mt">
                <div class="ap-card">
                    <h2 class="ap-card-title">
                        <span class="dashicons dashicons-layout"></span>
                        <?php esc_html_e( 'Gutenberg Block', 'atmopress-weather' ); ?>
                    </h2>
                    <p><?php esc_html_e( 'In the Block Editor, click + and search for "AtmoPress Weather". The block gives you live preview and full sidebar controls.', 'atmopress-weather' ); ?></p>
                    <div class="ap-code-block"><code>Search: "AtmoPress Weather" in block inserter</code></div>
                </div>
                <div class="ap-card">
                    <h2 class="ap-card-title">
                        <span class="dashicons dashicons-shortcode"></span>
                        <?php esc_html_e( 'Basic Shortcode', 'atmopress-weather' ); ?>
                    </h2>
                    <p><?php esc_html_e( 'Paste this shortcode into any post, page, or widget.', 'atmopress-weather' ); ?></p>
                    <div class="ap-code-block"><code>[atmopress location="London" template="template-1"]</code></div>
                </div>
            </div>

            <div class="ap-card ap-mt">
                <h2 class="ap-card-title"><?php esc_html_e( 'All Shortcode Parameters', 'atmopress-weather' ); ?></h2>
                <div class="ap-param-table-wrap">
                <table class="ap-param-table widefat">
                    <thead><tr><th><?php esc_html_e( 'Parameter', 'atmopress-weather' ); ?></th><th><?php esc_html_e( 'Values', 'atmopress-weather' ); ?></th><th><?php esc_html_e( 'Default', 'atmopress-weather' ); ?></th><th><?php esc_html_e( 'Description', 'atmopress-weather' ); ?></th></tr></thead>
                    <tbody>
                        <?php $rows = array(
                            array( 'template',         'template-1 … template-4', 'template-1', __( 'Visual template', 'atmopress-weather' ) ),
                            array( 'location',         __( 'City name or lat,lon', 'atmopress-weather' ), 'London', __( 'Weather location', 'atmopress-weather' ) ),
                            array( 'units',            'metric, imperial', 'metric', __( '°C or °F', 'atmopress-weather' ) ),
                            array( 'forecast_days',    '1–7', '7', __( 'Number of daily forecast days', 'atmopress-weather' ) ),
                            array( 'hourly_count',     '1–24', '8', __( 'Hourly forecast slots shown', 'atmopress-weather' ) ),
                            array( 'show_search',      'true, false', 'true', __( 'Show city search bar', 'atmopress-weather' ) ),
                            array( 'show_geolocation', 'true, false', 'true', __( 'Show detect-location button', 'atmopress-weather' ) ),
                            array( 'show_humidity',    'true, false', 'true', __( 'Show humidity stat', 'atmopress-weather' ) ),
                            array( 'show_wind',        'true, false', 'true', __( 'Show wind stat', 'atmopress-weather' ) ),
                            array( 'show_pressure',    'true, false', 'true', __( 'Show pressure stat', 'atmopress-weather' ) ),
                            array( 'show_sunrise',     'true, false', 'true', __( 'Show sunrise/sunset', 'atmopress-weather' ) ),
                            array( 'show_hourly',      'true, false', 'true', __( 'Show hourly forecast', 'atmopress-weather' ) ),
                            array( 'show_daily',       'true, false', 'true', __( 'Show daily forecast', 'atmopress-weather' ) ),
                            array( 'color_primary',    '#hex', '#2563eb', __( 'Primary theme color', 'atmopress-weather' ) ),
                            array( 'color_bg',         '#hex', '#ffffff', __( 'Widget background color', 'atmopress-weather' ) ),
                            array( 'border_radius',    '0–32', '16', __( 'Corner radius in px', 'atmopress-weather' ) ),
                            array( 'custom_class',     __( 'CSS class', 'atmopress-weather' ), '', __( 'Add custom CSS class', 'atmopress-weather' ) ),
                        );
                        foreach ( $rows as $row ) : ?>
                        <tr>
                            <td><code><?php echo esc_html( $row[0] ); ?></code></td>
                            <td><small><?php echo esc_html( $row[1] ); ?></small></td>
                            <td><code><?php echo esc_html( $row[2] ); ?></code></td>
                            <td><?php echo esc_html( $row[3] ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>

            <div class="ap-card ap-mt">
                <h2 class="ap-card-title"><?php esc_html_e( 'Template Previews', 'atmopress-weather' ); ?></h2>
                <div class="ap-template-grid">
                    <?php foreach ( $templates as $slug => $tpl ) : ?>
                    <div class="ap-template-tile ap-template-tile--<?php echo esc_attr( $tpl['preview'] ); ?>">
                        <div class="ap-template-tile-inner">
                            <span class="ap-template-tile-name"><?php echo esc_html( $tpl['label'] ); ?></span>
                        </div>
                        <div class="ap-template-tile-foot">
                            <code>[atmopress template="<?php echo esc_attr( $slug ); ?>"]</code>
                            <button class="ap-copy-btn button button-small" data-copy='[atmopress template="<?php echo esc_attr( $slug ); ?>"]'>
                                <?php esc_html_e( 'Copy', 'atmopress-weather' ); ?>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /* ───────────────────────────── Saved Templates ──────────────────────────── */

    public function page_saved_templates() {
        $saved = get_option( 'atmopress_saved_templates', array() );

        if ( isset( $_POST['ap_save_template'] ) && check_admin_referer( 'ap_save_template' ) ) {
            $name   = sanitize_text_field( $_POST['tpl_name'] ?? '' );
            $config = json_decode( stripslashes( $_POST['tpl_config'] ?? '{}' ), true ) ?: array();
            if ( $name ) {
                $saved[ sanitize_key( $name ) ] = array( 'name' => $name, 'config' => $config, 'created' => time() );
                update_option( 'atmopress_saved_templates', $saved );
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Template saved!', 'atmopress-weather' ) . '</p></div>';
            }
        }

        if ( isset( $_GET['delete_tpl'] ) && check_admin_referer( 'ap_delete_tpl' ) ) {
            $key = sanitize_key( $_GET['delete_tpl'] );
            unset( $saved[ $key ] );
            update_option( 'atmopress_saved_templates', $saved );
            $saved = get_option( 'atmopress_saved_templates', array() );
        }
        ?>
        <div class="wrap ap-admin">
            <?php $this->page_header( __( 'Saved Templates', 'atmopress-weather' ), __( 'Save and reuse widget configurations', 'atmopress-weather' ) ); ?>

            <div class="ap-grid ap-grid--2 ap-mt">
                <div class="ap-card">
                    <h2 class="ap-card-title"><?php esc_html_e( 'Save New Template', 'atmopress-weather' ); ?></h2>
                    <form method="post">
                        <?php wp_nonce_field( 'ap_save_template' ); ?>
                        <div class="ap-field">
                            <label><?php esc_html_e( 'Template Name', 'atmopress-weather' ); ?></label>
                            <input type="text" name="tpl_name" placeholder="<?php esc_attr_e( 'e.g. My Blue Card', 'atmopress-weather' ); ?>" class="regular-text" required />
                        </div>
                        <div class="ap-field">
                            <label><?php esc_html_e( 'Configuration JSON', 'atmopress-weather' ); ?></label>
                            <textarea name="tpl_config" rows="5" class="large-text code" placeholder='{"template":"template-1","color_primary":"#2563eb","location":"London"}'></textarea>
                            <p class="description"><?php esc_html_e( 'Paste any widget config JSON — copy it from the shortcode generator.', 'atmopress-weather' ); ?></p>
                        </div>
                        <input type="hidden" name="ap_save_template" value="1" />
                        <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Template', 'atmopress-weather' ); ?></button>
                    </form>
                </div>

                <div class="ap-card">
                    <h2 class="ap-card-title"><?php esc_html_e( 'My Saved Templates', 'atmopress-weather' ); ?></h2>
                    <?php if ( empty( $saved ) ) : ?>
                        <div class="ap-empty-state">
                            <span class="dashicons dashicons-saved" style="font-size:48px;color:#cbd5e1;"></span>
                            <p><?php esc_html_e( 'No saved templates yet. Save a configuration to reuse it later.', 'atmopress-weather' ); ?></p>
                        </div>
                    <?php else : ?>
                    <div class="ap-saved-list">
                        <?php foreach ( $saved as $key => $tpl ) : ?>
                        <div class="ap-saved-item">
                            <div class="ap-saved-info">
                                <strong><?php echo esc_html( $tpl['name'] ); ?></strong>
                                <small><?php echo esc_html( human_time_diff( $tpl['created'], time() ) . ' ' . __( 'ago', 'atmopress-weather' ) ); ?></small>
                            </div>
                            <div class="ap-saved-actions">
                                <button class="button button-small ap-copy-btn" data-copy='[atmopress <?php echo esc_attr( $this->config_to_attrs( $tpl['config'] ) ); ?>]'>
                                    <?php esc_html_e( 'Copy SC', 'atmopress-weather' ); ?>
                                </button>
                                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=atmopress-saved-templates&delete_tpl=' . $key ), 'ap_delete_tpl' ) ); ?>"
                                   class="button button-small ap-delete-btn" onclick="return confirm('Delete this template?')">
                                    <?php esc_html_e( 'Delete', 'atmopress-weather' ); ?>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /* ───────────────────────────── Settings ─────────────────────────────────── */

    public function page_settings() {
        $saved = false;
        if ( isset( $_POST['ap_settings_save'] ) && check_admin_referer( 'ap_settings_save' ) ) {
            Settings::save( $_POST );
            $saved = true;
        }
        $s         = Settings::get_all();
        $templates = TemplateLoader::registered();
        $active    = $_GET['tab'] ?? 'general';
        ?>
        <div class="wrap ap-admin">
            <?php $this->page_header( __( 'Settings', 'atmopress-weather' ), __( 'Configure your weather plugin', 'atmopress-weather' ) ); ?>

            <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved successfully!', 'atmopress-weather' ); ?></p></div>
            <?php endif; ?>

            <div class="ap-tabs-bar">
                <?php
                $tabs = array(
                    'general'  => array( 'dashicons-admin-settings', __( 'General',  'atmopress-weather' ) ),
                    'display'  => array( 'dashicons-visibility',     __( 'Display',  'atmopress-weather' ) ),
                    'template' => array( 'dashicons-layout',         __( 'Template', 'atmopress-weather' ) ),
                    'api'      => array( 'dashicons-cloud',          __( 'API',      'atmopress-weather' ) ),
                    'advanced' => array( 'dashicons-admin-tools',    __( 'Advanced', 'atmopress-weather' ) ),
                );
                foreach ( $tabs as $key => $tab ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=atmopress-settings&tab=' . $key ) ); ?>"
                   class="ap-tab <?php echo $active === $key ? 'ap-tab--active' : ''; ?>">
                    <span class="dashicons <?php echo esc_attr( $tab[0] ); ?>"></span>
                    <?php echo esc_html( $tab[1] ); ?>
                </a>
                <?php endforeach; ?>
            </div>

            <form method="post" action="" class="ap-settings-form">
                <?php wp_nonce_field( 'ap_settings_save' ); ?>
                <input type="hidden" name="ap_settings_save" value="1" />

                <!-- ── GENERAL TAB ── -->
                <?php if ( 'general' === $active ) : ?>
                <div class="ap-section">
                    <div class="ap-section-head">
                        <h2><?php esc_html_e( 'General Settings', 'atmopress-weather' ); ?></h2>
                        <p><?php esc_html_e( 'Core plugin behaviour and defaults.', 'atmopress-weather' ); ?></p>
                    </div>
                    <div class="ap-section-body">
                        <?php $this->field_text( 'default_location', __( 'Default Location', 'atmopress-weather' ), $s['default_location'], __( 'City name shown when no location is set on a widget (e.g. London)', 'atmopress-weather' ), 'London' ); ?>
                        <?php $this->field_select( 'units', __( 'Temperature Units', 'atmopress-weather' ), $s['units'], array( 'metric' => __( 'Metric (°C, m/s)', 'atmopress-weather' ), 'imperial' => __( 'Imperial (°F, mph)', 'atmopress-weather' ) ), __( 'Default unit system for all widgets.', 'atmopress-weather' ) ); ?>
                        <?php $this->field_number( 'cache_duration', __( 'Cache Duration (seconds)', 'atmopress-weather' ), $s['cache_duration'], __( 'How long to cache weather data. Set 0 to disable. Recommended: 600–1800.', 'atmopress-weather' ), 0, 86400, 60 ); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ── DISPLAY TAB ── -->
                <?php if ( 'display' === $active ) : ?>
                <div class="ap-section">
                    <div class="ap-section-head">
                        <h2><?php esc_html_e( 'Display Settings', 'atmopress-weather' ); ?></h2>
                        <p><?php esc_html_e( 'Control which weather elements are shown by default.', 'atmopress-weather' ); ?></p>
                    </div>
                    <div class="ap-section-body">
                        <div class="ap-toggle-grid">
                            <?php
                            $toggles = array(
                                'show_search'      => __( 'City Search Bar',    'atmopress-weather' ),
                                'show_geolocation' => __( 'Auto-detect Location', 'atmopress-weather' ),
                                'show_temperature' => __( 'Temperature',        'atmopress-weather' ),
                                'show_humidity'    => __( 'Humidity',           'atmopress-weather' ),
                                'show_wind'        => __( 'Wind Speed & Direction', 'atmopress-weather' ),
                                'show_pressure'    => __( 'Atmospheric Pressure', 'atmopress-weather' ),
                                'show_visibility'  => __( 'Visibility',         'atmopress-weather' ),
                                'show_feels_like'  => __( 'Feels Like',         'atmopress-weather' ),
                                'show_sunrise'     => __( 'Sunrise & Sunset',   'atmopress-weather' ),
                                'show_hourly'      => __( 'Hourly Forecast',    'atmopress-weather' ),
                                'show_daily'       => __( 'Daily Forecast',     'atmopress-weather' ),
                            );
                            foreach ( $toggles as $key => $label ) :
                                $this->field_toggle( $key, $label, $s[ $key ] );
                            endforeach; ?>
                        </div>
                        <div class="ap-divider"></div>
                        <?php $this->field_number( 'forecast_days', __( 'Forecast Days to Show', 'atmopress-weather' ), $s['forecast_days'], __( 'Number of daily forecast rows (1–7).', 'atmopress-weather' ), 1, 7 ); ?>
                        <?php $this->field_number( 'hourly_count', __( 'Hourly Forecast Slots', 'atmopress-weather' ), $s['hourly_count'], __( 'Number of hourly entries (1–24).', 'atmopress-weather' ), 1, 24 ); ?>
                        <?php $this->field_select( 'forecast_style', __( 'Forecast Layout Style', 'atmopress-weather' ), $s['forecast_style'], array( 'list' => __( 'List Rows', 'atmopress-weather' ), 'grid' => __( 'Grid Cards', 'atmopress-weather' ), 'compact' => __( 'Compact', 'atmopress-weather' ) ) ); ?>
                        <?php $this->field_select( 'icon_style', __( 'Weather Icon Style', 'atmopress-weather' ), $s['icon_style'], array( 'owm' => __( 'OpenWeatherMap Icons', 'atmopress-weather' ), 'flat' => __( 'Flat Emoji Icons', 'atmopress-weather' ) ) ); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ── TEMPLATE TAB ── -->
                <?php if ( 'template' === $active ) : ?>
                <div class="ap-section">
                    <div class="ap-section-head">
                        <h2><?php esc_html_e( 'Template & Style Settings', 'atmopress-weather' ); ?></h2>
                        <p><?php esc_html_e( 'Default visual template and style values for all widgets.', 'atmopress-weather' ); ?></p>
                    </div>
                    <div class="ap-section-body">
                        <div class="ap-template-selector">
                            <label class="ap-field-label"><?php esc_html_e( 'Default Template', 'atmopress-weather' ); ?></label>
                            <div class="ap-template-choices">
                                <?php foreach ( $templates as $slug => $tpl ) : ?>
                                <label class="ap-template-choice <?php echo $s['default_template'] === $slug ? 'ap-template-choice--active' : ''; ?>">
                                    <input type="radio" name="default_template" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $s['default_template'], $slug ); ?> />
                                    <div class="ap-template-thumb ap-template-thumb--<?php echo esc_attr( $tpl['preview'] ); ?>">
                                        <span>☁️</span>
                                    </div>
                                    <span class="ap-template-choice-label"><?php echo esc_html( $tpl['label'] ); ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="ap-divider"></div>
                        <div class="ap-color-grid">
                            <?php $this->field_color( 'color_primary', __( 'Primary Color',    'atmopress-weather' ), $s['color_primary'] ); ?>
                            <?php $this->field_color( 'color_bg',      __( 'Background Color', 'atmopress-weather' ), $s['color_bg'] ); ?>
                            <?php $this->field_color( 'color_text',    __( 'Text Color',       'atmopress-weather' ), $s['color_text'] ); ?>
                            <?php $this->field_color( 'color_accent',  __( 'Accent Color',     'atmopress-weather' ), $s['color_accent'] ); ?>
                        </div>
                        <div class="ap-divider"></div>
                        <?php $this->field_number( 'border_radius', __( 'Border Radius (px)', 'atmopress-weather' ), $s['border_radius'], '', 0, 32 ); ?>
                        <?php $this->field_number( 'card_spacing',  __( 'Card Spacing (px)',  'atmopress-weather' ), $s['card_spacing'],  '', 0, 40 ); ?>
                        <?php $this->field_number( 'font_size',     __( 'Base Font Size (px)', 'atmopress-weather' ), $s['font_size'],     '', 10, 24 ); ?>
                        <?php $this->field_select( 'font_family', __( 'Font Family', 'atmopress-weather' ), $s['font_family'], array(
                            'inherit'                                        => __( 'Inherit from Theme', 'atmopress-weather' ),
                            '-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif' => 'System UI',
                            '"Inter",sans-serif'                             => 'Inter',
                            '"Roboto",sans-serif'                            => 'Roboto',
                            '"Poppins",sans-serif'                           => 'Poppins',
                            'Georgia,serif'                                  => 'Georgia',
                        ) ); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ── API TAB ── -->
                <?php if ( 'api' === $active ) : ?>
                <div class="ap-section">
                    <div class="ap-section-head">
                        <h2><?php esc_html_e( 'API Settings', 'atmopress-weather' ); ?></h2>
                        <p><?php esc_html_e( 'Connect to a weather data provider.', 'atmopress-weather' ); ?></p>
                    </div>
                    <div class="ap-section-body">
                        <?php $this->field_select( 'api_provider', __( 'Weather Provider', 'atmopress-weather' ), $s['api_provider'], array(
                            'openweathermap' => __( 'OpenWeatherMap (Recommended)', 'atmopress-weather' ),
                            'weatherapi'     => __( 'WeatherAPI.com', 'atmopress-weather' ),
                        ), __( 'Both providers offer a generous free tier. OpenWeatherMap gives current + 5-day forecast.', 'atmopress-weather' ) ); ?>

                        <div class="ap-field">
                            <label class="ap-field-label"><?php esc_html_e( 'OpenWeatherMap API Key', 'atmopress-weather' ); ?></label>
                            <div class="ap-key-row">
                                <input type="text" name="api_key" id="ap-api-key" value="<?php echo esc_attr( $s['api_key'] ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Paste your API key here…', 'atmopress-weather' ); ?>" />
                                <button type="button" id="ap-test-key" class="button button-secondary"><?php esc_html_e( 'Test Key', 'atmopress-weather' ); ?></button>
                            </div>
                            <div id="ap-test-result" class="ap-test-result"></div>
                            <p class="description">
                                <a href="https://home.openweathermap.org/api_keys" target="_blank" rel="noopener"><?php esc_html_e( 'Get a free OpenWeatherMap API key →', 'atmopress-weather' ); ?></a>
                                &nbsp;|&nbsp;
                                <a href="https://www.weatherapi.com/my/" target="_blank" rel="noopener"><?php esc_html_e( 'Get a free WeatherAPI.com key →', 'atmopress-weather' ); ?></a>
                            </p>
                        </div>

                        <?php if ( ! empty( $s['api_key'] ) ) : ?>
                        <div class="ap-api-status ap-api-status--ok">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e( 'API key is configured', 'atmopress-weather' ); ?>
                        </div>
                        <?php else : ?>
                        <div class="ap-api-status ap-api-status--warn">
                            <span class="dashicons dashicons-warning"></span>
                            <?php esc_html_e( 'No API key configured — weather widgets will show an error.', 'atmopress-weather' ); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ── ADVANCED TAB ── -->
                <?php if ( 'advanced' === $active ) : ?>
                <div class="ap-section">
                    <div class="ap-section-head">
                        <h2><?php esc_html_e( 'Advanced Settings', 'atmopress-weather' ); ?></h2>
                        <p><?php esc_html_e( 'Performance and developer options.', 'atmopress-weather' ); ?></p>
                    </div>
                    <div class="ap-section-body">
                        <div class="ap-toggle-grid">
                            <?php $this->field_toggle( 'disable_animations', __( 'Disable Widget Animations', 'atmopress-weather' ), $s['disable_animations'] ); ?>
                            <?php $this->field_toggle( 'lazy_load', __( 'Lazy Load Widgets (load on scroll)', 'atmopress-weather' ), $s['lazy_load'] ); ?>
                        </div>
                        <div class="ap-divider"></div>
                        <div class="ap-field">
                            <label class="ap-field-label"><?php esc_html_e( 'Custom CSS', 'atmopress-weather' ); ?></label>
                            <textarea name="custom_css" rows="10" class="large-text code ap-code-area"><?php echo esc_textarea( $s['custom_css'] ); ?></textarea>
                            <p class="description"><?php esc_html_e( 'Advanced: add custom CSS targeting .ap-widget elements.', 'atmopress-weather' ); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="ap-form-footer">
                    <button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'Save Settings', 'atmopress-weather' ); ?></button>
                </div>
            </form>
        </div>
        <?php
    }

    /* ───────────────────────────── Manage Weather ───────────────────────────── */

    public function page_manage() {
        $widgets = get_option( 'atmopress_widgets', array() );
        ?>
        <div class="wrap ap-admin">
            <?php $this->page_header( __( 'Manage Weather', 'atmopress-weather' ), __( 'All your weather widget instances', 'atmopress-weather' ) ); ?>

            <div class="ap-card ap-mt">
                <div class="ap-card-toolbar">
                    <h2 class="ap-card-title"><?php esc_html_e( 'Weather Widgets', 'atmopress-weather' ); ?></h2>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=atmopress-add-new' ) ); ?>" class="button button-primary">
                        <span class="dashicons dashicons-plus-alt2"></span>
                        <?php esc_html_e( 'Add New Widget', 'atmopress-weather' ); ?>
                    </a>
                </div>

                <?php if ( empty( $widgets ) ) : ?>
                <div class="ap-empty-state">
                    <span class="dashicons dashicons-cloud" style="font-size:64px;color:#cbd5e1;height:auto;width:auto;"></span>
                    <h3><?php esc_html_e( 'No widgets yet', 'atmopress-weather' ); ?></h3>
                    <p><?php esc_html_e( 'Create your first weather widget to get started.', 'atmopress-weather' ); ?></p>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=atmopress-add-new' ) ); ?>" class="button button-primary">
                        <?php esc_html_e( 'Create Widget', 'atmopress-weather' ); ?>
                    </a>
                </div>
                <?php else : ?>
                <table class="widefat striped ap-widgets-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Name', 'atmopress-weather' ); ?></th>
                            <th><?php esc_html_e( 'Location', 'atmopress-weather' ); ?></th>
                            <th><?php esc_html_e( 'Template', 'atmopress-weather' ); ?></th>
                            <th><?php esc_html_e( 'Shortcode', 'atmopress-weather' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'atmopress-weather' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $widgets as $id => $w ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $w['name'] ); ?></strong></td>
                            <td><?php echo esc_html( $w['config']['location'] ?? '—' ); ?></td>
                            <td><?php echo esc_html( $w['config']['template'] ?? '—' ); ?></td>
                            <td><code>[atmopress <?php echo esc_html( $this->config_to_attrs( $w['config'] ) ); ?>]</code></td>
                            <td>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=atmopress-add-new&edit=' . $id ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'atmopress-weather' ); ?></a>
                                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=atmopress-manage&delete=' . $id ), 'ap_delete_widget' ) ); ?>"
                                   class="button button-small ap-delete-btn" onclick="return confirm('Delete this widget?')"><?php esc_html_e( 'Delete', 'atmopress-weather' ); ?></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /* ───────────────────────────── Add New Widget ───────────────────────────── */

    public function page_add_new() {
        $widgets   = get_option( 'atmopress_widgets', array() );
        $edit_id   = sanitize_key( $_GET['edit'] ?? '' );
        $editing   = $edit_id && isset( $widgets[ $edit_id ] ) ? $widgets[ $edit_id ] : null;
        $templates = TemplateLoader::registered();
        $s         = Settings::get_all();
        $def       = TemplateLoader::default_config();

        if ( isset( $_POST['ap_save_widget'] ) && check_admin_referer( 'ap_save_widget' ) ) {
            $name = sanitize_text_field( $_POST['widget_name'] ?? 'Widget' );
            $cfg  = array(
                'template'      => sanitize_key( $_POST['template'] ?? 'template-1' ),
                'location'      => sanitize_text_field( $_POST['location'] ?? 'London' ),
                'units'         => in_array( $_POST['units'] ?? '', array( 'metric', 'imperial' ), true ) ? $_POST['units'] : 'metric',
                'forecast_days' => min( 7, max( 1, absint( $_POST['forecast_days'] ?? 7 ) ) ),
                'hourly_count'  => min( 24, max( 1, absint( $_POST['hourly_count'] ?? 8 ) ) ),
                'color_primary' => sanitize_hex_color( $_POST['color_primary'] ?? '' ) ?: '#2563eb',
                'color_bg'      => sanitize_hex_color( $_POST['color_bg'] ?? '' )      ?: '#ffffff',
                'border_radius' => absint( $_POST['border_radius'] ?? 16 ),
                'show_search'   => ! empty( $_POST['show_search'] ),
                'show_humidity' => ! empty( $_POST['show_humidity'] ),
                'show_wind'     => ! empty( $_POST['show_wind'] ),
                'show_pressure' => ! empty( $_POST['show_pressure'] ),
                'show_hourly'   => ! empty( $_POST['show_hourly'] ),
                'show_daily'    => ! empty( $_POST['show_daily'] ),
                'show_sunrise'  => ! empty( $_POST['show_sunrise'] ),
            );

            $id = $edit_id ?: 'widget_' . time();
            $widgets[ $id ] = array( 'name' => $name, 'config' => $cfg, 'created' => time() );
            update_option( 'atmopress_widgets', $widgets );

            echo '<div class="notice notice-success is-dismissible"><p>';
            esc_html_e( 'Widget saved!', 'atmopress-weather' );
            echo ' <a href="' . esc_url( admin_url( 'admin.php?page=atmopress-manage' ) ) . '">' . esc_html__( 'Manage all widgets →', 'atmopress-weather' ) . '</a>';
            echo '</p></div>';

            $editing = $widgets[ $id ];
            $edit_id = $id;
        }

        $c = $editing['config'] ?? $def;
        ?>
        <div class="wrap ap-admin">
            <?php $this->page_header(
                $editing ? __( 'Edit Widget', 'atmopress-weather' ) : __( 'Add New Weather Widget', 'atmopress-weather' ),
                __( 'Configure your widget settings and styling', 'atmopress-weather' )
            ); ?>

            <form method="post" class="ap-widget-form">
                <?php wp_nonce_field( 'ap_save_widget' ); ?>
                <input type="hidden" name="ap_save_widget" value="1" />

                <div class="ap-widget-builder">
                    <!-- Left: Controls -->
                    <div class="ap-builder-controls">

                        <div class="ap-card">
                            <h2 class="ap-card-title"><?php esc_html_e( 'Widget Info', 'atmopress-weather' ); ?></h2>
                            <?php $this->field_text( 'widget_name', __( 'Widget Name', 'atmopress-weather' ), $editing['name'] ?? 'My Weather Widget', '', 'My Weather Widget' ); ?>
                            <?php $this->field_text( 'location', __( 'Location', 'atmopress-weather' ), $c['location'] ?? 'London', __( 'City name or "lat,lon"', 'atmopress-weather' ), 'London' ); ?>
                            <?php $this->field_select( 'units', __( 'Units', 'atmopress-weather' ), $c['units'] ?? 'metric', array( 'metric' => __( 'Metric (°C)', 'atmopress-weather' ), 'imperial' => __( 'Imperial (°F)', 'atmopress-weather' ) ) ); ?>
                        </div>

                        <div class="ap-card ap-mt-sm">
                            <h2 class="ap-card-title"><?php esc_html_e( 'Select Template', 'atmopress-weather' ); ?></h2>
                            <div class="ap-template-choices ap-template-choices--sm">
                                <?php foreach ( $templates as $slug => $tpl ) : ?>
                                <label class="ap-template-choice <?php echo ( $c['template'] ?? 'template-1' ) === $slug ? 'ap-template-choice--active' : ''; ?>">
                                    <input type="radio" name="template" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $c['template'] ?? 'template-1', $slug ); ?> />
                                    <div class="ap-template-thumb ap-template-thumb--<?php echo esc_attr( $tpl['preview'] ); ?>"><span>☁️</span></div>
                                    <span class="ap-template-choice-label"><?php echo esc_html( $tpl['label'] ); ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="ap-card ap-mt-sm">
                            <h2 class="ap-card-title"><?php esc_html_e( 'Forecast Options', 'atmopress-weather' ); ?></h2>
                            <?php $this->field_number( 'forecast_days', __( 'Forecast Days (1–7)', 'atmopress-weather' ), $c['forecast_days'] ?? 7, '', 1, 7 ); ?>
                            <?php $this->field_number( 'hourly_count', __( 'Hourly Slots (1–24)', 'atmopress-weather' ), $c['hourly_count'] ?? 8, '', 1, 24 ); ?>
                        </div>

                        <div class="ap-card ap-mt-sm">
                            <h2 class="ap-card-title"><?php esc_html_e( 'Show / Hide Elements', 'atmopress-weather' ); ?></h2>
                            <div class="ap-toggle-grid">
                                <?php
                                $element_toggles = array(
                                    'show_search'   => __( 'Search Bar',      'atmopress-weather' ),
                                    'show_humidity' => __( 'Humidity',         'atmopress-weather' ),
                                    'show_wind'     => __( 'Wind',             'atmopress-weather' ),
                                    'show_pressure' => __( 'Pressure',         'atmopress-weather' ),
                                    'show_sunrise'  => __( 'Sunrise / Sunset', 'atmopress-weather' ),
                                    'show_hourly'   => __( 'Hourly Forecast',  'atmopress-weather' ),
                                    'show_daily'    => __( 'Daily Forecast',   'atmopress-weather' ),
                                );
                                foreach ( $element_toggles as $key => $label ) :
                                    $this->field_toggle( $key, $label, $c[ $key ] ?? true );
                                endforeach; ?>
                            </div>
                        </div>

                        <div class="ap-card ap-mt-sm">
                            <h2 class="ap-card-title"><?php esc_html_e( 'Style', 'atmopress-weather' ); ?></h2>
                            <div class="ap-color-grid ap-color-grid--sm">
                                <?php $this->field_color( 'color_primary', __( 'Primary Color', 'atmopress-weather' ), $c['color_primary'] ?? '#2563eb' ); ?>
                                <?php $this->field_color( 'color_bg',      __( 'Background',    'atmopress-weather' ), $c['color_bg']      ?? '#ffffff' ); ?>
                            </div>
                            <?php $this->field_number( 'border_radius', __( 'Border Radius (px)', 'atmopress-weather' ), $c['border_radius'] ?? 16, '', 0, 32 ); ?>
                        </div>

                        <div class="ap-form-footer">
                            <button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'Save Widget', 'atmopress-weather' ); ?></button>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=atmopress-manage' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'atmopress-weather' ); ?></a>
                        </div>
                    </div>

                    <!-- Right: Shortcode preview -->
                    <div class="ap-builder-preview">
                        <div class="ap-card">
                            <h2 class="ap-card-title"><?php esc_html_e( 'Generated Shortcode', 'atmopress-weather' ); ?></h2>
                            <div class="ap-generated-sc">
                                <code id="ap-generated-sc">[atmopress template="<?php echo esc_attr( $c['template'] ?? 'template-1' ); ?>" location="<?php echo esc_attr( $c['location'] ?? 'London' ); ?>"]</code>
                            </div>
                            <button type="button" id="ap-copy-sc" class="button button-secondary ap-mt-sm"><?php esc_html_e( 'Copy Shortcode', 'atmopress-weather' ); ?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    /* ───────────────────────────── Tools ────────────────────────────────────── */

    public function page_tools() {
        ?>
        <div class="wrap ap-admin">
            <?php $this->page_header( __( 'Tools', 'atmopress-weather' ), __( 'Maintenance and developer utilities', 'atmopress-weather' ) ); ?>

            <div class="ap-grid ap-grid--2 ap-mt">
                <div class="ap-card">
                    <h2 class="ap-card-title">
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e( 'Cache Management', 'atmopress-weather' ); ?>
                    </h2>
                    <p><?php esc_html_e( 'Flush all cached weather data. The next widget load will fetch fresh data from the API.', 'atmopress-weather' ); ?></p>
                    <button type="button" id="ap-flush-cache" class="button button-secondary"><?php esc_html_e( 'Flush All Weather Cache', 'atmopress-weather' ); ?></button>
                    <div id="ap-flush-result" class="ap-mt-sm"></div>
                </div>

                <div class="ap-card">
                    <h2 class="ap-card-title">
                        <span class="dashicons dashicons-database-export"></span>
                        <?php esc_html_e( 'Export Settings', 'atmopress-weather' ); ?>
                    </h2>
                    <p><?php esc_html_e( 'Export your AtmoPress settings as a JSON file for backup or migration.', 'atmopress-weather' ); ?></p>
                    <button type="button" id="ap-export-settings" class="button button-secondary"><?php esc_html_e( 'Export Settings', 'atmopress-weather' ); ?></button>
                    <textarea id="ap-export-output" class="large-text code ap-mt-sm" rows="4" readonly style="display:none;"><?php echo esc_textarea( wp_json_encode( Settings::get_all(), JSON_PRETTY_PRINT ) ); ?></textarea>
                </div>

                <div class="ap-card">
                    <h2 class="ap-card-title">
                        <span class="dashicons dashicons-database-import"></span>
                        <?php esc_html_e( 'Import Settings', 'atmopress-weather' ); ?>
                    </h2>
                    <p><?php esc_html_e( 'Paste a previously exported JSON to restore settings.', 'atmopress-weather' ); ?></p>
                    <textarea id="ap-import-input" class="large-text code" rows="5" placeholder='{"api_key":"…"}'></textarea>
                    <button type="button" id="ap-import-settings" class="button button-secondary ap-mt-sm"><?php esc_html_e( 'Import & Save', 'atmopress-weather' ); ?></button>
                    <div id="ap-import-result" class="ap-mt-sm"></div>
                </div>

                <div class="ap-card">
                    <h2 class="ap-card-title">
                        <span class="dashicons dashicons-sos"></span>
                        <?php esc_html_e( 'System Information', 'atmopress-weather' ); ?>
                    </h2>
                    <table class="widefat ap-sysinfo">
                        <tr><td><strong><?php esc_html_e( 'Plugin Version', 'atmopress-weather' ); ?></strong></td><td><?php echo esc_html( ATMOPRESS_VERSION ); ?></td></tr>
                        <tr><td><strong><?php esc_html_e( 'WordPress Version', 'atmopress-weather' ); ?></strong></td><td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td></tr>
                        <tr><td><strong><?php esc_html_e( 'PHP Version', 'atmopress-weather' ); ?></strong></td><td><?php echo esc_html( PHP_VERSION ); ?></td></tr>
                        <tr><td><strong><?php esc_html_e( 'API Provider', 'atmopress-weather' ); ?></strong></td><td><?php echo esc_html( Settings::get( 'api_provider' ) ); ?></td></tr>
                        <tr><td><strong><?php esc_html_e( 'API Key Set', 'atmopress-weather' ); ?></strong></td><td><?php echo Settings::get( 'api_key' ) ? '<span style="color:#16a34a;">✓ Yes</span>' : '<span style="color:#dc2626;">✗ No</span>'; ?></td></tr>
                        <tr><td><strong><?php esc_html_e( 'Cache Duration', 'atmopress-weather' ); ?></strong></td><td><?php echo esc_html( Settings::get( 'cache_duration' ) . 's' ); ?></td></tr>
                        <tr><td><strong><?php esc_html_e( 'REST API', 'atmopress-weather' ); ?></strong></td><td><?php echo esc_url( rest_url( 'atmopress/v1/' ) ); ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }

    /* ───────────────────────────── Upgrade to Pro ───────────────────────────── */

    public function page_upgrade() {
        ?>
        <div class="wrap ap-admin">
            <?php $this->page_header( __( 'Upgrade to Pro', 'atmopress-weather' ), '' ); ?>

            <div class="ap-pro-banner">
                <div class="ap-pro-badge">⚡ PRO</div>
                <h2><?php esc_html_e( 'All Features Are Already Free!', 'atmopress-weather' ); ?></h2>
                <p class="ap-pro-lead"><?php esc_html_e( 'AtmoPress Weather includes every feature — templates, forecasts, custom styles, multi-widget support — completely free. No Pro tier needed.', 'atmopress-weather' ); ?></p>
            </div>

            <div class="ap-grid ap-grid--3 ap-mt">
                <?php
                $features = array(
                    array( '✅', __( '5 Templates', 'atmopress-weather' ),              __( 'Classic Card, Modern Grid, Minimal, Dark, and more.', 'atmopress-weather' ) ),
                    array( '✅', __( 'Gutenberg Block', 'atmopress-weather' ),          __( 'Native block with live preview and full sidebar controls.', 'atmopress-weather' ) ),
                    array( '✅', __( '7-Day Forecast', 'atmopress-weather' ),           __( 'Daily forecasts with min/max temps and weather icons.', 'atmopress-weather' ) ),
                    array( '✅', __( 'Hourly Forecast', 'atmopress-weather' ),          __( 'Up to 24-hour forecast strip.', 'atmopress-weather' ) ),
                    array( '✅', __( 'Auto Location', 'atmopress-weather' ),            __( 'Browser geolocation detect with one click.', 'atmopress-weather' ) ),
                    array( '✅', __( 'Custom Colors', 'atmopress-weather' ),            __( 'Per-widget primary, background, text, and accent colors.', 'atmopress-weather' ) ),
                    array( '✅', __( 'Typography Control', 'atmopress-weather' ),       __( 'Font family, size, and spacing controls.', 'atmopress-weather' ) ),
                    array( '✅', __( 'Multiple Widgets', 'atmopress-weather' ),         __( 'Create unlimited independent widget instances.', 'atmopress-weather' ) ),
                    array( '✅', __( 'REST API', 'atmopress-weather' ),                 __( 'Full JSON API for headless/custom integrations.', 'atmopress-weather' ) ),
                    array( '✅', __( 'Dual API Providers', 'atmopress-weather' ),       __( 'OpenWeatherMap and WeatherAPI.com support.', 'atmopress-weather' ) ),
                    array( '✅', __( 'Smart Caching', 'atmopress-weather' ),            __( 'Transient-based caching to minimise API calls.', 'atmopress-weather' ) ),
                    array( '✅', __( 'Show/Hide Toggles', 'atmopress-weather' ),        __( 'Per-widget control of every data element.', 'atmopress-weather' ) ),
                );
                foreach ( $features as $f ) : ?>
                <div class="ap-card ap-card--pro-feature">
                    <div class="ap-pro-icon"><?php echo esc_html( $f[0] ); ?></div>
                    <h3><?php echo esc_html( $f[1] ); ?></h3>
                    <p><?php echo esc_html( $f[2] ); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /* ═══════════════════════════════════════════════════════════════════════════
       SHARED COMPONENTS
    ═══════════════════════════════════════════════════════════════════════════ */

    private function page_header( $title, $subtitle = '' ) {
        ?>
        <div class="ap-page-header">
            <div class="ap-page-header-text">
                <h1 class="ap-page-title">
                    <span class="dashicons dashicons-cloud ap-page-icon"></span>
                    <?php echo esc_html( $title ); ?>
                </h1>
                <?php if ( $subtitle ) : ?>
                <p class="ap-page-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                <?php endif; ?>
            </div>
            <div class="ap-page-header-meta">
                <span class="ap-version-badge">v<?php echo esc_html( ATMOPRESS_VERSION ); ?></span>
            </div>
        </div>
        <?php
    }

    /* ───────────────────────── Field Helpers ───────────────────────────────── */

    private function field_text( $name, $label, $value, $desc = '', $placeholder = '' ) {
        ?>
        <div class="ap-field">
            <label class="ap-field-label" for="ap-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
            <input type="text" id="ap-<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" class="regular-text" />
            <?php if ( $desc ) : ?><p class="description"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
        </div>
        <?php
    }

    private function field_number( $name, $label, $value, $desc = '', $min = 0, $max = 9999, $step = 1 ) {
        ?>
        <div class="ap-field">
            <label class="ap-field-label" for="ap-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
            <input type="number" id="ap-<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" step="<?php echo esc_attr( $step ); ?>" />
            <?php if ( $desc ) : ?><p class="description"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
        </div>
        <?php
    }

    private function field_select( $name, $label, $value, $options, $desc = '' ) {
        ?>
        <div class="ap-field">
            <label class="ap-field-label" for="ap-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
            <select id="ap-<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>">
                <?php foreach ( $options as $k => $v ) : ?>
                <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $value, $k ); ?>><?php echo esc_html( $v ); ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ( $desc ) : ?><p class="description"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
        </div>
        <?php
    }

    private function field_toggle( $name, $label, $checked ) {
        ?>
        <label class="ap-toggle">
            <input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $checked ); ?> />
            <span class="ap-toggle-slider"></span>
            <span class="ap-toggle-label"><?php echo esc_html( $label ); ?></span>
        </label>
        <?php
    }

    private function field_color( $name, $label, $value ) {
        ?>
        <div class="ap-color-field">
            <label class="ap-field-label"><?php echo esc_html( $label ); ?></label>
            <div class="ap-color-input-wrap">
                <input type="color" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="ap-color-picker" />
                <input type="text" name="<?php echo esc_attr( $name ); ?>_hex" value="<?php echo esc_attr( $value ); ?>" class="ap-color-hex" maxlength="7" />
            </div>
        </div>
        <?php
    }

    /* ───────────────────────── Utility ─────────────────────────────────────── */

    private function config_to_attrs( $config ) {
        $skip = array( 'color_text', 'color_accent', 'card_spacing', 'font_size', 'font_family', 'icon_style', 'forecast_style', 'custom_class' );
        $parts = array();
        foreach ( $config as $k => $v ) {
            if ( in_array( $k, $skip, true ) ) continue;
            if ( is_bool( $v ) ) { if ( ! $v ) { $parts[] = $k . '="false"'; } continue; }
            $parts[] = $k . '="' . esc_attr( $v ) . '"';
        }
        return implode( ' ', $parts );
    }
}
