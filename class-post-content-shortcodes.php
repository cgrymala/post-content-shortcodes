<?php
/**
 * The class setup for post-content-shortcodes plugin
 * @version 0.3
 */
if( !class_exists( 'post_content_shortcodes' ) ) {
	/**
	 * Class and methods to implement various shortcodes for cloning content
	 */
	class post_content_shortcodes {
		var $defaults	= array();
		
		function __construct() {
			global $blog_id;
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
				'blog_id'		=> $blog_id,
			) );
			
			add_shortcode( 'post-content', array( &$this, 'post_content' ) );
			add_shortcode( 'post-list', array( &$this, 'post_list' ) );
			add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		}
		
		function register_widgets() {
			register_widget( 'pcs_list_widget' );
			register_widget( 'pcs_content_widget' );
		}
		
		/**
		 * Handle the shortcode to display another post's content
		 */
		function post_content( $atts=array() ) {
			global $wpdb;
			extract( shortcode_atts( $this->defaults, $atts ) );
			/**
			 * Attempt to avoid an endless loop
			 */
			if( $id == $GLOBALS['post']->ID || empty( $id ) )
				return;
			
			$p = $this->get_post_from_blog( $id, $blog_id );
			if( empty( $p ) || is_wp_error( $p ) )
				return apply_filters( 'post-content-shortcodes-no-posts-error', '<p>No posts could be found that matched the specified criteria.</p>', $this->get_args( $atts ) );
			
			return apply_filters( 'post-content-shortcodes-content', apply_filters( 'the_content', $p->post_content ), $p );
		}
		
		function get_post_from_blog( $post_id=0, $blog_id=0 ) {
			if( empty( $post_id ) )
				return;
			if( $blog_id == $GLOBALS['blog_id'] || empty( $blog_id ) )
				return get_post( $post_id );
			
			if( false !== ( $p = get_transient( 'pcsc-blog' . $blog_id . '-post' . $post_id ) ) )
				return $p;
			
			global $wpdb;
			$org_blog = $wpdb->set_blog_id( $blog_id );
			$p = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID=%d", $post_id ) );
			$wpdb->set_blog_id( $org_blog );
			
			set_transient( 'pcsc-blog' . $blog_id . '-post' . $post_id, $p, 60 *60 );
			
			return $p;
		}
		
		/**
		 * Handle the shortcode to display a list of posts
		 */
		function post_list( $atts=array() ) {
			$atts = shortcode_atts( $this->defaults, $atts );
			$this->is_true( $atts['exclude_current'] );
			
			$posts = $this->get_posts_from_blog( $atts, $atts['blog_id'] );
			if( empty( $posts ) )
				return apply_filters( 'post-content-shortcodes-no-posts-error', '<p>No posts could be found that matched the specified criteria.</p>', $this->get_args( $atts ) );
			
			$output = apply_filters( 'post-content-shortcodes-open-list', '<ul class="post-list">' );
			foreach( $posts as $p ) {
				$output .= apply_filters( 'post-content-shortcodes-open-item', '<li class="listed-post">' );
				$output .= apply_filters( 'post-content-shortcodes-item-link-open', '<a href="' . $this->get_shortlink_from_blog( $p->ID, $atts['blog_id'] ) . '" title="' . apply_filters( 'the_title_attribute', $p->post_title ) . '">', apply_filters( 'the_permalink', get_permalink( $p->ID ) ), apply_filters( 'the_title_attribute', $p->post_title ) );
				$output .= apply_filters( 'the_title', $p->post_title );
				$output .= apply_filters( 'post-content-shortcodes-item-link-close', '</a>' );
				$output .= apply_filters( 'post-content-shortcodes-close-item', '</li>' );
			}
			$output .= apply_filters( 'post-content-shortcodes-close-list', '</ul>' );
			
			return $output;
		}
		
		/**
		 * Retrieve a batch of posts from a specific blog
		 */
		function get_posts_from_blog( $atts=array(), $blog_id=0 ) {
			$args = $this->get_args( $atts );
			
			if( $blog_id == $GLOBALS['blog_id'] || empty( $blog_id ) || !is_numeric( $blog_id ) )
				return get_posts( $args );
			
			if( false !== ( $p = get_transient( 'pcsc-list-blog' . $blog_id . '-args' . md5( $args ) ) ) )
				return $p;
			
			global $wpdb;
			$org_blog = $wpdb->set_blog_id( $blog_id );
			$p = get_posts( $args );
			$wpdb->set_blog_id( $org_blog );
			
			set_transient( 'pcsc-list-blog'. $blog_id . '-args' . md5( $args ), $p, 60 * 60 );
			return $p;
		}
		
		/**
		 * Determine the shortlink to a post on a specific blog
		 */
		function get_shortlink_from_blog( $post_id=0, $blog_id=0 ) {
			if( empty( $post_id ) )
				return;
			if( empty( $blog_id ) || $blog_id == $GLOBALS['blog_id'] || !is_numeric( $blog_id ) )
				return apply_filters( 'the_permalink', get_permalink( $post_id ) );
			
			global $wpdb;
			$blog_info = $wpdb->get_row( $wpdb->prepare( "SELECT domain, path FROM {$wpdb->blogs} WHERE blog_id=%d", $blog_id ), ARRAY_A );
			return 'http://' . $blog_info['domain'] . $blog_info['path'] . '?p=' . $post_id;
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
			
			if( $atts['exclude_current'] && $GLOBALS['blog_id'] != $atts['blog_id'] ) {
				if( !empty( $atts['exclude'] ) ) {
					if( !is_array( $atts['exclude'] ) )
						$atts['exclude'] = array_map( 'trim', explode( ',', $atts['exclude'] ) );
					
					$atts['exclude'][] = $GLOBALS['post']->ID;
				} else {
					$atts['exclude'] = array( $GLOBALS['post']->ID );
				}
			}
			
			$atts['orderby'] = str_replace( 'post_', '', $atts['orderby'] );
			
			unset( $atts['blog_id'], $atts['exclude_current'] );
			
			return array_filter( $atts );
		}
	}
}
?>