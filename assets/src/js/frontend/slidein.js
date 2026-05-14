let $ = jQuery;
let defaults = {
    toggler: '#slidein-toggle'
};

function Slidein(element, options) {
    this.$el = $(element);
    this.opt = $.extend(true, {}, defaults, options);
    this.$toggler = $(this.opt.toggler);
    this._trapHandler = null;

    this.init(this);
}

Slidein.prototype = {
    init: function(self) {
        self.initToggle(self);
        self.initDropdown(self);
    },

    initToggle: function(self) {
        $(self.opt.toggler).on('click', function(e) {
            if (!self.$el.hasClass('show')) {
                self.$el.addClass('show');
                $('body').addClass('slidein-no-scroll');
                self.toggleOverlay();
                self.trapFocus(self);
                $(self.opt.toggler).attr('aria-expanded', 'true');

                $(document).on('click', function(e) {
                    var $target = $(e.target);
                    if (self.$el.is($target) || self.$el.find($target).length > 0) {
                        return;
                    }
                    if (!$target.closest(self.$toggler).is(self.$toggler)) {
                        self.$el.removeClass('show');
                        $('body').removeClass('slidein-no-scroll');
                        self.hideOverlay();
                        self.releaseFocus(self);
                        $(self.opt.toggler).attr('aria-expanded', 'false');
                        $(document).off('click');
                    }
                });

                $('[data-slidein-close]').on('click keydown', function(e) {
                    if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
                    self.$el.removeClass('show');
                    $('body').removeClass('slidein-no-scroll');
                    self.hideOverlay();
                    self.releaseFocus(self);
                    $(self.opt.toggler).attr('aria-expanded', 'false');
                    $(document).off('click');
                });
            }
        });
    },

    initDropdown: function(self) {
        self.$el.on('click', '[data-slidein-dropdown-toggle]', function(e) {
            var $this = $(this);

            $this
                .next('[data-slidein-dropdown]')
                .slideinToggle('fast');

            $this
                .find('[data-slidein-dropdown-icon]')
                .toggleClass('show');

            e.preventDefault();
        });
    },

    trapFocus: function(self) {
        var focusableSelectors = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';
        var $focusable = self.$el.find(focusableSelectors).filter(':visible');
        var $first = $focusable.first();
        var $last = $focusable.last();

        $first.trigger('focus');

        self._trapHandler = function(e) {
            if (e.key === 'Escape') {
                var $openSubmenu = self.$el.find('.sub-menu.is-open');
                if ($openSubmenu.length) {
                    var $parentLi = $openSubmenu.closest('.menu-item-has-children');
                    var $nextLi = $parentLi.next('li');
                    $openSubmenu.css('left', '100%').removeClass('is-open');
                    $parentLi.children('a').attr('aria-expanded', 'false');
                    $parentLi.siblings('li').removeClass('is-sibling-hidden');
                    if ($nextLi.length) {
                        var focusTarget = $nextLi.find('a, [tabindex="0"]').first()[0];
                        if (focusTarget) {
                            focusTarget.focus();
                        }
                    }
                    return;
                }
            }

            if (e.key !== 'Tab') return;

            $focusable = self.$el.find(focusableSelectors).filter(':visible');
            $first = $focusable.first();
            $last = $focusable.last();

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

    releaseFocus: function(self) {
        if (self._trapHandler) {
            document.removeEventListener('keydown', self._trapHandler);
            self._trapHandler = null;
        }
        $(self.opt.toggler).trigger('focus');
    },

    toggleOverlay: function() {
        var $overlay = $('[data-slidein-overlay]');

        if (!$overlay[0]) {
            $overlay = $('<div data-slidein-overlay class="slidein-overlay"/>');
            $('body').append($overlay);
        }

        if ($overlay.is(':visible')) {
            $overlay.fadeOut('fast');
        } else {
            $overlay.fadeIn('fast');
        }
    },

    hideOverlay: function() {
        $('[data-slidein-overlay]').fadeOut('fast');
    }
};

export { Slidein };