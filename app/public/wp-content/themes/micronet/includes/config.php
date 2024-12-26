<?php

if ( ! defined( 'ABSPATH' ) ) exit;

define('THEME_SHORT_NAME','micronet');
define('THEME_FULL_NAME','Micronet');
define('MICRONET_PATH',get_theme_root().'/micronet');
define('MICRONET_VERSION','1.1');
define('MICRONET_DB_VERSION','1.1'); 

define('VIBE_PATH',get_theme_root().'/micronet');
if ( !defined( 'VIBE_URL' ) )
define('VIBE_URL',get_template_directory_uri());

	
if ( !defined( 'MICRONET_URL' ) )
	define('MICRONET_URL',get_template_directory_uri());

if ( !defined( 'BP_AVATAR_THUMB_WIDTH' ) )
define( 'BP_AVATAR_THUMB_WIDTH', 150 ); 

if ( !defined( 'BP_AVATAR_THUMB_HEIGHT' ) )
define( 'BP_AVATAR_THUMB_HEIGHT', 150 ); 

if ( !defined( 'BP_AVATAR_FULL_WIDTH' ) )
define( 'BP_AVATAR_FULL_WIDTH', 460 ); 

if ( !defined( 'BP_AVATAR_FULL_HEIGHT' ) )
define( 'BP_AVATAR_FULL_HEIGHT', 460 ); 

if ( ! defined( 'BP_DEFAULT_COMPONENT' ) )
define( 'BP_DEFAULT_COMPONENT', 'profile' );
