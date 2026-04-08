<?php
/**
 * Plugin Uninstall Handler
 *
 * This file runs when Pearl Weather is uninstalled from WordPress.
 * It removes all plugin data, options, custom post types, transients,
 * and scheduled cron events based on user preferences.
 *
 * @package    PearlWeather
 * @since      1.0.0
 * @author     Your Name
 * @license    GPL-2.0-or-later
 */

// Prevent direct access - only allow uninstall via WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Class Pearl_Weather_Uninstaller
 * Handles all cleanup operations during plugin uninstallation.
 */
class Pearl_Weather_Uninstaller {

    /**
     * Plugin settings option name.
     *
     * @var string
     */
    private static $settings_option = 'pearl_weather_settings';

    /**
     * Custom post type for weather widgets.
     *
     * @var string
     */
    private static $post_type = 'pearl_weather_widget';

    /**
     * Saved templates post type.
     *
     * @var string
     */
    private static $template_post_type = 'pearl_weather_template';

    /**
     * Main uninstall handler.
     * Checks if user opted to delete data before proceeding.
     */
    public static function uninstall() {
        // Check if we should delete plugin data.
        if ( ! self::should_delete_data() ) {
            return;
        }

        // Delete all plugin options.
        self::delete_options();

        // Delete custom post types and their meta.
        self::delete_custom_post_types();

        // Delete all post meta keys.
        self::delete_post_meta();

        // Delete all transients (cache).
        self::delete_transients();

        // Clear scheduled cron events.
        self::clear_scheduled_events();

        // Multisite support.
        if ( is_multisite() ) {
            self::delete_multisite_data();
        }
    }

    /**
     * Check if plugin data should be deleted on uninstall.
     *
     * @return bool
     */
    private static function should_delete_data() {
        // Get plugin settings.
        $settings = get_option( self::$settings_option, array() );
        
        // Check if delete_on_uninstall flag is set to true.
        return isset( $settings['delete_on_uninstall'] ) && true === $settings['delete_on_uninstall'];
    }

    /**
     * Delete all plugin-related options.
     */
    private static function delete_options() {
        $options_to_delete = array(
            self::$settings_option,
            'pearl_weather_version',
            'pearl_weather_setup_completed',
            'pearl_weather_consent_notice_ignored',
            'pearl_weather_consent_notice_start_time',
            'pearl_weather_setup_wizard_visited',
            'pearl_weather_allow_tracking',
            'pearl_weather_site_type',
            'pearl_weather_blocks_visibility',
            'pearl_weather_api_key_last_check',
            'pearl_weather_last_weather_update',
        );

        foreach ( $options_to_delete as $option ) {
            delete_option( $option );
        }
    }

    /**
     * Delete all custom posts of our post types.
     */
    private static function delete_custom_post_types() {
        $post_types = array( self::$post_type, self::$template_post_type );
        
        foreach ( $post_types as $post_type ) {
            $posts = get_posts(
                array(
                    'post_type'      => $post_type,
                    'post_status'    => 'any',
                    'posts_per_page' => -1,
                    'fields'         => 'ids', // Only get IDs for performance.
                )
            );
            
            foreach ( $posts as $post_id ) {
                // Force delete (skip trash).
                wp_delete_post( $post_id, true );
            }
        }
    }

    /**
     * Delete all plugin-related post meta.
     */
    private static function delete_post_meta() {
        global $wpdb;
        
        // Delete meta keys specific to Pearl Weather.
        $meta_keys = array(
            'pearl_weather_widget_data',
            'pearl_weather_settings_data',
            'pearl_weather_template_data',
            'pearl_weather_location_data',
            'pearl_weather_api_response',
            'pearl_weather_last_updated',
        );
        
        foreach ( $meta_keys as $meta_key ) {
            $wpdb->delete(
                $wpdb->postmeta,
                array( 'meta_key' => $meta_key ),
                array( '%s' )
            );
        }
        
        // Also delete any meta starting with 'pearl_weather_' (wildcard).
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
                'pearl_weather_%'
            )
        );
    }

    /**
     * Delete all transients (cache data).
     */
    private static function delete_transients() {
        global $wpdb;
        
        // Delete transients with our prefix.
        $patterns = array(
            'pearl_weather_%',
            '_transient_pearl_weather_%',
            '_transient_timeout_pearl_weather_%',
            '_site_transient_pearl_weather_%',
            '_site_transient_timeout_pearl_weather_%',
        );
        
        foreach ( $patterns as $pattern ) {
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                    $pattern
                )
            );
        }
    }

    /**
     * Clear all scheduled cron events.
     */
    private static function clear_scheduled_events() {
        $cron_hooks = array(
            'pearl_weather_weekly_cleanup',
            'pearl_weather_daily_weather_update',
            'pearl_weather_cache_cleanup',
        );
        
        foreach ( $cron_hooks as $hook ) {
            $timestamp = wp_next_scheduled( $hook );
            if ( $timestamp ) {
                wp_unschedule_event( $timestamp, $hook );
            }
        }
        
        // Clear all scheduled events with our hook prefix.
        $cron = _get_cron_array();
        if ( ! empty( $cron ) ) {
            foreach ( $cron as $timestamp => $cron_hooks ) {
                foreach ( array_keys( $cron_hooks ) as $hook ) {
                    if ( strpos( $hook, 'pearl_weather_' ) === 0 ) {
                        wp_unschedule_event( $timestamp, $hook );
                    }
                }
            }
        }
    }

    /**
     * Delete multisite network-wide data.
     */
    private static function delete_multisite_data() {
        global $wpdb;
        
        // Get all blog IDs.
        $blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
        
        foreach ( $blog_ids as $blog_id ) {
            switch_to_blog( $blog_id );
            
            // Delete site-specific options.
            $options_to_delete = array(
                self::$settings_option,
                'pearl_weather_version',
                'pearl_weather_setup_completed',
                'pearl_weather_consent_notice_ignored',
            );
            
            foreach ( $options_to_delete as $option ) {
                delete_option( $option );
            }
            
            // Delete site-specific transients.
            self::delete_transients();
            
            restore_current_blog();
        }
        
        // Delete network-wide options.
        $network_options = array(
            'pearl_weather_network_settings',
            'pearl_weather_global_tracking_consent',
        );
        
        foreach ( $network_options as $option ) {
            delete_site_option( $option );
        }
        
        // Delete network transients.
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s",
                '_site_transient_pearl_weather_%'
            )
        );
    }
}

/**
 * Optionally load main plugin file to access settings.
 * We need to check if user opted to delete data.
 */
if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Load plugin main file to access settings.
$plugin_main_file = plugin_dir_path( __FILE__ ) . 'pearl-weather.php';
if ( file_exists( $plugin_main_file ) ) {
    require_once $plugin_main_file;
}

// Run uninstaller.
Pearl_Weather_Uninstaller::uninstall();