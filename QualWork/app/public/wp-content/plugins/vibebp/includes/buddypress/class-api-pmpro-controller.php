<?php

defined( 'ABSPATH' ) or die();


if ( ! class_exists( 'Vibe_BP_API_Rest_Pmpro_Controller' ) ) {
	
	class Vibe_BP_API_Rest_Pmpro_Controller extends WP_REST_Controller{
		
		public static $instance;
		
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new Vibe_BP_API_Rest_Pmpro_Controller();
	        return self::$instance;
	    }

	    public function __construct( ) {

			$this->namespace = Vibe_BP_API_NAMESPACE;
			$this->type= 'pmpro';
			$this->register_routes();
		}

		function register_routes(){

			
			register_rest_route( $this->namespace, '/'.$this->type.'/memberships/', array(
				'methods'                   =>   'POST',
				'callback'                  =>  array( $this, 'get_memberships' ),
				'permission_callback' => array( $this, 'get_user_permissions_check' ),
			) );

			register_rest_route( $this->namespace, '/'.$this->type.'/invoices/', array(
				'methods'                   =>   'POST',
				'callback'                  =>  array( $this, 'get_invoices' ),
				'permission_callback' => array( $this, 'get_user_permissions_check' ),
			) );

			register_rest_route( $this->namespace, '/'.$this->type.'/account/', array(
				'methods'                   =>   'POST',
				'callback'                  =>  array( $this, 'get_account' ),
				'permission_callback' => array( $this, 'get_user_permissions_check' ),
			) );

		}

		function get_account($request){
			$data = array('status'=>true);
			if ( ! empty( pmpro_getOption( 'member_profile_edit_page_id' ) ) ) {
				$edit_profile_url = pmpro_url( 'member_profile_edit' );
				$change_password_url = add_query_arg( 'view', 'change-password', pmpro_url( 'member_profile_edit' ) );
			} elseif ( ! pmpro_block_dashboard() ) {
				$edit_profile_url = admin_url( 'profile.php' );
				$change_password_url = admin_url( 'profile.php' );
			}
			$data['data'] = array(
				'edit_link' => esc_url( $edit_profile_url ),
				'change_password' => esc_url( $change_password_url ),
			);
			
			return new WP_REST_Response($data,200);
		}



		function get_memberships($request){
			$data = array('status'=>false,'message'=>_x('NO levels found','','vibebp'),'all_levels_page' => pmpro_url( 'levels' ));
			$levels = pmpro_getMembershipLevelsForUser($this->user->id);
			$pmpro_levels = pmpro_getAllLevels(false, true);

			if(!empty($levels)){
				$levels_data = [];
				foreach ($levels as $key => $level) {
					if($level->enddate)
						$expiration_text = date_i18n( get_option( 'date_format' ), $level->enddate );
					else
						$expiration_text = "---";

				    	$expiration_text =  apply_filters( 'pmpro_account_membership_expiration_text', $expiration_text, $level );
					$level_data = array(
						'id' => $level->id,
						'name' => $level->name,
						'expiration_html' => $expiration_text,
						'billing_html' => pmpro_getLevelCost($level, true, true),
						
						'cancel_link' => esc_url( add_query_arg( 'levelstocancel', $level->id, pmpro_url( 'cancel' ) ) )
					);
					if(count($pmpro_levels) > 1 && !defined("PMPRO_DEFAULT_LEVEL")) {
						$level_data['change_link'] =pmpro_url( 'levels' );
					}

					if( array_key_exists($level->id, $pmpro_levels) && pmpro_isLevelExpiringSoon( $level ) ) {
						$level_data['renew_link'] = esc_url( add_query_arg( 'level', $level->id, pmpro_url( 'checkout', '', 'https' ) ) );
					}
					$levels_data[] = $level_data;
				}
				$data = array('status'=>true,'data' => $levels_data,'all_levels_page' => pmpro_url( 'levels' ));
			}

			return new WP_REST_Response($data,200);
		}

		function get_invoices($request){
			$data = array('status'=>false,'message'=>_x('No invoices found','','vibebp'));
			global $wpdb;
			$per_page  = apply_filters('vibebp_pmpro_invoices_per_page',20);
			$user_id = $this->user->id;
			$invoices = $wpdb->get_results("SELECT *, UNIX_TIMESTAMP(timestamp) as timestamp FROM $wpdb->pmpro_membership_orders WHERE user_id = '{$user_id}' AND status NOT IN('review', 'token', 'error') ORDER BY timestamp DESC LIMIT {$per_page}");

			if(!empty($invoices)){
				$invoices_data = [];
				foreach ($invoices as $key => $invoice) {
					$invoice_id = (int)$invoice->id;
					$invoice = new MemberOrder;
					$invoice->getMemberOrderByID($invoice_id);
					$invoice->getMembershipLevel();

					if ( in_array( $invoice->status, array( '', 'success', 'cancelled' ) ) ) {
					    $display_status = __( 'Paid', 'paid-memberships-pro' );
					} elseif ( $invoice->status == 'pending' ) {
					    // Some Add Ons set status to pending.
					    $display_status = __( 'Pending', 'paid-memberships-pro' );
					} elseif ( $invoice->status == 'refunded' ) {
					    $display_status = __( 'Refunded', 'paid-memberships-pro' );
					}

					$invoice_data = array(
						'date' => date_i18n(get_option("date_format"), $invoice->timestamp),
						'level' => ((!empty($invoice->membership_level))? $invoice->membership_level->name: __("N/A", 'vibebp' )),
						'amount'=>  pmpro_formatPrice($invoice->total),
						'status' => $display_status,
						'invoice_link' =>pmpro_url("invoice", "?invoice=" . $invoice->code),
					);
					$invoices_data[] = $invoice_data;
				}
				$data = array('status'=>true,'data' => $invoices_data);
			}
			
			
			

			return new WP_REST_Response($data,200);
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

	    
	}
}


Vibe_BP_API_Rest_Pmpro_Controller::init();
			