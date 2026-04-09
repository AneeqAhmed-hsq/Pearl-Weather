<?php
/**
 * Framework Typography Field
 *
 * Renders a comprehensive typography control with Google Fonts integration.
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
 * Class TypographyField
 *
 * Handles typography field rendering in the framework.
 *
 * @since 1.0.0
 */
class TypographyField extends BaseField {

    /**
     * Whether to use Chosen.js.
     *
     * @var bool
     */
    private $use_chosen = false;

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'font_family'        => true,
        'font_weight'        => true,
        'font_style'         => true,
        'font_size'          => true,
        'line_height'        => true,
        'letter_spacing'     => true,
        'text_align'         => true,
        'text_transform'     => true,
        'color'              => false,
        'chosen'             => true,
        'preview'            => true,
        'unit'               => 'px',
        'preview_text'       => 'The quick brown fox jumps over the lazy dog',
        'margin_top'         => false,
        'margin_bottom'      => false,
    );

    /**
     * Font weight options.
     *
     * @var array
     */
    private $font_weights = array(
        '100' => 'Thin (100)',
        '200' => 'Extra Light (200)',
        '300' => 'Light (300)',
        '400' => 'Normal (400)',
        '500' => 'Medium (500)',
        '600' => 'Semi Bold (600)',
        '700' => 'Bold (700)',
        '800' => 'Extra Bold (800)',
        '900' => 'Black (900)',
    );

    /**
     * Font style options.
     *
     * @var array
     */
    private $font_styles = array(
        'normal'  => 'Normal',
        'italic'  => 'Italic',
        'oblique' => 'Oblique',
    );

    /**
     * Text align options.
     *
     * @var array
     */
    private $text_aligns = array(
        'inherit' => 'Inherit',
        'left'    => 'Left',
        'center'  => 'Center',
        'right'   => 'Right',
        'justify' => 'Justify',
    );

    /**
     * Text transform options.
     *
     * @var array
     */
    private $text_transforms = array(
        'none'       => 'None',
        'capitalize' => 'Capitalize',
        'uppercase'  => 'Uppercase',
        'lowercase'  => 'Lowercase',
    );

    /**
     * Text decoration options.
     *
     * @var array
     */
    private $text_decorations = array(
        'none'               => 'None',
        'underline'          => 'Underline',
        'line-through'       => 'Line Through',
        'underline overline' => 'Underline Overline',
    );

    /**
     * Constructor.
     *
     * @param array  $field  Field configuration.
     * @param mixed  $value  Field value.
     * @param string $unique Unique identifier.
     * @param string $where  Where the field is used.
     * @param string $parent Parent field.
     */
    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
        parent::__construct( $field, $value, $unique, $where, $parent );
        $this->use_chosen = isset( $field['chosen'] ) ? (bool) $field['chosen'] : true;
    }

    /**
     * Render the typography field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $default_value = array(
            'font-family'    => '',
            'font-weight'    => '',
            'font-style'     => '',
            'font-size'      => '',
            'line-height'    => '',
            'letter-spacing' => '',
            'text-align'     => '',
            'text-transform' => '',
            'color'          => '',
            'margin-top'     => '',
            'margin-bottom'  => '',
        );

        $default_value = ! empty( $this->field['default'] ) 
            ? wp_parse_args( $this->field['default'], $default_value ) 
            : $default_value;
        
        $this->value = wp_parse_args( $this->value, $default_value );
        $chosen_class = $this->use_chosen ? ' pw-typography-chosen' : '';

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        echo '<div class="pw-typography-wrapper' . esc_attr( $chosen_class ) . '">';

        // Font Family.
        if ( $args['font_family'] ) {
            $this->render_font_family();
        }

        // Font Weight & Style.
        if ( $args['font_weight'] || $args['font_style'] ) {
            $this->render_font_style();
        }

        // Text Align.
        if ( $args['text_align'] ) {
            $this->render_text_align();
        }

        // Text Transform.
        if ( $args['text_transform'] ) {
            $this->render_text_transform();
        }

        // Font Size, Line Height, Letter Spacing.
        $this->render_input_controls( $args );

        // Color.
        if ( $args['color'] ) {
            $this->render_color_picker();
        }

        // Margin Controls.
        if ( $args['margin_top'] || $args['margin_bottom'] ) {
            $this->render_margin_controls( $args );
        }

        // Preview.
        if ( $args['preview'] ) {
            $this->render_preview( $args );
        }

        echo '</div>';

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render font family select.
     *
     * @since 1.0.0
     */
    private function render_font_family() {
        $fonts = $this->get_google_fonts();
        $current_font = $this->value['font-family'];
        ?>
        <div class="pw-typography-block">
            <div class="pw-typography-title"><?php esc_html_e( 'Font Family', 'pearl-weather' ); ?></div>
            <select name="<?php echo esc_attr( $this->field_name( '[font-family]' ) ); ?>" 
                    class="pw-font-family-select" 
                    data-placeholder="<?php esc_attr_e( 'Select a font', 'pearl-weather' ); ?>">
                <option value=""><?php esc_html_e( 'Default', 'pearl-weather' ); ?></option>
                <?php foreach ( $fonts as $font ) : ?>
                    <option value="<?php echo esc_attr( $font ); ?>" <?php selected( $current_font, $font ); ?>>
                        <?php echo esc_html( $font ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /**
     * Render font weight and style.
     *
     * @since 1.0.0
     */
    private function render_font_style() {
        ?>
        <div class="pw-typography-block">
            <div class="pw-typography-title"><?php esc_html_e( 'Font Style', 'pearl-weather' ); ?></div>
            <select class="pw-font-style-select" data-placeholder="<?php esc_attr_e( 'Default', 'pearl-weather' ); ?>">
                <option value=""></option>
                <?php
                $current_weight = $this->value['font-weight'];
                $current_style = $this->value['font-style'];
                $current = $current_weight . $current_style;
                ?>
                <option value="<?php echo esc_attr( $current ); ?>" selected></option>
            </select>
            <input type="hidden" name="<?php echo esc_attr( $this->field_name( '[font-weight]' ) ); ?>" 
                   class="pw-font-weight" value="<?php echo esc_attr( $current_weight ); ?>" />
            <input type="hidden" name="<?php echo esc_attr( $this->field_name( '[font-style]' ) ); ?>" 
                   class="pw-font-style" value="<?php echo esc_attr( $current_style ); ?>" />
        </div>
        <?php
    }

    /**
     * Render text align select.
     *
     * @since 1.0.0
     */
    private function render_text_align() {
        ?>
        <div class="pw-typography-block">
            <div class="pw-typography-title"><?php esc_html_e( 'Text Align', 'pearl-weather' ); ?></div>
            <select name="<?php echo esc_attr( $this->field_name( '[text-align]' ) ); ?>" class="pw-text-align-select">
                <option value=""><?php esc_html_e( 'Default', 'pearl-weather' ); ?></option>
                <?php foreach ( $this->text_aligns as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $this->value['text-align'], $value ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /**
     * Render text transform select.
     *
     * @since 1.0.0
     */
    private function render_text_transform() {
        ?>
        <div class="pw-typography-block">
            <div class="pw-typography-title"><?php esc_html_e( 'Text Transform', 'pearl-weather' ); ?></div>
            <select name="<?php echo esc_attr( $this->field_name( '[text-transform]' ) ); ?>" class="pw-text-transform-select">
                <option value=""><?php esc_html_e( 'Default', 'pearl-weather' ); ?></option>
                <?php foreach ( $this->text_transforms as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $this->value['text-transform'], $value ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /**
     * Render input controls (font size, line height, letter spacing).
     *
     * @since 1.0.0
     * @param array $args Field arguments.
     */
    private function render_input_controls( $args ) {
        ?>
        <div class="pw-typography-inputs">
            <?php if ( $args['font_size'] ) : ?>
                <div class="pw-typography-input">
                    <div class="pw-typography-title"><?php esc_html_e( 'Font Size', 'pearl-weather' ); ?></div>
                    <div class="pw-input-wrap">
                        <input type="number" 
                               name="<?php echo esc_attr( $this->field_name( '[font-size]' ) ); ?>" 
                               class="pw-font-size" 
                               value="<?php echo esc_attr( $this->value['font-size'] ); ?>" />
                        <span class="pw-unit"><?php echo esc_html( $args['unit'] ); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( $args['line_height'] ) : ?>
                <div class="pw-typography-input">
                    <div class="pw-typography-title"><?php esc_html_e( 'Line Height', 'pearl-weather' ); ?></div>
                    <div class="pw-input-wrap">
                        <input type="number" 
                               name="<?php echo esc_attr( $this->field_name( '[line-height]' ) ); ?>" 
                               class="pw-line-height" 
                               value="<?php echo esc_attr( $this->value['line-height'] ); ?>" />
                        <span class="pw-unit"><?php echo esc_html( $args['unit'] ); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( $args['letter_spacing'] ) : ?>
                <div class="pw-typography-input">
                    <div class="pw-typography-title"><?php esc_html_e( 'Letter Spacing', 'pearl-weather' ); ?></div>
                    <div class="pw-input-wrap">
                        <input type="number" 
                               name="<?php echo esc_attr( $this->field_name( '[letter-spacing]' ) ); ?>" 
                               class="pw-letter-spacing" 
                               value="<?php echo esc_attr( $this->value['letter-spacing'] ); ?>" />
                        <span class="pw-unit"><?php echo esc_html( $args['unit'] ); ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render color picker.
     *
     * @since 1.0.0
     */
    private function render_color_picker() {
        $default_color = ! empty( $this->field['default']['color'] ) ? $this->field['default']['color'] : '';
        $default_attr = $default_color ? ' data-default-color="' . esc_attr( $default_color ) . '"' : '';
        ?>
        <div class="pw-typography-block pw-color-block">
            <div class="pw-typography-title"><?php esc_html_e( 'Font Color', 'pearl-weather' ); ?></div>
            <input type="text" 
                   name="<?php echo esc_attr( $this->field_name( '[color]' ) ); ?>" 
                   class="pw-color-picker" 
                   value="<?php echo esc_attr( $this->value['color'] ); ?>" 
                   <?php echo $default_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
        </div>
        <?php
    }

    /**
     * Render margin controls.
     *
     * @since 1.0.0
     * @param array $args Field arguments.
     */
    private function render_margin_controls( $args ) {
        ?>
        <div class="pw-typography-margins">
            <?php if ( $args['margin_top'] ) : ?>
                <div class="pw-typography-input">
                    <div class="pw-typography-title"><?php esc_html_e( 'Margin Top', 'pearl-weather' ); ?></div>
                    <div class="pw-input-wrap">
                        <span class="pw-icon">▲</span>
                        <input type="number" 
                               name="<?php echo esc_attr( $this->field_name( '[margin-top]' ) ); ?>" 
                               class="pw-margin-top" 
                               value="<?php echo esc_attr( $this->value['margin-top'] ); ?>" />
                        <span class="pw-unit"><?php echo esc_html( $args['unit'] ); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( $args['margin_bottom'] ) : ?>
                <div class="pw-typography-input">
                    <div class="pw-typography-title"><?php esc_html_e( 'Margin Bottom', 'pearl-weather' ); ?></div>
                    <div class="pw-input-wrap">
                        <span class="pw-icon">▼</span>
                        <input type="number" 
                               name="<?php echo esc_attr( $this->field_name( '[margin-bottom]' ) ); ?>" 
                               class="pw-margin-bottom" 
                               value="<?php echo esc_attr( $this->value['margin-bottom'] ); ?>" />
                        <span class="pw-unit"><?php echo esc_html( $args['unit'] ); ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render preview section.
     *
     * @since 1.0.0
     * @param array $args Field arguments.
     */
    private function render_preview( $args ) {
        $preview_text = ! empty( $args['preview_text'] ) ? $args['preview_text'] : __( 'The quick brown fox jumps over the lazy dog', 'pearl-weather' );
        ?>
        <div class="pw-typography-preview">
            <div class="pw-preview-toggle">
                <span class="pw-toggle-icon">▼</span>
                <span class="pw-toggle-text"><?php esc_html_e( 'Show Preview', 'pearl-weather' ); ?></span>
            </div>
            <div class="pw-preview-content" style="display: none;">
                <?php echo esc_html( $preview_text ); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get Google Fonts list.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_google_fonts() {
        $fonts = get_transient( 'pw_google_fonts' );
        
        if ( false === $fonts ) {
            $response = wp_remote_get( 'https://www.googleapis.com/webfonts/v1/webfonts?key=' . PW_GOOGLE_FONTS_API_KEY );
            
            if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                $body = json_decode( wp_remote_retrieve_body( $response ), true );
                $fonts = array();
                
                if ( isset( $body['items'] ) ) {
                    foreach ( $body['items'] as $item ) {
                        $fonts[] = $item['family'];
                    }
                }
                
                set_transient( 'pw_google_fonts', $fonts, WEEK_IN_SECONDS );
            } else {
                $fonts = $this->get_fallback_fonts();
            }
        }
        
        return $fonts;
    }

    /**
     * Get fallback fonts list.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_fallback_fonts() {
        return array(
            'Arial', 'Helvetica', 'Verdana', 'Georgia', 'Times New Roman',
            'Courier New', 'Trebuchet MS', 'Impact', 'Comic Sans MS',
            'Tahoma', 'Palatino', 'Lucida Sans', 'Bookman', 'Garamond',
        );
    }

    /**
     * Enqueue field assets.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_style( 'wp-color-picker' );
        
        if ( $this->use_chosen ) {
            wp_enqueue_script( 'chosen' );
            wp_enqueue_style( 'chosen' );
        }
    }
}