export default class{

    constructor(){
        let $ = jQuery;
        this.fixCartTableColumns();
        this.enableIncrementButtons();
        $(document.body).on( 'updated_cart_totals', ()=>{
            this.enableIncrementButtons();
            this.fixCartTableColumns();
        } );
    }

    /**
     * Appending Increment Buttons
     */
    enableIncrementButtons() {
        let $ = jQuery;
        if($('.increment__button').length > 0){
            return;
        }
        $(".quantity").append('<a data-action="increment" class="increment__button increment__button--inc">+</a><a data-action="decrement" class="increment__button increment__button--dec">-</a>');
        $(".increment__button").on("click", function() {
            let $clickedButton = $(this),
                $qntInput = $clickedButton.parent().find("input"),
                $updateCartButton = $('button[name="update_cart"]'),
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
            $updateCartButton.attr('disabled',false);

            $("[name='update_cart']").trigger("click");
        });
    }

    /**
     * Fix thead columns colspan in cart table
     */
    fixCartTableColumns() {
        let $ = jQuery;
        $('.woocommerce-cart-form__contents thead th.product-name').attr("colspan", 3);
        $('.woocommerce-cart-form__contents thead th.product-remove').remove();
        $('.woocommerce-cart-form__contents thead th.product-thumbnail').remove();
    }

}