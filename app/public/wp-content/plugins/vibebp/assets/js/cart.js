!function(e){var t={};function n(r){if(t[r])return t[r].exports;var i=t[r]={i:r,l:!1,exports:{}};return e[r].call(i.exports,i,i.exports,n),i.l=!0,i.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var i in e)n.d(r,i,function(t){return e[t]}.bind(null,i));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=1)}([function(e,t,n){},function(e,t,n){"use strict";function r(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){var n=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=n){var r,i,a,c,o=[],l=!0,u=!1;try{if(a=(n=n.call(e)).next,0===t){if(Object(n)!==n)return;l=!1}else for(;!(l=(r=a.call(n)).done)&&(o.push(r.value),o.length!==t);l=!0);}catch(e){u=!0,i=e}finally{try{if(!l&&null!=n.return&&(c=n.return(),Object(c)!==c))return}finally{if(u)throw i}}return o}}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return i(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return i(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function i(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}n.r(t);var a=wp.element,c=(a.createElement,a.useState),o=a.useEffect,l=(a.Fragment,a.render,wp.data),u=(l.dispatch,l.select,function(e,t,n,r,i,a){document.cookie=e+"="+escape(t)+(n?"; expires="+n:"")+(r?"; path="+r:"")+(i?"; domain="+i:window.location.hostname)+(a?"; secure":"")}),s=function(e){var t=" "+document.cookie,n=" "+e+"=",r=null,i=0,a=0;return t.length>0&&-1!=(i=t.indexOf(n))&&(i+=n.length,-1==(a=t.indexOf(";",i))&&(a=t.length),r=unescape(t.substring(i,a))),r},p=function(e){var t=r(c(e.time),2),n=t[0],i=(t[1],r(c(""),2)),a=i[0],l=i[1],u=function(){var t=0,r=0,i=Math.floor((n-(new Date).getTime())/1e3);i>60&&(t=(t=Math.floor(i/60))<10?"0"+t.toString():t.toString()),r=(r=Math.floor(i%60).toString().padStart(2,"0"))<10?"0"+r.toString():r.toString(),i<=0?(l(window.vibebp_cart.translations.timeout),e.removeItem()):l(t+":"+r)};return o((function(){var e=setInterval(u,1e3);return function(){clearInterval(e)}}),[]),wp.element.createElement("span",{className:"clock"},a)},m=void 0;function d(e){return function(e){if(Array.isArray(e))return _(e)}(e)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(e)||y(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function f(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){var n=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=n){var r,i,a,c,o=[],l=!0,u=!1;try{if(a=(n=n.call(e)).next,0===t){if(Object(n)!==n)return;l=!1}else for(;!(l=(r=a.call(n)).done)&&(o.push(r.value),o.length!==t);l=!0);}catch(e){u=!0,i=e}finally{try{if(!l&&null!=n.return&&(c=n.return(),Object(c)!==c))return}finally{if(u)throw i}}return o}}(e,t)||y(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function y(e,t){if(e){if("string"==typeof e)return _(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);return"Object"===n&&e.constructor&&(n=e.constructor.name),"Map"===n||"Set"===n?Array.from(e):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?_(e,t):void 0}}function _(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}var b=wp.element,v=(b.createElement,b.useState),w=b.useEffect,h=b.Fragment,g=(b.useRef,wp.data),E=(g.dispatch,g.select,function(e){var t=f(v(!1),2),n=t[0],r=t[1],i=f(v(!1),2),a=i[0],c=i[1],o=f(v([]),2),l=o[0],y=o[1],_=f(v({}),2),b=(_[0],_[1]);w((function(){setTimeout((function(){r(!0)}),200);return function(){r(!1)}}),[]),w((function(){return document.querySelector("body").addEventListener("cart_totals_refreshed",g),function(){document.querySelector("body").removeEventListener("cart_totals_refreshed",g)}}),[]),w((function(){return g(),document.addEventListener("vibebp_cart_updated",g),function(){document.removeEventListener("vibebp_cart_updated",g)}}),[]),String.prototype.insert=function(e,t){var n=e<0?m.length+e:e;return m.substring(0,n)+t+m.substr(n)};var g=function(){c(!0),fetch("".concat(window.vibebp_cart.api.get,"?client_id=").concat(window.vibebp.settings.client_id,"&force"),{method:"get"}).then((function(e){return e.json()})).then((function(e){b(e);var t=[];e.hasOwnProperty("items")&&e.items.length&&e.items.map((function(e){-1==t.findIndex((function(t){return t.key==e.id}))&&t.push({id:e.id,key:e.key,title:e.name,quantity:e.quantity,price:{currency_prefix:e.totals.currency_prefix,currency_suffix:e.totals.currency_suffix,amount:[e.totals.line_subtotal.slice(0,e.totals.line_subtotal.length-parseInt(e.totals.currency_minor_unit)),".",e.totals.line_subtotal.slice(e.totals.line_subtotal.length-parseInt(e.totals.currency_minor_unit))].join("")}})}));var n=s("appointment_products"),r=s("cart_items");n&&(n=JSON.parse(n)),Array.isArray(n)&&n.length&&n.map((function(e){var n,r="";e.price.currency_html.indexOf(e.price.amount)?n=e.price.currency_html.replace(e.price.amount,""):r=e.price.currency_html.replace(e.price.amount,""),t.push({key:e.id,id:e.id,title:e.title+"["+e.appointment_id+"]",expiry:e.expiry,image:e.image,price:{currency_html:e.price.currency_html,currency_prefix:n,currency_suffix:r,amount:e.price.amount}})})),r&&(r=JSON.parse(r)),Array.isArray(r)&&r.length&&r.map((function(e){var n,r="";e.price.currency_html.indexOf(e.price.amount)?n=e.price.currency_html.replace(e.price.amount,""):r=e.price.currency_html.replace(e.price.amount,""),t.push({key:e.id,id:e.id,title:e.title,image:e.image,price:{currency_html:e.price.currency_html,currency_prefix:n,currency_suffix:r,amount:e.price.amount}})})),localforage.setItem("cart_item_count",t.length),document.dispatchEvent(new Event("vibebp_update_cart")),y(t),c(!1)}))},E=function(t){fetch("".concat(window.vibebp_cart.api.remove_item,"?key=").concat(t,"&client_id=").concat(window.vibebp.settings.client_id,"&force"),{method:"post",headers:{Nonce:window.vibebp_cart.settings.nonce},body:JSON.stringify({key:t})}).then((function(e){return e.json()})).then((function(e){console.log(e)}));var n=s("appointment_products");if(n&&(n=JSON.parse(n)).findIndex((function(e){return e.id==t}))>-1){n.splice(n.findIndex((function(e){return e.id==t})),1);var r="/";r="undefined"==typeof VIBEAPPOINTMENTS?window.vibebp_cart.settings.cookiepath:VIBEAPPOINTMENTS.settings.cookiepath,u("appointment_products",JSON.stringify(n),new Date((new Date).setMinutes((new Date).getMinutes()+15)).toUTCString(),r,window.location.hostname)}var i=s("cart_items");i&&(i=JSON.parse(i)).findIndex((function(e){return e.id==t}))>-1&&(i.splice(i.findIndex((function(e){return e.id==t})),1),u("cart_items",JSON.stringify(i),new Date((new Date).setMinutes((new Date).getMinutes()+15)).toUTCString(),window.vibebp_cart.settings.cookiepath,window.location.hostname)),e.update()},S=function(t,n){fetch("".concat(window.vibebp_cart.api.update_item,"?key=").concat(t,"&quantitiy=").concat(n,"&client_id=").concat(window.vibebp.settings.client_id,"&force"),{method:"post",headers:{Nonce:window.vibebp_cart.settings.nonce},body:JSON.stringify({key:t,quantity:n})}).then((function(e){return e.json()})).then((function(t){e.update()}))};return wp.element.createElement("div",{className:"vibebp_body_wrapper"},wp.element.createElement("span",{className:"vibebp_close",onClick:e.close}),wp.element.createElement("div",{className:n?"vibebp_cart_wrapper active":"vibebp_cart_wrapper"},wp.element.createElement("div",{className:"vibebp_cart"},wp.element.createElement("strong",null,window.vibebp_cart.translations.cart,wp.element.createElement("span",{className:"vicon vicon-close",onClick:e.close})),wp.element.createElement("div",{className:"vibebp_cart_body"},a?"...":l.length?l.map((function(e,t){return wp.element.createElement("div",{className:"cart_item_wrapper",key:t},wp.element.createElement("div",{className:"cart_item"},e.image?wp.element.createElement("img",{src:e.image,className:"image"}):wp.element.createElement("svg",{className:"image",xmlns:"http://www.w3.org/2000/svg",width:"24",height:"24",viewBox:"0 0 24 24"},wp.element.createElement("path",{fill:"none",d:"M0 0h24v24H0z"}),wp.element.createElement("path",{d:"M4.828 21l-.02.02-.021-.02H2.992A.993.993 0 012 20.007V3.993A1 1 0 012.992 3h18.016c.548 0 .992.445.992.993v16.014a1 1 0 01-.992.993H4.828zM20 15V5H4v14L14 9l6 6zm0 2.828l-6-6L6.828 19H20v-1.172zM8 11a2 2 0 110-4 2 2 0 010 4z"})),wp.element.createElement("span",{className:"cart_item_title"},wp.element.createElement("span",{dangerouslySetInnerHTML:{__html:e.title}}),e.hasOwnProperty("desc")?wp.element.createElement("span",{dangerouslySetInnerHTML:{__html:e.desc}}):"",e.price.hasOwnProperty("currency_html")?wp.element.createElement("span",{dangerouslySetInnerHTML:{__html:e.price.currency_html}}):e.hasOwnProperty("price")?wp.element.createElement("span",{dangerouslySetInnerHTML:{__html:e.price.currency_prefix+e.price.amount+e.price.currency_suffix}}):"",e.hasOwnProperty("expiry")?wp.element.createElement(p,{time:new Date(e.expiry).getTime(),removeItem:function(){return E(e.key)}}):""),e.hasOwnProperty("quantity")?wp.element.createElement("span",{className:"quantity"},e.quantity>0?wp.element.createElement("span",{className:"vicon vicon-minus",onClick:function(n){var r=d(l);r[t].quantity--,r[t].quantity<=0?(r.splice(t,1),E(e.key)):S(e.key,r[t].quantity),y(r)}}):wp.element.createElement("span",null),wp.element.createElement("span",null,e.quantity),wp.element.createElement("span",{className:"vicon vicon-plus",onClick:function(){var n=d(l);n[t].quantity++,y(n),S(e.key,n[t].quantity)}})):"",wp.element.createElement("span",{className:"vicon vicon-trash",onClick:function(){var n=d(l);n.splice(t,1),y(n),E(e.key)}})))})):wp.element.createElement("span",{className:"vbp_error"},window.vibebp_cart.translations.cart_no_items)),wp.element.createElement("div",{className:"vibebp_cart_footer"},l.length?wp.element.createElement(h,null,window.vibebp_cart.settings.hasOwnProperty("continue_shopping_link")&&window.vibebp_cart.settings.continue_shopping_link?wp.element.createElement("a",{href:window.vibebp_cart.settings.continue_shopping_link,className:"button is-success"},window.vibebp_cart.translations.continue_shopping):wp.element.createElement("a",{onClick:e.close,className:"button is-success"},window.vibebp_cart.translations.continue_shopping),l.filter((function(e){return"credits"==e.type})).length?wp.element.createElement("a",{className:"button is-primary"},window.vibebp_cart.translations.complete_purchase_via_credits):wp.element.createElement("a",{href:window.vibebp_cart.settings.cart+"?"+Math.round(1e4*Math.random()),className:"button is-primary"},window.vibebp_cart.translations.view_cart)):wp.element.createElement("a",{className:"button",onClick:e.close},wp.element.createElement("span",{className:"vicon vicon-close"})," ",window.vibebp_cart.translations.close)))))});function S(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){var n=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=n){var r,i,a,c,o=[],l=!0,u=!1;try{if(a=(n=n.call(e)).next,0===t){if(Object(n)!==n)return;l=!1}else for(;!(l=(r=a.call(n)).done)&&(o.push(r.value),o.length!==t);l=!0);}catch(e){u=!0,i=e}finally{try{if(!l&&null!=n.return&&(c=n.return(),Object(c)!==c))return}finally{if(u)throw i}}return o}}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return O(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return O(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function O(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}var I=wp.element,N=(I.createElement,I.useState),x=I.useEffect,A=(I.Fragment,I.useRef,wp.data),j=(A.dispatch,A.select,function(e){var t=S(N(!1),2),n=t[0],r=t[1],i=S(N(0),2),a=i[0],c=i[1],o=function(){r(!0)};x((function(){var e=function(){var e=parseInt(sessionStorage.getItem("cart_item_count"));sessionStorage.setItem("cart_item_count",e+1),c(e+1)},t=function(){var e=parseInt(sessionStorage.getItem("cart_item_count"));sessionStorage.setItem("cart_item_count",e-1),c(e-1)};return"undefined"!=typeof jQuery&&(jQuery(document.body).on("added_to_cart",e),jQuery(document.body).on("removed_from_cart updated_cart_totals wc_cart_emptied",t)),function(){"undefined"!=typeof jQuery&&(jQuery(document.body).on("added_to_cart",e),jQuery(document.body).on("removed_from_cart updated_cart_totals wc_cart_emptied",t))}}),[]);var l=function(){var e=s("appointment_products");e&&(e=JSON.parse(e));var t=s("cart_items"),n=0;Array.isArray(e)&&e.length&&(n=e.length),Array.isArray(t)&&t.length&&(n+=t.length),fetch("".concat(window.vibebp_cart.api.get,"?client_id=").concat(window.vibebp.settings.client_id,"&force"),{method:"get"}).then((function(e){return e.json()})).then((function(r){r.hasOwnProperty("items")&&r.items.length&&(r.items.map((function(r){Array.isArray(t)&&t.length&&-1==t.findIndex((function(e){return e.id==r.id}))&&n++,Array.isArray(e)&&e.length&&-1==e.findIndex((function(e){return e.id==r.id}))&&n++})),n<r.items_count&&(n=r.items_count)),c(n),sessionStorage.setItem("cart_item_count",n)}))};return x((function(){return l(),document.addEventListener("vibebp_update_cart",l),document.addEventListener("vibebp_show_cart",o),function(){document.removeEventListener("vibebp_show_cart",o),document.removeEventListener("vibebp_update_cart",l)}}),[]),wp.element.createElement("span",{className:"vibebp_cart"},wp.element.createElement("svg",{onClick:function(){return r(!0)},xmlns:"http://www.w3.org/2000/svg",width:"24",height:"24",viewBox:"0 0 24 24"},wp.element.createElement("path",{fill:"currentColor",d:"M4 6.414L.757 3.172l1.415-1.415L5.414 5h15.242a1 1 0 01.958 1.287l-2.4 8a1 1 0 01-.958.713H6v2h11v2H5a1 1 0 01-1-1V6.414zM6 7v6h11.512l1.8-6H6zm-.5 16a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm12 0a1.5 1.5 0 110-3 1.5 1.5 0 010 3z"})),a?wp.element.createElement("span",null,a):"",n?ReactDOM.createPortal(wp.element.createElement(E,{update:l,close:function(e){r(!1)}}),document.body):"")}),k=(n(0),wp.element),M=(k.createElement,k.useState,k.useEffect,k.Fragment,k.render),T=wp.data;T.dispatch,T.select;document.addEventListener("DOMContentLoaded",(function(){document.querySelector(".vibebp-cart")&&M(wp.element.createElement(j,null),document.querySelector(".vibebp-cart"))}))}]);