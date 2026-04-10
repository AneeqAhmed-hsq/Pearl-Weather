<?php
namespace AtmoPress;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class DataCache {

    const PREFIX = 'atmopress_';

    private static function key( $raw ) {
        return self::PREFIX . md5( $raw );
    }

    public static function get( $identifier ) {
        return get_transient( self::key( $identifier ) );
    }

    public static function set( $identifier, $data, $minutes = null ) {
        if ( null === $minutes ) {
            $minutes = (int) Settings::get( 'cache_minutes', 30 );
        }
        if ( $minutes < 1 ) {
            return false;
        }
        return set_transient( self::key( $identifier ), $data, $minutes * MINUTE_IN_SECONDS );
    }

    public static function delete( $identifier ) {
        return delete_transient( self::key( $identifier ) );
    }

    public static function flush_all() {
        global $wpdb;
        $like = $wpdb->esc_like( '_transient_' . self::PREFIX ) . '%';
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $like,
            $wpdb->esc_like( '_transient_timeout_' . self::PREFIX ) . '%'
        ) );
    }
}
