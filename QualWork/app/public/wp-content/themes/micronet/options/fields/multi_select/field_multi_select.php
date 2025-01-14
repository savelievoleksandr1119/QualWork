<?php
class Vibe_Options_multi_select extends Vibe_Options{	
	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since Vibe_Options 1.0
	*/
	function __construct($field = array(), $value ='', $parent=''){
		
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
		
		$class = (isset($this->field['class']))?'class="'.$this->field['class'].'"':'';
		
		echo '<select id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].'][]" class="chosen" multiple="multiple" rows="6" >';
			if(is_array($this->field['options']))
			foreach($this->field['options'] as $k => $v){
				
				$selected = (is_array($this->value) && in_array($k, $this->value))?' selected="selected"':'';
				
				echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
				
			}//foreach

		echo '</select>';

		echo (isset($this->field['desc']) && !empty($this->field['desc']))?'<br/><span class="description">'.$this->field['desc'].'</span>':'';
		
	}//function
	
}//class
?>