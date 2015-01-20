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
                //Add colorbox to images
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