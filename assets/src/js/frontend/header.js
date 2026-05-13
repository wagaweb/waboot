import $ from 'jquery';

/**
 *
 * @param {string} selector
 */
export function initHeader(selector){
    mainPadding();
    headerFixedWhenBack();
    backOnSubmenu();
    mobileDropdown(selector);
    accessibleSubmenu();

    $(window).on("scroll", () => {
        headerFixed();
    });

    $(window).on("resize", () => {
        mainPadding();
    });
}

function mainPadding() {
    let $headerHeight = $(".header").outerHeight();
    $(".main").css("padding-top", $headerHeight);
}

function headerFixed() {
    let scroll = $(window).scrollTop(),
        header = $(".header"),
        headerHeight = header.outerHeight();
    if (scroll > headerHeight) {
        $("body").addClass("header--fixed");
    } else {
        $("body").removeClass("header--fixed");
    }
    if (scroll > headerHeight * 2) {
        $("body").addClass("header--animated");
    } else {
        $("body").removeClass("header--animated");
    }
    if (scroll > headerHeight * 3) {
        $("body").addClass("header--scrolled");
    } else {
        $("body").removeClass("header--scrolled");
    }
}

function headerFixedWhenBack() {
    const body = document.body;
    const scrollUp = "scroll-up";
    const scrollDown = "scroll-down";
    let lastScroll = 0;

    window.addEventListener("scroll", () => {
        const currentScroll = window.pageYOffset;
        if (currentScroll <= 0) {
            body.classList.remove(scrollUp);
            return;
        }

        if (currentScroll > lastScroll && !body.classList.contains(scrollDown)) {
            // down
            body.classList.remove(scrollUp);
            body.classList.add(scrollDown);
        } else if (currentScroll < lastScroll && body.classList.contains(scrollDown)) {
            // up
            body.classList.remove(scrollDown);
            body.classList.add(scrollUp);
        }
        lastScroll = currentScroll;
    });
}

function backOnSubmenu() {
    $('.navigation-mobile .sub-menu').prepend('<span class="backlevel__icon"><i class="far fa-angle-left"></i></span>');
    $('.navigation-mobile .menu-item-has-children').append('<span class="sublevel__icon"><i class="far fa-angle-right"></i></span>');
}

function mobileDropdown(el) {
    if($(el).length > 0) {
        $(el + ' > .sublevel__icon').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            let $target = $(e.currentTarget),
                $submenu = $target.prev('.sub-menu');
            $submenu.css('left', 0);
            $target.siblings('a').attr('aria-expanded', 'true');
        });

        $(el + ' > ul .backlevel__icon').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            let $target = $(e.currentTarget).parent('ul');
            $target.css('left', '100%');
            $target.closest('.menu-item-has-children').children('a').attr('aria-expanded', 'false');
        });

        $('[data-slidein-close]').on('click', function() {
            $('.navigation-mobile .sub-menu').css('left', '100%');
            $('.navigation-mobile .menu-item-has-children > a').attr('aria-expanded', 'false');
        });
    }
}

function accessibleSubmenu() {
    const $items = $('.navigation.navbar-nav .menu-item-has-children');

    $items.each(function() {
        $(this).children('a').attr({
            'role': 'button',
            'aria-expanded': 'false'
        });
    });

    $items.on('mouseenter', function() {
        $(this).addClass('is-hovered');
        $(this).children('a').attr('aria-expanded', 'true');
    });

    $items.on('mouseleave', function() {
        $(this).removeClass('is-hovered');
        $(this).children('a').attr('aria-expanded', 'false');
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $items.removeClass('is-hovered');
            $items.children('a').attr('aria-expanded', 'false');
        }
    });
}