import $ from 'jquery';

class GeneratorsHandler{
    constructor($form,endpoint,action){
        this.$form = $form;
        this.ajax_endpoint = endpoint;
        this.ajax_action = action;
        this.bindForm();
    }
    
    bindForm(){
        let $form_submit_button = this.$form.find("button[type='submit']");
        this.$form.on("submit", (e) => {
            e.preventDefault();
            //Disable button
            $form_submit_button.attr("disabled",true);
            let data = {
                'generator': this.$form.find("input[name='generator']").val(),
                'step': wbData.generators_first_step_slug
            };
            this.handleGenerator(data).then((status) => {
                $form_submit_button.attr("disabled",false);
            });
        });
    }

    handleGenerator(data){
        return $.ajax({
            url: this.ajax_endpoint,
            data: {
                action: this.ajax_action,
                params: data
            },
            method: "POST",
            dataType: "JSON"
        }).then((result,textStatus,jqx) => {
            debugger;
            switch(result.data.status){
                case "run":
                    return this.handleGenerator({
                        'generator': result.data.generator,
                        'step': result.data.next_step,
                        'action': result.data.next_action
                    });
                    break;
                case "complete":
                    return "complete";
                    break;
            }
        })
    }
}

export { GeneratorsHandler }