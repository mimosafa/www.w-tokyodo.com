<?php
namespace mimosafa\WP\Settings;

/**
 * WordPress Settings API View Class
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
class View {

	/**
	 * Instance Getter (Singleton)
	 *
	 * @access public
	 */
	public static function instance() {
		static $instance;
		return $instance ?: $instance = new self();
	}

	private function __construct() {}

	/**
	 * Settings Page Callback
	 *
	 * @global WP_Screen $current_screen
	 */
	public function page_callback() {
		global $current_screen;
		$args = $current_screen->settings_page_args;
		/**
		 * @var string  $page
		 * @var string  $title
		 * @var string  $content
		 * @var boolean $has_option_fields
		 * @var string  $file_path
		 * @var array   $include_file_args
		 */
		extract( $args, \EXTR_SKIP );
		if ( $file_path ) {
			/**
			 * File include
			 */
			extract( $include_file_args );
			include( $file_path );
		} else {
			/**
			 * Drow HTML
			 */
			?>
<div class="wrap">
	<h2><?= $title ?></h2><?php
			if ( $has_option_fields ) {
				/**
				 * @see http://wpcj.net/354
				 */
				global $parent_file;
				if ( $parent_file !== 'options-general.php' ) {
					require ABSPATH . 'wp-admin/options-head.php';
				}
			}
			echo $content;
			if ( $has_option_fields ) {
				echo '<form method="post" action="options.php">';
			}
			do_settings_sections( $page );
			if ( $has_option_fields ) {
				submit_button();
				echo '</form>';
			}
			?>
</div><?php
		}
		unset( $current_screen->settings_page_args );
	}

	/**
	 * Section Callback
	 *
	 * @global WP_Screen $current_screen
	 */
	public function section_callback( Array $section ) {
		global $current_screen;
		$args = $current_screen->settings_page_args['sections'][$section['id']];
		/**
		 * @var string $content
		 */
		extract( $args, \EXTR_SKIP );
		echo $content;
	}

	/**
	 * Field Callback
	 */
	public function field_callback( Array $args ) {
		/**
		 * @var string $id
		 * @var string $label_for
		 * @var string $title
		 * @var string $option
		 * @var string $attr_{$attr}
		 * ..
		 */
		extract( $args );
		echo isset( $content_before ) ? "<fieldset>\n" . $content_before : '';
		if ( isset( $field_callback_type ) ) {
			call_user_func( [ $this, 'field_callback_' . $field_callback_type ], $args );
		}
		echo isset( $content_after ) ? $content_after : '';
		echo isset( $content_before )|| isset( $content_after ) ? "\n<\fieldset>" : '';
	}

	/**
	 * Drow Form Element Checkbox
	 *
	 * @access private
	 *
	 * @param  array $args
	 * @return void
	 */
	private function field_callback_checkbox( Array $args ) {
		/**
		 * @var string $id
		 * @var string $option
		 * @var string $label_left
		 * @var string $label_right
		 * ..
		 */
		extract( $args );
		$label_left  = isset( $label_left )  ? $label_left  : '';
		$label_right = isset( $label_right ) ? $label_right : '';
		?>
<label for="<?= esc_attr( $id ) ?>">
	<?= $label_left ?>
	<input type="checkbox" name="<?= esc_attr( $option) ?>" id="<?= esc_attr( $id ) ?>" value="1"<?php checked( get_option( $option) ); ?>>
	<?= $label_right ?>
</label><?php
	}

	/**
	 * Drow Form Element Imput Text
	 *
	 * @access private
	 *
	 * @param  array $args
	 * @return void
	 */
	public function field_callback_text( Array $args ) {
		/**
		 * @var string $id
		 * @var int    $attr_size
		 */
		extract( $args );
		$attr = '';
		if ( $attr_size ) {
			$attr .= ' size="' . $attr_size . '"';
		}
		if ( ! $attr ) {
			$attr .= ' class="regular-text"';
		}
		?>
<input type="text" name="<?= esc_attr( $option ) ?>" id="<?= esc_attr( $id) ?>" value="<?php form_option( $option ); ?>"<?= $attr ?>><?php
	}

}
