<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pearl_Weather_Admin_Notices {

    public function __construct() {
        add_action( 'admin_notices', array( $this, 'maybe_show_activation_notice' ) );
    }

    public function maybe_show_activation_notice() {
        if ( get_transient( 'pearl_weather_activation_redirect' ) ) {
            delete_transient( 'pearl_weather_activation_redirect' );
            $settings = get_option( 'pearl_weather_settings', array() );
            if ( empty( $settings['api_key'] ) ) {
                ?>
                <div class="notice notice-info is-dismissible">
                    <p>
                        <?php
                        printf(
                            wp_kses(
                                /* translators: %s: settings page link */
                                __( 'Thank you for installing <strong>Pearl Weather</strong>! Please <a href="%s">configure your API key</a> to get started.', 'pearl-weather' ),
                                array(
                                    'strong' => array(),
                                    'a'      => array( 'href' => array() ),
                                )
                            ),
                            esc_url( admin_url( 'admin.php?page=pearl-weather-settings' ) )
                        );
                        ?>
                    </p>
                </div>
                <?php
            }
        }
    }
}

new Pearl_Weather_Admin_Notices();
