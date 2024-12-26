<?php

 if ( ! defined( 'ABSPATH' ) ) exit;

 class VibeBP_Member_Type_Settings{

 	protected $option = 'vibebp_member_types';
	public static $instance;
    public $bp_member_type_synced;
    public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Member_Type_Settings();
        return self::$instance;
    }

    public function __construct(){
        $this->bp_member_type_synced = get_option('bp_member_type_synced');
    	
        if(empty($this->bp_member_type_synced)){
            //add_action( 'bp_register_member_types',array($this, 'register_member_types_with_directory' ));
        }
        add_action('user_register',array($this,'assign_member_type_new_user'));

        add_action('wp_ajax_get_unassigned_members',array($this,'get_unassigned_members'));
        add_action('wp_ajax_change_bp_member_type',array($this,'change_bp_member_type'));

    }

    function change_bp_member_type(){
        global $wpdb;
        $results = [];
        if(current_user_can('manage_options') && wp_verify_nonce($_POST['security'],'change_bp_member_type') ){
        $get_default_mtype = vibebp_get_setting('default_member_type','bp','general');
            if(!empty($_POST['ID'])){
                foreach($_POST['ID'] as $user_id){
                    $user_id = intval($user_id);
                    bp_set_member_type($user_id, $get_default_mtype);
                }
            }
        }
    }

    function get_unassigned_members(){
        global $wpdb;
        $results = [];
        if(current_user_can('manage_options') && wp_verify_nonce($_POST['security'], 'assign_default_member_type') ){
            $userid_has_member_type = $wpdb->get_results("SELECT term_relation.object_id as user_id 
                    FROM {$wpdb->term_relationships} AS term_relation
                    INNER JOIN {$wpdb->term_taxonomy} AS term_tax
                ON term_relation.term_taxonomy_id = term_tax.term_id
                WHERE term_tax.taxonomy = 'bp_member_type'",ARRAY_A);
            if(!empty($userid_has_member_type)){
                $get_default_mtype = vibebp_get_setting('default_member_type','bp','general');
                $user_ids = wp_list_pluck( $userid_has_member_type, 'user_id' );
                $no_member_type_user_ids = $wpdb->get_results("SELECT ID FROM {$wpdb->users} WHERE ID NOT IN (".implode(',',$user_ids).")");
                if(!empty($no_member_type_user_ids)){
                    $alluids = wp_list_pluck($no_member_type_user_ids,'ID');
                    if(count($alluids) > 10 || count($alluids) < 10){
                        for($i=0;$i<count($alluids);$i=$i+intval($_POST['batch'])){
                            $arr = [];
                            for($k=0;$k<intval($_POST['batch']);$k++){
                                $arr[]=$alluids[$i+$k];
                            }
                            $results[]=array(
                                'action'=>'change_bp_member_type',
                                'security'=>wp_create_nonce('change_bp_member_type'),
                                'ID'=>$arr
                            );
                        }
                    }
                }
            }

        }
        print_r(json_encode($results));
        die();
    }

    function assign_member_type_new_user($user_id){
             $set_default_mtype = vibebp_get_setting('default_member_type','bp','general');
             return bp_set_member_type($user_id, $set_default_mtype);
    }

    function get_member_types(){

        if(empty($this->member_types))
            $this->member_types = get_option($this->option);

        return $this->member_types;
    }

    function show_member_type_field($name){
        $member_types = get_option($this->option);
        $forms = get_option('wplms_registration_forms');
        if(!empty($member_types)){
            echo '<li><label class="field_name">'.__('Assign member type','vibe-customtypes').'</label><select name="member_type|'.$name.'"><option value="">'._x('None','member types','vibe-customtypes').'</option><option value="enable_user_member_types_select" '.((isset($forms[$name]['settings']['member_type']) && $forms[$name]['settings']['member_type'] == 'enable_user_member_types_select')?'selected':'').'>'._x('Enable User to select one','member types','vibe-customtypes').'</option>';
            foreach($member_types as  $key => $member_type){
                echo '<option value="'.$member_type['id'].'" '.((isset($forms[$name]['settings']['member_type']) && $forms[$name]['settings']['member_type'] == $member_type['id'])?'selected':'').'>'.$member_type['sname'].'</option>';
            }
            echo '</select></li>';
        }
    }

    function add_member_types_settings($tabs){
    	if(!isset($_GET['tab']) || $_GET['tab'] == 'general'){
    		
	    	$tabs['member_types'] = _x('Member Types','configure Course menu in LMS - Settings','vibe-customtypes');
 		}
 		return $tabs;
    }

   function register_member_types_with_directory() {
        $member_types = get_option($this->option);
        if(!empty($member_types)){
            foreach($member_types as $key => $member_type){
                if(!empty($member_type)){
                    bp_register_member_type( $member_type['id'], array(
                        'labels' => array(
                            'name'          => $member_type['pname'],
                            'singular_name' => $member_type['sname'],
                        ),
                        'has_directory' => $member_type['id']
                    ));
                }
                
            }
        }
    }   

    function show(){
    	if(!isset($_GET['sub']) || $_GET['sub'] != 'members')
    		return;
            //Fetch Member types >
            $types = bp_get_member_types(array(),'objects');
                $mtypes = [''=>__('No default member type','vibebp')];
                if(!empty($types)){
                    foreach($types as $type => $labels){
                        $mtypes[$type]=$labels->labels['name'];
                    }
                }
            $settings = apply_filters('VibeBP_Settings_Buddypress_members',array(
                array(
                    'label' => __('Default Member','vibebp'),
                    'name' => 'default_member_type',
                    'type' => 'select',
                    'options'=>$mtypes,
                    'desc' => __('Select default member type').'<a id="assign_default_member_type" class="button">Apply to existing members who do not have anyu member type assigned</a>',
                    'default'=>''
                )
            ));
            //JS call assign_default_member_type
            ;

            ?>
            <script>
                jQuery(document).ready(function($){
                    $('#assign_default_member_type').on('click',function(){
                        var $this = $(this);
                        $this.after('<span class="member_type_status">Starting ...</span><div class="progress_wrap" style="margin: 30px 0;width: 300px;"><div class="progress" style="height: 10px;border-radius: 5px;"><div class="bar" style="width: 5%;"></div></div></div>');
                        //Show progress bar
                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            dataType: "json",
                            data: { 
                                    action: 'get_unassigned_members', 
                                    security: '<?php echo wp_create_nonce('assign_default_member_type'); ?>',
                                    batch:10
                                },
                            cache: false,
                            success: function (json) {
                                $this.parent().find('.progress_wrap .bar').css('width','10%');
                                $this.parent().find('span.member_type_status').text('fetched '+Object.keys(json).length*10+' unassigned members, sync in progress...');
                                    var defferred = [];
                                    var current = 0;
                                    $.each(json,function(i,item){
                                        defferred.push(item);
                                    });
                                    recursive_step(current,defferred,$this);
                                    //$.each() RUN loop on json and increment progress bar
                                    $('body').on('end_recursive_member_type_sync',function(){
                                        $this.parent().find('span.member_type_status').text(text);
                                        $this.parent().find('.progress_wrap .bar').css('width','100%');
                                        setTimeout(function(){$this.parent().find('.progress_wrap,.member_type_status').hide(200);},3000);
                                    });     
                            }
                        });
                    });

                    function recursive_step(current,defferred,$this){
                        if(current < defferred.length){
                            $.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: defferred[current],
                                cache: false,
                                success: function(){ 
                                    current++;
                                    $this.find('span.member_type_status').text(current+'/'+defferred.length+' complete, sync in progress...');
                                    var width = 10 + 90*current/defferred.length;
                                    $this.parent().find('.bar').css('width',width+'%');
                                    if(defferred.length == current){
                                        $('body').trigger('end_recursive_member_type_sync');
                                    }else{
                                        recursive_step(current,defferred,$this);
                                    }
                                }
                            });
                        }else{
                            $('body').trigger('end_recursive_member_type_sync');
                        }
                    }//End of function

                });
            </script>
            <?php
            $s = VibeBP_Settings::init();
            $s->vibebp_settings_generate_form('bp',$settings,'members');
            
        if(defined('BP_VERSION') && version_compare( BP_VERSION, '7.1', '>=' )){
            if(!empty($this->bp_member_type_synced)){
                echo '<p>'._x('You have migrated to buddypress member type panel','','vibebp').'</p>';
                echo '<a href="'.admin_url('edit-tags.php?taxonomy=bp_member_type').'" class="button is-primary" style="margin-bottom:50px;">'._x('Goto buddypress panel','','vibebp').'</a>';

                //Query Member types >
                $settings = apply_filters('VibeBP_Settings_Buddypress_members',array(
                    // array(
                    //     'label' => __('Editor Interface','vibebp'),
                    //     'name' => 'editor_interface',
                    //     'type' => 'select',
                    //     'options'=>array(
                    //         'full'=>__('Full Editor [ All Shortcodes, Media Library, Math, Columns','vibebp'),
                    //         'advanced'=>__('Advanced Editor [ All Shortcodes, Media Library, Columns','vibebp'),
                    //         'basic'=>__('Basic Editor [ Media library, No Shortcodes,No Columns ]','vibebp'),
                    //     ),
                    //     'desc' => __('What kind of editor is suitable for your site','vibebp').'<a href="" style="text-decoration:none;"><span class="dashicons dashicons-editor-help"></span></a>',
                    //     'default'=>''
                    // )
                ));
                $s = VibeBP_Settings::init();
                $s->vibebp_settings_generate_form('bp',$settings,'members');

                return;
            }else{
                echo '<a href="'.admin_url('edit-tags.php?taxonomy=bp_member_type&migrate_vibebp=1').'" class="button is-primary" style="margin-bottom:50px;">'._x('Migrate to buddypress panel','','vibebp').'</a>';
            }
        }
        
        if(function_exists('bp_get_member_types')){
            $member_types_array=array();
            $member_types = bp_get_member_types( array(), 'objects' );
            if(!empty($member_types)){
                foreach ($member_types as $key => $member_type) {
                    $member_types_array[] = array(
                        'show_settings'=>false,
                        'id'=> $key,
                        'sname'=>$member_type->labels['singular_name'],
                        'pname'=>$member_type->labels['name'],
                        'error'=>'',

                        );
                }

                echo '<script>var existing_member_types = '.json_encode($member_types_array).';</script>';
            }
        }
        
       ?>
        <section  id="member_types_wrapper">

            <button @click="add" id="add" class="button button-primary"><?php echo _x('Add another','member types','vibe-customtypes');?></button>
          
            <ul v-sortable id="member_types_cont">
                <li v-for="(listing,index) in listings" class="mt_listing">
                    <small class="dashicons dashicons-menu"></small>
                    <span>{{listing.sname}}</span>
                    <ul v-bind:class="{ show: listing.show_settings }" class="mt_listing_settings">
                        <li><label><?php echo _x('Slug','','vibe-customtypes');?></label>{{listing.id}}</li>
                        <li><label><?php echo _x('Singular Name','','vibe-customtypes');?></label><input type="text" name="slisting" placeholder="<?php echo _x('Member type singular name','','vibe-customtypes')?>" class="form-control" v-model="listing.sname" @keyup="check_data(listing,listing.sname)" ></li>

                        <li><label><?php echo _x('Plural Name','','vibe-customtypes');?></label><input type="text" name="plisting" placeholder="<?php echo _x('Member type plural name','','vibe-customtypes')?>" class="form-control" v-model="listing.pname" @keyup="check_data(listing,listing.pname)"></li>

                        
                        <li class="ithaserror red" v-html="listing.error"></li>
                    </ul>
                    <em @click="remove_data(index)" class="red remove_member_type action_point dashicons dashicons-no"></em>
                    <em @click="listing.show_settings = !listing.show_settings" class="mt_setting_toggle action_point dashicons dashicons-edit"></em>
                </li>
            </ul>
            <?php wp_nonce_field('vibe_security','vibe_security');?>
            <button v-bind:class="{ loading: loading }" @click="save_mts"  class="button button-primary">{{save_settings_text}}</button>
            <button v-bind:class="{ loading: loading_rs }" class="reset_member_types button"  @click="reset_mts">{{reset_settings_text}}</button>


        </section>
        <style>
            .mt_listing {
                display: block;
                margin: 15px;
                background: #fff;
                width: 85%;
                position: relative;
                padding: 15px;
                border-radius: 3px;
            }
            .mt_listing .action_point {
                right: 10px;
                position: absolute;
                top: 15px;
            }
            em.mt_setting_toggle.action_point {
                right: 40px;
            }
            .red{
                color:red;
            }
        	.remove_member_type,.mt_setting_toggle{
        		cursor: pointer;
        	}
            .ithaserror {
                display: block;
                color: red;
                margin: 10px 2px;
            }
            .loading{
                opacity: 0.5;
            }
            .mt_listing_settings{
                display: none;
            }
            .mt_listing_settings.show{
                display: block;
                margin: 10px;
            }
            .mt_listing_settings>li>label {
                width: 100px;
                display: inline-block;
            }
            

        </style>
        <?php
       
        return array();
    }

}	

VibeBP_Member_Type_Settings::init();
