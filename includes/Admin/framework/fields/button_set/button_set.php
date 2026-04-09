<?php
/**
 * Framework Button Set Field
 *
 * Renders a group of toggle buttons that function like radio/checkbox inputs.
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
 * Class ButtonSetField
 *
 * Handles button set field rendering in the framework.
 *
 * @since 1.0.0
 */
class ButtonSetField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'multiple'   => false,
        'options'    => array(),
        'query_args' => array(),
        'size'       => 'normal', // small, normal, large
    );

    /**
     * Render the button set field.
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
        $size_class = ' pw-button-set-' . $args['size'];
        $multiple_attr = $args['multiple'] ? ' data-multiple="true"' : '';

        echo '<div class="pw-button-set' . esc_attr( $size_class ) . '"' . $multiple_attr . '>';

        foreach ( $options as $key => $option ) {
            $this->render_button( $key, $option, $args, $value );
        }

        echo '</div>';
    }

    /**
     * Render a single button.
     *
     * @since 1.0.0
     * @param string $key     Option key.
     * @param string $label   Option label.
     * @param array  $args    Field arguments.
     * @param array  $value   Current value.
     */
    private function render_button( $key, $label, $args, $value ) {
        $type = $args['multiple'] ? 'checkbox' : 'radio';
        $extra = $args['multiple'] ? '[]' : '';

        $is_active = in_array( $key, $value, true ) || ( empty( $value ) && empty( $key ) );
        $active_class = $is_active ? ' pw-button-active' : '';

        $is_disabled = isset( $args['disabled'] ) && in_array( $key, (array) $args['disabled'], true );
        $disabled_attr = $is_disabled ? ' disabled' : '';

        $checked = $is_active ? ' checked' : '';

        ?>
        <label class="pw-button-set-item<?php echo esc_attr( $active_class . ( $is_disabled ? ' pw-disabled' : '' ) ); ?>">
            <input type="<?php echo esc_attr( $type ); ?>" 
                   name="<?php echo esc_attr( $this->field_name( $extra ) ); ?>" 
                   value="<?php echo esc_attr( $key ); ?>" 
                   <?php echo $this->field_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                   <?php echo esc_attr( $checked . $disabled_attr ); ?> />
            <span class="pw-button-label"><?php echo wp_kses_post( $label ); ?></span>
        </label>
        <?php
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
     * Enqueue field-specific styles.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        wp_add_inline_style( 'pearl-weather-framework', '
            .pw-button-set {
                display: inline-flex;
                flex-wrap: wrap;
                gap: 8px;
                margin: 5px 0;
            }
            .pw-button-set-item {
                display: inline-flex;
                align-items: center;
                margin: 0;
                cursor: pointer;
                background: #f8f9fa;
                border: 1px solid #ddd;
                border-radius: 4px;
                transition: all 0.2s ease;
            }
            .pw-button-set-item:hover {
                background: #e9ecef;
            }
            .pw-button-set-item.pw-button-active {
                background: #f26c0d;
                border-color: #f26c0d;
                color: #fff;
            }
            .pw-button-set-item.pw-disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }
            .pw-button-set-item input {
                position: absolute;
                opacity: 0;
                width: 0;
                height: 0;
                pointer-events: none;
            }
            .pw-button-label {
                padding: 6px 14px;
                font-size: 13px;
                font-weight: 500;
                line-height: 1.4;
            }
            /* Size variants */
            .pw-button-set-small .pw-button-label {
                padding: 4px 10px;
                font-size: 11px;
            }
            .pw-button-set-large .pw-button-label {
                padding: 10px 20px;
                font-size: 15px;
            }
            /* Responsive */
            @media (max-width: 768px) {
                .pw-button-set {
                    flex-wrap: wrap;
                }
            }
        ' );
    }
}