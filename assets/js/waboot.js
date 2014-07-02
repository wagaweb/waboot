//http://getbootstrap.com/getting-started/#support
if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
    var msViewportStyle = document.createElement('style')
    msViewportStyle.appendChild(
        document.createTextNode(
            '@-ms-viewport{width:auto!important}'
        )
    );
    document.querySelector('head').appendChild(msViewportStyle);
}

jQuery(document).ready(function() {
    // Style form controls
    jQuery(type = "select").addClass('form-control input-sm');
    jQuery('input#submit').addClass('btn btn-default');
    // Tables
    jQuery('table').addClass('table');
});
