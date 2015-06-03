/*
 Copyright 2014 Google Inc. All rights reserved.

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
 */

(function(window) {

  if (!!window.cookieChoices) {
    return window.cookieChoices;
  }

  var document = window.document;
  // IE8 does not support textContent, so we should fallback to innerText.
  //var supportsTextContent = 'textContent' in document.body;
  var supportsTextContent = true;

  var cookieChoices = (function() {

    var cookieName = 'displayCookieConsent';
    var cookieConsentId = 'cookieChoiceInfo';
    var dismissLinkId = 'cookieChoiceDismiss';

    function _createHeaderElement(cookieText, dismissText, linkText, linkHref) {
      //  var butterBarStyles = 'position:fixed;width:100%;background-color:#eee;' +
      //    'margin:0; left:0; top:0;padding:4px;z-index:1000;text-align:center;';

      var cookieConsentElement = document.createElement('div');
      cookieConsentElement.id = cookieConsentId;
      // cookieConsentElement.style.cssText = butterBarStyles;
      cookieConsentElement.appendChild(_createConsentText(cookieText));

      if (!!linkText && !!linkHref) {
        cookieConsentElement.appendChild(_createInformationLink(linkText, linkHref));
      }
      cookieConsentElement.appendChild(_createDismissLink(dismissText));
      return cookieConsentElement;
    }

    function _createDialogElement(cookieText, dismissText, linkText, linkHref) {
      var glassStyle = 'position:fixed;width:100%;height:100%;z-index:999;' +
          'top:0;left:0;opacity:0.5;filter:alpha(opacity=50);' +
          'background-color:#ccc;';
      var dialogStyle = 'z-index:1000;position:fixed;left:50%;top:50%';
      var contentStyle = 'position:relative;left:-50%;margin-top:-25%;' +
          'background-color:#fff;padding:20px;box-shadow:4px 4px 25px #888;';

      var cookieConsentElement = document.createElement('div');
      cookieConsentElement.id = cookieConsentId;

      var glassPanel = document.createElement('div');
      glassPanel.style.cssText = glassStyle;

      var content = document.createElement('div');
      content.style.cssText = contentStyle;

      var dialog = document.createElement('div');
      dialog.style.cssText = dialogStyle;

      var dismissLink = _createDismissLink(dismissText);
      dismissLink.style.display = 'block';
      dismissLink.style.textAlign = 'right';
      dismissLink.style.marginTop = '8px';

      content.appendChild(_createConsentText(cookieText));
      if (!!linkText && !!linkHref) {
        content.appendChild(_createInformationLink(linkText, linkHref));
      }
      content.appendChild(dismissLink);
      dialog.appendChild(content);
      cookieConsentElement.appendChild(glassPanel);
      cookieConsentElement.appendChild(dialog);
      return cookieConsentElement;
    }

    function _setElementText(element, text) {
      if (supportsTextContent) {
        //element.textContent = text;
        element.innerHTML = text;
      } else {
        element.innerText = text;
      }
    }

    function _createConsentText(cookieText) {
      var consentText = document.createElement('span');
      _setElementText(consentText, cookieText);
      return consentText;
    }

    function _createDismissLink(dismissText) {
      var dismissLink = document.createElement('a');
      _setElementText(dismissLink, dismissText);
      dismissLink.id = dismissLinkId;
      dismissLink.href = '#';
      // dismissLink.style.marginLeft = '24px';
      dismissLink.style.marginLeft = '10px';
      return dismissLink;
    }

    function _createInformationLink(linkText, linkHref) {
      var infoLink = document.createElement('a');
      _setElementText(infoLink, linkText);
      infoLink.href = linkHref;
      //infoLink.target = '_blank';
      infoLink.target = '_self';
      infoLink.style.marginLeft = '8px';
      return infoLink;
    }

    function _dismissLinkClick() {
      _saveUserPreference();
      _removeCookieConsent();
      return false;
    }

    function _showCookieConsent(cookieText, dismissText, linkText, linkHref, isDialog) {
      if (_shouldDisplayConsent()) {
        _removeCookieConsent();
        var consentElement = (isDialog) ?
            _createDialogElement(cookieText, dismissText, linkText, linkHref) :
            _createHeaderElement(cookieText, dismissText, linkText, linkHref);
        var fragment = document.createDocumentFragment();
        fragment.appendChild(consentElement);
        document.body.appendChild(fragment.cloneNode(true));
        document.getElementById(dismissLinkId).onclick = _dismissLinkClick;
      }
    }

    function showCookieConsentBar(cookieText, dismissText, linkText, linkHref) {
      _showCookieConsent(cookieText, dismissText, linkText, linkHref, false);
    }

    function showCookieConsentDialog(cookieText, dismissText, linkText, linkHref) {
      _showCookieConsent(cookieText, dismissText, linkText, linkHref, true);
    }

    function _removeCookieConsent() {
      var cookieChoiceElement = document.getElementById(cookieConsentId);
      if (cookieChoiceElement != null) {
        cookieChoiceElement.parentNode.removeChild(cookieChoiceElement);
      }
    }

    function _saveUserPreference() {
      // Set the cookie expiry to one year after today.
      var expiryDate = new Date();
      expiryDate.setFullYear(expiryDate.getFullYear() + 1);
      document.cookie = cookieName + '=y; expires=' + expiryDate.toGMTString();
    }

    function _shouldDisplayConsent() {
      // Display the header only if the cookie has not been set.
      return !document.cookie.match(new RegExp(cookieName + '=([^;]+)'));
    }

    var exports = {};
    exports.showCookieConsentBar = showCookieConsentBar;
    exports.showCookieConsentDialog = showCookieConsentDialog;
    return exports;
  })();

  window.cookieChoices = cookieChoices;
  return cookieChoices;
})(this);

