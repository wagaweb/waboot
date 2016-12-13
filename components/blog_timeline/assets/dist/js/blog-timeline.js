jQuery(document).ready(function($){
    "use strict";

    $ ('.blog-timeline article:odd').addClass('timeline-inverted');

    var my_posts = $("[rel=tooltip]");
    var size = $(window).width();

    var the_post = "";
    var i = 0;
    for(i=0;i<my_posts.length;i++){
        the_post = $(my_posts[i]);
        if(the_post.hasClass('invert') && size >=767 ){
            the_post.tooltip({ placement: 'left'});
            the_post.css("cursor","pointer");
        }else{
            the_post.tooltip({ placement: 'rigth'});
            the_post.css("cursor","pointer");
        }
    }
});