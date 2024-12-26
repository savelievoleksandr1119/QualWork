<?php
/**
 * Functions
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Fetch active session tokens for the User, used in 1 session & Settings - settings.
 *
 * @param int $user_id         The Vibebp User ID.
 */
function vibebp_get_user_active_tokens($user_id){  
	$tokens = array();
	if(!empty($user_id)){ 
		$tokens = get_user_meta($user_id,'vibebp_active_tokens',true);
		if(!empty($tokens)) {
			$changed = 0;
			foreach ($tokens as $key => $token) {
				if(!empty($token['exp']) && intval($token['exp']) <= time()){
					unset($tokens[$key]);
					$changed = 1;
				}
			}
			if($changed){
				update_user_meta($user_id,'vibebp_active_tokens',$tokens);
			}
		}
	}
	return $tokens;
 } 

/**
* Add current active session tokens for the User.
*
* @param int $user_id         The Vibebp User ID.
* @param string $tokendata       JWT Auth token.
*/
function vibebp_add_user_active_tokens($user_id,$tokendata) { 
	if(!empty($user_id)){
		$tokens = get_user_meta($user_id,'vibebp_active_tokens',true);

		if(!empty($tokens)){
			foreach ($tokens as $key => $token) {
				if(!empty($token['exp']) && intval($token['exp']) <= time()){
					unset($tokens[$key]);
				}
			}
		}else{
			$tokens= [];
		}
		$tokens[$tokendata['vibebp_token_key']] = $tokendata;
		update_user_meta($user_id,'vibebp_active_tokens',$tokens);
	}
}


/**
* Delete current active session tokens for the User.
*
* @param int $user_id         The Vibebp User ID.
* @param string $token       JWT Auth token.
* @param array $tokens       Stored JWT Auth tokens.
*/
function vibebp_delete_user_active_tokens($user_id,$token,$tokens=null) { 
	if(!empty($user_id)){
		if(empty($tokens)){
			$tokens = get_user_meta($user_id,'vibebp_active_tokens',true);
		}
		if(!empty($tokens)){
			foreach ($tokens as $key => $token) {
				if(!empty($token['exp']) && intval($token['exp']) <= time()){
					unset($tokens[$key]);
				}
				if($key==$token && !empty($tokens[$key])){
					unset($tokens[$key]);
				}
			}
			update_user_meta($user_id,'vibebp_active_tokens',$tokens);
		}
	}
}

/**
* Sanitising editor output. Define allowed tags.
*
* @param array $allowedposttags  Allowed tags for editor.
*/
function allow_vibebp_editor_tags( $allowedposttags ){

	$allowedposttags['iframe'] = array(
		'align'                 => true,
		'allow'                 => true,
		'class'                 => true,
		'frameborder'           => true,
		'id' 					=> true,
		'marginheight' 			=> true,
		'marginwidth' 			=> true,
		'name' 					=> true,
		'scrolling' 			=> true,
		'src' => true,
		'style' => true,
		'width' => true,
		'allowFullScreen' => true,
		'height' => true,
		'mozallowfullscreen' => true,
		'src' => true,
		'title' => true,
		'webkitAllowFullScreen' => true,
		'width' => true
	);
	$allowedposttags['video'] = array(
		'align' => true,
		'allow' => true,
		'class' => true,
		'frameborder' => true,
		'id' => true,
		'marginheight' => true,
		'marginwidth' => true,
		'name' => true,
		'scrolling' => true,
		'src' => true,
		'style' => true,
		'width' => true,
		'allowFullScreen' => true,
		'height' => true,
		'mozallowfullscreen' => true,
		'src' => true,
		'title' => true,
		'webkitAllowFullScreen' => true,
		'width' => true,
		'controls'=>true,
	);
	$allowedposttags['audio'] = array(
		'controls'=>true,
		'align' => true,
		'allow' => true,
		'class' => true,
		'frameborder' => true,
		'id' => true,
		'marginheight' => true,
		'marginwidth' => true,
		'name' => true,
		'scrolling' => true,
		'src' => true,
		'style' => true,
		'width' => true,
		'allowFullScreen' => true,
		'height' => true,
		'mozallowfullscreen' => true,
		'src' => true,
		'title' => true,
		'webkitAllowFullScreen' => true,
		'width' => true
	);
	$allowedposttags['source'] = array(
		'type' => true,'src' => true,
	);
	return $allowedposttags;
}

/**
* Sanitising editor output.
*
* @param array $array  Allowed tags for editor
*/
function vibebp_recursive_sanitize_array_field($array) {


	add_filter( 'wp_kses_allowed_html', 'allow_vibebp_editor_tags', 1 );

	if(is_array($array)){
		foreach ( $array as $key => &$value ) {
	        if ( is_array( $value ) ) {
	            $value = vibebp_recursive_sanitize_array_field($value);
	        }else {
	            $value = wp_kses_post( $value );
	        }
	    }
	}else{
		$array = wp_kses_post($array);
	}
    return $array;
}

/**
* Sanitise complex array field in Editor.
*
* @param array $array       Nested field in array.
*/

function vibebp_recursive_sanitize_text_field($array) {
	if(is_array($array)){
		foreach ( $array as $key => &$value ) {
	        if ( is_array( $value ) ) {
	            $value = vibebp_recursive_sanitize_text_field($value);
	        }else {
				if(gettype($value) !== 'integer'){
	            	$value = sanitize_text_field( $value );
				}
	        }
	    }
	}else{
		if(gettype($array) !== 'integer'){
			$array = sanitize_text_field($array);
		}
	}
    return $array;
}

/**
* Default Sanitizer to be used via filter.
*
* @param string $text       String to be sanitised.
*/

function vibebp_sanitizer($text){
	return apply_filters('vibebp_sanitizer',$text);
}

/**
* Set VibeBP Setup Wizard is complete
*/

function vibebp_is_setup_complete(){
	$setup = get_option('vibebp_setup_complete');
	if(empty($setup)){
		return false;
	}
	return true;
}

/**
* Set VibeBP default registerd user roles.
*/

function vibebp_get_registered_user_roles(){
    $roles = apply_filters('vibebp_get_registered_user_roles',
        array(
            'instructor'=>array(
                        'label'=>_x('Instructors','menu for instructors','vibebp'),
                        'caps' => 'edit_posts',
                    ),
            'student'=>array(
                    'label'=>_x('Students','menu for students','vibebp'),
                    'caps' => 'read',
            )
        ));
    return $roles;    
}

/**
* Firebase revalidate stale requests.
*
* @param int $uid   	WordPRess User ID.
* @param string $node   firebase node.
* @param int $item_id   WordPRess Item ID..
* @param int $data   	Data to be stored in stable request.
*/
function vibebp_fireabase_update_stale_request_data($uid,$node,$item_id,$data){
	if(class_exists('Vibebp_Firebase_Tokens')){
		$vft = Vibebp_Firebase_Tokens::init();
		$token =$vft->vibebp_firebase_generate_id_token();
		if(!empty($token)){
			$firebase_config = unserialize(vibebp_get_setting('firebase_config','general','firebase'));
			$requests=[];
			$requests[] = array(
				'url' => $firebase_config['databaseURL'].'/stale_requests/'.$uid.'/'.$node.'/'.$item_id.'.json?auth='.$token,
		        'type' => 'PUT',
		        'data' =>  json_encode($data),
			);
			
			Requests::request_multiple($requests);
			
		}
	}
}
/**
* Firebase revalidate stale requests.
*
* @param int $uid   	WordPRess User ID.
* @param array $urls    URL Array to clear out.
*/
function vibebp_fireabase_update_stale_requests($uid,$urls){
	//firebase UID
	if(class_exists('Vibebp_Firebase_Tokens')){
		$vft = Vibebp_Firebase_Tokens::init();
		$token =$vft->vibebp_firebase_generate_id_token();
		if(!empty($token)){
			$firebase_config = unserialize(vibebp_get_setting('firebase_config','general','firebase'));
			if(!is_array($urls)){
				$urls = array($urls);
			}
			$requests = [];
			foreach($urls as $url){
				$url = urlencode(urlencode($url));
				$requests[] = array(
					'url' => $firebase_config['databaseURL'].'/stale_requests/'.$uid.'/'.$url.'.json?auth='.$token,
			        'type' => 'PUT',
			        'data' =>  json_encode(array('time'=>time()))
				);
			}
			Requests::request_multiple($requests);
			
		}
	}
	return true;
}

