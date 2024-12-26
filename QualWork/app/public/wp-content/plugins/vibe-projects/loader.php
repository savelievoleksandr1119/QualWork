<?php
/*
Plugin Name: Vibe Projects
Plugin URI: https://vibethemes.com/
Description: Projects by VibeThemes
Version: 1.2.3.2
Author: Vibethemes
Author URI: https://vibethemes.com/
Text Domain: vibe-projects
Domain Path: /languages/
*/
if ( !defined( 'ABSPATH' ) ) exit;
/*  Copyright 2019 VibeThemes  (email: vibethemes@gmail.com) */

if( !defined('VIBE_PROJECTS_SLUG')){
	define( 'VIBE_PROJECTS_SLUG', 'projects' ); 
}
if( !defined('VIBE_PROJECTS_API_NAMESPACE')){
	define( 'VIBE_PROJECTS_API_NAMESPACE', 'vibeprojects/v1' ); 
}


if ( ! defined( 'VIBE_PROJECTS_PLUGIN_DIR' ) ) {
    define( 'VIBE_PROJECTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}


if(!defined('VIBEPROJECTS_VERSION')){
    define('VIBEPROJECTS_VERSION','1.2.3.2');
}


include_once 'includes/autoupdate.php';
include_once 'includes/projects.component.php';
include_once 'includes/class.functions.php';
include_once 'includes/class.profile.php';
include_once 'includes/class.settings.php';
include_once 'includes/class.admin.php';
include_once 'includes/api/class-projects-api.php';
include_once 'includes/api/class-boards-api.php';
include_once 'includes/api/class-cards-api.php';
include_once 'includes/api/class-list-api.php';
include_once 'includes/api/class-general-api.php';
include_once 'includes/api/class-widgets-api.php';
include_once 'includes/api/class-reports-api.php';
include_once 'includes/class.init.php';
include_once 'includes/filters.php';
include_once 'includes/class.activity.php';
include_once 'includes/class.mails.php';
include_once 'includes/class.notifications.php';
include_once 'includes/class.touchpoints.php';
include_once 'includes/class.shortcodes.php';

include_once 'includes/widgets/cardburndown.php';
include_once 'includes/widgets/myitems.php';
include_once 'includes/widgets/upcomingtasks.php';
include_once 'includes/widgets/cardmemberinfo.php';
include_once 'includes/widgets/project_by_status.php';
include_once 'includes/widgets/incomplete_tasks_by_project.php';

//Mandatory for VibeBP
register_activation_hook(__FILE__,function(){
	delete_transient('bp_rest_api_nav');
});
register_deactivation_hook(__FILE__,function(){
	delete_transient('bp_rest_api_nav');
});


add_action('plugins_loaded','vibe_projects_plugin_load_translations');
function vibe_projects_plugin_load_translations(){
    $locale = apply_filters("plugin_locale", get_locale(), 'vibe-projects');
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'vibe-projects', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;
    if ( file_exists( $mofile_global ) ) {
       load_textdomain( 'vibe-projects', $mofile_global );
       
    } else {
        load_textdomain( 'vibe-projects', $mofile_local );
    }  
}

add_action( 'admin_init', 'vibe_projects_plugin_update' );
function vibe_projects_plugin_update() {
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/autoupdate.php' );
    $config = array(
        'base'      => plugin_basename( __FILE__ ), 
        'dashboard' => true,
        'repo_uri'  => 'https://vibethemes.com/',
        'repo_slug' => 'vibe-projects',
    );

    /* Load Updater Class */
    new Vibe_Projects_Auto_Update( $config );
}
