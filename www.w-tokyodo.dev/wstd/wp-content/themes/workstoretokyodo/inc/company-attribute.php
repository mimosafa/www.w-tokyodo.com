<?php

/**
 * Company Attribute Setting - 会社情報の設定画面
 *
 * @since 0.0
 *
 * @uses register_setting
 * @uses add_settings_section
 * @uses add_settings_field
 *
 * @link http://www.warna.info/archives/588/ WordPressの設定画面への項目追加方法まとめ
 */

// optionテーブルに保存できるようにホワイトリストに追加
function register_company_attribute_setting() {
	register_setting( 'company_attribute_group', 'company_trade_name_ja' );
	register_setting( 'company_attribute_group', 'company_trade_name_en' );
	register_setting( 'company_attribute_group', 'company_postal_code' );
	register_setting( 'company_attribute_group', 'company_address' );
	register_setting( 'company_attribute_group', 'company_phone_number' );
	register_setting( 'company_attribute_group', 'company_fax_number' );
	register_setting( 'company_attribute_group', 'company_ceo_post' );
	register_setting( 'company_attribute_group', 'company_ceo_name' );
	register_setting( 'company_attribute_group', 'company_establishment' );
	register_setting( 'company_attribute_group', 'company_capital_stock' );
	register_setting( 'company_attribute_group', 'company_main_bank' );
	register_setting( 'company_attribute_group', 'company_business' );
}
add_filter( 'admin_init', 'register_company_attribute_setting' );

add_action( 'admin_menu', function() {
	add_menu_page( '会社情報', '会社情報', 'edit_themes', 'company-attribute', 'company_attribute_page', 'dashicons-location-alt', 64 );
} );

function company_attribute_page() {
?>
<div class="wrap">
<h2>会社情報</h2>
<form method="post" action="">
<?php settings_fields( 'company_attribute_group' ); ?>
<?php do_settings_sections( 'company-attribute' ); ?>
<p class="submit">
<input type="submit" name="submit" id="submit" class="button button-primary" value="Submit" />
</p>
</form>
</div>
<?php
}

// 追加実行
function add_general_company_attribute() {
	// section
	add_settings_section( 'company_attribute', '会社情報', 'company_attribute_message', 'company-attribute' );
	// fields
	add_settings_field( 'company_trade_name_ja', '商号 (ja)', 'company_trade_name_ja_field', 'company-attribute', 'company_attribute', array( 'label_for' => 'company_trade_name_ja' ) );
	add_settings_field( 'company_trade_name_en', 'Trade Name (en)', 'company_trade_name_en_field', 'company-attribute', 'company_attribute', array( 'label_for' => 'company_trade_name_en' ) );
	add_settings_field( 'company_postal_code', '郵便番号', 'company_postal_code_field', 'company-attribute', 'company_attribute', array( 'label_for' => 'company_postal_code' ) );
	add_settings_field( 'company_address', '本社所在地', 'company_address_field', 'company-attribute', 'company_attribute', array( 'label_for' => 'company_address' ) );
	add_settings_field( 'company_phone_number', '電話番号', 'company_phone_number_field', 'company-attribute', 'company_attribute', array( 'label_for' => 'company_phone_number' ) );
	add_settings_field( 'company_fax_number', 'FAX番号', 'company_fax_number_field', 'company-attribute', 'company_attribute', array( 'label_for' => 'company_fax_number' ) );
	add_settings_field( 'company_ceo', '代表者', 'company_ceo_field', 'company-attribute', 'company_attribute', array() );
	add_settings_field( 'company_establishment', '設立', 'company_establishment_field', 'company-attribute', 'company_attribute', array( 'label_for' => 'company_establishment' ) );
	add_settings_field( 'company_capital_stock', '資本金', 'company_capital_stock_field', 'company-attribute', 'company_attribute', array( 'label_for' => 'company_capital_stock' ) );
	add_settings_field( 'company_main_bank', '取引銀行', 'company_main_bank_field', 'company-attribute', 'company_attribute', array( 'label_for' => 'company_main_bank' ) );
	add_settings_field( 'company_business', '事業内容', 'company_business_field', 'company-attribute', 'company_attribute', array( 'label_for' => 'company_business' ) );
}
add_action( 'admin_init', 'add_general_company_attribute' );

// セクション見出し部分、説明書き
function company_attribute_message() {
	// なにか説明がある場合は
}

// 日本語 会社名
function company_trade_name_ja_field( $args ) {
	$string = get_option( 'company_trade_name_ja' );
?>
<input type="text" name="company_trade_name_ja" id="company_trade_name_ja" size="30" value="<?php echo esc_html( $string ); ?>" />
<p class="description">会社名（日本語）</p>
<?php
}

