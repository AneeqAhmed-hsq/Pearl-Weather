<?php
/**
 * Admin Initialization
 *
 * Handles all admin functionality for the Pearl Weather plugin,
 * including custom post type columns, shortcode display, and admin components.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Admin
 * @since      1.0.0
 */

namespace PearlWeather;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Admin
 *
 * Initializes and manages all admin components of the plugin.
 *
 * @since 1.0.0
 */
class Admin {

    /**
     * Custom post type name.
     *
     * @var string
     */
    private $post_type = 'pearl_weather_widget';

    /**
     * Instance of this class.
     *
     * @var Admin
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @since 1.0.0
     * @return Admin
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     * Initializes admin components and hooks.
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->init_components();
        $this->init_hooks();
    }

    /**
     * Initialize all admin components.
     *
     * @since 1.0.0
     */
    private function init_components() {
        // Load custom post type handler.
        if ( class_exists( 'PearlWeather\Admin\PostType' ) ) {
            new Admin\PostType();
        }

        // Load admin notices handler.
        if ( class_exists( 'PearlWeather\Admin\AdminNotices' ) ) {
            new Admin\AdminNotices();
        }

        // Load scripts/styles handler.
        if ( class_exists( 'PearlWeather\Admin\AssetsLoader' ) ) {
            new Admin\AssetsLoader();
        }

        // Load settings page.
        if ( class_exists( 'PearlWeather\Admin\SettingsPage' ) ) {
            new Admin\SettingsPage();
        }

        // Load widget handler.
        if ( class_exists( 'PearlWeather\Admin\WidgetManager' ) ) {
            new Admin\WidgetManager();
        }

        // Load import/export handler.
        if ( class_exists( 'PearlWeather\Admin\ImportExport' ) ) {
            new Admin\ImportExport();
        }

        // Load preview handler if in preview context.
        if ( $this->is_preview_context() && class_exists( 'PearlWeather\Admin\PreviewHandler' ) ) {
            new Admin\PreviewHandler();
        }
    }

