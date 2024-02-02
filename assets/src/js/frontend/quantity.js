import $ from 'jquery';

export function enableProductQuantity(){
    addToCartQuantity();
}

function addToCartQuantity() {
    if($('body').hasClass('single-product')) {
        $(".product__main .quantity").prepend('<span class="quantity__label">Quantità</span>');
        $(".product__main .quantity").append('<a data-action="increment" class="increment__button increment__button--inc">+</a><a data-action="decrement" class="increment__button increment__button--dec">-</a>');

        $(".increment__button").on("click", function() {
            let $clickedButton = $(this),
                $qntInput = $clickedButton.parent().find("input"),
                currentValue = $qntInput.val(),
                newValue;
            if ($clickedButton.data('action') === "increment") {
                newValue = parseFloat(currentValue) + 1;
            } else {
                // Don't allow decrementing below zero
                if (currentValue > 0) {
                    newValue = parseFloat(currentValue) - 1;
                } else {
                    newValue = 0;
                }
            }
            $qntInput.val(newValue);
        });
    }
}