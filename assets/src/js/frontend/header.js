const $ = jQuery;

export default class {
    constructor(el) {
        //this.initDropdown(el);
        this.backOnSubmenu();
        this.mobileDropdown(el);
    }

    backOnSubmenu() {
        $('.navigation-mobile .sub-menu').prepend('<span class="backlevel__icon"><i class="fas fa-angle-left"></i></span>');
        $('.navigation-mobile .menu-item-has-children').append('<span class="sublevel__icon"><i class="fas fa-angle-right"></i></span>');
    };

    mobileDropdown(el) {
        if($(el).length > 0) {
            $(el + ' > .sublevel__icon').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                let $target = $(e.currentTarget),
                    $submenu = $target.prev('.sub-menu');
                $submenu.css('left',0);
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
    };

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
    };

}
