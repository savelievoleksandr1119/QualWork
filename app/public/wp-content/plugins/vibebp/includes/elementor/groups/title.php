<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Groups_Title extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{

    public function get_name() {
		return 'group_title';
	}

	public function get_title() {
		return __( 'Group Title', 'vibebp' );
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
			'font_size',
			[
				'label' =>__('Font Size', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range'=>[
					'min' => 48,
					'max' => 500,
					'step' => 1,
				],
				'default' => [
					'size'=>24,
				]
			]
		);
		$this->add_control(
			'font_family',
			[
				'label' =>__('Font Family', 'vibebp'),
				'type' => \Elementor\Controls_Manager::FONT,
				'default'=> "'Open Sans', sans-serif",
				'selectors' => [
					'{{WRAPPER}} .title' => 'font-family: {{VALUE}}',
				],
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
					'image_perspective' =>__('Perspective','vibebp'),
					'image_shadow' =>__('Shadow','vibebp'),
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

		


		$title = bp_get_group_name($group);
		if(empty($title)){
			$title = 'Group Title';
		}
		

        $style ='';
        if(!empty($settings['font_size'])){
        	$style .= 'font-size:'.$settings['font_size']['size'].'px;';
        }

        echo '<h2 class="title '.$settings['style'].'" style="font-family: ' . $settings['font_family'] . ';'.$style.'"><a href="'.bp_get_group_permalink( $group).'">'.$title.'</a></h2>';
	}

}