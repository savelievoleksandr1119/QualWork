<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function main_class(){

    if(is_page_template('elementor_header_footer')){
        return '';
    }
    return 'flex flex-wrap gap-6';
}

if(!function_exists('vibe_breadcrumbs')){
function vibe_breadcrumbs() {  

    global $post;

    /* === OPTIONS === */  
    $text['home']     = __('Home','micronet'); // text for the 'Home' link  
    $text['category'] = '%s'; // text for a category page  
    $text['search']   = '%s'; // text for a search results page  
    $text['tag']      = '%s'; // text for a tag page  
    $text['author']   = '%s'; // text for an author page  
    $text['404']      = 'Error 404'; // text for the 404 page  
  
    $showCurrent = apply_filters('vibe_breadcrumbs_show_title',1); // 1 - show current post/page title in breadcrumbs, 0 - don't show  
    $showOnHome  = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show  
    $delimiter   = ''; // delimiter between crumbs  
    $before      = '<li class="current"><span>'; // tag before the current crumb  
    $after       = '</span></li>'; // tag after the current crumb  
    /* === END OF OPTIONS === */  
  
    $homeLink = home_url();  
    $linkBefore = '<li>';  
    $linkAfter = '</li><li><span class="mx-2">/</span></li>';  
    $linkAttr = ' class="font-bold whitespace-nowrap break-words" ';  
    $link = $linkBefore . '<a' . $linkAttr . ' href="%1$s" ><span>%2$s</span></a>' . $linkAfter;  
  
    if (is_home() || is_front_page()) {  
  
        if ($showOnHome == 1) echo '<div id="crumbs"><a href="' . $homeLink . '">' . $text['home'] . '</a></div>';  
  
    } else {  
  

        echo '<nav class="opacity-60 text-sm w-full">
        <ol class="list-reset flex">' . sprintf($link, $homeLink, $text['home']) . $delimiter;  

        if ( is_category() ) {  
            $thisCat = get_category(get_query_var('cat'), false);  
            if ($thisCat->parent != 0) {  
                $cats = get_category_parents($thisCat->parent, TRUE, $delimiter);  
                $cats = str_replace('<a', $linkBefore . '<a' . $linkAttr, $cats);  
                $cats = str_replace('</a>', '</a>' . $linkAfter, $cats);  
                echo vibe_sanitizer($cats);  
            }  
            echo vibe_sanitizer($before . sprintf($text['category'], single_cat_title('', false)) . $after);  
  
        } elseif ( is_tax() ) {  
          $taxonomy = get_query_var( 'taxonomy' );
          $taxonomy_obj = get_taxonomy($taxonomy);
          $term = get_query_var( 'term' );
          
             echo vibe_sanitizer($before .   $taxonomy_obj ->labels->name .' / '. $term . $after);  
  
        }elseif ( is_search() ) {  
            echo vibe_sanitizer($before . sprintf($text['search'], get_search_query()) . $after);  
  
        } elseif ( is_day() ) {  
            echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter;  
            echo sprintf($link, get_month_link(get_the_time('Y'),get_the_time('m')), get_the_time('F')) . $delimiter;  
            echo vibe_sanitizer($before . get_the_time('d') . $after);  
  
        } elseif ( is_month() ) {  
            echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter;  
            echo vibe_sanitizer($before . get_the_time('F') . $after);  
  
        } elseif ( is_year() ) {  
            echo vibe_sanitizer($before . get_the_time('Y') . $after);  
  
        } elseif(function_exists('bp_is_directory') && bp_is_directory()){

          $component = bp_current_component();
          $page_url = get_permalink(vibe_get_bp_page_id($component));
          printf($link, $homeLink . '/' . basename($page_url) . '/', get_the_title(vibe_get_bp_page_id($component)));  

        } elseif ( is_attachment() ) {  
            $parent = get_post($post->post_parent);  
            $cat = get_the_category($parent->ID); 
            if(isset($cat[0])){
            $cat = $cat[0];  
            $cats = get_category_parents($cat, TRUE, $delimiter);  
            $cats = str_replace('<a', $linkBefore . '<a' . $linkAttr, $cats);  
            $cats = str_replace('</a>', '</a>' . $linkAfter, $cats);  
            echo vibe_sanitizer($cats);  
            }
            printf($link, get_permalink($parent), __('Attachment','micronet'));  
            global $post;
            if ($showCurrent == 1) echo vibe_sanitizer($delimiter . $before . $post->post_title . $after);  
  
        } elseif ( is_page() && !$post->post_parent ) {  
            global $post;
            if(function_exists('WC')){
              $myaccount_pid = get_option('woocommerce_myaccount_page_id');
              if($post->ID == $myaccount_pid && is_user_logged_in()){
                if( function_exists('bp_loggedin_user_domain') )
                  $link = trailingslashit( bp_loggedin_user_domain() . $post->post_name );
                else
                  $link = get_permalink();
                if ($showCurrent == 1) echo vibe_sanitizer($before . '<a href="'.$link.'">'. $post->post_title .'</a>'. $after);  
              }
            }
            
            if ($showCurrent == 1) echo vibe_sanitizer($before . $post->post_title . $after);    
            
  
        } elseif ( is_page() && $post->post_parent ) { 
            $parent_id  = $post->post_parent;  
            $breadcrumbs = array();  
            while ($parent_id) {  
                $page = get_page($parent_id);  
                
                $pmproaccount_pid = get_option('pmpro_account_page_id');

                if($page->ID == $pmproaccount_pid && is_user_logged_in()){
                   $permalink = trailingslashit( bp_loggedin_user_domain() .$page->post_name );
                    $breadcrumbs[] = sprintf($link, $permalink, get_the_title($page->ID));  
                }else{
                  $breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));    
                }
                
                $parent_id  = $page->post_parent;  
            }  
            $breadcrumbs = array_reverse($breadcrumbs);  
            for ($i = 0; $i < count($breadcrumbs); $i++) {  
                echo vibe_sanitizer($breadcrumbs[$i]);  
                if ($i != count($breadcrumbs)-1) echo vibe_sanitizer($delimiter);  
            }  
            global $post;
            if ($showCurrent == 1) echo vibe_sanitizer($delimiter . $before .  $post->post_title . $after);  
  
        } elseif ( is_tag() ) {  
            echo vibe_sanitizer($before . sprintf($text['tag'], single_tag_title('', false)) . $after);  
  
        } elseif ( is_author() ) {  
            global $author;  
            $userdata = get_userdata($author);  
            echo vibe_sanitizer($before . sprintf($text['author'], $userdata->display_name) . $after);  
  
        } elseif ( !is_single() && !is_page() && !in_Array(get_post_type(),array('post','course','unit','quiz','product','news','forum')) && !is_404() ) {  


            $post_type = get_post_type_object(get_post_type());  

            echo vibe_sanitizer($before . $post_type->labels->menu_name . $after);  
  
        } elseif ( (is_singular() && !is_attachment()) || isset($post->post_type) ) {  


            $post_type_var = $post->post_type;

            switch($post_type_var){
              case 'post':
                  $cat = get_the_category(); 
                  if(isset($cat) && is_array($cat))
                    $cat = $cat[0];  


                  $cats = get_category_parents($cat, TRUE, $delimiter);  
                  if(isset($cats) && !is_object($cats)){
                  if ($showCurrent == 0) 
                    $cats = preg_replace("#^(.+)$delimiter$#", "$1", $cats);  
                  
                  $cats = str_replace('<a', $linkBefore . '<a' . $linkAttr, $cats);  

                  $cats = str_replace('</a>', '</a>' . $linkAfter, $cats);  
                  echo vibe_sanitizer($cats);  
                  }
                  global $post;
                  if ($showCurrent == 1) echo vibe_sanitizer($before . $post->post_title. $after); 
              break;
              case 'product':

                  $shop_page_id = '';
                    $shop_page_id = wc_get_page_id('shop');
                  
                  $shop_page_url = get_permalink( $shop_page_id );
                  $post_type = get_post_type_object(get_post_type());  
                  printf($link, $homeLink . '/' . basename($shop_page_url) . '/', $post_type->labels->singular_name);  
                  global $post;
                  if ($showCurrent == 1) echo vibe_sanitizer($delimiter . $before . $post->post_title . $after); 
              break;
              case 'news':
                  $course_id = get_post_meta(get_the_ID(),'vibe_news_course',true);
                  if(!empty($course_id)){
                    $course_url = get_permalink($course_id);
                  }
                  $slug = $post_type->rewrite;  
                  $post_type = get_post_type_object(get_post_type());  
                  printf($link, $homeLink . '/' . (!empty($course_id)?basename($course_url):$slug['slug']) . '/', (!empty($course_id)?get_the_title($course_id):$post_type->labels->singular_name));  
                  global $post;
                  if ($showCurrent == 1) echo vibe_sanitizer($delimiter . $before . $post->post_title . $after); 
              break;
              case 'course':
                  $post_type =  get_post_type_object($post->post_type); 

                  $course_categories = wp_get_post_terms( $post->ID, 'course-cat', array( 'orderby' => 'term_id' ) );
                  $slug = $post_type->rewrite;  
                  $courses_url = get_permalink(vibe_get_bp_page_id('course'));

                  echo vibe_sanitizer($delimiter . $before .'<a href="'.$courses_url.'">'.$post_type->labels->singular_name.'</a>'. $after);

                  $course_category = '';
                  if(isset($course_categories)){
                    foreach($course_categories as $category){
                      $course_category .= $delimiter . $before .'<a href="'.get_term_link($category).'">'.$category->name.'</a>'. $after; 
                    }
                    echo apply_filters('wplms_breadcrumbs_course_category',$course_category);
                  }

                  global $post;
                  if ($showCurrent == 1) echo vibe_sanitizer($delimiter . $before . $post->post_title . $after); 
              break;
              case 'forum':
                  $post_type = get_post_type_object(get_post_type());  
                  $slug = $post_type->rewrite;  
                  if($slug['slug'] == 'forums/forum')
                    $slug['slug'] = 'forums';
                  printf($link, $homeLink . '/' . $slug['slug'] . '/', $post_type->labels->singular_name);
                  global $post;  
                  if ($showCurrent == 1) echo vibe_sanitizer($delimiter . $before . $post->post_title . $after); 
              break;
              default:
                $defflag = 1;
                if(get_post_type() === 'quiz' ){
                  $course_id = get_post_meta(get_the_ID(),'vibe_quiz_course',true);
                  if(!empty($course_id)){
                      $defflag = 0;
                      printf($link, get_the_permalink($course_id), get_the_title($course_id));
                  }
                }else if(get_post_type() === 'wplms-assignment' ){
                  $course_id = get_post_meta(get_the_ID(),'vibe_assignment_course',true);
                  if(!empty($course_id)){
                      $defflag = 0;
                      printf($link, get_the_permalink($course_id), get_the_title($course_id));
                  }
                }

                if($defflag){
                  $post_type = get_post_type_object(get_post_type());  

                  $slug = $post_type->rewrite;
                  if(!empty($slug)){
                    printf($link, $homeLink . '/' . $slug['slug'] . '/', $post_type->labels->singular_name);
                  }  
                }
                  
                  global $post;  
                  if ($showCurrent == 1) echo vibe_sanitizer($delimiter . $before . $post->post_title . $after); 
              break;
            }
  
        }  elseif ( is_404() ) {  
          echo vibe_sanitizer($before . $text['404'] . $after);  
        }
  
        if ( get_query_var('paged') ) {  
            if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';  
            echo '<li>'.__('Page','micronet') . ' ' . get_query_var('paged').'</li>';  
            if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';  
        }  
  
        echo '</ol></nav>';  
    }  
} // end vibe_breadcrumbs()  
}


