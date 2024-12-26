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


class Vibe_Projects_API{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Projects_API();
        return self::$instance;
    }

	private function __construct(){

		add_action('rest_api_init',array($this,'register_api_endpoints'));
	}


	function register_api_endpoints(){


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/favprojects/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'favprojects' ),
                'permission_callback'       => array( $this, 'get_projects_permissions' ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/addfavproject/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'addfavproject' ),
                'permission_callback'       => array( $this, 'get_projects_permissions' ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/addNotice/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'add_notice' ),
                'permission_callback'       => array( $this, 'get_projects_permissions' ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/deleteNotice/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'delete_notice' ),
                'permission_callback'       => array( $this, 'get_projects_permissions' ),
            ),
        ));

		register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/projects/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_projects' ),
                'permission_callback'       => array( $this, 'get_projects_permissions' ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/projectList', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_projects_list' ),
                'permission_callback'       => array( $this, 'get_projects_permissions' ),
            ),
        ));
         //api to trigger while editing project
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/taxonomy/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_project_taxonomy' ),
                'permission_callback'       => array( $this, 'get_projects_permissions' ),
            ),
        ));


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/tabs', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_project_tabs' ),
                'permission_callback'       => array( $this, 'get_projects_permissions' ),
            ),
        ));

        //api to get project fields, triggers while editing the project
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/getFields', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_project_fields' ),
                'permission_callback'       => array( $this, 'get_projects_permissions' ),
            ),
        ));

        //api to fetch the author of project
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/member/(?P<id>\d+)', array(
             array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_project_member' ),
                'permission_callback' => array( $this, 'get_projects_members_permissions' ),
                'args'                =>  array(
                    'id'                        =>  array(
                        'validate_callback'     =>  function( $param, $request, $key ) {
                                                    return is_numeric( $param );
                                                }
                    ),
                ),
            )
        ));

        //api to fetch all the members connected in that particular project
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/members/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_project_members' ),
                'permission_callback'       => array( $this, 'get_projects_permissions' ),
                'args'                      =>  array(
                        'project_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/updateMembers/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'update_project_members' ),
                'permission_callback'       => array( $this, 'get_projects_permissions' ),
            ),
        ));


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/newproject', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'create_new_project' ),
                'permission_callback'       => array( $this, 'get_projects_create_update_permissions' ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/fullProject/view/(?P<project_id>\d+)', array(
            array(
                'methods'             =>  WP_REST_SERVER::READABLE,
                'callback'            =>  array( $this, 'get_full_project' ),
                'permission_callback'       => array( $this, 'get_projects_view_permissions' ),
                'args'                      =>  array(
                    'project_id'            =>  array(
                        'validate_callback'     =>  function( $param, $request, $key ) {
                                                    return is_numeric( $param );
                                                }
                    ),
                ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/delete/(?P<project_id>\d+)', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'delete_full_project' ),
                'permission_callback'       => array( $this, 'get_projects_permissions' ),
                'args'                      =>  array(
                    'project_id'            =>  array(
                        'validate_callback'     =>  function( $param, $request, $key ) {
                                                    return is_numeric( $param );
                                                }
                    ),
                ),
            ),
        ));


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/overview', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_project_overviews' ),
                'permission_callback'       => array( $this, 'get_projects_permissions' ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/sendMemberInvites', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'send_member_invites' ),
                'permission_callback'       => array( $this, 'get_projects_create_update_permissions' ),
            ),
        ));

        //email
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/(?P<project_id>\d+)/inviteUser', array(
            array(
                'methods'             =>  'GET',
                'callback'            =>  array( $this, 'registerInvitedUser' ),
                'permission_callback' => array( $this, 'cliendID_check' ),
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


    function get_projects_members_permissions($request){
        
        $body =json_decode($request->get_body(),true);

        if(!empty($body['token'])){
            global $wpdb;
            if($body['token'] == 'vibe_projects_share')
                return true;

            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
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


    function get_projects_permissions($request){

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
                        return true;
                    }
                }
            }else{
                return true;
            }
        }
       return false;

    }

    function get_projects_create_update_permissions($request){

        $body = json_decode($request->get_body(),true);
            
        if(!empty($body['token'])){
            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
            if(!empty($this->user) && apply_filters('vibe_projects_can_create_project',1,$this->user->caps)){
                return true;
            }
        }
        return false;
    }

    function get_projects_view_permissions($request){
        return true;
        $id = $request->get_param('project_id');
        if(is_numeric($id)){
            $view = get_post_meta($id,'vibe_projects_visibility',true);
        }
        if($view == 'public'){
            return true;
        }
        return false;
    }

    function favprojects($request){
        global $wpdb;
        $fav_projects = $wpdb->get_results($wpdb->prepare("SELECT p.ID as project_id,p.post_title as project_title FROM {$wpdb->posts} as p INNER JOIN {$wpdb->usermeta} as m on p.ID = m.meta_value AND m.meta_key = 'favourite_project' and m.user_id = %d",$this->user->id),ARRAY_A);
        if(!empty($fav_projects)){
            $projects=[];
            foreach($fav_projects as $project){
                $projects[]=[
                    'id'=>$project['project_id'],
                    'title'=>$project['project_title'],
                    'type'=>wp_get_object_terms($project['project_id'],'project-type'),
                    'avatar'=>get_the_post_thumbnail_url($project['project_id'],'full'),
                    'progress'=> vibe_projects_get_progress($project['project_id'])
                ];
            }
            return new WP_REST_Response(['status'=>1,'projects'=>$projects]);
        }

        return new WP_REST_Response(['status'=>0,'message'=>__('No favourite projects found','vibe-projects')]);
    }

    function addfavproject($request){
        global $wpdb;
        $args = json_decode($request->get_body(),true);
        if(!empty($args['project_id'])){
            global $wpdb;
            $check = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM {$wpdb->usermeta} WHERE meta_key = 'favourite_project' AND meta_value = %d",$args['project_id']));
            if(empty($check)){
                add_user_meta($this->user->id,'favourite_project',$args['project_id']);
            }else{
                delete_user_meta($this->user->id,'favourite_project',$args['project_id']);
            }
        }

        return new WP_REST_Response(['status'=>1,'message'=>__('Project marked in favourites.','vibe-projects')]);
    }

    function get_projects_list($request){
        $projects = [];
        global $wpdb;

        $project_ids = get_user_meta($this->user->id,'vibe_project',false);
        if(!empty($project_ids)){
            $projects = $wpdb->get_results("SELECT ID,post_title FROM {$wpdb->posts} WHERE ID IN (".implode(',',$project_ids).")",ARRAY_A);
        }
        return new WP_REST_Response(['status'=>1,'projects'=>$projects]);
    }

    function get_projects($request){

        global $wpdb;

        $body = json_decode($request->get_body(),true);
        if(!empty($body['args'])){
            $args=$body['args'];
        }
        
       
        $project_args = array(
            'post_type'    => 'project',
            'post_status'=>empty($args['status'])?'any':$args['status'],
            'posts_per_page' => 8,
            'paged'  => empty($args['page'])?1:$args['page'],
            's'=>empty($args['search'])?'':$args['search']
        );

        if(!empty($args['post__in'])){
            $project_args['post__in'] =$args['post__in'];
        }

        if(!empty($args['project_ids'])){
            $project_args['post__in'] =$args['project_ids'];
        }

        if(!empty($args['scope'])){
           
            if($args['scope'] === 'author'){
                $project_args['author'] = $this->user->id;     
            }
            if($args['scope'] == 'mine'){
                $projects = get_user_meta($this->user->id,'vibe_project',false);
               
                if(!empty($projects)){
                    if(!empty($args['post__in']) && in_array($args['post__in'],$projects)){
                        $key = array_search($args['post__in'], $projects);
                        unset($projects[$key]);                        
                        array_unshift($projects, $args['post__in']);
                        $project_args['post__in']=$projects;
                       $project_args['orderby'] = 'post__in';
                    }else{
                        $project_args['post__in']=$projects;    
                    }
                    
                }else{
                    $project_args['post__in']=[9999999999];
                }
            }
        }

        $query = new WP_Query(apply_filters('vibe_projects_get_projects',$project_args,$request));
        $data = array();

        $return = array(
            'status'=>0,
            'message'=>__('No Projects Found..!!','vibe-projects'),
            'data'=>$data,
            'total'=> $query->found_posts
        );
        $return['args'] = $project_args;
        if(!empty($query->have_posts())){
            $return['status'] = 1;
            $return['message'] = __('Projects Found..!!','vibe-projects'); 
            
            while($query->have_posts()){
                $query->the_post();
                $project = $this->prepare_project_object(get_the_ID(),'directory');
                $milestones = [];
                global $wpdb;
                $id = get_the_ID();
 
                $project['project_progress'] = vibe_projects_get_progress($id);
                $data[] = $project;

            }
            $return['data'] = $data;
        }
        wp_reset_postdata();
        $return = apply_filters('vibe_projects_get_all_projects',$return);
        return new WP_REST_Response($return,200);
    }

    function prepare_project_object($project_id,$context=null){
        $image = get_the_post_thumbnail_url($project_id);

        switch($context){
            case 'directory':
                global $post;
                $meta = [];


                $meta = [
                    [
                        'key'=>'member_count',
                        'icon'=>'vicon vicon-user',
                        'type'=>'number',
                        'value'=> vibe_projects_get_stats($project_id,'member_count'),
                        'desc'=> __('Members in project','vibe-project')
                    ],
                    [
                        'key'=>'milestone_count',
                        'icon'=>'vicon vicon-cup',
                        'type'=>'number',
                        'value'=> vibe_projects_get_stats($project_id,'milestone_count'),
                        'desc'=> __('Milestones in project','vibe-project')
                    ],
                    [
                        'key'=>'start_date',
                        'icon'=>'vicon vicon-timer',
                        'type'=>'date',
                        'value'=> vibe_projects_get_start_date($project_id),
                        'desc'=> __('Project Start','vibe-project')
                    ],
                    [
                        'key'=>'end_date',
                        'icon'=>'vicon vicon-alarm-clock',
                        'type'=>'date',
                        'value'=> vibe_projects_get_end_date($project_id),
                        'desc'=> __('Project Ends','vibe-project')
                    ],
                ];

                $fields = vibebp_get_setting('create_project_fields','vibe_projects','projects');
                if(!empty($fields)){
                    foreach($fields['key'] as $i => $key){
                        if(!empty($fields['preload'][$i])){
                            $v = get_post_meta($project_id,$key,true);
                            $meta[]=[
                                'key'=>$key,
                                'value'=>$v,
                                'type'=>$fields['type'][$i],
                                'label'=>$fields['label'][$i],
                                'desc'=>$fields['label'][$i],
                            ];
                        }
                    }
                }

                $return = array(
                    'id'          => $project_id,
                    'name'        => get_the_title($project_id),
                    'status'      => get_post_status($project_id),
                    'author'      => get_post_field('post_author',$project_id),
                    'type'        => get_the_terms($project_id,'project-type'),
                    'meta'        => $meta,
                    'project_progress'=>vibe_projects_get_progress($project_id),
                    'image'       => $image ? $image :plugins_url('../../assets/images/add_image.png', __FILE__),
                    'description' => apply_filters('vibebp_the_content',get_post_field('post_content', $project_id)),
                    'members'=> vibe_projects_get_project_members($project_id)
                );
            break;
            default:
                $return = array(
                    'id'    =>$project_id,
                    'status'       => get_post_status($project_id),
                    'image'=> $image ? $image : plugins_url('../../assets/images/add_image.png', __FILE__),
                    'name'  =>get_the_title($project_id),
                    'project_type'  => vibe_projects_get_project_type($project_id),
                    'description'=> apply_filters('vibebp_the_content',get_post_field('post_content', $project_id)),
                    'project_progress'=>vibe_projects_get_progress($project_id),
                    'members'=> vibe_projects_get_project_members($project_id)
                );
            break;
        }
       
        return $return;
    }


    function add_notice($request){
        $args = json_decode($request->get_body(),true);

        $notice= [
            'timestamp'=>time(),
            'member_type'=>esc_attr($args['member_type']),
            'team'=>esc_attr($args['team']),
            'content'=>$args['content'],
            'raw'=>wp_slash($args['raw']),
            'notify'=>$args['notify'],
            'user_id'=>$this->user->id
        ];

        if(vibe_projects_user_can($this->user->member_type,'add_project_notice')){
            $meta_id = add_post_meta(esc_attr($args['project_id']),'project_notice',$notice);
            $notice['meta_id']=$meta_id;

            do_action('vibe_projects_notice_added',esc_attr($args['project_id']),$notice);

            return new WP_REST_Response(['status'=>1,'notice'=>$notice,'message'=>__('Notice added','vibe-projects')]);    
        }
        
        return new WP_REST_Response(['status'=>0,'message'=>__('User does not have permissions to add a notice','vibe-projects')]);    
    }

    function delete_notice($request){
        $args = json_decode($request->get_body(),true);

        $notice= $args['notice'];

        if(vibe_projects_user_can($this->user->member_type,'add_project_notice')){
            delete_post_meta(esc_attr($args['project_id']),'project_notice',$notice);
            do_action('vibe_projects_notice_removed',$notice);
        }

        return new WP_REST_Response(['status'=>0,'message'=>__('User does not have permissions to add a notice','vibe-projects')]);    
    }

    function get_project_tabs($request){
        $args = json_decode($request->get_body(),true);

        $default_tabs = [
            ['value'=>'description','label'=>_x('Description','project tab','vibe-projects')],
            ['value'=>'members','label'=>_x('Members','project tab','vibe-projects')],
            ['value'=>'activity','label'=>_x('Activity','project tab','vibe-projects')]
        ];

        $metas = [
            'vibe_projects_boards'=>__('Tasks','vibe-projects'),
            'vibe_projects_gantt'=>__('Gantt','vibe-projects')
        ];


        foreach($metas as $key=>$label){
            $check = get_post_meta(esc_attr($args['project_id']),$key,true);
            if($check == 'S'){
              $default_tabs[]=['value'=>str_replace('vibe_projects_', '', $key),'label'=>$label];  
            }
        }

        $default_tabs[]=['value'=>'reports','label'=>_x('Reports','project tab','vibe-projects')];

        $tabs = apply_filters('vibe_project_tabs',$default_tabs,$args['project_id'],$this->user);

        $notices = get_post_meta(esc_attr($args['project_id']),'project_notice',false);

        if(!empty($notices)){
            foreach($notices as $i=>$notice){
                if(empty($notice['member_type']) || $notice['member_type'] != 'all' &&  $notice['member_type'] != $this->user->member_type){
                    //unset($notices[$i]);
                }
            }
        }
        return new WP_REST_Response(['status'=>1,'tabs'=>$tabs,'notices'=>array_values($notices),'d'=>$default_tabs]);
    }

    function get_project_fields($request){

        $args = json_decode($request->get_body(),true);
        $project_id = 0;
        if(!empty($args['project_id'])){
            $project_id = intval(esc_attr($args['project_id']));
        }
        return new WP_REST_Response(array('status'=>1,'fields'=>vibe_projects_get_project_fields($project_id)),200);
    }

    function get_project_member($request){
 
        $user_id = $request->get_param('id');
        $name = bp_core_get_user_displayname($user_id);
         
        $return = array();
        if(!empty($name)){
            $return = apply_filters('vibe_projects_get_member',array(
                'id'=>$user_id,
                'name'=>$name,
                'avatar'=>bp_core_fetch_avatar(array(
                        'item_id'=>$user_id,
                        'object' => 'user',  
                        'type' => 'thumb',  
                       'html'=>false
                     )
                )
            ),$user_id);
        }

        $status = 1;
        if(empty($name)){
            $status = 0;
        }
        //print_r('#####a');

        return new WP_REST_Response(array('status'=>$status,'member'=>$return),200);
    }

    function get_project_taxonomy($request){

        $args = json_decode($request->get_body(),true);
        if(empty($args) || empty($args['taxonomy'])){
            return new WP_REST_Response(array('status'=>1,'terms'=>[]),200);
        }

        $terms = get_terms( array(
            'taxonomy' => $args['taxonomy'],
            'hide_empty' => false,
        ) );

        $result = [];
        if(!empty($terms)){
            foreach($terms as $term){
                $result[] =['id'=>$term->term_id,'text'=>$term->name];
            }
            return new WP_REST_Response(array('status'=>1,'terms'=>$result),200);
        }

        return new WP_REST_Response(array('status'=>1,'terms'=>[]),200);
    }

    function get_project_members($request){
        
        $args = json_decode($request->get_body(),true);

        $project_args = apply_filters('vibe_projects_get_project_all_members',array(
            'type' => $args['type'],
            'search_terms' => empty($args['search'])?'':$args['search'],
            'meta_key' => 'vibe_project',
            'meta_value' => $args['project_id'],
            'per_page'=>12,
            'page' => $args['page'],
            'popular_extras'=> false
        ), $args);
        $result = bp_core_get_users($project_args);

        if(!empty($result) && $result['total'] > 0){
            $project_access_levels = get_post_meta($args['project_id'],'project_org',true);
            $types = bp_get_member_types('','objects');
            $all_types= $teams= [];
            foreach($result['users'] as $k=>$user){
                $type = bp_get_member_type($user->ID);
                if(!empty($type)){
                    $t = (Array)$types[$type];
                    $type = ['name'=>$t['labels']['singular_name'],'term_id'=>$t['db_id'],'slug'=>$type];
                    $all_types[$type['term_id']]=$type;
                }

                $team = vibe_projects_get_member_team($user->ID);
                
                $nteam=[];
                if(!empty($team->name)){
                    $nteam = [
                        'name'=>$team->name,
                        'slug'=>$team->slug,
                        'term_id'=>$team->term_id,
                        'color'=>$team->color
                    ];

                    $teams[$nteam['term_id']]=$nteam;    
                }
                
                $result['users'][$k]=[
                    'id'=>$user->ID,
                    'display_name'=>$user->display_name,
                    'avatar'=>get_avatar_url($user->ID, 32),
                    'member_type'=>$type,
                    'team'=>$nteam
                ];
            }

            $result['member_types']=array_values($all_types);
            $result['teams']=array_values($teams);

        }

        $status = 1;
        if(empty($result)){
            $status = 0;
        }
        
        return new WP_REST_Response(array('status'=>$status,'data'=>$result),200);
    
    }

    function update_project_members($request){
        $args = json_decode($request->get_body(),true);

        global $wpdb;
        if(!empty($args['project_id']) && is_numeric($args['project_id'])){
            $uids = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'vibe_project' AND meta_value = %d",$args['project_id']),ARRAY_A);    
        }
        

        $saved_member_ids=$member_ids=[];
        if(!empty($uids)){
            $saved_member_ids =   wp_list_pluck($uids,'user_id');    
        }
        if(!empty($args['members'])){
            $member_ids = wp_list_pluck($args['members'],'id');    
        }
        
        $add_members = array_diff($member_ids,$saved_member_ids);
        
        if(!empty($add_members)){
            foreach($add_members as $member_id){
                add_user_meta($member_id,'vibe_project',$args['project_id']);
                do_action('vibe_projects_member_added',$args['project_id'],$member_id,$this->user);
            }
        }
        $remove_members = array_diff($saved_member_ids,$member_ids);
        if(!empty($remove_members)){
            foreach($remove_members as $member_id){
                delete_user_meta($member_id,'vibe_project',$args['project_id']);
                do_action('vibe_projects_member_removed',$args['project_id'],$member_id,$this->user);
            }
        }
        return new WP_REST_Response(array('status'=>1,'message'=>__('Project members updated','vibe-projects')),200);
    }

    function create_new_project($request){

        $args = json_decode($request->get_body(),true);
      

        $project_args = array(
            'post_status' => 'publish',
            'post_author' => $this->user->id,
            'post_type' => 'project'
        );

        if(!empty($args['post_title'])){
            $project_args['post_title']=sanitize_text_field($args['post_title']);
        }
        if(!empty($args['post_status'])){
            $project_args['post_status']=sanitize_text_field($args['post_status']);
        }
        if(!empty($args['post_content'])){
            $project_args['post_content']=sanitize_text_field($args['post_content']);
        }
        $project_id =0;
        if(!empty($args['id'])){
            $project_args['ID'] = intval($args['id']);
        }

        $project_args = apply_filters('vibe_projects_create_edit_project_args',$project_args);
        if(!empty($project_args)){
            if(!empty($args['id'])){
                $project_id = wp_update_post($project_args);
            }else{
                $project_id = wp_insert_post($project_args);
                add_user_meta($this->user->id,'vibe_project',$project_id);
                $result = update_post_meta($project_id,'project_users',array('administrators'=>array($this->user->id)));
            }
        }
        

        if(is_numeric($project_id)){
            $return['project_id'] = $project_id;

            if(!empty($args['raw']))
                update_post_meta($project_id,'raw',wp_slash($args['raw']));

            if(!empty($args['project_type'])){

                if(is_numeric($args['project_type'])){
                    $projectTypeObject = get_term_by( 'id', absint( $args['project_type'] ), 'project-type' );
                    $projectTypeName = $projectTypeObject->name;
                }
                if( $args['project_type'] == 'new_project_type'){
                    wp_insert_term($args['new_type'],'project-type');
                }
                
                wp_set_object_terms( $project_id, $projectTypeName, 'project-type', false);
            }

            if(!empty($args['meta'])){
                foreach ($args['meta'] as $key => $meta) {
                    if($meta['meta_key']== '_thumbnail_id'){
                        $meta['meta_value'] = $meta['meta_value']['id'];
                    }
                   update_post_meta($project_id,$meta['meta_key'],$meta['meta_value']);
                }
            }
            if(!empty($args['taxonomy']) && count($args['taxonomy'])){
                $_cat_ids = array();
                foreach ($args['taxonomy'] as  $taxonomy) {
                    
                    if(empty($taxonomy['value']) && !empty($taxonomy['terms'])){
                        $taxonomy['value']   = $taxonomy['terms'];
                    }
                    if(!empty($taxonomy['value'])){
                        foreach($taxonomy['value'] as $k=>$cat_id){
                            if(!is_numeric($cat_id) && strpos($cat_id, 'new_') === 0){
                                $new_cat = explode('new_',$cat_id);
                                $cid = wp_insert_term($new_cat[1],$taxonomy['taxonomy']);
                                if(is_array($cid)){
                                    $taxonomy['value'][$k] = $cid['term_id'];
                                }else{
                                    unset($taxonomy['value'][$k]);
                                }
                            }
                        }
                        
                    }
                    wp_set_object_terms( $project_id, $taxonomy['value'], $taxonomy['taxonomy'] );
                }
            }

            $return = ['status'=>1,'data'=>$this->prepare_project_object($project_id,'directory')];
            do_action('vibe_projects_create_new_project',$project_id,$args,$this->user);
        }else{
                $return = ['status'=>0,'message'=>_x('Unable to create project','project creation','vibe-projects')];
        }

        
        return new WP_REST_Response($return,200);
    }

    function get_full_project($request){

        $project_id = $request->get_param('project_id');
        $author_id = get_post_field('post_author',$project_id);
        $status = 1;
        $return = array();
        $return = array(
            'id'        =>$project_id,
            'post_title'      =>get_the_title($project_id),
            'post_status'=>get_post_status($project_id),
            'date'      =>get_the_date($project_id),
            'author'    => $author_id,
            'avatar_url' => get_avatar_url($author_id, 150),
            'post_type'=>'project',
            'image'    => $image ? $image : plugins_url('../../assets/images/add_image.png', __FILE__),
            'description'=>get_the_content($project_id),
            'members'=> vibe_projects_get_project_members($project_id)
        );
        $context = array(
            'user'=>array('id'=>0,'display_name'=>'guest'),
            'token'=>'vibe_projects_share'
        );
        return new WP_REST_Response(array('status'=>$status,'fullProject'=>$return,'context'=>$context),200);
    }

    function delete_full_project($request){
        global $wpdb;

        $project_id = $request->get_param('project_id');

        $status = 0;
        
        $return = array();
        if(is_numeric($project_id) && vibe_projects_user_can($this->user->member_type,'delete_project')){
            $status = 1;
            //provision to delete all the data in project

            $board = $wpdb->get_results($wpdb->prepare("
              SELECT * FROM {$wpdb->posts} AS post
                WHERE   post.post_parent   = %d",$project_id));
            
            $return = wp_delete_post( $project_id, true );

            if($return){


                $boards = $wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->posts} AS post WHERE   post.post_parent = %d",$project_id));

                foreach ($boards as $key => $board) {
                    $lists = wp_get_object_terms($board->ID,'list',array(
                            'orderby'    => 'meta_value_num',
                            'meta_key'   => 'order',
                            'order'      => 'ASC',
                            'meta_query' => array(
                                'relation'=>'AND',
                                array(
                                    'key' => 'list_status',
                                    'value' => 'archived',
                                    'compare' => '!='
                                ),
                            )
                        )
                    );
                    if(!empty($lists) && !is_wp_error($lists)){
                        foreach($lists as $key=>$list){
                            update_term_meta( $list->term_id, 'list_status', 'archived');
                        }
                    }
                }

                $cards = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE   meta_key = 'vibe_card_project' AND meta_value = %d",$project_id));

                if(!empty($cards)){
                    foreach ($cards as $key => $card) {
                        wp_delete_post( $card['post_id'] );
                    }
                }
                            
                wp_trash_post( $project_id);
            }

        }

        return new WP_REST_Response(array('status'=>(empty($return)?0:1),'message'=>(empty($return)?_x('Project not deleted!','delete project api call','vibe-projects'):_x('Project deleted!','delete project api call','vibe-projects'))),200);
    }


    function get_cards_from_list($list_id,$details=null){
        $query = new WP_Query(array(
            'post_type'=>'card',
            'post_status'=>array('any','trash'),
            'posts_per_page'=>-1,
            'orderby' => 'menu_order', 
            'order' => 'ASC', 
            'tax_query'=>array(
                'relation'=>'AND',
                array(
                    'taxonomy'=>'list',
                    'field'=>'id',
                    'terms'=> array($list_id)
                )
            )
        ));
                
        $result = array();
        $status = 1;
        if(!empty($query->have_posts())){
            if($query->have_posts()){
                $status = 1;
                while($query->have_posts()){
                    $query->the_post();
                    global $post;

                    
                    $result[]= apply_filters('vibe_projects_get_cards_list', array( 
                        'card_id'=>get_the_ID(),
                        'completed'=>get_post_meta(get_the_ID(),'vibe_card_complete',true),
                        'milestone'=>get_post_meta(get_the_ID(),'vibe_project_milestone',true)
                        //'checklists'=>get_post_meta(get_the_ID(),'vibe_card_checklist',true) 
                    ));
                   
                }

            }
        }
        wp_reset_postdata();
        return $result;
    }


     function send_member_invites($request){

        $args = json_decode($request->get_body(),true);
        if(!empty($args['members'])){
            $blog_id = '';
            if(function_exists('get_current_blog_id')){
                $blog_id = get_current_blog_id();
            }
            set_transient('invitations_'.$args['project_id'],wp_list_pluck($args['members'],'email'),DAY_IN_SECONDS);
            foreach($args['members'] as $member){
                if(!email_exists(esc_attr($member['email']))){
                    bp_send_email( 'invite_project_members',esc_attr($member['email']), array(
                        'tokens' => array(
                            'user.name'=>$this->user->displayname,
                            'item.title'=>get_the_title($args['project_id']),
                            'member.team'=>$member['team'],
                            'member.type'=>$member['type'],
                            'registration.link'=>'<a href="'.get_rest_url($blog_id,VIBE_PROJECTS_API_NAMESPACE).'/'.$args['project_id'].'/inviteUser/?client_id='.vibebp_get_setting('client_id').'&email='.urlencode(esc_attr($member['email'])).'&name='.urlencode(esc_attr($member['name'])).'&member_type='.urlencode(esc_attr($member['type'])).'&team='.urlencode(esc_attr($member['team'])).'" target="_blank">'.__('Accept Invite','vibe-projects').'</a>'
                        ),
                    ) );
                }
            }
        }else{
            return new WP_REST_Response(array('status'=>0,'message'=>__('Unable to add users','vibe-projects')),200);
        }

        return new WP_REST_Response(array('status'=>1,'message'=>__('Invite emails sent to users','vibe-projects')),200);
    }


    function cliendID_check($request){
        $client_id = $request->get_param('client_id');
        if($client_id == vibebp_get_setting('client_id')){
            return true;
        }
        return false;
    }


    function registerInvitedUser($request){

        $project_id = esc_attr($request->get_param('project_id'));
        $email = esc_attr(urldecode($request->get_param('email')));
        $name = esc_attr(urldecode($request->get_param('name')));
        $member_type= esc_attr(urldecode($request->get_param('member_type')));
        $team = esc_attr(urldecode($request->get_param('team')));

        if(email_exists($email)){
            wp_die(__('User already registered','vibebp'));
        }

        $verify = get_transient('invitations_'.$project_id);
        
        if(empty($verify)){
            wp_die(__('Registration linked expired !','vibebp'));
        }

        if(!in_array($email,$verify)){
            wp_die(__('Unauthorised registration attempt!','vibebp'));
        }



        $password = wp_generate_password(8,false,false);
        $user_id = wp_insert_user([
            'user_email'=>$email,
            'user_login'=>esc_attr($name),
            'user_pass'=> $password
        ]);

        if(is_numeric($user_id)){

            $i = array_search($email, $verify);
            unset($verify[$i]);
            set_transient('invitations_'.$project_id,$verify,DAY_IN_SECONDS);

            bp_set_member_type($user_id,$member_type);
            vibe_projects_set_member_team( $user_id, $team );
            update_user_meta($user_id,'vibe_project',$project_id);
            bp_send_email( 'login_member_details',$email, array(
                'tokens' => array(
                    'user.email'=>$email,
                    'user.password'=>$password,
                )
            ) );
            $app = vibebp_get_setting('bp_single_page');
            if(!empty($app)){
                wp_set_auth_cookie($user_id);
                $link = get_permalink($app).'#component=projects&project='.$project_id;    
                wp_redirect($link);
                exit();
            }
            
        }

        if(is_wp_error($user_id)){
            wp_die($user_id->get_error_message());
        }
        
        
        wp_die(__('Something went wrong. Contact site administrator','vibe-projects'));
    }

    function get_project_overviews($request){
        $args = json_decode($request->get_body(),true);

       $project_id = intval(esc_attr($args['project_id']));

       $data = [
            'snapshot'=>[
                [
                    'key'=>'progress',
                    'label' => __('% Progress','vibe-projects'),
                    'value' => vibe_projects_get_progress($project_id),
                    'desc' => __('Project completed.','vibe-progress')
                ],
                [
                    'key'=>'time',
                    'label' => __('% Time','vibe-projects'),
                    'value' => vibe_projects_get_progress($project_id,'time'),
                    'desc' => __('Time remaining','vibe-progress')
                ],
                [
                    'key'=>'member_count',
                    'label' => __('# members','vibe-projects'),
                    'value' => vibe_projects_get_stats($project_id,'member_count'), 
                    'desc' => __('Member Count','vibe-progress')
                ],
                [
                    'key'=>'milestone_count',
                    'label' => __('# milestones','vibe-projects'),
                    'value' => vibe_projects_get_stats($project_id,'milestone_count'),
                    'desc' => __('Number of Milestones','vibe-progress')
                ],
                [
                    'key'=>'board_count',
                    'label' => __('# boards','vibe-projects'),
                    'value' => vibe_projects_get_stats($project_id,'board_count'),
                    'desc' => __('Number of Boards','vibe-progress')
                ],
                [
                    'key'=>'task_count',
                    'label' => __('# tasks','vibe-projects'),
                    'value' => vibe_projects_get_stats($project_id,'card_count'),
                    'desc' => __('Number of Tasks','vibe-progress')
                ]
            ],
            'active_member'=>[
                'label'=> __('Most active member','vibe-projects'),
                'member'=> vibe_projects_get_most_active_member($project_id)
            ],
            'active_card'=>[
                'label'=> __('Most active task','vibe-projects'),
                'card'=> vibe_projects_get_most_active_card($project_id)
            ],
            'milestone'=>[
                'label'=>__('Milestones','vibe-projects'),
                'data'=>$this->get_milestones($project_id)
            ]
       ];

       return new WP_REST_Response(array('status'=>1,'data'=>$data),200);
    }

    function get_milestones($project_id){
        $milestone_ids = vibe_projects_get_milestones($project_id);
        $milestone_report = [];
        if(!empty($milestone_ids)){
            foreach($milestone_ids as $milestone_id){
                $milestone_report[]=[
                    'id'=>$milestone_id,
                    'title'=>get_the_title($milestone_id),
                    'status'=>get_post_status($milestone_id),
                    'progress'=>vibe_projects_get_card_progress($milestone_id)
                ];
            }
        }
        return $milestone_report;
    }


}
Vibe_Projects_API::init();