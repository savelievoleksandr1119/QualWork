<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Wall extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{

    public function get_name() {
		return 'member_wall';
	}

	public function get_title() {
		return __( 'Member Wall', 'vibebp' );
	}

	public function get_icon() {
		return 'vicon vicon-flag-alt-2';
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
		

		$this->end_controls_section();
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
		?>
		<div class="vibebp_user_wall" data-user="<?php echo $user_id;?>"></div>
		<?php
		wp_enqueue_script('vibebp-members-wall',plugins_url('../../../assets/js/wall.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION);

		wp_enqueue_script('vibebplogin');
		$rg= VibeBP_Register::init();
		wp_localize_script('vibebplogin','vibebp',$rg->get_vibebp());
	}

}