    /**
     * Check if current request is for preview.
     *
     * @since 1.0.0
     * @return bool
     */
    private function is_preview_context() {
        return isset( $_GET['preview'] ) || isset( $_GET['preview_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

    /**
     * Initialize WordPress hooks.
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Custom post type columns.
        add_filter( "manage_{$this->post_type}_posts_columns", array( $this, 'add_shortcode_column' ) );
        add_action( "manage_{$this->post_type}_posts_custom_column", array( $this, 'render_custom_column' ), 10, 2 );

        // Custom post update messages.
        add_filter( 'post_updated_messages', array( $this, 'custom_update_messages' ) );

        // Add admin footer script for copy functionality.
        add_action( 'admin_footer', array( $this, 'add_copy_script' ) );
    }

    /**
     * Add custom columns to the post listing table.
     *
     * @since 1.0.0
     * @param array $columns Existing columns.
     * @return array Modified columns.
     */
    public function add_shortcode_column( $columns ) {
        $new_columns = array();

        // Reorder columns with shortcode column after title.
        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;

            if ( 'title' === $key ) {
                $new_columns['shortcode'] = esc_html__( 'Shortcode', 'pearl-weather' );
                $new_columns['layout']    = esc_html__( 'Layout', 'pearl-weather' );
            }
        }

        /**
         * Filter the admin columns for weather widget post type.
         *
         * @since 1.0.0
         * @param array $new_columns Modified columns.
         */
        return apply_filters( 'pearl_weather_admin_columns', $new_columns );
    }

    /**
     * Render custom column content.
     *
     * @since 1.0.0
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     */
    public function render_custom_column( $column, $post_id ) {
        switch ( $column ) {
            case 'shortcode':
                $this->render_shortcode_column( $post_id );
                break;

            case 'layout':
                $this->render_layout_column( $post_id );
                break;

            default:
                /**
                 * Action to render custom column content.
                 *
                 * @since 1.0.0
                 * @param int    $post_id Post ID.
                 * @param string $column  Column name.
                 */
                do_action( 'pearl_weather_render_custom_column', $post_id, $column );
                break;
        }
    }

    /**
     * Render shortcode column with copyable input.
     *
     * @since 1.0.0
     * @param int $post_id Post ID.
     */
    private function render_shortcode_column( $post_id ) {
        $shortcode = sprintf( '[pearl-weather id="%d"]', $post_id );
        $copy_text = esc_html__( 'Shortcode Copied to Clipboard!', 'pearl-weather' );

        ?>
        <div class="pearl-weather-shortcode-wrapper">
            <div class="pearl-weather-copy-notice" style="display:none;">
                <span class="success-notice">✓ <?php echo esc_html( $copy_text ); ?></span>
            </div>
            <input 
                type="text" 
                class="pearl-weather-shortcode-input" 
                value="<?php echo esc_attr( $shortcode ); ?>" 
                readonly 
                onclick="this.select();"
                style="width:220px; padding:8px 10px; font-family:monospace; cursor:pointer; border:1px solid #ccd0d4; border-radius:4px;"
            />
        </div>
        <?php
    }

    /**
     * Render layout column content.
     *
     * @since 1.0.0
     * @param int $post_id Post ID.
     */
    private function render_layout_column( $post_id ) {
        $layout_data = get_post_meta( $post_id, 'pearl_weather_layout', true );
        
        if ( ! is_array( $layout_data ) ) {
            $layout_data = array();
        }

        $layout = isset( $layout_data['weather_view'] ) ? $layout_data['weather_view'] : 'default';
        
        // Format layout name for display.
        $layout_name = ucwords( str_replace( array( '-', '_' ), ' ', $layout ) );
        
        /**
         * Filter the layout display name.
         *
         * @since 1.0.0
         * @param string $layout_name Display name.
         * @param string $layout      Raw layout value.
         * @param int    $post_id     Post ID.
         */
        echo esc_html( apply_filters( 'pearl_weather_layout_display_name', $layout_name, $layout, $post_id ) );
    }

    /**
     * Add JavaScript for copy functionality in admin footer.
     *
     * @since 1.0.0
     */
    public function add_copy_script() {
        global $current_screen;
        
        // Only load on our post type listing page.
        if ( ! isset( $current_screen->post_type ) || $this->post_type !== $current_screen->post_type ) {
            return;
        }
        ?>
        <script type="text/javascript">
        (function($) {
            'use strict';
            
            $(document).ready(function() {
                $('.pearl-weather-shortcode-input').on('click', function() {
                    var $input = $(this);
                    var $wrapper = $input.closest('.pearl-weather-shortcode-wrapper');
                    var $notice = $wrapper.find('.pearl-weather-copy-notice');
                    
                    // Select and copy text.
                    $input.select();
                    document.execCommand('copy');
                    
                    // Show success notice.
                    $notice.show().fadeOut(2000);
                });
            });
        })(jQuery);
        </script>
        <style type="text/css">
            .pearl-weather-copy-notice {
                position: absolute;
                background: #00a32a;
                color: #fff;
                padding: 4px 10px;
                border-radius: 4px;
                font-size: 12px;
                margin-top: -30px;
                margin-left: 10px;
                z-index: 10;
            }
            .pearl-weather-shortcode-wrapper {
                position: relative;
                display: inline-block;
            }
            .pearl-weather-shortcode-input:hover {
                background-color: #f8f8f8;
            }
        </style>
        <?php
    }

    /**
     * Customize post update and publish messages.
     *
     * @since 1.0.0
     * @param array $messages Existing messages.
     * @return array Modified messages.
     */
    public function custom_update_messages( $messages ) {
        $messages[ $this->post_type ] = array(
            0  => '', // Unused.
            1  => esc_html__( 'Weather widget updated.', 'pearl-weather' ),
            2  => esc_html__( 'Custom field updated.', 'pearl-weather' ),
            3  => esc_html__( 'Custom field deleted.', 'pearl-weather' ),
            4  => esc_html__( 'Weather widget updated.', 'pearl-weather' ),
            5  => isset( $_GET['revision'] ) ? sprintf(
                /* translators: %s: Revision title */
                esc_html__( 'Weather widget restored to revision from %s.', 'pearl-weather' ),
                wp_post_revision_title( (int) $_GET['revision'], false ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            ) : false,
            6  => esc_html__( 'Weather widget published.', 'pearl-weather' ),
            7  => esc_html__( 'Weather widget saved.', 'pearl-weather' ),
            8  => esc_html__( 'Weather widget submitted.', 'pearl-weather' ),
            9  => sprintf(
                /* translators: %s: Scheduled date */
                esc_html__( 'Weather widget scheduled for: %s.', 'pearl-weather' ),
                '<strong>' . date_i18n( __( 'M j, Y @ G:i', 'pearl-weather' ), strtotime( get_post()->post_date ) ) . '</strong>'
            ),
            10 => esc_html__( 'Weather widget draft updated.', 'pearl-weather' ),
        );

        return $messages;
    }
}

// Initialize the admin.
if ( ! function_exists( 'pearl_weather_admin_init' ) ) {
    /**
     * Initialize admin components.
     *
     * @since 1.0.0
     * @return Admin
     */
    function pearl_weather_admin_init() {
        return Admin::get_instance();
    }
    pearl_weather_admin_init();
}