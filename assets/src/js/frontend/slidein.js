let $ = jQuery;
let defaults = {
    toggler: '#slidein-toggle'
};

function Slidein(element, options) {
    this.$el          = $(element);
    this.opt          = $.extend(true, {}, defaults, options);
    this.$toggler     = $(this.opt.toggler);
    this._trapHandler = null;
    this._isOpen      = false;

    this.init(this);
}

Slidein.prototype = {

    init: function (self) {
        self.$el.attr('inert', '').attr('aria-hidden', 'true');
        self.bindToggle(self);
        self.bindClose(self);
        self.bindOutsideClick(self);
        self.initDropdown(self);
    },

    open: function (self) {
        self._isOpen = true;
        self.$el.addClass('show').removeAttr('inert').attr('aria-hidden', 'false');
        $('body').addClass('slidein-no-scroll');
        self.toggleOverlay();
        self.trapFocus(self);
        self.$toggler.attr('aria-expanded', 'true');
    },

    close: function (self) {
        self._isOpen = false;
        self.$el.removeClass('show').attr('inert', '').attr('aria-hidden', 'true');
        $('body').removeClass('slidein-no-scroll');
        self.hideOverlay();
        self.releaseFocus(self);
        self.$toggler.attr('aria-expanded', 'false');
    },

    bindToggle: function (self) {
        self.$toggler.on('click', function () {
            if (!self._isOpen) self.open(self);
        });
    },

    bindClose: function (self) {
        self.$el.on('click', '[data-slidein-close]', function () {
            self.close(self);
        });
    },

    bindOutsideClick: function (self) {
        $(document).on('click', function (e) {
            if (!self._isOpen) return;
            if (self.$el[0].contains(e.target))       return;
            if (self.$toggler[0].contains(e.target))   return;
            self.close(self);
        });
    },

    trapFocus: function (self) {
        var sel = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

        self.$el.find(sel).filter(':visible').first().trigger('focus');

        self._trapHandler = function (e) {

            if (e.key === 'Escape') {
                var $openSub = self.$el.find('.sub-menu.is-open');
                if ($openSub.length) {
                    var $li = $openSub.closest('.menu-item-has-children');
                    $openSub.removeClass('is-open').find('a, button').attr('tabindex', '-1');
                    $li.children('.sublevel__icon').attr('aria-expanded', 'false');
                    $li.siblings('li').removeClass('is-sibling-hidden');
                    $li.children('.sublevel__icon').trigger('focus');
                    return;
                }
                self.close(self);
                return;
            }

            if (e.key !== 'Tab') return;

            var $openSub   = self.$el.find('.sub-menu.is-open');
            var $context   = $openSub.length ? $openSub.last() : self.$el;
            var $focusable = $context.find(sel).filter(':visible');
            var $first     = $focusable.first();
            var $last      = $focusable.last();

            if (e.shiftKey) {
                if (document.activeElement === $first[0]) {
                    e.preventDefault();
                    $last.trigger('focus');
                }
            } else {
                if (document.activeElement === $last[0]) {
                    e.preventDefault();
                    $first.trigger('focus');
                }
            }
        };

        document.addEventListener('keydown', self._trapHandler);
    },

    releaseFocus: function (self) {
        if (self._trapHandler) {
            document.removeEventListener('keydown', self._trapHandler);
            self._trapHandler = null;
        }
        self.$toggler.trigger('focus');
    },

    initDropdown: function (self) {
        self.$el.on('click', '[data-slidein-dropdown-toggle]', function (e) {
            e.preventDefault();
            var $this = $(this);
            $this.next('[data-slidein-dropdown]').slideinToggle('fast');
            $this.find('[data-slidein-dropdown-icon]').toggleClass('show');
        });
    },

    toggleOverlay: function () {
        var $overlay = $('[data-slidein-overlay]');
        if (!$overlay[0]) {
            $overlay = $('<div data-slidein-overlay class="slidein-overlay"/>');
            $('body').append($overlay);
        }
        $overlay.is(':visible') ? $overlay.fadeOut('fast') : $overlay.fadeIn('fast');
    },

    hideOverlay: function () {
        $('[data-slidein-overlay]').fadeOut('fast');
    }

};

export { Slidein };
