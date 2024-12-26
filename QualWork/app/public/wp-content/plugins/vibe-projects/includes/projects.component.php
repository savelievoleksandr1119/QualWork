<?php 
add_action( 'bp_loaded', 'bp_projects_load_core_component' );
function bp_projects_load_core_component() {
	class BP_Projects_Component extends BP_Component {
		function __construct() {
			global $bp;
			parent::start(
				VIBE_PROJECTS_SLUG,
				__( 'Projects', 'vibe-projects' ),
				VIBE_PROJECTS_PLUGIN_DIR
			);
			$this->includes();

			
			$bp->active_components[$this->id] = '1';

			
			add_action( 'init', array( &$this, 'register_post_types' ) );
			
		}
		
		public function setup_globals($args=array()) {
			global $bp;

			// Defining the slug in this way makes it possible for site admins to override it
			if ( !defined( 'VIBE_PROJECTS_SLUG' ) )
				define( 'VIBE_PROJECTS_SLUG', $this->id );

			
			
			
			$globals = array(
				'slug'                  => VIBE_PROJECTS_SLUG,
				'root_slug'             => isset( $bp->pages->{$this->id}->slug ) ? $bp->pages->{$this->id}->slug : VIBE_PROJECTS_SLUG,
				'has_directory'         => false, // Set to false if not required
				'directory_title'       => _x( 'Projects Directory', 'projects directory title', 'vibe-projects' ),
				'notification_callback' => 'bp_projects_format_notifications',
				'search_string'         => __( 'Search Projects ...', 'vibe-projects' ),
				//'global_tables'         => $global_tables
			);
			parent::setup_globals( $globals );

		}

		function includes($includes=[]) {
			$includes = array(
			);
			parent::includes( $includes );
		 }
	}
	global $bp;

	$bp->projects = new BP_Projects_Component;
	//print_r($bp);die();
}