if(!function_exists('vibe_sanitizer')){
function vibe_sanitizer($string,$context=null){
  switch ($context) {
    case 'text':
      $string = esc_attr($string);
      break;
    case 'html':
      
      break;
    case 'url':
    
      break;
    default:
      break;
  }
  return apply_filters('vibe_sanitizer_filter',$string,$context);
}
}



function social_sharing_links(){

    $social_sharing = array(
        'Facebook' => 'http://www.facebook.com/share.php?u=[URL]',
        'Twitter' => 'http://twitter.com/share?url=[URL]',
        'VK'=>'http://vk.com/share.php?url=[URL]',
        'Digg' => 'http://www.digg.com/submit?phase=2&url=[URL]&title=[TITLE]',
        'Pinterest' => 'http://pinterest.com/pin/create/button/?url=[URL]',
        'Stumbleupon' => 'http://www.stumbleupon.com/submit?url=[URL]&title=[TITLE]',
        'Delicious' => 'http://del.icio.us/post?url=[URL]&title=[TITLE]]&notes=[DESCRIPTION]',
        'Google plus' => 'https://plus.google.com/share?url=[URL]',
        'GoogleBuzz' => 'http://www.google.com/reader/link?title=[TITLE]&url=[URL]',
        'LinkedIn' => 'http://www.linkedin.com/shareArticle?mini=true&url=[URL]&title=[TITLE]&source=[DOMAIN]',
        'SlashDot' => 'http://slashdot.org/bookmark.pl?url=[URL]&title=[TITLE]',
        'Technorati' => 'http://technorati.com/faves?add=[URL]&title=[TITLE]',
        'Posterous' => 'http://posterous.com/share?linkto=[URL]',
        'Tumblr' => 'http://www.tumblr.com/share?v=3&u=[URL]&t=[TITLE]',
        'Reddit' => 'http://www.reddit.com/submit?url=[URL]&title=[TITLE]',
        'GoogleBookmarks' => 'http://www.google.com/bookmarks/mark?op=edit&bkmk=[URL]&title=[TITLE]&annotation=[DESCRIPTION]',
        'NewsVine' => 'http://www.newsvine.com/_tools/seed&save?u=[URL]&h=[TITLE]',
        'PingFm' => 'http://ping.fm/ref/?link=[URL]&title=[TITLE]&body=[DESCRIPTION]',
        'Evernote' => 'http://www.evernote.com/clip.action?url=[URL]&title=[TITLE]',
        'FriendFeed' => 'http://www.friendfeed.com/share?url=[URL]&title=[TITLE]',
        'Telegram'=>'https://telegram.me/share/url?url=[URL]&text=[TITLE]',
        'Whatsapp'=>'https://wa.me/?text=[URL]'
    );

    return $social_sharing;
}

