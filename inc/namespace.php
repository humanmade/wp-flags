<?php
/**
 * Setup for our plugin.
 *
 * @package HumanMade\WpFlags
 */

namespace HumanMade\Flags;

/**
 * Bootstrap the plugin
 */
function bootstrap() : void {
	Cookie\bootstrap();
	User\bootstrap();
	UserMetabox\bootstrap();
	Site\bootstrap();
	SiteMetabox\bootstrap();

	if ( apply_filters( 'wp_flags_enable_admin_bar', true ) ) {
		AdminBar\bootstrap();
	}

	if ( apply_filters( 'wp_flags_enable_ajax', true ) ) {
		Ajax\bootstrap();
	}
}
