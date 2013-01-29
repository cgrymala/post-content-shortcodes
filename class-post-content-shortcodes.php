<?php
/**
 * The class setup for post-content-shortcodes plugin
 * @version 0.3.2
 */
if( !class_exists( 'Post_Content_Shortcodes' ) ) {
	/**
	 * Class and methods to implement various shortcodes for cloning content
	 */
	class Post_Content_Shortcodes {
		/**
		 * A container to hold our default shortcode attributes
		 */
		var $defaults	= array();
		/**
		 * A container to hold our global plugin settings
		 */
		var $settings 	= array();
		var $stock_settings	= array( 'enable-pcs-content-widget' => true, 'enable-pcs-list-widget' => true, 'enable-pcs-ajax' => false, 'use-styles' => true );
		var $use_styles = true;
		
		/**
		 * Build the post_content_shortcodes object
		 */
		function __construct() {
			$this->plugin_dir_name = 'post-content-shortcodes/post-content-shortcodes.php';
			
			global $blog_id;
			/**
			 * Set up the default values for our shortcode attributes
			 * These attributes are used for both shortcodes
			 * @uses apply_filters() to allow filtering the list with the post-content-shortcodes-defaults filter
			 */
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
				/* Non-standard arguments */
				'exclude_current'=> true,
				'blog_id'		=> $blog_id,
				'show_image'	=> false,
				'show_excerpt'	=> false,
				'excerpt_length'=> 0,
				'image_width'	=> 0,
				'image_height'	=> 0, 
				'show_title'    => false, 
				'show_author'   => false, 
				'show_date'     => false, 
			) );
			
			add_shortcode( 'post-content', array( &$this, 'post_content' ) );
			add_shortcode( 'post-list', array( &$this, 'post_list' ) );
			add_action( 'widgets_init', array( $this, 'register_widgets' ) );
			add_action( 'wp_print_styles', array( &$this, 'print_styles' ) );
			
			/**
			 * Set up the various admin options items
			 */
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			if( $this->is_multinetwork() )
				add_action( 'network_admin_menu', array( &$this, 'admin_menu' ) );
			elseif( $this->is_plugin_active_for_network() )
				add_action( 'network_admin_menu', array( &$this, 'admin_menu' ) );
			else
				add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		}
		
		/**
		 * Enqueue the stylesheet
		 */
		function print_styles() {
			$this->_get_options();
			if( $this->settings['use-styles'] )
				wp_enqueue_style( 'pcs-styles', plugins_url( 'default-styles.css', __FILE__ ), array(), '0.3', 'screen' );
		}
		
		/**
		 * Determine whether this is a multinetwork install or not
		 */
		function is_multinetwork() {
			return function_exists( 'is_multinetwork' ) && function_exists( 'add_mnetwork_option' ) && is_multinetwork();
		}
		
		/**
		 * Determine whether this plugin is network active in a multisite install
		 */
		function is_plugin_active_for_network() {
			return function_exists( 'is_plugin_active_for_network' ) && is_multisite() && is_plugin_active_for_network( $this->plugin_dir_name );
		}
		
		/**
		 * Retrieve our options from the database
		 */
		protected function _get_options() {
			if( $this->is_multinetwork() )
				$this->settings = get_mnetwork_option( 'pcs-settings', array() );
			elseif( $this->is_plugin_active_for_network() )
				$this->settings = get_site_option( 'pcs-settings', array() );
			else
				$this->settings = get_option( 'pcs-settings', array() );
			
			$this->settings = array_merge( $this->stock_settings, $this->settings );
			return;
		}
		
		/**
		 * Register the two widgets
		 */
		function register_widgets() {
			$this->_get_options();
			if( 'on' == $this->settings['enable-pcs-list-widget'] )
				register_widget( 'PCS_List_Widget' );
			if( 'on' == $this->settings['enable-pcs-content-widget'] )
				register_widget( 'PCS_Content_Widget' );
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
			
			/**
			 * Output a little debug info if necessary
			 */
			if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || isset( $_REQUEST['pcs-debug'] ) )
				error_log( '[PCS Debug]: Preparing to retrieve post content with the following args: ' . print_r( $atts, true ) );
			
			$p = $this->get_post_from_blog( $id, $blog_id );
			if( empty( $p ) || is_wp_error( $p ) )
				return apply_filters( 'post-content-shortcodes-no-posts-error', '<p>No posts could be found that matched the specified criteria.</p>', $this->get_args( $atts ) );
			
			$this->is_true( $show_excerpt );
			$this->is_true( $show_image );
			$this->is_true( $show_title );
			$this->is_true( $show_author );
			$this->is_true( $show_date );
			
			$post_date = mysql2date( get_option( 'date_format' ), $p->post_date );
			$post_author = get_userdata( $p->post_author );
			if ( empty( $post_author ) || ! is_object( $post_author ) || ! isset( $post_author->display_name ) ) {
				$post_author = (object) array( 'display_name' => '' );
				$show_author = false;
			}
			
			$content = $p->post_content;
			
			if ( $show_excerpt ) {
				$content = empty( $p->post_excerpt ) ? $p->post_content : $p->post_excerpt;
			}
			
			if ( intval( $excerpt_length ) && intval( $excerpt_length ) < str_word_count( $content ) ) {
				$content = explode( ' ', $content );
				$content = implode( ' ', array_slice( $content, 0, ( intval( $excerpt_length ) - 1 ) ) );
				$content = force_balance_tags( $content );
				$content .= apply_filters( 'post-content-shortcodes-read-more', ' <span class="read-more"><a href="' . get_permalink( $p->ID ) . '" title="' . apply_filters( 'the_title_attribute', $p->post_title ) . '">' . __( 'Read more' ) . '</a></span>' );
			}
			
			if ( $show_image ) {
				if ( empty( $image_height ) && empty( $image_width ) )
					$image_size = apply_filters( 'post-content-shortcodes-default-image-size', 'thumbnail' );
				else
					$image_size = array( intval( $image_width ), intval( $image_height ) );
					
				$content = $this->get_the_post_thumbnail( $p->ID, $image_size, array( 'class' => apply_filters( 'post-content-shortcodes-image-class', 'pcs-featured-image' ) ), $blog_id ) . $content;
			}
			
			if ( $show_date && $show_author )
				$content = apply_filters( 'post-content-shortcodes-meta', '<p class="post-meta">' . sprintf( __( 'Posted by <span class="post-author">%1$s</span> on <span class="post-date">%2$s</a>' ), $post_author->display_name, $post_date ) . '</p>', $p ) . $content;
			elseif ( $show_date )
				$content = apply_filters( 'post-content-shortcodes-meta', '<p class="post-meta">' . sprintf( __( 'Posted on %2$s' ), $post_author->display_name, $post_date ) . '</p>', $p ) . $content;
			elseif ( $show_author )
				$content = apply_filters( 'post-content-shortcodes-meta', '<p class="post-meta">' . sprintf( __( 'Posted by %s' ), $post_author->display_name, $post_date ) . '</p>', $p ) . $content;
			
			if ( $show_title )
				$content = apply_filters( 'post-content-shortcodes-title', '<h2>' . $p->post_title . '</h2>', $p->post_title ) . $content;
			
			return apply_filters( 'post-content-shortcodes-content', apply_filters( 'the_content', $content ), $p );
		}
		
		/**
		 * Retrieve the featured image HTML for the current post
		 */
		function get_the_post_thumbnail( $post_ID, $image_size = 'thumbnail', $attr = array(), $blog_id = 0 ) {
			if ( empty( $blog_id ) || (int) $blog_id === (int) $GLOBALS['blog_id'] )
				return get_the_post_thumbnail( $post_ID, $image_ise, $attr );
			if ( ! is_numeric( $post_ID ) || ! is_numeric( $blog_id ) )
				return '';
			
			global $wpdb;
			$old = $wpdb->set_blog_id( $blog_id );
			$post_thumbnail_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key=%s AND post_id=%d LIMIT 1", '_thumbnail_id', $post_ID ) );
			$html = wp_get_attachment_image( $post_thumbnail_id, $image_size, false, $attr );
			$wpdb->set_blog_id( $old );
			return $html;
		}
		
		function get_post_from_blog( $post_id=0, $blog_id=0 ) {
			if( empty( $post_id ) )
				return;
			if( $blog_id == $GLOBALS['blog_id'] || empty( $blog_id ) )
				return get_post( $post_id );
			
			if ( isset( $_GET['delete_transients'] ) )
				delete_transient( 'pcsc-blog' . $blog_id . '-post' . $post_id );
			
			if( false !== ( $p = get_transient( 'pcsc-blog' . $blog_id . '-post' . $post_id ) ) )
				return $p;
			
			global $wpdb;
			$org_blog = $wpdb->set_blog_id( $blog_id );
			$p = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID=%d", $post_id ) );
			$wpdb->set_blog_id( $org_blog );
			
			set_transient( 'pcsc-blog' . $blog_id . '-post' . $post_id, $p, apply_filters( 'pcsc-transient-timeout', 60 * 60 ) );
			
			return $p;
		}
		
		/**
		 * Handle the shortcode to display a list of posts
		 */
		function post_list( $atts=array() ) {
			$args = $atts;
			
			/** 
			 * Set this shortcode to display the post title by default
			 */
			if ( ! array_key_exists( 'show_title', $atts ) )
				$atts['show_title'] = true;
			
			$atts = shortcode_atts( $this->defaults, $atts );
			$atts['posts_per_page'] = $atts['numberposts'];
			
			$args = array_diff_key( $args, $atts );
			$atts['tax_query'] = array();
			foreach ( $args as $k => $v ) {
				$atts['tax_query'][] = array( 'taxonomy' => $k, 'field' => 'slug', 'terms' => $v );
			}
			
			$this->is_true( $atts['exclude_current'] );
			$this->is_true( $atts['show_excerpt'] );
			$this->is_true( $atts['show_image'] );
			$this->is_true( $atts['show_title'] );
			$this->is_true( $atts['show_author'] );
			$this->is_true( $atts['show_date'] );
			
			/**
			 * Output a little debug info if necessary
			 */
			if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || isset( $_REQUEST['pcs-debug'] ) )
				error_log( '[PCS Debug]: Preparing to retrieve post list with the following args: ' . print_r( $atts, true ) );
				
			$posts = $this->get_posts_from_blog( $atts, $atts['blog_id'] );
			if( empty( $posts ) )
				return apply_filters( 'post-content-shortcodes-no-posts-error', '<p>No posts could be found that matched the specified criteria.</p>', $this->get_args( $atts ) );
			
			$output = apply_filters( 'post-content-shortcodes-open-list', '<ul class="post-list' . ( $atts['show_excerpt'] ? ' with-excerpt' : '' ) . ( $atts['show_image'] ? ' with-image' : '' ) . '">' );
			foreach( $posts as $p ) {
				$post_date = mysql2date( get_option( 'date_format' ), $p->post_date );
				$post_author = get_userdata( $p->post_author );
				$show_author = $atts['show_author'];
				if ( empty( $post_author ) || ! is_object( $post_author ) || ! isset( $post_author->display_name ) ) {
					$post_author = (object) array( 'display_name' => '' );
					$show_author = false;
				}
				
				$output .= apply_filters( 'post-content-shortcodes-open-item', '<li class="listed-post">' );
				if ( $atts['show_title'] ) {
					$output .= apply_filters( 'post-content-shortcodes-item-link-open', '<a class="pcs-post-title" href="' . $this->get_shortlink_from_blog( $p->ID, $atts['blog_id'] ) . '" title="' . apply_filters( 'the_title_attribute', $p->post_title ) . '">', apply_filters( 'the_permalink', get_permalink( $p->ID ) ), apply_filters( 'the_title_attribute', $p->post_title ) );
					$output .= apply_filters( 'the_title', $p->post_title );
					$output .= apply_filters( 'post-content-shortcodes-item-link-close', '</a>' );
				}
				if ( $atts['show_author'] && $atts['show_date'] ) {
					$output .= apply_filters( 'post-content-shortcodes-meta', '<p class="post-meta">' . sprintf( __( 'Posted by <span class="post-author">%1$s</span> on <span class="post-date">%2$s</a>' ), $post_author->display_name, $post_date ) . '</p>', $p );
				} elseif ( $atts['show_date'] ) {
					apply_filters( 'post-content-shortcodes-meta', '<p class="post-meta">' . sprintf( __( 'Posted on <span class="post-date">%2$s</a>' ), $post_author->display_name, $post_date ) . '</p>', $p );
				} elseif ( $atts['show_author'] ) {
					apply_filters( 'post-content-shortcodes-meta', '<p class="post-meta">' . sprintf( __( 'Posted by <span class="post-author">%1$s</span>' ), $post_author->display_name, $post_date ) . '</p>', $p );
				}
				
				if( $atts['show_excerpt'] ) {
					$output .= '<div class="pcs-excerpt-wrapper">';
				}
				if( $atts['show_image'] && has_post_thumbnail( $p->ID ) ) {
					if( empty( $atts['image_height'] ) && empty( $atts['image_width'] ) )
						$image_size = 'thumbnail';
					else
						$image_size = array( $atts['image_width'], $atts['image_height'] );
					$output .= get_the_post_thumbnail( $p->ID, $image_size, array( 'class' => 'pcs-featured-image' ) );
				}
				if( $atts['show_excerpt'] ) {
					$excerpt = empty( $p->post_excerpt ) ? $p->post_content : $p->post_excerpt;
					if( !empty( $atts['excerpt_length'] ) && is_numeric( $atts['excerpt_length'] ) ) {
						$excerpt = apply_filters( 'the_excerpt', $excerpt );
						if( str_word_count( $excerpt ) > $atts['excerpt_length'] ) {
							$excerpt = explode( ' ', $excerpt );
							$excerpt = implode( ' ', array_slice( $excerpt, 0, ( $atts['excerpt_length'] - 1 ) ) );
							$excerpt = force_balance_tags( $excerpt );
						}
					}
					$output .= '<div class="pcs-excerpt">' . apply_filters( 'post-content-shortcodes-list-excerpt', apply_filters( 'the_content', $excerpt ), $p ) . '</div></div>';
				}
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
			
			if ( isset( $_GET['delete_transients'] ) )
				delete_transient( 'pcsc-list-blog' . $blog_id . '-args' . md5( maybe_serialize( $args ) ) );
			
			if( false !== ( $p = get_transient( 'pcsc-list-blog' . $blog_id . '-args' . md5( maybe_serialize( $args ) ) ) ) )
				return $p;
			
			global $wpdb;
			$org_blog = $wpdb->set_blog_id( $blog_id );
			if ( array_key_exists( 'tax_query', $args ) && is_array( $args['tax_query'] ) ) {
				foreach ( $args['tax_query'] as $t ) {
					register_taxonomy( $t['taxonomy'] );
				}
			}
			$p = get_posts( $args );
			$wpdb->set_blog_id( $org_blog );
			
			set_transient( 'pcsc-list-blog'. $blog_id . '-args' . md5( maybe_serialize( $args ) ), $p, apply_filters( 'pcsc-transient-timeout', 60 * 60 ) );
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
			if( in_array( $var, array( 'false', false, 0, '0' ), true ) )
				return $var = false;
			if( in_array( $var, array( 'true', true, 1, '1' ), true ) )
				return $var = true;
			
			$var = false;
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
			
			/**
			 * Output a little debug info if necessary
			 */
			if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || isset( $_REQUEST['pcs-debug'] ) )
				error_log( '[PCS Debug]: Preparing to return filtered args: ' . print_r( $atts, true ) );
			
			return array_filter( $atts );
		}
	}
}
?>