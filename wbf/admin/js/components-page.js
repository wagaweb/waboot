jQuery(document).ready(function ($) {
    "use strict";
    $("#component-framework-page-nav a").on("click",function(){
        var $selected_component_div = $('#'+$(this).attr("data-show-comp-settings"));
        $("#component-framework-page-content .component-tab").hide();
        $selected_component_div.show();
    });
});
