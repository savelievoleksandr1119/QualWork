<?php
/*
Plugin Name: Vibe Zoom
Plugin URI: http://www.Vibethemes.com
Description: Integrates Zoom with wplms
Version: 1.8.9
Author: VibeThemes
Author URI: http://www.vibethemes.com
License: GPL3 ( https://www.gnu.org/licenses/gpl-3.0.en.html , Use or Modify, Copy & distribution not allowed.)
Text Domain: vibe-zoom
*/
/*= All open source attempts fail when our projects are copied even before the release. Please support the projects. =*/

if ( ! defined( 'ABSPATH' ) ) exit;


define( 'VIBE_ZOOM_VERSION', '1.8.9');

define( 'VIBE_ZOOM_API_NAMESPACE', 'vibezoom/v1'  );

if(!defined('VIBE_ZOOM_PATH'))
define( 'VIBE_ZOOM_PATH', plugin_dir_path( __FILE__ ) );
include_once 'includes/class.settings.php';
include_once 'includes/class-vibe-zoom-api.php';
include_once 'includes/vibezoom.class.php';
include_once 'includes/class.init.php';
include_once 'includes/class.api.php';
include_once 'includes/class.actions.php';
include_once 'includes/functions.php';


if(class_exists('Vibe_Zoom'))
{   
    // Installation and uninstallation hooks
    //register_activation_hook(__FILE__, array('Vibe_Zoom', 'activate'));
    //register_deactivation_hook(__FILE__, array('Vibe_Zoom', 'deactivate'));

    // instantiate the plugin class
    add_action('init',function(){
        $active_plugins =get_option( 'active_plugins' );
        if ( (

            ((in_array( 'vibe-customtypes/vibe-customtypes.php', apply_filters( 'active_plugins', $active_plugins ) ) || function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'vibe-customtypes/vibe-customtypes.php')) && 
            
                            (in_array( 'vibe-course-module/loader.php', apply_filters( 'active_plugins', $active_plugins ) ) || function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'vibe-course-module/loader.php')))

                || 
                (in_array( 'wplms_plugin/loader.php', apply_filters( 'active_plugins', $active_plugins ) ) || function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'wplms_plugin/loader.php'))
        )  &&

                    (in_array( 'buddypress/bp-loader.php', apply_filters( 'active_plugins', $active_plugins ) ) || function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'buddypress/bp-loader.php')) 
                    

           ) {
                $Vibe_Zoom = Vibe_Zoom::init();
                add_action('bp_init','vibe_zoom_show_zoommeetings_tab');
        }
        
    },2);
}

add_action('plugins_loaded','Vibe_Zoom_translations');
function Vibe_Zoom_translations(){
    $locale = apply_filters("plugin_locale", get_locale(), 'vibe-zoom');
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'bbb-wplms', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'vibe-zoom', $mofile_global );
    } else {
        load_textdomain('vibe-zoom', $mofile_local );
    }  
}


add_action( 'init', 'vibebp_zoom_plugin_update' );
function vibebp_zoom_plugin_update() {
    /* Load Plugin Updater */
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/autoupdate.php' );

    /* Updater Config */
    $config = array(
        'base'      => plugin_basename( __FILE__ ), //required
        'dashboard' => true,
        'repo_uri'  => 'https://wplms.io/',  //required
        'repo_slug' => 'vibe-zoom',  //required
    );

    /* Load Updater Class */
    new Vibe_Zoom_Auto_Update( $config );
}


function vibe_zoom_show_zoommeetings_tab(){
    include_once 'includes/class.groups.php';
}



