<?php
/**
 * API\
 *
 * @class       Vibe_Projects_API
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Vibe_Projects_Reports_API{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Projects_Reports_API();
        return self::$instance;
    }

	private function __construct(){

		add_action('rest_api_init',array($this,'register_api_endpoints'));
	}


	function register_api_endpoints(){


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/reports/taskReport', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'task_report' ),
                'permission_callback'       => array( $this, 'get_permissions' ),
            ),
        ));

         register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/reports/memberReport', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'members_report' ),
                'permission_callback'       => array( $this, 'get_permissions' ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/memberReports', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'my_report' ),
                'permission_callback'       => array( $this, 'get_permissions' ),
            ),
        ));
         
        
    }

    function get_permissions($request){

        $body = json_decode($request->get_body(),true);

        if(!empty($body['token'])){
            global $wpdb;

            $this->user = apply_filters('vibe_projects_api_permission','',$request);
            if(!empty($this->user)){
                return true;
            }
        }
       return false;

    }

    function my_report($request){
        $args = json_decode($request->get_body(),true);

        $data = apply_filters('vibe_projects_my_reports',[
            [
                'title'=> __('Burndown','vibe-projects'),
                'class'=>'full',
                'type'=>'timeline',
                'data'=> $this->get_burn_down($this->user->id,$args['type'])
            ],
            [
                'title'=> __('Tasks by Status','vibe-projects'),
                'class'=>'',
                'type'=>'doughnut',
                'data'=> $this->get_cards_stats($this->user->id,$args['type'])
            ],
            [
                'title'=> __('Tasks by progress','vibe-projects'),
                'class'=>'',
                'type'=>'doughnut',
                'data'=> $this->get_cards_progress($this->user->id,$args['type'])
            ],
            [
                'title'=> __('Timeliness','vibe-projects'),
                'class'=>'',
                'type'=>'bar',
                'data'=> $this->get_cards_time($this->user->id,$args['type'])
            ]
        ],$this->user->id,$args['type']);

        return new WP_REST_Response(array('status'=>1,'data'=>$data),200);
    }

    function task_report($request){
        $args = json_decode($request->get_body(),true);

        $data = [];
        $data['burndown'] = $this->get_burn_down($args['id'],$args['type']);
        $data['status'] = $this->get_cards_stats($args['id'],$args['type']);
        $data['progress'] = $this->get_cards_progress($args['id'],$args['type']);
        $data['time'] = $this->get_cards_time($args['id'],$args['type']);

        return new WP_REST_Response(array('status'=>1,'data'=>$data),200);
    }



    function get_burn_down($id,$type){

        global $wpdb,$bp;
        if($type == 'project'){
            $data = $wpdb->get_results($wpdb->prepare("
                SELECT  count(DISTINCT(item_id)) as count, type, DATE(date_recorded) as date_recorded
                FROM {$bp->activity->table_name}
                WHERE component = %s 
                AND ( type = %s OR type = %s)
                AND item_id = %d 
                GROUP BY DATE(date_recorded), type"
                ,'vibe_projects','card_created','card_completed',$id),ARRAY_A);
        }

        if($type == 'member'){
            $data = $wpdb->get_results($wpdb->prepare("
                SELECT  count(*) as count, type, DATE(date_recorded) as date_recorded
                FROM {$bp->activity->table_name}
                WHERE component = %s 
                AND ( type = %s OR type = %s)
                AND user_id = %d 
                GROUP BY DATE(date_recorded), type"
                ,'vibe_projects','card_created','card_completed',$id),ARRAY_A);
        }
        

        
        return $data;
    }

    function get_cards_stats($id,$type){
        global $wpdb,$bp;
        if($type == 'project'){

           $results = $wpdb->get_results($wpdb->prepare("SELECT post_status as status,count(post_id) as count
            FROM {$wpdb->postmeta} as pm 
            LEFT JOIN {$wpdb->posts} as p ON p.ID = pm.post_id 
            WHERE p.post_type='card' AND pm.meta_key = 'vibe_card_project' AND pm.meta_value = %d
            GROUP BY p.post_status
            LIMIT 0,999",$id));

            return $results;
        }

        if($type == 'member'){

           $results = $wpdb->get_results($wpdb->prepare("
            SELECT count(ID) as count, p.post_status as status
            FROM {$wpdb->usermeta} as um 
            LEFT JOIN {$wpdb->posts} as p ON p.ID = um.meta_value 
            WHERE p.post_type='card' AND um.meta_key = 'vibe_project_card_member' AND um.user_id = %d
            GROUP BY p.post_status
            LIMIT 0,999",$id),ARRAY_A);
            
            $statuses = vibe_projects_get_statuses('card');
           
            $data = [];
            $labels = [];
            $bg=[];
            if(!empty($results)){
                // wp_list_pluck('count',$results)
                foreach($statuses as $status){
                    foreach($results as $result){
                        if($result['status'] == $status['value']){
                            $data[]=$result['count'];
                            $labels[]=$status['label'];
                            $bg[]=$status['color'];
                        }
                    }
                }
                
            }
            
            $return =[
                'labels' => $labels,
                'data'=>$data,
                'bg'=> $bg
            ];

            return $return;
        }
    }

    function get_cards_progress($id,$type){
        global $wpdb,$bp;
        $completed_cards=[];
        if($type == 'project'){
            $result = $wpdb->get_results($wpdb->prepare("
                SELECT post_id
                FROM {$wpdb->postmeta}
                WHERE meta_key = 'vibe_card_project' 
                AND meta_value = %d"
                ,$id),ARRAY_A);
            $completed_cards = vibe_projects_get_completed_cards($id);
            $card_ids = wp_list_pluck('post_id',$result);
        }

        if($type == 'member'){
            

            $card_ids = get_user_meta($id,'vibe_project_card_member',false);

            if(!empty($card_ids)){
                foreach($card_ids as $card_id){
                    $completed = get_post_meta($card_id,'vibe_card_complete',true);
                    if(!empty($completed)){
                        $completed_cards[]=$card_id;
                    }
                }
            }

        }

        $return =[
            'labels' => [ __('Not started','vibe-projects'),'0 - 25%','25 - 50%','50 - 75%','75 - 100%',__('Complete','vibe-projects')],
            'data'=>[0,0,0,0,0,0]
        ];


        $return['data'][5]=count($completed_cards);
        if(!empty($card_ids)){
            foreach($card_ids as $card_id){
                if(!in_array($card_id,$completed_cards)){
                    $progress = vibe_projects_get_card_progress($card_id);    
                    
                    if(intval($progress) == 0){
                        $return['data'][0]++;    
                    }
                    if(intval($progress) > 0 && intval($progress) < 25){
                        $return['data'][1]++;    
                    }
                    if(intval($progress) > 25 && intval($progress) < 50){
                        $return['data'][2]++;    
                    }
                    if(intval($progress) > 50 && intval($progress) < 75){
                        $return['data'][3]++;    
                    }
                    if(intval($progress) > 75){
                        $return['data'][4]++;    
                    }
                }
            }
        }
        return $return;
    }

    function get_cards_time($id,$type){
        global $wpdb,$bp;

        $card_ids=[];
        if($type == 'project'){

            $result = $wpdb->get_results($wpdb->prepare("
                SELECT post_id
                FROM {$wpdb->postmeta}
                WHERE meta_key = 'vibe_card_project' 
                AND m1.meta_value = %d"
                ,$id),ARRAY_A);

            $card_ids = wp_list_pluck($result,'post_id');   

            $completed_cards = $wpdb->get_results($wpdb->prepare("SELECT secondary_item_id as card_id,date_recorded FROM {$bp->activity->table_name} WHERE component = 'vibe_projects' AND item_id = %d AND secondary_item_id IN (".implode(',',$card_ids).") AND type = 'card_completed",$id),ARRAY_A); 
        }

        if($type == 'member'){

            $card_ids = get_user_meta($id,'vibe_project_card_member',false);
            
             $completed_cards = $wpdb->get_results($wpdb->prepare("SELECT secondary_item_id as card_id,date_recorded FROM {$bp->activity->table_name} WHERE component = 'vibe_projects' AND secondary_item_id IN (".implode(',',$card_ids).") AND type = %s",'card_completed'),ARRAY_A);

        }

        $ontime=$delayed=$untracked=0;
        if(!empty($card_ids)){

          
            
           

          

            $ccards = [];
            if(!empty($completed_cards)){
                foreach($completed_cards as $card){
                    $ccards[$card['card_id']] = strtotime($card['date_recorded']);
                }    
            }
            

           
            foreach($card_ids as $card_id){

                $date = get_post_meta($card_id,'vibe_card_due_date',true);

                if(!empty($date)){
                    if( !empty($ccards) && in_array($card_id,array_keys($ccards))){
                        if($date > $ccards[$card_id]){
                            $ontime++;
                        }else{
                            $delayed++;
                        }
                    }else{
                        if($date > time()){
                            $ontime++;
                        }else{
                            $delayed++;
                        }
                    }
                }else{
                    $untracked++;
                }   
            }
        }

        $return =[
            'labels' => [ __('On Time','vibe-projects'),__('OverDue','vibe-projects'),__('Untracked','vibe-projects')],
            'title'=>__('Timeliness','vibe-projects'),
            'data'=>[$ontime,$delayed,$untracked]
        ];
        return $return;
    }

    function get_cards_milestone($id,$type){
        global $wpdb,$bp;
        if($type == 'project'){
        }
    }

    function members_report($request){
         $args = json_decode($request->get_body(),true);
        $data= [];
        $project_id = esc_attr($args['id']);
         global $wpdb,$bp;
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT um.user_id as member_id,um.meta_value as card_id
            FROM {$wpdb->usermeta} as um LEFT JOIN {$wpdb->postmeta} as pm
            ON um.meta_value = pm.post_id
            WHERE um.meta_key = %s AND pm.meta_key = %s AND pm.meta_value = %d
            LIMIT 0,999",'vibe_project_card_member','vibe_card_project',$project_id),ARRAY_A);

        $member_data = [];
        if(!empty($results)){
            foreach($results as $result){
                if(empty($member_data[$result['member_id']])){$member_data[$result['member_id']]=[];}
                $member_data[$result['member_id']][]=$result['card_id'];
            }
        }

        if(!empty($member_data)){
            foreach($member_data as $member_id=>$cards){
                $data[]=$this->get_member_report($member_id,$cards,$project_id);
            }
        }

        
        return new WP_REST_Response(array('status'=>1,'data'=>$data),200);
    }

    function get_member_report($member_id,$cards,$project_id){
        
        global $wpdb,$bp;
        $member=[
            'name'=>bp_core_get_user_displayname($member_id),
            'avatar'=>bp_core_fetch_avatar(array(
                'item_id'=>$member_id,
                'object' => 'user',  
                'type' => 'thumb',  
               'html'=>false
            )),
            'total_cards'=>count($cards)
        ];

        if(!empty($cards)){           
            $status_breakup = $wpdb->get_results("SELECT count(post_status) as count,post_status FROM {$wpdb->posts} WHERE ID IN (".implode(',',$cards).") GROUP BY post_status");
            /* ===== */
            $member['status']=$status_breakup;
            /* ===== */

            $completed_cards=[];
            foreach($cards as $card_id){
                $completed_cards[] = get_post_meta($card_id,'vibe_card_complete',true);
            }
            $return =[
                'labels' => [ __('Not Done','vibe-projects'),__('Complete','vibe-projects')],
                'data'=>[(count($cards) - count($completed_cards)),count($completed_cards)]
            ];

            /* ===== */
            $member['progress'] = $return;
            /* ===== */

            $ontime=$delayed=$untracked=0;
            $completed_cards = $wpdb->get_results($wpdb->prepare("
                SELECT DISTINCT secondary_item_id as card_id,date_recorded 
                FROM {$bp->activity->table_name} 
                WHERE component = 'vibe_projects' 
                AND item_id = %d 
                AND secondary_item_id IN (".implode(',',$cards).") 
                AND type = 'card_completed'",
                $project_id),ARRAY_A);

            $ccards = [];
            if(!empty($completed_cards)){
                foreach($completed_cards as $card){
                    $ccards[$card['card_id']] = strtotime($card['date_recorded']);
                }    
            }
                

               
            foreach($cards as $card_id){

                $date = get_post_meta($card_id,'vibe_card_due_date',true);

                if(!empty($date)){
                    if( !empty($ccards) && in_array($card_id,array_keys($ccards))){
                        if($date > $ccards[$card_id]){
                            $ontime++;
                        }else{
                            $delayed++;
                        }
                    }else{
                        if($date > time()){
                            $ontime++;
                        }else{
                            $delayed++;
                        }
                    }
                }else{
                    $untracked++;
                }   
            }
            $return =[
                'title'=>__('Timeliness','vibe-projects'),
                'labels' => [ __('On Time','vibe-projects'),__('OverDue','vibe-projects'),__('Untracked','vibe-projects')],
                'data'=>[$ontime,$delayed,$untracked]
            ];
            /* ===== */
            $member['time'] = $return;
            /* ===== */

        }   

        return $member;
    }

}

Vibe_Projects_Reports_API::init();