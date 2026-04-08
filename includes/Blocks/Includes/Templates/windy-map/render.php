<?php
/**
 * Weather Block Map Renderer (Windy.com)
 *
 * This is a wrapper template for rendering Windy.com weather maps.
 * It includes the main windy-map template for map display.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Blocks/Templates/Parts
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template variables:
 * - $attributes: Block attributes (settings)
 * - $weather_data: Current weather data array
 * - $unique_id: Unique block identifier
 */

// Include the main Windy map template.
$windy_map_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/maps/windy-map.php';

if ( file_exists( $windy_map_template ) ) {
    include $windy_map_template;
} else {
    // Fallback if template not found.
    ?>
    <div class="pw-map-error">
        <p><?php esc_html_e( 'Map template not found. Please check your plugin installation.', 'pearl-weather' ); ?></p>
    </div>
    <?php
}