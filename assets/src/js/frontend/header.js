const $ = jQuery;

export default class {
    constructor(el) {
        this.initDropdown(el);
        this.enableToggle();
    }

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

    enableToggle() {
        $('.header__toggle').click(function () {
            $('.header__navigation').toggle({
                'easing': 'swing'
            });
        });
    }

}
