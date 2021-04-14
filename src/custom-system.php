<?php
/**
 * Custom System
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-03-23
 */

namespace st\basic;

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

function disable_emoji() {
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'embed_head', 'print_emoji_detection_script' );
}

function enable_used_tags() {
	global $allowedtags;
	$allowedtags['sub']  = array();
	$allowedtags['sup']  = array();
	$allowedtags['span'] = array();
}

function enable_to_add_timestamp_to_src() {
	add_filter( 'style_loader_src', '\st\basic\_cb_loader_src_timestamp' );
	add_filter( 'script_loader_src', '\st\basic\_cb_loader_src_timestamp' );
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
