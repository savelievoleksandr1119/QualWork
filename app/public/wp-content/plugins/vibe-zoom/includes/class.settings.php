<?php
/**
 * Settings in Admin
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	vibe_zoom/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Vibe_Zoom_Settings{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Zoom_Settings();
        return self::$instance;
    }

	private function __construct(){
		
		
		add_action('admin_menu',array($this,'initialize'));
		
		add_action('admin_enqueue_scripts',array($this,'admin_scripts'));

		add_action("wp_ajax_create_multi_zoom_credential_save", array($this,'create_multi_zoom_credential_save'));


	}

	function initialize(){
		if(class_exists('VibeBP_Settings')){
			add_filter('vibebp_settings_tabs',function($tabs){
				$tabs['vibe_zoom_function']=__('Zoom ','vibe-zoom');
				return $tabs;
			});
		}else{
			add_options_page(__('Zoom ','vibe_zoom'),__('Zoom ','vibe-zoom'),'manage_options','vibe_zoom',array($this,'settings'),1011);	
		}
		
	}


	function get_settings(){
		$settings = array(
			array(
				'label' => __('Zoom API Key','vibe-zoom'),
				'name' => 'vibe_zoom_api_key',
				'type' => 'text',
				'default'=> '',
				'desc' => __('Put your zoom JWT app api key OR new Server to server Ouath client key ','vibe-zoom').' <a href="https://wplms.io/support/knowledge-base/vibe-zoom-integration/" target="_blank">help</a>',
			),
			array(
				'label' => __('Zoom Secret Key','vibe-zoom'),
				'name' => 'vibe_zoom_secret_key',
				'type' => 'text',
				'default'=> '',
				'desc' => __('Put your zoom JWT app secret key OR new Server to server Ouath client secret ','vibe-zoom').' <a href="https://wplms.io/support/knowledge-base/vibe-zoom-integration/" target="_blank">help</a>',
			),
			array(
				'label' => __('Account id','vibe-zoom'),
				'name' => 'account_id',
				'type' => 'text',
				'default'=> '',
				'desc' => __('Put your zoom account id here (Requires in new Outh method to make api calls to zoom)','vibe-zoom').' <a href="https://wplms.io/support/knowledge-base/vibe-zoom-integration/" target="_blank">help</a>',
			),
			
			array(
				'label' => __('Enable meeting reminder','vibe-zoom'),
				'name' => 'vibe_zoom_enable_reminder',
				'type' => 'checkbox',
				'default'=> 0,
			),
			array(
				'label' => __('Meeting reminder time','vibe-zoom'),
				'name' => 'vibe_zoom_reminder_time',
				'type' => 'select',
				'options' => zoom_reminder_options(),
				'default' => '3600',
			)
		);

		return apply_filters('vibe_zoom_settings',$settings);	
	}



	function settings(){
		

        
		if(!empty($_GET['tab']) && $_GET['tab'] == 'vibe_zoom_function' && empty($this->settings)){
			$this->settings=get_option('vibe_zoom_settings');
        }
		$this->save();
		$settings = $this->get_settings();
		
			
		echo '<form method="post">';
		wp_nonce_field('vibe_zoom_settings');   
		echo '<table class="form-table">
				<tbody>';

		
		
		$settings = $this->get_settings();
		$this->generate_form($settings);

		echo '<tr valign="top"><th colspan="2"><input type="submit" name="save_vibe_zoom_settings" class="button button-primary" value="'.__('Save Settings','vibe-zoom').'" /></th>';
		echo '</tbody></table></form>';


		            
		//button show and form field append
		echo '<form method="post" style="margin-top: 50px;">';
		echo '<div class="zoom_notice">';
		echo '<p>'.__('Add multiple zoom credential so user can select at the time of create meeting.', 'vibe-zoom').'</p>';
		echo '</div>';
		wp_nonce_field('create_multi_zoom_credential_nonce','create_multi_zoom_credential_nonce'); 
		echo '<div class="create_multi_zoom_credential_container"></div>';
		echo '<table class="form-table"><tbody>';
		echo '<tr valign="top">';
		echo '<th scope="row" class="titledesc"><label>'.__('Add New', 'vibe-zoom').'</label></th>';
		echo '<td class="forminp"><span class="button button-primary create_multi_zoom_credential">'.__('+', 'vibe-zoom').'</span>';
		echo '</tr>';
		echo '<tr valign="top">';
		echo '<th scope="row" class="titledesc"><span class="button button-primary create_multi_zoom_credential_save">'.__('Save', 'vibe-zoom').'</span></label></th>';
		echo '</tr>';
		echo '</tbody></table>';
		echo '</form>';
		$multi_zoom_credential = get_option('multi_zoom_credential');

		$remove_str =__('Note: Do not remove credential if you have used to create meeting else edit meeting will not work for same meeting only.', 'vibe-zoom');

		?>	
			<style>
				.zoom_notice{
					border: 1px solid var(--border);
					padding: 4px;
					margin-bottom: 5px;
					font-weight: 700;
				}
			</style>

			<script type="text/javascript">
				jQuery(document).ready(function($){
					let wrapper = document.querySelector("div.create_multi_zoom_credential_container");

					// CREATING FIELD FROM DB DATA
					var multi_zoom_credential = <?php echo json_encode($multi_zoom_credential);	 ?>;
					if(!multi_zoom_credential || !Array.isArray(multi_zoom_credential)){
						multi_zoom_credential = [];
					}

					
					//dispatching event to rewrite element
					document.addEventListener('create_multi_zoom_credential_container_event', function (e) {
						wrapper.innerHTML = '';
						if(e.detail.hasOwnProperty('multi_zoom_credential')){
							let values = e.detail.multi_zoom_credential;
							if(Array.isArray(values) && values.length){
								values.map((value,index)=>{
									append_wrapper(value,index);
								})
							}
						}
					}, false);

					function dispatch_event_to_rerender(values){
						var event = new CustomEvent( "create_multi_zoom_credential_container_event", 
							{ detail: { multi_zoom_credential : values } 
						});
						document.dispatchEvent(event)
					}
					
					//removing value and rerndering
					function remove_value(index){
						let str = <?php echo json_encode($remove_str);	 ?>;
						if(confirm(str)){
							let temp = [...multi_zoom_credential];
							temp.splice(index,1);
							multi_zoom_credential = [...temp]
							dispatch_event_to_rerender(multi_zoom_credential);
						}
					}

					function append_wrapper(value,index){
						let fields = [
							{'label' : '<?php echo __('Title','vibe-zoom') ?>' , key:'title' ,value:value['title']},
							{'label' : '<?php echo __('API Key','vibe-zoom') ?>' , key:'api_key' ,value:value['api_key']},
							{'label' : '<?php echo __('Secret Key','vibe-zoom') ?>', key: 'api_secret' ,value:value['api_secret']},
							{'label' : '<?php echo __('Account id','vibe-zoom') ?>', key: 'account_id' ,value:value['account_id']}
						];
						let element = '<tr valign="top">';
						fields.map((field)=>{
							let rand = Math.floor(Math.random() * 10000) + 1;
							element += '<th scope="row" class="titledesc"><label>'+field.label+'</label></th>';
							element += '<td class="forminp"><input type="text" data-index='+index+' data-key='+field.key+' name="'+field.key+'" value="'+field.value+'" />';
							element +=  '</td>';
						})
						element +=  '<th scope="row" class="removeel titledesc button button-primary" data-index='+index+'><label>x</label></th>';
						element +='</tr>';
						jQuery(wrapper).append(element); //add input box
					}

					//create new object in array
					jQuery('.create_multi_zoom_credential').on('click', function(event) {
						event.preventDefault();
						let key = Math.random().toString(36).substr(2, 10);
						let value = {title:'',api_key:'',api_secret:'',key:key,account_id:''};
						multi_zoom_credential.push(value);
						dispatch_event_to_rerender(multi_zoom_credential)
					});

					// Remove parent of 'remove' link when link is clicked.
					jQuery('.create_multi_zoom_credential_container').on('click', 'tr>th.removeel', function(e) {
						event.preventDefault();
						let index = jQuery(this).attr("data-index");
						remove_value(index);
					});

					//onchange change the value
					jQuery('.create_multi_zoom_credential_container').on('keyup', 'input', function(e) {
						let index = jQuery(this).attr("data-index");
						let key = jQuery(this).attr("data-key");
						multi_zoom_credential[index][key] = e.target.value;
					});
					
					//save setting
					jQuery('.create_multi_zoom_credential_save').on('click', function(event) {
						let $this = jQuery(this);
						event.preventDefault();
						if(!Array.isArray(multi_zoom_credential)){
							multi_zoom_credential = [];
						}
						$this.addClass('disabled');
						let text = $this.text();
						$this.text(".....");
						$.ajax({
							type: "POST",
							url: ajaxurl,
							data: { 
									action: 'create_multi_zoom_credential_save',
									security: jQuery('#create_multi_zoom_credential_nonce').val(),
									value: multi_zoom_credential
								},
							cache: false,
							success: function (html) {
								$this.text(html);
								$this.removeClass('disabled');
								setTimeout(() => {
									$this.text(text);
								}, 2000);
							}
						});
					});

					dispatch_event_to_rerender(multi_zoom_credential);
				});
			</script>
		<?php
			
	}

	function create_multi_zoom_credential_save(){
		if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_multi_zoom_credential_nonce') ){
			echo __('Security check Failed. Contact Administrator !','vibe-zoom');
		}
		$new_value = array();
		if(isset($_POST['value'])){
			if(is_array($_POST['value'])){
				$new_value = $_POST['value'];
			}
		}
		update_option('multi_zoom_credential',$new_value);
		echo __('Saved','vibe-zoom');
		die();
	}

	function admin_scripts($hook){

		
		if($hook != 'settings_page_vibe_zoom' || empty($_GET['tab']) || (!empty($_GET['tab']) && $_GET['tab'] != 'general' ))
			return;

		wp_enqueue_script('jquery');
		wp_enqueue_media();

	}
	

	function generate_form($settings){
		
		foreach($settings as $setting ){

			if(!isset($this->settings[$setting['name']]) && !empty($setting['default']) ){
				if(is_array($setting['default'])){$setting['default']=json_encode($setting['default'],true);}
				$this->settings[$setting['name']]=$setting['default'];
			}
			
			echo '<tr valign="top">';
			$setting['desc'] = !empty($setting['desc'])?$setting['desc']:'';
			switch($setting['type']){
				case 'textarea':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><textarea name="'.$setting['name'].'" style="width: 50%; height: 240px;border:1px solid #DDD;">'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:'').'</textarea>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'select':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><select name="'.$setting['name'].'">';
					foreach($setting['options'] as $key=>$option){
						echo '<option value="'.$key.'" '.(isset($this->settings[$setting['name']])?selected($key,$this->settings[$setting['name']]):'').'>'.$option.'</option>';
					}
					echo '</select>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'checkbox':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><input type="checkbox" name="'.$setting['name'].'" '.(isset($this->settings[$setting['name']])?'CHECKED':'').' />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'number':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><input type="number" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:$setting['std']).'" />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'text':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><input type="text" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:'').'" />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'color':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><input type="text" class="color_picker" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:$setting['std']).'" style="background:'.$this->settings[$setting['name']].'"/>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'file':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>
					';
					?>
					<script>
	                	var media_uploader2;
	                	jQuery(document).ready(function($){
						jQuery('.upload_pdf_button').on('click', function( event ){
						  	console.log('#1');
						    var button = jQuery( this );
						    if ( media_uploader2 ) {
						      media_uploader2.open();
						      return;
						    }
						    // Create the media uploader.
						    media_uploader2 = wp.media.frames.media_uploader = wp.media({
						        title: button.data( 'uploader-title' ),
						        // Tell the modal to show only images.
						        library: {
						            type: '<?php $setting['file']?>',
						            query: false
						        },
						        button: {
						            text: button.data( 'uploader-button-text' ),
						        },
						        multiple: false
						    });

						    // Create a callback when the uploader is called
						    media_uploader2.on( 'select', function() {
						        var selection = media_uploader2.state().get('selection');
						            
						            selection.map( function( attachment ) {
						            attachment = attachment.toJSON();
						            var pdf_url='';
						            if(attachment &&  attachment.url !== undefined ){
						               pdf_url=attachment.url;
						            }else{
						            	alert('<?php echo _x("Unable to find url of selected file","","vibe_zoom");?>');
						            }
						            button.parent().find('.presentation_url').val(pdf_url);
						            button.parent().find('.presentation_name').html('<a href="'+pdf_url+'" class="dashicons dashicons-visibility" target="_blank"></a>');
						         });

						    });
						    // Open the uploader
						    media_uploader2.open();
						  });
					});
	                </script>
					<?php
					echo '<td class="forminp">
							<a class="upload_pdf_button button" data-uploader-button-text="'.__('Upload file','vibe-zoom').'" data-input-name="'.$setting['name'].'" >'.__('Upload','vibe-zoom').'</a>
							<input type="hidden" class="presentation_url" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:$setting['std']).'" /><span class="presentation_name">'.(isset($this->settings[$setting['name']])?'<a href="'.$this->settings[$setting['name']].'" class="dashicons dashicons-visibility" target="_blank"></a>':'').'</span>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'selectimg':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><div class="selectimg_options">';
					foreach($setting['options'] as $option){

						if(empty($this->settings[$setting['name']])){
							$this->settings[$setting['name']] = $option['value'];
						}
						echo '<div class="selectimg_option"><input type="radio" id="select_img'.$option['value'].'" name="'.$setting['name'].'" value="'.$option['value'].'" '.(($this->settings[$setting['name']] == $option['value'])?'checked':'').' /><label for="select_img'.$option['value'].'"><img src="'.$option['image'].'" /></label></div>';
					}
					
					echo '</div><span>'.$setting['desc'].'</span></td><style>.selectimg_options { display: grid; grid-template-columns: repeat(auto-fit,minmax(120px,1fr)); grid-gap: 15px; width: 100%; } .selectimg_option img { width: 100%; } .selectimg_option input { display: none; }.selectimg_option input[type="radio"]:checked+label img { box-shadow: 0 5px 20px rgba(0,0,0,0.4); border-radius: 5px;} </style>';
				break;
				
				case 'repeatable':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp">';

					echo '<input type="hidden" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:$setting['std']).'" />';


					echo '<a class="wa_meta_box_repeatable_add button button-primary button-large" href="#">'.__('Add More','vibe-zoom').'</a>
									<ul id="' . $field['id'] . '-repeatable" class="wa_meta_box_repeatable">';
	
							if ( !empty($this->settings[$setting['name']]) && is_Array($this->settings[$setting['name']])) {
								foreach( $this->settings[$setting['name']][$setting['multiple'][0]] as $k=>$key ) {
									echo '<li><span class="sort handle dashicons dashicons-sort"></span>';
									foreach($setting['multiple'] as $v){
										echo '<input type="text" name="' . $setting['name'] . '['.$v.'][]" id="' . $setting['name'] . '_'.$v.'" value="' . esc_attr( $this->settings[$setting['name']][$v][$k] ) . '" size="30" />';
									}
									echo '<a class="wa_meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
								}
							} 
								echo '<li class="hide"><span class="sort handle dashicons dashicons-sort"></span>';

								foreach($setting['multiple'] as $v){
									echo '<input type="text" rel-name="' . $setting['name'] . '['.$v.'][]" id="' . $setting['name'] . '_'.$v.'" placeholder="'.$v.'" value="" size="30" />';
								}
								echo '<a class="wa_meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
							
							echo '</ul>
								<span class="description">' . $field['desc'] . '</span>';

					?>


					<script>
						jQuery(document).ready(function($){
							jQuery('.wa_meta_box_repeatable_add').on('click', function(event) {
						        event.preventDefault();
								// clone
						        var repeatable = jQuery(this).siblings('.wa_meta_box_repeatable');
								var row = repeatable.find('li.hide');
						        var lastrow = repeatable.find('li:last-child');
								var clone = row.clone();
						        clone.removeClass('hide');
						        var inputname='';
								clone.find('input').each(function(){
									$(this).val('');
									inputname=$(this).attr('rel-name');
        							$(this).attr('name',inputname);
        						});

        						lastrow.after(clone);
        					});

        					jQuery('.wa_meta_box_repeatable_remove').live('click', function(){
		
						        var repeatable=jQuery(this).closest('.wa_meta_box_repeatable');
						        jQuery(this).closest('li').remove();
						        
								return false;
							});
						        
							jQuery('.wa_meta_box_repeatable').sortable({
								opacity: 0.6,
								revert: true,
								cursor: 'move',
								handle: '.handle',
						        update: function( event, ui ) {
						            
						        }
							});

						});
					</script><style>.hide{display:none;}.wa_meta_box_repeatable_remove{color:red;}</style>
					<?php
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'page':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp"><select name="'.$setting['name'].'"><option>'.__('Select a page','vibe-zoom').'</option>';

					$posts = new WP_Query(array('post_type'=>'page','posts_per_page'=>-1));
					if($posts->have_posts()){
						while($posts->have_posts()){
							$posts->the_post();
							echo '<option value="'.get_the_ID().'" '.(isset($this->settings[$setting['name']])?selected(get_the_ID(),$this->settings[$setting['name']]):'').'>'.get_the_title().'</option>';
						}
					}
					echo '</select>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'instructor_field':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp">';
					?>
					<a id="add_instructor_block" class="button"><?php _e('Add Searchable Instructor Field','vibe-zoom'); ?></a>
					<ul id="instructor_blocks">
					</ul>
					<script>
						jQuery(document).ready(function($){
							var instructor_blocks = [];

							var isValidJSON = true;
							try { JSON.parse($('#<?php echo $setting['name']; ?>').val()) } catch { isValidJSON = false }


							if(isValidJSON){
								instructor_blocks = JSON.parse($('#<?php echo $setting['name']; ?>').val());	
							}
							//console.log(instructor_blocks);

							var set_instructor_field = function(element){
								//console.log('covered');
								
								element.find('.set_field').on('change',function(){
									let i = $(this).val();
									//console.log('new field');
									if(i === 'new' ){
										$(this).after('<div class="newoption"><input type="text" placeholder="<?php _e('Value','vibe-zoom'); ?>" class="field_value"><input type="text" class="field_label " placeholder="<?php _e('Label','vibe-zoom'); ?>"><a class="add_newoption button"><?php _e('Add','vibe-zoom'); ?></a></div>');

										element.find('.add_newoption').on('click',function(){
											let label = $(this).parent().find('.field_label').val();
											let value = $(this).parent().find('.field_value').val();
											let i = $(this).parent().parent().attr('data-i');

											//console.log(instructor_blocks);
											if(!instructor_blocks[i]){
												instructor_blocks[i]=[];
											}
											if(!instructor_blocks[i].hasOwnProperty('options')){
												instructor_blocks[i]['options']=[];
											}else{
												if(!instructor_blocks[i]['options'] || instructor_blocks[i]['options'].length < 0){
													instructor_blocks[i]['options']=[];	
												}
												
											}
											
											instructor_blocks[i]['options'].push({'label':label,'value':value});
											$('#<?php echo $setting['name']; ?>').val(JSON.stringify(instructor_blocks));

											$(this).parent().parent().find('.set_field').append('<option>'+label+'</option>');
											$(this).parent().parent().find('.set_field').val('none');
											$(this).parent().remove();
										});
									}
									
								});


								
							}

							if(instructor_blocks.length){
								instructor_blocks.map(function(item,i){
									if(item && item.hasOwnProperty('label')){


										let block = '<li data-i="'+i+'"><span class="remove"></span><select class="set_field"><option value="none">'+item.label+'</option><option value="new"><?php _e('Add new option','vibe-zoom'); ?></option>';

										 item.options.map(function(it){
										 	if(it.hasOwnProperty('label')){
										 		block +='<option value="'+it.value+'">'+it.label+'</option>';
										 	}
										 	
										 });

										block +='</li>';
										jQuery('#instructor_blocks').append(block);

										set_instructor_field(jQuery('#instructor_blocks>li[data-i="'+i+'"]'));

										if(i === (instructor_blocks.length -1)){
											//console.log('all covered');
	    									$( "#instructor_blocks" ).sortable({
												stop : function(event,ui){ 
													let new_instructor_blocks = [];
													$('#instructor_blocks > li').each(function(index,item){
														let i = parseInt($(this).attr('data-i'));
														new_instructor_blocks.push(instructor_blocks[i]);
														$(this).attr('data-i',index);
													});
													instructor_blocks = new_instructor_blocks;
													$('#<?php echo $setting['name']; ?>').val(JSON.stringify(instructor_blocks));
										        }
											});
											$( "#instructor_blocks" ).disableSelection();
										}
									}
								});

								
							}

							jQuery('#instructor_blocks').on('click','.remove',function(){
								$(this).parent().remove();
								let new_instructor_blocks = [];
								$('#instructor_blocks > li').each(function(index,item){
									let i = parseInt($(this).attr('data-i'));
									new_instructor_blocks.push(instructor_blocks[i]);

									$(this).attr('data-i',index);
								});
								instructor_blocks = new_instructor_blocks;
								$('#<?php echo $setting['name']; ?>').val(JSON.stringify(instructor_blocks));
							});

							jQuery('#add_instructor_block').on('click',function(){


								let appender = '<li data-i="'+instructor_blocks.length+'"><div style="display: grid; padding: 10px; text-align: center; grid-gap: 5px;"><input type="text" class="field_key" placeholder="<?php _e('Set Field key','vibe-zoom'); ?>"><input type="text" class="field_name" placeholder="<?php _e('Set Field label','vibe-zoom'); ?>"><a class="add_field_label button-primary"><?php _e('Add Field','vibe-zoom'); ?></a></div><span class="remove"></span></li>';

								jQuery('#instructor_blocks').append(appender);

								jQuery('.add_field_label').on('click',function(){

									if($(this).parent().find('input.field_name').val().length){
										let new_instructor_field_block = {'type':'select','label':$(this).parent().find('input.field_name').val(),'key':$(this).parent().find('input.field_key').val(),'options':[]};
										instructor_blocks.push(new_instructor_field_block);

										//console.log('blocks');
										$('#<?php echo $setting['name']; ?>').val(JSON.stringify(instructor_blocks));

										$(this).parent().after('<select class="set_field"><option value="none">'+$(this).parent().find('input.field_name').val()+'</option><option value="new"><?php _e('Add new option','vibe-zoom'); ?></option></select>');
										

										set_instructor_field($(this).parent().parent());
										$(this).parent().remove();
									}
									
							});
							
							$( "#instructor_blocks" ).sortable({
								stop : function(event,ui){ 
									let new_instructor_blocks = [];
									$('#instructor_blocks > li').each(function(index,item){
										let i = parseInt($(this).attr('data-i'));
										new_instructor_blocks.push(instructor_blocks[i]);
										$(this).attr('data-i',index);
									});
									instructor_blocks = new_instructor_blocks;
									$('#<?php echo $setting['name']; ?>').val(JSON.stringify(instructor_blocks));
						        }
							});
							$( "#instructor_blocks" ).disableSelection();
						});


						});
					</script>
					<style>
					#instructor_blocks{max-width:320px;}
					#instructor_blocks span.remove{    position: absolute;right: 0;top: 0;}#instructor_blocks span.remove:after {
					    content: "\f158";
					    color: red;
					    font-family: dashicons;
					    font-size: 16px;
					    float: right;
					    padding: 5px;
					}#instructor_blocks .newoption{display:grid;grid-template-columns:1fr 1fr 48px;padding:5px;grid-gap:5px;}#instructor_blocks .newoption input{width:100%;}
					ul#instructor_blocks li:before{
					    content:"\f156";font-family: dashicons;line-height:2;
					    padding:0px 5px;font-size:12px;opacity:0.6;
					}
					#instructor_blocks li{border: 1px solid rgba(0,0,0,0.2);background:#fff;position:relative;}
					</style>
					<?php
					echo	"<input type='hidden'  name='".$setting['name']."' id='".$setting['name']."' value='".(isset($this->settings[$setting['name']])?stripslashes($this->settings[$setting['name']]):json_encode($setting['std']))."' />";
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'directory_search':
					echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
					echo '<td class="forminp">';
					?>
					<a id="add_search_block" class="button"><?php _e('Add Search block','vibe-zoom'); ?></a>
					<ul id="search_blocks">
					</ul>
					<script>


						jQuery(document).ready(function($){
							var search_blocks = [];

							var isValidJSON = true;
							try { JSON.parse($('#<?php echo $setting['name']; ?>').val()) } catch { isValidJSON = false }

							

							if(isValidJSON){
								//console.log($('#<?php echo $setting['name']; ?>').val());
								search_blocks = JSON.parse($('#<?php echo $setting['name']; ?>').val());	
							}
							
							
							var all_possible_search_blocks = <?php 
							
							$filters = array(
								array(
									'type'=>'course',
									'label'=> __('Search Course','vibe-zoom'),
								),
								array(
									'type'=>'availability',
									'label'=> __('Select Times','vibe-zoom'),
								),
								array(
									'type'=>'course-cat',
									'label'=> __('Select Category','vibe-zoom'),
								),
								array(
									'type'=>'location',
									'label'=> __('Select Location','vibe-zoom'),
								),
								array(
									'type'=>'level',
									'label'=> __('Select Levels','vibe-zoom'),
								),
								array(
									'type'=>'text',
									'label'=> __('Select Text','vibe-zoom'),
								),
							);

							
							echo json_encode(apply_filters('vibe__search_filters',$filters));

							?>;
							<?php
							if(!empty($this->settings['appointments_instructor_fields'])) {
								echo 'var more_search_blocks = '.stripslashes($this->settings['appointments_instructor_fields']).';';
							}
							?>
							more_search_blocks.map(function(item){
							if(item){
								item['type'] = item.key;
								all_possible_search_blocks.push(item);}
							});
							if(search_blocks.length){
								search_blocks.map(function(item,i){
									jQuery('#search_blocks').append('<li data-i="'+i+'">'+item.label+'<span class="remove"></span></li>');
									if(i === (search_blocks.length -1)){
										$( "#search_blocks" ).sortable({
											stop : function(event,ui){ 
												let new_search_blocks = [];
												$('#search_blocks > li').each(function(index,item){
													let i = parseInt($(this).attr('data-i'));
													new_search_blocks.push(search_blocks[i]);
													$(this).attr('data-i',index);
												});
												search_blocks = new_search_blocks;
												$('#<?php echo $setting['name']; ?>').val(JSON.stringify(search_blocks));
									        }
										});
    									$( "#search_blocks" ).disableSelection();
									}
								});
							}
							jQuery('#search_blocks').on('click','.remove',function(){
								$(this).parent().remove();
								let new_search_blocks = [];
								$('#search_blocks > li').each(function(index,item){
									let i = parseInt($(this).attr('data-i'));
									new_search_blocks.push(search_blocks[i]);

									$(this).attr('data-i',index);
								});
								search_blocks = new_search_blocks;
								$('#<?php echo $setting['name']; ?>').val(JSON.stringify(search_blocks));
							});
							jQuery('#add_search_block').on('click',function(){

								let options ='<option><?php _e('Select search option','vibe-zoom'); ?></option>';
								all_possible_search_blocks.map(function(item,i){
									if(search_blocks.findIndex(function(element, index, array){return element.type === item.type; }) < 0){
										options+='<option value="'+i+'">'+item.label+'</option>';
									}
								});

								jQuery('#search_blocks').append('<li data-i="'+search_blocks.length+'"><select class="set_block">'+options+'</select><span class="remove"></span></li>');
								jQuery('.set_block').on('change',function(){
									let i = $(this).val();
									search_blocks.push(all_possible_search_blocks[i]);
									$('#<?php echo $setting['name']; ?>').val(JSON.stringify(search_blocks));
								});
								$( "#search_blocks" ).sortable({
									stop : function(event,ui){ 
										let new_search_blocks = [];
										$('#search_blocks > li').each(function(index,item){
											let i = parseInt($(this).attr('data-i'));
											new_search_blocks.push(search_blocks[i]);
											$(this).attr('data-i',index);
										});
										search_blocks = new_search_blocks;
										$('#<?php echo $setting['name']; ?>').val(JSON.stringify(search_blocks));
							        }
								});
								$( "#search_blocks" ).disableSelection();
							});

						});
					</script>
					<style>
					#search_blocks{max-width:240px;}#search_blocks span.remove:after {
					    content: "\f158";
					    color: red;
					    font-family: dashicons;
					    font-size: 16px;
					    float: right;
					    padding: 5px;
					}
					ul#search_blocks li:before{
					    content:"\f156";font-family: dashicons;line-height:2;
					    padding:0px 5px;font-size:12px;opacity:0.6;
					}
					#search_blocks li{border: 1px solid rgba(0,0,0,0.2);background:#fff;}
					</style>
					<?php
					echo	"<input type='hidden'  name='".$setting['name']."' id='".$setting['name']."' value='".(isset($this->settings[$setting['name']])?stripslashes($this->settings[$setting['name']]):$setting['std'])."' />";
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
			}
		}	
		?>
		<script>
		jQuery(document).ready(function($){
			$(".color_picker").each(function(){
			    var $this = $(this);
			    $this.css("background",$this.val());

			    $this.iris({
			        width: 200,
			        hide: true,
			        change: function(event, ui) {
			            $this.css( "background", ui.color.toString());
			        }
			    });
			});

			$(".color_picker").on("click",function(){
			    $(this).parent().find(".iris-picker").toggle(100);
			});
		});
		</script><style>.iris-picker{position:absolute;}</style>
		<?php
	}

	function save(){
		

		if(!isset($_POST['save_vibe_zoom_settings']))
			return;
		

		if ( !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'],'vibe_zoom_settings') ){
		     echo '<div class="error notice is-dismissible"><p>'.__('Security check Failed. Contact Administrator !','vibe-zoom').'</p></div>';
		}
		

		$settings = $this->get_settings();
		
		foreach($settings as $setting){
			if(isset($_POST[$setting['name']])){
				$this->settings[$setting['name']] = $_POST[$setting['name']];
			}else if($setting['type'] == 'checkbox' && isset($this->settings[$setting['name']])){
				unset($this->settings[$setting['name']]);
			}
		}

		$user_id = get_current_user_id();
		if(!empty($_POST['vibe_zoom_enable_reminder']) && !empty($user_id)){
			$init = Vibe_Zoom_Init::init();
			$init->new_bp_core_email_register($user_id); //new email insert	
		}

		update_option('vibe_zoom_settings',$this->settings);
		echo '<div class="updated notice is-dismissible"><p>'.__('Settings Saved.','vibe-zoom').'</p></div>';
	}
	
	function help(){

		$myFile = plugin_dir_path(__FILE__).'/help.html';
        if (file_exists($myFile)) {
          $fh = fopen($myFile, 'r');
        }
        $contents = fread($fh,filesize($myFile));
        print_r($contents);
        fclose($fh); 
	}


	function create_test_meeting_ve(){


		if(!current_user_can('manage_options') || wp_verify_nonce('security',$_POST['security'])){
			echo '<strong>You do not have permissions</strong>';
			die();
		}

		$_api = VibeZoom__API::init();

		$meetingID = wp_generate_password(12,false,false);
		$object = $_api->create_meeting(array(
			'name' => 'Sample',
    		'meetingID'=>$meetingID,//AppointmentID
    		'attendeePW'=> '1234',
    		'moderatorPW'=>'2345',
    		'welcome'=>(!empty($this->settings['_welcome_message'])?$this->settings['_welcome_message']:''),
    		'maxParticipants'=>'2',
    		'record'=>false,
    		'duration'=>'', //(minutes)
    		'meta'=>'',//appointmentID
    		'allowStartStopRecording'=>false,
    		'webcamsOnlyForModerator'=>'',//boolean
    		'logo'=>(!empty($this->settings['_logo'])?$this->settings['_logo']:''),//url
    		//'logo'=>'',
    		'bannerText'=>'',
    		'bannerColor'=>'',
    		'copyright'=>(!empty($this->settings['_copyright_text'])?$this->settings['_copyright_text']:''),
    		//'copyright'=>''
    		)
		);
	
		$meeting_arr = $_api->object2array($object);


		$userName = 'Sample'; 
				
		echo '&nbsp;&nbsp;&nbsp;<a target="_blank" class="button button-primary"data-id="'.$meeting_arr['meetingID'].'" href="'.$_api->getJoinURL( $meetingID, $userName, '2345').'">'._x('Join Sample meeting','','vibe-zoom').'</a>';
		
		die();
		
	}
}

Vibe_Zoom_Settings::init();


function vibe_zoom_function(){
	$init = Vibe_Zoom_Settings::init();
	$init->settings();
}
