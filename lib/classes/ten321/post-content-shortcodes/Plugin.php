<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Ten321\Post_Content_Shortcodes {

	if ( ! class_exists( 'Plugin' ) ) {

		class Plugin extends Base {
			/**
			 * @var Plugin $instance holds the single instance of this class
			 * @access private
			 */
			private static Plugin $instance;

			/**
			 * Returns the instance of this class.
			 *
			 * @access  public
			 * @return  Plugin
			 * @since   2020.8
			 */
			public static function instance(): Plugin {
				if ( ! isset( self::$instance ) ) {
					$className      = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}
		}
	}
}
