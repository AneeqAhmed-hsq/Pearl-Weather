<?php
/**
 * Weather Widget Class
 *
 * Allows users to add weather widgets to WordPress widget areas.
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
 * Class WeatherWidget
 *
 * WordPress widget for displaying weather information.
 *
 * @since 1.0.0
 */
class WeatherWidget extends \WP_Widget {

    /**
     * Widget ID base.
     */
    const WIDGET_ID = 'pearl_weather_widget';

    /**
     * Post type for weather widgets.
     */
    const POST_TYPE = 'pearl_weather_widget';

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            self::WIDGET_ID,
            __( 'Pearl Weather', 'pearl-weather' ),
            array(
                'description' => __( 'Display weather information from Pearl Weather.', 'pearl-weather' ),
                'classname'   => 'widget_pearl_weather',
            )
        );
    }

    /**
     * Front-end display of widget.
     *
     * @since 1.0.0
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        $widget_id = isset( $instance['widget_id'] ) ? absint( $instance['widget_id'] ) : 0;

        if ( empty( $widget_id ) ) {
            return;
        }

        // Verify the widget exists and is published.
        $widget = get_post( $widget_id );
        if ( ! $widget || self::POST_TYPE !== $widget->post_type || 'publish' !== $widget->post_status ) {
            return;
        }

        // Before widget hook.
        echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        // Display title if set.
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        // Render the weather widget using shortcode.
        echo do_shortcode( '[pearl-weather id="' . esc_attr( $widget_id ) . '"]' );

        // After widget hook.
        echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Back-end widget form.
     *
     * @since 1.0.0
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $title = isset( $instance['title'] ) ? sanitize_text_field( $instance['title'] ) : '';
        $widget_id = isset( $instance['widget_id'] ) ? absint( $instance['widget_id'] ) : 0;

        $widgets = $this->get_available_widgets();

        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php esc_html_e( 'Title:', 'pearl-weather' ); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
                   type="text" 
                   value="<?php echo esc_attr( $title ); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'widget_id' ) ); ?>">
                <?php esc_html_e( 'Select Weather Widget:', 'pearl-weather' ); ?>
            </label>
            
            <?php if ( ! empty( $widgets ) ) : ?>
                <select class="widefat" 
                        id="<?php echo esc_attr( $this->get_field_id( 'widget_id' ) ); ?>" 
                        name="<?php echo esc_attr( $this->get_field_name( 'widget_id' ) ); ?>">
                    <option value=""><?php esc_html_e( '— Select —', 'pearl-weather' ); ?></option>
                    <?php foreach ( $widgets as $widget ) : ?>
                        <option value="<?php echo esc_attr( $widget->ID ); ?>" 
                                <?php selected( $widget_id, $widget->ID ); ?>>
                            <?php echo esc_html( $widget->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <p class="description">
                    <?php
                    printf(
                        /* translators: %s: link to create a new weather widget */
                        esc_html__( 'No weather widgets found. %sCreate one%s.', 'pearl-weather' ),
                        '<a href="' . esc_url( admin_url( 'post-new.php?post_type=' . self::POST_TYPE ) ) . '">',
                        '</a>'
                    );
                    ?>
                </p>
            <?php endif; ?>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @since 1.0.0
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();

        $instance['title'] = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['widget_id'] = isset( $new_instance['widget_id'] ) ? absint( $new_instance['widget_id'] ) : 0;

        return $instance;
    }

    /**
     * Get available weather widgets.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_available_widgets() {
        $widgets = get_posts( array(
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        return $widgets;
    }

    /**
     * Register the widget.
     *
     * @since 1.0.0
     */
    public static function register() {
        register_widget( __CLASS__ );
    }
}

// Hook to register widget.
add_action( 'widgets_init', array( 'PearlWeather\Admin\WeatherWidget', 'register' ) );