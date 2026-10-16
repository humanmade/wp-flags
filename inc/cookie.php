<?php
/**
 * Cookie flag handling.
 *
 * @package HumanMade\WpFlags
 */

namespace HumanMade\Flags\Cookie;

use HumanMade\Flags\Flag;
use HumanMade\Flags\Flags;

/**
 * Bootstrap the feature
 */
function bootstrap(): void {
	// Go through all registered.
	array_map( __NAMESPACE__ . '\\handle', Flags::get_all() );

	// Hook to any newly registered flag after this point.
	add_action( 'wp_flag_added', __NAMESPACE__ . '\\handle', 1 );
}

/**
 * Retrieve cookie preference, then register the callback
 *
 * @param \HumanMade\Flags\Flag $flag Flag to evaluate.
 */
function handle( Flag $flag ): void {
	// Check Flag scope.
	if ( $flag->scope !== 'cookie' ) {
		return;
	}

	// Get cookie preference, if any, to set current status of the flag.
	if ( isset( $_COOKIE[ $flag->get_storage_key() ] ) ) {
		$flag->set( 'active', $_COOKIE[ $flag->get_storage_key() ] === 'active' );
	}

	// Hook to any save operation afterwards.
	$flag->on( 'active', __NAMESPACE__ . '\save' );
}

/**
 * Toggle the cookie flag status
 *
 * The cookie uses the site-wide path so front-end requests send it, even when
 * it is set from `admin-ajax.php`.
 *
 * @param bool                  $value Enable or disable the flag.
 * @param \HumanMade\Flags\Flag $flag  Flag being saved.
 *
 * @return bool Whether the cookie was set.
 */
function save( bool $value, Flag $flag ) {
	$key   = $flag->get_storage_key();
	$state = $value ? 'active' : 'inactive';

	// The browser only sends the cookie from the next request on.
	$_COOKIE[ $key ] = $state;

	return setcookie(
		$key,
		$state,
		[
			'expires'  => time() + YEAR_IN_SECONDS,
			'path'     => COOKIEPATH,
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		]
	);
}
