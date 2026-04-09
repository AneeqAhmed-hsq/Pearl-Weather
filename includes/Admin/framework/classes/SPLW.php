<?php
/**
 * Main Framework Class
 *
 * Initializes the framework, manages admin options, metaboxes,
 * field loading, and asset enqueuing.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Framework
 * @since      1.0.0
 */

namespace PearlWeather\Framework;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Framework
 *
 * Main framework controller.
 *
 * @since 1.0.0
 */
class Framework {

    /**
     * Framework version.
     *
     * @var string
     */
    public static $version = '1.0.0';

    /**
     * Framework directory path.
     *
     * @var string
     */
    public static $dir = '';

    /**
     * Framework URL.
     *
     * @var string
     */
    public static $url = '';

    /**
     * Collected CSS.
     *
     * @var string
     */
    public static $css = '';

    /**
     * Google Webfonts.
     *
     * @var array
     */
    public static $web_fonts = array();

    /**
     * Font subsets.
     *
     * @var array
     */
    public static $subsets = array();

    /**
     * Registered admin options.
     *
     * @var array
     */
    public static $admin_options = array();

    /**
     * Registered metabox options.
     *
     * @var array
     */
    public static $metabox_options = array();

    /**
     * Registered sections.
     *
     * @var array
     */
    public static $sections = array();

    /**
     * Initialized instances.
     *
     * @var array
     */
    public static $inited = array();

    /**
     * Available field types.
     *
     * @var array
     */
    public static $fields = array();

