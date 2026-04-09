<?php
/**
 * Framework Spacing Field
 *
 * Renders input fields for spacing values (margin, padding) with
 * support for individual sides, all sides, and unit selection.
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
 * Class SpacingField
 *
 * Handles spacing field rendering in the framework.
 *
 * @since 1.0.0
 */
class SpacingField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'top_icon'           => '▲',
        'right_icon'         => '►',
        'bottom_icon'        => '▼',
        'left_icon'          => '◄',
        'all_icon'           => '◆',
        'horizontal_icon'    => '◄►',
        'top_placeholder'    => 'top',
        'right_placeholder'  => 'right',
        'bottom_placeholder' => 'bottom',
        'left_placeholder'   => 'left',
        'all_placeholder'    => 'all',
        'horizontal_placeholder' => 'horizontal',
        'top'                => true,
        'left'               => true,
        'bottom'             => true,
        'right'              => true,
        'horizontal'         => false,
        'unit'               => true,
        'show_units'         => true,
        'all'                => false,
        'units'              => array( 'px', '%', 'em', 'rem', 'vw', 'vh' ),
    );

    /**
     * Render the spacing field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $default_values = array(
            'top'        => '',
            'right'      => '',
            'bottom'     => '',
            'left'       => '',
            'all'        => '',
            'horizontal' => '',
            'unit'       => 'px',
        );

        $value = wp_parse_args( $this->value, $default_values );
        $unit = ( count( $args['units'] ) === 1 && ! empty( $args['units'][0] ) ) ? $args['units'][0] : '';

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        echo '<div class="pw-spacing-inputs">';

        if ( ! empty( $args['all'] ) ) {
            $this->render_all_input( $args, $value, $unit );
        } else {
            $this->render_side_inputs( $args, $value, $unit );
        }

        if ( ! empty( $args['horizontal'] ) ) {
            $this->render_horizontal_input( $args, $value, $unit );
        }

        if ( ! empty( $args['unit'] ) && ! empty( $args['show_units'] ) && count( $args['units'] ) > 1 ) {
            $this->render_unit_select( $args, $value );
        }

        echo '</div>';

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render all sides input.
     *
     * @since 1.0.0
     * @param array  $args Field arguments.
     * @param array  $value Current value.
     * @param string $unit Unit value.
     */
    private function render_all_input( $args, $value, $unit ) {
        $placeholder = ! empty( $args['all_placeholder'] ) ? ' placeholder="' . esc_attr( $args['all_placeholder'] ) . '"' : '';
        $title = ! empty( $args['all_title'] ) ? '<div class="pw-spacing-title">' . esc_html( $args['all_title'] ) . '</div>' : '';
        $icon = ! empty( $args['all_icon'] ) ? '<span class="pw-spacing-icon">' . wp_kses_post( $args['all_icon'] ) . '</span>' : '';
        $unit_class = $unit ? ' pw-has-unit' : '';

        ?>
        <div class="pw-spacing-input">
            <?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <input type="number" 
                   name="<?php echo esc_attr( $this->field_name( '[all]' ) ); ?>" 
                   value="<?php echo esc_attr( $value['all'] ); ?>" 
                   <?php echo $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                   class="pw-spacing-number pw-all-input<?php echo esc_attr( $unit_class ); ?>" />
            <?php if ( $unit ) : ?>
                <span class="pw-spacing-unit"><?php echo esc_html( $unit ); ?></span>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render individual side inputs.
     *
     * @since 1.0.0
     * @param array  $args Field arguments.
     * @param array  $value Current value.
     * @param string $unit Unit value.
     */
    private function render_side_inputs( $args, $value, $unit ) {
        $properties = array();

        foreach ( array( 'top', 'right', 'bottom', 'left' ) as $prop ) {
            if ( ! empty( $args[ $prop ] ) ) {
                $properties[] = $prop;
            }
        }

        // Reorder for better UX.
        if ( array( 'right', 'left' ) === $properties ) {
            $properties = array_reverse( $properties );
        }

        $unit_class = $unit ? ' pw-has-unit' : '';

        foreach ( $properties as $property ) {
            $placeholder = ! empty( $args[ $property . '_placeholder' ] ) 
                ? ' placeholder="' . esc_attr( $args[ $property . '_placeholder' ] ) . '"' 
                : '';
            $icon = ! empty( $args[ $property . '_icon' ] ) 
                ? '<span class="pw-spacing-icon">' . wp_kses_post( $args[ $property . '_icon' ] ) . '</span>' 
                : '';

            ?>
            <div class="pw-spacing-input">
                <div class="pw-spacing-title"><?php echo esc_html( ucfirst( $property ) ); ?></div>
                <?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <input type="number" 
                       name="<?php echo esc_attr( $this->field_name( '[' . $property . ']' ) ); ?>" 
                       value="<?php echo esc_attr( $value[ $property ] ); ?>" 
                       <?php echo $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                       class="pw-spacing-number pw-<?php echo esc_attr( $property ); ?>-input<?php echo esc_attr( $unit_class ); ?>" />
                <?php if ( $unit ) : ?>
                    <span class="pw-spacing-unit"><?php echo esc_html( $unit ); ?></span>
                <?php endif; ?>
            </div>
            <?php
        }
    }

    /**
     * Render horizontal input.
     *
     * @since 1.0.0
     * @param array  $args Field arguments.
     * @param array  $value Current value.
     * @param string $unit Unit value.
     */
    private function render_horizontal_input( $args, $value, $unit ) {
        $placeholder = ! empty( $args['horizontal_placeholder'] ) 
            ? ' placeholder="' . esc_attr( $args['horizontal_placeholder'] ) . '"' 
            : '';
        $icon = ! empty( $args['horizontal_icon'] ) 
            ? '<span class="pw-spacing-icon">' . wp_kses_post( $args['horizontal_icon'] ) . '</span>' 
            : '';
        $unit_class = $unit ? ' pw-has-unit' : '';

        ?>
        <div class="pw-spacing-input">
            <div class="pw-spacing-title"><?php esc_html_e( 'Horizontal', 'pearl-weather' ); ?></div>
            <?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <input type="number" 
                   name="<?php echo esc_attr( $this->field_name( '[horizontal]' ) ); ?>" 
                   value="<?php echo esc_attr( $value['horizontal'] ); ?>" 
                   <?php echo $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                   class="pw-spacing-number pw-horizontal-input<?php echo esc_attr( $unit_class ); ?>" />
            <?php if ( $unit ) : ?>
                <span class="pw-spacing-unit"><?php echo esc_html( $unit ); ?></span>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render unit select dropdown.
     *
     * @since 1.0.0
     * @param array $args  Field arguments.
     * @param array $value Current value.
     */
    private function render_unit_select( $args, $value ) {
        ?>
        <div class="pw-spacing-input pw-spacing-unit-select">
            <select name="<?php echo esc_attr( $this->field_name( '[unit]' ) ); ?>" class="pw-spacing-select">
                <?php foreach ( $args['units'] as $unit ) : ?>
                    <option value="<?php echo esc_attr( $unit ); ?>" <?php selected( $value['unit'], $unit ); ?>>
                        <?php echo esc_html( $unit ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /**
     * Enqueue field-specific styles.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        wp_add_inline_style( 'pearl-weather-framework', '
            .pw-spacing-inputs {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                margin: 10px 0;
            }
            .pw-spacing-input {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: #f8f9fa;
                padding: 8px 12px;
                border-radius: 4px;
                border: 1px solid #ddd;
            }
            .pw-spacing-title {
                font-size: 11px;
                font-weight: 500;
                text-transform: uppercase;
                color: #666;
                margin-right: 4px;
            }
            .pw-spacing-icon {
                color: #999;
                font-size: 12px;
            }
            .pw-spacing-number {
                width: 70px;
                padding: 4px 6px;
                text-align: center;
                border: 1px solid #ddd;
                border-radius: 3px;
            }
            .pw-spacing-number.pw-has-unit {
                border-right: none;
                border-radius: 3px 0 0 3px;
            }
            .pw-spacing-unit {
                background: #e9ecef;
                padding: 4px 8px;
                border: 1px solid #ddd;
                border-left: none;
                border-radius: 0 3px 3px 0;
                font-size: 11px;
                color: #666;
            }
            .pw-spacing-select {
                padding: 5px 8px;
                border: 1px solid #ddd;
                border-radius: 3px;
                background: #fff;
            }
        ' );
    }

    /**
     * Generate CSS from spacing values.
     *
     * @since 1.0.0
     * @param array  $values Spacing values.
     * @param string $property CSS property (margin, padding).
     * @return string
     */
    public static function generate_css( $values, $property = 'margin' ) {
        if ( empty( $values ) || ! is_array( $values ) ) {
            return '';
        }

        $unit = isset( $values['unit'] ) ? $values['unit'] : 'px';
        $css = '';

        if ( isset( $values['all'] ) && ! empty( $values['all'] ) ) {
            $css .= $property . ': ' . $values['all'] . $unit . ';';
        } else {
            $sides = array( 'top', 'right', 'bottom', 'left' );
            foreach ( $sides as $side ) {
                if ( isset( $values[ $side ] ) && ! empty( $values[ $side ] ) ) {
                    $css .= $property . '-' . $side . ': ' . $values[ $side ] . $unit . ';';
                }
            }
        }

        return $css;
    }
}