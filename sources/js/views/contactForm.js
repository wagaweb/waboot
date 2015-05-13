module.exports = Backbone.View.extend({
    events: {
        "submit": "onSubmit"
    },
    fields: [],
    message_tpl: null,
    initialize: function() {
        "use strict";
        //Set the profile of the email receiver on the model
        this.model.set("recipient", {
            id: this.$el.find("[name='to[id]']").val(),
            name: this.$el.find("[name='to[name]']").val(),
            mail: this.$el.find("[name='to[email]']").val()
        });
        this.model.set("postID", this.$el.find("[name=fromID]").val()); //Set the post ID on the model
        //Set the fields on the view
        var self = this;
        this.$el.find("[data-field]").each(function(){
            self.fields.push({
                $el: jQuery(this),
                validation: jQuery(this).attr("data-validation")
            })
        });
        //Prevent form submitting
        this.$el.submit(function(e) {
            e.preventDefault();
        });
        //Get the error message TPL
        this.message_tpl = _.template(this.$el.find("[data-messageTPL]").html());
        //Listen to errors
        this.listenTo(this.model, 'error', this.onError);
    },
    onSubmit: function() {
        "use strict";
        this.$el.removeClass("has-error");
        this.$el.find(".form-group").removeClass("error");
        this.$el.find("span.error").remove();

        this.model.setData(this.fields);

        if (!this.model.get("error")) {
            var self = this;
            this.model.sendmail().done(function(data, textStatus, jqXHR) {
                switch (data) {
                    case 0:
                        self.$el.html(self.message_tpl({
                            msgclass: 'bg-danger',
                            msg: wbData.contactForm.labels.error
                        }));
                        break;
                    case 1:
                        self.$el.html(self.message_tpl({
                            msgclass: 'bg-warning',
                            msg: wbData.contactForm.labels.warning
                        }));
                        break;
                    case 2:
                        self.$el.html(self.message_tpl({
                            msgclass: 'bg-success',
                            msg: wbData.contactForm.labels.success
                        }));
                        break;
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                self.$el.html(self.message_tpl({
                    msgclass: 'bg-warning',
                    msg: wbData.contactForm.labels.warning
                }));
            });
        }
    },
    onError: function(e) {
        "use strict";
        var $error_el = e.$el,
            error_code = e.code;
        switch (error_code) {
            case "isEmpty":
                $error_el.after("<span class='error'>"+wbData.contactForm.labels.errors[error_code]+"</span>");
                break;
            default:
                $error_el.after("<span class='error'>"+wbData.contactForm.labels.errors['_default_']+"</span>");
                break;
        }
        $error_el.parents(".form-group").addClass("has-error");
    }
});
