<?php
/**
 * Settings in Admin
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	wplms_chat/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define('VIBE_SAAS_OPTIONS','VIBE_SAAS_OPTIONS');
class Vibe_Saas_Migration_Settings{

	public static $instance;
    private  $steps;
    private  $media_imported;
    private  $per_page;
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Saas_Migration_Settings();
        return self::$instance;
    }

	private function __construct(){
        $this->media_imported= [];
        $this->steps = [
            
            //'generate_media',
            'generate_assignments',
            'generate_questions',
            'generate_quizzes',
            'generate_units',
            'generate_courses',
            'generate_taxonomies',
            'generate_taxonomy_relations',
            

        ];
        $this->per_page= apply_filters('vibe_migration_step_length',50);
		add_action('admin_menu',array($this,'initialize'));
        foreach ($this->steps as $key => $step) {
         
            add_action('wp_ajax_'.$step,[$this,$step]);
        }
        add_action('wp_ajax_download_saa_zip_file',[$this,'download_saa_zip_file']);
	}

    function createZipFromDir($dir, $zip_file) {
        if(!class_exists('ZipArchive')){
            echo 'ZipArchive does not exist';
            return;
        }
            

        @unlink($zip_file);
         $zip = new ZipArchive();
        if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            echo '<div class="notice notice-error"><p>Cannot create zip file.</p></div>';
            return;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            $file_path = $dir . '/' . $file;
            if (is_file($file_path)) {
                $zip->addFile($file_path, $file);
            }
        }

        $zip->close();    
    }

    function download_saa_zip_file(){
        if(empty($_POST)){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        if(!wp_verify_nonce(sanitize_text_field($_POST['saas_security']),'saas_security')){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        if(!current_user_can('manage_options'))
            die();

        $dir = wp_upload_dir();
        $filepath = $dir['basedir'] . '/saasexport/';
        if(file_exists($dir['basedir'] . '/saasexport/'.'saasexport.zip')){
            @unlink($dir['basedir'] . '/saasexport/'.'saasexport.zip');
        }
        $this->createZipFromDir($filepath,$dir['basedir'] . '/saasexport/'.'saasexport.zip');
        $url = $dir['baseurl'] . '/saasexport/saasexport.zip';
        delete_option('wplms_saas_media');
        print_R(json_encode(['url'=>$url.'?'.time()]));
        die();

    }

    function generate_media(){
        if(empty($_POST)){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        if(!wp_verify_nonce(sanitize_text_field($_POST['saas_security']),'saas_security')){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        $page = intval($_POST['page']);
        if($page){
            global $wpdb;
            $offset=$this->per_page*($page-1);
            $limit =$this->per_page;
            $posts = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type='attachment' LIMIT {$offset},{$limit}");
            $data = [];
            foreach ($posts as $key => $post) {
                $data[] = [
                    'id'=>$post->ID,
                    'url'=>$post->guid,
                    'name'=>$post->post_title,
                    'mime_type'=>$post->post_mime_type
                ];
            }
            if(empty($data)){
                print_r(json_encode(array('done'=>1)));
            }else{
                print_r(json_encode($data));
                $this->build_file('media',$data,$page);
            }
        }
        die();
    }

    function generate_units(){
        $meta_keys = ['vibe_type','vibe_post_video','vibe_duration','vibe_unit_duration_parameter','vibe_free','vibe_unit_attachments','vibe_practice_questions'];
        if(empty($_POST)){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        if(!wp_verify_nonce(sanitize_text_field($_POST['saas_security']),'saas_security')){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        $page = intval($_POST['page']);
        if($page){
            global $wpdb;
            $offset=$this->per_page*($page-1);
            $limit =$this->per_page;
            $posts = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type='unit' LIMIT {$offset},{$limit}");
            $data = [];
            $this->media_imported = get_option('wplms_saas_media');
            if(empty($this->media_imported) || !is_array($this->media_imported)){
                $this->media_imported=[];
            }
            foreach ($posts as $key => $post) {
                /*title slug content settings type duration*/
                $m_keys_join = implode(',', array_map(function($item){
                    return "'".$item."'";
                }, $meta_keys));
                $meta_arr = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE post_id={$post->ID} AND meta_key IN($m_keys_join)");
                $settings = [];
                $type = '';
                $duration_value = 0;
                $duration_parameter=0;
                foreach ($meta_arr as $key => $meta) {
                    if($meta->meta_key=='vibe_type'){
                        $type = $meta->meta_value;
                        if($type=='text-document' || $type='elementor' || $type='multimedia'){
                            $type = 'general';
                        }
                    }
                    if($meta->meta_key=='vibe_duration'){
                        $duration_value=$meta->meta_value;
                    }
                    if($meta->meta_key=='vibe_unit_duration_parameter'){
                        $duration_parameter=$meta->meta_value;
                    }
                    if($meta->meta_key=='vibe_free'){
                        $settings['vibe_free'] = ($meta->meta_value==='S'?true:false);
                    }
                    if($meta->meta_key=='vibe_practice_questions'){
                        $settings['practice_questions'] = maybe_unserialize($meta->meta_value);
                    }
                    if($meta->meta_key=='vibe_unit_attachments'){
                        $settings['vibe_unit_attachments'] = maybe_unserialize($meta->meta_value);
                    }
                    if($meta->meta_key=='vibe_post_video'){
                        
                        $meta->meta_value = maybe_unserialize($meta->meta_value);
                        if(!empty($meta->meta_value)){
                            if(!empty($meta->meta_value['type']) && !empty($meta->meta_value['url'])){
                                if($meta->meta_value['type']=='youtube'){
                                    $id = '';
                                    $type ='youtube';
                                    if(strpos($meta->meta_value['url'], 'https://www.youtube.com/watch?v=')!== false){
                                        $id = str_replace('https://www.youtube.com/watch?v=', '', $meta->meta_value['url']);
                                    }else{
                                        preg_match('/https:\/\/youtu.be\/(.*)\?/', $meta->meta_value['url'], $output_array);
                                        if(!empty($output_array)){
                                           $id =  $output_array[1];
                                        }
                                    }
                                    $settings['vibe_post_video'] = [
                                        'id'=>$id,
                                        'url'=>$meta->meta_value['url']
                                    ];
                                }else if($meta->meta_value['type']=='vimeo'){
                                    $type ='vimeo';
                                    $id = '';
                                    if(strpos($meta->meta_value['url'], 'https://vimeo.com/')!== false){
                                        $id = str_replace('https://vimeo.com/', '', $meta->meta_value['url']);
                                    }
                                   
                                    $settings['vibe_post_video'] = [
                                        'id'=>$id,
                                        'url'=>$meta->meta_value['url']
                                    ];
                                }else if($meta->meta_value['type']=='video'){
                                    $type ='video';
                                    
                                    $id = '';
                                    $settings['vibe_post_video'] = [
                                        'id'=>(!empty($meta->meta_value['id'])?$meta->meta_value['id']:''),
                                        'url'=>$meta->meta_value['url'],
                                        "videoId"=>'',
                                    ];
                                }
                            }
                        } 
                    }
                }
                $duration = 0;
                if(!empty($duration_value)){
                    $duration = intval($duration_parameter) * intval($duration_value);
                }
                $post->post_content = $this->parse_math($post->post_content);
                $parsed_media = $this->parse_media($post->post_content);

                if(!empty($parsed_media['media'])){
                    $post->post_content = $parsed_media['content'];
                    for($i=0;$i<count($parsed_media['media']);$i++){
                        if(!in_array($parsed_media['media'][$i], $this->media_imported)){
                            $this->media_imported[] = $parsed_media['media'][$i];
                            $medias[]=$parsed_media['media'][$i];
                        }
                        
                    }
                }
                $data[] = [
                    'id'=>$post->ID,
                    'title'=>$post->post_title,
                    'slug'=>$post->post_name,
                    'content'=>$post->post_content,
                    'settings'=>json_encode($settings),
                    'type'=>$type,
                    'duration'=>$duration,
                    'status'=>$post->post_status
                ];
            }
            if(empty($data)){
                print_r(json_encode(array('done'=>1)));
            }else{
                print_r(json_encode($data));
                $this->build_file('unit',$data,$page);
                if(!empty($medias)){
                    $medias = array_map(function($media){
                        return ['url'=>$media];
                    },$medias);
                    $this->build_file('media',$medias,0);
                }
            }
            
        }



        die();
        
    }

    function generate_questions(){
        $meta_keys = ['vibe_question_type','vibe_question_explaination','vibe_question_answer','vibe_question_hint','vibe_question_options','vibe_question_difficulty'];
        if(empty($_POST)){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        if(!wp_verify_nonce(sanitize_text_field($_POST['saas_security']),'saas_security')){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        $page = intval($_POST['page']);
        if($page){
            global $wpdb;
            $offset=$this->per_page*($page-1);
            $limit =$this->per_page;
            $posts = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type='question' LIMIT {$offset},{$limit}");
            $data = [];
            $this->media_imported = get_option('wplms_saas_media');
            if(empty($this->media_imported) || !is_array($this->media_imported)){
                $this->media_imported=[];
            }
            foreach ($posts as $key => $post) {
                /*title slug content settings type duration*/
                $m_keys_join = implode(',', array_map(function($item){
                    return "'".$item."'";
                }, $meta_keys));
                $meta_arr = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE post_id={$post->ID} AND meta_key IN($m_keys_join)");
                $settings = [];
                $type = '';
                $difficulty = 0;
                foreach ($meta_arr as $key => $meta) {
                    switch($meta->meta_key){
                        case 'vibe_question_type':
                            $type = $meta->meta_value;
                        break;
                        case 'vibe_question_difficulty':
                            $difficulty  = $meta->meta_value;
                        break;
                        case 'vibe_question_explaination':
                        case 'vibe_question_hint':
                        
                            $meta->meta_value  = $this->parse_math($meta->meta_value);
                            
                            $parsed_media = $this->parse_media($meta->meta_value);

                            if(!empty($parsed_media['media'])){
                                $meta->meta_value = $parsed_media['content'];
                                for($i=0;$i<count($parsed_media['media']);$i++){
                                    if(!in_array( $parsed_media['media'][$i], $this->media_imported)){
                                        $this->media_imported[] = $parsed_media['media'][$i];
                                        $medias[]=$parsed_media['media'][$i];
                                    }
                                    
                                }
                            }
                            $settings[$meta->meta_key]=$meta->meta_value;
                            
                        break;
                        case 'vibe_question_options':
                            $options = maybe_unserialize($meta->meta_value);
                            if(!empty($options)){
                                foreach ($options as $key => $option) {
                                    $options[$key]  = $this->parse_math($option);
                                    $parsed_media = $this->parse_media($option);

                                    if(!empty($parsed_media['media'])){
                                        $option = $parsed_media['content'];
                                        for($i=0;$i<count($parsed_media['media']);$i++){
                                            if(!in_array($parsed_media['media'][$i], $this->media_imported)){
                                                $this->media_imported[] = $parsed_media['media'][$i];
                                                $medias[]=$parsed_media['media'][$i];
                                            }
                                            
                                        }
                                    }
                                }
                            }


                            $settings[$meta->meta_key]=$options;
                        break;
                        default:
                            $settings[$meta->meta_key] = maybe_unserialize($meta->meta_value);
                        break;
                    }
                   
                }
                $post->post_content = $this->parse_math($post->post_content);
                
                if($type=='match'){
                    if(!empty($settings['vibe_question_options'])){
                        $options = $settings['vibe_question_options'];
                        $settings['vibe_question_options'] = [];
                        $settings['vibe_question_options']['drop_options'] = $options;


                        $content = apply_filters('vibebp_the_content',$post->post_content,$post->ID);
    
                        

                        $re = '/(.*)\[match\](.*)\[\/match\](.*)/s';
                        preg_match_all($re, $content, $content_array, PREG_SET_ORDER, 0);
                        if(!empty($content_array) && !empty($content_array)){
                            
                            preg_match_all("/<li\b[^>]*>(.*?)<\/li>/", apply_filters('wplms_question_preg_match_content',$content_array[0][2]), $s_items);
                            if(!empty($s_items)){
                                $shortcode_items = [];
                                foreach($s_items[1] as $i=>$item){
                                    $shortcode_items[$i] = do_shortcode($item);
                                }
                                
                                $post->post_content = apply_filters('the_content',$content_array[0][1].$content_array[0][3]);
                                $settings['vibe_question_options']['drag_options'] = $shortcode_items;
                            }
                            
                        } 
                    }
                    if(!empty($settings['vibe_question_answer'])){
                        $settings['vibe_question_answer'] = explode(',', $settings['vibe_question_answer']);
                    }
                }
                $post->post_content = do_shortcode($post->post_content);
                $parsed_media = $this->parse_media($post->post_content);

                if(!empty($parsed_media['media'])){
                    $post->post_content = $parsed_media['content'];
                    for($i=0;$i<count($parsed_media['media']);$i++){
                        if(!in_array($parsed_media['media'][$i], $this->media_imported)){
                            $this->media_imported[] = $parsed_media['media'][$i];
                            $medias[]=$parsed_media['media'][$i];
                        }
                        
                    }
                }
                
                $data[] = [
                    'id'=>$post->ID,
                    'title'=>$post->post_title,
                    'slug'=>$post->post_name,
                    'content'=>$post->post_content,
                    'settings'=>json_encode($settings),
                    'type'=>$type,
                    'difficulty'=>$difficulty,
                    'status'=>$post->post_status
                ];
            }
            if(empty($data)){
                print_r(json_encode(array('done'=>1)));
            }else{
                print_r(json_encode($data));
                $this->build_file('question',$data,$page);
                if(!empty($medias)){
                    $medias = array_map(function($media){
                        return ['url'=>$media];
                    },$medias);
                    $this->build_file('media',$medias,0);
                }
            }
        }
        die();
    }

    function generate_quizzes(){
        $meta_keys = ['vibe_duration','vibe_quiz_duration_parameter','vibe_quiz_retakes','vibe_quiz_questions','vibe_type','vibe_quiz_course','vibe_quiz_auto_evaluate','vibe_question_duration','vibe_question_number_react','vibe_quiz_retakes','vibe_quiz_message','vibe_quiz_check_answer','vibe_start_time','dynamic_quiz_questions','vibe_start_time','vibe_end_time','vibe_hide_submit_button','vibe_quiz_passing_score','vibe_quiz_random','vibe_quiz_course','vibe_quiz_negative_marks_per_question','vibe_quiz_questions'];
        if(empty($_POST)){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        if(!wp_verify_nonce(sanitize_text_field($_POST['saas_security']),'saas_security')){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        $page = intval($_POST['page']);
        if($page){
            global $wpdb;
            $offset=$this->per_page*($page-1);
            $limit =$this->per_page;
            $posts = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type='quiz' LIMIT {$offset},{$limit}");
            $data = [];
            $this->media_imported = get_option('wplms_saas_media');
            if(empty($this->media_imported) || !is_array($this->media_imported)){
                $this->media_imported=[];
            }
            foreach ($posts as $key => $post) {
                /*title slug content settings type duration*/
                $m_keys_join = implode(',', array_map(function($item){
                    return "'".$item."'";
                }, $meta_keys));
                $meta_arr = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE post_id={$post->ID} AND meta_key IN($m_keys_join)");
                $settings = [];
                $type = '';
                $duration_value = 0;
                $duration_parameter=0;
                foreach ($meta_arr as $key => $meta) {
                    switch($meta->meta_key){
                        case 'vibe_type':
                            $type = $meta->meta_value;
                        break;
                        case 'vibe_question_number_react':
                            $settings['question_number'] = $meta->meta_value;
                        break;
                        case 'vibe_duration':
                            $duration_value = $meta->meta_value;
                        break;
                        case 'vibe_quiz_duration_parameter':
                            $duration_parameter = $meta->meta_value;
                        break;
                        
                        case 'vibe_question_number_react':
                            $duration_parameter = $meta->meta_value;
                        break;
                        case 'vibe_quiz_retakes':
                            $settings['retakes'] = $meta->meta_value;
                        break;
                        case 'vibe_quiz_check_answer':
                            $settings['check_answer'] = ($meta->meta_value=='S'?true:false);
                        
                        break;
                        case 'vibe_quiz_auto_evaluate':
                            $settings['vibe_quiz_auto_evaluate'] = ($meta->meta_value=='S'?true:false);
                        
                        break;
                        case 'vibe_question_number_react':
                            $settings['question_number'] = $meta->meta_value;
                        
                        break;
                        case 'vibe_quiz_message':
                            $settings['completion_message'] = $meta->meta_value;
                        
                        break;
                        case 'vibe_quiz_message':
                            $settings['completion_message'] = $this->parse_math($meta->meta_value);
                        
                        break;
                        case 'dynamic_quiz_questions':
                            $settings['dynamic_quiz_questions'] = maybe_unserialize($meta->meta_value);
                        
                        break;
                        case 'vibe_quiz_negative_marks_per_question':
                            $settings['negative_marks'] = $meta->meta_value;
                        
                        break;
                        case 'vibe_quiz_passing_score':
                            $settings['quiz_passing_score'] = $meta->meta_value;
                        
                        break;
                        
                        case 'vibe_quiz_questions':
                            $questions= [];
                            $value = maybe_unserialize($meta->meta_value);
                            if(!empty($value) && !empty($value['ques'])){
                                foreach ($value['ques'] as $key => $qid) {
                                   
                                    $qtype = get_post_meta($qid,'vibe_question_type',true);
                                    $questions[] = [
                                        'data'=>[
                                            'id'=>$qid,
                                            "text"=>get_the_title($qid),
                                            "type"=> $qtype,
                                        ],
                                        "type"=> $qtype,
                                        "marks"=> (!empty($value['marks'][$key])?$value['marks'][$key]:0)
                                    ];
                                }
                            }
                            $settings['vibe_quiz_questions'] = $questions;
                        break;
                        default:
                            $settings[$meta->meta_key] = maybe_unserialize($meta->meta_value);
                        break;
                    }
                   
                }
                $duration = 0;
                if(!empty($duration_value)){
                    $duration = intval($duration_value)*intval($duration_parameter);
                }

                $post->post_content = do_shortcode($post->post_content);

                $post->post_content = $this->parse_math($post->post_content);
                $parsed_media = $this->parse_media($post->post_content);

                if(!empty($parsed_media['media'])){
                    $post->post_content = $parsed_media['content'];
                    for($i=0;$i<count($parsed_media['media']);$i++){
                        if(!in_array($parsed_media['media'][$i], $this->media_imported)){
                            $this->media_imported[] = $parsed_media['media'][$i];
                            $medias[]=$parsed_media['media'][$i];
                        }
                        
                    }
                }
                $data[] = [
                    'id'=>$post->ID,
                    'title'=>$post->post_title,
                    'slug'=>$post->post_name,
                    'content'=>$post->post_content,
                    'settings'=>json_encode($settings),
                    'type'=>$type,
                    'duration'=>$duration,
                    'status'=>$post->post_status
                ];
            }
            if(empty($data)){
                print_r(json_encode(array('done'=>1)));
            }else{
                print_r(json_encode($data));
                $this->build_file('quiz',$data,$page);
                if(!empty($medias)){
                    $medias = array_map(function($media){
                        return ['url'=>$media];
                    },$medias);
                    $this->build_file('media',$medias,0);
                }
            }
        }
        die();
    }

    function generate_assignments(){
        if(!current_user_can('manage_options'))die();
        $meta_keys = ['vibe_assignment_submission_type','vibe_assignment_marks','vibe_assignment_duration','vibe_assignment_duration_parameter','vibe_attachment_type','vibe_attachment_size','vibe_start_time','vibe_end_time'];
        if(empty($_POST)){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        if(!wp_verify_nonce(sanitize_text_field($_POST['saas_security']),'saas_security')){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        $page = intval($_POST['page']);
        if($page){
            global $wpdb;
            $offset=$this->per_page*($page-1);
            $limit =$this->per_page;
            $posts = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type='wplms-assignment' LIMIT {$offset},{$limit}");
            $this->media_imported = get_option('wplms_saas_media');
            if(empty($this->media_imported) || !is_array($this->media_imported)){
                $this->media_imported=[];
            }
            $data = [];
            foreach ($posts as $key => $post) {
                /*title slug content settings type duration*/
                $m_keys_join = implode(',', array_map(function($item){
                    return "'".$item."'";
                }, $meta_keys));
                $meta_arr = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE post_id={$post->ID} AND meta_key IN($m_keys_join)");
                $settings = [];
                $type = '';
                $duration_value = 0;
                $duration_parameter=0;
                foreach ($meta_arr as $key => $meta) {
                    switch($meta->meta_key){
                        case 'vibe_assignment_submission_type':
                            $type = $meta->meta_value;
                        break;
                        case 'vibe_assignment_duration':
                            $duration_value=$meta->meta_value;
                        break;
                        case 'vibe_assignment_duration_parameter':
                            $duration_parameter=$meta->meta_value;
                        break;
                        case 'vibe_attachment_type':
                            $values = maybe_unserialize($meta->meta_value);
                            if(!empty($values)){
                                $values = array_map(function($item){
                                    return ['value'=>$item,'label'=>$item];
                                }, $values);
                            }
                            $settings['vibe_attachment_type'] = $values;
                        break;
                        default:
                            $settings[$meta->meta_key] = maybe_unserialize($meta->meta_value);
                        break;
                    }
                   
                }
                $duration = 0;
                if(!empty($duration_value)){
                    $duration = intval($duration_parameter) * intval($duration_value);
                }
                $post->post_content = do_shortcode($post->post_content);
                $post->post_content = $this->parse_math($post->post_content);
                $parsed_media = $this->parse_media($post->post_content);

                if(!empty($parsed_media['media'])){
                    $post->post_content = $parsed_media['content'];
                    for($i=0;$i<count($parsed_media['media']);$i++){
                        if(!in_array($parsed_media['media'][$i], $this->media_imported)){
                            $this->media_imported[] = $parsed_media['media'][$i];
                            $medias[]=$parsed_media['media'][$i];
                        }
                        
                    }
                }
                $data[] = [
                    'id'=>$post->ID,
                    'title'=>$post->post_title,
                    'slug'=>$post->post_name,
                    'content'=>$post->post_content,
                    'settings'=>json_encode($settings),
                    'type'=>$type,
                    'duration'=>$duration,
                    'status'=>$post->post_status
                ];
            }
            if(empty($data)){
                print_r(json_encode(array('done'=>1)));
            }else{
                print_r(json_encode(array('done'=>0)));
                $this->build_file('assignment',$data,$page);

                
            }
            if(!empty($medias)){
                $medias = array_map(function($media){
                    return ['url'=>$media];
                },$medias);
                if($page==1){
                    $this->build_file('media',$medias,1);
                }else{
                    $this->build_file('media',$medias,0);
                }
            }else{
                if($page==1){
                    //initialising the csv
                    $dir = wp_upload_dir();
                    $file_name = 'export_media.csv';
                    $filepath = $dir['basedir'] . '/saasexport/';
                    if(!file_exists($filepath))
                        mkdir($filepath,0755);

                    $file = $filepath.$file_name;

                    if($page==1){
                        if(file_exists($file))
                            @unlink($file);
                    }

                    if (($handle = fopen($file, "a")) !== FALSE) {
                        fputcsv($handle, ['url']); 
                    }

                    fclose($handle);
                }
            }
            
        }
        die();
    }

    function generate_courses(){
        $meta_keys = ['vibe_duration','vibe_course_duration_parameter','vibe_max_students','vibe_start_date','vibe_course_auto_eval','vibe_pre_course','vibe_course_prev_unit_quiz_lock','vibe_course_offline','vibe_course_unit_content','vibe_course_button','vibe_course_progress','vibe_course_auto_progress','vibe_course_review',

            'vibe_course_drip','vibe_course_drip_origin','vibe_course_section_drip','vibe_course_drip_duration_type','vibe_course_drip_duration','vibe_course_drip_duration_parameter',

            'vibe_course_curriculum',

            'vibe_course_certificate','vibe_course_passing_percentage','vibe_certificate_template',

            'vibe_badge','vibe_course_badge_percentage','vibe_course_badge_title','vibe_course_badge',

            'vibe_course_retakes','vibe_course_instructions','vibe_course_message',

            'vibe_forum','vibe_group',

            'vibe_course_free','vibe_product','vibe_course_apply',
        ];
        if(empty($_POST)){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        if(!wp_verify_nonce(sanitize_text_field($_POST['saas_security']),'saas_security')){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        $page = intval($_POST['page']);
        if($page){
            global $wpdb;
            $offset=$this->per_page*($page-1);
            $limit =$this->per_page;
            $posts = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type='course' LIMIT {$offset},{$limit}");
            $data = [];
            $this->media_imported = get_option('wplms_saas_media');
            if(empty($this->media_imported) || !is_array($this->media_imported)){
                $this->media_imported=[];
            }
            $medias = [];
            foreach ($posts as $key => $post) {
                /*title slug content settings type duration*/
                $m_keys_join = implode(',', array_map(function($item){
                    return "'".$item."'";
                }, $meta_keys));
                $meta_arr = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE post_id={$post->ID} AND meta_key IN($m_keys_join)");
                $seats = 0;
                $start_date='';
                $student_count = 0;
                $type = '';
                $duration_value = 0;
                $duration_parameter=0;
                $settings= [];
                $product_duration= [];
                $product = '';
                $meta_array= ['drip'=>[],'courseDuration'=>[],'settings'=>[],'curriculum'=>[],'requiredCourses'=>[],'storage_meta'=>[],'pricePlan'=>[]];
                $featured = get_the_post_thumbnail_url($post->ID);
                if(!empty($featured)){
                    if(!in_array($featured, $this->media_imported)){
                        $this->media_imported[] = $featured;
                        $medias[]=$featured;
                        
                    }
                    $featured = '[REPLACE_IT]'.$featured.'[|REPLACE_IT]';
                    
                }

                $drip_duration = [];
                foreach ($meta_arr as $key => $meta) {
                    if(!empty($meta->meta_value)){
                        switch($meta->meta_key){
                            
                            case 'vibe_question_number_react':
                                $settings['question_number'] = $meta->meta_value;
                            break;
                            case 'vibe_duration':
                                $duration_value = $meta->meta_value;
                            break;
                            case 'vibe_course_duration_parameter':
                                $duration_parameter = $meta->meta_value;
                            break;
                            case 'vibe_max_students':
                                $seats = $meta->meta_value;
                            break;
                            case 'vibe_start_date':
                                
                                $start_date = $meta->meta_value;
                            break;
                            
                            case 'vibe_course_auto_eval':
                                $meta_array['settings'][] = ['id'=>'courseAutoEval','value'=>($meta->meta_value=='S'?true:false)];
                            break;
                            case 'vibe_pre_course':
                                $value = maybe_unserialize($meta->meta_value);
                                if(!empty( $value)){
                                    foreach ( $value as $key => $c) {
                                        $meta_array['requiredCourses'][] = ['value'=>$c,'label'=>get_the_title($c)];
                                    }
                                }
                            break;
                            case 'vibe_course_prev_unit_quiz_lock';
                                $meta_array['settings'][] = ['id'=>'unitLock','value'=>($meta->meta_value=='S'?true:false)];
                            break;
                            
                            /*'vibe_course_drip','vibe_course_drip_origin','vibe_course_section_drip','vibe_course_drip_duration_type','vibe_course_drip_duration','vibe_course_drip_duration_parameter',*/
                            case 'vibe_course_drip':
                                $meta_array['drip'][] = ['id'=>'dripFeed','value'=>($meta->meta_value=='S'?true:false)];
                            
                            break;
                            case 'vibe_course_drip_origin':
                                $meta_array['drip'][] = ['id'=>'dripFeedStartingPoint','value'=>($meta->meta_value=='S'?true:false)];
                            
                            break;
                            case 'vibe_course_section_drip':
                                $meta_array['drip'][] = ['id'=>'dripFeedsection','value'=>($meta->meta_value=='S'?true:false)];
                            
                            break;
                            case 'vibe_course_drip_duration_type':
                                $meta_array['drip'][] = ['id'=>'dripFeedUnitDuration','value'=>($meta->meta_value=='S'?true:false)];
                            
                            break;
                            case 'vibe_course_drip_duration':
                                $drip_duration['value'] = $meta->meta_value;
                            break;
                            case 'vibe_course_drip_duration_parameter':
                                $drip_duration['parameter'] = $meta->meta_value;
                            break;
                            
                            /*'vibe_course_certificate','vibe_course_passing_percentage','vibe_certificate_template',*/
                            case 'vibe_course_certificate':
                                $meta_array['settings'][] = ['id'=>'courseCertificate','value'=>($meta->meta_value=='S'?true:false)];
                            
                            break;
                            case 'vibe_course_passing_percentage':
                                $meta_array['settings'][] = ['id'=>'courseCertificatePercentage','value'=>$meta->meta_value];
                            
                            break;
                            case 'vibe_certificate_template':
                                $meta_array['settings'][] = ['id'=>'courseCertificateTemplate','value'=>['value'=>$meta->meta_value,'label'=>get_the_title($meta->meta_value)]];
                            break;
                            /*'vibe_course_retakes','vibe_course_instructions','vibe_course_message',*/
                            case 'vibe_course_retakes':
                                $meta_array['settings'][] = ['id'=>'retakes','value'=>$meta->meta_value];
                            break;
                            case 'vibe_course_instructions':
                                
                                $parsed_media = $this->parse_media($meta->meta_value);

                                if(!empty($parsed_media['media'])){
                                    $meta->meta_value = $parsed_media['content'];
                                    for($i=0;$i<count($parsed_media['media']);$i++){
                                        if(!in_array($parsed_media['media'][$i], $this->media_imported)){
                                            $this->media_imported[] = $parsed_media['media'][$i];
                                            $medias[]=$parsed_media['media'][$i];
                                        }
                                        
                                    }
                                }
                                $meta_array['storage_meta']['instructions.txt'] = $this->parse_math($meta->meta_value);
                                

                            break;
                            case 'vibe_course_message':
                                
                                $parsed_media = $this->parse_media($meta->meta_value);

                                if(!empty($parsed_media['media'])){
                                    $meta->meta_value = $parsed_media['content'];
                                    for($i=0;$i<count($parsed_media['media']);$i++){
                                        if(!in_array($parsed_media['media'][$i], $this->media_imported)){
                                            $this->media_imported[] = $parsed_media['media'][$i];
                                            $medias[]=$parsed_media['media'][$i];
                                        }
                                        
                                    }
                                }
                                $meta_array['storage_meta']['completion.txt'] = $this->parse_math($meta->meta_value);
                            break;
                            /*'vibe_course_free','vibe_product','vibe_course_apply',*/
                            case 'vibe_course_free':
                                $meta_array['settings'][] = ['id'=>'courseFree','value'=>($meta->meta_value=='S'?true:false)];
                            break;

                            case 'vibe_course_curriculum':
                                $curriculum = maybe_unserialize($meta->meta_value);
                                if(!empty($curriculum) && is_array($curriculum)){
                                     
                                    $new_curriculum = [];
                                    foreach ($curriculum as $key => $c) {
                                        /*description:""
                                        duration: 0
                                        post_type: "section"
                                        title:"Section 1"
                                        type:""*/


                                        if(is_numeric($c)){
                                            $type = '';
                                            $post_type = get_post_type($c);
                                            $item_type = wplms_get_element_type($c,$post_type);
                                            $duration = 0;
                                            switch ($post_type) {
                                                case 'unit':
                                                    $type = 'units';
                                                    $value = get_post_meta($c,'vibe_duration',true);
                                                    $unit_duration_parameter = apply_filters('vibe_unit_duration_parameter',60,$c);
                                                    $duration = intval($value)*intval($unit_duration_parameter);
                                                    break;
                                                case 'quiz':
                                                    $type = 'quiz';
                                                    $value = get_post_meta($c,'vibe_duration',true);
                                                    $unit_duration_parameter = apply_filters('vibe_quiz_duration_parameter',60,$c);
                                                    $duration = intval($value)*intval($unit_duration_parameter);
                                                    break;
                                                case 'wplms-assignment':
                                                    $type = 'assignments';
                                                    $value = get_post_meta($c,'vibe_duration',true);
                                                    $unit_duration_parameter = apply_filters('vibe_assignment_duration_parameter',86400,$c);
                                                    $duration = intval($value)*intval($unit_duration_parameter);
                                                    break;
                                                default:
                                                    $type = 'units';
                                                    $value = get_post_meta($c,'vibe_duration',true);
                                                    $unit_duration_parameter = apply_filters('vibe_unit_duration_parameter',60,$c);
                                                    $duration = intval($value)*intval($unit_duration_parameter);
                                                    break;
                                            }
                                            $new_curriculum[] = [
                                                'description'=>get_the_excerpt($c),
                                                'duration'=> $duration,
                                                'post_type'=> $type,
                                                'title'=>get_the_title($c),
                                                'type'=>$item_type,
                                                'id'=>$c,
                                            ];
                                        }else{
                                            $new_curriculum[] =[
                                                'description'=>"",
                                                'duration'=> 0,
                                                'post_type'=> "section",
                                                'title'=>$c,
                                                'type'=>"",
                                            ];
                                        }

                                    }
                                    $meta_array['curriculum'] = $new_curriculum;

                                }
                            break;
                            case 'vibe_product':
                                $product = wc_get_product(intval($meta->meta_value));
                                
                                $subs_enabled = get_post_meta($meta->meta_value,'vibe_subscription',true);
                                if($subs_enabled==='S'){
                                    $product_duration['value'] = get_post_meta($meta->meta_value,'vibe_duration',true);
                                    $product_duration['parameter'] = get_post_meta($meta->meta_value,'vibe_product_duration_parameter',true);
                                }
                                
                              
                                
                               /*
                                {
                                  "pricePlan": {
                                    "oneTime": {
                                      "price": 29,
                                      "duration": {
                                        "value": "2",
                                        "parameter": "3600"
                                      },
                                      "planName": "Buy now",
                                      "isDefault": false
                                    }
                                  }
                               */
                            break;
                            
                            default:
                                $settings[$meta->meta_key] = maybe_unserialize($meta->meta_value);
                            break;
                        }
                    }
                   
                }

                $duration = 0;
                if(!empty($duration_value)){
                    $meta_array['courseDuration'] = ['value'=>$duration_value,'parameter'=>$duration_parameter];
                }
                $meta_array['drip'][] = ['id'=>'dripFeedUnitDuration','value'=>$drip_duration];

                if(!empty($product)){
                    $pduration = [];
                    if(!empty($product_duration) && !empty($product_duration['value'])){
                        $pduration = $product_duration;
                    }else{
                        $pduration =$meta_array['courseDuration'];
                    }
                    $meta_array["pricePlan"] = [
                        "oneTime"=>[
                            "price"=>$product->get_price(),
                            "duration"=> $pduration ,
                            "planName"=> _x('Buy Now','','vibebp'),
                            "isDefault"=> true
                        ]
                    ];
                    $settings["pricePlan"] = [
                        "oneTime"=>[
                            "price"=>$product->get_price(),
                            "duration"=> $pduration ,
                            "planName"=> _x('Buy Now','','vibebp'),
                            "isDefault"=> true
                        ]
                    ];
                }

                $post->post_content = do_shortcode($post->post_content);
                $post->post_content = $this->parse_math($post->post_content);
                $parsed_media = $this->parse_media($post->post_content);

                if(!empty($parsed_media['media'])){
                    $post->post_content = $parsed_media['content'];
                    for($i=0;$i<count($parsed_media['media']);$i++){
                        if(!in_array($parsed_media['media'][$i], $this->media_imported)){
                            $this->media_imported[] = $parsed_media['media'][$i];
                            $medias[]=$parsed_media['media'][$i];
                        }
                        
                    }
                }
                $data[] = [
                    'id'=>$post->ID,
                    'title'=>$post->post_title,
                    'description'=>$post->post_excerpt,
                    'featured_image'=>json_encode(['image'=>$featured]),
                    'slug'=>$post->post_name,
                    'content'=>$post->post_content,
                    'status'=>$post->post_status,
                    'start_date'=>$start_date,
                    'settings'=>json_encode($settings),
                    'student_count'=>$student_count,
                    'seats'=>$seats,
                    'meta'=>json_encode($meta_array)
                ];
            }
            if(empty($data)){
                print_r(json_encode(array('done'=>1)));
            }else{
                print_r(json_encode($data));
                $this->build_file('course',$data,$page);
                if(!empty($medias)){
                    $medias = array_map(function($media){
                        return ['url'=>$media];
                    },$medias);
                    $this->build_file('media',$medias,0);
                }
                
            }
        }
        die();
    }

    function generate_taxonomies(){
        if(empty($_POST)){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        if(!wp_verify_nonce(sanitize_text_field($_POST['saas_security']),'saas_security')){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        $page = intval($_POST['page']);
        if($page){
            global $wpdb;
            $data =[];
            $offset=$this->per_page*($page-1);
            $limit =$this->per_page;
            $taxonomies = ['course-cat','level','location','question-tag','module-tag','quiz-type','category'];
            $t_keys_join = implode(',', array_map(function($item){
                        return "'".$item."'";
                    }, $taxonomies));
   
            $taxo_arr = $wpdb->get_results("SELECT t.term_id as term_id,tt.parent as parent,tt.taxonomy as taxonomy,tt.description as  description,t.name as name,t.slug as slug FROM {$wpdb->term_taxonomy} as tt LEFT JOIN {$wpdb->terms} as t ON t.term_id=tt.term_id WHERE tt.taxonomy IN($t_keys_join) LIMIT {$offset},{$limit}");
        
            foreach ($taxo_arr as $key => $taxonomy_term) {
      
                $settings['description']=$taxonomy_term->description;
                $data[] = [
                    'term_id'=>$taxonomy_term->term_id,
                    'name'=>$taxonomy_term->name,
                    'slug'=>$taxonomy_term->slug,
                    'settings'=>json_encode($settings),
                    'taxonomy'=>$taxonomy_term->taxonomy,
                    'parent'=>$taxonomy_term->parent
                ];

            }
            if(empty($data)){
                print_r(json_encode(array('done'=>1)));
            }else{
                print_r(json_encode($data));
                $this->build_file('taxonomy_terms',$data,$page);
            }
        }
        die();


    }

    function generate_taxonomy_relations(){
        if(empty($_POST)){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        if(!wp_verify_nonce(sanitize_text_field($_POST['saas_security']),'saas_security')){
            print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
            die();
        }
        $page = intval($_POST['page']);
        if($page){
            global $wpdb;
            $data =[];
            $offset=$this->per_page*($page-1);
            $limit =$this->per_page;
            $taxonomies = ['course-cat','level','location','question-tag','module-tag','quiz-type','category'];
            $t_keys_join = implode(',', array_map(function($item){
                        return "'".$item."'";
                    }, $taxonomies));
            $taxo_arr = $wpdb->get_results("SELECT tr.object_id as object_id,tr.term_taxonomy_id as term_id,tt.taxonomy as taxonomy FROM {$wpdb->term_relationships} as tr LEFT JOIN {$wpdb->term_taxonomy} as tt ON tr.term_taxonomy_id=tt.term_id WHERE tt.taxonomy IN($t_keys_join) LIMIT {$offset},{$limit}");
            foreach ($taxo_arr as $key => $taxonomy_term) {
        
                $data[] = [
                    'term_id'=>$taxonomy_term->term_id,
                    'object_id'=>$taxonomy_term->object_id,
                    
                ];

            }
            if(empty($data)){
                print_r(json_encode(array('done'=>1)));
            }else{
                print_r(json_encode($data));
                $this->build_file('term_object_relationship',$data,$page);
            }
        }
        die();
        

    }

    function build_file($post_type,$data,$page=0){
        if(!current_user_can('manage_options'))die();
        $dir = wp_upload_dir();
        $file_name = 'export_'.$post_type.'.csv';
        $filepath = $dir['basedir'] . '/saasexport/';
        if(!file_exists($filepath))
            mkdir($filepath,0755);

        $file = $filepath.$file_name;

        if($page==1){
            if(file_exists($file))
                @unlink($file);
        }
        if(!empty($data) && is_array($data)){


            


            if (($handle = fopen($file, "a")) !== FALSE) {
                if(is_array($data)){
                    if($page==1){
                        $keys = array_keys($data[0]);
                        fputcsv($handle, $keys);  
                    }
                    foreach($data as $fields){
                        if(is_array($fields))
                            fputcsv($handle, $fields);  
                    }
                }
            }

            fclose($handle);
            $filepath = $dir['baseurl']. '/saasexport/'.$file_name;
        }
      
    }

    function parse_math($content){


        // Regular expression pattern to match the span tag with the teX attribute
        $pattern = '/<span[^>]*teX="([^"]*)"[^>]*>.*?<\/span>/';

        $content = preg_replace_callback($pattern, function($matches) {
            // Extract the teX attribute value
            $teXValue = $matches[1];
            
            // Wrap the teX value in a <p> tag
            $wrappedTeXValue = '<math-component math="">' . $teXValue . '</math-component>';
            
            return $wrappedTeXValue;
        }, $content);
        return $content;
    }

    function parse_media($content){
        $media=[];
        if(!empty($content)){
            $site_url = get_site_url();
            $callback = function ($matches) use (&$media) {
                $media[] = $matches[0];
                return '[REPLACE_IT]'.$matches[0].'[|REPLACE_IT]'; // Replace with an empty string or any other replacement text if needed
            };
            $site_url = str_replace('/', '\/', get_site_url());
            //$content = preg_replace_callback('/'.$site_url.'\/[^\s]+\.(pdf|png|mp4|jpg|jpeg|gif|bmp|webm|ogg|wav|mp3)/i', $callback, $content);
            $content = preg_replace_callback('/http(s?):\/\/[^\s]+\.(pdf|png|jpg|jpeg|gif|bmp|webm|ogg|wav|mp3)/i', $callback, $content);
            
        }


        return ['content'=>$content,'media'=>$media];
    }


	function initialize(){
        if(class_exists('VibeBP_Settings')){
            add_filter('vibebp_settings_tabs',function($tabs){
                $tabs['saas_migrator_function']=__('Saas Migration ','vibebp');
                return $tabs;
            });
        }else{
            add_options_page(__('Saas Migration ','saas_migrator'),__('Saas Migration  ','vibebp'),'manage_options','saas_migrator',array($this,'settings'),1011);    
        }
        
    }


    function get_settings(){
        $settings = array(
            array(
                'label' => __('Export data for WPLMS SAAS','vibebp'),
                'name' => 'saasmigrate',
                'type' => 'saasmigrate',
                'default'=> '',
            ),
        );

        return apply_filters('saas_migrator_settings',$settings);   
    }



    function settings(){
        

        
        if(!empty($_GET['tab']) && $_GET['tab'] == 'saas_migrator_function' && empty($this->settings)){
            $this->settings=get_option(VIBE_SAAS_OPTIONS);
        }
        $this->save();
        $settings = $this->get_settings();
        
            
        echo '<form method="post">';
        wp_nonce_field('saas_migrator_settings');   
        echo '<table class="form-table">
                <tbody>';

        
        
        $settings = $this->get_settings();
        $this->generate_form($settings);

        echo '<tr valign="top"><th colspan="2"><input type="submit" name="save_saas_migrator_settings" class="button button-primary" value="'.__('Save Settings','vibebp').'" /></th>';
        echo '</tbody></table></form>';
            
    }

    function admin_scripts($hook){

        
        if($hook != 'settings_page_saas_migrator' || empty($_GET['tab']) || (!empty($_GET['tab']) && $_GET['tab'] != 'general' ))
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
                case 'saasmigrate':
                    echo '<th scope="row" class="titledesc"><label>'.$setting['label'].'</label></th>';
                    echo '<td class="forminp">';
                    echo '<span>'.$setting['desc'].'</span>';
                    ?>
                    <a class="button-primary saas_migration_button"><?php echo _x('Generate export zip','','vibebp');?></a>
                    <div class="step_info"></div>
                    <div class="progress_wrap">
                        <span class="progress"></span>
                    </div>
                    <div class="saasdownloadfile">
                    </div>
                    <script>
                        let steps  = <?php echo json_encode($this->steps);?>;
                        let migrating = 0;
                        document.querySelector('.saas_migration_button').addEventListener('click',async()=>{
                            console.log('#1');
                            if(migrating)return false;
                            migrating = 1;
                            document.querySelector('.saas_migration_button').setAttribute('style','opacity:0.5');
                            document.querySelector('.progress_wrap').classList.add('show');
                            document.querySelector('.progress_wrap .progress').setAttribute('style','width:'+10+'%');

                            for(let i=0;i<steps.length;i++){
                                
                                let step = steps[i];
                                let res = await run_migration_step(step);
                                let progress = Math.floor(((i+1)/steps.length)*100);
                                document.querySelector('.progress_wrap .progress').setAttribute('style','width:'+progress+'%');


                                console.log(res);
                                console.log(progress);
                            }
                            migrating = 0;
                            document.querySelector('.saas_migration_button').setAttribute('style','');
                            document.querySelector('.step_info').innerHTML='';
                            downloadZipFile();

                            
                        });

                        async function run_migration_step(step,i=null){
                            if(!i){
                                i=1;
                            }
                            return new Promise(async(resolve,reject)=>{
                                let formData = new FormData();
                                console.log('#3',step);
                                document.querySelector('.step_info').innerHTML='Exporting '+step.replace('generate_','') ;
                                formData.append( 'action', step );
                                formData.append( 'page', i );
                                formData.append('saas_security','<?php echo wp_create_nonce('saas_security');?>');
                                fetch( ajaxurl, {
                                    method: 'POST',
                                    body: formData,
                                } ) // wrapped
                                .then( res => res.json() )
                                .then( async(data) => {
                                    console.log('data',data);
                                    if(data.done){
                                        resolve(true);
                                    }else{
                                        i++;
                                        let response = await run_migration_step(step,i);
                                       
                                        console.log('response',response);
                                        if(response){
                                            resolve(true);
                                        }
                                    }
                                    
                                });
                            });
                        }

                        async function downloadZipFile(){
                            let formData = new FormData();
                            formData.append( 'action', 'download_saa_zip_file' );
                            formData.append('saas_security','<?php echo wp_create_nonce('saas_security');?>');
                            fetch( ajaxurl, {
                                method: 'POST',
                                body: formData,
                            } ) // wrapped
                            .then( res => res.json() )
                            .then( async(data) => {
                                console.log('data',data);
                                if(data.url){
                                    document.querySelector('.saasdownloadfile').innerHTML=`<a class="button button-primary" href="${data.url}" target="_blank">Download zip</a>`;
                                    alert('<?php echo _x('Export completed','','vibebp');?>');

                                }else{
                                    alert('<?php echo _x('some error occured','','vibebp');?>');
                                }
                                
                            });
                            
                        }

                    </script>
                    <style>
                        .step_info{
                            margin:1rem;
                            font-size:1.5rem;
                        }
                        .progress_wrap{
                            width:70%;
                            background:rgba(0,0,0,0.1);
                            border-radius:15px;
                            position:relative;
                            height:10px;
                            transform:scale(0);
                            margin:1rem;
                        }
                        .progress_wrap.show{
                            transform:scale(1);
                        }
                        .saasdownloadfile{
                            margin:1rem;
                        }
                        .progress_wrap .progress{
                            background: green;
                            height:100%;
                            border-radius:15px;
                            transition: 0.3s all;
                            position: absolute;
                            top:0;
                            left:0;
                        }
                        input[name="save_saas_migrator_settings"]{
                            display:none !important;
                        }
                    </style>
                    <?php
                    echo '</td>';
                    
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
        

        if(!isset($_POST['save_saas_migrator_settings']))
            return;
        if ( !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'],'saas_migrator_settings') ){
             echo '<div class="error notice is-dismissible"><p>'.__('Security check Failed. Contact Administrator !','vibebp').'</p></div>';
        }
        

        $settings = $this->get_settings();
        
        foreach($settings as $setting){
            if(isset($_POST[$setting['name']])){
                $this->settings[$setting['name']] = $_POST[$setting['name']];
            }else if($setting['type'] == 'checkbox' && isset($this->settings[$setting['name']])){
                unset($this->settings[$setting['name']]);
            }
        }

        update_option(VIBE_SAAS_OPTIONS,$this->settings);
        echo '<div class="updated notice is-dismissible"><p>'.__('Settings Saved.','vibebp').'</p></div>';
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

	
}

Vibe_Saas_Migration_Settings::init();


function saas_migrator_function(){
    $init = Vibe_Saas_Migration_Settings::init();
    $init->settings();
}
