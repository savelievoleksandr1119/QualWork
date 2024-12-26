<?php

if ( ! defined( 'ABSPATH' ) ) exit;
class Vibe_Customize_ImgSelect_Control extends WP_Customize_Control {
        
        public $type = 'hidden';

        public function enqueue() {
            ?>
            <style>
                ul.imgselect_choices {
                    display: grid;
                    grid-template-columns: 1fr 1fr 1fr 1fr;
                    grid-gap: 5px;
                }

                ul.imgselect_choices span.selected {
                    border: 2px solid #06cc4e;
                    display: inline-block;
                    line-height: 0;
                    box-shadow: 0 0 5px rgba(32,204,95,0.78);
                }
            </style>
            <?php
        }

        public function render_content() {
            $control = 'vibe_'.rand(0,9999999);
            $type = 'hidden';
            ?>
            <div class="vibe_customizer_<?php echo vibe_sanitizer($control,'text'); ?>">
                <label><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span></label>
                <ul class="imgselect_choices">
                <?php

                    foreach($this->choices as $key=>$choice){
                        echo '<li><span class="'.(($this->value() == $key)?'selected':'').'" data-val="'.$key.'"><img src="'.$choice.'" ></span></li>';
                    }
                ?>
                </ul><input type="<?php echo vibe_sanitizer($type); ?>"  <?php echo vibe_sanitizer($this->get_link()); ?> />
                <script>
                    jQuery('.vibe_customizer_<?php echo vibe_sanitizer($control); ?> input[type="<?php echo vibe_sanitizer($type); ?>"]').val(jQuery('.vibe_customizer_<?php echo vibe_sanitizer($control); ?> .imgselect_choices span.selected').attr('data-val'));
                    jQuery('.vibe_customizer_<?php echo vibe_sanitizer($control); ?> .imgselect_choices span').on('click',function(){
                        jQuery('.vibe_customizer_<?php echo vibe_sanitizer($control); ?> .imgselect_choices span.selected').removeClass('selected');
                        jQuery(this).addClass('selected');
                        jQuery('.vibe_customizer_<?php echo vibe_sanitizer($control); ?> input[type="<?php echo vibe_sanitizer($type); ?>"]').val(jQuery(this).attr('data-val'));
                        jQuery('.vibe_customizer_<?php echo vibe_sanitizer($control); ?> input[type="<?php echo vibe_sanitizer($type); ?>"]').trigger('change');
                    });
                </script>
            </div>
            <?php
        }
    }

    class Vibe_Customize_Color_Control extends WP_Customize_Control {
    
        public $type = 'alpha-color';
        public $palette;
        public $show_opacity;


        public function enqueue() {
            wp_enqueue_style('alpha-color-picker',VIBE_URL.'/js/alpha-color-picker.css',array('wp-color-picker'),MICRONET_VERSION);
            wp_enqueue_script(
                'alpha-color-picker',VIBE_URL.'/js/alpha-color-picker.js',array( 'jquery', 'wp-color-picker' ),MICRONET_VERSION,true);
        }
        public function render_content() {
            // Process the palette
            if ( is_array( $this->palette ) ) {
                $palette = implode( '|', $this->palette );
            } else {
                // Default to true.
                $palette = ( empty($this->palette) || false === $this->palette || 'false' === $this->palette ) ? 'false' : 'true';
            }
            // Support passing show_opacity as string or boolean. Default to true.
            $show_opacity = ( false === $this->show_opacity || 'false' === $this->show_opacity ) ? 'false' : 'true';
            // Begin the output. ?>
            <div class="vibe_customizer_<?php echo vibe_sanitizer($control); ?>">
                <label>
                <?php // Output the label and description if they were passed in.
                if ( isset( $this->label ) && '' !== $this->label ) {
                    echo '<span class="customize-control-title">' . sanitize_text_field( $this->label ) . '</span>';
                }
                if ( isset( $this->description ) && '' !== $this->description ) {
                    echo '<span class="description customize-control-description">' . sanitize_text_field( $this->description ) . '</span>';
                } ?>
                </label>
                <input class="alpha-color-control" type="text" data-show-opacity="<?php echo vibe_sanitizer($show_opacity); ?>" data-palette="<?php echo esc_attr( $palette ); ?>" data-default-color="<?php echo esc_attr( $this->settings['default']->default ); ?>" <?php $this->link(); ?>  />
            </div>
            <?php
        }
    }