    /**
     * Initialize the framework.
     *
     * @since 1.0.0
     */
    public static function init() {
        do_action( 'pw_framework_init' );

        self::set_constants();
        self::include_files();

        add_action( 'after_setup_theme', array( __CLASS__, 'setup' ) );
        add_action( 'init', array( __CLASS__, 'setup' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
        add_action( 'wp_head', array( __CLASS__, 'output_custom_css' ), 80 );
        add_filter( 'admin_body_class', array( __CLASS__, 'add_admin_body_class' ) );
    }

    /**
     * Set directory and URL constants.
     *
     * @since 1.0.0
     */
    private static function set_constants() {
        self::$dir = PEARL_WEATHER_PATH . 'includes/framework/';
        self::$url = PEARL_WEATHER_URL . 'includes/framework/';
    }

    /**
     * Include required files.
     *
     * @since 1.0.0
     */
    private static function include_files() {
        // Helpers.
        require_once self::$dir . 'functions/helpers.php';
        require_once self::$dir . 'functions/sanitize.php';
        require_once self::$dir . 'functions/validate.php';

        // Core classes.
        require_once self::$dir . 'classes/abstract.class.php';
        require_once self::$dir . 'classes/fields.class.php';
        require_once self::$dir . 'classes/admin-options.class.php';
        require_once self::$dir . 'classes/metabox-options.class.php';
    }

    /**
     * Setup the framework.
     *
     * @since 1.0.0
     */
    public static function setup() {
        // Setup admin options.
        foreach ( self::$admin_options as $key => $args ) {
            if ( ! empty( self::$sections[ $key ] ) && ! isset( self::$inited[ $key ] ) ) {
                self::$inited[ $key ] = true;
                new AdminOptions( $key, array(
                    'args'     => $args,
                    'sections' => self::$sections[ $key ],
                ) );
            }
        }

        // Setup metabox options.
        foreach ( self::$metabox_options as $key => $args ) {
            if ( ! empty( self::$sections[ $key ] ) && ! isset( self::$inited[ $key ] ) ) {
                self::$inited[ $key ] = true;
                new MetaboxOptions( $key, array(
                    'args'     => $args,
                    'sections' => self::$sections[ $key ],
                ) );
            }
        }

        do_action( 'pw_framework_loaded' );
    }

    /**
     * Create admin options page.
     *
     * @since 1.0.0
     * @param string $id   Unique identifier.
     * @param array  $args Arguments.
     */
    public static function create_options( $id, $args = array() ) {
        self::$admin_options[ $id ] = $args;
    }

    /**
     * Create metabox.
     *
     * @since 1.0.0
     * @param string $id   Unique identifier.
     * @param array  $args Arguments.
     */
    public static function create_metabox( $id, $args = array() ) {
        self::$metabox_options[ $id ] = $args;
    }

    /**
     * Create section.
     *
     * @since 1.0.0
     * @param string $id       Parent identifier.
     * @param array  $sections Section configuration.
     */
    public static function create_section( $id, $sections ) {
        self::$sections[ $id ][] = $sections;
        self::collect_fields( $sections );
    }

    /**
     * Collect field types for autoloading.
     *
     * @since 1.0.0
     * @param array $sections Sections containing fields.
     */
    private static function collect_fields( $sections ) {
        if ( empty( $sections['fields'] ) ) {
            return;
        }

        foreach ( $sections['fields'] as $field ) {
            if ( ! empty( $field['fields'] ) ) {
                self::collect_fields( $field );
            }
            if ( ! empty( $field['tabs'] ) ) {
                self::collect_fields( array( 'fields' => $field['tabs'] ) );
            }
            if ( ! empty( $field['type'] ) ) {
                self::$fields[ $field['type'] ] = $field;
            }
        }
    }

    /**
     * Include field class file.
     *
     * @since 1.0.0
     * @param string $type Field type.
     */
    public static function maybe_include_field( $type ) {
        $class_name = 'PearlWeather\Framework\Fields\\' . ucfirst( $type ) . 'Field';
        
        if ( ! class_exists( $class_name ) ) {
            $field_file = self::$dir . 'fields/' . $type . '.php';
            if ( file_exists( $field_file ) ) {
                require_once $field_file;
            }
        }
    }

    /**
     * Render a framework field.
     *
     * @since 1.0.0
     * @param array  $field  Field configuration.
     * @param mixed  $value  Field value.
     * @param string $unique Unique identifier.
     * @param string $where  Context.
     * @param mixed  $parent Parent field.
     */
    public static function render_field( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        // Handle dependency.
        $depend = '';
        $visible = '';

        if ( ! empty( $field['dependency'] ) ) {
            $dependency = $field['dependency'];
            $depend .= ' data-controller="' . esc_attr( $dependency[0] ) . '"';
            $depend .= ' data-condition="' . esc_attr( $dependency[1] ) . '"';
            $depend .= ' data-value="' . esc_attr( $dependency[2] ) . '"';
            $visible = ! empty( $dependency[4] ) ? ' pw-depend-visible' : ' pw-depend-hidden';
        }

        $field_type = isset( $field['type'] ) ? $field['type'] : '';
        $class = isset( $field['class'] ) ? ' ' . $field['class'] : '';

        echo '<div class="pw-field pw-field-' . esc_attr( $field_type . $class . $visible ) . '"' . $depend . '>';

        // Render title.
        if ( ! empty( $field['title'] ) ) {
            echo '<div class="pw-field-title">';
            echo '<h4>' . wp_kses_post( $field['title'] ) . '</h4>';
            if ( ! empty( $field['subtitle'] ) ) {
                echo '<div class="pw-field-subtitle">' . wp_kses_post( $field['subtitle'] ) . '</div>';
            }
            echo '</div>';
        }

        echo '<div class="pw-field-content">';

        // Get field value.
        if ( ! isset( $value ) && isset( $field['default'] ) ) {
            $value = $field['default'];
        }

        // Include and instantiate field class.
        self::maybe_include_field( $field_type );
        $class_name = 'PearlWeather\Framework\Fields\\' . ucfirst( $field_type ) . 'Field';

        if ( class_exists( $class_name ) ) {
            $instance = new $class_name( $field, $value, $unique, $where, $parent );
            $instance->render();
        } else {
            echo '<p>' . esc_html__( 'Field type not found.', 'pearl-weather' ) . '</p>';
        }

        echo '</div>';
        echo '<div class="clear"></div>';
        echo '</div>';
    }

    /**
     * Enqueue admin assets.
     *
     * @since 1.0.0
     * @param string $hook Current admin page hook.
     */
    public static function enqueue_admin_assets( $hook ) {
        $enqueue = false;

        // Check if we're on a framework page.
        foreach ( self::$admin_options as $args ) {
            if ( strpos( $hook, $args['menu_slug'] ) !== false ) {
                $enqueue = true;
                break;
            }
        }

        foreach ( self::$metabox_options as $args ) {
            $screen = get_current_screen();
            if ( $screen && in_array( $screen->post_type, (array) $args['post_type'], true ) ) {
                $enqueue = true;
                break;
            }
        }

        if ( ! $enqueue ) {
            return;
        }

        $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        // Enqueue styles.
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'pw-framework', self::$url . 'assets/css/framework' . $min . '.css', array(), self::$version );

        // Enqueue scripts.
        wp_enqueue_media();
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'pw-framework-plugins', self::$url . 'assets/js/plugins' . $min . '.js', array( 'jquery' ), self::$version, true );
        wp_enqueue_script( 'pw-framework', self::$url . 'assets/js/framework' . $min . '.js', array( 'pw-framework-plugins' ), self::$version, true );

        // Localize script.
        wp_localize_script( 'pw-framework', 'pwFramework', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'pw_framework_nonce' ),
            'i18n'     => array(
                'confirm' => __( 'Are you sure?', 'pearl-weather' ),
            ),
        ) );

        // Enqueue field-specific assets.
        foreach ( self::$fields as $field ) {
            if ( ! empty( $field['type'] ) ) {
                $class_name = 'PearlWeather\Framework\Fields\\' . ucfirst( $field['type'] ) . 'Field';
                if ( class_exists( $class_name ) && method_exists( $class_name, 'enqueue' ) ) {
                    call_user_func( array( $class_name, 'enqueue' ) );
                }
            }
        }

        do_action( 'pw_framework_enqueue' );
    }

    /**
     * Output custom CSS.
     *
     * @since 1.0.0
     */
    public static function output_custom_css() {
        if ( ! empty( self::$css ) ) {
            echo '<style type="text/css">' . wp_strip_all_tags( self::$css ) . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    /**
     * Add admin body class.
     *
     * @since 1.0.0
     * @param string $classes Existing classes.
     * @return string
     */
    public static function add_admin_body_class( $classes ) {
        return $classes . ' pw-framework';
    }

    /**
     * Get plugin URL.
     *
     * @since 1.0.0
     * @param string $file File path relative to framework.
     * @return string
     */
    public static function get_url( $file ) {
        return self::$url . ltrim( $file, '/' );
    }
}

// Initialize the framework.
Framework::init();