function vibe_check_plugin_installed($plugin){
  $check_array = get_function_class_array_from_plugin_path($plugin);

  if(is_array($check_array) && !empty($check_array)){
    
    if(is_array($check_array['function']) && !empty($check_array['function'])){
      foreach ($check_array['function'] as $key => $function) {
          if(function_exists($function)){
            return true;
            break;
          }
      }
    }
    if(is_array($check_array['class']) && !empty($check_array['class'])){
      foreach ($check_array['class'] as $key => $class) {
        if(class_exists($class)){
          return true;
          break;
        }
      }
    }
  }
  return false;
}

function get_function_class_array_from_plugin_path($plugin){
  $path = get_all_function_class_array_plugin_path();
  if(!empty($path) && isset($path[$plugin])){
    return $path[$plugin];
  }
  return array();
}

function get_all_function_class_array_plugin_path(){
  return apply_filters('get_all_function_class_array_plugin_path',
          array(
            
            'LayerSlider/layerslider.php' => array(
                                                'function' => array(),
                                                'class' => array('LS_Config'),
                                              ),
          )
  );
}


function micronet_site_header_attributes(){

  $classes = apply_filters('micronet_site_header_classes',['site-header']);

  if(!empty(vibe_get_option('header_fix'))){
    $classes[]='fixed_header';
  }

  echo ' class="'.implode(' ',$classes).'"';
}

