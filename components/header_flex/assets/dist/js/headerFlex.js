jQuery(document).ready(function($){

    /*$(document).click(function() {
        $(".sub-menu").hide();
    });*/
    $('.menu-item-has-children > a').click(function(e) {

        var $target = $(e.currentTarget);
        $('.sub-menu').each( function(index,el){
            if($(el).prev('a') != $target){
                $(this).hide();
            }else{
                console.log('dentro');
                $(this).slideToggle();
            }
        });

        //e.stopPropagation();
        e.preventDefault();
    });


    $('.navbar-toggle').click(function(){
        $('.main-navigation').toggle({
            'easing': 'swing'
        });
    });

    $(window).on('resize', function(){

    });

});