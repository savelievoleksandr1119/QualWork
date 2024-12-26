<?php

if ( ! defined( 'ABSPATH' ) ) exit;
class Customizer_Range_Value_Control extends \WP_Customize_Control {
	public $type = 'range-value';

	/**
	 * Enqueue scripts/styles.
	 *
	 * @since 3.4.0
	 */
	public function enqueue() {
		
	}

	public function render_content() {
		$input_id         = '_customize-input-' . $this->id;
		?>
		<label>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<div class="range-slider"  style="width:100%; display:flex;flex-direction: row;justify-content: flex-start;">
				<span  style="width:100%; flex: 1 0 0; vertical-align: middle;"><input class="range-slider__range" type="range" value="<?php echo esc_attr( $this->value() ); ?>">
				<span class="range-slider__value">0</span></span>
				<input id="<?php echo esc_attr( $input_id ); ?>" type="hidden" class="range-slider__input" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" />
			</div>
			<?php if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
			<?php endif; ?>

			
		</label>
		<?php
	}

}