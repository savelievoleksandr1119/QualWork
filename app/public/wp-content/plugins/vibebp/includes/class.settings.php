<?php

if ( ! defined( 'ABSPATH' ) ) exit;

include_once 'settings/groups.php';
include_once 'settings/member_types.php';
include_once 'settings/field_types.php';
include_once 'settings/emails.php';

class VibeBP_Settings{


	public static $instance;
    public static function init(){

        if ( is_null( self::$instance ) ) 
            self::$instance = new VibeBP_Settings();
        return self::$instance;
    }

	private function __construct(){
		
		add_action( 'add_meta_boxes', array($this,'member_profile_card'));
		add_action( 'save_post_member-profile', array($this,'save_member_profile_card' ),10,1);
		add_action( 'save_post_member-card', array($this,'save_member_profile_card' ),10,1);
		add_action('bp_members_admin_user_metaboxes',array($this,'user_metabox'),10,2);
		add_action( 'bp_members_admin_load', array( $this, 'process_member_profile_update' ) );
		add_action( 'add_meta_boxes', array($this,'group_layout_card'));
		add_action('bp_groups_admin_meta_boxes',array($this,'set_group_layout'));
		add_action( 'save_post_group-layout', array($this,'save_group_layout_card' ),10,1);
		add_action( 'save_post_group-card', array($this,'save_group_layout_card' ),10,1);
		add_action( 'bp_group_admin_edit_after',array($this, 'bp_groups_process_group_layout_update' ));
		add_action('wp_ajax_save_member_card',array($this,'save_member_card'));
		add_action('wp_ajax_update_wallet_credits',array($this,'update_wallet_credits'));

		add_action('wp_ajax_regenerate_service_worker',array($this,'regenerate_service_worker'));
		add_action('wp_ajax_ajax_button_call',array($this,'ajax_button_call'));

		add_action('admin_enqueue_scripts',[$this,'enqueue_scripts']);
		add_action('wp_ajax_userselect_search',array($this,'userselect_search'));

		
	}


	function enqueue_scripts($hook){

		
		if($hook == 'vibe-bp_page_vibebp_settings'){
			wp_enqueue_script('slimselect',plugins_url('../assets/js/slimselect.min.js',__FILE__),array(  ),
				VIBEBP_VERSION,
				true);
			wp_enqueue_style('slimselect',plugins_url('../assets/css/slimselect.css',__FILE__));
		}
	}
	public function vibebp_settings() {
	    $tab = isset( $_GET['tab'] ) ?sanitize_text_field( $_GET['tab']) : 'general';
		$this->vibebp_settings_tabs($tab); 
		$this->get_vibebp_settings($tab);
		do_action('vibebp_settings_page_loaded');
	}

	function vibebp_settings_tabs($tab){
		$tabs = apply_filters('vibebp_settings_tabs',array( 
	    		'general' => __('General','vibebp'), 
	    		'bp' => __('BuddyPress','vibebp'), 
	    		'touch' => __('Touch Points','vibebp'), 
	    		'registration_forms' => __('Registration','vibebp'),
	    		'ai' => __('AI','vibebp'),
    		));

		if(vibebp_get_setting('service_workers')){
			$tabs['service_worker'] = __('Service Worker','vibebp');
		}
		if(vibebp_get_setting('enable_wallet')){
			$tabs['wallet'] = __('Wallet','vibebp');
		}
		
		$tabs['app'] = __('App','vibebp');

	 	$current = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab'] ): 'general';
	    echo '<div id="icon-themes" class="icon32"><br></div>';
	    echo '<h2 class="nav-tab-wrapper">';
	    foreach( $tabs as $tab => $name ){
	        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
	        echo "<a class='nav-tab$class' href='?page=".VIBE_BP_SETTINGS."&tab=$tab'>$name</a>";

	    }
	    echo '</h2>';
	}


	function get_vibebp_settings($tab){
		if(isset($_POST['save'])){
		
			echo $this->vibebp_save_settings($tab);
		}		
		switch($tab){
			case 'bp':
				$this->vibebp_buddypress();
			break; 
			case 'app':
				$this->show_app_form();
			break;
			case 'service_worker':
				$this->service_workers();
			break;
			case 'ai':
				$this->ai();
			break;
			case 'touch':
				$this->touch_points();
			break;
			case 'registration_forms':
				$this->registration_forms();
			break;
			case 'wallet':
				$this->wallet();
			break;
			default:
				$function_name = apply_filters('vibebp_settings_tab',esc_attr($tab));
				if(!empty($tab) && function_exists($function_name) && $tab != 'general' && current_user_can('manage_options')){
					$function_name();
				}else{
					$this->vibebp_general_settings();
				}
				
			break;
		}
		do_action('get_vibebp_settings',$tab);
	}
	

	function ai(){
		echo '<h3>'.__('AI Settings','vibebp').'</h3> ';

		$template_array = apply_filters('vibebp_ai_settings_tabs',array(
			'chatgpt'=> __('ChatGPT','vibebp'),
		));

		echo '<ul class="subsubsub">';

		foreach($template_array as $k=>$value){
			if(empty($_GET['sub']) && empty($current)){
				$current = $k;
			}else if(!empty($_GET['sub']) && empty($current)){
				$current = sanitize_text_field($_GET['sub']);
			}
			echo '<li><a href="?page='.VIBE_BP_SETTINGS.'&tab=ai&sub='.$k.'" '.(($k == $current)?'class="current"':'').'>'.$value.'</a>  &#124; </li>';
		}
		echo '</ul><div class="clear"><hr/>';
		//
		if(!isset($_GET['sub'])){$_GET['sub']='chatgpt';}
		$sub = sanitize_text_field($_GET['sub']);

		switch($sub){
			case 'chatgpt':
				$settings = apply_filters('vibebp_chatgpt_settings',array(
					array(
						'label'=>__('ChatGPT Settings','vibebp' ),
						'type'=> 'heading',
					),
					array(
			            'label' => __('ChatGPT API Key','vibebp'),
			            'name' => 'chatgpt_api_key',
			            'type' => 'text',
			            'desc' => __('Configure API Key for ChatGPT').'<a href="https://platform.openai.com/account/api-keys" class="button" target="_blank">'.__('Get API keys','vibebp').'</a>',
			            'default'=>''
			        )
				));
			break;
			default:
				$settings = apply_filters('vibebp_ai_'.$sub.'_tab',[]);
				do_action('vibebp_ai_'.$sub.'_tab');
			break;
		}

		if(!empty($settings)){
			$this->vibebp_settings_generate_form('ai',$settings,$sub);
		}
	}

	function vibebp_buddypress(){

		$user_domain = bp_core_get_user_domain( get_current_user_id() );
		
		echo '<h3>'.__('BuddyPress General Settings','vibebp').'<a href="'.$user_domain.'?reload_nav=1" class="button-primary" target="_blank">Refresh BuddyPress Navigation</a></h3> ';

		$template_array = apply_filters('vibebp_buddypress_general_settings_tabs',array(
			'general'=> __('General Settings','vibebp'),
		));
		

		echo '<ul class="subsubsub">';

		foreach($template_array as $k=>$value){
			if(empty($_GET['sub']) && empty($current)){
				$current = $k;
			}else if(!empty($_GET['sub']) && empty($current)){
				$current = sanitize_text_field($_GET['sub']);
			}
			echo '<li><a href="?page='.VIBE_BP_SETTINGS.'&tab=bp&sub='.$k.'" '.(($k == $current)?'class="current"':'').'>'.$value.'</a>  &#124; </li>';
		}
		echo '</ul><div class="clear"><hr/>';
		//
		if(!isset($_GET['sub'])){$_GET['sub']='';}
		$sub = sanitize_text_field($_GET['sub']);


		
        if(empty($sub)){
        	$sub = 'general';
        }
        if($sub == 'general'){

			$review_options = array(
				'all'=>__('All','vibebp')
			);
			global $wp_roles;
			$roles = array_keys($wp_roles->roles);
			foreach($roles as $role){
				$review_options[$role]=$wp_roles->roles[$role]['name'];
			}

			$settings = apply_filters('vibebp_bp_settings_'.$sub,array(
				array(
					'label'=>__('Member Management','vibebp' ),
					'type'=> 'heading',
				),
				array(
		            'label' => __('Default Member','vibebp'),
		            'name' => 'default_member_type',
		            'type' => 'member_type',
		            'desc' => __('Select default member type').'<a id="assign_default_member_type" class="button">Apply to existing members who do not have anyu member type assigned</a>',
		            'default'=>''
		        ),
				array(
					'label' => __('Who can Create / View / Edit member information','vibebp'),
					'name' => 'create_member',
					'type' => 'select',
					'options'=>array(
						''=>__('Administrator','vibebp'),
						'edit_posts'=>__('Instructor / Editor +','vibebp'),
						'read'=>__('Everyone','vibebp'),
					),
					'desc' => __('Who can create and edit users in your site from front end.','vibebp'),
					'default'=>''
				),
				array(
					'label'=>__('Additional Components','vibebp' ),
					'type'=> 'heading',
				),
				array(
					'label' => __('Followers','vibebp'),
					'name' => 'bp_followers',
					'type' => 'checkbox',
					'value' => 1,
					'desc' => '',
				),
				array(
					'label' => __('Likes','vibebp'),
					'name' => 'bp_likes',
					'type' => 'checkbox',
					'value' => 1,
					'desc' => '',
				),
				array(
					'label'=>__('Menu Options','vibebp' ),
					'type'=> 'heading',
				),
				array(
					'label' => __('Different Menu For Instructors','vibebp'),
					'name' => 'role_based_menu',
					'type' => 'checkbox',
					'desc' => __('A Different Student and Instructor menu location.','vibebp'),
					'default'=>''
				),
				array(
					'label' => __('Different Menu by Member Types','vibebp'),
					'name' => 'member_type_based_menu',
					'type' => 'checkbox',
					'desc' => __('A different menu location for each member type. ','vibebp'),
					'default'=>''
				),
				array(
					'label'=>__('Dashboard','vibebp' ),
					'type'=> 'heading',
				),
				array(
					'label' => __('Different Dashboard For Member Type','vibebp'),
					'name' => 'member_type_based_dashboard',
					'type' => 'checkbox',
					'desc' => __('A Different dashboard based on member type. IMPORTANT : Enabling this setting ensure that widgets are set in Member Type Sidebars in WP Admin - Appearance - Widgets section, else your dashboard would show up blank.','vibebp'),
					'default'=>''
				),
				array(
					'label'=>__('Accessibility Settings','vibebp' ),
					'type'=> 'heading',
				),
				array(
					'label' => __('Disable Public Profile','vibebp'),
					'name' => 'public_profile',
					'type' => 'checkbox',
					'value' => 1,
					'desc' => _x('Profiles are accessible on internet by anyone. Switching this on disables public access for profiles.','settings','vibebp'),
				),
				array(
					'label' => __('Disable Public Member Directory','vibebp'),
					'name' => 'public_member_directory',
					'type' => 'checkbox',
					'value' => 1,
					'desc' => _x('Disable member directory acess for public. You can add member directory in profile menu.','settings','vibebp'),
				),
				array(
					'label' => __('Disable Groups & Group Directory','vibebp'),
					'name' => 'public_group_directory',
					'type' => 'checkbox',
					'value' => 1,
					'desc' => _x('Disable group directory access for public. You can add group directory in profile menu.','settings','vibebp'),
				),
				array(
					'label' => __('Disable Public Activities [recommended]','vibebp'),
					'name' => 'public_activity',
					'type' => 'checkbox',
					'value' => 1,
					'desc' => _x('Disable activities for public.','settings','vibebp'),
				),
				array(
					'label'=>__('BuddyPress Settings','vibebp' ),
					'type'=> 'heading',
				),
				array(
					'label' => __('BuddyPress Avatar Full Width (px)','vibebp'),
					'name' => 'bp_avatar_full_width',
					'type' => 'number',
					'desc' => '',
					'default'=>300
				),
				array(
					'label' => __('BuddyPress Avatar Full Height (px)','vibebp'),
					'name' => 'bp_avatar_full_height',
					'type' => 'number',
					'desc' => '',
					'default'=>300
				),
				array(
					'label' => __('BuddyPress Avatar Thumbnail Width (px)','vibebp'),
					'name' => 'bp_avatar_thumb_width',
					'type' => 'number',
					'desc' => '',
					'default'=>150
				),
				array(
					'label' => __('BuddyPress Avatar Thumbnail Height (px)','vibebp'),
					'name' => 'bp_avatar_thumb_height',
					'type' => 'number',
					'desc' => '',
					'default'=>150
				),
			));
			$this->vibebp_settings_generate_form('bp',$settings);
		}else{
        	do_action('vibebp_bp_subtab_'.$sub);
        }
	}

