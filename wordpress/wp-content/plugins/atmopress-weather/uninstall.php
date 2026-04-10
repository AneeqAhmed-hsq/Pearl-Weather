<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

// Remove settings
delete_option( 'atmopress_settings' );

// Remove all cached weather transients
global $wpdb;
$wpdb->query(
    "DELETE FROM {$wpdb->options}
     WHERE option_name LIKE '_transient_atmopress_%'
        OR option_name LIKE '_transient_timeout_atmopress_%'"
);
