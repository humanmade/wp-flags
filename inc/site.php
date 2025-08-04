<?php
/**
 * Handling of site-handled flags.
 *
 * @package HumanMade\WpFlags
 */

namespace HumanMade\Flags\Site;

use HumanMade\Flags\Flag;
use HumanMade\Flags\Flags;

/**
 * Setup namespace hooks.
 */
function bootstrap() : void {
	add_action( 'init', __NAMESPACE__ . '\\hook', 2 );
}

/**
 * Setup functionality after all other code has time to register flags.
 */
function hook() : void {
	// Go through all registered.
	array_map( __NAMESPACE__ . '\\handle', Flags::get_all() );

	// Hook to any newly registered flag after this point.
	add_action( 'wp_flag_added', __NAMESPACE__ . '\\handle', 1 );
}

/**
 * Retrieve site preference from meta, then register the callback.
 *
 * @param \HumanMade\Flags\Flag $flag Flag to evaluate.
 */
function handle( Flag $flag ) : void {
	// check Flag scope.
	if ( $flag->scope !== 'site' ) {
		return;
	}

	// Get site preference, if any, to set current status of the flag.
	$value = get_option( $flag->get_storage_key(), true, '' );
	if ( $value ) {
		$flag->set( 'active', $value === 'active' );
	}

	// Hook to any save operation afterwards.
	$flag->on( 'active', __NAMESPACE__ . '\\save' );
}

/**
 * Toggle the site flag status.
 *
 * @param bool                  $value Enable or disable the flag.
 * @param \HumanMade\Flags\Flag $flag  Flag being saved.
 *
 * @return bool|int
 */
function save( bool $value, Flag $flag ) {
	return update_option( $flag->get_storage_key(), $value );
}
