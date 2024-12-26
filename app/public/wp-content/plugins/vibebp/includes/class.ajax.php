<?php
/**
 * AjaxScripts
 *
 * @class       VibeBP_Register
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VibeBP_Ajax{
	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Ajax();
        return self::$instance;
    }

	private function __construct(){

		add_action('wp_ajax_nopriv_vibebp_wc_login',array($this,'vibebp_wc_login'));
		add_action('wp_ajax_nopriv_vibebp_wp_login',array($this,'vibebp_wp_login'));

		add_action('wp_ajax_nopriv_check_user_group_status',array($this,'check_user_group_status'));
		add_action('wp_ajax_check_user_group_status',array($this,'check_user_group_status'));

		add_action('wp_ajax_nopriv_join_user_group',array($this,'join_user_group'));
		add_action('wp_ajax_join_user_group',array($this,'join_user_group'));

		add_action('wp_ajax_nopriv_leave_user_group',array($this,'leave_user_group'));
		add_action('wp_ajax_leave_user_group',array($this,'leave_user_group'));
		
		add_Action('wp_ajax_request_user_group_membership',array($this,'request_user_group_membership'));
		add_Action('wp_ajax_nopriv_request_user_group_membership',array($this,'request_user_group_membership'));

		add_action( 'wp_ajax_vibebp_register_user', array($this,'vibebp_register_user' ));
        add_action( 'wp_ajax_nopriv_vibebp_register_user', array($this,'vibebp_register_user' ));
	}

	function vibebp_wp_login(){

		if(sanitize_text_field($_POST['client_id']) != vibebp_get_setting('client_id')){
			print_r(json_encode(array('status'=>0,'message'=>'Invalid client')));
			die();
		}
		if(!wp_verify_nonce(sanitize_text_field($_POST['security']),'security')){
			print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
			die();
		}
		$token = sanitize_text_field($_POST['token']);
		
		$data = vibebp_expand_token($token);
		if(!empty($data) && $data['status']){
			$expanded_token = $data['data'];
			if(email_exists($expanded_token->data->user->email) && !user_can($expanded_token->data->user->id,'manage_options')){
		        	//only works for non-admins
		        	wp_set_auth_cookie($expanded_token->data->user->id,false);
		        	print_r(json_encode(apply_filters(VIBEBP.'jwt_auth_token_validate_before_dispatch', $data)));
		        	die();
		        }else{
		        	print_r(json_encode(array('status'=>0,'message'=>'Invalid user')));
		        	die();
		        }
		}
		
		die();
	}

	function vibebp_wc_login(){

		if(sanitize_text_field($_POST['client_id']) != vibebp_get_setting('client_id')){
			print_r(json_encode(array('status'=>0,'message'=>'Invalid client')));
			die();
		}
		if(!wp_verify_nonce(sanitize_text_field($_POST['security']),'security')){
			print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
			die();
		}
		$token = sanitize_text_field($_POST['token']);
		
		$data = vibebp_expand_token($token);
		if(!empty($data) && $data['status']){
			$expanded_token = $data['data'];
			if(email_exists($expanded_token->data->user->email) && !user_can($expanded_token->data->user->id,'manage_options')){
		        	//only works for non-admins
		        	wp_set_auth_cookie($expanded_token->data->user->id,false);
		        	print_r(json_encode(apply_filters(VIBEBP.'jwt_auth_token_validate_before_dispatch', $data)));
		        	die();
		        }else{
		        	print_r(json_encode(array('status'=>0,'message'=>'Invalid user')));
		        	die();
		        }
		}
		
		die();
	}

	function check_user_group_status(){

		if(!empty(sanitize_text_field($_POST['token']))){
			$user = vibebp_expand_token(sanitize_text_field($_POST['token']));
			if(!empty( $user['data']) && !empty($_POST['group_id']) && is_numeric($_POST['group_id'])){
				$user_id = $user['data']->data->user->id; 
				$group_id = intval($_POST['group_id']);
				$group_label = '';
				if(groups_is_user_member( $user_id, $group_id )){
					$group_label = __('Leave group','vibebp');
					echo json_encode(array('status'=>1,'user_status'=>'joined','group_label'=>$group_label));
				}else{
					if(sanitize_text_field($_POST['status']) == 'public'){
						$group_label = __('Join group','vibebp');
					}
					if(sanitize_text_field($_POST['status']) == 'private'){
						if(groups_is_user_pending( $user_id, $group_id )){
							$group_label = __('Pending Request','vibebp');
							$status = 'pending_request';
						}else{
							$group_label = __('Request Membership','vibebp');	
							$status = 'request_membership';
						}
						
					}
					echo json_encode(array('status'=>1,'user_status'=>$status,'group_label'=>$group_label));
				}
			}
		}

		die();
	}

	function join_user_group(){
		if(!empty(sanitize_text_field($_POST['token']))){
			$user = vibebp_expand_token(sanitize_text_field($_POST['token']));
			if(!empty( $user['data']) && !empty($_POST['group_id']) && is_numeric($_POST['group_id'])){
				$user_id = $user['data']->data->user->id; 
				$group_id = intval($_POST['group_id']);
				$group_label = '';
				if(groups_join_group( $group_id, $user_id)){
					echo json_encode(array('status'=>1,'user_status'=>'joined','group_label'=>__('Leave group','vibebp')));
				}
			}
		}
		die();
	}

	function leave_user_group(){
		if(!empty($_POST['token'])){
			$user = vibebp_expand_token(sanitize_text_field($_POST['token']));
			if(!empty( $user['data']) && !empty($_POST['group_id']) && is_numeric($_POST['group_id'])){
				$user_id = $user['data']->data->user->id; 
				$group_id = intval($_POST['group_id']);
				$group_label = '';
				if(groups_leave_group( $group_id, $user_id)){
					echo json_encode(array('status'=>1,'user_status'=>'rejoin','group_label'=>__('Rejoingroup','vibebp')));
					die();
				}
			}
		}
		echo json_encode(array('status'=>0));
		die();
	}

	function request_user_group_membership(){
		if(!empty(sanitize_text_field($_POST['token']))){
			$user = vibebp_expand_token(sanitize_text_field($_POST['token']));
			if(!empty( $user['data']) && !empty($_POST['group_id']) && is_numeric($_POST['group_id'])){
				$user_id = $user['data']->data->user->id; 
				$group_id = intval($_POST['group_id']);
				$group_label = '';

				if(groups_send_membership_request(array(
					'user_id'       => $user_id,
					'group_id'      => $group_id))){
					echo json_encode(array('status'=>1,'user_status'=>'request_pending','group_label'=>__('Membership Requested','vibebp')));
				}
			}
		}
		die();
	}

	function find_setting_index($key,$datas){
        if(!empty($datas)){
            foreach ($datas as $k => $data) {
                if(!empty($data->id) && $data->id===$key){
                    return $k;
                }
            }
        }
        
        return -1;
    }
    function vibebp_register_user(){
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'bp_new_signup') || !isset($_POST['settings'])){
            echo '<div class="message">'.__('Security check Failed. Contact Administrator.','wplms').'</div>';
            die();
        }
        $flag = 0;
        $settings = json_decode(stripslashes($_POST['settings']));
        if(empty($settings)){
            $flag = 1; 
        }
        $name = sanitize_text_field($_POST['name']);
        if(empty($name)){
            echo '<div class="message_wrap error"><div class="message">'._x('Invalid Submission, name missing','error message when name missing','wplms').'<span></span></div></div>';
                die();
        }
        $member_type = '';
        $vibebp_user_bp_group = '';
        $form_settings = apply_filters('vibebp_registration_form_settings',array(
                'hide_username' =>  __('Auto generate username from email','wplms'),
                'password_meter' =>  __('Show password meter','wplms'),
                'show_group_label' =>  __('Show Field group labels','wplms'),
                'google_captcha' => __('Google Captcha','wplms'),
                'auto_login'=> __('Register & Login simultaneously','wplms'),
                'skip_mail' =>  __('Skip Mail verification','wplms'),
                'default_role'=>'',
                'member_type'=>'',
                'vibebp_user_bp_group'=>'',
        ));

        $user_args = $user_fields = $save_settings = array();

        if(empty($flag)){

            $all_form_settings = get_option('vibebp_registration_forms');
            if(!empty($all_form_settings))
                $reg_form_settings = $all_form_settings[$name];
            $secured_array = array('default_role','hide_username','auto_login','skip_mail','member_type');

            if(!empty($reg_form_settings)){
                if(!empty($reg_form_settings['settings'])){
                    //member_types select dropdown check
                    if(!empty($reg_form_settings['settings']['member_type']) &&  $reg_form_settings['settings']['member_type'] == 'enable_user_member_types_select'){
                        foreach($secured_array as $key => $secured){
                            if($secured == 'member_type'){
                                unset($secured_array[$key]);
                            }
                        }
                    }
                    
                    foreach ($secured_array as $secured) {
                       if(!empty($reg_form_settings['settings'][$secured])){ 
                            foreach($settings as $key => $setting){
                                if($setting->id == $secured){
                                    unset($settings[$key]);
                                }
                            }
                            $default_array= array('id'=>$secured,'value'=>$reg_form_settings['settings'][$secured]);
                            $settings[] = (object) $default_array;
                       
                        }
                    }
                }
            }

            $settings2 = array();

            foreach($settings as $setting){

                if(!empty($setting->id)){
                    $settings2[] = $setting->id;
                    if($setting->id == 'signup_username'){
                        $user_args['user_login'] = $setting->value;
                    }else if($setting->id == 'signup_email'){
                        $user_args['user_email'] = $setting->value;
                    }else if($setting->id == 'signup_password'){
                        $user_args['user_pass'] = $setting->value;
                    }else{
                        if(strpos($setting->id,'field') !== false){

                            $f = explode('_',$setting->id);
                            $field_id = $f[1]; 
                            if(strpos($field_id, '[')){ //checkbox
                                $v = str_replace('[','',$field_id);
                                $v = str_replace(']','',$v);
                                $field_id = $v;
                                if(is_Array($user_fields[$field_id]['value'])){
                                    $user_fields[$field_id]['value'][] = $setting->value;
                                }else{
                                    $user_fields[$field_id] = array('value'=>array($setting->value));
                                }
                            }else{
                                if(is_numeric($field_id) && !isset($f[2])){
                                    $user_fields[$field_id] = array('value'=>$setting->value);
                                }else{
                                    if(in_array($f[2],array('day','month','year'))){
                                        $user_fields['field_' . $field_id . '_'.$f[2]] = $setting->value;
                                    }else{
                                        $user_fields[$field_id]['visibility']=$setting->value;    
                                    }
                                }
                            }
                           
                        }else{
                            if(isset($form_settings[$setting->id])){
                            
                                $form_settings[$setting->id] = 0; // use it for empty check 

                                if($setting->id=='default_role'){
                                    //$save_settings[$setting->id]=$setting->value;
                                    //$user_args['role'] = $setting->value;
                                   	$user_args['role'] = $reg_form_settings['settings']['default_role'];
                                }

                                if($setting->id=='member_type'){
                                    $save_settings[$setting->id]=$setting->value;
                                    $member_type=$setting->value;
                                }
                                if($setting->id=='vibebp_user_bp_group'){
                                    if(in_array($setting->value,$reg_form_settings['settings']['vibebp_user_bp_group']) || $reg_form_settings['settings']['vibebp_user_bp_group'] === array('enable_user_select_group')){
                                        $save_settings[$setting->id]=$setting->value;
                                        $vibebp_user_bp_group = $setting->value;
                                    }else{
                                        echo '<div class="message_wrap"><div class="message error">'._x('Invalid Group selection','error message when group is not valid','wplms').'<span></span></div></div>';
                                        die();
                                    }
                                    
                                }
                            }
                            
                        }
                    }
                }
            }
            if(!in_array('vibebp_user_bp_group', $settings2)){
                if(!empty($reg_form_settings['settings']['vibebp_user_bp_group']) && is_array($reg_form_settings['settings']['vibebp_user_bp_group']) && $reg_form_settings['settings']['vibebp_user_bp_group'] !== array('enable_user_select_group') && count($reg_form_settings['settings']['vibebp_user_bp_group'])==1){
                    $vibebp_user_bp_group = $reg_form_settings['settings']['vibebp_user_bp_group'][0];
                }
            }
        }



        $user_args = apply_filters('vibebp_register_user_args',$user_args);
        

        //hook for validations externally
        do_action('vibebp_custom_registration_form_validations',$name,$settings,$all_form_settings,$user_args);
        do_action('wplms_custom_registration_form_validations',$name,$settings,$all_form_settings,$user_args);

        /*
        RUN CONDITIONAL CHECKS
        */
        $check_filter = filter_var($user_args['user_email'], FILTER_VALIDATE_EMAIL); // PHP 5.3
        if(empty($user_args['user_email']) || empty($user_args['user_pass']) || empty($check_filter)){
            echo '<div class="message_wrap"><div class="message error">'._x('Invalid Email/Password !','error message when registration form is empty','wplms').'<span></span></div></div>';
            die();
        }

        //Check if user exists
        if(!isset($user_args['user_email']) || email_exists($user_args['user_email'])){
            echo '<div class="message_wrap"><div class="message error">'._x('Email already registered.','error message','wplms').'<span></span></div></div>';
            die();
        }

        //Check if user exists
        if(!isset($user_args['user_login'])){

            $user_args['user_login'] = $user_args['user_email'];
            if(email_exists($user_args['user_login'])){
                echo '<div class="message_wrap"><div class="message error">'._x('Username already registered.','error message','wplms').'<span></span></div></div>';
                die();
            }
        }elseif (username_exists($user_args['user_login'])){
            echo '<div class="message_wrap"><div class="message error">'._x('Username already registered.','error message','wplms').'<span></span></div></div>';
            die();
        }
        if(!empty($reg_form_settings['settings']['google_captcha']) ){

            
            if(vibebp_get_setting('google_captcha_private_key','general','login')){
            
	            $res = wp_remote_post('https://www.google.com/recaptcha/api/siteverify',array(
	                'sslverify' => true,
	                'headers'   => [
	                    'content-type' => 'application/x-www-form-urlencoded',
	                ],
	                'body'=>[
	                    'secret'=>vibebp_get_setting('google_captcha_private_key','general','login'),
	                    'response'=>esc_attr($_POST['recaptchaToken'])
	                ]
	            ));
	            $apiBody     = json_decode( wp_remote_retrieve_body( $res ),true );
	            if(is_array($apiBody) && $apiBody['success']){
	                return new WP_REST_Response(array('status'=>0,'message'=>__('Captcha did not match.','vibebp'),'err'=>(empty($apiBody['error-codes'])?'':$apiBody['error-codes'])), 200);
	            }
	        }

            $index = $this->find_setting_index('g-recaptcha-response',$settings);
            if($index<0){
                echo '<div class="message_wrap"><div class="message error">'.__('Invalid Captcha field','wplms').'</div></div>';
                die();
            }else{
                $response = $objRecaptcha->verifyResponse($_SERVER['REMOTE_ADDR'], $settings[$index]->value);
                if(!isset($response->success) || 1 != $response->success){
                    echo '<div class="message_wrap"><div class="message error">'.__('Invalid Captcha field','wplms').'</div></div>';
                    die();
                }
            }
            
        }
        

        

        $error_message = array();
        if ( bp_is_active( 'xprofile' ) ) {

        // Make sure hidden field is passed and populated.
            if ( isset($user_fields) ) {

                // Let's compact any profile field info into an array.
                $profile_field_ids = array_keys($user_fields);

                // Loop through the posted fields formatting any datebox values then validate the field.
                foreach ( (array) $profile_field_ids as $field_id ) {
                    
                    if ( !isset( $user_fields[$field_id] ) || !isset($user_fields[$field_id]['value']) ) {

                        //Date Handling
                        if ( !empty( $user_fields['field_' . $field_id . '_day'] ) && !empty( $user_fields['field_' . $field_id . '_month'] ) && !empty( $user_fields['field_' . $field_id . '_year'] ) ){

                            if(empty($user_fields[$field_id])){$user_fields[$field_id] = array();}
                            $user_fields[$field_id]['value'] = date( 'Y-m-d H:i:s', strtotime( $user_fields['field_' . $field_id . '_day'] . $user_fields['field_' . $field_id . '_month'] . $user_fields['field_' . $field_id . '_year'] ) );

                        }
                        
                    }

                    
                    $field  = new BP_XProfile_Field( $field_id );
                     
                    // Create errors for required fields without values.
                    if ( xprofile_check_is_required_field( $field_id ) && empty($user_fields[$field_id]['value'])){
                        if($field->type!=='upload'){

                         $error_message[$field->id] = array('field_id'=>$field->id,'message'=>sprintf(__('%s is a required field', 'wplms' ),$field->name));
                        }else{
                            if ( empty($_FILES['file_field_'.$field_id])){
                                 $error_message[$field_id] = array('field_id'=>$field->id,'message'=>sprintf(__('%s is a required field', 'wplms' ),$field->name));
                            }
                        }
                    }else{
                        if (  !empty($user_fields[$field_id]['value']) && ! $field->type_obj->is_valid( $user_fields[$field_id]['value'] ) ) {
                            if(empty($error_message[$field->id])){
                                $error_message[]= array('field_id'=>$field->id,'message'=>sprintf(__('%s is not of type %s','wplms'),$field->name,$field->type));
                            }else{
                                $error_message[$field->id]['message'] .= ' , '.sprintf(__('%s is not of type %s','wplms'),$field->name,$field->type);
                            }
                        }
                    }
                }
                unset($user_fields['field_' . $field_id . '_day']);
                unset($user_fields['field_' . $field_id . '_month']);
                unset($user_fields['field_' . $field_id . '_year']);
                // This situation doesn't naturally occur so bounce to website root.
            }
        }

        if(!empty($_FILES) && is_array($_FILES)){
     
            foreach ($_FILES as $key => $file) {
                $uploadedfile = $file;

                $file_mime_type= $file['type'];
                $file_size=$file['size'];
                $upload_overrides = array( 'test_form' => false );
                $ffield = intval(str_replace('file_field_', '', $key));
                $field  = new BP_XProfile_Field( $ffield );

                if ( xprofile_check_is_required_field( $ffield ) && empty($file)){
                       
                     $error_message[$ffield] = array('field_id'=>$ffield,'message'=>sprintf(__('%s is a required field', 'wplms' ),$field->name));
                }
                $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
                if ( $movefile && ! isset( $movefile['error'] ) ) {
                    if ( $movefile && !isset( $movefile['error'] ) ) {
                        $filePath=$movefile['url'];
                        $attachment = array(
                            'guid'           => $filePath,
                            'post_mime_type' => $movefile['type'],
                            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filePath ) ),
                            'post_content'   => '',
                            'post_status'    => 'inherit',
                            'post_size'      => $file['size']
                        );
                        // Insert the attachment.
                        $attach_id = wp_insert_attachment( $attachment, $filePath );
                        if(!empty($attach_id)){
                            $post = get_post($attach_id);
                            if($post){
                                $attachment_data = $this->get_single_attachment($post);
                                $user_fields[$ffield]['value'] = $attachment_data;
                            }
                        }
                    }
                } else {
                     echo '<div class="message_wrap"><div class="message error">'._x('File could not be uploaded!','error message','wplms').'<span></span></div></div>';
                    die();
                } 
            }  
            
        }
        
        if(!empty($error_message)){
            
            foreach($error_message as $message){
                ?>
                document.querySelector(".bp-profile-field.field_<?php echo $message['field_id']; ?>").classList.add("field_error");
                var element1 = document.createElement('div');
                element1.classList.add('message');
                element1.classList.add('error');
                element1.classList.add('vbp_message');
                element1.innerHTML ="<?php echo $message['message']; ?>";
                document.querySelector(".bp-profile-field.field_<?php echo $message['field_id']; ?>").appendChild(element1);
                <?php
            }
            echo '</script>';
           
            
            die();
        }

        /*
        FORM SETTINGS
        */
        if(empty($form_settings['hide_username'])){
            $user_args['user_login'] = $user_args['user_email'];
        }
        $user_id = 0;
        if(empty($form_settings['skip_mail'])){
            $user_id = wp_insert_user($user_args);
            do_action('vibebp_custom_registration_form_user_added',$user_id,$user_args,$settings);
            do_action('wplms_custom_registration_form_user_added',$user_id,$user_args,$settings);

            if ( ! is_wp_error( $user_id ) ) {
                if(!empty($user_fields)){
                    foreach($user_fields as $field_id=>$val){
                        if(isset($val['value'])){
                            
                            $field  = new BP_XProfile_Field( $ffield );
                            if($field->type=='upload'){
                                remove_filter( 'xprofile_data_value_before_save',          'xprofile_sanitize_data_value_before_save', 1, 4 );
                            }
                            
                               

                            xprofile_set_field_data( $field_id, $user_id, $val['value'] );
                            if($field->type=='upload'){
                                 add_filter( 'xprofile_data_value_before_save',          'xprofile_sanitize_data_value_before_save', 1, 4 );
                            }
                            
                        }
                        if(isset($val['visibility'])){
                            xprofile_set_field_visibility_level( $field_id, $user_id, $val['visibility'] );
                        }
                    }
                }
                if(!empty($save_settings)){
                    foreach($save_settings as $s_id => $s_val){
                        update_user_meta($user_id,$s_id,$s_val);
                    }
                }

                if(!empty($member_type) && function_exists('bp_set_member_type')){
                    bp_set_member_type($user_id, $member_type );
                }
                if(function_exists('groups_join_group') && !empty($vibebp_user_bp_group) && is_numeric($vibebp_user_bp_group)){
                    groups_join_group($vibebp_user_bp_group, $user_id );
                }


                echo '<div class="message success"><div class="message_content">'.__('Congratulations ! you have been successfully registered !','wplms').'<span></span></div></div>';
            }else{
                echo '<div class="message_wrap"><div class="message error">'.$user_id->get_error_message().'<span></span></div></div>';
            }
        }else{
            $usermeta = array();

            $usermeta['password'] = wp_hash_password( $user_args['user_pass'] );

            if(!empty($user_fields)){
                foreach($user_fields as $field_id=>$val){
                    if(is_array($val) && isset($val['value'])){

                        $usermeta['field_' . $field_id] = $val['value'];
                    }else{
                        $usermeta['field_' . $field_id] = $val;
                    }
                    
                }
            }
            if(is_multisite()){
                foreach($save_settings as $s_id => $s_val){
                    $usermeta['vibebp_meta']=array('id'=>$s_id,'value'=>$s_val);
                }
            }
            $user_id = bp_core_signup_user( $user_args['user_login'], $user_args['user_pass'], $user_args['user_email'], $usermeta );

            do_action('vibebp_custom_registration_form_user_added',$user_id,$user_args,$settings);
            do_action('wplms_custom_registration_form_user_added',$user_id,$user_args,$settings);

            if(is_multisite()){
                if (  is_wp_error( $user_id ) ) {
                    echo '<div class="message_wrap"><div class="message error">'.$user_id->get_error_message().'<span></span></div></div>';
                }else{
                    echo '<div class="message success"><div class="message_content">'.__('Congratulations ! you have been successfully registered, Please check your email to activate the account','wplms').'<span></span></div></div>';
                }
            }else{
                if(!empty($user_fields)){
                    foreach($user_fields as $field_id=>$val){
                        if(isset($val['value'])){
                            $field  = new BP_XProfile_Field( $ffield );
                            if($field->type=='upload'){
                                remove_filter( 'xprofile_data_value_before_save',          'xprofile_sanitize_data_value_before_save', 1, 4 );
                            }
                            
                               

                            xprofile_set_field_data( $field_id, $user_id, $val['value'] );
                            if($field->type=='upload'){
                                 add_filter( 'xprofile_data_value_before_save',          'xprofile_sanitize_data_value_before_save', 1, 4 );
                            }
                            
                        }
                        if(isset($val['visibility'])){
                            
                            xprofile_set_field_visibility_level( $field_id, $user_id, $val['visibility'] );
                        }
                    }
                }
                if ( ! is_wp_error( $user_id ) ) {
                    if(!empty($save_settings)){
                        foreach($save_settings as $s_id => $s_val){
                            update_user_meta($user_id,$s_id,$s_val);
                        }
                    }
                    if(!empty($member_type) && function_exists('bp_set_member_type')){
                        bp_set_member_type($user_id, $member_type );
                    }
                     if(function_exists('groups_join_group') && !empty($vibebp_user_bp_group) && is_numeric($vibebp_user_bp_group)){
                        groups_join_group($vibebp_user_bp_group, $user_id );
                    }
                    echo '<div class="message success"><div class="message_content">'.__('Congratulations ! you have been successfully registered, Please check your email to activate the account','wplms').'<span></span></div></div>';

                }else{
                    echo '<div class="message_wrap"><div class="message error">'.$user_id->get_error_message().'<span></span></div></div>';
                }
            }
        }

        

        if(empty($form_settings['auto_login']) && !empty($user_id) && ! is_wp_error( $user_id )){
           if(!is_wp_error($user_id)){
                wp_set_current_user( $user_id, $user_args['user_login'] );
                wp_set_auth_cookie( $user_id,1 );
                do_action( 'wp_login', $user_args['user_login'], $user_args ); 
                $user = wp_get_current_user();
                $redirect_url = '';
                if(function_exists('vibe_get_option')){
                    $pageid = vibe_get_option('activation_redirect');
                    
                    if(function_exists('vibebp_get_setting') && !empty(vibebp_get_setting('bp_single_page'))){
                        $default_link = get_permalink(vibebp_get_setting('bp_single_page'));
                    }else{
                        $default_link = bp_core_get_user_domain($user_id);
                    }
                    if($pageid == 'dashboard'){
                        
                            $link = $default_link.'#component=dashboard';
                    }else if($pageid == 'profile'){
                        
                            $link = $default_link.'#component=profile';
                    }else if($pageid == 'mycourses'){
                        $link = $default_link.'#component=course&action=course';
                    
                    }else if(is_numeric($pageid)){

                        if(function_exists('icl_object_id')){
                            $pageid = icl_object_id($pageid, 'page', true);
                        }
                        $link = get_permalink($pageid);
                    }
                    $redirect_url = $link;
                }
                $redirect_url = apply_filters ( 'vibebp_registeration_redirect_url',$redirect_url, $user_id );
                
                if(empty($redirect_url)){
                    echo '<meta http-equiv="refresh" content="1">';
                    
                }else{
                    echo '<meta http-equiv="refresh" content="0;URL=\''.$redirect_url.'\'" />'; 
                }
                
            }
        }

        die();
    } 

}

VibeBP_Ajax::init();