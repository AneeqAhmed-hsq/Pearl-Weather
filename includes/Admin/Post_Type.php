<?php
/**
 * Custom Post Type Registration for Weather Widgets
 *
 * Registers the custom post type used to store weather widget configurations.
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
 * Class PostTypeRegistrar
 *
 * Handles registration of the weather widget custom post type.
 *
 * @since 1.0.0
 */
class PostTypeRegistrar {

    /**
     * Post type slug.
     */
    const POST_TYPE = 'pearl_weather_widget';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
    }

    /**
     * Register the weather widget post type.
     *
     * @since 1.0.0
     */
    public function register_post_type() {
        $capability = $this->get_required_capability();
        $show_ui = current_user_can( $capability );

        $labels = array(
            'name'               => __( 'Weather Widgets', 'pearl-weather' ),
            'singular_name'      => __( 'Weather Widget', 'pearl-weather' ),
            'menu_name'          => __( 'Pearl Weather', 'pearl-weather' ),
            'name_admin_bar'     => __( 'Weather Widget', 'pearl-weather' ),
            'add_new'            => __( 'Add New', 'pearl-weather' ),
            'add_new_item'       => __( 'Add New Weather Widget', 'pearl-weather' ),
            'new_item'           => __( 'New Weather Widget', 'pearl-weather' ),
            'edit_item'          => __( 'Edit Weather Widget', 'pearl-weather' ),
            'view_item'          => __( 'View Weather Widget', 'pearl-weather' ),
            'all_items'          => __( 'All Weather Widgets', 'pearl-weather' ),
            'search_items'       => __( 'Search Weather Widgets', 'pearl-weather' ),
            'parent_item_colon'  => __( 'Parent Weather Widget:', 'pearl-weather' ),
            'not_found'          => __( 'No weather widgets found.', 'pearl-weather' ),
            'not_found_in_trash' => __( 'No weather widgets found in Trash.', 'pearl-weather' ),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => $show_ui,
            'show_in_menu'        => $show_ui,
            'show_in_admin_bar'   => $show_ui,
            'show_in_nav_menus'   => false,
            'show_in_rest'        => false,
            'query_var'           => false,
            'rewrite'             => false,
            'capability_type'     => 'post',
            'capabilities'        => $this->get_capabilities( $capability ),
            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'supports'            => array( 'title' ),
            'menu_icon'           => 'dashicons-cloud',
            'menu_position'       => 25,
        );

        register_post_type( self::POST_TYPE, $args );
    }

    /**
     * Get the required capability for managing weather widgets.
     *
     * @since 1.0.0
     * @return string
     */
    private function get_required_capability() {
        /**
         * Filter the capability required to manage weather widgets.
         *
         * @since 1.0.0
         * @param string $capability The capability name. Default 'manage_options'.
         */
        return apply_filters( 'pearl_weather_widget_capability', 'manage_options' );
    }

    /**
     * Get capabilities array for the post type.
     *
     * @since 1.0.0
     * @param string $capability Base capability.
     * @return array
     */
    private function get_capabilities( $capability ) {
        return array(
            'edit_post'          => $capability,
            'read_post'          => $capability,
            'delete_post'        => $capability,
            'edit_posts'         => $capability,
            'edit_others_posts'  => $capability,
            'delete_posts'       => $capability,
            'publish_posts'      => $capability,
            'read_private_posts' => $capability,
            'create_posts'       => $capability,
        );
    }

    /**
     * Get all published weather widgets.
     *
     * @since 1.0.0
     * @param array $args Additional query arguments.
     * @return array
     */
    public static function get_widgets( $args = array() ) {
        $defaults = array(
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        $args = wp_parse_args( $args, $defaults );
        return get_posts( $args );
    }

    /**
     * Get widgets as options array for select dropdowns.
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_widget_options() {
        $widgets = self::get_widgets();
        $options = array( '' => __( '— Select Widget —', 'pearl-weather' ) );

        foreach ( $widgets as $widget ) {
            $options[ $widget->ID ] = $widget->post_title;
        }

        return $options;
    }
}

// Initialize post type registration.
new PostTypeRegistrar();