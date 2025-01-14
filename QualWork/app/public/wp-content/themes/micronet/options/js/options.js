jQuery(document).ready(function(){
	
   if(jQuery('#last_tab').val() == ''){

		jQuery('.vibe-opts-group-tab:first').slideDown('fast');
		jQuery('#vibe-opts-group-menu li:first').addClass('active');
	
	}else{
		
		tabid = jQuery('#last_tab').val();
		jQuery('#'+tabid+'_section_group').slideDown('fast');
		jQuery('#'+tabid+'_section_group_li').addClass('active');
		
	}
	
	
	jQuery('input[name="'+vibe_opts.opt_name+'[defaults]"]').click(function(){
		if(!confirm(vibe_opts.reset_confirm)){
			return false;
		}
	});
	
	jQuery('.vibe-opts-group-tab-link-a').on('click',function(){
		relid = jQuery(this).attr('data-rel');
		
		jQuery('#last_tab').val(relid);
		
		jQuery('.vibe-opts-group-tab').each(function(){
			if(jQuery(this).attr('id') == relid+'_section_group'){
				jQuery(this).delay(400).fadeIn(1200);
			}else{
				jQuery(this).fadeOut('fast');
			}
			
		});
		
		jQuery('.vibe-opts-group-tab-link-li').each(function(){
				if(jQuery(this).attr('id') != relid+'_section_group_li' && jQuery(this).hasClass('active')){
					jQuery(this).removeClass('active');
				}
				if(jQuery(this).attr('id') == relid+'_section_group_li'){
					jQuery(this).addClass('active');
				}
		});
	});
	

	
	
	
	if(jQuery('#vibe-opts-save').is(':visible')){
		jQuery('#vibe-opts-save').delay(4000).slideUp('slow');
	}
	
	if(jQuery('#vibe-opts-imported').is(':visible')){
		jQuery('#vibe-opts-imported').delay(4000).slideUp('slow');
	}	
	
	jQuery('input, textarea, select').change(function(){
		jQuery('#vibe-opts-save-warn').slideDown('slow');
	});
	
	
	jQuery('#vibe-opts-import-code-button').on('click',function(){
		if(jQuery('#vibe-opts-import-link-wrapper').is(':visible')){
			jQuery('#vibe-opts-import-link-wrapper').fadeOut('fast');
			jQuery('#import-link-value').val('');
		}
		jQuery('#vibe-opts-import-code-wrapper').fadeIn('slow');
	});
	
	jQuery('#vibe-opts-import-link-button').on('click',function(){
		if(jQuery('#vibe-opts-import-code-wrapper').is(':visible')){
			jQuery('#vibe-opts-import-code-wrapper').fadeOut('fast');
			jQuery('#import-code-value').val('');
		}
		jQuery('#vibe-opts-import-link-wrapper').fadeIn('slow');
	});
	
	
	
	
	jQuery('#vibe-opts-export-code-copy').on('click',function(){
		if(jQuery('#vibe-opts-export-link-value').is(':visible')){jQuery('#vibe-opts-export-link-value').fadeOut('slow');}
		jQuery('#vibe-opts-export-code').toggle('fade');
	});
	
	jQuery('#vibe-opts-export-link').on('click',function(){
		if(jQuery('#vibe-opts-export-code').is(':visible')){jQuery('#vibe-opts-export-code').fadeOut('slow');}
		jQuery('#vibe-opts-export-link-value').toggle('fade');
	});
});
