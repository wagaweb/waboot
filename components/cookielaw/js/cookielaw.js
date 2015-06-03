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