<?php
/**
 * Feature flag management plugin for WordPress
 *
 * @package   wp-flags
 * @link      https://github.com/humanmade/wp-flags
 * @author    Shady Sharaf <shady@sharaf.me>
 * @license   MIT
 *
 * Plugin Name:  Flags
 * Description:  Feature flag management for WordPress
 * Version:      0.0.4
 * Plugin URI:   https://github.com/humanmade/wp-flags
 * Author:       Human Made
 * Author URI:   https://github.com/humanmade/wp-flags/graphs/contributors
 * Text Domain:  wp-flags
 * Domain Path:  /languages/
 * Network:      true
 * Requires PHP: 7.2
 */

namespace HumanMade\Flags;

require_once __DIR__ . '/inc/class-flag.php';
require_once __DIR__ . '/inc/class-flags.php';

require_once __DIR__ . '/inc/admin-bar.php';
require_once __DIR__ . '/inc/ajax.php';
require_once __DIR__ . '/inc/user.php';
require_once __DIR__ . '/inc/user-metabox.php';
require_once __DIR__ . '/inc/site.php';
require_once __DIR__ . '/inc/namespace.php';

bootstrap();
