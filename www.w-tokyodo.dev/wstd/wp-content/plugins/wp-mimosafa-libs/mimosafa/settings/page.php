<?php
namespace mimosafa\WP\Settings;

/**
 * WordPress Settings API Controller & Interface Class
 *
 * @access public
 *
 * @uses mimosafa\WP\Settings\View
 * @uses mimosafa\WP\Settings\Options
 *
 * @package WordPress
 * @subpackage WordPress Libraries by mimosafa
 *
 * @license GPLv2
 *
 * @author Toshimichi Mimoto <mimosafa@gmail.com>
 */
class Page {

	/**
	 * Top Level Page
	 *
	 * @var string
	 */
	private $toplevel;

	/**
	 * Structure of Settings Page
	 *
	 * @var array
	 */
	private $pages = [];

	/**
	 * @var null|int
	 */
	private $position;

	/**
	 * @var boolean
	 */
	private $replace_menu = false;

	/**
	 * @var string
	 */
	private $icon_url = '';

	/**
	 * @var array
	 */
	private $sections = [];
	private $fields   = [];
	private $settings = [];

	/**
	 * Caches
	 *
	 * @var array
	 */
	private $cache = [
		'page'    => [],
		'section' => [],
		'field'   => [],
		'option'  => []
	];

	/**
	 * Arguments for Rendering Page
	 *
	 * @var array
	 */
	private $screen_args = [];

	/**
	 * Model(Options) Class Instance
	 *
	 * @var mimosafa\WP\Settings\Options
	 */
	private $opts;

	/**
	 * View Class Instance
	 *
	 * @var mimosafa\WP\Settings\View
	 */
	private static $view;

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @param  string|mimosafa\WP\Settings\Options $opt  Optional
	 * @param  array                               $args Optional
	 * @return void
	 */
	public function __construct() {
		/**
		 * Construct View Instance if yet
		 */
		self::$view ?: self::$view = View::instance();
		if ( $args = func_get_args() ) {
			$opts = $args[0];
			/**
			 * Set Options Instance
			 *
			 * @uses mimosaffa\WP\Settings\Options::instance()
			 */
			if ( $opts instanceof Options ) {
				$this->opts = $opts;
			} else if ( $opts && is_string( $opts ) && $opts === sanitize_key( $opts ) && $opts[0] !== '_' ) {
				$this->opts = Options::instance( $opts );
			}
			/**
			 * Initialize Pages from Array
			 */
			if ( isset( $args[1] ) && $args[1] && is_array( $args[1] ) ) {
				$this->_init_pages_array( $args[1] );
			}
		}
		add_action( 'admin_menu', [ $this, '_init' ] );
	}

	/**
	 * Initialize Pages from Array
	 *
	 * @access private
	 *
	 * @param  array $args
	 * @return void
	 */
	private function _init_pages_array( Array $args ) {
		//
	}

	/**
	 * Set Page
	 *
	 * @access public
	 *
	 * @param  string $page
	 * @param  string $title      Optional
	 * @param  string $menu_title Optional
	 * @return mimosafa\WP\Settings\Page
	 */
	public function page( $page, $title = null, $menu_title = null ) {
		if ( $page = filter_var( $page ) ) {
			$this->_flush_page();
			$cache =& $this->getCache( 'page' );
			$cache['page'] = $page;
			if ( ! isset( $this->toplevel ) ) {
				$this->toplevel = $page;
			}
			if ( $title ) {
				$this->title( $title );
			}
			if ( $menu_title ) {
				$this->menu_title( $menu_title );
			}
		}
		return $this;
	}

	/**
	 * Set Section
	 *
	 * @access public
	 *
	 * @param  string $section
	 * @param  string $title Optional. if blank, string made from section_id. if want to hide set empty string ''.
	 * @return mimosafa\WP\Settings\Page
	 */
	public function section( $section, $title = null ) {
		$cache =& $this->getCache();
		if ( ! empty( $cache['page'] ) ) {
			if ( $section = filter_var( $section ) ) {
				$this->_flush_section();
				$cache['section']['section'] = $section;
				if ( is_string( $title ) ) {
					$cache['section']['title'] = $title;
				}
			}
		}
		return $this;
	}

