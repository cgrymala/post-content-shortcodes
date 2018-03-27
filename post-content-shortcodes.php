<?php
/*
Plugin Name: Post Content Shortcodes
Plugin URI: http://plugins.ten-321.com/post-content-shortcodes/
Description: Adds shortcodes to show the content of another post or to show a list of posts
Version: 1.0
Author: cgrymala
Author URI: http://ten-321.com/
License: GPL2
*/

namespace {
	/**
	 * Set up an autoloader to automatically pull in the appropriate class definitions
	 *
	 * @param string $class_name the full name of the class being invoked
	 *
	 * @since 2018.1
	 * @return void
	 */
	spl_autoload_register( function ( $class_name ) {
		if ( ! stristr( $class_name, 'Post_Content_Shortcodes\\' ) ) {
			return;
		}

		$filename = plugin_dir_path( __FILE__ ) . '/lib/functions/' . strtolower( str_replace( array( '\\', '_' ), array( '/', '-' ), $class_name ) ) . '.php';

		if ( ! file_exists( $filename ) ) {
			return;
		}

		include $filename;
	} );
}

namespace Post_Content_Shortcodes {
	global $post_content_shortcodes_obj;
	$post_content_shortcodes_obj = \Post_Content_Shortcodes\Plugin::instance();
}