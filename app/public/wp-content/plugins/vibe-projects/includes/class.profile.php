<?php
/**
 * PRofile
 *
 * @class       Vibe_Projects_Profile
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



class Vibe_Projects_Profile{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Projects_Profile();
        return self::$instance;
    }

	private function __construct(){
		add_action( 'bp_setup_nav', array($this,'add_projects_tab'), 100 );
		add_action('wp_enqueue_scripts',array($this,'enqueue_script'));
	}


	function add_projects_tab(){
		global $bp;
		$link = trailingslashit( bp_loggedin_user_domain());
	    bp_core_new_nav_item( array( 
	        'name' => __('Projects','vibe-projects'),
	        'slug' => VIBE_PROJECTS_SLUG, 
	        'item_css_id' => VIBE_PROJECTS_SLUG,
	        'screen_function' => array($this,'show_myprojects'),
	        'default_subnav_slug' => 'projects', 
	        'position' => 20
	    ) );

	    //Add My Projects In Projects Tab
	    
		 bp_core_new_subnav_item( array(
			'name' 		  => __( 'My Projects', 'vibe-projects' ),
			'slug' 		  => 'projects',
			'parent_slug' => VIBE_PROJECTS_SLUG,
        	'parent_url' => $link.VIBE_PROJECTS_SLUG.'/',
			'screen_function' => array($this,'show_myprojects')
		) );

		bp_core_new_subnav_item( array(
			'name' 		  => __( 'My Tasks', 'vibe-projects' ),
			'slug' 		  => 'tasks',
			'parent_slug' => VIBE_PROJECTS_SLUG,
        	'parent_url' => $link.VIBE_PROJECTS_SLUG.'/',
			'screen_function' => array($this,'show_myprojects')
		) );

		bp_core_new_subnav_item( array(
			'name' 		  => __( 'My Calendar', 'vibe-projects' ),
			'slug' 		  => 'calendar',
			'parent_slug' => VIBE_PROJECTS_SLUG,
        	'parent_url' => $link.VIBE_PROJECTS_SLUG.'/',
			'screen_function' => array($this,'show_myprojects')
		) );

		bp_core_new_subnav_item( array(
			'name' 		  => __( 'My Reports', 'vibe-projects' ),
			'slug' 		  => 'reports',
			'parent_slug' => VIBE_PROJECTS_SLUG,
        	'parent_url' => $link.VIBE_PROJECTS_SLUG.'/',
			'screen_function' => array($this,'show_myprojects')
		) );

		//Add Favourites In Projects Tab
		/*
	    bp_core_new_subnav_item( array(
			'name' 		  => __( 'My Favourites', 'vibe-projects' ),
			'slug' 		  => 'my-favourites',
			'parent_slug' => VIBE_PROJECTS_SLUG,
        	'parent_url' => $bp->displayed_user->domain.VIBE_PROJECTS_SLUG.'/',
			'screen_function' => array($this,'show_myfavourite_projects'),
			'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
		) );
		*/
	}

	function enqueue_script(){
		//bp_displayed_user_id
		if(function_exists('bp_is_user') && bp_is_user() || apply_filters('vibebp_enqueue_profile_script',false)){
			$js_vars = $this->enqueue_project_vars();
			
			wp_enqueue_script('vibe-projects',plugins_url('../assets/js/vibeprojects.js',__FILE__),array('wp-element','wp-data'),VIBEPROJECTS_VERSION,true);

			//wp_enqueue_script('vibe-projects-gannt',plugins_url('../assets/js/frappe-gantt.min.js',__FILE__),array(),VIBEPROJECTS_VERSION,true);

			wp_enqueue_script('chartjs',plugins_url('../assets/js/Chart.min.js',__FILE__),array(),VIBEPROJECTS_VERSION,true);
			wp_enqueue_script('momentjs','https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js',array('chartjs'),VIBEPROJECTS_VERSION,true);
			
			wp_enqueue_script('fullcalendar',plugins_url('../assets/js/fullcalendar.min.js',__FILE__),array(),VIBEPROJECTS_VERSION,true);
			wp_enqueue_script('fullcalendar_all_locale',plugins_url('../assets/js/locales-all.min.js',__FILE__),array('fullcalendar'),VIBEPROJECTS_VERSION,true);
			

			

            wp_enqueue_style('fullcalendar',plugins_url('../assets/css/fullcalendar.min.css',__FILE__),array(),VIBEPROJECTS_VERSION);
			
			wp_localize_script('vibe-projects','vibeprojects',$js_vars);
		}
		
		
	}

	function enqueue_project_vars(){
		$link = site_url();
        $parents = [];
        if(function_exists('vibebp_get_setting')){
            $app_page = vibebp_get_setting('bp_single_page');
            if(!empty($app_page)){
                $link = get_permalink($app_page);
            }
        }
        $link .= '#component=projects';

        
        $capabilities=[];
        $caps=[''=>'project_capability','cards'=>'card_capability','boards'=>'board_capability'];
        foreach($caps as $sub=>$cap){
        	
    		$val='';
    		if(Empty($sub)){
    			$sub='projects';
    		}
    		if(function_exists('vibebp_get_setting')){
    			$val=vibebp_get_setting($cap,'vibe_projects',$sub);
    		}

    		$caps=[];
    		if(!empty($val) && !empty($val['key'])){
    			foreach($val['key'] as $i=>$v){
    				if(!empty($val['capabilities'][$i])){
    			        $caps[$v]=$val['capabilities'][$i];    
    			    }
    			}
    		}
    		$capabilities[$cap]=$caps;
    	}
        	

        $additional_fields=[];
        $add_fields=['projects'=>'create_project_fields','cards'=>'create_card_fields'];
        foreach($add_fields as $sub=>$ps){
    		$val='';
    		if(function_exists('vibebp_get_setting')){
    			$val = vibebp_get_setting($ps,'vibe_projects',$sub);
    		}
    		$afs=[];
    		if(!empty($val) && !empty($val['key'])){
    			foreach($val['key'] as $i=>$v){
    				if($val['type'][$i] == 'select'){
    					$options=[];
    					$ops = explode('|',$val['value'][$i]);
    					if(!empty($ops)){
    						foreach ($ops as $op) {
    							$keyval = explode('=>',$op);
    							$options[]=['label'=>$keyval[1],'value'=>$keyval[0]];
    						}
    					}
    					$afs[]=['id'=>$v,'key'=>$v,'label'=>$val['label'][$i],'type'=>$val['type'][$i],'options'=> $options,'preload'=>empty($val['preload']) || empty($val['preload'][$i])?0:1];	
    				}else if($val['type'][$i] == 'checkbox'){
    					$afs[]=['id'=>$v,'key'=>$v,'label'=>$val['label'][$i],'type'=>$val['type'][$i],'value'=>$val['value'][$i],'preload'=>empty($val['preload']) || empty($val['preload'][$i])?0:1];
    				}else{
    					$afs[]=['id'=>$v,'key'=>$v,'label'=>$val['label'][$i],'type'=>$val['type'][$i],'preload'=>empty($val['preload']) || empty($val['preload'][$i])?0:1];
    				}
    				
    			}
    		}
    		$additional_fields[$ps]=$afs;
    	}


		$js_vars = apply_filters('vibe_projects_enqueue_project_scripts',array(
			'label'=>__('Projects','vibe-projects'),
			'apiUrl'=> site_url().'/wp-json/'.VIBE_PROJECTS_API_NAMESPACE,
			'app_url'=>$link,
			'settings'=>array(
				'upload_limit'=>2048*1024,
				'capabilitites'=>$capabilities,
				'additional_fields'=>$additional_fields,
				'memberTypes'=>array_values(bp_get_member_types('','objects')),
				'memberTeams'=>get_terms(['taxonomy'=>'team']),
				'statuses'=>[
					'project'=>vibe_projects_get_statuses('project'),
					'board'=>vibe_projects_get_statuses('board'),
					'card'=>vibe_projects_get_statuses('card'),
				],
				'card_tabs'=>[
					['label'=>__('Attachments','vibe-projects'),'key'=>'attachments','icon'=>'vicon vicon-clip'],
					['label'=>__('Activity','vibe-projects'),'key'=>'activity','icon'=>'vicon vicon-pulse'],
					['label'=>__('Watchers','vibe-projects'),'key'=>'watchers','icon'=>'vicon vicon-eye']
				],
				'project'=>[
					'listView'=>[
						['key'=>'project_image','label'=>__('Project image','vibe-projects'),'selected'=>1,'required'=>1],
						['key'=>'project_title','label'=>__('Project title','vibe-projects'),'selected'=>1,'required'=>1],
						['key'=>'project_type','label'=>__('Type','vibe-projects'),'selected'=>1],
						['key'=>'project_status','label'=>__('Status','vibe-projects'),'selected'=>1],
						['key'=>'project_progress','label'=>__('Progress','vibe-projects'),'selected'=>1],
						['key'=>'end_date','label'=>__('Ends','vibe-projects'),'selected'=>1],
						['key'=>'members','label'=>__('Members','vibe-projects'),'selected'=>1],
					]
				],
				'board'=>[
					'bulk_actions'=>[
						['value'=>'add_member','label'=>__('Add member to cards','vibe-projects')],
						['value'=>'remove_member','label'=>__('Remove member from cards','vibe-projects')],
						['value'=>'add_label','label'=>__('Add label to cards','vibe-projects')],
						['value'=>'from_label','label'=>__('Remove label from cards','vibe-projects')],
						['value'=>'change_status','label'=>__('Change status of cards','vibe-projects')],
						['value'=>'change_due_date','label'=>__('Change due date of cards','vibe-projects')],
						['value'=>'watch_cards','label'=>__('Assign card watchers','vibe-projects')],
						['value'=>'assign_milestone','label'=>__('Assign Milestone','vibe-projects')],
						['value'=>'remove_milestone','label'=>__('Remove Milestone','vibe-projects')],
					],
					'automations'=>[
						['label'=>__('Add label','vibe-projects'),'type'=>'add_label'],
						['label'=>__('Remove label','vibe-projects'),'type'=>'remove_label'],
						['label'=>__('Change Status','vibe-projects'),'type'=>'change_status'],
						['label'=>__('Move Card','vibe-projects'),'type'=>'move_card'],
						['label'=>__('Complete Card','vibe-projects'),'type'=>'complete_card'],
						['label'=>__('Mark as Milestone','vibe-projects'),'type'=>'mark_milestone'],
						['label'=>__('Assign Member','vibe-projects'),'type'=>'assign_member'],
						['label'=>__('Remove Member','vibe-projects'),'type'=>'remove_member'],
						['type'=>'archive_card','label'=>__('Archive Card','vibe-projects')],
					],
					'listView'=>[
						['key'=>'title','label'=>__('Card title','vibe-projects'),'selected'=>1,'required'=>1],
						['key'=>'project','label'=>__('Project','vibe-projects'),'selected'=>1],
						['key'=>'list','label'=>__('List','vibe-projects'),'selected'=>1],
						['key'=>'progress','label'=>__('Progress','vibe-projects'),'selected'=>1],
						['key'=>'date','label'=>__('Dates','vibe-projects'),'selected'=>1],
						['key'=>'status','label'=>__('Status','vibe-projects'),'selected'=>1],
						['key'=>'milestone','label'=>__('Milestone','vibe-projects'),'selected'=>1],
						['key'=>'labels','label'=>__('Labels','vibe-projects'),'selected'=>1],
						['key'=>'members','label'=>__('Members','vibe-projects'),'selected'=>1],
					]
				],
				'label_colors'=> apply_filters('vibe_projects_card_labels_colors',array(
					'#eb5a46'=> __('Red','vibe-projects'),
					'#61bd4f'=> __('Green','vibe-projects'),
					'#f2d600'=> __('Yellow','vibe-projects'),
					'#ff9f1a'=> __('Orange','vibe-projects'),
					'#00c2e0'=> __('Blue','vibe-projects'),
					'#ff78cb'=> __('Pink','vibe-projects'),
					'#344563'=> __('black','vibe-projects'),
					'#c1fcd3'=> __('Light Green','vibe-projects'),
					'#f9dbd5'=> __('Light Pink','vibe-projects'),
					'#faf5ae'=> __('Light Yellow','vibe-projects'),
					'#ffdaf3'=> __('Light purple','vibe-projects'),
					'#c1e5fc'=> __('Light blue','vibe-projects'),
					'#f2f5d0'=> __('Musk','vibe-projects'),
					'#f5d0dc'=> __('Shade Pink','vibe-projects'),
					'#9cf7b8'=> __('Bright','vibe-projects'),
					'#f6c9c0'=> __('Shade Red','vibe-projects'),
					'#f3ed98'=> __('Autumn','vibe-projects'),
					'#f9cceb'=> __('Dark Pink','vibe-projects'),
					'#edf2b5'=> __('Shade Yellow','vibe-projects'),
					'#b0ddf9'=> __('Dark Blue','vibe-projects')
				)),
				'project_reports'=>[
					['value'=>'overview','label'=>__('Overview','vibe-projects')],
					['value'=>'tasks','label'=>__('Tasks','vibe-projects')],
					['value'=>'members','label'=>__('Members','vibe-projects')],
				],
				'checklist_item_fields'=>[
					['key'=>'duedate','label'=>__('Due Date','vibe-projects'),'type'=>'date']
				]
			),



			'sorters'=>[
				'members'=>array(
					'active'=>__('Active','vibe-projects'),
					'newest'=>__('Newly added','vibe-projects'),
					'alphabetical'=>__('Alphabetical','vibe-projects'),
				),
				'activity'=>vibe_projects_registered_activities()
			],
			'time_labels' => array(
                'year' => array('single'=>_x('year','time_labels','wplms'),'multi'=>_x('years','time_labels','wplms'),'symbol'=>_x('Y','time_labels','wplms')),
                
                'month' => array('single'=>_x('month','time_labels','wplms'),'multi'=>_x('months','time_labels','wplms'),'symbol'=>_x('M','time_labels','wplms')),
                'week' => array('single'=>_x('week','time_labels','wplms'),'multi'=>_x('weeks','time_labels','wplms'),'symbol'=>_x('W','time_labels','wplms')),
                'day' => array('single'=>_x('day','time_labels','wplms'),'multi'=>_x('days','time_labels','wplms'),'symbol'=>_x('d','time_labels','wplms')),
                'hour' => array('single'=>_x('hour','time_labels','wplms'),'multi'=>_x('hours','time_labels','wplms'),'symbol'=>_x('h','time_labels','wplms')),
                'minute' => array('single'=>_x('minute','time_labels','wplms'),'multi'=>_x('minutes','time_labels','wplms'),'symbol'=>_x('m','time_labels','wplms')),
                'second' => array('single'=>_x('second','time_labels','wplms'),'multi'=>_x('seconds','time_labels','wplms'),'symbol'=>_x('s','time_labels','wplms')),
            ),
			'translations'=>array(
				'no_project'=>_x('No Project','checkbox response','vibe-projects'),
				'on'=>_x('On','checkbox response','vibe-projects'),
				'back_to_all_projects'=>_x('Back to all projects','back notice','vibe-projects'),
				'notices'=>_x('Notices','project notice','vibe-projects'),
				'add_notice'=>_x('Add Notice','project notice','vibe-projects'),
				'just_now'=>__('Just now','vibe-projects'),
				'select_status'=>_x('Select Status','project status','vibe-projects'),
				'no_projects_found'=>_x('No projects found','','vibe-projects'),
				'favourite_projects' => _x('Favourites','','vibe-projects'),
				'yes' => _x('Yes','','vibe-projects'),
				'you_sure_delete_the_board'=>_x('Are you sure you want to delete the board?','','vibe-projects'),
				'no_labels_found'=>_x('No labels found!','','vibe-projects'),
				'copy_link'=>_x('Copy link','','vibe-projects'),
				'watching'=>_x('Watching','','vibe-projects'),
				'watch'=>_x('Watch','','vibe-projects'),
				'select_list'=>_x('Select List','','vibe-projects'),
				'select_list_to_move_cards'=>_x('Select list to move cards','','vibe-projects'),
				'send_to_board'=>_x('Send to board','','vibe-projects'),
				'archive'=>_x('Archive','','vibe-projects'),
				'change_status'=>_x('Change Status','','vibe-projects'),
				'remove_milestone'=>_x('Remove Milestone','','vibe-projects'),
				'completed'=>_x('Completed','card label','vibe-projects'),
				'not_done'=>_x('Not Done','card label','vibe-projects'),
				'milestone'=>_x('Milestone','','vibe-projects'),
				'total_cards'=>_x('Total Cards','','vibe-projects'),
				'total_milestones'=>_x('Total Milestones','','vibe-projects'),
				'complete'=>_x('Complete','','vibe-projects'),
				'milestones'=>_x('Milestones','','vibe-projects'),
				'add_milestones'=>_x('Add Milestones','','vibe-projects'),
				'members'=>_x('Members','','vibe-projects'),
				'all'=>_x('All','','vibe-projects'),
				'member_types'=>_x('Member Types','','vibe-projects'),
				'teams'=>_x('Teams','','vibe-projects'),
				'milestones_completed'=>_x('Milestones Completed','','vibe-projects'),
				'cards'=>_x('Cards','','vibe-projects'),
				'list'=>_x('List','','vibe-projects'),
				'position'=>_x('Position','','vibe-projects'),
				'current'=>_x('Current','','vibe-projects'),
				'move'=>_x('Move','','vibe-projects'),
				'please_enter_label_name'=>_x('Please enter label name','','vibe-projects'),
				'invalid_email'=>__('Invalid email address','vibe-projects'),
				'fields'=>__('Fields','vibe-projects'),
				'bulk_actions'=>__('Bulk Actions','vibe-projects'),
				'please_select_color'=>_x('Please select color','','vibe-projects'),
				'no_lables_found'=>_x('No labels found','','vibe-projects'),
				'add_new_project'=>__('New Project','vibe-projects'),
				'new_project_type'=>__('New Project Type','vibe-projects'),
				'set_project_type'=>__('Set Project Type','vibe-projects'),
				'administrators'=>__('Administrators','vibe-projects'),
				'add_new'=>__('Add New','vibe-projects'),
				'delete_project_notice'=>__('(Delete Project, Associated Boards, Associated Cards and meta)','vibe-projects'),
				'delete_project'=>__('Delete Project','vibe-projects'),
				'more_members'=>__('More Members','vibe-projects'),
				'boards'=>__('Task Boards','vibe-projects'),
				'add_new_board'=>__('Add New Board','vibe-projects'),
				'lists'=>__('Task Lists','vibe-projects'),
				'delete_list'=>__('Delete List', 'vibe-projects'),
				'default'=>__('Default', 'vibe-projects'),
				'alphabetical_asc'=>__('Alphabetically Ascending', 'vibe-projects'),
				'alphabetical_dsc'=>__('Alphabetically Descending', 'vibe-projects'),
				'older_date_creation'=>__('Older First', 'vibe-projects'),
				'recently_added'=>__('Recently Added', 'vibe-projects'),
				'move_list_cards'=>__('Move List Cards', 'vibe-projects'),
				'sort_list'=>__('Sort List', 'vibe-projects'),
				'add_another_card'=>__('Add Another Card','vibe-projects'),
				'add_new_list'=>__('Add New List','vibe-projects'),
				'more'=>__('See More','vibe-projects'),
				'project_title'=>__('Project Title','vibe-projects'),
				'project_description'=>__('Project Description','vibe-projects'),
				'create_project'=>__('Save Project','vibe-projects'),
				'update_project'=>__('Update Project','vibe-projects'),
				'select_image'=>__('Select Image','vibe-projects'),
				'image_size_error'=>__('Image exceeds upload limit. Click to re-upload.','vibe-projects'),
				'remove_user'=>__('Remove User','vibe-projects'),
				'promote_admin'=>__('Promote to Administrator','vibe-projects'),
				'promote_mod'=>__('Promote to Moderator','vibe-projects'),
				'demote_member'=>__('Demote to Member','vibe-projects'),
				'card_actions'=>__('Card Actions','vibe-projects'),
				'cancel'=>__('Cancel','vibe-projects'),
				'board_title'=>__('New Board Title','vibe-projects'),
				'board_description'=>__('New Board Description','vibe-projects'),
				'create_board'=>__('Create Board','vibe-projects'),
				'delete_board'=>__('Delete Board','vibe-projects'),
				'delete_board_notice'=>__('(Delete Board, Associated Boards, Associated Cards and meta)','vibe-projects'),
				'filter_by_board_type'=>__('Filter by board type','vibe-projects'),
				'add_members'=>__('Add Members','vibe-projects'),
				'project_type'=>__('Project Type','vibe-projects'),
				'project_visibility'=>__('Project Visibility','vibe-projects'),
				'type_to_search'=>__('Type To Search...','vibe-projects'),
				'enter_characters'=>__('Enter 3 or More Characters...','vibe-projects'),
				'update_board'=>__('Update Board','vibe-projects'),
				'list_name'=>__('List Name','vibe-projects'),
				'in_list'=>__('in List -','vibe-projects'),
				'delete_card_list'=>__('Archive List & Associated Cards','vibe-projects'),
				'my_tasks'=>__('Mine','vibe-projects'),
				'quarter_day'=>__('Quarter Day','vibe-projects'),
				'half_day'=>__('Half Day','vibe-projects'),
				'day'=>__('Half Day','vibe-projects'),
				'week'=>__('Half Day','vibe-projects'),
				'month'=>__('Month','vibe-projects'),
				'year'=>__('Year','vibe-projects'),
				'status'=>__('Status','vibe-projects'),
				'board_visibility'=>__('Board Visibility','vibe-projects'),
				'board_type'=>__('Board Type','vibe-projects'),
				'card_dates'=>__('Card Dates','vibe-projects'),
				'card_title'=>__('Card Title','vibe-projects'),
				'add_new_card'=>__('Add New Card','vibe-projects'),
				'enter_card_title'=>__('Enter Card Title...','vibe-projects'),
				'description'=>__('Description','vibe-projects'),
				'show_stats'=>__('Show statistics','vibe-projects'),
				'detailed_description'=>__('Detailed Description','vibe-projects'),
				'attachments'=>__('Attachments','vibe-projects'),
				'add_attachments'=>__('Add Attachments','vibe-projects'),
				'members'=>__('Members','vibe-projects'),
				'activity'=>__('Activity','vibe-projects'),
				'save_description'=>__('Save Description','vibe-projects'),
				'card_actions'=>__('Actions','vibe-projects'),
				'comment'=>__('Comment','vibe-projects'),
				'edit'=>__('Edit','vibe-projects'),
				'delete'=>__('Delete','vibe-projects'),
				'move'=>__('Move','vibe-projects'),
				'upcoming'=>__('Upcoming','vibe-projects'),
				'overdue'=>__('Overdue','vibe-projects'),
				'archive'=>__('Archive','vibe-projects'),
				'edit_list_title'=>__('Edit Title','vibe-projects'),
				'labels'=>__('Labels','vibe-projects'),
				'checklist'=>__('Checklist','vibe-projects'),
				'add_checklist'=>__('Add CheckList','vibe-projects'),
				'due_date'=>__('Due Date','vibe-projects'),
				'attachments'=>__('Attachments','vibe-projects'),
				'move_card'=>__('Move Card','vibe-projects'),
				'select_label_to_move'=>__('Select Label To Move','vibe-projects'),
				'invite_members'=>__('Invite Members','vibe-projects'),
				'create_new_label'=>__('Create New label','vibe-projects'),
				'label_header'=>__('Labels','vibe-projects'),
				'search_labels'=>__('Search Labels','vibe-projects'),
				'label_name'=>__('Label Name','vibe-projects'),
				'select_color'=>__('Select Color','vibe-projects'),
				'edit_label'=>__('Edit label','vibe-projects'),
				'label_button'=>__('Create Label','vibe-projects'),
				'label_color_name'=>__('Name','vibe-projects'),
				'delete_label_button'=>__('Delete','vibe-projects'),
				'no_attachments'=>__('No atachments found.','vibe-projects'),
				'edit_labels'=>__('Edit Labels','vibe-projects'),
				'change_due_date'=>__('Change Due Date','vibe-projects'),
				'add_task'=>__('Add Task','vibe-projects'),
				'add_new_task'=>__('Add New Task','vibe-projects'),
				'add'=>__('Add','vibe-projects'),
				'save'=>__('Save','vibe-projects'),
				'add_comment'=>__('Add comment','vibe-projects'),
				'write_comment'=>__('Write Comment....','vibe-projects'),
				'checklist_title'=>__('Checklist Title','vibe-projects'),
				'select_due_date'=>__('Set Due Date','vibe-projects'),
				'select_date'=>__('Select Date','vibe-projects'),
				'delete'=>__('Delete','vibe-projects'),
				'row_name'=>__('Name','vibe-projects'),
				'row_list'=>__('List','vibe-projects'),
				'row_info'=>__('Info','vibe-projects'),
				'row_start'=>__('Start Date','vibe-projects'),
				'row_end'=>__('End Date','vibe-projects'),
				'row_members'=>__('Members','vibe-projects'),
				'row_actions'=>__('Actions','vibe-projects'),
				'progress'=>__('Progress','vibe-projects'),
				'milestone_title'=>__('Milestone Title','vibe-projects'),
				'milestone_dates'=>__('Milestone Dates','vibe-projects'),
				'add_fav_projects_message'=>__('Add projects to favourites. Click on Heart icon on project to mark it as favourite.','vibe-projects'),
				'delete_milestone_message'=>__('Delete milestone. This would remove the milestone from the project.','vibe-projects'),
				'select_dependencies'=>__('Select dependencies','vibe-projects'),
				'no_tasks_found'=>__('No Tasks found','vibe-projects'),
				'no_watchers'=>__('No watchers !','vibe-projects'),
				'show_task_board'=>__('Show Tasks in Kanban Boards','vibe-projects'),
				'show_task_list'=>__('Show Tasks in Lists','vibe-projects'),
				'show_task_timeline'=>__('Show Tasks in Gant chart','vibe-projects'),
				'show_task_calendar'=>__('Show Tasks in Calendar','vibe-projects'),
				'wrap_lists'=>__('Wrap Lists','vibe-projects'),
				'show_milestones'=>__('Show Milestones','vibe-projects'),
				'filter_by'=>__('Filter by','vibe-projects'),
				'filter_dates'=>__('Due Date','vibe-projects'),
				'filter_status'=>__('Card Status','vibe-projects'),
				'filter_labels'=>__('Card label','vibe-projects'),
				'filter_members'=>__('Assignees','vibe-projects'),
				'search_tasks'=>__('Search Tasks','vibe-projects'),
				'bulk_actions_tip'=>__('Perform Bulk Actions','vibe-projects'),
				'show_filters' => __('Show filters','vibe-projects'),
				'search_task'=>__('Search Tasks','vibe-projects'),
				'send_invites'=>__('Send Invites','vibe-projects'),
				'member_email'=>__('Email','vibe-projects'),
				'member_name'=>__('Name','vibe-projects'),
				'add_member'=>__('Add Member','vibe-projects'),
				'select_member_type'=>__('Select Member Type','vibe-projects'),
				'select_member_team'=>__('Select Member Team','vibe-projects'),
				'select_cards'=>__('Select cards','vibe-projects'), 
				'cards_selected'=>__('cards selected','vibe-projects'), 
				'show_automations'=>__('Add automations','vibe-projects'),
				'apply'=>__('Apply','vibe-projects'),
				'select_milestone'=>__('Select milestone','vibe-projects'),
				'no_milestones'=>__('No milestones found.','vibe-projects'),
				'data_missing'=>__('Invalid selections or Missing data.','vibe-projects'),
				'tasks_by_lists'=>__('Tasks by lists','vibe-projects'),
				'tasks_by_labels'=>__('Tasks by labels','vibe-projects'),
				'tasks_by_status'=>__('Tasks by Status','vibe-projects'),
				'tasks_by_members'=>__('Tasks by members','vibe-projects'),
				'join_card'=>__('Join task.','vibe-projects'),
				'no_automation_options'=>__('No automation options available.','vibe-projects'),
				'create_new_automation'=>__('Create new automation','vibe-projects'),
				'select_automation'=>__('Select automation','vibe-projects'),
				'when'=>__('WHEN','vibe-projects'),
				'then'=>__('THEN','vibe-projects'),
				'select_automation_values'=>__('Select automation value','vibe-projects'),
				'due_today'=>__('Due Today','vibe-projects'),
				'due_tomorrow'=>__('Due Tomorrow','vibe-projects'),
				'due_this_month'=>__('Due This Month','vibe-projects'),
				'due_later'=>__('Later','vibe-projects'),
				'load_more'=>__('Load more','vibe-projects'),
				'show_completed'=>__('Show Completed Tasks','vibe-projects'),
				'show_upcoming'=>__('Show only upcoming Tasks','vibe-projects'),
				'hide_completed'=>__('Hide Completed Tasks','vibe-projects'),
				'card_complete'=>__('Card Complete','vibe-projects'),
				'task_burn_down'=>__('Task burn down','vibe-projects'),
				'task_reports'=>__('Task reports','vibe-projects'),
				'tasks_by_status'=>__('Task by Status','vibe-projects'),
				'card_completed'=>__('Task Completed','vibe-projects'),
				'card_created'=>__('Task Created','vibe-projects'),
				'cumulative'=>__('Net Tasks','vibe-projects'),
				'tasks_by_progress'=>__('Tasks by Progress','vibe-projects'),
				'tasks_by_time'=>__('Tasks by Time','vibe-projects'),
				'automation_applied'=>__('Automation applied','vibe-projects'),
				'member_reports'=>__('Member reports','vibe-projects'),
				'expand_tasks'=>__('Expand tasks','vibe-projects'),
				'tasks'=>__('Tasks','vibe-projects'),
				'unable_card_create'=>__('Unable to create a card without a list.','vibe-projects'),
				'start_date'=>__('Start Date','vibe-projects'),
				'on_going'=>__('On Going','vibe-projects'),
				'hide_unlisted'=>__('Hide Unlisted Tasks','vibe-projects'),
				'show_unlisted'=>__('Show Unlisted Tasks','vibe-projects'),
				'unlisted'=>__('Unlisted','vibe-projects'),
				'notify_users'=>__('Notify users','vibe-projects'),
				'all'=>__('All users','vibe-projects'),
				'notification_for'=>__('Notification for','vibe-projects'),	
				'show_hide_fields'=>__('Show hide fields','vibe-projects'),	
				'yes'=>__('Yes','vibe-projects'),	
				'no'=>__('No','vibe-projects'),	
				'project_owner'=>__('Project Owner','vibe-projects'),	
				'posted'=>_x('Posted','for time','vibe-projects'),	
				'ago'=>_x('ago','for time','vibe-projects'),
				'due_in'=>_x('Due in','for time','vibe-projects'),
				'due_by'=>_x('Due by','for time','vibe-projects'),
				'remove_members'=>__('Remove members','vibe-projects'),	
				'confirm'=>__('Confirm','vibe-projects'),
				'select_option'=>__('Select option','vibe-projects'),
				'select_project'=>__('Select a project','vibe-projects'),
				'delete_project_heading'=>__('Are you sure you want to delete this project','vibe-projects'),	
				'delete_project_message'=>__('Deleting this project would delete all of its boards, cards and reports. Click Confirm to continue.','vibe-projects'),	
				'confirm_card_delete'=>__('Are you sure you want to archive this task ?','vibe-projects'),	
				'admin'=>__('Admin','vibe-projects'),	
			)
		));
		
		wp_enqueue_style('vibe-projects',plugins_url('../assets/css/vibe_projects.css',__FILE__),array('vicons'),VIBEPROJECTS_VERSION);

		return $js_vars;
	
	}

	function show_myprojects(){

		//Show mydrive content
		add_action( 'bp_template_title', array( $this, 'projects_content_title' ) );
		add_action( 'bp_template_content', array($this,'projects_content'));
    	bp_core_load_template( 'members/single/plugins');

	}

	function projects_content_title(){
		_ex('Projects','title','vibe-projects');
	}

	function projects_content(){
		echo '<div id="projects_component"></div>';
		$login_security = get_user_meta(get_current_user_id(),'login_security',true);


		echo '<script>
		var event = new CustomEvent("component_loaded",{detail:{
			component:"projects",
			user:'.json_encode($login_security['user']).',
			token:"'.$login_security['security'].'"
		}});
		setTimeout(function(){
			document.dispatchEvent(event);
		},200);
		</script>';
	}

/*
	function show_myfavourite_projects(){

		//show my favourites tab content
		add_action( 'bp_template_title', array( $this, 'myfavourite_content_title' ) );
		add_action( 'bp_template_content', array($this,'myfavourite_content'));
    	bp_core_load_template( 'members/single/plugins');
	}

	function myfavourite_content_title(){
		_ex('My Favourite Projects','title','vibe-projects');
	}

	function myfavourite_content(){
		echo "here you'll show the content";
	}
*/

}
Vibe_Projects_Profile::init();