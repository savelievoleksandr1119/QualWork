<?php
/**
 * Register functions for Micronet
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Register
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;


class Micronet_Register{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Micronet_Register();

        return self::$instance;
    }

    private function __construct(){
        add_action('wp_enqueue_scripts',array($this,'enqueue_scripts'));
        add_action('admin_enqueue_scripts',array($this,'admin_enqueue_scripts'));
    	
    	add_action('widgets_init',array($this,'register_sidebars')); 
        add_action( 'customize_register', array($this,'logo_settings') );
        
        add_action( 'admin_init',array($this,'storegoogle_webfonts' ));

        add_action('in_widget_form',array($this,'widget_form'),10,3);
        add_filter('widget_update_callback', array($this,'save_widget_form'),10,3);
        add_filter('dynamic_sidebar_params',array($this,'footer_widgets'));

        add_filter('vibebp_member_profile_default_container',function($c){return '';});
    }


    function admin_enqueue_scripts($hook){

        if($hook == 'nav-menus.php'){
            wp_enqueue_style( 'micronet-menu-css', MICRONET_URL .'/includes/menu/css/admin_menu.css' ,array(),MICRONET_VERSION);
            wp_enqueue_script( 'micronet-menu-js', MICRONET_URL .'/includes/menu/js/admin_vibe_menu.js',array(),MICRONET_VERSION);
        }
    }

    function enqueue_scripts(){
        wp_enqueue_style('vicons');
        if(empty(vibe_get_option('default_font'))){
            wp_enqueue_style('default_fonts',MICRONET_URL.'/css/default_fonts.css',array(),MICRONET_VERSION);
        }

        if(empty(vibe_get_option('theme_type'))){
            wp_dequeue_style( 'wp-block-library' );
            wp_dequeue_style( 'wp-block-library-theme' );
            wp_dequeue_style( 'wc-block-style' ); // REMOVE WOOCOMMERCE BLOCK CSS
            wp_dequeue_style( 'global-styles' ); // REMOVE THEME.JSON
        }
        
        $sections  = vibe_options_get_sections();

        $colors = wp_list_pluck($sections[4]['fields'],'id');
        
        
        $custom_css='body,body.minimal{';
        foreach($colors as $color){
            if(!empty(vibe_get_option($color))){
                $custom_css .=' --'.$color.':'.vibe_get_option($color).';';
            }
            if($color == 'bg-primary'){
                $c=vibe_get_option($color);
                $custom_css .=' --primary:'.(empty($c)?'#f62b89':$c).';';
                $custom_css.='--swiper-theme-color:'.vibe_get_option($color).';';
            }
        }

        $bg = vibe_get_option('header_background');
        if(empty($bg)){$bg = VIBE_URL.'/images/header_background.jpg';}

        $color = vibe_get_option('header_color');
        if(empty($color)){$color = '#fff';}

        $custom_css .='--header-bg:'.(wp_http_validate_url($bg)?'url('.$bg.') no-repeat':$bg).';
            --header-color:'.$color.';';

        $font_family='Roboto Slab';
        if(micronet_get_option('body-font')){
             $custom_css .=' --body-font:'.vibe_get_option('body-font').';';
        }
        if(micronet_get_option('heading-font')){
             $custom_css .=' --heading-font:'.vibe_get_option('heading-font').';';
        }
        $custom_css .= '}';

        wp_add_inline_style('micronet',$custom_css);
    }


    function logo_settings( $wp_customize ) {
        $wp_customize->add_setting(
            'logo_width',
            array(
                'default' => '',
                'type' => 'theme_mod',
                'capability' => 'edit_theme_options',
                'transport' => 'postMessage', // or postMessage
                'sanitize_callback' => 'intval',
            ),
        );

        $wp_customize->add_control(  'logo_width',array(
            'type'     => 'text',
            'section'  => 'title_tagline',
            'settings' => 'logo_width',
            'transport'=>'postMessage',
            'label'      => __( 'Logo Width', 'micronet' ),
            'description' => __( 'Set a width to your logo [in Rem 4-20 recommended, number only]', 'micronet' ),
            'sanitize_callback' => 'intval',
        ) ) ;
       

    }

  	function register_sidebars(){
      	if(function_exists('register_sidebar')){     
          	register_sidebar( array(
          		'name' => 'MainSidebar',
          		'id' => 'mainsidebar',
          		'before_widget' => '<div id="%1$s" class="widget %2$s">',
          		'after_widget' => '</div>',
          		'before_title' => '<h4 class="widget_title"><span>',
          		'after_title' => '</span></h4>',
              	'description'   => __('This is the global default widget area/sidebar for pages, posts, categories, tags and archive pages','micronet')
    		) );

            if(function_exists('WC')){
                register_sidebar( array(
                    'name' => 'WooCommerce',
                    'id' => 'woocommerce',
                    'before_widget' => '<div id="%1$s" class="widget %2$s">',
                    'after_widget' => '</div>',
                    'before_title' => '<h4 class="widget_title"><span>',
                    'after_title' => '</span></h4>',
                    'description'   => __('This is the Woocommerce  widget area/sidebar for Shop, Products, categories, tags and archive pages','micronet')
                ) );
            }

            if(!empty(micronet_get_option('footer_sidebar'))){
                $columns = 3;
                if(!empty(vibe_get_option('footer_sidebar_colums'))){
                    $columns = vibe_get_option('footer_sidebar_colums');
                }
                register_sidebar( array(
                    'name' => 'Footer',
                    'id' => 'footer',
                    'before_widget' => '<div id="%1$s" class="widget flex-1 basis-1/'.$columns.' min-w-16 %2$s">',
                    'after_widget' => '</div>',
                    'before_title' => '<h3 class="widget_title"><span>',
                    'after_title' => '</span></h3>',
                    'description'   => __('This is footer widget area','micronet')
                ) );
            }
      	}

        $sidebars=vibe_get_option('sidebars');
          if(isset($sidebars) && is_array($sidebars)){ 
              foreach($sidebars as $sidebar){ 
                  register_sidebar( array(
              'name' => $sidebar,
              'id' => $sidebar,
              'before_widget' => '<div class="widget"><div class="inside">',
              'after_widget' => '</div></div>',
              'before_title' => '<h4 class="widgettitle"><span>',
              'after_title' => '</span></h4>',
                  'description'   => __('Footer Manager','micronet')
            ) );
            }
          }
    } 

     /*==== Reset Google Fonts ====*/
    function storegoogle_webfonts(){
        $google_webfonts=get_option('google_webfonts');
        if(!empty($google_webfonts)){
            
              if(!function_exists('WP_Filesystem')){
                require_once( ABSPATH . 'wp-admin/includes/file.php' );  
              }
              WP_Filesystem();
              global $wp_filesystem;
              $fonts = $wp_filesystem->get_contents(MICRONET_PATH.'/js/fonts.json');
              add_option( 'google_webfonts', "$fonts",'', 'no');
            
        }
    } 

     function widget_form($widget, $return, $instance) {
        
        $columns = 0;
        if(isset($instance['micronet_columns'])){$columns = $instance['micronet_columns'];}
        ?>
        <p>
            <select class="micronet_columns"  name="<?php echo esc_attr($widget->get_field_name('micronet_columns')); ?>" id="<?php echo esc_attr($widget->get_field_name('micronet_columns')); ?>">
                <option><?php _e('Set width column','micronet') ?></option>
                <option value="2" <?php selected($columns,'2'); ?>><?php _e('2 column','micronet') ?></option>
                <option value="3" <?php selected($columns,'3'); ?>><?php _e('3 column','micronet') ?></option>
                <option value="4" <?php selected($columns,'4'); ?>><?php _e('4 column','micronet') ?></option>
                <option value="5" <?php selected($columns,'5'); ?>><?php _e('5 column','micronet') ?></option>
            </select>
            <label for="<?php echo esc_attr($widget->get_field_name('micronet_columns')); ?>"><?php __('Width Column', 'micronet'); ?></label>
        </p>
        <?php
    }

    
    function save_widget_form($instance, $new_instance, $old_instance) {
        $instance['micronet_columns'] = isset($new_instance['micronet_columns'])?$new_instance['micronet_columns']:0;
        return $instance;
    }

    function footer_widgets($params) {
        global $wp_registered_widgets;
        $widget_id        = $params[0]['widget_id'];
        $widget_obj       = $wp_registered_widgets[ $widget_id ];
        $widget_opt       = get_option( $widget_obj['callback'][0]->option_name );
        $widget_num       = $widget_obj['params'][0]['number'];
        $grid_class       = isset( $widget_opt[ $widget_num ]['micronet_columns'] ) ? $widget_opt[ $widget_num ]['micronet_columns'] : '';
 
        if ( preg_match( '/class="/', $params[0]['before_widget'] ) && $grid_class ) {
            $params[0]['before_widget'] = preg_replace( '/basis-1/', "basis-$grid_class", $params[0]['before_widget'], 1 );
            $params[0]['before_widget'] = preg_replace( '/widget/', "col-span-$grid_class widget", $params[0]['before_widget'], 1 );
            
        }
 
        return $params;
    }
}

Micronet_Register::init();

