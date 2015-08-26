<?php
/**
 * Admin functions for the post_content_shortcodes class
 * @version 0.5.6
 */

if( !class_exists( 'Post_Content_Shortcodes' ) )
	/**
	 * Make sure the parent class exists
	 */
	require_once( 'class-post-content-shortcodes.php' );

if( !class_exists( 'Post_Content_Shortcodes_Admin' ) ) {
	/**
	 * Define the admin child class for post_content_shortcodes
	 */
	class Post_Content_Shortcodes_Admin extends Post_Content_Shortcodes {
		var $settings_page		= 'post-content-shortcodes';
		var $settings_section	= 'post-content-shortcodes';
		
		/**
		 * Save any options that have been modified
		 * Only used in network admin and multinetwork settings. The Settings API handles saving options
		 * 		in the regular site admin area.
		 * @return array the output from each save action
		 */
		protected function _set_options( $opt ) {
			if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $this->settings_page . '-options' ) )
				wp_die( 'The nonce could not be verified' );
				/*return false;*/
			if( ! is_network_admin() )
				return false;
			
			$opt = $this->sanitize_settings( $opt );
			
			return $this->is_multinetwork() ? update_mnetwork_option( 'pcs-settings', $opt ) : update_site_option( 'pcs-settings', $opt );
		}
		
		/**
		 * Perform any actions that should only occur in the admin area
		 */
		function admin_init() {
			add_settings_section( $this->settings_section, __( 'Post Content Shortcodes' ), array( $this, 'settings_section' ), $this->settings_page );
			
			/**
			 * Add a setting field to enable/disable network settings in a multi-network environment
			 */
			if ( $this->is_multinetwork() && isset( $_REQUEST['page'] ) && 'mn-post-content-shortcodes' == $_REQUEST['page'] )
				add_settings_field( 'enable-network-settings', __( 'Allow network admins to override these settings on an individual network?' ), array( $this, 'settings_field' ), $this->settings_page, $this->settings_section, array( 'label_for' => 'enable-network-settings' ) );
			/**
			 * Add a setting field to enable/disable site settings in a multisite environment
			 */
			if ( $this->is_plugin_active_for_network() && is_network_admin() )
				add_settings_field( 'enable-site-settings', __( 'Allow individual administrators to override these settings on their invidual sites/blogs?' ), array( $this, 'settings_field' ), $this->settings_page, $this->settings_section, array( 'label_for' => 'enable-site-settings' ) );
			
			add_settings_field( 'enable-pcs-content-widget', __( 'Enable the post content widget?' ), array( $this, 'settings_field' ), $this->settings_page, $this->settings_section, array( 'label_for' => 'enable-pcs-content-widget' ) );
			add_settings_field( 'enable-pcs-list-widget', __( 'Enable the post list widget?' ), array( $this, 'settings_field' ), $this->settings_page, $this->settings_section, array( 'label_for' => 'enable-pcs-list-widget' ) );
			/*add_settings_field( 'enable-pcs-ajax', __( 'Enable experimental AJAX features?' ), array( $this, 'settings_field' ), $this->settings_page, $this->settings_section, array( 'label_for' => 'enable-pcs-ajax' ) );*/
			add_settings_field( 'use-styles', __( 'Enable the default stylesheet?' ), array( $this, 'settings_field' ), $this->settings_page, $this->settings_section, array( 'label_for' => 'use-styles' ) );
			
			register_setting( $this->settings_section, 'pcs-settings', array( $this, 'sanitize_settings' ) );
		}
		
		/**
		 * Add the options page to the appropriate menu in the admin area
		 */
		function admin_menu() {
			$this->_get_options();
			
			if( $this->is_multinetwork() ) {
				add_submenu_page( 'index.php', __( 'Multi-Network Post Content Shortcodes Settings' ), __( 'Multi-Network Post Content Shortcodes' ), 'manage_network_plugins', 'mn-' . $this->settings_page, array( $this, 'admin_page' ) );
				if ( false === $this->settings['enable-network-settings'] && false == $this->settings['enable-site-settings'] )
					return;
			}
				
			if( $this->is_plugin_active_for_network() ) {
				if ( ! $this->is_multinetwork() || true === $this->settings['enable-network-settings'] )
					add_submenu_page( 'settings.php', __( 'Network Post Content Shortcodes Settings' ), __( 'Post Content Shortcodes' ), 'manage_network_plugins', $this->settings_page, array( $this, 'admin_page' ) );
			}
			
			if ( ( ! $this->is_multinetwork() && ! $this->is_plugin_active_for_network() ) || true === $this->settings['enable-site-settings'] )
				add_options_page( __( 'Post Content Shortcodes Settings' ), __( 'Post Content Shortcodes' ), 'manage_options', $this->settings_page, array( $this, 'admin_page' ) );
		}
		
		/**
		 * Output any content that needs to appear in the header section of the options page
		 */
		function settings_section() {
?>
<p><?php _e( 'This page allows you to tweak various elements within the Post Content Shortcodes plugin.' ) ?></p>
<?php
		}
		
		/**
		 * Generic function to build each settings field
		 */
		function settings_field( $args ) {
?>
	<input type="checkbox" name="pcs-settings[<?php echo $args['label_for'] ?>]" id="<?php echo $args['label_for'] ?>" value="on"<?php checked( $this->settings[$args['label_for']] ) ?>/>
<?php
			switch( $args['label_for'] ) {
				case 'enable-pcs-content-widget':
?>
	<p class="note"><?php _e( 'Allows users to add post content to widgetized areas using the Post Content Widget.' ) ?></p>
<?php
					break;
				case 'enable-pcs-list-widget':
?>
	<p class="note"><?php _e( 'Allows users to add lists of posts to widgetized areas using the Post List Widget.' ) ?></p>
<?php
					break;
				case 'enable-pcs-ajax':
?>
	<p class="note"><?php _e( 'Experimental feature that attempts to retrieve lists of posts based on the criteria specified; making it easier to find the right post ID. If enabled, this feature will be available in any enabled PCS widgets, as well as the Visual Editor button.' ) ?></p>
<?php
					break;
				case 'use-styles':
?>
	<p class="note"><?php _e( 'Some default styles are included with the plugin to help style the lists of posts. If you would like to use these default styles, check this box. If you want to apply your own styles, uncheck this box.' ) ?></p>
<?php
					break;
			}
		}
		
		/**
		 * Clean up and format the array of global plugin options
		 * Loops through the list of post_content_shortocdes::$settings to ensure all settings are set
		 * At this time, all of our options are true/false, so this function only handles those
		 */
		function sanitize_settings( $input ) {
			$sanitized = array();
			foreach( $this->settings as $k=>$v ) {
				if( isset( $input[$k] ) && ( 'on' == $input[$k] || true === $input[$k] ) )
					$sanitized[$k] = true;
				else
					$sanitized[$k] = false;
			}
			return $sanitized;
		}
		
		/**
		 * Build our admin page
		 * @uses post_content_shortcodes_admin::_no_permissions()
		 * @uses post_content_shortcodes_admin::_set_options()
		 * @uses post_content_shortcodes_admin::options_updated_message()
		 * @uses post_content_shortcodes::_get_options()
		 */
		function admin_page() {
			if( ( is_network_admin() && ! current_user_can( 'manage_network_options' ) ) || ( is_admin() && ! current_user_can( 'manage_options' ) ) )
				return $this->_no_permissions();
			
			if( is_network_admin() && isset( $_REQUEST['action'] ) && $this->settings_page == $_REQUEST['page'] ) {
				$msg = $this->_set_options( $_REQUEST['pcs-settings'] );
			}
			
			$this->_get_options();
?>
	<div class="wrap">
<div class="wrap">
	<h2><?php _e( 'Post Content Shortcodes Settings' ) ?></h2>
<?php
			if( isset( $msg ) ) {
				$this->options_updated_message( $msg );
			}
			if ( is_admin() && $this->is_plugin_active_for_network() ) {
?>
	<p><em><?php _e( 'If you save these settings, they will override any settings configured at the network level. If you would like this site to use the network-wide settings, do not save the settings on this page.' ) ?></em></p>
<?php
			} elseif ( is_network_admin() && $this->is_multinetwork() ) {
?>
	<p><em><?php _e( 'If you save these settings, they will override any settings configured at the multi-network level. If you would like this network to use the default multi-network settings, do not save the settings on this page.' ) ?></em></p>
<?php
			}
?>
    <form method="post" action="<?php echo ( is_network_admin() ) ? '' : 'options.php'; ?>">
<?php
		settings_fields( $this->settings_page );
		do_settings_sections( $this->settings_page );
?>
		<p><input type="submit" value="<?php _e( 'Save' ) ?>" class="button-primary"/></p>
    </form>
</div>
<?php
		}
		
		/**
		 * Output a message indicating whether or not the options were updated
		 */
		protected function options_updated_message( $msg ) {
?>
		<div class="updated fade">
<?php
				printf( __( '<p>The %s options were %supdated%s.</p>' ), $this->settings_titles[$k], ( true === $msg ? '' : '<strong>not</strong> ' ), ( true === $msg ? ' successfully' : '' ) );
?>
        </div>
<?php
		}
		
		/**
		 * Output the appropriate error message about the user not having the right permissions
		 */
		protected function _no_permissions() {
?>
	<div class="wrap">
		<h2><?php _e( 'Post Content Shortcodes', $this->text_domain ) ?></h2>
		<p><?php _e( 'You do not have the appropriate permissions to update these options. Please work with an administrator of the site to update the options. Thank you.', $this->text_domain ) ?></p>
	</div>
<?php
		}
		
	}
}
?>