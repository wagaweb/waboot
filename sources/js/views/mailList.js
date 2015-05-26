module.exports = Backbone.View.extend({
    template: null,
    events: {
        "click .next-page": "goToNextPage",
        "click .prev-page": "goToPrevPage",
        "click .first-page": "goToFirstPage",
        "click .last-page": "goToLastPage",
        "click #cb": "selectAllCombos",
        "click .view a": "openContentModal"
    },
    initialize: function(){
        "use strict";
        this.template = _.template(this.$el.find("#waboot-received-mails-tpl").html());
        this.listenTo(this.model,"pageChanged",this.render());
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
    selectAllCombos: function(){
        "use strict";
        var checkboxes = this.$el.find(".check-column input[name='mails[]']");
        checkboxes.prop("checked",!checkboxes.prop("checked"));
    },
    openContentModal: function(e){
        "use strict";
        var $mail_el = jQuery(e.target);
    },
    goToPage: function(n){
        "use strict";
        this.model.setPage(n);
        this.render();
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
        this.goToPage(this.model.get("page") + 1);
    },
    goToPrevPage: function(){
        "use strict";
        this.goToPage(this.model.get("page") - 1);
    }
});
