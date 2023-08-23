import { ref, reactive } from 'vue'
import { defineStore } from 'pinia'

export const useCurrentUserStore = defineStore('currentUser', () => {
    const isLoggedIn = ref(false);
    const profileData = reactive({
        'email': '',
        'firstName': '',
        'lastName': '',
        'birthDay': '',
        'phone': '',
    });
    const shippingData = reactive({
        'country': '',
        'address': '',
        'cap': '',
        'city': '',
        'state': '',
        'notes': '',
    });



    function setLoggedIn(){
        isLoggedIn.value = true;
    }

    return { isLoggedIn, profileData, shippingData, setLoggedIn }
})