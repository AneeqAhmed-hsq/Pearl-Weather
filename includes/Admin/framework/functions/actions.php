<?php
/**
 * Framework AJAX Actions
 *
 * Handles AJAX requests for icon library, settings export/import,
 * cache clearing, and dynamic select dropdowns.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Framework
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PearlWeather\API\WeatherAPI;

/**
 * Class FrameworkActions
 *
 * Manages all AJAX actions for the plugin framework.
 *
 * @since 1.0.0
 */
class FrameworkActions {

    /**
     * Nonce key for icon actions.
     */
    const ICON_NONCE_KEY = 'pw_icon_nonce';

    /**
     * Nonce key for backup actions.
     */
    const BACKUP_NONCE_KEY = 'pw_backup_nonce';

    /**
     * Nonce key for options actions.
     */
    const OPTIONS_NONCE_KEY = 'pw_options_nonce';

    /**
     * Nonce key for chosen AJAX.
     */
    const CHOSEN_NONCE_KEY = 'pw_chosen_nonce';

    /**
     * Constructor.
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize AJAX hooks.
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        add_action( 'wp_ajax_pw_get_icons', array( $this, 'ajax_get_icons' ) );
        add_action( 'wp_ajax_pw_export_settings', array( $this, 'ajax_export_settings' ) );
        add_action( 'wp_ajax_pw_import_settings', array( $this, 'ajax_import_settings' ) );
        add_action( 'wp_ajax_pw_reset_settings', array( $this, 'ajax_reset_settings' ) );
        add_action( 'wp_ajax_pw_clear_weather_cache', array( $this, 'ajax_clear_weather_cache' ) );
        add_action( 'wp_ajax_pw_chosen_search', array( $this, 'ajax_chosen_search' ) );
    }

    /**
     * Get icons via AJAX.
     *
     * @since 1.0.0
     */
    public function ajax_get_icons() {
        // Verify nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), self::ICON_NONCE_KEY ) ) {
            wp_send_json_error( array( 'error' => __( 'Invalid nonce verification.', 'pearl-weather' ) ), 403 );
        }

