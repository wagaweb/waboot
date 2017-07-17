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
            this.updateStatus(wbData.generators_labels.processing,0,wbData.generators_steps.length);
            //Go!
            let data = {
                'generator': this.$form.find("input[name='generator']:checked").val(),
                'step': wbData.generators_first_step_slug,
                'action': null
            };
            $("#setup-finish-btn").remove();
            this.handleGenerator(data).then((status) => {
                if(status === "complete"){
                    let finish_btn_tpl = _.template($("#finish-tpl").html()),
                        finish_btn = finish_btn_tpl();
                    $(finish_btn).insertBefore($form_submit_button);
                }
                $form_submit_button.attr("disabled",false);
                $form_submit_button.addClass("run-once",false);
                $form_submit_button.html(wbData.generators_labels.rerun_wizard);
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
            switch(result.data.status){
                case "failed":
                    this.updateStatus(result.data.message,wbData.generators_steps.length,wbData.generators_steps.length);
                    return "failed";
                    break;
                case "run":
                    this.updateStatus(wbData.generators_labels.processing,_.indexOf(wbData.generators_steps,result.data.next_step),wbData.generators_steps.length);
                    return this.handleGenerator({
                        'generator': result.data.generator,
                        'step': result.data.next_step,
                        'action': result.data.next_action
                    });
                    break;
                case "complete":
                    this.updateStatus(wbData.generators_labels.completed,wbData.generators_steps.length,wbData.generators_steps.length);
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