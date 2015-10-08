<?php
namespace mimosafa;

/**
 * PHP Auto Class Loader
 *
 * @author  Toshimichi Mimoto <mimosafa@gmail.com>
 * @license GPLv2
 */
class ClassLoader {

	/**
	 * @var string
	 */
	private $namespace;
	private $path;

	/**
	 * Namespace separater
	 */
	private $nsSep = '\\';

	/**
	 * ClassLoader Options
	 *
	 * - Hyphenate Classname
	 * - e.g.
	 *   Name_space\Class_Name
	 *   => Name_space/Class-Name.php
	 *
	 * @var boolean
	 */
	private $cnHyphenate = false;

	/**
	 * - Hyphenate Namespace
	 * - e.g.
	 *   Name_space\Class_Name
	 *   => Name-space/Class_Name.php
	 *
	 * @var boolean
	 */
	private $nsHyphenate = false;

	/**
	 * - Decamelize Classname
	 * - e.g.
	 *   NameSpace\ClassName.php
	 *   => NameSpace/Class_Name.php
	 *
	 * @todo
	 *
	 * @var boolean
	 */
	private $cnDecamelize = false;

	/**
	 * - Decamelize Namespace
	 * - e.g.
	 *   NameSpace\ClassName.php
	 *   => Name_Space/ClassName.php
	 *
	 * @todo
	 *
	 * @var boolean
	 */
	private $nsDecamelize = false;

	/**
	 * - Add prefix to filename
	 * - e.g.
	 *   Namespace\ClassName
	 *   => Namespace/class-ClassName.php
	 *
	 * @todo
	 *
	 * @var string
	 */
	private $filePrefix = '';

	/**
	 * Interface of registering ClassLoader
	 *
	 * @access public
	 *
	 * @param  string $namespace
	 * @param  string $path
	 * @param  null|array $options Optional
	 */
	public static function register( $namespace, $path, $options = null, $prepend = false ) {
		$self = new self( $namespace, $path, $options );
		if ( $self->path )
			$self->_autoload_register( $prepend );
	}

	/**
	 * Constructor
	 *
	 * @access private
	 *
	 * @param  string $namespace
	 * @param  string $path
	 * @param  null|array $options
	 */
	private function __construct( $namespace, $path, $options ) {
		if ( ! filter_var( $namespace ) || ! $path = realpath( $path ) )
			return;
		$this->namespace = $namespace;
		$this->path = rtrim( $path, '/' ) . '/';
		if ( is_array( $options ) && $options )
			$this->_set_options( $options );
	}

	/**
	 * @access private
	 *
	 * @param  array $options {
	 *     @type boolean $hyphenate_classname
	 *     @type boolean $hyphenate_namespace
	 *     @type boolean $decamelize_classname
	 *     @type boolean $decamelize_namespace
	 *     @type string  $file_prefix
	 * }
	 */
	private function _set_options( Array $options ) {
		static $def;
		if ( ! $def ) {
			$boolFilter = [ 'filter' => \FILTER_VALIDATE_BOOLEAN, 'flags' => \FILTER_NULL_ON_FAILURE ];
			$def = [
				'hyphenate_classname'  => $boolFilter,
				'hyphenate_namespace'  => $boolFilter,
				'decamelize_classname' => $boolFilter,
				'decamelize_namespace' => $boolFilter,
				'file_prefix' => [ 'filter' => \FILTER_VALIDATE_REGEXP, 'options' => [ 'regexp' => '/\A[a-z][a-z0-9_\-]*\z/' ] ],
			];
		}
		$options = filter_var_array( $options, $def );
		extract( $options );
		if ( isset( $hyphenate_classname ) )
			$this->cnHyphenate = $hyphenate_classname;
		if ( isset( $hyphenate_namespace ) )
			$this->nsHyphenate = $hyphenate_namespace;
		if ( isset( $decamelize_classname ) )
			$this->cnDecamelize = $decamelize_classname;
		if ( isset( $decamelize_namespace ) )
			$this->nsDecamelize = $decamelize_namespace;
		if ( isset( $file_prefix ) )
			$this->filePrefix = $file_prefix;
	}

	/**
	 * Autoloader register
	 *
	 * @access private
	 */
	private function _autoload_register() {
		spl_autoload_register( [ &$this, 'loadClass' ] );
	}

	/**
	 * Autoloader
	 */
	public function loadClass( $class ) {
		$sep = $this->nsSep;
		if ( $this->namespace . $sep !== substr( $class, 0, strlen( $this->namespace . $sep ) ) )
			return;
		$class = substr( $class, strlen( $this->namespace ) + 1 );
		$file = '';
		if ( 0 < $lastNsPos = strripos( $class, $sep ) ) {
			$subNs = substr( $class, 0, $lastNsPos );
			$class = substr( $class, $lastNsPos + 1 );
			if ( $this->nsDecamelize ) {
				// @todo
			}
			if ( $this->nsHyphenate )
				list( $search, $replace ) = [ [ '_', $sep ], [ '-', '/' ] ];
			else
				list( $search, $replace ) = [ $sep, '/' ];
			$file = str_replace( $search, $replace, $subNs ) . '/';
		}
		if ( $this->cnDecamelize ) {
			// @todo
		}
		$file .= $this->filePrefix;
		$file .= $this->cnHyphenate ? str_replace( '_', '-', $class ) : $class;
		$file  = $this->path . $file . '.php';
		if ( file_exists( $file ) )
			require_once $file;
	}

}
