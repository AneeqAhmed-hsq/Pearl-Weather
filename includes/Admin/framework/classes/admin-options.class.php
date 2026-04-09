<?php
/**
 * Framework Admin Options Class
 *
 * Handles the entire admin settings page including menu registration,
 * field rendering, AJAX saving, and database storage.
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
 * Class AdminOptions
 *
 * Manages plugin admin settings page.
 *
 * @since 1.0.0
 */
class AdminOptions extends BaseFramework {

    /**
     * Unique identifier.
     *
     * @var string
     */
    private $unique = '';

    /**
     * Notice message.
     *
     * @var string
     */
    private $notice = '';

    /**
     * Sections configuration.
     *
     * @var array
     */
    private $sections = array();

    /**
     * Options values.
     *
     * @var array
     */
    private $options = array();

    /**
     * Validation errors.
     *
     * @var array
     */
    private $errors = array();

    /**
     * Pre-processed tabs.
     *
     * @var array
     */
    private $pre_tabs = array();

    /**
     * Pre-processed fields.
     *
     * @var array
     */
    private $pre_fields = array();

    /**
     * Pre-processed sections.
     *
     * @var array
     */
    private $pre_sections = array();

    /**
     * Default arguments.
     *
     * @var array
     */
    private $default_args = array(
        'framework_title'         => '',
        'framework_class'         => '',
        'menu_title'              => '',
        'menu_slug'               => '',
        'menu_type'               => 'menu',
        'menu_capability'         => 'manage_options',
        'menu_icon'               => null,
        'menu_position'           => null,
        'menu_hidden'             => false,
        'menu_parent'             => '',
        'sub_menu_title'          => '',
        'show_bar_menu'           => false,
        'show_sub_menu'           => true,
        'show_in_network'         => true,
        'show_search'             => true,
        'show_reset_all'          => true,
        'show_reset_section'      => true,
        'show_footer'             => true,
        'show_all_options'        => true,
        'show_form_warning'       => true,
        'sticky_header'           => true,
        'save_defaults'           => true,
        'ajax_save'               => true,
        'database'                => 'options',
        'transient_time'          => 0,
        'enqueue_webfont'         => true,
        'async_webfont'           => false,
        'output_css'              => true,
        'theme'                   => 'dark',
        'class'                   => '',
    );

    /**
     * Constructor.
     *
     * @param string $key    Unique identifier.
     * @param array  $params Configuration parameters.
     */
    public function __construct( $key, $params = array() ) {
        $this->unique = $key;
        $this->args = wp_parse_args( $params['args'], $this->default_args );
        $this->sections = $params['sections'];

        $this->pre_tabs = $this->process_tabs( $this->sections );
        $this->pre_fields = $this->process_fields( $this->sections );
        $this->pre_sections = $this->process_sections( $this->sections );

        $this->get_options();
        $this->save_defaults();

        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 50 );
        add_action( 'wp_ajax_pw_' . $this->unique . '_save', array( $this, 'ajax_save' ) );

        if ( 'network' === $this->args['database'] && ! empty( $this->args['show_in_network'] ) ) {
            add_action( 'network_admin_menu', array( $this, 'add_admin_menu' ) );
        }