if(!function_exists('vibe_update_option')){
    function micronet_update_option($field,$value){    
        $micronet = get_option(THEME_SHORT_NAME);
        if(!empty($micronet)){
            $micronet[$field] = $value;
        }else{
            $micronet = array($field => $value);
        }
        update_option(THEME_SHORT_NAME,$micronet);        
        return; 
    }
}



if(!function_exists('primary_menu_fallback')){

  function primary_menu_fallback() {
    ?>

  <div id="primary-menu" class="hidden bg-gray-100 md:bg-transparent md:flex justify-between items-center"><ul id="menu-primary" class="md:flex md:-mx-4 flex-1">
          <li class="home menu-item <?php if(is_home() || is_front_page()) echo 'current-menu-item'; ?>"><a href="<?php echo esc_url( home_url() ); ?>"><?php _e('Home','micronet');?></a></li>
          <?php
          wp_list_pages(array(
              'depth' => 1, //number of tiers, 0 for unlimited
              'exclude' => '', //comma seperated IDs of pages you want to exclude
              'title_li' => '', 
              'number'=>5,
              'sort_column' => 'post_title', //see documentation for other possibilites
              'sort_order' => 'ASC', //ASCending or DESCending
          ));
          ?>
      </ul>
  </div>
      <?php
  }

}

