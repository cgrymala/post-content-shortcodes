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

/**
 * Initiate the post_content_shortcodes object
 */
add_filter( 'init', 'init_post_content_shortcodes' );
function init_post_content_shortcodes() {
	return new post_content_shortcodes;
}
?>