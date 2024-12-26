<?php

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'VIBE_BP_API_Rest_Messages_Controller' ) ) {
	
	class VIBE_BP_API_Rest_Messages_Controller extends WP_REST_Controller{
		
		public static $instance;
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new VIBE_BP_API_Rest_Messages_Controller();
	        return self::$instance;
	    }
	    public function __construct( ) {
			$this->namespace = Vibe_BP_API_NAMESPACE;
			$this->type= Vibe_BP_API_MESSAGES_TYPE;
			$this->register_routes();
		}

		public function register_routes() {
			register_rest_route( $this->namespace, '/' .$this->type. '/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_message' ),
					'permission_callback' => array( $this, 'get_messages_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type. '/labels', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_message_labels' ),
					'permission_callback' => array( $this, 'get_messages_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type. '/label/add', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'add_message_label' ),
					'permission_callback' => array( $this, 'get_messages_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type. '/label/remove', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'remove_message_label' ),
					'permission_callback' => array( $this, 'get_messages_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/thread_id/(?P<thread_id>\d+)', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_get_thread_message'),
					'permission_callback' => array( $this, 'get_messages_permissions' ),
					'args'                     	=>  array(
						'thread_id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));


			register_rest_route( $this->namespace, '/'.$this->type .'/send/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_messages_new_message'),
					'permission_callback' => array( $this, 'get_messages_upload_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/'.$this->type .'/delete/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_messages_delete_thread'),
					'permission_callback' => array( $this, 'get_messages_permissions' ),
				),
			));


			register_rest_route( $this->namespace, '/'.$this->type .'/actions/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'vibe_bp_api_message_actions'),
					'permission_callback' => array( $this, 'get_messages_permissions' ),
				),
			));

		}


		/*
	    PERMISSIONS
	     */

	    function get_messages_upload_permissions($request){
	    	$body = json_decode(stripslashes($_POST['body']),true);
 			$body = vibebp_recursive_sanitize_array_field($body);
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
	            
	            return false;
	        }
	    	

	    	return false;

	    	
	    }

	    function get_messages_permissions($request){
	    	$body = json_decode($request->get_body(),true);
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
	            
	            return false;
	        }
	    	

	    	return false;
	    }

	    function get_message($request){

	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);
	    	$args = vibebp_recursive_sanitize_text_field($args );
	    	$message_args = array(
	    		'user_id'      => $this->user->id,
				'page'         => (int)$args['page'],
				'per_page'		=> 10,
				'search_terms' => $args['search'],
				'meta_query'   => array(),
	    	);

	    	if($args['filter'] === 'inbox')
	    		$message_args['box'] = 'inbox';

    		if($args['filter'] === 'sent' || $args['filter'] === 'sentbox'){
	    		$message_args['box'] = 'sentbox';
	    	}
	    	
	    	if($args['filter'] == 'notices'){
	    		$message_args['box'] = 'notices';
	    	}

	    	if($args['filter'] === 'starred'){
	    		$message_args['box'] = 'starred';
	    		$message_args['meta_query'] = array( array(
					'key'   => 'starred_by_user',
					'value' => $this->user->id
				) );
	    	} 

	    	if(!empty($args['label'])){
	    		if(empty($message_args['meta_query'])){$message_args['meta_query'] = array();}
	    		$label_key = 'vibebp_label_'.$this->user->id;

	    		$message_args['meta_query'][] = array(
	    			'key'=>$label_key,
	    			'value'=>$args['label']
	    		);
	    	}
	    	if($message_args['box']==='notices'){
	    		$notices = BP_Messages_Notice::get_notices( array(
					'pag_num'  => $message_args['per_page'],
					'pag_page' => ($message_args['page']),
				) );
				$run= [];
				$run['threads'] = $notices;
	    	}else{
	    		$run = BP_Messages_Thread::get_current_threads_for_user($message_args);
	    	}
	    	if(!empty($run) && $message_args['box']!=='notices'){
	    		foreach($run['threads'] as $key => $message){

	    			
	    			$run['threads'][$key]->labels = bp_messages_get_meta( $message->thread_id, 'vibebp_label_'.$this->user->id,false);
	    		}
	    	}


	    	if(!empty($run['threads']) && (empty($message_args['box']) || $message_args['box']!=='notices')){
	    		foreach($run['threads'] as $key=>$thread){

	    			$user_names = $userlinks = [];
	    			if(!empty($thread->recipients)){
	    				foreach($thread->recipients as $userid => $data){
	    					$user_names[] = $userid;
	    				}
	    			}
	    			if(!empty($thread->sender_ids)){
	    				foreach($thread->sender_ids as $userid => $data){
	    					if(!in_array($userid, $user_names))
	    					$user_names[] = $userid;
	    				}
	    			}
	    			if(!empty($thread->messages)){
	    				foreach($thread->messages as $k=>$message){
		    				if(bp_messages_is_message_starred( $message->id, $this->user->id)){
			    				$run['threads'][$key]->star = 1;
			    			}else{
			    				$run['threads'][$key]->star = 0;
			    			}

		    				$all_meta = bp_messages_get_meta( $message->id,'',false );
		    				$run['threads'][$key]->messages[$k]->meta=$all_meta;
		    			}
	    			}
	    			
	    		}
	    	}

    		if( $run){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Messages Found','Messages Found','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Messages Not Found','Messages Not Found','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_get_message', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 ); 

	    }

	    function get_message_labels($request){

	    	$labels = get_user_meta($this->user->id,'vibebp_message_labels',true);
	    	if(!empty($labels)){
	    		$labels_array = wp_list_pluck( $labels, 'slug' );
	    		$labels_array = "'".implode("','",$labels_array)."'";
    			global $wpdb,$bp;

    			$label_key = 'vibebp_label_'.$this->user->id;
    			$labels_count = $wpdb->get_results("SELECT meta_value as slug, count(*) as count FROM {$bp->messages->table_name_meta} WHERE meta_key = '$label_key' AND meta_value IN ($labels_array) GROUP BY meta_value");

    			foreach($labels as $label){
					$label['count'] = 0;
				}

    			if(!empty($labels_count)){

    				foreach($labels_count as $label_count){
    					foreach($labels as $k=>$label){
    						if($label_count->slug == $label['slug']){
    							$labels[$k]['count'] =$label_count->count;
    						}
    					}
    				}
    			}
	    		
	    	}
	    	return new WP_REST_Response( array('status'=>1,'labels'=>$labels), 200 ); 
	    }


	    function add_message_label($request){

	    	$body = json_decode($request->get_body(),true);
	    	$body = vibebp_recursive_sanitize_text_field($body);
	    	$label = array(
	    		'name' =>$body['text'],
	    		'slug' =>  sanitize_title_with_dashes($body['text']),
	    		'color' => $body['color'],
	    		'count' => 0
	    	);
	    	$labels = get_user_meta($this->user->id,'vibebp_message_labels',true);
	  		if(empty($labels)){$labels = array();}else{
	  			foreach($labels as $l){
	  				if($label['slug'] === $l['slug']){
	  					return new WP_REST_Response( array('status'=>0,'message'=>__('Label exists.','vibebp')), 200 ); 
	  				}
	  			}
	  		}

	  		$labels[]=$label;
	  		
	  		update_user_meta($this->user->id,'vibebp_message_labels',$labels);
	  		vibebp_fireabase_update_stale_requests($this->user->id,'messages/labels');

	  		//
	    	return new WP_REST_Response( array('status'=>1,'labels'=>$labels,'message'=>_x('Label added.','message','vibebp')), 200 ); 
	    }

	    function remove_message_label($request){
	    	$body = json_decode($request->get_body(),true);
	    	$body = vibebp_recursive_sanitize_text_field($body);
	    	$labels = get_user_meta($this->user->id,'vibebp_message_labels',true);
	  		if(!empty($labels)){
	  			$remove = 0;
	  			foreach($labels as $k=>$l){
	  				if($l['slug'] === $body['slug']){
	  					 $remove = $k;
	  					 break;
	  				}
	  			}
	  			$label_key = 'vibebp_label_'.$this->user->id;
	  			$slug = $body['slug'];
	  			global $wpdb,$bp;
	  			
	  			$labels_count = $wpdb->get_results($wpdb->prepare("DELETE FROM {$bp->messages->table_name_meta} WHERE meta_key = %s AND meta_value = %s",$label_key,$labels[$remove]));

	  			unset($labels[$remove]);
	  			update_user_meta($this->user->id,'vibebp_message_labels',$labels);
	  		}

	  		return new WP_REST_Response( array('status'=>1,'labels'=>$labels,'message'=>_x('Label removed.','message','vibebp')), 200 ); 
	    }

	   	function vibe_bp_api_get_thread_message($request){

	   		$thread_id = (int)$request->get_param('thread_id');
	   		$run = BP_Messages_Thread::get_messages($thread_id);
    		if( !empty($run) ){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Messages Found','Messages Found','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Messages Not Found','Messages Not Found','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_get_thread_message', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 );  
	    }

	    function get_messages_new_message_permissions($request){
	    	$body = json_decode(stripslashes($_POST['body']),true);
	    	$body['token']  = sanitize_text_field($body['token']);
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

	    function vibe_bp_api_messages_new_message($request){
 			
 			//$body = json_decode($request->get_body(),true);
 			$body = json_decode(stripslashes($_POST['body']),true);
 			//$body = vibebp_recursive_sanitize_array_field($body);
	    	$args = $body['args'];
	    	

	    	$message_args = array(
		    	'sender_id'  => $this->user->id,
				'recipients' => $args['recipients'], // Can be an array of usernames, user_ids or mixed.
				'subject'    => $args['subject'],
				'content'    => $args['content'],
			);

			if(!empty($args['thread_id'])){
				$message_args['thread_id'] = (int)$args['thread_id'];
			}

			if(!empty($args['notice'])){
				if(user_can($this->user->id,'bp_moderate')){
					$notice            = new BP_Messages_Notice;
					$notice->subject   = $args['subject'];
					$notice->message   = $args['content'];
					$notice->date_sent = bp_core_current_time();
					$notice->is_active = 1;
					$run = $notice->save(); // Send it.

					do_action_ref_array( 'messages_send_notice', array( $args['subject'],$args['content'] ) );

					//$thread_id = messages_send_notice(,);
				}else{
					return new WP_REST_Response( array('status'=>0,'message'=>__('Not enough permission to send notice !','vibebp')), 200 );
				}
			}else{
				remove_filter( 'messages_message_content_before_save', 'bp_messages_filter_kses', 1 );

				$thread_id = messages_new_message($message_args);
				
				add_filter( 'messages_message_content_before_save', 'bp_messages_filter_kses', 1 );
			}

			global $wpdb;
			$bp = buddypress();

			if(!empty($_FILES) && !empty($args['meta'])){
				if ( ! function_exists( 'wp_handle_upload' ) ) {
				    require_once( ABSPATH . 'wp-admin/includes/file.php' );
				}
				 
				
				$upload_overrides = array(
				    'test_form' => false
				);
				foreach($args['meta'] as $key=>$meta){
					
					$uploadedfiles = $_FILES['files_'.$key]; 
					
					$movefile = wp_handle_upload( $uploadedfiles, $upload_overrides );
					
					if ( $movefile && ! isset( $movefile['error'] ) ) {
						$args['meta'][$key]['value'] = $movefile['url'];
						do_action('vibebp_upload_attachment',$movefile['url'],$this->user->id);
					}
				}
			}
			if(!empty($thread_id)){
				$message_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$bp->messages->table_name_messages} WHERE thread_id = %d ORDER BY id DESC", $thread_id ) );
			}
			
			if(isset($args['raw']) && !empty($message_id)){
				bp_messages_update_meta($message_id,'raw',$args['raw']);
			}
			
			if(!empty($args['meta']) && is_numeric($message_id)){
				
				foreach($args['meta'] as $meta){
					bp_messages_add_meta( $message_id, $meta['key'], $meta['value'], false  );
				}
			}

    		
    		if(!empty($args['thread_id'])){
    			if(!empty($thread_id)){
	    			$run = BP_Messages_Thread::get_messages($thread_id);
	    		}
	    	}else{
	    		if(!empty($thread_id)){
	    			$message = BP_Messages_Thread::get_messages($thread_id);
		    		BP_Messages_Thread::get_current_threads_for_user($message_args);
		    		$run = BP_Messages_Thread::get_messages($message[0]->thread_id);
	    		}
	    		

	    	}

	    	if(!empty($run) && is_array($run)){
	    		foreach($run as $key=>$message){
    				$all_meta = bp_messages_get_meta( $message->id,'',false );
    				$run[$key]->meta=$all_meta;
	    		}
	    		if(!empty($args['recipients'])){
	    			foreach($args['recipients'] as $recipient_id){
	    				vibebp_fireabase_update_stale_requests($recipient_id,'/messages');
    				}
    				//vibebp_fireabase_update_stale_requests($this->user->id,'/messages');
    				
	    		}
	    	}

    		if( !empty($run )){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Message Send','Message Send','vibebp')
	    		);

	    		
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => empty($run)?0:$run,
	    			'thread_id'=>$thread_id,
	    			'args'=>$message_args,
	    			'message' => _x('Message Not Send','Message Not Send','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_messages_new_message', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 );  
	    }

	    function vibe_bp_api_messages_delete_thread($request){

	    	$args = json_decode(file_get_contents('php://input'));
	    	$args_arr = json_decode(json_encode($args),true);
	    	$args_arr = vibebp_recursive_sanitize_text_field($args_arr);
	    	$thread_ids = $args_arr['thread_ids'];
			$user_id = $args_arr['user_id'];
			
	    	if( (!empty($user_id)) && is_numeric($user_id) && (!empty($thread_ids)) ){
	    		$run = messages_delete_thread( $thread_ids , $user_id );

    			vibebp_fireabase_update_stale_requests($user_id,'/messages');
	    		if( $run ){
	    	    	$data=array(
		    			'status' => 1,
		    			'data' => $run,
		    			'message' => _x('Message deleted','Message deleted','vibebp')
		    		);
	    	    }else{
	    	    	$data=array(
		    			'status' => 0,
		    			'data' => $run,
		    			'message' => _x('Message Not deleted','Message Not deleted','vibebp')
		    		);
	    	    }
	    		$data=apply_filters( 'vibe_bp_api_messages_new_message', $data , $request ,$args);
	    	}else{
	    		$data=array(
	    			'status' => 0,
	    			'data' => null,
	    			'message' => _x('Please share valid details','Please share valid details','vibebp')
	    		);
	    	}
	    
    		return new WP_REST_Response( $data, 200 );  
	    }

	    function vibe_bp_api_message_actions($request){

	    	$args = json_decode($request->get_body(),true);
	    	$args = vibebp_recursive_sanitize_text_field($args);
	    	if(is_numeric($args['id'])){
	    		$ids = array((int)$args['id']);
	    	}else{
	    		$ids = $args['id'];
	    	}

	    	foreach($ids as $id){
		    	switch($args['action']){
		    		case 'read':
		    			messages_mark_thread_read( $id );
		    		break;
		    		case 'unread':
		    			messages_mark_thread_unread( $id );
		    		break;
		    		case 'star':
		    		case 'unstar': 
		    			
			    		bp_messages_star_set_action( array( 
							'action'     =>$args['action'],
							//'thread_id' => (int) $id,
							'message_id'=>(int) $id,
							'user_id'	=>$this->user->id,
							'bulk'       => ! empty( $args['bulk'] ) ? true : false
						 ) );
		    		break;
		    		case 'delete':
		    			$users = BP_Messages_Thread::get_recipients_for_thread($args['id']);
		    			messages_delete_thread( $args['id'], $this->user->id);
		    			if(!empty($users)){
			    			foreach($users as $k=>$user){
			    				vibebp_fireabase_update_stale_requests($user->user_id,'/messages');
			    			}
		    			}
		    		break;
		    		case 'delete_notice':
		    			if(!class_exists('BP_Messages_Notice')){
			    			return WP_REST_Response( array('status'=>0), 200 );  
			    		}
		    			$n = new BP_Messages_Notice($args['id']);

		    			$n->delete();
		    			vibebp_fireabase_update_stale_requests($this->user->id,'/messages');
		    		break;
		    		case 'active_notice':
			    		if(!class_exists('BP_Messages_Notice')){
			    			return WP_REST_Response( array('status'=>0), 200 );  
			    		}
		    			$n = new BP_Messages_Notice($args['id']);
		    			if(!empty($args['active'])){
		    				$n->activate();
		    			}else{
		    				$n->deactivate();
		    			}
		    			vibebp_fireabase_update_stale_requests($this->user->id,'/messages');
		    		break;
		    		case 'add_label':
		    			$label_key = 'vibebp_label_'.$this->user->id;
			    		bp_messages_add_meta( $id, $label_key,$args['label'], false );
		    		break;
		    		case 'remove_label':
		    			$label_key = 'vibebp_label_'.$this->user->id;
		    			bp_messages_delete_meta( $id, $label_key, $args['label'] );
		    		break;
		    	}
		    }
		    vibebp_fireabase_update_stale_requests($this->user->id,'/messages');
		    return new WP_REST_Response( array('status'=>1), 200 );  
	    }

	}
}

VIBE_BP_API_Rest_Messages_Controller::init();