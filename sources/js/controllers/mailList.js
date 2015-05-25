module.exports = Backbone.Model.extend({
    defaults: {
        emails_data: [],
        page: 1,
        results_per_page: 2,
        pages_count: 0,
        emails_count: 0
    },
    initialize: function(){
        "use strict";
        this.set("emails_count",this.get("emails_data").length);
        this.set("pages_count",Math.round(this.get("emails_count") / this.get("results_per_page")));
        //this.on("change",this.onPageChange());
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
    }
});