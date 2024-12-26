<?php

class Vibe_Projects_TouchPoints {


	public static $instance;
    public $notice_members;
    public $member_id;

    public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Projects_TouchPoints;
        return self::$instance;
    }

	private function __construct(){

		add_filter('vibebp_touch_points',array($this,'touch_points'));
    	

		$this->run_touchpoints();

	}

	
	

	function run_touchpoints(){

		if(!defined('VIBE_BP_SETTINGS'))
			return;
		if(empty($this->vibebp_settings))
			$this->vibebp_settings = get_option(VIBE_BP_SETTINGS);

		if(!empty($this->vibebp_settings['touch']) && isset($this->vibebp_settings) && isset($this->vibebp_settings['touch']) && is_array($this->vibebp_settings['touch'])){

			foreach($this->vibebp_settings['touch'] as $key => $value){
				if(!empty($this->get_touch_points()[$key])){
					$hook = $this->get_touch_points()[$key]['hook'];
					if(!empty($hook)){
						
						if(!empty($value['student']['message'])){
							$student_fx = 'student_message_'.$key;
							//print_r($hook);print_r('----------');print_r($student_fx);print_r('----------');
							if(function_exists($student_fx)){
								add_action($hook,$student_fx,10,$this->get_touch_points()[$key]['params']);
							}else{
								add_action($hook,array($this,$student_fx),10,$this->get_touch_points()[$key]['params']);	
							}
						}

						if(!empty($value['student']['notification'])){
							$student_fx = 'student_notification_'.$key;
							if(function_exists($student_fx)){
								add_action($hook,$student_fx,9,$this->get_touch_points()[$key]['params']);
							}else{
								add_action($hook,array($this,$student_fx),9,$this->get_touch_points()[$key]['params']);	
							}	
						}

						if(!empty($value['student']['email'])){
							$student_fx = 'student_email_'.$key;
							if(function_exists($student_fx)){
								add_action($hook,$student_fx,10,$this->get_touch_points()[$key]['params']);
							}else{
								add_action($hook,array($this,$student_fx),10,$this->get_touch_points()[$key]['params']);	
							}
						}

						if(!empty($value['instructor']['message'])){
							$instructor_fx = 'instructor_message_'.$key;
							if(function_exists($instructor_fx)){
								add_action($hook,$instructor_fx,15,$this->get_touch_points()[$key]['params']);
							}else{
								add_action($hook,array($this,$instructor_fx),15,$this->get_touch_points()[$key]['params']);	
							}
						}

						if(!empty($value['instructor']['notification'])){
							$instructor_fx = 'instructor_notification_'.$key;
							if(function_exists($instructor_fx)){
								add_action($hook,$instructor_fx,15,$this->get_touch_points()[$key]['params']);
							}else{
								add_action($hook,array($this,$instructor_fx),15,$this->get_touch_points()[$key]['params']);	
							}
						}

						if(!empty($value['instructor']['email'])){
							$instructor_fx = 'instructor_email_'.$key;
							if(function_exists($instructor_fx)){
								add_action($hook,$instructor_fx,15,$this->get_touch_points()[$key]['params']);
							}else{
								add_action($hook,array($this,$instructor_fx),15,$this->get_touch_points()[$key]['params']);	
							}
						}

						if(!empty($value['admin']['message'])){
							$admin_fx = 'admin_message_'.$key;

							if(function_exists($admin_fx)){
								add_action($hook,$admin_fx,25,$this->get_touch_points()[$key]['params']);
							}else{
								add_action($hook,array($this,$admin_fx),25,$this->get_touch_points()[$key]['params']);	
							}
						}

						if(!empty($value['admin']['notification'])){
							$admin_fx = 'admin_notification_'.$key;
							if(function_exists($admin_fx)){

								add_action($hook,$admin_fx,25,$this->get_touch_points()[$key]['params']);
							}else{
								add_action($hook,array($this,$admin_fx),25,$this->get_touch_points()[$key]['params']);	
							}
						}

						if(!empty($value['admin']['email'])){
							$admin_fx = 'admin_email_'.$key;
							if(function_exists($admin_fx)){
								add_action($hook,$admin_fx,25,$this->get_touch_points()[$key]['params']);
							}else{
								add_action($hook,array($this,$admin_fx),25,$this->get_touch_points()[$key]['params']);	
							}
						}
					}
				}
				
				
			}
		}
	}

	function get_admins(){
		$admin_ids=array();
		if(empty($this->admin_ids)){
			$this->admin_ids = array();
		 	$user_query = new WP_User_Query( array( 'role' => 'Administrator' ,'fields' => array('ID','user_email')) );
			foreach( $user_query->results as $user){
				$admin_ids[] = array('ID' => $user->ID,'email'=> $user->user_email);
			}
			$this->admin_ids = $admin_ids;
		}

		return $this->admin_ids;
	}

	function touch_points($args){
		foreach ($this->get_touch_points() as $key => $touchpoint) {
			$args[$key] = $touchpoint;
		}
    	return $args;
    }

    function get_touch_points(){


    	$args['vibe_projects_create_new_project'] = array(
			'label' => __('Project Created/Updated','vibe-projects'),
			'name' =>'vibe_projects_create_new_project',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_create_new_project&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_create_new_project',
			'params'=>3,
		);
    	

    	$args['vibe_projects_member_added'] = array(
			'label' => __('Member added to project','vibe-projects'),
			'name' =>'vibe_projects_member_added',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_member_added&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_member_added',
			'params'=>3,
		);

		$args['vibe_projects_member_removed'] = array(
			'label' => __('Member removed from the project','vibe-projects'),
			'name' =>'vibe_projects_member_removed',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_member_removed&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_member_removed',
			'params'=>3,
		);

    	
    	$args['vibe_projects_notice_added'] = array(
			'label' => __('Notice added to project','vibe-projects'),
			'name' =>'vibe_projects_notice_added',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_notice_added&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_notice_added',
			'params'=>2,
		);

    	$args['vibe_projects_card_label_added'] = array(
			'label' => __('Card labels added','vibe-projects'),
			'name' =>'vibe_projects_card_label_added',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_card_label_added&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_card_label_added',
			'params'=>3,
		);
		$args['vibe_projects_card_label_removed'] = array(
			'label' => __('Card labels removed','vibe-projects'),
			'name' =>'vibe_projects_card_label_removed',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_card_label_removed&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_card_label_removed',
			'params'=>3,
		);

    	$args['vibe_projects_update_checklist'] = array(
			'label' => __('Card checklist updated','vibe-projects'),
			'name' =>'vibe_projects_update_checklist',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_update_checklist&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_update_checklist',
			'params'=>3,
		);

		$args['vibe_projects_card_duedate_set'] = array(
			'label' => __('Card due date set','vibe-projects'),
			'name' =>'vibe_projects_card_duedate_set',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_card_duedate_set&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_card_duedate_set',
			'params'=>3,
		);

		$args['vibe_projects_upload_attachment'] = array(
			'label' => __('Attachment uploaded to card','vibe-projects'),
			'name' =>'vibe_projects_upload_attachment',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_upload_attachment&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_upload_attachment',
			'params'=>4,
		);

		$args['vibe_projects_card_completed'] = array(
			'label' => __('Card marked completed','vibe-projects'),
			'name' =>'vibe_projects_card_completed',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_card_completed&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_card_completed',
			'params'=>3,
		);
		$args['vibe_projects_card_milestoned'] = array(
			'label' => __('Card added as milestone for project','vibe-projects'),
			'name' =>'vibe_projects_card_milestoned',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_card_milestoned&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_card_milestoned',
			'params'=>3,
		);

		$args['vibe_projects_card_archived'] = array(
			'label' => __('Card archived','vibe-projects'),
			'name' =>'vibe_projects_card_archived',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_card_archived&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_card_archived',
			'params'=>3,
		);
		
		//do_action('vibe_projects_create_new_card',$return,$args['cards'],$this->user->id);
		$args['vibe_projects_create_new_card'] = array(
			'label' => __('New Card','vibe-projects'),
			'name' =>'vibe_projects_create_new_card',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_create_new_card&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_create_new_card',
			'params'=>3,
		);

		$args['vibe_projects_add_member_to_card'] = array(
			'label' => __('Member added to Card','vibe-projects'),
			'name' =>'vibe_projects_add_member_to_card',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_add_member_to_card&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_add_member_to_card',
			'params'=>4,
		);
		$args['vibe_projects_remove_member_from_card'] = array(
			'label' => __('Member removed from Card','vibe-projects'),
			'name' =>'vibe_projects_remove_member_from_card',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_remove_member_from_card&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_remove_member_from_card',
			'params'=>4,
		);

		$args['vibe_projects_card_status_updated'] = array(
			'label' => __('Card status updated [Members + Watchers]','vibe-projects'),
			'name' =>'vibe_projects_card_status_updated',
			'value' => array(
				'student' => admin_url('edit.php?taxonomy=bp-email-type&term=vibe_projects_card_status_updated&post_type=bp-email'),
			),
			'type' => 'touchpoint',
			'hook' => 'vibe_projects_card_status_updated',
			'params'=>4,
		);
		

    	return apply_filters('vibe_projects_touch_points',$args);

    }

    /* == MEMBER REMOVED FROM PROJECT == */

    function student_message_vibe_projects_member_removed($project_id,$member_id,$user){

	    if(bp_is_active('messages')){
	    	
	      	vibe_projects_messages_new_message( array('sender_id' => $user->id, 'subject' => sprintf(__('You were removed from the project %s ','vibe-projects'),get_the_title($project_id)), 'content' => sprintf(__('You were removed from the project %s by %s','vibe-projects'),get_the_title($project_id),$user->displayname),   'recipients' => $members ) );
	    }
    	
    }

    function student_notification_vibe_projects_member_removed($project_id,$member_id,$user){
    	if(bp_is_active('notifications')){
    		
			vibe_projects_add_notification( array(
				'user_id'          => $member_id,
				'item_id'          => $project_id,
				'secondary_item_id'=>$user->id,
				'component_action' => 'vibe_projects_member_removed'
			));
    		
    	}
    }

    function student_email_vibe_projects_member_removed($project_id,$member_id,$user){
    	
    	
    	$enable = get_user_meta($member_id,'vibe_projects_member_removed',true);
		if($enable !== 'no'){
			$member = get_user_by( 'id', $member_id);
			if(!empty($member) && !is_wp_error($member)){
				$to[] = $member->user_email;
			}
		}
		
		if(!empty($to)){
			
			bp_send_email('vibe_projects_member_removed',$to,[
				'tokens'=>[
					'user.name'=>$user->displayname,
					'item.title'=>get_the_title($project_id)
			]]);
		}
		
    }

    /* == MEMBER ADDED TO PROJECT == */

    function student_message_vibe_projects_member_added($project_id,$member_id,$user){

	    if(bp_is_active('messages')){
	    	
	      	vibe_projects_messages_new_message( array('sender_id' => $user->id, 'subject' => sprintf(__('You are added to the project %s ','vibe-projects'),get_the_title($project_id)), 'content' => sprintf(__('You were added to the project %s by %s','vibe-projects'),get_the_title($project_id),$user->displayname),   'recipients' => $members ) );
	    }
    	
    }

    function student_notification_vibe_projects_member_added($project_id,$member_id,$user){
    	if(bp_is_active('notifications')){
    		
			vibe_projects_add_notification( array(
				'user_id'          => $member_id,
				'item_id'          => $project_id,
				'secondary_item_id'=>$user->id,
				'component_action' => 'vibe_projects_member_added'
			));
    		
    	}
    }

    function student_email_vibe_projects_member_added($project_id,$member_id,$user){
    	
    	
    	$enable = get_user_meta($member_id,'vibe_projects_member_added',true);
		if($enable !== 'no'){
			$member = get_user_by( 'id', $member_id);
			if(!empty($member) && !is_wp_error($member)){
				$to[] = $member->user_email;
			}
		}
		
		if(!empty($to)){
			
			bp_send_email('vibe_projects_member_added',$to,[
				'tokens'=>[
					'user.name'=>$user->displayname,
					'item.title'=>get_the_title($project_id)
			]]);
		}
		
    }
    /* == PROJECT UPDATED == */
    
    function student_message_vibe_projects_create_new_project($project_id,$args,$user){

	    if(bp_is_active('messages')){
	    	$members = vibe_projects_get_project_members($project_id);
	    	$i = array_search($user->id, $members);
	    	if($i >= 0){unset($members[$i]);}
	      	vibe_projects_messages_new_message( array('sender_id' => $user->id, 'subject' => sprintf(__('Project %s updated','vibe-projects'),get_the_title($project_id)), 'content' => sprintf(__('Project %s updated by %s','vibe-projects'),get_the_title($project_id),$user->displayname),   'recipients' => $members ) );
	    }
    	
    }

    function student_notification_vibe_projects_create_new_project($project_id,$args,$user){
    	if(bp_is_active('notifications')){
    		$members = vibe_projects_get_project_members($project_id);
    		foreach ($members as $key => $member) {
    			if($member != $user->id){
	    			vibe_projects_add_notification( array(
						'user_id'          => $member,
						'item_id'          => $project_id,
						'secondary_item_id'=>$user->id,
						'component_action' => 'vibe_projects_create_new_project'
					));
				}
    		}
	    	
    	}
    }

    function student_email_vibe_projects_create_new_project($project_id,$args,$user){
    	
    	$members = vibe_projects_get_project_members($project_id);
    	if(!empty($members)){
    		$to=[];
    		foreach ($members as $key => $member) {
    			if($member != $user->id){
			    	$enable = get_user_meta($member,'vibe_projects_create_new_project',true);
					if($enable !== 'no'){
						$member = get_user_by( 'id', $member);
						if(!empty($member) && !is_wp_error($member)){
							$to[] = $member->user_email;
						}
					}
				}
			}
			if(!empty($to)){
				
				bp_send_email('vibe_projects_create_new_project',$to,[
					'tokens'=>[
						'user.name'=>$user->displayname,
						'item.title'=>get_the_title($project_id)
				]]);
			}
		}
    }

   	/* == PROJECT NOTICE ADDED == */

   	function get_notice_members($project_id,$notice){
   		if(!empty($this->notice_members)){
   			return $this->notice_members;
   		}
   		$members = vibe_projects_get_project_members($project_id);
    	foreach($members as $i=>$member){
    		$type = bp_get_member_type($member);	
    		if(!empty($notice['member_type']) && $notice['member_type'] != 'all' && $notice['member_type'] != $type){
    			unset($members[$i]);
    		}
    	}

    	return $members;
   	}
   	function student_message_vibe_projects_notice_added($project_id,$notice){

	    if(bp_is_active('messages') && !empty($notice['notify'])){
	    	
	    	$watchers = $this->get_notice_members($project_id,$notice);
	      vibe_projects_messages_new_message( array('sender_id' => $notice['user_id'], 'subject' => sprintf(__('Notice added in project %s','vibe-projects'),get_the_title($project_id)), 'content' => $notice['content'],   'recipients' => $watchers ) );
	    }
    	
    }

    function student_notification_vibe_projects_notice_added($project_id,$notice){
    	if(bp_is_active('notifications') && !empty($notice['notify'])){
    		$watchers = $this->get_notice_members($project_id,$notice);
    		foreach ($watchers as $key => $watcher) {
    			vibe_projects_add_notification( array(
					'user_id'          => $watcher,
					'item_id'          => $project_id,
					'component_action' => 'vibe_projects_notice_added'
				));
    		}
	    	
    	}
    }

    function student_email_vibe_projects_notice_added($project_id,$notice){
    	
    	if(!empty($notice['notify'])){
    		$to=[];
    		$watchers = $this->get_notice_members($project_id,$notice);
    		foreach ($watchers as $key => $watcher) {
		    	$enable = get_user_meta($watcher,'vibe_projects_notice_added',true);
				if($enable !== 'no'){
					$user = get_user_by( 'id', $watcher);
					if(!empty($user) && !is_wp_error($user)){
						$to[] = $user->user_email;
					}
				}
			}
			if(!empty($to)){

				bp_send_email('vibe_projects_notice_added',$to,[
					'tokens'=>[
						'item.title'=>get_the_title($project_id),
						'item.notice'=>$notice['content'],
				]]);
			}
		}
    }

    /* == CARD LABEL ADDED == */
    function student_message_vibe_projects_card_label_added($card_id,$user_id,$post){
    	$watchers = $this->get_card_watchers($card_id);

    	if(!empty($watchers) ){
    		$label_name = '';
    		$card_board= $this->get_card_board($card_id);
    		if(!empty($card_board) && !empty($post['label'])){
    			$alllabels = $this->get_board_labels($card_board);

    			$label = $this->get_label_from_labels($post['label'],$alllabels);

    			if(!empty($label) && !empty($label['label'])){
    				$label_name = $label['label'];
    			}
    		}
    		$message = sprintf(__('Label %s added by %s in card %s','vibe-projects'),$label_name,bp_core_get_userlink($user_id),$this->get_card_title_link($card_id));
		    if(bp_is_active('messages') )
		      vibe_projects_messages_new_message( array('sender_id' => $user_id, 'subject' => __('Card label added','vibe-projects'), 'content' => $message,   'recipients' => $watchers ) );
    	}
    }

    function student_notification_vibe_projects_card_label_added($card_id,$user_id,$post){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		foreach ($watchers as $key => $watcher) {
    			vibe_projects_add_notification( array(
					'user_id'          => $watcher,
					'item_id'          => $card_id,
					'secondary_item_id' => $user_id,
					'component_action' => 'vibe_projects_card_label_added'
				));
    		}
	    	
    	}
    }
    function student_email_vibe_projects_card_label_added($card_id,$user_id,$post){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		$to=[];
    		foreach ($watchers as $key => $watcher) {
		    	$enable = get_user_meta($watcher,'vibe_projects_card_label_added',true);
				if($enable !== 'no'){
					$user = get_user_by( 'id', $watcher);
					if(!empty($user) && !is_wp_error($user)){
						$to[] = $user->user_email;
					}
				}
			}
			if(!empty($to)){
				$label_name = '';
	    		$card_board= $this->get_card_board($card_id);
	    		if(!empty($card_board) && !empty($post['label'])){
	    			$alllabels = $this->get_board_labels($card_board);
	    			$label = $this->get_label_from_labels($post['label'],$alllabels);
	    			if(!empty($label) && !empty($label['label'])){
	    				$label_name = $label['label'];
	    			}
	    		}

				bp_send_email('vibe_projects_card_label_added',$to,[
					'tokens'=>[
						'label.name'=>$label_name,
						'user.name'=>bp_core_get_user_displayname($user_id),
						'card.title'=>get_the_title($card_id),
						'user.link'=>bp_core_get_userlink($user_id),
						'card.link'=>$this->get_card_title_link($card_id),
				]]);
			}
		}
    }

    function student_message_vibe_projects_card_label_removed($card_id,$user_id,$post){
    	$watchers = $this->get_card_watchers($card_id);

    	if(!empty($watchers) ){
    		$label_name = '';
    		$card_board= $this->get_card_board($card_id);
    		if(!empty($card_board) && !empty($post['label'])){
    			$alllabels = $this->get_board_labels($card_board);
    			$label = $this->get_label_from_labels($post['label'],$alllabels);
    			if(!empty($label) && !empty($label['label'])){
    				$label_name = $label['label'];
    			}
    		}
    		get_post_meta( intval($post['board_id']), 'vibe_board_labels', true );
    		$message = sprintf(__('Label %s removed by %s from card %s','vibe-projects'),$label_name,bp_core_get_userlink($user_id),$this->get_card_title_link($card_id));
		    if(bp_is_active('messages') )
		      vibe_projects_messages_new_message( array('sender_id' => $user_id, 'subject' => __('Card label removed','vibe-projects'), 'content' => $message,   'recipients' => $watchers ) );
    	}
    }
    function student_notification_vibe_projects_card_label_removed($card_id,$user_id,$post){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		foreach ($watchers as $key => $watcher) {
    			vibe_projects_add_notification( array(
					'user_id'          => $watcher,
					'item_id'          => $card_id,
					'secondary_item_id' => $user_id,
					'component_action' => 'vibe_projects_card_label_removed'
				));
    		}
	    	
    	}
    }
    function student_email_vibe_projects_card_label_removed($card_id,$user_id,$post){
    	
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		$to=[];
    		foreach ($watchers as $key => $watcher) {
		    	$enable = get_user_meta($watcher,'vibe_projects_card_label_removed',true);
				if($enable !== 'no'){
					$user = get_user_by( 'id', $watcher);
					if(!empty($user) && !is_wp_error($user)){
						$to[] = $user->user_email;
					}
				}
			}
			if(!empty($to)){
				$label_name = '';
	    		$card_board= $this->get_card_board($card_id);
	    		if(!empty($card_board) && !empty($post['label'])){
	    			$alllabels = $this->get_board_labels($card_board);
	    			$label = $this->get_label_from_labels($post['label'],$alllabels);
	    			if(!empty($label) && !empty($label['label'])){
	    				$label_name = $label['label'];
	    			}
	    		}
	    		bp_send_email('vibe_projects_card_label_removed',$to,[
					'tokens'=>[
						'label.name'=>$label_name,
						'user.name'=>bp_core_get_user_displayname($user_id),
						'card.title'=>get_the_title($card_id),
						'user.link'=>bp_core_get_userlink($user_id),
						'card.link'=>$this->get_card_title_link($card_id),
				]]);
			}
		}
    }

    function student_message_vibe_projects_update_checklist($card_id,$user_id,$args){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		$message = sprintf(__('Card %s checklist updated by %s','vibe-projects'),$this->get_card_title_link($card_id),bp_core_get_userlink($user_id));
		    if(bp_is_active('messages') )
		      vibe_projects_messages_new_message( array('sender_id' => $user_id, 'subject' => __('Card checklist updated','vibe-projects'), 'content' => $message,   'recipients' => $watchers ) );
    	}
    }
    function student_notification_vibe_projects_update_checklist($card_id,$user_id,$args){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		foreach ($watchers as $key => $watcher) {
    			vibe_projects_add_notification( array(
					'user_id'          => $watcher,
					'item_id'          => $card_id,
					'secondary_item_id' => $user_id,
					'component_action' => 'vibe_projects_update_checklist'
				));
    		}
	    	
    	}
    }
    function student_email_vibe_projects_update_checklist($card_id,$user_id,$args){
    	
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		$to=[];
    		foreach ($watchers as $key => $watcher) {
		    	$enable = get_user_meta($watcher,'vibe_projects_update_checklist',true);
				if($enable !== 'no'){
					$user = get_user_by( 'id', $watcher);
					if(!empty($user) && !is_wp_error($user)){
						$to[] = $user->user_email;
					}
				}
			}
			if(!empty($to)){
				
				bp_send_email('vibe_projects_update_checklist',$to,[
					'tokens'=>[
						'user.name'=>bp_core_get_user_displayname($user_id),
						'card.title'=>get_the_title($card_id),
						'user.link'=>bp_core_get_userlink($user_id),
						'card.link'=>$this->get_card_title_link($card_id),
				]]);
			}
		}
    }

    function student_message_vibe_projects_card_duedate_set($card_id,$user_id,$args){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		$message = sprintf(__('Due date %s set in card %s by %s','vibe-projects'),date_i18n(get_option('date_format'), $args['timestamp'],true),$this->get_card_title_link($card_id),bp_core_get_userlink($user_id));
		    if(bp_is_active('messages') )
		      vibe_projects_messages_new_message( array('sender_id' => $user_id, 'subject' => __('Card due date set','vibe-projects'), 'content' => $message,   'recipients' => $watchers ) );
    	}
    }
    function student_notification_vibe_projects_card_duedate_set($card_id,$user_id,$args){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		foreach ($watchers as $key => $watcher) {
    			vibe_projects_add_notification( array(
					'user_id'          => $watcher,
					'item_id'          => $card_id,
					'secondary_item_id' => $user_id,
					'component_action' => 'vibe_projects_card_duedate_set'
				));
    		}
	    	
    	}
    }
    function student_email_vibe_projects_card_duedate_set($card_id,$user_id,$args){
    	
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		$to=[];
    		foreach ($watchers as $key => $watcher) {
		    	$enable = get_user_meta($watcher,'vibe_projects_card_duedate_set',true);
				if($enable !== 'no'){
					$user = get_user_by( 'id', $watcher);
					if(!empty($user) && !is_wp_error($user)){
						$to[] = $user->user_email;
					}
				}
			}
			if(!empty($to)){
				bp_send_email('vibe_projects_card_duedate_set',$to,[
					'tokens'=>[
						'user.name'=>bp_core_get_user_displayname($user_id),
						'card.title'=>get_the_title($card_id),
						'user.link'=>bp_core_get_userlink($user_id),
						'card.link'=>$this->get_card_title_link($card_id),
						'due.date'=>date_i18n(get_option('date_format'), $args['timestamp'],true),
				]]);

			}
		}
    }
    

    function student_message_vibe_projects_upload_attachment($card_id,$user_id,$args,$attachment){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		$att = '<a href="'.$attachment['value'].'" target="_blank">'.$attachment['name'].'</a>';
    		$message = sprintf(__('Attachment %s uploaded in card %s by %s','vibe-projects'),$att,$this->get_card_title_link($card_id),bp_core_get_userlink($user_id));
		    if(bp_is_active('messages') )
		      vibe_projects_messages_new_message( array('sender_id' => $user_id, 'subject' => __('Attachment uploaded in card','vibe-projects'), 'content' => $message,   'recipients' => $watchers ) );
    	}
    }
    function student_notification_vibe_projects_upload_attachment($card_id,$user_id,$args,$attachment){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		foreach ($watchers as $key => $watcher) {
    			vibe_projects_add_notification( array(
					'user_id'          => $watcher,
					'item_id'          => $card_id,
					'secondary_item_id' => $user_id,
					'component_action' => 'vibe_projects_upload_attachment'
				));
    		}
	    	
    	}
    }
    function student_email_vibe_projects_upload_attachment($card_id,$user_id,$args,$attachment){
    	
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		$to=[];
    		foreach ($watchers as $key => $watcher) {
		    	$enable = get_user_meta($watcher,'vibe_projects_upload_attachment',true);
				if($enable !== 'no'){
					$user = get_user_by( 'id', $watcher);
					if(!empty($user) && !is_wp_error($user)){
						$to[] = $user->user_email;
					}
				}
			}
			if(!empty($to)){
				$att = '<a href="'.$attachment['value'].'" target="_blank">'.$attachment['name'].'</a>';
				

				bp_send_email('vibe_projects_upload_attachment',$to,[
					'tokens'=>[
						'user.name'=>bp_core_get_user_displayname($user_id),
						'card.title'=>get_the_title($card_id),
						'user.link'=>bp_core_get_userlink($user_id),
						'card.link'=>$this->get_card_title_link($card_id),
						'attachment.link'=>$att,
				]]);
			}
		}
    }

    function student_message_vibe_projects_card_completed($card_id,$user_id,$args){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		$message = sprintf(__('Card %s marked completed by %s','vibe-projects'),$this->get_card_title_link($card_id),bp_core_get_userlink($user_id));
		    if(bp_is_active('messages') )
		      vibe_projects_messages_new_message( array('sender_id' => $user_id, 'subject' => __('Card marked completed','vibe-projects'), 'content' => $message,   'recipients' => $watchers ) );
    	}
    }
    function student_notification_vibe_projects_card_completed($card_id,$user_id,$args){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		foreach ($watchers as $key => $watcher) {
    			vibe_projects_add_notification( array(
					'user_id'          => $watcher,
					'item_id'          => $card_id,
					'secondary_item_id' => $user_id,
					'component_action' => 'vibe_projects_card_completed'
				));
    		}
	    	
    	}
    }
    function student_email_vibe_projects_card_completed($card_id,$user_id,$args){
    	
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		$to=[];
    		foreach ($watchers as $key => $watcher) {
		    	$enable = get_user_meta($watcher,'vibe_projects_card_completed',true);
				if($enable !== 'no'){
					$user = get_user_by( 'id', $watcher);
					if(!empty($user) && !is_wp_error($user)){
						$to[] = $user->user_email;
					}
				}
			}
			if(!empty($to)){
				bp_send_email('vibe_projects_card_completed',$to,[
					'tokens'=>[
						'user.name'=>bp_core_get_user_displayname($user_id),
						'card.title'=>get_the_title($card_id),
						'user.link'=>bp_core_get_userlink($user_id),
						'card.link'=>$this->get_card_title_link($card_id),
				]]);
			}
		}
    }


    function student_message_vibe_projects_card_milestoned($card_id,$user_id,$args){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		$message = sprintf(__('Card %s marked as milestone by %s','vibe-projects'),$this->get_card_title_link($card_id),bp_core_get_userlink($user_id));
		    if(bp_is_active('messages') )
		      vibe_projects_messages_new_message( array('sender_id' => $user_id, 'subject' => __('Card marked as milestone','vibe-projects'), 'content' => $message,   'recipients' => $watchers ) );
    	}
    }
    function student_notification_vibe_projects_card_milestoned($card_id,$user_id,$args){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		foreach ($watchers as $key => $watcher) {
    			vibe_projects_add_notification( array(
					'user_id'          => $watcher,
					'item_id'          => $card_id,
					'secondary_item_id' => $user_id,
					'component_action' => 'vibe_projects_card_milestoned'
				));
    		}
	    	
    	}
    }
    function student_email_vibe_projects_card_milestoned($card_id,$user_id,$args){
    	
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		$to=[];
    		foreach ($watchers as $key => $watcher) {
		    	$enable = get_user_meta($watcher,'vibe_projects_card_milestoned',true);
				if($enable !== 'no'){
					$user = get_user_by( 'id', $watcher);
					if(!empty($user) && !is_wp_error($user)){
						$to[] = $user->user_email;
					}
				}
			}
			if(!empty($to)){
				
				bp_send_email('vibe_projects_card_milestoned',$to,[
					'tokens'=>[
						'user.name'=>bp_core_get_user_displayname($user_id),
						'card.title'=>get_the_title($card_id),
						'user.link'=>bp_core_get_userlink($user_id),
						'card.link'=>$this->get_card_title_link($card_id)
				]]);
			}
		}
    }

    function student_message_vibe_projects_card_archived($card_id,$user_id,$args){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		$message = sprintf(__('Card %s archived by %s','vibe-projects'),$this->get_card_title_link($card_id),bp_core_get_userlink($user_id));
		    if(bp_is_active('messages') )
		      vibe_projects_messages_new_message( array('sender_id' => $user_id, 'subject' => __('Card archived','vibe-projects'), 'content' => $message,   'recipients' => $watchers ) );
    	}
    }
    function student_notification_vibe_projects_card_archived($card_id,$user_id,$args){
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		foreach ($watchers as $key => $watcher) {
    			vibe_projects_add_notification( array(
					'user_id'          => $watcher,
					'item_id'          => $card_id,
					'secondary_item_id' => $user_id,
					'component_action' => 'vibe_projects_card_archived'
				));
    		}
	    	
    	}
    }
    function student_email_vibe_projects_card_archived($card_id,$user_id,$args){
    	
    	$watchers = $this->get_card_watchers($card_id);
    	if(!empty($watchers)){
    		$to=[];
    		foreach ($watchers as $key => $watcher) {
		    	$enable = get_user_meta($watcher,'vibe_projects_card_archived',true);
				if($enable !== 'no'){
					$user = get_user_by( 'id', $watcher);
					if(!empty($user) && !is_wp_error($user)){
						$to[] = $user->user_email;
					}
				}
			}
			if(!empty($to)){
				bp_send_email('vibe_projects_card_archived',$to,[
					'tokens'=>[
						'user.name'=>bp_core_get_user_displayname($user_id),
						'card.title'=>get_the_title($card_id),
						'user.link'=>bp_core_get_userlink($user_id),
						'card.link'=>$this->get_card_title_link($card_id),
				]]);
			}
		}
    }

    /* == New Card in Project== */

    function notify_members_on_card_creation($return){
    	$project_id = $return['project'];
    	$board_id = $return['board'];
    	if(!empty($this->member_id)){
    		return $this->member_id;
    	}
    	$this->member_id = get_post_field('post_author',$board_id,'raw');
    	return [$this->member_id];
    }
    function student_email_vibe_projects_create_new_card($return,$args,$user_id){
    	
    	$members = $this->notify_members_on_card_creation($return);
    	if(!empty($members)){
    		foreach($members as $member_id){
    			$to=[];
		    	$enable = get_user_meta($member_id,'vibe_projects_create_new_card',true);
				if($enable !== 'no'){
					$user = get_user_by( 'id', $member_id);
					if(!empty($user) && !is_wp_error($user)){
						$to[] = $user->user_email;
					}
				}
				
				if(!empty($to)){
					bp_send_email('vibe_projects_create_new_card',$to,[
						'tokens'=>[
							'user.name'=>bp_core_get_user_displayname($user_id),
							'card.title'=>get_the_title($card_id),
							'item.title'=>get_the_title($project_id),
					]]);
				}
    		}
    	}
    }

    function student_message_vibe_projects_create_new_card($return,$args,$user_id){
    	$members = $this->notify_members_on_card_creation($return);
		$message = sprintf(__('New card %s added in board %s in project %s','vibe-projects'),get_the_title($return['card_id']),get_the_title($return['board']),get_the_title($return['project']));
	    if(bp_is_active('messages') )
	      vibe_projects_messages_new_message( array('sender_id' => $user_id, 'subject' => __('New card added','vibe-projects'), 'content' => $message,   'recipients' => [$members] ) );
    }

    function student_notification_vibe_projects_create_new_card($return,$args,$user_id){
    	$members = $this->notify_members_on_card_creation($return);
    	if(!empty($members)){
    		foreach($members as $member_id){
    			vibe_projects_add_notification( array(
					'user_id'          => $member_id,
					'item_id'          => $return['project'],
					'secondary_item_id' => $return['card_id'],
					'component_action' => 'vibe_projects_create_new_card'
				));
    		}	
    	}
    }
    

    /* == Member Added to Card == */

    function student_email_vibe_projects_add_member_to_card($card_id,$member_id,$project_id,$user_id){
    	
		$to=[];
    	$enable = get_user_meta($member_id,'vibe_projects_add_member_to_card',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $member_id);
			if(!empty($user) && !is_wp_error($user)){
				$to[] = $user->user_email;
			}
		}
		
		if(!empty($to)){
			bp_send_email('vibe_projects_add_member_to_card',$to,[
				'tokens'=>[
					'user.name'=>bp_core_get_user_displayname($user_id),
					'card.title'=>get_the_title($card_id),
					'item.title'=>get_the_title($project_id),
			]]);
		}
		
    }

    function student_message_vibe_projects_add_member_to_card($card_id,$member_id,$project_id,$user_id){
		$message = sprintf(__('Card %s assigned under project %s by %s : %s','vibe-projects'),$this->get_card_title_link($card_id),get_the_title($project_id),bp_core_get_userlink($user_id));
	    if(bp_is_active('messages') )
	      vibe_projects_messages_new_message( array('sender_id' => $user_id, 'subject' => __('Card Assigned','vibe-projects'), 'content' => $message,   'recipients' => [$member_id] ) );
    }

    function student_notification_vibe_projects_add_member_to_card($card_id,$member_id,$project_id,$user_id){
		vibe_projects_add_notification( array(
			'user_id'          => $member_id,
			'item_id'          => $project_id,
			'secondary_item_id' => $card_id,
			'component_action' => 'vibe_projects_add_member_to_card'
		));
    		
    }
    /* Member Removed from Card == */

    function student_email_vibe_projects_remove_member_from_card($card_id,$member_id,$project_id,$user_id){
    	
		$to=[];
    	$enable = get_user_meta($member_id,'vibe_projects_remove_member_from_card',true);
		if($enable !== 'no'){
			$user = get_user_by( 'id', $member_id);
			if(!empty($user) && !is_wp_error($user)){
				$to[] = $user->user_email;
			}
		}
		
		if(!empty($to)){
			bp_send_email('vibe_projects_remove_member_from_card',$to,[
				'tokens'=>[
					'user.name'=>bp_core_get_user_displayname($user_id),
					'card.title'=>get_the_title($card_id),
					'item.title'=>get_the_title($project_id),
			]]);
		}
		
    }

    function student_message_vibe_projects_remove_member_from_card($card_id,$member_id,$project_id,$user_id){
		$message = sprintf(__('Card %s assigned under project %s by %s : %s','vibe-projects'),$this->get_card_title_link($card_id),bp_core_get_userlink($user_id),$comment['comment_content']);
	    if(bp_is_active('messages') )
	      vibe_projects_messages_new_message( array('sender_id' => $user_id, 'subject' => __('Card Assigned','vibe-projects'), 'content' => $message,   'recipients' => [$member_id] ) );
    }

    function student_notification_vibe_projects_remove_member_from_card($card_id,$member_id,$project_id,$user_id){
		vibe_projects_add_notification( array(
			'user_id'          => $member_id,
			'item_id'          => $project_id,
			'secondary_item_id' => $card_id,
			'component_action' => 'vibe_projects_remove_member_from_card'
		));
    		
    }

    /* Card status updated == */
    
	function student_email_vibe_projects_card_status_updated($card_id,$project_id,$status,$auser){
    	
    	$members = vibe_projects_get_card_members($card_id);
    	$watchers =  $this->get_card_watchers($card_id);
    	$members = array_merge($members,$watchers);

		$to=[];
		if(!empty($members)){
			$statuses = vibe_projects_get_statuses('card');
			foreach($members as $member_id){
				$enable = get_user_meta($member_id,'vibe_projects_card_status_updated',true);
				if($enable !== 'no'){
					$user = get_user_by( 'id', $member_id);
					if(!empty($user) && !is_wp_error($user)){
						$to[] = $user->user_email;
					}
				}
				
				if(!empty($to)){
					bp_send_email('vibe_projects_card_status_updated',$to,[
						'tokens'=>[
							'card.title'=>get_the_title($card_id),
							'item.title'=>get_the_title($project_id),
							'status'=> (!empty($statuses) && empty($statuses[$status]))?$statuses[$status]:$status
					]]);
				}
			}
		}
    	
		
    }

    function student_message_vibe_projects_card_status_updated($card_id,$project_id,$status,$user){

    	$members = vibe_projects_get_card_members($card_id);
    	$watchers =  $this->get_card_watchers($card_id);
    	$members = array_merge($members,$watchers);

		
		if(!empty($members)){
			$statuses = vibe_projects_get_statuses('card');
			
			$status =(!empty($statuses) && empty($statuses[$status]))?$statuses[$status]:$status;

			$message = sprintf(__('Card %s status updated to %s under project %s by %s : %s','vibe-projects'),$this->get_card_title_link($card_id),$status,get_the_title($project_id),$user->displayname);
		    if(bp_is_active('messages') ){
		      vibe_projects_messages_new_message( array('sender_id' => $user->id, 'subject' => sprintf(__('Card %sstatus updaed','vibe-projects'),get_the_title($card_id)), 'content' => $message,   'recipients' => $members ) );
		    }
		}
    }

    function student_notification_vibe_projects_card_status_updated($card_id,$member_id,$project_id,$user_id){
    	$members = vibe_projects_get_card_members($card_id);
    	$watchers =  $this->get_card_watchers($card_id);
    	$members = array_merge($members,$watchers);
    	if(!empty($members)){
    		foreach($members as $member_id){
    			vibe_projects_add_notification( array(
					'user_id'          => $member_id,
					'item_id'          => $project_id,
					'secondary_item_id' => $card_id,
					'component_action' => 'vibe_projects_card_status_updated'
				));
    		}
		}
    		
    }
    /* == Card Functions == */

    function get_card_watchers($card_id){
    	if(empty($this->card_watchers)){
    		$this->card_watchers = [];
    	}
    	if(isset($this->card_watchers[$card_id])){
    		return $this->card_watchers[$card_id];
    	}
    	$watchers = get_post_meta($card_id,'watch_card',false);
    	if(empty($watchers)){
    		$watchers = [];
    	}
    	
    	$this->card_watchers[$card_id] = $watchers;
    	return $this->card_watchers[$card_id];
    }

    function get_card_title_link($card_id){
    	if(empty($this->card_title_link)){
    		$this->card_title_link = [];
    	}
    	if(isset($this->card_title_link[$card_id])){
    		return $this->card_title_link[$card_id];
    	}

    	$this->card_title_link[$card_id] = '<a href="'.get_post_meta($card_id,'card_share_link',true).'">'.get_the_title($card_id).'</a>';

    	return $this->card_title_link[$card_id];
    }
    function get_card_board($card_id){
    	if(empty($this->card_board)){
    		$this->card_board = [];
    	}
    	if(isset($this->card_board[$card_id])){
    		return $this->card_board[$card_id];
    	}
    	$card_board = get_post_meta($card_id,'vibe_card_board',true);
    	if(empty($card_board)){
    		$card_board = [];
    	}
    	
    	$this->card_board[$card_id] = $card_board;
    	return $this->card_board[$card_id];
    }
    function get_board_labels($board_id){
    	if(empty($this->board_labels)){
    		$this->board_labels=[];
    	}
    	if(isset($this->board_labels[$board_id])){
    		return $this->board_labels[$board_id];
    	}
    	
    	$this->board_labels[$board_id] = get_post_meta($board_id,'vibe_board_labels',true);
    	
    	return $this->board_labels[$board_id];
    }
    function get_label_from_labels($id,$labels){
    	if(!empty($labels)){
    		foreach ($labels as $key => $label) {
    			if($label['id']==$id){
    				return $label;
    			}
    		}
    	}
    	return false;
    }
}


add_action('bp_init',function(){

	$vibe_tc = Vibe_Projects_TouchPoints::init();
});