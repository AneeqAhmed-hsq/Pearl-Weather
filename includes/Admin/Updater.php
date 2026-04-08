<?php
/**
 * Plugin Update Handler
 *
 * Manages database migrations and version tracking during plugin updates.
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
 * Class PluginUpdater
 *
 * Handles plugin version tracking and database migrations.
 *
 * @since 1.0.0
 */
class PluginUpdater {

    /**
     * Option name for installed version.
     */
    const VERSION_OPTION = 'pearl_weather_version';

    /**
     * Option name for first installed version.
     */
    const FIRST_VERSION_OPTION = 'pearl_weather_first_version';

    /**
     * Option name for activation date.
     */
    const ACTIVATION_DATE_OPTION = 'pearl_weather_activation_date';

    /**
     * Option name for database version.
     */
    const DB_VERSION_OPTION = 'pearl_weather_db_version';

    /**
     * Updates that need to be run.
     * Maps version numbers to update script paths.
     *
     * @var array
     */
    private $updates = array();

    /**
     * Constructor.
     */
    public function __construct() {
        $this->init_updates();
        add_action( 'admin_init', array( $this, 'check_and_run_updates' ) );
        add_action( 'admin_init', array( $this, 'track_activation_metrics' ) );
    }

    /**
     * Initialize updates list.
     *
     * @since 1.0.0
     */
    private function init_updates() {
        $this->updates = array(
            '1.0.0' => 'updates/update-1.0.0.php',
            // Add future updates here.
        );
    }

    /**
     * Check if updates are needed.
     *
     * @since 1.0.0
     * @return bool
     */
    public function needs_update() {
        $installed_version = get_option( self::VERSION_OPTION );

        if ( ! $installed_version ) {
            return true;
        }

        return version_compare( $installed_version, PEARL_WEATHER_VERSION, '<' );
    }

    /**
     * Track activation metrics (first version, activation date).
     *
     * @since 1.0.0
     */
    public function track_activation_metrics() {
        $first_version = get_option( self::FIRST_VERSION_OPTION );
        $activation_date = get_option( self::ACTIVATION_DATE_OPTION );

        if ( false === $first_version ) {
            update_option( self::FIRST_VERSION_OPTION, PEARL_WEATHER_VERSION );
        }

        if ( false === $activation_date ) {
            update_option( self::ACTIVATION_DATE_OPTION, current_time( 'timestamp' ) );
        }
    }

    /**
     * Check and run updates if needed.
     *
     * @since 1.0.0
     */
    public function check_and_run_updates() {
        if ( ! $this->needs_update() ) {
            return;
        }

        if ( ! current_user_can( 'update_plugins' ) ) {
            return;
        }

        $this->perform_updates();
    }

    /**
     * Perform all pending updates.
     *
     * @since 1.0.0
     */
    private function perform_updates() {
        $installed_version = get_option( self::VERSION_OPTION, '0.0.0' );

        foreach ( $this->updates as $version => $script_path ) {
            if ( version_compare( $installed_version, $version, '<' ) ) {
                $this->run_update_script( $version, $script_path );
            }
        }

        // Update to current version.
        update_option( self::VERSION_OPTION, PEARL_WEATHER_VERSION );
        update_option( self::DB_VERSION_OPTION, PEARL_WEATHER_VERSION );

        // Trigger action after update.
        do_action( 'pearl_weather_updated', $installed_version, PEARL_WEATHER_VERSION );
    }

    /**
     * Run a specific update script.
     *
     * @since 1.0.0
     * @param string $version     Target version.
     * @param string $script_path Path to update script.
     */
    private function run_update_script( $version, $script_path ) {
        $full_path = PEARL_WEATHER_PATH . 'includes/updates/' . basename( $script_path );

        if ( file_exists( $full_path ) ) {
            include $full_path;
        }

        // Mark this version as updated.
        update_option( self::VERSION_OPTION, $version );

        /**
         * Action after specific version update.
         *
         * @param string $version Updated to version.
         */
        do_action( 'pearl_weather_updated_to_' . str_replace( '.', '_', $version ) );
    }

    /**
     * Get the installed version.
     *
     * @since 1.0.0
     * @return string
     */
    public static function get_installed_version() {
        return get_option( self::VERSION_OPTION, '0.0.0' );
    }

    /**
     * Get the first installed version.
     *
     * @since 1.0.0
     * @return string|false
     */
    public static function get_first_version() {
        return get_option( self::FIRST_VERSION_OPTION );
    }

    /**
     * Get activation date.
     *
     * @since 1.0.0
     * @return int|false
     */
    public static function get_activation_date() {
        return get_option( self::ACTIVATION_DATE_OPTION );
    }

    /**
     * Check if this is a fresh install.
     *
     * @since 1.0.0
     * @return bool
     */
    public static function is_fresh_install() {
        return false === get_option( self::VERSION_OPTION );
    }

    /**
     * Reset update data (for testing).
     *
     * @since 1.0.0
     */
    public static function reset_update_data() {
        delete_option( self::VERSION_OPTION );
        delete_option( self::FIRST_VERSION_OPTION );
        delete_option( self::ACTIVATION_DATE_OPTION );
        delete_option( self::DB_VERSION_OPTION );
    }
}

// Initialize the updater.
new PluginUpdater();