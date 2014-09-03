jQuery(document).ready(function($) {
    jQuery('ul.nav li.dropdown, ul.nav li.dropdown-submenu').hover(function() {
        jQuery(this).find(' > .dropdown-menu').stop(true, true).delay(200).fadeIn();
    }, function() {
        jQuery(this).find(' > .dropdown-menu').stop(true, true).delay(200).fadeOut();
    });

    jQuery('a[data-toggle]').on("click",function(){
        var url = jQuery(this).attr("href");
        window.location = url;
    });
});
