<?php
/**
 *  /!\ This is a copy of Walker_Nav_Menu_Edit class in core
 * 
 * Create HTML list of nav menu input items.
 *
 * @package WordPress
 * @since 3.0.0
 * @uses Walker_Nav_Menu
 */

if ( !defined( 'ABSPATH' ) ) exit;

class Walker_Nav_Menu_Edit_Custom extends Walker_Nav_Menu  {

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker_Nav_Menu::start_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 */
	function start_lvl( &$output, $depth = 0, $args = array() ) {}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker_Nav_Menu::end_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 */
	function end_lvl( &$output, $depth = 0, $args = array() ) {}

	/**
	 * Start the element output.
	 *
	 * @see Walker_Nav_Menu::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 * @param int    $id     Not used.
	 */
	/**
	 * @see Walker::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param object $args
	 */
	function start_el(&$output, $item, $depth =0, $args=array(),$id=0) {
	    global $_wp_nav_menu_max_depth;
	   
	    $_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;
	
	    $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
	
	    ob_start();
	    $item_id = esc_attr( $item->ID );
	    $removed_args = array(
	        'action',
	        'customlink-tab',
	        'edit-menu-item',
	        'menu-item',
	        'page-tab',
	        '_wpnonce',
	    );
	
	    $original_title = '';
	    if ( 'taxonomy' == $item->type ) {
	        $original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
	        if ( is_wp_error( $original_title ) )
	            $original_title = false;
	    } elseif ( 'post_type' == $item->type ) {
	        $original_object = get_post( $item->object_id );
	        $original_title = isset( $original_object->post_title ) ? $original_object->post_title : '';
	    }
	
	    $classes = array(
	        'menu-item menu-item-depth-' . $depth,
	        'menu-item-' . esc_attr( $item->object ),
	        'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? 'active' : 'inactive'),
	    );
	
	    $title = $item->title;
	
	    if ( ! empty( $item->_invalid ) ) {
	        $classes[] = 'menu-item-invalid';
	        /* translators: %s: title of menu item which is invalid */
	        $title = sprintf( __( '%s (Invalid)','micronet' ), $item->title );
	    } elseif ( isset( $item->post_status ) && 'draft' == $item->post_status ) {
	        $classes[] = 'pending';
	        /* translators: %s: title of menu item in draft status */
	        $title = sprintf( __('%s (Pending)','micronet'), $item->title );
	    }
	
	    $title = empty( $item->label ) ? $title : $item->label;
	
	    ?>
	    <li id="menu-item-<?php echo vibe_sanitizer($item_id,'text'); ?>" class="<?php echo implode(' ', $classes ); ?>">
	        <dl class="menu-item-bar">
	            <dt class="menu-item-handle">
	                <span class="item-title"><?php echo esc_html( $title ); ?></span>
	                <span class="item-controls">
	                    <span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
	                    <span class="item-order hide-if-js">
	                        <a href="<?php
	                            echo wp_nonce_url(
	                                add_query_arg(
	                                    array(
	                                        'action' => 'move-up-menu-item',
	                                        'menu-item' => $item_id,
	                                    ),
	                                    remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
	                                ),
	                                'move-menu_item'
	                            );
	                        ?>" class="item-move-up"><abbr title="<?php _e('Move up','micronet'); ?>">&#8593;</abbr></a>
	                        |
	                        <a href="<?php
	                            echo wp_nonce_url(
	                                add_query_arg(
	                                    array(
	                                        'action' => 'move-down-menu-item',
	                                        'menu-item' => $item_id,
	                                    ),
	                                    remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
	                                ),
	                                'move-menu_item'
	                            );
	                        ?>" class="item-move-down"><abbr title="<?php _e('Move down','micronet'); ?>">&#8595;</abbr></a>
	                    </span>
	                    <a class="item-edit" id="edit-<?php echo vibe_sanitizer($item_id,'text'); ?>" title="<?php _e('Edit Menu Item','micronet'); ?>" href="<?php
	                        echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : esc_url(add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) ) );
	                    ?>"><span class="screen-reader-text"><?php _e( 'Edit Menu Item','micronet' ); ?></span></a>
	                </span>
	            </dt>
	        </dl>
	
	        <div class="menu-item-settings" id="menu-item-settings-<?php echo vibe_sanitizer($item_id,'text'); ?>">
	            <?php if( 'custom' == $item->type ) : ?>
	                <p class="field-url description description-wide">
	                    <label for="edit-menu-item-url-<?php echo vibe_sanitizer($item_id,'text'); ?>">
	                        <?php _e( 'URL','micronet' ); ?>
	                        <input type="text" id="edit-menu-item-url-<?php echo vibe_sanitizer($item_id,'text'); ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo vibe_sanitizer($item_id,'text'); ?>]" value="<?php echo esc_attr( $item->url ); ?>" />
	                    </label>
	                </p>
	            <?php endif; ?>
	            <p class="description description-thin">
	                <label for="edit-menu-item-title-<?php echo vibe_sanitizer($item_id,'text'); ?>">
	                    <?php _e( 'Navigation Label','micronet' ); ?>
	                    <input type="text" id="edit-menu-item-title-<?php echo vibe_sanitizer($item_id,'text'); ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo vibe_sanitizer($item_id,'text'); ?>]" value="<?php echo esc_attr( $item->title ); ?>" />
	                </label>
	            </p>
	            <p class="description description-thin">
	                <label for="edit-menu-item-attr-title-<?php echo vibe_sanitizer($item_id,'text'); ?>">
	                    <?php _e( 'Title Attribute','micronet' ); ?>
	                    <input type="text" id="edit-menu-item-attr-title-<?php echo vibe_sanitizer($item_id,'text'); ?>" class="widefat edit-menu-item-attr-title" name="menu-item-attr-title[<?php echo vibe_sanitizer($item_id,'text'); ?>]" value="<?php echo esc_attr( $item->post_excerpt ); ?>" />
	                </label>
	            </p>
	            <p class="field-link-target description">
	                <label for="edit-menu-item-target-<?php echo vibe_sanitizer($item_id,'text'); ?>">
	                    <input type="checkbox" id="edit-menu-item-target-<?php echo vibe_sanitizer($item_id,'text'); ?>" value="_blank" name="menu-item-target[<?php echo vibe_sanitizer($item_id,'text'); ?>]"<?php checked( $item->target, '_blank' ); ?> />
	                    <?php _e( 'Open link in a new window/tab','micronet' ); ?>
	                </label>
	            </p>
	            <p class="field-css-classes description description-thin">
	                <label for="edit-menu-item-classes-<?php echo vibe_sanitizer($item_id,'text'); ?>">
	                    <?php _e( 'CSS Classes (optional)','micronet' ); ?>
	                    <input type="text" id="edit-menu-item-classes-<?php echo vibe_sanitizer($item_id,'text'); ?>" class="widefat code edit-menu-item-classes" name="menu-item-classes[<?php echo vibe_sanitizer($item_id,'text'); ?>]" value="<?php echo esc_attr( implode(' ', $item->classes ) ); ?>" />
	                </label>
	            </p>
	            <p class="field-xfn description description-thin">
	                <label for="edit-menu-item-xfn-<?php echo vibe_sanitizer($item_id,'text'); ?>">
	                    <?php _e( 'Link Relationship (XFN)','micronet' ); ?>
	                    <input type="text" id="edit-menu-item-xfn-<?php echo vibe_sanitizer($item_id,'text'); ?>" class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?php echo vibe_sanitizer($item_id,'text'); ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />
	                </label>
	            </p>
	            <p class="field-description description description-wide">
	                <label for="edit-menu-item-description-<?php echo vibe_sanitizer($item_id,'text'); ?>">
	                    <?php _e( 'Description' ,'micronet'); ?>
	                    <textarea id="edit-menu-item-description-<?php echo vibe_sanitizer($item_id,'text'); ?>" class="widefat edit-menu-item-description" rows="3" cols="20" name="menu-item-description[<?php echo vibe_sanitizer($item_id,'text'); ?>]"><?php echo esc_html( $item->description ); // textarea_escaped ?></textarea>
	                    <span class="description"><?php _e('The description will be displayed in the menu if the current theme supports it.','micronet'); ?></span>
	                </label>
	            </p>        
	            
                    <?php
	            /* New fields insertion starts here */
                    
                    $sidebars=$GLOBALS['wp_registered_sidebars'];
                    if($depth == 1){
	            ?>  
                <p class="field-custom description description-wide on_off_mega">
                    <label><?php _e('Enable Mega Menu','micronet') ?></label> 
                    <a href="#" class="on_off <?php if(!empty($item->megamenu_type)){echo 'active';} ?>"></a>
                    
                </p>   
                <div class="mega_menu_type_fields"> 
                <p class="field-custom custom description-wide megamenu_type  <?php if(!empty($item->megamenu_type))echo 'active'; ?>">
	                <label for="edit-menu-item-megamenu_type-<?php echo vibe_sanitizer($item_id,'text'); ?>">
	                    <?php _e( 'Select Mega Menu Type','micronet' ); ?>
                            <select id="edit-menu-item-megamenu_type-<?php echo vibe_sanitizer($item_id,'text'); ?>" name="menu-item-megamenu_type[<?php echo vibe_sanitizer($item_id,'text'); ?>]" class="select_metamenu_type">
                            	<option value=""><?php _e('Set MegaMenu Type','micronet'); ?></option>
                                <option value="sidebar" <?php selected('sidebar',esc_attr($item->megamenu_type));?>><?php _e('Sidebar','micronet'); ?></option>
                                <option value="cat_subcat" <?php selected('cat_subcat',esc_attr($item->megamenu_type)); ?>><?php _e('Category Terms - Sub Category Terms','micronet'); ?></option>
                                <option value="cat_posts" <?php selected('cat_posts',esc_attr($item->megamenu_type)); ?>><?php _e('Category - Posts','micronet'); ?></option>
                            </select>
	                </label>
	            </p>
	             <p class="field-custom description-thin menu_width <?php if(!empty($item->megamenu_type))echo 'active'; ?>">
	                <label for="edit-menu-item-max_elements-<?php echo vibe_sanitizer($item_id,'text'); ?>">
	                    <?php _e( 'Menu Width (default px)','micronet' ); ?>
                            <input type="text" id="edit-menu-item-menu_width-<?php echo vibe_sanitizer($item_id,'text'); ?>" name="menu-item-menu_width[<?php echo vibe_sanitizer($item_id,'text'); ?>]" value="<?php echo (empty($item->menu_width)?'100%':$item->menu_width ); ?>" />
	                </label>
	            </p>
	        	</div>
	            
		            <p class="field-custom custom description-wide sidebar mega_fields select-sidebar  <?php if(!empty($item->megamenu_type) && $item->megamenu_type == 'sidebar')echo 'active'; ?>">
		                <label for="edit-menu-item-sidebar-<?php echo vibe_sanitizer($item_id,'text'); ?>">
		                    <?php _e( 'Select Sidebar','micronet' ); ?>
	                            <select id="edit-menu-item-sidebar-<?php echo vibe_sanitizer($item_id,'text'); ?>" name="menu-item-sidebar[<?php echo vibe_sanitizer($item_id,'text'); ?>]">
	                                <option value="">none</option>
	                                <?php
	                                    foreach($sidebars as $sidebar){
	                                        echo '<option value="'.$sidebar['id'].'" '.selected($sidebar['id'],esc_attr($item->sidebar)).'>'.$sidebar['name'].'</option>';
	                                    }
	                                ?>
	                            </select>
		                </label>
		            </p>
		        
		            <p class="field-custom description-thin mega_fields taxonomy posts <?php if(!empty($item->megamenu_type) && ($item->megamenu_type == 'cat_subcat' || $item->megamenu_type == 'cat_posts'))echo 'active'; ?>">
		                <label for="edit-menu-item-taxonomy-<?php echo vibe_sanitizer($item_id,'text'); ?>">
		                    <?php _e( 'Taxonomy','micronet' ); ?>
	                            <select id="edit-menu-item-taxonomy-<?php echo vibe_sanitizer($item_id,'text'); ?>" name="menu-item-taxonomy[<?php echo vibe_sanitizer($item_id,'text'); ?>]">
	                            <?php
	                            $taxonomies = get_taxonomies(array('public'=> true),'objects');
	                            foreach($taxonomies as $taxonomy){
	                            	?><option value="<?php echo vibe_sanitizer($taxonomy->name); ?>" <?php selected($taxonomy->name,esc_attr($item->taxonomy)); ?>><?php echo vibe_sanitizer($taxonomy->labels->name); ?></option><?php
	                            }
	                            ?>
	                            </select>
		                </label>
		            </p>
		            <p class="field-custom description-thin mega_fields taxonomy posts  hide_taxonomy_terms  <?php if(!empty($item->megamenu_type) && $item->megamenu_type == 'cat_subcat')echo 'active'; ?>">
		                <label for="edit-menu-item-hide_taxonomy_terms-<?php echo vibe_sanitizer($item_id,'text'); ?>">
		                    <?php _e( 'Hide Terms','micronet' ); ?>
	                            <input type="text" id="edit-menu-item-hide_taxonomy_terms-<?php echo vibe_sanitizer($item_id,'text'); ?>" name="menu-item-hide_taxonomy_terms[<?php echo vibe_sanitizer($item_id,'text'); ?>]" value="<?php echo (empty($item->hide_taxonomy_terms)?'':$item->hide_taxonomy_terms ); ?>" />
	                            <span><?php _e('comma separated slugs','micronet'); ?></span>
		                </label>
		            </p>
	            
		            <p class="field-custom description-thin posts mega_fields max_elements  <?php if(!empty($item->megamenu_type) && $item->megamenu_type == 'cat_posts')echo 'active'; ?>">
		                <label for="edit-menu-item-max_elements-<?php echo vibe_sanitizer($item_id,'text'); ?>">
		                    <?php _e( 'Maximum Posts to display','micronet' ); ?>
	                            <input type="text" id="edit-menu-item-max_elements-<?php echo vibe_sanitizer($item_id,'text'); ?>" name="menu-item-max_elements[<?php echo vibe_sanitizer($item_id,'text'); ?>]" value="<?php echo (empty($item->max_elements)?'':$item->max_elements ); ?>" />
		                </label>
		            </p>
	                <p class="field-custom description-thin columns  <?php if(!empty($item->megamenu_type) && $item->megamenu_type == 'sidebar')echo 'active'; ?>">
		                <label for="edit-menu-item-columns-<?php echo vibe_sanitizer($item_id,'text'); ?>">
		                    <?php _e( 'Columns','micronet' ); ?>
	                            <select id="edit-menu-item-columns-<?php echo vibe_sanitizer($item_id,'text'); ?>" name="menu-item-columns[<?php echo vibe_sanitizer($item_id,'text'); ?>]">
	                                <option value="auto" <?php selected('auto',esc_attr($item->columns)); ?>>Auto</option>
	                                <option value="1" <?php selected(1,esc_attr($item->columns)); ?>>1</option>
	                                <option value="2" <?php selected(2,esc_attr($item->columns)); ?>>2</option>
	                                <option value="3" <?php selected(3,esc_attr($item->columns)); ?>>3</option>
	                                <option value="4" <?php selected(4,esc_attr($item->columns)); ?>>4</option>
	                                <option value="5" <?php selected(5,esc_attr($item->columns)); ?>>5</option>
	                                <option value="2_1" <?php selected('2_1',esc_attr($item->columns)); ?>>2:1</option>
	                                <option value="1_2" <?php selected('1_2',esc_attr($item->columns)); ?>>1:2</option>
	                                <option value="3_1" <?php selected('3_1',esc_attr($item->columns)); ?>>3:1</option>
	                                <option value="1_3" <?php selected('1_3',esc_attr($item->columns)); ?>>1:3</option>
	                            </select>
		                </label>
		            </p>
	           
	            <?php
	        }
				// COMPATIBILITY WITH NAV MENU ROLES
				do_action( 'wp_nav_menu_item_custom_fields', $item_id, $item, $depth, $args );
				// end added section
				?>
	            <?php
	            /* New fields insertion ends here */
	            ?>
	            <div class="menu-item-actions description-wide submitbox">
	                <?php if( 'custom' != $item->type && $original_title !== false ) : ?>
	                    <p class="link-to-original">
	                        <?php printf( __('Original: %s','micronet'), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
	                    </p>
	                <?php endif; ?>
	                <a class="item-delete submitdelete deletion" id="delete-<?php echo vibe_sanitizer($item_id,'text'); ?>" href="<?php
	                echo wp_nonce_url(
	                    add_query_arg(
	                        array(
	                            'action' => 'delete-menu-item',
	                            'menu-item' => $item_id,
	                        ),
	                        remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
	                    ),
	                    'delete-menu_item_' . $item_id
	                ); ?>"><?php _e('Remove','micronet'); ?></a> <span class="meta-sep"> | </span> <a class="item-cancel submitcancel" id="cancel-<?php echo vibe_sanitizer($item_id,'text'); ?>" href="<?php echo esc_url( add_query_arg( array('edit-menu-item' => $item_id, 'cancel' => time()), remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) ) ) );
	                    ?>#menu-item-settings-<?php echo vibe_sanitizer($item_id,'text'); ?>"><?php _e('Cancel','micronet'); ?></a>
	            </div>
	
	            <input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo vibe_sanitizer($item_id,'text'); ?>]" value="<?php echo vibe_sanitizer($item_id,'text'); ?>" />
	            <input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo vibe_sanitizer($item_id,'text'); ?>]" value="<?php echo esc_attr( $item->object_id ); ?>" />
	            <input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo vibe_sanitizer($item_id,'text'); ?>]" value="<?php echo esc_attr( $item->object ); ?>" />
	            <input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo vibe_sanitizer($item_id,'text'); ?>]" value="<?php echo esc_attr( $item->menu_item_parent ); ?>" />
	            <input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo vibe_sanitizer($item_id,'text'); ?>]" value="<?php echo esc_attr( $item->menu_order ); ?>" />
	            <input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo vibe_sanitizer($item_id,'text'); ?>]" value="<?php echo esc_attr( $item->type ); ?>" />
	        </div><!-- .menu-item-settings-->
	        <ul class="menu-item-transport"></ul>
	    </li>    
	    <?php
	    
	    $output .= ob_get_clean();

	}
}


