<?php
/**
 * Framework Tabbed Field
 *
 * Renders a tabbed interface where each tab contains a group of nested fields.
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
 * Class TabbedField
 *
 * Handles tabbed field rendering in the framework.
 *
 * @since 1.0.0
 */
class TabbedField extends BaseField {

    /**
     * Field types not allowed inside tabs.
     *
     * @var array
     */
    private $disallowed_types = array( 'tabbed' );

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'tabs'           => array(),
        'active_tab'     => 0,
        'orientation'    => 'horizontal', // horizontal, vertical
    );

    /**
     * Render the tabbed field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        if ( empty( $args['tabs'] ) ) {
            return;
        }

        $orientation_class = 'pw-tabbed-' . $args['orientation'];

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        ?>
        <div class="pw-tabbed-wrapper <?php echo esc_attr( $orientation_class ); ?>">
            
            <div class="pw-tabbed-nav" role="tablist">
                <?php foreach ( $args['tabs'] as $index => $tab ) : ?>
                    <?php
                    $tab_id = sanitize_title( $tab['title'] );
                    $active_class = ( $index === (int) $args['active_tab'] ) ? ' pw-tab-active' : '';
                    $icon_html = ! empty( $tab['icon'] ) ? '<span class="pw-tab-icon">' . wp_kses_post( $tab['icon'] ) . '</span>' : '';
                    ?>
                    <button type="button" 
                            class="pw-tab-button<?php echo esc_attr( $active_class ); ?>" 
                            data-tab="tab-<?php echo esc_attr( $tab_id ); ?>"
                            role="tab"
                            aria-selected="<?php echo ( $index === (int) $args['active_tab'] ) ? 'true' : 'false'; ?>">
                        <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <span class="pw-tab-title"><?php echo esc_html( $tab['title'] ); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <div class="pw-tabbed-sections">
                <?php foreach ( $args['tabs'] as $index => $tab ) : ?>
                    <?php
                    $tab_id = sanitize_title( $tab['title'] );
                    $hidden_class = ( $index !== (int) $args['active_tab'] ) ? ' pw-tab-hidden' : '';
                    ?>
                    <div class="pw-tabbed-section<?php echo esc_attr( $hidden_class ); ?>" 
                         id="tab-<?php echo esc_attr( $tab_id ); ?>"
                         role="tabpanel"
                         aria-labelledby="tab-<?php echo esc_attr( $tab_id ); ?>-button">
                        
                        <?php if ( ! empty( $tab['fields'] ) ) : ?>
                            <div class="pw-tab-fields">
                                <?php $this->render_tab_fields( $tab['fields'] ); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $tab['description'] ) ) : ?>
                            <div class="pw-tab-description"><?php echo wp_kses_post( $tab['description'] ); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
        </div>
        <?php

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render fields inside a tab.
     *
     * @since 1.0.0
     * @param array $fields Array of field configurations.
     */
    private function render_tab_fields( $fields ) {
        foreach ( $fields as $field ) {
            // Skip disallowed field types.
            if ( in_array( $field['type'], $this->disallowed_types, true ) ) {
                $field['_notice'] = true;
            }

            $field_id = isset( $field['id'] ) ? $field['id'] : '';
            $field_default = isset( $field['default'] ) ? $field['default'] : '';
            $field_value = isset( $this->value[ $field_id ] ) ? $this->value[ $field_id ] : $field_default;
            $unique_id = ! empty( $this->unique ) ? $this->unique : '';

            // Render the field using the framework.
            $this->render_nested_field( $field, $field_value, $unique_id );
        }
    }

    /**
     * Enqueue field-specific styles and scripts.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        wp_add_inline_style( 'pearl-weather-framework', '
            .pw-tabbed-wrapper {
                margin: 15px 0;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                overflow: hidden;
            }
            .pw-tabbed-nav {
                display: flex;
                background: #f8f9fa;
                border-bottom: 1px solid #ddd;
            }
            .pw-tabbed-vertical .pw-tabbed-nav {
                flex-direction: column;
                border-bottom: none;
                border-right: 1px solid #ddd;
                width: 200px;
                float: left;
            }
            .pw-tabbed-vertical .pw-tabbed-sections {
                margin-left: 200px;
            }
            .pw-tab-button {
                flex: 1;
                padding: 12px 16px;
                background: transparent;
                border: none;
                cursor: pointer;
                font-size: 13px;
                font-weight: 500;
                color: #666;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                gap: 8px;
                justify-content: center;
            }
            .pw-tabbed-vertical .pw-tab-button {
                justify-content: flex-start;
                border-bottom: 1px solid #eee;
            }
            .pw-tab-button:hover {
                background: #e9ecef;
            }
            .pw-tab-button.pw-tab-active {
                background: #fff;
                color: #f26c0d;
                border-bottom: 2px solid #f26c0d;
            }
            .pw-tabbed-vertical .pw-tab-button.pw-tab-active {
                border-bottom: none;
                border-right: 2px solid #f26c0d;
                background: #fff;
            }
            .pw-tab-icon {
                font-size: 16px;
            }
            .pw-tabbed-sections {
                padding: 20px;
            }
            .pw-tabbed-section {
                display: block;
            }
            .pw-tabbed-section.pw-tab-hidden {
                display: none;
            }
            .pw-tab-description {
                margin-top: 15px;
                padding-top: 10px;
                border-top: 1px solid #eee;
                font-size: 12px;
                color: #666;
                font-style: italic;
            }
            @media (max-width: 768px) {
                .pw-tabbed-nav {
                    flex-direction: column;
                }
                .pw-tabbed-vertical .pw-tabbed-nav {
                    width: 100%;
                    float: none;
                    border-right: none;
                }
                .pw-tabbed-vertical .pw-tabbed-sections {
                    margin-left: 0;
                }
            }
        ' );

        wp_add_inline_script( 'pearl-weather-framework', '
            (function($) {
                $(document).ready(function() {
                    $(".pw-tab-button").on("click", function(e) {
                        e.preventDefault();
                        var $wrapper = $(this).closest(".pw-tabbed-wrapper");
                        var tabId = $(this).data("tab");
                        
                        $wrapper.find(".pw-tab-button").removeClass("pw-tab-active");
                        $(this).addClass("pw-tab-active");
                        
                        $wrapper.find(".pw-tabbed-section").addClass("pw-tab-hidden");
                        $("#" + tabId).removeClass("pw-tab-hidden");
                    });
                });
            })(jQuery);
        ' );
    }
}