import $ from 'jquery';
import _ from 'underscore';

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
            //Update status
            this.updateStatus('Processing...',0,wbData.generators_steps.length);
            //Go!
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
                    this.updateStatus('Progressing...',_.indexOf(wbData.generators_steps,result.data.next_step),wbData.generators_steps.length);
                    return this.handleGenerator({
                        'generator': result.data.generator,
                        'step': result.data.next_step,
                        'action': result.data.next_action
                    });
                    break;
                case "complete":
                    this.updateStatus('Completed!',wbData.generators_steps.length,wbData.generators_steps.length);
                    return "complete";
                    break;
            }
        })
    }

    updateStatus(step_message,current,total){
        let progress_tpl = _.template($("#progress-tpl").html()),
            $progress_wrapper = $("#progress-status"),
            percentage = Math.ceil(( current / total ) * 100);

        let progress_html = progress_tpl({
            'step_message': step_message,
            'current_percentage': percentage,
            'total': total
        });

        $progress_wrapper.html(progress_html);
    }
}

export { GeneratorsHandler }