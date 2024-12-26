<?php

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'VIBE_BP_API_Rest_Members_Controller' ) ) {
	
	class Vibe_BP_API_Rest_Members_Controller extends WP_REST_Controller{
		
		public static $instance;
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new Vibe_BP_API_Rest_Members_Controller();
	        return self::$instance;
	    }
	    public function __construct( ) {
			$this->namespace = Vibe_BP_API_NAMESPACE;
			$this->register_routes();
		}

		public function register_routes() {
			register_rest_route( $this->namespace, '/members', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_public_members_permissions' ),
					'callback'            =>  array( $this, 'get_members' ),
				),
			));
			register_rest_route( $this->namespace, '/member/(?P<user_id>\d+)?', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_member' ),
					'permission_callback' => array( $this, 'get_public_members_permissions' ),
					'args'                     	=>  array(
						'user_id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/member_card/(?P<user_id>\d+)', array(
				array(
					'methods'             =>  'GET',
					'permission_callback' => array( $this, 'get_public_members_permissions' ),
					'callback'            =>  array( $this, 'get_member_card' ),
				),
			));
			register_rest_route( $this->namespace, '/member_card_post/(?P<user_id>\d+)/(?P<card_id>\d+)', array(
				array(
					'methods'             =>  'GET',
					'permission_callback' => array( $this, 'get_public_members_permissions' ),
					'callback'            =>  array( $this, 'get_member_card' ),
				),
			));
			register_rest_route( $this->namespace, '/member_values', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'callback'            =>  array( $this, 'get_member_card_values' ),
				),
			));
			
			register_rest_route( $this->namespace, '/member/avatars/', array(
				array(
					'methods'             => 'GET',
					'callback'            =>  array( $this, 'get_member_avatars' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
				),
			));
			register_rest_route( $this->namespace, '/friends/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_get_friends' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/friends/addfriendship/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_friends_add_friend' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/friends/removefriendship/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_friends_remove_friend' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/friends/action/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_friends_action_friendship' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));



			register_rest_route( $this->namespace, '/check/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'checkfuction' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			
			register_rest_route( $this->namespace, '/following_ids', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'callback'            =>  array( $this, 'get_following_ids' ),
				),
			));
			register_rest_route( $this->namespace, '/friend_ids', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'callback'            =>  array( $this, 'get_friend_ids' ),
				),
			));

			register_rest_route( $this->namespace, '/friends/requests/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'vibe_friends_get_friendId_request_ids_for_user' ),
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));


			register_rest_route( $this->namespace, '/followers', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'callback'            =>  array( $this, 'get_followers' ),
				),
			));
			register_rest_route( $this->namespace, '/follower_ids', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'callback'            =>  array( $this, 'get_follower_ids' ),
				),
			));
			register_rest_route( $this->namespace, '/following', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'callback'            =>  array( $this, 'get_following' ),
				),
			));
			register_rest_route( $this->namespace, '/followers/action', array(
				array(
					'methods'             =>  'POST',
					'permission_callback' => array( $this, 'get_members_permissions' ),
					'callback'            =>  array( $this, 'follower_action' ),
				),
			));


			register_rest_route( $this->namespace, '/members/taxonomies', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_member_taxonomies' ),
					'permission_callback' => array( $this, 'all_members_user_permissions_check' ),
				),
			));

			register_rest_route( $this->namespace, '/members/all', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_all_members' ),
					'permission_callback' => array( $this, 'all_members_user_permissions_check' ),
				),
			));

			register_rest_route( $this->namespace, '/members/assignTerms', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'assign_member_terms' ),
					'permission_callback' => array( $this, 'all_members_user_permissions_check' ),
				),
			));

			register_rest_route( $this->namespace, '/members/stats', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_member_stats' ),
					'permission_callback' => array( $this, 'all_members_user_permissions_check' ),
				),
			));

			register_rest_route( $this->namespace, '/members/create-tax', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'create_member_tax' ),
					'permission_callback' => array( $this, 'all_members_user_permissions_check' ),
				),
			));

			register_rest_route( $this->namespace, '/members/create-users', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'create_users' ),
					'permission_callback' => array( $this, 'create_members_user_permissions_check' ),
				),
			));

			register_rest_route( $this->namespace, '/members/getUserSlug', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_user_slug' ),
					'permission_callback' => array( $this, 'create_members_user_permissions_check' ),
				),
			));
			register_rest_route( $this->namespace, '/members/saveUserSlug', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'save_user_slug' ),
					'permission_callback' => array( $this, 'create_members_user_permissions_check' ),
				),
			));
			

		}


		/*
	    PERMISSIONS
	     */
	    function get_public_members_permissions($request){
	    	
	    	$body = json_decode($request->get_body(),true);
	       	
	        if (empty($body) || empty($body['token'])){
	           	$client_id = sanitize_text_field($request->get_param('client_id'));

	           	if($client_id == vibebp_get_setting('client_id')){
	           		return true;
	           	}
	        }

	        return $this->get_members_permissions($request);
	    }
	    function get_members_permissions($request){
	    	
	    	$body = json_decode($request->get_body(),true);
	       	
	       	if(empty($body['token']))
	       		return false;

        	$token = sanitize_text_field($body['token']);
	        
	        /** Get the Secret Key */
	        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
	        if (!$secret_key) {
	          	return false; 
	        }
	        /** Try to decode the token */ /** Else return exception*/
	        try {
	            $user_data = JWT::decode($token, $secret_key, array('HS256'));
	            /*
		        avatar: "//www.gravatar.com/avatar/73745bceffd75a7e5a1203d9f0e9fe44?s=150&#038;r=g&#038;d=mm"
				caps: ["subscriber"]
				displayname: "test"
				email: "q@q.com"
				id: "2"
				profile_link: "http://localhost/appointments/members/test"
				slug: "test"
				username: "test"*/
		        $this->user = $user_data->data->user;
		        /** Let the user modify the data before send it back */
	        	return true;

	        }catch (Exception $e) {
	            /** Something is wrong trying to decode the token, send back the error */
	            return false;
	        }
	    	

	    	return false;
	    }

    	function get_members($request){
    		$args = json_decode($request->get_body(),true);	
    		$args = vibebp_recursive_sanitize_text_field($args);
    		$args=apply_filters( 'vibe_bp_api_members_get_members_args', $args, $request);


    		/*'type'                => 'active',     // Active, newest, alphabetical, random or popular.
			'user_id'             => false,        // Pass a user_id to limit to only friend connections for this user.
			'exclude'             => false,        // Users to exclude from results.
			'search_terms'        => false,        // Limit to users that match these search terms.
			'meta_key'            => false,        // Limit to users who have this piece of usermeta.
			'meta_value'          => false,        // With meta_key, limit to users where usermeta matches this value.
			'member_type'         => '',
			'member_type__in'     => '',
			'member_type__not_in' => '',
			'include'             => false,        // Pass comma separated list of user_ids to limit to only these users.
			'per_page'            => 20,           // The number of results to return per page.
			'page'                => 1,            // The page to return if limiting per page.
			'populate_xtras'     => true,         // Fetch the last active, where the user is a friend, total friend count, latest update.
			'count_total'         => 'count_query' // What kind of total user count to do, if any. 'count_query', 'sql_calc_found_rows', or false.
			*/
			if(!empty($args['orderby'])){
				$args['type'] = $args['orderby'];
				unset($args['orderby']);
			}
			

			$stop_query = 0;
			if(!empty($args['filters'])){
				$xprofile_query = array(
					'relation'=>'AND'
				);
				foreach($args['filters'] as $filter){
					if(class_exists('BP_XProfile_Field')){
						if(empty($filter['type'])){
							$filter['type'] = BP_XProfile_Field::get_type($filter['field_id']);
						}
						
						switch($filter['type']){
							case 'checkbox':
								$args['include'] = array();
								global $wpdb,$bp;
								$results = $wpdb->get_results($wpdb->prepare("SELECT user_id,value from {$bp->profile->table_name_data} WHERE field_id=%d",$filter['field_id']),ARRAY_A);

								if(!empty($results)){
									foreach ($results as $k => $u) {
										if(is_serialized($u['value'])){
											$u['value'] = vibebp_recursive_sanitize_array_field(unserialize($u['value']));
										}
										
										foreach ($filter['values'] as $key => $v) {
											if(in_array($v, $u['value'])){
												$args['include'][] = $u['user_id'];

											}
										}
										
									}

									if(empty($args['include'])){
										$stop_query=1;
									}
								}
							break;
							default:
								$xprofile_query[] = array(
									'field'   => $filter['field_id'],
									'value'   => $filter['values'],
									'compare' => 'IN',
								);
							break;
						}
					}
				}
				
				$args['xprofile_query'] = $xprofile_query;
				$args = apply_filters('vibebp_members_directory_args',$args);
				unset($args['filters']);
			}
			
			$stop_query = apply_filters('vibebp_stop_members_query',$stop_query,$args);

			$args['populate_extras']=false;
			if(!$stop_query){
				$run =  bp_core_get_users($args);	
			}

			if(!empty($run) && count($run['users']) ){

				foreach($run['users'] as $key => $user){

					unset($run['users'][$key]->user_email);
					unset($run['users'][$key]->user_email);

					$run['users'][$key]->avatar = bp_core_fetch_avatar(array(
                            'item_id' 	=> $user->ID,
                            'object'  	=> 'user',
                            'type'		=>'thumb',
                            'html'    	=> false
                        ));
					$run['users'][$key]->url = bp_core_get_user_domain($run['users'][$key]->id);
					if(isset($user->last_update)){
						$run['users'][$key]->last_update = maybe_unserialize($user->last_update);	
					}
					
					if(!empty($args['show_map'])){
						$run['users'][$key]->location = array('lat'=>get_user_meta($user->ID,'lat',true),'lng'=>get_user_meta($user->ID,'lng',true));
					}

					if(!empty($args['firstLoad']) && $args['firstLoad'] == 'card'){

						$membercard = aply_filters('vibebp_generate_member_card','',$user);
						if(empty($membercard)){

							$layouts = new WP_Query(apply_filters('vibe_member_card',array(
								'post_type'=>'member-card',
								'posts_per_page'=>1,
								'meta_query'=>array(
									'relation'=>'AND',
									array(
										'key'=>'member_type',
										'compare'=>'=',
										'value'=> bp_get_member_type( $user->ID )
									)
								)
							),$user->ID));

							if(!$layouts->have_posts()){
								$layouts = new WP_Query(array(
									'post_type'=>'member-card',
									'posts_per_page'=>1,
									'meta_query'=>array(
										'relation'=>'AND',
										array(
											'key'=>'default_member-card',
											'compare'=>'=',
											'value'=>1
										)
									)
								));
								if(!$layouts->have_posts()){
									$layouts = new WP_Query(array(
											'post_type'=>'member-card',
											'posts_per_page'=>1,
											'meta_query'=>array(
												'relation'=>'AND',
												array(
													'key'=>'member_type',
													'compare'=>'NOT EXISTS'
												)
											)
										));
								}
							}

							$init = VibeBP_Init::init();
							$init->user_id = $user_id;
				    		ob_start();
							if($layouts->have_posts()){
								while($layouts->have_posts()){
									$layouts->the_post();
									echo '<div class="member_card_'.$post->post_slug.'">';
									the_content();
									echo '</div>';
								}
							}

							$membercard= ob_get_clean();
						}
						$run['users'][$key]->memberCard = $membercard;
					}
				}
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Members Exist','Members Exist','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 1,
	    			'data' => array('users'=>[],'total'=>'0'),
	    			'message' => _x('No members found !','Members Not Exist','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_members_get_members', $data, $request ,$args );
			return new WP_REST_Response( $data, 200 ); 
    	}


    	function get_member($request){
    		$user_id = (int)$request->get_param('user_id');	 // get param data 'id'
    		$run =  bp_core_get_core_userdata($user_id);	
			if( $run  ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Member Exist','Member Exist','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Member Not Exist','Member Not Exist','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_members_get_member', $data, $request ,$args );
			return new WP_REST_Response( $data, 200 ); 		
    	}

    	function vibe_bp_api_get_member_by_id($id){
    		$data = get_userdata($id);
    		$data=apply_filters( 'vibe_bp_api_get_member_by_id', $data);
    		return 	$data;
    	}

    	function get_friend_ids($request){
    		$args = json_decode($request->get_body(),true);
    		global $wpdb,$bp;
    		$fiendships = $wpdb->get_results("SELECT initiator_user_id, friend_user_id FROM {$bp->friends->table_name} WHERE (initiator_user_id = ".$this->user->id." OR friend_user_id = ".$this->user->id.") AND is_confirmed = 1",ARRAY_A);
    		$friend_ids = [];
    		if(!empty($fiendships)){

    			foreach($fiendships as $friendship){
    				if($friendship['initiator_user_id'] == $this->user->id){
    					$friend_ids[]=intval($friendship['friend_user_id']);
    				}else{
    					$friend_ids[]=intval($friendship['initiator_user_id']);
    				}
    			}
    		}

    		return new WP_REST_Response( array('status'=>1,'friends'=>$friend_ids), 200 ); 
    	}

    	function vibe_bp_api_get_friends($request){

    		
    		$args = json_decode($request->get_body(),true);
    		$args = vibebp_recursive_sanitize_text_field($args);
    		$run = bp_core_get_users( array(
				'type'         => $args['sort'],
				'per_page'     => 15,
				'page'         => $args['page'],
				'user_id'      => $this->user->id,
				'search_terms' => $args['search'],
			) );

    		if( $run['total'] ){

    			foreach($run['users'] as $key=>$user){
    				$run['users'][$key]=(Array)$user;
    				if(!empty($user->latest_update)){
    					$run['users'][$key]['latest_update'] = maybe_unserialize($user['latest_update']);	
    				}
    				
    				$run['users'][$key]['avatar'] = bp_core_fetch_avatar(array(
                        'item_id' 	=> $user->ID,
                        'object'  	=> 'user',
                        'type'		=>'thumb',
                        'html'    	=> false
                    ));
    			}

    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('User has Friends','User has Friends','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('User has no Friends','User has no Friends','vibebp')
	    		);
    	    }

    		$data=apply_filters( 'vibe_bp_api_get_friends', $data ,$request);

			return new WP_REST_Response( $data, 200 );  
    	}

    	// for sending frienship request get true if send else false
    	function vibe_bp_api_friends_add_friend($request){
 
    		$args = json_decode($request->get_body(),true);
    		$args = vibebp_recursive_sanitize_text_field($args);
    		$friends= $args['friends'];	 /* get param data 'friend_userid' */
    		$run = true;
    		if(!empty($friends)){

    			foreach($friends as $friend){
    				if($run){
    					$run=friends_add_friend($this->user->id,$friend,false);    /* return bool true|false */
    					vibebp_fireabase_update_stale_requests($friend,'friends/requests?args=%7B%22requester%22%3A0');
    				}
    				
    			}
    			vibebp_fireabase_update_stale_requests($this->user->id,'friends/requests?args=%7B%22requester%22%3A1');
    		}
    		
    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Request Send','Request Send','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Request Not Send','Request Not Send','vibebp')
	    		);
    	    }

    		$data=apply_filters( 'vibe_bp_api_friends_add_friend', $data ,$request);
			return new WP_REST_Response( $data, 200 );  	

    	}

    	function vibe_bp_api_friends_remove_friend($request){

    		$args = json_decode($request->get_body(),true);
    		$first_user_id = $this->user->id;	 /* get param data 'initiator_userid' */
    		$second_userid= (int)$args['friend_userid'];	 /* get param data 'friend_userid' */
    		$args = vibebp_recursive_sanitize_text_field($args);
    		$run=friends_remove_friend($first_user_id,$second_userid);  /* return bool
    		true|false */
    		if(empty($run)){
    			$run=friends_remove_friend($second_userid,$first_user_id);
    		}
    		
    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Friend  removed','Friend  removed','vibebp')
	    		);
	    		vibebp_fireabase_update_stale_requests($first_user_id,'friends?args=');
    			vibebp_fireabase_update_stale_requests($second_userid,'friends?args=');
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Friend not removed','Friend not removed','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_friends_add_friend', $data ,$request);
			return new WP_REST_Response( $data, 200 );  	
    	}

    	function vibe_get_friendship_ids_for_user($id){   		
    		return BP_Friends_Friendship::get_friendship_ids_for_user($id);
    	}



