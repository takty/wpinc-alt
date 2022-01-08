<?php
/**
 * Custom System
 *
 * @package Wpinc Robor
 * @author Takuto Yanagida
 * @version 2021-03-23
 */

namespace wpinc\robor;

function disable_embedded_sticky_post_behavior() {
	add_action(
		'pre_get_posts',
		function ( $query ) {
			if ( is_admin() || ! $query->is_main_query() ) {
				return;
			}
			$query->set( 'ignore_sticky_posts', '1' );  // Only for embedded 'post' type.
		}
	);
}

function enable_used_tags() {
	global $allowedtags;
	$allowedtags['sub']  = array();
	$allowedtags['sup']  = array();
	$allowedtags['span'] = array();
}

function enable_default_image_sizes( $add_medium_small = true ) {
	add_image_size( 'small', 320, 9999 );
	add_image_size( 'huge', 2560, 9999 );
	if ( $add_medium_small ) {
		add_image_size( 'medium-small', 480, 9999 );
	}
	add_filter(
		'image_size_names_choose',
		function ( $sizes ) use ( $add_medium_small ) {
			$is_ja = preg_match( '/^ja/', get_locale() );
			$ns    = array();
			foreach ( $sizes as $idx => $s ) {
				$ns[ $idx ] = $s;
				if ( 'thumbnail' === $idx ) {
					$ns['small'] = ( $is_ja ? '小' : 'Small' );
					if ( $add_medium_small ) {
						$ns['medium-small'] = ( $is_ja ? 'やや小' : 'Medium Small' );
					}
				}
				if ( 'medium' === $idx ) {
					$ns['medium_large'] = ( $is_ja ? 'やや大' : 'Medium Large' );
				}
			}
			return $ns;
		}
	);
}

function enable_to_add_timestamp_to_src() {
	add_filter( 'style_loader_src', '\wpinc\robor\_cb_loader_src_timestamp' );
	add_filter( 'script_loader_src', '\wpinc\robor\_cb_loader_src_timestamp' );
}

function _cb_loader_src_timestamp( $src ) {
	if ( strpos( $src, get_template_directory_uri() ) === false ) {
		return $src;
	}
	$removed_src   = strtok( $src, '?' );
	$path          = wp_normalize_path( ABSPATH );
	$resource_file = str_replace(  trailingslashit( site_url() ), trailingslashit( $path ), $removed_src );
	$resource_file = realpath( $resource_file );
	$src           = add_query_arg( 'fver', date( 'Ymdhis', filemtime( $resource_file ) ), $src );
	return $src;
}

function add_html_to_page_url() {
	global $wp_rewrite;
	$wp_rewrite->use_trailing_slashes = false;
	$wp_rewrite->page_structure       = $wp_rewrite->root . '%pagename%.html';

	add_filter(
		'home_url',
		function ( $url, $path, $orig_scheme, $blog_id ) {
			if ( empty( $path ) || '/' === $path ) {
				return $url;
			}
			$pu = parse_url( $url );
			if ( ! isset( $pu['path'] ) ) {
				return $url;
			}
			$p = get_page_by_path( $path );
			if ( $p === null ) {
				return $url;
			}
			$path = rtrim( $pu['path'], '/' );
			if ( substr( $path, - strlen( '.html' ) ) !== '.html' ) {
				$pu['path'] = "$path.html";
			}
			return \st\serialize_url( $pu );
		},
		10,
		4
	);
}
