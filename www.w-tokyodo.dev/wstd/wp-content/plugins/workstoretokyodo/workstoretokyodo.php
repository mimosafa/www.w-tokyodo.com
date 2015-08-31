<?php
/*
Plugin Name: Workstore Tokyo Do
Author: Toshimichi Mimoto
*/

foreach ( glob( dirname( __FILE__ ) . '/files/{*.php}', GLOB_BRACE ) as $php ) {
	require $php;
}