	/**
	 * Set Field
	 *
	 * @access public
	 *
	 * @param  string $field
	 * @param  string $title Optional.
	 * @return mimosafa\WP\Settings\Page
	 */
	public function field( $field, $title = null ) {
		$cache =& $this->getCache();
		if ( ! empty( $cache['section'] ) ) {
			if ( $field = filter_var( $field ) ) {
				$this->_flush_field();
				$cache['field']['field'] = $field;
				if ( $title = filter_var( $title ) ) {
					$cache['field']['title'] = $title;
				}
			}
		}
		return $this;
	}

	/**
	 * Set Option
	 *
	 * @access public
	 *
	 * @uses   mimosafa\WP\Settings\Page::_option_name()
	 *
	 * @param  string          $option
	 * @param  string|callable $callback  Optional.
	 * @param  array           $arguments Optional.
	 * @return mimosafa\WP\Settings\Page
	 */
	public function option( $option, $callback = null, $sanitize = null ) {
		$cache =& $this->getCache();
		if ( ! empty( $cache['field'] ) ) {
			if ( $option = $this->_option_name( $option ) ) {
				$this->_flush_option();
				$cache['option']['option'] = $option;
				if ( $callback ) {
					$this->callback( $callback, $sanitize );
				} else if ( $sanitize ) {
					$this->sanitize( $sanitize );
				}
				if ( ! isset( $cache['page']['has_option_fields'] ) ) {
					$cache['page']['has_option_fields'] = true;
				}
			}
		}
		return $this;
	}

	/**
	 * Misc Properties
	 *
	 * - attr_size( int|array $size )
	 * - attr_cols( int $size )
	 * - attr_rows( int $size )
	 * - attr_class( string $class )
	 * - {$callbak}( [ callable $sanitize ] )
	 *
	 * @access public
	 *
	 * @return mimosafa\WP\Settings\Page
	 */
	public function __call( $name, $params ) {
		if ( substr( $name, 0, 5 ) === 'attr_' ) {
			$key = substr( $name, 5 );
			/**
			 * @var callable $sizeFilter
			 */
			static $sizeFilter;
			if ( ! isset( $sizeFilter ) ) {
				$sizeFilter = function( $var ) {
					return filter_var( $var, \FILTER_VALIDATE_INT, [ 'options' => [ 'min_range' => 1 ] ] );
				};
			}
			if ( $key === 'size' ) {
				/**
				 * Attribute Size for option
				 */
				if ( $cache =& $this->getCache( 'option' ) ) {
					if ( is_array( $params[0] ) && count( $params[0] ) === 2 ) {
						$size = array_map( $sizeFilter, $params[0] );
						if ( $size[0] ) {
							$cache['cols'] = $size[0];
						}
						if ( $size[1] ) {
							$cache['rows'] = $size[1];
						}
					}
					else if ( $size = $sizeFilter( $params[0] ) ) {
						$cache['size'] = $size;
					}
				}
			}
			else if ( $key === 'cols' || $key === 'rows' ) {
				/**
				 * Attribute Cols/Rows for Option (Textarea)
				 */
				if ( $cache =& $this->getCache( 'option' ) ) {
					if ( $val = $sizeFilter( $params[0] ) ) {
						$cache[$key] = $val;
					}
				}
			}
			else if ( $key === 'class' ) {
				/**
				 * Attribute Class for option, field, and page
				 */
				if ( $cache =& $this->getCurrentCache() ) {
					if ( $params[0] === sanitize_html_class( $params[0] ) ) {
						if ( isset( $cache['class'] ) ) {
							$cache['class'] .= ' ' . trim( $params[0] );
						} else {
							$cache['class'] = trim( $params[0] );
						}
					}
				}
			}
		}
		else if ( method_exists( self::$view, 'option_callback_' . $name ) ) {
			/**
			 * Option Callbacks
			 */
			if ( $cache =& $this->getCache( 'option' ) ) {
				$this->callback( $name, $params ? $params[0] : null );
			}
		}
		return $this;
	}

