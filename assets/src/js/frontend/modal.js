import $ from 'jquery';
import {elementAvailable} from "../utils/utils";

export function initCustomerCareModal(){
    modal(".js-modal-customer-care", "#open-support");
}

function modal(modalSelector, btnSelector) {
    let $openBtn = $(btnSelector),
        $modal = $(modalSelector),
        $closeBtn = $modal.find('.close');

    if(!elementAvailable($openBtn) || !elementAvailable($modal)){
        return;
    }

    // open the modal
    $openBtn.click(function (e) {
        e.preventDefault();
        $modal.addClass('is-open');
        $('body').addClass('no-scroll');
    });

    $closeBtn.click(function () {
        $modal.removeClass('is-open');
        $('body').removeClass('no-scroll');
    });

    // When the user clicks anywhere outside of the modal, close it
    $(document).on('click', window, function (e) {
        if ($(e.target).is(modalSelector)) {
            $modal.removeClass('is-open');
            $('body').removeClass('no-scroll');
        }
    });
}