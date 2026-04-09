<?php
/**
 * Framework Select Field
 *
 * Renders a select dropdown with support for multiple selection,
 * AJAX loading, Chosen.js enhancement, and icon previews.
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
 * Class SelectField
 *
 * Handles select field rendering in the framework.
 *
 * @since 1.0.0
 */
class SelectField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'placeholder' => '',
        'chosen'      => false,
        'multiple'    => false,
        'sortable'    => false,
        'ajax'        => false,
        'settings'    => array(),
        'query_args'  => array(),
        'preview'     => false,
        'preview_type' => 'icon', // icon, image
        'preview_path' => '',
    );

    /**
     * Render the select field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $this->value = is_array( $this->value ) ? $this->value : array_filter( (array) $this->value );

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if ( isset( $this->field['options'] ) ) {
            $this->render_select( $args );
        }

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render select element.
     *
     * @since 1.0.0
     * @param array $args Field arguments.
     */
    private function render_select( $args ) {
        $options = $this->get_options( $args );

        if ( ( is_array( $options ) && ! empty( $options ) ) || ( $args['chosen'] && $args['ajax'] ) ) {

            $selected_value = $this->get_selected_value( $options );
            
            $this->render_select_element( $args, $options, $selected_value );
            
            if ( $args['preview'] ) {
                $this->render_preview( $selected_value, $args );
            }
        } else {
            $this->render_empty_message();
        }
    }

    /**
     * Get options for select.
     *
     * @since 1.0.0
     * @param array $args Field arguments.
     * @return array
     */
    private function get_options( $args ) {
        $maybe_options = $this->field['options'];

        if ( is_string( $maybe_options ) && ! empty( $args['chosen'] ) && ! empty( $args['ajax'] ) ) {
            return $this->field_wp_query_data_title( $maybe_options, $this->value );
        } elseif ( is_string( $maybe_options ) ) {
            return $this->field_data( $maybe_options, false, $args['query_args'] );
        }

        return $maybe_options;
    }

    /**
     * Get selected value for preview.
     *
     * @since 1.0.0
     * @param array $options Available options.
     * @return string
     */
    private function get_selected_value( $options ) {
        foreach ( $options as $key => $option ) {
            if ( is_array( $option ) ) {
                foreach ( $option as $sub_key => $sub_value ) {
                    if ( in_array( $sub_key, $this->value, true ) ) {
                        return $sub_key;
                    }
                }
            } elseif ( in_array( $key, $this->value, true ) ) {
                return $key;
            }
        }
        return '';
    }

    /**
     * Render select element.
     *
     * @since 1.0.0
     * @param array  $args     Field arguments.
     * @param array  $options  Available options.
     * @param string $selected_value Selected value.
     */
    private function render_select_element( $args, $options, $selected_value ) {
        $chosen_rtl = is_rtl() ? ' pw-chosen-rtl' : '';
        $multiple_name = $args['multiple'] ? '[]' : '';
        $multiple_attr = $args['multiple'] ? ' multiple="multiple"' : '';
        $chosen_sortable = $args['chosen'] && $args['sortable'] ? ' pw-chosen-sortable' : '';
        $chosen_ajax = $args['chosen'] && $args['ajax'] ? ' pw-chosen-ajax' : '';
        $placeholder_attr = $args['chosen'] && $args['placeholder'] ? ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '"' : '';
        
        $field_class = $args['chosen'] ? ' class="pw-chosen-select' . esc_attr( $chosen_rtl . $chosen_sortable . $chosen_ajax ) . '"' : '';
        $field_name = $this->field_name( $multiple_name );
        $chosen_data_attr = $args['chosen'] && ! empty( $args['settings'] ) ? ' data-chosen-settings="' . esc_attr( wp_json_encode( $args['settings'] ) ) . '"' : '';

        // Hidden select for chosen multiple.
        if ( $args['chosen'] && $args['multiple'] ) {
            echo '<select name="' . esc_attr( $field_name ) . '" class="pw-hide-select hidden"' . $multiple_attr . '>';
            foreach ( $this->value as $option_key ) {
                echo '<option value="' . esc_attr( $option_key ) . '" selected>' . esc_html( $option_key ) . '</option>';
            }
            echo '</select>';
            $field_name = '_pseudo';
        }

        echo '<select name="' . esc_attr( $field_name ) . '"' . $field_class . $multiple_attr . $placeholder_attr . $chosen_data_attr . '>';

        // Placeholder option.
        if ( $args['placeholder'] && empty( $args['multiple'] ) ) {
            echo '<option value="">' . esc_html( $args['placeholder'] ) . '</option>';
        }

        // Options.
        foreach ( $options as $option_key => $option ) {
            if ( is_array( $option ) && ! empty( $option ) ) {
                echo '<optgroup label="' . esc_attr( $option_key ) . '">';
                foreach ( $option as $sub_key => $sub_value ) {
                    $selected = in_array( $sub_key, $this->value, true ) ? ' selected' : '';
                    echo '<option value="' . esc_attr( $sub_key ) . '"' . esc_attr( $selected ) . '>' . esc_html( $sub_value ) . '</option>';
                }
                echo '</optgroup>';
            } else {
                $selected = in_array( $option_key, $this->value, true ) ? ' selected' : '';
                echo '<option value="' . esc_attr( $option_key ) . '"' . esc_attr( $selected ) . '>' . esc_html( $option ) . '</option>';
            }
        }

        echo '</select>';
    }

    /**
     * Render preview for selected option.
     *
     * @since 1.0.0
     * @param string $selected_value Selected value.
     * @param array  $args           Field arguments.
     */
    private function render_preview( $selected_value, $args ) {
        $preview_url = '';
        
        if ( ! empty( $args['preview_path'] ) ) {
            $preview_url = trailingslashit( $args['preview_path'] ) . $selected_value . '.svg';
        } elseif ( 'icon' === $args['preview_type'] ) {
            $preview_url = PEARL_WEATHER_ASSETS_URL . 'images/icons/' . $selected_value . '.svg';
        }

        if ( ! empty( $preview_url ) ) {
            echo '<div class="pw-select-preview">';
            echo '<img src="' . esc_url( $preview_url ) . '" class="pw-preview-image" alt="Preview">';
            echo '</div>';
        }
    }

    /**
     * Render empty message.
     *
     * @since 1.0.0
     */
    private function render_empty_message() {
        $message = ! empty( $this->field['empty_message'] ) 
            ? $this->field['empty_message'] 
            : __( 'No data available.', 'pearl-weather' );

        echo '<div class="pw-field-empty-message">' . esc_html( $message ) . '</div>';
    }

    /**
     * Enqueue field-specific assets.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        if ( ! wp_script_is( 'jquery-ui-sortable' ) ) {
            wp_enqueue_script( 'jquery-ui-sortable' );
        }

        if ( ! empty( $this->field['chosen'] ) ) {
            wp_enqueue_script( 'chosen' );
            wp_enqueue_style( 'chosen' );
        }

        wp_add_inline_style( 'pearl-weather-framework', '
            .pw-chosen-select {
                width: 100%;
                max-width: 400px;
            }
            .pw-chosen-select.chosen-container {
                width: 100% !important;
            }
            .pw-select-preview {
                margin-top: 10px;
                padding: 10px;
                background: #f8f9fa;
                border-radius: 4px;
                display: inline-block;
            }
            .pw-preview-image {
                max-width: 60px;
                height: auto;
            }
            .pw-hide-select {
                display: none;
            }
        ' );
    }
}