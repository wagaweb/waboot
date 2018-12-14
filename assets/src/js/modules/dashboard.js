import $ from "jquery";
import Vue from "vue/dist/vue";
import { GeneratorsHandler } from "./generatorsHandler";
import { AppData as AddNewComponentsPageHandlerData, AppParams as AddNewComponentsPageHandlerParams } from "./addNewComponentsHandler";

export default class{
    constructor(){
        "use strict";
        this.do_stuff();
    }
    do_stuff(){
        "use strict";
        this.manage_recent_post_widget();
        this.manage_components_page();
        this.manage_theme_options_page();
        this.manage_generators_page();
    }

    /**
     * Manage Generator page actions
     */
    manage_generators_page(){
        let $form = $("#waboot-wizard-form"),
            $selectors = $("img[data-select]");

        if($form.length > 0){
            new GeneratorsHandler($form,wbData.ajaxurl,wbData.generators_action);

        }
        if($selectors.length > 0){
            $selectors.on('click', function(){
                let value = $(this).data('select'),
                    $radio = $("input[name='generator']");
                $selectors.removeClass('selected');
                $(this).addClass('selected');
                $radio.prop('checked',false);
                $("input[value='"+value+"']").prop('checked',true);
            });
        }
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
        let $component_page_wrapper = $("#componentframework-wrapper"),
            $components_nav = $(".componentframework-nav");
        const components_saved_selected_category_var_name = "waboot_wbf_components_active_tab";

        let $addNewComponentsPage = $("#addNewComponents");
        if($addNewComponentsPage.length > 0){
            //Initialize the Vue App
            new Vue(AddNewComponentsPageHandlerParams);
        }

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

        //Components tabs
        let toggle_components = function(category){
            let $component_blocks = $("[data-component]");
            $component_blocks.each(function (index) {
                if($(this).is("[data-category='"+category+"']")){
                    $(this).show();
                    $components_nav.find("[data-category='"+category+"']").addClass("active");
                }else{
                    $(this).hide();
                }
            });
        };
        let $components_categories_tabs = $("li[data-category]");

        // Find if a selected tab is saved in localStorage
        let active_tab = this.get_active_tab(components_saved_selected_category_var_name);
        if (active_tab != '' && $("[data-category='"+active_tab+"']").length > 0) {
            toggle_components(active_tab);
        }else{
            //Activate the first category
            let first_category = $($components_categories_tabs[0]).data("category");
            $($components_categories_tabs[0]).addClass("active");
            toggle_components(first_category);
        }

        if($components_categories_tabs.length > 0){
            $components_categories_tabs.on("click",function(e){
                e.preventDefault();
                let selected_category = $(this).data("category");
                $components_categories_tabs.removeClass("active");
                $(this).addClass("active");
                if (typeof(localStorage) != 'undefined' ) {
                    localStorage.setItem(components_saved_selected_category_var_name, selected_category); //Save the selected category
                }
                toggle_components(selected_category);
            });
        }
    }
    manage_theme_options_page(){
        "use strict";
        let $options_page_wrapper = $("#optionsframework-wrapper"),
            $options_nav = $(".optionsframework-nav");
        const theme_options_saved_selected_category_var_name = "waboot_wbf_theme_options_active_tab";

        if($options_page_wrapper.length <= 0){
            return;
        }

        //Options tabs
        let toggle_option_groups = function(category){
            let $option_groups = $("section[data-category]");
            $option_groups.each(function (index) {
                if($(this).is("[data-category='"+category+"']")){
                    $(this).show();
                    $options_nav.find("[data-category='"+category+"']").addClass("active");
                }else{
                    $(this).hide();
                }
            });
        };
        let $options_categories_tabs = $("li[data-category]");

        // Find if a selected tab is saved in localStorage
        let active_tab = this.get_active_tab(theme_options_saved_selected_category_var_name);
        if (active_tab != '' && $("[data-category='"+active_tab+"']").length > 0) {
            toggle_option_groups(active_tab);
        }else{
            //Activate the first category
            let first_category = $($options_categories_tabs[0]).data("category");
            $($options_categories_tabs[0]).addClass("active");
            toggle_option_groups(first_category);
        }

        if($options_categories_tabs.length > 0){
            $options_categories_tabs.on("click",function(e){
                e.preventDefault();
                let selected_category = $(this).data("category");
                $options_categories_tabs.removeClass("active");
                $(this).addClass("active");
                if (typeof(localStorage) != 'undefined' ) {
                    localStorage.setItem(theme_options_saved_selected_category_var_name, selected_category); //Save the selected category
                }
                toggle_option_groups(selected_category);
            });
        }
    }

    /**
     * Get the active tab from local storage
     *
     * @param localStorage_var_name
     * @returns {string}
     */
    get_active_tab(localStorage_var_name){
        // Find if a selected tab is saved in localStorage
        let active_tab = '';
        if ( typeof(localStorage) != 'undefined' ) {
            active_tab = localStorage.getItem(localStorage_var_name); //Check for active tab
            if(active_tab != null && active_tab.match(/http/)){ //Hardcoded fix for some incompatibilities
                active_tab = '';
            }
            if(active_tab == null){
                active_tab = '';
            }
        }
        return active_tab;
    }
}