jQuery(document).ready(function($){

    $('.menu-item-has-children').click(function(e) {
        e.preventDefault();
        $(".sub-menu").hide();
        $(this).find(".sub-menu").slideToggle();
    });


    $('.navbar-toggle').click(function(){
        $('.main-navigation').toggle({
            'easing': 'swing'
        });
    });

    $(window).on('resize', function(){

    });

});