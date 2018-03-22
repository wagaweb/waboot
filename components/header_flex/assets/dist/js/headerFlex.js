jQuery(document).ready(function($){

    /**
    * Enables the Dropdown functionality
    * @param {string} el the menu with .sub-menu elements
    */
    var Dropdown = function(el){
        this.last_menu_id = "";
        this.hideMenus = function(){
            jQuery('.sub-menu').slideUp();
        };
        var self = this;
        $(el+' > a').on('click', function(e){
            e.preventDefault();
            e.stopPropagation();
            var $target = $(e.currentTarget),
                $submenu = $target.next('.sub-menu'),
                $menu = $target.parents('li'),
                menu_id = $menu.attr('id');
            if(menu_id === self.last_menu_id){
              $submenu.slideUp();
              self.last_menu_id = "";
            }else{
              self.hideMenus();
              $submenu.slideDown();
              self.last_menu_id = menu_id;
            }
        });
        $(document).click(function() {
            self.hideMenus();
            self.last_menu_id = "";
        });
    };

    if($('.menu-item-has-children').length > 0){
        new Dropdown('.menu-item-has-children');
    }


    /**
     * Enable Toggle
     */
    $('.navbar-toggle').click(function(){
        $('.main-navigation').toggle({
            'easing': 'swing'
        });
    });

    $(window).on('resize', function(){

    });
});
