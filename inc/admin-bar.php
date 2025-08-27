<?php
/**
 * Admin bar UI for toggling flags on or off.
 *
 * @package HumanMade\WpFlags
 */

namespace HumanMade\Flags\AdminBar;

use function HumanMade\Flags\Ajax\get_toggle_url;
use HumanMade\Flags\Flag;
use HumanMade\Flags\Flags;

/**
 * Register actions and filters
 */
function bootstrap() : void {
	add_action( 'admin_bar_init', function() {
		// Only display the admin bar entry if at least one Flag is available.
		if ( empty( wp_list_filter( Flags::get_all(), [ 'available' => true ] ) ) ) {
			return;
		}

		add_action( 'wp_before_admin_bar_render', __NAMESPACE__ . '\render' );
		add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_styles' );
		add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_styles' );
	} );
}

/**
 * Admin bar entry icon
 */
function enqueue_styles() : void {
	$css = "
	#wp-admin-bar-flags .ab-item {
		cursor: pointer;
	}
	#wp-admin-bar-flags .ab-icon:before {
		content: \"\\f227\";
	}
	#wp-admin-bar-flags .dashicons {
		font: 400 1rem/1 dashicons;
		vertical-align: middle;
		padding-right: 0.4em;
	}
	#wp-admin-bar-flags .dashicons.right {
		padding-left: 0.4em;
		padding-bottom: 0.2em;
	}
	#wp-admin-bar-flags.has-active-flags > .ab-item,
	#wp-admin-bar-flags.has-active-flags > .ab-item > .ab-icon::before { color: lightgreen }
	#wp-admin-bar-flags .ab-submenu .optin-1.active-1 .ab-item { color: lightgreen }
	#wp-admin-bar-flags .ab-submenu .optin-1.active-0 .ab-item {}
	#wp-admin-bar-flags .ab-submenu .optin-0.active-1 .ab-item { color: lightgreen }
	#wp-admin-bar-flags .ab-submenu .optin-0.active-0 .ab-item { }
	#wp-admin-bar-flags .flag-count {
		display: inline-block;
		vertical-align: text-bottom;
		box-sizing: border-box;
		margin: 1px 0 -1px 0.5em;
		padding: 0 5px;
		min-width: 18px;
		height: 18px;
		border-radius: 9px;
		background-color: lightgreen;
		color: black;
		font-size: 11px;
		line-height: 1.6;
		text-align: center;
	}";
	wp_add_inline_style( 'admin-bar', $css );
}

/**
 * Render admin bar entries
 */
function render() : void {
	/* @var $wp_admin_bar \WP_Admin_Bar Admin bar class */
	global $wp_admin_bar;

	$available_flags = wp_list_filter( Flags::get_all(), [ 'available' => true ] );
	$active_flags = wp_list_filter( $available_flags, [ 'active' => true ] );
	$flag_count = ! empty( $active_flags ) ? '<span class="flag-count">' . count( $active_flags ) . '</span>' : '';

	$wp_admin_bar->add_menu( [
		'id'    => 'flags',
		'title' => '<span class="ab-icon"></span>' . esc_html__( 'Flags', 'wp-flags' ) . $flag_count,
		'meta'   => [
			'class' => $flag_count > 0 ? 'has-active-flags' : 'no-active-flags',
		],
	] );

	array_map( __NAMESPACE__ . '\add_flag_node', $available_flags );
}

/**
 * Add a flag to the admin bar
 *
 * @param \HumanMade\Flags\Flag $flag Current flag.
 *
 * @return bool
 */
function add_flag_node( Flag $flag ) {
	$href      = $flag->optin ? ( $flag->href ?? get_toggle_url( $flag ) ) : null;
	$title     = $flag->title;
	$icon      = $flag->meta['icon'] ?? 'flag';
	$pre_title = sprintf( '<span class="dashicons dashicons-%s"></span>', sanitize_html_class( $icon ) );

	if ( ! $flag->optin ) {
		$title .= '<span class="dashicons dashicons-lock right"></span>';
	}

	/* @var $wp_admin_bar \WP_Admin_Bar Admin bar class */
	global $wp_admin_bar;
	$wp_admin_bar->add_menu( [
		'id'     => 'flags-' . esc_attr( $flag->id ),
		'parent' => 'flags',
		'title'  => $pre_title . esc_html( apply_filters( 'wp_flag_ab_title', $title, $flag ) ),
		'href'   => esc_url_raw( apply_filters( 'wp_flag_ab_href', $href, $flag ) ),
		'meta'   => [
			'class' => esc_attr( apply_filters( 'wp_flag_ab_class', sprintf( 'optin-%s active-%s', (int) $flag->optin, (int) $flag->active ) ) ),
		],
	] );

	return true;
}
