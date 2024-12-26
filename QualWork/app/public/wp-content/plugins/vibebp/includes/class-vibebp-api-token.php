<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'VibeBP_Token' ) ) {

	class VibeBP_Token extends WP_REST_Controller{
		/**
	     * The namespace to add to the api calls.
	     *
	     * @var string The namespace to add to the api call
	     */

		var $settings;
		var $temp;

		public static $instance;
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new VibeBP_Token();
	        return self::$instance;
	    }

		public function __construct(){
			$this->namespace = VIBEBP_NAMESPACE;
			$this->type = VIBEBP_TOKEN;
			$this->register_routes(); 	// Register Routes

			
		}

		public function register_routes(){
			register_rest_route( $this->namespace, '/'. $this->type .'/generate-token/', array(
				'methods'                   =>   'POST',
				'callback'                  =>  array( $this, 'generate_token' ),
				'permission_callback' => array( $this, 'get_user_permissions_check' ),
			) );
			register_rest_route( $this->namespace, '/'. $this->type .'/regenerate-token/', array(
				'methods'                   =>   'POST',
				'callback'                  =>  array( $this, 'regenerate_token' ),
				'permission_callback' => array( $this, 'get_user_permissions_check' ),
			) );
			
			register_rest_route( $this->namespace, '/'. $this->type .'/validate-token/', array(
				'methods'                   =>   'POST',
				'callback'                  =>  array( $this, 'validate_token' ),
				'permission_callback' => array( $this, 'get_user_permissions_check' ),
			) );
			register_rest_route( $this->namespace, '/'. $this->type .'/remove-token/', array(
				'methods'                   =>   'POST',
				'callback'                  =>  array( $this, 'remove_token' ),
				'permission_callback' => array( $this, 'get_user_permissions_check' ),
			) );
			

			
		}

		public function get_user_permissions_check($request){
			
			$security = $request->get_param('security');
			
			if($security == vibebp_get_api_security()){
				return true;	
			}
			$security = $request->get_param('client_id');
			if($security == vibebp_get_setting('client_id')){
				return true;	
			}
			return false;
		}

		function verify_force_logout($request){
			$security = $request->get_param('client_id');
			if($security == vibebp_get_setting('client_id')){
				return true;	
			}
			return false;
		}

		function regenerate_token($request){

			$post = json_decode($request->get_body(),true);
			if(empty($post['token'])){
				return new WP_REST_Response(array(
	          		'status'=>0,
                	'code'=>'vibebp_jwt_token_missing',
	              	'message'=>_x('Token missing','JWT authentication error','vibebp'),
	              )
            	);
			}

			$token = sanitize_text_field($post['token']);
	        
        	$data = array('status'=>false);

        	$data = vibebp_expand_token($token);
            
            if(!empty($data['status'])){
            	$expanded_token = $data['data'];
            }

            if(!empty($expanded_token)){

	            $tokens = vibebp_get_user_active_tokens($expanded_token->data->user->id);

	            if(empty($tokens)){
            		$tokens = [];
            	}
            	if(in_array($expanded_token->vibebp_token_key, array_keys($tokens))){

            		$user = get_user_by('id',$expanded_token->data->user->id);
        			vibebp_delete_user_active_tokens($expanded_token->data->user->id,$expanded_token->vibebp_token_key,$tokens);
        			$newtoken = vibebp_generate_token($user);
        			$issuedAt = time();
				    $notBefore = apply_filters( VIBEBP.'_token_expire_not_before', $issuedAt, $issuedAt);

				    $duration = vibebp_get_setting('token_duration');
				    if(empty($duration)){
				    	$duration = DAY_IN_SECONDS * 7;
				    }
				    $expire = apply_filters( VIBEBP.'_token_expire', $issuedAt  + $duration, $issuedAt);
			        $data = array(
			        	'status' => 1,
			            'token' => $newtoken,
			            'message'=>_x('Token generated','Token generated','vibebp'),
			            'expires'=> $expire
			        );
			        $data = apply_filters(VIBEBP.'jwt_auth_token_validate_before_dispatch', $data);
			        return new WP_REST_Response($data);
            	}else{
            		$data = array(
		        	'status' => 0,
		            'error' => 'jwt_auth_invalid_token_missing_from_active_tokens',
			        );
			        return $data;
            	}
	        }
	        
	        return new WP_REST_Response($data,200);
		}

		function generate_token($request){

			$secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false; 
			//Tougher Security
			$secret_key = apply_filters('vibebp_tougher_security',$secret_key);

			$post = json_decode($request->get_body(),true);

	        $username = sanitize_text_field($post['email']);
	        $password = $post['password'];
	        /** First thing, check the secret key if not exist return a error*/
	        if (!$secret_key) {
	          	return new WP_REST_Response(array(
	          		'status'=>0,
                	'code'=>'vibebp_jwt_security_missing',
	              	'message'=>_x('Secret key missing','JWT authentication error','vibebp'),
	              )
            	);
	        }
	        remove_all_actions( 'wp_login_failed' );
	        
        	$user = wp_authenticate($username, $password);	

	        /** If the authentication fails return a error*/
	        if (is_wp_error($user) || !$user) {
	        	$message = _x('Email or Password not valid','WP authentication error','vibebp');
	        	if(!empty($user->errors)){
	        		$errors = array_values($user->errors);
	        		$errors_keys = array_keys($user->errors);

	        		if(!empty($errors)){
	        			if(!in_array('incorrect_password', $errors_keys)){
	        				if(is_array($errors[0])){
		        				$message = $errors[0][0];
		        			}else{
		        				$message = $errors[0];
		        			}
		        		}
	        		}
	        	}
	          	return new WP_REST_Response(array(
	          		'status'=>0,
	          		'code'=>'vibebp_jwt_invalid_cred',
	          		'message'=>$message,
	          		)
            	);
	        }
	        $token = vibebp_generate_token($user);
	        $data = array(
	        	'status' => 1,
	            'token' => $token,
	            'message'=>_x('Token generated','Token generated','vibebp')
	        );
	        /** Let the user modify the data before send it back */
	        return new WP_REST_Response(apply_filters(VIBEBP.'jwt_auth_token_before_dispatch', $data, $user));
		}

		function validate_token($request){
			
			/*
	         * Looking for the HTTP_AUTHORIZATION header, if not present just
	         * return the user.
	         */
			$headers = $request->get_headers();
	        $token = sanitize_text_field($request->get_body());
	       
	        if (!$token) {
	        	$data = array(
		        	'status' => 0,
		            'data' => 'vibebp_jwt_auth_token_missing',
		            'message'=>_x('Authorization token missing','Authorization Token Missing','vibebp')
		        );
		        return $data;
	        }
	        /** Get the Secret Key */
	        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
	        //Tougher Security
			$secret_key = apply_filters('vibebp_tougher_security',$secret_key);

	        if (!$secret_key) {
	            $data = array(
		        	'status' => 0,
		            'data' => 'vibebp_jwt_secret_key_missing',
		            'message'=>_x('Secret key missing','Secret key missing','vibebp')
		        );
		        return $data;
	        } 

	        /** Try to decode the token */ /** Else return exception*/
	        try {
	            $data = vibebp_expand_token($token);

	            if(!empty($data['status'])){

	            	$expanded_token = $data['data'];
	            	$tokens = vibebp_get_user_active_tokens($expanded_token->data->user->id);
	            	if(empty($tokens)){
	            		$tokens = [];
	            	}


	            	if(in_array($expanded_token->vibebp_token_key, array_keys($tokens))){

	            		$data = array(
				        	'status' => 1, 
				            'data' => $expanded_token,
				            'message'=>_x('Valid Token','Valid Token','vibebp'),
				            'redirect_component' => apply_filters('vibebp_login_redirect_component',false,$expanded_token)
				        );
				        
				        return apply_filters(VIBEBP.'jwt_auth_token_validate_before_dispatch', $data);
	            	}else{
			        	$data = array(
				        	'status' => 0,
				            'error' => 'jwt_auth_invalid_token_missing_from_active_tokens',
				            'message'=>'jwt_auth_invalid_token_missing_from_active_tokens',
				        );
				        return $data;
			        }
		            
		        }else{
			        return $data;
		        }
		        

	        }catch (Exception $e) {
	            $data = array(
		        	'status' => 0,
		            'data' => 'jwt_auth_invalid_token',
		            'message'=>$e->getMessage()
		        );
		        return $data;
	        }
	        
		}

		function remove_token($request){
			$data = array('status'=>false);
        	$secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false; 
			//Tougher Security
			$secret_key = apply_filters('vibebp_tougher_security',$secret_key);
			$post = json_decode($request->get_body(),true);
			if(!empty($post['token'])){
				$token = sanitize_text_field($post['token']);
		        try {
		            $expanded_token = JWT::decode($token, $secret_key, array('HS256'));

		            $expanded_token = apply_filters('vibebp_validate_token',$expanded_token,$token);

		            if($expanded_token){
			            $tokens = vibebp_get_user_active_tokens($expanded_token->data->user->id);
			            if(empty($tokens)){
		            		$tokens = [];
		            	}	
		        		vibebp_delete_user_active_tokens($expanded_token->data->user->id,$expanded_token->vibebp_token_key,$tokens);
				        $data = array(
				        	'status' => 1,
				            'message'=>_x('Token deleted','Token deleted','vibebp'),
				        );
				        $data = apply_filters(VIBEBP.'jwt_auth_token_validate_before_dispatch', $data);
				        return new WP_REST_Response($data);
			            
			        }else{
			        	$data = array(
			        	'status' => 0,
			            'data' => 'jwt_auth_invalid_token',

				        );
				        return $data;
			        }
			        

		        }catch (Exception $e) {
		            $data = array(
			        	'status' => 0,
			            'data' => 'jwt_auth_invalid_token',
			            'message'=>$e->getMessage()
			        );
			        return new WP_REST_Response($data,200);
		        }
	    	}else{
	        	$data = array(
	        	'status' => 0,
	            'data' => 'jwt_auth_invalid_token',

		        );
		        return new WP_REST_Response($data,200);
	        }

		}
		
	}

}



VibeBP_Token::init();


