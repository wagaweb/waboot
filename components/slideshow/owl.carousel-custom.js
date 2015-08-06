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
 *   ATTENTION: the slideshow id must be prefixed with owl- (example: owl-homepage-slideshow)
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
var owlcarousel_params = {};

function wb_slideshow_start(id){
    var params = typeof owlcarousel_params[id] !== "undefined" ? owlcarousel_params[id] : {},
        $el = jQuery("#"+id),
        animable_items = wb_slideshow_get_animable_items($el);

    owlcarousels[id] = $el.owlCarousel(params);

    if(animable_items.length > 0){
        //Do the animations on the first visible item
        jQuery.each(animable_items,function(){
            wb_slideshow_do_anim(this);
        });
        owlcarousels[id]
            .on('refreshed.owl.carousel', function(event){
                console.log("refreshed");
            })
            .on('changed.owl.carousel', function(event) {
                jQuery.each(wb_slideshow_get_animable_items($el,true),function(){
                    wb_slideshow_stop_anim(this);
                });
            })
            .on('translate.owl.carousel',function(event){
                //$el.find(".didascalia").hide();
            })
            .on('translated.owl.carousel',function(event){
                jQuery.each(wb_slideshow_get_animable_items($el,true),function(){
                    wb_slideshow_do_anim(this);
                    $el.find(".owl-item").show();
                    //$el.find(".didascalia").show();
                });
            });
    }
}

/**
 * Add the specified animation class to the item
 * @param item
 */
function wb_slideshow_do_anim(item){
    "use strict";
    item.$el.addClass(item.animation);
}

/**
 * Remove the specified animation class from the item
 * @param item
 */
function wb_slideshow_stop_anim(item){
    "use strict";
    item.$el.removeClass(item.animation);
}

/**
 * Get the animable item for the selected carousel
 * @param $carousel [JQuery element]
 * @param from_active [Boolean]
 * @returns {Array}
 */
function wb_slideshow_get_animable_items($carousel,from_active){
    "use strict";
    var items = [],
        carousel_id = $carousel.attr("id"),
        owlactive_el = typeof from_active == "undefined" ? false : $carousel.find(".owl-item.active");
    if(typeof(owlcarousel_params[carousel_id]) !== "undefined" && typeof(owlcarousel_params[carousel_id].wb_animation) !== "undefined"){
        jQuery.each(owlcarousel_params[carousel_id].wb_animation,function(){
            items.push({
                $el: !owlactive_el ? $carousel.find(this.item_selector) : owlactive_el.find(this.item_selector),
                animation: this.animation_classes
            });
        });
    }
    return items;
}