<?php
/**
 * Gutenberg Blocks Integration
 *
 * Handles all Gutenberg block functionality for the Pearl Weather plugin,
 * including block registration, asset enqueuing, AJAX handlers, and dynamic CSS.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Blocks
 * @since      1.0.0
 */

namespace PearlWeather\Blocks;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class BlocksManager
 *
 * Main Gutenberg blocks management class.
 *
 * @since 1.0.0
 */
class BlocksManager {

    /**
     * Singleton instance.
     *
     * @var BlocksManager
     */
    private static $instance = null;

    /**
     * Block category slug.
     *
     * @var string
     */
    private $block_category_slug = 'pearl-weather';

    /**
     * Block category title.
     *
     * @var string
     */
    private $block_category_title = '';

    /**
     * Script suffix for minified files.
     *
     * @var string
     */
    private $script_suffix = '';

    /**
     * Plugin settings.
     *
     * @var array
     */
    private $settings = array();

    /**
     * Get singleton instance.
     *
     * @since 1.0.0
     * @return BlocksManager
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->init_properties();
        $this->init_hooks();
    }

    /**
     * Initialize class properties.
     *
     * @since 1.0.0
     */
    private function init_properties() {
        $this->script_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        $this->block_category_title = esc_html__( 'Pearl Weather', 'pearl-weather' );
        $this->settings = get_option( 'pearl_weather_settings', array() );
    }

    /**
     * Initialize WordPress hooks.
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'register_blocks' ) );
        add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );
        add_action( 'wp_ajax_pearl_weather_block_data', array( $this, 'ajax_get_block_data' ) );
        add_action( 'wp_ajax_nopriv_pearl_weather_block_data', array( $this, 'ajax_get_block_data' ) );
        add_action( 'wp_ajax_pearl_weather_block_colors', array( $this, 'ajax_save_block_colors' ) );
        add_action( 'wp_ajax_nopriv_pearl_weather_block_colors', array( $this, 'ajax_save_block_colors' ) );

        // Block category registration (compatible with WP 5.7 and below).
        if ( version_compare( get_bloginfo( 'version' ), '5.8', '<' ) ) {
            add_filter( 'block_categories', array( $this, 'register_block_category' ), 10, 2 );
        } else {
            add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );
        }

        // Extend REST API for random ordering.
        add_filter( 'rest_post_collection_params', array( $this, 'add_random_orderby_param' ), 10, 1 );
    }

    /**
     * Register Gutenberg blocks.
     *
     * @since 1.0.0
     */
    public function register_blocks() {
        // Only register if block editor is available.
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        // Register block editor script.
        wp_register_script(
            'pearl-weather-block-editor',
            PEARL_WEATHER_ASSETS_URL . 'blocks/build/index.js',
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch' ),
            PEARL_WEATHER_VERSION,
            true
        );

        // Register block editor styles.
        wp_register_style(
            'pearl-weather-block-editor',
            PEARL_WEATHER_ASSETS_URL . 'blocks/build/index.css',
            array( 'wp-edit-blocks' ),
            PEARL_WEATHER_VERSION
        );

        // Register frontend styles.
        wp_register_style(
            'pearl-weather-block-frontend',
            PEARL_WEATHER_ASSETS_URL . 'blocks/build/style-index.css',
            array(),
            PEARL_WEATHER_VERSION
        );

        // Register frontend script.
        wp_register_script(
            'pearl-weather-block-frontend',
            PEARL_WEATHER_ASSETS_URL . 'blocks/js/block-frontend' . $this->script_suffix . '.js',
            array( 'jquery' ),
            PEARL_WEATHER_VERSION,
            true
        );

        // Localize editor script.
        wp_localize_script(
            'pearl-weather-block-editor',
            'pearlWeatherBlock',
            array(
                'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                'nonce'         => wp_create_nonce( 'pearl_weather_block_nonce' ),
                'pluginUrl'     => PEARL_WEATHER_URL,
                'blockOptions'  => get_option( 'pearl_weather_block_options', array() ),
                'apiKey'        => ! empty( $this->settings['api_key'] ),
                'units'         => isset( $this->settings['units'] ) ? $this->settings['units'] : 'metric',
            )
        );

        // Localize frontend script.
        wp_localize_script(
            'pearl-weather-block-frontend',
            'pearlWeatherBlockFrontend',
            array(
                'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
                'postId'    => get_the_ID(),
                'nonce'     => wp_create_nonce( 'pearl_weather_block_nonce' ),
            )
        );

        // Register the actual block type.
        register_block_type(
            'pearl-weather/weather-widget',
            array(
                'editor_script'   => 'pearl-weather-block-editor',
                'editor_style'    => 'pearl-weather-block-editor',
                'style'           => 'pearl-weather-block-frontend',
                'script'          => 'pearl-weather-block-frontend',
                'render_callback' => array( $this, 'render_block' ),
                'attributes'      => $this->get_block_attributes(),
            )
        );
    }

    /**
     * Get block attributes definition.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_block_attributes() {
        return array(
            'widgetId' => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'layout' => array(
                'type'    => 'string',
                'default' => 'vertical',
            ),
            'showLocation' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'showTemperature' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'showDescription' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'showHumidity' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'showWind' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'customColors' => array(
                'type'    => 'object',
                'default' => array(),
            ),
        );
    }

    /**
     * Render callback for the block.
     *
     * @since 1.0.0
     * @param array  $attributes Block attributes.
     * @param string $content    Block content.
     * @return string
     */
    public function render_block( $attributes, $content ) {
        $widget_id = isset( $attributes['widgetId'] ) ? absint( $attributes['widgetId'] ) : 0;

        if ( empty( $widget_id ) ) {
            return '<div class="pearl-weather-block-error">' . esc_html__( 'No weather widget selected.', 'pearl-weather' ) . '</div>';
        }

        // Use shortcode to render the widget.
        return do_shortcode( sprintf( '[pearl-weather id="%d"]', $widget_id ) );
    }

