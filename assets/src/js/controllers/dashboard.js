import * as Backbone from "backbone";
import $ from "jquery";

export default class extends Backbone.Model{
    initialize(){
        "use strict";
        this.do_stuff();
    }
    do_stuff(){
        "use strict";
        var $mailtable = $("#waboot-received-mails-view"),
            $recent_posts_widget_pt_selector = $("#widgets-right [data-wbrw-post-type-selector]");
        /**
         * Init received mails viewer
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
            let get_checkboxes_status = function($container){
                //Get the state of all checkboxes
                let $checkboxes = $container.find("input[type=checkbox]"),
                    states = [];
                $checkboxes.each(() => {
                    states.push({
                        name: $(this).attr("value"),
                        checked: $(this).is(":checked") ? 1 : 0
                    });
                });
                return states;
            };

            let make_term_request = function(data){
                return $.ajax(wbData.ajaxurl,{
                    data: data,
                    dataType: "json",
                    method: "POST"
                });
            };

            $recent_posts_widget_pt_selector.find("input[type=checkbox]").on("change",function(){
                var states = get_checkboxes_status($recent_posts_widget_pt_selector),
                    $categories_container = $("#widgets-right [data-wbrw-term-type='category']"),
                    $tags_container = $("#widgets-right [data-wbrw-term-type='tag']");

                //Adding loading classes:
                $categories_container.addClass("loading");
                $tags_container.addClass("loading");

                //Make reguests for new terms:
                var category_request = make_term_request({
                    action: "wbrw_get_terms",
                    states: states,
                    hierarchical: 1
                }).fail(function(jqXHR, textStatus, errorThrown){
                    console.log(textStatus);
                    $categories_container.removeClass("loading");
                });
                var tags_request = make_term_request({
                    action: "wbrw_get_terms",
                    states: states,
                    hierarchical: 0
                }).fail(function(jqXHR, textStatus, errorThrown){
                    console.log(textStatus);
                    $tags_container.removeClass("loading");
                });

                //Resolve requests
                $.when(category_request,tags_request).done(function(categories_response,tags_response){
                    //console.log(categories_response);
                    //console.log(tags_response);
                    var assign_terms = function(terms,$container){
                        var tpl = _.template($container.find("[type='text/template']").html()),
                            $ul = $container.find("ul"),
                            field_name = $container.data("field-name"), //the value of <?php echo $this->get_field_name( 'cat' ) ?>
                            field_id = $container.data("field-id"); //the value of <?php echo $this->get_field_id( 'cat' ) ?>
                        $ul.html(tpl({
                            terms: terms,
                            field_name: field_name,
                            field_id: field_id
                        }));
                        $container.removeClass("loading");
                    };
                    assign_terms(categories_response[0],$categories_container);
                    assign_terms(tags_response[0],$tags_container);
                });
            });
        }
    }
}