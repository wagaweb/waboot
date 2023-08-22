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

  return { currentStep, nextStep, previousStep }
})