    /**
     * Enqueue block assets.
     *
     * @since 1.0.0
     */
    public function enqueue_block_assets() {
        // Enqueue frontend styles if not in admin.
        if ( ! is_admin() ) {
            wp_enqueue_style( 'pearl-weather-block-frontend' );
        }
    }

    /**
     * AJAX handler for block data.
     *
     * @since 1.0.0
     */
    public function ajax_get_block_data() {
        // Verify nonce.
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'pearl_weather_block_nonce' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'pearl-weather' ) ), 403 );
        }

        // Get and decode form data.
        $form_data = isset( $_POST['weatherData'] ) ? sanitize_text_field( wp_unslash( $_POST['weatherData'] ) ) : '';
        $attributes = json_decode( $form_data, true );

        if ( ! is_array( $attributes ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid data format.', 'pearl-weather' ) ) );
        }

        $widget_id = isset( $attributes['widgetId'] ) ? absint( $attributes['widgetId'] ) : 0;

        if ( empty( $widget_id ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Widget ID is required.', 'pearl-weather' ) ) );
        }

        // Get weather data.
        $weather_data = $this->get_weather_data_for_block( $widget_id, $attributes );

        wp_send_json_success(
            array(
                'weatherData' => $weather_data,
                'attributes'  => $attributes,
            )
        );
    }

    /**
     * Get weather data for block preview.
     *
     * @since 1.0.0
     * @param int   $widget_id  Widget post ID.
     * @param array $attributes Block attributes.
     * @return array
     */
    private function get_weather_data_for_block( $widget_id, $attributes ) {
        // Get widget meta data.
        $location = get_post_meta( $widget_id, 'pearl_weather_location', true );
        $units = isset( $this->settings['units'] ) ? $this->settings['units'] : 'metric';

        if ( empty( $location ) ) {
            return array(
                'error' => esc_html__( 'Location not set for this widget.', 'pearl-weather' ),
            );
        }

        // Use API handler to fetch data.
        if ( class_exists( 'PearlWeather\API\WeatherAPI' ) ) {
            $api_handler = new \PearlWeather\API\WeatherAPI();
            $data = $api_handler->get_current_weather( $location, $units, true ); // Skip cache for preview.

            if ( $data ) {
                return $data;
            }
        }

        return array(
            'error' => esc_html__( 'Could not fetch weather data.', 'pearl-weather' ),
        );
    }

    /**
     * AJAX handler for saving block color settings.
     *
     * @since 1.0.0
     */
    public function ajax_save_block_colors() {
        // Verify nonce.
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'pearl_weather_block_nonce' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'pearl-weather' ) ), 403 );
        }

        // Check user capabilities.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to do this.', 'pearl-weather' ) ), 403 );
        }

        $color_data = isset( $_POST['colorSettings'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['colorSettings'] ) ), true ) : array();

        if ( is_array( $color_data ) ) {
            update_option( 'pearl_weather_block_colors', $color_data );
        }

        // Get theme colors for response.
        $theme_colors = $this->get_theme_colors();

        wp_send_json_success(
            array(
                'message'      => esc_html__( 'Colors saved successfully.', 'pearl-weather' ),
                'themeColors'  => $theme_colors,
                'customColors' => get_option( 'pearl_weather_block_colors', array() ),
            )
        );
    }

    /**
     * Get theme colors from global settings.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_theme_colors() {
        $theme_colors = array();

        if ( function_exists( 'wp_get_global_settings' ) ) {
            $global_settings = wp_get_global_settings();
            $theme_palette = isset( $global_settings['color']['palette']['theme'] ) ? $global_settings['color']['palette']['theme'] : array();
            $custom_palette = isset( $global_settings['color']['palette']['custom'] ) ? $global_settings['color']['palette']['custom'] : array();
            $theme_colors = array_merge( $theme_palette, $custom_palette );
        }

        return $theme_colors;
    }

    /**
     * Register block category.
     *
     * @since 1.0.0
     * @param array  $categories Existing categories.
     * @param object $post       Current post object.
     * @return array
     */
    public function register_block_category( $categories, $post ) {
        $category_exists = false;

        foreach ( $categories as $category ) {
            if ( $this->block_category_slug === $category['slug'] ) {
                $category_exists = true;
                break;
            }
        }

        if ( ! $category_exists ) {
            array_unshift(
                $categories,
                array(
                    'slug'  => $this->block_category_slug,
                    'title' => $this->block_category_title,
                )
            );
        }

        return $categories;
    }

    /**
     * Add random orderby parameter to REST API.
     *
     * @since 1.0.0
     * @param array $params Existing query parameters.
     * @return array
     */
    public function add_random_orderby_param( $params ) {
        if ( isset( $params['orderby']['enum'] ) && is_array( $params['orderby']['enum'] ) ) {
            $params['orderby']['enum'][] = 'rand';
            $params['orderby']['enum'][] = 'menu_order';
            $params['orderby']['enum'] = array_unique( $params['orderby']['enum'] );
        }
        return $params;
    }
}

// Initialize blocks manager.
if ( ! function_exists( 'pearl_weather_blocks_init' ) ) {
    /**
     * Initialize blocks.
     *
     * @since 1.0.0
     * @return BlocksManager
     */
    function pearl_weather_blocks_init() {
        return BlocksManager::get_instance();
    }
    pearl_weather_blocks_init();
}