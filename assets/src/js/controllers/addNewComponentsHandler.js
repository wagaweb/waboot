let AppData = {
    testData: 'Hello World!',
    available_components: [],
    available_components_status: [],
    actionCounter: 0
};

let AppParams = {
    el: '#addNewComponents',
    data: AppData,
    mounted: function(){
        this.getComponentsFromRepository();
    },
    components: {
        'waboot-component': {
            props: ['component_data'],
            data: function(){
                return {
                    'installed': false,
                    'activated': false
                }
            },
            methods: {
                downloadComponent: function(){
                    let $installButton = jQuery(this.$el).find('[data-install-button]');
                    let $activateButton = jQuery(this.$el).find('[data-activate-button]');
                    $installButton.html(wbData.components_installer_labels.installing);
                    $installButton.attr('disabled','disabled');
                    this.$parent.requestComponentInstallation(this.component_data.slug)
                        .then((data, textStatus, jqXHR ) => {
                            if(!data.success){
                                $installButton.html(wbData.components_installer_labels.installFailedShort);
                                console.log(data);
                            }else{
                                this.installed = true;
                                this.$emit('installed',this.component_data.slug);
                                $installButton.hide();
                                $activateButton.show();
                            }
                        }, (jqXHR, textStatus, errorThrown) => {
                            console.log(errorThrown);
                        });
                },
                activateComponent: function(){
                    this.activated = true;
                    this.$emit('activated',this.component_data.slug);
                }
            }
        }
    },
    methods: {
        componentInstalled: function(slug){},
        componentActivated: function(slug){},
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