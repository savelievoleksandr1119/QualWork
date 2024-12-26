<?php
/**
 * API\
 *
 * @class       Vibe_Projects_Widgets_API
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Vibe_Projects_Widgets_API{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Projects_Widgets_API();
        return self::$instance;
    }

	private function __construct(){

		add_action('rest_api_init',array($this,'register_api_endpoints'));
	}


	function register_api_endpoints(){
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/get/myitems', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'myitems' ),
                'permission_callback'       => array( $this, 'get_widgets_permissions' ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/get/cardschartdata', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'cardschartdata' ),
                'permission_callback'       => array( $this, 'get_widgets_permissions' ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/get/mycardschartdata', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'mycardschartdata' ),
                'permission_callback'       => array( $this, 'get_widgets_permissions' ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/get/upcomingtasks', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'upcomingtasks' ),
                'permission_callback'       => array( $this, 'get_widgets_permissions' ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/get/memberinfo', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'memberinfo' ),
                'permission_callback'       => array( $this, 'get_widgets_permissions' ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/get/incompletetasks', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'incompletetasks' ),
                'permission_callback'       => array( $this, 'get_widgets_permissions' ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/get/projectsbystatus', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'projectsbystatus' ),
                'permission_callback'       => array( $this, 'get_widgets_permissions' ),
            ),
        ));
        

        
	}


    function get_widgets_permissions($request){
        $body =json_decode($request->get_body(),true);
         if(!empty($body['token'])){
            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
            if(!empty($this->user)){
                return true;
            }
        }
       return false;
    }


    function myitems($request){
        $post = json_decode($request->get_body(),true);
        $milestones=[];
        $project_count = 0;$cards_count = 0;$completed_count = 0;
        $milestones_count = 0;$milestones_completed_count = 0;

        $projects = get_user_meta($this->user->id,'vibe_project',false);
        if(empty($projects)){
            $projects=[];
        }
        $projects = array_unique($projects);
        $project_count = count($projects);

        $cards = get_user_meta($this->user->id,'vibe_project_card_member',false);
        $cards = array_unique($cards);
        $cards_count = count($cards);

        if(!empty($cards)){
            foreach ($cards as $key => $card) {
                $milestone = get_post_meta($card,'vibe_project_milestone',true);
                $completed = get_post_meta($card,'vibe_card_complete',true);
                if(!empty($completed)){
                    $completed_count++;
                }
                if(!empty($milestone)){
                    $milestones_count++;
                    if(!empty($completed)){
                        $milestones_completed_count++;
                    }
                }
            }
        }

        $return  = array(
            'project_count'=>$project_count,
            'cards_count'=>$cards_count,
            'cards_completed_count'=>$completed_count,
            'milestones_count'=>$milestones_count,
            'milestones_completed_count'=>$milestones_completed_count,
        );
        return new WP_REST_Response(array('status'=>true,'data'=>$return),200);
    }

    function cardschartdata($request){
        $post = json_decode($request->get_body(),true);
        return $this->get_chart_data($post);
    }

    function mycardschartdata($request){
        $post = json_decode($request->get_body(),true);
        return $this->get_chart_data($post,$this->user->id);   
    }

    function get_chart_data($post,$user_id=NULL){
        global $wpdb,$bp;
        $data = ['completed'=>[0],'total'=>[0]];
        $cards_count = 0;$completed_count = 0;
        $milestones_count = 0;$milestones_completed_count = 0;
        if(!empty($post['args']) && !empty($post['args']['start_date']) && !empty($post['args']['end_date'])){
            $start_date = date('Y-m-d H:i:s',(intval($post['args']['start_date'])/1000)) ;
            $end_date = date('Y-m-d H:i:s',(intval($post['args']['end_date'])/1000)) ;
            if(!empty($user_id)){
                $results = $wpdb->get_results("SELECT * FROM {$bp->activity->table_name} WHERE type='card_member_added' AND secondary_item_id={$user_id} AND date_recorded BETWEEN '{$start_date}' AND '{$end_date}'");
            }else{
               
                $results = $wpdb->get_results("SELECT * FROM {$bp->activity->table_name} WHERE type='card_member_added' AND date_recorded BETWEEN '{$start_date}' AND '{$end_date}'");
            }
            if(!empty($results)){
                foreach ($results as $key => $result) {
                    if(!empty($result->item_id)){
                        $cards_count++;
                        $completed = get_post_meta($result->item_id,'vibe_card_complete',true);
                   
                        $milestone = get_post_meta($result->item_id,'vibe_project_milestone',true);
              
                        if(!empty($completed)){
                            $completed_count++;
                        }
                        if(!empty($milestone)){
                            $milestones_count++;
                            if(!empty($completed)){
                                $milestones_completed_count++;
                            }
                        }
                    }
                    
                }
            }
            $data['completed'] = [$completed_count,$milestones_completed_count];
            $data['total'] = [$cards_count,$milestones_count];
        }else{
            return new WP_REST_Response(array('status'=>0,'message'=>_x('Select dates','','vibe-projects')));
        } 


        return new  WP_REST_Response(array('status'=>1,'data'=>$data));

    }

    function upcomingtasks($request){
        $post = json_decode($request->get_body(),true);
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT p.ID as id,p.post_title as title,pm.meta_value as time FROM {$wpdb->posts} AS p LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID=pm.post_id LEFT JOIN {$wpdb->usermeta} AS um ON pm.post_id=um.meta_value WHERE um.meta_key = %s AND pm.meta_key = %s AND pm.meta_value > %d AND um.user_id = %d AND p.post_status ='publish' ORDER BY pm.meta_value ASC",'vibe_project_card_member','vibe_card_due_date',time(),$this->user->id ));
        
        if(!empty($results)){
            $tasks = [];
            foreach ($results as $key => $r) {
                $project = get_post_meta($r->id,'vibe_card_project',true);
                $r->project = get_the_title($project);
                $tasks[] = $r;
            }
            return new  WP_REST_Response(array('status'=>1,'data'=>$tasks));
        }
        return new  WP_REST_Response(array('status'=>0));
    }

    function memberinfo($request){
        $post = json_decode($request->get_body(),true);
        $value = null;
        if(!empty($post['stats'])){
            global $wpdb;
            switch($post['stats']){
                case 'total_tasks':
                    $value = $wpdb->get_var($wpdb->prepare("SELECT Count(DISTINCT p.ID) as count FROM {$wpdb->posts} AS p  LEFT JOIN {$wpdb->usermeta} AS um ON p.ID=um.meta_value WHERE um.meta_key = %s AND um.user_id = %d AND p.post_status = 'publish'",'vibe_project_card_member',$this->user->id ));
                break;
                case 'incomplete_tasks':
                    $total =  $wpdb->get_var($wpdb->prepare("SELECT Count(DISTINCT p.ID) as count FROM {$wpdb->posts} AS p  LEFT JOIN {$wpdb->usermeta} AS um ON p.ID=um.meta_value WHERE um.meta_key = %s AND um.user_id = %d AND p.post_status = 'publish'",'vibe_project_card_member',$this->user->id ));
                    if(empty($total)){
                        $total= 0;
                    }
                    $completed = $wpdb->get_var($wpdb->prepare("SELECT Count(DISTINCT p.ID ) as count FROM {$wpdb->posts} AS p LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID=pm.post_id LEFT JOIN {$wpdb->usermeta} AS um ON pm.post_id=um.meta_value WHERE um.meta_key = %s AND pm.meta_key = %s  AND um.user_id = %d AND p.post_status ='publish'",'vibe_project_card_member','vibe_card_complete',$this->user->id ));  
                    if(empty($completed)){
                        $completed= 0;
                    }
                    $value  = $total-$completed;
                break;
                case 'finished_tasks':
                    $value = $wpdb->get_var($wpdb->prepare("SELECT Count(DISTINCT p.ID ) as count FROM {$wpdb->posts} AS p LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID=pm.post_id LEFT JOIN {$wpdb->usermeta} AS um ON pm.post_id=um.meta_value WHERE um.meta_key = %s AND pm.meta_key = %s  AND um.user_id = %d AND p.post_status ='publish'",'vibe_project_card_member','vibe_card_complete',$this->user->id ));
                break;
                case 'projects':
                    $value = $wpdb->get_var($wpdb->prepare("SELECT Count(DISTINCT p.ID) as count FROM {$wpdb->posts} AS p  LEFT JOIN {$wpdb->usermeta} AS um ON p.ID=um.meta_value WHERE um.meta_key = %s AND um.user_id = %d AND p.post_status = 'publish'",'vibe_project',$this->user->id ));
                break;
            }
        }
        
        
        
        if($value!==null){
           
            return new  WP_REST_Response(array('status'=>1,'data'=>$value));
        }
        return new  WP_REST_Response(array('status'=>0));
    }

    function incompletetasks($request){
        $response = array('status'=>0,'message'=>_x('No data!','','vibe-projects'));
        $args = json_decode($request->get_body(),true);
        $user_id = $this->user->id;
        global $wpdb;
        $data = [];
    
        $uprojects = get_user_meta($this->user->id,'vibe_project',false);
        if(empty($uprojects)){
            $uprojects=[];
        }
        $projects = [];
        $uprojects = array_unique($uprojects);
        $p_string= '';
        if(!empty($uprojects)){
            $p_string = implode(',', $uprojects);
            $results = $wpdb->get_results($wpdb->prepare("SELECT p.ID, p.post_title, pm.post_id FROM {$wpdb->posts} AS p LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID=pm.meta_value  WHERE pm.meta_key = 'vibe_card_project' AND p.post_status='publish' AND p.ID IN ($p_string)"));
     
            if(!empty($results)){
                
                foreach ($results as $key => $result) {
                    if(empty($projects[$result->ID])){
                        $projects[$result->ID] = [
                            'name'=>$result->post_title,
                            'cards'=>[]
                        ];
                    }
                    if(!in_array($result->post_id,$projects[$result->ID]['cards']) && get_post_status($result->post_id)=='publish'){
                         $projects[$result->ID]['cards'][] = $result->post_id; 
                    }
                }
            }

            if(!empty($projects)){
                $data['data'] = [];
                $data['labels'] = [];
                foreach ($projects as $key => $project) {
                    if(!empty($project['cards'])){
                        $total = count($project['cards']);
                        $card_string = implode(',',$project['cards']);
                        $completed_cards = $wpdb->get_var($wpdb->prepare("SELECT Count(*) FROM {$wpdb->postmeta} WHERE post_id IN ($card_string) AND meta_key=%s",'vibe_card_complete'));
                        if(empty($completed_cards)){
                            $completed_cards= 0;
                        }
                        $incomplete_cards = $total - $completed_cards;
                        $data['data'][] = $incomplete_cards;
                        $data['labels'][] = $project['name'];
                    }
                }
                $response['status'] = 1;
                $response['data'] = $data;
                $response['message'] = '';
            }
        }
        
        return new WP_REST_Response( $response, 200 );
    }

    function projectsbystatus($request){
        $response = array('status'=>0,'message'=>_x('No data!','','vibe-projects'));
        $args = json_decode($request->get_body(),true);
        $user_id = $this->user->id;
        global $wpdb;
        $data = [];
            
        $results = $wpdb->get_results($wpdb->prepare("SELECT COUNT(ID) AS count,p.post_status FROM {$wpdb->posts} AS p LEFT JOIN {$wpdb->usermeta} AS um ON p.ID=um.meta_value WHERE um.meta_key='vibe_project' AND um.user_id = %d AND p.post_type='project' GROUP BY p.post_status ",$this->user->id));
        if(!empty($results)){
            $response['status'] = 1;
            $response['data'] = $results;
            $response['message'] = '';
        }
        return new WP_REST_Response( $response, 200 );

    }
}

Vibe_Projects_Widgets_API::init();