if(!function_exists('vibe_pagination')){
  function vibe_pagination($pages = '', $range = 4)
  {  
       $showitems = ($range * 2)+1;  
   
       global $paged;
       if(empty($paged)) $paged = 1;
   
       if($pages == '')
       {
           global $wp_query;
           $pages = $wp_query->max_num_pages;
           if(!$pages)
           {
               $pages = 1;
           }
       }   
   
       if(1 != $pages)
       {
           echo "<div class=\"pagination flex mt-4 clear-both items-center gap-4 \"><span class='py-2'>".__('Page','micronet')." ".$paged." ".__('of','micronet')." ".$pages."</span><div class='flex items-center'>";
           if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<a href='".get_pagenum_link(1)."' class='p-2 px-4 border border-slate-100' title='".__('First','micronet')."'><span class='vicon vicon-angle-double-left'></span> </a>";
           if($paged > 1 && $showitems < $pages) echo "<a href='".get_pagenum_link($paged - 1)."' class='p-2 px-4 border border-slate-100' title='".__('Previous','micronet')."'><span class='vicon vicon-angle-left'></span></a>";
           echo '<div class="hidden md:flex">';
           
          
           for ($i=1; $i <= $pages; $i++)
           {
               if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
               {
                   echo (is_numeric($paged) && $paged == $i)? "<span class=\"current bg-primary p-2 px-4 border border-slate-100 \">".$i."</span>":"<a href='".get_pagenum_link($i)."' class=\"inactive p-2 px-4 border border-slate-100\">".$i."</a>";
               }
           }
           echo "</div>\n";
           if ($paged < $pages && $showitems < $pages) echo "<a href=\"".get_pagenum_link($paged + 1)."\" class='p-2 px-4 border border-slate-100' title='".__('Next','micronet')."'><span class='vicon vicon-angle-right'></span></a>";  
           if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo "<a href='".get_pagenum_link($pages)."' class='p-2 px-4 border border-slate-100' title='".__('Last','micronet')."'> <span class='vicon vicon-angle-double-right'></span></a>";
           echo '</div></div>';
       }
  }
}
