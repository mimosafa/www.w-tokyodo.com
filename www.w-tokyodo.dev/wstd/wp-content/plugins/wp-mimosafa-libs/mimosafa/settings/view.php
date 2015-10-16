<?php
namespace mimosafa\WP\Settings;

class View {

	/**
	 * Object hash
	 *
	 * @var string
	 */
	private $hash;

	/**
	 * Arguments for Callback Functions
	 *
	 * @var array
	 */
	private $args = [
		'pages'    => [],
		'sections' => []
	];

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
