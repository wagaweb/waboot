jQuery(document).ready(function(){
    jQuery.ajax(
        '/waboot/wp-admin/admin-ajax.php',{
            action : "waboot_needs_to_compile",
            data: {
                "action" : "waboot_needs_to_compile"
            },
            success: function(data, textStatus, jqXHR){
                console.log("Ris: "+data);
                if(parseInt(data) === 1){
                    console.log("Devo compilare i Less");
                    jQuery.ajax('/waboot/wp-admin/admin-ajax.php',{
                            action : "waboot_compile",
                            data: {
                                "action" : "waboot_compile"
                            },
                            success: function(data, textStatus, jqXHR){
                                console.log("Ris: "+data);
                            },
                            error: function(jqXHR, textStatus, errorThrown){
                                console.log("errore!");
                            }
                        }
                    );
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                console.log("errore!");
            }
        }
    );
});
