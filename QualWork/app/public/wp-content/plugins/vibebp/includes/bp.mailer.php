<?php


 if ( ! defined( 'ABSPATH' ) ) exit;
 
class vibebp_touch_mails{

  var $settings;
  var $subject;
  var $user_email;
  public static $instance;
  public static function init(){

    if ( is_null( self::$instance ) )
      self::$instance = new vibebp_touch_mails();
    return self::$instance;
  }

  private function __construct(){

    add_action( 'admin_notices', array($this,'vibebp_emails_migrate_notice' ));
    add_action('wp_ajax_vibebp_emails_migrate',array($this,'vibebp_emails_migrate'));

  }

    //MIGRATE EMAILS NOTICE
    function emails_migrated(){
        if(!function_exists('bp_get_email_post_type')){

          return false;
        }

        $migrated = get_option('vibebp_bp_emails');
        if($migrated == VIBEBP_VERSION){
            $this->migration_status = true;
            return $this->migration_status;
        }

        $flag = 0;
        $count = 0;
        $emails = vibebp_touch_all_mails();
        $tax = bp_get_email_tax_type();

        $migrated_emails = get_terms( $tax);
        $email_term_count = array();
        if(!empty($migrated_emails)){
          foreach($migrated_emails as $em){
            $email_term_count[$em->slug] = $em->count;
          }
        }
        $terms = array_keys($emails);

        foreach($terms as $term){

          if(!term_exists($term,$tax) || empty($email_term_count[$term])){
            $flag = 1;
            break;
          }          
        }
        
        if(empty($flag)){
           $this->migration_status = true; // Do not Show notice
           update_option('vibebp_bp_emails',VIBEBP_VERSION);
        }else{
          $this->migration_status = false;  //show notice
        }
        
        return $this->migration_status;
    }

    function vibebp_emails_migrate_notice(){
        global $pagenow;
        if (!( $pagenow == 'admin.php' && !empty($_GET['page']) && esc_attr($_GET['page']) == 'vibebp_settings')) {
            return;
        }
        if(!function_exists('bp_get_email_tax_type')){
            return;
        }

        $x = $this->emails_migrated(); // for php 5.4 and below
    
        if(empty($x)){
            $count=0;
            //Count number of emails to be migrated
            $emails = vibebp_touch_all_mails();
            $tax = bp_get_email_tax_type();
            $migrated_emails = get_terms( $tax);
            $email_term_count = array();
            if(!empty($migrated_emails)){
              foreach($migrated_emails as $em){
                $email_term_count[$em->slug] = $em->count;
              }
            }
            $terms = array_keys($emails);

            foreach($terms as $term){

              if(!term_exists($term,$tax) || empty($email_term_count[$term])){
                    $count++;
                }
            }

            $class = 'notice notice-error is-dismissible';
            $nonce = wp_create_nonce('vibebp_emails_migrate_notice');
            $message = sprintf(__( '%sMigrate Vibebp email templates to BuddyPress Emails.%s  %s mail templates will be migrated. Refer %s more information & tutorial%s   %s Migrate all email templates to BuddyPress %s %s', 'vibebp' ),'<strong>','</strong>',$count,'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/wplms-email-migration-to-buddypress-emails" target="_blank">','</a>','<br><br><a id="vibebp_emails_migrate" class="button-primary" data-nonce="'.$nonce.'">','</a>','<div class="migrate_progress"><span></span></div>');

            printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
            ?>
            <style>.migrate_progress{display:none;width:100%;overflow:hidden;background:#fafafa;border:1px solid rgba(0,0,0,0.1);border-radius:2px;}.migrate_progress span{width:0%;display:block;padding:3px;background:#46b450;-webkit-transition: width 0.5s ease-in-out;
    -moz-transition: width 0.5s ease-in-out;-o-transition: width 0.5s ease-in-out;transition: width 0.5s ease-in-out;}</style>
            <script>
            jQuery(document).ready(function($){
                $('#vibebp_emails_migrate').on('click',function(){
                    var $this=$(this);
                    
                    if($this.hasClass('disabled'))
                        return;

                    $this.addClass('disabled');
                    $this.parent().find('.button').hide(100);
                    $('.migrate_progress').show(100);
                    setTimeout(function(){$('.migrate_progress span').css('width','40%');},500);
                    $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: { action: 'vibebp_emails_migrate', 
                                security:$this.attr('data-nonce'),
                            },
                        cache: false,
                        success: function (html) {
                            $('.migrate_progress span').css('width','80%');
                            setTimeout(function(){$('.migrate_progress span').css('width','100%');
                                $this.closest('.notice-error').removeClass('notice-error').addClass('notice-success');},500);
                            $this.show(100).html(html).attr('id','vibebp_emails_migrated');
                            setTimeout(function(){$this.closest('.notice').fadeOut(1500);},500);
                        }
                    });
                });
            });
            </script>
            <?php
        }
    }

    function vibebp_emails_migrate(){
        if(!function_exists('bp_get_email_tax_type')){
            return;
           }
        $emails = vibebp_touch_all_mails();
        $post_type = bp_get_email_post_type();
        $tax_type = bp_get_email_tax_type();
        $migrated_emails = get_terms($tax_type );
        $email_term_count = array();
        if(!empty($migrated_emails)){
          foreach($migrated_emails as $em){
            $email_term_count[$em->slug] = $em->count;
          }
        }

        foreach($emails as $id=>$email){
            if(!term_exists($id,$tax_type) || empty($email_term_count[$id])){

                if(!term_exists($id,$tax_type) && !isset($email_term_count[$id])){
                  $id = wp_insert_term($id,$tax_type, array('description'=>sanitize_textarea_field($email['description'])));
                }
              
              if(!is_wp_error($id) || (empty($email_term_count[$id])) ){

                  $textbased = str_replace('titlelink','name',$email['message']);
                  $textbased = str_replace('userlink','name',$email['message']);
                  $post_id = wp_insert_post(array(
                              'post_title'=> '[{{{site.name}}}] '.$email['subject'],
                              'post_content'=> sanitize_textarea_field($email['message']),
                              'post_excerpt'=> sanitize_textarea_field($textbased),
                              'post_type'=> $post_type,
                              'post_status'=> 'publish',
                          ),true);

                  wp_set_object_terms( $post_id, $id, $tax_type );
              }
            }
        }

        update_option('vibebp_bp_emails',VIBEBP_VERSION);

        if(defined('DOING_AJAX') && isset($_POST['security']) && isset($_POST['action']) && esc_attr($_POST['action']) == 'vibebp_emails_migrate'){
          _ex('Migration complete.','Migrate WPLMS emails to BuddyPress success message','vibebp');
          die();
        }
    }

}

