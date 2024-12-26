<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Groups_Members extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{

    public function get_name() {
		return 'group_members';
	}

	public function get_title() {
		return __( 'Group Members', 'vibebp' );
	}

	public function get_icon() {
		return 'dashicons dashicons-businessman';
	}

	public function get_categories() {
		return [ 'vibebp' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Controls', 'vibebp' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		
		$this->add_control(
			'role',
			[
				'label' => __( 'Group Role', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''=>__('All','vibebp'),
					'members' =>__('Members [Excluding Admin & Mods]','vibebp'),
					'mods' =>__('Moderators','vibebp'),
					'admin' =>__('Administrators','vibebp'),
				)
			]
		);

		$this->add_control(
			'style',
			[
				'label' => __( 'Style', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					'' =>__('Default','vibebp'),
					'clean' =>__('Clean','vibebp'),
					'names' =>__('Names','vibebp'),
					'pop_names' =>__('Names Popup','vibebp'),
				)
			]
		);
	}

	protected function render() {

		$settings = $this->get_settings_for_display();
		global $bp;

		global $groups_template;
		$group_id = 0;$group = '';
		$init = VibeBP_Init::init();
		if(!empty($init->group)){
			$group_id = $init->group->id;
			if(!empty($init->group_id) && $init->group_id != $init->group->id){
				$init->group = groups_get_group($init->group_id);
			}
			$group = $init->group;
		}
		if(empty($group_id) && !empty($init->group_id)){
			$init->group = groups_get_group($init->group_id);
			$group_id = $init->group_id;
			$group = $init->group;
		}

		if(empty($group_id) && !empty($groups_template)){
			$group_id = $groups_template->group->id;
			$init->group = $groups_template->group;
			$group = $init->group;
		}
		
			

		if(empty($group_id) && !empty($bp->groups) && !empty($bp->groups->current_group)){
			$group_id = $bp->groups->current_group->id;
			$init->group = $bp->groups->current_group;
			$group = $bp->groups->current_group;
		}else if(empty($group_id)){
			$group_id = 1;
			$init->group = groups_get_group($group_id);
			$group = $init->group;
		}
		
		global $wpdb;

		
		$q = $wpdb->prepare("
			SELECT user_id as id FROM {$bp->groups->table_name_members} WHERE group_id = %d",$group_id);

		if($settings['role'] == 'members'){
			$q = $wpdb->prepare("
			SELECT user_id as id FROM {$bp->groups->table_name_members} WHERE group_id = %d AND is_mod !=1 AND is_admin !=1",$group_id);
		}
		if($settings['role'] == 'mods'){
			$q = $wpdb->prepare("
			SELECT user_id as id FROM {$bp->groups->table_name_members} WHERE group_id = %d AND is_mod = 1",$group_id);
		}
		if($settings['role'] == 'admin'){
			$q = $wpdb->prepare("
			SELECT user_id as id FROM {$bp->groups->table_name_members} WHERE group_id = %d AND is_admin = 1",$group_id);
		}

		$members = $wpdb->get_results($q,ARRAY_A);
		
        echo '<div class="vibebp_group_members '.$settings['style'].'"> ';
        if(!empty($members)){
        	foreach($members as $member){ 
        		
    			echo '<div class="vibebp_member">';
	        		echo '<a href="'.bp_core_get_user_domain($member['id']).'"><img src="'.bp_core_fetch_avatar(array('item_id'=>$member['id'],'object'  => 'user','type'=>'full','html'    => false)).'" ></a>';
	        		echo '<span>'.bp_core_get_user_displayname($member['id']).'</span>';
        		echo '</div>';
	        		
        		
        	}
        }
        echo ' </div>';
	}

}