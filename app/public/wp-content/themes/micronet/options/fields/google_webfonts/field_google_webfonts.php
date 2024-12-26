<?php
class Vibe_Options_google_webfonts extends Vibe_Options{	
	
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
		
		echo '<select id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].']" '.$class.'rows="6" class="chzn-select" style="width:300px;">';
             
             	if(function_exists('WP_Filesystem')){
          		require_once( ABSPATH . 'wp-admin/includes/file.php' );
				WP_Filesystem();   	
             	}
		
			global $wp_filesystem;
			$fonts = $wp_filesystem->get_contents( VIBE_PATH.'/js/fonts.json' );
			$fonts = json_decode($fonts,true);
			if(!empty($fonts)){
				foreach($fonts as $font){
	  		 		echo '<option value="'.$font.'" '.selected($this->value, $font, false).'>'.$font.'</option>';
				}	
			}
			
		echo '</select>';
                echo '<div style="float:right;width:200px;border:5px dotted #EFEFEF;text-align:center;" class="font_preview" data-ref="'.$this->field['id'].'">
                       <h1>'.__('Font Preview','micronet').' </h1>
                        </div>';
		echo (isset($this->field['desc']) && !empty($this->field['desc']))?' <span class="description">'.$this->field['desc'].'</span>':'';
	
               
        }//function
        
        /**
	 * Enqueue Function.
	 *
	 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
	 *
	 * @since Vibe_Options 1.0.5
	*/
	function enqueue(){
		
		
		wp_enqueue_script(
			'vibe-opts-google-webfont-js', 
			VIBE_OPTIONS_URL.'fields/google_webfonts/google_webfonts.js', 
			array('jquery'),
			time(),
			true
		);
                
		
	}//function
	
}//class
?>