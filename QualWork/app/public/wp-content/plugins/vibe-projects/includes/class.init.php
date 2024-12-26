<?php
/**
 * INIT\
 *
 * @class       Vibe_Projects_Init
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Vibe_Projects_Init{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Projects_Init();
        return self::$instance;
    }

	private function __construct(){


        add_filter('the_content',array($this,'add_front_end_project_div'),99);
        add_action('wp_enqueue_scripts',array($this,'enqueue_project'));
        add_filter('vibebp_component_icon',array($this,'set_icon'),99,2);

        add_action( 'before_delete_post',[$this,'delete_project_card_board'],10,2); 


        $default_tabs[]=['value'=>'reports','label'=>_x('Reports','project tab','vibe-projects')];

        add_filter('vibe_project_tabs',[$this,'project_components'],10,3); 

        add_filter('vibe_projects_project_create_form',[$this,'project_discussions'],10,2);
        add_action('vibe_projects_create_new_project',[$this,'create_forum'],10,3);
        add_filter('vibe_helpdesk_get_component_forum',[$this,'get_project_Forum'],10,2);

        
        add_filter( 'template_include', [$this,'public_board_page_template']);
        add_filter('the_content',[$this,'public_board']);
	}
        


    function public_board_page_template($template){
        
        if( get_post_Type() == 'board' && file_exists(get_template_directory() . '/fullwidth.php')){            
           return  locate_template ('fullwidth.php');         
        }
        return $template;
    }

    function showbadgeTitle($badge){
        if($badge['key'] == 'startdate' || $badge['key'] == 'duedate'){
            return $badge['label'].' : '.date_i18n( get_option('date_format'), $badge['value'] );
        }
        return $badge['label'];
    }

    function public_board($content){
        global $post;
        if( get_post_type() == 'board'){
            $public = get_post_meta($post->ID,'vibe_boards_visibility',true);
            $statuses = vibe_projects_get_statuses('card');
            $fields = vibebp_get_setting('create_card_fields','vibe_projects','cards');
            

            if(!empty($public) && $public == 'public'){
                $board_id=$post->ID;
                $lists = wp_get_object_terms($board_id,'list',array(
                        'orderby'    => 'meta_value_num',
                        'meta_key'   => 'order',
                        'order'      => 'ASC',
                        'meta_query' => array(
                            'relation'=>'AND',
                            array(
                                'key' => 'list_status',
                                'value' => 'archived',
                                'compare' => '!='
                            ),
                        )
                    )
                );

                if(!empty($lists) && !is_wp_error($lists)){
                    foreach($lists as $key=>$list){
                        $name = get_term_meta($list->term_id,'name',true);
                        if(!empty($name)){
                            $lists[$key]->name = $name;
                        }
                        unset($lists[$key]->taxonomy);
                        unset($lists[$key]->term_group);
                        unset($lists[$key]->filter);
                        unset($lists[$key]->term_taxonomy_id);

                        $api = Vibe_Boards_API::init();
                        $lists[$key]->cards=$api->get_cards_from_list($list->term_id,'full');
                    }
                }

                if(!empty($lists)){
                    $labels = get_post_meta($board_id,'vibe_board_labels',true);
                    
                    ob_start();
                    ?>
                    <div class="full_board flex flex-row gap-4">
                        <?php 
                        foreach($lists as $list){
                            ?>
                            <div class="flex flex-col gap-2 board_list border p-2 rounded">
                                <div class="list_head p-2 bg-slate-50">
                                    <?php  echo $list->name; ?>
                                </div>
                                <div class="list_card_wrapper flex flex-col gap-2">
                                    <?php 
                                    if(!empty($list->cards)){
                                        foreach($list->cards as $card){
                                            ?>
                                            <div class="card_wrapper flex flex-col gap-1 border rounded-lg p-2">
                                                <?php 
                                                if(!empty($card['labels'])){
                                                    echo '<div class="card_labels flex gap-1">';
                                                    foreach($card['labels'] as $label){
                                                        foreach($labels as $l){
                                                            if($l['id'] == $label){
                                                                echo '<span class="card_label p-1 tip rounded-lg" style="background:'.$l['color'].';" title="'.$l['label'].'"></span>';
                                                            }
                                                        }
                                                    }
                                                    echo '</div>';
                                                }
                                                ?>
                                                <h3><?php echo $card['title']; ?></h3>
                                                <p><?php echo substr($card['description'], 0,120); ?></p>
                                                <div class="card_vitals flex gap-1 items-center">
                                                    <?php 
                                                    foreach($statuses as $status){
                                                        if($post->post_status == $status['value']){
                                                            echo '<span class="card_status" style="background:'.$status['color'].'">'.$status['label'].'</span>';
                                                        }
                                                    }
                                                    ?>
                                                    <div class="flex gap-1 items-center">
                                                        <div class="progress_wrap">
                                                            <span class="increment" style="<?php echo 'width:'.$card['progress'];?>%"></span>
                                                        </div>
                                                        <?php echo $card['progress']; ?>%
                                                    </div>
                                                </div>
                                                <div class="card_meta flex items-center">
                                                <?php 
                                                if(!empty($card['icons'])){
                                                    foreach($card['icons'] as $icon){
                                                        echo '<span class="" title=""></span>';
                                                    }    
                                                }
                                                if(!empty($card['badges'])){
                                                    echo '<div class="flex gap-1">';
                                                     foreach($card['badges'] as $badge){
                                                        echo '<span class="'.$badge['icon'].' p-1 rounded" style="background:'.$badge['bg'].';color:'.$badge['color'].'" title="'.$this->showBadgeTitle($badge).'"></span>';
                                                    }
                                                    echo '</div>';
                                                }
                                                ?>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div><style>.full_board{overflow:auto;}.board_list{width:320px}.gap-1{gap:0.25rem;}
                    .p-1{padding:0.25rem}.progress_wrap { min-width: 80px;width:100%; display: flex; height: 5px; background: #eee; border-radius: 1rem; position: relative; overflow: hidden;}.increment{background:var(--primary);}.card_status{padding:1px 6px;border-radius:10px;color:#fff;}</style>
                    <?php
                    $content.= ob_get_clean();
                }
            }
        }

        return $content;
    }

    function set_icon($icon,$component_name){

        if($component_name == 'projects'){
            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-package"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path style="fill:none" d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>';
        }
        return $icon;
    }

    function get_project_Forum($return,$detail){

        if(!empty($detail['project'])){
            $return = get_post_meta($detail['project']['id'],'vibe_projects_forum_id',true);
        }

        return $return;
    }
    function project_components($tabs,$project_id,$user){

        $check = get_post_meta($project_id,'vibe_projects_forum',true);
        if($check == 'S'){
            $tabs[]=['value'=>'project_forum','label'=>__('Discussion','vibe-projects')];
        }

        $check = get_post_meta($project_id,'vibe_projects_drive',true);
        if($check == 'S'){
            $tabs[]=['value'=>'project_drive','label'=>__('Drive','vibe-projects')];
        }
        return $tabs;
    }

    function project_discussions($args,$project_id){

        if(function_exists('vibe_helpdesk_translations') && function_exists('bbpress')){
            
            $args[]=array(
                    'label'=> __('Discussion Forums','vibe-projects' ),
                    'type'=> 'switch',
                    'style'=>'',
                    'value_type'=>'single',
                    'id' => 'vibe_projects_forum',
                    'from'=>'meta',
                    'noscript'=>true,
                    'value'=> empty($project_id)? 'public':get_post_meta($project_id,'vibe_projects_forum',true),
                    'desc'=> __('Enable discussion board for the Project.','vibe-projects' ),
                );
        }

        if(function_exists('vibebp_vibedrive_plugin_update')){
            $args[]=array(
                    'label'=> __('Project Drive','vibe-projects' ),
                    'type'=> 'switch',
                    'style'=>'',
                    'value_type'=>'single',
                    'id' => 'vibe_projects_drive',
                    'from'=>'meta',
                    'noscript'=>true,
                    'value'=> empty($project_id)? 'public':get_post_meta($project_id,'vibe_projects_drive',true),
                    'desc'=> __('Share files among project members.','vibe-projects' ),
                );
        }
        return $args;
    }

    function create_forum($project_id,$args,$user){

        $flag=false;

        if(!empty($args['meta'])){
            foreach($args['meta'] as $meta){
                if($meta['meta_key'] == 'vibe_projects_forum' && $meta['meta_value'] == 'S'){
                    $flag=true;
                }
            }
        }

        if($flag  && function_exists('bbpress')){
           
            $forum_id = get_post_meta($project_id,'vibe_projects_forum_id',true);
            if(empty($forum_id)){
                $forum_parent_id = 0;
                if(function_exists('vibebp_get_setting')){
                   $forum_parent_id = vibebp_get_setting('bbp_parent_forum','helpdesk');
                }
                
                if(!empty($forum_parent_id)){
                    $forum_data = apply_filters( 'bbp_new_forum_pre_insert', array(
                        'post_author'    => $user->id,
                        'post_title'     => get_the_title($project_id),
                        'post_content'   => get_the_content($project_id),
                        'post_parent'    => $forum_parent_id,
                        'post_status'    => 'private',
                        'post_type'      => bbp_get_forum_post_type(),
                        'comment_status' => 'closed'
                    ) );
                    // Insert forum
                    $forum_id = bbp_insert_forum( $forum_data, ['forum_id'=>$forum_parent_id] );
                    update_post_meta($project_id,'vibe_projects_forum_id',$forum_id);
                }
            }
        }
    }

    function delete_project_card_board($post_id,$post){
        //cleanup of data
        if($post->post_type == 'project'){
            global $wpdb;
           // delete_user_meta()
            $boards = $wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_parent=%d",$post->ID),ARRAY_A);
            if(!empty($boards)){
                foreach($boards as $board){
                    $terms = get_the_terms( $board['ID'], 'list' );
                    if(!empty($terms)){
                        foreach($terms as $term){
                            wp_delete_term($term->term_id,'list');
                        }    
                    }
                    wp_delete_post($board['ID']);
                }
            }
            $cards = $wpdb->get_results("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'vibe_card_project' AND meta_value=$post->ID");
            if(!empty($cards)){
                foreach($cards as $card){
                    wp_delete_post($card->post_id);
                    $metas = $wpdb->get_results($wpdb->prepare("
                        SELECT user_id 
                        FROM {$wpdb->usermeta} 
                        WHERE meta_key = 'vibe_project_card_member' AND meta_value = %d
                    ",$card->post_id),ARRAY_A);
                    if(!empty($metas)){
                        foreach($metas as $meta){
                            delete_user_meta($meta['user_id'],'vibe_project_card_member',$card->post_id);
                        }
                    }
                    
                }
            }

            $forum_id = get_post_meta($post_id,'vibe_projects_forum_id',true);
            if(!empty($forum_id) && function_exists('bbp_delete_forum')){
                bbp_delete_forum($forum_id);    
            }
            
        }

        if($post->post_type == 'board'){
            $terms = get_the_terms( $post->ID, 'list' );
            if(!empty($terms)){
                foreach($terms as $term){
                    wp_delete_term($term->term_id,'list');
                }    
            }
        }

        if($post->post_type == 'card'){
            global $wpdb;
            $metas = $wpdb->get_results("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'vibe_project_card_member' AND meta_value = $post->ID");
            if(!empty($meta)){
                foreach($metas as $meta){
                    remove_user_meta($meta['user_id'],'vibe_project_card_member',$post->ID);
                }
            }
        }
    }   


    function add_front_end_project_div($content){

        if(get_post_type() !== 'project')
            return $content;

        return '<div id="vibe_projects_fullproject_front_end" data-project_id="'.get_the_ID().'">'.$content.'</div>';
    }
    function enqueue_project(){
        if(get_post_type() !== 'project')
            return;

        $pr = Vibe_Projects_Profile::init();
        $js_vars = $pr->enqueue_project_vars();
        wp_enqueue_script('vibe-projects',plugins_url('../assets/js/fullproject.js',__FILE__),array('wp-element','wp-data'),VIBEPROJECTS_VERSION,true);

        wp_localize_script('vibe-projects','vibeprojects',$js_vars);
        wp_enqueue_style('vibe-project_fullproject',plugins_url('../assets/css/vibe_projects.css',__FILE__),array(),VIBEPROJECTS_VERSION,true);
    }
}
Vibe_Projects_Init::init();