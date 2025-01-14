<?php
class Vibe_Options_checkbox_hide_below extends Vibe_Options{	
	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since Vibe_Options 1.0.1
	*/
	function __construct($field = array(), $value ='', $parent=''=''){
		
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
	 * @since Vibe_Options 1.0.1
	*/
	function render(){
		
		$class = (isset($this->field['class']))?$this->field['class']:'';
		
		 if($this->field['desc'] != ''){
		 	echo ' <label for="'.$this->field['id'].'">';
		 }
		
		echo '<input type="checkbox" id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].']" value="1" class="'.$class.' vibe-opts-checkbox-hide-below" '.checked($this->value, '1', false).' />';
		
		if($this->field['desc'] != ''){
			echo esc_html($this->field['desc']).'</label>';
		}
		
	}//function
	
	
	/**
	 * Enqueue Function.
	 *
	 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
	 *
	 * @since Vibe_Options 1.0.1
	*/
	function enqueue(){
		
		wp_enqueue_script(
			'vibe-opts-checkbox-hide-below-js', 
			VIBE_OPTIONS_URL.'fields/checkbox_hide_below/field_checkbox_hide_below.js', 
			array('jquery'),
			time(),
			true
		);
		
	}//function
	
}//class
?>