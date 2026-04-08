<?php
/**
 * Offer Banner Handler
 *
 * Displays seasonal offer banners in the WordPress admin area.
 * Supports multiple offer periods with dismiss functionality.
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
 * Class OfferBannerManager
 *
 * Manages display and dismissal of promotional offer banners.
 *
 * @since 1.0.0
 */
class OfferBannerManager {

    /**
     * Singleton instance.
     *
     * @var OfferBannerManager
     */
    private static $instance = null;

    /**
     * Option prefix for dismissed banners.
     */
    const DISMISS_OPTION_PREFIX = 'pw_offer_banner_dismissed_';

    /**
     * AJAX action for dismissal.
     */
    const DISMISS_ACTION = 'pw_dismiss_offer_banner';

    /**
     * Get singleton instance.
     *
     * @since 1.0.0
     * @return OfferBannerManager
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
        add_action( 'admin_notices', array( $this, 'render_banners' ) );
        add_action( 'wp_ajax_' . self::DISMISS_ACTION, array( $this, 'dismiss_banner' ) );
    }

    /**
     * Get active offers based on current date.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_active_offers() {
        $now = current_time( 'timestamp' );

        $offers = array(
            'black_friday' => array(
                'id'    => 'black_friday',
                'name'  => __( 'Black Friday Sale', 'pearl-weather' ),
                'start' => strtotime( date( 'Y-m-d', strtotime( 'November 18 this year' ) ) . ' 00:00:00' ),
                'end'   => strtotime( date( 'Y-m-d', strtotime( 'December 6 this year' ) ) . ' 23:59:59' ),
                'image' => PEARL_WEATHER_ASSETS_URL . 'images/offers/black-friday.svg',
                'url'   => 'https://pearlweather.com/pricing/?utm_source=plugin&utm_medium=banner&utm_campaign=black_friday',
            ),
            'cyber_monday' => array(
                'id'    => 'cyber_monday',
                'name'  => __( 'Cyber Monday Deal', 'pearl-weather' ),
                'start' => strtotime( date( 'Y-m-d', strtotime( 'November 29 this year' ) ) . ' 00:00:00' ),
                'end'   => strtotime( date( 'Y-m-d', strtotime( 'December 6 this year' ) ) . ' 23:59:59' ),
                'image' => PEARL_WEATHER_ASSETS_URL . 'images/offers/cyber-monday.svg',
                'url'   => 'https://pearlweather.com/pricing/?utm_source=plugin&utm_medium=banner&utm_campaign=cyber_monday',
            ),
            'new_year' => array(
                'id'    => 'new_year',
                'name'  => __( 'New Year Sale', 'pearl-weather' ),
                'start' => strtotime( date( 'Y-m-d', strtotime( 'December 26 this year' ) ) . ' 00:00:00' ),
                'end'   => strtotime( date( 'Y-m-d', strtotime( 'January 10 next year' ) ) . ' 23:59:59' ),
                'image' => PEARL_WEATHER_ASSETS_URL . 'images/offers/new-year.svg',
                'url'   => 'https://pearlweather.com/pricing/?utm_source=plugin&utm_medium=banner&utm_campaign=new_year',
            ),
            'summer_sale' => array(
                'id'    => 'summer_sale',
                'name'  => __( 'Summer Sale', 'pearl-weather' ),
                'start' => strtotime( date( 'Y-m-d', strtotime( 'June 1 this year' ) ) . ' 00:00:00' ),
                'end'   => strtotime( date( 'Y-m-d', strtotime( 'July 15 this year' ) ) . ' 23:59:59' ),
                'image' => PEARL_WEATHER_ASSETS_URL . 'images/offers/summer-sale.svg',
                'url'   => 'https://pearlweather.com/pricing/?utm_source=plugin&utm_medium=banner&utm_campaign=summer_sale',
            ),
            'halloween' => array(
                'id'    => 'halloween',
                'name'  => __( 'Halloween Sale', 'pearl-weather' ),
                'start' => strtotime( date( 'Y-m-d', strtotime( 'October 20 this year' ) ) . ' 00:00:00' ),
                'end'   => strtotime( date( 'Y-m-d', strtotime( 'November 5 this year' ) ) . ' 23:59:59' ),
                'image' => PEARL_WEATHER_ASSETS_URL . 'images/offers/halloween.svg',
                'url'   => 'https://pearlweather.com/pricing/?utm_source=plugin&utm_medium=banner&utm_campaign=halloween',
            ),
        );

        $active_offers = array();

        foreach ( $offers as $key => $offer ) {
            // Skip if banner is dismissed.
            if ( get_option( self::DISMISS_OPTION_PREFIX . $offer['id'] ) ) {
                continue;
            }

            if ( $now >= $offer['start'] && $now <= $offer['end'] ) {
                $active_offers[ $key ] = $offer;
            }
        }

        return $active_offers;
    }

    /**
     * Render offer banners.
     *
     * @since 1.0.0
     */
    public function render_banners() {
        // Only show to administrators.
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Only show on plugin-related pages.
        $screen = get_current_screen();
        if ( ! $screen || ! in_array( $screen->post_type, array( 'pearl_weather_widget', 'pearl_weather_template' ), true ) ) {
            return;
        }

        $active_offers = $this->get_active_offers();

        if ( empty( $active_offers ) ) {
            return;
        }

        foreach ( $active_offers as $offer ) {
            $this->render_single_banner( $offer );
        }

        $this->render_dismiss_script();
    }

