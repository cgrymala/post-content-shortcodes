<?php
namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Post_Content_Shortcodes {
	if ( ! class_exists( 'Admin' ) ) {
		class Admin {
			/**
			 * @var \Post_Content_Shortcodes\Admin $instance holds the single instance of this class
			 * @access private
			 */
			private static $instance;
			/**
			 * @var string $version holds the version number for the plugin
			 * @access private
			 */
			private $version = null;

			/**
			 * Creates the \Post_Content_Shortcodes\Admin object
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
			 * @return  \Post_Content_Shortcodes\Admin
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