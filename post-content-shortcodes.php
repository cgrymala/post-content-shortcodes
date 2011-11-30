<?php
/*
Plugin Name: Post Content Shortcodes
Plugin URI: http://plugins.ten-321.com/post-content-shortcodes/
Description: Adds shortcodes to show the content of another post or to show a list of posts
Version: 0.2
Author: cgrymala
Author URI: http://ten-321.com/
License: GPL2
*/
if( !class_exists( 'post_content_shortcodes' ) ) {
	/**
	 * Class and methods to implement various shortcodes for cloning content
	 */
	class post_content_shortcodes {
		var $defaults	= array();
		
		function __construct() {
			$this->defaults = apply_filters( 'post-content-shortcodes-defaults', array(
				'id'			=> 0,
				'post_type'		=> 'post',
				'order'			=> 'asc',
				'orderby'		=> 'post_title',
				'numberposts'	=> -1,
				'post_status'	=> 'publish',
				'offset'		=> null,
				'category'		=> null,
				'include'		=> null,
				'exclude'		=> null,
				'meta_key'		=> null,
				'meta_value'	=> null,
				'post_mime_type'=> null,
				'post_parent'	=> null,
				'exclude_current'=> true,
			) );
			add_shortcode( 'post-content', array( &$this, 'post_content' ) );
			add_shortcode( 'post-list', array( &$this, 'post_list' ) );
		}
		
		/**
		 * Handle the shortcode to display another post's content
		 */
		function post_content( $atts=array() ) {
			extract( shortcode_atts( $this->defaults, $atts ) );
			/**
			 * Attempt to avoid an endless loop
			 */
			if( $id == $GLOBALS['post']->ID || empty( $id ) )
				return;
			
			$p = get_post( $id );
			if( empty( $p ) || is_wp_error( $p ) )
				return;
			
			return apply_filters( 'post-content-shortcodes-content', apply_filters( 'the_content', $p->post_content ), $p );
		}
		
		/**
		 * Handle the shortcode to display a list of posts
		 */
		function post_list( $atts=array() ) {
			$atts = shortcode_atts( $this->defaults, $atts );
			$this->is_true( $atts['exclude_current'] );
			$posts = get_posts( $this->get_args( $atts ) );
			if( empty( $posts ) )
				return apply_filters( 'post-content-shortcodes-no-posts-error', '', $this->get_args( $atts ) );
			
			$output = apply_filters( 'post-content-shortcodes-open-list', '<ul class="post-list">' );
			foreach( $posts as $p ) {
				$output .= apply_filters( 'post-content-shortcodes-open-item', '<li class="listed-post">' );
				$output .= apply_filters( 'post-content-shortcodes-item-link-open', '<a href="' . apply_filters( 'the_permalink', get_permalink( $p->ID ) ) . '" title="' . apply_filters( 'the_title_attribute', $p->post_title ) . '">', apply_filters( 'the_permalink', get_permalink( $p->ID ) ), apply_filters( 'the_title_attribute', $p->post_title ) );
				$output .= apply_filters( 'the_title', $p->post_title );
				$output .= apply_filters( 'post-content-shortcodes-item-link-close', '</a>' );
				$output .= apply_filters( 'post-content-shortcodes-close-item', '</li>' );
			}
			$output .= apply_filters( 'post-content-shortcodes-close-list', '</ul>' );
			
			return $output;
		}
		
		/**
		 * Determine whether a variable evaluates to boolean true
		 */
		function is_true( &$var ) {
			if( in_array( $var, array( 'false', false, 0 ), true ) )
				return $var = false;
			if( in_array( $var, array( 'true', true, 1 ), true ) )
				return $var = true;
		}
		
		/**
		 * Build the list of get_posts() args
		 */
		function get_args( $atts ) {
			unset( $atts['id'] );
			
			if( $atts['exclude_current'] ) {
				if( !empty( $atts['exclude'] ) ) {
					if( !is_array( $atts['exclude'] ) )
						$atts['exclude'] = array_map( 'trim', explode( ',', $atts['exclude'] ) );
					
					$atts['exclude'][] = $GLOBALS['post']->ID;
				} else {
					$atts['exclude'] = array( $GLOBALS['post']->ID );
				}
			}
			
			return array_filter( $atts );
		}
	}
}

/**
 * Initiate the post_content_shortcodes object
 */
add_filter( 'init', 'init_post_content_shortcodes' );
function init_post_content_shortcodes() {
	return new post_content_shortcodes;
}
?>