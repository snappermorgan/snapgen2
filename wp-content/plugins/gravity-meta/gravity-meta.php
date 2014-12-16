<?php
/*
Plugin Name: Gravity Meta
Plugin URI: http://www.barbadospropertylist.com
Description: A simple plugin to add query string params Gravity Form dynamic fields. Latest WP version and PHP 5.3.0 required.
Version: 1.2
Author: barbadospropertylist
Author URI: http://www.barbadospropertylist.com
License: GPLv2
*/
?>
<?php
//
// Codes for grabbing query string params, parseing them into name value pairs and storing them in a session array called gmeta_query_array
//
if ( !is_admin() ) {
	// Check if query string is being used
	if ( isset( $_SERVER['QUERY_STRING'] ) && !empty($_SERVER['QUERY_STRING']) ) {
		// Create an array of query string variables
		$gmeta_query_string = $_SERVER['QUERY_STRING'];
		parse_str( $gmeta_query_string, $gmeta_query_array );
		// Add query string array params to cookie
		if ( isset( $_COOKIE['gmeta_cookie'] ) ) {
			// If cookie has been set merge new data
			$gmeta_cookie = unserialize( urldecode( $_COOKIE['gmeta_cookie'] ) );
			$gmeta_merge_array = array_merge( $gmeta_cookie, $gmeta_query_array );
			setcookie( 'gmeta_cookie', urlencode( serialize( $gmeta_merge_array ) ), time()+60*60*24*30, '/' );
		} else {
			// If cookie has not been set, start a new one
			setcookie( 'gmeta_cookie', urlencode( serialize( $gmeta_query_array ) ), time()+60*60*24*30, '/' );
		}
		/*echo '<!-- gmeta_cookie: ';
		print_r ( unserialize( urldecode( $_COOKIE['gmeta_cookie'] ) ) );
		echo '-->';*/
	}
	// Codes for populating dynamic fields with associated session array values if there is a match
	if ( isset( $_COOKIE['gmeta_cookie'] ) ) {
		$gmeta_cookie = unserialize( urldecode( $_COOKIE['gmeta_cookie'] ) );
		foreach ( $gmeta_cookie as $key => $value ) {
			add_filter( "gform_field_value_$key", function() use ( $value ) { return $value; } );
		}
	}
}
?>
<?php
/*  Copyright 2012  Barbados Property List (email : wordpress@barbadospropertylist.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
