<?php


/**
 * Customizer functions for vibeapp
 *
 * @author      VibeThemes
 * @category    Actions
 * @package     vibeapp
 * @version     1.0
 */


if ( ! defined( 'ABSPATH' ) ) exit;


class VibeBp_Design_Shortcodes{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBp_Design_Shortcodes();

        return self::$instance;
    }

    private function __construct(){
    	add_shortcode('micronet_counter',[$this,'counter']);
        add_Shortcode('rotating_test',[$this,'text']);
    }


    function counter($atts,$content){

        $defaults = [
            'speed'=>20
        ];
        wp_parse_args($atts,$defaults);

        return '<div class="counter_wrap">
                    <div class="start-counter"></div>
                    <div class="counter" data-stop='.do_shortcode($content).' data-speed=20></div>
                </div>';
    }

    function text($atts,$content){

        $colors = ['alizarin','wisteria','peter-river','emerald','sun-flower'];
        $return = '<div class="rotating-text">
              <p>'.$content.'</p><p>';
        
        foreach($atts as $k=>$word){
            $return .= '<span class="word '.($colors[($k%5)]).'">'.$word.'</span>';
        }  
               
        $return .= '</p></div>';
        return $return;
    }
}

VibeBp_Design_Shortcodes::init();