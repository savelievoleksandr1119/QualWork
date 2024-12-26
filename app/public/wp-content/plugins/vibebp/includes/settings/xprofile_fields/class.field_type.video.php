<?php

if ( ! defined( 'ABSPATH' ) ) 
	exit;


class VibeBP_Field_Type_Video extends BP_XProfile_Field_Type {

	public function __construct() {

		parent::__construct();

		$this->name     = _x( 'Video', 'xprofile field type', 'vibebp' );
		$this->category = _x( 'VibeBP', 'xprofile field type category', 'vibebp' );

		$this->set_format( '/^.+$/', 'replace' );
		do_action( 'bp_xprofile_field_type_video', $this );
	}

	
	public function edit_field_html( array $raw_properties = array() ) {

        // reset user_id.
	    if ( isset( $raw_properties['user_id'] ) ) {
			unset( $raw_properties['user_id'] );
		}

		$html = $this->get_edit_field_html_elements( array_merge(
			array(
				'type'  => 'video',
				'value' => bp_get_the_profile_field_edit_value(),
                'class' => 'vibebp-video'
			),
			$raw_properties
		) );
		?>

        <legend id="<?php bp_the_profile_field_input_name(); ?>-1">
			<?php bp_the_profile_field_name(); ?>
			<?php bp_the_profile_field_required_label(); ?>
        </legend>

		<?php do_action( bp_get_the_profile_field_errors_action() ); ?>

        <input type="file" <?php echo $html; ?> >

		<?php if ( bp_get_the_profile_field_description() ) : ?>
            <p class="description"
               id="<?php bp_the_profile_field_input_name(); ?>-3"><?php bp_the_profile_field_description(); ?></p>
		<?php endif; ?>

		<script>
			(function() {  
				function addError(node,error){
					if(document.querySelector('#'+node)){
						const element = document.createElement("div");
						element.setAttribute('id','error_message_'+node); 
						element.innerHTML = error;
						element.classList.add('vbp_message');
						element.classList.add('error');
						element.classList.add('message');
						element.classList.add('notice');
						   
						document.querySelector('#'+node).parentNode.appendChild(element);
						setTimeout(function(){element.remove();},5000);
					}
				}

			    let upfield = '<?php echo 'field_'.bp_get_the_profile_field_id();?>'; 
			    let allowed_video_size = parseInt('<?php echo $this->get_default_selected_video_size(bp_get_the_profile_field_id());?>');
			    let allowed_file_types = <?php echo json_encode($this->get_default_selected_types(bp_get_the_profile_field_id()));?>;
			    let all_mimtypes = <?php echo json_encode(vibebp_getVideoMimeTypes());?>;
			    let allowed_mimes = [];
			    if(document.querySelector('#'+upfield)){
			    	document.querySelector('#'+upfield).addEventListener('change',function(event){
			    		if(typeof event.target.files!=='undefined' && event.target.files.length){
			    			let file = event.target.files[0];
			    			console.log(file);
			    			let size = file.size / 1024 / 1024;
			    			if(all_mimtypes){
				               allowed_file_types.map((type)=>{
				                    if(all_mimtypes.hasOwnProperty(type)){
				                        if(all_mimtypes[type].length){
				                            all_mimtypes[type].map((mime)=>{
				                                allowed_mimes.push(mime);
				                            });
				                        }
				                    }
				                });
				                
				            }
				           
				            if(allowed_mimes.indexOf(file.type) === -1){
				                addError(upfield,window.vibebp.translations.file_type_not_allowed);
				                
				                event.target.value='';
				            }
				            if(parseInt(allowed_video_size) < size){
				                addError(upfield,window.vibebp.translations.file_size_error+' '+allowed_video_size+'MB');
				               	event.target.value='';
				            }
			    			
			    		}
			    	});
			    }
			})();
			
		</script>

		<?php

	}

