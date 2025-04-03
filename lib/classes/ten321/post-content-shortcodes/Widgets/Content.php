<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Ten321\Post_Content_Shortcodes\Widgets {
	if ( ! class_exists( 'Content' ) ) {
		class Content extends PCS_Widget {
			/**
			 * Construct the actual widget object
			 *
			 * @access public
			 * @since  0.1
			 */
			public function __construct() {
				parent::__construct();

				$widget_ops  = array(
					'classname'   => 'pcs-content-widget',
					'description' => __( 'Display the content of a single post.', 'post-content-shortcodes' )
				);
				$control_ops = array( 'width' => 400, 'id_base' => 'pcs-content-widget' );
				parent::WP_Widget_construct( 'pcs-content-widget', __( 'Post Content Widget', 'post-content-shortcodes' ), $widget_ops, $control_ops );
			}

			/**
			 * Old-style constructor method
			 *
			 * @return Content
			 * @since  0.1
			 * @deprecated since 0.9
			 * @access public
			 */
			public function Content() {
				return self::__construct();
			}

			/**
			 * Output the actual form used to create the widget in the admin
			 *
			 * @param array $instance the current settings for this instance of the widget
			 *
			 * @access public
			 * @return void
			 * @since  0.1
			 */
			public function form( $instance ) {
				$this->get_blogs();
				$instance = array_merge( $this->defaults, $instance );
				if ( ! array_key_exists( 'title', $instance ) ) {
					$instance['title'] = '';
				}
				?>
                <p>
                    <label for="<?php echo $this->get_field_id( 'title' ) ?>">
						<?php _e( 'Widget Title:', 'post-content-shortcodes' ) ?>
                    </label>
                    <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' ) ?>"
                           id="<?php echo $this->get_field_id( 'title' ) ?>"
                           value="<?php echo esc_attr( $instance['title'] ) ?>"/></p>
				<?php
				if ( $this->blog_list ) {
					?>
                    <p>
                        <label for="<?php echo $this->get_field_id( 'blog_id' ) ?>">
							<?php _e( 'Show post from which blog?', 'post-content-shortcodes' ) ?>
                        </label>
                        <select class="widefat" name="<?php echo $this->get_field_name( 'blog_id' ) ?>"
                                id="<?php echo $this->get_field_id( 'blog_id' ) ?>">
                            <option value=""><?php _e( '-- Please select a blog --' ) ?></option>
							<?php
							foreach ( $this->blog_list as $id => $name ) {
								?>
                                <option value="<?php echo $id ?>"<?php selected( $instance['blog_id'], $id ) ?>><?php echo $name ?></option>
								<?php
							}
							?>
                        </select>
                    </p>
					<?php
				}
				?>
                <fieldset style="padding: 5px; margin: 5px; border: 1px solid #999">
                    <legend style="font-weight: bold; margin-bottom: 10px;">
						<?php _e( 'Post Selection', 'post-content-shortcodes' ) ?>
                    </legend>
                    <p>
                        <label for="<?php echo $this->get_field_id( 'id' ) ?>">
							<?php _e( 'Post ID:', 'post-content-shortcodes' ) ?>
                        </label>
                        <input class="widefat" type="number" id="<?php echo $this->get_field_id( 'id' ) ?>"
                               name="<?php echo $this->get_field_name( 'id' ) ?>"
                               value="<?php echo $instance['id'] ?>"/>
                    </p>
                    <p>
						<?php _e( 'OR', 'post-content-shortcodes' ) ?>
                    </p>
                    <p>
                        <label for="<?php echo $this->get_field_id( 'post_name' ) ?>">
							<?php _e( 'Post Name (slug):', 'post-content-shortcodes' ) ?>
                        </label>
                        <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'post_name' ) ?>"
                               name="<?php echo $this->get_field_name( 'post_name' ) ?>"
                               value="<?php echo $instance['post_name'] ?>"/>
                    </p>
                </fieldset>
                <p>
                    <input type="checkbox" name="<?php echo $this->get_field_name( 'exclude_current' ) ?>"
                           id="<?php echo $this->get_field_id( 'exclude_current' ) ?>"
                           value="1"<?php checked( $instance['exclude_current'] ) ?>/>
                    <label for="<?php echo $this->get_field_id( 'exclude_current' ) ?>">
						<?php _e( 'Exclude this widget from the page/post that this widget displays?', 'post-content-shortcodes' ) ?>
                    </label>
                </p>
				<?php
				$this->common_fields( $instance );
			}

			/**
			 * Save and sanitize the settings for this instance of the widget
			 *
			 * @param array $new_instance the new settings for this instance of the widget
			 * @param array $old_instance the original settings for this instance of the widget
			 *
			 * @access public
			 * @return array the sanitized settings for this instance of the widget
			 * @since  0.1
			 */
			public function update( $new_instance, $old_instance ) {
				$instance                    = $this->get_common_values( $new_instance );
				$instance['type']            = 'content';
				$instance['id']              = isset( $new_instance['id'] ) ? absint( $new_instance['id'] ) : 0;
				$instance['post_name']       = isset( $new_instance['post_name'] ) ? esc_attr( $new_instance['post_name'] ) : null;
				$instance['blog_id']         = isset( $new_instance['blog_id'] ) ? $new_instance['blog_id'] : $GLOBALS['blog_id'];
				$instance['exclude_current'] = isset( $new_instance['exclude_current'] ) ? true : 'Do not exclude';
				$instance['title']           = isset( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : null;

				return $instance;
			}
		}
	}
}
