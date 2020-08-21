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
				$this->block_type  = 'list';
				$this->block_title = __( 'PCS List Block', 'post-content-shortcodes' );

				parent::__construct();
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
			 * Add any attributes for that need to be registered for this block
			 *
			 * @param array $atts the existing list of attributes
			 * @param array $defaults the array of default values
			 *
			 * @access public
			 * @return array the full list of attributes
			 * @since  0.1
			 */
			public function get_attributes( array $atts = array(), array $defaults = array() ) {
				$defaults = Plugin::instance()->defaults;
				$instance = parent::get_attributes( $atts, $defaults );

				$instance['type']             = array(
					'type'    => 'string',
					'default' => 'list',
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
					'type'    => 'object',
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

				return $instance;
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
				if ( array_key_exists( 'blog', $atts ) ) {
					$atts['blog'] = intval( $atts['blog']['key'] );
					$atts['blog_id'] = $atts['blog'];
				}

				if ( array_key_exists( 'orderby', $atts ) ) {
					$atts['orderby'] = $atts['orderby']['key'];
				}

				$rt = '';

				/*ob_start();
				print( '<pre><code>' );
				var_dump( $atts );
				print( '</code></pre>' );
				$rt = ob_get_clean();*/

				$rt .= Plugin::instance()->post_list( $atts );

				return $rt;
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
			public function get_args( array $args ) {
				return $this->register_args( $args );
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
			public function register_args( array $args ) {
				if ( ! array_key_exists( 'transforms', $args ) ) {
					$args['transforms'] = array( 'from' => array() );
				}
				if ( ! array_key_exists( 'from', $args['transforms'] ) ) {
					$args['transforms']['from'] = array();
				}

				$args['transforms']['from'][] = $this->get_transform_arguments();

				return $args;
			}

			/**
			 * Retrieve the "transforms" portion of the block registration arguments
			 *
			 * @access public
			 * @return array the transforms portion of the array
			 * @since  0.1
			 */
			public function get_transform_arguments() {
				$trans_atts = array();

				$atts = $this->get_attributes();
				foreach ( $atts as $key => $att ) {
					$trans_atts[ $key ] = array(
						'type'      => $att['type'],
						'shortcode' => function ( $shortcode, $key ) {
							return $shortcode['named'][ $key ];
						}
					);
				}

				return array(
					'type'       => 'shortcode',
					'tag'        => 'post-list',
					'attributes' => $trans_atts,
				);
			}
		}
	}
}