	/**
	 * Set Title
	 *
	 * @access public
	 *
	 * @param  string $page_title
	 * @return mimosafa\WP\Settings\Page
	 */
	public function title( $title ) {
		if ( $cache =& $this->getCurrentCache() ) {
			if ( $title = filter_var( $title ) ) {
				$cache['title'] = $title;
			}
		}
		return $this;
	}

	/**
	 * Set Page <h2> Title
	 *
	 * @access public
	 *
	 * @param  string $page_title
	 * @return mimosafa\WP\Settings\Page
	 */
	public function h2( $title ) {
		if ( $cache =& $this->getCache( 'page' ) ) {
			if ( $title = filter_var( $title ) ) {
				$cache['h2'] = $title;
			}
		}
		return $this;
	}

	/**
	 * Set Page Menu Title
	 *
	 * @access public
	 *
	 * @param  string $menu_title
	 * @return mimosafa\WP\Settings\Page
	 */
	public function menu_title( $menu_title ) {
		if ( $cache =& $this->getCache( 'page' ) ) {
			if ( $menu_title = filter_var( $menu_title ) ) {
				$cache['menu_title'] = $menu_title;
			}
		}
		return $this;
	}

	/**
	 * Set Page Capability
	 *
	 * @access public
	 *
	 * @param  string $capability
	 * @return mimosafa\WP\Settings\Page
	 */
	public function capability( $capability ) {
		if ( $cache =& $this->getCache( 'page' ) ) {
			if ( $capability = filter_var( $capability ) ) {
				$cache['capability'] = $capability;
			}
		}
		return $this;
	}

	/**
	 * Set Page Icon
	 *
	 * @param  string $icon_url
	 * @return mimosafa\WP\Settings\Page
	 */
	public function icon_url( $icon_url ) {
		$this->icon_url = filter_var( $icon_url );
		return $this;
	}

	/**
	 * Set Page Menu Position
	 *
	 * @param  integer $position
	 * @return mimosafa\WP\Settings\Page
	 */
	public function position( $position, $replace = false ) {
		$this->position = filter_var( $position, \FILTER_VALIDATE_INT, [ 'options' => [ 'default' => null ] ] );
		$this->replace_menu = filter_var( $replace, \FILTER_VALIDATE_BOOLEAN );
		return $this;
	}

	/**
	 * Set callback for option form
	 *
	 * @access public
	 *
	 * @param  string|callable $callback
	 * @param  array           $args     Optional.
	 * @return mimosafa\WP\Settings\Page
	 */
	public function callback( $callback, $sanitize = null ) {
		if ( $cache =& $this->getCurrentCache() ) {
			if ( $cache === $this->getCache( 'option' ) ) {
				if ( is_string( $callback ) && method_exists( self::$view, 'option_callback_' . $callback ) ) {
					$cache['option_callback_type'] = $callback;
				}
				if ( $sanitize ) {
					$this->sanitize( $sanitize );
				}
			}
			if ( ! isset( $cache['option_callback_type'] ) && is_callable( $callback ) ) {
				$cache['callback'] = $callback;
			}
		}
		return $this;
	}

	/**
	 * Set sanitize callback for option
	 *
	 * @access public
	 *
	 * @param  callable $sanitize
	 * @param  array    $args     Optional
	 * @return mimosafa\WP\Settings\Page
	 */
	public function sanitize( Callable $sanitize ) {
		if ( $cache =& $this->getCache( 'option' ) ) {
			$cache['sanitize'] = $sanitize;
		}
		return $this;
	}

	/**
	 * Set Option Selectable item(s)
	 *
	 * @access public
	 *
	 * @param  mixed|array $item
	 * @param  string      $label [description]
	 * @return mimosafa\WP\Settings\Page
	 */
	public function item( $item, $label = '' ) {
		if ( $cache =& $this->getCache( 'option' ) ) {
			if ( is_array( $item ) ) {
				if ( $item === array_values( $item ) ) {
					foreach ( $item as $val ) {
						if ( ! is_array( $val ) && ! is_object( $val ) ) {
							$this->item( $val );
						}
					}
				} else {
					foreach ( $item as $key => $val ) {
						if ( ! is_array( $val ) && ! is_object( $val ) ) {
							$this->item( $key, $val );
						}
					}
				}
			}
			else if ( ! isset( $cache['items'][(string) $item] ) ) {
				if ( ! isset( $cache['items'] ) ) {
					$cache['items'] = [];
				}
				$cache['items'][$item] = $label ? $label : __( ucwords( str_replace( [ '_', '-' ], ' ', $item ) ) );
			}
		}
		return $this;
	}

