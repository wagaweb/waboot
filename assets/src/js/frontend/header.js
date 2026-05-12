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

    accessibleSubmenu() {
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
