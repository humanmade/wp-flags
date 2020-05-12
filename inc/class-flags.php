<?php
/**
 * Single entry point for all flags.
 *
 * @package HumanMade\WpFlags
 */

namespace HumanMade\Flags;

/**
 * Class Flags
 *
 * Serving as the data store for flags
 *
 * @package HumanMade\Flags
 */
class Flags {

	/**
	 * Flags which have been registered.
	 *
	 * @var Flag[]
	 */
	private static $flags = [];

	/**
	 * Private constructor to prevent instantiation
	 */
	private function __construct() {
	}

	/**
	 * Add a new flag object
	 *
	 * @param string $id      Slug of the flag to use.
	 * @param string $title   Proper name of the flag.
	 * @param array  $options Options to assign to the flag. Further documented in the Flag class.
	 *
	 * @return \HumanMade\Flags\Flag
	 */
	static function add( string $id, string $title, array $options ) : Flag {
		self::$flags[ $id ] = new Flag( $id, $title, $options );

		do_action( 'wp_flag_added', self::$flags[ $id ] );

		return self::$flags[ $id ];
	}

	/**
	 * Get a flag object by id
	 *
	 * @param string $id ID of the flag to fetch.
	 *
	 * @return \HumanMade\Flags\Flag
	 * @throws \Exception Notice that a flag is unregistered.
	 */
	static function get( string $id ) : Flag {
		if ( ! isset( self::$flags[ $id ] ) ) {
			throw new \Exception( __( 'Unregistered flag', 'wp-flags' ) );
		}

		return self::$flags[ $id ];
	}

	/**
	 * Return all flag objects
	 *
	 * @return array
	 */
	static function get_all() : array {
		return self::$flags;
	}

}
