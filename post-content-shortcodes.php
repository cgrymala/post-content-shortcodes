<?php
/*
Plugin Name: Post Content Shortcodes
Plugin URI: http://plugins.ten-321.com/post-content-shortcodes/
Description: Adds shortcodes to show the content of another post or to show a list of posts
Version: 0.3
Author: cgrymala
Author URI: http://ten-321.com/
License: GPL2
*/
/**
 * Pull in the post_content_shortcodes class definition file
 */
if( !class_exists( post_content_shortcodes ) )
	require_once( 'class-post-content-shortcodes.php' );
if( !class_exists( 'pcs_widget' ) )
	require_once( 'class-post-content-widgets.php' );

/**
 * Initiate the post_content_shortcodes object
 */
add_action( 'init', 'init_post_content_shortcodes' );
add_action( 'widgets_init', array( 'post_content_shortcodes', 'register_widgets' ) );

function init_post_content_shortcodes() {
	global $post_content_shortcodes_obj;
	return $post_content_shortcodes_obj = new post_content_shortcodes;
}
?>