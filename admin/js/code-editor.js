(function ($) {
    "use strict";
    $.fn.codemirror = function (options) {

        var result = this;

        var settings = $.extend({
            'mode': 'javascript',
            'lineNumbers': false,
            'runmode': false
        }, options);

        if (settings.runmode) this.each(function () {
            var obj = $(this);
            var accum = [], gutter = [], size = 0;
            var callback = function (string, style) {
                if (string == "\n") {
                    accum.push("<br>");
                    gutter.push('<pre>' + (++size) + '</pre>');
                }
                else if (style) {
                    accum.push("<span class=\"cm-" + CodeMirror.htmlEscape(style) + "\">" + CodeMirror.htmlEscape(string) + "</span>");
                }
                else {
                    accum.push(CodeMirror.htmlEscape(string));
                }
            }
            CodeMirror.runMode(obj.val(), settings.mode, callback);
            $('<div class="CodeMirror">' + (settings.lineNumbers ? ('<div class="CodeMirror-gutter"><div class="CodeMirror-gutter-text">' + gutter.join('') + '</div></div>') : '<!--gutter-->') + '<div class="CodeMirror-lines">' + (settings.lineNumbers ? '<div style="position: relative; margin-left: ' + size.toString().length + 'em;">' : '<div>') + '<pre class="cm-s-default">' + accum.join('') + '</pre></div></div></div>').insertAfter(obj);
            obj.hide();
        });
        else this.each(function () {
            result = CodeMirror.fromTextArea(this, settings);
        });

        return result;
    };
})(jQuery);

jQuery(document).ready(function ($) {
    "use strict";
    var editors = [];
    var targets = $("textarea.codemirror[data-lang]");
    targets.each(function (index) {
        var my_option_group = $(this).closest(".group");
        var my_options_group_link = $("a#" + my_option_group.attr("id") + "-tab");
        var my_mode = $(this).attr("data-lang");
        var editor = $(this).codemirror({
            mode: my_mode,
            lineNumbers: true
        });
        editors.push(editor);
        my_options_group_link.bind("click", function () {
            editor.refresh();
        });
    });

    /*$("a#options-group-2-tab").on("click",function(){
     _.each(editors,function(element,index,list){
     element.refresh();
     });
     });*/
});
