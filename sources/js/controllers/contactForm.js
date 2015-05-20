module.exports = Backbone.Model.extend({
    defaults: {
        recipient: {
            name: "",
            mail: "",
            id: ""
        },
        senderInfo: {},
        postID: 0,
        subject: wbData.contactForm.contact_email_subject,
        message: ""
    },
    setData: function(fields) {
        "use strict";
        var error_occurred = false,
            self = this,
            senderInfo = this.get("senderInfo");

        _.each(fields, function(f, iteratee, context) {
            var val = f.$el.val(),
                name = f.$el.attr('name'),
                validation = f.validation;

            switch (validation) {
                case "!empty":
                    if (_.isEmpty(val)) {
                        self.trigger("error", {
                            $el: f.$el,
                            code: "isEmpty"
                        });
                        error_occurred = true;
                    } else {
                        self.updateData(name, val);
                    }
                    break;
            }
        });

        this.set("error", error_occurred);
    },
    escapeHtml: function(string) {
        "use strict";
        var entityMap = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': '&quot;',
            "'": '&#39;',
            "/": '&#x2F;'
        };
        return String(string).replace(/[&<>"'\/]/g, function(s) {
            return entityMap[s];
        });
    },
    updateData: function(name, val) {
        var senderInfo = this.get("senderInfo");

        val = this.escapeHtml(val);

        var matches = name.match(/from\[([a-zA-Z]+)\]/);

        if (matches) {
            senderInfo = this.get("senderInfo");
            senderInfo[matches[1]] = val;
            this.set('senderInfo', senderInfo);
        } else {
            if (name == 'message') {
                this.set("message", val);
            } else {
                senderInfo = this.get("senderInfo");
                senderInfo[name] = val;
                this.set('senderInfo', senderInfo);
            }
        }
    },
    sendmail: function() {
        "use strict";
        var recipient = this.get("recipient"),
            data = {
                action: "wbft_send_contact_email",
                to: recipient.mail,
                to_id: recipient.id,
                subject: this.get("subject"),
                message: this.get("message"),
                from: (function(data) {
                    var return_data = {};
                    _.each(data, function(val, key) {
                        return_data[key] = val;
                    });
                    return return_data;
                })(this.get("senderInfo")),
                post_id: this.get("postID")
            };
        return jQuery.ajax(wbData.ajaxurl, {
            data: data,
            dataType: "json",
            method: "POST"
        });
    }
});
