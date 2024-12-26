<?php

defined( 'ABSPATH' ) or die();


//Scope => My , public, group,
// Contenxt => information: select dropdown, member card, groups directory, full profile view
if ( ! class_exists( 'Vibe_BP_API_Rest_Groups_Controller' ) ) {
	
	class Vibe_BP_API_Rest_Groups_Controller extends WP_REST_Controller{

		public static $instance;
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new Vibe_BP_API_Rest_Groups_Controller();
	        return self::$instance;
	    }
	    public function __construct( ) {
			$this->namespace = Vibe_BP_API_NAMESPACE;
			$this->type= Vibe_BP_API_GROUPS_TYPE;
			$this->register_routes();
		}
		/**
		 * Register the routes for the objects of the controller.
		 *
		 * @since 3.0.0
		 */
		public function register_routes() {

			register_rest_route( $this->namespace, '/'.$this->type, array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_groups' ),
					'permission_callback' => array( $this, 'get_public_groups_permissions' ),
				),
			));
			register_rest_route( $this->namespace, '/'.$this->type.'/group', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_group' ),
					'permission_callback' => array( $this, 'get_public_groups_permissions' ),
				),
			));
			register_rest_route( $this->namespace, '/'.$this->type.'/group/is_member', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'groups_is_user_member' ),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
				),
			));
			
			register_rest_route( $this->namespace, '/group_card/(?P<group_id>\d+)', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_public_groups_permissions' ),
					'callback'            =>  array( $this, 'get_group_card' ),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type.'/invites', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_groups_invites' ),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
				),
			));
			register_rest_route( $this->namespace, '/'.$this->type .'/(?P<group_id>\d+)?', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_get_group_by_id' ),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/member_actions', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'group_member_actions' ),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
				),
			));
			
			
			register_rest_route( $this->namespace, '/'.$this->type .'/create_update_group/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_groups_create_group'),
					'permission_callback' => array( $this, 'create_group_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace,'/'.$this->type . '/delete_group/(?P<group_id>\d+)?', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_groups_delete_group'),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));
			
			register_rest_route( $this->namespace,'/'.$this->type . '/join_group/(?P<group_id>\d+)/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_groups_join_group'),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
					'args'                     	=>  array(
						'group_id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace,'/'.$this->type . '/invite_member/(?P<group_id>\d+)/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_groups_invite_member'),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
					'args'                     	=>  array(
						'group_id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/leave_group/(?P<group_id>\d+)?/(?P<user_id>\d+)?', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_groups_leave_group'),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace,'/'.$this->type . '/members/(?P<group_id>\d+)?', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_groups_get_group_members'),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace,'/groups/user/(?P<user_id>\d+)/get_items', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'get_members_groups'),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
					'args'                     	=>  array(
						'user_id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));
			register_rest_route( $this->namespace,'/'.$this->type . '/user/(?P<user_id>\d+)/get_items', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'get_members_groups'),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
					'args'                     	=>  array(
						'user_id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));


			register_rest_route( $this->namespace,'/'.$this->type . '/invite/(?P<group_id>\d+)/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'accept_reject_invite'),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
					'args'                     	=>  array(
						'group_id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type.'/(?P<group_id>\d+)/access-details', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_group_access_details' ),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type.'/announcements', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_group_announcememts' ),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type.'/announcements/action', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'update_group_announcememts' ),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type.'/galleries', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_group_galleries' ),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type.'/gallery', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_group_gallery' ),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
				),
			));
			
			register_rest_route( $this->namespace, '/'.$this->type.'/gallery/save', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'save_group_gallery' ),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
				),
			));


			register_rest_route( $this->namespace, '/'.$this->type.'/galleries/action', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'update_group_galleries' ),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
				),
			));
			register_rest_route( $this->namespace, '/'.$this->type.'/import_users', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'import_group_users' ),
					'permission_callback' => array( $this, 'get_post_groups_permissions_check' ),
				),
			));
			register_rest_route( $this->namespace, '/'.$this->type.'/fetchcustomfields', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'fetchcustomfields' ),
					'permission_callback' => array( $this, 'get_groups_permissions' ),
				),
			));
			


		}


		/*
	    PERMISSIONS
	     */
	    function get_public_groups_permissions($request){

           	$client_id = sanitize_text_field($request->get_param('client_id'));
           	if($client_id == vibebp_get_setting('client_id')){
           		return true;
           	}
         	
         	return $this->get_groups_permissions($request);  
       	}
       	
       	function get_post_groups_permissions_check($request){
			
			$body =json_decode(stripslashes($_POST['body']),true);
			
			if(!empty($body['token'])){
	            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
	            if(!empty($this->user)){
	            	//User->roles , user->caps can be checked
	            	$this->user_id =$this->user->id;
	                return true;
	            }
	        }

	        return false;
		}

	    function get_groups_permissions($request){

	    	$body = json_decode($request->get_body(),true);
	       	$token= '';
	       	$token = sanitize_text_field($body['token']);
	        $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
            if(!empty($this->user)){
            	return true;
            }

	    	return false;
	    }

	    function create_group_permissions($request){
	    	$body = json_decode(stripslashes($_POST['body']),true);
	       	
	       	$token= '';
	       	$token = sanitize_text_field($body['token']);
	        $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
            if(!empty($this->user) && $this->bp_user_can_create_groups()){
            	return true;
            }

	    	return false;
	    	

	    	return false;
	    }

	    function bp_user_can_create_groups() {
			// Super admin can always create groups.
			if (!empty($this->user) && user_can($this->user->id,'manage_options')) {
				return true;
			}

			// Get group creation option, default to 0 (allowed).
			$restricted = (int) bp_get_option( 'bp_restrict_group_creation', 0 );

			// Allow by default.
			$can_create = true;

			// Are regular users restricted?
			if ( $restricted ) {
				$can_create = false;
			}

			/**
			 * Filters if the current logged in user can create groups.
			 *
			 * @since 1.5.0
			 *
			 * @param bool $can_create Whether the person can create groups.
			 * @param int  $restricted Whether or not group creation is restricted.
			 */
			if(!empty($this->user)){
				return apply_filters( 'vibebp_user_can_create_groups', $can_create, $restricted ,$this->user->id);
			}else{
				return apply_filters( 'vibebp_user_can_create_groups', $can_create, $restricted ,0);
			}
			
		}

		

		function get_group($request){
			$args = json_decode($request->get_body(),true);

	    	
	    	if(!empty($args['id'])){
	    		$group_id = intval($args['id']);
	    	}

	    	global $bp;
	    	$bp->groups->current_group = groups_get_group($group_id);
	    	$init = VibeBP_Init::init();
	    	$init->group = $bp->groups->current_group;
	    	$init->group_id = $group_id;
			$layout = new WP_Query(apply_filters('vibebp_group_layout_query',array(
				'post_type'=>'group-layout',
				'post_name'=>bp_get_group_type($bp->groups->current_group),
				'posts_per_page'=>1,
			)));

			if ( !$layout->have_posts() ){
				$layout = new WP_Query(array(
					'post_type'=>'group-layout',
					'post_name'=>bp_get_group_type($bp->groups->current_group),
					'posts_per_page'=>1,
					'meta_query'=>[
						'relation'=>'AND',
						[
							'key'=>'default_group-layout',
							'compare'=>'=',
							'value'=>1
						]
					]
				));
			}

			
			if ( !$layout->have_posts() ){

				$layout = new WP_Query(array(
					'post_type'=>'group-layout',
					'orderby'=>'date',
					'order'=>'ASC',
					'posts_per_page'=>1,
				));
			}

			
			ob_start();
			if ( $layout->have_posts() ) :
				
				/* Start the Loop */
				while ( $layout->have_posts() ) :
					$layout->the_post();
					
					the_content();
					break;
				endwhile;
			endif;
			$html = ob_get_clean();
			return new WP_REST_Response( $html, 200 );  
		}

		function get_members_groups($request){

			$member_groups = groups_get_user_groups($this->user->id);

			if(empty($member_groups)){
				return new WP_REST_Response( ['status'=>0,'message'=>__('User not member of any group','vibebp')], 200 );  
			}

			global $wpdb,$bp;
			$s = implode(',',$member_groups['groups']);
			$groups = $wpdb->get_results("
				SELECT id,name as label FROM {$bp->groups->table_name}
				WHERE id IN ($s)");

			return new WP_REST_Response( ['status'=>1,'groups'=>$groups], 200 );  

		}
    	function get_groups($request){

    		$args = json_decode(file_get_contents('php://input'),true);
    		if(!empty($this->user) && user_can($this->user->id,'manage_options')){
    			unset($args['user_id']);
    			$args['show_hidden'] = true;
    		}
    		if(!empty($args['filter']) && $args['filter']=='groups-my-groups'){
    			$args['show_hidden'] = true;
    		}
    		$args = apply_filters( 'vibe_bp_api_get_groups_args', $args, $request);
    		
    		$args=vibebp_recursive_sanitize_text_field($args);

    		if(!empty($args['meta'])){
				$args['meta_query']=array(
					'relation'=>'AND'
				);
				foreach($args['meta'] as $meta){
					if(!empty($meta['values']) && is_Array($meta['values'])){
						if($meta['type'] == 'number'){
							if(is_array($meta['values'])){
								if($meta['values'][1] > 0){
									$args['meta_query'][]=array(
										'key'=>$meta['id'],
										'compare'=>'BETWEEN',
										'value'=>$meta['values'],
									);	
								}
							}
							
						}else if($meta['type'] == 'date'){
							
							$meta['values'][0]=date('Y-m-d', $meta['values'][0]);
							$meta['values'][1]=date('Y-m-d', $meta['values'][1]);

							$args['meta_query'][]=array(
								'key'=>$meta['id'],
								'compare'=>'BETWEEN',
								'value'=>$meta['values'],
								'type' => 'DATE',
							);
						}else{
							$args['meta_query'][]=array(
								'key'=>$meta['id'],
								'compare'=>'IN',
								'value'=>$meta['values'],
							);
						}
						
					}else{
						$args['meta_query'][]=array(
							'key'=>$meta['id'],
							'compare'=>'=',
							'value'=>$meta['value'],
						);	
					}
					
				}
				
			}

    		$run = groups_get_groups($args); 
    		
    		if( count($run['groups']) ) {

    			foreach($run['groups'] as $k=>$group){
    				$run['groups'][$k]->avatar = esc_url(bp_core_fetch_avatar(array(
                            'item_id' => $run['groups'][$k]->id,
                            'object'  => 'group',
                            'type'=> empty($args->full_avatar)?'thumb':'full',
                            'html'    => false
                        )));
    				
					$run['groups'][$k]->description_raw = groups_get_groupmeta($run['groups'][$k]->id,'description_raw',true);
					
    				$run['groups'][$k]->group_types = $this->get_group_types($run['groups'][$k]->id);
    				$run['groups'][$k]->members = groups_get_group_members(array('group_id'=>$run['groups'][$k]->id,'per_page'=>5,'type'=>'last_joined','exclude_admins_mods'=>false));
    				$run['groups'][$k]->url = bp_get_group_permalink($group);
    				$istatus =  groups_get_groupmeta($run['groups'][$k]->id,'invite_status',true);
    				$run['groups'][$k]->invite_status = (empty($istatus)?'members':$istatus);
    			}

    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Groups Exist','Groups Exist','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Groups Not Exist','Groups Not Exist','vibebp')
	    		);
    	    }

    	    $data['can_create_groups'] = $this->bp_user_can_create_groups();

    		$data = apply_filters( 'vibe_bp_api_get_groups', $data, $args, $request);
			return new WP_REST_Response( $data, 200 );  
    	}

    	function get_group_types($group_id){
    		$types = array();
    		$_types = bp_groups_get_group_type($group_id,false);
    		if(!empty($_types)){
    			foreach ($_types as $key => $type) {
    				$types[]=bp_groups_get_group_type_object($type);
    			}
    		}
    		return $types;
    	}

    	function get_group_card($request){
    		$group_id = $request->get_param('group_id');
    		$layouts = new WP_Query(apply_filters('vibebp_group_card',array(
				'post_type'=>'group-card',
				'posts_per_page'=>1,
				'meta_query'=>array(
					'relation'=>'AND',
					array(
						'key'=>'group_type',
						'compare'=>'NOT EXISTS'
					)
				)
			),$group_id));
			$init = VibeBP_Init::init();
			$init->group_id = $group_id;
			ob_start();

			if($layouts->have_posts()){
				while($layouts->have_posts()){
					$layouts->the_post();
					echo '<div class="group_card_'.$post->post_name.'">';
					the_content();
					echo '</div>';
				}
			}else{
				$layouts = new WP_Query(array(
					'post_type'=>'group-card',
					'posts_per_page'=>1,
					'meta_query'=>array(
						'relation'=>'AND',
						array(
							'key'=>'group_type',
							'compare'=>'NOT EXISTS'
						)
					)
				));
				while($layouts->have_posts()){
					$layouts->the_post();
					global $post;
					echo '<div class="group_card_'.$post->post_name.'">';
					the_content();
					echo '</div>';
				}
			}
			return ob_get_clean();
    	}

    	function get_groups_invites($request){

    		$args = json_decode($request->get_body(),true);

    		$args = apply_filters( 'vibe_bp_api_get_groups_args', $args, $request);

    		$args=vibebp_recursive_sanitize_text_field($args);

    		if($args['accepted'] == 'requests'){
    			$pending_invites = groups_get_requests( array(
					'user_id'  => $this->user->id,
					'page'     => $args['page'],
					'per_page' => 12,
				) );
				
    		}
    		if($args['accepted'] == 'pending'){
    			$pending_invites = groups_get_invites( array(
					'user_id'  => $this->user->id,
					'page'     => $args['page'],
					'per_page' => 12,
				) );
    			$run['total']=groups_get_invite_count_for_user($this->user->id);
    		}

    		if($args['accepted'] == 'pending_sent_invitation'){
			
				$pending_invites =  groups_get_invites( array(
					'inviter_id'  => $user_id,
					'page'        => $r['page'],
					'per_page'    => $r['per_page'],
				) );
			}
			

    		$run = array();
			

    		if(!empty($pending_invites)){

    			

    			foreach($pending_invites as $invite){
    				$group = groups_get_group($invite->item_id);

    				if(!is_wp_error($group) && !empty($group)){
    					
    					$invite->date_modified = strtotime($invite->date_modified);
    					$invite->item=$group;
    					$invite->item->avatar =esc_url(bp_core_fetch_avatar(array(
		                            'item_id' => $invite->item_id,
		                            'object'  => 'group',
		                            'type'=>'thumb',
		                            'html'    => false
		                        )));
    						
    					$run['invites'][] = $invite;
    				}
    			}

    		}
    		

    		if( !empty($run) ) {
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Invites Exist','Groups Exist','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Invites empty','Invites Not Exist','vibebp')
	    		);
    	    }
    		$data = apply_filters( 'vibe_bp_api_get_groups', $data, $args, $request);
			return new WP_REST_Response( $data, 200 );   
    	}
