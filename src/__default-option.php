<?php
/**
 * Default Options
 *
 * @package Wpinc Robor
 * @author Takuto Yanagida
 * @version 2021-03-23
 */

namespace wpinc\robor;

function update_media_options() {
	update_option( 'thumbnail_size_w', 320 );
	update_option( 'thumbnail_size_h', 320 );
	update_option( 'thumbnail_crop', 1 );
	update_option( 'medium_size_w', 640 );
	update_option( 'medium_size_h', 9999 );
	update_option( 'medium_large_size_w', 960 );
	update_option( 'medium_large_size_h', 9999 );
	update_option( 'large_size_w', 1280 );
	update_option( 'large_size_h', 9999 );
	update_option( 'uploads_use_yearmonth_folders', 1 );
}
