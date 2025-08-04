<?php
/**
 * AJAX handling for updating the flags from the admin bar.
 *
 * @package HumanMade\WpFlags
 */

namespace HumanMade\Flags\Ajax;

use HumanMade\Flags\Flag;
use HumanMade\Flags\Flags;

/**
 * Bootstrap the feature
 */
function bootstrap() : void {
	add_action( 'wp_ajax_wp_flag_ajax_trigger', __NAMESPACE__ . '\handle_endpoint' );
	add_action( 'all_admin_notices', __NAMESPACE__ . '\admin_notice' );
	add_filter( 'removable_query_args', __NAMESPACE__ . '\removable_query_args' );
}

/**
 * Handle the AJAX endpoint
 */
function handle_endpoint() : void {
	$redirect = filter_input( INPUT_GET, 'redirect' );

	try {
		handle_change();
	} catch ( \Exception $e ) {
		error_log( $e->getMessage() );
	}

	if ( $redirect ) {
		if ( isset( $e ) ) {
			$redirect = add_query_arg( 'wp_flags_error', rawurlencode( $e->getMessage() ), $redirect );
		}

		wp_safe_redirect( $redirect );
		exit;
	} else {
		if ( isset( $e ) ) {
			wp_send_json_error( $e->getMessage() );
		} else {
			wp_send_json_success();
		}
	}
}

/**
 * Handle changing the a flag's status.
 *
 * @throws \Exception Notice that a flag was toggled and should not have been.
 */
function handle_change() : void {
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
 * @param \HumanMade\Flags\Flag $flag Current flag.
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
			'redirect' => rawurlencode( $_SERVER['REQUEST_URI'] ),
		],
		admin_url( 'admin-ajax.php' )
	);
}

/**
 * Show the AJAX request error as an admin notice, if is_admin()
 */
function admin_notice() : void {
	$error = filter_input( INPUT_GET, 'wp_flags_error' );

	if ( empty( $error ) ) {
		return;
	}

	printf( '<div class="%s"><p>%s</p></div>', 'notice notice-error is-dismissible', esc_html( $error ) );
}

/**
 * Mark the wp_flags_error as a single use query arg for WordPress.
 *
 * @param array $args Arguments which can be removed from a URL string.
 *
 * @return array
 */
function removable_query_args( $args ) {
	return array_merge( $args, [ 'wp_flags_error' ] );
}