class WPLMS_backend_menu extends Walker_Nav_Menu {

	/**
	 * Constructor.
	 *
	 * @see Walker_Nav_Menu::__construct() for a description of parameters.
	 *
	 * @param array $fields See {@link Walker_Nav_Menu::__construct()}.
	 */
	public function __construct( $fields = false ) {
		if ( $fields ) {
			$this->db_fields = $fields;
		}
	}

	/**
	 * Create the markup to start a tree level.
	 *
	 * @see Walker_Nav_Menu::start_lvl() for description of parameters.
	 *
	 * @param string $output See {@Walker_Nav_Menu::start_lvl()}.
	 * @param int $depth See {@Walker_Nav_Menu::start_lvl()}.
	 * @param array $args See {@Walker_Nav_Menu::start_lvl()}.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent<ul class='children'>\n";
	}

	/**
	 * Create the markup to end a tree level.
	 *
	 * @see Walker_Nav_Menu::end_lvl() for description of parameters.
	 *
	 * @param string $output See {@Walker_Nav_Menu::end_lvl()}.
	 * @param int $depth See {@Walker_Nav_Menu::end_lvl()}.
	 * @param array $args See {@Walker_Nav_Menu::end_lvl()}.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent</ul>";
	}

	/**
	 * Create the markup to start an element.
	 *
	 * @see Walker::start_el() for description of parameters.
	 *
	 * @param string $output Passed by reference. Used to append additional
	 *        content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param object $args See {@Walker::start_el()}.
	 * @param int $id See {@Walker::start_el()}.
	 */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $_nav_menu_placeholder;

