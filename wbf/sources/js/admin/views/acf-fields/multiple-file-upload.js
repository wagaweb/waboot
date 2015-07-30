module.exports = {
    init_interface: function(){
        jQuery(document).ready(function($) {
            var $container = $(".mfu-files"),
                tpl = _.template($("#FileUploadInput").html());

            if($container.children().length == 0) add_file_input();

            //Add new field action
            $("a.add-attachment").on("click", function(e){
                e.preventDefault();
                add_file_input();
            });

            //Upload file action
            /*$("a.upload-attachment").on("click", function(e){
             e.preventDefault();
             console.log("Click!");
             });*/

            function add_file_input(){
                $container.append(tpl());
            }
        });
    }
};