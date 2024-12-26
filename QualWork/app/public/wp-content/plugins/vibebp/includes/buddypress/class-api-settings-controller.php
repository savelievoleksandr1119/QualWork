<?php

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'VIBE_BP_API_Rest_Settings_Controller' ) ) {
	
	class VIBE_BP_API_Rest_Settings_Controller extends WP_REST_Controller{
		
		public static $instance;
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new VIBE_BP_API_Rest_Settings_Controller();
	        return self::$instance;
	    }
	    public function __construct( ) {
			$this->namespace = Vibe_BP_API_NAMESPACE;
			$this->type= Vibe_BP_API_SETTINGS_TYPE;
			$this->register_routes();
		}

		public function register_routes() {

			register_rest_route( $this->namespace, '/' .$this->type.'/save/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'save_general_settings' ),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/email/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_user_email_settings' ),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));


			register_rest_route( $this->namespace, '/' .$this->type.'/email/set', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'set_email_notification_settings' ),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/export_data', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_export_data_settings' ),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/logins/get', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_login_data_settings' ),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/logins/remove', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'remove_login_data_settings' ),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/export_data/request', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_export_data_settings_request' ),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));
			register_rest_route( $this->namespace, '/profile/avatar', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'set_avatar'),
					'permission_callback' => array( $this, 'get_get_avatar_settings_permissions' ),
				),
			));
			
			register_rest_route( $this->namespace, '/profile/avatar/upload', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'upload_avatar'),
					'permission_callback' => array( $this, 'get_get_avatar_settings_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/profile/avatar/crop', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'crop_avatar'),
					'permission_callback' => array( $this, 'get_get_avatar_settings_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/avatar', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'get_avatar'),
					'permission_callback' => array( $this, 'get_client_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/avatars', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'get_avatars'),
					'permission_callback' => array( $this, 'get_client_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/cover', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'get_cover'),
					'permission_callback' => array( $this, 'get_client_permissions' ),
				),
			));
			register_rest_route( $this->namespace, '/component/cover', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'set_cover'),
					'permission_callback' => array( $this, 'get_get_avatar_settings_permissions' ),
				),
			));
			
			register_rest_route( $this->namespace, '/search/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'search'),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/wall/whocanpost/get', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'whocanpost_get'),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));
			register_rest_route( $this->namespace, '/wall/whocanpost/set', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'whocanpost_set'),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));
			register_rest_route( $this->namespace, '/wall/whocanpost/check', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'whocanpost_check'),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));
			register_rest_route( $this->namespace, '/' .$this->type.'/candeleteaccount/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'can_delete_account' ),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));
			register_rest_route( $this->namespace, '/' .$this->type.'/deleteaccount/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'delete_account' ),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/selectcpt/(?P<cpt>\w+)', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'selectcpt' ),
					'permission_callback'       => array( $this, 'get_settings_permissions' ),
					'args'                      =>  array(
					'cpt'                        =>  array(
						'validate_callback'     =>  function( $param, $request, $key ) {
													return !empty( $param );
												}
						),
					),
				),
			));
			register_rest_route( $this->namespace, '/taxonomy', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'get_taxonomy' ),
					'permission_callback'       => array( $this, 'get_settings_permissions' ),
					'args'                      =>  array(
					'taxonomy'                        =>  array(
						'validate_callback'     =>  function( $param, $request, $key ) {
													return !empty( $param );
												}
						),
					),
				),
			));
	
	
			register_rest_route( $this->namespace, '/createElement/(?P<cpt>\w+)', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'create_element' ),
					'permission_callback'       => array( $this, 'get_settings_permissions' ),
					'args'                      =>  array(
					'cpt'                        =>  array(
						'validate_callback'     =>  function( $param, $request, $key ) {
													return !empty( $param );
												}
						),
					),
				),
			));

		}


		/*
	    PERMISSIONS
	     */
	    function get_client_permissions($request){
	    	
           	$client_id = $request->get_param('client_id');
           	if($client_id == vibebp_get_setting('client_id')){
           		return true;
           	}

           	return $this->get_settings_permissions($request);
	        
	    }
	    function get_settings_permissions($request){

	    	$body = json_decode($request->get_body(),true);
	    	$token  = '';
	    	if(!empty($body['token'])){
	       		$body['token'] = sanitize_text_field($body['token']);
	       		$token = $body['token'];
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
	    	}
	        
        	
	        
	        /** Get the Secret Key */
	        
	    	

	    	return false;
	    }

	    function get_avatar($request){

	    	$body = json_decode($request->get_body(),true);
	    	$body = vibebp_recursive_sanitize_text_field($body);
	    	$name = '';
	    	$avatar= '';
	    	$key='';
	    	$type = '';
	    	if(!empty($body['type'])){$type=$body['type'];}
	    	switch($type){
    			case 'friends':
    			
				$key = 'user_'.$body['ids']['item_id'];
				$avatar = bp_core_fetch_avatar(array(
                    'item_id' => (int)$body['ids']['item_id'],
                    'object'  => 'user',
                    'type'=>'thumb',
                    'html'    => false
                ));
                $name = bp_core_get_user_displayname($body['ids']['item_id']);
    			
    				
    			break;
    			case 'group':
    				$key = 'group_'.$body['ids']['item_id'];
    				$avatar = bp_core_fetch_avatar(array(
                        'item_id' => (int)$body['ids']['item_id'],
                        'object'  => 'group',
                        'type'=>'thumb',
                        'html'    => false
                    ));
                    global $wpdb,$bp;
                    $name = $wpdb->get_var("SELECT name from {$bp->groups->table_name} WHERE id=".intval($body['ids']['item_id']));
    			break;
    			case 'activity':
    				$key = 'user_'.$body['ids']['secondary_item_id'];
    				$avatar = bp_core_fetch_avatar(array(
                        'item_id' => (int)$body['ids']['secondary_item_id'],
                        'object'  => 'user',
                        'type'=>'thumb',
                        'html'    => false
                    ));
                    $name = bp_core_get_user_displayname($body['ids']['secondary_item_id']);
				break;
				case 'forum':
					$key = 'forum_'.(int)$body['ids']['item_id'];
    				$avatar = get_the_post_thumbnail_url($body['ids']['item_id']);
                    $name = get_the_title($body['ids']['item_id']);
				break;
				case 'course':
    				$key = 'course_'.(int)$body['ids']['item_id'];
    				$avatar = get_the_post_thumbnail_url($body['ids']['item_id']);
                    $name = get_the_title($body['ids']['item_id']);
				break;
    			default:
    				if(empty($body['ids']['user_id']) && !empty($body['ids']['item_id']) && is_numeric($body['ids']['item_id'])){
    					$name = get_the_title($body['ids']['item_id']);
    					$avatar = get_the_post_thumbnail_url($body['ids']['item_id'], 'medium' );
						if(empty($avatar)){
							$avatar = plugins_url( '../../assets/images/avatar.jpg',  __FILE__ );
						}
    				}else{
    					if(empty($body['ids']['user_id']) && empty($body['ids']['item_id'])){
	    					$temp = $body['ids'];
	    					$body['ids'] = [];
	    					$body['ids']['user_id'] =	$temp ;
	    				}

	    				$name= 'N.A';
	    				$avatar = apply_filters('vibebp_get_avatar','',$body['ids']['user_id']);
	    	
	   
	    				if(empty($avatar) && !empty($body['ids']['user_id'])){
	    				
	    					$key = apply_filters('vibebp_get_avatar_key','user_'.$body['ids']['user_id'],$type,$body['ids']);
	    					$avatar = bp_core_fetch_avatar(array(
	                        'item_id' => (int)$body['ids']['user_id'],
	                        'object'  => 'user',
	                        'type'=>'thumb',
	                        'html'    => false
	                    	));
	                    	if(empty($avatar)){
	                    		$avatar =plugins_url('../assets/img/avatar.jpg',__FILE__);
	                    	}
	                    	$name = bp_core_get_user_displayname($body['ids']['user_id']);
	    				}
	                     
	                     
    				}
    				
    			break;
    		}
		    return array('avatar'=>$avatar,'name'=>$name);		
	    }

	    function get_avatars($request){
	    	$body = json_decode($request->get_body(),true);
	    	$values = [];
	    	switch($body['type']){
    			case 'friends':
    			
    			foreach($body['ids'] as $id){
    				$key = 'user_'.$id['key'];
					$avatar = bp_core_fetch_avatar(array(
	                    'item_id' => (int)$id['key'],
	                    'object'  => 'user',
	                    'type'=>'thumb',
	                    'html'    => false
	                ));
	                $name = bp_core_get_user_displayname($id['key']);
	                $values[] = ['type'=>'user','key'=>$id['key'],'value'=>['avatar'=>$avatar,'name'=>$name]];
    			}
				
    			
    			break;
    			case 'group':
    			foreach($body['ids'] as $id){
    				$key = 'group_'.$id['item_id'];
    				$avatar = bp_core_fetch_avatar(array(
                        'item_id' => (int)$id['item_id'],
                        'object'  => 'group',
                        'type'=>'thumb',
                        'html'    => false
                    ));
                    global $wpdb,$bp;
                    $name = $wpdb->get_var("SELECT name from {$bp->groups->table_name} WHERE id=".intval($body['ids']['item_id']));
                    $values[] = ['type'=>'group','key'=>$body['ids']['item_id'],'value'=>['avatar'=>$avatar,'name'=>$name]];
    			}
    			break;
    			case 'activity':
    			foreach($body['ids'] as $id){
    				$key = 'user_'.$body['ids']['secondary_item_id'];
    				$avatar = bp_core_fetch_avatar(array(
                        'item_id' => (int)$body['ids']['secondary_item_id'],
                        'object'  => 'user',
                        'type'=>'thumb',
                        'html'    => false
                    ));
                    $name = bp_core_get_user_displayname($body['ids']['secondary_item_id']);
                    $values[] = ['type'=>'user','key'=>$body['ids']['item_id'],'value'=>['avatar'=>$avatar,'name'=>$name]];
    			}
				break;
				case 'forum':
				foreach($body['ids'] as $id){
					$key = 'forum_'.(int)$id['key'];
    				$avatar = get_the_post_thumbnail_url($id['key']);
                    $name = get_the_title($id['key']);
                    $values[] = ['type'=>'forum','key'=>$id['key'],'value'=>['avatar'=>$avatar,'name'=>$name]];
    			}
				break;
				case 'course':
				foreach($body['ids'] as $id){
    				$key = 'course_'.(int)$id['key'];
    				$avatar = get_the_post_thumbnail_url($id['key']);
                    $name = get_the_title($id['key']);
                    $values[] = ['type'=>'course','key'=>$id['key'],'value'=>['avatar'=>$avatar,'name'=>$name]];
    			}
				break;
    			default:
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
    			break;
    		}
		    return array('status'=>1,'values'=>$values);	
	    }
	    
	    function get_cover($request){

	    	$body = json_decode($request->get_body(),true);
	    	$body = vibebp_recursive_sanitize_text_field($body);
	    	$name = '';
	    	if(!empty($body['type'])){
	    		switch($body['type']){
	    			
	    			case 'group':
	    				$key = 'group_'.(int)$body['ids']['item_id'];
	    				$avatar = bp_attachments_get_attachment('url', array(
					          'object_dir' => 'groups',
					          'item_id' => (int)$body['ids']['item_id'],
					    ));
                        global $wpdb,$bp;
	    			break;
	    			
					
					
	    			default:
	    				if(empty($body['ids']['user_id'])){
	    					$temp = $body['ids'];
	    					$body['ids'] = [];
	    					$body['ids']['user_id'] =	$temp ;
	    				}
	    				$key = apply_filters('vibebp_get_avatar_key','user_'.(int)$body['ids']['user_id'],$body['type'],$body['ids']);

	    				$avatar = apply_filters('vibebp_get_cover',bp_attachments_get_attachment('url', array(
					          'object_dir' => 'members',
					          'item_id' => (int)$body['ids']['user_id'],
					    )),$body['type'],$body['ids']);
	    			break;
	    		}
	    	}

	    	return new WP_REST_Response( array('status'=>1,'value'=>array('cover'=>$avatar),'key'=>$key), 200 ); 
	    }
	    
	    function whocanpost_get($request){
	    	$body = json_decode($request->get_body(),true);
	    	$value=  false;
	    	if(!empty($body['user_id'])){
	    		$value =get_post_meta($body['user_id'],'vibebp_wall_who_can_post',true);
	    	}else{
	    		$value =get_post_meta($this->user->id,'vibebp_wall_who_can_post',true);
	    	}
	    	return new WP_REST_Response( array('status'=>1,'value'=>$value), 200 );

	    }

	    function whocanpost_set($request){
	    	$body = json_decode($request->get_body(),true);
	    	$body = vibebp_recursive_sanitize_text_field($body);
	    	if(!empty($body['value'])){
	    		update_post_meta($this->user->id,'vibebp_wall_who_can_post',$body['value']);
	    	}
	    	return new WP_REST_Response( array('status'=>1,'message'=>_x('Wall setting saved!','','')), 200 );
	    	
	    }

	    function whocanpost_check($request){
	    	$body = json_decode($request->get_body(),true);
	    	$data = array('status'=>1,'value'=>0);
	    	if(!empty($body['user_id'])){
	    		$setting =get_post_meta($body['user_id'],'vibebp_wall_who_can_post',true);
	    		if(empty($setting) || $setting=='all'){
	    			$data['value'] = true;
	    		}else{
	    			if($setting=='friends'){
	    				if(function_exists('friends_check_friendship')){
	    					$data['value'] =friends_check_friendship( $this->user->id, $body['user_id'] );
	    				}
	    			}
	    		}
	    	}
	    	return new WP_REST_Response( $data, 200 );

	    }

	    function search($request){
	    	$body = json_decode($request->get_body(),true);
	    	$body = vibebp_recursive_sanitize_text_field($body);
	    	$return = array();
			if(empty($body['type'])){
				$body['type'] = 'member';
			}
	    	switch($body['type']){
	    		case 'user':
	    		case 'member':
	    			$args = array(
						'search'         => '*'.esc_attr( $body['search'] ).'*',
						'search_columns' => array( 'user_login', 'user_email','user_nicename','display_name' ),
						'number'=>9999,
						'fields'=>array('ID','display_name')
					);
					$args = apply_filters('vibebp_member_search_args',$args,$body,$request);
					$user_query = new WP_User_Query( $args );
					$results = $user_query->get_results();
					
					if(!empty($results)){
						foreach($results as $user){
							$return[]= array('id'=>$user->ID,'name'=>$user->display_name,'avatar'=>$avatar = apply_filters('vibebp_get_avatar',bp_core_fetch_avatar(array(
		                            'item_id' => $user->ID,
		                            'object'  => 'user',
		                            'type'=>'thumb',
		                            'html'    => false
		                        ))));
						}
					}
	    		break;
	    		case 'group':
	    			
    				$run = groups_get_groups(array('search_terms'=>$body['search'])); 
		    		if( count($run['groups']) ) {
		    			foreach($run['groups'] as $k=>$group){
		    				$return[] = array(
		    					'id'=>$group->id,
		    					'name'=>$group->name,
		    					'avatar'=>bp_core_fetch_avatar(array(
		                            'item_id' => $group->id,
		                            'object'  => 'group',
		                            'type'=> empty($args->full_avatar)?'thumb':'full',
		                            'html'    => false
		                        ))
		    				);
		    				$run['groups'][$k];
		    			}
		    	    }
	    		break;
	    	}

	    	return new WP_REST_Response( array('status'=>1,'results'=>apply_filters('vibebp_search_results',$return,$body)), 200 );
	    }

	    function save_general_settings($request){

	    	$args = json_decode($request->get_body(),true);
	    	$args = vibebp_recursive_sanitize_text_field($args);
	    	$status = 1;
	    	$update_user = get_userdata( $this->user->id );


	    	$bp            = buddypress(); // The instance
			$email_error   = false;        // invalid|blocked|taken|empty|nochange
			$pass_error    = false;        // invalid|mismatch|empty|nochange
			$pass_changed  = false;        // true if the user changes their password
			$email_changed = false;        // true if the user changes their email
			$feedback_type = 'error';      // success|error
			$feedback      = array();      // array of strings for feedback.
			$type = $args['type'];

	    	// Validate the user again for the current password when making a big change.
			if ($type=='email' ) {
				if(( is_super_admin() ) || ( !empty( $args['pwd'] ) && wp_check_password( $args['pwd'], $update_user->user_pass, $this->user->id ) )){
					if ( !empty( $args['email'] ) ) {

						// What is missing from the profile page vs signup -
						// let's double check the goodies.
						$user_email     = sanitize_email( esc_html( trim( $args['email'] ) ) );
						if(!empty($this->user->email)){
							$old_user_email = $this->user->email;
						}
						if(!empty($this->user->user_email)){
							$old_user_email = $this->user->user_email;
						}

						// User is changing email address.
						if ( $old_user_email != $user_email ) {

							// Run some tests on the email address.
							$email_checks = bp_core_validate_email_address( $user_email );

							if ( true !== $email_checks ) {
								if ( isset( $email_checks['invalid'] ) ) {
									$email_error = 'invalid';
								}

								if ( isset( $email_checks['domain_banned'] ) || isset( $email_checks['domain_not_allowed'] ) ) {
									$email_error = 'blocked';
								}

								if ( isset( $email_checks['in_use'] ) ) {
									$email_error = 'taken';
								}
							}

							// Store a hash to enable email validation.
							if ( false === $email_error ) {
								$hash = wp_generate_password( 32, false );

								$pending_email = array(
									'hash'     => $hash,
									'newemail' => $user_email,
								);

								bp_update_user_meta( $this->user->id, 'pending_email_change', $pending_email );
								$verify_link = bp_core_get_user_domain($this->user->id) . bp_get_settings_slug() . '/?verify_email_change=' . $hash;

								

								// Send the verification email.
								$args = array(
									'tokens' => array(
										'displayname'    => bp_core_get_user_displayname( $this->user->id ),
										'old-user.email' => $old_user_email,
										'user.email'     => $user_email,
										'verify.url'     => esc_url( $verify_link ),
									),
								);
								bp_send_email( 'settings-verify-email-change', [$old_user_email], $args );

								// We mark that the change has taken place so as to ensure a
								// success message, even though verification is still required.
								$args['email'] = $update_user->user_email;
								$email_changed = true;
							}

						// No change.
						} else {
							$email_error = false;
						}

					// Email address cannot be empty.
					} else {

						$email_error = 'empty';
					}
					
					

					
				}else{
					$pass_error = 'invalid';
				}
				

				/* Email Change Attempt ******************************************/

				

			// Password Error.
			} elseif($type=='password' && !user_can($this->user->id,'manage_options')) {
				

				/* Password Change Attempt ***************************************/

				if ( !empty( $args['pass1'] ) && !empty( $args['pass2'] ) ) {

					if ( ( $args['pass1'] == $args['pass2'] ) && !strpos( " " . wp_unslash( $args['pass1'] ), "\\" ) ) {

						// Password change attempt is successful.
						if ( ( ! empty( $update_user->user_pass ) && $update_user->user_pass != $args['pass1'] ) || is_super_admin() )  {
							$update_user->user_pass = $args['pass1'];
							$pass_changed = true;

						// The new password is the same as the current password.
						} else {
							$pass_error = 'same';
						}

					// Password change attempt was unsuccessful.
					} else {
						$pass_error = 'mismatch';
					}

				// Both password fields were empty.
				} elseif ( empty( $args['pass1'] ) && empty( $args['pass2'] ) ) {
					$pass_error = false;

				// One of the password boxes was left empty.
				} elseif ( ( empty( $args['pass1'] ) && !empty( $args['pass2'] ) ) || ( !empty( $args['pass1'] ) && empty( $args['pass2'] ) ) ) {
					$pass_error = 'empty';
				}

				// The structure of the $update_user object changed in WP 3.3, but
				// wp_update_user() still expects the old format.
				if ( isset( $update_user->data ) && is_object( $update_user->data ) ) {
					$update_user = $update_user->data;
					$update_user = get_object_vars( $update_user );

					// Unset the password field to prevent it from emptying out the
					// user's user_pass field in the database.
					// @see wp_update_user().
					if ( false === $pass_changed ) {
						unset( $update_user['user_pass'] );
					}
				}
				
			}elseif($type =='delete_account' && !in_array('manage_options',$this->user->caps)){
				// Bail if account deletion is disabled.
				//set buddypress current user

				$feedback_type ='error';

				if ( bp_disable_account_deletion() && ! bp_user_can($this->user->id, 'delete_users' ) ) {
					$feedback['permissions_error']    = __( 'Unsufficient permissions to delete account.', 'vibebp' );
				}

				if ( bp_core_delete_account( $this->user->id ) ) {
					$feedback_type ='success';
					$feedback['permissions_error']    = __( 'Account Deleted. Log out from site.', 'vibebp' );
				}
			}

			// Email feedback.
			switch ( $email_error ) {
				case 'invalid' :
					$feedback['email_invalid']  = __( 'That email address is invalid. Check the formatting and try again.', 'vibebp' );
					break;
				case 'blocked' :
					$feedback['email_blocked']  = __( 'That email address is currently unavailable for use.', 'vibebp' );
					break;
				case 'taken' :
					$feedback['email_taken']    = __( 'That email address is already taken.', 'vibebp' );
					break;
				case 'empty' :
					$feedback['email_empty']    = __( 'Email address cannot be empty.', 'vibebp' );
					break;
				case false :
					// No change.
					break;
			}
			// Password feedback.
			switch ( $pass_error ) {
				case 'invalid' :
					$feedback['pass_error']    = __( 'Your current password is invalid.', 'vibebp' );
					break;
				case 'mismatch' :
					$feedback['pass_mismatch'] = __( 'The new password fields did not match.', 'vibebp' );
					break;
				case 'empty' :
					$feedback['pass_empty']    = __( 'One of the password fields was empty.', 'vibebp' );
					break;
				case 'same' :
					$feedback['pass_same'] 	   = __( 'The new password must be different from the current password.', 'vibebp' );
					break;
				case false :
					// No change.
					break;
			}
			

			// No errors so show a simple success message.
			if ( ( ( false === $email_error ) || ( false == $pass_error ) ) && ( ( true === $pass_changed ) || ( true === $email_changed ) ) ) {

				// Clear cached data, so that the changed settings take effect
					// on the current page load.
				if ( ( false === $email_error ) && ( false === $pass_error ) && ( wp_update_user( $update_user ) ) ) {
					$this->user = bp_core_get_core_userdata( $this->user->id );
					$feedback[]    = __( 'Your settings have been saved.', 'vibebp' );
					$feedback_type = 'success';
				}
				

			// Some kind of errors occurred.
			} elseif ( ( ( false === $email_error ) || ( false === $pass_error ) ) && ( ( false === $pass_changed ) || ( false === $email_changed ) ) ) {
				if ( bp_is_my_profile() ) {
					$feedback['nochange'] = __( 'No changes were made to your account.', 'vibebp' );
				} else {
					$feedback['nochange'] = __( 'No changes were made to this account.', 'vibebp' );
				}
			}

			if(!empty($feedback_type) && $feedback_type == 'success'){
				do_action( 'bp_core_general_settings_after_save' );
				return new WP_REST_Response( array('status'=>1,'message'=>implode( "\n", $feedback )), 200 );
			}
			
			return new WP_REST_Response( array('status'=>0,'message'=>implode( "\n", $feedback )), 200 );

	    }

	    function get_login_data_settings($request){

    		$args = json_decode($request->get_body(),true);
    		$data = array('status'=>false);
    		$logins = get_user_meta($this->user->id,'vibebp_active_tokens',true);

    		if(!empty($logins)){

    			$data['status'] = true;
    			$data['data'] =array_values($logins);
    		}

    		return new WP_REST_Response( $data, 200 );

	    }

	    function remove_login_data_settings($request){

    		$args = json_decode($request->get_body(),true);
    		$args = vibebp_recursive_sanitize_array_field($args);
    		$data = array('status'=>false,'message'=>_x('Some error occured!','','vibebp'));
    		if(!empty($args['login'])){
    			$logins = get_user_meta($this->user->id,'vibebp_active_tokens',true);
    			if(empty($logins)){
    				$logins = [];
    			}
    			foreach($logins as $k=>$v){
    				if($k==$args['login']['vibebp_token_key']){
    					unset($logins[$k]);
    				}
    			}
    			update_user_meta($this->user->id,'vibebp_active_tokens',$logins);
    			$data = array('status'=>true,'message'=>_x('Active session removed!','','vibebp'));
    		}
    		return new WP_REST_Response( $data, 200 );
	    	
	    }

	    function get_user_email_settings($request){

    		$args = json_decode($request->get_body(),true);
    		$args = vibebp_recursive_sanitize_text_field($args);
    		$email_notices = array();

    		if(bp_is_active('activity')){
    			get_user_meta($this->user->id,'notification_activity_new_mention',true);

    			$email_notices['notification_activity_new_mention'] = array(
    				'label'=>sprintf(__( 'A member mentions you in an update using "@%s"', 'vibebp' ),bp_core_get_username( $this->user->id ) ),
    			);
				$email_notices['notification_activity_new_reply'] = array( 'label'=> __( "A member replies to an update or comment you've posted", 'vibebp' ));
    		}

    		if(bp_is_active('messages')){
    			$email_notices['notification_messages_new_message'] = array( 'label'=> __( 'A member sends you a new message', 'vibebp' ));
    		}

    		if(bp_is_active('friends')){
    			$email_notices['notification_friends_friendship_request'] = array( 'label'=> _x( 'A member sends you a friendship request', 'Friend settings on notification settings page', 'vibebp' ));
    			$email_notices['notification_friends_friendship_accepted']= array( 'label'=> _x( 'A member accepts your friendship request', 'Friend settings on notification settings page', 'vibebp' ));
    		}

    		if(bp_is_active('groups')){
    			$email_notices['notification_groups_invite']= array( 'label'=> _x( 'A member invites you to join a group', 'group settings on notification settings page','vibebp' ));
    			$email_notices['notification_groups_group_updated']= array( 'label'=> _x( 'Group information is updated', 'group settings on notification settings page', 'vibebp' ));
    			$email_notices['notification_groups_admin_promotion']= array( 'label'=> _x( 'You are promoted to a group administrator or moderator', 'group settings on notification settings page', 'vibebp' ));
    			$email_notices['notification_groups_membership_request']= array( 'label'=> _x( 'A member requests to join a private group for which you are an admin', 'group settings on notification settings page', 'vibebp' ));
    			$email_notices['notification_membership_request_completed']= array( 'label'=> _x( 'Your request to join a group has been approved or denied', 'group settings on notification settings page', 'vibebp' ));
    		}

    		if(!empty($email_notices)){
    			foreach($email_notices as $key=>$notice){
    				
    				$value = get_user_meta($this->user->id,$key,true);
    				if(empty($value)){
    					$value = 'yes';
    				}
    				$email_notices[$key]['value'] = $value;
    			}
    		}
    		$email_notices = apply_filters('vibebp_buddypress_email_settings',$email_notices);
    		return new WP_REST_Response( $email_notices, 200 );
    	}

	   	function set_email_notification_settings($request){

	    	$body = json_decode($request->get_body(),true);
	    	$body = vibebp_recursive_sanitize_text_field($body);
	    	if(!empty($body['setting'])){
	    		update_user_meta( $this->user->id , $body['setting'] , $body['value']);	
	    		$data=array(
	    			'status' => 1,
	    			'data' => true,
	    			'message' => _x('All Settings Updated','All Settings Not Updated','vibebp')
				);
	    	}
	    	
    		$data=apply_filters( 'vibe_bp_api_set_email_notification_settings', $data , $request );
    		return new WP_REST_Response( $data, 200 ); 
	    }

	    function get_export_data_settings($request){

			$request = bp_settings_get_personal_data_request($this->user->id);
			$can_make_new_request = false;

	    	if ( $request ){
		    	$return = array('status'=> $request->status);
		    	if ( 'request-completed' === $request->status ){
		    		if ( bp_settings_personal_data_export_exists( $request ) ){
		    			$return['message'] = __( 'Your request for an export of personal data has been completed.', 'vibebp' );
		    			$return['submessage'] = sprintf( esc_html__( 'You may download your personal data by clicking on the link below. For privacy and security, we will automatically delete the file on %s, so please download it before then.', 'vibebp' ), bp_settings_get_personal_data_expiration_date( $request ) );
						$return['report_link'] = bp_settings_get_personal_data_export_url( $request );
						$return['label']=__('Download Report','vibebp');
						$return['can_make_new_request'] = false;
		    		}else{
		    			$return['message']= __( 'Your previous request for an export of personal data has expired.', 'vibebp' );
		    			$return['submessage']=__( 'Please click on the button below to make a new request.', 'vibebp' );
						$return['report_link'] = 0;
		    			$return['label']=__('Request New Report','vibebp');
						$return['can_make_new_request'] = true;
		    		}
		    		
		    	}elseif ( 'request-confirmed' === $request->status ){
		    		$return['message']=sprintf(__( 'You previously requested an export of your personal data on %s.', 'vibebp' ), bp_settings_get_personal_data_confirmation_date( $request ) );
					$return['submessage']= __( 'You will receive a link to download your export via email once we are able to fulfill your request.', 'vibebp' );
					$return['report_link'] = 0;
					$return['label']=__('Request Confirmed','vibebp');
					$return['can_make_new_request'] = false;

		    	}
		    }else{
		    	$return = array(
					'status'=> 'not_requested',
					'label'=>__('Request Data','vibebp'
				));
		    	$return['message']=__( 'You can request an export of your personal data, containing the following items if applicable:', 'vibebp' );
				$return['report_link'] = 0;
				ob_start();
		    	bp_settings_data_exporter_items();
		    	$return['exports'] = ob_get_clean();
				$return['submessage']=__( 'If you want to make a request, please click on the button below:', 'vibebp' );
				$return['can_make_new_request'] = true;
		    }
			return new WP_REST_Response( $return, 200 ); 
		}
		
		function get_export_data_settings_request($request){
			$body = json_decode($request->get_body(),true);
			$user_id = $this->user->id;

			$user_info = get_userdata($user_id);
			$user_email = $user_info->user_email;

			$existing = bp_settings_get_personal_data_request( $user_id );
			if ( ! empty( $existing->ID ) ) {
				wp_delete_post( $existing->ID, true );
			}

			// Create the user request.
			$request_id = wp_create_user_request($user_email, 'export_personal_data' );
			$success = true;
			if ( is_wp_error( $request_id ) ) {
				$success = false;
				$message = $request_id->get_error_message();
			} elseif ( ! $request_id ) {
				$success = false;
				$message = __( 'We were unable to generate the data export request.', 'vibebp' );
			}

			/*
				* Auto-confirm the user request since the user already consented by
				* submitting our form.
			*/
			if ( $success ) {
				/** This hook is documented in /wp-login.php */
				do_action( 'user_request_action_confirmed', $request_id );
		
				$message = __( 'Data export request successfully created', 'vibebp' );
			}
			$return = array(
				'status' => $success,
				'message' => $message
			);
		
			/**
			 * Fires after a user has created a data export request.
			 *
			 * This hook can be used to intervene in the data export request process.
			 *
			 * @since 4.0.0
			 *
			 * @param int  $request_id ID of the request.
			 * @param bool $success    Whether the request was successfully created by WordPress.
			 */
			do_action( 'bp_user_data_export_requested', $request_id, $success );
			return new WP_REST_Response( $return, 200 ); 
		}

	    function get_get_avatar_settings_permissions($request){
	    	$body = json_decode(stripslashes($_POST['body']),true);
	    	$body['token'] = sanitize_text_field($body['token']);
	        if (empty($body['token'])){
           		return false;
	        }else{
	        	$token = $body['token'];
	        }
	        /** Get the Secret Key */
	        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
	        if (!$secret_key) {
	          	return false;
	        }
	        /** Try to decode the token */ /** Else return exception*/
	        try {
	            $user_data = JWT::decode($token, $secret_key, array('HS256'));
		        $this->user = $user_data->data->user;
	        	return true;

	        }catch (Exception $e) {
	            /** Something is wrong trying to decode the token, send back the error */
	            return false;
	        }
	    	

	    	return false;
	    } 

	    function set_cover($request){
	    	if(!function_exists('bp_attachments_cover_image_upload_dir'))
	    		return;
	    	$body = json_decode(stripslashes($_POST['body']),true);
	   		$body = vibebp_recursive_sanitize_array_field($body);
	    	$return = array(
	    		'status'=>1,
	    		'message'=>__('Avatar uploaded','vibebp')
	    	);
	    	$is_html4 ='';
	    	if(!empty($_FILES)){
	    		$bp_params = array(
	    			'has_cover_image'=>true,
	    		);
				$bp_params['item_id'] = (int) (!empty($body['item_id'])?$body['item_id']:$this->user->id);
				$bp_params['object']  = (!empty($body['item_id'])?'group':'user');
				// We need the object to set the uploads dir filter.
				if ( empty( $bp_params['object'] ) ) {
					bp_attachments_json_response( false, $is_html4 );
				}

				$bp          = buddypress();
				$needs_reset = array();

				// Member's cover image.
				if ( 'user' === $bp_params['object'] ) {
					$object_data = array( 'dir' => 'members', 'component' => 'members' );

					if ( ! bp_displayed_user_id() && ! empty( $bp_params['item_id'] ) ) {
						$needs_reset = array( 'key' => 'displayed_user', 'value' => $bp->displayed_user );
						$bp->displayed_user->id = $bp_params['item_id'];
					}

				// Group's cover image.
				} elseif ( 'group' === $bp_params['object'] ) {

					$object_data = array( 'dir' => 'groups', 'component' => 'groups' );

					if ( ! bp_get_current_group_id() && ! empty( $bp_params['item_id'] ) ) {
						$needs_reset = array( 'component' => 'groups', 'key' => 'current_group', 'value' => $bp->groups->current_group );
						$bp->groups->current_group = groups_get_group( $bp_params['item_id'] );
						$bp->current_component = 'groups';
					}

				// Other object's cover image.
				} else {
					$object_data = apply_filters( 'bp_attachments_cover_image_object_dir', array(), $bp_params['object'] );
				}
				// Stop here in case of a missing parameter for the object.
				if ( empty( $object_data['dir'] ) || empty( $object_data['component'] ) ) {
					bp_attachments_json_response( false, $is_html4 );
				}

				/**
				 * Filters whether or not to handle cover image uploading.
				 *
				 * If you want to override this function, make sure you return an array with the 'result' key set.
				 *
				 * @since 2.5.1
				 *
				 * @param array $value
				 * @param array $bp_params
				 * @param array $needs_reset Stores original value of certain globals we need to revert to later.
				 * @param array $object_data
				 */
				$pre_filter = apply_filters( 'bp_attachments_pre_cover_image_ajax_upload', array(), $bp_params, $needs_reset, $object_data );
				if ( isset( $pre_filter['result'] ) ) {
					bp_attachments_json_response( $pre_filter['result'], $is_html4, $pre_filter );
				}
				add_filter('bp_attachment_upload_overrides',function($overrides){
					$overrides['test_form'] = FALSE;
					return $overrides;
				});
				
				$cover_image_attachment = new BP_Attachment_Cover_Image();
				$uploaded = $cover_image_attachment->upload( $_FILES );
				// Reset objects.
				if ( ! empty( $needs_reset ) ) {
					if ( ! empty( $needs_reset['component'] ) ) {
						$bp->{$needs_reset['component']}->{$needs_reset['key']} = $needs_reset['value'];
					} else {
						$bp->{$needs_reset['key']} = $needs_reset['value'];
					}
				}

				if ( ! empty( $uploaded['error'] ) ) {
					// Upload error response.
					bp_attachments_json_response( false, $is_html4, array(
						'type'    => 'upload_error',
						'message' => sprintf(
							/* translators: %s: the upload error message */
							__( 'Upload Failed! Error was: %s', 'vibebp' ),
							$uploaded['error']
						),
					) );
				}

				$error_message = __( 'There was a problem uploading the cover image.', 'vibebp' );

				$bp_attachments_uploads_dir = bp_attachments_cover_image_upload_dir();

				// The BP Attachments Uploads Dir is not set, stop.
				if ( ! $bp_attachments_uploads_dir ) {
					bp_attachments_json_response( false, $is_html4, array(
						'type'    => 'upload_error',
						'message' => $error_message,
					) );
				}

				$cover_subdir = $object_data['dir'] . '/' . $bp_params['item_id'] . '/cover-image';
				$cover_dir    = trailingslashit( $bp_attachments_uploads_dir['basedir'] ) . $cover_subdir;
				/*
				if(! is_dir( $cover_dir )){

					mkdir($cover_dir,0755, true);

				}*/
				if ( 1 === validate_file( $cover_dir ) || ! is_dir( $cover_dir ) ) {
					// Upload error response.

					bp_attachments_json_response( false, $is_html4, array(
						'type'    => 'upload_error',
						'message' => $error_message,
					) );

				}

				/*
				 * Generate the cover image so that it fit to feature's dimensions
				 *
				 * Unlike the avatar, uploading and generating the cover image is happening during
				 * the same Ajax request, as we already instantiated the BP_Attachment_Cover_Image
				 * class, let's use it.
				 */
				$cover = bp_attachments_cover_image_generate_file( array(
					'file'            => $uploaded['file'],
					'component'       => $object_data['component'],
					'cover_image_dir' => $cover_dir
				), $cover_image_attachment );
				if ( ! $cover ) {
					bp_attachments_json_response( false, $is_html4, array(
						'type'    => 'upload_error',
						'message' => $error_message,
					) );
				}

				$cover_url = trailingslashit( $bp_attachments_uploads_dir['baseurl'] ) . $cover_subdir . '/' . $cover['cover_basename'];

				// 1 is success.
				$feedback_code = 1;

				// 0 is the size warning.
				if ( $cover['is_too_small'] ) {
					$feedback_code = 0;
				}

				// Set the name of the file.
				$name       = $_FILES['file']['name'];
				$name_parts = pathinfo( $name );
				$name       = trim( substr( $name, 0, - ( 1 + strlen( $name_parts['extension'] ) ) ) );

				// Set some arguments for filters.
				$item_id   = (int) $bp_params['item_id'];
				$component = $object_data['component'];

				/**
				 * Fires if the new cover image was successfully uploaded.
				 *
				 * The dynamic portion of the hook will be members in case of a user's
				 * cover image, groups in case of a group's cover image. For instance:
				 * Use add_action( 'members_cover_image_uploaded' ) to run your specific
				 * code once the user has set his cover image.
				 *
				 * @since 2.4.0
				 * @since 3.0.0 Added $cover_url, $name, $feedback_code arguments.
				 *
				 * @param int    $item_id       Inform about the item id the cover image was set for.
				 * @param string $name          Filename.
				 * @param string $cover_url     URL to the image.
				 * @param int    $feedback_code If value not 1, an error occured.
				 */
				do_action(
					$component . '_cover_image_uploaded',
					$item_id,
					$name,
					$cover_url,
					$feedback_code
				);

				// Handle deprecated xProfile action.
				if ( 'members' === $component ) {
					/** This filter is documented in wp-includes/deprecated.php */
					do_action_deprecated(
						'xprofile_cover_image_uploaded',
						array(
							$item_id,
							$name,
							$cover_url,
							$feedback_code,
						),
						'6.0.0',
						'members_cover_image_deleted'
					);
				}

				// Finally return the cover image url to the UI.
				bp_attachments_json_response( true, $is_html4, array(
					'name'          => $name,
					'url'           => $cover_url,
					'feedback_code' => $feedback_code,
				) );
	    	}


	    	return new WP_REST_Response( $return, 200 ); 
		}
		
		function can_access_member_details(){
			if( (!empty($this->user) && !vibebp_get_setting('create_member','bp','general') && user_can($this->user->id,'manage_options')) || (vibebp_get_setting('create_member','bp','general') && in_array(vibebp_get_setting('create_member','bp'),array_keys($this->user->caps))) ) {
				return true;
			}
			return false;
					
		}

	    function set_avatar($request){
	    	$body = json_decode(stripslashes($_POST['body']),true);
	   		$body = vibebp_recursive_sanitize_array_field($body);
	    	$return = array(
	    		'status'=>1,
	    		'message'=>__('Avatar uploaded','vibebp')
			);
			$user_id = $this->user->id;

	    	if ( !empty( $_FILES )  ) {
				add_filter('bp_attachment_upload_overrides',function($overrides){
					$overrides['test_form'] = FALSE;
					return $overrides;
				});
				$bp = buddypress();
				$bp->displayed_user = $this->user;
				
				if ( ! isset( $bp->avatar_admin ) ) {
					$bp->avatar_admin = new stdClass();
				}

				$avatar = bp_core_avatar_handle_upload($_FILES, 'bp_members_avatar_upload_dir' );
				
				//retun $bp->avatar_admin->image->url;
				//we have to first upload and then crop image
				if ( $avatar ) { 
					//bp_core_avatar_handle_crop( $cropargs );
					$return['original_file'] =  $bp->avatar_admin->image->url;
					
					$bp = buddypress();
					$bp->displayed_user = $this->user;

					if(isset($body['user_id']) && $this->can_access_member_details()){
						$x = new stdClass();
						$x->id = (int)($body['user_id']);
						$bp->displayed_user = $x;
						$user_id = (int)($body['user_id']);
					}

					if ( ! isset( $bp->avatar_admin ) ) {
						$bp->avatar_admin = new stdClass();
					}
					//we have to first upload and then crop image
					$bp->avatar_admin->step = 'crop-image';

					if ( !empty($bp->avatar_admin->image->url) ) {
						$cropargs = array(
							'object'        => 'user',
							'avatar_dir'    => 'avatars',
							'item_id'       => (!empty($body['item_id'])?$body['item_id']:$user_id),
							'original_file' => $bp->avatar_admin->image->url,
							'crop_x'        => $body['cropdata']['x'],
							'crop_y'        => $body['cropdata']['y'],
							'crop_w'        => $body['cropdata']['width'],
							'crop_h'        => $body['cropdata']['height']
						);
						$return['debug'] = $cropargs; 
						vibebp_avatar_handle_crop($cropargs,$user_id);
						$return['avatar'] = bp_core_fetch_avatar(array(
							'item_id' =>(!empty($body['item_id'])?$body['item_id']:$user_id),
							'object'  => (!empty($body['type'])?$body['type']:'user'),
							'type'=>'full',

							'html'    => false
						));
					}else{
						$return['status'] = 0;
					}
				}else{
					$return['status'] = 0;
					$return['message'] = _x('Something went wrong','','vibebp');
				}
			}

			return new WP_REST_Response( $return, 200 ); 
	    }

	    function upload_avatar($request){
	    	$body = json_decode(stripslashes($_POST['body']),true);
	    	$body = vibebp_recursive_sanitize_array_field($body );
	    	$return = array(
	    		'status'=>1,
	    		'message'=>__('Avatar uploaded','vibebp')
			);
			$user_id = $this->user->id;

	    	if ( !empty( $_FILES )  ) {
				add_filter('bp_attachment_upload_overrides',function($overrides){
					$overrides['test_form'] = FALSE;
					return $overrides;
				});
				$bp = buddypress();
				$bp->displayed_user = $this->user;

				if(isset($body['user_id']) && $this->can_access_member_details()){
					$x = new stdClass();
					$x->id = (int)($body['user_id']);
					$bp->displayed_user = $x;
					$user_id = (int)($body['user_id']);
				}

				if ( ! isset( $bp->avatar_admin ) ) {
					$bp->avatar_admin = new stdClass();
				}

				$bp->avatar_admin->ui_available_width = $body['ui_available_width'];
				$avatar = bp_core_avatar_handle_upload($_FILES, 'bp_members_avatar_upload_dir' );
				
				//retun $bp->avatar_admin->image->url;
				//we have to first upload and then crop image
				if ( $avatar ) { 
					//bp_core_avatar_handle_crop( $cropargs );
					$return['original_file'] =  $bp->avatar_admin->image->url;
					$return['ui_available_width'] = $bp->avatar_admin->ui_available_width;

					vibebp_fireabase_update_stale_requests('global','avatar/?id=user_'.$user_id);
				}else{
					$return['status'] = 0;
					$return['message'] = _x('Something went wrong','','vibebp');
				}
			}
			return new WP_REST_Response( $return, 200 ); 
	    }


	    function crop_avatar($request){
	    	$body = json_decode(stripslashes($_POST['body']),true);
	    	$body = vibebp_recursive_sanitize_array_field($body);
	    	$bp = buddypress();
			$bp->displayed_user = $this->user;

			$user_id = $this->user->id;
			if(isset($body['user_id']) && $this->can_access_member_details()){
				$x = new stdClass();
				$x->id = (int)($body['user_id']);
				$bp->displayed_user = $x;
				$user_id = (int)($body['user_id']);
			}

			if ( ! isset( $bp->avatar_admin ) ) {
				$bp->avatar_admin = new stdClass();
			}
			//retun $bp->avatar_admin->image->url;
			//we have to first upload and then crop image
			$bp->avatar_admin->step = 'crop-image';

			if ( !empty($body['original_file']) ) { 
				$cropargs = array(
					'object'        => 'user',
					'avatar_dir'    => 'avatars',
					'item_id'       => (!empty($body['item_id'])?$body['item_id']:$user_id),
					'original_file' => $body['original_file'],
					'crop_x'        => $body['cropdata']['x'],
					'crop_y'        => $body['cropdata']['y'],
					'crop_w'        => $body['cropdata']['width'],
					'crop_h'        => $body['cropdata']['height']
				);
				$return['debug'] = $cropargs; 

				//bp_core_avatar_handle_crop( $cropargs );
				vibebp_avatar_handle_crop($cropargs,$user_id);

				$return['avatar'] = bp_core_fetch_avatar(array(
					'item_id' =>(!empty($body['item_id'])?$body['item_id']:$user_id),
					'object'  => (!empty($body['type'])?$body['type']:'user'),
					'type'=>'full',

					'html'    => false
				));
			}else{
				$return['status'] = 0;
			}
			return new WP_REST_Response( $return, 200 ); 
	    }

		function can_delete_account($request){
	    	if(function_exists('bp_disable_account_deletion')){
				
				return new WP_REST_Response( array('status'=>!bp_disable_account_deletion()), 200 ); 
			}
			return new WP_REST_Response( array('status'=>false), 200 ); 

		}

		function delete_account($request){
	    	$args = json_decode($request->get_body(),true);
			$reassign_user_id=null;
			if(function_exists('bp_disable_account_deletion') && !bp_disable_account_deletion()){
 				$args = array(
					'role'    => 'administrator',
					'orderby' => 'ID',
					'order'   => 'ASC'
				);
				$users = get_users( $args );
				if(!empty($users)){
					$reassign_user_id = apply_filters('vibebp_delete_account_allow_reassign_user',$users[0]->ID,$users);
					
				}
				if(!function_exists('wp_delete_user'))
					require_once( ABSPATH.'wp-admin/includes/user.php' );

				$deleted = wp_delete_user($this->user->id,$reassign_user_id);
				if(empty($deleted) || is_wp_error($deleted)){
					return new WP_REST_Response( array('status'=>false,'message'=>_x('Something went wrong!','','vibebp')), 200 ); 
				}else{
					return new WP_REST_Response( array('status'=>true,'message'=>_x('Account deleted!','','vibebp')), 200 ); 
				}
			}else{
				return new WP_REST_Response( array('status'=>false,'message'=>_x('Account deletion not allowed!','','vibebp')), 200 ); 
			}
		}

		function selectcpt($request){

			$cpt= $request->get_param('cpt');
			$body = json_decode($request->get_body());
			$return = array();
			if($cpt=='assignment'){
				$cpt = 'wplms-assignment';
			}
			$cpt= str_replace('hyphen', '-', $cpt);
			$results = apply_filters('vibebp_selectcpt_field_results',array(),$body->search,$cpt,$request,$this->user);
			if(empty($results) && !empty($body ) && !empty($cpt) && !empty($this->user->id) && is_numeric($this->user->id)){
				
				if($cpt == 'groups'){
					if(function_exists('groups_get_group')){
	
						$args=apply_filters('selectcpt_wplms_groups',array(
						'per_page'=>999,
						'search_terms'=>$body->search,
						'search_columns'=>['name']
						),$this->user,$request);
						
	
						$vgroups =  groups_get_groups($args);
						$return = array();
						foreach($vgroups['groups'] as $vgroup){
							$results[] = array('id'=>$vgroup->id,'text'=>$vgroup->name,
								'link'=>bp_core_get_user_domain($this->user->id).'#component=groups&action=view&id='.$vgroup->id,'permalink'=>bp_get_group_permalink( $vgroup ));
						}
					}
				}else{
					$args = array(
						'post_type'=>$cpt,
						'posts_per_page'=>99,
						's'=>$body->search,
					);
					
	
					$args = apply_filters('vibebp_frontend_cpt_query',$args,$this->user);
					$query = new WP_Query($args);
					
					if($query->have_posts()){
						while($query->have_posts()){
							$query->the_post();
							global $post;
							$preturn = array('id'=>$post->ID,'text'=>$post->post_title,'link'=>get_permalink($post->ID));
							if($cpt == 'unit'){
								$type = get_post_meta($post->ID,'vibe_type',true);
								if(empty($type) || $type == 'unit'){$type = 'general';}
								if($type == 'text-document'){$type = 'general';}
								if($type == 'play'){$type = 'video';}
								if($type == 'music-file-1'){$type = 'audio';}
								if($type == 'podcast'){$type = 'audio';}
	
								$preturn['type']=$type;
							}
							
							if($cpt == 'question'){
								
								$type = get_post_meta($post->ID,'vibe_question_type',true);
								if(empty($type)){$type = 'multiple';}
								$preturn['type']=$type;
							}
	
							if($cpt == 'product'){
								$product = wc_get_product($post->ID);
								$preturn['text'] .= ' - '.$product->get_price_html();
	
								$preturn['fields'] = apply_filters('vibebp_product_fields',array(
									'ID'=>$post->ID,
									'post_title'=>$post->post_title,
									'meta'=>array(
										array('meta_key'=>'_price','meta_value'=>get_post_meta($post->ID,'_price',true)),
										array('meta_key'=>'vibe_subscription','meta_value'=>get_post_meta($post->ID,'vibe_subscription',true)),
										array('meta_key'=>'vibe_duration','meta_value'=>array('value'=>get_post_meta($post->ID,'vibe_duration',true),'parameter'=>get_post_meta($post->ID,'vibe_duration_parameter',true))
										)
									)
								));
							}
							$results[] = $preturn;
						}
					}
					wp_reset_postdata();
				}
			}else{
				$return = array('status'=>false,'message'=>_x('Sorry Something went wrong or invalid post type','','vibebp'));
			}
	
			if(empty($results)){
				return new WP_REST_Response( array('status'=>false,'message'=>_x('Sorry not results found!Try another search keyword!','no results in search api request','vibebp')), 200 );
			}
			return new WP_REST_Response( array('status'=>true,'posts'=>$results), 200 );
		}

		function get_taxonomy($request){
			$post = json_decode(file_get_contents('php://input'));
			$body = $request->get_body();
			$body = json_decode($body);
			$return = array();
			$taxonomy=$body->taxonomy;
			$posts = array();
	
			if(!empty($body ) && !empty($taxonomy) && !empty($this->user->id) && is_numeric($this->user->id)){
				$terms = get_terms( $taxonomy, array('hide_empty' => false,'orderby'=>'name','order'=>'ASC') );
				if(!empty($terms) && is_array($terms)){
					foreach ($terms as $key=>$term ){
						
						$posts[] = array('id'=>$term->term_id,'text'=>$this->get_taxonomy_name('',$term,$terms));
						  
						
					}
				}
				wp_reset_postdata();
			}else{
				$return = array('status'=>false,'message'=>_x('Sorry Something went wrong or invalid post type','','vibebp'));
			}
	
			if(empty($posts)){
				return new WP_REST_Response( array('status'=>false,'message'=>_x('Sorry no results found!Try another search keyword!','API request','vibebp')), 200 );
			}
			return new WP_REST_Response( array('status'=>true,'posts'=>$posts), 200 );
		}

		function create_element($request){
			$id = 0;
		 
			$post = json_decode(file_get_contents('php://input'));
			$body = json_decode($request->get_body(),true);
			$cpt= $request->get_param('cpt');
			if($cpt=='assignment'){
				$cpt = 'wplms-assignment';
			}
	
	
			$return = array();
			$return = array('status'=>false,'message'=>__('Not saved.','vibebp'));
	
			if(!in_array($cpt,apply_filters('wplms_create_element_cpts',array('unit','quiz','wplms-assignment','course','question','product','certificate')))){
				//PAge editing not allowed
	
				return new WP_REST_Response( $return, 200 ); 
			}
	
			do_action('wplms_create_course_create_element');
			
			$admin_approval = 0;
	
			if(function_exists('vibe_get_option') && vibe_get_option('new_course_status')=='pending' && $cpt 
				!= 'product' && $cpt !='question'){
				$admin_approval = 1;
			}
			$manage_options = user_can($this->user->id,'manage_options');
			
	
			if(empty($body['id'] )){
				$check_can_create = apply_filters('vibebp_user_can_create_element',false,$cpt,$this->user->id ,$body);
				if($check_can_create){
					$return = array('status'=>false,'message'=>$check_can_create);
					return new WP_REST_Response( $return, 200 ); 
				}
				$args = apply_filters('vibebp_front_end_create_curriculum',array(
					'post_type' => $cpt,
					'post_title' => sanitize_textarea_field($body['post_title']),
					'post_content' => (!empty($body['post_content'])?wp_slash($body['post_content']):sanitize_textarea_field($body['post_title'])),
					'post_status'=>'publish',
					'post_author'=>$this->user->id
				));
				if($admin_approval  && !$manage_options){
					$args['post_status']='pending';
					unset($args['post_content']); 
				}else{
					$args['post_status']='publish';
				}
				remove_filter('content_save_pre', 'wp_filter_post_kses');
				remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
				$id = wp_insert_post($args);
				if(is_wp_error($id)){
					return new WP_REST_Response( array('status'=>0,'message'=>$id->get_error_message()), 200 );
				}
	
				add_filter('content_save_pre', 'wp_filter_post_kses');
				add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
	
			}else{
			   
				$id =$body['id'];
				$can_edit = apply_filters('wplms_can_edit',true,$id,$this->user);
				if(empty($can_edit)){
					return new WP_REST_Response( array('status'=>false,'message'=>__('Can not make changes.','vibebp')), 200 );
				}
				if(!empty($body['post_title']) || !empty($body['post_content'])){
					$args = apply_filters('vibebp_front_end_create_curriculum',array(
						'ID'=>$body['id'],
						'post_type' => $cpt,
						'post_title' => sanitize_textarea_field($body['post_title']),
						'post_content' => !empty($body['post_content'])?wp_slash($body['post_content']):sanitize_textarea_field($body['post_title']),
						'post_status'=>'publish',
						'post_author'=>$this->user->id
					));
					$status = get_post_status($id);
					if($status=='pending'){
						if($admin_approval  && !$manage_options){
							$args['post_status']='pending';
							unset($args['post_content']); 
	
						}else{
							$args['post_status']='publish';
						}
					}else{
						if($status=='publish'){
							$admin_approval=0;
						}
					}
	
					remove_filter('content_save_pre', 'wp_filter_post_kses');
					remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
					$id = wp_update_post($args);
					add_filter('content_save_pre', 'wp_filter_post_kses');
					add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
				}
			}
			
	
	
			if($admin_approval  && !$manage_options){
				update_post_meta($id,'vibe_draft',$body['editfields']);
	
				$return = array('status'=>true,'data'=>array('id'=>$id,'text'=>$body['post_title'],'type'=>$body['type']),'message'=>__('Successfully saved !Pending for approval!','vibebp'));
	
				
				return new WP_REST_Response( apply_filters('wplms_create_cpt_return',$return,$body,$cpt), 200 );
			}
	
			if(!empty($id)){
	
				$return = array(
					'status'=>true,
					'data'=>array(
						'id'=>$id,
						'text'=>(empty($body['post_title'])?'':$body['post_title']),
					),
					'message'=>__('Successfully saved !','vibebp')
				);
				//handle product:
				
	
				if(!empty($body['raw'])){
					update_post_meta($id,'raw',wp_slash($body['raw']));
				}
				if(!empty($body['meta']) && count($body['meta'])){
					foreach ($body['meta'] as  $meta) {
						if(isset($meta['meta_value'])){
							if($meta['meta_key'] == 'vibe_type' && $cpt == 'wplms-assignment'){
								$meta['meta_key']='vibe_assignment_submission_type';
								
							}
	
	
							if($meta['meta_key'] == 'vibe_quiz_tags'){
								if(!empty($meta['meta_value'])){
									$_val = [];
									foreach ($meta['meta_value'] as $key => $value) {
										if(!empty($value['count'])){
											$_val['tags'][$key] = $value['tagfield']['value'];
											$_val['numbers'][$key] = $value['count'];
											$_val['marks'][$key] = $value['marks'];
										}
										
									}
									$meta['meta_value'] = $_val;
								}
							}
	
							if($meta['meta_key'] == 'vibe_practice_questions'){
								if(!empty($meta['meta_value'])){
									if(is_array($meta['meta_value']) && !empty($meta['meta_value']['type'])){
										if($meta['meta_value']['type']=='tags'){
											$_val = [];
											foreach ($meta['meta_value']['value'] as $key => $value) {
												if(!empty($value['count'])){
													$_val['tags'][$key] = $value['tagfield']['value'];
													$_val['numbers'][$key] = $value['count'];
												}
											}
											
										}
										elseif($meta['meta_value']['type']=='questions'){
											$_val = [];
											foreach ($meta['meta_value']['value'] as $key => $value) {
												$_val[] = $value['data']['id'];
											}
										}
										$meta['meta_value'] = array('type'=>$meta['meta_value']['type'],'value'=>$_val);
									}
								}
							}
							
							if($meta['meta_key'] == 'vibe_duration_parameter'){
								$_cpt = $cpt;
								if($_cpt=='wplms-assignment'){
									$_cpt =='assignment';
								}
								$meta['meta_key']='vibe_'.$_cpt.'_duration_parameter';
							}
	
							update_post_meta($id,$meta['meta_key'],wp_slash($meta['meta_value']));
	
	
							if($meta['meta_key'] =='vibe_product_duration'){
								update_post_meta($id,'vibe_duration',$meta['meta_value']['value']);
								
								update_post_meta($id,'vibe_product_duration_parameter',$meta['meta_value']['parameter']);
							}
	
						}else{
							delete_post_meta($id,$meta['meta_key']);
						}
						if(in_Array($cpt,array('unit','quiz','wplms-assignment','question'))){
	
							if($cpt == 'unit' && $meta['meta_key'] == 'vibe_type'){
							   $return['data']['type']=$meta['meta_value']; 
							}
							if($cpt == 'quiz' && $meta['meta_key'] == 'vibe_type'){
								$return['data']['type']=$meta['meta_value']; 
							}
							if($cpt == 'wplms-assignment' && $meta['meta_key'] == 'vibe_assignment_submission_type'){
								if($meta['meta_value'] == 'upload'){
									$return['data']['type']='upload';
								}else{
									$return['data']['type']='textarea';
								}
							}
	
							if($cpt == 'question' && $meta['meta_key'] == 'vibe_question_type'){
								$return['data']['type']=$meta['meta_value'];
							}
						}
					}
				}
				if(!empty($body['taxonomy']) && count($body['taxonomy'])){
					$_cat_ids = array();
					foreach ($body['taxonomy'] as  $taxonomy) {
						if(!empty($taxonomy['value'])){
							foreach($taxonomy['value'] as $k=>$cat_id){
								if(!is_numeric($cat_id) && strpos($cat_id, 'new_') === 0){
									$new_cat = explode('new_',$cat_id);
									$cid = wp_insert_term(sanitize_textarea_field($new_cat[1]),$taxonomy['taxonomy']);
									if(is_array($cid)){
										$taxonomy['value'][$k] = $cid['term_id'];
									}else{
										unset($taxonomy['value'][$k]);
									}
								}
							}
							wp_set_object_terms( $id, $taxonomy['value'], $taxonomy['taxonomy'] );
						}
					}
				}
	
				if(function_exists('wc_get_product') && $cpt == 'product'){
					wp_set_object_terms($id, 'simple', 'product_type');
					update_post_meta($id,'vibe_wplms',1);
					$product = wc_get_product($id);
					if(!empty( $product)){
						
						$sale_price = $product->get_sale_price();
					  
	
						$regular_price = $product->get_regular_price();
	
						if(empty($regular_price)){
							$price = $product->get_price();
							if(empty($price)){
								$price = 0;
							}
							
						}  
						if(!empty($sale_price)){
							update_post_meta($id,'_price',$sale_price);
							$product->set_price($sale_price);//to show correct value in get_price_html
							
						}else{
							if(isset($regular_price)){
								update_post_meta($id,'_price',$regular_price);
								$product->set_price($regular_price);
								
							}else{
								update_post_meta($id,'_price',$price);
								$product->set_price($price);
								
							}
						}
						if(empty($return['data']['text'])){
							$return['data']['text'] = $product->get_title();
						}
						$return['data']['text'] .= ' - '.$product->get_price_html();
	
	
	
	
					}else{
						$return = array('status'=>false,'message' => _x('Some error occured','',''));
					}
					
				}
			}
	
			$return = apply_filters('vibebp_create_cpt_return',$return,$body,$cpt);
			return new WP_REST_Response( $return, 200 );
		}

	}
}

