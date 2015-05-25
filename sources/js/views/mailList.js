module.exports = Backbone.View.extend({
    template: null,
    events: {
        "click .next-page": "goToNextPage",
        "click .prev-page": "goToPrevPage",
        "click .first-page": "goToFirstPage",
        "click .last-page": "goToLastPage",
        "change [name=advanced_enabled]": "render"
    },
    initialize: function(){
        "use strict";
        this.template = _.template(this.$el.find("#waboot-received-mails-tpl").html());
        this.render();
    },
    render: function(){
        "use strict";
        var html;

        html += this.template({
            mails: this.model.get_emails(),
            mails_count: this.model.get("emails_count"),
            pages_count: this.model.get("pages_count"),
            current_page: this.model.get("page")
        });

        this.$el.html(html);
    },
    goToPage: function(n){

    },
    goToFirstPage: function(){
        "use strict";
        this.goToPage(1);
    },
    goToLastPage: function(){
        "use strict";
        this.goToPage(this.model.get("pages_count"));
    },
    goToNextPage: function(){
        "use strict";
        this.goToPage(this.model.get("current_page") + 1);
    },
    goToPrevPage: function(){
        "use strict";
        this.goToPage(this.model.get("current_page") - 1);
    }
});
