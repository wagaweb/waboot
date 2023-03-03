import $ from 'jquery';

export function enableProductGallery(){
    //productImageHeight();

    if (window.matchMedia('(max-width: 767px)').matches) {
        productCarousel(
            '.product-images__carousel',
            {
                dotsContainer:'.product-images__dots',
                hasColorbox:false
            }
        );
    }

    $(window).on("resize",() => {
        //productImageHeight();
    });
}

function productImageHeight() {
    let windowHeight = $(window).height(),
        headerHeight = $('.header').outerHeight(),
        productCarousel = $('.product-images'),
        mainImageHeight = $('.product-images__carousel').outerHeight();
    if (window.matchMedia('(min-width: 992px)').matches) {
        $('.product-images__image').css('max-height', windowHeight - headerHeight);
        setTimeout(function(){
            productCarousel.css('max-height', $('.product-images__carousel').outerHeight())
        },500 );

    }
}

function productCarousel(selector, args) {
    if ($(selector).length <= 0) {
        return;
    }

    let $dotsContainer = $(args.dotsContainer),
        $dotItems = $dotsContainer.children('div');

    const owl = $(selector).owlCarousel({
        loop: false,
        autoplay: false,
        items: 1,
        autoHeight: true,
        nav: true,
        //animateOut: 'slideOutUp',
        //animateIn: 'slideInUp',
        navText: ['<svg width="20px" version="1.1" viewBox="0 0 50 94.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg"><path d="m49.1 93.1c1.2-1.3 1.3-3.3 0-4.5-1.4-1.5-2.9-2.9-4.4-4.4-3.5-3.5-6.9-7-10.4-10.4-4.2-4.2-8.4-8.5-12.6-12.7l-10.9-10.9c-1-1-2.1-2.1-3.1-3.2l2.1-2.1c3.5-3.5 6.9-7 10.4-10.4 4.2-4.2 8.4-8.5 12.6-12.7l10.9-10.9c1.8-1.8 3.6-3.5 5.3-5.3l0.1-0.1c1.2-1.2 1.3-3.4 0-4.5-1.3-1.2-3.2-1.3-4.5 0-1.4 1.5-2.9 2.9-4.4 4.4-3.5 3.5-6.9 7-10.4 10.4-4.2 4.2-8.4 8.5-12.6 12.7l-10.9 10.9c-1.8 1.8-3.6 3.5-5.3 5.3l-0.1 0.1c-1.2 1.2-1.2 3.3 0 4.5 1.4 1.5 2.9 2.9 4.4 4.4 3.5 3.5 6.9 7 10.4 10.4 4.2 4.2 8.4 8.5 12.6 12.7l10.9 10.9c1.8 1.8 3.5 3.6 5.3 5.3l0.1 0.1c1.2 1.2 3.3 1.3 4.5 0z"/></svg>', '<svg width="20px" version="1.1" viewBox="0 0 50 94.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg"><path d="m0.9 0.9c-1.2 1.3-1.3 3.3 0 4.5 1.4 1.5 2.9 2.9 4.4 4.4 3.5 3.5 6.9 7 10.4 10.4 4.2 4.2 8.4 8.5 12.6 12.7l10.9 10.9c1 1 2.1 2.1 3.1 3.2l-2.1 2.1c-3.5 3.5-6.9 7-10.4 10.4-4.2 4.2-8.4 8.5-12.6 12.7l-10.9 10.9c-1.8 1.9-3.6 3.6-5.3 5.5l-0.1 0.1c-1.2 1.2-1.3 3.4 0 4.5 1.3 1.2 3.2 1.3 4.5 0 1.4-1.5 2.9-2.9 4.4-4.4 3.5-3.5 6.9-7 10.4-10.4 4.2-4.2 8.4-8.5 12.6-12.7l10.9-10.9c1.8-1.8 3.6-3.5 5.3-5.3l0.1-0.1c1.2-1.2 1.2-3.3 0-4.5-1.4-1.5-2.9-2.9-4.4-4.4-3.5-3.5-6.9-7-10.4-10.4-4.2-4.2-8.4-8.5-12.6-12.7l-10.9-10.9c-1.8-1.9-3.5-3.8-5.3-5.5l-0.1-0.1c-1.2-1.2-3.3-1.3-4.5 0z"/></svg>'],
        dots: false,
        rewind: true,
        mouseDrag: true,

        responsive: {
            991: {
                nav: false,
                dotsContainer: args.dotsContainer,
            }
        },

        onTranslate: function (e) {
            let $item = $dotsContainer.find(`[data-index='${e.item.index}']`);
            if (!$item.hasClass('active')) {
                $item.parent().children().removeClass('active');
            }
            $item.addClass('active');
        }
    });

    $dotItems.each(function () {
        $(this).click(function(e) {
            e.preventDefault();
            let $this = $(this);
            if (!$this.hasClass('active')) {
                $this.parent().children().removeClass('active');
            }
            $this.addClass('active');
            owl.trigger('to.owl.carousel', [$this.index(), 300]);
        });
    });

    if (args.hasColorbox) {
        $(document).on('click', '#cboxNext', function () {
            owl.trigger('next.owl.carousel', [300]);
        });
        $(document).on('click', '#cboxPrevious', function () {
            owl.trigger('prev.owl.carousel', [300]);
        });
    }
}