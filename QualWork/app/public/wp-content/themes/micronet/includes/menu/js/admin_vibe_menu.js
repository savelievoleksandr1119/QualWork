/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


jQuery(document).on('click','.on_off',function(event){
    event.preventDefault();
  	if(jQuery(this).hasClass('active')){
      jQuery(this).removeClass('active');
      jQuery(this).parent().parent().find('.sidebar').hide(200);
      jQuery(this).parent().parent().find('.columns').hide(200);
      jQuery(this).parent().parent().find('.menu_width').hide(200);
      jQuery(this).parent().parent().find('.taxonomy').hide(200);
      jQuery(this).parent().parent().find('.megamenu_type').hide(200);
      jQuery(this).parent().parent().find('.hide_taxonomy_terms').hide(200);
      jQuery(this).parent().parent().find('.max_elements').hide(200);
      
  	}else{
      jQuery(this).addClass('active');
      jQuery(this).parent().parent().find('.columns').show(200);
      jQuery(this).parent().parent().find('.menu_width').show(200);
      jQuery(this).parent().parent().find('.megamenu_type').show(200);
  	}
});
jQuery(document).on('change','.select_metamenu_type',function(){
  if(jQuery(this).val()){
    if(jQuery(this).val() == 'sidebar'){
      jQuery('.mega_fields').hide(200);  
      jQuery('.sidebar').show(200);  
    }else if(jQuery(this).val() == 'cat_subcat'){
      jQuery('.mega_fields').hide(200);  
      jQuery('.taxonomy').show(200);  
    }else if(jQuery(this).val() == 'cat_posts'){
      jQuery('.mega_fields').hide(200);  
      jQuery('.posts').show(200);
      
    }
  }else{
    jQuery('.select-sidebar').hide(200);
    jQuery('.taxonomy').hide(200);
    jQuery('.hide_taxonomy_terms').hide(200);
    jQuery('.max_elements').hide(200);
  }
});
