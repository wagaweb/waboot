let $ = jQuery;
let defaults = {
  toggler: '#slidein-toggle',
};

function Slidein(element, options) {
  this.$el = $(element);
  this.opt = $.extend(true, {}, defaults, options);
  this.$toggler = $(this.opt.toggler);
  this.$focusableElements = null;
  this.$firstFocusableElement = null;
  this.$lastFocusableElement = null;
  this.init(this);
}

Slidein.prototype = {
  init: function (self) {
    self.initToggle(self);
    self.initDropdown(self);
    self.initKeyboardNavigation(self);
  },

  initToggle: function (self) {
    // Assicurati che il pannello abbia un ID
    if (!self.$el.attr('id')) {
      self.$el.attr('id', 'slidein-panel');
    }

    $(self.opt.toggler).on('click', function (e) {
      if (!self.$el.hasClass('show')) {
        self.openSlidein(self);
      }
    });
  },

  openSlidein: function (self) {
    // Accessibility
    self.$el.removeAttr('inert');
    self.$el.attr('aria-hidden', 'false');
    self.$toggler.attr('aria-expanded', 'true');

    // Styles
    self.$el.addClass('show');
    $('body').addClass('slidein-no-scroll');
    self.toggleOverlay();

    $(document).on('click', function (e) {
      let $target = $(e.target);
      if (self.$el.is($target) || self.$el.find($target).length > 0)
        return;
      if (!$target.closest(self.$toggler).is(self.$toggler)) {
        self.closeSlidein(self);
      }
    });

    $('[data-slidein-close]').on('click', function () {
      self.closeSlidein(self);
    });
  },

  closeSlidein: function (self) {
    // Accessibility
    self.$el.removeClass('show').attr('inert', '');
    self.$el.attr('aria-hidden', 'true');
    self.$toggler.attr('aria-expanded', 'false');

    // Style
    $('body').removeClass('slidein-no-scroll');
    self.hideOverlay();
    $(document).off('click');
    self.$toggler.focus();
  },

  initKeyboardNavigation: function (self) {
    self.$el.on('keyup', function (e) {
      // Gestione del tasto ESC
      if (e.key === 'Escape') {
        self.closeSlidein(self);
        return;
      }
    });
  },

  initDropdown: function (self) {
    self.$el.on(
      'click',
      '[data-slidein-dropdown-toggle]',
      function (e) {
        let $this = $(this);

        $this.next('[data-slidein-dropdown]').slideinToggle('fast');

        $this
          .find('[data-slidein-dropdown-icon]')
          .toggleClass('show');

        e.preventDefault();
      }
    );
  },

  toggleOverlay: function () {
    let $overlay = $('[data-slidein-overlay]');

    if (!$overlay[0]) {
      $overlay = $(
        '<div data-slidein-overlay class="slidein-overlay"/>'
      );
      $('body').append($overlay);
    }

    if ($overlay.is(':visible')) {
      $overlay.fadeOut('fast');
    } else {
      $overlay.fadeIn('fast');
    }
  },

  hideOverlay: function () {
    $('[data-slidein-overlay]').fadeOut('fast');
  },
};

export { Slidein };
