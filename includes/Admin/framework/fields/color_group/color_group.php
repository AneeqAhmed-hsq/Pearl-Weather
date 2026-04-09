<?php
/**
 * Framework Color Group Field
 *
 * Renders a group of color picker fields with individual labels.
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
 * Class ColorGroupField
 *
 * Handles color group field rendering in the framework.
 *
 * @since 1.0.0
 */
class ColorGroupField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'options'    => array(),
        'default'    => array(),
        'alpha'      => false,
        'show_palette' => true,
        'show_input'   => true,
    );

    /**
     * Render the color group field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );
        $options = ! empty( $args['options'] ) ? $args['options'] : array();

        if ( empty( $options ) ) {
            return;
        }

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        echo '<div class="pw-color-group">';

        foreach ( $options as $key => $label ) {
            $this->render_color_picker( $key, $label, $args );
        }

        echo '</div>';

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render a single color picker in the group.
     *
     * @since 1.0.0
     * @param string $key   Field key.
     * @param string $label Field label.
     * @param array  $args  Field arguments.
     */
    private function render_color_picker( $key, $label, $args ) {
        $color_value = isset( $this->value[ $key ] ) ? $this->value[ $key ] : '';
        $default_value = isset( $args['default'][ $key ] ) ? $args['default'][ $key ] : '';

        $default_attr = ! empty( $default_value ) 
            ? ' data-default-color="' . esc_attr( $default_value ) . '"' 
            : '';

        $alpha_attr = ! empty( $args['alpha'] ) ? ' data-alpha="true"' : '';

        ?>
        <div class="pw-color-group-item">
            <div class="pw-color-label"><?php echo esc_html( $label ); ?></div>
            <div class="pw-color-picker-wrapper">
                <input type="text" 
                       name="<?php echo esc_attr( $this->field_name( '[' . $key . ']' ) ); ?>" 
                       value="<?php echo esc_attr( $color_value ); ?>" 
                       class="pw-color-picker" 
                       data-show-palette="<?php echo ! empty( $args['show_palette'] ) ? 'true' : 'false'; ?>"
                       data-show-input="<?php echo ! empty( $args['show_input'] ) ? 'true' : 'false'; ?>"
                       <?php echo $default_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                       <?php echo $alpha_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue color picker assets.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker-alpha' );
        
        add_action( 'admin_footer', array( $this, 'render_init_script' ) );
    }

    /**
     * Render color picker initialization script.
     *
     * @since 1.0.0
     */
    public function render_init_script() {
        ?>
        <script type="text/javascript">
        (function($) {
            'use strict';
            
            $(document).ready(function() {
                $('.pw-color-picker').each(function() {
                    var $input = $(this);
                    var options = {
                        defaultColor: $input.data('default-color') || '',
                        hide: true,
                        palettes: $input.data('palette') || []
                    };
                    
                    if ($input.data('alpha') === true) {
                        options.alpha = true;
                    }
                    
                    if ($input.data('show-palette') === false) {
                        options.palettes = false;
                    }
                    
                    if ($input.data('show-input') === false) {
                        options.showInput = false;
                    }
                    
                    $input.wpColorPicker(options);
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Sanitize the color group value.
     *
     * @since 1.0.0
     * @param array $value The color values array.
     * @return array
     */
    public static function sanitize( $value ) {
        if ( ! is_array( $value ) ) {
            return array();
        }

        $sanitized = array();

        foreach ( $value as $key => $color ) {
            // Check if it's a valid hex color.
            if ( preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color ) ) {
                $sanitized[ $key ] = $color;
            } elseif ( preg_match( '/^rgba?\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})(?:,\s*(\d?(?:\.\d+)?))?\)$/', $color ) ) {
                $sanitized[ $key ] = $color;
            } else {
                $sanitized[ $key ] = '';
            }
        }

        return $sanitized;
    }

    /**
     * Get the color group as CSS variables.
     *
     * @since 1.0.0
     * @param array  $colors   Color values.
     * @param string $prefix   CSS variable prefix.
     * @return string
     */
    public static function to_css_vars( $colors, $prefix = '--pw-color-' ) {
        if ( empty( $colors ) || ! is_array( $colors ) ) {
            return '';
        }

        $css = '';

        foreach ( $colors as $key => $value ) {
            if ( ! empty( $value ) ) {
                $css .= $prefix . $key . ': ' . $value . ";\n";
            }
        }

        return $css;
    }
}