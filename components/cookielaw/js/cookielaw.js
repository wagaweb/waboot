jQuery(window).load(function(){
    "use strict";
    var $ = jQuery,
        cookieName = 'displayCookieConsent', //The same of cookieChoices lib
        $cookieItems = $("[data-cookieonly]"),
        $dismissBtn = $("#cookieChoiceDismiss"),
        data = cookielawData || {
                str: 'Cookies help us deliver our services. By continuing to use our website, you agree to our use of cookies',
                close_str: 'OK',
                learnmore_str: 'Learn more',
                learnmore_url: 'http://example.com'
            };

    cookieChoices.showCookieConsentBar(data.str, data.close_str, data.learnmore_str, data.learnmore_url);

    if(!document.cookie.match(new RegExp(cookieName + '=([^;]+)'))){ //from cookieChoices lib
        $cookieItems.each(function(){
            $(this).html("");
        });
    }

    $dismissBtn.bind("click",function(){
        location.reload();
    });
});
