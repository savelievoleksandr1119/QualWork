<?php
/**
 * Activity Vibe Projects
 *
 * @author 		VibeThemes
 * @category 	Init
 * @package 	vibe-projects/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



add_action('bp_activity_register_activity_actions',function(){
    global $bp; 
    $bp_card_actions = vibe_projects_registered_activities();

    foreach($bp_card_actions as $key => $value){
        bp_activity_set_action($bp->activity->id,$key,$value);  
    }
});

function vibe_projects_registered_activities($type=null){
    global $bp; 
    $bp_card_actions=apply_filters('bp_card_register_actions',array(
        'project_member_added'=> __( 'Member added to Project', 'vibe-projects' ),
        'project_member_removed'=> __( 'Member removed to Project', 'vibe-projects' ),
        'project_milestone_added'=> __('Milestone added to project.','vibe-projects'),
        'project_milestone_deleted'=> __( 'Milestone removed from Project', 'vibe-projects' ),
        'new_project'=> __( 'New Project', 'vibe-projects' ),
        'project_updated'=> __( 'Project updated', 'vibe-projects' ),
        'card_created' => __( 'A New Card Created', 'vibe-projects' ),
        'card_comment' => __( 'Card Comment Added', 'vibe-projects' ),
        'card_checklist' => __( 'Checklist Added in Card', 'vibe-projects' ),
        'card_attachment' => __( 'Card Attachment Added', 'vibe-projects' ),
        'card_delete_attachment' => __( 'Card Attachment Deleted', 'vibe-projects' ),
        'card_movement' => __( 'Card Moved', 'vibe-projects' ),
        'card_member_removed'=> __( 'Card member removed', 'vibe-projects' ),
        'card_member_added'=> __( 'Card member added', 'vibe-projects' ),
        'card_completed'=> __( 'Card completed', 'vibe-projects' ),
        'card_checklist'=> __( 'Checklist added to card', 'vibe-projects' ),
        'card_status'=> __( 'Card status updated', 'vibe-projects' ),
        'card_due_date'=> __( 'Card start & due dates updated', 'vibe-projects' ),
        'card_label_added'=> __( 'Card label updated', 'vibe-projects' ),
        'card_watchers_updated'=> __( 'Card watchers updated', 'vibe-projects' ),
    ));

    if(!empty($type)){
        return $bp_card_actions[$type];
    }
    return $bp_card_actions;

}

add_filter('vibebp_api_get_activity',function($activity_args,$args){
    if($args['filter'] == 'vibe_projects'){
        if(!empty($args['sorter'])){
            $activity_args['filter']['action'] = $args['sorter'];   
        }
    }
    return $activity_args;
},10,2);
add_filter('vibebp_vars',function($vars){
    $activities = vibe_projects_registered_activities();
    foreach($activities as $key => $label){
        $vars['components']['activity']['sorters'][$key]= $label;
    }
    
    return $vars;
});


class Vibe_Projects_Activity{


	public static $instance;

    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Projects_Activity();
        return self::$instance;
    }


    function __construct(){

        //purane actions
        add_action('vibe_projects_create_new_project',[$this,'project_create_activity'],10,3);
        add_action('vibe_projects_create_new_card',array($this,'vibe_projects_new_card_created'),10, 3);
        add_action('vibe_projects_card_duedate_set',array($this,'vibe_projects_card_duedate_action'),10, 3);
        add_action('vibe_projects_card_startdate_set',array($this,'vibe_projects_card_startdate_action'),10, 3);


        add_action('vibe_projects_add_checklist',array($this,'vibe_projects_add_checklist_action'),10, 2);
        add_action('vibe_projects_card_upload_attachment',array($this,'vibe_projects_card_upload_attachment'),10, 3);
        add_action('vibe_projects_card_delete_attachment',array($this,'vibe_projects_card_delete_attachment'),10, 3);
       add_action('vibe_projects_card_moved',array($this,'card_movement'),10,5);

        
        add_action('vibe_projects_add_member_to_card',array($this,'vibe_projects_add_member_to_card'),10,4);
        add_action('vibe_projects_remove_member_from_card',array($this,'vibe_projects_remove_member_from_card'),10,4);

        add_action('vibe_projects_card_completed',array($this,'vibe_projects_card_completed'),10,3);

        add_action('vibe_projects_member_added',[$this,'member_added'],10,2);
        add_action('vibe_projects_member_removed',[$this,'member_removed'],10,2);

        add_action('vibe_projects_milestone_created',[$this,'milestone_created'],10,3);
        add_action('vibe_projects_milestone_deleted',[$this,'milestone_deleted'],10,3);

        add_action('vibe_projects_card_status_updated',[$this,'card_status_updated'],10,4);
        
        add_action('vibe_projects_card_checklists',[$this,'card_checklists'],10,3);
        add_action('vibe_projects_card_milestoned',[$this,'milestone_created'],10,3);
        add_action('vibe_projects_card_set_due_date',[$this,'card_set_due_date'],10,3);
        add_action('vibe_projects_card_label_added',[$this,'label_added'],10,3);
        add_action('vibe_projects_card_watchers_updated',[$this,'watchers_updated'],10,3);
        add_action('vibe_projects_card_archived',[$this,'watchers_updated'],10,3);
    }


    function card_checklists($card_id,$user_id,$project_id){
         $this->record_activity([
            'user_id'=>$user_id,
            'type'=>'card_checklist',
            'item_id'=>$project_id,
            'secondary_item_id'=>$card_id,
            'content'=>sprintf(_x('Checklist was added in Card %s','activity','vibe-projects'), get_the_title($card_id)),
        ]);
    }

    function card_set_due_date($card_id,$user_id,$project_id){
        $this->record_activity([
            'user_id'=>$user_id,
            'type'=>'card_due_date',
            'item_id'=>$project_id,
            'secondary_item_id'=>$card_id,
            'content'=>sprintf(_x('DueDate updated in Card %s','activity','vibe-projects'), get_the_title($card_id)),
        ]);
    }

    function label_added($card_id,$user_id,$project_id){
        $this->record_activity([
            'user_id'=>$user_id,
            'type'=>'card_label_added',
            'item_id'=>$project_id,
            'secondary_item_id'=>$card_id,
            'content'=>sprintf(_x('Labels updated in Card %s','activity','vibe-projects'), get_the_title($card_id)),
        ]);
    }

    function watchers_updated($card_id,$user_id,$project_id){
         $this->record_activity([
            'user_id'=>$user_id,
            'type'=>'card_watchers_updated',
            'item_id'=>$project_id,
            'secondary_item_id'=>$card_id,
            'content'=>sprintf(_x('Added as a Watcher to Card %s','activity','vibe-projects'), get_the_title($card_id)),
        ]);
    }


    function card_status_updated($card_id,$project_id,$post_status,$user){

        $statuses = vibe_projects_get_statuses('card');
        $label= _x('Active','default card status','vibe-projects');
        foreach($statuses as $status){
            if($status['value'] == $post_status){
                $label = $status['label'];
                break;
            }
        }
        $this->record_activity([
            'user_id'=>$user->id,
            'type'=>'card_status',
            'item_id'=>$project_id,
            'secondary_item_id'=>$card_id,
            'content'=>sprintf(_x('Task %s status was updated %s','activity','vibe-projects'), get_the_title($card_id),$label),
        ]);
    }

    function milestone_created($milestone_id,$project_id,$member_id){
        $activity_id = $this->record_activity(array(
              'content' =>sprintf(_x('Milestone %s was added to project %s','activity','vibe-projects'), get_the_title($milestone_id),get_the_title($project_id)),
              'type' => 'project_milestone_added',
              'item_id' => $project_id,
              'user_id'=>$member_id
            ));
    }
    function milestone_deleted($milestone_id,$project_id,$member_id){
        $activity_id = $this->record_activity(array(
              'content' =>sprintf(_x('Milestone %s was removed from the project %s','activity','vibe-projects'), get_the_title($milestone_id),get_the_title($project_id)),
              'type' => 'project_milestone_deleted',
              'item_id' => $project_id,
              'user_id'=>$member_id
            ));
    }
    function member_added($project_id,$member_id){
        $activity_id = $this->record_activity(array(
              'action' => __('Member added to project.','vibe-projects'),
              'content' =>sprintf(_x('Member %s was added to project %s','activity','vibe-projects'), bp_core_get_user_displayname($member_id),get_the_title($project_id)),
              'type' => 'project_member_added',
              'item_id' => $project_id,
              'user_id'=>$member_id
            ));
    }

    function member_removed($project_id,$member_id){
        $activity_id = $this->record_activity(array(
              'action' => __('Member removed from project.','vibe-projects'),
              'content' =>sprintf(_x('Member %s was removed from project %s','activity','vibe-projects'), bp_core_get_user_displayname($member_id),get_the_title($project_id)),
              'type' => 'project_member_removed',
              'item_id' => $project_id,
              'user_id'=>$member_id
            ));
    }

    function project_create_activity($project_id,$args,$user){
        if(!empty($args['id'])){

            $activity_id = $this->record_activity(array(
              'action' => __('Project details updated.','vibe-projects'),
              'content' =>sprintf(_x('Project %s details was updated by %s','activity','vibe-projects'), get_the_title($project_id), bp_core_get_user_displayname($user->id)),
              'type' => 'project_updated',
              'item_id' => $project_id,
              'user_id'=>$user->id,
            ));

        }else{
            $activity_id = $this->record_activity(array(
              'action' => __('Project created.','vibe-projects'),
              'content' =>sprintf(_x('A new project %s was created by %s','activity','vibe-projects'), get_the_title($project_id), bp_core_get_userlink($user->id)),
              'type' => 'new_project',
              'item_id' => $project_id,
              'user_id'=>$user->id,
            ));
        }
        
    }

    function vibe_projects_card_completed($card_id,$user_id,$project_id){
        $activity_id = $this->record_activity(array(
          'action' => __('Card marked complete.','vibe-projects'),
          'content' =>sprintf(_x('Card %s marked complete by %s','activity','vibe-projects'), get_the_title($card_id),bp_core_get_userlink($user_id)),
          'type' => 'card_completed',
          'item_id' => $project_id,
          'secondary_item_id' => $card_id,
          'user_id'=>$user_id,
        ));
    }

    function vibe_projects_add_member_to_card($card_id,$member,$project_id,$user_id){
        $activity_id = $this->record_activity(array(
          'action' => __('Member added to card','vibe-projects'),
          'content' =>sprintf(_x('%s added to card %s','activity','vibe-projects'),bp_core_get_user_displayname($member), get_the_title($card_id)),
          'type' => 'card_member_added',
          'item_id' => $project_id,
          'secondary_item_id' => $card_id,
          'user_id'=>$member,
        ));
    }

    function vibe_projects_remove_member_from_card($card_id,$member,$project_id,$user_id){
        $activity_id = $this->record_activity(array(
          'action' => __('Member removed from card','vibe-projects'),
          'content' =>sprintf(_x('%s removed from card %s','activity','vibe-projects'),bp_core_get_user_displayname($member), get_the_title($card_id)),
          'type' => 'card_member_removed',
          'item_id' => $project_id,
          'secondary_item_id' => $card_id,
          'user_id'=>$member
        ));
    }

    


    function record_activity($args = ''){

      if(!function_exists('bp_is_active') || !bp_is_active('activity'))
        return;

        global $bp;
        $defaults = array(
            'id' => false,
            'user_id' => $bp->loggedin_user->id,
            'action' => '',
            'content' => '',
            'primary_link' => '',
            'component' => 'vibe_projects',
            'type' => false,
            'item_id' => false,
            'secondary_item_id' => false,
            'recorded_time' => gmdate( "Y-m-d H:i:s" ),
            'hide_sitewide' => false
        );

        $r = wp_parse_args( $args, $defaults );
        extract( $r );
        return bp_activity_add( 
            apply_filters('vibe_projects_record_activity',
                array( 
                    'id' => $id, 
                    'user_id' => $user_id, 
                    'action' => empty($action)?vibe_projects_registered_activities($type):$action, 
                    'content' => $content, 
                    'primary_link' => $primary_link, 
                    'component' => $component, 
                    'type' => $type, 
                    'item_id' => $item_id, 
                    'secondary_item_id' => $secondary_item_id, 
                    'recorded_time' => $recorded_time, 
                    'hide_sitewide' => $hide_sitewide 
                ) 
        ));

    }

    function record_activity_meta($activity_id,$meta_key,$meta_value){
      if(!function_exists('bp_is_active') || !bp_is_active('activity'))
        return;

        return bp_activity_update_meta($activity_id,$meta_key,$meta_value);
    }


    function vibe_projects_new_card_created($return,$args,$user_id){

        $activity_id = $this->record_activity(array(
          'action' => __('New card created ','vibe-projects'),
          'content' =>sprintf(_x('%s Created Card %s','activity','vibe-projects'),bp_core_get_user_displayname($user_id), get_the_title($return['card_id'])),
          'type' => 'card_created',
          'primary_link' => '',
          'item_id' =>  get_post_meta($return['card_id'],'vibe_card_project',true),
          'secondary_item_id' => $return['card_id']
        ));
        if(is_numeric($activity_id)){
          $this->record_activity_meta($activity_id,'cards_data',$args['cards']);
        }
        
    }


    function vibe_projects_card_duedate_action($card_id,$user_id,$date){

         $this->record_activity([
            'user_id'=>$user_id,
            'type'=>'card_due_date',
            'item_id'=> get_post_meta($card_id,'vibe_card_project',true),
            'secondary_item_id'=>$card_id,
            'content'=>sprintf(_x('Due Date updated in Card %s to %s','activity','vibe-projects'), get_the_title($card_id),(empty($date['date'])?(is_numeric($date)?wp_date( get_option( 'date_format' ), $date ):'N.A'):$date['date'])),
        ]);
    }

    function vibe_projects_card_startdate_action($card_id,$user_id,$date){

         $this->record_activity([
            'user_id'=>$user_id,
            'type'=>'card_due_date',
            'item_id'=>get_post_meta($card_id,'vibe_card_project',true),
            'secondary_item_id'=>$card_id,
            'content'=>sprintf(_x('Start Date updated in Card %s to %s','activity','vibe-projects'), get_the_title($card_id),(empty($date['date'])?(is_numeric($date)?wp_date( get_option( 'date_format' ), $date ):'N.A'):$date['date'])),
        ]);
    }

    function vibe_projects_add_checklist_action($checklist, $card_id){

       $this->record_activity(array(
          'action' => __('card_add_checklist','vibe-projects'),
          'content' =>sprintf(_x('Added %s to this Card','activity','vibe-projects'), $checklist),
          'type' => 'card_checklist',
          'primary_link' => '',
          'item_id' => get_post_meta($card_id,'vibe_card_project',true),
          'secondary_item_id' => $card_id
        ));

    }

     function vibe_projects_card_upload_attachment($attachment, $card_id,$user_id){

       $this->record_activity(array(
          'action' => __('card_attachment_added','vibe-projects'),
          'content' =>sprintf(_x('Attached %s to this Card','activity','vibe-projects'), $attachment),
          'type' => 'card_attachment', 
          'primary_link' => '',
          'user_id'=>$user_id,
          'item_id' => get_post_meta($card_id,'vibe_card_project',true),
          'secondary_item_id' => $card_id,
        ));

    }

    function vibe_projects_card_delete_attachment($attachment, $card_id,$user_id){

       $this->record_activity(array(
          'action' => __('Card attachment deleted','vibe-projects'),
          'content' =>sprintf(_x('%s Deleted %s to this Card','activity','vibe-projects'),$attachment),
          'type' => 'card_delete_attachment',
          'primary_link' => '',
          'item_id' => get_post_meta($card_id,'vibe_card_project',true),
          'secondary_item_id' => $card_id,
          'user_id'=>$user_id
        ));

    }

    function card_movement($card_id,$card_old_list_id,$card_new_list_id,$user,$board_id){
        $this->record_activity(array(
          'action' => __('Card Moved','vibe-projects'),
          'content' =>sprintf(_x('%s moved card %s from %s to %s ','activity','vibe-projects'),$user->display_name,get_the_title($card_id),get_term( $card_old_list_id )->name,get_term( $card_new_list_id )->name),
          'type' => 'card_movement', 
          'primary_link' => '',
          'item_id' => get,
          'secondary_item_id' => $card_id
        ));
    }
/*
    function vibe_projects_move_card_action($listName, $card_id){

       $this->record_activity(array(
          'action' => __('card_moved','vibe-projects'),
          'content' =>sprintf(_x('Moved this card from Test to Testing','activity','vibe-projects'), $listName),
          'type' => 'move_card',
          'primary_link' => '',
          'item_id' => $card_id,
          'secondary_item_id' => ''
        ));

    }

*/

}
Vibe_Projects_Activity::init();


