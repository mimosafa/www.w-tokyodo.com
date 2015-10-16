<?php
namespace mimosafa\WP\Settings;

/**
 * WordPress Settings API controller class
 *
 * @access private
 *
 * @package WordPress
 * @subpackage WordPress Libraries by mimosafa
 *
 * @license GPLv2
 *
 * @author Toshimichi Mimoto <mimosafa@gmail.com>
 */
class Controller {

	/**
	 * Object hash
	 *
	 * @var string
	 */
	private $hash;

	/**
	 * Top level page
	 *
	 * @var string
	 */
	private $toplevel;

	/**
	 * Structure of settings page
	 *
	 * @var array
	 */
	private $pages = [];

	/**
	 * @var array
	 */
	private $sections = [];
	private $fields   = [];
	private $settings = [];

	/**
	 * Arguments for Callback Functions
	 *
	 * @var array
	 */
	private static $arguments = [];

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @param  string $object_hash
	 */
	public function __construct( $object_hash ) {
		$this->hash = $object_hash;
	}

	public function defineToplevelPage( $toplevel, $hash ) {
		if ( ! isset( $this->toplevel ) && $hash === $this->hash ) {
			$this->toplevel = $toplevel;
		}
	}

	public function addPageArgs( Array $page, $hash ) {
		if ( $hash === $this->hash ) {
			$this->pages[] = $page;
		}
	}

	public function init() {
		if ( $this->pages ) {
			add_action( 'admin_menu', [ &$this, '_add_pages' ] );
			add_action( 'admin_init', [ &$this, '_add_settings' ] );
		}
	}

	/**
	 * Add pages
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
	 * Add page
	 *
	 * @access private
	 *
	 * @global array $admin_page_hook
	 *
	 * @param  array $page_args
	 * @return void
	 */
	private function _add_page( Array $page_args ) {
		global $admin_page_hooks;
		extract( $page_args, \EXTR_SKIP );
		if ( array_key_exists( $page, $admin_page_hooks ) ) {
			/**
			 * Avoiding Duplicated Page
			 */
			return;
		}
		if ( ! isset( $title ) ) {
			$title = __( ucwords( trim( str_replace( [ '-', '_', '/', '.php' ], ' ', $page ) ) ) );
			$page_args['title'] = $title;
		}
		if ( ! isset( $menu_title ) ) {
			$menu_title = $title;
			$page_args['menu_title'] = $menu_title;
		}
		if ( ! isset( $capability ) ) {
			$capability = 'manage_options';
			$page_args['capability'] = $capability;
		}
		if ( ! isset( $callback ) ) {
			if ( isset( $sections ) || isset( $html ) || isset( $description ) ) {
				$callback = [ &$this, 'page_body' ];
			} else if ( $page === $this->toplevel && count( $this->pages ) > 1 ) {
				$callback = '';
				add_action( $this->toplevel . '_added_pages', function() {
					/**
					 * No Page Body (Remove Self from Submenus)
					 */
					remove_submenu_page( $this->toplevel, $this->toplevel );
				} );
			} else {
				$callback = [ &$this, 'empty_page' ];
			}
		} else {
			unset( $page_args['callback'] ); // Optimize vars
		}
		if ( $page === $this->toplevel ) {
			if ( ! isset( $icon_url ) ) {
				$icon_url = '';
			}
			if ( ! isset( $position ) ) {
				$position = null;
			}
			/**
			 * Add as top level page
			 */
			$load_page = add_menu_page( $title, $menu_title, $capability, $page, $callback, $icon_url, $position );
		} else {
			/**
			 * Add as sub page
			 */
			$load_page = add_submenu_page( $this->toplevel, $title, $menu_title, $capability, $page, $callback );
		}
		if ( isset( $provision ) ) {
			add_action( 'load-' . $load_page, $provision );
		}
		if ( isset( $sections ) && $sections ) {
			foreach ( $sections as $section ) {
				$this->_add_section( $section, $page );
			}
			unset( $page_args['sections'] ); // Optimize vars
		}
		/**
		 * Cache argument for callback method
		 */
		$argsKey = $this->hash . '_page_' . $page;
		self::$arguments[$argsKey] = $page_args;
	}

	/**
	 * Add section
	 *
	 * @access private
	 *
	 * @param  array  $section
	 * @param  string $menu_slug
	 * @return void
	 */
	private function _add_section( Array $section, $menu_slug ) {
		extract( $section, \EXTR_SKIP );
		if ( ! isset( $title ) ) {
			$title = null;
		}
		if ( ! isset( $callback ) ) {
			$callback = [ &$this, 'section_body' ];
		} else {
			unset( $section['callback'] ); // Optimize vars
		}
		$this->sections[] = [ $id, $title, $callback, $menu_slug ];
		if ( isset( $fields ) && $fields ) {
			foreach ( $fields as $field ) {
				$this->_add_field( $field, $menu_slug, $id );
			}
			unset( $section['fields'] ); // Optimize vars
		}
		/**
		 * Cache argument for callback method
		 */
		$argsKey = $this->hash . '_section_' . $id;
		self::$arguments[$argsKey] = $section;
	}

