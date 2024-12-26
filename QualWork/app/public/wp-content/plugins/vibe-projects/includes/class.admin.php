<?php
/**
 * Admin Menu - Vibe Team Taxonomy
 *
 * @class       VibeProjects_TeamTaxonomy
 * @author      VibeThemes
 * @team    Admin
 * @package     VibeProjects_TeamTaxonomy
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VibeProjects_TeamTaxonomy{
	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeProjects_TeamTaxonomy();
        return self::$instance;
    }
    private function __construct(){
		add_filter( 'manage_team_custom_column',array($this, 'manage_team_column'), 10, 3 );
		add_action( 'team_add_form_fields', array ( $this, 'add_team_color' ), 10, 2 );
		add_action( 'created_team', array ( $this, 'save_team_color' ), 10, 2 );
		add_action( 'team_edit_form_fields', array ( $this, 'update_team_color' ), 10, 2 );
		add_action( 'edited_team', array ( $this, 'updated_team_color' ), 10, 2 );
		add_filter('get_object_terms',[$this,'get_team_color'],10,3);

		add_action( 'admin_menu', array($this,'vibebp_register_menu_page'),11 );
		add_action( 'init', array($this,'vibebp_register_custom_post_type'),5 );
		
		add_action( 'bp_members_admin_user_metaboxes',array($this,'add_user_team_metabox'),10,2);
		add_action( 'bp_members_admin_load', array( $this, 'process_member_team_update' ),99 );
		add_filter( 'manage_edit-team_columns', array($this,'manage_team_user_column') );
		add_action( 'user_new_form', array($this,'edit_user_team_section') );
		add_action( 'user_register', array($this, 'save_user_team_terms') );


		add_action( 'init', [$this,'register_post_statuses'] );
		add_action( 'post_submitbox_misc_actions', [$this,'project_post_status_dropdown']);
		add_action('admin_footer-edit.php',[$this,'custom_status_add_in_quick_edit']);
		add_filter( 'display_post_states', [$this,'display_state']);
	}

	function vibebp_register_menu_page(){
		if(!defined('VIBEBP_VERSION')){
			add_menu_page( __('Projects Dashboard','vibe-projects'), 'Projects', 'manage_options', 'projects', 'vibe_projects_dashboard','dashicons-book',80 );
		}
		add_submenu_page(
	        defined('VIBEBP_VERSION')?'vibebp':'projects',
	        __('Project Types','vibe'),
	        __('Project Types','vibe'),
	        'edit_posts',
	        'edit-tags.php?post_type=project&taxonomy=project-type'
	    );
	    add_submenu_page(
	    	defined('VIBEBP_VERSION')?'vibebp':'boards',
	    	__('Board Types', 'vibe'),
	    	__('Board Types', 'vibe'),
	    	'edit_posts',
	    	'edit-tags.php?post_type=board&taxonomy=board-type'
	    );
	    add_users_page(
			__('Teams','vibe'),
	        __('Teams','vibe'),
	        'manage_options',
			'edit-tags.php?taxonomy=team'
		);
	    /*add_submenu_page(
	        defined('VIBEBP_VERSION')?'vibebp':'projects',
	        __('Project Import/Export','vibe'),
	        __('Project Import/Export','vibe'),
	        'manage_options',
	        'projects-import-export-settings',
	        array($this,'projects_import_export_settings')
	    );
	    */
	}
