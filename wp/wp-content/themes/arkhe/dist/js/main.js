(()=>{"use strict";var e={730:(e,t,n)=>{n.d(t,{Z:()=>d});var o=n(948),r=n(284),l=n(41);const a={pageTop(){(0,l.l)(0,0)},toggleMenu(e){if(e.preventDefault(),null===o.Z.drawerMenu)return!1;const t=0!==e.screenX&&0!==e.screenY,n=e.currentTarget;o.Z.drawerToggleBtn=n,"opened"!==document.documentElement.getAttribute("data-drawer")?(document.documentElement.setAttribute("data-drawer","opened"),r.IW.modalOpen(!0),o.Z.lastFocusedElem=n):(document.documentElement.setAttribute("data-drawer","closed"),!t&&o.Z.lastFocusedElem&&(o.Z.lastFocusedElem.focus(),o.Z.lastFocusedElem=null,r.IW.modalOpen(!1)))},toggleSearch(e){e.preventDefault();const t=o.Z.searchModal;if(null===t)return!1;const n=0!==e.screenX&&0!==e.screenY,l=e.currentTarget;t.classList.contains("is-open")?(t.classList.remove("is-open"),!n&&o.Z.lastFocusedElem&&(o.Z.lastFocusedElem.focus(),o.Z.lastFocusedElem=null,r.IW.modalOpen(!1))):(t.classList.add("is-open"),r.IW.modalOpen(!0),o.Z.lastFocusedElem=l,setTimeout((()=>{t.querySelector('[name="s"]').focus()}),250))},toggleSubmenu(e){e.preventDefault();const t=e.currentTarget,n=t.parentNode.nextElementSibling;t.classList.toggle("is-opened"),n.classList.toggle("is-opened"),e.stopPropagation()}};function d(e){const t=e.querySelectorAll("[data-onclick]");for(let e=0;e<t.length;e++){const n=t[e];if(n){const e=n.getAttribute("data-onclick"),t=a[e];n.addEventListener("click",(function(e){t(e)}))}}}},987:(e,t,n)=>{n.d(t,{Z:()=>a});var o=n(948),r=n(284);const l=e=>{const t=document.getElementById("footer");if(null!==t)if(r.Ro)t.style.paddingBottom="0";else{const n=e.offsetHeight;t.style.paddingBottom=n+"px"}};function a(){null!==o.Z.fixBottomMenu&&l(o.Z.fixBottomMenu)}},948:(e,t,n)=>{n.d(t,{Z:()=>o});const o={header:null,gnav:null,fixBar:null,mainContent:null,sidebar:null,drawerMenu:null,searchModal:null,fixBottomMenu:null,wpadminbar:null,lastFocusedElem:null,drawerToggleBtn:null}},284:(e,t,n)=>{n.d(t,{IW:()=>m,Ro:()=>l,Z8:()=>i,gP:()=>s,ua:()=>u});let o=0,r=0,l=!1,a=!1,d=!1,c=!1,s=!1,i=0;const u=navigator.userAgent.toLowerCase(),m={mediaSize:()=>{l=999<window.innerWidth,d=600>window.innerWidth,a=!l,c=!d},headH:e=>{null!==e&&(o=e.offsetHeight,document.documentElement.style.setProperty("--ark-header_height",o+"px"))},adminbarH:e=>{null!==e&&(r=e.offsetHeight)},modalOpen:e=>{s=e},smoothOffset:()=>{let e=0;const t=window.arkheVars;if(void 0!==t){if(l){if(t.isFixHeadPC&&(e+=o),t.fixGnav){const t=document.querySelector(".l-headerUnder");null!==t&&(e+=t.offsetHeight)}}else t.isFixHeadSP&&(e+=o);document.documentElement.style.setProperty("--ark-header_height--fixed",e+"px"),i=8+e+r}},scrollbarW:()=>{const e=window.innerWidth-document.body.clientWidth;document.documentElement.style.setProperty("--ark-scrollbar_width",e+"px")}}},982:(e,t,n)=>{function o(e){e.header=document.getElementById("header"),e.gnav=document.getElementById("gnav"),e.drawerMenu=document.getElementById("drawer_menu"),e.wpadminbar=document.getElementById("wpadminbar"),e.mainContent=document.getElementById("main_content"),e.sidebar=document.getElementById("sidebar"),e.fixBottomMenu=document.getElementById("fix_bottom_menu"),e.searchModal=document.getElementById("search_modal")}n.d(t,{Z:()=>o})},549:(e,t,n)=>{n.d(t,{Z:()=>l});var o=n(948),r=n(284);function l(){document.addEventListener("keydown",(function(e){27===e.keyCode&&r.gP&&(e.preventDefault(),document.documentElement.setAttribute("data-drawer","closed"),document.querySelectorAll(".c-modal.is-open").forEach((function(e){e.classList.remove("is-open")})),o.Z.lastFocusedElem&&(o.Z.lastFocusedElem.focus(),o.Z.lastFocusedElem=null),r.IW.modalOpen(!1))}))}},829:(e,t,n)=>{n.d(t,{Z:()=>l});var o=n(948);const r=e=>{const t=e.querySelector("li.-current");t&&t.classList.remove("-current");const n=window.arkheVars.homeUrl||"",o=window.location.origin+window.location.pathname;if(n===o)return;const r=e.querySelectorAll(".c-gnav > li");for(let e=0;e<r.length;e++){const t=r[e];o===t.querySelector("a").getAttribute("href")&&t.classList.add("-current")}};function l(){const e=o.Z.gnav;if(null===e)return;r(e);const t=e.querySelector(".c-gnav");if(null===t)return!1;const n=t.getElementsByTagName("a");for(let e=0;e<n.length;e++){const t=n[e];t.addEventListener("focus",l,!0),t.addEventListener("blur",l,!0)}function l(){let e=this;for(;!e.classList.contains("c-gnav");)"li"===e.tagName.toLowerCase()&&e.classList.toggle("focus"),e=e.parentElement}}},188:(e,t,n)=>{n.d(t,{Z:()=>d});var o=n(948),r=n(284);function l(e){if(!e)return[];let t=e.querySelectorAll('a[href], input, select, textarea, button, [tabindex="0"]');return t=Array.prototype.slice.call(t),t}function a(e,t,n){if(!e)return;const l=t[0],a=t[t.length-1];e.addEventListener("keydown",(function(e){if(!r.gP)return;let t=null;"drawer"===n&&(t=o.Z.drawerToggleBtn),9===e.keyCode&&(e.shiftKey?document.activeElement===l&&(e.preventDefault(),t?t.focus():a.focus()):document.activeElement===a&&(e.preventDefault(),t?t.focus():l.focus()))}))}function d(){const e=l(o.Z.drawerMenu);a(o.Z.drawerMenu,e,"drawer"),function(e,t){const n=t[0],o=t[t.length-1];e.forEach((function(e){e.addEventListener("keydown",(function(t){r.gP&&9===t.keyCode&&document.activeElement===e&&(t.preventDefault(),t.shiftKey?o.focus():n.focus())}))}))}(document.querySelectorAll('.c-iconBtn[data-onclick="toggleMenu"]'),e);const t=l(o.Z.searchModal);a(o.Z.searchModal,t,"search")}},378:(e,t,n)=>{function o(){if(!window.IntersectionObserver||!("isIntersecting"in IntersectionObserverEntry.prototype))return;const e=new IntersectionObserver((e=>{e.forEach((e=>{e.isIntersecting?document.documentElement.setAttribute("data-scrolled","false"):document.documentElement.setAttribute("data-scrolled","true")}))}),{root:null,rootMargin:"0px",threshold:0}),t=document.querySelector(".l-scrollObserver");e.observe(t)}n.d(t,{Z:()=>o})},41:(e,t,n)=>{n.d(t,{B:()=>l,l:()=>r});var o=n(284);function r(e,t){const n=window.scrollY;let o=0;if(Number.isInteger(e))o=e;else{const r=e.getBoundingClientRect();o=r.top+n-t,o<0&&(o=0)}if("off"===window.arkheVars?.smoothScroll)return void window.scrollTo(0,o);let r=null,l=500;const a=Math.abs(o-n);1e4<a?l=1500:5e3<a?l=1e3:1e3<a&&(l=750);const d=e=>{const t=e-r,a=Math.min(1,t/l),c=n+(o-n)*(s=a,1-Math.pow(1-s,3));var s;window.scrollTo(0,c),a<1&&requestAnimationFrame(d)};r=performance.now(),d(r)}function l(e){(e||document).querySelectorAll('a[href*="#"]').forEach((e=>{if("_blank"===e.getAttribute("target"))return;const t=e.getAttribute("href"),n=t.split("#");if(n.length>2)return;const l=n[0],a=n[1],d=""===l,c=window.location.origin+window.location.pathname;(d||l===c)&&e.addEventListener("click",(function(e){const n=document.getElementById(a);if(!n)return!0;e.preventDefault(),window.history.pushState({},"",t),r(n,o.Z8),document.documentElement.setAttribute("data-drawer","closed")}))}))}}},t={};function n(o){var r=t[o];if(void 0!==r)return r.exports;var l=t[o]={exports:{}};return e[o](l,l.exports,n),l.exports}n.d=(e,t)=>{for(var o in t)n.o(t,o)&&!n.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:t[o]})},n.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{var e=n(948),t=n(982),o=n(284),r=n(378),l=n(829),a=n(987),d=n(730),c=n(549),s=n(188),i=n(41);-1!==o.ua.indexOf("fb")&&300>window.innerHeight&&location.reload(),o.IW.mediaSize();const u=location.hash;o.IW.scrollbarW(),document.addEventListener("DOMContentLoaded",(function(){(0,t.Z)(e.Z),o.IW.headH(e.Z.header),o.IW.adminbarH(e.Z.wpadminbar),o.IW.smoothOffset(e.Z.wpadminbar),window.objectFitImages&&window.objectFitImages(),(0,a.Z)(),(0,l.Z)(),(0,d.Z)(document),(0,r.Z)(),(0,s.Z)(),(0,c.Z)()})),window.addEventListener("load",(function(){if(document.documentElement.setAttribute("data-loaded","true"),o.IW.headH(e.Z.header),o.IW.smoothOffset(e.Z.wpadminbar),(0,i.B)(),u){const e=u.replace("#",""),t=document.getElementById(e);null!==t&&(0,i.l)(t,o.Z8)}})),window.addEventListener("orientationchange",(function(){setTimeout((()=>{o.IW.mediaSize(),o.IW.headH(e.Z.header),o.IW.smoothOffset(e.Z.wpadminbar),(0,a.Z)()}),5)})),window.addEventListener("resize",(function(){setTimeout((()=>{o.IW.scrollbarW(),o.IW.mediaSize(),o.IW.headH(e.Z.header),o.IW.smoothOffset(e.Z.wpadminbar),(0,a.Z)()}),5)}))})()})();