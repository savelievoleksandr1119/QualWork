<?php
/**
 * API\
 *
 * @class       Vibe_List_API
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Vibe_List_API{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_List_API();
        return self::$instance;
    }

	private function __construct(){

		add_action('rest_api_init',array($this,'register_api_endpoints'));
	}


	function register_api_endpoints(){

		register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/newlist/(?P<board_id>\d+)', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'create_new_list' ),
                'permission_callback'       => array( $this, 'get_lists_permissions' ),
                'args'                      =>  array(
                        'board_id'                        =>  array(
                            'validate_callback'     =>  function( $param, $request, $key ) {
                                                        return is_numeric( $param );
                                                    }
                        ),
                    ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/cards/list', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_cards_list' ),
                'permission_callback'       => array( $this, 'get_lists_permissions' ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/project/list/(?P<list_id>\d+)/cards', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'move_list_cards' ),
                'permission_callback'       => array( $this, 'get_lists_permissions' ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/list/deletelist/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'delete_list' ),
                'permission_callback'       => array( $this, 'get_lists_permissions' ),
            ),
        ));
        register_rest_route( VIBE_PROJECTS_API_NAMESPACE, '/list/(?P<list_id>\d+)/editlisttitle/', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'edit_list_title' ),
                'permission_callback'       => array( $this, 'get_lists_permissions' ),
            ),
        ));

	}

	function create_new_list($request){

        $args = json_decode($request->get_body(),true);

       	$board_id = $request->get_param('board_id');
       
        $return = array();
        $list = wp_insert_term(sanitize_text_field($args['name']),'list');
        if(is_wp_error($list)){
            $list = wp_insert_term(sanitize_text_field($args['name']).rand(0,9999999),'list');
            add_term_meta($list['term_id'],'name',sanitize_text_field($args['name']));
        }

            $r=wp_set_object_terms($board_id,$list['term_id'],'list',true);
            if(!is_wp_error($r))
            {
                add_term_meta($list['term_id'],'order',999);
                add_term_meta($list['term_id'],'list_status','published');
            }
            if(is_wp_error($r)){
                 return new WP_REST_Response(array('status'=>0,'message'=>__('Could not create list.','vibe-projects')),200);
            }else{
                $listobj = get_term($list['term_id'],'list');
                $listobj->name = sanitize_text_field($args['name']);
               return new WP_REST_Response(array('status'=>1,'listdetails'=>$listobj),200);
            }

    }


    function get_cards_list($request)  {

        $args = json_decode($request->get_body(),true);
        
        $query = new WP_Query(array(
            'post_type'=>'card',
            'post_status'=>'any',
            'posts_per_page'=>-1,
            'orderby' => 'menu_order', 
            'order' => 'ASC', 
            'tax_query'=>array(
                'relation'=>'AND',
                array(
                    'taxonomy'=>'list',
                    'field'=>'id',
                    'terms'=> array($args['list_id'])
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
                    $author_id = get_post_field( 'post_author', get_the_ID());
                    $comment_count = get_post_field( 'comment_count', get_the_ID());
                    $cards_comments = array('post_id'=> get_the_ID(), 'post_type' => 'card');

                global $wpdb;
                $members = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value= %d",'vibe_project_card_member',get_the_ID()));
                $meta = array();

                $startdate = get_post_meta(get_the_ID(),'vibe_card_start_date',true);
                if(empty($startdate)){
                    $startdate = get_the_date();
                }
                $startdate= strtotime($startdate);
                
                $meta[] = array(
                    'key'=>'startdate',
                    'icon'=>'vicon vicon-time',
                    'value'=>$startdate,
                );
                if(!empty($post->comment_count)){
                    $meta[]= array(
                            'key'=>'comments',
                            'icon'=>'vicon vicon-comment-alt',
                            'value'=>$post->comment_count,
                        );
                }

                $attachments = get_children( array( 'post_parent' => $post->ID ) );

                if ( count( $attachments ) ) {
                    $meta[]= array(
                        'key'=>'attachments',
                        'icon'=>'vicon vicon-link',
                        'value'=>count( $attachments )
                    );
                }

                if(!empty($post->post_content)){
                    $meta[]= array(
                            'key'=>'content',
                            'icon'=>'vicon vicon-align-justify',
                            'value'=>'',
                        );
                }

                $due = get_post_meta($post->ID,'vibe_card_due_date',true);
                
                if(!empty($due)){
                    $current_time = time();
                    if ($current_time > $due){
                        $background = '#ec9488';
                        $color = '#222';
                        $border_radius = '3px';
                    }else{
                        $background = '#f2d600';
                        $color = '#fff';
                        $border_radius = '3px';
                    }
                    $meta[]= array(
                        'key'=>'duedate',
                        'icon'=>'vicon vicon-time',
                        'value'=> $due,
                        'display'=> date_i18n(get_option('date_format'),$due),
                        'style' => array('backgroundColor'=>$background,'color'=>$color,'border-radius'=> $border_radius)
                    );
                }

                $checklist = get_post_meta($post->ID,'vibe_card_checklist',true);
                if(!empty($checklist)){
                    $meta[]= array(
                        'key'=>'checklist',
                        'icon'=>'vicon vicon-check-box',
                        'value'=>'',
                    );
                }


                $card_attachments = get_post_meta($post->ID,'vibe_card_attachments',true);
                if(!empty($card_attachments)){
                    $meta[]= array(
                        'key'=>'card_attachments',
                        'icon'=>'vicon vicon-link',
                        'value'=> $card_attachments,
                    );
                }

                $labels = get_post_meta(get_the_ID(),'vibe_card_label',false);
                if(empty($labels)){
                    $labels = [];
                }
                $result[]= apply_filters('vibe_projects_get_cards_list', array( 
                    'card_id'=>get_the_ID(),
                    'title'=>get_the_title(),
                    'status'=>get_post_status(),
                    'description'=>get_the_content(),
                    'cover' => get_the_post_thumbnail_url(get_the_ID()),
                    'meta'  => $meta,
                    'list' => vibe_cards_get_terms(get_the_ID()),
                    'members'=> wp_list_pluck( $members, 'user_id' ),
                    'labels'=> $labels ,
                    'checklists'=>[] 
                ));

                }

            }
        }
        wp_reset_postdata();

        //print_r($result);
        return new WP_REST_Response(array('status'=>$status,'cards'=>$result),200);
    }

    function delete_list($request){

        $args = json_decode($request->get_body(),true);
        $query = array(
        'post_type' => 'card',
        'tax_query' => array(
            array(
                'taxonomy' => 'list',
                'field' => 'term_id',
                'terms' => array($args['list_id']),
            )
        ));

        $related_cards = apply_filters('vibe_projects_delete_list',get_posts($query));

        $result = array();
        $status = 0;
        if(!empty($related_cards)){
            $status = 1;
            foreach ( $related_cards as $cards ){
                $result['posts'] = wp_trash_post( $cards->ID );
                $result['lists'] = update_term_meta( $args['list_id'], 'list_status', 'archived');
            }

        }
        else{
            $status =1;
            $list = $args['list_id'];
            $result['posts'] = wp_delete_post( $list, true );
            $result['lists'] = update_term_meta( $list, 'list_status', 'archived');
        }
        return new WP_REST_Response(array('status'=>$status,'message'=>_x('List Deleted','api hit list','vibe-projects')),200);

    }
    function edit_list_title($request){
        $list_id = intval($request->get_param('list_id'));
        $args = json_decode($request->get_body(),true);
        wp_update_term($list_id,'list',array(
            'name' =>sanitize_text_field($args['name']),
            'slug'=>sanitize_title($args['name']),
        ));
        return new WP_REST_Response(array('status'=>1,'message'=>_x('List name changed','api hit list','vibe-projects')),200);

    }

    function get_lists_permissions($request){
        $body =json_decode($request->get_body(),true);
         if(!empty($body['token'])){
            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
            if(!empty($this->user)){
                return true;
            }
        }
       return false;
    }
}

Vibe_List_API::init();