<?php

namespace HumanMade\Flags;

/**
 * Class Flag
 *
 * @package HumanMade\Flags
 */
class Flag {

	/**
	 * Flag ID
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Flag title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Is the flag available for the current request ( site, user, etc ) ?
	 *
	 * @var bool
	 */
	public $available = true;

	/**
	 * Is the flag opt-in or enforced ?
	 *
	 * @var bool
	 */
	public $optin = true;

	/**
	 * Is the flag currently available AND activated by the user ( or the system )
	 *
	 * @var bool
	 */
	public $active = false;

	/**
	 * Set the flag scope sitewide or on user level (`user`|`site`)
	 *
	 *
	 * @var string
	 */
	public $scope = 'user';

	/**
	 * Placeholder for additional meta values
	 *
	 * @var array
	 */
	public $meta = [];

	/**
	 * Flag constructor.
	 *
	 * @param string $id
	 * @param string $title
	 * @param array  $options
	 */
	public function __construct( string $id, string $title, array $options = [] ) {
		$this->id    = $id;
		$this->title = $title;

		if ( $options ) {
			array_map( [ $this, 'set' ], array_keys( $options ), array_values( $options ) );
		}

		// Make sure to evaluate all callable values first time after init, or now if the flag is registered after
		if ( did_action( 'init' ) ) {
			$this->evaluate();
		} else {
			add_action( 'init', [ $this, 'evaluate' ], 1 );
		}
	}

	/**
	 * Set an option
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return \HumanMade\Flags\Flag
	 */
	public function set( string $key, $value ) : Flag {
		if ( property_exists( __CLASS__, $key ) ) {
			$this->{$key} = $value;
		} else {
			$this->meta[ $key ] = $value;
		}

		do_action( 'wp_flag_change_' . $key, $value, $this );
		do_action( 'wp_flag_' . $this->id . '_change_' . $key, $value, $this );

		return $this;
	}

	/**
	 * Hook an action on updating a flag property
	 *
	 * @param string   $property
	 * @param callable $callback
	 * @param int      $priority
	 *
	 * @return \HumanMade\Flags\Flag
	 */
	public function on( string $property, callable $callback, int $priority = 10 ) : Flag {
		add_action( 'wp_flag_' . $this->id . '_change_' . $property, $callback, $priority, 2 );

		return $this;
	}

	/**
	 * Get the meta key used for our flag.
	 */
	public function get_meta_key() : string {
		return sprintf( '_wp_flag_%s', $this->id );
	}

	/**
	 * Evaluate all callable arguments early on the `init` action
	 */
	public function evaluate() : void {
		foreach ( get_object_vars( $this ) as $key => $value ) {
			if ( is_callable( $value ) ) {
				$this->{$key} = call_user_func( $value, $this );
			}
		}
	}
}
