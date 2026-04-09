<?php
/**
 * Framework Helper Functions
 *
 * Provides utility functions for array searching, timeout checking,
 * and WordPress version compatibility.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Framework
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class FrameworkHelpers
 *
 * Static helper methods for the plugin framework.
 *
 * @since 1.0.0
 */
class FrameworkHelpers {

    /**
     * Recursively search an array for a key-value pair.
     *
     * @since 1.0.0
     * @param array  $array The array to search.
     * @param string $key   The key to look for.
     * @param mixed  $value The value to match.
     * @return array All matching sub-arrays.
     */
    public static function array_search_recursive( $array, $key, $value ) {
        $results = array();

        if ( ! is_array( $array ) ) {
            return $results;
        }

        // Check current level.
        if ( isset( $array[ $key ] ) && $array[ $key ] === $value ) {
            $results[] = $array;
        }

        // Recursively search child arrays.
        foreach ( $array as $sub_array ) {
            if ( is_array( $sub_array ) ) {
                $results = array_merge( $results, self::array_search_recursive( $sub_array, $key, $value ) );
            }
        }

        return $results;
    }

    /**
     * Check if a timeout has not been exceeded.
     *
     * @since 1.0.0
     * @param int $current_time Current timestamp.
     * @param int $start_time   Start timestamp.
     * @param int $timeout      Timeout in seconds (default 30).
     * @return bool True if within timeout, false otherwise.
     */
    public static function is_within_timeout( $current_time, $start_time, $timeout = 30 ) {
        return ( $current_time - $start_time ) < $timeout;
    }

    /**
     * Check if the current WordPress version supports the block editor API.
     *
     * @since 1.0.0
     * @return bool True if version 4.8 or higher, false otherwise.
     */
    public static function supports_wp_editor_api() {
        global $wp_version;
        return version_compare( $wp_version, '4.8', '>=' );
    }

    /**
     * Get the minimum required WordPress version for a feature.
     *
     * @since 1.0.0
     * @param string $feature Feature name ('gutenberg', 'rest_api', 'widgets').
     * @return string Minimum version.
     */
    public static function get_min_wp_version( $feature = 'gutenberg' ) {
        $versions = array(
            'gutenberg'    => '5.0',
            'rest_api'     => '4.7',
            'widgets'      => '2.8',
            'block_editor' => '5.0',
        );

        return isset( $versions[ $feature ] ) ? $versions[ $feature ] : '5.0';
    }

    /**
     * Check if a feature is available in the current WordPress version.
     *
     * @since 1.0.0
     * @param string $feature Feature name.
     * @return bool
     */
    public static function is_feature_available( $feature ) {
        global $wp_version;
        $min_version = self::get_min_wp_version( $feature );
        return version_compare( $wp_version, $min_version, '>=' );
    }

    /**
     * Flatten a multidimensional array.
     *
     * @since 1.0.0
     * @param array $array The array to flatten.
     * @return array
     */
    public static function array_flatten( $array ) {
        $result = array();

        foreach ( $array as $value ) {
            if ( is_array( $value ) ) {
                $result = array_merge( $result, self::array_flatten( $value ) );
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Get a value from an array using dot notation.
     *
     * @since 1.0.0
     * @param array  $array   The array to search.
     * @param string $key     Dot notation key (e.g., 'level1.level2.key').
     * @param mixed  $default Default value if key not found.
     * @return mixed
     */
    public static function array_get( $array, $key, $default = null ) {
        if ( ! is_array( $array ) ) {
            return $default;
        }

        $keys = explode( '.', $key );
        $value = $array;

        foreach ( $keys as $segment ) {
            if ( isset( $value[ $segment ] ) ) {
                $value = $value[ $segment ];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Set a value in an array using dot notation.
     *
     * @since 1.0.0
     * @param array  $array The array to modify.
     * @param string $key   Dot notation key.
     * @param mixed  $value The value to set.
     * @return array
     */
    public static function array_set( $array, $key, $value ) {
        $keys = explode( '.', $key );
        $current = &$array;

        foreach ( $keys as $segment ) {
            if ( ! isset( $current[ $segment ] ) || ! is_array( $current[ $segment ] ) ) {
                $current[ $segment ] = array();
            }
            $current = &$current[ $segment ];
        }

        $current = $value;

        return $array;
    }

    /**
     * Remove a value from an array using dot notation.
     *
     * @since 1.0.0
     * @param array  $array The array to modify.
     * @param string $key   Dot notation key.
     * @return array
     */
    public static function array_forget( $array, $key ) {
        $keys = explode( '.', $key );
        $current = &$array;

        foreach ( $keys as $i => $segment ) {
            if ( $i === count( $keys ) - 1 ) {
                unset( $current[ $segment ] );
            } elseif ( isset( $current[ $segment ] ) && is_array( $current[ $segment ] ) ) {
                $current = &$current[ $segment ];
            } else {
                break;
            }
        }

        return $array;
    }

    /**
     * Convert a value to boolean (strict).
     *
     * @since 1.0.0
     * @param mixed $value The value to convert.
     * @return bool
     */
    public static function to_bool( $value ) {
        if ( is_bool( $value ) ) {
            return $value;
        }

        if ( is_string( $value ) ) {
            $value = strtolower( $value );
            if ( in_array( $value, array( 'true', 'yes', 'on', '1' ), true ) ) {
                return true;
            }
            if ( in_array( $value, array( 'false', 'no', 'off', '0' ), true ) ) {
                return false;
            }
        }

        return (bool) $value;
    }

    /**
     * Get the current URL.
     *
     * @since 1.0.0
     * @return string
     */
    public static function get_current_url() {
        $protocol = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

        return $protocol . '://' . $host . $uri;
    }
}

// Legacy function wrappers for backward compatibility.
if ( ! function_exists( 'splwt_array_search' ) ) {
    /**
     * Legacy wrapper for array_search_recursive.
     *
     * @deprecated 1.0.0 Use FrameworkHelpers::array_search_recursive()
     * @param array  $array The array to search.
     * @param string $key   The key to look for.
     * @param mixed  $value The value to match.
     * @return array
     */
    function splwt_array_search( $array, $key, $value ) {
        return FrameworkHelpers::array_search_recursive( $array, $key, $value );
    }
}

if ( ! function_exists( 'splwt_timeout' ) ) {
    /**
     * Legacy wrapper for is_within_timeout.
     *
     * @deprecated 1.0.0 Use FrameworkHelpers::is_within_timeout()
     * @param int $timenow   Current timestamp.
     * @param int $starttime Start timestamp.
     * @param int $timeout   Timeout in seconds.
     * @return bool
     */
    function splwt_timeout( $timenow, $starttime, $timeout = 30 ) {
        return FrameworkHelpers::is_within_timeout( $timenow, $starttime, $timeout );
    }
}

if ( ! function_exists( 'splwt_wp_editor_api' ) ) {
    /**
     * Legacy wrapper for supports_wp_editor_api.
     *
     * @deprecated 1.0.0 Use FrameworkHelpers::supports_wp_editor_api()
     * @return bool
     */
    function splwt_wp_editor_api() {
        return FrameworkHelpers::supports_wp_editor_api();
    }
}