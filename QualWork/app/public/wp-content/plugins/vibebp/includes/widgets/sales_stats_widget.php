<?php
add_action('widgets_init', 'Sales_Stats_Widget');
function Sales_Stats_Widget()
{
    register_widget('Sales_Stats_Widget');
}

class Sales_Stats_Widget extends WP_Widget
{

    /** constructor -- name this the same as the class above */
    public function __construct()
    {
        $widget_ops = array('classname' => 'sales_stats_widget', 'description' => __('Display the sales stats', 'vibebp'));
        $control_ops = array('width' => 300, 'height' => 350, 'id_base' => 'sales_stats_widget');
        parent::__construct('sales_stats_widget', __('DASHBOARD : Administrator - Sales Statistics Widget', 'vibebp'), $widget_ops, $control_ops);

        add_action('wp_enqueue_scripts',array($this,'enqueue_script'));
        add_filter('vibebp_member_dashboard_widgets',array($this,'add_custom_script'));
    }
    
    function add_custom_script($args){
        $args[]='sales_stats_widget';
        return $args;
    }

    function enqueue_script(){
        if(apply_filters('vibebp_enqueue_profile_script',false)){
            wp_enqueue_script('sales_stats_widget', VIBEBP_PLUGIN_URL.'/assets/js/widgets/sales_stats.js', array('wp-element', 'wp-data'), VIBEBP_VERSION,true);
            wp_enqueue_style('sales_stats_widget',VIBEBP_PLUGIN_URL.'/assets/css/widgets/sales_stats.css',array(),VIBEBP_VERSION);
            wp_localize_script('sales_stats_widget', 'sales_stats_widget', array(
                'settings' => array(),
                'api' => rest_url(Vibe_BP_API_NAMESPACE . '/dashboard/widget'),
                'translations' => array(
                    'total_sales' => __('Total Sales', 'vibebp'),
                    'total_instructors_commission' => __('Total Instructors Commission', 'vibebp'),
                    'total_payments_count' => __('Total No. Of Payments', 'vibebp'),
                    'most_earning_by_instructor' => __('Most Earning By Instructor', 'vibebp'),
                    'most_sold_course' => __('Most Sold Course', 'vibebp'),
                    'from' => __('From', 'vibebp'),
                    'to' => __('To', 'vibebp'),
                    'no_data_found' => __('No Data Found!', 'vibebp'),
                    'times'=> __('Times', 'vibebp'),
                ),
            ));
        }
    }
    /** @see WP_Widget::widget -- do not rename this */
    public function widget($args, $instance)
    {
        extract($args);

        //Our variables from the widget settings.
        $title = apply_filters('widget_title', $instance['title']);
        $width = $instance['width'];

		echo $args['before_title'] . $title . $args['after_title'];
        echo '<div class="' . $width . '">
            <div class="dash-widget sales_stats_widget"></div>
        </div>';
        
    }

    /** @see WP_Widget::update -- do not rename this */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['width'] = $new_instance['width'];
        return $instance;
    }

    /** @see WP_Widget::form -- do not rename this */
    public function form($instance)
    {
        $defaults = array(
            'title' => __('Sales Stats widget', 'vibebp'),
            'width' => 'col-md-6 col-sm-12',
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $title = esc_attr($instance['title']);
        $width = esc_attr($instance['width']);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'vibebp');?></label>
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Select Width', 'vibebp');?></label>
          <select id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>">
          	<option value="col-md-3 col-sm-6" <?php selected('col-md-3 col-sm-6', $width);?>><?php _e('One Fourth', 'vibebp');?></option>
          	<option value="col-md-4 col-sm-6" <?php selected('col-md-4 col-sm-6', $width);?>><?php _e('One Third', 'vibebp');?></option>
          	<option value="col-md-6 col-sm-12" <?php selected('col-md-6 col-sm-12', $width);?>><?php _e('One Half', 'vibebp');?></option>
            <option value="col-md-8 col-sm-12" <?php selected('col-md-8 col-sm-12', $width);?>><?php _e('Two Third', 'vibebp');?></option>
             <option value="col-md-8 col-sm-12" <?php selected('col-md-9 col-sm-12', $width);?>><?php _e('Three Fourth', 'vibebp');?></option>
          	<option value="col-md-12" <?php selected('col-md-12', $width);?>><?php _e('Full', 'vibebp');?></option>
          </select>
        </p>
        <?php
	}
}
