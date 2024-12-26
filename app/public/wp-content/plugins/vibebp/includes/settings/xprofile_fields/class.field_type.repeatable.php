<?php

if ( ! defined( 'ABSPATH' ) ) 
	exit;


class VibeBP_Field_Type_Repeatable extends BP_XProfile_Field_Type {

	public function __construct() {

		parent::__construct();

		$this->name     = _x( 'Repeatable', 'xprofile field type', 'vibebp' );
		$this->category = _x( 'VibeBP', 'xprofile field type category', 'vibebp' );

		$this->set_format( '/^.+$/', 'replace' );
		do_action( 'bp_xprofile_field_type_repeatable', $this );
	}

	
	public function edit_field_html( array $raw_properties = array() ) {

        // reset user_id.
	    if ( isset( $raw_properties['user_id'] ) ) {
			unset( $raw_properties['user_id'] );
		}

		$html = $this->get_edit_field_html_elements( array_merge(
			array(
				'type'  => 'repeatable',
				'value' => bp_get_the_profile_field_edit_value(),
                'class' => 'vibebp-repeatable'
			),
			$raw_properties
		) );
		?>

        <legend id="<?php bp_the_profile_field_input_name(); ?>-1">
			<?php bp_the_profile_field_name(); ?>
			<?php bp_the_profile_field_required_label(); ?>
        </legend>

		<?php do_action( bp_get_the_profile_field_errors_action() ); ?>

        <input <?php echo $html; ?>>

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
			array( 'type' => 'text', 'class'=>'vibebp_repeatable' ),
			$raw_properties
		) );
		?>

        <input <?php echo $html; ?>>
		<?php
	}

	public function admin_new_field_html( \BP_XProfile_Field $current_field, $control_type = '' ) {
		$type = array_search( get_class( $this ), bp_xprofile_get_field_types() );

		if ( false === $type ) {
			return; 
		}

		$class = $current_field->type != $type ? 'display: none;' : '';

		$selected_type = self::get_default_repeatable_type( $current_field->id );
		if(empty($selected_type)){
			$selected_type = 'stacked';
		}

		$repeatable_style = self::get_default_repeatable_style( $current_field->id );
		if(empty($repeatable_style)){
			$repeatable_style = 'default';
		}
		?>
        <div id="<?php echo esc_attr( $type ); ?>" class="postbox bp-options-box" style="<?php echo esc_attr( $class ); ?> margin-top: 15px;">
                <h3><?php esc_html_e( 'Select repeatable type', 'vibebp' ); ?></h3>
                <div class="inside">
                    <div class="repeatable_label_imgs">
                        
                            <?php
                            $types = array(
                            	'stacked'=>[
                            		'label'=>__('Icon Stacked , Title , Description','vibebp'),
                            		'img'=>plugins_url('../../../assets/images/stacked.png',__FILE__)
                            	],
                            	'spaced'=>[
                            		'label'=>__('Icon Spaced , Title , Description','vibebp'),
                            		'img'=>plugins_url('../../../assets/images/spaced.png',__FILE__)
                            	],
                            	'titled'=>[
                            		'label'=>__('Title , Description','vibebp'),
                            		'img'=>plugins_url('../../../assets/images/titled.png',__FILE__)
                            	],
                            	'desc'=>[
                            		'label'=>__('Description','vibebp'),
                            		'img'=>plugins_url('../../../assets/images/desc.png',__FILE__)
                            	]
                            );
                            foreach ($types as $key => $typee) {
                            	echo '<input type="radio"  name="vibebp_repeatable_type" id="'.$key.'" value="'.$key.'" '.(($key == $selected_type)?'checked':'').' /><label for="'.$key.'">
                            			<img src="'.$typee['img'].'" />
                            			'.$typee['label'].'
                            			</label>
                            			';
                            }
                            ?>
                    </div>
                </div><style>.repeatable_label_imgs{display: flex;flex-wrap: wrap;gap: 1rem;}.repeatable_label_imgs label{border: 1px solid #eee;padding:1rem;max-width:160px;display:flex;flex-direction:column;justify-content: space-between; align-items:center;}.repeatable_label_imgs img{width:100%;}.repeatable_label_imgs input{display: none;}.repeatable_label_imgs input:checked + label {border: 2px solid #42d342;}</style>
                <h3><?php esc_html_e( 'Select repeatable style', 'vibebp' ); ?></h3>
                <div class="inside">
                    <div class="repeatable_label_imgs">
                     <select name="vibebp_repeatable_style">
                     	<option value="default" <?php echo $repeatable_style == 'default'?'selected':''?> ><?php _ex('Default','repeatable field style','vibebp'); ?></option>
                     	<option value="listr" <?php echo $repeatable_style == 'listr'?'selected':''?>><?php _ex('List','repeatable field style','vibebp'); ?></option>
                     	<option value="timeliner" <?php echo $repeatable_style == 'timeliner'?'selected':''?>><?php _ex('Timeline','repeatable field style','vibebp'); ?></option>
                     </select>
                    </div>
                </div>
        </div>

		<?php
	}

	private static function get_default_repeatable_type( $field_id ) {

		if ( ! $field_id ) {
			return '';
		}

		return bp_xprofile_get_meta( $field_id, 'field', 'vibebp_repeatable_type', true );
	}

	private static function get_default_repeatable_style( $field_id ) {

		if ( ! $field_id ) {
			return '';
		}

		return bp_xprofile_get_meta( $field_id, 'field', 'vibebp_repeatable_style', true );
	}


}
