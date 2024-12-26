<?php
/**
 * Initialise plugin
 *
 * @class       VibeBP_Init
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 * @copyright   VibeThemes
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



class VibeBP_API_Init{


    public static $instance;
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_API_Init();
        return self::$instance;
    }

    private function __construct(){

        add_action('rest_api_init',array($this,'rest_api'));
        //add_filter( 'widget_text', 'do_shortcode' );
        //add_filter('bp_rest_activity_get_item_permissions_check',array($this,'auth'),99,2);
        
    }

    function rest_api(){

        register_rest_route( Vibe_BP_API_NAMESPACE, '/loggedinmenu/', array(
            array(
                'methods'             => 'POST',
                'callback'            =>  array( $this, 'loggedin_menu' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));

        register_rest_route( Vibe_BP_API_NAMESPACE, '/profilemenu/', array(
            array(
                'methods'             => 'POST',
                'callback'            =>  array( $this, 'profile_menu' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        )); 
        register_rest_route( Vibe_BP_API_NAMESPACE, '/storeFirebaseUID/', array(
            array(
                'methods'             => 'POST',
                'callback'            =>  array( $this, 'storeFirebaseUID' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        )); 
        
        register_rest_route( Vibe_BP_API_NAMESPACE, '/updateFirebasePass/', array(
            array(
                'methods'             => 'POST',
                'callback'            =>  array( $this, 'updateFirebasePass' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        )); 
        register_rest_route( Vibe_BP_API_NAMESPACE, '/getPost/', array(
            array(
                'methods'             => 'POST',
                'callback'            =>  array( $this, 'getPost' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        )); 

        
        register_rest_route( Vibe_BP_API_NAMESPACE, '/sociallogin/', array(
            array(
                'methods'             => 'POST',
                'callback'            =>  array( $this, 'social_login' ),
                'permission_callback' => array( $this, 'social_login_verify' ),
            ),
        ));

        register_rest_route(Vibe_BP_API_NAMESPACE,'/sidebar/(?P<sidebar_id>[^/]+)',
            array(
                'methods'              => 'POST',
                'callback'             => array( $this, 'get_sidebar' ),
                'permissions_callback' => array( $this, 'user_permissions_check' ),
                'args'                      =>  array(
                    'sidebar_id'                        =>  array(
                        'validate_callback'     =>  function( $param, $request, $key ) {
                                                    return is_string( $param );
                                                }
                    ),
                ),
            )
        );
        register_rest_route(Vibe_BP_API_NAMESPACE,'/save_sidebar',
            array(
                'methods'              => 'POST',
                'callback'             => array( $this, 'save_sidebar' ),
                'permissions_callback' => array( $this, 'user_permissions_check' )
            )
        );

        register_rest_route(
            Vibe_BP_API_NAMESPACE,'/widget/(?P<id_base>[^/]+)/(?P<widget_number>[\d]+)',
            array(
                'methods'              => 'post',
                'callback'             => array( $this, 'get_widget' ),
                'permission_callback'  => array( $this, 'user_permissions_check' ),
                'args'                      =>  array(
                    'id_base'                        =>  array(
                        'validate_callback'     =>  function( $param, $request, $key ) {
                                                    return is_string( $param );
                                                }
                    ),
                ),
            )
        );


        register_rest_route(
            Vibe_BP_API_NAMESPACE,'/process_notification',
            array(
                'methods'              => 'post',
                'callback'             => array( $this, 'process_notification' ),
                'permission_callback'  => array( $this, 'user_permissions_check' )
            )
        );

        register_rest_route( Vibe_BP_API_NAMESPACE, '/user/fetch_media', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_member_attachments' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));
        register_rest_route( Vibe_BP_API_NAMESPACE, '/user/upload_media', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'upload_attachment' ),
                'permission_callback' => array( $this, 'user_upload_permissions_check' ),
            ),
        ));
        register_rest_route( Vibe_BP_API_NAMESPACE, '/user/upload_media_stream', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'upload_attachment_stream' ),
                'permission_callback' => array( $this, 'user_upload_stream_permissions_check' ),
            ),
        ));
        register_rest_route( Vibe_BP_API_NAMESPACE, '/user/upload_media_stream/(?P<file>\w+)', array(
            array(
                'methods'             =>  'PATCH',
                'callback'            =>  array( $this, 'patch_upload_attachment_stream' ),
                'permission_callback' => '__return_true',
                'args'                      =>  array(
                    'file'                        =>  array(
                        'validate_callback'     =>  function( $param, $request, $key ) {
                                                    return is_string( $param );
                                                }
                    ),
                ),
            ),
        ));
        register_rest_route( Vibe_BP_API_NAMESPACE, '/user/upload_media_stream/(?P<file>\w+)', array(
            array(
                'methods'             =>  'HEAD',
                'callback'            =>  array( $this, 'get_upload_attachment_offset' ),
                'permission_callback' => '__return_true',
                'args'                      =>  array(
                    'file'                        =>  array(
                        'validate_callback'     =>  function( $param, $request, $key ) {
                                                    return is_string( $param );
                                                }
                    ),
                ),
            ),
        ));

        register_rest_route( Vibe_BP_API_NAMESPACE, '/user/upload_media_stream/(?P<file>\w+)/complete_stream', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'finalise_upload_attachment_stream' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
                'args'                      =>  array(
                    'file'                        =>  array(
                        'validate_callback'     =>  function( $param, $request, $key ) {
                                                    return is_string( $param );
                                                }
                    ),
                ),
            ),
        ));
        register_rest_route( Vibe_BP_API_NAMESPACE, '/user/delete_media', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'delete_attachment' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));
        
        
        register_rest_route( Vibe_BP_API_NAMESPACE, '/registerUser', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'registerUser' ),
                'permission_callback' => array( $this, 'cliendID_check' ),
            ),
        ));

        register_rest_route( Vibe_BP_API_NAMESPACE, '/forgotPassword', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'forgotPassword' ),
                'permission_callback' => array( $this, 'cliendID_check' ),
            ),
        ));
        register_rest_route( Vibe_BP_API_NAMESPACE, '/resetPassword', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'resetPassword' ),
                'permission_callback' => array( $this, 'cliendID_check' ),
            ),
        ));


        register_rest_route( Vibe_BP_API_NAMESPACE, '/dashboard/widget/server_stats', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_server_stats' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));

        register_rest_route( Vibe_BP_API_NAMESPACE, '/dashboard/widget/sales_stats', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_sales_stats' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));
        
        register_rest_route( Vibe_BP_API_NAMESPACE, '/dashboard/widget/users_report', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_users_report' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));

        register_rest_route( Vibe_BP_API_NAMESPACE, '/getCurrentComponentTours', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'getCurrentComponentTours' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));

        register_rest_route( Vibe_BP_API_NAMESPACE, '/get_carousel', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_carousel' ),
                'permission_callback' => array( $this, 'cliendID_check' ),
            ),
        ));

        register_rest_route( Vibe_BP_API_NAMESPACE, '/selectcpt/(?P<cpt>\w+)', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'selectcpt' ),
                'permission_callback'       => array( $this, 'user_permissions_check' ),
                'args'                      =>  array(
                'cpt'                        =>  array(
                    'validate_callback'     =>  function( $param, $request, $key ) {
                                                return !empty( $param );
                                            }
                    ),
                ),
            ),
        ));

        register_rest_route( Vibe_BP_API_NAMESPACE, '/createGroupProduct', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'create_group_product' ),
                'permission_callback'       => array( $this, 'create_group_product_permissions_check' ),
            ),
        ));
        register_rest_route( Vibe_BP_API_NAMESPACE, '/getProduct/(?P<id>\d+)', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_product' ),
                'permission_callback'       => array( $this, 'user_permissions_check' ),
                'args'                      =>  array(
                'cpt'                        =>  array(
                    'validate_callback'     =>  function( $param, $request, $key ) {
                                                return !empty( $param );
                                            }
                    ),
                ),
            ),
        ));

        register_rest_route( Vibe_BP_API_NAMESPACE, '/taxonomy', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_taxonomy' ),
                'permission_callback'       => array( $this, 'user_permissions_check' ),
                'args'                      =>  array(
                'taxonomy'                        =>  array(
                    'validate_callback'     =>  function( $param, $request, $key ) {
                                                return !empty( $param );
                                            }
                    ),
                ),
            ),
        ));
        register_rest_route( Vibe_BP_API_NAMESPACE, '/generateAIContent', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'generateAIContent' ),
                'permission_callback'       => array( $this, 'user_permissions_check' )
            ),
        ));

        
    }

    

    function cliendID_check($request){
        $client_id = $request->get_param('client_id');
        if($client_id == vibebp_get_setting('client_id')){
            return true;
        }
        return false;
    }

    function create_group_product_permissions_check($request){
        $body = json_decode($request->get_body(),true);

        if (empty($body['token'])){
            return false;
        }else{
            $token = $body['token'];
        }

        
        /** Get the Secret Key */
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;

        if (empty($secret_key)){
            return false;
        }
        try {
            $user_data = JWT::decode($token, $secret_key, array('HS256'));

            $this->user = $user_data->data->user;

            if(user_can($this->user->id,'manage_options')){
                return true;
            }
            
            $admins = groups_get_group_admins($body['group_id']);
            if(!empty($admins)){
                $admin_ids = wp_list_pluck($admins,'user_id');
                if(in_array($this->user->id,$admin_ids)){
                    return true;
                }
            }else{
                return false;
            }
        }catch (Exception $e) {
            /** Something is wrong trying to decode the token, send back the error */
            return false;
        }
    }

    function user_permissions_check($request){
        
        $body = json_decode($request->get_body(),true);
            
        if(!empty($body['token'])){
            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
           
            if(!empty($this->user)){
                return true;
            }
        }

        return false;

    }

    function user_upload_permissions_check($request){

        $body = json_decode(stripslashes($_POST['body']),true);
        if(!empty($body['token'])){
            $this->user = apply_filters('vibebp_api_get_user_from_token','',$body['token']);
            if(!empty($this->user)){
                return true;
            }
        }
        return false;
    }

    function social_login_verify($request){

        $body = json_decode($request->get_body());

        if($body->client_id == vibebp_get_setting('client_id')){

            
            //https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com
            $firebase_public_keys = get_transient('firebase_public_keys');
            if(empty($firebase_public_keys)){
                $req = wp_remote_get('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com');
                $firebase_public_keys = wp_remote_retrieve_body($req);    
                set_transient('firebase_public_keys',$firebase_public_keys,900); //Google changes certs every hour
            }
            
            try {
                $token = JWT::decode($body->idToken, json_decode($firebase_public_keys,true), array('RS256'));
                $data = array(
                    'status' => 1,
                    'data' => $token,
                    'message'=>_x('Valid Token','Valid Token','vibebp')
                );
                return true;
            }catch (Exception $e) {
                
                return new WP_Error(
                    'rest_forbidden',
                    $e->getMessage(),
                    array( 'status' => rest_authorization_required_code() )
                );
            }

        }

        

        return false;
    }


    function loggedin_menu($request){
        //$this->user->id
        $menuLocations = get_nav_menu_locations(); 
        $menu = apply_filters('vibebp_loggedin_menu','loggedin',$this->user);
        if(empty($menuLocations[$menu])){
            return new WP_REST_Response(array('status'=>0,'message'=>__('No menu connected to loggedinmenu location.','vibebp')));
        }
        add_filter('wp_setup_nav_menu_item',array($this,'remove_buddypress_invalid_url'));
        $primaryNav = wp_get_nav_menu_items($menuLocations[$menu]);

        return new WP_REST_Response(array('status'=>1,'menu'=>apply_filters('vibebp_loggedin_menu_nav',$primaryNav)));
    }

    function profile_menu($request){
        
        $menu = apply_filters('vibebp_profile_menu','profile',$this->user);
        $menuLocations = get_nav_menu_locations(); 
        $menuID = 0;
        if(!empty($menuLocations[$menu])){
            $menuID = $menuLocations[$menu];
        }
        
        $nav = get_transient('bp_rest_api_nav');
        if(empty($nav)){
            $nav =get_option('vibebp_reload_nav');
        }
        if(!empty($nav)){
            foreach($nav as $key=>$value){
                $nav[$key]['icon'] = apply_filters('vibebp_component_icon','vicon vicon-menu',$value['css_id']);
            }    
        }
   
        $nav = apply_filters('vibebp_setup_nav',$nav);

        if(empty($menuID)){
            return new WP_REST_Response(array('status'=>1,'menu'=>[],'bpmenu'=>$nav,'message'=>__('No menu set for profile.','vibebp')));
        }
        add_filter('wp_setup_nav_menu_item',array($this,'remove_buddypress_invalid_url'));
        $primaryNav = wp_get_nav_menu_items($menuID);
        return new WP_REST_Response(array('status'=>1,'menu'=>apply_filters('vibebp_profile_menu_nav',$primaryNav),'bpmenu'=>$nav));
    }

    function remove_buddypress_invalid_url($menu_item){
        $menu_classes = $menu_item->classes;

        if ( is_array( $menu_classes ) ) {
            $menu_classes = implode( ' ', $menu_item->classes);
        }
        preg_match( '/\sbp-(.*)-nav/', $menu_classes, $matches );

        if ( empty( $matches[1] ) ) {
            return $menu_item;
        }

        $menu_item->icon = apply_filters('vibebp_component_icon','vicon vicon-menu',$matches[1]);
        $menu_item->post_content = $matches[1];
        $menu_item->css_id=$matches[1];
        $menu_item->_invalid =0;

        if(!vibebp_get_setting('bp_single_page')){
            $bp_pages = bp_nav_menu_get_loggedin_pages();
            if(!empty($bp_pages[$matches[1]]) && !empty($bp_pages[$matches[1]]->guid)){
                $menu_item->guid = $bp_pages[$matches[1]]->guid;
            }
                
        }
        return $menu_item;
    }

    function updateFirebasePass($request){
        $body = json_decode($request->get_body());
        $idtoken = get_user_meta($this->user->id,'firebase_uid',true);
        if(!empty($idtoken)){
            
        }
    }

    function storeFirebaseUID($request){
        $body = json_decode($request->get_body());
        update_user_meta($this->user->id,'firebase_uid',$body->user->firebaseUid);
    }

    function social_login($request){

        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        if (!$secret_key) {
            return new WP_REST_Response(array(
                'status'=>0,
                'code'=>'vibebp_jwt_security_missing',
                'message'=>_x('Secret key missing','JWT authentication error','vibebp'),
              )
            );
        }


        $body = json_decode($request->get_body());

        $firebase_public_keys = get_transient('firebase_public_keys');
        $token = JWT::decode($body->idToken, json_decode($firebase_public_keys,true), array('RS256'));
        $custom_validation = apply_filters('vibebp_custom_validations_for_social_login',false,$body,$token);
        if(!empty($custom_validation)){
            return new WP_REST_Response($custom_validation);
        }
        $user_id = email_exists($token->email);
        $user = get_userdata($user_id);
            
        /** If the authentication fails return a error*/
        if (!$user) {
            $name = explode(' ', $body->user->name);
            $user_id = wp_insert_user(apply_filters('vibebp_social_sigup',array(
                'user_login'=>sanitize_text_field($body->user->email),
                'user_pass'=>wp_generate_password(18,false,false),
                'user_nicename'=>sanitize_text_field($body->user->email),
                'user_email'=>sanitize_text_field($body->user->email),
                'display_name'=>sanitize_text_field($body->user->name),
                'nickname'=>sanitize_text_field($body->user->name),
                'first_name'=>sanitize_text_field($name[0]),
                'last_name'=>((count($name) > 1)?sanitize_text_field($name[(count($name)-1)]):''),
            )));

            if($user_id && !is_wp_error( $user_id )){
                $user = get_userdata($user_id); 
                wp_send_new_user_notifications( $user_id, 'admin' ); 
                do_action('vibebp_user_registered',$user_id);  
            }else{
                return new WP_REST_Response(
                    array(
                        'status'=>0,
                        'error'=>(is_wp_error( $user_id )?$user_id->get_error_message():''),
                        'message'=>_x('Unable to register.','login error','vibebp')
                    )
                );
            }
            
        }

        update_user_meta($user_id,'firebase_uid',$body->user->uid);
        
        
        $current_time = bp_core_current_time( true, 'timestamp' );
        bp_update_user_last_activity( $user_id, date( 'Y-m-d H:i:s', $current_time ) );
        
        /** Valid credentials, the user exists create the according Token */
        $issuedAt = time();
        $notBefore = apply_filters( VIBEBP.'_token_expire_not_before', $issuedAt, $issuedAt);
        $expire = apply_filters( VIBEBP.'_token_expire', $issuedAt  + (DAY_IN_SECONDS * 7), $issuedAt);


        $refresh_token = get_user_meta($user_id,'vibebp_refresh_token',true);
        if(empty($refresh_token)){
            $refresh_token = wp_generate_password(12);
            update_user_meta($user_id,'vibebp_refresh_token',$refresh_token);
        }


        $user->vibebp_token_key = wp_generate_password(6,false,false);
        $tokendata = array(
            'vibebp_token_key'=>$user->vibebp_token_key,
            'exp'=>$expire,
            'login_time'=>time(),
            'ip'=>vibebp_get_client_ip(),
            'usr'=>(!empty($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:''),
        );
        vibebp_add_user_active_tokens($user->data->ID,$tokendata);
        $token = array(
            'iss' => get_bloginfo('url'),
            'iat' => $issuedAt,
            'nbf' => $notBefore,
            'exp' => $expire,
            'vibebp_token_key' => $user->vibebp_token_key,
            'data' => apply_filters('vibebp_jwt_token_data',array(
                'user' => array( 
                    'id' => $user->ID,
                    'username'=>$user->user_login,
                    'slug'=>$user->user_nicename,
                    'email'=>$user->user_email,
                    'refresh_token'=>$refresh_token,
                    'exp' => $expire,
                    'avatar'=> (function_exists('bp_core_fetch_avatar')?bp_core_fetch_avatar(array(
                                    'item_id' => $user->ID,
                                    'object'  => 'user',
                                    'type'=>'full',
                                    'html'    => false
                                )):get_avatar($user->user_email,240)),
                    'displayname'=>$user->display_name,
                    'roles'=>$user->roles,
                    'caps'=> apply_filters('vibebp_user_caps',$user->allcaps,$user),
                    'profile_link'=>vibebp_get_profile_link($user->user_nicename)
                    ),
                'ip'=>vibebp_get_client_ip(),
                'client_id'=>vibebp_get_setting('client_id')
                )
            ),
        );
        /** Let the user modify the token data before the sign. */
        $token = JWT::encode(apply_filters(VIBEBP.'jwt_auth_token_before_sign', $token, $user), $secret_key);

        /** The token is signed, now create the object with no sensible user data to the client*/
        $data = array(
            'status' => 1,
            'token' => $token,
            'message'=>_x('Token generated','Token generated','vibebp')
        );
        /** Let the user modify the data before send it back */
        return new WP_REST_Response(apply_filters(VIBEBP.'jwt_auth_token_before_dispatch', $data, $user));
    }


    function get_sidebar($request){

        global $wp_registered_sidebars, $sidebars_widgets;
        $sidebar_id = $request->get_param( 'sidebar_id' );
        
        if(empty($this->user)){
            $body = json_decode($request->get_body(),true);
            $token = $body['token'];
            $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
            try {
                $user_data = JWT::decode($token, $secret_key, array('HS256'));
                $this->user = $user_data->data->user;
            }catch (Exception $e) {
                return new WP_REST_Response($e,200);
            }
        }
        $data = array('status'=>0);


        if ( ! isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
            return new WP_REST_Response(array('status'=>0,'message'=>__('No such sidebar.','vibebp').$sidebar_id,'sidebars'=>$wp_registered_sidebars));
        }
        global $wp_registered_widgets;
        
        $sidebar_id = apply_filters('vibebp_member_dashboard',$sidebar_id,$this->user->id);
        $widgets = get_user_meta($this->user->id,$sidebar_id,true);
        if(empty($widgets)){
            if(!empty($sidebars_widgets[ $sidebar_id ])){
                $data['status']=1;
                $data['widgets']=array();
                $data['unusedwidgets']=[];
                $widgets=$sidebars_widgets[ $sidebar_id ];
                foreach ( $widgets as $k=>$widget ) {
                    
                    preg_match( '/^(.+)-(\d+)$/', $widget, $matches );
                    $link = rest_url( Vibe_BP_API_NAMESPACE.'/widget/' . $matches[1] . '/' . $matches[2] );

                    if(!empty($wp_registered_widgets[ $matches[1] . '-' .  $matches[2]  ]) && !empty($widgets[$k])){
                        if(is_array($widgets[$k])){
                            $widgets[$k] = array_merge($widgets[$k],$wp_registered_widgets[ $matches[1] . '-' .  $matches[2]  ] );
                        }else{
                            $widgets[$k] = array();
                            $widgets[$k] = $wp_registered_widgets[ $matches[1] . '-' .  $matches[2]  ];
                        }
                        
                        $widget_options = get_option( 'widget_' . $matches[1]);
                        if(!empty($widget_options[$matches[2]])){
                            $widgets[$k]['options'] = $widget_options[$matches[2]];
                        }  
                        $widgets[$k]['id_base'] = $matches[1];
                        $widgets[$k]['link'] = $link;
                        $widgets[$k]['key'] = $widget; 
                        $temp_user = wp_get_current_user();
                        wp_set_current_user($this->user->id);
                        ob_start();
                        the_widget(get_class($widgets[$k]['callback'][0]),$widgets[$k]['options']);    
                        $widgets[$k]['html']=ob_get_clean();
                        wp_set_current_user($temp_user->id);
                        unset($widgets[$k]['callback']);
                    }
                }
            }
            $nwidgets = [];
            if(!empty($widgets)){
                foreach($widgets as $widget){
                    $nwidgets[] = $widget;
                }    
            }
            
            $data['widgets']=$nwidgets;
        }else{
            $data['status']=1;
           
            $data['unusedwidgets']=[];
            $data['customized']=true;
            if(!empty($sidebars_widgets[ $sidebar_id ])){
                foreach ( $sidebars_widgets[ $sidebar_id ] as $k=> $widget ) {
                    preg_match( '/^(.+)-(\d+)$/', $widget, $matches );

                    $link = rest_url( Vibe_BP_API_NAMESPACE.'/widget/' . $matches[1] . '/' . $matches[2] );
                    $flag=0;
                    if(!empty($widgets)){
                        foreach($widgets as  $wid){

                            if(is_array($wid)){

                                if($wid['link'] == $link){
                                    $flag=1;
                                }
                            }else{
                                if($wid == $link){
                                    $flag=1;
                                }
                            }
                        }
                    }
                    if(empty($flag)){
                        $data['unusedwidgets'][]=array(
                            'link'=>$link,
                            'name'=>$matches[1]
                        );
                    }
                }
            }
            if(!empty($widgets)){
                foreach($widgets as $k=>$widget){
                    $matches = [];
                    if(!empty($widget['id'])){
                        preg_match( '/^(.+)-(\d+)$/',$widget['id'], $matches );
                        if(!empty($matches) && !empty($wp_registered_widgets[$matches[1]. '-' .$matches[2]]) && !empty($widgets[$k])){

                            if(is_array($widgets[$k])){
                                $widgets[$k] = array_merge($widgets[$k],$wp_registered_widgets[ $matches[1] . '-' .  $matches[2]  ] );
                            }else{
                                $widgets[$k] = array();
                                $widgets[$k] = $wp_registered_widgets[ $matches[1] . '-' .  $matches[2]  ];
                            }
                            $widget_options = get_option( 'widget_' . $matches[1]);
                            if(!empty($widget_options[$matches[2]])){
                                $widgets[$k]['options'] = $widget_options[$matches[2]];
                            }  
                            $widgets[$k]['id_base'] = $matches[1];
                            
                            $init = VibeBP_Init::init();
                            if(empty($init->user_id)){
                                $init->user_id = $this->user->id;
                            }
                            ob_start();
                            the_widget(get_class($widgets[$k]['callback'][0]),$widgets[$k]['options']);
                            $widgets[$k]['html']=ob_get_clean();
                            unset($widgets[$k]['callback']);
                        }
                        if(empty($widget['key'])){
                            delete_user_meta($this->user->id,$sidebar_id);
                        }
                    } 
                } 

                $data['widgets']=$widgets;
            }
            
        }
        $data['sidebar'] = $sidebar_id;
        return new WP_REST_Response($data,200);
    }

    function save_sidebar($request){
        $body = json_decode($request->get_body(),true);

        if(empty($this->user)){
            $body = json_decode($request->get_body(),true);
            $token = $body['token'];
            $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
            try {
                $user_data = JWT::decode($token, $secret_key, array('HS256'));
                $this->user = $user_data->data->user;
            }catch (Exception $e) {
                return new WP_REST_Response($data,200);
            }
        }
        $sidebar_id = apply_filters('vibebp_member_dashboard',$body['sidebar_id'],$this->user->id);
        $message = __('Sidebar Saved.','vibebp');
        if(!empty($body['widgets'])){
            update_user_meta($this->user->id,$sidebar_id,$body['widgets']);

        }else{
            delete_user_meta($this->user->id,$sidebar_id);
            $message = _x('Sidebar Reset!','','vibebp');
        }
        
        
        return new WP_REST_Response(
            array('status'=>1,'widgets'=>$body['widgets'],'message'=>$message),200); 
    }

    function get_widget($request){

        $id_base = $request->get_param( 'id_base' );
        $number  = absint( $request->get_param( 'widget_number' ) );

        global $wp_registered_widgets;
        if ( ! isset( $wp_registered_widgets[ $id_base . '-' . $number ] ) ) {
            return new WP_REST_Response(array('status'=>0,'message'=>__('No widget found!','vibebp')),200);
        }
        $widget_options = get_option( 'widget_' . $id_base );
        $widget            = $wp_registered_widgets[ $id_base . '-' . $number ];
        $widget['id_base'] = $id_base;
        $widget['options'] = $widget_options[ $number ];
        
        $init = VibeBP_Init::init();
        if(empty($init->user_id)){
            $init->user_id = $this->user->id;
        }

        ob_start();
        the_widget(get_class($widget['callback'][0]),$widget['options']);
        $widget['html']=ob_get_clean();
        unset($widget['callback']);
         return new WP_REST_Response(array('status'=>1,'widget'=>$widget),200);
    }


    function process_notification($request){
        $body = json_decode($request->get_body(),true);
        $message = vibebp_process_notification($body); 
        if($message){
            if(is_array($message)){
                return new WP_REST_Response(
                    array(
                        'status'=>1,
                        'text'=>$message['message'],
                        'actions'=>$message['actions'],
                        'event'=>$body['type'],
                        'item_id'=>(!empty($body['chat_id'])?$body['chat_id']:'')
                    )
                ,200);    
            }else{
                return new WP_REST_Response(array('status'=>1,'text'=>$message),200);    
            }
        }else{
            return new WP_REST_Response(array('status'=>0,'message'=>$message),200);
        }
        
    }

    function getPost($request){
        $body = json_decode($request->get_body(),true);
        $return = array('status'=>0,'content'=> '<div class="message">'.__('No page found.','vibebp').'</div>');

        $query = new WP_Query(array(
            'post_type'=>'page',
            'p'=>$body['id']
        ));
        if($query->have_posts()){
            while($query->have_posts()){
                $query->the_post();
                global $post;
                do_action('vibebp_getPost',$body,$this); //extract user if content restricted to user
                ob_start();
                the_content();
                $scripts = apply_filters('vibebp_inside_pwa_scripts',array(),$body['id']);
                $styles = apply_filters('vibebp_inside_pwa_styles',array(),$body['id']);
                $objects = apply_filters('vibebp_inside_pwa_objects',array(),$body['id']);

                $content = ob_get_clean();

            }
            $return = array('status'=>1,'content'=>$content,'scripts'=>$scripts,'styles'=>$styles,'objects'=>$objects);
        }
        return new WP_REST_Response($return,200);
    }

    function get_member_attachments($request){
        $post = json_decode(file_get_contents('php://input'));
        $post = json_decode(json_encode($post),true);
        $list = array();


        $posts_per_page = 20;  // defualt per_page
        if(!empty($post['posts_per_page'])){
            if($post['posts_per_page']<=20){
                $posts_per_page = $post['posts_per_page'];
            }
        }
        if(!empty($post['post_mime_type']) && !is_array($post['post_mime_type'])){
            if(in_array($post['post_mime_type'],['document','spreadsheet','interactive'])){
                $this->keyed_mime_types = $this->keyed_mime_types();
                $types = [];
                foreach ($this->keyed_mime_types as $key => $value) {
                    if($value == $post['post_mime_type']){
                        $types[] = $key;
                    }
                }
                $post['post_mime_type'] = $types;
            }
        }

        if(!empty($post)){
            
            $query =  array(
                'post_type' => 'attachment',
                'post_status' => 'any',
                'post_mime_type' => !empty($post['post_mime_type'])?$post['post_mime_type']:"",
                'posts_per_page' => $posts_per_page,
                'paged' => !empty($post['paged'])?$post['paged']:1,
                'orderby' => !empty($post['order'])?$post['order']:null,
                's' => !empty($post['search_terms'])?$post['search_terms']:''
            ) ;
            if ( !empty( $query['orderby'] ) ) {
                if($query['orderby']=='alphabetical'){
                    $query['order']='ASC';
                }
            }
            if ( !empty( $query['s'] ) ) {
                global $wpdb;
                $post_in = [];
                $posts = $wpdb->get_results($wpdb->prepare("SELECT ID from {$wpdb->posts} WHERE guid LIKE %s AND post_status='inherit'",'%'.$wpdb->esc_like($query['s'] ).'%'));
                if(!empty($posts)){
                    foreach($posts as $key => $att){
                        $post_in[] =(int)$att->ID; 
                    }
                }
                if(!empty($post_in)){
                    if(!empty($query['post__in'])){
                        $query['post__in'] = array_merge($query['post__in'],$post_in);
                    }else{
                        $query['post__in'] = $post_in;
                    }
                    unset($query['s']);
                }
                
            }
            if(empty($query['s'])){
                unset($query['s']);
            }
            if(empty($query['orderby'])){
                unset($query['orderby']);
            }
            $query = apply_filters( 'ajax_query_attachments_args',$query);

            $query = apply_filters( 'vibebp_attachments_query',$query,$post,$this->user->id,$request);
            $media_query = new WP_Query($query);

            foreach ($media_query->posts as $post) {
                $list[] = $this->get_single_attachment($post);
            }
        }
        if(empty($list)){
            $data = array(
                'status' => 1,
                'message' => _x('No Media Found','No Media Found','vibe'),
                'data' => $list,
                '$query'=>$query
            );
        }else{
            $data = array(
                'status' => 1,
                'message' => _x('Media Found','Media Found','vibe'),
                'data' => $list,
                'total'=> $media_query->found_posts,
                '$query'=>$query
            );
        }
        $data = apply_filters('vibe_fetch_media',$data,$post);
        return new WP_REST_Response($data, 200); 
    }

    function get_single_attachment($post){
        $attachment_id = $post->ID;
        $data = apply_filters('get_single_attachment',array(
            'name' => $post->post_name,
            'id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id)
        ));
        $post_mime_type = get_post_mime_type($post);
        if(!empty($post_mime_type)){
            if(!isset($this->keyed_mime_types)){
                $this->keyed_mime_types = $this->keyed_mime_types();
            }
            if(!empty($this->keyed_mime_types[$post_mime_type])){
                $data['type'] = $this->keyed_mime_types[$post_mime_type];
            }else if($post_mime_type === "video/webm"){//not in wp
                $data['type'] = "video";
            }else{
                $data['type'] = null;
            }
        }
        return $data;
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

    function user_upload_stream_permissions_check($request){
        
        $meta_data = $request->get_header('Upload-Metadata');
        if(!empty($meta_data)){
            $meta_data = explode(',',$request->get_header('Upload-Metadata'));
            $data = explode(' ',$meta_data[0]);
            if($data[0] == 'token'){
                $token = base64_decode($data[1]);
                /** Get the Secret Key */
                $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;

                if (empty($secret_key)){
                    return false;
                }
                try {
                    $user_data = JWT::decode($token, $secret_key, array('HS256'));
                    $this->user = $user_data->data->user;
                    return true;
                }catch (Exception $e) {
                    return false;
                }
            }
        }
        
        return false;
    }
    function upload_attachment_stream($request){


        $user_id = $this->user->id;
        

        $meta_data = explode(',',$request->get_header('Upload-Metadata'));
        
        $fileData = array();
        foreach($meta_data as $data){
            
            foreach($meta_data as $data){
                if(strpos($data, 'filename') !== false){
                    $mdata = explode(' ', $data);
                    $fileName = base64_decode($mdata[1]);
                }else if(strpos($data, 'filetype') !== false){
                    $mdata = explode(' ', $data);
                    $fileType = base64_decode($mdata[1]);
                }else{
                    $mdata = explode(' ', $data);
                    $fileData[$mdata[0]] = base64_decode($mdata[1]);
                }
            }
        }

        $check = wp_check_filetype($fileName);
        $response = apply_filters('vibebp_upload_attachment',false,$meta_data);

        
        if($check['ext'] && !empty($fileName) && empty($response)){
            
            $upload_dir_base = wp_upload_dir();
            $fileName = sanitize_file_name($fileName);
            $file_name = $upload_dir_base['path'].'/'.$fileName;
            if ( file_exists( $file_name ) ) {
                 $fileName =  wp_unique_filename( $upload_dir_base['path'], $fileName,null);
                 $file_name = $upload_dir_base['path'].'/'.$fileName;
            }
            
            if ( ! function_exists( 'wp_handle_upload' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }
            WP_Filesystem();
            global $wp_filesystem;
            $wp_filesystem->put_contents( $file_name, '' );
            $response = new WP_REST_Response($data, 201);

            $blog_id = '';
            if(function_exists('get_current_blog_id')){
                $blog_id = get_current_blog_id();
            }
            // Addition of security stops offline uploads !! 
            $key = wp_generate_password(6,false,false);


            $fileData['name']=$fileName;
            $fileData['ext']=$fileType;
            $fileData['path']=$file_name;
            $fileData['offset']=0;
            $fileData['author']=$this->user->id;

            set_transient($key,$fileData,DAY_IN_SECONDS);

            $response->header( 'Location', get_rest_url($blog_id,Vibe_BP_API_NAMESPACE).'/user/upload_media_stream/'.$key.'?upload'); 
            return $response;
            
        }

        return new WP_REST_Response(array('status'=>0,'message'=>__('Upload failed','vibebp')), 200);
    }

    function patch_upload_attachment_stream($request){
        
        $key = $request->get_param('file');
        $file = get_transient($key);

        if(!empty($file)){
            if(file_exists($file['path'])){
                $file['offset'] = $file['offset']+$request->get_header('Content-Length');
                set_transient($key,$file,DAY_IN_SECONDS);
                $return = $this->patch($file['path'],$request->get_header('Upload-Offset'),file_get_contents('php://input'));
                if($return){
                    $response = new WP_REST_Response($return, 204);
                    $response->header( 'Upload-Offset', $file['offset']); 
                    return $response;
                }
            }
        }
        return new WP_REST_Response(array('status'=>0,'message'=>__('Upload failed','vibebp')), 200);
    }

    function get_upload_attachment_offset($request){
        $key = $request->get_param('file');
        $file = get_transient($key);
        $response = new WP_REST_Response('', 204); 
        $response->header( 'Upload-Offset', $file['offset']); 
        return $response;
    }


    function finalise_upload_attachment_stream($request){
        $key = $request->get_param('file');
        $file = get_transient($key);
        delete_transient($key);
        
        $filetype = wp_check_filetype( basename( $file['path'] ), null );

        $external_process = apply_filters('vibebp_finalise_upload_attachment_stream',0,$file,$this->user->id); 
        $attachment_data ='';
        if(empty($external_process)){
            $wp_upload_dir = wp_upload_dir();
            $attachment = array(
                'guid'           => $wp_upload_dir['url'] . '/' . basename( $file['path'] ), 
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file['path'] ) ),
                'post_content'   => '',
                'post_status'    => 'inherit',
                'post_author'    => $file['author']
            );
             
            // Insert the attachment.
            if(!empty($file['parent'])){
                $attach_id = wp_insert_attachment( $attachment, sanitize_text_field($file['name']), $file['parent'] );    
            }else{
                $attach_id = wp_insert_attachment( $attachment, sanitize_text_field($file['name']));    
            }
            
            require_once( ABSPATH . 'wp-admin/includes/media.php' );
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
          
        
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file['path'] );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            $relativefilePath = ltrim(str_replace($wp_upload_dir['basedir'],'',$file['path']),'/');
            update_post_meta($attach_id,'_wp_attached_file',$relativefilePath); //wordpress bug
            $attachment_data = $this->get_single_attachment(get_post($attach_id));
        }

        return new WP_REST_Response(apply_filters('vibebp_upload_attachment_stream_message',array('status'=>1,'data'=>$attachment_data,'message'=>sprintf(__('%s upload complete','vibebp'),basename($file['path'])))), 200);
    }

    function patch($file, $offset, $new_data) {
        if (!$f = fopen($file, 'r+b')) {
            return false;
        }
        fseek($f, $offset);
        fwrite($f, $new_data, strlen($new_data));
        return true;
        fclose($f);
    }

    function upload_attachment($request){

        if(empty($this->user)){
            new WP_REST_Response(array(), 401);
        }
        $body = json_decode(stripslashes($_POST['body']),true);
        $user_id = $this->user->id;
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        if(!empty($_FILES['file'])){
            $uploadedfile = $_FILES['file'];
            $file_mime_type= $_FILES['file']['type'];
            $file_size=$_FILES['file']['size'];
            $upload_overrides = array( 'test_form' => false );

            $can_upload = apply_filters('vibe_can_upload_media',true,$_FILES,$request);
            if($can_upload){
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
                            'post_author'      => $user_id,
                            'post_size'      => $_FILES['file']['size']
                        );
                        // Insert the attachment.
                        $attach_id = wp_insert_attachment( $attachment, $filePath );
                        if(!empty($attach_id)){
                            $post = get_post($attach_id);
                            if($post){
                                $attachment_data = $this->get_single_attachment($post);
                                $return = array('status'=> 1,'message'=>_x('File is valid, and was successfully uploaded','vibe'),'data'=>$attachment_data);
                            }
                        }
                    }
                } else {
                    /**
                     * Error generated by _wp_handle_upload()
                     * @see _wp_handle_upload() in wp-admin/includes/file.php
                     */
                    $return=array('status'=> 0,'message'=>_x('File uploading failed','vibe'),'data'=>$movefile);
                }   
            }else{
                $return=array('status'=> 0,'message'=>_x('Can not upload','vibe'),'data'=>[]);
            }
        }else{
            $return=array('status'=> 0,'message'=>_x('File not found','vibe'),'data'=>[]);
        }
        return new WP_REST_Response($return, 200);
    }

    function delete_attachment($request){
        $body = json_decode($request->get_body(),true);
        
        $author = get_post_field('post_author',$body['media']['id']);
        if($this->user->id == $author){
            if(wp_delete_attachment($body['media']['id'])){
                return new WP_REST_Response(array('status'=>1,'message'=>__('Attachment deleted.','vibebp')), 200);
            }
        }

        return new WP_REST_Response(array('status'=>0,'message'=>__('Can not delete attachment.','vibebp')), 200);
    }

    function resetPassword($request){
        $body = json_decode($request->get_body(),true);
        $data = array('status'=>0,'message'=>_x('Something went wrong','','vibebp'));
        if(!empty($body['args']) && !empty($body['args']['key'])){
            if(empty($body['password']['first']) || empty($body['password']['second'])){
                $data['message'] = _x('Please enter Password','','vibebp');
                return new WP_REST_Response($data,200);
            }

            $firstpwd = sanitize_text_field($body['password']['first']);
            $secondpwd = sanitize_text_field($body['password']['second']);
            $key = sanitize_text_field($body['args']['key']);
            $user_login = sanitize_text_field($body['args']['user_login']);


            if($firstpwd!==$secondpwd){
                $data['message'] = _x('Password does not match!','','vibebp');
                return new WP_REST_Response($data,200);
            }

            $user = check_password_reset_key( $key, $user_login );
            if(!empty($user)){
                if(is_wp_error($user)){
                    $data['message'] = _x('Invalid key match!','','vibebp');
                    return new WP_REST_Response($data,200);
                }
                reset_password( $user, $firstpwd );
                $data = array('status'=>1,'message'=>_x('Password reset successfully!','','vibebp'));
                return new WP_REST_Response($data,200);
            }

        }
        return new WP_REST_Response($data,200);
    }

    function forgotPassword($request){

        $body = json_decode($request->get_body(),true);

        if(empty($body['email'])){
            return new WP_REST_Response(array('status'=>0,'message'=>__('Invalid email.','vibebp')), 200);
        }

        $user_data = get_user_by( 'email', trim( $body['email'] ) );
        if(!$user_data){
            $user_data = get_user_by( 'login', trim( $body['email'] ) );
        }

        if(!$user_data){
            //bail out
            return new WP_REST_Response(array('status'=>0,'message'=>_x('No registered user found!','forgot password email message','vibebp')),200);
        }

        // Redefining user_login ensures we return the right case in the email.
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;

        //Important WordPress hooks
        do_action( 'retreive_password', $user_login );
        
        $allow = apply_filters( 'allow_password_reset', true, $user_data->ID );
        if ( ! $allow ) {
            return new WP_REST_Response(array('status'=>0,'message'=>__('Password reset is not allowed for this user','vibebp')),200);
        } elseif ( is_wp_error( $allow ) ) {
            return new WP_REST_Response(array('status'=>0,'message'=>__('Password reset is not allowed.','vibebp')),200);
        }

        // Generate something random for a password reset key.
        $key = wp_generate_password( 20, false );

        do_action( 'retrieve_password_key', $user_login, $key );

        // Now insert the key, hashed, into the DB.
        if ( empty( $wp_hasher ) ) {
            require_once ABSPATH . WPINC . '/class-phpass.php';
            $wp_hasher = new PasswordHash( 8, true );
        }

        global $wpdb;
        $hashed = time() . ':' . $wp_hasher->HashPassword( $key );
        $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

        
        $furl  = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
        $app_page = vibebp_get_setting('bp_single_page');


        if(!empty($app_page) && $page_link = get_permalink($app_page)){
            $furl = $page_link.'?action=rp&key='.$key.'&login='.rawurlencode($user_login);
        }else{
            $global_login = vibebp_get_setting('global_login');
            $furl = network_site_url().'?action=rp&key='.$key.'&login='.rawurlencode($user_login);
        }
        $bpargs = array(
            'tokens' => array(
                'user.username'=>$user_login,
                'user.forgotpasswordlink'=> '<a href="'.$furl.'">'.$furl.'</a>',
                ),
        );
        
        $email_template = apply_filters('vibebp_forgot_password','vibebp_forgot_password');
        
        $check = bp_send_email( $email_template,$user_email, $bpargs );
        //vibebp_forgot_password
        if ( function_exists('bp_send_email') && !$check){
            return new WP_REST_Response(array('status'=>1,'message'=>__('The e-mail could not be sent.','vibebp') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.','vibebp') ),200);
        }else{
            return new WP_REST_Response(array('status'=>1,'message'=>_x('Please check your email for password recovery !','forgot password mail message','vibebp')),200);
        }
        return new WP_REST_Response(array('status'=>1,'message'=>__('Something went wrong','vibebp')), 200);
    }

    function registerUser($request){
        $body = json_decode($request->get_body(),true);

        $has_email=0;
        $has_password=0;
        $recaptchaToken = 0;
        $usermeta= [];
        $membertype = '';
        $invite_code = '';
        if(is_array($body)){
            foreach($body as $key => $value){
                if(!empty($value['type'])){
                    if($value['type'] == 'email'){
                        $has_email=1;
                        if(!is_email($value['value'])){
                            return new WP_REST_Response(array('status'=>0,'message'=>__('Invalid email.','vibebp')), 200);
                        }else{
                            $has_email = $value['value'];
                        }
                    }
                    if($value['type'] == 'password'){
                        $has_password=$value['value'];
                    }
                    if($value['type'] == 'token'){
                        $recaptchaToken = $value['recaptchaToken'];
                    }
                }
                
                //handle profile fields now
                if(!empty($value['id'])  ){
                    if(is_numeric($value['id'])){
                        if(!empty($value['required']) && empty($value['value'])){
                            return new WP_REST_Response(array('status'=>0,'message'=>sprintf(__('%s is a required field!','vibebp'),$value['name']),'err'=>(empty($apiBody['error-codes'])?'':$apiBody['error-codes'])), 200);
                        }
                        
                        if(!empty($value['type']) && !empty($value['value'])){
                            if($value['type'] == 'datebox' ){
                                $value['value'] = date('Y-m-d H:i:s',strtotime($value['value']));
                            }
                            if( ($value['type']=='gallery')){
                                $value['value'] = json_encode(wp_slash($value['value']));
                            }
                            
                            if(is_array($value['value']) && !empty($value['type']) && $value['type'] === 'table'){
                                $value['value'] = json_encode(wp_slash($value['value']));
                            }
                            $usermeta['field_' . $value['id']] = $value['value'];
                            if(empty($usermeta['profile_field_ids'])){
                                $usermeta['profile_field_ids'] = [];
                            }
                            if(!in_array($value['id'], $usermeta['profile_field_ids'])){
                                $usermeta['profile_field_ids'][] = $value['id'];
                                $usermeta['field_' . $value['id'].'_visibility'] = apply_filters('vibebp_register_user_field_visibility','public',$value,$body);
                            }
                            
                        }
                    }
                    if($value['id']=='member_type' && !empty($value['value'])){
                        $membertype =$value['value'];

                    }
                    if($value['id']=='invite_code' && !empty($value['value'])){
                        $invite_code =$value['value'];

                    }
                     
                }
                
            }
        }

        $flag = 0;
        if($has_password && $has_email){
            $flag =1;
        }
        
        $flag = apply_filters('vibebp_register_user_bypass',$flag,$body);

        if(vibebp_get_setting('google_captcha_private_key','general','login')){
            $res = wp_remote_post('https://www.google.com/recaptcha/api/siteverify',array(
                'sslverify' => true,
                'headers'   => [
                    'content-type' => 'application/json',
                ],
                'body'=>[
                    'secret'=>vibebp_get_setting('google_captcha_private_key','general','login'),
                    'response'=>$recaptchaToken
                ]
            ));
            $apiBody     = json_decode( wp_remote_retrieve_body( $res ),true );
            if(is_array($apiBody) && $apiBody['success']){
                return new WP_REST_Response(array('status'=>0,'message'=>__('Captcha did not match.','vibebp'),'err'=>(empty($apiBody['error-codes'])?'':$apiBody['error-codes'])), 200);
            }
        }

        if($flag){
            if(email_exists($has_email)){
                return new WP_REST_Response(array('status'=>0,'message'=>__('User already registered with email ID.','vibebp')), 200);
            }else{
                $user_args['user_email'] = $has_email;
                $user_args['user_login'] = $user_args['user_email'];
                $user_args['user_pass'] = $has_password;
                //$user_id = wp_insert_user($user_args);
                $user_args = apply_filters('vibebp_register_user_args_signup',$user_args,$body);


                if(!empty($invite_code)){
                    
                    $invite_codes = vibebp_get_setting('registration_invite_codes','general','login');
                    if(!empty($invite_code)){
                        $invite_codes = explode(',', $invite_codes);

                        if(apply_filters('vibebp_registration_invite_codes',in_array($invite_code, $invite_codes),$invite_code)){
                            $user_id = wp_insert_user($user_args);
                            
                            if ( ! is_wp_error( $user_id ) ) {
                                if(!empty($usermeta)){
                                    foreach($usermeta as $field_id=>$val){
                                        $field_id = str_replace('field_', '', $field_id);
                                        if(isset($val)){
                                            
                                            $field  = new BP_XProfile_Field( $field_id );
                                            if($field->type=='upload'){
                                                remove_filter( 'xprofile_data_value_before_save',          'xprofile_sanitize_data_value_before_save', 1, 4 );
                                            }
                                            xprofile_set_field_data( $field_id, $user_id, $val);
                                            if($field->type=='upload'){
                                                 add_filter( 'xprofile_data_value_before_save',          'xprofile_sanitize_data_value_before_save', 1, 4 );
                                            }
                                            
                                        }
                                    }
                                }

                                if(!empty($membertype) && function_exists('bp_set_member_type')){
                                    bp_set_member_type( $user_id,$membertype );
                                    
                                }
                                do_action('vibebp_invite_code_registration',$user_id,$user_args,$usermeta,$invite_code);
                                return new WP_REST_Response(array('status'=>1,'message'=>__('Congratulations ! you have been successfully registered !You can login using the chosen password and email!','vibebp')), 200);
                            }else{
                                return new WP_REST_Response(array('status'=>0,'message'=>$user_id->get_error_message()), 200);
                            }
                        }else{
                            return new WP_REST_Response(array('status'=>0,'message'=>_x('Invalid inivtation code!','','vibebp')), 200);
                        }
                        
                    }else{
                        return new WP_REST_Response(array('status'=>0,'message'=>_x('Invitation code not set!','','vibebp')), 200);
                    }
                    
                }else{
                    $usermeta['password'] = wp_hash_password( $has_password );
                    if(!empty($usermeta['profile_field_ids'])){
                        $usermeta['profile_field_ids'] = implode(',', $usermeta['profile_field_ids']);
                    }
                    $user_id = bp_core_signup_user( $user_args['user_login'], $has_password, $user_args['user_email'], $usermeta );
                    if(!empty($usermeta) && !empty($user_id)){
                        foreach($usermeta as $k =>$v){
                            xprofile_set_field_data( str_replace('field_', '', $k), $user_id, $v );
                           
                        }
                    }
                    if(!empty($membertype) && function_exists('bp_set_member_type')){
                        bp_set_member_type( $user_id,$membertype );
                        
                        
                    }
                    
                    do_action('vibebp_register_user',$user_id,$body);
                    return new WP_REST_Response(array('status'=>1,'message'=>__('Please check your email to activate your account.','vibebp')), 200);
                }
            }

        }else{

            return new WP_REST_Response(array('status'=>0,'message'=>apply_filters('vibebp_register_error',_x('Email ID or Password Missing','registration password email message','vibebp'))),200);
        }
    }


    // widget functions

    function get_server_stats($request){
        $body = json_decode($request->get_body(),true);

        $return = array( 'status' => 0 );
        if( !user_can($this->user->id,'manage_options' ) ){
            return new WP_REST_Response($return,200);
        }
            
        vibebp_load_widgets_functions();//dynamic loading

        $memUsage = getServerMemoryUsage(false);
        $cpuLoad = getServerLoad();
      
        $data = array(
            'memory_used' => empty($memUsage["total"])?0:getNiceFileSize($memUsage["total"] - $memUsage["free"]),
            'memory_total' => empty($memUsage["total"])?0:getNiceFileSize($memUsage["total"]),
            'memory_used_percentage' => number_format(getServerMemoryUsage(true), 2).'%',
            'cpu_used_percentage' => $cpuLoad.'%',
        );

        $return = array(
            'status' => 1,
            'data' => $data
        );

        return new WP_REST_Response(apply_filters('get_server_stats_widget',$return,$this->user->id),200);
    }

    

    function get_users_report($request){
        $body = json_decode($request->get_body(),true);

        $return = array( 'status' => 0 );
        if( !user_can($this->user->id,'manage_options' ) ){
            return new WP_REST_Response($return,200);
        }

        $total_activity = $not_logged_in = __('Buddypress:Activity not active!','vibebp');
        $most_active_user = 0;
        if(bp_is_active('activity')){
            global $wpdb,$bp;
            $total_activity = (int)$wpdb->get_var( "SELECT COUNT(id) FROM {$bp->activity->table_name}");
            
            $most_active = $wpdb->get_results("SELECT COUNT(id) as cact, user_id FROM {$bp->activity->table_name} GROUP BY user_id HAVING MAX(cact) ORDER BY cact DESC LIMIT 1");
            if(!empty($most_active) && !empty($most_active[0])){
                $most_active_user = (int)$most_active[0]->user_id;
            }
            $tempd = strtotime(date("Y-m-d h:i:s", strtotime("-7 day")));
            $date = "'".date("Y-m-d h:i:s",$tempd)."'";
            $not_logged_in = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE `meta_key` LIKE 'last_activity' AND `meta_value` > {$date}");
        }
        $user_count_data = count_users();
        $instructror_count = 0;
        if(!empty($user_count_data['avail_roles']) && !empty($user_count_data['avail_roles']['instructor'])){
            $instructror_count  = $user_count_data['avail_roles']['instructor'];
        }
        
        $data = array(
           'total_users' => $user_count_data['total_users'],
           'students_instructors' => $user_count_data['avail_roles']['student'].'/'.$instructror_count,
           'total_users_activity_count' => $total_activity,
           'most_active_user'=> $most_active_user,
           'users_did_not_login_in_7days' => $not_logged_in 
        );

        $return = array(
            'status' => 1,
            'data' => $data
        );

        return new WP_REST_Response(apply_filters('get_users_report_widget',$return,$this->user->id),200);
    }

    function parseDate($date){
        return str_replace(": ",":",$date);
    }
    function get_sales_stats($request){
        $body = json_decode($request->get_body(),true);

        $return = array( 'status' => 0 );
        if( !user_can($this->user->id,'manage_options' ) ){
            return new WP_REST_Response($return,200);
        }

        $start = $this->parseDate(date('Y-m-d h:i:s',$body['args']['start']/1000)); $end =  $this->parseDate(date('Y-m-d h:i:s',$body['args']['end']/1000));
        $total_sales = $total_instructor_commission =  $total_payments_count = $most_earning_by_instructor = $most_sold_course
                        =  __('WooCommerce Not Active!','vibebp');
        $data = [];
        if(class_exists( 'woocommerce' ) ){
            $data = array(
                'total_sales' => wc_price($this->get_total_sales(false,$start,$end)),
                'total_instructors_commission' => $this->get_total_instructors_commission(false,$start,$end),
                'total_payments_count' => $this->get_total_payments_count(false,$start,$end),
                'most_earning_by_instructor' => $this->get_most_earning_by_instructor(false,$start,$end),
                'most_sold_course' => $this->get_most_sold_course(false,$start,$end),
            );
        }              
        
        $return = array(
            'status' => 1,
            'data' => $data,
            
        );

        return new WP_REST_Response(apply_filters('get_sales_stats_widget',$return,$this->user->id,$body),200);
    }

    function get_total_sales($all,$start,$end){
        global $wpdb;  $where = "`status` LIKE 'wc-completed'";
        if(empty($all)){ $where .= " AND `date_created` >= '{$start}' AND `date_created` <='{$end}'"; }
        $table_name = $wpdb->prefix.'wc_order_stats';
        $value = $wpdb->get_var("SELECT SUM(net_total) FROM {$table_name} where {$where}");
        return $value;
    }

    function get_total_payments_count($all,$start,$end){
        global $wpdb;  $where = "`status` LIKE 'wc-completed'";
        if(empty($all)){ $where .= " AND `date_created` >= '{$start}' AND `date_created` <='{$end}'"; }
        $table_name = $wpdb->prefix.'wc_order_stats';
        $value = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} where {$where}");
        return $value;
    }

    function get_total_instructors_commission($all,$start,$end){
        global $wpdb,$bp;

        $currencies = $this->get_currencies();
        $results = array();
        if(!empty($currencies) && is_array($currencies)){

            $and_where = "";
            if(!empty($start) && !empty($end)){
                $and_where  .= " AND activity.date_recorded >= '$start' AND activity.date_recorded <='$end' ";
            }

            foreach ($currencies as $key => $value) {
                $currency = $value['currency'];
                if(!empty($currency)) {
                    $and_where .= " AND meta2.meta_value = '".$currency."' ";
                }
                $query = "
                    SELECT SUM(meta.meta_value)
                    FROM {$bp->activity->table_name} AS activity 
                    LEFT JOIN {$bp->activity->table_name_meta} as meta ON activity.id = meta.activity_id
                    LEFT JOIN {$bp->activity->table_name_meta} as meta2 ON activity.id = meta2.activity_id
                    WHERE     activity.component     = 'course'
                    AND     activity.type     = 'course_commission'
                    AND     meta.meta_key   LIKE '_commission%'
                    AND     meta2.meta_key   LIKE '_currency%' ".$and_where." ";
                $value = $wpdb->get_var( $query );

                if(!empty($value)){
                    $results[] = array(
                        'currency' => $currency,
                        'value' => $value
                    );
                }
            }
        }
        
        return $results;
    }
    
    function get_most_sold_course($all,$start,$end){
        global $wpdb,$bp;
        $results = array();
        $and_where='';
        if(empty($all)){ $and_where  .= " AND activity.date_recorded >= '$start' AND activity.date_recorded <='$end' "; }
        $query = "
        SELECT count(activity.item_id) as count ,activity.item_id as course_id 
        FROM wp_bp_activity AS activity
        WHERE activity.component = 'course' AND activity.type LIKE 'course_commission' ".$and_where."
        GROUP BY course_id ORDER BY count desc LIMIT 1";
        $value = $wpdb->get_results( $query ,ARRAY_A);
        if(!empty($value[0]) && !empty($value[0]['course_id'])){
            $value[0]['url'] = get_permalink($value[0]['course_id']);
            $results = $value[0];
        }
        return $results;
    }

    function get_most_earning_by_instructor($all,$start,$end){
        global $wpdb,$bp;
        $currencies = $this->get_currencies();
        $results = array();
        if(!empty($currencies) && is_array($currencies)){

            $and_where = "";
            if(empty($all)){ $and_where  .= " AND activity.date_recorded >= '$start' AND activity.date_recorded <='$end' "; }

            foreach ($currencies as $key => $value) {
                $currency = $value['currency'];
                if(!empty($currency)) {
                    $and_where .= " AND meta2.meta_value = '".$currency."' ";
                }
                $query = "
                    SELECT activity.user_id as user_id ,sum(meta.meta_value) as value
                    FROM {$bp->activity->table_name} AS activity 
                    LEFT JOIN {$bp->activity->table_name_meta} as meta ON activity.id = meta.activity_id
                    LEFT JOIN {$bp->activity->table_name_meta} as meta2 ON activity.id = meta2.activity_id
                    WHERE     activity.component     = 'course'
                    AND     activity.type     = 'course_commission'
                    AND     meta.meta_key   LIKE '_commission%'
                    AND     meta2.meta_key   LIKE '_currency%' ".$and_where." 
                    GROUP BY activity.user_id ORDER BY value  DESC LIMIT 1";
                $value = $wpdb->get_results( $query );
                if(!empty($value[0])){
                    $results[] = array(
                        'currency' => $currency,
                        'value' => $value[0]
                    );
                }
            }
        }
        
        return $results;
    }

    function get_currencies(){
        global  $wpdb,$bp;
        $currencies = $wpdb->get_results( "
        SELECT meta2.meta_value as currency
        FROM  {$bp->activity->table_name_meta} as meta2 
        WHERE  meta2.meta_key   LIKE '_currency%'
        AND meta2.meta_value IS NOT NULL
        GROUP BY meta2.meta_value
        ",ARRAY_A);
        return $currencies;
    }


    function getCurrentComponentTours($request){
        $body = json_decode($request->get_body(),true);

        if($body['component'] == 'profile'){$body['component'] = 'xprofile';}
        
        $query = new WP_Query(array(
            'post_type'=>'tour',
            'posts_per_page'=>99,
            'meta_query'=>array(
                'relation'=>'AND',
                array(
                    'key'=>'tour_component',
                    'value'=>$body['component'],
                    'compare'=>'='
                )
            )
        ));

        if($query->have_posts()){
            $tours = [];
            while($query->have_posts()){
                $query->the_post();
                $tour_role = get_post_meta(get_the_ID(),'tour_role',true);
                $can_view_tour = 1;
                if(!empty($tour_role)){
                    switch ($tour_role) {
                        case 'read':
                           
                            break;
                        case 'edit_posts':
                            if(!user_can($this->user->id,'edit_posts')){
                                $can_view_tour = 0;
                            }
                            break;
                        default:
                            if(!empty($this->user->member_type) && $tour_role !== $this->user->member_type){
                                $can_view_tour = 0;
                            }
                            break;
                    }
                }
                if($can_view_tour ){
                    $tour_id = get_the_ID();
                    if(function_exists('icl_object_id'))
                        $tour_id = icl_object_id(get_the_ID(), 'tours', true);

                    $tours[]=array('id'=>$tour_id,'title'=>get_the_title($tour_id),'description'=>get_the_content($tour_id),'steps'=>get_post_meta(get_the_ID($tour_id),'steps',true));  
                }
                
            }    
            wp_reset_postdata();

            
            if(count($tours)){
                foreach($tours as $t=>$tour){
                    $steps = [];
                    
                    foreach($tour['steps']['title'] as $i=>$title){
                        $steps[]=array(
                            'selector' =>$tour['steps']['selector'][$i],
                            'title' => $title,
                            'description' =>$tour['steps']['description'][$i],
                            'action' => $tour['steps']['action'][$i]
                        );
                    }
                    $tours[$t]['steps'] = $steps;
                }
            }
            $return = array( 'status' => 1,'tours'=>$tours,'count'=>$query->found_posts);
        }else{
            $return = array( 'status' => 0, 'message'=>_x('No tours found','api message','vibep') );
        }

        return new WP_REST_Response($return);
        
    }

    function get_carousel($request){

        $attributes = json_decode($request->get_body(),true);

        $s= vibebp_generate_carousel_shortcode($attributes);

        $return = do_shortcode($s);
        $shortcodes = VibeBp_Shortcodes::init();

        return new WP_REST_Response(['status'=>1,'carousel'=>$return,'args'=>(empty($shortcodes->args)?'':json_encode($shortcodes->args))]);

    }

    function selectcpt($request){

        $cpt= $request->get_param('cpt');
        $body = json_decode($request->get_body());
        $return = array();
        $args = array(
            'post_type'=>$cpt,
            'posts_per_page'=>99,
            's'=>$body->search,
        );
        

        $args = apply_filters('wplms_frontend_cpt_query',$args,$this->user);
        $query = new WP_Query($args);
        
        if($query->have_posts()){
            while($query->have_posts()){
                $query->the_post();
                global $post;
                $preturn = array('id'=>$post->ID,'text'=>$post->post_title,'link'=>get_permalink($post->ID));
                
                if($cpt == 'product'){
                    $product = wc_get_product($post->ID);
                    $preturn['text'] .= ' - '.$product->get_price_html();

                    $preturn['fields'] = apply_filters('wplms_product_fields',array(
                        'ID'=>$post->ID,
                        'post_title'=>$post->post_title,
                        'meta'=>array(
                            array('meta_key'=>'_price','meta_value'=>get_post_meta($post->ID,'_price',true)),
                            array('meta_key'=>'vibe_subscription','meta_value'=>get_post_meta($post->ID,'vibe_subscription',true)),
                            array('meta_key'=>'vibe_duration','meta_value'=>array('value'=>get_post_meta($post->ID,'vibe_duration',true),'parameter'=>get_post_meta($post->ID,'vibe_duration_parameter',true))
                            )
                        )
                    ));
                }
                $results[] = $preturn;
            }
            $return = array('status'=>true,'posts'=>$results);
        }else{
            $return = array('status'=>false,'message'=>_x('Sorry Something went wrong or invalid post type','','wplms'));
        }
        wp_reset_postdata();

        return new WP_REST_Response( $return, 200 );
    }

    function get_taxonomy( $request ) {

        $taxonomy = $request['taxonomy'];
        if(empty($taxonomy)){
            return;
        }
        // Prepare the element data
        $posts_data = array();
        
        $defaults = array(
            'taxonomy'  => $taxonomy,
            'number'    => 99,
            'hide_empty'=>false,
        );


        $args = array();
        //Enter REQUEST IN ARGS FOR FILTERS
        
        $args = wp_parse_args($args,$defaults);

        $categories = array();
        $terms = get_terms($args);
        

        if(!empty($terms) && !is_wp_error($terms)){

            foreach($terms as $term){ 
                $categories[]=$term;
            }
        }
        $posts_data = apply_filters( 'vibebp_api_get_course_terms', $categories, $request );
        return new WP_REST_Response( $posts_data, 200 );
    }

    function create_group_product($request){

        $body = json_decode($request->get_body(),true);

        if(empty($body['id'] )){
            $check_can_create = apply_filters('vibebp_user_can_set_group_price',true,$this->user->id,0);
            if(empty($check_can_create)){
                $return = array('status'=>false,'message'=>$check_can_create);
                return new WP_REST_Response( $return, 200 ); 
            }
            $args = apply_filters('vibebp_create_group_product_args',array(
                'post_type' => 'product',
                'post_title' => sanitize_textarea_field($body['post_title']),
                'post_content' => (!empty($body['post_content'])?wp_slash($body['post_content']):sanitize_textarea_field($body['post_title'])),
                'post_status'=>'publish',
                'post_author'=>$this->user->id
            ));
            $id = wp_insert_post($args);
            if(is_wp_error($id)){
                return new WP_REST_Response( array('status'=>0,'message'=>$id->get_error_message()), 200 );
            }else{
                update_post_meta($id,'group_product',$body['group_id']);
                if(!empty($body['meta'])){
                    foreach($body['meta'] as $meta){
                        update_post_meta($id,$meta['meta_key'],$meta['meta_value']);
                    }
                }
            }

        }else{
           
            $id =$body['id'];
            if(user_can('manage_options',$this->user->id) || get_post_field('post_author',$id) == $this->user->id){
                $can_edit= true;
            }

            $can_edit = apply_filters('vibebp_user_can_set_group_price',$can_edit,$this->user->id,$id);
            if(empty($can_edit)){
                return new WP_REST_Response( array('status'=>false,'message'=>__('Can not make changes.','vibebp')), 200 );
            }
            if(!empty($body['post_title']) || !empty($body['post_content'])){
                $args = apply_filters('vibebp_create_group_product_args',array(
                    'ID'=>$body['id'],
                    'post_title' => sanitize_textarea_field($body['post_title']),
                    'post_content' => !empty($body['post_content'])?wp_slash($body['post_content']):sanitize_textarea_field($body['post_title']),
                    'post_status'=>'publish',
                    'post_author'=>$this->user->id
                ));
                
                $id = wp_update_post($args);
                if(is_wp_error($id)){
                    return new WP_REST_Response( array('status'=>0,'message'=>$id->get_error_message()), 200 );
                }else{
                    update_post_meta($id,'group_product',$body['group_id']);
                    if(!empty($body['meta'])){
                        foreach($body['meta'] as $meta){
                            update_post_meta($id,$meta['meta_key'],$meta['meta_value']);
                        }
                    }
                }
            }
        }

        $product = wc_get_product($id);
         return new WP_REST_Response( array('status'=>1,'message'=>__('Price set.','vibebp'),'show_value'=>['value'=>$product->get_price(),'id'=>$id,'fields'=>$fields,'text'=>$product->get_price_html()]), 200 );
    }

    function get_product($request){
        $id = $request->get_param('id');
        $product = get_product($id);
        return new WP_REST_Response( array('status'=>1,'show_value'=>['value'=>$product->get_price(),'id'=>$id,'text'=>get_the_title($id).' - '.$product->get_price_html()]), 200 );
    }

    function generateAIContent($request){
         $body = json_decode($request->get_body(),true);
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions',[
            'method'      =>'POST',
            'timeout'     => 45,
            'blocking'    => true,
            'headers'     => [
                'Authorization'=>'Bearer '.vibebp_get_setting('chatgpt_api_key','ai','chatgpt'),
                'content-type'=>'application/json'
            ],
            'body'=> json_encode([
                'model' => 'gpt-3.5-turbo',
                "max_tokens"=> 1000,
                'messages'=> [
                    [
                    'role'=>'user', 
                    'content'=> $body['prompt']
                    ]
                ]
            ]),
        ]);
        $tags = [];
       
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            return new WP_REST_Response(['status'=>0,'message'=>$error_message], 200 );
        } else {
            $data = wp_remote_retrieve_body($response);
            $data = json_decode($data,true);
            if(!empty($data['error'])){
               $message =  $data['error']['message'];
            }else{
                $message = __('Invalid chatgpt content genereated','vibebp');    
            }
            
            if(!empty($data['choices'])){
                return new WP_REST_Response(['status'=>1,'content'=>$data['choices'][0]['message']['content']]);
            }
                
        }
    }
}

VibeBP_API_Init::init();