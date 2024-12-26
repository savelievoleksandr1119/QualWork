const reframe =  (target, cName) => {
    var _a, _b;
    var frames = typeof target === 'string' ? document.querySelectorAll(target) : target;
    var c = cName || 'js-reframe';
    if (!('length' in frames))
        frames = [frames];
    for (var i = 0; i < frames.length; i += 1) {
        var frame = frames[i];
        var hasClass = frame.className.split(' ').indexOf(c) !== -1;
        if (hasClass || frame.style.width.indexOf('%') > -1) {
            return;
        }
        // get height width attributes
        var height = frame.getAttribute('height') || frame.offsetHeight;
        var width = frame.getAttribute('width') || frame.offsetWidth;
        var heightNumber = typeof height === 'string' ? parseInt(height) : height;
        var widthNumber = typeof width === 'string' ? parseInt(width) : width;
        // general targeted <element> sizes
        var padding = (heightNumber / widthNumber) * 100;
        // created element <wrapper> of general reframed item
        // => set necessary styles of created element <wrapper>
        var div = document.createElement('div');
        div.className = c;
        var divStyles = div.style;
        divStyles.position = 'relative';
        divStyles.width = '100%';
        divStyles.paddingTop = "".concat(padding, "%");
        // set necessary styles of targeted <element>
        var frameStyle = frame.style;
        frameStyle.position = 'absolute';
        frameStyle.width = '100%';
        frameStyle.height = '100%';
        frameStyle.left = '0';
        frameStyle.top = '0';
        // reframe targeted <element>
        (_a = frame.parentNode) === null || _a === void 0 ? void 0 : _a.insertBefore(div, frame);
        (_b = frame.parentNode) === null || _b === void 0 ? void 0 : _b.removeChild(frame);
        div.appendChild(frame);
    }
}


const micronet_resize = ()=>{

      document.querySelectorAll('#primary-menu > ul > li > .sub-menu >li').forEach((el)=>{
            el.addEventListener('mouseenter',function(el){
                  if(!el.target.parentNode.classList.contains('openleft')){
                        let dimensions = el.target.getBoundingClientRect();
                        if(dimensions.right > window.innerWidth){
                              el.target.parentNode.classList.add('openleft');
                        }
                  }
            });
      });
      document.querySelectorAll('.menu_icon_item').forEach((el)=>{
            let icon = atob(el.getAttribute('data-icon'));
            if(icon.length < 400){ 
                  icon.split(' ').map(i=>el.classList.add(i));
            }else{
                  el.innerHTML = icon;
            }
      });

      if(document.querySelector('header.site-header nav')){
            let currentMenuitem =document.querySelector('#primary-menu>ul>li');

            if(document.querySelector('#primary-menu>ul>li.current-menu-item')){
                  currentMenuitem = document.querySelector('#primary-menu>ul>li.current-menu-item');
            }else if( document.querySelector('#primary-menu>ul>li.current-menu-ancestor')){
                  currentMenuitem =document.querySelector('#primary-menu>ul>li.current-menu-ancestor');
            }else if(document.querySelector('#primary-menu>ul>li.current_page_item')){
                  currentMenuitem = document.querySelector('#primary-menu>ul>li.current_page_item');

            }

            
            document.querySelectorAll('#primary-menu>ul>li').forEach((el,i)=>{
                  
                  if(window.innerWidth > 960){
                        let rect = null;
                        

                        el.addEventListener('mouseenter',()=>{
                              rect = el.getBoundingClientRect();
                              document.querySelector('.mega_menu_root_active_highlight').style.left='calc('+rect.x+'px - 0.5rem)';
                              document.querySelector('.mega_menu_root_active_highlight').style.width='calc('+rect.width+'px + 1rem)';
                        });
                        el.addEventListener('mouseleave',()=>{
                              rect = currentMenuitem.getBoundingClientRect();
                              document.querySelector('.mega_menu_root_active_highlight').style.left='calc('+rect.x+'px - 0.5rem)';
                              document.querySelector('.mega_menu_root_active_highlight').style.width='calc('+rect.width+'px + 1rem)';
                        });
                  }
            });

            if(currentMenuitem){
                  let rect = currentMenuitem.getBoundingClientRect();
                  currentMenuitem.classList.add('active');
                  document.querySelector('.mega_menu_root_active_highlight').style.left ='calc('+rect.x+'px - 0.5rem)';
                  document.querySelector('.mega_menu_root_active_highlight').style.width='calc('+rect.width+'px + 1rem)';      
            }
                
      }


      sticky_block();
      reframe('iframe');
};


