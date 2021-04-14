<?php
/**
 * Custom Front
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-03-23
 */

namespace st\basic;

function remove_single_title_indication( $protected, $private ) {
	if ( $protected ) {
		add_filter(
			'protected_title_format',
			function ( $prepend ) {
				if ( ! is_single() ) {
					return $prepend;
				}
				return '%s';
			}
		);
	}
	if ( $private ) {
		add_filter(
			'private_title_format',
			function ( $prepend ) {
				if ( ! is_single() ) {
					return $prepend;
				}
				return '%s';
			}
		);
	}
}


// -----------------------------------------------------------------------------


function remove_archive_title_text() {
	add_filter(
		'get_the_archive_title',
		function ( $title ) {
			if ( is_category() || is_tag() || is_tax() ) {
				$title = single_term_title( '', false );
			} elseif ( is_year() ) {
				$title = get_the_date( _x( 'Y', 'yearly archives date format' ) );
			} elseif ( is_month() ) {
				$title = get_the_date( _x( 'F Y', 'monthly archives date format' ) );
			} elseif ( is_day() ) {
				$title = get_the_date( _x( 'F j, Y', 'daily archives date format' ) );
			} elseif ( is_post_type_archive() ) {
				$title = post_type_archive_title( '', false );
			}
			return $title;
		}
	);
}

function remove_separator_in_title_and_description() {
	add_filter(
		'bloginfo',
		function ( $output, $show ) {
			if ( 'description' === $show || 'name' === $show || '' === $show ) {
				return implode( ' ', \st\separate_line( $output ) );
			}
			return $output;
		},
		10,
		2
	);
	add_filter(
		'document_title_parts',
		function ( $title ) {
			$title['title'] = implode( ' ', \st\separate_line( $title['title'] ) );
			return $title;
		}
	);
}


// -----------------------------------------------------------------------------


function add_current_to_archive_link() {
	add_filter(
		'get_archives_link',
		function ( $link_html ) {
			$regex = '/^\t<(link |option |li>)/';
			if ( preg_match( $regex, $link_html, $m ) ) {
				switch ( $m[1] ) {
					case 'option ':
						$search  = '<option';
						$replace = '<option selected="selected"';
						$regex   = "/^\t<option value='([^']+)'>[^<]+<\/option>/";
						break;
					case 'li>':
						$search  = '<li>';
						$replace = '<li class="current">';
						$regex   = "/^\t<li><a href='([^']+)' title='[^']+'>[^<]+<\/a><\/li>/";
						break;
					default:
						$search  = '';
						$replace = '';
						$regex   = '';
				}
			}
			if ( $regex && preg_match( $regex, $link_html, $m ) ) {
				$url = \st\get_current_uri();
				if ( strpos( $url, $m[1] ) === 0 ) {
					$link_html = str_replace( $search, $replace, $link_html );
				}
			}
			return $link_html;
		},
		99
	);
}


// -----------------------------------------------------------------------------


function enable_hidden_anchors_of_archives_link_option() {
	static $urls = array();
	add_filter(
		'get_archives_link',
		function ( $link_html, $url, $text, $format, $before, $after ) use ( &$urls ) {
			if ( 'option' === $format ) {
				if ( empty( $urls ) ) {
					add_action(
						'wp_footer',
						function () use ( &$urls ) {
							echo '<div style="display:none;"><!-- archive links of option values -->';
							foreach ( $urls as $url ) {
								echo '<a href="' . esc_attr( $url ) . '"></a>';
							}
							echo '</div>';
						}
					);
				}
				if ( preg_match( "/<option value=['\"]([^']+)['\"]>[^<]+<\/option>/", $link_html, $m ) ) {
					$urls[] = $m[1];
				}
			}
			return $link_html;
		},
		99,
		6
	);
}
