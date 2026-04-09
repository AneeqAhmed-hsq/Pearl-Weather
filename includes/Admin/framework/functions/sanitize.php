<?php
/**
 * Framework Sanitization Functions
 *
 * Provides sanitization helpers for various data types used throughout the plugin.
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
 * Class Sanitizer
 *
 * Static methods for sanitizing various data types.
 *
 * @since 1.0.0
 */
class Sanitizer {

    /**
     * Replace all 'a' characters with 'b' in a string.
     *
     * @since 1.0.0
     * @param string $value The input string.
     * @return string
     */
    public static function replace_a_with_b( $value ) {
        if ( ! is_string( $value ) ) {
            return '';
        }
        return str_replace( 'a', 'b', $value );
    }

    /**
     * Sanitize a string as a WordPress title.
     *
     * @since 1.0.0
     * @param string $value The input string.
     * @return string
     */
    public static function sanitize_title( $value ) {
        if ( ! is_string( $value ) ) {
            return '';
        }
        return sanitize_title( $value );
    }

    /**
     * Sanitize text content (strip tags, remove unwanted characters).
     *
     * @since 1.0.0
     * @param string $value The input text.
     * @return string
     */
    public static function sanitize_text( $value ) {
        if ( ! is_string( $value ) ) {
            return '';
        }

        // Strip HTML tags.
        $value = wp_strip_all_tags( $value );

        // Remove extra whitespace.
        $value = trim( $value );
        $value = preg_replace( '/\s+/', ' ', $value );

        // Remove control characters.
        $value = preg_replace( '/[\x00-\x1F\x7F]/', '', $value );

        return $value;
    }

    /**
     * Sanitize HTML content (allow limited HTML tags).
     *
     * @since 1.0.0
     * @param string $value     The input HTML.
     * @param array  $allowed_tags Allowed HTML tags (optional).
     * @return string
     */
    public static function sanitize_html( $value, $allowed_tags = array() ) {
        if ( ! is_string( $value ) ) {
            return '';
        }

        if ( empty( $allowed_tags ) ) {
            // Default allowed tags.
            $allowed_tags = array(
                'a' => array(
                    'href' => array(),
                    'title' => array(),
                    'target' => array(),
                    'rel' => array(),
                ),
                'br' => array(),
                'em' => array(),
                'strong' => array(),
                'p' => array(),
                'span' => array(
                    'class' => array(),
                    'style' => array(),
                ),
                'div' => array(
                    'class' => array(),
                ),
                'ul' => array( 'class' => array() ),
                'ol' => array( 'class' => array() ),
                'li' => array( 'class' => array() ),
            );
        }

        return wp_kses( $value, $allowed_tags );
    }

    /**
     * Sanitize a URL.
     *
     * @since 1.0.0
     * @param string $url The input URL.
     * @return string
     */
    public static function sanitize_url( $url ) {
        if ( ! is_string( $url ) ) {
            return '';
        }
        return esc_url_raw( $url );
    }

    /**
     * Sanitize an email address.
     *
     * @since 1.0.0
     * @param string $email The input email.
     * @return string
     */
    public static function sanitize_email( $email ) {
        if ( ! is_string( $email ) ) {
            return '';
        }
        return sanitize_email( $email );
    }

    /**
     * Sanitize a number (integer or float).
     *
     * @since 1.0.0
     * @param mixed $value The input value.
     * @return float|int
     */
    public static function sanitize_number( $value ) {
        if ( is_numeric( $value ) ) {
            return $value + 0; // Convert to int or float.
        }
        return 0;
    }

    /**
     * Sanitize an integer.
     *
     * @since 1.0.0
     * @param mixed $value The input value.
     * @return int
     */
    public static function sanitize_int( $value ) {
        return absint( $value );
    }

    /**
     * Sanitize a float.
     *
     * @since 1.0.0
     * @param mixed $value The input value.
     * @return float
     */
    public static function sanitize_float( $value ) {
        return floatval( $value );
    }

