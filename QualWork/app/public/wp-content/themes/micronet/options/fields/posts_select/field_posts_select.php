<?php
class Vibe_Options_posts_select extends Vibe_Options{	
	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since Vibe_Options 1.0.1
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
	 * @since Vibe_Options 1.0.1
	*/
	function render(){
		
		$class = (isset($this->field['class']))?'class="'.$this->field['class'].'" ':'';
		
		echo '<select id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].']" '.$class.' >';
		
		$args = wp_parse_args($this->field['args'], array('numberposts' => '-1'));
		//print_r($this->field);
		if(isset($this->field['post_type'])){
			$args['post_type'] = $this->field['post_type'];
		}
		$posts = get_posts($args); 
		foreach ( $posts as $post ) {
			echo '<option value="'.$post->ID.'"'.selected($this->value, $post->ID, false).'>'.$post->post_title.'</option>';
		}

		echo '</select>';

		echo (isset($this->field['desc']) && !empty($this->field['desc']))?' <span class="description">'.$this->field['desc'].'</span>':'';
		
	}//function
	
}//class
?>