/*
*	This is used to Accept a Friendship ID
*/
    	function vibe_bp_api_friends_action_friendship($request){

    		$args = json_decode($request->get_body(),true);
    		$args = vibebp_recursive_sanitize_text_field($args);
    		$bp = buddypress();
    		global $wpdb;

    		$friendship_id = (int)$args['friendship_id'];
    		$action = $args['action'];
    		if($action == 'accept'){

    			
				$run = $wpdb->query( $wpdb->prepare( "UPDATE {$bp->friends->table_name} SET is_confirmed = 1, date_created = %s WHERE id = %d AND friend_user_id = %d", bp_core_current_time(), $friendship_id, $this->user->id ) );
				if($run){
					$friendship = new BP_Friends_Friendship( $friendship_id, true, false );
					friends_update_friend_totals( $friendship->initiator_user_id, $friendship->friend_user_id );
					do_action( 'friends_friendship_accepted', $friendship->id, $friendship->initiator_user_id, $friendship->friend_user_id, $friendship );

					vibebp_fireabase_update_stale_requests($friendship->initiator_user_id,'friends/requests?args=%7B%22requester%22%3A1');
					vibebp_fireabase_update_stale_requests($friendship->friend_user_id,'friends?args=');
					vibebp_fireabase_update_stale_requests($friendship->initiator_user_id,'friends?args=');
					vibebp_fireabase_update_stale_requests($friendship->friend_user_id,'friends/requests?args=%7B%22requester%22%3A0');
				}
    			
    		}else if($action == 'reject'){
    			$friendship = new BP_Friends_Friendship( $friendship_id, true, false );
				

				if($friendship){
					$run =  $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->friends->table_name} WHERE id = %d AND friend_user_id = %d", $friendship_id, $this->user->id ) );
					if($run){
						do_action_ref_array( 'friends_friendship_rejected', array( $friendship_id, &$friendship ) );
						vibebp_fireabase_update_stale_requests($friendship->initiator_user_id,'friends/requests?args=%7B%22requester%22%3A1');
						vibebp_fireabase_update_stale_requests($friendship->friend_user_id,'friends/requests?args=%7B%22requester%22%3A0');
					}
					
				}

    		}else if($action == 'cancel'){
    			$friendship = new BP_Friends_Friendship( $friendship_id, true, false );
    			
    			if( $friendship){
    				
					$run = $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->friends->table_name} WHERE id = %d AND initiator_user_id = %d", $friendship_id, $this->user->id ) );
					if($run){
	    				do_action_ref_array( 'friends_friendship_whithdrawn', array( $friendship_id, &$friendship ) );
						do_action_ref_array( 'friends_friendship_withdrawn',  array( $friendship_id, &$friendship ) );
						vibebp_fireabase_update_stale_requests($this->user->id,'friends/requests?args=%7B%22requester%22%3A1');
					}
    				
    			}
    		}
			
    		if( $run ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Friend Request action complete','Friend Request action','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Friend Request action can not be completed','Friend Request Not Accepted','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_friends_accept_friendship', $data ,$request);
			return new WP_REST_Response( $data, 200 );  
    	}


    	// fetch friendship id for user
    	function vibe_friends_get_friendship_ids_for_user($user_id,$page=1,$requested=1,$sort=DESC,$is_confirmed=0){
	    	global $wpdb;
			$bp = buddypress();
			if($requested){
				$friendship_ids = $wpdb->get_results( $wpdb->prepare( "SELECT id, initiator_user_id,friend_user_id  FROM {$bp->friends->table_name} WHERE initiator_user_id = %d AND (is_confirmed=%d)  ORDER BY date_created $sort LIMIT %d,20",  $user_id ,$is_confirmed,($page-1)*20 ) );
			}else{
				$friendship_ids = $wpdb->get_results( $wpdb->prepare( "SELECT id, initiator_user_id,friend_user_id  FROM {$bp->friends->table_name} WHERE friend_user_id = %d AND (is_confirmed=%d)  ORDER BY date_created $sort LIMIT %d,20",  $user_id ,$is_confirmed,($page-1)*20 ) );
			}
			
			return $friendship_ids;
    	
    	}


		// friend id and friendship id who  is request to this user;
    	function vibe_friends_get_friendId_request_ids_for_user($request){

    		$args = json_decode($request->get_body(),true);
    		$args = vibebp_recursive_sanitize_text_field($args);

    		if(esc_attr($args['sort']) == "ASC"){$sort = "ASC";}else{$sort = "DESC";}

    	    $initiator_friendship_ids=$this->vibe_friends_get_friendship_ids_for_user($this->user->id,intval($args['page']),intval($args['requester']),$sort);
    	    $user_details = array();
    	    if(!empty($initiator_friendship_ids)){
	    		foreach ($initiator_friendship_ids as $initiator_friendship_id) {

	    			$uid = (int)$initiator_friendship_id->initiator_user_id;
	    			if(!empty($args['requester'])){
	    				$uid = (int)$initiator_friendship_id->friend_user_id;
	    			}
	    			$user = bp_core_get_core_userdata($uid);
	    			$user->avatar = bp_core_fetch_avatar(array(
	                            'item_id' 	=> $user->ID,
	                            'object'  	=> 'user',
	                            'type'		=>'thumb',
	                            'html'    	=> false
	                        ));
	    			 $user_details[]=array(
	    			 	'friendship_id'=>(int)$initiator_friendship_id->id,
	    			 	'user'=>$user
	    			 );
	    		}
	    	}
    		$data=apply_filters( 'vibe_friends_get_friendId_request_ids_for_user', array('status'=>1,'data'=>$user_details),$request );
			return new WP_REST_Response( $data, 200 );   

    	}
    			

    	function get_followers($request){

    		$body = json_decode($request->get_body(),true);
    		$body = vibebp_recursive_sanitize_text_field($body);
    		$data = array(
    			'status'=>1,
    			'followers'=>array()
    		);

    		global $wpdb;
    		$results = $wpdb->get_results($wpdb->prepare("
    			SELECT DISTINCT user_id 
    			FROM {$wpdb->usermeta}
    			WHERE meta_key ='vibebp_follow' 
    			AND meta_value = %d",
    			$this->user->id));

    		if(!empty($results)){
    			foreach($results as $result){
    				$user=bp_core_get_core_userdata($result->user_id);
    				$user->avatar = bp_core_fetch_avatar(array(
                        'item_id' 	=> (int)$result->user_id,
                        'object'  	=> 'user',
                        'type'		=>'thumb',
                        'html'    	=> false
                    ));
                    
                    $followers = get_user_meta($this->user->id,'vibebp_follow',false);
                    if(in_array($result->user_id,$followers)){
                    	$user->is_following = true;
                    }

                    $data['followers'][]=$user;
    			}
    		}
    		return new WP_REST_Response( $data, 200 );   
    	}

    	function get_following_ids($request){
    		global $wpdb;
    		$users=get_user_meta($this->user->id,'vibebp_follow' ,false);
    		return new WP_REST_Response( array('status'=>1,'following'=>$users), 200 ); 
    	}
    	function get_follower_ids($request){
    		global $wpdb;
    		$results = $wpdb->get_results($wpdb->prepare("
    			SELECT user_id 
    			FROM {$wpdb->usermeta}
    			WHERE meta_key ='vibebp_follow' 
    			AND meta_value = %d",
    			$this->user->id),ARRAY_A);
    		$users=array();
    		if(!empty($results)){
    			foreach($results as $result){
    				$users[]=(int)$result['user_id'];
    			}
    		}
    		return new WP_REST_Response( array('status'=>1,'followers'=>$users), 200 ); 
    	}

    	function get_following($request){

    		$body = json_decode($request->get_body(),true);
    		$body = vibebp_recursive_sanitize_text_field($body);
    		$data = array(
    			'status'=>1,
    			'following'=>array()
    		);

    		$results = get_user_meta($this->user->id,'vibebp_follow',false);
    		$results= array_unique($results);
    		if(!empty($results)){
    			foreach($results as $result){
    				$user=bp_core_get_core_userdata($result);
    				$user->avatar = bp_core_fetch_avatar(array(
                        'item_id' 	=> $result,
                        'object'  	=> 'user',
                        'type'		=>'thumb',
                        'html'    	=> false
                    ));
                    $user->is_following = true;
                    $data['following'][]=$user;
    			}
    		}
    		return new WP_REST_Response( $data, 200 );   
    	}


    	function follower_action($request){
    		$body = json_decode($request->get_body());
    		
    		if($body->action == 'follow'){
    			
    			$followers = get_user_meta($this->user->id,'vibebp_follow',false);
    			
    			if(!empty($body->followers)){
    				
    				$messages = $rtm = array();
    				if(empty($body->followers) && !empty($body->user)){
    					$user_id = $body->user;
						$name = bp_core_get_user_displayname($body->user);
						if(empty($followers) || !in_array($user_id,$followers)){
							add_user_meta($this->user->id,'vibebp_follow',$user_id);	
							do_action('vibebp_member_follow',$this->user->id,$user_id);
							$messages[] = sprintf(__('Now following %s','vibebp'),$name);
							$rtm[]=array('user_id'=>$user_id,'message'=>sprintf(__('%s is now following you','vibebp'),$this->user->name));   
		    			}
    				}else{
	    				foreach($body->followers as $user){
	    					if(is_numeric($user)){
	    						$user_id = $user;
	    						$name = bp_core_get_user_displayname($user);
	    					}else{
	    						$user_id = $user->id;
	    						$name = $user->name;
	    					}
	    					if(empty($followers) || !in_array($user_id,$followers)){
								add_user_meta($this->user->id,'vibebp_follow',$user_id);	
								do_action('vibebp_member_follow',$this->user->id,$user_id);
								$messages[] = sprintf(__('Now following %s','vibebp'),$name);
								$rtm[]=array('user_id'=>$user_id,'message'=>sprintf(__('%s is now following you','vibebp'),$this->user->displayname));   
			    			}
	    				}
    				}
    				
    				return new WP_REST_Response(array('status'=>1,'message'=>$messages,'rtm'=>$rtm),200);

    			}else{
    				if(!empty($body->user->ID)){
    					add_user_meta($this->user->id,'vibebp_follow',$body->user->ID);	
    					do_action('vibebp_member_follow',$this->user->id,$body->user->ID);
						return new WP_REST_Response( array('status'=>1,'message'=>sprintf(__('Now following %s','vibebp'),$body->user->display_name),'rtm'=>array('user_id'=>$body->user->ID,'message'=>sprintf(__('%s is now following you','vibebp'),$this->user->displayname))), 200 );   
    				}else{
    					if(!empty($body->user)){
    						add_user_meta($this->user->id,'vibebp_follow',$body->user);
    						do_action('vibebp_member_follow',$this->user->id,$body->user);	
							return new WP_REST_Response( array('status'=>1,'message'=>sprintf(__('Now following %s','vibebp'),$body->user->displayname),'rtm'=>array('user_id'=>$body->user,'message'=>sprintf(__('%s is now following you','vibebp'),$this->user->displayname))), 200 );  
    					}
    					 
    				}
					
    			}
    		}
    		if($body->action == 'unfollow'){
    			if(is_numeric($body->user)){
    				$body->user = get_user_by('id',$body->user);
    			}
    			$followers = get_user_meta($this->user->id,'vibebp_follow',false);
    			if(!empty($followers) && in_array($body->user->ID,$followers)){

    				if(is_numeric($body->user)){
						$user_id = $body->user;
						$name = bp_core_get_user_displayname($body->user);
					}else{
						$user_id = $body->user->ID;
						$name = $body->user->user_nicename;
					}

    				delete_user_meta($this->user->id,'vibebp_follow',$user_id );
    				do_action('vibebp_member_unfollow',$this->user->id,$user_id);

    				return new WP_REST_Response( array('status'=>1,'message'=>sprintf(__('Unfollowed %s','vibebp'),$name),'rtm'=>array('user_id'=>$user_id,'message'=>sprintf(__('%s is unfollowed you','vibebp'),$this->user->displayname))), 200 );   	
    			}
    		}
    		return new WP_REST_Response( array('status'=>0,'message'=>__('Unable to perform task','vibebp')),200);
    	}

    	function get_member_card($request){
    		$user_id = (int)$request->get_param('user_id');
    		$card_id = (int)$request->get_param('card_id');
    		$layouts = '';
    		if(!empty($card_id)){
    			$layouts = new WP_Query(apply_filters('vibebp_member_card_given',array(
					'post_type'=>'member-card',
					'posts_per_page'=>1,
					'post__in'=>[$card_id]
				),$user_id));
    		}else{
    			$type = bp_get_member_type($user_id,false);
	    		if(!empty($type)){
	    			$layouts = new WP_Query(apply_filters('vibebp_member_card',array(
						'post_type'=>'member-card',
						'posts_per_page'=>1,
						'meta_query'=>array(
							'relation'=>'AND',
							array(
								'key'=>'member_type',
								'compare'=>'=',
								'value'=>$type
							)
						)
					),$user_id));
	    		}

	    		if(empty($layouts) || (!empty($layouts) && !$layouts->have_posts())){
	    			$layouts = new WP_Query(array(
						'post_type'=>'member-card',
						'posts_per_page'=>1,
						'meta_query'=>array(
							'relation'=>'AND',
							array(
								'key'=>'default_member-card',
								'compare'=>'EXISTS'
							)
						)
					));
	    		}

	    		if(empty($layouts) || (!empty($layouts) && !$layouts->have_posts())){
	    			$layouts = new WP_Query(array(
						'post_type'=>'member-card',
						'posts_per_page'=>1,
						'meta_query'=>array(
							'relation'=>'AND',
							array(
								'key'=>'member_type',
								'compare'=>'NOT EXISTS'
							)
						)
					));
	    		}
    		}

    		$init = VibeBP_Init::init();
			$init->user_id = $user_id;
			
    		ob_start();
    		if($layouts){
				if($layouts->have_posts()){
					while($layouts->have_posts()){
						$layouts->the_post();
						the_content();
					}
					
				}
			}
			return ob_get_clean();
    	}

    	function get_member_card_values($request){
    		$body = json_decode($request->get_body(),true);
    		$body = vibebp_recursive_sanitize_array_field($body);
    		$data=[];
    		if(is_array($body['fields'])){
    			foreach($body['fields'] as $field){
    				if(is_numeric($field['id'])){
    					$d = xprofile_get_field_data( (int)$field['id'], (int)$body['user_id']);
    					if(is_array($d)){
    						$data[$field['id']] = $d;	
    					}else{
    						$json = json_decode($d);
	    					if(json_last_error() === 0){
	    						$data[$field['id']] = $json;	
	    					}else{
	    						$data[$field['id']] = $d;	
	    					}	
    					}
    					
    					
    				}else{
    					if($field['id'] === 'profile_pic'){
    						$data[$field['id']] = bp_core_fetch_avatar(array(
			                        'item_id' 	=> (int)$body['user_id'],
			                        'object'  	=> 'user',
			                        'type'		=>'full',
			                        'html'    	=> false
			                    ));
    					}
    					if($field['id'] === 'friend_count'){
    						$data[$field['id']] = (int)friends_get_total_friend_count((int)$body['user_id']);
    					}
    					if($field['id'] === 'group_count'){
    						$data[$field['id']] = (int)bp_get_total_group_count_for_user((int)$body['user_id']);
    					}
    					if($field['id'] === 'follower_count'){
    						global $wpdb;
    						$data[$field['id']] = $wpdb->get_var("SELECT count(user_id) FROM {$wpdb->usermeta} WHERE meta_key = 'vibebp_follow' AND meta_value = ".intval($body['user_id']));
    					}
    					if($field['id'] === 'following_count'){
    						$count = get_user_meta((int)$body['user_id'],'vibebp_follow',false);
    						$data[$field['id']] = count($count);
    					}
    					

    				}
    			}
    		}
    		if(empty($data)){
    			return new WP_REST_Response( array('status'=>0,'message'=>__('No card data','vibebp')), 200 );   
    		}else{
    			return new WP_REST_Response( array('status'=>1,'data'=>$data), 200 );   
    		}
    	}
		


		function all_members_user_permissions_check($request){
			$body = json_decode($request->get_body(),true);
			if(!empty($body['token'])){
				$token = sanitize_text_field($body['token']);
				global $wpdb;
				$this->user = apply_filters('vibebp_api_get_user_from_token','',$token);
				if( (!empty($this->user) && vibebp_can_access_member_details($this->user)) ) {
					return true;
				}
				
			}
			
			return false;
		}

		function create_members_user_permissions_check($request){
			$body = json_decode($request->get_body(),true);
			if(!empty($body['token'])){
				$token = sanitize_text_field($body['token']);
				global $wpdb;
				$this->user = apply_filters('vibebp_api_get_user_from_token','',$token);
				if( (!empty($this->user) && (vibebp_can_access_member_details($this->user) || apply_filters('vibe_user_can_create_members',false,$this->user->id))) ) {
					return true;
				}
			}
			return false;
		}

		
		function get_member_taxonomies($request){
			$body = json_decode($request->get_body(),true);
			$data = array( 'status' => 0);

	
			$taxonomies = [];

			$mtypes=  bp_get_member_types(array(),'objects');
			if(!empty($mtypes)){
				foreach($mtypes as $i=>$mtype){
					$mtypes[$i]->term_id=$mtype->db_id;
				}
			}

			$member_tax = vibebp_get_member_tax();

			$taxonomies[]=['type'=>'bp_member_type','label'=>__('Member Types','vibebp'),'value'=>$mtypes];

			$tags = get_terms(  array('taxonomy'=>'member_tag','hide_empty' => false,'orderby'=>'name','order'=>'ASC') );
			$taxonomies[]=['type'=>'member_tag','label'=>__('Member Tags','vibebp'),'value'=>array_map(function ($term){
						
						$c= get_term_meta ( $term->term_id, 'member_tag-color', true ); 
						if(!empty($c)){
							$term->color = $c;
						}else{
							$term->color = get_term_meta ( $term->term_id, 'member-tag-color', true ); 
						}
						return $term;
					},$tags)];

			$data = array(
				'status' => 1,
				'data' => apply_filters('vibebp_get_member_taxonomies',$taxonomies)
			);
			return new WP_REST_Response($data, 200);
		}
        
		function get_member_avatars($request){
	    	$body = json_decode($request->get_body(),true);
	    	$values = [];
			foreach($body['ids'] as $id){
				$key = apply_filters('vibebp_get_avatar_key','user_'.$id['key'],$body['type'],$body['ids']);
				$avatar = apply_filters('vibebp_get_avatar',bp_core_fetch_avatar(array(
                    'item_id' => (int)$id['key'],
                    'object'  => 'user',
                    'type'=>'thumb',
                    'html'    => false
                )),$body['type'],$body['ids']);
                 $name = bp_core_get_user_displayname($id['key']);
                  $values[] = ['type'=>'user','key'=>$id['key'],'value'=>['avatar'=>$avatar,'name'=>$name]];
             }
    			
		    return array('status'=>1,'values'=>$values);	
	    }
		function get_all_members($request){
			$args = json_decode($request->get_body(),true);
			$args = vibebp_recursive_sanitize_text_field($args );
			$data = array( 'status' => 0);

			global $wpdb;
			$new_args = array(
				'paged' => isset($args['paged']) ? (int)$args['paged'] : 1,
				'number' => 20,
				'search'         => '*'.esc_attr( $args['s'] ).'*',
				'search_columns' => array( 'user_login', 'user_email','user_nicename','display_name' ),
				'fields' => array('ID','display_name','user_email'),
				'orderby' => isset($args['orderby']) ? $args['orderby'] : '',
				'order' => isset($args['order']) ? $args['order'] : '',
			);
			if(!empty($args['taxonomies'])){
				$values = [];
				$is_value = false;
				foreach ($args['taxonomies'] as $value) {
					if($value && !empty($value['term_ids'])){
						$is_value = true;
						$values = array_merge($values,$value['term_ids']);	
					}
				}
				$values = implode(",",$values);

				if($is_value){
					$user_ids_arr = $wpdb->get_results("SELECT DISTINCT object_id FROM {$wpdb->term_relationships} WHERE `term_taxonomy_id` IN ($values)");
					$new_args['include'] = array_map(function($user){
						return (int)$user->object_id;
					},$user_ids_arr);

					if(!$user_ids_arr){
						$new_args['include'] = array(99999999);
					}
				}
			}
			$user_query = new WP_User_Query( $new_args );

			$nusers = array();
			if ( ! empty( $user_query->get_results() ) ) {
				foreach ( $user_query->get_results() as $user ) {
					$user->image = get_avatar_url($user->ID);
					
					$user->tax=[];
					$member_tax = vibebp_get_member_tax();
					foreach($member_tax as $tax){
						$user->tax[$tax]=wp_get_object_terms($user->ID, $tax);
					}
					$user->ID = (int)$user->ID;
					$nusers[] = apply_filters('vibebp_all_members_user',$user);
				}
				$data = array( 
					'status' => 1,
					'members' => $nusers,
					'total' => $user_query->get_total()
				);
			}

			return new WP_REST_Response($data, 200);
		}

		//tags && types
		function assign_member_terms($request){
			$body = json_decode($request->get_body(),true);
			$body = vibebp_recursive_sanitize_text_field($body );
			
			$data = array( 'status' => 0);
			if($body['action'] === 'add_tax' || $body['action'] === 'remove_tax'){
				if(!empty($body['member_ids']) && !empty($body['term_ids']) && !empty($body['action'])){
					
					$terms = array_map(function ($a) { return (int)$a; }, $body['term_ids']);

					$type = $body['tax'];

					foreach ($body['member_ids'] as $key => $member_id) {
						if($body['action'] == 'add_tax'){
							wp_add_object_terms((int)$member_id,$terms, $type );
							$data = array( 'status' => 1 , 'message' => __('Added!'));
						}else{
							wp_remove_object_terms((int)$member_id,$terms, $type );
							$data = array( 'status' => 1 , 'message' => __('Removed!'));
						}
					}
				}
			}
			return new WP_REST_Response(apply_filters('vibebp_bulk_member_action',$data,$body), 200);
		}

		function get_member_stats($request){
			$body = json_decode($request->get_body(),true);
			$body = vibebp_recursive_sanitize_text_field($body );
			$data = array( 'status' => 0);
			if(!empty((int)$body['user_id'])){
				$user_id = (int)$body['user_id'];
				$udata = get_userdata( $user_id );
				$registered = $udata->user_registered;
				$user_id = (int)$user_id;
				$stats_array = array(
					array(
						'key' => 'user_registered',
						'type' => 'timestamp',
						'label' => _x('User Registered','members user stats','vibebp'),
						'value' => strtotime( $registered )
					),
				);

				if(bp_is_active('groups')){
					$stats_array[]=array(
						'key' => 'user_group_count',
						'type' => 'group_count',
						'label' => _x('Group count','members user stats','vibebp'),
						'value' => bp_get_total_group_count_for_user($body['user_id'])
					);
				}

				if(bp_is_active('friends')){
					$stats_array[]=array(
						'key' => 'user_friends_count',
						'type' => 'friends_count',
						'label' => __('Friends Count','members user stats','vibebp'),
						'value' => friends_get_total_friend_count( $body['user_id'] )
					);
				}
				$arr = apply_filters('members_detail_get_member_stats',$stats_array,$body,$this->user->id);
				
				if(function_exists('bp_get_user_last_activity')){
					array_push($arr,array(
							'key' => 'last_update',
							'type' => 'timestamp',
							'label' => __('Last Active','vibebp'),
							'value' => strtotime(bp_get_user_last_activity($user_id))
						)
					);
				}
				
				$data = array( 'status' => 1 , 'data' => $arr);
			}
			return new WP_REST_Response($data, 200);
		}

		function get_user_last_update($user_id){
			global $wpdb;
			$query = "SELECT um.meta_value FROM {$wpdb->usermeta} as um WHERE um.user_id = %d AND um.meta_key = 'last_update'"; 
			$total_query = $wpdb->prepare ($query,$user_id);
			$total = $wpdb->get_var($total_query);
			return $total;
		}


		function create_member_tax($request){
			$body = json_decode($request->get_body(),true);
			$body = vibebp_recursive_sanitize_text_field($body );			
			$data = array( 'status' => 0,'message'=>__('Error occurred','vibebp'));

			$member_tax = vibebp_get_member_tax();

			if(!empty($body['tax']['tax']) && in_array(esc_attr($body['tax']['tax']),$member_tax)){
				$term_taxonomy = esc_attr($body['tax']['tax']);
				require_once(ABSPATH . 'wp-admin/includes/taxonomy.php'); 
				$tax = $body['tax'];
				if(!term_exists($tax['name'] , $term_taxonomy)){
					$newtax = wp_create_term( $tax['name'], $term_taxonomy );
					if(!empty($newtax['term_id'])){
						$term = get_term($newtax['term_id']);
						if($tax['color']){
							add_term_meta( $newtax['term_id'], esc_attr($body['tax']['tax']).'-color', $tax['color'], true );
							$term->color= $tax['color'];
							add_term_meta( $newtax['term_id'], 'bp_type_name', $tax['name'], true );
							
						}
						$data = array( 'status' => 1 , 'data' => $term , 'message' =>__('Term added!','vibebp'));
					}
				}else{
					$data['message'] = __('Term already exists!','vibebp');
				}
				
			}
			return new WP_REST_Response($data, 200);
		}

		function create_users($request){
			$body = json_decode($request->get_body(),true);
			$body = vibebp_recursive_sanitize_text_field($body );
			
			$data = array( 'status' => 0);
			$errors = [];
			$single_user = !empty($body['is_single']); //from temp option
			$created_user_ids = [];



			$can_not_create = apply_filters('vibebp_can_create_member',false,$body);
			if($can_not_create){
				return new WP_REST_Response( array('status' => 0,'message' => $can_not_create), 200);
			}

			if(!empty($body['users']) && is_array($body['users']) && !$can_not_create){
				$users = $body['users'];
				$user_count = count($users);
				$registered_count = 0;
				foreach ($users as $key => $newuser) {
					
					$user_id  = wp_create_user(sanitize_text_field($newuser['email']),sanitize_text_field($newuser['password']),sanitize_text_field($newuser['email']));
					
					if (is_wp_error($user_id)) {
						$errors[] =  $user_id->get_error_messages();
					}else{
						
						do_action('vibebp_add_user_as_child',$this->user->id,$user_id,$body);

						$created_user_ids[] = $user_id;
						$registered_count++;
						if(!empty($newuser['tags'])){
							wp_add_object_terms($user_id,explode('|',$newuser['tags'][0]), 'member_tag' );
						}
						if(!empty($newuser['member_types'])){
							wp_add_object_terms($user_id,$newuser['member_types'], 'bp_member_type' );
						}
						if($single_user){
							$options = get_option("temp_user_details_{$this->user->id}");
							if(!empty($options) && is_array($options)){
								foreach ($options as $key1 => $option) {
									$saved = xprofile_set_field_data( $key1, $user_id, $option['value'] );
								}
							}
							delete_option("temp_user_details_{$this->user->id}");
						}else if(!empty($newuser['fields'])){
							foreach ($newuser['fields'] as $field) {
								if(!empty($field['id'])){
									$saved = xprofile_set_field_data( $field['id'], $user_id, $field['value']);	
								}
							}	
						}
					}
				}
				$data =  array(
					'status' => 1,
					'message' => sprintf(__("%s user registered!",'vibebp'),$registered_count),
					'errors' => $errors,
					'created_user_ids' => $created_user_ids
				);
			}
			return new WP_REST_Response($data, 200);
		}

		function get_users_from_csv($csv){
			$data = [];
			$f = fopen($csv['tmp_name'], 'r');
			if ($f === false) {
				return $data;
			}
			$rowc = 1;
			while (($row = fgetcsv($f,1000,";")) !== false) {
				if($rowc >= 3){
					if(!empty( $row[0]) && !empty($row[1])){
						$data[] = array(
							'email' => $row[0],
							'password' => $row[1],
							'tags' => !empty($row[2])?explode(",",$row[2]):[],
							'member_types' => !empty($row[3])?explode(",",$row[3]):[],
						);
					}
				}
				$rowc++;
			}
			return $data;
		}

		function get_user_slug($request){
			$args = json_decode($request->get_body(),true);
			
			$data = get_userdata($args['user_id']);
			
			return new WP_REST_Response(['status'=>1,'slug'=>$data->user_nicename,'link'=>bp_core_get_user_domain($args['user_id'])], 200);
		}

		function save_user_slug($request){
			$args = json_decode($request->get_body(),true);

			$r = wp_update_user(['ID'=> $args['user_id'],'user_nicename' => esc_attr($args['userSlug'])]);
			
			if(is_wp_error($r)){
				$data = ['status'=>1,'message'=>$r->get_error_message()];
			}else{
				$data = ['status'=>1,'message'=>esc_html__('User slug updated','vibebp'),'slug'=>esc_attr($args['userSlug']),'link'=>bp_core_get_user_domain($args['user_id'])];
			}

			return new WP_REST_Response($data, 200);
		}

	}
}


Vibe_BP_API_Rest_Members_Controller::init();
