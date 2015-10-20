<?php
namespace mimosafa\WP\Settings;

/**
 * WordPress Options API interface class
 *
 * @access public
 *
 * @package WordPress
 * @subpackage WordPress Libraries by mimosafa
 *
 * @license GPLv2
 *
 * @author Toshimichi Mimoto <mimosafa@gmail.com>
 */
class Options {

	/**
	 * @var array { @type mimosafa\WP\Settings\Options }
	 */
	private static $_instances = [];

	/**
	 * @var string
	 */
	private $_prefix;
	private $_cache_group;

	/**
	 * @var array
	 */
	private $_keys = [];

	/**
	 * @var array
	 */
	private $_options = [];

	/**
	 * Get Instance (Singleton Pattern)
	 *
	 * @access public
	 *
	 * @param  string $group
	 * @return mimosafa\WP\Settings\Options
	 */
	public static function instance( $group = '' ) {
		if ( ! self::instanceExists( $group ) ) {
			self::$_instances[$group] = new self( $group );
		}
		return self::$_instances[$group];
	}

	/**
	 * @access public
	 *
	 * @param  string $group
	 * @return boolean
	 */
	public static function instanceExists( $group ) {
		if ( ! self::isSanitizedString( $group ) ) {
			throw new \Exception( 'Invalid Paramator' );
		}
		return isset( self::$_instances[$group] );
	}

	/**
	 * @access private
	 *
	 * @param  string $string
	 * @return boolean|string
	 */
	private static function isSanitizedString( $string ) {
		if ( ! $string || ! is_string( $string ) || $string[0] === '_' ) {
			return false;
		}
		return $string === sanitize_key( $string );
	}

	/**
	 * Constructor
	 *
	 * @access private
	 */
	private function __construct( $group ) {
		$this->_prefix = $group ? $group . '_' : '';
		$this->_cache_group = $this->_prefix . 'options_cache_group';
		/*
		add_filter( 'pre_update_option', [ $this, 'pre_update_option' ], 10, 3 );
		add_action( 'updated_option', [ $this, 'updated_option' ], 10, 3 );
		*/
	}

	/**
	 * Add option key
	 *
	 * @access public
	 *
	 * @param  string          $option
	 * @param  string|callable $filter
	 * @return mimosafa\WP\Settings\Options
	 */
	public function add( $option, $filter = null ) {
		if ( self::isSanitizedString( $option ) ) {
			if ( $filter ) {
				if ( method_exists( __CLASS__, 'option_filter_' . $filter ) ) {
					$filter_cb = [ $this, 'option_filter_' . $filter ];
				} else if ( is_callable( $filter ) ) {
					$filter_cb = $filter;
				}
			}
			$this->_keys[$option] = isset( $filter_cb ) ? $filter_cb : null;
		}
		return $this;
	}

	/**
	 * Full Option Key String
	 *
	 * @access public
	 *
	 * @param  string
	 * @return string|null
	 */
	public function __get( $name ) {
		return array_key_exists( $name, $this->_keys ) ? $this->_prefix . $name : null;
	}

	/**
	 * Options Method interface
	 *
	 * @access public
	 */
	public function __call( $name, $args ) {
		if ( ! $this->_keys )
			return;
		if ( substr( $name, 0, 4 ) === 'get_' ) :
			array_unshift( $args, substr( $name, 4 ) );
			return call_user_func_array( [ &$this, 'get' ], $args );
		elseif ( substr( $name, 0, 7 ) === 'update_' ) :
			array_unshift( $args, substr( $name, 7 ) );
			return call_user_func_array( [ &$this, 'update' ], $args );
		elseif ( substr( $name, 0, 7 ) === 'delete_' ) :
			array_unshift( $args, substr( $name, 7 ) );
			return call_user_func_array( [ &$this, 'delete' ], $args );
		endif;
	}

