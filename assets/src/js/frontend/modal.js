export default class{

    constructor(){
        this.modal(".js-modal-customer-care", "#open-support");
    }

    modal(modalSelector, btnSelector) {
        let $openBtn = jQuery(btnSelector),
            $modal = jQuery(modalSelector),
            $closeBtn = $modal.find('.close');

        if ($openBtn.length > 0 && $modal.length > 0) {
            // open the modal
            $openBtn.click(function (e) {
                e.preventDefault();
                $modal.addClass('is-open');
                jQuery('body').addClass('no-scroll');
            });

            $closeBtn.click(function () {
                $modal.removeClass('is-open');
                jQuery('body').removeClass('no-scroll');
            });

            // When the user clicks anywhere outside of the modal, close it
            jQuery(document).on('click', window, function (e) {
                if (jQuery(e.target).is(modalSelector)) {
                    $modal.removeClass('is-open');
                    jQuery('body').removeClass('no-scroll');
                }
            });
        }
    };

};
