<?php
/**
 * Framework Validation Functions
 *
 * Provides validation helpers for various data types used throughout the plugin.
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
 * Class Validator
 *
 * Static methods for validating various data types.
 *
 * @since 1.0.0
 */
class Validator {

    /**
     * Validate an email address.
     *
     * @since 1.0.0
     * @param string $value The email address to validate.
     * @return string|true Error message or true if valid.
     */
    public static function validate_email( $value ) {
        if ( ! is_string( $value ) ) {
            return __( 'Email must be a string.', 'pearl-weather' );
        }

        if ( ! is_email( $value ) ) {
            return __( 'Please enter a valid email address.', 'pearl-weather' );
        }

        return true;
    }

    /**
     * Validate a numeric value.
     *
     * @since 1.0.0
     * @param mixed $value The value to validate.
     * @return string|true Error message or true if valid.
     */
    public static function validate_numeric( $value ) {
        if ( ! is_numeric( $value ) ) {
            return __( 'Please enter a valid number.', 'pearl-weather' );
        }

        return true;
    }

    /**
     * Validate that a value is not empty.
     *
     * @since 1.0.0
     * @param mixed $value The value to validate.
     * @return string|true Error message or true if valid.
     */
    public static function validate_required( $value ) {
        if ( empty( $value ) && '0' !== $value ) {
            return __( 'This field is required.', 'pearl-weather' );
        }

        return true;
    }

    /**
     * Validate a URL.
     *
     * @since 1.0.0
     * @param string $value The URL to validate.
     * @return string|true Error message or true if valid.
     */
    public static function validate_url( $value ) {
        if ( ! is_string( $value ) ) {
            return __( 'URL must be a string.', 'pearl-weather' );
        }

        if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
            return __( 'Please enter a valid URL.', 'pearl-weather' );
        }

        return true;
    }

    /**
     * Validate a hex color code.
     *
     * @since 1.0.0
     * @param string $value The color code to validate.
     * @return string|true Error message or true if valid.
     */
    public static function validate_hex_color( $value ) {
        if ( ! is_string( $value ) ) {
            return __( 'Color must be a string.', 'pearl-weather' );
        }

        if ( ! preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value ) ) {
            return __( 'Please enter a valid hex color code (e.g., #FF0000).', 'pearl-weather' );
        }

        return true;
    }

    /**
     * Validate an integer (positive only).
     *
     * @since 1.0.0
     * @param mixed $value The value to validate.
     * @return string|true Error message or true if valid.
     */
    public static function validate_positive_int( $value ) {
        $valid = self::validate_numeric( $value );
        if ( true !== $valid ) {
            return $valid;
        }

        if ( $value < 0 ) {
            return __( 'Please enter a positive number.', 'pearl-weather' );
        }

        return true;
    }

    /**
     * Validate a value is within a range.
     *
     * @since 1.0.0
     * @param mixed $value The value to validate.
     * @param int   $min   Minimum allowed value.
     * @param int   $max   Maximum allowed value.
     * @return string|true Error message or true if valid.
     */
    public static function validate_range( $value, $min, $max ) {
        $valid = self::validate_numeric( $value );
        if ( true !== $valid ) {
            return $valid;
        }

        if ( $value < $min || $value > $max ) {
            return sprintf(
                /* translators: %1$d: minimum value, %2$d: maximum value */
                __( 'Please enter a number between %1$d and %2$d.', 'pearl-weather' ),
                $min,
                $max
            );
        }

        return true;
    }

    /**
     * Validate a value is one of the allowed options.
     *
     * @since 1.0.0
     * @param mixed $value   The value to validate.
     * @param array $allowed Array of allowed values.
     * @return string|true Error message or true if valid.
     */
    public static function validate_in_array( $value, $allowed ) {
        if ( ! in_array( $value, $allowed, true ) ) {
            return __( 'Please select a valid option.', 'pearl-weather' );
        }

        return true;
    }

    // Customizer-specific validation methods.

    /**
     * Email validation for WordPress Customizer.
     *
     * @since 1.0.0
     * @param object $validity     Customizer validity object.
     * @param string $value        The email value.
     * @param object $wp_customize WP_Customize instance.
     * @return object
     */
    public static function customizer_validate_email( $validity, $value, $wp_customize ) {
        $result = self::validate_email( $value );

        if ( true !== $result ) {
            $validity->add( 'invalid_email', $result );
        }

        return $validity;
    }

    /**
     * Numeric validation for WordPress Customizer.
     *
     * @since 1.0.0
     * @param object $validity     Customizer validity object.
     * @param string $value        The numeric value.
     * @param object $wp_customize WP_Customize instance.
     * @return object
     */
    public static function customizer_validate_numeric( $validity, $value, $wp_customize ) {
        $result = self::validate_numeric( $value );

        if ( true !== $result ) {
            $validity->add( 'invalid_number', $result );
        }

        return $validity;
    }

    /**
     * Required field validation for WordPress Customizer.
     *
     * @since 1.0.0
     * @param object $validity     Customizer validity object.
     * @param string $value        The field value.
     * @param object $wp_customize WP_Customize instance.
     * @return object
     */
    public static function customizer_validate_required( $validity, $value, $wp_customize ) {
        $result = self::validate_required( $value );

        if ( true !== $result ) {
            $validity->add( 'required', $result );
        }

        return $validity;
    }

    /**
     * URL validation for WordPress Customizer.
     *
     * @since 1.0.0
     * @param object $validity     Customizer validity object.
     * @param string $value        The URL value.
     * @param object $wp_customize WP_Customize instance.
     * @return object
     */
    public static function customizer_validate_url( $validity, $value, $wp_customize ) {
        $result = self::validate_url( $value );

        if ( true !== $result ) {
            $validity->add( 'invalid_url', $result );
        }

        return $validity;
    }

    /**
     * Validate all fields in an array.
     *
     * @since 1.0.0
     * @param array $data   The data to validate.
     * @param array $rules  Validation rules.
     * @return array Array of errors, empty if no errors.
     */
    public static function validate_array( $data, $rules ) {
        $errors = array();

        foreach ( $rules as $field => $validations ) {
            $value = isset( $data[ $field ] ) ? $data[ $field ] : null;

            foreach ( $validations as $validation ) {
                $method = 'validate_' . $validation['type'];
                $args = isset( $validation['args'] ) ? $validation['args'] : array();

                if ( method_exists( __CLASS__, $method ) ) {
                    array_unshift( $args, $value );
                    $result = call_user_func_array( array( __CLASS__, $method ), $args );

                    if ( true !== $result ) {
                        $errors[ $field ] = $result;
                        break;
                    }
                }
            }
        }

        return $errors;
    }
}

