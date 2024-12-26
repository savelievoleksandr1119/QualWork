<?php


/**
 * Customizer functions for vibeapp
 *
 * @author      VibeThemes
 * @category    Actions
 * @package     vibeapp
 * @version     1.0
 */


if ( ! defined( 'ABSPATH' ) ) exit;


class Vibe_Projects_Shortcodes{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Projects_Shortcodes();

        return self::$instance;
    }

    private function __construct(){

    	add_shortcode('site_project_data',[$this,'site_data']);
    	add_shortcode('member_project_data',array($this,'member_project_data'));
    	
    }

    function site_data($atts,$content=null){
    	$data = '';
    	global $wpdb;

    	switch($atts['data']){
    		case 'card_count':
    			$return = $wpdb->get_var("SELECT count(*) FROM {$wpdb->posts} WHERE post_type = 'card'");
    		break;
    		case 'completed_card_count':
    			$return = $wpdb->get_var("SELECT count(*) FROM {$wpdb->postmeta} WHERE meta_key = 'card_completed'");
    		break;
    		case 'board_count':
    			$return = $wpdb->get_var("SELECT count(*) FROM {$wpdb->posts} WHERE post_type = 'board'");
    		break;
    		default:
    			$return = $wpdb->get_var("SELECT count(*) FROM {$wpdb->posts} WHERE post_type = 'project'");
    		break;
    	}

    	return $return;
    }


    function member_project_data($atts,$content=null){
    	$data = '';
    	global $wpdb;

    	if(!empty( $atts['user'])){
    		$user_id = $atts['user'];
    	}else{
    		if(bp_displayed_user_id()){
				$user_id = bp_displayed_user_id();
			}else{
				global $members_template;
				if(!empty($members_template->member)){
					$user_id = $members_template->member->id;
				}
			}
			if(empty($user_id)){
				$init = VibeBP_Init::init();
				if(!empty($init->user_id)){
					$user_id = $init->user_id;
				}else if(is_user_logged_in()){
					$user_id = get_current_user_id();
				}
			}
    	}


    	if(!empty($atts['data']) && !empty($user_id)){
    		
    		$data = 0;
    		switch($atts['data']){
    			case 'projects':
	    			$data = vibe_projects_get_member_stats('projects_count',$user_id);
    			break;
    			case 'cards':
	    			$data = vibe_projects_get_member_stats('cards_count',$user_id);
    			break;
    			case 'completed_cards':
	    			$data = vibe_projects_get_member_stats('completed_cards',$user_id);
    			break;
    			case 'team':
    				$team = vibe_projects_get_member_team($user_id,true);
    				
    				if(!empty($team)){
    					$data = $team->name;	
    				}
    				
    			break;
    			default:
    				$data = apply_filters('vibe_projects_member_default_data',$data,$atts);
    			break;
    		}
    	}
    	return apply_filters('vibe_projects_member_project_data',$data,$atts);
    }
    
}

Vibe_Projects_Shortcodes::init();

