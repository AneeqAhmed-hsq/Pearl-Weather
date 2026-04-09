<?php
define( 'DB_NAME', 'wordpress' );
define( 'DB_USER', 'wordpress' );
define( 'DB_PASSWORD', 'wordpress' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         'replit-auth-key-unique-1' );
define( 'SECURE_AUTH_KEY',  'replit-secure-auth-key-unique-2' );
define( 'LOGGED_IN_KEY',    'replit-logged-in-key-unique-3' );
define( 'NONCE_KEY',        'replit-nonce-key-unique-4' );
define( 'AUTH_SALT',        'replit-auth-salt-unique-5' );
define( 'SECURE_AUTH_SALT', 'replit-secure-auth-salt-unique-6' );
define( 'LOGGED_IN_SALT',   'replit-logged-in-salt-unique-7' );
define( 'NONCE_SALT',       'replit-nonce-salt-unique-8' );

$table_prefix = 'wp_';

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost:5000';
define( 'WP_SITEURL', 'http://' . $http_host );
define( 'WP_HOME', 'http://' . $http_host );

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
