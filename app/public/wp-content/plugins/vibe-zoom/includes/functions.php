<?php
/**
 * Initialise WPLMS Zoom
 *
 * @class       Wplms_Zoom_Actions
 * @author      VibeThemes
 * @category    Admin
 * @package     WPLMS-Zoom/classes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/*  
    Note: update_post_meta(post_id,'mutlihostkey',key)
    if(key) then fetch key-secret from get_option('multi_zoom_credential) and send obj
    else admin obj
    use:Object create
*/
function get_zoom_api_object($k){
    $vibe_zoom_api_init  = null;
    if($k){
        $multi_zoom_credential = get_option('multi_zoom_credential');
        if(!is_array($multi_zoom_credential)){
            $multi_zoom_credential = array();
        }
        foreach ($multi_zoom_credential as $key => $value) {
            if($value['key'] == $k){
                if( !empty($value['api_key'])  && !empty($value['api_secret'])){
                    if(!empty($value['account_id'])){
                        $vibe_zoom_api_init = new Vibe_Zoom_Video_Conferencing_Api($value['api_key'],$value['api_secret'],$value['account_id']);
                    }else{
                        $vibe_zoom_api_init = new Vibe_Zoom_Video_Conferencing_Api($value['api_key'],$value['api_secret']);
                    }
                }
                
                break;
            }
        }
    }else{
        $vibe_zoom_api_init = vibe_zoom_api_init();
    }
    return $vibe_zoom_api_init;
}

function get_vibe_zoom_recordings($post_id,$meeting_id){
    $k = get_post_meta($post_id,'multihostkey',true);
    $vibe_zoom_api_init = get_zoom_api_object($k);
    $recordings = $vibe_zoom_api_init->recordingsByMeeting($meeting_id);
    return $recordings;
}

function zoom_reminder_options(){
    return  apply_filters('zoom_reminder_options',array(
        '3600'=>__('1 Hour','vibe-zoom'),
        '7200'=>__('2 Hour','vibe-zoom'),
        '21600'=>__('6 Hour','vibe-zoom'),
        '43200'=>__('12 Hour','vibe-zoom'),
        '86400'=>__('1 Day','vibe-zoom')
    ));
}


function zoom_generate_signature ( $api_key, $api_secret, $meeting_number, $role){

	$time = time() * 1000 - 30000;//time in milliseconds (or close enough)
	
	$data = base64_encode($api_key . $meeting_number . $time . $role);
	
	$hash = hash_hmac('sha256', $data, $api_secret, true);
	
	$_sig = $api_key . "." . $meeting_number . "." . $time . "." . $role . "." . base64_encode($hash);
	
	//return signature, url safe base64 encoded
	return rtrim(strtr(base64_encode($_sig), '+/', '-_'), '=');
}


function get_zoom_meeting_key_secret($meeting_id){
    $key_secret = array();
    $k = get_post_meta($meeting_id,'multihostkey',true);
    if($k){
        $multi_zoom_credential = get_option('multi_zoom_credential');
        if(!is_array($multi_zoom_credential)){
            $multi_zoom_credential = array();
        }
        foreach ($multi_zoom_credential as $key => $value) {
            if($value['key'] == $k && ($value['api_key']  && $value['api_secret'])){
                $key_secret = array(
                    'api_key' => $value['api_key'],
                    'api_secret' => $value['api_secret']
                );
                break;
            }
        }
    }else{
        $option=get_option('vibe_zoom_settings');
        $key_secret = array(
            'api_key' => $option['vibe_zoom_api_key'],
            'api_secret' => $option['vibe_zoom_secret_key']
        );
    }
    return $key_secret;
}