	function vibebp_general_settings(){
		echo '<h3>'.__('General Settings','vibebp').'</h3>';

		$template_array = apply_filters('vibebp_general_settings_tabs',array(
			'general'=> __('General Settings','vibebp'),
			'login'=> __('Login','vibebp'),
			'firebase'=> __('Firebase','vibebp'),
			'editor'=> __('Editor','vibebp'),
			'performance'=> __('Performance','vibebp'),
			'misc'=> __('Misc','vibebp'),
		));
		echo '<ul class="subsubsub">';

		foreach($template_array as $k=>$value){
			if(empty($_GET['sub']) && empty($current)){
				$current = $k;
			}else if(!empty($_GET['sub']) && empty($current)){
				$current = $_GET['sub'];
			}
			echo '<li><a href="?page='.VIBE_BP_SETTINGS.'&tab=general&sub='.$k.'" '.(($k == $current)?'class="current"':'').'>'.$value.'</a>  &#124; </li>';
		}

		echo '</ul><div class="clear"><hr/>';
		
		if(!isset($_GET['sub'])){$_GET['sub']='';}

		$sub = sanitize_text_field($_GET['sub']);

		if(empty($sub)){$sub='general';$current='general';}

		if(empty($this->pages)){
			$query = new WP_Query(array(
				'post_type'=>'page',
				'posts_per_page'=>-1
			));
			$this->pages[]=__('Select page','vibebp');
			while($query->have_posts()){
				$query->the_post();
				$this->pages[get_the_ID()]=get_the_title();
			}
		}

		switch($sub){
			case 'performance':
				if ( ! function_exists( 'get_home_path' ) ) {
		            include_once ABSPATH . '/wp-admin/includes/file.php';
		        }
				$settings = [
					array(
						'label'=>__('Performance Settings','vibebp' ).(file_exists(get_home_path().'/wp-content/mu-plugins/vibe-api-accelerator.php')?'<span class="success_on" title="Performance Accelerator is Enabled"></span>':'<span class="disabled_off" title="Performance Accelerator is OFF"></span>'),
						'name'=>'label',
						'type'=> 'heading',
					),
					array(
						'label' => __('Improve Rest API Performance','vibebp'),
						'name' => 'api_performance',
						'type' => 'checkbox',
						'desc' => __('API Performance Accelerator Status. Page reload required.','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Measure Performance Improvement in Rest API','vibebp'),
						'name' => 'measure_performance',
						'type' => 'rest_api_call',
						'url'=>rest_url(Vibe_BP_API_NAMESPACE.'/measure_performance/?_envelope&upload&_wp_nonce='.wp_create_nonce('wp-rest')),
						'button_label'=>(vibebp_get_setting('api_performance','general','performance') && !file_exists(get_home_path().'/wp-content/mu-plugins/vibe-api-accelerator.php'))?  __('Generate Accelerator','vibebp'):__('Refresh Accelerator Status & Measure Performance','vibebp'),
						'desc' => __('Measure performance impact. If Accelerator generation is stuck, setup mu-plugins folder manually.','vibebp').'<a href="https://wordpress.org/documentation/article/must-use-plugins/" target="_blank">?</a>',
					),
				];
				$this->vibebp_settings_generate_form('general',$settings,'performance');
			break;
			case 'login':
				$settings = [
					array(
						'label'=>__('Login Settings','vibebp' ),
						'name'=>'label',
						'type'=> 'heading',
					),
					array(
						'label' => __('Show Login form open by default','vibebp'),
						'name' => 'email_login',
						'type' => 'checkbox',
						'desc' => __('This only impacts Login form appearance. The Login form shows up as default option.','vibebp'),
						'default'=>'on'
					),
					array(
						'label' => __('Login Terms','vibebp'),
						'name' => 'login_checkbox',
						'type' => 'textarea',
						'desc' => __('Enables a Checkbox on Login. For accepting terms and conditions for logging in. Leave empty to disable. HTML Supported.','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Registration Terms ','vibebp'),
						'name' => 'registration_checkbox',
						'type' => 'textarea',
						'desc' => __('Enables a Checkbox on Registration. For accepting terms and conditions for registering on site. Leave empty to disable. HTML Supported.','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Login Heading','vibebp'),
						'name' => 'login_heading',
						'type' => 'textarea',
						'desc' => __('Login screen heading ','vibebp'),
						'default'=>_x('Welcome back','login','vibebp'),
					),
					array(
						'label' => __('Login message','vibebp'),
						'name' => 'login_message',
						'type' => 'textarea',
						'desc' => __('Login message below heading','vibebp'),
						'default'=>_x('Sign in to experience the next generation of WPLMS 4.0.','login','vibebp'),
					),
					array(
						'label' => __('Login Screen Terms','vibebp'),
						'name' => 'login_terms',
						'type' => 'textarea',
						'desc' => __('Terms and Conditions text in login screen ','vibebp'),
						'default'=>'To make VibeThemes work, we log user data and share it with service providers. Click “Sign in” above to accept VibeThemes’s Terms of Service & Privacy Policy.',
					),
					array(
						'label' => __('SignIn Title','vibebp'),
						'name' => 'signin_email_heading',
						'type' => 'text',
						'desc' => __('Title shown on login popup screen ','vibebp'),
						'default'=>'Sign in with email',
					),
					array(
						'label' => __('Sign In Description','vibebp'),
						'name' => 'signin_email_description',
						'type' => 'textarea',
						'desc' => __('Text shown below login title in popup screen ','vibebp'),
						'default'=>'To login enter the email address associated with your account, and the password.',
					),
					array(
						'label'=>__('Forgot Password Settings','vibebp' ),
						'type'=> 'heading',
						'name'=>'label',
					),
					array(
						'label' => __('Forgot Password Description','vibebp'),
						'name' => 'forgot_password',
						'type' => 'text',
						'desc' => __('Text shown below Forgot Password title in popup screen ','vibebp'),
						'default'=>'Enter the email address associated with your account, and we’ll send a magic link to your inbox.',
					),
					array(
						'label' => __('Use wp default forgot password','vibebp'),
						'name' => 'wp_forgot_password',
						'type' => 'checkbox',
						'desc' => __('Will redirect user to wordpress forgot password. ','vibebp'),
					),
					
					array(
						'label'=>__('Create Account Settings','vibebp' ),
						'type'=> 'heading',
						'name'=>'label',
					),
					array(
						'label' => __('Create Account Title','vibebp'),
						'name' => 'register_account_heading',
						'type' => 'text',
						'desc' => __('Title shown on login popup screen ','vibebp'),
						'default'=>'Join VibeThemes',
					),
					array(
						'label' => __('Create Account Description','vibebp'),
						'name' => 'register_account_description',
						'type' => 'textarea',
						'desc' => __('Text shown below login title in popup screen ','vibebp'),
						'default'=>'Login to connect and check your account, personalize your dashboard, and follow people and chat with them.',
					),
					array(
						'label' => __('Strong password restriction','vibebp'),
						'name' => 'strong_password',
						'type' => 'checkbox',
						'desc' => __('Enable to enforce strong password in Login popup create account ','vibebp'),
					),
					array(
						'label' => __('Show member types in registration','vibebp'),
						'name' => 'member_types_registration',
						'type' => 'checkbox',
						'desc' => __('Shows register form for different member types and roles!','vibebp'),
						'default'=>'',
					),
					array(
						'label' => __('Codes for registration using invite codes','vibebp'),
						'name' => 'registration_invite_codes',
						'type' => 'text',
						'desc' => __('Add comma separated invite codes for users here !','vibebp'),
						'default'=>'',
					),
					array(
						'label' => __('Google reCaptcha V3 public key','vibebp').' '.'<a href="https://www.google.com/recaptcha/admin/create">'.__('Get one','vibebp').'</a>',
						'name' => 'google_captcha_public_key',
						'type' => 'text',
						'desc' => __('Needed for captchas in forms','vibebp'),
						'default'=>'',
					),
					array(
						'label' => __('Google reCaptcha V3 private key','vibebp').' '.'<a href="https://www.google.com/recaptcha/admin/create">'.__('Get one','vibebp').'</a>',
						'name' => 'google_captcha_private_key',
						'type' => 'text',
						'desc' => __('Needed for captchas in forms','vibebp'),
						'default'=>'',
					),
				];
				$this->vibebp_settings_generate_form('general',$settings,'login');
			break;
			case 'firebase':
				$settings = apply_filters('vibebp_firebase_project_settings',[
					array(
						'label'=>__('Firebase Project','vibebp' ),
						'type'=> 'heading',
						'name'=>'label'
					),
					array(
						'label' => __('Enter Firebase Config','vibebp'),
						'name' => 'firebase_config',
						'type' => 'textarea',
						'desc' => __('Firebase Config for web app. ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Enter Firebase private key','vibebp'),
						'name' => 'firebase_private_key',
						'type' => 'textarea',
						'desc' => __('Firebase private key for advance firebase functionalities [ AI, Single Login Sessions, Cache First Service workers ]. ','vibebp').'<a href="https://docs.wplms.io/administrator-guide/step-by-step-guide-for-administrators/login-and-registration#single-session-users-accounts" target="_blank">?</a>',
						'default'=>''
					),
					array(
						'label' => __('Enter Firebase web api key','vibebp'),
						'name' => 'firebase_api_key',
						'type' => 'textarea',
						'desc' => __('Firebase web api key for advance firebase functionalities [ AI, Single Login Sessions, Cache First Service workers ]. ','vibebp').'<a href="https://docs.wplms.io/administrator-guide/step-by-step-guide-for-administrators/login-and-registration#single-session-users-accounts" target="_blank">?</a>',
						'default'=>''
					),
					array(
						'label' => __('Enter Firebase UID','vibebp'),
						'name' => 'firebase_UID',
						'type' => 'text',
						'desc' => __('Firebase UID for admin token generation. ','vibebp').'<a href="https://docs.wplms.io/administrator-guide/step-by-step-guide-for-administrators/login-and-registration#single-session-users-accounts" target="_blank">?</a>',
						'default'=>''
					),
					array(
						'label' => __('Enter Firebase service email','vibebp'),
						'name' => 'firebase_service_email',
						'type' => 'text',
						'desc' => __('Firebase service email for advance firebase functionalities [ AI, Single Login Sessions, Cache First Service workers ]. ','vibebp').'<a href="https://docs.wplms.io/administrator-guide/step-by-step-guide-for-administrators/login-and-registration#single-session-users-accounts" target="_blank">?</a>',
						'default'=>''
					),
					array(
						'label' => __('Use Brand Icons','vibebp'),
						'name' => 'use_brand_icons',
						'type' => 'checkbox',
						'desc' => __('Use brand icons in site.','vibebp'),
						'default'=>'on'
					),
					array(
						'label' => __('Google Login','vibebp'),
						'name' => 'firebase_google_auth',
						'type' => 'checkbox',
						'desc' => __('Google login via firebase,  make sure Google is enabled as Login method in Firebase auth signin. ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Facebook Login','vibebp'),
						'name' => 'firebase_facebook_auth',
						'type' => 'checkbox',
						'desc' => __('Facebook login via firebase,  make sure Facebook is enabled as Login method in Firebase auth signin. ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Twitter Login','vibebp'),
						'name' => 'firebase_twitter_auth',
						'type' => 'checkbox',
						'desc' => __('Twitter login via firebase,  make sure Twitter is enabled as Login method in Firebase auth signin. ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Github Login','vibebp'),
						'name' => 'firebase_github_auth',
						'type' => 'checkbox',
						'desc' => __('Github login via firebase,  make sure Github is enabled as Login method in Firebase auth signin. ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Apple Login','vibebp'),
						'name' => 'firebase_apple_auth',
						'type' => 'checkbox',
						'desc' => __('Apple ID login via firebase,  make sure Apple is enabled as Login method in Firebase auth signin. ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Microsoft Login','vibebp'),
						'name' => 'firebase_microsoft_auth',
						'type' => 'checkbox',
						'desc' => __('Microsoft login via firebase. ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Prevent simultaneous logins','vibebp'),
						'name' => 'session_lock',
						'type' => 'select',
						'options'=>array(
							''=>__('None','vibebp'),
							'loggedin_no_login'=>__('Do not log in if already logged in','vibebp'),
							'loggedin_logout_all'=>__('Logout all existing and log in','vibebp'),
						),
						'desc' => __('Prevents simultanous login of one user account , checks if user is online from firebase itself! ','vibebp'),
						'default'=>''
					)
				]);
				$this->vibebp_settings_generate_form('general',$settings,'firebase');
			break;
			case 'editor':
				$settings = [
					array(
						'label'=>__('Vibe Editor','vibebp' ),
						'type'=> 'heading',
						'name'=>'label'
					),
					array(
						'label' => __('Editor Interface','vibebp'),
						'name' => 'editor_interface',
						'type' => 'select',
						'options'=>array(
							'full'=>__('Full Editor [ All Shortcodes, Media Library, Math, Columns','vibebp'),
							'advanced'=>__('Advanced Editor [ All Shortcodes, Media Library, Columns','vibebp'),
							'basic'=>__('Basic Editor [ Media library, No Shortcodes,No Columns ]','vibebp'),
						),
						'desc' => __('What kind of editor is suitable for your site','vibebp').'<a href="" style="text-decoration:none;"><span class="dashicons dashicons-editor-help"></span></a>',
						'default'=>''
					),
					array(
						'label' => __('MicroLearning elements','vibebp'),
						'name' => 'microlearning_elements',
						'type' => 'checkbox',
						'desc' => __('Add microlearning elements & simple games. ','vibebp').'<a href="" style="text-decoration:none;"><span class="dashicons dashicons-editor-help"></span></a>',
						'default'=>''
					),
					array(
						'label' => __('Content privacy','vibebp'),
						'name' => 'instructor_privacy',
						'type' => 'checkbox',
						'desc' => __('Users will not be able to view each other content like media etc. ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __("Mask user's name on all videos!","vibebp"),
						'name' => 'mask_videos',
						'type' => 'checkbox',
						'desc' => __('User name as mask on all videos to stop piracy in form of video recording. ','vibebp'),
						'default'=>''
					)
				];
				$this->vibebp_settings_generate_form('general',$settings,'editor');
			break;
			case 'misc':
				$settings = [
					array(
						'label' => __('Google Maps API Key','vibebp'),
						'name' => 'google_maps_api_key',
						'type' => 'text',
						'desc' => __('Get your maps api key ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Giphy api key','vibebp').' '.'<a href="https://developers.giphy.com/dashboard/?create=true">'.__('Get one','vibebp').'</a>',
						'name' => 'giphy_api_key',
						'type' => 'text',
						'desc' => __('Needed to show giphy usage in activity and wall','vibebp'),
						'default'=>'',
					),
					array(
						'label' => __('Enable Reactions','vibebp'),
						'name' => 'enable_reactions',
						'type' => 'checkbox',
						'desc' => __('Enable Facebook like reactions on activities','vibebp'),
						'default'=>'',
					)
				];
				$this->vibebp_settings_generate_form('general',$settings,'misc');
			break;
			default:


			
			$settings = apply_filters('vibebp_general_settings',array(
					array(
						'label'=>__('Basic Settings','vibebp' ),
						'type'=> 'heading',
					),
					array(
						'label' => __('Client id','vibebp'),
						'name' => 'client_id',
						'type' => 'text',
						'desc' => __('Client id for all api hits ','vibebp'),
						'default'=>wp_generate_password(16,false),
					),
					array(
						'label' => __('Global Login','vibebp'),
						'name' => 'global_login',
						'type' => 'checkbox',
						'desc' => __('Are you adding login in Menu/Header or on specific page. Global login scripts loaded on entire site.','vibebp'),
						'default'=>'on'
					),
					array(
						'label' => __('Synchronise WP with VibeBP Login','vibebp'),
						'name' => 'sync_login',
						'type' => 'checkbox',
						'desc' => __('When user logs in WordPress he is also logged in VibeBP','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Synchronise VibeBP with WP Login','vibebp'),
						'name' => 'sync_wp_login',
						'type' => 'checkbox',
						'desc' => __('When user logs in VibeBP log the user into WordPress [third party plugins]. Does NOT work with CACHE FIRST Service Workers.','vibebp'),
						'default'=>''
					),

					array(
						'label' => __('IP Vaidate Loggedin Tokens','vibebp'),
						'name' => 'ip_validate_token',
						'type' => 'checkbox',
						'desc' => __('Validate tokens via IP.','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('VibeBP Logout Redirect [default HomePage]','vibebp'),
						'name' => 'logout_redirect',
						'type' => 'select',
						'options'=>$this->pages,
						'desc' => __('Default logout set at home page. Recommended if WP login sync is enabled.','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Token Duration','vibebp'),
						'name' => 'token_duration',
						'type' => 'select',
						'options'=>array(
							604800=>_x('1 Week','setting','vibebp'),
							300=>_x('5 Minutes','setting','vibebp'),
							1800=>_x('30 Minutes','setting','vibebp'),
							3600=>_x('1 Hour','setting','vibebp'),
							21600=>_x('6 Hours','setting','vibebp'),
							43200=>_x('12 Hours','setting','vibebp'),
							86400=>_x('24 Hours','setting','vibebp'),
							''=>_x('Never expires','setting','vibebp'),
						),
						'desc' => __(' User remains logged in without the need for re-login.','vibebp'),
						'default'=>''
					),

					array(
						'label' => __('Who can upload','vibebp'),
						'name' => 'upload_capability',
						'type' => 'select',
						'options'=>array(
							''=>_x('Select one','setting','vibebp'),
							'manage_options'=>_x('Admins','setting','vibebp'),
							'edit_posts'=>_x('Instructors','setting','vibebp'),
							'read'=>_x('Students','setting','vibebp'),
							
							
						),
						'desc' => __('Who can upload files from front end? ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('BuddyPress on Single Page','vibebp'),
						'name' => 'bp_single_page',
						'type' => 'select',
						'options'=>$this->pages,
						'desc' => __('Run entire BuddyPress on a single page. All features, lower your website load. BP Init hook is removed from all pages.','vibebp').'<a href="https://docs.wplms.io" target="_blank">'.__('See Tutorial','vibebp').'</a>',
						'default'=>''
					),
					
					array(
						'label' => __('Setup Service Workers','vibebp'),
						'name' => 'service_workers',
						'type' => 'checkbox',
						'desc' => __('Setup service workers for offline loading, pre-loading and push notifications. ','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Enable wallet','vibebp'),
						'name' => 'enable_wallet',
						'type' => 'checkbox',
						'desc' => __('Enable wallet feature','vibebp'),
						'default'=>'',
					),/*
					array(
						'label' => __('IP location client id from ipstack.com','vibebp').' '.'<a href="https://ipstack.com/product">'.__('Get one','vibebp').'</a>',
						'name' => 'ipstack_access_id',
						'type' => 'text',
						'desc' => __('Needed to show location indication from User Ip','vibebp'),
						'default'=>'',
					),*/
					
				));
	
				if(function_exists('wc')){
					$settings[]=array(
						'label' => __('Continue cart page','vibebp'),
						'name' => 'continue_shopping',
						'type' => 'select',
						'options'=>$this->pages,
						'desc' => __('If using Vibe Cart for WooCommerce, connect continue cart page.','vibebp'),
						'default'=>''
					);
				}
			$this->vibebp_settings_generate_form('general',$settings);
			break;
		}
	}	

	function get_layouts(){
		return apply_filters('vibebp_layouts',
			array(
				'members' => array(
						'index' => array(
							'label'=>_x('Members Directory', '', 'vibebp'),
							'value'=>'members_index',
						)
				),
				'activity' => array(
						'index' => array(
							'label'=>_x('Activity Directory', '', 'vibebp'),
							'value'=>'activity_index',
						)
				),
				'xprofile' => array(
						'public' => array(
							'label'=>_x('Public Profile', '', 'vibebp'),
							'value'=>'public_profile',
						),
						'private' => array(
							'label'=>_x('Private Profile', '', 'vibebp'),
							'value'=>'private_profile',
						)
				),
				'groups' => array(
						'index' => array(
							'label'=>_x('Groups Directory', '', 'vibebp'),
							'value'=>'groups_index',
						)
				),
			)
		);
	}

	/*
		Layout Connections
	*/
	function get_layout_options($parent,$key){
		
		$args = array(
		  'numberposts' => 999,
		  'post_type'   => 'layouts'
		);
		$options_html = '';
		$layouts = get_posts( $args );
		$option = get_option('vibebp_layout_connections');
		
		if ( !empty($layouts )) {
			foreach ($layouts  as $key => $l) {
				$selected = '';
		    	if(!empty($option) && !empty($option[$parent]) && !empty($option[$parent][$key])){
		    		$selected = 'selected="selected"';
		    	}
		    	
		        $options_html .= '<option value="'.$l->ID.'" '.$selected.'>' . $l->post_title . '</option>';
			}
	    	
		}
		wp_reset_postdata();
		return $options_html;
	}

	function vibebp_layouts(){
		$layouts = $this->get_layouts();

		foreach ($layouts as $key => $layout) {
			echo '<h3>'.ucfirst($key).'</h3>';
			echo '<ul>';

			foreach ($layout as $k => $l) {
				echo '<li><label>'.$l['label'].'</label>
				<select  name="'.$key.'['.$k.']'.'['.$l['value'].']'.'">
				'.$this->get_layout_options($key,$k).'
				</select>
				</li>';
			}
			echo '</ul>';
		}
	}


	function vibebp_settings_generate_form($tab,$settings,$sub='general'){

		if(empty($settings))
			return; 

		
		echo '<form method="post">';
		wp_nonce_field('vibebp_settings','_wpnonce');
		echo '<table class="form-table">
				<tbody>';

		$vibebp_settings=get_option(VIBE_BP_SETTINGS);

		//make the changes here.

		$types = bp_get_member_types(array(),'objects');
		$mtypes = [];
		if(!empty($types)){
			$mtypes['enable_user_member_types_select'] = _x('Enable user to select','','vibebp');
			foreach($types as $type => $labels){
				$mtypes[$type]=$labels->labels['name'];
			}
		}
		foreach($settings as $setting ){
			echo '<tr valign="top" '.(empty($setting['class'])?'':'class="'.$setting['class'].'"').'>';

			$value = '';
			if(!empty($setting['name']) && !empty($vibebp_settings[$tab][$setting['name']])){
				$value = $vibebp_settings[$tab][$setting['name']];
			}
			
			if(!empty($sub)){

				if(!empty($vibebp_settings[$tab][$sub])){
					
					if(isset($setting['name']) && isset($vibebp_settings[$tab][$sub][$setting['name']])){
						$value = $vibebp_settings[$tab][$sub][$setting['name']];
						if(!empty($setting['value'])){
							$setting['touchpoint_action'] = $setting['value'];
						}
							
						$setting['value']=$value;
					}
				}
				
				echo '<input type="hidden" name="sub_tab" value='.esc_attr($sub).'>';
			}

			switch($setting['type']){
				case 'heading':
					echo '<th scope="row" class="titledesc" colspan="2"><h3>'.$setting['label'].'</h3></th>';
				break;
				case 'rest_api_call':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp">
							<a id="'.$setting['name'].'" class="button ajax_button">'.$setting['button_label'].'</a>';
					?>
					<script>
						jQuery(document).ready(function($){
							$('#<?php echo $setting['name']; ?>').on('click',function(e){
								e.preventDefault();
								let text =  $('#<?php echo $setting['name']; ?>').text();
								$('#<?php echo $setting['name']; ?>').text('...');
								$.ajax({
							        url: '<?php echo $setting['url'];?>'
							    }).then(function(data) {
							    	let html ='';
							    	$('#<?php echo $setting['name']; ?>').text(text);
							    	if(data.body.hasOwnProperty('message')){
							    		html = '<div class="notice notice-error"><p>'+data.body.message+'</p></div>';
							    	}else{

								    	data = data.body;
								    	html = '<div class="grid_table">\
								    		<div class="table_head"><div class="td">Memory</div><div class="td">Time Seconds</div><div class="td">Number of Queries</div></div>\
								    		<div class="table_head"><div class="td">'+data.current.memory+'</div><div class="td">'+data.current.time+'</div><div class="td">'+data.current.queries+'</div></div>\
								    		</div>';
							    		if(data.past.hasOwnProperty('memory')){
							    			html += '<strong>Previous Result</strong><div class="grid_table">\
								    		<div class="table_head"><div class="td">Memory</div><div class="td">Time Seconds</div><div class="td">Number of Queries</div></div>\
								    		<div class="table_head"><div class="td">'+data.past.memory+'</div><div class="td">'+data.past.time+'</div><div class="td">'+data.past.queries+'</div></div>\
								    		</div>';
							    		}
							    	}
						    		$('.api_button_result').html(html);
							    });
						    });
						});
					</script>
					<?php
					echo '<span class="api_button_result">'.$setting['desc'].'</span></td><style>.grid_table { display: flex; margin: 1rem 0; border: 1px solid #aaa; flex-direction: column; align-items: flex-start; justify-content: flex-start; max-width: 420px; border-left: none; border-bottom: none; } .table_head { display: flex; width: 100%; } .table_head > * {flex: 1;text-align: center;padding: 5px;border-bottom: 1px solid #aaa;border-left: 1px solid #aaa;}span.success_on { width: 16px; height: 16px; display: block; background: #5fee52; border-radius: 50%; box-shadow: 0 2px 10px #5fd655; }span.disabled_off { width: 16px; height: 16px; display: block; background: #aaa; border-radius: 50%; }.titledesc h3{display:flex;align-items:center;gap:1.5rem;}</style>';	
				break;
				case 'ajax_button': 
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp">
							<a id="'.$setting['name'].'" class="button ajax_button">'.$setting['button_label'].'</a>';
							$params = [
								'action'=>'ajax_button_call',
								'security'=>wp_create_nonce('security'),
								'id'=>$setting['name']
							];

							if(!empty($setting['params'])){
								$params = array_merge($params,$setting['params']);
							}

					?>

					<script>
						jQuery(document).ready(function($){
							$('#<?php echo $setting['name']; ?>').on('click',function(e){
								e.preventDefault();
								let $this = $(this);
								var currtd = $(this).closest('td');
								let text = $this.text();
								$this.text('...');

								$.ajax({
				                    type: "POST",
				                    url: ajaxurl,
				                    data: <?php echo json_encode($params); ?>,
				                    cache: false,
				                    success: function (html) {
				                    	$this.text(text);
				                    	$('#<?php echo $setting['name']; ?>').parent().find('.ajax_button_result').html(html);
				                    }
				                });
			                })
		                });
					</script>
					<?php

					echo '<span class="ajax_button_result">'.$setting['desc'].'</span></td>';
				break;
				case 'link':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><a href="'.$setting['value'].'" class="button">'.$setting['button_label'].'</a>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'select':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><select name="'.$setting['name'].'">';
					foreach($setting['options'] as $key=>$option){
						echo '<option value="'.$key.'" '.(isset($value)?selected($key,$value):'').'>'.$option.'</option>';
					}
					echo '</select>';
					echo (!empty($setting['desc'])?'<span>'.$setting['desc'].'</span>':'').'</td>';
				break;
				case 'multiselect':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					
					echo '<td class="forminp"><select name="'.$setting['name'].'[]" multiple>';

					foreach($setting['options'] as $key => $option){
						$selected = '';
						if(!empty($value)){
							if(empty($option['value']) && in_array($key,$value) ){
								$selected = 'selected="selected"';
							}

							if(!empty($option['value']) && in_array($option['value'],$value) ){
								$selected = 'selected="selected"';
							}
						}
						echo '<option value="'.(empty($option['value'])?$key:$option['value']).'" '.$selected.'>'.(empty($option['value'])?$option:$option['label']).'</option>';
					}
					echo '</select>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'member_type':
					
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><select name="'.$setting['name'].'"><option>'.__('Select member type','vibebp').'</option>';
					foreach($mtypes as $key=>$option){
						echo '<option value="'.$key.'" '.(isset($value)?selected($key,$value):'').'>'.$option.'</option>';
					}
					echo '</select>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'registration_forms':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><select name="'.$setting['name'].'"><option>'.__('Select registration form','vibebp').'</option>';

					$forms = get_option('vibebp_registration_forms');
					if(!empty($forms)){
						foreach($forms as $key=>$option){
						echo '<option value="'.$key.'" '.(isset($value)?selected($key,$value):'').'>'.$key.'</option>';
						}
					}
					echo '</select>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'checkbox':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';


					echo '<td class="forminp"><input type="checkbox" name="'.$setting['name'].'" '.(!empty($value)?'CHECKED':'').' />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'number':
					
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><input type="number" name="'.$setting['name'].'" value="'.(isset($value)?$value:(isset($setting['default'])?$setting['default']:'')).'" />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'cptselect':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp">';
					echo '<select name="'.$setting['name'].'"><option value="">'.__('Select','vibebp').' '.$setting['cpt'].'</option>';
					global $wpdb;
					$cpts = '';
					if($setting['cpt']){
						$cpts = $wpdb->get_results("
							SELECT ID,post_title 
							FROM {$wpdb->posts} 
							WHERE post_type = '".$setting['cpt']."' 
							AND post_status='publish' 
							ORDER BY post_title DESC LIMIT 0,999");	
					}
					if(is_array($cpts)){
						foreach($cpts as $cpt){
							echo '<option value="'.$cpt->ID.'" '.((isset($value) && $value == $cpt->ID)?'selected="selected"':'').'>'.$cpt->post_title.'</option>';
						}
					}
					echo '</select>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'title':
					echo '<th scope="row" class="titledesc"><h3>'.$setting['label'].'</h3></th>';
					echo '<td class="forminp"><hr /></td>';
				break;
				case 'taxonomy':
					if(empty($this->taxonomy[$setting['taxonomy']])){
						$this->taxonomy[$setting['taxonomy']]=get_terms( array(
						    'taxonomy' => $setting['taxonomy'],
						    'hide_empty' => false,
						) );
					}
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>'.$tab.' = '.$setting['name'].' -> ';
					

					echo '<td class="forminp forminp-color"><select name="'.$setting['name'].'" >';
					if(!empty($this->taxonomy[$setting['taxonomy']])){
						foreach($this->taxonomy[$setting['taxonomy']] as $term){
							echo '<option value="'.$term->slug.'" '.(($value == $term->slug)?'selected':'').'>'.$term->name.'</option>';
						}
					}
					echo '</select><span>'.$setting['desc'].'</span></td>';
				break;
				case 'color':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp forminp-color"><input type="text" name="'.$setting['name'].'" class="colorpicker" value="'.(isset($value)?$value:'').'" />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'upload':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					$url =0;

					if(!empty($value)){
						$url = wp_get_attachment_image_src($value,'full');
					}
					
					echo '<td class="forminp forminp-upload">'.($url?'<img src="'.$url[0].'" class="upload_image_button" button_label="'.$setting['button_label'].'" input-name="'.$setting['name'].'" /><input type="hidden" name="'.$setting['name'].'" value='.$value.' /><span class="dashicons dashicons-no remove_uploaded"></span>':'').'<a class="button upload_image_button" input-name="'.$setting['name'].'" uploader-title="'.$setting['button_title'].'" style="'.($url?'display:none;':'').'">'.$setting['button_label'].'</a>';
					echo '<span>'.$setting['desc'].'</span></td>';					
				break;
				case 'hidden':
					echo '<td><input type="hidden" name="'.$setting['name'].'" value="1"/></td>';
				break;
				case 'bp_setup_nav':
					$nav = bp_get_nav_menu_items();
					update_option('bp_setup_nav',bp_get_nav_menu_items());
				break;
				case 'repeatable':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><a class="add_new_repeatable button-primary" data-name="'.$setting['name'].'[]" data-placeholder="'.$setting['placeholder'].'">'.__('Add New','vibebp').'</a><ul>';
					if(!empty($value)){
						foreach($value as $k=>$item){
							echo '<li><input type="text" name="'.$setting['name'].'[]" value="'.$item.'"/><span class="dashicons dashicons-no-alt remove_item"></span></li>';
						}
						
					}
					echo '</ul><span>'.$setting['desc'].'</span></td>';
					add_action('admin_footer',array($this,'repeatable_script'));
					
				break;
				case 'app_products': 
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><a class="app_products_repeatable button-primary" data-name="'.$setting['name'].'" data-placeholder="'.(!empty($setting['placeholder'])?$setting['placeholder']:'').'">'.__('Add New','vibebp').'</a><ul>';
					if(!empty($value)){
						foreach($value as $k=>$item){
							if(!empty($item['product'])){
								echo '<li><input type="text" name="'.$setting['name'].'[product][]" value="'.$item['product'].'"/><input type="number" name="'.$setting['name'].'[credits][]" value="'.$item['credits'].'"/><span class="dashicons dashicons-no-alt remove_item"></span></li>';
							}
						}
					}
					echo '</ul><span>'.$setting['desc'].'</span></td>';
					add_action('admin_footer',array($this,'repeatable_script'));
					
				break;
				case 'textarea':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><textarea name="'.$setting['name'].'">'.(isset($value)?stripslashes($value):(isset($setting['default'])?$setting['default']:'')).'</textarea>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'touchpoint': 
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><strong>'.__('USER','vibebp').'</strong></td>';
					echo '<td class="forminp">';
					echo __('Message','vibebp').'&nbsp; <select '.((function_exists('bp_is_active') && bp_is_active('messages'))?'':'disabled').' name="'.$setting['name'].'[student][message]">';
					echo '<option value="0" '.(isset($value['student']['message'])?selected(0,$value['student']['message']):'').'>'.__('No','vibebp').'</option>';
					echo '<option value="1" '.(isset($value['student']['message'])?selected(1,$value['student']['message']):'').'>'.__('Yes','vibebp').'</option>';
					echo '</select>';
					echo '&nbsp;&nbsp;'.__('Notification','vibebp').'&nbsp; <select '.((function_exists('bp_is_active') && bp_is_active('notifications'))?'':'disabled').' name="'.$setting['name'].'[student][notification]">';
					echo '<option value="0" '.(isset($value['student']['notification'])?selected(0,$value['student']['notification']):'').'>'.__('No','vibebp').'</option>';
					echo '<option value="1" '.(isset($value['student']['notification'])?selected(1,$value['student']['notification']):'').'>'.__('Yes','vibebp').'</option>';
					echo '</select>';
					echo '&nbsp;&nbsp;'.__('Email','vibebp').'&nbsp; <select name="'.$setting['name'].'[student][email]">';
					echo '<option value="0" '.(isset($value['student']['email'])?selected(0,$value['student']['email']):'').'>'.__('No','vibebp').'</option>';
					echo '<option value="1" '.(isset($value['student']['email'])?selected(1,$value['student']['email']):'').'>'.__('Yes','vibebp').'</option>';
					echo '</select>';

					/**/
					if(!empty($value)){
						do_action('wplms_student_touchpoint_setting_html',$value['student'],$value,$setting['name']);
	
					}

					echo '&nbsp;&nbsp;'.sprintf(__('%s Edit Email Template %s','vibebp'),'<a href="'.$setting['touchpoint_action']['student'].'" class="button">','</a>');
					echo '</td></tr>';
					if(!empty($setting['touchpoint_action']['instructor'])){
						echo '<tr valign="top"><th scope="row"></th>';
						echo '<td class="forminp"><strong>'.__('INSTRUCTOR','vibebp').'</strong></td>';
						echo '<td class="forminp">';
						echo __('Message','vibebp').'&nbsp; <select '.((function_exists('bp_is_active') && bp_is_active('messages'))?'':'disabled').' name="'.$setting['name'].'[instructor][message]">';
						echo '<option value="0" '.(isset($value['instructor']['message'])?selected(0,$value['instructor']['message']):'').'>'.__('No','vibebp').'</option>';
						echo '<option value="1" '.(isset($value['instructor']['message'])?selected(1,$value['instructor']['message']):'').'>'.__('Yes','vibebp').'</option>';
						echo '</select>';
						echo '&nbsp;&nbsp;'.__('Notification','vibebp').'&nbsp; <select '.((function_exists('bp_is_active') && bp_is_active('notifications'))?'':'disabled').' name="'.$setting['name'].'[instructor][notification]">';
						echo '<option value="0" '.(isset($value['instructor']['notification'])?selected(0,$value['instructor']['notification']):'').'>'.__('No','vibebp').'</option>';
						echo '<option value="1" '.(isset($value['instructor']['notification'])?selected(1,$value['instructor']['notification']):'').'>'.__('Yes','vibebp').'</option>';
						echo '</select>';
						echo '&nbsp;&nbsp;'.__('Email','vibebp').'&nbsp; <select name="'.$setting['name'].'[instructor][email]">';
						echo '<option value="0" '.(isset($value['instructor']['email'])?selected(0,$value['instructor']['email']):'').'>'.__('No','vibebp').'</option>';
						echo '<option value="1" '.(isset($value['instructor']['email'])?selected(1,$value['instructor']['email']):'').'>'.__('Yes','vibebp').'</option>';
						echo '</select>';

						if(!empty($value)){
							do_action('wplms_instructor_touchpoint_setting_html',$value['instructor'],$value,$setting['name']);
						}
						
						echo '&nbsp;&nbsp;'.sprintf(__('%s Edit Email Template %s','vibebp'),'<a href="'.$setting['touchpoint_action']['instructor'].'" class="button">','</a>');
						echo '</td>
							<tr><td colspan="3"><hr></td>';
					}
					
					
				break;
				case 'touchpoint_admin': 

					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><strong>'.__('INSTRUCTOR','vibebp').'</strong></td>';
					echo '<td class="forminp">';
					echo __('Message','vibebp').'&nbsp; <select '.((function_exists('bp_is_active') && bp_is_active('messages'))?'':'disabled').' name="'.$setting['name'].'[instructor][message]">';
					echo '<option value="0" '.(isset($value['instructor']['message'])?selected(0,$value['instructor']['message']):'').'>'.__('No','vibebp').'</option>';
					echo '<option value="1" '.(isset($value['instructor']['message'])?selected(1,$value['instructor']['message']):'').'>'.__('Yes','vibebp').'</option>';
					echo '</select>';
					echo '&nbsp;&nbsp;'.__('Notification','vibebp').'&nbsp; <select '.((function_exists('bp_is_active') && bp_is_active('notifications'))?'':'disabled').' name="'.$setting['name'].'[instructor][notification]">';
					echo '<option value="0" '.(isset($value['instructor']['notification'])?selected(0,$value['instructor']['notification']):'').'>'.__('No','vibebp').'</option>';
					echo '<option value="1" '.(isset($value['instructor']['notification'])?selected(1,$value['instructor']['notification']):'').'>'.__('Yes','vibebp').'</option>';
					echo '</select>';
					echo '&nbsp;&nbsp;'.__('Email','vibebp').'&nbsp; <select name="'.$setting['name'].'[instructor][email]">';
					echo '<option value="0" '.(isset($value['instructor']['email'])?selected(0,$value['instructor']['email']):'').'>'.__('No','vibebp').'</option>';
					echo '<option value="1" '.(isset($value['instructor']['email'])?selected(1,$value['instructor']['email']):'').'>'.__('Yes','vibebp').'</option>';
					echo '</select>';
					echo '&nbsp;&nbsp;'.sprintf(__('%s Edit Email Template %s','vibebp'),'<a href="'.$setting['touchpoint_action']['instructor'].'" class="button">','</a>');
					echo '</td></tr><tr valign="top">';
					echo '<th scope="row"></th>';
					echo '<td class="forminp"><strong>'.__('ADMINISTRATOR','vibebp').'</strong></td>';
					echo '<td class="forminp">';
					echo __('Message','vibebp').'&nbsp; <select '.((function_exists('bp_is_active') && bp_is_active('messages'))?'':'disabled').' name="'.$setting['name'].'[admin][message]">';
					echo '<option value="0" '.(isset($value['admin']['message'])?selected(0,$value['admin']['message']):'').'>'.__('No','vibebp').'</option>';
					echo '<option value="1" '.(isset($value['admin']['message'])?selected(1,$value['admin']['message']):'').'>'.__('Yes','vibebp').'</option>';
					echo '</select>';
					echo '&nbsp;&nbsp;'.__('Notification','vibebp').'&nbsp; <select '.((function_exists('bp_is_active') && bp_is_active('notifications'))?'':'disabled').' name="'.$setting['name'].'[admin][notification]">';
					echo '<option value="0" '.(isset($value['admin']['notification'])?selected(0,$value['admin']['notification']):'').'>'.__('No','vibebp').'</option>';
					echo '<option value="1" '.(isset($value['admin']['notification'])?selected(1,$value['admin']['notification']):'').'>'.__('Yes','vibebp').'</option>';
					echo '</select>';
					echo '&nbsp;&nbsp;'.__('Email','vibebp').'&nbsp; <select name="'.$setting['name'].'[admin][email]">';
					echo '<option value="0" '.(isset($value['admin']['email'])?selected(0,$value['admin']['email']):'').'>'.__('No','vibebp').'</option>';
					echo '<option value="1" '.(isset($value['admin']['email'])?selected(1,$value['admin']['email']):'').'>'.__('Yes','vibebp').'</option>';
					echo '</select>';
					echo '&nbsp;&nbsp;'.sprintf(__('%s Edit Email Template %s','vibebp'),'<a href="'.$setting['touchpoint_action']['admin'].'" class="button">','</a>');
					echo '</td>
						<tr><td colspan="3"><hr></td>';
				break;
				case 'schema_repeatable':
				echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					$saved_fields = $setting['value'];
					
					?>
					<td class="forminp">
					<div class="schema_field_repeatable_wrapper">
						<?php
						

						echo  '<ul class="schema_field_repeatable_list_'.$setting['name'].'">';

						echo '<li class="schema_field_repeatable_list_head">';
						foreach($setting['fields'] as $field){
							echo '<span '.(empty($field['class'])?'':'class="'.$field['class'].'"').'>'.(empty($field['label'])?'':$field['label']).'</span>';
						}
						echo '<span class="min"></span>';
						echo '</li>';

						if(!empty($saved_fields) && $setting['fields']){


							if(!empty($saved_fields['key'])){


								foreach($saved_fields['key'] as $i=>$saved_field){


									echo '<li '.(empty($field['class'])?'':$field['class']).'>';
										
										foreach($setting['fields'] as $field){
											

											switch($field['type']){
												case 'text':
													echo '<input type="text" name="'.$setting['name'].'['.$field['key'].'][]" value="'.(isset($saved_fields[$field['key']][$i])?$saved_fields[$field['key']][$i]:'').'" />';
												break;
												case 'color':
													echo '<input type="color" name="'.$setting['name'].'['.$field['key'].'][]" value="'.(isset($saved_fields[$field['key']][$i])?$saved_fields[$field['key']][$i]:'').'" />';
												break;
												case 'checkbox':
													echo '<input type="checkbox" name="'.$setting['name'].'['.$field['key'].'][]" value="1" '.(isset($saved_fields[$field['key']][$i])?'checked="checked"':'').' />';
												break;
												case 'select':
													echo '<select name="'.$setting['name'].'['.$field['key'].'][]">';
													
													
													foreach($field['options'] as $k=>$v){
														echo '<option value="'.$k.'" '.(($k==$saved_fields[$field['key']][$i])?'selected':'').'>'.$v.'</option>';
													}
													echo '</select>';
												break;
												case 'multiselect':
													echo '<select name="'.$setting['name'].'['.$field['key'].']['.$i.'][]" multiple>';
													foreach($field['options'] as $k=>$v){
														echo '<option value="'.$k.'" '.(is_array($saved_fields[$field['key']][$i]) && in_array($k,$saved_fields[$field['key']][$i])?'selected':'').'>'.$v.'</option>';
													}
													echo '</select>';
													
												break;
											}
										}
										echo '<span class="min dashicons dashicons-no-alt"></span></li>';
								}
							}
						}
						echo '</ul>';
						?>
						
					</div>
					<a id="add_new_schema_field_button_<?php echo $setting['name']; ?>" class="button-primary"><?php _ex('Add New','button create field','vibebp'); ?></a>
					<script>

						document.querySelector('#add_new_schema_field_button_<?php echo $setting['name']; ?>').addEventListener('click',function(e){
							e.preventDefault();
							let fields = <?php echo json_encode($setting['fields']); ?>;
							var div = document.createElement('div');
							fields.map((field)=>{

								let input='';
								switch(field.type){
									case 'text':
										
										input = document.createElement('input');
										input.type= 'text';
										input.placeholder = field.label;
										input.name= '<?php echo $setting['name']; ?>['+field.key+'][]';
										input.setAttribute('class','input-field');
										div.appendChild(input);
									break;
									case 'color':
										
										input = document.createElement('input');
										input.type= 'color';
										input.placeholder = field.label;
										input.name= '<?php echo $setting['name']; ?>['+field.key+'][]';
										input.setAttribute('class','input-field');
										div.appendChild(input);
									break;
								case 'checkbox':
										
										let checkboxdiv = document.createElement('div');
										let label = document.createElement('label');
										let attr = field.key+'_'+Math.floor(Math.random()*100);
										label.setAttribute('for',attr);
										label.innerHTML=field.label;
										checkboxdiv.appendChild(label);
										let cinput = document.createElement('input');
										cinput.type= 'checkbox';	
										cinput.setAttribute('id',attr);
										cinput.name= '<?php echo $setting['name']; ?>['+field.key+'][]';
										cinput.setAttribute('class','input-field');
										div.appendChild(cinput);
									break;
									case 'multiselect':

										let mselectdiv = document.createElement('div');

										let mlabel = document.createElement('label');
										let mattr = field.key+'_'+Math.floor(Math.random()*100);
										mlabel.setAttribute('for',mattr);
										mlabel.innerHTML=field.label;

										mselectdiv.appendChild(mlabel);
										
										let mselect = document.createElement('select');
										mselect.setAttribute('id',mattr);

										let index= 0;
										index= document.querySelector('<?php echo '.schema_field_repeatable_list_'.$setting['name']; ?>').childNodes.length;
										mselect.setAttribute('multiple',true);
										mselect.name = '<?php echo $setting['name']; ?>['+field.key+']['+index+'][]';
										Object.keys(field.options).map(function(k){
											let option = document.createElement('option');
											option.value=k;
											option.innerHTML = field.options[k];
											mselect.appendChild(option);	
										});
										mselectdiv.appendChild(mselect);
										div.appendChild(mselectdiv);
									break;
									case 'select':

										let select = document.createElement('select');
										
										select.name = '<?php echo $setting['name']; ?>['+field.key+'][]';
										let option = document.createElement('option');
											option.value='';
											option.innerHTML = field.label;
											select.appendChild(option);
										Object.keys(field.options).map(function(k){
											let option = document.createElement('option');
											option.value=k;
											option.innerHTML = field.options[k];
											select.appendChild(option);	
										});

										div.appendChild(select);
									break;
								}


							});

							let span = document.createElement('span');
							span.setAttribute('class','dashicons dashicons-no-alt');
							div.appendChild(span);

							let list = document.querySelector('.schema_field_repeatable_list_<?php echo $setting['name'];?>');
							if(list){
								list.appendChild(div);
								list.dispatchEvent(new Event('schema_field_repeatable_loaded'));	
							}
							document.querySelectorAll('select[multiple]').forEach(function(el){
								if(!el.getAttribute('data-id')){
									new SlimSelect({
									  select: el
									});			
								}
							});
							
							
						});

							function remove_projects_fields(){
								let close = document.querySelectorAll('.dashicons-no-alt');

								if(close.length){
									close.forEach(function(el){
										el.addEventListener('click',function(e){
											e.preventDefault();
											el.parentNode.remove();	
										});
									});
								}
								
						}
						
						document.querySelector('.schema_field_repeatable_list_<?php echo $setting['name']; ?>').addEventListener('schema_field_repeatable_loaded',function(e){
							remove_projects_fields();
						});
						
						remove_projects_fields();	
						document.addEventListener('DOMContentLoaded',function(){
							document.querySelectorAll('select[multiple]').forEach(function(el){
								if(!el.getAttribute('data-id')){
									new SlimSelect({
									  select: el
									});			
								}
							});	
						});
						
					</script>

					<?php

					echo '<span>'.$setting['desc'].'</span>';
					?>
					</td>
					<?php
				break;
				case 'userselect':

					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><select multiple id="'.$setting['name'].'" class="userselect" name="'.$setting['name'].'[]">';
					if(!empty($setting['value'])){
						foreach($setting['value'] as $key=>$option){
							$user = get_userdata($option);
							echo '<option value="'.$option.'" selected>'.$user->display_name.' - '.$user->user_email.'</option>';
						}
					}
					
					echo '</select>';
					echo '<span>'.$setting['desc'].'</span></td>';
					?>
					<script>
						document.addEventListener('DOMContentLoaded',function(){
							document.querySelectorAll('.userselect').forEach(function(el){
								
								let uss = new SlimSelect({
								  select: '#'+el.getAttribute('id'),

								  events: {
								    search: (search, currentData) => {
								      return new Promise((resolve, reject) => {
								        if (search.length < 2) {
								          return reject('<?php echo _x('Search must be at least 2 characters','','vibebp');?>')
								        }
								        var formData = new FormData();
								        formData.append("search",search);
									    formData.append("security",'<?php echo wp_create_nonce('userselect_search'); ?>');
									    formData.append("action",'userselect_search');
								        // Fetch random first and last name data
								        fetch(ajaxurl, {
							                method:'post',
							                body: formData,
							            })
								          .then((response) => response.json())
								          .then((data) => {
								          	console.log(data);
								            resolve(data)
								          })
								      })
								    }
								  }
								});

							});	
						});
					</script>
					<?php
				break;
				default:
					$setting['value']=$value;
					$html = apply_filters('vibebp_settings_type',0,$setting);
					if(empty($html)){
						echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
						echo '<td class="forminp"><input type="text" name="'.$setting['name'].'" value="'.(isset($value) && !is_array($value)?$value:(isset($setting['default'])?$setting['default']:'')).'" />';
						echo '<span>'.$setting['desc'].'</span></td>';	
					}
					
				break;
			}
			
			echo '</tr>';
		}
		echo '</tbody>
		</table>';
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_media();
		?>
		<script>
	        jQuery(document).ready(function($){
	            $('#assign_default_member_type').on('click',function(){
	                var $this = $(this);
	                $this.after('<span class="member_type_status">Starting ...</span><div class="progress_wrap" style="margin: 30px 0;width: 300px;"><div class="progress" style="height: 10px;border-radius: 5px;"><div class="bar" style="width: 5%;"></div></div></div>');
	                //Show progress bar
	                $.ajax({
	                    type: "POST",
	                    url: ajaxurl,
	                    dataType: "json",
	                    data: { 
	                            action: 'get_unassigned_members', 
	                            security: '<?php echo wp_create_nonce('assign_default_member_type'); ?>',
	                            batch:10
	                        },
	                    cache: false,
	                    success: function (json) {
	                    	if(json.length){
	                    		$this.parent().find('.progress_wrap .bar').css('width','10%');
		                        $this.parent().find('span.member_type_status').text('fetched '+Object.keys(json).length*10+' unassigned members, sync in progress...');
		                            var defferred = [];
		                            var current = 0;
		                            $.each(json,function(i,item){
		                                defferred.push(item);
		                            });
		                            recursive_step(current,defferred,$this);
		                            //$.each() RUN loop on json and increment progress bar
		                            $('body').on('end_recursive_member_type_sync',function(){
		                                $this.parent().find('span.member_type_status').text(text);
		                                $this.parent().find('.progress_wrap .bar').css('width','100%');
		                                setTimeout(function(){$this.parent().find('.progress_wrap,.member_type_status').hide(200);},3000);
		                            });
	                        }else{
	                        	 $('body').trigger('end_recursive_member_type_sync');
	                        	 $this.parent().find('.bar').css('width','100%');
	                        }
	                             
	                    }
	                });
	            });

	            function recursive_step(current,defferred,$this){
	                if(current < defferred.length){
	                    $.ajax({
	                        type: "POST",
	                        url: ajaxurl,
	                        data: defferred[current],
	                        cache: false,
	                        success: function(){ 
	                            current++;
	                            $this.find('span.member_type_status').text(current+'/'+defferred.length+' complete, sync in progress...');
	                            var width = 10 + 90*current/defferred.length;
	                            $this.parent().find('.bar').css('width',width+'%');
	                            if(defferred.length == current){
	                                $('body').trigger('end_recursive_member_type_sync');
	                            }else{
	                                recursive_step(current,defferred,$this);
	                            }
	                        }
	                    });
	                }else{
	                    $('body').trigger('end_recursive_member_type_sync');
	                }
	            }//End of function

	        });

	        document.querySelectorAll('select[multiple]').forEach(function(el){
				if(!el.getAttribute('data-id')){
					new SlimSelect({
					  select: el
					});			
				}
			});
	    </script>
	    <style>.schema_field_repeatable_wrapper >ul > * {display: flex;flex-wrap:wrap;align-items: center;gap: 5px;}.schema_field_repeatable_wrapper >ul > * >div{max-width:20rem;}.schema_field_repeatable_wrapper >ul > * > * {flex: 1;}.schema_field_repeatable_wrapper >ul > * > *.min , .schema_field_repeatable_wrapper input[type="checkbox"]{flex: 0 0 60px;}li.schema_field_repeatable_list_head {border-bottom: 1px solid #ddd;padding: 0 0 10px;}</style>
		<script>
			jQuery(document).ready(function($){

				$( 'input[type="checkbox"]' ).change(function(){
					var $this = jQuery(this);
					if($this .prop('checked')){
				        $this.attr('checked','checked');
				        
				        
				    }else{
				        $this.attr('checked','');
				    }
				});

				$( '.colorpicker' ).wpColorPicker();
				
				$('.remove_uploaded').on('click',function(){
					$(this).parent().find('img').remove();
					$(this).parent().find('input').remove();
					$(this).parent().find('.upload_image_button').show();
					$(this).remove();
				});
				
				var media_uploader=[];
				jQuery('.upload_image_button').on('click', function( event ){
				  
				    var button = jQuery( this );
				    var input_name = button.attr( 'input-name' );

				    if ( media_uploader[input_name]) {
				      media_uploader[input_name].open();
				      return;
				    }
				    // Create the media uploader.
				    media_uploader[input_name] = wp.media.frames.media_uploader = wp.media({
				        title: button.attr( 'uploader-title' ),
				        // Tell the modal to show only images.
				        library: {
				            type: 'image',
				            query: false
				        },
				        button: {
				            text: button.attr( 'button_label' ),
				        },
				        multiple: false
				    });

				    // Create a callback when the uploader is called
				    media_uploader[input_name].on( 'select', function() {
			        	var selection = media_uploader[input_name].state().get('selection');
			            
			            selection.map( function( attachment ) {
				            attachment = attachment.toJSON();

				            var url_image='';
				            if( attachment.sizes){
				                if(   attachment.sizes.thumbnail !== undefined  ) url_image=attachment.sizes.thumbnail.url; 
				                else if( attachment.sizes.medium !== undefined ) url_image=attachment.sizes.medium.url;
				                else url_image=attachment.sizes.full.url;
				            }
				            
					        if(button.prop('tagName') == 'IMG'){
					        	button.attr('src',url_image);
					        	button.parent().find('input[name="'+input_name+'"]').val(attachment.id);
					        }else{
					        	button.html('<img src="'+url_image+'" class="submission_thumb thumbnail" /><input id="'+input_name+'" class="post_field" data-type="featured_image" data-id="'+input_name+'" name="'+input_name+'" type="hidden" value="'+attachment.id+'" />');	
					        }
				            
			         	});

				    });
				    // Open the uploader
				    media_uploader[input_name].open();
				  });
			});
			</script>
		<?php
		if(!empty($settings))
			echo '<input type="hidden" name="tab" value="'.$tab.'" /><input type="submit" name="save" value="'.__('Save Settings','vibebp').'" class="button button-primary" /></form>';
	}

	function vibebp_save_settings($tab){

		if ( !empty($_POST) && check_admin_referer('vibebp_settings','_wpnonce') ){
			$vibebp_settings=array();

			$vibebp_settings = get_option(VIBE_BP_SETTINGS);	
		
			unset($_POST['_wpnonce']);
			unset($_POST['_wp_http_referer']);
			unset($_POST['save']);
			if(empty($tab)){
				$tab = apply_filters('vibebp_save_tab','general',$_POST);
			}

			$sub = '';
			if(!empty($_POST['sub_tab'])){
				$sub = esc_attr($_POST['sub_tab']);
			}
			
		
			switch($tab){
				case 'bp':
	
					if(!empty($sub)){
						if($sub=='general'){
							unset($vibebp_settings['bp']);
						}
						$vibebp_settings['bp'][$sub] = $_POST;

					}else{
						foreach((array)$_POST as $k=>$v){
							$vibebp_settings['bp'][$k]=$v;
						}
				

						//Delete
						foreach($vibebp_settings['bp'] as $k => $v){
							if(is_array($v) && !empty($v['sub_tab'])){
								//sub tab
								//$vibebp_settings['bp'][$sub] = apply_filters('vibebp_save_settings',$_POST,$tab);
							}else{
								if(!in_Array($k,array_keys($_POST)) ){
									unset($vibebp_settings['bp'][$k]);
								}
							}

						}
					}
				break;
				default:


				if(!empty($_POST['firebase_config'])){
					$firebase_config = $_POST['firebase_config'];
					if(!is_serialized($firebase_config)){
						$firebase_config = str_replace('{','{"',str_replace(',',',"',str_replace(': ','":',$firebase_config)));
						$firebase_config = stripslashes(preg_replace('/\s\s+/', '', str_replace(' ','',$firebase_config)));
						$_POST['firebase_config']=sanitize_textarea_field(serialize(json_decode($firebase_config,true)));
					}else{
						$_POST['firebase_config']=sanitize_textarea_field(stripslashes($firebase_config));
					}
				}
				if(!empty($_POST['firebase_private_key'])){
					$firebase_private_key = $_POST['firebase_private_key'];
					$check = json_decode(stripslashes($firebase_private_key),true);
					if(!empty($check)){
						$_POST['firebase_private_key']=esc_attr(urlencode(stripslashes($firebase_private_key)));
					}
				}
				if(!empty($_POST['google_play_service_account_json'])){
					$firebase_private_key = $_POST['google_play_service_account_json'];
					$check = json_decode(stripslashes($firebase_private_key),true);
					if(!empty($check)){
						$_POST['google_play_service_account_json']=esc_attr(urlencode(stripslashes($firebase_private_key)));
					}
				}
				if(is_array($_POST)){

					foreach($_POST as $k=>$v){
						if(is_array($v)){
							$_POST[$k]=vibebp_recursive_sanitize_text_field($v);
						}else{
							if(vibe_isJson(stripslashes($v))){
								$_POST[$k]=sanitize_textarea_field(json_decode(stripslashes($v)));
							}else{
								$_POST[$k]=wp_kses_data($v);
							}
						}
					}
				}
				if(!empty($_POST['google_play_app_products']) && !empty($_POST['google_play_app_products']['product'])){
					$val = [];
					foreach ($_POST['google_play_app_products']['product'] as $key => $product_id) {
						if(!empty($product_id) && !empty($_POST['google_play_app_products']['credits']) && !empty($_POST['google_play_app_products']['credits'][$key])){
							$val[] = array('product'=>$product_id,'credits'=>esc_attr($_POST['google_play_app_products']['credits'][$key]));
						}
					}
					$_POST['google_play_app_products'] = $val;
				}

				if(!empty($sub)){
					$vibebp_settings[$tab][$sub] = apply_filters('vibebp_save_settings',$_POST,$tab);  
				}else{
					$vibebp_settings[$tab] = apply_filters('vibebp_save_settings',$_POST,$tab);  					
				}
				break;
			}

			if(!empty($_POST['sub_tab']) && $_POST['sub_tab'] == 'general'){
				
				foreach($vibebp_settings['general'] as $k=>$field){
					if(empty($_POST[$k]) && isset($vibebp_settings['general'][$k]) && $field == 'on'){
						unset($vibebp_settings['general'][$k]);
					}
				}
				
			}

			update_option(VIBE_BP_SETTINGS,$vibebp_settings);

			echo '<div class="updated"><p>'.__('Settings Saved','vibebp').'</p></div>';
		}else{
			echo '<div class="error"><p>'.__('Unable to Save settings','vibebp').'</p></div>';
		}
	}
	
	function save_member_card(){

		if ( !isset($_POST['security']) || !wp_verify_nonce(sanitize_text_field($_POST['security']),'security') ){
	         die();
      	}
		if(!current_user_can('manage_options') || empty($_POST['card'])){
			die();
		}

		update_option('member_card',wp_kses_post($_POST['card']));

		die();
	}

	function update_wallet_credits(){
		if ( !isset($_POST['security']) || !wp_verify_nonce(sanitize_text_field($_POST['security']),'security') ){
	         die();
      	}
      	if(!current_user_can('manage_options')){
			die();
		}
      	if(!empty($_POST['user']) && !empty($_POST['wallet'])){
      		update_user_meta(intval($_POST['user']),'wallet',intval($_POST['wallet']));
      	}
      	die();
	}

	function userselect_search(){
		if ( !isset($_POST['security']) || !wp_verify_nonce(sanitize_text_field($_POST['security']),'userselect_search') ){
	         die();
      	}
      	if(!current_user_can('manage_options')){
			die();
		}
      	if(!empty($_POST['search'])){
      		$search = sanitize_text_field($_POST['search']);
      		$args = array(
				'search'         => '*'.esc_attr( $search).'*',
				'search_columns' => array( 'user_login', 'user_email','user_nicename','display_name' ),
				'number'=>9999,
				'fields'=>array('ID','display_name','user_email')
			);
			$args = apply_filters('vibebp_member_search_args',$args,$body,$request);
			$user_query = new WP_User_Query( $args );
			$results = $user_query->get_results();
			$return=[];
			if(!empty($results)){
				foreach($results as $user){
					$return[]= array('value'=>$user->ID,'text'=>$user->display_name.' - '.$user->user_email);
				}
			}
			echo json_encode($return);
      	}
      	die();
	}

	function show_app_form(){
	?>
		<h2><?php _e('Build your Mobile App','vibebp');?></h2>
		
		<p>This is a 3 in one app generator. Generate apps for Android, Apple MacOS, Windows. Apple iOS [currently unavailable, work in progress]</p>
		<?php
		if(!vibebp_get_setting('service_workers')){
			echo '<div class="notice notice-error is-dismissible"><p>Enable service workers and generate PWA.<a href="https://www.youtube.com/watch?v=LSHRqf-gm14" target="_blank">Reference Video</a></p></div>';
			return;
		}
		$pwa_url = vibebp_get_setting('offline_page','service_worker','general');
		$pwa_url = get_permalink($pwa_url);
		if(empty($pwa_url)){
			echo '<div class="notice notice-error is-dismissible"><p>Set an offline page for PWA.<a href="https://www.youtube.com/watch?v=LSHRqf-gm14" target="_blank">Reference Video</a></p></div>';
			return;
		}
		$app_name = vibebp_get_setting('app_short_name','service_worker','general');
		if(empty($app_name)){
			echo '<div class="notice notice-error is-dismissible"><p>Set an offline page for PWA.<a href="https://www.youtube.com/watch?v=LSHRqf-gm14" target="_blank">Reference Video</a></p></div>';
			return;
		}

		if ( ! function_exists( 'get_home_path' ) ) {
            include_once ABSPATH . '/wp-admin/includes/file.php';
        }
		$site_root = get_home_path();	
		if(!file_exists($site_root.'/manifest.json')){
			echo '<div class="notice notice-error is-dismissible"><p>Missing manifest for the PWA.<a href="https://www.youtube.com/watch?v=LSHRqf-gm14" target="_blank">Reference Video</a></p></div>';
			return;
		}

					            
		if(!file_exists($site_root.'/firebase-messaging-sw.js')){
			echo '<div class="notice notice-error is-dismissible"><p>Service Worker missing for the PWA.<a href="https://www.youtube.com/watch?v=LSHRqf-gm14" target="_blank">Reference Video</a></p></div>';
			return;
		}

		$site_root = get_home_path();				            
		if(!file_exists($site_root.'/firebase-messaging-sw.js')){
			echo '<div class="notice notice-error is-dismissible"><p>Service Worker missing for the PWA.<a href="https://www.youtube.com/watch?v=LSHRqf-gm14" target="_blank">Reference Video</a></p></div>';
			return;
		}
		$site_url = site_url();
		if(stripos($site_url, 'localhost') || stripos($site_url, '127.0.0.1')){
			echo '<div class="notice notice-error is-dismissible"><p>Apps can not be generated for local development environments. Needs to be publically accessible.<a href="https://www.youtube.com/watch?v=LSHRqf-gm14" target="_blank">Reference Video</a></p></div>';
			return;
		}
		
		if(stripos($pwa_url, 'localhost') || stripos($pwa_url, '127.0.0.1')){
			echo '<div class="notice notice-error is-dismissible"><p>Apps can not be generated for local development environments. Needs to be publically accessible.<a href="https://www.youtube.com/watch?v=LSHRqf-gm14" target="_blank">Reference Video</a></p></div>';
			return;
		}
		?>
		<iframe width="560" height="315" src="https://www.youtube.com/embed/8QkRH3yE6Hg" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		<?php

	}

	function service_workers(){
		
		$template_array = apply_filters('vibebp_service_worker_settings_tabs',array(
			'general'=> __('General Settings','vibebp'),
			'background_sync'=> __('Background Sync','vibebp'),
			'push_notification'=> __('Push Notifications','vibebp'),
		));
		echo '<h3>'.__('Service Workers','vibebp').(file_exists($_SERVER['DOCUMENT_ROOT'].'/firebase-messaging-sw.js')?'<a class="button-primary generate_service_worker">'.__('Regenerate Service Worker','vibebp').'</a>':'<a class="button-primary generate_service_worker">'.__('Generate Service Worker','vibebp').'</a>').'</h3>';


		echo '<ul class="subsubsub">';
		foreach($template_array as $k=>$value){
			if(empty($_GET['sub']) && empty($current)){
				$current = $k;
			}else if(!empty($_GET['sub']) && empty($current)){
				$current = sanitize_text_field($_GET['sub']);
			}
			echo '<li><a href="?page='.VIBE_BP_SETTINGS.'&tab=service&sub='.$k.'" '.(($k == $current)?'class="current"':'').'>'.$value.'</a>  &#124; </li>';
		}
		echo '</ul><div class="clear"><hr/>';

		if(empty($_GET['sub'])){$_GET['sub']='general';}

		if(empty($this->pages)){
			$query = new WP_Query(array(
				'post_type'=>'page',
				'posts_per_page'=>-1
			));
			$this->pages[]=__('Select page','vibebp');
			while($query->have_posts()){
				$query->the_post();
				$this->pages[get_the_ID()]=get_the_title();
			}
		}
		$sub = sanitize_text_field($_GET['sub']);
		switch($sub){
			case 'push_notification':
				if(!class_exists('WPLMS_Push_Notifications_Init')){
					?>
					<div id="message" class="warning fade">
						<p><?php _e('Push Notification addon required !','vibebp'); ?></p>
					</div>
					<?php
				}
			break;
			case 'background_sync':
				?>
					<div id="message" class="warning fade">
						<p><?php _e('Coming up.','vibebp'); ?></p>
					</div>
					<?php
			break;
			default:
				$service_worker_settings = apply_filters('vibebp_service_workers_general_settings',array(
					array(
						'label' => __('Version','vibebp'),
						'name' => 'version',
						'type' => 'text',
						'default' => '0.0001',
						'desc' => __('Service Worker Version. Updates service workers, clears out cached scripts and other API data.','vibebp'),
					),
					array(
						'label' => __('App Name','vibebp'),
						'name' => 'app_name',
						'type' => 'text',
						'default' => get_bloginfo('name'),
						'desc' => __('App name when users download on desktop','vibebp'),
					),
					array(
						'label' => __('App Shortname','vibebp'),
						'name' => 'app_short_name',
						'type' => 'text',
						'default' => get_bloginfo('name'),
						'desc' => __('App name when users download on desktop','vibebp'),
					),
					array(
						'label' => __('App description','vibebp'),
						'name' => 'app_description',
						'type' => 'textarea',
						'desc' => __('App description when users download on desktop','vibebp'),
						'default'=>get_bloginfo('description'),
					),
					array(
						'label' => __('Theme Color','vibebp'),
						'name' => 'theme_color',
						'type' => 'color',
						'default' => '#3ecf8e',
						'desc' => __('App theme color','vibebp'),
					),
					array(
						'label' => __('App display mode','vibebp'),
						'name' => 'app_display',
						'type' => 'select',
						'options'=>array(
							'fullscreen'=>_x('Fullscreen','setting','vibebp'),
							'standalone'=>_x('StandAlone','setting','vibebp'),
						),
						'desc' => __('Select the app display mode,fullscreen or others','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('App Icon','vibebp'),
						'name' => 'app_icon',
						'type' => 'upload',
						'desc' => __('Recommended Size 512x512','vibebp'),
						'button_label'=>__('Set App Icon','vibebp'),
						'button_title'=>__('Select App Icon Image','vibebp'),
						'desc'=>'<a href="https://maskable.app/" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>',
					),
					array(
						'label' => __('Default Image','vibebp'),
						'name' => 'default_image',
						'type' => 'upload',
						'button_label'=>__('Set default image','vibebp'),
						'button_title'=>__('Select Fallback Image','vibebp'),
						'desc' => __('Fallback image when image not available in offline mode.','vibebp'),
					),
					array(
						'label' => __('App Splashscreen','vibebp'),
						'name' => 'app_screenshot',
						'type' => 'upload',
						'button_label'=>__('Set Splash Screen','vibebp'),
						'button_title'=>__('Select Splash Screen Image','vibebp'),
						'desc' => __('Recommended Size 2732x2732','vibebp').'<a href="https://developer.apple.com/design/human-interface-guidelines/ios/visual-design/launch-screen/"><span class="dashicons dashicons-editor-help"></span></a>',
					),
					array(
						'label' => __('Offline Page URL [Required]','vibebp'),
						'name' => 'offline_page',
						'type' => 'select',
						'options'=>$this->pages,
						'desc' => __('App home, this is cached. Set to User profile or custom page with VibeBp profile shortcode to enable app in offline mode','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Scope of service worker is root','vibebp'),
						'name' => 'root_is_scope_for_sw',
						'type' => 'checkbox',
						'desc' => __('Check this if your site is installed on root and not on any another url like site.com/mysite','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Enable Cache first strategy','vibebp'),
						'name' => 'cache_first',
						'type' => 'checkbox',
						'desc' => __('Cache first strategy everything loads from cache and user has to refresh.','vibebp'),
						'default'=>''
					),
					array(
						'label' => __('Pre-Cache Resources','vibebp'),
						'name' => 'pre-cached',
						'type' => 'repeatable',
						'placeholder'=>__('Enter Script/Style URL','vibebp'),
						'desc' => __('Additional scripts which need to be precached. All VibeBP & Addon scripts are cached by default.','vibebp'),
						'default'=>''
					),
				)
	);
			$this->vibebp_settings_generate_form('service_worker',$service_worker_settings);
			break;
		}
		
	}

	function wallet(){
		
		$template_array = apply_filters('vibebp_service_worker_settings_tabs',array(
			'general'=> __('General Settings','vibebp'),
			'balance'=> __('Balance','vibebp'),
		));
		echo '<h3>'.__('Wallet','vibebp').'</h3>';


		echo '<ul class="subsubsub">';
		foreach($template_array as $k=>$value){
			if(empty($_GET['sub']) && empty($current)){
				$current = $k;
			}else if(!empty($_GET['sub']) && empty($current)){
				$current = sanitize_text_field($_GET['sub']);
			}
			echo '<li><a href="?page='.VIBE_BP_SETTINGS.'&tab=wallet&sub='.$k.'" '.(($k == $current)?'class="current"':'').'>'.$value.'</a>  &#124; </li>';
		}
		echo '</ul><div class="clear"><hr/>';

		if(empty($_GET['sub'])){$_GET['sub']='general';}

		if(empty($this->pages)){
			$query = new WP_Query(array(
				'post_type'=>'page',
				'posts_per_page'=>-1
			));
			$this->pages[]=__('Select page','vibebp');
			while($query->have_posts()){
				$query->the_post();
				$this->pages[get_the_ID()]=get_the_title();
			}
		}
		$sub = sanitize_text_field($_GET['sub']);
		switch($sub){
			case 'balance':
				$this->show_balance_list();
			break;
			default:
			$wallet_settings = apply_filters('vibebp_wallet_general_settings',array(
				array(
					'label' => __('Recharge Wallet Credits','vibebp'),
					'name' => 'buy_credits',
					'type' => 'select',
					'options'=>$this->pages,
					'desc' => __('Users can purchase credits from this page','vibebp'),
					'default'=>'',
				),
				array(
					'label' => __('Credit currency symbol','vibebp'),
					'name' => 'credits_symbol',
					'type' => 'text',
					'desc' => __('A symbol prepends credits display [ text or glyph ]','vibebp'),
					'default'=>'',
				),
				array(
					'label' => __('Google play credits setup','vibebp'),
					'name' => 'google_play_app_products',
					'type' => 'app_products',
					'desc' => __('Google play products configuration','vibebp'),
					'default'=>'',
				),
				array(
                        'label' => __('Google play package name','vibebp'),
                        'name' => 'google_play_package_name',
                        'type' => 'text',
                        'default' => '',
                        'desc' => __('Google play package name for wallet purchase verification','vibebp'),
                    ),
				array(
					'label' => __('Google play service account json','vibebp'),
					'name' => 'google_play_service_account_json',
					'type' => 'textarea',
					'desc' => __('Google play service account json','vibebp'),
					'default'=>'',
				),
				
			));
			$this->vibebp_settings_generate_form('wallet',$wallet_settings);
			break;
		}
		
	}

	function regenerate_service_worker(){

		if(wp_verify_nonce(sanitize_text_field($_POST['security']),'security')){
			$this->remove_old_stale_requests_firebase();
			$actions = VibeBP_Actions::init();
			$actions->generate_manifest(1);
			$actions->install_sw(1);
			
			echo json_encode(array('status'=>1,'message'=>__('Successfully regenerated','vibebp')));
		}
		die();
	}

	function ajax_button_call(){
		if(wp_verify_nonce(sanitize_text_field($_POST['security']),'security')){
			do_action('vibebp_ajax_button_call',esc_attr($_POST['id']));
		}else{
			echo 'Security failed';
		}
		die();
	}


	function remove_old_stale_requests_firebase(){
		if(class_exists('Vibebp_Firebase_Tokens')){
			$vft = Vibebp_Firebase_Tokens::init();
			$token =$vft->vibebp_firebase_generate_id_token();
			if(!empty($token)){
				$firebase_config = unserialize(vibebp_get_setting('firebase_config'));
				if(!empty($firebase_config['databaseURL'])){
					$requests =[];
					$requests[] = array(
						'url' => $firebase_config['databaseURL'].'/stale_requests.json?auth='.$token,
				        'type' => 'DELETE',
					);
					
					Requests::request_multiple($requests);
				}
				
			}
		}
	}

	function repeatable_script(){
		?>
		<script>
			jQuery(document).ready(function($){
				$('.add_new_repeatable').on('click',function(){
					$(this).parent().find('ul').append('<li><input type="text" name="'+$(this).attr('data-name')+'" placeholder="'+$(this).attr('data-placeholder')+'" /><span class="dashicons dashicons-no-alt remove_item"></span></li>');
					$('.remove_item').on('click',function(){
						$(this).parent().remove();
					});
				});

				$('.app_products_repeatable').on('click',function(){
					$(this).parent().find('ul').append('<li><input type="text" name="'+$(this).attr('data-name')+'[product][]" placeholder="<?php echo _X('product id','','vibebp')?>" /><input type="number" name="'+$(this).attr('data-name')+'[credits][]" placeholder="<?php echo _X('Credits','','vibebp')?>" /><span class="dashicons dashicons-no-alt remove_item"></span></li>');
					$('.remove_item').on('click',function(){
						$(this).parent().remove();
					});
				});

				$('.remove_item').on('click',function(){
					$(this).parent().remove();
				});

				$('.generate_service_worker').on('click',function(){
					let $this = $(this);
					let text = $this.text();
					$this.text('...');
					$(this).addClass('disabled');
					$.ajax({
			          	type: "POST",
			          	url: ajaxurl,
			          	dataType:'json',
			          	data: { action: 'regenerate_service_worker',
			                  security:'<?php echo wp_create_nonce('security','security'); ?>',
			                },
			          	cache: false,
			          	success: function (json) {
			            	if(json.status){
			            		$this.text(json.message);
			            		setTimeout(function(){
			            			$this.text(text);
			            			$this.removeClass('disabled');
			            		},4000);
			            	}
			          	}
			        });
				})
			});
		</script><style>.forminp img{max-width:320px;}</style>
		<?php
	}

	function member_profile_card() {
	    add_meta_box( 'member_type_selector', __( 'Apply on Member Type', 'vibebp' ), array($this,'member_type_selector'), 'member-profile','side' );
	    add_meta_box( 'member_type_selector', __( 'Apply on Member Type', 'vibebp' ), array($this,'member_type_selector'), 'member-card' ,'side');

	    //Add meta box selection in User extended profile
		
	}

	function user_metabox($true,$user_id){
		$screen_id = get_current_screen()->id;
		add_meta_box( 'member_profile_selector', __( 'Select Member Profile Layout', 'vibebp' ), array($this,'member_profile_selector'), $screen_id,'side' );
	}
	function member_profile_selector($user = null){

		// Bail if no user ID.
		if ( empty( $user->ID ) ) {
			return;
		}
		
		$profile_layout = get_user_meta($user->ID,'member_profile',true);
		?>
		<label for="bp-members-profile-member-type" class="screen-reader-text"><?php
			/* translators: accessibility text */
			esc_html_e( 'Select Member Profile Layout', 'vibebp' );
		?></label>
		<select name="member_profile">
			<option value=""><?php _ex('Select Member Profile','vibebp'); ?></option>
			<?php
			$query = new WP_Query(array(
				'post_type'=>'member-profile',
				'posts_per_page'=>-1
			));
			if($query->have_posts()){
				while($query->have_posts()){
					$query->the_post();
					echo '<option value="'.get_the_ID().'" '.($profile_layout == get_the_ID()?'selected':'').'>'.get_the_title().'</option>';
				}
			}
			?>
		</select>
		<input type="hidden" name="wpadmin_check" value="1" />
		<?php
		wp_nonce_field( 'bp-member-profile-change-' . $user->ID, 'bp-member-profile-nonce' );
	}

	function process_member_profile_update(){

		if ( ! isset( $_POST['bp-member-profile-nonce'] ) || ! isset( $_POST['member_profile'] ) ) {
			return;
		}
		
		$user_id = (int) get_current_user_id();

		// We'll need a user ID when not on self profile.
		if ( ! empty( $_GET['user_id'] ) ) {
			$user_id = intval( $_GET['user_id']);
		}
		

		if(empty($_POST['wpadmin_check']))
			return;

		// Permission check.
		if ( ! bp_current_user_can( 'bp_moderate' ) && $user_id != bp_loggedin_user_id() ) {
			return;
		}

		
		// Member type string must either reference a valid member type, or be empty.
		$member_profile = stripslashes(wp_kses_post( $_POST['member_profile']));
		update_user_meta($user_id,'member_profile',$member_profile);
	}

	function group_layout_card(){
		add_meta_box( 'group_type_selector', __( 'Apply on Group Type', 'vibebp' ), array($this,'group_type_selector'), 'group-layout','side' );
	    add_meta_box( 'group_type_selector', __( 'Apply on Group Type', 'vibebp' ), array($this,'group_type_selector'), 'group-card','side' );
	}
	function member_type_selector(){
		$types = bp_get_member_types(array(),'objects');
		global $post;
		$selected_type = get_post_meta($post->ID,'member_type',true);
		?>
		<select name="member_type">
			<option value=""><?php _ex('Select Member Type','vibebp'); ?></option>
			<?php
				if(!empty($types)){
					
					foreach($types as $type => $labels){
						echo '<option value="'.$type.'" '.($selected_type == $type?'selected':'').'>'.$labels->labels['name'].'</option>';	
					}
					
				}
			?>
		</select>
		<input type="hidden" name="wpadmin_check" value="1" />
		<?php
		wp_nonce_field( 'bp-member-type-change-' . $post->ID, 'bp-member-type-nonce' );
	}

	function group_type_selector(){

		if(!function_exists('bp_groups_get_group_types'))
			return;
		$types = bp_groups_get_group_types(array(),'objects');
		global $post;
		$selected_type = get_post_meta($post->ID,'group_type',true);
		?>
		<select name="group_type">
			<option value=""><?php _ex('Select Group Type','vibebp'); ?></option>
			<?php
				if(!empty($types)){
					
					foreach($types as $type => $labels){
						echo '<option value="'.$type.'" '.($selected_type == $type?'selected':'').'>'.$labels->labels['name'].'</option>';	
					}
					
				}
			?>
		</select>
		<input type="hidden" name="wpadmin_check" value="1" />
		<?php
		wp_nonce_field( 'bp-group-layout-change-' . $post->ID, 'bp-group-layout-nonce' );
	}

	function save_member_profile_card($post_id){
		
		
		if(empty($_POST['wpadmin_check']))
			return;

		if(!empty($_POST['member_type']) && current_user_can('manage_options')){
			update_post_meta($post_id,'member_type',sanitize_title($_POST['member_type']));
		}else{
			delete_post_meta($post_id,'member_type');
		}

	}

	function save_group_layout_card($post_id){

		
		if(empty($_POST['wpadmin_check']))
			return;
		if(!empty($_POST['group_type']) && current_user_can('manage_options')){
			update_post_meta($post_id,'group_type',sanitize_title($_POST['group_type']));
		}else{
			delete_post_meta($post_id,'group_type');
		}
	}

	function set_group_layout(){

		add_meta_box( 'bp_group_layout_settings', _x( 'Group Layout', 'group admin edit screen', 'vibebp' ), array($this,'show_group_layouts'), get_current_screen()->id, 'side', 'core' );
	}

	function show_group_layouts($item){
		// Bail if no user ID.
		if ( empty( $item->id ) ) {
			return;
		}
		
		$group_layout = groups_get_groupmeta($item->id,'group_layout',true);
		?>
		<label for="bp-group-layouts" class="screen-reader-text"><?php
			/* translators: accessibility text */
			esc_html_e( 'Select Group Layout', 'vibebp' );
		?></label>
		<select name="group_layout">
			<option><?php _ex('Select Group Layout','vibebp'); ?></option>
			<?php
			$query = new WP_Query(array(
				'post_type'=>'group-layout',
				'posts_per_page'=>-1
			));
			if($query->have_posts()){
				while($query->have_posts()){
					$query->the_post();
					echo '<option value="'.get_the_ID().'" '.($group_layout == get_the_ID()?'selected':'').'>'.get_the_title().'</option>';
				}
			}
			?>
		</select>
		<input type="hidden" name="wpadmin_check" value="1" />
		<?php
		wp_nonce_field( 'bp-group-layout-change-' . $item->id, 'bp-group-layout-nonce' );
	}

	function bp_groups_process_group_layout_update( $group_id ) {
		if ( ! isset( $_POST['bp-group-layout-nonce'] ) ) {
			return;
		}

		if(empty($_POST['wpadmin_check']))
			return;

		// Permission check.
		if ( ! bp_current_user_can( 'bp_moderate' ) ) {
			return;
		}

		$group_layout = ! empty( $_POST['group_layout'] ) ? wp_unslash( $_POST['group_layout'] ) : array();

		groups_update_groupmeta($group_id,'group_layout',$group_layout);
	}

	function touch_points(){
		echo '<h3>'.__('User Touch Points','vibebp').'</h3>';
		echo '<p>'.__('Set touch points for Users in Vibebp.','vibebp').'</p>';
		
			$this->touchpoints = $this->get_touch_points();
			$this->vibebp_settings_generate_form('touch',$this->touchpoints);
	}

	function get_touch_points(){
		$settings = [];
		return apply_filters('vibebp_touch_points',$settings);
	}

	function registration_forms(){
		echo '<h3>'.__('Registration Forms','vibebp').'</h3>';
		echo '<p>'.sprintf(__('Build registration forms for Students and Instructors, refer %s tutorial %s','vibebp'),'<a href="https://wplms.io/support/knowledge-base/custom-registration-forms-in-wplms/">','</a>').'</p>';
		if(!function_exists('bp_xprofile_get_groups')){
			echo _x('xProfile fields not enabled','error message displayed in registration forms when xprofile are disabled','vibebp');
			return;
		}

		//for groups selection select2
		?>

		<script type="text/javascript">
			jQuery(document).ready(function($){
				
				$('#wplms_user_bp_group').on("change", function() {
			       var values = $(this).val();
			       var $this = $(this);
			       console.log(values);
			       var check = ['enable_user_select_group'];
			       if(typeof values != 'undefined' && values != null && jQuery.inArray('enable_user_select_group',values) > -1){
			       		$.each($this.find('option:not(.all)'),function(){
			       			var $option = $(this);
			       			$option.removeAttr('selected');
			       		});
			       		if(!compareArrays(values,check)){
			       			$this.trigger('change');
			       		}
			       }
			    });
			    
				function compareArrays(a, b) {
				    return !a.some(function (e, i) {
				        return e != b[i];
				    });
				}
			});
		</script>
		<?php
		$groups = bp_xprofile_get_groups( array(
			'fetch_fields' => true
		) );

		if(empty($groups)){
			echo _x('No fields found !','error message displayed in registration forms when no xprofile fields exist','vibebp');
			return;
		}
		global $wp_roles;

		if ( ! isset( $wp_roles ) )
		    $wp_roles = new WP_Roles();

		$registration_emails = new WP_Query(array(
			'post_type'=>'bp-email',
			'posts_per_page'=>-1,
			'tax_query' => array(
				array(
					'taxonomy' => 'bp-email-type',
					'field'    => 'slug',
					'terms'    => 'core-user-registration',
				),
			),
		));
		$registration_mails = array('no'=>__('Disable activation email and manually approve accounts.','vibebp'));
		if ( $registration_emails->have_posts() ) {
			
			while ( $registration_emails->have_posts() ) {
				$registration_emails->the_post();
				$registration_mails[get_the_ID()]=get_the_title();
			}
			
			/* Restore original Post Data */
			wp_reset_postdata();
		}
		//Sync with Vibe shortcodes Ajax calls and Shortcode.php
		
		$types = bp_get_member_types(array(),'objects');
		$mtypes = [];
		if(!empty($types)){
			$mtypes['enable_user_member_types_select'] = _x('Enable user to select','','vibebp');
			foreach($types as $type => $labels){
				$mtypes[$type]=$labels->labels['name'];
			}
		}

		$form_settings = array(
			'hide_username' =>  __('Auto generate username from email','vibebp'),
			'password_meter' =>  __('Show password meter','vibebp'),
			'show_group_label' =>  __('Show Field group labels','vibebp'),
			'google_captcha' => __('Google Captcha','vibebp'),
			'auto_login'=> __('Register & Login simultaneously','vibebp'),
			'skip_mail' =>  __('Skip Mail verification','vibebp'),
			'custom_activation_mail' =>  array( 
								'label' => __('Custom Activation Mail ','vibebp'),
								'default_option' => _x('Default activation email is sent','registration form','vibebp'),
								'options' => $registration_mails
							),
			'default_role' =>  array( 
								'label' => __('Assign User role','vibebp'),
								'default_option' => _x('Default role','role in registration form','vibebp'),
								'options' => $wp_roles->get_names()

							),
			'member_type' =>  array( 
								'label' => __('Assign Member Type','vibebp'),
								'default_option' => _x('None','','vibebp'),
								'options' => $mtypes
							),
		);
		
		$form_settings=apply_filters('vibebp_registration_form_settings',$form_settings);
		/*
			FORM CREATION
		*/
		
		$forms = get_option('vibebp_registration_forms');
		
		if(!empty($_POST['wplms_create_registration_from']) && !empty($_POST['vibebp_registration_form_security']) && !empty($_POST['wplms_add_registration_form'])){
			if(wp_verify_nonce($_POST['vibebp_registration_form_security'],'wplms_security')){
				if(empty($forms)){$forms=array();}
				$name = strtolower(strip_tags($_POST['wplms_add_registration_form']));
				$name = str_replace(' ','_',$name);
				$forms[$name] = array();
				update_option('vibebp_registration_forms',$forms);
			}
		}

		// SAVE FORM FIELDS
		if(!empty($_POST['wplms_save_registration_fields']) && !empty($_POST['wplms_save_registration_form_fields'])){
			if(wp_verify_nonce($_POST['wplms_save_registration_form_fields'],'wplms_fields_security')){
				if(!empty($forms) && !empty($_POST)){

					foreach($forms as $k=>$v){
						$k = str_replace(' ','_',$k); //Sanitize form names
						$forms[$k]=$v;
					}
					$form_names = array_keys($forms);
					foreach($form_names as $name){
						unset($forms[$name]['fields']);
					}
					
					foreach($_POST as $label=>$value){
						if(!in_array($label,array('wplms_save_registration_form_fields','_wp_http_referer','wplms_save_registration_fields'))){
							$names = explode('|',$label);							
							if(!empty($names) && isset($forms[$names[1]])){
								if(empty($forms[$names[1]])){
									$forms[$names[1]] = array('fields'=>array($names[0]));
								}else if(empty($forms[$names[1]]['fields'])) {
									$forms[$names[1]]['fields'] = array($names[0]);
								}else if(!in_array($names[0],$forms[$names[1]]['fields'])){
									$forms[$names[1]]['fields'][] = $names[0];
								}
							}
						}
					}
					update_option('vibebp_registration_forms',$forms);
				}
			}
		}

		if(!empty($_POST['vibebp_registration_form_sub_security']) && !empty($_POST['registration_form_name'])){
			if(wp_verify_nonce($_POST['vibebp_registration_form_sub_security'],'wplms_sub_security')){
				
				if(isset($_POST['default_registration_form'])){ 
					// UNSET ALL DEFAULT KEYS
					foreach($forms as $key=>$f){
						if(!empty($f) && isset($f['default'])){
							unset($forms[$key]['default']);
						}
					}
					//SET THE CURRENT DEFAULT KEY
					if(empty($forms[strip_tags($_POST['registration_form_name'])])){
						$forms[strip_tags($_POST['registration_form_name'])] = array('default'=>1);
					}else{
						$forms[strip_tags($_POST['registration_form_name'])]['default'] = 1;
					}
				}else if(!empty($_POST['remove_registration_form'])){
					if(isset($forms[strip_tags($_POST['registration_form_name'])])){
						unset($forms[strip_tags($_POST['registration_form_name'])]);
					}
				}
				update_option('vibebp_registration_forms',$forms);
			}
		}

		if(!empty($_POST['save_form_settings']) && !empty($_POST['registration_form_name'])){
			if(wp_verify_nonce($_POST['vibebp_registration_form_sub_security'],'wplms_sub_security')){
				$forms[$_POST['registration_form_name']]['settings'] = array();
				foreach($_POST as $k => $la){
					if(!in_array($k,array('registration_form_name','vibebp_registration_form_sub_security','_wp_http_referer'))){
						$sv = explode('|',$k);
						$forms[$_POST['registration_form_name']]['settings'][$sv[0]]=$la;
					}
				}
				update_option('vibebp_registration_forms',$forms);
			}
		}
		if(!empty($forms))
			$form_names = array_keys($forms);

		if(!empty($forms)){
			$default = 0;
			foreach($form_names as $i=>$name){
				if(!empty($forms[$name]['default']) && $forms[$name]['default'] == 1){
					$default = $name;
				}
			}
			echo '<h3>'._x('Existing Registration forms','Forms registered in site','vibebp').'</h3>
			<ul class="registration_field_groups">';
			foreach($form_names as $i=>$name){
				if(empty($default) && $i ==0){$default = $name;}
				$name = str_replace(' ','_',$name);
				echo '<li><form method="post"><label class="field_name">'.$name.'&nbsp;<br>

				<span style="font-weight:400;text-transform:none;"><code id="'.$name.'" onclick="copyToClipboard(\'#'.$name.'\')">[vibebp_registration_form name="'.$name.'" field_meta=1]</code>

				</span> <small style="font-weight:200; font-size:12px;text-transform:none;">
				<br>('.__('field_meta for field description & visbility','vibebp').')</small></label><input type="hidden" value="'.$name.'" name="registration_form_name"><input type="submit" name="default_registration_form" class="button '.(($default == $name)?'button-primary':'').'"  value="'.(($default == $name)?__('Default','vibebp'):_x('Set as default','set a default registration form','vibebp')).'">&nbsp;<a class="button" onClick="jQuery(this).parent().find(\'.registration_form_settings\').toggle(200);">'._x('Settings','delete button label','vibebp').'</a>&nbsp;<input type="submit" name="remove_registration_form" class="button" value="'._x('Delete','delete button label','vibebp').'">';
				echo '<div class="registration_form_settings" style="display:none;">';
				echo '<ul class="registration_field_groups" style="padding:10px;">';
				
				foreach($form_settings as $key => $label){
					$key = str_replace(' ','_',$key);
					echo '<li>';
					if(is_array($label)){
						echo '<label class="field_name">'.$label['label'].'</label><select name="'.$key.'|'.$name.'"><option value="">'.$label['default_option'].'</option>';
						foreach($label['options'] as $k=>$l){
							echo '<option value="'.$k.'" '.((isset($forms[$name]['settings'][$key]) && $forms[$name]['settings'][$key] == $k)?'selected':'').'>'.$l.'</option>';
						}
						echo '</select>';
					}else{
						echo '<label class="field_name">'.$label.'</label><input type="checkbox" name="'.$key.'|'.$name.'" '.(isset($forms[$name]['settings'][$key])?'checked':'').'/></li>';	
					}
				}
				// groups select
				if(function_exists('bp_is_active') && bp_is_active('groups') && class_exists('BP_Groups_Group')){
					$vgroups = BP_Groups_Group::get(array(
							'type'=>'alphabetical',
							'per_page'=>999
							));
					$vgroups = apply_filters('wplms_custom_registration_form_groups_select_settings_form',$vgroups);
					if(!empty($vgroups['groups'] ) && count($vgroups['groups'] )){
						echo '<li><label class="field_name">'.__('Add to Buddypress group','vibebp').'</label><select multiple class="select2 chosen" name="wplms_user_bp_group|'.$name.'[]" id="wplms_user_bp_group">
						

						<optgroup  label="'._x('All groups','','vibebp').'">
						<option  class="all" value="enable_user_select_group" '.((isset($forms[$name]['settings']['wplms_user_bp_group']) && is_array($forms[$name]['settings']['wplms_user_bp_group']) && in_array('enable_user_select_group',$forms[$name]['settings']['wplms_user_bp_group']))?'selected="selected"':'').'>'._x('Enable user to select from all groups','','vibebp').'</option>

						</optgroup>';

						echo '<optgroup groupid="selected_groups" label="'._x('Selected Groups','','vibebp').'">';
						foreach ($vgroups['groups'] as $key => $group) {
							echo '<option value="'.$group->id.'"  '.((isset($forms[$name]['settings']['wplms_user_bp_group']) && is_array($forms[$name]['settings']['wplms_user_bp_group']) && in_array($group->id,$forms[$name]['settings']['wplms_user_bp_group']))?'selected="selected"':'').'>'.$group->name.'</option>';
						}
						echo '</optgroup></select></li>';
					}
				}
				?>
				
				<?php
				do_action('vibebp_registration_form_setting',$name);
				echo '<li><input type="submit" name="save_form_settings" class="button-primary" value="'._x('Save','save form settings','vibebp').'" /></li>';
				echo '</ul>';
				echo '</div>';
				wp_nonce_field('wplms_sub_security','vibebp_registration_form_sub_security');
			echo '</form></li>';
			}
			echo '</ul>';
			?>
			<script>
			function copyToClipboard(element) {
			    var $temp = jQuery("<input>");
			    jQuery("body").append($temp);
			    $temp.val(jQuery(element).text()).select();
			    document.execCommand("copy");
			    $temp.remove();
			    alert('<?php _e('Shortcode Copied !','vibebp'); ?>');
			}
			</script>
			<?php
			
		}
		echo '<a id="create_registration_form" onClick="jQuery(this).next().toggle(200);" class="button-primary">'._x('Add Registration Form','create registration form button label','vibebp').'</a><form method="post" style="display:none;"><br>';
		echo '<input type="text" name="wplms_add_registration_form" style="width:50%;" placeholder="'._x('Type the name of the form, avoid spaces and special characters','enter form name placeholder','vibebp').'"><input type="submit" name="wplms_create_registration_from" class="button" value="'._x('Add Form','Add form submit button label','vibebp').'" >';
		wp_nonce_field('wplms_security','vibebp_registration_form_security');
		echo '</form>';

		if(empty($forms)){
			echo '<div class="message error"><p>'._x('No Registration forms found !','warning message when no registration forms are found','vibebp').'</p></div>';
			return;
		}

		
		echo '<br><hr><h3>'._x('Connect Forms with Fields','connect form heading','vibebp').'</h3>
		<form method="post"><ul class="registration_field_groups">';
		foreach($groups as $group){
			echo '<h4>'._x('Field Group','field group prefix in registration form','vibebp').' : '.esc_html( apply_filters( 'bp_get_the_profile_group_name', $group->name ,$group->id) ).'</h4>';
			if ( !empty( $group->fields ) ) {
				echo '<ul class="profile_fields">';
				
				//Form NAMES
				echo '<li><label class="field_name">'._x('Field Name','','vibebp').'</label>';
				if(!empty($form_names)){
					foreach($form_names as $name){
						echo '<label>'.$name.'</label>';
					}
				}
				echo '</li>';

				//CHECK IF FIELDS ENABLED
				foreach ( $group->fields as $field ) {
					$field = xprofile_get_field( $field->id );
					echo '<li>';
					echo '<label class="field_name">'.$field->name.' ( '.$field->type.''.(empty($field->can_delete)?', '._x('Necessary','necessary fields for buddypress registration','vibebp'):'').
					')</label>';
					if(!empty($form_names)){
						foreach($form_names as $name){
							$k = str_replace(' ','_',$field->name);
							$name = str_replace(' ','_',$name);
							echo '<label><input type="checkbox" name="'.$k.'|'.$name.'" '.((isset($forms[$name]['fields']) && in_array($k,$forms[$name]['fields']) || empty($field->can_delete))?'checked':'').' value="1"></label>';
						}
					}
					echo '</li>';
				} // end for
				echo '</ul>';
			}else {
				?>

					<p class="nodrag nofields"><?php _e( 'There are no fields in this group.', 'buddypress' ); ?></p>
				<?php
			}
		}
		echo '</ul><br>';

		wp_nonce_field('wplms_fields_security','wplms_save_registration_form_fields');
		echo '<input type="submit" name="wplms_save_registration_fields" value="'._x('Save Form fields','save form fields label in registration forms lms - settings','vibebp').'" class="button-primary"/>';
		echo '</form><style>.registration_field_groups h4{font-size:16px;opacity:0.8;} .registration_field_groups label.field_name{word-break: break-all; width:300px;display:inline-block;text-transform: uppercase;font-weight:600; } .registration_field_groups label+label{ width:120px;word-break: break-all;padding:0 2px;display:inline-block;text-transform: uppercase;font-weight:600; }</style>';
	}

	function show_balance_list(){
		echo '<h3>'._x('Balance list','Forms registered in site','vibebp').'</h3>';

		echo '<style>table.table.wallet_users{ width:100%; } table.table.wallet_users td img{ width:32px;height:32px; border-radius:50%; } table.table.wallet_users td{ padding:1rem 0 !important; border-bottom:1px solid rgba(0,0,0,0.5) } table.table.wallet_users td:first-child{ display:flex; gap:0.5rem; align-items:center; } table.table.wallet_users td div{ display:flex; flex-direction:column; }ul.user_activities li{ display:flex; justify-content:space-between; width:100%; } ul.user_activities{ width:100%; }</style>';
		if(!empty($_GET['action']) && $_GET['action']=='history'){
			echo '<a class="" href="'.vibebpaddOrUpdateUrlParam('action','').'">'._x('back','','vibebp').'</a>';
			global $wpdb,$bp;
			$user_id = intval($_GET['user']);
			$results = $wpdb->get_results(
				$wpdb->prepare("
				SELECT m.meta_value as value,a.* 
				FROM {$bp->activity->table_name} as a 
				LEFT JOIN {$bp->activity->table_name_meta} as m 
				ON a.id=m.activity_id
				WHERE a.user_id = %d 
				AND m.meta_key = %s
				AND a.component = %s 
				
				ORDER BY a.id DESC",$user_id,'transaction','wallet'),ARRAY_A);
			$transactions = array();
			$d = get_option('date_format');
			$t = get_option('time_format');
			if(!empty($results)){
				echo '<ul class="user_activities">';
				foreach($results as $result){
					echo '<li><span>'.$result['content'].'</span><span>'.date($d.' '.$t,strtotime($result['date_recorded'])).'</span><li>';
				}
				echo '</ul>';
			}
		}else{
			$new_args = array(
				'paged' => isset($_GET['paged']) ? (int)$_GET['paged'] : 1,
				'number' => apply_filters('vibebp_wallet_users_list',20),
				'search' => isset($_GET['s']) ? '*'.esc_attr($_GET['s']) : '',
				'fields' => array('ID','display_name','user_email'),
				'orderby' => isset($_GET['orderby']) ? esc_attr($_GET['orderby']) : '',
				'order' => isset($_GET['order']) ? esc_attr($_GET['order']) : '',
			);
			$user_query = new WP_User_Query( $new_args );

			$nusers = array();
			if ( ! empty( $user_query->get_results() ) ) {
				echo '<input class="search_input" type="text" value="'.sanitize_text_field((!empty($_GET['s'])?esc_attr($_GET['s']):'')).'"/><a class="button search_user" href="'.vibebpaddOrUpdateUrlParam('paged','').'">'._x('Search','','vibebp').'</a>';
				echo '<table class="table wallet_users">';
				echo '<tr><th>'._x('User','','vibebp').'</th><th>'._x('Credits','','vibebp').'</th><th>'._x('History','','vibebp').'</th></tr>';
				$i= 0;
				foreach ( $user_query->get_results() as $user ) {
					$i++;
					$image = get_avatar_url($user->ID);
					$credits = get_user_meta($user->ID,'wallet',true);
					if(empty($credits))$credits=0;

					$history_link = vibebpaddOrUpdateUrlParamarr(array(
						'user'=>$user->ID,
						'action'=>'history'
					));

					
					echo '<tr>
					<td>
					<img src="'.$image.'">
					<div><span class="name">'.$user->display_name.'</span><span class="name">'.$user->user_email.'</span></div>
					</td>
					<td> <input type="number" value="'.$credits.'"><a data-user="'.$user->ID.'" class="button is-primary update_credits">'._x('Update','','vibebp').'</a></a></td>
					<td><a class="button is-primary" href="'.$history_link.'">'._x('History','','vibebp').'</a></td></tr>';
				}
				echo '</table>';
				

				if($i < $user_query->total_users){
					$paged = (!empty($_GET['paged'])?intval($_GET['paged']):1);
					if(!empty($paged) && $paged>1){
						
						echo '<a class="button" href="'.vibebpaddOrUpdateUrlParam('paged',($paged-1)).'">'._x('Previous','','vibebp').'</a>';
					}
					if($user_query->total_users > (($paged)*apply_filters('vibebp_wallet_users_list',20))){
						echo '<a class="button" href="'.vibebpaddOrUpdateUrlParam('paged',($paged+1)).'">'._x('Next','','vibebp').'</a>';
					}
					
				}
			}else{
				echo '<p class="message error">'._x('Users not found!','','vibebp').'</p>';
			}
		}
		?>
		<script>
		function vbpremoveParam(key, sourceURL) {
		    var rtn = sourceURL.split("?")[0],
		        param,
		        params_arr = [],
		        queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
		    if (queryString !== "") {
		        params_arr = queryString.split("&");
		        for (var i = params_arr.length - 1; i >= 0; i -= 1) {
		            param = params_arr[i].split("=")[0];
		            if (param === key) {
		                params_arr.splice(i, 1);
		            }
		        }
		        if (params_arr.length) rtn = rtn + "?" + params_arr.join("&");
		    }
		    return rtn;
		}

		jQuery(document).ready(function($){
			$('.update_credits').on('click',function(event){
				var currtd = $(this).closest('td');
				$.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'update_wallet_credits', 
                            security:'<?php echo wp_create_nonce('security','security'); ?>',
                            wallet:currtd.find('input').val(),
                            user:$(this).data('user'),
                        },
                    cache: false,
                    success: function (html) {
                       alert('<?php echo _x('Credits updated!','','vibebp')?>');
                    }
                });
			});
			$('.search_user').on('click',function(event){
				event.preventDefault();
				let url = vbpremoveParam('paged',window.location.href);
				window.location.href=url+'&s='+$('.search_input').val();
			});
		});
		</script>
		<?php
	}
}



VibeBP_Settings::init();

if(!function_exists('vibe_isJson')){
	function vibe_isJson($string) {
	 	json_decode($string);
	 	return (json_last_error() == JSON_ERROR_NONE);
	}
}
function vibebpaddOrUpdateUrlParamarr($args)
{
    $params = $_GET;
    if(!empty($args) ){
    	foreach($args as $k=>$arg){
    		unset($params[$k]);
    		$params[$k] = $arg;
    	}
    }
    
    return basename($_SERVER['PHP_SELF']).'?'.http_build_query($params);
}
function vibebpaddOrUpdateUrlParam($name, $value)
{
    $params = $_GET;
    unset($params[$name]);
    $params[$name] = $value;
    
    return basename($_SERVER['PHP_SELF']).'?'.http_build_query($params);
}