<?php
/**
 * Initialise plugin
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



class VibeBP_Init{

	public $group=[];//cache group
	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Init();
        return self::$instance;
    }

	private function __construct(){

		
		//BP 12 compatibility
		add_filter('bp_core_get_query_parser',[$this,'bp_legacy_parser']);
		add_action('bp_setup_nav',[$this,'add_user_domain']);
		add_filter( 'bp_core_get_directory_post_type', [$this,'bp_classic_get_directory_post_type'], 10, 0 );
		//BP 12 END

		add_action('init',array($this,'record_bp_setup_nav'));
		
		add_filter('vibebp_vars',array($this,'add_login_api'),99);
		add_action( 'widgets_init', array($this,'dashboard_sidebar'),99);
		add_action('init',array($this,'register_member_dashboards'));

		add_filter('bp_core_avatar_full_width',array($this,'avatar_full_width'));
		add_filter('bp_core_avatar_full_height',array($this,'avatar_full_height'));
		add_filter('bp_core_avatar_thumb_width',array($this,'avatar_thumb_width'));
		add_filter('bp_core_avatar_thumb_height',array($this,'avatar_thumb_height'));

		add_action('init',array($this,'logout_user'));
		add_filter('vibebp_elementor_filters',array($this,'bp_profile'));


		add_filter('vibebp_public_profile_layout_query',array($this,'profile_layout_query'));
		add_filter('vibebp_group_layout_query',array($this,'group_layout_query'));



		add_filter('vibebp_member_card',array($this,'member_card'),10,2);
		add_filter('vibebp_group_card',array($this,'group_card'),10,2);


		remove_action( 'bp_enqueue_scripts', 'bp_core_register_common_scripts', 1 );

		add_action('wp_footer', array($this,'dispatch_content_event'),9999);
		add_filter('vibe_helpdesk_get_component_forum',[$this,'get_group_Forum'],10,2);


		add_action('template_redirect',array($this,'add_products_to_cart'),1);
		
		add_filter('vibebp_validate_token',[$this,'validate_token']);
        	
		add_filter( 'wp_is_application_passwords_available', '__return_true' );	
	}

	/* BP 12 Compatibility */
	function bp_legacy_parser($f){
		return 'legacy';
	}
	function bp_classic_get_directory_post_type() {
		return 'page';
	}
	
	function add_user_domain(){ 
		global $bp; 
		if(empty((array)$bp->displayed_user)){ 
			$bp->displayed_user->domain=''; 
		} 
	}
	/* END BP 12 Compatibility */


	function validate_token($expanded_token){
		$ip = vibebp_get_client_ip();
		
		if(vibebp_get_setting('ip_validate_token','general','general')){
			if(empty($expanded_token->data->ip)|| $expanded_token->data->ip != $ip){
				$expanded_token =0;
			}
		}
		return $expanded_token;
	}
	

    function get_group_Forum($return,$detail){
        if(!empty($detail['group'])){
            $return = groups_get_groupmeta($detail['group']['id'],'forum_id',true);
        }
        return $return;
    }
	

	function add_products_to_cart(){
        if(function_exists('wc_get_cart_url')){
            if(is_page(wc_get_page_id('cart'))){
                if(!empty($_COOKIE['cart_items'])){
                    $str = stripslashes(urldecode($_COOKIE['cart_items']));
                    
                    $str = utf8_encode($str);
                    $products = json_decode($str,true);

                    if(!empty($products)){

                        $cart_items = WC()->cart->get_cart();
                        $cart_pids=[];
                        if(!empty($cart_items)){
                            forEach($cart_items as $item){
                                if(!empty($item['product_id'])){
                                    $cart_pids[]=$item['product_id'];
                                }
                            }    
                        }
                    
                        foreach($products as $product){              

                            if(!empty($product['quantity'])){
                                WC()->cart->add_to_cart($product['id'],intval($product['quantity']));
                            }else{
                                WC()->cart->add_to_cart($product['id'],1);
                            }
                            
                        }
                        //expire the cookie
                        setcookie("cart_items", "", time()-3600,COOKIEPATH,$_SERVER['SERVER_NAME']);
                    }
                }
                
            }
        }
    }
    

	function dispatch_content_event() {
		
	
		?>
			<script>	
				document.dispatchEvent(new Event('VibeBP_Editor_Content'));
			</script>
		<?php
	}


	function record_bp_setup_nav(){

		if(is_user_logged_in() && (bp_is_my_profile() || ( !empty(vibebp_get_setting('bp_single_page')) && get_permalink(vibebp_get_setting('bp_single_page') ) ) ) && current_user_can('manage_options')){

			$bp_rest_api_nav = get_transient('bp_rest_api_nav');
			$reload_nav = get_option('vibebp_reload_nav');
			//if(empty($bp_rest_api_nav) || !empty($reload_nav) || !empty($_GET['reload_nav'])){
			if(empty($reload_nav) || !empty($_GET['reload_nav'])){

				$nav =[];

			    global $bp;
			    $bpnav = new ReflectionObject($bp->members->nav);
			    $property = $bpnav->getProperty('nav');
			    $property->setAccessible(true);
			    $index = -1;
			    $profile_avatar_exists = -1;
		    	foreach($property->getValue($bp->members->nav) as $members_nav){
		    	
			        foreach($members_nav as $key=>$obj){

			        	if(!is_callable($obj)){
			        		$item = (Array)($obj);
				        	$item['class']=array('menu-child');
				        	if(!empty($item['parent_slug'])){
				        		$item['parent'] = $item['parent_slug'];
				        	}
				        	$item['name'] = apply_filters('vibebp_force_apply_translations',translate($item['name'],'vibebp'),$item);
				        	if(!empty($item['parent_slug']) && $item['parent_slug']==bp_get_profile_slug() && $item['slug']=='edit'){
				        		$index = count($nav);
				        	}
				        	if(!empty($item['parent_slug']) && $item['parent_slug']==bp_get_profile_slug() && $item['slug']=='change-avatar'){
				        		$profile_avatar_exists = count($nav);
				        	}
				            $nav[]=$item;
			        	}
		        			
			        	
			        }
			    }
			   	if($profile_avatar_exists == -1 && $index > -1){
			   		array_splice($nav, $index+1,0,array($this->get_profile_photo_object()));
			   	}
			    
			     
			    set_transient('bp_rest_api_nav',$nav,0);
			    update_option('vibebp_reload_nav',$nav);
			    do_action('vibebp_record_bp_setup_nav',$nav);
			}
		}
	}

	function get_profile_photo_object(){
		$slug         = bp_get_profile_slug();
		$access       = bp_core_can_edit_settings();
		return array(
				'name'            => _x( 'Change Profile Photo', 'Profile header sub menu', 'vibebp' ),
				'slug'            => 'change-avatar',
				'parent_slug'     => $slug,
				'screen_function' => 'bp_members_screen_change_avatar',
				'position'        => 30,
				'user_has_access' => $access,
				'class' => array('menu-child'),
				'parent' => $slug,
				'css_id' => 'change-avatar',
            
			);
	}

	function logout_user(){
		if(!empty($_GET['vibebp_logout']) && is_user_logged_in()){
			wp_destroy_current_session();
			$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$actual_link = strtok($actual_link, '?');//remove all get variables
			wp_redirect($actual_link);
			exit;
		}
	}

	function dashboard_sidebar() {

	    register_sidebar( array(
	        'name' => __( 'VibeBP Member Dashboard', 'vibebp' ),
	        'id' => 'vibebp-dashboard',
	        'description' => __( 'Widgets appear in Dashboard', 'vibebp' ),
	        'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>',
	    ) );
	}

	function register_member_dashboards(){
		$member_type_based_dashboard = vibebp_get_setting('member_type_based_dashboard','bp','general');


        if(!empty($member_type_based_dashboard) && $member_type_based_dashboard == 'on'){
            $types = bp_get_member_types(array(),'objects');
 			
            foreach($types as $type => $labels){
            	register_sidebar( array(
			        'name' => sprintf(__( 'VibeBP Member Dashboard for %s','vibebp' ),$labels->labels['name']),
			        'id' => 'vibebp-dashboard-'.$type,
			        'description' => __( 'Widgets appear in Dashboard', 'vibebp' ),
			        'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h3 class="widgettitle">',
					'after_title'   => '</h3>',
			    ) );
            }
        }
	}
	
	function avatar_full_width($width){
		$this->get_settings();
		if(!empty($this->settings['bp']['bp_avatar_full_width'])){
			return $this->settings['bp']['bp_avatar_full_width'];
		}

		return 300;
	}
	function avatar_full_height($height){
		$this->get_settings();
		if(!empty($this->settings['bp']['bp_avatar_full_height'])){
			return $this->settings['bp']['bp_avatar_full_height'];
		}

		return 300;
	}
	function avatar_thumb_width($width){
		$this->get_settings();
		if(!empty($this->settings['bp']['bp_avatar_thumb_width'])){
			return $this->settings['bp']['bp_avatar_thumb_width'];
		}

		return 150;
	}
	function avatar_thumb_height($height){
		$this->get_settings();
		if(!empty($this->settings['bp']['bp_avatar_thumb_height'])){
			return $this->settings['bp']['bp_avatar_thumb_height'];
		}

		return 150;
	}

	function get_settings(){
		if(empty($this->settings)){
			$this->settings = get_option(VIBE_BP_SETTINGS);
		}

		return $this->settings;
	}
	function add_login_api($vars){

		if(empty($vars['api'])){
			$vars['api']=[];
		}
		
		$vars['api']['api_security']=vibebp_get_api_security();
		$vars['api']['generate_token']= get_rest_url('',VIBEBP_NAMESPACE.'/'. VIBEBP_TOKEN .'/generate-token/');
		$vars['api']['validate_token']=get_rest_url('',VIBEBP_NAMESPACE. '/'. VIBEBP_TOKEN .'/validate-token/');
		$vars['api']['regenerate_token']=get_rest_url('',VIBEBP_NAMESPACE. '/'. VIBEBP_TOKEN .'/regenerate-token/');
		$vars['api']['remove_token']=get_rest_url('',VIBEBP_NAMESPACE. '/'. VIBEBP_TOKEN .'/remove-token/');;
		
		return $vars;
	}

	function get_security(){


		if(empty($this->security)){
			$this->security = get_transient('vibebp_api_security');
			if(empty($this->security)){
				$this->security = wp_generate_password(8,false,false);
				set_transient('vibebp_api_security',$this->security,24*HOUR_IN_SECONDS);
			}
		}
		return $this->security;
	}
	
	function install(){
		
		add_rewrite_rule('vibebpsw.js','/wp-admin/admin-ajax.php?action=vibebp-sw', 'top');
		flush_rewrite_rules();
	}


	function get_profile_link(){
		$this->get_settings();

		$slug = '';
		if(empty($this->settings['general']) || empty($this->settings['general']['profile']) ){
			$slug = 'profile';
		}else{
			$slug = $this->settings['general']['profile'];
		}
		return home_url().'/'.$slug;
	}

	function bp_page_id($page){
        if(empty($this->bp_pages)){
            $this->bp_pages = get_option('bp-pages');
        }
        if(function_exists('icl_object_id')){
            $this->bp_pages[$page] = icl_object_id($this->bp_pages[$page], 'page', true);
        }

        if(isset($this->bp_pages[$page])){
        	return $this->bp_pages[$page];
        }else{
        	return home_url().'/'.$page;
        }
        
    }

    function get_setting($field,$type = 'general',$sub=null){
    	$this->get_settings();

    	if(!empty($sub)){
    		if(!empty($this->settings[$type][$sub][$field])){
    			return $this->settings[$type][$sub][$field];
    		}
    	}else if(!empty( $field)){
    		return  isset($this->settings[$type][$field])?$this->settings[$type][$field]:'';
    	}else if(!empty($type)){
    		return $this->settings[$type];
    	}
    	return false;
    }

    function bp_profile($args){

    	if(bp_displayed_user_id()){
    		$args['author']=bp_displayed_user_id();
    	}

    	return $args;
    }

    function profile_layout_query($args){

    	$id = get_user_meta(bp_displayed_user_id(),'member_profile',true);
    	if(!empty($id)){
    		return array(
    			'post_type'=>'member-profile',
				'posts_per_page'=>1,
				'p'=>$id
    		);
    	}
    	$member_type = bp_get_member_type(bp_displayed_user_id());
    	
    	if(!empty($member_type)){
			$args=array(
			'post_type'=>'member-profile',
			'posts_per_page'=>1,
			'meta_query'=>array(
				'relation'=>'AND',
				array(
					'key'=>'member_type',
					'value'=>$member_type,
					'compare'=>'='
				)
			));
		}
		return $args;
	}

	function group_layout_query($args){

    	$group_type = bp_groups_get_group_type(bp_get_group_id());
    	if(!empty($group_type)){
			$args=array(
			'post_type'=>'group-layout',
			'posts_per_page'=>1,
			'meta_query'=>array(
				'relation'=>'AND',
				array(
					'key'=>'group_type',
					'value'=>$group_type,
					'compare'=>'='
				)
			));
		}
		return $args;
	}

	function member_card($args,$user_id){
		$member_type = bp_get_member_type($user_id);
		if(!empty($member_type)){
			$args=array(
			'post_type'=>'member-card',
			'posts_per_page'=>1,
			'meta_query'=>array(
				'relation'=>'AND',
				array(
					'key'=>'member_type',
					'value'=>$member_type,
					'compare'=>'='
				)
			));
		}
		return $args;
	}
	function group_card($args,$group_id){
		$type = groups_get_groupmeta($group_id,'group_layout',true);
		if(!empty($type)){
			unset($args['meta_query']);
			$args['post__in'] = array($group_id);
		}else{
			$group = groups_get_group($group_id);
			$group_type = bp_get_group_type($group);
			if(!empty($group_type)){
				$args=array(
				'post_type'=>'group-card',
				'posts_per_page'=>1,
				'meta_query'=>array(
					'relation'=>'AND',
					array(
						'key'=>'group_type',
						'value'=>$group_type,
						'compare'=>'='
					)
				));
			}
		}
		return $args;
	}
	

}
VibeBP_Init::init();

