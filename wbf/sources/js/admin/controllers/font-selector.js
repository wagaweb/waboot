module.exports = {
    loadWebFonts: function(families){
        if(!_.isEmpty(families)){
            WebFont.load({
                google: {
                    families: families
                }
            });
        }
    }
};