<?php
/**
 * Register Scripts
 *
 * @class       VibeBP_Register
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VibeBP_Register{


    public static $instance;
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Register();
        return self::$instance;
    }

    private function __construct(){

        add_action('init',array($this,'register_menus'));
        add_action('wp_enqueue_scripts',array($this,'enqueue_head'),11);
        add_action('init',array($this,'register_scripts'));
        add_action('wp_footer',array($this,'check_flatpickr_script_lang'),9999999);

        /*
        SPECIAL HOOKS IN INTEGRATION WITH SHORTCODES
        */
       add_action('bp_core_activated_user',array($this,'activate_user'),10,3);
       add_filter('bp_get_email_args',array($this,'get_custom_activation_email_args'),99,2);
    }

    function register_scripts(){
        //adding additional handle for flatpickr since elementor also uses 'flatpickr' handle
        wp_register_script('vibebp-flatpickr',plugins_url('../assets/js/flatpickr.min.js',__FILE__),array(),VIBEBP_VERSION,true);
        wp_register_style('vibebp-flatpickr',plugins_url('../assets/css/flatpickr.min.css',__FILE__),array(),VIBEBP_VERSION);

        wp_register_style('vibebp-swiper',plugins_url('../assets/css/swiper-bundle.min.css',__FILE__));
        wp_register_script('vibebp-swiper',plugins_url('../assets/js/swiper-bundle.min.js',__FILE__),array(),VIBEBP_VERSION,true);


        wp_enqueue_style('iziToast',plugins_url('../assets/css/iziToast.min.css',__FILE__),array(),VIBEBP_VERSION);
        wp_enqueue_script('iziToast',plugins_url('../assets/js/iziToast.min.js',__FILE__),array(),VIBEBP_VERSION);
    }

    function check_flatpickr_script_lang(){
        global $wp_scripts;
    
        if( ((!empty($wp_scripts->queue) && in_array('vibebp-flatpickr',$wp_scripts->queue)) || !empty($wp_scripts->done) && in_array('vibebp-flatpickr',$wp_scripts->done))  && vibebp_get_flatpickrLang() && vibebp_get_flatpickrLang()!='en'){
            
            echo '<script src="https://npmcdn.com/flatpickr@4.6.13/dist/l10n/'.vibebp_get_flatpickrLang().'.js"></script>';
            ?>
            <script>
                window.onload = function flatpickrLang(){flatpickr.localize(flatpickr.l10ns.<?php echo vibebp_get_flatpickrLang();?>)};
            </script>
            <?php
       }
    }

    function register_menus(){

        register_nav_menus(
            array(
                'loggedin' => __( 'LoggedIn DropDown Menu','vibebp' ),
                'profile' => __( 'Profile Menu','vibebp' )
            )
        );

        $enabled = vibebp_get_setting('role_based_menu','bp');
        if(!empty($enabled) && $enabled == 'on'){
            $roles = vibebp_get_registered_user_roles();
            foreach($roles as $key=>$role){
                register_nav_menus(
                    array(
                        'loggedin_'.$key => sprintf(__( 'LoggedIn DropDown Menu for %s [Optional]','vibebp' ),$role['label']),
                        'profile_'.$key => sprintf(__( 'Profile Menu for %s [Optional]','vibebp' ),$role['label'])
                    )
                );
            }
        }

        $member_type_based_menu = vibebp_get_setting('member_type_based_menu','bp','general');
        if(!empty($member_type_based_menu) && $member_type_based_menu == 'on'){
            $types = bp_get_member_types(array(),'objects');
 
            foreach($types as $type => $labels){
                register_nav_menus(
                    array(
                        'loggedin_'.$type => sprintf(__( 'LoggedIn DropDown Menu for %s [Optional]','vibebp' ),$labels->labels['name']),
                        'profile_'.$type => sprintf(__( 'Profile Menu for %s [Optional]','vibebp' ),$labels->labels['name'])
                    )
                );
            }
        }
    }


    function get_vibebp(){

        $blog_id = '';
        if(function_exists('get_current_blog_id')){
            $blog_id = get_current_blog_id();
        }

        $firebase_config =vibebp_get_setting('firebase_config','general','firebase');
        if(!empty($firebase_config) && is_serialized($firebase_config)){
            $firebase_config = json_encode(unserialize($firebase_config)); 
        }

        $customizer = VibeBP_Customizer::init();
        $customizer->get_customizer();


        $group_types = [];
        if(function_exists('bp_groups_get_group_types')){
            $gt = bp_groups_get_group_types(array(),'objects');
            foreach($gt as $k=>$t){
                $group_types[$k]=$t->labels['name'];
            }
        }
        $vibebp= array(
            'style'=>empty($customizer->customizer['theme'])?'medium':$customizer->customizer['theme'],
            'dark_mode'=>empty($customizer->customizer['mode'])?'':$customizer->customizer['mode'],
            'expanded_menu'=>empty($customizer->customizer['expanded_menu'])?'':$customizer->customizer['expanded_menu'],
            'user_id'=>bp_displayed_user_id(),
            'xnonce'=>vibebp_get_setting('sync_wp_login')?wp_create_nonce( 'wp_rest' ):false,
            'sw_version'=>vibebp_get_setting('version','service_worker'),
            'api'=>array(
                'url'=> apply_filters('vibebp_rest_api',get_rest_url($blog_id,Vibe_BP_API_NAMESPACE)),
                'xprofile_url'=>apply_filters('vibebp_rest_api',get_rest_url($blog_id,Vibe_BP_API_NAMESPACE)).'/'.Vibe_BP_API_XPROFILE_TYPE,
                'sw_enabled'=>vibebp_get_setting('service_workers'),
                'sw'=>untrailingslashit(function_exists('network_site_url') && is_multisite()?network_site_url():site_url()).'/firebase-messaging-sw.js?v='.vibebp_get_setting('version','service_worker'),

                'ipstack_url'=>'http://api.ipstack.com/',

                'endpoints'=>array(
                    'activity'      => Vibe_BP_API_ACTIVITY_TYPE,
                    'public_activity'      => Vibe_BP_API_PUBLIC_ACTIVITY_TYPE,
                    'members'       => Vibe_BP_API_MEMBERS_TYPE,
                    'groups'        => Vibe_BP_API_GROUPS_TYPE,
                    'friends'       => 'friends',
                    'notifications' => Vibe_BP_API_NOTIFICATIONS_TYPE,
                    'messages'      => Vibe_BP_API_MESSAGES_TYPE,
                    'settings'      => Vibe_BP_API_SETTINGS_TYPE,
                    'xprofile'      => Vibe_BP_API_XPROFILE_TYPE
                ),
            ),
            'icons'=>Array(
                'dark_mode'=>apply_filters('vibebp_component_icon','<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M19 6.734c0 4.164-3.75 6.98-3.75 10.266h-1.992c.001-2.079.997-3.826 1.968-5.513.912-1.585 1.774-3.083 1.774-4.753 0-3.108-2.517-4.734-5.004-4.734-2.483 0-4.996 1.626-4.996 4.734 0 1.67.862 3.168 1.774 4.753.971 1.687 1.966 3.434 1.967 5.513h-1.991c0-3.286-3.75-6.103-3.75-10.266 0-4.343 3.498-6.734 6.996-6.734 3.502 0 7.004 2.394 7.004 6.734zm-4 11.766c0 .276-.224.5-.5.5h-5c-.276 0-.5-.224-.5-.5s.224-.5.5-.5h5c.276 0 .5.224.5.5zm0 2c0 .276-.224.5-.5.5h-5c-.276 0-.5-.224-.5-.5s.224-.5.5-.5h5c.276 0 .5.224.5.5zm-1.701 3.159c-.19.216-.465.341-.753.341h-1.093c-.288 0-.562-.125-.752-.341l-1.451-1.659h5.5l-1.451 1.659z"/></svg>','dark_mode'),
                'light_mode'=>apply_filters('vibebp_component_icon','<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M19 6.734c0 4.164-3.75 6.98-3.75 10.266h-1.992c.001-2.079.997-3.826 1.968-5.513.912-1.585 1.774-3.083 1.774-4.753 0-3.108-2.517-4.734-5.004-4.734-2.483 0-4.996 1.626-4.996 4.734 0 1.67.862 3.168 1.774 4.753.971 1.687 1.966 3.434 1.967 5.513h-1.991c0-3.286-3.75-6.103-3.75-10.266 0-4.343 3.498-6.734 6.996-6.734 3.502 0 7.004 2.394 7.004 6.734zm-4 11.766c0 .276-.224.5-.5.5h-5c-.276 0-.5-.224-.5-.5s.224-.5.5-.5h5c.276 0 .5.224.5.5zm0 2c0 .276-.224.5-.5.5h-5c-.276 0-.5-.224-.5-.5s.224-.5.5-.5h5c.276 0 .5.224.5.5zm-1.701 3.159c-.19.216-.465.341-.753.341h-1.093c-.288 0-.562-.125-.752-.341l-1.451-1.659h5.5l-1.451 1.659z"/></svg>','light_mode'),
                'logout'=>apply_filters('vibebp_component_icon','<svg width="24" height="24" viewBox="0 0 24 24">
                        <path d="M18.213,1.754L17,3.353C19.984,5.085 22,8.308 22,12C22,17.514 17.514,22 12,22C6.486,22 2,17.514 2,12C2,8.308 4.016,5.085 7,3.353L5.787,1.754C2.322,3.857 0,7.651 0,12C0,18.627 5.373,24 12,24C18.627,24 24,18.627 24,12C24,7.651 21.678,3.857 18.213,1.754Z" />
                        <rect x="10" y="0" width="4" height="12" />
                    </svg>','logout')
            ),
            'settings'=>array(
                'custom_header_items'=>apply_filters('vibebp_custom_header_items',array()),
                'forgotpassword_link' => (!empty(vibebp_get_setting('wp_forgot_password','general','login'))?wp_lostpassword_url():''),
                'cart'=>apply_filters('vibebp_show_cart',function_exists('wc_get_cart_url')),
                'giphy'=>array(
                    'api_key'=>vibebp_get_setting('giphy_api_key','general','misc'),
                    'endpoints'=>array(
                        'search'=>'https://api.giphy.com/v1/gifs/search',
                        'trending'=>'https://api.giphy.com/v1/gifs/trending',
                    ),
                ),
                'groups_csv_file'=>plugins_url('../assets/images/groups_import.csv',__FILE__),
                'google_play_products'=>get_vibebp_google_products_config(),
                'google_play_payment_method' =>'https://play.google.com/billing',
                'coin_image'=>plugins_url('../assets/images/coin.svg',__FILE__),
                'member_types_registration'=>(!empty(vibebp_get_setting('member_types_registration','general','login'))?true:false),
                'registration_invite_codes'=>(!empty(vibebp_get_setting('registration_invite_codes','general','login'))?true:false),
                'enable_reactions'=>(!empty(vibebp_get_setting('enable_reactions','general','misc'))?true:false),
                'reactions'=>vibebp_get_reactions_array(),
                'post_data_check_interval'=>apply_filters('vibebp_post_data_check_interval',1*60*60*1000),
                'show_groups_dir' => apply_filters('vibebp_show_join_button',true),
                'show_join_button' => apply_filters('vibebp_show_join_button',true),
                'groups_per_page'=>apply_filters('vibebp_groups_per_page',9),
                'ipstack_access_id'=>vibebp_get_setting('ipstack_access_id','general','misc'),
                'timestamp'=>time(),
                'recaptcha_key'=>vibebp_get_setting('google_captcha_public_key','general','login'),
                'cache_first' => vibebp_get_setting('cache_first','service_worker'),
                'password_strength_colors' => apply_filters('vibebp_password_strength_colors',['#129039','#ffae2a','#ff995b','#fb721f','#ff5b5b']),
                'password_length' => apply_filters('vibebp_allowed_password_ength',6),
                'widget_classes' =>['col-md-3 col-sm-6','col-md-4 col-sm-6','col-md-6 col-sm-12','col-md-8 col-sm-12','col-md-12'],
                'client_id'=>vibebp_get_setting('client_id'),
                'security'=>vibebp_get_api_security(),
                'upload_limit'=>wp_max_upload_size(),
                'email_login'=>vibebp_get_setting('email_login','general','login'),
                'google_maps_api_key'=>vibebp_get_setting('google_maps_api_key','general','misc'),
                'strong_password' => vibebp_get_setting('strong_password','general','login'),
                'firebase_config'=>$firebase_config,
                'session_lock' => vibebp_get_setting('session_lock','general','firebase'),
                'create_member'=>vibebp_get_setting('create_member','bp','general'),
                'auth'=>array(
                    'google'=>vibebp_get_setting('firebase_google_auth','general','firebase'),
                    'facebook'=>vibebp_get_setting('firebase_facebook_auth','general','firebase'),
                    'twitter'=>vibebp_get_setting('firebase_twitter_auth','general','firebase'),
                    'github'=>vibebp_get_setting('firebase_github_auth','general','firebase'),
                    'apple'=>vibebp_get_setting('firebase_apple_auth','general','firebase'),
                    'microsoft'=>vibebp_get_setting('firebase_microsoft_auth','general','firebase')
                ),
                'is_rtl' => is_rtl(),
                'flatpickrLang'=>vibebp_get_flatpickrLang(),
                'logout_redirect'=>(vibebp_get_setting('logout_redirect')?get_permalink(vibebp_get_setting('logout_redirect')):home_url()),
                'login_redirect'=>apply_filters('vibebp_login_redirect_link',get_permalink(vibe_get_bp_page_id('members'))),
                'login_redirect_component'=>apply_filters('vibebp_login_redirect_component',false),
                'pdfjs_script'=>plugins_url('../assets/js/pdf/pdf.min.js',__FILE__),
                'map_marker'=>plugins_url('../assets/images/marker.png',__FILE__),
                'followers' => vibebp_get_setting('bp_followers','bp','general'),
                'friends' => (function_exists('bp_is_active') && bp_is_active('friends'))?1:0,
                'likes' => vibebp_get_setting('bp_likes','bp','general'),
                'profile_page'=>vibebp_get_setting('offline_page','service_worker'),
                'enable_registrations'=>apply_filters('vibebp_enable_registration',true),
                'media_tabs'=>apply_filters('vibebp_media_tabs',array(
                    'media'=>__('Select media','vibebp'),
                    'upload'=>__('Upload media','vibebp'),
                    'embed'=>__('Embed media','vibebp'),
                )),
                'upload_capability' => apply_filters('vibebp_upload_capability',(!empty(vibebp_get_setting('upload_capability'))?vibebp_get_setting('upload_capability'):null)),
                'registration_fields'=>array(
                    ['type'=>'email','id'=>'email','label'=>_x('Email','login','vibebp'),'value'=>'','class'=>'input'],
                    ['type'=>'password','id'=>'password','label'=>_x('Password','login','vibebp'),'value'=>'','class'=>'input']    
                ),
                'customizer'=>apply_filters('vibebp_customizer',array(
                    'theme'=>empty($customizer->customizer['theme'])?'':$customizer->customizer['theme'],
                    'profile_menu_promo'=>empty($customizer->customizer['profile_menu_promo'])?'':$customizer->customizer['profile_menu_promo'],
                    'profile_grid_promo'=>empty($customizer->customizer['profile_grid_promo'])?'':$customizer->customizer['profile_grid_promo'],
                    'login_image'=>empty($customizer->customizer['login_image'])?'':$customizer->customizer['login_image'],
                )),
                'default_image'=>plugins_url('../assets/images/avatar.jpg',__FILE__),
                'delete_account'=>bp_disable_account_deletion()?false:true,
                'profile_settings'=>array(), //array of arrays each array(key=>'CLASS ID TO INITIALIZE REACT COMPONENT','value'=>'xx')
                'browser_names' => array(
                     'opera' => _x('Opera','','vibebp'),
                     'msie' => _x('MSIE','','vibebp'),
                     'netscape' => _x('Netscape','','vibebp'),
                     'chrome' => _x('Chrome','','vibebp'),
                     'safari' => _x('Safari','','vibebp'),
                     'firefox' => _x('Firefox','','vibebp'),
                ),
                
                'os_names'=>array(
                    'win_10' => _x('Windows 10','','vibebp'),
                    'win_81' => _x('Windows 8.1','','vibebp'),
                    'win_7' => _x('Windows 7','','vibebp'),
                    'win_vista' => _x('Windows Vista','','vibebp'),
                    'win_xp' => _x('Windows XP','','vibebp'),
                    'win_2000' => _x('Windows 2000','','vibebp'),
                    'mac_os' => _x('Mac/iOS','','vibebp'),
                    'unix' => _x('UNIX','','vibebp'),
                    'linux' => _x('Linux','','vibebp'),
                    'iphone' => _x('iPhone/iPad/iPod','','vibebp'),
                    'android' => _x('Android','','vibebp'), 
                ),
                'group_types'=>$group_types,
                'group_sorters'=>array(
                    'active' =>__('Active','vibebp'),
                    'newest' =>__('Recently Added','vibebp'),
                    'alphabetical' =>__('Alphabetical','vibebp'),
                    'random'=>__('Random','vibebp'),
                    'popular'=>__('Popular','vibebp')
                ),
                'ai_prompts'=>[
                ]
            ),   
            'translations'=>array(
                'login_checkbox'=>_x('Please check the login terms','','vibebp'),
                'make_inactive'=>_x('Make In-active','','vibebp'),
                'make_active'=>_x('Make active','','vibebp'),
                'add_option'=>_x('Add option','','vibebp'),
                'end_time'=>_x('End time','','vibebp'),
                'every'=>_x('Every','','vibebp'),
                'start_time'=>_x('Start time','','vibebp'),
                'yes'=>_x('Yes','','vibebp'),
                'no'=>_x('No','','vibebp'),
                'sure_delete_account'=>_x('Are you sure you want to delete your account?','','vibebp'),
                'delete_account'=>_x('Delete account','','vibebp'),
                'have_a_invitation_code'=>_x('Have a invitation code?','','vibebp'),
                'invitation_code'=>_x('Invitation Code','','vibebp'),
                'enter_invite_code'=>_x('Please enter invite code','','vibebp'),
                'register_using_invite_code'=>_x('Register using invite code','','vibebp'),
                'error_loading_data'=>_x('Error loading data','','vibebp'),
                'download_sample'=>_x('Download sample','','vibebp'),
                'upload_members_csv'=>_x('Upload Members Csv','','vibebp'),
                'no_transactions_found'=>_x('No transactions found','','vibebp'),
                'no_products'=>_x('No products found','','vibebp'),
                'balance'=>_x('Balance','','vibebp'),
                'credits'=>_x('Credits','','vibebp'),
                'reported'=>_x('Reported','','vibebp'),
                'its_inappropriate'=>_x('It\'s inappropriate','','vibebp'),
                'its_spam'=>_x('It\'s spam','','vibebp'),
                'post_actions'=>_x('Post actions','','vibebp'),
                'report_post'=>_x('Report post','','vibebp'),
                'hide_post'=>_x('Hide post','','vibebp'),
                'wall_settings'=>_x('Wall Settings','','vibebp'),
                'who_can_post'=>_x('Who can post on you wall!','','vibebp'),
                'you_dont_have_post_permissions'=>_x('You dont have permisison to post!','','vibebp'),
                'post'=>_x('Post','','vibebp'),
                'add_comment'=>_x('Add comment','','vibebp'),
                'wall'=>_x('Wall','','vibebp'),
                'please_enter_pwd'=>_x('Please enter Password','','vibebp'),
                'passwords_does_not_match'=>_x('Passwords does not match ','','vibebp'),
                'reset_password'=>_x('Reset Password','','vibebp'),
                'enter_password' => _x('Enter Password','','vibebp'),
                're_enter_password' => _x('Re-Enter Password','','vibebp'),
                'back' => _x('Back','','vibebp'),
                'set_new_password' => _x('Set a new Password','','vibebp'),
                'default_user'=>_x('Default User','registration','vibebp'),
                'register_as'=>_x('Register as','registration','vibebp'),
                'view_membership'=>_x('View Membership','','vibebp'),
                'view_as_public'=>_x('View as Public','','vibebp'),
                'members'=>_x('Members','','vibebp'),
                'all'=>_x('All','','vibebp'),
                'join_group'=>_x('Join Group','','vibebp'),
                'select_type'=>_x('Select type','','vibebp'),
                'sort_groups' => _x('Sort Groups','','vibebp'),
                'no_groups_found'=>__('No groups found !','vibebp'),
                'show_filters'=>__('Show Filters','vibebp'),
                'close_filters'=>__('Close Filters','vibebp'),
                'clear_all'=>__('Clear All','vibebp'),
                'on'=>_x('on','','vibebp'),
                'remove_device'=>_x('Remove Device','','vibebp'),
                'active_sessions'=>_x('Active Sessions','','vibebp'),
                'password_strength'=>['',_x('Too Weak','password strength','vibebp'),_x('Weak','password strength','vibebp'),_x('Good','password strength','vibebp'),_x('Better','password strength','vibebp'),_x('Best','password strength','vibebp')],
                'password_6_chars' => sprintf(_x('Your password must be at least %d characters','','vibebp'),apply_filters('vibebp_allowed_password_ength',6)),
                'password_one_letter' => _x('Your password must contain at least one letter.','','vibebp'),
                'password_digit' => _x('Your password must contain at least one digit.','','vibebp'),
                'password_uppercase' => _x('Your password must contain at least one upper case letter.','','vibebp'),
                'special_char' => _x('Your password must contain at least one special character.','','vibebp'),
                'reset_to_default'=>_x('Reset to Default','dashboard','vibebp'),
                'change_cover' =>_x('Change Cover','dashboard','vibebp'),
                'uploading' =>_x('Uploading...','dashboard','vibebp'),
                'upload' =>_x('Upload','dashboard','vibebp'),
                'invalid_url' => _x('Invalid URL','dashboard','vibebp'),
                'close' => _x('Close','dashboard','vibebp'),
                'hello'=> _x('Hello','dashboard','vibebp'),
                'online'=> _x('Online','api','vibebp'),
                'offline'=> _x('You are no longer connected to internet.','api','vibebp'),
                'empty_dashboard' => _x('Empty dashboard.','api','vibebp'),
                'facebook' => _x('Sign In with Facebook','api','vibebp'),
                'twitter' => _x('Sign In with Twitter','api','vibebp'),
                'google' => _x('Sign In with Google','api','vibebp'),
                'github' => _x('Sign In with Github','api','vibebp'),
                'apple' => _x('Sign In with  Apple','api','vibebp'),
                'microsoft' => _x('Sign In with Microsoft','api','vibebp'),
                'login_heading'=> ''.stripslashes(vibebp_get_setting('login_heading','general','login')).'',
                'login_message'=>''.stripslashes(vibebp_get_setting('login_message','general','login')).'',
                'email_login'=>_x('Login with Email',' login','vibebp'),
                'no_account'=>_x('No account ?',' login','vibebp'),
                'create_one'=>_x('Create one',' login','vibebp'),
                'create_account'=>_x('Create Account',' login','vibebp'),
                'login_terms'=> stripslashes(vibebp_get_setting('login_terms','general','login')),
                'signin_email_heading'=> stripslashes(vibebp_get_setting('signin_email_heading','general','login')),
                'forgot_password'=> stripslashes(vibebp_get_setting('forgot_password','general','login')),
                'signin_email_description'=>stripslashes(vibebp_get_setting('signin_email_description','general','login')),
                'online_from_other_account' => _x('You\'re online on another device. Please logout from there!','','vibebp'),
                'login_checkbox'=>stripslashes(vibebp_get_setting('login_checkbox','general','login')),
                'registration_checkbox'=>stripslashes(vibebp_get_setting('registration_checkbox','general','login')),
                'all_signin_options'=>_x('All sign in options','login','vibebp'),
                'signin'=>_x('Sign In','login','vibebp'),
                'forgotpassword'=>_x('Forgot Password','login','vibebp'),
                'password_recovery_email'=>_x('Send password recovery email','login','vibebp'),
                'missing_subject'=>_x('Missing subject','message error','vibebp'), 
                'missing_recipients'=>_x('Missing recipients','message error','vibebp'), 
                'missing_content'=>_x('Missing message content','message error','vibebp'),
                'light_mode'=>_x('Light mode','profile','vibebp'),
                'dark_mode'=>_x('Dark mode','profile','vibebp'),
                'register_account_heading'=>stripslashes(vibebp_get_setting('register_account_heading','general','login')),
                'email'=>_x('Email or Username',' login','vibebp'),
                'password'=>_x('Password','login','vibebp'),
                'register_account_description'=>stripslashes(vibebp_get_setting('register_account_description','general','login')),
                'account_already'=>_x('Already have an account ? ','login','vibebp'),
                'like'=>_x('Like','login','vibebp'),
                'likes'=>_x('Likes','login','vibebp'),
                'liked'=>_x('Liked','login','vibebp'),
                'followers'=>_x('Followers','login','vibebp'),
                'following'=>_x('Following','login','vibebp'),
                'follow_members'=>_x('Follow more members','login','vibebp'),
                'profile'=>_x('Profile ','login','vibebp'),
                'logout'=>_x('Logout ','login','vibebp'),
                'more'=>_x('Load more.. ','login','vibebp'),
                'years'=>_x('years','login','vibebp'),
                'year'=>_x('year ','login','vibebp'),
                'months'=>_x('months','login','vibebp'),
                'month'=>_x('month','login','vibebp'),
                'weeks'=>_x('weeks','login','vibebp'),
                'week'=>_x('week','login','vibebp'),
                'days'=>_x('days','login','vibebp'),
                'day'=>_x('day','login','vibebp'),
                'hours'=>_x('hours','login','vibebp'),
                'hour'=>_x('hour','login','vibebp'),
                'minutes'=>_x('minutes','login','vibebp'),
                'minute'=>_x('minute','login','vibebp'),
                'seconds'=>_x('seconds','login','vibebp'),
                'second'=>_x('second','login','vibebp'),
                'no_activity_found'=>_x('No activity found !','login','vibebp'),
                'whats_new'=>_x('Whats New','login','vibebp'),
                'post_update'=>_x('Post update','login','vibebp'),
                'select_component'=>_x('Select component','login','vibebp'),
                'justnow'=>_x('Just now','login','vibebp'),
                'cancel'=>_x('Cancel','login','vibebp'),
                'owner'=>_x('Owner','login','vibebp'),
                'date'=>_x('Date','login','vibebp'),
                'apply'=>_x('Apply','login','vibebp'),
                'type_message'=>_x('Type Message','login','vibebp'),
                'drag_to_refresh'=>_x('Drag to refresh','drag','vibebp'),
                'refresh' => _x('Refresh','','vibebp'),
                'load_new_data'=>_x('New data available, reload to get new data.','','vibebp'),
                'selectaction'=>_x('Select Action','login','vibebp'),
                'no_notifications_found'=>_x('No notifications found !','login','vibebp'),
                'sender'=>_x('Sender','login','vibebp'),
                'no_messages_found'=>_x('No messages found !','login','vibebp'),
                'no_groups_found'=>_x('No groups found !','login','vibebp'),
                'new_message'=>_x('New Message','login','vibebp'),
                'send_notice'=>_x('Send Notice','login','vibebp'),
                'labels'=>_x('Labels','login','vibebp'),
                'add_new'=>_x('Add New','login','vibebp'),
                'search_text'=>_x('Search ...','login','vibebp'),
                'recipients'=>_x('Recipients ...','login','vibebp'),
                'subject'=>_x('Subject','login','vibebp'),
                'message'=>_x('Message','login','vibebp'),
                'attachments'=>_x('Attachment','login','vibebp'),
                'send_message'=>_x('Send Message','login','vibebp'),
                'search_member'=>_x('Search Member','login','vibebp'),
                'add_label'=>_x('Add Label','login','vibebp'),
                'remove_label'=>_x('Remove Label','login','vibebp'),
                'select_image'=>_x('Upload File','login','vibebp'),
                'group_name'=>_x('Group Name','login','vibebp'),
                'group_description'=>_x('Group Description','login','vibebp'),
                'group_status'=>_x('Group Status','login','vibebp'),
                'group_type'=>_x('Group Type','login','vibebp'),
                'invite_members'=>_x('Invite members','login','vibebp'),
                'add_members'=>_x('Add members','login','vibebp'),
                'create_group'=>_x('Create Group','login','vibebp'),
                'group_invitations'=>_x('Group Invite Permissions','login','vibebp'),
                'image_size_error'=>sprintf(_x('Image size should be less than upload limit %s','login','vibebp'),'( '.floor(wp_max_upload_size()/1024).' kb )'),
                'file_size_error'=>_x('File size should be less than ','login','vibebp'),
                'file_type_not_allowed'=>_x('File type not allowed!','login','vibebp'),
                'allowed_file_types'=>_x('Allowed file types','login','vibebp'),
                'max_size'=>_x('Max file size allowed','login','vibebp'),
                'admin'=>_x('Admin','login','vibebp'),
                'mod'=>_x('Mod','login','vibebp'),
                'select_option'=>_x('Select Option','login','vibebp'),
                'accept_invite'=>_x('Accept Invite','login','vibebp'),
                'cancel_invite'=>_x('Cancel Invite','login','vibebp'),
                'no_friends_found'=>_x('No Friends found !','login','vibebp'),
                'requester'=>_x('Requested','login','vibebp'),
                'requestee'=>_x('Requests','login','vibebp'),
                'no_requests_found'=>_x('No Requests found !','login','vibebp'),
                'add_friend'=>_x('Add Friend','login','vibebp'),
                'send_friend_request'=>_x('Send friend request','login','vibebp'),
                'account_email'=>_x('Account Email','login','vibebp'),
                'confirm_old_password'=>_x('Confirm Old Password','login','vibebp'),  
                'change_password'=>_x('Change Password','login','vibebp'), 
                'change_email' => _x('Change Email','','vibebp'),
                'repeat_new_password'=>_x('Repeat Password','login','vibebp'), 
                'save_changes'=>_x('Save Changes','login','vibebp'),
                'send_email_notice'=>_x('Send email notice','login','vibebp'),
                'visibility_settings'=>_x('Profile Field Visibility Settings','login','vibebp'),
                'export_data_settings'=>_x('Export data settings','login','vibebp'),
                'download_data'=>__( 'Download personal data', 'vibebp' ),
                'new_data'=>__('Request new data export', 'vibebp' ),
                'request_data'=>__('Request personal data export', 'vibebp'),
                'update_image'=>__('Update Image', 'vibebp'),
                'change_image'=>__('Change Image', 'vibebp'),
                'address'=>__('Address', 'vibebp'),
                'city'=>__('City', 'vibebp'),
                'country'=>__('Country', 'vibebp'),
                'country'=>__('ZipCode', 'vibebp'),
                'no_followers'=>__('No followers found !', 'vibebp'),
                'no_following'=>__('You are not following anyone !', 'vibebp'),
                'set_icon'=>__('Save Items', 'vibebp'),
                'submit'=>__('Submit', 'vibebp'),
                'topic_title'=>__('Topic Title', 'vibebp'),
                'select_forum'=>__('Select a forum', 'vibebp'),
                'topic_content'=>__('Write content in topic', 'vibebp'),
                'subscribed'=>__('Subscribed', 'vibebp'),
                'subscribe'=>__('Subscribe', 'vibebp'),
                'no_orders'=>__('No Orders', 'vibebp'),
                'coupons_applied'=>__('Coupons Applied', 'vibebp'),
                'shipping_rates'=>__('Shipping Rates', 'vibebp'),
                'fees'=>__('Fees', 'vibebp'),
                'select_order_status'=>__('Select Order Status', 'vibebp'),
                'order_number'=>__('Order Number', 'vibebp'),
                'order_date'=>__('Order Date', 'vibebp'), 
                'order_status'=>__('Order Status', 'vibebp'), 
                'order_quantity'=>__('Order Quantity', 'vibebp'), 
                'order_amount'=>__('Order Total', 'vibebp'),
                'order_payment_method'=>__('Payment Method', 'vibebp'),
                'item_name'=>__('Item Name', 'vibebp'),
                'item_total'=>__('Item Total', 'vibebp'),
                'billing_address'=>__('Billing Address', 'vibebp'),
                'shipping_address'=>__('Shipping Address', 'vibebp'),
                'no_downloads'=>__('No Downloads found !', 'vibebp'),
                'download'=>__('Download', 'vibebp'),
                'access_expires'=>__('Access Expires', 'vibebp'),
                'product_name'=>__('Product Name', 'vibebp'),
                'remaining_downloads'=>__('Remaining Downloads', 'vibebp'),
                'allMedia' =>__('All Media', 'vibebp'),
                'uploaded_media'=>__('Uploads', 'vibebp'),
                'choose_column_type'=>__('Choose Columns', 'vibebp'),
                'type_here'=>__('Type here...', 'vibebp'),
                'no_media'=>__('No media found!', 'vibebp'),
                'preview'=>__('Preview', 'vibebp'),
                'select_widget'=>__('Select Widget', 'vibebp'),
                'missing_data'=>__('Missing data', 'vibebp'),
                'invalid_email'=>__('Invalid email id.', 'vibebp'),
                'password_too_short'=>__('Too short password.', 'vibebp'),
                'enter_emabed_name'=>__('Enter Embed Name', 'vibebp'),
                'enter_embed_url'=>__('Enter Embed Url', 'vibebp'),
                'embed'=>__('Embed', 'vibebp'),
                'hide_panel'=>__('Hide Panel', 'vibebp'),
                'show_panel'=>__('Show Panel', 'vibebp'),
                'title'=>__('Title', 'vibebp'),
                'description'=>__('Description', 'vibebp'),
                'site_activity'=>__('Site Activity', 'vibebp'),
                'select_action'=>__('Select Action', 'vibebp'),
                'select_group_type'=>__('Select group type', 'vibebp'),
                'set_group_forum'=>__('Select group forum', 'vibebp'),
                'back_to_signin'=>__('Back to sign in', 'vibebp'),
                'leave_group'=>__('Leave group', 'vibebp'),
                'announcements'=>__('Announcements', 'vibebp'),
                'no_announcements'=>__('No Announcements available!', 'vibebp'),
                'no_galleries'=>__('No Galleries available!', 'vibebp'),
                'announcement_title'=>__('Write an announcement for the group', 'vibebp'),
                'add_announcement'=>__('Add Announcement', 'vibebp'),
                'gallery_title'=>__('Enter a gallery title', 'vibebp'),
                'add_gallery'=>__('Add Gallery', 'vibebp'),
                'galleries'=>__('Galleries', 'vibebp'),
                'edit'=>__('Edit', 'vibebp'),
                'delete'=>__('Delete', 'vibebp'),
                'complete_your_profile'=>__('Complete your profile', 'vibebp'),
                'complete_profile'=>__('Complete profile', 'vibebp'),
                'delete_account'=>__( 'Delete Account', 'vibebp' ),
                'settings'=>__( 'Settings', 'vibebp' ),
                'chat'=>__( 'Chat', 'vibebp' ),
                'filter'=>__( 'Filter', 'vibebp' ),
                'clear_data'=>_x( 'Clear data','pwa settings', 'vibebp' ),
                'clear_saved_data'=>_x( 'Clear all saved data', 'pwa settings','vibebp' ),
                'data_cleared'=>_x( 'All user saved data removed, you can now safely logout.', 'pwa settings','vibebp' ),
                'get_help'=>_x( 'See user guide.', 'pwa settings','vibebp' ),
                'tours'=>_x( 'Tours', 'tour dropdown','vibebp' ),
                'get_help'=>_x( 'more tours', 'tour dropdown','vibebp' ),
                'start_tour'=>_x( 'Start tour', 'tour dropdown','vibebp' ),
                'prev_step'=>_x( 'Previous step', 'tour dropdown','vibebp' ),
                'next_step'=>_x( 'Next step', 'tour dropdown','vibebp' ),
                'end_tour'=>_x( 'End tour', 'tour dropdown','vibebp' ),
                'tour_complete'=>_x( 'Tour completed !', 'tour ','vibebp' ),
                'thank_you'=>_x( 'Your guided tour has ended. Thank you for completing this tour.', 'tour ','vibebp' ),
                'member_types' => __('Types','vibebp'),
                'member_tags' => __('Tags','vibebp'),
                'load_more' =>  __('Load More','vibebp'),
                'no_members' => __('No Members Found!','vibebp'),
                'create_member'=> __('Create member','vibebp'),
                'search' => __('Search...','vibebp'),
                'bulk_actions' => __('Bulk Action','vibebp'),
                'user_selected' => __('user selected[Expand]','vibebp'),
                'clearall' => __('Clear All','vibebp'),
                'select_tags' => __('Select Tags','vibebp'),
                'select_member_types'=> __('Select Member Types','vibebp'),
                'select_member_type'=> __('Select Member Type','vibebp'),
                'select_users' => __('Select Users','vibebp'),
                'tax_name' => __('Term Name','vibebp'),
                'email_not_valid' => __('Email not valid!','vibebp'),
                'enter_email' => __('Enter Email','vibebp'),
                'enter_password' => __('Enter Password','vibebp'),
                'update' => __('update','vibebp'),
                'select_csv' => __('Click to upload CSV','vibebp'),
                'deselect_csv' => __('Click to remove CSV','vibebp'),
                'create_user' => __('Create User','vibebp'),
                'bulk_upload' => __('Bulk Upload','vibebp'),
                'create_members_csv' => __('Create-Members','vibebp'),
                'email' => __('Email','vibebp'),
                'member_tags_comma' => __('User Tags','vibebp'),
                'member_types_comma' => __('Member Types Slug','vibebp'),
                'all_registered' => __('All User Registerd!','vibebp'),
                'show_more' => __('Show More','vibebp'),
                'search_gif' => __('Search a Gif','vibebp'),
                'select_media'=> __('Select Media','vibebp'),
                'upload_media'=> __('Upload Media','vibebp'),
                'no_price'=> __('No Price','vibebp'),
                'add_cpt'=>[
                    'product'=>[
                        'search'=>__('Search Product','vibebp'),
                        'add'=>__('Add Product','vibebp'),
                    ]
                ],
                'select_tax'=>__('Select Taxonomy/Category','vibebp'),
                'add_tax'=>__('Set Terms','vibebp'),
                'remove_tax'=>__('Remove Terms','vibebp'),
                'add_message'=>__('Add Message','vibebp'),
                'type_to_search'=> _x('Type to Search...','dashboard','vibebp'),
                'generate_content'=>__('Generate Content','vibebp'),
                'ai_content_generator'=>__('AI Generator','vibebp'),
                'type_ai_prompt'=>__('Type AI Prompt','vibebp'),
                'add_content'=>__('Add Content','vibebp'),
            ),
        );
        

        $vibebp['components'] = array(
            'default_component'=>'course',
            'dashboard'=>array(
                    'sidebar'=>apply_filters('vibebp_member_dashboard','vibebp-dashboard',$vibebp['user_id']),
                    'widgets'=>apply_filters('vibebp_member_dashboard_widgets',array())
                ),
            'profile'=>array(
                'label'=>__('Profile','vibebp'),
                'default_component'=>'view'
            )
        );
        $social_icons = apply_filters('vibebp_social_icons',array(
            array(
                'icon'=>'vicon vicon-flickr',
                'label'=>__('Flickr','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-instagram',
                'label'=>__('Instagram','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-google',
                'label'=>__('Google','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-github',
                'label'=>__('Github','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-facebook',
                'label'=>__('Facebook','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-dropbox',
                'label'=>__('Dropbox','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-dribbble',
                'label'=>__('Dribbble','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-apple',
                'label'=>__('Apple','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-yahoo',
                'label'=>__('Yahoo','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-trello',
                'label'=>__('Trello','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-stack-overflow',
                'label'=>__('Stack-overflow','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-soundcloud',
                'label'=>__('Soundcloud','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-sharethis',
                'label'=>__('Sharethis','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-reddit',
                'label'=>__('Reddit','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-microsoft',
                'label'=>__('Microsoft','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-linux',
                'label'=>__('Linux','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-jsfiddle',
                'label'=>__('Jsfiddle','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-joomla',
                'label'=>__('Joomla','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-html5',
                'label'=>__('Html5','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-css3',
                'label'=>__('Css3','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-drupal',
                'label'=>__('Drupal','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-wordpress',
                'label'=>__('Wordpress','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-tumblr',
                'label'=>__('Tumblr','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-skype',
                'label'=>__('Skype','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-youtube',
                'label'=>__('Youtube','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-vimeo',
                'label'=>__('Vimeo','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-twitter',
                'label'=>__('Twitter','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-linkedin',
                'label'=>__('Linkedin','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-pinterest',
                'label'=>__('Pinterest','vibebp')
            )
        ));
        $vibebp['social_icons']=$social_icons;


        $vibebp['repeatable_icons']=apply_filters('vibebp_repeatable_icons',array(
                'vicon vicon-arrow-up','vicon vicon-arrow-right','vicon vicon-arrow-left','vicon vicon-arrow-down','vicon vicon-arrows-vertical','vicon vicon-arrows-horizontal','vicon vicon-angle-up','vicon vicon-angle-right','vicon vicon-angle-left','vicon vicon-angle-down','vicon vicon-angle-double-up','vicon vicon-angle-double-right','vicon vicon-angle-double-left','vicon vicon-angle-double-down','vicon vicon-move','vicon vicon-fullscreen','vicon vicon-arrow-top-right','vicon vicon-arrow-top-left','vicon vicon-arrow-circle-up','vicon vicon-arrow-circle-right','vicon vicon-arrow-circle-left','vicon vicon-arrow-circle-down','vicon vicon-arrows-corner','vicon vicon-split-v','vicon vicon-split-v-alt','vicon vicon-split-h','vicon vicon-hand-point-up','vicon vicon-hand-point-right','vicon vicon-hand-point-left','vicon vicon-hand-point-down','vicon vicon-back-right','vicon vicon-back-left','vicon vicon-exchange-vertical
','vicon vicon-wand','vicon vicon-save','vicon vicon-save-alt','vicon vicon-direction','vicon vicon-direction-alt','vicon vicon-user','vicon vicon-link','vicon vicon-unlink','vicon vicon-trash','vicon vicon-target','vicon vicon-tag','vicon vicon-desktop','vicon vicon-tablet','vicon vicon-mobile','vicon vicon-email','vicon vicon-star','vicon vicon-spray','vicon vicon-signal','vicon vicon-shopping-cart','vicon vicon-shopping-cart-full','vicon vicon-settings','vicon vicon-search','vicon vicon-zoom-in','vicon vicon-zoom-out','vicon vicon-cut','vicon vicon-ruler','vicon vicon-ruler-alt-2','vicon vicon-ruler-pencil','vicon vicon-ruler-alt','vicon vicon-bookmark','vicon vicon-bookmark-alt','vicon vicon-reload','vicon vicon-plus','vicon vicon-minus','vicon vicon-close','vicon vicon-pin','vicon vicon-pencil','vicon vicon-pencil-alt','vicon vicon-paint-roller','vicon vicon-paint-bucket','vicon vicon-na','vicon vicon-medall','vicon vicon-medall-alt','vicon vicon-marker','vicon vicon-marker-alt','vicon vicon-lock','vicon vicon-unlock','vicon vicon-location-arrow','vicon vicon-layout','vicon vicon-layers','vicon vicon-layers-alt','vicon vicon-key','vicon vicon-image','vicon vicon-heart','vicon vicon-heart-broken','vicon vicon-hand-stop','vicon vicon-hand-open','vicon vicon-hand-drag','vicon vicon-flag','vicon vicon-flag-alt','vicon vicon-flag-alt-2','vicon vicon-eye','vicon vicon-import','vicon vicon-export','vicon vicon-cup','vicon vicon-crown','vicon vicon-comments','vicon vicon-comment','vicon vicon-comment-alt','vicon vicon-thought','vicon vicon-clip','vicon vicon-check','vicon vicon-check-box','vicon vicon-camera','vicon vicon-announcement','vicon vicon-brush','vicon vicon-brush-alt','vicon vicon-palette','vicon vicon-briefcase','vicon vicon-bolt','vicon vicon-bolt-alt','vicon vicon-blackboard','vicon vicon-bag','vicon vicon-world','vicon vicon-wheelchair','vicon vicon-car','vicon vicon-truck','vicon vicon-timer','vicon vicon-ticket','vicon vicon-thumb-up','vicon vicon-thumb-down','vicon vicon-stats-up','vicon vicon-stats-down','vicon vicon-shine','vicon vicon-shift-right','vicon vicon-shift-left','vicon vicon-shift-right-alt','vicon vicon-shift-left-alt','vicon vicon-shield','vicon vicon-notepad','vicon vicon-server','vicon vicon-pulse','vicon vicon-printer','vicon vicon-power-off','vicon vicon-plug','vicon vicon-pie-chart','vicon vicon-panel','vicon vicon-package','vicon vicon-music','vicon vicon-music-alt','vicon vicon-mouse','vicon vicon-mouse-alt','vicon vicon-money','vicon vicon-microphone','vicon vicon-menu','vicon vicon-menu-alt','vicon vicon-map','vicon vicon-map-alt','vicon vicon-location-pin','vicon vicon-light-bulb','vicon vicon-info','vicon vicon-infinite','vicon vicon-id-badge','vicon vicon-hummer','vicon vicon-home','vicon vicon-help','vicon vicon-headphone','vicon vicon-harddrives','vicon vicon-harddrive','vicon vicon-gift','vicon vicon-game','vicon vicon-filter','vicon vicon-files','vicon vicon-file','vicon vicon-zip','vicon vicon-folder','vicon vicon-envelope','vicon vicon-dashboard','vicon vicon-cloud','vicon vicon-cloud-up','vicon vicon-cloud-down','vicon vicon-clipboard','vicon vicon-calendar','vicon vicon-book','vicon vicon-bell','vicon vicon-basketball','vicon vicon-bar-chart','vicon vicon-bar-chart-alt','vicon vicon-archive','vicon vicon-anchor','vicon vicon-alert','vicon vicon-alarm-clock','vicon vicon-agenda','vicon vicon-write','vicon vicon-wallet','vicon vicon-video-clapper','vicon vicon-video-camera','vicon vicon-vector','vicon vicon-support','vicon vicon-stamp','vicon vicon-slice','vicon vicon-shortcode','vicon vicon-receipt','vicon vicon-pin2','vicon vicon-pin-alt','vicon vicon-pencil-alt2','vicon vicon-eraser','vicon vicon-more','vicon vicon-more-alt','vicon vicon-microphone-alt','vicon vicon-magnet','vicon vicon-line-double','vicon vicon-line-dotted','vicon vicon-line-dashed','vicon vicon-ink-pen','vicon vicon-info-alt','vicon vicon-help-alt','vicon vicon-headphone-alt','vicon vicon-gallery','vicon vicon-face-smile','vicon vicon-face-sad','vicon vicon-credit-card','vicon vicon-comments-smiley','vicon vicon-time','vicon vicon-share','vicon vicon-share-alt','vicon vicon-rocket','vicon vicon-new-window','vicon vicon-rss','vicon vicon-rss-alt','vicon vicon-control-stop','vicon vicon-control-shuffle','vicon vicon-control-play','vicon vicon-control-pause','vicon vicon-control-forward','vicon vicon-control-backward','vicon vicon-volume','vicon vicon-control-skip-forward','vicon vicon-control-skip-backward','vicon vicon-control-record','vicon vicon-control-eject','vicon vicon-paragraph','vicon vicon-uppercase','vicon vicon-underline','vicon vicon-text','vicon vicon-Italic','vicon vicon-smallcap','vicon vicon-list','vicon vicon-list-ol','vicon vicon-align-right','vicon vicon-align-left','vicon vicon-align-justify','vicon vicon-align-center','vicon vicon-quote-right','vicon vicon-quote-left','vicon vicon-layout-width-full','vicon vicon-layout-width-default','vicon vicon-layout-width-default-alt','vicon vicon-layout-tab','vicon vicon-layout-tab-window','vicon vicon-layout-tab-v','vicon vicon-layout-tab-min','vicon vicon-layout-slider','vicon vicon-layout-slider-alt','vicon vicon-layout-sidebar-right','vicon vicon-layout-sidebar-none','vicon vicon-layout-sidebar-left','vicon vicon-layout-placeholder','vicon vicon-layout-menu','vicon vicon-layout-menu-v','vicon vicon-layout-menu-separated','vicon vicon-layout-menu-full','vicon vicon-layout-media-right','vicon vicon-layout-media-right-alt','vicon vicon-layout-media-overlay','vicon vicon-layout-media-overlay-alt','vicon vicon-layout-media-overlay-alt-2','vicon vicon-layout-media-left','vicon vicon-layout-media-left-alt','vicon vicon-layout-media-center','vicon vicon-layout-media-center-alt','vicon vicon-layout-list-thumb','vicon vicon-layout-list-thumb-alt','vicon vicon-layout-list-post','vicon vicon-layout-list-large-image','vicon vicon-layout-line-solid','vicon vicon-layout-grid4','vicon vicon-layout-grid3','vicon vicon-layout-grid2','vicon vicon-layout-grid2-thumb','vicon vicon-layout-cta-right','vicon vicon-layout-cta-left','vicon vicon-layout-cta-center','vicon vicon-layout-cta-btn-right','vicon vicon-layout-cta-btn-left','vicon vicon-layout-column4','vicon vicon-layout-column3','vicon vicon-layout-column2','vicon vicon-layout-accordion-separated','vicon vicon-layout-accordion-merged','vicon vicon-layout-accordion-list','vicon vicon-widgetized','vicon vicon-widget','vicon vicon-widget-alt','vicon vicon-view-list','vicon vicon-view-list-alt','vicon vicon-view-grid','vicon vicon-upload','vicon vicon-download','vicon vicon-loop','vicon vicon-layout-sidebar-2','vicon vicon-layout-grid4-alt','vicon vicon-layout-grid3-alt','vicon vicon-layout-grid2-alt','vicon vicon-layout-column4-alt','vicon vicon-layout-column3-alt','vicon vicon-layout-column2-alt','<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
    <path fill="#000" fill-rule="nonzero" d="M12.074 7.31h9.852l-.898 10.174-4.034 1.131-4.023-1.13-.897-10.174zm-10.01 0h9.873l-.9 10.244-4.043 1.068-4.032-1.089-.899-10.222zm17.714-1.306v.407H18V5.85h.932L18 4.991V4.5h1.778v.562h-.795l.795.942zm-2.111 0v.407H16V5.85h.86L16 4.991V4.5h1.667v.562h-.757l.757.942zm-2-.155v.562H14.11V4.5h1.556v.562h-.89v.787h.89zM2.995 4.57h.625v.625h.571V4.57h.625v1.892h-.625v-.633H3.62v.633h-.625V4.57zm2.643.628h-.55V4.57h1.725v.628h-.55v1.264h-.625V5.198zm1.448-.628h.652l.4.665.4-.665h.652v1.892h-.622v-.938l-.43.673h-.01l-.43-.673v.938h-.612V4.57zm2.415 0h.625v1.267h.879v.625H9.5V4.57zM19.503 15.45l.261-2.462.299-3.39.03-.375h-6.178l.03.38.056.678.024.291h4.72l-.113 1.237h-3.098l.029.337.056.635.025.265h2.878l-.145 1.636-1.377.395-1.371-.37-.088-.986h-1.238l.17 1.95 2.527.697 2.504-.707v-.211zm-9.435-5.724l.03-.278H3.905l.03.356.303 3.467h4.284l-.144 1.543L7 15.111v-.001.026l-1.377-.353-.088-.951h-1.24l.171 1.918 2.531.665.003-.002 2.532-.689.022-.19.29-3.25.03-.25H5.37l-.113-1.35h4.73l.024-.303.057-.656z"/>
</svg>','<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
    <path fill="#000" fill-rule="nonzero" d="M19.482 6.413c.441.268.664.768.664 1.303v8.257c0 .536-.223 1.035-.664 1.305l-6.76 4.13a1.377 1.377 0 0 1-1.43 0l-2.275-1.413c-.34-.2-.175-.27-.063-.311.454-.165.543-.204 1.027-.491.05-.03.113-.019.166.013l1.74 1.09c.063.037.139.037.197 0l6.783-4.13c.064-.037.05-.114.05-.193V7.716c0-.08.01-.155-.055-.196l-6.757-4.126a.222.222 0 0 0-.217 0L5.067 7.52c-.066.04-.137.118-.137.195v8.257c0 .08.039.154.104.191l1.863 1.132c1.012.532 1.568-.094 1.568-.723V8.421c0-.115.217-.286.326-.286h.864c.108 0 .04.171.04.287v8.151c0 1.42-.666 2.233-1.947 2.233-.393 0-.633 0-1.498-.448l-1.803-1.08a1.558 1.558 0 0 1-.747-1.305V7.716c0-.535.3-1.036.74-1.303L11.28 2.28c.43-.256.99-.256 1.418 0l6.785 4.134zm-5.372 8.252c1.434 0 2.045-.34 2.045-1.141 0-.46-.173-.803-2.402-1.032-1.864-.194-3.016-.627-3.016-2.192 0-1.444 1.158-2.305 3.1-2.305 2.182 0 3.26.795 3.398 2.501a.216.216 0 0 1-.052.158.197.197 0 0 1-.144.064h-.885a.197.197 0 0 1-.19-.16c-.213-.99-.728-1.306-2.128-1.306-1.566 0-1.748.572-1.748 1.002 0 .52.215.668 2.33.962 2.093.291 3.087.697 3.087 2.242 0 1.559-1.237 2.44-3.395 2.44-2.981 0-3.605-1.461-3.605-2.667 0-.114.088-.252.197-.252h.88c.099 0 .18.12.196.222.133.942.53 1.464 2.332 1.464z"/>
</svg>','<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
    <path fill="#000" fill-rule="nonzero" d="M11.917 6.61l7.916-4.57v19.92l-7.916-4.57L4 21.96V2.04l7.917 4.57zM8.154 12l3.938 2.277L16.029 12l-3.937-2.277L8.154 12zM6.747 6.415v3.259l2.816-1.63-2.816-1.629zm0 7.911v3.014l2.605-1.507-2.605-1.507zm10.501 3.014v-3.014l-2.605 1.507 2.605 1.507zm-2.816-9.296l2.816 1.63V6.415l-2.816 1.63z"/>
</svg>'

        ));

        if(bp_is_active('xprofile')){
            $cover_support = get_option('bp-disable-cover-image-uploads');
            if(!empty($cover_support)){
                $cover_support = false;
            }else{
                $cover_support = true;
            }
            $vibebp['components']['xprofile']=array(
                'visibility'=>array(
                    'public'=>__('Public','vibebp'),
                    'loggedin'=>__('All members','vibebp'),
                    'friends'=>__('Friends','vibebp'),
                    'adminsonly'=>__('Only me','vibebp'),
                ),
                'countries'=>vibebp_get_countries(),
                'cover' =>$cover_support,
            );
        }
        global $post;
        if((function_exists('bp_is_user') && bp_is_user()) || (!empty($post) &&  !empty($post->post_content) && has_shortcode($post->post_content,'vibebp_profile'))){
            //$vibebp['user_id'] = bp_displayed_user_id();
            $nav = get_option('bp_setup_nav');
            if(empty($nav) && empty(vibebp_get_setting('bp_single_page'))){
                if(!empty(bp_get_nav_menu_items())){
                    $nav = bp_get_nav_menu_items();    
                }
            }
            if(!is_array($nav)){$nav=array();}
            if(!empty($nav)){
                foreach($nav as $key => $item){
                    $name = strip_tags($item->name);
                    if(is_numeric(substr($name, -1,1))){
                        $end = strrpos($name, ' ');
                        $name = substr($name, 0,$end);
                        $count = substr($name, $end);
                        $nav[$key]->count = $count;
                    }
                    $nav[$key]->name = $name;
                    if(in_array("current-menu-parent",$item->class) ){
                        unset($nav[$key]->class[array_search("current-menu-parent",$item->class)]);
                    }
                    if(in_array("current-menu-item",$item->class)){
                        unset($nav[$key]->class[array_search("current-menu-item",$item->class)]);
                    }

                    

                    if($item->parent === 0){
                       // if(in_Array($item->css_id,array('activity','profile','')
                    }
                }
            }
            if(!is_array($nav)){$nav=array();}
            array_unshift($nav, array(
                'css_id'=>'dashboard',
                'name'=>__('Dashboard','vibebp'),
                'parent'=>0,
                'class'=>["menu-parent","current-menu-parent"]
            ));

            $vibebp['nav'] = $nav;
            $vibebp['components']['wallet']=array(
                'label'=>__('Wallet','vibebp'),
            );
            
            if(bp_is_active('activity')){
                $vibebp['components']['activity']=array(
                    'label'=>__('Activity','vibebp'),
                    'post'=>array(
                        'components'=>array(
                            '' => __('Personal','vibebp'),
                            'public' => __('Wall','vibebp'),
                            'groups' => __('Groups','vibebp'),

                        ),
                    ),
                    'sorters'=>array(
                        '-1'=>_x('Everything','login','vibebp'),
                        'activity_update'=>_x('Updates','login','vibebp'),
                        'friendship_accepted,friendship_created'=>_x('Friendships','login','vibebp'),
                        'created_group'=>_x('New Groups','login','vibebp'),
                        'joined_group'=>_x('Group Memberships','login','vibebp'),
                        'group_details_updated'=>_x('Group Updates','login','vibebp'),
                        
                    ),
                );
                $vibebp['components']['wall']=array(
                    'label'=>__('Wall','vibebp'),
                    
                    'sorters'=>array(
                        '-1'=>_x('Everything','login','vibebp'),
                        'activity_update'=>_x('Updates','login','vibebp'),
                        'friendship_accepted,friendship_created'=>_x('Friendships','login','vibebp'),
                        'created_group'=>_x('New Groups','login','vibebp'),
                        'joined_group'=>_x('Group Memberships','login','vibebp'),
                        'group_details_updated'=>_x('Group Updates','login','vibebp'),
                        
                    ),
                    'who_can_post'=>array(
                        'all'=>_x('All','login','vibebp'),
                        'friends'=>_x('Friends','login','vibebp'),
                    ),
                );
            }
            
            if(bp_is_active('notifications')){
                $vibebp['components']['notifications']=array(
                    'label'=>__('Notifications','vibebp'),
                    'sorters'=>array(
                        'DESC'=>_x('Newest First','login','vibebp'),
                        'ASC'=>_x('Oldest First','login','vibebp'),
                    ),
                    'actions'=>Array(
                        'read'=>_x('Read','login','vibebp'),
                        'unread'=>_x('Unread','login','vibebp'),
                        'delete'=>_x('Delete','login','vibebp'),
                    )
                );
            }


            if(bp_is_active('messages')){
                $vibebp['components']['messages']=array(
                    'label'=>__('Messages','vibebp'),
                    'sorters'=>array(
                        'DESC'=>_x('Newest First','login','vibebp'),
                        'ASC'=>_x('Oldest First','login','vibebp'),
                    ),
                    'actions'=>Array(
                        'read'=>_x('Mark Read','login','vibebp'),
                        'unread'=>_x('Mark Unread','login','vibebp'),
                        'delete'=>_x('Delete','login','vibebp'),
                        'star'=>_x('Add Star','login','vibebp'),
                        'unstar'=>_x('Remove Star','login','vibebp'),
                    )
                );
            }

            if(bp_is_active('friends')){
                $vibebp['components']['friends']=array(
                    'label'=>__('Friends','vibebp'),
                    'sorters'=>array(
                        'active'=>_x('Active','login','vibebp'),
                        'alphabetical'=>_x('Alphabetical','login','vibebp'),
                        'newest'=>_x('Newest','login','vibebp'),
                    ),
                    'requests_sorter'=>array(
                        'DESC'=>_x('Newest first','login','vibebp'),
                        'ASC'=>_x('Earliest first','login','vibebp'),
                    ),
                    'actions'=>Array(
                        'read'=>_x('Mark Read','login','vibebp'),
                        'unread'=>_x('Mark Unread','login','vibebp'),
                        'delete'=>_x('Delete','login','vibebp'),
                        'star'=>_x('Add Star','login','vibebp'),
                        'unstar'=>_x('Remove Star','login','vibebp'),
                    )
                );
            }
            
            if(bp_is_active('groups')){
                $cover_support = get_option('bp-disable-group-cover-image-uploads');
                if(!empty($cover_support)){
                    $cover_support = false;
                }else{
                    $cover_support = true;
                }
                $vibebp['components']['activity']['post']['components']['groups']=__('Groups','vibebp');
                $vibebp['components']['groups']=array(
                    'label'=>__('Groups','vibebp'),
                    'all'=>__('All Groups','vibebp'),
                    'sorters'=>array(
                        'active'=>_x('Last Active','login','vibebp'),
                        'popular'=>_x('Most Members','login','vibebp'),
                        'newest'=>_x('Newly Created','login','vibebp'),
                        'alphabetical'=>_x('Alphabetical','login','vibebp'),
                    ),
                    'cover' => $cover_support,
                );

                
              
                if(function_exists('bp_groups_get_group_types')){
                    $types = bp_groups_get_group_types(array(),'objects');    
                    $gt=[];
                    if(!empty($types)){
                        foreach($types as $key=>$t){
                            $gt[$key]=$t->labels['name'];
                        }    
                    }
                    $vibebp['components']['groups']['type'] = $gt;
                }
                
                $vibebp['components']['groups']['status'] = array(
                    'public'=>_x('Public','login','vibebp'),
                    'private'=>_x('Private','login','vibebp'),
                    'hidden'=>_x('Hidden','login','vibebp'),
                );
                $vibebp['components']['groups']['invite_type'] = array(
                    'pending'=>_x('Pending Invites','login','vibebp'),
                    'accepted'=>_x('Accepted invites','login','vibebp'),
                );
                $vibebp['components']['groups']['invite_sort'] = array(
                    'DESC'=>_x('Recently invited','login','vibebp'),
                    'ASC'=>_x('Last Invited','login','vibebp'),
                );
                $vibebp['components']['groups']['membertypes'] = array(
                    ''=>_x('All Members','login','vibebp'),
                    'mod'=>_x('Moderators','login','vibebp'),
                    'admin'=>_x('Administrators','login','vibebp'),
                    'banned'=>_x('Banned Users','login','vibebp'),
                    'invited'=>_x('Invited Users','login','vibebp'),
                    'requests'=>__('Membership Requests', 'vibebp'),
                );

                if(function_exists('bbpress') && function_exists('vibe_helpdesk_translations')){
                    $vibebp['components']['groups']['forum'] =VIBE_HELPDESK_API_NAMESPACE;
                }
                
                $vibebp['components']['groups']['invite_status'] = array(
                    'members'=>_x('All Group Members','login','vibebp'),
                    'mods'=>_x('Group Moderators and Administrators','login','vibebp'),
                    'admins'=>_x('Group Administrators only','login','vibebp'),
                );

                $vibebp['components']['groups']['nav'] = array(
                    'activty'=>_x('Activity','login','vibebp'),
                    'members'=>_x('Members','login','vibebp'),
                    'manage'=>_x('Manage','login','vibebp'),
                );
            }

            if(vibebp_get_setting('bp_followers','bp','general')){
                $vibebp['components']['followers']=array(
                    'label'=>__('Followers','vibebp'),
                    'sorters'=>array(
                        'active'=>_x('Last Active','login','vibebp'),
                        'popular'=>_x('Most Members','login','vibebp'),
                        'newest'=>_x('Newly Created','login','vibebp'),
                        'alphabetical'=>_x('Alphabetical','login','vibebp'),
                    ),
                );
            }



            if(function_exists('WC')){
                $vibebp['components']['shop']=array(
                    'label'=>__('Shop','vibebp'),
                    'order_type'=>array(
                        'shop_order_refund'=>__('Refunds','vibebp')
                    ),
                    'order_statuses'=>wc_get_order_statuses(),
                    'order_timelines'=>array(
                        '' => __('All orders','vibebp'),
                        'this_month' => __('This month','vibebp'),
                        'last_month' => __('Last month','vibebp'),
                        'last_6_months' => __('Last 6 months','vibebp'),
                        'last_year' => __('Last Year','vibebp')

                    ),
                    'per_page' => apply_filters('vibebp_short_order_per_page',10),
                    'translations' => array(
                        'load_more' => _x('Load more','','vibebp'),
                    )
                );
            }

       
            $vibebp['components']['members_detail']=array(
                'label'=>__('All Members','vibebp'),
                'sorters'=>array(
                    ['label'=>__('All','vibebp'),'value'=>''],
                    ['label'=>__('Alphabetical','vibebp'),'value'=>'nicename'],
                    ['label'=>__('Recently Joined','vibebp'),'value'=>'user_registered'],
                    ['label'=>__('ID','vibebp'),'value'=>'ID'],
                ),
                'settings' => array(
                    'bulk_actions' => array(
                        ['key'=>'send_message','label'=>__('Send Message','vibebp')]
                    )
                ) 
            );

            if(bp_is_active('groups')){
                $groups = [];
                if(function_exists('groups_get_groups')){
                    $allgroups = groups_get_groups();
                   
                    if(!empty($allgroups) && !empty($allgroups['groups'])){
                        foreach ($allgroups['groups'] as $key => $g) {
                            $groups[]= ['key'=>$g->id,'label'=>$g->name];
                        }
                    }
                }
                $vibebp['components']['members_detail']['settings']['bulk_actions'][] =[
                    'key'=>'add_to_group',
                    'label'=>__('Add to group','vibebp'),
                    'options'=>$groups,];
                $vibebp['components']['members_detail']['settings']['bulk_actions'][] =[
                    'key'=>'remove_from_group',
                    'label'=>__('Remove from group','vibebp'),
                    'options'=>$groups,];
            }
            
            if(function_exists('pmpro_hasMembershipLevel')){
                $vibebp['components']['pmpro']=array(
                    'label'=>__('My Memberships','vibebp'),
                    'translations' => array(
                        'level' => _x('Level','','vibebp'),
                        'billing' =>  _x('Billing','','vibebp'),
                        'expiration' => _x('Expiration','','vibebp'), 
                        'renew' => _x('Renew','','vibebp'), 
                        'change' => _x('Change','','vibebp'), 
                        'cancel' => _x('Cancel','','vibebp'), 
                        'view_all_levels' => _x('View all membership Options','','vibebp'), 
                        'past_invoices' => _x('Past Invoices','','vibebp'), 
                        'date' => _x('Date','','vibebp'), 
                        'amount' => _x('Amount','','vibebp'), 
                        'status' => _x('Status','','vibebp'), 
                        'username' => _x('Username','','vibebp'), 
                        'email' => _x('Email','','vibebp'), 
                        'edit_profile' => _x('Edit Profile','','vibebp'), 
                        'change_password' => _x('Change Password','','vibebp'), 
                        'member_links' => _x('Member Links','','vibebp'), 
                        'view_print' => _x('View and print membership cards','','vibebp'), 
                        'no_invoices' => _x('No invoices found','','vibebp'),
                        'no_memberships' => _x('No memberships found','','vibebp'),

                    ),
                    
                );
            }
            $vibebp['components']=apply_filters('vibebp_active_compontents',$vibebp['components']);
        }
        return apply_filters('vibebp_vars',$vibebp);
    }




    function enqueue_head(){
        
        wp_enqueue_script('localforage',plugins_url('../assets/js/localforage.min.js',__FILE__),array(),VIBEBP_VERSION,true);

        
        $deps=  array('wp-element','wp-data','localforage');



        if(vibebp_get_setting('google_captcha_public_key','general','login')){
            $enabled = get_option('users_can_register');
            $custom_registration_page =false;
            if(function_exists('vibe_get_option')){
                $custom_registration_page = vibe_get_option('custom_registration_page');
            }
            if($enabled && !$custom_registration_page){
                wp_register_script('google-recaptchav3','https://www.google.com/recaptcha/api.js?render='.vibebp_get_setting('google_captcha_public_key','general','login'));
                $deps[]='google-recaptchav3';    
            }
        }

        wp_register_script('vibebplogin',plugins_url('../assets/js/login.js',__FILE__),$deps,VIBEBP_VERSION,true);

        wp_register_style('vibebp_main',plugins_url('../assets/css/front.css',__FILE__),array(),VIBEBP_VERSION);

        wp_enqueue_style('vibebp_main');
        ?>
        <script>
        window.addEventListener('load', (event) => {
            if(typeof iziToast==='undefined' && typeof global !=='undefined' && typeof global.iziToast==='object'){
                window.iziToast = global.iziToast;
            }
        });
        </script>
        <?php

        if(function_exists('WC') || vibebp_get_setting('enable_wallet')){
            wp_enqueue_script('vibebp-cart',plugins_url('../assets/js/cart.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION,true);
            $blog_id = '';
                if(function_exists('get_current_blog_id')){
                    $blog_id = get_current_blog_id();
                }
            wp_localize_script('vibebp-cart','vibebp_cart',[
                'api'=>[
                    'get_products'=>get_rest_url($blog_id,'wc/store/v1/products'),
                    'get'=>get_rest_url($blog_id,'/wc/store/cart'),
                    'add_item'=>get_rest_url($blog_id,'/wc/store/v1/cart/add-item'),
                    'remove_item'=>get_rest_url($blog_id,'/wc/store/v1/cart/remove-item'),
                    'update_item'   => get_rest_url($blog_id,'/wc/store/v1/cart/update-item'),
                    'coupons'=>get_rest_url($blog_id,'/wc/store/v1/cart/coupons'),
                    'apply_coupon'=>get_rest_url($blog_id,'/wc/store/v1/cart/apply-coupon'),  
                    'remove_coupon'=>get_rest_url($blog_id,'/wc/store/v1/cart/remove-coupon'),
                    'select_shipping_rate' => get_rest_url($blog_id,'/wc/store/v1/cart/select-shipping-rate'),
                    'update_customer' => get_rest_url($blog_id,'/wc/store/v1/cart/update-customer'),
                    'checkout' => get_rest_url($blog_id,'/wc/store/v1/cart/checkout')
                ],
                'settings'=>[
                    'nonce'=>wp_create_nonce( 'wc_store_api' ),
                    'cart'=>function_exists('wc_get_cart_url')?wc_get_cart_url():'',
                    'checkout'=>function_exists('wc_get_checkout_url')?wc_get_checkout_url():'',
                    'continue_shopping_link'=>vibebp_get_setting('continue_shopping')?get_permalink(vibebp_get_setting('continue_shopping')):'',
                    'is_wallet_active'=>vibebp_get_setting('enable_wallet'),
                    'buy_credits'=>vibebp_get_setting('buy_credits','wallet'),
                    'credits_symbol'=>vibebp_get_setting('credits_symbol','wallet'),
                    'cookiepath'=>COOKIEPATH
                ],
                'translations'=>[
                    'cart'=>__('Cart','vibebp'),
                    'view_cart'=>__('View Cart','vibebp'),
                    'continue_shopping'=>__('Continue Shopping','vibebp'),
                    'cart_no_items'=>__('No items in cart.','vibebp'),
                    'close'=>__('Close','vibebp'),
                    'checkout'=>__('Checkout','vibebp'),
                    'timeout'=>__('Timeout','vibebp'),
                    'complete_purchase_via_credits'=>__('Complete purchase using Credits','vibebp')
                ]
            ]);
        }

        if(vibebp_get_setting('bp_single_page') && is_page(vibebp_get_setting('bp_single_page'))){

            wp_add_inline_style('vibebp_main','span.vibebp-login {width: 100vw;height: 100vh;display: flex;align-items: center;position:absolute;justify-content: center;color: transparent;background-color: transparent;
    background: '.(function_exists('vibe_get_customizer')?vibe_get_customizer('header_bg'):'').' url('.(function_exists('vibe_get_option')?vibe_get_option('logo'):(has_custom_logo()?esc_url( wp_get_attachment_url( get_theme_mod( 'custom_logo' ) ) ):'')).');background-repeat: no-repeat;background-position: center;}');
            
        }
        if(vibebp_get_setting('service_workers')){  
            $version = vibebp_get_setting('version','service_worker') ;
            $css = "@font-face {
                font-family: 'vicon';
                src:url('".plugins_url('../assets/fonts/vicon.eot?ver='.$version,__FILE__)."');
                src:url('".plugins_url('../assets/fonts/vicon.eot?ver='.$version,__FILE__)."') format('embedded-opentype'),
                    url('".plugins_url('../assets/fonts/vicon.woff?ver='.$version,__FILE__)."') format('woff'),
                    url('".plugins_url('../assets/fonts/vicon.ttf?ver='.$version,__FILE__)."') format('truetype'),
                    url('".plugins_url('../assets/fonts/vicon.svg?ver='.$version,__FILE__)."') format('svg');
                font-weight: normal;
                font-style: normal;
            }";
            wp_add_inline_style('vicons',$css);
        }
        
        wp_localize_script('vibebplogin','vibebp',$this->get_vibebp());
        if(!vibebp_get_setting('global_login') && !apply_filters('vibebp_enqueue_login_script',false))
            return;

        wp_enqueue_script('vibebplogin');
        
        add_action('wp_footer',function(){?><style>.vibebp_my_profile #vibebp_login_wrapper{display:none;}</style><?php });


        wp_enqueue_style('vicons',plugins_url('../assets/vicons.css',__FILE__),array(),VIBEBP_VERSION);
        
        wp_enqueue_script('vibebplogin',plugins_url('../assets/js/login.js',__FILE__),array('wp-element','wp-data','localforage'),VIBEBP_VERSION);
        wp_enqueue_script('pdf-view',plugins_url('../assets/js/pdfjs.js',__FILE__),array(),VIBEBP_VERSION,true);
        wp_localize_script('vibebplogin','vibebp',$this->get_vibebp());
        
        wp_register_script('plyr',plugins_url('../assets/js/plyr.js',__FILE__),array(),VIBEBP_VERSION,true);
        wp_register_style('plyr',plugins_url('../assets/css/plyr.css',__FILE__),array(),VIBEBP_VERSION);

        if(vibebp_get_setting('firebase_config','general','firebase')){

            wp_enqueue_script('firebase',plugins_url('../assets/js/firebase-app.js',__FILE__),array(),VIBEBP_VERSION);
            wp_enqueue_script('firebase-auth',plugins_url('../assets/js/firebase-auth.js',__FILE__),array(),VIBEBP_VERSION);
            wp_enqueue_script('firebase-database',plugins_url('../assets/js/firebase-database.js',__FILE__),array(),VIBEBP_VERSION);
            $check = false;
            if(function_exists('bp_is_user') && bp_is_user()){
                $check = true;
            }
        }
            

        $enqueue_script = false;
        
        
        if(apply_filters('vibebp_enqueue_profile_script',$enqueue_script)){
            //wp_dequeue_script('jquery');

            wp_enqueue_script('tus',plugins_url('../assets/js/tus.min.js',__FILE__),array(),VIBEBP_VERSION,true);
            
            wp_enqueue_style('vibebp_profile_libs',plugins_url('../assets/css/profile.css',__FILE__),array(),VIBEBP_VERSION);
            
            wp_enqueue_script('chartjs',plugins_url('../assets/js/Chart.min.js',__FILE__),array(),VIBEBP_VERSION,true);
            wp_enqueue_script('cropprjs',plugins_url('../assets/js/croppr.min.js',__FILE__),array(),VIBEBP_VERSION,true);
            wp_deregister_script('flatpickr');

            wp_enqueue_script('flatpickr',plugins_url('../assets/js/flatpickr.min.js',__FILE__),array(),VIBEBP_VERSION,true);
            
            
            wp_enqueue_style('iziToast',plugins_url('../assets/css/iziToast.min.css',__FILE__),array(),VIBEBP_VERSION);
            wp_enqueue_script('iziToast',plugins_url('../assets/js/iziToast.min.js',__FILE__),array(),VIBEBP_VERSION,true);

            if(vibebp_get_flatpickrLang() && vibebp_get_flatpickrLang()!='en'){
                 wp_enqueue_script('flatpickrLang','https://npmcdn.com/flatpickr@4.6.13/dist/l10n/'.vibebp_get_flatpickrLang().'.js',array(),null,true);

                 wp_add_inline_script('flatpickrLang','window.onload = function flatpickrLang(){flatpickr.localize(flatpickr.l10ns.'.vibebp_get_flatpickrLang().')}');
            }

            wp_enqueue_script('colorpickr',plugins_url('../assets/js/vanilla-picker.min.js',__FILE__),array(),VIBEBP_VERSION,true);
            
            if(!empty(vibebp_get_setting('mask_videos','general','editor'))){
                wp_enqueue_script('player-mask',plugins_url('../assets/js/player-mask.js',__FILE__),array(),VIBEBP_VERSION,true);
            }
            
           wp_enqueue_script('plyr');

            wp_enqueue_script('dragabillyjs',plugins_url('../assets/js/draggabilly.pkgd.min.js',__FILE__),array(),VIBEBP_VERSION,true);
            wp_enqueue_script('packeryjs',plugins_url('../assets/js/packery.pkgd.min.js',__FILE__),array('dragabillyjs'),VIBEBP_VERSION,true);

            wp_enqueue_style('vibebp-swiper',plugins_url('../assets/css/swiper-bundle.min.css',__FILE__));
            wp_enqueue_script('vibebp-swiper',plugins_url('../assets/js/swiper-bundle.min.js',__FILE__),array(),VIBEBP_VERSION,true);

            wp_enqueue_script('vibebpprofile',plugins_url('../assets/js/profile.js',__FILE__),array('wp-element','wp-data','packeryjs'),VIBEBP_VERSION,true);
            wp_localize_script('vibebpprofile','vibebp_profile_field_table',apply_filters('vibebp_profile_field_table',array()));

            wp_enqueue_script('vibebp-members-actions',plugins_url('../assets/js/actions.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION,true);
            $blog_id = '';
            if(function_exists('get_current_blog_id')){
                $blog_id = get_current_blog_id();
            }
            wp_localize_script('vibebp-members-actions','vibebpactions',apply_filters('vibebpactions_translations',array(
                'api_url'=>apply_filters('vibebp_rest_api',get_rest_url($blog_id,Vibe_BP_API_NAMESPACE)),
                'friends'=>bp_is_active('friends')?1:0,
                'followers'=>vibebp_get_setting('followers','bp','general')?1:0,
                'translations'=>array(
                    'message_text'=>__('Type message','vibebp'),
                    'message_subject'=>__('Message subject','vibebp'),
                    'cancel'=>__('Cancel','vibebp'),
                    'offline'=>__('Offline','vibebp'),
                )
            )));

            


            wp_enqueue_style('vicons',plugins_url('../assets/vicons.css',__FILE__),array(),VIBEBP_VERSION);
            wp_enqueue_style('plyr');

            if(apply_filters('vibebp_basic_editor',true)){
                wp_enqueue_script('vibe_editor',plugins_url('../assets/js/editor.js',__FILE__),array(),VIBEBP_VERSION,true);

                $editor_script_Args = $this->vibebp_editor_data();

                wp_localize_script('vibe_editor','vibeEditor',$editor_script_Args);
                wp_enqueue_style('vibe_editor',plugins_url('../assets/css/editor.css',__FILE__),array(),VIBEBP_VERSION);
            }
            
        }
    }

    function enqueue_editor(){
         
        wp_enqueue_script('vibe_editor',plugins_url('../assets/js/editor.js',__FILE__),array(),VIBEBP_VERSION,true);

        $editor_script_Args = $this->vibebp_editor_data();

        wp_localize_script('vibe_editor','vibeEditor',$editor_script_Args);
        wp_enqueue_style('vibe_editor',plugins_url('../assets/css/editor.css',__FILE__),array(),VIBEBP_VERSION);
        
    }

    function get_cardstack_types(){
        return array(
            array('label'=> __('Swipe Right','vibebp'),'value' =>'yuda'),
            array('label'=> __('Slide Right','vibebp'),'value' =>'krisna'),
            array('label'=> __('Swipe Down','vibebp'),'value' =>'wangi'),
            array('label'=> __('Swipe Up','vibebp'),'value' =>'wira'),
            array('label'=> __('Pinch Right','vibebp'),'value' =>'utari'),
            array('label'=> __('Flip Down','vibebp'),'value' =>'cinta'),
            array('label'=> __('Flip Up','vibebp'),'value' =>'eka'),
            array('label'=> __('Rotate Right','vibebp'),'value' =>'dian'), 
            array('label'=> __('Spring Right Pop','vibebp'),'value' =>'iman'),
            array('label'=> __('Slide Down','vibebp'),'value' =>'buana'),
            array('label'=> __('Fold','vibebp'),'value' =>'mawar'),
        );
    }
 
    function vibebp_editor_data(){
        if(!empty(vibebp_get_setting('editor_interface','general','editor')) && vibebp_get_setting('editor_interface','general','editor') == 'full'){
            wp_enqueue_script('cocoen',plugins_url('../assets/js/cocoen.min.js',__FILE__),array('vibe_editor'),VIBEBP_VERSION,true);
            wp_enqueue_style('cocoen',plugins_url('../assets/css/cocoen.min.css',__FILE__),array('vibe_editor'),VIBEBP_VERSION);
        }


        ob_start();
         
        if(vibebp_get_setting('editor_interface','general','editor') && vibebp_get_setting('editor_interface','general','editor') != 'basic'){ ?>
            window.addEventListener("load", (event) => {
              var s = document.createElement('script');
                s.type = 'text/javascript';
                s.async = true;
                s.id="mathjax";
                s.src = window.vibeEditor.mathjax+"?config=TeX-AMS-MML_HTMLorMML";
                var x = document.getElementsByTagName('script')[0];
                x.parentNode.insertBefore(s, x);
            });
        
        <?php } 
        //'<?php echo plugins_url('../assets/js/MathJax2.7.js?config=TeX-AMS-MML_HTMLorMML',__FILE__) 

        vibebp_editor_content_script();
        $script = ob_get_clean();
        wp_add_inline_script('vibebplogin',$script);

        $fonts=[];
        if(function_exists('vibe_get_option')){
            $google_fonts=vibe_get_option('google_fonts'); 
            if(!empty($google_fonts) && is_array($google_fonts)){
                foreach($google_fonts as $font){
                    $font_var = explode('-',$font);  
                    $fonts[]=$font_var[0];
                }
            }
        }
        $fonts = apply_filters('vibebp_editor_font_styles',$fonts);

        $editor_script_Args = apply_filters('vibe_editor_script_args',
        array(
            'blocks'=>array(
                'editor'=>1,
                'media'=>1,
                'columns'=>1,
                'shortcodes'=>1,
                'math'=>1,
                'video'=>plugins_url('../assets/images/video.svg',__FILE__),
                'audio'=>plugins_url('../assets/images/audio.svg',__FILE__)
            ),
            'mathjax'=>'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js',
            'media_order'=>array(
                'recent'=>__('Recently uploaded','vibebp'),
                'alphabetical'=>__('Alphabetical','vibebp'),
            ),
            'textdirection'=>is_rtl()?'RTL':'LTR',
            'block_attributes'=>array(
                'code'=>array(
                    'languages'=>array(
                        ''=>_x('Text','editor code block','vibebp'),
                        'js'=>_x('Javascript','editor code block','vibebp'),
                        'markup'=>_x('Markup','editor code block','vibebp'),
                        'css'=>_x('CSS','editor code block','vibebp'),
                        'aspnet'=>_x('Asp.net','editor code block','vibebp'),
                        'cpp'=>_x('C++','editor code block','vibebp'),
                        'c'=>_x('C','editor code block','vibebp'),
                        'cs'=>_x('C#','editor code block','vibebp'),
                        'coffee'=>_x('CoffeeScript','editor code block','vibebp'),
                        'dart'=>_x('Dart','editor code block','vibebp'),
                        'django'=>_x('dJango','editor code block','vibebp'),
                        'erlang'=>_x('Erlang','editor code block','vibebp'),
                        'xlsx'=>_x('Excel Formula','editor code block','vibebp'),
                        'flow'=>_x('Flow','editor code block','vibebp'),
                        'git'=>_x('Git','editor code block','vibebp'),
                        'go'=>_x('Go','editor code block','vibebp'),
                        'graphql'=>_x('GraphQL','editor code block','vibebp'),
                        'http'=>_x('HTTP','editor code block','vibebp'),
                        'java'=>_x('Java','editor code block','vibebp'),
                        'json'=>_x('JSON','editor code block','vibebp'),
                        'kotlin'=>_x('Kotlin','editor code block','vibebp'),
                        'matlab'=>_x('Matlab','editor code block','vibebp'),
                        'mongodb'=>_x('Mongodb','editor code block','vibebp'),
                        'nginx'=>_x('Nginx','editor code block','vibebp'),
                        'python'=>_x('Python','editor code block','vibebp'),
                        'php'=>_x('PHP','editor code block','vibebp'),
                        'plsql'=>_x('PLSQL','editor code block','vibebp'),
                        'powershell'=>_x('Powershell','editor code block','vibebp'),
                        'regex'=>_x('Regex','editor code block','vibebp'),
                        'jsx'=>_x('ReactJS','editor code block','vibebp'),
                        'sass'=>_x('SASS (Sass)','editor code block','vibebp'),
                        'scss'=>_x('SASS (scss)','editor code block','vibebp'),
                        'swift'=>_x('Swift','editor code block','vibebp'),
                        'scala'=>_x('Scala','editor code block','vibebp'),
                        'sql'=>_x('SQL','editor code block','vibebp'),
                        'ts'=>_x('Typescript','editor code block','vibebp'),
                        'vb'=>_x('Visual Basic','editor code block','vibebp'),
                        'yaml'=>_x('YAML','editor code block','vibebp'),

                    ),
                ),

            ),
            'customBlocks'=>[],
            'tooltip_styles'=>array(
                ''=>_x('Default','tooltip style','vibebp'),
                'primary'=>_x('Primary','tooltip style','vibebp'),
                'blue'=>_x('Blue','tooltip style','vibebp'),
                'green'=>_x('Green','tooltip style','vibebp'),
                'red'=>_x('Red','tooltip style','vibebp'),
                'orange'=>_x('Orange','tooltip style','vibebp'),
                'pink'=>_x('Pink','tooltip style','vibebp'),
                'purple'=>_x('Purple','tooltip style','vibebp'),
            ),
            'font_styles'=>$fonts,
            'embed_types'=>array(
                'image'=> __('image','vibebp'),
                'video'=> __('Video','vibebp'),
                'audio'=> __('Audio','vibebp'),
                'file'=> __('File','vibebp')
            ),
            'shortcodes' => array(
                array(
                    'key'=> 'note',
                    'title'=> __('Note','vibebp'),
                    'icon'=> '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
                    'attributes'=>array(
                        array(
                            'field_type'=> 'number',
                            'output'=> 'style',
                            'label'=> __('Margin','vibebp'),
                            'parameter'=> 'margin',
                            'suffix'=> 'px',
                            'default'=> 0
                        ),
                        array(
                            'field_type'=> 'number',
                            'output'=> 'style',
                            'label'=> __('Padding','vibebp'),
                            'parameter'=> 'padding',
                            'suffix'=> 'px',
                            'default'=> 0
                        ),
                        array(
                            'field_type'=> 'color',
                            'output'=> 'style',
                            'label'=> __('Background','vibebp'),
                            'parameter'=> 'background',
                            'default'=> ''
                        ),
                        array(
                            'field_type'=> 'color',
                            'output'=> 'style',
                            'label'=> __('Color','vibebp'),
                            'parameter'=> 'color',
                            'default'=> ''
                        )
                    )
                ),
                array(
                    'key'=> 'tab',
                    'title'=> __('Tab','vibebp'),
                    'icon'=> '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-credit-card"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="12" y1="10" x2="23" y2="10"></line><line x1="12" y1="5" x2="12" y2="10"></line></svg>',
                    'tabs'=> []  // [{title:'',content:'editordata}]
                ),
                array(
                    'key'=> 'accordion',
                    'title'=> __('Accordion','vibebp'),
                    'icon'=> '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>',
                    'accordions'=> []  // [{title:'',content:'editordata}]
                ),
                array(
                    'key'=> 'html',
                    'title'=> __('Html','vibebp'),
                    'icon'=> '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-code"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
                ),
                array(
                    'key'=> 'youtube',
                    'title'=> __('Youtube','vibebp'),
                    'icon'=> 'vicon vicon-youtube',
                ),
                array(
                    'key'=> 'vimeo',
                    'title'=> __('vimeo','vibebp'),
                    'icon'=> 'vicon vicon-vimeo',
                ),
                array(
                    'key'=> 'interactive',
                    'title'=> __('PPT','vibebp'),
                    'icon'=> '<?xml version="1.0" encoding="utf-8"?><svg version="1.1" id="Livello_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve"><style type="text/css">.pst0{fill:#ED6C47;} .pst1{fill:#FF8F6B;} .pst2{fill:#D35230;} .pst3{opacity:0.1;enable-background:new ;} .pst4{opacity:0.2;enable-background:new ;} .pst5{fill:url(#SVGID_1_);} .pst6{fill:#FFFFFF;}</style><path class="st0" d="M309.58,279.81L273.86,17.86h-2.67C141.3,18.24,36.1,123.44,35.72,253.33V256L309.58,279.81z"/><path class="st1" d="M276.53,17.86h-2.67V256l119.07,47.63L512,256v-2.67C511.62,123.44,406.42,18.24,276.53,17.86z"/><path class="st2" d="M512,256v2.62c-0.37,129.92-105.6,235.15-235.52,235.52h-5.24c-129.92-0.37-235.15-105.6-235.52-235.52V256H512   z"/><path class="st3" d="M285.77,134.91V389c-0.06,8.83-5.41,16.76-13.57,20.12c-2.6,1.1-5.39,1.67-8.22,1.67H91.92    c-3.33-3.81-6.55-7.86-9.53-11.91c-30.34-40.47-46.71-89.69-46.68-140.26v-5.24c-0.07-45.62,13.26-90.25,38.34-128.36   c2.62-4.05,5.36-8.1,8.33-11.91h181.58C275.97,113.21,285.68,122.91,285.77,134.91z"/><path class="st4" d="M273.86,146.81v254.09c0,2.82-0.57,5.62-1.67,8.22c-3.36,8.16-11.29,13.51-20.12,13.57H102.76  c-3.77-3.82-7.38-7.8-10.84-11.91c-3.33-3.81-6.55-7.86-9.53-11.91c-30.34-40.47-46.71-89.69-46.68-140.26v-5.24    c-0.07-45.62,13.26-90.25,38.34-128.36h178.01C264.07,125.11,273.77,134.82,273.86,146.81z"/><path class="st4" d="M273.86,146.81v230.28c-0.09,12-9.79,21.7-21.79,21.79H82.4c-30.34-40.47-46.71-89.69-46.68-140.26v-5.24    c-0.07-45.62,13.26-90.25,38.34-128.36h178.01C264.07,125.11,273.77,134.82,273.86,146.81z"/><path class="st4" d="M261.95,146.81v230.28c-0.09,12-9.79,21.7-21.79,21.79H82.4c-30.34-40.47-46.71-89.69-46.68-140.26v-5.24    c-0.07-45.62,13.26-90.25,38.34-128.36h166.1C252.16,125.11,261.86,134.82,261.95,146.81z"/><linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="45.5067" y1="-1120.0306" x2="216.4468" y2="-823.9694" gradientTransform="matrix(1 0 0 1 0 1228)"><stop offset="0" style="stop-color:#CA4C28"/><stop offset="0.5" style="stop-color:#C5401E"/><stop offset="1" style="stop-color:#B62F14"/></linearGradient><path class="st5" d="M21.83,125.02h218.3c12.05,0,21.83,9.77,21.83,21.83v218.3c0,12.05-9.77,21.83-21.83,21.83H21.83 C9.77,386.98,0,377.2,0,365.15v-218.3C0,134.8,9.77,125.02,21.83,125.02z"/><path class="pst6" d="M133.36,183.24c14.21-0.96,28.3,3.17,39.75,11.65c9.55,8.52,14.65,20.96,13.84,33.73    c0.16,8.88-2.21,17.62-6.82,25.21c-4.67,7.46-11.4,13.4-19.37,17.12c-9.12,4.24-19.08,6.33-29.14,6.12H104v51.32H75.72V183.24   H133.36z M103.97,254.89h24.34c7.72,0.57,15.37-1.72,21.52-6.42c5.08-4.88,7.75-11.75,7.28-18.78c0-16-9.3-23.99-27.89-23.99h-25.24 L103.97,254.89L103.97,254.89z"/></svg>',
                ),
                array(
                    'key'=> 'document',
                    'title'=> __('Document','vibebp'),
                    'icon'=> '<svg version="1.1" id="Livello_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve"><style type="text/css">.st0{fill:#41A5EE;} .st1{fill:#2B7CD3;} .st2{fill:#185ABD;} .st3{fill:#103F91;} .st4{opacity:0.1;enable-background:new ;} .st5{opacity:0.2;enable-background:new ;} .st6{fill:url(#SVGID_1_);} .st7{fill:#FFFFFF;}</style><path class="st0" d="M490.17,19.2H140.9c-12.05,0-21.83,9.72-21.83,21.7l0,0v96.7l202.42,59.2L512,137.6V40.9  C512,28.91,502.23,19.2,490.17,19.2L490.17,19.2z"/><path class="st1" d="M512,137.6H119.07V256l202.42,35.52L512,256V137.6z"/><path class="st2" d="M119.07,256v118.4l190.51,23.68L512,374.4V256H119.07z"/><path class="st3" d="M140.9,492.8h349.28c12.05,0,21.83-9.72,21.83-21.7l0,0v-96.7H119.07v96.7 C119.07,483.09,128.84,492.8,140.9,492.8L140.9,492.8z"/><path class="st4" d="M263.94,113.92H119.07v296h144.87c12.04-0.04,21.79-9.73,21.83-21.7v-252.6    C285.73,123.65,275.98,113.96,263.94,113.92z"/><path class="st5" d="M252.04,125.76H119.07v296h132.97c12.04-0.04,21.79-9.73,21.83-21.7v-252.6 C273.82,135.49,264.07,125.8,252.04,125.76z"/><path class="st5" d="M252.04,125.76H119.07v272.32h132.97c12.04-0.04,21.79-9.73,21.83-21.7V147.46   C273.82,135.49,264.07,125.8,252.04,125.76z"/><path class="st5" d="M240.13,125.76H119.07v272.32h121.06c12.04-0.04,21.79-9.73,21.83-21.7V147.46   C261.91,135.49,252.17,125.8,240.13,125.76z"/><linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="45.8183" y1="-1083.4916" x2="216.1361" y2="-788.5082" gradientTransform="matrix(1 0 0 1 0 1192)"><stop offset="0" style="stop-color:#2368C4"/><stop offset="0.5" style="stop-color:#1A5DBE"/><stop offset="1" style="stop-color:#1146AC"/></linearGradient><path class="st6" d="M21.83,125.76h218.3c12.05,0,21.83,9.72,21.83,21.7v217.08c0,11.99-9.77,21.7-21.83,21.7H21.83   C9.77,386.24,0,376.52,0,364.54V147.46C0,135.48,9.77,125.76,21.83,125.76z"/><path class="st7" d="M89.56,292.21c0.43,3.35,0.71,6.26,0.85,8.76h0.5c0.19-2.37,0.59-5.22,1.19-8.56c0.6-3.34,1.15-6.16,1.63-8.47  l22.96-98.49h29.68l23.81,97.01c1.38,6.03,2.37,12.15,2.96,18.3h0.39c0.44-5.97,1.27-11.9,2.48-17.76l18.99-97.6h27.02  l-33.36,141.13H157.1l-22.62-93.47c-0.65-2.69-1.4-6.2-2.23-10.53s-1.33-7.48-1.54-9.47h-0.39c-0.26,2.3-0.77,5.71-1.54,10.23   c-0.76,4.52-1.37,7.87-1.83,10.04l-21.27,93.17h-32.1L40.04,185.46h27.5l20.68,98.69C88.7,286.17,89.14,288.87,89.56,292.21z"/></svg>',
                ),
                array(
                    'key'=> 'spreadsheet',
                    'title'=> __('SpreadSheet','vibebp'),
                    'icon'=> '<svg version="1.1" id="Livello_1" xmlns:x="&ns_extend;" xmlns:i="&ns_ai;" xmlns:graph="&ns_graphs;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 2289.75 2130" enable-background="new 0 0 2289.75 2130" xml:space="preserve"> <metadata> <sfw xmlns="&ns_sfw;"> <slices></slices> <sliceSourceBounds bottomLeftOrigin="true" height="2130" width="2289.75" x="-1147.5" y="-1041"></sliceSourceBounds> </sfw> </metadata> <path fill="#185C37" d="M1437.75,1011.75L532.5,852v1180.393c0,53.907,43.7,97.607,97.607,97.607l0,0h1562.036 c53.907,0,97.607-43.7,97.607-97.607l0,0V1597.5L1437.75,1011.75z"/> <path fill="#21A366" d="M1437.75,0H630.107C576.2,0,532.5,43.7,532.5,97.607c0,0,0,0,0,0V532.5l905.25,532.5L1917,1224.75 L2289.75,1065V532.5L1437.75,0z"/> <path fill="#107C41" d="M532.5,532.5h905.25V1065H532.5V532.5z"/> <path opacity="0.1" enable-background="new " d="M1180.393,426H532.5v1331.25h647.893c53.834-0.175,97.432-43.773,97.607-97.607 V523.607C1277.825,469.773,1234.227,426.175,1180.393,426z"/> <path opacity="0.2" enable-background="new " d="M1127.143,479.25H532.5V1810.5h594.643 c53.834-0.175,97.432-43.773,97.607-97.607V576.857C1224.575,523.023,1180.977,479.425,1127.143,479.25z"/> <path opacity="0.2" enable-background="new " d="M1127.143,479.25H532.5V1704h594.643c53.834-0.175,97.432-43.773,97.607-97.607 V576.857C1224.575,523.023,1180.977,479.425,1127.143,479.25z"/> <path opacity="0.2" enable-background="new " d="M1073.893,479.25H532.5V1704h541.393c53.834-0.175,97.432-43.773,97.607-97.607 V576.857C1171.325,523.023,1127.727,479.425,1073.893,479.25z"/> <linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="203.5132" y1="1729.0183" x2="967.9868" y2="404.9817" gradientTransform="matrix(1 0 0 -1 0 2132)"> <stop offset="0" style="stop-color:#18884F"/> <stop offset="0.5" style="stop-color:#117E43"/> <stop offset="1" style="stop-color:#0B6631"/> </linearGradient> <path fill="url(#SVGID_1_)" d="M97.607,479.25h976.285c53.907,0,97.607,43.7,97.607,97.607v976.285 c0,53.907-43.7,97.607-97.607,97.607H97.607C43.7,1650.75,0,1607.05,0,1553.143V576.857C0,522.95,43.7,479.25,97.607,479.25z"/> <path fill="#FFFFFF" d="M302.3,1382.264l205.332-318.169L319.5,747.683h151.336l102.666,202.35 c9.479,19.223,15.975,33.494,19.49,42.919h1.331c6.745-15.336,13.845-30.228,21.3-44.677L725.371,747.79h138.929l-192.925,314.548 L869.2,1382.263H721.378L602.79,1160.158c-5.586-9.45-10.326-19.376-14.164-29.66h-1.757c-3.474,10.075-8.083,19.722-13.739,28.755 l-122.102,223.011H302.3z"/> <path fill="#33C481" d="M2192.143,0H1437.75v532.5h852V97.607C2289.75,43.7,2246.05,0,2192.143,0L2192.143,0z"/> <path fill="#107C41" d="M1437.75,1065h852v532.5h-852V1065z"/> </svg>',
                )
            ),
            'settings' => array(
                'shortcode' => array( 
                    'text_aligns' => array(
                        array('key'=> 'start','label'=> __('Start','vibebp')),
                        array('key'=> 'center','label'=> __('Center','vibebp')),
                        array('key'=> 'end','label'=> __('End','vibebp'))
                    )
                ),
                'memorygame'=> array(
                    'columns' => array(
                        array('label'=> __('Two','vibebp'),'value' =>2),
                        array('label'=> __('Three','vibebp'),'value' =>3),
                        array('label'=> __('Four','vibebp'),'value' =>4),
                        array('label'=> __('Five','vibebp'),'value' =>5)
                    )
                ),
                'cardstack'=> array(
                    'types' => $this->get_cardstack_types()
                )
            ),
            'translations'=>array(

                'add_tab'=> __('Add Tab','vibebp'),
                'add_accordion_tab'=> __('Add Accordion Tab','vibebp'),
                'enter_title'=> __('Enter Title...','vibebp'),
                'search'=> __('Search...','vibebp'),
                'view_form'=> __('View Form','vibebp'),
                'advance_elements'=> __('Advance Elements','vibebp'),
                'select_bg_color'=> __('Background color','vibebp'),
                'select_text_color'=> __('Text color','vibebp'),
                'select_fontsize'=> __('Font size','vibebp'),
                'select_font'=> __('Font','vibebp'),
                'margin'=> __('Margin','vibebp'),
                'padding'=> __('Padding','vibebp'),
                'save_content_template'=> __('Save Content Template','vibebp'),
                'select_card_image' => __('Select a card image','vibebp'),
                'choose_front_background_image' => __('Select a card image','vibebp'),
                'choose_back_background_image' => __('Back background image','vibebp'),
                'choose_background_image' => __('Choose background image','vibebp'),
                'choose_hotspot_background_image' => __('Choose Hotspots background image','vibebp'),
                'remove_background_image' => __('Remove image','vibebp'),
                'select_card_style'=> __('Select card style','vibebp'),
                'enter_html'=> __('Enter Html Element','vibebp'),
                'enter_url_id'=> __('Enter URL/ID','vibebp'),
                'choose_front_image' => __('Choose Front image','vibebp'),
                'choose_back_image' => __('Choose Back image','vibebp'),
                'same_window' => _x('Same window','','vibebp'),
                'new_window' => _x('New window','','vibebp'),
                'choose_image'=>__('Choose Image', 'vibebp'),
                'choose_images'=>__('Choose Images', 'vibebp'),
                'select_columns'=>__('Select Columns', 'vibebp'),
                'select_file'=>__('Select File', 'vibebp'),
                'select_type'=>_x('Select type','','vibebp'),
                'sort_groups' => _x('Sort Groups','','vibebp'),
            )
        )
        );

        if(!empty(vibebp_get_setting('editor_interface','general','editor'))){
            if(vibebp_get_setting('editor_interface','general','editor') == 'advanced'){
                $editor_script_Args['blocks']['math']=0;
            }
            if(vibebp_get_setting('editor_interface','general','editor') == 'basic'){
                $editor_script_Args['blocks']['columns']=0;
                $editor_script_Args['blocks']['shortcodes']=0;
                $editor_script_Args['blocks']['math']=0;
            }                
        }

        if(!empty(vibebp_get_setting('microlearning_elements','general','editor'))){
            $editor_script_Args['shortcodes'][]=array(
                'key'=> 'flashcard',
                'title'=> __('Flash Card','vibebp'),
                'icon'=> '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"  class="feather feather-credit-card"><rect x="1" y="1" width="22" height="22" rx="2" ry="2"></rect><line x1="12" y1="1" x2="12" y2="23"></line></svg>',
            );
            $editor_script_Args['shortcodes'][]=array(
                'key'=> 'scratchcard',
                'title'=> __('Scratch Card','vibebp'),
                'icon'=> '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-battery-charging"><path d="M5 18H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h3.19M15 6h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-3.19"></path><polyline points="11 6 7 12 13 12 9 18"></polyline></svg>',
            );
            $editor_script_Args['shortcodes'][]=array(
                'key'=> 'imagerevealer',
                'title'=> __('Image Reveal','vibebp'),
                'icon'=> '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-image"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline><line x1="12" y1="1" x2="12" y2="23"></line></svg>',
            );
            $editor_script_Args['shortcodes'][]=array(
                'key'=> 'hotspots',
                'title'=> __('HotSpots','vibebp'),
                'icon'=> '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
            );
            $editor_script_Args['shortcodes'][]=array(
                'key'=> 'memorygame',
                'title'=> __('Memory Game','vibebp'),
                'icon'=> '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
            );
            $editor_script_Args['shortcodes'][]=array(
                'key'=> 'cardstack',
                'title'=> __('Card Stack','vibebp'),
                'icon'=> '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"  class="feather feather-credit-card"><rect x="1" y="1" width="22" height="22" rx="2" ry="2"></rect><line x1="12" y1="1" x2="12" y2="23"></line></svg>',
            );
            if(defined('VIBEFORMS_VERSION')){
                $editor_script_Args['shortcodes'][]=array(
                    'key'=> 'vibe_forms',
                    'title'=> __('Forms','vibebp'),
                    'icon'=> '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"  class="feather feather-credit-card"><rect x="1" y="1" width="22" height="22" rx="2" ry="2"></rect><line x1="12" y1="1" x2="12" y2="23"></line></svg>',
                );
            }
            wp_enqueue_style('cardstack_component',plugins_url('../assets/css/cardstack/component.css',__FILE__),array('vibe_editor'),VIBEBP_VERSION);
            wp_enqueue_script('cardstack_classie',plugins_url('../assets/js/cardstack/classie.js',__FILE__),array('vibe_editor'),VIBEBP_VERSION,true);
            wp_enqueue_script('cardstack_dynamics_min',plugins_url('../assets/js/cardstack/dynamics.min.js',__FILE__),array('vibe_editor'),VIBEBP_VERSION,true);
            wp_enqueue_script('cardstack_modernizr-custom',plugins_url('../assets/js/cardstack/modernizr-custom.js',__FILE__),array('vibe_editor'),VIBEBP_VERSION,true);
            wp_enqueue_script('cardstack_main',plugins_url('../assets/js/cardstack/main.js',__FILE__),array('vibe_editor'),VIBEBP_VERSION,true);
            
        }

        return $editor_script_Args;
    }

    /*
    REGISTRATION FROM DEFAULT ROLE
     */
    function activate_user($user_id,$key,$user){
  
        $user_data =  $user;
        if(is_multisite()){
            if(!empty($user_data) && !empty($user_data['meta'])){
                if(!empty($user_data['meta']['wplms_meta'] )){
                    foreach ($user_data['meta']['wplms_meta'] as $key => $value) {
                        if($key = 'default_role'){
                            wp_update_user(array('ID'=>$user_id,'role'=>$value));
                            $new_user = new WP_User( $user_id );
                            $new_user->add_role( $value );
                        }
                        if($key = 'member_type' && function_exists('bp_set_member_type')){
                            bp_set_member_type($user_id,$value);
                        }
                        if(function_exists('groups_join_group') && $key = 'wplms_user_bp_group' && is_numeric($value)){
                            groups_join_group($value, $user_id );
                        }
                    }
                }
                
                if(bp_is_active( 'xprofile' )){
                    unset($user_data['meta']['wplms_meta']);
                    $user_fields = $user_data['meta'];
                    if(!empty($user_fields)){
                        
                        foreach($user_fields as $field_id=>$val){
                            $field_id = explode('_',$field_id);
                            foreach($field_id as $f){
                                if(is_numeric($f)){
                                    $field = $f;
                                }
                            }
                            if(isset($val['value']))
                                xprofile_set_field_data( $field, $user_id, $val['value'] );
                            if(isset($val['visibility']))
                                xprofile_set_field_visibility_level( $field, $user_id, $val['visibility'] );
                        }
                    }
                }


            }

        }else{
            $default_role = get_user_meta($user_id,'default_role',true);
            if(!empty($default_role)){
                wp_update_user(array('ID'=>$user_id,'role'=>$default_role));
                $new_user = new WP_User( $user_id );
                $new_user->add_role( $default_role );
            }
        }
        
       
    }


    /* Custom Activation email */
    function get_custom_activation_email_args($args,$email_type){
        if($email_type != 'core-user-registration' && (empty($_POST) || empty($_POST['settings'])))
            return $args;

        
        if(empty($_POST['settings']))
            return $args;
        $post_settings = json_decode(stripslashes($_POST['settings']));
        $settings = array();
        foreach($post_settings as $setting){
            $settings[$setting->id]=$setting->value;
        }
        
        if(!empty($settings['custom_activation_mail']) && is_numeric($settings['custom_activation_mail'])){
            
            if(wp_verify_nonce($_POST['security'],'bp_new_signup') ){
                $args['post__in'] = array($settings['custom_activation_mail']);
            }
        }elseif(!empty($settings['custom_activation_mail']) && $settings['custom_activation_mail'] == 'no'){
            
            if(wp_verify_nonce($_POST['security'],'bp_new_signup') ){
                $args['post__in'] = array(0); 
            }
        }
        return $args;
    }
}
VibeBP_Register::init();