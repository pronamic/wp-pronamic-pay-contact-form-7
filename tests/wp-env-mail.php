<?php
/*
 * Plugin Name: wp-env mail
 */

/**
 * Short-circuit mail.
 * 
 * @link https://github.com/WordPress/wordpress-develop/blob/2f8f1fc795789530db31bdf020e1d96acf02a760/src/wp-includes/pluggable.php#L194-L214
 */
add_filter( 'pre_wp_mail', function( $short_circuit ) {
	return true;
} );
