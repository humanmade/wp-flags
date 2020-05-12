<?php
/**
 * User flag handling.
 *
 * @package HumanMade\WpFlags
 */

namespace HumanMade\Flags\User;

use HumanMade\Flags\Flag;
use HumanMade\Flags\Flags;

/**
 * Bootstrap the feature
 */
function bootstrap() {
	add_action( 'init', __NAMESPACE__ . '\hook', 2 );
}

/**
 * Setup all after we know who's logged in
 */
function hook() {
	// Go through all registered.
	array_map( __NAMESPACE__ . '\handle', Flags::get_all() );

	// Hook to any newly registered flag after this point.
	add_action( 'wp_flag_added', __NAMESPACE__ . '\handle', 1 );
}

/**
 * Retrieve user preference from meta, then register the callback
 *
 * @param \HumanMade\Flags\Flag $flag Flag to evaluate.
 */
function handle( Flag $flag ) {
	// Get user preference, if any, to set current status of the flag.
	$value = get_user_meta( get_current_user_id(), get_flag_meta_key( $flag ), true );
	if ( $value ) {
		$flag->set( 'active', $value === 'active' );
	}

	// Hook to any save operation afterwards.
	$flag->on( 'active', __NAMESPACE__ . '\save' );
}

/**
 * Return meta key for the flag
 *
 * @param \HumanMade\Flags\Flag $flag
 *
 * @return string
 */
function get_flag_meta_key( Flag $flag ) : string {
	return sprintf( '_wp_flag_%s', $flag->id );
}

/**
 * Toggle the user flag status
 *
 * @param bool                  $value Enable or disable the flag.
 * @param \HumanMade\Flags\Flag $flag  Flag being saved.
 *
 * @return bool|int
 */
function save( bool $value, Flag $flag ) {
	return update_user_meta( get_current_user_id(), get_flag_meta_key( $flag ), $value ? 'active' : 'inactive' );
}
