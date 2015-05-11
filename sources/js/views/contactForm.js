module.exports = Backbone.View.extend({
    events: {
        "submit": "onSubmit"
    },
    $nameInput: null,
    $phoneInput: null,
    $emailInput: null,
    $messageInput: null,
    $termsCheck: null,
    message_tpl: null,
    initialize: function() {
        "use strict";
        this.model.set("contactProfile", {
            name: this.$el.attr("data-contactName"),
            mail: this.$el.attr("data-contactEmail"),
            id: this.$el.attr("data-contactID")
        });
        this.model.set("property", this.$el.attr("data-propertyID"));
        this.$nameInput = this.$el.find("[name='from[name]']");
        this.$phoneInput = this.$el.find("[name='from[phone]']");
        this.$emailInput = this.$el.find("[name='from[email]']");
        this.$messageInput = this.$el.find("[name=inputMessage]");
        this.$termsCheck = this.$el.find("[name=terms]");
        this.$el.submit(function(e) {
            e.preventDefault();
        });
        this.message_tpl = _.template(this.$el.find("[data-messageTPL]").html());
        this.listenTo(this.model, 'error', this.onError);
    },
    onSubmit: function() {
        "use strict";
        this.$el.removeClass("has-error");
        this.$el.find(".form-group").removeClass("error");
        this.$el.find("span.error").remove();

        var name = this.$nameInput.val(),
            phone = this.$phoneInput.val(),
            email = this.$emailInput.val(),
            message = this.$messageInput.val(),
            terms_accepted = this.$termsCheck.is(":checked");

        this.model.setData({
            name: name,
            phone: phone,
            mail: email,
            message: message,
            terms_accepted: terms_accepted
        });

        if (!this.model.get("error")) {
            var self = this;
            this.model.sendmail().done(function(data, textStatus, jqXHR) {
                switch (data) {
                    case 0:
                        self.$el.html(self.message_tpl({
                            msgclass: 'bg-danger',
                            msg: wbData.contactForm.labels.contact_form.error
                        }));
                        break;
                    case 1:
                        self.$el.html(self.message_tpl({
                            msgclass: 'bg-warning',
                            msg: wbData.contactForm.labels.contact_form.warning
                        }));
                        break;
                    case 2:
                        self.$el.html(self.message_tpl({
                            msgclass: 'bg-success',
                            msg: wbData.contactForm.labels.contact_form.success
                        }));
                        break;
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                self.$el.html(self.message_tpl({
                    msgclass: 'bg-warning',
                    msg: wbData.contactForm.labels.contact_form.warning
                }));
            });
        }
    },
    onError: function(e) {
        "use strict";
        var $error_el;
        switch (e) {
            case "emptyName":
                $error_el = this.$nameInput;
                $error_el.after("<span class='error'>Il campo non può essere lasciato vuoto</span>");
                break;
            case "emptyPhone":
                $error_el = this.$phoneInput;
                $error_el.after("<span class='error'>Il campo non può essere lasciato vuoto</span>");
                break;
            case "emptyEmail":
                $error_el = this.$emailInput;
                $error_el.after("<span class='error'>Il campo non può essere lasciato vuoto</span>");
                break;
            case "emptyMessage":
                $error_el = this.$messageInput;
                $error_el.after("<span class='error'>Il campo non può essere lasciato vuoto</span>");
                break;
            case "termsUnchecked":
                $error_el = this.$termsCheck;
                break;
            case "emptyData":
                $error_el = this.$el;
                $error_el.after("<span class='error'>Il campo non può essere lasciato vuoto</span>");
                break;
            default:
                $error_el = this.$el;
                break;
        }
        $error_el.parents(".form-group").addClass("has-error");
    }
});
