jQuery(document).ready(function(){
    jQuery.ajax(
        waboot.ajax_url,{
            action : "waboot_needs_to_compile",
            data: {
                "action" : "waboot_needs_to_compile"
            },
            success: function(data, textStatus, jqXHR){
                console.log("Ris: "+data+" | "+textStatus+" | "+jqXHR);
                if(parseInt(data) === 1){
                    console.log("Devo compilare i Less");
                    var overlay = jQuery('<div id="less-overlay"></div><div id="less-overlay-content"><p>Compiling Less Files...</p></div>');
                    overlay.appendTo(document.body);
                    jQuery('#less-overlay-content').center();
                    jQuery.ajax(waboot.ajax_url,{
                            action : "waboot_compile",
                            data: {
                                "action" : "waboot_compile"
                            },
                            success: function(data, textStatus, jqXHR){
                                console.log("Ris: "+data+" | "+textStatus+" | "+jqXHR);
                                jQuery("#less-overlay-content").html('<p>Completed!</p><p><a href="#" onclick="location.reload();">Click here to reload</a></p>');
                            },
                            error: function(jqXHR, textStatus, errorThrown){
                                console.log("Errore: "+jqXHR+" | "+textStatus+" | "+errorThrown);
                                jQuery("#less-overlay-content").html('<p>Error!</p><p>'+errorThrown+'</p>');
                            }
                        }
                    );
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                console.log("Errore: "+jqXHR+" | "+textStatus+" | "+errorThrown);
            }
        }
    );
});

jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", Math.max(0, ((jQuery(window).height() - jQuery(this).outerHeight()) / 2) +
        jQuery(window).scrollTop()) + "px");
    this.css("left", Math.max(0, ((jQuery(window).width() - jQuery(this).outerWidth()) / 2) +
        jQuery(window).scrollLeft()) + "px");
    return this;
};