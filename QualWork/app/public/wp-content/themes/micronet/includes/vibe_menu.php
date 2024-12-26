<?php

if ( !defined( 'ABSPATH' ) ) exit;


class vibe_menu {

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {

		// add custom menu fields to menu
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'vibe_add_nav_fields' ) );

		// save menu custom fields
		add_action( 'wp_update_nav_menu_item', array( $this, 'vibe_update_nav_fields'), 10, 3 );
		
		// edit menu walker
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'vibe_edit_walker'), 10, 2 );

	} // end constructor
	
	
	
	/**
	 * Add custom fields to $item nav object
	 * in order to be used in custom Walker
	 *
	 * @access      public
	 * @since       1.0 
	 * @return      void
	*/
	function vibe_add_nav_fields( $menu_item ) {
	      $menu_item->megamenu_type = get_post_meta( $menu_item->ID, '_menu_item_megamenu_type', true );
        $menu_item->taxonomy = get_post_meta( $menu_item->ID, '_menu_item_taxonomy', true );
        $menu_item->hide_taxonomy_terms = get_post_meta( $menu_item->ID, '_menu_item_hide_taxonomy_terms', true );
	    	$menu_item->sidebar = get_post_meta( $menu_item->ID, '_menu_item_sidebar', true );
        $menu_item->max_elements = get_post_meta( $menu_item->ID, '_menu_item_max_elements', true );
        $menu_item->columns = get_post_meta( $menu_item->ID, '_menu_item_columns', true );
        $menu_item->menu_width = get_post_meta( $menu_item->ID, '_menu_item_menu_width', true );
        
	    return $menu_item;
	    
	}
	
	/**
	 * Save menu custom fields
	 *
	 * @access      public
	 * @since       1.0 
	 * @return      void
	*/
	function vibe_update_nav_fields( $menu_id, $menu_item_db_id, $args ) {
		
      if ( isset($_REQUEST['menu-item-megamenu_type']) && is_array( $_REQUEST['menu-item-megamenu_type']) ) {

      		if(!empty($_REQUEST['menu-item-megamenu_type'][$menu_item_db_id])){
      			$megamenu_type = $_REQUEST['menu-item-megamenu_type'][$menu_item_db_id];
          	update_post_meta( $menu_item_db_id, '_menu_item_megamenu_type', $megamenu_type );	

          	//Save sidebar
          	if ( isset($_REQUEST['menu-item-sidebar']) && is_array( $_REQUEST['menu-item-sidebar']) ){
			        if(!empty($_REQUEST['menu-item-sidebar'][$menu_item_db_id])){
			        	$sidebar_value = $_REQUEST['menu-item-sidebar'][$menu_item_db_id];
			        	update_post_meta( $menu_item_db_id, '_menu_item_sidebar', $sidebar_value );

			        }
			    	}

			    	if ( isset($_REQUEST['menu-item-columns']) && is_array( $_REQUEST['menu-item-columns']) ) {
			    			if(!empty($_REQUEST['menu-item-columns'][$menu_item_db_id])){
			    				$sidebar_columns = $_REQUEST['menu-item-columns'][$menu_item_db_id];	
			    				update_post_meta( $menu_item_db_id, '_menu_item_columns', $sidebar_columns );
			    			}
				    }
      		}
          

          if ( isset($_REQUEST['menu-item-taxonomy']) && is_array( $_REQUEST['menu-item-taxonomy']) ) {
          		if(!empty($_REQUEST['menu-item-taxonomy'][$menu_item_db_id])){
          			$taxonomy = $_REQUEST['menu-item-taxonomy'][$menu_item_db_id];
		          	update_post_meta( $menu_item_db_id, '_menu_item_taxonomy', $taxonomy );	
          		}
		          
		      }

		      if ( isset($_REQUEST['menu-item-hide_taxonomy_terms']) && is_array( $_REQUEST['menu-item-hide_taxonomy_terms']) ) {
		      	if(!empty($_REQUEST['menu-item-hide_taxonomy_terms'][$menu_item_db_id])){
		      		$taxonomy = $_REQUEST['menu-item-hide_taxonomy_terms'][$menu_item_db_id];
		      		update_post_meta( $menu_item_db_id, '_menu_item_hide_taxonomy_terms', $taxonomy );
		      	}
		      }
		      if ( isset($_REQUEST['menu-item-max_elements']) && is_array( $_REQUEST['menu-item-max_elements']) ) {
		      	if(!empty($_REQUEST['menu-item-max_elements'][$menu_item_db_id])){
		      		$max_elements = $_REQUEST['menu-item-max_elements'][$menu_item_db_id];
		      		update_post_meta( $menu_item_db_id, '_menu_item_max_elements', $max_elements );
		      	}
		      }
		      if ( isset($_REQUEST['menu-item-menu_width']) && is_array( $_REQUEST['menu-item-menu_width']) ) {
		      		if(!empty($_REQUEST['menu-item-menu_width'][$menu_item_db_id])){
		      			$menu_width = $_REQUEST['menu-item-menu_width'][$menu_item_db_id];	
		      			update_post_meta( $menu_item_db_id, '_menu_item_menu_width', $menu_width );
		      		}
		      }
      }
	}
	
	/**
	 * Define new Walker edit
	 *
	 * @access      public
	 * @since       1.0 
	 * @return      void
	*/
	function vibe_edit_walker($walker,$menu_id) {
	
	    return 'Walker_Nav_Menu_Edit_Custom';
	    
	}

}

// instantiate plugin's class
$GLOBALS['vibe_menu'] = new vibe_menu();


include_once MICRONET_THEME_FILE_INCLUDE_PATH.'/includes/menu/edit_custom_walker.php';
include_once MICRONET_THEME_FILE_INCLUDE_PATH.'/includes/menu/custom_walker.php';