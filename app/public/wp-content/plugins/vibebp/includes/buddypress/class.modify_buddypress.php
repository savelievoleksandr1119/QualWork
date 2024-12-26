<?php
/**
 * Action functions for BP Modifier
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Vibe Course Module
 * @version     2.0
 * @Credits     Code modified from GPL v3 Peter Shaw ( https://shawfactor.com/ )https://lhero.org/portfolio/lh-dequeue-buddypress/
 */

 if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_BP_Modify_Init{

    public static $instance;
    public static function init(){
    if ( is_null( self::$instance ) )
        self::$instance = new Vibe_BP_Modify_Init();

        return self::$instance;
    }

    private function __construct(){

    	add_action( 'bp_loaded', array($this,'bp_boot'));
    }


    function bp_boot(){
        if (!is_admin()){
            
            add_action( 'wp_print_scripts', array($this,"dequeue_scripts"), 10000 ); 
          
            add_action('wp_print_styles', array($this,"dequeue_styles"),100000);
            
            add_action( 'init', array($this,"remove_admin_bar") );
            
            add_filter("body_class", array($this,"remove_body_classes"), 1000, 2);
            
            
        }
    }

    static function should_we_deqeue(){
        
        $return = false;
        
        if (!is_user_logged_in() && !is_admin() && !bp_is_groups_component() && !bp_is_blogs_component()){
           
         $return = true;   
            
        }
        
        
        return apply_filters('vibebp_bp_deqeue', $return);
        
        
    }
    public function dequeue_scripts() {
        
        if (self::should_we_deqeue()){
                
           wp_dequeue_script( 'bp-legacy-js' );
           wp_deregister_script( 'bp-legacy-js' );
           
           wp_dequeue_script( 'bp-widget-members' );
           wp_deregister_script( 'bp-widget-members' );
           
           wp_dequeue_script( 'bp-jquery-query' );
           wp_deregister_script( 'bp-jquery-query' );
           
            wp_dequeue_script( 'bp-jquery-cookie' );
            wp_deregister_script( 'bp-jquery-cookie' );
           
            wp_dequeue_script( 'bp-jquery-scroll-to' );
            wp_deregister_script( 'bp-jquery-scroll-to' );
            
            
           wp_dequeue_script( 'activity-auto-loader' );
           wp_deregister_script( 'activity-auto-loader' );
           
          
            wp_dequeue_script( 'bp-confirm' );
            wp_deregister_script( 'bp-confirm' );
           
            wp_deregister_script( 'jquery-caret' );
            wp_deregister_script( 'bp-mentions' );
        
            
        }
        
    }
    
    
    public function dequeue_styles() {
        
        if (self::should_we_deqeue()){

                wp_dequeue_style('wpfla-style-handle');
                wp_deregister_style( 'wpfla-style-handle' );
                
                wp_dequeue_style('bp-mentions-css');
                wp_deregister_style( 'bp-mentions-css' );
                
                wp_dequeue_style('bp-mentions-css');
                wp_deregister_style( 'bp-mentions-css' );


                wp_dequeue_style('bp-legacy-css');
                wp_deregister_style( 'bp-legacy-css' );
                
                wp_dequeue_style('bp-nouveau');
                wp_deregister_style( 'bp-nouveau' );
              
               
                wp_dequeue_style('bp-legacy-css');
                wp_deregister_style( 'bp-legacy-css' );
                
                wp_dequeue_style('bp-twentyfifteen');
                wp_deregister_style( 'bp-twentyfifteen' );
                
                wp_dequeue_style('bp-activity-share');
                wp_deregister_style( 'bp-activity-share' );
                
                wp_dequeue_style('bp-xprofile-custom-field-types');
                wp_deregister_style( 'bp-xprofile-custom-field-types' );
                
                wp_dequeue_style('bp-admin-bar');
                wp_deregister_style( 'bp-admin-bar' );
            
                wp_dequeue_style('bp-twentyfifteen');
                wp_deregister_style( 'bp-twentyfifteen' );

             //not strictly buddypress but plugin that rely on it    
                wp_dequeue_style('mass_messaging_in_buddypress-frontend');
                wp_deregister_style( 'mass_messaging_in_buddypress-frontend' );

                wp_dequeue_script( 'bp-activity-share' );
                wp_deregister_script( 'bp-activity-share' );
                
                wp_dequeue_style('bp-groups-taxo-style');
                wp_deregister_style( 'bp-groups-taxo-style' );

        }
    }
    
    
    public function remove_admin_bar() { 
        
        if ( function_exists('bp_core_admin_bar') ) remove_action( 'wp_footer', 'bp_core_admin_bar', 8 );
        if ( function_exists('bp_core_admin_bar') ) remove_action( 'admin_footer', 'bp_core_admin_bar' );
        if ( function_exists('bp_core_admin_bar_css') ) {
            remove_action( 'wp_head', 'bp_core_admin_bar_css', 1 ); // priority 1 for RC2 BuddyPress release
        }
        if ( function_exists('bp_core_add_admin_css') ) remove_action( 'admin_menu', 'bp_core_add_admin_css' ); 
    }


    public function remove_body_classes($classes, $class){
        if (!is_user_logged_in() and !is_admin()){
            foreach($classes as &$str){
                if(strpos($str, "no-js") > -1){
                    $str = "";
                }
            }
        }
        return $classes;
    }   
}

Vibe_BP_Modify_Init::init();