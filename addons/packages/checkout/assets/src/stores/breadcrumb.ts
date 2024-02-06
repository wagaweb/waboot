import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useBreadCrumbStore = defineStore('breadcrumb', () => {
    const currentStep = ref(1)
    const maxStep = 3;

    function nextStep() {
        if(currentStep.value < maxStep){
            currentStep.value++
        }
    }

    function previousStep(){
        if(currentStep.value > 1){
            currentStep.value--;
        }
    }

    function goToNamedStep(step: string){
        switch (step){
            case 'login':
                currentStep.value = 1;
                break;
            case 'addresses':
                currentStep.value = 2;
                break;
            case 'payment':
                currentStep.value = 3;
                break;
        }
    }

    return { currentStep, nextStep, previousStep, goToNamedStep }
})
