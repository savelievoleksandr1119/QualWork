<?php
/**
 * Admin Menu 
 *
 * @class       vibebp_Menu
 * @author      VibeThemes
 * @category    Admin
 * @package     vibebp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VibeBP_Menu{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Menu();
        return self::$instance;
    }

	private function __construct(){

		add_action( 'admin_menu', array($this,'register_menu_page'),11 );
		add_action('admin_init',array($this,'setup_wizard'));
		add_action('upgrader_process_complete',array($this,'service_worker_Check'));
		add_action('admin_notices',array($this,'tours_importer'));
	}

	function register_menu_page(){


		add_menu_page( _x('Vibe BP','title','vibebp'), 
			_x('Vibe BP','menu title','vibebp'), 'manage_options', 
			'vibebp', array($this,'dashboard'),'dashicons-buddicons-groups',101 );
		$vibebp_settings = VibeBP_Settings::init();
	    add_submenu_page( 'vibebp', __('Settings','vibebp'), __('Settings','vibebp'),  'manage_options', 'vibebp_settings', array($vibebp_settings ,'vibebp_settings'));
	    add_submenu_page( 'vibebp', __('Add Ons','vibebp'), __('Add Ons','vibebp'),  'manage_options', 'vibebp_addons', array($this,'addons'));
	}

	function dashboard(){
		echo 'This is Awesome Dashboard';
	}

	function service_worker_Check(){
		if(!empty(vibebp_get_setting('cache_first','service_worker'))){
			set_transient( 'vibebp_show_update_service_worker_notice', 1 );
		}
	}


	function get_addons(){
		$this->addons = apply_filters('vibebp_addons',array(
			'vibe-reports'=>array(
                'label'=> __('Vibe Reports','vibebp'),
                'sub'=> __('Report generation system.','vibebp'),
                'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
                'requires'=> '',
                'license_key'=>'vibe_reports_license_key',
                'link' => 'https://vibethemes.com/downloads/vibe-reports',
                'extra'=>array('Report Generation','Share Reports'),
                'activated'=> true,
                'price'=>'Free',
                'included'=>1,
                'tag'=>array('label'=>'Free','class'=>'external'),
            ),
			'vibe-projects'=>array(
                'label'=> __('Vibe Projects','vibebp'),
                'sub'=> __('Work & Project Management System.','vibebp'),
                'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
                'requires'=> '',
                'license_key'=>'vibe_projects_license_key',
                'link' => 'https://vibethemes.com/downloads/vibe-projects',
                'extra'=>array('Tasks & Projects','Kanban Boards & Gantt Charts','Teams & Members'),
                'activated'=> true,
                'price'=>'$59',
                'included'=>1,
                'tag'=>array('label'=>'Paid','class'=>'external'),
            ),
            'vibe-forms'=>array(
                'label'=> __('Vibe Forms','vibebp'),
                'sub'=> __('Dynamic From generation.','vibebp'),
                'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
                'requires'=> '',
                'license_key'=>'vibe_forms_license_key',
                'link' => 'https://vibethemes.com/downloads/vibe-forms',
                'extra'=>array('Chat with Members','Notifications'),
                'activated'=> true,
                'price'=>'Free',
                'included'=>1,
                'tag'=>array('label'=>'Free','class'=>'external'),
            ),
			'vibe-chat'=>array(
                'label'=> __('Live Chat','vibebp'),
                'sub'=> __('Live chat with Community members.','vibebp'),
                'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
                'requires'=> '',
                'license_key'=>'vibe_chat_license_key',
                'link' => 'https://wplms.io/downloads/vibe-chat',
                'extra'=>array('Chat with Members','Notifications'),
                'activated'=> true,
                'price'=>'$29 One time',
                'included'=>1,
                'tag'=>array('label'=>'Paid','class'=>'external'),
            ),
			'vibe-google-meet'=>array(
                'label'=> __('Google Meet','vibebp'),
                'sub'=> __('Google meet extension for Vibe App.','vibebp'),
                'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
                'requires'=> '',
                'license_key'=>'vibe_google_meet_license_key',
                'link' => 'https://wplms.io/downloads/vibe-google-meet',
                'extra'=>array('Google Meet','Calendar API'),
                'activated'=> true,
                'price'=>'FREE',
                'included'=>1,
                'tag'=>array('label'=>'Free','class'=>'external'),
            ),
			'vibe-whatsapp'=>array(
                'label'=> __('WhatsApp Notifications','vibebp'),
                'sub'=> __('Send WhatsApp Notifications.','vibebp'),
                'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
                'requires'=> '',
                'license_key'=>'vibe_whatsapp_license_key',
                'link' => 'https://wplms.io/downloads/vibe-whatsapp',
                'extra'=>array('WhatsApp','Notifications'),
                'activated'=> true,
                'price'=>'$29',
                'included'=>1,
                'tag'=>array('label'=>'Paid','class'=>'external'),
            ),
            'vibe-stripe-payouts'=>array(
                'label'=> __('Stripe Payouts','vibebp'),
                'sub'=> __('Send Commissions to Instructor bank accounts (162 countries)','vibebp'),
                'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
                'requires'=> '',
                'license_key'=>'vibe_whatsapp_license_key',
                'link' => 'https://wplms.io/downloads/vibe-stripe-payouts',
                'extra'=>array('Commission Payouts','Automatic Payouts'),
                'activated'=> true,
                'price'=>'$29',
                'included'=>1,
                'tag'=>array('label'=>'Paid','class'=>'external'),
            ),
			'vibe-razorpay-payouts'=>array(
                'label'=> __('Razorpay Payouts','vibebp'),
                'sub'=> __('Send Commission to Instructor bank accounts (India)','vibebp'),
                'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
                'requires'=> '',
                'license_key'=>'vibe_razorpay_payouts_license_key',
                'link' => 'https://wplms.io/downloads/vibe-razorpay-payouts',
                'extra'=>array('Commission Payouts','Automatic Payouts'),
                'activated'=> true,
                'price'=>'$29',
                'included'=>1,
                'tag'=>array('label'=>'Paid','class'=>'external'),
            ),
			'vibe-zoom' =>array(
				'label'=> __('Zoom Integration','vibebp'),
				'sub'=> __('Video Conferencing with Zoom.','vibebp'),
				'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
				'requires'=> '',
				'license_key'=>'',
				'link' => '',
				'extra'=>array('Zoom','Share with users, group, courses', 'Calendar'),
				'activated'=> (is_plugin_active('vibe-zoom/vibe-zoom.php')?true:false),
				'price'=>0,
				'included'=>1,
				'tag'=>array('label'=>'INCLUDED','class'=>'included'),
			),
			'vibe-bbb' =>array(
				'label'=> __('BigBlueButton Integration','vibebp'),
				'sub'=> __('VideoConferencing with BigBlueButton.','vibebp'),
				'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
				'requires'=> '',
				'license_key'=>'',
				'link' => '',
				'extra'=>array('BBB','Share with users, group, courses', 'Calendar'),
				'activated'=> (is_plugin_active('vibe-bbb/vibe-bbb.php')?true:false),
				'price'=>0,
				'included'=>1,
				'tag'=>array('label'=>'INCLUDED','class'=>'included'),
			),
			'vibe-calendar' =>array(
				'label'=> __('Calendar','vibebp'),
				'sub'=> __('Events & Calendar.','vibebp'),
				'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
				'requires'=> '',
				'license_key'=>'',
				'link' => '',
				'extra'=>array('Calendar','Events', 'Location map'),
				'activated'=> (is_plugin_active('vibe-calendar/loader.php')?true:false),
				'price'=>0,
				'included'=>1,
				'tag'=>array('label'=>'INCLUDED','class'=>'included'),
			),
			'vibedrive' =>array(
				'label'=> __('Drive','vibebp'),
				'sub'=> __('Drive for Members.','vibebp'),
				'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
				'requires'=> '',
				'license_key'=>'',
				'link' => '',
				'extra'=>array('Space per Member','Upload attachments','Share Docs'),
				'activated'=> (is_plugin_active('vibe-calendar/loader.php')?true:false),
				'price'=>0,
				'included'=>1,
				'tag'=>array('label'=>'INCLUDED','class'=>'included'),
			),
			'vibe-kb' =>array(
				'label'=> __('Knowledge Base','vibebp'),
				'sub'=> __('Knowledge Base & Articles, Editor and sharing','vibebp'),
				'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
				'requires'=> '',
				'license_key'=>'',
				'link' => '',
				'extra'=>array('Articles','Sharing','Roles'),
				'activated'=> (is_plugin_active('vibe-kb/loader.php')?true:false),
				'price'=>0,
				'included'=>1,
				'tag'=>array('label'=>'INCLUDED','class'=>'included'),
				'class'=>'featured'
			),
			'vibe-helpdesk' =>array(
				'label'=> __('HelpDesk','vibebp'),
				'sub'=> __('Convert BBPress into a Ticketing Solution.','vibebp'),
				'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
				'requires'=> '',
				'license_key'=>'',
				'link' => '',
				'extra'=>array('Tickets','Agents', 'SLA'),
				'activated'=> (is_plugin_active('vibe-helpdesk/loader.php')?true:false),
				'price'=>0,
				'included'=>1,
				'tag'=>array('label'=>'INCLUDED','class'=>'included'),
			),
			'vibe-appointments' =>array(
				'label'=> __('Appointments','vibebp'),
				'sub'=> __('Appointments Booking.','vibebp'),
				'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
				'requires'=> '',
				'license_key'=>'vibe_appointments_license_key',
				'link' => 'https://wplms.io/downloads/vibe-appointments',
				'extra'=>array('Booking','Video Conferencing', 'Payments'),
				'activated'=> (is_plugin_active('vibe-appointments/loader.php')?true:false),
				'price'=>'$59',
				'included'=>1,
				'tag'=>array('label'=>'$59','class'=>'external'),
			),
			'vibe-blog' =>array(
				'label'=> __('Blog','vibebp'),
				'sub'=> __('Multi-Author Blog Management.','vibebp'),
				'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
				'requires'=> '',
				'license_key'=>'',
				'link' => '',
				'extra'=>array('Blog Posts','Categories/ Tags', 'Comments/ Bookmarks'),
				'activated'=> (is_plugin_active('vibe-blog/loader.php')?true:false),
				'price'=>0,
				'included'=>1,
				'tag'=>array('label'=>'FREE','class'=>'external'),
			),
			'vibe-linkedin-login'=>array(
                'label'=> __('LinkedIn Login','vibebp'),
                'sub'=> __('Login Via LinkedIn.','vibebp'),
                'icon'=> '<span class="dashicons dashicons-portfolio"></span>',
                'requires'=> '',
                'license_key'=>'vibe_linkedin_login_license_key',
                'link' => 'https://wplms.io/downloads/vibe-linkedin-login',
                'extra'=>array('LinkedIn','Linkedin Login connect'),
                'activated'=> class_exists('Vibe_LinkedIn_Init')?true:false,
                'price'=>'$19',
                'included'=>1,
                'tag'=>array('label'=>'$19','class'=>'external'),
            )
		));
	}
	function addons(){
		$this->get_addons();
		
		?>
		<div class="vibebp_addons">
		<?php
		foreach($this->addons as $key=>$addon){ 
			if(!empty($addon) && !empty($addon['label'])){

				$class = apply_filters('vibebp_addon_class','',$addon);

				?>
					<div class="vibebp_addon_block">
						<div class="inside <?php echo $class.' '.(($addon['activated'])?'active':''); ?>">
							<span class="<?php echo $addon['tag']['class']; ?>"><?php echo $addon['price']?$addon['price']:$addon['tag']['label']; ?></span>
							<h3 class=""><?php echo $addon['label']; ?><span><?php echo $addon['sub']; ?></span></h3>
							<?php 
							if(!empty($addon['extra'])){
								if(is_array($addon['extra'])){
									echo '<ul>';
									foreach($addon['extra'] as $ex){
										echo '<li>'.$ex.'</li>';
									}
									echo '</ul>';
								}else{
									echo $addon['extra'];
								}
							}
							if(!empty($addon['license_key']) && $addon['activated']){
								$val = get_option($addon['license_key']);
								?>
								<div class="activate_license">
	                                <form action="<?php  echo admin_url( 'admin.php?page=vibebp_addons'); ?>" method="post">
	                                    <input type="text" id="<?php echo $addon['license_key']; ?>" name="license_key" class="vibe_license_key" value="<?php echo $val ?>" placeholder="<?php _e('Enter License Key','vibebp'); ?>" />
	                                    <?php 
	                                    if(!empty($val) && strpos($class,'invalid') === false){    ?>
	                                    <input type="submit" class="button primary" name="<?php echo $addon['license_key']; ?>" value="Deactivate" />
	                                    <?php
	                                    }else{
	                                        ?>
	                                    <input type="submit" class="button primary" name="<?php echo $addon['license_key']; ?>" value="Activate" />
	                                    <?php
	                                    }
	                                    wp_nonce_field( $key, $key);
	                                    ?>
	                                </form>
	                            </div>
								<?php
							}

							if(empty($addon['included'])){
							?>
							<a href="<?php echo $addon['link']; ?>" target="_blank" class="button"><?php _e('Learn more','vibebp'); ?></a>
							<?php
							}
							?>
						</div>
					</div>
			<?php
				}
			}
			?>
			</div>
			<style>.vibebp_addons {
    				display: grid;
				    grid-template-columns: repeat(auto-fit,minmax(320px,1fr));
				    grid-gap: 1.5rem;
				    margin: 1.5rem 0;
				}

				.vibebp_addon_block {
				    border-radius: 5px;
				    box-shadow: 0 5px 10px rgba(0,0,0,0.1);
				}
				.vibebp_addon_block .inside {
				    padding: 1.5rem;
				    height: calc(100% - 3rem);
				    border-radius: 5px;
				}

				.vibebp_addon_block .inside.active {
				    background: #e9faff;
				}

				.vibebp_addon_block .inside.valid {
				    background: #d4e7c3;
				}
				.vibebp_addon_block >.inside>h3{display:flex;flex-direction:column;}
				.vibebp_addon_block >.inside>h3>span{margin-top:5px;font-size:80%;opacity:0.6;}
				.vibebp_addon_block >.inside>span {
				    color: green;
				    font-weight: 600;
}</style>
			<div class="clear">	</div>
			<?php

	}


	function setup_wizard(){
		$option = get_option('vibebp_version');
		if(empty($option)){
			add_action('admin_notices',array($this,'init_setupwizard'));
		}
		//VIBEBP_VERSION
		add_action('admin_notices',array($this,'vibebp_show_update_service_worker_notice'));
	}

	function vibebp_show_update_service_worker_notice(){

		if(get_transient('vibebp_show_update_service_worker_notice') && empty($_GET['dismiss_service_worker_notice']) && !empty(vibebp_get_setting('cache_first','service_worker')) ){
			?>
			<div class="notice notice-warning is-dismissible">
			    <p><?php _ex('May require Service worker version update & regeneration.','','vibebp'); ?>
			     	<a href="<?php echo admin_url('admin.php?page=vibebp_settings&tab=service_worker'); ?>" class="button-primary"><?php _e('Regenerate SW','vibebp'); ?></a>
			    	<a href="<?php echo esc_url(add_query_arg(array('dismiss_service_worker_notice' => '1'))); ?>"  class="button"><?php _e('Dismiss','vibebp'); ?></a>
			    </p>
			</div>
			<?php
		}
		if(!empty($_GET['dismiss_service_worker_notice'] ) ){
			delete_transient('vibebp_show_update_service_worker_notice');
		}
	}
	function init_setupwizard(){

		if(vibebp_is_setup_complete())
			return;

		wp_enqueue_script('vibebp_setup',plugins_url('../assets/js/vibebp_setup.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION,true);
		wp_enqueue_style('vibebp_setup',plugins_url('../assets/css/backend.css',__FILE__),array(),VIBEBP_VERSION);
		$color = '#666666';

		$blog_id = '';
        if(function_exists('get_current_blog_id')){
            $blog_id = get_current_blog_id();
        }
        $security = get_transient('vibebp_admin_security');
        if(empty($security)){
        	$security = wp_generate_password(12,false,false);
        	set_transient('vibebp_admin_security',$security,60*60);
        }

        
		 wp_localize_script('vibebp_setup','vibebp_setup',apply_filterS('vibebp_setup_wizard',array(
		 		'security'=> $security,
		 		'api'=>Array(
		 			'url'=> get_rest_url($blog_id,Vibe_BP_API_NAMESPACE),
		 			'admin_id'=>get_current_user_id()
		 		),
		 		'installation'=>array(
		 			'title'=>__('Welcome, VibeBP Installer','vibebp'),
		 			'description'=>__('Welcome to the react social network for your site. Get started by installing the required and recommended plugins. Installing the recommended plugins also helps in setting them up along with this setup wizard itself.','vibebp'),
		 			'plugins'=>array(
		 				array(
		 					'plugin'=>'buddypress',
		 					'label' => __('BuddyPress','vibebp'),
		 					'icon'=>'<span class="dashicons dashicons-buddicons-buddypress-logo" style="font-size:2.25rem;    width: 2.25rem;"></span>',
		 					'desc'=>__('Built a powerful and customisable Social Community for your audience.','vibebp'),
		 					'required'=> 1,
		 					'status'=>function_exists('buddypress')?2:(file_exists(plugin_dir_path(__FILE__).'../../buddypress/loader.php')?1:0)
		 				),
		 				array(
		 					'plugin'=>'vibe-chat',
		 					'label' => __('Live Chat','vibebp'),
		 					'desc'=>__('Live Chat Communities, build a customisable Discord OR Slack like community.','vibebp'),
		 					'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22H2L4.92893 19.0711C3.11929 17.2614 2 14.7614 2 12ZM6.82843 20H12C16.4183 20 20 16.4183 20 12C20 7.58172 16.4183 4 12 4C7.58172 4 4 7.58172 4 12C4 14.1524 4.85124 16.1649 6.34315 17.6569L7.75736 19.0711L6.82843 20ZM11 6H13V18H11V6ZM7 9H9V15H7V9ZM15 9H17V15H15V9Z"></path></svg>',
		 					'required'=> 0,
		 					'link'=>'https://vibethemes.com/',
		 					'status'=>function_exists('vibe_chat_plugin_load_translations')?2:(file_exists(plugin_dir_path(__FILE__).'../../vibe-chat/loader.php')?1:0)
		 				),
		 				
		 				array(
		 					'plugin'=>'wplms',
		 					'label' => __('Learning Management','vibebp'),
		 					'desc'=>__('Online learning course & student management for Instructors, Academy, Universities & education institutes.','vibebp'),
		 					'required'=> 0,
		 					'icon'=>'<span class="dashicons dashicons-welcome-learn-more" style="font-size:2.25rem;    width: 2.25rem;"></span>',
		 					'link'=>'https://wplms.io',
		 					'status'=>function_exists('wplms_plugin_load_translations')?2:(file_exists(plugin_dir_path(__FILE__).'../../wplms_plugin/loader.php')?1:0)
		 				),
		 				array(
		 					'plugin'=>'vibe-appointments',
		 					'label' => __('Appointments','vibebp'),
		 					'required'=> 0,
		 					'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M17 3H21C21.5523 3 22 3.44772 22 4V20C22 20.5523 21.5523 21 21 21H3C2.44772 21 2 20.5523 2 20V4C2 3.44772 2.44772 3 3 3H7V1H9V3H15V1H17V3ZM4 9V19H20V9H4ZM6 11H8V13H6V11ZM6 15H8V17H6V15ZM10 11H18V13H10V11ZM10 15H15V17H10V15Z"></path></svg>',
		 					'desc'=>__('Booking Marketplace & Community for Tutors, Doctors, Instructors & groups.','vibebp'),
		 					'link'=>'https://wpappointify.com',
		 					'status'=>function_exists('vibe_appointments_plugin_load_translations')?2:(file_exists(plugin_dir_path(__FILE__).'../../vibe-appointments/loader.php')?1:0)
		 				),
		 				array(
		 					'plugin'=>'vibe-projects',
		 					'label' => __('Projects','vibebp'),
		 					'desc'=>__('Task & Projects management for Companies, Cohorts & developers. .','vibebp'),
		 					'required'=> 0,
		 					'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M8.00008 6V9H5.00008V6H8.00008ZM3.00008 4V11H10.0001V4H3.00008ZM13.0001 4H21.0001V6H13.0001V4ZM13.0001 11H21.0001V13H13.0001V11ZM13.0001 18H21.0001V20H13.0001V18ZM10.7072 16.2071L9.29297 14.7929L6.00008 18.0858L4.20718 16.2929L2.79297 17.7071L6.00008 20.9142L10.7072 16.2071Z"></path></svg>',
		 					'link'=>'https://wpappointify.com',
		 					'status'=>function_exists('vibe_projects_plugin_load_translations')?2:(file_exists(plugin_dir_path(__FILE__).'../../vibe-projects/loader.php')?1:0)
		 				),
		 				array(
		 					'plugin'=>'vibedrive',
		 					'label' => __('Drive','vibebp'),
		 					'desc'=>__('Share Files securely among members. Your own Google Drive. Live transfers like AirDrop','vibebp'),
		 					'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M6 7V4C6 3.44772 6.44772 3 7 3H13.4142L15.4142 5H21C21.5523 5 22 5.44772 22 6V16C22 16.5523 21.5523 17 21 17H18V20C18 20.5523 17.5523 21 17 21H3C2.44772 21 2 20.5523 2 20V8C2 7.44772 2.44772 7 3 7H6ZM6 9H4V19H16V17H6V9ZM8 5V15H20V7H14.5858L12.5858 5H8Z"></path></svg>',
		 					'required'=> 0,
		 					'link'=>'https://vibethemes.com/',
		 					'status'=>function_exists('vibe_drive_plugin_load_translations')?2:(file_exists(plugin_dir_path(__FILE__).'../../vibedrive/loader.php')?1:0)
		 				),
		 				array(
		 					'plugin'=>'vibe-helpdesk',
		 					'label' => __('Forums & Helpdesk','vibebp'),
		 					'desc'=>__('Discussion Forums, Helpdesk & Support portal','vibebp'),
		 					'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M19.9381 8H21C22.1046 8 23 8.89543 23 10V14C23 15.1046 22.1046 16 21 16H19.9381C19.446 19.9463 16.0796 23 12 23V21C15.3137 21 18 18.3137 18 15V9C18 5.68629 15.3137 3 12 3C8.68629 3 6 5.68629 6 9V16H3C1.89543 16 1 15.1046 1 14V10C1 8.89543 1.89543 8 3 8H4.06189C4.55399 4.05369 7.92038 1 12 1C16.0796 1 19.446 4.05369 19.9381 8ZM3 10V14H4V10H3ZM20 10V14H21V10H20ZM7.75944 15.7849L8.81958 14.0887C9.74161 14.6662 10.8318 15 12 15C13.1682 15 14.2584 14.6662 15.1804 14.0887L16.2406 15.7849C15.0112 16.5549 13.5576 17 12 17C10.4424 17 8.98882 16.5549 7.75944 15.7849Z"></path></svg>',
		 					'required'=> 0,
		 					'link'=>'https://vibethemes.com/',
		 					'status'=>function_exists('vibe_helpdesk_plugin_load_translations')?2:(file_exists(plugin_dir_path(__FILE__).'../../vibe-helpdesk/loader.php')?1:0)
		 				),
		 				array(
		 					'plugin'=>'vibe-calender',
		 					'label' => __('Calendar & Events','vibebp'),
		 					'desc'=>__('Personal calendar & events for everyone','vibebp'),
		 					'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M9 1V3H15V1H17V3H21C21.5523 3 22 3.44772 22 4V20C22 20.5523 21.5523 21 21 21H3C2.44772 21 2 20.5523 2 20V4C2 3.44772 2.44772 3 3 3H7V1H9ZM20 11H4V19H20V11ZM7 5H4V9H20V5H17V7H15V5H9V7H7V5Z"></path></svg>',
		 					'required'=> 0,
		 					'link'=>'https://vibethemes.com/',
		 					'status'=>function_exists('vibe_calendar_plugin_load_translations')?2:(file_exists(plugin_dir_path(__FILE__).'../../vibe-calendar/loader.php')?1:0)
		 				),
		 				array(
		 					'plugin'=>'vibe-kb',
		 					'label' => __('Knowledge Base','vibebp'),
		 					'desc'=>__('Powerful Knowledge base , shared co-authored articles & AI answering.','vibebp'),
		 					'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M20 22H4C3.44772 22 3 21.5523 3 21V3C3 2.44772 3.44772 2 4 2H20C20.5523 2 21 2.44772 21 3V21C21 21.5523 20.5523 22 20 22ZM19 20V4H5V20H19ZM7 6H11V10H7V6ZM7 12H17V14H7V12ZM7 16H17V18H7V16ZM13 7H17V9H13V7Z"></path></svg>',
		 					'required'=> 0,
		 					'link'=>'https://vibethemes.com/',
		 					'status'=>function_exists('vibe_kb_plugin_load_translations')?2:(file_exists(plugin_dir_path(__FILE__).'../../vibe-helpdesk/loader.php')?1:0)
		 				),
		 				array(
		 					'plugin'=>'vibe-earnings',
		 					'label' => __('Commussions & Earnings','vibebp'),
		 					'desc'=>__('Affiliates, Commissions & payouts','vibebp'),
		 					'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M17.0047 16.0029H19.0047V4.00293H9.00468V6.00293H17.0047V16.0029ZM17.0047 18.0029V21.0021C17.0047 21.5548 16.5547 22.0029 15.9978 22.0029H4.01154C3.45548 22.0029 3.00488 21.5583 3.00488 21.0021L3.00748 7.0038C3.00759 6.45104 3.45752 6.00293 4.0143 6.00293H7.00468V3.00293C7.00468 2.45064 7.4524 2.00293 8.00468 2.00293H20.0047C20.557 2.00293 21.0047 2.45064 21.0047 3.00293V17.0029C21.0047 17.5552 20.557 18.0029 20.0047 18.0029H17.0047ZM7.00468 16.0029V18.0029H9.00468V19.0029H11.0047V18.0029H11.5047C12.8854 18.0029 14.0047 16.8836 14.0047 15.5029C14.0047 14.1222 12.8854 13.0029 11.5047 13.0029H8.50468C8.22854 13.0029 8.00468 12.7791 8.00468 12.5029C8.00468 12.2268 8.22854 12.0029 8.50468 12.0029H13.0047V10.0029H11.0047V9.00293H9.00468V10.0029H8.50468C7.12397 10.0029 6.00468 11.1222 6.00468 12.5029C6.00468 13.8836 7.12397 15.0029 8.50468 15.0029H11.5047C11.7808 15.0029 12.0047 15.2268 12.0047 15.5029C12.0047 15.7791 11.7808 16.0029 11.5047 16.0029H7.00468Z"></path></svg>',
		 					'required'=> 0,
		 					'link'=>'https://vibethemes.com/',
		 					'status'=>function_exists('vibe_drive_plugin_load_translations')?2:(file_exists(plugin_dir_path(__FILE__).'../../vibe-helpdesk/loader.php')?1:0)
		 				),
		 			),
		 		
			 		'steps'=>array(
			 			array(
			 				'key'=>'features',
			 				'label'=>_x('Features','installation step','vibebp'),
			 				'description'=>_x('Select features you want to enable in your site. This feature set comes from the plugins you have selected in the previous step of required and recommended plugin.
			 					<span>To restart the setup wizard, just click outside the setup wizard box and restart it.</span> ','installation step','vibebp'),
			 				'features'=>array(
			 					array(
			 						'type'=>'core',
			 						'key' => 'xprofile',
			 						'icon'=> '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M11 14.0619V20H13V14.0619C16.9463 14.554 20 17.9204 20 22H4C4 17.9204 7.05369 14.554 11 14.0619ZM12 13C8.685 13 6 10.315 6 7C6 3.685 8.685 1 12 1C15.315 1 18 3.685 18 7C18 10.315 15.315 13 12 13Z"></path></svg>',
			 						'label'=>__('Profile Fields','vibebp'),
			 						'required'=>1,
			 						'is_active'=>bp_is_active('xprofile')
			 					),
			 					array(
			 						'type'=>'core',
			 						'key' => 'activity',
			 						'label'=>__('Activity','vibebp'),
			 						'required'=>1,
			 						'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M16.5 3C19.5376 3 22 5.5 22 9C22 16 14.5 20 12 21.5C10.0224 20.3135 4.91625 17.5626 2.8685 13L7.56619 13L8.5 11.4437L11.5 16.4437L13.5662 13H17V11H12.4338L11.5 12.5563L8.5 7.55635L6.43381 11L2.21024 10.9999C2.07418 10.3626 2 9.69615 2 9C2 5.5 4.5 3 7.5 3C9.35997 3 11 4 12 5C13 4 14.64 3 16.5 3Z"></path></svg>',
			 						'is_active'=>bp_is_active('activity')
			 					),
			 					array(
			 						'type'=>'core',
			 						'key' => 'messages',
			 						'label'=>__('Messaging','vibebp'),
			 						'recommended'=>1,
			 						'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M3 3H21C21.5523 3 22 3.44772 22 4V20C22 20.5523 21.5523 21 21 21H3C2.44772 21 2 20.5523 2 20V4C2 3.44772 2.44772 3 3 3ZM20 7.23792L12.0718 14.338L4 7.21594V19H20V7.23792ZM4.51146 5L12.0619 11.662L19.501 5H4.51146Z"></path></svg>',
			 						'is_active'=>bp_is_active('messages')
			 					),
			 					array(
			 						'type'=>'core',
			 						'key' => 'notifications',
			 						'label'=>__('Notifications','vibebp'),
			 						'recommended'=>1,
			 						'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M20 17H22V19H2V17H4V10C4 5.58172 7.58172 2 12 2C16.4183 2 20 5.58172 20 10V17ZM18 17V10C18 6.68629 15.3137 4 12 4C8.68629 4 6 6.68629 6 10V17H18ZM9 21H15V23H9V21Z"></path></svg>',
			 						'is_active'=>bp_is_active('notifications')
			 					),
			 					array(
			 						'type'=>'core',
			 						'key' => 'friends',
			 						'label'=>__('Friends','vibebp'),
			 						'recommended'=>1,
			 						'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M2 22C2 17.5817 5.58172 14 10 14C14.4183 14 18 17.5817 18 22H16C16 18.6863 13.3137 16 10 16C6.68629 16 4 18.6863 4 22H2ZM10 13C6.685 13 4 10.315 4 7C4 3.685 6.685 1 10 1C13.315 1 16 3.685 16 7C16 10.315 13.315 13 10 13ZM10 11C12.21 11 14 9.21 14 7C14 4.79 12.21 3 10 3C7.79 3 6 4.79 6 7C6 9.21 7.79 11 10 11ZM18.2837 14.7028C21.0644 15.9561 23 18.752 23 22H21C21 19.564 19.5483 17.4671 17.4628 16.5271L18.2837 14.7028ZM17.5962 3.41321C19.5944 4.23703 21 6.20361 21 8.5C21 11.3702 18.8042 13.7252 16 13.9776V11.9646C17.6967 11.7222 19 10.264 19 8.5C19 7.11935 18.2016 5.92603 17.041 5.35635L17.5962 3.41321Z"></path></svg>',
			 						'is_active'=>bp_is_active('friends')
			 					),
			 					array(
			 						'type'=>'core',
			 						'key' => 'groups',
			 						'label'=>__('Groups','vibebp'),
			 						'recommended'=>1,
			 						'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M9.55 11.5C8.30736 11.5 7.3 10.4926 7.3 9.25C7.3 8.00736 8.30736 7 9.55 7C10.7926 7 11.8 8.00736 11.8 9.25C11.8 10.4926 10.7926 11.5 9.55 11.5ZM10 19.748V16.4C10 15.9116 10.1442 15.4627 10.4041 15.0624C10.1087 15.0213 9.80681 15 9.5 15C7.93201 15 6.49369 15.5552 5.37091 16.4797C6.44909 18.0721 8.08593 19.2553 10 19.748ZM4.45286 14.66C5.86432 13.6168 7.61013 13 9.5 13C10.5435 13 11.5431 13.188 12.4667 13.5321C13.3447 13.1888 14.3924 13 15.5 13C17.1597 13 18.6849 13.4239 19.706 14.1563C19.8976 13.4703 20 12.7471 20 12C20 7.58172 16.4183 4 12 4C7.58172 4 4 7.58172 4 12C4 12.9325 4.15956 13.8278 4.45286 14.66ZM18.8794 16.0859C18.4862 15.5526 17.1708 15 15.5 15C13.4939 15 12 15.7967 12 16.4V20C14.9255 20 17.4843 18.4296 18.8794 16.0859ZM12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22ZM15.5 12.5C14.3954 12.5 13.5 11.6046 13.5 10.5C13.5 9.39543 14.3954 8.5 15.5 8.5C16.6046 8.5 17.5 9.39543 17.5 10.5C17.5 11.6046 16.6046 12.5 15.5 12.5Z"></path></svg>',
			 						'is_active'=>bp_is_active('groups')
			 					),
			 					array(
			 						'type'=>'vibebp',
			 						'key' => 'followers',
			 						'label'=>__('Followers','vibebp'),
			 						'recommended'=>1,
			 						'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M14 14.252V16.3414C13.3744 16.1203 12.7013 16 12 16C8.68629 16 6 18.6863 6 22H4C4 17.5817 7.58172 14 12 14C12.6906 14 13.3608 14.0875 14 14.252ZM12 13C8.685 13 6 10.315 6 7C6 3.685 8.685 1 12 1C15.315 1 18 3.685 18 7C18 10.315 15.315 13 12 13ZM12 11C14.21 11 16 9.21 16 7C16 4.79 14.21 3 12 3C9.79 3 8 4.79 8 7C8 9.21 9.79 11 12 11ZM17.7929 19.9142L21.3284 16.3787L22.7426 17.7929L17.7929 22.7426L14.2574 19.2071L15.6716 17.7929L17.7929 19.9142Z"></path></svg>',
			 						'is_active'=>(vibebp_get_setting('followers','bp','general') == 'on')?true:false
			 					),
			 					array(
			 						'type'=>'vibebp',
			 						'key' => 'likes',
			 						'label'=>__('Likes','vibebp'),
			 						'recommended'=>1,
			 						'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36"><path d="M20.2426 4.75736C22.5053 7.02472 22.583 10.637 20.4786 12.993L11.9999 21.485L3.52138 12.993C1.41705 10.637 1.49571 7.01901 3.75736 4.75736C6.02157 2.49315 9.64519 2.41687 12.001 4.52853C14.35 2.42 17.98 2.49 20.2426 4.75736ZM5.17157 6.17157C3.68183 7.66131 3.60704 10.0473 4.97993 11.6232L11.9999 18.6543L19.0201 11.6232C20.3935 10.0467 20.319 7.66525 18.827 6.1701C17.3397 4.67979 14.9458 4.60806 13.3743 5.98376L9.17157 10.1869L7.75736 8.77264L10.582 5.946L10.5002 5.87701C8.92545 4.61197 6.62322 4.71993 5.17157 6.17157Z"></path></svg>',
			 						'is_active'=>(vibebp_get_setting('likes','bp','general') == 'on')?true:false
			 					),
			 				)
			 			),
						array(
							'key'=>'content',
			 				'label'=>_x('Content','installation step','vibebp'),
			 				'description'=>_x('Configure default content, layouts and menus for profiles','installation step','vibebp'),
			 				'layouts'=>array(
			 					array(
			 						'key'=>'menus',
			 						'type'=>'checkbox',
			 						'label'=>_x('Set default Profile & Loggedin menu [Recommended]','installation','vibebp'),
			 					),
			 					array(
			 						'key'=>'profile',
			 						'type'=>'checkbox',
			 						'label'=>_x('Install default profile layout & xprofile fields','installation','vibebp'),
			 					),
			 					array(
			 						'key'=>'group',
			 						'type'=>'checkbox',
			 						'label'=>_x('Install default Group Layout','installation','vibebp'),
			 					),
			 					array(
			 						'key'=>'members_directory',
			 						'type'=>'checkbox',
			 						'label'=>_x('Member Directory','installation','vibebp'),
			 					),
			 					array(
			 						'key'=>'groups_directory',
			 						'type'=>'checkbox',
			 						'label'=>_x('Group Directory','installation','vibebp'),
			 					),
			 					array(
			 						'key'=>'bp_single_age',
			 						'type'=>'checkbox',
			 						'label'=>_x('BuddyPress App page [Recommended]','installation','vibebp'),
			 					),
			 				)
						),
						array(
							'key'=>'access',
			 				'label'=>_x('Accessibility','installation step','vibebp'),
			 				'description'=>_x('Accessibility of various modules. If enabled, accessibility to world and search engines. If disabled then only accessible to you and members of your site.','installation step','vibebp'),
			 				'access'=>array(
			 					array(
			 						'key'=>'default_config',
			 						'type'=>'checkbox',
			 						'label'=>_x('Setup default Config','installation ( recommended)','vibebp'),
			 					),
			 					array(
			 						'key'=>'public_activity',
			 						'type'=>'checkbox',
			 						'label'=>_x('Disable Public Activities (recommended )','installation','vibebp'),
			 					),
			 				)
						),
			 		),
		 		),
                'translations'=>array(
                	
                    'configure_vibebp'=>__('Configure VibeBP, the Modern social network framework built on BuddyPress.', 'vibebp'),
                    'setup_wizard'=>__('Setup Wizard', 'vibebp'),
                    'required'=>__('Required','vibebp'),
                    'recommended'=>__('Recommended','vibebp'),
                    'installed'=>__('Plugin Active','vibebp'),
                    'activate_plugin'=>__('Activate','vibebp'),
                    'install_plugin'=>__('Learn more','vibebp'),
                    'begin_setup'=>__('Begin Setup','vibebp'),
                    'next_step'=>__('Next Step','vibebp'),

                )
            ))
	 	);
		?>
		<div id="vibebp_setup_wizard"></div>
		<?php
	}

	function tours_importer(){
		global $pagenow;
		if($pagenow != 'edit.php' || (!empty($_GET['post_type']) && esc_attr($_GET['post_type']) != 'tour')){
			return;
		}
		global $wpdb;
		$posts = $wpdb->get_results("SELECT post_name FROM {$wpdb->posts} WHERE post_type = 'tour'");
		if(!empty($posts)){

			$all_tours = apply_filters('vibebp_all_tours',[]);
			$saved_tours = wp_list_pluck('post_name',$posts);
			$to_install_tours = array_diff($all_tours, $saved_tours);
			do_action('VibeBP_Install_Tours',$to_install_tours);
		}else{


			if ( file_exists( plugin_dir_path(__FILE__) . '../assets/js/tours.json' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				WP_Filesystem();
				global $wp_filesystem;
				$filedata = $wp_filesystem->get_contents( plugin_dir_path(__FILE__) . '../assets/js/tours.json' );
			}

			if(!empty($filedata)){
				$filedata = preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "\n", $filedata)); 
				
				$tours = json_decode($filedata,true);
				if(!empty($tours) && is_array($tours)){
					foreach($tours['tour'] as $tour){
						$tour = (Array)$tour;
						
						$tour['post_status'] = 'publish';
						$tour['post_type'] = 'tour';
						wp_insert_post($tour);

					}
				}
			}
			do_action('VibeBP_Install_All_Tours');
		}
	}
}

VibeBP_Menu::init();
