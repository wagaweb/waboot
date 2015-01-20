jQuery(document).ready(function($){
    "use strict";
    if(wabootCbox.elements !== false){
        var elements = "";
        var cboxProps = {
            maxWidth: "90%",
            maxHeight: "90%",
            close: "",
            previous: "",
            next: "",
            current: wabootCbox.current
        };
        switch(wabootCbox.elements){
            case "galleries":
                elements = ".gallery-item a";
                cboxProps.rel = "gallery";
                break;
            case "custom":
                if(wabootCbox.custom_elements !== false){
                    elements = wabootCbox.custom_elements;
                }else{
                    elements = "a.img-colorbox";
                }
                break;
            case "all-images":
                elements = "a.img-colorbox";
                cboxProps.rel = "cboximg";
                break;
            default:
                elements = "a.img-colorbox";
                cboxProps.rel = "cboximg";
                break;
        }
        $(""+elements+"").colorbox(cboxProps);
    }
});