jQuery(document).ready(function($){

    $('.menu-item-has-children > a').click(function(e) {
        e.preventDefault();
        $(".sub-menu").hide();
        $(this).next(".sub-menu").slideToggle();
    });


    $('.navbar-toggle').click(function(){
        $('.main-navigation').toggle({
            'easing': 'swing'
        });
    });

    $(window).on('resize', function(){

    });

});