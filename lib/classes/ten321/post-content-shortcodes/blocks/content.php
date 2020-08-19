<?php
/**
 * Class definition for the main Post Content Shortcodes "Content" Block
 */

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Ten321\Post_Content_Shortcodes\Blocks {

	use Ten321\Post_Content_Shortcodes\Plugin;

	if ( ! class_exists( 'Content' ) ) {
		class Content extends PCS_Block {
			/**
			 * @var Content $instance holds the single instance of this class
			 * @access private
			 */
			private static $instance;

			/**
			 * Creates the Content object
			 *
			 * @access private
			 * @since  2020.8
			 */
			private function __construct() {
				$this->block_type  = 'content';
				$this->block_title = __( 'PCS Content Block', 'post-content-shortcodes' );

				parent::__construct();
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Content
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

				$instance['type']            = array(
					'type'    => 'string',
					'default' => 'content',
				);
				$instance['id']              = array(
					'type'    => 'integer',
					'default' => $defaults['id'],
				);
				$instance['post_name']       = array(
					'type'    => 'string',
					'default' => $defaults['post_name'],
				);
				$instance['exclude_current'] = array(
					'type'    => 'boolean',
					'default' => $defaults['exclude_current'],
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
					$atts['blog'] = $atts['blog']['key'];
				}

				ob_start();
				print( '<pre><code>' );
				var_dump( $atts );
				print( '</code></pre>' );
				$rt = ob_get_clean();

				return $rt . Plugin::instance()->post_content( $atts );
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
					'tag'        => 'post-content',
					'attributes' => $trans_atts,
				);
			}
		}
	}
}
