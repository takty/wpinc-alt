<?php
/**
 * Secure Site - Disabling Unnecessary Functions
 *
 * @package Wpinc Alt
 * @author Takuto Yanagida
 * @version 2024-03-12
 */

declare(strict_types=1);

namespace wpinc\alt;

/**
 * Disable REST API except specific routes.
 *
 * @param string[] $permitted_routes Permitted routes. For example, array( 'oembed', 'contact-form-7' ).
 */
function disable_rest_api_without_permission( array $permitted_routes = array() ): void {
	add_filter(
		'rest_pre_dispatch',
		function ( $result, $_wp_rest_server, $request ) use ( $permitted_routes ) {
			if ( is_user_logged_in() ) {
				return $result;
			}
			$path = $request->get_route();
			foreach ( $permitted_routes as $r ) {
				if ( 0 === strpos( $path, "/$r/" ) ) {
					return $result;
				}
			}
			return new \WP_Error( 'rest_disabled', __( 'REST API is disabled.' ) . ' (status: ' . rest_authorization_required_code() . ')' );
		},
		10,
		3
	);
}

/**
 * Disable REST API without authentication.
 */
function disable_rest_api_without_authentication(): void {
	add_filter(
		'rest_authentication_errors',
		function ( $result ) {
			if ( true === $result || is_wp_error( $result ) || is_user_logged_in() ) {
				return $result;
			}
			return new \WP_Error( 'rest_disabled', __( 'REST API is disabled.' ) . ' (status: ' . rest_authorization_required_code() . ')' );
		}
	);
}

/**
 * Shutdown REST API completely.
 */
function shutdown_rest_api(): void {
	remove_action( 'init', 'rest_api_init' );
	remove_action( 'rest_api_init', 'rest_api_default_filters', 10 );
	remove_action( 'rest_api_init', 'register_initial_settings', 10 );
	remove_action( 'rest_api_init', 'create_initial_rest_routes', 99 );
	remove_action( 'parse_request', 'rest_api_loaded' );
	add_filter(
		'rewrite_rules_array',
		function ( $rules ) {
			foreach ( $rules as $rule => $_rewrite ) {
				if ( preg_match( '/wp-json/', $rule ) ) {
					unset( $rules[ $rule ] );
				}
			}
			return $rules;
		}
	);
}


// -----------------------------------------------------------------------------


/**
 * Disallow file edit.
 */
function disallow_file_edit(): void {
	if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
		define( 'DISALLOW_FILE_EDIT', true );
	}
}

/**
 * Disable XML RPC.
 */
function disable_xml_rpc(): void {
	/** @psalm-suppress PossiblyInvalidArgument */  // phpcs:ignore
	add_filter( 'xmlrpc_enabled', '__return_false' );
}

/**
 * Disable embed feature.
 *
 * @global \WP_Embed $wp_embed
 *
 * @param string[] $allowed_urls Allowed URLs.
 */
function disable_embed( array $allowed_urls = array() ): void {
	add_filter(
		'embed_oembed_html',
		function ( $cached_html, $url ) use ( $allowed_urls ) {
			foreach ( $allowed_urls as $au ) {
				if ( 0 === strpos( $url, $au ) ) {
					return $cached_html;
				}
			}
			global $wp_embed;
			return $wp_embed->maybe_make_link( $url );
		},
		10,
		2
	);
	/** @psalm-suppress PossiblyInvalidArgument */  // phpcs:ignore
	add_filter( 'embed_oembed_discover', '__return_false' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );

	add_filter(
		'rest_endpoints',
		function ( $endpoints ) {
			unset( $endpoints['/oembed/1.0/embed'] );
			return $endpoints;
		},
		10,
		1
	);
	add_filter(
		'oembed_response_data',
		/** @psalm-suppress MissingClosureParamType, InvalidArgument */  // phpcs:ignore
		function ( $data ) {
			/** @psalm-suppress RedundantCondition */  // phpcs:ignore
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				return false;
			}
			return $data;
		},
		10,
		1
	);
	add_filter(
		'rewrite_rules_array',
		function ( $rules ) {
			foreach ( $rules as $rule => $rewrite ) {
				if ( false !== strpos( $rewrite, 'embed=true' ) ) {
					unset( $rules[ $rule ] );
				}
			}
			return $rules;
		},
		10,
		1
	);
	add_action(
		'wp_default_scripts',
		function ( $scripts ) {
			if ( isset( $scripts->registered['wp-edit-post'] ) ) {
				$scripts->registered['wp-edit-post']->deps = array_diff(
					$scripts->registered['wp-edit-post']->deps,
					array( 'wp-embed' )
				);
			}
		},
		10,
		1
	);
}


// -----------------------------------------------------------------------------


/**
 * Disable author pages.
 */
function disable_author_page(): void {
	/** @psalm-suppress PossiblyInvalidArgument */  // phpcs:ignore
	add_filter( 'author_rewrite_rules', '__return_empty_array' );
	/** @psalm-suppress PossiblyInvalidArgument */  // phpcs:ignore
	add_filter( 'author_link', '__return_empty_string' );

	add_action(
		'parse_query',
		function ( \WP_Query $query ) {
			if ( ! is_admin() && is_author() ) {
				$query->set_404();
				status_header( 404 );
				nocache_headers();
			}
		}
	);
	// Remove authors from feeds.
	add_filter(
		'the_author',
		function ( string $author ): string {
			return is_feed() ? get_bloginfo_rss( 'name' ) : $author;
		}
	);
	add_filter(
		'the_author_url',
		function ( string $author_meta ): string {
			return is_feed() ? home_url() : $author_meta;
		}
	);
	// Remove information of authors from REST response.
	add_filter(
		'rest_prepare_user',
		function ( \WP_REST_Response $response ) {
			if ( is_user_logged_in() ) {
				return $response;
			}
			return new \WP_REST_Response( array() );
		},
		10
	);
	// Remove author name from oEmbed response data.
	add_filter(
		'oembed_response_data',
		function ( $data ) {
			$data['author_name'] = '';
			// 'author_url' becomes an empty string by 'author_link' filter added above.
			return $data;
		},
		10
	);
}


// -----------------------------------------------------------------------------


/**
 * Set membership options secure.
 */
function set_membership_option(): void {
	update_option( 'users_can_register', 0 );
	update_option( 'default_role', 'subscriber' );
}