function vibebp_get_api_security(){
	$init = VibeBP_Init::init();;
	return $init->get_security();
}

function vibebp_get_profile_link($user_nicename){
	if(!empty(vibebp_get_setting('bp_single_page','general','general'))){
		$single_page =vibebp_get_setting('bp_single_page','general','general');
		if(function_exists('icl_object_id')){
			$single_page = icl_object_id($single_page, 'page', true);
		}
		return esc_url(get_permalink($single_page ));
	}
	if(is_numeric($user_nicename)){
		$user = get_userdata($user_nicename);
		$user_nicename = $user->user_nicename;
	}
	return esc_url(get_permalink(vibe_get_bp_page_id('members')).$user_nicename);
}

if(!function_exists('vibe_get_bp_page_id')){
    function vibe_get_bp_page_id($page){
        $init = VibeBP_Init::init();  
        return $init->bp_page_id($page);
    }
}

function vibebp_get_setting($field,$type='',$sub=''){
	$init = VibeBP_Init::init();

	$val = $init->get_setting($field,$type,$sub);
	if( (empty($type) || $type == 'general') && empty($val) && empty($sub)){
		$val = $init->get_setting($field,'general','general');
		if(empty($val)){
			$val = $init->get_setting($field,'general');
		}
	}

	return apply_filters('vibebp_get_setting',$val,$field);
}