	/**
	 * Set Option Selectable items
	 * - Arias of item()
	 *
	 * @access public
	 *
	 * @param  array $item
	 * @return mimosafa\WP\Settings\Page
	 */
	public function items( Array $items ) {
		return $this->item( $items );
	}

	/**
	 *
	 */
	public function multiple() {
		if ( $cache =& $this->getCache( 'option' ) ) {
			$cache['multiple'] = true;
		}
		return $this;
	}

	/**
	 * Set Option Form Label
	 *
	 * @todo   rtl
	 *
	 * @access public
	 *
	 * @param  string $label
	 * @param  string $pos   Optional l|r
	 * @return mimosafa\WP\Settings\Page
	 */
	public function label( $label, $pos = null ) {
		if ( $cache =& $this->getCache( 'option' ) ) {
			if ( $label = filter_var( $label ) ) {
				if ( is_string( $pos ) && in_array( $pos, [ 'l', 'r', 'before', 'after' ], true ) ) {
					if ( $pos === 'l' ) {
						$pos = 'before';
					}
					else if ( $pos === 'r' ) {
						$pos = 'after';
					}
					$key = 'label_' . $pos;
				} else {
					$key = 'label';
				}
				$cache[$key] = $label;
			}
		}
		return $this;
	}

	/**
	 * Insert <br> Before Option Form
	 *
	 * @access public
	 *
	 * @return mimosafa\WP\Settings\Page
	 */
	public function br() {
		if ( $cache =& $this->getCache( 'option' ) ) {
			$cache['br'] = true;
		}
		return $this;
	}

	/**
	 * Wrap Option Form by <p>
	 *
	 * @access public
	 *
	 * @return mimosafa\WP\Settings\Page
	 */
	public function p() {
		if ( $cache =& $this->getCache( 'option' ) ) {
			$cache['p'] = true;
		}
		return $this;
	}

	/**
	 * Set Content
	 *
	 * @access public
	 *
	 * @param  string $content
	 * @param  string $place   Optional before|after
	 * @return mimosafa\WP\Settings\Page
	 */
	public function content( $content, $place = null ) {
		if ( $cache =& $this->getCurrentCache() ) {
			if ( $content = filter_var( $content ) ) {
				$key = 'content';
				if ( $cache === $this->getCache( 'option' ) || $cache === $this->getCache( 'field' ) ) {
					if ( $place && in_array( $place, [ 'before', 'after' ], true ) ) {
						$key .= '_' . $place;
					} else {
						$key .= $cache === $this->getCache( 'field' ) ? '_before' : '_after';
					}
				}
				if ( isset( $cache[$key] ) ) {
					$cache[$key] = $cache[$key] . "\n" . $content;
				} else {
					$cache[$key] = $content;
				}
			}
		}
		return $this;
	}

	/**
	 * Set description text
	 *
	 * @access public
	 *
	 * @param  string $text
	 * @return mimosafa\WP\Settings\Page
	 */
	public function description( $text, $place = null ) {
		if ( $cache =& $this->getCurrentCache() ) {
			if ( $text = filter_var( $text ) ) {
				if ( $cache === $this->getCache( 'option' ) ) {
					$text = '<p class="description">' . $text . '</p>';
				} else {
					$text = '<p>' . $text . '</p>';
				}
			}
			$this->content( $text, $place );
		}
		return $this;
	}

	/**
	 * @access public
	 *
	 * @param  string  $path
	 * @param  array   $args
	 * @param  boolean $wrap_div Optional. if true wrap $html by 'div'
	 * @return mimosafa\WP\Settings\Page
	 */
	public function file( $path, $args = [] ) {
		if ( $cache =& $this->getCache( 'page' ) ) {
			if ( $path == realpath( $path ) ) {
				$cache['file_path'] = $path;
				$cache['include_file_args'] = (array) $args;
			}
		}
		return $this;
	}