// 英語 会社名
function company_trade_name_en_field( $args ) {
	$string = get_option( 'company_trade_name_en' );
?>
<input type="text" name="company_trade_name_en" id="company_trade_name_en" size="30" value="<?php echo esc_html( $string ); ?>" />
<p class="description">会社名（英語）</p>
<?php
}

// 郵便番号
function company_postal_code_field( $args ) {
	$string = get_option( 'company_postal_code' );
?>
〒<input type="text" name="company_postal_code" id="company_postal_code" size="15" value="<?php echo esc_html( $string ); ?>" />
<?php
}

// 本社所在地
function company_address_field( $args ) {
	$string = get_option( 'company_address' );
?>
<input type="text" name="company_address" id="company_address" size="40" value="<?php echo esc_html( $string ); ?>" />
<?php
}

// 電話番号
function company_phone_number_field( $args ) {
	$string = get_option( 'company_phone_number' );
?>
<input type="text" name="company_phone_number" id="company_phone_number" size="20" value="<?php echo esc_html( $string ); ?>" />
<?php
}

// FAX番号
function company_fax_number_field( $args ) {
	$string = get_option( 'company_fax_number' );
?>
<input type="text" name="company_fax_number" id="company_fax_number" size="20" value="<?php echo esc_html( $string ); ?>" />
<?php
}

// 代表者
function company_ceo_field( $args ) {
	$ceo_post = get_option( 'company_ceo_post' );
	$ceo_name = get_option( 'company_ceo_name' );
?>
<label for="company_ceo_post">役職</label>
<input type="text" name="company_ceo_post" id="company_ceo_post" size="15" value="<?php echo esc_html( $ceo_post ); ?>" />
<label for="company_ceo_name">氏名</label>
<input type="text" name="company_ceo_name" id="company_ceo_name" size="20" value="<?php echo esc_html( $ceo_name ); ?>" />
<?php
}

// 設立
function company_establishment_field( $args ) {
	$string = get_option( 'company_establishment' );
?>
<input type="text" name="company_establishment" id="company_establishment" size="20" value="<?php echo esc_html( $string ); ?>" />
<?php
}

// 資本金
function company_capital_stock_field( $args ) {
	$string = get_option( 'company_capital_stock' );
?>
<input type="text" name="company_capital_stock" id="company_capital_stock" size="20" value="<?php echo esc_html( $string ); ?>" />
<?php
}

// 取引銀行
function company_main_bank_field( $args ) {
	$string = get_option( 'company_main_bank' );
?>
<input type="text" name="company_main_bank" id="company_main_bank" size="40" value="<?php echo esc_html( $string ); ?>" />
<?php
}

// 事業内容
function company_business_field( $args ) {
	$string = get_option( 'company_business' );
?>
<textarea name="company_business" id="company_business" class="large-text" rows="5"><?php echo $string; ?></textarea>
<?php
}

/**
 * bootstrap css framework compornents
 */
function simple_bootstrap_horizontal_table( $args, $class = '', $echo = false ) {
	if ( empty( $args ) || !is_array( $args ) )
		return;
	$table  = '<table class="table ' . esc_attr( $class ) . '">' . "\n";
	$table .= "<tbody>\n";
	foreach ( $args as $key => $value ) {
		$table .= "<tr>\n";
		$table .= '<th>' . esc_html( $key ) . "</th>\n";
		$table .= '<td>' . esc_html( $value ) . "</td>\n";
		$table .= "</tr>\n";
	}
	$table .= "</tbody>\n";
	$table .= "</table>\n";
	if ( $echo )
		echo $table;
	else
		return $table;
}

/**
 * shortcode - 会社概要のテーブルを表示
 */
add_shortcode( 'company_attribute', 'the_company_attribute' );
function the_company_attribute() {
	$args = array(
		'商号' => get_option( 'company_trade_name_ja' ),
		'本社所在地' => '〒' . get_option( 'company_postal_code' ) . ' ' . get_option( 'company_address' ),
		'電話番号' => get_option( 'company_phone_number' ),
		'FAX番号' => get_option( 'company_fax_number' ),
		'代表者' => get_option( 'company_ceo_post' ) . ' ' . get_option( 'company_ceo_name' ),
		'設立' => get_option( 'company_establishment' ),
		'資本金' => get_option( 'company_capital_stock' ),
		'事業内容' => get_option( 'company_business' ),
		'取引銀行' => get_option( 'company_main_bank' )
	);
	return simple_bootstrap_horizontal_table( $args );
}
