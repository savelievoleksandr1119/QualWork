<?php
/**
 * Initialise WPLMS Zoom
 *
 * @class       Wplms_Zoom_Actions
 * @author      VibeThemes
 * @category    Admin
 * @package     WPLMS-Zoom/classes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Wplms_Zoom_Actions{

	public static $instance;
	
	public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Zoom_Actions();
        return self::$instance;
    }

	private function __construct(){
        if(function_exists('bp_activity_add')){
            add_action('wplms_zoom_meeting_connected',array($this,'wplms_zoom_meeting_connected'),10,4);
            add_action('wplms_zoom_record_join_activity',array($this,'wplms_zoom_record_join_activity'),10,2);
            add_action('wplms_zoom_meeting_created',array($this,'wplms_zoom_meeting_created'),10,2);
        }
       
    }
    
    function wplms_zoom_meeting_connected($meeting_id,$user_id,$shared_type,$shared_values){
        $type = 'vibe_zoom_connected_'.$shared_type;
        switch ($shared_type) {
            case 'course':
                    foreach ($shared_values as $key => $shared_value) {
                        bp_activity_add( 
                            apply_filters('bp_course_record_activity',array( 
                                    'user_id' => $user_id, 
                                    'action' => __('Course connected with ZOOM meeting','vibe-zoom'),
                                    'content' => sprintf(__('Course %s is connected with ZOOM meeting %s','vibe-zoom'),get_the_title($shared_value),get_the_title($meeting_id)),
                                    'primary_link' => get_permalink($meeting_id), 
                                    'component' => $this->get_component_name($meeting_id,$user_id),
                                    'type' => $type, 
                                    'item_id' => $shared_value, 
                                    'secondary_item_id' => $meeting_id,
                                ) 
                            )
                        );
                    }
                break;
            case 'shared':
                break;
            case 'group':
                break;
            default:
                break;
        }
        
    }

    function wplms_zoom_record_join_activity($meeting_id,$user_id){
        $type = 'vibe_zoom_join';
        bp_activity_add( 
            apply_filters('bp_vibe_zoom_join_record_activity',array( 
                    'user_id' => $user_id, 
                    'action' => __('User joined the ZOOM meeting','vibe-zoom'),
                    'content' => sprintf(__('User %s joined the ZOOM meeting %s','vibe-zoom'),bp_core_get_userlink($user_id),get_the_title($meeting_id)),
                    'primary_link' => get_permalink($meeting_id), 
                    'component' =>  $this->get_component_name($meeting_id,$user_id),
                    'type' => $type, 
                    'item_id' => $meeting_id, 
                    'secondary_item_id' => $user_id,
                ) 
            )
        );
    }

    function wplms_zoom_meeting_created($meeting_id,$user_id){
        $type = 'vibe_zoom_created';
        bp_activity_add( 
            apply_filters('bp_vibe_zoom_created_record_activity',array( 
                    'user_id' => $user_id, 
                    'action' => __('User created the ZOOM meeting','vibe-zoom'),
                    'content' => sprintf(__('User %s created the ZOOM meeting %s','vibe-zoom'),bp_core_get_userlink($user_id),get_the_title($meeting_id)),
                    'primary_link' => get_permalink($meeting_id), 
                    'component' => $this->get_component_name($meeting_id,$user_id),
                    'type' => $type, 
                    'item_id' => $meeting_id, 
                    'secondary_item_id' => $user_id,
                ) 
            )
        );
    }


    function get_component_name($meeting_id,$user_id){
        $component = 'vibe_zoom';
        $shared_type = get_post_meta($meeting_id,'shared_type',true);
        if(!empty($shared_type)){
            switch ($shared_type) {
                case 'course':
                        $component = 'course';
                    break;
                case 'group':
                        $component = 'group';
                    break; 
                case 'shared':
                        $component = 'shared';
                    break;
            }
        }
        return $component;
    }
	
}

add_action('bp_include',function(){
    Wplms_Zoom_Actions::init();
});

