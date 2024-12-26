<?php

if(!class_exists('Vibe_Options')){
	require_once( dirname( __FILE__ ) . '/options/options.php' );
}

/*
 * 
 * Custom function for filtering the sections array given by theme, good for child themes to override or add to the sections.
 * Simply include this function in the child themes functions.php file.
 *
 * NOTE: the defined constansts for urls, and dir will NOT be available at this point in a child theme, so you must use
 * get_template_directory_uri() if you want to use any of the built in icons
 *
 */
function add_another_section($sections){
	
	//$sections = array();
	$sections[] = array(
				'title' => __('A Section added by hook', 'micronet'),
				'desc' => '<p class="description">'.__('This is a section created by adding a filter to the sections array, great to allow child themes, to add/remove sections from the options.', 'micronet').'</p>',
				//all the glyphicons are included in the options folder, so you can hook into them, or link to your own custom ones.
				//You dont have to though, leave it blank for default.
				'icon' => trailingslashit(get_template_directory_uri()).'options/img/glyphicons/glyphicons_062_attach.png',
				//Lets leave this as a blank section, no options just some intro text set above.
				'fields' => array()
				);
	
	return $sections;
	
}//function


/*
 * 
 * Custom function for filtering the args array given by theme, good for child themes to override or add to the args array.
 *
 */
function change_framework_args($args){
	
	//$args['dev_mode'] = false;
	
	return $args;
	
}


