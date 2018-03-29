<?php
/**
 * The class setup for post-content-shortcodes plugin
 * @version 1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You do not have permission to access this file directly.' );
}

if( !class_exists( 'Post_Content_Shortcodes' ) ) {
	/**
	 * Class and methods to implement various shortcodes for cloning content
	 */
	class Post_Content_Shortcodes {
		/**
		 * Holds the version number for use with various assets
		 *
		 * @since  1.0
		 * @access public
		 * @var    string
		 */
		public $version = '1.0';
		/**
		 * Holds the class instance.
		 *
		 * @since   0.1
		 * @access	private
		 * @var		\Post_Content_Shortcodes
		 */
		private static $instance;
		/**
		 * @since   0.1
		 * @access  public
		 * @var     array() the array of default shortcode attributes
		 */
		public $defaults	= array();
		/**
		 * @since   0.1
		 * @access  public
		 * @var     array() the array of global plugin settings
		 */
		public $settings 	= array();
		/**
		 * @since  0.1
		 * @access public
		 * @var    array the array of default plugin settings
		 */
		public $stock_settings	= array( 'enable-network-settings' => true, 'enable-site-settings' => true, 'enable-pcs-content-widget' => true, 'enable-pcs-list-widget' => true, 'enable-pcs-ajax' => false, 'use-styles' => true );
		/**
		 * @since  0.1
		 * @access public
		 * @var    bool whether to use the built-in plugin style sheet
		 */
		public $use_styles = true;
		/**
		 * @since  0.1
		 * @access public
		 * @var    array the array of attributes for the current shortcode
		 */
		public $shortcode_atts = array();
		/**
		 * @since  0.1
		 * @access public
		 * @var    null|int the ID of the post currently being processed
		 */
		public $current_post_id = null;
		/**
		 * @since  0.1
		 * @access public
		 * @var    null|int the ID of the blog from which the post/list is being retrieved
		 */
		public $current_blog_id = null;
		
		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @since   1.0
		 * @return	\Post_Content_Shortcodes
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				$className = __CLASS__;
				self::$instance = new $className;
			}
			return self::$instance;
		}
		
		/**
		 * Build the post_content_shortcodes object
		 * Set up default options for the plugin, register the shortcodes and widgets
		 *
		 * @access protected
		 * @since  0.1
		 */
		protected function __construct() {
			$this->plugin_dir_name = 'post-content-shortcodes/post-content-shortcodes.php';
			add_action( 'plugins_loaded', array( $this, 'startup' ), 99 );
		}
		
		/**
		 * Perform initial startup actions to build this object
		 *
		 * @access public
		 * @since  1.0
		 * @return void
		 */
		public function startup() {
			add_action( 'init', array( $this, 'load_textdomain' ) );

			$this->_setup_defaults();
			
			/**
			 * Register the two shortcodes
			 */
			add_shortcode( 'post-content', array( &$this, 'post_content' ) );
			add_shortcode( 'post-list', array( &$this, 'post_list' ) );
			/**
			 * Register a shortcode to be used by Views, since the featured image
			 * 		doesn't work properly through Views
			 */
			add_shortcode( 'pcs-thumbnail', array( &$this, 'do_post_thumbnail' ) );
			add_shortcode( 'pcs-post-url', array( &$this, 'do_post_permalink' ) );
			add_shortcode( 'pcs-entry-classes', array( &$this, 'do_entry_classes' ) );
			
			/**
			 * Prepare to register the two widgets
			 */
			add_action( 'widgets_init', array( $this, 'register_widgets' ) );
			/**
			 * Prepare to register the default stylesheet
			 */
			add_action( 'wp_print_styles', array( $this, 'print_styles' ) );
			
			$this->debug( 'Adding the admin_init action' );
			/**
			 * Set up the various admin options items
			 */
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			
			if( $this->is_plugin_active_for_network() )
				add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		/**
		 * Load the plugin's text domain
		 *
		 * @access public
		 * @since  1.0.1
		 * @return bool
		 */
		public function load_textdomain() {
			$this->debug( 'Attempting to load text domain from ' . dirname( plugin_basename( dirname( __FILE__ ) ) ) . '/lang' );

			return load_plugin_textdomain( 'post-content-shortcodes', false, dirname( plugin_basename( dirname( __FILE__ ) ) ) . '/lang' );
		}
		
		/**
		 * Set up the default values for our shortcode attributes
		 * These attributes are used for both shortcodes
		 * @uses apply_filters() to allow filtering the list with the post-content-shortcodes-defaults filter
		 *
		 * @access private
		 * @since  0.1
		 * @return void
		 */
		private function _setup_defaults() {
			global $blog_id;
			$args = array(
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
				// Whether or not skip over the current page/post being displayed
				'exclude_current'=> true,
				// The ID of the blog from which to pull the post(s)
				'blog_id'		=> $blog_id,
				// Whether or not to display the featured image
				'show_image'	=> false,
				// Whether or not to show the content/excerpt of the post(s)
				'show_excerpt'	=> false,
				// The maximum length (in words) of the excerpt
				'excerpt_length'=> 0,
				// The maximum width of the featured image to be displayed
				'image_width'	=> 0,
				// The maximum height of the featured image to be displayed
				'image_height'	=> 0, 
				// Whether or not to show the title with the post(s)
				'show_title'    => false, 
				// Whether or not to show the author's name with the post(s)
				'show_author'   => false, 
				// Whether or not to show the date when the post was published
				'show_date'     => false, 
				/* Added 0.3.3 */
				// Whether or not include the list of comments
				'show_comments' => false, 
				// Whether or not to show the "read more" link at the end of the excerpt
				'read_more' => false, 
				// Whether to include shortcodes in the post content/excerpt
				'shortcodes' => false, 
				/* Added 0.3.4 */
				// Whether to strip out all HTML from the content/excerpt
				'strip_html' => false, 
				/* Added 0.3.4 */
				// A blog name that can be used in place of the blog ID
				'blog' => null,
				/* Added 0.3.4 */
				// A post slug that can be used in place of the post ID
				'post_name' => null, 
				/* Added 0.6 */
				// A taxonomy name to limit content by
				'tax_name' => null, 
				// A list of taxonomy term slugs or IDs to limit content by
				'tax_term' => null, 
				// Whether to wrap the featured image in a link to the post
				'link_image' => false, 
				// Whether to ignore password-protected posts in post list
				'ignore_protected' => false, 
			);
			/**
			 * If this site is using the WP Views plugin, add support for a 
			 * 		completely custom layout using a Views Content Template
			 * @since 0.6
			 */
			if ( class_exists( 'WP_Views_plugin' ) ) {
				$args['view_template'] = null;
			}
			$this->defaults = apply_filters( 'post-content-shortcodes-defaults', $args );
		}
		
		/**
		 * Enqueue the default stylesheet
		 * Only enqueues the stylesheet if the option is set to do so
		 *
		 * @access public
		 * @since  0.1
		 * @return void
		 */
		public function print_styles() {
			$this->_get_options();
			if( $this->settings['use-styles'] )
				wp_enqueue_style( 'pcs-styles', plugins_url( '/styles/default-styles.css', dirname( __FILE__ ) ), array(), $this->version, 'screen' );
		}
		
		/**
		 * Determine whether this is a multinetwork install or not
		 * Will only return true if the is_multinetwork() & the add_mnetwork_option() functions exist
		 *
		 * @access protected
		 * @since  0.1
		 * @return bool whether this is a multi-network install capable of handling multi-network options
		 */
		protected function is_multinetwork() {
			return function_exists( 'is_multinetwork' ) && function_exists( 'add_mnetwork_option' ) && is_multinetwork();
		}
		
		/**
		 * Determine whether this plugin is network active in a multisite install
		 * @uses is_plugin_active_for_network()
		 * @uses is_multisite()
		 *
		 * @access protected
		 * @since  0.1
		 * @return bool whether this is a multisite install with the plugin activated network-wide
		 */
		protected function is_plugin_active_for_network() {
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			$rt = function_exists( 'is_plugin_active_for_network' ) && is_multisite() && is_plugin_active_for_network( $this->plugin_dir_name );
			$fe = function_exists( 'is_plugin_active_for_network' ) ? 'does' : 'does not';
			$im = is_multisite() ? 'is' : 'is not';
			$pa = 'is not';
			if ( function_exists( 'is_plugin_active_for_network' ) )
				$pa = is_plugin_active_for_network( $this->plugin_dir_name ) ? 'is' : 'is not';
			
			/*$this->debug( sprintf( 'Evaluating whether the plugin is active on the network. The primary result is: %s.', print_r( $rt, true ) ) );
			$this->debug( sprintf( 'The function %s exist(s)', $fe ) );
			$this->debug( sprintf( 'This install %s multisite', $im ) );
			$this->debug( sprintf( 'The plugin %s network-active', $pa ) );*/
			return $rt;
		}
		
		/**
		 * Retrieve our options from the database
		 *
		 * @uses \Post_Content_Shortcodes::is_multinetwork()
		 * @uses get_mnetwork_option() if this is multinetwork
		 * @uses get_site_option() if this is network activated in multisite
		 * @uses get_option() if this is active on a single site
		 * @uses \Post_Content_Shortcodes::$stock_settings
		 * @uses \Post_Content_Shortcodes::$settings
		 *
		 * @access protected
		 * @since  0.1
		 * @return void
		 */
		protected function _get_options() {
			$this->debug( 'Stepping into the _get_options method' );
			
			global $blog_id;
			$this->current_blog_id = $blog_id;
			$this->current_post_id = is_singular() ? get_the_ID() : false;
			
			$this->settings = array();
			if ( isset( $_REQUEST['page'] ) && stristr( $_REQUEST['page'], 'post-content-shortcodes' ) ) {
				$this->debug( 'The page param is set, and it looks like this is the right settings page' );
				if ( is_network_admin() ) {
					$this->debug( 'While retrieving settings, it looks like we are in the network admin area' );
					if ( 1 == $GLOBALS['site_id'] && isset( $_REQUEST['page'] ) && 'mn-post-content-shortcodes' == $_REQUEST['page'] )
						$this->settings = get_mnetwork_option( 'pcs-settings', array() );
					else
						$this->settings = get_site_option( 'pcs-settings', array() );
				} elseif ( is_admin() ) {
					$this->debug( 'We are in the normal admin area retrieving settings' );
					$this->settings = get_option( 'pcs-settings', array() );
					if ( $this->is_plugin_active_for_network() ) {
						$tmp = get_site_option( 'pcs-settings', array() );
						if ( array_key_exists( 'enable-site-settings', $tmp ) && false === $tmp['enable-site-settings'] ) {
							$this->settings = $tmp;
							return;
						}
						$this->settings['enable-site-settings'] = true;
					}
				}
				$this->settings = array_merge( $this->stock_settings, $this->settings );
				return;
			}
			
			if ( $this->is_multinetwork() ) {
				$settings = array_merge( $this->stock_settings, get_mnetwork_option( 'pcs-settings', array() ) );
				if ( true === $settings['enable-network-settings'] )
					$tmp = get_site_option( 'pcs-settings', array() );
				if ( true === $settings['enable-site-settings'] )
					$tmp2 = get_option( 'pcs-settings', array() );
				
				if ( ! empty( $tmp2 ) ) {
					$this->settings = array_merge( $this->stock_settings, $tmp2 );
					return;
				}
				
				if ( ! empty( $tmp ) ) {
					$this->settings = array_merge( $this->stock_settings, $tmp );
					return;
				}
				
				$this->settings = $settings;
				return;
			}
			
			if ( $this->is_plugin_active_for_network() ) {
				$settings = array_merge( $this->stock_settings, get_site_option( 'pcs-settings', array() ) );
				if ( true === $settings['enable-site-settings'] )
					$tmp = get_option( 'pcs-settings', array() );
				
				if ( ! empty( $tmp ) ) {
					$this->debug( 'Retrieving and returning individual site settings' );
					$this->settings = array_merge( $this->stock_settings, $tmp );
					$this->settings['enable-site-settings'] = $settings['enable-site-settings'];
					return;
				}
				
				$this->settings = $settings;
				return;
			}
			
			$this->settings = get_option( 'pcs-settings', array() );
			$this->settings = array_merge( $this->stock_settings, $this->settings );
			return;
		}
		
		/**
		 * Register the two widgets
		 * @uses Post_Content_Shortcodes::$settings
		 * @uses register_widget()
		 *
		 * @access public
		 * @since  0.1
		 * @return void
		 */
		public function register_widgets() {
			$this->_get_options();
			if( 'on' == $this->settings['enable-pcs-list-widget'] )
				register_widget( 'PCS_List_Widget' );
			if( 'on' == $this->settings['enable-pcs-content-widget'] )
				register_widget( 'PCS_Content_Widget' );
		}
		
		/**
		 * Set the shortcode attributes to a class variable for access in other methods
		 * @param array $atts the array of attributes to store
		 *
		 * @access private
		 * @since  0.1
		 * @return array the parsed list of attributes
		 */
		private function _get_attributes( $atts=array() ) {
			$this->debug( 'The full list of shortcode attributes before processing looks like: ' . print_r( $atts, true ) );

			foreach ( $atts as $k=>$v ) {
				if ( ! is_string( $v ) ) {
					continue;
				}
				/* When a shortcode is used inside of a Gutenberg Paragraph block, quoted attributes are sent with &quot; on either side of the value */
				$this->debug( 'The ' . $k . ' attribute is currently set to: ' . $v );
				if ( substr( $v, 0, strlen( '&quot;' ) ) == '&quot;' ) {
					$atts[$k] = substr( substr( $v, strlen( '&quot;' ) ), 0, ( 0 - strlen( '&quot;' ) ) );
					$this->debug( 'The updated ' . $k . ' attribute is now set to : ' . $atts[$k] );
				}
			}

			global $blog_id;
			if ( is_array( $atts ) && array_key_exists( 'blog', $atts ) ) {
				if ( is_numeric( $atts['blog'] ) ) {
					$atts['blog_id'] = $atts['blog'];
				} else {
					$tmp = get_id_from_blogname( $atts['blog'] );
					if ( is_numeric( $tmp ) )
						$atts['blog_id'] = $tmp;
				}
			}
			if ( ! array_key_exists( 'blog_id', $atts ) ) {
				$atts['blog_id'] = $blog_id;
			}

			$this->debug( 'The blog ID was set to ' . print_r( $atts['blog_id'], true ) );

			$atts['blog_id'] = intval( $atts['blog_id'] );

			if ( is_array( $atts ) && array_key_exists( 'post_name', $atts ) ) {
				$tmp = $this->get_id_from_post_name( $atts['post_name'], $atts['blog_id'] );
				if ( false !== $tmp )
					$atts['id'] = $tmp;
			}
			$this->shortcode_atts = shortcode_atts( $this->defaults, $atts );
			
			$this->is_true( $this->shortcode_atts['show_excerpt'] );
			$this->is_true( $this->shortcode_atts['show_image'] );
			$this->is_true( $this->shortcode_atts['show_title'] );
			$this->is_true( $this->shortcode_atts['show_author'] );
			$this->is_true( $this->shortcode_atts['show_date'] );
			$this->is_true( $this->shortcode_atts['show_comments'] );
			$this->is_true( $this->shortcode_atts['read_more'] );
			$this->is_true( $this->shortcode_atts['strip_html'] );
			$this->is_true( $this->shortcode_atts['exclude_current'] );
			$this->is_true( $this->shortcode_atts['shortcodes'] );
			$this->is_true( $this->shortcode_atts['link_image'] );
			$this->is_true( $this->shortcode_atts['ignore_protected'] );
			
			return $this->shortcode_atts;
		}
		
		/**
		 * Retrieve a post ID based on its slug
		 * @param string $post_name the slug of the post being retrieved
		 * @param int    $blog the ID of the site from which to pull the post
		 *
		 * @access public
		 * @since  0.1
		 * @return bool|int false if not found; the ID of the appropriate post if it is found
		 */
		public function get_id_from_post_name( $post_name, $blog=0 ) {
			global $blog_id, $wpdb;
			if ( empty( $blog ) || $blog == $blog_id ) {
				$ID = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name=%s LIMIT 1", $post_name ) );
				if ( is_numeric( $ID ) )
					return $ID;
				else
					return false;
			}
			switch_to_blog( $blog );
			$ID = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name=%s LIMIT 1", $post_name ) );
			restore_current_blog();
			if ( is_numeric( $ID ) )
				return $ID;
			else
				return false;
		}
		
		/**
		 * Handle the shortcode to display another post's content
		 * @param array $atts the array of shortcode attributes
		 *
		 * @todo Extract HTML into separate template file, so it can be easily overridden by site owners/developers
		 *
		 * @uses $wpdb
		 * @uses shortcode_atts()
		 * @uses $post to determine the ID of the current post in the loop
		 * @uses Post_Content_Shortcodes::get_post_from_blog() to retrieve the appropriate post
		 * @uses Post_Content_Shortcodes::is_true() to make sure all of the settings are proper boolean values
		 * @uses get_option()
		 * @uses get_userdata()
		 * @uses mysql2date()
		 * @uses force_balance_tags()
		 * @uses Post_Content_Shortcodes::get_the_post_thumbnail() to retrieve the featured image
		 *
		 * @uses apply_filters() to filter the post-content-shortcodes-no-posts-error error 
		 * 		message that appears when no posts are retrieved
		 * @uses apply_filters() to filter the "read more" link with the 
		 * 		post-content-shortcodes-read-more filter
		 * @uses apply_filters() to filter the featured image size with the 
		 * 		post-content-shortcodes-default-image-size filter
		 * @uses apply_filters() to assign a specific CSS class to the featured image with the 
		 * 		post-content-shortcodes-image-class filter
		 * @uses apply_filters() to filter the output where the post author and date would 
		 * 		normally appear with the post-content-shortcodes-meta filter
		 * @uses apply_filters() to filter the title of the post with the 
		 * 		post-content-shortcodes-title filter
		 * @uses apply_filters() to filter the final HTML output with the 
		 * 		post-content-shortcodes-content filter
		 *
		 * @access public
		 * @since  0.1
		 * @return string the final HTML for the post
		 */
		public function post_content( $atts=array() ) {
			do_action( 'pcs_starting_post_content' );
			
			global $wpdb;
			$this->shortcode_atts = $this->_get_attributes( $atts );
			
			extract( $this->shortcode_atts );
			/**
			 * Attempt to avoid an endless loop
			 */
			if( ( is_array( $this->shortcode_atts ) && array_key_exists( 'exclude_current', $this->shortcode_atts ) && 'Do not exclude' !== $this->shortcode_atts['exclude_current'] ) && ( $id == $GLOBALS['post']->ID || empty( $id ) ) ) {
				do_action( 'pcs_ending_post_content' );
				return '';
			}
			
			/**
			 * Output a little debug info if necessary
			 */
			$this->debug( sprintf( 'Preparing to retrieve post content with the following args: %s', print_r( $this->shortcode_atts, true ) ) );
			
			$p = $this->get_post_from_blog( $id, $blog_id );
			if( empty( $p ) || is_wp_error( $p ) ) {
				do_action( 'pcs_ending_post_content' );
				return apply_filters( 'post-content-shortcodes-no-posts-error', __( '<p>No posts could be found that matched the specified criteria.</p>', 'post-content-shortcodes' ), $this->get_args( $this->shortcode_atts ) );
			}
			
			/**
			 * If Views is active, and the user has chosen to use a Content Template, 
			 * 		render that instead of the default layout
			 */
			if ( function_exists( 'render_view_template' ) ) {
				if ( array_key_exists( 'view_template', $this->shortcode_atts ) && ! empty( $this->shortcode_atts['view_template'] ) ) {
					do_action( 'pcs_ending_post_content' );
					return $this->do_view_template( $p );
				}
			}
			
			$post_date = mysql2date( get_option( 'date_format' ), $p->post_date );
			$post_author = get_userdata( $p->post_author );
			if ( empty( $post_author ) || ! is_object( $post_author ) || ! isset( $post_author->display_name ) ) {
				$post_author = (object) array( 'display_name' => '' );
				$show_author = false;
			}
			
			if ( true !== $shortcodes ) {
				$p->post_content = strip_shortcodes( $p->post_content );
				$p->post_excerpt = strip_shortcodes( $p->post_excerpt );
			}
			
			if ( $strip_html ) {
				if ( property_exists( $p, 'post_content' ) && ! empty( $p->post_content ) )
					$p->post_content = strip_tags( apply_filters( 'the_content', $p->post_content, $p, $this->shortcode_atts ) );
				if ( property_exists( $p, 'post_excerpt' ) && ! empty( $p->post_excerpt ) )
					$p->post_excerpt = strip_tags( apply_filters( 'the_excerpt', $p->post_excerpt, $p, $this->shortcode_atts ) );
			}
			
			$content = $p->post_content;
			
			if ( $show_excerpt ) {
				$content = empty( $p->post_excerpt ) ? $p->post_content : $p->post_excerpt;
			}
			
			if ( intval( $excerpt_length ) && intval( $excerpt_length ) < str_word_count( $content ) ) {
				$content = explode( ' ', $content );
				$content = implode( ' ', array_slice( $content, 0, ( intval( $excerpt_length ) - 1 ) ) );
				$content = force_balance_tags( $content );
				$content .= apply_filters( 'post-content-shortcodes-read-more', ' <span class="read-more"><a href="' . get_permalink( $p->ID ) . '" title="' . apply_filters( 'the_title_attribute', $p->post_title, $p, $this->shortcode_atts ) . '">' . __( 'Read more', 'post-content-shortcodes' ) . '</a></span>', $p, $this->shortcode_atts );
			}
			
			if ( $show_image ) {
				if ( empty( $image_height ) && empty( $image_width ) ) {
					$image_size = apply_filters( 'post-content-shortcodes-default-image-size', 'thumbnail', $p, $this->shortcode_atts );
				} else {
					if ( empty( $image_height ) )
						$image_height = 9999999;
					if ( empty( $image_width ) )
						$image_width = 9999999;
					$image_size = array( intval( $image_width ), intval( $image_height ) );
				}
				
				if ( $link_image ) {
					$link = get_permalink( $p->ID );
					$p->post_thumbnail_linked = sprintf( '<a href="%s">%s</a>', $link, $p->post_thumbnail );
					
					$content = apply_filters( 'post-content-shortcodes-include-thumbnail', $p->post_thumbnail_linked . $content, $p->post_thumbnail, $content, $p, $this->shortcode_atts );
				} else {
					$content = apply_filters( 'post-content-shortcodes-include-thumbnail', $p->post_thumbnail . $content, $p->post_thumbnail, $content, $p, $this->shortcode_atts );
				}
			}
			
			if ( $show_comments ) {
				$content .= $p->post_comments;
			}
			
			if ( $show_date && $show_author )
				$content = apply_filters( 'post-content-shortcodes-meta', '<p class="post-meta">' . sprintf( __( 'Posted by <span class="post-author">%1$s</span> on <span class="post-date">%2$s</a>', 'post-content-shortcodes' ), $post_author->display_name, $post_date ) . '</p>', $p, $this->shortcode_atts ) . $content;
			elseif ( $show_date )
				$content = apply_filters( 'post-content-shortcodes-meta', '<p class="post-meta">' . sprintf( __( 'Posted on %2$s', 'post-content-shortcodes' ), $post_author->display_name, $post_date ) . '</p>', $p, $this->shortcode_atts ) . $content;
			elseif ( $show_author )
				$content = apply_filters( 'post-content-shortcodes-meta', '<p class="post-meta">' . sprintf( __( 'Posted by %s', 'post-content-shortcodes' ), $post_author->display_name, $post_date ) . '</p>', $p, $this->shortcode_atts ) . $content;
			
			if ( $show_title )
				$content = apply_filters( 'post-content-shortcodes-title', '<h2>' . $p->post_title . '</h2>', $p->post_title, $p, $this->shortcode_atts ) . $content;
			
			do_action( 'pcs_ending_post_content' );
			
			return apply_filters( 'post-content-shortcodes-content', apply_filters( 'the_content', $content, $p, $this->shortcode_atts ), $p, $this->shortcode_atts );
		}
		
		/**
		 * Output the post content using a specified Content Template
		 * @param $p object the Post object
		 *
		 * @access public
		 * @since  0.6
		 * @return string the rendered View content template
		 */
		public function do_view_template( $p=null ) {
			if ( ! function_exists( 'render_view_template' ) ) {
				return '';
			}
			if ( empty( $p ) ) {
				return '';
			}
			
			global $post;
			setup_postdata( $p );
			
			$rt = render_view_template( $this->shortcode_atts['view_template'], $p );
			
			wp_reset_postdata();
			
			return $rt;
		}
		
		/**
		 * Handle the shortcode to display a list of posts
		 * @param array $atts the array of shortcode attributes
		 *
		 * @todo Extract HTML to external template to make it easier for theme devs/site owners to override
		 *       
		 * @uses shortcode_atts() to parse the default/allowed attributes
		 * @uses Post_Content_Shortcodes::get_args()
		 * @uses Post_Content_Shortcodes::get_posts_from_blog()
		 * @uses mysql2date()
		 * @uses get_option()
		 * @uses get_userdata()
		 * @uses Post_Content_Shortcodes::get_shortlink_from_blog()
		 * @uses force_balance_tags()
		 * @uses has_post_thumbnail()
		 * @uses get_the_post_thumbnail()
		 * @uses strip_shortcodes()
		 *
		 * @uses apply_filters() to filter the error message that's displayed when no 
		 * 		posts are retrieved with the post-content-shortcodes-no-posts-error filter
		 * @uses apply_filters() to filter the HTML element that's used to open the list with 
		 * 		the post-content-shortcodes-open-list filter
		 * @uses apply_filters() to filter the HTML element that's used to open each list item 
		 * 		with the post-content-shortcodes-open-item filter
		 * @uses apply_filters() to filter the HTML element that's used to open each link with 
		 * 		the post-content-shortcodes-item-link-open filter
		 * @uses apply_filters() to filter the HTML element that's used to close each link
		 * 		with the post-content-shortcodes-item-link-close filter
		 * @uses apply_filters() to filter the author and date meta information that's 
		 * 		displayed with the post-content-shortcodes-meta filter
		 * @uses apply_filters() to filter the "Read more" link that's displayed at the end 
		 * 		of each excerpt with the post-content-shortcodes-read-more filter
		 * @uses apply_filters() to filter the HTML output for each list item with the 
		 * 		post-content-shortcodes-list-excerpt filter
		 * @uses apply_filters() to filter the HTML element that's used to close each list item 
		 * 		with the post-content-shortcodes-close-item filter
		 * @uses apply_filters() to filter the HTML element that's used to close the list with the 
		 * 		post-content-shortcodes-close-list filter
		 *
		 * @access public
		 * @since  0.1
		 * @return string the final HTML output for the list
		 */
		public function post_list( $atts=array() ) {
			do_action( 'pcs_starting_post_list' );
			
			if ( ! is_array( $atts ) )
				$atts = array();
			
			$args = $atts;
			
			/** 
			 * Set this shortcode to display the post title by default
			 */
			if ( ! array_key_exists( 'show_title', $atts ) )
				$atts['show_title'] = true;
			
			$atts = $this->_get_attributes( $atts );
			$atts['posts_per_page'] = $atts['numberposts'];
			$this->shortcode_atts = $atts;
			
			$args = array_diff_key( $args, $atts );
			$atts['tax_query'] = array();
			
			if ( isset( $atts['tax_term'] ) && ! empty( $atts['tax_term'] ) && isset( $atts['tax_name'] ) && ! empty( $atts['tax_name'] ) ) {
				$terms = explode( ' ', $atts['tax_term'] );
				if ( count( $terms ) > 0 ) {
					if ( 'tag' == $atts['tax_name'] ) {
						if ( is_numeric( $terms[0] ) ) {
							$atts['tag__in'] = $terms;
						} else {
							$atts['tag_slug__in'] = $terms;
						}
					} else if ( 'category' == $atts['tax_name'] ) {
						if ( is_numeric( $terms[0] ) ) {
							$atts['cat'] = implode( ',', $terms );
						} else {
							$atts['category_name'] = implode( ',', $terms );
						}
					} else {
						if ( is_numeric( $terms[0] ) ) {
							$field = 'id';
							$terms = array_map( 'absint', $terms );
						} else {
							$field = 'slug';
						}
							
						$atts['tax_query'][] = array( 'taxonomy' => $atts['tax_name'], 'field' => $field, 'terms' => $terms );
					}
				}
			}

			$excluded_tax = apply_filters( 'post-content-shortcodes-excluded-taxonomies', array(
				'csb_visibility',
				'csb_clone',
			) );

			foreach ( $args as $k => $v ) {
				if ( 'view_template' == $k || in_array( $k, $excluded_tax ) ) {
					continue;
				}
				if ( is_numeric( $v ) )
					$atts['tax_query'][] = array( 'taxonomy' => $k, 'field' => 'id', 'terms' => intval( $v ) );
				else
					$atts['tax_query'][] = array( 'taxonomy' => $k, 'field' => 'slug', 'terms' => $v );
			}
			
			if ( isset( $atts['category'] ) ) {
				if ( is_numeric( $atts['category'] ) )
					$atts['cat'] = $atts['category'];
				else
					$atts['category_name'] = $atts['category'];
			}
			
			/**
			 * Output a little debug info if necessary
			 */
			$this->debug( sprintf( 'Preparing to retrieve post list with the following args: %s', print_r( $atts, true ) ) );
				
			$posts = $this->get_posts_from_blog( $atts, $atts['blog_id'] );
			if( empty( $posts ) ) {
				do_action( 'pcs_ending_post_list' );
				return apply_filters( 'post-content-shortcodes-no-posts-error', __( '<p>No posts could be found that matched the specified criteria.</p>', 'post-content-shortcodes' ), $this->get_args( $atts ) );
			}
			
			/**
			 * If the user is using the WP Views plugin and specified a Content Template 
			 * 		to use for the results, use that instead of the default layout
			 * @since 0.6
			 */
			if ( function_exists( 'render_view_template' ) ) {
				if ( array_key_exists( 'view_template', $this->shortcode_atts ) && ! empty( $this->shortcode_atts['view_template'] ) ) {
					$output = apply_filters( 'post-content-shortcodes-views-template-opening', '<div class="post-list">' );
					add_filter( 'post_thumbnail_html', array( $this, 'do_post_thumbnail' ), 99 );
					add_filter( 'post_link', array( $this, 'do_post_permalink' ), 99 );
					$this->shortcode_atts['post_number'] = 1;
					foreach ( $posts as $p ) {
						$output .= $this->do_view_template( $p );
						$this->shortcode_atts['post_number'] ++;
					}
					remove_filter( 'post_thumbnail_html', array( $this, 'do_post_thumbnail' ), 99 );
					remove_filter( 'post_link', array( $this, 'do_post_permalink' ), 99 );
					$output .= apply_filters( 'post-content-shortcodes-views-template-closing', '</div>' );
					do_action( 'pcs_ending_post_list' );
					
					return $output;
				}
			}
			
			$output = apply_filters( 'post-content-shortcodes-open-list', '<ul class="post-list' . ( $atts['show_excerpt'] ? ' with-excerpt' : '' ) . ( $atts['show_image'] ? ' with-image' : '' ) . '">', $atts );
			foreach( $posts as $p ) {
				if ( ! is_object( $p ) )
					continue;
				
				if ( $atts['strip_html'] ) {
					if ( property_exists( $p, 'post_content' ) && ! empty( $p->post_content ) ) 
						$p->post_content = strip_tags( apply_filters( 'the_content', $p->post_content, $p, $atts ) );
					if ( property_exists( $p, 'post_excerpt' ) && ! empty( $p->post_excerpt ) )
						$p->post_excerpt = strip_tags( apply_filters( 'the_excerpt', $p->post_excerpt, $p, $atts ) );
				}
				
				$post_date = mysql2date( get_option( 'date_format' ), $p->post_date );
				$post_author = get_userdata( $p->post_author );
				$show_author = $atts['show_author'];
				if ( empty( $post_author ) || ! is_object( $post_author ) || ! isset( $post_author->display_name ) ) {
					$post_author = (object) array( 'display_name' => '' );
					$show_author = false;
				}
				
				$li_classes = 'listed-post';
				if ( $this->current_blog_id == $atts['blog_id'] && $this->current_post_id == $p->ID ) {
					$li_classes .= ' current-post-item';
				}
				$output .= apply_filters( 'post-content-shortcodes-open-item', sprintf( '<li class="%s">', $li_classes ), $p->ID, $atts );
				if ( $atts['show_title'] ) {
					/** 
					 * Applying filters to the link opening tag
					 * @uses apply_filters() to filter the title attribute
					 * @uses apply_filters() to filter the permalink
					 * @uses apply_filters() to filter the title attribute again?
					 * This portion of code probably applies filters too many times, but
					 * 		it is being left the way it is in order to preserve backward-compatibility
					 */
					$output .= apply_filters( 
						'post-content-shortcodes-item-link-open', 
						'<a class="pcs-post-title" href="' . $this->get_shortlink_from_blog( $p->ID, $atts['blog_id'] ) . '" title="' . 
						apply_filters( 
							'the_title_attribute', 
							$p->post_title 
						) . 
						'">', 
						apply_filters( 
							'the_permalink', 
							get_permalink( $p->ID ) 
						), 
						apply_filters( 
							'the_title_attribute', 
							$p->post_title 
						), 
						$p, 
						$atts 
					);
					$output .= apply_filters( 'the_title', $p->post_title, $p, $atts );
					$output .= apply_filters( 'post-content-shortcodes-item-link-close', '</a>', $atts );
				}
				if ( $atts['show_author'] && $atts['show_date'] ) {
					$output .= apply_filters( 'post-content-shortcodes-meta', '<p class="post-meta">' . sprintf( __( 'Posted by <span class="post-author">%1$s</span> on <span class="post-date">%2$s</a>', 'post-content-shortcodes' ), $post_author->display_name, $post_date ) . '</p>', $p, $atts );
				} elseif ( $atts['show_date'] ) {
					$output .= apply_filters( 'post-content-shortcodes-meta', '<p class="post-meta">' . sprintf( __( 'Posted on <span class="post-date">%2$s</a>', 'post-content-shortcodes' ), $post_author->display_name, $post_date ) . '</p>', $p, $atts );
				} elseif ( $atts['show_author'] ) {
					$output .= apply_filters( 'post-content-shortcodes-meta', '<p class="post-meta">' . sprintf( __( 'Posted by <span class="post-author">%1$s</span>', 'post-content-shortcodes' ), $post_author->display_name, $post_date ) . '</p>', $p, $atts );
				}
				
				if( $atts['show_excerpt'] ) {
					$output .= '<div class="pcs-excerpt-wrapper">';
					if ( stristr( $p->post_content, '<!--more-->' ) )
						$p->post_content = force_balance_tags( substr( $p->post_content, 0, stripos( $p->post_content, '<!--more-->' ) ) );
				}
				if( $atts['show_image'] ) {
					if ( $atts['link_image'] ) {
						$output .= '<a href="' . $this->get_shortlink_from_blog( $p->ID, $atts['blog_id'] ) . '">' . $p->post_thumbnail . '</a>';
					} else {
						$output .= $p->post_thumbnail;
					}
				}
				if( $atts['show_excerpt'] ) {
					$excerpt = empty( $p->post_excerpt ) ? $p->post_content : $p->post_excerpt;
					if( ! empty( $atts['excerpt_length'] ) && is_numeric( $atts['excerpt_length'] ) ) {
						if ( ! $atts['shortcodes'] )
							$excerpt = strip_shortcodes( $excerpt );
						
						$excerpt = apply_filters( 'the_excerpt', $excerpt );
						if( str_word_count( $excerpt ) > $atts['excerpt_length'] ) {
							$excerpt = explode( ' ', $excerpt );
							$excerpt = implode( ' ', array_slice( $excerpt, 0, ( $atts['excerpt_length'] - 1 ) ) );
							$excerpt = force_balance_tags( $excerpt );
						}
					}
					$read_more = $atts['read_more'] ? 
						apply_filters( 'post-content-shortcodes-read-more', ' <span class="read-more"><a href="' . $this->get_shortlink_from_blog( $p->ID, $atts['blog_id'] ) . '" title="' . apply_filters( 'the_title_attribute', $p->post_title ) . '">' . __( 'Read more', 'post-content-shortcodes' ) . '</a></span>', $p, $atts ) :
						'';
					$output .= '<div class="pcs-excerpt">' . apply_filters( 'post-content-shortcodes-list-excerpt', apply_filters( 'the_content', $excerpt . $read_more ), $p, $atts ) . '</div></div>';
				}
				if ( $atts['show_comments'] ) {
					$output .= $p->post_comments;
				}
				$output .= apply_filters( 'post-content-shortcodes-close-item', '</li>', $atts );
			}
			$output .= apply_filters( 'post-content-shortcodes-close-list', '</ul>', $atts );
			
			do_action( 'pcs_ending_post_list' );
			
			return $output;
		}
		
		/**
		 * Retrieve a post from a specific blog/site
		 * @param int $post_id the ID of the post to be retrieved
		 * @param int $blog_id the ID of the blog/site from which to retrieve it
		 *
		 * @uses $blog_id to determine whether we're on the appropriate site/blog already
		 * @uses get_transient() to retrieve the cached version of the post if relevant
		 * @uses $wpdb
		 * @uses WPDB::set_blog_id() to switch to the appropriate site
		 * @uses WPDB::get_row to retrieve the post from the database
		 * @uses set_transient() to set a cache version of the post
		 * @uses apply_filters() to filter the amount of time the transient is valid with the 
		 * 		pcsc-transient-timeout filter
		 *
		 * @access public
		 * @since  0.1
		 * @return null|\WP_Post the post object
		 */
		public function get_post_from_blog( $post_id=0, $blog_id=0 ) {
			if ( empty( $this->shortcode_atts['image_height'] ) && empty( $this->shortcode_atts['image_width'] ) ) {
				$image_size = apply_filters( 'post-content-shortcodes-default-image-size', 'thumbnail', $this->shortcode_atts );
			} else {
				if ( empty( $this->shortcode_atts['image_height'] ) )
					$this->shortcode_atts['image_height'] = 9999999;
				if ( empty( $this->shortcode_atts['image_width'] ) )
					$this->shortcode_atts['image_width'] = 9999999;
				$image_size = array( intval( $this->shortcode_atts['image_width'] ), intval( $this->shortcode_atts['image_height'] ) );
			}
			
			if( empty( $post_id ) )
				return null;
			
			if( ! is_multisite() || $blog_id == $GLOBALS['blog_id'] || empty( $blog_id ) ) {
				$p = get_post( $post_id );
				if ( has_post_thumbnail( $post_id ) )
					$p->post_thumbnail = get_the_post_thumbnail( $post_id, $image_size, array( 'class' => apply_filters( 'post-content-shortcodes-image-class', 'pcs-featured-image', $p, $this->shortcode_atts ) ) );
				else
					$p->post_thumbnail = '';
				
				$p->post_comments = $this->do_comments( $p );
				
				return $p;
			}
			
			if ( isset( $_GET['delete_transients'] ) )
				delete_transient( 'pcsc-blog' . $blog_id . '-post' . $post_id );
			
			if( false !== ( $p = get_transient( 'pcsc-blog' . $blog_id . '-post' . $post_id ) ) )
				return $p;
			
			$org_blog = switch_to_blog( $blog_id );
			$p = get_post( $post_id );
			if ( has_post_thumbnail( $post_id ) && $this->shortcode_atts['show_image'] ) {
				$p->post_thumbnail = get_the_post_thumbnail( $post_id, $image_size, array( 'class' => apply_filters( 'post-content-shortcodes-image-class', 'pcs-featured-image', $p, $this->shortcode_atts ) ) );
			} else {
				$p->post_thumbnail = '';
			}
			
			$p->post_comments = $this->do_comments( $p );
			restore_current_blog();
			
			set_transient( 'pcsc-blog' . $blog_id . '-post' . $post_id, $p, apply_filters( 'pcsc-transient-timeout', 60 * 60 ) );
			
			return $p;
		}
		
		/**
		 * Retrieve a batch of posts from a specific blog
		 * @param array $atts the array of shortcode attributes
		 * @param int $blog_id the ID of the blog/site from which to pull the posts
		 * @uses Post_Content_Shortcodes::get_args()
		 * @uses get_posts()
		 * @uses get_transient() to retrieve a cached list of posts if valid
		 * @uses $wpdb
		 * @uses WPDB::set_blog_id() to switch to the appropriate site
		 * @uses set_transient() to cache the list of posts
		 * @uses apply_filters() to filter the amount of time the transient is cached with 
		 * 		the pcsc-transient-timeout filter
		 *
		 * @access public
		 * @since  0.1
		 * @return \WP_Post[] the array of post objects
		 */
		public function get_posts_from_blog( $atts=array(), $blog_id=0 ) {
			if ( empty( $this->shortcode_atts['image_height'] ) && empty( $this->shortcode_atts['image_width'] ) ) {
				$image_size = apply_filters( 'post-content-shortcodes-default-image-size', 'thumbnail', $this->shortcode_atts );
			} else {
				if ( empty( $this->shortcode_atts['image_height'] ) )
					$this->shortcode_atts['image_height'] = 9999999;
				if ( empty( $this->shortcode_atts['image_width'] ) )
					$this->shortcode_atts['image_width'] = 9999999;
				$image_size = array( intval( $this->shortcode_atts['image_width'] ), intval( $this->shortcode_atts['image_height'] ) );
			}
			
			$args = $this->get_args( $atts );
			
			if( ! is_multisite() || $blog_id == $GLOBALS['blog_id'] || empty( $blog_id ) || ! is_numeric( $blog_id ) ) {
				$posts = get_posts( $args );
				if ( false === $this->shortcode_atts['show_image'] )
					return $posts;
				
				foreach ( $posts as $key => $p ) {
					if ( has_post_thumbnail( $p->ID ) )
						$posts[$key]->post_thumbnail = get_the_post_thumbnail( $p->ID, $image_size, array( 'class' => apply_filters( 'post-content-shortcodes-image-class', 'pcs-featured-image', $posts[$key], $this->shortcode_atts ) ) );
					else
						$posts[$key]->post_thumbnail = '';
						
					$posts[$key]->post_comments = $this->do_comments( $p );
				}
				
				return $posts;
			}
			
			$args['cache_results'] = false;
			
			if ( isset( $_GET['delete_transients'] ) )
				delete_transient( 'pcsc-list-blog' . $blog_id . '-args' . md5( maybe_serialize( $args ) ) );
			
			if( false !== ( $p = get_transient( 'pcsc-list-blog' . $blog_id . '-args' . md5( maybe_serialize( $args ) ) ) ) )
				return $p;
			
			$org_blog = switch_to_blog( $blog_id );
			if ( array_key_exists( 'tax_query', $args ) ) {
				$this->check_taxonomies( $args['tax_query'], $atts['post_type'] );
			}
			$posts = get_posts( $args );
			
			if ( false !== $this->shortcode_atts['show_image'] ) {
				foreach ( $posts as $key => $p ) {
					if ( has_post_thumbnail( $p->ID ) )
						$posts[$key]->post_thumbnail = get_the_post_thumbnail( $p->ID, $image_size, array( 'class' => apply_filters( 'post-content-shortcodes-image-class', 'pcs-featured-image', $posts[$key], $this->shortcode_atts ) ) );
					else
						$posts[$key]->post_thumbnail = '';
				}
			}
			if ( false !== $this->shortcode_atts['show_comments'] ) {
				foreach ( $posts as $key => $p ) {
					$posts[$key]->post_comments = $this->do_comments( $p );
				}
			}
			restore_current_blog();
			
			set_transient( 'pcsc-list-blog'. $blog_id . '-args' . md5( maybe_serialize( $args ) ), $posts, apply_filters( 'pcsc-transient-timeout', 60 * 60 ) );
			return $posts;
		}
		
		/**
		 * Check to make sure necessary taxonomies exist
		 * If we've switched blogs, and the query needs to retrieve items associated 
		 * 		with a specific taxonomy and/or term, we need to make sure that 
		 * 		taxonomy is registered on the new blog before the query will work
		 * @param array $tax_query the tax_query argument for the query
		 * @param string $post_type the type of post/object being queried
		 *
		 * @access public
		 * @since  0.3.4.1
		 * @return void
		 */
		public function check_taxonomies( $tax_query=null, $post_type=null ) {
			if ( empty( $tax_query ) )
				return;
			if ( empty( $post_type ) )
				$post_type = 'post';
			
			foreach ( $tax_query as $tq ) {
				$taxes = get_taxonomies( array( 'name' => $tq['taxonomy'] ), 'names' );
				if ( ! empty( $taxes ) && ! is_wp_error( $taxes ) )
					continue;
				
				register_taxonomy( $tq['taxonomy'], $post_type );
			}
		}
		
		/**
		 * Retrieve the featured image HTML for the current post
		 * @param int $post_ID the ID of the post for which to retrieve the image
		 * @param string|array $image_size the size of the image to retrieve
		 * @param array $attr the extra attributes to assign to the image
		 * @param int $blog_id the ID of the site from which the post is being pulled
		 * @see get_the_post_thumbnail()
		 *
		 * @uses get_the_post_thumbnail() if the post is in the current site/blog
		 * @uses $wpdb
		 * @uses WPDB::set_blog_id() to change to the proper site/blog
		 * @uses WPDB::get_var() to retrieve the thumbnail ID
		 * @uses wp_get_attachment_image() to retrieve the HTML for the featured image
		 *
		 * @deprecated since 0.3.4
		 *
		 * @access public
		 * @since  0.1
		 * @return string the HTML for the image element
		 */
		function get_the_post_thumbnail( $post_ID, $image_size = 'thumbnail', $attr = array(), $blog_id = 0 ) {
			_deprecated_function( '\Post_Content_Shortcodes::get_the_post_thumbnail', '0.3.4', '' );
			return '';
			
			if ( empty( $blog_id ) || (int) $blog_id === (int) $GLOBALS['blog_id'] )
				return get_the_post_thumbnail( $post_ID, $image_size, $attr );
			if ( ! is_numeric( $post_ID ) || ! is_numeric( $blog_id ) )
				return '';
			
			$old = switch_to_blog( $blog_id );
			$post_thumbnail_id = get_post_meta( $post_ID, '_thumbnail_id', true );
			if ( empty( $post_thumbnail_id ) )
				return '';
			$html = get_the_post_thumbnail( $post_ID, $image_size, $attr );
			restore_current_blog();
			return $html;
			
			global $wpdb;
			$old = $wpdb->set_blog_id( $blog_id );
			$post_thumbnail_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key=%s AND post_id=%d LIMIT 1", '_thumbnail_id', $post_ID ) );
			if ( empty( $post_thumbnail_id ) )
				return '';
			$html = wp_get_attachment_image( $post_thumbnail_id, $image_size, false, $attr );
			$wpdb->set_blog_id( $old );
			return $html;
		}
		
		/**
		 * Output any comments on a post
		 * @param post $newpost the post object for which to display comments
		 * @uses comments_template() to output the comment template
		 *
		 * @access public
		 * @since  0.1
		 * @return string the HTML for the comments
		 */
		public function do_comments( $newpost ) {
			global $post;
			if ( is_object( $post ) )
				$tmpp = clone $post;
			$post = $newpost;
			ob_start();
			comments_template();
			$rt = ob_get_clean();
			if ( isset( $tmpp ) )
				$post = clone $tmpp;
			
			return $rt;
		}
		
		/**
		 * Determine the shortlink to a post on a specific blog
		 * @param int $post_id the ID of the post to retrieve
		 * @param int $blog_id the ID of the blog/site from which to retrieve the post
		 * @uses $wpdb
		 * @uses WPDB::get_row()
		 *
		 * @access public
		 * @since  0.1
		 * @return string the URL to the post
		 */
		public function get_shortlink_from_blog( $post_id=0, $blog_id=0 ) {
			if( empty( $post_id ) )
				return '';
			if( empty( $blog_id ) || $blog_id == $GLOBALS['blog_id'] || !is_numeric( $blog_id ) )
				return apply_filters( 'the_permalink', get_permalink( $post_id ) );
			
			global $wpdb;
			$blog_info = $wpdb->get_row( $wpdb->prepare( "SELECT domain, path FROM {$wpdb->blogs} WHERE blog_id=%d", $blog_id ), ARRAY_A );
			return 'http://' . $blog_info['domain'] . $blog_info['path'] . '?p=' . $post_id;
		}
		
		/**
		 * Determine whether a variable evaluates to boolean true
		 * @param mixed &$var the variable to be evaluated
		 *
		 * @access public
		 * @since  0.1
		 * @return void
		 */
		function is_true( &$var ) {
			if( in_array( $var, array( 'true', true, 1, '1' ), true ) ) {
				$var = true;
			} else {
				$var = false;
			}
			
			return;
		}
		
		/**
		 * Build the list of get_posts() args
		 * @param array $atts the array of attributes to evaluate
		 *
		 * @access public
		 * @since  0.1
		 * @return array the parsed array of attributes
		 */
		public function get_args( $atts=array() ) {
			if ( ! is_array( $atts ) )
				$atts = maybe_unserialize( $atts );
			if ( ! is_array( $atts ) )
				return $atts;
			
			unset( $atts['id'] );
			
			if ( $atts['exclude_current'] && $GLOBALS['blog_id'] != $atts['blog_id'] ) {
				if ( ! empty( $atts['exclude'] ) ) {
					if ( ! is_array( $atts['exclude'] ) )
						$atts['exclude'] = array_map( 'trim', explode( ',', $atts['exclude'] ) );
					
					$atts['exclude'][] = $GLOBALS['post']->ID;
				} else {
					$atts['exclude'] = array( $GLOBALS['post']->ID );
				}
			}
			
			$atts['orderby'] = str_replace( 'post_', '', $atts['orderby'] );
			
			if ( true === $atts['ignore_protected'] ) {
				$atts['has_password'] = false;
			}
			
			unset( $atts['blog_id'], $atts['exclude_current'], $atts['tax_name'], $atts['tax_term'], $atts['view_template'], $atts['show_image'], $atts['image_width'], $atts['image_height'] );
			
			$atts = array_filter( $atts );
			
			/**
			 * Output a little debug info if necessary
			 */
			$this->debug( sprintf( 'Preparing to return filtered args: %s', print_r( $atts, true ) ) );
			
			return $atts;
		}
		
		/**
		 * Build and return the featured image for the current post
		 * @param null|string $html the current HTML for the post
		 *
		 * @access public
		 * @since  0.6
		 * @return null|string
		 */
		public function do_post_thumbnail( $html=null ) {
			if ( $GLOBALS['blog_id'] == $this->shortcode_atts['blog_id'] && ! empty( $html ) )
				return $html;
			
			return $GLOBALS['post']->post_thumbnail;
		}
		
		/**
		 * Attempt to build the URL for the post being output
		 * @param string $link the current URL for the post
		 *
		 * @access public
		 * @since  0.5
		 * @return string
		 */
		public function do_post_permalink( $link ) {
			if ( $GLOBALS['blog_id'] == $this->shortcode_atts['blog_id'] && ! empty( $link ) )
				return $link;
			
			return $this->get_shortlink_from_blog( $GLOBALS['post']->ID, $this->shortcode_atts['blog_id'] );
		}
		
		/**
		 * Compile and return the list of CSS classes for the specific entry being displayed
		 * @param array $atts the attributes for the current shortcode being processed
		 *
		 * @access public
		 * @since  0.1
		 * @return string the full list of CSS classes
		 */
		public function do_entry_classes( $atts=array() ) {
			$atts = shortcode_atts( array( 'classes' => '', 'columns' => 0 ), $atts );
			$classes = explode( ' ', $atts['classes'] );
			if ( is_numeric( $atts['columns'] ) && $atts['columns'] > 0 ) {
				$col = $this->shortcode_atts['post_number'] % $atts['columns'];
				
				if ( 1 == $col ) {
					$classes[] = 'first';
				}
				
				$classes[] = $col > 0 ? sprintf( 'column-%d', $col ) : sprintf( 'column-%d', $atts['columns'] );
			}
			
			return implode( ' ', $classes );
		}
		
		/**
		 * Output/log a debug message if appropriate
		 *
		 * @param $message string the message that should be logged/output
		 * @param $log     bool whether to log the message or output it
		 *
		 * @access protected
		 * @since  1.0
		 * @return void
		 */
		protected function debug( $message, $log=true ) {
			if ( ( ! defined( 'WP_DEBUG' ) || true !== WP_DEBUG ) && ! isset( $_REQUEST['pcs-debug'] ) ) {
				return;
			}
			
			if ( $log ) {
				error_log( sprintf( '[PCS Debug Message]: %s', $message ) );
			} else {
				printf( "\n<!-- PCS Debug Message: \n%s\n -->\n", $message );
			}
		}
	}
}