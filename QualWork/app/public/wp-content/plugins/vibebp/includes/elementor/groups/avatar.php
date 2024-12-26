<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Groups_Avatar extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{

    public function get_name() {
		return 'group_avatar';
	}

	public function get_title() {
		return __( 'Group Avatar', 'vibebp' );
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

		$init = VibeBP_Init::init();
		$init->get_settings();
		if(!empty($init->settings['bp']['bp_avatar_full_width'])){
			$width= $init->settings['bp']['bp_avatar_full_width'];
		}else{
			$width = 320;
		}

		$this->add_control(
			'width',
			[
				'label' =>__('Image Width', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range'=>[
					'min' => 48,
					'max' => $width,
					'step' => 1,
				],
				'default' => [
					'size'=>320,
				]
			]
		);
		$this->add_control(
			'border-radius',
			[
				'label' =>__('Border Radius', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range'=>[
					'min' => 48,
					'max' => round($width/2,0),
					'step' => 1,
				],
				'default' => [
					'size'=>0,
				]
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
		
		
		$src =  bp_core_fetch_avatar(array(
            'item_id' => $group_id,
            'object'  => 'group',
            'type'=>'full',
            'html'    => false
        ));

        if(empty($src)){
        	$src = plugins_url('../../../assets/images/avatar.jpg',__FILE__);
        }

        $style ='';
        if(!empty($settings['width'])){
        	$style .= 'width:'.$settings['width']['size'].$settings['width']['unit'].';';
        }
        if(!empty($settings['border-radius'])){
        	$style .= 'border-radius:'.$settings['border-radius']['size'].$settings['border-radius']['unit'].';';
        }
        echo '<a href="'.bp_get_group_permalink($group).'"><img src="'.$src.'" class="'.$settings['style'].'" '.(empty($style)?'':'style="'.$style.'"').' /></a>';
	}

}