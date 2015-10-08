<?php
namespace mimosafa\WP\Repository;

/**
 * @package WordPress
 * @author Toshimichi Mimoto <mimosafa@gmail.com>
 */
class Definition {

	/**
	 * Instances (for Singleton Pattern)
	 *
	 * @var array { @type __CLASS__ ${$prefix} }
	 */
	private static $instances = [];

	/**
	 * @var array
	 */
	private static $maybe_prefixes = [];

	/**
	 * @var array { @type array ${$prefix} }
	 */
	private static $cache = [];

	/**
	 * @var array
	 */
	private static $repositories = [];

	/**
	 * @var string
	 */
	private $prefix = '';

	/**
	 * @var array
	 */
	private $post_types_defaults;

	/**
	 * @var array
	 */
	private $taxonomies_defaults;

	/**
	 * Class name
	 *
	 * @var string
	 */
	private static $ptReg;
	private static $txReg;

	/**
	 * Get instance interface method
	 *
	 * @access public
	 *
	 * @uses   __CLASS__::getInstance()
	 *
	 * @param  string       $prefix    Optional.
	 * @param  array|string $defaults  Optional.
	 * @return __CLASS__
	 */
	public static function instance( $prefix = null, $defaults = null ) {
		if ( ! did_action( 'setup_theme' ) ) {
			throw new \Exception( 'Too Fast' );
		}
		if ( did_action( 'init' ) ) {
			throw new \Exception( 'Too Late' );
		}
		if ( ! isset( $prefix ) || $prefix === '' ) {
			return self::getInstance( '', $defaults );
		}
		if ( ! is_string( $prefix ) || $prefix !== sanitize_key( $prefix ) || strlen( $prefix ) > 16 ) {
			throw new \Exception( 'Invalid' );
		}
		$maybe_prefix = str_replace( [ '-', '_' ], ' ', $prefix );
		if ( in_array( $maybe_prefix, self::$maybe_prefixes, true ) ) {
			throw new \Exception( 'Similar Prefix Exists' );
		}
		self::$maybe_prefixes[] = $maybe_prefix;
		return self::getInstance( (string) $prefix, $defaults );
	}

	/**
	 * Initialize Post Type
	 *
	 * @access public
	 *
	 * @param  string       $name
	 * @param  array|string $args  Optional.
	 * @return __CLASS__
	 */
	public function post_type( $name, $args = null ) {
		if ( ! is_string( $name )
			|| ! $name
			|| ! filter_var( $this->prefix . $name, \FILTER_CALLBACK, [ 'options' => self::$ptReg . '::validate_name' ] )
		) {
			throw new \Exception( 'Invalid Post Type Name' );
		}
		if ( $cache =& $this->getCache() ) {
			$this->cleanCache();
		}
		$cache['post_type'] = $name;
		if ( $args && is_array( $args ) ) {
			array_walk( $args, self::$ptReg . '::arguments_walker' );
			foreach ( $args as $key => $val ) {
				if ( isset( $val ) ) {
					$cache[$key] = $val;
				}
			}
		}
	}

	/**
	 * Initialize Taxonomy
	 *
	 * @access public
	 */
	public function taxonomy( $name, $args = null ) {}






	/**
	 * Get instance
	 *
	 * @access private
	 *
	 * @param  string            $prefix
	 * @param  array|string|null $defaults
	 * @return __CLASS__
	 */
	private static function getInstance( $prefix, $defaults ) {
		if ( ! isset( self::$instances[$prefix] ) ) {
			self::$instances[$prefix] = new self( $prefix, $defaults );
		}
		return self::$instances[$prefix];
	}

	/**
	 * Constructor
	 *
	 * @access private
	 *
	 * @param  string            $prefix
	 * @param  array|string|null $defaults
	 */
	private function __construct( $prefix, $defaults ) {
		self::$cache[$prefix] = [];
		if ( $prefix ) {
			$this->prefix = $prefix;
		}
		if ( $defaults ) {
			$this->set_defaults( $defaults );
		}
		static $added_action = false;
		if ( ! $added_action ) {
			add_action( 'init', __CLASS__ . '::register', 1 );
			$added_action = true;
		}
		if ( ! isset( self::$ptReg ) ) {
			self::$ptReg = __NAMESPACE__ . '\\PostType\\Regulation';
			self::$txReg = __NAMESPACE__ . '\\Taxonomy\\Regulation';
		}
	}

	private function set_defaults( $defaults ) {
		//
	}

	private function cleanCache() {
		if ( $cache =& $this->getCache() ) {
			if ( isset( $cache['post_type'] ) ) {
				$name = $cache['post_type'];
				$post_type = $this->prefix . $name;
				unset( $cache['post_type'] );
				self::$repositories[$name] = [ 'post_type' => $post_type, $cache ];
			} else if ( isset( $cache['taxonomy'] ) ) {
				$name = $cache['taxonomy'];
				unset( $cache['taxonomy'] );
				$object_type = $cache['object_type'];
				unset( $cache['object_type'] );
				self::$repositories[$name] = [ $this->prefix . $name, $object_type, $cache ];
			}
			$cache = [];
		}
	}

	private function &getCache() {
		static $falseVal = false;
		return self::$cache[$this->prefix] ?: $falseVal;
	}

	/**
	 * @access private
	 */
	public static function register() {
		if ( doing_action( 'init' ) ) {
			foreach ( self::$instances as $instance ) {
				$instance->cleanCache();
			}
			if ( self::$repositories ) {
				foreach ( self::$repositories as $name => $args ) {
					//
					if ( isset( $args['post_type'] ) ) {
						self::register_post_type( $name, $args );
					} else if ( isset( $args['taxonomy'] ) ) {
						self::register_taxonomy( $name, $args );
					}
				}
			}
		}
	}

	private static function register_post_type( $name, Array $args ) {
		//
	}

	private static function register_taxonomy( $name, Array $args ) {
		//
	}

}
