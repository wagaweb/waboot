(function ($) {

    acf.fields.file = acf.field.extend({

        type: 'file',
        $el: null,

        events: {
            'click [data-name="add"]': 'add',
            'click [data-name="edit"]': 'edit',
            'click [data-name="remove"]': 'remove',
        },

        focus: function () {

            this.$el = this.$field.find('.acf-file-uploader');

            this.settings = acf.get_data(this.$el);

        },

        add: function () {

            // reference
            var self = this;


            // vars
            var field_key = acf.get_data(this.$field, 'key');


            // get repeater
            var $repeater = acf.get_closest_field(this.$field, {type: 'repeater'});


            // popup
            var frame = acf.media.popup({

                title: acf._e('file', 'select'),
                mode: 'select',
                type: '',
                multiple: $repeater.exists(),
                library: this.settings.library,

                select: function (attachment, i) {

                    // select / add another image field?
                    if (i > 0) {

                        // vars
                        var $tr = self.$field.parent(),
                            $next = false;


                        // find next image field
                        $tr.nextAll('.acf-row').not('.clone').each(function () {

                            // get next $field
                            $next = acf.get_field(field_key, $(this));


                            // bail early if $next was not found
                            if (!$next) {

                                return;

                            }


                            // bail early if next file uploader has value
                            if ($next.find('.acf-file-uploader.has-value').exists()) {

                                $next = false;
                                return;

                            }


                            // end loop if $next is found
                            return false;

                        });


                        // add extra row if next is not found
                        if (!$next) {

                            $tr = acf.fields.repeater.doFocus($repeater).add();


                            // bail early if no $tr (maximum rows hit)
                            if (!$tr) {

                                return false;

                            }


                            // get next $field
                            $next = acf.get_field(field_key, $tr);

                        }


                        // update $el
                        self.doFocus($next);

                    }


                    // vars
                    var file = {
                        id: attachment.id,
                        title: attachment.attributes.title,
                        name: attachment.attributes.filename,
                        url: attachment.attributes.url,
                        icon: attachment.attributes.icon,
                        size: attachment.attributes.filesize
                    };


                    // add file to field
                    self.render(file);

                }
            });

        },

        render: function (file) {

            // set atts
            this.$el.find('[data-name="icon"]').attr('src', file.icon);
            this.$el.find('[data-name="title"]').text(file.title);
            this.$el.find('[data-name="name"]').text(file.name).attr('href', file.url);
            this.$el.find('[data-name="size"]').text(file.size);
            this.$el.find('[data-name="id"]').val(file.id).trigger('change');


            // set div class
            this.$el.addClass('has-value');

        },

        edit: function () {

            // reference
            var self = this;


            // vars
            var id = this.$el.find('[data-name="id"]').val();


            // popup
            var frame = acf.media.popup({

                title: acf._e('file', 'edit'),
                button: acf._e('file', 'update'),
                mode: 'edit',
                id: id,

                select: function (attachment, i) {

                    // vars
                    var file = {
                        id: attachment.id,
                        title: attachment.attributes.title,
                        name: attachment.attributes.filename,
                        url: attachment.attributes.url,
                        icon: attachment.attributes.icon,
                        size: attachment.attributes.filesize
                    };


                    // add file to field
                    self.render(file);

                }
            });

        },

        remove: function () {

            // vars
            var file = {
                id: '',
                title: '',
                name: '',
                url: '',
                icon: '',
                size: ''
            };


            // add file to field
            this.render(file);


            // remove class
            this.$el.removeClass('has-value');

        }

    });


})(jQuery);
