import $ from 'jquery';
import {elementAvailable} from "../utils/utils";

export function alterAttributesView(){
    let $sizesFilter = $('#filter-sizes');
    if(!elementAvailable($sizesFilter)){
        return;
    }
    let $sizesFilterItem = $sizesFilter.children('li'),
        $sizesFilterSelect = $sizesFilter.next('select'),
        $sizesNotice = $('#sizes-notice');

    $sizesFilterItem.each(function () {
        let $this = $(this), sizeValue;

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