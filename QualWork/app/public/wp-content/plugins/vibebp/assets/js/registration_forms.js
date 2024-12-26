function hasClasss(elem, className) {
    if(typeof elem.className=='string'){
        return elem.className.split(' ').indexOf(className) > -1;
    }else{
        return false;
    }
    
}
var closestByClass = function(el, clazz) {
    
    while (el.className != clazz) {
        
        el = el.parentNode;
        if (!el) {
            return null;
        }
    }
 
    return el;
}


var vibegetelementType = function(el) {
    if(el && el !== '' && el !== 'undefined'){
        if( el.getAttribute('type')!=='undefined' && el.getAttribute('type') !== null && el.getAttribute('type')!=='' && (el.getAttribute('type')=='radio' || el.getAttribute('type')=='checkbox')){
              return  el.getAttribute('type');
        }else if(el.nodeName=='TEXTAREA'){
            return 'textarea';
        }else if(el.nodeName=='SELECT'){
            return 'select';
        }else{
            if(el.tagName=='INPUT'){
                if(el.getAttribute('type')=='file'){
                    return  'file';
                 
                }
                return  'input';
            }
        }
    }
}
let loading = 0;
document.addEventListener('click', function (event) {
  console.log(event);
    if (hasClasss(event.target, 'vibebp_submit_registration_form') || hasClasss(event.target.parentElement, 'vibebp_submit_registration_form')) {
          console.log('hasClasss(event.target.parentElement,',hasClasss(event.target, 'submit_registration_form'));
        let el = event.target;
   
        if(hasClasss(event.target.parentElement, 'submit_registration_form')){
            el = event.target.parentElement;
         }
        event.preventDefault();
        window.onbeforeunload = null;
        
        var parent = closestByClass(el,'vibebp_registration_form');
  
        if(parent.querySelector('.message')){
            parent.querySelector('.message').remove();
        }
        if(loading)
            return;

        let errrfields = document.querySelectorAll('.field_error');
        for (var i = errrfields.length - 1; i >= 0; i--) {
            if(errrfields[i].classList){
                errrfields[i].classList.remove("field_error")
            }
            
        }
        if(el.classList){
            el.classList.add('loading');
        }
        loading=1;
        
        var settings = [];

        let formfields = parent.querySelectorAll('input,textarea,select');
        let formdata = new FormData();
        let filesdata = [];
        for (var i = formfields.length - 1; i >= 0; i--) {
            var data = null;
            if(vibegetelementType(formfields[i])=='radio'){
                if(formfields[i].checked){
                    var data = {id:formfields[i].getAttribute('name'),value: formfields[i].value};
                }
                
            }else if(vibegetelementType(formfields[i])=='checkbox'){
                if(formfields[i].checked){
                    var data = {id:formfields[i].getAttribute('name'),value: formfields[i].value};
                }
            }else if(vibegetelementType(formfields[i])=='select'){
                var data = {id:formfields[i].getAttribute('name'),value: formfields[i].options[formfields[i].selectedIndex].value};
            }else if(vibegetelementType(formfields[i])=='input'){
                var data = {id:formfields[i].getAttribute('name'),value: formfields[i].value,'field':formfields[i]};
            }else if(vibegetelementType(formfields[i])=='textarea'){
                if(hasClasss(formfields[i],'wp-editor-area') && formfields[i].getAttribute('aria-hidden') == 'true'){
                var data = {id:formfields[i].getAttribute('name'),value:tinyMCE.get(formfields[i].getAttribute('id')).getContent({format : 'raw'})};
                }else{
                    var data = {id:formfields[i].getAttribute('name'),value: formfields[i].value};
                }
            }else if(vibegetelementType(formfields[i])=='file'){
       
                if(formfields[i].files.length){
                    filesdata.push({id:formfields[i].getAttribute('name'),value:formfields[i].files[0]});
                }
                
                var data = {id:formfields[i].getAttribute('name'),value: formfields[i].value,'field':formfields[i]};
            }
      
            if(data)
              settings.push(data);
        }

        var response='';
        


        xhr = new XMLHttpRequest();
        
        formdata.append("action", 'vibebp_register_user');
    
        formdata.append("security", document.getElementById('bp_new_signup').value);
        formdata.append("name", parent.getAttribute('data-form-name'));
        formdata.append("settings", JSON.stringify(settings));

        if(filesdata.length){
            filesdata.map(function(file,i){
                formdata.append("file_"+file.id, file.value);
            });
        }
       
        

        xhr.open('POST', ajaxurl, true);
        //xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {console.log('asdasd');
                loading=0;
              if (xhr.status === 200) {

                  let data = xhr.responseText;
                    if(el.classList){
                        el.classList.remove('loading');
                    }

                    let _element  = document.createElement("div");
                    _element.innerHTML = data;
                    parent.appendChild(_element);
         

                    setTimeout(function(){
                        if( parent.querySelector('.field_error .message.error')){
                            parent.querySelector('.field_error .message.error').click(function(){
                                parent.querySelector('.field_error .message.error').remove();
                            });
                        }
                        

                    },200);
                    
                  
              }
              else if (xhr.status !== 200) {
                  console.log('Something went wrong.Request failed.' + xhr.status);
                  
              }
              
        };
        if(typeof grecaptcha != 'undefined'){
            grecaptcha.ready(function() {
                grecaptcha.execute(vibebp_reg_forms.recaptcha_key, {action: 'submit'}).then(function(token) { 
                    formdata.append("recaptchaToken", token);
                    xhr.send(formdata); //send only after invalid captcha
                });
            });
        }else{
            xhr.send(formdata);    
        }
        
    }
}, false);