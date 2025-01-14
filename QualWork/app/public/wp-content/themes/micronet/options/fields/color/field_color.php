<?php
class Vibe_Options_color extends Vibe_Options{	
	
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
		
		$class = (isset($this->field['class']))?$this->field['class']:'';
		if(!isset($this->value) || !$this->value){
                    $this->value=$this->field['std'];
                }
                $needle=strtoupper($this->field['id']);
                $theme_colors=array('BODYBG','COLOR','DARKBG','DARKERBG','DARKESTBG','DARKCOLOR','DARKBORDER','DARKTITLECOLOR','PRIMARYCOLOR','PRIMARYLIGHTCOLOR','PRIMARYBORDER','LIGHTBG','LIGHTERBG','LIGHTESTBG','LIGHTCOLOR','LIGHTBORDER','LIGHTERBORDER','LIGHTERCOLOR','LIGHTESTCOLOR','LIGHTERBORDER','SPECIALBG','SPECIALLIGHTBG','SPECIALBORDER','SPECIALCOLOR');
                if( in_array($needle,$theme_colors))
                {
                    global $vibe_options;
                    if(isset($vibe_options['custom_theme'])){
                    if(!$vibe_options['custom_theme']){
                            $this->value=reset_colors($needle);
                    }
                    }
                }
		echo '<div class="farb-popup-wrapper">';
		
		echo '<input type="text" id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].']" value="'.$this->value.'" class="'.$class.' popup-colorpicker" style="width:70px;"/>';
		echo '<div class="farb-popup"><div class="farb-popup-inside"><div id="'.$this->field['id'].'picker" class="color-picker"></div></div></div>';
		
		echo (isset($this->field['desc']) && !empty($this->field['desc']))?' <span class="description">'.$this->field['desc'].'</span>':'';
		
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
			'vibe-opts-field-color-js', 
			VIBE_OPTIONS_URL.'fields/color/field_color.js', 
			array('jquery', 'farbtastic'),
			time(),
			true
		);
		
	}//function
	
}//class
?>