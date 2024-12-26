<?php
/**
 * Enqueue functions for Micronet
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Initialization
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;


class Micronet_Enqueue{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Micronet_Enqueue();

        return self::$instance;
    }

    private function __construct(){
        add_action('wp_enqueue_scripts',array($this,'enqueue_styles'));
        add_action('wp_enqueue_scripts',array($this,'enqueue_scripts'));
        add_action( 'after_setup_theme', array($this,'micronet_setup' ));            

    }


    function micronet_setup() {
        add_theme_support( 'title-tag' );

        register_nav_menus(
            array(
                'primary' => __( 'Primary Menu', 'micronet' ),
                'mobile' => __( 'Mobile Menu', 'micronet' ),
            )
        );

        add_theme_support(
            'html5',
            array(
                'search-form',
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
            )
        );
        
        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'custom-logo' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'woocommerce' );
        add_theme_support( 'align-wide' );
        add_theme_support( 'wp-block-styles' );
        add_theme_support( 'editor-styles' );
        add_editor_style( 'css/editor-style.css' );
        remove_theme_support( 'widgets-block-editor' );
    }


    
    function micronet_asset( $path ) {
        if ( wp_get_environment_type() === 'production' ) {
            return get_template_directory_uri() . '/' . $path;
        }

        return add_query_arg( 'time', time(),  get_template_directory_uri() . '/' . $path );
    }

    function enqueue_styles(){
        $theme = wp_get_theme();
        wp_enqueue_style('vicons',$this->micronet_asset('css/vicons.css'),array(),MICRONET_VERSION);
        wp_enqueue_style( 'micronet', $this->micronet_asset( 'css/app.css' ), array('vicons','wp-block-library'), MICRONET_VERSION );
    }

    function enqueue_scripts(){
        $theme = wp_get_theme();
        wp_enqueue_script( 'micronet', $this->micronet_asset( 'js/app.js' ), array( 'wp-blocks' ), MICRONET_VERSION , true);
    }
}

Micronet_Enqueue::init();