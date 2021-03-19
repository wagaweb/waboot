export default class{

    constructor(){
        let $ = jQuery;
        let $sizesFilter = $('#filter-sizes');
        this.alterAttributesView($sizesFilter);
    }

    alterAttributesView(el) {
        let $sizesFilterItem = el.children('li'),
            $sizesFilterSelect = el.next('select'),
            $sizesNotice = jQuery('#sizes-notice');

        $sizesFilterItem.each(function () {
            let $this = jQuery(this), sizeValue;

            $this.on('click', function () {
                if ($this.hasClass('active')) {
                    $this.removeClass('active');
                    sizeValue = '';
                } else {
                    $sizesFilterItem.removeClass('active');
                    $this.addClass('active');
                    sizeValue = $this.attr('data-value');
                }

                if($sizesNotice.hasClass('is-visible')){
                    $sizesNotice.hide('fast');
                    $sizesNotice.removeClass('is-visible');
                }

                $sizesFilterSelect.val(sizeValue).trigger('change');
            });
        });
    }

};