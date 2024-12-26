<?php

defined( 'ABSPATH' ) or die();


if ( ! class_exists( 'Vibe_BP_API_Rest_Wallet_Controller' ) ) {
	
	class Vibe_BP_API_Rest_Wallet_Controller extends WP_REST_Controller{
		
		public static $instance;
		
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new Vibe_BP_API_Rest_Wallet_Controller();
	        return self::$instance;
	    }

	    public function __construct( ) {

			$this->namespace = Vibe_BP_API_NAMESPACE;
			$this->type= 'wallet';
			$this->register_routes();
		}

		function register_routes(){

			
			/*
			In App Purchases
			 */
			register_rest_route( $this->namespace, '/'. $this->type, array(
				array(
					'methods'                   =>  "POST",
					'callback'                  =>  array( $this, 'get_user_wallet' ),
					'permission_callback' 		=> array( $this, 'get_user_permissions_check' ),
				),
			));
			register_rest_route( $this->namespace, '/'. $this->type .'/transactions', array(
				array(
					'methods'                   =>  "POST",
					'callback'                  =>  array( $this, 'get_transactions' ),
					'permission_callback' 		=> array( $this, 'get_user_permissions_check' ),
				),
			));
			register_rest_route( $this->namespace, '/'. $this->type .'/update', array(
				array(
					'methods'                   =>  'POST',
					'callback'                  =>  array( $this, 'update_wallet' ),
					'permission_callback' 		=> array( $this, 'get_user_permissions_check' ),
				),
			));

		}

		function get_user_permissions_check($request){
	    	
	    	$body = json_decode($request->get_body(),true);
	       	$body['token'] = sanitize_text_field($body['token']);
	        if (empty($body['token'])){
	           	$client_id = $request->get_param('client_id');
	           	if($client_id == vibebp_get_setting('client_id')){
	           		return true;
	           	}
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

	    function get_user_wallet($request){

			$wallet = get_user_meta($this->user->id,'wallet',true); //Amount

			if(empty($wallet)){$wallet=0;}

			return 	new WP_REST_Response( array('amount'=>$wallet), 200 );
		}
		
		function get_transactions($request){
			$body = json_decode($request->get_body(),true);
			$page = $body['paged']; 
			if(empty($body['paged'])){$page =1;}
			if(empty($body['action'])){$type = '';}else{
				$type = 'AND a.type = "'.esc_attr($body['type']).'"';
			}

			$per_page = $body['per_page'];
			global $wpdb,$bp;
			// Add limit for paged
			$total = $wpdb->get_var(
				$wpdb->prepare("
				SELECT COUNT(m.meta_value) as value 
				FROM {$bp->activity->table_name} as a 
				LEFT JOIN {$bp->activity->table_name_meta} as m 
				ON a.id=m.activity_id
				WHERE a.user_id = %d 
				AND m.meta_key = %s
				AND a.component = %s 
				$type
				ORDER BY a.id DESC
				 ",$this->user->id,'transaction','wallet'));


			$results = $wpdb->get_results(
				$wpdb->prepare("
				SELECT m.meta_value as value,a.* 
				FROM {$bp->activity->table_name} as a 
				LEFT JOIN {$bp->activity->table_name_meta} as m 
				ON a.id=m.activity_id
				WHERE a.user_id = %d 
				AND m.meta_key = %s
				AND a.component = %s 
				$type
				ORDER BY a.id DESC
				LIMIT %d,%d 
				 ",$this->user->id,'transaction','wallet',(($page-1)*$per_page),$per_page),ARRAY_A);
		
			$transactions = array();
			if(!empty($results)){

				foreach($results as $result){
					$result = array_merge(unserialize($result['value']),$result);
					array_push($transactions,$result);
				}
			}
			
			return 	new WP_REST_Response( array('status'=>1,'data'=>$transactions,'total'=>intval($total)), 200 );
			
		}

		function update_wallet($request){
			$post = json_decode(file_get_contents('php://input'),true);
			
			$message='';
			$status=false;
			$wallet = 0;
			if(!function_exists('bp_activity_add')){
				$message= _x('Unable to create wallet ! Enable activity in site.','activity disabled for api','vibebp');
			}
			$points = 0;
			if($post['status'] == 'debit'){

				$status = apply_filters('wplms_wallet_transaction_status',true,$post); 

				$activity_id = bp_activity_add( array( 
					'user_id' => $this->user->id, 
					'action' => $post['status'], 
					'content' => sprintf(_x('Wallet transaction "%s" %s worth %s','wallet','vibebp'),$post['description'],$post['status'].' '.($status?'success':'failed'),$post['amount']), 
					'component' => 'wallet', 
					'type' => $post['status'], 
				));
				bp_activity_update_meta($activity_id,'transaction',(Array)$post);

				if($status){
					$wallet = get_user_meta($this->user->id,'wallet',true);
					$points = (int)$post['amount'];
					$wallet = get_user_meta($this->user->id,'wallet',true); //Amount
					$wallet = $wallet - ($points);
					update_user_meta($this->user->id,'wallet',$wallet); //Amount
					$message= _x('Points debited from Wallet','wallet','vibebp');
				}
				ob_start();
				do_action('wplms_wallet_transaction',array('user_id' => $this->user->id,'post'=>(Array)$post));
				$message = ob_get_clean();
				
			}else if($post['status'] == 'credit'){

				$success = true;
				switch($post['store']){
					case 'google':
						
						
						//Validate $post['response']
						//$success <---
					break;
					case 'apple':
						//validate
						//$success <---
					break;
					case 'sample':
						//No validation required
					break;
				}
				$status = apply_filters('wplms_wallet_transaction_status',$success,(Array)$post);
				if($status){
					$products = get_vibebp_google_products_config();
					if(!empty($products) && !empty($products[$post['sku']])){
						$points = get_vibebp_google_products_config()[$post['sku']];
						$wallet = (int)get_user_meta($this->user->id,'wallet',true); //Amount
						$wallet = $wallet + ($points);
						update_user_meta($this->user->id,'wallet',$wallet); //Amount
						$message= _x('Points credited in Wallet','wallet','vibebp');
						$activity_id = bp_activity_add( array( 
							'user_id' => $this->user->id, 
							'action' => $post['status'], 
							'content' => sprintf(_x('Wallet transaction "%s" %s  worth %s','vibebp'),$post['sku'],$post['status'].' '.($status?'success':'failed'),$points), 
							'component' => 'wallet', 
							'type' => $post['status'], 
						));
						if(!empty($activity_id)){
							$post['amount'] = $points;
							bp_activity_update_meta($activity_id,'transaction',(Array)$post);
						}
						$message= _x('Credits added successfully!','wallet','vibebp');
					}else{
						$message= _x('Points not configured','wallet','vibebp');
					}
				}

				ob_start();
				do_action('wplms_wallet_transaction',array('user_id' => $this->user->id,'post'=>(ARRAY)$post));
				$message .= ob_get_clean();
				


			}else if($post['status'] == 'refund'){

			}else if($post['status'] == 'cancel'){

			}
			return 	new WP_REST_Response( array('message'=>$message,'points'=>$wallet,'status'=>$status), 200 );
		}

	    
	}
}


Vibe_BP_API_Rest_Wallet_Controller::init();
			