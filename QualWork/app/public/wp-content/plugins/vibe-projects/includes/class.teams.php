<?php
/**
 * Admin Menu - Vibe Team Taxonomy
 *
 * @class       VibeProjects_TeamTaxonomy
 * @author      VibeThemes
 * @team    Admin
 * @package     VibeProjects_TeamTaxonomy
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VibeProjects_TeamTaxonomy{
	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeProjects_TeamTaxonomy();
        return self::$instance;
    }
    private function __construct(){
    }
}
