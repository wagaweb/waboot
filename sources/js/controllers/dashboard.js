module.exports = Backbone.Model.extend({
    initialize: function() {
        "use strict";
        console.log("It'admin time!");
        this.do_stuff();
    },
    do_stuff: function(){
        "use strict";
        var $ = jQuery,
            $mailtable = $("#waboot-received-mails-view"),
            $recent_posts_widget_pt_selector = $("[data-wbrw-post-type-selector]");
        /**
         * Init received mails viewerr
         */
        if($mailtable.length > 0){
            var MailListView = require("../views/mailList.js"),
                MailListModel = require("./mailList.js"),
                MailWindow = new MailListView({
                    model: new MailListModel({
                        emails_data: (function(){
                            if(!_.isUndefined(wbData.contactForm.mails)){
                                return jQuery.parseJSON(wbData.contactForm.mails);
                            }else{
                                return [];
                            }
                        })()
                    }),
                    el: $mailtable
                });
        }
        /**
         * RECENT POST WIDGET
         */
        if($recent_posts_widget_pt_selector.length > 0){
            console.log("Recent Post Widget Time!");
        }
    }
});
