var EnvatoWizard = (function($){

    var t;

    // callbacks from form button clicks.
    var callbacks = {
        install_plugins: function(btn){
            var plugins = new PluginManager();
            plugins.init(btn);
        },
        install_content: function(btn){
            var content = new ContentManager();
            content.init(btn);
        },
        run_upgrader: function(btn){
            var content = new RunUpgrader();
            content.init(btn);
        },
    };

    function window_loaded(){
        // init button clicks:
        $('.button-next').on( 'click', function(e) {
            var loading_button = vibe_loading_button(this);
            if(!loading_button){
                return false;
            }
            if($(this).data('callback') && typeof callbacks[$(this).data('callback')] != 'undefined'){
                // we have to process a callback before continue with form submission
                callbacks[$(this).data('callback')](this);
                return false;
            }else{
                loading_content();
                return true;
            }
        });
        $('.button-upload').on( 'click', function(e) {
            e.preventDefault();
            renderMediaUploader($(this));
        });

        /* Clear imported postids
        */
       $('.clear_imported_posts').on('click',function(event){
            event.preventDefault();
            var $this = $(this);
            if($this.hasClass('disabled'))
                return;

            $this.addClass('disabled');
            $.ajax({
                type: "POST",
                url: envato_setup_params.ajaxurl,
                data: { action: 'clear_imported_posts', 
                        security: $this.attr('data-security'),
                      },
                cache: false,
                success: function (html) {
                    $this.removeClass('disabled');
                }
              });
       });
        $('.theme-features li > a:first-child').on( 'click', function(e) {
            e.preventDefault();
            let $this = $(this).parent();
            if($this.hasClass('selected')){
                $this.find('input[type="hidden"]').attr('name','');
                $this.removeClass('selected');
            }else{
                $this.find('input[type="hidden"]').attr('name','plugins[]');
                $this.addClass('selected');
            }
            return false;
        });
        $('.theme-presets a.sitestyle').on( 'click', function(event) {
            event.preventDefault();
            var $ul = $(this).parent().parent();
            $ul.find('.current').removeClass('current');
            var $li = $(this).parent();
            $li.addClass('current');
            var demo = $(this).data('style');
            $('#demo_style').val(demo);
            return false;
        });
    }

    function loading_content(){
        $('.envato-setup-content').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
    }

    function PluginManager(){

        var complete;
        var items_completed = 0;
        var current_item = '';
        var $current_node;
        var current_item_hash = '';

        function ajax_callback(response){
            if(typeof response == 'object' && typeof response.message != 'undefined'){
                $current_node.find('span').text(response.message);
                if(typeof response.url != 'undefined'){
                    // we have an ajax url action to perform.

                    if(response.hash == current_item_hash){
                        $current_node.find('span').text("failed");
                        find_next();
                    }else {
                        current_item_hash = response.hash;
                        jQuery.post(response.url, response, function(response2) {
                            process_current();
                            $current_node.find('span').text(response.message + envato_setup_params.verify_text);
                        }).fail(ajax_callback);
                    }

                }else if(typeof response.done != 'undefined'){
                    // finished processing this plugin, move onto next
                    find_next();
                }else{
                    // error processing this plugin
                    find_next();
                }
            }else{
                // error - try again with next plugin
                $current_node.find('span').text("ajax error");
                find_next();
            }
        }
        function process_current(){
            if(current_item){
                // query our ajax handler to get the ajax to send to TGM
                // if we don't get a reply we can assume everything worked and continue onto the next one.
                jQuery.post(envato_setup_params.ajaxurl, {
                    action: 'envato_setup_plugins',
                    wpnonce: envato_setup_params.wpnonce,
                    slug: current_item
                }, ajax_callback).fail(ajax_callback);
            }
        }
        function find_next(){
            var do_next = false;
            if($current_node){
                if(!$current_node.data('done_item')){
                    items_completed++;
                    $current_node.data('done_item',1);
                }
                $current_node.find('.spinner').css('visibility','hidden');
            }
            var $li = $('.envato-wizard-plugins li');
            $li.each(function(){
                if(current_item == '' || do_next){
                    current_item = $(this).data('slug');
                    $current_node = $(this);
                    process_current();
                    do_next = false;
                }else if($(this).data('slug') == current_item){
                    do_next = true;
                }
            });
            if(items_completed >= $li.length){
                // finished all plugins!
                complete();
            }
        }
        
        return {
            init: function(btn){
                $('.envato-wizard-plugins').addClass('installing');
                complete = function(){
                    loading_content();
                    window.location.href=btn.href;
                };
                find_next();
            }
        }
    }

    function ContentManager(){

        var complete;
        var items_completed = 0;
        var current_item = '';
        var $current_node;
        var current_item_hash = '';

        function ajax_callback(response) {
            console.log(response);
            if(typeof response == 'object' && typeof response.message != 'undefined'){
                $current_node.find('span').text(response.message);
                if(typeof response.url != 'undefined'){
                    // we have an ajax url action to perform.
                    if(response.hash == current_item_hash){
                        $current_node.find('span').text("failed");
                        find_next();
                    }else {
                        current_item_hash = response.hash;
                        jQuery.post(response.url, response, ajax_callback).fail(ajax_callback); // recuurrssionnnnn
                    }
                }else if(typeof response.done != 'undefined'){
                    // finished processing this plugin, move onto next
                    find_next();
                }else{
                    // error processing this plugin
                    find_next();
                }
            }else{
                // error - try again with next plugin
                $current_node.find('span').text("...success");
                find_next();
            }
        }

        function process_current(){
            if(current_item){

                var $check = $current_node.find('input:checkbox');
                if($check.is(':checked')) {
                    console.log("Doing 2 "+current_item);
                    // process htis one!
                    jQuery.post(envato_setup_params.ajaxurl, {
                        action: 'envato_setup_content',
                        wpnonce: envato_setup_params.wpnonce,
                        content: current_item
                    }, ajax_callback).fail(ajax_callback);
                }else{
                    $current_node.find('span').text("Skipping");
                    setTimeout(find_next,300);
                }
            }
        }
        function find_next(){
            var do_next = false;
            if($current_node){
                if(!$current_node.data('done_item')){
                    items_completed++;
                    $current_node.data('done_item',1);
                }
                $current_node.find('.spinner').css('visibility','hidden');
            }
            var $items = $('tr.envato_default_content');
            var $enabled_items = $('tr.envato_default_content input:checked');
            $items.each(function(){
                if (current_item == '' || do_next) {
                    current_item = $(this).data('content');
                    $current_node = $(this);
                    process_current();
                    do_next = false;
                } else if ($(this).data('content') == current_item) {
                    do_next = true;
                }
            });
            if(items_completed >= $items.length){
                // finished all items!
                complete();
            }
        }

        return {
            init: function(btn){
                $('.envato-setup-pages').addClass('installing');
                $('.envato-setup-pages').find('input').prop("disabled", true);
                complete = function(){
                    loading_content();
                    window.location.href=btn.href;
                };
                find_next();
            }
        }
    }

    function RunUpgrader(){

        var complete;
     

        function syncAction($this,resolve,reject){
            return new Promise(function(resolve,reject){
                console.log($this);
                $this.after('<span class="status">Starting ...</span><div class="progress_wrap" style="margin: 30px 0;width: 300px;"><div class="progress" style="height: 10px;border-radius: 5px;"><div class="bar" style="width: 5%;"></div></div></div>');
                //Show progress bar
                $.ajax({
                    type: "POST",
                    url: envato_setup_params.ajaxurl,
                    dataType: "json",
                    data: { action: 'sync_resync', 
                          id: $this.attr('data-id'),
                          security: $('#sync_security').val(),
                        },
                    cache: false,
                    success: function (json) {

                        $this.parent().find('.progress_wrap .bar').css('width','10%');
                        $this.parent().find('span.status').text('fetch '+Object.keys(json).length+' results, sync in progress...');
                        var defferred = [];
                        var current = 0;
                        $.each(json,function(i,item){
                            defferred.push(item);
                        });
                        recursive_step_sync(current,defferred,$this);
                        //$.each() RUN loop on json and increment progress bar
                        $('body').on('end_recursive_sync_'+$this.attr('data-id'),function(){
                            $.ajax({
                                type: "POST",
                                url: envato_setup_params.ajaxurl,
                                data: { action: $this.attr('data-id'), 
                                      security: $('#sync_security').val(),
                                    },
                                cache: false,
                                success: function (text) {
                                    $this.text(text);
                                    //Complete the progress
                                    $this.parent().find('.progress_wrap .bar').css('width','100%');
                                    setTimeout(function(){
                                        $this.parent().find('.progress_wrap,.status').hide(200);

                                    },3000);
                                    resolve();
                                }
                            });
                        });
                    }
                });
            });
        }

        function recursive_step_sync(current,defferred,$this){
            if(current < defferred.length){
                $.ajax({
                    type: "POST",
                    url: envato_setup_params.ajaxurl,
                    data: defferred[current],
                    cache: false,
                    success: function(){ 
                        current++;
                        $this.parent().find('span.status').text(current+'/'+defferred.length+' complete, sync in progress...');
                        var width = 10 + 90*current/defferred.length;
                        $this.parent().find('.bar').css('width',width+'%');
                        if(defferred.length == current){
                            $('body').trigger('end_recursive_sync_'+$this.attr('data-id'));
                        }else{
                            recursive_step_sync(current,defferred,$this);
                        }
                    }
                });
            }else{
                $('body').trigger('end_recursive_sync_'+$this.attr('data-id'));
            }
        }//End of function
        
        return {
            init: function(btn){
                complete = function(){
                    loading_content();
                    window.location.href=btn.href;
                };
                //can initialize sync loop
                (async function(){
                    var $items = $('tr.sync_step .sync_resync');
                    if($items.length){
                        for (var i =  0; i < $items.length; i++) {
                            await syncAction(jQuery($items[i]));
                            if(i==($items.length-1)){
                                complete();
                            }
                        }
                    }
                })();
            }
        }
    }

    /**
     * Callback function for the 'click' event of the 'Set Footer Image'
     * anchor in its meta box.
     *
     * Displays the media uploader for selecting an image.
     *
     * @since 0.1.0
     */
    function renderMediaUploader($this) {
        'use strict';

        // Default Logo
        var file_frame, attachment;

        if ( undefined !== file_frame ) {
            file_frame.open();
            return;
        }
        
        console.log($this.data('title'));
        file_frame = wp.media.frames.file_frame = wp.media({
            title: $this.data('title'),//'Upload Logo',//jQuery( this ).data( 'uploader_title' ),
            button: {
                text: $this.data('text'), //jQuery( this ).data( 'uploader_button_text' )
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();

            jQuery($this.data('target')).attr('src',attachment.url);
            jQuery($this.data('save')).val(attachment.url);
            //jQuery($this.data('save')).val(attachment.id);
            // Do something with attachment.id and/or attachment.url here
        });
        // Now display the actual file_frame
        file_frame.open();

    }

    function vibe_loading_button(btn){

        var $button = jQuery(btn);
        if($button.data('done-loading') == 'yes')return false;
        var existing_text = $button.text();
        var existing_width = $button.outerWidth();
        var loading_text = '⡀⡀⡀⡀⡀⡀⡀⡀⡀⡀⠄⠂⠁⠁⠂⠄';
        var completed = false;

        $button.css('width',existing_width);
        $button.addClass('vibe_loading_button_current');
        var _modifier = $button.is('input') || $button.is('button') ? 'val' : 'text';
        $button[_modifier](loading_text);
        //$button.attr('disabled',true);
        $button.data('done-loading','yes');

        var anim_index = [0,1,2];

        // animate the text indent
        function moo() {
            if (completed)return;
            var current_text = '';
            // increase each index up to the loading length
            for(var i = 0; i < anim_index.length; i++){
                anim_index[i] = anim_index[i]+1;
                if(anim_index[i] >= loading_text.length)anim_index[i] = 0;
                current_text += loading_text.charAt(anim_index[i]);
            }
            $button[_modifier](current_text);
            setTimeout(function(){ moo();},60);
        }

        moo();

        return {
            done: function(){
                completed = true;
                $button[_modifier](existing_text);
                $button.removeClass('vibe_loading_button_current');
                $button.attr('disabled',false);
            }
        }

    }

    return {
        init: function(){
            t = this;
            $(window_loaded);
        },
        callback: function(func){
            console.log(func);
            console.log(this);
        }
    }

})(jQuery);


EnvatoWizard.init();

jQuery('document').ready(function(){
    jQuery('.hide_next').on('click',function(){
        jQuery(this).toggleClass('show');
        jQuery(this).next().toggleClass('show');
    });
});
    