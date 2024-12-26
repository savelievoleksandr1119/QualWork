<?php
class Vibe_Options_google_webfonts_multi_select extends Vibe_Options{	
	
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
		if(empty($this->field)){$this->field=array();}
		$this->field['fonts'] = array();
		
		
		
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
		
		
        if(!function_exists('WP_Filesystem')){
	        require_once( ABSPATH . 'wp-admin/includes/file.php' );  
      	}
	      WP_Filesystem();
	      global $wp_filesystem;
	      $fonts = $wp_filesystem->get_contents(MICRONET_PATH.'/js/fonts.json');
        $fonts=  json_decode($fonts);	
                    
                   
		echo '<select id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].'][]" '.$class.'rows="6" class="chzn-select" multiple="multiple" style="width:300px;" data-placeholder="Type to search...">';
		if(is_array($fonts->items)){
		foreach($fonts->items as $font){
			if(isset($font->variants)){
				foreach($font->variants as $variant){
					if(isset($font->subsets)){
						foreach($font->subsets as $subset){
							$value = $font->family.'-'.$variant.'-'.$subset;
							$selected = (is_array($this->value) && in_array($value, $this->value))?' selected="selected"':'';
	                		echo '<option value="'.$value.'" '.$selected.'>'.$value.'</option>';
						}
					}
				}
			}
			}
		}
		echo '</select>';
                  echo  '<span class="right-description"><i class="icon-refresh"></i><a id="reset-google-fonts" class="reset">Refresh Google Webfont List</a> <small> * Updates the font list with latest available Google fonts. Reload after font refresh.</small></span>';
	
               
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
			'vibe-opts-field-google-webfonts-multi-select-js', 
			VIBE_OPTIONS_URL.'fields/google_webfonts_multi_select/google_webfonts_multi_select.js', 
			array('jquery'),
			time(),
			true
		);
		wp_localize_script('vibe-opts-field-google-webfonts-multi-select-js', 'google_webfonts_check',[
			'wpnonce'=>wp_create_nonce('google_webfonts')
		]);
		
	}//function
	
}//class



