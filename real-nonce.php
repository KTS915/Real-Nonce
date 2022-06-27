<?php if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Plugin Name: Real Nonce
 * Version: 0.1
 * Description: A very simple, but genuine, NONCE system for ClassicPress Developers. Forked, with a major bug fix, from wp-simple-nonce by Cal Evans.
 * Author: Tim Kaye
 * Author URI: https://timkaye.org
 * Plugin URI: https://timkaye.org
 */

Class WPSimpleNonce {

	const option_root = 'wp-snc';

	public static function createNonce( $name ) {

		if ( is_array( $name ) ) {
			if ( isset( $name['name'] ) ) {
				$name = $name['name'];
			} else {
				$name = 'nonce';
			}
		}

		$id = self::generate_id();
		$name = substr( $name, 0, 17 ) . '_' . $id;

		$nonce = md5( wp_salt( 'nonce' ) . $name . microtime( true ) );
		self::storeNonce( $nonce, $name );
		return array( 'name' => $name, 'value' => $nonce );
	}


	public static function createNonceField( $name = 'nonce') {
		if ( is_array( $name ) ) {
			if ( isset( $name['name'] ) ) {
				$name = $name['name'];
			} else {
				$name = 'nonce';
			}
		}

		$name = filter_var( $name, FILTER_SANITIZE_STRING );
		$nonce = self::createNonce( $name );
		$nonce['value'] = '<input type="hidden" name="' . $nonce['name'] . '" value="' . $nonce['value'] . '">';
		return $nonce;
	}


	public static function checkNonce( $name, $value ) {
		if ( empty( $name ) || empty( $value ) ) {
			return false;
		}
		$name = filter_var( $name, FILTER_SANITIZE_STRING );
		$nonce = self::fetchNonce( $name );
		$returnValue = ( $nonce === $value );
		return $returnValue;
	}


	public static function storeNonce( $nonce, $name ) {
		if ( empty( $name ) ) {
			return false;
		}

		add_option( self::option_root . '_' . $name, $nonce );
		add_option( self::option_root . '_expires_' . $name, time() + 86400 );
		return true;
	}


	protected static function fetchNonce( $name ) {
		$fetched_value = get_option( self::option_root . '_' . $name );
		$nonce_expires = get_option( self::option_root . '_expires_' . $name );
		
		self::deleteNonce( $name );
		
		if ( $nonce_expires < time() ) {
			$fetched_value = null;
		}

		return $fetched_value;
	}


	public static function deleteNonce( $name ) {
		$optionDeleted = delete_option( self::option_root . '_' . $name );
		$optionDeleted = $optionDeleted && delete_option( self::option_root . '_expires_' . $name);
		return (bool) $optionDeleted;
	}


	public static function clearNonces( $force = false ) {
		if ( defined( 'WP_SETUP_CONFIG' ) or defined( 'WP_INSTALLING' ) ) {
			return;
		}

		global $wpdb;
		$sql = 'SELECT option_id, option_name, option_value FROM ' . $wpdb->options . ' WHERE option_name like "' . self::option_root . '_expires_%"';
		$rows = $wpdb->get_results( $sql );
		$noncesDeleted = 0;

		foreach ( $rows as $singleNonce ) {

			if ( $force or ( $singleNonce->option_value < time() + 86400 ) ) {
				$name = substr( $singleNonce->option_name, strlen( self::option_root . '_expires_' ) );
				$noncesDeleted += ( self::deleteNonce( $name ) ? 1 : 0 );
			}
		}

		return (int) $noncesDeleted;

	}


	protected static function generate_id() {
		require_once( ABSPATH . 'wp-includes/class-phpass.php' );
		$hasher = new PasswordHash( 8, false );
		return md5( $hasher->get_random_bytes( 100, false ) );
	}

}


add_shortcode( 'simpleNonce', 'WPSimpleNonce::createNonce' );


function wp_snonce_cleanup() {
	WPSimpleNonce::clearNonces();
}
add_action( 'wp_simple_nonce_cleanup', 'wp_snonce_cleanup' );


function wp_simple_nonce_register_garbage_collection() {
	if ( ! wp_next_scheduled( 'wp_simple_nonce_cleanup' ) ) {
		wp_schedule_event( time(), 'hourly', 'wp_simple_nonce_cleanup' );
	}
}
add_action( 'wp', 'wp_simple_nonce_register_garbage_collection' );
