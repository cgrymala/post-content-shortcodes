<?php
/**
 * Class definition for the main Post Content Shortcodes "List" Block
 */

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Ten321\Post_Content_Shortcodes\Blocks {

	use Ten321\Post_Content_Shortcodes\Plugin;

	if ( ! class_exists( 'Content_List' ) ) {
		class Content_List extends PCS_Block {
			/**
			 * @var Content_List $instance holds the single instance of this class
			 * @access private
			 */
			private static $instance;

			/**
			 * Creates the Content_List object
			 *
			 * @access private
			 * @since  2020.8
			 */
			private function __construct() {
				$this->block_namespace = 'ten321/post-content-shortcodes/list';
				$this->block_path      = Plugin::plugin_dir_url( '/dist/list/' );
				$this->block_title     = __( 'PCS List Block', 'post-content-shortcodes' );

				parent::__construct();
				add_filter( 'ten321/post-content-shortcodes/blocks/attributes', array(
					$this,
					'add_attributes'
				), 10, 2 );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Content_List
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
			 * Add any additional attributes that are unique to this block
			 *
			 * @param array $atts the existing list of attributes
			 * @param array $defaults the array of default values
			 *
			 * @access public
			 * @return array the updated list of attributes
			 * @since  0.1
			 */
			public function add_attributes( array $atts, array $defaults ) {
				$instance = array();

				$instance['title']            = array(
					'type'    => 'string',
					'default' => $defaults['title'],
				);
				$instance['type']             = array(
					'type'    => 'string',
					'default' => 'list',
				);
				$instance['blog_id']          = array(
					'type'    => 'integer',
					'default' => $defaults['blog_id'],
				);
				$instance['post_type']        = array(
					'type'    => 'string',
					'default' => $defaults['post_type'],
				);
				$instance['post_parent']      = array(
					'type'    => 'integer',
					'default' => $defaults['post_parent'],
				);
				$instance['orderby']          = array(
					'type'    => 'string',
					'default' => $defaults['orderby'],
				);
				$instance['order']            = array(
					'type'    => 'string',
					'default' => $defaults['order'],
				);
				$instance['numberposts']      = array(
					'type'    => 'integer',
					'default' => $defaults['numberposts'],
				);
				$instance['post_status']      = array(
					'type'    => 'string',
					'default' => $defaults['post_status'],
				);
				$instance['exclude_current']  = array(
					'type'    => 'boolean',
					'default' => $defaults['exclude_current'],
				);
				$instance['tax_name']         = array(
					'type'    => 'string',
					'default' => $defaults['tax_name'],
				);
				$instance['tax_term']         = array(
					'type'    => 'string',
					'default' => $defaults['tax_term'],
				);
				$instance['ignore_protected'] = array(
					'type'    => 'boolean',
					'default' => $defaults['ignore_protected'],
				);

				return array_merge( $atts, $instance );
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
			public function render( array $atts, string $content = '' ) {
				// TODO: Implement render() method.
			}
		}
	}
}
