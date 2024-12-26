<?php
/**
 * Register Post Types
 *
 * @class       VibeBP_PostTypes
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VibeBP_Wallet{

	private $render_content = 0;
	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Wallet();
        return self::$instance;
    }

	private function __construct(){
        add_action( 'woocommerce_product_options_advanced', array($this,'adv_product_options'));
        add_action( 'woocommerce_process_product_meta', array($this,'save_fields'), 10, 2 );
        add_action( 'woocommerce_payment_complete', array($this,'payment_complete' ));
        //add_filter( 'woocommerce_payment_gateways', 'add_gateway_class' );

        //add_action( 'plugins_loaded', [$this,'init_gateway_class']);

    }

    function add_gateway_class( $gateways ) {
        if(!empty(vibebp_get_setting('enable_wallet','wallet'))){
            $gateways[] = 'WC_VibeBP_Wallet_Gateway'; // your class name is here
        }
        return $gateways;
    }


    function adv_product_options(){
       

        if(!empty(vibebp_get_setting('enable_wallet','wallet'))){
     
            echo '<div class="options_group">';
         
            woocommerce_wp_text_input( array(
                'id'      => 'vibebp_credits',
                'value'   => get_post_meta( get_the_ID(), 'vibebp_credits', true ),
                'label'   => esc_html__('VibeBP Credits','vibebp'),
                'desc_tip' => true,
                'description' => esc_html__('Users can use this product to purchase Credits','vibebp'),
            ) );
         
            echo '</div>';
        }
     
    }
     
     
    
    function save_fields( $id, $post ){
        if( !empty( $_POST['vibebp_credits'] ) ) {
            update_post_meta( $id, 'vibebp_credits', $_POST['vibebp_credits'] );
        } else {
            delete_post_meta( $id, 'vibebp_credits' );
        }
    }

    function payment_complete( $order_id ){  
        $order = wc_get_order( $order_id );
        $user_id = $order->get_user_id();
        $items = $order->get_items();
        foreach($items as $item){
            $product_id = $item->get_product_id();
            $credits = get_post_meta($product_id,'vibebp_credits',true);
            if(!empty($credits)){
                $cred = get_user_meta($user_id,'wallet',true);
                $cred  = parseInt($cred) + parseInt($credits);
                update_user_meta($user_id,'wallet',$cred);
                
                $activity_id = bp_activity_add( array( 
                    'user_id' => $user_id, 
                    'action' => 'credit', 
                    'content' => sprintf(_x('Wallet credited with payment for "%s" with %s','wallet','vibebp'),get_the_title($product_id),$credits), 
                    'component' => 'wallet', 
                    'type' => 'credit', 
                ));
                bp_activity_update_meta($activity_id,'transaction',['status'=>'credit','amount'=>$credits]);
            }
        }
    }

    // function init_gateway_class() {

    //     class WC_VibeBP_Wallet_Gateway extends WC_Payment_Gateway {

    //         /**
    //          * Class constructor, more about it in Step 3
    //          */
    //         public function __construct() {

    //             $this->id = 'vibebp_wallet';
    //             $this->icon = ''; 
    //             $this->has_fields = true;
    //             $this->method_title = 'VibeBP Wallet';
    //             $this->method_description = 'Member Wallet for payments';

    //             // gateways can support subscriptions, refunds, saved payment methods,
    //             // but in this tutorial we begin with simple payments
    //             $this->supports = array(
    //                 'products','subscriptions'
    //             );

    //         }

    //         /**
    //          * Plugin options, we deal with it in Step 3 too
    //          */
    //         public function init_form_fields(){

           
        
    //         }

    //         /**
    //          * You will need it if you want your custom credit card form, Step 4 is about it
    //          */
    //         public function payment_fields() {

           
                     
    //         }

    //         /*
    //          * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
    //          */
    //         public function payment_scripts() {

            
        
    //         }

    //         /*
    //          * Fields validation, more in Step 5
    //          */
    //         public function validate_fields() {

    //             $wallet = (int)get_user_meta($user_id,'wallet',true); //Amount

    //         }

    //         /*
    //          * We're processing the payments here, everything about it is in Step 5
    //          */
    //         public function process_payment( $order_id ) {

           
                        
    //         }

    //         /*
    //          * In case you need a webhook, like PayPal IPN etc
    //          */
    //         public function webhook() {

            
                        
    //         }
    //     }
    // }
}
VibeBP_Wallet::init();