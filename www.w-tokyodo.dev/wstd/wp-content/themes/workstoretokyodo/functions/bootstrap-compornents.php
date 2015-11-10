<?php

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