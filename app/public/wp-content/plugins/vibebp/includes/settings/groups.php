<?php

 if ( ! defined( 'ABSPATH' ) ) exit;

 class VibeBP_Groups_Settings{

 	protected $option = 'vibebp_group_types';
	public static $instance;
    public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Groups_Settings();
        return self::$instance;
    }

    public function __construct(){

        add_filter('vibebp_buddypress_general_settings_tabs',array($this,'add_tab'));
        add_action('vibebp_bp_subtab_groups',array($this,'group_tab'));

        add_filter('vibebp_settings_type',array($this,'group_fields'),10,2);
    }

    function add_tab($tabs){
        if(function_exists('bp_is_active') && bp_is_active('groups')){
            $tabs['groups'] = esc_html__('Groups','vibebp');
        }
        return $tabs;
    }
    
    function group_tab(){

        $types = bp_get_member_types(array(),'objects');
        $mtypes = [''=>__('All member types','vibebp')];
        if(!empty($types)){
            foreach($types as $type => $labels){
                $mtypes[$type]=$labels->labels['name'];
            }
        }

        $settings = [
            array(
                'label' => esc_html__('Who can create Groups.','vibebp'),
                'name' => 'can_create_group',
                'type' => 'select',
                'options'=> $mtypes,
                'desc' => '',
                'default'=>''
            ),
            array(
                'label' => esc_html__('BuddyPress Groups Custom Fields','vibebp'),
                'name' => 'group_custom_fields',
                'type' => 'group_fields',
                'desc' => '',
                'default'=>''
            ),
        ];

        $s = VibeBP_Settings::init();
        $s->vibebp_settings_generate_form('bp',$settings,'groups');
        return 1;
    }

    function group_fields($return,$setting){
        if($setting['type'] == 'group_fields'){

            echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
            echo '<td class="forminp"><a class="button-primary add_new_group_field" data-name="'.$setting['name'].'">'.esc_html__('Add Group Field','vibebp').'</a><ul class="group_field_list">';

            
            if(!empty($setting['value']) && is_array($setting['value'])){
                foreach($setting['value']['type'] as $k=>$val){
                    if(!empty($setting['value']['key'][$k])){
                    
                    echo '<li>
                                <label class="group_field_type">'.$setting['value']['label'][$k].' ['.$setting['value']['key'][$k].']</label>
                                <span class="field_type">'.$setting['value']['type'][$k].'</span>
                                <span class="field_desc">'.$setting['value']['desc'][$k].'</span>
                            <span class="remove_item dashicons dashicons-no-alt"></span>
                            <input type="hidden" name="'.$setting['name'].'[type][]" value="'.$setting['value']['type'][$k].'" />
                            <input type="hidden" name="'.$setting['name'].'[key][]" value="'.$setting['value']['key'][$k].'" />
                            <input type="hidden" name="'.$setting['name'].'[label][]" value="'.$setting['value']['label'][$k].'"/>
                            <input type="hidden" name="'.$setting['name'].'[options][]" value="'.(empty($setting['value']['options'][$k])?'':$setting['value']['options'][$k]).'"/>
                            <input type="hidden" name="'.$setting['name'].'[desc][]" value="'.$setting['value']['desc'][$k].'"/></li>';
                    }
                }
            }
            echo '</ul><span>'.$setting['desc'].'</span></td>';  


            ?>
            <script>
                jQuery(document).ready(function($){
                    $('.add_new_group_field').on('click',function(){
                        var $html = '<li class="new_field"><select class="group_field_type" name="'+$(this).attr('data-name')+'[type][]">';

                        $html +='<option value="text">Text Field</option>';
                        $html +='<option value="number">Number Field</option>';
                        $html +='<option value="date">Date Field</option>';
                        $html +='<option value="yesno">Switch</option>';
                        $html +='<option value="select">Select Drop</option>';
                        $html +='<option value="checkbox">Checkboxes</option>';
                        $html +='<option value="radio">Radio</option>';
                        $html +='<option value="editor">Editor</option>';
                        $html +='<option value="repeatable">Repeatable</option>';
                        $html +='<option value="product">WooCommerce Product</option>';
                        $html +='<option value="selectcpt">Post Type</option>';
                        $html +'</select>';
                        $html +='<input type="text" name="'+$(this).attr('data-name')+'[key][]" placeholder="Field Key" />';
                        $html +='<input type="text" name="'+$(this).attr('data-name')+'[label][]" placeholder="Field label" />';
                        $html +='<input type="text" name="'+$(this).attr('data-name')+'[options][]" placeholder="Options key;label|key;label " />';
                        $html +='<textarea name="'+$(this).attr('data-name')+'[desc][]" placeholder="Field Description"></textarea>';
                        $html +='<span class="remove_item dashicons dashicons-no-alt"></span>';
                        $html +='</li>';
                        $(this).parent().find('ul').append($html);
                        $('.remove_item').on('click',function(){
                            $(this).parent().remove();
                        });
                    });

                    $('.remove_item').on('click',function(){
                        $(this).parent().remove();
                    });
                });
            </script>
            <style>
                .group_field_list{display: flex;flex-direction: column; gap: 1rem;}
                .group_field_list li{display: flex;flex-wrap: wrap;gap: 1rem;align-items: center;justify-content: space-between;    background: #fff;padding: 10px;}.new_field textarea{flex: 1 0 320px}
                .new_field input{flex: 1 0 180px;}
                .group_field_list label{
                    font-size: 1.2rem;
                    font-weight: 600;
                   
                } .group_field_list .field_type{
                    font-size: 11px;
                    padding: 5px;
                    background: #ddd;border-radius: 5px;
                }.new_field{
                    padding: 1rem;background: #fff;
                    border: 1px solid #ddd;border-radius: 5px;
                    display: flex;position: relative;
                    flex-wrap: wrap;
                }.new_field > .field_Desc{
                    flex: 1;
                }.new_field .remove_item{
                    color: red;
                    position: absolute;
                    top: 0.5rem;
                    right:0.5rem;
                }
            </style>
            <?php

            return 1;
        }

        return $return;
    }
}	


VibeBP_Groups_Settings::init();
