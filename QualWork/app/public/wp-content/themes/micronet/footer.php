
<?php do_action( 'micronet_content_end' ); ?>

</div>

<?php do_action( 'micronet_content_after' ); ?>

<footer  class="site-footer py-12" role="contentinfo">
	<div class="container mx-auto footer_sidebar">
	<?php do_action( 'micronet_footer' ); ?>
	</div>
	<div class="container mx-auto text-center">
		&copy; <?php echo date_i18n( 'Y' );?> - <?php echo esc_html(vibe_get_option('credits'));?>
	</div>
</footer>

</div>
</div>
<?php wp_footer();  ?>
</body>
</html>
