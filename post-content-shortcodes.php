<?php
/*
Plugin Name: Post Content Shortcodes
Plugin URI: http://plugins.ten-321.com/post-content-shortcodes/
Description: Adds shortcodes to show the content of another post or to show a list of posts
Version: 1.0.1.1
Author: cgrymala
Author URI: http://ten-321.com/
License: GPL2
Text Domain: post-content-shortcodes
Domain Path: /lang
*/
/**
 * Pull in the post_content_shortcodes class definition file
 */
if( ! class_exists( '\Post_Content_Shortcodes' ) )
	require_once( plugin_dir_path( __FILE__ ) . '/classes/class-post-content-shortcodes-admin.php' );
if( ! class_exists( '\PCS_Widget' ) )
	require_once( plugin_dir_path( __FILE__ ) . '/classes/class-post-content-widgets.php' );

global $post_content_shortcodes_obj;
if ( is_admin() ) {
	$post_content_shortcodes_obj = \Post_Content_Shortcodes_Admin::instance();
} else {
	$post_content_shortcodes_obj = \Post_Content_Shortcodes::instance();
}
