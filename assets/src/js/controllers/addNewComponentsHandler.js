let AppData = {
    testData: 'Hello World!'
};

let AppParams = {
    el: '#addNewComponents',
    data: AppData,
    mounted: function(){
        alert(this.testData)
    },
    components: {
        'waboot-component': {

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