vibebp_touch_mails::init();

// BP Course Mail function

function vibebp_wp_mail($to,$subject,$message,$args=''){

    /*=== Migartion to BuddyPRess HTML emails ==*/
    $mails = vibebp_touch_mails::init();
    if(!empty($args['tokens']) ){
     
        $email_type = $args['action'];
        $bpargs = array(
            'tokens' => $args['tokens'],
        );
        bp_send_email( $email_type,$to, $bpargs );
        return;
    }
}


function vibebp_touch_all_mails(){
    $vibebp_touch_mails = array(
        'vibebp_forgot_password'=>array(
            'description'=> __('Forgot password ','vibe'),
            'subject' =>  __(' Password Reset','vibe'),
            'message' =>  __('Someone requested that the password be reset for the following account: ','vibe') . "\r\n\r\n". network_home_url( '/' ) . "\r\n\r\n". sprintf(__('Username: %s','vibe'), '{{user.username}}') . "\r\n\r\n".__('If this was a mistake, just ignore this email and nothing will happen.','vibe') . "\r\n\r\n".sprintf(__('To reset your password, visit the following address: %s','vibe'),'{{{user.forgotpasswordlink}}}') . "\r\n\r\n",
        ),
        /*'student_course_addedd'=>array(
            'description'=> __('Student : Student added to course','wplms'),
            'subject' =>  sprintf(__('Added to course %s','wplms'),'{{course.name}}'),
            'message' =>  sprintf(__('You\'ve been added to course : %s','wplms'),'{{{course.titlelink}}}')
        ),
        'instructor_course_addedd'=>array(
            'description'=> __('Instructor : Instructor adds Student to course','wplms'),
            'subject' =>  sprintf(__('Student added to course %s','wplms'),'{{course.name}}'),
            'message' =>  sprintf(__('%s student added to course : %s','wplms'),'{{{student.userlink}}}','{{{course.titlelink}}}'),
        ),*/
    );
    return apply_filters('vibebp_touch_all_mails',$vibebp_touch_mails);
}

/*===== END INTEGRATION === */
