<?php
/**
 * Saved Templates Handler for Weather Blocks
 *
 * Manages saved/reusable template patterns for weather blocks.
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
 * Class SavedTemplatesManager
 *
 * Handles registration and management of saved weather templates.
 *
 * @since 1.0.0
 */
class SavedTemplatesManager {

    /**
     * Custom post type for saved templates.
     */
    const POST_TYPE = 'pw_weather_template';

    /**
     * Shortcode tag for saved templates.
     */
    const SHORTCODE_TAG = 'pearl_weather_template';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'admin_init', array( $this, 'handle_redirects' ) );
        add_filter( 'use_block_editor_for_post_type', array( $this, 'force_block_editor' ), 10, 2 );
        add_shortcode( self::SHORTCODE_TAG, array( $this, 'render_shortcode' ) );
    }

    /**
     * Register the saved template custom post type.
     *
     * @since 1.0.0
     */
    public function register_post_type() {
        if ( post_type_exists( self::POST_TYPE ) ) {
            return;
        }

        $show_ui = current_user_can( 'manage_options' );

        $labels = array(
            'name'                     => __( 'Saved Templates', 'pearl-weather' ),
            'singular_name'            => __( 'Saved Template', 'pearl-weather' ),
            'menu_name'                => __( 'Saved Templates', 'pearl-weather' ),
            'all_items'                => __( 'All Templates', 'pearl-weather' ),
            'add_new'                  => __( 'Add New Template', 'pearl-weather' ),
            'add_new_item'             => __( 'Add New Template', 'pearl-weather' ),
            'edit_item'                => __( 'Edit Template', 'pearl-weather' ),
            'new_item'                 => __( 'New Template', 'pearl-weather' ),
            'view_item'                => __( 'View Template', 'pearl-weather' ),
            'search_items'             => __( 'Search Templates', 'pearl-weather' ),
            'not_found'                => __( 'No templates found', 'pearl-weather' ),
            'not_found_in_trash'       => __( 'No templates found in trash', 'pearl-weather' ),
            'item_published'           => __( 'Template published', 'pearl-weather' ),
            'item_published_privately' => __( 'Template published privately', 'pearl-weather' ),
            'item_reverted_to_draft'   => __( 'Template reverted to draft', 'pearl-weather' ),
            'item_scheduled'           => __( 'Template scheduled', 'pearl-weather' ),
            'item_updated'             => __( 'Template updated', 'pearl-weather' ),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => $show_ui,
            'show_in_menu'        => false,
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => false,
            'show_in_rest'        => true, // Required for block editor.
            'rest_base'           => 'weather-templates',
            'supports'            => array( 'title', 'editor', 'revisions' ),
            'hierarchical'        => false,
            'rewrite'             => false,
            'query_var'           => false,
            'can_export'          => true,
            'capability_type'     => 'post',
            'capabilities'        => array(
                'edit_post'          => 'edit_post',
                'read_post'          => 'read_post',
                'delete_post'        => 'delete_post',
                'edit_posts'         => 'edit_posts',
                'edit_others_posts'  => 'edit_others_posts',
                'delete_posts'       => 'delete_posts',
                'publish_posts'      => 'publish_posts',
                'read_private_posts' => 'read_private_posts',
            ),
        );

        register_post_type( self::POST_TYPE, $args );
    }

    /**
     * Handle redirects for saved template admin pages.
     *
     * @since 1.0.0
     */
    public function handle_redirects() {
        global $pagenow;

        // Check if we're editing a saved template post.
        if ( 'post.php' === $pagenow && isset( $_GET['post'], $_GET['action'] ) ) {
            $post_id = absint( $_GET['post'] );
            $action = sanitize_text_field( wp_unslash( $_GET['action'] ) );

            if ( 'edit' === $action && self::POST_TYPE === get_post_type( $post_id ) ) {
                return; // Allow editing.
            }
        }

        // Check if we're creating a new saved template.
        if ( 'post-new.php' === $pagenow ) {
            if ( isset( $_GET['post_type'] ) && self::POST_TYPE === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) {
                return; // Allow creation.
            }
        }

        // Redirect if accessing the templates list directly.
        if ( isset( $_GET['post_type'] ) && self::POST_TYPE === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) {
            $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
            if ( 'pw_admin_dashboard#saved_templates' !== $page ) {
                wp_safe_redirect( admin_url( 'admin.php?page=pw_admin_dashboard#saved_templates' ) );
                exit;
            }
        }
    }

    /**
     * Force block editor for saved templates.
     *
     * @since 1.0.0
     * @param bool   $use_block_editor Whether to use block editor.
     * @param string $post_type        Post type.
     * @return bool
     */
    public function force_block_editor( $use_block_editor, $post_type ) {
        if ( self::POST_TYPE === $post_type ) {
            return true;
        }
        return $use_block_editor;
    }

    /**
     * Render saved template shortcode.
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function render_shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            self::SHORTCODE_TAG
        );

        $template_id = absint( $atts['id'] );

        if ( empty( $template_id ) ) {
            return '';
        }

        // Don't render in Elementor editor mode.
        if ( $this->is_elementor_edit_mode() ) {
            return '[pearl_weather_template id="' . $template_id . '"]';
        }

        $template = get_post( $template_id );

        if ( ! $template || 'publish' !== $template->post_status || self::POST_TYPE !== $template->post_type ) {
            return '';
        }

        $content = $template->post_content;

        // Process blocks and shortcodes.
        $content = do_blocks( $content );
        $content = do_shortcode( $content );
        $content = str_replace( ']]>', ']]&gt;', $content );
        $content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content );
        $content = preg_replace( '/^(?:<br\s*\/?>\s*)+/', '', $content );

        // Enqueue necessary assets.
        wp_enqueue_style( 'pearl-weather-block-frontend' );
        wp_enqueue_script( 'pearl-weather-block-frontend' );

        // Enqueue dynamic CSS for this template.
        $this->enqueue_dynamic_css( $template_id );

        return $content;
    }

    /**
     * Enqueue dynamic CSS for a saved template.
     *
     * @since 1.0.0
     * @param int $template_id Template ID.
     */
    private function enqueue_dynamic_css( $template_id ) {
        $upload_dir = wp_upload_dir();
        $css_dir = trailingslashit( $upload_dir['basedir'] ) . 'pw-weather-css/';
        $css_file = $css_dir . "pw-template-{$template_id}.css";

        if ( file_exists( $css_file ) ) {
            $css_url = trailingslashit( $upload_dir['baseurl'] ) . "pw-weather-css/pw-template-{$template_id}.css";
            wp_enqueue_style(
                "pw-template-dynamic-{$template_id}",
                $css_url,
                array( 'pearl-weather-block-frontend' ),
                filemtime( $css_file )
            );
        } else {
            $inline_css = get_post_meta( $template_id, '_pw_dynamic_css', true );
            if ( ! empty( $inline_css ) ) {
                wp_add_inline_style( 'pearl-weather-block-frontend', $inline_css );
            }
        }
    }

    /**
     * Check if we're in Elementor edit mode.
     *
     * @since 1.0.0
     * @return bool
     */
    private function is_elementor_edit_mode() {
        if ( ! class_exists( '\Elementor\Plugin' ) ) {
            return false;
        }

        return \Elementor\Plugin::$instance->editor->is_edit_mode();
    }

    /**
     * Get all saved templates.
     *
     * @since 1.0.0
     * @param array $args Additional arguments.
     * @return array
     */
    public static function get_templates( $args = array() ) {
        $defaults = array(
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        $args = wp_parse_args( $args, $defaults );
        $templates = get_posts( $args );

        return $templates;
    }

    /**
     * Get templates as options array for select dropdown.
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_template_options() {
        $templates = self::get_templates();
        $options = array( '' => __( '— Select Template —', 'pearl-weather' ) );

        foreach ( $templates as $template ) {
            $options[ $template->ID ] = $template->post_title;
        }

        return $options;
    }
}

// Initialize saved templates manager.
new SavedTemplatesManager();