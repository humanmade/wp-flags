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
		$keys = [
			'available',
			'active',
			'optin',
		];

		if ( in_array( $key, $keys, true ) ) {
			$this->{$key} = $this->evaluate( $value );
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
	public function on( string $property, callable $callback, int $priority = 10 ) {
		add_action( 'wp_flag_' . $this->id . '_change_' . $property, $callback, $priority, 2 );

		return $this;
	}

	/**
	 * Returns either the value if not callable, or the return of the callable otherwise
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function evaluate( $value ) {
		return is_callable( $value ) ? $value( $this ) : $value;
	}
}
