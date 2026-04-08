<?php
/**
 * Premade Pattern Library Manager
 *
 * Handles fetching, caching, and managing premade design patterns
 * for weather blocks. Includes wishlist functionality and REST API endpoints.
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
 * Class PatternLibraryManager
 *
 * Manages premade pattern library for Gutenberg blocks.
 *
 * @since 1.0.0
 */
class PatternLibraryManager {

    /**
     * REST API namespace.
     */
    const API_NAMESPACE = 'pearl-weather/v1';

    /**
     * Remote pattern API URL.
     */
    const REMOTE_API_URL = 'https://demo.pearlweather.io/wp-json/pearl-weather/v1/pattern-list';

    /**
     * Cache directory name.
     */
    const CACHE_DIR = 'pearl-weather-cache';

    /**
     * Cache file name.
     */
    const CACHE_FILE = 'premade-patterns.json';

    /**
     * Cache expiration in days.
     */
    const CACHE_EXPIRATION_DAYS = 3;

    /**
     * Wishlist option name.
     */
    const WISHLIST_OPTION = 'pearl_weather_pattern_wishlist';

    /**
     * Constructor.
     * Registers REST API routes.
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
    }

    /**
     * Register REST API routes.
     *
     * @since 1.0.0
     */
    public function register_rest_routes() {
        // Get patterns endpoint.
        register_rest_route(
            self::API_NAMESPACE,
            '/patterns',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_patterns' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
            )
        );
        
