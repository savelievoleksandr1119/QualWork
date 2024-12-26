<?php

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'VIBE_BP_API_Rest_XProfile_Controller' ) ) {
	
	class VIBE_BP_API_Rest_XProfile_Controller extends WP_REST_Controller{
		
		public static $instance;
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new VIBE_BP_API_Rest_XProfile_Controller();
	        return self::$instance;
	    }
	    public function __construct( ) {
			$this->namespace = Vibe_BP_API_NAMESPACE;
			$this->type= Vibe_BP_API_XPROFILE_TYPE;
			$this->register_routes();
		}

		public function register_routes() {
			register_rest_route( $this->namespace, '/profile', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_profile' ),
					'permission_callback' => array( $this, 'get_xprofile_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/profile/subNav', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_profile_subnav' ),
					'permission_callback' => array( $this, 'get_xprofile_permissions' ),
				),
			));
			register_rest_route( $this->namespace, '/profile/(?P<user_id>\d+)', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_profile' ),
					'permission_callback' => array( $this, 'get_xprofile_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/getProfileCompleteness', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'getProfileCompleteness' ),
					'permission_callback' => array( $this, 'get_xprofile_permissions' ),
				),
			));

			
			register_rest_route( $this->namespace, '/' .$this->type, array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_xprofile' ),
					'permission_callback' => array( $this, 'get_xprofile_permissions' ),
				),
			));
		

			register_rest_route( $this->namespace, '/' .$this->type.'/fields', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_xprofile_fields' ),
					'permission_callback' => array( $this, 'get_xprofile_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/fields/setvisibility', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'set_xprofile_field_visibility' ),
					'permission_callback' => array( $this, 'get_xprofile_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/allfields', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_allxprofile_fields' ),
					'permission_callback' => array( $this, 'get_xprofile_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/field/options', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_xprofile_field_options' ),
					'permission_callback' => array( $this, 'get_xprofile_public_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/field/save', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'save_xprofile_field' ),
					'permission_callback' => array( $this, 'get_xprofile_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/get/allfields', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_all_fields' ),
					'permission_callback' => array( $this, 'get_xprofile_public_permissions' ),
				),
			));

			
		}
		/*
	    PERMISSIONS
	     */
	    function get_xprofile_public_permissions($request){
	    	$security = $request->get_param('client_id');
			if($security == vibebp_get_setting('client_id')){
				return true;	
			}

			return $this->get_xprofile_permissions($request);
	    }
	    function get_xprofile_permissions($request){
	    	$body = json_decode($request->get_body(),true);
	    	if(!empty($body['token'])){
	    		$body['token'] = sanitize_text_field($body['token']);
	    	}else{
	    		return false;
	    	}
	       	
	        
        	$token = $body['token'];
	        
	        /** Get the Secret Key */
	        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
	        if (!$secret_key) {
	          	return false;
	        }

	        try {
	            $user_data = JWT::decode($token, $secret_key, array('HS256'));
		        $this->user = $user_data->data->user;
		        /** Let the user modify the data before send it back */
	        	return true;

	        }catch (Exception $e) {
	            /** Something is wrong trying to decode the token, send back the error */
	            return false;
	        }
	    	

	    	return false;
	    }

	    function get_all_fields($request){
	    	return new WP_REST_Response(array('status'=>true,'data'=>vibebp_get_all_member_type_profile_fields(),'default_member_type'=>vibebp_get_setting('default_member_type','bp','general')) , 200 );
	    }

	    function get_profile_subnav($request){
			$body = json_decode($request->get_body(),true);
			$data='';
			if(!empty($body['activeTab'])){
				wp_set_current_user( $this->user->id);
				$nav = get_transient('bp_rest_api_nav');
				$nav = maybe_unserialize($nav);
				if(!empty($nav)){
					forEach($nav as $item){
						if($item['css_id'] == $body['activeTab']){
							ob_start();
							if(function_exists($item['screen_function'])){
								$item['screen_function']();
							}
							
							$data = ob_get_clean();
							break;
						}
					}

					return new WP_REST_Response(['status'=>1,'html'=>$data],200);
				}

			}
			
			return new WP_REST_Response(['status'=>0,'message'=>__('Unable to get data','vibebp')],200);
		}

	    function get_profile($request){

	    	$body = json_decode($request->get_body(),true);
	    	$user_id = $this->user->id;
	    	if(!empty($body['id'])){
	    		$user_id = (int)$body['id'];
			}


			if(!empty($body['user_id'])){
				$user_id  = (int)$body['user_id'];
			}

			if(!empty($request->get_param('user_id'))){
				$user_id = (int)$request->get_param('user_id');	
			}
			

	    	global $bp;
	    	$bp->displayed_user->id = $user_id;
	    	$layout ='';
	    	if(!empty(bp_get_member_type($user_id))){
	    		$layout = new WP_Query(apply_filters('vibebp_public_profile_layout_query',array(
					'post_type'=>'member-profile',
					'post_name'=>bp_get_member_type($user_id),
					'posts_per_page'=>1,
					'meta_query'=>array(
						'relation'=>'AND',
						array(
							'key'=>'member_type',
							'compare'=>'NOT EXISTS'
						)
					)
				)));
	    	}
	    	
			if (!$layout || !$layout->have_posts() ){

				$layout = new WP_Query(array(
					'post_type'=>'member-profile',
					'orderby'=>'date',
					'order'=>'ASC',
					'posts_per_page'=>1,
					'meta_query'=>array(
						'relation'=>'AND',
						array(
							'key'=>'default_member-profile',
							'compare'=>'=',
							'value'=>1
						)
					)
				));
			}
			if ( !$layout->have_posts() ){
				$layout = new WP_Query(array(
					'post_type'=>'member-profile',
					'orderby'=>'date',
					'order'=>'ASC',
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

	    	$return ='';
			if ( $layout->have_posts() ){
				/* Start the Loop */
				while ( $layout->have_posts() ) :
					$layout->the_post();
					ob_start();
					global $post;
					setup_postdata($post);
                    the_content();
                    do_action('wp_head');
                    do_action('wp_footer');
                    do_action('wp_enqueue_scripts');
                    do_action('wp_enqueue_styles');
                    if(class_exists('\Elementor\Frontend')){
                        $elementorFrontend = new \Elementor\Frontend();
						$elementorFrontend->enqueue_scripts();
						$elementorFrontend->enqueue_styles();
					}
					$return = ob_get_clean();
					break;
				endwhile;
			}

			return new WP_REST_Response( do_shortcode($return), 200 );
	    }

	    function getProfileCompleteness($request){
	    	$user_id = $this->user->id;

	    	$completeness = 0;
	    	$completeness = get_user_meta($user_id,'profileCompleteness',true);

	    	if(!empty($completeness) && $completeness >=100){
	    		return new WP_REST_Response( array('status'=>false), 200 );
	    	}

	    	$groups = bp_xprofile_get_groups( array(
				'fetch_fields' => true
			) );

			if(!empty($groups)){

				$return = array('status'=>true);
				$incomplete = $total = 0;
				foreach($groups as $group){
					$return['groups'][]=array(
						'id'=>$group->id,
						'name'=>esc_html( apply_filters( 'bp_get_the_profile_group_name', $group->name,$group->id ) )
					);
					

					if(!empty($group->fields)){
						foreach ( $group->fields as $field ) {
							$total++;
							$member_type = bp_get_member_type($this->user->id);
							$types = bp_xprofile_get_meta( $field->id, 'field', 'member_type', false );
							if(empty($types) || (!empty($types) && in_array($member_type,$types))){
								$val = xprofile_get_field_data( $field->id, $this->user->id);
								if(empty($val)){
									$incomplete++;
									
								
									remove_filter( 'xprofile_get_field_data', 'xprofile_filter_format_field_value_by_field_id', 5, 2 );
									$field = xprofile_get_field( $field->id );
									$details = array(
										'id'=>$field->id,
										'group_id'=>$group->id,
										'name'=>$field->name,
										'description'=>$field->description,
										'type'=>$field->type,
										'value'=>(empty($val)?'':$val),
										'visibility'=>xprofile_get_field_visibility_level( $field->id, $this->user->id )
									);
									if($field->type == 'datebox'){
										$details['date_format'] = bp_xprofile_get_meta($field->id,'field','date_format',true);
									}

									if($field->type == 'upload'){
										$details['upload_size'] = (int)bp_xprofile_get_meta($field->id,'field','vibebp_upload_size',true);
										$details['upload_types'] = bp_xprofile_get_meta($field->id,'field','vibebp_upload_types',true);
										$details['all_upload_types']  = vibebp_getMimeTypes();
										
									}
									if($field->type == 'video'){
										$details['upload_size'] = (int)bp_xprofile_get_meta($field->id,'field','vibebp_video_size',true);
										$details['upload_types'] = bp_xprofile_get_meta($field->id,'field','vibebp_upload_types',true);
										$details['all_upload_types']  = vibebp_getMimeTypes();
										
									}
									if($field->type == 'gallery'){
										$details['upload_size'] = (int)bp_xprofile_get_meta($field->id,'field','vibebp_gallery_size',true);
										$details['upload_types'] = bp_xprofile_get_meta($field->id,'field','vibebp_gallery_types',true);
										$details['all_upload_types']  = vibebp_getMimeTypes();
										
									}
									
									$return['fields'][]= apply_filters('vibebp_get_allxprofile_fields_field_details',$details);
								}
							}
						}
					}
				}
				if(empty($return['fields'])){
					$incomplete = 0;
				}
				$complete = $total-$incomplete;
				if(empty($complete)){$complete=1;}
				$completeness = round(($complete*100)/$total,2);
				if(empty($incomplete)){
					update_user_meta($user_id,'profileCompleteness',$completeness);	
				}
				$return['completeness']=$completeness;
				$return['total_field_count']=$total;
			}
			return new WP_REST_Response( $return, 200 );
	    }

	    function get_xprofile($request){
	    	// return 'hi';

	    	$args = json_decode(file_get_contents('php://input'));
	    	$args = json_decode(json_encode($args),true);


	    	$groups = BP_XProfile_Group::get( $args );
	/*    	foreach ($groups as $group) {
	    		$ids[] = $group->id;
	    		$data[] = xprofile_get_field($group->id, 1);
	    	}
	    	return $data;


	    	bp_xprofile_format_activity_action_new_avatar( $action, $activity );

	    	$obj = new BP_XProfile_Field;

	    	return $obj->get_field_data(1);

	    	$run = bp_activity_get($args);


    		if( $run){
    	    	$data=array(
	    			'status' => 1,
	    			'data' => $run,
	    			'message' => _x('Activities Found','Activities Found','vibebp')
	    		);
    	    }else{
    	    	$data=array(
	    			'status' => 0,
	    			'data' => $run,
	    			'message' => _x('Activities not Found','Activities not Found','vibebp')
	    		);
    	    }
    		$data=apply_filters( 'vibe_bp_api_get_xprofile', $data , $request ,$args);
    		return new WP_REST_Response( $data, 200 ); */
	    }

	    function get_xprofile_fields($request){

	    	$return = array();
	    	$groups = bp_xprofile_get_groups( array(
				'fetch_fields' => true
			) );

			if(!empty($groups)){
				foreach($groups as $group){
					$return['groups'][]=array(
						'id'=>$group->id,
						'name'=>esc_html( apply_filters( 'bp_get_the_profile_group_name', $group->name,$group->id ) )
					);
					if(!empty($group->fields)){
						foreach ( $group->fields as $field ) {
							$field = xprofile_get_field( $field->id );

							$types = bp_xprofile_get_meta( $field->id, 'field', 'member_type', false );
							$member_type = bp_get_member_type($this->user->id);
							if(empty($types) || (!empty($types) && in_array($member_type,$types))){

								$return['fields'][]=array(
									'id'=>$field->id,
									'group_id'=>$group->id,
									'name'=>$field->name,
									'type'=>$field->type,
									'visibility'=>xprofile_get_field_visibility_level( $field->id, $this->user->id )
								);
							}
						}
					}
				}
			}

			return new WP_REST_Response( $return, 200 );
	    }

	    function set_xprofile_field_visibility($request){
	    	$body = json_decode($request->get_body(),true);
	    	$return = xprofile_set_field_visibility_level( (int)$body['field_id'], $this->user->id, sanitize_text_field($body['visibility']));
	    	vibebp_fireabase_update_stale_requests($this->user->id,'xprofile/fields');
    		return new WP_REST_Response( array('status'=>$return), 200 );

	    }


	    function get_allxprofile_fields($request){
			$body = json_decode($request->get_body(),true);
	    	$return = array('status'=>1);

	    	if(!empty($request->get_param('register'))){
	    		$fields = bp_xprofile_get_signup_field_ids();
	    		if(!empty($fields)){
	    			foreach($fields as $field){
	    				$field = new stdClass();
						$field->id = $field_id;
	    				$this->capture_field($field,$user_id);		
	    			}
	    		}
	    		
	    	}else{
	    		$groups = bp_xprofile_get_groups( array(
					'fetch_fields' => true
				) );	
	    	}
	    	
			
			if(!empty($body['user_id']) && vibebp_can_access_member_details($this->user)){
				$user_id = $body['user_id'];	
			}else{
				$user_id = $this->user->id;	
			}

			if(empty($user_id)){
				$user_id=0;
			}
			if(!empty($groups)){
				foreach($groups as $group){
					$return['groups'][]=array(
						'id'=>$group->id,
						'name'=>esc_html( apply_filters( 'bp_get_the_profile_group_name', $group->name ,$group->id) )
					);
					if(!empty($group->fields)){

						foreach ( $group->fields as $field) {
							$return['fields'][]=$this->capture_field($field,$user_id,$group);
						}
					}
				}
			}else{
				$return['status']=0;
			}

			return new WP_REST_Response( $return, 200 );
	    }

	    function capture_field($field,$user_id=0,$group=null){

	    	$types = bp_xprofile_get_meta( $field->id, 'field', 'member_type', false );
			$member_type = bp_get_member_type($user_id);

			$val='';
			if(empty($types) || (!empty($types) && in_array($member_type,$types))){
				remove_filter( 'xprofile_get_field_data', 'xprofile_filter_format_field_value_by_field_id', 5, 2 );
				if($field->type=='upload' || $field->type=='video' || $field->type=='gallery'){

					$val =BP_XProfile_ProfileData::get_value_byid( $field->id, $user_id);
					if(is_serialized($val)){
						$val = unserialize(stripcslashes($val));
					}
					
				}else{
					if(!empty($user_id)){
						$val = xprofile_get_field_data( $field->id, $user_id);
					}
				}
				$field = xprofile_get_field( $field->id );
				$details = apply_filters('vibebp_bp_xprofile_field',array(
					'id'=>$field->id,
					'name'=>$field->name,
					'type'=>$field->type,
					'value'=>(empty($val)?'':$val),
					'visibility'=>xprofile_get_field_visibility_level( $field->id, $user_id ),
					'description'=>$field->description
				));
				if(!empty($group)){
					$details['group_id']=$group->id;
				}
				if($field->type == 'datebox'){
					$details['date_format'] = bp_xprofile_get_meta($field->id,'field','date_format',true);
				}
				if($field->type == 'upload'){
					$details['upload_size'] = (int)bp_xprofile_get_meta($field->id,'field','vibebp_upload_size',true);
					$details['upload_types'] = bp_xprofile_get_meta($field->id,'field','vibebp_upload_types',true);
					$details['all_upload_types']  = vibebp_getMimeTypes();
					
				}
				if($field->type == 'video'){
					$details['upload_size'] = (int)bp_xprofile_get_meta($field->id,'field','vibebp_video_size',true);
					$details['upload_types'] = bp_xprofile_get_meta($field->id,'field','vibebp_video_types',true);
					$details['all_upload_types']  = vibebp_getMimeTypes();
					
				}
				if($field->type == 'gallery'){
					$details['upload_size'] = (int)bp_xprofile_get_meta($field->id,'field','vibebp_gallery_size',true);
					$details['upload_types'] = bp_xprofile_get_meta($field->id,'field','vibebp_gallery_types',true);
					$details['all_upload_types']  = vibebp_getMimeTypes();
					
				}
				if($field->type == 'repeatable'){
					$details['rtype'] = bp_xprofile_get_meta($field->id,'field','vibebp_repeatable_type',true);
					$details['style'] = bp_xprofile_get_meta($field->id,'field','vibebp_repeatable_style',true);
					
				}
				if($field->type == 'table'){
					$details['value'] = json_decode($details['value']);
				}
				return apply_filters('vibebp_get_allxprofile_fields_field_details',$details);
			}
			return false;
	    }

	    function save_xprofile_field($request){
			$body = json_decode($request->get_body(),true);
			$body = vibebp_recursive_sanitize_array_field($body );
			$user_id = $this->user->id;

		
	    	$return = array('status'=>1,'message'=>__('Field Saved !','vibebp'));

	    	if(!empty($body['type'])){
	    		if($body['type'] == 'datebox' && !empty($body['value'])){
	    			$body['value'] = date('Y-m-d H:i:s',strtotime($body['value']));
	    		}
	    	}

	    	if(!empty($body['type']) && ($body['type']=='upload' || $body['type']=='video' )){
	    		//remove_all_filters( 'xprofile_data_value_before_save' );
	    		remove_filter( 'xprofile_data_value_before_save','xprofile_sanitize_data_value_before_save', 1, 4 );
	    		//remove_filter( 'bp_xprofile_set_field_data_pre_validate',  'xprofile_filter_pre_validate_value_by_field_type', 10, 3 );
	    	}
	    	
	    	$vv = $body['value'];
	    	if(!empty($body['type']) && ($body['type']=='gallery')){
	    		$body['value'] = json_encode(wp_slash($body['value']));
			}
			
			if(is_array($body['value']) && !empty($body['type']) && $body['type'] === 'table'){
				$body['value'] = json_encode(wp_slash($body['value']));
			}

			/*
				user_id = 0 => save to option(temp)
				user_id => update user data by {accessed} user
			*/
			if(isset($body['user_id'])){
				if(vibebp_can_access_member_details($this->user) || apply_filters('vibe_user_can_create_members',false,$this->user->id)){
					$user_id  = $body['user_id'];
					if($body['user_id'] === 0){
						$option = get_option("temp_user_details_{$this->user->id}");
						if(empty($option) || !is_array($option)){
							$option = array();
						}
						$option[$body['field_id']] = array(
							'value' => $body['value'],
							'type' => $body['type']
						);
						update_option("temp_user_details_{$this->user->id}",$option);
						return new WP_REST_Response( array('status'=>1), 200 );
					}
				}else{
					return new WP_REST_Response( array('status'=>0,'message'=>__('Access Forbidden!','vibebp')), 403 );
				}
			}

			if(!empty($body['type'])){
	    		if($body['type'] == 'location' && $user_id){
	    			update_user_meta($user_id,'lat',$body['value']['lat']);
	    			update_user_meta($user_id,'lng',$body['value']['lng']);
	    		}
	    	}

			
			$saved = xprofile_set_field_data( $body['field_id'], $user_id, $body['value'] );

	    	add_filter( 'xprofile_data_value_before_save','xprofile_sanitize_data_value_before_save', 1, 4 );
	    	// $obj =new stdClass();
	    	// $obj->id=$body['field_id'];
	    	// $obj->user_id=$user_id;
	    	// $obj->value=$body['value'];
	    	// $obj->last_updated=bp_core_current_time();
	    	$obj = new BP_XProfile_ProfileData($body['field_id'], $user_id);
	    	do_action( 'xprofile_data_after_save',  $obj);

	    	if($saved && !empty($body['type']) && !in_array($body['type'], array('upload','video','gallery'))){
	    		if(is_Array($body['value'])){
		    		foreach($body['value'] as $key=>$value){
		    			bp_xprofile_update_field_meta($body['field_id'], $key,  wp_filter_nohtml_kses($value));
		    		}
		    	}
	    	}

	    	if(!$saved){
	    		$return['status']=0;
	    		$return['message']=__('Unable to save','vibebp');
	    	}else{
	    		vibebp_fireabase_update_stale_requests($user_id,'allfields');
	    		vibebp_fireabase_update_stale_requests('global','member_card/'.$user_id);
	    		vibebp_fireabase_update_stale_requests('global','member/'.$user_id);
	    		vibebp_fireabase_update_stale_requests($user_id,'getProfileCompleteness');
	    		
	    	}
	    	
	    	return new WP_REST_Response( $return, 200 );
	    }

	    function get_xprofile_field_options($request){
	    	$body = json_decode($request->get_body(),true);
	    	$body = vibebp_recursive_sanitize_array_field($body );
	    	$return = array('status'=>0,'message'=>__('Fetch Options','vibebp'));
	    	if(!empty($body['field_id'] )){
				if(!empty($body['type'] ) && $body['type'] === 'table'){
					$return['status'] = 1;
					$return['values'] = bp_xprofile_get_meta( $body['field_id'], 'field', 'vibebp_profile_field_table', true );
				}else{
					$field_obj = xprofile_get_field( $body['field_id'] );
					if(!empty($field_obj)){
						$return['status'] = 1;
						$return['values'] = $field_obj->get_children();	
					}
				}
	    	}

	    	if(!empty($body['fields'])){
	    		foreach($body['fields'] as $field){
	    			if(!empty($field['field_id'])){
	    				$field_obj = xprofile_get_field( $field['field_id'] );
	    				if(!empty($field_obj) && $field_obj->type=='country'){
	    					$data = array(
			    				'id'=>$field['field_id'],
			    			);
			    			$countries = vibebp_get_countries();
			    			foreach ($countries as $j => $c) {
			    				$data['values'][] = array('id'=>$j,'name'=>$c);
			    			}
	    					
			    			$return['values'][] = $data;
	    				}else{
	    					$return['values'][] = array(
			    				'id'=>$field['field_id'],
			    				'values'=>$field_obj->get_children()
			    			);
	    				}
		    			
		    			
	    			}
	    			
	    		}
	    		$return['status'] = 1;
	    	}


	    	return new WP_REST_Response( apply_filters('vibebp_xprofile_field_options',$return), 200 );
	    }
	}



}

VIBE_BP_API_Rest_XProfile_Controller::init();