	/**
	 * Set submit button
	 *
	 * @todo
	 *
	 * @access public
	 *
	 * @param  string $text
	 * @return mimosafa\WP\Settings\Page
	 */
	public function submit_button( $text ) {
		//
		return $this;
	}

	/**
	 * @access private
	 *
	 * @return (void)
	 */
	public function _init() {
		$this->_flush_page();
		$this->_add_pages();
		add_action( 'admin_init', [ $this, '_add_settings' ] );
	}

	/**
	 * Initialize static cache $page
	 *
	 * @access private
	 * @uses   mimosafa\WP\Settings\Controller::addPageArgs()
	 */
	private function _flush_page() {
		$this->_flush_section();
		if ( $cache =& $this->getCache( 'page' ) ) {
			$this->pages[] = $cache;
		}
		$cache = [];
	}

	/**
	 * Initialize static cache $section
	 *
	 * @access private
	 */
	private function _flush_section() {
		$this->_flush_field();
		$cache =& $this->getCache();
		if ( $cache['section'] ) {
			if ( ! isset( $cache['page']['sections'] ) ) {
				$cache['page']['sections'] = [];
			}
			$cache['page']['sections'][] = $cache['section'];
		}
		$cache['section'] = [];
	}

	/**
	 * Initialize static cache $field
	 *
	 * @access private
	 */
	private function _flush_field() {
		$this->_flush_option();
		$cache =& $this->getCache();
		if ( $cache['field'] ) {
			if ( ! isset( $cache['section']['fields'] ) ) {
				$cache['section']['fields'] = [];
			}
			$cache['section']['fields'][] = $cache['field'];
		}
		$cache['field'] = [];
	}

	/**
	 * Initialize static cache $option
	 *
	 * @access private
	 */
	private function _flush_option() {
		$cache =& $this->getCache();
		if ( $cache['option'] ) {
			if ( ! isset( $cache['field']['option'] ) ) {
				$cache['field']['option'] = [];
			}
			$cache['field']['options'][] = $cache['option'];
		}
		$cache['option'] = [];
	}

	/**
	 * Option Full Name
	 *
	 * @access private
	 *
	 * @uses   mimosafa\WP\Settings\Options
	 *
	 * @param  string $option
	 * @return string|boolean
	 */
	private function _option_name( $option ) {
		if ( $option = filter_var( $option ) ) {
			if ( $this->opts ) {
				$option = $this->opts->$option;
			}
			return $option;
		}
		return false;
	}

	/**
	 * Get caches of $this
	 *
	 * @access private
	 *
	 * @return &array
	 */
	private function &getCache( $key = null ) {
		if ( ! $key || ! in_array( $key, [ 'page', 'section', 'field', 'option' ] ) ) {
			return $this->cache;
		}
		return $this->cache[$key];
	}

	/**
	 * Get end of caches
	 *
	 * @access private
	 *
	 * @return &array|&boolean
	 */
	private function &getCurrentCache() {
		$falseVal = false;
		$cache =& $this->getCache();
		if ( $cache['option'] ) {
			return $cache['option'];
		}
		else if ( $cache['field'] ) {
			return $cache['field'];
		}
		else if ( $cache['section'] ) {
			return $cache['section'];
		}
		else if ( $cache['page'] ) {
			return $cache['page'];
		}
		return $falseVal;
	}

	/**
	 * Add Pages
	 *
	 * @access private
	 */
	public function _add_pages() {
		foreach ( $this->pages as $page ) {
			$this->_add_page( $page );
		}
		do_action( $this->toplevel . '_added_pages' );
	}

