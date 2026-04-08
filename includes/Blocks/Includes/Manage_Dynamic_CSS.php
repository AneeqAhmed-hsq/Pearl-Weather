<?php
/**
 * Dynamic CSS Manager for Gutenberg Blocks
 *
 * Handles saving, enqueuing, and cleaning up dynamic CSS for all blocks.
 * Manages CSS file storage, Google Fonts loading, and REST API endpoints.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Blocks/Includes
 * @since      1.0.0
 */

namespace PearlWeather\Blocks\Includes;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class DynamicCSSManager
 *
 * Manages dynamic CSS generation, storage, and enqueuing for blocks.
 *
 * @since 1.0.0
 */
class DynamicCSSManager {

    /**
     * CSS directory name.
     */
    const CSS_DIR = 'pearl-weather-css';

    /**
     * REST API namespace.
     */
    const API_NAMESPACE = 'pearl-weather/v1';

    /**
     * Transient expiration for preview CSS (1 hour).
     */
    const PREVIEW_EXPIRATION = HOUR_IN_SECONDS;

    /**
     * Option prefix for widget CSS storage.
     */
    const WIDGET_CSS_PREFIX = '_pw_widget_css_';

    /**
     * Option prefix for widget fonts storage.
     */
    const WIDGET_FONTS_PREFIX = '_pw_widget_fonts_';

    /**
     * Option prefix for template CSS storage.
     */
    const TEMPLATE_CSS_PREFIX = '_pw_template_css_';

    /**
     * Option prefix for template fonts storage.
     */
    const TEMPLATE_FONTS_PREFIX = '_pw_template_fonts_';

    /**
     * Post meta key for CSS storage.
     */
    const POST_META_CSS = '_pw_dynamic_css';

    /**
     * Post meta key for fonts storage.
     */
    const POST_META_FONTS = '_pw_dynamic_fonts';

    /**
     * Constructor.
     * Initializes WordPress hooks.
     */
    public function __construct() {
        // REST API routes.
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

        // Frontend enqueuing (only for non-admin).
        if ( ! is_admin() ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_css' ), 20 );
        }

        // Cleanup on post deletion.
        add_action( 'deleted_post', array( $this, 'delete_post_css_files' ) );
        add_action( 'delete_post', array( $this, 'delete_post_css_files' ) );
    }

