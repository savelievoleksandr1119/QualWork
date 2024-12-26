<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Location extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{


    public function get_name() {
		return 'member_avatar';
	}

	public function get_title() {
		return __( 'Member Location Google Map', 'vibebp' );
	}

	public function get_icon() {
		return 'vicon vicon-map-alt';
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

		$groups = array();
		$fields = array(''=>__('Select Field','vibebp'));
		$field_names = [''=>__('Select Field','vibebp')];

		if(function_exists('bp_xprofile_get_groups')){
			$groups = bp_xprofile_get_groups( array(
				'fetch_fields' => true
			) );
		}
		
		if(!empty($groups)){
			foreach($groups as $group){
				if(!empty($group->fields)){
					foreach ( $group->fields as $field ) {
						if($field->type == 'location'){
							$fields[$field->id]=$field->name;
							$field_names[$field->name]=$field->name;	
						}
					}
				}
			}
		}

		$this->add_control(
			'field_id',
			[
				'label' => __( 'Select Field', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'name',
				'options' => $fields,
			]
		);

		$this->add_control(
			'width',
			[
				'label' =>__('Max Map Width', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range'=>[
					'min' => 10,
					'max' => 100,
					'step' => 1,
				],
				'default' => [
					'size'=>100,
				]
			]
		);

		$this->add_control(
			'height',
			[
				'label' =>__('Max Map Height', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range'=>[
					'min' => 10,
					'max' => 100,
					'step' => 1,
				],
				'default' => [
					'size'=>100,
				]
			]
		);

		$this->add_control(
			'style',
			[
				'label' => __( 'Map Style', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					'' =>__('Default','vibebp'),
					'image_perspective' =>__('Perspective','vibebp'),
					'image_shadow' =>__('Shadow','vibebp'),
				)
			]
		);
	}

	protected function render() {

		$settings = $this->get_settings_for_display();

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
			}else{
				$user_id = get_current_user_id();
			}
		}
		
		
		$field = xprofile_get_field( $settings['field_id'] );

        if(!empty($field)){

        }

        $style ='';
        if(!empty($settings['width'])){
        	$style .= 'width:'.$settings['width']['size'].$settings['width']['unit'].';';
        }
        if(!empty($settings['border-radius'])){
        	$style .= 'border-radius:'.$settings['border-radius']['size'].$settings['border-radius']['unit'].';';
        }
        echo '<a href="'.bp_core_get_user_domain( $user_id ).'"><img src="'.$src.'" class="'.(empty($settings['style'])?'':$settings['style']).'" '.(empty($style)?'':'style="'.$style.'"').' /></a>';
	}

}