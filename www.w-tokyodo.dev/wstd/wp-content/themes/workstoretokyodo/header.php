<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1240, initial-scale=.25">
<meta name="format-detection" content="telephone=no">
<title><?php wp_title( '|', 1, 'right' ); ?>ワークストア・トウキョウドゥ</title>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php

/**
 * navigation bar (legacy) : function
 */
area_site_header_legacy();

/**
 * header image : action
 */
do_action( 'wstd_header_image' ); ?>
<div class="container" id="wstd-contents">