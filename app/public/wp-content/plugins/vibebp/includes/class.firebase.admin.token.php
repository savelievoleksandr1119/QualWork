<?php 
/**
 * Initialise plugin
 *
 * @class       Vibebp_Firebase_Tokens
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if(!class_exists('Vibebp_Firebase_Tokens')){
	class Vibebp_Firebase_Tokens{


		public static $instance;
		public static function init(){

	        if ( is_null( self::$instance ) )
	            self::$instance = new Vibebp_Firebase_Tokens();
	        return self::$instance;
	    }

		private function __construct(){
			$this->custom_token = '';
			$this->id_token = '';
		}

		
		function vibebp_firebase_generate_custom_token(){
		    if(!empty($this->custom_token))return $this->custom_token; 
		    $this->custom_token = get_transient('vibebp_firebase_custom_token');

		    if(empty($this->custom_token) && !empty( vibebp_get_setting('firebase_service_email','general','firebase') ) && !empty(vibebp_get_setting('firebase_private_key','general','firebase') )  && !empty(vibebp_get_setting('firebase_UID','general','firebase')) && !empty(vibebp_get_setting('cache_first','service_worker'))){

		        require_once(dirname(__FILE__).'/core/JWT.php');

		        $service_account_email = vibebp_get_setting('firebase_service_email','general','firebase');
		        $private_key = '';
		        $private_key_contents = json_decode(urldecode(vibebp_get_setting('firebase_private_key','general','firebase')));
		        if(!empty( $private_key_contents) && !empty($private_key_contents->private_key)){
		        	$private_key =$private_key_contents->private_key;
		        }
		        if(!empty($private_key)){
		        	$uid = vibebp_get_setting('firebase_UID','general','firebase');
			        $now_seconds = time();
			        $payload = array(
			            "iss" => $service_account_email,
			            "sub" => $service_account_email,
			            "aud" => "https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit",
			            "iat" => $now_seconds,
			            "exp" => $now_seconds+(60*60),  // Maximum expiration time is one hour
			            "uid" => vibebp_get_setting('firebase_UID','general','firebase'),
			            "claims" => array(
			                "https://www.googleapis.com/auth/userinfo.email"=>"",
							"https://www.googleapis.com/auth/firebase.database"=>""
			            )
			        );
			       
			        $this->custom_token = JWT::encode($payload, $private_key, "RS256");
			        set_transient('vibebp_firebase_custom_token',$this->custom_token, 3600);
		        }
		        
		    }
		    if(empty($this->custom_token)){
		    	$this->custom_token = false;
		    	return $this->custom_token;
		    }
		    return $this->custom_token;
		}

		function vibebp_firebase_generate_id_token(){
			if(!empty($this->id_token))return $this->id_token; 
			$this->id_token = get_transient('vibebp_firebase_id_token');
			if(!empty($this->id_token)){
				return $this->id_token;
			}else{
				$custom_token = $this->vibebp_firebase_generate_custom_token();
			    
			    if(!empty($custom_token)){
			    	$response= wp_remote_post('https://identitytoolkit.googleapis.com/v1/accounts:signInWithCustomToken?key='.vibebp_get_setting('firebase_api_key','general','firebase'),
			    		array(
			    		'method' => 'POST',
			    		'timeout'=>30,
			    		'body'=>array(
			    			'token'=>$custom_token,
			    			'returnSecureToken'=>'true'
			    			)
			    		)
			    	);
			    	if(empty($response) || is_wp_error($response)){
			    		$this->id_token =false;
			    		return $this->id_token;
			    	}
			    	$api_response = json_decode( wp_remote_retrieve_body( $response ), true );
			    	set_transient('vibebp_firebase_id_token',$api_response['idToken'],intval($api_response['expiresIn']));
			    	$this->id_token = $api_response['idToken'];
			    	return $this->id_token;
			    }
		    }
		    $this->id_token =false;
		    return $this->id_token;
		}
		

	}
}
