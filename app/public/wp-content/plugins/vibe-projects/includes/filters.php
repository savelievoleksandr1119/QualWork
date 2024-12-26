<?php
/**
 * INIT\
 *
 * @class       Vibe_Projects_Init
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Vibe_Projects_Filters{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Projects_Filters();
        return self::$instance;
    }

	private function __construct(){
		add_filter('vibe_projects_api_permission',array($this,'check_vibebp_framework'),10,2);
		add_filter('vibebp_api_get_activity',[$this,'project_activity'],10,3);

		add_filter('vibe_project_tabs',[$this,'project_tab_access'],10,3);
        add_filter('vibe_projects_enqueue_project_scripts',[$this,'custom_project_fields']);
        add_filter('vibe_projects_enqueue_project_scripts',[$this,'custom_card_fields']);

        add_filter('vibebp_member_taxonomy_slugs',[$this,'add_team']);
    	add_filter('vibebp_vars',[$this,'bulk_project_actions']);
    	add_filter('vibebp_bulk_member_action',[$this,'member_action'],10,2);
    	add_filter('vibebp_get_member_taxonomies',[$this,'team_taxonomy']);
		
		add_filter('vibe_projects_project_create_form',[$this,'create_project_fields'],10,2);
		add_filter('vibe_card_can_complete',[$this,'can_complete_card'],10,4);
		

	}

	function can_complete_card($return,$card_id,$project_id,$user){

		$dependencies = get_post_meta($card_id,'vibe_card_dependency',false);
		if(empty($dependencies)){
			return $return;
		}
		foreach($dependencies as $dependency){
			if(!vibe_card_is_complete($dependency)){
				return sprintf(__('Card dependency not complete %s','vibe-projects'),get_the_title($dependency));
			}
		}

		return $return;
	}
	
	function add_team($slugs){
		$slugs[]='team';
		return $slugs;
	}

	function team_taxonomy($taxonomies){
		$terms = get_terms('team');
		$new_tax=[];
		foreach($taxonomies as $i=>$tax){
			$new_tax[]=$tax;
			if($i == 0){
				$new_tax[]=['type'=>'team','label'=>__('Member Teams','vibebp'),'value'=>$terms];
			}
		}
		
		return $new_tax;
	 
	}
    
    function bulk_project_actions($args){

        global $wpdb;
        $projects = $wpdb->get_results("SELECT ID,post_title FROM {$wpdb->posts} WHERE post_type='project' AND post_status !='trash'",ARRAY_A);
        $all_projects = [];
        if(!empty($projects)){
            foreach($projects as $project){
                $all_projects[]=['key'=>$project['ID'],'label'=>$project['post_title']];
            }
        }
        if(!empty($all_projects)){
            $args['components']['members_detail']['settings']['bulk_actions'][]=[
                'key'=>'add_project',
                'label'=>__('Add To Project','vibebp'),
                'options'=> $all_projects
            ];  
            $args['components']['members_detail']['settings']['bulk_actions'][]=[
                'key'=>'remove_project',
                'label'=>__('Remove from Project','vibebp'),
                'options'=> $all_projects
            ];  
        }

        return $args;
    }

    function member_action($return,$body){

        if(!empty($body['member_ids']) && !empty($body['term_ids'])){
            if($body['action'] === 'add_project'){
                $project_ids = wp_list_pluck($body['term_ids'],'key');
                foreach($body['member_ids'] as $member_id){
                    foreach($project_ids as $project_id){
                        vibe_projects_add_member_to_project($member_id,$project_id);
                    }
                }
                do_action('vibe_projects_bulk_add_members',$body['member_ids'],$project_ids);
                $return =['status'=>1,'message'=>sprintf(__('%d members added to %d project','vibe-projects'),count($body['member_ids']),count($project_ids))];
            }
            if($body['action'] === 'remove_project'){
                $project_ids = wp_list_pluck($body['term_ids'],'key');
                foreach($body['member_ids'] as $member_id){
                    foreach($project_ids as $project_id){
                        vibe_projects_remove_member_from_project($member_id,$project_id);
                    }
                }
                do_action('vibe_projects_bulk_remove_members',$body['member_ids'],$project_ids);
                $return =['status'=>1,'message'=>sprintf(__('%d members removed from %d project','vibe-projects'),count($body['member_ids']),count($project_ids)) ];
            }

            if($body['action'] === 'add_team'){
                $team_ids = wp_list_pluck($body['term_ids'],'key');
                foreach($body['member_ids'] as $member_id){
                    vibe_projects_set_member_team( $member_id, $team_ids );
                }
                do_action('vibe_projects_bulk_add_team_members',$body['member_ids'],$team_ids);
                $return =['status'=>1,'message'=>sprintf(__('%d members added to %d teams.','vibe-projects'),count($body['member_ids']),count($team_ids))];
            }
            if($body['action'] === 'remove_team'){
                $team_ids = wp_list_pluck($body['term_ids'],'key');
                foreach($body['member_ids'] as $member_id){
                    vibe_projects_remove_member_team($member_id,$team_ids);
                    
                }
                do_action('vibe_projects_bulk_remove_team_members',$body['member_ids'],$team_ids);
                $return =['status'=>1,'message'=>sprintf(__('%d members added to %d teams.','vibe-projects'),count($body['member_ids']),count($team_ids))];
            }
        }

        return $return;
    }

    function custom_project_fields($args){
    	$fields = vibebp_get_setting('create_project_fields','vibe_projects','projects');
		if(!empty($fields)){
			foreach($fields['key'] as $i => $key){
				if(!empty($fields['preload'][$i])){
					$args['settings']['project']['listView'][]=['key'=>$key,'from'=>'meta','label'=>$fields['label'][$i],'selected'=>1,'required'=>0];
				}
			}
		}
		return $args;
    }

	function custom_card_fields($args){
		$fields = vibebp_get_setting('create_card_fields','vibe_projects','cards');
		if(!empty($fields)){
			foreach($fields['key'] as $i => $key){
				if(!empty($fields['preload'][$i]) ){
					$args['settings']['board']['listView'][]=['key'=>$key,'from'=>'meta','label'=>$fields['label'][$i],'selected'=>1,'required'=>0];
				}
			}
		}
		return $args;
	}

	function create_project_fields($args,$project_id=0){
		$fields = vibebp_get_setting('create_project_fields','vibe_projects','projects');

		if(!empty($fields)){
			$args[]=array(
		        'label'=> __('Custom fields Project.','vibe-projects' ),
		        'type'=> 'label',
		        'style'=>'full',
		        'id'=>'project_fields',
		        'desc'=> __('Custom added fields for the Project.','vibe-projects' ),
		    );
			foreach($fields['type'] as $i => $type){

				$f=array(
			        'label'=> $fields['label'][$i],
			        'type'=> $type,
			        'style'=>'',
			        'value_type'=>'single',
			        'id' => $fields['key'][$i],
			        'from'=>'meta',
			        'desc'=> '',
			        'value' => empty($project_id)? '':get_post_meta($project_id,$fields['key'][$i],true),
			    );
			    if($type == 'select'){
			    	$options=[];
					$ops = explode('|',$fields['value'][$i]);
					if(!empty($ops)){
						foreach ($ops as $op) {
							$keyval = explode('=>',$op);
							$options[]=['label'=>$keyval[1],'value'=>$keyval[0]];
						}
					}
			    	$f['options']=$options;
			    }
			     if($type == 'checkbox'){
			     	$f['type'] = 'switch';

			     }
			    $args[]=$f;
			}
		}
		return $args;
	} 
	    

 	function project_tab_access($tabs,$project_id,$user){
 		if(in_array('administrator',$user->roles))
 			return $tabs;

        foreach($tabs as $k=>$tab){
        	$check = vibe_projects_user_can('view_project_'.$tab['value'],$user->member_type);        	
        	if(empty($check)){
        		unset($tabs[$k]);
        	}
        }

        
        return array_values($tabs);
    }

	function check_vibebp_framework($user,$request){

		if(function_exists('vibebp_api_get_user_from_token')){
			$body = json_decode($request->get_body(),true);
			if(!empty($body['token'])){
				$user = vibebp_api_get_user_from_token($body['token']);
			}
		}
		return $user;
	}

	function project_activity($activity_args,$args,$user_id){
		if(!empty($args)){
			
			if(!empty($args['filter']) && $args['filter'] == 'vibe_projects'){
				if(!empty($args['id'])){
					$args['item_id']=$args['id'];
				}

				if(!empty($args['item_id'])){
				
					//Check if User a regular user of Project OR admin PAss used ID
					$arguments= array('primary_id'=>$args['item_id'],'object'=>$args['filter']);
					if(!empty($args['secondary_item_id'])){
						$arguments['secondary_id'] = $args['secondary_item_id'];
					}

					$activity_args['filter'] =$arguments;
					
				}
			} 



		}


		return $activity_args;
	}

}

Vibe_Projects_Filters::init();