<?php
/**
 * Plugin Name:       AtmoPress Weather
 * Plugin URI:        https://example.com/atmopress-weather
 * Description:       A powerful, lightweight WordPress weather plugin with Gutenberg blocks, shortcodes, multiple templates, auto location detection, and full forecast support.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            AtmoPress Team
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       atmopress-weather
 * Domain Path:       /languages
 *
 * @package AtmoPressWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ATMOPRESS_VERSION',    '1.0.0' );
define( 'ATMOPRESS_FILE',       __FILE__ );
define( 'ATMOPRESS_DIR',        plugin_dir_path( __FILE__ ) );
define( 'ATMOPRESS_URL',        plugin_dir_url( __FILE__ ) );
define( 'ATMOPRESS_ASSETS_URL', ATMOPRESS_URL . 'assets/' );
define( 'ATMOPRESS_CORE_DIR',   ATMOPRESS_DIR . 'core/' );
define( 'ATMOPRESS_TPL_DIR',    ATMOPRESS_DIR . 'templates/' );
define( 'ATMOPRESS_SLUG',       'atmopress-weather' );
define( 'ATMOPRESS_OPT',        'atmopress_settings' );

require_once ATMOPRESS_CORE_DIR . 'class-settings.php';
require_once ATMOPRESS_CORE_DIR . 'class-data-cache.php';
require_once ATMOPRESS_CORE_DIR . 'class-api-client.php';
require_once ATMOPRESS_CORE_DIR . 'class-template-loader.php';
require_once ATMOPRESS_CORE_DIR . 'class-shortcode.php';
require_once ATMOPRESS_CORE_DIR . 'class-rest-api.php';
require_once ATMOPRESS_CORE_DIR . 'class-gutenberg-block.php';
require_once ATMOPRESS_CORE_DIR . 'admin/class-admin-page.php';
require_once ATMOPRESS_CORE_DIR . 'class-bootstrap.php';

register_activation_hook( ATMOPRESS_FILE, array( 'AtmoPress\\Bootstrap', 'on_activate' ) );
register_deactivation_hook( ATMOPRESS_FILE, array( 'AtmoPress\\Bootstrap', 'on_deactivate' ) );

add_action( 'plugins_loaded', array( 'AtmoPress\\Bootstrap', 'init' ) );
