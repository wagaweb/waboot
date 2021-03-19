import $ from 'jquery';

export default class {

    constructor() {

        let $minicart = $('[data-minicart]');

        this.$el = $minicart;
        this.$toggler = $('.minicart-toggle');

        this.initMiniCart();
        this.getCartItems();
        this.handleProductDelete();

        let addedProduct = this.getParameterByName("addedProduct");
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
        $(document).on('open-minicart', ()=>{
            this.toggleMiniCart();
        })
    }

    initToggle() {
        $(this.$toggler).on('click', () => {
            this.toggleMiniCart();
        });
        $('[data-minicar-close]').on('click', () => {
            this.toggleMiniCart();
        });
    }

    toggleMiniCart(){
        if(!this.$el.hasClass('show')){
            this.$el.addClass('show');
            $('body').addClass('minicart-no-scroll');
            this.toggleOverlay();

            $('.minicart-overlay').on('click', { self: this } ,this.hideHandler);
        }else{
            this.$el.removeClass('show');
            $('body').removeClass('minicart-no-scroll');
            this.hideOverlay();
            $('.minicart-overlay').off('click', '**', this.hideHandler);
        }
    }

    hideHandler(e){
        e.stopPropagation();
        let $target = $(e.target);
        let self = e.data.self;
        if(self.$el.is($target) || self.$el.find($target).length > 0){
            return;
        }
        if(!$target.closest(self.$toggler).is(self.$toggler)){
            self.$el.removeClass('show');
            $('body').removeClass('minicart-no-scroll');

            self.hideOverlay();

            $('.minicart-overlay').off('click', '**', self.hideHandler);
            $('#minicart-toggle').off('click', self.hideHandler);
        }
    }

    toggleOverlay() {
        let $overlay = $('[data-minicart-overlay]');

        if (!$overlay[0]) {
            $overlay = $('<div data-minicart-overlay class="minicart-overlay"/>');
            $('body').append($overlay);
        }


        if($overlay.is(':visible')){
            $overlay.fadeOut('fast');
        }else{
            $overlay.fadeIn('fast');
        }

    }

    hideOverlay() {
        $('[data-minicart-overlay]').fadeOut('fast');
    }

    getParameterByName(name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
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
                let $cartCounter = document.querySelectorAll('[data-cart-items]');
                $cartCounter.forEach(function (el) {
                    el.classList.add('show');
                    el.innerHTML = count;
                })
            }
        }
    }

    handleProductDelete() {
        jQuery(document).on('click', '.mini_cart_item .remove_from_cart_button', function () {
            let $counterWrap = $('[data-cart-items]');
            let $deletedProductCount = parseInt(jQuery(this).parents('li.mini_cart_item').find('[data-cart-item-quantity]').attr('data-cart-item-quantity'));
            if ($deletedProductCount > 0) {
                let $counter = parseInt($counterWrap.html()) - $deletedProductCount;
                if ($counter > 0) {
                    $counterWrap.html($counter);
                } else {
                    $counterWrap.html(0);
                    $counterWrap.hide();
                }
            }
        })
    }

}
