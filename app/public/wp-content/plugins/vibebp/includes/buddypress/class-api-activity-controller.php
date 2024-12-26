<?php

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'VIBE_BP_API_Rest_Activity_Controller' ) ) {
	
	class VIBE_BP_API_Rest_Activity_Controller extends WP_REST_Controller{
		
		public static $instance;
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new VIBE_BP_API_Rest_Activity_Controller();
	        return self::$instance;
	    }
	    public function __construct( ) {
			$this->namespace = Vibe_BP_API_NAMESPACE;
			$this->type= Vibe_BP_API_ACTIVITY_TYPE;
			$this->register_routes();
		}

		public function register_routes() {
			register_rest_route( $this->namespace, '/' .$this->type, array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_activity' ),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
				),
			));
			register_rest_route( $this->namespace, '/' .$this->type.'/public', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_public_activity' ),
					'permission_callback' => array( $this, 'get_public_activity_permissions' ),
				),
			));


			register_rest_route( $this->namespace, '/' .$this->type.'/add', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'post_activity' ),
					'permission_callback' => array( $this, 'get_activity_post_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/remove', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'remove_activity' ),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/report', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'report_activity' ),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
				),
			));

			

			register_rest_route( $this->namespace, '/'.$this->type .'/add-favorite', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_add_favorite'),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/remove-favorite', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_remove_favorite'),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/add-like', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_add_like'),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/reaction', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_udpate_reaction'),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/remove-like', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_remove_like'),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/get-favorite', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_get_favorite'),
					'permission_callback' => array( $this, 'get_activity_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			

		}


		/*
	    PERMISSIONS
	     */
	    function get_public_activity_permissions($request){
	    	$body = json_decode($request->get_body(),true);

           	if(!empty($body['token'])){
           		$token = sanitize_text_field($body['token']);
		        /** Get the Secret Key */
		        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
		        if (!$secret_key) {
		          	return false;
		        }
		        /** Try to decode the token */ /** Else return exception*/
		        try {
		            $user_data = JWT::decode($token, $secret_key, array('HS256'));
		            
			        $this->user = $user_data->data->user;
			        /** Let the user modify the data before send it back */
		        	return true;

		        }catch (Exception $e) {
		            /** Something is wrong trying to decode the token, send back the error */
		            return false;
		        }
           	}else{
           		$client_id = $request->get_param('client_id');
           		if($client_id == vibebp_get_setting('client_id')){
           			$this->user = (object)['id'=>0];
	           		return true;
	           	}
           	}
	        
	        return false;
	    }

	    function get_activity_permissions($request){
	    	$body = json_decode($request->get_body(),true);
	       	
	        if (empty($body['token'])){
	        	return false;
	        }

	        if(!empty($body['token'])){
	            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
	            
	            if(!empty($this->user)){
	                return true;
	            }
	        }

	    	return false;
	    }

	    function get_activity_post_permissions($request){
	    	$body = json_decode(stripslashes($_POST['body']),true);
	    	
	        	$token = sanitize_text_field($body['token']);
	       
	       
	        if(!empty($token)){
	            $this->user = apply_filters('vibebp_api_get_user_from_token','',$token);
	            
	            if(!empty($this->user)){
	                return true;
	            }
	        }

	    	return false;
	    }

	    function get_public_activity($request){
	    	$args = vibebp_recursive_sanitize_text_field(json_decode($request->get_body(),true));

	    	$activity_args = array();
			$activity_args['search_terms'] = (!empty($args['search'])?$args['search']:'');
			if(!empty($args['meta_key'])){
				$activity_args['meta_query'] =  array(
			        'relation' => 'AND',
			        array(
			            'key' => 'comment_of_media',
			            'value' => $args['meta_key'],
			        ),
			    );
			}else{
				if(!empty($args['filter']) && $args['filter'] === 'public'){
					$activity_args['scope'] = 'public';
					if(!empty($args['user_id'])){
						//$activity_args['filter']['user_id'] = $args['user_id'];
						$activity_args['meta_query'] =  array(
					        'relation' => 'AND',
					        array(
					            'key' => 'wall_user',
					            'value' => $args['user_id'],
					            'compare' => '='
					        ),
					    );
					}
				}
			}

			
			if(!empty($args['page'])){
				$activity_args['page'] = $args['page'];
			}
			$activity_args['per_page'] = 20;

			$activity_args['display_comments'] = 'stream';
			if(empty($activity_args['meta_query'])){
				$activity_args['meta_query'] = [];
			}
			$excluded_acts = [];
	        if(!empty($this->user->id)){
	        	global $wpdb,$bp;
				$reported_acts = $wpdb->get_results("SELECT activity_id
	                    FROM  {$bp->activity->table_name_meta} 
	                    WHERE  meta_key IN ('inappropriate','spam','hide')      
	                    AND     meta_value     = {$this->user->id}"
	            );
	           
				if(!empty($reported_acts)){
					foreach ($reported_acts as $key => $ra) {
						$excluded_acts[] = $ra->activity_id;
					}
				}
				$activity_args['exclude'] = $excluded_acts;
	        }else{
	        	global $wpdb,$bp;
				$reported_acts = $wpdb->get_results("SELECT activity_id
	                    FROM  {$bp->activity->table_name_meta} 
	                    WHERE  meta_key IN ('inappropriate','spam','hide')");
	            $excluded_acts = [];
				if(!empty($reported_acts)){
					foreach ($reported_acts as $key => $ra) {
						$excluded_acts[] = $ra->activity_id;
					}
				}
	        	$activity_args['exclude'] = $excluded_acts;
	        }
	        
			$activity_args = apply_filters('vibebp_api_get_activity',$activity_args,$args,$this->user->id);
	    	//check this
	    	$run = bp_activity_get($activity_args);

	    	$activity_ids = wp_list_pluck( $run['activities'], 'id');
	    	$activities = [];
	    	if(!empty($run['activities'])){
	    		foreach($run['activities'] as $key=>$activity){
	    			if(in_array($activity->secondary_item_id, $activity_ids)){
	    			}else{
	    				$all_meta = bp_activity_get_meta( $activity->id,'',false );
	    				$run['activities'][$key]->meta=$all_meta;
	    				
	    				$run['activities'][$key]->reactions = vibebp_get_activity_reactions($activity->id);

	    				if(!empty($run['activities'][$key]->reactions)){
	    					foreach($run['activities'][$key]->reactions as $reaction => $user_ids){
	    						if(in_array($this->user->id,$user_ids)){
	    							$run['activities'][$key]->my_reaction = $reaction;
	    						}
	    					}
	    				}
	    				$run['activities'][$key]= $this->check_children_meta($run['activities'][$key],$this->user->id);
	    				
	    				$activities[]=$run['activities'][$key];
	    			}
	    		}
	    		$run['activities']=$activities;
	    	}
    		if( $run){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Activities Found','Activities Found','vibebp'),
	    			'$activity_args'=>$activity_args,
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Activities not Found','Activities not Found','vibebp'),
	    			'$activity_args'=>$activity_args,
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_get_activity', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 ); 
	    }
		
	    function get_activity($request){

	    	$args = vibebp_recursive_sanitize_text_field(json_decode($request->get_body(),true));

	    	if(empty($this->user->id)){
    			return new WP_REST_Response( array('status'=>0,'message'=>'Security error'), 200 );
    		}
		 	$excluded_acts = [];
	    	$activity_args = array();
			$activity_args['search_terms'] = (!empty($args['search'])?$args['search']:'');
			if(!empty($args['meta_key'])){
				$activity_args['meta_query'] =  array(
			        'relation' => 'AND',
			        array(
			            'key' => 'comment_of_media',
			            'value' => $args['meta_key'],
			        ),
			    );
			}else{
				if(isset($args['user_id']) && vibebp_can_access_member_details($this->user)){
					$activity_args['filter'] = array('user_id'=>$args['user_id'],'action'=>'');
				}

				if(!empty($args['filter']) && $args['filter'] === 'just-me'){
					$activity_args['scope'] = 'just-me'; 
					$activity_args['filter'] = array('user_id'=>$this->user->id,'action'=>'');
				}

				if(!empty($args['filter']) && $args['filter'] === 'activity-mentions'){
					$activity_args['search_terms'] = '@' . bp_activity_get_user_mentionname( $this->user->id );
				}

				if(!empty($args['filter']) && $args['filter'] === 'activity-favs'){
					$activity_args['scope'] = 'favorites'; 
					$activity_args['filter'] = array('user_id'=>$this->user->id,'action'=>'');
				}

				if(!empty($args['filter']) && $args['filter'] === 'activity-likes'){
					if(!empty(vibebp_get_setting('enable_reactions'))){
						$reactions = [];
						$rr = vibebp_get_reactions_array();
						if(!empty($rr)){
							foreach ($rr as $key => $r) {
								$reactions[] = 'reaction_'.$key;
							}
						}
						$activity_args['meta_query'] =  array(
					        'relation' => 'AND',
					        array(
					            'key' => $reactions,
					            'compare' => 'IN',
					            'value' => $this->user->id,
					        ),
					    );
					    $activity_args['filter'] = array('user_id'=>$this->user->id,'action'=>'');
					}else{
						$activity_args['scope'] = 'likes'; 
						$activity_args['filter'] = array('user_id'=>$this->user->id,'action'=>'');
					}
					
					
				}

				if(!empty($args['filter']) && $args['filter'] === 'activity-following'){
					$activity_args['scope'] = 'following'; 
					$activity_args['filter'] = array('user_id'=>$this->user->id,'action'=>'');
				}

				if(!empty($args['filter']) && $args['filter'] === 'activity-friends'){
					$activity_args['scope'] = 'friends'; 
					$activity_args['filter'] = array('user_id'=>$this->user->id,'action'=>'');
				}

				if(!empty($args['filter']) && $args['filter'] === 'activity-groups'){
					$activity_args['scope'] = 'groups'; 
					$activity_args['filter'] = array('user_id'=>$this->user->id,'action'=>'');
				}

				if(!empty($args['filter']) && $args['filter'] === 'groups'){
					$id=0;
					if(!empty($args['item_id'])){$id=$args['item_id'];}
					if(!empty($args['id'])){$id=$args['id'];}
					$activity_args['filter'] = array('primary_id'=>$id,'object'=>'groups');
				}

				if(!empty($args['sorter'])){

					$activity_args['filter']['action'] = $args['sorter'];

				}
				if(!empty($args['filter']) && $args['filter'] === 'public'){

					$activity_args['scope'] = 'public';
					if(!empty($args['user_id'])){
						$activity_args['filter']['user_id'] = $args['user_id'];
					}else{
						//$activity_args['filter'] = array('user_id'=>$this->user->id,'action'=>'');
						$activity_args['meta_query']=['relation'=>'AND',['key'=>'wall_user','value'=>$this->user->id,'compare'=>'=']];
					}

				}
				if(!empty($args['filter']) && $args['filter'] === 'reported'){
					global $wpdb,$bp;
					$reported_acts = $wpdb->get_results("SELECT activity_id
                    FROM  {$bp->activity->table_name_meta} 
                    WHERE  meta_key IN ('inappropriate','spam','hide')      
                    AND     meta_value     = {$this->user->id}");
					if(!empty($reported_acts)){
						foreach ($reported_acts as $key => $ra) {
							$excluded_acts[] = $ra->activity_id;
						}
					}
					if(empty($excluded_acts)){
						$excluded_acts[] = 99999999999;
					}
					$activity_args['in'] = $excluded_acts;

				}
			}

			
			if(!empty($args['page'])){
				$activity_args['page'] = $args['page'];
			}
			$activity_args['per_page'] = 20;

			$activity_args['display_comments'] = 'stream';
			if(empty($activity_args['meta_query'])){
				$activity_args['meta_query'] = [];
			}
			if(empty($excluded_acts)){
				global $wpdb,$bp;
				$reported_acts = $wpdb->get_results("SELECT activity_id
	                    FROM  {$bp->activity->table_name_meta} 
	                    WHERE  meta_key IN ('inappropriate','spam','hide')      
	                    AND     meta_value     = {$this->user->id}"
	            );
	           
				if(!empty($reported_acts)){
					foreach ($reported_acts as $key => $ra) {
						$excluded_acts[] = $ra->activity_id;
					}
				}
				$activity_args['exclude'] = $excluded_acts;
			}
			
			
			$activity_args = apply_filters('vibebp_api_get_activity',$activity_args,$args,$this->user->id);

	    	$run = bp_activity_get($activity_args); 
	    	$activity_ids = wp_list_pluck( $run['activities'], 'id');
	    	$activities = [];
	    	if(!empty($run['activities'])){
	    		foreach($run['activities'] as $key=>$activity){
	    			if(in_array($activity->secondary_item_id, $activity_ids)){
	    			}else{
	    				$all_meta = bp_activity_get_meta( $activity->id,'',false );
	    				$run['activities'][$key]->meta=$all_meta;
	    				
	    				$run['activities'][$key]->reactions = vibebp_get_activity_reactions($activity->id);

	    				if(!empty($run['activities'][$key]->reactions)){
	    					foreach($run['activities'][$key]->reactions as $reaction => $user_ids){
	    						if(in_array($this->user->id,$user_ids)){
	    							$run['activities'][$key]->my_reaction = $reaction;
	    						}
	    					}
	    				}
	    				$run['activities'][$key]= $this->check_children_meta($run['activities'][$key],$this->user->id);
	    				
	    				$activities[]=$run['activities'][$key];
	    			}
	    		}
	    		$run['activities']=$activities;
	    	}
    		if( $run){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Activities Found','Activities Found','vibebp'),
	    			'$activity_args'=>$activity_args,
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Activities not Found','Activities not Found','vibebp'),
	    			'$activity_args'=>$activity_args,
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_get_activity', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 ); 

	    }

	    function check_children_meta($activity,$user_id=null){
	    	if(!empty($activity->children)){
				foreach($activity->children as $k=>$child){
					$all_meta = bp_activity_get_meta( $child->id,'',false );
					$activity->children[$k]->meta=$all_meta;
					$activity->children[$k]->my_reaction = vibebp_get_user_activity_reaction($child->id,$user_id);
					if(!empty($child->children)){
						$child = $this->check_children_meta($child,$user_id);
					}
				}
			}

	    	return $activity;
	    }

	    function post_activity($request){


	    	$body = json_decode(stripslashes($_POST['body']),true);
	    	
	    	if(empty($this->user->id) || empty($body['args'])){
	    		return new WP_REST_Response( array('status'=>0,'message'=>'Security error'), 200 );
	    	}

	    	
	    	$args = $body['args'];
	    	if($args['component'] == 'activity_comment'){
	    		$activity_args = array(
	    			'content'			=>wp_kses_post($args['content']),
	    			'component'         => sanitize_text_field($args['component']),
	    			'user_id'			=>intval($this->user->id),
	    			'activity_id'		=>intval($args['parent_id']),
	    			'parent_id'			=>(!empty($args['component_id'])?intval($args['component_id']):0),
	    		);
	    		 
	    		$activity_id = bp_activity_new_comment($activity_args); 
	    		if(sanitize_text_field($args['component']) == 'public'){
	    			bp_activity_add_meta( $activity_id, 'wall_user', intval($this->user->id), false );
	    		}

	    	}else{
	    		if(!empty($args['component_id']) && $args['component_id'] == 'group'){
	    			$args['component_id']='groups';
	    		}
	    		$activity_args = array(
		    		'content'			=>wp_kses_post($args['content']),
	    			'user_id'			=>$this->user->id,
	    			'item_id'			=>(!empty($args['component_id'])?intval($args['component_id']):0),
	    			'secondary_item_id'=>(!empty($args['secondary_item_id'])?intval($args['secondary_item_id']):0),
					'component'         =>(!empty($args['component'])?sanitize_text_field($args['component']):'activity'),
					'type'              => (!empty($args['type'])?sanitize_text_field($args['type']):'activity_update'),
					'parent_id'			=>intval($args['parent_id']),
		    	);
	    		if(!empty($args['type']) && $args['type']=='public'){
	    			$activity_args['action']= sprintf(_x('%s posted an update.','','vibebp'),bp_core_get_userlink($this->user->id));
	    		}
		    	$activity_id = bp_activity_add($activity_args);
	    	}

	    	if(is_numeric($activity_id)){
    			
	    		
    			if(!empty($_FILES) && !empty($args['meta'])){
    				if ( ! function_exists( 'wp_handle_upload' ) ) {
					    require_once( ABSPATH . 'wp-admin/includes/file.php' );
					}
					
					$upload_overrides = array(
					    'test_form' => false
					);
					foreach($args['meta'] as $key=>$meta){
						if($key == 'image'){

						}
						$uploadedfiles = $_FILES['files_'.$key];
						
						$movefile = wp_handle_upload( $uploadedfiles, $upload_overrides );
						
						if ( $movefile && ! isset( $movefile['error'] ) ) {
							$args['meta'][$key]['value'] = $movefile['url'];
							do_action('vibebp_upload_attachment',$movefile['url'],$this->user->id);
						}
					}
    			}
    			if(!empty($body['medias'])){
    				foreach($body['medias'] as $media){
    					bp_activity_add_meta( $activity_id, sanitize_text_field($media['type']), wp_kses_post($media['url']), false );
    				}
    			}

    			if(!empty($args['meta'])){
    				foreach($args['meta'] as $meta){
    					//process upload and get a meta value
    					bp_activity_add_meta( $activity_id, sanitize_text_field($meta['key']), wp_kses_post($meta['value']), false );
    				}
    			}

    			if(!empty($args['meta_key'])){
    				bp_activity_add_meta( $activity_id, 'comment_of_media', wp_kses_post($args['meta_key']), false );
    			}
    			if(!empty($args['wallUserId'])){
    				bp_activity_add_meta( $activity_id, 'wall_user', intval($args['wallUserId']), false );
    			}
    		}else{
    			return new WP_REST_Response( array('status'=>0,'message'=>__('Activity not saved !','vibebp'),'$activity_args'=>$activity_args), 200 );
    		}

    		if($args['component'] == 'activity_comment'){
    			$activity = array(
    				'id'=>intval($activity_id),
					'avatar'=> esc_url($this->user->avatar),
					'component'=>'activity',
					'content'=> wp_kses_post($args['content']),
					'item_id'=> intval($args['parent_id']),
					'secondary_item_id'=>(!empty($args['component_id'])?intval( $args['component_id']):0),
					'type'=> 'activity_comment',
					'display_name'=>  sanitize_text_field($this->user->displayname),
					'user_id'=>intval($this->user->id)
    			);
    			$all_meta = bp_activity_get_meta( $activity_id,'',false );
    			$activity['meta']=$all_meta;
    			$activity['action'] = bp_activity_generate_action_string($activity);
    			if(!$activity['action']){
    				$activity['action'] = sprintf(__('%s posted new comment','vibebp'),$this->user->displayname);
    			}
    			 
    		}else{
    			$act_obj = bp_activity_get(array('in'=>array($activity_id)));
				$activity = (Array)$act_obj['activities'][0];
				$all_meta = bp_activity_get_meta( $activity_id,'',false );
    			$activity['meta']=$all_meta;
    		}
    		
    		
    		$activity['action']=strip_tags($activity['action']);
    		return new WP_REST_Response( array('status'=>1,'activity'=>$activity), 200 );
			
	    }

	    function remove_activity($request){

	    	if(empty($this->user->id)){
	    		return new WP_REST_Response( array('status'=>0,'message'=>'Security error'), 200 );
	    	}

	    	$args = json_decode($request->get_body(),true);
	    	if(!empty($args['parent_id']) && $args['parent_id'] != $args['activity_id']){
	    		bp_activity_delete_comment( intval($args['parent_id']), intval($args['activity_id']) );
	    	}else{
	    		bp_activity_delete(array('id'=>intval($args['activity_id'])) );
	    	}
			return new WP_REST_Response( array('status'=>1,'message'=>__('Activity removed','vibebp')), 200 );
	    }

	    function report_activity($request){

	    	if(empty($this->user->id)){
	    		return new WP_REST_Response( array('status'=>0,'message'=>'Security error'), 200 );
	    	}

	    	$args = json_decode($request->get_body(),true);
	    	$args['type'] = sanitize_text_field($args['type']);
	    	if(!empty($args['activity'])){
	    		$values = bp_activity_get_meta($args['activity']['id'],$args['type'] ,false);
	    		if(empty($values)){
	    			$values = [];
	    		}
		    	if(!in_array($this->user->id, $values)){
	    			bp_activity_add_meta( $args['activity']['id'], $args['type'], $this->user->id, false );
		    	}
	    	}
	    	
			return new WP_REST_Response( array('status'=>1,'message'=>__('Post reported!','vibebp')), 200 );
	    }

	    function vibe_bp_api_add_favorite($request){

	    	if(empty($this->user->id)){
	    		return new WP_REST_Response( array('status'=>0,'message'=>'Security error'), 200 );
	    	}

	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);
	    	$activity_id = intval($args['activity_id']);
	    	$user_id  = $this->user->id;

	    	$run = bp_activity_add_user_favorite( $activity_id, $user_id  );
    		if( $run){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Activity added as Favorite','Activity added as Favorite','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Activity not added as Favorite','Activity not added as Favorite','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_add_favorite', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 ); 
	    }

	    function vibe_bp_api_remove_favorite($request){

	    	if(empty($this->user->id)){
	    		return new WP_REST_Response( array('status'=>0,'message'=>'Security error'), 200 );
	    	}

	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);
	    	$activity_id = intval($args['activity_id']);
	    	$user_id  = $this->user->id;

	    	$run = bp_activity_remove_user_favorite( $activity_id, $user_id  );
    		if( $run){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Activity removed from favorite','Activity removed as Favorite','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Activity not removed as Favorite','Activity not removed as Favorite','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_remove_favorite', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 ); 
	    }

	    function vibe_bp_api_add_like($request){


	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);
	    	$activity_id = intval($args['activity_id']);
	    	$user_id  = $this->user->id;

	    	$run = vibebp_activity_add_user_like( $activity_id, $user_id  );
    		if( $run){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Activity liked','Activity like','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Activity unliked','Activity unliked','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_add_favorite', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 ); 
	    }

	    function vibe_bp_api_udpate_reaction($request){
	    	$args = json_decode(file_get_contents('php://input'),true);
	    	$data = array('status'=>0,'message'=>'');
	    	if(!empty($args['activity'])){
	    		if(!empty($args['old'])){
    				bp_activity_delete_meta($args['activity']['id'],'reaction_'.$args['old'],$this->user->id);
    				$data = array('status'=>1,'message'=>_x('Reaction removed from post!','','vibebp'));
    			}
	    		if(!empty($args['activity']['my_reaction'])){

	    			$reactions = bp_activity_get_meta($args['activity']['id'],'reaction_'.$args['activity']['my_reaction'],false);
		    		if(empty($reactions)){
		    			$reactions=[];
		    		}
		    		if(!in_array($this->user->id, $reactions)){
		    			bp_activity_add_meta($args['activity']['id'],'reaction_'.$args['activity']['my_reaction'],$this->user->id);
		    		}
		    		$data = array('status'=>1,'message'=>_x('Reacted on post!','','vibebp'));
	    		}

	    	}
	    	return new WP_REST_Response( $data, 200 ); 
	    }

		function vibe_bp_api_remove_like($request){


	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);
	    	$activity_id = intval($args['activity_id']);
	    	$user_id  = $this->user->id;

	    	$run = vibebp_activity_remove_user_like( $activity_id, $user_id  );
    		if( $run){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Activity like removed','activity like removed','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Activity like removed','activity like removed','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_remove_favorite', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 ); 
	    }


	}
}

VIBE_BP_API_Rest_Activity_Controller::init();