<?php
/**
 * Plugin Name: Vibe API Accelerator
 * Plugin URI: https://vibethemes.com
 * Description: Enhance performance of your API 20x
 * Author: VibeThemes
 * Author URI: https://vibethemes.com
 * Version: 1.0
 * Text Domain: vibe-api-accelerate
 * Domain Path: /languages
 * Tested up to: 6.2.2
 *
 * @package VibeBP
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'VIBE_API_ACCELERATOR_VERSION' ) ){
    define('VIBE_API_ACCELERATOR_VERSION','1.0');
}


class Vibe_API_Accelerator{


    public static $instance;
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_API_Accelerator();
        return self::$instance;
    }


    private function __construct(){
        
        add_filter( 'option_active_plugins',[$this,'conditional_include_plugins'],1);
    }

    
    function conditional_include_plugins( $plugins ) {

        $all_addons=[
            'buddypress/bp-loader.php',
            'vibebp/loader.php',
            'paid-memberships-pro/paid-memberships-pro.php',
            'woocommerce/woocommerce.php',
            'wplms_plugin/loader.php',
            'vibedrive/loader.php',
            'vibe-appointments/loader.php',
            'vibe-projects/loader.php',
            'vibe-calendar/loader.php',

        ];
        
        if (strpos( $_SERVER['REQUEST_URI'], 'wp-json') != false){

            $api_url_list = [
                'vibebp/v1' =>[
                    'vibebp'=>['buddypress/bp-loader.php','vibebp/loader.php'],
                ],
                'vbp/v1'=>[
                    'measure_performance'=>['buddypress/bp-loader.php','vibebp/loader.php'],
                    'pmpro' =>['buddypress/bp-loader.php','vibebp/loader.php','paid-memberships-pro/paid-memberships-pro.php'],
                    'wc'    =>['buddypress/bp-loader.php','vibebp/loader.php','woocommerce/woocommerce.php'],
                    'following_ids'=>['buddypress/bp-loader.php','vibebp/loader.php'],
                    'loggedinmenu'=>['buddypress/bp-loader.php','vibebp/loader.php'],
                    'getProfileCompleteness'=>['buddypress/bp-loader.php','vibebp/loader.php'],
                    'fetch_media'=>['buddypress/bp-loader.php','vibebp/loader.php','vibedrive/loader.php'],
                    'profile\/subNav'=>['buddypress/bp-loader.php','vibebp/loader.php','elementor/elementor.php'],
                    'xprofile\/allfields'=>['buddypress/bp-loader.php','vibebp/loader.php'],
                    'messages'=>['buddypress/bp-loader.php','vibebp/loader.php'],
                    'members\/all'=>['buddypress/bp-loader.php','vibebp/loader.php'],
                    'avatar'=>['buddypress/bp-loader.php','vibebp/loader.php'],
                ],
                'wplms/v2'=>[
                    'course_card'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','elementor/elementor.php','woocommerce/woocommerce.php','paid-memberships-pro/paid-memberships-pro.php'],
                    'student\/courses'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','wplms-coinstructors/loader.php'],
                    'student\/badges'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','wplms-coinstructors/loader.php'],
                    'student\/certificates'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','wplms-pdf-certificates/wplms-pdf-certificates.php','wplms-coinstructors/loader.php'],
                    'student\/finishedCourses'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','wplms-coinstructors/loader.php'],
                    'student\/quiz'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','wplms-coinstructors/loader.php'],
                    'instructor\/courses\?args'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','wplms-coinstructors/loader.php'],
                    'instructor\/courses\/members\/\d+\/'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','wplms-coinstructors/loader.php'],
                    'instructor\/course\/\d+\/activity'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','wplms-coinstructors/loader.php'],
                    'instructor\/quizzes'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','wplms-coinstructors/loader.php'],
                    'instructor\/assignments'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','wplms-coinstructors/loader.php'],
                    'instructor\/manageStudents'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','wplms-coinstructors/loader.php'],
                    'instructor\/questions'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','wplms-coinstructors/loader.php'],
                    'instructor\/get_posts\/course'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','wplms-coinstructors/loader.php'],
                    'createElement'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','woocommerce/woocommerce.php','wplms-coinstructors/loader.php'],
                    'singlecourse'=>['vibebp/loader.php','wplms_plugin/loader.php','woocommerce/woocommerce.php','elementor/elementor.php']
                ],
                'vibehd/v1'=>[
                    'bbp\/forums'=>['buddypress/bp-loader.php','vibebp/loader.php','vibe-helpdesk/loader.php','bbpress/bbpress.php'],
                    'bbp\/topics'=>['buddypress/bp-loader.php','vibebp/loader.php','vibe-helpdesk/loader.php','bbpress/bbpress.php'],
                    'bbp\/replies'=>['buddypress/bp-loader.php','vibebp/loader.php','vibe-helpdesk/loader.php','bbpress/bbpress.php','bbpress-private-replies/bbpress-private-replies.php']
                ],
                'vibekb/v1'=>[
                    'user\/articles'=>['buddypress/bp-loader.php','vibebp/loader.php','vibe-kb/loader.php']
                ],
                'vibeappointments/v1'=>[
                    'favourites'=>['vibebp/loader.php','vibe-appointments/loader.php'],
                    'getUserEnrolledServices'=>['vibebp/loader.php','vibe-appointments/loader.php'],
                    'getOpenSlots'=>['vibebp/loader.php','vibe-appointments/loader.php'],
                    'getUserEnrolledServices'=>['vibebp/loader.php','vibe-appointments/loader.php'],
                    'getAllServices'=>['vibebp/loader.php','vibe-appointments/loader.php'],
                    'servicedata'=>['vibebp/loader.php','vibe-appointments/loader.php'],
                    'getservicespricing'=>['vibebp/loader.php','vibe-appointments/loader.php'],
                    'getPriceRange'=>['vibebp/loader.php','vibe-appointments/loader.php'],
                    
                ],
                'vibeprojects/v1'=>[
                    'vibeprojects'=>['buddypress/bp-loader.php','vibebp/loader.php','vibe-projects/loader.php']
                ],
                'wplms-ai-qg/v1'=>[
                    'wplms-ai-qg'=>['buddypress/bp-loader.php','vibebp/loader.php','wplms_plugin/loader.php','wplms-ai-quiz-questions/loader.php']
                ]
            ];


            $flag=1;
            foreach($api_url_list as $key => $value){
                if(strpos( $_SERVER['REQUEST_URI'], $key) != false ){

                    if(!empty($value)){
                        foreach($value as $k=>$plgns){
                            preg_match( '/'.$k.'/i',$_SERVER['REQUEST_URI'],$check);

                            if(!empty($check)){
                                foreach($plugins as $k=>$plugin){
                                    if(!in_array($plugin,$plgns)){
                                        unset($plugins[$k]);        
                                    }
                                }
                                break;
                            }
                        }
                    }else{
                        $flag=0;
                    }
                    break;
                }   
            }   
            if(empty($flag)){
                foreach($plugins as $k=>$plugin){
                    unset($plugins[$k]);    
                }
            }
        }
        return $plugins;
    }
}

Vibe_API_Accelerator::init();
