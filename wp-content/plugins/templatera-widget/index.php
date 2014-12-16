<?php
/*
Plugin Name: Templatera Widget
Plugin URI: http://www.everdesign.nl
Description: A plugin to activate a widget containing "Templatera" available templates.
Version: 1.0
Author: EverDesign
Author URI: http://www.everdesign.nl
*/


/**
 * Display a widgetized version of your Gravatar Profile
 * http://blog.gravatar.com/2010/03/26/gravatar-profiles/
 */
class wp_my_plugin extends WP_Widget {

	// constructor
	function wp_my_plugin() {
		parent::__construct(
			'np_templatera_widget', // Base ID
			__('Templatera Widget', 'text_domain'), // Name
			array( 'description' => __( 'Show a Templatera template in a sidebar', 'text_domain' ) )
		);
	}

	// widget form creation
	// widget form creation
	function form($instance) {
		// Check value
		$select = $instance ? esc_attr($instance['select']) : '';
		
		?>

		<p>
			<label for="<?php echo $this->get_field_id('select'); ?>"><?php _e('Select template', 'wp_widget_plugin'); ?></label>
			<select name="<?php echo $this->get_field_name('select'); ?>" id="<?php echo $this->get_field_id('select'); ?>" class="widefat">
			<?php
			$options = npGetTemplateList();
			foreach ($options as $k=>$v) {
			echo '<option value="' . $v . '" id="' . $v . '"', $select == $v ? ' selected="selected"' : '', '>', $k, '</option>';
			}
			?>
			</select>
		</p>
		<?php
	}

	// update widget
	function update($new_instance, $old_instance) {
	      $instance = $old_instance;
	      
	     // Fields
	     $instance['select'] = strip_tags($new_instance['select']);
	     return $instance;
	}

	// display widget
	function widget($args, $instance) {

	   // Extract arguments
	   extract( $args );

	   // these are the widget options
	   $select = $instance['select'];

	   // Display the widget
	   echo $before_widget;
	   echo '<div class="widget-text wp_widget_plugin_box">';

	   // Get $select value
	echo do_shortcode('[templatera id='.$select.']');

	   echo '</div>';
	   echo $after_widget;
	}
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("wp_my_plugin");'));


/**
 * Gets list of existing templates. Checks access rules defined by template author.
 * @return array
 */
function npGetTemplateList()
{
    global $current_user;
    get_currentuserinfo();
    $current_user_role = isset($current_user->roles[0]) ? $current_user->roles[0] : false;
    $list = array();
    $templates = get_posts(array(
        'post_type' => 'templatera',
        'numberposts' => -1
    ));
    $post = !empty($_POST['post_id']) ? get_post($_POST['post_id']) : false;
    foreach ($templates as $template) {
        $id = $template->ID;
        $meta_data = get_post_meta($id, 'templatera', true);
        $post_types = isset($meta_data['post_type']) ? $meta_data['post_type'] : false;
        $user_roles = isset($meta_data['user_role']) ? $meta_data['user_role'] : false;
        if (
            (!$post || !$post_types || in_array($post->post_type, $post_types))
            && (!$current_user_role || !$user_roles || in_array($current_user_role, $user_roles))
        ) {
            $list[$template->post_title] = $id;
        }
    }
    return $list; 
}
?>