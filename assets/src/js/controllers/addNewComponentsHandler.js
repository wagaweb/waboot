let AppData = {
    testData: 'Hello World!',
    available_components: []
};

let AppParams = {
    el: '#addNewComponents',
    data: AppData,
    mounted: function(){
        this.getComponentsFromRepository();
    },
    components: {
        'waboot-component': {
            props: ['data'],
            methods: {
                downloadComponent: function(){
                    this.$parent.requestComponentInstallation(this.data.slug)
                        .then((data, textStatus, jqXHR ) => {
                            console.log('Installed');
                        }, (jqXHR, textStatus, errorThrown) => {
                            console.log(errorThrown);
                        });
                }
            }
        }
    },
    methods: {
        getComponentsFromRepository: function(){
            jQuery(this.$el).addClass('loading');
            this.requestComponentsFromRepository()
                .then((data, textStatus, jqXHR ) => {
                    jQuery(this.$el).removeClass('loading');
                    this.available_components = data.data;
                },(jqXHR, textStatus, errorThrown) => {
                    console.log(errorThrown);
                    jQuery(this.$el).removeClass('loading');
                })
        },
        /**
         * return {jqXHR}
         */
        requestComponentsFromRepository: function(){
            return jQuery.ajax({
                url: wbData.ajaxurl,
                method: 'POST',
                dataType: "json",
                data: {
                    'action': 'get_available_components'
                }
            });
        },
        /**
         * return {jqXHR}
         */
        requestComponentInstallation: function(slug){
            return jQuery.ajax({
                url: wbData.ajaxurl,
                method: 'POST',
                dataType: "json",
                data: {
                    'action': 'install_remote_component',
                    'slug': slug
                }
            });
        }
    }
};

export { AppData, AppParams }