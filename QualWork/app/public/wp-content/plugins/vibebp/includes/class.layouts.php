<?php
/**
 * Register Post Types
 *
 * @class       VibeBP_PostTypes
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VibeBP_PostTypes{

	private $render_content = 0;
	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_PostTypes();
        return self::$instance;
    }

	private function __construct(){
		
		add_action( 'init', array($this,'post_types'),5 );

		add_filter('the_content',array($this,'apply_single_template_content'));
		//add_filter('member_profile_content',array($this,'apply_single_tempalte')); No longer required
		add_filter('single_template', array($this,'layouts_template'));

		add_action('admin_init',array($this,'default_marker'));
		add_action('wp_ajax_default_post',array($this,'mark_default'));
		
	}

	function apply_single_template_content($content){
		if(empty(bp_displayed_user_id()) || !function_exists('did_filter') || function_exists('wplms_define_constants') || function_exists('tutorly_update_option')){
			return $content;
		}
		$did_filter = did_filter('the_content');

		if(!empty($did_filter) && $did_filter >1){
			return '';
		}

		if($this->render_content){
			return $content;	
		}


		ob_start();
			$layout='';
			if(bp_get_member_type(bp_displayed_user_id())){
				//Ensure if user has a member type assigned
				$layout = new WP_Query(apply_filters('vibebp_public_profile_layout_query',array(
					'post_type'=>'member-profile',
					'post_name'=>bp_get_member_type(bp_displayed_user_id()),
					'posts_per_page'=>1,
					'meta_query'=>array(
						'relation'=>'AND',
						array(
							'key'=>'member_type',
							'compare'=>'NOT EXISTS'
						)
					)
				)));
			}

			if ( !$layout || !$layout->have_posts() ){

				$layout = new WP_Query(array(
					'post_type'=>'member-profile',
					'post_name'=>bp_get_member_type(bp_displayed_user_id()),
					'posts_per_page'=>1,
					'meta_query'=>array(
						'relation'=>'AND',
						array(
							'key'=>'default_member-profile',
							'compare'=>'=',
							'value'=>1
						)
					)
				));
			}

			if ( !$layout->have_posts() ){
				$layout = new WP_Query(array(
					'post_type'=>'member-profile',
					'orderby'=>'date',
					'order'=>'ASC',
					'posts_per_page'=>1,
					'meta_query'=>array(
						'relation'=>'AND',
						array(
							'key'=>'member_type',
							'compare'=>'NOT EXISTS'
						)
					)	
				));
			}

			
			if ( $layout->have_posts() ) :
				
				
				while ( $layout->have_posts() ) :
					$layout->the_post();
					$this->render_content++;
					the_content();
					if(class_exists('\Elementor\Frontend')){
						
					 	$elementorFrontend = new \Elementor\Frontend();
	                    $elementorFrontend->enqueue_scripts();
	                    $elementorFrontend->enqueue_styles();
	                }
					
				endwhile;
			endif;
		wp_reset_postdata();

		$content .= ob_get_clean();


		return $content;
	}
	function apply_single_tempalte(){

		
		if(empty(bp_displayed_user_id()) || $this->render_content){
			return ;
		}

		

		$layout='';
		if(bp_get_member_type(bp_displayed_user_id())){
			//Ensure if user has a member type assigned
			$layout = new WP_Query(apply_filters('vibebp_public_profile_layout_query',array(
				'post_type'=>'member-profile',
				'post_name'=>bp_get_member_type(bp_displayed_user_id()),
				'posts_per_page'=>1,
				'meta_query'=>array(
					'relation'=>'AND',
					array(
						'key'=>'member_type',
						'compare'=>'NOT EXISTS'
					)
				)
			)));
		}

		if ( !$layout || !$layout->have_posts() ){

			$layout = new WP_Query(array(
				'post_type'=>'member-profile',
				'post_name'=>bp_get_member_type(bp_displayed_user_id()),
				'posts_per_page'=>1,
				'meta_query'=>array(
					'relation'=>'AND',
					array(
						'key'=>'default_member-profile',
						'compare'=>'=',
						'value'=>1
					)
				)
			));
		}

		if ( !$layout->have_posts() ){
			$layout = new WP_Query(array(
				'post_type'=>'member-profile',
				'orderby'=>'date',
				'order'=>'ASC',
				'posts_per_page'=>1,
				'meta_query'=>array(
					'relation'=>'AND',
					array(
						'key'=>'member_type',
						'compare'=>'NOT EXISTS'
					)
				)	
			));
		}
		
		
		if ( $layout->have_posts() ) :
			
			
			while ( $layout->have_posts() ) :
				$layout->the_post();
				the_content();
				if(class_exists('\Elementor\Frontend')){
					
				 	$elementorFrontend = new \Elementor\Frontend();
                    $elementorFrontend->enqueue_scripts();
                    $elementorFrontend->enqueue_styles();
                }
				break;
			endwhile;
		
			
		endif;
		
	}

	function default_marker(){
		$this->posts = apply_filters('vibebp_layouts',array(
			'member-profile','member-card','group-layout','group-card'
		));
		foreach($this->posts as $post){
			add_filter('manage_'.$post.'_posts_columns', array($this,'default_column'));
			add_action('manage_'.$post.'_posts_custom_column', array($this,'default_mark'), 10, 2);
			add_action('admin_enqueue_scripts',array($this,'print_default_script'),10,1);
			
		}
	}

	function default_column($defaults){
		$defaults['default_col'] = __('Default','vibebp');
		return $defaults;
	}

	function default_mark($column_name, $post_id){

		if ($column_name == 'default_col') {
		    $default = get_post_meta($post_id,'default_'.$_GET['post_type'],true);
		    if (!empty($default)) {
		        echo '<span class="dashicons dashicons-star-filled" data-id="'.$post_id.'"></span>';
		    }else{
		    	echo '<span class="dashicons dashicons-star-empty" data-id="'.$post_id.'"></span>';
		    }
		}
	}

	function print_default_script($hook){
		if($hook == 'edit.php' && isset($_GET['post_type']) && in_array(esc_attr($_GET['post_type']),$this->posts)){

			add_action('admin_footer',function(){
				//Only admin can manage options
				if(!current_user_can('manage_options'))
					return;

			?>
			<style>.dashicons.dashicons-star-empty:hover{text-shadow:0 1px 5px rgba(0,0,0,0.5);}</style>
			<script>
				document.addEventListener('DOMContentLoaded',function(){
					document.querySelectorAll('td.column-default_col').forEach(function(e){
						e.addEventListener('click',function(event){

							event.preventDefault();
							
							var ddefault = 0;
							if(document.querySelector('td.column-default_col span.dashicons-star-filled')){
								document.querySelector('td.column-default_col span.dashicons-star-filled').classList.add('dashicons-star-empty');
								document.querySelector('td.column-default_col span.dashicons-star-filled').classList.remove('dashicons-star-filled');
							}
							
							if(e.querySelector('span').classList.contains('dashicons-star-empty')){
								ddefault = 1;
								e.querySelector('span').classList.remove('dashicons-star-empty');
								e.querySelector('span').classList.add('dashicons-star-filled');
							}else{
								ddefault = 0;
								e.querySelector('span').classList.remove('dashicons-star-filled');
								e.querySelector('span').classList.add('dashicons-star-empty');
							}


							const request = new XMLHttpRequest();

							request.addEventListener('load', function () {
							  if (this.readyState === 4 && this.status === 200) {
							    console.log(this.responseText);
							  }
							});

							request.open('POST', ajaxurl, true);
							request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
							request.send('action=default_post&id='+e.querySelector('span').getAttribute('data-id')+'&default='+ddefault);
						});
						
					});
				});
			</script>
			<?php
			});
		}
	}

	function mark_default(){
		
		if ( !current_user_can('manage_options') || !is_numeric($_POST['id'])){
	        _e('Security check Failed. Contact Administrator.','vibebp');
	        die();
	    }

	    global $wpdb;
	    $post_type = get_post_type(intval($_POST['id']));
	    if(empty($post_type)){
	    	_e('Security check Failed. Contact Administrator.','wplms');
	        die();
	    }
	    $post_id = $wpdb->get_var($wpdb->prepare("
	    	SELECT post_id 
	    	from $wpdb->postmeta as m 
	    	LEFT JOIN $wpdb->posts as p 
	    	ON p.id = m.post_id 
	    	WHERE m.meta_key=%s
	    	AND p.post_type=%s 
	    	",'default_'.$post_type,$post_type));
	    if(!empty($post_id)){
	    	delete_post_meta($post_id,'default_'.$post_type);	
	    }
	    
	    update_post_meta(intval($_POST['id']),'default_'.$post_type,intval($_POST['default']));

	    do_action('update_default_post',instval($_POST['id']),intval($_POST['default']));
	    die();
	}

	function layouts_template($single) {


        global $post;
 
        if ( in_array($post->post_type,apply_filters('vibebp_elementor_layout_template_post_types',array('member-profile','member-card','group-layout','group-card')))) {
        	if(file_exists(get_stylesheet_directory().'/templates/vibebp.php')){
        		return get_stylesheet_directory().'/templates/vibebp.php';
        	}


        	if(file_exists(get_template_directory().'/templates/vibebp.php')){
        		
        		return get_template_directory().'/templates/vibebp.php';
        	}
            if ( file_exists( plugin_dir_path( __FILE__ ).'/templates/vibebp.php' ) ) {
                return plugin_dir_path( __FILE__ ).'/templates/vibebp.php';
            }
        }

        return $single;

    }

	function post_types(){

		/* register Post types */

		register_post_type( 'member-profile',
			array(
				'labels' => array(
					'name' => __('Member Profiles','vibebp'),
					'menu_name' => __('Member Profiles','vibebp'),
					'singular_name' => __('Profile Layout','vibebp'),
					'add_new_item' => __('Add New Profile Layout','vibebp'),
					'all_items' => __('Member Profiles','vibebp')
				),
				'public' => true,
				'show_in_rest' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'capability_type' => 'page',
				'exclude_from_search'=>true,
	            'has_archive' => false,
				'show_in_menu' => 'vibebp',
				'show_in_admin_bar' => false,
				'show_in_nav_menus' => false,
				'supports' => array( 'title','editor','custom-fields'),
				'hierarchical' => false,
			)
		);

		register_post_type( 'member-card',
			array(
				'labels' => array(
					'name' => __('Member Cards','vibebp'),
					'menu_name' => __('Member Card','vibebp'),
					'singular_name' => __('Profile Card','vibebp'),
					'add_new_item' => __('Add New Profile Card','vibebp'),
					'all_items' => __('Member Card','vibebp')
				),
				'public' => true,
				'show_in_rest' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'capability_type' => 'page',
				'exclude_from_search'=>true,
	            'has_archive' => false,
				'show_in_menu' => 'vibebp',
				'show_in_admin_bar' => false,
				'show_in_nav_menus' => false,
				'menu_position'=>100,
				'supports' => array( 'title','editor','custom-fields'),
				'hierarchical' => false,
			)
		);

		if(function_exists('bp_is_active') && bp_is_active('groups')){
			register_post_type( 'group-layout',
				array(
					'labels' => array(
						'name' => __('Group Layouts','vibebp'),
						'menu_name' => __('Group Layouts','vibebp'),
						'singular_name' => __('Group Layout','vibebp'),
						'add_new_item' => __('Add New Group Layout','vibebp'),
						'all_items' => __('Group Layouts','vibebp')
					),
					'public' => true,
					'show_in_rest' => true,
					'publicly_queryable' => true,
					'show_ui' => true,
					'capability_type' => 'page',
					'exclude_from_search'=>true,
		            'has_archive' => false,
					'show_in_menu' => 'vibebp',
					'show_in_admin_bar' => false,
					'show_in_nav_menus' => false,
					'supports' => array( 'title','editor','custom-fields'),
					'hierarchical' => false,
				)
			);

			register_post_type( 'group-card',
				array(
					'labels' => array(
						'name' => __('Group Card','vibebp'),
						'menu_name' => __('Group Card','vibebp'),
						'singular_name' => __('Group Card','vibebp'),
						'add_new_item' => __('Add New Group Card','vibebp'),
						'all_items' => __('Group Card','vibebp')
					),
					'public' => true,
					'show_in_rest' => true,
					'publicly_queryable' => true,
					'show_ui' => true,
					'capability_type' => 'page',
					'exclude_from_search'=>true,
		            'has_archive' => false,
					'show_in_menu' => 'vibebp',
					'show_in_admin_bar' => false,
					'show_in_nav_menus' => false,
					'supports' => array( 'title','editor','custom-fields'),
					'hierarchical' => false,
				)
			);
		}


		register_post_type( 'tour',
			array(
				'labels' => array(
					'name' => __('Tours','vibebp'),
					'menu_name' => __('Tours','vibebp'),
					'singular_name' => __('Tour','vibebp'),
					'add_new_item' => __('Add New Tour','vibebp'),
					'all_items' => __('All Tours','vibebp')
				),
				'public' => false,
				'show_in_rest' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'capability_type' => 'page',
				'exclude_from_search'=>true,
	            'has_archive' => false,
				'show_in_menu' => 'vibebp',
				'show_in_admin_bar' => false,
				'show_in_nav_menus' => false,
				'supports' => array( 'title','editor','custom-fields'),
				'hierarchical' => false,
			)
		);
		flush_rewrite_rules();

		add_action( 'add_meta_boxes', array($this,'tour_metabox') );
		add_action( 'save_post',array($this,'save_tour_metabox'), 10, 1 );
	}

	function tour_metabox(){

		add_meta_box( 'tour-box-id', __( 'Tour Component', 'vibebp' ),array($this,'show_tour_component'), 'tour' );
	}
	
	function show_tour_component(){
		global $bp;
		if(!empty($bp->loaded_components)){
			$saved_component = get_post_meta(get_the_ID(),'tour_component',true);
			echo '<div style="padding:1rem; background:#f6f6f6;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;">
			<div><label>'.__('Select Tour component','vibebp').'</label>';
			echo '<select name="tour_component"><option value="dashboard" '.($saved_component == 'dashboard'?'selected':'').'>'.__('Dashboard','vibebp').'</option>';
			
			
			foreach((Array)$bp->loaded_components as $component){
				echo '<option value="'.$component.'" '.($saved_component == $component?'selected':'').'>'.$component.'</option>';
			}

			if(function_exists('wc_get_account_menu_items')){
				echo '<option value="shop" '.($saved_component == 'shop'?'selected':'').'>'.__('Woo-Commerce Shop','vibebp').'</option>';
			}
			echo '</select></div>';

			$tour_role = get_post_meta(get_the_ID(),'tour_role',true);

			echo '<div><label>'.__('Select User Type','vibebp').'</label>';
			echo '<select name="tour_role">';
			
			$member_type_objects = bp_get_member_types(array(),'objects');
			if(!empty($member_type_objects) && count($member_type_objects)){
				
				foreach($member_type_objects as $member_type=>$mt){
					$member_types[$member_type]=$member_type_objects[ $member_type ]->labels['singular_name'];
				}
				$member_types = array_merge(array(''=>__('All','vibebp'),'read'=>__('Students','vibebp'),'edit_posts'=>__('Instructors','vibebp')),$member_types);
			}
			
			foreach($member_types as $member_type=>$label){
				echo '<option value="'.$member_type.'" '.($tour_role == $member_type?'selected':'').'>'.$label.'</option>';
			}

			echo '</select></div></div>';
		}

		?>
		<div>
			<h3><?php _e('Setup tour steps','vibebp')?></h3>
			<button id="add_new_tour_step" class="button-primary"><?php _e('Add new step','vibebp'); ?></button>
			<div id="tour_steps">
				<?php
					$steps = get_post_meta(get_the_ID(),'steps',true);

					if(!empty($steps)){
						foreach($steps['selector'] as $i => $step){
							echo '<div><span class="remove_step"></span>';
							echo '<input type="text" name="step[selector][]" placeholder="'.__('Selector','vibebp').'" value="'.$step.'">
							<input type="text" name="step[title][]" placeholder="'.__('Step title','vibebp').'" value="'.$steps['title'][$i].'">
							<select name="step[action][]"><option>'.__('view','vibebp').'</option>
							<option value="click" '.($steps['action'][$i] == 'click'?'selected':'').'>'.__('On Click','vibebp').'</option>
							<option value="change" '.($steps['action'][$i] == 'change'?'selected':'').'>'.__('On Change','vibebp').'</option>
							<option '.($steps['action'][$i] == 'drag'?'selected':'').' value="drag">'.__('On Drag','vibebp').'</option></select>
							<textarea placeholder="'.__('Step description','vibebp').'" name="step[description][]">'.$steps['description'][$i].'</textarea>';
							echo '</div>';
						}
					}
					 //wp_nonce_field( 'tour_step_nonce', 'tour_step_nonce' ); 
				?>
			</div>
		</div>
		<script>
			document.querySelector('#add_new_tour_step').addEventListener('click',function(){

				let newContent = document.createElement("div");

				let input = document.createElement('input');
			    input.type = "text";
			    input.name='step[selector][]';
			    input.placeholder = "Selector";
			    newContent.appendChild(input);

				input = document.createElement('input');
			    input.type = "text";
			    input.name='step[title][]';
			    input.placeholder = "Step title";
			    newContent.appendChild(input);


			    input = document.createElement('textarea');
			    input.placeholder = "Step description";
			    input.name='step[description][]';
			    newContent.appendChild(input);

			    input = document.createElement('span');
			    input.setAttribute('class','remove_step');
			    newContent.appendChild(input);

			    let select  = document.createElement('select');
			    select.name='step[action][]';
			    let option  = document.createElement('option');
			    option.value="";
			    option.innerHTML='View';
			    select.appendChild(option);

			    option  = document.createElement('option');
			    option.value="click";
			    option.innerHTML='On Click';
			    select.appendChild(option);

			    option  = document.createElement('option');
			    option.value="click";
			    option.innerHTML='On Drag';
			    select.appendChild(option);
			    newContent.appendChild(select);


				document.querySelector('#tour_steps').appendChild(newContent);

				document.querySelectorAll('.remove_step').forEach((el)=>{
					el.addEventListener('click',function(e){
						el.parentNode.parentNode.removeChild(el.parentNode);
					});
				});
			});
			document.querySelectorAll('.remove_step').forEach((el)=>{
				el.addEventListener('click',function(e){
					el.parentNode.parentNode.removeChild(el.parentNode);
				});
			});
		</script>
		<style>
		div#tour_steps > div {
		    display: flex;
		    flex-direction: column;
		    margin: 1rem 0;
		    border: 1px solid rgba(0,0,0,0.1);
		    padding: 0.75rem;position: relative;
		}
		div#tour_steps > div > * {
		    margin: 0.25rem;
		}
		div#tour_steps > div .remove_step {
		    color: red;
		    cursor: pointer;
		    position: absolute;
		    top: 0;
		    right: 0;
		    font-size: 1.4rem;
		    border-radius: 5px;
		}div#tour_steps > div .remove_step:before{
			content: '\f14f';
			font-family: 'dashicons';
		}

		div#tour_steps > div > * {
		    margin: 0.25rem;
		}

		div#tour_steps > div input, div#tour_steps > div >textarea {
		    border-radius: 0;
		    border-color: rgba(0,0,0,0.1);
		}
		div#tour_steps > div input, div#tour_steps > div >textarea {
		    border-radius: 0;
		    border-color: rgba(0,0,0,0.1);
		}
		</style>
		<?php
	}	

	function save_tour_metabox($post_id){


		if(isset($_POST['tour_component'])){
			update_post_meta($post_id,'tour_component',sanitize_textarea_field($_POST['tour_component']));
		}
		if(isset($_POST['tour_role'])){
			update_post_meta($post_id,'tour_role',sanitize_textarea_field($_POST['tour_role']));
		}

		if(isset($_POST['step'])){
			$steps = (array)$_POST['step'];
			if(!empty($steps)){
				if(!empty($steps['name'])){
					foreach($steps['name'] as $i=>$step){
						$steps['name'][$i] = sanitize_text_field($step);
						$steps['description'][$i] = sanitize_text_field($steps['description'][$i]);
						$steps['selector'][$i] = sanitize_text_field($steps['selector'][$i]);
					}
				}
			}
			update_post_meta($post_id,'steps',$steps);
		}
	}

}

VibeBP_PostTypes::init();