<?php
/**
 * Admin Notices Handler
 *
 * Handles admin-facing notices including review requests and
 * other important messages.
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
 * Class AdminNotices
 *
 * Manages admin notices and dismissals.
 *
 * @since 1.0.0
 */
class AdminNotices {

    /**
     * Review notice option name.
     */
    const REVIEW_NOTICE_OPTION = 'pearl_weather_review_notice';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_notices', array( $this, 'render_admin_notices' ) );
        add_action( 'wp_ajax_pearl_weather_dismiss_review_notice', array( $this, 'dismiss_review_notice' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Enqueue admin assets.
     *
     * @since 1.0.0
     * @param string $hook Current page hook.
     */
    public function enqueue_assets( $hook ) {
        // Only load on plugin pages.
        if ( ! in_array( $hook, array( 'edit.php', 'post.php', 'post-new.php' ), true ) ) {
            return;
        }

        wp_enqueue_style( 'pearl-weather-admin' );
    }

    /**
     * Render all admin notices.
     *
     * @since 1.0.0
     */
    public function render_admin_notices() {
        $this->render_review_notice();
    }

    /**
     * Render review notice.
     *
     * @since 1.0.0
     */
    private function render_review_notice() {
        // Only show to users who can manage options.
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $notice_data = get_option( self::REVIEW_NOTICE_OPTION, array() );
        $now = time();

        // Initialize notice data if not exists.
        if ( empty( $notice_data ) ) {
            $notice_data = array(
                'time'      => $now,
                'dismissed' => false,
                'dismiss_type' => null,
            );
            update_option( self::REVIEW_NOTICE_OPTION, $notice_data );
            return;
        }

        // Check if notice should be shown.
        $should_show = false;

        if ( ! $notice_data['dismissed'] ) {
            $install_time = isset( $notice_data['time'] ) ? $notice_data['time'] : $now;
            $days_passed = ( $now - $install_time ) / DAY_IN_SECONDS;

            // Show after 3 days if not dismissed.
            if ( $days_passed >= 3 ) {
                $should_show = true;
            }
        }

        if ( ! $should_show ) {
            return;
        }

        $plugin_name = __( 'Pearl Weather', 'pearl-weather' );
        $review_url = 'https://wordpress.org/support/plugin/pearl-weather/reviews/';
        $nonce = wp_create_nonce( 'pw_review_notice' );

        ?>
        <div id="pw-review-notice" class="notice notice-info is-dismissible pw-review-notice">
            <div class="pw-notice-icon">
                <img src="<?php echo esc_url( PEARL_WEATHER_ASSETS_URL . 'images/plugin-icon.png' ); ?>" 
                     alt="<?php echo esc_attr( $plugin_name ); ?>"
                     width="50" height="50">
            </div>
            <div class="pw-notice-content">
                <h3><?php printf( esc_html__( 'Enjoying %s?', 'pearl-weather' ), '<strong>' . esc_html( $plugin_name ) . '</strong>' ); ?></h3>
                <p>
                    <?php
                    printf(
                        /* translators: %s: plugin name */
                        esc_html__( 'We hope you had a wonderful experience using %s. Please take a moment to leave a review on WordPress.org. Your positive review will help us improve. Thank you! 😊', 'pearl-weather' ),
                        '<strong>' . esc_html( $plugin_name ) . '</strong>'
                    );
                    ?>
                </p>
                <div class="pw-notice-actions">
                    <a href="<?php echo esc_url( $review_url ); ?>" 
                       class="button button-primary pw-rate-now" 
                       target="_blank"
                       data-dismiss-type="rated">
                        <?php esc_html_e( 'Ok, you deserve ★★★★★', 'pearl-weather' ); ?>
                    </a>
                    <a href="#" 
                       class="pw-remind-later" 
                       data-dismiss-type="remind"
                       data-nonce="<?php echo esc_attr( $nonce ); ?>">
                        <span class="dashicons dashicons-clock"></span>
                        <?php esc_html_e( 'Nope, maybe later', 'pearl-weather' ); ?>
                    </a>
                    <a href="#" 
                       class="pw-never-show" 
                       data-dismiss-type="never"
                       data-nonce="<?php echo esc_attr( $nonce ); ?>">
                        <span class="dashicons dashicons-dismiss"></span>
                        <?php esc_html_e( 'Never show again', 'pearl-weather' ); ?>
                    </a>
                </div>
            </div>
        </div>

        <style>
            .pw-review-notice {
                display: flex;
                align-items: center;
                padding: 12px;
            }
            .pw-notice-icon {
                margin-right: 16px;
                flex-shrink: 0;
            }
            .pw-notice-icon img {
                border-radius: 8px;
            }
            .pw-notice-content {
                flex: 1;
            }
            .pw-notice-content h3 {
                margin: 0 0 8px 0;
                font-size: 16px;
            }
            .pw-notice-content p {
                margin: 0 0 12px 0;
            }
            .pw-notice-actions {
                display: flex;
                gap: 12px;
                align-items: center;
                flex-wrap: wrap;
            }
            .pw-notice-actions a {
                text-decoration: none;
            }
            .pw-remind-later,
            .pw-never-show {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                color: #555;
            }
            .pw-remind-later:hover,
            .pw-never-show:hover {
                color: #0073aa;
            }
        </style>

        <script>
        (function($) {
            $(document).ready(function() {
                const notice = $('#pw-review-notice');
                if (!notice.length) return;

                function dismissNotice(dismissType) {
                    const nonce = notice.find('.pw-remind-later').data('nonce') || 
                                   notice.find('.pw-never-show').data('nonce');
                    
                    $.post(ajaxurl, {
                        action: 'pearl_weather_dismiss_review_notice',
                        dismiss_type: dismissType,
                        nonce: nonce
                    }).done(function() {
                        notice.fadeOut(300, function() {
                            notice.remove();
                        });
                    });
                }

                notice.find('.pw-rate-now').on('click', function() {
                    dismissNotice('rated');
                });

                notice.find('.pw-remind-later').on('click', function(e) {
                    e.preventDefault();
                    dismissNotice('remind');
                });

                notice.find('.pw-never-show').on('click', function(e) {
                    e.preventDefault();
                    dismissNotice('never');
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Dismiss review notice via AJAX.
     *
     * @since 1.0.0
     */
    public function dismiss_review_notice() {
        // Check nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'pw_review_notice' ) ) {
            wp_die( 'Invalid nonce', 'pearl-weather', array( 'response' => 403 ) );
        }

        // Check user capability.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized', 'pearl-weather', array( 'response' => 403 ) );
        }

        $dismiss_type = isset( $_POST['dismiss_type'] ) ? sanitize_text_field( wp_unslash( $_POST['dismiss_type'] ) ) : 'never';

        $notice_data = get_option( self::REVIEW_NOTICE_OPTION, array() );
        
        if ( empty( $notice_data ) ) {
            $notice_data = array();
        }

        $notice_data['time'] = time();
        $notice_data['dismiss_type'] = $dismiss_type;

        switch ( $dismiss_type ) {
            case 'rated':
            case 'never':
                $notice_data['dismissed'] = true;
                break;
            case 'remind':
                $notice_data['dismissed'] = false;
                break;
            default:
                $notice_data['dismissed'] = true;
                break;
        }

        update_option( self::REVIEW_NOTICE_OPTION, $notice_data );

        wp_die();
    }

    /**
     * Reset review notice (for testing or after plugin update).
     *
     * @since 1.0.0
     */
    public static function reset_review_notice() {
        delete_option( self::REVIEW_NOTICE_OPTION );
    }

    /**
     * Display a custom admin notice.
     *
     * @since 1.0.0
     * @param string $message Notice message.
     * @param string $type    Notice type ('success', 'error', 'warning', 'info').
     * @param bool   $is_dismissible Whether the notice is dismissible.
     */
    public static function add_notice( $message, $type = 'info', $is_dismissible = true ) {
        $notices = get_option( 'pearl_weather_admin_notices', array() );
        
        $notices[] = array(
            'message' => $message,
            'type' => $type,
            'dismissible' => $is_dismissible,
            'time' => time(),
        );
        
        update_option( 'pearl_weather_admin_notices', $notices );
    }

    /**
     * Display all queued admin notices.
     *
     * @since 1.0.0
     */
    public static function display_queued_notices() {
        $notices = get_option( 'pearl_weather_admin_notices', array() );
        
        if ( empty( $notices ) ) {
            return;
        }
        
        foreach ( $notices as $notice ) {
            $class = 'notice notice-' . $notice['type'];
            if ( $notice['dismissible'] ) {
                $class .= ' is-dismissible';
            }
            ?>
            <div class="<?php echo esc_attr( $class ); ?>">
                <p><?php echo wp_kses_post( $notice['message'] ); ?></p>
            </div>
            <?php
        }
        
        delete_option( 'pearl_weather_admin_notices' );
    }
}