        // Refresh patterns endpoint.
        register_rest_route(
            self::API_NAMESPACE,
            '/patterns/refresh',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'refresh_patterns' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
            )
        );
        
        // Wishlist endpoints.
        register_rest_route(
            self::API_NAMESPACE,
            '/wishlist',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_wishlist' ),
                'permission_callback' => array( $this, 'check_editor_permission' ),
            )
        );
        
        register_rest_route(
            self::API_NAMESPACE,
            '/wishlist',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'update_wishlist' ),
                'permission_callback' => array( $this, 'check_editor_permission' ),
                'args'                => $this->get_wishlist_args(),
            )
        );
    }

    /**
     * Get REST API arguments for wishlist endpoint.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_wishlist_args() {
        return array(
            'pattern_id' => array(
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function( $param ) {
                    return ! empty( $param );
                },
            ),
            'action' => array(
                'required'          => true,
                'type'              => 'string',
                'enum'              => array( 'add', 'remove' ),
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }

    /**
     * Check if user has admin permission.
     *
     * @since 1.0.0
     * @return bool
     */
    public function check_admin_permission() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Check if user has editor permission.
     *
     * @since 1.0.0
     * @return bool
     */
    public function check_editor_permission() {
        return current_user_can( 'edit_posts' );
    }

    /**
     * Get patterns (with caching).
     *
     * @since 1.0.0
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function get_patterns( $request ) {
        $force_refresh = $request->get_param( 'refresh' ) === 'true';
        
        // Check cache first.
        if ( ! $force_refresh && $this->is_cache_valid() ) {
            $cached_data = $this->get_cached_patterns();
            if ( $cached_data ) {
                return rest_ensure_response( array(
                    'success' => true,
                    'data'    => $cached_data,
                    'cached'  => true,
                ) );
            }
        }
        
        // Fetch fresh data.
        $result = $this->fetch_patterns_from_remote();
        
        if ( ! $result['success'] ) {
            // Try to return cached data even if expired.
            $cached_data = $this->get_cached_patterns();
            if ( $cached_data ) {
                return rest_ensure_response( array(
                    'success' => true,
                    'data'    => $cached_data,
                    'cached'  => true,
                    'warning' => $result['message'],
                ) );
            }
            
            return rest_ensure_response( array(
                'success' => false,
                'message' => $result['message'],
            ), 500 );
        }
        
        return rest_ensure_response( array(
            'success' => true,
            'data'    => $result['data'],
            'cached'  => false,
        ) );
    }

    /**
     * Refresh patterns manually.
     *
     * @since 1.0.0
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function refresh_patterns( $request ) {
        $result = $this->fetch_patterns_from_remote( true );
        
        if ( ! $result['success'] ) {
            return rest_ensure_response( array(
                'success' => false,
                'message' => $result['message'],
            ), 500 );
        }
        
        return rest_ensure_response( array(
            'success' => true,
            'data'    => $result['data'],
            'message' => __( 'Pattern library refreshed successfully.', 'pearl-weather' ),
        ) );
    }

    /**
     * Get user's wishlist.
     *
     * @since 1.0.0
     * @return \WP_REST_Response
     */
    public function get_wishlist() {
        $wishlist = get_user_meta( get_current_user_id(), self::WISHLIST_OPTION, true );
        
        if ( ! is_array( $wishlist ) ) {
            $wishlist = array();
        }
        
        return rest_ensure_response( array(
            'success'   => true,
            'wishlist'  => $wishlist,
        ) );
    }

    /**
     * Update user's wishlist (add/remove pattern).
     *
     * @since 1.0.0
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function update_wishlist( $request ) {
        $pattern_id = $request->get_param( 'pattern_id' );
        $action     = $request->get_param( 'action' );
        $user_id    = get_current_user_id();
        
        $wishlist = get_user_meta( $user_id, self::WISHLIST_OPTION, true );
        if ( ! is_array( $wishlist ) ) {
            $wishlist = array();
        }
        
        if ( 'add' === $action ) {
            if ( ! in_array( $pattern_id, $wishlist, true ) ) {
                $wishlist[] = $pattern_id;
                $message = __( 'Pattern added to wishlist.', 'pearl-weather' );
            } else {
                $message = __( 'Pattern already in wishlist.', 'pearl-weather' );
            }
        } else {
            $index = array_search( $pattern_id, $wishlist, true );
            if ( false !== $index ) {
                array_splice( $wishlist, $index, 1 );
                $message = __( 'Pattern removed from wishlist.', 'pearl-weather' );
            } else {
                $message = __( 'Pattern not found in wishlist.', 'pearl-weather' );
            }
        }
        
        update_user_meta( $user_id, self::WISHLIST_OPTION, $wishlist );
        
        return rest_ensure_response( array(
            'success'   => true,
            'message'   => $message,
            'wishlist'  => $wishlist,
        ) );
    }

    /**
     * Check if cache is still valid.
     *
     * @since 1.0.0
     * @return bool
     */
    private function is_cache_valid() {
        $cache_file = $this->get_cache_file_path();
        
        if ( ! file_exists( $cache_file ) ) {
            return false;
        }
        
        $cache_age = time() - filemtime( $cache_file );
        $expiration = self::CACHE_EXPIRATION_DAYS * DAY_IN_SECONDS;
        
        return $cache_age < $expiration;
    }

    /**
     * Get cached patterns from local file.
     *
     * @since 1.0.0
     * @return array|null
     */
    private function get_cached_patterns() {
        $cache_file = $this->get_cache_file_path();
        
        if ( ! file_exists( $cache_file ) ) {
            return null;
        }
        
        $content = $this->get_file_contents( $cache_file );
        
        if ( empty( $content ) ) {
            return null;
        }
        
        $data = json_decode( $content, true );
        
        return is_array( $data ) ? $data : null;
    }

    /**
     * Fetch patterns from remote API.
     *
     * @since 1.0.0
     * @param bool $force Whether to force fetch even if cached.
     * @return array
     */
    private function fetch_patterns_from_remote( $force = false ) {
        // Check if we should skip due to rate limiting.
        if ( ! $force && $this->is_rate_limited() ) {
            return array(
                'success' => false,
                'message' => __( 'Rate limit exceeded. Please try again later.', 'pearl-weather' ),
            );
        }
        
        $response = wp_remote_get( self::REMOTE_API_URL, array(
            'timeout'   => 30,
            'sslverify' => true,
            'headers'   => array(
                'Accept' => 'application/json',
            ),
        ) );
        
        if ( is_wp_error( $response ) ) {
            $this->record_failed_attempt();
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }
        
        $status_code = wp_remote_retrieve_response_code( $response );
        
        if ( 200 !== $status_code ) {
            $this->record_failed_attempt();
            return array(
                'success' => false,
                'message' => sprintf(
                    __( 'Remote server returned status code %d.', 'pearl-weather' ),
                    $status_code
                ),
            );
        }
        
        $body = wp_remote_retrieve_body( $response );
        
        if ( empty( $body ) ) {
            $this->record_failed_attempt();
            return array(
                'success' => false,
                'message' => __( 'Empty response from remote server.', 'pearl-weather' ),
            );
        }
        
        $data = json_decode( $body, true );
        
        if ( ! is_array( $data ) ) {
            $this->record_failed_attempt();
            return array(
                'success' => false,
                'message' => __( 'Invalid JSON response from remote server.', 'pearl-weather' ),
            );
        }
        
        // Cache the data.
        $this->cache_patterns( $body );
        $this->reset_rate_limit_counter();
        
        return array(
            'success' => true,
            'data'    => $data,
        );
    }

    /**
     * Cache patterns to local file.
     *
     * @since 1.0.0
     * @param string $content JSON content to cache.
     * @return bool
     */
    private function cache_patterns( $content ) {
        global $wp_filesystem;
        
        if ( ! $this->init_filesystem() ) {
            return false;
        }
        
        $cache_file = $this->get_cache_file_path();
        $cache_dir = dirname( $cache_file );
        
        // Create directory if it doesn't exist.
        if ( ! $wp_filesystem->is_dir( $cache_dir ) ) {
            wp_mkdir_p( $cache_dir );
            
            // Add index.php for security.
            $this->add_directory_index( $cache_dir );
        }
        
        return $wp_filesystem->put_contents( $cache_file, $content, FS_CHMOD_FILE );
    }

    /**
     * Get cache file path.
     *
     * @since 1.0.0
     * @return string
     */
    private function get_cache_file_path() {
        $upload_dir = wp_upload_dir();
        return trailingslashit( $upload_dir['basedir'] ) . self::CACHE_DIR . '/' . self::CACHE_FILE;
    }

    /**
     * Add directory index for security.
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
     * Get file contents using WP_Filesystem.
     *
     * @since 1.0.0
     * @param string $path File path.
     * @return string
     */
    private function get_file_contents( $path ) {
        global $wp_filesystem;
        
        if ( ! $this->init_filesystem() ) {
            return '';
        }
        
        if ( ! $wp_filesystem->exists( $path ) ) {
            return '';
        }
        
        return $wp_filesystem->get_contents( $path );
    }

    /**
     * Initialize WordPress filesystem.
     *
     * @since 1.0.0
     * @return bool
     */
    private function init_filesystem() {
        global $wp_filesystem;
        
        if ( $wp_filesystem ) {
            return true;
        }
        
        require_once ABSPATH . 'wp-admin/includes/file.php';
        
        $creds = request_filesystem_credentials( '', '', false, false, null );
        
        if ( ! WP_Filesystem( $creds ) ) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if rate limited (prevent excessive API calls).
     *
     * @since 1.0.0
     * @return bool
     */
    private function is_rate_limited() {
        $transient_key = 'pearl_weather_pattern_api_attempts';
        $attempts = get_transient( $transient_key );
        
        // Allow up to 5 attempts per hour.
        return $attempts && $attempts >= 5;
    }

    /**
     * Record a failed API attempt for rate limiting.
     *
     * @since 1.0.0
     */
    private function record_failed_attempt() {
        $transient_key = 'pearl_weather_pattern_api_attempts';
        $attempts = get_transient( $transient_key );
        
        if ( false === $attempts ) {
            set_transient( $transient_key, 1, HOUR_IN_SECONDS );
        } else {
            set_transient( $transient_key, $attempts + 1, HOUR_IN_SECONDS );
        }
    }

    /**
     * Reset rate limit counter on successful fetch.
     *
     * @since 1.0.0
     */
    private function reset_rate_limit_counter() {
        delete_transient( 'pearl_weather_pattern_api_attempts' );
    }
}

// Initialize the pattern library manager.
if ( ! function_exists( 'pearl_weather_init_pattern_library' ) ) {
    /**
     * Initialize pattern library manager.
     *
     * @since 1.0.0
     */
    function pearl_weather_init_pattern_library() {
        new PatternLibraryManager();
    }
    add_action( 'init', 'pearl_weather_init_pattern_library' );
}