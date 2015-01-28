jQuery(document).ready(function($){
    "use strict";
    var elements = "";
    var cboxProps = {
        maxWidth: "90%",
        maxHeight: "90%",
        close: "",
        previous: "",
        next: "",
        current: wabootCbox.current
    };
    if(wabootCbox.elements !== false){
        switch(wabootCbox.elements){
            case "custom":
                //Do nothing :)
                break;
            case "galleries":
                elements = ".gallery-item a";
                cboxProps.rel = "gallery";
                $(""+elements+"").colorbox(cboxProps);
                break;
            case "all-images":
                //Add colorbox to all link that target an image
                $("a").each(function(){
                    var my_href = $(this).attr("href");
                    if(/\.(?:jpg|jpeg|gif|png)/i.test(my_href)){
                        $(this).colorbox(cboxProps);
                    }else{
                        var my_img = $(this).find("img");
                        if(my_img.lenght > 0 && my_img.hasClass("img-colorbox")){ //Add colorbox to all images with class img-colorbox
                            $(this).colorbox(cboxProps);
                        }
                    }
                });
                //Add colorbox to all link that has class img-colorbox
                elements = "a.img-colorbox";
                cboxProps.rel = "cboximg";
                $(""+elements+"").colorbox(cboxProps);
                //Add colorbox to galleries
                elements = ".gallery-item a";
                cboxProps.rel = "gallery";
                $(""+elements+"").colorbox(cboxProps);
                break;
            default:
                //Add colorbox to images
                elements = "a.img-colorbox";
                cboxProps.rel = "cboximg";
                $(""+elements+"").colorbox(cboxProps);
                //Add colorbox to galleries
                elements = ".gallery-item a";
                cboxProps.rel = "gallery";
                $(""+elements+"").colorbox(cboxProps);
                break;
        }
    }
    if(wabootCbox.custom_elements !== false && wabootCbox.custom_elements !== ""){
        elements = wabootCbox.custom_elements;
        cboxProps.rel = "cboximg";
        $(""+elements+"").colorbox(cboxProps);
    }
});