    /**
     * Render a single offer banner.
     *
     * @since 1.0.0
     * @param array $offer Offer data.
     */
    private function render_single_banner( $offer ) {
        $nonce = wp_create_nonce( self::DISMISS_ACTION );
        ?>
        <div id="pw-offer-banner-<?php echo esc_attr( $offer['id'] ); ?>" 
             class="notice notice-info is-dismissible pw-offer-banner">
            <a href="<?php echo esc_url( $offer['url'] ); ?>" 
               target="_blank" 
               rel="noopener noreferrer">
                <img src="<?php echo esc_url( $offer['image'] ); ?>" 
                     alt="<?php echo esc_attr( $offer['name'] ); ?>"
                     style="width:100%; height:auto; display:block;">
            </a>
            <button type="button" 
                    class="notice-dismiss pw-offer-dismiss" 
                    data-offer-id="<?php echo esc_attr( $offer['id'] ); ?>"
                    data-nonce="<?php echo esc_attr( $nonce ); ?>">
                <span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'pearl-weather' ); ?></span>
            </button>
        </div>
        <?php
    }

    /**
     * Render dismiss script.
     *
     * @since 1.0.0
     */
    private function render_dismiss_script() {
        ?>
        <script type="text/javascript">
        (function($) {
            $(document).on('click', '.pw-offer-dismiss', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var $banner = $button.closest('.pw-offer-banner');
                var offerId = $button.data('offer-id');
                var nonce = $button.data('nonce');
                
                $.post(ajaxurl, {
                    action: '<?php echo esc_js( self::DISMISS_ACTION ); ?>',
                    offer_id: offerId,
                    nonce: nonce
                }).done(function() {
                    $banner.fadeOut(300, function() {
                        $banner.remove();
                    });
                });
            });
        })(jQuery);
        </script>
        <style>
            .pw-offer-banner {
                padding: 0 !important;
                border: none !important;
                background: transparent !important;
            }
            .pw-offer-banner a {
                display: block;
            }
            .pw-offer-banner .notice-dismiss {
                position: absolute;
                top: 8px;
                right: 8px;
                background: rgba(0,0,0,0.5);
                border-radius: 50%;
                color: #fff;
            }
            .pw-offer-banner .notice-dismiss:hover {
                background: rgba(0,0,0,0.7);
            }
            .pw-offer-banner .notice-dismiss:before {
                color: #fff;
            }
        </style>
        <?php
    }

    /**
     * Handle AJAX dismissal request.
     *
     * @since 1.0.0
     */
    public function dismiss_banner() {
        // Verify nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), self::DISMISS_ACTION ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'pearl-weather' ) ), 403 );
        }

        // Check user capability.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'pearl-weather' ) ), 403 );
        }

        $offer_id = isset( $_POST['offer_id'] ) ? sanitize_text_field( wp_unslash( $_POST['offer_id'] ) ) : '';

        if ( empty( $offer_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid offer ID.', 'pearl-weather' ) ), 400 );
        }

        update_option( self::DISMISS_OPTION_PREFIX . $offer_id, true );

        wp_send_json_success( array( 'message' => __( 'Banner dismissed.', 'pearl-weather' ) ) );
    }

    /**
     * Reset all dismissed banners (for testing).
     *
     * @since 1.0.0
     */
    public static function reset_dismissed_banners() {
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '" . self::DISMISS_OPTION_PREFIX . "%'" );
    }
}

// Initialize the offer banner manager.
OfferBannerManager::get_instance();