<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



 class VibeBP_Members_Directory extends \Elementor\Widget_Base {

	public function __construct($data = [], $args = null) {
		$this->settings =array();
		
	    if(!empty($data['settings'])){
	    	$this->settings = $data['settings'];
	    }
    	foreach($this->get_members_directory_scripts($this->settings) as $k => $script){
	    	$deps = array_merge($script['deps'],array('elementor-frontend'));
	    	wp_register_script($script['id'],$script['src'],$deps , VIBEBP_VERSION, true );
		}
		foreach($this->get_members_directory_styles($this->settings) as $k => $style){
			 wp_register_style($style['id'],$style['src']);
		}
	    parent::__construct($data, $args);
	}

	public function get_script_depends() {
		$ss = [];
		foreach($this->get_members_directory_scripts($this->settings) as $k => $script){
	    	$ss[] = $script['id'];
		}
	    return $ss;
	}

	public function get_style_depends() {
       $ss = [];
		foreach($this->get_members_directory_styles($this->settings) as $k => $style){
	    	$ss[] = $style['id'];
		}
	    return $ss;
   }

    public function get_name() {
		return 'members_directory';
	}

	public function get_title() {
		return __( 'Members Directory', 'vibebp' );
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
			'members_per_page',
			[
				'label' =>__('Total Number of Members in view', 'vibebp'),
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
				'label' =>__('Min-width of Member', 'vibebp'),
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
					'{{WRAPPER}} .vibebp_members_directory' => 'grid-template-columns: repeat(auto-fit,minmax({{SIZE}}{{UNIT}},1fr));',
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
		$member_type_objects = bp_get_member_types(array(),'objects');
		if(!empty($member_type_objects) && count($member_type_objects)){
			$member_types=array();
			foreach($member_type_objects as $member_type=>$mt){
				$member_types[$member_type]=$member_type_objects[ $member_type ]->labels['singular_name'];
			}
			$member_types = array_merge(array('all'=>__('All','vibebp')),$member_types);
			$this->add_control(
				'member_type',
				[
					'label' => __( 'Member Type', 'vibebp' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'all',
					'options' => $member_types
				]
			);
		}
		$this->add_control(
			'members_pagination',
			[
				'label' => __( 'Show Pagination', 'vibebp' ),
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
		if(vibebp_get_setting('google_maps_api_key')){
			$this->add_control(
				'show_map',
				[
					'label' => __( 'Show Map & Filters', 'vibebp' ),
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
		}
		

		global $wpdb,$bp;
		$results = $wpdb->get_results("SELECT field.id as id, field.name as name FROM {$bp->profile->table_name_fields} as field INNER JOIN {$bp->profile->table_name_meta} as meta ON field.id = meta.object_id
			WHERE meta.object_type = 'field' AND meta.meta_key = 'do_autolink' AND meta.meta_value = 'on'");
		if(!empty($results)){
			$options = array();
			foreach($results as $result){
				$options[$result->id] = $result->name;
			}
			$this->add_control(
				'member_directory_filters',
				[
					'label' => __( 'Select Filters', 'vibebp' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'multiple'=>true,
					'options' => $options
				]
			);
		}
	

		$this->add_control(
			'full_avatar',
			[
				'label' => __( 'Full avatar', 'vibebp' ),
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
			'card_style',
			[
				'label' => __( 'Card Style', 'vibebp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => apply_filters('vibebp_card_style',array(
					'' =>__('Default','vibebp'),
					'names' =>__('Name','vibebp'),
					'pop_names' =>__('Pop Names','vibebp'),
					'card' =>__('Card','vibebp'),
				))
			]
		);

		$this->add_control(
			'search_members', [
				'label' => __( 'Show Search', 'vibebp' ),
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
			'sort_members', [
				'label' => __( 'Show Sort options', 'vibebp' ),
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


		if(!empty($member_types) && count($member_types)){

			$this->add_control(
				'member_type_filter', [
					'label' => __( 'Show Member type Filter', 'vibebp' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'multiple'=>true,
					'options' => $member_types,
					'default' => 'all'
				]
			);
		}

		$this->add_control(
			'show_member_popup',
			[
				'label' => __( 'Show member in popup', 'wplms' ),
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



	protected function render() {

		$settings = $this->get_settings_for_display();
 		
 		$this->settings = $settings;

 		

		//wp_enqueue_script('vibebp-members-actions',plugins_url('../../../assets/js/actions.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION,true);

		$blog_id = '';
        if(function_exists('get_current_blog_id')){
            $blog_id = get_current_blog_id();
        }

		//wp_localize_script('vibebp-members-actions','vibebpactions',);
		$member_directory_filters = [];
		if(!empty($settings['member_directory_filters'])){
			global $wpdb,$bp;
			$results = $wpdb->get_results("SELECT id,name,type FROM {$bp->profile->table_name_fields} WHERE id IN (".implode(',',$settings['member_directory_filters']).")");
			foreach($results as $field){
				$member_directory_filters[]=array('field_id'=>$field->id,'name'=>$field->name,'type'=>$field->type);

				if($field->type == 'datebox'){
					wp_enqueue_script('flatpickr');
				}
			}
			$settings['member_directory_filters']=apply_filters('vibebp_member_directory_filters',$member_directory_filters);
		}

		$member_type_objects = bp_get_member_types(array(),'objects');
		$member_types=array();
		if(!empty($member_type_objects) && count($member_type_objects)){
			foreach($member_type_objects as $member_type=>$mt){
				$member_types[$member_type]=$mt->labels['singular_name'];
			}
		}


		if($settings['card_style'] == 'card'){
			global $wpdb;
			$ids = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_type='member-card' AND post_status='publish'");
			
			if(!empty($ids)){
				$upload_dir   = wp_upload_dir();
				foreach($ids as $id){
					if(file_exists($upload_dir['basedir'].'/elementor/css/post-'.$id->ID.'.css')){
						wp_enqueue_style('vibebp-member-card-'.$id->ID,$upload_dir['baseurl'].'/elementor/css/post-'.$id->ID.'.css',array());	
					}
				}
			}

			//wp_enqueue_script('vibebp-members-actions',plugins_url('../../../assets/js/actions.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION,true);
		}

		
		
		$blog_id = '';
        if(function_exists('get_current_blog_id')){
            $blog_id = get_current_blog_id();
        }

        
        
		$this->args = array(
			'api'=>array(
				'url'=>get_rest_url($blog_id,Vibe_BP_API_NAMESPACE),
				'client_id'=>vibebp_get_setting('client_id'),
				'xprofile'=>Vibe_BP_API_XPROFILE_TYPE,
				'google_maps_api_key'=>vibebp_get_setting('google_maps_api_key','general','misc'),
				'map_marker'=>plugins_url('../../../assets/images/marker.png',__FILE__)
			),
			'settings'=>$settings,
			'member_types'=>$member_types,
			'member_sorters'=>array(
						'active' =>__('Active','vibebp'),
						'newest' =>__('Recently Added','vibebp'),
						'alphabetical' =>__('Alphabetical','vibebp'),
						'random'=>__('Random','vibebp'),
						'popular'=>__('Popular','vibebp')
					),
			'translations'=>array(
				'search_text'=>__('Type to search','vibebp'),
				'all'=>__('All','vibebp'),
				'no_members_found'=>__('No members found !','vibebp'),
				'member_types'=>__('Member Type','vibebp'),
				'map_search'=>__('Map Search','vibebp'),
				'show_filters'=>__('Show Filters','vibebp'),
				'close_filters'=>__('Close Filters','vibebp'),
				'clear_all'=>__('Clear All','vibebp'),
			)
		);



		//wp_localize_script('vibebp-members-directory-js','vibebpmembers',$this->args);
		
		$args = array(
			'type'		=>$settings['order'],
			'per_page'	=>$settings['members_per_page']['size']
		);
		if(!empty($settings['member_type']) && $settings['member_type'] != 'all'){
			$args['member_type'] = $settings['member_type'];
		}

		$run = bp_core_get_users($args);
    		
		if( count($run['users']) ) {

			foreach($run['users'] as $k=>$user){
				
				$run['users'][$k]->avatar = bp_core_fetch_avatar(array(
                        'item_id' => $run['users'][$k]->id,
                        'object'  => 'user',
                        'type'=> ($settings['full_avatar']?'full':'thumb'),
                        'html'    => false
                    ));
				$run['users'][$k]->url = bp_core_get_user_domain($run['users'][$k]->id);
			}
		}
		$action_data = apply_filters('vibebpactions_translations',array(
			'api_url'=>apply_filters('vibebp_rest_api',get_rest_url($blog_id,Vibe_BP_API_NAMESPACE)),
			'friends'=>bp_is_active('friends')?1:0,
			'followers'=>vibebp_get_setting('followers','bp','general')?1:0,
			'translations'=>array(
				'message_text'=>__('Type message','vibebp'),
				'message_subject'=>__('Message subject','vibebp'),
				'cancel'=>__('Cancel','vibebp'),
				'offline'=>__('Offline','vibebp'),
			)
		));
		?>
		
		<style>.member_card .vibebp-directory-content-block { background-color: #fff; width: 300px; border-radius: 3px; } .member_card .vibebp-directory-content-block .loader-wrapper { height: 300px; padding: 12px; } .member_card .vibebp-directory-content-block .loader-animation { background: #f5f6f7; height: 107px; width:300px; overflow: hidden; position: relative; } .member_card .vibebp-directory-content-block .loader-animation svg { position: absolute; fill:#fff; top:0; left:-1px; width:442px; height:107px; } .member_card .vibebp-directory-content-block .loader-animation::before { background-color: #f5f6f7; background-image: url(<?php echo plugins_url('../../../assets/images/loading.gif',__FILE__);?>); background-repeat: repeat-y; background-size: 100% 1px; content: ' '; display: block; height: 100%; }</style>
		<div id="vibebp_members_directory" <?php  echo (!empty($settings['show_map'])?'class="with_map"':'')?>">
			<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
			<div class="vibebp_members_directory_wrapper opacity_0 <?php  echo (!empty($settings['horizontal_filters']))?'horizontal_filters':''?>">
			<?php
				if(!empty($settings['member_directory_filters'])){
					
					?>
					<div class="vibebp_member_directory_filters">
						<?php
							if($settings['search_members'] && empty($settings['member_directory_filters'])){
							?>
							<div class="vibebp_members_search">
								<input type="text" placeholder="<?php _e('Type to search','vibebp'); ?>" />
							</div>
							<?php
						}
						

						foreach($settings['member_directory_filters'] as $field){
							echo '<div class="vibebp_member_directory_filter">
							<span>'.$field['name'].'</span></div>';
						}
						?>
					</div>
					<?php
				}
			?>
			<div class="vibebp_members_directory_main">
				<div class="vibebp_members_directory_header">
				<?php
					if($settings['search_members'] && empty($settings['member_directory_filters'])){
						?>
						<div class="vibebp_members_search">
							<input type="text" placeholder="<?php _e('Type to search','vibebp'); ?>" />
						</div>
						<?php
					}

					if(!empty($settings['member_type_filter'])){
						?>
						<div class="vibebp_members_filter">
							<ul>
							<?php
					
							$member_types = bp_get_member_types();
							
							if(empty($settings['member_type_filter']) || (is_array($settings['member_type_filter']) && in_array('all',$settings['member_type_filter'])) || $settings['member_type_filter'] === 'all'){
								echo '<li><a class="member_type all">'.__('All','vibebp').'</a></li>';
								foreach($member_types as $type=>$label){
										echo '<li><a class="member_type '.$type.'">'.$label.'</a></li>';
									}
							}else{ 

								if(!empty($settings['member_type_filter']) && is_array($settings['member_type_filter'])){

									foreach($settings['member_type_filter'] as $type){
										echo '<li><a class="member_type '.$type.'">'.$member_types[$type].'</a></li>';
									}
								}
							}
							?>
							</ul>
						</div>
						<?php
					}

					if($settings['sort_members']){

						$default_sorters = array(
							'active' =>__('Active','vibebp'),
							'newest' =>__('Recently Added','vibebp'),
							'alphabetical' =>__('Alphabetical','vibebp'),
							'random'=>__('Random','vibebp'),
							'popular'=>__('Popular','vibebp')
						);
						?>
						<div class="vibebp_members_sort">
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
				<div class="vibebp_members_directory <?php echo (!empty( $settings['style'])? $settings['style']:'');?>" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(<?php echo $settings['per_row']['size']; ?>px,1fr))">
					<?php 
					if( $run['total'] ){
						foreach($run['users'] as $key=>$member){
							if(!empty($member) && !empty($member->name)){
								echo '<div class="vibebp_member">';
								echo '<a href="'.bp_core_get_user_domain($member->id).'"><img src="'.$member->avatar.'" /></a>';
								if($settings['card_style'] == 'names' || $settings['card_style'] == 'pop_names'){
									echo '<span>'.$member->name.'</span>';
								}
								echo '</div>';
							}
							
						}
					}
					?>
				</div>
				<?php
				$count = count($run['users']);
				if(!empty($count) && $run['total'] > count($run['users'])){
					if($settings['members_pagination']){
						?>
						<div class="vibebp_members_directory_pagination">
							<span>1</span>
							<a class="page_name">2</a>
							<?php
								$end = ceil($run['total']/count($run['users']));
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
		</div>
		</div>
		<script>
        	
        	var vibebpactions= <?php echo json_encode($action_data);?>;
        	var vibebpmembers= <?php echo json_encode($this->args);?>;
        	document.dispatchEvent(new Event('vibebpmembers_loaded'));
        </script>
		<?php

		add_filter('vibebp_inside_pwa_scripts',function($scripts){
			$scripts['vibebpmembers']= plugins_url('../../../assets/js/members.js',__FILE__); return $scripts;});

		add_filter('vibebp_inside_pwa_styles',array($this,'pwa_styles'),10,2);
        add_filter('vibebp_inside_pwa_objects',array($this,'pwa_object'));
	}

	function pwa_styles($styles,$post_id){
		$upload_dir   = wp_upload_dir();
		if(file_exists($upload_dir['basedir'].'/elementor/css/post-'.$post_id.'.css')){
			$styles['elementor_specific_css']=$upload_dir['baseurl'].'/elementor/css/post-'.$post_id.'.css?v='.WPLMS_PLUGIN_VERSION;	
		}
		if($this->settings['card_style'] == 'card'){
			global $wpdb;
			$ids = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_type='member-card' AND post_status='publish'");
			
			if(!empty($ids)){
				$upload_dir   = wp_upload_dir();
				foreach($ids as $id){
					if(file_exists($upload_dir['basedir'].'/elementor/css/post-'.$id->ID.'.css')){
						$styles['vibebp-member-card-'.$id->ID]=$upload_dir['baseurl'].'/elementor/css/post-'.$id->ID.'.css?v='.WPLMS_PLUGIN_VERSION;	
					}
				}
			}
		}

		return $styles;
	}

	function pwa_object($objects){
		$objects['vibebpmembers']= $this->args; 
		return $objects;
	}

	function get_members_directory_scripts($settings=null){
		$scripts =  array(
			array(
				'src'=>null,
				'id'=>'vibebp-members-directory-js',
				'deps'=>array(),
			),
		);

		if(!empty($settings['show_member_popup'])){
			$scripts[] = array(
				'id'=>'singleprofile',
				'src'=>plugins_url('../../../assets/js/singleprofile.js',__FILE__),
				'deps'=>array()
			);
			
		}

		if(!empty($settings['card_style']) && $settings['card_style'] == 'card'){
			
			$scripts[] = array(
				'id'=>'vibebp-members-actions',
				'src'=>plugins_url('../../../assets/js/actions.js',__FILE__),
				'deps'=>array('wp-element','wp-data')
			);
		}

		return apply_filters('vibebp_groups_directory_scripts',$scripts);
	}

		

	function get_members_directory_styles(){
	
		$styles =  array(
			array(
				'src'=>null,
				'id'=>'vicons',
				'deps'=>array(),
			),
			array(
				'src'=>null,
				'id'=>'vibebp-front',
				'deps'=>array(),
			),
		);

		

		return apply_filters('vibebp_groups_directory_styles',$styles);
	}
}