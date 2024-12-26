<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class VibeBP_Groups_Directory extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{
	public function __construct($data = [], $args = null) {
		$this->settings =array();
		
	    if(!empty($data['settings'])){
	    	$this->settings = $data['settings'];
	    }
		
		foreach($this->get_groups_directory_scripts($this->settings) as $k => $script){
	    	$deps = array_merge($script['deps'],array('elementor-frontend'));
	    	wp_register_script($script['id'],$script['src'],$deps , VIBEBP_VERSION, true );
		}
		foreach($this->get_groups_directory_styles($this->settings) as $k => $style){
			 wp_register_style($style['id'],$style['src']);
		}
	    parent::__construct($data, $args);
		
	    
	}

	public function get_script_depends() {
		$ss = [];
		foreach($this->get_groups_directory_scripts($this->settings) as $k => $script){
	    	$ss[] = $script['id'];
		}
	    return $ss;
	}

	public function get_style_depends() {
       $ss = [];
		foreach($this->get_groups_directory_styles($this->settings) as $k => $style){
	    	$ss[] = $style['id'];
		}
	    return $ss;
   }

    public function get_name() {
		return 'groups_directory';
	}

	public function get_title() {
		return __( 'Groups Directory', 'vibebp' );
	}

	public function get_icon() {
		return 'dashicons dashicons-groups';
	}

	public function get_categories() {
		return [ 'vibebp' ];
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
			'groups_per_page',
			[
				'label' =>__('Total Number of Groups in view', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range'=>[
					'min' => 1,
					'max' => 20,
					'step' => 1,
				],
				'default' => [
					'size'=>1,
				]
			]
		);

		$this->add_control(
			'per_row',
			[
				'label' =>__('Min-width of Group', 'vibebp'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'=>[
					'px' => [
						'min' => 200,
						'max' => 760,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size'=>240,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .vibebp_groups_directory' => 'grid-template-columns: repeat(auto-fit,minmax({{SIZE}}{{UNIT}},1fr));',
				],
			]
		);
		$this->add_control(
			'order',
			[
				'label' => __( 'Default Sort by', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'active',
				'options' => array(
					'active' =>__('Active','vibebp'),
					'newest' =>__('Recently Added','vibebp'),
					'alphabetical' =>__('Alphabetical','vibebp'),
					'random'=>__('Random','vibebp'),
					'popular'=>__('Popular','vibebp'),
				)
			]
		);
		$group_types = [];
		if(function_exists('bp_groups_get_group_types')){
			$gt = bp_groups_get_group_types(array(),'objects');
			foreach($gt as $k=>$t){
				$group_types[$k]=$t->labels['name'];
			}
		}
		
		if(!empty($group_types) && count($group_types)){
			$group_types = array_merge(array('all'=>__('All','vibebp')),$group_types);
			$this->add_control(
				'group_type',
				[
					'label' => __( 'Group Type', 'vibebp' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'all',
					'options' => $group_types
				]
			);
		}
		
		$groups_settings = vibebp_get_groups_meta_fields_array();
		foreach($groups_settings as $metabox){
			if(in_array($metabox['type'],array('showhide','number','date','yesno','switch','select','checkbox','radio'))){
				$this->add_control(
					'meta__'.$metabox['id'],
					[
						'label' => sprintf(esc_html__('Show %s filter','vibebp'),$metabox['label']),
						'type' => \Elementor\Controls_Manager::CHOOSE,
						'options' => [
							'0' => [
								'title' => __( 'No', 'wplms' ),
								'icon' => 'fa fa-x',
							],
							'1' => [
								'title' => __( 'Yes', 'wplms' ),
								'icon' => 'fa fa-check',
							],
						],
					]
				);
			}
		}

		$this->add_control(
			'groups_pagination',
			[
				'label' => __( 'Show Pagination', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'vibebp' ),
				'label_off' => __( 'No', 'vibebp' ),
				'return_value' => '1',
				'default' => '0',
			]
		);
		

		$this->add_control(
			'full_avatar',
			[
				'label' => __( 'Full avatar', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'vibebp' ),
				'label_off' => __( 'No', 'vibebp' ),
				'return_value' => '1',
				'default' => '0',
			]
		);

		$this->add_control(
			'card_style',
			[
				'label' => __( 'Card Style', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => apply_filters('vibebp_group_styles',array(
					'' =>__('Default','vibebp'),
					'names' =>__('Name','vibebp'),
					'pop_names' =>__('Pop Names','vibebp'),
					'card' =>__('Card','vibebp'),
				))
			]
		);

		
		$this->add_control(
			'search_groups',
			[
				'label' => __( 'Show Search Groups', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'vibebp' ),
				'label_off' => __( 'No', 'vibebp' ),
				'return_value' => '1',
				'default' => '0',
			]
		);

		$this->add_control(
			'sort_groups',
			[
				'label' => __( 'Show Sort options', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'vibebp' ),
				'label_off' => __( 'No', 'vibebp' ),
				'return_value' => '1',
				'default' => '0',
			]
		);

		$this->add_control(
			'join_button',
			[
				'label' => __( 'Join button', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'vibebp' ),
				'label_off' => __( 'No', 'vibebp' ),
				'return_value' => 1,
				'default' => '',
			]
		);


		if(!empty($group_types) && count($group_types)){

			$this->add_control(
				'group_type_filter', [
					'label' => __( 'Show Group type Filter', 'vibebp' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'multiple'=>true,
					'options' => $group_types,
					'default' => 'all'
				]
			);
		}

		$this->add_control(
			'show_group_popup',
			[
				'label' => __( 'Show Group in popup', 'vibep' ),
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
		
		$this->end_controls_section();
	}
	//required for select2
	

	protected function render() {

		$settings = $this->get_settings_for_display();
		$defaults = array(
			'style'=>'card'
		);

		$settings = wp_parse_args($settings,$defaults);
		
		
		
		$group_types = [];
		if(function_exists('bp_groups_get_group_types')){
			$gt = bp_groups_get_group_types(array(),'objects');
			foreach($gt as $k=>$t){
				$group_types[$k]=$t->labels['name'];
			}
		}

		$group_fields = [];
		$groups_settings = vibebp_get_groups_meta_fields_array();
		foreach($groups_settings as $metabox){
			if(in_array($metabox['type'],array('showhide','number','date','yesno','switch','select','checkbox','radio')) && !empty($settings['meta__'.$metabox['id']])){
				$metabox['property'] ='meta';
				$group_fields[] = $metabox;
			}
		}
		

		if(!empty($settings['join_button'])){
			$vibebp_elementor=VibeBP_Elementor_Init::init();
			add_action('wp_footer',array($vibebp_elementor,'join_button'));
		}
		$blog_id = get_current_blog_id();
		$this->args = array(
			'api'=>array(
				'url'=>get_rest_url($blog_id,Vibe_BP_API_NAMESPACE),
				'client_id'=>vibebp_get_setting('client_id'),
			),
			'settings'=>$settings,
			'group_fields' => $group_fields,
			'group_types'=>$group_types,
			'group_sorters'=>array(
						'active' =>__('Active','vibebp'),
						'newest' =>__('Recently Added','vibebp'),
						'alphabetical' =>__('Alphabetical','vibebp'),
						'random'=>__('Random','vibebp'),
						'popular'=>__('Popular','vibebp')
					),
			'translations'=>array(
				'search_text'=>__('Type to search','vibebp'),
				'all'=>__('All','vibebp'),
				'no_groups_found'=>__('No groups found !','vibebp'),
				'show_filters'=>__('Show Filters','vibebp'),
				'close_filters'=>__('Close Filters','vibebp'),
				'clear_all'=>__('Clear All','vibebp'),
				'select_option'=>__('Select Option','vibebp'),
			)
		);
		//wp_localize_Script('vibebp-groups-directory-js','vibebpgroups',$this->args);
		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			$user_id = get_current_user_id();
		}
		
		$args = array(
			'type'		=>empty($settings['order'])?'':$settings['order'],
			'per_page'	=>empty($settings['groups_per_page'])?10:$settings['groups_per_page']['size']
		);
		if(!empty($settings['group_type']) && $settings['group_type'] != 'all'){
			$args['group_type'] = $settings['group_type'];
		}
		if(!function_exists('groups_get_groups'))
			return;
		$run = groups_get_groups($args);
    		
		if( count($run['groups']) ) {

			foreach($run['groups'] as $k=>$group){
				
				$run['groups'][$k]->avatar = bp_core_fetch_avatar(array(
                        'item_id' => $run['groups'][$k]->id,
                        'object'  => 'group',
                        'type'=> ($settings['full_avatar']?'full':'thumb'),
                        'html'    => false
                    ));
			}
		}
		?>
		<div id="vibebp_groups_directory" class="vibebp_groups_directory_wrapper">
			<div class="vibebp_groups_directory_header">
			<?php
				if($settings['search_groups']){
					?>
					<div class="vibebp_groups_search">
						<input type="text" placeholder="<?php _e('Type to search','vibebp'); ?>" />
					</div>
					<?php
				}

				if(!empty($settings['group_type_filter'])){
					?>
					<div class="vibebp_groups_filter">
						<ul>
						<?php
						$group_types = bp_groups_get_group_types();
						
						if(!is_array($settings['group_type_filter']) || in_array('all',$settings['group_type_filter'])){
							echo '<li><a class="group_type all">'.__('All','vibebp').'</a></li>';
							foreach($group_types as $type=>$label){
									echo '<li><a class="group_type '.$type.'">'.$label.'</a></li>';
								}
						}else{ 
							foreach($settings['group_type_filter'] as $type){
								echo '<li><a class="group_type '.$type.'">'.$group_types[$type].'</a></li>';
							}
						}
						?>
						</ul>
					</div>
					<?php
				}

				if($settings['sort_groups']){

					$default_sorters = array(
						'active' =>__('Active','vibebp'),
						'newest' =>__('Recently Added','vibebp'),
						'alphabetical' =>__('Alphabetical','vibebp'),
						'random'=>__('Random','vibebp'),
						'popular'=>__('Popular','vibebp')
					);
					?>
					<div class="vibebp_groups_sort">
						<select>
							<?php
							foreach($default_sorters as $key => $val){
								echo '<option value="'.$key.'">'.$val.'</option>';
							}
							?>
						</select>
					</div>
					<?php
				
				}
			?>
			</div>
			<div class="vibebp_groups_directory <?php echo $settings['style'];?>">
				<?php 
				if( $run['total'] ){
					foreach($run['groups'] as $key=>$group){
						echo '<div class="vibebp_group">';
						
						if($settings['card_style'] == 'names' || $settings['card_style'] == 'pop_names'){
							echo '<a href="'.bp_get_group_permalink($group).'"><img src="'.$group->avatar.'" /></a>';
							echo '<span>'.$group->name.'</span>';
						}else{

						}
						echo '</div>';
					}
				}
				?>
			</div>
			<?php
			if( $run['total'] > count($run['groups'])){
				if($settings['groups_pagination']){
					?>
					<div class="vibebp_groups_directory_pagination">
						<span>1</span>
						<a class="page_name">2</a>
						<?php
							$end = ceil($run['total']/count($run['groups']));
							if($end === 3){
								echo '<a class="page_name">'.$end.'</a>';
							}else if($end > 3){
								echo '<span>...</span><a class="page_name">'.$end.'</a>';
							}
						?>
					</div>
					<?php
				}
			}
			?>
		</div>
		<script>
        	
        	var vibebpgroups= <?php echo json_encode($this->args);?>;
        	document.dispatchEvent(new Event('vibebpgroups_loaded'));
        </script>
		<?php
		add_filter('vibebp_inside_pwa_scripts',function($scripts){
			$scripts['vibebpgroups']= plugins_url('../../../assets/js/groups.js',__FILE__); 
			$scripts['nouislider']= plugins_url('../../../assets/js/nouislider.min.js',__FILE__);
			//
			$scripts['wnumb']=plugins_url('../../..//assets/js/wNumb.min.js',__FILE__);
			$scripts['flatpickr']=plugins_url('../../../assets/js/flatpickr.min.js',__FILE__);
			return $scripts;
		});
        add_filter('vibebp_inside_pwa_objects',array($this,'pwa_object'));
        add_filter('vibebp_inside_pwa_styles',array($this,'pwa_styles'),10,2);
        add_filter('vibebp_inside_pwa_objects',array($this,'pwa_object'));
	}

	function pwa_styles($styles,$post_id){
		$upload_dir   = wp_upload_dir();
		if(file_exists($upload_dir['basedir'].'/elementor/css/post-'.$post_id.'.css')){
			$styles['elementor_specific_css']=$upload_dir['baseurl'].'/elementor/css/post-'.$post_id.'.css?v='.WPLMS_PLUGIN_VERSION;	
		}
		return $styles;
	}

	function pwa_object($objects){
		$objects['vibebpgroups']= $this->args; 
		return $objects;
	}

	function get_groups_directory_scripts($settings=null){
		$scripts =  array(
			array(
				'src'=>plugins_url('../../../assets/js/nouislider.min.js',__FILE__),
				'id'=>'nouislider',
				'deps'=>array(),
			),
			array(
				'src'=>plugins_url('../../../assets/js/flatpickr.min.js',__FILE__),
				'id'=>'flatpickr',
				'deps'=>array(),
			),
			array(
				'src'=>plugins_url('../../../assets/js/wNumb.min.js',__FILE__),
				'id'=>'wnumb',
				'deps'=>array(),
			),
			array(
				'src'=>plugins_url('../../../assets/js/groups.js',__FILE__),
				'id'=>'vibebp-groups-directory-js',
				'deps'=>array('wp-element','wp-data'),
			),
		);
		if(!empty($settings['show_group_popup'])){
			$scripts[] = array(
				'id'=>'singlegroup',
				'src'=>plugins_url('../../../assets/js/singlegroup.js',__FILE__),
				'deps'=>array()
			);
			
		}

		return apply_filters('vibebp_members_directory_scripts',$scripts);
	}

		

	function get_groups_directory_styles(){
		$styles =  array(
			array(
				'src'=>plugins_url('../../../assets/css/nouislider.min.css',__FILE__),
				'id'=>'nouislider_css',
				'deps'=>array(),
			),
			array(
				'src'=>plugins_url('../../../assets/vicons.css',__FILE__),
				'id'=>'vicons',
				'deps'=>array(),
			),
			array(
				'src'=>plugins_url('../../../assets/css/front.css',__FILE__),
				'id'=>'vibebp-front',
				'deps'=>array(),
			),
		);

		

		return apply_filters('vibebp_members_directory_styles',$styles);
	}
}