<?php

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
	// Go through all registered
	array_map( __NAMESPACE__ . '\handle', Flags::get_all() );

	// Hook to any newly registered flag after this point
	add_action( 'wp_flag_added', __NAMESPACE__ . '\handle', 1 );
}

/**
 * Retrieve user preference from meta, then register the callback
 *
 * @param \HumanMade\Flags\Flag $flag
 */
function handle( Flag $flag ) {
	// check Flag scope
	if ( $flag->scope !== 'user' ) {
		return;
	}

	// Get user preference, if any, to set current status of the flag
	$value = get_user_meta( get_current_user_id(), $flag->get_meta_key(), true );
	if ( $value ) {
		$flag->set( 'active', $value === 'active' );
	}

	// Hook to any save operation afterwards
	$flag->on( 'active', __NAMESPACE__ . '\save' );
}

/**
 * Toggle the user flag status
 *
 * @param bool                  $value
 * @param \HumanMade\Flags\Flag $flag
 *
 * @return bool|int
 */
function save( bool $value, Flag $flag ) {
	return update_user_meta( get_current_user_id(), $flag->get_meta_key(), $value ? 'active' : 'inactive' );
}
