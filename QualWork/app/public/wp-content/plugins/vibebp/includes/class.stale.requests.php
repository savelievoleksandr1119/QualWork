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

class VibeBP_Stale_Requests{
	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Stale_Requests();
        return self::$instance;
    }

	private function __construct(){
		add_action('bp_notification_after_save',array($this,'update_stale_notifications'));
		add_action('groups_join_group',array($this,'update_stale_groups'),10,2);
	}



	function update_stale_notifications($args){
		vibebp_fireabase_update_stale_requests($args->user_id,'notifications?%7B%22filter%22%3A%22unread');
	}

	function update_stale_groups($group_id,$user_id){
		vibebp_fireabase_update_stale_requests($user_id,'groups');
	}
	
}

VibeBP_Stale_Requests::init();