<?php


/**
 * Customizer functions for vibeapp
 *
 * @author      VibeThemes
 * @category    Actions
 * @package     vibeapp
 * @version     1.0
 */


if ( ! defined( 'ABSPATH' ) ) exit;


class VibeBp_Shortcodes{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBp_Shortcodes();

        return self::$instance;
    }

    private function __construct(){

    	add_action('wp_enqueue_scripts',array($this,'enqueue_Scripts'));
    	add_action('template_redirect',array($this,'check_shortcode'),0);
    	add_shortcode('vibebp_login',array($this,'login_shortcode'));
    	add_shortcode('vibebp_profile',array($this,'profile'));
    	add_shortcode('vibebp_carousel',array($this,'vibeapp_carousel'));
    	add_filter('vibebp_service_worker_js_scope',array($this,'check_scope_setting'));
    	add_shortcode('vibebp_registration_form',array($this,'vibebp_registration_form'));
    	add_shortcode('vibebp_member_type_count',array($this,'vibebp_member_type_count'));
    	add_shortcode('profile_field',array($this,'profile_field'));
		add_shortcode('group_field',array($this,'group_field'));
    	add_shortcode('vicon',array($this,'vicon'));

    	add_shortcode('vibe_search',array($this,'vibebp_search'));

    	add_shortcode('taxonomy_post_carousel',[$this,'taxonomy_post_carousel']);
		add_action('wp_ajax_nopriv_viebbp_load_taxonomy_carousel',[$this,'viebbp_load_taxonomy_carousel']);
		add_action('wp_ajax_viebbp_load_taxonomy_carousel',[$this,'viebbp_load_taxonomy_carousel']);
    }

    function vibebp_search($atts,$content = null){
    	global $wp;
    	$html ='<div class="vibe_search"><form method="get" action="'.home_url( $wp->request ).'">
    	<input type="text" name="s" value="'.(empty($_GET['search'])?'':esc_html($_GET['search'])).'" placeholder="'.(empty($atts['placeholder'])?__('Type to search...','vibebp'):$atts['placeholder']).'"/><span class="vicon vicon-search"></span>';
    	
    	if(!empty($atts['post_type'])){
    		if(strpos($atts['post_type'], ',') > -1){
    			$pts = explode(',',$atts['post_type']);
    			foreach($pts as $pt){
    				$html .= '<input type="hidden" name="post_type" value="'.esc_attr($pt).'" />';	
    			}
    		}else{
    			$html .= '<input type="hidden" name="post_type" value="'.esc_attr($atts['post_type']).'" />';	
    		}
    	}
    	

    	$html .='</form></div><style>.vibe_search form{display:flex;align-items:center;padding:7px 10px;border:1px solid var(--border);}
    	.vibe_search form input{border:none; background:none;flex:1}</style>';
    	return $html;
    }

    function enqueue_scripts(){
    	global $post;

    	wp_register_style('swipercss',plugins_url('../../assets/css/swiper-bundle.min.css',__FILE__));
		wp_register_style('swiperjs',plugins_url('../../assets/js/swiper-bundle.min.js',__FILE__),array(),VIBEBP_VERSION,true);

    	if(!empty($post) && has_shortcode('vibebp_carousel',$post->post_content)){
    		wp_enqueue_style('swipercss');
    		wp_enqueue_script('swiperjs');
    	}
    }

    function check_scope_setting($scope){
    	if(!empty(vibebp_get_setting('root_is_scope_for_sw','service_worker','general'))){
			$scope = '/';
			
		}

		return $scope;
    }

    function check_pwabuilder(){

    	$pwa = vibebp_get_setting('offline_page','service_worker');
    	if(!empty($pwa) && is_page($pwa)){
	    	?>
	    	<script type="module">
			   import 'https://cdn.jsdelivr.net/npm/@pwabuilder/pwaupdate';
			   const el = document.createElement('pwa-update');
			   document.body.appendChild(el);
			</script>
	    	<?php
	    }
    }

    function check_shortcode(){

    	global $post;

    	if(!empty($post)){
    		
    		if(has_shortcode($post->post_content,'vibebp_login') || bp_is_user()){
    			add_filter('vibebp_enqueue_login_script',function($x){return true;});
    		}
    		if(has_shortcode($post->post_content,'vibebp_profile') || bp_is_user()){
    			if(vibebp_get_setting('service_workers')){

    				//Please respect code privacy. Small code but lot of effort. Respect orignal.
    				

    				add_action('wp_head',function(){

    					$upload_dir = wp_get_upload_dir();
    					$path =$upload_dir['baseurl'];
    					$abspath =$upload_dir['basedir'];
    					$app_title =vibebp_get_setting('app_name','service_worker');
	    				if(empty($app_title)){
	    					$app_title = 'WPLMS';
	    				}

	    				$theme_color =vibebp_get_setting('theme_color','service_worker');
	    				if(empty($theme_color)){
	    					$theme_color = '#3ecf8e';
	    				}
    					//<!-- iOS  -->
    					if(file_exists($abspath.'/icon-72x72.png')){
    						echo '<link href="'.$upload_dir['baseurl'].'/icon-72x72.png" rel="apple-touch-icon">';
    					}else{
    						echo '<link href="'.plugins_url('../../assets/images/icons/icon-80x80.png',__FILE__).'" rel="apple-touch-icon">';
    					}

    					if(file_exists($abspath.'/icon-128x128.png')){
    						echo '<link href="'.$upload_dir['baseurl'].'/icon-128x128.png" rel="apple-touch-icon" sizes="120x120">';
    					}else{
    						echo '<link href="'.plugins_url('../../assets/images/icons/icon-120x120.png',__FILE__).'" rel="apple-touch-icon" sizes="120x120">';
    					}

    					if(file_exists($abspath.'/icon-152x152.png')){
    						echo '<link href="'.$upload_dir['baseurl'].'/icon-152x152.png" rel="apple-touch-icon" sizes="152x152">';
    					}else{
    						echo '<link href="'.plugins_url('../../assets/images/icons/icon-152x152.png',__FILE__).'" rel="apple-touch-icon" sizes="152x152">';
    					}

    					
	    				//<!-- Android  -->
	    				if(file_exists($abspath.'/icon-192x192.png')){
    						echo '<link href="'.$upload_dir['baseurl'].'/icon-192x192.png" rel="icon" sizes="192x192">';
    					}else{
    						echo '<link href="'.plugins_url('../../assets/images/icons/icon-192x192.png',__FILE__).'" rel="icon" sizes="192x192">';
    					}
    					if(file_exists($abspath.'/icon-128x128.png')){
    						echo '<link href="'.$upload_dir['baseurl'].'/icon-128x128.png" rel="icon" sizes="128x128">';
    					}else{
    						echo '<link href="'.plugins_url('../../assets/images/icons/icon-128x128.png',__FILE__).'" rel="icon" sizes="128x128">';
    					}

						//<!--  Microsoft  -->
						if(file_exists($abspath.'/icon-144x144.png')){
							echo '<meta name="msapplication-TileImage" content="'.$upload_dir['baseurl'].'/icon-144x144.png" />';
						}else{
							echo '<meta name="msapplication-TileImage" content="'.plugins_url('../../assets/images/icons/icon-144x144.png',__FILE__).'" />';
						}

						if(file_exists($abspath.'/icon-72x72.png')){
    						echo '<meta name="msapplication-square70x70logo" content="'.$upload_dir['baseurl'].'/icon-72x72.png" />';
    					}else{
    						echo '<meta name="msapplication-square70x70logo" content="'.plugins_url('../../assets/images/icons/icon-72x72.png',__FILE__).'" />';
    					}
						if(file_exists($abspath.'/icon-152x152.png')){
    						echo '<meta name="msapplication-square150x150logo" content="'.$upload_dir['baseurl'].'/icon-152x152.png" />';
    					}else{
    						echo '<meta name="msapplication-square150x150logo" content="'.plugins_url('../../assets/images/icons/icon-152x152.png',__FILE__).'" />';
    					}
    					if(file_exists($abspath.'/icon-384x384.png')){
    						echo '<meta name="msapplication-square310x310logo" content="'.$upload_dir['baseurl'].'/icon-384x384.png" />';
    					}else{
    						echo '<meta name="msapplication-square310x310logo" content="'.plugins_url('../../assets/images/icons/icon-384x384.png',__FILE__).'" />';
    					}


    					$splash_sizes = array(
    						'1242x2688'=>array(
    							'width'=>414,'height'=>896,'pixel-ratio'=>3,),
    						'828x1792'=>array(
    							'width'=>414,'height'=>896,'pixel-ratio'=>2),
    						'1125x2436'=>array(
    							'width'=>375,'height'=>812,'pixel-ratio'=>3),
    						'1242x2208'=>array(
    							'width'=>414,'height'=>736,'pixel-ratio'=>3),
    						'750x1334'=>array(
    							'width'=>375,'height'=>667,'pixel-ratio'=>2),
    						'2048x2732'=>array(
    							'width'=>1024,'height'=>1366,'pixel-ratio'=>2),
    						'1668x2224'=>array(
    							'width'=>834,'height'=>1112,'pixel-ratio'=>2),
    						'1536x2048'=>array(
    							'width'=>168,'height'=>1024,'pixel-ratio'=>2),
    					);
 
    					foreach($splash_sizes as $ext=>$size){
    						if(file_exists($path.'/splash-'.$ext.'.png')){
    							echo '<link rel="apple-touch-startup-image" media="(device-width: '.$size['width'].'px) and (device-height: '.$size['height'].'px) and (-webkit-device-pixel-ratio: '.$size['pixel-ratio'].')"  href="'.$upload_dir['baseurl'].'/splash-'.$ext.'.png">';
    						}else{

    							echo '<link rel="apple-touch-startup-image" media="(device-width: '.$size['width'].'px) and (device-height: '.$size['height'].'px) and (-webkit-device-pixel-ratio: '.$size['pixel-ratio'].')"  href="'.plugins_url('../../assets/images/splash/splash-'.$ext.'.png',__FILE__).'">';
    						}
    						
    					}

    					$upload_dir = wp_get_upload_dir();
    					$path = $upload_dir['baseurl'];
    					$serverpath = $upload_dir['basedir'];
    					if ( ! function_exists( 'get_home_path' ) ) {
				            include_once ABSPATH . '/wp-admin/includes/file.php';
				        }
    					$site_root = get_home_path();				          

    					if(file_exists($site_root.'/manifest.json')){

	    					echo '<link rel="manifest" href="'.untrailingslashit(function_exists('network_site_url') && is_multisite()?network_site_url():site_url()).'/manifest.json'.'"><meta name="theme-color" content="'.$theme_color.'"><meta name="theme-color" content="'.$theme_color.'"><meta name="msapplication-TileColor" content="'.$theme_color.'" /><meta name="msapplication-config" content="none"/><meta name="msapplication-navbutton-color" content="'.$theme_color.'">';
	    				}

    					echo '<meta name="apple-mobile-web-app-title" content="'.$app_title.'"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"><meta name="mobile-web-app-capable" content="yes">';
    					if(file_exists($serverpath.'/icon-192x192.png')){
    						echo '<link rel="apple-touch-icon" href="'.$upload_dir['baseurl'].'/icon-192x192.png">';
    					}else{
    						echo '<link rel="apple-touch-icon" href="'.plugins_url('../../assets/images/icons/icon-192x192.png',__FILE__).'">';
    					}
    					

						if(file_exists($serverpath.'/icon-144x144.png')){
    						echo '<link rel="apple-touch-icon" href="'.$upload_dir['baseurl'].'/icon-144x144.png">';
    					}else{
    						echo '<link rel="apple-touch-icon" href="'.plugins_url('../../assets/images/icons/icon-144x144.png',__FILE__).'">';
    					}
    				});

    				remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
				    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
				    remove_action( 'wp_print_styles', 'print_emoji_styles' );
				    remove_action( 'admin_print_styles', 'print_emoji_styles' );   
				    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
				    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );     
				    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

				    //To be decided -----***
				    //remove_action('wp_footer',array('Wplms_Wishlist_Init','append_span'));
				    //remove_action('wp_footer',array('Wplms_Wishlist_Init','append_collection'));

    				//add_action('wp_print_scripts', array($this,'remove_all_scripts'), 100);
					//add_action('wp_print_styles',  array($this,'remove_all_styles'), 100);
					// add_filter( 'style_loader_src', function ( $src ) {
					//     if ( strpos( $src, 'ver=' ) ){
					//         $src = remove_query_arg( 'ver', $src );
					//     }
					//     return $src;
					// }, 9999 );

					// add_filter( 'script_loader_src', function ( $src ) {
					//     if ( strpos( $src, 'ver=' ) )
					//         $src = remove_query_arg( 'ver', $src );
					//     return $src;
					// }, 9999 );
					// add_action('wp_footer',function(){
					// 	echo '<script>document.querySelector("#wpadminbar").remove();
					// 	document.querySelector("body").classList.remove("admin-bar");</script>';
					// },9999);
    			}

    			add_filter('vibebp_enqueue_profile_script',function($x){return true;});
    		}
    	}
    	
    }
    function vibebp_member_type_count($atts,$content){
    	if(!function_exists('bp_get_member_type_tax_name'))
    		return;
    	if(!empty($atts['member_type'])){
    		$term = get_term_by('slug',sanitize_text_field($atts['member_type']),bp_get_member_type_tax_name());
    		if(!empty($term) && !is_wp_error($term)){
    			return $term->count;
	    	}
	    	
    	}
    }
    function login_shortcode($atts,$content=null){

    	$defaults = array(
    		'class'=>'',
    		'button'=>'',
    	);
    	if(empty($content)){
    		$content = _x('Login','','vibebp');
    	}
    	$return = '';
    	
    	if(!empty($atts['button'])){
    		$return .='<span class="'.$atts['class'].' vibebp-login" type="popup">'.$content.'</span>';
    	}else{
    		$return .='<span class="vibebp-login" type="static">'.$content.'</span>';
    	}
    	return $return;
    }

    function profile($atts=array(),$content=null){

    	$return ='<div id="vibebp_member"></div>';

    	add_action('wp_footer',function(){

    		if ( ! function_exists( 'get_home_path' ) ) {
	            include_once ABSPATH . '/wp-admin/includes/file.php';
	        }
        
			$site_root = get_home_path();				          

    		if(file_exists($site_root.'/firebase-messaging-sw.js')){
    			if(vibebp_get_setting('service_workers')){
    				$scope = '';
    				//$url = vibebp_get_setting('offline_page','service_worker');
    				$site_url = site_url();
    				//$scope = str_replace($site_url.'/','',$url);

					if($_SERVER["DOCUMENT_ROOT"] != $site_root){
						$scope =  rtrim(str_replace($_SERVER["DOCUMENT_ROOT"],'', $site_root).$scope,'/').'/';
					}
					$scope = apply_filters('vibebp_service_worker_js_scope',$scope);

			    	?>
			    	<script>if ('serviceWorker' in navigator && window.vibebp.api.sw_enabled) {

							navigator.serviceWorker.getRegistrations().then(registrations => {

							    let check = registrations.findIndex((item)=>{
							    	return (item.active.scriptURL == window.vibebp.api.sw && item.active.state == 'activated')
							    });
							    let sw_first = window.vibebp.api.sw.split('?v=');
							    let index = registrations.findIndex((i) => {return i.active.scriptURL.indexOf(sw_first[0]) > -1 });
							    if(index > -1 && registrations[index].active.scriptURL.indexOf(sw_first[1]) == -1){
							    	//unregister previous version

							    	registrations[registrations.findIndex(i => i.active.scriptURL.indexOf(sw_first[0]) > -1)].unregister();
							    	check = -1;
							    }
								//service worker registration to be called only once.
								if(check == -1){
								  	
								  	navigator.serviceWorker.register(window.vibebp.api.sw,{
								  		scope:'<?php echo $scope; ?>'
								  	}).then(function(registration) {
								      console.log('Vibebp ServiceWorker registration successful with scope: ', registration.scope);
										
								    }, function(err) {
								      console.log('Vibebp ServiceWorker registration failed: ', err);
								    });
							  	}else{
							  		console.log('Vibebp Service worker already registered & active.');
							  	}
						  	});

							navigator.serviceWorker.ready.then(async function(registration) {
								

								if ('periodicSync' in registration) {
									const status = await navigator.permissions.query({
								      name: 'periodic-background-sync',
								    });
								    if (status.state === 'granted') {
								      	try {
									      	registration.periodicSync.register({
										  		tag: 'vibebp-post-data',         // default: ''
										    	minPeriod: 24 * 60 * 1000, // default: 0
										  	}).then(function(periodicSyncReg) {
										    // success
										  	}, function() {
										    // failure
										  	})
								      	}catch(e) {
									        console.error(`Periodic background sync failed:\n${e}`);
									    }
								  	}
									
							  	}
							});

						}</script>
			    	<?php
		    	}else{
		    		unlink($site_root.'/firebase-messaging-sw.js');
	    			$delete_sw = 1; 
	    			//WP_Filesystem_Direct::delete($site_root.'/firebase-messaging-sw.js');

		    		?>
		    		<script>
		    			navigator.serviceWorker.getRegistrations().then(function(registrations) {
						 	for(let registration of registrations) {
						  		registration.unregister();
						  		<?php if($delete_sw){ ?>
						  		setTimeout(function(){
					              window.location.replace(window.location.href);
					            }, 3000);
						  		<?php } ?>
							} 
						});
						if ('caches' in window) {
						    caches.keys()
						      .then(function(keyList) {
						          return Promise.all(keyList.map(function(key) {
						              return caches.delete(key);
						          }));
						      })
						}
		    		</script>
		    		<?php
	    		}
	    	} 
	    },99);
		//wp_enqueue_script('jquery');
		if(function_exists('bp_is_active') && bp_is_active('groups')){
			add_action('wp_footer',function(){ ?><div class="vibebp_group_popups"></div><?php });
		}
    	return $return;
    }


    function remove_all_scripts() {

		if(!vibebp_get_setting('service_workers'))
			return;

	    global $wp_scripts;
	    $queue = $wp_scripts->queue;
	    
	    //$wp_scripts->queue = vibebp_get_pwa_scripts(1);
	}


	function remove_all_styles() {
		if(!vibebp_get_setting('service_workers'))
			return;
	    global $wp_styles;

	    //$wp_styles->queue = vibebp_get_pwa_styles(1);

	}


    static public function vibebp_grid($atts){

    	$attributes_string = '';
    	$width = '268';
    	if(!empty($atts['gutter'])){
    		$attributes_string .= 'data-gutter="'.$atts['gutter'].'" ';
    	}
    	
    	$grid_control = [];
    	if(!empty($atts['grid_control'])){
    		
    		$grid_control = json_decode($atts['grid_control'],true);
    	
    		$atts['grid_number']  = count($grid_control['grid']);
    	}



    	$randclass = wp_generate_password(8,false,false);
    	$output .= '<div class="vibebp_grid '.$randclass.'" '.$attributes_string.'>';
    	if(!isset($atts['post_ids']) || strlen($atts['post_ids']) < 2){
        
	        if(isset($atts['term']) && isset($atts['taxonomy']) && $atts['term'] !='nothing_selected'){
	            
	            if(!empty($atts['taxonomy'])){ 
	                
	                if(strpos($atts['term'], ',') === false){
	                    $check=term_exists($atts['term'], $atts['taxonomy']);
	                    if($atts['term'] !='nothing_selected'){    
	                        if ($check == 0 || $check == null || !$check) {
	                            $error = new VibeAppErrors();
	                            $output .= $error->get_error('term_taxonomy_mismatch');
	                            $output .='</div>';
	                            return $output;
	                       } 
	                    }
	                }    
	                $check=is_object_in_taxonomy($atts['post_type'], $atts['taxonomy']);
	                if ($check == 0 || $check == null || !$check) {
	                    $error = new VibeAppErrors();
	                    $output .= $error->get_error('term_postype_mismatch');
	                    $output .='</div>';
	                    return $output;
	               }

	                $terms = $atts['term'];
	                if(strpos($terms,',') !== false){
	                    $terms = explode(',',$atts['term']);
	                } 
	            }

	            
	            $query_args=array( 'post_type' => $atts['post_type'],'posts_per_page' => $atts['grid_number']);
	            if(!empty($atts['taxonomy'])){ 
	              $query_args['tax_query'] = array(
	                  'relation' => 'AND',
	                  array(
	                      'taxonomy' => $atts['taxonomy'],
	                      'field'    => 'slug',
	                      'terms'    => $terms,
	                  ),
	              );
	            }
	        }else{
	           $query_args=array('post_type'=>$atts['post_type'], 'posts_per_page' => $atts['grid_number']);
	        }
	        
	        if($atts['post_type'] == 'course' && isset($atts['course_style'])){
	            switch($atts['course_style']){
	                case 'popular':
	                  $query_args['orderby'] = 'meta_value_num';
	                  $query_args['meta_key'] = 'vibe_students';
	                break;
	                case 'featured':
	                  if(empty($query_args['meta_query'])){
	                    $query_args['meta_query'] = array(
	                              array(
	                                  'key'     => 'featured',
	                                  'value'   => 1,
	                                  'compare' => '>='
	                              )
	                          );
	                  }else{
	                    $query_args['meta_query'][] = array(
	                                  'key'     => 'featured',
	                                  'value'   => 1,
	                                  'compare' => '>='
	                          );
	                  }
	                break;
	                case 'rated':
	                  $query_args['orderby'] = 'meta_value_num';
	                  $query_args['meta_key'] = 'average_rating';
	                break;
	                case 'reviews':
	                  $query_args['orderby'] = 'comment_count';
	                break;
	                case 'start_date':
	                  $query_args['orderby'] = 'meta_value';
	                  $query_args['meta_key'] = 'vibe_start_date';
	                  $query_args['meta_type'] = 'DATE';
	                  $query_args['order'] = 'ASC';
	                  $today = date('Y-m-d');
	                  if(empty($query_args['meta_query'])){
	                    $query_args['meta_query'] = array(
	                              array(
	                                  'key'     => 'vibe_start_date',
	                                  'value'   => $today,
	                                  'compare' => '>='
	                              )
	                          );
	                  }else{
	                    $query_args['meta_query'][] = array(
	                                  'key'     => 'vibe_start_date',
	                                  'value'   => $today,
	                                  'compare' => '>='
	                          );
	                  }
	                  
	                break;
	                case 'expired_start_date':
	                  $query_args['orderby'] = 'meta_value';
	                  $query_args['meta_key'] = 'vibe_start_date';
	                  $query_args['meta_type'] = 'DATE';
	                  $query_args['order'] = 'ASC';
	                  $today = date('Y-m-d');
	                  if(empty($query_args['meta_query'])){
	                    $query_args['meta_query'] = array(
	                              array(
	                                  'key'     => 'vibe_start_date',
	                                  'value'   => $today,
	                                  'compare' => '<'
	                              )
	                          );
	                  }else{
	                    $query_args['meta_query'][] = array(
	                                  'key'     => 'vibe_start_date',
	                                  'value'   => $today,
	                                  'compare' => '<'
	                          );
	                  }
	                  
	                break;
	                case 'random':
	                   $query_args['orderby'] = 'rand';
	                break;
	                case 'free':
	                 if(empty($query_args['meta_query'])){
	                  $query_args['meta_query'] =  array(
	                      array(
	                        'key'     => 'vibe_course_free',
	                        'value'   => 'S',
	                        'compare' => '=',
	                      ),
	                    );
	                }else{
	                  $query_args['meta_query'][] =  array(
	                        'key'     => 'vibe_course_free',
	                        'value'   => 'S',
	                        'compare' => '=',
	                    );
	                }
	                break;
	                default:
	                  $query_args['orderby'] = '';
	            }
	            if(empty($query_args['order']))
	              $query_args['order'] = 'DESC';

	            
	        }

	        if(!empty($_GET['search']) && $query_args['post_type'] == esc_attr($_GET['post_type'])){
	        	$query_args['s']=esc_html($_GET['search']);
	        }
	        $query_args =  apply_filters('vibebp_elementor_filters',$query_args);

	        $the_query = new WP_Query($query_args);

        }else{

          $cus_posts_ids=explode(",",$atts['post_ids']);
        	$query_args=array( 'post_type' => $atts['post_type'], 'post__in' => $cus_posts_ids , 'orderby' => 'post__in','posts_per_page'=>count($cus_posts_ids)); 
        	$query_args =  apply_filters('vibebp_elementor_filters',$query_args);
        	$the_query = new WP_Query($query_args);
        }
        if($atts['column_width'] < 311)
             $cols = 'small';
         
        if(($atts['column_width'] >= 311) && ($atts['column_width'] < 460))    
             $cols='medium';
         
        if(($atts['column_width'] >= 460) && ($atts['column_width'] < 769))    
             $cols='big';
         
        if($atts['column_width'] >= 769)    
             $cols='full';
         
        $style ='';
       	if( $the_query->have_posts() ) {
    		$style .= '<style>.vibebp_grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; } .grid_item { border: 1px solid var(--border); padding: 1rem; }.'.$randclass.' {
						    width:100%;
						    display:grid;';




			if(!empty($atts['column_align_verticle'])){
				$style .= 'align-items:'.$atts['column_align_verticle'].';';
			}
			if(!empty($atts['column_align_horizontal'])){
				$style .= 'justify-content:'.$atts['column_align_horizontal'].';';
			}
				    
			$style .=	'grid-gap:'.$atts['gutter'].'px;
						}</style>';
	        //$row_logic = $atts['carousel_rows'];
	       // $row_logic = 0; 
			$i = 0;
	        while ( $the_query->have_posts() ) : $the_query->the_post();
	        global $post;
	        $style_string  ='';
	        if(!empty($grid_control['grid']) && !empty($grid_control['grid'][$i])){
	        	 $style_string .= 'grid-column:'.$grid_control['grid'][$i]['col'].';';
	        	 $style_string .= 'grid-row:'.$grid_control['grid'][$i]['row'].';';
	        }
	        if(!empty($atts['grid_width'])){
	        	$width = $atts['grid_width'];
	        	//$style_string .= 'width:'.$atts['grid_width'].'px;';
	        }
	       	$output .= '<div class="grid_item " style="'.$style_string.'">
	       	'.vibebp_render_block_from_style($post,$atts['featured_style'],$cols,$atts['carousel_excerpt_length']).'
	       	</div>';
	       	$i++;
	    	endwhile;
			
       	}else{
       		$output .= '<div class="vbp_message">'._x('No posts Found !','','vibebp').'</div>';
       	}
       	$output .= '</div>'.$style;
       	wp_reset_postdata();
    	return $output;
    }

    function vibeapp_carousel($atts,$content=null){
    	

    	$output = '';
    	$posts=[];

    	add_filter('excerpt_more','__return_false');
    	add_filter( 'excerpt_length', function($l){return 20;}, 999 );
    	$args='';
    	if(!empty($atts['args'])){
    		$args = json_decode(base64_decode($atts['args']),ARRAY_A);	
    	}
    	
    	
    	switch($atts['type']){
    		case 'taxonomy':
    			$posts = get_terms($args);
    			if(empty($atts['featured_style'])){$atts['featured_style']='term_block';}
    		break;
    		case 'members':
    			$run = bp_core_get_users( $args);

	    		if( $run['total'] ){
	    			foreach($run['users'] as $key=>$user){
	    				$posts[]=$user;
	    			}
	    		}
	    		if(empty($atts['featured_style'])){$atts['featured_style']='member_block';}
    		break;
    		case 'groups':
    			if(function_exists('groups_get_groups')){
	    			$run = groups_get_groups( $args);

		    		if( $run['total'] ){
		    			foreach($run['groups'] as $key=>$user){
		    				$posts[]=$user;
		    			}
		    		}
		    	}
    			if(empty($atts['featured_style'])){$atts['featured_style']='group_block';}
    		break;
    		case 'post_type':
    			$args = apply_filters('vibebp_vibe_carousel_args',$args);
    			$query = new WP_Query($args);
    			if($query->have_posts()){
    				while($query->have_posts()){
    					$query->the_post();
    					$posts[]=$query->post;
    				}
    			}
    			wp_reset_postdata();
    			if(empty($atts['featured_style'])){$atts['featured_style']='post_block';}
    		break;
    		case 'slides':
    			$posts=json_decode(base64_decode($atts['slides']),ARRAY_A);
    			if(empty($atts['featured_style'])){
    				$atts['featured_style']='slide';
    			};
    		break;
    	}
    	
    	
    	wp_enqueue_style('vibebp-swiper',plugins_url('../../assets/css/swiper-bundle.min.css',__FILE__));
		wp_enqueue_script('vibebp-swiper',plugins_url('../../assets/js/swiper-bundle.min.js',__FILE__),array(),VIBEBP_VERSION,true);
    	ob_start();
    	?>
    	<div id="carousel_<?php echo $atts['id']; ?>" class="swiper-container swiper_style_<?php echo $atts['carousel_style']; ?> swiper_<?php echo $atts['id'].' '.(!empty($atts['show_controlnav'])?'with_pagination':'').' '.(empty($atts['show_controls'])?'':'with_navigation').' '.(empty($atts['scrollbar'])?'':'with_scrollbar');?> ">
		  <div class="swiper-wrapper">
		  	<?php
		  	if(!empty($posts)){
		  		foreach($posts as $k=>$post){
		  			?>
		  			<div class="swiper-slide <?php echo $atts['type'].'_'.$k; ?>">
	  				<?php 
  					vibebp_featured_style($post,$atts['featured_style']);	
  					?>
		  			</div>
		  			<?php
		  		}
		  	}
		  	?>
		  </div>
		  <?php 
		  	if(!empty($atts['show_controlnav'])){
		  		?>
		  		<div class="swiper-pagination"></div>
		  		<?php
		  	}
		   ?>
		  <?php 
		  	if(!empty($atts['show_controls'])){
		  		?>
		  		<div class="swiper-button-prev"></div>
		  		<div class="swiper-button-next"></div>
		  		<?php		
		  	}
		   ?>
		   <?php 
		  	if(!empty($atts['scrollbar'])){
		  		?>
		  		<div class="swiper-scrollbar"></div>
		  		<?php		
		  	}
		  	$atts['space'] = empty($atts['space'])?40:intval($atts['space']);
		  	$atts['columns'] = empty($atts['columns'])?1:intval($atts['columns']);
	  		
	  		
		  	$args = [

			  	'speed'	=>400,
			  	'autoHeight' =>empty($atts['autoheight'])?true: false,
			  	'direction'=>empty($atts['vertical'])?'horizontal': 'vertical',
			  	'effect'=> (empty($atts['effect']) || is_numeric($atts['effect']))?'slide':$atts['effect'],
			  	'watchOverflow'=> true,
			  	'navigation'=> [
                    'nextEl'=> '.swiper-button-next',
                    'prevEl'=>'.swiper-button-prev',
                    'disabledClass'=> 'disabled_swiper_button'
                 ],
			  	'initialSlide'=>empty($atts['starting_slide'])?0:$atts['starting_slide'],
			  	'grid'=>[
			  		'rows'=>$atts['rows'],
			  	],
			  	'slidesPerView'=>$atts['columns'],
			  	'spaceBetween'=>$atts['space'],
			  	'navigation'=> [
			    	'nextEl'=> '.swiper-button-next',
			    	'prevEl'=> '.swiper-button-prev',
			    	'disabledClass'=> 'disabled_swiper_button'
			  	],
			  	'pagination'=>[
			  		 'el'=> ".swiper-pagination",
			  		 'clickable'=> true
			  	],
			  	'loop'=>empty($atts['loop'])?false: true,
			  	'show_controlnav'=>empty($atts['show_controlnav'])?false: true,
			  	'show_controls'=>empty($atts['show_controls'])?false: true,
			  	'auto_slide'=>empty($atts['auto_slide'])?false: true,
			  	'vertical'=>empty($atts['vertical'])?false: true,
			  	'scrollbar'=>[
		          'el'=> ".swiper-scrollbar",
		          'hide'=> true,
		        ]
				
		  	];

		  	if(!empty($atts['columns']) && $atts['columns'] > 1){
		  		
		  		$args['breakpoints'] = [
		  			'1140'=>[
						'slidesPerView'=>$atts['columns'],
			  			'spaceBetween'=>$atts['space']
					]
				];

				if($atts['columns'] >  2){
					//480px and above
					$args['breakpoints']['768']=[
						'slidesPerView'=>2,
						'spaceBetween'=> 10,
					];
				}

				if($atts['columns'] >=  4){
					//960 px and above
					$args['breakpoints']['960']=[
						'slidesPerView'=>($atts['columns'] - 1),
						'spaceBetween'=> (($atts['space'] > 20 )?$atts['space'] - 10:$atts['space'])
					];
				}

				$args['breakpoints']['320']=[
					'slidesPerView'=>1,
					'spaceBetween'=>0,
				];
		  	}

		  	if($atts['effect'] == 'coverflow'){
		  		$args['centeredSlides']= true;
		  		$args['initialSlide']=0;
		        $args['coverflowEffect']=[
			          'rotate'=> 50,
			          'stretch'=> 0,
			          'depth'=> 100,
			          'modifier'=> 1,
			          'slideShadows'=> true,
			        ];
		  	}
		  	if(!empty($args['extras'])){
		  		$extras = json_decode($atts['extras']);
		  		if(is_array($extras)){
		  			$args = array_merge($args,$extras);
		  		}
		  	}
		  	//$args = apply_filters('vibebp_carousel_args',$args,$atts);

		  	if($args['slidesPerView'] == 1){
		  		unset($args['breakpoints']);
		  		$args['initialSlide']=0;
		  	}
		  	$this->args = $args;

		   ?>
		  
		  
		</div>
		<script>
			document.addEventListener('DOMContentLoaded',function(){
				window.swiper_args_<?php echo $atts['id'];?> = <?php echo json_encode($args); ?>;
				window.swiper_<?php echo $atts['id'];?> = new Swiper('.swiper_<?php echo $atts['id'];?>', window.swiper_args_<?php echo $atts['id'];?> );
			});
			document.addEventListener('VibeBP_Editor_Content',function(){
				window.swiper_args_<?php echo $atts['id'];?> = <?php echo json_encode($args); ?>;
				window.swiper_<?php echo $atts['id'];?> = new Swiper('.swiper_<?php echo $atts['id'];?>', window.swiper_args_<?php echo $atts['id'];?> );
			});
		</script><style>
		<?php if(!empty($atts['autoheight'])){ ?>
		#carousel_<?php echo $atts['id'];?> .swiper-slide{height: auto !important;}#carousel_<?php echo $atts['id'];?> .swiper-slide > *{height: 100%}
    	<?php
    	} ?>.swiper-button-next:after, .swiper-button-prev:after{font-size:75% !important;}
    	#carousel_<?php echo $atts['id'];?>{position:relative;overflow:hidden;}</style><?php
    	do_action('vibebp_carousel_styles_scripts',$atts['featured_style'],$atts['id']);
    	$output = ob_get_clean();



    	return $output;
    }

    function vibebp_registration_form($atts, $content = null){
        extract(shortcode_atts(array(
                    'name'   => '',
                    'field_meta'=>0,
                ), $atts));

        if(empty($name) && function_exists('xprofile_get_field'))
            return;
        wp_dequeue_script('wplms-registration-forms');
        wp_enqueue_script('vibebp-registration-forms',plugins_url('../../assets/js/registration_forms.js',__FILE__),array(),VIBEBP_VERSION,true);
     	wp_localize_script('vibebp-registration-forms','vibebp_reg_forms',apply_filters('vibebp_reg_forms',
     		array(
     			'recaptcha_key'=>vibebp_get_setting('google_captcha_public_key','general','login'),
		 		'translations'=>array(
		 			'captcha_mismatch'=>_x('Captcha Mismatch','','vibebp'),
		 		),
		 	)
     	));
        $return = '';
        $forms = get_option('vibebp_registration_forms');
        global $bp;
        $types = bp_get_member_types(array(),'objects');
        $member_types = [];
        if(!empty($types)){
            foreach($types as $type => $labels){
                $member_types[]=array('id'=>$type,'sname'=>$labels->labels['name']);
            }
        }


        if(!empty($forms[$name])){
        	$fields =[];
        	if(!empty($forms[$name]['fields'])){
        		$fields = $forms[$name]['fields'];
        	}

            $settings = [];
            if(!empty($forms[$name]['settings'])){
                $settings = $forms[$name]['settings'];
            }
            
            

            /*
            STANDARD FIELDS
            */

          
            $return = '<div class="vibebp_registration_form" data-form-name="'.$name.'"><form action="/" name="signup_form" id="signup_form" class="standard-form" method="post" enctype="multipart/form-data">';
            
            
            if(!empty($settings['show_group_label'])){
            	$return .='<fieldset class="account_info">';
             	$return .='<legend>'.__('Account Information','vibebp').'</legend>';
            } 
    		
            if(empty($settings['hide_username'])){
                $return .='<div class="vibebp_registration_field"><label>'.__('Username','vibebp').'</label>'.'<input type="text" name="signup_username" placeholder="'.__('Login Username','vibebp').'" required></div>';
            }

            $return .='<div class="vibebp_registration_field">'.'<label>'.__('Email','vibebp').'</label>'.'<input type="email" name="signup_email" placeholder="'.__('Email','vibebp').'" required></div>';

            $return .='<div class="vibebp_registration_field_wrap"><div class="password_fields"><div class="vibebp_registration_field">'.'<label '.(empty($settings['password_meter'])?:'for="signup_password"').'>'.__('Password','vibebp').'</label>'.'<input type="password" '.(empty($settings['password_meter'])?'':'id="signup_password" class="form_field"').' name="signup_password" placeholder="'.__('Password','vibebp').'" autocomplete="new-password"></div>';
            $return .='<div class="vibebp_registration_field">'.'<label>'.__('Confirm Password','vibebp').'</label>'.'<input type="password" name="confirm_password" placeholder="'.__('Password','vibebp').'" autocomplete="new-password"></div></div><div class="password_terms_wrapper"><label>'.__('Password Requirements','vibebp').'</label>
            <div class="password_terms">
            <p><span class="vicon vicon-close"></span>'._x('Minimum 10 characters','password meter','vibebp').'</p>
            <p><span class="vicon vicon-close"></span>'._x('One uppercase character','password meter','vibebp').'</p>
            <p><span class="vicon vicon-close"></span>'._x('One Numerical character','password meter','vibebp').'</p>
            <p><span class="vicon vicon-close"></span>'._x('One Special character','password meter','vibebp').'</p>
            <p><span class="vicon vicon-close"></span>'._x('Must Match','password meter','vibebp').'</p>
            </div></div>';

            add_action('wp_footer',[$this,'password_meter']);
            if(!empty($member_types) && count($member_types) && !empty($settings['member_type']) && $settings['member_type'] == 'enable_user_member_types_select'){
                $return .= '<div class="vibebp_registration_field">'.'<label>'.__('Member type','vibebp').'</label><select name="member_type" id="member_type"><option value="">'._x('None','','vibebp').'</option>';

                foreach ($member_types as $member_type) {
                    $return .=  '<option value="'.$member_type['id'].'">'.$member_type['sname'].'</option>';
                }
                $return .= '</select></div>';
            }


            if(function_exists('bp_is_active') && bp_is_active('groups') && class_exists('BP_Groups_Group')){
                $vgroups = BP_Groups_Group::get(array(
                        'type'=>'alphabetical',
                        'per_page'=>999
                        ));

                $vgroups = apply_filters('wplms_custom_reg_form_groups_select_reg_form',$vgroups);
                $all_groups = array();
                foreach ($vgroups['groups'] as $value) {
                     $all_groups[$value->id] = $value->name;
                }

                if(!empty($all_groups ) && count($all_groups ) && !empty($settings['wplms_user_bp_group']) && is_array($settings['wplms_user_bp_group']) ){

                    if($settings['wplms_user_bp_group']===array('enable_user_select_group')){
                        $return .= '<div class="vibebp_registration_field"><label class="field_name">'.__('Group','vibebp').'</label><select name="wplms_user_bp_group" id="wplms_user_bp_group"><option value="">'._x('None','','vibebp').'</option>';
                        foreach ($all_groups as $key => $group) {
                            $return .= '<option value="'.$key.'">'.$group.'</option>';
                        }
                        $return .= '</select></div>';
                    }elseif(count($settings['wplms_user_bp_group'])>1){
                        $return .= '<div class="vibebp_registration_field"><label class="field_name">'.__('Group','vibebp').'</label><select name="wplms_user_bp_group" id="wplms_user_bp_group"><option value="">'._x('None','registration','vibebp').'</option>';
                       foreach($settings['wplms_user_bp_group'] as $group_id){
                            if(array_key_exists($group_id, $all_groups)){
                                 $return .= '<option value="'.$group_id.'">'.$all_groups[$group_id].'</option>';
                            }
                       }
                        $return .= '</select></div>';
                    }
                   
                }
            }
            if(!empty($settings['show_group_label'])){
            	$return .='</fieldset>';
            }
            if ( bp_is_active( 'xprofile' ) ) : 
                if ( bp_has_profile( array( 'fetch_field_data' => false,'member_type'=>'' ) ) ) : 
                    while ( bp_profile_groups() ) : bp_the_profile_group(); 

                    	$group_label_fieldset_flag=0;
                        $return_fields = '';

                        $group_field_num=0;
                        while ( bp_profile_fields() ) : bp_the_profile_field();
                        global $field;
                        if(empty($group_field_num) && empty($group_label_fieldset_flag)){
                        	if(!empty($settings['show_group_label'])){
	                        	$group_label_fieldset_flag=1;
	                        	$return .='<fieldset>';
	             				$return .='<legend>'.bp_get_the_profile_group_name().'<span>'.do_shortcode(bp_get_the_profile_group_description()).'</span></legend>';
	                        }
                        }
                        $fname = str_replace(' ','_',$field->name);
                        if(is_array($fields) && in_array($fname,$fields)){

                            $return_fields .='<div class="vibebp_registration_field vibebp_field_type_'.bp_get_the_profile_field_type().'">';
                            $field_type = bp_xprofile_create_field_type( bp_get_the_profile_field_type() );
                            ob_start();
                            ?><div <?php bp_field_css_class( 'bp-profile-field' ); ?>>
                            <?php
                            $field_type->edit_field_html();

                            if(!empty($field_meta)){

                                if ( bp_get_the_profile_field_description()){
                                     //now buddypress already show descption below the field since 2.9 
                                    if(function_exists('version_compare') && !empty($bp->version) && version_compare($bp->version, '2.9.0','<')){
                                        
                                        echo '<p class="description">'.bp_the_profile_field_description().'</p>';
                                    }
                                }
                            }
                            ?></div><?php
                            $return_fields .=ob_get_clean();
                            $return_fields .='</div>';
                        }
                        endwhile;
                        $return .= $return_fields;
                        if(!empty($settings['show_group_label']) && !empty($group_label_fieldset_flag)){
			            	$return .='</fieldset>';
			            }
                    endwhile;
                endif;
            endif; 
           
            
            $form_settings = apply_filters('vibebp_registration_form_settings',array(
                    'password_meter'         => __('Show password meter','vibebp'),
                    'show_group_label'       => __('Show Field group labels','vibebp'),
                    'google_captcha'         => __('Google Captcha','vibebp'),
                    'custom_activation_mail' => __('Custom activation email','vibebp'),
            ));
           
            foreach($form_settings as $key=>$setting){
                if(!empty($settings[$key])){
                    if(!empty($settings['google_captcha']) && $key == 'google_captcha'){
                        
                        $google_captcha_public_key = vibebp_get_setting('google_captcha_public_key','general','login');
                        if(!empty($google_captcha_public_key)){
                        	if ( ! wp_script_is( 'google-recaptchav3', 'enqueued' ) ) {
	                            wp_enqueue_script('google-recaptchav3','https://www.google.com/recaptcha/api.js?render='.vibebp_get_setting('google_captcha_public_key','general','login'));
	                        }
                        }
                        
                    }
                    if( $key !== 'mailchimp_list' )
                        $return .= '<input type="hidden" name="'.$key.'" value="'.$settings[$key].'"/>';
                }
            }
            
            //SETTINGS
            
            ob_start();
            do_action('wplms_before_registration_form',$name);
            wp_nonce_field( 'bp_new_signup' ,'bp_new_signup');
            $return .= ob_get_clean();

            $return .='<div class="vibebp_registration_action">'.apply_filters('vibebp_registration_form_submit_button','<a href="#" class="vibebp_submit_registration_form button">'.__('Register','vibebp').'</a>').'</div>';
            $return .= '</ul></form></div><style>.vibebp_registration_action{flex:1 0 100%;min-width:100%;}</style>';
        }
        return $return;
    }

	function password_meter(){
		?>
		<script>
			document.addEventListener('DOMContentLoaded',function(){
				
			if(document.querySelector('input[name="signup_password"]')){
				
				document.querySelectorAll('input[name="signup_password"]').forEach(function(el){
					
					let confirmEl = el.parentElement.parentElement.querySelector('input[name="confirm_password"]');
					let parentEl = el.parentElement.parentElement.parentElement;
					el.addEventListener('keyup',function(e){

						checkStrength(e.target.value,parentEl);
						if(el.value.localeCompare(confirmEl.value,undefined, { sensitivity: 'base' })){
							if(parentEl.querySelector('.password_terms > p:nth-child(5)>.vicon-check')){
					        	parentEl.querySelector('.password_terms > p:nth-child(5)>.vicon').classList.toggle('vicon-close');
					        	parentEl.querySelector('.password_terms > p:nth-child(5)>.vicon').classList.toggle('vicon-check');
				        	}
						}else{
							if(parentEl.querySelector('.password_terms > p:nth-child(5)>.vicon-close')){
					        	parentEl.querySelector('.password_terms > p:nth-child(5)>.vicon').classList.toggle('vicon-close');
					        	parentEl.querySelector('.password_terms > p:nth-child(5)>.vicon').classList.toggle('vicon-check');
				        	}
						}
					});
					confirmEl.addEventListener('keyup',function(e){
						console.log(el.value.localeCompare(confirmEl.value,undefined, { sensitivity: 'base' }) == 0);
						if(el.value.localeCompare(confirmEl.value,undefined, { sensitivity: 'base' })){
							if(parentEl.querySelector('.password_terms > p:nth-child(5)>.vicon-check')){
					        	parentEl.querySelector('.password_terms > p:nth-child(5)>.vicon').classList.toggle('vicon-close');
					        	parentEl.querySelector('.password_terms > p:nth-child(5)>.vicon').classList.toggle('vicon-check');
				        	}
						}else{
							if(parentEl.querySelector('.password_terms > p:nth-child(5)>.vicon-close')){
					        	parentEl.querySelector('.password_terms > p:nth-child(5)>.vicon').classList.toggle('vicon-close');
					        	parentEl.querySelector('.password_terms > p:nth-child(5)>.vicon').classList.toggle('vicon-check');
				        	}
							
						}
					});
				});
			}
			const checkStrength = function(password,el) {
				
		        if (password.length < 10) {
		        	if(el.querySelector('.password_terms > p:nth-child(1)>.vicon-check')){
			        	el.querySelector('.password_terms > p:nth-child(1)>.vicon').classList.toggle('vicon-close');
			        	el.querySelector('.password_terms > p:nth-child(1)>.vicon').classList.toggle('vicon-check');
		        	}
		        }else{
		        	if(el.querySelector('.password_terms > p:nth-child(1)>.vicon-close')){
		        		el.querySelector('.password_terms > p:nth-child(1)>.vicon').classList.toggle('vicon-close');
			        	el.querySelector('.password_terms > p:nth-child(1)>.vicon').classList.toggle('vicon-check');
		        	}
		        }
		        
		        if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)){
		        	if(el.querySelector('.password_terms > p:nth-child(2)>.vicon-close')){
		        		el.querySelector('.password_terms > p:nth-child(2)>.vicon').classList.toggle('vicon-close');
			        	el.querySelector('.password_terms > p:nth-child(2)>.vicon').classList.toggle('vicon-check');
		        	}
		        }else{
		        	if(el.querySelector('.password_terms > p:nth-child(2)>.vicon-check')){
			        	el.querySelector('.password_terms > p:nth-child(2)>.vicon').classList.toggle('vicon-close');
			        	el.querySelector('.password_terms > p:nth-child(2)>.vicon').classList.toggle('vicon-check');
		        	}
		        }
		        
		        if (password.match(/([0-9])/)){
		        	if(el.querySelector('.password_terms > p:nth-child(3)>.vicon-close')){
		        		el.querySelector('.password_terms > p:nth-child(3)>.vicon').classList.toggle('vicon-close');
			        	el.querySelector('.password_terms > p:nth-child(3)>.vicon').classList.toggle('vicon-check');
		        	}
		        }else{
		        	if(el.querySelector('.password_terms > p:nth-child(3)>.vicon-check')){
			        	el.querySelector('.password_terms > p:nth-child(3)>.vicon').classList.toggle('vicon-close');
			        	el.querySelector('.password_terms > p:nth-child(3)>.vicon').classList.toggle('vicon-check');
		        	}
		        }
		        
		        if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)){
		        	if(el.querySelector('.password_terms > p:nth-child(4)>.vicon-close')){
		        		el.querySelector('.password_terms > p:nth-child(4)>.vicon').classList.toggle('vicon-close');
			        	el.querySelector('.password_terms > p:nth-child(4)>.vicon').classList.toggle('vicon-check');
		        	}
		        }else{
		        	if(el.querySelector('.password_terms > p:nth-child(4)>.vicon-check')){
			        	el.querySelector('.password_terms > p:nth-child(4)>.vicon').classList.toggle('vicon-close');
			        	el.querySelector('.password_terms > p:nth-child(4)>.vicon').classList.toggle('vicon-check');
		        	}
		        }
	      	}
	      });
		</script>
		<?php
	}
    function profile_field($atts,$content=null){
    	$return = '';

    	if(!empty($atts['user_id'])){
    		$user_id = $atts['user_id'];
    	}else{
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
    	}
    	

		if(!empty($atts['name'])){
			$id = xprofile_get_field_id_from_name($atts['name']);
		}

		if(!empty($atts['id'])){
			$id = $atts['id'];
		}
		$field = xprofile_get_field( $id );
		$value = xprofile_get_field_data( $id, $user_id);
		if(!empty($atts['flag']) && is_string($value)){
			if(empty($value)){$value='us';}
			$value = '<img src="https://flagcdn.com/'.(strlen($value) < 2?'us':strtolower($value)).'.svg" width="'.(empty($atts['size'])?'32':$atts['size']).'" alt="country flag">';
		}
		 
		$return = vibebp_process_profile_field_data($value,$field,$user_id);
		
		if(!empty($field) && !empty($field->type) &&  $field->type == 'gallery' && null !== json_decode($return)){
			$html = '<div class="vibebp_media_gallery">';

			$return = json_decode($return,true);
                foreach ($return as $key => $image) {
                   
                    if(!empty($image['url'])){
                        if(!empty($image['id'])){

                            if($image['type'] == 'image'){
                                if(!empty($field->full_image)){
                                    $html .= '<div class="full_image"><img src="'.$image['url'].'"></div>';    
                                }else{
                                    $html .= '<div class="vibebp_media_gallery_image" data-url="'.$image['url'].'"><img src="'.wp_get_attachment_image_url($image['id'],'thumbnail').'"></div>';        
                                }
                                $html .= '<div class="vibebp_media_gallery_image" data-url="'.$image['url'].'"><img src="'.wp_get_attachment_image_url($image['id'],'thumbnail').'"></div>';    
                            }else{
                                $html .= '<div class="vibebp_media_gallery_attachment"><a href="'.$image['url'].'" target="_blank">'.$image['name'].'</a></div>'; 
                            }
                            
                        }else{
                            $html .= '<div class="vibebp_media_gallery_image"><img src="'.$image['url'].'"></div>';
                        }
                        
                    }
                }
                $html .= '</div><style>.gallery.field_type_gallery,.vibebp_media_gallery{width:100%;}.vibebp_media_gallery { display: grid; grid-template-columns: repeat( auto-fit, minmax(120px,1fr) ); grid-gap: 0; }.vibebp_media_gallery_image{cursor:pointer;}.vibebp_gallery_popup { display: flex; position: fixed; z-index: 99999; background: rgba(0,0,0,0.4); left: 0; top: 0; width: 100vw; height: 100vh; align-items: center; justify-content: center; } .vibebp_gallery_popup > * { max-width: 80vw; max-height: 80vh; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 10px rgba(0,0,0,0.4); }</style>';
                ob_start();
                ?>
                <script>
                    window.addEventListener('load',function(){
                        (function() {  
                            document.querySelectorAll('.field_<?php echo $field->id;?> .vibebp_media_gallery .vibebp_media_gallery_image').forEach(function(el){
                                el.addEventListener('click',function(ee){
                                    let src = '';
                                    if (event.target.nodeName == 'IMG'){
                                        src = ee.target.getAttribute('src');
                                    }else{
                                        src = ee.target.querySelector('img').getAttribute('src');
                                    }
                                    
                                    if(src.length){
                                        let popup=document.querySelector('.vibebp_gallery_popup');
                                        if(popup){
                                            popup.parentNode.removeChild(popup);    
                                        }
                                        

                                        const element = document.createElement("div");
                                        element.classList.add('vibebp_gallery_popup');   
                                        element.innerHTML = '<div class="vibebp_gallery_popup_content"><img src="'+src+'"></div>';
                                        element.onclick = function(){
                                            element.remove();
                                        };
                                        document.querySelector('body').appendChild(element);
                                    }
                                });
                            });
                        })();  
                    });
                </script>
                <?php
                $html .= ob_get_clean();
                $return =$html;
		}
    	return $return;
    }

    function group_field($atts,$content=null){

    	$return='';
    	

    	if(!empty($atts['group_id'])){
    		$group_id = $atts['group_id'];
    	}else{
    		if(bp_get_current_group_id()){
				$group_id = bp_get_current_group_id();
			}
			if(empty($group_id)){
				$init = VibeBP_Init::init();
				if(!empty($init->group_id)){
					$group_id = $init->group_id;
				}
			}
    	}
    	

		if(!empty($atts['field_name']) && !empty($group_id)){
			$data = groups_get_groupmeta($group_id,esc_attr($atts['field_name']),true);
			
			$gfields = vibebp_get_setting('group_custom_fields','bp','groups');
			if(!empty($gfields)){
				
				if(in_Array($atts['field_name'],$gfields['key'])){
					$i = array_search($atts['field_name'],$gfields['type']);
					if(in_array($gfields['type'][$i],['select','radio','checkbox'])){
						$options = explode('|',$gfields['options'][$i]);
						if(!empty($options)){
							foreach($options as $option){
								$vals = explode(';',$option);
								if($vals[0] == $data){
									$data = $vals[1];
								}
							}
						}
					}
				}
			}
			if(is_array($data)){
				$data = implode(',',$data);
			}
			$return = apply_filters('vibebp_group_field', $data, $group_id);
		}
    	return $return;
    }
    function vicon($atts,$content=null){

    	wp_enqueue_style('vicons');

    	$return ='<span class="vicon vicon-'.$atts['vicon'].'" style="'.(empty($atts['size'])?'':'font-size:'.$atts['size'].'px;').' '.(empty($atts['color'])?'':'color:'.$atts['color']).'"></span>';
    	return $return;
    }
    
    function block($post_type,$taxonomy){
    	$return = '';
    	$return .='<div class="swiper-slide"><div class="gutenberg_post_block relative flex flex-col gap-4 post_block_'.get_the_ID().'">';
		$return .='<div class="border rounded-xl"><a href="'.get_permalink().'">';
		if(has_post_thumbnail()){
			$return .='<img src="'.get_the_post_thumbnail_url(get_the_ID(),'medium').'" class="rounded-xl" />';	
		}else{
			$return .='<img src="'.plugins_url('../../assets/images/default.svg',__FILE__).'" class="rounded-xl" />';
		}
		
		$return .='</a></div>
		<div class="flex flex-col gap-1 flex-1">';
		$return .= '<strong class="text-xl"><a href="'.get_permalink().'">'.get_the_title().'</a></strong>';
		$return .= '<span>'.get_the_author_meta('display_name').'</span>';
		$return .='</div>';
		$return .= '<div class="flex gap-4 items-center justify-between">';
		if($post_type == 'course' && function_exists('bp_course_get_course_credits')){
			$return .= '<div class="amount">'.bp_course_get_course_credits().'</div>';	
		}
		$return .='<div class="post_hover_block">
		<div class="p-4 rounded-xl border-xl flex flex-col gap-2 relative">
		<h3>'.get_the_title().'</h3>
		<small class="post-taxonoomy-terms">'.get_post_modified_time('F d, Y').'</small>';

		if($post_type == 'course' && function_exists('bp_course_get_curriculum')){
			

			$rating=get_post_meta(get_the_ID(),'average_rating',true);
			if(empty($rating)){$rating ='N.A';}
			$return .='<div class="flex gap-2 items-center opacity-50">
				<span class="flex items-center gap-1">
					<span class="vicon vicon-star"></span>
					<span>'.$rating.'</span>
				</span>
				<span class="flex items-center gap-1">
					<span class="vicon vicon-user"></span>
					<span>'.bp_course_get_students_count(get_the_ID()).'</span>
				</span>
				<span class="flex items-center gap-1">
					<span class="vicon vicon-timer"></span>
					<span>'.tofriendlytime(bp_course_get_course_duration(get_the_ID())).'</span>
				</span>
			</div>';
			
		}

		$return .='<div class="post_intro">
		'.wp_strip_all_tags(get_the_excerpt()).'
		</div>
		<div class="post_actions flex justify-between gap-4">
		<span class="opacity-80 gap-1 flex items-center"><img class="tax_post_author" src="'.bp_core_fetch_avatar( array(
                    'item_id' => get_the_author_meta('ID'),
                    'type' => 'thumb',
                    'html'    => false
                    )).'">'.get_the_author_meta('display_name').'</span>
		<a class="link" href="'.get_permalink().'"><span class="vicon vicon-arrow-right"></span></a>
		</div>
		</div>
		</div>';
		$return .='</div></div></div>';

		return $return;
    }


    function taxonomy_post_carousel($atts,$content=null){
		$defaults = [
			'posts_per_page'=>8,
			'taxonomy'=>'course-cat',
			'post_type'=>'course',
			'include_terms'=>'',
			'exclude_terms'=>'',
			'popular'=>0,
			'new'=>0,
			'term_orderby'=>'meta_value',			
		];
		$args = wp_parse_args($atts,$defaults);
		extract($args);

		wp_enqueue_style('vibebp-swiper');
		wp_enqueue_script('vibebp-swiper');
		wp_enqueue_style('vicons');

		if($args['taxonomy'] == 'course-cat'){
			$terms = get_terms( $taxonomy, array(
				'hide_empty' => false,
				'meta_key' => 'course_cat_order',
		   		'orderby' => 'meta_value',
		   		'order' => 'DESC'
		   	));
		}else{
			$terms = get_terms( $taxonomy, array('hide_empty' => false) );	
		}
		
		$return ='<div class="gutenberg_vibe_post_carousel flex flex-col gap-4">';

		$return .='<div class="gutenberg_vibe_category_list flex flex-wrap gap-4 items-center">';
		if(!empty($terms)){
			foreach($terms as $i=>$term){
				$return .='<a class="link '.$taxonomy.'_term '.(empty($i)?'primary-color':'').'" data-term="'.$term->term_id.'">'.$term->name.'</a>';
			}
		}
		$return .='</div>';
		$query_args =apply_filters('course_category_carousel',[
			'post_type'=>$post_type,
			'posts_per_page'=>$posts_per_page,
		]);

		if(!empty($terms)){
			$query_args['tax_query']=[
				'relation'=>'AND',
				[
					'taxonomy'=>$taxonomy,
					'terms'=>[$terms[0]->term_id]
				]
			];
		}
		$query = new WP_Query($query_args);
		$return .='<div class="taxonomy_swiper_wrapper p-4 border rounded flex flex-col gap-4">';
		if(!empty($terms)){
			foreach($terms as $k=>$term){
				$return .= '<div class="flex flex-col gap-2 about_term items-start term-'.$term->term_id.' '.(empty($k)?'active':'').'"><p>'.$term->description.'</p><a href="'.get_term_link($term).'" class="button">'.sprintf(_x('Explore %s','vibebp'),$term->name).'</a></div>';	
			}
			
		}

		if($query->have_posts()){
			$return .='<div class="taxonomy_swiper"><div class="swiper-wrapper">';
			while($query->have_posts()){
				$query->the_post();
				$return .= $this->block($post_type,$taxonomy);
			}
			$return .='</div><div class="swiper-button-prev"></div>
		  		<div class="swiper-button-next"></div></div>';
		}
		$return .='</div>';
		$return .='</div><style>.link{cursor:pointer;}.gutenberg_post_block:hover .post_hover_block{display:flex;}
		.post_hover_block {
		    position: absolute;
		    left: calc(100% + 30px);
		    top: 0;
		    z-index: 99;
		    width:100%;
		    background: var(--highlight);
		    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
		    border-radius: 1rem;
		    display:none;
		}.tax_post_author{width:32px;height:32px;border-radius:50%;}

		.post_hover_block > .p-4:before {
		    content: "";
		    position: absolute;
		    left: calc(-1rem - 5px);
		    height: 0;
		    background: var(--bordeR);
		    display: block;
		    border: 10px solid;
		    border-color: transparent var(--border) transparent transparent;
		}
		.taxonomy_swiper .swiper-wrapper > .swiper-slide:last-child .post_hover_block{
			left: calc(-100% - 30px);
		}.taxonomy_swiper  .swiper-wrapper > .swiper-slide:last-child .post_hover_block > .p-4:before {
			left:auto;
			right:calc(-1rem - 5px);
			border-color: transparent transparent transparent var(--border);
		}
		.about_term{display:none;}.about_term.active{display:flex;}
		@media (max-width:480px){
			.gutenberg_post_block:hover .post_hover_block{
				left:0;
				height:100%;
			}
		}
		</style><script>

		
		let taxonomy_swiper=null;
		document.addEventListener("DOMContentLoaded",function(){
			

			const taxonomy_swiper = new Swiper(".taxonomy_swiper",
			{
				"speed":400,
			 	"direction":"horizontal",
			 	"effect":"slide",
			 	"watchOverflow":true,
			 	"navigation":{
			 		"nextEl":".swiper-button-next",
			 		"prevEl":".swiper-button-prev",
			 		"disabledClass":"disabled_swiper_button"
				},
				"initialSlide":"0",
				"slidesPerView":4,
				"spaceBetween":40,
				"breakpoints":{
					"@0.00":{"slidesPerView":1,"spaceBetween":10},
					"@0.75":{"slidesPerView":2,"spaceBetween":20},
					"@1.00":{"slidesPerView":3,"spaceBetween":30},
					"@1.50":{"slidesPerView":4,"spaceBetween":30}
				}
			});


			document.querySelectorAll(".'.$taxonomy.'_term").forEach(function(el){
				if(!el.parentElement.parentElement.querySelector(".swiper-wrapper").classList.contains("opacity-50")){
					
					
					el.addEventListener("click",function(){
						el.parentElement.parentElement.querySelector(".swiper-wrapper").classList.add("opacity-50");

el.parentElement.parentElement.querySelector(".about_term.active").classList.remove("active");
el.parentElement.parentElement.querySelector(".term-"+el.getAttribute("data-term")).classList.add("active");

						var xhr = new XMLHttpRequest();
						xhr.open("POST", ajaxurl);
						xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
						xhr.onload = function() {
						    if (xhr.status === 200) {
						    	el.parentElement.querySelector(".primary-color").classList.remove("primary-color");
						    	el.parentElement.parentElement.querySelector(".swiper-wrapper").classList.remove("opacity-50");
						    	el.classList.add("primary-color");
						    	el.parentElement.parentElement.querySelector(".swiper-wrapper").innerHTML=xhr.responseText;
						    	el.parentElement.parentElement.querySelector(".taxonomy_swiper").swiper.update();
						    }
						};

						xhr.send(encodeURI(\'action=viebbp_load_taxonomy_carousel&security='.wp_create_nonce('security').'&term_id=\'+el.getAttribute("data-term")+\'&args='.json_encode($args).'\'));
					});
				}
			});
		},false);
		</script>';
		wp_reset_postdata();

		return $return;
	}


	function viebbp_load_taxonomy_carousel(){

		if(empty($_POST['security']) || !wp_verify_nonce($_POST['security'],'security'))
			return 0;

		$args = json_decode(stripslashes($_POST['args']),true);
		
		extract($args);
		$query_args =apply_filters('course_category_carousel',[
			'post_type'=>$post_type,
			'posts_per_page'=>$posts_per_page,
		]);

		
		$query_args['tax_query']=[
			'relation'=>'AND',
			[
				'taxonomy'=>$taxonomy,
				'terms'=>[intval($_POST['term_id'])]
			]
		];
		
		$query = new WP_Query($query_args);
		$return = '';
		if($query->have_posts()){

			$return ='';
			while($query->have_posts()){
				$query->the_post();
				$return .= $this->block($post_type,$taxonomy);
			}
		}
		echo $return;

		die();
	}
}

VibeBp_Shortcodes::init();

