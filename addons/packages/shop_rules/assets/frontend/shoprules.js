import $ from 'jquery';

export default class {

    constructor() {

        if ($('body').hasClass("woocommerce-cart")) {
            this.init_giftbox_feature();
            this.giftOnCart();
            this.modalGwp();
        }

    }

    /**
     * Init the giftbox feature in cart
     */
    init_giftbox_feature(){
        var $giftbox_input_tpl = $("input[name='order_giftbox']").clone();
        var unbind_giftbox_input_events = function(){
            var $giftbox_input = $("input[name='order_giftbox']");
            $giftbox_input.unbind('click');
        };
        var bind_giftbox_input_events = function(){
            var $giftbox_input = $("input[name='order_giftbox']"),
                $checkout_button = $('.wc-proceed-to-checkout a'),
                checkout_link = $checkout_button.attr('href');
            if($giftbox_input.length > 0){
                $giftbox_input.on('change', function(){
                    var order_giftbox_querystring_params = [];
                    $giftbox_input.each(function(){
                        if($(this).is(":checked")){
                            order_giftbox_querystring_params.push($(this).data('ordergiftbox'));
                        }
                    });
                    if(order_giftbox_querystring_params.length > 0){
                        $checkout_button.attr('href',checkout_link+'?order_giftbox='+order_giftbox_querystring_params.join());
                    }else{
                        $checkout_button.attr('href',checkout_link);
                    }
                });
            }
        };

        bind_giftbox_input_events();

        $( document.body ).on( 'updated_cart_totals', function(){
            //var $giftbox_input_wrapper = $(".order-giftbox-wrapper"),
            //    $giftbox_input = $("input[name='order_giftbox']");

            unbind_giftbox_input_events();
            bind_giftbox_input_events();

            //$giftbox_input_wrapper.addClass('loading');
            //$giftbox_input.attr('checked',false).trigger('change');

            /*$.ajax({
                url: customCoppolaOptions.ajax_url,
                data: {
                    action: 'can_order_giftbox'
                },
                dataType: "json",
                method: "POST"
            }).then(function(result, textStatus, jqXHR){
                debugger;
                if(result.data.can_order){
                    if($("input[name='order_giftbox']").length == 0){
                        $giftbox_input_tpl.prepend($giftbox_input_wrapper);
                    }
                    $giftbox_input_wrapper.show();
                    unbind_giftbox_input_events();
                    bind_giftbox_input_events();
                    $giftbox_input_wrapper.removeClass('loading');
                }else{
                    $giftbox_input.remove();
                }
            },function(jqXHR, textStatus, errorThrown){
                console.log(errorThrown);
            });*/
        });
    }

    giftOnCart() {
        $(document).on('click', '#add-gift', function() {
            $('#gift-modal').show();
        });
        $(document).on('click', '.giftoncart__modal--close', function() {
            $('#gift-modal').hide();
        });

        $(document).on('change', '#gift-location', function() {
            const $this = $(this);
            const location = $this.val();
            const $giftTableSalone = $('#gift-table-salon');
            const $salonRadios = $('[data-location]');
            if (location.length === 0) {
                $giftTableSalone.hide();
            } else {
                $giftTableSalone.show();
                $salonRadios.hide();
                $salonRadios.find('input[type="radio"]').prop('checked', false);
                $(`[data-location="${location}"]`).show();
                $('.giftoncart__send').hide();
            }
        });

        $(document).on('change', '.searchsalon__list input[type="radio"]', function() {
            $('.giftoncart__send').show();
        });
    }

    modalGwp() {
        $(document).on('click', '.gwp-choice__modal--close', function(){
            $('.gwp-choice__modal').css('display', 'none');
        });
        $(document).on('click','#gwp-choice-modal', function(e){
            if($(e.target).is('#gwp-choice-modal')){
                $('.gwp-choice__modal').css('display', 'none');
            }
        });
        $(document).on('click', 'a[data-modal-target]', function(){
            const target = $(this).data('modal-target');
            $('.'+target).css('display', 'flex');
        });
    }
}
