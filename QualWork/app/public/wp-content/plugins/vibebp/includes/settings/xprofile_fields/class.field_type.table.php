<?php

if ( ! defined( 'ABSPATH' ) ) 
	exit;


class VibeBP_Field_Type_Table extends BP_XProfile_Field_Type {

	public function __construct() {

		parent::__construct();

		$this->name     = _x( 'Table', 'xprofile field type', 'vibebp' );
		$this->category = _x( 'VibeBP', 'xprofile field type category', 'vibebp' );

		$this->set_format( '/^.+$/', 'replace' );
		do_action( 'bp_xprofile_field_type_table', $this );

		add_action('xprofile_fields_saved_field',array($this,'save_field'), 10, 1);
	}

	
	public function edit_field_html( array $raw_properties = array() ) {

        // reset user_id.
	    if ( isset( $raw_properties['user_id'] ) ) {
			unset( $raw_properties['user_id'] );
		}

		$html = $this->get_edit_field_html_elements( array_merge(
			array(
				'type'  => 'table',
				'value' => bp_get_the_profile_field_edit_value(),
                'class' => 'vibebp-table'
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
		<?php

	}

	/**
     * Admin field list html.
     *
	 * @param array $raw_properties properties.
	 */
	public function admin_field_html( array $raw_properties = array() ) {

	    $html = $this->get_edit_field_html_elements( array_merge(
			array( 'type' => 'text', 'class'=>'vibebp_table' ),
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
		if(isset($value['url']) && $value['url']){
			return true;
		}
		return true;
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
		$fields = self::get_default_selected_table_field( $current_field->id );
        
		?>
        <div id="<?php echo esc_attr( $type ); ?>" class="postbox bp-options-box" style="<?php echo esc_attr( $class ); ?> margin-top: 15px;"> 
            <div class="inside">
                <div class="profile_field_type_table_backend"></div>
            </div>
            <?php
                wp_enqueue_style('vibebp_profile_field_table',plugins_url('../../../assets/css/profile-fields/table.css',__FILE__),array(),VIBEBP_VERSION);
                wp_enqueue_script('vibebp_profile_field_table',plugins_url('../../../assets/js/profile-fields/table.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION); 
                wp_localize_script('vibebp_profile_field_table','vibebp_profile_field_table',apply_filters('vibebp_profile_field_table',array()));
            ?>

            <script>
                window.addEventListener('load', (event) => {	
					let currentField = {}
					
					let fields = <?php echo json_encode($fields); ?>;
                    if(!fields){
                        currentField = {
                            rows:[{
								name:"",
								id:Math.random()
							},{
								name:"",
								id:Math.random()
							}],
                            columns:[{
								name:"",
								id:Math.random()
							},{
								name:"",
								id:Math.random()
							}],
                            values:[]
                        }
                    }else{
						currentField = fields;
					}
					
					console.log(currentField);

                    document.dispatchEvent(new CustomEvent("profile_field_type_table_backend", {detail: {fields: currentField,field_id:'vibebp_profile_field_table'}}));	
                });
            </script>
        </div>
		<?php
	}

	private static function get_default_selected_table_field( $field_id ) {
		if ( ! $field_id ) {
			return '';
		}
		return bp_xprofile_get_meta( $field_id, 'field', 'vibebp_profile_field_table', true );
	}

	function save_field($field){
		
		$init = VibeBP_Init::init();
		if(empty($init->saved_table_meta) && $field->type == 'table'){
			if(!empty($_POST['vibebp_profile_field_table'])){
				bp_xprofile_update_field_meta( (int) $field->id, 'vibebp_profile_field_table', vibebp_recursive_sanitize_array_field(json_decode(stripslashes($_POST['vibebp_profile_field_table']),true))) ;
			}
			$init->saved_table_meta=1;
		}
	}
}
