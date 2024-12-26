<?php
/**
 * API\
 *
 * @class       Vibe_Boards_API
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Vibe_Boards_API{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Boards_API();
        return self::$instance;
    }

	private function __construct(){

		add_action('rest_api_init',array($this,'register_api_endpoints'));
	}


	function register_api_endpoints(){

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/boards/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_project_boards' ),
                'permission_callback'       => array( $this, 'get_boards_permissions' ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/board/getFullBoard/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_full_board' ),
                'permission_callback'       => array( $this, 'get_boards_permissions' ),
            ),
        ));


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/milestones/get', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_project_milestones' ),
                'permission_callback'       => array( $this, 'get_boards_permissions' ),
            ),
        ));
        
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/taxonomy/board-type/terms', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_board_types' ),
                'permission_callback'       => array( $this, 'get_boards_permissions' ),
            ),
        ));
        

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/(?P<project_id>\d+)/board/getFields', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_board_fields' ),
                'permission_callback'       => array( $this, 'get_new_boards_permissions' ),
                 'args'                      =>  array(
                        'project_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/(?P<project_id>\d+)/newboard/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'create_new_board' ),
                'permission_callback'       => array( $this, 'get_new_boards_permissions' ),
                 'args'                      =>  array(
                        'project_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/(?P<project_id>\d+)/deleteboard/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'delete_board' ),
                'permission_callback'       => array( $this, 'get_new_boards_permissions' ),
                 'args'                      =>  array(
                        'project_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));
        

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/boards/save_lists', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'save_lists' ),
                'permission_callback'       => array( $this, 'get_boards_permissions' ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/boards/save_cards', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'save_cards' ),
                'permission_callback'       => array( $this, 'get_boards_permissions' ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/boards/lists/(?P<board_id>\d+)', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_board_lists' ),
                'permission_callback'       => array( $this, 'get_boards_permissions' ),
            ),
        ));

        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/board/bulkAction', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'board_bulk_action' ),
                'permission_callback'       => array( $this, 'get_boards_permissions' ),
            ),
        ));


        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, 'board/getAutomations', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'board_get_automations' ),
                'permission_callback'       => array( $this, 'get_boards_permissions' ),
            ),
        ));
        
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, 'board/saveAutomations', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'board_save_automations' ),
                'permission_callback'       => array( $this, 'get_boards_permissions' ),
            ),
        ));
	}

    function prepare_board_object($board_id){
        $image = get_the_post_thumbnail_url($board_id);
        
        global $post;


        $return = array(
            'id'=>$board_id,
            'title'=>get_the_title($board_id),
            'description'=> apply_filters('vibebp_the_content',get_post_field('post_content', $board_id)),
            'image'=>$image ? $image : plugins_url('../../assets/images/add_image.png', __FILE__),
            'board_visibility'=> vibe_projects_get_board_visibility($board_id),
            'type'=>wp_get_object_terms($board_id,'board-type'),
            'members'=>[$this->user->id]
        );

        $check = get_post_meta($board_id,'vibe_board_show_progress',true);
        if(!empty($check) && $check == 'S'){
            $progress=0;
            global $wpdb;
            $lists = wp_get_object_terms($board_id,'list');

            if(!empty($lists)){
                $list_ids = wp_list_pluck($lists,'term_id');
               
                 $card_ids = $wpdb->get_results($wpdb->prepare("
                    SELECT object_id
                    FROM {$wpdb->term_relationships} 
                    WHERE term_taxonomy_id IN (".implode(',',$list_ids).")"));


                $completed_cards = 0;
                if(!empty($card_ids)){
                    $completed_cards = $wpdb->get_var($wpdb->prepare("
                        SELECT count(post_id)
                        FROM {$wpdb->postmeta} 
                        WHERE post_id IN (".implode(',',wp_list_pluck($card_ids,'object_id')).")
                        AND meta_key = 'vibe_card_complete'"));
                    
                   
                }
                
                
                //$count = count($card_ids)-1;
                $count = count($card_ids)-count($list_ids);
                if(empty($count)){$count=1;}

                $return['progress'] = ['complete'=>intval($completed_cards),'total'=>$count];
            }

            
        }
       
        return $return;
    }
    


    function get_boards_permissions($request){

       $body =json_decode($request->get_body(),true);

        if(!empty($body['token'])){
            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
            if(!empty($this->user)){
                return true;
            }
            
        }
       return false;
    }

    function get_new_boards_permissions($request){

        $body = json_decode($request->get_body(),true);
            
        if(!empty($body['token'])){
            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
            if(!empty($this->user) && apply_filters('vibe_projects_can_create_board',1,$this->user->caps)){
                return true;
            }
        }
        return false;
    }

    function get_board_fields($request){
        $args = json_decode($request->get_body(),true);
        $project_id =$request->get_param('project_id');

        $board_id = 0;
        if(!empty($args['board_id'])){
            $board_id =$args['board_id'];
        }        
        return new WP_REST_Response(array('status'=>1,'fields'=>vibe_projects_get_board_fields($board_id,$project_id)),200);
    }

    function get_board_types($request){
        $terms = get_terms( array(
            'taxonomy' => 'board-type',
            'hide_empty' => false,
        ) );

        $result = [];
        if(!empty($terms)){
            foreach($terms as $term){
                $result[] =['id'=>$term->term_id,'text'=>$term->name];
            }
            return new WP_REST_Response(array('status'=>1,'terms'=>$result),200);
        }
        return new WP_REST_Response(array('status'=>0,'terms'=>[]),200);
    }

    function get_project_boards($request){
        
        $args = json_decode($request->get_body(),true);

        $board_args = array(
            'post_type'=>'board',
            'per_page'=>10,
            'paged'=>$args['page'],
            'post_parent'=>esc_attr($args['project_id'])
        );
        if(!empty($args['search'])){
            $board_args['s'] = $args['search'];
        }
        if(!empty($args['tax_query'])){
            $board_args['tax_query']=$args['tax_query'];
        }
        
        if(!empty($args['boardType'])){
            if(empty($board_args['tax_query'])){$board_args['tax_query']=[];};
            $board_args['tax_query'][]=[
                'taxonomy'=>'board-type',
                'field'=>'term_id',
                'terms'=>intval($args['boardType'])
            ];
        }
        $query = new WP_Query(apply_filters('vibe_projects_get_project_boards',$board_args));
        $result = array();
       
        if(!empty($query->have_posts())){
            if($query->have_posts()){
                $status =1;
                while($query->have_posts()){
                    $query->the_post();
                    $result[]= $this->prepare_board_object(get_the_ID());
                }
            }
        }
        $total = $query->found_posts;
        wp_reset_postdata();
        
        $default_view = get_post_meta(esc_attr($args['project_id']),'vibe_projects_default_task_view',true);
        if(empty($default_view)){$default_view='boards';}
        return new WP_REST_Response(array(
            'status'=>1,
            'data'=>$result,
            'types'=> get_terms(['taxonomy' => 'board-type','hide_empty' => false]),
            'total'=>$total,
            'default_view'=>$default_view
        ),200);
    
    }


    function get_full_board($request){

        $args = json_decode($request->get_body(),true);
       
        $board_id = esc_attr($args['board_id']);
        $project_id = esc_attr($args['project_id']);
        $lists = wp_get_object_terms($board_id,'list',array(
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


        $cards = [];
        if(!empty($lists) && !is_wp_error($lists)){
            foreach($lists as $key=>$list){
                $name = get_term_meta($list->term_id,'name',true);
                if(!empty($name)){
                    $lists[$key]->name = $name;
                }
                unset($lists[$key]->taxonomy);
                unset($lists[$key]->term_group);
                unset($lists[$key]->filter);
                unset($lists[$key]->term_taxonomy_id);
                
                $list_cards=$this->get_cards_from_list($list->term_id,'full');

                $cards = array_merge($cards,$list_cards);
                $lists[$key]->cards=wp_list_pluck($list_cards,'card_id');

                //$lists[$key]->cards=$this->get_cards_from_list($list->term_id,'full');
            }
        }

        $card_ids = wp_list_pluck($cards,'card_id');
        global $wpdb;

        $unlisted_card_ids = $wpdb->get_results($wpdb->prepare("
                    SELECT post_id
                    FROM  {$wpdb->postmeta} 
                    WHERE meta_key = 'vibe_card_project' 
                    AND meta_value = %d AND post_id NOT IN (".implode(',',$card_ids).")
                    ",$project_id));


        if(!empty($unlisted_card_ids)){
            $unlisted_cards=[];
            foreach($unlisted_card_ids as $card_id){
                $c = vibe_projects_get_task_object($card_id->post_id,false);    
                if($c){
                    $unlisted_cards[] = $c;
                }
            }
             $cards = array_merge($cards,$unlisted_cards);
        }

        $members = [];
        if(empty($lists)){
            $lists =[];
        }else{
            if(!empty($cards)){
                foreach($cards as $card){
                    foreach($card['members'] as $member_id){
                        if(!in_array($member_id,$members)){
                            $members[]=$member_id;
                        }
                    }
                }
            }
        }

        if(empty($members)){
            $members = [get_the_author_meta('ID')];
        }
        if(!in_Array(get_the_author_meta('ID'),$members)){
            $members[]=get_the_author_meta('ID');
        }
        
        
        $image = get_the_post_thumbnail_url($board_id);
        $return = array(
            'id'=>$board_id,
            'project_id'=>wp_get_post_parent_id($board_id),
            'title'=>get_the_title($board_id),
            'description'=> apply_filters('vibebp_the_content',get_post_field('post_content', $board_id)),
            'image'=>empty($image) ? $image : plugins_url('../../assets/images/add_image.png', __FILE__),
            'type'=>wp_get_object_terms($board_id,'board-type'),
            'lists'=>$lists,
            'cards'=>$cards,
            'members'=>$members,
            'labels'=>get_post_meta($board_id,'vibe_board_labels',true),
            'automations'=>get_post_meta($board_id,'automations',true)
        );

        return new WP_REST_Response(array('status'=>true,'board'=>$return),200);
    }

    
    function get_project_milestones($request){
        $args = json_decode($request->get_body(),true);
        $milestones = [];
        global $wpdb;
        $args['project_id'] = esc_attr(intval($args['project_id']));

        $cards = $wpdb->get_results("
            SELECT DISTINCT pm.post_id as post_id,p.post_title as title,p.post_status as status 
            FROM {$wpdb->postmeta} AS pm 
            LEFT JOIN {$wpdb->posts} as p ON p.ID=pm.post_id 
            WHERE pm.meta_key='vibe_project_milestone' 
            AND pm.meta_value={$args['project_id']} 
            AND p.post_status !='trash'
            AND p.post_type = 'card'
        ");
        if(!empty($cards)){
            foreach ($cards as $key => $card) {
                $milestones[] = array(
                    'card_id'=>$card->post_id,
                    'title'=>$card->title,
                    'status'=>$card->status
                );
            }
        }
        return new WP_REST_Response(array('status'=>1,'milestones'=>$milestones),200);
    }

    function delete_board($request){
        $args = json_decode($request->get_body(),true);
        $project_id = $request->get_param('project_id');
        $return = array('status'=>1);
        if(!empty($args['id'])){
            $lists = wp_get_object_terms($args['id'],'list',array(
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
                   
                    $cards=$this->get_cards_from_list($list->term_id,'');
                    foreach ($cards as $key => $card) {
                        wp_trash_post( $card['card_id'] );
                    }
                }
            }
            wp_trash_post($args['id']);
        }else{
            return new WP_REST_Response(array('status'=>0,'message'=>_x('Board details missing','','vibe-projects')),200);
        }
        return new WP_REST_Response(array('status'=>1,'message'=>_x('Board and its data deleted','','vibe-projects')),200);
    }

    function create_new_board($request){
            $args = json_decode($request->get_body(),true);
            $project_id = $request->get_param('project_id');
            $return = array('status'=>1);

            $board_args = apply_filters('vibe_projects_create_edit_board_args',array(
                'post_title'=> sanitize_text_field($args['post_title']),
                'post_content'=>sanitize_text_field($args['post_content']),
                'post_status' => (empty($args['post_status'])?'publish':$args['post_status']),
                'post_author' => $this->user->id,
                'post_type' => 'board',
                'post_parent'=>$project_id
            ));
            if(!empty($args['post_content'])){
                $board_args['post_content']=sanitize_text_field($args['post_content']);
            }
            $board_id =0;
            if(!empty($args['id'])){
                $board_args['ID'] = $args['id'];
                $board_id = wp_update_post($board_args);
            }else{
                $board_id = wp_insert_post($board_args);
                $result = update_post_meta($board_id,'project_users',array('administrators'=>array($this->user->id)));
            }
           
            if(!empty($board_id) && is_numeric($board_id)){

                if(!empty($args['raw']))
                    update_post_meta($board_id,'raw',wp_slash($args['raw']));

                if(!empty($args['board_type'])){

                    if(is_numeric($args['board_type'])){
                        $boardTypeObject = get_term_by( 'id', absint( $args['board_type'] ), 'board-type' );
                        $boardTypeName = $boardTypeObject->name;
                    }
                    if( $args['board_type'] == 'new_board_type'){
                        wp_insert_term($args['new_type'],'board-type');
                    }
                    
                    wp_set_object_terms( $board_id, $boardTypeName, 'board-type', false);
                }

                if(!empty($args['meta'])){
                    foreach ($args['meta'] as $key => $meta) {
                        if($meta['meta_key']== '_thumbnail_id'){
                            $meta['meta_value'] = $meta['meta_value']['id'];
                        }
                       update_post_meta($board_id,$meta['meta_key'],$meta['meta_value']);
                    }
                }
                if(!empty($args['taxonomy']) && count($args['taxonomy'])){
                    $_cat_ids = array();
                    foreach ($args['taxonomy'] as  $taxonomy) {
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
                        wp_set_object_terms( $board_id, $taxonomy['value'], $taxonomy['taxonomy'] );
                    }
                }

                $return = [ 'status'=>1,'board'=> $this->prepare_board_object($board_id)];
            }else{
                $return=['status'=>0,'message'=>__('Unable to create board','vibebp')];
            }

        do_action('vibe_projects_create_new_board',$args);
        
        return new WP_REST_Response(array('status'=>$return['status'],'data'=>$return),200);
    }

    function save_lists($request){
        $body = json_decode($request->get_body(),true);
        $lists = $body['lists'];
       

        if(!empty($lists)){
            $changed = false;
            foreach($lists as $key => $list){
                if(!empty($list['list_order_changed'])){
                    $changed = true;
                }
                update_term_meta($list['term_id'],'order',($key+1));

                if(!empty($list['cards']) && !empty($list['is_changed'])){

                    foreach($list['cards'] as  $k => $card_id){
                        wp_update_post(['ID' => $card_id,'menu_order' => ($k+1)]);
                        wp_set_object_terms($card_id,[$list['term_id']],'list');
                        if(!empty($card['is_list_changed'])){
                            do_action('vibe_projects_move_card_action',$card_id, $this->user->id,$list['term_id']);
                        }
                    }
                }
            }
        }
        
        return new WP_REST_Response(array('status'=>1,'message'=>__('List order saved','vibe-projects')),200);
    }

    function save_cards($request){
        $body = json_decode($request->get_body(),true);

        if(!empty($body['card_order'])){
            $card = $body['card_order'];
            if($card['old_list_id'] != $card['new_list_id']){
                wp_set_object_terms($card['card_id'],$card['new_list_id'],'list');
                do_action('vibe_projects_card_moved',$card['card_id'],$card['old_list_id'],$card['new_list_id'],$this->user,$body['board_id']);
            }
            
            foreach($card['order'] as $key => $card_id){
                wp_update_post(array('ID'=>$card_id,'menu_order'=>$key));
            }
        }

        //menu order
        return new WP_REST_Response(array('status'=>1,'body'=>$body,'message'=>__('Card order saved','vibe-projects')),200);
    }

    function get_board_lists($request){
        $body = json_decode($request->get_body(),true);
        $board_id = intval($request->get_param('board_id'));
        $lists = wp_get_object_terms($board_id,'list',array(
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
        $cards = [];
        if(!empty($lists) && !is_wp_error($lists)){

            foreach($lists as $key=>$list){
                $name = get_term_meta($list->term_id,'name',true);
                if(!empty($name)){
                    $lists[$key]->name = $name;
                }

                $list_cards=$this->get_cards_from_list($list->term_id,'full');
                array_merge($cards,$list_cards);
                
                $lists[$key]->cards=wp_list_pluck($list_cards,'card_id');
            }
        }else{
            return new WP_REST_Response(array('status'=>0),200);
        }
        $labels =  get_post_meta( intval($board_id), 'vibe_board_labels', true );
        if(empty($labels)){$labels = [];}
        return new WP_REST_Response(array('status'=>1,'lists'=>$lists,'cards'=>$cards,'allLabels'=>$labels),200);
    }

    function get_cards_from_list($list_id,$details=null){


        $query = new WP_Query(array(
            'post_type'=>'card',
            'post_status'=>'any',
            'posts_per_page'=>-1,
            'orderby' => 'menu_order',
            'order' => 'ASC', 
            //'list'=>$list_id,
            'tax_query'=>array(
                array(
                    'taxonomy'=>'list',
                    'field'    => 'term_taxonomy_id',
                    'terms'=> $list_id,
                    'operator'=>'IN'
                )
            )
        ));

        
        
        $result = array();
        $status = 1;
        
        if(!empty($query->have_posts())){
            if($query->have_posts()){
                

                while($query->have_posts()){
                    $query->the_post();
                    global $post;
                    if($details=='full'){
                        $result[] = vibe_projects_get_task_object(get_the_ID());
                    }else{
                        $result[]= apply_filters('vibe_projects_get_cards_list', array( 
                            'card_id'=>get_the_ID(),
                            'completed'=>get_post_meta(get_the_ID(),'vibe_card_complete',true),
                            'milestone'=>get_post_meta(get_the_ID(),'vibe_project_milestone',true)
                            //'checklists'=>get_post_meta(get_the_ID(),'vibe_card_checklist',true) 
                        ));
                        
                    }
                   

                }

            }
        }
        wp_reset_postdata();
        return $result;
    }

    function board_bulk_action($request){
        $body = json_decode($request->get_body(),true);

        if(!vibe_projects_user_can('board_bulk_actions',$this->user->member_type)){
            return new WP_REST_Response(array('status'=>0,'message'=>__('User can not perform bulk actions','vibe-projects')),200);
        }

        switch($body['bulkAction']){
            case 'assign_milestone':
                if(!empty($body['selectedCards'])){
                    foreach($body['selectedCards'] as $card_id){
                        wp_update_post(['ID'=>$card_id,'post_parent'=>intval($body['data'])]);
                    }
                    return new WP_REST_Response(array('status'=>1,'message'=>__('Milestone added to Cards.','vibe-projects')),200);
                }
            break;
            case 'watch_cards':
                if(!empty($body['selectedCards'])){
                    foreach($body['selectedCards'] as $card_id){
                        if(!empty($body['data'])){
                            foreach($body['data'] as $label){

                            }
                        }
                        wp_update_post(['ID'=>$card_id,'post_parent'=>intval($body['data']['milestone'])]);
                    }
                    return new WP_REST_Response(array('status'=>1,'message'=>__('Milestone added to Cards.','vibe-projects')),200);
                }
            break;
            case 'add_label':
                if(!empty($body['selectedCards'])){
                    foreach($body['selectedCards'] as $card_id){
                        $labels = get_post_meta($card_id,'vibe_card_label',false);
                        foreach($body['data'] as $label){
                            if(empty($labels) || !in_Array($label,$labels)){
                                if(is_array($label) && !empty($label['id'])){
                                    add_post_meta($card_id,'vibe_card_label',$label['id']);    
                                }
                            }
                        }
                    }
                    return new WP_REST_Response(array('status'=>1,'message'=>__('Labels added to Cards.','vibe-projects')),200);
                }
            break;
            case 'remove_label':
                if(!empty($body['selectedCards'])){
                    foreach($body['selectedCards'] as $card_id){
                        delete_post_meta($card_id,'vibe_card_label',$label['id']);
                    }
                    return new WP_REST_Response(array('status'=>1,'message'=>__('Labels removed from Cards.','vibe-projects')),200);
                }
            break;
            case 'add_member':
                if(!empty($body['selectedCards'])){
                    foreach($body['selectedCards'] as $card_id){
                        $members = get_post_meta($card_id,'vibe_project_card_member',false);
                        foreach($body['data'] as $member_id){
                            if(!in_Array($member_id,$members)){
                                add_post_meta($card_id,'vibe_project_card_member',$member_id);
                            }
                        }
                    }
                    return new WP_REST_Response(array('status'=>1,'message'=>__('Member added to Cards.','vibe-projects')),200);
                }
            break;
            case 'change_due_date':
                if(!empty($body['selectedCards'])){
                    foreach($body['selectedCards'] as $card_id){
                        update_post_meta($card_id,'due_date',$body['data']);
                    }
                    return new WP_REST_Response(array('status'=>1,'message'=>__('Due date set for Cards.','vibe-projects')),200);
                }
            break;
        }
    }


    function board_get_automations($request){
        $body = json_decode($request->get_body(),true);
        if(!empty($body['automations'])){
            $automations = get_post_meta($body['board_id'],'automations',true);
        }
        return new WP_REST_Response(array('status'=>1,'automations'=>$automations),200);
    }

    function board_save_automations($request){
        $body = json_decode($request->get_body(),true);
        if(!empty($body['automations'])){
            update_post_meta(intval($body['board_id']),'automations',$body['automations']);
            do_action('vibe_projects_automations_saved',$body['project_id'],$body['board_id'],$body['automations']);
        }
        return new WP_REST_Response(array('status'=>1,'message'=>__('Automations Saved.','vibe-projects')),200);
    }
}

Vibe_Boards_API::init();