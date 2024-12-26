<?php
/**
 * vibebp - Members Activate
 *
 * @package vibebp
 * @subpackage bp-legacy
 * @version 3.0.0
 */

?>

<div id="vibebp">

	<?php

	/**
	 * Fires before the display of the member activation page.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_before_activation_page' ); ?>

	<div class="page" id="activate-page">

		<div id="template-notices" role="alert" aria-atomic="true">
			<?php

			/** This action is documented in bp-templates/bp-legacy/vibebp/activity/index.php */
			do_action( 'template_notices' ); ?>

		</div>

		<?php

		/**
		 * Fires before the display of the member activation page content.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_before_activate_content' ); ?>

		<?php if ( bp_account_was_activated() ) : ?>

			<?php if ( isset( $_GET['e'] ) ) : ?>
				<p><?php _e( 'Your account was activated successfully! Your account details have been sent to you in a separate email.', 'vibebp' ); ?></p>
			<?php else : ?>
				<p>
					<?php
					/* translators: %s: login url */
					printf( __( 'Your account was activated successfully! You can now <a href="%s" class="vibebp-login">log in</a> with the username and password you provided when you signed up.', 'vibebp' ), '#' );
					?>
				</p>
			<?php endif; ?>

		<?php else : ?>

			<p><?php _e( 'Please provide a valid activation key.', 'vibebp' ); ?></p>

			<form action="" method="post" class="standard-form" id="activation-form">

				<label for="key"><?php _e( 'Activation Key:', 'vibebp' ); ?></label>
				<input type="text" name="key" id="key" value="<?php echo esc_attr( bp_get_current_activation_key() ); ?>" />

				<p class="submit mt-6">
					<input type="submit" name="submit" value="<?php esc_attr_e( 'Activate', 'vibebp' ); ?>" />
				</p>

			</form>

		<?php endif; ?>

		<?php

		/**
		 * Fires after the display of the member activation page content.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_after_activate_content' ); ?>

	</div><!-- .page -->

	<?php

	/**
	 * Fires after the display of the member activation page.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_after_activation_page' ); ?>

</div><!-- #vibebp -->
