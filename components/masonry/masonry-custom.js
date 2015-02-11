jQuery(document).ready(function($) {
	// Initialize Set options
    var $container = $('.blog-masonry');
    $container.imagesLoaded(function(){
        console.log("Images loaded!");
        $container.masonry({
            // gutter: 20,
            itemSelector: '.item'
        });
    });
});



