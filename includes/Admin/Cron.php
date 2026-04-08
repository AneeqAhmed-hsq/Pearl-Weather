<?php
/**
 * Cron Scheduler for Pearl Weather
 *
 * Handles scheduling of recurring events for cache cleanup,
 * data syncing, and maintenance tasks.
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
 * Class CronScheduler
 *
 * Manages cron schedules and events.
 *
 * @since 1.0.0
 */
class CronScheduler {

    /**
     * Weekly event hook name.
     */
    const WEEKLY_EVENT = 'pearl_weather_weekly_cleanup';

    /**
     * Daily event hook name.
     */
    const DAILY_EVENT = 'pearl_weather_daily_maintenance';

    /**
     * Hourly event hook name.
     */
    const HOURLY_EVENT = 'pearl_weather_hourly_check';

    /**
     * Constructor.
     */
    public function __construct() {
        add_filter( 'cron_schedules', array( $this, 'add_custom_schedules' ) );
        add_action( 'wp', array( $this, 'schedule_events' ) );
        add_action( self::WEEKLY_EVENT, array( $this, 'run_weekly_cleanup' ) );
        add_action( self::DAILY_EVENT, array( $this, 'run_daily_maintenance' ) );
        add_action( self::HOURLY_EVENT, array( $this, 'run_hourly_check' ) );
        
        // Clean up on plugin deactivation.
        register_deactivation_hook( PEARL_WEATHER_FILE, array( $this, 'clear_all_schedules' ) );
    }

    /**
     * Add custom cron schedules.
     *
     * @since 1.0.0
     * @param array $schedules Existing schedules.
     * @return array
     */
    public function add_custom_schedules( $schedules ) {
        // Add weekly schedule.
        if ( ! isset( $schedules['weekly'] ) ) {
            $schedules['weekly'] = array(
                'interval' => WEEK_IN_SECONDS,
                'display'  => __( 'Once Weekly', 'pearl-weather' ),
            );
        }

        // Add bi-weekly schedule.
        if ( ! isset( $schedules['biweekly'] ) ) {
            $schedules['biweekly'] = array(
                'interval' => WEEK_IN_SECONDS * 2,
                'display'  => __( 'Once Every 2 Weeks', 'pearl-weather' ),
            );
        }

        // Add monthly schedule.
        if ( ! isset( $schedules['monthly'] ) ) {
            $schedules['monthly'] = array(
                'interval' => MONTH_IN_SECONDS,
                'display'  => __( 'Once Monthly', 'pearl-weather' ),
            );
        }

        return $schedules;
    }

    /**
     * Schedule all cron events.
     *
     * @since 1.0.0
     */
    public function schedule_events() {
        $this->schedule_weekly_cleanup();
        $this->schedule_daily_maintenance();
        $this->schedule_hourly_check();
    }

    /**
     * Schedule weekly cleanup event.
     *
     * @since 1.0.0
     */
    private function schedule_weekly_cleanup() {
        if ( ! wp_next_scheduled( self::WEEKLY_EVENT ) ) {
            // Schedule at a random time within the next week to avoid spikes.
            $offset = rand( 0, WEEK_IN_SECONDS );
            wp_schedule_event( time() + $offset, 'weekly', self::WEEKLY_EVENT );
        }
    }

    /**
     * Schedule daily maintenance event.
     *
     * @since 1.0.0
     */
    private function schedule_daily_maintenance() {
        if ( ! wp_next_scheduled( self::DAILY_EVENT ) ) {
            // Schedule at 2 AM daily.
            $timestamp = strtotime( 'tomorrow 02:00:00' );
            wp_schedule_event( $timestamp, 'daily', self::DAILY_EVENT );
        }
    }

    /**
     * Schedule hourly check event.
     *
     * @since 1.0.0
     */
    private function schedule_hourly_check() {
        if ( ! wp_next_scheduled( self::HOURLY_EVENT ) ) {
            wp_schedule_event( time(), 'hourly', self::HOURLY_EVENT );
        }
    }

    /**
     * Run weekly cleanup tasks.
     *
     * @since 1.0.0
     */
    public function run_weekly_cleanup() {
        // Clear expired transients.
        $this->clean_expired_transients();
        
        // Delete old temporary files.
        $this->clean_temp_files();
        
        // Optimize database tables (if needed).
        $this->optimize_database();
        
        // Send anonymous usage data if consented.
        $this->send_anonymous_stats();
    }

    /**
     * Run daily maintenance tasks.
     *
     * @since 1.0.0
     */
    public function run_daily_maintenance() {
        // Refresh cached weather data for popular locations.
        $this->refresh_popular_caches();
        
        // Clean up old logs.
        $this->clean_old_logs();
        
        // Update plugin version data.
        $this->update_version_data();
    }

    /**
     * Run hourly check tasks.
     *
     * @since 1.0.0
     */
    public function run_hourly_check() {
        // Check for plugin updates.
        $this->check_for_updates();
        
        // Verify API key status.
        $this->verify_api_key();
        
        // Process any pending queue items.
        $this->process_pending_queue();
    }

    /**
     * Clean expired transients.
     *
     * @since 1.0.0
     */
    private function clean_expired_transients() {
        global $wpdb;
        
        // Delete expired transients.
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_timeout_pw_%' 
             AND option_value < UNIX_TIMESTAMP()"
        );
        
