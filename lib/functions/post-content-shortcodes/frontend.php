<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Post_Content_Shortcodes {
	if ( ! class_exists( 'Frontend' ) ) {
		class Frontend {
			/**
			 * @var \Post_Content_Shortcodes\Frontend $instance holds the single instance of this class
			 * @access private
			 */
			private static $instance;
			/**
			 * @var string $version holds the version number for the plugin
			 * @access private
			 */
			private $version = null;

			/**
			 * Creates the \Post_Content_Shortcodes\Frontend object
			 *
			 * @access private
			 * @since  0.1
			 */
			private function __construct() {
				$this->version = Plugin::$version;
			}

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @since   0.1
			 * @return  \Post_Content_Shortcodes\Frontend
			 */
			public static function instance() {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}