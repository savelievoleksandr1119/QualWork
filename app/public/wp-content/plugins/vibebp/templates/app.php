<?php
/**
 * Template Name: VibeBP App
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
    wp_head();
?>
</head>
<body <?php body_class(); ?> id="vibeapp">
<?php
if ( have_posts() ) : while ( have_posts() ) : the_post();
    the_content();
endwhile;
endif;
?>
<?php
wp_footer(); 
?>
</body>
</html>