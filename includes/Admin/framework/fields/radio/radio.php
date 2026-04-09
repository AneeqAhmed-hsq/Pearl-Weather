<?php
/**
 * Framework Radio Field
 *
 * Renders a radio button field with support for inline layouts,
 * nested options, and single radio buttons.
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
 * Class RadioField
 *
 * Handles radio button field rendering in the framework.
 *
 * @since 1.0.0
 */
class RadioField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'inline'     => false,
        'query_args' => array(),
    );

    /**
     * Render the radio field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $inline_class = $args['inline'] ? ' pw-radio-inline' : '';

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if ( isset( $this->field['options'] ) ) {
            $this->render_radio_group( $args, $inline_class );
        } else {
            $this->render_single_radio();
        }

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render a group of radio buttons.
     *
     * @since 1.0.0
     * @param array  $args         Field arguments.
     * @param string $inline_class CSS class for inline layout.
     */
    private function render_radio_group( $args, $inline_class ) {
        $options = $this->field['options'];
        $options = is_array( $options ) ? $options : array_filter( $this->field_data( $options, false, $args['query_args'] ) );

        if ( ! is_array( $options ) || empty( $options ) ) {
            $this->render_empty_message();
            return;
        }

        echo '<ul class="pw-radio-list' . esc_attr( $inline_class ) . '">';

        foreach ( $options as $option_key => $option_value ) {
            if ( is_array( $option_value ) && ! empty( $option_value ) ) {
                $this->render_optgroup( $option_key, $option_value );
            } else {
                $this->render_radio_item( $option_key, $option_value );
            }
        }

        echo '</ul>';
    }

    /**
     * Render an optgroup (nested radio buttons).
     *
     * @since 1.0.0
     * @param string $group_key   Group key.
     * @param array  $group_items Group items.
     */
    private function render_optgroup( $group_key, $group_items ) {
        echo '<li class="pw-radio-optgroup">';
        echo '<ul>';
        echo '<li class="pw-optgroup-title"><strong>' . esc_html( $group_key ) . '</strong></li>';

        foreach ( $group_items as $sub_key => $sub_value ) {
            $this->render_radio_item( $sub_key, $sub_value );
        }

        echo '</ul>';
        echo '</li>';
    }

    /**
     * Render a single radio item.
     *
     * @since 1.0.0
     * @param string $key   Option key.
     * @param string $label Option label.
     */
    private function render_radio_item( $key, $label ) {
        $checked = ( $key === $this->value ) ? ' checked' : '';
        $is_disabled = ( isset( $this->field['disabled'] ) && in_array( $key, (array) $this->field['disabled'], true ) );
        $disabled_attr = $is_disabled ? ' disabled' : '';

        ?>
        <li class="pw-radio-item">
            <label>
                <input type="radio" 
                       name="<?php echo esc_attr( $this->field_name() ); ?>" 
                       value="<?php echo esc_attr( $key ); ?>" 
                       <?php echo $this->field_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                       <?php echo esc_attr( $checked . $disabled_attr ); ?> />
                <span class="pw-radio-text"><?php echo esc_html( $label ); ?></span>
            </label>
        </li>
        <?php
    }

    /**
     * Render a single radio button (no options array).
     *
     * @since 1.0.0
     */
    private function render_single_radio() {
        $label = ! empty( $this->field['label'] ) ? $this->field['label'] : '';
        $checked = checked( $this->value, 1, false );
        $disabled = isset( $this->field['disabled'] ) ? ' disabled' : '';

        ?>
        <label class="pw-radio-single">
            <input type="radio" 
                   name="<?php echo esc_attr( $this->field_name() ); ?>" 
                   value="1" 
                   <?php echo $this->field_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                   <?php echo esc_attr( $checked . $disabled ); ?> />
            <?php if ( ! empty( $label ) ) : ?>
                <span class="pw-radio-text"><?php echo esc_html( $label ); ?></span>
            <?php endif; ?>
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
            .pw-radio-list {
                list-style: none;
                margin: 0;
                padding: 0;
            }
            .pw-radio-list.pw-radio-inline {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
            }
            .pw-radio-list.pw-radio-inline .pw-radio-item {
                margin: 0;
            }
            .pw-radio-item {
                margin-bottom: 8px;
            }
            .pw-radio-item label {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                cursor: pointer;
            }
            .pw-radio-item input[type="radio"] {
                margin: 0;
            }
            .pw-radio-text {
                font-size: 13px;
            }
            .pw-radio-optgroup {
                margin-bottom: 12px;
            }
            .pw-radio-optgroup .pw-optgroup-title {
                margin-bottom: 6px;
                font-weight: 600;
            }
            .pw-radio-optgroup ul {
                list-style: none;
                margin: 0 0 0 15px;
                padding: 0;
            }
            .pw-radio-single {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                cursor: pointer;
            }
            .pw-field-empty-message {
                color: #666;
                font-style: italic;
                padding: 8px 0;
            }
        ' );
    }
}