<?php

if ( ! defined( 'ABSPATH' ) ) 
	exit;


class VibeBP_Field_Type_Country extends BP_XProfile_Field_Type {

	public function __construct() {

		parent::__construct();

		$this->name     = _x( 'Country', 'xprofile field type', 'vibebp' );
		$this->category = _x( 'VibeBP', 'xprofile field type category', 'vibebp' );

		$this->set_format( '/^.+$/', 'replace' );
		do_action( 'bp_xprofile_field_type_color', $this );
	}

	
	public function edit_field_html( array $raw_properties = array() ) {

        // reset user_id.
		$val= '';
		if(!empty( $raw_properties['user_id'])){
			$val = xprofile_get_field_data( bp_get_the_profile_field_id(), $raw_properties['user_id']);
		}
		

	    if ( isset( $raw_properties['user_id'] ) ) {
			unset( $raw_properties['user_id'] );
		}

		$html = $this->get_edit_field_html_elements( array_merge(
			array(
				'type'  => 'text',
				'value' => bp_get_the_profile_field_edit_value(),
                'class' => 'vibebp-text'
			),
			$raw_properties
		) );

		$countries = vibebp_get_countries();
		?>

        <legend id="<?php bp_the_profile_field_input_name(); ?>-1">
			<?php bp_the_profile_field_name(); ?>
			<?php bp_the_profile_field_required_label(); ?>
        </legend>

		<?php do_action( bp_get_the_profile_field_errors_action() ); ?>

		<select <?php echo $html; ?>>
        	<?php
        		foreach($countries as $code=>$country){
        			echo '<option '.($code==$val?'selected':'').' value="'.$code.'">'.$country.'</option>';
        		}
        	?>
        </select>

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
			array( 'type' => 'text', 'class'=>'vibebp_country_select' ),
			$raw_properties
		) );

	    $countries = vibebp_get_countries();
		
		?>
        <select <?php echo $html; ?>>
        	<?php
        		foreach($countries as $code=>$country){
        			echo '<option value="'.$code.'">'.$country.'</option>';
        		}
        	?>
        </select>
		<?php
	}

	public function admin_new_field_html( \BP_XProfile_Field $current_field, $control_type = '' ) {
	}
}


