<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Field extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{


    public function get_name() {
		return 'member_field';
	}

	public function get_title() {
		return __( 'Member Profile Field', 'vibebp' );
	}

	public function get_icon() {
		return 'vicon vicon-direction-alt';
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
						//$field = xprofile_get_field( $field->id );
						$fields[$field->id]=$field->name;
						$field_names[$field->name]=$field->name;
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
			'field_name',
			[
				'label' => esc_html__( 'Field Name', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::HIDDEN,
				//'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $field_names,
			]
		);

		$this->add_control(
			'font_size',
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
					'{{WRAPPER}} .vibebp_profile_field' => 'font-size: {{SIZE}}px',
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
					'{{WRAPPER}} .vibebp_profile_field' => 'color: {{VALUE}}',
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
					'stacked' =>__('Stacked','vibebp'),
					'spaced'=>__('Spaced','vibebp'),
					'nolabel'=>__('No Label','vibebp'),
					'icon'=>__('Icons','vibebp'),
				)
			]
		);

		$this->end_controls_section();
	}

	protected function render() {

		$settings = $this->get_settings_for_display();
		$loggedinUser = get_current_user_id();
		$init = VibeBP_Init::init();
		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
			$init->user_id = $user_id;
		}else{
			global $members_template;
			if(!empty($members_template->member)){
				$user_id = $members_template->member->id;
				$init->user_id = $user_id;
			}
		}
		if(empty($user_id)){
			
			if(!empty($init->user_id)){
				$user_id = $init->user_id;
			}else{
				$user_id = $loggedinUser;
			}
		}

		

		
		$field = xprofile_get_field( $settings['field_id'] );
		if(empty($field)){
			return;
		}
		$can_display=false;
		$check_display = xprofile_get_field_visibility_level( $settings['field_id'], $user_id );
		switch($check_display){
			case 'adminsonly':
				if(current_user_can('manage_options') || $user_id==$loggedinUser)
					$can_display = true;
			break;
			case 'friends':
				if(current_user_can('manage_options') || (function_exists('friends_check_friendship_status') && friends_check_friendship_status($user_id,$loggedinUser)))
					$can_display = true;
			break;
			case 'public':
				$can_display = true;
			break;
			case 'loggedin':
				if(!empty($loggedinUser))
					$can_display = true;
			break;
			default:
				$can_display = true;
			break;
		}

		if(!$can_display){
			return;
		}
		$value = xprofile_get_field_data( $settings['field_id'], $user_id);
		$value = vibebp_process_profile_field_data($value,$field,$user_id,$settings);

		?>
		<div class="vibebp_profile_field field_<?php echo $settings['field_id'].' '.$settings['style']; ?> " <?php  echo 'style="color:'.$settings['text_color'].';font-size:'.$settings['font_size']['size'].'px"'; ?>>
			<?php
				if($settings['style'] == 'icon'){
					?><label class="<?php echo $field->name ?>"></label><?php
				}else if($settings['style'] != 'nolabel' ){
					?><label><?php echo $field->name ?></label><?php
				}
			?>
			<div class="<?php echo sanitize_title($field->name).' field_type_'.$field->type;?> "><?php echo do_shortcode(apply_filters('vibebp_profile_field_block_value',$value,$field)); ?></div>
		</div>
		<?php
	}

}