(function(cookieChoices){
    "use strict";
    var cookieName = 'displayCookieConsent'; //The same of cookieChoices lib
    document.addEventListener('DOMContentLoaded', function(event) {
        var cookieItems = document.querySelectorAll('[data-cookieonly]'),
            scrollFlag = false,
            data = cookielawData || {
                    str: 'Questo sito utilizza cookie, anche di terze parti, per fornire servizi in linea con le tue preferenze. Utilizzando i nostri servizi, l\'utente accetta le nostre modalità d`uso dei cookie.',
                    close_str: 'Ok',
                    learnmore_str: 'Ulteriori informazioni',
                    learnmore_url: '/cookie-policy/',
                    saveonscroll: '1',
                    scroll_limit: '60'
                };

        if(!cookieIsPresent()){
            //Hide cookie items content
            for (var i = 0; i < cookieItems.length; ++i) {
                cookieItems[i].innerHTML = "";
            }

            //Show bar
            cookieChoices.showCookieConsentBar(data.str, data.close_str, data.learnmore_str, data.learnmore_url);

            //Add classes to GUI elements
            stylize();

            //Bind click event
            document.querySelector("#cookieChoiceDismiss").addEventListener("click", function(){
                location.reload();
            });

            //Bind scroll event
            if(!location.href.match(/data.learnmore_url/) && Boolean(parseInt(data.saveonscroll))){
                window.addEventListener("scroll",function(e){
                    if(window.scrollY >= parseInt(data.scroll_limit) && !scrollFlag){
                        scrollFlag = true;
                        setCookie();
                        location.reload();
                    }
                });
            }
        }
    });

    function stylize(){
        var body = document.querySelector("body"),
            cookieChoiceInfo = document.querySelector("#cookieChoiceInfo"),
            cookieChoiceDismiss = document.querySelector("#cookieChoiceDismiss");

        if(body.classList){
            body.classList.add("cookiebanner");
        }else{
            body.className += ' ' + "cookiebanner";
        }

        if(cookieChoiceInfo.classList){
            cookieChoiceInfo.classList.add("cookiebanner-slideInUp");
        }else{
            cookieChoiceInfo.className += ' ' + "cookiebanner-slideInUp";
        }

        if(cookieChoiceDismiss.classList){
            cookieChoiceDismiss.classList.add("btn");
            cookieChoiceDismiss.classList.add("btn-sm");
            cookieChoiceDismiss.classList.add("btn-primary");
        }else{
            body.className += ' ' + "btn btn-primary";
        }
    }

    function cookieIsPresent(){
        return document.cookie.match(new RegExp(cookieName + '=([^;]+)')); //from cookieChoices lib
    }

    function setCookie(){
        // Set the cookie expiry to one year after today.
        var expiryDate = new Date();
        expiryDate.setFullYear(expiryDate.getFullYear() + 1);
        document.cookie = cookieName + '=y; expires=' + expiryDate.toGMTString();
    }
})(cookieChoices);