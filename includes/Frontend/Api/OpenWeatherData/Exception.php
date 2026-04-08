<?php
/**
 * Custom Exception Class for OpenWeatherMap API
 *
 * Extends the base PHP Exception class to provide a distinguishable
 * exception type for OpenWeatherMap API-related errors.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/API/OpenWeatherData
 * @since      1.0.0
 */

namespace PearlWeather\API\OpenWeatherData;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class OpenWeatherException
 *
 * Custom exception for OpenWeatherMap API operations.
 * Allows catching API-specific exceptions separately from general exceptions.
 *
 * @since 1.0.0
 */
class OpenWeatherException extends \Exception {

    /**
     * HTTP status code associated with the exception.
     *
     * @var int|null
     */
    private $http_status_code;

    /**
     * API endpoint that generated the exception.
     *
     * @var string|null
     */
    private $endpoint;

    /**
     * Constructor.
     *
     * @param string          $message  Exception message.
     * @param int             $code     Exception code.
     * @param \Throwable|null $previous Previous exception.
     * @param int|null        $http_status_code HTTP status code.
     * @param string|null     $endpoint API endpoint.
     */
    public function __construct(
        $message = '',
        $code = 0,
        \Throwable $previous = null,
        $http_status_code = null,
        $endpoint = null
    ) {
        parent::__construct( $message, $code, $previous );
        $this->http_status_code = $http_status_code;
        $this->endpoint = $endpoint;
    }

    /**
     * Get the HTTP status code.
     *
     * @since 1.0.0
     * @return int|null
     */
    public function get_http_status_code() {
        return $this->http_status_code;
    }

    /**
     * Get the API endpoint.
     *
     * @since 1.0.0
     * @return string|null
     */
    public function get_endpoint() {
        return $this->endpoint;
    }

    /**
     * Check if this is an authentication error (401).
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_auth_error() {
        return 401 === $this->http_status_code;
    }

    /**
     * Check if this is a not found error (404).
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_not_found_error() {
        return 404 === $this->http_status_code;
    }

    /**
     * Check if this is a rate limit error (429).
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_rate_limit_error() {
        return 429 === $this->http_status_code;
    }

    /**
     * Create an exception from an HTTP response.
     *
     * @since 1.0.0
     * @param int    $status_code HTTP status code.
     * @param string $message     Error message.
     * @param string $endpoint    API endpoint.
     * @return self
     */
    public static function from_http_response( $status_code, $message, $endpoint = null ) {
        return new self( $message, $status_code, null, $status_code, $endpoint );
    }

    /**
     * Create a connection error exception.
     *
     * @since 1.0.0
     * @param string $message   Error message.
     * @param string $endpoint  API endpoint.
     * @return self
     */
    public static function connection_error( $message, $endpoint = null ) {
        return new self( $message, 0, null, null, $endpoint );
    }

    /**
     * Convert to string with additional context.
     *
     * @since 1.0.0
     * @return string
     */
    public function __toString() {
        $string = parent::__toString();
        
        if ( $this->http_status_code ) {
            $string .= "\nHTTP Status Code: {$this->http_status_code}";
        }
        
        if ( $this->endpoint ) {
            $string .= "\nEndpoint: {$this->endpoint}";
        }
        
        return $string;
    }
}