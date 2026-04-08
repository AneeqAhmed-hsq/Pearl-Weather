<?php
/**
 * Import/Export Handler for Weather Widgets
 *
 * Handles exporting and importing weather widget configurations
 * in JSON format for backup or migration purposes.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Admin
 * @since      1.0.0
 */

namespace PearlWeather\Admin;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ImportExportManager
 *
 * Manages import/export of weather widget configurations.
 *
 * @since 1.0.0
 */
class ImportExportManager {

    /**
     * Post type for weather widgets.
     */
    const POST_TYPE = 'pearl_weather_widget';

    /**
     * AJAX action for export.
     */
    const EXPORT_ACTION = 'pearl_weather_export_widgets';

    /**
     * AJAX action for import.
     */
    const IMPORT_ACTION = 'pearl_weather_import_widgets';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'wp_ajax_' . self::EXPORT_ACTION, array( $this, 'export_widgets' ) );
        add_action( 'wp_ajax_' . self::IMPORT_ACTION, array( $this, 'import_widgets' ) );
    }

    /**
     * Export widgets to JSON.
     *
     * @since 1.0.0
     * @param array|string $widget_ids Widget IDs to export (or 'all').
     * @return array|false
     */
    public function export( $widget_ids = 'all' ) {
        $export = array();

        // Get widgets.
        $args = array(
            'post_type'        => self::POST_TYPE,
            'post_status'      => array( 'publish', 'draft', 'private' ),
            'orderby'          => 'modified',
            'posts_per_page'   => -1,
            'suppress_filters' => true,
        );

        if ( 'all' !== $widget_ids && ! empty( $widget_ids ) ) {
            $args['post__in'] = array_map( 'absint', (array) $widget_ids );
        }

        $widgets = get_posts( $args );

        if ( empty( $widgets ) ) {
            return false;
        }

        foreach ( $widgets as $widget ) {
            $widget_export = array(
                'title'       => sanitize_text_field( $widget->post_title ),
                'original_id' => absint( $widget->ID ),
                'meta'        => array(),
            );

            // Get all post meta.
            $meta_data = get_post_meta( $widget->ID );

            foreach ( $meta_data as $meta_key => $meta_values ) {
                // Skip internal meta.
                if ( strpos( $meta_key, '_wp_' ) === 0 ) {
                    continue;
                }

                $meta_value = maybe_unserialize( $meta_values[0] );
                $widget_export['meta'][ $meta_key ] = $meta_value;
            }

            $export['widgets'][] = $widget_export;
        }

        // Add metadata.
        $export['metadata'] = array(
            'version' => PEARL_WEATHER_VERSION,
            'date'    => current_time( 'Y-m-d H:i:s' ),
            'site_url' => home_url(),
            'generator' => 'Pearl Weather Export Tool',
        );

        return $export;
    }

    /**
     * Import widgets from JSON data.
     *
     * @since 1.0.0
     * @param array $widgets Array of widget data to import.
     * @return array|\WP_Error
     */
    public function import( $widgets ) {
        $imported = array();
        $errors = array();

        foreach ( $widgets as $index => $widget_data ) {
            $widget_id = 0;

            try {
                // Validate required data.
                if ( empty( $widget_data['title'] ) ) {
                    throw new \Exception( __( 'Widget title is required.', 'pearl-weather' ) );
                }

                // Create new widget.
                $widget_id = wp_insert_post( array(
                    'post_title'  => sanitize_text_field( $widget_data['title'] ),
                    'post_status' => 'publish',
                    'post_type'   => self::POST_TYPE,
                ), true );

                if ( is_wp_error( $widget_id ) ) {
                    throw new \Exception( $widget_id->get_error_message() );
                }

                // Import meta data.
                if ( isset( $widget_data['meta'] ) && is_array( $widget_data['meta'] ) ) {
                    foreach ( $widget_data['meta'] as $meta_key => $meta_value ) {
                        // Replace placeholder IDs if needed.
                        $meta_value = str_replace( '{#ID#}', $widget_id, $meta_value );
                        update_post_meta( $widget_id, sanitize_key( $meta_key ), maybe_unserialize( $meta_value ) );
                    }
                }

                $imported[] = array(
                    'new_id' => $widget_id,
                    'old_id' => isset( $widget_data['original_id'] ) ? $widget_data['original_id'] : 0,
                    'title'  => $widget_data['title'],
                );

                /**
                 * Action after a widget is imported.
                 *
                 * @param int   $widget_id   New widget ID.
                 * @param array $widget_data Original widget data.
                 */
                do_action( 'pearl_weather_widget_imported', $widget_id, $widget_data );

            } catch ( \Exception $e ) {
                $errors[] = array(
                    'index' => $index,
                    'title' => isset( $widget_data['title'] ) ? $widget_data['title'] : __( 'Unknown', 'pearl-weather' ),
                    'error' => $e->getMessage(),
                );

                // Clean up if widget was created.
                if ( $widget_id > 0 ) {
                    wp_delete_post( $widget_id, true );
                }
            }
        }

        if ( ! empty( $errors ) ) {
            return new \WP_Error( 'import_errors', __( 'Some widgets could not be imported.', 'pearl-weather' ), $errors );
        }

        return $imported;
    }

    /**
     * AJAX handler for export.
     *
     * @since 1.0.0
     */
    public function export_widgets() {
        // Verify nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'pw_import_export_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce verification.', 'pearl-weather' ) ), 401 );
        }

        // Check user capabilities.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to export.', 'pearl-weather' ) ), 403 );
        }

        // Get widget IDs to export.
        $widget_ids = isset( $_POST['widget_ids'] ) ? $_POST['widget_ids'] : 'all';

        if ( is_array( $widget_ids ) ) {
            $widget_ids = array_map( 'absint', $widget_ids );
        }

        $export_data = $this->export( $widget_ids );

        if ( ! $export_data ) {
            wp_send_json_error( array( 'message' => __( 'No widgets found to export.', 'pearl-weather' ) ), 404 );
        }

        // Send JSON response.
        if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
            wp_send_json( $export_data, 200, JSON_PRETTY_PRINT );
        } else {
            wp_send_json( $export_data, 200 );
        }
    }

    /**
     * AJAX handler for import.
     *
     * @since 1.0.0
     */
    public function import_widgets() {
        // Verify nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'pw_import_export_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce verification.', 'pearl-weather' ) ), 401 );
        }

        // Check user capabilities.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to import.', 'pearl-weather' ) ), 403 );
        }

        // Get and decode JSON data.
        $json_data = isset( $_POST['widget_data'] ) ? wp_unslash( $_POST['widget_data'] ) : '';

        if ( empty( $json_data ) ) {
            wp_send_json_error( array( 'message' => __( 'No data provided for import.', 'pearl-weather' ) ), 400 );
        }

        $decoded_data = json_decode( $json_data, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            wp_send_json_error( array( 'message' => __( 'Invalid JSON data.', 'pearl-weather' ) ), 400 );
        }

        // Validate data structure.
        if ( ! isset( $decoded_data['widgets'] ) || ! is_array( $decoded_data['widgets'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid import data structure.', 'pearl-weather' ) ), 400 );
        }

        // Import widgets.
        $result = $this->import( $decoded_data['widgets'] );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array(
                'message' => $result->get_error_message(),
                'errors'  => $result->get_error_data(),
            ), 400 );
        }

        wp_send_json_success( array(
            'message'  => sprintf( __( 'Successfully imported %d widget(s).', 'pearl-weather' ), count( $result ) ),
            'imported' => $result,
        ), 200 );
    }

    /**
     * Generate export file and trigger download.
     *
     * @since 1.0.0
     * @param array|string $widget_ids Widget IDs to export.
     */
    public function download_export( $widget_ids = 'all' ) {
        $export_data = $this->export( $widget_ids );

        if ( ! $export_data ) {
            wp_die( __( 'No widgets found to export.', 'pearl-weather' ) );
        }

        $json = wp_json_encode( $export_data, JSON_PRETTY_PRINT );
        $filename = 'pearl-weather-export-' . current_time( 'Y-m-d' ) . '.json';

        header( 'Content-Type: application/json' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Content-Length: ' . strlen( $json ) );
        header( 'Cache-Control: no-cache, must-revalidate' );

        echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }

    /**
     * Validate import file before processing.
     *
     * @since 1.0.0
     * @param string $file_path Path to JSON file.
     * @return array|false
     */
    public function validate_import_file( $file_path ) {
        if ( ! file_exists( $file_path ) ) {
            return false;
        }

        $content = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $data = json_decode( $content, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return false;
        }

        if ( ! isset( $data['widgets'] ) || ! is_array( $data['widgets'] ) ) {
            return false;
        }

        return $data;
    }
}

// Initialize the import/export manager.
new ImportExportManager();