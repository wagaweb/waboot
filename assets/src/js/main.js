import Header from './frontend/header';

jQuery(document).ready(function($) {
    "use strict";

    new Header('.menu-item-has-children');

    asideBodyClass();

    $(window).on("load",function(){
        //Put here snippets
    });
});

function asideBodyClass() {
    let $ = jQuery;
    if($('.main__aside').length > 0) {
        $('body').addClass('with-sidebar');
    }
}
