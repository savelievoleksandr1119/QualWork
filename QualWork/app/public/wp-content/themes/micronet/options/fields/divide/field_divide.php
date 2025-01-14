<?php
class Vibe_Options_divide extends Vibe_Options{	
	
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
		
		$class = (isset($this->field['class']))?' '.$this->field['class'].'':'';
		
		echo '<div class="hr'.$class.'" style="position: absolute;left: 0;width: 100%;">
                       '. ((isset($this->field['desc']) && !empty($this->field['desc']))?' <h3 class="description">'.$this->field['desc'].'</h3>':'').'
                      </div>';
		
	}//function
	
}//class
?>