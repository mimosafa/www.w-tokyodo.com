<?php
/**
 * Documents for Workstore Tokyo Do Theme
 *
 * @since 0.1
 */

/**
 * @see inc/rewrite-hook.php
 */
add_action( 'wstd_rewrites_managable', 'wstd_init_document' );

function wstd_init_document() {
	add_rewrite_rule( 'document/?$', 'index.php?wstd_document=', 'top' );
	add_rewrite_rule( 'document/([^/]+)/?', 'index.php?wstd_document=$matches[1]', 'top' );
}

add_filter( 'query_vars', 'wstd_document_query_vars' );

function wstd_document_query_vars( $vars ) {
	$vars[] = 'wstd_document';
	return $vars;
}

add_action( 'template_redirect', 'wstd_document_template_redirect' );

function wstd_document_template_redirect() {
	global $wp_query;
	if ( isset( $wp_query->query['wstd_document'] ) ) {
		# var_dump( $wp_query );
	}
}
