<?php
class Vibe_Options_seo_panel extends Vibe_Options{	
	
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
		
		$class = (isset($this->field['class']))?'class="'.$this->field['class'].'" ':'';
		
		foreach($this->field['options'] as $k => $v){
				if(vibe_check_plugin_installed($k)){
                                    echo '<div class="panel_element active">
                                        <img src="'.VIBE_URL.'/options/img/active.png" />
                                            <h4>'.$v.__(' Plugin is Inactive','micronet').'</h4></div>';
                                }else{
                                    echo '<div class="panel_element">
                                        <img src="'.VIBE_URL.'/options/img/inactive.png" />
                                            <h4>'.$v.__(' Plugin is Inactive','micronet').'</h4></div>';
                                }
				
				
			}//foreach	
	}//function
	
}//class
?>