<?php
/**
 * Help & Resources Page
 *
 * Displays help documentation, recommended plugins, and lite vs pro comparison.
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
 * Class HelpPage
 *
 * Manages the plugin help and resources page.
 *
 * @since 1.0.0
 */
class HelpPage {

    /**
     * Singleton instance.
     *
     * @var HelpPage
     */
    private static $instance = null;

    /**
     * Page slugs.
     *
     * @var array
     */
    private $page_slugs = array( 'pw_help' );

    /**
     * Get singleton instance.
     *
     * @since 1.0.0
     * @return HelpPage
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_pages' ), 80 );
        add_action( 'admin_print_scripts', array( $this, 'remove_admin_notices' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Enqueue admin assets for help page.
     *
     * @since 1.0.0
     * @param string $hook Current page hook.
     */
    public function enqueue_assets( $hook ) {
        if ( 'location-weather_page_pw_help' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'pearl-weather-admin' );
        wp_enqueue_script( 'pearl-weather-admin' );
        add_thickbox();
    }

    /**
     * Add admin menu pages.
     *
     * @since 1.0.0
     */
    public function add_menu_pages() {
        add_submenu_page(
            'edit.php?post_type=pearl_weather_widget',
            __( 'Help & Resources', 'pearl-weather' ),
            __( 'Help', 'pearl-weather' ),
            'manage_options',
            'pw_help',
            array( $this, 'render_help_page' )
        );
    }

