<?php
/**
 * UI for turning flags on or off for a given user.
 *
 * @package HumanMade\WpFlags
 */

namespace HumanMade\Flags\UserMetabox;

use HumanMade\Flags\Flags;

/**
 * Bootstrap the feature
 */
function bootstrap() : void {
	add_action( 'show_user_profile', __NAMESPACE__ . '\render' );
	add_action( 'edit_user_profile', __NAMESPACE__ . '\render' );
	add_action( 'personal_options_update', __NAMESPACE__ . '\save' );
	add_action( 'edit_user_profile_update', __NAMESPACE__ . '\save' );
}

/**
 * Render user profile metabox
 *
 * @param \WP_User $user User whose profile is being edited.
 */
function render( \WP_User $user ) : void {
	$flags = wp_list_filter(
		Flags::get_all(),
		[
			'available' => true,
			'scope' => 'user',
		]
	);
	$values = call_user_func_array( 'array_merge', array_map( function ( $flag ) use ( $user ) {
		/* @var \HumanMade\Flags\Flag $flag */
		return [ $flag->id => get_user_meta( $user->ID, $flag->get_storage_key(), true ) === 'active' ];
	}, $flags ) );
	?>
	<table class="form-table">
		<tr>
			<th>
				<h3><?php esc_html_e( 'Flags', 'wp-flags' ); ?></h3>
			</th>
			<td>
				<?php foreach ( $flags as $flag ) : ?>
					<label for="wp-flags-<?php echo esc_attr( $flag->id ); ?>">
						<input
							type="checkbox"
							name="wp-flags[<?php echo esc_attr( $flag->id ); ?>]"
							id="wp-flags-<?php echo esc_attr( $flag->id ); ?>"
							value="1"
							<?php checked( true, $values[ $flag->id ] ?? false ); ?>
							<?php disabled( false, $flag->optin ); ?>
						/>
						<?php echo esc_html( $flag->title ); ?>
					</label>
					<br/>
				<?php endforeach; ?>
			</td>
		</tr>
	</table>
	<?php
	wp_nonce_field( 'wp-flags', '_wp_flags_nonce' );
}

/**
 * Save user flag preferences
 *
 * @param int $user_id ID of the user whose profile is being saved.
 *
 * @return bool
 */
function save( int $user_id ) {
	if ( ! wp_verify_nonce( filter_input( INPUT_POST, '_wp_flags_nonce' ), 'wp-flags' ) ) {
		return false;
	}

	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	$flags  = wp_list_filter( Flags::get_all(), [ 'available' => true ] );
	$values = filter_input( INPUT_POST, 'wp-flags', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	foreach ( $flags as $flag ) {
		$value = isset( $values[ $flag->id ] ) ? 'active' : 'inactive';
		update_user_meta( $user_id, $flag->get_storage_key(), $value );
	}

	return true;
}