		$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval($_nav_menu_placeholder) - 1 : -1;
		$possible_object_id = isset( $item->post_type ) && 'nav_menu_item' == $item->post_type ? $item->object_id : $_nav_menu_placeholder;
		$possible_db_id = ( ! empty( $item->ID ) ) && ( 0 < $possible_object_id ) ? (int) $item->ID : 0;

		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$output .= $indent . '<li>';
		$output .= '<label class="menu-item-title">';
		$output .= '<input type="checkbox" class="menu-item-checkbox';

		if ( property_exists( $item, 'label' ) ) {
			$title = $item->label;
		}

		$output .= '" name="menu-item[' . $possible_object_id . '][menu-item-object-id]" value="'. esc_attr( $item->object_id ) .'" /> ';
		$output .= isset( $title ) ? esc_html( $title ) : esc_html( $item->title );
		$output .= '</label>';

		if ( empty( $item->url ) ) {
			$item->url = $item->guid;
		}

		if (!empty($item->post_excerpt) && ! in_array( array( 'bp-menu', 'bp-'. $item->post_excerpt .'-nav' ), $item->classes )) {
			$item->classes[] = 'bp-menu';
			$item->classes[] = 'bp-'. $item->post_excerpt .'-nav';
		}

		// Menu item hidden fields
		$output .= '<input type="hidden" class="menu-item-db-id" name="menu-item[' . $possible_object_id . '][menu-item-db-id]" value="' . $possible_db_id . '" />';
		$output .= '<input type="hidden" class="menu-item-object" name="menu-item[' . $possible_object_id . '][menu-item-object]" value="'. esc_attr( $item->object ) .'" />';
		$output .= '<input type="hidden" class="menu-item-parent-id" name="menu-item[' . $possible_object_id . '][menu-item-parent-id]" value="'. esc_attr( $item->menu_item_parent ) .'" />';
		$output .= '<input type="hidden" class="menu-item-type" name="menu-item[' . $possible_object_id . '][menu-item-type]" value="custom" />';
		$output .= '<input type="hidden" class="menu-item-title" name="menu-item[' . $possible_object_id . '][menu-item-title]" value="'. esc_attr( $item->title ) .'" />';
		$output .= '<input type="hidden" class="menu-item-url" name="menu-item[' . $possible_object_id . '][menu-item-url]" value="'. esc_attr( $item->url ) .'" />';
		$output .= '<input type="hidden" class="menu-item-target" name="menu-item[' . $possible_object_id . '][menu-item-target]" value="'. esc_attr( $item->target ) .'" />';
		$output .= '<input type="hidden" class="menu-item-attr_title" name="menu-item[' . $possible_object_id . '][menu-item-attr_title]" value="'. esc_attr( $item->attr_title ) .'" />';
		$output .= '<input type="hidden" class="menu-item-classes" name="menu-item[' . $possible_object_id . '][menu-item-classes]" value="'. esc_attr( implode( ' ', $item->classes ) ) .'" />';
		$output .= '<input type="hidden" class="menu-item-xfn" name="menu-item[' . $possible_object_id . '][menu-item-xfn]" value="'. esc_attr( $item->xfn ) .'" />';
	}
}



?>