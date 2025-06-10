import $ from 'jquery';

/**
 * @param {string} selector
 */
export function initHeader(selector) {
    mainPadding();
    headerFixedWhenBack();
    backOnSubmenu();
    keyboardSubmenu();
    mobileDropdown(selector);
    closeSubmenuOnFocusOut();
    keyboardMegaMenu();

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
            body.classList.remove(scrollUp);
            body.classList.add(scrollDown);
        } else if (currentScroll < lastScroll && body.classList.contains(scrollDown)) {
            body.classList.remove(scrollDown);
            body.classList.add(scrollUp);
        }
        lastScroll = currentScroll;
    });
}

function backOnSubmenu() {
    $('.navigation-mobile .sub-menu').each(function () {
        $(this).prepend('<button class="backlevel__icon"><i class="far fa-angle-left"></i></button>');
    });

    $('.navigation-mobile .menu-item-has-children').each(function () {
        const $submenu = $(this).children('.sub-menu');
        if ($submenu.length) {
            $('<button class="sublevel__icon" aria-haspopup="true" aria-expanded="false"><i class="far fa-angle-right"></i></button>').insertBefore($submenu);
        }
    });
}

function keyboardSubmenu() {
    const $menuItems = $('.menu > li');

    $menuItems.on('focusin', function () {
        const $li = $(this);

        // Chiude altri submenu aperti
        $li.siblings('.submenu-open')
            .removeClass('submenu-open')
            .find('> a')
            .attr('aria-expanded', 'false');

        // Apre questo submenu se esiste
        const $submenu = $li.find('> .sub-menu');
        if ($submenu.length) {
            $li.addClass('submenu-open');
            $li.find('> a').attr('aria-expanded', 'true');
        }
    });

    $menuItems.on('focusout', function () {
        const $li = $(this);
        // Ritarda la chiusura per permettere il focus dentro il submenu
        setTimeout(() => {
            if (!$li.find(':focus').length) {
                $li.removeClass('submenu-open');
                $li.find('> a').attr('aria-expanded', 'false');
            }
        }, 10);
    });
}

function closeSubmenuOnFocusOut() {
    $('.menu-item-has-children').on('focusout', function () {
        const $li = $(this);
        setTimeout(() => {
            if (!$li.find(':focus').length) {
                $li.removeClass('submenu-open');
                $li.find('.sublevel__icon').attr('aria-expanded', 'false');
            }
        }, 10);
    });
}


function mobileDropdown(el) {
    if ($(el).length > 0) {
        $(el + ' > .sublevel__icon').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let $target = $(e.currentTarget),
                $submenu = $target.siblings('.sub-menu');
            $submenu.css('left', 0);
        });

        $(el + ' > a').on('click', function (e) {
            let my_href = $(this).attr("href");
            if (my_href === '#') {
                e.preventDefault();
                e.stopPropagation();
                let $target = $(e.currentTarget),
                    $submenu = $target.siblings('.sub-menu');
                $submenu.css('left', 0);
            }
        });

        $(el + ' > ul .backlevel__icon').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let $target = $(e.currentTarget).closest('ul');
            $target.css('left', '100%');
        });

        $('[data-slidein-close]').on('click', function () {
            $('.navigation-mobile .sub-menu').css('left', '100%');
        });
    }
}

function keyboardMegaMenu() {
    const $menu = $('.header__megamenu');

    $menu.find('a').on('keydown', function (e) {
        const $current = $(this);
        const $li = $current.closest('li');
        const $allItems = $li.parent().find('> li > a');
        let index = $allItems.index($current);

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (index < $allItems.length - 1) {
                    $allItems.eq(index + 1).focus();
                } else {
                    $allItems.eq(0).focus(); // cicla
                }
                break;

            case 'ArrowUp':
                e.preventDefault();
                if (index > 0) {
                    $allItems.eq(index - 1).focus();
                } else {
                    $allItems.eq($allItems.length - 1).focus(); // cicla
                }
                break;

            case 'ArrowRight':
                e.preventDefault();
                const $submenu = $li.find('> .sub-menu');
                if ($submenu.length) {
                    const $firstSubItem = $submenu.find('> li > a').first();
                    $firstSubItem.focus();
                    $li.addClass('submenu-open');
                    $current.attr('aria-expanded', 'true');
                }
                break;

            case 'ArrowLeft':
                e.preventDefault();
                const $parentMenu = $li.closest('.sub-menu');
                if ($parentMenu.length) {
                    const $parentItem = $parentMenu.closest('li').find('> a');
                    $parentItem.focus();
                    $li.removeClass('submenu-open');
                    $parentItem.attr('aria-expanded', 'false');
                }
                break;

            case 'Escape':
                e.preventDefault();
                $('.submenu-open').removeClass('submenu-open');
                $menu.find('[aria-expanded="true"]').attr('aria-expanded', 'false');
                $current.blur();
                break;
        }
    });
}

