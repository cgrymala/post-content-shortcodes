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
				add_filter( 'ten321/post-content-shortcodes/blocks/attributes', array(
					$this,
					'add_attributes'
				), 10, 2 );

				add_filter( 'ten321/post-content-shortcodes/blocks/register', array(
					$this,
					'register_args'
				) );

				add_filter( 'ten321/post-content-shortcodes/blocks/localized-scripts', array(
					$this,
					'localize_script'
				) );
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
				$instance['blog_id']         = array(
					'type'    => 'integer',
					'default' => $defaults['blog_id'],
				);
				$instance['exclude_current'] = array(
					'type'    => 'boolean',
					'default' => $defaults['exclude_current'],
				);
				$instance['title']           = array(
					'type'    => 'string',
					'default' => '',
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
						'shortcode' => function ( $shortcode ) {
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