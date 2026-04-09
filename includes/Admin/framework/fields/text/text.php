<?php
/**
 * Framework Text Field
 *
 * Renders a text input field with support for various input types.
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
 * Class TextField
 *
 * Handles text field rendering in the framework.
 *
 * @since 1.0.0
 */
class TextField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'type'        => 'text',
        'placeholder' => '',
        'maxlength'   => '',
        'size'        => '',
        'disabled'    => false,
        'readonly'    => false,
        'pattern'     => '',
    );

    /**
     * Allowed input types.
     *
     * @var array
     */
    private $allowed_types = array(
        'text', 'email', 'url', 'password', 'number', 'tel', 
        'search', 'date', 'time', 'datetime-local', 'month', 'week',
    );

    /**
     * Render the text field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $type = $this->validate_type( $args['type'] );
        $disabled_attr = $args['disabled'] ? ' disabled' : '';
        $readonly_attr = $args['readonly'] ? ' readonly' : '';
        $placeholder_attr = ! empty( $args['placeholder'] ) ? ' placeholder="' . esc_attr( $args['placeholder'] ) . '"' : '';
        $maxlength_attr = ! empty( $args['maxlength'] ) ? ' maxlength="' . esc_attr( $args['maxlength'] ) . '"' : '';
        $size_attr = ! empty( $args['size'] ) ? ' size="' . esc_attr( $args['size'] ) . '"' : '';
        $pattern_attr = ! empty( $args['pattern'] ) ? ' pattern="' . esc_attr( $args['pattern'] ) . '"' : '';

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        printf(
            '<input type="%s" 
                    name="%s" 
                    value="%s" 
                    class="pw-text-input%s" 
                    %s%s%s%s%s%s />',
            esc_attr( $type ),
            esc_attr( $this->field_name() ),
            esc_attr( $this->value ),
            $args['disabled'] ? ' pw-disabled' : '',
            $placeholder_attr,
            $maxlength_attr,
            $size_attr,
            $pattern_attr,
            $disabled_attr,
            $readonly_attr
        );

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Validate input type.
     *
     * @since 1.0.0
     * @param string $type Input type.
     * @return string
     */
    private function validate_type( $type ) {
        if ( in_array( $type, $this->allowed_types, true ) ) {
            return $type;
        }
        return 'text';
    }

    /**
     * Enqueue field-specific styles.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        wp_add_inline_style( 'pearl-weather-framework', '
            .pw-text-input {
                width: 100%;
                max-width: 400px;
                padding: 8px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 13px;
                transition: all 0.2s ease;
            }
            .pw-text-input:focus {
                border-color: #f26c0d;
                outline: none;
                box-shadow: 0 0 0 1px rgba(242,108,13,0.2);
            }
            .pw-text-input.pw-disabled,
            .pw-text-input:disabled {
                background: #f8f9fa;
                color: #999;
                cursor: not-allowed;
            }
            .pw-text-input[readonly] {
                background: #f8f9fa;
            }
        ' );
    }

    /**
     * Sanitize the text value.
     *
     * @since 1.0.0
     * @param string $value The value to sanitize.
     * @param array  $field Field configuration.
     * @return string
     */
    public static function sanitize( $value, $field ) {
        $type = isset( $field['type'] ) ? $field['type'] : 'text';
        
        switch ( $type ) {
            case 'email':
                return sanitize_email( $value );
            case 'url':
                return esc_url_raw( $value );
            case 'number':
                return is_numeric( $value ) ? (float) $value : '';
            default:
                return sanitize_text_field( $value );
        }
    }
}