    /**
     * Register REST API routes for CSS saving.
     *
     * @since 1.0.0
     */
    public function register_rest_routes() {
        register_rest_route(
            self::API_NAMESPACE,
            '/save-block-css',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'save_block_css' ),
                'permission_callback' => array( $this, 'check_save_permission' ),
            )
        );
    }

    /**
     * Check permission for saving CSS.
     *
     * @since 1.0.0
     * @return bool
     */
    public function check_save_permission() {
        return current_user_can( 'edit_posts' );
    }

    /**
     * Save block CSS via REST API.
     *
     * @since 1.0.0
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function save_block_css( $request ) {
        $params = $request->get_params();

        // Sanitize inputs.
        $post_id        = isset( $params['post_id'] ) ? absint( $params['post_id'] ) : 0;
        $widget_id      = isset( $params['widget_id'] ) ? sanitize_text_field( $params['widget_id'] ) : '';
        $template_slug  = isset( $params['template_slug'] ) ? sanitize_text_field( $params['template_slug'] ) : '';
        $theme_slug     = isset( $params['theme_slug'] ) ? sanitize_text_field( $params['theme_slug'] ) : wp_get_theme()->get_stylesheet();
        $block_css      = isset( $params['css'] ) ? $this->sanitize_css( $params['css'] ) : '';
        $google_fonts   = isset( $params['fonts'] ) && is_array( $params['fonts'] ) ? array_map( 'sanitize_text_field', $params['fonts'] ) : array();
        $is_preview     = isset( $params['is_preview'] ) ? (bool) $params['is_preview'] : false;
        $has_block      = isset( $params['has_block'] ) ? (bool) $params['has_block'] : false;
        $block_type     = isset( $params['block_type'] ) ? sanitize_text_field( $params['block_type'] ) : '';
        $reusable_id    = isset( $params['reusable_id'] ) ? absint( $params['reusable_id'] ) : 0;

        // Handle widget CSS.
        if ( ! empty( $widget_id ) ) {
            return $this->save_widget_css( $widget_id, $block_css, $google_fonts, $has_block );
        }

        // Handle template CSS.
        if ( ! empty( $template_slug ) ) {
            return $this->save_template_css( $theme_slug, $template_slug, $block_css, $google_fonts, $has_block );
        }

        // Handle reusable block.
        if ( 'wp_block' === $block_type && $reusable_id > 0 ) {
            $post_id = $reusable_id;
        }

        // Handle preview.
        if ( $is_preview && $post_id > 0 ) {
            return $this->save_preview_css( $post_id, $block_css, $google_fonts );
        }

        // Handle regular post/page CSS.
        if ( $post_id > 0 ) {
            return $this->save_post_css( $post_id, $block_css, $google_fonts, $has_block );
        }

        return new \WP_REST_Response(
            array(
                'success' => false,
                'message' => __( 'Invalid parameters provided.', 'pearl-weather' ),
            ),
            400
        );
    }

    /**
     * Save widget CSS.
     *
     * @since 1.0.0
     * @param string $widget_id    Widget ID.
     * @param string $css          CSS content.
     * @param array  $fonts        Google Fonts.
     * @param bool   $has_block    Whether block exists.
     * @return \WP_REST_Response
     */
    private function save_widget_css( $widget_id, $css, $fonts, $has_block ) {
        $css_key  = self::WIDGET_CSS_PREFIX . $widget_id;
        $fonts_key = self::WIDGET_FONTS_PREFIX . $widget_id;

        if ( $has_block && ! empty( $css ) ) {
            update_option( $css_key, $css );
            update_option( $fonts_key, $fonts );
        } else {
            delete_option( $css_key );
            delete_option( $fonts_key );
        }

        return new \WP_REST_Response(
            array(
                'success' => true,
                'message' => __( 'Widget CSS saved successfully.', 'pearl-weather' ),
            )
        );
    }

    /**
     * Save template CSS.
     *
     * @since 1.0.0
     * @param string $theme_slug    Theme slug.
     * @param string $template_slug Template slug.
     * @param string $css           CSS content.
     * @param array  $fonts         Google Fonts.
     * @param bool   $has_block     Whether block exists.
     * @return \WP_REST_Response
     */
    private function save_template_css( $theme_slug, $template_slug, $css, $fonts, $has_block ) {
        $css_key  = self::TEMPLATE_CSS_PREFIX . $theme_slug . '_' . $template_slug;
        $fonts_key = self::TEMPLATE_FONTS_PREFIX . $theme_slug . '_' . $template_slug;

        if ( $has_block && ! empty( $css ) ) {
            update_option( $css_key, $css );
            update_option( $fonts_key, $fonts );
        } else {
            delete_option( $css_key );
            delete_option( $fonts_key );
        }

        return new \WP_REST_Response(
            array(
                'success' => true,
                'message' => __( 'Template CSS saved successfully.', 'pearl-weather' ),
            )
        );
    }

    /**
     * Save preview CSS as transient.
     *
     * @since 1.0.0
     * @param int    $post_id Post ID.
     * @param string $css     CSS content.
     * @param array  $fonts   Google Fonts.
     * @return \WP_REST_Response
     */
    private function save_preview_css( $post_id, $css, $fonts ) {
        set_transient( '_pw_preview_css_' . $post_id, $css, self::PREVIEW_EXPIRATION );
        set_transient( '_pw_preview_fonts_' . $post_id, $fonts, self::PREVIEW_EXPIRATION );

        return new \WP_REST_Response(
            array(
                'success' => true,
                'message' => __( 'Preview CSS saved temporarily.', 'pearl-weather' ),
            )
        );
    }

    /**
     * Save post/page CSS to file.
     *
     * @since 1.0.0
     * @param int    $post_id   Post ID.
     * @param string $css       CSS content.
     * @param array  $fonts     Google Fonts.
     * @param bool   $has_block Whether block exists.
     * @return \WP_REST_Response
     */
    private function save_post_css( $post_id, $css, $fonts, $has_block ) {
        if ( $has_block && ! empty( $css ) ) {
            // Save CSS to file.
            $file_saved = $this->save_css_to_file( $post_id, $css );
            
            if ( $file_saved ) {
                update_post_meta( $post_id, self::POST_META_CSS, $css );
                update_post_meta( $post_id, self::POST_META_FONTS, $fonts );
                
                return new \WP_REST_Response(
                    array(
                        'success' => true,
                        'message' => __( 'CSS file saved successfully.', 'pearl-weather' ),
                    )
                );
            } else {
                return new \WP_REST_Response(
                    array(
                        'success' => false,
                        'message' => __( 'Could not save CSS file due to permission issues.', 'pearl-weather' ),
                    ),
                    500
                );
            }
        } else {
            // Delete CSS when no blocks exist.
            $this->delete_post_css_files( $post_id );
            delete_post_meta( $post_id, self::POST_META_CSS );
            delete_post_meta( $post_id, self::POST_META_FONTS );
            
            return new \WP_REST_Response(
                array(
                    'success' => true,
                    'message' => __( 'CSS file deleted successfully.', 'pearl-weather' ),
                )
            );
        }
    }

    /**
     * Save CSS content to file in uploads directory.
     *
     * @since 1.0.0
     * @param int    $post_id Post ID.
     * @param string $css     CSS content.
     * @return bool
     */
    private function save_css_to_file( $post_id, $css ) {
        global $wp_filesystem;
        
        if ( ! $wp_filesystem ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }
        
        $upload_dir = wp_upload_dir();
        $css_dir = trailingslashit( $upload_dir['basedir'] ) . self::CSS_DIR;
        
        // Create directory if it doesn't exist.
        if ( ! $wp_filesystem->is_dir( $css_dir ) ) {
            $wp_filesystem->mkdir( $css_dir, FS_CHMOD_DIR );
        }
        
        // Add directory index for security.
        $this->add_directory_index( $css_dir );
        
        $filename = "pw-block-{$post_id}.css";
        $filepath = trailingslashit( $css_dir ) . $filename;
        
        // Minify CSS before saving.
        $minified_css = $this->minify_css( $css );
        
        return $wp_filesystem->put_contents( $filepath, $minified_css, FS_CHMOD_FILE );
    }

    /**
     * Add index.php to directory for security.
     *
     * @since 1.0.0
     * @param string $dir Directory path.
     */
    private function add_directory_index( $dir ) {
        global $wp_filesystem;
        
        $index_file = trailingslashit( $dir ) . 'index.php';
        
        if ( ! $wp_filesystem->exists( $index_file ) ) {
            $wp_filesystem->put_contents( $index_file, '<?php // Silence is golden.', FS_CHMOD_FILE );
        }
    }

    /**
     * Minify CSS content.
     *
     * @since 1.0.0
     * @param string $css CSS content.
     * @return string Minified CSS.
     */
    private function minify_css( $css ) {
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

    /**
     * Sanitize CSS content.
     *
     * @since 1.0.0
     * @param string $css CSS content.
     * @return string Sanitized CSS.
     */
    private function sanitize_css( $css ) {
        // Remove potential HTML tags.
        $css = wp_strip_all_tags( $css );
        
        // Remove @import rules for security.
        $css = preg_replace( '/@import[^;]+;/', '', $css );
        
        return $css;
    }

    /**
     * Enqueue frontend dynamic CSS and Google Fonts.
     *
     * @since 1.0.0
     */
    public function enqueue_frontend_css() {
        global $post, $wp_registered_sidebars;
        
        $post_id = isset( $post->ID ) ? $post->ID : 0;
        $all_css = '';
        $all_fonts = array();
        
        // Helper to merge assets.
        $merge_assets = function( $css, $fonts ) use ( &$all_css, &$all_fonts ) {
            if ( ! empty( $css ) ) {
                $all_css .= "\n" . $css;
            }
            if ( ! empty( $fonts ) && is_array( $fonts ) ) {
                $all_fonts = array_merge( $all_fonts, $fonts );
            }
        };
        
        // 1. Preview CSS.
        if ( is_preview() && $post_id ) {
            $preview_css = get_transient( '_pw_preview_css_' . $post_id );
            $preview_fonts = get_transient( '_pw_preview_fonts_' . $post_id );
            $merge_assets( $preview_css, $preview_fonts );
        }
        
        // 2. Widget CSS.
        $this->collect_widget_css( $merge_assets );
        
        // 3. Template CSS.
        $this->collect_template_css( $merge_assets );
        
        // 4. Post/Page CSS.
        if ( is_singular() && $post_id ) {
            $this->collect_post_css( $post_id, $merge_assets );
            
            // Also collect CSS from reusable blocks inside the post.
            $reusable_ids = $this->get_reusable_block_ids( $post_id );
            foreach ( $reusable_ids as $ref_id ) {
                $this->collect_post_css( $ref_id, $merge_assets );
            }
        }
        
        // 5. Enqueue inline CSS.
        if ( ! empty( $all_css ) ) {
            wp_add_inline_style( 'pearl-weather-block-frontend', $all_css );
        }
        
        // 6. Enqueue Google Fonts.
        $this->enqueue_google_fonts( $all_fonts );
    }

    /**
     * Collect CSS from widgets.
     *
     * @since 1.0.0
     * @param callable $merge_assets Merge callback.
     */
    private function collect_widget_css( $merge_assets ) {
        $sidebars_widgets = get_option( 'sidebars_widgets', array() );
        
        if ( empty( $sidebars_widgets ) || ! is_array( $sidebars_widgets ) ) {
            return;
        }
        
        foreach ( $sidebars_widgets as $sidebar => $widgets ) {
            if ( 'wp_inactive_widgets' === $sidebar || empty( $widgets ) || ! is_array( $widgets ) ) {
                continue;
            }
            
            foreach ( $widgets as $widget_id ) {
                $css = get_option( self::WIDGET_CSS_PREFIX . $widget_id, '' );
                $fonts = get_option( self::WIDGET_FONTS_PREFIX . $widget_id, array() );
                $merge_assets( $css, $fonts );
            }
        }
    }

    /**
     * Collect CSS from templates.
     *
     * @since 1.0.0
     * @param callable $merge_assets Merge callback.
     */
    private function collect_template_css( $merge_assets ) {
        $theme_slug = wp_get_theme()->get_stylesheet();
        $templates = array( 'header', 'footer', 'sidebar' );
        
        // Add conditional templates.
        if ( is_home() ) {
            $templates[] = 'home';
        } elseif ( is_archive() ) {
            $templates[] = 'archive';
        } elseif ( is_single() ) {
            $templates[] = 'single';
        } elseif ( is_page() ) {
            $templates[] = 'page';
        }
        
        foreach ( $templates as $template ) {
            $css = get_option( self::TEMPLATE_CSS_PREFIX . $theme_slug . '_' . $template, '' );
            $fonts = get_option( self::TEMPLATE_FONTS_PREFIX . $theme_slug . '_' . $template, array() );
            $merge_assets( $css, $fonts );
        }
    }

    /**
     * Collect CSS from a post/page.
     *
     * @since 1.0.0
     * @param int      $post_id      Post ID.
     * @param callable $merge_assets Merge callback.
     */
    private function collect_post_css( $post_id, $merge_assets ) {
        $upload_dir = wp_upload_dir();
        $css_file = trailingslashit( $upload_dir['basedir'] ) . self::CSS_DIR . "/pw-block-{$post_id}.css";
        
        if ( file_exists( $css_file ) ) {
            $css_url = trailingslashit( $upload_dir['baseurl'] ) . self::CSS_DIR . "/pw-block-{$post_id}.css";
            wp_enqueue_style(
                "pw-dynamic-{$post_id}",
                $css_url,
                array(),
                filemtime( $css_file )
            );
            
            // Also collect fonts from post meta.
            $fonts = get_post_meta( $post_id, self::POST_META_FONTS, true );
            if ( ! empty( $fonts ) && is_array( $fonts ) ) {
                $merge_assets( '', $fonts );
            }
        } else {
            $css = get_post_meta( $post_id, self::POST_META_CSS, true );
            $fonts = get_post_meta( $post_id, self::POST_META_FONTS, true );
            $merge_assets( $css, $fonts );
        }
    }

    /**
     * Get reusable block IDs from post content.
     *
     * @since 1.0.0
     * @param int $post_id Post ID.
     * @return array
     */
    private function get_reusable_block_ids( $post_id ) {
        $reusable_ids = array();
        $post = get_post( $post_id );
        
        if ( ! $post || empty( $post->post_content ) ) {
            return $reusable_ids;
        }
        
        if ( has_blocks( $post->post_content ) ) {
            $blocks = parse_blocks( $post->post_content );
            
            foreach ( $blocks as $block ) {
                if ( isset( $block['blockName'] ) && 'core/block' === $block['blockName'] ) {
                    if ( isset( $block['attrs']['ref'] ) ) {
                        $reusable_ids[] = absint( $block['attrs']['ref'] );
                    }
                }
            }
        }
        
        return array_unique( $reusable_ids );
    }

    /**
     * Enqueue Google Fonts.
     *
     * @since 1.0.0
     * @param array $fonts Array of font strings.
     */
    private function enqueue_google_fonts( $fonts ) {
        if ( empty( $fonts ) ) {
            return;
        }
        
        // Flatten and unique fonts.
        $flat_fonts = array();
        array_walk_recursive( $fonts, function( $value ) use ( &$flat_fonts ) {
            if ( is_string( $value ) && ! empty( $value ) ) {
                $flat_fonts[] = $value;
            }
        } );
        $flat_fonts = array_unique( $flat_fonts );
        
        foreach ( $flat_fonts as $font ) {
            $parts = explode( ':', $font );
            $font_name = str_replace( ' ', '+', $parts[0] );
            $font_weights = isset( $parts[1] ) ? $parts[1] : '400';
            
            wp_enqueue_style(
                'pw-font-' . sanitize_title( $font_name ),
                '//fonts.googleapis.com/css2?family=' . $font_name . ':wght@' . $font_weights . '&display=swap',
                array(),
                PEARL_WEATHER_VERSION
            );
        }
    }

    /**
     * Delete CSS files when a post is deleted.
     *
     * @since 1.0.0
     * @param int $post_id Post ID.
     */
    public function delete_post_css_files( $post_id ) {
        $upload_dir = wp_upload_dir();
        $css_dir = trailingslashit( $upload_dir['basedir'] ) . self::CSS_DIR;
        $css_file = trailingslashit( $css_dir ) . "pw-block-{$post_id}.css";
        
        if ( file_exists( $css_file ) ) {
            wp_delete_file( $css_file );
        }
        
        // Also delete transient preview CSS.
        delete_transient( '_pw_preview_css_' . $post_id );
        delete_transient( '_pw_preview_fonts_' . $post_id );
        
        // Remove empty directory.
        if ( is_dir( $css_dir ) && $this->is_dir_empty( $css_dir ) ) {
            rmdir( $css_dir );
        }
    }

    /**
     * Check if directory is empty.
     *
     * @since 1.0.0
     * @param string $dir Directory path.
     * @return bool
     */
    private function is_dir_empty( $dir ) {
        if ( ! is_readable( $dir ) ) {
            return false;
        }
        
        $files = array_diff( scandir( $dir ), array( '.', '..', 'index.php' ) );
        return empty( $files );
    }
}

// Initialize the dynamic CSS manager.
new DynamicCSSManager();