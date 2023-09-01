<?php
/**
 * Adding Timestamp to Ensure Updated Styles and Scripts Are Loaded.
 *
 * @package Wpinc Alt
 * @author Takuto Yanagida
 * @version 2023-08-30
 */

namespace wpinc\alt;

/**
 * Add timestamps to style and script sources.
 */
function add_timestamp_to_source(): void {
	add_filter( 'style_loader_src', '\wpinc\alt\_cb_loader_src__add_timestamp' );
	add_filter( 'script_loader_src', '\wpinc\alt\_cb_loader_src__add_timestamp' );
}

/**
 * Callback function for '*_loader_src' filters.
 *
 * @access private
 *
 * @param string $src The source URL of the enqueued style and script.
 * @return string Source.
 */
function _cb_loader_src__add_timestamp( string $src ): string {
	if ( strpos( $src, get_template_directory_uri() ) === false ) {
		return $src;
	}
	$removed_src = strtok( $src, '?' );
	if ( false === $removed_src ) {
		$removed_src = $src;
	}
	$path          = wp_normalize_path( ABSPATH );
	$resource_file = str_replace( trailingslashit( site_url() ), trailingslashit( $path ), $removed_src );
	$resource_file = realpath( $resource_file );
	if ( false === $resource_file ) {
		return $src;
	}
	$fmt = filemtime( $resource_file );
	if ( false === $fmt ) {
		return $src;
	}
	$fts  = gmdate( 'Ymdhis', $fmt );
	$hash = hash( 'crc32b', $resource_file . $fts );
	return add_query_arg( 'v', $hash, $src );
}
