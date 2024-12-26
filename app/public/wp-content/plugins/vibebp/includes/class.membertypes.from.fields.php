<?php
/**
 * 
 * @author 		VibeThemes
 * @category 	Init
 * @package 	wplms-appointments/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class MemberTypes_Custom_Field{


	public static $instance;

    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new MemberTypes_Custom_Field();
        return self::$instance;
    }


    function __construct(){
        $this->bp_get_member_type_tax_name = bp_get_member_type_tax_name();
		add_action( $this->bp_get_member_type_tax_name.'_add_form_fields', array( $this, 'add_category_fields' ));
		add_action(  $this->bp_get_member_type_tax_name.'_edit_form_fields', array( $this, 'edit_category_fields' ));
		add_action( 'bp_type_updated', array($this,'save_category_meta'), 10, 2 );
		add_action( 'bp_type_inserted', array($this,'save_category_meta'), 10, 2 );
    }

    
    function add_category_fields(){
    	
    	global $wp_roles;

	    $all_roles = $wp_roles->roles;
	    $editable_roles = apply_filters('editable_roles', $all_roles);
    	?>
    	<div class="form-field bp-types-form term-vibebp_member_type_user_role-wrap">
    	<label><?php _e( 'Select user role to be assigned with the member type.', 'vibebp' ); ?></label>
    	<select name="vibebp_member_type_user_role" id="vibebp_member_type_user_role"/>
    	<option value=""><?php echo _x('Select','','vibebp');?></option>
    	<?php 
    	foreach ($editable_roles as $key => $role) {
    		echo '<option value="'.$key.'">'.$role['name'].' </option>';
    	}
    	?>
    	</select>
    	<div class="clear"></div>
    	</div>
    	
		<?php
    }
    /*
    *	Edit Course Category Featured thubmanils
    *	Use WP 4.4 Term meta for storing information
    * 	@reference : WooCommerce (GPLv2)
    */
    function edit_category_fields($term){


    	$user_role = get_term_meta( $term->term_id, 'vibebp_member_type_user_role', true ); 
		global $wp_roles;

	    $all_roles = $wp_roles->roles;
	    $editable_roles = apply_filters('editable_roles', $all_roles);
	
    	?>
    	<tr class="form-field bp-types-form term-vibebp_member_type_user_role-wrap">
    		<th scope="row" valign="top"><label><?php _e( 'Select user role to be assigned with the member type.', 'vibebp' ); ?></label></th>
			<td>
				<select name="vibebp_member_type_user_role" id="vibebp_member_type_user_role"/>
				<option value=""><?php echo _x('Select','','vibebp');?></option>
		    	<?php 
		    	foreach ($editable_roles as $key => $role) {
		    		echo '<option value="'.$key.'" '.($user_role===$key?'selected':'').'>'.$role['name'].' </option>';
		    	}
		    	?>
		    	</select>
		    	<div class="clear"></div>
			</td>
    	</tr>
    	
		<?php
    }


	function save_category_meta( $term_id, $tt_id ){
		global $wpdb;
	    if( isset( $_POST['vibebp_member_type_user_role'] ) ){
	    	if(is_array($term_id) && !empty($term_id['term_id'])){
	    		$term_id = $term_id['term_id'];
	    	}
	        update_term_meta( $term_id, 'vibebp_member_type_user_role', esc_attr($_POST['vibebp_member_type_user_role'] ));
	    }
	}

}
add_action('bp_init',function(){
	if(function_exists('bp_get_member_type_tax_name')){
		MemberTypes_Custom_Field::init();
	}
});
