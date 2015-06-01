jQuery(window).load(function(){
    "use strict";
    var $ = jQuery,
        cookieName = 'displayCookieConsent', //The same of cookieChoices lib
        $cookieItems = $("[data-cookieonly]"),
        data = cookielawData || {
                str: 'Questo sito utilizza cookie, anche di terze parti, per fornire servizi in linea con le tue preferenze. Utilizzando i nostri servizi, l\'utente accetta le nostre modalità d`uso dei cookie.',
                close_str: 'OK',
                learnmore_str: 'Ulteriori informazioni',
                learnmore_url: '/cookie-policy/',
                saveonscroll: '1',
                scroll_limit: '60'
            };

    if(!location.href.match(/data.learnmore_url/)){
        cookieChoices.showCookieConsentBar(data.str, data.close_str, data.learnmore_str, data.learnmore_url);

        if(!document.cookie.match(new RegExp(cookieName + '=([^;]+)'))){ //from cookieChoices lib
            $cookieItems.each(function(){
                $(this).html("");
            });

            $("body").addClass("cookiebanner");

            $(document).on("click","#cookieChoiceDismiss",function(){
                location.reload();
            });

            if(Boolean(parseInt(data.saveonscroll))){
                $(window).scroll(function(){
                    if($(window).scrollTop() >= parseInt(data.scroll_limit)){
                        // Set the cookie expiry to one year after today.
                        var expiryDate = new Date();
                        expiryDate.setFullYear(expiryDate.getFullYear() + 1);
                        document.cookie = cookieName + '=y; expires=' + expiryDate.toGMTString();
                        location.reload();
                    }
                });
            }
        }
    }
});
