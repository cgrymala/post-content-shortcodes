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
			 * Creates the PCS_Block object
			 *
			 * @access private
			 * @since  2020.8
			 */
			public function __construct() {
				$this->register_block_type();
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
			 * @access protected
			 * @return void
			 * @since  0.1
			 */
			protected function register_block_type() {
				register_block_type(
					$this->block_namespace, array(
						// Enqueue blocks.style.build.css on both frontend & backend.
						'style'           => $this->get_stylesheet(),
						// Enqueue blocks.build.js in the editor only.
						'editor_script'   => $this->get_editor_script(),
						// Enqueue blocks.editor.build.css in the editor only.
						'editor_style'    => $this->get_editor_style(),
						'render_callback' => array( $this, 'render' ),
						'attributes'      => $this->get_attributes(),
					)
				);
			}

			/**
			 * Register the block stylesheet and return the handle
			 *
			 * @access public
			 * @return string the handle of the registered stylesheet
			 * @since  0.1
			 */
			public function get_stylesheet() {
				wp_register_style(
					$this->block_namespace . '/style',
					$this->block_path . 'blocks.style.build.css',
					is_admin() ? array( 'wp-editor' ) : null,
					null,
					'all'
				);

				return $this->block_namespace . '/style';
			}

			/**
			 * Register the block editor javascript and return the handle
			 *
			 * @access public
			 * @return string the handle of the registered JS file
			 * @since  0.1
			 */
			public function get_editor_script() {
				wp_register_script(
					$this->block_namespace . '/script',
					$this->block_path . 'blocks.build.js',
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

				// WP Localized globals. Use dynamic PHP stuff in JavaScript via `cgbGlobal` object.
				wp_localize_script(
					'wp_nav_menu_block-cgb-block-js',
					'cgbGlobal', // Array containing dynamic data for a JS Global.
					[
						'pluginDirPath' => Plugin::plugin_dir_path(),
						'pluginDirUrl'  => Plugin::plugin_dir_url(),
						// Add more data here that you want to access from `cgbGlobal` object.
					]
				);

				return $this->block_namespace . '/script';
			}

			/**
			 * Register the block editor stylesheet and return the handle
			 *
			 * @access public
			 * @return string the handle of the stylesheet
			 * @since  0.1
			 */
			public function get_editor_style() {
				wp_register_style(
					$this->block_namespace . '/editor-style',
					$this->block_path . 'blocks.editor.build.css',
					array( 'wp-edit-blocks' ),
					null,
					'all'
				);

				return $this->block_namespace . '/editor-style';
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
			 * Retrieve a list of the block attributes
			 *
			 * @access public
			 * @return array the list of attributes
			 * @since  0.1
			 */
			public function get_attributes() {
				$all = Plugin::instance()->defaults;

				$instance                   = array();
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
				$instance['view_template']  = array(
					'type'    => 'integer',
					'default' => $all['view_template'],
				);
				$instance['link_image']     = array(
					'type'    => 'boolean',
					'default' => $all['link_image'],
				);

				return apply_filters( 'ten321/post-content-shortcodes/blocks/attributes', $instance, $all );
			}

			/**
			 * Add any additional attributes that are unique to this block
			 *
			 * @param array $atts the existing list of attributes
			 * @param array $defaults the array of default values
			 *
			 * @access public
			 * @return array the updated list of attributes
			 * @since  0.1
			 */
			abstract public function add_attributes( array $atts, array $defaults );
		}
	}
}
