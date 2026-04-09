<?php
/**
 * Framework Notice Field
 *
 * Renders a customizable notice/info box with different style variants.
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
 * Class NoticeField
 *
 * Handles notice field rendering in the framework.
 *
 * @since 1.0.0
 */
class NoticeField extends BaseField {

    /**
     * Available notice styles.
     *
     * @var array
     */
    private $styles = array(
        'normal'  => 'info',
        'info'    => 'info',
        'warning' => 'warning',
        'error'   => 'error',
        'success' => 'success',
    );

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'style'       => 'normal',
        'content'     => '',
        'dismissible' => false,
        'icon'        => '',
    );

    /**
     * Render the notice field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        if ( empty( $args['content'] ) ) {
            return;
        }

        $style = isset( $this->styles[ $args['style'] ] ) ? $this->styles[ $args['style'] ] : 'info';
        $dismissible_class = $args['dismissible'] ? ' pw-notice-dismissible' : '';
        $icon_html = $this->get_icon_html( $style );

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        printf(
            '<div class="pw-notice pw-notice-%s%s" data-notice-id="%s">%s<div class="pw-notice-content">%s</div>%s</div>',
            esc_attr( $style ),
            esc_attr( $dismissible_class ),
            esc_attr( $this->field_name() ),
            $icon_html,
            wp_kses_post( $args['content'] ),
            $args['dismissible'] ? '<button type="button" class="pw-notice-dismiss"><span class="screen-reader-text">Dismiss</span></button>' : ''
        );

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Get icon HTML for notice type.
     *
     * @since 1.0.0
     * @param string $style Notice style.
     * @return string
     */
    private function get_icon_html( $style ) {
        $icons = array(
            'info'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/><path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
            'warning' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 9V13M12 17H12.01M4.952 20H19.048C20.6 20 21.6 18.333 20.824 17L13.776 4C13 2.667 11 2.667 10.224 4L3.176 17C2.4 18.333 3.4 20 4.952 20Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
            'error'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/><path d="M15 9L9 15M9 9L15 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
            'success' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/><path d="M8 12L11 15L16 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        );

        return isset( $icons[ $style ] ) ? '<div class="pw-notice-icon">' . $icons[ $style ] . '</div>' : '';
    }

    /**
     * Enqueue field-specific assets.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        wp_add_inline_style( 'pearl-weather-framework', '
            .pw-notice {
                position: relative;
                display: flex;
                align-items: flex-start;
                gap: 12px;
                padding: 12px 16px;
                margin: 10px 0;
                border-left: 4px solid;
                border-radius: 4px;
                background: #fff;
                box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            }
            .pw-notice-info {
                border-left-color: #00a0d2;
                background: #f0f8ff;
            }
            .pw-notice-warning {
                border-left-color: #f0ad4e;
                background: #fff9e6;
            }
            .pw-notice-error {
                border-left-color: #dc3232;
                background: #fff5f5;
            }
            .pw-notice-success {
                border-left-color: #46b450;
                background: #f0fff4;
            }
            .pw-notice-icon {
                flex-shrink: 0;
                line-height: 1;
            }
            .pw-notice-icon svg {
                display: block;
            }
            .pw-notice-content {
                flex: 1;
                font-size: 13px;
                line-height: 1.5;
            }
            .pw-notice-content p {
                margin: 0 0 5px 0;
            }
            .pw-notice-content p:last-child {
                margin-bottom: 0;
            }
            .pw-notice-content a {
                color: #0073aa;
                text-decoration: none;
            }
            .pw-notice-content a:hover {
                text-decoration: underline;
            }
            .pw-notice-dismiss {
                position: absolute;
                top: 8px;
                right: 8px;
                background: transparent;
                border: none;
                cursor: pointer;
                padding: 4px;
                color: #999;
            }
            .pw-notice-dismiss:hover {
                color: #333;
            }
            .pw-notice-dismiss:before {
                content: "×";
                font-size: 16px;
                font-weight: 600;
            }
            .pw-notice-dismissible {
                padding-right: 32px;
            }
        ' );

        wp_add_inline_script( 'pearl-weather-framework', '
            (function($) {
                $(document).on("click", ".pw-notice-dismiss", function() {
                    var $notice = $(this).closest(".pw-notice");
                    var noticeId = $notice.data("notice-id");
                    
                    $.post(ajaxurl, {
                        action: "pw_dismiss_notice",
                        notice_id: noticeId,
                        nonce: pwFramework.nonce
                    });
                    
                    $notice.fadeOut(300, function() {
                        $(this).remove();
                    });
                });
            })(jQuery);
        ' );
    }
}