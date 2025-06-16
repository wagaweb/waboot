import $ from 'jquery';

export default class {
  constructor() {
    let $minicart = $('[data-minicart]');

    this.$el = $minicart;
    this.$toggler = $('.minicart-toggle');
    this.$focusableElements = null;
    this.$firstFocusableElement = null;
    this.$lastFocusableElement = null;

    // Imposta gli attributi ARIA di base
    this.$el.attr({
      role: 'dialog',
      'aria-modal': 'true',
      'aria-hidden': 'true',
      'aria-label': 'Carrello',
    });

    // Assicurati che il minicart abbia un ID
    if (!this.$el.attr('id')) {
      this.$el.attr('id', 'minicart-panel');
    }

    // Imposta gli attributi ARIA del toggler
    this.$toggler.attr({
      'aria-expanded': 'false',
      'aria-controls': this.$el.attr('id'),
      'aria-haspopup': 'true',
    });

    this.initMiniCart();
    this.getCartItems();
    this.handleProductDelete();
    this.initKeyboardNavigation();

    let addedProduct = this.getParameterByName('addedProduct');
    if (addedProduct) {
      $(document).trigger('open-minicart');
    }

    $(document).on('click', '.ajax_add_to_cart', function (s) {
      s.stopPropagation();
      $(document).trigger('open-minicart');
    });
  }

  initMiniCart() {
    this.initToggle();
    $(document).on('open-minicart', () => {
      this.toggleMiniCart();
    });
  }

  initToggle() {
    $(this.$toggler).on('click', () => {
      this.toggleMiniCart();
    });
    $('[data-minicar-close]').on('click', () => {
      this.toggleMiniCart();
    });
  }

  initKeyboardNavigation() {
    this.$el.on('keydown', (e) => {
      // Gestione del tasto ESC
      if (e.key === 'Escape') {
        this.toggleMiniCart();
        return;
      }

      // Gestione del focus trap
      if (e.key === 'Tab') {
        // Aggiorna gli elementi focusabili
        this.updateFocusableElements();

        if (e.shiftKey) {
          // Shift + Tab
          if (
            document.activeElement === this.$firstFocusableElement[0]
          ) {
            e.preventDefault();
            this.$lastFocusableElement.focus();
          }
        } else {
          // Tab
          if (
            document.activeElement === this.$lastFocusableElement[0]
          ) {
            e.preventDefault();
            this.$firstFocusableElement.focus();
          }
        }
      }
    });
  }

  updateFocusableElements() {
    this.$focusableElements = this.$el.find(
      'a, button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    this.$firstFocusableElement = this.$focusableElements.first();
    this.$lastFocusableElement = this.$focusableElements.last();
  }

  toggleMiniCart() {
    const isOpen = this.$el.hasClass('show');

    if (!isOpen) {
      this.$el.addClass('show').css('display', 'block');
      $('body').addClass('minicart-no-scroll');
      this.$el.attr('aria-hidden', 'false').removeAttr('inert');
      this.$toggler.attr('aria-expanded', 'true');
      this.toggleOverlay();

      this.updateFocusableElements();

      // Rendi focusabili tutti gli elementi
      this.$focusableElements.each((_, el) => {
        $(el).removeAttr('tabindex');
      });

      this.$firstFocusableElement.focus();
      $('.minicart-overlay').on('click', { self: this }, this.hideHandler);
    } else {
      this.$el.removeClass('show').css('display', 'none');
      $('body').removeClass('minicart-no-scroll');
      this.$el.attr('aria-hidden', 'true').attr('inert', '');
      this.$toggler.attr('aria-expanded', 'false');
      this.hideOverlay();

      this.updateFocusableElements();

      // Disabilita il focus
      this.$focusableElements.each((_, el) => {
        $(el).attr('tabindex', '-1');
      });

      if (document.activeElement && $.contains(this.$el[0], document.activeElement)) {
        this.$toggler.focus();
      }

      $('.minicart-overlay').off('click', '**', this.hideHandler);
    }
  }




  hideHandler(e) {
    e.stopPropagation();
    let $target = $(e.target);
    let self = e.data.self;
    if (self.$el.is($target) || self.$el.find($target).length > 0) {
      return;
    }
    if (!$target.closest(self.$toggler).is(self.$toggler)) {
      self.$el.removeClass('show');
      $('body').removeClass('minicart-no-scroll');

      self.$el.attr('aria-hidden', 'true').attr('inert', '');
      self.hideOverlay();

      $('.minicart-overlay').off('click', '**', self.hideHandler);
      $('#minicart-toggle').off('click', self.hideHandler);
    }
  }

  toggleOverlay() {
    let $overlay = $('[data-minicart-overlay]');

    if (!$overlay[0]) {
      $overlay = $(
        '<div data-minicart-overlay class="minicart-overlay"/>'
      );
      $('body').append($overlay);
    }

    if ($overlay.is(':visible')) {
      $overlay.fadeOut('fast');
    } else {
      $overlay.fadeIn('fast');
    }
  }

  hideOverlay() {
    $('[data-minicart-overlay]').fadeOut('fast');
  }

  getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
      results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
  }

  getCartItems() {
    let $ = jQuery;
    let count = 0;
    let $miniCartContainer = $('.woocommerce-mini-cart');
    if ($miniCartContainer.length) {
      let numbs = $('[data-cart-item-quantity]', $miniCartContainer);
      numbs.each(function () {
        let numb = parseInt($(this).attr('data-cart-item-quantity'));
        count += numb;
      });

      if (count > 0) {
        let $cartCounter = document.querySelectorAll(
          '[data-cart-items]'
        );
        $cartCounter.forEach(function (el) {
          el.classList.add('show');
          el.innerHTML = count;
        });
      }
    }
  }

  handleProductDelete() {
    jQuery(document).on(
      'click',
      '.mini_cart_item .remove_from_cart_button',
      function () {
        let $counterWrap = $('[data-cart-items]');
        let $deletedProductCount = parseInt(
          jQuery(this)
            .parents('li.mini_cart_item')
            .find('[data-cart-item-quantity]')
            .attr('data-cart-item-quantity')
        );
        if ($deletedProductCount > 0) {
          let $counter =
            parseInt($counterWrap.html()) - $deletedProductCount;
          if ($counter > 0) {
            $counterWrap.html($counter);
          } else {
            $counterWrap.html(0);
            $counterWrap.hide();
          }
        }
      }
    );
  }
}
