import {getBackEndData} from "@/services/wp/backendData.ts";
import type {fetchedUserData, userShippingDataWP, wpJsonResponse} from "../../../env";
import {getDateFromBackendFormat} from "@/utils/helpers/dates.ts";

export const wpUserAPI = {
    fetchUser,
    signIn,
    fetchShippingAddresses,
    checkIfEmailIsRegistered
}

const baseUrl = getBackEndData().ajax_url;

async function fetchUser(): Promise<fetchedUserData> {
    const data = new FormData();

    data.append('action','retrieve_user');
    data.append( 'nonce', getBackEndData().nonce );

    const res = await fetch(baseUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: data
    });
    const resJson: wpJsonResponse = await res.json();
    if(resJson.success){
        if('profile_data' in resJson.data){
            if('birthDay' in resJson.data.profile_data){
                if(resJson.data.profile_data.birthDay !== ''){
                    resJson.data.profile_data.birthDay = getDateFromBackendFormat(resJson.data.profile_data.birthDay);
                }
            }
        }
        return resJson.data;
    }else if(resJson.data.error){
        throw new Error('fetchUser(): '+resJson.data.error);
    }
    throw new Error('fetchUser(): unrecognized WordPress response');
}

async function fetchShippingAddresses(userId: number): Promise<userShippingDataWP[]> {
    const data = new FormData();

    data.append('action','retrieve_shipping_addresses');
    data.append('user_id', String(userId));
    data.append( 'nonce', getBackEndData().nonce );

    const res = await fetch(baseUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: data
    });
    const resJson: wpJsonResponse = await res.json();
    if(resJson.success){
        return resJson.data;
    }else if(resJson.data.error){
        throw new Error('fetchShippingAddresses(): '+resJson.data.error);
    }
    throw new Error('fetchShippingAddresses(): unrecognized WordPress response');
}

async function signIn(email: string, password: string): Promise<fetchedUserData> {
    const data = new FormData();

    data.append('action','signin_user_by_email_and_password');
    data.append('email',email);
    data.append('password',password);
    data.append( 'nonce', getBackEndData().nonce );

    const res = await fetch(baseUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: data
    });
    const resJson: wpJsonResponse = await res.json();
    if(resJson.success){
        return resJson.data;
    }else if(resJson.data.error){
        throw new Error(resJson.data.error);
    }
    throw new Error('signInUser(): unrecognized WordPress response');
}

async function checkIfEmailIsRegistered(email: string): Promise<boolean> {
    const data = new FormData();

    data.append('action','is_email_registered');
    data.append('email',email);
    data.append( 'nonce', getBackEndData().nonce );

    const res = await fetch(baseUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: data
    });
    const resJson: wpJsonResponse = await res.json();
    if(resJson.success){
        return resJson.data.is_email_registered;
    }else if(resJson.data.error){
        throw new Error('checkIfEmailIsRegistered(): '+resJson.data.error);
    }
    throw new Error('checkIfEmailIsRegistered(): unrecognized WordPress response');
}