<?php
/**
 * Framework Sortable Field
 *
 * Renders a sortable list of draggable items with nested fields.
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
 * Class SortableField
 *
 * Handles sortable field rendering in the framework.
 *
 * @since 1.0.0
 */
class SortableField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'fields'       => array(),
        'sortable'     => true,
        'drag_handle'  => true,
        'drag_icon'    => '⋮⋮',
    );

    /**
     * Render the sortable field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        if ( empty( $args['fields'] ) ) {
            return;
        }

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        echo '<div class="pw-sortable-list" data-sortable="' . esc_attr( $args['sortable'] ? 'true' : 'false' ) . '">';

        $sorted_fields = $this->get_sorted_fields( $args['fields'] );

        foreach ( $sorted_fields as $key => $field_config ) {
            $this->render_sortable_item( $key, $field_config, $args );
        }

        echo '</div>';

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Get sorted fields based on saved order.
     *
     * @since 1.0.0
     * @param array $fields Original fields.
     * @return array
     */
    private function get_sorted_fields( $fields ) {
        $pre_sortby = array();
        $pre_fields = array();

        // Index fields by ID.
        foreach ( $fields as $field ) {
            if ( isset( $field['id'] ) ) {
                $pre_fields[ $field['id'] ] = $field;
            }
        }

        // Sort by saved value.
        if ( ! empty( $this->value ) && is_array( $this->value ) ) {
            foreach ( $this->value as $key => $value ) {
                if ( isset( $pre_fields[ $key ] ) ) {
                    $pre_sortby[ $key ] = $pre_fields[ $key ];
                }
            }

            // Add any new fields not yet saved.
            $diff = array_diff_key( $pre_fields, $this->value );
            if ( ! empty( $diff ) ) {
                $pre_sortby = array_merge( $pre_sortby, $diff );
            }
        } else {
            // Use default order.
            $pre_sortby = $pre_fields;
        }

        return $pre_sortby;
    }

    /**
     * Render a single sortable item.
     *
     * @since 1.0.0
     * @param string $key          Item key.
     * @param array  $field_config Field configuration.
     * @param array  $args         Field arguments.
     */
    private function render_sortable_item( $key, $field_config, $args ) {
        $field_default = isset( $this->field['default'][ $key ] ) ? $this->field['default'][ $key ] : '';
        $field_value = isset( $this->value[ $key ] ) ? $this->value[ $key ] : $field_default;
        
        $drag_handle = $args['drag_handle'] ? '<div class="pw-sortable-handle">' . esc_html( $args['drag_icon'] ) . '</div>' : '';

        echo '<div class="pw-sortable-item" data-item-id="' . esc_attr( $key ) . '">';
        echo $drag_handle;
        echo '<div class="pw-sortable-content">';

        // Render nested field.
        $unique_id = ! empty( $this->unique ) 
            ? $this->unique . '[' . $this->field['id'] . ']' 
            : $this->field['id'];

        // Include field class and render.
        $field_class = $this->get_field_class( $field_config['type'] );
        if ( $field_class && class_exists( $field_class ) ) {
            $field_instance = new $field_class( $field_config, $field_value, $unique_id, 'field/sortable' );
            $field_instance->render();
        }

        echo '</div>';
        echo '</div>';
    }

    /**
     * Get field class name by type.
     *
     * @since 1.0.0
     * @param string $type Field type.
     * @return string|null
     */
    private function get_field_class( $type ) {
        $class_map = array(
            'checkbox' => 'PearlWeather\Framework\Fields\CheckboxField',
            'radio'    => 'PearlWeather\Framework\Fields\RadioField',
            'select'   => 'PearlWeather\Framework\Fields\SelectField',
            'text'     => 'PearlWeather\Framework\Fields\TextField',
            'textarea' => 'PearlWeather\Framework\Fields\TextareaField',
            'toggle'   => 'PearlWeather\Framework\Fields\ToggleField',
            'color'    => 'PearlWeather\Framework\Fields\ColorField',
        );

        return isset( $class_map[ $type ] ) ? $class_map[ $type ] : null;
    }

    /**
     * Enqueue sortable assets.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        if ( ! wp_script_is( 'jquery-ui-sortable' ) ) {
            wp_enqueue_script( 'jquery-ui-sortable' );
        }

        wp_add_inline_style( 'pearl-weather-framework', '
            .pw-sortable-list {
                margin: 10px 0;
                border: 1px solid #ddd;
                border-radius: 4px;
                background: #f8f9fa;
            }
            .pw-sortable-item {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                padding: 12px;
                background: #fff;
                border-bottom: 1px solid #eee;
                cursor: move;
            }
            .pw-sortable-item:last-child {
                border-bottom: none;
            }
            .pw-sortable-handle {
                cursor: grab;
                color: #999;
                font-size: 18px;
                line-height: 1;
                padding: 5px;
                user-select: none;
            }
            .pw-sortable-handle:active {
                cursor: grabbing;
            }
            .pw-sortable-content {
                flex: 1;
            }
            .pw-sortable-item.ui-sortable-helper {
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                background: #fff;
            }
            .pw-sortable-item.ui-sortable-placeholder {
                visibility: visible !important;
                background: #f0f0f0;
                border: 1px dashed #ddd;
                min-height: 60px;
            }
        ' );
    }

    /**
     * Sanitize sortable field value.
     *
     * @since 1.0.0
     * @param array $value The value to sanitize.
     * @return array
     */
    public static function sanitize( $value ) {
        if ( ! is_array( $value ) ) {
            return array();
        }

        $sanitized = array();

        foreach ( $value as $key => $item_value ) {
            $key = sanitize_key( $key );
            
            if ( is_array( $item_value ) ) {
                $sanitized[ $key ] = array_map( 'sanitize_text_field', $item_value );
            } else {
                $sanitized[ $key ] = sanitize_text_field( $item_value );
            }
        }

        return $sanitized;
    }
}