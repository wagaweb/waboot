var WBToolbar = function WBToolbar(id, editor) {
    "use strict";
    var $ = jQuery;
    this.id = id;
    this.Editor = editor;
    this.$self = $("#" + this.id);
    //this.$self = $("[data-toolbar='"+editor.container_id+"']");
};

(function($) {
    "use strict";
    WBToolbar.prototype = {
        initialize: function() {
            this.enable_toolbar_actions();
        },
        disable_all_tools: function() {
            var Myself = this;
            this.$self.find(".pbtool").each(function() {
                var my_block = $(this).data("add");
                Myself.disable_tool(my_block);
            });
        },
        enable_tool: function(blockname) {
            var $element = $(".pbtool[data-add=" + blockname + "]");
            var $my_parent_menu = $element.parent(".wb-pb-toolsmenu");
            $element.removeClass("disabled").removeAttr("disabled");
            if ($my_parent_menu.length > 0) {
                $my_parent_menu.removeClass("disabled").removeAttr("disabled");
            }
        },
        disable_tool: function(blockname) {
            var $element = $(".pbtool[data-add=" + blockname + "]");
            var $my_parent_menu = $element.parent(".wb-pb-toolsmenu");
            $element.addClass("disabled");
            if ($element.is("option")) {
                $element.attr("disabled", "disabled");
            }
            if ($my_parent_menu.length > 0) {
                var tot_options = 0;
                var options_disabled = 0;
                $my_parent_menu.find("option").each(function() {
                    if ($(this).attr("value") !== "label") {
                        tot_options++;
                        if ($(this).hasClass("disabled")) {
                            options_disabled++;
                        }
                    }
                });
                if (tot_options === options_disabled) {
                    $my_parent_menu.addClass("disabled").attr("disabled", "disabled");
                }
            }
        },
        enable_toolbar_actions: function() {
            var Myself = this;
            //Enable adding blocks
            $(document).on("click", "#" + Myself.Editor.container_id + " a.pbtool", function(e) {
                e.preventDefault();
                Myself.Editor.maybe_add_block($(this));
            });
            $(document).on("change", "#" + Myself.Editor.container_id + " select.wb-pb-toolsmenu", function(e) {
                e.preventDefault();
                var $selected = $(this).find(":selected");
                Myself.Editor.maybe_add_block($selected);
                $(this).prop('selectedIndex', 0); //reset the selected index
            });
        },
        disable_toolbar_actions: function() {
            var Myself = this;
            $(document).off("click", "#" + Myself.Editor.container_id + " a.pbtool");
            $(document).off("change", "#" + Myself.Editor.container_id + " select.wb-pb-toolsmenu");
        }
    };
})(jQuery);
