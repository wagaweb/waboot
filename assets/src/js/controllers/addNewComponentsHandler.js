let AppData = {
    testData: 'Hello World!'
};

let AppParams = {
    el: '#addNewComponents',
    data: AppData,
    mounted: function(){
        alert(this.testData)
    }
};

export { AppData, AppParams }