<?php
/**
 * Functions\
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

class Vibe_Projects_Functions{

	public static $instance;
	public $start_date;
	public $end_date;
	public $card_start_date = [];
	public $card_due_date = [];
	public $project_members=[];
	public $card_members=[];

	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Projects_Functions();
        return self::$instance;
    }

	private function __construct(){
		$this->project_members=[];
		
	}

	function get_project_type($project_id){
		if(!empty($this->project_type))
		$project_type = get_the_terms($project_id,'project-type');
		return $project_type;
	}


	function get_project_members($project_id){

		global $wpdb;

		if(!empty($this->project_members[$project_id])){
			return $this->project_members[$project_id];
		}

		$users = $wpdb->get_results($wpdb->prepare(
			"SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'vibe_project' AND meta_value = %d",$project_id));

		$allusers=[];
		if(!empty($users)){
			foreach ($users as $key => $u) {
				$allusers[]=$u->user_id;
			}
		}
		$this->project_members[$project_id] = array_unique($allusers);
		return $allusers;
	}

	function get_card_members($card_id){

		if(!empty($this->card_members[$card_id])){
			return $this->card_members[$card_id];
		}

		global $wpdb;
		$members = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value= %d",'vibe_project_card_member',$card_id));

		if(!empty($members)){
			$members = wp_list_pluck($members,'user_id');
			$this->card_members[$card_id] = array_unique($members);
			return $this->card_members[$card_id];
		}	

		return [];
	}
	

	function get_card_taxonomy_list($card_id){
		$terms = wp_get_post_terms($card_id, 'list');
		if(!empty($terms)){
			return array('term_id'=>$terms[0]->term_id,'name'=>$terms[0]->name);	
		}
	}

	function get_project_start_date($project_id){
		if(empty($this->start_date)){
			$this->start_date = get_post_meta($project_id,'vibe_project_start_date',true);
			if(!is_numeric($this->start_date)){
				$this->start_date=strtotime($this->start_date);
			}
		}	

		return $this->start_date;
	}

	function update_project_start_date($project_id,$date){
		if(!is_numeric($date)){
			$date = strtotime($date);
		}
		update_post_meta($project_id,'vibe_project_start_date',$date);
		if(!empty($this->start_date)){			
			$this->start_date = $date;
		}	

		return $this->start_date;
	}


	function get_project_end_date($project_id){
		if(empty($this->end_date)){
			$this->end_date = get_post_meta($project_id,'vibe_project_end_date',true);
			if(!is_numeric($this->end_date)){
				$this->end_date=strtotime($this->end_date);
			}
		}	

		return $this->end_date;
	}

	function update_project_end_date($project_id,$date){
		if(!is_numeric($date)){
			$date = strtotime($date);
		}
		update_post_meta($project_id,'vibe_project_end_date',$date);
		if(!empty($this->end_date)){			
			$this->end_date = $date;
		}	

		return $this->end_date;
	}

	function get_card_start_date($card_id){

		if(empty($this->card_start_date)){
			$this->card_start_date=[];
		}
		if(empty($this->card_start_date[$card_id])){
			$card_start_date = apply_filters('vibe_projects_get_card_start_date','',$card_id);
			if(empty($card_start_date)){
				$card_start_date = get_post_meta($card_id,'vibe_card_start_date',true);	
			}
			$this->card_start_date[$card_id] = $card_start_date;	
		}
		
		
		return $this->card_start_date[$card_id];
	}
	
	function get_card_due_date($card_id){
		if(empty($this->card_due_date)){
			$this->card_due_date=[];
		}
		if(empty($this->card_due_date[$card_id])){
			$card_due_date = apply_filters('vibe_projects_get_due_date','',$card_id);
			if(empty($card_due_date)){
				$card_due_date = get_post_meta($card_id,'vibe_card_due_date',true);	
			}
			$this->card_due_date[$card_id] = $card_due_date;	
		}
		
		
		return $this->card_due_date[$card_id];
	}
}

function vibe_projects_get_member_stats($stat,$user_id){

	global $wpdb;
	$return=0;
	switch($stat){
		case 'cards_count':
			$return = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND user_id = %d",'vibe_project_card_member',$user_id));
		break;
		case 'projects_count':
			$return = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND user_id = %d",'vibe_project_card_member',$user_id));
		break;
		case 'completed_cards':
			$retrun = $wpdb->get_var($wpdb->prepare("SELECT Count(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE post_id IN (SELECT p.ID FROM {$wpdb->posts} AS p LEFT JOIN {$wpdb->usermeta} AS um ON p.ID=um.meta_value WHERE um.meta_key = 'vibe_project_card_member'  AND um.user_id = %d AND p.post_status='publish') AND meta_key=%s",'vibe_card_complete',$user_id));
		break;
	}

	return $return;
}

function vibe_projects_get_card_start_date($card_id){
	$init = Vibe_Projects_Functions::init();;
	$return = $init->get_card_start_date($card_id);
	return $return;
}

function vibe_projects_get_card_due_date($card_id){
	$init = Vibe_Projects_Functions::init();;
	$return = $init->get_card_due_date($card_id);
	return $return;
}

function vibe_projects_get_project_members($project_id){
	$init = Vibe_Projects_Functions::init();;
	return $init->get_project_members($project_id);
}

function vibe_projects_add_member_to_project($member_id,$project_id){
	if(!empty($project_id) && !empty($member_id)){
		add_user_meta($member_id,'vibe_project',$project_id);	
	}
	
}
function vibe_projects_remove_member_from_project($member_id,$project_id){
	if(!empty($project_id) && !empty($member_id)){
		delete_user_meta($member_id,'vibe_project',$project_id);
	}
}

function vibe_projects_get_board_visibility($board_id){
    return get_post_meta($board_id,'vibe_boards_visibility',true);
}

function vibe_projects_get_member_type($user_id){
	$types = bp_get_member_types(array(),'objects');
    if(!empty($types)){
        foreach($types as $type => $labels){
            $mtypes[$type]=$labels->labels['name'];
        }
    }
    $member_type = bp_get_member_type($user_id);
    if(empty($member_type)){
    	return '';
    }else{
    	return $mtypes[$member_type];
    }
}
function vibe_projects_set_member_team( $user_id, $team ) {
	wp_delete_object_term_relationships($user_id,'team');
	$retval= wp_set_object_terms( $user_id, $team, 'team', false);
	clean_object_term_cache( $user_id, 'team' );
	return $retval;
}

function vibe_projects_remove_member_team( $user_id, $team ) {
	wp_delete_object_term_relationships($user_id,'team');
	$retval= wp_remove_object_terms( $user_id, $team, 'team');
	clean_object_term_cache( $user_id, 'team' );
	return $retval;
}

function vibe_projects_get_member_team( $user_id, $single = true ) {

    $types = wp_cache_get( $user_id, 'team' );
 
    if ( false === $types ) {


        $raw_types = bp_get_object_terms( $user_id, 'team' );
 
        if ( ! is_wp_error( $raw_types ) ) {
            $types =  array();
 
            // Only include currently registered group types.
            foreach ( $raw_types as $mtype ) {
                $types[] = $mtype;
            }
 
            wp_cache_set( $user_id, $types, 'team' );
        }
    }
 
    $type = false;
    if ( ! empty( $types ) ) {
        if ( $single ) {
            $type = array_pop( $types );
        } else {
            $type = $types;
        }
    }
 
    return apply_filters( 'vibe_projects_get_member_team', $type, $user_id, $single );
}



function vibe_projects_get_start_date($project_id){
	$init = Vibe_Projects_Functions::init();;
	return $init->get_project_start_date($project_id);
}

function vibe_projects_get_end_date($project_id){
	$init = Vibe_Projects_Functions::init();;
	return $init->get_project_end_date($project_id);
}
function vibe_projects_get_project_type($project_id){
	$init = Vibe_Projects_Functions::init();;
	//print_r('get type');
	return $init->get_project_type($project_id);
}

function vibe_projects_get_card_members($card_id){

	$init = Vibe_Projects_Functions::init();
	
	return $init->get_card_members($card_id);
}

function vibe_cards_get_terms($card_id){
	$init = Vibe_Projects_Functions::init();;
	return $init->get_card_taxonomy_list($card_id);
}

function vibe_projects_get_project_fields($project_id = null){

	if(!empty($project_id)){
		$cats = [];
		$terms = get_the_terms($project_id,'project-type');
		if(!empty($terms) && !is_wp_error($terms)){
			$cats = wp_list_pluck($terms,'term_id');
		}
	}

	$args =  array(
	    array(
	        'label'=> __('Project Image','vibe-projects' ),
	        'type'=> 'featured',
	        'level'=>'thumbnail',
	        'value_type'=>'single',
	        'upload_title'=>__('Upload a Project Image','vibe-projects' ),
	        'upload_button'=>__('Set as Project Image','vibe-projects' ),
	        'style'=>'',
	        'from'=>'meta',
	        'id' => '_thumbnail_id',
	        'default'=> '',
	        'value'=>(empty($project_id)?'':array(
	        	'id'=> get_post_thumbnail_id($project_id),
	        	'name'=> 'image',
	        	'type' => 'image',
	        	'url' => get_the_post_thumbnail_url($project_id,'full')))
	    ),
	    array(
	        'label'=> __('Project title','vibe-projects' ),
	        'type'=> 'title',
	        'id' => 'post_title',
	        'from'=>'post',
	        'value_type'=>'single',
	        'style'=>'full',
	        'default'=> __('Enter a project title','vibe-projects' ),
	        'desc'=> __('This is the title of the project, the most relevant detail to recognise the project.','vibe-projects' ),
	        'value'=>(empty($project_id)?'':get_the_title($project_id))
        ),
	    array(
	        'label'=> __('Project status','vibe-projects' ),
	        'type'=> 'select',
	        'id' => 'post_status',
	        'from'=>'post',
	        'value_type'=>'single',
	        'style'=>'cat',
	        'default'=>'publish',
	        'options'=>vibe_projects_get_statuses('project'),
	        'desc'=> __('This is the status of the project','vibe-projects' ),
	        'value'=>(empty($project_id)?'':get_post_status($project_id))
        ),
	    array(
	        'label'=> __('Project Category','vibe-projects' ),
	        'type'=> 'taxonomy',
	        'taxonomy'=> 'project-type',
	        'from'=>'taxonomy',
	        'value_type'=>'single',
	        'style'=>'assign_cat',
	        'id' => 'project-type',
	        'desc'=> __('Select a Project Type','vibe-projects' ),
	        'value' => (empty($project_id)?'':$cats)
	        ),
	    array(
	        'label'=> __('Detailed Description of the Project','vibe-projects' ),
	        'type'=> 'editor',
	        'style'=>'full',
	        'value_type'=>'single',
	        'id' => 'post_content',
	        'from'=>'post',
	        'noscript'=>true,
	        'raw'=> empty($project_id)? '':wp_unslash(get_post_meta($project_id,'raw',true)),
	        'desc'=> __('Enter full description for the Project.','vibe-projects' ),
	        'value' => (empty($project_id)? '':get_the_content('','',$project_id))
	    ),
	    array(
	        'label'=> __('Important aspects of Project.','vibe-projects' ),
	        'type'=> 'label',
	        'style'=>'full',
	        'id'=>'project_aspects',
	        'desc'=> __('Project Vitals','vibe-projects' ),
	    ),
	    array(
	        'label'=> __('Project Start date','vibe-projects' ),
	        'type'=> 'date',
	        'style'=>'',
	        'value_type'=>'single',
	        'id' => 'vibe_project_start_date',
	        'from'=>'meta',
	        'noscript'=>true,
	        'raw'=> empty($project_id)? '':get_post_meta($project_id,'raw',true),
	        'desc'=> __('Date of commence for the Project.','vibe-projects' ),
	        'value' => empty($project_id)? '':vibe_projects_get_start_date($project_id),
	    ),
	    array(
	        'label'=> __('Project End date','vibe-projects' ),
	        'type'=> 'date',
	        'style'=>'',
	        'value_type'=>'single',
	        'id' => 'vibe_project_end_date',
	        'from'=>'meta',
	        'noscript'=>true,
	        'raw'=> empty($project_id)? '':get_post_meta($project_id,'raw',true),
	        'desc'=> __('End date for the Project.','vibe-projects' ),
	        'value' => empty($project_id)? '':vibe_projects_get_end_date($project_id),
	    ),
	    array(
	        'label'=> __('Project Progress','vibe-projects' ),
	        'type'=> 'select',
	        'style'=>'',
	        'value_type'=>'single',
	        'id' => 'vibe_project_progress_criteria',
	        'from'=>'meta',
	        'noscript'=>true,
	        'value'=> empty($project_id)? 'cards':get_post_meta($project_id,'vibe_project_progress_criteria',true),
	        'desc'=> __('Track project progress based on.','vibe-projects' ),
	        'options' => [
	        	['value'=>'cards','label'=>__('Progress based on Card completion','vibe-projects')],
	        	['value'=>'milestones','label'=>__('Progress based on milestone completion.','vibe-projects')],
	        	['value'=>'time','label'=>__('Progress based on time elapsed.','vibe-projects')],
	        	['value'=>'','label'=>__('Do not track progress.','vibe-projects')]
	        ]
	    ),
	    array(
	        'label'=> __('Activate features for this Project.','vibe-projects' ),
	        'type'=> 'label',
	        'style'=>'full',
	        'id'=>'project_features',
	        'desc'=> __('Project Features','vibe-projects' ),
	    ),
	    array(
	        'label'=> __('Do you need Milestones ? ','vibe-projects' ),
	        'type'=> 'switch',
	        'style'=>'',
	        'value_type'=>'single',
	        'id' => 'vibe_projects_gantt',
	        'from'=>'meta',
	        'noscript'=>true,
	        'value'=> empty($project_id)? 'public':get_post_meta($project_id,'vibe_projects_gantt' ,true),
	        'desc'=> __('Milestones help you plan the project major phases.','vibe-projects' ),
	    ),
	    array(
	        'label'=> __('Do you need tasks ?','vibe-projects' ),
	        'type'=> 'switch',
	        'style'=>'',
	        'value_type'=>'single',
	        'id' => 'vibe_projects_boards',
	        'from'=>'meta',
	        'noscript'=>true,
	        'value'=> empty($project_id)? 'H':get_post_meta($project_id,'vibe_projects_boards',true),
	        'desc'=> __('Tasks clarify smaller steps towards your projects bigger goals.','vibe-projects' ),
	    ),
	    array(
	        'label'=> __('Default Tasks view','vibe-projects' ),
	        'type'=> 'select',
	        'options'=>[
	        	['value'=>'boards','label'=>__('Scrum board','vibe-projects')],
	        	['value'=>'timeline','label'=>__('Gant chart','vibe-projects')],
	        	['value'=>'list','label'=>__('List','vibe-projects')]
	        ],
	        'style'=>'',
	        'value_type'=>'single',
	        'id' => 'vibe_projects_default_task_view',
	        'from'=>'meta',
	        'noscript'=>true,
	        'value'=> empty($project_id)? 'boards':get_post_meta($project_id,'vibe_projects_default_task_view',true),
	        'desc'=> __('Enable Task Lists for the Project.','vibe-projects' ),
	    ),
	);
	$args = apply_filters('vibe_projects_project_create_form',$args,$project_id);
	return $args;
}

	    

function vibe_projects_get_board_fields($board_id =null,$project_id=null){
	if(!empty($board_id)){
		$cats = [];
		$terms = get_the_terms($board_id,'board-type');
		if(!empty($terms) && !is_wp_error($terms)){
			$cats = wp_list_pluck($terms,'term_id');
		}
	}
	$mtypes=[
		['value'=>'private','label'=>__('All project members','vibe-projects')],
		['value'=>'public','label'=>__('Public board','vibe-projects')]
	];

	
	$types = bp_get_member_types(array(),'objects');
    if(!empty($types)){

        foreach($types as $type => $labels){
            $mtypes[]=['value' => $type,'label'=>$labels->labels['name']];
        }
    }

	return apply_filters('vibe_projects_create_board_form',array(
	    array(
	        'label'=> __('Boards Image','vibe-projects' ),
	        'type'=> 'featured',
	        'level'=>'thumbnail',
	        'value_type'=>'single',
	        'upload_title'=>__('Upload a Boards Image','vibe-projects' ),
	        'upload_button'=>__('Set as Boards Image','vibe-projects' ),
	        'style'=>'',
	        'from'=>'meta',
	        'id' => '_thumbnail_id',
	        'default'=> '',
	        'value'=>(empty($board_id)?'':array(
	        	'id'=> get_post_thumbnail_id($board_id),
	        	'name'=> 'image',
	        	'type' => 'image',
	        	'url' => get_the_post_thumbnail_url($board_id,'full')))
	    ),
	    array(
	        'label'=> __('Boards title','vibe-projects' ),
	        'type'=> 'title',
	        'id' => 'post_title',
	        'from'=>'post',
	        'value_type'=>'single',
	        'style'=>'full',
	        'default'=> __('Enter a board title','vibe-projects' ),
	        'desc'=> __('This is the title of the board which is displayed on top of every board','vibe-projects' ),
	        'value'=>(empty($board_id)?'':get_the_title($board_id))
	        ),
	    array(
	        'label'=> __('Boards Category','vibe-projects' ),
	        'type'=> 'taxonomy',
	        'taxonomy'=> 'board-type',
	        'from'=>'taxonomy',
	        'value_type'=>'single',
	        'style'=>'assign_cat',
	        'id' => 'board-type',
	        'desc'=> __('Select a Boards Type','vibe-projects' ),
	        'value' => (empty($board_id)? '':$cats)
	        ),
	    array(
	        'label'=> __('Access','vibe-projects' ),
	        'text'=>__('Board access','vibe-projects' ),
	        'type'=> 'select',
	        'options'  => $mtypes,
	        'style'=>'',
	        'id' => 'vibe_boards_visibility', 
	        'from'=> 'meta',
	        'is_child'=>true,
	        'desc'=> __('Who can see this board.','vibe-projects' ),
	        'value' => empty($board_id)?'':get_post_meta($board_id,'vibe_board_public',true)
	    ),
	     array(
	        'label'=> __('Board Progress','vibe-projects' ),
	        'text'=>__('Display Board Progress','vibe-projects' ),
	        'type'=> 'switch',
	        'options'  => array('H'=>__('No','vibe-projects' ),'S'=>__('Yes','vibe-projects' )),
	        'style'=>'',
	        'id' => 'vibe_board_show_progress', 
	        'from'=> 'meta',
	        'default'=>'H',
	        'is_child'=>true,
	        'desc'=> __('Show a progress bar in Board based on card progress.','vibe-projects' ),
	        'value' => empty($board_id)?'':get_post_meta($board_id,'vibe_board_show_progress',true)
	    ),
	    array(
	        'label'=> __('Detailed Description of the Boards','vibe-projects' ),
	        'type'=> 'editor',
	        'style'=>'tag_open',
	        'value_type'=>'single',
	        'id' => 'post_content',
	        'from'=>'post',
	        'noscript'=>true,
	        'raw'=> empty($board_id)? '':get_post_meta($board_id,'raw',true),
	        'desc'=> __('Enter full description for the Boards.','vibe-projects' ),
	        'value' => (empty($board_id)? '':get_the_content('','',$board_id))
	    ),
	));
}


function vibe_card_is_complete($card_id){
	$check = apply_filters('vibe_card_is_complete',get_post_meta($card_id,'vibe_card_complete',true),$card_id);
	if(!empty($check)){
		return 1;
	}
	return 0;
}

function get_card_list($card_id){
	$lists = wp_get_object_terms($card_id,'list',array(
            'orderby'    => 'meta_value_num',
            'meta_key'   => 'order',
            'order'      => 'ASC',
            'meta_query' => array(
                'relation'=>'AND',
                array(
                    'key' => 'list_status',
                    'value' => 'archived',
                    'compare' => '!='
                ),
            )
        )
    );
    if(!empty($lists)){
    	return $lists[0];
    }
}

function get_card_list_board($card_id){
	$lists = wp_get_object_terms($card_id,'list',array(
            'orderby'    => 'meta_value_num',
            'meta_key'   => 'order',
            'order'      => 'ASC',
            'meta_query' => array(
                'relation'=>'AND',
                array(
                    'key' => 'list_status',
                    'value' => 'archived',
                    'compare' => '!='
                ),
            )
        )
    );
    if(!empty($lists) && !empty($lists[0])){
    	$query = new WP_Query(array(
            'post_type'=>'board',
            'post_status'=>array('any'),
            'posts_per_page'=>-1,
            'orderby' => 'menu_order', 
            'order' => 'ASC', 
            'tax_query'=>array(
                'relation'=>'AND',
                array(
                    'taxonomy'=>'list',
                    'field'=>'id',
                    'terms'=> array($lists[0]->term_id)
                )
            )
        ));
        $board = null;
        if(!empty($query->have_posts())){
            if($query->have_posts()){
                $status = 1;
                while($query->have_posts()){
                    $query->the_post();
                    global $post;
                    $board = $post;
                }
            }
        }

        return array('list'=>$lists[0],'board'=>$board);
    }
}

function get_card_list_board_project($card_id){
	$lists = wp_get_object_terms($card_id,'list',array(
            'orderby'    => 'meta_value_num',
            'meta_key'   => 'order',
            'order'      => 'ASC',
            'meta_query' => array(
                'relation'=>'AND',
                array(
                    'key' => 'list_status',
                    'value' => 'archived',
                    'compare' => '!='
                ),
            )
        )
    );
    if(!empty($lists) && !empty($lists[0])){
    	$query = new WP_Query(array(
            'post_type'=>'board',
            'post_status'=>array('any'),
            'posts_per_page'=>-1,
            'orderby' => 'menu_order', 
            'order' => 'ASC', 
            'tax_query'=>array(
                'relation'=>'AND',
                array(
                    'taxonomy'=>'list',
                    'field'=>'id',
                    'terms'=> array($lists[0]->term_id)
                )
            )
        ));
        $board = null;
        if(!empty($query->have_posts())){
            if($query->have_posts()){
                $status = 1;
                while($query->have_posts()){
                    $query->the_post();
                    global $post;
                    $board = $post;
                }
            }
        }

        if(!empty($board)){
        	return array('list'=>$lists[0],'board'=>$board,'project'=>get_post($board->post_parent));
        }
    }
}


function vibe_projects_record_activity( $args = '' ) {
    global $bp;

    if ( !function_exists( 'bp_activity_add' ) )
        return false;

    $defaults = array(
        'id' => false,
        'user_id' => $bp->loggedin_user->id,
        'action' => '',
        'content' => '',
        'primary_link' => '',
        'component' => 'vibe_projects',
        'type' => false,
        'item_id' => false,
        'secondary_item_id' => false,
        'recorded_time' => gmdate( "Y-m-d H:i:s" ),
        'hide_sitewide' => false
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r );

    return bp_activity_add( apply_filters('vibe_projects_record_activity',array( 'id' => $id, 'user_id' => $user_id, 'action' => $action, 'content' => $content, 'primary_link' => $primary_link, 'component' => $component, 'type' => $type, 'item_id' => $item_id, 'secondary_item_id' => $secondary_item_id, 'recorded_time' => $recorded_time, 'hide_sitewide' => $hide_sitewide ) ));
}

function vibe_projects_record_activity_meta($args=''){

    if ( !function_exists( 'bp_activity_update_meta' ) )
        return false;

    $defaults = array(
        'id' => false,
        'meta_key' => '',
        'meta_value' => ''
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r );

    return bp_activity_update_meta($id,$meta_key,$meta_value);
}




function vibe_projects_messages_new_message($args = null){
	if(!function_exists('bp_is_active') || !bp_is_active('messages') || !function_exists('messages_new_message'))
		return;
	global $bp;
	$defaults = array(
		'sender_id' => $bp->loggedin_user->id,
		'subject' => '',
		'content' => '',
		'recipients' => '',
	);
	$r = wp_parse_args( $args, $defaults );
	extract( $r );
	return  messages_new_message( 
			array('sender_id' =>  $sender_id,
			  'subject' => $subject,
			  'content' => $content,
			  'recipients' => $recipients
			  )
		);
}



function vibe_projects_get_task_object($post_id,$list=true){

	$result = wp_cache_get( 'card_'. $post_id,'cards');
    if ( false === $result ) {

    	$terms = vibe_cards_get_terms($post_id);

    	if(!$list && $terms){
    		return false;
    	}
		$author_id = get_post_field('post_author',$post_id);

		$icons = [];
		$badges = [];

		$members = vibe_projects_get_card_members($post_id);
		
		$duedate = get_post_meta($post_id,'vibe_card_due_date',true);
	
		

		if(!empty($duedate)){
			if($duedate < strtotime(date('Y-m-d', time()) . ' 00:00:00')){
		        $badges[]=[ 
		            'bg'=> '#ffc6c6',
		            'value'=>$duedate,
		            'date'=>date('Y-m-d', $duedate),
		            'key'=>'duedate',
		            'type'=>__('Due date','vibe-projects'),
		            'icon'=> 'vicon vicon-timer',
		            'label'=> __('Overdue','vibe-projects')
		        ];   
		    }else if($duedate < strtotime(date('Y-m-d', time()) . ' 00:00:00')+864000*2){
		        $badges[]=[
		            'bg'=> '#3f86d8',
		            'color'=>'#fff',
		            'value'=>$duedate,
		            'date'=>date('Y-m-d', $duedate),
		            'key'=>'duedate',
		            'type'=>__('Due date','vibe-projects'),
		            'icon'=> 'vicon vicon-timer',
		            'label'=> __('Upcoming','vibe-projects')
		        ];    
		    }
		}
		$startdate = vibe_projects_get_card_start_date($post_id);
		if(!empty($startdate) && is_numeric($startdate)){
			if(!empty($startdate) && $startdate > time()){
				$badges[]=[
			        'bg'=> '#d0ffdf',
			        'color'=>'#222',
			        'key'=>'startdate',
			        'type'=>__('Start date','vibe-projects'),
			        'icon'=> 'vicon vicon-control-play',
			        'label'=> __('Not started','vibe-projects'),
			        'value'=>$startdate,
			        'date'=>date('Y-m-d',$startdate)
			    ];
			}else{
				$badges[]=[
			        'bg'=> '#a2e3ff',
			        'color'=>'#222',
			        'key'=>'startdate',
			        'type'=>__('Start date','vibe-projects'),
			        'icon'=> 'vicon vicon-control-forward',
			        'label'=> __('Active','vibe-projects'),
			        'value'=>$startdate,
			        'date'=>date('Y-m-d',$startdate)
			    ];
			}
		}

		$labels = get_post_meta($post_id,'vibe_card_label',false);
		if(empty($labels)){
		    $labels = [];
		}   

		$milestone = get_post_meta($post_id,'vibe_project_milestone',false);
		if(!empty($milestone)){
			$icons[]=[
		            'icon'=>'vicon vicon-control-record',
		            'key'=>'milestone',
		            'label'=>__('Milestone','vibe-projects')
		        ];
		}
		$complete=get_post_meta( $post_id,'vibe_card_complete',true);
		if(!empty($complete)){
			$icons[]=[
				'icon'=>'vicon vicon-check',
				'key'=>'complete',
				'label'=>__('Card Complete','vibe-projects')
			];
		}

		$fields = [];
		if(function_exists('vibebp_get_setting')){
			$fields = vibebp_get_setting('create_card_fields','vibe_projects','cards');
		}
		$meta=[];
		if(!empty($fields)){
			foreach($fields['key'] as $i=>$key){
				if(!empty($fields['preload']) && !empty($fields['preload'][$i])){
					$meta[]=['meta_key'=>$key,'meta_value'=>get_post_meta($post_id,$key,true)];
				}
			}
		}
		//Custom Fields processing

		$result = apply_filters('vibe_projects_get_card', array( 
		    'card_id'=>$post_id,
		    'id'=>'card_'.$post_id,
		    'title'=>get_the_title($post_id),
		    'description'=>get_the_content($post_id),
		    'cover' => get_the_post_thumbnail_url($post_id),
		    'list' => $terms,
		    'members'=> empty($members)?[$author_id]:$members,
		    'labels'=>$labels,
		    'icons'=>$icons,
		    'badges'=>$badges,
		    'meta'=>$meta,
		    'dependencies'=>get_post_meta($post_id,'vibe_card_dependency',false),
		    'status'=>get_post_status($post_id),
		    'progress'=>vibe_projects_get_card_progress($post_id)
		));

		wp_cache_set('card_'.$post_id,$result,'cards');
	}

	return $result;

}


function vibe_projects_get_statuses($type){

	$statuses = [
    	['value'=>'publish','label'=>__('Live','vibe-projects'),'color'=>'#46c37c'],
    	['value'=>'draft','label'=>__('Draft.','vibe-projects'),'color'=>'#d1d1d1'],
    	['value'=>'trash','label'=>__('Archive.','vibe-projects'),'color'=>'#d1d1d1'],
    ];
    $types = $type.'s';
    $saved_statuses=[];
    if(function_exists('vibebp_get_setting')){
    	$saved_statuses=vibebp_get_setting($type.'_status','vibe_projects',$types);
    }
    
    if(!empty($saved_statuses)){
    	foreach($saved_statuses['key'] as $i=>$status){
    		$statuses[]=['value'=>$status,'label'=>$saved_statuses['label'][$i],'color'=>$saved_statuses['color'][$i]];
    	}
    }

    
    return $statuses;
}

    
function vibe_projects_user_can($cap,$member_type){
	$capabilities=[];
	
	if(function_exists('vibebp_get_setting')){

		if(strpos($cap,'project') != false){
			$val=vibebp_get_setting('project_capability','vibe_projects','projects');	
		}else if(strpos($cap,'board') != false){
			$val=vibebp_get_setting('board_capability','vibe_projects','boards');		
		}else if(strpos($cap,'card') != false){
			$val=vibebp_get_setting('card_capability','vibe_projects','cards');		
		}


		
		if(!empty($val['key'])){
			foreach($val['key'] as $i=>$v){
				if($v == $member_type){
					if(in_array($cap,$val['capabilities'][$i])){
						return true;
					}else{
						return false;
					}
				}
			}
		}
	}

	return true;
}

function vibe_projects_get_milestones($project_id){
	global $wpdb;
	$milestones = $wpdb->get_results($wpdb->prepare("
				SELECT post_id
				FROM  {$wpdb->postmeta} 
				WHERE meta_key = 'vibe_project_milestone' AND meta_value = %d",$project_id));
	if(!empty($milestones)){
		$milestones = wp_list_pluck($milestones,'post_id');
	}
	return $milestones;
}

function vibe_projects_get_progress($project_id,$criteria = null){

	if(empty($criteria)){
		$criteria = get_post_meta($project_id,'vibe_project_progress_criteria',true);
	}
	$progress = 0;
	global $wpdb;
	switch($criteria){
		case 'milestones':
			$card_ids = $wpdb->get_results($wpdb->prepare("
				SELECT post_id
				FROM  {$wpdb->postmeta} 
				WHERE meta_key = 'vibe_project_milestone' AND meta_value = %d",$project_id));
			if(!empty($card_ids)){
				$card_ids = wp_list_pluck($card_ids,'post_id');
				$completed_cards = $wpdb->get_results("
				SELECT count(*)
				FROM  {$wpdb->postmeta} 
				WHERE post_id IN (".implode(',',$card_ids).") AND meta_key = 'vibe_card_complete' AND meta_value = 1");
				$progress = round(100*(intval($completed_cards)/count($card_ids)),2);
			}

		break;
		case 'time':
			$start_date = vibe_projects_get_start_date($project_id);
			$end_date = vibe_projects_get_end_date($project_id);
			$time = time();
			if(empty($start_date) || empty($end_date) || strtotime($start_date) > $time)
				return 0;
			else if($time >= $end_date){
				$progress= 100;
			}else{
				$d = (strtotime($end_date) - strtotime($start_date));
			    if(empty($d)){$d=1;}
				$progress = round(100*(($time - strtotime($start_date))/$d),2);
			}
		break;
		default:
			$card_ids = $wpdb->get_results($wpdb->prepare("
				SELECT post_id
				FROM  {$wpdb->postmeta} 
				WHERE meta_key = 'vibe_card_project' AND meta_value = %d",$project_id));


			if(!empty($card_ids)){
				$card_ids = wp_list_pluck($card_ids,'post_id');
				

				$completed_cards = $wpdb->get_results("
				SELECT post_id
				FROM  {$wpdb->postmeta} 
				WHERE post_id IN (".implode(',',$card_ids).") 
				AND meta_key = 'vibe_card_complete' AND meta_value > 1");

				$count = count($card_ids);

				
				if(empty($count)){$count=1;}
				$progress= round(100*(intval(count($completed_cards))/$count),2);
			}

		break;
	}

	if(!is_numeric($progress)){$progress=0;}
	if($progress > 100){$progress = 100;}
	return apply_filters('vibe_projects_get_progress',intval($progress),$project_id);
}


function vibe_projects_get_completed_cards($project_id){
	global $wpdb;
	$results = $wpdb->get_results($wpdb->prepare("
		SELECT m.post_id
		FROM  {$wpdb->postmeta} as m LEFT JOIN {$wpdb->postmeta} as m1 ON m.post_id = m1.post_id
		WHERE m.meta_key = 'vibe_card_complete' AND m1.meta_vaue = 'vibe_card_project' AND m1.meta_value = %d AND m.meta_value = %d",$project_id,$project_id),ARRAY_A);

	if(!empty($results)){
		return wp_list_pluck($results,'post_id');
	}
	return [];
}

function vibe_projects_get_stats($project_id,$type){

	global $wpdb,$bp;
    switch($type){
        case 'member_count':
        	$return = $wpdb->get_var($wpdb->prepare(
			"SELECT count(user_id) FROM {$wpdb->usermeta} WHERE meta_key = 'vibe_project' AND meta_value = %d",$project_id));

        break;
        case 'milestone_count':
        	$return = $wpdb->get_var($wpdb->prepare("
				SELECT count(post_id)
				FROM  {$wpdb->postmeta} 
				WHERE meta_key = 'vibe_project_milestone' AND meta_value = %d",$project_id));
        break;
        case 'card_count':
        	$return = $wpdb->get_var($wpdb->prepare("
				SELECT count(post_id)
				FROM  {$wpdb->postmeta} 
				WHERE meta_key = 'vibe_card_project' AND meta_value = %d",$project_id));
        break;
        case 'board_count':
        	$return = $wpdb->get_var($wpdb->prepare("
				SELECT count(ID)
				FROM  {$wpdb->posts} 
				WHERE post_type = 'board' 
				AND post_status = 'publish'
				AND post_parent = %d",$project_id));
        break;
        case 'activity_count':
        	$return = $wpdb->get_var($wpdb->prepare("
        		SELECT count(*)
        		FROM {$bp->activity->table_name}
        		WHERE component = 'vibe_projects'
        		AND item_id = %d
    		",$project_id));
        break;
    }

    if(empty($return)){$return = 0;}

    return $return;
}

function vibe_projects_get_most_active_member($project_id){
	global $wpdb,$bp;
	$member = $wpdb->get_results($wpdb->prepare("
		SELECT user_id,count(*) as count
		FROM {$bp->activity->table_name}
		WHERE component = 'vibe_projects'
		AND item_id = %d
		GROUP BY user_id
		ORDER BY count DESC
		LIMIT 0,1
	",$project_id),ARRAY_A);

	if(!empty($member)){
		$member_id = $member[0]['user_id'];
		$activity_count = $member[0]['count'];

	}else{
		$member_id = get_post_field('post_author',$project_id,'raw');
		$count= 1;
	}
	$card_count =0;
	if(!empty($member_id)){
		$card_count = $wpdb->get_var($wpdb->prepare("
		SELECT count(*) 
		FROM {$wpdb->postmeta} as m1
		INNER JOIN {$wpdb->postmeta} as m2 
		ON m1.post_id = m2.post_id
		WHERE m1.meta_key = 'vibe_card_project' AND m1.meta_value = %d 
		AND m2.meta_key = 'vibe_project_card_member' AND m2.meta_value = %d ",$project_id,$member_id));	
	}
	
	return [
		'id'=>$member_id,
		'activity_count'=>$activity_count,
		'card_count'=>$card_count,
		'name'=>bp_core_get_user_displayname($member_id),
		'avatar'=>bp_core_fetch_avatar(array(
            'item_id'=>$member_id,
            'object' => 'user',  
            'type' => 'thumb',  
           'html'=>false
        ))
	];
}

function vibe_projects_get_most_active_card($project_id){
	global $wpdb,$bp;
	
	$all_filters = vibe_projects_registered_activities();
	$card_activities=[];
	foreach($all_filters as $key=>$val){
		if( strpos($key, 'card_') !== false){
			$card_activities[]="'".$key."'";
		}
	}
	$card = $wpdb->get_results($wpdb->prepare("
		SELECT secondary_item_id,count(*) as count
		FROM {$bp->activity->table_name}
		WHERE component = 'vibe_projects'
		AND item_id = %d
		AND type IN (".implode(',',$card_activities).")
		GROUP BY secondary_item_id
		LIMIT 0,1
	",$project_id),ARRAY_A);

	
	if(empty($card)){
		$card_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s and meta_value = %d LIMIT 0,1",'vibe_card_project',$project_id));
	}else{
		$card_id = $card[0]['secondary_item_id'];
	}

	if(!empty($card_id)){
		$card=vibe_projects_get_task_object($card_id);

		return $card;
	}
	
	

	return 0;
}


function vibe_projects_get_card_progress($card_id){

	$complete = get_post_meta($card_id,'vibe_card_complete',true);
	if(!empty($complete)){
		return 100;
	}
	$progress = get_post_meta($card_id,'vibe_card_progress',true);

	if(!empty($progress)){
		return $progress;
	}
	global $wpdb;
	$is_milestone = get_post_meta($card_id,'vibe_project_milestone',true);
	$flag = 1;
	if(empty($is_milestone)){
		global $wpdb;
		$child_cards = $wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d",$card_id),ARRAY_A);
		if(!empty($child_cards)){
			$flag= 0;
			$count=0;
			$total = count($child_cards);
			foreach($child_cards as $child_card_id){
				$check = get_post_meta($child_card_id,'vibe_card_complete',true);
				if(!empty($check)){
					$count++;
				}
			}
			if(empty($total)){return 0;}
			return round((100*$count/$total),2);
		}
	}

	if($flag){
		$checklists = get_post_meta($card_id,'vibe_card_checklist',false);	
		$total =0;
		$count =0;

		if(!empty($checklists)){
			
			foreach($checklists as $checklist){
				if(!empty($checklist)){
					foreach($checklist as $item){
						if(!empty($item['tasks'])){
							foreach($item['tasks'] as $task){
								$total++;
								if(!empty($task['complete'])){$count++;}
							}
						}
					}
				}
			}
		}
		if(empty($total)){return 0;}
		return round((100*$count/$total),2);
	}
	
	return 0;
}

function vibe_projects_is_member($member_id,$project_id){
	$projects = get_user_meta($member_id,'vibe_project',false);	
	return in_array($project_id,$projects);
}

function vibeProjectsConvertPhpToJsMomentFormat(string $phpFormat){
    $replacements = [
        'A' => 'A',      // for the sake of escaping below
        'a' => 'a',      // for the sake of escaping below
        'B' => '',       // Swatch internet time (.beats), no equivalent
        'c' => 'YYYY-MM-DD[T]HH:mm:ssZ', // ISO 8601
        'D' => 'ddd',
        'd' => 'DD',
        'e' => 'zz',     // deprecated since version 1.6.0 of moment.js
        'F' => 'MMMM',
        'G' => 'H',
        'g' => 'h',
        'H' => 'HH',
        'h' => 'hh',
        'I' => '',       // Daylight Saving Time? => moment().isDST();
        'i' => 'mm',
        'j' => 'D',
        'L' => '',       // Leap year? => moment().isLeapYear();
        'l' => 'dddd',
        'M' => 'MMM',
        'm' => 'MM',
        'N' => 'E',
        'n' => 'M',
        'O' => 'ZZ',
        'o' => 'YYYY',
        'P' => 'Z',
        'r' => 'ddd, DD MMM YYYY HH:mm:ss ZZ', // RFC 2822
        'S' => 'o',
        's' => 'ss',
        'T' => 'z',      // deprecated since version 1.6.0 of moment.js
        't' => '',       // days in the month => moment().daysInMonth();
        'U' => 'X',
        'u' => 'SSSSSS', // microseconds
        'v' => 'SSS',    // milliseconds (from PHP 7.0.0)
        'W' => 'W',      // for the sake of escaping below
        'w' => 'e',
        'Y' => 'YYYY',
        'y' => 'YY',
        'Z' => '',       // time zone offset in minutes => moment().zone();
        'z' => 'DDD',
    ];

    // Converts escaped characters.
    foreach ($replacements as $from => $to) {
        $replacements['\\' . $from] = '[' . $from . ']';
    }

    return strtr($phpFormat, $replacements);
}
