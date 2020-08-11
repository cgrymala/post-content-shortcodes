<?php
/*
Plugin Name: Post Content Shortcodes
Plugin URI: http://plugins.ten-321.com/post-content-shortcodes/
Description: Adds shortcodes to show the content of another post or to show a list of posts
Version: 2020.8.1
Author: cgrymala
Author URI: http://ten-321.com/
License: GPL2
Text Domain: post-content-shortcodes
Domain Path: /lang
*/
namespace {
	spl_autoload_register( function ( $class_name ) {
		if ( ! stristr( $class_name, 'Ten321\Post_Content_Shortcodes\\' ) && ! stristr( $class_name, 'Ten321\Common\\' ) ) {
			return;
		}

		$filename = plugin_dir_path( __FILE__ ) . '/lib/classes/' . strtolower( str_replace( array(
				'\\',
				'_'
			), array( '/', '-' ), $class_name ) ) . '.php';

		if ( ! file_exists( $filename ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[Post Content Shortcodes Debug]: Attempted to include ' . $filename . ' but could not find it' );
			}
			return;
		}

		include $filename;
	} );
}

namespace Ten321\Post_Content_Shortcodes {
	global $post_content_shortcodes_obj;
	if ( is_admin() ) {
		$post_content_shortcodes_obj = Admin::instance();
	} else {
		$post_content_shortcodes_obj = Plugin::instance();
	}
}