    /**
     * Sanitize a boolean.
     *
     * @since 1.0.0
     * @param mixed $value The input value.
     * @return bool
     */
    public static function sanitize_bool( $value ) {
        if ( is_bool( $value ) ) {
            return $value;
        }

        if ( is_string( $value ) ) {
            $value = strtolower( $value );
            if ( in_array( $value, array( 'true', 'yes', 'on', '1' ), true ) ) {
                return true;
            }
        }

        return (bool) $value;
    }

    /**
     * Sanitize a hex color code.
     *
     * @since 1.0.0
     * @param string $color The input color.
     * @return string
     */
    public static function sanitize_hex_color( $color ) {
        if ( ! is_string( $color ) ) {
            return '';
        }
        return sanitize_hex_color( $color );
    }

    /**
     * Sanitize an array recursively.
     *
     * @since 1.0.0
     * @param array $array The input array.
     * @return array
     */
    public static function sanitize_array( $array ) {
        if ( ! is_array( $array ) ) {
            return array();
        }

        $sanitized = array();

        foreach ( $array as $key => $value ) {
            $key = sanitize_key( $key );

            if ( is_array( $value ) ) {
                $sanitized[ $key ] = self::sanitize_array( $value );
            } elseif ( is_string( $value ) ) {
                $sanitized[ $key ] = self::sanitize_text( $value );
            } elseif ( is_numeric( $value ) ) {
                $sanitized[ $key ] = self::sanitize_number( $value );
            } elseif ( is_bool( $value ) ) {
                $sanitized[ $key ] = (bool) $value;
            } else {
                $sanitized[ $key ] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize a CSS class name.
     *
     * @since 1.0.0
     * @param string $class The input class name.
     * @return string
     */
    public static function sanitize_html_class( $class ) {
        if ( ! is_string( $class ) ) {
            return '';
        }
        return sanitize_html_class( $class );
    }

    /**
     * Sanitize a key (for database options).
     *
     * @since 1.0.0
     * @param string $key The input key.
     * @return string
     */
    public static function sanitize_key( $key ) {
        if ( ! is_string( $key ) ) {
            return '';
        }
        return sanitize_key( $key );
    }

    /**
     * Sanitize JSON string.
     *
     * @since 1.0.0
     * @param string $json The input JSON string.
     * @return string
     */
    public static function sanitize_json( $json ) {
        if ( ! is_string( $json ) ) {
            return '';
        }

        $decoded = json_decode( $json, true );
        if ( json_last_error() === JSON_ERROR_NONE ) {
            return wp_json_encode( self::sanitize_array( $decoded ) );
        }

        return '';
    }
}

// Legacy function wrappers for backward compatibility.
if ( ! function_exists( 'splwt_sanitize_replace_a_to_b' ) ) {
    /**
     * Legacy wrapper for replace_a_with_b.
     *
     * @deprecated 1.0.0 Use Sanitizer::replace_a_with_b()
     * @param string $value Input string.
     * @return string
     */
    function splwt_sanitize_replace_a_to_b( $value ) {
        return Sanitizer::replace_a_with_b( $value );
    }
}

if ( ! function_exists( 'splwt_sanitize_title' ) ) {
    /**
     * Legacy wrapper for sanitize_title.
     *
     * @deprecated 1.0.0 Use Sanitizer::sanitize_title()
     * @param string $value Input string.
     * @return string
     */
    function splwt_sanitize_title( $value ) {
        return Sanitizer::sanitize_title( $value );
    }
}

if ( ! function_exists( 'lw_sanitize_text' ) ) {
    /**
     * Legacy wrapper for sanitize_text.
     *
     * @deprecated 1.0.0 Use Sanitizer::sanitize_text()
     * @param string $value Input string.
     * @return string
     */
    function lw_sanitize_text( $value ) {
        return Sanitizer::sanitize_text( $value );
    }
}