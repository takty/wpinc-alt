<?php
/**
 * Default Options
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-03-23
 */

namespace st\basic;

function update_reading_options() {
	update_option( 'show_on_front', 'page' );
	if ( empty( get_option( 'page_on_front' ) ) ) {
		$pages = get_pages( array( 'sort_column' => 'post_id' ) );
		if ( ! empty( $pages ) ) {
			update_option( 'page_on_front', $pages[0]->ID );
		}
	}
	update_option( 'page_for_posts', '' );
}

function update_discussion_options() {
	update_option( 'default_pingback_flag', 0 );
	update_option( 'default_ping_status', 0 );
	update_option( 'default_comment_status', 0 );
}

function update_media_options() {
	update_option( 'uploads_use_yearmonth_folders', 1 );
}
