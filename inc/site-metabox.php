<?php
/**
 * Add a UI for enabling or disabling site-wide flags.
 *
 * @package HumanMade/WpFlags;
 */

namespace HumanMade\Flags\SiteMetabox;

use HumanMade\Flags\Flags;

/**
 * Bootstrap the feature
 */
function bootstrap() {
	add_action( 'admin_init', __NAMESPACE__ . '\\register_settings' );
}

/**
 * Register our fieldgroup and setting with WordPress.
 */
function register_settings() {
	// Add a new setting group for site-wide flags in General Options.
	add_settings_section(
		'wp_flags',
		esc_html__( 'Flags', 'wp-flags' ),
		'__return_null',
		'general'
	);

	// Add site-wide option to enable/disable all flags.
	add_settings_field(
		'wp-flags',
		esc_html__( 'Enable Flags', 'wp-flags' ),
		__NAMESPACE__ . '\\render',
		'general',
		'wp_flags'
	);

	// Register each flag individually so they're properly handled upon save.
	foreach ( get_all_site_flags() as $flag ) {
		register_setting(
			'general',
			$flag->get_storage_key(),
			[
				'sanitize_callback' => __NAMESPACE__ . '\\sanitize_value',
				'type' => 'string',
			]
		);
	}
}

/**
 * Render site metabox.
 */
function render() {
	$flags  = get_all_site_flags();
	$values = [];
	foreach ( $flags as $flag ) {
		/* @var \HumanMade\Flags\Flag $flag */
		$values[ $flag->id ] = get_option( $flag->get_storage_key(), true, '' ) === 'active';
	}
	?>
	<table class="form-table">
		<tr>
			<td>
				<?php foreach ( $flags as $flag ) : ?>
					<label for="wp-flags-<?php echo esc_attr( $flag->id ); ?>">
						<input
								type="checkbox"
								name="<?php echo esc_attr( $flag->get_storage_key() ); ?>"
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
}

/**
 * Convert values from on/off to `active`/`inactive`.
 *
 * @param string $value Value to check against, expected to be boolean or "active"|"inactive".
 * @return string Value to save.
 */
function sanitize_value( $value ) : string {
	return ( $value && $value !== 'inactive' )
		? 'active'
		: 'inactive';
}

/**
 * Get all available flags which are registered as site-wide.
 *
 * @return array
 */
function get_all_site_flags() : array {
	return wp_list_filter(
		Flags::get_all(),
		[
			'available' => true,
			'scope' => 'site',
		]
	);
}
