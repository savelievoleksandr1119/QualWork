<?php
/**
 * Plugin Name: VibeBP
 * Plugin URI: https://vibethemes.com
 * Description: Convert your BuddyPress site into a PWA.
 * Author: VibeThemes
 * Author URI: https://www.vibethemes.com
 * Version: 1.9.9.7.6
 * Text Domain: vibebp
 * Domain Path: /languages
 * Tested up to: 6.5.2
 *
 * @package VibeBP
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'VIBEBP_VERSION','1.9.9.7.6');
define( 'VIBEBP_SERVICE_WORKER_VERSION', '1');
defined('VIBEBP_TOKEN') or define('VIBEBP_TOKEN', 'token');
defined('VIBEBP') or define('VIBEBP', 'VibeBP_');
define( 'VIBEBP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VIBEBP_PLUGIN_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define( 'VIBEBP_PLUGIN_FILE', __FILE__ );
define( 'VIBEBP_PLUGIN_BASE', plugin_basename( __FILE__ ) );

defined('VIBEBP_NAMESPACE') or define('VIBEBP_NAMESPACE', 'vibebp/v1');
defined('Vibe_BP_API_NAMESPACE') or define('Vibe_BP_API_NAMESPACE','vbp/v1');

defined('VIBE_BP_SETTINGS') or define('VIBE_BP_SETTINGS','vibebp_settings');

//DEFINE / REDEFINE JWT_AUTH_SECRET_KEY IN WP-CONFIG TO LOGOUT ALL USERS AND EXPIRE ALL TOKENS
if(defined('AUTH_KEY') && !defined('JWT_AUTH_SECRET_KEY')){	
	define( 'JWT_AUTH_SECRET_KEY', AUTH_KEY );	
}
//BP 12 compatibility
//add_filter('bp_core_get_query_parser',function($f){return 'legacy';});
add_Action('bp_setup_nav',function(){ global $bp; if(empty((array)$bp->displayed_user)){ $bp->displayed_user->domain=''; } },1);

if(function_exists('bp_is_active')){

require_once(dirname(__FILE__).'/includes/class.vibebp_blocks.php');
require_once(dirname(__FILE__).'/includes/bp.mailer.php');
require_once(dirname(__FILE__).'/includes/class.admin.php');
require_once(dirname(__FILE__).'/includes/class.actions.php');
require_once(dirname(__FILE__).'/includes/class.init.php');

require_once(dirname(__FILE__).'/includes/class.settings.php');

//require_once(dirname(__FILE__).'/includes/class.saas-migrator.php');

require_once(dirname(__FILE__).'/includes/class.setup_wizard.php');
require_once(dirname(__FILE__).'/includes/class.layouts.php');
require_once(dirname(__FILE__).'/includes/class.templates.php');
require_once(dirname(__FILE__).'/includes/class.register.php');
require_once(dirname(__FILE__).'/includes/class.api.php');
require_once(dirname(__FILE__).'/includes/class.elementor.php');
require_once(dirname(__FILE__).'/includes/class.customizer.php');
require_once(dirname(__FILE__).'/includes/class.filters.php');
require_once(dirname(__FILE__).'/includes/class.wallet.php');
require_once(dirname(__FILE__).'/includes/shortcodes/class.design_shortcodes.php');
require_once(dirname(__FILE__).'/includes/shortcodes/class.shortcodes.php');
require_once(dirname(__FILE__).'/includes/class.ajax.php');
require_once(dirname(__FILE__).'/includes/class.firebase.admin.token.php');
require_once(dirname(__FILE__).'/includes/class.performance.php');
require_once(dirname(__FILE__).'/includes/functions.php');
require_once(dirname(__FILE__).'/includes/class.membertypes.from.fields.php');
require_once(dirname(__FILE__).'/includes/class.woocommerce.wallet.php');

require_once(dirname(__FILE__).'/includes/class.widgets.php');
require_once(dirname(__FILE__).'/includes/class.stale.requests.php');
require_once(dirname(__FILE__).'/includes/class.membertags.php');
require_once(dirname(__FILE__).'/includes/class.menu.icon.php');
require_once(dirname(__FILE__).'/includes/buddypress/class.modify_buddypress.php');
require_once(dirname(__FILE__).'/includes/buddypress/group-metafields/class.groups.metafields.php');
}
/*
*	Include file when Api hit makes
*/	
add_action('rest_api_init',function(){ 
	require_once(dirname(__FILE__).'/'.'includes/core/JWT.php');
	require_once(dirname(__FILE__).'/'.'includes/class-vibebp-api-token.php');
},1);


if(class_exists('VibeBP_Init')){
	$init = VibeBP_Init::init();
	register_activation_hook( __FILE__, array( $init, 'install' ) );
}



function vibe_bp_api_init() {
	/* Only load the component if BuddyPress is loaded and initialized. */
	if ( defined('BP_VERSION') && version_compare( BP_VERSION, '1.8', '>' ) ){
		require_once(dirname(__FILE__).'/'.'includes/buddypress/bp-api-loader.php');
	}
}
add_action( 'bp_include', 'vibe_bp_api_init' );



add_action( 'admin_init', 'vibebp_plugin_update' );
function vibebp_plugin_update() {
    /* Load Plugin Updater */
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/autoupdate.php' );

    /* Updater Config */
    $config = array(
        'base'      => plugin_basename( __FILE__ ), //required
        'dashboard' => true,
        'repo_uri'  => 'https://wplms.io/',  //required
        'repo_slug' => 'vibebp',  //required
    );

    /* Load Updater Class */
    new VibeBp_Auto_Update( $config );
}


add_action('plugins_loaded','vibebp_plugin_load_translations');
function vibebp_plugin_load_translations(){
    $locale = apply_filters("plugin_locale", get_locale(), 'vibebp');
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'vibebp', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;
    if ( file_exists( $mofile_global ) ) {
       load_textdomain( 'vibebp', $mofile_global );
       
    } else {
        load_textdomain( 'vibebp', $mofile_local );
    }  
}