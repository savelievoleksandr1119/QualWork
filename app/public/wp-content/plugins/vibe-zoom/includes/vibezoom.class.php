<?php

if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('Vibe_Zoom'))
{   
    class Vibe_Zoom  // We'll use this just to avoid function name conflicts 
    {
        public static $instance;
        public static function init(){
            if ( is_null( self::$instance ) )
                self::$instance = new Vibe_Zoom();
            return self::$instance;
        } 
        public function __construct(){  
            $this->hosts = array();
            add_shortcode('vibe_zoom',array($this,'Vibe_Zoom_shortcode')); 

            add_action( 'media_buttons', array($this,'vibe_zoom_create_meeting'),100);
            add_action('media_upload_vibe_zoom_meetings',array($this,'media_bbb_create_meeting'));

            add_action('wp_ajax_select_users_bbb',array($this,'select_users_bbb'));
            add_action('wp_ajax_create_new_meeting_zoom',array($this,'create_new_meeting'));
            add_action('wp_ajax_edit_meeting_zoom',array($this,'edit_meeting'));
            add_action('Vibe_Zoom_meeting_created',array($this,'set_reminder_cron_jobs'));
            add_action('wplms_send_Vibe_Zoom_reminders',array($this,'wplms_send_Vibe_Zoom_reminders'),10,2);
            add_action('wp_ajax_fetch_meeting_iframe',array($this,'fetch_meeting_iframe'));
            add_action('Vibe_Zoom_user_meeting_logout',array($this,'user_meeting_logout'));
            add_action('wp_ajax_join_bbb_Vibe_Zoom_do_action',array($this,'join_bbb_Vibe_Zoom_do_action'));
            add_action('Vibe_Zoom_user_meeting_join',array($this,'record_join_meeting_activity'));
            add_action('wp_ajax_meeting_logout',array($this,'meeting_logout'));
            add_filter('bp_course_all_mails',array($this,'add_Vibe_Zoom_email'));

            add_action( 'bp_setup_nav', array($this,'Vibe_Zoom_meetings_tab'),5);

            add_filter('wplms_get_all_meetings',array($this,'apply_instructor_privacy'));
            //reset cron jobs on edit meeting
            add_action('Vibe_Zoom_meeting_edited',array($this,'reset_crons_on_edit'),10,3);
            add_action('wp_ajax_delete_wplms_bb_meeting',array($this,'delete_wplms_bb_meeting'));
            add_action('admin_print_scripts',array($this,'remove_badge_os_scripts_for_bbb'));
            $this->restrictions = apply_filters('bbb_restrictions_options',array(
                'logged_in'=>__('Logged in users','vibe-zoom'),
                'instructors'=>__('Instructors only','vibe-zoom'),
                'course_students'=>__('Course students','vibe-zoom'),
                'selected_users'=>__('Selected users only','vibe-zoom'),
                
            ));
            if( function_exists('bp_is_active') && bp_is_active( 'groups' ) ){
                add_action('wp_ajax_get_front_groups_bbb',array($this,'get_groups'));
                $this->restrictions['group'] = __('Group','vibe-zoom');
            }
            $this->offset = get_option('gmt_offset');
            $this->Vibe_Zoom_meetings = get_option('Vibe_Zoom_meetings');
            $this->open_in_new_tab = apply_filters('wplms_open_in_new_tab',1,$this->Vibe_Zoom_meetings);
        } // END public function __construct
        public function activate(){
            // ADD Custom Code which you want to run when the plugin is activated
        }
        public function deactivate(){

                wp_clear_scheduled_hook('wplms_send_Vibe_Zoom_reminders');  
            
        }

        function video_conferencing_zoom_api_get_user_transients() {
            
            if(!empty($this->hosts))
                return $this->hosts;

            $this->options = get_option('vibe_zoom_settings');
            if(!empty($this->options) &&  !empty($this->options['vibe_zoom_api_key'])){
                $encoded_users = vibe_zoom_api_init()->listUsers();
                $decoded_users = json_decode( $encoded_users );
            if ( ! empty( $decoded_users->code ) && $decoded_users->code == 300 ) {
                $users = false;
            } else {
                $users = $decoded_users->users;
            }
            }else{
                $users = array();
            }

            $new_users = array();
            if(!empty($users)){
                $users = (array)$users;
                foreach ($users as $key => $value) {
                    $new_users[$value->id] = $value->first_name.' '.$value->last_name.' ('.$value->email.')';
                }
            }
            $this->hosts = $new_users;
            return $this->hosts;
        }

        function vibe_zoom_create_meeting(){
            
            global $post;
            $ids = '';
            if(is_object($post) && !empty($post) && is_admin() ){
               if( get_post_type($post->ID) == 'unit')
                $ids = $post->ID;
            }
            ?>
            <script>
            jQuery(document).ready(function($){
                $('#course_curriculum').on('active',function(){
                    $('.wplms-bbb-button').on('click',function () {
                    var unit_id = $(this).closest('.element_overlay').find('#save_element_button').attr('data-id');
                        $(this).find('#meeting_info_meta').val(unit_id);
                        $('body').trigger('meeting_meta_added',[{"unit_id":unit_id}]);
                    });
                });
            });
            </script>
            <?php
            echo '<a href="'.admin_url('media-upload.php?type=vibe_zoom_meetings&TB_iframe=true&tab=all_meetings').'" class="thickbox wplms-bbb-button button">
             <div class="dashicons dashicons-format-status"></div> '._x('Meetings','','wplms-bbb').'<input type="hidden" id="meeting_info_meta" name="meeting_info_meta" value="'.$ids.'"></a>';
             ?>
             <script>
             jQuery(document).ready(function($){
                $("body").on("meeting_meta_added",function(e, data){
                    jQuery( "body").on( "thickbox:iframe:loaded", function() {
                        if(typeof data.unit_id !== "undefined"){
                            $("#TB_iframeContent").contents().find(".insert_meeting").attr("data-meta",data.unit_id);
                        }else{
                            $("#TB_iframeContent").contents().find(".insert_meeting").attr("data-meta",$("#meeting_info_meta").val());
                        }
                        
                    });  
                });
                
             });
             </script>
             <?php
        }

        function media_bbb_create_meeting(){

            if(isset($_GET['tab']) && $_GET['tab']=='create'){
                
                wp_iframe( array($this,"media_create_meeting_content" ));
            }
            elseif(isset($_GET['tab']) && $_GET['tab']=='edit' && isset($_GET['meeting'])){
                wp_iframe( array($this,"media_edit_meeting_content" ));
            }else{
                wp_iframe( array($this,"media_all_meetings_form" ));
            }
        }

        function get_groups(){
            $user_id = get_current_user_id();
            if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'bbb_meetings'.$user_id) ){
                echo 'Security check failed !';
                die();
            } 
            $q = $_POST['q'];

            if(function_exists('groups_get_group')){
                $vgroups =  groups_get_groups(array(
                'per_page'=>999,
                'search_terms'=>$q['term'],
               'user_id' => $user_id,
               'show_hidden'=>true,
                ));
                $return = array();
                foreach($vgroups['groups'] as $vgroup){
                    $return[] = array('id'=>$vgroup->id,'text'=>$vgroup->name);
                }
            }
            print_r(json_encode($return));
            die();
        }

        function remove_badge_os_scripts_for_bbb(){
            if(!empty($_GET['type']) && $_GET['type'] == 'Vibe_Zoom_meetings'){

                if(in_array( 'badgeos/badgeos.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'badgeos/badgeos.php'))){
                      wp_deregister_script('badgeos-select2');
                      wp_dequeue_script('badgeos-select2');
                     // wp_deregister_script('select2');
                      //wp_iframedequeue_script('select2');
                      wp_dequeue_style('badgeos-select2-css');
                      wp_deregister_style('badgeos-select2-css');
                }
            }
        }

        function apply_instructor_privacy($all_meetings){
            if(!is_user_logged_in() || (is_user_logged_in() && current_user_can('manage_options')))
                return $all_meetings;
            if(function_exists('vibe_get_option')){
                $inst_privacy_enabled = vibe_get_option('instructor_content_privacy');
                if(!empty($inst_privacy_enabled)){
                    $user_id = get_current_user_id();
                    foreach($all_meetings as $key => $meeting){
                        if($meeting['author'] != $user_id ){
                            unset($all_meetings[$key]);
                        }
                    }
                }
            }
            
            return $all_meetings;
        }

        function Vibe_Zoom_meetings_tab(){
            if(!function_exists('is_wplms_4_0') || !is_wplms_4_0()){
                bp_core_new_nav_item( array( 
                    'name' => __( 'Meetings','vibe-zoom'), 
                    'slug' => 'zoommeetings', 
                    'screen_function' => array($this,'Vibe_Zoom_screen'), 
                    'show_for_displayed_user' => false,
                    'item_css_id' => 'zoommeetings',
                    'default_subnav_slug' => 'home', 
                    'position' => 55,
                    ) 
                );
            }
        }

        function Vibe_Zoom_screen() {
            add_action( 'bp_template_title',array($this, 'Vibe_Zoom_title' ));
            add_action( 'bp_template_content', array($this,'Vibe_Zoom_content') );
            bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
        }

        function Vibe_Zoom_title() {
           echo '<h3 class="heading"><span>'._x('Meetings','','vibe-zoom').'</span></h3>';
        }

        function delete_wplms_bb_meeting(){
            if(!is_user_logged_in() || (is_user_logged_in() && !current_user_can('edit_posts'))){
                return;
            }
            $user_id = get_current_user_id();
            if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'bbb_meetings'.$user_id) ){
                echo 'Security check failed !';
                die();
            }
            if(empty($_POST['meeting_id'])){
                die();
            }
            if(empty($this->Vibe_Zoom_meetings))
                die();
            $new_meetings = $this->Vibe_Zoom_meetings;
            $meeting_id = sanitize_text_field($_POST['meeting_id']);
            $meeting = $this->get_meeting($meeting_id);
            if(empty($meeting) || empty($meeting['author']))
                die();
            $flag  = 0;
            if(function_exists('vibe_get_option')){
                $inst_privacy_enabled = vibe_get_option('instructor_content_privacy');
                if(empty($inst_privacy_enabled)){
                   $flag = 1; 
                }elseif(!empty($inst_privacy_enabled) && ($meeting['author'] == $user_id)){
                    $flag = 1;
                }
            }
            if($flag || current_user_can('manage_options')){
                unset($new_meetings[$meeting_id]);
                update_option('Vibe_Zoom_meetings',$new_meetings);
                if ( ! empty( $meeting_id ) ) {
                    vibe_zoom_api_init()->deleteAMeeting( $meeting_id );
                }


            }else{
                echo 'You are not allowed to delete this meeting';
            }
            die();
        }

        function Vibe_Zoom_content() {
            if(empty($this->Vibe_Zoom_meetings)){
                echo '<div class-"message">'._x('No meetings found','','vibe-zoom').'</div>';
                return;
            }
            if(!is_user_logged_in())
                return;
            $user_id = get_current_user_id();
            echo '<table id="user-tours" class="table table-hover">';
            echo '<thead><tr><th>'._x('Meeting name','','vibe').'</th><th>'._x('Privacy','','vibe-zoom').'</th><th>'._x('Status','','vibe').'</th><th>'._x('Action','','vibe').'</th></tr></thead><tbody>';
            foreach ($this->Vibe_Zoom_meetings as $meetng_id => $meeting) {
                $scope = $meeting['restrictions']['scope'];
                $flag = 0;
                $users = $this->users_from_restriction($meeting,1);
                if(in_array($user_id,$users)){
                    
                    $status = _x('NA','','vibe-zoom');
                    if(!empty($meeting['meeting_details']) && !empty($meeting['meeting_details']['start_time'])){
                        $start_time = strtotime($meeting['meeting_details']['start_time']);

                        $_start_time = strtotime($meeting['start_date'].' '.$meeting['start_time']);

                        $_expiry_time = $_start_time + ($meeting['duration']['duration']* $meeting['duration']['parameter']);                            $expiry_time = $start_time + ($meeting['duration']['duration']* $meeting['duration']['parameter']);
                        
                        $format = get_option( 'date_format' ).' '.get_option('time_format');
                        $readable_time_start = date_i18n($format ,$_start_time);
                        $readable_time_expire =date_i18n($format , $_expiry_time); 
                        if(time() >= $start_time &&  time() <= $expiry_time ){
                            $status = _x('Ongoing','','vibe-zoom');
                        }elseif(time() <= $start_time){
                        
                            $status = sprintf(_x('To be started on %s (%s)','','vibe-zoom'), $readable_time_start ,$meeting['timezone']);
                        }elseif(time() >= $expiry_time){
                            $status =sprintf( _x('Meeting over on %s (%s)','','vibe-zoom'),$readable_time_expire,$meeting['timezone'] );
                        }
                    }
                   
                    $restriction = _x('NA','','vibe-zoom');
                    if(!empty($meeting['restrictions'])){
                        if(!empty($meeting['restrictions']['scope']))
                        $restriction = $this->restrictions[$meeting['restrictions']['scope']];
                        if(!empty($meeting['restrictions']['data'])){
                            if($meeting['restrictions']['scope'] == 'course_students'){
                                foreach($meeting['restrictions']['data'] as $course){
                                  $restriction .= '<br><span>('.get_the_title($course).')</span>';  
                                }
                                
                            }elseif($meeting['restrictions']['scope'] == 'selected_users'){
                                $i=0;
                                $count = (count($meeting['restrictions']['data'])-2);
                                $restriction .= '<br><span>(';
                                foreach ($meeting['restrictions']['data'] as $value) {
                                    if($i < 2){
                                      $student = get_user_by('id',$value);
                                      $restriction .= $student->display_name;
                                    }
                                    if($i < 1){
                                        $restriction .= ',';
                                    }
                                    $i++;
                                }
                                if($count > 0){
                                    $restriction .= sprintf(_x(' and %s more','','vibe-zoom'),$count);
                                }
                                $restriction .= ')</span>';
                            }elseif($meeting['restrictions']['scope'] == 'group'){
                                foreach($meeting['restrictions']['data'] as $id){
                                    if(function_exists('groups_get_group')){
                                        $group = groups_get_group(esc_attr($id));
                                    }
                                  $restriction .= '<br><span>('.$group->name.')</span>';  
                                }
                            }
                        }
                    }
                    echo '<tr><td>'.$meeting['name'].'</td>';
                    echo '<td>'.$restriction.'</td>';
                    echo '<td>'.$status.'</td>';
                    echo '<td>'.do_shortcode('[vibe_zoom token="'.$meeting['id'].'" popup="1" size="1"]').'</td>';
                    echo '<tr>';
                }
            }
            echo '</tbody></table>';


        }

        function record_join_meeting_activity($meeting_id){

            if(!is_user_logged_in())
                return;
            if(empty($meeting_id))
                return;
            $user_id = get_current_user_id();
            $meeting = $this->get_meeting($meeting_id);
            global $wpdb;
            $table_name = $wpdb->prefix.'bp_'.'activity';
            $meta_table_name = $wpdb->prefix.'bp_'.'activity_meta';
            $offset = $this->offset;
            $utc_time = time();
            $start_time = strtotime($meeting['start_date'].' '.$meeting['start_time']);
            if($offset > 0){//means gmt offset is in positive
                $start_time = $start_time - (abs($offset)*60*60);
                $utc_time = $utc_time - (abs($offset)*60*60);
                

            }else{//means gmt offset is in negative
                $start_time = $start_time + (abs($offset)*60*60);
                $utc_time = $utc_time + (abs($offset)*60*60);
                
            }
            $meeting_expire_time = $start_time + ($meeting['duration']['duration']*$meeting['duration']['parameter']);
           
            if($meeting_expire_time <= time())
                return;
            $sql = "SELECT a.date_recorded as activity_time from {$table_name} as a 
            LEFT JOIN {$meta_table_name} as m 
            ON a.id = m.activity_id WHERE 
            a.user_id= {$user_id} 
            AND m.meta_value = '{$meeting_id}' 
            AND m.meta_key ='meeting_id_join' 
            ORDER BY a.id DESC LIMIT 0,1";
            $meta = $wpdb->get_row($sql);
  
            if(!empty($meta)){
                $activity_time = strtotime($meta->activity_time);
                if($activity_time  >= $start_time &&  $activity_time <= $meeting_expire_time ){
                    return;
                }
            } 
            if(!empty($meeting['course'])){
                //record activity show message //
                if(function_exists('bp_activity_add') && function_exists('bp_core_get_user_displayname')){

                   $activity_id = bp_activity_add(array(
                      'action' => sprintf(__('Student %s joins meeting %s','vibe-zoom'),bp_core_get_user_displayname($user_id),$meeting['name']),
                      'content' => sprintf(__('Student %s joined meeting %s','vibe-zoom'),bp_core_get_user_displayname($user_id),$meeting['name']),
                      'type' => 'meeting_joined',
                      'item_id' => $meeting['course'],
                      'primary_link'=>get_permalink($meeting['course']),
                      'secondary_item_id'=>$user_id
                    ));
                    bp_activity_update_meta($activity_id,'meeting_id_join',$meeting_id);
                    
                } 
            }
        }

        function join_bbb_Vibe_Zoom_do_action(){
            if(!is_user_logged_in())
                die();
            $user_id = get_current_user_id();
            if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'join_bbb_Vibe_Zoom_do_action') || empty($_POST['meeting_id'])){
                echo 'Security check failed !';
                die();
            }
            $meeting_id = sanitize_text_field($_POST['meeting_id']);
            do_action('Vibe_Zoom_user_meeting_join',  $meeting_id );
            die();
        }

        function meeting_logout(){
            
            if(!is_user_logged_in())
                die();
         
            $user_id = get_current_user_id();
             var_dump(wp_verify_nonce($_REQUEST['meeting_security'],'meeting_logout'.$user_id));
            if ( empty($_REQUEST['meeting_security']) || !wp_verify_nonce($_REQUEST['meeting_security'],'meeting_logout'.$user_id) || empty($_REQUEST['meeting'])){
                echo 'Security check failed !';
                die();
            }
            do_action('Vibe_Zoom_user_meeting_logout',$_REQUEST['meeting']);
            die();
            
        }

        function user_meeting_logout($meeting_id){
            if(!is_user_logged_in())
                return;
            $user_id = get_current_user_id();
            $meeting = $this->get_meeting($meeting_id);
            global $wpdb;
            $table_name = $wpdb->prefix.'bp_'.'activity';
            $meta_table_name = $wpdb->prefix.'bp_'.'activity_meta';
            $offset = $this->offset;
            $utc_time = time();
            $start_time = strtotime($meeting['start_date'].' '.$meeting['start_time']);
            if($offset > 0){//means gmt offset is in positive
                $start_time = $start_time - (abs($offset)*60*60);
                $utc_time = $utc_time - (abs($offset)*60*60);
                

            }else{//means gmt offset is in negative
                $start_time = $start_time + (abs($offset)*60*60);
                $utc_time = $utc_time + (abs($offset)*60*60);
                
            }
            $meeting_expire_time = $start_time + ($meeting['duration']['duration']*$meeting['duration']['parameter']);
           
            if($meeting_expire_time <= time())
                return;
            $sql = "SELECT a.date_recorded as activity_time from {$table_name} as a 
            LEFT JOIN {$meta_table_name} as m 
            ON a.id = m.activity_id WHERE 
            a.user_id= {$user_id} 
            AND m.meta_value = '{$meeting_id}' 
            AND m.meta_key ='meeting_id_logout' 
            ORDER BY a.id DESC LIMIT 0,1";
            $meta = $wpdb->get_row($sql);
  
            if(!empty($meta)){
                $activity_time = strtotime($meta->activity_time);
                if($activity_time  >= $start_time &&  $activity_time <= $meeting_expire_time ){
                    return;
                }
            } 
          
            if(!empty($meeting['course'])){
                //record activity show message //
                if(function_exists('bp_activity_add') && function_exists('bp_core_get_user_displayname')){
                   $activity_id = bp_activity_add(array(
                      'action' => sprintf(__('Student logs out from meeting %s','vibe-zoom'),bp_core_get_user_displayname($user_id),$meeting['name']),
                      'content' => sprintf(__('Student %s logs out from meeting %s','vibe-zoom'),bp_core_get_user_displayname($user_id),$meeting['name']),
                      'type' => 'meeting_logout',
                      'item_id' => $meeting['course'],
                      'primary_link'=>get_permalink($meeting['course']),
                      'secondary_item_id'=>$user_id
                    ));
                   bp_activity_update_meta($activity_id,'meeting_id_logout',$meeting_id);
                  
                } 
            }
            echo _x('You logged out from meeting.','','vibe-zoom');
        }

        function add_Vibe_Zoom_email($bp_emails){

            $bp_emails['Vibe_Zoom_reminder']=array(
            'description'=> __('Meeting about to start','vibe-zoom'),
            'subject' =>  sprintf(__('%s meeting is about to start.','vibe-zoom'),'{{meeting.name}}'),
            'message' =>  sprintf(__('Meeting %s is about to start on %s','vibe-zoom'),'{{meeting.name}}','{{{site.name}}}')
            );
            return $bp_emails;
        }

        function set_reminder_cron_jobs($option_data){
            //set cron job for reminder
            if(empty($option_data['reminder']))
                return;
            if(!empty($option_data['start_date'])){
               $start_time = strtotime($option_data['start_date'].' '.$option_data['start_time']); 

                $timestamp = 0;
                $offset = $this->offset;
                $utc_time = time();
                $reminder = ($option_data['reminder']['duration']*$option_data['reminder']['parameter']);
                if($offset > 0){//means gmt offset is in positive
                    $start_time = $start_time - (abs($offset)*60*60);
                    $utc_time = $utc_time - (abs($offset)*60*60);
                    if($start_time > $reminder){
                       $timestamp = $start_time - $reminder;
                    }

                }else{//means gmt offset is in negative
                    $start_time = $start_time + (abs($offset)*60*60);
                    $utc_time = $utc_time + (abs($offset)*60*60);
                    if($start_time > $reminder){
                       $timestamp = $start_time - $reminder;
                    }
                }
                /*$timestamp = $start_time - $option_data['reminder'];
                $timestamp += (abs($offset)*60*60);*/
                if(  $timestamp  > $utc_time){
                    $users = array();
                    if(!empty($option_data['restrictions']['scope'])){

                        $users = $this->users_from_restriction($option_data);
                        
                    }
                    if(!empty($users)){
                        $args = array($option_data['name'], $users);
                        if(!empty($args) && count($args)){
                            wp_clear_scheduled_hook($timestamp,'wplms_send_Vibe_Zoom_reminders',$args);
                            if(!wp_next_scheduled('wplms_send_Vibe_Zoom_reminders',$args)){
                                wp_schedule_single_event($timestamp,'wplms_send_Vibe_Zoom_reminders',$args);
                            }
                        }
                    }
                }
            }

        }

        function reset_crons_on_edit($old_options_data,$option_data,$meeting_id){
            if(empty($meeting_id) || empty($option_data))
                return;
            if(!empty($option_data['start_date'])){
                $start_time = strtotime($option_data['start_date'].' '.$option_data['start_time']);
                $timestamp = 0;
                $offset = $this->offset;
                $utc_time = time();
                $reminder = ($option_data['reminder']['duration']*$option_data['reminder']['parameter']);
                if($offset > 0){//means gmt offset is in positive
                    $start_time = $start_time - (abs($offset)*60*60);
                    $utc_time = $utc_time - (abs($offset)*60*60);
                    if($start_time > $reminder){
                       $timestamp = $start_time - $reminder;
                    }

                }else{//means gmt offset is in negative
                    $start_time = $start_time + (abs($offset)*60*60);
                    $utc_time = $utc_time + (abs($offset)*60*60);
                    if($start_time > $reminder){
                       $timestamp = $start_time - $reminder;
                    }
                }
       
                if(  $timestamp  > $utc_time){
                    $users = array();
                    if(!empty($option_data['restrictions']['scope'])){
                        $old_users = $this->users_from_restriction($old_options_data);
                        $users = $this->users_from_restriction($option_data);
                        
                    }
                    if(!empty($old_users)){
                        $old_args = array($old_options_data['name'], $old_users);
                        wp_clear_scheduled_hook('wplms_send_Vibe_Zoom_reminders',$old_args);
                    }
                    
                    if(!empty($users)){
                        $args =  array($option_data['name'], $users);
                        if(!wp_next_scheduled('wplms_send_Vibe_Zoom_reminders',$args))
                            wp_schedule_single_event($timestamp,'wplms_send_Vibe_Zoom_reminders',$args);
                    }
                }
            }
        }

        function users_from_restriction($option_data,$check_access=0){
            if(empty($option_data['restrictions']['scope']))
                return;
            $users = array();
            switch($option_data['restrictions']['scope']){
                case 'course_students':
                    if(!empty($option_data['restrictions']['data']) &&  function_exists('bp_course_get_course_students')){
                        //there are no multiple courses its just course id saved in array loop will run only once
                        foreach ($option_data['restrictions']['data'] as $course) {
                            $course_students = bp_course_get_course_students($course,'',9999999);
                        }
                        if(!empty($course_students) && count($course_students)){
                            $users = $course_students['students'];
                        }
                    }
                break;
                case 'selected_users':
                    if(!empty($option_data['restrictions']['data']) && count($option_data['restrictions']['data'])){
                        $users = $option_data['restrictions']['data'];
                    }
                break;
                case 'instructors':
                    if(current_user_can('edit_posts')){
                        return array(get_current_user_id());
                    }
                break;
                case 'logged_in':
                case 'everyone':
                    if(!$check_access){
                       $role_not_in = apply_filters('Vibe_Zoom_reminder_users_role_not_in',array());
                        $exclude = apply_filters('Vibe_Zoom_reminder_users_exclude',array());
                        $args = array(
                            'role__not_in' => $role_not_in,
                            'exclude'      => $exclude,
                         ); 
                        $role_not_in = apply_filters('Vibe_Zoom_reminder_users_role_not_in',array());
                        $exclude = apply_filters('Vibe_Zoom_reminder_users_exclude',array());
                        $args = array(
                            'role__not_in' => $role_not_in,
                            'exclude'      => $exclude,
                         ); 
                        $students = get_users( $args );
                        foreach ($students as $student) {
                             $users[] = $student->ID;
                        }
                    }else{
                        $users = apply_filters('Vibe_Zoom_logged_in',array(get_current_user_id()));
                    }
                break;
                case 'group':
                if( function_exists('bp_is_active') && bp_is_active( 'groups' ) ){
                    if(!empty($option_data['restrictions']['data'])){
                        $per_page = apply_filters('Vibe_Zoom_group_members_per_page',9999,$option_data);
                        //there are no multiple courses its just course id saved in array loop will run only once
                        foreach ($option_data['restrictions']['data'] as $group) {
                            $has_members_str = "group_id=" . $group.'&per_page='.$per_page.'&exclude_admins_mods=0';

                            if ( bp_group_has_members( $has_members_str ) ): 
                                
                            while ( bp_group_members() ) : bp_group_the_member();
                                $users[] = bp_get_group_member_id();
                            endwhile;
                            endif;
                        }
                    }
                }
                    
                break;
                default:
                    $users = apply_filters('Vibe_Zoom_logged_in',array(get_current_user_id()));
                break;
            }
            if(is_array($users)){
                $users[] = $option_data['author'];
            }
            
            return $users;
        }

        function wplms_send_Vibe_Zoom_reminders($meeting_name,$users){
            $bpargs = array(
                'tokens' => array('meeting.name' => $meeting_name),
            );
         
            foreach ($users as  $user) {
                $user = get_user_by('id',$user);
                bp_send_email( 'Vibe_Zoom_reminder',$user->user_email, $bpargs );  
            }
            wp_clear_scheduled_hook('wplms_send_Vibe_Zoom_reminders',array($meeting_name,$users));
        }

        function create_new_meeting(){
            if(!is_user_logged_in() || (is_user_logged_in() && !current_user_can('edit_posts'))){
                return;
            }
            $user_id = get_current_user_id();
            if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'bbb_meetings'.$user_id) ){
                echo 'Security check failed !';
                die();
            }
            $data = stripcslashes($_POST['data']);
            $data = json_decode($data);

            $processed_data = array();
            if(!empty($data) && class_exists('Vibe_Zoom_Video_Conferencing_Api')){
                foreach ($data as $key => $value) {
                    if($value->field == 'mduration' || $value->field == 'mdurationparam' || $value->field == 'mdrduration' || $value->field == 'mdrdurationparam'  ){
                        if(is_numeric($value->value)){
                            $processed_data[$value->field] = $value->value;
                        }
                    }elseif($value->field == 'mrusers'){
                        if(is_array($value->value)){
                            foreach ($value->value as $k => $v) {
                                if(!is_numeric($v)){
                                    unset($value->value[$k]);
                                }
                            }
                            $processed_data[$value->field] = $value->value;
                        }
                    }elseif($value->field == 'mrcourses'){
                        if(is_numeric($value->value)){
                          $processed_data[$value->field] = $value->value;  
                        }
                    }else{
                        $processed_data[$value->field] = sanitize_text_field($value->value);
                    }
                }
                $data =  $processed_data;

                $moderator_code = $data[ 'moderatorPW' ] ;
                $option_auto_recording = $data[ 'option_auto_recording' ] ;
                $host_video     = (empty($data['host_video'])?false:true);
                $participant_video     = (empty($data['participant_video'])?false:true);
                $join_before_host     = (empty($data['join_before_host'])?false:true); 
                $mute_upon_entry     = (empty($data['mute_upon_entry'])?false:true); 

                $minutes = (intval($data['mduration'])*intval($data['mdurationparam']))/60;

                //create meeting on zoom server
                $pwd       = sanitize_text_field( $moderator_code );
                $pwd       = ! empty( $pwd ) ? $pwd : $post->ID;
                $mtg_param = array(
                    'userId'                    => $data['userId'],
                    'meetingTopic'              => $data['meetingName'],
                    'start_date'                => sanitize_text_field( $data['m_date'].' '.$data['m_time'] ),
                    'timezone'                  => sanitize_text_field( $data['timezone'] ),
                    'duration'                  => $minutes,
                    'password'                  => $pwd,
                    'join_before_host'          =>  $join_before_host,
                    'option_host_video'         => $host_video,
                    'option_participants_video' => $participant_video,
                    'option_mute_participants'  => $mute_upon_entry,
                    'option_auto_recording'     => $option_auto_recording,
                );

                $meeting_created = json_decode( vibe_zoom_api_init()->createAMeeting( $mtg_param ),true );
                if ( empty( $meeting_created->error ) && !empty($data['meetingName'])) {
                    //add this meeting in options table 
                    $option_data = array(
                                    'id' => $meeting_created['id'],
                                    'name'=>$data['meetingName'],
                                    'userId'    => $data['userId'],
                    );


                    $option_data['author'] = $user_id;
                    if(!empty($data['mduration']) && !empty($data['mdurationparam'])){
                       $option_data['duration'] = array(
                                                    'duration'=>$data['mduration'],
                                                    'parameter'=>$data['mdurationparam']);
                    }
                    if(!empty($data['mrestriction'])){

                       $restrictions_array = array(
                            'scope' => $data['mrestriction'],
                        );
                       if(!empty($data['mrcourses']) && $data['mrestriction'] == 'course_students'){
                            $restrictions_array['data'] = array($data['mrcourses']);
                            $option_data['course'] = $data['mrcourses'];
                       }elseif(!empty($data['mrusers']) && $data['mrestriction'] == 'selected_users'){
                           $restrictions_array['data'] = $data['mrusers'];
                       }elseif(!empty($data['mrgroups']) && $data['mrestriction'] == 'group'){
                            $restrictions_array['data'] = array($data['mrgroups']);
                            $option_data['group'] = $data['mrgroups'];
                       }
                       $option_data['restrictions'] = $restrictions_array;
                    }
                    if(!empty($data['mdr'])){
                        if(!empty($data['mdrduration']) && !empty($data['mdrdurationparam'])){
                           $option_data['reminder'] = array(
                                                    'duration'=>$data['mdrduration'],
                                                    'parameter'=>$data['mdrdurationparam']);
                        }
                    }
                    if(!empty($data['m_date'])){
                        $option_data['start_date'] = $data['m_date'];
                    }
                    if(!empty($data['m_time'])){
                        $option_data['start_time'] = $data['m_time'];
                    }

                    foreach ($mtg_param as $k => $v) {
                        if(!isset($option_data[$k])){
                            $option_data[$k] = $v;
                        }
                    }

                    $option_data['meeting_details'] = $meeting_created;
                    $existing = $this->Vibe_Zoom_meetings;
                    if(empty($existing)){
                        $existing = array();
                    }

                    if(!empty($option_data) && is_array($option_data)){
                        $existing[$meeting_created['id']] = $option_data;
                        update_option('Vibe_Zoom_meetings',$existing);
                        $out .= '<div class="updated">
                                <p>
                                <strong>'._x('Meeting Room Created','','vibe-zoom').'</strong>
                                </p>
                                </div>';
                        do_action('Vibe_Zoom_meeting_created',$option_data);
                    }
                }
                
            }
            echo $out;
            die();
        }

        function edit_meeting(){
            if(!is_user_logged_in() || (is_user_logged_in() && !current_user_can('edit_posts'))){
                die();
            }

            $user_id = get_current_user_id();
            if ( !isset($_POST['security']) || empty($_POST['meeting_id']) || !wp_verify_nonce($_POST['security'],'edit_meeting'.$user_id.$_POST['meeting_id']) ){
                echo 'Security check failed !';
                die();
            }
           
            $meeting_id = sanitize_text_field($_POST['meeting_id']);
            $meeting = $this->get_meeting($meeting_id);

            if(empty($meeting) || empty($meeting['author']))
                die();
            $flag  = 0;
            if(function_exists('vibe_get_option')){
                $inst_privacy_enabled = vibe_get_option('instructor_content_privacy');
                if(empty($inst_privacy_enabled)){
                   $flag = 1; 
                }elseif(!empty($inst_privacy_enabled) && ($meeting['author'] == $user_id)){
                    $flag = 1;
                }
            }
            if($flag || current_user_can('manage_options')){
                $data = stripcslashes($_POST['data']);
                $data = json_decode($data);

                $processed_data = array();
                if(!empty($data)){
                       foreach ($data as $key => $value) {
                        if($value->field == 'mduration' || $value->field == 'mdurationparam' || $value->field == 'mdrduration' || $value->field == 'mdrdurationparam'  ){
                            if(is_numeric($value->value)){
                                $processed_data[$value->field] = $value->value;
                            }
                        }elseif($value->field == 'mrusers'){
                            if(is_array($value->value)){
                                foreach ($value->value as $k => $v) {
                                    if(!is_numeric($v)){
                                        unset($value->value[$k]);
                                    }
                                }
                                $processed_data[$value->field] = $value->value;
                            }
                        }elseif($value->field == 'mrcourses'){
                            if(is_numeric($value->value)){
                              $processed_data[$value->field] = $value->value;  
                            }
                        }else{
                            $processed_data[$value->field] = sanitize_text_field($value->value);
                        }
                    } 
                }
                
                $data =  $processed_data; 
                
                $option_auto_recording = $data[ 'option_auto_recording' ] ;
                $host_video     = (empty($data['host_video'])?false:true);
                $participant_video     = (empty($data['participant_video'])?false:true);
                $join_before_host     = (empty($data['join_before_host'])?false:true); 
                $mute_upon_entry     = (empty($data['mute_upon_entry'])?false:true); 
                $minutes = (intval($data['mduration'])*intval($data['mdurationparam']))/60;
                $pwd       = sanitize_text_field( $data['moderatorPW'] );
                $pwd       = ! empty( $pwd ) ? $pwd :$meeting_id;
                $mtg_param = array(
                    'meeting_id'                => $meeting_id,
                    'userId'                    => $data['userId'],
                    'topic'                     => $data['meetingName'],
                    'start_date'                => sanitize_text_field( $data['m_date'].' '.$data['m_time'] ),
                    'timezone'                  => sanitize_text_field( $data['timezone'] ),
                    'duration'                  => $minutes,
                    'password'                  => $pwd,
                    'option_jbh'                => $join_before_host,
                    'option_host_video'         => $host_video,
                    'option_participants_video' =>  $participant_video,
                    'option_mute_participants'  => $mute_upon_entry,
                    'option_auto_recording'     => $option_auto_recording,
                );

                $meeting_updated = json_decode( vibe_zoom_api_init()->updateMeetingInfo( $mtg_param ) ,true);
                
                if ( empty( $meeting_updated->error ) ) {
                    
                    $meeting_info = json_decode( vibe_zoom_api_init()->getMeetingInfo( $meeting_id ) ,true);

                    if ( ! empty( $meeting_info ) ) {
                        $option_data = array(
                                        'id' => $meeting_info['id'],
                                        'name'=>$data['meetingName'],
                                        'userId' => $meeting_info['host_id'],
                        );
                        if(!empty($meeting['author'])){
                            $option_data['author'] = $meeting['author'];
                        }else{
                            $option_data['author'] = $user_id; 
                        }
                        
                        if(!empty($data['mduration']) && !empty($data['mdurationparam'])){
                           $option_data['duration'] = array(
                                                        'duration'=>$data['mduration'],
                                                        'parameter'=>$data['mdurationparam']);
                        }
                        if(!empty($data['mrestriction'])){

                           $restrictions_array = array(
                                'scope' => $data['mrestriction'],
                            );
                           if(!empty($data['mrcourses']) && $data['mrestriction'] == 'course_students'){
                                $restrictions_array['data'] = array($data['mrcourses']);
                                $option_data['course'] = $data['mrcourses'];
                           }elseif(!empty($data['mrusers']) && $data['mrestriction'] == 'selected_users'){
                               $restrictions_array['data'] = $data['mrusers'];
                           }elseif(!empty($data['mrgroups']) && $data['mrestriction'] == 'group'){
                                $restrictions_array['data'] = array($data['mrgroups']);
                                $option_data['group'] = $data['mrgroups'];
                           }
                           $option_data['restrictions'] = $restrictions_array;
                        }


                        if(!empty($data['mdr'])){
                            if(!empty($data['mdrduration']) && !empty($data['mdrdurationparam'])){
                               $option_data['reminder'] = array(
                                                        'duration'=>$data['mdrduration'],
                                                        'parameter'=>$data['mdrdurationparam']);
                            }
                        }

                        
                        if(!empty($data['m_date'])){
                            $option_data['start_date'] = $data['m_date'];
                        }
                        if(!empty($data['m_time'])){
                            $option_data['start_time'] = $data['m_time'];
                        }
                        foreach ($mtg_param as $k => $v) {
                            if(!isset($option_data[$k])){
                                if($k == 'option_jbh'){
                                    $k = 'join_before_host';
                                }
                                $option_data[$k] = $v;
                            }
                        }
                        $option_data['meeting_details'] = $meeting_info;
                        $existing = $this->Vibe_Zoom_meetings;
                        if(empty($existing)){
                            $existing = array();
                        }
                        if(!empty($option_data) && is_array($option_data)){
                            
                            $existing[$meeting_id] = $option_data;

                            update_option('Vibe_Zoom_meetings',$existing);
                            
                            echo '<div class="updated"><p><strong>'._x('Meeting Room Edited','','wplms-bbb').'</strong></p></div>';   
                           
                            do_action('Vibe_Zoom_meeting_edited',$meeting,$option_data,$meeting_id);
                            
                        }
                    }
                    

                }
            }else{
                echo '<div class="message notice error">You cannnot edit this meeting...</div>';
            }
            
            die();
        }

        function select_users_bbb(){
            if(!is_user_logged_in() || (is_user_logged_in() && !current_user_can('edit_posts'))){
                return;
            }
            $user_id = get_current_user_id();
            if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'bbb_meetings'.$user_id) ){
                echo 'Security check failed !';
                die();
            }
            global $wpdb;
            $term= '';
            if(!empty($_POST['q']['term'])){
                $term = sanitize_text_field($_POST['q']['term']);
            }
            
            $q = "
                SELECT ID, display_name FROM {$wpdb->users} 
                WHERE (
                    user_login LIKE '%$term%'
                    OR user_nicename LIKE '%$term%'
                    OR user_email LIKE '%$term%' 
                    OR user_url LIKE '%$term%'
                    OR display_name LIKE '%$term%'
                    )";
            $users = $wpdb->get_results($q);

            $user_list = array();
              // Check for results
            if (!empty($users)) {
                foreach($users as $user){
                    $user_list[] = array(
                      'id'=>$user->ID,
                      'image'=>bp_core_fetch_avatar(array('item_id' => $user->ID, 'type' => 'thumb', 'width' => 32, 'height' => 32, 'html'=>false)),
                      'text'=>$user->display_name
                    );
                }
                echo json_encode($user_list);
            } else {
                echo json_encode(array('id'=>'','text'=>_x('No Users found !','No users found in Course - admin - add users area','vibe-zoom')));
            }
            die();
        }



        function vibe_zoom_tabs($tabs) {
            
            $newtab1 = array('create' => __('Add meeting','vibe-zoom'));
            $newtab2 = array('all_meetings' => __('All meetings','vibe-zoom'));
            if( isset($_GET['meeting'])){
             $newtab3 = array('edit' => __('Edit meeting','vibe-zoom'));
             return array_merge(   $newtab3, $newtab2,$newtab1);
            }
            return array_merge( $newtab2,$newtab1);
        }

        function print_tabs(){ 
            add_filter('media_upload_tabs', array($this,'vibe_zoom_tabs'));
            media_upload_header();
        }

        function Vibe_Zoom_shortcode($atts, $content = null){

            extract(shortcode_atts(array(
                    'token' => '',
                    'popup' => '',
                    'size' => null,
                    ), $atts));

            if(empty($token) || empty($this->Vibe_Zoom_meetings))
                return;

            $meeting = $this->get_meeting($token);

            if(empty($meeting ) || empty($meeting['meeting_details']) || empty($meeting['meeting_details']['start_time']))
                return;

            $user_id = get_current_user_id();
            $users = $this->users_from_restriction( $meeting,1);
            if(!empty($users) && !in_array($user_id , $users))
                return;

            $return = '';
            $_start_time = $start_time = 0;
            if(!empty($meeting['meeting_details']) && !empty($meeting['meeting_details']['start_time'])){
                $start_time = strtotime($meeting['meeting_details']['start_time']);
                 $_start_time =    strtotime($meeting['start_date'].' '.$meeting['start_time']);
            }

            $meeting_expiry_time = $start_time + ($meeting['duration']['duration']*$meeting['duration']['parameter']);
             $meeting_expiry_time_gmt = $_start_time + ($meeting['duration']['duration']*$meeting['duration']['parameter']);
            if($meeting_expiry_time <= time()){
                $format = get_option( 'date_format' ).' '.get_option('time_format');;
                $display_expire_time = date_i18n($format ,$meeting_expiry_time_gmt );
                $return .='<div class="message">'. sprintf(_x('Meeting Expired on %s','meeting expired','vibe-zoom'),$display_expire_time).'</div><br>';
            }else{
                //$utc_time is current time adjusted with gmt
                if( $start_time < time()){ //show meeting iframe   
                    $return .= '<div class="bbb_meeting_wrapper '.$meeting['id'].'">';
                        $return .=  $this->meeting_form($meeting,$popup,$size);
                    $return .=  '</div>';

                }else{ //show conutown and do ajax call 

                    $return .=  '<div class="bbb_meeting_wrapper waiting '.$meeting['id'].'">'.do_shortcode('[countdown_timer event="'.$meeting['id'].'" seconds="'.( $start_time-time()).'" size="'.((!empty($size) && is_numeric($size))?$size:3).'"]').'</div>';
                 
                    $return .= "<script>
                        //ajax call for fetch meeting iframe open meeting
                        jQuery(document).ready(function($){
                            $('body').on('".$meeting['id']."',function(){
                                //make call fetch meeting
                                $.ajax({
                                    type: 'POST',
                                    url: ajaxurl,
                                    data: { action: 'fetch_meeting_iframe', 
                                            security: '".wp_create_nonce('bbb_user_meeting'.$user_id)."',
                                            popup:'".$popup."',
                                            meeting_id:'".$meeting['id']."',
                                          },
                                    cache: false,
                                    success: function (html) {
                                       console.log(html);
                                       $('.bbb_meeting_wrapper.".$meeting['id']."').html(html);
                                       $('body').trigger('Vibe_Zoom_button_loaded');
                                    }   
                                });
                            });
                        });
                        </script>";
                }
            }

           return $return; 
        }

        function meeting_form($meeting,$popup=null,$size=null){
            if(!is_user_logged_in())
                return;
            if(empty($meeting['id']))
                return;
            $user = wp_get_current_user();
            
            $check_author_admin_for_meeting = 0;
            if($meeting_post->post_author == $user->ID || is_super_admin()){
                $check_author_admin_for_meeting = 1;
            }
            $vibezoom_joinURL = '';
            if(!empty($meeting['meeting_details'])){
               if (  $check_author_admin_for_meeting   ) {
                    //If the password submitted is correct then the user gets redirected
                    $vibezoom_joinURL = $meeting['meeting_details']['start_url'];
                }else{
                    $vibezoom_joinURL = $meeting['meeting_details']['join_url'];
                } 
            }
            if(!empty($vibezoom_joinURL)){
                if(!$this->open_in_new_tab){
                    $out .= '<a class="vibe-zoom-meeting-popup button" href="'.$vibezoom_joinURL.'">'.__('Open Meeting','vibe-zoom').'</a>';
                    do_action('Vibe_Zoom_user_meeting_join',$meeting['id']);
                }else{
                    $out .=  '<a class="button" target="_blank" href="'.$vibezoom_joinURL.'">'.__('Open Meeting','vibe-zoom').'</a>';
                    do_action('Vibe_Zoom_user_meeting_join',$meeting['id']);
                }
            }else {
                
                $out .= '<div class="message">'._x('Some error occured','','vibe-zoom').'</div>';
                return $out;
            }
            return $out;

            //If the viewer has the correct password, but the meeting has not yet started they have to wait 
            //for the moderator to start the meeting
            
                
            
        }

        function wplms_bigbluebutton_display_redirect_script($meeting,$vibezoom_joinURL, $meetingID, $meetingName, $name,$popup=null) {
            if(empty($popup)){
                  $embed =  do_shortcode('[iframe]'.$vibezoom_joinURL.'[/iframe]');
            }else{
                $embed  .= '<a class="vibe-zoom-meeting-popup button" href="'.$vibezoom_joinURL.'">'.__('Open Meeting','vibe-zoom').'</a>';
            }
            $out .= '
            <script type="text/javascript">';
            if(!empty($popup)){
            $out .= 'jQuery(document).ready(function(){
                        jQuery(".vibe-zoom-meeting-popup").magnificPopup({type: "iframe",midclick:false});
                        jQuery("body").on("Vibe_Zoom_button_loaded",function(){
                            jQuery(".vibe-zoom-meeting-popup").magnificPopup({type: "iframe",midclick:false});
                        });
                    });';
            }

            $out .= 'function wplms_bigbluebutton_ping() {
                    jQuery.ajax({
                        url : "'.plugins_url('bigbluebutton/php/broker.php?action=ping&meetingID='.urlencode($meetingID)).'",
                        async : true,
                        dataType : "xml",
                        success : function(xmlDoc) {
                            $xml = jQuery( xmlDoc ), $running = $xml.find( "running" );
                            if($running.text() == "true") {
                                var meeting_content = \''.$embed.'\';

                                jQuery("body").find(".'.$meeting['id'].'.bbb_meeting_wrapper").html(meeting_content);
                                clearInterval(Vibe_Zoom_interval);
                                jQuery.ajax({
                                    type: "POST",
                                    url: ajaxurl,
                                    async:true,
                                    data: { action: "join_bbb_Vibe_Zoom_do_action", 
                                            security: "'.wp_create_nonce('join_bbb_Vibe_Zoom_do_action').'",
                                            meeting_id:"'.$meeting['id'].'",
                                          },
                                    cache: false,
                                    
                                });
                                jQuery("body").trigger("Vibe_Zoom_button_loaded");
                            }
                        },
                        error : function(xmlHttpRequest, status, error) {
                            console.debug(xmlHttpRequest);
                        }
                    });
                }
                var Vibe_Zoom_interval = setInterval("wplms_bigbluebutton_ping()", 15000);
            </script>';

            $out .= '
            <table>
              <tbody>
                <tr>
                  <td>
                    '.sprintf(_x("Welcome %s!","","vibe-zoom"),$name).'<br /><br />
                    '.sprintf(_x("%s session has not been started yet.","","vibe-zoom"),$meetingName).'
                    <br /><br />
                    <div align="center"><img src="'.plugins_url('bigbluebutton/images/polling.gif').'" /></div><br />
                    '._x("(Meeting will be loaded automatically.)","","vibe-zoom").'
                    
                  </td>
                </tr>
              </tbody>
            </table>';

            return $out;
        }

        function fetch_meeting_iframe(){
            if(!is_user_logged_in())
                die();
            $user_id = get_current_user_id();
            if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'bbb_user_meeting'.$user_id) || empty($_POST['meeting_id'])){
                echo 'Security check failed !';
                die();
            }
            $meeting_id = sanitize_text_field($_POST['meeting_id']);
            $meeting = $this->get_meeting($meeting_id );
            $popup = 0;
            if(!empty($_POST['popup']) && is_numeric($_POST['popup'])){
                $popup = sanitize_text_field($_POST['popup']);
            }
            echo $this->meeting_form($meeting,$popup);
            die();
        }

        function print_js(){
            if(defined('VIBE_PLUGIN_URL')){
                if(!is_user_logged_in())
                    return;
                $user_id = get_current_user_id();
                wp_enqueue_script('customselect2-bbb',VIBE_PLUGIN_URL.'/vibe-customtypes/metaboxes/js/select2.min.js');
                wp_enqueue_style('customselect2-bbb',VIBE_PLUGIN_URL.'/vibe-customtypes/metaboxes/css/select2.min.css');
                wp_enqueue_style( 'vibe-zoom-css', plugins_url( '../assets/css/vibe-zoom.css' , __FILE__ ));
                wp_enqueue_style( 'meta_box_css', VIBE_PLUGIN_URL . '/vibe-customtypes/metaboxes/css/meta_box.css');
               wp_enqueue_script('vibe-zoom-meetings', plugins_url( '../assets/js/vibe-zoom.js' , __FILE__ ),array('jquery','jquery-migrate'));
                $translation_array = array( 
                'ajax_url' => admin_url( 'admin-ajax.php' ) ,
                'more_chars'=> __( 'Please enter more characters','vibe-zoom'),
                'security' => wp_create_nonce('bbb_meetings'.$user_id),
                'vibe_security'=>wp_create_nonce('vibe_security'),
                'creating' => _x('Adding...','','vibe-zoom'),
                'editing' => _x('Editing...','','vibe-zoom'),
                'deleting' => _x('Deleting...','','vibe-zoom'),
                'sure' => _x('Are you sure you want to delete this meeting?','','vibe-zoom'),
                'required_warning' => _x('Please fill all required fields','','vibe-zoom'),
                );
                wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery', 'jquery-ui-core' ) );
                wp_enqueue_script( 'timepicker_box', VIBE_PLUGIN_URL . '/vibe-customtypes/metaboxes/js/jquery.timePicker.min.js', array( 'jquery' ) );
                wp_localize_script('vibe-zoom-meetings', 'vibe_zoom_meetings_strings', $translation_array );
                echo '<script type="text/javascript">

                        jQuery(document).ready(function() {

                            jQuery("#m_date").datepicker({
                            dateFormat: \'yy-mm-dd\',

                              monthNames: [ "Januar", "Februar", "Marts", "April", "Maj", "Juni", "Juli", "August", "September", "Oktober", "November", "December" ],
                                dayNames: [ "Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi" ]


                            });
                            jQuery("input.timepicker").timePicker({});
                        });
                        </script><style>.create_meeting {padding:15px;}.all_meetings{padding:15px;}.edit_meeting{padding:15px;}</style>';
            }
        }

        function print_create(){

            $this->print_js();
            $options = array(
                array('value' =>'1','label'=>__('Seconds','vibe-zoom')),
                array('value' =>'60','label'=>__('Minutes','vibe-zoom')),
                array('value' =>'3600','label'=>__('Hours','vibe-zoom')),
                array('value' =>'86400','label'=>__('Days','vibe-zoom')),
                array('value' =>'604800','label'=>__('Weeks','vibe-zoom')),
                array('value' =>'2592000','label'=>__('Months','vibe-zoom')),
            );
            
            ?>
            <form id="Vibe_Zoom_create_form" name="form1" method="post" action="">
                <!-- course id here-->

                <table class="form-table" style="    width: 90%;">
                    <tbody>

                    <tr><td><?php echo _x('Meeting Host:','','vibe-zoom') ;?> </td>
                        
                        <td>
                            <select name="userId" class="bbb_create_m_field">
                            <?php
                            if(!empty($this->video_conferencing_zoom_api_get_user_transients())){
                                foreach ($this->video_conferencing_zoom_api_get_user_transients() as $key => $value) {
                                    echo '<option value="'.$key.'" >'.$value.'</option>';
                                }
                            }
                            ?>
                            </select>
                        </td>
                    </tr>
                        <tr><td><?php echo _x('Meeting Room Name:','','vibe-zoom') ;?> </td><td><input class="bbb_create_m_field required" type="text" name="meetingName" value="" size="20"></td></tr>
                        <tr><td><?php echo _x('Meeting time:','','vibe-zoom') ;?> </td><td><input type="text" class="datepicker bbb_create_m_field required" name="m_date" id="m_date" value="" size="30" /><input type="text" class="timepicker bbb_create_m_field required" name="m_time" id="m_time" value="" size="30" autocomplete="OFF"/></td></tr>

                        <tr><td><?php echo _x('Timezone:','','vibe-zoom') ;?> </td>
                        
                        <td>
                        <select name="timezone" class="bbb_create_m_field select2 chozen"> 
                        <?php 

                        
                        foreach ($this->vibe_zoom_get_timezone_options() as $key => $value) {
                            echo '<option value="'.$key.'" >'.$value.'</option>';
                        }
                        
                        ?>
                        </select>
                        </td>
                        </tr>


                        <tr><td><?php echo _x('Meeting duration:','','vibe-zoom') ;?> </td><td> 
                            <input class="bbb_create_m_field required" type="number" name="mduration">
                            <select class="bbb_create_m_field required" name="mdurationparam">
                                <?php 
                                foreach ( $options as $option )
                                    echo '<option' . selected( esc_attr( $meta ), $option['value'], false ) . ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
                                ?>
                            </select>

                        </td></tr>
                        <tr><td><?php echo _x('Restrictions:','','vibe-zoom') ;?> </td><td>
                        
                        <select class="bbb_create_m_field" name="mrestriction" id="mrestriction">
                            <?php
                            foreach ( $this->restrictions as $key =>  $option )
                                echo '<option value="' . $key . '">' . $option . '</option>';
                            ?>
                        </select>
                        <div class="mrcourses_div">
                            <select name="mrcourses" style="width: 50%"  class="mrcourses selectcoursecpt bbb_create_m_field" data-cpt="course" data-placeholder="<?php _x('Select Courses','','vibe-zoom'); ?>">
                                
                            </select>
                        </div>
                        <div class="mrgroups_div">
                            <select name="mrgroups" style="width: 50%"  class="selectgroup mrgroups selectgroupscpt bbb_create_m_field" data-cpt="groups" data-placeholder="<?php _x('Select Groups','','vibe-zoom'); ?>">
                                
                            </select>
                        </div>
                        <div class="mrusers_div">
                            <select name="mrusers"  style="width: 100%;" class="selectusers_bbb bbb_create_m_field" data-placeholder="<?php echo __('Enter Student Usernames/Emails, separated by comma','vibe-zoom')?>" multiple>
                            </select>
                        </div>
                        </td></tr>
                        <tr><td><?php echo _x('Password: ','','vibe-zoom') ;?> </td><td><input class="bbb_create_m_field" type="text" name="moderatorPW" value="" size="20"></td></tr>
                        <tr><td><?php echo _x('Join meeting before host start the meeting. Only for scheduled or recurring meetings.','','vibe-zoom') ;?></td><td><input type="checkbox" class="bbb_create_m_field required" name="join_before_host" value="True" /></td></tr>
                        <tr><td><?php echo _x('Start video when host join meeting.','','vibe-zoom') ;?></td><td> <input class="bbb_create_m_field" type="checkbox" name="host_video" value="1" /></td></tr>


                        <tr><td><?php echo _x('Start video when participants join meeting.','','vibe-zoom') ;?></td><td> <input class="bbb_create_m_field" type="checkbox" name="participant_video" value="1" /></td></tr>

                        <tr><td><?php echo _x('Mute Participants upon entry.','','vibe-zoom') ;?></td><td> <input class="bbb_create_m_field" type="checkbox" name="mute_upon_entry" value="1" /></td></tr>


                        <tr><td><?php echo _x('Recording settings.','','vibe-zoom') ;?></td><td> 
                        <select class="bbb_create_m_field" name="option_auto_recording">
                            <option value="none" selected="selected">
                                <?php echo _x('No Recordings','','vibe-zoom')?>                </option>
                            <option value="local">
                                 <?php echo _x('Local','','vibe-zoom')?>               </option>
                            <option value="cloud">
                                  <?php echo _x('Cloud','','vibe-zoom')?>              </option>
                        </select>
                        </td></tr>



                        <tr><td><?php echo _x('Enable reminders:  ','','vibe-zoom') ;?><br>
                           <span>(<?php echo _x('It will send email reminder to each student before the meeting.So please choose carefully','','vibe-zoom') ;?>)<span> 
                        </td><td><input class="bbb_create_m_field" type="checkbox" name="mdr" value="True" /></td></tr>
                        <tr class="reminder_duration"><td><?php echo _x('Time before reminders will be sent:','','vibe-zoom') ;?> </td><td> 
                            <input class="bbb_create_m_field" type="number" name="mdrduration">
                            <select class="bbb_create_m_field" name="mdrdurationparam">
                                <?php 
                                foreach ( $options as $option )
                                    echo '<option' . selected( esc_attr( $meta ), $option['value'], false ) . ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
                                ?>
                            </select>

                        </td></tr>

                        <tr class="submit"><td><input type="submit" name="SubmitCreate" class="button-primary" value="<?php echo _x('Add Meeting ','','vibe-zoom') ;?>" /></td></tr>

                    </tbody>
                </table>
            </form>
            <div class="bbb_create_meeting_message"></div>
            <?php
        }

        function print_edit(){
            if(!is_user_logged_in())
                return;
            $user_id =get_current_user_id();
            $this->print_js();
            $options = array(
                array('value' =>'1','label'=>__('Seconds','vibe-zoom')),
                array('value' =>'60','label'=>__('Minutes','vibe-zoom')),
                array('value' =>'3600','label'=>__('Hours','vibe-zoom')),
                array('value' =>'86400','label'=>__('Days','vibe-zoom')),
                array('value' =>'604800','label'=>__('Weeks','vibe-zoom')),
                array('value' =>'2592000','label'=>__('Months','vibe-zoom')),
            );
            if(!empty($_GET['meeting'])){
                $meeting = $this->get_meeting($_GET['meeting']);
                global $wpdb;
                $meetingid = $_GET['meeting'];
                $table= $wpdb->prefix.'bigbluebutton';
                if(is_numeric($_GET['meeting'])){

                    $meeting_post = array();
                    $attendeepassword = get_post_meta($meetingid,'bbb-room-viewer-code',true);
                    $moderatorpw = get_post_meta($meetingid,'bbb-room-moderator-code',true);
                    $is_recorded = get_post_meta($meetingid,'bbb-room-recordable',true);
                    $waitfor_moderator = get_post_meta($meetingid,'bbb-room-wait-for-moderator',true);

                    $meeting_post = (object)array(
                        'attendeePW'=>$attendeepassword,
                        'moderatorPW'=>$moderatorpw,
                        'waitForModerator'=>(!empty($waitfor_moderator)?true:false),
                        'recorded'=>(!empty($is_recorded)?true:false),
                    );

                    $meeting['post'] = $meeting_post;  
                }
               
            }
            ?>
            <form id="Vibe_Zoom_create_form" name="form1" method="post" action="">
                <input type="hidden" class="wplm_bbb_meeting_id" value="<?php echo $meeting['id'];?>">
                 <input type="hidden" id="Vibe_Zoom_edit_meeting_security" value="<?php echo  wp_create_nonce('edit_meeting'.$user_id.$meeting['id'])?>">
                <table class="form-table" style="    width: 90%;">
                    <tbody>

                        <tr><td><?php echo _x('Meeting Host:','','vibe-zoom') ;?> </td>
                        
                            <td>
                                <select name="userId" class="bbb_create_m_field">
                                <?php
                                if(!empty($this->video_conferencing_zoom_api_get_user_transients())){
                                    foreach ($this->video_conferencing_zoom_api_get_user_transients() as $key => $value) {
                                        echo '<option value="'.$key.'" ' . selected( esc_attr( $meeting['userId'] ), $key , false ) . '>'.$value.'</option>';
                                    }
                                }
                                ?>
                                </select>
                            </td>
                        </tr>

                        <tr><td><?php echo _x('Meeting Room Name:','','vibe-zoom') ;?> </td><td><input class="bbb_create_m_field required" type="text" required name="meetingName" value="<?php echo (!empty($meeting['name'])?esc_attr($meeting['name']):'') ?>" size="20"></td></tr>
                        <tr><td><?php echo _x('Meeting time:','','vibe-zoom') ;?> </td><td><input type="text" class="datepicker bbb_create_m_field required" name="m_date" id="m_date" value="<?php echo (!empty($meeting['start_date'])?esc_attr($meeting['start_date']):'') ?>" size="30" /><input type="text" class="timepicker bbb_create_m_field required" name="m_time" id="m_time" value="<?php echo (!empty($meeting['start_time'])?esc_attr($meeting['start_time']):'') ?>" size="30" autocomplete="OFF"/></td></tr>

                        <tr><td><?php echo _x('Timezone:','','vibe-zoom') ;?> </td>
                        
                        <td>
                        <select name="timezone" class="bbb_create_m_field select2 chozen"> 
                        <?php 

                        
                        foreach ($this->vibe_zoom_get_timezone_options() as $key => $value) {
                            echo '<option value="'.$key.'" ' . selected( esc_attr( $meeting['timezone'] ), $key , false ) . '>'.$value.'</option>';
                        }
                        
                        ?>
                        </select>
                        </td>
                        </tr>



                        <tr><td><?php echo _x('Meeting duration:','','vibe-zoom') ;?> </td><td> 
                            <input class="bbb_create_m_field required" type="number" name="mduration" value="<?php echo (!empty($meeting['duration']['duration'])?esc_attr($meeting['duration']['duration']):'') ?>">
                            <select class="bbb_create_m_field required" name="mdurationparam">
                                <?php 
                                foreach ( $options as $option )
                                    echo '<option' . selected( esc_attr( $meeting['duration']['parameter'] ), $option['value'], false ) . ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
                                ?>
                            </select>

                        </td></tr>
                        <tr><td><?php echo _x('Restrictions:','','vibe-zoom') ;?> </td><td>
                        
                        <select class="bbb_create_m_field" name="mrestriction" id="mrestriction">
                            <?php
                            foreach ( $this->restrictions as $key =>  $option )
                                echo '<option ' . selected( esc_attr( $meeting['restrictions']['scope'] ), $key , false ) . ' value="' . $key . '">' . $option . '</option>';
                            $show_course_select =0;
                            $show_students_select =0;
                            if($meeting['restrictions']['scope'] == 'course_students' && !empty($meeting['restrictions']['data'][0]))
                            {
                                $show_course_select = 1; 
                            }elseif($meeting['restrictions']['scope'] == 'selected_users' && !empty($meeting['restrictions']['data'])){
                                $show_students_select = 1; 
                            }elseif($meeting['restrictions']['scope'] == 'group' && !empty($meeting['restrictions']['data'])){
                                $show_goups_select = 1; 
                            }
                            ?>
                        </select>
                        <div class="mrcourses_div <?php echo (!empty($show_course_select)?'show':'')?>">
                            <select  name="mrcourses" style="width: 50%"  class="mrcourses selectcoursecpt bbb_create_m_field" data-cpt="course" data-placeholder="<?php _x('Select Courses','','vibe-zoom'); ?>">
                            <?php 
                            if(!empty($show_course_select) &&  !empty($meeting['restrictions']['data'][0])){
                                echo '<option value="'.esc_attr($meeting['restrictions']['data'][0]).'" selected="selected">'.get_the_title($meeting['restrictions']['data'][0]).'</option>';
                            }
                            ?>
                            </select>
                        </div>
                        <div class="mrgroups_div <?php echo (!empty($show_goups_select)?'show':'')?>"">
                            <select name="mrgroups" style="width: 50%"  class="selectgroup mrgroups selectgroupscpt bbb_create_m_field" data-cpt="groups" data-placeholder="<?php _x('Select Groups','','vibe-zoom'); ?>">
                            <?php 
                            if(!empty($show_goups_select) &&  !empty($meeting['restrictions']['data'][0])){
                                if(function_exists('groups_get_group')){
                                    $group = groups_get_group(esc_attr($meeting['restrictions']['data'][0]));
                                }
                                echo '<option value="'.esc_attr($meeting['restrictions']['data'][0]).'" selected="selected">'.(!empty($group)?$group->name:'').'</option>';
                            }
                            ?>   
                            </select>
                        </div>

                        <div class="mrusers_div <?php echo (!empty($show_students_select)?'show':'')?>">
                            <select name="mrusers"  style="width: 100%;" class="selectusers_bbb bbb_create_m_field" data-placeholder="<?php echo __('Enter Student Usernames/Emails, separated by comma','vibe-zoom')?>" multiple>
                            <?php
                            if(!empty($show_students_select) &&  !empty($meeting['restrictions']['data'])){
                                foreach ($meeting['restrictions']['data'] as $userid) {
                                   $user =  get_user_by('id',$userid);
                                   echo '<option value="'.$userid.'" selected="selected">'.bp_core_fetch_avatar(array('item_id' => $userid, 'type' => 'thumb', 'width' => 32, 'height' => 32)).''.bp_core_get_user_displayname($userid).'</option>';
                                }
                            }
                            ?>

                            </select>
                        </div>
                        </td></tr>

                        
                        <tr><td><?php echo _x('Moderator Password: ','','vibe-zoom') ;?> </td><td><input class="bbb_create_m_field" type="text" name="moderatorPW" value="<?php echo (!empty($meeting['password'])?esc_attr($meeting['password']):'') ?>" size="20"></td></tr>


                        <tr><td><?php echo _x('Join meeting before host start the meeting. Only for scheduled or recurring meetings.','','vibe-zoom') ;?></td><td><input type="checkbox" class="bbb_create_m_field required" name="join_before_host" value="1" <?php echo (!empty($meeting['join_before_host'])?'checked="checked"':'') ?>
                        /></td></tr>

                        <tr><td><?php echo _x('Start video when host join meeting.','','vibe-zoom') ;?></td><td> <input class="bbb_create_m_field" type="checkbox" name="host_video" value="1"  <?php echo (!empty($meeting['option_host_video'])?'checked="checked"':'') ?>/></td></tr>


                        <tr><td><?php echo _x('Start video when participants join meeting.','','vibe-zoom') ;?></td><td> <input class="bbb_create_m_field" type="checkbox" name="participant_video" value="1" <?php echo (!empty($meeting['option_participants_video'])?'checked="checked"':'') ?>/></td></tr>

                        <tr><td><?php echo _x('Mute Participants upon entry.','','vibe-zoom') ;?></td><td> <input class="bbb_create_m_field" type="checkbox" name="mute_upon_entry" value="1" <?php echo (!empty($meeting['option_mute_participants'])?'checked="checked"':'') ?>/></td></tr>


                        <tr><td><?php echo _x('Recording settings.','','vibe-zoom') ;?></td><td> 
                        <select class="bbb_create_m_field" name="option_auto_recording">
                            <option value="none" <?php echo (!empty($meeting['option_auto_recording'] && $meeting['option_auto_recording']=='none')?'selected="selected"':'') ?>>
                                <?php echo _x('No Recordings','','vibe-zoom')?>                </option>
                            <option value="local" <?php echo (!empty($meeting['option_auto_recording'] && $meeting['option_auto_recording']=='local')?'selected="selected"':'') ?>>
                                 <?php echo _x('Local','','vibe-zoom')?>               </option>
                            <option value="cloud" <?php echo (!empty($meeting['option_auto_recording'] && $meeting['option_auto_recording']=='cloud')?'selected="selected"':'') ?>>
                                  <?php echo _x('Cloud','','vibe-zoom')?>              </option>
                        </select>
                        </td></tr>

                        <tr><td><?php echo _x('Enable reminders:  ','','vibe-zoom') ;?> <br>
                           <span>(<?php echo _x('It will send email reminder to each student before the meeting.So please choose carefully','','vibe-zoom') ;?>)<span> 
                        </td><td><input class="bbb_create_m_field" type="checkbox" name="mdr" value="True" <?php echo (!empty($meeting['reminder']['duration'])?'checked="checked"':'') ?>/></td></tr>
                        <tr class="reminder_duration <?php echo (!empty($meeting['reminder']['duration'])?'show':'')?>"><td><?php echo _x('Time before reminders will be sent:','','vibe-zoom') ;?> </td><td> 
                            <input class="bbb_create_m_field" type="number" name="mdrduration" value="<?php echo (!empty($meeting['reminder']['duration'])?esc_attr($meeting['reminder']['duration']):'') ?>">
                            <select  class="bbb_create_m_field" name="mdrdurationparam" >
                                <?php 
                                foreach ( $options as $option )
                                    echo '<option' . selected( esc_attr( $meeting['reminder']['parameter'] ), $option['value'], false ) . ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
                                ?>
                            </select>

                        </td></tr>

                        <tr class="submit"><td><input type="submit" name="SubmitCreate" class="button-primary" value="<?php echo _x('Edit Meeting ','','vibe-zoom') ;?>" /></td></tr>

                    </tbody>
                </table>
            </form>
            <div class="bbb_create_meeting_message"></div>
            <?php
        }

        function get_meeting($meeting_id=null){
            if(empty($this->Vibe_Zoom_meetings) || !is_array($this->Vibe_Zoom_meetings))
                return array();
            return apply_filters('wplms_get_bbb_meeting',$this->Vibe_Zoom_meetings[$meeting_id]);
        }

        function get_all_meetings(){
            if(empty($this->Vibe_Zoom_meetings) || !is_array($this->Vibe_Zoom_meetings))
                return array();
            return apply_filters('wplms_get_all_meetings',$this->Vibe_Zoom_meetings);
        }

        function all_meetings_content(){
            
            if(!is_user_logged_in() || (is_user_logged_in() && !current_user_can('edit_posts')))
                return;
            $this->print_js();
            $all_meetings = $this->get_all_meetings();
            if(!empty($all_meetings)){
                ?>

                <table class="wp-list-table widefat fixed striped"><thead><tr><th><?php echo _x('Meeting name','','vibe-zoom');?></th><th><?php echo _x('Meeting token','','vibe-zoom');?></th><th><?php echo _x('Start time','','vibe-zoom');?></th><th><?php echo _x('Privacy','','vibe-zoom');?></th><th><?php echo _x('Actions','','vibe-zoom');?></th></tr></thead><tbody>
                <?php
                foreach ($all_meetings as $key => $meeting) {
                    $time = _x('NA','','vibe-zoom');
                    if(!empty($meeting['start_date']) && !empty($meeting['start_time'])){

                        $time = strtotime($meeting['start_date'].' '.$meeting['start_time']);
                        $format = get_option( 'date_format' ).' '.get_option('time_format');;
                        $time = date_i18n($format ,$time);
                    }
                   
                    $restriction = _x('NA','','vibe-zoom');
                    if(!empty($meeting['restrictions'])){
                        if(!empty($meeting['restrictions']['scope']))
                        $restriction = $this->restrictions[$meeting['restrictions']['scope']];
                        if(!empty($meeting['restrictions']['data'])){
                            if($meeting['restrictions']['scope'] == 'course_students'){
                                foreach($meeting['restrictions']['data'] as $course){
                                  $restriction .= '<br><span>('.get_the_title($course).')</span>';  
                                }
                                
                            }elseif($meeting['restrictions']['scope'] == 'selected_users'){
                                $i=0;
                                $count = (count($meeting['restrictions']['data'])-2);
                                $restriction .= '<br><span>(';
                                foreach ($meeting['restrictions']['data'] as $value) {
                                    if($i < 2){
                                      $student = get_user_by('id',$value);
                                      $restriction .= $student->display_name;
                                    }
                                    if($i < 1){
                                        $restriction .= ',';
                                    }
                                    $i++;
                                }
                                if($count > 0){
                                    $restriction .= sprintf(_x(' and %s more','','vibe-zoom'),$count);
                                }
                                $restriction .= ')</span>';
                            }elseif($meeting['restrictions']['scope'] == 'group'){
                                foreach($meeting['restrictions']['data'] as $id){
                                    if(function_exists('groups_get_group')){
                                        $group = groups_get_group(esc_attr($id));
                                    }
                                  $restriction .= '<br><span>('.$group->name.')</span>';  
                                }
                            }
                        }
                    }

                    ?>
                        <tr>
                            <td><?php echo $meeting['name']?></td>
                            <td><?php echo $meeting['id']?></td>
                            <td><?php echo $time;?></td>
                            <td><?php echo $restriction;?></td>
                            <td>
                                <a href="javascript:void(0)" class="insert_meeting button" data-id="<?php echo $meeting['id']?>"><?php echo _x('Insert','','vibe-zoom');?></a>
                                <a href="<?php echo admin_url('media-upload.php?type=vibe_zoom_meetings&tab=edit&meeting='.$meeting['id']);?>" class="edit_meeting button" data-id="<?php echo $meeting['id']?>"><?php echo _x('Edit','','vibe-zoom');?></a>
                                <a href="javascript:void(0)" class="delete_meeting button"  data-id="<?php echo $meeting['id']?>"><?php echo _x('Delete','','vibe-zoom');?></a>
                            </td>
                        </tr>
                    <?php
                }
            }else{
                echo '<div class="message notice error"><p>'._x('No meetings found','','vibe-zoom').'</p></div>';
            }
            ?>

            </tbody></table>
            <?php
            
            
        }
        function media_create_meeting_content(){
           
            $this->print_tabs();
            echo '<div class="create_meeting">';
            echo '<h2>'.__('Add meeting','vibe-zoom').'</h2>';
            $this->print_create();
            echo "</div>";
        }

        function media_edit_meeting_content(){
           
            $this->print_tabs();
            echo '<div class="create_meeting">';
            echo '<h2>'.__('Edit meeting','vibe-zoom').'</h2>';
            $this->print_edit();
            echo "</div>";
        }

        function media_all_meetings_form(){
            $wplmsthis = Vibe_Zoom::init();
            $wplmsthis->print_tabs();
            echo '<div class="all_meetings">';
            echo '<h2>'.__('Meetings','vibe-zoom').'</h2>';
            $wplmsthis->all_meetings_content();
            echo '</div>';
        }


        function vibe_zoom_get_timezone_options() {
            $zones_array = array(
                "Pacific/Midway"                 => "(GMT-11:00) Midway Island, Samoa ",
                "Pacific/Pago_Pago"              => "(GMT-11:00) Pago Pago ",
                "Pacific/Honolulu"               => "(GMT-10:00) Hawaii ",
                "America/Anchorage"              => "(GMT-8:00) Alaska ",
                "America/Vancouver"              => "(GMT-7:00) Vancouver ",
                "America/Los_Angeles"            => "(GMT-7:00) Pacific Time (US and Canada) ",
                "America/Tijuana"                => "(GMT-7:00) Tijuana ",
                "America/Phoenix"                => "(GMT-7:00) Arizona ",
                "America/Edmonton"               => "(GMT-6:00) Edmonton ",
                "America/Denver"                 => "(GMT-6:00) Mountain Time (US and Canada) ",
                "America/Mazatlan"               => "(GMT-6:00) Mazatlan ",
                "America/Regina"                 => "(GMT-6:00) Saskatchewan ",
                "America/Guatemala"              => "(GMT-6:00) Guatemala ",
                "America/El_Salvador"            => "(GMT-6:00) El Salvador ",
                "America/Managua"                => "(GMT-6:00) Managua ",
                "America/Costa_Rica"             => "(GMT-6:00) Costa Rica ",
                "America/Tegucigalpa"            => "(GMT-6:00) Tegucigalpa ",
                "America/Winnipeg"               => "(GMT-5:00) Winnipeg ",
                "America/Chicago"                => "(GMT-5:00) Central Time (US and Canada) ",
                "America/Mexico_City"            => "(GMT-5:00) Mexico City ",
                "America/Panama"                 => "(GMT-5:00) Panama ",
                "America/Bogota"                 => "(GMT-5:00) Bogota ",
                "America/Lima"                   => "(GMT-5:00) Lima ",
                "America/Caracas"                => "(GMT-4:30) Caracas ",
                "America/Montreal"               => "(GMT-4:00) Montreal ",
                "America/New_York"               => "(GMT-4:00) Eastern Time (US and Canada) ",
                "America/Indianapolis"           => "(GMT-4:00) Indiana (East) ",
                "America/Puerto_Rico"            => "(GMT-4:00) Puerto Rico ",
                "America/Santiago"               => "(GMT-4:00) Santiago ",
                "America/Halifax"                => "(GMT-3:00) Halifax ",
                "America/Montevideo"             => "(GMT-3:00) Montevideo ",
                "America/Araguaina"              => "(GMT-3:00) Brasilia ",
                "America/Argentina/Buenos_Aires" => "(GMT-3:00) Buenos Aires, Georgetown ",
                "America/Sao_Paulo"              => "(GMT-3:00) Sao Paulo ",
                "Canada/Atlantic"                => "(GMT-3:00) Atlantic Time (Canada) ",
                "America/St_Johns"               => "(GMT-2:30) Newfoundland and Labrador ",
                "America/Godthab"                => "(GMT-2:00) Greenland ",
                "Atlantic/Cape_Verde"            => "(GMT-1:00) Cape Verde Islands ",
                "Atlantic/Azores"                => "(GMT+0:00) Azores ",
                "UTC"                            => "(GMT+0:00) Universal Time UTC ",
                "Etc/Greenwich"                  => "(GMT+0:00) Greenwich Mean Time ",
                "Atlantic/Reykjavik"             => "(GMT+0:00) Reykjavik ",
                "Africa/Nouakchott"              => "(GMT+0:00) Nouakchott ",
                "Europe/Dublin"                  => "(GMT+1:00) Dublin ",
                "Europe/London"                  => "(GMT+1:00) London ",
                "Europe/Lisbon"                  => "(GMT+1:00) Lisbon ",
                "Africa/Casablanca"              => "(GMT+1:00) Casablanca ",
                "Africa/Bangui"                  => "(GMT+1:00) West Central Africa ",
                "Africa/Algiers"                 => "(GMT+1:00) Algiers ",
                "Africa/Tunis"                   => "(GMT+1:00) Tunis ",
                "Europe/Belgrade"                => "(GMT+2:00) Belgrade, Bratislava, Ljubljana ",
                "CET"                            => "(GMT+2:00) Sarajevo, Skopje, Zagreb ",
                "Europe/Oslo"                    => "(GMT+2:00) Oslo ",
                "Europe/Copenhagen"              => "(GMT+2:00) Copenhagen ",
                "Europe/Brussels"                => "(GMT+2:00) Brussels ",
                "Europe/Berlin"                  => "(GMT+2:00) Amsterdam, Berlin, Rome, Stockholm, Vienna ",
                "Europe/Amsterdam"               => "(GMT+2:00) Amsterdam ",
                "Europe/Rome"                    => "(GMT+2:00) Rome ",
                "Europe/Stockholm"               => "(GMT+2:00) Stockholm ",
                "Europe/Vienna"                  => "(GMT+2:00) Vienna ",
                "Europe/Luxembourg"              => "(GMT+2:00) Luxembourg ",
                "Europe/Paris"                   => "(GMT+2:00) Paris ",
                "Europe/Zurich"                  => "(GMT+2:00) Zurich ",
                "Europe/Madrid"                  => "(GMT+2:00) Madrid ",
                "Africa/Harare"                  => "(GMT+2:00) Harare, Pretoria ",
                "Europe/Warsaw"                  => "(GMT+2:00) Warsaw ",
                "Europe/Prague"                  => "(GMT+2:00) Prague Bratislava ",
                "Europe/Budapest"                => "(GMT+2:00) Budapest ",
                "Africa/Tripoli"                 => "(GMT+2:00) Tripoli ",
                "Africa/Cairo"                   => "(GMT+2:00) Cairo ",
                "Africa/Johannesburg"            => "(GMT+2:00) Johannesburg ",
                "Europe/Helsinki"                => "(GMT+3:00) Helsinki ",
                "Africa/Nairobi"                 => "(GMT+3:00) Nairobi ",
                "Europe/Sofia"                   => "(GMT+3:00) Sofia ",
                "Europe/Istanbul"                => "(GMT+3:00) Istanbul ",
                "Europe/Athens"                  => "(GMT+3:00) Athens ",
                "Europe/Bucharest"               => "(GMT+3:00) Bucharest ",
                "Asia/Nicosia"                   => "(GMT+3:00) Nicosia ",
                "Asia/Beirut"                    => "(GMT+3:00) Beirut ",
                "Asia/Damascus"                  => "(GMT+3:00) Damascus ",
                "Asia/Jerusalem"                 => "(GMT+3:00) Jerusalem ",
                "Asia/Amman"                     => "(GMT+3:00) Amman ",
                "Europe/Moscow"                  => "(GMT+3:00) Moscow ",
                "Asia/Baghdad"                   => "(GMT+3:00) Baghdad ",
                "Asia/Kuwait"                    => "(GMT+3:00) Kuwait ",
                "Asia/Riyadh"                    => "(GMT+3:00) Riyadh ",
                "Asia/Bahrain"                   => "(GMT+3:00) Bahrain ",
                "Asia/Qatar"                     => "(GMT+3:00) Qatar ",
                "Asia/Aden"                      => "(GMT+3:00) Aden ",
                "Africa/Khartoum"                => "(GMT+3:00) Khartoum ",
                "Africa/Djibouti"                => "(GMT+3:00) Djibouti ",
                "Africa/Mogadishu"               => "(GMT+3:00) Mogadishu ",
                "Europe/Kiev"                    => "(GMT+3:00) Kiev ",
                "Asia/Dubai"                     => "(GMT+4:00) Dubai ",
                "Asia/Muscat"                    => "(GMT+4:00) Muscat ",
                "Asia/Tehran"                    => "(GMT+4:30) Tehran ",
                "Asia/Kabul"                     => "(GMT+4:30) Kabul ",
                "Asia/Baku"                      => "(GMT+5:00) Baku, Tbilisi, Yerevan ",
                "Asia/Yekaterinburg"             => "(GMT+5:00) Yekaterinburg ",
                "Asia/Tashkent"                  => "(GMT+5:00) Islamabad, Karachi, Tashkent ",
                "Asia/Calcutta"                  => "(GMT+5:30) India ",
                "Asia/Kolkata"                   => "(GMT+5:30) Mumbai, Kolkata, New Delhi ",
                "Asia/Kathmandu"                 => "(GMT+5:45) Kathmandu ",
                "Asia/Novosibirsk"               => "(GMT+6:00) Novosibirsk ",
                "Asia/Almaty"                    => "(GMT+6:00) Almaty ",
                "Asia/Dacca"                     => "(GMT+6:00) Dacca ",
                "Asia/Dhaka"                     => "(GMT+6:00) Astana, Dhaka ",
                "Asia/Krasnoyarsk"               => "(GMT+7:00) Krasnoyarsk ",
                "Asia/Bangkok"                   => "(GMT+7:00) Bangkok ",
                "Asia/Saigon"                    => "(GMT+7:00) Vietnam ",
                "Asia/Jakarta"                   => "(GMT+7:00) Jakarta ",
                "Asia/Irkutsk"                   => "(GMT+8:00) Irkutsk, Ulaanbaatar ",
                "Asia/Shanghai"                  => "(GMT+8:00) Beijing, Shanghai ",
                "Asia/Hong_Kong"                 => "(GMT+8:00) Hong Kong ",
                "Asia/Taipei"                    => "(GMT+8:00) Taipei ",
                "Asia/Kuala_Lumpur"              => "(GMT+8:00) Kuala Lumpur ",
                "Asia/Singapore"                 => "(GMT+8:00) Singapore ",
                "Australia/Perth"                => "(GMT+8:00) Perth ",
                "Asia/Yakutsk"                   => "(GMT+9:00) Yakutsk ",
                "Asia/Seoul"                     => "(GMT+9:00) Seoul ",
                "Asia/Tokyo"                     => "(GMT+9:00) Osaka, Sapporo, Tokyo ",
                "Australia/Darwin"               => "(GMT+9:30) Darwin ",
                "Australia/Adelaide"             => "(GMT+9:30) Adelaide ",
                "Asia/Vladivostok"               => "(GMT+10:00) Vladivostok ",
                "Pacific/Port_Moresby"           => "(GMT+10:00) Guam, Port Moresby ",
                "Australia/Brisbane"             => "(GMT+10:00) Brisbane ",
                "Australia/Sydney"               => "(GMT+10:00) Canberra, Melbourne, Sydney ",
                "Australia/Hobart"               => "(GMT+10:00) Hobart ",
                "Asia/Magadan"                   => "(GMT+10:00) Magadan ",
                "SST"                            => "(GMT+11:00) Solomon Islands ",
                "Pacific/Noumea"                 => "(GMT+11:00) New Caledonia ",
                "Asia/Kamchatka"                 => "(GMT+12:00) Kamchatka ",
                "Pacific/Fiji"                   => "(GMT+12:00) Fiji Islands, Marshall Islands ",
                "Pacific/Auckland"               => "(GMT+12:00) Auckland, Wellington"
            );

            return apply_filters( 'vibe_zoom_api_timezone_list', $zones_array );
        }

        

    }//class ends here/
}
