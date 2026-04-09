<?php
/**
 * Framework Button Clean Field
 *
 * Renders a button group with confirmation dialog for destructive actions
 * like cache clearing or data reset.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Framework/Fields
 * @since      1.0.0
 */

namespace PearlWeather\Framework\Fields;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ButtonCleanField
 *
 * Handles button clean field rendering with confirmation.
 *
 * @since 1.0.0
 */
class ButtonCleanField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'multiple'    => false,
        'options'     => array(),
        'query_args'  => array(),
        'confirm_text' => 'Are you sure you want to perform this action?',
    );

    /**
     * Render the button clean field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $value = is_array( $this->value ) ? $this->value : array_filter( (array) $this->value );

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if ( isset( $this->field['options'] ) ) {
            $options = $this->field['options'];
            $options = is_array( $options ) ? $options : array_filter( $this->field_data( $options, false, $args['query_args'] ) );

            if ( is_array( $options ) && ! empty( $options ) ) {
                $this->render_button_group( $args, $options, $value );
            } else {
                $this->render_empty_message();
            }
        }

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render button group.
     *
     * @since 1.0.0
     * @param array $args    Field arguments.
     * @param array $options Available options.
     * @param array $value   Current value.
     */
    private function render_button_group( $args, $options, $value ) {
        $confirm_text = ! empty( $args['confirm_text'] ) 
            ? $args['confirm_text'] 
            : __( 'Are you sure you want to perform this action?', 'pearl-weather' );

        echo '<div class="pw-button-group pw-confirm-group" data-confirm-text="' . esc_attr( $confirm_text ) . '">';

        foreach ( $options as $key => $option ) {
            $is_active = in_array( $key, $value, true ) || ( empty( $value ) && empty( $key ) );
            $active_class = $is_active ? ' pw-button-active' : '';

            printf(
                '<button type="button" class="pw-button-clean%s" data-action="%s" data-confirm="%s">%s</button>',
                esc_attr( $active_class ),
                esc_attr( $key ),
                esc_attr( $confirm_text ),
                wp_kses_post( $option )
            );
        }

        echo '</div>';
    }

    /**
     * Render empty message.
     *
     * @since 1.0.0
     */
    private function render_empty_message() {
        $message = ! empty( $this->field['empty_message'] ) 
            ? $this->field['empty_message'] 
            : __( 'No data available.', 'pearl-weather' );

        echo '<div class="pw-field-empty-message">' . esc_html( $message ) . '</div>';
    }

    /**
     * Enqueue field-specific scripts.
     *
     * @since 1.0.0
     */
    public static function enqueue_scripts() {
        wp_add_inline_script( 'pearl-weather-framework', '
            (function($) {
                $(document).on("click", ".pw-button-clean", function(e) {
                    e.preventDefault();
                    var $button = $(this);
                    var confirmText = $button.data("confirm") || "Are you sure?";
                    
                    if (confirm(confirmText)) {
                        var action = $button.data("action");
                        var nonce = pwFramework.nonce || "";
                        
                        $.post(ajaxurl, {
                            action: action,
                            nonce: nonce
                        }).done(function(response) {
                            if (response.success) {
                                alert(response.data.message || "Action completed successfully.");
                                location.reload();
                            } else {
                                alert(response.data.message || "An error occurred.");
                            }
                        }).fail(function() {
                            alert("Request failed. Please try again.");
                        });
                    }
                });
            })(jQuery);
        ' );
    }
}