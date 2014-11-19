(function ($) {

    // comon
    acf.pro = acf.model.extend({

        actions: {
            'conditional_logic_show_field': 'show_field_cl',
            'conditional_logic_hide_field': 'hide_field_cl'
        },

        filters: {
            'is_field_ready_for_js': 'is_field_ready_for_js',
        },

        is_field_ready_for_js: function (ready, $field) {

            // debug
            //console.log('is_field_ready_for_js %o, %b', $field, ready);


            // check cache
            if ($field.data('acf_clone')) {

                return false;

            }


            // repeater sub field
            if ($field.closest('.clone').exists()) {

                $field.data('acf_clone', 1);
                return false;

            }


            // flexible content sub field
            if ($field.closest('.clones').exists()) {

                $field.data('acf_clone', 1);
                return false;

            }


            // return
            return ready;

        },

        show_field_cl: function ($field) {

            // bail early if not a sub field
            if (!acf.is_sub_field($field)) {

                return;

            }


            // bail early if not a td
            if (!$field.is('td')) {

                return;

            }


            // vars
            var key = acf.get_field_key($field),
                $table = $field.closest('.acf-table'),
                $th = $table.find('> thead > tr > th[data-key="' + key + '"]'),
                $td = $table.find('> tbody > tr:not(.clone) > td[data-key="' + key + '"]');


            // remove class
            $field.removeClass('appear-empty');


            // show entire column
            $td.filter('.hidden-by-conditional-logic').addClass('appear-empty');
            $th.removeClass('hidden-by-conditional-logic');


            // render table
            this.render_table($table);

        },

        hide_field_cl: function ($field) {

            // debug
            //console.log('conditional_logic_hide_field %o', $field);

            // bail early if not a sub field
            if (!acf.is_sub_field($field)) {

                return;

            }


            // bail early if not a td
            if (!$field.is('td')) {

                return;

            }


            // vars
            var key = acf.get_field_key($field),
                $table = $field.closest('.acf-table'),
                $th = $table.find('> thead > tr > th[data-key="' + key + '"]'),
                $td = $table.find('> tbody > tr:not(.clone) > td[data-key="' + key + '"]');


            // add class
            $field.addClass('appear-empty');

            //console.log($td);
            // if all cells are hidden, hide the entire column
            if ($td.filter('.hidden-by-conditional-logic').length == $td.length) {

                $td.removeClass('appear-empty');
                $th.addClass('hidden-by-conditional-logic');

            }


            // render table
            this.render_table($table);

        },

        render_table: function ($table) {

            // bail early if table is row layout
            if ($table.hasClass('row-layout')) {

                return;

            }


            // vars
            var $th = $table.find('> thead > tr > th'),
                available_width = 100,
                count = 0;


            // accomodate for order / remove
            if ($th.filter('.order').exists()) {

                available_width = 93;

            }


            // clear widths
            $th.removeAttr('width');


            // update $th
            $th = $th.not('.order, .remove, .hidden-by-conditional-logic');


            // set custom widths first
            $th.filter('[data-width]').each(function () {

                // bail early if hit limit
                if ((count + 1) == $th.length) {

                    return false;

                }


                // increase counter
                count++;


                // vars
                var width = parseInt($(this).attr('data-width'));


                // remove from available
                available_width -= width;


                // set width
                $(this).attr('width', width + '%');

            });


            // set custom widths first
            $th.not('[data-width]').each(function () {

                // bail early if hit limit
                if ((count + 1) == $th.length) {

                    return false;

                }


                // increase counter
                count++;


                // cal width
                var width = available_width / $th.length;


                // set width
                $(this).attr('width', width + '%');

            });

        }

    });


    acf.fields.repeater = acf.field.extend({

        type: 'repeater',
        $el: null,
        $clone: null,

        actions: {
            'ready': 'initialize',
            'append': 'initialize'
        },

        events: {
            'click .acf-repeater-add-row': 'add',
            'click .acf-repeater-remove-row': 'remove',
            'mouseenter .acf-row': 'mouseenter'
        },

        focus: function () {

            this.$el = this.$field.find('.acf-repeater').first();
            this.$clone = this.$el.find('> table > tbody > tr.clone');

            this.settings = acf.get_data(this.$el);

        },

        initialize: function () {

            // reference
            var self = this,
                $field = this.$field;


            // sortable
            if (this.settings.max != 1) {

                this.$el.find('> table > tbody').unbind('sortable').sortable({

                    items: '> tr',
                    handle: '> td.order',
                    forceHelperSize: true,
                    forcePlaceholderSize: true,
                    scroll: true,

                    start: function (event, ui) {

                        acf.do_action('sortstart', ui.item, ui.placeholder);

                    },

                    stop: function (event, ui) {

                        acf.do_action('sortstop', ui.item, ui.placeholder);


                        // render
                        self.doFocus($field).render();

                    }
                });

            }


            // set column widths
            acf.pro.render_table(this.$el.find('> table'));


            // disable clone inputs
            this.$clone.find('[name]').attr('disabled', 'disabled');


            // render
            this.render();

        },

        count: function () {

            return this.$el.find('> table > tbody > tr').length - 1;

        },

        render: function () {

            // update order numbers
            this.$el.find('> table > tbody > tr').each(function (i) {

                $(this).children('td.order').html(i + 1);

            });


            // empty?
            if (this.count() == 0) {

                this.$el.addClass('empty');

            } else {

                this.$el.removeClass('empty');

            }


            // row limit reached
            if (this.settings.max > 0 && this.count() >= this.settings.max) {

                this.$el.addClass('disabled');
                this.$el.find('> .acf-hl .acf-button').addClass('disabled');

            } else {

                this.$el.removeClass('disabled');
                this.$el.find('> .acf-hl .acf-button').removeClass('disabled');

            }

        },

        add: function (e) {

            // find $before
            var $before = this.$clone;

            if (e && e.$el.is('.acf-icon')) {

                $before = e.$el.closest('.acf-row');

            }


            // validate
            if (this.settings.max > 0 && this.count() >= this.settings.max) {

                alert(acf._e('repeater', 'max').replace('{max}', this.settings.max));
                return false;

            }


            // create and add the new field
            var new_id = acf.get_uniqid(),
                html = this.$clone.outerHTML();


            // replace acfcloneindex
            var html = html.replace(/(="[\w-\[\]]+?)(acfcloneindex)/g, '$1' + new_id),
                $html = $(html);


            // remove clone class
            $html.removeClass('clone');


            // enable inputs
            $html.find('[name]').removeAttr('disabled');


            // add row
            $before.before($html);


            // trigger mouseenter on parent repeater to work out css margin on add-row button
            this.$field.parents('.acf-row').trigger('mouseenter');


            // update order
            this.render();


            // validation
            acf.validation.remove_error(this.$field);


            // setup fields
            acf.do_action('append', $html);


            // return
            return $html;
        },

        remove: function (e) {

            // reference
            var self = this,
                $field = this.$field;


            // vars
            var $tr = e.$el.closest('.acf-row'),
                $table = $tr.closest('table');


            // validate
            if (this.count() <= this.settings.min) {

                alert(acf._e('repeater', 'min').replace('{min}', this.settings.min));
                return false;
            }


            // animate out tr
            acf.remove_tr($tr, function () {

                // render
                self.doFocus($field).render();


                // trigger mouseenter on parent repeater to work out css margin on add-row button
                $field.closest('.acf-row').trigger('mouseenter');


                // trigger conditional logic render
                // when removing a row, there may not be a need for some appear-empty cells
                if ($table.hasClass('table-layout')) {

                    acf.conditional_logic.render($table);

                }


            });

        },

        mouseenter: function (e) {

            // vars
            var $td = e.$el.find('> td.remove'),
                $a = $td.find('.acf-repeater-add-row'),
                margin = ( $td.height() / 2 ) + 9; // 9 = padding + border


            // css
            $a.css('margin-top', '-' + margin + 'px');

        }


    });


    acf.fields.flexible_content = acf.field.extend({

        type: 'flexible_content',
        $el: null,
        $values: null,
        $clones: null,

        actions: {
            'ready': 'initialize',
            'append': 'initialize'
        },

        events: {
            'click .acf-fc-remove': 'remove',
            'click .acf-fc-layout-handle': 'toggle',
            'click .acf-fc-popup li a': 'add',
            'click .acf-fc-add': 'open_popup',
            'blur .acf-fc-popup .focus': 'close_popup'
        },

        focus: function () {

            this.$el = this.$field.find('.acf-flexible-content').first();
            this.$values = this.$el.children('.values');
            this.$clones = this.$el.children('.clones');


            // get options
            this.settings = acf.get_data(this.$el);


            // min / max
            this.settings.min = this.settings.min || 0;
            this.settings.max = this.settings.max || 0;

        },

        count: function () {

            return this.$values.children('.layout').length;

        },

        initialize: function () {

            // reference
            var self = this,
                $field = this.$field;


            // sortable
            if (this.settings.max != 1) {

                this.$values.unbind('sortable').sortable({

                    items: '> .layout',
                    handle: '> .acf-fc-layout-handle',
                    forceHelperSize: true,
                    forcePlaceholderSize: true,
                    scroll: true,

                    start: function (event, ui) {

                        acf.do_action('sortstart', ui.item, ui.placeholder);

                    },
                    stop: function (event, ui) {

                        acf.do_action('sortstop', ui.item, ui.placeholder);


                        // render
                        self.doFocus($field).render();
                    }
                });

            }


            // set column widths
            this.$values.find('> .layout > .acf-table').each(function () {

                acf.pro.render_table($(this));

            });


            // disable clone inputs
            this.$clones.find('[name]').attr('disabled', 'disabled');


            // render
            this.render();

        },

        render: function () {

            // update order numbers
            this.$values.children('.layout').each(function (i) {

                $(this).find('> .acf-fc-layout-handle .fc-layout-order').html(i + 1);

            });


            // empty?
            if (this.count() == 0) {

                this.$el.addClass('empty');

            } else {

                this.$el.removeClass('empty');

            }


            // row limit reached
            if (this.settings.max > 0 && this.count() >= this.settings.max) {

                this.$el.addClass('disabled');
                this.$el.find('> .acf-hl .acf-button').addClass('disabled');

            } else {

                this.$el.removeClass('disabled');
                this.$el.find('> .acf-hl .acf-button').removeClass('disabled');

            }

        },

        validate_add: function (layout) {

            // vadiate max
            if (this.settings.max > 0 && this.count() >= this.settings.max) {

                // vars
                var identifier = ( this.settings.max == 1 ) ? 'layout' : 'layouts',
                    s = acf._e('flexible_content', 'max');


                // translate
                s = s.replace('{max}', this.settings.max);
                s = s.replace('{identifier}', acf._e('flexible_content', identifier));


                // alert
                alert(s);


                // return
                return false;
            }


            // vadiate max layout
            var $popup = $(this.$el.children('.tmpl-popup').html()),
                $a = $popup.find('[data-layout="' + layout + '"]'),
                layout_max = parseInt($a.attr('data-max')),
                layout_count = this.$values.children('.layout[data-layout="' + layout + '"]').length;


            if (layout_max > 0 && layout_count >= layout_max) {

                // vars
                var identifier = ( layout_max == 1 ) ? 'layout' : 'layouts',
                    s = acf._e('flexible_content', 'max_layout');


                // translate
                s = s.replace('{max}', layout_count);
                s = s.replace('{label}', '"' + $a.text() + '"');
                s = s.replace('{identifier}', acf._e('flexible_content', identifier));


                // alert
                alert(s);


                // return
                return false;
            }


            // return
            return true;

        },

        validate_remove: function (layout) {

            // vadiate min
            if (this.settings.min > 0 && this.count() <= this.settings.min) {

                // vars
                var identifier = ( this.settings.min == 1 ) ? 'layout' : 'layouts',
                    s = acf._e('flexible_content', 'min') + ', ' + acf._e('flexible_content', 'remove');


                // translate
                s = s.replace('{min}', this.settings.min);
                s = s.replace('{identifier}', acf._e('flexible_content', identifier));
                s = s.replace('{layout}', acf._e('flexible_content', 'layout'));


                // return
                return confirm(s);

            }


            // vadiate max layout
            var $popup = $(this.$el.children('.tmpl-popup').html()),
                $a = $popup.find('[data-layout="' + layout + '"]'),
                layout_min = parseInt($a.attr('data-min')),
                layout_count = this.$values.children('.layout[data-layout="' + layout + '"]').length;


            if (layout_min > 0 && layout_count <= layout_min) {

                // vars
                var identifier = ( layout_min == 1 ) ? 'layout' : 'layouts',
                    s = acf._e('flexible_content', 'min_layout') + ', ' + acf._e('flexible_content', 'remove');


                // translate
                s = s.replace('{min}', layout_count);
                s = s.replace('{label}', '"' + $a.text() + '"');
                s = s.replace('{identifier}', acf._e('flexible_content', identifier));
                s = s.replace('{layout}', acf._e('flexible_content', 'layout'));


                // return
                return confirm(s);
            }


            // return
            return true;

        },

        open_popup: function (e) {

            // reference
            var $values = this.$values;


            // vars
            var $popup = $(this.$el.children('.tmpl-popup').html());


            // modify popup
            $popup.find('a').each(function () {

                // vars
                var min = parseInt($(this).attr('data-min')),
                    max = parseInt($(this).attr('data-max')),
                    name = $(this).attr('data-layout'),
                    label = $(this).text(),
                    count = $values.children('.layout[data-layout="' + name + '"]').length,
                    $status = $(this).children('.status');


                if (max > 0) {

                    // find diff
                    var available = max - count,
                        s = acf._e('flexible_content', 'available'),
                        identifier = ( available == 1 ) ? 'layout' : 'layouts',


                    // translate
                        s = s.replace('{available}', available);
                    s = s.replace('{max}', max);
                    s = s.replace('{label}', '"' + label + '"');
                    s = s.replace('{identifier}', acf._e('flexible_content', identifier));


                    // show status
                    $status.show().text(available).attr('title', s);


                    // limit reached?
                    if (available == 0) {

                        $status.addClass('warning');

                    }

                }


                if (min > 0) {

                    // find diff
                    var required = min - count,
                        s = acf._e('flexible_content', 'required'),
                        identifier = ( required == 1 ) ? 'layout' : 'layouts',


                    // translate
                        s = s.replace('{required}', required);
                    s = s.replace('{min}', min);
                    s = s.replace('{label}', '"' + label + '"');
                    s = s.replace('{identifier}', acf._e('flexible_content', identifier));


                    // limit reached?
                    if (required > 0) {

                        $status.addClass('warning').show().text(required).attr('title', s);

                    }

                }

            });


            // add popup
            e.$el.after($popup);


            // within layout?
            if (e.$el.attr('data-before')) {

                $popup.addClass('within-layout');
                $popup.closest('.layout').addClass('popup-open');

            }


            // vars
            $popup.css({
                'margin-top': 0 - $popup.height() - e.$el.outerHeight() - 14,
                'margin-left': ( e.$el.outerWidth() - $popup.width() ) / 2,
            });


            // check distance to top
            var offset = $popup.offset().top;

            if (offset < 30) {

                $popup.css({
                    'margin-top': 15
                });

                $popup.find('.bit').addClass('top');
            }


            // focus
            $popup.children('.focus').trigger('focus');

        },

        close_popup: function (e) {

            var $popup = e.$el.parent();


            // hide controlls?
            if ($popup.closest('.layout').exists()) {

                $popup.closest('.layout').removeClass('popup-open');

            }


            setTimeout(function () {

                $popup.remove();

            }, 200);

        },

        add: function (e) {

            // vars
            var $popup = e.$el.closest('.acf-fc-popup'),
                layout = e.$el.attr('data-layout');


            // bail early if validation fails
            if (!this.validate_add(layout)) {

                return;

            }


            // create and add the new layout
            var new_id = acf.get_uniqid(),
                html = this.$clones.children('.layout[data-layout="' + layout + '"]').outerHTML();


            // replace acfcloneindex
            var html = html.replace(/(="[\w-\[\]]+?)(acfcloneindex)/g, '$1' + new_id),
                $html = $(html);


            // enable inputs
            $html.find('[name]').removeAttr('disabled');


            // hide no values message
            this.$el.children('.no-value-message').hide();


            // add row
            this.$values.append($html);


            // move row
            if ($popup.hasClass('within-layout')) {

                $popup.closest('.layout').before($html);

            }


            // setup fields
            acf.do_action('append', $html);


            // update order
            this.render();


            // validation
            acf.validation.remove_error(this.$field);

        },

        remove: function (e) {

            // vars
            var $layout = e.$el.closest('.layout');


            // bail early if validation fails
            if (!this.validate_remove($layout.attr('data-layout'))) {

                return;

            }


            // close field
            var end_height = 0,
                $message = this.$el.children('.no-value-message');

            if ($layout.siblings('.layout').length == 0) {

                end_height = $message.outerHeight();

            }


            // remove
            acf.remove_el($layout, function () {

                if (end_height > 0) {

                    $message.show();

                }

            }, end_height);

        },

        toggle: function (e) {

            // vars
            var $layout = e.$el.closest('.layout');


            if ($layout.attr('data-toggle') == 'closed') {

                $layout.attr('data-toggle', 'open');
                $layout.children('.acf-input-table').show();

            } else {

                $layout.attr('data-toggle', 'closed');
                $layout.children('.acf-input-table').hide();

            }


            // sync local storage (collapsed)
            this.sync();

        },

        sync: function () {

            // vars
            var name = 'acf_collapsed_' + acf.get_data(this.$field, 'key'),
                collapsed = [];

            this.$values.children('.layout').each(function (i) {

                if ($(this).attr('data-toggle') == 'closed') {

                    collapsed.push(i);

                }

            });

            acf.update_cookie(name, collapsed.join('|'));

        }
    });


    /*
     *  Gallery
     *
     *  static model for this field
     *
     *  @type	event
     *  @date	18/08/13
     *
     */

    acf.fields.gallery = acf.field.extend({

        type: 'gallery',
        $el: null,

        actions: {
            'ready': 'initialize',
            'append': 'initialize',
            'submit': 'close_sidebar'
        },

        events: {
            'click .acf-gallery-attachment': 'select_attachment',
            'click .remove-attachment': 'remove_attachment',
            'click .edit-attachment': 'edit_attachment',
            'click .update-attachment': 'update_attachment',
            'click .add-attachment': 'add_attachment',
            'click .close-sidebar': 'close_sidebar',
            'change .acf-gallery-side input': 'update_attachment',
            'change .acf-gallery-side textarea': 'update_attachment',
            'change .acf-gallery-side select': 'update_attachment',
            'change .bulk-actions': 'sort'
        },

        focus: function () {

            this.$el = this.$field.find('.acf-gallery').first();
            this.$values = this.$el.children('.values');
            this.$clones = this.$el.children('.clones');


            // get options
            this.settings = acf.get_data(this.$el);


            // min / max
            this.settings.min = this.settings.min || 0;
            this.settings.max = this.settings.max || 0;

        },

        get_attachment: function (id) {

            // defaults
            id = id || '';


            // vars
            var selector = '.acf-gallery-attachment';


            // update selector
            if (id === 'active') {

                selector += '.active';

            } else if (id) {

                selector += '[data-id="' + id + '"]';

            }


            // return
            return this.$el.find(selector);

        },

        count: function () {

            return this.get_attachment().length;

        },

        initialize: function () {

            // reference
            var self = this,
                $field = this.$field;


            // sortable
            this.$el.find('.acf-gallery-attachments').unbind('sortable').sortable({

                items: '.acf-gallery-attachment',
                forceHelperSize: true,
                forcePlaceholderSize: true,
                scroll: true,

                start: function (event, ui) {

                    ui.placeholder.html(ui.item.html());
                    ui.placeholder.removeAttr('style');

                    acf.do_action('sortstart', ui.item, ui.placeholder);

                },

                stop: function (event, ui) {

                    acf.do_action('sortstop', ui.item, ui.placeholder);

                }
            });


            // resizable
            this.$el.unbind('resizable').resizable({
                handles: 's',
                minHeight: 200,
                stop: function (event, ui) {

                    acf.update_user_setting('gallery_height', ui.size.height);

                }
            });


            // resize
            $(window).on('resize', function () {

                self.doFocus($field).resize();

            });


            // render
            this.render();


            // resize
            this.resize();

        },

        render: function () {

            // vars
            var $select = this.$el.find('.bulk-actions'),
                $a = this.$el.find('.add-attachment');


            // disable select
            if (this.settings.max > 0 && this.count() >= this.settings.max) {

                $a.addClass('disabled');

            } else {

                $a.removeClass('disabled');

            }

        },

        sort: function (e) {

            // vars
            var sort = e.$el.val();


            // validate
            if (!sort) {

                return;

            }


            // vars
            var data = acf.prepare_for_ajax({
                action: 'acf/fields/gallery/get_sort_order',
                field_key: this.settings.key,
                post_id: acf.get('post_id'),
                ids: [],
                sort: sort
            });


            // find and add attachment ids
            this.get_attachment().each(function () {

                data.ids.push($(this).attr('data-id'));

            });


            // get results
            var xhr = $.ajax({
                url: acf.get('ajaxurl'),
                dataType: 'json',
                type: 'post',
                cache: false,
                data: data,
                context: this,
                success: this.sort_success
            });

        },

        sort_success: function (json) {

            // validate
            if (!acf.is_ajax_success(json)) {

                return;

            }


            // reverse order
            json.data.reverse();


            // loop over json
            for (i in json.data) {

                var id = json.data[i],
                    $attachment = this.get_attachment(id);


                // prepend attachment
                this.$el.find('.acf-gallery-attachments').prepend($attachment);

            }
            ;

        },

        clear_selection: function () {

            this.get_attachment().removeClass('active');

        },

        select_attachment: function (e) {

            // vars
            var $attachment = e.$el;


            // bail early if already active
            if ($attachment.hasClass('active')) {

                return;

            }


            // vars
            var id = $attachment.attr('data-id');


            // clear selection
            this.clear_selection();


            // add selection
            $attachment.addClass('active');


            // fetch
            this.fetch(id);


            // open sidebar
            this.open_sidebar();

        },

        open_sidebar: function () {

            // add class
            this.$el.addClass('sidebar-open');


            // hide bulk actions
            this.$el.find('.bulk-actions').hide();


            // animate
            this.$el.find('.acf-gallery-main').animate({right: 350}, 250);
            this.$el.find('.acf-gallery-side').animate({width: 349}, 250);

        },

        close_sidebar: function () {

            // remove class
            this.$el.removeClass('sidebar-open');


            // vars
            var $select = this.$el.find('.bulk-actions');


            // deselect attachmnet
            this.clear_selection();


            // disable sidebar
            this.$el.find('.acf-gallery-side').find('input, textarea, select').attr('disabled', 'disabled');


            // animate
            this.$el.find('.acf-gallery-main').animate({right: 0}, 250);
            this.$el.find('.acf-gallery-side').animate({width: 0}, 250, function () {

                $select.show();

                $(this).find('.acf-gallery-side-data').html('');

            });

        },

        fetch: function (id) {

            // vars
            var data = acf.prepare_for_ajax({
                action: 'acf/fields/gallery/get_attachment',
                field_key: this.settings.key,
                nonce: acf.get('nonce'),
                post_id: acf.get('post_id'),
                id: id
            });


            // abort XHR if this field is already loading AJAX data
            if (this.$el.data('xhr')) {

                this.$el.data('xhr').abort();

            }


            // get results
            var xhr = $.ajax({
                url: acf.get('ajaxurl'),
                dataType: 'html',
                type: 'post',
                cache: false,
                data: data,
                context: this,
                success: this.render_fetch
            });


            // update el data
            this.$el.data('xhr', xhr);

        },

        render_fetch: function (html) {

            // bail early if no html
            if (!html) {

                return;

            }


            // vars
            var $side = this.$el.find('.acf-gallery-side-data');


            // render
            $side.html(html);


            // remove acf form data
            $side.find('.compat-field-acf-form-data').remove();


            // detach meta tr
            var $tr = $side.find('> .compat-attachment-fields > tbody > tr').detach();


            // add tr
            $side.find('> table.form-table > tbody').append($tr);


            // remove origional meta table
            $side.find('> .compat-attachment-fields').remove();


            // setup fields
            acf.do_action('append', $side);

        },

        update_attachment: function () {

            // vars
            var $a = this.$el.find('.update-attachment')
            $form = this.$el.find('.acf-gallery-side-data'),
                data = acf.serialize_form($form);


            // validate
            if ($a.attr('disabled')) {

                return false;

            }


            // add attr
            $a.attr('disabled', 'disabled');
            $a.before('<i class="acf-loading"></i>');


            // append AJAX action
            data.action = 'acf/fields/gallery/update_attachment';


            // prepare for ajax
            acf.prepare_for_ajax(data);


            // ajax
            $.ajax({
                url: acf.get('ajaxurl'),
                data: data,
                type: 'post',
                dataType: 'json',
                complete: function (json) {

                    $a.removeAttr('disabled');
                    $a.prev('.acf-loading').remove();

                }
            });

        },

        add: function (image) {

            // validate
            if (this.settings.max > 0 && this.count() >= this.settings.max) {

                acf.validation.add_warning(this.$field, acf._e('gallery', 'max'));

                return;

            }


            // append to image data
            image.name = this.$el.find('[data-name="ids"]').attr('name');


            // template
            var tmpl = acf._e('gallery', 'tmpl'),
                html = _.template(tmpl, image);


            // append
            this.$el.find('.acf-gallery-attachments').append(html);


            // render
            this.render();

        },

        edit_attachment: function (e) {

            // reference
            var self = this;


            // vars
            var id = acf.get_data(e.$el, 'id');


            // popup
            var frame = acf.media.popup({
                'title': acf._e('image', 'edit'),
                'button': acf._e('image', 'update'),
                'mode': 'edit',
                'id': id,
                'select': function (attachment) {

                    // override url
                    if (acf.isset(attachment, 'attributes', 'sizes', self.settings.preview_size, 'url')) {

                        attachment.url = attachment.attributes.sizes[self.settings.preview_size].url;

                    }


                    // update image
                    self.get_attachment(id).find('img').attr('src', attachment.url);


                    // render sidebar
                    self.fetch(id);

                }
            });

        },

        remove_attachment: function (e) {

            // prevent event from triggering click on attachment
            e.stopPropagation();


            // vars
            var id = acf.get_data(e.$el, 'id');


            // deselect attachmnet
            this.clear_selection();


            // update sidebar
            this.close_sidebar();


            // remove image
            this.get_attachment(id).remove();


            // render
            this.render();


        },

        render_collection: function (frame) {

            var self = this;


            // Note: Need to find a differen 'on' event. Now that attachments load custom fields, this function can't rely on a timeout. Instead, hook into a render function foreach item

            // set timeout for 0, then it will always run last after the add event
            setTimeout(function () {


                // vars
                var $content = frame.content.get().$el
                collection = frame.content.get().collection || null;


                if (collection) {

                    var i = -1;

                    collection.each(function (item) {

                        i++;

                        var $li = $content.find('.attachments > .attachment:eq(' + i + ')');


                        // if image is already inside the gallery, disable it!
                        if (self.get_attachment(item.id).exists()) {

                            item.off('selection:single');
                            $li.addClass('acf-selected');

                        }

                    });

                }


            }, 10);


        },

        add_attachment: function (e) {

            // validate
            if (this.settings.max > 0 && this.count() >= this.settings.max) {

                acf.validation.add_warning(this.$field, acf._e('gallery', 'max'));

                return;

            }


            // vars
            var library = this.settings.library,
                preview_size = this.settings.preview_size;


            // reference
            var self = this;


            // popup
            var frame = acf.media.popup({
                'title': acf._e('gallery', 'select'),
                'mode': 'select',
                'type': 'all',
                'multiple': 'add',
                'library': library,
                'select': function (attachment, i) {

                    // is image already in gallery?
                    if (self.get_attachment(attachment.id).exists()) {

                        return;

                    }


                    // vars
                    var image = {
                        'id': attachment.id,
                        'url': attachment.attributes.url
                    };


                    // file?
                    if (attachment.attributes.type != 'image') {

                        image.url = attachment.attributes.icon;

                    }


                    // is preview size available?
                    if (acf.isset(attachment, 'attributes', 'sizes', preview_size)) {

                        image.url = attachment.attributes.sizes[preview_size].url;

                    }


                    // add file to field
                    self.add(image);

                }
            });


            // modify DOM
            frame.on('content:activate:browse', function () {

                self.render_collection(frame);

                frame.content.get().collection.on('reset add', function () {

                    self.render_collection(frame);

                });

            });

        },

        resize: function () {

            // vars
            var min = 100,
                max = 175,
                columns = 4,
                width = this.$el.width();


            // get width
            for (var i = 0; i < 10; i++) {

                var w = width / i;

                if (min < w && w < max) {

                    columns = i;
                    break;

                }

            }


            // update data
            this.$el.attr('data-columns', columns);
        }

    });


})(jQuery);
