<?php
/**
 * Actions for Micronet
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Initialization
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;


class Micronet_Filters{

    public static $instance;
    public static $option;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Micronet_Filters();

        return self::$instance;
    }

    private function __construct(){
        add_filter('get_custom_logo_image_attributes',array($this,'custom_logo'));
        add_filter('vibebp_featured_post_type_styles_options',array($this,'testimonial_block'));
        add_filter('body_class',array($this,'theme_skin'));
        add_filter('micronet_single_sidebar',array($this,'show_sidebar'));
        add_filter('excerpt_more',array($this,'new_excerpt_more'));
        add_filter( 'excerpt_length', array($this,'custom_excerpt_length'), 999 );

        add_filter('vibe_bp_gutenberg_text_color_options',array($this,'add_primary'));
        add_filter('micronet_sidebar',array($this,'check_sidebar'),11);

        add_filter('vibebp_featured_post_type_styles_options',array($this,'generic_post'));
        add_filter('vibebp_featured_members_styles_options',array($this,'micronet_member_blocks'));
    
    }


    function micronet_member_blocks($styles){
        $styles['micronet_member'] = 'Micronet Member';
        return $styles;
    }
    
    function generic_post($args){
        $args['generic_post'] = 'Generic post';
        return $args;
    }


    function check_sidebar($sidebar){
        if(function_exists('is_shop') && is_shop() || is_singular('product')){
            $sidebar = 'woocommerce';
        }
        return $sidebar;
    }

    function add_primary($colors){
        
        $colors[]=[
            'name'=>_x('Primary','color description','micronet'),
            'color'=> 'var(--bg-primary)'
        ];
        $colors[]=[
            'name'=>_x('Body Text','color description','micronet'),
            'color'=>' var(--text)'
        ];
        return $colors;
    }

    function custom_logo($attr){     
        
        $width = get_theme_mod('logo_width');
        if(empty($width)){$width = '8';}
        $attr['style']='width:'.$width.'rem';
        return $attr;
    }

    function new_excerpt_more($more) {
        global $post;
        return '<span class="flex justify-start"><a class="link mt-4" href="'. get_permalink($post->ID) . '">'.__('Read more','micronet').'</a></span>';
    }
    function custom_excerpt_length( $length ) {
        return 20;
    }
    function theme_skin($class){
        $theme_skin = vibe_get_option('theme_skin');
        if(!Empty($theme_skin)){
            $class[]='minimal';
        }
        return $class;
    }

    function testimonial_block($styles){
        $styles['testimonial_block'] = 'Testimonial Block';
        return $styles;
    }

    function show_sidebar($flag){
        global $post;
        if(in_Array($post->post_type ,['member-profile','member-card'])){
            return 1;
        }
        return $flag;
    }
}

Micronet_Filters::init();