        // Delete corresponding expired transient values.
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_pw_%' 
             AND option_name NOT IN (
                 SELECT REPLACE(option_name, '_transient_timeout_', '_transient_')
                 FROM {$wpdb->options}
                 WHERE option_name LIKE '_transient_timeout_pw_%'
                 AND option_value >= UNIX_TIMESTAMP()
             )"
        );
    }

    /**
     * Clean temporary files.
     *
     * @since 1.0.0
     */
    private function clean_temp_files() {
        $upload_dir = wp_upload_dir();
        $cache_dir = trailingslashit( $upload_dir['basedir'] ) . 'pearl-weather-cache/';
        
        if ( ! is_dir( $cache_dir ) ) {
            return;
        }
        
        // Delete files older than 30 days.
        $files = glob( $cache_dir . '*.json' );
        $now = time();
        
        foreach ( $files as $file ) {
            if ( is_file( $file ) && ( $now - filemtime( $file ) ) > 30 * DAY_IN_SECONDS ) {
                wp_delete_file( $file );
            }
        }
    }

    /**
     * Optimize database tables.
     *
     * @since 1.0.0
     */
    private function optimize_database() {
        global $wpdb;
        
        // Optimize options table (where transients are stored).
        $wpdb->query( "OPTIMIZE TABLE {$wpdb->options}" );
        
        // Optimize postmeta table.
        $wpdb->query( "OPTIMIZE TABLE {$wpdb->postmeta}" );
    }

    /**
     * Send anonymous usage statistics.
     *
     * @since 1.0.0
     */
    private function send_anonymous_stats() {
        $allow_tracking = get_option( 'pearl_weather_allow_tracking', false );
        
        if ( ! $allow_tracking ) {
            return;
        }
        
        // Collect and send anonymous stats.
        // Implementation would depend on your tracking service.
    }

    /**
     * Refresh popular caches.
     *
     * @since 1.0.0
     */
    private function refresh_popular_caches() {
        // Get most frequently requested locations.
        $popular_locations = get_option( 'pearl_weather_popular_locations', array() );
        
        if ( empty( $popular_locations ) ) {
            return;
        }
        
        // Refresh cache for top locations.
        $api = new \PearlWeather\API\WeatherAPI();
        
        foreach ( array_slice( $popular_locations, 0, 10 ) as $location ) {
            $api->get_current_weather( $location['query'], $location['units'], true );
        }
    }

    /**
     * Clean old log files.
     *
     * @since 1.0.0
     */
    private function clean_old_logs() {
        $upload_dir = wp_upload_dir();
        $log_dir = trailingslashit( $upload_dir['basedir'] ) . 'pearl-weather-logs/';
        
        if ( ! is_dir( $log_dir ) ) {
            return;
        }
        
        // Delete logs older than 90 days.
        $files = glob( $log_dir . '*.log' );
        $now = time();
        
        foreach ( $files as $file ) {
            if ( is_file( $file ) && ( $now - filemtime( $file ) ) > 90 * DAY_IN_SECONDS ) {
                wp_delete_file( $file );
            }
        }
    }

    /**
     * Update version data in database.
     *
     * @since 1.0.0
     */
    private function update_version_data() {
        $current_version = get_option( 'pearl_weather_version', '0' );
        
        if ( version_compare( $current_version, PEARL_WEATHER_VERSION, '<' ) ) {
            update_option( 'pearl_weather_version', PEARL_WEATHER_VERSION );
            update_option( 'pearl_weather_version_updated', time() );
        }
    }

    /**
     * Check for plugin updates.
     *
     * @since 1.0.0
     */
    private function check_for_updates() {
        // Check for updates using WordPress update system.
        // This is handled automatically by WordPress, but we can trigger it.
        delete_site_transient( 'update_plugins' );
        wp_update_plugins();
    }

    /**
     * Verify API key status.
     *
     * @since 1.0.0
     */
    private function verify_api_key() {
        $last_check = get_option( 'pearl_weather_api_key_last_check', 0 );
        $now = time();
        
        // Check once per week.
        if ( ( $now - $last_check ) < WEEK_IN_SECONDS ) {
            return;
        }
        
        $settings = get_option( 'pearl_weather_settings', array() );
        $api_key = isset( $settings['api_key'] ) ? $settings['api_key'] : '';
        
        if ( ! empty( $api_key ) ) {
            $api = new \PearlWeather\API\WeatherAPI();
            $result = $api->verify_api_key( $api_key );
            
            if ( $result ) {
                update_option( 'pearl_weather_api_key_valid', true );
            } else {
                update_option( 'pearl_weather_api_key_valid', false );
            }
        }
        
        update_option( 'pearl_weather_api_key_last_check', $now );
    }

    /**
     * Process pending queue items.
     *
     * @since 1.0.0
     */
    private function process_pending_queue() {
        $queue = get_option( 'pearl_weather_queue', array() );
        
        if ( empty( $queue ) ) {
            return;
        }
        
        foreach ( $queue as $index => $item ) {
            // Process item based on type.
            switch ( $item['action'] ) {
                case 'send_stats':
                    // Send statistics.
                    break;
                case 'update_cache':
                    // Update cache.
                    break;
                default:
                    // Unknown action.
                    break;
            }
            
            // Remove processed item.
            unset( $queue[ $index ] );
        }
        
        update_option( 'pearl_weather_queue', array_values( $queue ) );
    }

    /**
     * Clear all scheduled events.
     *
     * @since 1.0.0
     */
    public function clear_all_schedules() {
        wp_clear_scheduled_hook( self::WEEKLY_EVENT );
        wp_clear_scheduled_hook( self::DAILY_EVENT );
        wp_clear_scheduled_hook( self::HOURLY_EVENT );
    }

    /**
     * Reschedule all events (useful after timezone change).
     *
     * @since 1.0.0
     */
    public static function reschedule_events() {
        wp_clear_scheduled_hook( self::WEEKLY_EVENT );
        wp_clear_scheduled_hook( self::DAILY_EVENT );
        wp_clear_scheduled_hook( self::HOURLY_EVENT );
        
        $cron = new self();
        $cron->schedule_events();
    }
}

// Initialize the cron scheduler.
new CronScheduler();