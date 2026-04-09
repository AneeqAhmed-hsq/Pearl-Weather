<?php
/**
 * Framework Subheading Field
 *
 * Renders a subheading or descriptive text section within the admin settings.
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
 * Class SubheadingField
 *
 * Handles subheading field rendering in the framework.
 *
 * @since 1.0.0
 */
class SubheadingField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'content' => '',
        'tag'     => 'h3',
        'icon'    => '',
        'class'   => '',
    );

    /**
     * Render the subheading field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        if ( empty( $args['content'] ) ) {
            return;
        }

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        $tag = $this->validate_tag( $args['tag'] );
        $icon_html = ! empty( $args['icon'] ) ? '<span class="pw-subheading-icon">' . wp_kses_post( $args['icon'] ) . '</span>' : '';
        $class = ! empty( $args['class'] ) ? ' ' . sanitize_html_class( $args['class'] ) : '';

        printf(
            '<div class="pw-subheading-wrapper%s">',
            esc_attr( $class )
        );

        if ( ! empty( $icon_html ) ) {
            echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        printf(
            '<%1$s class="pw-subheading">%2$s</%1$s>',
            esc_attr( $tag ),
            wp_kses_post( $args['content'] )
        );

        echo '</div>';

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Validate HTML heading tag.
     *
     * @since 1.0.0
     * @param string $tag The HTML tag.
     * @return string
     */
    private function validate_tag( $tag ) {
        $allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div' );
        
        return in_array( $tag, $allowed_tags, true ) ? $tag : 'h3';
    }

    /**
     * Enqueue field-specific styles.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        wp_add_inline_style( 'pearl-weather-framework', '
            .pw-subheading-wrapper {
                display: flex;
                align-items: center;
                gap: 10px;
                margin: 20px 0 15px;
                padding-bottom: 8px;
                border-bottom: 1px solid #eee;
            }
            .pw-subheading-icon {
                display: inline-flex;
                align-items: center;
                color: #f26c0d;
                font-size: 20px;
            }
            .pw-subheading {
                margin: 0;
                font-size: 16px;
                font-weight: 600;
                color: #1e1e1e;
            }
            .pw-subheading-wrapper + .pw-field {
                margin-top: 0;
            }
        ' );
    }
}