var sticky_block = function(){
      if(document.querySelector('#vibebpmember')){
            document.querySelectorAll('.sticky_block').forEach(function(el){

                  var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
                  if(w > 768){
                 
                        var cwidth = el.offsetWidth;
                        var contentHeight=document.querySelector('#vibebpmember').offsetHeight;
                        var top=0;
                    
                        var etop = el.getBoundingClientRect().top;
                        var endheight = top + contentHeight - el.offsetHeight-etop;  
                        el.style.width = cwidth+'px';
                       
                        window.addEventListener('scroll',function(event){
                            var st = window.pageYOffset;

                            if( st < endheight){
                          if(st > etop){
                                el.style.transform ='translateY('+st+'px)';
                          }else{
                            el.style.transform ='none';
                          }
                            }
                        },false);
                
                  }
            });
      }
};

window.addEventListener('scroll',  (event)=>{
      document.querySelectorAll('.vibe_animate').forEach((el)=>{
            if (isInViewport(el)) {
                  el.classList.add('loaded');
                  if(document.querySelector('.'+el.getAttribute('id'))){
                        document.querySelector('.'+el.getAttribute('id')).classList.add('active');
                  }
            }
      })  
      
}, false);


function isInViewPort(element) {
    // Get the bounding client rectangle position in the viewport
    var bounding = element.getBoundingClientRect();

    // Checking part. Here the code checks if it's *fully* visible
    // Edit this part if you just want a partial visibility
    if (
        bounding.top >= 0 &&
        bounding.left >= 0 &&
        bounding.right <= (window.innerWidth || document.documentElement.clientWidth) &&
        bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight)
    ) {
        return true;
    } else {
        return false;
    }
}



function addListBlockClassName( settings, name ) {
    if ( name !== 'core/list' ) {
        return settings;
    }
 
    return lodash.assign( {}, settings, {
        supports: lodash.assign( {}, settings.supports, {
            className: true,
        } ),
    } );
}
 
wp.hooks.addFilter(
    'blocks.registerBlockType',
    'my-plugin/class-names/list-block',
    addListBlockClassName
);

document.addEventListener('DOMContentLoaded',()=>{
      setTimeout(()=>{
            micronet_resize();
      },100);

      if(document.querySelectorAll('header.site-header nav .menu-item')){

            document.querySelectorAll('header.site-header nav .menu-item').forEach(function(el) {
              
                  if(el.querySelector('.megadrop')){ 
                        el.classList.add('hasmegamenu');
                        el.style.position = 'static';
                        var attr = el.querySelector('.megadrop').getAttribute('data-width');
                        if ( attr != '100%' && attr != 'container' ) {     
                             el.style.position = 'relative';
                              let nattr = parseInt(attr.replace('px',''));
                              if(!isNaN(nattr)){
                                    el.querySelector('.sub-menu').style.left = '-'+(Math.round(nattr/2,2) - 30 )+'px';      
                              }
                        }
                        el.querySelector('.sub-menu').style.width = attr;
                        //el.querySelector('.sub-menu').style.top='100';
                  }
              
            });

            if(document.querySelector('.mega_sub_posts_menu')){
                  document.querySelector('.mega_menu_term').classList.add('active');
                  document.querySelector('.mega_sub_posts_menu').classList.add('active');
            

                  document.querySelectorAll('.mega_menu_term').forEach((el)=>{
                      
                        el.addEventListener('mouseenter',()=>{
                              if(document.querySelector('.'+el.getAttribute('data-id')+'_posts')){
                                    
                                    if( el.parentNode.querySelector('.mega_menu_term.active')){
                                          el.parentNode.querySelector('.mega_menu_term.active').classList.remove('active');      
                                    }
                                    el.classList.add('active');

                                    if(el.parentNode.parentNode.querySelector('.mega_sub_posts_menu.active')){
                                          el.parentNode.parentNode.querySelector('.mega_sub_posts_menu.active').classList.remove('active');      
                                    }                                    
                                    el.parentNode.parentNode.querySelector('.'+el.getAttribute('data-id')+'_posts').classList.add('active');
                              }
                        });
                  });
            } 
      } 

      if(document.querySelector('.menu-item-has-children') && window.innerWidth < 768){
            document.querySelectorAll('.menu-item-has-children').forEach((el)=>{
                  el.addEventListener('click',function(e){
                        if(e.target.nodeName == 'LI'){
                              e.preventDefault();
                              el.classList.toggle('active');
                              e.stopPropagation();
                        }
                  });
            });
      }
});

