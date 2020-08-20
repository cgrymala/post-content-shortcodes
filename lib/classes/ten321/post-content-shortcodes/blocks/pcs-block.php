<?php
/**
 * Class definition for the main Post Content Shortcodes Block
 */

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Ten321\Post_Content_Shortcodes\Blocks {

	use Ten321\Post_Content_Shortcodes\Plugin;

	if ( ! class_exists( 'PCS_Block' ) ) {
		abstract class PCS_Block {
			/**
			 * @var PCS_Block $instance holds the single instance of this class
			 * @access private
			 */
			private static $instance;
			/**
			 * @var string $block_path holds the file path for the block's assets
			 * @access protected
			 */
			protected $block_path = '';
			/**
			 * @var string $block_namespace holds the namespace for the block
			 * @access protected
			 */
			protected $block_namespace = '';
			/**
			 * @var string $block_title holds the name of the block
			 * @access protected
			 */
			protected $block_title = '';
			/**
			 * @var string $block_type the sub-type of block being registered
			 * @access protected
			 */
			protected $block_type = '';

			/**
			 * Creates the PCS_Block object
			 *
			 * @access private
			 * @since  2020.8
			 */
			public function __construct() {
				Plugin::log( 'Entered the PCS_Block __construct() method' );

				$this->block_namespace = 'ten321--post-content-shortcodes--blocks/' . $this->block_type;
				$this->block_path      = Plugin::plugin_dir_url( '/dist/ten321/post-content-shortcodes/blocks/' . $this->block_type . '/' );

				add_action( 'init', array( $this, 'register_block_type' ) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  PCS_Block
			 * @since   2020.8
			 */
			public static function instance() {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Register the Block object
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function register_block_type() {
				$atts = $this->get_attributes();
				Plugin::log( 'Attributes Array looks like: ' );
				Plugin::log( print_r( $atts, true ) );

				$args = array(
					// Enqueue blocks.style.build.css on both frontend & backend.
					'style'           => $this->get_stylesheet(),
					// Enqueue blocks.build.js in the editor only.
					'editor_script'   => $this->get_editor_script(),
					// Enqueue blocks.editor.build.css in the editor only.
					'editor_style'    => $this->get_editor_style(),
					'render_callback' => array( $this, 'render' ),
					'attributes'      => $atts,
				);

				$args = $this->get_args( $args );

				Plugin::log( 'Preparing to register a new Block' );
				Plugin::log( print_r( $args, true ) );

				register_block_type(
					$this->block_namespace, $args
				);
			}

			/**
			 * Get the array of arguments used to register the new block
			 *
			 * @param array $args the existing array of arguments
			 *
			 * @access public
			 * @return array the updated list of arguments
			 * @since  0.1
			 */
			abstract public function get_args( array $args );

			/**
			 * Register the block stylesheet and return the handle
			 *
			 * @access public
			 * @return string the handle of the registered stylesheet
			 * @since  0.1
			 */
			public function get_stylesheet() {
				if ( $this->script_debug() ) {
					$file = $this->block_path . 'style.css';
				} else {
					$file = $this->block_path . 'block.build.css';
				}

				$handle = $this->block_namespace . '/style';

				wp_register_style(
					$handle,
					$file,
					is_admin() ? array( 'wp-editor' ) : null,
					null,
					'all'
				);

				return $handle;
			}

			/**
			 * Register the block editor javascript and return the handle
			 *
			 * @access public
			 * @return string the handle of the registered JS file
			 * @since  0.1
			 */
			public function get_editor_script() {
				if ( $this->script_debug() ) {
					$file = $this->block_path . 'block.js';
				} else {
					$file = $this->block_path . 'block.min.js';
				}

				$handle = $this->block_namespace . '/script';

				wp_register_script(
					$handle,
					$file,
					array(
						'wp-blocks',
						'wp-i18n',
						'wp-element',
						'wp-editor',
						'wp-components',
						'wp-compose',
					),
					null,
					'all'
				);

				$this->localize_script( $handle );

				return $handle;
			}

			/**
			 * Register the block editor stylesheet and return the handle
			 *
			 * @access public
			 * @return string the handle of the stylesheet
			 * @since  0.1
			 */
			public function get_editor_style() {
				if ( $this->script_debug() ) {
					$file = $this->block_path . 'editor.css';
				} else {
					$file = $this->block_path . 'block.editor.build.css';
				}

				$handle = $this->block_namespace . '/editor-style';

				wp_register_style(
					$handle,
					$file,
					array( 'wp-edit-blocks' ),
					null,
					'all'
				);

				return $handle;
			}

			/**
			 * Determine whether to use debuggable scripts
			 *
			 * @access protected
			 * @return bool whether to use debuggable scripts
			 * @since  0.1
			 */
			protected function script_debug() {
				return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
			}

			/**
			 * Render the block itself
			 *
			 * @param array $atts the block attributes
			 * @param string $content the content of the block
			 *
			 * @access public
			 * @return string the rendered HTML for the block
			 * @since  0.1
			 */
			abstract public function render( array $atts, string $content = '' );

			/**
			 * Add any additional elements that need to be in the localized script array
			 *
			 * @param string $handle the handle for the script being localized
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function localize_script( string $handle ) {
				$script = [
					'pluginDirPath' => Plugin::plugin_dir_path(),
					'pluginDirUrl'  => Plugin::plugin_dir_url(),
					// Add more data here that you want to access from `cgbGlobal` object.
				];

				$script['reg_args'] = array(
					'attributes' => $this->get_attributes(),
					'transforms' => $this->get_transform_arguments(),
				);

				if ( is_multisite() ) {
					$script['blogList'] = $this->get_blog_list();
					$blog_id            = intval( get_current_blog_id() );
					foreach ( $script['blogList'] as $blog ) {
						if ( intval( $blog['key'] ) === $blog_id ) {
							$script['currentBlog'] = $blog;
						}
					}
				}

				// WP Localized globals. Use dynamic PHP stuff in JavaScript via `cgbGlobal` object.
				wp_localize_script(
					$handle,
					'ten321__post_content_shortcodes__blocks__' . $this->block_type, // Array containing dynamic data for a JS Global.
					$script
				);
			}

			/**
			 * Retrieve a list of the block attributes
			 *
			 * @param array $atts the existing list of attributes
			 * @param array $defaults the array of default values
			 *
			 * @access public
			 * @return array the list of attributes
			 * @since  0.1
			 */
			public function get_attributes( array $atts = array(), array $defaults = array() ) {
				$all = Plugin::instance()->defaults;

				$instance = array();

				if ( is_multisite() ) {
					$instance['blog'] = array(
						'type'    => 'object',
						'default' => array(
							'key'  => $GLOBALS['blog_id'],
							'name' => get_blog_option( $GLOBALS['blog_id'], 'name' ),
						)
					);
					$instance['blog_id']        = array(
						'type'    => 'integer',
						'default' => $GLOBALS['blog_id'],
					);
				}

				$instance['show_title']     = array(
					'type'    => 'boolean',
					'default' => $all['show_title'],
				);
				$instance['show_image']     = array(
					'type'    => 'boolean',
					'default' => $all['show_image'],
				);
				$instance['show_excerpt']   = array(
					'type'    => 'boolean',
					'default' => $all['show_excerpt'],
				);
				$instance['read_more']      = array(
					'type'    => 'boolean',
					'default' => $all['read_more'],
				);
				$instance['shortcodes']     = array(
					'type'    => 'boolean',
					'default' => $all['shortcodes'],
				);
				$instance['strip_html']     = array(
					'type'    => 'boolean',
					'default' => $all['strip_html'],
				);
				$instance['show_author']    = array(
					'type'    => 'boolean',
					'default' => $all['show_author'],
				);
				$instance['show_date']      = array(
					'type'    => 'boolean',
					'default' => $all['show_date'],
				);
				$instance['show_comments']  = array(
					'type'    => 'boolean',
					'default' => $all['show_comments'],
				);
				$instance['excerpt_length'] = array(
					'type'    => 'integer',
					'default' => $all['excerpt_length'],
				);
				$instance['image_width']    = array(
					'type'    => 'integer',
					'default' => $all['image_width'],
				);
				$instance['image_height']   = array(
					'type'    => 'integer',
					'default' => $all['image_height'],
				);
				if ( class_exists( '\WP_Views_plugin' ) ) {
					$instance['view_template'] = array(
						'type'    => 'integer',
						'default' => 0,
					);
				}
				$instance['link_image']     = array(
					'type'    => 'boolean',
					'default' => $all['link_image'],
				);

				return $instance;
			}

			/**
			 * Add any additional block registration arguments for this block
			 *
			 * @param array $args the existing list of arguments
			 *
			 * @access public
			 * @return array the updated list of arguments
			 * @since  0.1
			 */
			abstract public function register_args( array $args );

			/**
			 * Retrieve a list of all blogs in this multisite
			 *
			 * @access protected
			 * @return array the list of blogs
			 * @since  0.1
			 */
			protected function get_blog_list() {
				$blog_list = array();

				global $wpdb;
				$blogs    = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs} ORDER BY blog_id" );
				$org_blog = $GLOBALS['blog_id'];

				foreach ( $blogs as $blog ) {
					if ( empty( $org_blog ) ) {
						$org_blog = $wpdb->set_blog_id( $blog );
					} else {
						$wpdb->set_blog_id( $blog );
					}

					$blog_list[] = array(
						'key'  => $blog,
						'name' => $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name=%s", 'blogname' ) ),
					);
				}

				$wpdb->set_blog_id( $org_blog );

				return $blog_list;
			}
		}
	}
}
