<?php
/**
 * Performance Improvements plugin
 *
 * @class       VibeBP_Performance
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     2.0
 * @copyright   VibeThemes
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



class VibeBP_Performance{


    public static $instance;
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Performance();
        return self::$instance;
    }


    private function __construct(){
    	add_action('rest_api_init',array($this,'rest_api'));
		add_action( 'upgrader_process_complete', [$this,'check_accelerator'], 10, 2 );
		
    }

    
    function rest_api(){

        register_rest_route( Vibe_BP_API_NAMESPACE, '/measure_performance/', array(
            array(
                'methods'             => 'GET',
                'callback'            =>  array( $this, 'measure_performance_api' ),
                'permission_callback' => array( $this, 'xnonce_check' ),
            ),
        ));
    }
    function xnonce_check($request){
        return true;
    }
    function measure_performance_api($request){

    	if(vibebp_get_setting('api_performance','general','performance')){

    		if(!file_exists(WPMU_PLUGIN_DIR .'/vibe-api-accelerator.php')){
    			if($this->setup_accelerator()){
                    return  new WP_REST_Response(['message' => __('Accelerator setup done. Re-check to Measure performance.','vibebp')],200);
                }else{
                    return  new WP_REST_Response(['message' => __('Manually create mu-plugins folder','vibebp')],200);
                }
    			
    		}
    		
		}else{
			$this->remove_accelerator();
		}

		$past = get_transient('vibebp_api_performance');
		if(empty($past)){$past=[];}
		
		ob_start();
		timer_stop(1);
		$time= ob_get_clean();
		$current = ['memory'=>round(memory_get_usage()/1048576,2).''.' MB','queries'=>get_num_queries(),'time'=>$time];
		set_transient('vibebp_api_performance',$current,3600);

        return new WP_REST_Response(['current' => $current,'past'=>$past],200);

		
    }


    function setup_accelerator(){
    	
        if(!file_exists(WPMU_PLUGIN_DIR.'/vibe-api-accelerator.php') || (defined('VIBE_API_ACCELERATOR_VERSION') && VIBE_API_ACCELERATOR_VERSION != VIBEBP_VERSION)

    	){

            
	    	if(file_exists(plugin_dir_path(__FILE__).'/core/vibe-api-accelerator.php')){


                if(function_exists('WP_Filesystem')){
                    WP_Filesystem();
                }

                if ( ! is_dir( WPMU_PLUGIN_DIR ) ) {
                    if(!wp_mkdir_p(WPMU_PLUGIN_DIR )){
                        return false;
                    }
                }

	    		$myFile = WPMU_PLUGIN_DIR.'/vibe-api-accelerator.php';
            	
                $fh = fopen($myFile, 'w');
	            $contents = file_get_contents(plugin_dir_path(__FILE__).'/core/vibe-api-accelerator.php');
	            $contents = str_replace('1.0',VIBEBP_VERSION,$contents);
	            fwrite($fh, print_r($contents, true)."\n");
            	fclose($fh);
                return true;
	        }
        }
    }

    function remove_accelerator(){
    	if ( ! function_exists( 'get_home_path' ) ) {
            include_once ABSPATH . '/wp-admin/includes/file.php';
        }
		$site_root = get_home_path();				    
        if(file_exists(WPMU_PLUGIN_DIR.'/vibe-api-accelerator.php')){
        	wp_delete_file(WPMU_PLUGIN_DIR.'/vibe-api-accelerator.php');
        }
    }

    function  check_accelerator( $upgrader_object, $options ) {
		
		 if ( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {

	        foreach( $options['plugins'] as $plugin ) {
	            if( $plugin == 'vibebp/loader.php' ) {
	            	if(vibebp_get_setting('api_performance','general','general')){
	            		$this->setup_accelerator();	
	            	}
					
		        }
	        }
        }
    }

	
}

VibeBP_Performance::init();