	/**
	 * Add Page
	 *
	 * @access private
	 *
	 * @global array $admin_page_hook
	 *
	 * @param  array $page_args
	 * @return void
	 */
	private function _add_page( Array $args ) {
		global $admin_page_hooks;
		/**
		 * @var string   $page
		 * @var string   $title             Optional
		 * @var string   $h2                Optional
		 * @var string   $menu_title        Optional
		 * @var string   $capability        Optional
		 * @var callable $callback          Optional
		 * @var array    $sections          Optional
		 * @var string   $content           Optional
		 * @var boolean  $has_option_fields Optional
		 * @var string   $file_path         Optional
		 * @var array    $include_file_args Optional
		 */
		extract( $args, \EXTR_SKIP );
		if ( array_key_exists( $page, $admin_page_hooks ) ) {
			/**
			 * Avoiding Duplicated Page
			 */
			return;
		}
		if ( ! isset( $title ) ) {
			$title = __( ucwords( trim( str_replace( [ '-', '_', '/', '.php' ], ' ', $page ) ) ) );
		}
		$args['title'] = isset( $h2 ) ? $h2 : $title;
		$title = esc_html( $title );
		if ( ! isset( $menu_title ) ) {
			$menu_title = $title;
		}
		if ( ! isset( $capability ) ) {
			$capability = 'manage_options';
		}
		if ( ! isset( $callback ) ) {
			if ( $page === $this->toplevel && count( $this->pages ) > 1
				&& ! isset( $sections ) && ! isset( $content ) && ! isset( $file_path ) )
			{
				$callback = '';
				add_action( $this->toplevel . '_added_pages', function() {
					/**
					 * No Page Body (Remove Self from Submenus)
					 */
					remove_submenu_page( $this->toplevel, $this->toplevel );
				} );
			}
			else {
				$callback = [ self::$view, 'page_callback' ];
			}
		}
		if ( $page === $this->toplevel ) {
			if ( isset( $this->position ) && ! $this->replace_menu ) {
				global $menu;
				$positions = array_keys( $menu );
				for ( $this->position; true; $this->position++ ) {
					if ( ! in_array( $this->position, $positions, true ) ) {
						break;
					}
				}
			}
			/**
			 * Add as top level page
			 */
			$load_page = add_menu_page( $title, $menu_title, $capability, $page, $callback, $this->icon_url, $this->position );
		} else {
			/**
			 * Add as sub page
			 */
			$load_page = add_submenu_page( $this->toplevel, $title, $menu_title, $capability, $page, $callback );
		}
		/**
		 * Store Args
		 */
		$this->screen_args[$load_page] = self::_page_callback_args( $args );
		if ( isset( $sections ) && $sections ) {
			/**
			 * Sections
			 */
			foreach ( $sections as $section ) {
				$this->_add_section( $section, $page, $load_page );
			}
		}
		add_action( 'load-' . $load_page, [ $this, '_init_screen' ] );
	}

	/**
	 * @access private
	 *
	 * @param  array $args
	 * @return array {
	 *     @type string  $page
	 *     @type string  $title
	 *     @type string  $content
	 *     @type boolean $has_option_fields
	 *     @type string  $file_path
	 *     @type array   $include_file_args
	 *     @type string  $class
	 * }
	 */
	private static function _page_callback_args( Array $args ) {
		$return = [];
		$return['page']              = $args['page'];
		$return['title']             = $args['title'];
		$return['content']           = isset( $args['content'] ) ? $args['content'] : '';
		$return['has_option_fields'] = isset( $args['has_option_fields'] ) ? $args['has_option_fields'] : false;
		$return['file_path']         = isset( $args['file_path'] ) ? $args['file_path'] : '';
		$return['include_file_args'] = isset( $args['include_file_args'] ) ? $args['include_file_args'] : [];
		$return['class']             = isset( $args['class'] ) ? $args['class'] : '';
		return $return;
	}

