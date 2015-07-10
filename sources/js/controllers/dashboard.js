module.exports = Backbone.Model.extend({
    initialize: function() {
        "use strict";
        console.log("It'admin time!");
        this.do_stuff();
    },
    do_stuff: function(){
        "use strict";
        var $ = jQuery,
            $mailtable = $("#waboot-received-mails-view"),
            $recent_posts_widget_pt_selector = $("#widgets-right [data-wbrw-post-type-selector]");
        /**
         * Init received mails viewerr
         */
        if($mailtable.length > 0){
            var MailListView = require("../views/mailList.js"),
                MailListModel = require("./mailList.js"),
                MailWindow = new MailListView({
                    model: new MailListModel({
                        emails_data: (function(){
                            if(!_.isUndefined(wbData.contactForm.mails)){
                                return jQuery.parseJSON(wbData.contactForm.mails);
                            }else{
                                return [];
                            }
                        })()
                    }),
                    el: $mailtable
                });
        }
        /**
         * RECENT POST WIDGET
         */
        if($recent_posts_widget_pt_selector.length > 0){
            var get_checkboxes_status = function($container){
                //Get the state of all checkboxes
                var $checkboxes = $container.find("input[type=checkbox]"),
                    states = [];
                $checkboxes.each(function(){
                    states.push({
                        name: $(this).attr("value"),
                        checked: $(this).is(":checked") ? 1 : 0
                    });
                });
                return states;
            };

            $recent_posts_widget_pt_selector.find("input[type=checkbox]").on("change",function(){
                var states = get_checkboxes_status($recent_posts_widget_pt_selector),
                    $categories_container = $("#widgets-right [data-wbrw-term-type='category']"),
                    $tags_container = $("#widgets-right [data-wbrw-term-type='tag']");

                //Adding loading classes:
                $categories_container.addClass("loading");
                $tags_container.addClass("loading");

                //Make reguests for new terms:
                var category_request = $.ajax(wbData.ajaxurl,{
                    data: {
                        action: "wbrw_get_terms",
                        states: states,
                        hierarchical: 1
                    },
                    dataType: "json",
                    method: "POST"
                }).done(function(data, textStatus, jqXHR){
                    var tpl = _.template($categories_container.find("[type='text/template']").html()),
                        $ul = $categories_container.find("ul"),
                        slug = $ul.find("li:first-child").find("input").attr("id").match(/([a-zA-Z0-9-_]+)-[0-9]+$/)[1]; //the value of <?php echo $this->get_field_id( 'cat' ) ?>
                    console.log(data);
                    $ul.html(tpl({
                        terms: data,
                        widget_cat: slug
                    }));
                    $categories_container.removeClass("loading");
                }).fail(function(jqXHR, textStatus, errorThrown){
                    console.log(textStatus);
                    $categories_container.removeClass("loading");
                });
                var tags_request = $.ajax(wbData.ajaxurl,{
                    data: {
                        action: "wbrw_get_terms",
                        states: states,
                        hierarchical: 0
                    },
                    dataType: "json",
                    method: "POST"
                }).done(function(data, textStatus, jqXHR){
                    var tpl = _.template($tags_container.find("[type='text/template']").html()),
                        $ul = $tags_container.find("ul"),
                        slug = $ul.find("li:first-child").find("input").attr("id").match(/([a-zA-Z0-9-_]+)-[0-9]+$/)[1]; //<?php echo $this->get_field_id( 'tag' ) ?>
                    console.log(data);
                    $ul.html(tpl({
                        terms: data,
                        widget_tag: slug
                    }));
                    $tags_container.removeClass("loading");
                }).fail(function(jqXHR, textStatus, errorThrown){
                    console.log(textStatus);
                    $tags_container.removeClass("loading");
                });
            });
        }
    }
});