        // Check user capability.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'error' => __( 'You do not have permission to do this.', 'pearl-weather' ) ), 403 );
        }

        ob_start();

        // Get icon library.
        $icons = $this->get_icon_list();

        if ( ! empty( $icons ) ) {
            foreach ( $icons as $icon ) {
                echo '<i title="' . esc_attr( $icon ) . '" class="' . esc_attr( $icon ) . '"></i>';
            }
        } else {
            echo '<div class="pw-error-text">' . esc_html__( 'No icons found.', 'pearl-weather' ) . '</div>';
        }

        $content = ob_get_clean();

        wp_send_json_success( array( 'content' => $content ) );
    }

    /**
     * Get icon list.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_icon_list() {
        // Default icon list (Font Awesome 5 free icons).
        $icons = array(
            'fas fa-cloud-sun',
            'fas fa-cloud-rain',
            'fas fa-snowflake',
            'fas fa-wind',
            'fas fa-tint',
            'fas fa-temperature-high',
            'fas fa-temperature-low',
            'fas fa-sun',
            'fas fa-moon',
            'fas fa-cloud-moon',
            'fas fa-cloud-sun-rain',
            'fas fa-bolt',
            'fas fa-smog',
            'fas fa-hurricane',
            'fas fa-water',
            'fas fa-tachometer-alt',
            'fas fa-compass',
            'fas fa-map-marker-alt',
            'fas fa-clock',
            'fas fa-calendar-alt',
        );

        /**
         * Filter the icon list.
         *
         * @since 1.0.0
         * @param array $icons List of icon classes.
         */
        return apply_filters( 'pearl_weather_icon_list', $icons );
    }

    /**
     * Export settings via AJAX.
     *
     * @since 1.0.0
     */
    public function ajax_export_settings() {
        // Verify nonce.
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), self::BACKUP_NONCE_KEY ) ) {
            wp_die( esc_html__( 'Invalid nonce verification.', 'pearl-weather' ), 403 );
        }

        $option_key = isset( $_GET['option_key'] ) ? sanitize_key( $_GET['option_key'] ) : '';

        if ( empty( $option_key ) ) {
            wp_die( esc_html__( 'Invalid option key.', 'pearl-weather' ), 400 );
        }

        // Check user capability.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to do this.', 'pearl-weather' ), 403 );
        }

        $settings = get_option( $option_key, array() );

        // Set headers for file download.
        header( 'Content-Type: application/json' );
        header( 'Content-Disposition: attachment; filename=pearl-weather-backup-' . gmdate( 'Y-m-d' ) . '.json' );
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        echo wp_json_encode( $settings, JSON_PRETTY_PRINT );
        wp_die();
    }

    /**
     * Import settings via AJAX.
     *
     * @since 1.0.0
     */
    public function ajax_import_settings() {
        // Verify nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), self::BACKUP_NONCE_KEY ) ) {
            wp_send_json_error( array( 'error' => __( 'Invalid nonce verification.', 'pearl-weather' ) ), 403 );
        }

        // Check user capability.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'error' => __( 'You do not have permission to do this.', 'pearl-weather' ) ), 403 );
        }

        $option_key = isset( $_POST['option_key'] ) ? sanitize_key( $_POST['option_key'] ) : '';
        $import_data = isset( $_POST['import_data'] ) ? json_decode( wp_unslash( $_POST['import_data'] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        if ( empty( $option_key ) ) {
            wp_send_json_error( array( 'error' => __( 'Invalid option key.', 'pearl-weather' ) ), 400 );
        }

        if ( empty( $import_data ) || ! is_array( $import_data ) ) {
            wp_send_json_error( array( 'error' => __( 'Invalid import data.', 'pearl-weather' ) ), 400 );
        }

        update_option( $option_key, $import_data );

        wp_send_json_success( array( 'message' => __( 'Settings imported successfully.', 'pearl-weather' ) ) );
    }

    /**
     * Reset settings via AJAX.
     *
     * @since 1.0.0
     */
    public function ajax_reset_settings() {
        // Verify nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), self::BACKUP_NONCE_KEY ) ) {
            wp_send_json_error( array( 'error' => __( 'Invalid nonce verification.', 'pearl-weather' ) ), 403 );
        }

        // Check user capability.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'error' => __( 'You do not have permission to do this.', 'pearl-weather' ) ), 403 );
        }

        $option_key = isset( $_POST['option_key'] ) ? sanitize_key( $_POST['option_key'] ) : '';

        if ( empty( $option_key ) ) {
            wp_send_json_error( array( 'error' => __( 'Invalid option key.', 'pearl-weather' ) ), 400 );
        }

        delete_option( $option_key );

        wp_send_json_success( array( 'message' => __( 'Settings reset successfully.', 'pearl-weather' ) ) );
    }

    /**
     * Clear weather cache via AJAX.
     *
     * @since 1.0.0
     */
    public function ajax_clear_weather_cache() {
        // Verify nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), self::OPTIONS_NONCE_KEY ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce verification.', 'pearl-weather' ) ), 403 );
        }

        // Check user capability.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'pearl-weather' ) ), 403 );
        }

        global $wpdb;

        if ( is_multisite() ) {
            $table = $wpdb->get_blog_prefix( get_current_blog_id() ) . 'sitemeta';
            $pattern = '%\_site_transient_pw_weather_%';
        } else {
            $table = $wpdb->options;
            $pattern = '%\_transient_pw_weather_%';
        }

        // Delete weather transients.
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$table} WHERE `option_name` LIKE %s",
            $pattern
        ) );

        // Delete timeout transients.
        $timeout_pattern = str_replace( 'transient', 'transient_timeout', $pattern );
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$table} WHERE `option_name` LIKE %s",
            $timeout_pattern
        ) );

        wp_send_json_success( array( 'message' => __( 'Weather cache cleared successfully.', 'pearl-weather' ) ) );
    }

    /**
     * Chosen AJAX search for dynamic select fields.
     *
     * @since 1.0.0
     */
    public function ajax_chosen_search() {
        // Verify nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), self::CHOSEN_NONCE_KEY ) ) {
            wp_send_json_error( array( 'error' => __( 'Invalid nonce verification.', 'pearl-weather' ) ), 403 );
        }

        // Check user capability.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'error' => __( 'You do not have permission to do this.', 'pearl-weather' ) ), 403 );
        }

        $search_type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
        $search_term = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
        $query_args = isset( $_POST['query_args'] ) ? wp_kses_post_deep( $_POST['query_args'] ) : array();

        if ( empty( $search_type ) ) {
            wp_send_json_error( array( 'error' => __( 'Invalid search type.', 'pearl-weather' ) ), 400 );
        }

        $results = $this->perform_chosen_search( $search_type, $search_term, $query_args );

        wp_send_json_success( $results );
    }

    /**
     * Perform chosen search based on type.
     *
     * @since 1.0.0
     * @param string $type       Search type (posts, terms, users, etc.).
     * @param string $search_term Search term.
     * @param array  $query_args Additional query arguments.
     * @return array
     */
    private function perform_chosen_search( $type, $search_term, $query_args ) {
        $results = array();

        switch ( $type ) {
            case 'posts':
                $query = new \WP_Query( array_merge( array(
                    's'              => $search_term,
                    'posts_per_page' => 20,
                    'post_type'      => isset( $query_args['post_type'] ) ? $query_args['post_type'] : 'any',
                    'post_status'    => 'publish',
                ), $query_args ) );

                foreach ( $query->posts as $post ) {
                    $results[] = array(
                        'id'   => $post->ID,
                        'text' => $post->post_title,
                    );
                }
                break;

            case 'terms':
                $taxonomy = isset( $query_args['taxonomy'] ) ? $query_args['taxonomy'] : 'category';
                $terms = get_terms( array(
                    'taxonomy'   => $taxonomy,
                    'name__like' => $search_term,
                    'hide_empty' => false,
                    'number'     => 20,
                ) );

                foreach ( $terms as $term ) {
                    $results[] = array(
                        'id'   => $term->term_id,
                        'text' => $term->name,
                    );
                }
                break;

            case 'users':
                $users = get_users( array(
                    'search'         => '*' . $search_term . '*',
                    'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
                    'number'         => 20,
                ) );

                foreach ( $users as $user ) {
                    $results[] = array(
                        'id'   => $user->ID,
                        'text' => $user->display_name . ' (' . $user->user_email . ')',
                    );
                }
                break;
        }

        return $results;
    }
}

// Initialize framework actions.
new FrameworkActions();