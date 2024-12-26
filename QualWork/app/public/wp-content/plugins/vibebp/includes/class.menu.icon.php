<?php
/**
 * AjaxScripts
 *
 * @class       VibeBP_Register
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vibebp_get_menu_icon($menu_item_id){
	$icon = get_post_meta($menu_item_id,'menu_icon',true);
	
	if(!empty($icon)){
		$icon =base64_decode($icon);;
		if(strlen($icon) > 256){
			return $icon;
		}else{
			return '<span class="'.$icon.'"></span>';
		}	
	}
	return false;
} 


class VibeBP_Menu_Icons{
	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Menu_Icons();
        return self::$instance;
    }

	private function __construct(){
		add_action('admin_enqueue_scripts',array($this,'enqueue_script'));
		add_action( 'wp_nav_menu_item_custom_fields',array($this, 'menu_icon'), 10, 2 );
		add_action( 'wp_update_nav_menu_item', array($this, 'save_menu_icon'), 10, 3 );
		add_filter('vibebp_component_icon', array($this, 'apply_saved_icon'), 999, 2 );
	}

	function enqueue_script($hook){
		if($hook == 'nav-menus.php'){

			wp_enqueue_script( 'menu_vicons', plugins_url('../assets/js/menu_icons.js',__FILE__ ), array('wp-element','wp-data'), VIBEBP_VERSION ,true);
			wp_enqueue_style( 'menu_icons', plugins_url('../assets/css/menu_icons.css',__FILE__ ), array(), VIBEBP_VERSION );
			wp_enqueue_style('vicons',plugins_url('../assets/vicons.css',__FILE__),array(),VIBEBP_VERSION);
			$icons = vicon_list();
			wp_localize_script('menu_vicons','menu_icons',array(
				'icons'=>$icons,
				'translations'=>[
					'select_icon'=>__('Select Icon','vibebp'),
					'search_icon'=>__('Search Icon','vibebp'),
					'svg_icon'=>__('Svg Icon','vibebp'),
					'add_icon'=>__('Add Icon','vibebp')
				]
			));
		}
	}

	function menu_icon( $item_id, $item ) {
		if(empty($this->menu_icons)){
			$this->menu_icons = get_option('vibebp_menu_icons');
		}
		if(empty($this->menu_icons)){
			$this->menu_icons = [];
		}
		$menu_icon = '';
		//$item->classes
		if(!empty($item->classes)){
			if(!empty($item->classes[0]) && $item->classes[0]=='bp-menu' && !empty($item->classes[1])){


				preg_match('/bp-(.*)-nav/', $item->classes[1], $component_name);
				if(!empty($component_name) && !empty($component_name[1]) && !empty( $this->menu_icons[$component_name[1]])){
		    		$menu_icon = $this->menu_icons[$component_name[1]];	
				}

				$icons = vicon_list();
				$icons[]='CUSTOM';
				?>
				<div style="clear: both;">
				    <span class="description"><?php _e( "Menu icon", 'vibebp' ); ?></span><br />
				    <input type="hidden" class="nav-menu-id" value="<?php echo $item_id ;?>" />
				    <div class="logged-input-holder">
				    	<a class="select_vicon_popup" data-id=<?php echo $item_id ;?>><?php _ex('Select Icon','icon selector menu','vibebp'); ?></a>
				        <input type="hidden" name="menu_icon[<?php echo $item_id ;?>]" id="menu-icon-<?php echo $item_id ;?>" value="<?php echo (!empty($this->menu_icons[$component_name[1]] )?$this->wp_kses( $this->menu_icons[$component_name[1]] ):''); ?>" />
				    </div>
				</div>
				<?php
			}
		}
		
	}

	function save_menu_icon( $menu_id, $menu_item_db_id, $args ) {

		if(!current_user_can('manage_options'))
			return;
		if(empty($this->menu_icons)){
			$this->menu_icons = get_option('vibebp_menu_icons');
		}
		if(empty($this->menu_icons)){
			$this->menu_icons = [];
		}
		//
		$component_name='';

		if(!empty($args['menu-item-classes']) && $args['menu-item-classes'][0]=='bp-menu' && !empty($args['menu-item-classes'][1])){
			preg_match('/bp-(.*)-nav/', $args['menu-item-classes'][1], $component_name);
			if(!empty($component_name) && !empty($component_name[1]) ){
				

				if ( !empty( $_POST['menu_icon'][$menu_item_db_id]  ) ) {
					
					$sanitized_data = $this->wp_kses( $_POST['menu_icon'][$menu_item_db_id] );
					$this->menu_icons[$component_name[1]] =$sanitized_data;
				} else {
					unset($this->menu_icons[$component_name[1]] );
				}
				
				
				update_option('vibebp_menu_icons',$this->menu_icons);
			}
		}
	}

	function apply_saved_icon($icon,$component){

		if(empty($this->menu_icons)){
			$this->menu_icons = get_option('vibebp_menu_icons');
		}
		if(empty($this->menu_icons) || empty($this->menu_icons[$component]) ){
			return $icon;
		}
        $icon = urldecode($this->menu_icons[$component]);
	    return $icon;
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

}

VibeBP_Menu_Icons::init();