        parent::__construct();
    }

    /**
     * Process tabs from sections.
     *
     * @param array $sections Sections array.
     * @return array
     */
    private function process_tabs( $sections ) {
        // Implementation for processing tabs.
        return $sections;
    }

    /**
     * Process fields from sections.
     *
     * @param array $sections Sections array.
     * @return array
     */
    private function process_fields( $sections ) {
        $fields = array();
        foreach ( $sections as $section ) {
            if ( ! empty( $section['fields'] ) ) {
                $fields = array_merge( $fields, $section['fields'] );
            }
        }
        return $fields;
    }

    /**
     * Process sections.
     *
     * @param array $sections Sections array.
     * @return array
     */
    private function process_sections( $sections ) {
        return $sections;
    }

    /**
     * Get options from database.
     */
    private function get_options() {
        $this->options = get_option( $this->unique, array() );
    }

    /**
     * Save default values.
     */
    private function save_defaults() {
        if ( ! $this->args['save_defaults'] || ! empty( $this->options ) ) {
            return;
        }

        foreach ( $this->pre_fields as $field ) {
            if ( ! empty( $field['id'] ) && isset( $field['default'] ) ) {
                $this->options[ $field['id'] ] = $field['default'];
            }
        }

        update_option( $this->unique, $this->options );
    }

    /**
     * Save options to database.
     *
     * @param array $data Options data.
     */
    private function save_options( $data ) {
        update_option( $this->unique, $data );
    }

    /**
     * AJAX save handler.
     */
    public function ajax_save() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pw_options_nonce' ) ) {
            wp_send_json_error( array( 'error' => __( 'Invalid nonce.', 'pearl-weather' ) ) );
        }

        $data = json_decode( wp_unslash( $_POST['data'] ), true );
        $options = isset( $data[ $this->unique ] ) ? $data[ $this->unique ] : array();

        foreach ( $this->pre_fields as $field ) {
            if ( ! empty( $field['id'] ) ) {
                $field_id = $field['id'];
                $this->options[ $field_id ] = isset( $options[ $field_id ] ) 
                    ? $this->sanitize_field( $field, $options[ $field_id ] ) 
                    : '';
            }
        }

        $this->save_options( $this->options );

        wp_send_json_success( array( 'notice' => __( 'Settings saved.', 'pearl-weather' ) ) );
    }

    /**
     * Sanitize field value.
     *
     * @param array  $field Field configuration.
     * @param mixed  $value Field value.
     * @return mixed
     */
    private function sanitize_field( $field, $value ) {
        if ( isset( $field['sanitize'] ) && is_callable( $field['sanitize'] ) ) {
            return call_user_func( $field['sanitize'], $value );
        }

        if ( is_array( $value ) ) {
            return array_map( 'sanitize_text_field', $value );
        }

        return sanitize_text_field( $value );
    }

    /**
     * Add admin menu page.
     */
    public function add_admin_menu() {
        $menu_slug = $this->args['menu_slug'];
        $menu_title = $this->args['menu_title'];

        add_menu_page(
            $menu_title,
            $menu_title,
            $this->args['menu_capability'],
            $menu_slug,
            array( $this, 'render_page' ),
            $this->args['menu_icon'],
            $this->args['menu_position']
        );
    }

    /**
     * Add admin bar menu.
     *
     * @param object $wp_admin_bar Admin bar object.
     */
    public function add_admin_bar_menu( $wp_admin_bar ) {
        if ( ! $this->args['show_bar_menu'] || $this->args['menu_hidden'] ) {
            return;
        }

        $wp_admin_bar->add_node( array(
            'id'    => $this->args['menu_slug'],
            'title' => $this->args['menu_title'],
            'href'  => admin_url( 'admin.php?page=' . $this->args['menu_slug'] ),
        ) );
    }

    /**
     * Render the options page.
     */
    public function render_page() {
        ?>
        <div class="pw-options-page">
            <div class="pw-options-header">
                <h1><?php echo esc_html( $this->args['framework_title'] ); ?></h1>
            </div>
            <div class="pw-options-wrapper">
                <form method="post" action="" id="pw-options-form">
                    <?php wp_nonce_field( 'pw_options_nonce', 'pw_options_nonce' ); ?>
                    <input type="hidden" name="action" value="pw_<?php echo esc_attr( $this->unique ); ?>_save">
                    
                    <div class="pw-options-nav">
                        <?php foreach ( $this->pre_tabs as $tab ) : ?>
                            <a href="#tab-<?php echo esc_attr( sanitize_title( $tab['title'] ) ); ?>" class="pw-nav-tab">
                                <?php echo esc_html( $tab['title'] ); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="pw-options-content">
                        <?php foreach ( $this->pre_sections as $section ) : ?>
                            <div id="tab-<?php echo esc_attr( sanitize_title( $section['title'] ) ); ?>" class="pw-options-section">
                                <h2><?php echo esc_html( $section['title'] ); ?></h2>
                                <?php if ( ! empty( $section['description'] ) ) : ?>
                                    <p><?php echo wp_kses_post( $section['description'] ); ?></p>
                                <?php endif; ?>
                                
                                <div class="pw-options-fields">
                                    <?php foreach ( $section['fields'] as $field ) : ?>
                                        <div class="pw-field-wrapper">
                                            <label class="pw-field-label"><?php echo esc_html( $field['title'] ); ?></label>
                                            <div class="pw-field-control">
                                                <?php $this->render_field( $field ); ?>
                                                <?php if ( ! empty( $field['subtitle'] ) ) : ?>
                                                    <p class="pw-field-subtitle"><?php echo wp_kses_post( $field['subtitle'] ); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="pw-options-footer">
                        <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Settings', 'pearl-weather' ); ?></button>
                        <button type="button" class="button pw-reset-all"><?php esc_html_e( 'Reset All', 'pearl-weather' ); ?></button>
                    </div>
                </form>
            </div>
        </div>

        <style>
            .pw-options-page {
                margin: 20px 20px 0 0;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .pw-options-header {
                padding: 20px;
                border-bottom: 1px solid #ddd;
            }
            .pw-options-nav {
                display: flex;
                border-bottom: 1px solid #ddd;
                background: #f8f9fa;
            }
            .pw-nav-tab {
                padding: 12px 20px;
                text-decoration: none;
                color: #333;
            }
            .pw-nav-tab:hover {
                background: #e9ecef;
            }
            .pw-options-content {
                padding: 20px;
            }
            .pw-options-section {
                display: none;
            }
            .pw-options-section.active {
                display: block;
            }
            .pw-field-wrapper {
                display: flex;
                margin-bottom: 20px;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 6px;
            }
            .pw-field-label {
                width: 200px;
                font-weight: 600;
            }
            .pw-field-control {
                flex: 1;
            }
            .pw-field-subtitle {
                margin: 5px 0 0;
                color: #666;
                font-size: 12px;
            }
            .pw-options-footer {
                padding: 20px;
                border-top: 1px solid #ddd;
                background: #f8f9fa;
                display: flex;
                gap: 10px;
            }
        </style>

        <script>
        (function($) {
            $(document).ready(function() {
                // Tab navigation
                $('.pw-nav-tab').on('click', function(e) {
                    e.preventDefault();
                    var target = $(this).attr('href');
                    $('.pw-options-section').removeClass('active');
                    $(target).addClass('active');
                    $('.pw-nav-tab').removeClass('active');
                    $(this).addClass('active');
                });
                
                // Show first tab
                $('.pw-nav-tab:first').click();
                
                // Form submit
                $('#pw-options-form').on('submit', function(e) {
                    e.preventDefault();
                    var formData = $(this).serialize();
                    
                    $.post(ajaxurl, formData, function(response) {
                        if (response.success) {
                            alert(response.data.notice);
                        } else {
                            alert('Error saving settings.');
                        }
                    });
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Render a single field.
     *
     * @param array $field Field configuration.
     */
    private function render_field( $field ) {
        $field_id = $field['id'];
        $field_type = $field['type'];
        $field_value = isset( $this->options[ $field_id ] ) ? $this->options[ $field_id ] : '';

        switch ( $field_type ) {
            case 'text':
                echo '<input type="text" name="' . esc_attr( $this->unique . '[' . $field_id . ']' ) . '" value="' . esc_attr( $field_value ) . '" class="regular-text">';
                break;
            case 'textarea':
                echo '<textarea name="' . esc_attr( $this->unique . '[' . $field_id . ']' ) . '" rows="5" class="large-text">' . esc_textarea( $field_value ) . '</textarea>';
                break;
            case 'checkbox':
                echo '<input type="checkbox" name="' . esc_attr( $this->unique . '[' . $field_id . ']' ) . '" value="1" ' . checked( $field_value, 1, false ) . '>';
                break;
            case 'select':
                echo '<select name="' . esc_attr( $this->unique . '[' . $field_id . ']' ) . '">';
                foreach ( $field['options'] as $option_key => $option_label ) {
                    echo '<option value="' . esc_attr( $option_key ) . '" ' . selected( $field_value, $option_key, false ) . '>' . esc_html( $option_label ) . '</option>';
                }
                echo '</select>';
                break;
            default:
                echo '<p>' . esc_html__( 'Field type not supported.', 'pearl-weather' ) . '</p>';
                break;
        }
    }
}