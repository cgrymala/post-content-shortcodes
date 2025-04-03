<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Ten321\Post_Content_Shortcodes {

	if ( ! class_exists( 'Helpers' ) ) {
		final class Helpers {
			/**
			 * Custom logging function that can be short-circuited
			 *
			 * @param string $message the text to output to the log
			 * @param string $level one of "debug", "warning" or "error"
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public static function log( string $message, string $level = 'debug' ): void {
				if ( ! defined( 'UMW_DEBUG' ) || false === UMW_DEBUG ) {
					return;
				}

				$intro = '[UMW RSS Display ' . ucfirst( $level ) . ']: ';

				if ( class_exists( '\QM' ) ) {
					do_action( 'qm/' . $level, $intro . $message );
				} else {
					error_log( $intro . $message );
				}
			}

			/**
			 * Retrieve a URL relative to the root of this plugin
			 *
			 * @param string $path the path to append to the root plugin path
			 *
			 * @access public
			 * @return string the full URL to the provided path
			 * @since  0.1
			 */
			public static function plugins_url( string $path ): string {
				return plugins_url( $path, dirname( __FILE__, 4 ) );
			}

			/**
			 * Retrieve a path relative to the root of this plugin
			 *
			 * @param string $path the path to append to the root plugin path
			 *
			 * @access public
			 * @return string the full path to the provided path
			 * @since  0.1
			 */
			public static function plugins_path( string $path ): string {
				$plugin_path = self::plugin_dir_path();

				if ( str_starts_with( $path, '/' ) ) {
					$plugin_path = untrailingslashit( $plugin_path );
				} else {
					$plugin_path = trailingslashit( $plugin_path );
				}

				return $plugin_path . $path;
			}

			/**
			 * Retrieve and return the root path of this plugin
			 *
			 * @access public
			 * @return string the absolute path to the root of this plugin
			 * @since  0.1
			 */
			public static function plugin_dir_path(): string {
				return plugin_dir_path( dirname( __FILE__, 4 ) );
			}

			/**
			 * Retrieve and return the root URL of this plugin
			 *
			 * @access public
			 * @return string the absolute URL
			 * @since  0.1
			 */
			public static function plugin_dir_url(): string {
				return plugin_dir_url( dirname( __FILE__, 4 ) );
			}

			/**
			 * Attempt to determine whether the Block Editor is being used
			 *
			 * @access public
			 * @return bool whether the block editor is being used
			 * @since  0.1
			 */
			public static function is_block_editor_active(): bool {
				// Gutenberg plugin is installed and activated.
				$gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

				// Block editor since 5.0.
				$block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

				if ( ! $gutenberg && ! $block_editor ) {
					return false;
				}

				if ( self::is_classic_editor_plugin_active() ) {
					$editor_option       = get_option( 'classic-editor-replace' );
					$block_editor_active = array( 'no-replace', 'block' );

					return in_array( $editor_option, $block_editor_active, true );
				}

				return true;
			}

			/**
			 * Determine whether the Classic Editor plugin is active
			 *
			 * @access protected
			 * @return bool whether the plugin is active
			 * @since  0.1
			 */
			protected static function is_classic_editor_plugin_active(): bool {
				return self::is_plugin_active( 'classic-editor/classic-editor.php' );
			}

			/**
			 * Determine whether a plugin is active on a site or network
			 *
			 * @param string $plugin the plugin slug to check
			 *
			 * @access public
			 * @return bool whether the plugin is active or not
			 * @since  0.1
			 */
			public static function is_plugin_active( string $plugin ): bool {
				if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {
					include_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				if ( is_plugin_active( $plugin ) ) {
					return true;
				}

				if ( is_plugin_active_for_network( $plugin ) ) {
					return true;
				}

				return false;
			}

			/**
			 * Formats a DateTime object into the WordPress date/time format
			 *
			 * @param \DateTime|boolean|null $date the object being formatted
			 *
			 * @access public
			 * @return string the formatted date/time
			 * @since  2023.04
			 */
			public static function format_date_time( $date = false ): string {
				if ( false === $date || is_null( $date ) ) {
					return '';
				}

				$time_format = get_option( 'date_format' ) . ' \a\t ' . get_option( 'time_format' );

				return $date->format( $time_format );
			}

			/**
			 * Format a timestamp into the WordPress date format
			 *
			 * @param numeric $time the timestamp being formatted
			 *
			 * @access public
			 * @return string the formatted date
			 * @since 2023.04
			 */
			public static function format_date( $time ): string {
				if ( false === $time ) {
					return '';
				}

				return date( get_option( 'date_format' ), $time );
			}

			public static function get_class_name( $classname ) {
				if ( $pos = strrpos( $classname, '\\' ) ) {
					return substr( $classname, $pos + 1 );
				}

				return $pos;
			}

			/**
			 * Determine which environment we are currently in
			 *
			 * @access public
			 * @return string environment handle
			 * @since  0.4.1
			 */
			public static function get_environment(): string {
				if ( getenv( 'WP_ENVIRONMENT_TYPE' ) !== false ) {
					return getenv( 'WP_ENVIRONMENT_TYPE' );
				} else {
					return 'unknown';
				}
			}
		}
	}
}
