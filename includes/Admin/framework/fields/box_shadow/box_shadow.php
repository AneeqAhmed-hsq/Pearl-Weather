<?php
/**
 * Framework Box Shadow Field
 *
 * Renders a box shadow configuration control with offset inputs,
 * blur, spread, style selector, and color picker.
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
 * Class BoxShadowField
 *
 * Handles box shadow configuration in the framework.
 *
 * @since 1.0.0
 */
class BoxShadowField extends BaseField {

    /**
     * Box shadow style options.
     *
     * @var array
     */
    private $shadow_styles = array(
        'inset'  => 'Inset',
        'outset' => 'Outset',
    );

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'vertical_icon'          => 'Y offset',
        'horizontal_icon'        => 'X offset',
        'blur_icon'              => 'Blur',
        'spread_icon'            => 'Spread',
        'vertical_placeholder'   => 'v-offset',
        'horizontal_placeholder' => 'h-offset',
        'blur_placeholder'       => 'blur',
        'spread_placeholder'     => 'spread',
        'vertical'               => true,
        'horizontal'             => true,
        'blur'                   => true,
        'spread'                 => true,
        'color'                  => true,
        'hover_color'            => false,
        'style'                  => true,
        'unit'                   => 'px',
    );

    /**
     * Render the box shadow field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $default_value = array(
            'vertical'    => '',
            'horizontal'  => '',
            'blur'        => '',
            'spread'      => '',
            'color'       => '',
            'style'       => 'outset',
            'hover_color' => '',
        );

        if ( ! empty( $this->field['default'] ) ) {
            $default_value = wp_parse_args( $this->field['default'], $default_value );
        }

        $value = wp_parse_args( $this->value, $default_value );

        // Output field wrapper.
        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        // Inputs container.
        echo '<div class="pw-field-box-shadow-inputs">';

        $properties = array();

        foreach ( array( 'vertical', 'horizontal', 'blur', 'spread' ) as $prop ) {
            if ( ! empty( $args[ $prop ] ) ) {
                $properties[] = $prop;
            }
        }

        foreach ( $properties as $property ) {
            $this->render_shadow_input( $args, $property, $value );
        }

        if ( ! empty( $args['style'] ) ) {
            $this->render_style_select( $value );
        }

        echo '</div>';

        // Color pickers.
        if ( ! empty( $args['color'] ) ) {
            $this->render_color_picker( 'color', __( 'Color', 'pearl-weather' ), $value['color'], $default_value['color'] );
        }

        if ( ! empty( $args['hover_color'] ) ) {
            $this->render_color_picker( 'hover_color', __( 'Hover Color', 'pearl-weather' ), $value['hover_color'], $default_value['hover_color'] );
        }

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render a single shadow input field.
     *
     * @since 1.0.0
     * @param array  $args     Field arguments.
     * @param string $property Property name (vertical, horizontal, blur, spread).
     * @param array  $value    Current field value.
     */
    private function render_shadow_input( $args, $property, $value ) {
        $placeholder = ! empty( $args[ $property . '_placeholder' ] ) 
            ? ' placeholder="' . esc_attr( $args[ $property . '_placeholder' ] ) . '"' 
            : '';

        $icon = ! empty( $args[ $property . '_icon' ] ) ? $args[ $property . '_icon' ] : '';

        ?>
        <div class="pw-field-box-shadow-input">
            <?php if ( ! empty( $icon ) ) : ?>
                <div class="pw-field-title"><?php echo esc_html( $icon ); ?></div>
            <?php endif; ?>
            <div class="pw-input-wrapper">
                <input type="number" 
                       name="<?php echo esc_attr( $this->field_name( '[' . $property . ']' ) ); ?>" 
                       value="<?php echo esc_attr( $value[ $property ] ); ?>" 
                       <?php echo $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                       class="pw-input-number pw-is-unit" 
                       step="any" />
                <?php if ( ! empty( $args['unit'] ) ) : ?>
                    <span class="pw-field-unit"><?php echo esc_html( $args['unit'] ); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render style select dropdown.
     *
     * @since 1.0.0
     * @param array $value Current field value.
     */
    private function render_style_select( $value ) {
        ?>
        <div class="pw-field-box-shadow-input pw-field-style-select">
            <select name="<?php echo esc_attr( $this->field_name( '[style]' ) ); ?>" class="pw-field-select">
                <?php foreach ( $this->shadow_styles as $style_key => $style_label ) : ?>
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
        <div class="pw-field-box-shadow-color">
            <div class="pw-color-picker-wrapper">
                <div class="pw-field-title"><?php echo esc_html( $title ); ?></div>
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
     * Generate CSS from box shadow values.
     *
     * @since 1.0.0
     * @param array $value Box shadow values.
     * @return string
     */
    public static function generate_css( $value ) {
        if ( empty( $value ) || ! is_array( $value ) ) {
            return '';
        }

        $parts = array();

        if ( ! empty( $value['horizontal'] ) ) {
            $parts[] = $value['horizontal'] . 'px';
        } else {
            $parts[] = '0';
        }

        if ( ! empty( $value['vertical'] ) ) {
            $parts[] = $value['vertical'] . 'px';
        } else {
            $parts[] = '0';
        }

        if ( ! empty( $value['blur'] ) ) {
            $parts[] = $value['blur'] . 'px';
        } else {
            $parts[] = '0';
        }

        if ( ! empty( $value['spread'] ) ) {
            $parts[] = $value['spread'] . 'px';
        } else {
            $parts[] = '0';
        }

        if ( ! empty( $value['color'] ) ) {
            $parts[] = $value['color'];
        } else {
            $parts[] = 'rgba(0,0,0,0.3)';
        }

        if ( ! empty( $value['style'] ) && 'inset' === $value['style'] ) {
            $parts[] = 'inset';
        }

        return implode( ' ', $parts );
    }

    /**
     * Generate hover CSS from box shadow values.
     *
     * @since 1.0.0
     * @param array $value Box shadow values.
     * @return string
     */
    public static function generate_hover_css( $value ) {
        if ( empty( $value ) || ! is_array( $value ) ) {
            return '';
        }

        if ( empty( $value['hover_color'] ) ) {
            return '';
        }

        $parts = array();

        $parts[] = ! empty( $value['horizontal'] ) ? $value['horizontal'] . 'px' : '0';
        $parts[] = ! empty( $value['vertical'] ) ? $value['vertical'] . 'px' : '0';
        $parts[] = ! empty( $value['blur'] ) ? $value['blur'] . 'px' : '0';
        $parts[] = ! empty( $value['spread'] ) ? $value['spread'] . 'px' : '0';
        $parts[] = $value['hover_color'];

        if ( ! empty( $value['style'] ) && 'inset' === $value['style'] ) {
            $parts[] = 'inset';
        }

        return implode( ' ', $parts );
    }
}