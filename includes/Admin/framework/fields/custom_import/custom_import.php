<?php
/**
 * Framework Custom Import Field
 *
 * Renders a file upload field for importing JSON configuration files.
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
 * Class CustomImportField
 *
 * Handles custom import field rendering with file upload and JSON validation.
 *
 * @since 1.0.0
 */
class CustomImportField extends BaseField {

    /**
     * Allowed file extensions.
     *
     * @var array
     */
    private $allowed_extensions = array( 'json' );

    /**
     * Maximum file size (in bytes).
     *
     * @var int
     */
    private $max_file_size = 2097152; // 2 MB

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'import_label'    => 'Import',
        'file_label'      => 'Choose JSON file',
        'redirect_url'    => '',
        'success_message' => 'Import completed successfully.',
        'error_message'   => 'Import failed. Please check your file and try again.',
    );

    /**
     * Render the custom import field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $redirect_url = ! empty( $args['redirect_url'] ) 
            ? esc_url( $args['redirect_url'] ) 
            : admin_url( 'edit.php?post_type=pearl_weather_widget' );

        $import_label = ! empty( $args['import_label'] ) ? $args['import_label'] : __( 'Import', 'pearl-weather' );
        $file_label = ! empty( $args['file_label'] ) ? $args['file_label'] : __( 'Choose JSON file', 'pearl-weather' );

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        ?>
        <div class="pw-custom-import-field">
            <div class="pw-import-file-wrapper">
                <input type="file" 
                       id="pw-import-file" 
                       accept=".json" 
                       class="pw-import-file-input" />
                <div class="pw-import-message"></div>
            </div>
            <div class="pw-import-actions">
                <button type="button" 
                        class="button button-primary pw-import-btn" 
                        data-redirect="<?php echo esc_attr( $redirect_url ); ?>"
                        data-import-label="<?php echo esc_attr( $import_label ); ?>"
                        data-file-label="<?php echo esc_attr( $file_label ); ?>"
                        data-success-msg="<?php echo esc_attr( $args['success_message'] ); ?>"
                        data-error-msg="<?php echo esc_attr( $args['error_message'] ); ?>">
                    <?php echo esc_html( $import_label ); ?>
                </button>
                <span class="spinner"></span>
            </div>
            <div class="pw-import-note">
                <small><?php esc_html_e( 'Only JSON files are allowed. Maximum file size: 2 MB.', 'pearl-weather' ); ?></small>
            </div>
        </div>

        <style>
            .pw-custom-import-field {
                padding: 15px 0;
            }
            .pw-import-file-wrapper {
                margin-bottom: 15px;
            }
            .pw-import-file-input {
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                width: 100%;
                max-width: 400px;
            }
            .pw-import-actions {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 10px;
            }
            .pw-import-message {
                margin-top: 10px;
                padding: 8px 12px;
                border-radius: 4px;
            }
            .pw-import-message.success {
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .pw-import-message.error {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .pw-import-note {
                color: #666;
                font-style: italic;
            }
        </style>

        <script type="text/javascript">
        (function($) {
            'use strict';
            
            $(document).ready(function() {
                $('.pw-import-btn').on('click', function(e) {
                    e.preventDefault();
                    
                    var $btn = $(this);
                    var $spinner = $btn.siblings('.spinner');
                    var $messageDiv = $btn.closest('.pw-custom-import-field').find('.pw-import-message');
                    var fileInput = $btn.closest('.pw-custom-import-field').find('.pw-import-file-input')[0];
                    var redirectUrl = $btn.data('redirect');
                    
                    if (!fileInput.files || !fileInput.files[0]) {
                        $messageDiv.html('<span class="error">Please select a JSON file to import.</span>');
                        return;
                    }
                    
                    var file = fileInput.files[0];
                    var fileName = file.name;
                    var fileExt = fileName.split('.').pop().toLowerCase();
                    
                    if (fileExt !== 'json') {
                        $messageDiv.html('<span class="error">Invalid file type. Please upload a JSON file.</span>');
                        return;
                    }
                    
                    if (file.size > 2097152) {
                        $messageDiv.html('<span class="error">File size exceeds 2 MB limit.</span>');
                        return;
                    }
                    
                    var reader = new FileReader();
                    
                    reader.onload = function(evt) {
                        var fileContent = evt.target.result;
                        var nonce = pwAdmin ? pwAdmin.nonce : '';
                        
                        $btn.prop('disabled', true);
                        $spinner.addClass('is-active');
                        $messageDiv.html('');
                        
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'pw_import_settings',
                                nonce: nonce,
                                import_data: fileContent,
                                option_key: '<?php echo esc_js( $this->unique ); ?>'
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    $messageDiv.html('<span class="success">' + (response.data.message || $btn.data('success-msg')) + '</span>');
                                    if (redirectUrl) {
                                        setTimeout(function() {
                                            window.location.href = redirectUrl;
                                        }, 1500);
                                    }
                                } else {
                                    $messageDiv.html('<span class="error">' + (response.data.message || $btn.data('error-msg')) + '</span>');
                                }
                            },
                            error: function() {
                                $messageDiv.html('<span class="error">Request failed. Please try again.</span>');
                            },
                            complete: function() {
                                $btn.prop('disabled', false);
                                $spinner.removeClass('is-active');
                            }
                        });
                    };
                    
                    reader.onerror = function() {
                        $messageDiv.html('<span class="error">Error reading file.</span>');
                    };
                    
                    reader.readAsText(file);
                });
            });
        })(jQuery);
        </script>
        <?php

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Process the imported data.
     *
     * @since 1.0.0
     * @param string $json_data   JSON string from file.
     * @param string $option_key  Option key to update.
     * @return array
     */
    public static function process_import( $json_data, $option_key ) {
        // Decode JSON.
        $data = json_decode( $json_data, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return array(
                'success' => false,
                'message' => __( 'Invalid JSON format.', 'pearl-weather' ),
            );
        }

        if ( empty( $data ) || ! is_array( $data ) ) {
            return array(
                'success' => false,
                'message' => __( 'No valid data found in file.', 'pearl-weather' ),
            );
        }

        // Sanitize data.
        $sanitized = self::sanitize_import_data( $data );

        // Update option.
        update_option( $option_key, $sanitized );

        return array(
            'success' => true,
            'message' => __( 'Import completed successfully.', 'pearl-weather' ),
        );
    }

    /**
     * Sanitize imported data.
     *
     * @since 1.0.0
     * @param array $data The imported data.
     * @return array
     */
    private static function sanitize_import_data( $data ) {
        $sanitized = array();

        foreach ( $data as $key => $value ) {
            $key = sanitize_key( $key );

            if ( is_array( $value ) ) {
                $sanitized[ $key ] = self::sanitize_import_data( $value );
            } elseif ( is_string( $value ) ) {
                $sanitized[ $key ] = sanitize_text_field( $value );
            } elseif ( is_numeric( $value ) ) {
                $sanitized[ $key ] = intval( $value );
            } elseif ( is_bool( $value ) ) {
                $sanitized[ $key ] = (bool) $value;
            } else {
                $sanitized[ $key ] = $value;
            }
        }

        return $sanitized;
    }
}