<?php
/**
 * Widgets for VibeBP 
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBP Plugin
 * @version     4.0
 */

 if ( ! defined( 'ABSPATH' ) ) exit;

class VibeBP_Widgets{

    public static $instance;
    public static function init(){
    if ( is_null( self::$instance ) )
        self::$instance = new VibeBP_Widgets();
        return self::$instance;
    }

    private function __construct(){
      require_once(dirname(__FILE__).'/widgets/server_stats_widget.php');
      require_once(dirname(__FILE__).'/widgets/users_report_widget.php');
      require_once(dirname(__FILE__).'/widgets/sales_stats_widget.php');
        //add_filter('vibebp_enqueue_profile_script',array($this,'gutenberg_widget_preview'));
    }

    function gutenberg_widget_preview($x){

        if(empty($this->once)){
            $this->once = 1;
            global $pagenow;
            if(!empty($_GET['legacy-widget-preview'])){
                ?>
                <script>
                    window.addEventListener('load',function(){
                        console.log('#--->');
                         document.dispatchEvent(new Event('<?php echo $_GET['legacy-widget-preview']['idBase']; ?>'));
                    });
                   
                </script>
                <?php
            }


            add_filter('vibebp_member_dashboard_widgets',function($scripts){
                return $scripts;
            });

            if( $pagenow === 'widgets.php' ) {
                return true;
            }
        }
        return $x;
    }
}

VibeBP_Widgets::init();



function vibebp_load_widgets_functions(){
    require_once(dirname(__FILE__).'/widgets/functions.php');
}


			