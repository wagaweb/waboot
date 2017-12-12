let AppData = {
    testData: 'Hello World!',
    available_components: []
};

let AppParams = {
    el: '#addNewComponents',
    data: AppData,
    mounted: function(){
        this.available_components = this.getComponentsFromRepository();
    },
    components: {
        'waboot-component': {
            props: ['data']
        }
    },
    methods: {
        getComponentsFromRepository: function(){
            return [
                {
                    'slug': 'component-a',
                    'title': 'Component a',
                    'description': 'Component A Desc',
                    'tags': ['tag-a','tab-b'],
                    'download_url': '#'
                },
                {
                    'slug': 'component-b',
                    'title': 'Component b',
                    'description': 'Component B Desc',
                    'tags': ['tag-a','tab-c'],
                    'download_url': '#'
                },
                {
                    'slug': 'component-c',
                    'title': 'Component C',
                    'description': 'Component C Desc',
                    'tags': ['tag-b','tab-c'],
                    'download_url': '#'
                }
            ];
        }
    }
};

export { AppData, AppParams }