    /**
     * Remove admin notices on help page.
     *
     * @since 1.0.0
     */
    public function remove_admin_notices() {
        global $wp_filter;

        if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $this->page_slugs, true ) ) {
            // Remove all admin notices.
            remove_all_actions( 'admin_notices' );
            remove_all_actions( 'user_admin_notices' );
            remove_all_actions( 'all_admin_notices' );
        }
    }

    /**
     * Render the help page.
     *
     * @since 1.0.0
     */
    public function render_help_page() {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'getting-started';
        ?>
        <div class="pw-help-page">
            <div class="pw-help-header">
                <div class="pw-help-header-inner">
                    <div class="pw-help-logo">
                        <img src="<?php echo esc_url( PEARL_WEATHER_ASSETS_URL . 'images/logo.svg' ); ?>" alt="Pearl Weather">
                        <span class="pw-version"><?php echo esc_html( PEARL_WEATHER_VERSION ); ?></span>
                    </div>
                    <div class="pw-help-upgrade">
                        <a href="https://pearlweather.com/pricing/" target="_blank" class="button button-primary">
                            <?php esc_html_e( 'Upgrade to Pro', 'pearl-weather' ); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="pw-help-nav">
                <div class="pw-help-nav-inner">
                    <ul>
                        <li><a href="<?php echo esc_url( $this->get_tab_url( 'getting-started' ) ); ?>" class="<?php echo $active_tab === 'getting-started' ? 'active' : ''; ?>"><?php esc_html_e( 'Getting Started', 'pearl-weather' ); ?></a></li>
                        <li><a href="<?php echo esc_url( $this->get_tab_url( 'lite-vs-pro' ) ); ?>" class="<?php echo $active_tab === 'lite-vs-pro' ? 'active' : ''; ?>"><?php esc_html_e( 'Lite vs Pro', 'pearl-weather' ); ?></a></li>
                        <li><a href="<?php echo esc_url( $this->get_tab_url( 'recommended' ) ); ?>" class="<?php echo $active_tab === 'recommended' ? 'active' : ''; ?>"><?php esc_html_e( 'Recommended Plugins', 'pearl-weather' ); ?></a></li>
                        <li><a href="<?php echo esc_url( $this->get_tab_url( 'about' ) ); ?>" class="<?php echo $active_tab === 'about' ? 'active' : ''; ?>"><?php esc_html_e( 'About Us', 'pearl-weather' ); ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="pw-help-content">
                <?php
                switch ( $active_tab ) {
                    case 'lite-vs-pro':
                        $this->render_lite_vs_pro_tab();
                        break;
                    case 'recommended':
                        $this->render_recommended_tab();
                        break;
                    case 'about':
                        $this->render_about_tab();
                        break;
                    default:
                        $this->render_getting_started_tab();
                        break;
                }
                ?>
            </div>

            <div class="pw-help-footer">
                <p>
                    <?php esc_html_e( 'Made with ❤️ by the Pearl Weather Team', 'pearl-weather' ); ?>
                </p>
            </div>
        </div>

        <style>
            .pw-help-page {
                background: #f1f1f1;
                min-height: 100vh;
            }
            .pw-help-header {
                background: #fff;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .pw-help-header-inner {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .pw-help-logo {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .pw-help-logo img {
                height: 40px;
            }
            .pw-version {
                background: #f0f0f0;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
            }
            .pw-help-nav {
                background: #fff;
                border-bottom: 1px solid #ddd;
            }
            .pw-help-nav-inner {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 20px;
            }
            .pw-help-nav ul {
                display: flex;
                list-style: none;
                margin: 0;
                padding: 0;
            }
            .pw-help-nav li {
                margin: 0;
            }
            .pw-help-nav a {
                display: block;
                padding: 15px 20px;
                text-decoration: none;
                color: #333;
                border-bottom: 2px solid transparent;
            }
            .pw-help-nav a.active {
                border-bottom-color: #f26c0d;
                color: #f26c0d;
            }
            .pw-help-content {
                max-width: 1200px;
                margin: 40px auto;
                padding: 0 20px;
            }
            .pw-help-footer {
                text-align: center;
                padding: 30px;
                background: #fff;
                border-top: 1px solid #ddd;
                color: #666;
            }
        </style>
        <?php
    }

    /**
     * Get tab URL.
     *
     * @since 1.0.0
     * @param string $tab Tab slug.
     * @return string
     */
    private function get_tab_url( $tab ) {
        return add_query_arg( array(
            'page' => 'pw_help',
            'tab'  => $tab,
        ), admin_url( 'edit.php?post_type=pearl_weather_widget' ) );
    }

    /**
     * Render Getting Started tab.
     *
     * @since 1.0.0
     */
    private function render_getting_started_tab() {
        ?>
        <div class="pw-getting-started">
            <div class="pw-video-section">
                <h2><?php esc_html_e( 'Welcome to Pearl Weather!', 'pearl-weather' ); ?></h2>
                <p><?php esc_html_e( 'Watch this video to get started with the plugin.', 'pearl-weather' ); ?></p>
                <iframe width="724" height="405" src="https://www.youtube.com/embed/example" frameborder="0" allowfullscreen></iframe>
                <div class="pw-action-buttons">
                    <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=pearl_weather_widget' ) ); ?>" class="button button-primary">
                        <?php esc_html_e( 'Create Weather Widget', 'pearl-weather' ); ?>
                    </a>
                    <a href="https://pearlweather.com/demo/" target="_blank" class="button">
                        <?php esc_html_e( 'Live Demo', 'pearl-weather' ); ?>
                    </a>
                    <a href="https://pearlweather.com/docs/" target="_blank" class="button">
                        <?php esc_html_e( 'Documentation', 'pearl-weather' ); ?>
                    </a>
                </div>
            </div>
            <div class="pw-sidebar">
                <div class="pw-sidebar-box">
                    <h4><?php esc_html_e( 'Need Support?', 'pearl-weather' ); ?></h4>
                    <p><?php esc_html_e( 'Get help from our support team.', 'pearl-weather' ); ?></p>
                    <a href="https://pearlweather.com/support/" target="_blank" class="button">
                        <?php esc_html_e( 'Submit Ticket', 'pearl-weather' ); ?>
                    </a>
                </div>
            </div>
        </div>
        <style>
            .pw-getting-started {
                display: grid;
                grid-template-columns: 2fr 1fr;
                gap: 30px;
            }
            .pw-action-buttons {
                display: flex;
                gap: 12px;
                margin-top: 20px;
            }
            .pw-sidebar-box {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            @media (max-width: 768px) {
                .pw-getting-started {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <?php
    }

    /**
     * Render Lite vs Pro tab.
     *
     * @since 1.0.0
     */
    private function render_lite_vs_pro_tab() {
        ?>
        <div class="pw-lite-vs-pro">
            <div class="pw-comparison-table">
                <h2><?php esc_html_e( 'Lite vs Pro Comparison', 'pearl-weather' ); ?></h2>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Feature', 'pearl-weather' ); ?></th>
                            <th><?php esc_html_e( 'Lite', 'pearl-weather' ); ?></th>
                            <th><?php esc_html_e( 'Pro', 'pearl-weather' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><?php esc_html_e( 'Weather Layouts', 'pearl-weather' ); ?></td><td>2</td><td><strong>15+</strong></td></tr>
                        <tr><td><?php esc_html_e( 'Weather Maps', 'pearl-weather' ); ?></td><td>❌</td><td>✅</td></tr>
                        <tr><td><?php esc_html_e( 'Daily Forecast (16 days)', 'pearl-weather' ); ?></td><td>❌</td><td>✅</td></tr>
                        <tr><td><?php esc_html_e( 'Hourly Forecast (120 hours)', 'pearl-weather' ); ?></td><td>❌</td><td>✅</td></tr>
                        <tr><td><?php esc_html_e( 'Air Quality Index', 'pearl-weather' ); ?></td><td>❌</td><td>✅</td></tr>
                        <tr><td><?php esc_html_e( 'Weather Alerts', 'pearl-weather' ); ?></td><td>❌</td><td>✅</td></tr>
                        <tr><td><?php esc_html_e( 'Priority Support', 'pearl-weather' ); ?></td><td>❌</td><td>✅</td></tr>
                    </tbody>
                </table>

                <div class="pw-upgrade-cta">
                    <a href="https://pearlweather.com/pricing/" target="_blank" class="button button-primary button-hero">
                        <?php esc_html_e( 'Upgrade to Pro Now', 'pearl-weather' ); ?>
                    </a>
                </div>
            </div>
        </div>
        <style>
            .pw-upgrade-cta {
                text-align: center;
                margin-top: 30px;
            }
            .pw-upgrade-cta .button-hero {
                font-size: 18px;
                padding: 12px 30px;
                height: auto;
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
            <h2><?php esc_html_e( 'Recommended Plugins', 'pearl-weather' ); ?></h2>
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
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
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
     * Render About Us tab.
     *
     * @since 1.0.0
     */
    private function render_about_tab() {
        ?>
        <div class="pw-about-us">
            <div class="pw-about-content">
                <h2><?php esc_html_e( 'About Pearl Weather', 'pearl-weather' ); ?></h2>
                <p><?php esc_html_e( 'Pearl Weather is a powerful WordPress weather plugin that helps you display real-time weather information on your website. With beautiful layouts and extensive customization options, you can create stunning weather displays that match your brand.', 'pearl-weather' ); ?></p>
                <p><?php esc_html_e( 'Our mission is to provide the easiest and most convenient way to display weather forecasts on WordPress websites.', 'pearl-weather' ); ?></p>
                <div class="pw-about-buttons">
                    <a href="https://pearlweather.com/" target="_blank" class="button button-primary">
                        <?php esc_html_e( 'Learn More', 'pearl-weather' ); ?>
                    </a>
                    <a href="https://shapedplugin.com/about-us/" target="_blank" class="button">
                        <?php esc_html_e( 'About The Team', 'pearl-weather' ); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get recommended plugins from WordPress.org API.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_recommended_plugins() {
        $cached = get_transient( 'pw_recommended_plugins' );
        
        if ( false !== $cached ) {
            return $cached;
        }

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
}

// Initialize help page.
HelpPage::get_instance();