<?php
/**
 * API\
 *
 * @class       Vibe_Cards_API
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Vibe_Cards_API{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Cards_API();
        return self::$instance;
    }

	private function __construct(){

		add_action('rest_api_init',array($this,'register_api_endpoints'));
	}


	function register_api_endpoints(){


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/newmilestone/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'create_new_milestone' ),
                'permission_callback'       => array( $this, 'get_cards_permissions' )
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/updateMilestone/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'update_milestone' ),
                'permission_callback'       => array( $this, 'get_cards_permissions' )
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/updateMilestoneOrder/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'update_milestone_order' ),
                'permission_callback'       => array( $this, 'get_cards_permissions' )
            ),
        ));
        

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/fetchmilestones/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'fetch_milestone' ),
                'permission_callback'       => array( $this, 'get_cards_permissions' )
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/deletemilestone/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'delete_milestone' ),
                'permission_callback'       => array( $this, 'get_cards_permissions' )
            ),
        ));

        
         

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/newcard/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'create_new_card' ),
                'permission_callback'       => array( $this, 'get_cards_permissions' ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/updateTask/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'update_card' ),
                'permission_callback'       => array( $this, 'get_cards_permissions' ),
            ),
        ));
        

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<card_id>\d+)/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_full_card' ),
                'permission_callback'       => array( $this, 'card_view_permissions' ),
                'args'                      =>  array(
                        'card_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<action>(addCardAttachment|deleteCardAttachment|archive|labels|checklist|duedate|attachments|complete|milestone|watch))/(?P<card_id>\d+)/action', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'card_actions' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
                'args'                      =>  array(
                        'card_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<card_id>\d+)/adddescription', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'save_card_description' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
                'args'                      =>  array(
                    'card_id'                        =>  array(
                        'validate_callback'     =>  function( $param, $request, $key ) {
                                                    return is_numeric( $param );
                                                }
                    ),
                ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/addmembertocard', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'addmembertocard' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/removemembercard', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'removemembercard' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/changeStatus', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'changeStatus' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/changeTitle', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'changeTitle' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
            ),
        ));


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<card_id>\d+)/create_label', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'create_new_label' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
                'args'                      =>  array(
                        'card_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<card_id>\d+)/delete_label', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'delete_label' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
                'args'                      =>  array(
                        'card_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<card_id>\d+)/edit_label', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'edit_label' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
                'args'                      =>  array(
                        'card_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));

        
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<card_id>\d+)/watchers', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_watchers' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
                'args'                      =>  array(
                        'card_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<card_id>\d+)/addchecklist', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'add_new_checklist' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
                'args'                      =>  array(
                        'card_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<card_id>\d+)/activity', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'fetch_card_activity' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
                'args'                      =>  array(
                        'card_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<card_id>\d+)/setduedate', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'set_due_date' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
                'args'                      =>  array(
                        'card_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<card_id>\d+)/saveAttachments', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'save_card_attachments' ),
                'permission_callback'       => array( $this, 'set_card_attachments_permissions_check' ),
                'args'                      =>  array(
                        'card_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<card_id>\d+)/setattachments', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'set_card_attachments' ),
                'permission_callback'       => array( $this, 'set_card_attachments_permissions_check' ),
                'args'                      =>  array(
                        'card_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<card_id>\d+)/all_lists', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'fetch_all_lists' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
                'args'                      =>  array(
                        'card_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<card_id>\d+)/movecard', array(
                array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'move_card_action' ),
                'permission_callback'       => array( $this, 'card_actions_permissions' ),
                    'args'                      =>  array(
                        'card_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/card/(?P<card_id>\d+)/commentactions', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'card_comment_actions' ),
                'permission_callback'       => array( $this, 'card_comment_actions_permissions' ),
                'args'                      =>  array(
                        'card_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/tasks', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'fetch_tasks' ),
                'permission_callback'       => array( $this, 'get_cards_permissions' ),
            ),
        ));

	}



    function card_comment_actions_permissions($request){
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



    function set_card_attachments_permissions_check($request){
        $body =json_decode(stripslashes($_POST['body']),true);
        if(!empty($body['token'])){
            global $wpdb;
            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
            if(empty($this->user)){
                return false;
            }else{
                return true;
            }
        }
       return false;
    }


    function card_view_permissions($request){
        $body =json_decode($request->get_body(),true);
         if(!empty($body['token'])){
            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
            if(!empty($this->user)){
                return true;
            }
        }
       return false;

    }

    function card_actions_permissions($request){
        $body =json_decode($request->get_body(),true);
         if(!empty($body['token'])){
            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
            if(!empty($this->user)){
                //chcek card owner or card assigned or site administrator
                return true;
            }
        }
       return false;

    }
 

    function get_cards_permissions($request){
        $body =json_decode($request->get_body(),true);
         if(!empty($body['token'])){
            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
            if(!empty($this->user)){
                return true;
            }
        }
       return false;
    }

    function get_label_key($label_id,$labels){
        if(!empty($labels)){
            foreach ($labels as $key => $label) {
                if(!empty($label['id']) && $label_id==$label['id'])
                    return $key;
            }
        }
        return -1;

    }


     function fetch_tasks($request){
        $body =json_decode($request->get_body(),true);

        $return =['status'=>0,'message'=>__('No tasks assigned to the user')];
        $args = $body['args'];
        $args['post_type']='card';
        $args['post_status'] = 'any';
        if(empty($args['s'])){unset($args['s']);}
        $cards = [];
        if($args['type'] == 'watching'){
            global $wpdb;
            $results = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value = %d",'watch_card',$this->user->id),ARRAY_A);
            if(!empty($results)){
                $cards = wp_list_pluck($results,'post_id');
            }

        }else{
            $cards = get_user_meta($this->user->id,'vibe_project_card_member',false);
        }
        
        if(empty($cards)){
            $cards = [99999];
        }

        $return = ['status'=>1];
        $return['tasks']=[];

        $args['meta_query']=['relation'=>'AND'];
        
        if(!empty($args['upcoming']) || (!empty($args['type']) && $args['type'] == 'mine')){
            $args['meta_query'][]=[
                'key'=>'vibe_card_due_date',
                'value'=>strtotime(date('Y-m-d', time()) . ' 00:00:00'),
                'compare'=>'>=',
                'type' => 'numeric',
            ];
            $args['orderby']='meta_value_num date';
            $args['meta_key']='vibe_card_due_date';
            $args['order'] = 'ASC';
        }
        
            
        if(empty($args['showCompleted'])){
            $args['meta_query'][]=
                [
                    'key'=>'vibe_card_complete',
                    'type' => 'NOT EXISTS',
                ];
        }

        if(!empty($args['project'])){
            
            foreach($cards as $k=>$card_id){
                $project_id = get_post_meta($card_id,'vibe_card_project',true);
                if(intval($args['project']) != $project_id){
                    unset($cards[$k]);
                }
            }
        }
          
       

        if(!empty($args['posts_not__in'])){
            foreach($cards as $i=>$card_id){
                if(in_array($card_id,$args['posts_not__in'])){
                    unset($cards[$i]);
                }
            }
            unset($args['posts_not__in']);
        }
        $args['post__in']=$cards;

        if(empty($args['posts_per_page'])){
            $args['posts_per_page']=20;    
        }
        
        if(!empty($args['page'])){
            $args['paged']=intval($args['page']);    
        }

        $query = new WP_Query($args);

        $return['args']=$args;
        $return['total'] = $query->found_posts;

        while($query->have_posts()){
            $query->the_post();

            unset($cards[get_the_ID()]);
            $obj = vibe_projects_get_task_object(get_the_ID());
            $pid = get_post_meta(get_the_ID(),'vibe_card_project',true);
            if(!empty($pid)){
                $obj['project']= ['id'=>$pid,'title'=>get_the_title($pid)];
            }                
            $return['tasks'][$obj['card_id']]=$obj;
        }
        wp_reset_postdata();

        if(!empty($cards)){
            $args = $body['args'];
            $args['post_type']='card';
            $args['post_status'] = 'any';
            $args['post__in']=$cards;

            $query = new WP_Query($args);
            $return['total'] += $query->found_posts;
            while($query->have_posts()){
                $query->the_post();
                
                $obj = vibe_projects_get_task_object(get_the_ID());
                $pid = get_post_meta(get_the_ID(),'vibe_card_project',true);
                if(!empty($pid)){
                    $obj['project']= ['id'=>$pid,'title'=>get_the_title($pid)];
                }                
                $return['tasks'][$obj['card_id']]=$obj;
            }
            wp_reset_postdata();
        }

         $return['tasks']=array_values($return['tasks']);
        
    


        return new WP_REST_Response($return,200);
    }

    function update_card($request){
        $post = json_decode($request->get_body(),true);

        switch(esc_attr($post['type'])){
            case 'dates':
                $dates = $post['task']['dates'];
                $card_id = $post['task']['card_id'];
                if(is_array($dates) && count($dates) > 1){
                    update_post_meta( $card_id,'vibe_card_start_date',$dates[0]);
                    update_post_meta( $card_id,'vibe_card_due_date',$dates[1]);
                }
            break;
            case 'progress':
                update_post_meta( $card_id,'vibe_card_progress',$post['task']['progress']);
            break;
        }
        return new WP_REST_Response(['status'=>1,'message'=>__('Card updated','vibe-projects')],200);
    }
    function create_new_label($request){
        $post = json_decode($request->get_body(),true);
        $card_id = $request->get_param('card_id');
        if(empty($post['board_id'])){
            return new WP_REST_Response(['status'=>0,'message'=>__('Missing board.','vibe-projects')]);
        }
        $board_id = $post['board_id'];
        $labels = get_post_meta($board_id,'vibe_board_labels',true);
        if(empty( $labels)){
             $labels= [];
        }
        if(!empty($post['id'])){
            $label_id = $post['id'];
            $label_key = $this->get_label_key($label_id,$labels);
            if($label_key > -1){
                $labels[$label_key] = array(
                    'id'=>$label_id,
                    'board_id'=>$board_id,
                    'label'=>$post['label'],
                    'color'=>$post['color']
                );
                update_post_meta($board_id,'vibe_board_labels',$labels);
                return new WP_REST_Response(array('status'=>1,'labels'=>$labels,'message'=>_x('Label edited!','','vibe-projects')),200);
            }else{
                return new WP_REST_Response(array('status'=>0,'message'=>_x('Label not found.','','vibe-projects')),200);
            }
            
        }else{
            $label_id = wp_generate_password( 10, false, false );
            $return = array(
                'id'=>$label_id,
                'board_id'=>$board_id,
                'label'=>$post['label'],
                'color'=>$post['color']
            );
            
            
            $labels[] = $return;
            update_post_meta($board_id,'vibe_board_labels',$labels);
            add_post_meta($card_id,'vibe_card_label',$label_id);

            do_action('vibe_projects_create_new_label',$return,$board_id);
            return new WP_REST_Response(array('status'=>1,'labels'=>$labels,'message'=>_x('Label created!','','vibe-projects')),200);
        }
        
    }

    function delete_label($request){
        $post = json_decode($request->get_body(),true);
        $card_id = $request->get_param('card_id');
        $board_id = $post['board_id'];

        $labels = get_post_meta($board_id,'vibe_board_labels',true);
        if(empty( $labels)){
             $labels= [];
        }
        if(!empty($post['id'])){
            $label_id = $post['id'];
            $label_key = $this->get_label_key($label_id,$labels);
            if($label_key > -1){
                unset($labels[$label_key]);
                delete_post_meta($card_id,'vibe_card_label',$label_id);
                $labels = array_values($labels);
                update_post_meta($board_id,'vibe_board_labels',$labels);
                return new WP_REST_Response(array('status'=>1,'labels'=>$labels,'message'=>_x('Label deleted!','','vibe-projects')),200);
            }
            
        }
        return new WP_REST_Response(array('status'=>0,'message'=>_x('Something went wrong','','vibe-projects')),200);
    }

    function edit_label($request){
        $post = json_decode($request->get_body(),true);
        $card_id = $request->get_param('card_id');
        $label = $post['label'];
        $board_id=$post['board_id'];
        $labels = get_post_meta($board_id,'vibe_board_labels',true);
        if(!empty($post['label']['id']) && !empty($labels)){
            $label_id = $post['label']['id'];
            $label_key = $this->get_label_key($label_id,$labels);
            if($label_key > -1){
                $labels[$label_key]=$label;
                $labels = array_values($labels);
                update_post_meta($board_id,'vibe_board_labels',$labels);
                return new WP_REST_Response(array('status'=>1,'message'=>_x('Label edited!','','vibe-projects')),200);
            }
        }
        return new WP_REST_Response(array('status'=>0,'message'=>_x('Something went wrong','','vibe-projects')),200);
    }

    function create_new_milestone($request){
        $args = json_decode($request->get_body(),true);

        if(!empty($args['id'])){


            $args['id'] = str_replace('milestone_','',$args['id']);
            if(get_post_type($args['id']) == 'card'){

                $card_id = wp_update_post(apply_filters('vibe_projects_update_card',array(
                    'ID'=>$args['id'],
                    'post_title'=>$args['title'],
                )));    
               
            }
            
        }else{
            $card_id = wp_insert_post(apply_filters('vibe_projects_add_card',array(
                'post_title'=>$args['title'],
                'post_content'=>'',
                'post_status'=>'publish',
                'post_type'=>'card',
                'menu_order'=>1,
                'post_author'=>$this->user->id,
            )));   
            do_action('vibe_projects_milestone_created',$card_id,$args['project_id'],$this->user->id);
        }
        
        if(!empty($args['dates'])){
            forEach($args['dates'] as $k=>$date){
                if(!is_numeric($date)){
                    $args['dates'][$k]=strtotime($date);
                }
            }
        }

        if($card_id){
            update_post_meta( $card_id,'vibe_project_milestone',$args['project_id']);
            update_post_meta( $card_id,'vibe_card_start_date',$args['dates'][0]);
            update_post_meta( $card_id,'vibe_card_due_date',$args['dates'][1]);

            if(!empty($args['dependencies'])){
               

                $dependencies = get_post_meta($card_id,'vibe_card_dependency',false);

                foreach($args['dependencies'] as $k=>$id){

                    $id = str_replace('milestone_','',$id);
                    $args['dependencies'][$k]=$id;
                    if(empty($dependencies) || !in_array($id,$dependencies)){
                        add_post_meta( $card_id,'vibe_card_dependency',$id);        
                    }
                }
                if(!empty($dependencies)){
                    foreach($dependencies as $id){
                        if(!in_array($id,$args['dependencies'])){
                            delete_post_meta($card_id,'vibe_card_dependency',$id);
                        }
                    }    
                }
                
            }

            update_post_meta( $card_id,'vibe_card_progress',intval($args['progress']));
            global $wpdb;
            $results = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value= %d",'vibe_project_card_member',get_the_ID()));
            if(!empty($results)){
                $members = wp_list_pluck('user_id',$results);
            }
            if(empty($members)){
                $members=[['id'=>$this->user->id,'img'=>bp_core_fetch_avatar(array(
                                'item_id' => $this->user->id,
                                'object'  => 'user',
                                'html'    => false
                            ))]];
            }

            if(!empty($args['dependencies'])){
                foreach($args['dependencies'] as $i=>$d){
                    if(is_numeric($id)){
                        $args['dependencies'][$i]='milestone_'.$d;
                    }
                }
            }

            return new WP_REST_Response(array('status'=>1,'card'=>[
                'id'=>'milestone_'.$card_id,
                'card_id'=>$card_id,
                'name'=>$args['title'],
                'start'=>date('Y-m-d',$args['dates'][0]),
                'end'=>date('Y-m-d',$args['dates'][1]),
                'members'=>$members,
                'progress'=>empty($args['progress'])?0:$args['progress'],
                'dependencies'=>implode(',',$args['dependencies'])
            ]),200);
        }

        return new WP_REST_Response(array('status'=>0,'message'=>is_wp_error($card_id)?$card_id->get_error_message():__('Unable to add milestone','vibeprojects')));
    }

    function update_milestone($request){
        $args = json_decode($request->get_body(),true);

        if(!empty($args['milestone'])){
            $card_id = str_replace('milestone_','',$args['milestone']['id']);
            switch($args['type']){
                case 'progress':
                    update_post_meta($card_id,'vibe_card_progress',$args['milestone']['progress']);
                break;
                case 'dates':
                    update_post_meta( $card_id,'vibe_card_start_date',$args['milestone']['dates'][0]);
                    update_post_meta( $card_id,'vibe_card_due_date',$args['milestone']['dates'][1]);
                break;
            }

            return new WP_REST_Response(array('status'=>1,'task'=>$args['milestone'],'message'=>__('Milestone updated','vibe-projects')));
        }

         return new WP_REST_Response(array('status'=>0,'message'=>is_wp_error($card_id)?$card_id->get_error_message():__('Unable to add milestone','vibeprojects')));
    }

    function update_milestone_order($request){
        $data = json_decode($request->get_body(),true);
        $args = $data['args'];
        if(!empty($args['start_card_id'])){
            wp_update_post(['ID'=>$args['start_card_id'],'menu_order'=>$args['start_card_order']]);
        }
        if(!empty($args['end_card_id'])){
            wp_update_post(['ID'=>$args['end_card_id'],'menu_order'=>$args['end_card_order']]);
        }

        return new WP_REST_Response(array('status'=>1,'message'=>__('Milestone order updated','vibe-projects'),'args'=>$args));
    }

    function delete_milestone($request){
        $args = json_decode($request->get_body(),true);

        $id = $args['milestone']['card_id'];
        if(is_numeric($id) && get_post_type($id) == 'card'){
            $project_id = get_post_meta($id,'vibe_project_milestone',true);
            if($project_id == $args['project_id']){
                if(wp_trash_post($id)){

                    do_action('vibe_projects_milestone_deleted',$args['milestone']['id'],$args['project_id'],$this->user->id);
                     return new WP_REST_Response(array('status'=>1,'message'=>__('Milestone removed','vibe-projects')));
                }    
            }
        }
        return new WP_REST_Response(array('status'=>0,'message'=>__('Unable to remove Milestone.','vibe-projects')));
    }

    function fetch_milestone($request){
        $args = json_decode($request->get_body(),true);

        $tasks=[];
        $query = new WP_Query(apply_filters('vibe_projects_card_query',[
            'post_type'=>'card',
            'posts_per_page'=>99,
            'orderby'=>'menu_order',
            'order'=>'ASC',
            'post_status'=>'any',
            'meta_query'=>[
                'relation'=>'AND',
                [
                    'key'=>'vibe_project_milestone',
                    'value'=>intval($args['project_id']),
                    'compare'=>'='
                ]
            ]
        ]));



        if($query->have_posts()){
            while($query->have_posts()){
                $query->the_post();
                $s = get_post_meta(get_the_ID(),'vibe_card_start_date',true);
                if(empty($s)){$s=time();}
                $e = get_post_meta(get_the_ID(),'vibe_card_due_date',true);
                if(empty($e)){$e=time()+86400;}

                global $wpdb;

                $results = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value= %d",'vibe_project_card_member',get_the_ID()));
                if(!empty($results)){
                    $members = wp_list_pluck('user_id',$results);
                }
                if(empty($members)){
                    $members=[get_the_author_meta('ID')];
                }
                $allmembers=[];
                foreach($members as $member){
                    $allmembers[]=[
                        'id'=>get_the_author_meta('ID'),
                        'img'=>bp_core_fetch_avatar(array(
                            'item_id'=>$member,
                            'object' => 'user',  
                            'type' => 'thumb',  
                           'html'=>false
                        ))
                    ];
                }
                $tasks[]=[
                    'id'=>'milestone_'.get_the_ID(),
                    'card_id'=>get_the_ID(),
                    'name'=>get_the_title(),
                    'start'=>date('Y-m-d',$s),
                    'end'=>date('Y-m-d',$e),
                    'members'=>$allmembers,
                    'progress'=>vibe_projects_get_card_progress(get_the_ID()),
                    'dependencies'=>get_post_meta(get_the_ID(),'vibe_card_dependency',false),
                    'children'=>$this->get_sub_tasks(get_The_ID())
                ];
            }
        }
        wp_reset_postdata();

        return new WP_REST_Response(array('status'=>1,'tasks'=>$tasks,'query'=>$query),200);
    }

    function get_sub_tasks($milestone_id){
        global $wpdb;
        $cards = $wpdb->get_results($wpdb->prepare("SELECT ID,post_title,post_status FROM {$wpdb->posts} WHERE post_type = 'card' && post_status != 'trash' AND post_parent = %d",$milestone_id),ARRAY_A);

        $children = [];
        if(!empty($cards)){
            foreach($cards as $card){

                $results = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value= %d",'vibe_project_card_member',$card['ID']));

                if(!empty($results)){
                    $members = wp_list_pluck('user_id',$results);
                }
                if(empty($members)){
                    $members=[get_the_author_meta('ID')];
                }
                $allmembers=[];
                foreach($members as $member){
                    $allmembers[]=[
                        'id'=>get_the_author_meta('ID'),
                        'img'=>bp_core_fetch_avatar(array(
                            'item_id'=>$member,
                            'object' => 'user',  
                            'type' => 'thumb',  
                           'html'=>false
                        ))
                    ];
                }
                $start = get_post_meta( $card['ID'], 'vibe_card_start_date', true );
                if(!empty($start)){
                    $start = date('Y-m-d',$start);
                }
                $end = get_post_meta( $card['ID'], 'vibe_card_due_date', true );
                if(!empty($end)){
                    $end = date('Y-m-d',$end);
                }
                $child=[
                    'id'=>$card['ID'],
                    'name'=>$card['post_title'],
                    'status'=>$card['post_status'],
                    'sub'=>true,
                    'members'=>$allmembers,
                    'progress'=>vibe_projects_get_card_progress($card['ID']),
                    'start'=> $start,
                    'end'=> $end
                ];
                $children[]=$child;     
            }
        }

        return $children;
        
    }


    function create_new_card($request){

        $args = json_decode($request->get_body(),true);

        if(!vibe_projects_user_can('create_new_card',$this->user->id)){
            return new WP_REST_Response(array('status'=>0,'message'=>__('User can not create new cards.','vibe-projects')),200);
        }

        if(!empty($args['list_id'])){
            $list_id = esc_attr($args['list_id']);
        }else if(!empty($args['list'])){
            $list_id = esc_attr($args['list']);
        }

        $status = 1;
        $return = array();

        $postargs = apply_filters('vibe_projects_add_card',array(
            'post_title'=>$args['title'],
            'post_content'=>'',
            'post_status'=>empty($args['post_status'])?'publish':$args['post_status'],
            'post_type'=>'card',
            'post_author'=>$this->user->id,
        ));

        if(!empty($args['id']) && is_numeric($args['id'])){
            $postargs['ID']=intval($args['id']);
            $card_id = wp_update_post($postargs);    
        }else if(!empty($args['card_id']) && is_numeric($args['card_id'])){
            $postargs['ID']=intval($args['card_id']);

            $card_id = wp_update_post($postargs);
        }else{
            $card_id = wp_insert_post($postargs);    
        }
       
        if(is_numeric($card_id)){

            if(!empty($list_id)){
                wp_set_object_terms($card_id,intval($list_id),'list');    
            }
            if(!empty($args['type'])){
                switch($args['type']){
                    case 'project':
                        update_post_meta($card_id,'vibe_card_project',$args['item_id']);
                    break;
                    case 'member':
                        add_user_meta($args['item_id'],'vibe_project_card_member',$card_id);
                    brea;
                    case  'labels':
                        add_post_meta($card_id,'vibe_card_label',sanitize_text_field($args['label']));
                    break;
                    case 'status':
                        wp_update_post(['id'=>$card_id,'post_status'=>$args['item_id']]);
                    break;
                    case  'milestone':
                        if($args['item_id']){
                            update_post_meta($card_id,'vibe_project_milestone',1);    
                        }else{
                            delete_post_meta($card_id,'vibe_project_milestone');
                        }
                        
                    break;
                    case 'date':
                        if($args['item_id'] == 'today'){
                            update_post_meta($card_id,'vibe_card_start_date',(strtotime(date('m/d/Y', time()))  - 86400));
                            update_post_meta($card_id,'vibe_card_due_date',(strtotime(date('m/d/Y', time()))-80400) );
                        }
                        if($args['item_id'] == 'tomorrow'){
                            update_post_meta($card_id,'vibe_card_start_date',strtotime(date('m/d/Y', time())) -86400);
                            update_post_meta($card_id,'vibe_card_due_date',strtotime(date('m/d/Y', time())));
                        }

                        if($args['item_id'] == 'month'){
                            update_post_meta($card_id,'vibe_card_due_date',strtotime(date('y-m', time()).'-30')+300);
                        }
                    break;
                }
            }
            
            if(!empty($args['progress'])){
                update_post_meta($card_id,'vibe_card_progress',intval($args['progress']));
            }
            if(!empty($args['dates']) && is_array($args['dates']) && count($args['dates'])){
                update_post_meta($card_id,'vibe_card_start_date',$args['dates'][0]);
                update_post_meta($card_id,'vibe_card_due_date',$args['dates'][1]);
            }

            if(!empty($args['dependencies'])){
               

                $dependencies = get_post_meta($card_id,'vibe_card_dependency',false);

                foreach($args['dependencies'] as $k=>$id){

                    $id = str_replace('card_','',$id);
                    $args['dependencies'][$k]=$id;
                    if(empty($dependencies) || !in_array($id,$dependencies)){
                        add_post_meta( $card_id,'vibe_card_dependency',$id);        
                    }
                }
                if(!empty($dependencies)){
                    foreach($dependencies as $id){
                        if(!in_array($id,$args['dependencies'])){
                            delete_post_meta($card_id,'vibe_card_dependency',$id);
                        }
                    }    
                }
                
            }

            if(empty($args['id'])){
                add_user_meta($this->user->id,'vibe_project_card_member',$card_id);
            }
            $return = vibe_projects_get_task_object($card_id);
            $return['card_id']=$card_id;
            $args['cards'][]=$return;
            $link = site_url();
            if(function_exists('vibebp_get_setting')){
                $app_page = vibebp_get_setting('bp_single_page');
                if(!empty($app_page)){
                    $link = get_permalink($app_page);
                    $link .= '#component=projects';
                    if(!empty($args['project'])){
                        $link .='&project='.$args['project'];
                        update_post_meta($card_id,'vibe_card_project',$args['project']);
                    }
                    if(!empty($args['board'])){
                        $link .='&board='.$args['board'];
                        update_post_meta($card_id,'vibe_card_board',$args['board']);
                    }
                    if(!empty($list_id)){
                        $parents['list'] = $list_id;    
                    }
                    $link .='&card='.$card_id;
                }
            }
            if(empty($args['id'])){
                
                update_post_meta($card_id,'card_share_link',$link);
                do_action('vibe_projects_create_new_card',$return,$args,$this->user->id);
                
            }

        }else{
            $status = 0;
        }

        return new WP_REST_Response(array('status'=>$status,'card'=>$return),200);


    }

    function get_full_card($request){
        $post = json_decode($request->get_body(),true);
        $card_id = intval($request->get_param('card_id'));

        $return = array(
            'status'=>1,
            'allLabels'=>[],
            'dueDateCard'=>[],
            'checklists'=>[],
            'raw'=>get_post_meta($card_id,'raw',true)
        );

        $due_date = get_post_meta( $card_id, 'vibe_card_due_date', true );
        
        $return['checklists'] = get_post_meta( $card_id, 'vibe_card_checklist', true );

        $return['dueDateCard'] = ['duedate' => (!empty($due_date)?date('Y-m-d', $due_date):''),'timestamp'=>get_post_meta( $card_id, 'vibe_card_due_date', true )];

        $return['cardAttachments'] = get_post_meta( $card_id, 'vibe_card_attachments', true );
        
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value= %d",'vibe_project_card_member',$card_id));

        $members = [];
        if(!empty($results)){
            foreach ($results as $key => $result) {
                $members[] = array(
                    'id'=>$result->user_id,
                    'label'=>bp_core_get_user_displayname($result->user_id),
                    'avatar'=>bp_core_fetch_avatar(array(
                            'item_id'=>$result->user_id,
                            'object' => 'user',  
                            'type' => 'thumb',  
                           'html'=>false
                         )
                    ),
                );
            }
        }
        $cardlabels = get_post_meta( $card_id, 'vibe_card_label', true );
        $return['labels'] = $cardlabels;
        $return['cardmembers'] = $members;

        $card_fields = vibebp_get_setting('create_card_fields','vibe_projects','cards');
        if(!empty($card_fields)){
            $metas=[];
            foreach($card_fields['key'] as $key){
                $value = get_post_meta($card_id,$key,true);
                $metas[]=['meta_key'=>$key,'meta_value'=>$value];
            }
            $return['meta']=$metas;
        }
        return new WP_REST_Response($return,200); 
    }

    function card_actions($request){

        $post = json_decode($request->get_body(),true);
        $card_id = $request->get_param('card_id');
        $action = $request->get_param('action');
        $user_id =0;
        if(!empty($post->user_id)){
            $user_id = $post->user_id;
        }
        if(empty($post['project_id'])){
            $project_id = get_post_meta($card_id,'vibe_card_project',true);
        }else{
            $project_id = $post['project_id'];
        }
        $result = array();
        $status = 0;
        wp_cache_delete('card_'.$card_id,'cards');
        switch($action){

            case 'addCardAttachment':
                $atts = get_post_meta( $card_id, 'vibe_card_attachments', true );
                if(empty($atts)){
                    $atts= [];
                }
                $attachment = $post['attachment'];
                $atts[]=$attachment;
                update_post_meta($card_id,'vibe_card_attachments',$atts);
               
                do_action('vibe_projects_card_upload_attachment',$attachment,$card_id,$this->user->id);
                return new WP_REST_Response(array('status'=>1,'message'=>_x('Attachment added to card','added message','vibe-projects')),200);
            break;
            case 'deleteCardAttachment':

                $atts = get_post_meta( $card_id, 'vibe_card_attachments', true );
                if(empty($atts)){
                    $atts= [];
                }
                $attachment = $post['attachment'];
                if(!empty($atts) && !empty($post['attachment'])){
                    $new_atts=[];
                    foreach ($atts as $key => $att) {
                        if($post['attachment']['value']!=$att['value']){
                            $new_atts[] = $att;
                        }
                    }
                    update_post_meta($card_id,'vibe_card_attachments',$new_atts);
                    
                    $att_id =attachment_url_to_postid($post['attachment']['value']);
                    $deleted = wp_delete_attachment($att_id);
                }
                do_action('vibe_projects_card_attachment_removed',$attachment,$card_id,$this->user->id);
                return new WP_REST_Response(array('status'=>1,'message'=>_x('Attachment removed','removed mesage','vibe-projects')),200);
            break;

            case 'archive': 
                $status = 1;
                $message = _x('Card sent to board!','','vibe-projects');
                if(!empty($post['archive'])){
                    $message = _x('Card archived!','','vibe-projects');
                    wp_trash_post( $card_id); 
                }else{
                    wp_untrash_post( $card_id); 
                }

                do_action('vibe_projects_card_archived',$card_id,$this->user->id,$project_id);
                return new WP_REST_Response(array('status'=>$status,'message'=>$message),200);

            break;
            case 'watch': 
                 
                $watchers = get_post_meta($card_id,'watch_card',false);
                if(empty($watchers) || !in_array($this->user->id,$watchers)){
                    add_post_meta($card_id,'watch_card',$this->user->id);    
                    do_action('vibe_projects_card_watchers_updated',$card_id,$this->user->id,$project_id);
                    return new WP_REST_Response(array('status'=>1,'message'=>_x('Watching card!','api response','vibe-projects')),200);
                }else{
                     delete_post_meta($card_id,'watch_card',$this->user->id);
                     return new WP_REST_Response(array('status'=>1,'message'=>_x('Un-Watching card!','api response','vibe-projects')),200);
                }
                
                return new WP_REST_Response(array('status'=>1,'message'=>_x('Watching card!','api response','vibe-projects')),200);

            break;
            case 'unwatch': 
                
                $watchers = get_post_meta($card_id,'watch_card',false);
                if(!empty($watchers) && in_array($this->user->id,$watchers)){
                    delete_post_meta($card_id,'watch_card',$this->user->id);
                    do_action('vibe_projects_card_watchers_updated',$card_id,$this->user->id,$project_id);
                    return new WP_REST_Response(array('status'=>1,'message'=>_x('Un Watching card!','api response','vibe-projects')),200);
                }else{
                    add_post_meta($card_id,'watch_card',$this->user->id);    
                    do_action('vibe_projects_card_watchers_updated',$card_id,$this->user->id,$project_id);
                    return new WP_REST_Response(array('status'=>1,'message'=>_x('Watching card!','api response','vibe-projects')),200);
                }
                

            break;
            case 'labels':  

                $status = 1;
                $message = _x('Label removed','removed message','vibe-projects');
                $result = get_post_meta( $card_id, 'vibe_card_label', false);
                if(empty($result)){
                    $result=[];
                }

                if(!empty($post['added'])){
                    if(!in_array($post['label'],$result)){                        
                        add_post_meta($card_id,'vibe_card_label',sanitize_text_field($post['label']));
                    }
                    $message = _x('Card Labels updated','','vibe-projects');
                }else{
                    if(!empty($result)){
                        foreach ($result as $k => $r) {
                            if($r==$post['label']){
                                delete_post_meta($card_id,'vibe_card_label',$result[$k]);
                                break;
                            }
                        }
                    }
                }
               
                if(!empty($post['added'])){
                    do_action('vibe_projects_card_label_added',$card_id,$this->user->id,$project_id);
                }else{
                    do_action('vibe_projects_card_label_removed',$card_id,$this->user->id,$project_id);
                }
                
                return new WP_REST_Response(array('status'=>$status,'message'=>$message),200); 
            break;


            case 'checklist':  

                $status = 0;
                $result = array();
                $result['name'] = get_post_meta( $card_id, 'vibe_card_checklist', true );
                $result['tasks'] = array();
                if(!empty($result['name'])){
                    $status = 1;
                }

                do_action('vibe_projects_card_checklists',$card_id,$this->user->id,$project_id);
                return new WP_REST_Response(array('status'=>$status,'checklists'=>$result),200);
            break;


            case 'duedate':  

                $status = 0;
                $result = array();
                $result['timestamp'] = get_post_meta( $card_id, 'vibe_card_due_date', true );
                $result['duedate'] = date('Y-m-d',$result['timestamp']);

                if(!empty($result)){
                    $status = 1;
                }

                do_action('vibe_projects_card_set_due_date',$card_id,$this->user->id,$project_id);

                return new WP_REST_Response(array('status'=>$status,'dueDateCard'=>$result),200);
            break;

            case 'attachments':  

                $status = 1;
                $result = get_post_meta( $card_id, 'vibe_card_attachments', true );
                do_action('vibe_projects_card_attachments',$result);

                return new WP_REST_Response(array('status'=>$status,'cardAttachments'=>$result),200);
            break;
            case 'complete':  

                $status = 1;

                $result = array();

                
                if(!empty($post['complete'])){
                    $can_complete = apply_filters('vibe_card_can_complete',0,$card_id,$project_id,$this->user);
                    if(empty($can_complete)){
                        update_post_meta( $card_id,'vibe_card_complete',$project_id);    
                        $message = _x('Card marked completed!','','vibe-projects');
                        do_action('vibe_projects_card_completed',$card_id,$this->user->id,$project_id);
                    }else{
                        $message = $can_complete;
                        $status=0;
                    }
                }else{
                    delete_post_meta( $card_id,'vibe_card_complete');
                    $message = _x('Card marked incomplete!','','vibe-projects');
                }
                
                return new WP_REST_Response(array('status'=>$status,'message'=>$message),200);
            break;
            case 'milestone':  
                $status = 1;
                $result = array();
                if(!empty($project_id)){
                    if(!empty($post['milestone'])){
                        update_post_meta( $card_id,'vibe_project_milestone',$project_id);
                        do_action('vibe_projects_milestone_added',$card_id,$project_id,$this->user->id);    
                    }else{
                        delete_post_meta( $card_id,'vibe_project_milestone');
                    }
                }
                $message = _x('Card removed as milestone!','','vibe-projects');
                if(!empty($post['milestone'])){
                    $message = _x('Card added as milestone for project!','','vibe-projects');
                    do_action('vibe_projects_card_milestoned',$card_id,$project_id,$this->user->id);
                }else{
                    do_action('vibe_projects_card_unmilestoned',$card_id,$project_id,$this->user->id);
                }
                
                do_action('vibe_projects_card_milestone',$result);
                return new WP_REST_Response(array('status'=>$status,'message'=>$message),200);
            break;
            case 'move':  

                $status = 1;

                $result = array();

                
                if(!empty($post['complete'])){

                    update_post_meta( $card_id,'vibe_card_complete',$project_id);
                }else{
                    delete_post_meta( $card_id,'vibe_card_complete');
                }
                
                $message = _x('Card marked incomplete!','','vibe-projects');
                if(!empty($post['complete'])){
                    $message = _x('Card marked completed!','','vibe-projects');
                    do_action('vibe_projects_card_completed',$card_id,$this->user->id,$project_id);
                }
                return new WP_REST_Response(array('status'=>$status,'message'=>$message),200);
            break;

        }

        //return new WP_REST_Response(array('status'=>$status,'data'=>$result),200);
    }
    
    function get_watchers($request){
        $card_id = $request->get_param('card_id');
        return new WP_REST_Response(array('status'=>1,'members'=>get_post_meta($card_id,'watch_card',false)),200);
    }

    function save_card_description($request){

        $args = json_decode($request->get_body(),true);
        $card_id = $request->get_param('card_id');

        if(!empty($args['post_content'])){
            $result = wp_update_post( array(
                'ID' => $card_id,
                'post_type'    => 'card',
                'post_content' => $args['post_content']
            ) );
            if(!empty($args['raw'])){
                update_post_meta($card_id,'raw',wp_slash($args['raw']));    
            }
        }
        
        if(!empty($args['meta'])){
            foreach($args['meta'] as $meta){
                update_post_meta($card_id,$meta['meta_key'],$meta['meta_value']);
            }
        }

        return new WP_REST_Response(array('status'=>1,'cardDescription'=>$result),200);
    }


    function add_new_checklist($request){
        $args = json_decode($request->get_body(),true);

        $card_id = $request->get_param('card_id');

        $status = 0;
        $result = array();
        $content = [];
        if(!empty($args['list'])){
            
            $status = 1;
            update_post_meta($card_id,'vibe_card_checklist',$args['list']);
            $result = $args['list'];
            
        }else{
            if(!empty($args['name'])){
               $status = 1;
                
                $result = get_post_meta($card_id,'vibe_card_checklist',true);
                if(empty($result)){
                    $result = [];
                }
                $content = array('name'=> sanitize_text_field($args['name']));
                if(!empty($args['duedate'])){
                    if(!is_numeric($args['duedate'])){
                        $content['duedate'] = strtotime(sanitize_text_field($args['duedate']));
                    }else{
                        $content['duedate'] = $args['duedate'];
                    }
                    
                }
                if(!empty($args['member'])){
                    $content['member'] = sanitize_text_field($args['member']);
                }
                $result[]=$content;
                update_post_meta($card_id,'vibe_card_checklist', $result); 
                do_action('vibe_projects_update_checklist',$card_id, $this->user->id,$args);
            }else{
                $status = 1;
                update_post_meta($card_id,'vibe_card_checklist',$args['list']);
                $result = $args['list'];
                do_action('vibe_projects_add_checklist',$card_id, $this->user->id,$args);
            }
        }

        return new WP_REST_Response(array('status'=>$status,'addedChecklist'=>$result,'message'=>_x('List updated!','','vibe-projects')),200); 

    }


    function fetch_card_activity($request){
        global $bp, $wpdb;

        $args = json_decode($request->get_body(),true);

        $card_id = $request->get_param('card_id');
        $user_id = get_current_user_id();

        $status = 1;
        $result = array();

        if ( ! bp_is_active( 'activity' ) ) {
            return;
        }

        $table_name=$bp->activity->table_name;
        

        $retakes = $wpdb->get_results($wpdb->prepare( "SELECT activity.user_id, activity.id, activity.type, activity.content, 
                    activity.date_recorded FROM {$table_name} AS activity
                    WHERE  activity.component  = 'vibe_projects'
                    AND   item_id = %d
                    ORDER BY date_recorded DESC" ,$card_id));
        $this->time_format = get_option('date_format').' '.get_option('time_format');
        if(!empty($retakes)){
            foreach($retakes as $value){
                $the_user = get_userdata($value->user_id);
                if(!empty($the_user) && !is_wp_error($the_user)){
                    $result['content'][] = array('id'=>$value->id,'activity'=>$value->content,'type'=>$value->type,'name'=>$the_user->display_name, 'date_recorded'=>date($this->time_format,strtotime($value->date_recorded)),
                    'avatar'=>bp_core_fetch_avatar(array(
                            'item_id'=>$value->user_id,
                            'object' => 'user',  
                            'type' => 'thumb',  
                           'html'=>false
                         )
                    ));
                }
                
            }
        }else{
            $result = array('status'=>0,'message'=>__('No Activity Recorded','vibe-projects'));
        }
       
        return new WP_REST_Response(array('status'=>$status,'cardActivity'=>$result),200);
    }


    function set_due_date($request){

        $args = json_decode($request->get_body(),true);
        $card_id = $request->get_param('card_id');
        if(!empty($args['duedate'])){
            if(is_numeric($args['duedate'])){
                update_post_meta( $card_id, 'vibe_card_due_date', intval($args['duedate']));
            }else{
                if($args['duedate']['timestamp'] > 100000000000){
                    $args['duedate']['timestamp']=round($args['duedate']['timestamp']/1000,0);
                }
                update_post_meta( $card_id, 'vibe_card_due_date', intval($args['duedate']['timestamp'])); 
            }
            
            do_action('vibe_projects_card_duedate_set',$card_id,$this->user->id,$args['duedate']);
        }
        if(!empty($args['startdate'])){
            if(is_numeric($args['startdate'])){
                update_post_meta( $card_id, 'vibe_card_start_date', intval($args['startdate'])); 
            }else{
                if($args['startdate']['timestamp'] > 100000000000){
                    $args['startdate']=round($args['startdate']['timestamp']/1000,0);
                }
                update_post_meta( $card_id, 'vibe_card_start_date', intval($args['startdate']['timestamp'])); 
            }
            do_action('vibe_projects_card_startdate_set',$card_id,$this->user->id,$args['startdate']);    
        }
        
        return new WP_REST_Response(array('status'=>1,'message'=>__('Card dates set','vibe-projects')),200);
    }


    function upload_card_attachments($request){

        $body =json_decode(stripslashes($_POST['body']),true);
        $card_id = $request->get_param('card_id');

        $status = 0;
        $movefile = [];
        $attachment = [];
        if(is_numeric($card_id)){

            if(!empty($_FILES) && !empty($body['attachments'])){
                if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                }
                $upload_overrides = array(
                    'test_form' => false
                );
                foreach($body['attachments'] as $key=>$meta){
                    $uploadedfiles = $_FILES['files_'.$key];
                    $movefile = wp_handle_upload( $uploadedfiles, $upload_overrides );
                    if ( $movefile && ! isset( $movefile['error'] ) ) {
                        $attachment=array('type'=>$meta['type'],'value'=>$movefile['url'], 'name'=>basename($movefile['url']));
                        $existing_atts = get_post_meta($card_id,'vibe_card_attachments',true);
                        if(empty($existing_atts)){
                            $existing_atts= [];
                        }
                        $existing_atts[] = $attachment;
                        update_post_meta($card_id, 'vibe_card_attachments', $existing_atts);
                        do_action('vibe_projects_upload_attachment',$card_id,$this->user->id,$body,$attachment);
                    }
                }
            }

        }
        if(empty($movefile['url'])){
             return new WP_REST_Response(array('status'=>0,'message'=>_x('Something went wrong!','','vibe-projects')),200);
        }
        do_action('vibe_projects_card_upload_attachment',basename($movefile['url']), $card_id);
        return new WP_REST_Response(array('status'=>1,'attachment'=>$attachment),200);

    }

    function fetch_all_lists($request){

        $post = json_decode($request->get_body(),true);
        $card_id = $request->get_param('card_id');

        $status = 1;
        $moveCard = array();

        $moveCard = get_terms( array(
            'taxonomy' => 'list',
            'hide_empty' => false,
            ));

        return new WP_REST_Response(array('status'=>$status,'moveCard'=>$moveCard),200); 

    }


    function move_card_action($request){

        $post = json_decode($request->get_body(),true);
        $card_id = $request->get_param('card_id');

        $status = 0;
        $moveArgs = array();

        if(!empty($post['list_id'])){
            $status = 1;

            wp_set_object_terms($card_id,[$post['list_id']],'list');
            foreach($post['order'] as  $k=>$c_id){
                $result = wp_update_post(['ID' => $c_id,'menu_order' => ($k+1)]);
            }
        }
        do_action('vibe_projects_move_card_action',$card_id, $this->user->id,$post['list_id']);
        
        return new WP_REST_Response(array('status'=>$status,'moveArgs'=>$moveArgs),200); 

    }


    function set_card_attachments($request){

        $body =json_decode(stripslashes($_POST['body']),true);
        $card_id = $request->get_param('card_id');

        $status = 0;

        $attachments = [];
        if(is_numeric($card_id)){

            if(!empty($_FILES) && !empty($body['attachments'])){
                if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                }
                $upload_overrides = array(
                    'test_form' => false
                );
                foreach($body['attachments'] as $key=>$meta){
                    $uploadedfiles = $_FILES['files_'.$key];
                    $movefile = wp_handle_upload( $uploadedfiles, $upload_overrides );
                    if ( $movefile && ! isset( $movefile['error'] ) ) {
                        $attachments['attachments']=array('type'=>$meta['type'],'value'=>$movefile['url'], 'name'=>basename($movefile['url']));
                        add_post_meta($card_id, 'vibe_card_comments_attachments', $attachments);
                        do_action('vibebp_upload_attachment',$movefile['url'],$user_id);
                    }
                }
            }

        }

        return new WP_REST_Response(array('status'=>1,'attachments'=>$attachments),200);

    }


     function card_comment_actions($request){

        global $wpdb, $bp;
        $args = json_decode($request->get_body(),true);
        $card_id = $request->get_param('card_id');

        $table_name=$bp->activity->table_name;
        $status = 0;
        $result = array();
        switch($args['type']){
            case 'delete_comment':
            if(!empty($args['comment']['id'])){
                $status = 1;
                $results = $wpdb->get_results("SELECT * FROM {$table_name} WHERE id={$args['comment']['id']} ");
                if(!empty($results) && !empty($results[0])){
                    wp_delete_comment($results[0]->secondary_item_id);
                }
                bp_activity_delete( array( 'id' => $args['comment']['id'] ) );
                do_action('vibe_projects_card_comment_deleted',$card_id,$this->user->id,$args,$commentarr);
                return new WP_REST_Response(array('status'=>$status,'cardCommentAction'=>$result,'message'=>_x('comment deleted!','','vibe-projects')),200);
            }
            break;
            case 'edit_comment':
            if(!empty($args['comment']['id'])){
                $status = 1;
                $results = $wpdb->get_results("SELECT * FROM {$table_name} WHERE id={$args['comment']['id']} ");
                $commentarr = [];
                if(!empty($results) && !empty($results[0])){
                    
                    $commentarr['comment_ID'] = $results[0]->secondary_item_id;
                    $commentarr['comment_content'] = $args['comment']['activity'];
                    wp_update_comment($commentarr);
                }
                $results = $wpdb->query("UPDATE {$table_name} SET content='{$args['comment']['activity']}' WHERE id={$args['comment']['id']} ");
                do_action('vibe_projects_card_comment_updated',$card_id,$this->user->id,$args,$commentarr);
                return new WP_REST_Response(array('status'=>$status,'cardCommentAction'=>$result,'message'=>_x('comment updated!','','vibe-projects')),200);
            }
            break;
            
        }
        
    }

    function addmembertocard($request){
        $args = json_decode($request->get_body(),true);
        if(!empty($args['card_id']) && !empty($args['members'])){
            $card_id = esc_attr($args['card_id']);
            $project_id = esc_attr($args['project_id']);

            if(empty($project_id)){
                $project_id = get_post_meta($card_id,'vibe_card_project',true);
            }
            
            foreach($args['members'] as $member){

                if(vibe_projects_is_member($member,$project_id)){
                    $existing = get_user_meta($member,'vibe_project_card_member',false);
                    if(empty($existing)){
                        $existing = [];
                    }
                    if(empty($existing) || !in_array($card_id, $existing)){
                        add_user_meta($member,'vibe_project_card_member',$card_id);
                        do_action('vibe_projects_add_member_to_card',$card_id,$member,$project_id,$this->user->id,);
                    }
                }
            }
        }
        return new WP_REST_Response(array('status'=>true,'message'=>sprintf(_x('%s members added to card!','','vibe-projects'),count($args['members']))),200);
        
    }

    
    function removemembercard($request){
        $args = json_decode($request->get_body(),true);
        if(!empty($args['project_id']) && !empty($args['card_id']) && !empty($args['member'])){

            $card_id = esc_attr($args['card_id']);
            $project_id = esc_attr($args['project_id']);

            $existing = get_user_meta($args['member'],'vibe_project_card_member',false);
            if(empty($existing)){
                $existing = [];
            }
            if(!empty($existing) && in_array(esc_attr($args['card_id']), $existing)){
                delete_user_meta(esc_attr($args['member']),'vibe_project_card_member',esc_attr($args['card_id']));
                do_action('vibe_projects_remove_member_from_card',esc_attr($args['card_id']),esc_attr($args['member']),$project_id,$this->user->id);
            }
        }
        return new WP_REST_Response(array('status'=>true,'message'=>_x('Member removed from card!','','vibe-projects')),200);
        
    }

    function changeStatus($request){
        $args = json_decode($request->get_body(),true);
        $post_status = esc_attr($args['status']);
        $card_id = esc_attr($args['card_id']);
        $project_id = esc_attr($args['project_id']);

        if(vibe_projects_user_can('complete_card',$this->user->member_type) && wp_update_post(['ID'=>$card_id,'post_status'=>$post_status])){
            do_action('vibe_projects_card_status_updated',$card_id,$project_id,$post_status,$this->user);
        }else{
            return new WP_REST_Response(array('status'=>false,'message'=>_x('Card status not changed!','api response','vibe-projects')),200);
        }
        return new WP_REST_Response(array('status'=>true,'message'=>_x('Card status changed!','api response','vibe-projects')),200);
    }

    function changeTitle($request){
        $args = json_decode($request->get_body(),true);
        $title = esc_attr($args['title']);
        $card_id = esc_attr($args['card_id']);
        $project_id = esc_attr($args['project_id']);

        if(vibe_projects_user_can('complete_card',$this->user->member_type) && wp_update_post(['ID'=>$card_id,'post_title'=>$title])){
            do_action('vibe_projects_card_title_updated',$card_id,$project_id,$title,$this->user);
        }else{
            return new WP_REST_Response(array('status'=>false,'message'=>_x('Card title not changed!','api response','vibe-projects')),200);
        }
        return new WP_REST_Response(array('status'=>true,'message'=>_x('Card title changed!','api response','vibe-projects')),200);
    }
    
}

Vibe_Cards_API::init();