VIBE_BP_API_Rest_Settings_Controller::init();



function vibebp_avatar_handle_crop($args,$user_id){  

	$args['item_id'] = (int) $args['item_id'];

	$relative_path = sprintf( '/%s/%s/%s', $args['avatar_dir'], $args['item_id'], basename( $args['original_file'] ) );

	$upload_path = bp_core_avatar_upload_path();
	$url         = bp_core_avatar_url();
	$upload_dir  = bp_upload_dir();

	$absolute_path = $upload_path . $relative_path;

	

	// Bail if the avatar is not available.
	// if ( ! file_exists( $absolute_path ) )  {
	// 	return false;
	// }
	


	/** This filter is documented in bp-core/bp-core-avatars.php */
	$avatar_folder_dir = apply_filters( 'bp_core_avatar_folder_dir', $upload_path . '/' . $args['avatar_dir'] . '/' . $args['item_id'], $args['item_id'], $args['object'], $args['avatar_dir'] );
	
	
	
	// Bail if the avatar folder is missing for this item_id.
	// if ( ! file_exists( $avatar_folder_dir ) ) {
	// 	return false;
	// }
	
	// Delete the existing avatar files for the object.
	$existing_avatar = bp_core_fetch_avatar( array(
		'object'  => $args['object'],
		'item_id' => $args['item_id'],
		'html' => false,
	) );
	
	/**
	 * Check that the new avatar doesn't have the same name as the
	 * old one before deleting
	 */
	if ( ! empty( $existing_avatar ) && $existing_avatar !== $url . $relative_path ) {
		bp_core_delete_existing_avatar( array( 'object' => $args['object'], 'item_id' => $args['item_id'], 'avatar_path' => $avatar_folder_dir ) );
	}
	
	// Make sure we at least have minimal data for cropping.
	if ( empty( $args['crop_w'] ) ) {
		$args['crop_w'] = bp_core_avatar_full_width();
	}

	if ( empty( $args['crop_h'] ) ) {
		$args['crop_h'] = bp_core_avatar_full_height();
	}

	// Get the file extension.
	$data = @getimagesize( $absolute_path );
	$ext  = $data['mime'] == 'image/png' ? 'png' : 'jpg';

	$args['original_file'] = $absolute_path;
	$args['src_abs']       = false;
	$avatar_types = array( 'full' => '', 'thumb' => '' );
	
	foreach ( $avatar_types as $key_type => $type ) {
		if ( 'thumb' === $key_type ) {
			$args['dst_w'] = bp_core_avatar_thumb_width();
			$args['dst_h'] = bp_core_avatar_thumb_height();
		} else {
			$args['dst_w'] = bp_core_avatar_full_width();
			$args['dst_h'] = bp_core_avatar_full_height();
		}
		
		$filename         = wp_unique_filename( $avatar_folder_dir, uniqid() . "-bp{$key_type}.{$ext}" );

		$args['dst_file'] = $avatar_folder_dir . '/' . $filename;

		

		if ( ! function_exists( 'wp_crop_image' ) ) {
		  include( ABSPATH . 'wp-admin/includes/image.php' );
		}

		$avatar_types[ $key_type ] = wp_crop_image( $args['original_file'], (int) $args['crop_x'], (int) $args['crop_y'], (int) $args['crop_w'], (int) $args['crop_h'], (int) $args['dst_w'], (int) $args['dst_h'], $args['src_abs'], $args['dst_file'] );

	}

	// Remove the original.
	@unlink( $absolute_path );

	return $avatar_types;
}

