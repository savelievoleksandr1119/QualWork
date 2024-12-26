<?php
/**
 * Installation related functions and actions.
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	Setup Install
 * @version     1.8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


include_once MICRONET_PATH.'/setup/welcome.php';
include_once MICRONET_PATH.'/setup/installer/envato_setup.php';

function vibe_get_site_style(){
	$site_style = get_option('micronet_site_style');
	if(!empty($site_style)){
		return $site_style;
	}
	return 'elementor_demo';
}

if ( ! class_exists( 'Micronet_Install' ) ) :

/**
 * Micronet_Install Class
 */
class Micronet_Install {

	public $version = MICRONET_DB_VERSION;
	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action('after_switch_theme', array( $this, 'install' ) , 10 , 2);
		// Hooks
		add_action( 'admin_init', array( $this, 'check_version' ), 5 );
		
		add_action('micronet_after_sample_data_import',array($this,'micronet_setup_settings'),20,1);
		add_action('micronet_after_sample_data_import',array($this,'micronet_flush_permalinks'),100);

		add_filter( 'theme_action_links_' . THEME_SHORT_NAME, array( $this, 'theme_action_links' ) );
		add_filter( 'theme_row_meta', array( $this, 'theme_row_meta' ), 10, 3 );

		remove_action( 'bp_admin_init', 'bp_do_activation_redirect', 1    );
	}

	/**
	 * check_version function.
	 *
	 * @access public
	 * @return void
	 */
	public function check_version() {
		$micronet_version=get_option( 'micronet_version' );
		if (empty($micronet_version) || $micronet_version != $this->version ) {
			$this->install();
			do_action( 'micronet_updated' );
		}
	}


	/**
	 * Install Micronet
	 */
	public function install() {
		

		$current_version    = get_option( 'micronet_version', null );

		
		if(!isset($current_version)){
			update_option( 'micronet_version', $this->version );
			set_transient( '_micronet_activation_redirect', 1, HOUR_IN_SECONDS );
		}
		// Update version
		if($current_version != $this->version){
			update_option( 'micronet_version', $this->version );
			flush_rewrite_rules();
			set_transient( '_micronet_activation_redirect', 2, HOUR_IN_SECONDS );
		}
	}



	/**
	 * Show action links on the plugin screen.
	 *
	 * @access	public
	 * @param	mixed $links Plugin Action links
	 * @return	array
	 */
	public function theme_action_links( $links ) {
		$action_links = array(
			'settings'	=>	'<a href="' . admin_url( 'admin.php?page=micronet_options' ) . '" title="' . esc_attr( __( 'View Micronet Options panel', 'micronet' ) ) . '">' . __( 'Options panel', 'micronet' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @access	public
	 * @param	mixed $links Plugin Row Meta
	 * @param	mixed $file  Plugin Base file
	 * @return	array
	 */
	public function theme_row_meta( $links, $file,$theme ) {
		if ( $theme ==  THEME_SHORT_NAME) {
			$row_meta = array(
				'docs'		=>	'<a href="' . esc_url( apply_filters( 'micronet_docs_url', 'https://vibethemes.com/micronet' ) ) . '" title="' . esc_attr( __( 'View Micronet Documentation', 'micronet' ) ) . '">' . __( 'Docs', 'micronet' ) . '</a>',
				'support'	=>	'<a href="' . esc_url( apply_filters( 'micronet_support_url', 'http://vibethemes.ticksy.com/' ) ) . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'micronet' ) ) . '">' . __( 'Support Forum', 'micronet' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	

	function micronet_setup_settings($file){
		global $wpdb;
		//flush_rewrite_rules();
		//Set important settings
		update_option('permalink_structure','/%postname%/');
		update_option('membership_active','yes');
		update_option('require_name_email','');
		update_option('comment_moderation','');
		update_option('comment_whitelist','');
		update_option('posts_per_page',6);
		update_option('comments_per_page',5);
		update_option('users_can_register',1);
		update_option('default_role','student');
		$bp_active_components = apply_filters('micronet_setup_bp_components',array(
			'xprofile' => 1,
			'settings' => 1,
			'friends' => 1,
			'messages' => 1,
			'activity' => 1,
			'notifications' => 1,
			'members' => 1 
			));
		update_option('bp-active-components',$bp_active_components);
		

		
		$options_panel = apply_filters('micronet_setup_options_panel',$options_panel);
		update_option(THEME_SHORT_NAME,$options_panel);
		foreach($bp_pages as $key=>$page){
			$page_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_name = %s LIMIT 1;", "{$page}" ) );	
			if(isset($page_id) && is_numeric($page_id)){
				$bp_pages[$key] = $page_id;
			}else{
				unset($bp_pages[$key]);
			}
		}
		update_option('bp-pages',$bp_pages);
		
	}

	
	function micronet_flush_permalinks(){
		update_option('medium_size_w',460);
	}
}

endif;

new Micronet_Install();
