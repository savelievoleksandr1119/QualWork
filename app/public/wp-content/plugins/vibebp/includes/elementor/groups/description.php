<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Groups_Description extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{


    public function get_name() {
		return 'group_description';
	}

	public function get_title() {
		return __( 'Group Description', 'vibebp' );
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
			'font_Size',
			[
				'label' =>__('Font Size', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range'=>[
					'min' => 48,
					'max' => 100,
					'step' => 1,
				],
				'default' => [
					'size'=>12,
				],
				'selectors' => [
					'{{WRAPPER}} .group_description' => 'font-size: {{SIZE}}px',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .group_description' => 'color: {{VALUE}}',
				],
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

		$description = bp_get_group_description($init->group);
		if(empty($description)){
			$description = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.";
		}
		

        echo '<div class="group_description"> '.$description.' </div>';
	}

}