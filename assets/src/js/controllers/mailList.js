module.exports = Backbone.Model.extend({
    defaults: {
        emails_data: [],
        page: 1,
        results_per_page: 3,
        pages_count: 1,
        emails_count: 0
    },
    initialize: function(){
        "use strict";
        this.set("emails_count",this.get("emails_data").length);
        this.setStats();
    },
    setStats: function(){
        this.set("emails_count",this.get("emails_data").length);
        var pages_count = Math.round(this.get("emails_count") / this.get("results_per_page"));
        if(pages_count <= 1) pages_count = 1;
        this.set("pages_count",pages_count);
    },
    get_emails: function(){
        "use strict";
        var offset = (function(page,results_per_page){
            if(page === 1){
                return 0;
            }else{
                return results_per_page * (page - 1);
            }
        })(this.get("page"),this.get("results_per_page"));

        var target = this.get("page") !== this.get("pages_count") ? this.get("results_per_page") : 0;

        if(target !== 0){
            return this.get("emails_data").slice(offset,target);
        }else{
            return this.get("emails_data").slice(offset);
        }
    },
    setPage: function(n){
        "use strict";
        var max_pages = this.get("pages_count");
        if(n < 1){
            n = 1;
        }
        if(n > max_pages){
            n = max_pages;
        }
        this.set("page",n);
        this.trigger("pageChanged");
    },
    deleteMail: function(n){
       var self = this,
           data = {
            action: "wbft_delete_contact_email",
            id: n
       };

       var call = jQuery.ajax(wbData.ajaxurl, {
           data: data,
           dataType: "json",
           method: "POST"
       });

       call.done(function(data, textStatus, jqXHR){
            //Delete the mail
            var current_emails_data = self.get("emails_data"),
                mail_to_delete = _.findWhere(current_emails_data,{id:""+n+""}),
                new_emails_data = _.difference(current_emails_data,mail_to_delete);
            self.set("emails_data", new_emails_data);
            self.setStats();
            self.trigger("emailDeleted",n);
       }).fail(function(jqXHR, textStatus, errorThrown){
            console.log("Failed to delete mail "+n);
       });
    }
});