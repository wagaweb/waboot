module.exports = Backbone.View.extend({
    template: null,
    modals: [],
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
        var self = this;
        var html;

        html += this.template({
            mails: this.model.get_emails(),
            mails_count: this.model.get("emails_count"),
            pages_count: this.model.get("pages_count"),
            current_page: this.model.get("page")
        });

        _.each(jQuery(html),function(el){
            if(jQuery(el).attr("data-content-of")){
                var mail_id = jQuery(el).data("content-of");
                if(_.isEmpty(_.findWhere(self.modals,{id:mail_id}))){
                    self.modals.push({
                        id: mail_id,
                        $el: jQuery(el).dialog({autoOpen:false})
                    })
                }
            }
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
        var $mail_el = jQuery(e.target),
            target = _.findWhere(this.modals,{id:$mail_el.data("view-content-of")});

        if(!_.isEmpty(target)){
            target.$el.dialog("open");
        }
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