/*function projects_import_export_settings(){

		echo '<div class="project_import_container"><h3>'.__('Import Projects','vibe-projects').'</h3>
			<form method="post" enctype="multipart/form-data">
			    '.sprintf(__('Select File to upload [ Maximum upload size %s MB(s) ]','vibe-projects'),$this->projects_getmaxium_upload_file_size()).'<br />
			    <input type="file" name="upfile" id="fileToUpload"><br />
			    <input type="submit" value="'.__('Upload File','vibe-projects').'" name="import" class="button-primary">';
			echo '</form></div>';
	}
	function projects_getmaxium_upload_file_size(){
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        $memory_limit = (int)(ini_get('memory_limit'));
        $upload_mb = min($max_upload, $max_post, $memory_limit);
        return $upload_mb;
    }
*/
	function vibebp_register_custom_post_type(){
		if ( ! defined( 'VIBE_PROJECTS_BOARDS_SLUG' ) )
			define( 'VIBE_PROJECTS_BOARDS_SLUG', 'board' );

		if ( ! defined( 'VIBE_PROJECTS_CARDS_SLUG' ) )
			define( 'VIBE_PROJECTS_CARDS_SLUG', 'card' );
		register_post_type( 'project',
			array(
				'labels' => array(
					'name' => __('Projects','vibe-projects'),
					'menu_name' => __('Projects','vibe-projects'),
					'singular_name' => __('Project','vibe-projects'),
					'add_new_item' => __('Add New Project','vibe-projects'),
					'all_items' => __('All Projects','vibe-projects')
				),
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'capability_type' => 'post',
	            'has_archive' => true,
				'show_in_menu' => defined('VIBEBP_VERSION')?'vibebp':'projects',
				'show_in_admin_bar' => true,
				'show_in_nav_menus' => true,
				'supports' => array( 'title','editor','thumbnail','author','comments','excerpt','revisions','custom-fields', 'page-attributes'),
				'hierarchical' => true,
				'rewrite' => array( 'slug' => 'project', 'hierarchical' => true, 'with_front' => false )
			)
		);
		register_taxonomy( 'project-type', array( 'project'),
			array(
				'labels' => array(
				'name' => __('Project Type','vibe-projects'),
				'menu_name' => __('Project Types','vibe-projects'),
				'singular_name' => __('Project Type','vibe-projects'),
				'add_new_item' => __('Add New Type','vibe-projects'),
				'all_items' => __('All Project Types','vibe-projects')
			),
			'public' => true,
			'hierarchical' => true,
			'show_ui' => true,
			'show_in_menu' => false,
			'show_admin_column' => true,
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => true,
			'rewrite' => array( 'slug' => 'project-type', 'hierarchical' => true, 'with_front' => false ),
			)
		);
		register_post_type( 'board',
			array(
				'labels' => array(
					'name' => __('Boards','vibe-projects'),
					'menu_name' => __('Boards','vibe-projects'),
					'singular_name' => __('Board','vibe-projects'),
					'add_new_item' => __('Add New Board','vibe-projects'),
					'all_items' => __('All Boards','vibe-projects')
				),
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'capability_type' => 'post',
	            'has_archive' => false,
				'show_in_menu' => defined('VIBEBP_VERSION')?'vibebp':'projects',
				'show_in_admin_bar' => false,
				'show_in_nav_menus' => false,
				'supports' => array( 'title','editor','thumbnail','author','comments','excerpt','revisions','custom-fields', 'page-attributes'),
				'hierarchical' => false,
				'rewrite' => true 
			)
		);
		register_taxonomy( 'board-type', array( 'board'),
			array(
				'labels' => array(
				'name' => __('Board Type','vibe-projects'),
				'menu_name' => __('Board Types','vibe-projects'),
				'singular_name' => __('Board Type','vibe-projects'),
				'add_new_item' => __('Add New Type','vibe-projects'),
				'all_items' => __('All Board Types','vibe-projects')
			),
			'public' => true,
			'hierarchical' => true,
			'show_ui' => true,
			'show_in_menu' => false,
			'show_admin_column' => true,
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => true,
			'rewrite' => array( 'slug' => 'board-type', 'hierarchical' => true, 'with_front' => false ),
			)
		);
		register_post_type( 'card',
			array(
				'labels' => array(
					'name' => __('Cards','vibe-projects'),
					'menu_name' => __('Cards','vibe-projects'),
					'singular_name' => __('card','vibe-projects'),
					'add_new_item' => __('Add New Card','vibe-projects'),
					'all_items' => __('All Cards','vibe-projects')
				),
				'public' => false,
				'publicly_queryable' => true,
				'show_ui' => true,
				'capability_type' => 'post',
	            'has_archive' => false,
	            'taxonomies'=>['list'],
				'show_in_menu' => false,
				'show_in_admin_bar' => false,
				'show_in_nav_menus' => false,
				'supports' => array( 'title','editor','thumbnail','author','comments','excerpt','revisions','custom-fields', 'page-attributes'),
				'hierarchical' => true,
				'rewrite' => array( 'slug' => 'card', 'hierarchical' => true, 'with_front' => false )
			)
		);
		register_taxonomy( 'list', array( 'board','card'),
			array(
				'labels' => array(
				'name' => __('List','vibe-projects'),
				'menu_name' => __('Lists','vibe-projects'),
				'singular_name' => __('List','vibe-projects'),
				'add_new_item' => __('Add New List','vibe-projects'),
				'all_items' => __('All Lists','vibe-projects')
			),
			'public' => true,
			'hierarchical' => true,
			'show_ui' => true,
			'show_in_menu' => false,
			'show_admin_column' => true,
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => true,
			'rewrite' => array( 'slug' => 'list', 'hierarchical' => true, 'with_front' => false ),
			)
		);
		register_taxonomy( 'team', 'user', array(
			'labels'                     => array(
			    'name'                       => _x( 'Teams', 'Teams Name', 'vibe-projects' ),
			    'singular_name'              => _x( 'Team', 'Team Name', 'vibe-projects' ),
			    'menu_name'                  => __( 'Teams', 'vibe-projects' ),
			    'all_items'                  => __( 'All Teams', 'vibe-projects' ),
			    'parent_item'                => __( 'Parent Team', 'vibe-projects' ),
			    'parent_item_colon'          => __( 'Parent Team:', 'vibe-projects' ),
			    'new_item_name'              => __( 'New Team Name', 'vibe-projects' ),
			    'add_new_item'               => __( 'Add Team', 'vibe-projects' ),
			    'edit_item'                  => __( 'Edit Team', 'vibe-projects' ),
			    'update_item'                => __( 'Update Team', 'vibe-projects' ),
			    'view_item'                  => __( 'View Team', 'vibe-projects' ),
			    'separate_items_with_commas' => __( 'Separate team with commas', 'vibe-projects' ),
			    'add_or_remove_items'        => __( 'Add or remove team', 'vibe-projects' ),
			    'choose_from_most_used'      => __( 'Choose from the most used', 'vibe-projects' ),
			    'popular_items'              => __( 'Popular Teams', 'vibe-projects' ),
			    'search_items'               => __( 'Search Teams', 'vibe-projects' ),
			    'not_found'                  => __( 'Not Found', 'vibe-projects' ),
			    'no_terms'                   => __( 'No team', 'vibe-projects' ),
			    'items_list'                 => __( 'Teams list', 'vibe-projects' ),
			    'items_list_navigation'      => __( 'Teams list navigation', 'vibe-projects' ),
			),
		    'hierarchical'               => false,
		    'public'                     => true,
		    'show_ui'                    => true,
		    'show_admin_column'          => true,
		    'show_in_nav_menus'          => true,
		    'show_tagcloud'              => true,
		  )
		);
	}

	function manage_team_column( $display, $column, $term_id ) {
	  if ( 'users' === $column ) {
	    $term = get_term( $term_id, 'team' );
	    echo $term->count;
	  }
	}
	/**
	 * Unsets the 'posts' column and adds a 'users' column on the manage team admin page.
	 */
	function manage_team_user_column( $columns ) {

	  unset( $columns['posts'] );

	  $columns['users'] = __( 'Users' );

	  return $columns;
	}/**
	 * @param object $user The user object currently being edited.
	 */
	function edit_user_team_section( $user ) {
	  global $pagenow;
	  $tax = get_taxonomy( 'team' );
	  /* Make sure the user can assign terms of the team taxonomy before proceeding. */
	  if ( !current_user_can( $tax->cap->assign_terms ) )
	    return;
	  /* Get the terms of the 'team' taxonomy. */
	  $terms = get_terms( 'team', array( 'hide_empty' => false ) ); ?>
	  <h3><?php _e( 'Teams','vibe-projects' ); ?></h3>
	  <table class="form-table">
	    <tr>
	      <th><label for="team"><?php _e( 'Allocate Teams','vibe-projects' ); ?></label></th>
	      <td><?php
	      /* If there are any team terms, loop through them and display checkboxes. */
	      if ( !empty( $terms ) ) {
	      		if(!empty($user->ID)){
	      			echo $this->custom_form_field('team', $terms,$user->ID);
	      		}else{
	      			echo $this->custom_form_field('team', $terms);		
	      		}
	          
	      }
	      /* If there are no team terms, display a message. */
	      else {
	        _e( 'There are no teams available.','vibe-projects' );
	      }
	      ?></td>
	    </tr>
	  </table>
	<?php }
	/**
	 * return field as dropdown or checkbox, by default checkbox if no field type given
	 * @param: name = taxonomy, options = terms avaliable, userId = user id to get linked terms
	 */
	function custom_form_field( $name, $options, $userId=null, $type = 'checkbox') {
	global $pagenow;
	  switch ($type) {
	    case 'checkbox':
	      foreach ( $options as $term ) : 
	      ?>
	        <label for="team-<?php echo esc_attr( $term->slug ); ?>">
	          <input type="checkbox" name="team[]" id="team-<?php echo esc_attr( $term->slug ); ?>" value="<?php echo $term->slug; ?>" <?php if ( $pagenow !== 'user-new.php' ) checked( true, is_object_in_term( $userId, 'team', $term->slug ) ); ?>>
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
	  	
	      $usrTerms = [];
	      if(!empty($userId)){
	      	$usrTerms = get_the_terms( $userId, 'team');
	      }
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
	/**
	 * @param int $user_id The ID of the user to save the terms for.
	 */
	function save_user_team_terms( $user_id ) {
	  $tax = get_taxonomy( 'team' );
	  /* Make sure the current user can edit the user and assign terms before proceeding. */
	  if ( !current_user_can( 'edit_user', $user_id ) && current_user_can( $tax->cap->assign_terms ) )
	    return false;
		if(!empty($_POST['team'])){
			$term = esc_attr($_POST['team']);
			  $terms = is_array($term) ? $term : (int) $term;
			  if(!is_array($terms)){$terms=[$terms];}
			  wp_set_object_terms( $user_id, $terms, 'team', false);
			  do_action('vibe_projects_user_team_updated',$user_id,$terms);
			  clean_object_term_cache( $user_id, 'team' );
		}
	  
	}
	function add_user_team_metabox($true,$user_id){
		$screen_id = get_current_screen()->id;
		add_meta_box( 'team_taxonomy', __( 'Select Team', 'vibe-projects' ), array($this,'get_team_taxonomy'), $screen_id,'side' );
	}
	function get_team_taxonomy($user = null ){
		    $terms = get_terms([
			    'taxonomy' => 'team',
			    'hide_empty' => false
			]);

			foreach ( $terms as $term ) : 
	      ?>
	        <label for="team-<?php echo esc_attr( $term->name ); ?>">
	          <input type="checkbox" name="team[]" id="team-<?php echo esc_attr( $term->name ); ?>" value="<?php echo $term->name; ?>" <?php checked( true, is_object_in_term( $user->ID, 'team', $term->name ) ); ?>>
	          <?php echo $term->name; ?>
	        </label><br/>
	      <?php
	      endforeach; ?>
	      <?php
        wp_nonce_field( 'bp-member-profile-change-' . $user->ID, 'bp-member-profile-nonce' );	
    }
    function process_member_team_update(){
    	$user_id = $this->get_user_id();

		if ( ! isset( $_POST['bp-member-profile-nonce'] ) || ! isset( $_POST['team'] ) ) {
			return;
		}
		if(empty($_POST['team']))
			return;
				// Permission check.
		if ( ! bp_current_user_can( 'bp_moderate' ) ) {
			return;
		}
		
		$team =$_POST['team'];
		$terms = is_array($team) ? $team : (int) $team;
		
		wp_delete_object_term_relationships($user_id,'team');
		$retval= wp_set_object_terms( $user_id, $team, 'team', false);
		clean_object_term_cache( $user_id, 'team' );

		return $retval;
	}
	private function get_user_id() {
		if ( ! empty( $this->user_id ) ) {
			return $this->user_id;
		}
		$this->user_id = (int) get_current_user_id();

		// We'll need a user ID when not on self profile.
		if ( ! empty( $_GET['user_id'] ) ) {
			$this->user_id = (int) $_GET['user_id'];
		}
		return $this->user_id;
	}
		 
	 /*
	  * Add a form field in the new team page
	  * @since 1.0.0
	 */
	public function add_team_color ( $taxonomy ) { ?>
		<div class="form-field term-image">
			<label for="team-image-id"><?php _e('Team color', 'vibe-projects'); ?></label>
			<input type="color" id="team-image-id" name="team-color" value="">
		</div>
	 <?php
	 }	 
	 /*
	  * Save the form field
	  * @since 1.0.0
	 */
	 public function save_team_color ( $term_id, $tt_id ) {
	   if( isset( $_POST['team-color'] ) && '' !== $_POST['team-color'] ){
	     $color = $_POST['team-color'];
	     add_term_meta( $term_id, 'team-color', $color, true );
	   }
	 }	 
	 /*
	  * Edit the form field
	  * @since 1.0.0
	 */
	 public function update_team_color ( $term, $taxonomy ) { ?>
	   <tr class="form-field term-image-wrap">
	     <th scope="row">
	       <label for="team-image-id"><?php _e( 'Team Color', 'vibe-projects' ); ?></label>
	     </th>
	     <td>
	       <?php $color = get_term_meta ( $term -> term_id, 'team-color', true ); ?>
	       <input type="color" id="team-color-id" name="team-color" value="<?php echo $color; ?>">
	     </td>
	   </tr>
	 <?php
	 }
	/*
	 * Update the form field value
	 * @since 1.0.0
	 */
	 public function updated_team_color ( $term_id, $tt_id ) {
	   if( isset( $_POST['team-color'] ) && '' !== $_POST['team-color'] ){
	     $color = $_POST['team-color'];
	     update_term_meta ( $term_id, 'team-color', $color );
	   } else {
	     update_term_meta ( $term_id, 'team-color', '' );
	   }
	}

	function get_team_color($terms,$object_ids,$taxonomies){

		if(in_array('team',$taxonomies)){
			foreach($terms as $i=>$term){
				if(is_object($term)){
					$terms[$i]->color=get_term_meta($term->term_id,'team-color',true);
				}
			}
		}

		

		return $terms;
	}


	function register_post_statuses(){

		$statuses=[];
		if(function_exists('vibebp_get_setting')){
			$statuses=vibebp_get_setting('project_status','vibe_projects');
		}
		if(!empty($statuses)){
			foreach($statuses['key'] as $i=>$status){
				register_post_status( $status, array(
					'label'                     => $statuses['label'][$i],
					'label_count'               => _n_noop($statuses['label'][$i].'<span class="count">(%s)</span>',$statuses['label'][$i].'<span class="count">(%s)</span>'),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true
				));
			}
		}

		if(function_exists('vibebp_get_setting')){
			$statuses=vibebp_get_setting('board_status','vibe_projects','boards');
		}
		if(!empty($statuses)){
			foreach($statuses['key'] as $i=>$status){
				register_post_status( $status, array(
					'label'                     => $statuses['label'][$i],
					'label_count'               => _n_noop($statuses['label'][$i].'<span class="count">(%s)</span>',$statuses['label'][$i].'<span class="count">(%s)</span>'),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true
				));
			}
		}
		$statuses=[];
		if(function_exists('vibebp_get_setting')){
			$statuses=vibebp_get_setting('card_status','vibe_projects','cards');
		}
		
		if(!empty($statuses)){
			foreach($statuses['key'] as $i=>$status){
				register_post_status( $status, array(
					'label'                     => $statuses['label'][$i],
					'label_count'               => _n_noop($statuses['label'][$i].'<span class="count">(%s)</span>',$statuses['label'][$i].'<span class="count">(%s)</span>'),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true
				));
			}
		}
		
	}

	function project_post_status_dropdown(){

		global $post;
		$status_post_types= ['project','board','card'];

		if(!in_array($post->post_type,$status_post_types))
			return false;

		$status_html='';
		echo "<script>";

		$post_type = $post->post_type;
		$statuses=[];
		if(function_exists('vibebp_get_setting')){
			$statuses=vibebp_get_setting($post_type.'_status','vibe_projects',$post_type.'s');
		}
		if(!empty($statuses)){
			foreach($statuses['key'] as $i=>$status){
				if($post->post_status == $status){
					$status_html .= "document.querySelector( '#post-status-display' ).innerHTML= '".$statuses['label'][$i]."';
						document.querySelector( 'select[name=\"post_status\"]' ).val='$status';";
				}
			}
		}

		echo "document.addEventListener('DOMContentLoaded',function() {
			let option = '';
		";
		if(!empty($statuses)){
			foreach($statuses['key'] as $i=>$status){
			echo "option = document.createElement('option');
				option.setAttribute('value','$status');
				option.innerHTML='".$statuses['label'][$i]."';
			document.querySelector( 'select[name=\"post_status\"]' ).append(option);";
			}
		}
		


		echo $status_html."
		});
		</script>";
	}
	
	function custom_status_add_in_quick_edit() {
		global $post;
		$status_post_types= ['project','board','card'];

		if(empty($post) || !in_array($post->post_type,$status_post_types))
			return false;

		echo "<script>document.addEventListener('DOMContentLoaded',function() { let option = '';";

		
		foreach($status_post_types as $post_type){

			$statuses=[];
			if(function_exists('vibebp_get_setting')){
				$statuses=vibebp_get_setting($post_type.'_status','vibe_projects',$post_type.'s');
			}
			if(!empty($statuses)){
				
				foreach($statuses['key'] as $i=>$status){
				echo "option = document.createElement('option');
					option.setAttribute('value','$status');
					option.innerHTML='".$statuses['label'][$i]."';
				document.querySelector( 'select[name=\"_status\"]' ).append(option);";
				}
			}
		}


		echo "
		});</script>";
	}
		
	function display_state( $states ) {
		global $post;
		$arg = get_query_var( 'post_status' );
		$status_post_types= ['project','board','card'];
		foreach($status_post_types as $post_type){
			$statuses=[];
			if(function_exists('vibebp_get_setting')){
				$statuses=vibebp_get_setting($post_type.'_status','vibe_projects',$post_type.'s');
			}
			if(!empty($statuses)){
				if(!empty($post) && in_Array($post->post_status,$statuses['key'])){
					return array($statuses['label'][array_search($post->post_status,$statuses['key'])]);
				}
			}
		}
		return $states;
	}	
}

VibeProjects_TeamTaxonomy::init();