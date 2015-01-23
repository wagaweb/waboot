jQuery(document).ready(function ($) {
    "use strict";
    $('.behavior-metabox-image').click(function(){
        $(this).parents(".behavior-images-options").find('.behavior-metabox-image').removeClass('behavior-metabox-image-selected');
        $(this).addClass('behavior-metabox-image-selected');
    });

    $('.behavior-metabox-image-default').click(function(){
        $(this).parent(".behavior-images-wrapper").find('.behavior-metabox-image').removeClass('behavior-metabox-image-selected');
    });
});
