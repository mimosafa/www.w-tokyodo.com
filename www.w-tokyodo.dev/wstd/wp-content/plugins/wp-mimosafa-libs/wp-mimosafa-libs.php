<?php
/*
Plugin Name: WordPress Libraries by mimosafa
Author: Toshimichi Mimoto
*/

namespace mimosafa;

require_once 'mimosafa/classloader.php';
ClassLoader::register( 'mimosafa\\WP', __DIR__ . '/mimosafa' );

WP\Repository\Taxonomy\Extension::init();
