<?php

if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly.
}

class Vibe_Carousel extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{


    public function get_name() {
		return 'vibebp-carousel';
	}

	public function get_title() {
		return __( 'Vibe Carousel', 'vibebp' );
	}

	public function get_icon() {
		return 'dashicons dashicons-editor-code';
	}

	public function get_categories() {
		return [ 'vibebp','vibebp' ];
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
			'carousel_id',
			[
				'label' => __('Unique ID', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'default'=>rand(0,9999),
				'placeholder' => __( 'Enter a unique default number for identifying this carousel', 'vibebp' ),
			]
		);

		$this->add_control(
			'carousel_style',
			[
				'label' => __('Style', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
	                '' => 'Default',
	                'sleek'=> 'Sleek'
                ),
			]
		);

	    
		$options = [			
			'post_type'=>_x('Post Type','elementor selct','vibebp'),
			'taxonomy'=>_x('Taxonomy','elementor selct','vibebp'),
			'members'=>_x('Members','elementor selct','vibebp'),
			'slides'=>_x('Slides','elementor selct','vibebp')
		];
		if(function_exists('bp_is_active') && bp_is_active('groups')){
			$options['groups']=_x('Groups','elementor selct','vibebp');
		}
		$this->add_control(
			'carousel_type',
			[
				'label' => __('Carousel Type', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'post',
				'options' => $options,
			]
		);

			
		$this->add_control(
			'post_type',
			[
				'label' => __('Enter Post Type', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => ['carousel_type' => 'post_type'],
				'placeholder' => __( 'Enter taxonomy', 'vibebp' ),
			]
		);

		$this->add_control(
			'post_type_taxonomy',
			[
				'label' => __('Enter Post Taxonomy [optional]', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => [
					'carousel_type' => 'post_type'
				],
				'placeholder' => __( 'Enter Taxonomy', 'vibebp' ),
			]
		);
		
		$this->add_control(
			'taxonomy',
			[
				'label' => __('Enter Taxonomy', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => [
					'carousel_type' => 'taxonomy'
				],
				'placeholder' => __( 'Enter Taxonomy', 'vibebp' ),
			]
		);

		$this->add_control(
			'post_type_include_terms',
			[
				'label' => __('Include Taxonomy Terms [ Optional ]', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => [
					'carousel_type' => 'post_type',
				],
				'placeholder' => __( 'Enter comma seperated Taxonomy Term slugs', 'vibebp' ),
			]
		);

		$this->add_control(
			'parent_term',
			[
				'label' => __('Taxonomy Terms of Parent', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => [
					'carousel_type' => 'taxonomy'
				],
				'placeholder' => __( 'Child terms of this Parent Term ID', 'vibebp' ),
			]
		);

		$this->add_control(
			'only_parents',
			[
				'label' =>__('Root terms only', 'vibebp'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'condition' => [
					'carousel_type' => 'taxonomy'
				],
				'options' => [
					'0' => [
						'title' => __( 'No', 'vibebp' ),
						'icon' => 'vicon vicon-close',
					],
					'1' => [
						'title' => __( 'Yes', 'vibebp' ),
						'icon' => 'vicon vicon-check',
					],
				],
			]
		);

		$this->add_control(
			'include_terms',
			[
				'label' => __('Include Taxonomy Terms', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => [
					'carousel_type' => 'taxonomy'
				],
				'placeholder' => __( 'Enter comma seperated Taxonomy Term slugs', 'vibebp' ),
			]
		);

		$this->add_control(
			'include_member_types',
			[
				'label' => __('Include Member Types', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => [
					'carousel_type' => 'members',
				],
				'placeholder' => __( 'Comma separated member types', 'vibebp' ),
			]
		);


		$this->add_control(
			'include_group_types',
			[
				'label' => __('Include Group Types', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => [
					'carousel_type' => 'groups',
				],
				'placeholder' => __( 'Comma separated group types', 'vibebp' ),
			]
		);

		$this->add_control(
			'exclude_terms',
			[
				'label' => __('Exclude Taxonomy Terms', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => [
					'carousel_type' => 'post_type',
					'carousel_type' => 'taxonomy'
				],
				'placeholder' => __( 'Comma separated Taxonomy terms slugs', 'vibebp' ),
			]
		);

		$this->add_control(
			'include_post_ids',
			[
				'label' => __('Enter Specific Post Ids', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => [
            		'carousel_type' => 'post_type',
        		],
				'placeholder' => __( 'Comma separated Post IDs', 'vibebp' ),
			]
		);

		

		$this->add_control(
			'include_group_ids',
			[
				'label' => __('Enter Specific Group Ids', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => [
            		'carousel_type' => 'groups',
        		],
				'placeholder' => __( 'Comma separated Group IDs', 'vibebp' ),
			]
		);

		$this->add_control(
			'include_member_ids',
			[
				'label' => __('Enter Specific Member Ids', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => [
            		'carousel_type' => 'members',
        		],
				'placeholder' => __( 'Comma separated Member IDs', 'vibebp' ),
			]
		);

		$this->add_control(
			'exclude_post_ids',
			[
				'label' => __('Enter Post Ids to Exclude', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => [
            		'carousel_type' => 'post_type',
        		],
				'placeholder' => __( 'Comma separated Post IDs', 'vibebp' ),
			]
		);

		
		$this->add_control(
			'exclude_group_ids',
			[
				'label' => __('Enter Group Ids to Exclude', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => [
            		'carousel_type' => 'groups',
        		],
				'placeholder' => __( 'Comma separated group IDs', 'vibebp' ),
			]
		);

		$this->add_control(
			'exclude_member_ids',
			[
				'label' => __('Enter Member Ids to Exclude', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'condition' => [
            		'carousel_type' => 'members',
        		],
				'placeholder' => __( 'Comma separated member IDs', 'vibebp' ),
			]
		);

		$this->add_control(
			'post_type_order',
			[
				'label' => __('Order', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'condition' => [
            		'carousel_type' => 'post_type',
        		],
				'default' => '',
				'options' => array(
	                '' => 'Default',
	                'alphabetical'=> 'Alphabetical'
                ),
			]
		);
		$this->add_control(
			'term_order',
			[
				'label' => __('Order', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'condition' => [
            		'carousel_type' => 'taxonomy',
        		],
				'default' => '',
				'options' => array(
	                '' => 'Default',
	                'alphabetical'=> 'Alphabetical'
                ),
			]
		);

		$this->add_control(
			'member_order',
			[
				'label' => __('Order', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'condition' => [
            		'carousel_type' => 'members',
        		],
				'default' => '',
				'options' => array(
	                '' => 'Default',
	                'alphabetical'=> 'Alphabetical'
                ),
			]
		);

		$this->add_control(
			'group_order',
			[
				'label' => __('Order', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'condition' => [
            		'carousel_type' => 'groups',
        		],
				'default' => '',
				'options' => array(
	                '' => 'Default',
	                'alphabetical'=> 'Alphabetical'
                ),
			]
		);
		
		$this->add_control(
			'post_type_featured_style',
			[
				'label' => __( 'Featured Card', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'condition' => [
            		'carousel_type' => 'post_type',
        		],
				'options' => vibebp_get_featured_blocks('post_type')
			]
		);

		$this->add_control(
			'taxonomy_featured_style',
			[
				'label' => __( 'Featured Card', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'condition' => [
            		'carousel_type' => 'taxonomy',
        		],
				'options' => vibebp_get_featured_blocks('taxonomy'),
			]
		);

		$this->add_control(
			'member_featured_style',
			[
				'label' => __( 'Featured Card', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'condition' => [
            		'carousel_type' => 'members',
        		],
				'options' => vibebp_get_featured_blocks('members'),
			]
		);

		$this->add_control(
			'group_featured_style',
			[
				'label' => __( 'Featured Card', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'condition' => [
            		'carousel_type' => 'groups',
        		],
				'options' => vibebp_get_featured_blocks('groups'),
			]
		);

		$repeater = new \Elementor\Repeater();


		$repeater->add_control(
			'slide_image',
			[
				'label' => esc_html__( 'Slide Image', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'slide_content', [
				'label' => esc_html__( 'Slide Content', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Slide Content' , 'vibebp' ),
				'show_label' => false,
			]
		);

		$this->add_control(
			'slides',
			[
				'label' => esc_html__( 'Slides', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'condition' => [
            		'carousel_type' => 'slides',
        		],
				'fields' => $repeater->get_controls(),
				'default' => [],
			]
		);

		$this->add_control(
			'slides_featured_style',
			[
				'label' => __( 'Featured Card', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'condition' => [
            		'carousel_type' => 'slides',
        		],
				'options' => vibebp_get_featured_blocks('slides'),
			]
		);

		$this->add_control(
			'show_controls',
			[
				'label' =>__('Show Direction arrows', 'vibebp'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'0' => [
						'title' => __( 'No', 'vibebp' ),
						'icon' => 'fa fa-x',
					],
					'1' => [
						'title' => __( 'Yes', 'vibebp' ),
						'icon' => 'fa fa-check',
					],
				],
			]
		);

		$this->add_control(
			'show_controlnav',
			[
				'label' =>__('Show Control dots', 'vibebp'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'0' => [
						'title' => __( 'No', 'vibebp' ),
						'icon' => 'fa fa-x',
					],
					'1' => [
						'title' => __( 'Yes', 'vibebp' ),
						'icon' => 'fa fa-check',
					],
				],
			]
		);

		$this->add_control(
			'vertical',
			[
				'label' =>__('Vertical Slide', 'vibebp'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'0' => [
						'title' => __( 'No', 'vibebp' ),
						'icon' => 'vicon vicon-close',
					],
					'1' => [
						'title' => __( 'Yes', 'vibebp' ),
						'icon' => 'vicon vicon-check',
					],
				],
			]
		);

		$this->add_control(
			'auto_slide',
			[
				'label' =>__('Auto slide/rotate', 'vibebp'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'0' => [
						'title' => __( 'No', 'vibebp' ),
						'icon' => 'vicon vicon-close',
					],
					'1' => [
						'title' => __( 'Yes', 'vibebp' ),
						'icon' => 'vicon vicon-check',
					],
				],
			]
		);

		
		$this->add_control(
			'autoheight',
			[
				'label' =>__('Disable AutoHeight', 'vibebp'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'0' => [
						'title' => __( 'No', 'vibebp' ),
						'icon' => 'vicon vicon-close',
					],
					'1' => [
						'title' => __( 'Yes', 'vibebp' ),
						'icon' => 'vicon vicon-check',
					],
				],
			]
		);

		$this->add_control(
			'scrollbar',
			[
				'label' =>__('Slide scrollbar', 'vibebp'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'0' => [
						'title' => __( 'No', 'vibebp' ),
						'icon' => 'vicon vicon-close',
					],
					'1' => [
						'title' => __( 'Yes', 'vibebp' ),
						'icon' => 'vicon vicon-check',
					],
				],
			]
		);

		$this->add_control(
			'loop',
			[
				'label' =>__('Slide Loop', 'vibebp'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'0' => [
						'title' => __( 'No', 'vibebp' ),
						'icon' => 'vicon vicon-close',
					],
					'1' => [
						'title' => __( 'Yes', 'vibebp' ),
						'icon' => 'vicon vicon-check',
					],
				],
			]
		);

		$this->add_control(
			'carousel_columns',
			[
				'label' =>__('Number of columns in carousel', 'vibebp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 12,
				'step' => 1,
				'default' => 4,
			]
		);

		$this->add_control(
			'carousel_space',
			[
				'label' =>__('Space between columns in carousel', 'vibebp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 100,
				'step' => 10,
				'default' => 40,
			]
		);

		$this->add_control(
			'rows',
			[
				'label' =>__('Rows', 'vibebp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 10,
				'step' => 1,
				'default' => 1,
			]
		);

		$this->add_control(
			'effect',
			[
				'label' => __('Slide Effect', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
	                'slide' => 'Slide',
	                'fade'=> 'Fade',
	                'cube'=> 'Cube',
	                'coverflow'=> 'Coverflow',
	                'flip'=> 'Flip',
	                'creative'=> 'Creative',
	                'cards'=> 'Cards',
                ),
			]
		);

		$this->add_control(
			'carousel_move',
			[
				'label' =>__('Move blocks in one slide', 'vibebp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 12,
				'step' => 1,
				'default' => 1,
			]
		);

		$this->add_control(
			'starting_slide',
			[
				'label' =>__('Starting slide', 'vibebp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 12,
				'step' => 1,
				'default' => 1,
			]
		);

		

		$this->add_control(
			'carousel_number',
			[
				'label' =>__('Total Number of Blocks', 'vibebp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 99,
				'step' => 1,
				'default' => 6,
			]
		);

		$this->add_control(
			'extras',
			[
				'label' =>__('extras', 'vibebp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text'
			]
		);

		$this->end_controls_section();

	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$args = [];

		$flag=0;
		if(empty($settings['carousel_type'])){
			$settings['carousel_type']='';
		}

		switch($settings['carousel_type']){
			case 'members':
				$featured_style = $settings['member_featured_style'];
				$args = [
					'type'                => 'active',
					'per_page'            => (empty($settings['carousel_number'])?10:$settings['carousel_number']),
					'page'                => 1, 
					'populate_xtras'     => true,
				];
				if(!empty($settings['include_member_types'])){
					$args['member_type__in']=explode(',',$settings['include_member_types']);
				}
				if(!empty($settings['exclude_member_types'])){
					$args['member_type__not_in']=explode(',',$settings['exclude_member_types']);
				}
				if(!empty($settings['include_member_ids'])){
					$args['include']=explode(',',$settings['include_member_ids']);
				}
				if(!empty($settings['exclude_member_ids'])){
					$args['exclude']=explode(',',$settings['exclude_member_ids']);
				}

				if(!empty($settings['member_order'])){
					$args['type'] = $settings['member_order'];
				}

			break;
			case 'groups':
				$args = array(
					'order'              => 'DESC',         // 'ASC' or 'DESC'
					'orderby'            => 'date_created', 
					'per_page'           => empty($setings['carousel_number'])?10:$setings['carousel_number'],
					'page'               => 1,  
					'fields'             => 'all',
				);
				$featured_style = $settings['group_featured_style'];
				if(!empty($settings['include_group_types'])){
					$args['group_type__in']=explode(',',$settings['include_group_types']);
				}
				if(!empty($settings['exclude_group_types'])){
					$args['group_type__not_in']=explode(',',$settings['exclude_group_types']);
				}
				if(!empty($settings['include_group_ids'])){
					$args['include']=explode(',',$settings['include_group_ids']);
				}
				if(!empty($settings['exclude_group_ids'])){
					$args['exclude']=explode(',',$settings['exclude_group_ids']);
				}

				if(!empty($settings['group_order'])){
					$args['type'] = $settings['group_order'];
				}

			break;
			case 'taxonomy':

				if(empty($settings['taxonomy'])){
					$flag =1;
					
				}else{
					$featured_style = $settings['taxonomy_featured_style'];
					$args=[
						'taxonomy' => $settings['taxonomy'],
    					'hide_empty' => false,
    					'number'=>empty($setings['carousel_number'])?10:$setings['carousel_number'],
					];

					if(!empty($settings['only_parents'])){
						$args['parent']=0;
					}

					if(!empty($settings['parent_term']) && is_numeric($settings['parent_term'])){
						$args['parent']=$settings['parent_term'];
					}

					if(!empty($settings['include_terms'])){
						$args['slug']=explode(',',$settings['include_terms']);
					}
					if(!empty($settings['exclude_terms'])){
						$args['exclude']=explode(',',$settings['exclude_terms']);;
					}

					$featured_style = $settings['taxonomy_featured_style'];
					
					if(!empty($settings['group_order'])){
						$args['type'] = $settings['group_order'];
					}
				}
			break;
			case 'post_type':
				if(empty($settings['post_type'])){
					$flag =1;
				}else{
					$featured_style = $settings['post_type_featured_style'];
					$args = [
						'post_type'=>$settings['post_type'],
						'posts_per_page'=>empty($setings['carousel_number'])?10:$settings['post_type'],
					];

					if(!empty($settings['include_post_ids'])){
						$args['post__in']=explode(',',$settings['include_post_ids']);
					}

					if(!empty($settings['exclude_post_ids'])){
						$args['post_not__in']=explode(',',$settings['include_post_ids']);
					}

					$tax_query = [];
					if(!empty($settings['post_type_include_terms'])){
						$tax_query['relation']='AND';
						$tax_query[]=[
								'taxonomy'=>$settings['post_type_taxonomy'],
								'field'=>'slug',
								'terms'=>explode(',',$settings['include_terms'])
							];
					}
					if(!empty($settings['exclude_terms'])){
						$tax_query['relation']='AND';
						$tax_query[]=[
								'taxonomy'=>$settings['post_type_taxonomy'],
								'field'=>'slug',
								'terms'=>explode(',',$settings['exclude_terms'])
							];
					}

					if(!empty($tax_query)){
						$args['tax_query'] = $tax_query;	
					}
					
				}
				
			break;
			case 'slides':
				$featured_style = $settings['slides_featured_style'];
				$flag=0;
			break;
		}

		
		if(empty($flag)){
;
			$shortcode = "[vibebp_carousel 
			id='".$settings['carousel_id']."'
			carousel_style='".(empty($settings['carousel_style'])?'default':$settings['carousel_style'])."'
			autoheight='".(empty($settings['autoheight'])?'':$settings['autoheight'])."'  
		    show_controls='".(empty($settings['show_controls'])?'':$settings['show_controls'])."'  
		    effect='".(empty($settings['effect'])?'1':$settings['effect'])."' 
		    rows='".(empty($settings['rows'])?'1':$settings['rows'])."'
		    show_controlnav='".(empty($settings['show_controlnav'])?'':$settings['show_controlnav'])."'  
		    scrollbar='".(empty($settings['scrollbar'])?'':$settings['scrollbar'])."' 
		    type='".$settings['carousel_type']."'  
		    args='".(empty($args)?'':base64_encode(json_encode($args)))."' 
		    featured_style='".$featured_style."' 
		    space='".$settings['carousel_space']."'
		    columns='".$settings['carousel_columns']."' 
		    vertical='".(empty($settings['vertical'])?'':$settings['vertical'])."' 
		    loop='".(empty($settings['loop'])?'':$settings['loop'])."' 
		    show_controlnav='".(empty($settings['show_controlnav'])?'':$settings['show_controlnav'])."' 
		  	show_controls='".(empty($settings['show_controls'])?'':$settings['show_controls'])."' 
		  	auto_slide='".(empty($settings['auto_slide'])?'':$settings['auto_slide'])."' 
		    starting_slide='".$settings['starting_slide']."' 
		    auto_slide='".(isset($settings['auto_slide'])?$settings['auto_slide']:'')."'  
		    slides='".(empty($settings['slides'])?'':base64_encode(json_encode($settings['slides'])))."' 
		    extras='".(empty($settings['extras'])?'':$settings['extras'])."'  
		    /]";
		   
			echo do_shortcode($shortcode);
		}else{

			echo '<div class="message error">'.__('Incorrect configuration','tutorly').'</div>';
		}
	}

}


