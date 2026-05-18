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
        const i18n       = window.wabootI18n || {};
        const backLabel  = i18n.backLevel   || 'Torna al livello precedente';
        const openSuffix = i18n.openSubmenu || 'apri sottomenu';

        $('.navigation-mobile .sub-menu').prepend(
            '<button type="button" class="backlevel__icon" aria-label="' + backLabel + '">' +
            '<i class="far fa-angle-left" aria-hidden="true"></i>' +
            '</button>'
        );
        $('.navigation-mobile .menu-item-has-children').each(function () {
            const $li       = $(this);
            const labelText = $li.children('a').clone().children().remove().end().text().trim();
            $li.append(
                $('<button>', {
                    type:            'button',
                    class:           'sublevel__icon',
                    'aria-label':    labelText + ': ' + openSuffix,
                    'aria-expanded': 'false'
                }).html('<i class="far fa-angle-right" aria-hidden="true"></i>')
            );
        });

        // Tutti i sub-menu partono chiusi: rimuovili dal tab order
        $('.navigation-mobile .sub-menu').find('a, button').attr('tabindex', '-1');
    };

    mobileDropdown(el) {
        const $nav = $('.navigation-mobile');
        if (!$nav.length) return;

        function lockSubmenu($submenu) {
            $submenu.find('a, button').attr('tabindex', '-1');
        }

        function unlockSubmenu($submenu) {
            // Backlevel button (figlio diretto del sub-menu)
            $submenu.children('.backlevel__icon').attr('tabindex', '0');
            // Link e sublevel button del livello diretto
            $submenu.children('li').children('a, .sublevel__icon').attr('tabindex', '0');
            // Sub-menu annidati restano bloccati
            $submenu.children('li').children('.sub-menu').find('a, button').attr('tabindex', '-1');
        }

        $nav.on('click', '.sublevel__icon', function (e) {
            e.stopPropagation();
            const $btn     = $(this);
            const $li      = $btn.closest(el);
            const $submenu = $li.children('.sub-menu');
            $submenu.addClass('is-open');
            unlockSubmenu($submenu);
            $btn.attr('aria-expanded', 'true');
            $li.siblings('li').addClass('is-sibling-hidden');
            $submenu.children('.backlevel__icon').trigger('focus');
        });

        $nav.on('click', '.backlevel__icon', function (e) {
            e.stopPropagation();
            const $submenu = $(this).closest('.sub-menu');
            const $li      = $submenu.closest(el);
            $submenu.removeClass('is-open');
            lockSubmenu($submenu);
            $li.children('.sublevel__icon').attr('aria-expanded', 'false');
            $li.siblings('li').removeClass('is-sibling-hidden');
            $li.children('.sublevel__icon').trigger('focus');
        });

        $nav.on('click', '[data-slidein-close]', function () {
            $nav.find('.sub-menu').removeClass('is-open').each(function () {
                $(this).find('a, button').attr('tabindex', '-1');
            });
            $nav.find('.sublevel__icon').attr('aria-expanded', 'false');
            $nav.find('li').removeClass('is-sibling-hidden');
        });
    }

    accessibleSubmenu() {
        const $nav = $('.header__navigation');
        if (!$nav.length) return;

        $nav.find('.menu-item-has-children').each(function () {
            const $li       = $(this);
            const $a        = $li.children('a');
            const isNested  = $li.parents('.sub-menu').length > 0;
            const labelText = $a.clone().children().remove().end().text().trim();
            const iconClass = isNested ? 'fa-angle-right' : 'fa-angle-down';

            $a.removeAttr('role aria-expanded aria-haspopup tabindex');

            const $btn = $('<button>', {
                type:           'button',
                class:          'submenu-toggle',
                'aria-expanded': 'false',
                'aria-label':   labelText + ': apri sottomenu'
            }).html(`<i class="far ${iconClass}" aria-hidden="true"></i>`);

            $a.after($btn);

            $li.children('.sub-menu').find('a, .submenu-toggle').attr('tabindex', '-1');
        });

        function openSubmenu($btn) {
            const $li = $btn.parent();
            $li.addClass('is-hovered');
            $btn.attr('aria-expanded', 'true');
            $li.children('.sub-menu').children('li').find('> a, > .submenu-toggle').attr('tabindex', '0');
        }

        function closeSubmenu($btn, returnFocus) {
            const $li = $btn.parent();
            $li.removeClass('is-hovered');
            $btn.attr('aria-expanded', 'false');
            $li.find('.submenu-toggle').attr('aria-expanded', 'false');
            $li.find('.menu-item-has-children').removeClass('is-hovered');
            $li.children('.sub-menu').find('a, .submenu-toggle').attr('tabindex', '-1');
            if (returnFocus) $btn.trigger('focus');
        }

        $nav.on('click', '.submenu-toggle', function (e) {
            e.stopPropagation();
            const $btn   = $(this);
            const isOpen = $btn.attr('aria-expanded') === 'true';

            $btn.parent().siblings('.menu-item-has-children')
                .find('> .submenu-toggle[aria-expanded="true"]')
                .each(function () { closeSubmenu($(this)); });

            isOpen ? closeSubmenu($btn) : openSubmenu($btn);
        });

        $nav.on('keydown', '.submenu-toggle, .sub-menu a', function (e) {
            if (e.key !== 'Escape') return;
            e.preventDefault();
            e.stopPropagation();
            const $btn = $(this).closest('.menu-item-has-children').children('.submenu-toggle');
            closeSubmenu($btn, true);
        });

        $nav.find('.menu-item-has-children').on('focusout', function () {
            const $li  = $(this);
            const $btn = $li.children('.submenu-toggle');
            setTimeout(function () {
                if (!$li[0].contains(document.activeElement)) {
                    closeSubmenu($btn);
                }
            }, 0);
        });

        $nav.find('.menu-item-has-children')
            .on('mouseenter', function () {
                $(this).addClass('is-hovered');
            })
            .on('mouseleave', function () {
                const $li  = $(this);
                const $btn = $li.children('.submenu-toggle');
                $li.removeClass('is-hovered');
                if ($btn.length) closeSubmenu($btn);
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
