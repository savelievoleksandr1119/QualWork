<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if(!defined('MICRONET_THEME_FILE_INCLUDE_PATH')){
	define('MICRONET_THEME_FILE_INCLUDE_PATH',get_template_directory());
}

if(defined('MICRONET_THEME_FILE_INCLUDE_PATH')){
	// Essentials
	include_once MICRONET_THEME_FILE_INCLUDE_PATH.'/includes/config.php';
	include_once MICRONET_THEME_FILE_INCLUDE_PATH.'/includes/plugin-activation.php';
	include_once MICRONET_THEME_FILE_INCLUDE_PATH.'/includes/class.setup.php';
	include_once MICRONET_THEME_FILE_INCLUDE_PATH.'/includes/class.enqueue.php';

	include_once MICRONET_THEME_FILE_INCLUDE_PATH.'/includes/class.register.php';
	include_once MICRONET_THEME_FILE_INCLUDE_PATH.'/includes/functions.php';
	include_once MICRONET_THEME_FILE_INCLUDE_PATH.'/includes/class.init.php';
	include_once MICRONET_THEME_FILE_INCLUDE_PATH.'/includes/class.actions.php';
	include_once MICRONET_THEME_FILE_INCLUDE_PATH.'/includes/class.filters.php';
	include_once MICRONET_THEME_FILE_INCLUDE_PATH.'/includes/vibe_menu.php';

	include_once MICRONET_THEME_FILE_INCLUDE_PATH.'/setup/install.php';
	if ( class_exists( 'WP_Customize_Control' ) ) {
		include_once MICRONET_THEME_FILE_INCLUDE_PATH.'/includes/customizer/range_control.php';
	}
}

get_template_part('vibe','options');



add_action( 'after_setup_theme','micronet_translate_theme' );
function micronet_translate_theme() {
    $locale = get_locale();

    $locale_file = get_stylesheet_directory() . "/languages/";
    $template_file = get_template_directory() . "/languages/";
    $global_file = WP_LANG_DIR . "/themes/micronet/";

    // Loco translate fix
    if ( file_exists( WP_LANG_DIR."/themes/micronet-".$locale.'.mo' ) ) { 
        load_theme_textdomain( 'micronet', WP_LANG_DIR."/themes/micronet-".$locale.'.mo' );
    }else if ( file_exists( $global_file.$locale.'.mo' ) ) {
        load_theme_textdomain( 'micronet', $global_file );
    }else if ( file_exists( $locale_file.$locale.'.mo' ) ) { 
        load_theme_textdomain( 'micronet', $locale_file );
    }else {
        load_theme_textdomain( 'micronet', $template_file );
    }
}