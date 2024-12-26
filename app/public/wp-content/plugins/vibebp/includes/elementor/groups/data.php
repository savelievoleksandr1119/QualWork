<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Group_Data extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{



    public function get_name() {
		return 'group_data';
	}

	public function get_title() {
		return __( 'Group Data', 'vibebp' );
	}

	public function get_icon() {
		return 'vicon vicon-direction';
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

		$profile_data = apply_filters('vibebp_group_layout_data_element',array(
					'group_type' =>__('Group Type','vibebp'),
					'group_status' =>__('Group Status','vibebp'),
					'last_active' =>__('Last Active','vibebp'),
					'create_date' =>__('Creation Date','vibebp'),
					'last_status_update' =>__('Last Status update','vibebp'),
					'moderator_count' =>__('Moderator Count','vibebp'),
					'admin_count' =>__('Administrator Count','vibebp'),
					'member_count' =>__('Member Count','vibebp'),
					'join_button' =>__('Join/Leave Button','vibebp'),
				));

		
		$this->add_control(
			'data',
			[
				'label' => __( 'Group Data', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'last_active',
				'options' => apply_filters('vibebp_elementor_group_data',$profile_data)
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
				
			

        echo '<div class="group_data_field">';
   		if(empty($group)){
   			$init->group = groups_get_group($group_id);
   		}

        echo $this->get_group_data($settings['data'],$group_id);
        echo '</div>';
	}

	function get_group_data($type,$group_id){


		if(empty($this->group) || $this->group->id != $group_id){
			$this->group = groups_get_group($group_id);	
		}

		switch($type){
			case 'last_active':
				$time = groups_get_groupmeta( $group_id, 'last_activity',true);
				if(empty($time)){
					$time = bp_core_current_time();
				}
				if(!empty($time))
				echo bp_core_time_since($time);
			break;
			case 'create_date':
				echo bp_get_group_date_created($this->group);
			break; 
			case 'creator_name':
				echo bp_get_group_creator_username($group_id);
			break;
			case 'admin_count':
				echo count( groups_get_group_admins( $group_id ) );
			break; 
			case 'moderator_count':
				echo count( groups_get_group_mods( $group_id ) );
			break; 
			case 'member_count':
				echo bp_get_group_total_members($this->group);
			break;
			case 'group_status':
				echo bp_get_group_status($this->group);
			break;
			case 'join_button':
				echo '<a class="button is-primary join_group_button" data-status="'.$this->group->status.'" data-id="'.$group_id.'">'.__('Join','vibebp').'</a>';
				$vibebp_elementor=VibeBP_Elementor_Init::init();
				add_action('wp_footer',array($vibebp_elementor,'join_button'));
			break;
			case 'group_type':
				if(!empty(bp_groups_get_group_type( $group_id))){
						echo bp_groups_get_group_type_object(bp_groups_get_group_type( $group_id))->name;	
				}
			break;
			default:
				do_action('vibebp_get_group_data',$type,$group_id);
			break;
		}
	}
}