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
                    alert('Download '+this.data.title+'!');
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
        }
    }
};

export { AppData, AppParams }