	/**
	 * @access private
	 *
	 * @global WP_Screen $current_screen
	 */
	public function _init_screen() {
		global $current_screen;
		if ( empty( $current_screen ) ) {
			set_current_screen();
		}
		$current_screen->settings_page_args = $this->screen_args[$current_screen->base];
		/**
		 * Stylesheet, JavaScripts
		 */
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Add Section
	 *
	 * @access private
	 *
	 * @param  array  $args
	 * @param  string $page
	 * @param  string $load_page
	 * @return void
	 */
	private function _add_section( Array $args, $page, $load_page ) {
		/**
		 * @var string   $section
		 * @var string   $title    Optional
		 * @var callable $callback Optional
		 * @var array    $fields   Optional
		 * @var string   $content  Optional
		 */
		extract( $args, \EXTR_SKIP );
		if ( ! isset( $title ) ) {
			$title = null;
		}
		if ( ! isset( $callback ) ) {
			$callback = [ self::$view, 'section_callback' ];
		}
		$this->sections[] = [ $section, $title, $callback, $page ];
		if ( isset( $fields ) && $fields ) {
			foreach ( $fields as $field ) {
				$this->_add_field( $field, $page, $section );
			}
		}
		/**
		 * Store Args
		 */
		$this->screen_args[$load_page]['sections'][$section] = self::_section_callback_args( $args );
	}

	/**
	 * @access private
	 *
	 * @param  array $args
	 * @return array {
	 *     @type string $content
	 * }
	 */
	private static function _section_callback_args( Array $args ) {
		$return = [];
		$return['content'] = isset( $args['content'] ) ? $args['content'] : '';
		return $return;
	}

	/**
	 * Add Field
	 *
	 * @access private
	 *
	 * @param  array  $args
	 * @param  string $page
	 * @param  string $section
	 * @return void
	 */
	private function _add_field( Array $args, $page, $section ) {
		/**
		 * @var string $field
		 * @var string $title                  Optional
		 * @var string $options                Optional
		 * @var string $content_{before|after} Optional
		 * ..
		 */
		extract( $args, \EXTR_SKIP );
		if ( ! isset( $title ) ) {
			$title = __( ucwords( str_replace( [ '-', '_' ], ' ', $field ) ) );
		}
		if ( ! isset( $callback ) ) {
			$callback = [ self::$view, 'field_callback' ];
		} else {
			/**
			 * Optimize Vars
			 */
			unset( $args['callback'] );
		}
		if ( isset( $options ) && $options ) {
			if ( count( $options ) === 1 ) {
				$args['label_for'] = $options[0]['option'];
			}
			else if ( count( $options ) > 1 ) {
				$args['label_for'] = $field;
			}
			$option_group = 'group_' . $page;
			foreach ( $options as $option ) {
				$this->_add_option( $option, $option_group );
			}
		}
		$args['page'] = $page;
		$args['section'] = $section;
		$this->fields[] = [ $field, $title, $callback, $page, $section, $args ];
	}

	/**
	 * Add Option
	 *
	 * @access private
	 *
	 * @param  array  $args
	 * @param  string $option_group
	 */
	private function _add_option( Array $args, $option_group ) {
		/**
		 * @var string   $option
		 * @var callable $sanitize
		 */
		extract( $args );
		$this->settings[] = [ $option_group, $option, isset( $sanitize ) ? $sanitize : '' ];
	}

	/**
	 * Setting Sections & Fields & Options
	 *
	 * @access private
	 *
	 * @uses   add_settings_section
	 * @uses   add_settings_add_settings_field
	 * @uses   register_setting
	 */
	public function _add_settings() {
		foreach ( $this->sections as $section_arg ) {
			call_user_func_array( 'add_settings_section', $section_arg );
		}
		foreach ( $this->fields as $field_arg ) {
			call_user_func_array( 'add_settings_field', $field_arg );
		}
		foreach ( $this->settings as $setting_arg ) {
			call_user_func_array( 'register_setting', $setting_arg );
		}
	}

	/**
	 * Enqueue Stylesheets & JavaScripts
	 *
	 * @access private
	 */
	public function enqueue_scripts() {
		$src = plugins_url( 'src', __FILE__ );
		$ver = defined( 'WP_MIMOSAFA_LIBS_VER' ) ? WP_MIMOSAFA_LIBS_VER : '';
		if ( ! wp_style_is( 'mimosafa_admin_form', 'registered' ) ) {
			wp_register_style( 'mimosafa_admin_form', $src . '/css/admin-form.css', [], $ver, 'screen' );
		}
		wp_enqueue_style( 'mimosafa_admin_form' );
	}

}