function vibe_options_get_sections(){
	$sections = array();

	$sections[] = array(
					'title' => __('Theme', 'micronet'),
					'desc' => '<p class="description">'.sprintf(__('Welcome to %s Theme Options panel. ','micronet'),THEME_FULL_NAME).'</p>',
					'icon' => 'menu',
	                'fields' => array( 
							array(
								'id' => 'theme_skin',
								'type' => 'button_set',
								'title' => __('Theme Skin', 'micronet'), 
								'sub_desc' => __('set a theme skin' , 'micronet'),
								'desc' => __('FSE Theme vs Regular WP Theme.', 'micronet'),
								'options' => array('0' => __('Default skin','micronet'),'minimal' => __('Minimal skin','micronet')),//Must provide key => value pairs for radio options
							'std' => '0'
							),  
	                    )
	            	);


	$sections[] = array(
					'icon' => 'admin-generic',
					'title' => __('Header', 'micronet'),
					'desc' => '<p class="description">'.__('Header settings','micronet').'..</p>',
					'fields' => array(
	                        array(
								'id' => 'header_fix',
								'type' => 'button_set',
								'title' => __('Fix Top Header on Scroll', 'micronet'), 
								'sub_desc' => __('Fix header on top of screen' , 'micronet'),
								'desc' => __('header is fixed to top as user scrolls down.', 'micronet'),
								'options' => array('0' => __('Static','micronet'),'1' => __('Fixed on Scroll','micronet')),//Must provide key => value pairs for radio options
							'std' => '0'
							),  
							array(
								'id' => 'header_search',
								'type' => 'button_set',
								'title' => __('Show Search in Site Header', 'micronet'), 
								'sub_desc' => __('Search bar appears in the header.' , 'micronet'),
								'desc' => __('Show an expanded search bar in the header', 'micronet'),
								'options' => array('0' => __('Disable','micronet'),'1' => __('Enable','micronet')),//Must provide key => value pairs for radio options
							'std' => '0'
							),
							array(
								'id' => 'wide_header',
								'type' => 'button_set',
								'title' => __('Fullwidth Header' ,'micronet'), 
								'sub_desc' => __('Default Wide header' , 'micronet'),
								'desc' => __('Set a screen wide header.', 'micronet'),
								'options' => array('0' => __('Disable','micronet'),'1' => __('Enable','micronet')),//Must provide key => value pairs for radio options
							'std' => '0'
							),
							
							array(
								'id' => 'mega_menus',
								'type' => 'button_set',
								'title' => __('Enable MegaMenu', 'micronet'), 
								'sub_desc' => __('Mega menus by default. Changes presentation of default menus.' , 'micronet'),
								'desc' => __('Mega menu presentation is changed.', 'micronet'),
								'options' => array('0' => __('Disable','micronet'),'1' => __('Enable','micronet')),//Must provide key => value pairs for radio options
							'std' => '0'
							),  
							array(
								'id' => 'header_background',
								'type' => 'text_upload',
								'title' => __('Header Background', 'micronet'), 
								'sub_desc' => __('Header background', 'micronet'),
								'desc' => __('Set a header background', 'micronet'),
		                        'std' => VIBE_URL.'/images/header_background.jpg'
							),  
							array(
								'id' => 'header_color',
								'type' => 'color',
								'title' => __('Header Text color', 'micronet'), 
								'sub_desc' => __('Color of Text in header', 'micronet'),
								'desc' => __('Heading , breadcrumbs and other text color appearing on background', 'micronet'),
		                        'std' => '#ffffff'
							), 
							array(
								'id' => 'header_extras',
								'type' => 'button_set',
								'title' => __('Show Login / Cart in Header', 'micronet'), 
								'sub_desc' => __('Login link and cart appears in header.' , 'micronet'),
								'desc' => __('Cart only appears when WooCommerce installed.', 'micronet'),
								'options' => array('0' => __('Disable','micronet'),'1' => __('Enable','micronet')),//Must provide key => value pairs for radio options
							'std' => '0'
							),
							
						)
					);


	$sections[] = array(
					'icon' => 'groups',
					'title' => __('Buddypress', 'micronet'),
					'desc' => '<p class="description">'.__('BuddyPress settings and Variables','micronet').'..</p>',
					'fields' => array(
						array(
							'id' => 'default_avatar',
							'type' => 'upload',
							'title' => __('Upload BuddyPress default member avatar', 'micronet'), 
							'sub_desc' => __('BuddyPress default members avatar', 'micronet'),
							'desc' => '',
	                        'std' => VIBE_URL.'/assets/images/avatar.jpg'
							),
						array(
							'id' => 'wp_admin_access',
	                        'title' => __('WP Admin area access', 'micronet'),
	                        'sub_desc' => __('Restrict WP Admin area access', 'micronet'),
	                        'desc' => __('WP Admin area is restricted for', 'micronet'),
	                        'type' => 'button_set',
							'options' => array('' => __('All','micronet'),'1'=>__('Instructors & Administrators only','micronet'),'2'=>__('Administrators only','micronet')),
							'std' => ''
							),
						 array(
							'id' => 'hide_wp_admin_bar',
	                        'title' => __('Hide WP Admin bar for', 'micronet'),
	                        'sub_desc' => __('Hide the top WP admin bar', 'micronet'),
	                        'desc' => sprintf(__('WP Admin bar is hidden for user types %s Tutorial %s', 'micronet'),'<a href="https://www.youtube.com/watch?v=I_NkIlf7cUY" target="_blank">','</a>'),
	                        'type' => 'button_set',
							'options' => array('' => __('Non Administratos only','micronet'),'2'=>__('Everyone','micronet')),
							'std' => ''
							),
						array(
							'id' => 'custom_registration_page',
							'type' => 'pages_select',
	                        'title' => __('Custom Registration page', 'micronet'),
	                        'sub_desc' => __('Overrides the default registration page from buddypress', 'micronet'),
						),								
					
						array(
							'id' => 'blog_create',
	                        'title' => __('Create Blog (multisite)', 'micronet'),
	                        'sub_desc' => __('Blogs can be created by :', 'micronet'),
	                        'desc' => sprintf(__('Blog creation : Members{Loggedin Members},Teachers {Teachers, Admins,Editors}, %s screenshot %s', 'micronet'),'<a href="http://prntscr.com/cldri1" target="_blank">','</a>'),
	                        'type' => 'button_set',
							'options' => array('1'=>__('Members only','micronet'),'2' => __('Teachers only','micronet'),'3' => __('Admins only','micronet')),//Must provide key => value pairs for radio options
							'std' => '1'
							),	
						
					)
				);

	$font_fields = [
		array(
			'id' => 'default_font',
            'title' => __('Disable Default font Roboto Slab', 'micronet'),
            'sub_desc' => __('default font in Theme', 'micronet'),
            'desc' => __('Since Google fonts store data, we have included a default font in the theme.', 'micronet'),
            'type' => 'button_set',
			'options' => array(''=>__('No','micronet'),'1' => __('Yes','micronet')),//Must provide key => value pairs for radio options
			'std' => ''
		),
		array(
			'id' => 'google_fonts',
			'type' => 'google_webfonts_multi_select',
            'title' => __('Select Fonts for Live Theme Editor ', 'micronet'),
            'sub_desc' => __('Select Fonts and setup fonts in Live Editor', 'micronet'),
            'desc' => __('Use these sample layouts in PageBuilder.', 'micronet')
		),
        array(
			'id' => 'custom_fonts',
			'type' => 'multi_text',
	        'title' => __('Custom Fonts (Enter CSS Font Family name)', 'micronet'),
	        'sub_desc' => __(' Custom Fonts are added to Theme Customizer Font List.. ', 'micronet')
		)
	];

	$fontlist = [];
	if(!empty(micronet_get_option('google_fonts'))){
		$fontlist = array_merge($fontlist,micronet_get_option('google_fonts'));
	}

	if(!empty(micronet_get_option('custom_fonts'))){
		$fontlist = array_merge($fontlist,micronet_get_option('google_fonts'));
	}
	if(!empty($fontlist)){
		$font_fields[]=[
			'id' => 'heading-font',
            'title' => __('Heading Font', 'micronet'),
            'sub_desc' => __('Font for all Headings in Theme', 'micronet'),
            'desc' => __('Select a fonts for the Headings', 'micronet'),
            'type' => 'select',
			'options' => $fontlist,
			'std' => ''
		];
		$font_fields[]=[
			'id' => 'body-font',
            'title' => __('Body Font', 'micronet'),
            'sub_desc' => __('Font for all elements in Theme', 'micronet'),
            'desc' => __('Select a fonts for the Body', 'micronet'),
            'type' => 'select',
			'options' => $fontlist,
			'std' => ''
		];
	}
	$sections[] = array(
					'icon' => 'editor-spellcheck',
					'title' => __('Fonts Manager', 'micronet'),
					'desc' => '<p class="description">'.__('Manage Fonts to be used in the Site. Fonts selected here will be available in Theme customizer font family select options.','micronet').'..</p>',
					'fields' => $font_fields
					);

	$sections[] = array(
					'icon' => 'editor-spellcheck',
					'title' => __('Color Manager', 'micronet'),
					'desc' => '<p class="description">'.__('Manage Colors used in the Site.','micronet').'..</p>',
					'fields' => array(
						array(
							'id' => 'bg-primary',
	                        'title' => __('Theme Primary color', 'micronet'),
	                        'sub_desc' => __('Primary color in Theme', 'micronet'),
	                        'desc' => __('Button background, link colors, hover colors.', 'micronet'),
	                        'type' => 'color',
							'std' => ''
						),
						array(
							'id' => 'color-primary',
							'type' => 'color',
	                        'title' => __('Text color on Primary Background', 'micronet'),
	                        'sub_desc' => __('Color of text on primary bg', 'micronet'),
	                        'desc' => __('Primary Button background has this text color.', 'micronet'),
	                        'std' => ''
						),

						array(
							'id' => 'header-bg',
							'type' => 'color',
	                        'title' => __('Header Background', 'micronet'),
	                        'sub_desc' => __(' Background of header area ', 'micronet'),
	                        'std' => ''
						),
						array(
							'id' => 'root-menu-main-active-color',
							'type' => 'color',
	                        'title' => __('Active menu item', 'micronet'),
	                        'sub_desc' => __('Text color of active menu item', 'micronet'),
	                        'std' => ''
						),
						array(
							'id' => 'sub-menu-hover-color',
							'type' => 'color',
	                        'title' => __('Active Sub menu item color', 'micronet'),
	                        'sub_desc' => __('Text color of active sub-menu item', 'micronet'),
	                        'std' => ''
						),
						array(
							'id' => 'sub-menu-background-color',
							'type' => 'color',
	                        'title' => __('Background Sub menu color', 'micronet'),
	                        'sub_desc' => __('Background color of sub-menu  & Mega menu', 'micronet'),
	                        'std' => ''
						),
						
						
						array(
							'id' => 'header-color',
							'type' => 'color',
	                        'title' => __('Header Text color', 'micronet'),
	                        'sub_desc' => __('Color of text on Header background ', 'micronet'),
	                        'std' => ''
						),
						array(
							'id' => 'bodybg',
							'type' => 'color',
	                        'title' => __('Body Background', 'micronet'),
	                        'sub_desc' => __('Background of body. ', 'micronet'),
	                        'std' => ''
						),
	                    array(
							'id' => 'contentbg',
							'type' => 'color',
	                        'title' => __('Content Background', 'micronet'),
	                        'sub_desc' => __(' Background of content area ', 'micronet'),
	                        'std' => ''
						),
						array(
							'id' => 'text-color',
							'type' => 'color',
	                        'title' => __('Text Color', 'micronet'),
	                        'sub_desc' => __(' default Text area ', 'micronet'),
	                        'std' => ''
						),
						array(
							'id' => 'highlight-color',
							'type' => 'color',
	                        'title' => __('Highlight color', 'micronet'),
	                        'sub_desc' => __('Color of text on Header background ', 'micronet'),
	                        'std' => ''
						),
						array(
							'id' => 'highlight-bg',
							'type' => 'color',
	                        'title' => __('Highlight Background', 'micronet'),
	                        'sub_desc' => __('Background color of highlighted area', 'micronet'),
	                        'std' => ''
						),
						array(
							'id' => 'post-content-border',
							'type' => 'color',
	                        'title' => __('Border Color', 'micronet'),
	                        'sub_desc' => __('Border color', 'micronet'),
	                        'std' => ''
						),
						array(
							'id' => 'footer-bg',
							'type' => 'color',
	                        'title' => __('Footer background Color', 'micronet'),
	                        'sub_desc' => __('Background color in footer', 'micronet'),
	                        'std' => ''
						),
						array(
							'id' => 'footer-color',
							'type' => 'color',
	                        'title' => __('Footer Textarea Color', 'micronet'),
	                        'sub_desc' => __('Text color in footer', 'micronet'),
	                        'std' => ''
						),
					)
				);
	

$sections[] = array(
				'icon' => 'feedback',
				'title' => __('Sidebar Manager', 'micronet'),
				'desc' => '<p class="description">'.__('Generate more sidebars dynamically and use them in various layouts','micronet').'..</p>',
				'fields' => array(
					 array(
						'id' => 'sidebars',
						'type' => 'multi_text',
                        'title' => __('Create New sidebars ', 'micronet'),
                        'sub_desc' => __('Dynamically generate sidebars', 'micronet'),
                        'desc' => __('Use these sidebars in various layouts. DO NOT ADD ANY SPECIAL CHARACTERS in Sidebar name', 'micronet')
						),		
					)
				);

	$sections[] = array(
					'icon' => 'editor-insertmore',
					'title' => __('Footer ', 'micronet'),
					'desc' => '<p class="description">'.__('Setup footer settings','micronet').'..</p>',
					'fields' => array( 
							           
	                        array(
								'id' => 'google_analytics',
								'type' => 'textarea',
								'title' => __('Google Analytics Code', 'micronet'), 
								'sub_desc' => __('Google Analytics account', 'micronet'),
								'desc' => __('Please enter full code with javascript tags.', 'micronet'),
							),
	                        array(
								'id' => 'footer_sidebar',
		                        'title' => __('Widgetised Footer', 'micronet'),
		                        'sub_desc' => __('set widgets in footer', 'micronet'),
		                        'desc' => __('Create a Footer using Widgets.', 'micronet'),
		                        'type' => 'button_set',
								'options' => array(
									''=>__('Disable','micronet'),
									'1' => __('Enable','micronet')
								),
								'std' => ''
							),
	                        array(
								'id' => 'footer_sidebar_colums',
								'type' => 'select',
								'options'=>[
									5=>__('5 columns','micronet'),
									4=>__('4 columns','micronet'),
									3=>__('3 columns','micronet'),
									2=>__('2 columns','micronet')
								],
								'title' => __('Number of Columns', 'micronet'), 
								'sub_desc' => __('Set columns for Widgets in Footer', 'micronet'),
								'desc' => __('This is only valid for Classic Widgets.', 'micronet'),
							),

						 				
						)
					);

	$sections[] = array(
					'icon' => 'location',
					'title' => __('Miscellaneous', 'micronet'),
					'desc' =>'<p class="description">'. __('Miscellaneous settings used in the theme.', 'micronet').'</p>',
					'fields' => array(
							array(
							'id' => 'excerpt_length',
							'type' => 'text',
							'title' => __('Default Excerpt Length', 'micronet'), 
							'sub_desc' => __('Excerpt length in number of Words.', 'micronet'),
							'std' => '20'
							),
	                        array(
								'id' => 'error404',
								'type' => 'pages_select',
								'title' => __('Select 404 Page', 'micronet'), 
								'sub_desc' => __('This page is shown when page not found on your site.', 'micronet'),
								'desc' => __('User redirected to this page when page not found.', 'micronet'),
							), 
							array(
								'id' => 'xmlrpc',
								'type' => 'button_set',
								'title' => __('Disable XMLRPC/RSD/WLWManifest', 'micronet'), 
								'sub_desc' => __('Remove security vulnerabilities', 'micronet'),
								'desc' => __('Removes vulnerabilities at expense of ability to login via remote apps.', 'micronet'),
								'options' => array('' => __('No','micronet'),1 => __('Yes','micronet')),
								'std' => ''
							),
							array(
								'id' => 'wp_login_screen',
								'type' => 'textarea',
								'title' => __('Custom CSS for WP Login Screen', 'micronet'), 
								'sub_desc' => __('Add custom CSS', 'micronet'),
								'desc' => __('Custom CSS for WP Login screen.', 'micronet'),
							),
							array(
								'id' => 'credits',
								'type' => 'text',
								'title' => __('Author & Credits', 'micronet'), 
								'sub_desc' => __('Credits and Author of the Website', 'micronet'),
								'desc' => __('Changes the reference to Author {VibeThemes}', 'micronet'),
		                        'std' => 'VibeThemes'
							),
	                      )
	                    );

	global $Vibe_Options;
	return apply_filters('micronet_option_custom_sections',$sections);
}
/*
 * This is the meat of creating the optons page
 *
 * Override some of the default values, uncomment the args and change the values
 * - no $args are required, but there there to be over ridden if needed.
 *
 *
 */

