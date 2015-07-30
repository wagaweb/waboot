(function(){
    //Init ACF Custom Fields
    var acf_fields_views = require("./views/acf-fields.js");
    _.each(acf_fields_views,function(element,index,list){
        if(_.isUndefined(element.init_interface)){
            element.init_interface();
        }
    });
    //Init component page
    var component_page_view = require("./views/component-page.js");
    component_page_view.init_interface();
    //Init code editor view
    var code_editor_view = require("./views/code-editor.js");
    code_editor_view.init_jq_plugin();
    code_editor_view.init_interface();
    //Init font selector
    if(!_.isUndefined(wbfData.wbfOfFonts)){
        var font_selector_controller = require("./controllers/font-selector.js"),
            font_selector_view = require("./views/font-selector.js");
        font_selector_controller.loadWebFonts(wbfData.wbfOfFonts.families);
        font_selector_view.init_interface();
    }
    //Init behavior view
    var behavior_view = require("./views/behavior.js");
    behavior_view.init_interface();
})();