	/**
     * Admin field list html.
     *
	 * @param array $raw_properties properties.
	 */
	public function admin_field_html( array $raw_properties = array() ) {

	    $html = $this->get_edit_field_html_elements( array_merge(
			array( 'type' => 'text', 'class'=>'vibebp_video' ),
			$raw_properties
		) );
		?>

        <input <?php echo $html; ?>>
		<?php
	}
	/**
	 * Check if valid.
	 *
	 * @param string $value Country code.
	 *
	 * @return bool
	 */
	public function is_valid( $value ) {
		//return $value && array_key_exists( $value, self::get_countries() );
		if(!empty($value['url']) && $value['url']){
			return true;
		}
		return false;
	}
	/**
	 * Modify the appearance of value.
	 *
	 * @param  string $field_value Original value of field.
	 * @param  int    $field_id  field id.
	 *
	 * @return string   Value formatted
	 
	public static function display_filter( $field_value, $field_id = 0 ) {

		if ( empty( $field_value ) ) {
			return '';
		}

		$field_value = trim( $field_value, '/\\' );// no absolute path or dir please.
		// the BP Xprofile Custom Fields type stored '/path' which was a bad decision
		// we are using the above line for back compatibility with them.
		$videos = wp_upload_dir();

		$new_field_value = trailingslashit( $videos['baseurl'] ) . $field_value;
		$new_field_value = apply_filters( 'bpxcftr_file_type_field_data', $new_field_value, $field_id );

		$new_field_value = sprintf( '<a href="%s" rel="nofollow" class="bpxcftr-file-link">%s</a>', esc_url( $new_field_value ), __( 'Download file', 'vibebp' ) );
		
		return apply_filters( 'bpxcftr_file_display_data', $new_field_value, $field_id );
	}*/

	/**
	 * Override parent's implementation to avoid required attribute on input elements.
	 *
	 * @see \BP_XProfile_Field_Type::get_edit_field_html_elements()
	 *
	 * Get a sanitised and escaped string of the edit field's HTML elements and attributes.
	 *
	 * Must be used inside the {@link bp_profile_fields()} template loop.
	 * This method was intended to be static but couldn't be because php.net/lsb/ requires PHP >= 5.3.
	 *
	 * @param array $properties Optional key/value array of attributes for this edit field.
	 * @return string
	 */
	protected function get_edit_field_html_elements( array $properties = array() ) {

		$required = isset( $properties['required'] ) ? true : false;
		unset( $properties['required'] );
		$r = bp_parse_args(
			$properties,
			array(
				'id'   => bp_get_the_profile_field_input_name(),
				'name' => bp_get_the_profile_field_input_name(),
			)
		);

		if ( $required ) {
			$r['aria-required'] = 'true';

			// Moderators can bypass field requirements.
			if ( ! bp_current_user_can( 'bp_moderate' ) ) {
				$r[] = 'required';
			}
		}

		/**
		 * Filters the edit html elements and attributes.
		 *
		 * @param array  $r     Array of parsed arguments.
		 * @param string $value Class name for the current class instance.
		 */
		$r = (array) apply_filters( 'bp_xprofile_field_edit_html_elements', $r, get_class( $this ) );

		return bp_get_form_field_attributes( sanitize_key( bp_get_the_profile_field_name() ), $r );
	}

	/**
	 * Dashboard->Users->Profile Fields->New|Edit entry.
	 *
	 * @param \BP_XProfile_Field $current_field object.
	 * @param string             $control_type type.
	 */
	public function admin_new_field_html( \BP_XProfile_Field $current_field, $control_type = '' ) {
		$type = array_search( get_class( $this ), bp_xprofile_get_field_types() );

		if ( false === $type ) {
			return;
		}

		$class = $current_field->type != $type ? 'display: none;' : '';

		$selected_types = self::get_default_selected_types( $current_field->id );
		if(empty($selected_types)){
			$selected_types=[];
		}

		$size = self::get_default_selected_video_size( $current_field->id );

		?>
        <div id="<?php echo esc_attr( $type ); ?>" class="postbox bp-options-box" style="<?php echo esc_attr( $class ); ?> margin-top: 15px;">
                <h3><?php esc_html_e( 'Select allowed video file types:', 'vibebp' ); ?></h3>
                <div class="inside">
                    <p>
						<?php _e( 'Select video field file types:', 'vibebp' ); ?>
                        <select name="vibebp_video_types[]" id="vibebp_video_types" multiple>
                            <?php
                            foreach (array_keys(vibebp_getVideoMimeTypes()) as $key => $typee) {
                            	echo '<option value="'.$typee.'" '.(in_array($typee, $selected_types)?'selected':'').'>'.$typee.'</option>';
                            }
                            ?>
                        </select>
                    </p>
                </div>
        

        
                <h3><?php esc_html_e( 'Select video file size(in MBs)', 'vibebp' ); ?></h3>
                <div class="inside">
                    <p>
						
                        <input type="number" name="vibebp_video_size" id="vibebp_video_size" <?php if(!empty($size)) echo ' value="'.$size.'"';?>>
                            
                      
                    </p>
                </div>
        </div>

		<?php
	}

	private static function get_default_selected_types( $field_id ) {

		if ( ! $field_id ) {
			return '';
		}

		return bp_xprofile_get_meta( $field_id, 'field', 'vibebp_video_types', true );
	}

	private static function get_default_selected_video_size( $field_id ) {

		if ( ! $field_id ) {
			return '';
		}

		return bp_xprofile_get_meta( $field_id, 'field', 'vibebp_video_size', true );
	}



}
