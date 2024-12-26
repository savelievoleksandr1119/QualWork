<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VibeBP_MemberTagsTaxonomy{
	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_MemberTagsTaxonomy();
        return self::$instance;
    }
    private function __construct(){
		//taxnomy
		add_filter( 'manage_member_tag_custom_column',array($this, 'manage_member_tag_column'), 10, 3 );
		add_action( 'member_tag_add_form_fields', array ( $this, 'add_member_tag_color' ), 10, 2 );
		add_action( 'created_member_tag', array ( $this, 'save_member_tag_color' ), 10, 2 );
		add_action( 'member_tag_edit_form_fields', array ( $this, 'update_member_tag_color' ), 10, 2 );
		add_action( 'edited_member_tag', array ( $this, 'updated_member_tag_color' ), 10, 2 );
		add_action( 'admin_menu', array($this,'vibebp_register_menu_page'),11 );
		add_action( 'init', array($this,'vibebp_register_custom_post_type'),5 );
		add_action( 'bp_members_admin_user_metaboxes',array($this,'add_user_member_tag_metabox'),10,2);
		add_action( 'bp_members_admin_load', array( $this, 'process_member_member_tag_update' ),99 );
		add_filter( 'manage_edit-member_tag_columns', array($this,'manage_member_tag_user_column') );

     	add_filter('vibebp_component_icon',array($this,'set_icon'),10,2);
        add_filter('wplms_get_element_icon',array($this,'set_icon'),10,2);
	}
	function vibebp_register_menu_page(){
	    add_users_page(
			__('Member Tags','vibe'),
	        __('Member Tags','vibe'),
	        'manage_options',
			'edit-tags.php?taxonomy=member_tag'
		);
	}

	function vibebp_register_custom_post_type(){
		register_taxonomy( 'member_tag', 'user', array(
			'labels'                     => array(
			    'name'                       => _x( 'Member Tags', 'Member Tags Name', 'vibe-projects' ),
			    'singular_name'              => _x( 'Member Tag', 'Member TagsName', 'vibe-projects' ),
			    'menu_name'                  => __( 'Member Tags', 'vibe-projects' ),
			    'all_items'                  => __( 'All Member Tags', 'vibe-projects' ),
			    'parent_item'                => __( 'Parent Member Tag', 'vibe-projects' ),
			    'parent_item_colon'          => __( 'Parent Member Tag:', 'vibe-projects' ),
			    'new_item_name'              => __( 'New Member TagsName', 'vibe-projects' ),
			    'add_new_item'               => __( 'Add Member Tag', 'vibe-projects' ),
			    'edit_item'                  => __( 'Edit Member Tag', 'vibe-projects' ),
			    'update_item'                => __( 'Update Member Tag', 'vibe-projects' ),
			    'view_item'                  => __( 'View Member Tag', 'vibe-projects' ),
			    'separate_items_with_commas' => __( 'Separate member_tag with commas', 'vibe-projects' ),
			    'add_or_remove_items'        => __( 'Add or remove member tag', 'vibe-projects' ),
			    'choose_from_most_used'      => __( 'Choose from the most used', 'vibe-projects' ),
			    'popular_items'              => __( 'Popular Member Tags', 'vibe-projects' ),
			    'search_items'               => __( 'Search Member Tags', 'vibe-projects' ),
			    'not_found'                  => __( 'Not Found', 'vibe-projects' ),
			    'no_terms'                   => __( 'No member tag', 'vibe-projects' ),
			    'items_list'                 => __( 'Member Tags list', 'vibe-projects' ),
			    'items_list_navigation'      => __( 'Member Tags list navigation', 'vibe-projects' ),
			),
		    'hierarchical'               => false,
		    'public'                     => false,
		    'show_ui'                    => true,
		    'show_admin_column'          => false,
		    'show_in_nav_menus'          => false,
		    'show_tagcloud'              => false,
		  )
		);
	}

	function manage_member_tag_column( $display, $column, $term_id ) {
	  if ( 'users' === $column ) {
	    $term = get_term( $term_id, 'member_tag' );
	    echo $term->count;
	  }
	}
	/**
	 * Unsets the 'posts' column and adds a 'users' column on the manage member_tag admin page.
	 */
	function manage_member_tag_user_column( $columns ) {

	  unset( $columns['posts'] );

	  $columns['users'] = __( 'Users' );

	  return $columns;
	}

	/**
	 * return field as dropdown or checkbox, by default checkbox if no field type given
	 * @param: name = taxonomy, options = terms avaliable, userId = user id to get linked terms
	 */
	function custom_form_field( $name, $options, $userId, $type = 'checkbox') {
	global $pagenow;
	  switch ($type) {
	    case 'checkbox':
	      foreach ( $options as $term ) : 
	      ?>
	        <label for="member_tag-<?php echo esc_attr( $term->slug ); ?>">
	          <input type="checkbox" name="member_tag[]" id="member_tag-<?php echo esc_attr( $term->slug ); ?>" value="<?php echo $term->slug; ?>" <?php if ( $pagenow !== 'user-new.php' ) checked( true, is_object_in_term( $userId, 'member_tag', $term->slug ) ); ?>>
	          <?php echo $term->name; ?>
	        </label><br/>
	      <?php
	      endforeach;
	    break;
	    case 'dropdown':
	      $selectTerms = [];
	      foreach ( $options as $term ) {
	        $selectTerms[$term->term_id] = $term->name;
	      }
	  
	      // get all terms linked with the user
	      $usrTerms = get_the_terms( $userId, 'member_tag');
	      $usrTermsArr = [];
	      if(!empty($usrTerms)) {
	        foreach ( $usrTerms as $term ) {
	          $usrTermsArr[] = (int) $term->term_id;
	        }
	      }
	      // Dropdown
	      echo "<select name='{$name}'>";
	      echo "<option value=''>-Select-</option>";
	      foreach( $selectTerms as $options_value => $options_label ) {
	        $selected = ( in_array($options_value, array_values($usrTermsArr)) ) ? " selected='selected'" : "";
	        echo "<option value='{$options_value}' {$selected}>{$options_label}</option>";
	      }
	      echo "</select>";
	    break;
	  }
	}
	
	function add_user_member_tag_metabox($true,$user_id){
		$screen_id = get_current_screen()->id;
		add_meta_box( 'member_tag_taxonomy', __( 'Select Member Tag', 'vibe-projects' ), array($this,'get_member_tag_taxonomy'), $screen_id,'side' );
	}
	function get_member_tag_taxonomy($user = null ){
		    $terms = get_terms([
			    'taxonomy' => 'member_tag',
			    'hide_empty' => false,
			]);
			

			foreach ( $terms as $term ) : 
	      ?>
	        <label for="member_tag-<?php echo esc_attr( $term->name ); ?>">
	          <input type="checkbox" name="member_tag[]" id="member_tag-<?php echo esc_attr( $term->name ); ?>" value="<?php echo $term->name; ?>" <?php checked( true, is_object_in_term( $user->ID, 'member_tag', $term->name ) ); ?>>
	          <?php echo $term->name; ?>
	        </label><br/>
	      <?php
	      endforeach; ?>
	      <?php
        wp_nonce_field( 'bp-member-profile-change-' . $user->ID, 'bp-member-profile-nonce' );	
    }
    function process_member_member_tag_update(){
    	$user_id = $this->get_user_id();

		if ( ! isset( $_POST['bp-member-profile-nonce'] ) || ! isset( $_POST['member_tag'] ) ) {
			return;
		}
		if(empty($_POST['member_tag']))
			return;
				// Permission check.
		if ( ! bp_current_user_can( 'bp_moderate' ) ) {
			return;
		}
		
		$member_tag =sanitize_text_field($_POST['member_tag']);
		$terms = is_array($member_tag) ? $member_tag : (int) $member_tag;
		
		wp_delete_object_term_relationships($user_id,'member_tag');
		$retval= wp_set_object_terms( $user_id, $member_tag, 'member_tag', false);
		clean_object_term_cache( $user_id, 'member_tag' );

		return $retval;
	}
	private function get_user_id() {
		if ( ! empty( $this->user_id ) ) {
			return $this->user_id;
		}
		$this->user_id = (int) get_current_user_id();

		// We'll need a user ID when not on self profile.
		if ( ! empty( $_GET['user_id'] ) ) {
			$this->user_id = intval( $_GET['user_id']);
		}
		return $this->user_id;
	}
	
	 /*
	  * Add a form field in the new member_tag page
	  * @since 1.0.0
	 */
	public function add_member_tag_color ( $taxonomy ) { ?>
		<div class="form-field term-color">
			<label for="member-tag-color"><?php _e('Color', 'vibebp'); ?></label>
			<input type="color" id="member-tag-color" name="member-tag-color" class="custom_media_url" value="">
		</div>
	 <?php
	 }	 
	 /*
	  * Save the form field
	  * @since 1.0.0
	 */
	 public function save_member_tag_color ( $term_id, $tt_id ) {
	   if( isset( $_POST['member-tag-color'] ) && '' !== $_POST['member-tag-color'] ){
	     $color = sanitize_text_field($_POST['member-tag-color']);
	     add_term_meta( $term_id, 'member-tag-color', $color, true );
	   }
	 }	 
	 /*
	  * Edit the form field
	  * @since 1.0.0
	 */
	 public function update_member_tag_color ( $term, $taxonomy ) { ?>
	   <tr class="form-field term-color-wrap">
	     <th scope="row">
	       <label for="member-tag-color"><?php _e( 'Color', 'vibebp' ); ?></label>
	     </th>
	     <td>
	       <?php $color_id = get_term_meta ( $term -> term_id, 'member-tag-color', true ); ?>
	       <input type="color" id="member-tag-color" name="member-tag-color" value="<?php echo $color_id; ?>">
	     </td>
	   </tr>
	 <?php
	 }
	/*
	 * Update the form field value
	 * @since 1.0.0
	 */
	 public function updated_member_tag_color ( $term_id, $tt_id ) {
	   if( isset( $_POST['member-tag-color'] ) && '' !== $_POST['member-tag-color'] ){
	     $color = sanitize_text_field($_POST['member-tag-color']);
	     update_term_meta ( $term_id, 'member-tag-color', $color );
	   } else {
	     update_term_meta ( $term_id, 'member-tag-color', '' );
	   }
	 }



	function set_icon($icon,$component_name){

        if($component_name == 'members_detail'){
            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path style="fill:none" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>';
        }
        return $icon;
    }
	
}

VibeBP_MemberTagsTaxonomy::init();