(()=>{var e={n:t=>{var s=t&&t.__esModule?()=>t.default:()=>t;return e.d(s,{a:s}),s},d:(t,s)=>{for(var n in s)e.o(s,n)&&!e.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:s[n]})}};e.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(e){if("object"==typeof window)return window}}(),e.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{var t;e.g.importScripts&&(t=e.g.location+"");var s=e.g.document;if(!t&&s&&(s.currentScript&&(t=s.currentScript.src),!t)){var n=s.getElementsByTagName("script");n.length&&(t=n[n.length-1].src)}if(!t)throw new Error("Automatic publicPath is not supported in this browser");t=t.replace(/#.*$/,"").replace(/\?.*$/,"").replace(/\/[^\/]+$/,"/"),e.p=t})(),e.p=window.wcpayAssets.url,(()=>{"use strict";const t=e=>"undefined"!=typeof wcpayConfig?wcpayConfig[e]:s(e),s=e=>{let t=null;if("undefined"!=typeof wcpay_upe_config)t=wcpay_upe_config;else{if("object"!=typeof wc||void 0===wc.wcSettings)return null;t=wc.wcSettings.getSetting("woocommerce_payments_data")||{}}return t[e]||null};function n(e){let t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",s=arguments.length>2?arguments[2]:void 0;for(const o in e){const a=e[o],i=t?t+"["+o+"]":o;"string"==typeof a||"number"==typeof a?s.append(i,a):"object"==typeof a&&n(a,i,s)}return s}async function o(e,t,s){const o=n(t,"",new FormData),a=await fetch(e,{method:"POST",body:o,...s});return await a.json()}const a=e=>"object"==typeof wcpayPaymentRequestParams&&wcpayPaymentRequestParams.hasOwnProperty(e)?wcpayPaymentRequestParams[e]:null,i=window.wp.element,r=window.React,c=window.wp.i18n;window.wp.domReady;const l=()=>{return e=void 0,s=void 0,o=function*(){var e,s,n;let o=(()=>{const e=document.cookie.split(";");for(let t=0;t<e.length;t++){let s=e[t];for(;" "===s.charAt(0);)s=s.substring(1,s.length);if(0===s.indexOf("tk_ai="))return s.substring(6,s.length)}})();if(!o){const i=null!==(e=t("platformTrackerNonce"))&&void 0!==e?e:null===(s=a("nonce"))||void 0===s?void 0:s.platform_tracker,r=null!==(n=t("ajaxUrl"))&&void 0!==n?n:a("ajax_url"),c=new FormData;c.append("tracksNonce",i),c.append("action","get_identity");try{const e=yield fetch(r,{method:"post",body:c});if(!e.ok)return;const t=yield e.json();if(!t.success||!t.data)return;o=t.data._ui}catch(e){return}}const i={_ut:"anon",_ui:o};return JSON.stringify(i)},new((n=void 0)||(n=Promise))((function(t,a){function i(e){try{c(o.next(e))}catch(e){a(e)}}function r(e){try{c(o.throw(e))}catch(e){a(e)}}function c(e){var s;e.done?t(e.value):(s=e.value,s instanceof n?s:new n((function(e){e(s)}))).then(i,r)}c((o=o.apply(e,s||[])).next())}));var e,s,n,o};function d(e){window.WooPayConnect||(window.WooPayConnect={}),window.WooPayConnect.iframeInjectedState=e}const u=()=>{const e=(0,r.useRef)(),[s,n]=(0,r.useState)("");return(0,r.useEffect)((()=>{(async()=>{const e=t("testMode"),s=t("woopayHost"),o=new URLSearchParams({testMode:e,source_url:window.location.href}),a=await l();a&&o.append("tracksUserIdentity",a),n(`${s}/connect/?${o.toString()}`)})()}),[]),(0,r.useEffect)((()=>{if(!e.current)return;const s=e.current;s.addEventListener("load",(()=>{d(2),window.dispatchEvent(new MessageEvent("message",{source:window,origin:t("woopayHost"),data:{action:"get_iframe_post_message_success",value:e=>s.contentWindow.postMessage(e,t("woopayHost"))}}))}))}),[s]),(0,i.createElement)("iframe",{ref:e,id:"woopay-connect-iframe",src:s,style:{height:0,width:0,border:"none",margin:0,padding:0,overflow:"hidden",display:"block",visibility:"hidden",position:"fixed",pointerEvents:"none",userSelect:"none"},title:(0,c.__)("WooPay Connect Direct Checkout","woocommerce-payments")})},h=window.ReactDOM;var y=e.n(h);const w=class{iframePostMessage=null;listeners={};constructor(){this.listeners={getIframePostMessageCallback:()=>{}},this.removeMessageListener=this.attachMessageListener(),this.injectWooPayConnectIframe()}attachMessageListener(){const e=e=>{t("woopayHost").startsWith(e.origin)&&this.callbackFn(e.data)};return window.addEventListener("message",e),()=>{window.removeEventListener("message",e)}}detachMessageListener(){"function"==typeof this.removeMessageListener&&this.removeMessageListener()}injectWooPayConnectIframe(){const e=(null===(s=window)||void 0===s||null===(n=s.WooPayConnect)||void 0===n?void 0:n.iframeInjectedState)||0;var s,n;if(2===e){const e=document.querySelector("#woopay-connect-iframe");return void(e&&(this.iframePostMessage=Promise.resolve((s=>{e.contentWindow.postMessage(s,t("woopayHost"))}))))}if(1===e)return void(this.iframePostMessage=new Promise((e=>{this.listeners.getIframePostMessageCallback=e})));d(1);const o=document.createElement("div");o.style.visibility="hidden",o.style.position="fixed",o.style.height="0",o.style.width="0",o.style.bottom="0",o.style.right="0",o.id="woopay-connect-iframe-container",document.body.appendChild(o);const a=this;this.iframePostMessage=new Promise((e=>{a.listeners.getIframePostMessageCallback=e})),y().render((0,i.createElement)(u,null),o)}injectTemporaryWooPayConnectIframe(){let e;const s=new Promise((t=>{e=t})),n=document.createElement("iframe");return n.id="temp-woopay-connect-iframe",n.src=t("woopayHost")+"/connect/",n.height=0,n.width=0,n.border="none",n.margin=0,n.padding=0,n.overflow="hidden",n.display="block",n.visibility="hidden",n.position="fixed",n.pointerEvents="none",n.userSelect="none",n.addEventListener("load",(()=>{e((e=>n.contentWindow.postMessage(e,t("woopayHost"))))})),document.body.appendChild(n),{resolvePostMessagePromise:s,removeTemporaryIframe:()=>{document.body.removeChild(n)}}}async sendMessageAndListenWith(e,t){const s=new Promise((e=>{this.listeners[t]=e}));return(await this.iframePostMessage)(e),await s}callbackFn(e){"get_iframe_post_message_success"===e.action&&this.listeners.getIframePostMessageCallback(e.value)}},p=class extends w{constructor(){super(),this.listeners={...this.listeners,getIsUserLoggedInCallback:()=>{}}}async isUserLoggedIn(){return await this.sendMessageAndListenWith({action:"getIsUserLoggedIn"},"getIsUserLoggedInCallback")}callbackFn(e){super.callbackFn(e),"get_is_user_logged_in_success"===e.action&&this.listeners.getIsUserLoggedInCallback(e.value)}},g=class extends w{constructor(){super(),this.listeners={...this.listeners,setRedirectSessionDataCallback:()=>{},setTempThirdPartyCookieCallback:()=>{},getIsThirdPartyCookiesEnabledCallback:()=>{},setPreemptiveSessionDataCallback:()=>{}}}async isWooPayThirdPartyCookiesEnabled(){const{resolvePostMessagePromise:e,removeTemporaryIframe:t}=this.injectTemporaryWooPayConnectIframe(),s=new Promise((e=>{this.listeners.setTempThirdPartyCookieCallback=e})),n=await e;if(n({action:"setTempThirdPartyCookie"}),!await s)return!1;const o=new Promise((e=>{this.listeners.getIsThirdPartyCookiesEnabledCallback=e}));n({action:"getIsThirdPartyCookiesEnabled"});const a=await o;return t(),a}async sendRedirectSessionDataToWooPay(e){return await super.sendMessageAndListenWith({action:"setRedirectSessionData",value:e},"setRedirectSessionDataCallback")}async setPreemptiveSessionData(e){return await super.sendMessageAndListenWith({action:"setPreemptiveSessionData",value:e},"setPreemptiveSessionDataCallback")}callbackFn(e){switch(super.callbackFn(e),e.action){case"set_redirect_session_data_success":this.listeners.setRedirectSessionDataCallback(e.value);break;case"set_redirect_session_data_error":this.listeners.setRedirectSessionDataCallback({is_error:!0});break;case"set_temp_third_party_cookie_success":this.listeners.setTempThirdPartyCookieCallback(e.value);break;case"get_is_third_party_cookies_enabled_success":this.listeners.getIsThirdPartyCookiesEnabledCallback(e.value);break;case"set_preemptive_session_data_success":this.listeners.setPreemptiveSessionDataCallback(e.value);break;case"set_preemptive_session_data_error":this.listeners.setPreemptiveSessionDataCallback({is_error:!0})}}},m=class{static userConnect;static sessionConnect;static init(){this.getSessionConnect()}static getUserConnect(){return this.userConnect||(this.userConnect=new p),this.userConnect}static getSessionConnect(){return this.sessionConnect||(this.sessionConnect=new g),this.sessionConnect}static teardown(){var e,t;null===(e=this.sessionConnect)||void 0===e||e.detachMessageListener(),null===(t=this.userConnect)||void 0===t||t.detachMessageListener(),this.sessionConnect=null,this.userConnect=null}static isWooPayEnabled(){return t("isWooPayEnabled")}static async isUserLoggedIn(){return this.getUserConnect().isUserLoggedIn()}static async isWooPayThirdPartyCookiesEnabled(){return this.getSessionConnect().isWooPayThirdPartyCookiesEnabled()}static async resolveWooPayRedirectUrl(){try{const e=await this.getEncryptedSessionData();if(!this.isValidEncryptedSessionData(e))throw new Error("Could not retrieve encrypted session data from store.");const t=await this.getSessionConnect().sendRedirectSessionDataToWooPay(e);if(null==t||!t.redirect_url)throw new Error("Could not retrieve WooPay checkout URL.");return t.redirect_url}catch(e){throw new Error(e.message)}}static isValidEncryptedSessionData(e){var t,s,n;return e&&(null==e?void 0:e.blog_id)&&(null==e||null===(t=e.data)||void 0===t?void 0:t.session)&&(null==e||null===(s=e.data)||void 0===s?void 0:s.iv)&&(null==e||null===(n=e.data)||void 0===n?void 0:n.hash)}static getCheckoutRedirectElements(){const e=[],t=t=>{const s=document.querySelector(t);s&&e.push(s)};return t(".wc-proceed-to-checkout .checkout-button"),t(".wp-block-woocommerce-proceed-to-checkout-block a"),e}static redirectToWooPay(e,t){e.forEach((e=>{e.addEventListener("click",(async e=>{const s=e.currentTarget.href;if(s){e.preventDefault();try{let e=await this.resolveWooPayRedirectUrl();t&&(e+="&checkout_redirect=1"),this.teardown(),window.location.href=e}catch(e){console.warn(e),this.teardown(),window.location.href=s}}else this.teardown()}))}))}static async getEncryptedSessionData(){return o(function(e,t){let s=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"wcpay_";return e.toString().replace("%%endpoint%%",s+t)}(t("wcAjaxUrl"),"get_woopay_session"),{_ajax_nonce:t("woopaySessionNonce")})}};window.addEventListener("load",(async()=>{if(!m.isWooPayEnabled())return;m.init();const e=await m.isWooPayThirdPartyCookiesEnabled(),t=m.getCheckoutRedirectElements();e?await m.isUserLoggedIn()&&m.redirectToWooPay(t,!1):m.redirectToWooPay(t,!0)}))})()})();