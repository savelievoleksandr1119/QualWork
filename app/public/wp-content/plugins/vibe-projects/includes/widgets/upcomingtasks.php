<?php 


/* To CHECK :
        DATE FORMAT
        To check unique entry created variable -- $check_last_recorded_date
        Need to add a action in attendance activity  -- attendance removed to make absent


*/

add_action( 'widgets_init', 'vibe_projects_upcoming_tasks_widget' );
function vibe_projects_upcoming_tasks_widget() {
    register_widget('vibe_projects_upcoming_tasks');
}

class vibe_projects_upcoming_tasks extends WP_Widget {

    protected static $did_script = false;
    /** constructor -- name this the same as the class above */
    function __construct() {
        $widget_ops = array( 'classname' => 'vibe_projects_upcoming_tasks', 'description' => __('Upcoming Tasks', 'vibe-projects') );
        $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'vibe_projects_upcoming_tasks' );
        parent::__construct( 'vibe_projects_upcoming_tasks', __('DASHBOARD : Upcoming Tasks', 'vibe-projects'), $widget_ops, $control_ops );

        add_action('wp_enqueue_scripts',array($this,'enqueue_scripts'));
        add_filter('vibebp_member_dashboard_widgets',array($this,'add_custom_script'));
    }


    function enqueue_scripts(){
        if(!defined('VIBEBP_PLUGIN_URL'))
            return;
        if(apply_filters('vibebp_enqueue_profile_script',false)){
            
            wp_enqueue_script('vibe_projects_upcoming_tasks',plugins_url('../../assets/js/upcomingtasks.js',__FILE__), array('wp-element', 'wp-data','chartjs-js'), VIBEPROJECTS_VERSION,true);
            wp_enqueue_style('vibe_projects_upcoming_tasks',plugins_url('../../assets/css/upcomingtasks.css',__FILE__),array(),VIBEPROJECTS_VERSION);
            
            wp_localize_script('vibe_projects_upcoming_tasks', 'upcomingtasks', array(
                'settings' => array(
                    'date_format' => apply_filters('vibe_projects_date_format',vibeProjectsConvertPhpToJsMomentFormat(get_option('date_format'))),
                    'time_format' => apply_filters('vibe_projects_time_format',vibeProjectsConvertPhpToJsMomentFormat(get_option('time_format'))),
                ),
                'api' => rest_url(VIBE_PROJECTS_API_NAMESPACE),
                'translations' => array(
                   'task_name'=>_x('Task name','','vibe-projects'),
                   'due_date'=>_x('Due date','','vibe-projects'),
                   'project'=>_x('Project','','vibe-projects'),
                    
                ),
            ));
        }  
    }

    function add_custom_script($x){
        $x[] = 'vibe_projects_upcoming_tasks';
        return $x;
    }
    
    function widget( $args, $instance ) {

        extract( $args );
        $title = apply_filters('widget_title', $instance['title'] );
        $width = $instance['width'];
        
        if(empty($width)){
            $width = 'col-md-6';
        }

        global $bp;

        echo '<div class="'.$width.'">
        <div class="dash-widget">'.$before_widget;

        // Display the widget title 
        if ( $title )
            echo '<h4 class="dash_widget_title">'.$title.'</h4>';          
        echo '<div id="vibe_projects_upcoming_tasks"></div>';
       
        echo $after_widget.'</div></div>';
    }

    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['width'] = $new_instance['width'];
        return $instance;
    }
   
      /** @see WP_Widget::form -- do not rename this */
    function form($args) {  
        $defaults = array( 
            'title'  => __('Upcoming Tasks','vibe-projects'),
            'width' => 'col-md-6 col-sm-12'
        );
        $args = wp_parse_args( $args, $defaults );
        extract($args);

        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','vibe-projects'); ?></label> 
            <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Select Width','vibe-projects'); ?></label> 
            <select id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>">
                <option value="col-md-3 col-sm-6" <?php selected('col-md-3 col-sm-6',$width); ?>><?php _e('One Fourth','vibe-projects'); ?></option>
                <option value="col-md-4 col-sm-6" <?php selected('col-md-4 col-sm-6',$width); ?>><?php _e('One Third','vibe-projects'); ?></option>
                <option value="col-md-6 col-sm-12" <?php selected('col-md-6 col-sm-12',$width); ?>><?php _e('One Half','vibe-projects'); ?></option>
              <option value="col-md-8 col-sm-12" <?php selected('col-md-8 col-sm-12',$width); ?>><?php _e('Two Third','vibe-projects'); ?></option>
               <option value="col-md-8 col-sm-12" <?php selected('col-md-9 col-sm-12',$width); ?>><?php _e('Three Fourth','vibe-projects'); ?></option>
                <option value="col-md-12" <?php selected('col-md-12',$width); ?>><?php _e('Full','vibe-projects'); ?></option>
            </select>
        </p>
        <?php 
    }
} 