// Legacy function wrappers for backward compatibility.
if ( ! function_exists( 'splwt_validate_email' ) ) {
    /**
     * Legacy wrapper for validate_email.
     *
     * @deprecated 1.0.0 Use Validator::validate_email()
     * @param string $value Email address.
     * @return string|true
     */
    function splwt_validate_email( $value ) {
        return Validator::validate_email( $value );
    }
}

if ( ! function_exists( 'splwt_validate_numeric' ) ) {
    /**
     * Legacy wrapper for validate_numeric.
     *
     * @deprecated 1.0.0 Use Validator::validate_numeric()
     * @param mixed $value Numeric value.
     * @return string|true
     */
    function splwt_validate_numeric( $value ) {
        return Validator::validate_numeric( $value );
    }
}

if ( ! function_exists( 'splwt_validate_required' ) ) {
    /**
     * Legacy wrapper for validate_required.
     *
     * @deprecated 1.0.0 Use Validator::validate_required()
     * @param mixed $value Value to check.
     * @return string|true
     */
    function splwt_validate_required( $value ) {
        return Validator::validate_required( $value );
    }
}

if ( ! function_exists( 'splwt_validate_url' ) ) {
    /**
     * Legacy wrapper for validate_url.
     *
     * @deprecated 1.0.0 Use Validator::validate_url()
     * @param string $value URL.
     * @return string|true
     */
    function splwt_validate_url( $value ) {
        return Validator::validate_url( $value );
    }
}

if ( ! function_exists( 'splwt_customize_validate_email' ) ) {
    /**
     * Legacy wrapper for customizer_validate_email.
     *
     * @deprecated 1.0.0 Use Validator::customizer_validate_email()
     * @param object $validity     Customizer validity.
     * @param string $value        Email value.
     * @param object $wp_customize Customizer instance.
     * @return object
     */
    function splwt_customize_validate_email( $validity, $value, $wp_customize ) {
        return Validator::customizer_validate_email( $validity, $value, $wp_customize );
    }
}

if ( ! function_exists( 'splwt_customize_validate_numeric' ) ) {
    /**
     * Legacy wrapper for customizer_validate_numeric.
     *
     * @deprecated 1.0.0 Use Validator::customizer_validate_numeric()
     * @param object $validity     Customizer validity.
     * @param string $value        Numeric value.
     * @param object $wp_customize Customizer instance.
     * @return object
     */
    function splwt_customize_validate_numeric( $validity, $value, $wp_customize ) {
        return Validator::customizer_validate_numeric( $validity, $value, $wp_customize );
    }
}

if ( ! function_exists( 'splwt_customize_validate_required' ) ) {
    /**
     * Legacy wrapper for customizer_validate_required.
     *
     * @deprecated 1.0.0 Use Validator::customizer_validate_required()
     * @param object $validity     Customizer validity.
     * @param string $value        Field value.
     * @param object $wp_customize Customizer instance.
     * @return object
     */
    function splwt_customize_validate_required( $validity, $value, $wp_customize ) {
        return Validator::customizer_validate_required( $validity, $value, $wp_customize );
    }
}

if ( ! function_exists( 'splwt_customize_validate_url' ) ) {
    /**
     * Legacy wrapper for customizer_validate_url.
     *
     * @deprecated 1.0.0 Use Validator::customizer_validate_url()
     * @param object $validity     Customizer validity.
     * @param string $value        URL value.
     * @param object $wp_customize Customizer instance.
     * @return object
     */
    function splwt_customize_validate_url( $validity, $value, $wp_customize ) {
        return Validator::customizer_validate_url( $validity, $value, $wp_customize );
    }
}