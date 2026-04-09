<?php
/**
 * Framework Switcher Field
 *
 * Renders a modern toggle switch (on/off) as an alternative to
 * standard checkboxes.
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
 * Class SwitcherField
 *
 * Handles toggle switch field rendering in the framework.
 *
 * @since 1.0.0
 */
class SwitcherField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'text_on'     => 'On',
        'text_off'    => 'Off',
        'text_width'  => '',
        'label'       => '',
        'disabled'    => false,
        'readonly'    => false,
    );

    /**
     * Render the switcher field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $is_active = ! empty( $this->value );
        $active_class = $is_active ? ' pw-switcher-active' : '';
        
        $disabled_attr = $args['disabled'] ? ' disabled' : '';
        $readonly_attr = $args['readonly'] ? ' readonly' : '';
        
        $text_on = ! empty( $args['text_on'] ) ? $args['text_on'] : __( 'On', 'pearl-weather' );
        $text_off = ! empty( $args['text_off'] ) ? $args['text_off'] : __( 'Off', 'pearl-weather' );
        
        $width_style = ! empty( $args['text_width'] ) 
            ? ' style="width: ' . esc_attr( $args['text_width'] ) . 'px;"' 
            : '';

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        ?>
        <div class="pw-switcher-wrapper">
            <label class="pw-switcher<?php echo esc_attr( $active_class ); ?>"<?php echo $width_style; ?>>
                <span class="pw-switcher-on"><?php echo esc_html( $text_on ); ?></span>
                <span class="pw-switcher-off"><?php echo esc_html( $text_off ); ?></span>
                <span class="pw-switcher-ball"></span>
                <input type="checkbox" 
                       name="<?php echo esc_attr( $this->field_name() ); ?>" 
                       value="1" 
                       <?php checked( $is_active, true ); ?>
                       <?php echo $disabled_attr; ?>
                       <?php echo $readonly_attr; ?>
                       <?php echo $this->field_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
            </label>
            <?php if ( ! empty( $args['label'] ) ) : ?>
                <span class="pw-switcher-label"><?php echo esc_html( $args['label'] ); ?></span>
            <?php endif; ?>
        </div>
        <?php

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Enqueue field-specific styles.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        wp_add_inline_style( 'pearl-weather-framework', '
            .pw-switcher-wrapper {
                display: inline-flex;
                align-items: center;
                gap: 12px;
            }
            .pw-switcher {
                position: relative;
                display: inline-block;
                cursor: pointer;
                background: #ddd;
                border-radius: 30px;
                transition: all 0.3s ease;
                user-select: none;
                min-width: 50px;
            }
            .pw-switcher.pw-switcher-active {
                background: #f26c0d;
            }
            .pw-switcher-on,
            .pw-switcher-off {
                display: inline-block;
                padding: 5px 12px;
                font-size: 11px;
                font-weight: 500;
                text-transform: uppercase;
                color: #fff;
                opacity: 0.7;
                transition: opacity 0.2s;
            }
            .pw-switcher-on {
                opacity: 0;
            }
            .pw-switcher-off {
                opacity: 1;
            }
            .pw-switcher.pw-switcher-active .pw-switcher-on {
                opacity: 1;
            }
            .pw-switcher.pw-switcher-active .pw-switcher-off {
                opacity: 0;
            }
            .pw-switcher-ball {
                position: absolute;
                top: 2px;
                left: 2px;
                width: 20px;
                height: 20px;
                background: #fff;
                border-radius: 50%;
                transition: transform 0.2s ease;
                box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            }
            .pw-switcher.pw-switcher-active .pw-switcher-ball {
                transform: translateX(calc(100% - 4px));
            }
            .pw-switcher input {
                position: absolute;
                opacity: 0;
                width: 0;
                height: 0;
                pointer-events: none;
            }
            .pw-switcher:active .pw-switcher-ball {
                transform: scale(1.1);
            }
            .pw-switcher-label {
                font-size: 13px;
                color: #666;
            }
            /* Disabled state */
            .pw-switcher input:disabled + .pw-switcher-ball,
            .pw-switcher input:disabled ~ .pw-switcher-on,
            .pw-switcher input:disabled ~ .pw-switcher-off {
                opacity: 0.5;
                cursor: not-allowed;
            }
        ' );
    }

    /**
     * Sanitize the switcher value.
     *
     * @since 1.0.0
     * @param mixed $value The value to sanitize.
     * @return int
     */
    public static function sanitize( $value ) {
        return (int) (bool) $value;
    }
}