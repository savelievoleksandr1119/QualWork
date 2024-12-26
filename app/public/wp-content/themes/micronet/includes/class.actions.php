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


class Micronet_Actions{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Micronet_Actions();

        return self::$instance;
    }

    private function __construct(){
        
        add_action('micronet_footer',array($this,'footer'));
        add_action( 'wp_ajax_reset_googlewebfonts',array($this,'reset_googlewebfonts' ));

        add_action('vibebp_featured_style',array($this,'testimonial_block'),10,2);
        add_action('vibebp_carousel_styles_scripts',array($this,'testimonial_block_style'));
        add_action( 'login_enqueue_scripts',array($this, 'vibe_login_css' ));
        
        // WP Admin access
        add_action('current_screen',array($this,'wp_admin_access'));
        add_action('micronet_post_content',array($this,'show_tags'));
        add_action( 'wp_enqueue_scripts', array($this,'remove_wp_block_library_css'), 100 );
        add_action('wp_footer',array($this,'footer_google_analytics'),99);


        add_action('vibebp_featured_style',array($this,'micronet_member_block'),10,2);
        add_action('vibebp_featured_style',array($this,'generic_block'),10,2);
    
    }

    function generic_block($post,$style){

        if($style == 'generic_post'){
            ?>
                
                <div class="flex flex-col gap-4 p-4 rounded border border-slate-100 <?php echo get_post_format($post->ID); ?>">
                    <?php if(has_post_thumbnail($post->ID)){ ?>
                         <div class="featured rounded overflow-hidden">
                            <a href="<?php echo get_permalink($post->ID) ?>"><?php echo get_the_post_thumbnail($post->ID,'full'); ?></a>
                        </div>
                    <?php } ?>
                    <div class="flex gap-4 justify-between">
                       
                        <div class="flex flex-col gap-2">
                            <span class="primary-color"><?php echo get_the_category_list('','',$post->ID); ?></span>
                            <h3 class="text-2xl"><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title($post->ID); ?></a></h3>
                            <span class="blogpost_style2_date"><?php echo get_the_time('M j,y',$post->ID); ?></span>
                        </div>
                         <?php
                            $name = get_the_author_meta( 'display_name' );
                            echo '<a href="'.get_author_posts_url( $post->post_author ).'" 
                        title="'.$name.'" class="blogpost_author h-8 w-8 basis-8 shrink-0 rounded-full overflow-hidden">'.((function_exists('bp_core_fetch_avatar'))?bp_core_fetch_avatar(array(
                                'item_id' => $post->post_author,
                                'object'  => 'user'
                            )):$name).'</a>';
                        ?>
                    </div>

                    <div class="excerpt flex flex-col gap-4">
                        <p><?php echo get_the_excerpt($post->ID); ?></p>
                        <a href="<?php echo get_permalink($post->ID); ?>" class="link primary-color"><?php echo __('Read More','micronet'); ?></a>
                    </div>
                </div>
                <?php
        }
    }

    function micronet_member_block($post,$style){
        
        if($style != 'micronet_member' || !function_exists('vibe_projects_get_member_stats') )
            return;

        $avatar = bp_core_fetch_avatar(array(
            'item_id'   => $post->id,
            'object'    => 'user',
            'type'      =>'thumb',
            'html'      => false
        ));

        $link = bp_core_get_user_domain($post->id);
        $member_type = bp_get_member_type($post->id);
        $name = bp_core_get_user_displayname($post->id);
        $types = bp_get_member_types(array(),'objects');
        $card_count = vibe_projects_get_member_stats('cards_count',$post->id);
        $project_count= vibe_projects_get_member_stats('projects_count',$post->id);

        $member_team= vibe_projects_get_member_team($post->id);

        if(empty($avatar)){
            $avatar = plugins_url('../assets/images/avatar.jpg',__FILE__);
        }

        ?>
        <div class="member_featured_block_wrapper micronet_member_block border rounded-lg member_<?php echo esc_attr($post->id); ?> flex flex-col" >
            <a href="<?php echo esc_url($link);?>" class="flex-1">
                <img src="<?php echo esc_url($avatar); ?>" alt="user profile image" class="member_avatar" />
            </a>
            <div class="member_info flex flex-col gap-4 p-4 contentbg flex-1">
                <div class="flex gap-4">
                    
                    <div class="flex flex-col gap-2">
                        <a href="<?php echo esc_url($link);?>" class="flex-col flex">
                            <?php echo '<span class="text-sm mt-2">'.(empty($types) || empty($types[$member_type])?'':$types[$member_type]->labels['name']).'</span>'; ?>
                            <strong class="text-xl"><?php echo esc_attr( $name ); ?></strong>
                        </a>
                    </div>
                </div>
                <span class="flex-1">
                </span>
                 <?php 
                    echo '<span class="text-sm mt-2">'.(empty($member_team)?'':'<span class="rounded-2xl" style="color:#fff;padding:5px 10px;background:'.$member_team->color.'">'.$member_team->name).'</span></span>';          
                ?>
            </div>
        </div>
        <?php

        add_action('wp_footer',array($this,'micronet_member_block_style'));
    }

    function micronet_member_block_style(){
        echo '<style>.micronet_member_block .member_avatar{width:100%;border-radius:5px 5px 0 0;}</style>';
    }
    
    function footer_google_analytics(){
      echo micronet_get_option('google_analytics');
    }
    function remove_wp_block_library_css(){
        global $post;
        if(!empty( $post->ID)){
            //if elementor active remove WP Blocks
            $elementor_page = get_post_meta( $post->ID, '_elementor_edit_mode', true );
            if(!empty($elementor_page)){
                wp_dequeue_style( 'wp-block-library' );
                wp_dequeue_style( 'wp-block-library-theme' );
                wp_dequeue_style( 'wc-blocks-style' ); // Remove WooCommerce block CSS    
            }    
        }
    } 
    
 
    
    function vibe_login_css() {    //Copy this function to customize WP Admin login screen
        ?>
        <style type="text/css">
            <?php
            $wp_login_screen = vibe_get_option('wp_login_screen');
            echo esc_html($wp_login_screen);
            ?>
        </style>
        <?php 
    }
    function reset_googlewebfonts(){ 

        if ( check_ajax_referer( 'google_webfonts', 'wpnonce' )){
            echo "reselecting..";
            $r = get_option('google_webfonts');
            if(isset($r)){
                delete_option('google_webfonts');
            }
        }
        
        die();
    }

    /*============================================*/
    /*===========  WP ADMIN ACCESS    ============*/
    /*============================================*/
    function wp_admin_access(){
        if((defined( 'DOING_AJAX' ) && DOING_AJAX) || (defined('IFRAME_REQUEST') && IFRAME_REQUEST))
          return;
        
        $val = vibe_get_option('wp_admin_access');
        $cap = apply_filters('wplms_admin_access_capabilities',array(1=>array('edit_posts'),2=>array('manage_options')));
        $flag = 1;
        if(!empty($val) && !empty($cap[$val])){
          foreach($cap[$val] as $value){
              if(!current_user_can($value)){ 
                  $flag=0;
                  break;
                }
            }
        }
        if(empty($flag)){
          wp_redirect(home_url());
          exit;
        }
    }
   

    function footer(){
       
        if(!empty(vibe_get_option('footer_sidebar'))){
            $sidebar = apply_filters('micronet_sidebar','footer');
            ?><div class="m-2 flex flex-wrap gap-4"><?php
            dynamic_sidebar($sidebar);
            ?></div><?php
        }
    }

    function testimonial_block($post,$style){
        if($style != 'testimonial_block')
            return;

        ?>
        <div class="testimonial_block_wrapper testimonial_'<?php echo esc_attr($post->id); ?>" >
            <div class="testimonial_content">
                <?php echo apply_filters('the_content',$post->post_content); ?>
            </div>
            <div class="testimonial_author">
                <?php echo get_the_post_thumbnail($post); ?>
                <strong><?php echo esc_html($post->post_title); ?></strong>
            </div>
        </div>
        <?php

    }

    function testimonial_block_style($style){
       
       if($style != 'testimonial_slide')
            return;

        add_action('wp_footer',function(){


        ?>
        <style>
            .testimonial_block_wrapper {
                margin: 1.5rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }
            .slide_block_wrapper.testimonial_slide_style {
                padding:1.5rem;
            }
            .slide_block_wrapper.testimonial_slide_style p{display:flex;text-align:center}
            .slide_block_wrapper.testimonial_slide_style:before {
                content: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="124" height="124"><path fill="none" d="M0 0h24v24H0z"/><path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179z"/></svg>');
                font-family:vicon;
                font-size:3rem;position:absolute;left:-1rem;top:-1rem;
                z-index:-1;
                opacity:0.1;
            }
            .testimonial_block_wrapper .testimonial_content {
                background: #eee;
                border-radius: 1rem;
                padding: 1.5rem;
            }
            .testimonial_author{display: flex;align-items: center;flex-direction: column;}
            .testimonial_author img{
                width: 64px;
                height: 64px;
                border-radius: 50%;
                border: 1px solid #eee;
                overflow: hidden;
            }
        </style>
        <?php
    });
    }
    //Show tags in single posts
    function show_tags(){
        
        if(is_single() && !is_singular('product')){
            $prev_post = get_previous_post();
            $next_post = get_next_post();
            ob_start();
            the_tags('<div class="flex flex-wrap gap-4"><span class="tag">','</span><span class="tag">','</span></div>'); 
            $tags = ob_get_clean();
            if(!empty($tags)){


        ?>
        <div class="tags py-4 clear-both">
        <?php echo wp_kses_post($tags); ?>
        </div>
        <?php 
            }
            if(!empty($prev_post) && !empty($next_post)){
        ?>
        <div class="prev_next_posts_link flex justify-between w-full py-4 gap-4">
                    
                <?php 
                $prev_post = get_previous_post();
                $next_post = get_next_post();
                ?>
                <div class="flex items-center ">
                <?php

                if(!empty($prev_post))
                echo '<a href="'.get_permalink($prev_post->ID).'" class="prev link flex items-center gap-4 font-bold text-start break-all"><span class="vicon vicon-angle-left"></span>'.$prev_post->post_title.'</a>';
                ?>
                </div>
                <div class="flex items-center ">
                <?php
                if(!empty($next_post))
                echo '<a href="'.get_permalink($next_post->ID).'" class="next link flex items-center gap-4 font-bold text-end break-all">'.$next_post->post_title.'<span class="vicon vicon-angle-right"></span></a>';
                ?> 
                </div>
        </div>
        <?php
            }
        }
    }
}

Micronet_Actions::init();
