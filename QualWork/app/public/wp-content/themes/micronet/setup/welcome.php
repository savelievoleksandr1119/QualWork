<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Micronet_Admin_Welcome {

	private $plugin;
	public $major_version = MICRONET_VERSION;
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Bail if user cannot moderate
		if ( ! current_user_can( 'manage_options' ) )
			return;
		add_action( 'admin_menu', array( $this, 'admin_menus') );
	}

	/**
	 * Add admin menus/screens
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menus() {	

		
		$page = add_dashboard_page( __( 'Welcome to Micronet', 'micronet' ), __( 'About Micronet', 'micronet' ), 'manage_options', 'micronet-about', array( $this, 'about_screen' ) );
		add_action( 'admin_print_styles-'. $page, array( $this, 'admin_css' ) );
			
	}

	/**
	 * admin_css function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_css() {
		wp_enqueue_style( 'micronet-intro-font',"https://fonts.googleapis.com/css2?family=Mulish:wght@200;300;400;700;900&display=swap" );
		wp_enqueue_style( 'vibe-activation', MICRONET_URL.'/setup/installer/css/envato-setup.css',array(),MICRONET_VERSION);
	}

	/**
	 * Add styles just for this page, and remove dashboard page links.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_head() {
		if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'micronet-about'){
			remove_submenu_page( 'index.php', 'micronet-about' );		
		}

		?>
		<style type="text/css">
			/*<![CDATA[*/
			.micronet-wrap .micronet-badge {
				<?php echo is_rtl() ? 'left' : 'right'; ?>: 0;
			}
			.micronet-wrap .feature-rest div {
				float:<?php echo is_rtl() ? 'right':'left' ; ?>;
			}
			.micronet-wrap .feature-rest div.last-feature {
				padding-<?php echo is_rtl() ? 'right' : 'left'; ?>: 50px !important;
				padding-<?php echo is_rtl() ? 'left' : 'right'; ?>: 0;
			}
			.three-col > div{
				float:<?php echo is_rtl() ? 'right':'left' ; ?>;
			}
			/*]]>*/
		</style>
		<?php
	}

	/**
	 * Into text/links shown on all about pages.
	 *
	 * @access private
	 * @return void
	 */
	private function intro() {

		// Flush after upgrades
		if ( ! empty( $_GET['micronet-updated'] ) || ! empty( $_GET['micronet-installed'] ) )
			flush_rewrite_rules();
		?>
		<h1><?php printf( __( 'Micronet %s', 'micronet' ), $this->major_version ); ?></h1>

		<?php 

		
		$this->purchase_code = get_option('micronet_purchase_code');

		if(empty($this->purchase_code)){
			?>
				<a href="<?php echo admin_url('/themes.php?page=micronet-setup&setup=updaed&referrer=about&step=updates'); ?>" class="important_notice"> <?php wp_nonce_field(); ?>
					Plugin auto-updates not configured. Click here to Setup Plugin auto-updates.
				</a>

			<?php
		}
		?>
		

		<div class="about-text micronet-about-text">
			Thank you for installing <strong>Micronet Theme</strong>. This is the about page for Micronet.
		</div>
		<a href="https://micronet.work/article" target="_blank">Latest Documentation ›</a>
		<br><br>
		<?php
	}

	
	
	/**
	 * Output the about screen.
	 */
	public function about_screen() {
		?>
		<div class="setup_wizard_wrapper_wrapper">
		<div class="setup_wizard_wrapper">
			<div class="onboarding introduction">
				<div class="onboarding_header">
					<span><img id="micronet_logo" class="site-logo" src="<?php echo VIBE_URL.'/images/logo.png';?>" alt="micronet" width="180">
						<span>Work Management System</span>
					</span>
					
				</div>
				<div class="onboarding_introduction">
					<h2>Introduction.</h2>
					<span><a>2 years</a>. Inbuilt App platform. Commissions.</span>
				</div>
				<a href="https://micronetdocs.wpappointify.com/about/update-log" target="_blank">View full update log ›</a>
			</div>
			<div class="setup_wizard_main">
				<div class="setup_wizard_main_header">
					<span></span>
					<span>Having Troubles ? <a href="https://micronet.work/app/">Get Help</a></span>
				</div>
				<div class="envato-setup-content">				
					<?php $this->intro(); ?>

				<div class="micronet_about_tabs">
					
					<input type="radio" id="micronet_whats_new" name="micronet_about_active_tab" checked />
					<label for="micronet_whats_new">Whats New</label>
					

					<input type="radio" id="micronet_support" name="micronet_about_active_tab" />
					<label for="micronet_support">Support</label>
					
					<input type="radio" id="micronet_system_status" name="micronet_about_active_tab"  />
					<label for="micronet_system_status">System Status</label>
					
					<input type="radio" id="micronet_changelog" name="micronet_about_active_tab" />
					<label for="micronet_changelog">Changelog</label>
					<hr>
					<div class="micronet_about_tab micronet_whats_new"> 
						<?php 
						$this->welcome_screen();
						?>
					</div>
					<div class="micronet_about_tab micronet_support"> 
						<?php 
						$this->support_screen();
						?>
					</div>
					<div class="micronet_about_tab micronet_system_status"> 
						<?php 
						$this->system_screen();
						?>
					</div>
					<div class="micronet_about_tab micronet_changelog"> 
						<?php 
						$this->changelog_screen();
						?>
					</div>
				</div>
				<p class="envato-setup-actions step">
					<div class="return-to-dashboard">
					<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'micronet_options' ), 'admin.php' ) ) ); ?>"><?php _e( 'Go to Micronet Options panel', 'micronet' ); ?></a>

					</div>
				</p>
				</div>
				

			</div>
			
		</div>	
		</div>
		<?php
	}

	public function welcome_screen(){
		?>
		<div class="welcome_slider">
  
		  	<div class="slides">
		  		<div id="slide-1">
		    		<img src="<?php echo VIBE_URL.'/setup/installer/images/demo.png'; ?>" alt="base demo" />
			    </div>
			    
			    <div id="slide-2">
		    		<img src="<?php echo VIBE_URL.'/setup/installer/images/2.png'; ?>" />
			    </div>
			    
		  	</div>
		  	<div class="slider_dots">
		  		<a href="#slide-1"></a>
		  		<a href="#slide-2"></a>
			</div>
		</div>
		<?php
	}

	public function support_screen(){
		?>
		<div class="micronet_support_wrapper">
			<a href="https://facebook.com/vibethemes">Get Live Support. Just drop a message.</a>
			<a href="https://micronet.work/app/">Create a Ticket</a>
			
		</div>
		<?php
	}

	public function system_screen(){
		?>
		<table class="micronet_status_table widefat" cellspacing="0" id="status">
				<thead>
					<tr>
						<th colspan="2"><h4><?php _e( 'Environment', 'micronet' ); ?></h4></th>
					</tr>
				</thead>

				<tbody>
					<tr>
						<td><?php _e( 'Home URL', 'micronet' ); ?>:</td>
						<td><?php echo home_url(); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'Site URL', 'micronet' ); ?>:</td>
						<td><?php echo site_url(); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Version', 'micronet' ); ?>:</td>
						<td><?php bloginfo('version'); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Multisite Enabled', 'micronet' ); ?>:</td>
						<td><?php if ( is_multisite() ) echo __( 'Yes', 'micronet' ); else echo __( 'No', 'micronet' ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'PHP Version', 'micronet' ); ?>:</td>
						<td><?php if ( function_exists( 'phpversion' ) ) echo esc_html( phpversion() ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'MySQL Version', 'micronet' ); ?>:</td>
						<td>
							<?php
							/** @global wpdb $wpdb */
							global $wpdb;
							echo esc_attr($wpdb->db_version());
							?>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'WP Active Plugins', 'micronet' ); ?>:</td>
						<td><?php echo count( (array) get_option( 'active_plugins' ) ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Memory Limit', 'micronet' ); ?>:</td>
						<td><?php
							$memory = $this->micronet_let_to_num( WP_MEMORY_LIMIT );
							if ( $memory < 134217728 ) {
								echo '<mark class="error">' . sprintf( __( '%s - We recommend setting memory to at least 128MB. See: <a href="%s">Increasing memory allocated to PHP</a>', 'micronet' ), size_format( $memory ), 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP' ) . '</mark>';
							} else {
								echo '<mark class="yes">' . size_format( $memory ) . '</mark>';
							}
						?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Debug Mode', 'micronet' ); ?>:</td>
						<td><?php if ( defined('WP_DEBUG') && WP_DEBUG ) echo '<mark class="no">' . __( 'Yes', 'micronet' ) . '</mark>'; else echo '<mark class="yes">' . __( 'No', 'micronet' ) . '</mark>'; ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Language', 'micronet' ); ?>:</td>
						<td><?php echo get_locale(); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Max Upload Size', 'micronet' ); ?>:</td>
						<td><?php echo size_format( wp_max_upload_size() ); ?></td>
					</tr>
					<?php if ( function_exists( 'ini_get' ) ) : ?>
						<tr>
							<td><?php _e('PHP Post Max Size', 'micronet' ); ?>:</td>
							<td><?php echo size_format($this->micronet_let_to_num( ini_get('post_max_size') ) ); ?></td>
						</tr>
						<tr>
							<td><?php _e('PHP Time Limit', 'micronet' ); ?>:</td>
							<td><?php echo ini_get('max_execution_time'); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'PHP Max Input Vars', 'micronet' ); ?>:</td>
							<td><?php echo ini_get('max_input_vars'); ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<td><?php _e( 'Default Timezone', 'micronet' ); ?>:</td>
						<td><?php
							$default_timezone = date_default_timezone_get();
							if ( 'UTC' !== $default_timezone ) {
								echo '<mark class="error">' . sprintf( __( 'Default timezone is %s - it should be UTC', 'micronet' ), $default_timezone ) . '</mark>';
							} else {
								echo '<mark class="yes">' . sprintf( __( 'Default timezone is %s', 'micronet' ), $default_timezone ) . '</mark>';
							} ?>
						</td>
					</tr>
				</tbody>


			</table>
		<?php
	}

	/**
	 * Output the changelog screen
	 */
	public function changelog_screen() {
		?>
		<div class="wrap micronet-wrap about-wrap">

			
			<div class="changelog-description">
			<p><?php printf( __( 'Full Changelog of Micronet Theme', 'micronet' ), 'micronet' ); ?></p>

			<?php
				if(function_exists('WP_Filesystem')){
					WP_Filesystem();
					global $wp_filesystem;
					$file = VIBE_URL.'/changelog.txt';
					$content = $wp_filesystem->get_contents( $file );

					print_r($content);	
				}
				
			?>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Sends user to the welcome page on first activation
	 */
	public function welcome() {
		//Bail if no activation redirect transient is set
	    if ( ! get_transient( '_micronet_activation_redirect' ) ) {
			return;
	    }

		
		// Bail if activating from network, or bulk, or within an iFrame
		if ( is_network_admin() || defined( 'IFRAME_REQUEST' ) ) {
			return;
		}

		if(!$this->check_installed()){
			if(empty($_GET['page']) || $_GET['page'] != 'micronet-setup'){
				wp_redirect( admin_url( 'themes.php?page=micronet-setup' ) );
			}
		}else{
			if(empty($_GET['page']) || $_GET['page'] != 'micronet-about'){
				wp_redirect( admin_url( 'index].php?page=micronet-about' ) );
			}
		}

		wp_redirect( admin_url( 'index.php?page=micronet-about' ) );
		exit;
	}
	function micronet_let_to_num( $size ) {
		$l   = substr( $size, -1 );
		$ret = substr( $size, 0, -1 );
		switch ( strtoupper( $l ) ) {
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'M':
				$ret *= 1024;
			case 'K':
				$ret *= 1024;
		}
		return $ret;
	}
	function check_installed(){


		// $check = get_transient( '_micronet_activation_redirect' );
		// if(!empty($check) && $check == 1){
		// 	delete_transient( '_micronet_activation_redirect' );
		// 	return false;
		// }
		// if(!empty($check) && $check == 2){
		// 	delete_transient( '_micronet_activation_redirect' );
		// 	return true;
		// }

		return true;
	}
}

add_action('init','micronet_welcome_user');
function micronet_welcome_user(){
	new Micronet_Admin_Welcome();	
}
