<?php
/**
 * Framework Preview Field
 *
 * Renders a container for live preview of weather widgets.
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
 * Class PreviewField
 *
 * Handles preview field rendering for live widget previews.
 *
 * @since 1.0.0
 */
class PreviewField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'title'       => 'Live Preview',
        'height'      => 'auto',
        'width'       => '100%',
        'background'  => '#f5f5f5',
        'show_refresh' => true,
        'refresh_text' => 'Refresh Preview',
    );

    /**
     * Render the preview field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $height_style = 'auto' !== $args['height'] ? 'height: ' . esc_attr( $args['height'] ) . ';' : '';
        $width_style = '100%' !== $args['width'] ? 'width: ' . esc_attr( $args['width'] ) . ';' : '';

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        ?>
        <div class="pw-preview-field" data-preview-nonce="<?php echo esc_attr( wp_create_nonce( 'pw_preview_nonce' ) ); ?>">
            <?php if ( ! empty( $args['title'] ) ) : ?>
                <div class="pw-preview-header">
                    <h3 class="pw-preview-title"><?php echo esc_html( $args['title'] ); ?></h3>
                    <?php if ( $args['show_refresh'] ) : ?>
                        <button type="button" class="button pw-preview-refresh">
                            <?php echo esc_html( $args['refresh_text'] ); ?>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="pw-preview-container" style="<?php echo esc_attr( $height_style . $width_style ); ?> background: <?php echo esc_attr( $args['background'] ); ?>;">
                <div class="pw-preview-loading">
                    <div class="pw-preview-spinner"></div>
                    <span><?php esc_html_e( 'Loading preview...', 'pearl-weather' ); ?></span>
                </div>
                <div id="pw-preview-content" class="pw-preview-content">
                    <?php echo $this->render_preview_placeholder(); ?>
                </div>
            </div>
        </div>

        <style>
            .pw-preview-field {
                margin: 15px 0;
                border: 1px solid #ddd;
                border-radius: 8px;
                overflow: hidden;
                background: #fff;
            }
            .pw-preview-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 16px;
                background: #f8f9fa;
                border-bottom: 1px solid #ddd;
            }
            .pw-preview-title {
                margin: 0;
                font-size: 14px;
                font-weight: 600;
            }
            .pw-preview-refresh {
                font-size: 12px;
                padding: 4px 12px;
            }
            .pw-preview-container {
                position: relative;
                min-height: 300px;
                padding: 20px;
                transition: all 0.3s ease;
            }
            .pw-preview-loading {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255,255,255,0.9);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                z-index: 10;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            .pw-preview-loading.active {
                opacity: 1;
                visibility: visible;
            }
            .pw-preview-spinner {
                width: 40px;
                height: 40px;
                border: 3px solid #f3f3f3;
                border-top: 3px solid #f26c0d;
                border-radius: 50%;
                animation: pw-spin 0.8s linear infinite;
                margin-bottom: 12px;
            }
            @keyframes pw-spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .pw-preview-content {
                width: 100%;
                min-height: 260px;
            }
            .pw-preview-placeholder {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 260px;
                color: #999;
                font-style: italic;
                text-align: center;
            }
        </style>

        <script type="text/javascript">
        (function($) {
            'use strict';
            
            var previewTimeout;
            
            function loadPreview($field, widgetId, formData) {
                var $container = $field.find('.pw-preview-container');
                var $loading = $field.find('.pw-preview-loading');
                var $content = $field.find('#pw-preview-content');
                var nonce = $field.data('preview-nonce');
                
                $loading.addClass('active');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pearl_weather_preview_widget',
                        nonce: nonce,
                        widget_id: widgetId,
                        form_data: formData
                    },
                    success: function(response) {
                        $content.html(response);
                    },
                    error: function() {
                        $content.html('<div class="pw-preview-placeholder">Preview failed to load. Please try again.</div>');
                    },
                    complete: function() {
                        $loading.removeClass('active');
                    }
                });
            }
            
            $(document).ready(function() {
                $('.pw-preview-refresh').on('click', function() {
                    var $field = $(this).closest('.pw-preview-field');
                    var $form = $(this).closest('form');
                    var widgetId = $form.find('#post_ID').val();
                    var formData = $form.serialize();
                    
                    loadPreview($field, widgetId, formData);
                });
                
                // Auto-refresh on form field changes (debounced)
                $('.splwt-lite-form').on('change', 'input, select, textarea', function() {
                    var $form = $(this).closest('form');
                    var $field = $form.find('.pw-preview-field');
                    
                    if ($field.length && $field.data('auto-refresh') !== false) {
                        clearTimeout(previewTimeout);
                        previewTimeout = setTimeout(function() {
                            var widgetId = $form.find('#post_ID').val();
                            var formData = $form.serialize();
                            loadPreview($field, widgetId, formData);
                        }, 500);
                    }
                });
            });
        })(jQuery);
        </script>
        <?php

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render preview placeholder.
     *
     * @since 1.0.0
     * @return string
     */
    private function render_preview_placeholder() {
        $message = __( 'Click "Refresh Preview" to see a live preview of your weather widget.', 'pearl-weather' );
        
        return '<div class="pw-preview-placeholder">' . esc_html( $message ) . '</div>';
    }
}