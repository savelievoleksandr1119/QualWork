<?php
/**
 * Register blocks.
 *
 * @package VibeBP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load registration for our blocks.
 *
 * @since 1.6.0
 */
class VibeBP_Register_Blocks {


	/**
	 * This plugin's instangce.
	 *
	 * @var VibeBP_Register_Blocks
	 */
	private static $instance;
	public $counter;
	/**
	 * Registers the plugin.
	 */
	public static function register() {
		if ( null === self::$instance ) {
			self::$instance = new VibeBP_Register_Blocks();
		}
	}

	/**
	 * The Plugin version.
	 *
	 * @var string $_slug
	 */
	private $_slug;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->counter = 0;
		$this->_slug = 'vibebp';

		add_action( 'init', array($this,'register_blocks'));
		add_filter( 'block_categories_all', array($this,'block_category'), 1, 2);


		// Hook: Frontend assets.
		add_action( 'enqueue_block_assets', array($this,'block_assets'),9 );

		add_action( 'enqueue_block_editor_assets', array($this,'editor_assets' ));
	}

	/**
	 * Add actions to enqueue assets.
	 *
	 * @access public
	 */
	

	function get_profiile_fields_array(){
		$groups = bp_xprofile_get_groups( array(
			'fetch_fields' => true
		) );
 		$options_array = array(0=>array('value'=>0,'label'=>_x('Select field','','vibebp')));
 		if(!empty($groups) && is_array($groups)){
 			foreach($groups as $group){
			
				if ( !empty( $group->fields ) ) {
					//CHECK IF FIELDS ENABLED
					foreach ( $group->fields as $field ) {
						if(is_object($field)){


							$field = xprofile_get_field( $field->id );

							$user_id = get_current_user_id();
							
							$data = xprofile_get_field_data( $field->id, $user_id);
							$data = vibebp_process_profile_field_data($data,$field,$user_id);

							$options_array[] = array(
								'value'=>$field->id,
								'label'=>$field->name,
								'admin_value'=>$data
							);
						}
						
					} // end for
					
				}
				
			}
 		}

 		return apply_filters('vibebp_get_profile_fields_array',$options_array);
	}

	function block_assets(){


		
    }



	function editor_assets() {

		/*wp_enqueue_script(
			'vibe-shortcodes-gutenblocks-js', // Handle.
			plugins_url( 'blocks.build.js', __FILE__ ), 
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ), VIBE_SHORTCODES_VERSION,
			true 
		);*/

		$color_options = apply_filters('vibe_bp_gutenberg_text_color_options',array(

			array(
				'name'=>_x('Black','','vibebp'),
				'color'=>'#000000'
			),
			array(
				'name'=>_x('White','','vibebp'),
				'color'=>'#ffffff'
			),

		));

		$fontsizeunit_options = apply_filters('vibe_bp_gutenberg_text_size_options',array(
			'px','em','rem','pt','vh','vw','%'
		));
		$groups_data_options = array(
					'group_status' =>__('Group Status','vibebp'),
					'last_active' =>__('Last Active','vibebp'),
					'create_date' =>__('Creation Date','vibebp'),
					'last_status_update' =>__('Last Status update','vibebp'),
					'moderator_count' =>__('Moderator Count','vibebp'),
					'admin_count' =>__('Administrator Count','vibebp'),
					'member_count' =>__('Member Count','vibebp'),
					'join_button' =>__('Join/Leave Button','vibebp'),
				);
		$profile_field_styles = apply_filters('vibebp_profile_field_styles',array(
			array(
				'label' => _x('Default','field display style','vibebp'),
				'value' => ''
			),
			array(
				'label' => _x('Spaced','field display style','vibebp'),
				'value' => 'spaced'
			),
			array(
				'label' => _x('Stacked','field display style','vibebp'),
				'value' => 'stacked'
			),
			array(
				'label' => _x('No Label','field display style','vibebp'),
				'value' => 'nolabel'
			),
			array(
				'label' => _x('Icons','field display style','vibebp'),
				'value' => 'icon_style'
			),
		));
		$avatar_style_options = apply_filters('vibebp_avatar_style_options',array(
			array(
				'label' => _x('Default','','vibebp'),
				'value' => ''
			),
			array(
				'label' => _x('Perspective','','vibebp'),
				'value' => 'image_perspective'
			),
			array(
				'label' => _x('Shadow','','vibebp'),
				'value' => 'image_shadow'
			),
			
		));

		$member_data_options = apply_filters('vibebp_member_profile_data',array(
					'member_type' =>__('Member Type','vibebp'),
					'last_active' =>__('Last Active','vibebp'),
					'time_in_site' =>__('Register Time','vibebp'),
					'last_status_update' =>__('Last Status update','vibebp'),
				));

		if(bp_is_active('friends')){
			$member_data_options['count_friends'] =__('Friends Count','vibebp');
		}
		if(bp_is_active('groups')){
			$member_data_options['count_groups'] = __('Group Count','vibebp');
		}
		if(vibebp_get_setting('bp_followers','bp','general')){
			$member_data_options['count_followers'] = __('Followers Count','vibebp');
			$member_data_options['count_following'] = __('Following Count','vibebp');
		}
		$groups_members_style_options =  array(
					'' =>__('Default','vibebp'),
					'names' =>__('Names','vibebp'),
					'pop_names' =>__('Names Popup','vibebp'),
				);
		$profile_data = apply_filters('vibebp_member_profile_actions',array(
			'view'=>__('View Profile','vibebp')
		));

		if(function_exists('bp_is_active') && bp_is_active('messages')){
			$profile_data['send_message'] =__('Send Message','vibebp');
		}

		if(bp_is_active('friends')){
			$profile_data['add_friend'] =__('Add Friend','vibebp');
		}
		if(vibebp_get_setting('bp_followers','bp','general')){
			$profile_data['follow'] = __('Follow Member','vibebp');
		}

		if(vibebp_get_setting('firebase_config','general','firebase') && defined('VIBE_CHAT_VERSION')){
			$profile_data['chat'] = __('Live Chat with Member','vibebp');
		}

		$fontwieght_options = apply_filters('vibe_bp_gutenberg_text_color_options',array(
			'100','200','300','400','500','600','700','800','900'
		));

		$default_last_active = 0;
		$user_id = bp_displayed_user_id();
		if(!empty($user_id)){
			$default_last_active = $this->vibe_bp_get_user_last_activity( $user_id );
		}else{
			$user_id = get_current_user_id();
			$default_last_active = $this->vibe_bp_get_user_last_activity( $user_id );
		}

		$member_types = array();
		if(function_exists('bp_get_member_types')){
			$member_type_objects = bp_get_member_types(array(),'objects');
			if(!empty($member_type_objects) && count($member_type_objects)){
				
				foreach($member_type_objects as $member_type=>$mt){
					$member_types[$member_type]=$member_type_objects[ $member_type ]->labels['singular_name'];
				}
				$member_types = array_merge(array('all'=>__('All','vibebp')),$member_types);
			}
		}
		$groups_types = array();
		if(function_exists('bp_groups_get_group_types')){
            $group_type_objects = bp_groups_get_group_types(array(),'objects');
			if(!empty($group_type_objects) && count($group_type_objects)){
			
				foreach($group_type_objects as $group_type=>$mt){
					$groups_types[$member_type]=$group_type_objects[ $group_type ]->labels['singular_name'];
				}
				$groups_types = array_merge(array('all'=>__('All','vibebp')),$groups_types);
			}

        }
		
		global $wpdb,$bp;$members_field_filters = array();
		$results = $wpdb->get_results("SELECT field.id as id, field.name as name FROM {$bp->profile->table_name_fields} as field INNER JOIN {$bp->profile->table_name_meta} as meta ON field.id = meta.object_id
			WHERE meta.object_type = 'field' AND meta.meta_key = 'do_autolink' AND meta.meta_value = 'on'");
		if(!empty($results)){
			$options = array();
			foreach($results as $result){
				$options[$result->id] = $result->name;
			}
			$members_field_filters = $options;
		}

		

		$settings = apply_filters('vibe_bp_gutenberg_data',array(
			'client_id'=>vibebp_get_setting('client_id'),
			'default_avatar'=>plugins_url( '../../assets/images/avatar.jpg',  __FILE__ ),
			'default_profile_value'=>_x('default value','','vibebp'),
			'default_group_name'=>_x('Yoga Champs','','vibebp'),
			'wall_image'=>plugins_url( '../../assets/images/avatar.jpg',  __FILE__ ),
			'default_name'=>_x('default name','','vibebp'),
			'profile_fields' => (array)$this->get_profiile_fields_array(),
			'default_text_color_options' => $color_options,
			'fontsizeunit_options' => $fontsizeunit_options,
			'fontwieght_options'=>$fontwieght_options,
			'default_last_active' => $default_last_active,
			'profile_field_styles' => $profile_field_styles,
			'avatar_style_options' => $avatar_style_options,
			'member_data_options' => $member_data_options,
			'profile_action_options' => apply_filters('vibebp_profile_actions',$profile_data),
			'groups_data_options' => apply_filters('vibebp_elementor_group_data',$groups_data_options),
			'groups_members_style_options' => $groups_members_style_options,
			'groups_members_options' => array(
					''=>__('All','vibebp'),
					'members' =>__('Members [Excluding Admin & Mods]','vibebp'),
					'mods' =>__('Moderators','vibebp'),
					'admin' =>__('Administrators','vibebp'),
				),
			'members_field_filters' => $members_field_filters,
			'google_maps_api_key'=>vibebp_get_setting('google_maps_api_key','general','misc'),
			'friends_sort_options' => array(
					'' =>__('Active','vibebp'),
					'newest' =>__('Recently Added','vibebp'),
					'alphabetical' =>__('Alphabetical','vibebp'),
					'random'=>__('Random','vibebp'),
					'popular'=>__('Popular','vibebp'),
				),
			'friends_style_options' =>  array(
					'' =>__('Default','vibebp'),
					'names' =>__('Names','vibebp'),
					'pop_names' =>__('Hover Names','vibebp'),
				),
			'members_sort_options' => array(
					'active' =>__('Active','vibebp'),
					'newest' =>__('Recently Added','vibebp'),
					'alphabetical' =>__('Alphabetical','vibebp'),
					'random'=>__('Random','vibebp'),
					'popular'=>__('Popular','vibebp'),
				),
			'members_style_options' =>  array(
					'' =>__('Default','vibebp'),
					'names' =>__('Names','vibebp'),
					'pop_names' =>__('Hover Names','vibebp'),
					'card' =>__('Card','vibebp'),
				),
			'members_types' =>  $member_types,
			'groups_types' =>  $groups_types,
			
			'member_block_min_default_width' =>  apply_filters('vibebp_member_block_min_default_width',768),
			'groups_style_options' =>  array(
					'' =>__('Default','vibebp'),
					'names' =>__('Name','vibebp'),
					'pop_names' =>__('Pop Names','vibebp'),
					'card' =>__('Card','vibebp'),
				),
			'current_user'=>wp_get_current_user(),
			'carousel'=>[
				'api'=>home_url().'/wp-json/'.Vibe_BP_API_NAMESPACE.'/get_carousel',
				'featured_blocks'=>[
					'members'=>vibebp_get_featured_blocks('members'),
					'groups'=>vibebp_get_featured_blocks('groups'),
					'taxonomy'=>vibebp_get_featured_blocks('taxonomy'),
					'post_type'=>vibebp_get_featured_blocks('post_type')
				],
				'sorters'=>[
					['label'=>__('Default','vibebp'),'value'=>''],
					['label'=>__('Alphabetical','vibebp'),'value'=>'alphabetical'],
				]
			],
			'api_url'=>home_url().'/wp-json/'.Vibe_BP_API_NAMESPACE,
		));

		wp_localize_script( 'vibebp-editor', 'vibe_bp_gutenberg_data', $settings );
		
		wp_enqueue_style('vibebp-swiper',plugins_url('../assets/css/swiper-bundle.min.css',__FILE__),[],VIBEBP_VERSION);
        	wp_enqueue_script('vibebp-swiper',plugins_url('../assets/js/swiper-bundle.min.js',__FILE__),array(),VIBEBP_VERSION,true);
		wp_enqueue_style('vicons',plugins_url('../../assets/vicons.css',__FILE__),array(),VIBEBP_VERSION);
		wp_enqueue_style('vibebp-front',plugins_url('../../assets/css/front.css',__FILE__),array(),VIBEBP_VERSION);	
	}

	function block_category( $categories, $post ) {

		
	        return array_merge(
	                $categories,
	                array(
	                        array(
	                                'slug' => 'vibebp',
	                                'title' => __( 'Vibe Buddypress', 'vibebp' ),
	                                'icon'  => 'wordpress',
	                        ),
	                )
	        );
	}

	function register_blocks(){
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}
		$this->_slug = 'vibebp';
		// Shortcut for the slug.
		$slug = $this->_slug;
		wp_register_style(
			'vibebp-style-css', // Handle.
			plugins_url( 'assets/css/new_blocks.css', dirname( __FILE__ ) ), 
			array( 'wp-editor' ), 
			null 
		);

		// Register block editor script for backend.
		wp_register_script(
			'vibebp-block-js', // Handle.
			plugins_url( '/assets/js/new_blocks.js', dirname( __FILE__ ) ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
			null, 
			true 
		);

		// Register block editor styles for backend.
		wp_register_style(
			'vibebp-block-editor-css',
			plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ),
			array( 'wp-edit-blocks' ), 
			null 
		);


		register_block_type(
			'vibebp/bpavatar', 
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'bpavatar')
			)
		);

		register_block_type(
			'vibebp/bpprofilefield', 
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'bpprofilefield'),
			)
		);

		register_block_type(
			'vibebp/memberprofiledata', 
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'member_profile_data')
			)
		);

		register_block_type(
			'vibebp/memberwall', 
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'member_wall')
			)
		);


		register_block_type(
			'vibebp/lastactive',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'lastactive')
			)
		);

		register_block_type(
			'vibebp/memberfriends',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'memberfriends')
			)
		);
		register_block_type(
			'vibebp/membergroups',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'membergroups')
			)
		);
		register_block_type(
			'vibebp/memberactions',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'memberactions')
			)
		);
		register_block_type(
			'vibebp/groupsavatar',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'groupsavatar')
			)
		);
		register_block_type(
			'vibebp/groupsdata',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'groupsdata')
			)
		);

		register_block_type(
			'vibebp/groupsdescription',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'groupsdescription')
			)
		);
		
		register_block_type(
			'vibebp/groupsmembers',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'groupsmembers')
			)
		);

		register_block_type(
			'vibebp/groupstitle',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'groupstitle')
			)
		);



		register_block_type(
			'vibebp/membersdirectory', 
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'membersdirectory')
			)
		);
		register_block_type(
			'vibebp/groupsdirectory', 
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'groupsdirectory')
			)
		);

		register_block_type(
			'vibebp/carousel', 
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'carousel')
			)
		);

	}

	function carousel($atts){

		$default = array(
			'coursel_id'=>rand(0,999),
			'carousel_type'=>'members',
			'carousel_number'=>9,			
			'member_featured_style'=>'',
			'include_member_types'=>'',
			'include_member_ids'=>'',
			'exclude_member_ids'=>'',
			'member_order'=>'',
			'group_featured_style'=>'',
			'include_group_types'=>'',
			'exclude_group_types'=>'',
			'include_group_ids'=>'',
			'exclude_group_ids'=>'',
			'group_order'=>'',
			'taxonomy'=>'',
			'taxonomy_featured_style'=>'',
			'only_parents'=>'',
			'parent_term'=>'',
			'include_terms'=>'',
			'exclude_terms'=>'',
			'taxonomy_featured_style'=>'',
			'term_order'=>'',
			'post_type'=>'',
			'post_type_featured_style'=>'',
			'include_post_ids'=>'',
			'exclude_post_ids'=>'',
			'post_type_taxonomy'=>'',
			'post_type_include_terms'=>'',
			'post_type_exclude_terms'=>'',
			'carousel_columns'=>4,
			'space'=>40,
			'loop'=>0,
			'show_controlnav'=>0,
			'show_controls'=>1,
			'auto_slide'=>0
		);


		$settings =  wp_parse_args($atts,$default);

		if(!empty($settings['carousel_columns'])){
			$settings['columns']=$settings['carousel_columns'];	
		}
		
		$shortcode = vibebp_generate_carousel_shortcode($settings);

		return do_shortcode($shortcode);
	}

	function groupsdirectory($settings){
		$defaults = array(
			'style'=>'card',
			'card_style'=>'card',
			'sort_groups'=>true,
			'search_groups'=>true,
		);


		if(!empty($settings['groups_per_page'])){
			$settings['groups_per_page'] = array('size'=>$settings['groups_per_page']);
		}

		$settings = wp_parse_args($settings,$defaults);
		wp_enqueue_script('vibebp-groups-directory-js',plugins_url('../../assets/js/groups.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION,true);
		wp_enqueue_style('vibebp-front',plugins_url('../../assets/css/front.css',__FILE__),array(),VIBEBP_VERSION);
		wp_enqueue_style('vicons',plugins_url('../../assets/vicons.css',__FILE__),array(),VIBEBP_VERSION);
		
		$group_types = [];
		if(function_exists('bp_groups_get_group_types')){
			$gt = bp_groups_get_group_types(array(),'objects');
			foreach($gt as $k=>$t){
				$group_types[$k]=$t->labels['name'];
			}
		}


		if(!empty($settings['show_group_popup']) && $settings['show_group_popup']){
			wp_enqueue_script('singlegroup',plugins_url('../../assets/js/singlegroup.js',__FILE__),array(),VIBEBP_VERSION,true);
			
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
			)
		);
		wp_localize_Script('vibebp-groups-directory-js','vibebpgroups',$this->args);
		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			$user_id = get_current_user_id();
		}
		
		$args = array(
			'type'		=>empty($settings['order'])?'':$settings['order'],
			'per_page'	=> (empty($settings['groups_per_page'])?10:(is_numeric($settings['groups_per_page'])?$settings['groups_per_page']:$settings['groups_per_page']['size']))
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
		$html = '';
		ob_start();
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
						echo '<a href="'.bp_get_group_permalink($group).'"><img src="'.$group->avatar.'" /></a>';
						if($settings['card_style'] == 'names' || $settings['card_style'] == 'pop_names'){
							echo '<span>'.$group->name.'</span>';
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
		<?php
		$html .= ob_get_clean();
		add_filter('vibebp_inside_pwa_scripts',function($scripts){
			$scripts['vibebpgroups']= plugins_url('../../assets/js/groups.js',__FILE__); return $scripts;});
        add_filter('vibebp_inside_pwa_objects',array($this,'pwa_object'));
        add_filter('vibebp_inside_pwa_styles',array($this,'pwa_styles_groups'),10,2);
        add_filter('vibebp_inside_pwa_objects',array($this,'pwa_object_groups'));

        return $html;
	}

	function pwa_styles_groups($styles,$post_id){
		$upload_dir   = wp_upload_dir();
		if(file_exists($upload_dir['basedir'].'/elementor/css/post-'.$post_id.'.css')){
			$styles['elementor_specific_css']=$upload_dir['baseurl'].'/elementor/css/post-'.$post_id.'.css?v='.WPLMS_PLUGIN_VERSION;	
		}
		return $styles;
	}

	function pwa_object_groups($objects){
		$objects['vibebpgroups']= $this->args; 
		return $objects;
	}

	function membersdirectory($settings){
		if(!empty($settings['members_per_page'])){
			$settings['members_per_page'] = array('size'=>$settings['members_per_page']);
		}

		$defaults = array(
			'card_style' => 'card',
			'members_per_page' => array('size'=>5),
			'full_avatar' => false,
			'order'=>'alphabetical',
			'member_directory_filters'=>array(),
			'member_type'=>'all',
			'sort_members'=>true,
			'search_members'=>true,
			'show_map'=>false,
			'horizontal_filters'=>false,
			'per_row' => array('size'=>100,'unit'=>'%'),
			'members_pagination' => true,
		);

		$settings = wp_parse_args($settings,$defaults);

		$this->settings = $settings;

		wp_register_script('vibebp-members-directory-js',plugins_url('../../assets/js/members.js',__FILE__),array('wp-element','wp-data','wp-redux-routine','wp-hooks'),VIBEBP_VERSION,true);
		
		wp_enqueue_script('vibebp-members-directory-js');
		wp_enqueue_style('vicons');
		wp_enqueue_style('vibebp-front');

		wp_enqueue_script('vibebp-members-actions',plugins_url('../../assets/js/actions.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION,true);

		$blog_id = '';
        if(function_exists('get_current_blog_id')){
            $blog_id = get_current_blog_id();
        }
        $html = '';
		wp_localize_script('vibebp-members-actions','vibebpactions',apply_filters('vibebpactions_translations',array(
			'api_url'=>apply_filters('vibebp_rest_api',get_rest_url($blog_id,Vibe_BP_API_NAMESPACE)),
			'friends'=>bp_is_active('friends')?1:0,
			'followers'=>vibebp_get_setting('followers','bp','general')?1:0,
			'translations'=>array(
				'message_text'=>__('Type message','vibebp'),
				'message_subject'=>__('Message subject','vibebp'),
				'cancel'=>__('Cancel','vibebp'),
				'offline'=>__('Offline','vibebp'),
			)
		)));
		
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

			wp_enqueue_script('vibebp-members-actions',plugins_url('../../assets/js/actions.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION,true);
		}

		if(!empty($settings['show_member_popup'])){
			wp_enqueue_script('singleprofile',plugins_url('../../assets/js/singleprofile.js',__FILE__),array(),VIBEBP_VERSION,true);
			
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
				'map_marker'=>plugins_url('../../assets/images/marker.png',__FILE__)
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



		wp_localize_script('vibebp-members-directory-js','vibebpmembers',$this->args);
		
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
		ob_start();
		?>
		<style>.member_card .vibebp-directory-content-block { background-color: #fff; width: 300px; border-radius: 3px; } .member_card .vibebp-directory-content-block .loader-wrapper { height: 300px; padding: 12px; } .member_card .vibebp-directory-content-block .loader-animation { background: #f5f6f7; height: 107px; width:300px; overflow: hidden; position: relative; } .member_card .vibebp-directory-content-block .loader-animation svg { position: absolute; fill:#fff; top:0; left:-1px; width:442px; height:107px; } .member_card .vibebp-directory-content-block .loader-animation::before { background-color: #f5f6f7; background-image: url(<?php echo plugins_url('../../assets/images/loading.gif',__FILE__);?>); background-repeat: repeat-y; background-size: 100% 1px; content: ' '; display: block; height: 100%; }</style>
		<div id="vibebp_members_directory" <?php  echo $settings['show_map']?'class="with_map"':''?>">
			<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
			<div class="vibebp_members_directory_wrapper opacity_0 <?php  echo $settings['horizontal_filters']?'horizontal_filters':''?>">
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
				<div class="vibebp_members_directory <?php echo $settings['card_style'];?>" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(<?php echo $settings['per_row']['size']; ?>px,1fr))">
					<?php 
					if( $run['total'] ){
						foreach($run['users'] as $key=>$member){
							echo '<div class="vibebp_member">';
							echo '<a href="'.bp_core_get_user_domain($member->id).'"><img src="'.$member->avatar.'" /></a>';
							if($settings['card_style'] == 'names' || $settings['card_style'] == 'pop_names'){
								echo '<span>'.$member->name.'</span>';
							}
							echo '</div>';
						}
					}
					?>
				</div>
				<?php
				if( $run['total'] > count($run['users'])){
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
		<?php
		$html .= ob_get_clean();


		add_filter('vibebp_inside_pwa_scripts',function($scripts){
			$scripts['vibebpmembers']= plugins_url('../../assets/js/members.js',__FILE__); return $scripts;});

		add_filter('vibebp_inside_pwa_styles',array($this,'pwa_styles'),10,2);
        add_filter('vibebp_inside_pwa_objects',array($this,'pwa_object'));

        return $html;
	}

	function pwa_styles($styles,$post_id){
		$upload_dir   = wp_upload_dir();
		if(file_exists($upload_dir['basedir'].'/elementor/css/post-'.$post_id.'.css')){
			$styles['elementor_specific_css']=$upload_dir['baseurl'].'/elementor/css/post-'.$post_id.'.css?v='.VIBEBP_VERSION;	
		}
		if($this->settings['card_style'] == 'card'){
			global $wpdb;
			$ids = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_type='member-card' AND post_status='publish'");
			
			if(!empty($ids)){
				$upload_dir   = wp_upload_dir();
				foreach($ids as $id){
					if(file_exists($upload_dir['basedir'].'/elementor/css/post-'.$id->ID.'.css')){
						$styles['vibebp-member-card-'.$id->ID]=$upload_dir['baseurl'].'/elementor/css/post-'.$id->ID.'.css?v='.VIBEBP_VERSION;	
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

	function groupstitle(){
		if(bp_get_current_group_id()){
			$group_id = bp_get_current_group_id();
		}else{
			global $groups_template;
			if(!empty($groups_template->group)){
				$group_id = $groups_template->group->id;
			}
		}
		if(empty($group_id)){
			$init = VibeBP_Init::init();
			if(!empty($init->group_id)){
				$group_id = $init->group_id;
			}
		}

		if(empty($group_id) && empty($_GET['action'])){
			$init = VibeBP_Init::init();
			if(empty($init->group_id)){
				global $wpdb,$bp;
				$group_id = $wpdb->get_var("SELECT id FROM {$bp->groups->table_name} LIMIT 0,1");
				$init->group_id = $group_id;
			}else{
				$group_id = $init->group_id;
			}
		}

		$init = VibeBP_Init::init();		
		if(!empty($init->group) && !empty($init->group->id) &&  $init->group->id == $group_id){
			$group = $init->group;
		}else if(empty($init->group)){
			$init->group = groups_get_group($group_id);
			$group = $init->group;
		}
		
		

		$title = bp_get_group_name($group);
		if(empty($title)){
			$title = 'Group Title';
		}
		


        return '<h2 class="title"><a href="'.bp_get_group_permalink( $group ).'">'.$title.'</a></h2>';
	}

	function groupsmembers($settings){
		if(bp_get_current_group_id()){
			$group_id = bp_get_current_group_id();
		}else{
			global $groups_template;
			if(!empty($groups_template->group)){
				$group_id = $groups_template->group->id;
			}
		}
		if(empty($group_id)){
			$init = VibeBP_Init::init();
			if(!empty($init->group_id)){
				$group_id = $init->group_id;
			}
		}

		if(empty($group_id) && empty($_GET['action'])){
			$init = VibeBP_Init::init();
			if(empty($init->group_id)){
				global $wpdb,$bp;
				$group_id = $wpdb->get_var("SELECT id FROM {$bp->groups->table_name} LIMIT 0,1");
				$init->group_id = $group_id;
			}else{
				$group_id = $init->group_id;
			}
		}

		
		
		global $wpdb,$bp;

		
		$q = $wpdb->prepare("
			SELECT user_id as id FROM {$bp->groups->table_name_members} WHERE group_id = %d",$group_id);

		if($settings['role'] == 'members'){
			$q = $wpdb->prepare("
			SELECT user_id as id FROM {$bp->groups->table_name_members} WHERE group_id = %d AND is_mod !=1 AND is_admin !=1",$group_id);
		}
		if($settings['role'] == 'mods'){
			$q = $wpdb->prepare("
			SELECT user_id as id FROM {$bp->groups->table_name_members} WHERE group_id = %d AND is_mod = 1",$group_id);
		}
		if($settings['role'] == 'admin'){
			$q = $wpdb->prepare("
			SELECT user_id as id FROM {$bp->groups->table_name_members} WHERE group_id = %d AND is_admin = 1",$group_id);
		}

		$members = $wpdb->get_results($q,ARRAY_A);
		
        $html .= '<div class="vibebp_group_members '.$style.'"> ';
        if(!empty($members)){
        	foreach($members as $member){ 
        		$html .= '<div class="vibebp_member">';
        		$html .= '<a href="'.bp_core_get_user_domain($member['id']).'"><img src="'.bp_core_fetch_avatar(array('item_id'=>$member['id'],'object'  => 'user','type'=>'full','html'    => false)).'" ></a>';
        		$html .= '<span>'.bp_core_get_user_displayname($member['id']).'</span>';
        		$html .= '</div>';
        	}
        }
        $html .= ' </div><style>.vibebp_group_members{display:flex;flex-wrap:wrap;margin:-.5rem}.vibebp_group_members>*{flex:1 0 80px;display:flex;align-items:center;background:var(--border);border-radius:32px;margin:.5rem}.vibebp_group_members>* img{width:32px;height:32px;border-radius:50% !important;margin-right:10px}.vibebp_group_members>* span{text-overflow: ellipsis; white-space: nowrap; max-width: 64px; overflow: hidden;}</style>';

        return $html;
	}

	function groupsdescription($settings){
		if(bp_get_current_group_id()){
			$group_id = bp_get_current_group_id();
		}else{
			global $groups_template;
			if(!empty($groups_template->group)){
				$group_id = $groups_template->group->id;
			}
		}
		if(empty($group_id)){
			$init = VibeBP_Init::init();
			if(!empty($init->group_id)){
				$group_id = $init->group_id;
			}
		}
		$init = VibeBP_Init::init();
		if(!empty($init->group) && !empty($init->group->id) && $init->group->id == $group_id){
			$group = $init->group;
		}else if(empty($init->group)){
			$init->group = groups_get_group($group_id);
			$group = $init->group;
		}

		

		$description = '';
		if(!empty($group)){
			$description = bp_get_group_description($group);
		}
		if(empty($description)){
			$description = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.";
		}
		$style='';
		if(!empty($settings['text_color'])){
			$style .= 'color:'.$settings['text_color'].';';
		}
		if(!empty($settings['font_size'])){
			$style .= 'font-size:'.$settings['font_size'].'px';
		}

        return '<div class="group_description" style="'.$style.'"> '.$description.' </div>';
	}

	function groupsdata($settings){
		$group_id = '';

		if(function_exists('bp_get_current_group_id') && !empty(bp_get_current_group_id())){
			$group_id = bp_get_current_group_id();
		}
		$init = VibeBP_Init::init();
		if(empty($group_id)){
			if(!empty($init->group_id)){
				$group_id = $init->group_id;
			}
		}



		if(empty($group_id) && !empty($_GET['action'])){
			if(empty($init->group_id)){
				global $wpdb,$bp;
				$group_id = $wpdb->get_var("SELECT id FROM {$bp->groups->table_name} LIMIT 0,1");
				$init->group_id = $group_id;
			}else{
				$group_id = $init->group_id;
			}
		}


		if(!empty($init->group) && $init->group->id == $group_id){
			$group = $init->group;
		}else if(empty($init->group) && !empty($group_id)){
			$init->group = groups_get_group($group_id);
			$group = $init->group;
		}
		$style='';
		if(!empty($settings['text_color'])){
			$style .= 'color:'.$settings['text_color'].';';
		}
		if(!empty($settings['font_size'])){
			$style .= 'font-size:'.$settings['font_size'].'px';
		}
        $html = '<div class="group_data_field" style="'.$style.'">';
        
        if(!empty($settings['data']))
        $html .= $this->get_group_data($settings['data'],$group_id);
        $html .= '</div>';

        return $html;
	}

	function get_group_data($type,$group_id){


		if(empty($this->group) || $this->group->id != $group_id){
			$this->group = groups_get_group($group_id);	
		}

		switch($type){
			case 'last_active':
				$time = groups_get_groupmeta( $group_id, 'last_activity',true);
				if(empty($time)){
					$time = bp_core_current_time();
				}
				if(!empty($time))
				return bp_core_time_since($time);
			break;
			case 'create_date':
				return bp_get_group_date_created($this->group);
			break; 
			case 'creator_name':
				return bp_get_group_creator_username();
			break;
			case 'last_status_update':
				$activity = bp_get_user_last_activity($user_id);
				return $activity->content;
			break; 
			case 'admin_count':
				return count( groups_get_group_admins( $group_id ) );
			break; 
			case 'moderator_count':
				return count( groups_get_group_mods( $group_id ) );
			break; 
			case 'member_count':
				return bp_get_group_total_members($this->group);
			break;
			case 'group_status':
				return bp_get_group_status( $group_id );
			break;
			case 'join_button':
				return '<a class="button is-primary join_group_button" data-status="'.$this->group->status.'" data-id="'.$group_id.'">'.__('Join','vibebp').'</a>';
				$vibebp_elementor=VibeBP_Elementor_Init::init();
				add_action('wp_footer',array($vibebp_elementor,'join_button'));
			break;
			default:
				do_action('vibebp_get_group_data',$type,$group_id);
			break;
		}
	}

	function groupsavatar($settings){
		if(bp_get_current_group_id()){
			$group_id = bp_get_current_group_id();
		}else{
			global $groups_template;
			if(!empty($groups_template->group)){
				$group_id = $groups_template->group->id;
			}
		}

		$init = VibeBP_Init::init();

		if(empty($group_id)){
			
			if(!empty($init->group_id)){
				$group_id = $init->group_id;
			}
		}

		if(empty($group_id) && empty($_GET['action'])){
			
			if(empty($init->group_id)){
				global $wpdb,$bp;
				$group_id = $wpdb->get_var("SELECT id FROM {$bp->groups->table_name} LIMIT 0,1");
				$init->group_id = $group_id;
			}else{
				$group_id = $init->group_id;
			}
		}

		if(!empty($init->group) && $init->group->id == $group_id){
			$group = $init->group;
		}else if(empty($init->group) && !empty($group_id)){
			$init->group = groups_get_group($group_id);
			$group = $init->group;
		}

		if(!empty($group_id)){
			$src =  bp_core_fetch_avatar(array(
	            'item_id' => $group_id,
	            'object'  => 'group',
	            'type'=>'full',
	            'html'    => false
	        ));
		}

		

        if(empty($src)){
        	$src = plugins_url('../../assets/images/avatar.jpg',__FILE__);
        }

        $style ='';
        if(!empty($settings['width'])){
        	$style .= 'width:'.$settings['width'].'px;';
        }
        if(!empty($settings['radius'])){
        	$style .= 'border-radius:'.$settings['radius'].'%;';
        }
        $link = '';
        if(function_exists('bp_get_group_permalink') && bp_get_current_group_id()){
        	$link = bp_get_group_permalink();
        }
        return '<a href="'.$link.'"><img src="'.$src.'" class="'.(empty($settings['style'])?'':$settings['style']).'" '.(empty($style)?'':'style="'.$style.'"').' /></a>';
	}

	function memberactions($settings){


		if(empty($user_id)){
			$init = VibeBP_Init::init();
			if(!empty($init->user_id)){
				$user_id = $init->user_id;
			}
		}
		if(empty($user_id) && bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			global $members_template;
			if(!empty($members_template->member)){
				$user_id = $members_template->member->id;
			}
		}
		if(empty($user_id) && is_user_logged_in()){
			$user_id = get_current_user_id();
		}
		

        $blog_id = '';
        if(function_exists('get_current_blog_id')){
            $blog_id = get_current_blog_id();
        }

        
		$vibebp_elementor=VibeBP_Elementor_Init::init();
		add_action('wp_footer',array($vibebp_elementor,'event_button'),999);

		wp_localize_script('vibebp-members-actions','vibebpactions',apply_filters('vibebpactions_translations',array(
			'api_url'=>apply_filters('vibebp_rest_api',get_rest_url($blog_id,Vibe_BP_API_NAMESPACE)),
			'friends'=>bp_is_active('friends')?1:0,
			'followers'=>vibebp_get_setting('followers','bp','general')?1:0,
			'translations'=>array(
				'message_text'=>__('Type message','vibebp'),
				'message_subject'=>__('Message subject','vibebp'),
				'cancel'=>__('Cancel','vibebp'),
				'offline'=>__('Offline','vibebp'),
			)
		)));

       	wp_enqueue_script('vibebp-members-actions',plugins_url('../../assets/js/actions.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION);
       	$label = '';
       	

       	if(empty($settings['action']) ){$settings['action']='view';};
       	if(empty($settings['post_action_label'])){$settings['post_action_label']='';}
       	return '<span class="profile_data_action">'.$this->get_profile_action($settings['action'],$settings['action_label'],$settings['post_action_label']).'</span>';
	}

	function get_profile_action($action,$label,$post_action_label){

		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			global $members_template;
			if(!empty($members_template->member)){
				$user_id = $members_template->member->id;
			}
		}
		if(empty($user_id)){
			$init = VibeBP_Init::init();
			if(!empty($init->user_id)){
				$user_id = $init->user_id;
			}else{
				$user_id = get_current_user_id();
			}
		}
		
		
		switch($action){
			case 'view':
				return '<a class="member_action view_profile" href="'.bp_core_get_user_domain($user_id).'"><span class="button is-primary">'.$label.'</span></a>';
			break;
			case 'send_message':
				return '<a class="member_action send_message" data-member="'.$user_id.'"><span class="button is-primary">'.$label.'</span><span class="hide">'.$post_action_label.'</span></a>';
			break;
			case 'add_friend':
				return '<a class="member_action friend" data-member="'.$user_id.'"><span class="button is-primary">'.$label.'</span><span class="hide">'.$post_action_label.'</span></a>';
			break;
			case 'follow':
				return '<a class="member_action follow" data-member="'.$user_id.'"><span class="button is-primary">'.$label.'</span><span class="hide">'.$post_action_label.'</span></a>';
			break;
			default:
				ob_start();
				do_action('vibe_profile_action_'.$action,$user_id,$label,$post_action_label);
				return ob_get_clean();
			break;

		}
	}

	function membergroups($settings){

		if(!function_exists('groups_get_groups'))
			return;
		
		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			global $members_template;
			if(!empty($members_template->member)){
				$user_id = $members_template->member->id;
			}
		}
		if(empty($user_id)){
			$init = VibeBP_Init::init();
			if(!empty($init->user_id)){
				$user_id = $init->user_id;
			}else{
				$user_id = get_current_user_id();
			}
		}
		
		$args = array(
			'type'		=>empty($settings['order'])?'':$settings['order'],
			'user_id'	=>$user_id,
			'per_page'	=>empty($settings['show_groups']) || empty($settings['show_groups']['size'])?5:$settings['show_groups']['size']
		);
		$run = groups_get_groups($args);
    		
		if( count($run['groups']) ) {

			foreach($run['groups'] as $k=>$group){
				$run['groups'][$k]->avatar = bp_core_fetch_avatar(array(
                        'item_id' => $run['groups'][$k]->id,
                        'object'  => 'group',
                        'type'=>'thumb',
                        'html'    => false
                    ));
			}
		}
		ob_start();
		?>
		<div class="vibebp_user_groups <?php echo $settings['style'];?>">
			<?php 
			if( $run['total'] ){
				foreach($run['groups'] as $key=>$group){
					echo '<div class="vibebp_user_group">';
					echo '<img src="'.$group->avatar.'" />';
					if($settings['style'] == 'names' || $settings['style'] == 'pop_names'){
						echo '<span>'.$group->name.'</span>';
					}
					echo '</div>';
				}
				if($settings['groups_count'] && ($run['total'] - count($run['groups']))){
					echo '<span>'.($run['total'] - count($run['groups'])).' '.__('more','vibebp').'</span>';	
				}
				
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	function memberfriends($settings){

		

		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			global $members_template;
			if(!empty($members_template->member)){
				$user_id = $members_template->member->id;
			}
		}
		if(empty($user_id)){
			$init = VibeBP_Init::init();
			if(!empty($init->user_id)){
				$user_id = $init->user_id;
			}else{
				$user_id = get_current_user_id();
			}
		}

		
		$run = bp_core_get_users( array(
				'type'         => empty($settings['order'])?'':$settings['order'],
				'per_page'     => empty($settings['show_friends'])?5:$settings['show_friends']['size'],
				'user_id'      => $user_id,
			) );

		if( $run['total'] ){

			foreach($run['users'] as $key=>$user){
				$run['users'][$key]->latest_update = maybe_unserialize($user->latest_update);
				$run['users'][$key]->avatar = bp_core_fetch_avatar(array(
                    'item_id' 	=> $user->ID,
                    'object'  	=> 'user',
                    'type'		=>'thumb',
                    'html'    	=> false
                ));
			}
		}
		ob_start();
		?>
		<div class="vibebp_user_friends <?php echo empty($settings['style'])?'':$settings['style'];?>">
			<?php 
			if( $run['total'] ){
				foreach($run['users'] as $key=>$user){
					echo '<div class="vibebp_user_friend">';
					echo '<img src="'.$user->avatar.'" />';
					if($settings['style'] == 'names' || $settings['style'] == 'pop_names'){
						echo '<span>'.$user->display_name.'</span>';
					}
					echo '</div>';
				}
				if($settings['friends_count'] && ($run['total'] - count($run['users']))){
					echo '<span>'.($run['total'] - count($run['users'])).' '.__('more','vibebp').'</span>';	
				}
				
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	function member_profile_data($settings){

		$default = array(
			'data'=>'member_type'
		);

		$settings = wp_parse_args($settings,$default);
		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			$user_id = get_current_user_id();
		}


        return '<span class="profile_data_field '.(isset($settings['style'])?$settings['style']:'').'" style="'.(isset($settings['text_color'])?'color:'.$settings['text_color']:'').';'.(isset($settings['font_size'])?'font-size:'.$settings['font_size'].'px':'').'">'.(empty($settings['data'])?'':$this->get_profile_data($settings['data'])).'</span>';
	}

	function member_wall($settings){
		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			global $members_template;
			if(!empty($members_template->member)){
				$user_id = $members_template->member->id;
			}
		}
		if(empty($user_id)){
			$init = VibeBP_Init::init();
			if(!empty($init->user_id)){
				$user_id = $init->user_id;
			}else{
				$user_id = get_current_user_id();
			}
		}
		
		wp_enqueue_script('vibebp-members-wall',plugins_url('../../assets/js/wall.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION);

		wp_enqueue_script('vibebplogin');
		$rg= VibeBP_Register::init();
		wp_localize_script('vibebplogin','vibebp',$rg->get_vibebp());	

		return '<div class="vibebp_user_wall" data-user="'.$user_id.'"></div>';
	}

	function get_profile_data($type){
		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			global $members_template;
			if(!empty($members_template->member)){
				$user_id = $members_template->member->id;
			}
		}
		if(empty($user_id)){
			$init = VibeBP_Init::init();
			if(!empty($init->user_id)){
				$user_id = $init->user_id;
			}else{
				$user_id = get_current_user_id();
			}
		}
		
		switch($type){
			case 'member_type':
				$mtype =  bp_get_member_type($user_id);
				$member_type_object = bp_get_member_type_object( $mtype );
				if(is_object($member_type_object)){
					return $member_type_object->labels['singular_name'];	
				}else{
					return _x('N.A','no member type','vibebp');
				}
			break;
			case 'last_active':
				$activity = bp_get_user_last_activity($user_id);
				if(!empty($activity))
				return bp_core_time_since($activity);
			break;
			case 'time_in_site':
				$time = get_userdata($user_id)->user_registered;
				if(!empty($time))
				return bp_core_time_since($time);
			break; 
			case 'last_status_update':
				$activity = bp_get_user_last_activity($user_id);
				return empty($activity) || empty($activity->content)?'':$activity->content;
			break; 
			case 'count_friends':
				if ( bp_is_active( 'friends' ) ) {
					return BP_Friends_Friendship::total_friend_count( $user_id );
				}
			break; 
			case 'count_groups':
				if ( bp_is_active( 'groups' ) ) {
					return BP_Groups_Member::total_group_count( $user_id );
				}
			break;
			case 'count_followers':
				global $wpdb;
				$count = $wpdb->get_var($wpdb->prepare("
    			SELECT count(user_id) 
    			FROM {$wpdb->usermeta}
    			WHERE meta_key ='vibebp_follow' 
    			AND meta_value = %d",
    			$user_id));
				return intval($count);
			break;
			case 'count_following':
				$following = get_user_meta($user_id,'vibebp_follow',false);
				return count($following);
			break;
			default:
				ob_start();
				do_action('vibebp_profile_get_profile_data',$type,$user_id);
				return ob_get_clean();
			break;
		}
	}

	function bpprofilefield($atts){

		$default = array(
			'text_color'=>'',
			'style'=>''
		);
		

		$settings =  wp_parse_args($atts,$default);
	
		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			global $members_template;
			if(!empty($members_template->member)){
				$user_id = $members_template->member->id;
			}
		}
		if(empty($user_id)){
			$init = VibeBP_Init::init();
			if(!empty($init->user_id)){
				$user_id = $init->user_id;
			}else{
				$user_id = get_current_user_id();
			}
		}

		
		$field = xprofile_get_field( $settings['field_id'] );
		$value = xprofile_get_field_data( $settings['field_id'], $user_id);

		$value = vibebp_process_profile_field_data($value,$field,$user_id);

		ob_start();
		?>
		<div class="vibebp_profile_field field_<?php echo $settings['field_id'].' '.$settings['style']; ?> " <?php  echo 'style="color:'.(!empty($settings['text_color'])?$settings['text_color']:'').';font-size:'.(!empty($settings['font_size'])?$settings['font_size']:'inherit').'px"'; ?>>
			<?php
				if($settings['style'] == 'icon'){
					?><label class="<?php echo $field->name ?>"></label><?php
				}else if($settings['style'] != 'nolabel' ){
					?><label><?php echo $field->name ?></label><?php
				}
				$value = apply_filters('vibebp_profile_field_block_value',$value,$field);
				if(is_array($value)){
					$value = '';
				}
			?>
			<div class="<?php echo sanitize_title($field->name).' field_type_'.$field->type;?> "><?php echo do_shortcode($value); ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}


	function bpavatar($settings){

		if(bp_displayed_user_id()){
			$user_id = bp_displayed_user_id();
		}else{
			global $members_template;
			if(!empty($members_template->member)){
				$user_id = $members_template->member->id;
			}
		}
		if(empty($user_id)){
			$init = VibeBP_Init::init();
			if(!empty($init->user_id)){
				$user_id = $init->user_id;
			}else{
				$user_id = get_current_user_id();
			}
		}
		
		
		$src =  bp_core_fetch_avatar(array(
            'item_id' => $user_id,
            'object'  => 'user',
            'type'=>'full',
            'html'    => false
        ));



        $style = $cover_style = '';
        if(!empty($settings['width'])){
        	$style .= 'width:'.$settings['width'].'px;';
        }
        if(!empty($settings['radius'])){
        	$style .= 'border-radius:'.$settings['radius'].'%;';
        }
        
        if(bp_is_active('members','cover_image')){
        	$cover_src = bp_attachments_get_attachment( 'url', array(
				'item_id'   => $user_id,
			) );
			
        	if(!empty($cover_src)){ 
        		$cover_style = '<style>.background_cover{background:url('.$cover_src.') center; background-size:cover;border-radius:10px;margin:0 -1.5rem 1.5rem;padding:4.5rem 1.5rem 1.5rem;}</style>';
        	}else{
        		$cover_style = '<style>.background_cover{background:var(--primary); background-size:cover;margin:0 -1.5rem 1.5rem;padding:4.5rem 1.5rem 1.5rem;}</style>';
        	}
        }
        return '<a class="'.(empty($settings['style'])?'':$settings['style']).'" href="'.bp_core_get_user_domain( $user_id ).'"><img src="'.$src.'" '.(empty($style)?'':'style="'.$style.'"').' /></a>'.$cover_style;
	}

	function lastactive($atts){
		$styles = array(

			'font-size'=>$atts['customFontSize'].'px',
			'font-weight'=>$atts['fontWeight'],
			'color'=>$atts['font']['Color'],
			'font-family'=>$atts['fontFamily'],
			//'line-height'=>props.attributes.lineHeight,
			'letter-spacing'=>$atts['letterSpacing'].'px',
			'text-transform'=>$atts['textTransform']
		);


		$stylestring = '';
		foreach ($styles as $key => $style) {
			$stylestring .= $key.':'.$style.';';
		}
		$time = 0;
		$user_id = bp_displayed_user_id();
		if(!empty($user_id)){
			$time = $this->vibe_bp_get_user_last_activity( $user_id );
		}else{
			$user_id = get_current_user_id();
			$time = $this->vibe_bp_get_user_last_activity( $user_id );
		}

		return '<div class="lastactive_member" style="'.$stylestring.'">
	                 <span class="bp_last_active"><label>'.$time.'</label></span>
	            </div>';

	}

	


	function vibe_bp_get_user_last_activity($user_id){
		ob_start();
		bp_last_activity($user_id);
		$html = ob_get_clean();
		return $html;
	}
	
}

VibeBP_Register_Blocks::register();
