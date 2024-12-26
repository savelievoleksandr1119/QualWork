<?php
/**
 * Board templates
 *
 * @author 		VibeThemes
 * @category 	Init
 * @package 	vibe-projects/templates/board_templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


function vibe_projects_board_templates(){

	return apply_filters('vibe_projects_board_templates',[
		[
			'id'=>'agency'
			'title'=>__('Agency Collaboration','vibe-projects'),
			'lists'=>[
				[
					'name'=>__('Upcoming work','vibe-projects')
				],
				[
					'name'=>__('Work in Progress','vibe-projects')
				],
				[
					'name'=>__('Planning','vibe-projects')
				],
			]

		]
	]);
}