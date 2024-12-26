<?php
/**
 * Filters
 *
 * @class       VibeBP_Init
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VibeBP_Actions{


	public static $instance;
	public $templates;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Actions();
        return self::$instance;
    }

	private function __construct(){

		add_filter('bp_signups_create_user',function($x){
			return true;
		},12);
		add_action( 'bp_setup_nav', array($this,'add_bp_nav'), 100 );
		add_action('wp_ajax_vibebp-sw',array($this,'install_sw'));
		add_action('wp_ajax_nopriv_vibebp-sw',array($this,'install_sw'));
		add_action('wp_footer',array($this,'synchronise_wp_logins'),9999);
		add_action('wp_footer',array($this,'add_login_portal'));
		add_action('wp_footer',array($this,'add_notifications_portal'));
		add_action('wp_head',array($this,'pre_cache'),999999);
		//add_action('wp_head',array($this,'apply_sw_version'),1);
		add_action('wp_ajax_generate_token',array($this,'generate_token'));
		add_action('template_redirect',array($this,'accessibility_settings'),1);
		add_action('wp_head',array($this,'login_detect_at_woocommerce_checkout'),11);
		
		add_action('wp_head',array($this,'vibebp_wp_login'),999999);
		add_action('wp_head',array($this,'login_for_pmpro_pages'),9999);

        add_action('bp_core_activated_user',array($this,'user_activated'),999,1);
        add_action('wp_head',array($this,'user_activated_login'),1);


        //allowing user to view his profile of other users
        
        //add_action('wp_head',array($this,'bp_single_page'));
        
        //App Page template
        add_filter('theme_page_templates', array( $this, 'add_new_template' ));
        add_filter('wp_insert_post_data', array( $this, 'register_app_template') );
        add_filter('template_include', array( $this, 'view_app_template') );
		$this->templates = array('templates/app.php'=>'VibeBP App');


		add_action('bp_core_activated_user',array($this,'activate_user'),12,3);

		add_action('bp_activity_register_activity_actions',array($this,'register_actions'));
		add_action('bp_signup_validate', array($this,'google_captcha_validate'),1);
		add_action('bp_before_registration_submit_buttons', array($this,'show_google_captcha'),1,1);
    	add_action('vibebp_member_follow',array($this,'member_follow'),10,2);
    	add_action('vibebp_member_unfollow',array($this,'member_unfollow'),10,2);

    	add_action('vibebp_invite_code_registration',array($this,'record_invite_code_activity'),10,4);

    	

		add_action('wp_head',array($this,'check_group_type_page'));

		add_action('vibebp_get_group_data',array($this,'group_custom_field_display'),10,2);


	}

	function group_custom_field_display($type,$group_id){
		
		if(!empty(vibebp_get_setting('group_custom_fields','bp','groups'))){

			$fields = vibebp_get_setting('group_custom_fields','bp','groups');
			$k = array_search($type,$fields['key']);
			$value = groups_get_groupmeta($group_id,$fields['key'][$k],true);
			
			if(!empty($fields) && $k !== false){

				if(!empty($fields['options'][$k])){
					$option= groups_get_groupmeta($group_id,$type,true);
					
					$options = explode('|', $fields['options'][$k]);
					

					foreach($options as $op){
						
						$op = explode(';',$op);
						
						if(!empty($op[1])){
							if( !is_array($value) && $op[0] == $value){
								echo '<span>'.$op[1].'</span>';	
							}
							if( is_array($value) && in_array($op[0],$value)){
								echo '<span>'.$op[1].'</span>';	
							}
						}
					}
					
					
				}else{
					echo $value;
				}
				
			}
		}
	}

	function check_group_type_page(){
		global $bp;
	    if(!empty($bp) && function_exists('bp_is_groups_directory') && bp_is_groups_directory() && !empty($bp->groups) && !empty($bp->groups->current_directory_type)){
	       
	        ?>
	        <script>
	            var page_vibebp_group_type = '<?php echo $bp->groups->current_directory_type;?>';
	        </script>
	        <?php
	    }
	}
	

	function register_actions(){
		global $bp; 
        $bp_course_action_desc=apply_filters('bp_course_register_actions',array(
            'wallet' => __( 'Wallet.', 'vibebp' ),
            
            
            ));
        foreach($bp_course_action_desc as $key => $value){
            bp_activity_set_action($bp->activity->id,$key,$value);  
        }
	}

	function activate_user($user_id,$key,$user){
		$membertype = bp_get_member_type($user_id,false);
		if(!empty($membertype)){
			$membertype = $membertype[0];
		}
		if(!empty($membertype)){
			$type = bp_get_member_type_object($membertype);
	        if(!empty($type) && !empty($type->db_id)){
	            $role = get_term_meta($type->db_id,'vibebp_member_type_user_role',true);
	            if(!empty($role)){
	         
	                $user = new WP_User( $user_id );
	                $user->set_role( $role );
	            }
	        }
		}
		
	}
	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}

	public function register_app_template( $atts ) {

		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		} 
		wp_cache_delete( $cache_key , 'themes');
		$templates = array_merge( $templates,  $this->templates);
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );
		return $atts;
	}

	public function view_app_template( $template ) {
		global $post;
		if ( ! $post ) {
			return $template;
		}
		if ( !isset( $this->templates[get_post_meta( 
			$post->ID, '_wp_page_template', true 
		)] ) ) {
			return $template;
		} 
		$file = plugin_dir_path(__FILE__). get_post_meta( 
			$post->ID, '_wp_page_template', true
		);

		$file = str_replace('includes/','',$file);
		if ( file_exists( $file ) ) {
			return $file;
		} 

		return $template;
	}

	function bp_single_page(){
		global $bp;
		
		if(!empty(vibebp_get_setting('bp_single_page')) && bp_current_component() == 'profile'){
			?>
			<script>
				if(typeof localforage == 'object' && window.location.href.indexOf('#')){
					localforage.getItem('bp_login_token').then(function(token){
						if(token){
							let uri = window.location.href.split('#')[1];
							window.location.href='<?php echo get_permalink(vibebp_get_setting('bp_single_page')); ?>#'+uri;}
					});
				}
			</script>
			<?php
		}
	}

	function vibebp_wp_login(){

		if(!is_user_logged_in() && vibebp_get_setting('sync_wp_login')){
			?>
			<script>
				document.addEventListener('DOMContentLoaded',function(){
					if(typeof localforage == 'object'){
						localforage.getItem('bp_login_token').then(function(token){
							if(token){
							    var xhr = new XMLHttpRequest();
								xhr.open('POST', ajaxurl);
								xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
								xhr.onload = function() {
								    if (xhr.status === 200) {
								    	let check = JSON.parse(xhr.responseText);
								    	if(check.status){
								    		sessionStorage.setItem('bp_user',check.data.data.user);
								    		window.location.reload(true);
								    	}
								    }
								};

								xhr.send(encodeURI('action=vibebp_wp_login&client_id=<?php echo vibebp_get_setting('client_id'); ?>&security=<?php echo wp_create_nonce('security'); ?>&token=' + token));
							}
						});
					}
				});	
			</script>
			<?php
		}
	}
	function login_for_pmpro_pages(){
		global $post;
		if(function_exists('pmpro_hasMembershipLevel')){
			global $pmpro_pages;
			if(!empty($post) && in_array($post->ID, $pmpro_pages) && !is_user_logged_in()){
			?>
			<script>
				
				document.addEventListener('DOMContentLoaded',function(){
					
					if(typeof localforage == 'object'){
						localforage.getItem('bp_login_token').then(function(token){
							if(token){
							    var xhr = new XMLHttpRequest();
								xhr.open('POST', ajaxurl);
								xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
								xhr.onload = function() {
								    if (xhr.status === 200) {
								    	let check = JSON.parse(xhr.responseText);
								    	if(check.status){
								    		window.location.reload(true);
								    	}
								    }
								};

								xhr.send(encodeURI('action=vibebp_wc_login&client_id=<?php echo vibebp_get_setting('client_id'); ?>&security=<?php echo wp_create_nonce('security'); ?>&token=' + token));
							}
						});
					}
				});	
			</script>
			<?php
			}
		}
	}

	function accessibility_settings(){
		if(function_exists('bp_is_user') && bp_is_user()){
			$disable = vibebp_get_setting('public_profile','bp','general');
			$client_id = vibebp_get_setting('client_id');
			if($disable == 'on'){

				if(!empty($_GET) && !empty($_GET['client_id']) && sanitize_text_field($_GET['client_id']) == $client_id){
					//continue
				}else{
					global $wp_query;
				  $wp_query->set_404();
				  status_header( 404 );
				  get_template_part( 404 ); exit();
				}
			}
		}
		
		if(function_exists('bp_current_component') && bp_current_component() == 'members'){
			$disable = vibebp_get_setting('public_member_directory','bp','general');
			$client_id = vibebp_get_setting('client_id');
			if($disable == 'on'){

				if(!empty($_GET) && !empty($_GET['client_id']) && sanitize_text_field($_GET['client_id'] )== $client_id){
					//continue
				}else{
					global $wp_query;
				  $wp_query->set_404();
				  status_header( 404 );
				  get_template_part( 404 ); exit();
				}
			}
		}


		if(function_exists('bp_current_component') && bp_current_component() == 'groups'){
			$disable = vibebp_get_setting('public_group_directory','bp','general');
			$client_id = vibebp_get_setting('client_id');
			if($disable == 'on'){

				if(!empty($_GET) && !empty($_GET['client_id']) && sanitize_text_field($_GET['client_id']) == $client_id){
					//continue
				}else{
					global $wp_query;
				  $wp_query->set_404();
				  status_header( 404 );
				  get_template_part( 404 ); exit();
				}
			}
		}
		
		if(function_exists('bp_current_component') && bp_current_component() == 'activity'){
			$disable = vibebp_get_setting('public_activity','bp','general');
			$client_id = vibebp_get_setting('client_id');
			if($disable == 'on'){

				if(!empty($_GET) && !empty($_GET['client_id']) && sanitize_text_field($_GET['client_id']) == $client_id){
					//continue
				}else{
					global $wp_query;
				  $wp_query->set_404();
				  status_header( 404 );
				  get_template_part( 404 ); exit();
				}
			}
		}


	}

	function add_bp_nav(){
		global $bp;

		if(!function_exists('is_wplms_4_0') || (function_exists('is_wplms_4_0') && is_wplms_4_0() )){
			
			$link = trailingslashit( bp_loggedin_user_domain());
			bp_core_new_nav_item( array( 
		        'name' => __('Dashboard','vibebp'),
		        'slug' => 'dashboard', 
		        'item_css_id' => 'dashboard',
		        'screen_function' => array($this,'show_screen'),
		        'default_subnav_slug' => 'dashboard', 
		        'position' => 1,
		        'show_for_displayed_user'=>false,
		        'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
		    ) );

		    if(vibebp_get_setting('wallet','general','general')){

			    bp_core_new_nav_item( array( 
			        'name' => __('Wallet','vibebp'),
			        'slug' => 'wallet', 
			        'item_css_id' => 'wallet',
			        'screen_function' => array($this,'show_screen'),
			        'default_subnav_slug' => 'balance', 
			        'position' => 105,
			        'show_for_displayed_user'=>false,
			        'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
			    ) );
			    bp_core_new_subnav_item( array(
					'name' => __('Balance','vibebp'),
					'slug' => 'balance', 
					'parent_slug' => 'wallet',
		        	'parent_url' => $link.'wallet/balance/',
					'screen_function' => array($this,'show_screen'),
					'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
				) );
				bp_core_new_subnav_item( array(
					'name' => __('Transactions','vibebp'),
					'slug' => 'transactions', 
					'parent_slug' => 'wallet',
		        	'parent_url' => $link.'wallet/transactions',
					'screen_function' => array($this,'show_screen'),
					'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
				) );
			}
			if(function_exists('wc_get_account_menu_items')){
				$slug='shop';
				bp_core_new_nav_item( array( 
			        'name' => __('Shop','vibebp'),
			        'slug' => $slug, 
			        'item_css_id' => 'shop',
			        'screen_function' => array($this,'show_screen'),
			        'default_subnav_slug' => 'home', 
			        'position' => 55,
			        'show_for_displayed_user'=>false,
			        'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
			    ) );

				if(!empty($_GET['reload_nav'])){
					foreach ( wc_get_account_menu_items() as $endpoint => $label ){
						if(in_Array($endpoint,apply_filters('vibebp_supported_endpoints',array('orders','edit-address','downloads')))){
					
							bp_core_new_subnav_item( array(
								'name' 		  => $label,
								'slug' 		  => $endpoint,
								'parent_slug' => $slug,
					        	'parent_url' => $link.$slug.'/',
								'screen_function' => array($this,'show_screen'),
								'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
							) );
						}
						
					}
				}
			}


			if(vibebp_get_setting('bp_followers','bp','general')){
				
		    	$slug = 'followers';
				global $bp;
				//Add Appointments tab in profile menu
			    bp_core_new_nav_item( array( 
			        'name' => __('Followers','vibebp'),
			        'slug' => 'followers', 
			        'item_css_id' => 'followers',
			        'screen_function' => array($this,'show_screen'),
			        'default_subnav_slug' => 'home', 
			        'position' => 55,
			        'show_for_displayed_user'=>false,
			        'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
			    ) );
			    	    	

		    	bp_core_new_subnav_item( array(
					'name' 		  => __( 'Followers', 'vibebp' ),
					'slug' 		  => 'home',
					'parent_slug' => $slug,
		        	'parent_url' => $link.$slug.'/',
					'screen_function' => array($this,'show_screen'),
					'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
				) );
				bp_core_new_subnav_item( array(
					'name' 		  => __( 'Following', 'vibebp' ),
					'slug' 		  => _x('following','following slug in profile ','vibebp'),
					'parent_slug' => $slug,
		        	'parent_url' => $link.$slug.'/',
					'screen_function' => array($this,'show_screen'),
					'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
				) );
			}	
		
			if(function_exists('pmpro_hasMembershipLevel')){
				$slug = 'memberships';
				bp_core_new_nav_item( array( 
		            'name' => __('My Memberships', 'vibe' ), 
		            'slug' => $slug , 
		            'position' => 99,
		            'item_css_id'     => $slug,
		            'screen_function' => array($this,'show_screen'), 
		            'default_subnav_slug' => '',
		            'show_for_displayed_user' => bp_is_my_profile(),
		            'default_subnav_slug'=> $slug
		      	) );


				$link = trailingslashit( bp_loggedin_user_domain() . $slug );

				bp_core_new_subnav_item( array(
					'name'            => __('My Memberships', 'vibe' ), 
					'slug'            => $slug,
					'parent_slug'     => $slug,
					'parent_url'      => $link,
					'position'        => 10,
					'screen_function' => array( $this, 'show_screen' ),
					'user_has_access' => bp_is_my_profile(),
					'no_access_url'   => home_url(),
				) );

				bp_core_new_subnav_item( array(
					'name'            => __('Past invoices', 'vibe' ), 
					'slug'            => 'invoices',
					'parent_slug'     => $slug,
					'parent_url'      => $link,
					'position'        => 10,
					'screen_function' => array( $this, 'show_screen' ),
					'user_has_access' => bp_is_my_profile(),
					'no_access_url'   => home_url(),
				) );
				bp_core_new_subnav_item( array(
					'name'            => __('My Account', 'vibe' ), 
					'slug'            => 'account',
					'parent_slug'     => $slug,
					'parent_url'      => $link,
					'position'        => 10,
					'screen_function' => array( $this, 'show_screen' ),
					'user_has_access' => bp_is_my_profile(),
					'no_access_url'   => home_url(),
				) );
			}


            $slug = 'members_detail';
            bp_core_new_nav_item( array( 
                'name' => __('All Members','vibebp'),
    	        'slug' => $slug, 
    	        'item_css_id' => 'members_detail',
    	        'screen_function' => array($this,'show_screen'),
    	        'default_subnav_slug' => 'home', 
    	        'position' => 58,
            	'show_for_displayed_user'=>false,
    	        'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
    	    ) );
        }
	}


    function getvw(){
    	
    	//read file

    	if(file_exists(plugin_dir_path(__FILE__).'../assets/js/service-worker.js') && !empty(vibebp_get_setting('offline_page','service_worker','general'))){
            
            $contents = file_get_contents(plugin_dir_path(__FILE__).'../assets/js/service-worker.js');
            //replace constants
            $version = vibebp_get_setting('version','service_worker','general') ;
            $cache_first = vibebp_get_setting('cache_first','service_worker','general') ;
            if(!empty($cache_first)){
            	$contents = str_replace('[CACHE_FIRST]','true',$contents);            	
            }else{
            	$contents = str_replace('[CACHE_FIRST]','false',$contents);            
            }
            
            $contents = str_replace('[SW_VERSION]','"'.$version.'"',$contents);            
			$contents = str_replace('[PLUGIN_URL]',plugins_url('../',__FILE__),$contents);
			if(!vibebp_get_setting('firebase','general','firebase')){
				$contents = preg_replace('/\[FIREBASE\](.*)\[\/FIREBASE\]/is', ' ', $contents);
			}else{
				$contents = str_replace('[FIREBASE]','',$contents);
				$contents = str_replace('[/FIREBASE]','',$contents);
				$firebase_config = vibebp_get_setting('firebase_config','general','firebase');
				$contents = str_replace('[FIREBASE_OBJECT]',json_encode($firebase_config),$contents);
				
			}

			$get = wp_remote_get(get_permalink(vibebp_get_setting('offline_page','service_worker','general')).'?pre_cache=1');

			$scripts=  get_option('vibe_sw_scripts');
			$styles=  get_option('vibe_sw_styles');

			array_splice($scripts,count($scripts),0,$styles);

			$scripts[]=plugins_url('../assets/js/localforage.min.js',__FILE__);
			
			foreach($scripts as $k=>$script){
				$scripts[$k] = $script.'?v='.$version;
			}

			if(vibebp_get_setting('service_workers')){
				$links = vibebp_get_setting('pre-cached','service_worker','general');	
				if(!empty($links)){
					foreach($links as $key=>$link){
						$scripts[]=$link;
					}
				}
			}

			$scripts[]=plugins_url('../assets/fonts/vicon.woff',__FILE__);
			$scripts[]=plugins_url('../assets/fonts/vicon.svg',__FILE__);
			$scripts[]=plugins_url('../assets/fonts/vicon.eot',__FILE__);
			$scripts[]=plugins_url('../assets/fonts/vicon.ttf',__FILE__);
			$scripts[]=plugins_url('../assets/vicons.css',__FILE__);
			
			
			$contents = str_replace('[STATIC_ASSETS]',json_encode($scripts),$contents);

			$image = vibebp_get_setting('default_image','service_worker','general');
			if(is_numeric($image)){
				$image = wp_get_attachment_image_src($image,'full');
				$image = $image[0];
			}else{
				$image = plugins_url('../assets/images/avatar.jpg',__FILE__);
			}
			$contents = str_replace('[DEFAULT_IMAGE]','"'.$image.'"', $contents);
			$pid = vibebp_get_setting('offline_page','service_worker','general');
			$contents = str_replace('[OFFLINE_URL]','"'.get_permalink($pid).'"', $contents);
			ob_start();
			do_action('vibebp_default_service_worker',$contents);
			$contents .= ob_get_clean();

        }else{
        	_e('File missing','vibebp');
        	die();
        }
    	

        
        return $contents;
    }

    function pre_cache(){
    	
    	if(vibebp_get_setting('service_workers')){
    		$pid = vibebp_get_setting('offline_page','service_worker','general');
    		if(is_page($pid)){
    			global $wp_scripts;global $wp_styles;
    			$scripts = $styles = [];
    			if(!empty($_GET['pre_cache'])){
		    		foreach( $wp_scripts->queue as $script ){
		    			$flag = apply_filters('vibebp_precache_script',true,$script);
		    			if($flag){
		    				$scripts[] =  $wp_scripts->registered[$script]->src;
		    			}
		    		}
		    		foreach( $wp_styles->queue as $style ){
		    			$flag = apply_filters('vibebp_precache_style',true,$script);
		    			if($flag){
				       		$styles[] =  $wp_styles->registered[$style]->src;
				       	}
		    		}
				    
		    		update_option('vibe_sw_scripts',$scripts);
					update_option('vibe_sw_styles',$styles);
				}
	    	}
    	}
    }

    function apply_sw_version(){
    	if(vibebp_get_setting('service_workers')){
    		$pid = vibebp_get_setting('offline_page','service_worker','general');
    		if(is_page($pid)){
    			global $wp_scripts;global $wp_styles;
    			foreach( $wp_scripts->queue as $script ){
					$flag = apply_filters('vibebp_precache_script',true,$script);
	    			if($flag){
						$wp_scripts->registered[$script]->ver=vibebp_get_setting('version','service_worker','general');
					}
				}
				
				foreach( $wp_styles->queue as $style ){
					$flag = apply_filters('vibebp_precache_style',true,$script);
	    			if($flag){
	    				$wp_styles->registered[$style]->ver=vibebp_get_setting('version','service_worker','general');
	    			}
				}
    		}
    	}
    }
    function generate_manifest($force=0){
    	//app_name  app_description app_icon app_screenshot offline_page pre-cached
    	
    	$app_icon = vibebp_get_setting('app_icon','service_worker','general');
    	if(!empty($app_icon)){
    		$att = wp_get_attachment_image_src($app_icon,'full');
    		if(empty($att) || get_post_mime_type($app_icon) != 'image/png'){
    			echo json_encode(array('status'=>1,'message'=>__('App Icon not a png image.','vibebp')));
				die();
    		}
    		if($att[1] >= 512 && $att[2] >=512){
	    		$image = wp_get_image_editor( $att[0] );
	    		
	    		$upload_dir = wp_get_upload_dir();
	    		$path = $upload_dir['basedir'];
				if ( ! is_wp_error( $image ) ) {
					if($att[1] >512){
						$image->resize( 512, 512, true );	
						$saved[512] = $image->save( $path.'/icon-512x512.png' );
					}
				    $image->resize( 384, 384, true );
				    $saved[384]=$image->save( $path.'/icon-384x384.png' );
				    $image->resize( 192, 192, true );
				    $saved[192]=$image->save( $path.'/icon-192x192.png' );
				    $image->resize( 152, 152, true );
				    $saved[152]=$image->save( $path.'/icon-152x152.png' );
				    $image->resize( 144, 144, true );
				    $saved[144]=$image->save( $path.'/icon-144x144.png' );
				    $image->resize( 128, 128, true );
				    $saved[128]=$image->save( $path.'/icon-128x128.png' );
				    $image->resize( 96, 96, true );
				    $saved[96]=$image->save( $path.'/icon-96x96.png' );
				    $image->resize( 72, 72, true );
				    $saved[72]=$image->save( $path.'/icon-72x72.png' );
				}	
			}else{
				echo json_encode(array('status'=>1,'message'=>__('App Icon image size less than recommended dimensions','vibebp')));
				die();
			}
    	}else{
    		$upload_dir=array('baseurl'=>plugins_url('../assets/images/icons',__FILE__));
    	}

    	$app_splash = vibebp_get_setting('app_screenshot','service_worker','general');

    	
    	if(!empty($app_splash)){
    		$att = wp_get_attachment_image_src($app_splash,'full');
    		if(empty($att) || get_post_mime_type($app_splash) != 'image/png'){
    			echo json_encode(array('status'=>1,'message'=>__('App Splash Image not a png image.','vibebp')));
				die();
    		}

    		
    		if($att[1] >= 2048 && $att[2] >=2732){
	    		$image = wp_get_image_editor( $att[0] );
	    		
	    		$upload_dir = wp_get_upload_dir();
	    		$path = $upload_dir['basedir'];
				if ( ! is_wp_error( $image ) ) {
					if($att[1] >2048){
						$image->resize( 2048, 2732, true );	
						$image->save( $path.'/splash-2048x2732.png' );
					}
				    $image->resize( 640, 1136, true );
				    $image->save( $path.'/splash-640x1136.png' );
				    $image->resize( 750, 1294, true );
				    $image->save( $path.'/splash-750x1294.png' );
				    $image->resize( 1242, 2148, true );
				    $image->save( $path.'/splash-1242x2148.png' );
				    $image->resize( 1125,2436, true );
				    $image->save( $path.'/splash-1125x2436.png' );
				    $image->resize( 1536, 2048, true );
				    $image->save( $path.'/splash-1536x2048.png' );
				    $image->resize( 1668, 2224, true );
				    $image->save( $path.'/splash-1668x2224.png' );
				    $image->resize( 828, 1792, true );
				    $image->save( $path.'/splash-828x1792.png' );
				}	
			}else{
				echo json_encode(array('status'=>1,'message'=>__('App Icon image size less than recommended dimensions','vibebp')));
				die();
			}
    	}

    	$customizer = VibeBP_Customizer::init();
    	$theme_color = '';
    	$background_color= '';
    	if(!empty($customizer->customizer)){
    		$theme_color = $customizer->customizer['light_primary'];
    		$background_color=$customizer->customizer['light_body'];
    		if($customizer->customizer['dark_theme']){
	   			$theme_color = $customizer->customizer['dark_primary'];
	    		$background_color=$customizer->customizer['dark_body'];
	   		}
	   		
    	}
    	if(empty($background_color)){$background_color = '#fafafa';}
	   	if(empty($theme_color)){$theme_color = '#598dee';}
   		
   		
   		$display_mode = vibebp_get_setting('app_display','service_worker','general');
   		if(empty($display_mode)){
   			$display_mode = 'fullscreen';
   		}
   		$scope ='/';

   		if ( ! function_exists( 'get_home_path' ) ) {
            include_once ABSPATH . '/wp-admin/includes/file.php';
        }
   		$site_root = get_home_path();
   		$site_url = site_url();
		if($_SERVER["DOCUMENT_ROOT"] != $site_root){
			$scope =  rtrim(str_replace($_SERVER["DOCUMENT_ROOT"],'', $site_root).$scope,'/').'/';
		}
    	$manifest = array(
    		"lang"=>get_locale(),
  			"name"=>vibebp_get_setting('app_name','service_worker','general'),
  			"short_name"=>vibebp_get_setting('app_short_name','service_worker','general'),
  			"theme_color"=> $theme_color,
			"background_color"=> $background_color,
  			"display"=>$display_mode,
  			"orientation"=>"any",
  			"scope"=> $scope, 
  			"start_url"=>get_permalink(vibebp_get_setting('offline_page','service_worker','general')),
  			"gcm_sender_id"=>"103953800507",
  			"icons"=> array(
  				array(
  					"src"=> $upload_dir['baseurl']."/icon-72x72.png",
				    "sizes"=> "72x72",
				    "type"=> "image/png"
  				),
  				array(
  					"src"=> $upload_dir['baseurl']."/icon-96x96.png",
			      	"sizes"=> "96x96",
			      	"type"=> "image/png"
  				),
  				array(
  					"src"=> $upload_dir['baseurl']."/icon-128x128.png",
      				"sizes"=> "128x128",
      				"type"=> "image/png"
  				),
  				array(
  					"src"=> $upload_dir['baseurl']."/icon-144x144.png",
      				"sizes"=> "144x144",
      				"type"=> "image/png"
  				),
  				array(
  					"src"=> $upload_dir['baseurl']."/icon-152x152.png",
      				"sizes"=> "152x152",
      				"type"=> "image/png"
  				),
  				array(
  					"src"=> $upload_dir['baseurl']."/icon-192x192.png",
				      "sizes"=> "192x192",
				      "type"=> "image/png"
  				),
  				array(
  					"src"=> $upload_dir['baseurl']."/icon-384x384.png",
			      	"sizes"=> "384x384",
			      	"type"=> "image/png"
  				),
  				array(
  					"src"=> !empty($att) && $att[1] == 512?wp_get_attachment_url($app_icon):$upload_dir['baseurl']."/icon-512x512.png",
      				"sizes"=> "512x512",
      				"type"=> "image/png"	
  				),
  			),
    	);

    	$site_root = get_home_path();	

    	if(!file_exists($site_root.'/manifest.json') || $force){
            $myFile = $site_root."/manifest.json";
            $fh = fopen($myFile, 'w');
            fwrite($fh, json_encode($manifest)."\n");
            fclose($fh);
        }
        return;
    }

	function install_sw($force=0){
		
		if(vibebp_get_setting('service_workers')){
			
			if ( ! function_exists( 'get_home_path' ) ) {
	            include_once ABSPATH . '/wp-admin/includes/file.php';
	        }
        
			$site_root = get_home_path();				            
	        if(!file_exists($site_root.'/firebase-messaging-sw.js') || $force){
	            $myFile = $site_root."/firebase-messaging-sw.js";

	            $fh = fopen($myFile, 'w');
	            $firebase_sw = $this->getvw();
	            fwrite($fh, print_r($firebase_sw, true)."\n");
	            fclose($fh);
	        }
        }
        delete_transient('vibebp_show_update_service_worker_notice');
        echo json_encode(array('status'=>1,'message'=>__('Successfully Generated','vibebp')));
        die();
	}

	function show_screen(){

	}
	function add_login_portal(){
		echo '<div id="vibebp_login_wrapper"></div>';
	}
	function add_notifications_portal(){
		echo '<div id="vibebp_notifications_wrapper"></div>';
	}

	function synchronise_wp_logins(){
		if(is_user_logged_in()){
			$sync_login = vibebp_get_setting('sync_login');

			if(!empty($sync_login)){
				wp_enqueue_script('localforage',plugins_url('../assets/js/localforage.min.js',__FILE__),array(),VIBEBP_VERSION,true);

				$init = VibeBP_Init::init();
				$current_user = wp_get_current_user();

				$blog_id = '';
                if(function_exists('get_current_blog_id')){
                    $blog_id = get_current_blog_id();
                }

				?><script>document.addEventListener("DOMContentLoaded",function(){localforage.getItem("bp_login_token").then(function(e){if(!e){var t=new XMLHttpRequest;t.open("POST",ajaxurl),t.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),t.onload=function(){if(200===t.status){let n=JSON.parse(t.responseText);if(n.status){localforage.setItem("bp_login_token",n.token);var e=new CustomEvent("tokenGenerated");document.dispatchEvent(e)}}},t.send(encodeURI("action=generate_token&client_id=<?php echo vibebp_get_setting('client_id'); ?>&security=<?php echo wp_create_nonce('security'); ?>&email=<?php echo urlencode($current_user->user_email); ?>"))}})});
				</script>
				<?php

			}
		}
	}

	function generate_token(){
		
		$id = email_exists(sanitize_email(urldecode($_POST['email'])));
		if($id !== get_current_user_id()){
			$data = array(
	        	'status' => 0,
	        	'message'=>__('Email & UserID do not match.','vibebp')
	        );
	        echo json_encode($data);
	        die();
		}
		
		$token =vibebp_generate_token();

		/** The token is signed, now create the object with no sensible user data to the client*/
        $data = array(
        	'status' => 1,
            'token' => $token,
            'message'=>_x('Token generated','Token generated','vibebp')
        );

		echo json_encode($data);
        die();
	}




	function login_detect_at_woocommerce_checkout(){
		
		if(function_exists('wc_get_page_id') && is_page(apply_filters('vibebp_wc_get_page_id',wc_get_page_id('checkout'))) && !is_user_logged_in()){
			$rand = 'ldawc'.rand(0,999999)
;		?>
		<script id="<?php echo $rand; ?>">
			document.addEventListener('DOMContentLoaded',function(){
				
				if(typeof localforage == 'object'){
					localforage.getItem('bp_login_token').then(function(token){
						if(token){
						    var xhr = new XMLHttpRequest();
							xhr.open('POST', ajaxurl);
							xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							xhr.onload = function() {
							    if (xhr.status === 200) {
							    	let check = JSON.parse(xhr.responseText);
							    	if(check.status){
							    		window.location.reload(true);
							    	}
							    }
							    document.querySelector('#<?php echo $rand; ?>').remove();
							};

							xhr.send(encodeURI('action=vibebp_wc_login&client_id=<?php echo vibebp_get_setting('client_id'); ?>&security=<?php echo wp_create_nonce('security'); ?>&token=' + token));
						}
					});
				}
			});	
		</script>
		<?php
		}
	}
	
	function user_activated($user_id){

        update_user_meta($user_id,bp_get_user_meta_key('last_activity'),0);
    }

    function user_activated_login(){

        if(is_user_logged_in() && function_exists('bp_is_user_profile') && bp_is_user_profile() && empty(bp_get_user_last_activity()) ){

            wp_enqueue_script('localforage',plugins_url('../assets/js/localforage.min.js',__FILE__),array(),VIBEBP_VERSION,true);

            if(is_user_logged_in()){
                $current_user = wp_get_current_user();
            ?><script>
                document.addEventListener('DOMContentLoaded',function(){ 
                    
                    localforage.getItem('bp_login_token').then(function(token){
                        
                        if(!token){
                            var xhr = new XMLHttpRequest();
                            xhr.open('POST', ajaxurl);
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            xhr.onload = function() {
                                if (xhr.status === 200) {
                                    let check = JSON.parse(xhr.responseText);
                                    if(check.status){
                                        localforage.setItem('bp_login_token',check.token).then(function(){
                                        	var event = new CustomEvent("tokenGenerated");
											document.dispatchEvent(event);	
                                        });
                                        //
                                    }else{
                                        localforage.removeItem('bp_login_token');
                                    }
                                }
                            };

                            xhr.send(encodeURI('action=generate_token&client_id=<?php echo vibebp_get_setting('client_id'); ?>&security=<?php echo wp_create_nonce('security'); ?>&email=<?php echo $current_user->user_email; ?>'));
                        }
                    });
                });
            </script>
            <?php
            }
            
        }
        ?>
        <script>setTimeout(function(){if(document.querySelector('body') && document.querySelector('body').classList.contains('loading')){
        	document.querySelector('body').classList.remove('loading'); }},5000);</script>
        <?php
    }


    function show_google_captcha(){
		
    	$google_captcha_public_key = vibebp_get_setting('google_captcha_private_key','general','login');
    	if(empty($google_captcha_public_key)){
    		return;
    	}

        if(!empty($google_captcha_public_key)){
        	if ( ! wp_script_is( 'google-recaptchav3', 'enqueued' ) ) {
                wp_register_script('google-recaptchav3','https://www.google.com/recaptcha/api.js?render='.vibebp_get_setting('google_captcha_public_key','general','login'));
            }
        }
        
    }

    function google_captcha_validate(){

    	$google_captcha_private_key = vibebp_get_setting('google_captcha_private_key','general','login');
    	if(empty($google_captcha_private_key)){
    		return;
    	}

    }

    function member_follow($follower,$user_id){
    	if(!function_exists('bp_activity_add'))
    		return;
    	bp_activity_add(
    		array(
    			'user_id' => $follower, 
    			'action' => _x('User Followed','','vibebp'), 
    			'content' => sprintf(_x('%s followed %s','','vibebp'),bp_core_get_userlink($follower),bp_core_get_userlink($user_id)),
    			'component' => 'buddypress', 
    			'type' => 'member_follow', 
    			'item_id' => $user_id, 
    		)
    	);
    }

    function member_unfollow($follower,$user_id){
    	if(!function_exists('bp_activity_add'))
    		return;
    	bp_activity_add(
    		array(
    			'user_id' => $follower, 
    			'action' => _x('User Un-followed','','vibebp'), 
    			'content' => sprintf(_x('%s un-followed %s','','vibebp'),bp_core_get_userlink($follower),bp_core_get_userlink($user_id)),
    			'component' => 'buddypress', 
    			'type' => 'member_unfollow', 
    			'item_id' => $user_id, 
    		)
    	);
    }

    function record_invite_code_activity($user_id,$user_args,$usermeta,$invite_code){
    	if(!function_exists('bp_activity_add'))
    		return;
    	bp_activity_add( apply_filters('record_invite_code_activity',array( 
    		'user_id' => $user_id, 
    		'action' => _x('Regsitration using invitation code','','vibebp'), 
    		'content' => sprintf(_x('%s registered using invite code %s','','vibebp'),bp_core_get_userlink($user_id),$invite_code), 
    		'component' => 'vibebp', 
    		'type' => 'register', 
    		'hide_sitewide' => true ) 
    	));
    }
}

VibeBP_Actions::init();
