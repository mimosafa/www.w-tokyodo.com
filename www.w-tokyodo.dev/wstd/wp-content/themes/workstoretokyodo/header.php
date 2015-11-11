<?php
/**
 * Workstore Tokyo Do Header.PHP
 *
 * @since 0.0
 *
 * @uses wstd_header_logo()
 * @uses wstd_header_contact_uri()
 * @uses wstd_header_site_id()
 * @uses wstd_header_division_nav_lists()
 *
 * @see  inc/views.php
 */
?><!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1240, initial-scale=.25">
<meta name="format-detection" content="telephone=no">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div id="areaSiteHeader">
<div class="container">
<div id="wstdLogo">
<?php wstd_header_logo(); ?>
</div>
<ul id="wstdCompanyNav">
<li><a href="/recruit"><i class="fa fa-child"></i> 採用情報</a></li>
<li><a href="/#company"><i class="fa fa-building-o"></i> 会社案内</a></li>
<li><a href="<?php wstd_header_contact_uri(); ?>"><i class="fa fa-envelope"></i> お問い合わせ</a></li>
</ul>
</div>
</div><!-- /#areaSiteHeader -->
<div id="header-legacy">
<div class="container">
<div id="siteid">
<?php wstd_header_site_id(); ?>
</div>
<ul>
<?php wstd_header_division_nav_lists(); ?>
</ul>
</div>
</div><!-- /.header-legacy -->
<?php
/**
* header image : action
*/
do_action( 'wstd_header_image' ); ?>
<div class="container" id="wstd-contents">
