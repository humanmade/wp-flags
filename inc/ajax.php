<?php

namespace HumanMade\Flags\Ajax;

use HumanMade\Flags\Flag;
use HumanMade\Flags\Flags;

/**
 * Bootstrap the feature
 */
function bootstrap() {
	add_action( 'wp_ajax_wp_flag_ajax_trigger', __NAMESPACE__ . '\handle_endpoint' );
	add_action( 'all_admin_notices', __NAMESPACE__ . '\admin_notice' );
	add_filter( 'removable_query_args', __NAMESPACE__ . '\removable_query_args' );
}

/**
 * Handle the AJAX endpoint
 *
 * @throws \Exception
 */
function handle_endpoint() {
	$redirect = filter_input( INPUT_GET, 'redirect' );

	try {
		handle_change();
	} catch ( \Exception $e ) {
	}

	if ( $redirect ) {
		if ( isset( $e ) ) {
			$redirect = add_query_arg( 'wp_flags_error', rawurlencode( $e->getMessage() ), $redirect );
		}

		wp_safe_redirect( $redirect );
	} else {
		if ( isset( $e ) ) {
			wp_send_json_error( $e->getMessage() );
		} else {
			wp_send_json_success();
		}
	}
}

/**
 * Handle changing the user meta
 *
 * @throws \Exception
 */
function handle_change() {
	if ( ! wp_verify_nonce( filter_input( INPUT_GET, 'nonce' ), 'wp_flags' ) ) {
		throw new \Exception( __( 'Invalid security nonce.', 'wp-flags' ) );
	}

	$id     = filter_input( INPUT_GET, 'flag' );
	$status = filter_input( INPUT_GET, 'status' );
	$flag   = Flags::get( $id );

	if ( ! $flag->available ) {
		/* translators: %s is the flag id */
		throw new \Exception( sprintf( __( '"%s" Flag is not available.', 'wp-flags' ), $flag->title ) );
	}

	if ( ! $flag->optin ) {
		/* translators: %s is the flag id */
		throw new \Exception( sprintf( __( '"%s" Flag is forced and not op-in.', 'wp-flags' ), $flag->title ) );
	}

	$flag->set( 'active', (bool) $status );
}

/**
 * Return the AJAX endpoint to switch flag status
 *
 * @param \HumanMade\Flags\Flag $flag
 *
 * @return string
 */
function get_toggle_url( Flag $flag ) : string {
	return add_query_arg(
		[
			'action'   => 'wp_flag_ajax_trigger',
			'flag'     => $flag->id,
			'status'   => (int) ! $flag->active,
			'nonce'    => wp_create_nonce( 'wp_flags' ),
			'redirect' => $_SERVER['REQUEST_URI'],
		],
		admin_url( 'admin-ajax.php' )
	);
}

/**
 * Show the AJAX request error as an admin notice, if is_admin()
 */
function admin_notice() {
	$error = filter_input( INPUT_GET, 'wp_flags_error' );

	if ( empty( $error ) ) {
		return;
	}

	printf( '<div class="%s"><p>%s</p></div>', 'notice notice-error is-dissmissible', $error );
}

/**
 * Mark the wp_flags_error as a single use query arg for WordPress
 *
 * @param $args
 *
 * @return array
 */
function removable_query_args( $args ) {
	return array_merge( $args, [ 'wp_flags_error' ] );
}
