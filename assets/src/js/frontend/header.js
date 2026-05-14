const $ = jQuery;

export default class {
    constructor(el) {
        this.mainPadding();
        this.headerFixed();
        this.backOnSubmenu();
        this.mobileDropdown(el);
        this.accessibleSubmenu();

        $(window).on("scroll", ()=> {
            this.headerFixed();
        });

        $(window).on("resize", ()=> {
            this.mainPadding();
        });
    }

    mainPadding() {
        let $ = jQuery,
            $headerHeight = $(".header").outerHeight();
        $(".main").css("padding-top", $headerHeight);
    }

    headerFixed() {
        let $ = jQuery,
            scroll = $(window).scrollTop(),
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

    backOnSubmenu() {
        $('.navigation-mobile .sub-menu').prepend('<span class="backlevel__icon"><i class="far fa-angle-left"></i></span>');
        $('.navigation-mobile .menu-item-has-children').append('<span class="sublevel__icon"><i class="far fa-angle-right"></i></span>');
    };

    mobileDropdown(el) {
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

    accessibleSubmenu() {
        const $items = $('.header__navigation .menu-item-has-children');

        $items.each(function() {
            $(this).children('a').attr({
                'role': 'button',
                'aria-expanded': 'false',
                'tabindex': '0'
            });
            $(this).find('.sub-menu a').attr('tabindex', '-1');
        });

        $items.on('mouseenter', function() {
            $(this).addClass('is-hovered');
            $(this).children('a').attr('aria-expanded', 'true');
            $(this).children('.sub-menu').addClass('is-open');
        });

        $items.on('mouseleave', function() {
            $(this).removeClass('is-hovered');
            $(this).children('a').attr('aria-expanded', 'false');
            $(this).children('.sub-menu').removeClass('is-open');
        });

        $items.children('a').on('keydown', function(e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            e.preventDefault();
            const $a = $(this);
            const $li = $a.parent();
            const isOpen = $li.hasClass('is-hovered');
            if (isOpen) {
                $li.removeClass('is-hovered');
                $a.attr('aria-expanded', 'false');
                $li.find('.sub-menu a').attr('tabindex', '-1');
            } else {
                $li.addClass('is-hovered');
                $a.attr('aria-expanded', 'true');
                $li.children('.sub-menu').find('a').attr('tabindex', '0');
            }
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $items.removeClass('is-hovered');
                $items.children('a').attr('aria-expanded', 'false');
                $items.find('.sub-menu a').attr('tabindex', '-1');
            }
        });
    }

    /* Old Dropdown
    initDropdown(el) {
        if($(el).length > 0) {
            this.last_menu_id = "";
            this.hideMenus = function () {
                jQuery('.sub-menu').slideUp();
            };
            let self = this;
            $(el + ' > a').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                let $target = $(e.currentTarget),
                    $submenu = $target.next('.sub-menu'),
                    $menu = $target.parents('li'),
                    menu_id = $menu.attr('id');
                if (menu_id === self.last_menu_id) {
                    $submenu.slideUp();
                    self.last_menu_id = "";
                } else {
                    self.hideMenus();
                    $submenu.slideDown();
                    self.last_menu_id = menu_id;
                }
            });
            $(document).click(function () {
                self.hideMenus();
                self.last_menu_id = "";
            });
        }
    };*/

}
