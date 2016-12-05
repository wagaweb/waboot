import * as Backbone from "backbone";
import $ from "jquery";

export default class extends Backbone.Model{
    initialize(){
        "use strict";
        this.do_stuff();
    }
    do_stuff(){
        "use strict";
        this.manage_recent_post_widget();
        this.manage_components_page();
    }
    /**
     * RECENT POST WIDGET
     */
    manage_recent_post_widget(){
        "use strict";
        let $recent_posts_widget_pt_selector = $("#widgets-right [data-wbrw-post-type-selector]");

        if($recent_posts_widget_pt_selector.length <= 0){
            return;
        }

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
            let states = get_checkboxes_status($recent_posts_widget_pt_selector),
                $categories_container = $("#widgets-right [data-wbrw-term-type='category']"),
                $tags_container = $("#widgets-right [data-wbrw-term-type='tag']");

            //Adding loading classes:
            $categories_container.addClass("loading");
            $tags_container.addClass("loading");

            //Make reguests for new terms:
            let category_request = make_term_request({
                action: "wbrw_get_terms",
                states: states,
                hierarchical: 1
            }).fail(function(jqXHR, textStatus, errorThrown){
                console.log(textStatus);
                $categories_container.removeClass("loading");
            });
            let tags_request = make_term_request({
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
                let assign_terms = function(terms,$container){
                    let tpl = _.template($container.find("[type='text/template']").html()),
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
    manage_components_page(){
        "use strict";
        let $component_page_wrapper = $("#componentframework-wrapper");

        if($component_page_wrapper.length <= 0){
            return;
        }

        //Component options accordion:
        $("[data-action='open-details']").on("click", function (e) {
            e.preventDefault();

            let $components = $("[data-component]"),
                $my_component = $(this).parents("[data-component]");

            //$components.find("[data-component-options]").hide();
            //$my_component.find("[data-component-options]").show();
            $my_component.find("[data-component-options]").slideToggle("slow");
            $my_component.find("img").toggleClass("active");
        });
    }
}