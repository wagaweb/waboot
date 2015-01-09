jQuery(document).ready(function ($) {
    "use strict";
    $(".font-family-selector").on("change",function(){
        var $familySeletor = $(this);
        var $styleSelector = $(this).siblings(".font-style-selector");
        var $charsetSelector = $(this).siblings(".font-charset-selector");
        var $categoryInput = $(this).siblings(".font-category-selector");
        var request = $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                action: "gfontfetcher_getFontInfo",
                family: $(this).val()
            },
            dataType: "json",
            beforeSend: function(){
                $familySeletor.attr("disabled","disabled");
                $styleSelector.attr("disabled","disabled");
                $charsetSelector.attr("disabled","disabled");
            }
        });
        request.done(function(data, textStatus, jqXHR){
            console.log(data);
            //Assign new styles to the html select
            $styleSelector.html((function(){
                var output = "";
                $.each(data.variants,function(){
                    output += "<option value='"+this+"'>"+this+"</option>";
                });
                return output;
            })());
            //Assign new charset to the html select
            $charsetSelector.html((function(){
                var output = "";
                $.each(data.subsets,function(){
                    output += "<option value='"+this+"'>"+this+"</option>";
                });
                return output;
            })());
            //Assign new category to the html input
            $categoryInput.val(data.category);
        });
        request.fail(function(jqXHR, textStatus, errorThrown){
            console.log(errorThrown);
        });
        request.always(function(result, textStatus, returned){
           $familySeletor.removeAttr("disabled");
           $styleSelector.removeAttr("disabled");
           $charsetSelector.removeAttr("disabled");
        });
    });
});
