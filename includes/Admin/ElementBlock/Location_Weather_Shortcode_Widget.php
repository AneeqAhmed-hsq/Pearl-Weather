<?php
/**
 * Elementor Weather Widget
 *
 * Integrates Pearl Weather with Elementor page builder.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Admin/Elementor
 * @since      1.0.0
 */

namespace PearlWeather\Admin\Elementor;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PearlWeather\Frontend\ShortcodeHandler;
use PearlWeather\Admin\AssetsManager;

/**
 * Class WeatherElementorWidget
 *
 * Elementor widget for displaying weather widgets.
 *
 * @since 1.0.0
 */
class WeatherElementorWidget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_name() {
        return 'pearl_weather';
    }

    /**
     * Get widget title.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_title() {
        return __( 'Pearl Weather', 'pearl-weather' );
    }

    /**
     * Get widget icon.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_icon() {
        return 'pw-icon-weather';
    }

    /**
     * Get widget categories.
     *
     * @since 1.0.0
     * @return array
     */
    public function get_categories() {
        return array( 'general', 'pearl-weather' );
    }

    /**
     * Get widget keywords.
     *
     * @since 1.0.0
     * @return array
     */
    public function get_keywords() {
        return array( 'weather', 'forecast', 'temperature', 'humidity', 'wind', 'aqi' );
    }

    /**
     * Register widget controls.
     *
     * @since 1.0.0
     */
    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __( 'Content', 'pearl-weather' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'widget_id',
            array(
                'label'       => __( 'Select Weather Widget', 'pearl-weather' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'label_block' => true,
                'default'     => '',
                'options'     => $this->get_widget_options(),
                'description' => __( 'Select a weather widget from the list.', 'pearl-weather' ),
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            array(
                'label' => __( 'Style', 'pearl-weather' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'widget_max_width',
            array(
                'label' => __( 'Max Width', 'pearl-weather' ),
                'type'  => \Elementor\Controls_Manager::SLIDER,
                'range' => array(
                    'px' => array(
                        'min' => 200,
                        'max' => 1200,
                    ),
                    '%' => array(
                        'min' => 10,
                        'max' => 100,
                    ),
                ),
                'selectors' => array(
                    '{{WRAPPER}} .pw-weather-widget' => 'max-width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'widget_margin',
            array(
                'label'      => __( 'Margin', 'pearl-weather' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .pw-weather-widget' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Get widget options for select dropdown.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_widget_options() {
        $options = array( '' => __( '— Select —', 'pearl-weather' ) );

        $widgets = get_posts( array(
            'post_type'      => 'pearl_weather_widget',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        foreach ( $widgets as $widget ) {
            $options[ $widget->ID ] = $widget->post_title;
        }

        return $options;
    }

    /**
     * Render widget output on frontend.
     *
     * @since 1.0.0
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $widget_id = isset( $settings['widget_id'] ) ? absint( $settings['widget_id'] ) : 0;

        if ( empty( $widget_id ) ) {
            echo '<div class="pw-elementor-placeholder">' . esc_html__( 'Please select a weather widget.', 'pearl-weather' ) . '</div>';
            return;
        }

        // Check if we're in Elementor edit mode.
        if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            $this->render_preview( $widget_id );
        } else {
            echo do_shortcode( '[pearl-weather id="' . esc_attr( $widget_id ) . '"]' );
        }
    }

    /**
     * Render preview in Elementor editor.
     *
     * @since 1.0.0
     * @param int $widget_id Widget ID.
     */
    private function render_preview( $widget_id ) {
        // Get widget data.
        $widget = get_post( $widget_id );
        
        if ( ! $widget ) {
            echo '<div class="pw-elementor-error">' . esc_html__( 'Widget not found.', 'pearl-weather' ) . '</div>';
            return;
        }

        $settings = get_post_meta( $widget_id, 'pearl_weather_settings', true );
        $layout = get_post_meta( $widget_id, 'pearl_weather_layout', true );

        // Enqueue preview assets.
        wp_enqueue_style( 'pearl-weather-public' );
        wp_enqueue_style( 'pearl-weather-icons' );
        wp_enqueue_script( 'pearl-weather-public' );

        // Render widget preview.
        $shortcode_handler = new ShortcodeHandler();
        
        // Set up preview mode flag.
        add_filter( 'pearl_weather_is_preview', '__return_true' );
        
        // Render the widget.
        $shortcode_handler->render_widget( $widget_id, $settings, $layout );
        
        remove_filter( 'pearl_weather_is_preview', '__return_true' );
    }

    /**
     * Render widget output in editor (for Elementor template).
     *
     * @since 1.0.0
     */
    protected function content_template() {
        ?>
        <# if ( settings.widget_id ) { #>
            <div class="pw-elementor-preview">
                <div class="pw-preview-header">
                    <span class="pw-preview-icon">🌤️</span>
                    <span class="pw-preview-text"><?php esc_html_e( 'Pearl Weather Widget', 'pearl-weather' ); ?></span>
                </div>
                <div class="pw-preview-info">
                    <?php esc_html_e( 'Widget ID:', 'pearl-weather' ); ?> {{ settings.widget_id }}
                </div>
            </div>
        <# } else { #>
            <div class="pw-elementor-placeholder">
                <?php esc_html_e( 'Please select a weather widget.', 'pearl-weather' ); ?>
            </div>
        <# } #>
        <?php
    }
}

// Register the widget with Elementor.
add_action( 'elementor/widgets/register', function( $widgets_manager ) {
    $widgets_manager->register( new WeatherElementorWidget() );
} );