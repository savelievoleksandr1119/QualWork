<?php
/**
 * Init functions for Micronet
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Initialization
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;


class Micronet_Init{

    public static $instance;
    public  $option;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Micronet_Init();

        return self::$instance;
    }

    private function __construct(){
        add_action( 'login_head', array($this,'login_head'), 100 );
        add_filter( 'template_include', array($this,'bp_template'), 99 );
        add_action( 'init', array($this,'disable_emojis' ));
        add_action( 'after_setup_theme', array($this,'content_width'), 0 );
        add_filter('widget_nav_menu_args', array($this,'widget_menu_args'));
        add_filter('bp_hide_loggedout_adminbar',[$this,'hide_toolbar']);
    }


    function content_width() {
        $GLOBALS['content_width'] = apply_filters( 'micronet_content_width', 1140 );
    }

    function disable_emojis() {
      // remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
      // remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
      // remove_action( 'wp_print_styles', 'print_emoji_styles' );
      // remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
      // remove_action( 'admin_print_styles', 'print_emoji_styles' );
      // remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
      // remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
      // add_filter( 'tiny_mce_plugins', array($this,'disable_emojis_tinymce' ));
    }

    function disable_emojis_tinymce( $plugins ) {
      if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
      } else {
        return array();
      }
    }
    
    function bp_template($template){

        $flag = 0;
        if(defined('VIBE_APPOINTMENTS_OPTION')){
            if(empty($this->settings)){
            $this->settings=get_option(VIBE_APPOINTMENTS_OPTION);
            }
            if(!empty($this->settings['appointments_directory_page']))
                $flag = is_page($this->settings['appointments_directory_page']);    
        }
        
        if(function_exists('bp_is_members_directory') && (bp_is_members_directory() || bp_is_groups_directory() || $flag) ) {
            $new_template = locate_template( array( 'fullwidth.php' ) );
            if ( '' != $new_template ) {
                return $new_template ;
            }
        }
        return $template;
    }

    function get_option($field=null){
        if(empty($this->option)){
            $this->option = get_option(THEME_SHORT_NAME);
        }
        if(empty($field)){
            return $this->option;
        }

        if(empty($this->option[$field])){
            return false;
        }

        return $this->option[$field];
    }
    

    function login_head() {

        if ( has_custom_logo() ) :
     
            $image = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );

            ?>
            <style type="text/css">
                .login h1 a {
                    background-image: url(<?php echo esc_url( $image[0] ); ?>);
                    -webkit-background-size: <?php echo absint( $image[1] )?>px;
                    background-size: <?php echo absint( $image[1] ) ?>px;
                    height: <?php echo absint( $image[2] ) ?>px;
                    width: <?php echo absint( $image[1] ) ?>px;
                }
            </style>
            <?php
        
        endif;
    }
     

    function widget_menu_args($args) { //$args is only argument needed to add custom walker
        if(empty($args['walker'])){
            return array_merge( $args, array(
                'walker' => new Vibe_Menu_Icon_Walker(),
           ));
        }
       return $args;
    }
    
}

Micronet_Init::init();

if(!function_exists('vibe_get_option')){
    function vibe_get_option($field=null){
        $init = Micronet_Init::init();
        return $init->get_option($field);
    }
}
if(!function_exists('micronet_get_option')){
    function micronet_get_option($field=null){
        $init = Micronet_Init::init();
        return $init->get_option($field);
    }
}