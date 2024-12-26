<?php
/**
 * Emails\
 *
 * @class       Vibe_Projects_Mails
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Vibe_Projects_Mails{


    public $mails_version = '1.0';
	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Projects_Mails();
        return self::$instance;
    }

	private function __construct(){

        add_filter('vibebp_touch_all_mails',array($this,'email_templates'),9999);
        
	}

    function email_templates($templates){
        $mails = $this->all_mails();
        $templates = array_merge($templates,$mails);
        return $templates;
    }
    
    //vibe_projects_create_new_project',$project_id,$args,$this->user
        //vibe_projects_member_added',$args['project_id'],$member_id
        //do_action('vibe_projects_notice_added',esc_attr($args['project_id']),$notice);
        

    
    function all_mails(){
        $all_mails = array(
            'vibe_projects_create_new_project'=>array(
                'description'=> __('Project Updated','vibe-projects'),
                'subject' =>  sprintf(__('Project %s updated','vibe-projects'),'{{item.title}}'),
                'message' =>  sprintf(__('%s project has been updated by  %s ','vibe-projects'),'{{item.title}}','{{user.name}}')
            ),
            'vibe_projects_notice_added'=>array(
                'description'=> __('New Project Notice','vibe-projects'),
                'subject' =>  sprintf(__('New notice in project %s','vibe-projects'),'{{item.title}}'),
                'message' =>  sprintf(__('New notice in project %s : %s ','vibe-projects'),'{{item.title}}','{{item.notice}}')
            ),
            'vibe_projects_member_added'=>array(
                'description'=> __('Member Added to Project','vibe-projects'),
                'subject' =>  sprintf(__('You are added to the project %s','vibe-projects'),'{{item.title}}'),
                'message' =>  sprintf(__('You are now added to the project %s by %s','vibe-projects'),'{{item.title}}','{{user.name}}')
            ),
            'vibe_projects_member_removed'=>array(
                'description'=> __('Member removed from the Project','vibe-projects'),
                'subject' =>  sprintf(__('You are removed to the project %s','vibe-projects'),'{{item.title}}'),
                'message' =>  sprintf(__('You are now removed to the project %s by %s','vibe-projects'),'{{item.title}}','{{user.name}}')
            ),
            //project status updated
            'vibe_projects_status_updated'=>array(
                'description'=> __('Project status updated','vibe-projects'),
                'subject' =>  sprintf(__('The status of the project %s was updated','vibe-projects'),'{{item.title}}'),
                'message' =>  sprintf(__('Project %s status updated to %s','vibe-projects'),'{{item.status}}','{{user.name}}')
            ),
            //new board created only public
            //board archived.
            'invite_project_members'=>array(
                'description'=> __('Invite members to Project','vibe-projects'),
                'subject' =>  sprintf(__('Invitation for Project %s','vibe-projects'),'{{item.title}}'),
                'message' =>  sprintf(__('%s has sent you an invite to join the project %s as %s under team %s. Click on this link to join. %s','vibe-projects'),'{{user.name}}','{{item.title}}','{{member.type}}','{{member.team}}','{{{registration.link}}}')
            ),
            'login_member_details'=>array(
                'description'=> __('User site credentials','vibe-projects'),
                'subject' =>  sprintf(__('Your login details','vibe-projects'),'{{item.title}}'),
                'message' =>  sprintf(__('Your login details. Email %s and Password %s','vibe-projects'),'{{user.email}}','{{user.password}}')
            ),
            
            //New Card
            // card status updated
            'vibe_projects_card_label_added'=> array(
                'description'=> __('Card label added','vibe-projects'),
                'subject' =>  sprintf(__('Label added by %s in card %s ','vibe-projects'),'{{user.name}}','{{card.title}}'),
                'message' =>  sprintf(__('Label %s added by %s in card %s','vibe-projects'),'{{label.name}}','{{{user.link}}}','{{{card.link}}}')
            ),
            'vibe_projects_card_label_removed' => array(
                'description'=> __('Card label removed','vibe-projects'),
                'subject' =>   sprintf(__('Label removed by %s in card %s ','vibe-projects'),'{{card.title}}','{{user.name}}'),
                'message' => sprintf(__('Label %s removed by %s in card %s','vibe-projects'),'{{label.name}}','{{{user.link}}}','{{{card.link}}}')
            ),
            'vibe_projects_update_checklist' => array(
                'description'=> __('Card checklist updated','vibe-projects'),
                'subject' =>  sprintf(__('Card %s checklist updated by %s','vibe-projects'),'{{card.title}}','{{user.name}}'),
                'message' =>  sprintf(__('Card %s labels updated by %s','vibe-projects'),'{{{card.link}}}','{{{user.link}}}')
            ),
            'vibe_projects_card_duedate_set' => array(
                'description'=> __('Card due date set','vibe-projects'),
                'subject' =>  sprintf(__('Card %s due date set by %s','vibe-projects'),'{{card.title}}','{{user.name}}'),
                'message' =>  sprintf(__('Due date set to %s by %s in card %s ','vibe-projects'),'{{due.date}}','{{{user.link}}}','{{{card.link}}}')
            ),
            'vibe_projects_upload_attachment'=> array(
                'description'=> __('Attachment uploded in card','vibe-projects'),
                'subject' =>  sprintf(__('Attachment uploaded in card %s by %s','vibe-projects'),'{{card.name}}','{{user.name}}'),
                'message' =>  sprintf(__('Attachment %s uploaded in card %s by %s','vibe-projects'),'{{{attachment.link}}}','{{{card.link}}}','{{{user.link}}}')
            ),
            'vibe_projects_card_completed' => array(
                'description'=> __('Card marked complete','vibe-projects'),
                'subject' =>  sprintf(__('Card %s marked complete by %s','vibe-projects'),'{{card.title}}','{{user.name}}'),
                'message' =>  sprintf(__('Card %s marked complete by %s','vibe-projects'),'{{{card.link}}}','{{{user.link}}}')
            ),
            'vibe_projects_card_milestoned' => array(
                'description'=> __('Card marked as milestone for project','vibe-projects'),
                'subject' =>  sprintf(__('Card %s marked as milestone by %s','vibe-projects'),'{{card.title}}','{{user.name}}'),
                'message' =>  sprintf(__('Card %s marked as milestone by %s','vibe-projects'),'{{{card.link}}}','{{{user.link}}}')
            ),
            'vibe_projects_card_archived' => array(
                'description'=> __('Card archived','vibe-projects'),
                'subject' =>  sprintf(__('Card %s archived by %s','vibe-projects'),'{{card.title}}','{{user.name}}'),
                'message' =>  sprintf(__('Card %s archived by %s','vibe-projects'),'{{{card.link}}}','{{{user.link}}}')
            ),
            'vibe_projects_add_member_to_card' => array(
                'description'=> __('Member added to Card','vibe-projects'),
                'subject' =>  sprintf(__('Assigned to Card %s','vibe-projects'),'{{card.title}}'),
                'message' =>  sprintf(__('You are assigned to Card %s under project %s by %s','vibe-projects'),'{{{card.link}}}','{{item.title}}','{{user.name}}')
            ),
            'vibe_projects_remove_member_from_card' => array(
                'description'=> __('Member removed to Card','vibe-projects'),
                'subject' =>  sprintf(__('Unassigned from Card %s','vibe-projects'),'{{card.title}}'),
                'message' =>  sprintf(__('You are un-assigned from the Card %s under project %s by %s','vibe-projects'),'{{card.title}}','{{item.title}}','{{user.name}}')
            ),
            'vibe_projects_create_new_card' => array(
                'description'=> __('New Card added ','vibe-projects'),
                'subject' =>  sprintf(__('New card added to project %s','vibe-projects'),'{{card.title}}'),
                'message' =>  sprintf(__('Card %s added to board %s in project %s by %s','vibe-projects'),'{{card.title}}','{{board.title}}','{{item.title}}','{{user.name}}')
            ),

        );
        return apply_filters('vibe_projects_all_mails',$all_mails);
    }
}

Vibe_Projects_Mails::init();
