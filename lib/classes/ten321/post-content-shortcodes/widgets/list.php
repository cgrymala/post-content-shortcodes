<?php

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace Ten321\Post_Content_Shortcodes\Widgets {
	if ( ! class_exists( 'Content_List' ) ) {
		class Content_List extends PCS_Widget {
			/**
			 * Construct the actual widget object
			 *
			 * @access public
			 * @since  0.1
			 */
			public function __construct() {
				parent::__construct();

				$widget_ops  = array(
					'classname'   => 'pcs-list-widget',
					'description' => __( 'Display a filtered list of posts/pages.', 'post-content-shortcodes' )
				);
				$control_ops = array( 'width' => 400, 'id_base' => 'pcs-list-widget' );
				parent::WP_Widget_construct( 'pcs-list-widget', __( 'Post List Widget', 'post-content-shortcodes' ), $widget_ops, $control_ops );
			}

			/**
			 * Old-style constructor method
			 *
			 * @return Content_List
			 * @since  0.1
			 * @deprecated since 0.9
			 * @access public
			 */
			public function Content_List() {
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
                    <input class="widefat" type="text" name="<?php echo $this->get_field_name( 'title' ) ?>"
                           id="<?php echo $this->get_field_id( 'title' ) ?>"
                           value="<?php echo esc_attr( $instance['title'] ) ?>"/>
                </p>
				<?php
				if ( $this->blog_list ) {
					?>
                    <p>
                        <label for="<?php echo $this->get_field_id( 'blog_id' ) ?>">
							<?php _e( 'List posts from which blog?', 'post-content-shortcodes' ) ?>
                        </label>
                        <select class="widefat" name="<?php echo $this->get_field_name( 'blog_id' ) ?>"
                                id="<?php echo $this->get_field_id( 'blog_id' ) ?>">
                            <option value=""><?php _e( '-- Please select a blog --', 'post-content-shortcodes' ) ?></option>
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
                <p>
                    <label for="<?php echo $this->get_field_id( 'post_type' ) ?>">
						<?php _e( 'Post type:', 'post-content-shortcodes' ) ?>
                    </label>
                    <input type="text" name="<?php echo $this->get_field_name( 'post_type' ) ?>"
                           id="<?php echo $this->get_field_id( 'post_type' ) ?>" class="widefat"
                           value="<?php echo $instance['post_type'] ?>"/>
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id( 'post_parent' ) ?>">
						<?php _e( 'Post parent ID:', 'post-content-shortcodes' ) ?>
                    </label>
                    <input type="number" class="widefat" id="<?php echo $this->get_field_id( 'post_parent' ) ?>"
                           name="<?php echo $this->get_field_name( 'post_parent' ) ?>"
                           value="<?php echo $instance['post_parent'] ?>"/>
                    <br/>
                    <span class="note" style="font-style: italic;">
		<?php _e( 'Leave this blank (or set to 0) to retrieve and display all posts that match the other criteria specified.', 'post-content-shortcodes' ) ?>
	</span>
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id( 'tax_name' ) ?>">
						<?php _e( 'Taxonomy Slug:', 'post-content-shortcodes' ) ?>
                    </label>
                    <input class="widefat" type="text" name="<?php echo $this->get_field_name( 'tax_name' ) ?>"
                           id="<?php echo $this->get_field_id( 'tax_name' ) ?>"
                           value="<?php echo $instance['tax_name'] ?>"/>
                    <br/>
					<?php _e( '<span style="font-style: italic;">If you would like to limit posts to a specific set of terms within a taxonomy, please enter the taxonomy slug above (e.g. "category", "tag", etc.)</span>', 'post-content-shortcodes' ) ?>
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id( 'tax_term' ) ?>">
						<?php _e( 'Term Slugs:', 'post-content-shortcodes' ) ?>
                    </label>
                    <input class="widefat" type="text" name="<?php echo $this->get_field_name( 'tax_term' ) ?>"
                           id="<?php echo $this->get_field_id( 'tax_term' ) ?>"
                           value="<?php echo $instance['tax_term'] ?>"/>
                    <br/>
					<?php _e( '<span style="font-style: italic;">If you would like to limit posts to a specifc set of terms within a taxonomy, please enter a space-separated list of either the term slugs or the term IDs</span>', 'post-content-shortcodes' ) ?>
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id( 'orderby' ) ?>">
						<?php _e( 'Sort posts by:', 'post-content-shortcodes' ) ?>
                    </label>
                    <select class="widefat" name="<?php echo $this->get_field_name( 'orderby' ) ?>"
                            id="<?php echo $this->get_field_id( 'orderby' ) ?>">
						<?php
						$sortfields = array(
							'post_title'    => __( 'Title', 'post-content-shortcodes' ),
							'date'          => __( 'Post Date', 'post-content-shortcodes' ),
							'menu_order'    => __( 'Menu/Page order', 'post-content-shortcodes' ),
							'ID'            => __( 'Post ID', 'post-content-shortcodes' ),
							'author'        => __( 'Author', 'post-content-shortcodes' ),
							'modified'      => __( 'Post Modification Date', 'post-content-shortcodes' ),
							'parent'        => __( 'Post Parent ID', 'post-content-shortcodes' ),
							'comment_count' => __( 'Number of Comments', 'post-content-shortcodes' ),
							'rand'          => __( 'Random', 'post-content-shortcodes' ),
						);

						foreach ( $sortfields as $val => $lbl ) {
							?>
                            <option value="<?php echo $val ?>"<?php selected( $val, $instance['orderby'] ) ?>><?php echo $lbl ?></option>
							<?php
						}
						?>
                    </select>
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id( 'order' ) ?>"><?php _e( 'In which order?', 'post-content-shortcodes' ) ?>
                    </label>
                    <select class="widefat" name="<?php echo $this->get_field_name( 'order' ) ?>"
                            id="<?php echo $this->get_field_id( 'order' ) ?>">
                        <option value="asc"<?php selected( 'asc', strtolower( $instance['order'] ) ) ?>><?php _e( 'Ascending', 'post-content-shortcodes' ) ?></option>
                        <option value="desc"<?php selected( 'desc', strtolower( $instance['order'] ) ) ?>><?php _e( 'Descending', 'post-content-shortcodes' ) ?></option>
                    </select>
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id( 'numberposts' ) ?>">
						<?php _e( 'How many posts should be shown?', 'post-content-shortcodes' ) ?>
                    </label>
                    <input type="number" class="widefat" name="<?php echo $this->get_field_name( 'numberposts' ) ?>"
                           id="<?php echo $this->get_field_id( 'numberposts' ) ?>"
                           value="<?php echo $instance['numberposts'] ?>"/>
                    <br/>
                    <span class="note" style="font-style: italic;">
		<?php _e( 'Leave this set to -1 if you would like all posts to be retrieved and displayed.', 'post-content-shortcodes' ) ?>
	</span>
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id( 'post_status' ) ?>">
						<?php _e( 'Post status:', 'post-content-shortcodes' ) ?>
                    </label>
                    <select class="widefat" name="<?php echo $this->get_field_name( 'post_status' ) ?>"
                            id="<?php echo $this->get_field_id( 'post_status' ) ?>">
						<?php
						$stati = array(
							'publish' => __( 'Published', 'post-content-shortcodes' ),
							'draft'   => __( 'Draft', 'post-content-shortcodes' ),
							'pending' => __( 'Pending Review', 'post-content-shortcodes' ),
							'inherit' => __( 'Inherited', 'post-content-shortcodes' ),
						);

						foreach ( $stati as $val => $lbl ) {
							?>
                            <option value="<?php echo $val ?>"<?php selected( $val, $instance['post_status'] ) ?>><?php echo $lbl ?></option>
							<?php
						}
						?>
                    </select>
                </p>
                <p>
                    <input type="checkbox" name="<?php echo $this->get_field_name( 'ignore_protected' ) ?>"
                           id="<?php echo $this->get_field_id( 'ignore_protected' ) ?>"
                           value="1"<?php checked( $instance['ignore_protected'] ) ?>/>
                    <label for="<?php echo $this->get_field_id( 'ignore_protected' ) ?>">
						<?php _e( 'Exclude password-protected posts from the list?', 'post-content-shortcodes' ) ?>
                    </label>
                </p>
                <p>
                    <input type="checkbox" name="<?php echo $this->get_field_name( 'exclude_current' ) ?>"
                           id="<?php echo $this->get_field_id( 'exclude_current' ) ?>"
                           value="1"<?php checked( $instance['exclude_current'] ) ?>/>
                    <label for="<?php echo $this->get_field_id( 'exclude_current' ) ?>">
						<?php _e( 'Exclude the post being viewed from the list of posts?', 'post-content-shortcodes' ) ?>
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
				$instance = $this->get_common_values( $new_instance );

				$instance['title']            = isset( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : null;
				$instance['type']             = 'list';
				$instance['blog_id']          = isset( $new_instance['blog_id'] ) ? $new_instance['blog_id'] : $GLOBALS['blog_id'];
				$instance['post_type']        = isset( $new_instance['post_type'] ) ? $new_instance['post_type'] : null;
				$instance['post_parent']      = isset( $new_instance['post_parent'] ) && ! empty( $new_instance['post_parent'] ) ? absint( $new_instance['post_parent'] ) : null;
				$instance['orderby']          = isset( $new_instance['orderby'] ) ? $new_instance['orderby'] : null;
				$instance['order']            = isset( $new_instance['order'] ) ? $new_instance['order'] : 'ASC';
				$instance['numberposts']      = isset( $new_instance['numberposts'] ) ? intval( $new_instance['numberposts'] ) : 0;
				$instance['post_status']      = isset( $new_instance['post_status'] ) ? $new_instance['post_status'] : 'publish';
				$instance['exclude_current']  = isset( $new_instance['exclude_current'] );
				$instance['tax_name']         = isset( $new_instance['tax_name'] ) ? esc_attr( $new_instance['tax_name'] ) : null;
				$instance['tax_term']         = isset( $new_instance['tax_term'] ) ? esc_attr( $new_instance['tax_term'] ) : null;
				$instance['ignore_protected'] = isset( $new_instance['ignore_protected'] ) ? true : false;

				return $instance;
			}
		}
	}
}
