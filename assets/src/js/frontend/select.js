import $ from 'jquery';

export function initSelect(){
    customSelect();
}

function customSelect() {

    /*
    Reference: http://jsfiddle.net/BB3JK/47/
    */

    $('select').each(function() {
        if( $(this).attr('id') === 'rating' || $(this).attr('id') === 'billing_country' || $(this).attr('id') === 'billing_state') {
            return;
        }
        const $this = $(this);
        const numberOfOptions = $this.children('option').length;

        $this.addClass('select--hidden');
        $this.wrap('<div class="select"></div>').after('<div class="select--styled"></div>');

        const $styledSelect = $this.next('div.select--styled');
        $styledSelect.text($this.children('option').eq(0).text());

        const $list = $('<ul/>', {
            class: 'select__options'
        }).insertAfter($styledSelect);

        for (let i = 0; i < numberOfOptions; i++) {
            const $option = $this.children('option').eq(i);
            $('<li/>', {
                text: $option.text(),
                rel: $option.val()
            }).appendTo($list);
        }

        const $listItems = $list.children('li');

        $styledSelect.click(function(e) {
            e.stopPropagation();
            const $active = $('div.select--styled.active');
            $active.not(this).removeClass('active').next('ul.select__options').hide();
            $(this).toggleClass('active').next('ul.select__options').toggle();
        });

        $listItems.click(function(e) {
            e.stopPropagation();
            $styledSelect.text($(this).text()).removeClass('active');
            $this.val($(this).attr('rel'));
            $list.hide();
        });

        $(document).click(function() {
            $styledSelect.removeClass('active');
            $list.hide();
        });
    });

}