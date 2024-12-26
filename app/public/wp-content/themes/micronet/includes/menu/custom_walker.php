<?php
/**
 * Custom Walker
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

if ( !defined( 'ABSPATH' ) ) exit;

class Vibe_Walker extends Walker_Nav_Menu{
       
    function start_el(&$output, $item, $depth =0, $args=Array(),$id=0){ 
        global $wp_query;
        $menuargs = $args;
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
        $class_names = $value = '';
        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );

        $width = 0;
        if((!empty($item->sidebar) && strlen($item->sidebar) > 2) || (!empty($item->megamenu_type))){
                $class_names .= ' megadrop '.(empty($item->megamenu_type)?'':$item->megamenu_type);
        }
             
        if(!empty($item->menu_width)){
            $width = (is_numeric($item->menu_width)?$item->menu_width.'px':$item->menu_width);         
        }
        if($width == 'container'){
            $width = '100%';
        }
             
        $class_names = ' class="'. esc_attr( $class_names ) . '" '.($depth == 1 && !empty($item->menu_width)?'data-width="'.$width.'"':'');

        $output .= $indent . '<li id="main-menu-item-'. $item->ID . '"' . $value . $class_names .'>';

        $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
        $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
        $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
        $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

        $prepend = '';
        $append = '';
        $description  = ! empty( $item->description ) ? '<span>'.esc_attr( $item->description ).'</span>' : '';

        $item_output = $menuargs->before;

        if(!empty($depth) && $depth == 1 && !empty($item->megamenu_type)){
            
            $mega_menu_id = 'vibe-mega-'.sanitize_title($item->megamenu_type).rand(0,99);
                  
            if($item->columns == 'auto'){
                $item->columns = ' auto-cols-fr';
            }
          
            $class= ' gap-4 grid grid-cols-'.$item->columns;  
              
            if($item->megamenu_type == 'sidebar'){
                $item_output .= $this->sidebar($item->sidebar,$item->columns,$item);
            }


            if($item->megamenu_type == 'cat_subcat'){

                $item_output .= '<div id="'.$mega_menu_id.'" class="menu-cat_subcat">';
                if(!empty($item->taxonomy)){
                    $args =array(
                                'taxonomy' => $item->taxonomy,
                                'hide_empty' => false,
                              );
                  
                    $args = apply_filters('micronet_megamenu_post_cat_subcat',$args,$item);
                    $terms = get_terms($args );
                    $term_array = $hide_terms = array();
                    if(!empty($item->hide_taxonomy_terms)){
                      $hide_terms = explode(',',$item->hide_taxonomy_terms);
                    }

                    if(!empty($terms)){
                      foreach($terms as $term){
                        if(!in_array($term->slug,$hide_terms)){
                          if(empty($term->parent)){
                            $term_array[$term->term_id]['term'] = array('title'=>$term->name,'slug'=>$term->slug);  
                          }else{
                            $term_array[$term->parent]['children'][$term->term_id] =  array('title'=>$term->name,'slug'=>$term->slug);  
                          }
                        }
                      }
                    }


                    if(!empty($term_array)){
                      $item_output .= '<ul class="taxonomy_menu p-4 '.$item->taxonomy.'_menu">';
                     
                         

                        foreach($term_array as $id => $t){
                          
                          $item_output .= '<li>';
                          $item_output .= '<a href="'.get_term_link($id).'" class="term_'.(empty($t['term']['slug'])?'':$t['term']['slug']).'">'.$t['term']['title'].'</a>';
                        
                        if(isset($t['children'])){
                          $item_output .='<div class="sub_cat_menu gap-4 p-4 columns-'.$item->columns.' '.$item->taxonomy.'_'.(empty($t['term']['slug'])?'':$t['term']['slug']).'_menu flex-wrap"><div class="taxonomy_submenu"><div class="row">';

                          foreach($t['children'] as $k=>$child){

                            $item_output .= '<div class="'.$class.'"><a href="'.get_term_link($k).'">'.$child['title'].'</a></div>';
                          }
                          $item_output .='</div></div></div>';
                        }
                        $item_output .= '</li>';
                      }  
                      $item_output .= '</ul>';
                    }
                    
                }
                $item_output .= '</div>';
            }
            if($item->megamenu_type == 'cat_posts'){

                $item_output .= '<div id="'.$mega_menu_id.'" class="menu-cat_subcat ">';
                if(!empty($item->taxonomy)){
                    $args =array(
                                'taxonomy' => $item->taxonomy,
                                'hide_empty' => true,
                              );
                    
                    $args = apply_filters('micronet_megamenu_cat_posts',$args,$item);
                    $terms = get_terms($args );


                    if(!empty($terms)){
                      $hide_terms = array();
                      if(!empty($item->hide_taxonomy_terms)){
                        $hide_terms = explode(',',$item->hide_taxonomy_terms);
                      }

                      $item_output .= '<div class="mega_menu_cat_posts '.$item->taxonomy.'_posts_menu flex '.(($item->menu_width == 'container')?'container m-auto':'').'">';

                      if(!is_wp_error($terms)){
                        $item_output .= '<div class="mega_menu_term_list">';
                        $cat_output = '';
                        foreach($terms as $term){
                          if(!in_array($term->slug,$hide_terms)){
                            
                            $item_output .= '<div class="mega_menu_term" data-id="term_id_'.$term->term_id.'">';
                            if($item->taxonomy == 'service-type'){
                              
                              $item_output .= '<a href="'.get_term_link($term->term_id).'" class="flex flex-col"><strong>'.$term->name.'</strong><span class="text-sm">'.$term->description.'</span></a>';
                            }else{
                              $item_output .= '<a href="'.get_term_link($term->term_id).'">'.$term->name.'</a>';
                            }
                            

                            $class= ' gap-4 grid grid-cols-'.$item->columns;  
                            $cat_output .='<div class="mega_sub_posts_menu p-4 term_id_'.$term->term_id.'_posts taxonomy_submenu '.$item->taxonomy.'_'.$term->slug.'_menu flex-wrap '.$class.'">';

                            $max = (empty($item->max_elements)?$item->columns:$item->max_elements);
                            $args = apply_filters('wplms_megamenu_filter',array(
                                'post_type' => 'any',  
                                'orderby'=>'menu_order',
                                'order'=>'desc',
                                'tax_query' => array(
                                    array(
                                        'taxonomy' => $item->taxonomy, 
                                        'field' => 'slug',     
                                        'terms' => $term->slug,
                                    )
                                ),
                                'posts_per_page'=>$max,
                                'cache_results'=>true
                            ),$item);

                            $query = new WP_Query( $args );
                            
                            while ( $query->have_posts() ) : $query->the_post();
                              global $post;
                              $cat_output .= '<div class="cat_posts_wrapper flex-1 basis-12">';
                              

                              if($post->post_type == 'service' && class_exists('Vibe_Appointments_Actions')){
                                $actions = Vibe_Appointments_Actions::init();
                                add_action('wp_footer',function(){$actions = Vibe_Appointments_Actions::init();$actions->service_type_block_styles('service_adblock');});
                                ob_start();
                                $actions->service_adblock($post,'service_adblock');
                                $cat_output .= ob_get_clean();
                              }else{
                                $cat_output .= '<a href="'.get_permalink($post->ID).'">';
                                $cat_output .= '<div class="menu_featured">';
                                if(has_post_thumbnail()){ 
                                $cat_output .= get_the_post_thumbnail($post->ID,'small'); 
                              }
                              $cat_output .= '<strong>'.get_the_title().'</strong>';

                              $cat_output .= '</div>';
                              $cat_output .= '</a>'; 
                              }
                              
                              $cat_output .='</div>';
                            endwhile;

                            wp_reset_query();
                            $cat_output .='</div>';
                            
                          }
                          $item_output .= '</div>';
                        }
                        
                        $item_output .= '</div><div class="mega_posts flex-grow">'.$cat_output.'</div>';
                        $item_output .= '</div>';
                      }
                    }
                }
                $item_output .= '</div>';
            }
        }else{

            $icon= 0;
            if(function_exists('vibebp_get_menu_icon')){
                $icon = vibebp_get_menu_icon($item->ID);
                if(!empty($icon)){
                  $attributes .= ' class="flex gap-4 flex-row p-2 rounded items-center"';
                  $menuargs->link_before = '<span class="flex flex-col">';
                  $menuargs->link_after = '</span>';
                }else{
                  $attributes .= ' class="flex flex-col px-4 py-2"';
                }
            }
                
            $item_output .= '<a'. $attributes .'>';

            if(!empty($icon)){
                $item_output .= $icon;
            }


            $item_output .= $menuargs->link_before .$prepend.apply_filters( 'the_title', $item->title, $item->ID ).$append;

            $item_output .= '<span class="text-sm">'.$description.'</span>';
            
            if(!empty($item->attr_title))
              $item_output .= '<span class="text-sm">'.$item->attr_title.'</span>';

            $item_output .= $menuargs->link_after;
            $item_output .= '</a>';
            
            
            $item_output .= $menuargs->after;
        }

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $menuargs );
    }
            
        /* 
	 * Show a sidebar
	 */
	function sidebar($name,$columns,$item){

		if(function_exists('dynamic_sidebar')){
			ob_start();
          if(is_object($item)){
            $width = (is_numeric($item->menu_width)?$item->menu_width.'px':$item->menu_width);
          }
          $class= ' ';
          if($width == 'container'){
            $class='container mx-auto';
          }

          if(empty($width) || $width == 'container'){$width = '100%';}
			echo '<div id="vibe-mega-'.sanitize_title($name).'" data-width="'.$width.'" class="megamenu-sidebar gap-4 grid grid-cols-'.$columns.' '.$class.'">';
			  dynamic_sidebar($name);		
			echo '</div>';
			return ob_get_clean();
		}

		return 'none';
	}


}