/*
*	Get Group by group_id
*/
    	function vibe_bp_api_get_group_by_id($request){

    		$group_id = (int)$request->get_param('group_id');
    		$args = json_decode($request->get_body(),true);
    		$args = vibebp_recursive_sanitize_text_field($args);
    		$data = '';
    		if($args['context'] === 'meta'){
    			$data = groups_get_groupmeta( $group_id, '', false);
    		}

    		$tabs = apply_filters('vibebp_group_tabs',array(
                    'home'=>_x('Home','groups','vibebp'),
                ),$group_id,$this->user->id);

    		$meta['is_admin'] = $meta['can_add_members'] = $meta['can_invite'] = false;
    		if(user_can($this->user->id,'manage_options') ){
    			$meta['is_admin']  = $meta['can_add_members'] = true;
    		}else{
    			$admins = groups_get_group_admins($group_id);
    			if(!empty($admins)){
					foreach ($admins as $key => $mod) {
						if($mod->user_id==$this->user->id){
							$meta['is_admin']  = $meta['can_add_members'] = true;
							
							break;
						}
					}
				}
				if ( $group_mods = groups_get_group_mods( $group_id ) ) {
					foreach ( (array) $group_mods as $mod ){
						if($mod->user_id==$this->user->id){
							$meta['is_admin']  = $meta['can_add_members'] = true;
							
							break;
						}
					}
	    		}
    		}
    		$invite_status = groups_get_groupmeta($group_id,'invite_status',true);
    		

			if(!empty($invite_status)){
				switch ($invite_status) {
					case 'admins':
						if($meta['is_admin']){
							$meta['can_invite'] = true;
						}
						break;
					case 'mods':
						$mods = groups_get_group_mods( $group_id );
						if(!empty($mods)){
							foreach ($mods as $key => $mod) {
								if($mod->user_id==$this->user->id){
									$meta['can_invite'] = true;
									break;
								}
							}
						}
						if($meta['is_admin']){
							$meta['can_invite'] = true;
						}
						break;
					case 'members':
						$meta['can_invite'] = true;

					break;
					default:
						
						break;
				}
			}
			$meta['is_member'] = groups_is_user_member($this->user->id,$group_id);
    		$meta['is_admin'] = apply_filters('vibebp_groups_api_is_admin',$meta['is_admin'],$group_id,$this->user->id);
			if(!empty($meta['is_admin'])){
				$meta['is_member'] = true;
			}

    		$meta['can_add_members'] = apply_filters('vibebp_groups_api_can_add_members',$meta['can_add_members'],$group_id,$this->user->id);
    		$meta['can_invite'] = apply_filters('vibebp_groups_api_can_invite',$meta['can_invite'],$group_id,$this->user->id);
    		if(!empty($meta['is_admin']) || !empty($meta['can_add_members'])){
    			$meta['is_member'] = true;
    		}
    		$data = array_merge($data,$meta);
    		$cover = esc_url(bp_attachments_get_attachment('url', array(
				          'object_dir' => 'groups',
				          'item_id' => $group_id,
				    )));
    		
    		
			if( $group_id ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $data,
	    			'tabs' => $tabs,
	    			'message' => _x('Group Exist','Group Exist','vibebp')
	    		);
	    		if(!empty($cover )){
	    			$data['cover'] = $cover;
	    		}
    	    }else{
    	    	
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $data,
	    			'tabs' => $tabs,
	    			'message' => _x('No Data for Group','Group Not Exist','vibebp')
	    		);
    	    }	
    	    $data=apply_filters( 'vibe_bp_api_get_group_by_id', $data, $request);
			return new WP_REST_Response( $data, 200 );   
    	}

    	function group_member_actions($request){

    		$args = json_decode($request->get_body(),true);
    		$args = vibebp_recursive_sanitize_array_field($args);
    		if(empty($args['members'])){
    			return new WP_REST_Response( array('status'=>0,'message'=>__('No members selected','vibebp')), 200 );   
    		}
    		$group_id = intval($args['group_id']);
    		$members = $args['members'];
    		switch($args['action']){
    			case 'remove':
    				foreach($members as $user_id){
    					$member = new BP_Groups_Member( $user_id, $group_id );	
    					do_action( 'groups_remove_member', $group_id, $user_id );
    					$member->remove();
    					$args['action']= __('Member removed','group action','vibebp');
    					vibebp_fireabase_update_stale_requests($user_id,'groups');
    				}
    			break;
    			case 'ban':
    				foreach($members as $user_id){
    					$member = new BP_Groups_Member( $user_id, $group_id );
						do_action( 'groups_ban_member', $group_id, $user_id );
						$member->ban();
						$args['action']= __('Member banned','group action','vibebp');
					}
    			break;
    			case 'unban':
    				foreach($members as $user_id){
    					$member = new BP_Groups_Member( $user_id, $group_id );
						do_action( 'groups_unban_member', $group_id, $user_id );
						$member->unban();
						$args['action']= __('Member unbanned','group action','vibebp');
					}
    			break;
    			case 'change_role_member':
    				foreach($members as $user_id){
    					$member = new BP_Groups_Member( $user_id, $group_id );
						do_action( 'groups_demote_member', $group_id, $user_id );
						$member->demote();
						$args['action']= __('Member demoted','group action','vibebp');
						vibebp_fireabase_update_stale_requests($user_id,'groups');
					}
    			break;
    			case 'change_role_moderator':
    				foreach($members as $user_id){
    					$member = new BP_Groups_Member( $user_id, $group_id );
						do_action( 'groups_promote_member', $group_id, $user_id, 'mod' );
						$member->promote( 'mod' );
						$args['action']= __('Member promoted','group action','vibebp');
						vibebp_fireabase_update_stale_requests($user_id,'groups');
					}
    			break;
    			case 'change_role_admin':
    				foreach($members as $user_id){
    					$member = new BP_Groups_Member( $user_id, $group_id );
						do_action( 'groups_promote_member', $group_id, $user_id, 'admin' );
						$member->promote( 'admin' );
						$args['action']= __('Member converted to administrator ','group action','vibebp');
						vibebp_fireabase_update_stale_requests($user_id,'groups');
					}
    			break;
    			case 'accept_request':
    					$invitation = new BP_Groups_Invitation_Manager();
    					if(!empty($members)){
    						foreach($members as $user_id){
								groups_accept_membership_request('', $user_id, $group_id);
							}
						}
						$args['action']= __('Membership accepted ','group action','vibebp');

    			break;
    			case 'reject_request':
    				if(!empty($members)){
    					foreach($members as $user_id){
    						groups_reject_membership_request('', $user_id, $group_id);
    						vibebp_fireabase_update_stale_requests($user_id,'groups');
							vibebp_fireabase_update_stale_requests($user_id,'invites');
    					}
    				}
    				
    				$args['action']= __('Membership rejected ','group action','vibebp');
    			break;
    			case 'leave_group':
    				if ( groups_is_user_member( $this->user->id, $group_id ) ) {
						$group_admins = groups_get_group_admins( $group_id );

						if ( 1 == count( $group_admins ) && $group_admins[0]->user_id == $this->user->id ) {
							return new WP_REST_Response( array('status'=>0,'message'=>__('This group must have at least one admin','vibebp')), 200 );   

						} elseif ( ! groups_leave_group( $group_id , $this->user->id) ) {
							return new WP_REST_Response( array('status'=>0,'message'=>__('There was an error leaving the group.', 'vibebp' )), 200 );  
						} else {
							return new WP_REST_Response( array('status'=>1,'message'=>__('You successfully left the group.', 'vibebp' )), 200 );  
						}
						vibebp_fireabase_update_stale_requests($this->user->id,'groups');
					}else{
						return new WP_REST_Response( array('status'=>0,'message'=>__('You are not a member of the group.', 'vibebp' )), 200 );
					}
    			break;
    			case 'join_group':
    				if ( !groups_is_user_member( $this->user->id, $group_id ) ) {
						groups_join_group($group_id,$this->user->id);
						vibebp_fireabase_update_stale_requests($this->user->id,'groups');
						
					}
					return new WP_REST_Response( array('status'=>1,'message'=>_x('Group joined!','','vibebp') ), 200 );
    			break;
    		}

    		return new WP_REST_Response( array('status'=>1,'message'=>sprintf(__('%s performed on %d selected members','vibebp'),$args['action'],count($args['members'])) ), 200 );   
		}


    	function vibe_bp_api_groups_create_group($request){
    		
    		$args = json_decode(stripslashes($_POST['body']),true);
    		$args = vibebp_recursive_sanitize_array_field($args);

    		$group_args = array(
    			'name'=> $args['name'],
    			'slug'=> sanitize_title_with_dashes($args['name']),
    			'description'=>$args['description'],
    			'status' => $args['status'],
    			'creator_id' => $this->user->id
    		);

    		if(!empty($args['id'])){
    			$group_id = $group_args['group_id'] = $args['id'];
    		}


		    $group_args=apply_filters( 'vibe_bp_api_groups_create_group_args', $group_args, $request);
		    remove_all_filters('groups_group_description_before_save');

			$group_id = groups_create_group($group_args);

			if(!empty($args['group_type'])){
    			bp_groups_set_group_type($group_id, $args['group_type'] );
    		}

			if ( bp_is_active( 'activity' ) ) {
				if(!empty($group_args['group_id'])){
					groups_record_activity( array(
						'type' => 'group_details_updated',
						'item_id' => $group_id,
						'user_id' => $this->user->id
					) );
				}else{
					groups_record_activity( array(
						'type' => 'created_group',
						'item_id' => $group_id,
						'user_id' => $this->user->id
					) );
				}
			}

			$this->group_id = $group_id;

			add_filter('bp_get_current_group_id',array($this,'set_group_id'));
			

			$run = false;
			if(is_numeric($group_id)){
				if(!empty($args['description_raw'])){
					groups_update_groupmeta($group_id,'description_raw',$args['description_raw']);
				}
				if(!empty($args['invite_status'])){
					groups_update_groupmeta( $group_id, 'invite_status', $args['invite_status'] );
				}
				// assign to coure or forum
				if(!empty($args['meta'])){
					foreach($args['meta'] as $i => $meta){

						$flag = 1;
						if($meta['meta_key'] == 'forum_id'){
							if(!empty($meta['meta_value'])){
								
								if($meta['meta_value'] == 'new' && function_exists('bbp_insert_forum')){

									$forum_parent_id = vibebp_get_setting('bbp_parent_forum','helpdesk');
					                	
				                    $forum_data = apply_filters( 'bbp_new_forum_pre_insert', array(
				                        'post_author'    => $this->user->id,
				                        'post_title'     => $args['name'],
				                        'post_content'   => $args['description'],
				                        'post_parent'    => $forum_parent_id,
				                        'post_status'    => 'private',
				                        'post_type'      => bbp_get_forum_post_type(),
				                        'comment_status' => 'closed'
				                    ) );
				                    // Insert forum
				                    $ar=[];
				                    if(!empty($forum_parent_id)){
				                    	$ar=['forum_id'=>$forum_parent_id];
				                    }
				                    $forum_id = bbp_insert_forum( $forum_data, $ar );
				                    if(is_wp_error($forum_id)){
				                    	$flag=0;
				                    }else{
				                    	$meta['meta_value'] = $forum_id;	
				                    }
					                    
					                

								}

								groups_edit_group_settings( $group_id, 1,$args['status']);
							}else{
								groups_edit_group_settings( $group_id, 0,$args['status']);
							}
						}
						
						if($flag){
							groups_update_groupmeta( $group_id, $meta['meta_key'], $meta['meta_value'] );	
						}
						
						if(!empty($args['editors'])){
							foreach($args['editors'] as $editor){
								if(is_Array($editor) && !empty($editor['value'])){
									groups_update_groupmeta( $group_id, $editor['id'], $editor['value'] );
									groups_update_groupmeta( $group_id, $editor['id'].'_raw', $editor['raw'] );
								}
							}
						}

						
					}
				}
				$bp = buddypress();
				//Asign avatr
				if ( ! isset( $bp->avatar_admin ) ) {
					$bp->avatar_admin = new stdClass();
				}
				

				if ( !empty( $_FILES )  ) {
					add_filter('bp_attachment_upload_overrides',function($overrides){
						$overrides['test_form'] = FALSE;
						return $overrides;
					});
 					
 					global $bp;
					$bp->groups->current_group = groups_get_group($group_id);
 
					if (  bp_core_avatar_handle_upload( $_FILES, 'groups_avatar_upload_dir' ) ) { 
						// Normally we would check a nonce here, but the group save nonce is used instead.

						$cropargs = array(
							'object'        => 'group',
							'avatar_dir'    => 'group-avatars',
							'item_id'       => $group_id,
							'original_file' => $bp->avatar_admin->image->url,
							'crop_x'        => $args['avatar']['cropdata']['x'],
							'crop_y'        => $args['avatar']['cropdata']['y'],
							'crop_w'        => $args['avatar']['cropdata']['width'],
							'crop_h'        => $args['avatar']['cropdata']['height']
						);

						vibebp_avatar_handle_crop($cropargs,$this->user->id); 
						
					}
				}

			    //send invites

				if(!empty($args['invitees'])){

					foreach ( $args['invitees'] as $user_id ) {
						groups_invite_user( array( 
							'user_id'  => $user_id,
							'group_id' => $group_id,
						) );
					}
					groups_send_invites( array(	'group_id' => $group_id ) );
				}

				$run =groups_get_group( $group_id );
				$run->avatar =bp_core_fetch_avatar(array(
                            'item_id' 	=> $group_id,
                            'object'  	=> 'group',
                            'type'		=>'thumb',
                            'html'    	=> false
                        ));
				$cover = bp_attachments_get_attachment('url', array(
				          'object_dir' => 'groups',
				          'item_id' => $group_id,
				    ));

				if(!empty($cover)){
					$run->cover =$cover;
				}
				if(!empty($args['description_raw'])){
					$run->description_raw =$args['description_raw'];
				}
			}

			if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Group Created','Group Created','vibebp')
	    		);
	    		vibebp_fireabase_update_stale_requests($this->user->id,'groups');
	    		vibebp_fireabase_update_stale_requests($this->user->id,'group/'.$group_id);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Group Not Created','Group Not Created','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_groups_create_group', $data, $request ,$args );
			return new WP_REST_Response( $data, 200 );   
    	}

    	function set_group_id($gid){
    		$this->group_id = $gid;
			return $this->group_id;
		}

		function gid($x){
			return $this->group_id;
		}


    	function vibe_bp_api_groups_delete_group($request){
			$group_id = (int)$request->get_param('group_id');	 // get param data 'group_id
			$run=groups_delete_group( $group_id );
    	   
    	    if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Group Deleted','Group Deleted','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Group Not Deleted','Group Not Deleted','vibebp')
	    		);
    	    }

    		$data=apply_filters( 'vibe_bp_api_groups_delete_group', $data ,$request);
			return new WP_REST_Response( $data, 200 );  
    	}

    	function vibe_bp_api_groups_join_group($request){

    		$args = json_decode(file_get_contents('php://input'));
    		$group_id = (int)$request->get_param('group_id');	 // get param data 'group_id
    		$members = $args->invitees;$run=0;
    		$members = vibebp_recursive_sanitize_text_field($members);
    		$can_add_members= false;

    		if(user_can($this->user->id,'manage_options') ){
    			$can_add_members = true;
    		}else{
    			$admins = groups_get_group_admins($group_id);
    			if(!empty($admins)){
					foreach ($admins as $key => $mod) {
						if($mod->user_id==$this->user->id){
							$can_add_members = true;
							
							break;
						}
					}
				}
				if (!$can_add_members && $group_mods = groups_get_group_mods( $group_id ) ) {
					foreach ( (array) $group_mods as $mod ){
						if($mod->user_id==$this->user->id){
							$can_add_members = true;
							
							break;
						}
					}
	    		}
    		}

    		$can_add_members = apply_filters('vibebp_groups_api_can_add_members',$can_add_members,$group_id,$this->user->id,$members);
    		if(!$can_add_members){
    			$data=array(
	    			'status' => 0,
	    			'message' => apply_filters('vibebp_groups_can_members_message',_x('Unable to add members!','invited for group','vibebp'),$can_add_members,$group_id,$this->user->id,$members)
	    		);
				return new WP_REST_Response( $data, 200 );  
    		}
    		if(!empty($members)){
    			foreach ($members as $key => $user_id) {
    				$run = groups_join_group( $group_id, $user_id);
    			}
    		}
    		

    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Group Joined','Group Joined','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Group Not Joined','Group Not Joined','vibebp')
	    		);
    	    }
    	    
    		$data=apply_filters( 'vibe_bp_api_groups_join_group', $data ,$request);
			return new WP_REST_Response( $data, 200 );  
    	}

    	function vibe_bp_api_groups_invite_member($request){

    		$args = json_decode($request->get_body(),true);
    		$group_id = (int)$request->get_param('group_id');	 // get param data 'group_id
    		$args = vibebp_recursive_sanitize_text_field($args);
    		if(!empty($args['invitees'])){

				foreach ( $args['invitees'] as $user_id ) {
					groups_invite_user( array( 
						'user_id'  => $user_id,
						'group_id' => $group_id,
					) );
					vibebp_fireabase_update_stale_requests($user_id,'invites');
				}

				groups_send_invites( array(	'group_id' => $group_id ) );
			}

			$data=array(
    			'status' => 1,
    			'message' => _x('Users Invited','invited for group','vibebp')
    		);

			return new WP_REST_Response( $data, 200 );  
    	}


    	function vibe_bp_api_groups_leave_group($request){
    		$args = json_decode(file_get_contents('php://input'));
    		$group_id = (int)$request->get_param('group_id');	 // get param data 'group_id
    		$user_id = (int)$request->get_param('user_id');	 // get param data 'user_id'
    		$args = vibebp_recursive_sanitize_text_field($args);
    		$run = groups_leave_group( $group_id, $user_id);
    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Group leaved','Group leaved','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Group Not leaved','Group Not leaved','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_groups_leave_group', $data ,$request);
			return new WP_REST_Response( $data, 200 );  
    	}

    	function vibe_bp_api_groups_get_group_members($request){

 
    		$args = json_decode($request->get_body(),true);
    		$group_id = (int)$request->get_param('group_id');	 // get param data 'group_id
    		$args = vibebp_recursive_sanitize_text_field($args);
    		$group_args = array(
    			'group_id'=>$group_id,
    			'per_page'=>999999,
    			'page'=>1,
    			'exclude_admins_mods'=>false,
    			'group_role'=>array($args['role']), //'admin', 'mod', 'member', 'banned'
    			'search_terms'=>$args['search_terms'],
    			'type'=>'last_joined'
    		);

    		$run = array();
			if($args['role'] == 'invited'){
				$invites = groups_get_invites( array('item_id'=> $group_id));

				if(!empty($invites)){
					$run = array('members'=>array());
					foreach($invites as $invite){
						$run['members'][]=array(
							'ID'=>$invite->user_id,
							'display_name'=> bp_core_get_user_displayname($invite->user_id)
						);
					}
					$run['count']=count($invites);
				}else{
					$run['members']=[];
					$run['count']=0;
				}
			}else if($args['role'] == 'requests'){
				$user_ids = groups_get_membership_requested_user_ids($group_id);

				if(!empty($user_ids)){
					$run = array('members'=>array());
					foreach($user_ids as $user_id){
						$run['members'][]=(object)array(
							'ID'=>$user_id,
							'display_name'=> bp_core_get_user_displayname($user_id)
						);
					}
					$run['count']=count($user_ids);
				}else{
					$run['members']=[];
					$run['count']=0;
				}
			}else{
				$run = groups_get_group_members( $group_args );
			}
    		

    		if(!empty($run['members'])){
    			foreach($run['members'] as $key => $user){
    				if(is_array($run['members'][$key]) && is_array($user)){
    					$run['members'][$key]['avatar'] = bp_core_fetch_avatar(array(
                            'item_id' 	=> $user['ID'],
                            'object'  	=> 'user',
                            'type'		=>'thumb',
                            'html'    	=> false
                    	));
    				}else{
    					$run['members'][$key]->avatar = bp_core_fetch_avatar(array(
                            'item_id' 	=> $user->ID,
                            'object'  	=> 'user',
                            'type'		=>'thumb',
                            'html'    	=> false
                    	));
    				}
					
                    
					
				}
			}

    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Group Members','Group Members','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Group Not Members','Group Not Members','vibebp')
	    		);
    	    }

    	    
			$data['meta'] = vibe_get_group_meta_permissions($group_id,$this->user->id);
    		$data=apply_filters( 'vibe_bp_api_groups_get_group_members', $data ,$request);
			return new WP_REST_Response( $data, 200 );  
    	}

    	function accept_reject_invite($request){

    		$body = json_decode($request->get_body(),true);
    		$group_id = (int)$request->get_param('group_id');
    		$data = array(
    			'status'=>0,
    			'message'=>__('User not logged in','vibebp'),
    		);
    		$body = vibebp_recursive_sanitize_text_field($body);
    		if(empty($this->user)){
    			return new WP_REST_Response( $data, 200 );  
    		}

    		if($body['action'] === 'accept'){
    			$data['status']=1;
    			$data['message']= __('Invitation Accepted','vibebp');
    			groups_accept_invite( $this->user->id, $group_id );
    			vibebp_fireabase_update_stale_requests($this->user->id,'invites');
    			vibebp_fireabase_update_stale_requests($this->user->id,'groups');
    		}

    		if($body['action'] === 'reject'){
    			$data['status']=1;
    			$data['message']= __('Invitation Rejected','vibebp');
    			groups_reject_invite( $this->user->id, $group_id );
    			vibebp_fireabase_update_stale_requests($this->user->id,'invites');
    		}

    		if($body['action'] === 'cancel' || $body['action'] === 'delete'){
    			$data['status']=1;
    			$data['message']= __('Invitation removed','vibebp');
    			groups_delete_invite( $this->user->id, $group_id);
    			vibebp_fireabase_update_stale_requests($this->user->id,'invites');

    		}

    		return new WP_REST_Response($data, 200 );  
    	}

		function get_group_access_details($request){
			$body = json_decode($request->get_body(),true);
    		$group_id = (int)$request->get_param('group_id');
    		$body = vibebp_recursive_sanitize_text_field($body);
    		$data = array(
    			'status'=>1,
				'data' => vibe_get_group_meta_permissions($group_id,(int)$this->user->id)
    		);
			return new WP_REST_Response($data, 200 );  
		}

		function get_group_announcememts($request){
			$body = json_decode($request->get_body(),true);
			$data = array('status' => 0);
			if(!empty($body['group_id']) && vibebp_can_user_view_group($body['group_id'],(int)$this->user->id)){
				$group_id = (int)$body['group_id'];
				global $wpdb,$bp;
				$query = $wpdb->prepare("SELECT gm.id as meta_id ,gm.meta_value as meta_value FROM {$bp->groups->table_name_groupmeta} as gm WHERE gm.meta_key='vibe_group_annoucement' AND gm.group_id = %d",$group_id);
				$results = $wpdb->get_results($query,ARRAY_A);
				if(!empty($results)){
					$data = array(
						'status' => 1,
						'data' => array_map(function ($a) {
							$a['meta_id'] = (int)$a['meta_id'];
							return $a;
						}, $results)
					);
				}
			}
			return new WP_REST_Response($data, 200 );  
		}
		function update_group_announcememts($request){
			$body = json_decode($request->get_body(),true);
			$data = array('status' => 0);

			if(!empty($body['group_id']) && vibebp_can_user_edit_group($body['group_id'],(int)$this->user->id && !empty($body['action']))){
				$group_id = (int)$body['group_id'];
				global $wpdb,$bp;
				switch ($body['action']) {
					case 'create':
							if(!empty($body['meta_value'])){
								$data['message'] = __('Not Created!','vibebp');
								$meta_id = groups_add_groupmeta($group_id,'vibe_group_annoucement',$body['meta_value']);
								if(!empty($meta_id)){
									$arr = array(
										'meta_id' => (int)$meta_id,
										'meta_value' => $body['meta_value']
									);
									do_action('vibe_group_annoucement',$body['action'],$group_id,$arr);
									$data = array(
										'status' => 1,
										'data' => $arr,
										'message' => __('Created!','vibebp')
									);
								}
							}
						break;
					case 'edit':
							$data['message'] = __('Not updated!','vibebp');
							if(!empty($body['meta_id']) && isset($body['meta_value'])){
								$query = $wpdb->prepare("UPDATE {$bp->groups->table_name_groupmeta} as gm SET gm.meta_value = %s WHERE gm.meta_key = 'vibe_group_annoucement' AND gm.id = %d AND gm.group_id = %d",$body['meta_value'],$body['meta_id'],$group_id);
								$results = $wpdb->query($query);
								
								
								if(!empty($results)){
									do_action('vibe_group_annoucement',$body['action'],$group_id,$arr);
									$data = array(
										'status' => 1,
										'data' => array(
											'meta_id' => (int)$body['meta_id'],
											'meta_value' => $body['meta_value']
										),
										'message' => __('Updated!','vibebp')
									);
								}
							}
						break;
					case 'delete':
							$data['message'] = __('Not Deleted!','vibebp');
							if(!empty($body['meta_id'])){
								$query = $wpdb->prepare("DELETE FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'vibe_group_annoucement' AND id = %d AND group_id = %d",$body['meta_id'],$group_id);
								$results = $wpdb->query($query);
								if(!empty($results)){
									do_action('vibe_group_annoucement',$body['action'],$group_id,array('meta_id'=>$body['meta_id']));
									$data = array(
										'status' => 1,
										'data'=> array(
											'meta_id' => (int)$body['meta_id'],
											'meta_value' => $body['meta_value']
										),
										'message' => __('Deleted!','vibebp')
									);
								}
							}
						break;
					default:
						break;
				}
			}
			return new WP_REST_Response($data, 200 );  
		}
		

		function get_group_galleries($request){
			$body = json_decode($request->get_body(),true);
			$data = array('status' => 0);
			if(!empty($body['group_id']) && vibebp_can_user_view_group($body['group_id'],(int)$this->user->id)){
				$group_id = (int)$body['group_id'];
				global $wpdb,$bp;
				$query = $wpdb->prepare("SELECT gm.id as meta_id ,gm.meta_value as meta_value FROM {$bp->groups->table_name_groupmeta} as gm WHERE gm.meta_key='vibe_group_gallary' AND gm.group_id = %d",$group_id);
				$results = $wpdb->get_results($query,ARRAY_A);
				if(!empty($results)){
					$data = array(
						'status' => 1,
						'data' => array_map(function ($a) {
							$a['meta_id'] = (int)$a['meta_id'];
							return $a;
						}, $results)
					);
				}
			}
			return new WP_REST_Response($data, 200 );  
		}

		function update_group_galleries($request){
			$body = json_decode($request->get_body(),true);
			$data = array('status' => 0);

			if(!empty($body['group_id']) && vibebp_can_user_edit_group($body['group_id'],(int)$this->user->id && !empty($body['action']))){
				$group_id = (int)$body['group_id'];
				global $wpdb,$bp;
				switch ($body['action']) {
					case 'create':
							if(!empty($body['meta_value'])){
								$data['message'] = __('Not Created!','vibebp');
								$meta_id = (int)groups_add_groupmeta($group_id,'vibe_group_gallary',$body['meta_value']);
								if(!empty($meta_id)){
									$arr = array(
										'meta_id' => $meta_id,
										'meta_value' => $body['meta_value']
									);
									do_action('vibe_group_gallary',$body['action'],$group_id,$arr);
									$data = array(
										'status' => 1,
										'data' => $arr,
										'message' => __('Created!','vibebp')
									);
								}
							}
						break;
					case 'edit':
							$data['message'] = __('Not updated!','vibebp');
							if(!empty($body['meta_id']) && isset($body['meta_value'])){
								$query = $wpdb->prepare("UPDATE {$bp->groups->table_name_groupmeta} as gm SET gm.meta_value = %s WHERE gm.meta_key = 'vibe_group_gallary' AND gm.id = %d AND gm.group_id = %d",$body['meta_value'],$body['meta_id'],$group_id);
								$results = $wpdb->query($query);
								if(!empty($results)){
									do_action('vibe_group_gallary',$body['action'],$group_id,$arr);
									$data = array(
										'status' => 1,
										'data' => array(
											'meta_id' => (int)$body['meta_id'],
											'meta_value' => $body['meta_value']
										),
										'message' => __('Updated!','vibebp')
									);
								}
							}
						break;
					case 'delete':
							$data['message'] = __('Not Deleted!','vibebp');
							if(!empty($body['meta_id'])){
								$query = $wpdb->prepare("DELETE FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'vibe_group_gallary' AND id = %d AND group_id = %d",$body['meta_id'],$group_id);
								$results = $wpdb->query($query);
								if(!empty($results)){
									do_action('vibe_group_gallary',$body['action'],$group_id,array('meta_id'=>$body['meta_id']));
									$data = array(
										'status' => 1,
										'data'=> array(
											'meta_id' => (int)$body['meta_id'],
											'meta_value' => $body['meta_value']
										),
										'message' => __('Deleted!','vibebp')
									);
								}
							}
						break;
					default:
						break;
				}
			}
			return new WP_REST_Response($data, 200 ); 
		}

		function get_group_gallery($request){
			$body = json_decode($request->get_body(),true);
			$data = array('status' => 0);
			if(!empty($body['group_id']) && !empty($body['meta_id']) && vibebp_can_user_view_group($body['group_id'],(int)$this->user->id)){
				$group_id = (int)$body['group_id'];
				$meta_id = (int)$body['meta_id'];
				global $wpdb,$bp;
				$query = $wpdb->prepare("SELECT gm.id as meta_id ,gm.meta_value as meta_value FROM {$bp->groups->table_name_groupmeta} as gm WHERE gm.meta_key='vibe_group_gallary_%d' AND gm.group_id = %d",$meta_id,$group_id);
				$results = $wpdb->get_results($query,ARRAY_A);
				if(!empty($results)){
					$data = array(
						'status' => 1,
						'data' => array_map(function ($a) {
							$a['meta_id'] = (int)$a['meta_id'];
							$a['meta_value'] = array(
								'id' => (int)$a['meta_value'],
								'url' => wp_get_attachment_url((int)$a['meta_value'])
							);
							return $a;
						}, $results)
					);
				}
			}
			return new WP_REST_Response($data, 200 );  
		}

		function save_group_gallery($request){
			$body = json_decode($request->get_body(),true);
			$data = array('status' => 0);
			
			if(!empty($body['group_id']) && !empty($body['meta_id']) && vibebp_can_user_edit_group($body['group_id'],(int)$this->user->id)){
				$meta_key = 'vibe_group_gallary_'.$body['meta_id'];
				groups_delete_groupmeta($body['group_id'],$meta_key);
				foreach ($body['items'] as $key => $value) {
					groups_add_groupmeta($body['group_id'],$meta_key,(int)$value);
				}
				$data = array('status' => 1,'message'=>__('Gallary items updated!','vibebp'));
			}
			return new WP_REST_Response($data, 200 );  
		}

		function groups_is_user_member($request){
			$body = json_decode($request->get_body(),true);
			$group_id = $body['group_id'];
			return new WP_REST_Response(array('status'=>groups_is_user_member($this->user->id,intval($group_id))), 200 );  
		}

		function import_group_users($request){
			$body =json_decode(stripslashes($_POST['body']),true);
			$group_id = $body['group_id'];
			if(!empty(vibe_get_group_meta_permissions($group_id,$this->user->id)['can_add_members'])){
				if(!empty($_FILES['file'])){
					global $wpdb;
					$file = $_FILES['file']['tmp_name'];
					$labels = 0;
					if (($handle = fopen($file, "r")) !== FALSE) {
					    while ( ($data = fgetcsv($handle,1000,",") ) !== FALSE ) {
					    	if($labels){
					    		$email = $data[0];
					    		if(!empty($email) && strpos($email, '@') !== false){
					    			$name = $data[1];
						    		$user_id = $this->check_user($email,$name);
						    		if($user_id){
						    			groups_join_group($group_id,$user_id);
						    		}
					    		}
					    	}else{ //Skips the first row/
					    		$labels = 1;
					    	}
					    }
					    fclose($handle);
					    $return=array('status'=> 1,'message'=>_x('Users Imported','wplms'));
					}
					$this->check_user($email,$name);
				}else{
	            	$return=array('status'=> 0,'message'=>_x('File not found','wplms'));
	        	}
			}else{
	            	$return=array('status'=> 0,'message'=>_x('You cannot add members to this group!','wplms'));
			}
			
        	return new WP_REST_Response( $return, 200 );
		}

		function check_user($email,$name){
			$exists = email_exists($email);
			if($exists){
				return $exists;// Map new user via Email
			}else{
				$username = sanitize_title($name);
				if(username_exists($username))
					$username.=rand(1,99);

				$default_pass = apply_filters('wplms_user_pass',$username,$email);
				$userdata = array(
				    'user_login'  =>  $username,
				    'user_email'  =>  $email,
				    'display_name' => sanitize_textarea_field($name),
				    'display_name' => sanitize_textarea_field($name),
				    'user_nicename' => sanitize_textarea_field($name),
				    'user_pass'   =>  sanitize_textarea_field($default_pass),  // When creating an user, `user_pass` is expected.
				);
				$user_id = wp_insert_user( $userdata );
				if(is_numeric($user_id)){
					if(function_exists('bp_update_user_last_activity')){
						bp_update_user_last_activity( $user_id, bp_core_current_time() );
					}
					return $user_id;
				}
			}
			return false;
		}

		function fetchcustomfields($request){
			$body = json_decode($request->get_body(),true);
			$group_id = 0;
			$return = [];
			if(!empty($body['group_id'])){
				$group_id = $body['group_id'];
				$fields = vibebp_get_groups_meta_fields_array();
				if(!empty($fields)){
					foreach($fields as $key=> $field){
						$val = vibebp_groups_get_groupmeta_value($field,$group_id);
						if(!empty($val) ){
							if($field['type'] == 'editor'){
								$fields[$key]['value'] = $val['value'];
								$fields[$key]['raw'] = $val['raw'];
							}else{
								$fields[$key]['value'] = $val;
							}
						}
						
						
					}
					$return['fields'] =  $fields;
				}
			}else{
				$return['fields'] =  vibebp_get_groups_meta_fields_array();
			}
			return new WP_REST_Response( $return, 200 );
		}

	}
}
Vibe_BP_API_Rest_Groups_Controller::init();