	/**
	 * Add & set field
	 *
	 * @access private
	 *
	 * @param  array  $field
	 * @param  string $menu_slug
	 * @param  string $section_id Optional.
	 * @return void
	 */
	private function _add_field( Array $field, $menu_slug, $section_id ) {
		extract( $field, \EXTR_SKIP );
		if ( ! isset( $title ) ) {
			$title = __( ucwords( str_replace( [ '-', '_' ], ' ', $id ) ) );
			$field['title'] = $title;
		}
		if ( ! isset( $callback ) ) {
			$callback = [ &$this, 'field_body' ];
		} else {
			unset( $field['callback'] ); // Optimize vars
		}
		if ( isset( $option ) ) {
			$option_group = 'group_' . $menu_slug;
			if ( ! isset( $sanitize ) || ( ! method_exists( __CLASS__, $sanitize ) && ! is_callable( $sanitize ) ) ) {
				$sanitize = '';
			} else if ( isset( $sanitize ) ) {
				unset( $field['sanitize'] ); // Optimize vars
			}
			$this->settings[] = [ $option_group, $option, $sanitize ];
		}
		$this->fields[] = [ $id, $title, $callback, $menu_slug, $section_id, $field ];
	}

	/**
	 * Setting sections & fields method
	 *
	 * @access private
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
	 * Drow default page html (if has form)
	 * 
	 * @return void
	 */
	public function page_body() {
		$menu_slug = filter_input( \INPUT_GET, 'page' );
		if ( ! $args = self::$arguments[$this->hash . '_page_' . $menu_slug] ) {
			return;
		}
		extract( $args, \EXTR_SKIP );
		echo '<div class="wrap">';
		echo "<h2>{$title}</h2>";
		if ( isset( $has_option_fields ) ) {
			/**
			 * @see http://wpcj.net/354
			 */
			global $parent_file;
			if ( $parent_file !== 'options-general.php' ) {
				require ABSPATH . 'wp-admin/options-head.php';
			}
		}
		echo isset( $description ) ? $description : '';
		echo isset( $html ) ? $html : '';
		if ( isset( $has_option_fields ) ) {
			echo '<form method="post" action="options.php">';
			settings_fields( 'group_' . $menu_slug );
		}
		do_settings_sections( $menu_slug );
		if ( isset( $has_option_fields ) ) {
			submit_button();
			echo '</form>';
		}
		echo '</div>';
	}

	/**
	 * @todo ?
	 */
	public function empty_page() {
		//
	}

	/**
	 *
	 */
	public function include_file() {
		$menu_slug = filter_input( \INPUT_GET, 'page' );
		if ( ! $args = self::$arguments[$this->hash . '_page_' . $menu_slug] ) {
			return;
		}
		extract( $args, \EXTR_SKIP );
		$_path = $file_path;
		$_wrap = $wrap_included_file;
		$title = $wrap && isset( $title ) ? $title : '';
		if ( isset( $include_file_args ) ) {
			extract( $include_file_args, \EXTR_SKIP );
		}
		echo $_wrap ? '<div class="wrap">' : '';
		echo $title ? '<h2>' . $title . '</h2>' : '';
		include $_path;
		echo $_wrap ? '</div>' : '';
	}

	/**
	 * @param  array $array
	 */
	public function section_body( Array $array ) {
		$arg = self::$arguments[$this->hash . '_section_' . $array['id']];
		echo isset( $arg['description'] ) ? $arg['description'] : '';
		echo isset( $arg['html'] ) ? $arg['html'] : '';
	}

	/**
	 * @param  array $array
	 */
	public function field_body( Array $arg ) {
		echo isset( $arg['description'] ) ? $arg['description'] : '';
		echo isset( $arg['html'] ) ? $arg['html'] : '';
	}

	/**
	 * Drow form element Checkbox
	 *
	 * @access private
	 *
	 * @param  array $args
	 * @return void
	 */
	public function callback_checkbox( Array $args ) {
		static $def = [
			'id'          => \FILTER_DEFAULT,
			'option'      => \FILTER_DEFAULT,
			'label'       => \FILTER_DEFAULT,
			'description' => \FILTER_DEFAULT,
			'html'        => \FILTER_DEFAULT,
		];
		$args = filter_var_array( $args, $def );
		extract( $args, \EXTR_SKIP );
		if ( $id && $option ) {
?>
<label for="<?= esc_attr( $id ) ?>">
	<input type="checkbox" name="<?= esc_attr( $option) ?>" id="<?= esc_attr( $id ) ?>" value="1"<?php checked( get_option( $option) ); ?>>
	<?= $label ?>
</label>
<?= $description ?>
<?= $html ?>
<?php
		}
	}

	public function callback_checkboxes( Array $args ) {
		//
	}

	/**
	 * Drow form element Imput Text
	 *
	 * @access private
	 *
	 * @param  array $args
	 * @return void
	 */
	public function callback_text( Array $args ) {
		static $def = [
			'id'          => \FILTER_DEFAULT,
			'option'      => \FILTER_DEFAULT,
			'attr_size'   => [ 'filter' => \FILTER_VALIDATE_INT, 'options' => [ 'min_range' => 1 ] ],
			'description' => \FILTER_DEFAULT,
			'html'        => \FILTER_DEFAULT
		];
		$args = filter_var_array( $args, $def );
		extract( $args, \EXTR_SKIP );
		if ( $id && $option ) {
			$attr = '';
			if ( $attr_size ) {
				$attr .= ' size="' . $attr_size . '"';
			}
			if ( ! $attr ) {
				$attr .= ' class="regular-text"';
			}
?>
<input type="text" name="<?= esc_attr( $option ) ?>" id="<?= esc_attr( $id) ?>" value="<?php form_option( $option ); ?>"<?= $attr ?>>
<?= $description ?>
<?= $html ?>
<?php
		}
	}

}
