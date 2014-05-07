jQuery(document).ready(function(){
    enableDropDown(jQuery,window,200);
    jQuery('a[data-toggle]').on("click",function(){
        var url = jQuery(this).attr("href");
        console.log(url);
        window.location = url;
    });
});

function enableDropDown($,window, delay){
    // http://jsfiddle.net/AndreasPizsa/NzvKC/
    var theTimer = 0;
    var theElement = null;
    var theLastPosition = {x:0,y:0};
    $('[data-toggle="dropdown"]')
        .closest('li')
        .on('mouseenter', function (inEvent) {
            if (theElement) theElement.removeClass('open');
            window.clearTimeout(theTimer);
            theElement = $(this);

            theTimer = window.setTimeout(function () {
                theElement.addClass('open');
            }, delay);
        })
        .on('mousemove', function (inEvent) {
            if(Math.abs(theLastPosition.x - inEvent.ScreenX) > 4 ||
                Math.abs(theLastPosition.y - inEvent.ScreenY) > 4)
            {
                theLastPosition.x = inEvent.ScreenX;
                theLastPosition.y = inEvent.ScreenY;
                return;
            }

            if (theElement.hasClass('open')) return;
            window.clearTimeout(theTimer);
            theTimer = window.setTimeout(function () {
                theElement.addClass('open');
            }, delay);
        })
        .on('mouseleave', function (inEvent) {
            window.clearTimeout(theTimer);
            theElement = $(this);
            theTimer = window.setTimeout(function () {
                theElement.removeClass('open');
            }, delay);
        });
}
