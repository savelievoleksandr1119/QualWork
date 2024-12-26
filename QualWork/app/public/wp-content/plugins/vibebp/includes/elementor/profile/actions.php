<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Profile_Actions extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{



    public function get_name() {
		return 'member_profile_actions';
	}

	public function get_title() {
		return __( 'Member Profile Actions', 'vibebp' );
	}

	public function get_icon() {
		return 'vicon vicon-bolt-alt';
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

		

		$profile_data = apply_filters('vibebp_member_profile_actions',array(
			'view'=>__('View Profile','vibebp')
		));

		if(function_exists('bp_is_active') && bp_is_active('messages')){
			$profile_data['send_message'] =__('Send Message','vibebp');
		}

		if(bp_is_active('friends')){
			$profile_data['add_friend'] =__('Add Friend','vibebp');
		}
		if(vibebp_get_setting('bp_followers','bp','general')){
			$profile_data['follow'] = __('Follow Member','vibebp');
		}


		$this->add_control(
			'action',
			[
				'label' => __( 'Select Action', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => apply_filters('vibebp_profile_actions',$profile_data)
			]
		);

		$this->add_control(
			'action_label',
			[
				'label' => __( 'Action Label', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'raw' =>''
			]
		);
		$this->add_control(
			'post_action_label',
			[
				'label' => __( 'Post action Label', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'raw' =>''
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .profile_data_action>.member_action>.button' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'background_color',
			[
				'label' => __( 'Background Color', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .profile_data_action>.member_action>.button' => 'background: {{VALUE}}',
				],
			]
		);
		$this->end_controls_section();

	}

	protected function render() {

		$settings = $this->get_settings_for_display();
		$blog_id = '';
		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			$user_id = get_current_user_id();
		}

        echo '<span class="profile_data_action">'.$this->get_profile_action($settings['action'],$settings['action_label'],$settings['post_action_label']).'</span>';

        
		$vibebp_elementor=VibeBP_Elementor_Init::init();
		add_action('wp_footer',array($vibebp_elementor,'event_button'),999);
		wp_enqueue_script('vibebp-members-actions',plugins_url('../../../assets/js/actions.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION);
		wp_localize_script('vibebp-members-actions','vibebpactions',apply_filters('vibebpactions_translations',array(
			'api_url'=>apply_filters('vibebp_rest_api',get_rest_url($blog_id,Vibe_BP_API_NAMESPACE)),
			'friends'=>bp_is_active('friends')?1:0,
			'followers'=>vibebp_get_setting('followers','bp','general')?1:0,
			'translations'=>array(
				'message_text'=>__('Type message','vibebp'),
				'message_subject'=>__('Message subject','vibebp'),
				'cancel'=>__('Cancel','vibebp'),
				'offline'=>__('Offline','vibebp'),
			)
		)));

       	

	}
	

	function get_profile_action($action,$label,$post_action_label){

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
		
		
		switch($action){
			case 'view':
				return '<a class="member_action view_profile" href="'.bp_core_get_user_domain($user_id).'"><span class="button is-primary">'.$label.'</span></a>';
			break;
			case 'send_message':
				return '<a class="member_action send_message" data-member="'.$user_id.'"><span class="button is-primary">'.$label.'</span><span class="hide">'.$post_action_label.'</span></a>';
			break;
			case 'add_friend':
				return '<a class="member_action friend" data-member="'.$user_id.'"><span class="button is-primary">'.$label.'</span><span class="hide">'.$post_action_label.'</span></a>';
			break;
			case 'follow':
				return '<a class="member_action follow" data-member="'.$user_id.'"><span class="button is-primary">'.$label.'</span><span class="hide">'.$post_action_label.'</span></a>';
			break;
			default:
				do_action('vibe_profile_action_'.$action,$user_id,$label,$post_action_label);
			break;
		}
	}
}