<?php
/**
 * Class definitions for the widgets made available by the post-content-shortcodes WordPress plugin
 * @package WordPress
 * @subpackage Post Content Shortcodes
 * @version 0.3
 */
if( !class_exists( 'PCS_Widget' ) ) {
	/**
	 * Class definition for the generic PCS_Widget parent class.
	 * Must be overridden by either the list or content widget class.
	 */
	class PCS_Widget extends WP_Widget {
		var $defaults = array();
		var $blog_list = false;
		
		function PCS_Widget() {
		}
		
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
			) );
		}
		
		function widget( $args, $instance ) {
			if( !isset( $instance['type'] ) )
				return;
			
			global $post_content_shortcodes_obj;
			
			extract( $args );
			$title = apply_filters( 'widget_title', $instance['title'] );
			
			echo $before_widget;
			if( $title )
				echo $before_title . $title . $after_title;
			unset( $instance['title'] );
			
			switch( $instance['type'] ) {
				case 'content':
					echo $post_content_shortcodes_obj->post_content( $instance );
					break;
				case 'list':
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
			$blogs = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM {$wpdb->blogs} ORDER BY blog_id" ) );
			foreach( $blogs as $blog ) {
				if( empty( $org_blog ) )
					$org_blog = $wpdb->set_blog_id( $blog );
				else
					$wpdb->set_blog_id( $blog );
				
				$this->blog_list[$blog] = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name=%s", 'blogname' ) );
			}
			$wpdb->set_blog_id( $org_blog );
		}
		
		function form() {
		}
		
		function update() {
		}
	}
	
	/**
	 * Class definition for the Post Content Shortcodes "Content" widget
	 */
	class PCS_Content_Widget extends PCS_Widget {
		function PCS_Content_Widget() {
			return self::__construct();
		}
		
		function __construct() {
			parent::__construct();
			
			$widget_ops = array( 'classname' => 'pcs-content-widget', 'description' => 'Display the content of a single post.' );
			$control_ops = array( 'id_base' => 'pcs-content-widget' );
			parent::WP_Widget( 'pcs-content-widget', 'Post Content Widget', $widget_ops, $control_ops );
		}
		
		function form( $instance ) {
			$this->get_blogs();
			$instance = array_merge( $this->defaults, $instance );
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
<p><label for="<?php echo $this->get_field_id( 'id' ) ?>"><?php _e( 'Post ID:' ) ?></label>
	<input class="widefat" type="number" id="<?php echo $this->get_field_id( 'id' ) ?>" name="<?php echo $this->get_field_name( 'id' ) ?>" value="<?php echo $instance['id'] ?>"/></p>
<?php
		}
		
		function update( $new_instance, $old_instance ) {
			$instance['type']	= 'content';
			$instance['id']		= isset( $new_instance['id'] ) ? absint( $new_instance['id'] ) : 0;
			$instance['blog_id']= isset( $new_instance['blog_id'] ) ? $new_instance['blog_id'] : $GLOBALS['blog_id'];
			$instance['show_excerpt'] = true;
			return $instance;
		}
	}
	
	/**
	 * Class definition for the Post Content Shortcodes "List" widget
	 */
	class PCS_List_Widget extends PCS_Widget {
		function PCS_List_Widget() {
			return self::__construct();
		}
		
		function __construct() {
			parent::__construct();
			
			$widget_ops = array( 'classname' => 'pcs-list-widget', 'description' => 'Display a filtered list of posts/pages.' );
			$control_ops = array( 'id_base' => 'pcs-list-widget' );
			parent::WP_Widget( 'pcs-list-widget', 'Post List Widget', $widget_ops, $control_ops );
		}
		
		function form( $instance ) {
			$this->get_blogs();
			$instance = array_merge( $this->defaults, $instance );
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
<p><input type="checkbox" name="<?php $this->get_field_name( 'exclude_current' ) ?>" id="<?php echo $this->get_field_id( 'exclude_current' ) ?>" value="1"/>
	<label for="<?php echo $this->get_field_id( 'exclude_current' ) ?>"><?php _e( 'Exclude the post being viewed from the list of posts?' ) ?></label></p>
<?php
		}
		
		function update( $new_instance, $old_instance ) {
			$instance['type'] 			= 'list';
			$instance['blog_id']		= isset( $new_instance['blog_id'] ) ? $new_instance['blog_id'] : $GLOBALS['blog_id'];
			$instance['post_type']		= isset( $new_instance['post_type'] ) ? $new_instance['post_type'] : null;
			$instance['post_parent']	= isset( $new_instance['post_parent'] ) ? absint( $new_instance['post_parent'] ) : 0;
			$instance['orderby']		= isset( $new_instance['orderby'] ) ? $new_instance['orderby'] : null;
			$instance['order']			= isset( $new_instance['order'] ) ? $new_instance['order'] : 'ASC';
			$instance['numberposts']	= isset( $new_instance['numberposts'] ) ? $new_instance['numberposts'] : 0;
			$instance['post_status']	= isset( $new_instance['post_status'] ) ? $new_instance['post_status'] : 'publish';
			$instance['exclude_current']= isset( $new_instance['exclude_current'] ) ? true : false;
			
			return $instance;
		}
	}
}
?>