	/**
	 * Option getter
	 * - If the option dose not exists, return null value
	 *
	 * @access private
	 *
	 * @param  string $key
	 * @param  string $subkey Optional
	 * @return mixed|null
	 */
	private function get() {
		$args = func_get_args();
		$key = $args[0];
		if ( ! array_key_exists( $key, $this->_keys ) ) {
			return null;
		}
		$subkey =  isset( $args[1] ) && filter_var( $args[1] ) ? $args[1] : null;
		$key .= $subkey ? '_' . $subkey : '';
		if ( ! $value = wp_cache_get( $key, $this->_cache_group ) ) {
			if ( $value = get_option( $this->_prefix . $key, null ) ) {
				wp_cache_set( $key, $value, $this->_cache_group );
			}
		}
		// for Test
		$value = apply_filters( $this->_prefix . 'options_get_' . $args[0], $value, $subkey );
		return $value;
	}


	/**
	 * Option setter
	 *
	 * @access private
	 *
	 * @param  string $key
	 * @param  string $subkey   Optional
	 * @param  mixed  $newvalue
	 * @return boolean
	 */
	private function update() {
		$args = func_get_args();
		$key = $args[0];
		if ( count( $args ) > 2 && ! is_array( $args[1] ) && ! is_object( $args[1] ) ) {
			$newvalue = $args[2];
			$subkey = $args[1];
			$oldvalue = $this->get( $key, $subkey );
		} else {
			$newvalue = $args[1];
			$oldvalue = $this->get( $key );
		}
		if ( $filter = $this->_keys[$key] ) {
			$newvalue = call_user_func( $filter, $newvalue );
			if ( ! isset( $newvalue ) ) {
				return null;
			}
		}
		if ( $oldvalue === $newvalue ) {
			return false;
		}
		$key .= isset( $subkey ) ? '_' . $subkey : '';
		wp_cache_delete( $key, $this->_cache_group );
		return update_option( $this->_prefix . $key, $newvalue );
	}

	/**
	 * Deleter
	 *
	 * @access private
	 *
	 * @param  string $key
	 * @param  string $subkey Optional
	 * @return
	 */
	private function delete() {
		$args = func_get_args();
		$key = $args[0];
		if ( ! array_key_exists( $key, $this->_keys ) ) {
			return null;
		}
		if ( isset( $args[1] ) && filter_var( $args[1] ) ) {
			$key .= '_' . $args[1];
		}
		wp_cache_delete( $key, $this->_cache_group );
		return delete_option( $this->_prefix . $key );
	}

	private function option_filter_default( $var ) {
		$var = filter_var( $var );
		return $var ?: null;
	}

	private function option_filter_boolean( $var ) {
		return filter_var( $var, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
	}

	/**
	 * @access private
	 *
	 * @see https://github.com/WordPress/WordPress/blob/4.2-branch/wp-includes/option.php#L270
	 *
	 * @param mixed  $value     The new, unserialized option value.
	 * @param string $option    Name of the option.
	 * @param mixed  $old_value The old option value.
	 */
	/*
	public function pre_update_option( $value, $option, $old_value ) {
		if ( $keys = $this->option_key( $option ) ) {
			extract( $keys );
			return apply_filters( $this->_prefix . 'pre_update_option_' . $key, $value, $old_value, $subkey );
		}
		return $value;
	}

	public function updated_option( $option, $old_value, $value ) {
		if ( $keys = $this->option_key( $option ) ) {
			extract( $keys );
			do_action( $this->_prefix . 'updated_option_' . $key, $old_value, $value, $subkey );
		}
	}

	private function option_key( $option ) {
		if ( $this->_prefix !== substr( $option, 0, strlen( $this->_prefix ) ) ) {
			return false;
		}
		$key = substr( $option, strlen( $this->_prefix ) );
		$subkey = '';
		if ( ! array_key_exists( $key, $this->_keys ) ) {
			foreach ( $this->_keys as $string ) {
				if ( $string . '_' === substr( $key, 0, strlen( $string ) + 1 ) ) {
					$key = $string;
					$subkey = substr( $key, strlen( $string ) + 2 );
					break;
				}
			}
			if ( ! $subkey ) {
				return false;
			}
		}
		return [ 'key' => $key, 'subkey' => $subkey ];
	}
	*/

}
