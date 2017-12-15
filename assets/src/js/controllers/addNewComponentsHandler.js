let AppData = {
    available_components: [],
    isLoadingComponents: false
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
                    'installing': false,
                    'activating': false,
                    'activated': false,
                    'installationFailed': false,
                }
            },
            computed: {
                actionButtonLabel: function(){
                    if(this.installationFailed){
                        return wbData.components_installer_labels.installFailedShort;
                    }else if(this.installing){
                        return wbData.components_installer_labels.installing;
                    }else if(this.activating){
                        return wbData.components_installer_labels.activating;
                    }else if(this.installed && !this.activated){
                        return wbData.components_installer_labels.activate;
                    }else if(this.installed && this.activated){
                        return wbData.components_installer_labels.active;
                    }else{
                        return wbData.components_installer_labels.download;
                    }
                }
            },
            mounted: function(){
                if(this.component_data.status === 1){
                    this.installed = true;
                }else if(this.component_data.status === 2){
                    this.installed = true;
                    this.activated = true;
                }
            },
            methods: {
                /**
                 * Download the component
                 */
                downloadComponent: function(){
                    this.installing = true;
                    this.$parent.requestComponentInstallation(this.component_data.slug)
                        .then((data, textStatus, jqXHR ) => {
                            if(!data.success){
                                this.installationFailed = true;
                                this.installing = false;
                                console.log(data);
                            }else{
                                this.installed = true;
                                this.installing = false;
                                this.$emit('installed',this.component_data.slug);
                            }
                        }, (jqXHR, textStatus, errorThrown) => {
                            console.log(errorThrown);
                        });
                },
                /**
                 * Activate the component
                 */
                activateComponent: function(){
                    this.activating = true;
                    this.$parent.requestComponentActivation(this.component_data.slug)
                        .then((data, textStatus, jsXHR) => {
                            if(!data.success){
                                this.installationFailed = true;
                                this.activating = false;
                                console.log(data);
                            }else{
                                this.activated = true;
                                this.activating = false;
                                this.$emit('activated',this.component_data.slug);
                            }
                        }, (jqXHR, textStatus, errorThrown) => {
                            console.log(errorThrown);
                        });
                }
            }
        }
    },
    methods: {
        /**
         * Component installed event callback
         * @param {string} slug
         */
        componentInstalled: function(slug){
            if(typeof this.available_components[""+slug+""] !== "undefined"){
                this.available_components[""+slug+""].status = 1;
            }
        },
        /**
         * Component activated event callback
         * @param {string} slug
         */
        componentActivated: function(slug){
            if(typeof this.available_components[""+slug+""] !== "undefined"){
                this.available_components[""+slug+""].status = 2;
            }
        },
        /**
         * Get components from repository
         */
        getComponentsFromRepository: function(){
            this.isLoadingComponents = true;
            this.requestComponentsFromRepository()
                .then((data, textStatus, jqXHR ) => {
                    this.isLoadingComponents = false;
                    this.available_components = data.data;
                },(jqXHR, textStatus, errorThrown) => {
                    console.log(errorThrown);
                    this.isLoadingComponents = false;
                })
        },
        /**
         * Ajax request for getting components
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
         * Ajax request for installing a component
         * @param {string} slug
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
        },
        /**
         * Ajax request for activating a component
         * @param {string} slug
         * return {jqXHR}
         */
        requestComponentActivation: function(slug){
            return jQuery.ajax({
                url: wbData.ajaxurl,
                method: 'POST',
                dataType: "json",
                data: {
                    'action': 'activate_component_from_installer',
                    'slug': slug
                }
            });
        }
    }
};

export { AppData, AppParams }