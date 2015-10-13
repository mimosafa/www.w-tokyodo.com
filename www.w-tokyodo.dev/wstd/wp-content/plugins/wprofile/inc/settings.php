<?php
namespace WProfile;
class Settings extends Base {
	protected $order = 9999;
	protected function init() {
		$org_profile = $this->opts->get_activate_org_profile();
		if ( $org_profile ) {
			define( 'WPROFILE_MENU_ID', 'wprofile' );
		}
		if ( $org_profile ) {
			if ( $adminmenu = $this->opts->get_use_orgname_adminmenu() ) {
				if ( $adminmenu === 'org_abbr' && ( $abbr = $this->opts->get_org_abbr() ) ) {
					define( 'WPROFILE_MENU_TITLE', $abbr );
				} else if ( $adminmenu === 'org_name' && ( $name = $this->opts->get_org_name() ) ) {
					define( 'WPROFILE_MENU_TITLE', $name );
				}
			}
			Organization::getInstance();
		}
	}
	protected function define_options() {
		$this->features_options();
		$this->org_options();
		$this->add_filters();
	}
	private function features_options() {
		$this->opts
			->add( 'activate_org_profile', 'boolean' )
		;
	}
	private function org_options() {
		$this->opts
			->add( 'org_name' )
			->add( 'org_abbr' )
			->add( 'use_orgname_adminmenu' )
		;
	}
	private function add_filters() {
		add_filter( 'wprofile_option_pre_update_option_use_orgname_adminmenu', [ $this, 'pre_update_use_orgname_adminmenu' ], 10, 2 );
	}
	public function settings_page( $page ) {
		if ( WPROFILE_MENU_ID === 'wprofile' ) {
			$page->init( 'wprofile-settings', __( 'Settings' ) );
		} else {
			$page->page_title( __( 'Settings' ) );
		}
		if ( $this->opts->get_activate_org_profile() ) {
			$page
				->section( 'org-settings', __( 'Organization Settings', 'wprofile' ) )
					->field( 'org-name', __( 'Organization Name', 'wprofile' ) )
						->option( $this->opts->org_name, 'text', 'esc_html' )
						->size( 80 )
					->field( 'org-abbr', __( 'Organization Abbreviation' ) )
						->option( $this->opts->org_abbr, 'text', 'esc_html' )
						->size( 30 )
					->field( 'use-orgname-adminmenu', __( 'Admin Menu Title', 'wprofile' ) )
						->option( $this->opts->use_orgname_adminmenu, [ $this, 'radio_adminmenu' ] )
			;
		}
		$page
			->section( 'available-features', __( 'Available Features' ) )
				->field( 'org-profile', __( 'Organization Profile' ) )
					->option( $this->opts->activate_org_profile, 'checkbox' )
		;
		return $page;
	}
	public function radio_adminmenu( $args ) {
		extract( $args );
		$val = get_option( $option );
?>
<fieldset>
	<label>
		<input type="radio" name="<?= $option ?>" value=""<?php checked( $val, false ); ?>>
		<?php _e( 'WProfile (Default)', 'wprofile' ); ?>
	</label>
<?php 
		if ( $abbr = $this->opts->get_org_abbr() ) {
			$abbrLbl = sprintf( __( '%s <small>(Organization Abbreviation)</small>', 'wprofile' ), $abbr );
?>
	<br>
	<label>
		<input type="radio" name="<?= $option ?>" value="org_abbr"<?php checked( $val, 'org_abbr' ); ?>>
		<?= $abbrLbl ?>
	</label>
<?php
		}
		if ( $name = $this->opts->get_org_name() ) {
			$nameLbl = sprintf( __( '%s <small>(Organization Name)</small>', 'wprofile' ), $name );
?>
	<br>
	<label>
		<input type="radio" name="<?= $option ?>" value="org_name"<?php checked( $val, 'org_name' ); ?>>
		<?= $nameLbl ?>
	</label>
<?php
		}
?>
</fieldset>
<?php
	}
	public function pre_update_use_orgname_adminmenu( $value, $old_value ) {
		if ( ! $value || ! in_array( $value, [ 'org_abbr', 'org_name' ], true ) )
			return false;
		if ( $value === 'org_abbr' && ! $this->opts->get_org_abbr() )
			return false;
		if ( $value === 'org_name' && ! $this->opts->get_org_name() )
			return false;
		return $value;
	}
}
