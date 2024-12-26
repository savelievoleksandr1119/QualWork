<?php
/**
 * Action functions for Micronet
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Initialization
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;


class Micronet_Setup{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Micronet_Setup();

        return self::$instance;
    }

    private function __construct(){
    	add_filter( 'nav_menu_submenu_css_class',array($this, 'micronet_nav_menu_add_submenu_class'), 10, 3 );
		//add_filter( 'nav_menu_css_class', array($this,'micronet_nav_menu_add_li_class'), 10, 4 );

		add_action('tgmpa_register', array($this,'register_required_plugins'));
		
		add_action( 'admin_init', array($this,'micronet_editor_styles' ));
		add_action( 'wp_head', array($this,'preload_webfonts' ));
		//add_action('init',array($this,'check_theme_type'));

		add_action( 'wp_nav_menu_item_custom_fields',array($this, 'menu_icon'), 10, 2 );
		add_action( 'wp_update_nav_menu_item', array($this, 'save_menu_icon'), 10, 3 );

    }
	

    function check_theme_type(){
    	$theme_type = micronet_get_option('theme_type');
    
    	require_once( ABSPATH . 'wp-admin/includes/file.php' );
		WP_Filesystem();
		global $wp_filesystem;
    	if($theme_type == 1 && !$wp_filesystem->exists(MICRONET_PATH.'/templates')){
			$wp_filesystem->move(MICRONET_PATH.'/gutenberg_templates',MICRONET_PATH.'/templates');
			$wp_filesystem->move(MICRONET_PATH.'/gutenberg_templates/theme.json',MICRONET_PATH.'/theme.json');
    	}

    	if($theme_type == 0 && $wp_filesystem->exists(MICRONET_PATH.'/templates')){
			$wp_filesystem->move(MICRONET_PATH.'/templates',MICRONET_PATH.'/gutenberg_templates');
			$wp_filesystem->move(MICRONET_PATH.'/theme.json',MICRONET_PATH.'/gutenberg_templates/theme.json');
    	}
    }
	
	

	function menu_icon( $item_id, $item ) {
		
		$menu_icon = '';
		if(function_Exists('vicon_list'))
		if(empty($item->classes) || !(!empty($item->classes && !empty($item->classes[0]) &&$item->classes[0]=='bp-menu' && !empty($item->classes[1]))) ){
			$menu_icon = get_post_meta($item_id,'menu_icon',true);
			$icons = vicon_list();
			$icons[]='CUSTOM';
			?>
			<div style="clear: both;">
			    <span class="description"><?php _e( "Menu icon", 'micronet' ); ?></span><br />
			    <input type="hidden" class="nav-menu-id" value="<?php echo intval($item_id) ;?>" />
			    <div class="logged-input-holder">
			    	<a class="select_vicon_popup" data-id=<?php echo intval($item_id) ;?>><?php _ex('Select Icon','icon selector menu','micronet'); ?></a>
			        <input type="hidden" name="menu_icon[<?php echo intval($item_id) ;?>]" id="menu-icon-<?php echo intval($item_id) ;?>" value="<?php echo esc_attr($menu_icon); ?>" />
			    </div>
			</div>
			<?php
		}
	}

	function save_menu_icon( $menu_id, $menu_item_db_id, $args ) {

		if(!current_user_can('manage_options'))
			return;
		

		if ( !empty( esc_attr($_POST['menu_icon'][$menu_item_db_id]  ) )) {
			
			if(strlen(esc_attr($_POST['menu_icon'][$menu_item_db_id])) > 120){
				$sanitized_data = $this->wp_kses( $_POST['menu_icon'][$menu_item_db_id] );
			}else{
				$sanitized_data = sanitize_text_field($_POST['menu_icon'][$menu_item_db_id]);
			}
			
			update_post_meta($menu_item_db_id,'menu_icon',$sanitized_data);
			
		} else {
			delete_post_meta($menu_item_db_id,'menu_icon');
		}
		
	}

	function wp_kses($data){
		$kses_defaults = wp_kses_allowed_html( 'post' );

		$svg_args = array(
		    'svg'   => array(
		        'class' => true,
		        'aria-hidden' => true,
		        'aria-labelledby' => true,
		        'role' => true,
		        'xmlns' => true,
		        'width' => true,
		        'height' => true,
		        'viewbox' => true, // <= Must be lower case!
		    ),
		    'g'     => array( 'fill' => true ),
		    'title' => array( 'title' => true ),
		    'path'  => array( 'd' => true, 'fill' => true,  ),
		);

		$allowed_tags = array_merge( $kses_defaults, $svg_args );

		return wp_kses( $data, $allowed_tags );
	}
	
	function micronet_editor_styles() {

		wp_add_inline_style( 'wp-block-library', $this->get_font_face_styles() );

	}

	function get_font_face_styles() {

		return "
		@font-face{
			font-family: 'RobotoSlab';
			font-weight: 400;
			font-style: normal;
			font-stretch: normal;
			font-display: swap;
			src: url('" . get_theme_file_uri( 'css/fonts/RobotoSlab-Regular-webfont.woff' ) . "') format('woff');
		}

		@font-face{
			font-family: 'RobotoSlab';
			font-weight: 600 700 800;
			font-style: bold;
			font-stretch: normal;
			font-display: swap;
			src: url('" . get_theme_file_uri( 'css/fonts/RobotoSlab-Bold-webfont.woff' ) . "') format('woff');
		}
		@font-face{
			font-family: 'RobotoSlab';
			font-weight: 300;
			font-style: light;
			font-stretch: normal;
			font-display: swap;
			src: url('" . get_theme_file_uri( 'css/fonts/RobotoSlab-Light-webfont.woff' ) . "') format('woff');
		}
		@font-face{
			font-family: 'RobotoSlab';
			font-weight: 100;
			font-style: thin;
			font-stretch: normal;
			font-display: swap;
			src: url('" . get_theme_file_uri( 'css/fonts/RobotoSlab-Thin-webfont.woff' ) . "') format('woff');
		}
		";

	}

	function preload_webfonts() {

		?>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100;400;600;800&display=swap" rel="stylesheet">

		<?php
	}


	
	function micronet_nav_menu_add_li_class( $classes, $item, $args, $depth ) {
		if ( isset( $args->li_class ) ) {
			$classes[] = $args->li_class;
		}

		if ( isset( $args->{"li_class_$depth"} ) ) {
			$classes[] = $args->{"li_class_$depth"};
		}

		return $classes;
	}


	/**
	 * Adds option 'submenu_class' to 'wp_nav_menu'.
	 *
	 * @param string  $classes String of classes.
	 * @param mixed   $item The curren item.
	 * @param WP_Term $args Holds the nav menu arguments.
	 *
	 * @return array
	 */
	function micronet_nav_menu_add_submenu_class( $classes, $args, $depth ) {
		if ( isset( $args->submenu_class ) ) {
			$classes[] = $args->submenu_class;
		}

		if ( isset( $args->{"submenu_class_$depth"} ) ) {
			$classes[] = $args->{"submenu_class_$depth"};
		}

		return $classes;
	}	


	function register_required_plugins() {

	   
	    $force_activate = true;
	    $required = true;
	    if(function_exists('is_wplms_4_0') && !is_wplms_4_0()){
	        $force_activate=false;
	        $required = false;
	    }
	    $purchase_code = get_option('item_purchase_code');
	    $plugins = array(
	        array(
	            'name'                  => 'Buddypress',
	            'slug'                  => 'buddypress',
	            'required'              => true,
	            'force_activation'      => false,
	            'force_deactivation'    => false,
	            'external_url'          => '',
	            'is_callable'				=> 'buddypress',
	            'file'                  => 'buddypress/bp-loader.php',
	        ),
	        array(
	            'name'                  => 'VibeBP',
	            'slug'                  => 'vibebp',
	            'source'                => 'https://micronet.work/verify/?purchase_code='.$purchase_code.'&plugin=wplms.io/vibebp.zip',
	            'required'              => true, 
	            'version'               => '', 
	            'force_activation'      => false,
	            'force_deactivation'    => false,
	            'is_callable'				=> 'vibebp_plugin_update',
	            'external_url'          => '',
        	),
	        array(
	            'name'                  => 'Elementor',
	            'slug'                  => 'elementor',
	            'required'              => (micronet_get_option('theme_type')?false:true),
	            'version'               => '',
	            'force_activation'      => false,
	            'force_deactivation'    => false,
	            'external_url'          => '',
	            'is_callable'				=> 'elementor_load_plugin_textdomain',
	            'file'                  => 'elementor/elementor.php',
	        ),
        	array(
	            'name'                  => 'Vibe Zoom',
	            'slug'                  => 'vibe-zoom',
	            'source'                => 'https://micronet.work/verify/?purchase_code='.$purchase_code.'&plugin=wplms.io/vibe-zoom.zip',
	            'required'              => false, 
	            'version'               => '', 
	            'force_activation'      => false,
	            'force_deactivation'    => false,
	             'is_callable'				=> 'vibebp_zoom_plugin_update',
	            'external_url'          => '',
        	),
        	array(
	            'name'                  => 'Vibe Projects',
	            'slug'                  => 'vibe-projects',
	            'source'                => 'https://micronet.work/verify/?purchase_code='.$purchase_code.'&plugin=vibe/vibe-projects.zip',
	            'required'              => false, 
	            'version'               => '', 
	            'force_activation'      => false,
	            'force_deactivation'    => false,
	            'is_callable'			=> 'vibe_projects_plugin_load_translations',
	            'external_url'          => '',
        	),
        );
	        
	    $plugins = apply_filters('micronet_required_plugins',$plugins);

	    $theme_text_domain = 'micronet';
	    $purchase_code = get_option('item_purchase_code');
	    $config = array(
	        'domain'            =>'micronet',  
	        'default_path'      => '',     
	        'menu'              => 'install-required-plugins', 
	        'has_notices'       => true,                       
	        'is_automatic'      => true,                       
	        'message'           => '',                         
	        'strings'           => array(
	            'page_title'                                => __( 'Install Required Plugins','micronet' ).(empty($purchase_code)?' - Warning Missing Purchase Code':''),
	            'menu_title'                                => __( 'Install Plugins','micronet' ),
	            'installing'                                => __( 'Installing Plugin: %s','micronet' ), // %1$s = plugin name
	            'oops'                                      => __( 'Something went wrong with the plugin API.','micronet' ),
	            'notice_can_install_required'               => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.' ,'micronet'), // %1$s = plugin name(s)
	            'notice_can_install_recommended'            => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.','micronet' ), // %1$s = plugin name(s)
	            'notice_cannot_install'                     => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.','micronet' ), // %1$s = plugin name(s)
	            'notice_can_activate_required'              => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.','micronet' ), // %1$s = plugin name(s)
	            'notice_can_activate_recommended'           => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.','micronet' ), // %1$s = plugin name(s)
	            'notice_cannot_activate'                    => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.','micronet' ), // %1$s = plugin name(s)
	            'notice_ask_to_update'                      => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.','micronet' ), // %1$s = plugin name(s)
	            'notice_cannot_update'                      => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.','micronet' ), // %1$s = plugin name(s)
	            'install_link'                              => _n_noop( 'Begin installing plugin', 'Begin installing plugins','micronet' ),
	            'activate_link'                             => _n_noop( 'Activate installed plugin', 'Activate installed plugins','micronet' ),
	            'return'                                    => __( 'Return to Required Plugins Installer','micronet' ),
	            'plugin_activated'                          => __( 'Plugin activated successfully.','micronet' ),
	            'complete'                                  => __( 'All plugins installed and activated successfully. %s','micronet' ), // %1$s = dashboard link
	            'nag_type'                                  => 'updated' // Determines admin notice type - can only be 'updated' or 'error'
	        )
	    );

	    tgmpa($plugins, $config);
	}

	
}

Micronet_Setup::init();
