var owlcarousels = []; //Store all carousels

/*
 * If you wish to add some setting from an external file to owlcarousel_params, then do:
 *   jQuery.extend(owlcarousel_params,{
 *       'newid': {
 *           autoplay: true
 *           ...
 *           ...
 *           ...
 *       }
 *   });
 *
 *   ANIMATIONS:
 *   You can also pass an array called wb_animation, with the animations params. For example:
 *   jQuery.extend(owlcarousel_params,{
 *       'newid': {
 *          autoplay: true,
 *          wb_animation: [
 *               {
 *                  item_selector: "div:first-child",
 *                  animation_classes: "zoom-image"
 *               },
 *               {
 *                  item_selector: ".didascalia h2",
 *                  animation_classes: "animated fadeInDown"
 *               },
 *               {
 *                  item_selector: ".didascalia p",
 *                  animation_classes: "animated fadeInDown"
 *               },
 *               {
 *                  item_selector: ".didascalia a",
 *                  animation_classes: "animated fadeInUp"
 *               }
 *           ]
 *       }
 *   });
 */
var owlcarousel_params = {
    'owl-slide-home': {
        autoplay: true
    }
};

function wb_slideshow_do_anim(item){
    "use strict";
    item.$el.addClass(item.animation);
}

function wb_slideshow_stop_anim(item){
    "use strict";
    item.$el.removeClass(item.animation);
}

/**
 * Set the animable item for the selected carousel
 * @param $carousel
 * @returns {Array}
 */
function wb_slideshow_get_to_animate_items($carousel){
    "use strict";
    var items = [],
        carousel_id = $carousel.attr("id"),
        owlactive_el = $carousel.find(".owl-item.active");
    if(typeof(owlcarousel_params[carousel_id]) !== "undefined" && typeof(owlcarousel_params[carousel_id].wb_animation) !== "undefined"){
        jQuery.each(owlcarousel_params[carousel_id].wb_animation,function(){
            items.push({
                $el: owlactive_el.find(this.item_selector),
                animation: this.animation_classes
            });
        });
    }
    return items;
}

function wb_slideshow_get_animable_items($carousel){
    "use strict";
    var items = [],
        carousel_id = $carousel.attr("id");
    if(typeof(owlcarousel_params[carousel_id]) !== "undefined" && typeof(owlcarousel_params[carousel_id].wb_animation) !== "undefined"){
        jQuery.each(owlcarousel_params[carousel_id].wb_animation,function(){
            items.push({
                $el: $carousel.find(this.item_selector),
                animation: this.animation_classes
            });
        });
    }
    return items;
}