window.addEventListener('resize', micronet_resize);

document.addEventListener('userLoaded',micronet_resize);

class CountUp {
  constructor(el) {
  const counter = el.querySelector('.counter')
  const trigger = el.querySelector('.start-counter');
  let num = 0
  const decimals = counter.dataset.decimals
  let increment = counter.dataset.increment
  const countUp = () => {
    if (num < counter.dataset.stop)
      
      // Do we want decimals?
      if (decimals) {
            if(!increment){increment = 0.01}
        num += increment
        counter.textContent = new Intl.NumberFormat('en-GB', { 
          minimumFractionDigits: 2,
          maximumFractionDigits: 2 
}).format(num)
   }
    else {
      if(!increment){increment = 1}
      num=num+increment
      counter.textContent = num
    }
  }
  
  const observer = new IntersectionObserver((el) => {
    if (el[0].isIntersecting) {
      const interval = setInterval(() => {
        (num < counter.dataset.stop) ? countUp() : clearInterval(interval)
      }, counter.dataset.speed)
    }
  }, { threshold: [0] })

      observer.observe(trigger)
  }
}

const rotatingText = () => {

      let words = document.querySelectorAll(".word");
      words.forEach(word => {
            let letters = word.textContent.split("");
            word.textContent = "";
                  letters.forEach(letter => {
                  let span = document.createElement("span");
                  span.textContent = letter;
                  span.className = "letter";
                  word.append(span);
            });
      });

      let currentWordIndex = 0;
      let maxWordIndex = words.length - 1;
      (words[currentWordIndex] as HTMLElement).style.opacity = "1";

      let rotateText = () => {
            let currentWord = words[currentWordIndex];
            let nextWord =
            currentWordIndex === maxWordIndex ? words[0] : words[currentWordIndex + 1];
            // rotate out letters of current word
            Array.from(currentWord.children).forEach((letter, i) => {
                  setTimeout(() => {
                        letter.className = "letter out";
                  }, i * 80);
            });
            // reveal and rotate in letters of next word
            (nextWord as HTMLElement).style.opacity = "1";
            Array.from(nextWord.children).forEach((letter, i) => {
            letter.className = "letter behind";
            setTimeout(() => {
                  letter.className = "letter in";
            }, 340 + i * 80);
            });
            currentWordIndex =
            currentWordIndex === maxWordIndex ? 0 : currentWordIndex + 1;
      };

      rotateText();
      setInterval(rotateText, 4000);

}

document.addEventListener('DOMContentLoaded',()=>{
      if(document.querySelector('#open_menu_toggle')){
            document.querySelector('#open_menu_toggle').addEventListener('click',(e)=>{
                  e.preventDefault();
                  document.querySelector('.site_mobile_menu').classList.toggle('hidden');
                  setTimeout(()=>{document.querySelector('.site_mobile_menu').classList.toggle('active');},300);
            })
      }
      if(document.querySelector('#close_menu_toggle')){
            document.querySelector('#close_menu_toggle').addEventListener('click',(e)=>{
                  e.preventDefault();
                  document.querySelector('.site_mobile_menu').classList.toggle('active');
                  setTimeout(()=>{document.querySelector('.site_mobile_menu').classList.toggle('hidden');},300);
            })
      }
      if(document.querySelector('.white_bg')){

            document.querySelectorAll('.white_bg').forEach((el)=>{
                  var newContent = "<div class='squares'> <div class='square'></div> <div class='square'></div> <div class='square'></div> <div class='square'></div> <div class='square'></div> <div class='square'></div> <div class='square'></div> <div class='square'></div> <div class='square'></div> <div class='square'></div> </div>";
                  el.insertAdjacentHTML('beforeend', newContent);
            })
      }

      if(document.querySelector('.particles_bg')){
            document.querySelectorAll('.particles_bg').forEach((el)=>{
                  var newContent = '<div class="particles_container"><div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div> <div class="particle"></div></div>';
                  el.insertAdjacentHTML('beforeend', newContent);
            })
      }
      if(document.querySelector('.counter_wrap')){
            document.querySelectorAll('.counter_wrap').forEach((el)=>{
                  new CountUp(el);
            })
      }
      rotatingText();
})