function vibe_get_group_meta_permissions($group_id,$user_id){
	$meta['is_admin'] = $meta['can_add_members'] = $meta['can_invite'] = false;
	$meta['member_actions']=[];
	$meta['can_view'] = groups_is_user_member( $user_id, $group_id );
	if(user_can($user_id,'manage_options')){
		$meta['is_admin']  = $meta['can_add_members'] =$meta['can_view']= true;
		$meta['member_actions']=array(
			'remove'=>__('Remove member','vibebp'),
			'ban'=>__('Ban member','vibebp'),
			'unban'=>__('Un Ban member','vibebp'),
			'change_role_member'=>__('Set Member','vibebp'),
			'change_role_moderator'=>__('Set Moderator','vibebp'),
			'change_role_admin'=>__('Set Adminitrator','vibebp'),
			'uninvite_member'=>__('Remove Invite','vibebp'),
		);
	}else{
		$admins = groups_get_group_admins($group_id);
		if(!empty($admins)){
			foreach ($admins as $key => $mod) {
				if($mod->user_id==$user_id){
					$meta['is_admin']  = $meta['can_add_members'] = true;
					$meta['member_actions']=array(
						'remove'=>__('Remove member','vibebp'),
						'ban'=>__('Ban member','vibebp'),
						'unban'=>__('Un Ban member','vibebp'),
						'change_role_member'=>__('Set Member','vibebp'),
						'change_role_moderator'=>__('Set Moderator','vibebp'),
						'change_role_admin'=>__('Set Adminitrator','vibebp'),
						'uninvite_member'=>__('Remove Invite','vibebp'),
					);

					if(!empty($args) && $args['role'] == 'requests'){
						$meta['member_actions']['accept_request']=__('Accept request','vibebp');
						$meta['member_actions']['reject_request']=__('Reject request','vibebp');
					}
					break;
				}
			}
		}

		if ( $group_mods = groups_get_group_mods( $group_id ) ) {
			foreach ( (array) $group_mods as $mod ){
				if($mod->user_id==$user_id){
					$meta['is_admin']  = false;
					$meta['is_mod']  = false;$meta['can_add_members'] = true;
					$meta['member_actions']=array(
						'remove'=>__('Remove member','vibebp'),
						'ban'=>__('Ban member','vibebp'),
						'unban'=>__('Un Ban member','vibebp'),
						'uninvite_member'=>__('Remove Invite','vibebp'),
					);
					break;
				}
			}
		}
	}
	$invite_status = groups_get_groupmeta($group_id,'invite_status',true);
	

	if(!empty($invite_status)){
		switch ($invite_status) {
			case 'admins':
				if($meta['is_admin']){
					$meta['can_invite'] = true;

				}
				break;
			case 'mods':
				$mods = groups_get_group_mods( $group_id );
				if(!empty($mods)){
					foreach ($mods as $key => $mod) {
						if($mod->user_id==$user_id){
							$meta['can_invite'] = true;
							break;
						}
					}
				}
				if($meta['is_admin']){
					$meta['can_invite'] = true;
				}
				break;
			case 'members':
				if(groups_is_user_member($user_id,$group_id)){
					$meta['can_invite'] = true;
				}
				

			break;
			default:
				
				break;
		}
	}

	$meta['is_admin'] = apply_filters('vibebp_groups_api_is_admin',$meta['is_admin'],$group_id,$user_id);
	$meta['can_add_members'] = apply_filters('vibebp_groups_api_can_add_members',$meta['can_add_members'],$group_id,$user_id);
	$meta['can_invite'] = apply_filters('vibebp_groups_api_can_invite',$meta['can_invite'],$group_id,$user_id);
	
	return $meta;
}



function vibebp_can_user_edit_group($group_id,$user_id){
	$meta = vibe_get_group_meta_permissions($group_id,$user_id);
	return !empty($meta['can_add_members']);
}

function vibebp_can_user_view_group($group_id,$user_id){
	$meta = vibe_get_group_meta_permissions($group_id,$user_id);
	return !empty($meta['can_view']);
}
