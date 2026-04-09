<?php
/**
 * Framework Slider Field
 *
 * Renders a jQuery UI slider with numeric input for selecting
 * numeric values within a defined range.
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
 * Class SliderField
 *
 * Handles slider field rendering in the framework.
 *
 * @since 1.0.0
 */
class SliderField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'max'     => 100,
        'min'     => 0,
        'step'    => 1,
        'unit'    => '',
        'decimal' => 0,
    );

    /**
     * Render the slider field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $unit_class = ! empty( $args['unit'] ) ? ' pw-slider-has-unit' : '';
        $decimal_places = (int) $args['decimal'];
        $step = (float) $args['step'];
        
        // Ensure step works with decimal places.
        if ( $decimal_places > 0 && $step >= 1 ) {
            $step = 1 / pow( 10, $decimal_places );
        }

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        ?>
        <div class="pw-slider-wrapper<?php echo esc_attr( $unit_class ); ?>" 
             data-min="<?php echo esc_attr( $args['min'] ); ?>" 
             data-max="<?php echo esc_attr( $args['max'] ); ?>" 
             data-step="<?php echo esc_attr( $step ); ?>"
             data-decimal="<?php echo esc_attr( $decimal_places ); ?>">
            
            <div class="pw-slider-ui"></div>
            
            <div class="pw-slider-input">
                <input type="number" 
                       name="<?php echo esc_attr( $this->field_name() ); ?>" 
                       value="<?php echo esc_attr( $this->value ); ?>" 
                       class="pw-slider-input-number" 
                       data-min="<?php echo esc_attr( $args['min'] ); ?>" 
                       data-max="<?php echo esc_attr( $args['max'] ); ?>" 
                       data-step="<?php echo esc_attr( $step ); ?>"
                       step="any" />
                <?php if ( ! empty( $args['unit'] ) ) : ?>
                    <span class="pw-slider-unit"><?php echo esc_html( $args['unit'] ); ?></span>
                <?php endif; ?>
            </div>
            
        </div>
        <?php

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Enqueue slider assets.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        if ( ! wp_script_is( 'jquery-ui-slider' ) ) {
            wp_enqueue_script( 'jquery-ui-slider' );
            wp_enqueue_style( 'jquery-ui-slider' );
        }

        wp_add_inline_style( 'pearl-weather-framework', '
            .pw-slider-wrapper {
                display: flex;
                align-items: center;
                gap: 20px;
                margin: 10px 0;
                flex-wrap: wrap;
            }
            .pw-slider-ui {
                flex: 1;
                min-width: 200px;
            }
            .pw-slider-input {
                display: flex;
                align-items: center;
                gap: 5px;
                min-width: 100px;
            }
            .pw-slider-input-number {
                width: 80px;
                padding: 6px 8px;
                text-align: center;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .pw-slider-unit {
                font-size: 13px;
                color: #666;
            }
            .pw-slider-wrapper .ui-slider {
                background: #e0e0e0;
                border: none;
                height: 4px;
                border-radius: 2px;
            }
            .pw-slider-wrapper .ui-slider .ui-slider-handle {
                width: 16px;
                height: 16px;
                background: #f26c0d;
                border: none;
                border-radius: 50%;
                top: -6px;
                cursor: pointer;
            }
            .pw-slider-wrapper .ui-slider .ui-slider-handle:focus {
                outline: none;
            }
            .pw-slider-wrapper .ui-slider .ui-slider-range {
                background: #f26c0d;
                height: 4px;
                border-radius: 2px;
            }
            .pw-slider-wrapper .ui-slider .ui-slider-handle.ui-state-active,
            .pw-slider-wrapper .ui-slider .ui-slider-handle:hover {
                transform: scale(1.2);
            }
        ' );
    }

    /**
     * Sanitize the slider value.
     *
     * @since 1.0.0
     * @param mixed $value     The value to sanitize.
     * @param array $field     Field configuration.
     * @return float
     */
    public static function sanitize( $value, $field ) {
        $min = isset( $field['min'] ) ? (float) $field['min'] : 0;
        $max = isset( $field['max'] ) ? (float) $field['max'] : 100;
        $value = (float) $value;
        
        return min( $max, max( $min, $value ) );
    }
}