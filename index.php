<?php
/**
 * Silence is golden.
 *
 * This file prevents direct access to the plugin directory.
 * Direct access to this file returns a blank HTTP 200 response
 * without exposing any sensitive information.
 *
 * @package PearlWeather
 * @since   1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;