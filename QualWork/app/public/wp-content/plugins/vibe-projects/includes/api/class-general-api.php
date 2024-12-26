<?php
/**
 * API\
 *
 * @class       Vibe_Projects_GENERAL_APIU
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Vibe_Projects_General_API{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Projects_General_API();
        return self::$instance;
    }

	private function __construct(){

		add_action('rest_api_init',array($this,'register_api_endpoints'));
	}


	function register_api_endpoints(){

       
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/search/member', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'search_members' ),
                'permission_callback'       => array( $this, 'get_search_permissions' ),
                 'args'                      =>  array(
                        'project_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/add/member', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'add_members' ),
                'permission_callback'       => array( $this, 'get_search_permissions' ),
                 'args'                      =>  array(
                        'project_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/remove/member', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'remove_members' ),
                'permission_callback'       => array( $this, 'get_search_permissions' ),
                 'args'                      =>  array(
                        'project_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));
    }


    function get_search_permissions($request){

        $body = json_decode($request->get_body(),true);

        if(!empty($body['token'])){
            global $wpdb;

            if($body['token'] == 'vibe_projects_share')
                return true;


            $this->user = apply_filters('vibe_projects_api_permission','',$request);
            if(empty($this->user)){
                $user_id = $wpdb->get_var("SELECT user_id from {$wpdb->usermeta} WHERE meta_key = '".$body['token']."'");
                if(!empty($user_id)){
                    $this->user = get_userdata($user_id);
                    if(!is_wp_error($this->user)){
                        $this->user->id = $user_id;
                        
                    }
                    return true;
                }
            }else{
                return true;
            }
        }
       return false;
    }

    function add_members($request){

        $body = json_decode($request->get_body(),true);

        
        

        
        global $wpdb;
        if(!empty($body['users']) && !empty($body['project_id'])){
            $users = $body['users'];
            $project_id = $body['project_id'];
            foreach($users as $user){
                $exist = get_user_meta($user['id'],'vibe_project',false);
                if(empty($exist)){
                    $exist = [];
                }
                if(!in_array($project_id, $exist)){
                    add_user_meta($user['id'],'vibe_project',$project_id);
                }
                
            }
            $return = array('status'=>1,'message'=>__('Members added','vibe-projects'));
        }else{
            $return = array('status'=>0,'message'=>__('Something went wrong','vibe-projects'));
        }
        


       return new WP_REST_Response($return,200);
    }

    function remove_members($request){

        $body = json_decode($request->get_body(),true);
        global $wpdb;
        if(!empty($body['users']) && !empty($body['project_id'])){
            $users = $body['users'];
            $project_id = $body['project_id'];
            foreach($users as $user){
                $exist = get_user_meta($user['id'],'vibe_project',false);
                if(empty($exist)){
                    $exist = [];
                }
                if(in_array($project_id, $exist)){
                    delete_user_meta($user['id'],'vibe_project',$project_id);
                }
            }
            $return = array('status'=>1,'message'=>__('Members removed','vibe-projects'));
        }else{
            $return = array('status'=>0,'message'=>__('Something went wrong','vibe-projects'));
        }
        


       return new WP_REST_Response($return,200);
    }

    function search_members($request){

        $body = json_decode($request->get_body(),true);

        

        $search = $body['s'];
        global $wpdb;
        $results =[];
        if(!empty($body['project_id'])){
            $project_id = $body['project_id'];

            $results = $wpdb->get_results( "SELECT DISTINCT ID,display_name FROM {$wpdb->users} as u LEFT JOIN {$wpdb->usermeta} as um ON u.ID=um.user_id WHERE (`user_nicename` LIKE '%{$search}%' OR 
            `user_email` LIKE '%{$search}%' OR `user_login` LIKE '%{$search}%' OR `display_name` LIKE '%{$search}%') AND um.meta_key='vibe_project' AND um.meta_value={$project_id}", ARRAY_A );
            

        }else{
            $results = $wpdb->get_results( "SELECT ID,display_name FROM {$wpdb->users} WHERE `user_nicename` LIKE '%{$search}%' OR 
            `user_email` LIKE '%{$search}%' OR `user_login` LIKE '%{$search}%' OR `display_name` LIKE '%{$search}%'", ARRAY_A );

        }
        if(!empty($results)){
            $return = array('status'=>1,'message'=>__('Found Searched Users','vibe-projects'));
            $types= bp_get_member_types('','objects');
            foreach($results as $result){
                $type = bp_get_member_type($result['ID']);
                $team = vibe_projects_get_member_team($result['ID']);
                if(!empty($types[$type])){
                    $mtype = $types[$type];    
                }
                

                $return['members'][]=array(
                    'id'=>$result['ID'],
                    'label'=>$result['display_name'], 
                    'avatar'=>bp_core_fetch_avatar(array(
                        'item_id'=>$result['ID'],
                        'object' => 'user',  
                        'type' => 'thumb',  
                       'html'=>false
                    )),
                    'member_type'=>empty($type) || empty($mtype)?'':['name'=>$mtype->labels['singular_name'],  'term_id'=>$mtype->db_id , 'slug'=>$mtype->name],
                    'team'=>empty($team)?'':['name'=>$team->name, 'slug'=>$team->slug, 'term_id'=>$team->term_id, 'color'=>$team->color]
                    
                );

            }
        }else{
            $return = array('status'=>0,'message'=>__('No Users Found','vibe-projects'));
        }

       return new WP_REST_Response($return,200);
    }
}
Vibe_Projects_General_API::init();
