<?php
/**
 * Frontend Scripts & Styles Manager
 *
 * Handles enqueuing of CSS/JS files, dynamic CSS generation,
 * and shortcode-to-page relationship management.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Frontend
 * @since      1.0.0
 */

namespace PearlWeather\Frontend;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PearlWeather\Helpers;

/**
 * Class AssetsManager
 *
 * Manages frontend assets and dynamic styling.
 *
 * @since 1.0.0
 */
class AssetsManager {

    /**
     * Script suffix (minified or not).
     *
     * @var string
     */
    private $suffix = '';

    /**
     * Constructor.
     */
    public function __construct() {
        $this->suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 10 );
    }

    /**
     * Enqueue all frontend assets.
     *
     * @since 1.0.0
     */
    public function enqueue_assets() {
        $page_data = $this->get_page_shortcodes();
        $shortcode_ids = $page_data['shortcode_ids'];

        if ( empty( $shortcode_ids ) ) {
            return;
        }

        // Enqueue core styles.
        wp_enqueue_style( 'pearl-weather-icons' );
        wp_enqueue_style( 'pearl-weather-public' );
        wp_enqueue_style( 'pearl-weather-swiper' );

        // Enqueue core scripts.
        wp_enqueue_script( 'pearl-weather-public' );
        wp_enqueue_script( 'pearl-weather-swiper' );

        // Load dynamic CSS for each shortcode.
        $dynamic_css = $this->generate_dynamic_css( $shortcode_ids );
        
        if ( ! empty( $dynamic_css ) ) {
            wp_add_inline_style( 'pearl-weather-public', $dynamic_css );
        }

        // Add custom JS from settings.
        $this->add_custom_js();
    }

    /**
     * Get all shortcode IDs present on the current page.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_page_shortcodes() {
        $page_id = get_queried_object_id();
        $option_key = 'pw_page_shortcodes_' . $page_id;
        
        if ( is_multisite() ) {
            $option_key .= '_' . get_current_blog_id();
            $shortcode_ids = get_site_option( $option_key, array() );
        } else {
            $shortcode_ids = get_option( $option_key, array() );
        }

        // Ensure array and filter out invalid IDs.
        $shortcode_ids = is_array( $shortcode_ids ) ? $shortcode_ids : array();
        $shortcode_ids = array_filter( $shortcode_ids, 'is_numeric' );

        return array(
            'page_id'       => $page_id,
            'shortcode_ids' => $shortcode_ids,
            'option_key'    => $option_key,
        );
    }

    /**
     * Generate dynamic CSS for all shortcodes on the page.
     *
     * @since 1.0.0
     * @param array $shortcode_ids Array of shortcode/post IDs.
     * @return string
     */
    private function generate_dynamic_css( $shortcode_ids ) {
        $all_css = '';
        $custom_css = $this->get_global_custom_css();

        foreach ( $shortcode_ids as $shortcode_id ) {
            $post = get_post( $shortcode_id );
            
            if ( ! $post || 'trash' === $post->post_status ) {
                continue;
            }

            $settings = get_post_meta( $shortcode_id, 'pearl_weather_settings', true );
            
            if ( ! empty( $settings ) ) {
                $generator = new DynamicCSSGenerator( $shortcode_id, $settings );
                $all_css .= $generator->get_css();
            }
        }

        // Merge with global custom CSS.
        if ( ! empty( $custom_css ) ) {
            $all_css .= "\n/* Custom CSS */\n" . $custom_css;
        }

        return Helpers::minify_css( $all_css );
    }

    /**
     * Get global custom CSS from plugin settings.
     *
     * @since 1.0.0
     * @return string
     */
    private function get_global_custom_css() {
        $settings = get_option( 'pearl_weather_settings', array() );
        $custom_css = isset( $settings['custom_css'] ) ? wp_strip_all_tags( $settings['custom_css'] ) : '';
        
        return $custom_css;
    }

    /**
     * Add custom JavaScript from plugin settings.
     *
     * @since 1.0.0
     */
    private function add_custom_js() {
        $settings = get_option( 'pearl_weather_settings', array() );
        $custom_js = isset( $settings['custom_js'] ) ? $settings['custom_js'] : '';
        
        if ( ! empty( $custom_js ) ) {
            wp_add_inline_script( 'pearl-weather-public', $custom_js );
        }
    }

    /**
     * Register a shortcode on a page (called during shortcode rendering).
     *
     * @since 1.0.0
     * @param int $shortcode_id Shortcode/post ID.
     * @param int $page_id      Page ID (optional, defaults to current).
     */
    public static function register_shortcode_on_page( $shortcode_id, $page_id = null ) {
        if ( is_null( $page_id ) ) {
            $page_id = get_queried_object_id();
        }

        if ( empty( $page_id ) ) {
            return;
        }

        $option_key = 'pw_page_shortcodes_' . $page_id;
        
        if ( is_multisite() ) {
            $option_key .= '_' . get_current_blog_id();
            $existing = get_site_option( $option_key, array() );
        } else {
            $existing = get_option( $option_key, array() );
        }

        $existing = is_array( $existing ) ? $existing : array();

        if ( ! in_array( $shortcode_id, $existing, true ) ) {
            $existing[] = $shortcode_id;
            
            if ( is_multisite() ) {
                update_site_option( $option_key, $existing );
            } else {
                update_option( $option_key, $existing );
            }
        }
    }

    /**
     * Clean up page options when a shortcode is deleted.
     *
     * @since 1.0.0
     * @param int $shortcode_id Deleted shortcode ID.
     */
    public static function cleanup_shortcode_references( $shortcode_id ) {
        global $wpdb;
        
        $pattern = 'pw_page_shortcodes_%';
        
        if ( is_multisite() ) {
            $option_keys = $wpdb->get_col( $wpdb->prepare(
                "SELECT meta_key FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s",
                $pattern
            ) );
            
            foreach ( $option_keys as $key ) {
                $shortcodes = get_site_option( $key, array() );
                if ( is_array( $shortcodes ) ) {
                    $key_index = array_search( $shortcode_id, $shortcodes, true );
                    if ( false !== $key_index ) {
                        unset( $shortcodes[ $key_index ] );
                        update_site_option( $key, array_values( $shortcodes ) );
                    }
                }
            }
        } else {
            $option_keys = $wpdb->get_col( $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                $pattern
            ) );
            
            foreach ( $option_keys as $key ) {
                $shortcodes = get_option( $key, array() );
                if ( is_array( $shortcodes ) ) {
                    $key_index = array_search( $shortcode_id, $shortcodes, true );
                    if ( false !== $key_index ) {
                        unset( $shortcodes[ $key_index ] );
                        update_option( $key, array_values( $shortcodes ) );
                    }
                }
            }
        }
    }
}

// Hook cleanup on post deletion.
add_action( 'deleted_post', array( 'PearlWeather\Frontend\AssetsManager', 'cleanup_shortcode_references' ) );

/**
 * Helper function to minify CSS.
 */
if ( ! function_exists( 'minify_css' ) ) {
    /**
     * Minify CSS content.
     *
     * @param string $css CSS content.
     * @return string
     */
    function minify_css( $css ) {
        // Remove comments.
        $css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
        // Remove whitespace.
        $css = str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $css );
        // Remove multiple spaces.
        $css = preg_replace( '/\s+/', ' ', $css );
        // Remove spaces around brackets.
        $css = str_replace( array( '{ ', ' }', '( ', ' )' ), array( '{', '}', '(', ')' ), $css );
        
        return trim( $css );
    }
}