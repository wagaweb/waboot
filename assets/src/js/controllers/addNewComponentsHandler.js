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
                    'thumbnail': 'http://via.placeholder.com/128x128',
                    'description': 'Component A Desc',
                    'tags': ['tag-a','tab-b'],
                    'download_url': '#',
                    'author': 'WAGA'
                },
                {
                    'slug': 'component-b',
                    'title': 'Component b',
                    'thumbnail': 'http://via.placeholder.com/128x128',
                    'description': 'Component B Desc',
                    'tags': ['tag-a','tab-c'],
                    'download_url': '#',
                    'author': 'WAGA'
                },
                {
                    'slug': 'component-c',
                    'title': 'Component C',
                    'thumbnail': 'http://via.placeholder.com/128x128',
                    'description': 'Component C Desc',
                    'tags': ['tag-b','tab-c'],
                    'download_url': '#',
                    'author': 'WAGA'
                }
            ];
        }
    }
};

export { AppData, AppParams }