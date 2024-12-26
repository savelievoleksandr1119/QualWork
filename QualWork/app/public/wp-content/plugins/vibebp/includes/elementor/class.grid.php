<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Grid extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{

    public function get_name() {
		return 'vibebp grid';
	}

	public function get_title() {
		return __( 'VibeBP Grid', 'vibebp' );
	}

	public function get_icon() {
		return 'dashicons dashicons-grid-view';
	}

	public function get_categories() {
		return [ 'vibeapp' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Controls', 'vibebp' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		
		

		$this->add_control(
			'taxonomy',
			[
				'label' => __( 'Enter Taxonomy', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'placeholder' => __( 'Enter Taxonomy Slug', 'vibebp' ),
			]
		);


		$terms = get_terms( 'post_tag', array(
		    'hide_empty' => false,
		) );
		$termarray = array();
		foreach($terms as $term){
			$termarray[$term->slug]=$term->name;
		}
		$this->add_control(
			'term',
			[
				'label' => __('Taxonomy Term ', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'placeholder' => __( 'Enter Taxonomy Term/s', 'vibebp' ),
			]
		);

		$this->add_control(
			'post_ids',
			[
				'label' => __( 'Or Enter Specific Post Ids (comma saperated)', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'placeholder' => __( 'Enter post ids', 'vibebp' ),
			]
		);

		$v_post_types = array();
	    $post_types=get_post_types('','objects'); 

	    foreach ( $post_types as $post_type ){
	        if( !in_array($post_type->name, array('attachment','revision','nav_menu_item','sliders','modals','shop','shop_order','shop_coupon','forum','topic','reply')))
	           $v_post_types[$post_type->name]=$post_type->label;
	    }
	    

		$this->add_control(
			'post_type',
			[
				'label' => __('Select Post Type', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'post',
				'options' => $v_post_types,
			]
		);

		$this->add_control(
			'course_style',
			[
				'label' => __('Post Order', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'rated',
				'options' => array(
	                'recent' => 'Recently published',
	                'alphabetical'=> 'Alphabetical',
	                'random' => 'Random'
                ),
			]
		);

		$this->add_control(
			'grid_excerpt_length',
			[
				'label' =>__('Excerpt Length in Block (in characters)', 'vibebp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 10,
				'max' => 200,
				'step' => 5,
				'default' => 100,
			]
		);

		$this->add_control(
			'grid_width',
			[
				'label' =>__('Block width', 'vibebp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 10,
				'max' => 1600,
				'step' => 1,
				'default' => 268,
			]
		);



		$this->add_control(
			'featured_style',
			[
				'label' => __( 'Featured Style', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => plugins_url('../images/thumb_2.png',__FILE__),
				'options' => apply_filters('vibebp_grid_styles',[
					'default'=>'Default'
				])
			]
		);


		$this->add_control(
			'gutter',
			[
				'label' =>__('Spacing between Columns (in px)', 'vibebp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 200,
				'step' => 1,
				'default' => 30,
			]
		);
		
		$this->add_control(
			'column_align_verticle',
			[
				'label' => __('Adjust Vertically', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'center',
				'options' => array(
	                'start'=>_x('Start','','vibebp'),
	                'end' => _x('End','','vibebp'),
	                'center' => _x('Center','','vibebp'),
	               	'stretch' => _x('Stretch','','vibebp'),
                ),
			]
		);
		
		$this->add_control(
			'column_align_horizontal',
			[
				'label' => __('Adjust Horizontally', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'center',
				'options' => array(
	                'start'=>_x('Start','','vibebp'),
	                'end' => _x('End','','vibebp'),
	                'center' => _x('Center','','vibebp'),
	               	'stretch' => _x('Stretch','','vibebp'),
                ),
			]
		);

		$this->end_controls_section();
	}

	protected function render() {

		$settings = $this->get_settings_for_display();
		
		echo Vibebp_Shortcodes::vibebp_grid($settings);
	}

}