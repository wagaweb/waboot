import { ref, reactive } from 'vue'
import { defineStore } from 'pinia'
import type {userProfileData, userShippingData} from "../../env";

export const useCurrentUserStore = defineStore('currentUser', () => {
    const isLoggedIn = ref(false);
    const mustRegisterNewUser = ref(false);
    const newAccountData = reactive({
        'password': ''
    });
    const profileData: userProfileData = reactive({
        'email': '',
        'firstName': '',
        'lastName': '',
        'birthDay': '',
        'phone': '',
    });
    const shippingData: userShippingData = reactive({
        'country': '',
        'address': '',
        'postcode': '',
        'city': '',
        'state': '',
        'notes': '',
    });

    function setLoggedIn(){
        isLoggedIn.value = true;
    }

    function setMustRegisterNewUser(){
        mustRegisterNewUser.value = true;
    }

    function setUserPassword(password: string){
        newAccountData.password = password;
    }

    function setProfileData(newProfileData: userProfileData){
        if(newProfileData.firstName !== undefined){
            profileData.firstName = newProfileData.firstName;
        }
        if(newProfileData.lastName !== undefined){
            profileData.lastName = newProfileData.lastName;
        }
        if(newProfileData.birthDay !== undefined){
            profileData.birthDay = newProfileData.birthDay;
        }
        if(newProfileData.phone !== undefined){
            profileData.phone = newProfileData.phone;
        }
    }

    function setShippingData(newShippingData: userShippingData){
        if(newShippingData.country !== undefined){
            shippingData.country = newShippingData.country;
        }
        if(newShippingData.address !== undefined){
            shippingData.address = newShippingData.address;
        }
        if(newShippingData.postcode !== undefined){
            shippingData.postcode = newShippingData.postcode;
        }
        if(newShippingData.city !== undefined){
            shippingData.city = newShippingData.city;
        }
        if(newShippingData.state !== undefined){
            shippingData.state = newShippingData.state;
        }
        if(newShippingData.notes !== undefined){
            shippingData.notes = newShippingData.notes;
        }
    }

    function setEmail(email: string){
        profileData.email = email;
    }

    return { isLoggedIn, profileData, shippingData, setLoggedIn, setProfileData, setEmail, setShippingData, mustRegisterNewUser, setMustRegisterNewUser, newAccountData, setUserPassword}
})