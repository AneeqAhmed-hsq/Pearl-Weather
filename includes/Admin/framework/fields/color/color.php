<?php
/**
 * Framework Color Field
 *
 * Renders a color picker field using WordPress iris color picker.
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
 * Class ColorField
 *
 * Handles color picker field rendering in the framework.
 *
 * @since 1.0.0
 */
class ColorField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'alpha'      => false,
        'default'    => '',
        'palette'    => array(),
        'show_palette' => true,
        'show_input'   => true,
    );

    /**
     * Render the color picker field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $default_attr = ! empty( $args['default'] ) 
            ? ' data-default-color="' . esc_attr( $args['default'] ) . '"' 
            : '';

        $alpha_attr = ! empty( $args['alpha'] ) ? ' data-alpha="true"' : '';
        
        $palette_attr = '';
        if ( ! empty( $args['palette'] ) && is_array( $args['palette'] ) ) {
            $palette_attr = ' data-palette="' . esc_attr( wp_json_encode( $args['palette'] ) ) . '"';
        }

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        printf(
            '<input type="text" 
                    name="%s" 
                    value="%s" 
                    class="pw-color-picker" 
                    data-show-palette="%s"
                    data-show-input="%s"%s%s%s />',
            esc_attr( $this->field_name() ),
            esc_attr( $this->value ),
            ! empty( $args['show_palette'] ) ? 'true' : 'false',
            ! empty( $args['show_input'] ) ? 'true' : 'false',
            $default_attr,
            $alpha_attr,
            $palette_attr
        );

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
     * Sanitize the color value.
     *
     * @since 1.0.0
     * @param string $value The color value.
     * @return string
     */
    public static function sanitize( $value ) {
        // Check if it's a valid hex color.
        if ( preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value ) ) {
            return $value;
        }
        
        // Check if it's a valid rgba color.
        if ( preg_match( '/^rgba?\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})(?:,\s*(\d?(?:\.\d+)?))?\)$/', $value ) ) {
            return $value;
        }
        
        return '';
    }

    /**
     * Convert color to RGB array.
     *
     * @since 1.0.0
     * @param string $color The color value (hex or rgb/rgba).
     * @return array|null
     */
    public static function to_rgb( $color ) {
        // Handle hex.
        if ( strpos( $color, '#' ) === 0 ) {
            $color = ltrim( $color, '#' );
            
            if ( strlen( $color ) === 3 ) {
                $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
            }
            
            return array(
                'r' => hexdec( substr( $color, 0, 2 ) ),
                'g' => hexdec( substr( $color, 2, 2 ) ),
                'b' => hexdec( substr( $color, 4, 2 ) ),
                'a' => 1,
            );
        }
        
        // Handle rgb/rgba.
        if ( preg_match( '/^rgba?\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})(?:,\s*(\d?(?:\.\d+)?))?\)$/', $color, $matches ) ) {
            return array(
                'r' => (int) $matches[1],
                'g' => (int) $matches[2],
                'b' => (int) $matches[3],
                'a' => isset( $matches[4] ) ? (float) $matches[4] : 1,
            );
        }
        
        return null;
    }

    /**
     * Convert RGB array to hex color.
     *
     * @since 1.0.0
     * @param array $rgb RGB array with r, g, b keys.
     * @return string
     */
    public static function rgb_to_hex( $rgb ) {
        return sprintf( '#%02x%02x%02x', $rgb['r'], $rgb['g'], $rgb['b'] );
    }
}