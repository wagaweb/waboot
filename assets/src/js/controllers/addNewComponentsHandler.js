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
                    let $installButton = jQuery(this.$el).find('[data-install-button]');
                    $installButton.html(wbData.components_installer_labels.installing);
                    this.$parent.requestComponentInstallation(this.data.slug)
                        .then((data, textStatus, jqXHR ) => {
                            if(!data.success){
                                $installButton.html(wbData.components_installer_labels.installFailedShort);
                                console.log(data);
                            }else{
                                $installButton.html(wbData.components_installer_labels.activate);
                            }
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