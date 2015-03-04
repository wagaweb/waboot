var WBEditor = function WBEditor(container_id,toolbar_id,blocks_cache_selector,content_cache_selector){
    "use strict";
    var $ = jQuery;
    this.container_id = container_id;
    this.toolbar_id = toolbar_id;
    this.$block_cache = $(blocks_cache_selector);
    this.$content_cache = $(content_cache_selector);
};

(function($){
    "use strict";
    WBEditor.prototype = {
        /**
         * Initialize an editor instance
         * @param is_new (bool) this instance is a new instance? (default: true)
         * @param rebuild_cache (bool) rebuild the caches? (default: false)
         * @param register_block_actions (bool) register the blocks toolbar actions? (default: true)
         */
        initialize: function(is_new,rebuild_cache,register_block_actions){
            if(typeof(is_new)==='undefined'){is_new = true;}
            if(typeof(register_block_actions)==='undefined'){register_block_actions = true;}
            if(typeof(rebuild_cache)==='undefined'){rebuild_cache = false;}

            this.$container = $("#"+this.container_id);
            this.$main_block = this.$container.find("[data-block=main-container]");
            this.$edit_screen = this.$container.find("[data-editscreen]");
            this.$selected_element = ""; //Current selected element
            this.$editing_element = undefined;

            this.toolbar = new WBToolbar(this.toolbar_id,this);

            if(is_new){
                this.toolbar.initialize();
            }
            if(rebuild_cache){
                this.rebuild_cache();
            }
            this.toolbar.disable_all_tools();

            var Myself = this;
            this.enable_selection();
            this.enable_sorting();

            if(register_block_actions){
                this.$main_block.find("[data-block]").each(function(){
                    Myself.enable_block_toolbar_actions($(this));
                });
            }
        },
        /**
         * Returns the current selected element
         * @returns jQuery object
         */
        get_selected_element: function() {
            return this.$selected_element;
        },
        /**
         * Returns the HTML content of the editor
         * @returns {*}
         */
        get_content: function(){
            return this.$main_block.html();
        },
        /**
         * Set a new HTML content of the editor
         * @param content
         */
        set_content: function(content) {
            this.$main_block.html(content);
            this.content_cache_update();
        },
        /**
         * Assess the requisites to add a new block
         * @param $caller the jQuery obj that initiated the call to this function
         * @uses this.add_block
         */
        maybe_add_block: function($caller){
            var parent = this.$selected_element,
                my_block = $caller.attr("data-add"),
                can_add = false,
                parent_max_children = parseInt(parent.attr("data-max-children")),
                parent_children_number = this.get_children_number(parent);

            if($caller.is("option")){
                if (!$caller.hasClass("disabled") && $caller.attr("value") !== "label") {
                    can_add = true;
                }
            }else{
                if(!$caller.hasClass("disabled")) {
                    can_add = true;
                }
            }
            if(can_add){
                this.add_block(my_block, parent);
                parent_children_number++;
                if(parent_children_number === parent_max_children){
                    toolbar.disable_tool(my_block);
                }
            }
        },
        /**
         * Add a new block to the editor. It is called via this.maybe_add_block.
         * @param block_name the name of the block to add
         * @param $container the container in which to add the block
         * @param callback
         * @uses this.add_children()
         */
        add_block: function(block_name, $container, callback) {
            var parent_block = $container.attr("data-block"),
                parent_info = {},
                current_blocks = this.get_current_blocks(),
                new_block_id = block_name + "-" + this.get_next_available_id(),
                $new_block_obj = {};

            //Get parent info
            parent_info = wbpbData.blocks["" + parent_block + ""].info;
            if(!_.isEmpty(parent_info) && typeof(parent_info) !== "undefined"){
                //Get block and append
                $new_block_obj = $(wbpbData.blocks["" + block_name + ""].layout).attr("id", new_block_id); //create the new object
                if(!_.isEmpty($new_block_obj) && typeof($new_block_obj) !== "undefined"){
                    //adding the object to blocks list input
                    current_blocks["" + new_block_id + ""] = this.generate_block_object($(wbpbData.blocks["" + block_name + ""].layout), new_block_id);
                    this.blocks_cache_overwrite(current_blocks);
                    //adding the block to editor AND Register new mouse actions for current block (uses enable_sorting_and_selection)
                    this.add_children($new_block_obj, $container, parent_info.max_children_per_row);
                    //Calling the callback (if specified)
                    if(typeof callback !== "undefined"){
                        callback();
                    }
                    this.content_cache_update(); //Update editor content input
                }else{
                    console.log("Error: unable to find a correct block to add");
                }
            }else{
                console.log("Error: unable to find the info about container block");
            }
        },
        /**
         * Discard the current $block_cache and recalculate it, starting from blocks into this.$main_block
         */
        reset_current_blocks: function() {
            var Myself = this;
            var current_blocks = {};
            this.$main_block.find("[data-block]").each(function() {
                var block_name = $(this).attr("data-block");
                var new_block_id = block_name + "-" + Myself.get_next_available_id();
                $(this).attr("id", new_block_id); //assign the id to the block
                current_blocks["" + new_block_id + ""] = Myself.generate_block_object($(this), new_block_id);
            });
            Myself.blocks_cache_overwrite(current_blocks);
        },
        /**
         * Returns an object representation of a Block. Used by reset_current_blocks() and add_block()
         * @param element
         * @param id
         * @returns {{}}
         */
        generate_block_object: function(element, id) {
            var return_obj = {};
            return_obj.name = id;
            if (element.attr('data-colspan')) {
                return_obj.colspan = element.attr('data-colspan');
            }
            return return_obj;
        },
        /**
         * Returns the current value of $block_cache input
         * @returns {*}
         */
        get_blocks_cache: function(){
            return this.$block_cache.val();
        },
        /**
         * Returns the current value of $content_cache input
         * @returns {*}
         */
        get_content_cache: function(){
            return this.$content_cache.val();
        },
        /**
         * Returns the current editor blocks (from $block_cache)
         * @returns {*}
         */
        get_current_blocks: function() {
            var current_blocks = $.parseJSON(this.$block_cache.val());
            return current_blocks;
        },
        /**
         * Discard and rebuild content and blocks caches
         * @uses this.reset_current_blocks()
         * @uses this.content_cache_update()
         */
        rebuild_cache: function(){
            this.reset_current_blocks();
            this.content_cache_update();
        },
        /**
         * Reset the values of $block and $content _cache inputs
         */
        clear_cache: function(){
            this.$content_cache.val("");
            this.$block_cache.val("{}");
        },
        /**
         * Overwrite input#pbblocks with a new blocks
         * @param new_blocks (from: generate_block_object())
         */
        blocks_cache_overwrite: function(new_blocks) {
            var json = JSON.stringify(new_blocks, null, 2);
            this.$block_cache.val(json);
        },
        /**
         * Add a block from input#pbblocks
         * @param new_block_obj
         */
        blocks_cache_add: function(new_block_obj) {
            var current_blocks = this.get_current_blocks();
            current_blocks[new_block_obj.name] = new_block_obj;
            var json = JSON.stringify(current_blocks, null, 2);
            this.$block_cache.val(json);
        },
        /**
         * Remove a block from input#pbblocks
         * @param blockname
         */
        blocks_cache_remove: function(blockname) {
            var blocks = $.parseJSON(this.$block_cache.val());
            blocks = _.omit(blocks, blockname);
            var json = JSON.stringify(blocks, null, 2);
            this.$block_cache.val(json);
        },
        content_cache_update: function() {
            //Update editor content input
            var editorContent = this.$main_block.html();
            this.$content_cache.val(editorContent);
        },
        /**
         * Return a not used id for page builder element. Uses input#pbblocks as cache.
         * @returns {*}
         */
        get_next_available_id: function() {
            var current_blocks = this.get_current_blocks();
            var current_blocks_number = _.keys(current_blocks).length;
            if (current_blocks_number > 0) {
                return current_blocks_number + 1;
            } else {
                return 1;
            }
        },
        /**
         * Remove a block from the editor
         * @param element
         */
        remove_element: function(element) {
            var parent = element.parent("[data-block]");
            element.remove();
            this.decrease_children_number(parent);
            this.blocks_cache_remove(element.attr("id"));
            this.content_cache_update();
        },
        /**
         * Get children count of a block
         * @param element
         * @returns {Number|*}
         */
        get_children_number: function(element) {
            var children_number = parseInt(element.attr("data-children"));
            if (isNaN(children_number)) {
                children_number = 0;
                element.attr("data-children", "0");
            }
            return children_number;
        },
        /**
         * Increase children count of a block
         * @param $block
         */
        increase_children_number: function($block) {
            $block.attr("data-children", parseInt($block.attr("data-children")) + 1);
        },
        /**
         * Decrease children count of a block
         * @param $block
         */
        decrease_children_number: function($block) {
            var newnum = parseInt(element.attr("data-children")) - 1;
            if (newnum < 0) {
                newnum = 0;
            }
            $block.attr("data-children", newnum);
        },
        /**
         * Add a children to a block
         * @param $element the children $element (jQuery obj)
         * @param $container the parent $element (jQuery obj)
         * @param maxcols the max cols number of the parent @deprecated?
         * @param resize (bool) if must resize the childrens in the parent @deprecated?
         */
        add_children: function($element, $container, maxcols, resize) {
            var my_children_number = this.get_children_number($container);
            maxcols = wbpbData.blocks["" + $container.attr("data-block") + ""].info.max_children_per_row;
            resize = wbpbData.blocks["" + $container.attr("data-block") + ""].info.resize;
            resize = typeof resize !== 'undefined' ? resize : false;

            if (resize) {
                if (my_children_number === 0) {
                    $element.attr("data-colspan", maxcols);
                } else {
                    switch (my_children_number) {
                        case 1: //We adding the second $element
                            $container.children("[data-block]").each(function() {
                                $(this).attr("data-colspan", maxcols / 2);
                            });
                            $element.attr("data-colspan", maxcols / 2);
                            break;
                        case 2: //We adding the third $element
                            $container.children("[data-block]").each(function() {
                                $(this).attr("data-colspan", "1");
                            });
                            $element.attr("data-colspan", "1");
                            break;
                        default: //We adding the fourth and subsequent $elements
                            $container.children("[data-block]").each(function() {
                                $(this).attr("data-colspan", "1");
                            });
                            $element.attr("data-colspan", "1");
                            break;
                    }
                }
            } else {
                $element.attr("data-colspan", maxcols);
            }
            //Adds children counter to the parent
            this.increase_children_number($container);
            $container.append($element);
            this.enable_block_toolbar_actions($element);
            this.enable_sorting();
            //Select the new element
            this.make_selected($element);
        },
        /**
         * Handles the operation for opening the edit screen for specified block
         * @param $block
         * @uses this.show_edit_screen
         * @uses this.hide_edit_screen
         */
        open_edit_screen: function($block) {
            var Myself = this,
                block_name = $block.attr("data-block"),
                block_id = $block.attr("id"),
                my_values_json = $block.attr("data-options") || "{}",
                my_values = $.parseJSON(my_values_json);

            this.show_edit_screen(); //Display the edit screen
            this.toggle_loading(Myself.$edit_screen,"show");

            $.post(wbpbData.url, { //Gets block edit content..
                action: "pagebuilder_get_edit_screen",
                block_name: block_name
            }, function(data) {
                Myself.$edit_screen.attr("data-active-block", block_id);
                Myself.$edit_screen.attr("data-active-block-name", block_name);
                Myself.$edit_screen.html(data); //...and injects into the modal dialog
                //Puts previously inserted values into the widget input areas
                Myself.$edit_screen.find("[data-save]").each(function() {
                    var my_name = $(this).attr("name");
                    var value = my_values["" + my_name + ""];
                    if (!_.isUndefined(value)) {
                        $(this).val(value);
                    }
                });
                //If there are tmce textareas, init them
                Myself.$edit_screen.find("[data-is-tmce]").each(function(){
                    var my_id = $(this).attr("id");
                    initTinyMce($(this));
                    //window.tinymce.execCommand('mceAddEditor', true, my_id);
                });
                /*
                 * Register actions on submit and cancel
                 */
                $(document).on("click","[data-link-action='submit-edit']",function(){
                    Myself.save_edit_screen_data();
                });
                $(document).on("click","[data-link-action='close-edit']",function(){
                    Myself.hide_edit_screen();
                    Myself.$edit_screen.find("[data-save]").each(function() {
                        var element_id = $(this).attr("id");
                        if (_.has(window.tinymce.EditorManager.editors, element_id)) {
                            window.tinymce.execCommand('mceRemoveEditor', true, element_id);
                            delete window.tinyMCEPreInit.mceInit[element_id];
                            delete window.tinyMCEPreInit.qtInit[element_id];
                        }
                    });
                    Myself.$edit_screen.html("");
                });
                Myself.toggle_loading(Myself.$edit_screen,"hide");
            });
        },
        save_edit_screen_data: function() {
            var Myself = this,
                block_name = Myself.$edit_screen.attr("data-active-block-name"),
                block_id = Myself.$edit_screen.attr("data-active-block"),
                my_values = {},
                my_values_json = "{}";

            Myself.toggle_loading(Myself.$edit_screen,"show");

            Myself.$edit_screen.find("[data-save]").each(function() { //If the field in modal has [data-save] attribute, then get the value and save it in the corresponding editor block
                var ev = { //<--- current (e)lement (v)alues
                    "name": $(this).attr("name"),
                    "id": $(this).attr("id")
                };
                if (_.has(window.tinymce.EditorManager.editors, ev.id)) { //if current element is registerd into window.tinymce editors, then use getContent() function...
                    var tmce = window.tinymce.EditorManager.editors["" + ev.id + ""];
                    my_values["" + ev.name + ""] = tmce.getContent({
                        format: 'raw'
                    });
                    //...and remove the editor
                    window.tinymce.execCommand('mceRemoveEditor', true, ev.id);
                    delete window.tinyMCEPreInit.mceInit[ev.id];
                    delete window.tinyMCEPreInit.qtInit[ev.id];
                } else {
                    my_values["" + ev.name + ""] = $(this).val();
                }
            });

            //Encode the data of the edit screen
            ajax_json_encode(my_values,function(my_values_json){
                //Set the values into data-options of modified block
                Myself.$editing_element.attr("data-options", my_values_json);

                //If preview is enabled, then update the preview
                if (wbpbData.blocks[block_name].info.preview) {
                    var $preview_container = Myself.$editing_element.find(wbpbData.blocks[block_name].info.preview_to),
                        preview_content = "";

                    if (wbpbData.blocks[block_name].info.preview_from_field !== "") {
                        preview_content = my_values[wbpbData.blocks[block_name].info.preview_from_field];
                        create_excerpt(preview_content, 12, "...", function(excerpt){
                            $preview_container.html(excerpt);
                        },function(){
                            close_edit_and_update();
                        });
                    }else{
                        close_edit_and_update();
                    }
                }
            });

            /**
             * Encode string to JSON via Ajax Call
             * @param values
             * @returns {string}
             * @param success_callback
             */
            function ajax_json_encode(values,success_callback) {
                var json = "{}";
                $.ajax(wbpbData.url, {
                    data: {
                        action: "JSON_encode",
                        array: values
                    },
                    type: "POST",
                    complete: function(jqXHR, textStatus) {},
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert("Error!");
                    },
                    success: function(data, textStatus, jqXHR) {
                        console.log(data);
                        json = data || "{}";
                        success_callback(json);
                    }
                });
                return json;
            }

            /**
             * Create an excerpt of a specified content. Uses the native wordpress function called via Ajax.
             * @param content
             * @param length
             * @param more_txt
             * @returns {string}
             * @param complete_callback
             * @param success_callback
             */
            function create_excerpt(content, length, more_txt, success_callback, complete_callback) {
                /*content = content.replace(/< /?[^>]+>/gi, '');
                 content = $.trim(content);
                 content = content.substring(0,length)+more_txt;
                 return content;*/
                var excerpt = "";
                $.ajax(wbpbData.url, {
                    data: {
                        action: "create_excerpt",
                        text: content
                    },
                    type: "POST",
                    complete: function (jqXHR, textStatus) {
                        complete_callback();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert("Error!");
                    },
                    success: function (data, textStatus, jqXHR) {
                        console.log(data);
                        excerpt = data || "";
                        success_callback(excerpt);
                    }
                });
                return excerpt;
            }

            function close_edit_and_update(){
                Myself.content_cache_update(); //Update editor content input
                //Close the widget
                Myself.toggle_loading(Myself.$edit_screen,"hide");
                Myself.hide_edit_screen();
            }
        },
        enable_toolbar: function(){
            if(this.toolbar){
                this.toolbar.initialize();
            }
        },
        enable_block_toolbar_actions: function($block){
            var Myself = this;
            var block_id = $block.attr("id");

            $block.find(".tools:first a").each(function(){
                /*
                 * DELETE
                 */
                if($(this).hasClass("remove")){
                    $(this).on("click", function(e) {
                        e.stopImmediatePropagation();
                        e.preventDefault();
                        Myself.remove_element($block);
                    });
                }
                /*
                 * CLONE
                 */
                if($(this).hasClass("clone")){
                    $(this).on("click", function(e) {
                        e.preventDefault();
                        var block_name = $block.attr("data-block");
                        var $parent = $block.parent("[data-block]");
                        var parent_children_number = Myself.get_children_number($parent);
                        var parent_max_children = parseInt($parent.attr("data-max-children"));
                        if (parent_children_number !== parent_max_children) {
                            var $cloned_element = $block.clone();
                            var new_block_id = block_name + "-" + Myself.get_next_available_id();
                            Myself.blocks_cache_add(Myself.generate_block_object($cloned_element, new_block_id)); //Update block cache
                            $cloned_element.attr("id", new_block_id);
                            $cloned_element.find("[data-block]").each(function(index) {
                                var block_name = $(this).attr("data-block");
                                var new_block_id = block_name + "-" + Myself.get_next_available_id();
                                $(this).attr("id", new_block_id);
                                Myself.blocks_cache_add(Myself.generate_block_object($(this), new_block_id)); //Update block cache
                            });
                            Myself.add_children($cloned_element, $parent);
                            Myself.content_cache_update(); //Update editor content input
                        } else {
                            alert("Max children number reached");
                        }
                    });
                }
                /*
                 * EDIT
                 */
                if($(this).hasClass("edit")){
                    $(this).on("click", function(e) {
                        e.preventDefault();
                        Myself.$editing_element = $(this).closest("[data-block]");
                        Myself.open_edit_screen(Myself.$editing_element);
                    });
                }
                /*
                 * RESIZE
                 */
                if($(this).hasClass("resize")){
                    $(this).on("click", function(e) {
                        var direction = $(this).attr("data-direction");
                        var max_colspan = (function() {
                            var parent_block_name = $block.parent("[data-block]").attr("data-block");
                            var parent_block = wbpbData.blocks[parent_block_name];
                            return parent_block.info.max_children_per_row || 4; //todo: qui viene dato un colspan massimo di default... 4?
                        })();
                        var my_colspan = parseInt($block.attr("data-colspan"));
                        var current_blocks_cache = Myself.get_current_blocks();
                        switch (direction) {
                            case "left":
                                if (my_colspan > 1) {
                                    my_colspan--;
                                }
                                $block.attr("data-colspan", my_colspan);
                                break;
                            case "right":
                                if (my_colspan < max_colspan) {
                                    my_colspan++;
                                }
                                $block.attr("data-colspan", my_colspan);
                                break;
                            default:
                                break;
                        }
                        //Update the input#pbblocks with the new colspan
                        current_blocks_cache["" + block_id + ""].colspan = my_colspan;
                        Myself.blocks_cache_overwrite(current_blocks_cache);
                        //Update the input#pbcontent
                        Myself.content_cache_update();
                    });
                }
            });
        },
        enable_sorting: function() {
            this.$container.find("[data-sortable]").each(function(index) {
                var sortable_item = $(this).attr("data-sortable");
                $(this).sortable({
                    handle: "a.drag",
                    items: "> " + sortable_item
                });
            });
        },
        enable_selection: function(){
            var Myself = this;
            $(document).on("click", "#"+Myself.container_id+" [data-selectable]", function(e) {
                e.stopPropagation();
                Myself.make_selected($(this));
            });
        },
        disable_ui_actions: function(){
            /*$("[data-block]").each(function(index){
             if($(this).hasClass('ui-sortable')){
             $(this).sortable("destroy");
             }
             });*/
            this.$container.find(".ui-sortable").each(function(){$(this).sortable("destroy")});
            this.$container.find(".tools a").unbind("click");
            this.$container.find(".tools a").off("click");
            this.toolbar.disable_toolbar_actions();
        },
        make_selected: function($block){
            $("[data-selectable]").removeClass("ui-selected");
            $block.addClass("ui-selected");
            this.$selected_element = $block; //Set the current selected element
            this.toggle_toolbar_actions();
        },
        /**
         * Enable or Disable Tools from the toolbar
         */
        toggle_toolbar_actions: function() {
            var Myself = this;
            if (Myself.$selected_element !== "") {
                Myself.toolbar.disable_all_tools();
                var selected_block_name = Myself.$selected_element.attr("data-block");
                var my_max_children = parseInt(Myself.$selected_element.attr("data-max-children"));
                var my_children_number = Myself.get_children_number(Myself.$selected_element);
                $.each(wbpbData.tools, function(index, value) {
                    if (value.enabled_on === "*") {
                        Myself.toolbar.enable_tool(index);
                    } else {
                        var enabled_on_array = [];
                        if (value.enabled_on.indexOf(',') === -1) {
                            enabled_on_array = [value.enabled_on];
                        } else {
                            enabled_on_array = value.enabled_on.split(",");
                        }
                        if (_.indexOf(enabled_on_array, selected_block_name) !== -1 && (my_children_number !== my_max_children)) {
                            Myself.toolbar.enable_tool(index);
                        } else {
                            Myself.toolbar.disable_tool(index);
                        }
                    }
                });
            }
        },
        /**
         * Hide the editor toolbar
         */
        hide_toolbar: function(){
            this.toolbar.disable_all_tools();
            this.toolbar.$self.hide();
        },
        /**
         * Show the editor toolbar
         */
        show_toolbar: function(){
            this.toolbar.disable_all_tools();
            this.toolbar.$self.show();
        },
        /**
         * Performs the GUI operations for opening an edit screen
         */
        show_edit_screen: function(){
            this.$main_block.hide();
            this.hide_toolbar();
            this.$container.find(".close-icon").hide();
            this.$container.find(".wb-pagebuilder-footer").hide();
            this.$container.toggleClass("edit-screen");
            this.$edit_screen.show();
        },
        /**
         * Performs the GUI operatios for closing an edit screen
         */
        hide_edit_screen: function(){
            this.$edit_screen.hide();
            this.$main_block.show();
            this.show_toolbar();
            this.$container.find(".close-icon").show();
            this.$container.find(".wb-pagebuilder-footer").show();
            this.$container.toggleClass("edit-screen");
            //Unbind the click event on save/cancel buttons
            $(document).off("click","[data-link-action='submit-edit']");
            $(document).off("click","[data-link-action='close-edit']");
        },
        /**
         * Show / hide loading screen
         * @param $block
         * @param action
         */
        toggle_loading: function($block,action){
            var $loading_window;

            if($("[data-loading-window=cloned]").length === 0){
                $loading_window = $("[data-loading-window]").clone();
                $loading_window.attr("data-loading-window","cloned");
            }else{
                $loading_window = $("[data-loading-window=cloned]");
            }

            if(typeof($block) === "undefined"){
                $block = this.$container;
            }

            if(action === "show"){
                $block.append($loading_window);
                $block.addClass("wbpbloading");
            }else{
                //$("wb-pagebuilder-editors").after($loading_window);
                $block.removeClass("wbpbloading");
                $loading_window.remove();
            }

            //todo: below is a more efficient way, but we have to fix the double call issue
            /*if($.contains($block[0],$loading_window[0])){
                $("body").append($loading_window);
            }else{
                $block.append($loading_window);
            }
            $block.toggleClass("wbpbloading");*/
        }
    };
})(jQuery);
