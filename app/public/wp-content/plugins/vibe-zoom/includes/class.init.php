<?php
/**
 * Init in VibeZoom
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	vibe_zoom/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Vibe_Zoom_Init{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Zoom_Init();
        return self::$instance;
    }

	private function __construct(){
		add_filter('wplms_course_creation_tabs',array($this,'zoom_unit'));
        add_action('wp_enqueue_scripts',array($this,'enqueue_script'));
        add_action( 'init', array( $this,'register_post_types') );
        add_action( 'bp_setup_nav', array($this,'add_zoom_tab'), 100 );

        add_filter('vibebp_component_icon',array($this,'set_icon'),10,2);
        add_filter('wplms_get_element_icon',array($this,'set_icon'),10,2);
        
        add_filter('wplms_course_created_updated',array($this,'connect_zoom_id'),10,2);

        //on crederntial select new hosts user set 
        add_filter('get_new_meeting_pre_hosts',array($this,'get_hosts'),10,3);

        //email reminder 4.x
        add_action('wplms_zoom_meeting_updated',array($this,'wplms_zoom_meeting_updated'),10,2);
        add_action('wplms_send_vibe_zoom_reminders_vibebp',array($this,'wplms_send_vibe_zoom_reminders_vibebp'),10,1);
        
        add_filter('bp_course_api_get_user_course_status_item_unit_meta',array($this,'bp_course_api_get_user_course_status_item_unit_meta'),10,4);
        
        add_filter('ws_meetings_initialize_data',array($this,'ws_meetings_initialize_data'),10,1);

        add_filter('members_detail_get_member_stats',array($this,'members_detail_get_member_stats'),10,3);
        
    }

    function set_icon($icon,$component_name){

        if($component_name == 'zoom' || $component_name == 'vibezoom' || $component_name == 'zoom_meeting'){
            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M16 16c0 1.104-.896 2-2 2h-12c-1.104 0-2-.896-2-2v-8c0-1.104.896-2 2-2h12c1.104 0 2 .896 2 2v8zm8-10l-6 4.223v3.554l6 4.223v-12z"/></svg>';
        }
        return $icon;
    }
    
    function register_post_types(){
        register_post_type( 'vibe_zoom',
			array(
				'labels' => array(
					'name' => __('Zoom meetings','vibe-zoom'),
					'menu_name' => __('VibeZoom','vibe-zoom'),
					'singular_name' => __('Meetings','vibe-zoom'),
					'add_new_item' => __('Add New Meeting','vibe-zoom'),
					'all_items' => __('Zoom Meetings','vibe-zoom')
				),
				'public' => false,
				'show_in_rest' => true,
				'publicly_queryable' => false,
				'show_ui' => true,
				'capability_type' => 'page',
	            'has_archive' => true,
				'show_in_menu' => 'vibebp',
				'show_in_admin_bar' => false,
				'show_in_nav_menus' => false,
				'supports' => array( 'title','editor','custom-fields','author'),
				'hierarchical' => false,
			)
		);
    }

    function add_zoom_tab(){
        global $bp;
            
        if( !function_exists('is_wplms_4_0') || (function_exists('is_wplms_4_0') && is_wplms_4_0())){

            $slug = 'zoom_meeting';
            bp_core_new_nav_item( array( 
                'name' => __('Zoom Meetings','vibe-zoom'),
    	        'slug' => $slug, 
    	        'item_css_id' => 'zoom_meetings',
    	        'screen_function' => array($this,'show_screen'),
    	        'default_subnav_slug' => 'home', 
    	        'position' => 58,
            	'show_for_displayed_user'=>false,
    	        'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
    	    ) );
    		bp_core_new_subnav_item( array(
    			'name' 		  => __('My Meetings','vibe-zoom'),
    			'slug' 		  => 'my_meetings',
    			'parent_slug' => $slug,
            	'parent_url' => $bp->displayed_user->domain.$slug.'/',
    			'screen_function' => array($this,'show_screen'),
    			'user_has_access' => true
    		) );

    	    bp_core_new_subnav_item( array(
    			'name' 		  => __('Manage Meeting','vibe-zoom'),
    			'slug' 		  => 'manage_meetings',
    			'parent_slug' => $slug,
            	'parent_url' => $bp->displayed_user->domain.$slug.'/',
    			'screen_function' => array($this,'show_screen'),
    			'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
            ) );
            
            if (  apply_filters('show_vibezoom_vibecalendar',true)) {
                bp_core_new_subnav_item( array(
                    'name' 		  => __('Zoom Meetings','vibe-zoom'),
                    'slug' 		  => 'vibe_zoom_meeting',
                    'parent_slug' => 'calendar',
                    'parent_url' => $bp->displayed_user->domain.$slug.'/',
                    'screen_function' => array($this,'show_screen'),
                    'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
                ) );
            }

        }
	}

	function zoom_unit($tabs){
		$tabs['course_curriculum']['fields'][0]['curriculum_elements'][1]['types'][]= array(
			'id'=>'zoom',
            'icon'=>'<svg class="zoom_icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M16 16c0 1.104-.896 2-2 2h-12c-1.104 0-2-.896-2-2v-8c0-1.104.896-2 2-2h12c1.104 0 2 .896 2 2v8zm8-10l-6 4.223v3.554l6 4.223v-12z"/></svg>',
            'label'=>__('Zoom','vibe-zoom'),
            'fields'=>array(
                array(
                    'label'=> __('Unit title','vibe-zoom' ),
                    'type'=> 'title',
                    'id' => 'post_title',
                    'from'=>'post',
                    'value_type'=>'single',
                    'style'=>'full',
                    'default'=> __('Unit Name','vibe-zoom' ),
                    'desc'=> __('This is the title of the unit which is displayed on top of every unit','vibe-zoom' )
                    ),
                array(
                    'label'=> __('Unit Tag','vibe-zoom' ),
                    'type'=> 'taxonomy',
                    'taxonomy'=> 'module-tag',
                    'from'=>'taxonomy',
                    'value_type'=>'single',
                    'style'=>'assign_cat',
                    'id' => 'module-tag',
                    'default'=> __('Select a tag','vibe-zoom' ),
                ),
                array(
                    'label'=> __('Add Zoom Meeting','vibe-zoom' ),
                    'type'=> 'selectcpt',
                    'level'=>'zoom',
                    'post_type'=>'vibe_zoom',
                    'value_type'=>'single',
                    'upload_title'=>__('Upload a Video','vibe-zoom' ),
                    'desc'=>__('Select a Zoom Meeting. Create new meetings in Zoom Meetings Menu.','vibe-zoom' ),
                    'upload_button'=>__('Set as unit Video','vibe-zoom' ),
                    'style'=>'small_icon',
                    'from'=>'meta',
                    'is_child'=>true,
                    'id' => 'vibe_zoom_meeting',
                    'default'=> '',
                ),
                array(
                    'label'=> __('What is the unit about','vibe-zoom' ),
                    'type'=> 'editor',
                    'style'=>'',
                    'value_type'=>'single',
                    'id' => 'post_content',
                    'from'=>'post',
                    'extras' => '',
                    'default'=> __('Enter description about the unit.','vibe-zoom' ),
                ),
                array(
                    'label'=> __('Unit duration','vibe-zoom' ),
                    'type'=> 'duration',
                    'style'=>'course_duration_stick_left',
                    'id' => 'vibe_duration',
                    'from'=> 'meta',
                    'default'=> array('value'=>9999,'parameter'=>86400),
                    'from'=>'meta',
                ),
                array( 
                    'label' => __('Free Unit','vibe-zoom'),
                    'desc'  => __('Set Free unit, viewable to all','vibe-zoom'), 
                    'id'    => 'vibe_free',
                    'type'  => 'switch',
                    'default'   => 'H',
                    'from'=>'meta',
                ),
                array(
                    'label' => __('Unit Forum','vibe-zoom'),
                    'desc'  => __('Connect Forum with Unit.','vibe-zoom'),
                    'id'    => 'vibe_forum',
                    'type'  => 'selectcpt',
                    'post_type' => 'forum',
                    'std'=>0,
                    'from'=>'meta',
                ),
                array(
                    'label' => __('Connect Assignments','vibe-zoom'),
                    'desc'  => __('Select an Assignment which you can connect with this Unit','vibe-zoom'),
                    'id'    => 'vibe_assignment',
                    'type'  => 'selectmulticpt', 
                    'post_type' => 'assignment',
                    'from'=>'meta',
                ),
                array(
                    'label' => __('Attachments','vibe-zoom'),
                    'desc'  => __('Display these attachments below units to be downloaded by students','vibe-zoom'),
                    'id'    => 'vibe_unit_attachments', 
                    'type'  => 'multiattachments', 
                    'from'=>'meta',
                ),
                array(
                    'label'=> __('Practice Questions','wplms' ),
                    'text'=> '',
                    'type'=> 'practice_questions',
                    'from'=>'meta',
                    'post_type'=>'question',
                    'id' => 'vibe_practice_questions',
                    'default'=> __('Select a type','wplms' ),
                    'buttons' => array(
                        'question_types'=>wplms_get_question_types(),
                    )
                ),
            ),
		);
		return $tabs;
	}


	function enqueue_script(){
        if(function_exists('bp_is_user') && bp_is_user() || apply_filters('vibebp_enqueue_profile_script',false)){
            $blog_id = '';
            if(function_exists('get_current_blog_id')){
                $blog_id = get_current_blog_id();
            }


                
            $zoom=apply_filters('vibe_zoom_script_args',array(
                'api'=>array(
                    'url'=>get_rest_url($blog_id,VIBE_ZOOM_API_NAMESPACE),
                    'create_caps'=>'edit_posts',
                ),
                'settings'=>array(
                    'editor_slug' => apply_filters('vibezoom_editor_slug',array('manage_meetings')),
                    'timestamp'=>time(),
                    'new_vibezoom_cap'=>['edit_posts'],
                    'dafault_password' => '64327892',
                    'websdk_language' => ''
                ),
                'label'=>__('Vibe Zoom','vibe-zoom'),
                'sorters'=>array(
                    'date'=>_x('Recent','api','vibe-zoom'),
                    'name'=>_x('Alphabetical','api','vibe-zoom'),
                ),
                'shared_tabs'=>array(
                    'shared'=>_x('Shared ','api','vibe-zoom'),
                    'group'=>_x('Group','api','vibe-zoom'),
                    'course'=>_x('Course','api','vibe-zoom'),
                ),
                'shared_types'=>array(
                    'shared'=>_x('Shared ','api','vibe-zoom'),
                    'group'=>_x('Group','api','vibe-zoom'),
                    'course'=>_x('Course','api','vibe-zoom'),
                ),
                'translations'=>array(
                    'my_zooms'=>__('My Meetings', 'vibe-zoom'),
                    'create_zoom'=>__('Create New', 'vibe-zoom'),
                    'meeting_title'=>__('Meeting Title', 'vibe-zoom'),
                    'meeting_content'=>__('Meeting Content', 'vibe-zoom'),
                    'zoom_category'=>__('Meeting Category', 'vibe-zoom'),
                    'no_zooms'=>__('No Meetings found.', 'vibe-zoom'),
                    'search_text'=>__('Type to Search..', 'vibe-zoom'),
                    'submit'=>__('Submit Meeting', 'vibe-zoom'),
                    'preview'=>__('Preview Meeting', 'vibe-zoom'),
                    'new_zoom_cateogry'=>__('New Meeting Category', 'vibe-zoom'),
                    'load_more'=>__('Load more', 'vibe-zoom'),
                    'no_steps_created'=>__('No Steps Created!', 'vibe-zoom'),
                    'title'=>__('Title', 'vibe-zoom'),
                    'description'=>__('Description', 'vibe-zoom'),
                    'meeting_start_time'=>__('Meeting Start Time', 'vibe-zoom'),
                    'meeting_end_time'=>__('Meeting End Time', 'vibe-zoom'),
                    'meeting_duration_in_minutes'=>__('Meeting Duration In Minutes', 'vibe-zoom'),
                    'password'=>__('Password', 'vibe-zoom'),
                    'select_sharing_type'=>__('Select Sharing Type', 'vibe-zoom'),
                    'select_shared_value'=>__('Select Shared Items', 'vibe-zoom'),
                    'search_shared_values'=>__('Search sharing value', 'vibe-zoom'),
                    'join_before_host'=>__('Join Before Host', 'vibe-zoom'),
                    'host_video'=>__('Host Video', 'vibe-zoom'),
                    'participant_video'=>__('Participants Video', 'vibe-zoom'),
                    'mute_upon_entry'=>__('Mute Upon Entry', 'vibe-zoom'),
                    'enforce_login'=>__('Enforce Login', 'vibe-zoom'),
                    'auto_recording'=>__('Auto Recording', 'vibe-zoom'),
                    'select_host'=>__('Select Host(Required)', 'vibe-zoom'),
                    'select_host_user'=>__('Select Host User', 'vibe-zoom'),
                    'select_multihost_users'=>__('Select Multiple Co-Host Users(optional)', 'vibe-zoom'),
                    'select_cohost_user'=>__('Select Co-host User', 'vibe-zoom'),
                    'time_zone'=>__('Time Zone', 'vibe-zoom'),
                    'select_time_zone'=>__('Select Time Zone', 'vibe-zoom'),
                    'shared_with'=>__('Shared With', 'vibe-zoom'),
                    'vsearch_results'=>__('Search Result', 'vibe-zoom'),
                    'start_date'=>__('Start Date','vibe-zoom'),
                    'start_url'=>__('Start Url','vibe-zoom'),
                    'join_url'=>__('Join Url','vibe-zoom'),
                    'timezone'=>__('TimeZone','vibe-zoom'),
                    'minutes'=>__('Minutes','vibe-zoom'),
                    'join_meeting'=> __('Join Meeting','vibe-zoom'),
                    'open_meeting'=> __('Open Meeting','vibe-zoom'),
                    'start_meeting'=> __('Start Meeting','vibe-zoom'),
                    'open_meeting_in_new_tab'=>__('Open Meeting in new tab','vibe-zoom'),
                    'passcode'=>  __('Passcode:','vibe-zoom'),
                    'join_url'=>  __('Join URL','vibe-zoom'),
                    'click_to_copy_url'=>__('Click To Copy URL','vibe-zoom'),
                    'click_to_copy'=>__('Click To Copy Code','vibe-zoom'),
                    'meeting_over'=>__('Meeting Over','vibe-zoom'),
                    'meeting_running'=>__('Meeting Running','vibe-zoom'),
                    'days'=>__('Days','vibe-zoom'),
                    'hours'=>__('Hours','vibe-zoom'),
                    'minutes'=>__('Minutes','vibe-zoom'),
                    'seconds'=>__('Seconds','vibe-zoom'),
                    'today'=>__('Today','vibe-zoom'),
                    'month'=>__('Month','vibe-zoom'),
                    'week'=>__('Week','vibe-zoom'),
                    'day'=>__('Day','vibe-zoom'),
                    'list'=>__('List','vibe-zoom'),
                    'starts'=>__('Starts','vibe-zoom'),
                    'ends'=>__('Ends','vibe-zoom'),
                    'cancel'=>__('Cancel','vibe-zoom'),
                    'view_details'=>__('View Details','vibe-zoom'),
                    'not_valid'=>__('Not valid!','vibe-zoom'),
                    'no_recordings'=>__('No Recordings Found!','vibe-zoom'),
                    'starts'=>__('Starts','vibe-zoom'),
                    'ends'=>__('Ends','vibe-zoom'),
                    'view_recording'=>__('View Recording','vibe-zoom'),
                    'select_multihost_credential'=>__('Choose credential to create/edit meeting(Optional)','vibe-zoom'),
                    'show_recordings_to_user'=>__('Show recodings to user','vibe-zoom'),
                )
            )); 
            wp_enqueue_script('createzoom',plugins_url('../assets/js/create_zoom.js',__FILE__),array('wp-element','wp-data'),VIBE_ZOOM_VERSION);
            wp_enqueue_script('zoom_calendar',plugins_url('../assets/js/zoom_calendar.js',__FILE__),array('wp-element','wp-data'),VIBE_ZOOM_VERSION);
            wp_localize_script('createzoom','vibezoom',$zoom);
            wp_enqueue_style('vibe-zoom',plugins_url('../assets/css/create_zoom.css',__FILE__),array(),VIBE_ZOOM_VERSION);
        }
	}
     
    function connect_zoom_id($return,$body){
        if(!empty($return['course_id'])){
            foreach($body['course']['meta'] as $meta){
                if($meta['meta_key'] == 'vibe_course_curriculum' && is_Array($meta['meta_value'])){
                    global $wpdb;
                    $ids = [];
                    foreach($meta['meta_value'] as $val){
                        if(is_numeric($val)){
                            $ids[]=$val;
                        }
                    }
                    $zoom_meetings = $wpdb->get_results("SELECT post_id,meta_value FROM {$wpdb->postmeta} WHERE meta_key='vibe_zoom_meeting' AND post_id IN (".implode(',',$ids).")");
                   
                    if(!empty($zoom_meetings)){
                        foreach($zoom_meetings as $meeting_id){
                            update_post_meta($meeting_id->meta_value,'shared_type','course');
                            $values = get_post_meta($meeting_id,'shared_values',false);
                            if(empty($values) || !in_Array($return['course_id'],$values)){
                                add_post_meta($meeting_id->meta_value,'shared_values',$return['course_id']);
                            }
                        }
                    }
                }
            }
        }
        return $return; 
	}
	
	function get_hosts($hosts,$args,$user_id=0){
        if(!empty($args['key'])){
            if($args['key'] == 'admin'){
                return $hosts;
            }

            $hosts = array();
            $vibe_zoom_api_init = get_zoom_api_object($args['key']);
            if(!empty($vibe_zoom_api_init)){
                $encoded_users = $vibe_zoom_api_init->listUsers();
                $decoded_users = json_decode( $encoded_users );
                if ( ! empty( $decoded_users->code ) &&( $decoded_users->code == 300  || $decoded_users->code == 124) || empty($decoded_users->users)) {
                    $users = false;
                } else {
                    $users = $decoded_users->users;
                }
                $new_users = array();
                if(!empty($users)){
                    $users = (array)$users;
                    foreach ($users as $key => $value) {
                        $new_users[$value->id] = $value->first_name.' '.$value->last_name.' ('.$value->email.')';
                    }
                    $hosts = $new_users;
                }
            }
        }
        return $hosts;
    }

    function wplms_zoom_meeting_updated($post_id,$user_id){
        if(!empty($post_id)){
            $option = get_option('vibe_zoom_settings');
            if(!empty($option['vibe_zoom_enable_reminder'])){


                $details = get_post_meta($post_id,'vibe_zoom_meeting_details',true);
                
                if(!empty($details['start_time'])){
                    $start_timestamp = strtotime($details['start_time'])*1000;

                    $timestamp = $start_timestamp - (int)$option['vibe_zoom_reminder_time'] *1000; //ms
                    $args = array($post_id);

                    wp_clear_scheduled_hook('wplms_send_vibe_zoom_reminders_vibebp',$args);
                    $timestamp_in_sec = $timestamp/1000;
                    wp_schedule_single_event($timestamp_in_sec,'wplms_send_vibe_zoom_reminders_vibebp',$args);
                   
                }
            }
        }
    }

    function new_bp_core_email_register($user_id){
        $post_title =  __( 'Zoom meeting reminder', 'vibe-zoom' );
        if ( ! function_exists( 'post_exists' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/post.php' );
        }
        $post_exists = post_exists( $post_title );
    
        if ( $post_exists != 0 && get_post_status( $post_exists ) == 'publish' )
        return;
    
        // Create post object
        $my_post = array(
            'post_title'    => $post_title,
            'post_content'  => sprintf(__('Meeting %s is about to start in %s','vibe-zoom'),'{{meeting.name}}','{{{meeting.timeleft_html}}}'),  // HTML email content.
            'post_excerpt'  => sprintf(__('Meeting %s is about to start in %s','vibe-zoom'),'{{meeting.name}}','{{{meeting.timeleft}}}'),  // Plain text email content.
            'post_status'   => 'publish',
            'post_type' => bp_get_email_post_type(),
            'post_author' => $user_id
        );
    
        $post_id = wp_insert_post( $my_post );
    
        if ( $post_id ) {
            // add our email to the taxonomy term 'zoom_meeting_reminder'
            // Email is a custom post type, therefore use wp_set_object_terms
            $tt_ids = wp_set_object_terms( $post_id, 'zoom_meeting_reminder', bp_get_email_tax_type() );
            foreach ( $tt_ids as $tt_id ) {
                $term = get_term_by( 'term_taxonomy_id', (int) $tt_id, bp_get_email_tax_type() );
                wp_update_term( (int) $term->term_id, bp_get_email_tax_type(), array(
                    'description' => __( 'Vibe Zoom meeting reminder', 'vibe-zoom' ),
                ) );
            }
        }
    }
    
    //send mail here to users //do_action('wplms_send_vibe_zoom_reminders_vibebp',array($post_id));
    function wplms_send_vibe_zoom_reminders_vibebp($args){ //array($post_id);
        
        $post_id = $args;

        if(!empty($post_id)){
            $option = get_option('vibe_zoom_settings');
            if(!empty($option['vibe_zoom_enable_reminder'])){
                $user_ids = $this->get_meeting_user_ids($post_id);
                if(is_array($user_ids) && !empty($user_ids)){
                    $title =  get_the_title( $post_id );
                    $timeleft =  $this->calculate_timeleft($post_id);
                    foreach ($user_ids as $key => $user_id) {
                        $zoom_component_url = bp_core_get_user_domain($user_id).'#component=zoom_meeting';
                        $timeleft_html = '<a href="'.$zoom_component_url.'" target="_blank">'.$timeleft.'</a>';
                        $args = array(
                            'tokens' => array(
                                'meeting.name' => $title,
                                'meeting.timeleft' => $timeleft,
                                'meeting.timeleft_html' => $timeleft_html,
                            ),
                        );

                        bp_send_email( 'zoom_meeting_reminder', (int) $user_id, $args );
                    }
                }
            }
        }
    }

    function calculate_timeleft($post_id){
        $timeleft  = '';
        $details = get_post_meta($post_id,'vibe_zoom_meeting_details',true);
        if(!empty($details['start_time'])){
            $date1 = strtotime($details['start_time']);  
            $date2 = strtotime("now"); 
            // Formulate the Difference between two dates 
            $diff = abs($date2 - $date1);  
            
            
            $years = floor($diff / (365*60*60*24)); 
            if(!empty($years)){
                $timeleft .= sprintf(__(' %d years,','vibe-zoom'),$years);
            }
            
            
            $months = floor(($diff - $years * 365*60*60*24) 
                                        / (30*60*60*24)); 
            if(!empty($months)){
                $timeleft .= sprintf(__(' %d months,','vibe-zoom'),$months);
            }
            
        
            $days = floor(($diff - $years * 365*60*60*24 -  
                        $months*30*60*60*24)/ (60*60*24)); 
            if(!empty($days)){
                $timeleft .= sprintf(__(' %d days,','vibe-zoom'),$days);
            }
            

            $hours = floor(($diff - $years * 365*60*60*24  
                - $months*30*60*60*24 - $days*60*60*24) 
                                            / (60*60));  
            if(!empty($hours)){
                $timeleft .= sprintf(__(' %d hours,','vibe-zoom'),$hours);
            }
            

            $minutes = floor(($diff - $years * 365*60*60*24  
                    - $months*30*60*60*24 - $days*60*60*24  
                                    - $hours*60*60)/ 60);  
            if(!empty($minutes)){
                $timeleft .= sprintf(__(' %d minutes,','vibe-zoom'),$minutes);
            }
            
            
            $seconds = floor(($diff - $years * 365*60*60*24  
                    - $months*30*60*60*24 - $days*60*60*24 
                            - $hours*60*60 - $minutes*60)); 
            if(!empty($seconds)){
                $timeleft .= sprintf(__(' %d seconds','vibe-zoom'),$seconds);
            }
        }
        return $timeleft;
    }

    function get_meeting_user_ids($id){
        $type = get_post_meta($id,'shared_type',true);
        $users = array();
        switch ($type) {
            case 'shared':
                    $users = get_post_meta($id,'shared_values');
                break;
            case 'course':
                    $courses = get_post_meta($id,'shared_values');
                    if(!empty($courses) && is_array($courses)){
                        global $wpdb;
                        foreach ($courses as $key => $course_id) {
                            $query = "SELECT user_id  FROM {$wpdb->usermeta} where `meta_key` LIKE 'course_status{$course_id}' AND `meta_value` = 2";
                            $ncourse_members = $wpdb->get_results($query,ARRAY_A);
                            if(!empty($ncourse_members) && is_array($ncourse_members)){
                                foreach ($ncourse_members as $ncourse_member) {
                                    if(!in_array($ncourse_member['user_id'],$users)){
                                        $users[] = $ncourse_member['user_id'];
                                    }
                                }
                            }
                        }
                    }
                break;
            case 'group':
                    $groups = get_post_meta($id,'shared_values');
                    if(!empty($groups) && is_array($groups)){
                        foreach ($groups as $key => $group_id) {
                            $args = array( 
                                'group_id' => $group_id,
                                'per_page' => -1,
                                'exclude_admins_mods' => false
                            );
                            $group_members_result = groups_get_group_members( $args );
                            foreach(  $group_members_result['members'] as $member ) {
                                if(!in_array($member->ID,$users)){
                                    $users[] = $member->ID;
                                }
                            }
                        }
                    }
                break;    
            default:
                break;
        }
        return apply_filters('vibe_zoom_get_meeting_user_ids',$users,$id);
    }

    function bp_course_api_get_user_course_status_item_unit_meta($meta,$course_id,$item_id,$user_id=null){
        $type = get_post_meta($item_id,'vibe_type',true);
        if($type == 'zoom' ){
            $meta['unit_type'] = 'wplms_zoom';
            $post_id = get_post_meta($item_id,'vibe_zoom_meeting',true);
            $api_obj = Vibe_Zoom_API::init();
            $meta['zoom'] =  $api_obj->get_meeting_by_id($post_id,$user_id);
        }
        return $meta;
    }

    function ws_meetings_initialize_data($arr){
        $arr['zoom'] = array(
            'name' => __('Zoom','vibe-zoom'),
            'api' => get_rest_url($blog_id,VIBE_ZOOM_API_NAMESPACE.'/user/meetings'),
            'css_id' => 'zoom_meeting'
        );
        return $arr;
    }

    function members_detail_get_member_stats($arr,$body,$token_user_id){
        if(function_exists('bp_activity_get')){
            global $wpdb,$bp;
            $query = $wpdb->prepare("SELECT COUNT(DISTINCT ac.id) FROM {$bp->activity->table_name} as ac WHERE ac.type= 'vibe_zoom_join' and ac.user_id=%d",(int)$body['user_id']);
            $results = (int)$wpdb->get_var($query);
            $arr[] = array(
                'key' => 'zoom_joined',
                'type' => 'int',
                'label' => __('Zoom Joined','vibe-zoom'),
                'value' => $results
            );
        }
        return $arr;
    }
    
}
Vibe_Zoom_Init::init();