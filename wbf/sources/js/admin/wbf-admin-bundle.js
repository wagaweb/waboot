(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
module.exports = {
    loadWebFonts: function(families){
        if(!_.isEmpty(families)){
            WebFont.load({
                google: {
                    families: families
                }
            });
        }
    }
};
},{}],2:[function(require,module,exports){
module.exports = {
    multipleFileUpload: require("./acf-fields/multiple-file-upload"),
    wcfGallery: require("./acf-fields/wcf-gallery")
};
},{"./acf-fields/multiple-file-upload":3,"./acf-fields/wcf-gallery":4}],3:[function(require,module,exports){
module.exports = {
    init_interface: function(){
        var $ = jQuery;
        var $container = $(".mfu-files");
        if($container.length > 0){
            var tpl = _.template($("#FileUploadInput").html());

            if($container.children().length == 0) add_file_input();

            //Add new field action
            $("a.add-attachment").on("click", function(e){
                e.preventDefault();
                add_file_input();
            });

            //Upload file action
            $("a.upload-attachment").on("click", function(e){
             e.preventDefault();
             console.log("Click!");
             });

            function add_file_input(){
                $container.append(tpl());
            }
        }
    }
};
},{}],4:[function(require,module,exports){
module.exports = {
    init_interface: function(){
        var $ = jQuery;
        var custom_uploader;
        $('#upload-btn').click(function(e) {
            e.preventDefault();
            //If the uploader object has already been created, reopen the dialog
            if (custom_uploader) {
                custom_uploader.open();
                return;
            }
            //Extend the wp.media object
            custom_uploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose Image',
                button: {
                    text: 'Choose Image'
                },
                multiple: true
            });
            custom_uploader.on('select', function() {
                var selection = custom_uploader.state().get('selection');
                var imgIds = [];
                selection.map( function( attachment ) {
                    attachment = attachment.toJSON();
                    imgIds.push(attachment.id);
                    console.log(imgIds);
                    $.ajax(wbfData.ajaxurl,{ //ajax url is not available in the front end. Needs to wp_localize_script
                        data: {
                            action: "wcf_get_thumbnail", //the action specified in ajax wordpress hooks
                            id: attachment.id
                        },
                        dataType: "json", //Default is an "intelligent guess"; does not work very often
                        method: "POST" //Default is GET
                    }).done(function(data, textStatus, jqXHR){
                        console.log(data);
                        var newData = JSON.stringify(data);
                        var imgUrl = JSON.parse(newData);
                        var newImgUrl = imgUrl.thumb;
                        var newDataIndex = $('.containerImgGalleryAdmin').length;
                        $("#imageContainer").append("<div class='containerImgGalleryAdmin'>" +
                        "<img class='imgGalleryAdmin' src=" +newImgUrl+" data-id='"+ attachment.id +"'>" +
                        "<div class='deleteImg'>"+
                        "<a class='acf-icon dark remove-attachment ' data-index='"+ newDataIndex  +"' href='#' data-id='"+ attachment.id +"'>"+
                        "<i class='acf-sprite-delete'></i>"+
                        "</a>"+
                        "</div></div>");
                        $("#imageContainer").sortable('refresh');
                    }).fail(function(jqXHR, textStatus, errorThrown){
                        console.log(errorThrown);
                    }).always(function(result, textStatus, type){
                        console.log(type);
                    });
                });
                var savedVal = $('#imgId').val();
                console.log(savedVal);
                if(savedVal=="  "||savedVal==''||savedVal==' '){
                    $('#imgId').val(imgIds);
                }else{
                    $('#imgId').val(savedVal + ',' + imgIds);
                }

            });
            custom_uploader.open();
        });
        $('#imageContainer').on('mouseover', '.containerImgGalleryAdmin',function(){
            $(this).addClass('on');
        });
        $('#imageContainer').on('click',' .deleteImg', function(e){
            e.preventDefault();
            var oldValues = $('#imgId').val();
            var arrayValues = oldValues.split(",").map(Number);
            var elemIndex = $(this).children().attr('data-index');
            var imgIds = [];
            console.log(arrayValues, elemIndex);
            $.each(arrayValues, function (index, value) {
                console.log(index, elemIndex);
                if (index != elemIndex) {
                    imgIds.push($(value)[0]);
                }
            });
            $('.containerImgGalleryAdmin.on').remove();
            $('#imgId').val(imgIds);
            $.each($('.deleteImg'), function (index, value) {
                $(this).children().attr('data-index', index);
            });
        });
        $('#imageContainer').on('click','.imgGalleryAdmin',function(){
            if($('.containerImgGalleryAdmin').hasClass('selected')){
                $('.containerImgGalleryAdmin').removeClass('selected');
            }
            $('#imageContainer, .uploadContainer, .mainContainer').addClass('selected');
            $(this).parent().addClass('selected');
            $('#imageInfo').addClass('active');
            $.ajax(wbfData.ajaxurl,{ //ajax url is not available in the front end. Needs to wp_localize_script
                data: {
                    action: "wcf_media_info", //the action specified in ajax wordpress hooks
                    id: $(this).attr('data-id')
                },
                dataType: "json", //Default is an "intelligent guess"; does not work very often
                method: "POST" //Default is GET
            }).done(function(data, textStatus, jqXHR){
                $('#imageInfo #imgThumb, #imageInfo .body, #imageInfo .footer').addClass('active');
                $('#imageInfo #imgThumb img').attr('src',data.thumb);
                $('#imageInfo #mainInfo .imgName').text(data.name);
                $('#imageInfo #mainInfo .upload').text(data.upload);
                $('#imageInfo #mainInfo .dimensions').text(data.size +' ('+ data.filesize + ')' );
                $('.title #imageTitle').val(data.title);
                $('.caption #imageCaption').val(data.caption);
                $('.alt #imageAlt').val(data.alt);
                $('.description #imageDescription').val(data.description);
            }).fail(function(jqXHR, textStatus, errorThrown){
                console.log(errorThrown);
            }).always(function(result, textStatus, type){
                console.log(type);
            });
        });
        $('#imageInfo #closeBtn').on('click',function(e){
            e.preventDefault();
            $('.mainContainer, #imageContainer, .uploadContainer, .containerImgGalleryAdmin').removeClass('selected');
            $('#imageInfo, #imageInfo .body, #imageInfo .footer  ').removeClass('active');
        });
        $('#imageInfo #updateBtn').on('click',function(e){
            e.preventDefault();
            $('#imageInfo .spinner').addClass('is-active');
            $(this).attr('disabled','disabled');
            $.ajax(wbfData.ajaxurl,{ //ajax url is not available in the front end. Needs to wp_localize_script
                data: {
                    action: "wcf_update_media_info", //the action specified in ajax wordpress hooks
                    id: $('.containerImgGalleryAdmin.selected .imgGalleryAdmin').attr('data-id'),
                    title:$('.title #imageTitle').val(),
                    caption:$('.caption #imageCaption').val(),
                    alt:$('.alt #imageAlt').val(),
                    description:$('.description #imageDescription').val()
                },
                dataType: "json", //Default is an "intelligent guess"; does not work very often
                method: "POST" //Default is GET
            }).done(function(data, textStatus, jqXHR){
                if(data.response=='true') {
                    $('#imageInfo #updateBtn').removeAttr('disabled');
                    $('#imageInfo .spinner').removeClass('is-active');
                }

            }).fail(function(jqXHR, textStatus, errorThrown){
                console.log(errorThrown);
            }).always(function(result, textStatus, type){
                console.log(type);
            });
        });
        $('#imageContainer').on('click','.containerImgGalleryAdmin.selected .imgGalleryAdmin',function(){
            $('.mainContainer, #imageContainer, .uploadContainer, .containerImgGalleryAdmin').removeClass('selected');
            $('#imageInfo, #imageInfo .body, #imageInfo .footer  ').removeClass('active');
        });
        $('#imageContainer').on('mouseout', '.containerImgGalleryAdmin',function(){
            $(this).removeClass('on');

        });

        $('#imageContainer').sortable({
            stop: function(event, ui) {
                var newImgArray = $('.imgGalleryAdmin');
                $('#imgId').val('');
                var imgIds = [];
                $.each(newImgArray, function(index, value){
                    imgIds.push($(value).attr('data-id'));
                });
                $('#imgId').val(imgIds);
            }
        });
        $('#imageContainer').disableSelection();


    }
};

},{}],5:[function(require,module,exports){
module.exports = {
    init_interface: function(){
        "use strict";
        var $ = jQuery;
        $('.behavior-metabox-image').click(function(){
            $(this).parents(".behavior-images-options").find('.behavior-metabox-image').removeClass('behavior-metabox-image-selected');
            $(this).addClass('behavior-metabox-image-selected');
        });

        $('.behavior-metabox-image-default').click(function(){
            $(this).parent(".behavior-images-wrapper").find('.behavior-metabox-image').removeClass('behavior-metabox-image-selected');
        });
    }
};
},{}],6:[function(require,module,exports){
module.exports = {
    init_interface: function(){
        "use strict";
        var $ = jQuery;

        var editors = [];
        var targets = $("textarea.codemirror[data-lang]");
        var isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;

        //Initialize all editors.
        //The Timeout is necessary due to the lag between window load and the time needed for theme options script to arrange/show/hide the tabs.
        setTimeout(function () {
            targets.each(function (index) {
                var my_option_group = $(this).closest(".group");
                var my_options_group_link = $("a#" + my_option_group.attr("id") + "-tab");
                var my_mode = $(this).attr("data-lang");
                var editor = $(this).codemirror({
                    mode: {name: my_mode, globalVars: true},
                    lineNumbers: true,
                    theme: "ambiance",
                    extraKeys: (function () {
                        if (isMac) {
                            return {"Ctrl-F": "autocomplete"};
                        } else {
                            return {"Ctrl-Space": "autocomplete"};
                        }
                    })()
                });
                editors.push(editor);
                my_options_group_link.bind("click", function () {
                    setTimeout(function () {
                        editor.refresh();
                    }, 1000);
                });
            });
        }, 1500);

        /*$("a#options-group-2-tab").on("click",function(){
         setTimeout(function(){
         _.each(editors,function(element,index,list){
         element.refresh();
         });
         }, 1000);
         });*/
    },
    init_jq_plugin: function(){
        jQuery.fn.codemirror = function (options) {
            var $ = jQuery;

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
                };
                CodeMirror.runMode(obj.val(), settings.mode, callback);
                $('<div class="CodeMirror">' + (settings.lineNumbers ? ('<div class="CodeMirror-gutter"><div class="CodeMirror-gutter-text">' + gutter.join('') + '</div></div>') : '<!--gutter-->') + '<div class="CodeMirror-lines">' + (settings.lineNumbers ? '<div style="position: relative; margin-left: ' + size.toString().length + 'em;">' : '<div>') + '<pre class="cm-s-default">' + accum.join('') + '</pre></div></div></div>').insertAfter(obj);
                obj.hide();
            });
            else this.each(function () {
                result = CodeMirror.fromTextArea(this, settings);
            });

            return result;
        };
    }
};
},{}],7:[function(require,module,exports){
module.exports = {
    init_interface: function(){
        "use strict";
        var $ = jQuery;

        $(".nav-tab-wrapper a").on("click",function(){
            var $selected_component_div = $('#'+$(this).attr("data-show-comp-settings"));
            $("#componentframework-metabox .group").hide();
            $selected_component_div.show();
        });
    }
};

},{}],8:[function(require,module,exports){
module.exports = {
    init_interface: function(my_controller){
        "use strict";
        var $ = jQuery;

        var controller = my_controller;

        $(".font-family-selector").on("change",function(){
            var $familySeletor = $(this);
            var $styleSelector = $(this).siblings(".font-style-selector");
            var styleOptName = $styleSelector.find('input:first').attr("name");
            var $charsetSelector = $(this).siblings(".font-charset-selector");
            var charsetOptName = $charsetSelector.find('input:first').attr("name");
            var $categoryInput = $(this).siblings(".font-category-selector");
            var $fontPreview = $(this).siblings(".font-preview");
            var request = $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "gfontfetcher_getFontInfo",
                    family: $(this).val()
                },
                dataType: "json",
                beforeSend: function(){
                    $familySeletor.attr("disabled","disabled");
                    $styleSelector.addClass("disabled");
                    $charsetSelector.addClass("disabled");
                }
            });
            request.done(function(data, textStatus, jqXHR){
                console.log(data);
                //Load GFonts and set the preview
                if(data.kind == "webfonts#webfont"){
                    controller.loadWebFonts([$familySeletor.val()]);
                }
                $fontPreview.find("p").css("font-family","'"+data.family+"',"+data.category);
                //Assign new styles to the html select
                $styleSelector.html((function(){
                    var output = "";
                    $.each(data.variants,function(){
                        output += "<input name='"+styleOptName+"' type='checkbox' value='"+this+"' />"+this;
                    });
                    return output;
                })());
                //Assign new charset to the html select
                $charsetSelector.html((function(){
                    var output = "";
                    $.each(data.subsets,function(){
                        output += "<input name='"+charsetOptName+"' type='checkbox' value='"+this+"' />"+this;
                    });
                    return output;
                })());
                //Assign new category to the html input
                $categoryInput.val(data.category);
            });
            request.fail(function(jqXHR, textStatus, errorThrown){
                console.log(errorThrown);
            });
            request.always(function(result, textStatus, returned){
                $familySeletor.removeAttr("disabled");
                $styleSelector.removeClass("disabled");
                $charsetSelector.removeClass("disabled");
            });
        });
    }
};
},{}],9:[function(require,module,exports){
jQuery(document).ready(function($) {
    //Init ACF Custom Fields
    var acf_fields_views = require("./views/acf-fields.js");
    _.each(acf_fields_views,function(element,index,list){
        if(!_.isUndefined(element.init_interface)){
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
        font_selector_view.init_interface(font_selector_controller);
    }
    //Init behavior view
    var behavior_view = require("./views/behavior.js");
    behavior_view.init_interface();
});

},{"./controllers/font-selector.js":1,"./views/acf-fields.js":2,"./views/behavior.js":5,"./views/code-editor.js":6,"./views/component-page.js":7,"./views/font-selector.js":8}]},{},[9]);
