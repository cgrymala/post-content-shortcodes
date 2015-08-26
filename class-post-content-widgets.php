<?php
/**
 * Class definitions for the widgets made available by the post-content-shortcodes WordPress plugin
 * @package WordPress
 * @subpackage Post Content Shortcodes
 * @version 0.5.6
 */
if( !class_exists( 'PCS_Widget' ) ) {
	/**
	 * Class definition for the generic PCS_Widget parent class.
	 * Must be overridden by either the list or content widget class.
	 */
	class PCS_Widget extends WP_Widget {
		var $defaults = array();
		var $blog_list = false;
		
		function __construct() {
			$this->defaults = apply_filters( 'post-content-shortcodes-defaults', array(
				'id'			=> 0,
				'post_type'		=> 'post',
				'order'			=> 'asc',
				'orderby'		=> 'post_title',
				'numberposts'	=> -1,
				'post_status'	=> 'publish',
				'post_parent'	=> null,
				'exclude_current'=> true,
				'blog_id'		=> $GLOBALS['blog_id'],
				'offset'		=> null,
				'category'		=> null,
				'include'		=> null,
				'exclude'		=> null,
				'meta_key'		=> null,
				'meta_value'	=> null,
				'post_mime_type'=> null,
				// Whether or not to display the featured image
				'show_image'	=> false,
				// The maximum width of the featured image to be displayed
				'image_width'	=> 0,
				// The maximum height of the featured image to be displayed
				'image_height'	=> 0, 
				// Whether or not to show the content/excerpt of the post(s)
				'show_excerpt'	=> false,
				// The maximum length (in words) of the excerpt
				'excerpt_length'=> 0,
				// Whether or not to show the title with the post(s)
				'show_title'    => false, 
				// Whether or not to show the author's name with the post(s)
				'show_author'   => false, 
				// Whether or not to show the date when the post was published
				'show_date'     => false, 
				/* Added 0.3.3 */
				// Whether or not include the list of comments
				'show_comments' => false, 
				// Whether or not to show the "read more" link at the end of the excerpt
				'read_more' => false, 
				// Whether to include shortcodes in the post content/excerpt
				'shortcodes' => false, 
				/* Added 0.3.4 */
				// Whether to strip out all HTML from the content/excerpt
				'strip_html' => false, 
				/* Added 0.3.4 */
				// Allow the specification of a post name instead of ID
				'post_name' => null
			) );
		}
		
		function WP_Widget_construct( $id, $name, $widget_ops=array(), $control_ops=array() ) {
			parent::__construct( $id, $name, $widget_ops, $control_ops );
		}
		
		function widget( $args, $instance ) {
			if( !isset( $instance['type'] ) )
				return;
			
			global $post_content_shortcodes_obj;
			
			extract( $args );
			$title = apply_filters( 'widget_title', $instance['title'] );
			if ( 'content' == $instance['type'] && $instance['show_title'] )
				$title = null;
			
			echo $before_widget;
			if( $title )
				echo $before_title . $title . $after_title;
			unset( $instance['title'] );
			
			switch( $instance['type'] ) {
				case 'content':
					echo $post_content_shortcodes_obj->post_content( $instance );
					break;
				case 'list':
					unset( $instance['type'] );
					echo $post_content_shortcodes_obj->post_list( $instance );
					break;
			}
			
			echo $after_widget;
		}
		
		function get_blogs() {
			if( !is_multisite() ) {
				error_log( '[PCS Notice]: This site does not appear to be multisite-enabled.' );
				return $this->blog_list = false;
			}
			
			$this->blog_list = array();
			global $wpdb;
			$blogs = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs} ORDER BY blog_id" );
			foreach( $blogs as $blog ) {
				if( empty( $org_blog ) )
					$org_blog = $wpdb->set_blog_id( $blog );
				else
					$wpdb->set_blog_id( $blog );
				
				$this->blog_list[$blog] = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name=%s", 'blogname' ) );
			}
			$wpdb->set_blog_id( $org_blog );
		}
		
		function form( $instance ) {
		}
		
		function update( $new_instance, $old_instance ) {
		}
		
		function common_fields( $instance=array() ) {
?>
<p><input type="checkbox" name="<?php echo $this->get_field_name( 'show_title' ) ?>" id="<?php echo $this->get_field_id( 'show_title' ) ?>" value="1"<?php checked( $instance['show_title'] ) ?>/> 
	<label for="<?php echo $this->get_field_id( 'show_title' ) ?>"><?php _e( 'Display the post title?' ) ?></label></p>
<p><input type="checkbox" name="<?php echo $this->get_field_name( 'show_image' ) ?>" id="<?php echo $this->get_field_id( 'show_image' ) ?>" value="1"<?php checked( $instance['show_image'] ) ?>/> 
	<label for="<?php echo $this->get_field_id( 'show_image' ) ?>"><?php _e( 'Display the featured image with the post?' ) ?></label></p>
<p><?php _e( 'Image Dimensions' ) ?><br/>
	<label for="<?php echo $this->get_field_id( 'image_width' ) ?>"><?php _e( 'Width: ' ) ?></label>
		<input type="number" value="<?php echo intval( $instance['image_width'] ) ?>" name="<?php echo $this->get_field_name( 'image_width' ) ?>" id="<?php echo $this->get_field_id( 'image_width' ) ?>"/><?php _e( 'px' ) ?>
	<?php _e( ' x ' ) ?>
	<label for="<?php echo $this->get_field_id( 'image_height' ) ?>"><?php _e( 'Height: ' ) ?></label> 
		<input type="number" value="<?php echo intval( $instance['image_height'] ) ?>" name="<?php echo $this->get_field_name( 'image_height' ) ?>" id="<?php echo $this->get_field_id( 'image_height' ) ?>"/><?php _e( 'px' ) ?></p>
<p><input type="checkbox" name="<?php echo $this->get_field_name( 'show_excerpt' ) ?>" id="<?php echo $this->get_field_id( 'show_excerpt' ) ?>" value="1"<?php checked( $instance['show_excerpt'] ) ?>/>
	<label for="<?php echo $this->get_field_id( 'show_excerpt' ) ?>"><?php _e( 'Display an excerpt of the post content?' ) ?></label></p>
<p><label for="<?php echo $this->get_field_id( 'excerpt_length' ) ?>"><?php _e( 'Limit the excerpt to how many words?' ) ?></label> 
	<input type="number" value="<?php echo $instance['excerpt_length'] ?>" name="<?php echo $this->get_field_name( 'excerpt_length' ) ?>" id="<?php echo $this->get_field_id( 'excerpt_length' ) ?>"/><br/>
	<em><?php _e( 'Leave set to 0 if you do not want the excerpts limited.' ) ?></em></p>
<p><input type="checkbox" name="<?php echo $this->get_field_name( 'read_more' ) ?>" id="<?php echo $this->get_field_id( 'read_more' ) ?>" value="1"<?php checked( $instance['read_more'] ) ?>/> 
	<label for="<?php echo $this->get_field_id( 'read_more' ) ?>"><?php _e( 'Include a "Read more" link?' ) ?></label></p>
<p><input type="checkbox" name="<?php echo $this->get_field_name( 'shortcodes' ) ?>" id="<?php echo $this->get_field_id( 'shortcodes' ) ?>" value="1"<?php checked( $instance['shortcodes'] ) ?>/> 
	<label for="<?php echo $this->get_field_id( 'shortcodes' ) ?>"><?php _e( 'Allow shortcodes inside of the excerpt?' ) ?></label></p>
<p><input type="checkbox" name="<?php echo $this->get_field_name( 'strip_html' ) ?>" id="<?php echo $this->get_field_id( 'strip_html' ) ?>" value="1"<?php checked( $instance['strip_html'] ) ?>/> 
	<label for="<?php echo $this->get_field_id( 'strip_html' ) ?>"><?php _e( 'Attempt to strip all HTML out of the excerpt?' ) ?></label></p>
<p><input type="checkbox" name="<?php echo $this->get_field_name( 'show_author' ) ?>" id="<?php echo $this->get_field_id( 'show_author' ) ?>" value="1"<?php checked( $instance['show_author'] ) ?>/> 
	<label for="<?php echo $this->get_field_id( 'show_author' ) ?>"><?php _e( 'Display the author\'s name?' ) ?></label></p>
<p><input type="checkbox" name="<?php echo $this->get_field_name( 'show_date' ) ?>" id="<?php echo $this->get_field_id( 'show_date' ) ?>" value="1"<?php checked( $instance['show_date'] ) ?>/> 
	<label for="<?php echo $this->get_field_id( 'show_date' ) ?>"><?php _e( 'Display the publication date?' ) ?></label></p>
<p><input type="checkbox" name="<?php echo $this->get_field_name( 'show_comments' ) ?>" id="<?php echo $this->get_field_id( 'show_comments' ) ?>" value="1"<?php checked( $instance['show_comments'] ) ?>/> 
	<label for="<?php echo $this->get_field_id( 'show_comments' ) ?>"><?php _e( 'Display comments with the post?' ) ?></label></p>
<?php
		}
		
		function get_common_values( $new_instance=array() ) {
			$instance = array();
			$instance['show_title'] = array_key_exists( 'show_title', $new_instance ) ? true : false;
			$instance['show_image'] = array_key_exists( 'show_image', $new_instance ) ? true : false;
			$instance['show_excerpt'] = array_key_exists( 'show_excerpt', $new_instance ) ? true : false;
			$instance['read_more'] = array_key_exists( 'read_more', $new_instance ) ? true : false;
			$instance['shortcodes'] = array_key_exists( 'shortcodes', $new_instance ) ? true : false;
			$instance['strip_html'] = array_key_exists( 'strip_html', $new_instance ) ? true : false;
			$instance['show_author'] = array_key_exists( 'show_author', $new_instance ) ? true : false;
			$instance['show_date'] = array_key_exists( 'show_date', $new_instance ) ? true : false;
			$instance['show_comments'] = array_key_exists( 'show_comments', $new_instance ) ? true : false;
			$instance['excerpt_length'] = array_key_exists( 'excerpt_length', $new_instance ) && is_numeric( $new_instance['excerpt_length'] ) ? intval( $new_instance['excerpt_length'] ) : 0;
			$instance['image_width'] = array_key_exists( 'image_width', $new_instance ) && is_numeric( $new_instance['image_width'] ) ? intval( $new_instance['image_width'] ) : 0;
			$instance['image_height'] = array_key_exists( 'image_height', $new_instance ) && is_numeric( $new_instance['image_height'] ) ? intval( $new_instance['image_height'] ) : 0;
			
			return $instance;
		}
	}
	
	/**
	 * Class definition for the Post Content Shortcodes "Content" widget
	 */
	class PCS_Content_Widget extends PCS_Widget {
		function __construct() {
			parent::__construct();
			
			$widget_ops = array( 'classname' => 'pcs-content-widget', 'description' => 'Display the content of a single post.' );
			$control_ops = array( 'width' => 400, 'id_base' => 'pcs-content-widget' );
			parent::WP_Widget_construct( 'pcs-content-widget', 'Post Content Widget', $widget_ops, $control_ops );
		}
		
		function PCS_Content_Widget() {
			return self::__construct();
		}
		
		function form( $instance ) {
			$this->get_blogs();
			$instance = array_merge( $this->defaults, $instance );
?>
<p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Widget Title:' ) ?></label> 
	<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo esc_attr( $instance['title'] ) ?>"/></p>
<?php
			if( $this->blog_list ) {
?>
<p><label for="<?php echo $this->get_field_id( 'blog_id' ) ?>"><?php _e( 'Show post from which blog?' ) ?></label>
	<select class="widefat" name="<?php echo $this->get_field_name( 'blog_id' ) ?>" id="<?php echo $this->get_field_id( 'blog_id' ) ?>">
    	<option value=""><?php _e( '-- Please select a blog --' ) ?></option>
<?php
				foreach( $this->blog_list as $id=>$name ) {
?>
		<option value="<?php echo $id ?>"<?php selected( $instance['blog_id'], $id ) ?>><?php echo $name ?></option>
<?php
				}
?>
    </select></p>
<?php
			}
?>
<fieldset style="padding: 5px; margin: 5px; border: 1px solid #999">
	<p><label for="<?php echo $this->get_field_id( 'id' ) ?>"><?php _e( 'Post ID:' ) ?></label>
		<input class="widefat" type="number" id="<?php echo $this->get_field_id( 'id' ) ?>" name="<?php echo $this->get_field_name( 'id' ) ?>" value="<?php echo $instance['id'] ?>"/></p>
		<p><?php _e( 'OR' ) ?></p>
	<p><label for="<?php echo $this->get_field_id( 'post_name' ) ?>"><?php _e( 'Post Name (slug):' ) ?></label> 
		<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'post_name' ) ?>" name="<?php echo $this->get_field_name( 'post_name' ) ?>" value="<?php echo $instance['post_name'] ?>"/></p>
</fieldset>
<p><input type="checkbox" name="<?php echo $this->get_field_name( 'exclude_current' ) ?>" id="<?php echo $this->get_field_id( 'exclude_current' ) ?>" value="1"<?php checked( $instance['exclude_current'] ) ?>/> 
	<label for="<?php echo $this->get_field_id( 'exclude_current' ) ?>"><?php _e( 'Exclude this widget from the page/post that this widget displays?' ) ?></label></p>
<?php
			$this->common_fields( $instance );
		}
		
		function update( $new_instance, $old_instance ) {
			$instance = $this->get_common_values( $new_instance );
			$instance['type']	 = 'content';
			$instance['id']		 = isset( $new_instance['id'] ) ? absint( $new_instance['id'] ) : 0;
			$instance['post_name'] = isset( $new_instance['post_name'] ) ? esc_attr( $new_instance['post_name'] ) : null;
			$instance['blog_id'] = isset( $new_instance['blog_id'] ) ? $new_instance['blog_id'] : $GLOBALS['blog_id'];
			$instance['exclude_current'] = isset( $new_instance['exclude_current'] ) ? true : 'Do not exclude';
			$instance['title']   = isset( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : null;
			return $instance;
		}
	}
	
	/**
	 * Class definition for the Post Content Shortcodes "List" widget
	 */
	class PCS_List_Widget extends PCS_Widget {
		function __construct() {
			parent::__construct();
			
			$widget_ops = array( 'classname' => 'pcs-list-widget', 'description' => 'Display a filtered list of posts/pages.' );
			$control_ops = array( 'width' => 400, 'id_base' => 'pcs-list-widget' );
			parent::WP_Widget_construct( 'pcs-list-widget', 'Post List Widget', $widget_ops, $control_ops );
		}
		
		function PCS_List_Widget() {
			return self::__construct();
		}
		
		function form( $instance ) {
			$this->get_blogs();
			$instance = array_merge( $this->defaults, $instance );
?>
<p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Widget Title:' ) ?></label> 
	<input type="text" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo esc_attr( $instance['title'] ) ?>"/></p>
<?php
			if( $this->blog_list ) {
?>
<p><label for="<?php echo $this->get_field_id( 'blog_id' ) ?>"><?php _e( 'List posts from which blog?' ) ?></label>
	<select class="widefat" name="<?php echo $this->get_field_name( 'blog_id' ) ?>" id="<?php echo $this->get_field_id( 'blog_id' ) ?>">
    	<option value=""><?php _e( '-- Please select a blog --' ) ?></option>
<?php
				foreach( $this->blog_list as $id=>$name ) {
?>
		<option value="<?php echo $id ?>"<?php selected( $instance['blog_id'], $id ) ?>><?php echo $name ?></option>
<?php
				}
?>
    </select></p>
<?php
			}
?>
<p><label for="<?php echo $this->get_field_id( 'post_type' ) ?>"><?php _e( 'Post type:' ) ?></label>
	<input type="text" name="<?php echo $this->get_field_name( 'post_type' ) ?>" id="<?php echo $this->get_field_id( 'post_type' ) ?>" class="widefat" value="<?php echo $instance['post_type'] ?>"/></p>
<p><label for="<?php echo $this->get_field_id( 'post_parent' ) ?>"><?php _e( 'Post parent ID:' ) ?></label>
	<input type="number" class="widefat" id="<?php echo $this->get_field_id( 'post_parent' ) ?>" name="<?php echo $this->get_field_name( 'post_parent' ) ?>" value="<?php echo $instance['post_parent'] ?>"/><br>
	<span class="note"><?php _e( 'Leave this blank (or set to 0) to retrieve and display all posts that match the other criteria specified.' ) ?></span></p>
<p><label for="<?php echo $this->get_field_id( 'orderby' ) ?>"><?php _e( 'Sort posts by:' ) ?></label>
	<select class="widefat" name="<?php echo $this->get_field_name( 'orderby' ) ?>" id="<?php echo $this->get_field_id( 'orderby' ) ?>">
<?php
			$sortfields = array( 
				'post_title'	=> __( 'Title' ), 
				'date'			=> __( 'Post Date' ),
				'menu_order'	=> __( 'Menu/Page order' ),
				'ID'			=> __( 'Post ID' ), 
				'author'		=> __( 'Author' ),
				'modified'		=> __( 'Post Modification Date' ),
				'parent'		=> __( 'Post Parent ID' ),
				'comment_count'	=> __( 'Number of Comments' ),
				'rand'			=> __( 'Random' ),
			);
			foreach( $sortfields as $val=>$lbl ) {
?>
		<option value="<?php echo $val ?>"<?php selected( $val, $instance['orderby'] ) ?>><?php echo $lbl ?></option>
<?php
			}
?>
    </select></p>
<p><label for="<?php echo $this->get_field_id( 'order' ) ?>"><?php _e( 'In which order?' ) ?></label>
	<select class="widefat" name="<?php echo $this->get_field_name( 'order' ) ?>" id="<?php echo $this->get_field_id( 'order' ) ?>">
    	<option value="asc"<?php selected( 'asc', strtolower( $instance['order'] ) ) ?>>Ascending</option>
        <option value="desc"<?php selected( 'desc', strtolower( $instance['order'] ) ) ?>>Descending</option>
    </select></p>
<p><label for="<?php echo $this->get_field_id( 'numberposts' ) ?>"><?php _e( 'How many posts should be shown?' ) ?></label>
	<input type="number" class="widefat" name="<?php echo $this->get_field_name( 'numberposts' ) ?>" id="<?php echo $this->get_field_id( 'numberposts' ) ?>" value="<?php echo $instance['numberposts'] ?>"/><br>
	<span class="note"><?php _e( 'Leave this set to -1 if you would like all posts to be retrieved and displayed.' ) ?></span></p>
<p><label for="<?php echo $this->get_field_id( 'post_status' ) ?>"><?php _e( 'Post status:' ) ?></label>
	<select class="widefat" name="<?php echo $this->get_field_name( 'post_status' ) ?>" id="<?php echo $this->get_field_id( 'post_status' ) ?>">
<?php
			$stati = array( 
				'publish'	=> __( 'Published' ), 
				'draft' 	=> __( 'Draft' ),
				'pending' 	=> __( 'Pending Review' ),
				'inherit' 	=> __( 'Inherited' ),
			);
			foreach( $stati as $val=>$lbl ) {
?>
		<option value="<?php echo $val ?>"<?php selected( $val, $instance['post_status'] ) ?>><?php echo $lbl ?></option>
<?php
			}
?>
    </select></p>
<p><input type="checkbox" name="<?php echo $this->get_field_name( 'exclude_current' ) ?>" id="<?php echo $this->get_field_id( 'exclude_current' ) ?>" value="1"<?php checked( $instance['exclude_current'] ) ?>/>
	<label for="<?php echo $this->get_field_id( 'exclude_current' ) ?>"><?php _e( 'Exclude the post being viewed from the list of posts?' ) ?></label></p>
<?php
			$this->common_fields( $instance );
		}
		
		function update( $new_instance, $old_instance ) {
			$instance = $this->get_common_values( $new_instance );
			
			$instance['title']          = isset( $new_instance['title'] ) ? esc_attr( $new_instance['title'] ) : null;
			$instance['type'] 			= 'list';
			$instance['blog_id']		= isset( $new_instance['blog_id'] ) ? $new_instance['blog_id'] : $GLOBALS['blog_id'];
			$instance['post_type']		= isset( $new_instance['post_type'] ) ? $new_instance['post_type'] : null;
			$instance['post_parent']	= isset( $new_instance['post_parent'] ) && ! empty( $new_instance['post_parent'] ) ? absint( $new_instance['post_parent'] ) : null;
			$instance['orderby']		= isset( $new_instance['orderby'] ) ? $new_instance['orderby'] : null;
			$instance['order']			= isset( $new_instance['order'] ) ? $new_instance['order'] : 'ASC';
			$instance['numberposts']	= isset( $new_instance['numberposts'] ) ? intval( $new_instance['numberposts'] ) : 0;
			$instance['post_status']	= isset( $new_instance['post_status'] ) ? $new_instance['post_status'] : 'publish';
			$instance['exclude_current'] = isset( $new_instance['exclude_current'] );
			
			return $instance;
		}
	}
}
?>