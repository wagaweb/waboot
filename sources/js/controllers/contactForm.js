module.exports = Backbone.Model.extend({
    defaults: {
        contactProfile: {
            name: "",
            mail: "",
            id: ""
        },
        property: 0,
        name: "",
        phone: "",
        mail: "",
        subject: wbData.contactForm.contact_email_subject,
        message: "",
        terms_accepted: ""
    },
    setData: function(data) {
        "use strict";
        var error = false;
        if (_.isEmpty(data)) {
            this.trigger("error", "emptyData");
            error = true;
        }
        if (_.isEmpty(data.name)) {
            this.trigger("error", "emptyName");
            error = true;
        } else {
            this.set("name", this.escapeHtml(data.name));
        }
        if (_.isEmpty(data.phone)) {
            this.trigger("error", "emptyPhone");
            error = true;
        } else {
            this.set("phone", this.escapeHtml(data.phone));
        }
        if (_.isEmpty(data.mail)) {
            this.trigger("error", "emptyEmail");
            error = true;
        } else {
            this.set("mail", this.escapeHtml(data.mail));
        }
        if (_.isEmpty(data.message)) {
            this.trigger("error", "emptyMessage");
            error = true;
        } else {
            this.set("message", this.escapeHtml(data.message));
        }
        if (!data.terms_accepted) {
            this.trigger("error", "termsUnchecked");
            error = true;
        } else {
            this.set("terms_accepted", true);
        }
        this.set("error", error);
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
    sendmail: function() {
        "use strict";
        var contactProfile = this.get("contactProfile");
        return jQuery.ajax(wbData.ajaxurl, {
            data: {
                action: "wbpmp_send_mail",
                to: contactProfile.mail,
                to_id: contactProfile.id,
                subject: this.get("subject"),
                message: this.get("message"),
                from: {
                    name: this.get("name"),
                    phone: this.get("phone"),
                    mail: this.get("mail"),
                    property: this.get("property")
                },
                post_id: this.get("property")
            },
            dataType: "json",
            method: "POST"
        });
    }
});
