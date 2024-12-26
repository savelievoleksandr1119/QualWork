<?php
/**
 * Envato Theme Setup Wizard Class
 *
 * Takes new users through some basic steps to setup their ThemeForest theme.

 *
 * Based off the WooThemes installer.
 *
 *
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



if ( ! class_exists( 'Envato_Theme_Setup_Wizard' ) ) {
	/**
	 * Envato_Theme_Setup_Wizard class
	 */
	class Envato_Theme_Setup_Wizard {

		/**
		 * The class version number.target
		 *
		 * @since 1.1.1
		 * @access private
		 *
		 * @var string
		 */
		protected $version = MICRONET_VERSION;

		/** @var string Current theme name, used as namespace in actions. */
		protected $theme_name = 'Micronet';

		/** @var string Theme author username, used in check for oauth. */
		protected $envato_username = 'vibethemes';

		protected $oauth_script = '';

		/** @var string Current Step */
		protected $step = '';

		/** @var array Steps for the setup wizard */
		protected $steps = array();

		/**
		 * Relative plugin path
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $plugin_path = '';

		/**
		 * Relative plugin url for this plugin folder, used when enquing scripts
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $plugin_url = '';

		/**
		 * The slug name to refer to this menu
		 *
		 * @since 1.1.1
		 *
		 * @var string
		 */
		protected $page_slug;

		/**
		 * TGMPA instance storage
		 *
		 * @var object
		 */
		protected $tgmpa_instance;

		/**
		 * TGMPA Menu slug
		 *
		 * @var string
		 */
		public $tgmpa_menu_slug = 'tgmpa-install-plugins';

		/**
		 * TGMPA Menu url
		 *
		 * @var string
		 */
		public $tgmpa_url = 'themes.php?page=tgmpa-install-plugins';

		/**
		 * The slug name for the parent menu
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $page_parent;

		/**
		 * Complete URL to Setup Wizard
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $page_url;

		/**
		 * @since 1.1.8
		 *
		 */
		public $site_styles = array();
		/**
		 * @since 1.1.8
		 *
		 */
		public $debug = 0;
		/**
		 * @since 1.1.8
		 *
		 */
		public $features = array();

		/**
		 * Holds the current instance of the theme manager
		 *
		 * @since 1.1.3
		 * @var Envato_Theme_Setup_Wizard
		 */
		private static $instance = null;

		/**
		 * @since 1.1.3
		 *
		 * @return Envato_Theme_Setup_Wizard
		 */
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * A dummy constructor to prevent this class from being loaded more than once.
		 *
		 * @see Envato_Theme_Setup_Wizard::instance()
		 *
		 * @since 1.1.1
		 * @access private
		 */
		public function __construct() {

			$this->init_globals();
			$this->init_actions();

		}

		/**
		 * Get the default style. Can be overriden by theme init scripts.
		 *
		 * @see Envato_Theme_Setup_Wizard::instance()
		 *
		 * @since 1.1.7
		 * @access public
		 */
		public function get_default_theme_style() {
			return 'demo';
		}

		

		/**
		 * Get the default style. Can be overriden by theme init scripts.
		 *
		 * @see Envato_Theme_Setup_Wizard::instance()
		 *
		 * @since 1.1.9
		 * @access public
		 */
		public function get_logo_image() {
			$image_url = '';
			return apply_filters( 'envato_setup_logo_image', get_template_directory_uri().'/images/logo.png' );
		}

		/**
		 * Setup the class globals.
		 *
		 * @since 1.1.1
		 * @access public
		 */
		public function init_globals() {

			$current_theme         = wp_get_theme();
			$this->theme_name      = 'micronet';
			$this->envato_username = apply_filters( $this->theme_name . '_theme_setup_wizard_username', 'vibethemes' );
			
			$this->page_slug       = apply_filters( $this->theme_name . '_theme_setup_wizard_page_slug', $this->theme_name . '-setup' );
			$this->parent_slug     = apply_filters( $this->theme_name . '_theme_setup_wizard_parent_slug', '' );

			$this->features = array(
								'bp'=>array(
	                    						'label'=>__('[ RECOMMENDED ] Vibe App','micronet'),
	                    						'icon'=>'<svg id="Capa_1" height="80" viewBox="0 0 512 512" width="80" xmlns="http://www.w3.org/2000/svg"><g><path d="m487.159 47.969h-366.804c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h366.803c5.426 0 9.841 4.415 9.841 9.841v22.985h-481.999v-22.984c0-5.427 4.414-9.841 9.841-9.841h65.515c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5h-65.515c-13.697-.001-24.841 11.143-24.841 24.841v366.379c0 13.698 11.144 24.841 24.841 24.841h462.318c13.698 0 24.841-11.143 24.841-24.841v-143.677c0-4.142-3.358-7.5-7.5-7.5s-7.5 3.358-7.5 7.5v143.677c0 5.427-4.415 9.841-9.841 9.841h-54.469v-15.591h9.482c8.326 0 15.099-6.773 15.099-15.099v-23.419c0-8.325-6.773-15.099-15.099-15.099h-9.482v-57.823h36.714c8.325 0 15.099-6.773 15.099-15.099v-23.419c0-8.325-6.773-15.099-15.099-15.099h-36.714v-23.616h36.487c8.325 0 15.099-6.773 15.099-15.099v-23.418c0-8.326-6.773-15.099-15.099-15.099h-36.487v-80.355h64.31v154.717c0 4.142 3.358 7.5 7.5 7.5s7.5-3.358 7.5-7.5v-192.702c0-13.698-11.144-24.842-24.841-24.842zm-218.083 220.414v-85.808h32.614c8.325 0 15.099-6.773 15.099-15.099v-23.418c0-8.325-6.773-15.099-15.099-15.099h-32.614v-18.164h66.807v157.588zm-38.323 53.617h23.323v27.549h-66.806v-166.974h66.807v85.808h-23.323c-8.326 0-15.099 6.773-15.099 15.099v23.419c-.001 8.325 6.772 15.099 15.098 15.099zm100.415 57.824c-8.325 0-15.099 6.773-15.099 15.099v23.419c0 8.325 6.773 15.099 15.099 15.099h4.716v15.591h-66.807v-45.866h.702c8.326 0 15.099-6.773 15.099-15.099v-23.418c0-8.325-6.773-15.099-15.099-15.099h-.702v-27.55h66.807v57.824zm-172.395-212.249c-.055 0-.099-.044-.099-.099v-23.418c0-.054.044-.099.099-.099h142.917c.054 0 .099.045.099.099v23.418c0 .054-.044.099-.099.099zm28.497-38.616v-18.164h66.807v18.164zm-15 0h-13.496c-8.326 0-15.099 6.773-15.099 15.099v23.418c0 8.325 6.773 15.099 15.099 15.099h13.496v166.973h-13.496c-8.326 0-15.099 6.773-15.099 15.099v23.418c0 8.326 6.773 15.099 15.099 15.099h13.496v45.866h-56.52v-338.235h56.52zm97.508 235.589c.055 0 .099.044.099.099v23.418c0 .055-.044.099-.099.099h-111.005c-.055 0-.099-.044-.099-.099v-23.418c0-.054.044-.099.099-.099zm-82.508 38.617h66.807v45.866h-66.807zm-172.27 36.024v-328.394h85.75v338.235h-75.909c-5.427.001-9.841-4.414-9.841-9.841zm402.69 9.842h-66.807v-15.591h66.807zm24.582-54.109v23.419c0 .054-.044.099-.099.099h-111.005c-.054 0-.099-.044-.099-.099v-23.419c0-.054.044-.099.099-.099h111.005c.055.001.099.045.099.099zm-24.582-15.098h-66.807v-57.824h66.807zm51.813-96.342v23.419c0 .054-.044.099-.099.099h-238.651c-.055 0-.099-.044-.099-.099v-23.419c0-.054.044-.099.099-.099h238.651c.054 0 .099.045.099.099zm-.227-77.232v23.418c0 .054-.044.099-.099.099h-72.711c-.054 0-.099-.045-.099-.099v-23.418c0-.055.044-.099.099-.099h72.711c.055 0 .099.044.099.099zm-51.586-15.099h-21.224c-8.325 0-15.099 6.773-15.099 15.099v23.418c0 8.325 6.773 15.099 15.099 15.099h21.224v23.616h-66.807v-157.588h66.807z"/><path d="m36.252 167.781h43.245c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5h-43.245c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5z"/><path d="m79.498 211.74h-43.246c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h43.245c4.142 0 7.5-3.358 7.5-7.5.001-4.142-3.357-7.5-7.499-7.5z"/><path d="m79.498 270.7h-43.246c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h43.245c4.142 0 7.5-3.358 7.5-7.5.001-4.142-3.357-7.5-7.499-7.5z"/><path d="m79.498 329.659h-43.246c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h43.245c4.142 0 7.5-3.358 7.5-7.5.001-4.142-3.357-7.5-7.499-7.5z"/><path d="m79.498 388.618h-43.246c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h43.245c4.142 0 7.5-3.358 7.5-7.5.001-4.142-3.357-7.5-7.499-7.5z"/></g></svg>',
	                    						'description'=> 'Calendar Booking Appointments for micronet. ',
	                    						'link'=>'https://micronet.io/downloads/vibe-appointments/',
	                    						'verify'=>array('vibe-appointments/loader.php'),
	                    						'default'=>1
                    						),
                    			'projects'=>array(
	                    						'label'=>__('[ RECOMMENDED ] Project Management','micronet'),
	                    						'icon'=>'<svg id="Capa_1" height="80" viewBox="0 0 512 512" width="80" xmlns="http://www.w3.org/2000/svg"><g><path d="m487.159 47.969h-366.804c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h366.803c5.426 0 9.841 4.415 9.841 9.841v22.985h-481.999v-22.984c0-5.427 4.414-9.841 9.841-9.841h65.515c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5h-65.515c-13.697-.001-24.841 11.143-24.841 24.841v366.379c0 13.698 11.144 24.841 24.841 24.841h462.318c13.698 0 24.841-11.143 24.841-24.841v-143.677c0-4.142-3.358-7.5-7.5-7.5s-7.5 3.358-7.5 7.5v143.677c0 5.427-4.415 9.841-9.841 9.841h-54.469v-15.591h9.482c8.326 0 15.099-6.773 15.099-15.099v-23.419c0-8.325-6.773-15.099-15.099-15.099h-9.482v-57.823h36.714c8.325 0 15.099-6.773 15.099-15.099v-23.419c0-8.325-6.773-15.099-15.099-15.099h-36.714v-23.616h36.487c8.325 0 15.099-6.773 15.099-15.099v-23.418c0-8.326-6.773-15.099-15.099-15.099h-36.487v-80.355h64.31v154.717c0 4.142 3.358 7.5 7.5 7.5s7.5-3.358 7.5-7.5v-192.702c0-13.698-11.144-24.842-24.841-24.842zm-218.083 220.414v-85.808h32.614c8.325 0 15.099-6.773 15.099-15.099v-23.418c0-8.325-6.773-15.099-15.099-15.099h-32.614v-18.164h66.807v157.588zm-38.323 53.617h23.323v27.549h-66.806v-166.974h66.807v85.808h-23.323c-8.326 0-15.099 6.773-15.099 15.099v23.419c-.001 8.325 6.772 15.099 15.098 15.099zm100.415 57.824c-8.325 0-15.099 6.773-15.099 15.099v23.419c0 8.325 6.773 15.099 15.099 15.099h4.716v15.591h-66.807v-45.866h.702c8.326 0 15.099-6.773 15.099-15.099v-23.418c0-8.325-6.773-15.099-15.099-15.099h-.702v-27.55h66.807v57.824zm-172.395-212.249c-.055 0-.099-.044-.099-.099v-23.418c0-.054.044-.099.099-.099h142.917c.054 0 .099.045.099.099v23.418c0 .054-.044.099-.099.099zm28.497-38.616v-18.164h66.807v18.164zm-15 0h-13.496c-8.326 0-15.099 6.773-15.099 15.099v23.418c0 8.325 6.773 15.099 15.099 15.099h13.496v166.973h-13.496c-8.326 0-15.099 6.773-15.099 15.099v23.418c0 8.326 6.773 15.099 15.099 15.099h13.496v45.866h-56.52v-338.235h56.52zm97.508 235.589c.055 0 .099.044.099.099v23.418c0 .055-.044.099-.099.099h-111.005c-.055 0-.099-.044-.099-.099v-23.418c0-.054.044-.099.099-.099zm-82.508 38.617h66.807v45.866h-66.807zm-172.27 36.024v-328.394h85.75v338.235h-75.909c-5.427.001-9.841-4.414-9.841-9.841zm402.69 9.842h-66.807v-15.591h66.807zm24.582-54.109v23.419c0 .054-.044.099-.099.099h-111.005c-.054 0-.099-.044-.099-.099v-23.419c0-.054.044-.099.099-.099h111.005c.055.001.099.045.099.099zm-24.582-15.098h-66.807v-57.824h66.807zm51.813-96.342v23.419c0 .054-.044.099-.099.099h-238.651c-.055 0-.099-.044-.099-.099v-23.419c0-.054.044-.099.099-.099h238.651c.054 0 .099.045.099.099zm-.227-77.232v23.418c0 .054-.044.099-.099.099h-72.711c-.054 0-.099-.045-.099-.099v-23.418c0-.055.044-.099.099-.099h72.711c.055 0 .099.044.099.099zm-51.586-15.099h-21.224c-8.325 0-15.099 6.773-15.099 15.099v23.418c0 8.325 6.773 15.099 15.099 15.099h21.224v23.616h-66.807v-157.588h66.807z"/><path d="m36.252 167.781h43.245c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5h-43.245c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5z"/><path d="m79.498 211.74h-43.246c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h43.245c4.142 0 7.5-3.358 7.5-7.5.001-4.142-3.357-7.5-7.499-7.5z"/><path d="m79.498 270.7h-43.246c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h43.245c4.142 0 7.5-3.358 7.5-7.5.001-4.142-3.357-7.5-7.499-7.5z"/><path d="m79.498 329.659h-43.246c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h43.245c4.142 0 7.5-3.358 7.5-7.5.001-4.142-3.357-7.5-7.499-7.5z"/><path d="m79.498 388.618h-43.246c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h43.245c4.142 0 7.5-3.358 7.5-7.5.001-4.142-3.357-7.5-7.499-7.5z"/></g></svg>',
	                    						'description'=> 'Project Management, Task management. ',
	                    						'link'=>'https://wpappointify..com/',
	                    						'verify'=>array('vibe-appointments/loader.php'),
	                    						'default'=>1
                    						),
                    			'helpdesk'=>array(
	                    						'label'=>__('Vibe Helpdesk','micronet'),
	                    						'icon'=>'<svg id="Capa_1" height="80" viewBox="0 0 512 512" width="80" xmlns="http://www.w3.org/2000/svg"><g><path d="m194.472 373.491c12.407 0 22.5-10.094 22.5-22.5s-10.093-22.5-22.5-22.5-22.5 10.094-22.5 22.5 10.093 22.5 22.5 22.5zm0-30c4.136 0 7.5 3.365 7.5 7.5s-3.364 7.5-7.5 7.5-7.5-3.365-7.5-7.5 3.364-7.5 7.5-7.5z"/><path d="m256 373.491c12.407 0 22.5-10.094 22.5-22.5s-10.093-22.5-22.5-22.5-22.5 10.094-22.5 22.5 10.093 22.5 22.5 22.5zm0-30c4.136 0 7.5 3.365 7.5 7.5s-3.364 7.5-7.5 7.5-7.5-3.365-7.5-7.5 3.364-7.5 7.5-7.5z"/><path d="m317.528 373.491c12.407 0 22.5-10.094 22.5-22.5s-10.093-22.5-22.5-22.5-22.5 10.094-22.5 22.5 10.093 22.5 22.5 22.5zm0-30c4.136 0 7.5 3.365 7.5 7.5s-3.364 7.5-7.5 7.5-7.5-3.365-7.5-7.5 3.364-7.5 7.5-7.5z"/><path d="m268.592 183.356h-20.319c-8.247 0-14.956 6.709-14.956 14.955v11.802c0 8.246 6.709 14.955 14.956 14.955h20.08 7.694 17.15c18.959 0 34.383-15.424 34.383-34.383v-1.311h3.475c10.076 0 18.273-8.197 18.273-18.273v-29.929c0-10.076-8.197-18.273-18.273-18.273h-17.389c-.69-31.221-26.284-56.411-57.667-56.411-31.384 0-56.977 25.19-57.667 56.411h-17.389c-10.076 0-18.273 8.197-18.273 18.273v29.929c0 10.076 8.197 18.273 18.273 18.273h24.857c4.142 0 7.5-3.358 7.5-7.5v-17.986-33.489-6.211c0-23.545 19.155-42.7 42.7-42.7s42.7 19.155 42.7 42.7v6.211 34.738 16.737c0 4.142 3.358 7.5 7.5 7.5h6.381v1.311c0 10.688-8.695 19.383-19.383 19.383h-9.65v-11.757c-.001-8.246-6.71-14.955-14.956-14.955zm-70.292-8.982h-17.356c-1.805 0-3.273-1.468-3.273-3.273v-29.929c0-1.805 1.468-3.273 3.273-3.273h17.356v25.989zm136.029-33.202v29.929c0 1.805-1.468 3.273-3.273 3.273h-17.356v-9.237-27.238h17.356c1.805 0 3.273 1.469 3.273 3.273zm-86.056 57.184 20.274-.045v11.757h-.194c-.086 0-.17.01-.256.013l-19.78.032z"/><path d="m500.483 398.304h-19.185v-270.302c0-14.185-11.54-25.725-25.725-25.725h-67.881c-1.942-4.442-4.164-8.77-6.677-12.947-2.135-3.549-6.743-4.696-10.293-2.56-3.549 2.135-4.695 6.744-2.56 10.293 9.171 15.242 14.018 32.756 14.018 50.648v5.594c0 54.256-44.141 98.396-98.397 98.396-4.972 0-9.605 2.675-12.091 6.981l-15.692 27.185-15.694-27.184c-2.486-4.306-7.119-6.981-12.091-6.981-54.256 0-98.396-44.14-98.396-98.396v-5.594c0-54.256 44.141-98.397 98.396-98.397h55.572c25.793 0 50.171 9.908 68.643 27.899 2.967 2.889 7.715 2.827 10.606-.14 2.89-2.967 2.827-7.716-.14-10.606-21.29-20.734-49.385-32.153-79.109-32.153h-55.572c-46.378 0-86.335 27.991-103.885 67.962h-67.905c-14.185 0-25.725 11.54-25.725 25.725v270.303h-19.184c-6.35-.001-11.516 5.166-11.516 11.516v28.606c0 21.647 17.611 39.259 39.258 39.259h57.956c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5h-57.956c-13.376-.001-24.258-10.883-24.258-24.259v-25.123h131.742l6.274 19.882c2.229 7.063 8.703 11.808 16.109 11.808h173.75c7.406 0 13.88-4.745 16.109-11.809l6.274-19.882h131.742v30.123c0 14.199-12.531 19.259-24.259 19.259h-345.527c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h345.527c23.482 0 39.259-13.767 39.259-34.259v-33.606c0-6.349-5.166-11.516-11.517-11.516zm-44.909-281.027c5.914 0 10.725 4.811 10.725 10.725v270.303h-20v-241.812c0-10.596-8.62-19.216-19.215-19.216h-30.394c-.624-6.781-1.842-13.475-3.655-20zm-227.96 149.424 18.319 31.73c2.07 3.585 5.927 5.812 10.067 5.812 4.139 0 7.997-2.227 10.066-5.812l18.319-31.73c62.254-.322 112.796-51.066 112.796-113.395v-1.029h29.901c2.324 0 4.215 1.891 4.215 4.216v241.812h-350.596v-241.812c0-2.325 1.891-4.216 4.215-4.216h29.901v1.029c0 62.328 50.542 113.073 112.797 113.395zm-181.913-138.699c0-5.914 4.811-10.725 10.725-10.725h62.545c-1.8 6.455-3.04 13.14-3.669 20h-30.386c-10.595 0-19.215 8.62-19.215 19.216v241.812h-20zm298.978 300.67c-.25.791-.975 1.323-1.804 1.323h-173.75c-.83 0-1.555-.532-1.804-1.322l-4.85-15.368h187.058z"/></g></svg>',
	                    						'description'=> __('Discussion Forums with BBPress. Course specific forums.','micronet'),
	                    						'verify'=>array('bbpress/bbpress.php','vibe-helpdesk/loader.php'),
	                    						'default'=>1,
                    						),
								'drive'=>array(
	                    						'label'=>__('Drive','micronet'),
	                    						'icon'=>'<svg version="1.1" id="Upload_and_Download" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M59,14h-3v-4c0-0.552-0.448-1-1-1h-6v2h5v3h-5v2h6h3v42H2V2h24v8v4H8v2h19h5v-2h-4v-3h4V9h-4V1c0-0.552-0.448-1-1-1H1C0.448,0,0,0.448,0,1v58c0,0.552,0.448,1,1,1h58c0.552,0,1-0.448,1-1V15C60,14.448,59.552,14,59,14z"/><path style="fill:#231F20;" d="M35,3.414V42h2V3.414l1.293,1.293l1.414-1.414l-3-3c-0.391-0.391-1.023-0.391-1.414,0l-3,3l1.414,1.414L35,3.414z"/><path style="fill:#231F20;" d="M45,42c0.256,0,0.512-0.098,0.707-0.293l3-3l-1.414-1.414L46,38.586V0h-2v38.586l-1.293-1.293l-1.414,1.414l3,3C44.488,41.902,44.744,42,45,42z"/><rect x="8" y="52" style="fill:#231F20;" width="4" height="2"/><rect x="16" y="52" style="fill:#231F20;" width="4" height="2"/><rect x="24" y="52" style="fill:#231F20;" width="4" height="2"/><rect x="32" y="52" style="fill:#231F20;" width="4" height="2"/><rect x="40" y="52" style="fill:#231F20;" width="4" height="2"/><rect x="48" y="52" style="fill:#231F20;" width="4" height="2"/><rect x="8" y="20" style="fill:#231F20;" width="4" height="2"/><rect x="16" y="20" style="fill:#231F20;" width="4" height="2"/><rect x="24" y="20" style="fill:#231F20;" width="4" height="2"/><rect x="48" y="20" style="fill:#231F20;" width="4" height="2"/></g></svg>',
	                    						'description'=> __('Upload and share attachments via drive with restricted access.','micronet'),
	                    						'verify'=>array('vibedrive/vibedrive.php'),
                    						),
	                    		'kb'=>array(
	                    						'label'=>__('Knowledge Base','micronet'),
	                    						'default'=>1,
	                    						'icon'=>'<svg version="1.1" id="Content_Sharing" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M42,58H2V37h41.586l-1.293,1.293l1.414,1.414l3-3c0.391-0.391,0.391-1.023,0-1.414l-3-3l-1.414,1.414L43.586,35H2V2h30v9c0,0.552,0.448,1,1,1h9v4h2v-5c0-0.022-0.011-0.041-0.013-0.063c-0.005-0.088-0.022-0.173-0.051-0.257c-0.011-0.032-0.02-0.063-0.034-0.094c-0.049-0.106-0.11-0.207-0.196-0.293l-10-10c-0.086-0.086-0.188-0.148-0.294-0.197c-0.029-0.013-0.059-0.021-0.089-0.032c-0.086-0.03-0.173-0.047-0.264-0.053C33.039,0.011,33.021,0,33,0H1C0.448,0,0,0.448,0,1v58c0,0.552,0.448,1,1,1h42c0.552,0,1-0.448,1-1v-5h-2V58z M40.586,10H34V3.414L40.586,10z"/><path style="fill:#231F20;" d="M59.987,28.937c-0.005-0.088-0.022-0.173-0.051-0.257c-0.011-0.032-0.02-0.063-0.034-0.094c-0.049-0.106-0.11-0.207-0.196-0.293l-8-8c-0.086-0.086-0.187-0.147-0.293-0.196c-0.031-0.014-0.062-0.023-0.094-0.034c-0.084-0.028-0.169-0.045-0.257-0.051C51.041,20.011,51.021,20,51,20H37c-0.552,0-1,0.448-1,1v11h2V22h12v7c0,0.552,0.448,1,1,1h7v18H38v-8h-2v9c0,0.552,0.448,1,1,1h22c0.552,0,1-0.448,1-1V29C60,28.978,59.989,28.959,59.987,28.937z M52,28v-4.586L56.586,28H52z"/><rect x="6" y="6" style="fill:#231F20;" width="6" height="2"/><rect x="6" y="11" style="fill:#231F20;" width="17" height="2"/><rect x="6" y="20" style="fill:#231F20;" width="18" height="2"/><rect x="6" y="26" style="fill:#231F20;" width="26" height="2"/><rect x="6" y="43" style="fill:#231F20;" width="26" height="2"/><rect x="6" y="49" style="fill:#231F20;" width="26" height="2"/></g></svg>',
	                    						'description'=> __('Upload and share attachments via drive with restricted access.','micronet'),
	                    						'verify'=>array('vibe-kb/loader.php'),
                    						),
	                    		'elementor'=>array(
	                    						'label'=>__('Elementor','micronet'),
	                    						'icon'=>'<svg version="1.1" id="Web_login" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><g><path style="fill:#231F20;" d="M58,0H2C0.897,0,0,0.897,0,2v56c0,1.103,0.897,2,2,2h56c1.103,0,2-0.897,2-2V2C60,0.897,59.103,0,58,0z M2,58V2h56l0.001,56H2z"/><path style="fill:#231F20;" d="M53,6H7C6.448,6,6,6.448,6,7v12c0,0.552,0.448,1,1,1h46c0.552,0,1-0.448,1-1V7C54,6.448,53.552,6,53,6z M52,18H8V8h44V18z"/><path style="fill:#231F20;" d="M53,24H7c-0.552,0-1,0.448-1,1v12c0,0.552,0.448,1,1,1h46c0.552,0,1-0.448,1-1V25C54,24.448,53.552,24,53,24z M52,36H8V26h44V36z"/><path style="fill:#231F20;" d="M27,42H8c-0.552,0-1,0.448-1,1v10c0,0.552,0.448,1,1,1h19c0.552,0,1-0.448,1-1V43C28,42.448,27.552,42,27,42z M26,52H9v-8h17V52z"/><path style="fill:#231F20;" d="M52,42H33c-0.552,0-1,0.448-1,1v10c0,0.552,0.448,1,1,1h19c0.552,0,1-0.448,1-1V43C53,42.448,52.552,42,52,42z M51,52H34v-8h17V52z"/><rect x="38" y="47" style="fill:#231F20;" width="9" height="2"/><rect x="13" y="47" style="fill:#231F20;" width="9" height="2"/><rect x="12" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="21" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="30" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="39" y="12" style="fill:#231F20;" width="5" height="2"/><rect x="12" y="30" style="fill:#231F20;" width="5" height="2"/><rect x="21" y="30" style="fill:#231F20;" width="5" height="2"/><rect x="30" y="30" style="fill:#231F20;" width="5" height="2"/><rect x="39" y="30" style="fill:#231F20;" width="5" height="2"/></g></svg>',
	                    						'description'=> __('[ FREE ] Best modern page builder for WordPress.','micronet'),
	                    						'verify'=>array('elementor/elementor.php')
                    						),
	                    		
	                    		'zoom'=>array(
	                    						'label'=>__('Zoom Conferencing','micronet'),
	                    						'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" height="60" width="90" viewBox="-12.7143 -4.762 110.1906 28.572"><path fill-rule="evenodd" fill="#231F20" d="M69.012 5.712c.324.559.43 1.195.465 1.91l.046.953v6.664l.047.954c.094 1.558 1.243 2.71 2.813 2.808l.949.047V8.575l.047-.953c.039-.707.144-1.355.473-1.918a3.806 3.806 0 016.59.012c.324.559.425 1.207.464 1.906l.047.95v6.667l.047.954c.098 1.566 1.238 2.718 2.813 2.808l.949.047V7.622a7.62 7.62 0 00-7.617-7.62 7.6 7.6 0 00-5.715 2.581A7.61 7.61 0 0065.715.001c-1.582 0-3.05.48-4.266 1.309C60.707.482 59.047.001 58.094.001v19.047l.953-.047c1.594-.105 2.746-1.226 2.808-2.808l.051-.954V8.575l.047-.953c.04-.719.14-1.351.465-1.914a3.816 3.816 0 013.297-1.898 3.81 3.81 0 013.297 1.902zM3.809 19.002l.953.046h14.285l-.047-.95c-.129-1.566-1.238-2.71-2.809-2.812l-.953-.047h-8.57l11.426-11.43-.047-.949c-.074-1.582-1.23-2.725-2.809-2.812l-.953-.043L0 .001l.047.953c.125 1.551 1.25 2.719 2.808 2.809l.954.047h8.57L.953 15.24l.047.953c.094 1.57 1.227 2.707 2.809 2.808zM54.355 2.789a9.523 9.523 0 010 13.469 9.53 9.53 0 01-13.472 0c-3.719-3.719-3.719-9.75 0-13.469A9.518 9.518 0 0147.613 0a9.525 9.525 0 016.742 2.79zM51.66 5.486a5.717 5.717 0 010 8.082 5.717 5.717 0 01-8.082 0 5.717 5.717 0 010-8.082 5.717 5.717 0 018.082 0zM27.625 0a9.518 9.518 0 016.73 2.79c3.72 3.718 3.72 9.75 0 13.468a9.53 9.53 0 01-13.472 0c-3.719-3.719-3.719-9.75 0-13.469A9.518 9.518 0 0127.613 0zm4.035 5.484a5.717 5.717 0 010 8.083 5.717 5.717 0 01-8.082 0 5.717 5.717 0 010-8.082 5.717 5.717 0 018.082 0z"/></svg>',
	                    						'description'=> __('Enable Video conferencing with Zoom.','micronet'),
	                    						'link'=>'https://www.youtube.com/watch?v=UPCNJwAG2JI&t=8s',
	                    						'verify'=>array('vibe-zoom/vibe-zoom.php')
                    						),
	                    		
	                    	);

			// create an images/styleX/ folder for each style here.
			$this->site_styles = array(
				'demo' => array(
					'label'=>'Demo',
					'installation_type'=>['gutenberg'],
					'src' => VIBE_URL.'/setup/installer/images/demo.png',
					'link'=>'https://micronet.work/demo/',
					'plugins'=>array('vibebp','vibe-projects','buddypress')
				)
           );

			//If we have parent slug - set correct url
			if ( $this->parent_slug !== '' ) {
				$this->page_url = 'admin.php?page=' . $this->page_slug;
			} else {
				$this->page_url = 'themes.php?page=' . $this->page_slug;
			}
			$this->page_url = apply_filters( $this->theme_name . '_theme_setup_wizard_page_url', $this->page_url );

			//set relative plugin path url
			$this->plugin_path = trailingslashit( $this->cleanFilePath( dirname( __FILE__ ) ) );
			$relative_url      = str_replace( $this->cleanFilePath( get_template_directory() ), '', $this->plugin_path );
			$this->plugin_url  = trailingslashit( get_template_directory_uri() . $relative_url );
		}

		/**
		 * Setup the hooks, actions and filters.
		 *
		 * @uses add_action() To add actions.
		 * @uses add_filter() To add filters.
		 *
		 * @since 1.1.1
		 * @access public
		 */
		public function init_actions() {

			if ( apply_filters( $this->theme_name . '_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {

				add_action( 'after_switch_theme', array( $this, 'switch_theme' ) );

				if ( class_exists( 'TGM_Plugin_Activation' ) && isset( $GLOBALS['tgmpa'] ) ) {
					add_action( 'init', array( $this, 'get_tgmpa_instanse' ), 30 );
					add_action( 'init', array( $this, 'set_tgmpa_url' ), 40 );
				}

				add_action( 'admin_menu', array( $this, 'admin_menus' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

				add_action( 'admin_init', array( $this, 'admin_redirects' ), 30 );
				add_action( 'admin_init', array( $this, 'init_wizard_steps' ), 30 );
				add_action( 'admin_init', array( $this, 'setup_wizard' ), 30 );
				add_filter( 'tgmpa_load', array( $this, 'tgmpa_load' ), 10, 1 );
				add_action( 'wp_ajax_envato_setup_plugins', array( $this, 'ajax_plugins' ) );
				add_action( 'wp_ajax_envato_setup_content', array( $this, 'ajax_content' ) );

				//add_action('wp_ajax_save_item_purchase_code',array($this,'save_item_purchase_code'));
				add_filter('micronet_required_plugins',array($this,'setup_wizard_plugins'));

				add_filter('micronet_import_post_type_content',array($this,'check_post_type'),10,2);
				add_filter('micronet_import_post_type_content_disable',array($this,'check_post_type'),10,2);

				add_action('wp_ajax_clear_imported_posts',array($this,'clear_imported_posts'));
			}
			if ( function_exists( 'envato_market' ) ) {
				add_action( 'admin_init', array( $this, 'envato_market_admin_init' ), 20 );
				add_filter( 'http_request_args', array( $this, 'envato_market_http_request_args' ), 10, 2 );
			}
			add_action('widgets_init',array($this,'micronet_register_sidebars'));
			add_action( 'upgrader_post_install', array( $this, 'upgrader_post_install' ), 10, 2 );
			add_filter('woocommerce_enable_setup_wizard',function($x){return false;});
		}


		function clear_imported_posts(){
			
			if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'micronet_clear_imported_posts') || !current_user_can('manage_options')){
	         	_e('Security check Failed. Contact Administrator.','micronet');
	         	die();
	      	}
	      	delete_transient( 'importpostids');
	      	delete_transient( 'importtermids');
	      	die();
		}



		function micronet_register_sidebars(){

			//anual adjustments
		}

		function check_post_type($check,$post_type){
			
			if(empty($this->check_micronet_plugins)){
				$this->check_micronet_plugins = get_option('micronet_plugins');	

			}
			
			if(is_array($this->check_micronet_plugins)){
				
				if(!in_array('woocommerce/woocommerce.php',$this->check_micronet_plugins)){
					if(in_array($post_type,array('product'))){
						$check = 0;
					}
				}

				
			}

			return $check;	
		}
		function setup_wizard_plugins($plugins){

			// SETUP WIZARD PLUGINS
			$micronet_plugins = get_option( 'micronet_plugins');
			if(isset($micronet_plugins) && is_array($micronet_plugins)){
				
	        	$plugins[] = array(
	            'name'                  => 'BigBlueButton',
	            'slug'                  => 'bigbluebutton', 
	            'file'					=> 'bigbluebutton/bigbluebutton.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'Vibe BigBluebutton',
	            'slug'                  => 'vibe-bbb', 
	            'file'					=> 'vibe-bbb/vibe-bbb.php',
	        	);
	        	$plugins[] = array(
	            'name'                  => 'Vibe Zoom',
	            'slug'                  => 'vibe-bbb', 
	            'file'					=> 'vibe-zoom/vibe-zoom.php',
	        	);

	        	$plugins[] = array(
	            'name'                  => 'Vibe Earnings',
	            'slug'                  => 'vibe-earnings', 
	            'file'					=> 'vibe-earnings/loader.php',
	        	);
	        	
				foreach($plugins as $k=>$plugin){
					if(empty($plugin['required']) && isset($plugin['file']) && !in_array($plugin['file'],$micronet_plugins)){
						unset($plugins[$k]);
					}
				}
			}

			return $plugins;
		}
		/**
		 * After a theme update we clear the setup_complete option. This prompts the user to visit the update page again.
		 *
		 * @since 1.1.8
		 * @access public
		 */
		public function upgrader_post_install( $return, $theme ) {
			if ( is_wp_error( $return ) ) {
				return $return;
			}
			if ( $theme != get_stylesheet() ) {
				return $return;
			}
			update_option( 'envato_setup_complete', false );


			return $return;
		}

		/**
		 * We determine if the user already has theme content installed. This can happen if swapping from a previous theme or updated the current theme. We change the UI a bit when updating / swapping to a new theme.
		 *
		 * @since 1.1.8
		 * @access public
		 */
		public function is_possible_upgrade() {
			return false;
		}

		public function enqueue_scripts() {
		}

		public function tgmpa_load( $status ) {
			return is_admin() || current_user_can( 'install_themes' );
		}

		public function switch_theme() {
			set_transient( '_' . $this->theme_name . '_activation_redirect', 1 );
		}

		public function admin_redirects() {

			ob_start();
			if ( ! get_transient( '_' . $this->theme_name . '_activation_redirect' ) || get_option( 'envato_setup_complete', false ) ) {
				return;
			}
			delete_transient( '_' . $this->theme_name . '_activation_redirect' );
			wp_safe_redirect( admin_url( $this->page_url ) );
			exit;
		}

		/**
		 * Get configured TGMPA instance
		 *
		 * @access public
		 * @since 1.1.2
		 */
		public function get_tgmpa_instanse() {
			$this->tgmpa_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
		}

		/**
		 * Update $tgmpa_menu_slug and $tgmpa_parent_slug from TGMPA instance
		 *
		 * @access public
		 * @since 1.1.2
		 */
		public function set_tgmpa_url() {

			$this->tgmpa_menu_slug = ( property_exists( $this->tgmpa_instance, 'menu' ) ) ? $this->tgmpa_instance->menu : $this->tgmpa_menu_slug;
			$this->tgmpa_menu_slug = apply_filters( $this->theme_name . '_theme_setup_wizard_tgmpa_menu_slug', $this->tgmpa_menu_slug );

			$tgmpa_parent_slug = ( property_exists( $this->tgmpa_instance, 'parent_slug' ) && $this->tgmpa_instance->parent_slug !== 'themes.php' ) ? 'admin.php' : 'themes.php';

			$this->tgmpa_url = apply_filters( $this->theme_name . '_theme_setup_wizard_tgmpa_url', $tgmpa_parent_slug . '?page=' . $this->tgmpa_menu_slug );

		}

		/**
		 * Add admin menus/screens.
		 */
		public function admin_menus() {

			
			add_theme_page( esc_html__( 'Setup Wizard' ,'micronet'), esc_html__( 'Setup Wizard','micronet' ), 'manage_options', $this->page_slug, array(
				$this,
				'setup_wizard',
			) );
			


		}

		/**
		 * Setup steps.
		 *
		 * @since 1.1.1
		 * @access public
		 * @return array
		 */
		public function init_wizard_steps() {

			$this->steps = array(
				'introduction' => array(
					'name'    => esc_html__( 'Introduction','micronet' ),
					'view'    => array( $this, 'envato_setup_introduction' ),
					'handler' => array( $this, 'envato_setup_introduction_save' ),
				),
			);
			
			
				$this->steps['style'] = array(
					'name'    => esc_html__( 'Select a Demo Style','micronet' ),
					'view'    => array( $this, 'envato_setup_demo_style' ),
					'handler' => array( $this, 'envato_setup_demo_style_save' ),
				);
			
			$this->steps['start']         = array(
				'name'    => esc_html__( 'Select features you want in your site.','micronet' ),
				'view'    => array( $this, 'envato_start_setup' ),
				'handler' => array( $this, 'envato_start_setup_save' ),
			);

			$this->steps['updates']         = array(
				'name'    => esc_html__( 'Authenticate and Setup Updates','micronet' ),
				'view'    => array( $this, 'envato_setup_updates' ),
				'handler' => array( $this, 'envato_setup_updates_save' ),
			);

			if ( class_exists( 'TGM_Plugin_Activation' ) && isset( $GLOBALS['tgmpa'] ) ) {
				$this->steps['default_plugins'] = array(
					'name'    => esc_html__( 'Activate plugins required for features.','micronet' ),
					'view'    => array( $this, 'envato_setup_default_plugins' ),
					'handler' => '',
				);
			}

			$this->steps['pagesetup']         = array(
				'name'    => esc_html__( 'Setup necessary settings' ,'micronet'),
				'view'    => array( $this, 'envato_page_setup' ),
				'handler' => array( $this, 'envato_page_setup_save' ),
			);

			if(!empty($_GET['referrer']) && $_GET['referrer'] == 'upgrader'){

				$this->steps['sync_content'] = array(
					'name'    => esc_html__( 'Syncronise Content','micronet' ),
					'view'    => array( $this, 'syncronise_content' ),
					'handler' => '',
				);

				$this->steps['functional_settings'] = array(
					'name'    => esc_html__( 'Functional settings','micronet' ),
					'view'    => array( $this, 'functional_settings' ),
					'handler' => array( $this, 'save_functional_settings' ),
				);
			}else{
				$this->steps['default_content'] = array(
					'name'    => esc_html__( 'Import Content from Theme','micronet' ),
					'view'    => array( $this, 'envato_setup_default_content' ),
					'handler' => '',
				);
				$this->steps['design']          = array(
					'name'    => esc_html__( 'Change design elements','micronet' ),
					'view'    => array( $this, 'envato_setup_design' ),
					'handler' => array( $this, 'envato_setup_design_save' ),
				);
			}

			
			
			
			$this->steps['next_steps']      = array(
				'name'    => esc_html__( 'Are you ready to Roll ?','micronet' ),
				'view'    => array( $this, 'envato_setup_ready' ),
				'handler' => '',
			);

			$this->steps = apply_filters( $this->theme_name . '_theme_setup_wizard_steps', $this->steps );

		}



		function save_functional_settings(){
			
			check_admin_referer( 'envato-setup' );
			

			
			wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
			exit;
			
		}
		function envato_start_setup(){
			
			$micronet_style = get_option('micronet_style');
			
			?><h1><?php esc_html_e( 'Select features for this site','micronet' ); ?></h1>
            <form method="post">
                <p><?php echo esc_html_e( 'Select the features you need for your site. The features here are pre-configured, so the next steps would be based on the selection you make here. These features can be added/removed later on from theme settings as well.','micronet' ); ?></p>
                <hr>
                <div id="purpose_description"></div>
                <div class="theme-features">
                    <ul>
	                    <?php

                    	$demo_style =vibe_get_site_style();
                    		
	                    foreach ( $this->features as $feature => $data ) {
	                    	$class='';$flag=0;
	                    	
	                    	if(isset($data['verify'])){
	                    		$flag = 1;
	                    		foreach($data['verify'] as $plugin){

	                    			if(!vibe_check_plugin_installed($plugin)){
	                    				$flag=0;break;
	                    			}
	                    		}
	                    	}

	                    	if(isset($data['default'])){$flag = 1;}
	                    	if(empty($flag)){$class='';}else{$class='selected';}

		                    ?>
                            <li class="<?php echo vibe_sanitizer($class,'text'); ?>">
                                <a href="#" data-style="<?php echo esc_attr( $feature ); ?>" ><?php echo vibe_sanitizer($data['icon'],'raw');?>
                                    <h4><?php echo vibe_sanitizer($data['label']); ?></h4>
                                    <p><?php echo vibe_sanitizer($data['description']); ?></p>
                                    <?php
                                    if(isset($data['link'])){
                                    	echo '<a href="'.$data['link'].'" target="_blank">Learn More &rsaquo;</a>';
                                    }
                                    if(isset($data['verify'])){
                                    	foreach($data['verify'] as $plugin){
                                    		echo '<input type="hidden" '.(empty($flag)?'':'name="plugins[]"').' value="'.$plugin.'" />';
                                    	}
                                    }
                                    ?>
                                </a>
                            </li>
	                    <?php } ?>
	                     
                    </ul>
                </div>

                <hr><p><em>Have a suggestion for us. Share it with us <a href="https://vibethemes.com/micronet/app/" target="_blank">here</a>  !</em></p>

                <div class="envato-setup-actions step">
                	<div>
                    <input type="submit" class="large_next_button button-next"
                           value="<?php _e( 'Continue', 'micronet' ); ?>" name="save_step"/>
                    <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
                       ><?php esc_html_e( 'Skip this step','micronet' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
					</div>
					<a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>"
                       class="previous_step"><?php esc_html_e( 'Previous step','micronet' ); ?></a>
                </div>
            </form>
            <?php
		}

		function envato_start_setup_save(){
			check_admin_referer( 'envato-setup' );
			if ( ! empty( $_REQUEST['save_step'] )){
			
				$deactivate_plugins = array();
				if(isset($_POST['plugins'])){
					foreach($this->features as $key=>$feature){
						if($key !='course' && isset($feature['verify'])){
							foreach($feature['verify'] as $plugin){
								if(vibe_check_plugin_installed($plugin) && !in_array($plugin,$_POST['plugins'])){
									deactivate_plugins($plugin);
								}
							}					
						}
					}	
				}
				update_option( 'micronet_plugins', $_POST['plugins'] );
			}
			wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
			exit;
		}

		public function onboarding_step(){

			echo '<div class="onboarding '.$this->step.'">';
			echo '<div class="onboarding_header">
			<span><img id="micronet_logo" class="site-logo" src="'.(($this->step == 'introduction')?get_template_directory_uri().'/images/logo.png':get_template_directory_uri().'/images/logo_black.png').'" alt="'.get_bloginfo( 'name' ).'" />
				<span>Work Management Platform</span>
			</span></div>';
			if($this->step =='introduction'){
				?>
				<div class="onboarding_introduction">
					<h2>You are now few steps away from creating your very own Work Management platform !</h2>
					<span>Start your project management platform in minutes.</span>
				</div>
				<a href="https://micronet.work/article/?s=setup" target="_blank">Setup Wizard Video for Reference &rsaquo;</a>
				<?php
			}else{
				$this->setup_wizard_steps();
			}
			
			echo '</div>';
		}
		/**
		 * Show the setup wizard
		 */
		public function setup_wizard() {


			if ( empty( $_GET['page'] ) || $this->page_slug !== $_GET['page'] ) {
				return;
			}
			ob_end_clean();

			$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

			wp_register_script( 'jquery-blockui', $this->plugin_url . 'js/jquery.blockUI.js', array( 'jquery' ), '2.70', true );
			wp_enqueue_script( 'envato-color', $this->plugin_url . 'js/jscolor.js',array(), $this->version );
			wp_register_script( 'envato-setup', $this->plugin_url . 'js/envato-setup.js', array(
				'jquery',
				'jquery-blockui',
			), $this->version );
			wp_localize_script( 'envato-setup', 'envato_setup_params', array(
				'tgm_plugin_nonce' => array(
					'update'  => wp_create_nonce( 'tgmpa-update' ),
					'install' => wp_create_nonce( 'tgmpa-install' ),
				),
				'tgm_bulk_url'     => admin_url( $this->tgmpa_url ),
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'wpnonce'          => wp_create_nonce( 'envato_setup_nonce' ),
				'verify_text'      => esc_html__( '...verifying','micronet' ),
			) );

			//wp_enqueue_style( 'envato_wizard_admin_styles', $this->plugin_url . '/css/admin.css', array(), $this->version );
			wp_enqueue_style( 'envato-setup', $this->plugin_url . 'css/envato-setup.css', array(
				'wp-admin',
				'dashicons',
				'install',
			),MICRONET_VERSION);

			//enqueue style for admin notices
			wp_enqueue_style( 'wp-admin');

			wp_enqueue_media();
			wp_enqueue_script( 'media');
			ob_start();

			$this->setup_wizard_header();
			echo '<div class="setup_wizard_wrapper">';
			$this->onboarding_step();
			echo '<div class="setup_wizard_main">';
			

			
			$show_content = true;
			echo '<div class="setup_wizard_main_header">
			<span></span>
			<span>Having Troubles ? <a href="https://vibethemes.com/micronet/app/">Get Help</a></span>
			</div>';
			echo '<div class="envato-setup-content">';
			if ( ! empty( $_REQUEST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
				$show_content = call_user_func( $this->steps[ $this->step ]['handler'] );
			}
			if ( $show_content ) {
				$this->setup_wizard_content();
			}
			echo '</div></div></div>';
			$this->setup_wizard_footer();
			exit;
		}

		public function get_step_link( $step ) {
			return add_query_arg( 'step', $step, admin_url( 'admin.php?page=' . $this->page_slug ) );
		}

		public function get_next_step_link($info = null) {
			$keys = array_keys( $this->steps );

			$link = add_query_arg( array(
				'step'=> $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ],'installation_type'=>$info
			), remove_query_arg( 'translation_updated' ) );
			

			return $link;
		}

		public function get_previous_step_link($info = null) {
			$keys = array_keys( $this->steps );

			$link = add_query_arg( array(
				'step'=> $keys[ array_search( $this->step, array_keys( $this->steps ) ) - 1 ],'installation_type'=>$info
			), remove_query_arg( 'translation_updated' ) );
			

			return $link;
		}

		public function envato_setup_updates_save(){
			$purchase_code = esc_attr($_POST['purchase_code']);
			if(!empty($purchase_code)){
				$response = wp_remote_get('https://micronet.work/verify?purchase_code='.$purchase_code,array('timeout'     => 120));
				
				$body = json_decode(wp_remote_retrieve_body($response),true);
				if(!empty($body) && !empty($body['verify-purchase']) && $body['verify-purchase']['item_id'] == 44924507){
					update_option('item_purchase_code',$purchase_code);
					return true;
				}
			}
			return false;
		}

		public function envato_setup_updates(){

			//Support the most advanced project in WordPress. do not kill the micronet Project.
			$verified = 0;
			$purchase_code = get_option('item_purchase_code');
			
			if(!empty($purchase_code)){
				$verified =1;
			}else{
				if(empty($_GET['security'])){
					$security = wp_generate_password(6,false,false);	
					set_transient('security',$security,300);
				}else{
					$check = get_transient('security');
					if($_GET['security'] == $check){
						$verified=1;
					}
				}
			}
			
			
			?><h1><?php esc_html_e( 'Authenticate and Setup Updates','micronet' ); ?></h1>
			<p><?php echo esc_html_e( 'Required to setup theme and plugin updates.','micronet' ); ?></p>
                <hr>
                <?php
                if($verified ){

                	if(!empty($_GET['purchase_code'])){
	            		update_option('item_purchase_code',esc_attr($_GET['purchase_code']));

	            		update_option('envato_token',array(
							'refresh_token'=>esc_attr($_GET['refresh_token']),
							'access_token'=>esc_attr($_GET['access_token']),
							'expires'=>esc_attr($_GET['expires']),
						));
					}
        		?>
        		<div class="envato-setup-actions step">
        			<?php
        			if(!empty($_GET['referrer']) && $_GET['referrer'] == 'about'){
        				?>
        				<a href="<?php echo admin_url( 'index.php?page=micronet-about'); ?>"
	                       class="large_next_button button-next"><?php esc_html_e( 'Updates Active. Back to About page.','micronet' ); ?></a>
        				<?php
        			}else{
        				?>
        				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
	                       class="large_next_button button-next"><?php esc_html_e( 'Updates Active. Proceed to next.','micronet' ); ?></a>
        				<?php

        			}
        			?>
	               <a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>" class="previous_step"><?php esc_html_e( 'Previous Step.','micronet' ); ?></a>
               	</div>
        		<?php
                }else{
                ?>
                <div class="envato_authentication_wrapper">
		            <div class="envato_authentication_block">
	                	<p>Use your item purchase code to validate this site. </p>
		                <form method="post">
		                    <input type="text" name="purchase_code" placeholder="Enter Item Purchase Code">
							<input type="submit" class="large_next_button button-next"
		                           value="<?php _e( 'Continue', 'micronet' ); ?>" name="save_step"/>
		                </form>
		            </div>
	            </div>
            <?php
            	}
		}

		public function envato_page_setup(){

			global $wpdb;
			$layout_count = $wpdb->get_results($wpdb->prepare("SELECT post_type,count(*) as count FROM {$wpdb->posts} where post_type IN (%s,%s,%s,%s,%s,%s) GROUP BY post_type ",'member-profile','member-card','group-layout','group-card','course-layout','course-card'),ARRAY_A);
			?><h1><?php esc_html_e( 'Setup Required Pages/Layouts','micronet' ); ?></h1>
			<p><?php echo esc_html_e( 'Automatically configure and set required pages for micronet. There are important pages required for LMS to work properly. We recommend everyone using the LMS to setup these pages.','micronet' ); ?></p>

                <hr>

                <table class="micronet-setup-pages" cellspacing="0">
					<thead>
						<tr>
							<th class="page-name">Layout/Page Name</th>
							<th class="page-description">Description</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$layouts = array(
							'user-fields'=>array(
								'label'=>__('User Fields','micronet'),
								'description'=>__('Import sample User fields used in displaying member profiles','micronet')
							),
							'member-types'=>array(
								'label'=>__('Member Types','micronet'),
								'description'=>__('Sample member types which will use the sites. Each member types has its own portal.','micronet')
							),
							'member-teams'=>array(
								'label'=>__('Member Teams','micronet'),
								'description'=>__('Setup member teams and hierarchy in your site.','micronet')
							),
							'group-card'=>array(
								'label'=>__('Group Card','micronet'),
								'description'=>__('Group cards displaying group in directory','micronet')
							)
						);

						foreach($layouts as $key=>$layout){
							$check=0;
							foreach($layout_count as $count){
								if($count['post_type'] == $key){
									if(!empty($count['count'])){
										$check=1;
									}
									break;
								}
							}
							?>
							<tr <?php echo (empty($check)?'':'class="done"');?>>
							<td class="page-name"><?php echo esc_html($layout['label']); ?></td>
							<td><?php echo esc_html($layout['description']); ?></td>
							</tr>
						<?php
						}
						?>
						
						<?php if(function_exists('get_option')){$page_ids = get_option('bp-pages');} ?>
						<tr <?php echo (empty($page_ids['course'])?'':'class="done"');?>>
							<td class="page-name">Directory Pages</td>
							<td>
								The Directory pages for Members will be created to browse various items in site. 					</td>
						</tr>
						<?php if(function_exists('get_option') && empty($page_ids)){$page_ids = get_option('bp-pages');}?>
						<tr <?php echo (empty($page_ids['register'])?'':'class="done"');?>>
							<td class="page-name">Registration</td>
							<td>
								Set a default registration form for users to register on your site. You can disable it from settings. 						</td>
						</tr>

						<?php 
							if(!empty($_GET['referrer']) && $_GET['referrer'] == 'upgrader'){
								$done = false;
								if(!defined('BP_COURSE_MOD_INSTALLED') ){
									$done = true;
								}

								echo '<tr class="'.($done?'done':'').'">
										<td class="page-name">[Important] Deactivate Old plugins</td>
										<td>
											Old plugins like vibe course module,vibe customtypes ,micronet front end will bve deactivated since new micronet plugin has all of them in micronet plugin.</td>
									</tr>';
								$page_set = false;
								if(function_exists('vibebp_get_setting') && !empty(vibebp_get_setting('bp_single_page'))){
									$page_set = true;
								}
								echo '<tr class="'.($page_set?'done':'').'">
									<td class="page-name">[Important] Setup Bp single page</td>
									<td>
										Bp single page is actual app dashboard page necessary for v4.</td>
								</tr>';
							}
						?>

					</tbody>
				</table>
				<br><p><em>You can deactivate registration and directories features from settings provided in the theme. In case you have a suggestion for us. Share it with us <a href="https://micronet.work/app" target="_blank">here</a>  !</em></p>
				<form method="post">
                <div class="envato-setup-actions step">
                	<div>
	                    <input type="submit" class="large_next_button button-next"
	                           value="<?php _e( 'Continue', 'micronet' ); ?>" name="save_step"/>
	                    <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
	                       ><?php esc_html_e( 'Skip this step','micronet' ); ?></a>
						<?php wp_nonce_field( 'envato-setup' ); ?>
					</div>
				 	<a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>"
                       class="previous_step"><?php esc_html_e( 'Previous step','micronet' ); ?></a>
                </div>
                </form>
			<?php                

		}
		function getRandomColor()
	    {
	        $letters = '0123456789ABCDEF';
	        $color = '#';
	        for ($i = 0; $i < 6; $i++) {
	            $color .= $letters[rand(0, 15)];
	        }
	        return $color;
	    }
		public function envato_page_setup_save($go=null){
			if(empty($go)){
				check_admin_referer( 'envato-setup' );
			}
			update_option('elementor_unfiltered_files_upload',1);

			
			if(class_exists('VibeBP_SetupWizard')){

				$wizrd = VibeBP_SetupWizard::init();
				$style = vibe_get_site_style();
				if(empty($style)){$style = $this->get_default_theme_style();}
				$theme_style = 'https://micronet.work/demodata/' . basename($style) .'/';
				if(!xprofile_get_field_id_from_name('Gender')){
					$wizrd->import_default_xprofile($theme_style.'bp.json');	
				}
				
			}

			$custom_options = $this->_get_json( 'options.json' );
      		if(!empty($custom_options)){
      			foreach ( $custom_options as $option => $value ) {
					if($option == 'vibebp_customizer' ){
						$ops = get_option($option);
						if(empty($ops) || !is_array($ops)){$ops = array();}
						foreach($value as $key => $val){
							$ops[$key] = $val;
						}

						update_option( $option, $ops );

						break;
					}
					if($option == 'vibebp_settings' ){
						$ops = get_option($option);
						if(empty($ops) || !is_array($ops)){$ops = array();}
						foreach($value as $key => $val){
							$ops[$key] = $val;
						}

						update_option( $option, $ops );

						break;
					}
				}
      		}


      		require_once(ABSPATH . 'wp-admin/includes/taxonomy.php'); 
      		$import_tax_terms = [
      			'bp_member_type'=>[
      				'employee'=>'Employee',
      				'intern'=>'Intern',
      				'senior'=>'Senior',
      			],
      			'team'=>[
      				'design'=>'Design',
      				'finance'=>'Finance',
      				'legal'=>'Legal',
      				'management'=>'Management',
      				'marketing'=>'Marketer',
      			]	
      		];
			foreach($import_tax_terms as $tax => $terms){

				foreach($terms as $term=>$label){
					if(!term_exists($term,$tax)){
						$term_id = wp_create_term($term,$tax);
						if(!is_wp_error($term_id) && !is_array($term_id)){
							$term_id=$term_id['term_id'];
							if($tax == 'bp_member_type'){
								add_term_meta($term_id,'bp_type_name',$label.'s');
								add_term_meta($term_id,'bp_type_singular_name',$label);
							}
							add_term_meta( $term_id, $tax.'-color', $this->getRandomColor(), true );	
						}
						
					}
					
				}
			}
			
			update_option('permalink_structure','/%postname%/');
			update_option('membership_active','yes');
			update_option('require_name_email','');
			update_option('comment_moderation','');
			update_option('comment_whitelist','');
			update_option('posts_per_page',6);
			update_option('comments_per_page',5);
			update_option('users_can_register',1);
			
			$bp_active_components = apply_filters('micronet_setup_bp_components',array(
				'xprofile' => 1,
				'settings' => 1,
				'friends' => 1,
				'messages' => 1,
				'activity' => 1,
				'notifications' => 1,
				'members' => 1 
				));

			global $bp;
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			require_once( $bp->plugin_dir . '/bp-core/admin/bp-core-admin-schema.php' );

			bp_update_option( 'bp-active-components', $bp_active_components);
			bp_core_install( $bp_active_components );
			bp_core_add_page_mappings( $bp_active_components);
			
			flush_rewrite_rules();


			if(empty($go)){
				wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
				exit;
			}
		}
		/**
		 * Setup Appointments
		 */
		public function init_vibe_appointments(){

			if(class_exists('VIBE_APPOINTMENTS_DB')){
				$appointment = new VIBE_APPOINTMENTS_DB;
	            $meta = new VIBE_APPOINTMENTS_META_DB;
	    		if(!$appointment->table_exists($appointment->table_name) || !$meta->table_exists($meta->table_name)){
	    			
	                if(!$appointment->table_exists($appointment->table_name)){
	                    $appointment->create_table();    
	                }
	    			if(!$meta->table_exists($meta->table_name)){
	                    $meta->create_table();
	                }
	            }
	        }

			$appointment_settings = [
				'appointments_services'=>'on',
				'appointments_provider'	=> ['instructor'],
				'appointments_slot_time'	=>	30,
				'appointments_buffer_time'	=>	30,
				'expired_slots_clear'	=>	'on',
				'reminder_minutes'	=>	1,
				'appointments_services'	=>	'on',
				'global_commission_percentage'	=>	70
			];


			update_option(VIBE_APPOINTMENTS_OPTION,$appointment_settings);
				
		}
		/**
		 * Setup Wizard Header
		 */

	public function setup_wizard_header() {

		if( is_null ( get_current_screen() )) {
			set_current_screen('micronet_setup_wizard');
		}

		?>
		<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<?php
			// avoid theme check issues.
			echo '<title>' . esc_html__( 'Theme &rsaquo; Setup Wizard' ,'micronet') . '</title>'; ?>
			<link href="https://fonts.googleapis.com/css2?family=Mulish:wght@200;300;400;700;900&display=swap" rel="stylesheet">
			<?php wp_print_scripts( 'envato-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="envato-setup wp-core-ui">
		<?php
		}

		/**
		 * Setup Wizard Footer
		 */
		public function setup_wizard_footer() {
		?>
		<?php if ( 'next_steps' === $this->step ) : ?>
			<a class="wc-return-to-dashboard"
			   href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Return to the WordPress Dashboard' ,'micronet'); ?></a>
		<?php endif; ?>
		</body>
		<?php
		@do_action( 'admin_footer' ); // this was spitting out some errors in some admin templates. quick @ fix until I have time to find out what's causing errors.
		do_action( 'admin_print_footer_scripts' );
		?>
		</html>
		<?php
	}

		/**
		 * Output the steps
		 */
		public function setup_wizard_steps() {
			$ouput_steps = $this->steps;
			array_shift( $ouput_steps );
			?>
			<ol class="envato-setup-steps">
				<?php foreach ( $ouput_steps as $step_key => $step ) : ?>
					<li class="<?php
					$show_link = false;
					if ( $step_key === $this->step ) {
						echo 'active';
					} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
						echo 'done';
						$show_link = true;
					}
					?>"><?php
						if ( $show_link ) {
							?>
							<a href="<?php echo esc_url( $this->get_step_link( $step_key ) ); ?>"><?php echo esc_html( $step['name'] ); ?></a>
							<?php
						} else {
							echo esc_html( $step['name'] );
						}
						?></li>
				<?php endforeach; ?>
			</ol>
			<span></span>
			<?php
		}

		/**
		 * Output the content for the current step
		 */
		public function setup_wizard_content() {
			isset( $this->steps[ $this->step ] ) ? call_user_func( $this->steps[ $this->step ]['view'] ) : false;
		}

		/**
		 * Introduction step
		 */
		public function envato_setup_introduction() {

			if ( isset( $_REQUEST['debug'] ) ) {
				echo '<pre>';
				// debug inserting a particular post so we can see what's going on
				$post_type = 'nav_menu_item';
				$post_id   = 239; // debug this particular import post id.
				$all_data  = $this->_get_json( 'default.json' );
				if ( ! $post_type || ! isset( $all_data[ $post_type ] ) ) {
					echo "Post type $post_type not found.";
				} else {
					echo "Looking for post id $post_id \n";
					foreach ( $all_data[ $post_type ] as $post_data ) {

						if ( $post_data['post_id'] == $post_id ) {
							$this->_process_post_data( $post_type, $post_data, 0, true );
						}
					}
				}
				$this->_handle_delayed_posts();
				
				echo '</pre>';
			} else if ( $this->is_possible_upgrade() ) {

				?>
				<h1><?php printf( esc_html__( 'Welcome to the setup wizard for %s.' ,'micronet'), 'micronet' ); ?></h1>
				<p><?php esc_html_e( 'It looks like you may have recently upgraded to this theme. Great! This setup wizard will help ensure all the default settings are correct. It will also show some information about your new website and support options.','micronet' ); ?></p>
				<p class="envato-setup-actions step">
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button-primary button button-large button-next"><?php esc_html_e( 'Let\'s Go!','micronet' ); ?></a>
					<a href="<?php echo esc_url( wp_get_referer() && ! strpos( wp_get_referer(), 'update.php' ) ? wp_get_referer() : admin_url( '' ) ); ?>"
					   class="button button-large"><?php esc_html_e( 'Not right now','micronet' ); ?></a>
				</p>
				<?php
			} else if ( get_option( 'envato_setup_complete', false )) {

				if(!empty($setup_options)){
					echo vibe_sanitizer($setup_options);	
				}
				
				?>
				<h1><?php printf( esc_html__( 'Welcome to the setup wizard for %s Theme.' ,'micronet'), 'micronet'); ?></h1>
				<p><?php esc_html_e( 'It looks like you have already run the setup wizard. Below are some options: ','micronet' ); ?></p>
				<ul>
					<li>
						<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
						   class="button-next large_next_button"><?php esc_html_e( 'Run Setup Wizard Again','micronet' ); ?></a>
					</li>
					<li>
						<form method="post">
							<input type="hidden" name="reset-font-defaults" value="yes">
							<!--input type="submit" class="button-primary button button-large button-next"
							       value="<?php //_e( 'Reset font style and colors', 'micronet' ); ?>" name="save_step"/ -->
							<?php wp_nonce_field( 'envato-setup' ); ?>
						</form>
					</li>
				</ul>
				<p class="envato-setup-actions step">
					<a href="<?php echo esc_url( wp_get_referer() && ! strpos( wp_get_referer(), 'update.php' ) ? wp_get_referer() : admin_url( '' ) ); ?>"><?php esc_html_e( 'Cancel','micronet' ); ?></a>
				</p>
				<?php
			} else {

				if(!empty($setup_options)){
					echo vibe_sanitizer($setup_options);	
				}
				
				?>

				<h1>Welcome to Micronet Installation Wizard</h1>
				<p>Welcome to Micronet Setup wizard. This setup Wizard will guide you through the setup process. The purpose of this wizard is to make the setup process simpler. You can always enable or disable features and designs after the setup as well.</p>
				<a href="<?php echo esc_url( $this->get_next_step_link('gutenberg') ); ?>" class="large_next_button button-next">Start Installation</a>
				<ul class="micronet_configuration_checks">
				<?php
				$check =1;
				ob_start();

				?>
					<ul class="config">
					<?php
					$memory = $this->micronet_let_to_num( WP_MEMORY_LIMIT );
					$class='no';
					 if ( $memory >= 134217728 ) {$class='yes'; }
					?>
					<li class="<?php echo vibe_sanitizer($class,'text'); ?>"><label><?php echo esc_html__( 'PHP Memory allocation','micronet'); ?></label>
					<?php
					if ( $memory < 134217728 ) { $check=0;
						echo '<mark class="error">' . sprintf( __( '%s - We recommend setting memory to at least 128MB. See: %s Increasing memory allocated to PHP %s ', 'micronet' ), size_format( $memory ), '<a href="http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP">','</a>') . '</mark>';
					} else {
						echo '<mark class="yes">' . size_format( $memory ) . '</mark>';
					}
					?>
					</li>
					<?php 
					$class='no';
					$x = wp_max_upload_size();
					 
					 ?>
					<li class="<?php echo vibe_sanitizer($class,'text'); ?>"><label><?php _e( 'WP Max Upload Size', 'micronet' ); ?></label>
					<?php echo size_format( $x ); ?></li>
					<?php if ( function_exists( 'ini_get' ) ) : ?>
							<?php $class='no'; $x = $this->micronet_let_to_num( ini_get('post_max_size') ) ; if($x >= 33554432){$class = 'yes';}else{if($check){$check=0;}} ?>
							<li class="<?php echo vibe_sanitizer($class,'text'); ?>"><label><?php _e('PHP Post Max Size', 'micronet' ); ?></label>
							<?php echo size_format($x); ?></li>
							<?php
							$class='no'; $x = ini_get('max_execution_time') ; if($x >= 30){$class = 'yes';}
							?>					
							<li class="<?php echo vibe_sanitizer($class,'text'); ?>"><label><?php _e('PHP Time Limit', 'micronet' ); ?></label>
							<?php echo vibe_sanitizer($x,'text').' s '; if($x < 60){printf( '<mark> - We recommend increasing this value to 60. See <a href="%s">Increasing PHP Time limit</a></mark>','https://premium.wpmudev.org/blog/increase-memory-limit/');} ?></li>
							<?php $class='yes';?>
							<li class="<?php echo vibe_sanitizer($class,'text'); ?>"><label><?php _e( 'PHP Max Input Vars', 'micronet' ); ?></label>
								<?php echo ini_get('max_input_vars'); ?>
							</li>
					<?php endif; ?>
					</ul>
				<?php
				$configuration_checks = ob_get_clean();
				?>
				<li><strong><?php echo esc_html__( 'Configuration Check','micronet'); ?> <span class="
					<?php if($check){echo 'yes';}else{echo 'no';}?>"
					><?php if($check){echo __('Passed','micronet');}else{echo __('Failed','micronet');} ?></span></strong>
					<?php echo wp_kses_post($configuration_checks); ?>
				</li>
				<li>
				<?php
					$wp_content = WP_CONTENT_DIR;
					
					$files_to_check = array(
										'' => '0755',
										'themes/micronet/plugins' => '0755',
										'themes/micronet/assets' => '0755',);
					
					$root = WP_CONTENT_DIR;
					
					ob_start();
					?>
					<ul class="config">
						<?php
						$check = 1;
					foreach($files_to_check as $k => $v){
						
						$path = $root.'/'.$k;

						$stat = @stat($path);
						$suggested = $v;
						$actual=__('Unable to detect','micronet');
						if(is_Array($stat)){
							$actual = substr(sprintf('%o', $stat['mode']), -4);	
						}
						

						if($check && version_compare($actual, $suggested) < 0 ){
							$check =0;
						}
						echo '<li class="'.((version_compare($actual, $suggested) < 0 ) ? 'no' : 'yes').'"><label>'.$k.'</label>
						'.$actual.''.((version_compare($actual, $suggested) < 0 ) ? '- '._x('[Recommended]','recommended label','micronet').'<mark> '.$suggested.'</mark>' : '').'
						</li>';
					}
					?>
					</ul>
					<?php
					$configuration_checks = ob_get_clean();
					?>
					<li><strong><?php _ex('File Permissions Check','installer label','micronet'); ?><span class="
					<?php if($check){echo 'yes';}else{echo 'no';}?>"
					><?php if($check){echo __('Passed','micronet');}else{echo __('Failed','micronet');} ?></span></strong>
					<?php echo wp_kses_post($configuration_checks); ?>
					</li>
				</ul>

				<p style="font-size:80%;opacity:0.8"><?php echo sprintf( '<a href="'.esc_url( wp_get_referer() && ! strpos( wp_get_referer(), 'update.php' ) ? wp_get_referer() : admin_url( '' ) ).'">No time right now?</a> If you don\'t want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind! You can re-start setup wizard from WP Admin - Appearance - setup wizard %sImage Reference%s','[ <a href="'.esc_url(get_template_directory_uri() .'/images/help_doc.png').'" target="_blank">','</a> ]'); ?>
				</p>
				<?php
			}
		}

		function micronet_let_to_num( $size ) {
			$l   = substr( $size, -1 );
			$ret = substr( $size, 0, -1 );
			switch ( strtoupper( $l ) ) {
				case 'P':
					$ret *= 1024;
				case 'T':
					$ret *= 1024;
				case 'G':
					$ret *= 1024;
				case 'M':
					$ret *= 1024;
				case 'K':
					$ret *= 1024;
			}
			return $ret;
		}

		public function filter_options( $options ) {
			return $options;
		}

		/**
		 *
		 * Handles save button from welcome page. This is to perform tasks when the setup wizard has already been run. E.g. reset defaults
		 *
		 * @since 1.2.5
		 */
		public function envato_setup_introduction_save() {

			check_admin_referer( 'envato-setup' );

			if ( ! empty( $_POST['reset-font-defaults'] ) && $_POST['reset-font-defaults'] == 'yes' ) {


				$file_name = get_template_directory() . '/style.custom.css';
				if ( file_exists( $file_name ) ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					WP_Filesystem();
					global $wp_filesystem;
					$wp_filesystem->put_contents( $file_name, '' );
				}
				?>
				<p>
					<strong><?php esc_html_e( 'Options have been reset. Please go to Appearance > Customize in the WordPress backend.','micronet' ); ?></strong>
				</p>
				<?php
				return true;
			}

			return false;
		}

		private function _get_plugins() {
			$instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
			$plugins  = array(
				'all'      => array(), // Meaning: all plugins which still have open actions.
				'install'  => array(),
				'update'   => array(),
				'activate' => array(),
			);


			foreach ( $instance->plugins as $slug => $plugin ) {
				if ( $instance->is_plugin_actived( $slug ) && false === $instance->does_plugin_have_update( $slug ) ) {
					
					continue; 
				} else {

					$plugins['all'][ $slug ] = $plugin;

					if ( ! $instance->is_plugin_installed( $slug ) ) {
						$plugins['install'][ $slug ] = $plugin;
					} else {
						if ( false !== $instance->does_plugin_have_update( $slug ) ) {
							$plugins['update'][ $slug ] = $plugin;
						}

						if ( $instance->can_plugin_activate( $slug ) ) {
							$plugins['activate'][ $slug ] = $plugin;
						}
					}
				}
			}

			return $plugins;
		}

		/**
		 * Page setup
		 */
		public function envato_setup_default_plugins() {

			tgmpa_load_bulk_installer();
			// install plugins with TGM.
			if ( ! class_exists( 'TGM_Plugin_Activation' ) || ! isset( $GLOBALS['tgmpa'] ) ) {
				die( 'Failed to find TGM' );
			}
			$url     = wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'envato-setup' );
			$plugins = $this->_get_plugins();

			// copied from TGM

			$method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
			$fields = array_keys( $_POST ); // Extra fields to pass to WP_Filesystem.

			if ( false === ( $creds = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields ) ) ) {
				return true; // Stop the normal page form from displaying, credential request form will be shown.
			}

			// Now we have some credentials, setup WP_Filesystem.
			if ( ! WP_Filesystem( $creds ) ) {
				// Our credentials were no good, ask the user for them again.
				request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );

				return true;
			}

			/* If we arrive here, we have the filesystem */

			?>
			<h1><?php esc_html_e( 'Required Plugins for Installation','micronet' ); ?></h1>
			<form method="post">

				<?php
				$plugins = $this->_get_plugins();
				if ( count( $plugins['all'] ) ) {
					?>
					<p><?php esc_html_e( 'Your website needs a few essential plugins. The following plugins will be installed or updated:','micronet' ); ?></p>
					<ul class="envato-wizard-plugins">
						<?php 
						foreach ( $plugins['all'] as $slug => $plugin ) { 
							?>
							<li data-slug="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $plugin['name'] ); ?>
								<span>
    								<?php
								    $keys = array();
								    if ( isset( $plugins['install'][ $slug ] ) ) {
									    $keys[] = 'Installation';
								    }
								    if ( isset( $plugins['update'][ $slug ] ) ) {
									    $keys[] = 'Update';
								    }
								    if ( isset( $plugins['activate'][ $slug ] ) ) {
									    $keys[] = 'Activation';
								    }
								    echo implode( ' and ', $keys ) . ' required';
								    ?>
    							</span>
								<div class="spinner"></div>
							</li>
						<?php } ?>
					</ul>
					

				<p><?php esc_html_e( 'You can add and remove plugins later on from within WordPress.','micronet' ); ?></p>
				<p><strong>Note</strong> : If you see a "failed" message for a plugin then pelase get in touch with us at <a>facebook.com/vibethemes</a>, "ajax-error" messages are safe to ignore.</p>
				<?php
				} else {
					echo '<p style="padding: 1rem; border: 1px solid #5ab001; background: #e7fce9; color: #5ab001;"><strong>' . __( 'Good news! All plugins are already installed and up to date. Please continue.','micronet' ) . '</strong></p>';
				} ?>
				<div class="envato-setup-actions step">
					<div>
						<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="large_next_button button-next"
					   data-callback="install_plugins"><?php esc_html_e( 'Continue','micronet' ); ?></a>
						<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"><?php esc_html_e( 'Skip this step' ,'micronet'); ?></a>
					</div>
					<a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>" class="previous_step"><?php esc_html_e( 'Previous step' ,'micronet'); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
				</div>
			</form>
			<?php
		}

		public function ajax_plugins() {
			if ( ! check_ajax_referer( 'envato_setup_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) ) {
				wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No Slug Found','micronet' ) ) );
			}
			$json = array();
			// send back some json we use to hit up TGM
			$plugins = $this->_get_plugins();
			// what are we doing with this plugin?
			foreach ( $plugins['activate'] as $slug => $plugin ) {
				if ( esc_attr($_POST['slug']) == $slug ) {

					$json = array(
						'url'           => admin_url( $this->tgmpa_url ),
						'plugin'        => array( $slug ),
						'tgmpa-page'    => $this->tgmpa_menu_slug,
						'plugin_status' => 'all',
						'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
						'action'        => 'tgmpa-bulk-activate',
						'action2'       => - 1,
						'message'       => esc_html__( 'Activating Plugin','micronet' ),
					);
					break;
				}
			}
			foreach ( $plugins['update'] as $slug => $plugin ) {
				if ( esc_attr($_POST['slug']) == $slug ) {
					$json = array(
						'url'           => admin_url( $this->tgmpa_url ),
						'plugin'        => array( $slug ),
						'tgmpa-page'    => $this->tgmpa_menu_slug,
						'plugin_status' => 'all',
						'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
						'action'        => 'tgmpa-bulk-update',
						'action2'       => - 1,
						'message'       => esc_html__( 'Updating Plugin','micronet' ),
					);
					break;
				}
			}
			foreach ( $plugins['install'] as $slug => $plugin ) {
				if ( esc_attr($_POST['slug']) == $slug ) {

					$json = array(
						'url'           => admin_url( $this->tgmpa_url ),
						'plugin'        => array( $slug ),
						'tgmpa-page'    => $this->tgmpa_menu_slug,
						'plugin_status' => 'all',
						'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
						'action'        => 'tgmpa-bulk-install',
						'action2'       => - 1,
						'message'       => esc_html__( 'Installing Plugin','micronet' ),
					);
					break;
				}
			}

			if ( $json ) {
				$json['hash'] = md5( serialize( $json ) ); // used for checking if duplicates happen, move to next plugin
				wp_send_json( $json );
			} else {
				wp_send_json( array( 'done' => 1, 'message' => esc_html__( 'Success' ,'micronet') ) );
			}
			exit;

		}

		private function _content_default_get() {

			$content = array();

			// find out what content is in our default json file.
			$available_content = $this->_get_json( 'default.json' );
			if(empty($available_content)){
				echo '<div class="message">Unable to load file from server, reload this page. If issue persists, consult webhost,as your server is unable to load sample data from Amazon server.</div>';
			}else{
				foreach ( $available_content as $post_type => $post_data ) {
					if ( count( $post_data ) ) {
						$first           = current( $post_data );
						$post_type_title = ! empty( $first['type_title'] ) ? $first['type_title'] : ucwords( $post_type ) . 's';
						if ( $post_type_title == 'Navigation Menu Items' ) {
							$post_type_title = 'Navigation';
						}

						$check = apply_filters('micronet_import_post_type_content',1,$post_type);
						
						$content[ $post_type ] = array(
							'title'            => $post_type_title,
							'description'      => sprintf( esc_html__( 'This will create default %s as seen in the demo.','micronet' ), $post_type_title ),
							'pending'          => esc_html__( 'Pending.','micronet' ),
							'installing'       => esc_html__( 'Installing.','micronet' ),
							'success'          => esc_html__( 'Success.' ,'micronet'),
							'install_callback' => array( $this, '_content_install_type' ),
							'checked'          => $this->is_possible_upgrade()?0:$check,
							'disabled'		   => !$check,
							// dont check if already have content installed.
						);
					}
				}
			}

			$content['widgets'] = array(
				'title'            => esc_html__( 'Widgets' ,'micronet'),
				'description'      => esc_html__( 'Insert default sidebar widgets as seen in the demo.' ,'micronet'),
				'pending'          => esc_html__( 'Pending.','micronet' ),
				'installing'       => esc_html__( 'Installing Default Widgets.' ,'micronet'),
				'success'          => esc_html__( 'Success.','micronet' ),
				'install_callback' => array( $this, '_content_install_widgets' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);
			

			$content['options_panel'] = array(
				'title'            => esc_html__( 'Vibe Options Panel','micronet' ),
				'description'      => esc_html__( 'Configure options panel.','micronet' ),
				'pending'          => esc_html__( 'Pending.','micronet' ),
				'installing'       => esc_html__( 'Installing options panel settings.','micronet' ),
				'success'          => esc_html__( 'Success.','micronet' ),
				'install_callback' => array( $this, '_content_options_settings' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);
			
			$content['settings'] = array(
				'title'            => esc_html__( 'Settings' ,'micronet'),
				'description'      => esc_html__( 'Configure default settings (menus locations, widget connections, set home page, link course units, quiz questions etc).' ,'micronet'),
				'pending'          => esc_html__( 'Pending.','micronet' ),
				'installing'       => esc_html__( 'Installing Default Settings.','micronet' ),
				'success'          => esc_html__( 'Success.','micronet' ),
				'install_callback' => array( $this, '_content_install_settings' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);

			$content['users'] = array(
				'title'            => esc_html__( 'User Fields','micronet' ),
				'description'      => esc_html__( 'Configure profile fields for imported layouts.','micronet' ),
				'pending'          => esc_html__( 'Pending.','micronet' ),
				'installing'       => esc_html__( 'Installing user settings.','micronet' ),
				'success'          => esc_html__( 'Success.','micronet' ),
				'install_callback' => array( $this, '_content_setup_users' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);
			
			$content = apply_filters( $this->theme_name . '_theme_setup_wizard_content', $content );

			return $content;

		}

		public function functional_settings(){


			?>
			<h1><?php esc_html_e( 'Functional Settings','micronet' ); ?></h1>
			<p>Configure most important system settings on how you would like to use micronet.</p>
			<hr>
			<form method="post">
				<div class="button-group-wrapper">
					<input type="checkbox" name="vibebp_login" id="vibebp_login" />
					<label for="vibebp_login">
					<div class="buttons-group">					
						<a class="border-button active">Keep WP Login system</a>
						<a class="border-button">Migrate to VibeBP Login system</a>
					</div>
					</label>
				</div>
				<div class="button-group-wrapper">
					<input type="checkbox" name="vibebp_profiles" id="vibebp_profiles">
					<label for="vibebp_profiles">
					<div class="buttons-group">
						<input type="checkbox">
						<a class="border-button active">Keep Standard Profiles</a>
						<a class="border-button">Migrate to VibeBP Profiles</a>
					</div>
					</label>
				</div>
				<div class="button-group-wrapper">
					<input type="checkbox" name="vibebp_course_layouts" id="vibebp_course_layouts">
					<label for="vibebp_course_layouts">
					<div class="buttons-group">
						<input type="checkbox">
						<a class="border-button active">Keep Standard Course layouts</a>
						<a class="border-button">Migrate to VibeBP Course Layouts</a>
					</div>
					</label>
				</div>
				<div class="button-group-wrapper">
					<input type="checkbox" name="vibebp_group_layouts" id="vibebp_group_layouts">
					<label for="vibebp_group_layouts">
					<div class="buttons-group">
						<input type="checkbox">
						<a class="border-button active">Keep Standard Group layouts</a>
						<a class="border-button">Migrate to VibeBP Group Layouts</a>
					</div>
					</label>
				</div>
				<div class="button-group-wrapper">
					<input type="checkbox" name="vibebp_directory_layouts" id="vibebp_directory_layouts">
					<label for="vibebp_directory_layouts">
					<div class="buttons-group">
						<input type="checkbox">
						<a class="border-button active">Keep Standard Directory</a>
						<a class="border-button">Migrate to VibeBP Directory</a>
					</div>
					</label>
				</div>
				<p>Colored is selected. Whats the difference between Standard vs VibeBP ? See <a href="" target="_blank">more</a></p>
				<div class="envato-setup-actions step">
					<div><input type="submit"
					   class="large_next_button button-next" name="save_step"
					   value="<?php esc_html_e( 'Finalise' ,'micronet'); ?>" />
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   ><?php esc_html_e( 'Skip this step','micronet' ); ?></a>
					</div>
					<a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>" class="previous_step"><?php esc_html_e( 'Previous step','micronet' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
				</div>
			</form>
			<?php
		}

		public function syncronise_content(){
			?>
			<h1><?php esc_html_e( 'Syncronise Content','micronet' ); ?></h1>
			<form method="post">
					<p>micronet 4 is a revolutionary framework. Much faster and built for modern web.
				<hr></p>
				<table class="" cellspacing="0">
					<thead>
					<tr>
						<th class="item"><?php esc_html_e( 'Item' ,'micronet'); ?></th>
						<th class="item"><?php esc_html_e( 'Description' ,'micronet'); ?></th>
						<th class="status"><?php esc_html_e( 'Status' ,'micronet'); ?></th>
					</tr>
					</thead>
					<tbody>
				<?php
				if(function_exists('micronet_get_sync_settings')){
					$sync_settings = micronet_get_sync_settings();

					foreach($sync_settings as $setting){

						if(!empty($setting['required_for_upgrade'])){
							echo '<tr valign="top" class="sync_step">
								<td scope="row" class="titledesc">
									<label>'.$setting['label'].'</label>
								</td>
								<td>'.$setting['description'].'</td>
								<td class="forminp"><a class=" sync_resync" data-id="'.$setting['id'].'">'.__('Waiting for sync ','micronet').'</a></td></tr>';
						}
					}
					wp_nonce_field('sync_resync','sync_security');
				}else{
					echo 'micronet plugin not active';
				}
				
				?>
					</tbody>
				</table>

				<div class="envato-setup-actions step">
					<div><a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="large_next_button button-next"
					   data-callback="run_upgrader"><?php esc_html_e( 'Upgrade & Sync' ,'micronet'); ?></a>
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   ><?php esc_html_e( 'Skip this step','micronet' ); ?></a>
					</div>
					<a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>" class="previous_step"><?php esc_html_e( 'Previous step','micronet' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
				</div>
			</form>
			<?php
		}
		/**
		 * Page setup
		 */
		public function envato_setup_default_content() {
			?>
			<h1><?php esc_html_e( 'Import Demo Content','micronet' ); ?></h1>
			<form method="post">
					<p>It's time to insert some default content for your new WordPress website. Choose what you would like inserted below and click Continue. It is recommended to leave everything selected. Once inserted, this content can be managed from the WordPress admin dashboard.
				<hr><p><strong>Note</strong>&nbsp;&nbsp;If you do not see "Posts", "Pages" in import items section. Make sure to reload this page. Make sure you are connected to the internet  before content re-import. <a href="?page=micronet-setup&step=default_content&force=1">Load content from alternate server</a>  or See <a href="http://micronet.work/article?s=import+data" target="_blank">document</a> help</a></p>
				<p style="font-size:80%;opacity:0.8;">Re-installing content from another demo, <a class="clear_imported_posts" data-security="<?php  echo wp_create_nonce('clear_imported_posts'); ?>"> clear cache </a></p>    


				<table class="envato-setup-pages" cellspacing="0">
					<thead>
					<tr>
						<td class="check"></td>
						<th class="item"><?php esc_html_e( 'Item' ,'micronet'); ?></th>
						<th class="description"><?php esc_html_e( 'Description','micronet' ); ?></th>
						<th class="status"><?php esc_html_e( 'Status' ,'micronet'); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php 

					foreach ( $this->_content_default_get() as $slug => $default ) { ?>
						<tr class="envato_default_content" data-content="<?php echo esc_attr( $slug ); ?>">
							<td>
								<input type="checkbox" name="default_content[<?php echo esc_attr( $slug ); ?>]"
								       class="envato_default_content"
								       id="default_content_<?php echo esc_attr( $slug ); ?>"
								       value="1" <?php echo ( ! isset( $default['checked'] ) || $default['checked'] ) ? ' checked' : ''; ?> <?php echo (  isset( $default['disabled'] ) && $default['disabled'] ) ? ' disabled' : ''; ?>>
							</td>
							<td><label
									for="default_content_<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $default['title'] ); ?></label>
							</td>
							<td class="description"><?php echo esc_html( $default['description'] ); ?></td>
							<td class="status"><span><?php echo esc_html( $default['pending'] ); ?></span>
								<div class="spinner"></div>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>

				<div class="envato-setup-actions step">
					<div><a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="large_next_button button-next"
					   data-callback="install_content"><?php esc_html_e( 'Import Content' ,'micronet'); ?></a>
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   ><?php esc_html_e( 'Skip this step','micronet' ); ?></a>
					</div>
					<a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>" class="previous_step"><?php esc_html_e( 'Previous step','micronet' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
				</div>
			</form>
			<?php
		}

		public function ajax_content() { //here

			//cehck
			$content = $this->_content_default_get();
			if ( ! check_ajax_referer( 'envato_setup_nonce', 'wpnonce' ) || empty( $_POST['content'] ) && isset( $content[ $_POST['content'] ] ) ) {
				wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No content Found','micronet' ) ) );
			}

			$json         = false;
			$this_content = $content[ $_POST['content'] ];

			if ( isset( $_POST['proceed'] ) ) {
				// install the content!

				$this->log( ' -!! STARTING SECTION for ' . $_POST['content'] );

				// init delayed posts from transient.
				$this->delay_posts = get_transient( 'delayed_posts' );
				if ( ! is_array( $this->delay_posts ) ) {
					$this->delay_posts = array();
				}

				if ( ! empty( $this_content['install_callback'] ) ) {
					if ( $result = call_user_func( $this_content['install_callback'] ) ) {

						$this->log( ' -- FINISH. Writing ' . count( $this->delay_posts, COUNT_RECURSIVE ) . ' delayed posts to transient ' );
						set_transient( 'delayed_posts', $this->delay_posts, 60 * 60 * 24 );

						if ( is_array( $result ) && isset( $result['retry'] ) ) {
							// we split the stuff up again.
							$json = array(
								'url'         => admin_url( 'admin-ajax.php' ),
								'action'      => 'envato_setup_content',
								'proceed'     => 'true',
								'retry'       => time(),
								'retry_count' => $result['retry_count'],
								'content'     => $_POST['content'],
								'_wpnonce'    => wp_create_nonce( 'envato_setup_nonce' ),
								'message'     => $this_content['installing'],
								'logs'        => $this->logs,
								'errors'      => $this->errors,
							);
						} else {
							$json = array(
								'done'    => 1,
								'message' => $this_content['success'],
								'debug'   => $result,
								'logs'    => $this->logs,
								'errors'  => $this->errors,
							);
						}
					}
				}
			} else {

				$json = array(
					'url'      => admin_url( 'admin-ajax.php' ),
					'action'   => 'envato_setup_content',
					'proceed'  => 'true',
					'content'  => $_POST['content'],
					'_wpnonce' => wp_create_nonce( 'envato_setup_nonce' ),
					'message'  => $this_content['installing'],
					'logs'     => $this->logs,
					'errors'   => $this->errors,
				);
			}

			if ( $json ) {
				$json['hash'] = md5( serialize( $json ) ); // used for checking if duplicates happen, move to next plugin
				wp_send_json( $json );

			} else {
				wp_send_json( array(
					'error'   => 1,
					'message' => esc_html__( 'Error','micronet' ),
					'logs'    => $this->logs,
					'errors'  => $this->errors,
				) );
			}

			exit;

		}


		private function _imported_term_id( $original_term_id, $new_term_id = false ) {
			$terms = get_transient( 'importtermids' );
			if ( ! is_array( $terms ) ) {
				$terms = array();
			}
			if ( $new_term_id ) {
				if ( ! isset( $terms[ $original_term_id ] ) ) {
					$this->log( 'Insert old TERM ID ' . $original_term_id . ' as new TERM ID: ' . $new_term_id );
				} else if ( $terms[ $original_term_id ] != $new_term_id ) {
					$this->error( 'Replacement OLD TERM ID ' . $original_term_id . ' overwritten by new TERM ID: ' . $new_term_id );
				}
				$terms[ $original_term_id ] = $new_term_id;
				set_transient( 'importtermids', $terms, 60 * 60 * 24 );
			} else if ( $original_term_id && isset( $terms[ $original_term_id ] ) ) {
				return $terms[ $original_term_id ];
			}

			return false;
		}


		public function vc_post( $post_id = false ) {

			$vc_post_ids = get_transient( 'import_vc_posts' );
			if ( ! is_array( $vc_post_ids ) ) {
				$vc_post_ids = array();
			}
			if ( $post_id ) {
				$vc_post_ids[ $post_id ] = $post_id;
				set_transient( 'import_vc_posts', $vc_post_ids, 60 * 60 * 24 );
			} else {

				$this->log( 'Processing vc pages 2: ' );

				return;
				if ( class_exists( 'Vc_Manager' ) && class_exists( 'Vc_Post_Admin' ) ) {
					$this->log( $vc_post_ids );
					$vc_manager = Vc_Manager::getInstance();
					$vc_base    = $vc_manager->vc();
					$post_admin = new Vc_Post_Admin();
					foreach ( $vc_post_ids as $vc_post_id ) {
						$this->log( 'Save ' . $vc_post_id );
						$vc_base->buildShortcodesCustomCss( $vc_post_id );
						$post_admin->save( $vc_post_id );
						$post_admin->setSettings( $vc_post_id );
						//twice? bug?
						$vc_base->buildShortcodesCustomCss( $vc_post_id );
						$post_admin->save( $vc_post_id );
						$post_admin->setSettings( $vc_post_id );
					}
				}
			}

		}

		public function elementor_post( $post_id = false ) {
			//
			// regenrate the CSS for this Elementor post
			if( class_exists( 'Elementor\Post_CSS_File' ) ) {
                $post_css = new Elementor\Post_CSS_File($post_id);
				$post_css->update();
			}
			if(class_exists('Elementor\Core\Files\CSS\Post')){
				$post_css = new Elementor\Core\Files\CSS\Post($post_id);
				$post_css->update();
			}
			
		}

		private function _imported_post_id( $original_id = false, $new_id = false ) {
			if ( is_array( $original_id ) || is_object( $original_id ) ) {
				return false;
			}
			$post_ids = get_transient( 'importpostids' );
			if ( ! is_array( $post_ids ) ) {
				$post_ids = array();
			}
			if ( $new_id ) {
				if ( ! isset( $post_ids[ $original_id ] ) ) {
					$this->log( 'Insert old ID ' . $original_id . ' as new ID: ' . $new_id );
				} else if ( $post_ids[ $original_id ] != $new_id ) {
					$this->error( 'Replacement OLD ID ' . $original_id . ' overwritten by new ID: ' . $new_id );
				}
				$post_ids[ $original_id ] = $new_id;
				set_transient( 'importpostids', $post_ids, 60 * 60 * 24 );
			} else if ( $original_id && isset( $post_ids[ $original_id ] ) ) {
				return $post_ids[ $original_id ];
			} else if ( $original_id === false ) {
				return $post_ids;
			}

			return false;
		}

		private function _post_orphans( $original_id = false, $missing_parent_id = false ) {
			$post_ids = get_transient( 'postorphans' );
			if ( ! is_array( $post_ids ) ) {
				$post_ids = array();
			}
			if ( $missing_parent_id ) {
				$post_ids[ $original_id ] = $missing_parent_id;
				set_transient( 'postorphans', $post_ids, 60 * 60 * 24 );
			} else if ( $original_id && isset( $post_ids[ $original_id ] ) ) {
				return $post_ids[ $original_id ];
			} else if ( $original_id === false ) {
				return $post_ids;
			}

			return false;
		}

		private function _cleanup_imported_ids() {
			// loop over all attachments and assign the correct post ids to those attachments.

		}

		private $delay_posts = array();

		private function _delay_post_process( $post_type, $post_data ) {
			if ( ! isset( $this->delay_posts[ $post_type ] ) ) {
				$this->delay_posts[ $post_type ] = array();
			}
			$this->delay_posts[ $post_type ][ $post_data['post_id'] ] = $post_data;

		}

		// return the difference in length between two strings
		public function cmpr_strlen( $a, $b ) {
			return strlen( $b ) - strlen( $a );
		}

		private function _process_post_data( $post_type, $post_data, $delayed = 0, $debug = false ) {

			$this->log( " Processing $post_type " . $post_data['post_id']." - ". $post_data['post_title'] );

			$this->log($post_data['post_content']);
			$original_post_data = $post_data;

			if ( $debug ) {
				echo "HERE\n";
			}
			if ( ! post_type_exists( $post_type ) ) {
				return false;
			}
			if ( ! $debug && $this->_imported_post_id( $post_data['post_id'] ) ) {
				return true; // already done :)
			}

			if ( empty( $post_data['post_title'] ) && empty( $post_data['post_name'] ) ) {
				// this is menu items
				$post_data['post_name'] = $post_data['post_id'];
			}

			$post_data['post_type'] = $post_type;

			$post_parent = (int) $post_data['post_parent'];
			if ( $post_parent ) {
				// if we already know the parent, map it to the new local ID
				if ( $this->_imported_post_id( $post_parent ) ) {
					$post_data['post_parent'] = $this->_imported_post_id( $post_parent );
					// otherwise record the parent for later
				} else {
					$this->_post_orphans( intval( $post_data['post_id'] ), $post_parent );
					$post_data['post_parent'] = 0;
				}
			}

			// check if already exists
			if ( ! $debug ) {
				if ( empty( $post_data['post_title'] ) && ! empty( $post_data['post_name'] ) ) {
					global $wpdb;
					$sql     = "
					SELECT ID, post_name, post_parent, post_type
					FROM $wpdb->posts
					WHERE post_name = %s
					AND post_type = %s
				";
					$pages   = $wpdb->get_results( $wpdb->prepare( $sql, array(
						$post_data['post_name'],
						$post_type,
					) ), OBJECT_K );
					$foundid = 0;
					foreach ( (array) $pages as $page ) {
						if ( $page->post_name == $post_data['post_name'] && empty( $page->post_title ) ) {
							$foundid = $page->ID;
						}
					}
					if ( $foundid ) {
						$this->_imported_post_id( $post_data['post_id'], $foundid );

						return true;
					}
				}
				// dont use post_exists because it will dupe up on media with same name but different slug
				if ( ! empty( $post_data['post_title'] ) && ! empty( $post_data['post_name'] ) ) {
					global $wpdb;
					$sql     = "
					SELECT ID, post_name, post_parent, post_type
					FROM $wpdb->posts
					WHERE post_name = %s
					AND post_title = %s
					AND post_type = %s
					";
					$pages   = $wpdb->get_results( $wpdb->prepare( $sql, array(
						$post_data['post_name'],
						$post_data['post_title'],
						$post_type,
					) ), OBJECT_K );
					$foundid = 0;
					foreach ( (array) $pages as $page ) {
						if ( $page->post_name == $post_data['post_name'] ) {
							$foundid = $page->ID;
						}
					}
					if ( $foundid ) {
						$this->_imported_post_id( $post_data['post_id'], $foundid );

						return true;
					}
				}
			}

			switch ( $post_type ) {
				case 'attachment':
					// import media via url
					if ( ! empty( $post_data['guid'] ) ) {

						// check if this has already been imported.
						$old_guid = $post_data['guid'];
						if ( $this->_imported_post_id( $old_guid ) ) {
							return true; // alrady done;
						}
						// ignore post parent, we haven't imported those yet.
						// $file_data = wp_remote_get($post_data['guid']);
						$remote_url = $post_data['guid'];

						$post_data['upload_date'] = date( 'Y/m', strtotime( $post_data['post_date_gmt'] ) );
						if ( isset( $post_data['meta'] ) ) {
							foreach ( $post_data['meta'] as $key => $meta ) {
								if ( $key == '_wp_attached_file' ) {
									foreach ( (array) $meta as $meta_val ) {
										if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta_val, $matches ) ) {
											$post_data['upload_date'] = $matches[0];
										}
									}
								}
							}
						}

						$upload = $this->_fetch_remote_file( $remote_url, $post_data );

						if ( ! is_array( $upload ) || is_wp_error( $upload ) ) {
							// todo: error
							return false;
						}

						if ( $info = wp_check_filetype( $upload['file'] ) ) {
							$post['post_mime_type'] = $info['type'];
						} else {
							return false;
						}

						$post_data['guid'] = $upload['url'];

						// as per wp-admin/includes/upload.php
						$post_id = wp_insert_attachment( $post_data, $upload['file'] );
						if($post_id) {

							if ( ! empty( $post_data['meta'] ) ) {
								foreach ( $post_data['meta'] as $meta_key => $meta_val ) {
									if($meta_key != '_wp_attached_file' && !empty($meta_val)) {
										update_post_meta( $post_id, $meta_key, $meta_val );
									}
								}
							}

							wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

							// remap resized image URLs, works by stripping the extension and remapping the URL stub.
							if ( preg_match( '!^image/!', $info['type'] ) ) {
								$parts = pathinfo( $remote_url );
								$name  = basename( $parts['basename'], ".{$parts['extension']}" ); // PATHINFO_FILENAME in PHP 5.2

								$parts_new = pathinfo( $upload['url'] );
								$name_new  = basename( $parts_new['basename'], ".{$parts_new['extension']}" );

								$this->_imported_post_id( $parts['dirname'] . '/' . $name, $parts_new['dirname'] . '/' . $name_new );
							}
							$this->_imported_post_id( $post_data['post_id'], $post_id );
							//$this->_imported_post_id( $old_guid, $post_id );
						}

					}
					break;	
				default:
					// work out if we have to delay this post insertion

					$replace_meta_vals = array(
						/*'_vc_post_settings'                                => array(
							'posts'      => array( 'item' ),
							'taxonomies' => array( 'taxonomies' ),
						),
						'_menu_item_object_id|_menu_item_menu_item_parent' => array(
							'post' => true,
						),*/
					);

					if ( ! empty( $post_data['meta'] ) && is_array( $post_data['meta'] ) ) {

						// replace any elementor post data:

						// fix for double json encoded stuff:
						foreach ( $post_data['meta'] as $meta_key => $meta_val ) {
							if ( is_string( $meta_val ) && strlen( $meta_val ) && $meta_val[0] == '[' ) {
								$test_json = @json_decode( $meta_val, true );
								if ( is_array( $test_json ) ) {
									$post_data['meta'][ $meta_key ] = $test_json;
								}
							}
						}

						array_walk_recursive( $post_data['meta'], array( $this, '_elementor_id_import' ) );

						// replace menu data:
						// work out what we're replacing. a tax, page, term etc..

						if(!empty($post_data['meta']['_menu_item_menu_item_parent'])) {
							$this->log[]='finding id for ...'.$post_data['meta']['_menu_item_menu_item_parent']. '##';
							$new_parent_id = $this->_imported_post_id( $post_data['meta']['_menu_item_menu_item_parent'] );
							if(!$new_parent_id) {
								if ( $delayed ) {
									// already delayed, unable to find this meta value, skip inserting it
									$this->error( 'Unable to find replacement. Continue anyway.... content will most likely break..' );
								} else {
									$this->error( 'Unable to find replacement. Delaying.... ' );
									$this->_delay_post_process( $post_type, $original_post_data );
									return false;
								}
							}
							$post_data['meta']['_menu_item_menu_item_parent'] = $new_parent_id;
						}
						if(isset($post_data['meta'][ '_menu_item_type' ])){

							switch($post_data['meta'][ '_menu_item_type' ]){
								case 'post_type':
									if(!empty($post_data['meta']['_menu_item_object_id'])) {
										$new_parent_id = $this->_imported_post_id( $post_data['meta']['_menu_item_object_id'] );

										$this->log(' #3 FOUND id '.$post_data['meta']['_menu_item_object_id'].' - '.$new_parent_id);

										if(!$new_parent_id) {
											if ( $delayed ) {
												// already delayed, unable to find this meta value, skip inserting it
												$this->error( 'Unable to find replacement. Continue anyway.... content will most likely break..' );
											} else {
												$this->error( 'Unable to find replacement. Delaying.... ' );
												$this->_delay_post_process( $post_type, $original_post_data );
												return false;
											}
										}
										$post_data['meta']['_menu_item_object_id'] = $new_parent_id;
									}
									break;
								case 'taxonomy':
									if(!empty($post_data['meta']['_menu_item_object_id'])) {
										$new_parent_id = $this->_imported_term_id( $post_data['meta']['_menu_item_object_id'] );
										if(!$new_parent_id) {
											if ( $delayed ) {
												// already delayed, unable to find this meta value, skip inserting it
												$this->error( 'Unable to find replacement. Continue anyway.... content will most likely break..' );
											} else {
												$this->error( 'Unable to find replacement. Delaying.... ' );
												$this->_delay_post_process( $post_type, $original_post_data );
												return false;
											}
										}
										$post_data['meta']['_menu_item_object_id'] = $new_parent_id;
									}
									break;
							}
						}

						// please ignore this horrible loop below:
						// it was an attempt to automate different visual composer meta key replacements
						// but I'm not using visual composer any more, so ignoring it.
						foreach ( $replace_meta_vals as $meta_key_to_replace => $meta_values_to_replace ) {

							$meta_keys_to_replace   = explode( '|', $meta_key_to_replace );
							$success                = false;
							$trying_to_find_replace = false;
							foreach ( $meta_keys_to_replace as $meta_key ) {

								if ( ! empty( $post_data['meta'][ $meta_key ] ) ) {

									$meta_val = $post_data['meta'][ $meta_key ];

									if ( $debug ) {
										echo "Meta key: $meta_key \n";
										var_dump( $meta_val );
									}

									// if we're replacing a single post/tax value.
									if ( isset( $meta_values_to_replace['post'] ) && $meta_values_to_replace['post'] && (int) $meta_val > 0 ) {
										$trying_to_find_replace = true;
										$new_meta_val           = $this->_imported_post_id( $meta_val );
										if ( $new_meta_val ) {
											$post_data['meta'][ $meta_key ] = $new_meta_val;
											$success                        = true;
										} else {
											$success = false;
											break;
										}
									}
									if ( isset( $meta_values_to_replace['taxonomy'] ) && $meta_values_to_replace['taxonomy'] && (int) $meta_val > 0 ) {
										$trying_to_find_replace = true;
										$new_meta_val           = $this->_imported_term_id( $meta_val );
										if ( $new_meta_val ) {
											$post_data['meta'][ $meta_key ] = $new_meta_val;
											$success                        = true;
										} else {
											$success = false;
											break;
										}
									}
									if ( is_array( $meta_val ) && isset( $meta_values_to_replace['posts'] ) ) {

										foreach ( $meta_values_to_replace['posts'] as $post_array_key ) {

											$this->log( 'Trying to find/replace "' . $post_array_key . '"" in the ' . $meta_key . ' sub array:' );
											//$this->log(var_export($meta_val,true));

											$this_success = false;
											array_walk_recursive( $meta_val, function ( &$item, $key ) use ( &$trying_to_find_replace, $post_array_key, &$success, &$this_success, $post_type, $original_post_data, $meta_key, $delayed ) {
												if ( $key == $post_array_key && (int) $item > 0 ) {
													$trying_to_find_replace = true;
													$new_insert_id          = $this->_imported_post_id( $item );
													if ( $new_insert_id ) {
														$success      = true;
														$this_success = true;
														$this->log( 'Found' . $meta_key . ' -> ' . $post_array_key . ' replacement POST ID insert for ' . $item . ' ( as ' . $new_insert_id . ' ) ' );
														$item = $new_insert_id;
													} else {
														$this->error( 'Unable to find ' . $meta_key . ' -> ' . $post_array_key . ' POST ID insert for ' . $item . ' ' );
													}
												}
											} );
											if ( $this_success ) {
												$post_data['meta'][ $meta_key ] = $meta_val;
											}
										}
										foreach ( $meta_values_to_replace['taxonomies'] as $post_array_key ) {

											$this->log( 'Trying to find/replace "' . $post_array_key . '"" TAXONOMY in the ' . $meta_key . ' sub array:' );
											//$this->log(var_export($meta_val,true));

											$this_success = false;
											array_walk_recursive( $meta_val, function ( &$item, $key ) use ( &$trying_to_find_replace, $post_array_key, &$success, &$this_success, $post_type, $original_post_data, $meta_key, $delayed ) {
												if ( $key == $post_array_key && (int) $item > 0 ) {
													$trying_to_find_replace = true;
													$new_insert_id          = $this->_imported_term_id( $item );
													if ( $new_insert_id ) {
														$success      = true;
														$this_success = true;
														$this->log( 'Found' . $meta_key . ' -> ' . $post_array_key . ' replacement TAX ID insert for ' . $item . ' ( as ' . $new_insert_id . ' ) ' );
														$item = $new_insert_id;
													} else {
														$this->error( 'Unable to find ' . $meta_key . ' -> ' . $post_array_key . ' TAX ID insert for ' . $item . ' ' );
													}
												}
											} );

											if ( $this_success ) {
												$post_data['meta'][ $meta_key ] = $meta_val;
											}
										}
									}

									if ( $success ) {
										if ( $debug ) {
											echo "Meta key AFTER REPLACE: $meta_key \n";
											//print_r( $post_data['meta'] );
										}
									}
								}
							}
							if ( $trying_to_find_replace ) {
								$this->log( 'Trying to find/replace postmeta "' . $meta_key_to_replace . '" ' );
								if ( ! $success ) {
									// failed to find a replacement.
									if ( $delayed ) {
										// already delayed, unable to find this meta value, skip inserting it
										$this->error( 'Unable to find replacement. Continue anyway.... content will most likely break..' );
									} else {
										$this->error( 'Unable to find replacement. Delaying.... ' );
										$this->_delay_post_process( $post_type, $original_post_data );

										return false;
									}
								} else {
									$this->log( 'SUCCESSSS ' );
								}
							}
						}
					}

					$post_data['post_content'] = $this->_parse_gallery_shortcode_content($post_data['post_content']);

					// we have to fix up all the visual composer inserted image ids
					$replace_post_id_keys = array(
						'parallax_image',
						'image',
						'item', // vc grid
						'post_id',
					);
					foreach ( $replace_post_id_keys as $replace_key ) {
						if ( preg_match_all( '# ' . $replace_key . '="(\d+)"#', $post_data['post_content'], $matches ) ) {
							foreach ( $matches[0] as $match_id => $string ) {
								$new_id = $this->_imported_post_id( $matches[1][ $match_id ] );
								if ( $new_id ) {
									$post_data['post_content'] = str_replace( $string, ' ' . $replace_key . '="' . $new_id . '"', $post_data['post_content'] );
								} else {
									$this->error( 'Unable to find POST replacement for ' . $replace_key . '="' . $matches[1][ $match_id ] . '" in content.' );
									if ( $delayed ) {
										// already delayed, unable to find this meta value, insert it anyway.

									} else {

										$this->error( 'Adding ' . $post_data['post_id'] . ' to delay listing.' );
										//                                      echo "Delaying post id ".$post_data['post_id']."... \n\n";
										$this->_delay_post_process( $post_type, $original_post_data );

										return false;
									}
								}
							}
						}
					}
					$replace_tax_id_keys = array(
						'taxonomies',
					);
					foreach ( $replace_tax_id_keys as $replace_key ) {
						if ( preg_match_all( '# ' . $replace_key . '="(\d+)"#', $post_data['post_content'], $matches ) ) {
							foreach ( $matches[0] as $match_id => $string ) {
								$new_id = $this->_imported_term_id( $matches[1][ $match_id ] );
								if ( $new_id ) {
									$post_data['post_content'] = str_replace( $string, ' ' . $replace_key . '="' . $new_id . '"', $post_data['post_content'] );
								} else {
									$this->error( 'Unable to find TAXONOMY replacement for ' . $replace_key . '="' . $matches[1][ $match_id ] . '" in content.' );
									if ( $delayed ) {
										// already delayed, unable to find this meta value, insert it anyway.
									} else {
										//                                      echo "Delaying post id ".$post_data['post_id']."... \n\n";
										$this->_delay_post_process( $post_type, $original_post_data );

										return false;
									}
								}
							}
						}
					}

					$post_id = wp_insert_post( $post_data, true );

					if ( ! is_wp_error( $post_id ) ) {
						$this->_imported_post_id( $post_data['post_id'], $post_id );
						// add/update post meta
						if ( ! empty( $post_data['meta'] ) ) {
							foreach ( $post_data['meta'] as $meta_key => $meta_val ) {

								// if the post has a featured image, take note of this in case of remap
								if ( '_thumbnail_id' == $meta_key ) {
									/// find this inserted id and use that instead.
									$inserted_id = $this->_imported_post_id( intval( $meta_val ) );
									if ( $inserted_id ) {
										$meta_val = $inserted_id;
									}
								}

								if(!is_numeric($meta_key)){
									update_post_meta( $post_id, $meta_key, $meta_val );
								}

							}
						}
						if ( ! empty( $post_data['terms'] ) ) {
							$terms_to_set = array();
							foreach ( $post_data['terms'] as $term_slug => $terms ) {
								foreach ( $terms as $term ) {
									$taxonomy = $term['taxonomy'];

									if (!is_Array($taxonomy) && taxonomy_exists( $taxonomy ) ) {
										$term_exists = term_exists( $term['slug'], $taxonomy );
										$term_id     = is_array( $term_exists ) ? $term_exists['term_id'] : $term_exists;
										if ( ! $term_id ) {
											if ( ! empty( $term['parent'] ) ) {
												// see if we have imported this yet?
												$term['parent'] = $this->_imported_term_id( $term['parent'] );
											}
											$t = wp_insert_term( $term['name'], $taxonomy, $term );
											if ( ! is_wp_error( $t ) ) {
												$term_id = $t['term_id'];
											} else {
												// todo - error
												continue;
											}
										}
										$this->_imported_term_id( $term['term_id'], $term_id );
										// add the term meta.
										if($term_id && !empty($term['meta']) && is_array($term['meta'])){
											foreach($term['meta'] as $meta_key => $meta_val){
											    // we have to replace certain meta_key/meta_val
                                                // e.g. thumbnail id from woocommerce product categories.
                                                switch($meta_key){
                                                    case 'thumbnail_id':
                                                        if( $new_meta_val = $this->_imported_post_id($meta_val) ){
                                                            // use this new id.
                                                            $meta_val = $new_meta_val;
                                                        }
                                                        break;
                                                    case 'course_cat_thumbnail_id':
                                                    	 if( $new_meta_val = $this->_imported_post_id($meta_val) ){
                                                            // use this new id.
                                                            $meta_val = $new_meta_val;
                                                        }
                                                    break;
                                                    case 'service_type_thumbnail_id':
                                                    	 if( $new_meta_val = $this->_imported_post_id($meta_val) ){
                                                            // use this new id.
                                                            $meta_val = $new_meta_val;
                                                        }
                                                    break;
                                                }
												update_term_meta( $term_id, $meta_key, $meta_val );
											}
										}
										$terms_to_set[ $taxonomy ][] = intval( $term_id );
									}
								}
							}
							foreach ( $terms_to_set as $tax => $ids ) {
								wp_set_post_terms( $post_id, $ids, $tax );
							}
						}

						// procses visual composer just to be sure.
						if ( strpos( $post_data['post_content'], '[vc_' ) !== false ) {
							$this->vc_post( $post_id );
						}
						if ( !empty($post_data['meta']['_elementor_data']) || !empty($post_data['meta']['_elementor_css']) ) {
							$this->log( ' ---- Processing ' . $post_id  . ' for elementor css ' );
							$this->elementor_post( $post_id );
						}
					}

					break;
			}

			return true;
		}

		private function _parse_gallery_shortcode_content($content){
			// we have to format the post content. rewriting images and gallery stuff
			$replace      = $this->_imported_post_id();
			$urls_replace = array();
			foreach ( $replace as $key => $val ) {
				if ( $key && $val && ! is_numeric( $key ) && ! is_numeric( $val ) ) {
					$urls_replace[ $key ] = $val;
				}
			}
			if ( $urls_replace ) {
				uksort( $urls_replace, array( &$this, 'cmpr_strlen' ) );
				foreach ( $urls_replace as $from_url => $to_url ) {
					$content = str_replace( $from_url, $to_url, $content );
				}
			}
			if ( preg_match_all( '#\[gallery[^\]]*\]#', $content, $matches ) ) {
				foreach ( $matches[0] as $match_id => $string ) {
					if ( preg_match( '#ids="([^"]+)"#', $string, $ids_matches ) ) {
						$ids = explode( ',', $ids_matches[1] );
						foreach ( $ids as $key => $val ) {
							$new_id = $val ? $this->_imported_post_id( $val ) : false;
							if ( ! $new_id ) {
								unset( $ids[ $key ] );
							} else {
								$ids[ $key ] = $new_id;
							}
						}
						$new_ids                   = implode( ',', $ids );
						$content = str_replace( $ids_matches[0], 'ids="' . $new_ids . '"', $content );
					}
				}
			}
			return $content;
		}

		public function _elementor_id_import( &$item, $key ) {
			
			if ( $key == 'id' && ! empty( $item ) && is_numeric( $item ) ) {
				// check if this has been imported before
				$new_meta_val = $this->_imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( $key == 'page' && ! empty( $item ) ) {

				if ( false !== strpos( $item, 'p.' ) ) {
					$new_id = str_replace('p.', '', $item);
					// check if this has been imported before
					$new_meta_val = $this->_imported_post_id( $new_id );
					if ( $new_meta_val ) {
						$item = 'p.' . $new_meta_val;
					}
				}else if(is_numeric($item)){
					// check if this has been imported before
					$new_meta_val = $this->_imported_post_id( $item );
					if ( $new_meta_val ) {
						$item = $new_meta_val;
					}
				}
			}
			if ( $key == 'post_id' && ! empty( $item ) && is_numeric( $item ) ) {
				// check if this has been imported before
				$new_meta_val = $this->_imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( $key == 'url' && ! empty( $item ) && strstr( $item, 'ocalhost' ) ) {
				// check if this has been imported before
				$new_meta_val = $this->_imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( ($key == 'shortcode' || $key == 'editor') && ! empty( $item ) ) {
				// we have to fix the [contact-form-7 id=133] shortcode issue.
				$item = $this->_parse_gallery_shortcode_content($item);

			}
		}

		public function _content_install_type($type=null,$index=null) {
			$post_type = ! empty( $_POST['content'] ) ? $_POST['content'] : false;
			if(!empty($type)){
				$post_type= $type;
			}
			$all_data  = $this->_get_json( 'default.json' );
			if ( ! $post_type || ! isset( $all_data[ $post_type ] ) ) {
				return false;
			}
			$limit = 10 + ( isset( $_REQUEST['retry_count'] ) ? (int) $_REQUEST['retry_count'] : 0 );
			if(!isset($_REQUEST['retry_count']) && !empty($index)){
				$limit = 5 + ( isset( $index) ? (int) $index : 0 );
			}
			$x  = 0;
			
			$this->logs[]='#1 - Inside the Nav menu item - '.$post_type;
			

			foreach ( $all_data[ $post_type ] as $post_data ) {

				$this->_process_post_data( $post_type, $post_data );

				if ( $x ++ > $limit ) {
					return array( 'retry' => 1, 'retry_count' => $limit );
				}
			}

			$this->_handle_delayed_posts();
			$this->_handle_post_orphans();

			return true;

		}

		private function _handle_post_orphans() {
			$orphans = $this->_post_orphans();
			foreach ( $orphans as $original_post_id => $original_post_parent_id ) {
				if ( $original_post_parent_id ) {
					if ( $this->_imported_post_id( $original_post_id ) && $this->_imported_post_id( $original_post_parent_id ) ) {
						$post_data                = array();
						$post_data['ID']          = $this->_imported_post_id( $original_post_id );
						$post_data['post_parent'] = $this->_imported_post_id( $original_post_parent_id );
						wp_update_post( $post_data );
						$this->_post_orphans( $original_post_id, 0 ); // ignore future
					}
				}
			}
		}

		private function _handle_delayed_posts( $last_delay = false ) {

			$this->log( ' ---- Processing ' . count( $this->delay_posts, COUNT_RECURSIVE ) . ' delayed posts' );
			for ( $x = 1; $x < 4; $x ++ ) {
				foreach ( $this->delay_posts as $delayed_post_type => $delayed_post_datas ) {
					foreach ( $delayed_post_datas as $delayed_post_id => $delayed_post_data ) {
						if ( $this->_imported_post_id( $delayed_post_data['post_id'] ) ) {
							$this->log( $x . ' - Successfully processed ' . $delayed_post_type . ' ID ' . $delayed_post_data['post_id'] . ' previously.' );
							unset( $this->delay_posts[ $delayed_post_type ][ $delayed_post_id ] );
							$this->log( ' ( ' . count( $this->delay_posts, COUNT_RECURSIVE ) . ' delayed posts remain ) ' );
						} else if ( $this->_process_post_data( $delayed_post_type, $delayed_post_data, $last_delay ) ) {
							$this->log( $x . ' - Successfully found delayed replacement for ' . $delayed_post_type . ' ID ' . $delayed_post_data['post_id'] . '.' );
							// successfully inserted! don't try again.
							unset( $this->delay_posts[ $delayed_post_type ][ $delayed_post_id ] );
							$this->log( ' ( ' . count( $this->delay_posts, COUNT_RECURSIVE ) . ' delayed posts remain ) ' );
						}
					}
				}
			}
		}

		private function _fetch_remote_file( $url, $post ) {
			// extract the file name and extension from the url
			$file_name  = basename( $url );
			$upload     = false;

			if ( ! $upload || $upload['error'] ) {
				// get placeholder file in the upload dir with a unique, sanitized filename
				$upload = wp_upload_bits( $file_name, 0, '', $post['upload_date'] );
				if ( $upload['error'] ) {
					return new WP_Error( 'upload_dir_error', $upload['error'] );
				}

				$max_size = (int) apply_filters( 'import_attachment_size_limit', 0 );

				if ( empty( $this->debug ) ) {

					//Change to Uploaded file path if uploaded
					$path = get_option('micronet_export_import_content_path');
					if( !empty($path) ){
						$vibe_url = site_url().'/wp-content/uploads/upload_demos/'.basename($path).'/images/'.$file_name;
					}
				}

				$response = wp_remote_get( $vibe_url ,array('timeout' => 120));
				if ( is_array( $response ) && ! empty( $response['body'] ) && $response['response']['code'] == '200' ) {
					//
				}else{
					$local_file = trailingslashit( get_template_directory() ) . 'assets/images/title_bg.png';
					
					if ( is_file( $local_file ) && filesize( $local_file ) > 0 ) {
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
						WP_Filesystem();
						global $wp_filesystem;
						$file_data = $wp_filesystem->get_contents( $local_file );
						$upload    = wp_upload_bits( $file_name, 0, $file_data, $post['upload_date'] );
						if ( $upload['error'] ) {
							return new WP_Error( 'upload_dir_error', $upload['error'] );
						}
					}
				}

				if ( is_array( $response ) && ! empty( $response['body'] ) && $response['response']['code'] == '200' ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					$headers = $response['headers'];
					WP_Filesystem();
					global $wp_filesystem;
					$wp_filesystem->put_contents( $upload['file'], $response['body'] );
					//
				} else {
					// required to download file failed.
					@unlink( $upload['file'] );

					return new WP_Error( 'import_file_error', esc_html__( 'Remote server did not respond','micronet' ) );
				}

				$filesize = filesize( $upload['file'] );

				if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
					@unlink( $upload['file'] );

					return new WP_Error( 'import_file_error', esc_html__( 'Remote file is incorrect size','micronet' ) );
				}

				if ( 0 == $filesize ) {
					@unlink( $upload['file'] );

					return new WP_Error( 'import_file_error', esc_html__( 'Zero size file downloaded','micronet' ) );
				}

				if ( ! empty( $max_size ) && $filesize > $max_size ) {
					@unlink( $upload['file'] );

					return new WP_Error( 'import_file_error', sprintf( esc_html__( 'Remote file is too large, limit is %s','micronet' ), size_format( $max_size ) ) );
				}
			}

			// keep track of the old and new urls so we can substitute them later
			$this->_imported_post_id( $url, $upload['url'] );
			$this->_imported_post_id( $post['guid'], $upload['url'] );
			// keep track of the destination if the remote url is redirected somewhere else
			if ( isset( $headers['x-final-location'] ) && $headers['x-final-location'] != $url ) {
				$this->_imported_post_id( $headers['x-final-location'], $upload['url'] );
			}

			return $upload;
		}

		public function _content_install_widgets() {
			// todo: pump these out into the 'content/' folder along with the XML so it's a little nicer to play with
			$import_widget_positions = $this->_get_json( 'widget_positions.json' );
			$import_widget_options   = $this->_get_json( 'widget_options.json' );

			// importing.
			$widget_positions = get_option( 'sidebars_widgets' );
			if ( ! is_array( $widget_positions ) ) {
				$widget_positions = array();
			}

			foreach ( $import_widget_options as $widget_name => $widget_options ) {
				// replace certain elements with updated imported entries.
				foreach ( $widget_options as $widget_option_id => $widget_option ) {

					// replace TERM ids in widget settings.
					foreach ( array( 'nav_menu' ) as $key_to_replace ) {
						if ( ! empty( $widget_option[ $key_to_replace ] ) ) {
							// check if this one has been imported yet.
							$new_id = $this->_imported_term_id( $widget_option[ $key_to_replace ] );
							if ( ! $new_id ) {
								// do we really clear this out? nah. well. maybe.. hmm.
							} else {
								$widget_options[ $widget_option_id ][ $key_to_replace ] = $new_id;
							}
						}
					}
					// replace POST ids in widget settings.
					foreach ( array( 'image_id', 'post_id' ) as $key_to_replace ) {
						if ( ! empty( $widget_option[ $key_to_replace ] ) ) {
							// check if this one has been imported yet.
							$new_id = $this->_imported_post_id( $widget_option[ $key_to_replace ] );
							if ( ! $new_id ) {
								// do we really clear this out? nah. well. maybe.. hmm.
							} else {
								$widget_options[ $widget_option_id ][ $key_to_replace ] = $new_id;
							}
						}
					}
				}
				$existing_options = get_option( 'widget_' . $widget_name, array() );
				if ( ! is_array( $existing_options ) ) {
					$existing_options = array();
				}
				$new_options = $existing_options + $widget_options;
				update_option( 'widget_' . $widget_name, $new_options );
			}
			update_option( 'sidebars_widgets', array_merge( $widget_positions, $import_widget_positions ) );

			return true;

		}

		public function _content_options_settings(){

			$this->logs[] = 'inside options panel';
			$custom_options = $this->_get_json( 'options.json' );

			foreach ( $custom_options as $option => $value ) {
				if($option == 'micronet' ){
					$ops = get_option($option);
					if(empty($ops) || !is_array($ops)){$ops = array();}
					foreach($value as $key => $val){
						$ops[$key] = $val;
					}

					update_option( $option, $ops );

					break;
				}

				if($option == 'vibe_appointments' ){
					$this->logs[] = 'inside vibe appointments options panel';
					$ops = get_option($option);
					if(empty($ops) || !is_array($ops)){$ops = array();}
					foreach($value as $key => $val){
						switch($key){
							case 'instructor_video_field':
								//find promo field if
								global $wpdb,$bp;
        						$field_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$bp->profile->table_name} WHERE name = %s", 'Promo' ) );
        						if(!empty($field_id)){
        							$val = $field_id;	
        						}
        						
							break;
							case 'directory_member_card':
								global $wpdb,$bp;
								$card_id = get_page_by_title('Basic','member-card');
        						if(!empty($card_id)){
        							$val = $card_id;	
        						}
							break;
							case 'appointments_directory_page':
							
								global $wpdb,$bp;
        						$page_id = get_page_by_title( 'Find your tutor' );
        						if(!empty($page_id)){
        							$val = $page_id;	
        						}
							break;
						}
						$ops[$key] = $val;
					}
					update_option( $option, $ops );

				}

				
				
			}

			return true;
		}

		public function _content_install_customizer(){

			$this->logs[] = 'inside customizer settings';
			
      		$custom_options = $this->_get_json( 'options.json' );
      		if(!empty($custom_options)){
      			foreach ( $custom_options as $option => $value ) {
					if($option == 'vibebp_customizer' ){
						$ops = get_option($option);
						if(empty($ops) || !is_array($ops)){$ops = array();}
						foreach($value as $key => $val){
							$ops[$key] = $val;
						}

						update_option( $option, $ops );

						break;
					}
				}
      		}
            
			return true;

		}

		public function _content_install_settings() {

			$this->_handle_delayed_posts( true ); // final wrap up of delayed posts.
			$this->vc_post(); // final wrap of vc posts.
			$this->logs[] = 'inside settings';
			$custom_options = $this->_get_json( 'options.json' );

			// we also want to update the widget area manager options.
			foreach ( $custom_options as $option => $value ) {
				// we have to update widget page numbers with imported page numbers.
				if (
					preg_match( '#(wam__position_)(\d+)_#', $option, $matches ) ||
					preg_match( '#(wam__area_)(\d+)_#', $option, $matches )
				) {
					$new_page_id = $this->_imported_post_id( $matches[2] );
					if ( $new_page_id ) {
						// we have a new page id for this one. import the new setting value.
						$option = str_replace( $matches[1] . $matches[2] . '_', $matches[1] . $new_page_id . '_', $option );
					}
				}
			}

			
			$style = vibe_get_site_style();
			if(empty($style)){$style = $this->get_default_theme_style();}

            $locations = get_theme_mod('nav_menu_locations');

            $loggedin_menu = wp_get_nav_menu_object( 'VibeBP Loggedin Menu' );
            if(isset($loggedin_menu->term_id))
            	$locations['loggedin']=$loggedin_menu->term_id;
            $profile_menu = wp_get_nav_menu_object( 'VibeBP Profile Menu');

            if(isset($profile_menu->term_id))
            	$locations['profile']=$profile_menu->term_id;
            
            // $primary_menu = wp_get_nav_menu_object( 'primary');
            // if(isset($primary_menu->term_id))
            // 	$locations['primary'] = $primary_menu->term_id;

            set_theme_mod( 'nav_menu_locations', $locations );


            

			
			
			$homepage = get_page_by_title( 'Home' );
			if ( $homepage ) { 
				update_option( 'page_on_front', $homepage->ID );
				update_option( 'show_on_front', 'page' );
				update_post_meta($homepage->ID,'_wp_page_template','no_sidebar.php');
			}

			// $blogpage = get_page_by_title( 'Blog' );
			// if ( $blogpage ) {
			// 	update_option( 'page_for_posts', $blogpage->ID );
			// 	update_option( 'show_on_front', 'page' );
			// }

			$post_ids = get_transient( 'importpostids' );
			
			if(!empty($post_ids)){
				
			}

			//die here
			update_option('vibebp_setup_complete',1);
			update_option('_bp_theme_package_id','vibebp');
			global $wp_rewrite;
			$wp_rewrite->set_permalink_structure( '/%postname%/' );
			update_option( 'rewrite_rules', false );
			$wp_rewrite->flush_rules( true );

			return true;
		}

		function _content_install_slider(){

			$style = vibe_get_site_style();


			return true;
		}


		public function _get_json( $file ) {

			//Change to Uploaded file path if uploaded
			$path = get_option('micronet_export_import_content_path');

			if( !empty($path) ){
				$style = basename($path);
				$theme_style = $path.'/';

			}else{
				$style = vibe_get_site_style();
				
				if(empty($style)){$style = $this->get_default_theme_style();}

				if(!empty($_GET['force'])){
					$theme_style = 'https://micronet.s3.amazonaws.com/demos/' . basename($style) .'/';	
				}else{
					$theme_style = 'https://micronet.work/demodata/' . basename($style) .'/';
					
				}
				

				//$theme_style = __DIR__ . '/content/' . basename($style) .'/';
			}

			if($file == 'default.json'){
				//echo '<a href="https://micronet.s3.amazonaws.com/demos/' . basename($style) .'/default.json">Direct link to Import file</a>';
			}
			

			if(!empty($_GET['capture']) && current_user_can('manage_options')){
				$theme_style = $_GET['capture'];
			}
			//$theme_style = 'http://localhost/vibebp/instructor/';
            if($file == 'options.json'){
                
                $file_name = $theme_style . basename( $file );  
                
                $loaded = get_transient($style.'_'.$file);
                if(empty($loaded)) {
                	$request = wp_remote_get($file_name,array('timeout' => 320));	
                	if( !is_wp_error( $request ) ) {
						$loaded = json_decode(wp_remote_retrieve_body($request), true );
						set_transient($style.'_'.$file,$loaded,HOUR_IN_SECONDS);
						return $loaded;
					}
                }
                
            }



        	$file_name = $theme_style . basename( $file );   

        	$loaded = get_transient($style.'_'.$file);
        	
            if(empty($loaded)) {
            	if(wp_http_validate_url($file_name)){
            		$request = wp_remote_get($file_name);	
            		
	            	if( !is_wp_error( $request ) ) {
	            		$request = wp_remote_get(esc_url_raw($file_name),array('timeout' => 600));
	          
			            if( !is_wp_error( $request ) ) {
							$loaded = json_decode(wp_remote_retrieve_body($request), true );
							set_transient($style.'_'.$file,$loaded,HOUR_IN_SECONDS);
							return $loaded;
						}
					}
            	}else if(file_exists($file_name)){
            		
            		//$content = file_get_contents( $file_name);
        			require_once( ABSPATH . 'wp-admin/includes/file.php' );
            		WP_Filesystem();
					global $wp_filesystem;
					$content = $wp_filesystem->get_contents( $file_name );
					
            		$loaded = json_decode($content, true );
					set_transient($style.'_'.$file,$loaded,HOUR_IN_SECONDS);
					return $loaded;
            	}
            	
            }else{
            	return $loaded;
            }
            
            
            return array();
        }
        

		public function _content_setup_users(){

			$current_style = vibe_get_site_style();
			//setup users
			
			$style = vibe_get_site_style();
			if(empty($style)){$style = $this->get_default_theme_style();}
			
			$theme_style = 'https://micronet.s3.amazonaws.com/demos/' . basename($style) .'/';
			if(!empty($_GET['capture']) && current_user_can('manage_options')){
				$theme_style = $_GET['capture'];
			}

			if(!empty($_GET['force'])){
				$theme_style = 'https://wpappointify.com/micronet/demodata/' . basename($style)  .'/';
			}




			$users = $this->_get_json($theme_style.'users.json'); 

			
			// we also want to update the widget area manager options.
			foreach ( $users as $user ) {
				
				$uid = email_exists($user['user_email']);
				if(empty($uid)){
					$uid = wp_insert_user([
						'user_login'=>$user['user_login'],
						'user_pass'=>$user['user_pass'],
						'user_email'=>$user['user_email'],
						'display_name'=>$user['display_name'],
					]);	
				}
				
				if(is_numeric($uid)){
					bp_set_member_type($uid,'instructor');
					bp_update_user_last_activity( $uid, time() );


					if(!empty($user['fields'])){
						
						//remove_filter( 'xprofile_data_value_before_save','xprofile_sanitize_data_value_before_save', 1, 4 );
						foreach($user['fields'] as $field){
							
							xprofile_set_field_data($field['name'],$uid,$field['value']);

							if(!empty($field['meta']) && is_Array($field['meta'])){
								foreach($field['meta'] as $k=>$v){
									bp_xprofile_update_field_meta($field['id'], $k,  wp_filter_nohtml_kses($v));
								}
							}
						}
					}

					global $wp_filesystem;
					if(!$wp_filesystem->exists(WP_CONTENT_DIR.'/uploads/avatars')){
						wp_mkdir_p(  WP_CONTENT_DIR.'/uploads/avatars' );
					}
			    	if(!$wp_filesystem->exists(WP_CONTENT_DIR.'/uploads/avatars/'.$uid)){

			    		$create = 0;
			    		if(!$wp_filesystem->exists(WP_CONTENT_DIR.'/uploads/avatars/'.$uid)){
			    			$create =  wp_mkdir_p(WP_CONTENT_DIR.'/uploads/avatars/'.$uid);
			    		}else{
			    			$create=1;
			    		}

			    		if($create){
			    			if($wp_filesystem->exists(MICRONET_PATH.'/setup/installer/images/'.$uid)){
				    			copy_dir( MICRONET_PATH.'/setup/installer/images/'.$uid, WP_CONTENT_DIR.'/uploads/avatars/'.$uid );
				    		}else{
				    			copy_dir( MICRONET_PATH.'/setup/installer/images/n', WP_CONTENT_DIR.'/uploads/avatars/'.$uid );
				    		}
			    		}
			    	}
				}


		        if(class_exists('VIBE_APPOINTMENTS_DB')){
		        	$appointments_db = new VIBE_APPOINTMENTS_DB;
		            
                    $start_timestamp = (time()+3600)*1000;
                    $end_timestamp =(time()+2*3600)*1000;;
                    $args = array(
                        'start_date'        => $start_timestamp,
                        'end_date'          => $end_timestamp,
                        'start_time'        => (date('H',round($start_timestamp/1000))*60)+date('i',round($start_timestamp/1000)),
                        'end_time'          => (date('H',round($end_timestamp/1000))*60)+date('i',round($end_timestamp/1000)),
                        'author_id'         => $uid,
                        'type'              => 'available',
                        'item_id'           => 0,
                        'status'            => 'open',
                    );
                    
                    
                    $appointment_id = $appointments_db->insert_appointment($args);

	            }
	        }
			return true;
		}

		public $logs = array();

		public function log( $message ) {
			$this->logs[] = $message;
		}

		public $errors = array();

		public function error( $message ) {
			$this->logs[] = 'ERROR!!!! ' . $message;
		}

		public function envato_setup_demo_style() {

			$installation_type = '';
			if(!empty($_GET['installation_type'])){
				$installation_type=$_GET['installation_type'];
				if($installation_type == 'gutenberg'){
					micronet_update_option('theme_type',1);
				}
			}
			?>
            <h1><?php esc_html_e( 'Theme Style','micronet' ); ?></h1>
            <form method="post">
                <p>'Please click on theme style to select the style for your site from below options. You can switch or mix and match demos post setup as well using the demo switcher.</p>

                <div class="theme-presets">
                    <ul>
	                    <?php

	                    $current_style = vibe_get_site_style();
	                    
						if(empty($current_style)){$current_style = $this->get_default_theme_style();}
	                    foreach ( $this->site_styles as $style_name => $style_data ) {

	                    	if(empty($installation_type) || (!empty($style_data) && in_array($installation_type,$style_data['installation_type']))){
		                    ?>
                            <li<?php echo vibe_sanitizer($style_name == $current_style ? ' class="current" ' : ''); ?>>
                                <a href="#" class="sitestyle" data-style="<?php echo esc_attr( $style_name ); ?>">
                                	<img
                                            src="<?php echo esc_url($style_data['src']);?>"></a><a href="<?php echo vibe_sanitizer($style_data['link'],'url'); ?>" target="_blank" class="link"></a>
                            </li>
	                    <?php 
	                    	}
	                	} ?>
                    </ul>
                </div>

                <input type="hidden" name="demo_style" id="demo_style" value="<?php echo vibe_sanitizer($current_style,'text'); ?>">

                <div class="envato-setup-actions step">
                	
                    <input type="submit" class="large_next_button button-next"
                           value="<?php _e( 'Continue','micronet' ); ?>" name="save_step"/>
                    <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
                       class="large_skip_button"><?php esc_html_e( 'Skip this step','micronet' ); ?></a>
                   

					<?php wp_nonce_field( 'envato-setup' ); ?>
                </div>
                <p><em>Please Note: Advanced changes to website graphics/colors may require extensive Design & Web
                        Development knowledge. We recommend hiring an expert from <a
                                href="https://micronet.work/contact"
                                target="_blank">Micronet Contact</a> to assist with any advanced website changes.</em></p>
            </form>
			<?php
		}

		/**
		 * Save logo & design options
		 */
		public function envato_setup_demo_style_save($demo=null) {
			if(empty($demo)){
				check_admin_referer( 'envato-setup' );
			}

			$demo_style = isset( $_POST['demo_style'] ) ? $_POST['demo_style'] : false;
			
			

			if(!empty($demo)){
				$demo_style=$demo;
			}
			if ( $demo_style ) {
				update_option( 'micronet_site_style', $demo_style );
				//switch case to be added to download respective demo style templates
				if($demo_style && micronet_get_option('theme_type')){
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					WP_Filesystem();

					global $wp_filesystem;
			    	if($theme_type == 1 && !$wp_filesystem->exists(MICRONET_PATH.'/templates')){
						$wp_filesystem->move(MICRONET_PATH.'/gutenberg_templates',MICRONET_PATH.'/templates');
						$wp_filesystem->move(MICRONET_PATH.'/gutenberg_templates/theme.json',MICRONET_PATH.'/theme.json');
			    	}
				}
			}
			
			if(empty($demo)){
				wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
				exit;
			}

			
			
		}

		/**
		 * Logo & Design
		 */
		public function envato_setup_design() {
			/*Delete option for uploaded content to avoid conflicts when setup wizard runs again*/
			delete_option( 'micronet_export_import_content_path' );

			?>
			<h1><?php esc_html_e( 'Design and Layouts','micronet' ); ?></h1>
			<form method="post">
				<p><?php printf( esc_html__( 'Please add your logo below. For best results, the logo should be a transparent PNG ( 466 by 277 pixels). The logo can be changed at any time from the Appearance > Customize area in your dashboard. Try %sEnvato Studio%s if you need a new logo designed.','micronet' ), '<a href="http://studiotracking.envato.com/aff_c?offer_id=4&aff_id=1564&source=DemoInstall" target="_blank">', '</a>' ); ?></p>

				<table>
					<tr>
						<td>
							LOGO
						</td>
						<td>
							<div id="micronet-logo">
								<?php
								$image_url = vibe_get_option('logo');
								if(empty($image_url)){
									$image_url = MICRONET_URL.'/images/logo_black.png';
								}
								
								if ( $image_url ) {
									$image = '<img class="site-logo" style="max-width:466px;" id="current-logo" src="%s" alt="%s" />';
									printf(
										$image,
										$image_url,
										get_bloginfo( 'name' )
									);
								} ?>
							</div>
							<input type="hidden" name="logo_url" id="logo_url" value="<?php echo vibe_sanitizer($image_url,'url'); ?>">
						</td>
						<td>
							<a href="#" class="button button-upload" data-title="Upload a logo" data-text="select a logo" data-target="#current-logo" data-save="#logo_url"><?php esc_html_e( 'Upload New Logo' ,'micronet'); ?></a>
						</td>
					</tr>
					<tr>
						<td>
							Theme Skin
						</td>
						<?php
							$theme_skin = micronet_get_option('theme_skin');
						?>
						<td>
							<select name="theme_skin">
								<option value="" <?php echo (empty($theme_skin)?'selected':''); ?>>Default</option>
								<option value="minimal"  <?php echo (($theme_skin == 'minimal')?'selected':''); ?>>Minimal</option>								
							</select>
						</td>
					</tr>
					<tr>
						<td>
							Primary color
							<?php 
							$primary_bg=micronet_get_option('bg-primary');
							if(Empty($primary_bg)){$primary_bg= '#009dd8';}
							?>
						</td>
						<td>
							<input id="primary_bg" class="jscolor {hash:true}" name="primary_bg" type="text" value="<?php echo vibe_sanitizer($primary_bg); ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Primary text color
							<?php 
							$primary_color=micronet_get_option('primary-color');
							if(Empty($primary_color)){$primary_color= '#ffffff';}
							?>
						</td>
						<td>
							<input id="primary_color" class="jscolor {hash:true}" name="primary_color" type="text" value="#ffffff" />
						</td>
					</tr>
				</table>
				<br>
				<hr>
				<p><em>Please Note: micronet has live support at Facebook.com/VibeThemes. Also a free installation service at <a
							href="https://wpappointify.com/micronet/app/"
							target="_blank">micronet Support</a>.</em></p>
				

				<div class="envato-setup-actions step">
					<input type="submit" class="large_next_button button-next"
					       value="<?php _e( 'Continue','micronet' ); ?>" name="save_step"/>
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class=" button-next"><?php esc_html_e( 'Skip this step','micronet' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
				</div>
			</form>
			<?php
		}

		/**
		 * Save logo & design options
		 */
		public function envato_setup_design_save($theme=null) {
			if(empty($theme)){
				check_admin_referer( 'envato-setup' );
			}

			$logo_url = esc_url($_POST['logo_url']);
			if(!empty($logo_url)){
				set_theme_mod('custom_logo',attachment_url_to_postid($logo_url));
			}
			

			$theme_skin =  isset( $_POST['theme_skin'] ) ? esc_attr($_POST['theme_skin']) : false;
			if(!empty($theme)){
				$theme_skin = $theme;
			}
			if ( $theme_skin ) {
				micronet_update_option('theme_skin',$theme_skin);

			}
			$primary_bg = isset( $_POST['primary_bg'] ) ? sanitize_hex_color($_POST['primary_bg']) : false;
			if ( $primary_bg ) {
				micronet_update_option('bg-primary',$primary_bg);
			}

			$primary_color = isset( $_POST['primary_color'] ) ? sanitize_hex_color($_POST['primary_color']) : false;
			if ( $primary_color ) {
				micronet_update_option('primary-color',$primary_color);
			}

			do_action('micronet_envato_setup_design_save',$theme);
			if(class_exists('Elementor\Plugin')){
				Elementor\Plugin::$instance->files_manager->clear_cache();
			}
			$this->handle_demo_adjustments();

			if(empty($theme)){
				wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
				exit;
			}


		}
		
		/**
		 * Final step
		 */
		public function envato_setup_ready() {

			update_option( 'envato_setup_complete', time() );
			?>
			<a href="https://twitter.com/share" class="twitter-share-button"
			   data-url="http://themeforest.net/user/vibethemes/portfolio?ref=vibethemes"
			   data-text="<?php echo esc_attr( 'I just installed the ' . wp_get_theme() . ' #WordPress theme from #ThemeForest' ); ?>"
			   data-via="EnvatoMarket" data-size="large">Tweet</a>
			<script>!function (d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0];
					if (!d.getElementById(id)) {
						js = d.createElement(s);
						js.id = id;
						js.src = "//platform.twitter.com/widgets.js";
						fjs.parentNode.insertBefore(js, fjs);
					}
				}(document, "script", "twitter-wjs");</script>

			<h1><?php esc_html_e( 'Your Website is Ready!','micronet' ); ?></h1>

			<p>Congratulations! The theme has been activated and your website is ready. Login to your WordPress
				dashboard to make changes and modify any of the default content to suit your needs.</p>
			<p>Please come back and <a href="http://themeforest.net/downloads" target="_blank">leave a 5-star rating</a>
				if you are happy with this theme. <br/>Follow <a href="https://twitter.com/vibethemes" target="_blank">@vibethemes</a>
				on Twitter to see updates. Thanks! </p>
			<?php flush_rewrite_rules(); ?>
			<div class="envato-setup-next-steps">
				<div class="envato-setup-next-steps-first">
					<h2><?php esc_html_e( 'Next Steps','micronet' ); ?></h2>
					<ul>
						<!--li class="setup-product"><a class="button button-primary button-large" style="color:#fff;" href="#">Watch Post Setup Configuration <br>[ Recommended Video ]
	                         </a>
						</li-->
						<li class="setup-product"><a class="button button-next button-large"
						                             href="<?php echo esc_url( home_url() ); ?>"><?php esc_html_e( 'View your new website!','micronet' ); ?></a>
						</li>
					</ul>
				</div>
				<div class="envato-setup-next-steps-last">
					<h2><?php esc_html_e( 'More Resources','micronet' ); ?></h2>
					<ul>
						<li class="documentation"><a href="https://micronetdocs.wpappointify.com/"
						                             target="_blank"><?php esc_html_e( 'Read the Theme Documentation','micronet' ); ?></a>
						</li>

						<li class="howto"><a href="https://wordpress.org/support/"
						                     target="_blank"><?php esc_html_e( 'Learn how to use WordPress','micronet' ); ?></a>
						</li>
						<li class="rating"><a href="http://themeforest.net/downloads"
						                      target="_blank"><?php esc_html_e( 'Leave an Item Rating','micronet' ); ?></a></li>
						<li class="support"><a href="https://wpappointify.com/micronet/app//"
						                       target="_blank"><?php esc_html_e( 'Get Help and Support','micronet' ); ?></a></li>
					</ul>
				</div>
			</div>
			<?php
		}

		public function envato_market_admin_init() {

			if ( ! function_exists( 'envato_market' ) ) {
				return;
			}

			global $wp_settings_sections;
			if ( ! isset( $wp_settings_sections[ envato_market()->get_slug() ] ) ) {
				// means we're running the admin_init hook before envato market gets to setup settings area.
				// good - this means our oauth prompt will appear first in the list of settings blocks
				register_setting( envato_market()->get_slug(), envato_market()->get_option_name() );
			}

			// pull our custom options across to envato.
			$option         = get_option( 'envato_setup_wizard', array() );
			$envato_options = envato_market()->get_options();
			$envato_options = $this->_array_merge_recursive_distinct( $envato_options, $option );
			update_option( envato_market()->get_option_name(), $envato_options );

			//add_thickbox();

			if ( ! empty( $_POST['oauth_session'] ) && ! empty( $_POST['bounce_nonce'] ) && wp_verify_nonce( $_POST['bounce_nonce'], 'envato_oauth_bounce_' . $this->envato_username ) ) {
				// request the token from our bounce url.
				$my_theme    = wp_get_theme();
				$oauth_nonce = get_option( 'envato_oauth_' . $this->envato_username );
				if ( ! $oauth_nonce ) {
					// this is our 'private key' that is used to request a token from our api bounce server.
					// only hosts with this key are allowed to request a token and a refresh token
					// the first time this key is used, it is set and locked on the server.
					$oauth_nonce = wp_create_nonce( 'envato_oauth_nonce_' . $this->envato_username );
					update_option( 'envato_oauth_' . $this->envato_username, $oauth_nonce );
				}
				$response = wp_remote_post( $this->oauth_script, array(
						'method'      => 'POST',
						'timeout'     => 15,
						'redirection' => 1,
						'httpversion' => '1.0',
						'blocking'    => true,
						'headers'     => array(),
						'body'        => array(
							'oauth_session' => $_POST['oauth_session'],
							'oauth_nonce'   => $oauth_nonce,
							'get_token'     => 'yes',
							'url'           => home_url(),
							'theme'         => $my_theme->get( 'Name' ),
							'version'       => $my_theme->get( 'Version' ),
						),
						'cookies'     => array(),
					)
				);
				if ( is_wp_error( $response ) ) {
					$error_message = $response->get_error_message();
					$class         = 'error';
					echo "<div class=\"$class\"><p>" . sprintf( esc_html__( 'Something went wrong while trying to retrieve oauth token: %s' ,'micronet'), $error_message ) . '</p></div>';
				} else {
					$token  = @json_decode( wp_remote_retrieve_body( $response ), true );
					$result = false;
					if ( is_array( $token ) && ! empty( $token['access_token'] ) ) {
						$token['oauth_session'] = $_POST['oauth_session'];
						$result                 = $this->_manage_oauth_token( $token );
					}
					if ( $result !== true ) {
						echo 'Failed to get oAuth token. Please go back and try again';
						exit;
					}
				}
			}

			add_settings_section(
				envato_market()->get_option_name() . '_' . $this->envato_username . '_oauth_login',
				sprintf( esc_html__( 'Login for %s updates','micronet' ), $this->envato_username ),
				array( $this, 'render_oauth_login_description_callback' ),
				envato_market()->get_slug()
			);
			// Items setting.
			add_settings_field(
				$this->envato_username . 'oauth_keys',
				esc_html__( 'oAuth Login','micronet' ),
				array( $this, 'render_oauth_login_fields_callback' ),
				envato_market()->get_slug(),
				envato_market()->get_option_name() . '_' . $this->envato_username . '_oauth_login'
			);
		}

		private static $_current_manage_token = false;

		private function _manage_oauth_token( $token ) {
			if ( is_array( $token ) && ! empty( $token['access_token'] ) ) {
				if ( self::$_current_manage_token == $token['access_token'] ) {
					return false; // stop loops when refresh auth fails.
				}
				self::$_current_manage_token = $token['access_token'];
				// yes! we have an access token. store this in our options so we can get a list of items using it.
				$option = get_option( 'envato_setup_wizard', array() );
				if ( ! is_array( $option ) ) {
					$option = array();
				}
				if ( empty( $option['items'] ) ) {
					$option['items'] = array();
				}
				// check if token is expired.
				if ( empty( $token['expires'] ) ) {
					$token['expires'] = time() + 3600;
				}
				if ( $token['expires'] < time() + 120 && ! empty( $token['oauth_session'] ) ) {
					// time to renew this token!
					$my_theme    = wp_get_theme();
					$oauth_nonce = get_option( 'envato_oauth_' . $this->envato_username );
					$response    = wp_remote_post( $this->oauth_script, array(
							'method'      => 'POST',
							'timeout'     => 10,
							'redirection' => 1,
							'httpversion' => '1.0',
							'blocking'    => true,
							'headers'     => array(),
							'body'        => array(
								'oauth_session' => $token['oauth_session'],
								'oauth_nonce'   => $oauth_nonce,
								'refresh_token' => 'yes',
								'url'           => home_url(),
								'theme'         => $my_theme->get( 'Name' ),
								'version'       => $my_theme->get( 'Version' ),
							),
							'cookies'     => array(),
						)
					);
					if ( is_wp_error( $response ) ) {
						$error_message = $response->get_error_message();
						echo "Something went wrong while trying to retrieve oauth token: $error_message";
					} else {
						$new_token = @json_decode( wp_remote_retrieve_body( $response ), true );
						$result    = false;
						if ( is_array( $new_token ) && ! empty( $new_token['new_token'] ) ) {
							$token['access_token'] = $new_token['new_token'];
							$token['expires']      = time() + 3600;
						}
					}
				}
				// use this token to get a list of purchased items
				// add this to our items array.
				$response                    = envato_market()->api()->request( 'https://api.envato.com/v3/market/buyer/purchases', array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $token['access_token'],
					),
				) );
				self::$_current_manage_token = false;
				if ( is_array( $response ) && is_array( $response['purchases'] ) ) {
					// up to here, add to items array
					foreach ( $response['purchases'] as $purchase ) {
						// check if this item already exists in the items array.
						$exists = false;
						foreach ( $option['items'] as $id => $item ) {
							if ( ! empty( $item['id'] ) && $item['id'] == $purchase['item']['id'] ) {
								$exists = true;
								// update token.
								$option['items'][ $id ]['token']      = $token['access_token'];
								$option['items'][ $id ]['token_data'] = $token;
								$option['items'][ $id ]['oauth']      = $this->envato_username;
								if ( ! empty( $purchase['code'] ) ) {
									$option['items'][ $id ]['purchase_code'] = $purchase['code'];
								}
							}
						}
						if ( ! $exists ) {
							$option['items'][] = array(
								'id'            => '' . $purchase['item']['id'],
								// item id needs to be a string for market download to work correctly.
								'name'          => $purchase['item']['name'],
								'token'         => $token['access_token'],
								'token_data'    => $token,
								'oauth'         => $this->envato_username,
								'type'          => ! empty( $purchase['item']['wordpress_theme_metadata'] ) ? 'theme' : 'plugin',
								'purchase_code' => ! empty( $purchase['code'] ) ? $purchase['code'] : '',
							);
						}
					}
				} else {
					return false;
				}
				if ( ! isset( $option['oauth'] ) ) {
					$option['oauth'] = array();
				}
				// store our 1 hour long token here. we can refresh this token when it comes time to use it again (i.e. during an update)
				$option['oauth'][ $this->envato_username ] = $token;
				update_option( 'envato_setup_wizard', $option );

				$envato_options = envato_market()->get_options();
				$envato_options = $this->_array_merge_recursive_distinct( $envato_options, $option );
				update_option( envato_market()->get_option_name(), $envato_options );
				envato_market()->items()->set_themes( true );
				envato_market()->items()->set_plugins( true );

				return true;
			} else {
				return false;
			}
		}

		/**
		 * @param $array1
		 * @param $array2
		 *
		 * @return mixed
		 *
		 *
		 * @since    1.1.4
		 */
		private function _array_merge_recursive_distinct( $array1, $array2 ) {
			$merged = $array1;
			foreach ( $array2 as $key => &$value ) {
				if ( is_array( $value ) && isset( $merged [ $key ] ) && is_array( $merged [ $key ] ) ) {
					$merged [ $key ] = $this->_array_merge_recursive_distinct( $merged [ $key ], $value );
				} else {
					$merged [ $key ] = $value;
				}
			}

			return $merged;
		}

		/**
		 * @param $args
		 * @param $url
		 *
		 * @return mixed
		 *
		 * Filter the WordPress HTTP call args.
		 * We do this to find any queries that are using an expired token from an oAuth bounce login.
		 * Since these oAuth tokens only last 1 hour we have to hit up our server again for a refresh of that token before using it on the Envato API.
		 * Hacky, but only way to do it.
		 */
		public function envato_market_http_request_args( $args, $url ) {
			if ( strpos( $url, 'api.envato.com' ) && function_exists( 'envato_market' ) ) {
				// we have an API request.
				// check if it's using an expired token.
				if ( ! empty( $args['headers']['Authorization'] ) ) {
					$token = str_replace( 'Bearer ', '', $args['headers']['Authorization'] );
					if ( $token ) {
						// check our options for a list of active oauth tokens and see if one matches, for this envato username.
						$option = envato_market()->get_options();
						if ( $option && ! empty( $option['oauth'][ $this->envato_username ] ) && $option['oauth'][ $this->envato_username ]['access_token'] == $token && $option['oauth'][ $this->envato_username ]['expires'] < time() + 120 ) {
							// we've found an expired token for this oauth user!
							// time to hit up our bounce server for a refresh of this token and update associated data.
							$this->_manage_oauth_token( $option['oauth'][ $this->envato_username ] );
							$updated_option = envato_market()->get_options();
							if ( $updated_option && ! empty( $updated_option['oauth'][ $this->envato_username ]['access_token'] ) ) {
								// hopefully this means we have an updated access token to deal with.
								$args['headers']['Authorization'] = 'Bearer ' . $updated_option['oauth'][ $this->envato_username ]['access_token'];
							}
						}
					}
				}
			}

			return $args;
		}

		public function render_oauth_login_description_callback() {
			echo 'If you have purchased items from ' . esc_html( $this->envato_username ) . ' on ThemeForest or CodeCanyon please login here for quick and easy updates.';

		}

		public function render_oauth_login_fields_callback() {
			$option = envato_market()->get_options();
			?>
			<div class="oauth-login" data-username="<?php echo esc_attr( $this->envato_username ); ?>">
				<a href="<?php echo esc_url( $this->get_oauth_login_url( admin_url( 'admin.php?page=' . envato_market()->get_slug() . '#settings' ) ) ); ?>"
				   class="oauth-login-button button button-primary">Login with Envato to activate updates</a>
			</div>
			<?php
		}

		/// a better filter would be on the post-option get filter for the items array.
		// we can update the token there.

		public function get_oauth_login_url( $return ) {
			return $this->oauth_script . '?bounce_nonce=' . wp_create_nonce( 'envato_oauth_bounce_' . $this->envato_username ) . '&wp_return=' . urlencode( $return );
		}

		/**
		 * Helper function
		 * Take a path and return it clean
		 *
		 * @param string $path
		 *
		 * @since    1.1.2
		 */
		public static function cleanFilePath( $path ) {
			$path = str_replace( '', '', str_replace( array( '\\', '\\\\', '//' ), '/', $path ) );
			if ( $path[ strlen( $path ) - 1 ] === '/' ) {
				$path = rtrim( $path, '/' );
			}

			return $path;
		}

		function handle_demo_adjustments($demo_style=null){
			if(empty($demo_style)){
				$demo_style = get_option( 'micronet_site_style' );
			}

			if(!empty($demo_style)){

				if($demo_style=='elementor'){
					$locations = get_theme_mod('nav_menu_locations');

					if(empty($locations['primary'])){
						$locations['primary'] = 48;
						set_theme_mod( 'nav_menu_locations', $locations );
					}
					
					

				}
			}

			if(defined('VIBE_BP_SETTINGS')){
				global $wpdb;
				$apppage = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name=%s",'app'));
				if(!empty($apppage )){
					$all_settings = get_option(VIBE_BP_SETTINGS);
					if(empty($all_settings)){
						$all_settings = array();
					}
					if(empty($all_settings['general'])){
						$all_settings['general'] = array();
					}
						
					$all_settings['general']['bp_single_page'] = $apppage;
					
					update_option(VIBE_BP_SETTINGS,$all_settings);
				}
			}
		}
	}

}// if !class_exists

/**
 * Loads the main instance of Envato_Theme_Setup_Wizard to have
 * ability extend class functionality
 *
 * @since 1.1.1
 * @return object Envato_Theme_Setup_Wizard
 */
add_action( 'after_setup_theme', 'envato_theme_setup_wizard', 10 );
if ( ! function_exists( 'envato_theme_setup_wizard' ) ) :
	function envato_theme_setup_wizard() {
		Envato_Theme_Setup_Wizard::get_instance();
	}
endif;

add_filter('micronet_theme_setup_wizard_username', 'micronet_set_theme_setup_wizard_username', 10);
if( ! function_exists('micronet_set_theme_setup_wizard_username') ){
    function micronet_set_theme_setup_wizard_username($username){
        return 'vibethemes';
    }
}
