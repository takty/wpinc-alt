<?php
/**
 * Custom Admin Bar
 *
 * @package Wpinc Robor
 * @author Takuto Yanagida
 * @version 2021-03-23
 */

namespace wpinc\robor;

function remove_wp_logo() {
	add_action(
		'admin_bar_menu',
		function ( $wp_admin_bar ) {
			$wp_admin_bar->remove_menu( 'wp-logo' );
		},
		300
	);
}

function remove_customize_menu() {
	add_action(
		'admin_bar_menu',
		function ( $wp_admin_bar ) {
			$wp_admin_bar->remove_menu( 'customize' );
		},
		300
	);
	add_action(
		'admin_menu',
		function () {
			global $submenu;
			if ( isset( $submenu['themes.php'] ) ) {
				$customize_menu_index = -1;
				foreach ( $submenu['themes.php'] as $index => $menu_item ) {
					foreach ( $menu_item as $data ) {
						if ( strpos( $data, 'customize' ) === 0 ) {
							$customize_menu_index = $index;
							break;
						}
					}
					if ( -1 !== $customize_menu_index ) {
						break;
					}
				}
				unset( $submenu['themes.php'][ $customize_menu_index ] );
			}
		}
	);
}

function ensure_admin_side_bar_menu_area() {
	add_action(
		'admin_menu',
		function () {
			global $menu;
			$menu[19] = $menu[10];
			unset( $menu[10] );
		}
	);
}

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
