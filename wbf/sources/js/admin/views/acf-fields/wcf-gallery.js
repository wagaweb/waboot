module.exports = {
    init_interface: function(){
        var $ = jQuery;
        var custom_uploader;
        $('#upload-btn').click(function(e) {
            e.preventDefault();
            //If the uploader object has already been created, reopen the dialog
            if (custom_uploader) {
                custom_uploader.open();
                return;
            }
            //Extend the wp.media object
            custom_uploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose Image',
                button: {
                    text: 'Choose Image'
                },
                multiple: true
            });
            custom_uploader.on('select', function() {
                var selection = custom_uploader.state().get('selection');
                var imgIds = [];
                selection.map( function( attachment ) {
                    attachment = attachment.toJSON();
                    imgIds.push(attachment.id);
                    console.log(imgIds);
                    $.ajax(wbfData.ajaxurl,{ //ajax url is not available in the front end. Needs to wp_localize_script
                        data: {
                            action: "wcf_get_thumbnail", //the action specified in ajax wordpress hooks
                            id: attachment.id
                        },
                        dataType: "json", //Default is an "intelligent guess"; does not work very often
                        method: "POST" //Default is GET
                    }).done(function(data, textStatus, jqXHR){
                        console.log(data);
                        var newData = JSON.stringify(data);
                        var imgUrl = JSON.parse(newData);
                        var newImgUrl = imgUrl.thumb;
                        var newDataIndex = $('.containerImgGalleryAdmin').length;
                        $("#imageContainer").append("<div class='containerImgGalleryAdmin'>" +
                        "<img class='imgGalleryAdmin' src=" +newImgUrl+" data-id='"+ attachment.id +"'>" +
                        "<div class='deleteImg'>"+
                        "<a class='acf-icon dark remove-attachment ' data-index='"+ newDataIndex  +"' href='#' data-id='"+ attachment.id +"'>"+
                        "<i class='acf-sprite-delete'></i>"+
                        "</a>"+
                        "</div></div>");
                        $("#imageContainer").sortable('refresh');
                    }).fail(function(jqXHR, textStatus, errorThrown){
                        console.log(errorThrown);
                    }).always(function(result, textStatus, type){
                        console.log(type);
                    });
                });
                var savedVal = $('#imgId').val();
                console.log(savedVal);
                if(savedVal=="  "||savedVal==''||savedVal==' '){
                    $('#imgId').val(imgIds);
                }else{
                    $('#imgId').val(savedVal + ',' + imgIds);
                }

            });
            custom_uploader.open();
        });
        $('#imageContainer').on('mouseover', '.containerImgGalleryAdmin',function(){
            $(this).addClass('on');
        });
        $('#imageContainer').on('click',' .deleteImg', function(e){
            e.preventDefault();
            var oldValues = $('#imgId').val();
            var arrayValues = oldValues.split(",").map(Number);
            var elemIndex = $(this).children().attr('data-index');
            var imgIds = [];
            console.log(arrayValues, elemIndex);
            $.each(arrayValues, function (index, value) {
                console.log(index, elemIndex);
                if (index != elemIndex) {
                    imgIds.push($(value)[0]);
                }
            });
            $('.containerImgGalleryAdmin.on').remove();
            $('#imgId').val(imgIds);
            $.each($('.deleteImg'), function (index, value) {
                $(this).children().attr('data-index', index);
            });
        });
        $('#imageContainer').on('click','.imgGalleryAdmin',function(){
            if($('.containerImgGalleryAdmin').hasClass('selected')){
                $('.containerImgGalleryAdmin').removeClass('selected');
            }
            $('#imageContainer, .uploadContainer, .mainContainer').addClass('selected');
            $(this).parent().addClass('selected');
            $('#imageInfo').addClass('active');
            $.ajax(wbfData.ajaxurl,{ //ajax url is not available in the front end. Needs to wp_localize_script
                data: {
                    action: "wcf_media_info", //the action specified in ajax wordpress hooks
                    id: $(this).attr('data-id')
                },
                dataType: "json", //Default is an "intelligent guess"; does not work very often
                method: "POST" //Default is GET
            }).done(function(data, textStatus, jqXHR){
                $('#imageInfo #imgThumb, #imageInfo .body, #imageInfo .footer').addClass('active');
                $('#imageInfo #imgThumb img').attr('src',data.thumb);
                $('#imageInfo #mainInfo .imgName').text(data.name);
                $('#imageInfo #mainInfo .upload').text(data.upload);
                $('#imageInfo #mainInfo .dimensions').text(data.size +' ('+ data.filesize + ')' );
                $('.title #imageTitle').val(data.title);
                $('.caption #imageCaption').val(data.caption);
                $('.alt #imageAlt').val(data.alt);
                $('.description #imageDescription').val(data.description);
            }).fail(function(jqXHR, textStatus, errorThrown){
                console.log(errorThrown);
            }).always(function(result, textStatus, type){
                console.log(type);
            });
        });
        $('#imageInfo #closeBtn').on('click',function(e){
            e.preventDefault();
            $('.mainContainer, #imageContainer, .uploadContainer, .containerImgGalleryAdmin').removeClass('selected');
            $('#imageInfo, #imageInfo .body, #imageInfo .footer  ').removeClass('active');
        });
        $('#imageInfo #updateBtn').on('click',function(e){
            e.preventDefault();
            $('#imageInfo .spinner').addClass('is-active');
            $(this).attr('disabled','disabled');
            $.ajax(wbfData.ajaxurl,{ //ajax url is not available in the front end. Needs to wp_localize_script
                data: {
                    action: "wcf_update_media_info", //the action specified in ajax wordpress hooks
                    id: $('.containerImgGalleryAdmin.selected .imgGalleryAdmin').attr('data-id'),
                    title:$('.title #imageTitle').val(),
                    caption:$('.caption #imageCaption').val(),
                    alt:$('.alt #imageAlt').val(),
                    description:$('.description #imageDescription').val()
                },
                dataType: "json", //Default is an "intelligent guess"; does not work very often
                method: "POST" //Default is GET
            }).done(function(data, textStatus, jqXHR){
                if(data.response=='true') {
                    $('#imageInfo #updateBtn').removeAttr('disabled');
                    $('#imageInfo .spinner').removeClass('is-active');
                }

            }).fail(function(jqXHR, textStatus, errorThrown){
                console.log(errorThrown);
            }).always(function(result, textStatus, type){
                console.log(type);
            });
        });
        $('#imageContainer').on('click','.containerImgGalleryAdmin.selected .imgGalleryAdmin',function(){
            $('.mainContainer, #imageContainer, .uploadContainer, .containerImgGalleryAdmin').removeClass('selected');
            $('#imageInfo, #imageInfo .body, #imageInfo .footer  ').removeClass('active');
        });
        $('#imageContainer').on('mouseout', '.containerImgGalleryAdmin',function(){
            $(this).removeClass('on');

        });

        $('#imageContainer').sortable({
            stop: function(event, ui) {
                var newImgArray = $('.imgGalleryAdmin');
                $('#imgId').val('');
                var imgIds = [];
                $.each(newImgArray, function(index, value){
                    imgIds.push($(value).attr('data-id'));
                });
                $('#imgId').val(imgIds);
            }
        });
        $('#imageContainer').disableSelection();


    }
};