function vibebp_member_class(){
	
}	

function vibebp_api_get_user_from_token($token){
	$data = vibebp_expand_token($token);
	if(!empty($data['status'])){
		return $data['data']->data->user;
	}
	return false;
}


function vibebp_get_pwa_scripts($force=null){
	$vibe_scripts = array(
			    	'vibecal','vibebp-swiper','vibeforms','tabulator','helpdesk','vibekb','vibe-projects
			','vibedrive','vibedrive_group','wplms-course-component-js','localforage','vibebplogin',
			'wplms_dash_text','wplms_course_progress','wplms_dash_activity','wplms_dash_text','contact_users',
			'wplms_todo_task','wplms_dashboard_student_stats','wplms_dashboard_simple_stats','wplms_dashboard_notes_discussions','wplms_dashboard_mymodules','wplms_dashboard_instructor_simple_stats', 'wplms_dashboard_news','wplms_dashboard_instructor_stats',
			'wplms_dashboard_instructor_commissions','wplms_dashboard_instructor_announcements','wplms_dash_instructing_modules','wplms_instructor_students_widget','firebase','firebase-auth','firebase-database','firebase-messaging','vibebp_live','flatpickr','colorpickr','plyr','tus','vibebpprofile','vibe_editor','swiper'
	    ); 
	global $wp_scripts;
	if(vibebp_get_setting('service_workers')){
		$links = vibebp_get_setting('pre-cached','service_worker');	
		if(!empty($links)){
			
			
			foreach($wp_scripts->registered as $key=>$script){
				$script = (Array)$script;
				if(!empty($script['src']) && in_array($script['src'],$links)){
					$vibe_scripts[]=$key;
				}
			}
		}
	}
	
	$vibe_scripts = apply_filters('vibebp_sw_preache_scripts',$vibe_scripts);
	$allscripts=array();
	
	if($force && !empty($vibe_scripts)){
		foreach($wp_scripts->registered as $key=>$script){
			$script = (Array)$script;
			
			if(!empty($script['src']) && in_Array($key,$vibe_scripts)){
				$allscripts[]=$script['src'];
			}
		}
		$allscripts[]= includes_url('js/dist/element.min.js');
		$allscripts[]= includes_url('js/dist/data.min.js');
		$allscripts[]= includes_url('js/dist/redux-routine.min.js');
		$allscripts[]= includes_url('js/dist/hooks.min.js');
		$allscripts[]= includes_url('js/dist/vendor/react-dom.min.js');
		$allscripts[]= includes_url('js/dist/vendor/react.min.js');
		$allscripts[]= includes_url('js/dist/vendor/lodash.min.js');
		$allscripts[]= includes_url('js/dist/vendor/wp-polyfill.min.js');
		$allscripts[]= includes_url('js/dist/escape-html.min.js');
		$allscripts[]= includes_url('js/dist/compose.min.js');
		$allscripts[]= includes_url('/js/dist/deprecated.min.js');
		$allscripts[]= includes_url('js/dist/priority-queue.min.js');
		update_option('vibe_sw_scripts',$allscripts); 
	}

	//set transient
	return $vibe_scripts;
	    
}


