<table width="100%">
	<tr>
		<td align="left" width="70">
			<strong>Flags</strong><br />
			Lightweight WordPress plugin to enable exposing feature flags to end-users, based on code-based ( or admin UI in the future ) criteria.
		</td>
		<td align="right" width="20%">
		</td>
	</tr>
	<tr>
		<td>
			A <strong><a href="https://hmn.md/">Human Made</a></strong> project.
		</td>
		<td align="center">
            <img src="https://humanmade.com/uploads/2025/01/HM-red-square.svg" width="100" />
		</td>
	</tr>
</table>

**NOTE**: This is a work-in-progress plugin.

Installation
==========

#### Using Composer

- Require the package in your project

`composer require humanmade/wp-flags`

#### Using submodules

- Add the plugin as a submodule ( adjust the path as necessary )

`git submodule add git@github.com:humanmade/wp-flags.git content/plugins/wp-flags`

Usage
==========

```$php
use HumanMade\Flags\Flags;

add_action( 'init', function() {
    Flags::add( 'new-flag', 'New Flag', [
        // Is the flag exposed to users ?
        'available' => function() {
            return current_user_can( 'manage_options' );
        },
        // At what level the flag can be set. One of `user` or `site`
        'scope' => 'user',
        // Default flag status
        'active' => true,
        // Is the flag controllable by users ?
        'optin' => true,
        // Custom icon ? ( dashicon-compatible )
        'icon' => 'dashboard',
        // Custom attribute ?
        'some_custom_meta_key' => 'some_value',
    ] );

    // OR just..
    $flag = Flags::add( 'another-flag', 'Another flag' );
    $flag->on( 'active', function( $active, $flag ) {
        // do something based on active status change
    } );

    // Execute logic based on flag status
    if ( Flags::get( 'new-flag' )->active ) {
        show_the_new_sidebar();
    } );
} );
```

Scope of Setting a Flag
==========

A flag can be set at either the `user` or `site` scope, which determines how a flag is controlled. A `user`-scoped flag can be turned on or off by each user for that user only on a site, whereas a `site`-scoped flag is turned on or off for every user and is controlled in the site settings.

Credits
=======

Written and maintained by [Shady Sharaf](https://github.com/shadyvb). Thanks to all our [contributors](https://github.com/humanmade/wp-flags/graphs/contributors).

Interested in joining in on the fun? [Join us, and become human!](https://hmn.md/is/hiring/)
