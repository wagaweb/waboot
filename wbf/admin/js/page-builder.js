jQuery.noConflict()(function($) {
    "use strict";

    //$(document).ajaxStart(function(){toggle_loading_screen();}).ajaxStop(function(){toggle_loading_screen();});

    var Editors = [];
    var $editors_data_div = $("#wb-pagebuilder-editors-data");
    var $editors_gui_div = $("#wb-pagebuilder-editors");
    var base_editor_original_content = "";
    register_tinymce_view();
    reinit_caches();

    $(document).ready(function() {
        var pagebuilder_displayed_flag = false;

        /*
         * Opens new page builder editor
         */
        $(document).on('click','#insert-wb-new-pagebuilder-placeholder', function(){
            var Editor = new WBEditor("wb-pagebuilder-base","wb-pagebuilder-base-toolbar","input#base-pbblocks","input#base-pbcontent");
            Editor.initialize(true,true);
            Editors[Editor.container_id] = Editor;
            display_pagebuilder("wb-pagebuilder-base",true);
            pagebuilder_displayed_flag = true;
            base_editor_original_content = Editor.$main_block.html();
        });

        /*
         * Handle page builder saving
         */
        $(document).on('click', 'a[data-savepb]', function() {
            var pb_id = $(this).closest("[data-editor]").attr("id");
            if(pb_id === "wb-pagebuilder-base"){
                var new_temp_id = _.uniqueId('temp-');
                save_new_pagebuilder(new_temp_id);
                var new_shortcode = '[pagebuilder id="'+new_temp_id+'"]';
                get_tinymce_focus();
                window.send_to_editor(new_shortcode);
            }
            pagebuilder_displayed_flag = false;
            close_modal();
        });

        /*
         * Handle the selection highlight into the editors
         */
        $(document).on("mouseover", "[data-selectable]", function(e) {
            e.stopPropagation();
            var element = $(this);
            element.parents("[data-selectable]").removeClass("ui-hovered");
            element.addClass("ui-hovered");
        });
        $(document).on("mouseout", "[data-selectable]", function(e) {
            e.stopPropagation();
            var element = $(this);
            element.parents("[data-selectable]").removeClass("ui-hovered");
            element.removeClass("ui-hovered");
        });

        /**
         * Handle Close Editor
         */
        $(document).on("click","[data-link-action=close]", function(){
            var editor_id = $(this).closest("[data-editor]").attr("id");
            if(editor_id == "wb-pagebuilder-base"){
                reset_base_editor();
            }
            close_modal();
        });
    });

    /**
     * Popup a page builder edit screen
     * @param id [string] the id of the pagebuilder
     * @param is_new [bool] if must display a new page builder
     */
    function display_pagebuilder(id,is_new){
        if(typeof(is_new)==='undefined') is_new = false;
        var must_rebuild_cache_flag = false;

        if(!is_new){
            var Editor = Editors[id];
            if(typeof(Editor)==='undefined'){ //This is not supposed to occur... but.. :)
                Editors[id] = new WBEditor(id,"wb-pagebuilder-"+id+"-toolbar",'input[data-block-cache-for='+id+']','input[data-content-cache-for='+id+']');
                Editor = Editors[id];
                must_rebuild_cache_flag = true;
            }
            if(must_rebuild_cache_flag){
                Editor.initialize(is_new,true);
                Editor.enable_toolbar();
            }else{
                Editor.initialize(is_new);
            }
        }

        id = id+"-modal";
        $("a#open-pagebuilder").attr("href","#TB_inline?&inlineId="+id);
        open_modal(id);
    }

    /**
     * Save new page builder into the DOM
     * @param id [string] the id of the new page builder
     */
    function save_new_pagebuilder(id){
        //Take the content of wb-pagebuilder-base and clone it
        if(Editors["wb-pagebuilder-base"]){
            var Editor = Editors["wb-pagebuilder-base"];

            //Copy the cache
            var $new_blocks_cache = Editor.$block_cache.clone();
            var $new_content_cache = Editor.$content_cache.clone();
            $new_blocks_cache.attr("data-block-cache-for",id);
            $new_blocks_cache.attr("name",'pbcache['+id+'][blocks]');
            $new_blocks_cache.attr("id",id+'-pbblocks');
            $new_content_cache.attr("data-content-cache-for",id);
            $new_content_cache.attr("name",'pbcache['+id+'][content]');
            $new_content_cache.attr("id",id+'-pbcontent');
            $editors_data_div.append($new_blocks_cache);
            $editors_data_div.append($new_content_cache);

            //Add new editor
            var new_container_id = "wb-pagebuilder-"+id;
            var new_toolbar_id = "wb-pagebuilder-"+id+"-toolbar";
            var $new_editor_div = $(_.template(wbpbData.templates.editor,{
                id: id,
                content: Editor.get_content()
            }));
            $new_editor_div.find("[data-editor]").attr("data-editor",id);
            $new_editor_div.find("[data-toolbar]").attr("data-toolbar",id);
            $editors_gui_div.append($new_editor_div);

            //Edit the Editor obj
            Editors[id] = new WBEditor(new_container_id,new_toolbar_id,'input[data-block-cache-for='+id+']','input[data-content-cache-for='+id+']');
            Editors[id].initialize();

            //Reset the base editor
            reset_base_editor();
        }
    }

    /**
     * Find all pre-existing editors, add them to Editors var and compile their caches.
     */
    function reinit_caches(){
        $editors_gui_div.find("[data-editor]").each(function(){
            var editor_id = $(this).attr("data-editor");
            var toolbar_id = $(this).find("[data-toolbar]").attr("id");

            if(editor_id != "" && toolbar_id != ""){
                var Editor = new WBEditor("wb-pagebuilder-"+editor_id,toolbar_id,'input[data-block-cache-for='+editor_id+']','input[data-content-cache-for='+editor_id+']');
                Editor.initialize(false,true,false); //!is_new rebuild_caches !register_actions
                Editor.enable_toolbar();
                Editors[editor_id] = Editor;
            }
        });
    }

    /**
     * Reset the base editor to default
     */
    function reset_base_editor(){
        if(typeof(Editors["wb-pagebuilder-base"])!=="undefined"){
            var Editor = Editors["wb-pagebuilder-base"];
            Editor.disable_ui_actions();
            Editor.$main_block.html(base_editor_original_content);
            Editor.clear_cache();
            delete Editors["wb-pagebuilder-base"];
        }
    }

    /*
     * --------------- MODAL ---------------
     */

    var modal;

    function open_modal(pagebuilder_id){
        //$("#open-pagebuilder").trigger("click"); //trigger Thickbox
        //$("#TB_overlay").unbind("click",tb_remove); //Remove the click into backgroud to close thickbox

        var default_settings = {
            adminmenuwrap: {
                'z-index': 0
            }
        };
        modal = $("#"+pagebuilder_id).dialog({
            autoOpen: false,
            height: "auto",
            width: "auto",
            modal: true,
            dialogClass: "wb-pagebuilder-modal",
            draggable: false,
            minHeight: 1024,
            minWidth: 768,
            resizable: false,
            closeOnEscape: false,
            open: function(event, ui){
                default_settings['adminmenuwrap']['z-index'] = $("#adminmenuwrap").css("z-index");
                $("#adminmenuwrap").css("z-index","0");
            },
            close: function(event, ui){
                $("#adminmenuwrap").css("z-index",default_settings['adminmenuwrap']['z-index']);
            }
        });

        modal.dialog("open");
    }

    /**
     * Close the page builder modal
     * @uses unbind_all_actions()
     */
    function close_modal(caller){
        //tb_remove();
        modal.dialog("close");
    }

    /*
     * --------------- TINYMCE ---------------
     */

    function get_tinymce_focus(){
        window.switchEditors.go('content', 'tmce');
        /*window.tinymce.get("content").focus();
         window.tinymce.activeEditor.focus();*/
        window.tinymce.execCommand('mceFocus',false,'content');
    }

    function register_tinymce_view(){
        // wp.media shortcut
        var media = wp.media;

        // Create the `wp.mce` object if necessary.
        wp.mce = wp.mce || {};

        // Register the pagebuilder view
        wp.mce.views.register( 'pagebuilder', {
            View: {
                template: media.template('editor-pagebuilder'),
                initialize: function(options){
                    this.shortcode = options.shortcode;
                    //this.fetch() //todo: WP (mce-views.js:505) usa qst metodo per inizializzare alcune proprietà. Si potrebbe pensare di utilizzarlo per il rendering... magari andare a prendere il contenuto in cache dell'editor selezionato...
                },
                getHtml: function(){
                    var attrs = this.shortcode.attrs.named, //The attributes of shortcode
                        options = {
                            id: attrs.id
                        };
                    return this.template( options );
                }
            },
            edit: function(node){
                var editor_id = $(node).find(".pagebuilder").attr("data-id");
                display_pagebuilder(editor_id);
            }
        });
    }
});

