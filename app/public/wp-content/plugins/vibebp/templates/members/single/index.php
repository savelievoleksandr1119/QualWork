<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$layout = new WP_Query(apply_filters('vibebp_public_profile_layout_query',array(
	'post_type'=>'member-profile',
	'post_name'=>bp_get_member_type(bp_displayed_user_id()),
	'posts_per_page'=>1,
)));
if ( !$layout->have_posts() ){

	$layout = new WP_Query(array(
		'post_type'=>'member-profile',
		'orderby'=>'date',
		'order'=>'ASC',
		'posts_per_page'=>1,
	));
}

if ( !$layout->have_posts() ){
	wp_die(__('Create a Member Profile layout in WP admin - VibeBp - Member Profiles','vibebp'));
}

get_header();
$member_class = 'vibebp_member';
if(!empty(vibebp_get_setting('bp_single_page'))){
	$member_class="vibebpmember";
}
?>
<div id="<?php echo $member_class; ?>">
	<div id="primary" class="content-area">
		<div class="<?php echo apply_filters('vibebp_member_profile_default_container','container'); ?>">
			<main id="user_<?php echo bp_displayed_user_id(); ?>" <?php vibebp_member_class(); ?>>
			<?php
			if ( $layout->have_posts() ) :
				
				/* Start the Loop */
				while ( $layout->have_posts() ) :
					$layout->the_post();
					
					the_content();
					if(class_exists('\Elementor\Frontend')){
						
					 	$elementorFrontend = new \Elementor\Frontend();
	                    $elementorFrontend->enqueue_scripts();
	                    $elementorFrontend->enqueue_styles();
	                }
					break;
				endwhile;
			
				
			endif;
			?>

			</main><!-- #main -->
		</div>
	</div><!-- #primary -->
</div>
<?php
get_footer();