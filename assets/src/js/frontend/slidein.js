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

  // Imposta gli attributi ARIA di base
  this.$el.attr({
    role: 'dialog',
    'aria-modal': 'true',
    'aria-hidden': 'true',
    'aria-label': 'Menu di navigazione',
  });

  this.init(this);
}

Slidein.prototype = {
  init: function (self) {
    self.initToggle(self);
    self.initDropdown(self);
    self.initKeyboardNavigation(self);
  },

  initToggle: function (self) {
    // Aggiungi attributi ARIA al toggler
    self.$toggler.attr({
      'aria-expanded': 'false',
      'aria-controls': self.$el.attr('id') || 'slidein-panel',
      'aria-haspopup': 'true',
    });

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
    self.$el.removeClass('inert');
    self.$el.removeAttr('inert');
    self.$el.addClass('show');
    self.$el.attr('aria-hidden', 'false');
    self.$toggler.attr('aria-expanded', 'true');
    $('body').addClass('slidein-no-scroll');
    self.toggleOverlay();

    // Prepara gli elementi focusabili
    self.$focusableElements = self.$el.find(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    self.$firstFocusableElement = self.$focusableElements.first();
    self.$lastFocusableElement = self.$focusableElements.last();

    // Focus sul primo elemento interattivo
    self.$firstFocusableElement.focus();

    // Gestione del click fuori dal pannello
    $(document).on('click', function (e) {
      let $target = $(e.target);
      if (self.$el.is($target) || self.$el.find($target).length > 0)
        return;
      if (!$target.closest(self.$toggler).is(self.$toggler)) {
        self.closeSlidein(self);
      }
    });

    // Gestione dei pulsanti di chiusura
    $('[data-slidein-close]').on('click', function () {
      self.closeSlidein(self);
    });
  },

  closeSlidein: function (self) {
    self.$el.removeClass('show').attr('inert', '');
    self.$el.attr('aria-hidden', 'true');
    self.$toggler.attr('aria-expanded', 'false');
    $('body').removeClass('slidein-no-scroll');
    self.hideOverlay();
    $(document).off('click');
    self.$toggler.focus();
  },

  initKeyboardNavigation: function (self) {
    self.$el.on('keydown', function (e) {
      // Gestione del tasto ESC
      if (e.key === 'Escape') {
        self.closeSlidein(self);
        return;
      }

      // Gestione del focus trap
      if (e.key === 'Tab') {
        if (e.shiftKey) {
          // Shift + Tab
          if (
            document.activeElement === self.$firstFocusableElement[0]
          ) {
            e.preventDefault();
            self.$lastFocusableElement.focus();
          }
        } else {
          // Tab
          if (
            document.activeElement === self.$lastFocusableElement[0]
          ) {
            e.preventDefault();
            self.$firstFocusableElement.focus();
          }
        }
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
