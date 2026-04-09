<?php
/**
 * Framework Code Editor Field
 *
 * Renders a code editor field using WordPress CodeMirror for syntax highlighting.
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
 * Class CodeEditorField
 *
 * Handles code editor field rendering with CodeMirror integration.
 *
 * @since 1.0.0
 */
class CodeEditorField extends BaseField {

    /**
     * Default CodeMirror settings.
     *
     * @var array
     */
    private $default_settings = array(
        'tabSize'     => 2,
        'lineNumbers' => true,
        'theme'       => 'default',
        'mode'        => 'css',
        'lineWrapping' => true,
        'indentUnit'  => 2,
        'autoCloseBrackets' => true,
    );

    /**
     * Supported editor modes.
     *
     * @var array
     */
    private $supported_modes = array(
        'css'      => 'CSS',
        'javascript' => 'JavaScript',
        'php'      => 'PHP',
        'html'     => 'HTML',
        'xml'      => 'XML',
        'scss'     => 'SCSS',
        'less'     => 'LESS',
        'markdown' => 'Markdown',
    );

    /**
     * Render the code editor field.
     *
     * @since 1.0.0
     */
    public function render() {
        $settings = wp_parse_args( $this->get_field_settings(), $this->default_settings );
        
        // Enqueue editor assets.
        $this->enqueue();

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        $editor_attr = ' data-editor="' . esc_attr( wp_json_encode( $settings ) ) . '"';
        $mode_attr = ! empty( $settings['mode'] ) ? ' data-mode="' . esc_attr( $settings['mode'] ) . '"' : '';

        printf(
            '<textarea name="%s"%s%s%s class="pw-code-editor">%s</textarea>',
            esc_attr( $this->field_name() ),
            $this->field_attributes(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            $editor_attr,
            $mode_attr,
            esc_textarea( $this->value )
        );

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Get field settings merged with defaults.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_field_settings() {
        if ( empty( $this->field['settings'] ) ) {
            return $this->default_settings;
        }

        return wp_parse_args( $this->field['settings'], $this->default_settings );
    }

    /**
     * Enqueue code editor assets.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        // Enqueue WordPress code editor assets.
        wp_enqueue_script( 'code-editor' );
        wp_enqueue_style( 'code-editor' );

        // Enqueue additional CodeMirror add-ons.
        wp_enqueue_script( 'codemirror-mode-css' );
        wp_enqueue_script( 'codemirror-mode-javascript' );
        wp_enqueue_script( 'codemirror-mode-html' );

        // Add inline script to initialize the editor.
        add_action( 'admin_footer', array( $this, 'render_editor_init_script' ) );
    }

    /**
     * Render editor initialization script.
     *
     * @since 1.0.0
     */
    public function render_editor_init_script() {
        ?>
        <script type="text/javascript">
        (function($) {
            'use strict';
            
            $(document).ready(function() {
                $('.pw-code-editor').each(function() {
                    var $textarea = $(this);
                    var settings = $textarea.data('editor');
                    
                    if (settings && typeof wp !== 'undefined' && wp.codeEditor) {
                        var editorSettings = wp.codeEditor.defaultSettings;
                        
                        if (settings) {
                            editorSettings.codemirror = $.extend({}, editorSettings.codemirror, settings);
                        }
                        
                        wp.codeEditor.initialize($textarea, editorSettings);
                    }
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Get available editor modes.
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_available_modes() {
        $instance = new self( array(), '' );
        return $instance->supported_modes;
    }

    /**
     * Validate the code editor content.
     *
     * @since 1.0.0
     * @param string $value The code content.
     * @return string|true
     */
    public static function validate( $value ) {
        // Allow CSS, JS, HTML, PHP content with basic sanitization.
        $value = wp_unslash( $value );
        
        // Remove potential harmful content.
        $value = wp_strip_all_tags( $value );
        
        // Decode HTML entities.
        $value = html_entity_decode( $value, ENT_QUOTES, 'UTF-8' );
        
        return $value;
    }
}