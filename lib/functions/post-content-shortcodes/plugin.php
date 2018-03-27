<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Post_Content_Shortcodes {
	if ( ! class_exists( 'Plugin' ) ) {
		class Plugin {
			/**
			 * @var \Post_Content_Shortcodes\Plugin $instance holds the single instance of this class
			 * @access private
			 */
			private static $instance;
			/**
			 * @var string $version holds the version number for the plugin
			 * @access public
			 */
			public static $version = '2.0.1';
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
			 * Creates the \Post_Content_Shortcodes\Plugin object
			 *
			 * @access private
			 * @since  0.1
			 */
			private function __construct() {
				add_action( 'plugins_loaded', array( $this, 'startup' ) );
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @since   0.1
			 * @return  \Post_Content_Shortcodes\Plugin
			 */
			public static function instance() {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}

			/**
			 * Set up any actions that need to happen within this plugin
			 *
			 * @access public
			 * @since  2.0
			 * @return void
			 */
			public function startup() {

			}
		}
	}
}