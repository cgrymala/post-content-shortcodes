<?php
/*
Plugin Name: Post Content Shortcodes
Plugin URI: http://plugins.ten-321.com/post-content-shortcodes/
Description: Adds shortcodes to show the content of another post or to show a list of posts
Version: 2.0.0
Author: cgrymala
Author URI: http://ten-321.com/
License: GPL2
Text Domain: post-content-shortcodes
Domain Path: /lang
*/

namespace {
	require_once __DIR__ . '/vendor/autoload.php';

	if ( file_exists( __DIR__ . '/.env' ) ) {
		$dotenv = Dotenv\Dotenv::createImmutable( __DIR__ );
		$dotenv->ifPresent( 'UMW_RSS_DISPLAY_REPO_IS_CUSTOM_GITLAB' )->isBoolean();
		$dotenv->load();
	} else if ( file_exists( __DIR__ . '/.env.default' ) ) {
		$dotenv = Dotenv\Dotenv::createImmutable( __DIR__, '.env.default' );
		$dotenv->ifPresent( 'UMW_RSS_DISPLAY_REPO_IS_CUSTOM_GITLAB' )->isBoolean();
		$dotenv->load();
	}
}

namespace Ten321\Post_Content_Shortcodes {
	global $post_content_shortcodes_obj;

	if ( ! isset( $post_content_shortcodes_obj ) ) {
		$GLOBALS['post_content_shortcodes_obj'] = Plugin::instance();
	}
}