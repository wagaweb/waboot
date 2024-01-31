import $ from 'jquery';

/**
 *
 * @param {string} selector
 */
export function initHeader(selector){
    headerFixedWhenBack();
    backOnSubmenu();
    mobileDropdown(selector);

    $(window).on("scroll", () => {
        headerFixed();
    });

    $(window).on("resize", () => {

    });
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
        $(el + ' > .sublevel__icon').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let $target = $(e.currentTarget),
                $submenu = $target.prev('.sub-menu');
            $submenu.css('left',0);
        });
        $(el + ' > a').on('click', function (e) {
            let my_href = $(this).attr("href");
            if(my_href === '#'){
                e.preventDefault();
                e.stopPropagation();
                let $target = $(e.currentTarget),
                    $submenu = $target.next('.sub-menu');
                $submenu.css('left',0);
            }
        });
        $(el + ' > ul .backlevel__icon').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let $target = $(e.currentTarget).parent('ul');
            $target.css('left','100%');
        });
        $('[data-slidein-close]').on('click', function(){
            $('.navigation-mobile .sub-menu').css('left','100%');
        });
    }
}
