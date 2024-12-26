<?php
/**
 * Customizer plugin
 *
 * @class       VibeBP_Customizer
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 * @copyright   VibeThemes
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}




class VibeBP_Customizer{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Customizer();
        return self::$instance;
    }

	private function __construct(){

		add_action('customize_register', array($this,'vibebp_customize'));
		add_action('wp_head',array($this,'generate_css'));

        add_filter('vibebp_component_icon',array($this,'elegance_theme'),11,2);

        add_filter('vibebp_customizer_config',array($this,'customizer_slick'));
	}
 
    function get_customizer(){
        if(empty($this->customizer)){
            $this->customizer=get_option('vibebp_customizer');    
        }
        return $this->customizer;
    }


	function generate_controls(){
		return apply_filters('vibebp_customizer_config',array(
		    'sections' => array(
                'vibebp_general_settings'=>'VibeBp General Settings',
            	'vibebp_light_colors'=>'VibeBp Light Colors',
            	'vibebp_dark_colors'=>'VibeBp Dark Colors',
            ),
		    'controls' => array(
                'vibebp_general_settings' => array( 
                    'theme' => array(
                        'label' => 'Theme style',
                        'type'  => 'select',
                        'choices'=>array(
                            ''=>'Default',
                            'elegance'=>'Elegance',
                            'slick'=>'Slick',
                            'simple'=>'Simple',
                        ),
                        'default' => ''
                    ),
                    'mode' => array(
                        'label' => 'Default Dark Style',
                        'type'  => 'toggle',
                        'default' => ''
                    ),
                    'expanded_menu' => array(
                        'label' => 'Show expanded profile menu',
                        'type'  => 'toggle',
                        'default' => ''
                    ),
                    'loggedin_profile_header' => array(
                        'label' => 'Disable Header in My Profile',
                        'type'  => 'toggle',
                        'default' => ''
                    ),
                    'loggedin_profile_footer' => array(
                        'label' => 'Disable Footer in My Profile',
                        'type'  => 'toggle',
                        'default' => ''
                    ),
                    'profile_menu_promo' => array(
                        'label' => 'Show above profile menu',
                        'type'  => 'image',
                        'default' => ''
                    ),
                    'profile_grid_promo' => array(
                        'label' => 'Show above members area',
                        'type'  => 'text',
                        'default' => ''
                    ),
                ),
		        'vibebp_light_colors' => array( 
                    'light_primary' => array(
                        'label' => 'Primary Color',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'light_primarycolor' => array(
                        'label' => 'Text Color on  Primary background',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    
                    'light_body' => array(
                        'label' => 'Body Background',
                        'type'  => 'color',
                        'default' => ''
                    ), 
                    'light_highlight' => array(
                        'label' => 'Highlight Background',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'light_text' => array(
                        'label' => 'Text color',
                        'type'  => 'color',
                        'default' => ''
                    ), 
                    'light_bold' => array(
                        'label' => 'Heading / Title color',
                        'type'  => 'color',
                        'default' => ''
                    ), 
                    'light_sidebar' => array(
                        'label' => 'Sidebar Background',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'light_border' => array(
                        'label' => 'Border color',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'light_darkborder' => array(
                        'label' => 'Dark Border',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'light_shadow' => array(
                        'label' => 'Shadow color',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'light_dark' => array(
                        'label' => 'Darker Background',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'light_light' => array(
                        'label' => 'Lighter Background',
                        'type'  => 'color',
                        'default' => ''
                    ),  
                ),
		        'vibebp_dark_colors' => array( 
                    'dark_primary' => array(
                        'label' => 'Primary Color',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'dark_primarycolor' => array(
                        'label' => 'Text Color on  Primary background',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    
                    'dark_body' => array(
                        'label' => 'Body Background',
                        'type'  => 'color',
                        'default' => ''
                    ), 
                    'dark_highlight' => array(
                        'label' => 'Highlight Background',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'dark_text' => array(
                        'label' => 'Text color',
                        'type'  => 'color',
                        'default' => ''
                    ), 
                    'dark_bold' => array(
                        'label' => 'Heading / Title color',
                        'type'  => 'color',
                        'default' => ''
                    ), 
                    'dark_sidebar' => array(
                        'label' => 'Sidebar Background',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'dark_border' => array(
                        'label' => 'Border color',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'dark_darkborder' => array(
                        'label' => 'Dark Border',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'dark_shadow' => array(
                        'label' => 'Shadow color',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'dark_dark' => array(
                        'label' => 'Darker Background',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'dark_dark' => array(
                        'label' => 'darker Background',
                        'type'  => 'color',
                        'default' => ''
                    ),  
                )
			)
		));
	}

    function customizer_slick($settings){
        $settings['controls']['vibebp_general_settings']['login_image']=array(
            'label' => 'Login screen featured image',
            'type'  => 'image',
            'default' => ''
        );
        return $settings;
    }

	function vibebp_customize($wp_customize) {


	    $vibe_customizer = $this->generate_controls();


	    $wp_customize->add_panel( 
			'vibebp_settings',
			array(
				'priority'       => 10001,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '',
				'title'          => __('VibeBP Settings', 'mytheme'),
				'description'    => __('VibeBP Settings', 'mytheme'),
			)
		);

		$i=9991; // Show sections after the WordPress default sections
	    if(isset($vibe_customizer) && is_Array($vibe_customizer)){
	        foreach($vibe_customizer['sections'] as $key=>$value){
	        	

	            $wp_customize->add_section( $key, array(
	            'title'          => $value,
	            'panel'			 => 'vibebp_settings',
	            'priority'       => $i,
	        ) );
	            $i = $i+4;
	        }
	    }
	    if(isset($vibe_customizer) && is_array($vibe_customizer)){
		    foreach($vibe_customizer['controls'] as $section => $settings){ 
		    	$i=1;
		        foreach($settings as $control => $type){
		            $i=$i+2;
		            $wp_customize->add_setting( 'vibebp_customizer['.$control.']', array(
	                    'label'         => $type['label'],
	                    'type'           => 'option',
	                    'capability'     => 'edit_theme_options',
	                    'transport'  => 'refresh',
	                    'sanitize_callback'=> 'vibebp_sanitizer',
	                    'default'       => (empty($type['default'])?'':$type['default'])
	                ) );
		            
		            switch($type['type']){
		                case 'color':
	                        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $control, array(
	                        'label'   => $type['label'],
	                        'section' => $section,
	                        'settings'   => 'vibebp_customizer['.$control.']',
	                        'priority'       => $i
	                        ) ) );            
	                    break;  
                        case 'image':
                            $wp_customize->add_control(
                               new WP_Customize_Image_Control(
                                   $wp_customize,
                                   $control,
                                   array(
                                       'label'   => $type['label'],
                                        'section' => $section,
                                        'settings'   => 'vibebp_customizer['.$control.']',
                                        'priority'       => $i
                                   )
                               )
                           );
                        break; 
		                case 'select':
		                        $wp_customize->add_control( $control, array(
	                                'label'   => $type['label'],
	                                'section' => $section,
	                                'settings'   => 'vibebp_customizer['.$control.']',
	                                'priority'   => $i,
	                                'type'    => 'select',
	                                'choices'    => (empty($type['choices'])?'':$type['choices'])                       
	                                ) );
		                break;
                        case 'text':
                                $wp_customize->add_control( $control, array(
                                    'label'   => $type['label'],
                                    'section' => $section,
                                    'settings'   => 'vibebp_customizer['.$control.']',
                                    'priority'   => $i,
                                    'type'    => 'textarea',
                                    'choices'    => (empty($type['choices'])?'':$type['choices'])                       
                                    ) );
                        break;
                        case 'toggle':
                            $wp_customize->add_control( new VibeBp_Customizer_Toggle_Control( $wp_customize, $control, array(
                                    'label'       => $type['label'],
                                    'section'     => $section,
                                    'settings'   => 'vibebp_customizer['.$control.']',
                                    'priority'   => $i,
                                    'type'        => 'ios',// light, ios, flat
                            ) ) );
                        break;
		            }
		        }
		    }
		}

	}

	function generate_css(){

		$customizer=$this->get_customizer();


		if(!empty($customizer)){
			echo '<style>';
			$light = $dark = [];
			foreach($customizer as $key=>$customise){
				if(!empty($customise)){
					if(stripos($key, 'light_') !== false){
						$lkey = str_replace('light_','--',$key);
						$light[]= $lkey.':'.$customise;
                        if($lkey == 'primary'){
                            echo '.button.is-primary{background-color:'.$customise.';}';
                        }
					}else if(stripos($key, 'dark_') !== false){
						$dkey = str_replace('dark_','--',$key);
						$dark[]=$dkey.':'.$customise;
                        if($dkey == 'primary'){
                            echo '.vibebp_myprofile.dark_theme .button.is-primary{background-color:'.$customise.';}';
                        }
					}else{
                        if($key == 'loggedin_profile_header'){
                            echo '.vibebp_my_profile header,.vibebp_my_profile #headertop,.vibebp_my_profile .header_content,.vibebp_my_profile #title{display:none;}.pusher{overflow:visible;}.vibebp_my_profile #vibebp_member{padding-top:0;}';
                        }
                        if($key == 'loggedin_profile_footer'){
                            echo '.vibebp_my_profile footer,.vibebp_my_profile #footerbottom{display:none;} #vibebp_member{padding-top:0 !important;} ';
                        }
                    }
                    
				}
			}

            if(!empty($customizer['loggedin_profile_footer']) && !empty($customizer['loggedin_profile_header'])){
                echo '.vibebp_my_profile.logged-in #vibebp_member {
                        padding-top: 0;
                        width: 100vw;
                        height: 100vh;
                        position: fixed;
                        top: 0;
                        left: 0;
                        overflow-y:scroll;
                        max-width: 100%;
                    }';
            }
            if(!empty($customizer['light_primary'])  ){
                echo '.vibebp_myprofile{--plyr-color-main:'.$customizer['light_primary'].';}';
                
            }
            if(!empty($light)){
                 echo '.vibebp_myprofile{'.implode(';',$light).'}';
            }
            if(!empty($dark)){
                 echo '.vibebp_myprofile.dark_theme{'.implode(';',$dark).'}';
            }
            if(!empty($customizer['dark_primary'])){
                echo '.vibebp_myprofile.dark_theme{--plyr-color-main:'.$customizer['dark_primary'].';}';
            }
			
			echo '</style>';
		}
	}

    function elegance_theme($icon,$name){
        $customizer = $this->get_customizer();


        if(!empty($customizer['theme'])){
            switch($customizer['theme']){
                case 'elegance':
                    switch($name){
                        case 'dashboard':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-cpu"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>';
                        break;
                        case 'activity':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>';
                        break;
                        case 'groups':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>';
                        break;
                        case 'drive':
                        case 'mydrive':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-server"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>';
                        break;
                        case 'forums':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';
                        break;
                        case 'profile':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>';
                        break;
                        case 'messages':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>';
                        break;
                        case 'notifications':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>';
                        break;
                        case 'friends':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-check"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>';
                        break;
                        case 'followers':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-plus"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>';
                        break;case 'settings':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>';
                        break;case 'shop':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-bag"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>';
                        break;case 'commissions':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>';
                        break;case 'memberships':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-aperture"><circle cx="12" cy="12" r="10"></circle><line x1="14.31" y1="8" x2="20.05" y2="17.94"></line><line x1="9.69" y1="8" x2="21.17" y2="8"></line><line x1="7.38" y1="12" x2="13.12" y2="2.06"></line><line x1="9.69" y1="16" x2="3.95" y2="6.06"></line><line x1="14.31" y1="16" x2="2.83" y2="16"></line><line x1="16.62" y1="12" x2="10.88" y2="21.94"></line></svg>';
                        break;case 'kb':
                            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-help-circle"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
                        break;
                        case 'calendar':
                        case 'appointments':
                            $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
                        break;
                        case 'course':
                            $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-book-open"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>';
                        break;
                        case 'light_mode':
                            $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-sun"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';
                        break;
                        case 'dark_mode':
                            $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-moon"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>';
                        break;
                        case 'logout':
                            $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-power"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path><line x1="12" y1="2" x2="12" y2="12"></line></svg>';
                        break;
                        case 'bbb_meeting':
                        case 'zoom_meeting':
                        case 'jitsi_meeting':
                            $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-video"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>';
                        break;
                        case 'clp':
                            $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-git-branch"><line x1="6" y1="3" x2="6" y2="15"></line><circle cx="18" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><path d="M18 9a9 9 0 0 1-9 9"></path></svg>';
                        break;
                    }
                break;
                case 'simple':
                case 'slick':
                    switch($name){
                        case 'dashboard':
                            return '<svg version="1.1" width="64" height="64" viewBox="0 0 64 64" id="lni_lni-dashboard" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" width="64" height="64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M32,14.4c-17,0-30.8,15-30.8,33.4c0,1,0.8,1.8,1.8,1.8h58c1,0,1.8-0.8,1.8-1.8C62.8,29.4,49,14.4,32,14.4z M35.6,46.1l-2.7-8.6c-0.3-0.9-1.5-0.9-1.8,0l-2.7,8.6H4.8C5.6,30.4,17.5,17.9,32,17.9s26.4,12.5,27.2,28.2H35.6z"/><path d="M32,25.2c-1,0-1.8,0.8-1.8,1.8v2.6c0,1,0.8,1.8,1.8,1.8c1,0,1.8-0.8,1.8-1.8V27C33.8,26,33,25.2,32,25.2z"/><path d="M47.3,32.7l-1.6,1.7c-0.7,0.7-0.7,1.8,0,2.5c0.3,0.3,0.8,0.5,1.2,0.5c0.5,0,0.9-0.2,1.3-0.5l1.6-1.7c0.7-0.7,0.7-1.8,0-2.5C49.1,32,48,32,47.3,32.7z"/><path d="M16.7,32.7c-0.6-0.7-1.8-0.8-2.5-0.1c-0.7,0.6-0.8,1.8-0.1,2.5l1.5,1.7c0.3,0.4,0.8,0.6,1.3,0.6c0.4,0,0.8-0.1,1.2-0.4c0.7-0.6,0.8-1.8,0.1-2.5L16.7,32.7z"/></g></svg>';
                        break;
                        case 'activity':
                            return '<svg version="1.1" id="lni_lni-pulse" width="64" height="64" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M35.8,49.6C35.7,49.6,35.7,49.6,35.8,49.6c-2,0-3.6-1.2-4.2-3l-9.1-28.2c-0.1-0.2-0.3-0.3-0.5-0.3l-0.1,0c-0.2,0-0.4,0.1-0.4,0.2l-0.1,0.1L11.1,41.8c-0.7,1.6-2.3,2.6-4,2.6H3c-1,0-1.8-0.8-1.8-1.8S2,40.9,3,40.9h4c0.4,0,0.7-0.2,0.8-0.5l10.3-23.4c0.5-1.5,2.2-2.6,3.9-2.5c1.7,0,3.3,1.1,3.8,2.7l9.1,28.3c0.2,0.5,0.6,0.6,0.8,0.6c0.2,0,0.7,0,0.9-0.6l3.7-10.2c0.6-1.4,1.9-2.4,3.4-2.6l0.1,0c1.5-0.1,2.9,0.7,3.8,2.1l0.1,0.1l4.2,8.1c0.2,0.3,0.5,0.5,0.8,0.5H61c1,0,1.8,0.8,1.8,1.8S62,47,61,47h-8.3c-1.6,0-3.1-0.9-3.9-2.4l-4.2-8c-0.1-0.2-0.3-0.4-0.5-0.4c-0.2,0-0.5,0.2-0.6,0.5l-3.6,10C39.2,48.4,37.6,49.6,35.8,49.6z"/></g></svg>';
                        break;
                        case 'friends':
                        case 'followers':
                            return '<svg version="1.1" id="lni_lni-users" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"  width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M21.5,36.4c6.8,0,12.3-5.5,12.3-12.3s-5.5-12.3-12.3-12.3S9.2,17.3,9.2,24.1S14.7,36.4,21.5,36.4z M21.5,15.3c4.8,0,8.8,3.9,8.8,8.8c0,4.9-3.9,8.8-8.8,8.8s-8.8-3.9-8.8-8.8C12.7,19.2,16.6,15.3,21.5,15.3z"/><path d="M21.5,40.8c-7.3,0-14.3,3-19.7,8.4c-0.7,0.7-0.7,1.8,0,2.5C2.1,52,2.6,52.2,3,52.2c0.5,0,0.9-0.2,1.2-0.5c4.7-4.8,10.8-7.4,17.2-7.4c6.3,0,12.4,2.6,17.2,7.4c0.7,0.7,1.8,0.7,2.5,0c0.7-0.7,0.7-1.8,0-2.5C35.7,43.8,28.7,40.8,21.5,40.8z"/><path d="M47.8,36.4c3.9,0,7-3.2,7-7s-3.2-7-7-7s-7,3.2-7,7S43.9,36.4,47.8,36.4z M47.8,25.8c1.9,0,3.5,1.6,3.5,3.5s-1.6,3.5-3.5,3.5s-3.5-1.6-3.5-3.5S45.9,25.8,47.8,25.8z"/><path d="M62.2,46.5c-5.3-5-12.7-6.9-20.1-5c-0.9,0.2-1.5,1.2-1.3,2.1c0.2,0.9,1.2,1.5,2.1,1.3c6.2-1.6,12.4,0,16.8,4.2c0.3,0.3,0.8,0.5,1.2,0.5c0.5,0,0.9-0.2,1.3-0.6C62.9,48.3,62.9,47.2,62.2,46.5z"/></g></svg>';
                        break;
                        case 'groups':
                            return '<svg version="1.1" id="lni_lni-network" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"  width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M31.9,17.9c3.9,0,7-3.2,7-7s-3.2-7-7-7c-3.9,0-7,3.2-7,7S28,17.9,31.9,17.9z M31.9,7.3c2,0,3.5,1.6,3.5,3.5s-1.6,3.5-3.5,3.5c-2,0-3.5-1.6-3.5-3.5S29.9,7.3,31.9,7.3z"/><path d="M23.2,28.2c2.2-2.4,5.4-3.8,8.8-3.8c3.3,0,6.5,1.4,8.8,3.8c0.3,0.4,0.8,0.6,1.3,0.6c0.4,0,0.9-0.2,1.2-0.5c0.7-0.7,0.8-1.8,0.1-2.5c-2.9-3.1-7-5-11.4-5c-4.3,0-8.4,1.8-11.4,5c-0.7,0.7-0.6,1.8,0.1,2.5C21.5,28.9,22.6,28.9,23.2,28.2z"/><path d="M13.4,49.6c3.9,0,7-3.2,7-7s-3.2-7-7-7s-7,3.2-7,7S9.5,49.6,13.4,49.6z M13.4,39c2,0,3.5,1.6,3.5,3.5s-1.6,3.5-3.5,3.5s-3.5-1.6-3.5-3.5S11.4,39,13.4,39z"/><path d="M13.1,52.3c-4.3,0-8.4,1.8-11.4,5c-0.7,0.7-0.6,1.8,0.1,2.5c0.7,0.7,1.8,0.6,2.5-0.1c2.2-2.4,5.4-3.8,8.8-3.8c3.3,0,6.5,1.4,8.8,3.8c0.3,0.4,0.8,0.6,1.3,0.6c0.4,0,0.9-0.2,1.2-0.5c0.7-0.7,0.8-1.8,0.1-2.5C21.5,54.1,17.4,52.3,13.1,52.3z"/><path d="M50.4,49.6c3.9,0,7-3.2,7-7s-3.2-7-7-7s-7,3.2-7,7S46.5,49.6,50.4,49.6z M50.4,39c2,0,3.5,1.6,3.5,3.5s-1.6,3.5-3.5,3.5s-3.5-1.6-3.5-3.5S48.5,39,50.4,39z"/><path d="M62.3,57.3c-2.9-3.1-7-5-11.4-5c-4.3,0-8.4,1.8-11.4,5c-0.7,0.7-0.6,1.8,0.1,2.5c0.7,0.7,1.8,0.6,2.5-0.1c2.2-2.4,5.4-3.8,8.8-3.8s6.5,1.4,8.8,3.8c0.3,0.4,0.8,0.6,1.3,0.6c0.4,0,0.9-0.2,1.2-0.5C62.9,59.1,62.9,58,62.3,57.3z"/><path d="M37.9,38.4l-4.2-1.9V32c0-1-0.8-1.8-1.7-1.8S30.1,31,30.1,32v4.3l-4.3,2.1c-0.9,0.4-1.2,1.5-0.8,2.3c0.4,0.9,1.5,1.2,2.3,0.8l4.4-2.1l4.7,2.1c0.2,0.1,0.5,0.2,0.7,0.2c0.7,0,1.3-0.4,1.6-1C39.2,39.8,38.8,38.8,37.9,38.4z"/></g></svg>';
                            
                        break;
                        case 'drive':
                            return '<svg version="1.1" id="lni_lni-cloud-download" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"  width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M36.3,35.2l-2.5,2.6v-8.5c0-1-0.8-1.8-1.8-1.8c-1,0-1.8,0.8-1.8,1.8v8.5l-2.5-2.6c-0.7-0.7-1.8-0.7-2.5,0c-0.7,0.7-0.7,1.8,0,2.5l4.9,5.1c0.5,0.5,1.1,0.8,1.8,0.8c0.7,0,1.3-0.3,1.8-0.8l4.9-5.1c0.7-0.7,0.7-1.8,0-2.5C38,34.5,36.9,34.5,36.3,35.2z"/><path d="M57.8,23.7c-2.7-2.9-6.6-4.9-10.6-5.6c-2.2-3.5-5.5-6.1-9.3-7.4c-1.7-0.6-3.7-1-5.8-1c-9.6,0-17.5,7.5-17.9,16.9C6.9,27.2,1.3,33.2,1.3,40.4c0,7.6,6.3,13.8,14.1,13.9c0,0,0,0,0,0h28.8c10.3,0,18.6-8.2,18.6-18.2C62.8,31.5,61,27.1,57.8,23.7z M44.1,50.8H15.4c-6,0-10.6-4.6-10.6-10.4S9.4,30,15.4,30h0.5c1,0,1.8-0.8,1.8-1.8v-1.1c0-7.7,6.5-14,14.4-14c1.7,0,3.2,0.3,4.6,0.8c3.3,1.1,6.1,3.5,7.9,6.6c0.3,0.5,0.8,0.8,1.3,0.9c3.6,0.4,7,2,9.3,4.6c2.6,2.8,4,6.3,4,10C59.3,44.2,52.5,50.8,44.1,50.8z"/></g></svg>';
                        break;
                        case 'forums':
                            return '<svg version="1.1" id="lni_lni-comments" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"  width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M18.3,22.9c-3.4,0-6.2,2.8-6.2,6.2c0,3.4,2.8,6.2,6.2,6.2s6.2-2.8,6.2-6.2C24.5,25.7,21.7,22.9,18.3,22.9z M18.3,31.8c-1.5,0-2.7-1.2-2.7-2.7c0-1.5,1.2-2.7,2.7-2.7s2.7,1.2,2.7,2.7C21,30.6,19.7,31.8,18.3,31.8z"/><path d="M32,22.9c-3.4,0-6.2,2.8-6.2,6.2c0,3.4,2.8,6.2,6.2,6.2s6.2-2.8,6.2-6.2C38.2,25.7,35.4,22.9,32,22.9z M32,31.8c-1.5,0-2.7-1.2-2.7-2.7c0-1.5,1.2-2.7,2.7-2.7s2.7,1.2,2.7,2.7C34.7,30.6,33.5,31.8,32,31.8z"/><path d="M45.8,22.9c-3.4,0-6.2,2.8-6.2,6.2c0,3.4,2.8,6.2,6.2,6.2s6.2-2.8,6.2-6.2C52,25.7,49.2,22.9,45.8,22.9z M45.8,31.8c-1.5,0-2.7-1.2-2.7-2.7c0-1.5,1.2-2.7,2.7-2.7s2.7,1.2,2.7,2.7C48.5,30.6,47.2,31.8,45.8,31.8z"/><path d="M56.8,10H7.2c-3.3,0-6,2.7-6,6V48c0,2.2,1.1,4.1,3,5.2c0.9,0.5,2,0.8,3,0.8c1,0,2-0.3,3-0.8l8.8-5.1c0.4-0.2,0.8-0.3,1.2-0.3h36.5c3.3,0,6-2.7,6-6V16C62.8,12.7,60.1,10,56.8,10z M59.3,41.8c0,1.4-1.1,2.5-2.5,2.5H20.3c-1,0-2.1,0.3-3,0.8l-8.8,5.1l0,0c-0.8,0.4-1.7,0.4-2.5,0c-0.8-0.4-1.2-1.2-1.2-2.1V16c0-1.4,1.1-2.5,2.5-2.5h49.6c1.4,0,2.5,1.1,2.5,2.5V41.8z"/></g></svg>';
                        break;
                        case 'profile':
                            return '<svg version="1.1" id="lni_lni-user" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M32,36.4c8.2,0,14.9-6.7,14.9-14.9S40.2,6.5,32,6.5s-14.9,6.7-14.9,14.9S23.8,36.4,32,36.4z M32,10c6.3,0,11.4,5.1,11.4,11.4c0,6.3-5.1,11.4-11.4,11.4c-6.3,0-11.4-5.1-11.4-11.4C20.6,15.2,25.7,10,32,10z"/><path d="M62.1,54.4c-8.3-7.1-19-11-30.1-11s-21.8,3.9-30.1,11C1.1,55,1,56.1,1.7,56.9c0.6,0.7,1.7,0.8,2.5,0.2c7.7-6.5,17.6-10.1,27.9-10.1s20.2,3.6,27.9,10.1c0.3,0.3,0.7,0.4,1.1,0.4c0.5,0,1-0.2,1.3-0.6C63,56.1,62.9,55,62.1,54.4z"/></g></svg>';
                        break;
                        case 'messages':
                            return '<svg version="1.1" id="lni_lni-envelope" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><path d="M57,10.9H7c-3.2,0-5.8,2.6-5.8,5.8v30.7c0,3.2,2.6,5.8,5.8,5.8h50c3.2,0,5.8-2.6,5.8-5.8V16.7C62.8,13.5,60.2,10.9,57,10.9z M57,14.4c0.5,0,0.9,0.1,1.3,0.4L33.4,29.9c-0.9,0.5-1.9,0.5-2.8,0L5.7,14.8c0.4-0.2,0.8-0.4,1.3-0.4H57z M57,49.6H7c-1.2,0-2.3-1-2.3-2.3v-29l24,14.6c1,0.6,2.1,0.9,3.2,0.9c1.1,0,2.2-0.3,3.2-0.9l24-14.6v29C59.3,48.6,58.2,49.6,57,49.6z"/></svg>';
                        break;
                        case 'notifications':
                            return '<svg version="1.1" id="lni_lni-warning" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M61.1,42L38.9,11.8c-1.6-2.2-4.2-3.5-6.9-3.5c-2.8,0-5.3,1.3-6.9,3.5L2.9,42c-1.9,2.6-2.2,6.1-0.7,9c1.5,2.9,4.4,4.7,7.7,4.7h44.3c3.3,0,6.2-1.8,7.7-4.7S63,44.6,61.1,42z M58.7,49.4c-0.9,1.7-2.6,2.8-4.5,2.8H9.9c-2,0-3.7-1-4.5-2.8c-0.9-1.7-0.7-3.7,0.4-5.3l22.1-30.2c1-1.3,2.5-2.1,4.1-2.1c1.6,0,3.1,0.8,4.1,2.1l22.1,30.2C59.4,45.7,59.6,47.6,58.7,49.4z"/><path d="M32,38c1,0,1.8-0.8,1.8-1.8V25c0-1-0.8-1.8-1.8-1.8S30.2,24,30.2,25v11.3C30.2,37.2,31,38,32,38z"/><path d="M32,41.3c-1.6,0-2.9,1.3-2.9,2.9c0,1.6,1.3,2.9,2.9,2.9c1.6,0,2.9-1.3,2.9-2.9C34.9,42.6,33.6,41.3,32,41.3z"/></g></svg>';
                        break;case 'settings':
                        case 'setting':
                        return '<svg version="1.1" id="lni_lni-cog" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M32.1,19.7c-6.8,0-12.3,5.5-12.3,12.3c0,6.8,5.5,12.3,12.3,12.3c6.8,0,12.3-5.5,12.3-12.3C44.3,25.3,38.8,19.7,32.1,19.7z M32.1,40.8c-4.8,0-8.8-3.9-8.8-8.8c0-4.9,3.9-8.8,8.8-8.8s8.8,3.9,8.8,8.8C40.8,36.9,36.9,40.8,32.1,40.8z"/><path d="M61.5,38.9l-6.2-3.4c0.2-1.3,0.3-2.5,0.3-3.7c0-1.4-0.1-2.6-0.3-3.8l6.3-3.5c1.1-0.6,1.5-1.9,1-3.1l-6-10.1c-0.6-1-1.9-1.4-3.1-1L47,13.9c-0.4-0.3-0.8-0.7-1.2-1c-1.1-1-2.2-1.9-3.7-2.6c-0.3-0.1-0.5-0.5-0.5-0.8V5.6c0-2.4-2-4.4-4.4-4.4H26.8c-2.4,0-4.4,2-4.4,4.4l0,4c0,0.3-0.2,0.6-0.5,0.8c-1.5,0.7-2.6,1.6-3.8,2.7c-0.3,0.3-0.7,0.6-1.1,0.9l-5.8-3.3c-1.9-1-3.2-0.3-3.8,0.7l-5.7,9.8c-0.3,0.6-0.4,1.3-0.2,1.9c0.2,0.6,0.6,1.2,1.2,1.5l6.2,3.5c-0.2,1.1-0.3,2.3-0.3,3.7c0,1.3,0.1,2.5,0.2,3.7l0,0L2.4,39c-1.1,0.6-1.5,1.9-1,3.1l5.9,10.1c0.6,1,1.9,1.4,3.1,1l6.5-3.6c0.4,0.3,0.8,0.6,1.1,0.9c1.2,1,2.2,1.9,3.8,2.6c0.3,0.1,0.5,0.5,0.5,0.8v4.4c0,2.4,2,4.4,4.4,4.4h10.5c2.4,0,4.4-2,4.4-4.4v-4.6c0-0.3,0.2-0.6,0.5-0.8c1.5-0.7,2.5-1.6,3.7-2.6c0.4-0.3,0.7-0.6,1.1-1l5.8,3.3c1.9,1,3.2,0.3,3.7-0.6l5.8-9.8c0.3-0.6,0.4-1.2,0.3-1.9C62.5,39.7,62,39.2,61.5,38.9z M54.1,49.4l-5.5-3.1c-1.2-0.7-2.7-0.5-3.7,0.3c-0.5,0.4-0.9,0.8-1.3,1.1c-1.1,0.9-1.8,1.6-2.9,2.1c-1.5,0.7-2.5,2.3-2.5,3.9v4.6c0,0.5-0.4,0.9-0.9,0.9H26.8c-0.5,0-0.9-0.4-0.9-0.9v-4.4c0-1.7-1-3.2-2.5-3.9c-1.1-0.5-2-1.2-3-2.1c-0.4-0.3-0.8-0.7-1.3-1.1C18.5,46.3,17.7,46,17,46c-0.6,0-1.1,0.1-1.6,0.4l-5.5,3.1l-4.7-8l5.3-3c1.3-0.7,1.9-2.1,1.8-3.5c-0.1-1-0.2-2.1-0.2-3.2c0-1.2,0.1-2.2,0.2-3.1c0.3-1.5-0.4-3-1.7-3.7l-5.3-2.9l4.7-8l5.5,3.1c1.2,0.7,2.7,0.5,3.7-0.3c0.4-0.4,0.8-0.7,1.2-1c1.1-0.9,1.9-1.6,3-2.2c1.5-0.7,2.5-2.3,2.5-3.9l0-4c0-0.5,0.4-0.9,0.9-0.9h10.5c0.5,0,0.9,0.4,0.9,0.9v3.9c0,1.7,1,3.2,2.5,3.9c1.1,0.5,1.9,1.2,2.9,2.1c0.4,0.4,0.9,0.7,1.4,1.1c1,0.8,2.5,1,3.7,0.3l5.5-3.1l4.7,8l-5.3,2.9c-1.3,0.7-2,2.2-1.7,3.7c0.2,0.9,0.2,2,0.2,3.1c0,1-0.1,2.1-0.3,3.2c-0.3,1.5,0.5,3,1.8,3.7l5.2,2.8L54.1,49.4z"/></g></svg>';
                        case 'posts':
                        case 'vibe_blog':
                            return '<svg version="1.1" id="lni_lni-pencil" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><path d="M61.9,12.8c-3.4-3.5-6.8-7.1-10.4-10.5c-0.6-0.6-1.3-1-2.1-1c-0.8,0-1.5,0.3-2.1,0.8l-39,38.8c-0.5,0.5-0.9,1.2-1.2,1.9L1.4,60.2c-0.2,0.6-0.1,1.3,0.2,1.8c0.2,0.3,0.7,0.8,1.7,0.8h0.3l17.6-5.9c0.7-0.2,1.3-0.6,1.9-1.2l38.8-38.8c0.6-0.6,0.9-1.3,0.9-2.1C62.7,14.1,62.4,13.3,61.9,12.8z M20.6,53.2c-0.1,0.1-0.3,0.3-0.5,0.3L5.6,58.4l4.8-14.4c0.1-0.2,0.2-0.4,0.3-0.5l28.9-28.8l9.8,9.8L20.6,53.2z M51.9,21.9l-9.8-9.8L49.3,5c3.3,3.2,6.6,6.6,9.7,9.8L51.9,21.9z"/></svg>';
                        break;
                        break;case 'shop':
                            return '<svg version="1.1" id="lni_lni-shopping-basket" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M57.3,19.7h-3L47.5,2.4c-0.4-0.9-1.4-1.3-2.3-1c-0.9,0.4-1.3,1.4-1,2.3l6.3,16.1H13.4l6.3-16.1c0.4-0.9-0.1-1.9-1-2.3c-0.9-0.4-1.9,0.1-2.3,1L9.6,19.7h-3c-1.5,0-2.8,1.2-2.8,2.8V31c0,1.5,1.2,2.8,2.8,2.8h0.1l2.6,22.8c0.4,3.5,3.4,6.2,7,6.2h31.2c3.6,0,6.6-2.7,7-6.2l2.6-22.8h0.1c1.5,0,2.8-1.2,2.8-2.8v-8.5C60.1,21,58.9,19.7,57.3,19.7z M7.4,23.2h49.2v7H7.4V23.2z M51.1,56.1c-0.2,1.8-1.7,3.1-3.5,3.1H16.4c-1.8,0-3.3-1.3-3.5-3.1l-2.6-22.4h43.4L51.1,56.1z"/><path d="M21.5,50.8c1,0,1.8-0.8,1.8-1.8v-7.7c0-1-0.8-1.8-1.8-1.8s-1.8,0.8-1.8,1.8v7.7C19.7,50,20.5,50.8,21.5,50.8z"/><path d="M42.5,50.8c1,0,1.8-0.8,1.8-1.8v-7.7c0-1-0.8-1.8-1.8-1.8s-1.8,0.8-1.8,1.8v7.7C40.8,50,41.6,50.8,42.5,50.8z"/></g></svg>';
                        break;case 'memberships':
                            return '<svg version="1.1" id="lni_lni-license" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M17.4,35.2c3.6,0,6.6-3,6.6-6.6S21,22,17.4,22s-6.6,3-6.6,6.6S13.8,35.2,17.4,35.2z M17.4,25.5c1.7,0,3.1,1.4,3.1,3.1s-1.4,3.1-3.1,3.1s-3.1-1.4-3.1-3.1S15.7,25.5,17.4,25.5z"/><path d="M49,41.5H13.7c-1,0-1.8,0.8-1.8,1.8s0.8,1.8,1.8,1.8H49c1,0,1.8-0.8,1.8-1.8S50,41.5,49,41.5z"/><path d="M49,22.1h-9.7c-1,0-1.8,0.8-1.8,1.8s0.8,1.8,1.8,1.8H49c1,0,1.8-0.8,1.8-1.8S50,22.1,49,22.1z"/><path d="M49,31.8h-9.7c-1,0-1.8,0.8-1.8,1.8c0,1,0.8,1.8,1.8,1.8H49c1,0,1.8-0.8,1.8-1.8C50.8,32.6,50,31.8,49,31.8z"/><path d="M57,10.8H7c-3.2,0-5.8,2.6-5.8,5.8v30.8c0,3.2,2.6,5.8,5.8,5.8h50c3.2,0,5.8-2.6,5.8-5.8V16.6C62.8,13.4,60.2,10.8,57,10.8z M59.3,47.4c0,1.2-1,2.3-2.3,2.3H7c-1.2,0-2.3-1-2.3-2.3V16.6c0-1.2,1-2.3,2.3-2.3h50c1.2,0,2.3,1,2.3,2.3V47.4z"/></g></svg>';
                        break;case 'kb':
                            return '<svg version="1.1" id="lni_lni-agenda" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><path d="M47,1.3H17c-2.6,0-4.8,2.1-4.8,4.8v45.5c0,2.5,1.9,4.5,4.3,4.7v3.7c0,0.9,1.1,1.4,1.8,0.8l0.1-0.1c0.4-0.3,1-0.3,1.4,0l0.1,0.1c0.7,0.6,1.8,0.1,1.8-0.8v-3.7H47c2.6,0,4.8-2.1,4.8-4.8V6C51.8,3.4,49.6,1.3,47,1.3z M40.4,20.2h7.9v14.5h-7.9V20.2z M36.9,34.7H15.7V20.2h21.2V34.7z M48.3,6v10.7h-7.9V4.8H47C47.7,4.8,48.3,5.3,48.3,6z M17,4.8h19.9v11.9H15.7V6C15.7,5.3,16.3,4.8,17,4.8z M15.7,51.5V38.2h21.2v14.5H17C16.3,52.7,15.7,52.2,15.7,51.5z M47,52.7h-6.6V38.2h7.9v13.3C48.3,52.2,47.7,52.7,47,52.7z"/></svg>';
                        break;
                        case 'commissions':
                            return '<svg version="1.1" id="lni_lni-investment" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M62.3,41.4l-4.7-4.9c-0.5-0.5-1.3-0.7-2-0.4l-5.1,2.4c-0.9,0.4-1.3,1.5-0.8,2.3c0.4,0.9,1.5,1.2,2.3,0.8l1.8-0.8C50.2,50.2,41.7,56.4,32,56.4c-10.1,0-18.9-6.7-22.1-16.7c-0.3-0.9-1.3-1.4-2.2-1.1c-0.9,0.3-1.4,1.3-1.1,2.2C10.2,52.2,20.4,59.9,32,59.9c11.4,0,21.5-7.4,25.3-18.6l2.4,2.5c0.3,0.4,0.8,0.5,1.3,0.5c0.4,0,0.9-0.2,1.2-0.5C62.9,43.2,62.9,42.1,62.3,41.4z"/><path d="M8.5,30.5l5.5-3.1c0.8-0.5,1.1-1.5,0.7-2.4c-0.5-0.8-1.5-1.1-2.4-0.7l-2.8,1.6C12.2,15.1,21.4,7.6,32,7.6c10.7,0,20,7.6,22.7,18.5c0.2,0.9,1.2,1.5,2.1,1.3c0.9-0.2,1.5-1.2,1.3-2.1C55.1,12.9,44.3,4.1,32,4.1c-12.2,0-22.7,8.5-25.9,20.7l-1.9-2c-0.7-0.7-1.8-0.7-2.5-0.1c-0.7,0.7-0.7,1.8-0.1,2.5l4.7,4.9c0.3,0.4,0.8,0.5,1.3,0.5C7.9,30.7,8.2,30.6,8.5,30.5z"/><path d="M31.9,44.8c1,0,1.8-0.8,1.8-1.8v-1.3h1c3.2,0,5.8-2.6,5.8-5.8c0-3.2-2.6-5.8-5.8-5.8h-5.2c-1.3,0-2.3-1-2.3-2.3s1-2.3,2.3-2.3h8.3c1,0,1.8-0.8,1.8-1.8s-0.8-1.8-1.8-1.8h-4.1v-1.3c0-1-0.8-1.8-1.8-1.8s-1.8,0.8-1.8,1.8v1.3h-0.7c-3.2,0-5.8,2.6-5.8,5.8s2.6,5.8,5.8,5.8h5.2c1.3,0,2.3,1,2.3,2.3c0,1.3-1,2.3-2.3,2.3H26c-1,0-1.8,0.8-1.8,1.8s0.8,1.8,1.8,1.8h4.1v1.3C30.1,44.1,30.9,44.8,31.9,44.8z"/></g></svg>';
                        break;
                        case 'calendar':
                        case 'appointments':
                            return '<svg version="1.1" id="lni_lni-calendar" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M17.8,37.8h-3.3c-0.6,0-1,0.4-1,1V42c0,0.6,0.4,1,1,1h3.3c0.6,0,1-0.4,1-1v-3.2C18.8,38.2,18.4,37.8,17.8,37.8z"/><path d="M28.4,37.8h-3.3c-0.6,0-1,0.4-1,1V42c0,0.6,0.4,1,1,1h3.3c0.6,0,1-0.4,1-1v-3.2C29.4,38.2,28.9,37.8,28.4,37.8z"/><path d="M38.9,37.8h-3.3c-0.6,0-1,0.4-1,1V42c0,0.6,0.4,1,1,1h3.3c0.6,0,1-0.4,1-1v-3.2C39.9,38.2,39.5,37.8,38.9,37.8z"/><path d="M49.5,37.8h-3.3c-0.6,0-1,0.4-1,1V42c0,0.6,0.4,1,1,1h3.3c0.6,0,1-0.4,1-1v-3.2C50.5,38.2,50,37.8,49.5,37.8z"/><path d="M17.8,48.2h-3.3c-0.6,0-1,0.4-1,1v3.2c0,0.6,0.4,1,1,1h3.3c0.6,0,1-0.4,1-1v-3.2C18.8,48.6,18.4,48.2,17.8,48.2z"/><path d="M28.4,48.2h-3.3c-0.6,0-1,0.4-1,1v3.2c0,0.6,0.4,1,1,1h3.3c0.6,0,1-0.4,1-1v-3.2C29.4,48.6,28.9,48.2,28.4,48.2z"/><path d="M38.9,48.2h-3.3c-0.6,0-1,0.4-1,1v3.2c0,0.6,0.4,1,1,1h3.3c0.6,0,1-0.4,1-1v-3.2C39.9,48.6,39.5,48.2,38.9,48.2z"/><path d="M49.5,48.2h-3.3c-0.6,0-1,0.4-1,1v3.2c0,0.6,0.4,1,1,1h3.3c0.6,0,1-0.4,1-1v-3.2C50.5,48.6,50,48.2,49.5,48.2z"/><path d="M57,15.6H33.5v-3.3c2.3-0.6,4.1-2.8,4.1-5.3c0-3-2.5-5.5-5.6-5.5c-3.1,0-5.6,2.5-5.6,5.5c0,2.5,1.7,4.6,4.1,5.3v3.3H7c-3,0-5.5,2.5-5.5,5.5V57c0,3,2.5,5.5,5.5,5.5h50c3,0,5.5-2.5,5.5-5.5V21.1C62.5,18.1,60,15.6,57,15.6z M29.4,7c0-1.4,1.1-2.5,2.6-2.5c1.4,0,2.6,1.1,2.6,2.5c0,1.4-1.1,2.5-2.6,2.5C30.6,9.5,29.4,8.3,29.4,7z M7,18.6h50c1.4,0,2.5,1.1,2.5,2.5v7.4h-55v-7.4C4.5,19.7,5.6,18.6,7,18.6z M57,59.5H7c-1.4,0-2.5-1.1-2.5-2.5V31.5h55V57C59.5,58.4,58.4,59.5,57,59.5z"/></g></svg>';
                        break;
                        case 'course':
                            $icon = '<svg version="1.1" id="lni_lni-graduation" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><path d="M62.1,34.5l-4.5-4.6v-4.4c0.7-0.6,1-1.5,1-2.4c0-1.3-0.7-2.4-1.9-3L31.4,8.6c-0.8-0.4-1.8-0.4-2.6,0L3.2,19.9c-1.2,0.5-1.9,1.7-1.9,3s0.7,2.5,1.9,3l9,4.1v12.6c0,7.2,5.8,13.1,13,13.1h9.4c7.2,0,13-5.9,13-13.1V30.2l5.3-2.4l1.1-0.5v2.8l-4.3,4.4c-0.9,0.9-0.9,2.4,0,3.3l4.5,4.6c0.4,0.4,1,0.7,1.6,0.7c0.6,0,1.2-0.3,1.6-0.7l4.5-4.6C63,36.8,63,35.4,62.1,34.5z M44.2,42.5c0,5.3-4.3,9.6-9.5,9.6h-9.4c-5.3,0-9.5-4.3-9.5-9.6v-11l13.2,5.9c0.4,0.2,0.9,0.3,1.3,0.3c0.4,0,0.9-0.1,1.3-0.3l12.6-5.7V42.5z M51.5,24.6l-21.3,9.6L5.1,22.9l25-11l24.8,11.2L51.5,24.6z M55.9,39l-2.8-2.9l2.8-2.9l2.8,2.9L55.9,39z"/></svg>';
                        break;
                        case 'light_mode':
                            $icon = '<svg version="1.1" id="lni_lni-sun" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M32,18.5c-7.5,0-13.5,6.1-13.5,13.5S24.5,45.5,32,45.5S45.5,39.5,45.5,32S39.5,18.5,32,18.5z M32,42c-5.5,0-10-4.5-10-10s4.5-10,10-10s10,4.5,10,10S37.5,42,32,42z"/><path d="M32,10c1,0,1.8-0.8,1.8-1.8V3c0-1-0.8-1.8-1.8-1.8S30.3,2,30.3,3v5.3C30.3,9.2,31,10,32,10z"/><path d="M32,54c-1,0-1.8,0.8-1.8,1.8V61c0,1,0.8,1.8,1.8,1.8s1.8-0.8,1.8-1.8v-5.3C33.8,54.8,33,54,32,54z"/><path d="M50.9,10.6l-3.3,3.3c-0.7,0.7-0.7,1.8,0,2.5c0.3,0.3,0.8,0.5,1.2,0.5s0.9-0.2,1.2-0.5l3.3-3.3c0.7-0.7,0.7-1.8,0-2.5C52.7,9.9,51.6,9.9,50.9,10.6z"/><path d="M13.9,47.6l-3.3,3.3c-0.7,0.7-0.7,1.8,0,2.5c0.3,0.3,0.8,0.5,1.2,0.5c0.4,0,0.9-0.2,1.2-0.5l3.3-3.3c0.7-0.7,0.7-1.8,0-2.5C15.7,46.9,14.6,46.9,13.9,47.6z"/><path d="M61,30.3h-5.3c-1,0-1.8,0.8-1.8,1.8s0.8,1.8,1.8,1.8H61c1,0,1.8-0.8,1.8-1.8S62,30.3,61,30.3z"/><path d="M8.3,30.3H3c-1,0-1.8,0.8-1.8,1.8S2,33.8,3,33.8h5.3c1,0,1.8-0.8,1.8-1.8S9.2,30.3,8.3,30.3z"/><path d="M50.1,47.6c-0.7-0.7-1.8-0.7-2.5,0c-0.7,0.7-0.7,1.8,0,2.5l3.3,3.3c0.3,0.3,0.8,0.5,1.2,0.5c0.4,0,0.9-0.2,1.2-0.5c0.7-0.7,0.7-1.8,0-2.5L50.1,47.6z"/><path d="M13.1,10.6c-0.7-0.7-1.8-0.7-2.5,0c-0.7,0.7-0.7,1.8,0,2.5l3.3,3.3c0.3,0.3,0.8,0.5,1.2,0.5c0.4,0,0.9-0.2,1.2-0.5c0.7-0.7,0.7-1.8,0-2.5L13.1,10.6z"/></g></svg>';
                        break;
                        case 'dark_mode':
                            $icon = '<svg version="1.1" id="lni_lni-night" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64"  width="64" height="64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><path d="M29.7,5c0,0,0.1,0,0.1,0c1.6,0.3,3.3,0.6,4.9,1.1c13.9,4.6,21.8,19.2,17.9,32.9C49.2,51.2,38,59,25.9,59c-2.7,0-5.5-0.4-8.2-1.2c-2.5-0.8-4.9-1.9-7-3.2c-0.6-0.4-0.4-1.4,0.4-1.4c11.5-0.4,22-7.8,25.6-19.1c3.2-10.2,0-20.8-7.5-27.7C28.7,5.9,29.1,5,29.7,5 M29.7,1.5L29.7,1.5c-1.8,0-3.4,1.1-4,2.8c-0.6,1.7-0.2,3.5,1.1,4.6c6.7,6.2,9.2,15.5,6.5,24.1c-3,9.6-12,16.3-22.3,16.7c-1.9,0-3.5,1.3-4,3.1c-0.5,1.8,0.3,3.8,1.9,4.7c2.6,1.6,5.2,2.8,7.8,3.6c3,0.9,6.1,1.4,9.2,1.4c14,0,26.4-9.2,30.2-22.5c4.4-15.6-4.4-31.9-20.1-37.2l0,0l0,0c-1.7-0.5-3.5-1-5.4-1.3C30.2,1.5,30,1.5,29.7,1.5L29.7,1.5z"/></svg>';
                        break;
                        case 'logout':
                            $icon = '<svg version="1.1" id="lni_lni-power-switch" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M44.1,15.6c-0.8-0.5-1.9-0.2-2.4,0.7c-0.5,0.8-0.2,1.9,0.7,2.4c6.9,3.8,11.2,11.1,11.2,19c0,11.9-9.7,21.6-21.7,21.6c-11.9,0-21.7-9.7-21.7-21.6c0-7.8,4.2-15,10.9-18.8c0.8-0.5,1.1-1.5,0.7-2.4c-0.5-0.8-1.5-1.1-2.4-0.7C11.7,20.2,6.8,28.6,6.8,37.6c0,13.9,11.3,25.1,25.2,25.1c13.9,0,25.2-11.3,25.2-25.1C57.2,28.4,52.2,20,44.1,15.6z"/><path d="M31.7,29.5c1,0,1.7-0.8,1.7-1.8V3c0-1-0.8-1.8-1.7-1.8S29.9,2,29.9,3v24.7C29.9,28.7,30.7,29.5,31.7,29.5z"/></g></svg>';
                        break;
                        case 'bbb_meeting':
                        case 'zoom_meeting':
                        case 'jitsi_meeting':
                            $icon = '<svg version="1.1" id="lni_lni-target-customer" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M61,30.3h-6.6c-0.9-11-9.7-19.7-20.6-20.6V3c0-1-0.8-1.8-1.8-1.8c-1,0-1.8,0.8-1.8,1.8v6.7c-10.9,0.9-19.7,9.6-20.6,20.6H3c-1,0-1.8,0.8-1.8,1.8c0,1,0.8,1.8,1.8,1.8h6.6c0.8,11.1,9.6,20,20.6,20.9V61c0,1,0.8,1.8,1.8,1.8c1,0,1.8-0.8,1.8-1.8v-6.4c11-0.9,19.8-9.8,20.6-20.9H61c1,0,1.8-0.8,1.8-1.8C62.8,31,62,30.3,61,30.3z M33.8,51.1v-0.7c0-1-0.8-1.8-1.8-1.8c-1,0-1.8,0.8-1.8,1.8v0.7c-9.1-0.8-16.3-8.2-17.1-17.4h3c1,0,1.8-0.8,1.8-1.8c0-1-0.8-1.8-1.8-1.8h-3c0.9-9,8.1-16.2,17.1-17.1v0.4c0,1,0.8,1.8,1.8,1.8c1,0,1.8-0.8,1.8-1.8v-0.4c9,0.8,16.2,8,17.1,17.1h-0.4c-1,0-1.8,0.8-1.8,1.8c0,1,0.8,1.8,1.8,1.8h0.4C50.1,42.9,42.9,50.3,33.8,51.1z"/><path d="M37.3,36.5c1.6-1.4,2.6-3.5,2.6-5.8c0-4.4-3.5-7.9-7.9-7.9s-7.9,3.5-7.9,7.9c0,2.3,1,4.4,2.6,5.8c-1.9,0.7-3.6,1.7-5.1,3c-0.7,0.6-0.8,1.7-0.1,2.5c0.3,0.4,0.8,0.6,1.3,0.6c0.4,0,0.8-0.1,1.2-0.4c2.3-2,5.1-3.1,8.1-3.1c3,0,5.9,1.1,8.1,3.1c0.7,0.6,1.8,0.6,2.5-0.1c0.6-0.7,0.6-1.8-0.1-2.5C40.9,38.2,39.2,37.2,37.3,36.5z M32,26.3c2.4,0,4.4,2,4.4,4.4c0,2.4-2,4.4-4.4,4.4c-2.4,0-4.4-2-4.4-4.4C27.6,28.2,29.6,26.3,32,26.3z"/></g></svg>';
                        break;
                        case 'clp':
                            $icon = '<svg version="1.1" id="lni_lni-graph" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="64" height="64" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M46.3,13.4c-3.3,0-6,2.7-6,6c0,0.3,0,0.6,0.1,1l-2.9,1.9l-3.2-1c-0.7-2.6-3-4.5-5.8-4.5c-3.3,0-6,2.7-6,6c0,0.6,0.1,1.3,0.3,1.8c-0.9,0.7-1.9,1.5-2.9,2.2c-1-0.8-2.3-1.3-3.7-1.3c-3.3,0-6,2.7-6,6c0,3.3,2.7,6,6,6s6-2.7,6-6c0-0.6-0.1-1.2-0.3-1.8c0.9-0.7,1.9-1.5,2.9-2.3c1,0.8,2.3,1.3,3.7,1.3c2.6,0,4.7-1.6,5.6-3.9l2.5,0.8c0.3,0.1,0.6,0.2,1,0.2c0.6,0,1.2-0.2,1.7-0.5l2.7-1.7c1.1,1.2,2.6,1.9,4.4,1.9c3.3,0,6-2.7,6-6S49.6,13.4,46.3,13.4z M16.2,33.9c-1.4,0-2.5-1.1-2.5-2.5s1.1-2.5,2.5-2.5s2.5,1.1,2.5,2.5S17.6,33.9,16.2,33.9z M28.5,25.1c-1.4,0-2.5-1.1-2.5-2.5s1.1-2.5,2.5-2.5s2.5,1.1,2.5,2.5S29.9,25.1,28.5,25.1z M46.3,21.9c-1.4,0-2.5-1.1-2.5-2.5s1.1-2.5,2.5-2.5s2.5,1.1,2.5,2.5S47.7,21.9,46.3,21.9z"/><path d="M55.4,6.4H8.6c-4.1,0-7.4,3.3-7.4,7.4v23.2c0,4.1,3.3,7.4,7.4,7.4h21.6v9.8H18.8c-1,0-1.8,0.8-1.8,1.8s0.8,1.8,1.8,1.8h26.4c1,0,1.8-0.8,1.8-1.8s-0.8-1.8-1.8-1.8H33.8v-9.8h21.6c4.1,0,7.4-3.3,7.4-7.4V13.7C62.8,9.7,59.5,6.4,55.4,6.4z M59.3,36.9c0,2.1-1.7,3.9-3.9,3.9H8.6c-2.1,0-3.9-1.7-3.9-3.9V13.7c0-2.1,1.7-3.9,3.9-3.9h46.8c2.1,0,3.9,1.7,3.9,3.9V36.9z"/></g></svg>';
                        break;
                        
                    }
                break;
            }
        }
        return $icon;
    }
}

VibeBP_Customizer::init();


add_action('customize_register', function(){
class VibeBp_Customizer_Toggle_Control extends WP_Customize_Control {
    public $type = 'ios';

    /**
     * Enqueue scripts/styles.
     *
     * @since 3.4.0
     */
    public function enqueue() {
        wp_enqueue_script( 'customizer-toggle-control', plugins_url('../assets/js/vibebp_toggle.js',__FILE__), array( 'jquery' ), rand(), true );
        wp_enqueue_style( 'customizer-toggle-control', plugins_url('../assets/css/vibebp_toggle.css',__FILE__), array(), rand() );

        $css = '
            .disabled-control-title {
                color: #a0a5aa;
            }
            input[type=checkbox].tgl-light:checked + .tgl-btn {
                background: #0085ba;
            }
            input[type=checkbox].tgl-light + .tgl-btn {
              background: #a0a5aa;
            }
            input[type=checkbox].tgl-light + .tgl-btn:after {
              background: #f7f7f7;
            }
            input[type=checkbox].tgl-ios:checked + .tgl-btn {
              background: #0085ba;
            }
            input[type=checkbox].tgl-flat:checked + .tgl-btn {
              border: 4px solid #0085ba;
            }
            input[type=checkbox].tgl-flat:checked + .tgl-btn:after {
              background: #0085ba;
            }
        ';
        wp_add_inline_style( 'pure-css-toggle-buttons', $css );
    }

    /**
     * Render the control's content.
     *
     * @author soderlind
     * @version 1.2.0
     */
    public function render_content() {
        ?>
        <label class="customize-toogle-label">
            <div style="display:flex;flex-direction: row;justify-content: flex-start;">
                <span class="customize-control-title" style="flex: 2 0 0; vertical-align: middle;"><?php echo esc_html( $this->label ); ?></span>
                <input id="cb<?php echo $this->instance_number; ?>" type="checkbox" class="tgl tgl-<?php echo $this->type; ?>" value="<?php echo esc_attr( $this->value() ); ?>"
                                        <?php
                                        $this->link();
                                        checked( $this->value() );
                                        ?>
                 />
                <label for="cb<?php echo $this->instance_number; ?>" class="tgl-btn"></label>
            </div>
            <?php if ( ! empty( $this->description ) ) : ?>
            <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>
        </label>
        <?php
    }

    /**
     * Plugin / theme agnostic path to URL
     *
     * @see https://wordpress.stackexchange.com/a/264870/14546
     * @param string $path  file path
     * @return string       URL
     */
    private function abs_path_to_url( $path = '' ) {
        $url = str_replace(
            wp_normalize_path( untrailingslashit( ABSPATH ) ),
            site_url(),
            wp_normalize_path( $path )
        );
        return esc_url_raw( $url );
    }
}
},9);