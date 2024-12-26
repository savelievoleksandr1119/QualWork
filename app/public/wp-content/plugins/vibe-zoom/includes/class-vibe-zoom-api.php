<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require  VIBE_ZOOM_PATH.'/includes/vendor/autoload.php';

use \Firebase\JWT\JWT;

/**
 * Class Connecting Zoom APi V2
 *
 * @since   2.0
 * @author  Deepen
 * @modifiedn
 */
if ( ! class_exists( 'Vibe_Zoom_Video_Conferencing_Api' ) ) {

	class Vibe_Zoom_Video_Conferencing_Api {

		/**
		 * Zoom API KEY
		 *
		 * @var
		 */
		public $zoom_api_key;

		/**
		 * Zoom API Secret
		 *
		 * @var
		 */
		public $zoom_api_secret;
		public $account_id;
		/**
		 * Hold my instance
		 *
		 * @var
		 */
		protected static $_instance;

		/**
		 * API endpoint base
		 *
		 * @var string
		 */
		private $api_url = 'https://api.zoom.us/v2/';

		/**
		 * Create only one instance so that it may not Repeat
		 *
		 * @since 2.0.0
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Vibe_Zoom_Video_Conferencing_Api constructor.
		 *
		 * @param $zoom_api_key
		 * @param $zoom_api_secret
		 */
		public function __construct( $zoom_api_key = '', $zoom_api_secret = '', $account_id = ''  ) {
			if(empty($zoom_api_key) || empty($zoom_api_secret)){
				$option=get_option('vibe_zoom_settings');
				$this->zoom_api_key    = (!empty($option['vibe_zoom_api_key'])?$option['vibe_zoom_api_key']:false);
				$this->zoom_api_secret = (!empty($option['vibe_zoom_secret_key'])?$option['vibe_zoom_secret_key']:false);
				$this->account_id = (!empty($option['account_id'])?$option['account_id']:false);
			}else{
				$this->zoom_api_key    = $zoom_api_key;
				$this->zoom_api_secret = $zoom_api_secret;
				$this->account_id = $account_id;
			}
		}

		/**
		 * Send request to API
		 *
		 * @param $calledFunction
		 * @param $data
		 * @param string $request
		 *
		 * @return array|bool|string|WP_Error
		 */
		protected function sendRequest( $calledFunction, $data, $request = "GET" ) {

			$token = '';
			$request_url = $this->api_url . $calledFunction;
			if(!empty($this->account_id) || !empty($data['account_id'])){


				$account_id = $this->account_id;
				if(!empty($data['account_id'])){
					$account_id = $data['account_id'];
				}

				$token = get_transient('zoom_token_'.$account_id);
				if(empty($token)){
					if(!empty($data['api_key']) && !empty($data['api_secret'])){
						$client_jwt =  $this->generateJWTKey($data['api_key'],$data['api_secret']);
					}else{
						$client_jwt = $this->generateJWTKey();
					}

					
					$url = 'https://zoom.us/oauth/token?grant_type=account_credentials&account_id='.$account_id;
					$_response     = wp_remote_post( $url, array(
						'headers'=>array(
							'Authorization'=>'Bearer ' . $client_jwt,
						)
					) );
					
					$_response = wp_remote_retrieve_body( $_response );
					//print_r($_response);
					if(!empty($_response) && !is_wp_error($_response) ){
						$_response = json_decode($_response,true);
						if(!empty($_response['access_token'])){
							$token  = $_response['access_token'];
							set_transient('zoom_token_'.$account_id,$token,3500);//took threshold of 100 seconds meanwhile 
						}
						
					}
				}

			}else{
				if(!empty($data['api_key']) && !empty($data['api_secret'])){
					$token =  $this->generateJWTKey($data['api_key'],$data['api_secret']);
				}else{
					$token = $this->generateJWTKey();
				}
			}
			if(empty($token)){
				return false;
			}

			$args  = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json'
				)
			);
			

			if ( $request == "GET" ) {
				$args['body'] = ! empty( $data ) ? $data : array();
				$response     = wp_remote_get( $request_url, $args );
			} else if ( $request == "DELETE" ) {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "DELETE";
				$response       = wp_remote_request( $request_url, $args );
			} else if ( $request == "PATCH" ) {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "PATCH";
				$response       = wp_remote_request( $request_url, $args );
			} else {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "POST";
				 /*$filename = '/Applications/MAMP/htdocs/vibebp/wp-content/plugins/vibe-zoom/includes/file.txt';
                  if(!file_put_contents($filename,$request_url.'#############'. serialize($args))){
                   // overwriting the file failed (permission problem maybe), debug or log here
                  }*/
				$response       = wp_remote_post( $request_url, $args );

			}

			$response = wp_remote_retrieve_body( $response );
			/*dump($response);
			die;*/

			if ( ! $response ) {
				return false;
			}

			return $response;
		}

		//function to generate JWT
		private function generateJWTKey($key=null,$secret=null) {

			if(!empty($key) && !empty($secret)){

			}else{
				$key    = apply_filters('vibe_zoom_default_api_key',$this->zoom_api_key);
				$secret = apply_filters('vibe_zoom_default_api_secret',$this->zoom_api_secret);
			}
			

			$token = array(
				"iss" => $key,
				"exp" => time() + 3600 //60 seconds as suggested
			);

			return JWT::encode( $token, $secret );
		}

		/**
		 * Creates a User
		 *
		 * @param $postedData
		 *
		 * @return array|bool|string
		 */
		public function createAUser( $postedData = array() ) {
			$createAUserArray              = array();
			$createAUserArray['action']    = $postedData['action'];
			$createAUserArray['user_info'] = array(
				'email'      => $postedData['email'],
				'type'       => $postedData['type'],
				'first_name' => $postedData['first_name'],
				'last_name'  => $postedData['last_name']
			);

			return $this->sendRequest( 'users', $createAUserArray, "POST" );
		}

		/**
		 * User Function to List
		 *
		 * @param $page
		 *
		 * @return array
		 */
		public function listUsers( $page = 1 ) {
			$listUsersArray                = array();
			$listUsersArray['page_size']   = 300;
			$listUsersArray['page_number'] = absint( $page );

			return $this->sendRequest( 'users', $listUsersArray, "GET" );
		}

		/**
		 * Get A users info by user Id
		 *
		 * @param $user_id
		 *
		 * @return array|bool|string
		 */
		public function getUserInfo( $user_id ) {
			$getUserInfoArray = array();

			return $this->sendRequest( 'users/' . $user_id, $getUserInfoArray );
		}

		/**
		 * Delete a User
		 *
		 * @param $userid
		 *
		 * @return array|bool|string
		 */
		public function deleteAUser( $userid ) {
			$deleteAUserArray       = array();
			$deleteAUserArray['id'] = $userid;

			return $this->sendRequest( 'users/' . $userid, false, "DELETE" );
		}

		/**
		 * Get Meetings
		 *
		 * @param $host_id
		 *
		 * @return array
		 */
		public function listMeetings( $host_id ) {
			$listMeetingsArray              = array();
			$listMeetingsArray['page_size'] = 300;

			return $this->sendRequest( 'users/' . $host_id . '/meetings', $listMeetingsArray, "GET" );
		}

		/**
		 * Create A meeting API
		 *
		 * @param array $data
		 *
		 * @return array|bool|string|void|WP_Error
		 */
		public function createAMeeting( $data = array() ) {
			$post_time  = $data['start_date'];
			if(!is_numeric($post_time)){
				$start_time =  date( "Y-m-d\TH:i:s", strtotime( $post_time ) );
			}else{
				$start_time =  date( "Y-m-d\TH:i:s",  $post_time);
			}
			$createAMeetingArray = array();

			if ( ! empty( $data['alternative_host_ids'] ) ) {
				if ( count( $data['alternative_host_ids'] ) > 1 ) {
					$alternative_host_ids = implode( ",", $data['alternative_host_ids'] );
				} else {
					$alternative_host_ids = $data['alternative_host_ids'][0];
				}
			}

			$createAMeetingArray['topic']      = $data['meetingTopic'];
			$createAMeetingArray['agenda']     = ! empty( $data['agenda'] ) ? $data['agenda'] : "";
			$createAMeetingArray['type']       = ! empty( $data['type'] ) ? $data['type'] : 2; //Scheduled
			$createAMeetingArray['start_time'] = $start_time;
			$createAMeetingArray['timezone']   = $data['timezone'];
			$createAMeetingArray['password']   = ! empty( $data['password'] ) ? $data['password'] : "";
			$createAMeetingArray['duration']   = ! empty( $data['duration'] ) ? $data['duration'] : 60;
			$createAMeetingArray['settings']   = array(
				'join_before_host'  => ! empty( $data['join_before_host'] ) ? true : false,
				'host_video'        => ! empty( $data['option_host_video'] ) ? true : false,
				'participant_video' => ! empty( $data['option_participants_video'] ) ? true : false,
				'mute_upon_entry'   => ! empty( $data['option_mute_participants'] ) ? true : false,
				'authentication_option' => ! empty( $data['option_enforce_login'] ) ? true : false,
				'auto_recording'    => ! empty( $data['option_auto_recording'] ) ? $data['option_auto_recording'] : "none",
				'alternative_hosts' => isset( $alternative_host_ids ) ? $alternative_host_ids : ""
			);
			if(!empty($data['api_key']) && !empty($data['api_secret'])){
	          
	            $createAMeetingArray['api_key'] = $data['api_key'];
				$createAMeetingArray['api_secret'] = $data['api_secret'];
	        }
	        if(!empty($data['account_id'])){
	            $createAMeetingArray['account_id'] = $data['account_id'];
	        }

			if(!empty($data['recurrence']) && !empty($data['recurrence']['weekly_days'])){
				$createAMeetingArray['recurrence'] = $data['recurrence'];
			}
			$createAMeetingArray = apply_filters( 'vczapi_createAmeeting', $createAMeetingArray );
			if ( ! empty( $createAMeetingArray ) ) {

				

				$response =  $this->sendRequest( 'users/' . $data['userId'] . '/meetings', $createAMeetingArray, "POST" );
				  
				return $response;				
				
			} else {
				return;
			}
		}

		/**
		 * Updating Meeting Info
		 *
		 * @param array $update_data
		 *
		 * @return array|bool|string|void|WP_Error
		 */
		public function updateMeetingInfo( $update_data = array() ) {
			$post_time  = $update_data['start_date'];
			$start_time = gmdate( "Y-m-d\TH:i:s", strtotime( $post_time ) );

			$updateMeetingInfoArray = array();

			if ( ! empty( $update_data['alternative_host_ids'] ) ) {
				if ( count( $update_data['alternative_host_ids'] ) > 1 ) {
					$alternative_host_ids = implode( ",", $update_data['alternative_host_ids'] );
				} else {
					$alternative_host_ids = $update_data['alternative_host_ids'][0];
				}
			}

			$updateMeetingInfoArray['topic']      = ! empty( $update_data['topic'] ) ? $update_data['topic'] : "";
			$updateMeetingInfoArray['agenda']     = ! empty( $update_data['agenda'] ) ? $update_data['agenda'] : "";
			$updateMeetingInfoArray['type']       = ! empty( $update_data['type'] ) ? $update_data['type'] : 2; //Scheduled
			$updateMeetingInfoArray['start_time'] = $start_time;
			$updateMeetingInfoArray['timezone']   = $update_data['timezone'];
			$updateMeetingInfoArray['password']   = ! empty( $update_data['password'] ) ? $update_data['password'] : "";
			$updateMeetingInfoArray['duration']   = ! empty( $update_data['duration'] ) ? $update_data['duration'] : 60;
			$updateMeetingInfoArray['settings']   = array(
				'join_before_host'  => ! empty( $update_data['join_before_host'] ) ? true : false,
				'host_video'        => ! empty( $update_data['option_host_video'] ) ? true : false,
				'participant_video' => ! empty( $update_data['option_participants_video'] ) ? true : false,
				'mute_upon_entry'   => ! empty( $update_data['option_mute_participants'] ) ? true : false,
				'enforce_login'     => ! empty( $update_data['option_enforce_login'] ) ? true : false,
				'auto_recording'    => ! empty( $update_data['option_auto_recording'] ) ? $update_data['option_auto_recording'] : "none",
				'alternative_hosts' => isset( $alternative_host_ids ) ? $alternative_host_ids : ""
			);
			if(!empty($update_data['recurrence']) && !empty($update_data['recurrence']['weekly_days'])){
				$updateMeetingInfoArray['recurrence'] = $update_data['recurrence'];
			}
			$updateMeetingInfoArray = apply_filters( 'vczapi_updateMeetingInfo', $updateMeetingInfoArray );
			if ( ! empty( $updateMeetingInfoArray ) ) {
				$response =  $this->sendRequest( 'meetings/' . $update_data['meeting_id'], $updateMeetingInfoArray, "PATCH" );
				  return $response;
				
			} else {
				return;
			}
		}

		/**
		 * Get a Meeting Info
		 *
		 * @param  [INT] $id
		 * @param  [STRING] $host_id
		 *
		 * @return array
		 */
		public function getMeetingInfo( $id ) {
			$getMeetingInfoArray = array();

			return $this->sendRequest( 'meetings/' . $id, $getMeetingInfoArray, "GET" );
		}

		/**
		 * Delete A Meeting
		 *
		 * @param $meeting_id [int]
		 *
		 * @return array
		 */
		public function deleteAMeeting( $meeting_id ) {
			$deleteAMeetingArray = array();

			return $this->sendRequest( 'meetings/' . $meeting_id, $deleteAMeetingArray, "DELETE" );
		}

		/*Functions for management of reports*/
		/**
		 * Get daily account reports by month
		 *
		 * @param $month
		 * @param $year
		 *
		 * @return bool|mixed
		 */
		public function getDailyReport( $month, $year ) {
			$getDailyReportArray          = array();
			$getDailyReportArray['year']  = $year;
			$getDailyReportArray['month'] = $month;

			return $this->sendRequest( 'report/daily', $getDailyReportArray, "GET" );
		}

		/**
		 * Get ACcount Reports
		 *
		 * @param $zoom_account_from
		 * @param $zoom_account_to
		 *
		 * @return array
		 */
		public function getAccountReport( $zoom_account_from, $zoom_account_to ) {
			$getAccountReportArray              = array();
			$getAccountReportArray['from']      = $zoom_account_from;
			$getAccountReportArray['to']        = $zoom_account_to;
			$getAccountReportArray['page_size'] = 300;

			return $this->sendRequest( 'report/users', $getAccountReportArray, "GET" );
		}

		public function registerWebinarParticipants( $webinar_id, $first_name, $last_name, $email ) {
			$postData               = array();
			$postData['first_name'] = $first_name;
			$postData['last_name']  = $last_name;
			$postData['email']      = $email;

			return $this->sendRequest( 'webinars/' . $webinar_id . '/registrants', $postData, "POST" );
		}

		/**
		 * List webinars
		 *
		 * @param $userId
		 *
		 * @return bool|mixed
		 */
		public function listWebinar( $userId ) {
			$postData              = array();
			$postData['page_size'] = 300;

			return $this->sendRequest( 'users/' . $userId . '/webinars', $postData, "GET" );
		}

		/**
		 * List Webinar Participants
		 *
		 * @param $webinarId
		 *
		 * @return bool|mixed
		 */
		public function listWebinarParticipants( $webinarId ) {
			$postData              = array();
			$postData['page_size'] = 300;

			return $this->sendRequest( 'webinars/' . $webinarId . '/registrants', $postData, "GET" );
		}

		/**
		 * Get recording by meeting ID
		 *
		 * @param $meetingId
		 *
		 * @return bool|mixed
		 */
		public function recordingsByMeeting( $meetingId ) {
			return $this->sendRequest( 'meetings/' . $meetingId . '/recordings', false, "GET" );
		}

		/**
		 * Get all recordings by USER ID
		 *
		 * @param $host_id
		 * @param $data array
		 *
		 * @return bool|mixed
		 */
		public function listRecording( $host_id, $data = array() ) {
			$postData = array();
			$from     = date( 'Y-m-d', strtotime( '-1 year', time() ) );
			$to       = date( 'Y-m-d' );

			$postData['from'] = ! empty( $data['from'] ) ? $data['from'] : $from;
			$postData['to']   = ! empty( $data['to'] ) ? $data['to'] : $to;

			return $this->sendRequest( 'users/' . $host_id . '/recordings', $postData, "GET" );
		}
	}

	function vibe_zoom_api_init() {
		return Vibe_Zoom_Video_Conferencing_Api::instance();
	}

	vibe_zoom_api_init();
}
