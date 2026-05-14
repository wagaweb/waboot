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
    $('.navigation-mobile .sub-menu').on('focusin', function() {
        $(this).addClass('is-open');
    });
    if($(el).length > 0) {
        $(el + ' > .sublevel__icon').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            let $target = $(e.currentTarget),
                $li = $target.parent(el),
                $submenu = $target.prev('.sub-menu');
            $submenu.css('left', 0).addClass('is-open');
            $target.siblings('a').attr('aria-expanded', 'true');
            $li.siblings('li').addClass('is-sibling-hidden');
        });

        $(el + ' > ul .backlevel__icon').on('click keydown', function(e) {
            if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
            e.preventDefault();
            e.stopPropagation();
            let $target = $(e.currentTarget).closest('ul.sub-menu'),
                $li = $target.parent(el);
            $target.css('left', '100%').removeClass('is-open');
            $li.children('a').attr('aria-expanded', 'false');
            $li.siblings('li').removeClass('is-sibling-hidden');
        });

        $('[data-slidein-close]').on('click keydown', function(e) {
            if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
            $('.navigation-mobile .sub-menu').css('left', '100%').removeClass('is-open');
            $('.navigation-mobile .menu-item-has-children > a').attr('aria-expanded', 'false');
            $('.navigation-mobile li').removeClass('is-sibling-hidden');
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