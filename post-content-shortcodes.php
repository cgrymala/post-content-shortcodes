<?php
/*
Plugin Name: Post Content Shortcodes
Plugin URI: http://plugins.ten-321.com/post-content-shortcodes/
Description: Adds shortcodes to show the content of another post or to show a list of posts
Version: 0.5.6
Author: cgrymala
Author URI: http://ten-321.com/
License: GPL2
*/
/**
 * Pull in the post_content_shortcodes class definition file
 */
if( ! class_exists( 'Post_Content_Shortcodes' ) )
	require_once( 'class-post-content-shortcodes-admin.php' );
if( ! class_exists( 'PCS_Widget' ) )
	require_once( 'class-post-content-widgets.php' );

/**
 * Initiate the post_content_shortcodes object
 */
add_action( 'after_setup_theme', 'init_post_content_shortcodes', 1 );
/*add_action( 'widgets_init', array( 'post_content_shortcodes', 'register_widgets' ) );*/

function init_post_content_shortcodes() {
	global $post_content_shortcodes_obj;
	if( is_admin() )
		return $post_content_shortcodes_obj = new Post_Content_Shortcodes_Admin;
	else
		return $post_content_shortcodes_obj = new Post_Content_Shortcodes;
}
?>