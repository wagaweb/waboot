const $ = jQuery;

export default class {
    constructor(el) {
        this.filtersTrigger();
        this.closeFiltersButton();
    }

    filtersTrigger() {
        let $ = jQuery;
        $('.filters__trigger').on('click', function(){
            $('.WOOF_Widget').addClass('WOOF_Widget--opened');
            //$('.wcpf_widget_filters').addClass('wcpf_widget_filters--opened');
        });
    };

    closeFiltersButton() {
        let $ = jQuery;
        $('.WOOF_Widget').prepend('<a class="close-filters close__button"><i class="far fa-times"></i></a>');
        $('.WOOF_Widget').append('<a class="btn btn-primary close-filters apply__button">Applica</a>');
        //$('.wcpf_widget_filters').prepend('<a href="#" class="close-filters"><i class="far fa-times"></i></a>');
        $('.close-filters').on('click', function(){
            //e.preventDefault();
            //e.stopPropagation();
            $('.WOOF_Widget').removeClass('WOOF_Widget--opened');
            //$('.wcpf_widget_filters').removeClass('wcpf_widget_filters--opened');
        });
    };

}
