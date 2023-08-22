import { ref, reactive } from 'vue'
import { defineStore } from 'pinia'

export const useCurrentUserStore = defineStore('currentUser', () => {
    const isLoggedIn = ref(false);
    const profileData = reactive({
        'email': '',
        'firstName': '',
        'lastName': '',
        'birthDate': '',
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

    return { isLoggedIn, profileData, shippingData }
})