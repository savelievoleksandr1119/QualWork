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

class VibeBP_Woo_Wallet{
	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Woo_Wallet();
        return self::$instance;
    }

	private function __construct(){
		add_action( 'woocommerce_product_options_general_product_data', [$this,'add_wallet_field_to_product_editor'] );
		add_action( 'woocommerce_process_product_meta', [$this,'save_wallet_field_value'], 10, 2 );

		add_action('woocommerce_order_status_completed',array($this,'add_wallet_creds'));

	}


	function add_wallet_creds($order_id){

        $order = new WC_Order( $order_id );
        $items = $order->get_items();
        $user_id=$order->get_user_id();

        foreach($items as $item_id=>$item){

          $instructors=array();
        
            $credits=get_post_meta($item['product_id'],'vibe_product_wallet_credits',true);
            if(!empty($credits)){
                $wallet = (int)get_user_meta($user_id,'wallet',true); //Amount
				$wallet = $wallet + intval($credits);
				update_user_meta($user_id,'wallet',$wallet);
            }
        }
    }

	function add_wallet_field_to_product_editor(){
	 	global $woocommerce, $post;
	 	if(!function_exists('woocommerce_wp_text_input'))
	 		return;
	 	$val = get_post_meta( $post->ID,'vibe_product_wallet_credits',true);
	 	if(empty($val)){
	 		$val = 0;
	 	}
	    echo '<div class="options_group">';

	    woocommerce_wp_text_input( array(
	        'id' => 'vibe_product_wallet_credits',
	        'label' => _x( 'Credits to be given','','vibebp' ),
	        'placeholder' => _x('Credits','','vibebp'),
	        'desc_tip' => 'true',
	        'value'=>$val,
	        'description' =>_x('Set Credits to be given on purchase of this product','','vibebp'),

	    ) );

	    echo '</div>';
	}

	function save_wallet_field_value( $post_id, $post ) {
	    $custom_field_name = 'vibe_product_wallet_credits';

	    if ( isset( $_POST[$custom_field_name] ) ) {
	        $custom_field_value = sanitize_text_field( $_POST[$custom_field_name] );
	        update_post_meta( $post_id, $custom_field_name, $custom_field_value );
	    }
	}
}

VibeBP_Woo_Wallet::init();