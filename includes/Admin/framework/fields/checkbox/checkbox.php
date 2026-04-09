<?php
/**
 * Framework Checkbox Field
 *
 * Renders a checkbox field with support for single checkboxes,
 * checkbox groups, nested options, and inline layouts.
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
 * Class CheckboxField
 *
 * Handles checkbox field rendering in the framework.
 *
 * @since 1.0.0
 */
class CheckboxField extends BaseField {

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
     * Render the checkbox field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $inline_class = $args['inline'] ? ' pw-checkbox-inline' : '';

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if ( isset( $this->field['options'] ) ) {
            $this->render_checkbox_group( $args, $inline_class );
        } else {
            $this->render_single_checkbox();
        }

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render a group of checkboxes.
     *
     * @since 1.0.0
     * @param array  $args         Field arguments.
     * @param string $inline_class CSS class for inline layout.
     */
    private function render_checkbox_group( $args, $inline_class ) {
        $value = is_array( $this->value ) ? $this->value : array_filter( (array) $this->value );
        $options = $this->field['options'];
        $options = is_array( $options ) ? $options : array_filter( $this->field_data( $options, false, $args['query_args'] ) );

        if ( ! is_array( $options ) || empty( $options ) ) {
            $this->render_empty_message();
            return;
        }

        echo '<ul class="pw-checkbox-list' . esc_attr( $inline_class ) . '">';

        foreach ( $options as $option_key => $option_value ) {
            if ( is_array( $option_value ) && ! empty( $option_value ) ) {
                $this->render_optgroup( $option_key, $option_value, $value );
            } else {
                $this->render_checkbox_item( $option_key, $option_value, $value );
            }
        }

        echo '</ul>';
    }

    /**
     * Render an optgroup (nested checkboxes).
     *
     * @since 1.0.0
     * @param string $group_key   Group key.
     * @param array  $group_items Group items.
     * @param array  $value       Current values.
     */
    private function render_optgroup( $group_key, $group_items, $value ) {
        echo '<li class="pw-checkbox-optgroup">';
        echo '<ul>';
        echo '<li class="pw-optgroup-title"><strong>' . esc_html( $group_key ) . '</strong></li>';

        foreach ( $group_items as $sub_key => $sub_value ) {
            $this->render_checkbox_item( $sub_key, $sub_value, $value );
        }

        echo '</ul>';
        echo '</li>';
    }

    /**
     * Render a single checkbox item.
     *
     * @since 1.0.0
     * @param string $key   Option key.
     * @param string $label Option label.
     * @param array  $value Current values.
     */
    private function render_checkbox_item( $key, $label, $value ) {
        $checked = in_array( $key, $value, true ) ? ' checked' : '';
        ?>
        <li class="pw-checkbox-item">
            <label>
                <input type="checkbox" 
                       name="<?php echo esc_attr( $this->field_name( '[]' ) ); ?>" 
                       value="<?php echo esc_attr( $key ); ?>" 
                       <?php echo $this->field_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                       <?php echo esc_attr( $checked ); ?> />
                <span class="pw-checkbox-text"><?php echo esc_html( $label ); ?></span>
            </label>
        </li>
        <?php
    }

    /**
     * Render a single checkbox (no options array).
     *
     * @since 1.0.0
     */
    private function render_single_checkbox() {
        $checked = checked( $this->value, 1, false );
        $label = ! empty( $this->field['label'] ) ? $this->field['label'] : '';
        ?>
        <label class="pw-checkbox-single">
            <input type="hidden" name="<?php echo esc_attr( $this->field_name() ); ?>" value="0" />
            <input type="checkbox" 
                   name="<?php echo esc_attr( $this->field_name() ); ?>" 
                   value="1" 
                   <?php echo $this->field_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                   <?php echo esc_attr( $checked ); ?> />
            <?php if ( ! empty( $label ) ) : ?>
                <span class="pw-checkbox-text"><?php echo esc_html( $label ); ?></span>
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
}