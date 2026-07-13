import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useCheckoutDataStore } from "@/stores/checkoutData.ts";

// @see: https://pinia.vuejs.org/core-concepts/#Setup-Stores
export const useBreadCrumbStore = defineStore('breadcrumb', () => {
    const checkoutDataStore = useCheckoutDataStore();
    const currentStep = ref(1)
    const maxStep = 3;

    checkoutDataStore.$subscribe((mutation, state) => {
        switch (state.currentStep) {
            case 'email':
            case 'password':
                goToNamedStep('login');
                break;
            case 'profile':
                goToNamedStep('profile')
                break;
            case 'address':
                goToNamedStep('addresses');
                break;
            case 'pay':
                goToNamedStep('payment');
                break;
        }
    });

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
            case 'profile':
                currentStep.value = 2;
                break;
            case 'addresses':
                currentStep.value = 3;
                break;
            case 'payment':
                currentStep.value = 4;
                break;
        }
    }

    return { currentStep, nextStep, previousStep, goToNamedStep }
})
