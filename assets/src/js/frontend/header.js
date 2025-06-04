const $ = jQuery;

export default class {
    constructor(el) {
        this.$el = $(el);
        this.$nav = null;
        this.closeAllSubmenus = this.closeAllSubmenus.bind(this);

        if (this.$el.length) {
            this.init();
        }

        $(window).on('scroll', () => {
            //this.headerFixed();
        });

        $(window).on('resize', () => {
            //this.mainPadding();
        });
    }

    init() {
        this.backOnSubmenu();
        this.mobileDropdown();
    }

    mainPadding() {
        let $ = jQuery,
            $headerHeight = $('.header').outerHeight();
        $('.main').css('padding-top', $headerHeight);
    }

    headerFixed() {
        let $ = jQuery,
            scroll = $(window).scrollTop(),
            header = $('.header'),
            headerHeight = header.outerHeight();
        if (scroll > headerHeight) {
            $('body').addClass('header--fixed');
        } else {
            $('body').removeClass('header--fixed');
        }
        if (scroll > headerHeight * 2) {
            $('body').addClass('header--animated');
        } else {
            $('body').removeClass('header--animated');
        }
        if (scroll > headerHeight * 3) {
            $('body').addClass('header--scrolled');
        } else {
            $('body').removeClass('header--scrolled');
        }
    }

    closeAllSubmenus() {
        if (!this.$nav) return;
        this.$nav.find('.sub-menu').attr('aria-hidden', 'true');
        this.$nav.find('.sublevel__icon').attr('aria-expanded', 'false');
    }

    backOnSubmenu() {
        $('.navigation-mobile .sub-menu').prepend(
            '<button class="backlevel__icon" aria-label="Torna al menu precedente">' +
            '<i class="fal fa-angle-left" aria-hidden="true"></i>' +
            '</button>'
        );
    }

