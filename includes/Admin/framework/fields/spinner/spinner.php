<?php
/**
 * Framework Spinner Field
 *
 * Renders a numeric input with spinner controls for incrementing/
 * decrementing values within a defined range.
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
 * Class SpinnerField
 *
 * Handles spinner field rendering in the framework.
 *
 * @since 1.0.0
 */
class SpinnerField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'max'      => 100,
        'min'      => 0,
        'step'     => 1,
        'unit'     => '',
        'decimal'  => 0,
        'disabled' => false,
        'readonly' => false,
    );

    /**
     * Render the spinner field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $disabled_attr = $args['disabled'] ? ' disabled' : '';
        $readonly_attr = $args['readonly'] ? ' readonly' : '';
        $unit_html = ! empty( $args['unit'] ) ? '<span class="pw-spinner-unit">' . esc_html( $args['unit'] ) . '</span>' : '';
        $decimal_places = (int) $args['decimal'];
        $step = (float) $args['step'];
        
        // Adjust step for decimal places.
        if ( $decimal_places > 0 && $step >= 1 ) {
            $step = 1 / pow( 10, $decimal_places );
        }

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        ?>
        <div class="pw-spinner-wrapper">
            <div class="pw-spinner-input-group">
                <input type="number" 
                       name="<?php echo esc_attr( $this->field_name() ); ?>" 
                       value="<?php echo esc_attr( $this->value ); ?>" 
                       class="pw-spinner-input" 
                       data-min="<?php echo esc_attr( $args['min'] ); ?>" 
                       data-max="<?php echo esc_attr( $args['max'] ); ?>" 
                       data-step="<?php echo esc_attr( $step ); ?>"
                       data-decimal="<?php echo esc_attr( $decimal_places ); ?>"
                       data-unit="<?php echo esc_attr( $args['unit'] ); ?>"
                       <?php echo $disabled_attr; ?>
                       <?php echo $readonly_attr; ?>
                       step="any" />
                <?php echo $unit_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
        <?php

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Enqueue spinner assets.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        if ( ! wp_script_is( 'jquery-ui-spinner' ) ) {
            wp_enqueue_script( 'jquery-ui-spinner' );
            wp_enqueue_style( 'jquery-ui-spinner' );
        }

        wp_add_inline_script( 'pearl-weather-framework', '
            (function($) {
                $(document).ready(function() {
                    $(".pw-spinner-input").each(function() {
                        var $input = $(this);
                        var min = parseFloat($input.data("min")) || 0;
                        var max = parseFloat($input.data("max")) || 100;
                        var step = parseFloat($input.data("step")) || 1;
                        var decimal = parseInt($input.data("decimal")) || 0;
                        
                        $input.spinner({
                            min: min,
                            max: max,
                            step: step,
                            spin: function(event, ui) {
                                if (decimal > 0) {
                                    var value = ui.value;
                                    $(this).val(value.toFixed(decimal));
                                    return false;
                                }
                                return true;
                            }
                        });
                    });
                });
            })(jQuery);
        ' );

        wp_add_inline_style( 'pearl-weather-framework', '
            .pw-spinner-wrapper {
                display: inline-block;
            }
            .pw-spinner-input-group {
                display: inline-flex;
                align-items: center;
                gap: 5px;
            }
            .pw-spinner-input {
                width: 100px;
                padding: 6px 8px;
                text-align: center;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .pw-spinner-unit {
                font-size: 13px;
                color: #666;
            }
            .ui-spinner {
                position: relative;
                display: inline-block;
            }
            .ui-spinner-input {
                margin: 0;
                text-align: center;
            }
            .ui-spinner-button {
                cursor: pointer;
                width: 20px;
                height: 50%;
                position: absolute;
                right: 0;
                background: #f8f9fa;
                border: 1px solid #ddd;
            }
            .ui-spinner-up {
                top: 0;
                border-bottom: none;
                border-radius: 0 4px 0 0;
            }
            .ui-spinner-down {
                bottom: 0;
                border-radius: 0 0 4px 0;
            }
            .ui-spinner-button .ui-icon {
                font-size: 10px;
                text-indent: 0;
                overflow: visible;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }
        ' );
    }

    /**
     * Sanitize the spinner value.
     *
     * @since 1.0.0
     * @param mixed $value The value to sanitize.
     * @param array $field Field configuration.
     * @return float
     */
    public static function sanitize( $value, $field ) {
        $min = isset( $field['min'] ) ? (float) $field['min'] : 0;
        $max = isset( $field['max'] ) ? (float) $field['max'] : 100;
        $decimal = isset( $field['decimal'] ) ? (int) $field['decimal'] : 0;
        
        $value = (float) $value;
        $value = min( $max, max( $min, $value ) );
        
        if ( $decimal > 0 ) {
            $value = round( $value, $decimal );
        }
        
        return $value;
    }
}