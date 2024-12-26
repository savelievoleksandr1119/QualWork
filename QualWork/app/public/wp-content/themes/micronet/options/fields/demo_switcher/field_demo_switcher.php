<?php
class Vibe_Options_demo_switcher extends Vibe_Options{	
	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since Vibe_Options 1.0
	*/
	function __construct($field = array(), $value = '', $parent = ''){
		
		parent::__construct($parent->sections, $parent->args, $parent->extra_tabs);
		$this->field = $field;
		$this->value = $value;
		//$this->render();
		
	}//function
	
	
	
	/**
	 * Field Render Function.
	 *
	 * Takes the vars and outputs the HTML for the field in the settings
	 *
	 * @since Vibe_Options 1.0
	*/
	function render(){
		
		$plugins_installed_function_check = [
			'vibebp_plugin_load_translations',
			'WC','x',
			'Vibe_Appointments_Plugin_updater'
		];
		$flag = 0;
		foreach($plugins_installed_function_check as $fx){
			if(!function_exists($fx)){
				$flag=1;
				break;
			}
		}

		$class = (isset($this->field['class']))?'class="'.$this->field['class'].'" ':'';
		
		echo '<div id="demo_switcher_wrapper_margin"></div><div id="demo_switcher_wrapper"><fieldset>';
		
		if($flag){
			?>
			<div class="error">
				<p>
					<strong>Required plugins missing</strong>
				</p>
				<p>
					Installed plugins missing.</p>
				
					<a href="<?php echo admin_url('themes.php?page=install-required-plugins'); ?>" class=" button-primary">Install plugins now	</a>
				</p>
			</div>
			<?php
		}			
			foreach($this->field['options'] as $k => $v){

				$selected = (checked($this->value, $k, false) != '')?' vibe-radio-img-selected':'';

				echo '<label class="vibe-radio-img'.$selected.' vibe-radio-img-'.$this->field['id'].'" for="'.$this->field['id'].'_'.array_search($k,array_keys($this->field['options'])).'">';

				echo '<input type="radio" id="'.$this->field['id'].'_'.array_search($k,array_keys($this->field['options'])).'" name="'.$this->args['opt_name'].'['.$this->field['id'].']" '.$class.' value="'.$k.'" '.checked($this->value, $k, false).'/>';
				echo '<img src="'.$v['img'].'" alt="'.(empty($v['title'])?'demo':$v['title']).'" onclick="jQuery:vibe_demo_switcher_select(\''.$this->field['id'].'_'.array_search($k,array_keys($this->field['options'])).'\', \''.$this->field['id'].'\');" />';
				echo '<br/><span>'.$v['title'].'</span>';
				echo '<small class="demo_switcher_overlay">
				<a class="button button-primary import_demo import_complete_demo" data-demo="'.$k.'">'._x('Import Complete Demo','','micronet').'</a>
				<a class="button button-primary import_demo import_demo_layout" data-demo="'.$k.'">'._x('Import Styles only','','micronet').'</a>
				<a class="button button-primary import_demo import_demo_layout" data-demo="'.$k.'">'._x('Import Templates only','','micronet').'</a>
				</small>';
				echo '</label>';
				
			}//foreach

		wp_nonce_field('switch_demo_layouts','switch_demo_layouts');	
		echo (isset($this->field['desc']) && !empty($this->field['desc']))?'<br/><span class="description">'.$this->field['desc'].'</span>':'';

		
		echo '</fieldset>';
		echo '</div>';
		
	}//function
	
	
	
	/**
	 * Enqueue Function.
	 *
	 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
	 *
	 * @since Vibe_Options 1.0
	*/
	function enqueue(){
		
		wp_enqueue_script(
			'vibe-demo-switcher-js', 
			VIBE_OPTIONS_URL.'fields/demo_switcher/field_demo_switcher.js', 
			array('jquery'),
			time(),
			true
		);
		wp_enqueue_style(
			'vibe-demo-switcher-css', 
			VIBE_OPTIONS_URL.'fields/demo_switcher/field_demo_switcher.css'
		);
	}//function
	
}//class
?>