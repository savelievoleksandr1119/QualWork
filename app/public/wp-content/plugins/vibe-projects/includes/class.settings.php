<?php
/**
 * Admin Menu - VibeProjects_Menu
 *
 * @class       VibeProjects_Menu
 * @author      VibeThemes
 * @team    Admin
 * @package     VibeProjects_Menu
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class VibeProjects_Settings{
	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeProjects_Settings();
        return self::$instance;
    }
	private function __construct(){
		
		add_action( 'vibebp_settings_tabs',array($this,'projects_Tab')); 
		add_filter( 'vibebp_save_tab',array($this,'save_tab'));
		add_filter('vibebp_settings_type',array($this,'project_fields'),10,2);

		add_action('admin_enqueue_scripts',[$this,'enqueue_scripts']);
	}
	function projects_Tab($tabs){
		$tabs['vibe_projects'] = __('Projects','vibe-projects');
		return $tabs;
	}
	function save_tab($tab){
		if(empty($_POST['tab'])){
			$tab = 'projects';
		}
		return $tab;
	}

	function enqueue_scripts($hook){

		if($hook == 'vibe-bp_page_vibebp_settings' && !empty($_GET['tab']) && $_GET['tab'] == 'vibe_projects'){
			wp_enqueue_script('slimselect',plugins_url('../assets/js/slimselect.min.js',__FILE__));
			wp_enqueue_style('slimselect',plugins_url('../assets/css/slimselect.css',__FILE__));
		}
	}
	function show_settings(){
		echo '<h3>'.__('Projects General Settings','vibe-projects').'</h3>';

		$template_array = apply_filters('vibebp_project_general_settings_tabs',array(
			'projects'=> __('Projects','vibe-projects'),
			'boards'=> __('Boards','vibe-projects'),
			'cards'=> __('Cards','vibe-projects'),
		));


		echo '<ul class="subsubsub">';

		foreach($template_array as $k=>$value){
			if(empty($_GET['sub']) && empty($current)){
				$current = 'projects';
			}else if(!empty($_GET['sub']) && empty($current)){
				$current = esc_attr($_GET['sub']);
			}
			echo '<li><a href="?page='.VIBE_BP_SETTINGS.'&tab=vibe_projects&sub='.$k.'" '.(($k == $current)?'class="current"':'').'>'.$value.'</a>  &#124; </li>';
		}
		echo '</ul><div class="clear"><hr/>';

		$vibebp_settings = VibeBP_Settings::init();
		$sub = 'projects';
		if(!empty($_GET['sub'])){$sub=esc_attr($_GET['sub']);}
		$settings = apply_filters('vibe_projects_get_settings_array',$this->get_selected_tab_settings_array($sub),$sub);


		$vibebp_settings->vibebp_settings_generate_form('vibe_projects',$settings,$sub);
		
	}
	function get_selected_tab_settings_array($tab){
		

		$types = bp_get_member_types(array(),'objects');
        $mtypes = [''=>__('No default member type','vibebp')];
        if(!empty($types)){
            foreach($types as $type => $labels){
                $mtypes[$type]=$labels->labels['name'];
            }
        }
		$settings = array();
		switch($tab){

			case 'boards':
				$settings = apply_filters('vibebp_projects_boards_settings',array(
					array(
						'label'=>__('Task Boards/Lists','vibe-projects'),
						'type'=> 'heading',
						'name'=>'board_title'
					),
					array(
						'label' => __('Capabilities'),
						'type' => 'project_field_repeatable',
						'name' => 'board_capability',
						'fields' => [
							[
								'type'=>'select',
								'key'=>'key',
								'label'=>__('Member Type','vibe-projects'),
								'options'=>$mtypes
							],
							[
								'type'=>'multiselect',
								'key'=>'capabilities',
								'label'=>__('Select Capabilities','vibe-projects'),
								'options'=>[
									'create_board'=>__('Create Board','vibe-projects'),
									'create_board_list'=>__('Create List','vibe-projects'),
									'delete_board'=>__('Delete Board','vibe-projects'),
									'board_bulk_actions'=>__('Bulk Board actions','vibe-projects'),
									'board_automations'=>__('Board automations','vibe-projects'),
									'board_stats'=>__('Board Statistics','vibe-projects')
								]
							]
						],
						'desc' => __('Set Project Hierarcy Levels & Capabilities. You will be able to assign these heirarchy in project members tab.','vibe-projects')
					),
					array(
						'label' => __('Custom Board Statuses'),
						'type' => 'project_field_repeatable',
						'name' => 'board_status',
						'fields' => [
							[
								'type'=>'text',
								'key'=>'key',
								'label'=>__('Status slug [text, no space, no number , no special chars]','vibe-projects')
							],
							[
								'type'=>'text',
								'key'=>'label',
								'label'=>__('Status Label','vibe-projects')
							],
							[
								'type'=>'color',
								'key'=>'color',
								'label'=>__('Status Color','vibe-projects')
							],
						],
						'desc' =>  __('Set custom statuses for boards.','vibe-projects')
					)
				));
			break;
			case 'cards':
				$settings = apply_filters('vibebp_projects_cards_settings',array(
					array(
						'label'=>__('Cards or Tasks','vibe-projects' ),
						'type'=> 'heading',
						'name'=>'title',
					),
					array(
						'label' => __('Capabilities'),
						'type' => 'project_field_repeatable',
						'name' => 'card_capability',
						'fields' => [
							[
								'type'=>'select',
								'key'=>'key',
								'label'=>__('Member Type','vibe-projects'),
								'options'=>$mtypes
							],
							[
								'type'=>'multiselect',
								'key'=>'capabilities',
								'label'=>__('Select Capabilities','vibe-projects'),
								'options'=>[
									'view_card'=>__('View all Cards','vibe-projects'),
									'move_card'=>__('Move Cards','vibe-projects'),
									'create_card'=>__('Create Cards','vibe-projects'),
									'watch_card'=>__('Watch any Card','vibe-projects'),
									'change_status'=>__('Change Card Status','vibe-projects'),
									'complete_card'=>__('Complete Card','vibe-projects'),
									'delete_card'=>__('Delete Card','vibe-projects'),
									'add_card_label'=>__('Add Card Labels','vibe-projects'),
									'add_card_members'=>__('Add Card Members','vibe-projects'),
									'set_card_date'=>__('Set Dates','vibe-projects'),
									'set_workloads'=>__('Set Workloads','vibe-projects'),
									'add_card_checklists'=>__('Add Checklists','vibe-projects'),
									'finish_card_checklists'=>__('Complete Checklists','vibe-projects'),
									'add_card_attachments'=>__('Add Attachments','vibe-projects'),
								]
							]
						],
						'desc' => __('Set Card Capabilities.','vibe-projects')
					),
					array(
						'label' => __('Custom Card Fields'),
						'type' => 'project_field_repeatable',
						'name' => 'create_card_fields',
						'fields' => [
							[
								'type'=>'checkbox',
								'class'=>'min',
								'key'=>'preload',
								'label'=>__('PreLoad','vibe-projects')
							],
							[
								'type'=>'text',
								'key'=>'key',
								'label'=>__('Key [no spaces, no special chars]','vibe-projects')
							],
							[
								'type'=>'text',
								'key'=>'label',
								'label'=>__('Add Label','vibe-projects')
							],
							[
								'type'=>'select',
								'key'=>'type',
								'label'=>__('Type of Field','vibe-projects'),
								'options'=>[
									'number'=>__('Number','vibe-projects'),
									'text'=>__('Text','vibe-projects'),
									'checkbox'=>__('Checkbox','vibe-projects'),
									'select'=>__('Select','vibe-projects'),
									'date'=>__('Date','vibe-projects')
								]
							],
							[
								'type'=>'text',
								'key'=>'value',
								'label'=>__('Default Value, options a=>A|b=>B','vibe-projects')
							],
						],
						'desc' =>  __('Custom fields for cards creation','vibe-projects')
					),
					array(
						'label' => __('Custom Card Statuses'),
						'type' => 'project_field_repeatable',
						'name' => 'card_status',
						'fields' => [
							[
								'type'=>'text',
								'key'=>'key',
								'label'=>__('Status slug [text, no space, no number , no special chars]','vibe-projects')
							],
							[
								'type'=>'text',
								'key'=>'label',
								'label'=>__('Status Label','vibe-projects')
							],
							[
								'type'=>'color',
								'key'=>'color',
								'label'=>__('Status Color','vibe-projects')
							],
						],
						'desc' =>  __('Set custom statuses for Cards. Default Statuses : Active,Complete, Archived','vibe-projects')
					)
				));
			break;
			default:
				$tab='projects';

				$settings = apply_filters('vibebp_projects_general_settings',array(
					array(
						'label'=>__('Project Settings','vibebp' ),
						'type'=> 'heading',
						'name' => 'project_Settings_title',
					),
					array(
						'label' => __('Project Capabilities'),
						'type' => 'project_field_repeatable',
						'name' => 'project_capability',
						'fields' => [
							[
								'type'=>'select',
								'key'=>'key',
								'label'=>__('Member Type','vibe-projects'),
								'options'=>$mtypes
							],
							[
								'type'=>'multiselect',
								'key'=>'capabilities',
								'label'=>__('Select Capabilities','vibe-projects'),
								'options'=>[
									'create_project'=>__('Create Project','vibe-projects'),
									'update_project_status'=>__('Update project status','vibe-projects'),
									'create_project_milestones'=>__('Create Milestones','vibe-projects'),
									'complete_project'=>__('Complete Project','vibe-projects'),
									'complete_milestone'=>__('Complete Milestones','vibe-projects'),
									'delete_project_milestones'=>__('Delete Milestones','vibe-projects'),
									'delete_project'=>__('Delete Project','vibe-projects'),
									'view_project_activity'=>__('View project activity','vibe-projects'),
									'post_project_activity'=>__('Post project activity','vibe-projects'),
									'view_project_members'=>__('View project members','vibe-projects'),
									'add_project_members'=>__('Add project members','vibe-projects'),
									'add_project_notice'=>__('Add project notice','vibe-projects'),

									'view_project_boards'=>__('View Project Boards/Tasks','vibe-projects'),
							        'view_project_gantt'=>__('View Project Gantt charts','vibe-projects'),
							        'view_project_notes'=>__('view Project Notes','vibe-projects'),
							        'view_project_reports'=>__('view Project Reports','vibe-projects'),
							        'project_member_invite'=>__('Invite members to Project','vibe-projects'),
							        'add_project_member'=>__('Add Project member','vibe-projects'),
								]
							]
						],
						'desc' => __('Set Project Hierarcy Levels & Capabilities. You will be able to assign these heirarchy in project members tab.','vibe-projects')
					),
					array(
						'label' => __('Custom Project Fields'),
						'type' => 'project_field_repeatable',
						'name' => 'create_project_fields',
						'fields' => [
							[
								'type'=>'checkbox',
								'class'=>'min',
								'key'=>'preload',
								'label'=>__('PreLoad','vibe-projects')
							],
							[
								'type'=>'text',
								'key'=>'key',
								'label'=>__('Key [no spaces, no special chars]','vibe-projects')
							],
							[
								'type'=>'text',
								'key'=>'label',
								'label'=>__('Add Label','vibe-projects')
							],
							[
								'type'=>'select',
								'key'=>'type',
								'label'=>__('Type of Field','vibe-projects'),
								'options'=>[
									'number'=>__('Number','vibe-projects'),
									'text'=>__('Text','vibe-projects'),
									'checkbox'=>__('Checkbox','vibe-projects'),
									'select'=>__('Select','vibe-projects')
								]
							],
							[
								'type'=>'text',
								'key'=>'value',
								'label'=>__('Default Value','vibe-projects')
							],
						],
						'desc' =>  __('Custom fields for project creation','vibe-projects')
					),
					array(
						'label' => __('Custom Project Statuses'),
						'type' => 'project_field_repeatable',
						'name' => 'project_status',
						'fields' => [
							[
								'type'=>'text',
								'key'=>'key',
								'label'=>__('Status slug [text, no space, no number , no special chars]','vibe-projects')
							],
							[
								'type'=>'text',
								'key'=>'label',
								'label'=>__('Status Label','vibe-projects')
							],
							[
								'type'=>'color',
								'key'=>'color',
								'label'=>__('Status Color','vibe-projects')
							],
						],
						'desc' =>  __('Set custom statuses for projects. Default Statuses : Live, Draft (unsaved)','vibe-projects')
					)
				));
			break;
		}

		return $settings;
	}

	function project_fields($html,$setting){
		

		if($setting['type'] == 'project_field_repeatable'){

			
			echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
			$saved_fields = $setting['value'];
			
			?>
			<td class="forminp">
			<div class="project_field_repeatable_wrapper">
				<?php
				

				echo  '<ul class="project_field_repeatable_list_'.$setting['name'].'">';

				echo '<li class="project_field_repeatable_list_head">';
				foreach($setting['fields'] as $field){
					echo '<span '.(empty($field['class'])?'':'class="'.$field['class'].'"').'>'.(empty($field['label'])?'':$field['label']).'</span>';
				}
				echo '<span class="min"></span>';
				echo '</li>';

				if(!empty($saved_fields) && $setting['fields']){


					if(!empty($saved_fields['key'])){


						foreach($saved_fields['key'] as $i=>$saved_field){


							echo '<li '.(empty($field['class'])?'':$field['class']).'>';
								
								foreach($setting['fields'] as $field){
									

									switch($field['type']){
										case 'text':
											echo '<input type="text" name="'.$setting['name'].'['.$field['key'].'][]" value="'.(isset($saved_fields[$field['key']][$i])?$saved_fields[$field['key']][$i]:'').'" />';
										break;
										case 'color':
											echo '<input type="color" name="'.$setting['name'].'['.$field['key'].'][]" value="'.(isset($saved_fields[$field['key']][$i])?$saved_fields[$field['key']][$i]:'').'" />';
										break;
										case 'checkbox':
											echo '<input type="checkbox" name="'.$setting['name'].'['.$field['key'].'][]" value="1" '.(isset($saved_fields[$field['key']][$i])?'checked="checked"':'').' />';
										break;
										case 'select':
											echo '<select name="'.$setting['name'].'['.$field['key'].'][]">';
											
											
											foreach($field['options'] as $k=>$v){
												echo '<option value="'.$k.'" '.(($k==$saved_fields[$field['key']][$i])?'selected':'').'>'.$v.'</option>';
											}
											echo '</select>';
										break;
										case 'multiselect':
											echo '<select name="'.$setting['name'].'['.$field['key'].']['.$i.'][]" multiple>';
											foreach($field['options'] as $k=>$v){
												echo '<option value="'.$k.'" '.(is_array($saved_fields[$field['key']][$i]) && in_array($k,$saved_fields[$field['key']][$i])?'selected':'').'>'.$v.'</option>';
											}
											echo '</select>';
											
										break;
									}
								}
								echo '<span class="min dashicons dashicons-no-alt"></span></li>';
						}
					}
				}
				echo '</ul>';
				?>
				
			</div>
			<a id="add_new_project_field_button_<?php echo $setting['name']; ?>" class="button-primary"><?php _ex('Add New','button create field','vibe-projects'); ?></a>
			<script>

				document.querySelector('#add_new_project_field_button_<?php echo $setting['name']; ?>').addEventListener('click',function(e){
					e.preventDefault();
					let fields = <?php echo json_encode($setting['fields']); ?>;
					var div = document.createElement('div');
					fields.map((field)=>{

						let input='';
						switch(field.type){
							case 'text':
								
								input = document.createElement('input');
								input.type= 'text';
								input.placeholder = field.label;
								input.name= '<?php echo $setting['name']; ?>['+field.key+'][]';
								input.setAttribute('class','input-field');
								div.appendChild(input);
							break;
							case 'color':
								
								input = document.createElement('input');
								input.type= 'color';
								input.placeholder = field.label;
								input.name= '<?php echo $setting['name']; ?>['+field.key+'][]';
								input.setAttribute('class','input-field');
								div.appendChild(input);
							break;
						case 'checkbox':
								
								let checkboxdiv = document.createElement('div');
								let label = document.createElement('label');
								let attr = field.key+'_'+Math.floor(Math.random()*100);
								label.setAttribute('for',attr);
								label.innerHTML=field.label;
								checkboxdiv.appendChild(label);
								let cinput = document.createElement('input');
								cinput.type= 'checkbox';	
								cinput.setAttribute('id',attr);
								cinput.name= '<?php echo $setting['name']; ?>['+field.key+'][]';
								cinput.setAttribute('class','input-field');
								div.appendChild(cinput);
							break;
							case 'multiselect':

								let mselectdiv = document.createElement('div');

								let mlabel = document.createElement('label');
								let mattr = field.key+'_'+Math.floor(Math.random()*100);
								mlabel.setAttribute('for',mattr);
								mlabel.innerHTML=field.label;

								mselectdiv.appendChild(mlabel);
								
								let mselect = document.createElement('select');
								mselect.setAttribute('id',mattr);

								let index= 0;
								index= document.querySelector('<?php echo '.project_field_repeatable_list_'.$setting['name']; ?>').childNodes.length;
								mselect.setAttribute('multiple',true);
								mselect.name = '<?php echo $setting['name']; ?>['+field.key+']['+index+'][]';
								Object.keys(field.options).map(function(k){
									let option = document.createElement('option');
									option.value=k;
									option.innerHTML = field.options[k];
									mselect.appendChild(option);	
								});
								mselectdiv.appendChild(mselect);
								div.appendChild(mselectdiv);
							break;
							case 'select':

								let select = document.createElement('select');
								
								select.name = '<?php echo $setting['name']; ?>['+field.key+'][]';
								let option = document.createElement('option');
									option.value='';
									option.innerHTML = field.label;
									select.appendChild(option);
								Object.keys(field.options).map(function(k){
									let option = document.createElement('option');
									option.value=k;
									option.innerHTML = field.options[k];
									select.appendChild(option);	
								});

								div.appendChild(select);
							break;
						}


					});

					let span = document.createElement('span');
					span.setAttribute('class','dashicons dashicons-no-alt');
					div.appendChild(span);

					let list = document.querySelector('.project_field_repeatable_list_<?php echo $setting['name'];?>');
					if(list){
						list.appendChild(div);
						list.dispatchEvent(new Event('project_field_repeatable_loaded'));	
					}
					document.querySelectorAll('select[multiple]').forEach(function(el){
						if(!el.getAttribute('data-id')){
							new SlimSelect({
							  select: el
							});			
						}
					});
					
					
				});

					function remove_projects_fields(){
						let close = document.querySelectorAll('.dashicons-no-alt');

						if(close.length){
							close.forEach(function(el){
								el.addEventListener('click',function(e){
									e.preventDefault();
									el.parentNode.remove();	
								});
							});
						}
						
				}
				
				document.querySelector('.project_field_repeatable_list_<?php echo $setting['name']; ?>').addEventListener('project_field_repeatable_loaded',function(e){
					remove_projects_fields();
				});
				
				remove_projects_fields();	
				document.addEventListener('DOMContentLoaded',function(){
					document.querySelectorAll('select[multiple]').forEach(function(el){
						if(!el.getAttribute('data-id')){
							new SlimSelect({
							  select: el
							});			
						}
					});	
				});
				
			</script>

			<?php

			echo '<span>'.$setting['desc'].'</span>';
			?><style>.project_field_repeatable_wrapper >ul > * {display: flex;flex-wrap:wrap;align-items: center;gap: 5px;}.project_field_repeatable_wrapper >ul > * >div{max-width:20rem;}.project_field_repeatable_wrapper >ul > * > * {flex: 1;}.project_field_repeatable_wrapper >ul > * > *.min , .project_field_repeatable_wrapper input[type="checkbox"]{flex: 0 0 60px;}li.project_field_repeatable_list_head {border-bottom: 1px solid #ddd;padding: 0 0 10px;}</style>
			</td>
			<?php
			return 1;
		}

		return $html;
	}
}
VibeProjects_Settings::init();
function vibe_projects(){
	$settings = VibeProjects_Settings::init();
	$settings->show_settings();
}