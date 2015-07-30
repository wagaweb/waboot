module.exports = {
    init_interface: function(){
        jQuery(document).ready(function ($) {
            "use strict";
            $(".nav-tab-wrapper a").on("click",function(){
                var $selected_component_div = $('#'+$(this).attr("data-show-comp-settings"));
                $("#componentframework-metabox .group").hide();
                $selected_component_div.show();
            });
        });
    }
};
