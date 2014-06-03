jQuery(document).ready(function($) {
    jQuery('ul.nav li.dropdown, ul.nav li.dropdown-submenu').hover(function() {
        jQuery(this).find(' > .dropdown-menu').stop(true, true).delay(200).fadeIn();
    }, function() {
        jQuery(this).find(' > .dropdown-menu').stop(true, true).delay(200).fadeOut();
    });

    jQuery('a[data-toggle]').on("click", function() {
        var url = jQuery(this).attr("href");
        window.location = url;
    });
});

function enableDropDown($, window, delay) {
    // http://jsfiddle.net/AndreasPizsa/NzvKC/
    var theTimer = 0;
    var theElement = null;
    var theLastPosition = {
        x: 0,
        y: 0
    };
    $('[data-toggle="dropdown"]')
        .closest('li')
        .on('mouseenter', function(inEvent) {
            if (theElement) theElement.removeClass('open');
            window.clearTimeout(theTimer);
            theElement = $(this);

            theTimer = window.setTimeout(function() {
                theElement.addClass('open');
            }, delay);
        })
        .on('mousemove', function(inEvent) {
            if (Math.abs(theLastPosition.x - inEvent.ScreenX) > 4 ||
                Math.abs(theLastPosition.y - inEvent.ScreenY) > 4) {
                theLastPosition.x = inEvent.ScreenX;
                theLastPosition.y = inEvent.ScreenY;
                return;
            }

            if (theElement.hasClass('open')) return;
            window.clearTimeout(theTimer);
            theTimer = window.setTimeout(function() {
                theElement.addClass('open');
            }, delay);
        })
        .on('mouseleave', function(inEvent) {
            window.clearTimeout(theTimer);
            theElement = $(this);
            theTimer = window.setTimeout(function() {
                theElement.removeClass('open');
            }, delay);
        });
}

//http://getbootstrap.com/getting-started/#support
if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
    var msViewportStyle = document.createElement('style')
    msViewportStyle.appendChild(
        document.createTextNode(
            '@-ms-viewport{width:auto!important}'
        )
    );
    document.querySelector('head').appendChild(msViewportStyle);
}

jQuery(document).ready(function() {
    // Style form controls
    jQuery(type = "select").addClass('form-control input-sm');
    jQuery('input#submit').addClass('btn btn-default');
    // Tables
    jQuery('table').addClass('table');
});
jQuery(document).ready(function() {
    jQuery.ajax(
        '/waboot/wp-admin/admin-ajax.php', {
            action: "waboot_needs_to_compile",
            data: {
                "action": "waboot_needs_to_compile"
            },
            success: function(data, textStatus, jqXHR) {
                console.log("Ris: " + data);
                if (parseInt(data) === 1) {
                    console.log("Devo compilare i Less");
                    var overlay = jQuery('<div id="less-overlay"></div><div id="less-overlay-content"><p>Compiling Less Files...</p></div>');
                    overlay.appendTo(document.body);
                    jQuery('#less-overlay-content').center();
                    jQuery.ajax('/waboot/wp-admin/admin-ajax.php', {
                        action: "waboot_compile",
                        data: {
                            "action": "waboot_compile"
                        },
                        success: function(data, textStatus, jqXHR) {
                            console.log("Ris: " + data);
                            jQuery("#less-overlay-content").html('<p>Completed!</p><p><a href="#" onclick="location.reload();">Click here to reload</a></p>');
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log("errore!");
                        }
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log("errore!");
            }
        }
    );
});

jQuery.fn.center = function() {
    this.css("position", "absolute");
    this.css("top", Math.max(0, ((jQuery(window).height() - jQuery(this).outerHeight()) / 2) +
        jQuery(window).scrollTop()) + "px");
    this.css("left", Math.max(0, ((jQuery(window).width() - jQuery(this).outerWidth()) / 2) +
        jQuery(window).scrollLeft()) + "px");
    return this;
};
