<?php
/**
 * Admin functions for the \Post_Content_Shortcodes class
 *
 * @package WordPress
 * @subpackage Post Content Shortcodes
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You do not have permission to access this file directly.' );
}

/**
 * Make sure the parent class exists
 */
if ( ! class_exists( '\Post_Content_Shortcodes' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . '/class-post-content-shortcodes.php' );
}

if ( ! class_exists( 'Post_Content_Shortcodes_Admin' ) ) {
	/**
	 * Define the admin child class for post_content_shortcodes
	 */
	class Post_Content_Shortcodes_Admin extends Post_Content_Shortcodes {
		/**
		 * Holds the version number for use with various assets
		 *
		 * @since  1.0
		 * @access public
		 * @var    string
		 */
		public $version = '1.0';
		/**
		 * Holds the class instance.
		 *
		 * @since   0.1
		 * @access	private
		 * @var		\Post_Content_Shortcodes_Admin
		 */
		private static $instance;
		/**
		 * @since  0.1
		 * @access public
		 * @var    string the handle for the admin settings page
		 */
		public $settings_page = 'post-content-shortcodes';
		/**
		 * @since  0.1
		 * @access public
		 * @var    string the handle for the admin settings section
		 */
		public $settings_section = 'post-content-shortcodes';

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @since   1.0
		 * @return	\Post_Content_Shortcodes_Admin
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				$className = __CLASS__;
				self::$instance = new $className;
			}
			
			return self::$instance;
		}
		
		/**
		 * Save any options that have been modified
		 * Only used in network admin and multinetwork settings. The Settings API handles saving options
		 * 		in the regular site admin area.
		 *
		 * @param  array $opt the array of options being saved/updated
		 *
		 * @access protected
		 * @since  0.1
		 * @return array|bool the output from each save action
		 */
		protected function _set_options( $opt ) {
			if ( ! is_multisite() ) {
				return false;
			}
			
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $this->settings_page . '-options' ) ) {
				wp_die( 'The nonce could not be verified' );
			}
			
			if ( ! is_network_admin() ) {
				return false;
			}
			
			$opt = $this->sanitize_settings( $opt );
			
			if ( $this->is_multinetwork() && function_exists( 'update_mnetwork_option' ) ) {
				return update_mnetwork_option( 'pcs-settings', $opt );
			} else {
				return update_site_option( 'pcs-settings', $opt );
			}
		}
		
		/**
		 * Perform any actions that should only occur in the admin area
		 *
		 * @access public
		 * @since  0.1
		 * @return void
		 */
		public function admin_init() {
		    $this->debug( 'Stepping into the admin_init action' );
		    
			add_settings_section( $this->settings_section, __( 'Post Content Shortcodes', 'post-content-shortcodes' ), array( $this, 'settings_section' ), $this->settings_page );
			
			/**
			 * Add a setting field to enable/disable network settings in a multi-network environment
			 */
			if ( $this->is_multinetwork() && isset( $_REQUEST['page'] ) && 'mn-post-content-shortcodes' == $_REQUEST['page'] ) {
				add_settings_field( 'enable-network-settings', __( 'Allow network admins to override these settings on an individual network?', 'post-content-shortcodes' ), array( $this, 'settings_field' ), $this->settings_page, $this->settings_section, array( 'label_for' => 'enable-network-settings' ) );
			}
			/**
			 * Add a setting field to enable/disable site settings in a multisite environment
			 */
			if ( $this->is_plugin_active_for_network() && is_network_admin() ) {
				add_settings_field( 'enable-site-settings', __( 'Allow individual administrators to override these settings on their invidual sites/blogs?', 'post-content-shortcodes' ), array( $this, 'settings_field' ), $this->settings_page, $this->settings_section, array( 'label_for' => 'enable-site-settings' ) );
			}
			
			add_settings_field( 'enable-pcs-content-widget', __( 'Enable the post content widget?', 'post-content-shortcodes' ), array( $this, 'settings_field' ), $this->settings_page, $this->settings_section, array( 'label_for' => 'enable-pcs-content-widget' ) );
			add_settings_field( 'enable-pcs-list-widget', __( 'Enable the post list widget?', 'post-content-shortcodes' ), array( $this, 'settings_field' ), $this->settings_page, $this->settings_section, array( 'label_for' => 'enable-pcs-list-widget' ) );
			/*add_settings_field( 'enable-pcs-ajax', __( 'Enable experimental AJAX features?', 'post-content-shortcodes' ), array( $this, 'settings_field' ), $this->settings_page, $this->settings_section, array( 'label_for' => 'enable-pcs-ajax' ) );*/
			add_settings_field( 'use-styles', __( 'Enable the default stylesheet?', 'post-content-shortcodes' ), array( $this, 'settings_field' ), $this->settings_page, $this->settings_section, array( 'label_for' => 'use-styles' ) );
			
			register_setting( $this->settings_section, 'pcs-settings', array( $this, 'sanitize_settings' ) );
		}
		
		/**
		 * Add the options page to the appropriate menu in the admin area
		 *
		 * @access public
		 * @since  0.1
		 * @return void
		 */
		public function admin_menu() {
		    $this->debug( 'Stepping into the admin menu function' );
			$this->_get_options();
			
			if ( $this->is_multinetwork() ) {
				$this->debug( 'This install is reporting as a multi-network installation' );
				add_submenu_page( 'index.php', __( 'Multi-Network Post Content Shortcodes Settings', 'post-content-shortcodes' ), __( 'Multi-Network Post Content Shortcodes', 'post-content-shortcodes' ), 'manage_network_plugins', 'mn-' . $this->settings_page, array( $this, 'admin_page' ) );
				if ( false === $this->settings['enable-network-settings'] && false == $this->settings['enable-site-settings'] ) {
					return;
				}
			}
				
			if ( $this->is_plugin_active_for_network() && is_network_admin() ) {
				$this->debug( 'This plugin appears to be network-active in multisite' );
				if ( ! $this->is_multinetwork() || true === $this->settings['enable-network-settings'] ) {
					$this->debug( 'Adding the network admin menu' );
					add_submenu_page( 'settings.php', __( 'Network Post Content Shortcodes Settings', 'post-content-shortcodes' ), __( 'Post Content Shortcodes', 'post-content-shortcodes' ), 'manage_network_plugins', $this->settings_page, array( $this, 'admin_page' ) );
				}
			}
			
			if ( ( ! $this->is_multinetwork() && ! $this->is_plugin_active_for_network() ) || true === $this->settings['enable-site-settings'] ) {
				$this->debug( 'Adding the individual sub-site menu' );
				add_options_page( __( 'Post Content Shortcodes Settings', 'post-content-shortcodes' ), __( 'Post Content Shortcodes', 'post-content-shortcodes' ), 'manage_options', $this->settings_page, array( $this, 'admin_page' ) );
			}
		}
		
		/**
		 * Output any content that needs to appear in the header section of the options page
		 *
		 * @access public
		 * @since  0.1
		 * @return void
		 */
		public function settings_section() {
?>
<p>
	<?php _e( 'This page allows you to tweak various elements within the Post Content Shortcodes plugin.', 'post-content-shortcodes' ) ?>
</p>
<?php
		}
		
		/**
		 * Generic function to build each settings field
		 * @param array $args the array of attributes for the specific field
		 *
		 * @access public
		 * @since  0.1
		 * @return void
		 */
		function settings_field( $args ) {
?>
	<input type="checkbox" name="pcs-settings[<?php echo $args['label_for'] ?>]" id="<?php echo $args['label_for'] ?>" value="on"<?php checked( $this->settings[$args['label_for']] ) ?>/>
<?php
			switch( $args['label_for'] ) {
				case 'enable-pcs-content-widget':
?>
	<p class="note">
		<?php _e( 'Allows users to add post content to widgetized areas using the Post Content Widget.', 'post-content-shortcodes' ) ?>
	</p>
<?php
					break;
				case 'enable-pcs-list-widget':
?>
	<p class="note">
		<?php _e( 'Allows users to add lists of posts to widgetized areas using the Post List Widget.', 'post-content-shortcodes' ) ?>
	</p>
<?php
					break;
				case 'enable-pcs-ajax':
?>
	<p class="note">
		<?php _e( 'Experimental feature that attempts to retrieve lists of posts based on the criteria specified; making it easier to find the right post ID. If enabled, this feature will be available in any enabled PCS widgets, as well as the Visual Editor button.', 'post-content-shortcodes' ) ?>
	</p>
<?php
					break;
				case 'use-styles':
?>
	<p class="note">
		<?php _e( 'Some default styles are included with the plugin to help style the lists of posts. If you would like to use these default styles, check this box. If you want to apply your own styles, uncheck this box.', 'post-content-shortcodes' ) ?>
	</p>
<?php
					break;
			}
		}
		
		/**
		 * Clean up and format the array of global plugin options
		 * Loops through the list of post_content_shortocdes::$settings to ensure all settings are set
		 * At this time, all of our options are true/false, so this function only handles those
		 * @param array $input the pre-sanitized options
		 *
		 * @access public
		 * @since  0.1
		 * @return array() the sanitized settings
		 */
		public function sanitize_settings( $input ) {
			$sanitized = array();
			foreach ( $this->settings as $k=>$v ) {
				if ( isset( $input[$k] ) && ( 'on' == $input[$k] || true === $input[$k] ) ) {
					$sanitized[$k] = true;
				} else {
					$sanitized[$k] = false;
				}
			}
			
			return $sanitized;
		}
		
		/**
		 * Build our admin page
		 *
		 * @uses post_content_shortcodes_admin::_no_permissions()
		 * @uses post_content_shortcodes_admin::_set_options()
		 * @uses post_content_shortcodes_admin::options_updated_message()
		 * @uses post_content_shortcodes::_get_options()
		 *
		 * @access public
		 * @since  0.1
		 * @return string
		 */
		public function admin_page() {
			if ( ( is_network_admin() && ! current_user_can( 'manage_network_options' ) ) || ( is_admin() && ! current_user_can( 'manage_options' ) ) ) {
				$this->_no_permissions();
				return '';
			}
			
			if ( is_network_admin() && isset( $_REQUEST['action'] ) && $this->settings_page == $_REQUEST['page'] ) {
				$msg = $this->_set_options( $_REQUEST['pcs-settings'] );
			}
			
			$this->_get_options();
?>
	<div class="wrap">
<div class="wrap">
	<h2>
		<?php _e( 'Post Content Shortcodes Settings', 'post-content-shortcodes' ) ?>
	</h2>
<?php
			if ( isset( $msg ) ) {
				$this->options_updated_message( $msg );
			}
			if ( is_admin() && ! is_network_admin() && $this->is_plugin_active_for_network() ) {
?>
	<p>
		<em>
			<?php _e( 'If you save these settings, they will override any settings configured at the network level. If you would like this site to use the network-wide settings, do not save the settings on this page.', 'post-content-shortcodes' ) ?>
		</em>
	</p>
<?php
			} else if ( is_network_admin() && $this->is_multinetwork() ) {
?>
	<p>
		<em>
			<?php _e( 'If you save these settings, they will override any settings configured at the multi-network level. If you would like this network to use the default multi-network settings, do not save the settings on this page.', 'post-content-shortcodes' ) ?>
		</em>
	</p>
<?php
			}
?>
	<form method="post" action="<?php echo ( is_network_admin() ) ? '' : 'options.php'; ?>">
<?php
		settings_fields( $this->settings_page );
		do_settings_sections( $this->settings_page );
?>
		<p>
			<input type="submit" value="<?php _e( 'Save', 'post-content-shortcodes' ) ?>" class="button-primary"/>
		</p>
	</form>
</div>
<?php
			return '';
		}
		
		/**
		 * Output a message indicating whether or not the options were updated
		 * @param string $msg the message to be output
		 *
		 * @access protected
		 * @since  0.1
		 * @return void
		 */
		protected function options_updated_message( $msg ) {
?>
		<div class="updated fade">
<?php
				printf( __( '<p>The %1$s options were %2$supdated%3$s.</p>', 'post-content-shortcodes' ), __( 'Post Content Shortcodes', 'post-content-shortcodes' ), ( true === $msg ? '' : __( '<strong>not</strong> ', 'post-content-shortcodes' ) ), ( true === $msg ? __( ' successfully', 'post-content-shortcodes' ) : '' ) );
?>
		</div>
<?php
		}
		
		/**
		 * Output the appropriate error message about the user not having the right permissions
		 *
		 * @access protected
		 * @since  0.1
		 * @return void
		 */
		protected function _no_permissions() {
?>
	<div class="wrap">
		<h2>
			<?php _e( 'Post Content Shortcodes', 'post-content-shortcodes' ) ?>
		</h2>
		<p>
			<?php _e( 'You do not have the appropriate permissions to update these options. Please work with an administrator of the site to update the options. Thank you.', 'post-content-shortcodes' ) ?>
		</p>
	</div>
<?php
		}
	}
}