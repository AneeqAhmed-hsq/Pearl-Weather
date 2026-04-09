<?php
/**
 * Framework Border Field
 *
 * Renders a border configuration control with side inputs,
 * style selector, color picker, and optional radius field.
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
 * Class BorderField
 *
 * Handles border configuration in the framework.
 *
 * @since 1.0.0
 */
class BorderField extends BaseField {

    /**
     * Border style options.
     *
     * @var array
     */
    private $border_styles = array(
        'solid'  => 'Solid',
        'dashed' => 'Dashed',
        'dotted' => 'Dotted',
        'double' => 'Double',
        'inset'  => 'Inset',
        'outset' => 'Outset',
        'groove' => 'Groove',
        'ridge'  => 'Ridge',
        'none'   => 'None',
    );

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'top_icon'           => '<i class="fas fa-long-arrow-alt-up"></i>',
        'left_icon'          => '<i class="fas fa-long-arrow-alt-left"></i>',
        'bottom_icon'        => '<i class="fas fa-long-arrow-alt-down"></i>',
        'right_icon'         => '<i class="fas fa-long-arrow-alt-right"></i>',
        'all_icon'           => '<i class="fas fa-arrows-alt"></i>',
        'top_placeholder'    => 'top',
        'right_placeholder'  => 'right',
        'bottom_placeholder' => 'bottom',
        'left_placeholder'   => 'left',
        'all_placeholder'    => 'all',
        'top'                => true,
        'left'               => true,
        'bottom'             => true,
        'right'              => true,
        'all'                => false,
        'background'         => false,
        'active_color'       => false,
        'active_bg'          => false,
        'radius'             => false,
        'color'              => true,
        'style'              => true,
        'unit'               => 'px',
    );

    /**
     * Render the border field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );
        
        $default_value = array(
            'top'          => '',
            'right'        => '',
            'bottom'       => '',
            'left'         => '',
            'color'        => '',
            'style'        => 'solid',
            'all'          => '',
            'radius'       => '',
            'background'   => '',
            'active_color' => '',
            'active_bg'    => '',
        );

        if ( ! empty( $this->field['default'] ) ) {
            $default_value = wp_parse_args( $this->field['default'], $default_value );
        }

        $value = wp_parse_args( $this->value, $default_value );

        // Output field wrapper.
        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        // Side inputs container.
        echo '<div class="pw-field-border-inputs">';

        if ( ! empty( $args['all'] ) ) {
            $this->render_all_input( $args, $value );
        } else {
            $this->render_side_inputs( $args, $value );
        }

        if ( ! empty( $args['style'] ) ) {
            $this->render_style_select( $value );
        }

        echo '</div>';

        // Color pickers.
        if ( ! empty( $args['color'] ) ) {
            $this->render_color_picker( 'color', __( 'Color', 'pearl-weather' ), $value['color'], $default_value['color'] );
        }

        if ( ! empty( $args['active_color'] ) ) {
            $this->render_color_picker( 'active_color', __( 'Active Color', 'pearl-weather' ), $value['active_color'], $default_value['active_color'] );
        }

        if ( ! empty( $args['background'] ) ) {
            $this->render_color_picker( 'background', __( 'Background', 'pearl-weather' ), $value['background'], $default_value['background'] );
        }

        if ( ! empty( $args['active_bg'] ) ) {
            $this->render_color_picker( 'active_bg', __( 'Active Background', 'pearl-weather' ), $value['active_bg'], $default_value['active_bg'] );
        }

        // Border radius.
        if ( ! empty( $args['radius'] ) ) {
            $this->render_radius_input( $args, $value );
        }

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render all sides input (single value for all sides).
     *
     * @since 1.0.0
     * @param array $args  Field arguments.
     * @param array $value Current field value.
     */
    private function render_all_input( $args, $value ) {
        $placeholder = ! empty( $args['all_placeholder'] ) ? ' placeholder="' . esc_attr( $args['all_placeholder'] ) . '"' : '';
        ?>
        <div class="pw-field-border-input">
            <div class="pw-field-title"><?php esc_html_e( 'Width', 'pearl-weather' ); ?></div>
            <?php if ( ! empty( $args['all_icon'] ) ) : ?>
                <span class="pw-field-icon"><?php echo wp_kses_post( $args['all_icon'] ); ?></span>
            <?php endif; ?>
            <input type="number" 
                   name="<?php echo esc_attr( $this->field_name( '[all]' ) ); ?>" 
                   value="<?php echo esc_attr( $value['all'] ); ?>" 
                   <?php echo $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                   class="pw-input-number pw-is-unit" />
            <?php if ( ! empty( $args['unit'] ) ) : ?>
                <span class="pw-field-unit"><?php echo esc_html( $args['unit'] ); ?></span>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render individual side inputs (top, right, bottom, left).
     *
     * @since 1.0.0
     * @param array $args  Field arguments.
     * @param array $value Current field value.
     */
    private function render_side_inputs( $args, $value ) {
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

        foreach ( $properties as $property ) {
            $placeholder = ! empty( $args[ $property . '_placeholder' ] ) 
                ? ' placeholder="' . esc_attr( $args[ $property . '_placeholder' ] ) . '"' 
                : '';
            ?>
            <div class="pw-field-border-input">
                <?php if ( ! empty( $args[ $property . '_icon' ] ) ) : ?>
                    <span class="pw-field-icon"><?php echo wp_kses_post( $args[ $property . '_icon' ] ); ?></span>
                <?php endif; ?>
                <input type="number" 
                       name="<?php echo esc_attr( $this->field_name( '[' . $property . ']' ) ); ?>" 
                       value="<?php echo esc_attr( $value[ $property ] ); ?>" 
                       <?php echo $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                       class="pw-input-number pw-is-unit" />
                <?php if ( ! empty( $args['unit'] ) ) : ?>
                    <span class="pw-field-unit"><?php echo esc_html( $args['unit'] ); ?></span>
                <?php endif; ?>
            </div>
            <?php
        }
    }

    /**
     * Render border style select dropdown.
     *
     * @since 1.0.0
     * @param array $value Current field value.
     */
    private function render_style_select( $value ) {
        ?>
        <div class="pw-field-border-input">
            <div class="pw-field-title"><?php esc_html_e( 'Style', 'pearl-weather' ); ?></div>
            <select name="<?php echo esc_attr( $this->field_name( '[style]' ) ); ?>" class="pw-field-select">
                <?php foreach ( $this->border_styles as $style_key => $style_label ) : ?>
                    <option value="<?php echo esc_attr( $style_key ); ?>" 
                            <?php selected( $value['style'], $style_key ); ?>>
                        <?php echo esc_html( $style_label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /**
     * Render color picker field.
     *
     * @since 1.0.0
     * @param string $key          Field key.
     * @param string $title        Field title.
     * @param string $value        Current color value.
     * @param string $default_value Default color value.
     */
    private function render_color_picker( $key, $title, $value, $default_value ) {
        $default_attr = ! empty( $default_value ) ? ' data-default-color="' . esc_attr( $default_value ) . '"' : '';
        ?>
        <div class="pw-field-border-color">
            <div class="pw-field-title"><?php echo esc_html( $title ); ?></div>
            <div class="pw-color-picker-wrapper">
                <input type="text" 
                       name="<?php echo esc_attr( $this->field_name( '[' . $key . ']' ) ); ?>" 
                       value="<?php echo esc_attr( $value ); ?>" 
                       class="pw-color-picker" 
                       <?php echo $default_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
            </div>
        </div>
        <?php
    }

    /**
     * Render border radius input.
     *
     * @since 1.0.0
     * @param array $args  Field arguments.
     * @param array $value Current field value.
     */
    private function render_radius_input( $args, $value ) {
        $placeholder = ! empty( $args['all_placeholder'] ) ? esc_attr( $args['all_placeholder'] ) : '';
        ?>
        <div class="pw-field-border-input pw-border-radius">
            <div class="pw-field-title"><?php esc_html_e( 'Radius', 'pearl-weather' ); ?></div>
            <?php if ( ! empty( $args['all_icon'] ) ) : ?>
                <span class="pw-field-icon"><?php echo wp_kses_post( $args['all_icon'] ); ?></span>
            <?php endif; ?>
            <input type="number" 
                   name="<?php echo esc_attr( $this->field_name( '[radius]' ) ); ?>" 
                   value="<?php echo esc_attr( $value['radius'] ); ?>" 
                   placeholder="<?php echo esc_attr( $placeholder ); ?>" 
                   class="pw-input-number pw-is-unit" />
            <?php if ( ! empty( $args['unit'] ) ) : ?>
                <span class="pw-field-unit"><?php echo esc_html( $args['unit'] ); ?></span>
            <?php endif; ?>
        </div>
        <?php
    }
}