    mobileDropdown() {
        this.$nav = this.$el;
        if (!this.$nav.length) {
            console.log('Nav non trovato');
            return;
        }

        // Cache dei selettori frequentemente utilizzati
        const $submenus = this.$nav.find('.sub-menu');
        const $sublevelIcons = this.$nav.find('.sublevel__icon');
        const $menuItems = this.$nav.find('.menu-item-has-children');

        console.log('Elementi trovati:', {
            submenus: $submenus.length,
            sublevelIcons: $sublevelIcons.length,
            menuItems: $menuItems.length,
        });

        // Gestione toggle sottomenu solo per mobile
        if (window.matchMedia('(max-width: 991px)').matches) {
            // Gestione toggle sottomenu
            $sublevelIcons.on('click keypress', (e) => {
                if (
                    e.type === 'click' ||
                    (e.type === 'keypress' &&
                        (e.which === 13 || e.which === 32))
                ) {
                    e.preventDefault();
                    e.stopPropagation();

                    const $btn = $(e.currentTarget);
                    const $submenu = $btn.siblings('.sub-menu');
                    const isOpen = $submenu.attr('aria-hidden') === 'false';

                    // Chiudi tutti i sottomenu aperti
                    this.closeAllSubmenus();

                    if (!isOpen) {
                        // Apri il sottomenu con una slide da destra
                        $submenu.attr('aria-hidden', 'false');
                        $btn.attr('aria-expanded', 'true');

                        // Focus sul primo elemento del sottomenu
                        setTimeout(() => {
                            $submenu.find('a:first').focus();
                        }, 300);
                    }
                }
            });

            // Gestione back button
            this.$nav.find('.backlevel__icon').on('click keypress', (e) => {
                if (
                    e.type === 'click' ||
                    (e.type === 'keypress' &&
                        (e.which === 13 || e.which === 32))
                ) {
                    e.preventDefault();
                    e.stopPropagation();

                    const $submenu = $(e.currentTarget).closest('.sub-menu');
                    const $parentItem = $submenu.parent(
                        '.menu-item-has-children'
                    );
                    const $parentSubmenu = $parentItem.closest('.sub-menu');

                    // Chiudi il sottomenu corrente con una slide verso destra
                    $submenu.attr('aria-hidden', 'true');

                    if ($parentSubmenu.length) {
                        // Se siamo in un sottomenu di secondo livello, torniamo al sottomenu padre
                        $parentSubmenu.attr('aria-hidden', 'false');
                        $parentItem
                            .find('.sublevel__icon')
                            .attr('aria-expanded', 'true');
                        setTimeout(() => {
                            $parentItem.find('.sublevel__icon').focus();
                        }, 300);
                    } else {
                        // Se siamo in un sottomenu di primo livello, torniamo al menu principale
                        $parentItem
                            .find('.sublevel__icon')
                            .attr('aria-expanded', 'false');
                        setTimeout(() => {
                            $parentItem.find('.sublevel__icon').focus();
                        }, 300);
                    }
                }
            });

            // Gestione navigazione tastiera
            this.$nav.find('a').on('keydown', (e) => {
                if (e.key !== 'Tab') return;

                const $link = $(e.currentTarget);
                const $menuItem = $link.closest('.menu-item-has-children');
                const $submenu = $menuItem.find('.sub-menu');

                if ($submenu.attr('aria-hidden') === 'false' && !e.shiftKey) {
                    const $nextFocusable = $(':focusable').eq(
                        $(':focusable').index(e.currentTarget) + 1
                    );
                    if (!$nextFocusable.closest($submenu).length) {
                        this.closeAllSubmenus();
                    }
                }
            });

            // Chiusura al click fuori
            $(document).on('click', (e) => {
                if (!$(e.target).closest(this.$el).length) {
                    this.closeAllSubmenus();
                }
            });

            // Chiusura al click su elementi con data-slidein-close
            $('[data-slidein-close]').on('click', this.closeAllSubmenus);

            // Gestione focusout
            $menuItems.each((_, item) => {
                const $menuItem = $(item);
                const $submenu = $menuItem.find('.sub-menu');

                $submenu.on('focusout', () => {
                    setTimeout(() => {
                        if (!$submenu.has(document.activeElement).length) {
                            $submenu.attr('aria-hidden', 'true');
                            $menuItem
                                .find('.sublevel__icon')
                                .attr('aria-expanded', 'false');
                        }
                    }, 10);
                });
            });
        } else {
            console.log('Modalità desktop attiva');

            // Comportamento desktop: hover e interazione per aprire i sottomenu
            $sublevelIcons.each((_, icon) => {
                const $icon = $(icon);
                const $menuItem = $icon.closest('.menu-item-has-children');
                const $submenu = $menuItem.find('.sub-menu');
                const $submenuLinks = $submenu.find('a');

                console.log('Configurando eventi per:', {
                    icon: $icon.length,
                    menuItem: $menuItem.length,
                    submenu: $submenu.length,
                });

                // Gestione click e tastiera sul pulsante sublevel__icon
                $icon.on('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const isOpen = $submenu.attr('aria-hidden') === 'false';

                    if (isOpen) {
                        $submenu.attr('aria-hidden', 'true');
                        $icon.attr('aria-expanded', 'false');
                    } else {
                        $submenu.attr('aria-hidden', 'false');
                        $icon.attr('aria-expanded', 'true');
                        // Focus sul primo link del sottomenu
                        setTimeout(() => {
                            $submenuLinks.first().focus();
                        }, 0);
                    }
                });

                $icon.on('keypress', (e) => {
                    if (e.which === 13 || e.which === 32) {
                        e.preventDefault();
                        e.stopPropagation();

                        const isOpen = $submenu.attr('aria-hidden') === 'false';

                        if (isOpen) {
                            $submenu.attr('aria-hidden', 'true');
                            $icon.attr('aria-expanded', 'false');
                        } else {
                            $submenu.attr('aria-hidden', 'false');
                            $icon.attr('aria-expanded', 'true');
                            // Focus sul primo link del sottomenu
                            setTimeout(() => {
                                $submenuLinks.first().focus();
                            }, 0);
                        }
                    }
                });

                // Gestione hover
                $menuItem
                    .on('mouseenter', () => {
                        $submenu.attr('aria-hidden', 'false');
                        $icon.attr('aria-expanded', 'true');
                    })
                    .on('mouseleave', () => {
                        // Chiudi solo se non c'è focus sul pulsante o sui link del sottomenu
                        if (
                            !$icon.is(document.activeElement) &&
                            !$submenu.has(document.activeElement).length
                        ) {
                            $submenu.attr('aria-hidden', 'true');
                            $icon.attr('aria-expanded', 'false');
                        }
                    });

                // Gestione focus solo per il pulsante sublevel__icon
                $icon.on('focus', () => {
                    $submenu.attr('aria-hidden', 'false');
                    $icon.attr('aria-expanded', 'true');
                });

                // Gestione focusout per il pulsante e il sottomenu
                $menuItem.on('focusout', (e) => {
                    setTimeout(() => {
                        // Se il focus non è né sul pulsante né sui link del sottomenu e non c'è hover
                        if (
                            !$icon.is(document.activeElement) &&
                            !$submenu.has(document.activeElement).length &&
                            !$menuItem.is(':hover')
                        ) {
                            $submenu.attr('aria-hidden', 'true');
                            $icon.attr('aria-expanded', 'false');
                        }
                    }, 10);
                });

                // Gestione Tab all'interno del sottomenu
                $submenuLinks.last().on('keydown', (e) => {
                    if (e.key === 'Tab' && !e.shiftKey) {
                        // Se siamo sull'ultimo link e premo Tab, chiudo il sottomenu
                        $submenu.attr('aria-hidden', 'true');
                        $icon.attr('aria-expanded', 'false');
                    }
                });

                $submenuLinks.first().on('keydown', (e) => {
                    if (e.key === 'Tab' && e.shiftKey) {
                        // Se siamo sul primo link e premo Shift+Tab, torno al pulsante
                        e.preventDefault();
                        $icon.focus();
                    }
                });
            });
        }
    }
}