class Vibe_Menu_Icon_Walker extends Walker_Nav_Menu{

  function start_el(&$output, $item, $depth =0, $args=Array(),$id=0){ 


    global $wp_query;
     $menuargs = $args;
     $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

     $class_names = $value = '';

     $classes = empty( $item->classes ) ? array() : (array) $item->classes;
     
     $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );

     
       
       $class_names = ' class="'. esc_attr( $class_names ) . '" ';

  

     $output .= $indent . '<li id="main-menu-item-'. $item->ID . '"' . $value . $class_names .'>';

     $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
     $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
     $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
     $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

     $prepend = '';
     $append = '';
     $description  = ! empty( $item->description ) ? '<span>'.esc_attr( $item->description ).'</span>' : '';

    
    $item_output = $menuargs->before;
    $icon= 0;
    if(function_exists('vibebp_get_menu_icon')){
        $icon = vibebp_get_menu_icon($item->ID);

        if(!empty($icon)){
          $attributes .= ' class="flex gap-2 flex-row p-2 rounded"';
          $menuargs->link_before = '<span class="flex flex-col">';
          $menuargs->link_after = '</span>';
        }else{
          $attributes .= ' class="flex flex-col"';
        }
    }
        
    $item_output .= '<a'. $attributes .'>';

    if(!empty($icon)){
      $item_output .= $icon;
    }


      $item_output .= $menuargs->link_before .$prepend.apply_filters( 'the_title', $item->title, $item->ID ).$append;
      $item_output .= $description;
      
      if(!empty($item->attr_title))
        $item_output .= '<span class="text-sm opacity-60">'.$item->attr_title.'</span>';

      $item_output .= $menuargs->link_after;
      $item_output .= '</a>';
    
    
    $item_output .= $menuargs->after;

    $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $menuargs );
  }

}