function setup_framework_options(){
	$args = array();
	global $Vibe_Options;

	$Vibe_Options = get_option(THEME_SHORT_NAME);  //Initialize micronetoptions
	//Set it to dev mode to view the class settings/info in the form - default is false
	$args['dev_mode'] = false;

	//google api key MUST BE DEFINED IF YOU WANT TO USE GOOGLE WEBFONTS
	//$args['google_api_key'] = '***';

	//Remove the default stylesheet? make sure you enqueue another one all the page will look whack!
	//$args['stylesheet_override'] = true;

	//Add HTML before the form
	$args['intro_text'] = '';
	//Setup custom links in the footer for share icons
	$args['share_icons']['twitter'] = array(
		'link' => 'http://twitter.com/vibethemes',
		'title' => __('Folow me on Twitter','micronet'), 
		'img' => VIBE_OPTIONS_URL.'img/ico-twitter.png'
	);
	$args['share_icons']['facebook'] = array(
		'link' => 'http://facebook.com/vibethemes',
		'title' => __('Be our Fan on Facebook','micronet'), 
		'img' => VIBE_OPTIONS_URL.'img/ico-facebook.png'
	);
	$args['share_icons']['rss'] = array(
		'link' => 'feed://themeforest.net/feeds/users/VibeThemes',
		'title' => __('Latest News from VibeThemes','micronet'), 
		'img' => VIBE_OPTIONS_URL.'img/ico-rss.png'
	);

	//Choose to disable the import/export feature
	//$args['show_import_export'] = false;

	//Choose a custom option name for your theme options, the default is the theme name in lowercase with spaces replaced by underscores
	$args['opt_name'] = THEME_SHORT_NAME;

	//Custom menu icon
	//$args['menu_icon'] = '';

	//Custom menu title for options page - default is "Options"
	$args['menu_title'] = THEME_FULL_NAME;

	//Custom Page Title for options page - default is "Options"
	$args['page_title'] = __('Vibe Options Panel v 2.0', 'micronet');

	//Custom page slug for options page (wp-admin/themes.php?page=***) - default is "micronet_theme_options"
	$args['page_slug'] = THEME_SHORT_NAME.'_options';

	//Custom page capability - default is set to "manage_options"
	$args['page_cap'] = 'manage_options';

	//page type - "menu" (adds a top menu section) or "submenu" (adds a submenu) - default is set to "menu"
	//$args['page_type'] = 'submenu';
	//$args['page_parent'] = 'themes.php';
	$social_links=array();
	if(function_exists('social_sharing_links')){
	$social_links= social_sharing_links();
	foreach($social_links as $link => $value){
	    $social_links[$link]=$link;
	 }
	}


	//custom page location - default 100 - must be unique or will override other items
	$args['page_position'] = 62;

	$args['help_tabs'][] = array(
								'id' => 'micronet-opts-1',
								'title' => __('Support', 'micronet'),
								'content' => '<p>'.__('We provide support via three mediums (in priority)','micronet').':
	                                                            <ul><li><a href="https://micronetdocs.wpappointify.com" target="_blank">'.THEME_FULL_NAME.' VibeThemes Forums</a></li><li>'.__('Support Email: VibeThemes@gmail.com', 'micronet').'</li><li>'.__('ThemeForest Item Comments','micronet').'</li></ul>
	                                                            </p>',
								);
	$args['help_tabs'][] = array(
								'id' => 'micronet-opts-2',
								'title' => __('Documentation & Links', 'micronet'),
								'content' => '<ul><li><a href="http://micronetthemes.com/documentation/wplms/forums/" target="_blank">'.THEME_FULL_NAME.' Support Panel</a></li>
		                                          <li><a href="http://wpappointify.com" target="_blank">'.THEME_FULL_NAME.' Theme Setup</a></li>
		                                          <li><a href="http://wpappointify.com" target="_blank">'.THEME_FULL_NAME.' Common FAQs/Issues</a></li>  
		                                          <li><a href="http://wpappointify.com" target="_blank">'.THEME_FULL_NAME.' Tips and Tricks</a></li>
		                                          <li><a href="http://wpappointify.com" target="_blank">'.THEME_FULL_NAME.' Feature Requests</a></li>    
		                                          <li><a href="http://wpappointify.com" target="_blank">'.THEME_FULL_NAME.' Update Log</a></li>    
		                                          <li><a href="#" target="_blank">'.THEME_FULL_NAME.' Video Guide</a></li>
		                                      </ul>
	                                                            ',
								);


	//Set the Help Sidebar for the options page - no sidebar by default										
	$args['help_sidebar'] = '<p>'.__('For Support/Help and Documentation open','micronet').'<strong><a href="http://wpappointify.com/">'.THEME_FULL_NAME.' forums</a></strong>'.__('Or email us at','micronet').' <a href="mailto:vibethemes@gmail.com">vibethemes@gmail.com</a>. </p>';




	$sections = vibe_options_get_sections();

	$tabs = array();
	

	if (function_exists('wp_get_theme')){
		$theme_data = wp_get_theme();
		$theme_uri = $theme_data->get('ThemeURI');
		$description = $theme_data->get('Description');
		$author = $theme_data->get('Author');
		$version = $theme_data->get('Version');
		$tags = $theme_data->get('Tags');
	}else{
		$theme_data = wp_get_theme(trailingslashit(get_stylesheet_directory()).'style.css');
		$theme_uri = $theme_data['URI'];
		$description = $theme_data['Description'];
		$author = $theme_data['Author'];
		$version = $theme_data['Version'];
		$tags = $theme_data['Tags'];
	}	

	$theme_info = '<div class="micronet-opts-section-desc">';
	$theme_info .= '<p class="micronet-opts-theme-data description theme-uri"><strong>Theme URL:</strong> <a href="'.$theme_uri.'" target="_blank">'.$theme_uri.'</a></p>';
	$theme_info .= '<p class="micronet-opts-theme-data description theme-author"><strong>Author:</strong>'.$author.'</p>';
	$theme_info .= '<p class="micronet-opts-theme-data description theme-version"><strong>Version:</strong> '.$version.'</p>';
	$theme_info .= '<p class="micronet-opts-theme-data description theme-description">'.$description.'</p>';
	$theme_info .= '<p class="micronet-opts-theme-data description theme-tags"><strong>Tags:</strong> '.implode(', ', $tags).'</p>';
	$theme_info .= '</div>';
	$tabs['theme_info'] = array(
		'icon' => 'info-sign',
		'title' => __('Theme Information', 'micronet'),
		'content' => $theme_info
	);
	$Vibe_Options = new Vibe_Options($sections, $args, $tabs);
	wp_cache_delete('micronet_option','settings');
	
}//function
add_action('init', 'setup_framework_options', 0);

/*
 * 
 * Custom function for the callback referenced above
 *
 */
function my_custom_field($field, $value){
	

}//function

/*
 * 
 * Custom function for the callback validation referenced above
 *
 */
function validate_callback_function($field, $value, $existing_value){
	
	$error = false;
	$value =  'just testing';
	
	$return['value'] = $value;
	if($error == true){
		$return['error'] = $field;
	}
	return $return;
	
}//function


?>