/**
 * Initialize TinyMCE (used via paghebuilder editing screen)
 * @param element
 */
function initTinyMce(element) {
    "use strict";
    var qt;
    var textfield_id = element.attr("id");

    if(typeof(textfield_id) !== "undefined" && textfield_id !== ""){

        if (_.isUndefined(tinyMCEPreInit.qtInit[textfield_id])) {
            var prevInstances, newInstance;
            prevInstances = QTags.instances;
            QTags.instances = [];

            tinyMCEPreInit.qtInit[textfield_id] = _.extend({}, tinyMCEPreInit.qtInit['content'], {
                id: textfield_id
            });
            qt = quicktags(tinyMCEPreInit.qtInit[textfield_id]);
            QTags._buttonsInit();

            newInstance = QTags.instances[textfield_id];
            QTags.instances = prevInstances;
            QTags.instances[textfield_id] = newInstance;
        }

        tinyMCEPreInit.mceInit[textfield_id] = _.extend({}, tinyMCEPreInit.mceInit['content'], {
            resize: 'vertical',
            height: 200,
            selector: '#'+textfield_id
        });

        tinymce.init( tinyMCEPreInit.mceInit[textfield_id] );
    }
}

/**
 * Get an HTML tag attribute
 * @param string the HTML tag
 * @param attr the attribute to retrieve
 * @returns {string}
 */
function getAttr(string, attr) {
    "use strict";
    attr = new RegExp(attr + '=\"([^\"]+)\"', 'g').exec(string);
    return attr ?  window.decodeURIComponent(attr[1]) : '';
}
