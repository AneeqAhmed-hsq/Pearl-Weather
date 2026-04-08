<?php
/**
 * Block Settings Helper
 *
 * Provides helper functions for managing Gutenberg blocks,
 * including block lists and visibility settings.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Admin/Dashboard/Includes
 * @since      1.0.0
 */

namespace PearlWeather\Admin\Dashboard\Includes;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SettingsHelper
 *
 * Manages block configuration and helper methods.
 *
 * @since 1.0.0
 */
class SettingsHelper {

    /**
     * List of available blocks with their visibility status.
     *
     * @var array
     */
    private static $available_blocks = array(
        array(
            'name' => 'pearl-weather/vertical-card',
            'show' => true,
            'title' => 'Vertical Weather Card',
            'description' => 'A clean vertical card showing current weather and forecast.',
            'category' => 'weather',
        ),
        array(
            'name' => 'pearl-weather/horizontal-card',
            'show' => true,
            'title' => 'Horizontal Weather Card',
            'description' => 'A horizontal layout for compact weather display.',
            'category' => 'weather',
        ),
        array(
            'name' => 'pearl-weather/grid-card',
            'show' => true,
            'title' => 'Grid Weather Card',
            'description' => 'Display weather data in a responsive grid layout.',
            'category' => 'weather',
        ),
        array(
            'name' => 'pearl-weather/tabs-card',
            'show' => true,
            'title' => 'Tabs Weather Card',
            'description' => 'Weather information organized in tabs.',
            'category' => 'weather',
        ),
        array(
            'name' => 'pearl-weather/table-card',
            'show' => true,
            'title' => 'Table Weather Card',
            'description' => 'Weather forecast displayed in a table format.',
            'category' => 'weather',
        ),
        array(
            'name' => 'pearl-weather/accordion-card',
            'show' => true,
            'title' => 'Accordion Weather Card',
            'description' => 'Collapsible sections for weather details.',
            'category' => 'weather',
        ),
        array(
            'name' => 'pearl-weather/combined-card',
            'show' => true,
            'title' => 'Combined Weather Card',
            'description' => 'Multiple weather views combined.',
            'category' => 'weather',
        ),
        array(
            'name' => 'pearl-weather/aqi-minimal',
            'show' => true,
            'title' => 'AQI Minimal',
            'description' => 'Air Quality Index in a compact card.',
            'category' => 'air-quality',
        ),
        array(
            'name' => 'pearl-weather/aqi-detailed',
            'show' => true,
            'title' => 'AQI Detailed',
            'description' => 'Detailed air quality information.',
            'category' => 'air-quality',
        ),
        array(
            'name' => 'pearl-weather/weather-map',
            'show' => true,
            'title' => 'Weather Map',
            'description' => 'Interactive weather map.',
            'category' => 'maps',
        ),
        array(
            'name' => 'pearl-weather/windy-map',
            'show' => true,
            'title' => 'Windy Map',
            'description' => 'Windy.com interactive weather map.',
            'category' => 'maps',
        ),
        array(
            'name' => 'pearl-weather/section-heading',
            'show' => true,
            'title' => 'Section Heading',
            'description' => 'Heading block for weather sections.',
            'category' => 'layout',
        ),
        array(
            'name' => 'pearl-weather/historical-weather',
            'show' => true,
            'title' => 'Historical Weather',
            'description' => 'Display historical weather data.',
            'category' => 'historical',
        ),
        array(
            'name' => 'pearl-weather/sun-moon',
            'show' => true,
            'title' => 'Sun & Moon',
            'description' => 'Sunrise, sunset, moonrise, and moonset times.',
            'category' => 'astronomy',
        ),
        array(
            'name' => 'pearl-weather/historical-aqi',
            'show' => true,
            'title' => 'Historical AQI',
            'description' => 'Historical air quality data.',
            'category' => 'historical',
        ),
    );

    /**
     * Get all available blocks with their full data.
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_all_blocks() {
        /**
         * Filter the list of available blocks.
         *
         * @since 1.0.0
         * @param array $available_blocks List of blocks.
         */
        return apply_filters( 'pearl_weather_available_blocks', self::$available_blocks );
    }

    /**
     * Get all block names.
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_block_names() {
        $block_names = array();
        
        foreach ( self::$available_blocks as $block ) {
            $block_names[] = $block['name'];
        }
        
        return $block_names;
    }

    /**
     * Get blocks by category.
     *
     * @since 1.0.0
     * @param string $category Block category.
     * @return array
     */
    public static function get_blocks_by_category( $category ) {
        $filtered = array();
        
        foreach ( self::$available_blocks as $block ) {
            if ( isset( $block['category'] ) && $block['category'] === $category ) {
                $filtered[] = $block;
            }
        }
        
        return $filtered;
    }

    /**
     * Get block data by name.
     *
     * @since 1.0.0
     * @param string $block_name Block name.
     * @return array|null
     */
    public static function get_block_by_name( $block_name ) {
        foreach ( self::$available_blocks as $block ) {
            if ( $block['name'] === $block_name ) {
                return $block;
            }
        }
        
        return null;
    }

    /**
     * Get visible blocks (show = true).
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_visible_blocks() {
        $visible = array();
        
        foreach ( self::$available_blocks as $block ) {
            if ( isset( $block['show'] ) && true === $block['show'] ) {
                $visible[] = $block;
            }
        }
        
        return $visible;
    }

    /**
     * Get hidden blocks (show = false).
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_hidden_blocks() {
        $hidden = array();
        
        foreach ( self::$available_blocks as $block ) {
            if ( isset( $block['show'] ) && false === $block['show'] ) {
                $hidden[] = $block;
            }
        }
        
        return $hidden;
    }

    /**
     * Update block visibility.
     *
     * @since 1.0.0
     * @param string $block_name Block name.
     * @param bool   $visible    Visibility status.
     * @return bool
     */
    public static function set_block_visibility( $block_name, $visible ) {
        foreach ( self::$available_blocks as $key => $block ) {
            if ( $block['name'] === $block_name ) {
                self::$available_blocks[ $key ]['show'] = (bool) $visible;
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get block categories.
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_block_categories() {
        $categories = array();
        
        foreach ( self::$available_blocks as $block ) {
            if ( isset( $block['category'] ) && ! in_array( $block['category'], $categories, true ) ) {
                $categories[] = $block['category'];
            }
        }
        
        return $categories;
    }

    /**
     * Get pro blocks list (for upgrade notice).
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_pro_blocks() {
        // This would be populated with pro-only blocks.
        $pro_blocks = array(
            array(
                'name' => 'pearl-weather/advanced-map',
                'title' => 'Advanced Weather Map',
                'description' => 'Interactive map with multiple layers.',
            ),
            array(
                'name' => 'pearl-weather/weather-alerts',
                'title' => 'Weather Alerts',
                'description' => 'Display severe weather alerts.',
            ),
        );
        
        /**
         * Filter the list of pro blocks.
         *
         * @since 1.0.0
         * @param array $pro_blocks List of pro blocks.
         */
        return apply_filters( 'pearl_weather_pro_blocks', $pro_blocks );
    }
}