function vibebp_get_pwa_styles($force=null){
	$vibe_styles = array(
	    	'bp-member-block','bp-group-block','vibebp-frontend','vibecal','vibeforms_profile_css','tabulator','helpdesk','vibekb','vicons','vibe-projects','vibedrive_profile_css','wplms-cc', 'wplms_dashboard_css', 'vibebp-swiper','vibebp_main', 'vibebp_profile_libs', 'plyr', 'vibe_editor','swiper');
	
	global $wp_styles;
	if(vibebp_get_setting('service_workers')){
		$links = vibebp_get_setting('pre-cached','service_worker');	
		if(!empty($links) && is_Array($links)){
			
			foreach($wp_styles->registered as $key=>$style){
				$style = (Array)$style;

				if(!empty($style['src']) && in_array($style['src'],$links)){
					$vibe_styles[]=$key;
				}
			}
		}
	}
	$vibe_styles = apply_filters('vibebp_sw_precache_styles',$vibe_styles);
	$allstyles = array();
	if($force && !empty($vibe_styles)){
		foreach($wp_styles->registered as $key=>$style){
			$style = (Array)$style;
			if(!empty($style['src']) && in_Array($key,$vibe_styles)){
				$allstyles[]=$style['src'];
			}
		}
		
		update_option('vibe_sw_styles',$allstyles);
	}

	return $vibe_styles;
}