/**
* Generate JWT login Token.
*
* @param WP_User $user   	WordPRess User.
*/
function vibebp_generate_token($user=null){

	$secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false; 
	/** Valid credentials, the user exists create the according Token */
    $issuedAt = time();
    $notBefore = apply_filters( VIBEBP.'_token_expire_not_before', $issuedAt, $issuedAt);

    $duration = vibebp_get_setting('token_duration');
    if(empty($duration)){
    	$duration = DAY_IN_SECONDS * 7;
    }
    $expire = apply_filters( VIBEBP.'_token_expire', $issuedAt  + $duration, $issuedAt);

    if(empty($user)){
    	$user = wp_get_current_user();	
    }
    if(empty($user)){
    	return false;
    }
    
    $refresh_token = get_user_meta($user->data->ID,'vibebp_refresh_token',true);
    if(empty($refresh_token)){
    	$refresh_token = wp_generate_password(12);
    	update_user_meta($user->data->ID,'vibebp_refresh_token',$refresh_token);
    }
    

    $user->vibebp_token_key = wp_generate_password(6,false,false);
    $ip = vibebp_get_client_ip();

    $tokendata = array(
    	'vibebp_token_key'=>$user->vibebp_token_key,
    	'exp'=>$expire,
    	'login_time'=>time(),
    	'ip'=>$ip,
    	'usr'=>(!empty($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:''),
    );
    vibebp_add_user_active_tokens($user->data->ID,$tokendata);

    $tokendata = array(
        'iss' => get_bloginfo('url'),
        'iat' => $issuedAt,
        'nbf' => $notBefore,
        'exp' => $expire,
        'vibebp_token_key' => $user->vibebp_token_key,
        'data' => apply_filters('vibebp_jwt_token_data',array(
            'user' => array(
                'id' => $user->data->ID,
                'username'=>$user->data->user_login,
                'slug'=>$user->data->user_nicename,
                'email'=>$user->data->user_email,
                'exp' => $expire,
                'member_type'=>bp_get_member_type($user->data->ID),
                'refresh_token'=>$refresh_token,
                'avatar'=> (function_exists('bp_core_fetch_avatar')?bp_core_fetch_avatar(array(
                                'item_id' => $user->data->ID,
                                'object'  => 'user',
                                'type'=>'full',
                                'html'    => false
                            )):get_avatar($user->data->user_email,240)),
                'displayname'=>$user->data->display_name,
                'roles'=> $user->roles,
                'caps'=> apply_filters('vibebp_user_caps',$user->allcaps,$user),
                'profile_link'=>vibebp_get_profile_link($user->data->user_nicename)
                ),
            'ip'=>$ip,
            'client_id'=>vibebp_get_setting('client_id')
            )
        ),
    );
    if(function_exists('bp_update_user_last_activity')){
    	bp_update_user_last_activity($user->data->ID);	
    }else{
    	update_post_meta($user->data->ID,'last_activity',time());
    }
    
    
    if(!class_exists('JWT')){
    	include_once 'core/JWT.php';
    }
    /** Let the user modify the token data before the sign. */
    $token = JWT::encode(apply_filters(VIBEBP.'jwt_auth_token_before_sign', $tokendata, $user), $secret_key);
    
    return $token;
}

/**
* Expand / Decode JWT login Token.
*
* @param string $token   JWT token.
*/
function vibebp_expand_token($token){
	$secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
    
    if(!class_exists('JWT')){
    	include_once ('core/JWT.php');
    }
    try {
        $expanded_token = JWT::decode($token, $secret_key, array('HS256'));
        $expanded_token = apply_filters('vibebp_validate_token',$expanded_token,$token);

        if($expanded_token){

        	if(!empty($expanded_token->data->client_id) && $expanded_token->data->client_id == vibebp_get_setting('client_id')
        	 ){
        		$data = array(
		        	'status' => 1, 
		            'data' => $expanded_token,
		            'message'=>_x('Valid Token','Valid Token','vibebp')
		        );	
        	}else{
        		$data = array(
		        	'status' => 0, 
		        	'data' => 'jwt_auth_invalid_token',
		            'message'=>_x('Token XSS security issue.','XSS','vibebp')
		        );	
        	}
            ;
	        return apply_filters(VIBEBP.'jwt_auth_token_validate_before_dispatch', $data);
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
        return $data;
    }
}

/**
* Process notification types.
*
* @param string $notification   JWT token.
*/
function vibebp_process_notification($notification){

	$text='';
	if(isset($notification['type'])){
		switch($notification['type']){
			case 'chat_invite':
				if(is_numeric($notification['sender'])){
					$name = bp_core_get_user_displayname($notification['sender']);
				}else{
					$name = _x('Guest','notification','vibebp');
				}
				
				$text=array(
					'message'=>sprintf(_x('%s invited you to chat.','notification','vibebp'),$name),
					'actions'=>array(
						array('label'=>__('Accept','vibebp'),'key'=>'accept','event'=>$notification['type'],'message'=>__('Chat Invite accepted','vibebp')),
						array('label'=>__('Reject','vibebp'),'key'=>'reject','event'=>$notification['type'],'message'=>__('Chat Invite rejected','vibebp'))
					)
				);
			break;
			case 'user_online':
				if(is_numeric($notification['sender'])){
					$name = bp_core_get_user_displayname($notification['sender']);
				}else{
					$name = _x('Guest','notification','vibebp');
				}
				$text=sprintf(_x('%s is now online.','notification','vibebp'),$name);
			break;
		}
}
	return apply_filters('vibebp_process_notification',$text,$notification);
}

/**
* General purpose function.
*
* @param Object $obj   An Object.
*/
function ConverObjectToArray($obj) {

    $array  = array(); // noisy $array does not exist
    
    foreach ((array)$obj as $key => $val) {
    	$val = (is_object($val)) ? ConverObjecttoArray($val) : $val;
        $array[$key] = $val;
    }
    return $array;
}
/**
* Return all world countries.
*/
function vibebp_get_countries(){
	$countries =
		array(
		"AF" => _x("Afghanistan","country list","vibebp"),
		"AL" => _x("Albania","country list","vibebp"),
		"DZ" => _x("Algeria","country list","vibebp"),
		"AS" => _x("American Samoa","country list","vibebp"),
		"AD" => _x("Andorra","country list","vibebp"),
		"AO" => _x("Angola","country list","vibebp"),
		"AI" => _x("Anguilla","country list","vibebp"),
		"AQ" => _x("Antarctica","country list","vibebp"),
		"AG" => _x("Antigua and Barbuda","country list","vibebp"),
		"AR" => _x("Argentina","country list","vibebp"),
		"AM" => _x("Armenia","country list","vibebp"),
		"AW" => _x("Aruba","country list","vibebp"),
		"AU" => _x("Australia","country list","vibebp"),
		"AT" => _x("Austria","country list","vibebp"),
		"AZ" => _x("Azerbaijan","country list","vibebp"),
		"BS" => _x("Bahamas","country list","vibebp"),
		"BH" => _x("Bahrain","country list","vibebp"),
		"BD" => _x("Bangladesh","country list","vibebp"),
		"BB" => _x("Barbados","country list","vibebp"),
		"BY" => _x("Belarus","country list","vibebp"),
		"BE" => _x("Belgium","country list","vibebp"),
		"BZ" => _x("Belize","country list","vibebp"),
		"BJ" => _x("Benin","country list","vibebp"),
		"BM" => _x("Bermuda","country list","vibebp"),
		"BT" => _x("Bhutan","country list","vibebp"),
		"BO" => _x("Bolivia","country list","vibebp"),
		"BA" => _x("Bosnia and Herzegovina","country list","vibebp"),
		"BW" => _x("Botswana","country list","vibebp"),
		"BV" => _x("Bouvet Island","country list","vibebp"),
		"BR" => _x("Brazil","country list","vibebp"),
		"IO" => _x("British Indian Ocean Territory","country list","vibebp"),
		"BN" => _x("Brunei Darussalam","country list","vibebp"),
		"BG" => _x("Bulgaria","country list","vibebp"),
		"BF" => _x("Burkina Faso","country list","vibebp"),
		"BI" => _x("Burundi","country list","vibebp"),
		"KH" => _x("Cambodia","country list","vibebp"),
		"CM" => _x("Cameroon","country list","vibebp"),
		"CA" => _x("Canada","country list","vibebp"),
		"CV" => _x("Cape Verde","country list","vibebp"),
		"KY" => _x("Cayman Islands","country list","vibebp"),
		"CF" => _x("Central African Republic","country list","vibebp"),
		"TD" => _x("Chad","country list","vibebp"),
		"CL" => _x("Chile","country list","vibebp"),
		"CN" => _x("China","country list","vibebp"),
		"CX" => _x("Christmas Island","country list","vibebp"),
		"CC" => _x("Cocos (Keeling) Islands","country list","vibebp"),
		"CO" => _x("Colombia","country list","vibebp"),
		"KM" => _x("Comoros","country list","vibebp"),
		"CG" => _x("Congo","country list","vibebp"),
		"CD" => _x("Congo, the Democratic Republic of the","country list","vibebp"),
		"CK" => _x("Cook Islands","country list","vibebp"),
		"CR" => _x("Costa Rica","country list","vibebp"),
		"CI" => _x("Cote D'Ivoire","country list","vibebp"),
		"HR" => _x("Croatia","country list","vibebp"),
		"CU" => _x("Cuba","country list","vibebp"),
		"CY" => _x("Cyprus","country list","vibebp"),
		"CZ" => _x("Czech Republic","country list","vibebp"),
		"DK" => _x("Denmark","country list","vibebp"),
		"DJ" => _x("Djibouti","country list","vibebp"),
		"DM" => _x("Dominica","country list","vibebp"),
		"DO" => _x("Dominican Republic","country list","vibebp"),
		"EC" => _x("Ecuador","country list","vibebp"),
		"EG" => _x("Egypt","country list","vibebp"),
		"SV" => _x("El Salvador","country list","vibebp"),
		"GQ" => _x("Equatorial Guinea","country list","vibebp"),
		"ER" => _x("Eritrea","country list","vibebp"),
		"EE" => _x("Estonia","country list","vibebp"),
		"ET" => _x("Ethiopia","country list","vibebp"),
		"FK" => _x("Falkland Islands (Malvinas)","country list","vibebp"),
		"FO" => _x("Faroe Islands","country list","vibebp"),
		"FJ" => _x("Fiji","country list","vibebp"),
		"FI" => _x("Finland","country list","vibebp"),
		"FR" => _x("France","country list","vibebp"),
		"GF" => _x("French Guiana","country list","vibebp"),
		"PF" => _x("French Polynesia","country list","vibebp"),
		"TF" => _x("French Southern Territories","country list","vibebp"),
		"GA" => _x("Gabon","country list","vibebp"),
		"GM" => _x("Gambia","country list","vibebp"),
		"GE" => _x("Georgia","country list","vibebp"),
		"DE" => _x("Germany","country list","vibebp"),
		"GH" => _x("Ghana","country list","vibebp"),
		"GI" => _x("Gibraltar","country list","vibebp"),
		"GR" => _x("Greece","country list","vibebp"),
		"GL" => _x("Greenland","country list","vibebp"),
		"GD" => _x("Grenada","country list","vibebp"),
		"GP" => _x("Guadeloupe","country list","vibebp"),
		"GU" => _x("Guam","country list","vibebp"),
		"GT" => _x("Guatemala","country list","vibebp"),
		"GN" => _x("Guinea","country list","vibebp"),
		"GW" => _x("Guinea-Bissau","country list","vibebp"),
		"GY" => _x("Guyana","country list","vibebp"),
		"HT" => _x("Haiti","country list","vibebp"),
		"HM" => _x("Heard Island and Mcdonald Islands","country list","vibebp"),
		"VA" => _x("Holy See (Vatican City State)","country list","vibebp"),
		"HN" => _x("Honduras","country list","vibebp"),
		"HK" => _x("Hong Kong","country list","vibebp"),
		"HU" => _x("Hungary","country list","vibebp"),
		"IS" => _x("Iceland","country list","vibebp"),
		"IN" => _x("India","country list","vibebp"),
		"ID" => _x("Indonesia","country list","vibebp"),
		"IR" => _x("Iran, Islamic Republic of","country list","vibebp"),
		"IQ" => _x("Iraq","country list","vibebp"),
		"IE" => _x("Ireland","country list","vibebp"),
		"IL" => _x("Israel","country list","vibebp"),
		"IT" => _x("Italy","country list","vibebp"),
		"JM" => _x("Jamaica","country list","vibebp"),
		"JP" => _x("Japan","country list","vibebp"),
		"JO" => _x("Jordan","country list","vibebp"),
		"KZ" => _x("Kazakhstan","country list","vibebp"),
		"KE" => _x("Kenya","country list","vibebp"),
		"KI" => _x("Kiribati","country list","vibebp"),
		"KP" => _x("Korea, Democratic People's Republic of","country list","vibebp"),
		"KR" => _x("Korea, Republic of","country list","vibebp"),
		"KW" => _x("Kuwait","country list","vibebp"),
		"KG" => _x("Kyrgyzstan","country list","vibebp"),
		"LA" => _x("Lao People's Democratic Republic","country list","vibebp"),
		"LV" => _x("Latvia","country list","vibebp"),
		"LB" => _x("Lebanon","country list","vibebp"),
		"LS" => _x("Lesotho","country list","vibebp"),
		"LR" => _x("Liberia","country list","vibebp"),
		"LY" => _x("Libyan Arab Jamahiriya","country list","vibebp"),
		"LI" => _x("Liechtenstein","country list","vibebp"),
		"LT" => _x("Lithuania","country list","vibebp"),
		"LU" => _x("Luxembourg","country list","vibebp"),
		"MO" => _x("Macao","country list","vibebp"),
		"MK" => _x("Macedonia, the Former Yugoslav Republic of","country list","vibebp"),
		"MG" => _x("Madagascar","country list","vibebp"),
		"MW" => _x("Malawi","country list","vibebp"),
		"MY" => _x("Malaysia","country list","vibebp"),
		"MV" => _x("Maldives","country list","vibebp"),
		"ML" => _x("Mali","country list","vibebp"),
		"MT" => _x("Malta","country list","vibebp"),
		"MH" => _x("Marshall Islands","country list","vibebp"),
		"MQ" => _x("Martinique","country list","vibebp"),
		"MR" => _x("Mauritania","country list","vibebp"),
		"MU" => _x("Mauritius","country list","vibebp"),
		"YT" => _x("Mayotte","country list","vibebp"),
		"MX" => _x("Mexico","country list","vibebp"),
		"FM" => _x("Micronesia, Federated States of","country list","vibebp"),
		"MD" => _x("Moldova, Republic of","country list","vibebp"),
		"MC" => _x("Monaco","country list","vibebp"),
		"MN" => _x("Mongolia","country list","vibebp"),
		"MS" => _x("Montserrat","country list","vibebp"),
		"MA" => _x("Morocco","country list","vibebp"),
		"MZ" => _x("Mozambique","country list","vibebp"),
		"MM" => _x("Myanmar","country list","vibebp"),
		"NA" => _x("Namibia","country list","vibebp"),
		"NR" => _x("Nauru","country list","vibebp"),
		"NP" => _x("Nepal","country list","vibebp"),
		"NL" => _x("Netherlands","country list","vibebp"),
		"AN" => _x("Netherlands Antilles","country list","vibebp"),
		"NC" => _x("New Caledonia","country list","vibebp"),
		"NZ" => _x("New Zealand","country list","vibebp"),
		"NI" => _x("Nicaragua","country list","vibebp"),
		"NE" => _x("Niger","country list","vibebp"),
		"NG" => _x("Nigeria","country list","vibebp"),
		"NU" => _x("Niue","country list","vibebp"),
		"NF" => _x("Norfolk Island","country list","vibebp"),
		"MP" => _x("Northern Mariana Islands","country list","vibebp"),
		"NO" => _x("Norway","country list","vibebp"),
		"OM" => _x("Oman","country list","vibebp"),
		"PK" => _x("Pakistan","country list","vibebp"),
		"PW" => _x("Palau","country list","vibebp"),
		"PS" => _x("Palestinian Territory, Occupied","country list","vibebp"),
		"PA" => _x("Panama","country list","vibebp"),
		"PG" => _x("Papua New Guinea","country list","vibebp"),
		"PY" => _x("Paraguay","country list","vibebp"),
		"PE" => _x("Peru","country list","vibebp"),
		"PH" => _x("Philippines","country list","vibebp"),
		"PN" => _x("Pitcairn","country list","vibebp"),
		"PL" => _x("Poland","country list","vibebp"),
		"PT" => _x("Portugal","country list","vibebp"),
		"PR" => _x("Puerto Rico","country list","vibebp"),
		"QA" => _x("Qatar","country list","vibebp"),
		"RE" => _x("Reunion","country list","vibebp"),
		"RO" => _x("Romania","country list","vibebp"),
		"RU" => _x("Russian Federation","country list","vibebp"),
		"RW" => _x("Rwanda","country list","vibebp"),
		"SH" => _x("Saint Helena","country list","vibebp"),
		"KN" => _x("Saint Kitts and Nevis","country list","vibebp"),
		"LC" => _x("Saint Lucia","country list","vibebp"),
		"PM" => _x("Saint Pierre and Miquelon","country list","vibebp"),
		"VC" => _x("Saint Vincent and the Grenadines","country list","vibebp"),
		"WS" => _x("Samoa","country list","vibebp"),
		"SM" => _x("San Marino","country list","vibebp"),
		"ST" => _x("Sao Tome and Principe","country list","vibebp"),
		"SA" => _x("Saudi Arabia","country list","vibebp"),
		"SN" => _x("Senegal","country list","vibebp"),
		"CS" => _x("Serbia and Montenegro","country list","vibebp"),
		"SC" => _x("Seychelles","country list","vibebp"),
		"SL" => _x("Sierra Leone","country list","vibebp"),
		"SG" => _x("Singapore","country list","vibebp"),
		"SK" => _x("Slovakia","country list","vibebp"),
		"SI" => _x("Slovenia","country list","vibebp"),
		"SB" => _x("Solomon Islands","country list","vibebp"),
		"SO" => _x("Somalia","country list","vibebp"),
		"ZA" => _x("South Africa","country list","vibebp"),
		"GS" => _x("South Georgia and the South Sandwich Islands","country list","vibebp"),
		"ES" => _x("Spain","country list","vibebp"),
		"LK" => _x("Sri Lanka","country list","vibebp"),
		"SD" => _x("Sudan","country list","vibebp"),
		"SR" => _x("Suriname","country list","vibebp"),
		"SJ" => _x("Svalbard and Jan Mayen","country list","vibebp"),
		"SZ" => _x("Swaziland","country list","vibebp"),
		"SE" => _x("Sweden","country list","vibebp"),
		"CH" => _x("Switzerland","country list","vibebp"),
		"SY" => _x("Syrian Arab Republic","country list","vibebp"),
		"TW" => _x("Taiwan, Province of China","country list","vibebp"),
		"TJ" => _x("Tajikistan","country list","vibebp"),
		"TZ" => _x("Tanzania, United Republic of","country list","vibebp"),
		"TH" => _x("Thailand","country list","vibebp"),
		"TL" => _x("Timor-Leste","country list","vibebp"),
		"TG" => _x("Togo","country list","vibebp"),
		"TK" => _x("Tokelau","country list","vibebp"),
		"TO" => _x("Tonga","country list","vibebp"),
		"TT" => _x("Trinidad and Tobago","country list","vibebp"),
		"TN" => _x("Tunisia","country list","vibebp"),
		"TR" => _x("Turkey","country list","vibebp"),
		"TM" => _x("Turkmenistan","country list","vibebp"),
		"TC" => _x("Turks and Caicos Islands","country list","vibebp"),
		"TV" => _x("Tuvalu","country list","vibebp"),
		"UG" => _x("Uganda","country list","vibebp"),
		"UA" => _x("Ukraine","country list","vibebp"),
		"AE" => _x("United Arab Emirates","country list","vibebp"),
		"GB" => _x("United Kingdom","country list","vibebp"),
		"US" => _x("United States","country list","vibebp"),
		"UM" => _x("United States Minor Outlying Islands","country list","vibebp"),
		"UY" => _x("Uruguay","country list","vibebp"),
		"UZ" => _x("Uzbekistan","country list","vibebp"),
		"VU" => _x("Vanuatu","country list","vibebp"),
		"VE" => _x("Venezuela","country list","vibebp"),
		"VN" => _x("Viet Nam","country list","vibebp"),
		"VG" => _x("Virgin Islands, British","country list","vibebp"),
		"VI" => _x("Virgin Islands, U.s.","country list","vibebp"),
		"WF" => _x("Wallis and Futuna","country list","vibebp"),
		"EH" => _x("Western Sahara","country list","vibebp"),
		"YE" => _x("Yemen","country list","vibebp"),
		"ZM" => _x("Zambia","country list","vibebp"),
		"ZW" => _x("Zimbabwe","country list","vibebp"),
		);

return $countries;
}

/**
* Record activity as Liked.
*
* @param int $activity_id   ID of activity to be liked.
* @param int $user_id   	WordPRess User ID.
*/

function vibebp_activity_add_user_like($activity_id,$user_id){


	$my_favs = bp_get_user_meta( $user_id, 'bp_like_activities', true );
	if ( empty( $my_favs ) || ! is_array( $my_favs ) ) {
		$my_favs = array();
	}

	// Bail if the user has already favorited this activity item.
	if ( in_array( $activity_id, $my_favs ) ) {
		return false;
	}
	$my_favs[] = $activity_id;
	$fav_count = bp_activity_get_meta( $activity_id, 'like_count' );
	$fav_count = !empty( $fav_count ) ? (int) $fav_count + 1 : 1;
	bp_update_user_meta( $user_id, 'bp_like_activities', $my_favs );
	if ( bp_activity_update_meta( $activity_id, 'like_count', $fav_count ) ) {
		do_action( 'bp_activity_add_user_like', $activity_id, $user_id );
		return true;
	} else {
		do_action( 'bp_activity_add_user_like_fail', $activity_id, $user_id );
		return false;
	}
}

/**
* Remove activity as Liked.
*
* @param int $activity_id   ID of activity to be liked.
* @param int $user_id   	WordPRess User ID.
*/
function vibebp_activity_remove_user_like($activity_id,$user_id){
	
	$my_favs = bp_get_user_meta( $user_id, 'bp_like_activities', true );
	$my_favs = array_flip( (array) $my_favs );

	if ( ! isset( $my_favs[ $activity_id ] ) ) {
		return false;
	}

	unset( $my_favs[$activity_id] );
	$my_favs = array_unique( array_flip( $my_favs ) );

	// Update the total number of users who have favorited this activity.
	$fav_count = bp_activity_get_meta( $activity_id, 'like_count' );
	if ( ! empty( $fav_count ) ) {
		if ( bp_activity_update_meta( $activity_id, 'like_count', (int) $fav_count - 1 ) ) {
			if ( bp_update_user_meta( $user_id, 'bp_like_activities', $my_favs ) ) {
				do_action( 'bp_activity_remove_user_like', $activity_id, $user_id );
			} else {
				return false;
			}
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/**
* Record activity as Liked.
*
* @param int $activity_id   ID of activity to be liked.
* @param int $user_id   	WordPRess User ID.
*/
function vibebp_can_access_member_details($user){
	$member_type  = vibebp_get_setting('create_member','bp','general');
	$caps = (array)$user->caps;
	if( !empty($user) && ( (!$member_type && user_can($user->id,'manage_options')) || !empty($caps[$member_type] )) ){
		return true;
	}
	return apply_filters('vibebp_can_access_member_details',false,$user);
}

/**
* Record activity as Liked.
*
* @param int $activity_id   ID of activity to be liked.
* @param int $user_id   	WordPRess User ID.
*/
if(!function_exists('vibebp_render_block_from_style')){
  function vibebp_render_block_from_style($custom_post,$featured_style,$cols='medium',$n=100,$link=0,$zoom=0){
    $return=$read_more=$class='';    

    $more = __('Read more','vibebp');
    
    if(strlen($custom_post->post_content) > $n)
        $read_more= '<a href="'.get_permalink($custom_post->ID).'" class="link">'.$more.'</a>';
                    
    switch($featured_style){

        default:
               $return .='<div class="block">';
                $return .='<div class="block_media">';
                
                if(isset($link) && $link){
                    $return .='<span class="overlay"></span>';
                    $return .= '<a href="'.get_permalink($custom_post->ID).'" class="hover-link hyperlink"><i class="icon-hyperlink"></i></a>';
                }
                
                $return .= vibebp_featured_component($custom_post->ID,$cols);
                
                $category='';
                if($custom_post->post_type == 'post'){
                    $cats = get_the_category(); 
                    if(is_array($cats)){
                        foreach($cats as $cat){
                        $category .= '<a href="'.get_category_link($cat->term_id ).'">'.$cat->name.'</a> ';
                        }
                    }
                }
                
                if($custom_post->post_type == 'product'){
                    $category = get_the_term_list( $custom_post->ID, 'product_cat', '', ' / ' );
                }

                $return .='</div>';
                $return .='<div class="block_content">';
                $return .= apply_filters('vibeapp_thumb_heading','<h4 class="block_title"><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>',$featured_style);

                if($custom_post->post_type == 'product'){
                    if(function_exists('wc_get_product')){
                        $product = wc_get_product( $custom_post->ID );
                        $return .= '<div class="date"><small>'.((strlen($category)>2)? ' / '.$category:'').'</small></div><div class="price">'.$product->get_price_html().'</div>';
                    }
                }else{
                    $return .= apply_filters('vibeapp_thumb_date','<div class="date"><small>'. get_the_time('F d,Y').''.((strlen($category)>2)? ' / '.$category:'').' / '.get_comments_number( '0', '1', '%' ).' '.__(' Comments','vibebp').'</small></div>',$featured_style);
                }
                if($custom_post->post_type == 'product'){
                    if(function_exists('woocommerce_template_loop_add_to_cart')){
                        ob_start();
                        woocommerce_template_loop_add_to_cart( $custom_post, $product );
                        $return.= ob_get_clean();
                    }
                }else{
                    $return .= apply_filters('vibeapp_thumb_desc','<p class="block_desc">'.vibeapp_custom_types_excerpt($n,$custom_post->ID).'</p>',$featured_style);    
                }
                
                $return .='</div>';
                $return .='</div>';

            break;
        }
        
        return apply_filters('vibeapp_featured_thumbnail_style',$return,$custom_post,$featured_style);
    }
}

/**
* Fetch custom post type excerpt.
*
* @param int $chars   Number of cahracters for excerpt.
* @param int $id   	 Post id.
*/
if(!function_exists('vibeapp_custom_types_excerpt')){

    function vibeapp_custom_types_excerpt($chars=0, $id = NULL) {
        global $post;
      if(!isset($id)) $id=$post->ID;
        $text = get_post($id);
            
        if(strlen($text->post_excerpt) > 10)
                $text = $text->post_excerpt . " ";
            else
                $text = $text->post_content . " ";
            
        $text = strip_tags($text);
            $ellipsis = false;
            $text = strip_shortcodes($text);
        if( strlen($text) > $chars )
            $ellipsis = true;
      

        $text = substr($text,0,intval($chars));
        
        if(function_exists('mb_convert_encoding'))
            $text = mb_convert_encoding((string)$text, 'UTF-8', mb_list_encodings());   

        $latin=preg_match("/\p{Han}+/u", $text);
        if($latin !=1)
        $text = substr($text,0,strrpos($text,' '));

        if( $ellipsis == true && $chars > 1)
            $text = $text . "...";
            
        return $text;
    }
}

/**
* Member Taxonomies.
*/
function vibebp_get_member_tax(){
	return apply_filters('vibebp_member_taxonomy_slugs',['member_tag','bp_member_type']);
}
/**
* Feature Block.
*
* @param int $post_ID Post ID.
* @param int $cols    Width number in fullwidth.
*/
function vibebp_featured_component($post_ID,$cols){


    $custom_post_thumbnail = '';
    $default_image = plugins_url('../assets/images/default.svg',__FILE__);
    if(!in_array($cols,array('big','small','medium','mini','full'))){
        switch($cols){
          case '2':{ $cols = 'big';
          break;}
          case '3':{ $cols = 'medium';
          break;}
          case '4':{ $cols = 'medium';
          break;}
          case '5':{ $cols = 'small';
          break;}
          case '6':{ $cols = 'small';
          break;}  
          default:{ $cols = 'full';
          break;}
        }
    }
    
    if(has_post_thumbnail($post_ID)){
        $custom_post_thumbnail=  '<a href="'.get_permalink().'">'.get_the_post_thumbnail($post_ID,$cols).'</a>';
    }else if(isset($default_image) && $default_image)
            $custom_post_thumbnail='<img src="'.$default_image.'" />';

    return apply_filters('vibebp_featured_component_filter',$custom_post_thumbnail,$post_ID,$cols); 
}

/**
* Fetch Flatpicker Language.
*/
function vibebp_get_flatpickrLang(){
	$lang = get_locale();
	$_lang = $lang;
	if ( strlen( $lang ) > 0 ) {
	 $lang = explode( '_', $lang )[0];
	}
	return apply_filters('vibebp_get_flatpickrLang',$lang,$_lang);
}
/**
* Fetch Upload Mime types supported by WP.
*/
function vibebp_getMimeTypes(){
        return apply_filters('vibebp_upload_mimes_array',array(
            'JPG' => array(
                            'image/jpeg',
                            'image/jpg',
                            'image/jp_',
                            'application/jpg',
                            'application/x-jpg',
                            'image/pjpeg',
                            'image/pipeg',
                            'image/vnd.swiftview-jpeg',
                            'image/x-xbitmap'),
            'GIF' => array(
                            'image/gif',
                            'image/x-xbitmap',
                            'image/gi_'),
            'PNG' => array(
                            'image/png',
                            'application/png',
                            'application/x-png'),
            'DOCX'=> ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'RAR'=> ['application/x-rar'],
            'ZIP' => array(
                            'application/zip',
                            'application/x-zip',
                            'application/x-zip-compressed',
                            'application/x-compress',
                            'application/x-compressed',
                            'multipart/x-zip'),
            'DOC' => array(
                            'application/msword',
                            'application/doc',
                            'application/text',
                            'application/vnd.msword',
                            'application/vnd.ms-word',
                            'application/winword',
                            'application/word',
                            'application/x-msw6',
                            'application/x-msword'),
            'PDF' => array(
                            'application/pdf',
                            'application/x-pdf',
                            'application/acrobat',
                            'applications/vnd.pdf',
                            'text/pdf',
                            'text/x-pdf'),
            'PPT' => array(
                            'application/vnd.ms-powerpoint',
                            'application/mspowerpoint',
                            'application/ms-powerpoint',
                            'application/mspowerpnt',
                            'application/vnd-mspowerpoint',
                            'application/powerpoint',
                            'application/x-powerpoint',
                            'application/x-m'),
            'PPTX'=> ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'PPS' => ['application/vnd.ms-powerpoint'],
            'PPSX'=> ['application/vnd.openxmlformats-officedocument.presentationml.slideshow'],
            'PSD' => array('application/octet-stream',
                            'image/vnd.adobe.photoshop'
                            ),
            'ODT' => array(
                            'application/vnd.oasis.opendocument.text',
                            'application/x-vnd.oasis.opendocument.text'),
            'XLS' => array(
                            'application/vnd.ms-excel',
                            'application/msexcel',
                            'application/x-msexcel',
                            'application/x-ms-excel',
                            'application/vnd.ms-excel',
                            'application/x-excel',
                            'application/x-dos_ms_excel',
                            'application/xls'),
            'XLSX'=> array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                      'application/vnd.ms-excel'),
            'MP3' => array(
                            'audio/mpeg',
                            'audio/x-mpeg',
                            'audio/mp3',
                            'audio/x-mp3',
                            'audio/mpeg3',
                            'audio/x-mpeg3',
                            'audio/mpg',
                            'audio/x-mpg',
                            'audio/x-mpegaudio'),
            'M4A' => array(
                            'audio/mp4a-latm',
                            'audio/m4a',
                            'audio/mp4'),
            'OGG' => array(
                            'audio/ogg',
                            'application/ogg'),
            'WAV' => array(
                            'audio/wav',
                            'audio/x-wav',
                            'audio/wave',
                            'audio/x-pn-wav'),
            'WMA' => ['audio/x-ms-wma'],
            'MP4' => array(
                            'video/mp4v-es',
                            'audio/mp4',
                            'video/mp4'),
            'M4V' => array(
                            'video/mp4',
                            'video/x-m4v'),
            'MOV' => array(
                            'video/quicktime',
                            'video/x-quicktime',
                            'image/mov',
                            'audio/aiff',
                            'audio/x-midi',
                            'audio/x-wav',
                            'video/avi'),
            'WMV' => ['video/x-ms-wmv'],
            'AVI' => array(
                            'video/avi',
                            'video/msvideo',
                            'video/x-msvideo',
                            'image/avi',
                            'video/xmpg2',
                            'application/x-troff-msvideo',
                            'audio/aiff',
                            'audio/avi'),
            'MPG' => array(
                            'video/avi',
                            'video/mpeg',
                            'video/mpg',
                            'video/x-mpg',
                            'video/mpeg2',
                            'application/x-pn-mpg',
                            'video/x-mpeg',
                            'video/x-mpeg2a',
                            'audio/mpeg',
                            'audio/x-mpeg',
                            'image/mpg'),
            'OGV' => ['video/ogg'],
            '3GP' => array(
                            'audio/3gpp',
                            'video/3gpp'),
            '3G2' => array(
                            'video/3gpp2',
                            'audio/3gpp2'),
            'FLV' => ['video/x-flv'],
            'WEBM'=> ['video/webm'],
            'APK' => ['application/vnd.android.package-archive'],
        ));
  
}

/**
* Fetch Mime Types for Videos.
*/
function vibebp_getVideoMimeTypes(){
        return apply_filters('vibebp_upload_video_mimes_array',array(
         
           
            'MP4' => array(
                            'video/mp4v-es',
                            'audio/mp4',
                            'video/mp4'),
           
            'MOV' => array(
                            'video/quicktime',
                            'video/x-quicktime',
                            'image/mov',
                            
                            'video/avi'),
            'WMV' => 'video/x-ms-wmv',
            'AVI' => array(
                            'video/avi',
                            'video/msvideo',
                            'video/x-msvideo',
                            'image/avi',
                            'video/xmpg2',
                            'application/x-troff-msvideo',
                            'audio/aiff',
                            'audio/avi'),
           
           
            
            'FLV' => 'video/x-flv',
            'WEBM'=> 'video/webm',
        ));
  
}
/**
* Fetch client IP address from SERVER.
*/
function vibebp_get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';

    
    return apply_filters('vibebp_get_client_ip',$ipaddress);
}

/**
* Compelte vIcons list.
*/
function vicon_list(){

	$icons = apply_filters('vibebp_repeatable_icons',array(
                'vicon vicon-arrow-up','vicon vicon-arrow-right','vicon vicon-arrow-left','vicon vicon-arrow-down','vicon vicon-arrows-vertical','vicon vicon-arrows-horizontal','vicon vicon-angle-up','vicon vicon-angle-right','vicon vicon-angle-left','vicon vicon-angle-down','vicon vicon-angle-double-up','vicon vicon-angle-double-right','vicon vicon-angle-double-left','vicon vicon-angle-double-down','vicon vicon-move','vicon vicon-fullscreen','vicon vicon-arrow-top-right','vicon vicon-arrow-top-left','vicon vicon-arrow-circle-up','vicon vicon-arrow-circle-right','vicon vicon-arrow-circle-left','vicon vicon-arrow-circle-down','vicon vicon-arrows-corner','vicon vicon-split-v','vicon vicon-split-v-alt','vicon vicon-split-h','vicon vicon-hand-point-up','vicon vicon-hand-point-right','vicon vicon-hand-point-left','vicon vicon-hand-point-down','vicon vicon-back-right','vicon vicon-back-left','vicon vicon-exchange-vertical
','vicon vicon-wand','vicon vicon-save','vicon vicon-save-alt','vicon vicon-direction','vicon vicon-direction-alt','vicon vicon-user','vicon vicon-link','vicon vicon-unlink','vicon vicon-trash','vicon vicon-target','vicon vicon-tag','vicon vicon-desktop','vicon vicon-tablet','vicon vicon-mobile','vicon vicon-email','vicon vicon-star','vicon vicon-spray','vicon vicon-signal','vicon vicon-shopping-cart','vicon vicon-shopping-cart-full','vicon vicon-settings','vicon vicon-search','vicon vicon-zoom-in','vicon vicon-zoom-out','vicon vicon-cut','vicon vicon-ruler','vicon vicon-ruler-alt-2','vicon vicon-ruler-pencil','vicon vicon-ruler-alt','vicon vicon-bookmark','vicon vicon-bookmark-alt','vicon vicon-reload','vicon vicon-plus','vicon vicon-minus','vicon vicon-close','vicon vicon-pin','vicon vicon-pencil','vicon vicon-pencil-alt','vicon vicon-paint-roller','vicon vicon-paint-bucket','vicon vicon-na','vicon vicon-medall','vicon vicon-medall-alt','vicon vicon-marker','vicon vicon-marker-alt','vicon vicon-lock','vicon vicon-unlock','vicon vicon-location-arrow','vicon vicon-layout','vicon vicon-layers','vicon vicon-layers-alt','vicon vicon-key','vicon vicon-image','vicon vicon-heart','vicon vicon-heart-broken','vicon vicon-hand-stop','vicon vicon-hand-open','vicon vicon-hand-drag','vicon vicon-flag','vicon vicon-flag-alt','vicon vicon-flag-alt-2','vicon vicon-eye','vicon vicon-import','vicon vicon-export','vicon vicon-cup','vicon vicon-crown','vicon vicon-comments','vicon vicon-comment','vicon vicon-comment-alt','vicon vicon-thought','vicon vicon-clip','vicon vicon-check','vicon vicon-check-box','vicon vicon-camera','vicon vicon-announcement','vicon vicon-brush','vicon vicon-brush-alt','vicon vicon-palette','vicon vicon-briefcase','vicon vicon-bolt','vicon vicon-bolt-alt','vicon vicon-blackboard','vicon vicon-bag','vicon vicon-world','vicon vicon-wheelchair','vicon vicon-car','vicon vicon-truck','vicon vicon-timer','vicon vicon-ticket','vicon vicon-thumb-up','vicon vicon-thumb-down','vicon vicon-stats-up','vicon vicon-stats-down','vicon vicon-shine','vicon vicon-shift-right','vicon vicon-shift-left','vicon vicon-shift-right-alt','vicon vicon-shift-left-alt','vicon vicon-shield','vicon vicon-notepad','vicon vicon-server','vicon vicon-pulse','vicon vicon-printer','vicon vicon-power-off','vicon vicon-plug','vicon vicon-pie-chart','vicon vicon-panel','vicon vicon-package','vicon vicon-music','vicon vicon-music-alt','vicon vicon-mouse','vicon vicon-mouse-alt','vicon vicon-money','vicon vicon-microphone','vicon vicon-menu','vicon vicon-menu-alt','vicon vicon-map','vicon vicon-map-alt','vicon vicon-location-pin','vicon vicon-light-bulb','vicon vicon-info','vicon vicon-infinite','vicon vicon-id-badge','vicon vicon-hummer','vicon vicon-home','vicon vicon-help','vicon vicon-headphone','vicon vicon-harddrives','vicon vicon-harddrive','vicon vicon-gift','vicon vicon-game','vicon vicon-filter','vicon vicon-files','vicon vicon-file','vicon vicon-zip','vicon vicon-folder','vicon vicon-envelope','vicon vicon-dashboard','vicon vicon-cloud','vicon vicon-cloud-up','vicon vicon-cloud-down','vicon vicon-clipboard','vicon vicon-calendar','vicon vicon-book','vicon vicon-bell','vicon vicon-basketball','vicon vicon-bar-chart','vicon vicon-bar-chart-alt','vicon vicon-archive','vicon vicon-anchor','vicon vicon-alert','vicon vicon-alarm-clock','vicon vicon-agenda','vicon vicon-write','vicon vicon-wallet','vicon vicon-video-clapper','vicon vicon-video-camera','vicon vicon-vector','vicon vicon-support','vicon vicon-stamp','vicon vicon-slice','vicon vicon-shortcode','vicon vicon-receipt','vicon vicon-pin2','vicon vicon-pin-alt','vicon vicon-pencil-alt2','vicon vicon-eraser','vicon vicon-more','vicon vicon-more-alt','vicon vicon-microphone-alt','vicon vicon-magnet','vicon vicon-line-double','vicon vicon-line-dotted','vicon vicon-line-dashed','vicon vicon-ink-pen','vicon vicon-info-alt','vicon vicon-help-alt','vicon vicon-headphone-alt','vicon vicon-gallery','vicon vicon-face-smile','vicon vicon-face-sad','vicon vicon-credit-card','vicon vicon-comments-smiley','vicon vicon-time','vicon vicon-share','vicon vicon-share-alt','vicon vicon-rocket','vicon vicon-new-window','vicon vicon-rss','vicon vicon-rss-alt','vicon vicon-control-stop','vicon vicon-control-shuffle','vicon vicon-control-play','vicon vicon-control-pause','vicon vicon-control-forward','vicon vicon-control-backward','vicon vicon-volume','vicon vicon-control-skip-forward','vicon vicon-control-skip-backward','vicon vicon-control-record','vicon vicon-control-eject','vicon vicon-paragraph','vicon vicon-uppercase','vicon vicon-underline','vicon vicon-text','vicon vicon-Italic','vicon vicon-smallcap','vicon vicon-list','vicon vicon-list-ol','vicon vicon-align-right','vicon vicon-align-left','vicon vicon-align-justify','vicon vicon-align-center','vicon vicon-quote-right','vicon vicon-quote-left','vicon vicon-layout-width-full','vicon vicon-layout-width-default','vicon vicon-layout-width-default-alt','vicon vicon-layout-tab','vicon vicon-layout-tab-window','vicon vicon-layout-tab-v','vicon vicon-layout-tab-min','vicon vicon-layout-slider','vicon vicon-layout-slider-alt','vicon vicon-layout-sidebar-right','vicon vicon-layout-sidebar-none','vicon vicon-layout-sidebar-left','vicon vicon-layout-placeholder','vicon vicon-layout-menu','vicon vicon-layout-menu-v','vicon vicon-layout-menu-separated','vicon vicon-layout-menu-full','vicon vicon-layout-media-right','vicon vicon-layout-media-right-alt','vicon vicon-layout-media-overlay','vicon vicon-layout-media-overlay-alt','vicon vicon-layout-media-overlay-alt-2','vicon vicon-layout-media-left','vicon vicon-layout-media-left-alt','vicon vicon-layout-media-center','vicon vicon-layout-media-center-alt','vicon vicon-layout-list-thumb','vicon vicon-layout-list-thumb-alt','vicon vicon-layout-list-post','vicon vicon-layout-list-large-image','vicon vicon-layout-line-solid','vicon vicon-layout-grid4','vicon vicon-layout-grid3','vicon vicon-layout-grid2','vicon vicon-layout-grid2-thumb','vicon vicon-layout-cta-right','vicon vicon-layout-cta-left','vicon vicon-layout-cta-center','vicon vicon-layout-cta-btn-right','vicon vicon-layout-cta-btn-left','vicon vicon-layout-column4','vicon vicon-layout-column3','vicon vicon-layout-column2','vicon vicon-layout-accordion-separated','vicon vicon-layout-accordion-merged','vicon vicon-layout-accordion-list','vicon vicon-widgetized','vicon vicon-widget','vicon vicon-widget-alt','vicon vicon-view-list','vicon vicon-view-list-alt','vicon vicon-view-grid','vicon vicon-upload','vicon vicon-download','vicon vicon-loop','vicon vicon-layout-sidebar-2','vicon vicon-layout-grid4-alt','vicon vicon-layout-grid3-alt','vicon vicon-layout-grid2-alt','vicon vicon-layout-column4-alt','vicon vicon-layout-column3-alt','vicon vicon-layout-column2-alt'));
	
	return $icons;
}

/**
* Process profile field data.
*
* @param any $value  Value of the profile field to process
* @param array $field   	 BuddyPress field.
* @param int $user_id   	 WP User id.
* @param array $settings   	 Settings for field.
*/
function vibebp_process_profile_field_data($value,$field,$user_id=null,$settings=[]){

	if(empty($field))
		return;
	
	if(is_string($value)){
		$json = json_decode($value,true);
		if (json_last_error() === 0) {
		   $value = $json;
		}
	}
	if(empty($field) || empty($field->type)){
		return $value;
	}

    if($field->type == "upload" && is_array($value)){
		$pattern = '/href="(?:https?:\/\/[^\/]+\/)?(\/[^"]+)"/i';
		$replacement = 'href="$1"';
		$value =  preg_replace($pattern, $replacement, $value[2]);
    }

	if($field->type == 'social'){
		$newvalue = '';
		$newvalue .='<div class="social_icons">';
		if(!empty($value) && is_Array($value)){
			foreach($value as $social_icon){

				if(is_array($social_icon) && !empty($social_icon['url']) && !empty($social_icon['icon']) ){
					$newvalue .='<a href="'.$social_icon['url'].'" target="_blank" style="'.(empty($settings['text_color'])?'':'color:'.$settings['text_color'].';').(empty($settings['font_size'])?'':'font-size:'.$settings['font_size']['size'].$settings['font_size']['unit']).'"><span class="'.$social_icon['icon'].'"></span></a>';	
				}
				
			}
		}
		
		$newvalue .='</div>';
		$value = $newvalue;
	}
	if($field->type == 'gallery'){
	}
	if($field->type == 'repeatable'){
		$newvalue = '';
		$type = bp_xprofile_get_meta( $field->id, 'field', 'vibebp_repeatable_type', true );
		$style = bp_xprofile_get_meta( $field->id, 'field', 'vibebp_repeatable_style', true );


		$newvalue .='<div class="repeatable_icons '.$type.' '.$style.'">';
		if($type == 'list'){
			$newvalue .='<ul>';
		}
		if(!empty($value) && is_Array($value)){
			foreach($value as $social_icon){

				if($type == 'list'){
					$newvalue .='<li class="repeatable_icon ">';
				}else{
					$newvalue .='<div class="repeatable_icon ">';
				}
				

				if($type == 'stacked' || $type == 'spaced'){
					$newvalue .='<span>'.(strlen($social_icon['icon']) > 100?stripslashes($social_icon['icon']):'<span class="'.$social_icon['icon'].'"></span>').'</span>';	
				}
				

				$newvalue .='<div class="repeatable_icon_data">';

				if($type != 'desc'){
					$newvalue .='<h4>'.$social_icon['title'].'</h4>';	
				}
				
				$newvalue .='<p>'.$social_icon['description'].'</p>';

				if($type == 'list'){
					$newvalue .='</li>';
				}else{
					$newvalue .='</div>';
				}
				
				$newvalue .='</div>';
			}
		}
		
		$newvalue .='</div>';
		$value = $newvalue;
	}
	if($field->type == 'color'){
		$value = '<style>.'.sanitize_title($field->name).'_color,.'.sanitize_title($field->name).'_color .elementor-heading-title{color:'.$value.' !important;}.'.sanitize_title($field->name).'_background,.'.sanitize_title($field->name).'_background .elementor-element-populated{background-color:'.$value.' !important;}</style>';
	} 
	if($field->type == 'table' ){

        $table = bp_xprofile_get_meta( $field->id, 'field', 'vibebp_profile_field_table', true );
        if(is_serialized($value)){
            $value = unserialize(stripcslashes($value));
        }
        if(is_serialized($table)){
            $table = unserialize(stripcslashes($table));
        }
        ob_start();
        if(!empty($table) && !empty($table['columns'])){
            ?>
            <table class="vibebpProfile_table">
                <?php
                if(is_array($table['rows']))
                foreach($table['rows'] as $row){
                	 echo '<tr>';
                	 echo '<th></th>';
                	foreach($table['columns'] as $column){
                        echo '<td>'.$column['name'].'</td>';
                    }
            	 	echo '</tr>';
                	break;
                }
                if(is_array($table['rows']))
                foreach($table['rows'] as $row){
                    echo '<tr>';
                     echo '<td>'.$row['name'].'</td>';
                     if(is_array($table['columns']))
                    foreach($table['columns'] as $column){
                        $v = '';
                        if(is_array($value))
                        foreach($value as $val){
                            if($val['row'] == $row['id'] && $val['column'] == $column['id']){
                            	$ctype ='';
                            	foreach($table['values'] as $k){
                            		if($k['row'] == $row['id'] && $k['column'] == $column['id']){
                            			$ctype = $k['type'];
                            		}
                            	}
                            	if($ctype == 'range' ){
                            		$v= '<div class="progressbar_wrapper"><div class="progressbar"><span class="bar" style="width:'.$val['value'].'%" title="'.$val['value'].'%"></span></div><span>'.$val['value'].'</span></div>';
                            	}else if($ctype == 'points' ){
                            		$v = '<div class="points_wrap">';
                            		for($i=0;$i<5;$i++){
                            			if($i <= $val['value']){
                            				$v .='<span class="point"></span>';
                            			}else{
                            				$v .='<span></span>';
                            			}
                            		}
                            		$v .='</div>';
                            	}else{
                            		$v = $val['value'];	
                            	}
                                
                            }
                        }
                        echo '<td>'.$v.'</td>';
                    }
                    echo '</tr>';
                }
                ?>
            </table>
            <?php
        }
        $value = ob_get_clean();
    }


	if(is_array($value)){

		$newvalue = '';
		foreach($value as $key=>$val){
			if(is_array($val)){
				foreach($val as $k=>$v){
					$newvalue .='<div class="'.$k.'">'.$v.'</div>';	
				}
			}else{
				$newvalue .='<div class="'.$key.'">'.$val.'</div>';	
			}
		}
		$value = $newvalue;
	}

	return apply_filters('vibebp_profile_block_field_value',$value,$field,$user_id);
}


/**
* Fetch all reactions for activities.
*/
function vibebp_get_reactions_array(){
	return array(
        'like'=>array('label'=>_x('Like','activity reactions','vibebp'),'icon'=>plugins_url('../assets/images/reactions/like.svg',__FILE__)),
        'sad'=>array('label'=>_x('Sad','activity reactions','vibebp'),'icon'=>plugins_url('../assets/images/reactions/sad.svg',__FILE__)),
        'wow'=>array('label'=>_x('Wow','activity reactions','vibebp'),'icon'=>plugins_url('../assets/images/reactions/wow.svg',__FILE__)),
        'haha'=>array('label'=>_x('Haha','activity reactions','vibebp'),'icon'=>plugins_url('../assets/images/reactions/haha.svg',__FILE__)),
        'love'=>array('label'=>_x('Love','activity reactions','vibebp'),'icon'=>plugins_url('../assets/images/reactions/love.svg',__FILE__)),
        'angry'=>array('label'=>_x('Angry','activity reactions','vibebp'),'icon'=>plugins_url('../assets/images/reactions/angry.svg',__FILE__)),
        'care'=>array('label'=>_x('Care','activity reactions','vibebp'),'icon'=>plugins_url('../assets/images/reactions/care.svg',__FILE__)),
    );
}
/**
* Fetch all featured blocks for Gutenberg block.
*/
function vibebp_get_featured_blocks($type){

	$blocks=[];
	switch($type){
		case 'members':
			$blocks = [
				'member_block' => 'Member block'
			];
		break;
		case 'groups':
			$blocks = ['group_block' => 'Group block'];
		break;
		case 'post_type':
			$blocks = ['post_block' => 'Post block'];
		break; 
		case 'taxonomy':
			$blocks = ['term_block' => 'Term block'];
		break;
		case 'slides':
			$blocks = ['slide' => 'Default',
	                'icon_slide' => 'Image as Icon on top',
	                'icon_left_slide' => 'Image as Icon on left',
	                'testimonial_slide' => 'Testimonial block'];
		break;
	}

	return apply_filters('vibebp_featured_'.$type.'_styles_options',$blocks);
}



/**
* Process featured block.
*
* @param WP_POST $post  	WP Post.
* @param string $style   	Selected style.
* @param string $swiper_id  ID of the swiper carousel on page.
*/
function vibebp_featured_style($post,$style,$swiper_id=null){
	

	$key= $style.'_'.(empty($post->term_id)?(empty($post->id)?rand(0,99999):$post->id):$post->term_id);
	
    $result = wp_cache_get($key,'featured_block');
    $cache_duration = apply_filters('vibebp_featured_style_cache_duration',3600);

    if(is_array($post)){ // Slide
    	$cache_duration=0;
    }
    if($result === false && !empty($cache_duration)){
    	ob_start();
    }else{
    	echo $result;
    	return;
    }
    	
	switch($style){
		case 'member_block':
			$avatar = bp_core_fetch_avatar(array(
                'item_id' 	=> $post->id,
                'object'  	=> 'user',
                'type'		=>'thumb',
                'html'    	=> false
            ));
			$link = bp_core_get_user_domain($post->id);
			$member_type = bp_get_member_type($post->id);
			$name = bp_core_get_user_displayname($post->id);
			$types = bp_get_member_types(array(),'objects');

			$cover_url = bp_attachments_get_attachment(
				'url',
				array(
					'item_id' => $post->id,
				)
			);

			if(empty($avatar)){

				$avatar = plugins_url('../assets/images/avatar.jpg',__FILE__);
			}

			if(empty($cover_url)){
				$cover_url = plugins_url('../assets/images/cover.jpg',__FILE__);
			}

			?>
			<div class="member_featured_block_wrapper default_member_block member_type_<?php echo $member_type.' member_'.$post->id; ?>" >
				<a href="<?php echo $link;?>">
					<div class="member_background" style="background-image:url(<?php echo $cover_url; ?>)">
						<img src="<?php echo $avatar; ?>" class="member_avatar" />
					</div>
				</a>
				<a href="<?php echo $link;?>"><strong><?php echo esc_attr( $name ); ?></strong></a>
				<?php echo empty($types[$member_type])?'':'<span>'.$types[$member_type]->labels['singular_name'].'</span>'; ?>
				</a>
			</div>
			<?php

		break;
		case 'term_block':
			?>
			<div class="term_featured_block_wrapper default_term_block <?php echo $post->post_type.' '.$post->taxonomy.'_'.$post->slug; ?>" >
				<a href="<?php echo get_term_link($post,$post->taxonomy);?>"><strong><?php echo esc_attr( $post->name ); ?></strong>
				<span><?php echo esc_attr( $post->count ); ?></span>
				</a>
			</div>
		<?php
		break;
		case 'post_block':
			?>
			<div class="post_featured_block_wrapper default_post_block <?php echo $post->post_type.' '.$post->post_type.'_'.$post->ID; ?>" >
				<a href="<?php echo get_permalink($post->ID); ?>" title="<?php the_title_attribute(['post'=>$post]); ?>">
					<?php echo get_the_post_thumbnail($post); ?>
				</a>
				<a href="<?php echo get_permalink($post->ID); ?>">
					<h3><?php echo $post->post_title; ?></h3>
				</a>
				<div class="post_description">
					<?php echo esc_html(get_the_excerpt($post->ID)); ?>
					<a href="<?php echo get_permalink($post->ID); ?>" title="<?php the_title_attribute(['post'=>$post]); ?>" class="read_more_link"><?php _ex('READ MORE','read more','vibebp'); ?><span class="vicon <?php echo is_rtl()?'vicon-arrow-left':'vicon-arrow-right'; ?>"></span></a>
				</div>
				<div class="post_block_meta">
					<span><?php echo get_the_time(get_option('date_format'), $post->ID); ?></span>
					<span><?php the_author($post->ID); ?></span>
				</div>				
			</div>
			<?php
		break;
		case 'group_block':
			$avatar = bp_core_fetch_avatar(array(
                'item_id' 	=> $post->id,
                'object'  	=> 'group',
                'type'		=>'thumb',
                'html'    	=> false
            ));
			$link = bp_get_group_permalink($post->id);
			$group_type = bp_groups_get_group_type($post->id);
			$name = bp_get_group_name($post->id);
			$types = bp_groups_get_group_types(array(),'objects');

			$cover_url = bp_attachments_get_attachment(
				'url',
				array(
					'item_id' => $post->id,
				)
			);

			if(empty($avatar)){
				$avatar = plugins_url('../assets/images/avatar.jpg',__FILE__);
			}

			if(empty($cover_url)){
				$cover_url = plugins_url('../assets/images/cover.jpg',__FILE__);
			}
		?>
		</div>
		<?php
		break;
		case 'icon_left_slide':
			?>
			<div class="slide_block_wrapper <?php echo $style; ?>_style" >
				
			<?php
				if(!empty($post['slide_image']) && is_numeric($post['slide_image']['id']) && !empty($post['slide_image']['url'])){
					?><img src="<?php echo $post['slide_image']['url'] ?>" /><?php
				}
			?>
			<div class="slide_content">
			<?php
				if(!empty($post['slide_content'])){
					echo $post['slide_content']; 
				}
			?>
			</div>
			</div>
			<?php
		break;
		case 'testimonial_slide':
			?>
			<div class="slide_block_wrapper <?php echo $style; ?>_style">			
			<?php
				if(!empty($post['slide_content'])){
					echo $post['slide_content']; 
				}
			?>
			<?php
				if(!empty($post['slide_image']) && !empty($post['slide_image']['url'])){
					?><img src="<?php echo $post['slide_image']['url'] ?>" /><?php
				}
			?>
			</div>
			<?php
		break;
		default;
			do_action('vibebp_featured_style',$post,$style,$swiper_id);
		break;
	}		

	if($result === false && !empty($cache_duration)){
    	$result = ob_get_clean();
    	wp_cache_set( $key,$result,'featured_block',$cache_duration);
    	echo $result;
    }

}

/**
* Get Activity reactions form a user.
* @param int $activity_id   ID of the activtiy.
* @param int $user_id  		ID of the user
*/

function vibebp_get_user_activity_reaction($activity_id,$user_id){
	$reaction = null;
	global $wpdb,$bp;
	$kk = 'reaction_%';
	$query = "
        SELECT meta_key
        FROM {$bp->activity->table_name_meta} 
        WHERE activity_id = {$activity_id}
        AND  meta_value = {$user_id}
		AND meta_key LIKE '{$kk}'";
    $v = $wpdb->get_var( $query );
    if(!empty($v)){
    	$reaction = str_replace('reaction_', '', $v);
    }
	return $reaction;
}
/**
* Get Activity reactions by all users.
* @param int $activity_id   ID of the activtiy.
*/
function vibebp_get_activity_reactions($activity_id){
	$reactions = [];
	global $wpdb,$bp;
	$kk = 'reaction_%';
	$query = "
        SELECT meta_key,meta_value
        FROM {$bp->activity->table_name_meta} 
        WHERE activity_id = {$activity_id}
		AND meta_key LIKE '{$kk}'";
    $results = $wpdb->get_results( $query );
    if(!empty($results)){
    	foreach ($results as $key => $rr) {
    		if(empty($reactions[str_replace('reaction_', '', $rr->meta_key)])){
    			$reactions[str_replace('reaction_', '', $rr->meta_key)]=[];
    		}
    		$reactions[str_replace('reaction_', '', $rr->meta_key)][] = $rr->meta_value;
    	}
    }
	return $reactions;
}

/**
* Fetch All Profile fields.
*/
function vibebp_get_all_member_type_profile_fields(){
	$mtfields = [];
	if(!function_exists('bp_xprofile_get_groups')){
		return $mtfields;
	}
	global $wpdb,$bp;
	$signup_fields = bp_xprofile_get_signup_field_ids();
	$fields=[];
	if(!empty($signup_fields)){

		
		foreach($signup_fields as $k=>$field_id){
			$field = xprofile_get_field( $field_id );
			if(in_array($field->type,array('upload','gallery','video'))){
				continue;
			}

			if(!in_array($field->type,array('option'))){
				$details = array(
					'id'=>$field->id,
					'name'=>$field->name,
					'description'=>$field->description,
					'type'=>$field->type,
					'position'=>$k,
					'required'=>$field->is_required,
				);
				//$details = array_merge($details,(array)$field);
				if($field->type == 'datebox'){
					$details['date_format'] = bp_xprofile_get_meta($field->id,'field','date_format',true);
				}

				if($field->type == 'upload'){
					$details['upload_size'] = (int)bp_xprofile_get_meta($field->id,'field','vibebp_upload_size',true);
					$details['upload_types'] = bp_xprofile_get_meta($field->id,'field','vibebp_upload_types',true);
					$details['all_upload_types']  = vibebp_getMimeTypes();
					
				}
				if($field->type == 'checkbox_acceptance'){
					$page = (int)bp_xprofile_get_meta($field->id,'field','bp_xprofile_checkbox_acceptance_page',true);
					if(!empty($page))
					$details['name'] .= ' <a href="'.get_permalink($page).'">'.get_the_title($page).'</a>';
					
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
			
				$fields[]= apply_filters('vibebp_get_allxprofile_fields_field_details',$details);
			}
		}
	}
	    		

	$member_types_objects = bp_get_member_types( array(), 'objects' );

	if(!empty($member_types_objects)){

		foreach($member_types_objects as $member_type => $member_type_object){
			$mtfields[$member_type]['fields'] = [];
			
			$mtfields[$member_type]['label'] = (!empty($member_type_object->labels['singular_name'])?$member_type_object->labels['singular_name']:$member_type_object->labels['name']);
		}
		
		if(!empty($signup_fields)){
			foreach($signup_fields as $k=>$field_id){
				$mtypes = bp_xprofile_get_meta( $field_id, 'field', 'member_type',false );
				if(empty($mtypes)){
					foreach($member_types_objects as $member_type=>$obj){
						if(!empty($fields[$k])){
							$mtfields[$member_type]['fields'][]=$fields[$k];		
						}
					}
				}else{
					foreach($mtypes as $member_type){
						if(!empty($fields[$k])){
							if($member_type=='null'){
								$member_type=0;
								$mtfields[$member_type]['label'] = _x('Default User','','vibebp');
							}
							$mtfields[$member_type]['fields'][]=$fields[$k];	
						}
					}
				}
			}
		}
	}

	return apply_filters('vibebp_get_all_member_type_profile_fields',$mtfields);
}

/**
* Fetch all Group statuses.
*/
function vibebp_get_group_statuses(){
	return apply_filters('vibebp_get_group_statuses',[
	'public'=>_x('Public','group status','vibebp'),
	'private'=>_x('Private','group status','vibebp'),
	'hidden'=>_x('Hidden','group status','vibebp'),
	]);
}
/**
* Fetch all Google Playstore products defined in the App.
*/
function get_vibebp_google_products_config(){
	$products = vibebp_get_setting('google_play_app_products','wallet');
	if(empty($products)){
		$products = [];
	}
	$return = [];
	foreach ($products as $key => $product) {
		$return[$product['product']] = intval($product['credits']);
	}
	return apply_filters('get_vibebp_google_products_config',$return);
}
/**
* Generate carousel Shortcode
* @param array $settings   Settings array.
*/
function vibebp_generate_carousel_shortcode($settings){
		$args = [];

		$flag=0;
		if(empty($settings['carousel_type'])){
			$settings['carousel_type']='';
		}
		$featured_style='';
		switch($settings['carousel_type']){
			case 'members':
				$featured_style = $settings['member_featured_style'];
				$args = [
					'type'                => 'active',
					'per_page'            => (empty($settings['carousel_number'])?10:$settings['carousel_number']),
					'page'                => 1, 
					'populate_xtras'     => true,
				];
				if(!empty($settings['include_member_types'])){
					$args['member_type__in']=explode(',',$settings['include_member_types']);
				}
				if(!empty($settings['exclude_member_types'])){
					$args['member_type__not_in']=explode(',',$settings['exclude_member_types']);
				}
				if(!empty($settings['include_member_ids'])){
					$args['include']=explode(',',$settings['include_member_ids']);
				}
				if(!empty($settings['exclude_member_ids'])){
					$args['exclude']=explode(',',$settings['exclude_member_ids']);
				}

				if(!empty($settings['member_order'])){
					$args['type'] = $settings['member_order'];
				}

			break;
			case 'groups':
				$args = array(
					'order'              => 'DESC',         // 'ASC' or 'DESC'
					'orderby'            => 'date_created', 
					'per_page'           => empty($setings['carousel_number'])?10:$setings['carousel_number'],
					'page'               => 1,  
					'fields'             => 'all',
				);
				$featured_style = $settings['group_featured_style'];
				if(!empty($settings['include_group_types'])){
					$args['group_type__in']=explode(',',$settings['include_group_types']);
				}
				if(!empty($settings['exclude_group_types'])){
					$args['group_type__not_in']=explode(',',$settings['exclude_group_types']);
				}
				if(!empty($settings['include_group_ids'])){
					$args['include']=explode(',',$settings['include_group_ids']);
				}
				if(!empty($settings['exclude_group_ids'])){
					$args['exclude']=explode(',',$settings['exclude_group_ids']);
				}

				if(!empty($settings['group_order'])){
					$args['type'] = $settings['group_order'];
				}
			break;
			case 'taxonomy':

				if(empty($settings['taxonomy'])){
					$flag =1;
					
				}else{
					$featured_style = $settings['taxonomy_featured_style'];
					$args=[
						'taxonomy' => $settings['taxonomy'],
    					'hide_empty' => false,
    					'number'=>empty($setings['carousel_number'])?10:$setings['carousel_number'],
					];

					if(!empty($settings['only_parents'])){
						$args['parent']=0;
					}

					if(!empty($settings['parent_term']) && is_numeric($settings['parent_term'])){
						$args['parent']=$settings['parent_term'];
					}

					if(!empty($settings['include_terms'])){
						$args['slug']=explode(',',$settings['include_terms']);
					}
					if(!empty($settings['exclude_terms'])){
						$args['exclude']=explode(',',$settings['exclude_terms']);;
					}

					$featured_style = $settings['taxonomy_featured_style'];
					
					if(!empty($settings['term_order'])){
						$args['type'] = $settings['term_order'];
					}
				}

			break;
			case 'post_type':

				if(empty($settings['post_type'])){
					$flag =1;
				}else{
					$featured_style = $settings['post_type_featured_style'];
					$args = [
						'post_type'=>$settings['post_type'],
						'posts_per_page'=>empty($setings['carousel_number'])?10:$settings['carousel_number'],
					];

					if(!empty($settings['include_post_ids'])){
						$args['post__in']=explode(',',$settings['include_post_ids']);
					}

					if(!empty($settings['post__in']) && is_array($settings['post__in'])){
						if(!is_numeric($settings['post__in'][0]) && !empty($settings['post__in'][0]['value'])){
							$settings['post__in'] = wp_list_pluck($settings['post__in'],'value');
						}
						$args['post__in']=$settings['post__in'];
					}

					if(!empty($settings['exclude_post_ids'])){
						$args['post_not__in']=explode(',',$settings['exclude_post_ids']);
					}

					$tax_query = [];
					if(!empty($settings['post_type_include_terms'])){
						$tax_query['relation']='AND';
						$tax_query[]=[
								'taxonomy'=>$settings['post_type_taxonomy'],
								'field'=>'slug',
								'terms'=>explode(',',$settings['post_type_include_terms'])
							];
					}
					if(!empty($settings['post_type_exclude_terms'])){
						$tax_query['relation']='AND';
						$tax_query[]=[
								'taxonomy'=>$settings['post_type_taxonomy'],
								'field'=>'slug',
								'terms'=>explode(',',$settings['post_type_exclude_terms'])
							];
					}

					if(!empty($tax_query)){
						$args['tax_query'] = $tax_query;	
					}
					
				}				
			break;
			case 'slides':
				$featured_style = $settings['slides_featured_style'];
				$flag=0;
			break;
		}

		$shortcode='';
		if(empty($flag)){
		
			$shortcode = "[vibebp_carousel 
			id='".(empty($settings['carousel_id'])?'car_'.rand(0,999):$settings['carousel_id'])."'
			carousel_style='".(empty($settings['carousel_style'])?'default':$settings['carousel_style'])."'
		    show_controls='".(empty($settings['show_controls'])?'':$settings['show_controls'])."'  
		    effect='".(empty($settings['effect'])?'1':$settings['effect'])."' 
		    rows='".(empty($settings['rows'])?'1':$settings['rows'])."'
		    show_controlnav='".(empty($settings['show_controlnav'])?'':$settings['show_controlnav'])."'  
		    scrollbar='".(empty($settings['scrollbar'])?'':$settings['scrollbar'])."' 
		    type='".(empty($settings['carousel_type'])?'':$settings['carousel_type'])."'  
		    args='".(empty($args)?'':base64_encode(json_encode($args)))."' 
		    featured_style='".$featured_style."' 
		    space='".(empty($settings['carousel_space'])?'':$settings['carousel_space'])."'
		    columns='".(empty($settings['columns'])?4:$settings['columns'])."' 
		    vertical='".(empty($settings['vertical'])?'':$settings['vertical'])."' 
		    starting_slide='".(empty($settings['starting_slide'])?0:$settings['starting_slide'])."' 
		    auto_slide='".(isset($settings['auto_slide'])?$settings['auto_slide']:'')."'  
		    slides='".(empty($settings['slides'])?'':base64_encode(json_encode($settings['slides'])))."' 
		    extras='".(empty($settings['extras'])?'':$settings['extras'])."'  
		    ][/vibebp_carousel]";
		}
		
		return $shortcode;
}

/**
* Get all group fields.
* @param int $groupd_id   ID of the group.
*/
function vibebp_get_groups_meta_fields_array($group_id=null){

	$fields = vibebp_get_setting('group_custom_fields','bp','groups');
	$group_fields = [];
	if(!empty($fields)){
		

		foreach($fields['type'] as $k=>$v){
			$f=[
				'label'=> $fields['label'][$k],
			 	'text'=>'',
			 	'type'=> $fields['type'][$k],
			 	'id' => $fields['key'][$k],
			 	'from'=> 'meta',
			 	'desc'=> $fields['desc'][$k],
			];

			if($v == 'yesno'){
				$f['options'] = array(
				  array('value' => 'H',
						'label' =>__('Hide','vibebp')),
				  array('value' => 'S',
						'label' =>__('Show','vibebp')),
				);
			}

			if($v == 'product'){
				$f['fields']=[
					array(
	                    'label'=> __('Price','vibebp' ),
	                    'type'=> 'text',
	                    'style'=>'',
	                    'id' => '_regular_price',
	                    'from'=> 'meta',
	                    'cpt'=>'product',
	                    'default'=>'',
	                    'desc'=> __('Price of Group.','vibebp' ),
	                ),
				];
			}

			if($v == 'select' || $v == 'checkbox' || $v == 'radio'){
				$o = explode('|',$fields['options'][$k]);
				if(!empty($o)){
					$options =[];
					foreach($o as $i=>$p){
						$c = explode(';',$p);
						if(!empty($c) && is_array($c) && count($c)> 1){
							$options[]=[
								'value'=>$c[0],
								'label'=>$c[1]
							];
						}
						
					}
				}
				$f['options'] = $options;
			}

			$group_fields[]=$f;
		}
	}

	if(function_exists('vibe_helpdesk_translations')){
		global $wpdb;
		$forums = $wpdb->get_results("SELECT ID as value, post_title as label FROM {$wpdb->posts} WHERE post_type = 'forum' AND post_status != 'trash'");

		$base=[['value'=>'','label'=>__('Select a forum','vibebp')],['value'=>'new','label'=>__('Create new forum','vibebp')]];
		$forums = array_merge($base,$forums);
		$group_fields[]=[
				'label'=> __('Group forum','vibebp'),
			 	'text'=>'',
			 	'type'=> 'select',
			 	'options'=>$forums,
			 	'id' => 'forum_id',
			 	'from'=> 'meta',
			 	'desc'=> __('Select a group forum','vibebp'),
			];
	}
	return $group_fields;   
}

/**
* Get group field / meta value.
* @param array $field   Field defined in groups.
* @param int $groupd_id	ID of the group
*/

function vibebp_groups_get_groupmeta_value($field,$group_id){
	$value = groups_get_groupmeta($group_id,$field['id'],true);

	switch($field['type']){
		case 'editor':
			$value=['value'=>groups_get_groupmeta($group_id,$field['id'],true),'raw'=>groups_get_groupmeta($group_id,$field['id'].'_raw',true)];
		break;
		default:
		
		break;
	}
	return $value;
}

/**
* Process editor script.
*/

function vibebp_editor_content_script(){


	?>
	
	document.addEventListener('VibeBP_Editor_Content', function(){ 
               
                setTimeout(function(){

                    document.querySelectorAll('pre').forEach(async function(el){
                        if(typeof Prism == 'object'){
                            await Prism.highlightAllUnder(el);
                        }
                    });

                    <?php 
                if(vibebp_get_setting('editor_interface','general','editor') && vibebp_get_setting('editor_interface','general','editor') != 'basic'){ ?>
                    document.querySelectorAll('.mathjax').forEach(async function(el){
                        
                        el.classList.remove('mathjax');

                        var scriptMath = document.createElement("script");
                        scriptMath.setAttribute("type","math/tex");                        
                        scriptMath.appendChild(document.createTextNode(el.getAttribute('tex')));
                        el.appendChild(scriptMath);
                        if(typeof MathJax != 'undefined'){
                           await MathJax.Hub.Queue(['Typeset', MathJax.Hub, el[0]]);
                        }
                    });
                <?php } ?>

                	const youtube_parser = (url) => {
				        var regExp = /(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&|?]+)/;
				        var match = url.match(regExp);
				        return (match && match.length) ? match[1] : false;
				    }
                    document.querySelectorAll('.video_plyr').forEach(async function(el){

                    	if(el.node != 'video'){
                    		if(el.querySelector('video')){
                    			el = el.querySelector('video');
                    		}
                    	}

                        if(typeof Plyr == 'function'){
                            
                            let src = el.getAttribute('src');
                            if(!src){
                            	src = el.getAttribute('data-plyr-embed-id');
                            }

                     
                            if(src && src.split('.').pop() == 'mpd' && typeof dash != 'undefined'){
                                var dash = dashjs.MediaPlayer().create();
                                dash.initialize(el, src, true);
                            }
                            
                            let pplayer = new Plyr(el);
                            let ycode = youtube_parser(src);
                            if(ycode){
                            	pplayer.source = {
								  type: 'video',
								  sources: [
								    {
								      src: ycode,
								      provider: 'youtube',
								    },
								  ],
								};
                            }
                            

                            if(src && src.split('.').pop() == 'm3u8'){
                                if (Hls.isSupported() && typeof Hls != 'undefined') {
                                    const hls = new Hls();
                                    hls.loadSource(src);
                                    hls.attachMedia(el);
                                }  
                            }
                        }
                    });

                    document.querySelectorAll('.vibebp_tabs_wrapper').forEach((tabs)=>{


                        tabs.querySelector(".vibebp_tab_titles > label").classList.add('active');
                         tabs.querySelector('.'+document.querySelector(".vibebp_tab_titles > label").getAttribute('for')).classList.add('show');

                        tabs.querySelectorAll(".vibebp_tab_titles > label").forEach(function(el){
                            el.addEventListener('click',function(){
                                tabs.querySelector(".vibebp_tab_titles > label.active").classList.remove('active');
                                el.classList.add('active');
                                tabs.querySelector('.vibebp_tab_content.show').classList.remove('show');
                                tabs.querySelector('.'+el.getAttribute('for')).classList.add('show');
                            })
                        });
                    });
                    
                    <?php 
                if(vibebp_get_setting('editor_interface','general','editor') && vibebp_get_setting('editor_interface','general','editor') != 'basic'){ ?>
                    //SCRATCH CARD START
                    document.querySelectorAll(".scratcardContainer>canvas.scratchcard").forEach((scratchcard)=>{
                        let scratchcardCanvas = scratchcard.getContext('2d');
                        let img = new Image();
                        let brushRadius = 20;
                        
                        let imageObject = pathNameGetFromImage(scratchcard.getAttribute('front-image'));
                        img.loc = imageObject.path;
                        img.filename = imageObject.name;
                        img.src = img.loc + img.filename;
                        img.onload = function(){  
                            console.log('image laoded');
                            scratchcardCanvas.drawImage(img, 0, 0, scratchcard.width, scratchcard.height);
                            scratchcard.style.display = "block";
                        }

                        function detectLeftButton(event) {
                            if ('buttons' in event) {
                                return event.buttons === 1;
                            } else if ('which' in event) {
                                return event.which === 1;
                            } else {
                                return event.button === 1;
                            }
                        }

                        function getBrushPos(xRef, yRef) {
                            var scratchcardRect = scratchcard.getBoundingClientRect();
                            return {
                            x: Math.floor((xRef-scratchcardRect.left)/(scratchcardRect.right-scratchcardRect.left)*scratchcard.width),
                            y: Math.floor((yRef-scratchcardRect.top)/(scratchcardRect.bottom-scratchcardRect.top)*scratchcard.height)
                            };
                        }
                            
                        function drawDot(mouseX,mouseY){
                            scratchcardCanvas.beginPath();
                            scratchcardCanvas.arc(mouseX, mouseY, brushRadius, 0, 2*Math.PI, true);
                            scratchcardCanvas.fillStyle = '#000';
                            scratchcardCanvas.globalCompositeOperation = "destination-out";
                            scratchcardCanvas.fill();
                        }

                        scratchcard.addEventListener("mousemove", function(e) {
                            var brushPos = getBrushPos(e.clientX, e.clientY);
                            var leftBut = detectLeftButton(e);
                            if (leftBut == 1) {
                                    drawDot(brushPos.x, brushPos.y);
                            }
                        }, false);

                        scratchcard.addEventListener("touchmove", function(e) {
                            e.preventDefault();
                            var touch = e.targetTouches[0];
                            if (touch) {
                            var brushPos = getBrushPos(touch.pageX, touch.pageY);
                                drawDot(brushPos.x, brushPos.y);
                            }
                        }, false);
                    });
                    function pathNameGetFromImage(str){
                        const regex = /[^/\\&\?]+\.\w{3,4}(?=([\?&].*$|$))/g;
                        let m;
                        let ImageObj = {
                            'path':'',
                            'name':''
                        }
                        while ((m = regex.exec(str)) !== null) {
                            if (m.index === regex.lastIndex) {
                                regex.lastIndex++;
                            }
                            m.forEach((match, groupIndex) => {
                                if(groupIndex == 0 && match.length){
                                    ImageObj.path = str.split(match)[0];
                                    ImageObj.name = match;
                                }
                            });
                        }
                        return ImageObj;
                    }
                    //SCRATCH CARD END

                    //Image Revealer Start
                    document.querySelectorAll('.cocoen').forEach(function(element){
                        new Cocoen(element);
                    });
                    //Image Revealer End

                    //Memory Game Start
                    class MemoryGame {
                        constructor(jsCard) {
                            this.solved = false;
                            this.duration = 1000;
                            this.cardsContainer = jsCard;
                            this.cards = Array.from(this.cardsContainer.children);
                        }
                        shuffleCards() {
                            this.cards.forEach(card => {
                                const randomNumber = Math.floor(Math.random() * this.cards.length) + 1;

                                card.classList.remove('has-match');

                                setTimeout(() => {
                                    card.style.order = `${randomNumber}`;
                                }, 400);
                            })
                        }
                        checkAllCards() {
                            if (!this.cards.every(card => card.classList.contains('has-match'))) {
                                return;
                            }else{
                                if(!this.solved){
                                    this.cardsContainer.insertAdjacentHTML("afterend", "<span class='reload_memory_game vicon vicon-reload'></span>");             
                                }
                                this.solved = true;
                                let reloadButton = this.cardsContainer.parentElement.children[1];
                                let $this = this;
                                $this.cardsContainer.style.opacity = 0.5;
                                reloadButton.style.display = "block";
                                reloadButton.addEventListener('click',function(){
                                    $this.shuffleCards();
                                    reloadButton.style.display = "none";
                                    $this.cardsContainer.style.opacity = 1;
                                });
                            }
                            if(!this.solved){
                                setTimeout(() => {
                                    this.shuffleCards();
                                }, this.duration);  
                            }  
                        }
                        stopEvent() {
                            this.cardsContainer.classList.add('no-event');

                            setTimeout(() => {
                                this.cardsContainer.classList.remove('no-event');
                            }, this.duration);
                        }
                        checkIfMatched(firstCard, secondCard) {
                            if (firstCard.dataset.card === secondCard.dataset.card) {
                                firstCard.classList.remove('flipped');
                                secondCard.classList.remove('flipped');

                                firstCard.classList.add('has-match');
                                secondCard.classList.add('has-match');
                                
                                this.checkAllCards();
                            }
                            else {
                                setTimeout(() => {
                                    firstCard.classList.remove('flipped');
                                    secondCard.classList.remove('flipped');
                                }, this.duration);
                            }
                        }
                        flip(selectedCard) {
                            selectedCard.classList.add('flipped');

                            const flippedCards = this.cards.filter(card => card.classList.contains('flipped'));

                            if (flippedCards.length === 2) {
                                this.stopEvent();
                                this.checkIfMatched(flippedCards[0], flippedCards[1]);
                            }
                        }
                    }
                    document.querySelectorAll('.memorygame__cards.js-cards').forEach((jsCard)=>{
                        const memorygame = new MemoryGame(jsCard);
                        memorygame.cards.forEach(card => {
                            card.addEventListener('click', memorygame.flip.bind(memorygame, card));
                        });
                    })
                    //Memory Game End

                    //CardStack start
                    {

                        var clickeventtype = 'click';

                        (function() {
				
                            let types = ['yuda','krisna','wangi','wira','utari','cinta','eka','dian','iman','iskandar','kasih','buana','mawar'];
                           
                            types.map((type)=>{
                                document.querySelectorAll(`.cardstack_${type}`).forEach((elm)=>{
                                    if(elm){
                                        let stackElm = null;
                                        switch (type) {
                                            case 'yuda':
                                            case 'krisna':
                                            case 'wangi':
                                            case 'wira':
                                            case 'utari':
                                                stackElm = new Stack(elm);
                                            break;
                                            case 'cinta':
                                                stackElm = new Stack(elm, {
                                                    perspective: 1400,
                                                    perspectiveOrigin: '200% -100%',
                                                    visible: 6,
                                                    infinite: false,
                                                    onEndStack: function(instance) {
                                                        setTimeout(function() {
                                                            instance.restart();
                                                        }, 300);
                                                    }
                                                });
                                                break;
                                            case 'eka':
                                                stackElm = new Stack(elm, {
                                                    cardstackItemsAnimation : {
                                                        duration: 800, 
                                                        type: dynamics.spring 
                                                    }
                                                });
                                            break;
                                            case 'dian':
                                                stackElm = new Stack(elm, {
                                                    cardstackItemsAnimationDelay : 100,
                                                    cardstackItemsAnimation : {
                                                        duration: 800, 
                                                        type: dynamics.spring,	
                                                    }, 
                                                    visible: 1, 
                                                    perspectiveOrigin: '50% 50%'
                                                });
                                            break;
                                            case 'iman':
                                                stackElm = new Stack(elm, {
                                                    cardstackItemsAnimation : {
                                                        duration: 800, 
                                                        type: dynamics.spring	
                                                    },
                                                    cardstackItemsPreAnimation : {
                                                        accept : {
                                                            elastic: true,
                                                            animationProperties: {translateX : 100, translateY : 10, rotateZ: 5},
                                                            animationSettings: {
                                                                duration: 100,
                                                                type: dynamics.easeIn
                                                            }
                                                        },
                                                        reject : {
                                                            elastic: true,
                                                            animationProperties: {translateX : -100, translateY : 10, rotateZ: -5},
                                                            animationSettings: {
                                                                duration: 100,
                                                                type: dynamics.easeIn
                                                            }
                                                        }
                                                    }
                                                });
                                            break;
                                            case 'iskandar':
                                                stackElm = new Stack(elm, {
                                                        cardstackItemsAnimation : {
                                                        duration: 800, 
                                                        type: dynamics.spring	
                                                    }, 
                                                    perspectiveOrigin : '50% 130%',
                                                    visible: 5,
                                                    cardstackItemsPreAnimation : {
                                                        accept : {
                                                            elastic: true,
                                                            animationProperties: {translateX : 150, translateY : -10},
                                                            animationSettings: {
                                                                duration: 200,
                                                                type: dynamics.easeOut
                                                            }
                                                        }
                                                    }
                                                });
                                            break;
                                            case 'kasih':
                                                stackElm = new Stack(elm, {
                                                    cardstackItemsAnimation : {
                                                        duration: 1300,
                                                        type: dynamics.spring, 
                                                        friction: 420,
                                                    },
                                                    visible: 4,  
                                                    perspectiveOrigin : '50% 50%',
                                                    cardstackItemsPreAnimation : {
                                                        accept : {
                                                            elastic: true,
                                                            animationProperties: {translateX : 250},
                                                            animationSettings: {
                                                                duration: 400,
                                                                type: dynamics.easeIn
                                                            }
                                                        },
                                                        reject : {
                                                            elastic: true,
                                                            animationProperties: {translateX : -250},
                                                            animationSettings: {
                                                                duration: 400,
                                                                type: dynamics.easeIn
                                                            }
                                                        }
                                                    }
                                                });
                                            break;
                                            case 'buana':
                                                stackElm = new Stack(elm, {
                                                    perspectiveOrigin : '-50% -50%'
                                                });
                                            break;
                                            case 'mawar':
                                                stackElm = new Stack(elm, {
                                                    perspective : 1500,
                                                    perspectiveOrigin : '-150% 50%',
                                                    visible: 4,
                                                    cardstackItemsAnimation : {
                                                        duration: 1300,
                                                        type: dynamics.spring
                                                    },
                                                    cardstackItemsPreAnimation : {
                                                        accept : {
                                                            elastic: true,
                                                            animationProperties: {translateX : 100},
                                                            animationSettings: {
                                                                duration: 200,
                                                                type: dynamics.easeIn
                                                            }
                                                        },
                                                        reject : {
                                                            elastic: true,
                                                            animationProperties: {translateX : -100},
                                                            animationSettings: {
                                                                duration: 200,
                                                                type: dynamics.easeIn
                                                            }
                                                        }
                                                    }
                                                });
                                            break;
                                            default:
                                                stackElm = new Stack(elm);
                                                break;
                                        }
                                        if(stackElm){
                                            elm.addEventListener(clickeventtype, function() {   
                                                stackElm.accept(); 
                                            });
                                        }
                                    }
                                })
                            });

                        })();
                    }
                   
                    //CardStack end
                <?php } ?>
                 },500);
            },false);
	<?php
}