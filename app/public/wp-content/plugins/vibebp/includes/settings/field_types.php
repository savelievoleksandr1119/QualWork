<?php

if ( ! defined( 'ABSPATH' ) ) exit;


class VibeBP_Field_Types{

 	public static $instance;
    public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Field_Types();
        return self::$instance;
    }

    public function __construct(){

    	add_filter( 'bp_xprofile_get_field_types', array( $this, 'register_field_types' ), 10, 1 );
        add_action( 'xprofile_fields_saved_field', array( $this, 'save_meta' ) );

        add_action('bp_custom_profile_edit_fields',array($this,'echo_upload_error_action'));
        add_action('bp_signup_validate', array($this,'upload_validation'));
        //add_action( 'xprofile_data_before_save',array($this, 'validate_upload_check'), 1, 1 );


         add_action( 'bp_signup_usermeta',array($this, 'save_upload_field'));

         //this handled at buddypress save level //react part also uses this not other validations
         add_filter('xprofile_data_value_before_save',array($this,'checkupload_field'),0,4);

        add_filter('vibebp_profile_field_block_value',array($this,'check_video_gallery'),21,2);

         add_filter('vibebp_profile_block_field_value',array($this,'check_video_gallery_field'),21,3);
    }

    function check_video_gallery_field($value,$field,$user_id){


       if($field->type=='video' || $field->type=='gallery'){
            $val =BP_XProfile_ProfileData::get_value_byid( $field->id, $user_id);
            
            if(is_serialized($val)){
                $val = unserialize(stripcslashes($val));
            }
            if(!empty($val)){
                $value = $val;
            }
        }

        return $value;
    }

    function check_video_gallery($value,$field){
       
        if($field->type=='video' || $field->type=='youtube' || $field->type=='vimeo'){
            
            if(!empty($value['url'])){
                global $wp_embed;
                $value = $wp_embed->run_shortcode('[embed]'.$value['url'].'[/embed]').'<style>.field_type_video{width:100%}.video.field_type_video .fitvids { position: relative; padding-bottom: 56.25%; /* 16:9 */ height: 0; } .video.field_type_video .fitvids iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }</style>';
            }
            
        }
        if($field->type=='gallery'){
            
            if(is_string($value)){
                $json = json_decode($value,true);
                if (json_last_error() === 0) {
                   $value = $json;
                }
            }

            if(!empty($value) && is_array($value)){
                $html = '<div class="vibebp_media_gallery">';
                foreach ($value as $key => $image) {
                   
                    if(!empty($image['url'])){
                        if(!empty($image['id'])){

                            if($image['type'] == 'image'){
                                if(!empty($field->full_image)){
                                    $html .= '<div class="full_image"><img src="'.$image['url'].'"></div>';    
                                }else{
                                    $html .= '<div class="vibebp_media_gallery_image" data-url="'.$image['url'].'"><img src="'.wp_get_attachment_image_url($image['id'],'thumbnail').'"></div>';        
                                }
                                
                            }else{
                                $html .= '<div class="vibebp_media_gallery_attachment"><a href="'.$image['url'].'" target="_blank">'.$image['name'].'</a></div>'; 
                            }
                            
                        }else{
                            $html .= '<div class="vibebp_media_gallery_image"><img src="'.$image['url'].'"></div>';
                        }
                        
                    }
                }
                $html .= '</div><style>.gallery.field_type_gallery,.vibebp_media_gallery{width:100%;}.vibebp_media_gallery { display: grid; grid-template-columns: repeat( auto-fit, minmax(120px,1fr) ); grid-gap: 0; }.vibebp_media_gallery_image{cursor:pointer;}.vibebp_gallery_popup { display: flex; position: fixed; z-index: 99999; background: rgba(0,0,0,0.4); left: 0; top: 0; width: 100vw; height: 100vh; align-items: center; justify-content: center; } .vibebp_gallery_popup > * { max-width: 80vw; max-height: 80vh; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 10px rgba(0,0,0,0.4); }</style>';
                ob_start();
                ?>
                <script>
                    window.addEventListener('load',function(){
                        (function() {  
                            document.querySelectorAll('.field_<?php echo $field->id;?> .vibebp_media_gallery .vibebp_media_gallery_image').forEach(function(el){
                                el.addEventListener('click',function(ee){
                                    let src = '';
                                    if (event.target.nodeName == 'IMG'){
                                        src = ee.target.getAttribute('src');
                                    }else{
                                        src = ee.target.querySelector('img').getAttribute('src');
                                    }
                                    
                                    if(src.length){
                                        let popup=document.querySelector('.vibebp_gallery_popup');
                                        if(popup){
                                            popup.parentNode.removeChild(popup);    
                                        }
                                        

                                        const element = document.createElement("div");
                                        element.classList.add('vibebp_gallery_popup');   
                                        element.innerHTML = '<div class="vibebp_gallery_popup_content"><img src="'+src+'"></div>';
                                        element.onclick = function(){
                                            element.remove();
                                        };
                                        document.querySelector('body').appendChild(element);
                                    }
                                });
                            });
                        })();  
                    });
                </script>
                <?php
                $html .= ob_get_clean();
                $value =$html;
            }  


        }
        return $value;
    }

    function save_upload_field($usermeta){
        $profile_field_ids = explode( ',', vibebp_recursive_sanitize_array_field($_POST['signup_profile_field_ids']) );
        
        
        foreach ($profile_field_ids as $key => $field_id ) {
            
            if(!empty($field_id)){
                $field =  new BP_XProfile_Field( $field_id  );
                if(!empty($field) && ($field->type =='upload' || $field->type =='video')){
                   
                    $file = $_FILES['field_'.$field_id];
                    if(!empty($file)){
                        
                        $uploadedfile = $file;

                        $file_mime_type= $file['type'];
                        $file_size=$file['size'];
                        $upload_overrides = array( 'test_form' => false );
                        $ffield = $field_id;
                        if ( ! function_exists( 'wp_handle_upload' ) ) {
                            require_once( ABSPATH . 'wp-admin/includes/file.php' );
                        }
                        
                        $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
                        if ( $movefile && ! isset( $movefile['error'] ) ) {
                            if ( $movefile && !isset( $movefile['error'] ) ) {
                                $filePath=$movefile['url'];
                                $attachment = array(
                                    'guid'           => $filePath,
                                    'post_mime_type' => $movefile['type'],
                                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filePath ) ),
                                    'post_content'   => '',
                                    'post_status'    => 'inherit',
                                    'post_size'      => $file['size']
                                );
                            
                                // Insert the attachment.
                                $attach_id = wp_insert_attachment( $attachment, $filePath );
                                if(!empty($attach_id)){
                                    $post = get_post($attach_id);
                                    if($post){
                                        $attachment_data = $this->get_single_attachment($post);
                                        
                                        $usermeta['field_'.$field_id] = $attachment_data;
                                        
                                        
                                    }
                                }
                            }
                        } else {
                             wp_die(_x('File could not be uploaded!','error message','wplms')) ;
                            
                        } 
                    } else {
                         wp_die(_x('File could not be uploaded!','error message','wplms')) ;
                    } 
                }
            }
                
            
            
        }
        return $usermeta;
    }

    function checkupload_field( $value, $id, $bool, $that){
         $field =  new BP_XProfile_Field( $that->field_id );
        if($field->type =='upload' || $field->type =='video' || $field->type =='gallery' || $field->type =='table' ){
            remove_filter( 'xprofile_data_value_before_save',          'xprofile_sanitize_data_value_before_save', 1, 4 );
        }else{
            add_filter( 'xprofile_data_value_before_save',          'xprofile_sanitize_data_value_before_save', 1, 4 );
        }
        


        return $value;
    }

    function validate_phone_check ($that ) {
        if(!function_exists('bp_displayed_user_id'))
            return;
        $userdomain = bp_displayed_user_id();
        if(empty($userdomain))
            return;
        $flag = 0;
       

        
        $field =  new BP_XProfile_Field( $that->field_id );
        if($field->type =='upload'){

        }

        if(!$flag){ 
            $redirect_url = trailingslashit( bp_displayed_user_domain() . bp_get_profile_slug() . '/edit/group/' . bp_action_variable( 1 ) );
            bp_core_add_message( $message, 'error' );
            bp_core_redirect( $redirect_url );
            
        }
    }

    function echo_upload_error_action(){
        $field =  new BP_XProfile_Field( bp_get_the_profile_field_id() );
        if($field->type =='upload'){
            do_action( 'bp_vibebp_upload_field_errors' );
        }
    }

     function upload_validation(){
        global $bp,$wpdb;
        $profile_field_ids = explode( ',', vibebp_recursive_sanitize_array_field($_POST['signup_profile_field_ids']) );
        
         /*
         * Loop through the posted fields, formatting any
         * datebox values, then add to usermeta.
         */
        foreach ( (array) $profile_field_ids as $field_id ) {
            $field =  new BP_XProfile_Field( $field_id  );
            if($field->type =='upload' || $field->type =='video'){
                if(!empty($bp->signup->errors['field_' . $field_id]) && !empty($_FILES['field_'.$field_id])){
                    unset($bp->signup->errors['field_' . $field_id]);
                }
                $file = $_FILES['field_'.$field_id];
                if(!empty($file)){
                    $allowed_size = bp_xprofile_get_meta( $field_id, 'field', 'vibebp_upload_size', true );

                    if($allowed_size < (($file['size']/1024)/1024)){
                        $bp->signup->errors['field_' . $field_id] = sprintf(_x('Allowed file size is %s','','vibebp'),$allowed_size.'MB');
                    }
                    $allowed_types = bp_xprofile_get_meta( $field_id, 'field', 'vibebp_upload_types', true );
                    $all_types = vibebp_getMimeTypes();
                    $mimetypes = [];
                    foreach ($allowed_types as $key => $allowed_type) {
                        if(!empty($all_types[$allowed_type])){
                            foreach ($all_types[$allowed_type] as $key => $type) {
                               $mimetypes[] =$type;
                            }
                        }
                        
                    }
                    if(!in_array($file['type'], $mimetypes)  ){
                        $bp->signup->errors['field_' . $field_id] = _x('File type not allowed!','','vibebp');
                    }

                }
            }
        }
        return;
    }

    public function save_meta( $field ) {

        switch ( $field->type ) {

            case 'upload':
           
                $data = isset( $_POST['vibebp_upload_types'] ) ? wp_unslash( vibebp_recursive_sanitize_array_field($_POST['vibebp_upload_types']) ) : '';
                bp_xprofile_update_field_meta( $field->id, 'vibebp_upload_types', $data );
                 $data = isset( $_POST['vibebp_upload_size'] ) ? wp_unslash( sanitize_text_field($_POST['vibebp_upload_size']) ) : '';
                bp_xprofile_update_field_meta( $field->id, 'vibebp_upload_size', $data );
                break;
            case 'video':
            $data = isset( $_POST['vibebp_video_types'] ) ? wp_unslash( vibebp_recursive_sanitize_array_field($_POST['vibebp_video_types'] )) : '';
                bp_xprofile_update_field_meta( $field->id, 'vibebp_video_types', $data );
                 $data = isset( $_POST['vibebp_video_size'] ) ? wp_unslash( sanitize_text_field($_POST['vibebp_video_size']) ) : '';
                bp_xprofile_update_field_meta( $field->id, 'vibebp_video_size', $data );
              break;
            case 'gallery':
            $data = isset( $_POST['vibebp_gallery_types'] ) ? wp_unslash( vibebp_recursive_sanitize_array_field($_POST['vibebp_gallery_types']) ) : '';
                bp_xprofile_update_field_meta( $field->id, 'vibebp_gallery_types', $data );
                 $data = isset( $_POST['vibebp_gallery_size'] ) ? wp_unslash( sanitize_text_field($_POST['vibebp_gallery_size']) ) : '';
                bp_xprofile_update_field_meta( $field->id, 'vibebp_gallery_size', $data );
              break;
            case 'repeatable':
                $data = isset( $_POST['vibebp_repeatable_type'] ) ? sanitize_text_field($_POST['vibebp_repeatable_type']): '';
                bp_xprofile_update_field_meta( $field->id, 'vibebp_repeatable_type', $data );
                $data = isset( $_POST['vibebp_repeatable_style'] ) ? sanitize_text_field($_POST['vibebp_repeatable_style']): '';
                bp_xprofile_update_field_meta( $field->id, 'vibebp_repeatable_style', $data );
              break;
            default:
                
                break;
            
        }
    }

    function register_field_types($fields){

    	$fields = array_merge( $fields, $this->get_field_types() );
		return $fields;
    }	

    function includes(){
    	include_once 'xprofile_fields/class.field_type.color.php';	
    	include_once 'xprofile_fields/class.field_type.country.php';	
    	include_once 'xprofile_fields/class.field_type.location.php';	
        include_once 'xprofile_fields/class.field_type.social.php';   
        include_once 'xprofile_fields/class.field_type.repeatable.php';
        include_once 'xprofile_fields/class.field_type.upload.php';
        include_once 'xprofile_fields/class.field_type.video.php';
        include_once 'xprofile_fields/class.field_type.gallery.php';
        include_once 'xprofile_fields/class.field_type.table.php';
    }

    function get_field_types(){

    	$this->includes();

    	$fields = array(
			'color' => 'VibeBP_Field_Type_Color',
			'country' => 'VibeBP_Field_Type_Country',
			'location' => 'VibeBP_Field_Type_Location',
            'social' => 'VibeBP_Field_Type_Social',
            'repeatable' => 'VibeBP_Field_Type_Repeatable',
            'upload' => 'VibeBP_Field_Type_Upload',
            'video' => 'VibeBP_Field_Type_Video',
            'gallery' => 'VibeBP_Field_Type_Gallery',
            'table' => 'VibeBP_Field_Type_Table',
		);

		return $fields;
    }

    function keyed_mime_types(){
        $key_pair = array();
        $mime_types = wp_get_mime_types();
        $a_mime_types = array();
        if(!empty($mime_types)){
            foreach ($mime_types as $key=>$value) {
                $expoloed_keys = explode("|",$key);
                foreach($expoloed_keys as $key1=>$value1){
                    $a_mime_types[$value1] = $value;
                }
            }
        }
        $ext_types = wp_get_ext_types();
        if(!empty($ext_types)){
            foreach ($ext_types as $key=>$value) {
                foreach($value  as $key1=>$value1){
                    if(!empty($a_mime_types[$value1])){
                        $key_pair[$a_mime_types[$value1]] = $key;
                    }   
                }
            }
        }
        return  $key_pair;
    }

    function get_single_attachment($post){
        $attachment_id = $post->ID;
        $data = array(
            'name' => $post->post_name,
            'id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id)
        );
        $post_mime_type = get_post_mime_type($post);
        if(!empty($post_mime_type)){
            if(!isset($this->keyed_mime_types)){
                $this->keyed_mime_types = $this->keyed_mime_types();
            }
            if(!empty($this->keyed_mime_types[$post_mime_type])){
                $data['type'] = $this->keyed_mime_types[$post_mime_type];
            }else{
                $data['type'] = null;
            }
        }
        return $data;
    }

}

VibeBP_Field_Types::init();

