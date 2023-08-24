import type {fetchedUserData, stepCheckoutBackendDataType, wpJsonResponse} from "../../../env";
import {getBackEndData} from "@/services/wp/backendData";

export class WPUser{
    backendData: stepCheckoutBackendDataType;
    constructor() {
        this.backendData = getBackEndData();
    }

    async fetchUser(): Promise<fetchedUserData> {
        const data = new FormData();

        data.append('action','retrieve_user');
        data.append( 'nonce', this.backendData.nonce );

        const res = await fetch(this.backendData.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        });
        const resJson: wpJsonResponse = await res.json();
        if(resJson.success){
            return resJson.data;
        }else if(resJson.data.error){
            throw new Error('fetchUser(): '+resJson.data.error);
        }
        throw new Error('fetchUser(): unrecognized WordPress response');
    }

    async signInUser(email: string, password: string): Promise<fetchedUserData> {
        const data = new FormData();

        data.append('action','signin_user_by_email_and_password');
        data.append('email',email);
        data.append('password',password);
        data.append( 'nonce', this.backendData.nonce );

        const res = await fetch(this.backendData.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        });
        const resJson: wpJsonResponse = await res.json();
        if(resJson.success){
            return resJson.data;
        }else if(resJson.data.error){
            throw new Error('signInUser(): '+resJson.data.error);
        }
        throw new Error('signInUser(): unrecognized WordPress response');
    }

    async checkIfEmailIsRegistered(email: string): Promise<boolean> {
        const data = new FormData();

        data.append('action','is_email_registered');
        data.append('email',email);
        data.append( 'nonce', this.backendData.nonce );

        const res = await fetch(this.backendData.ajax_url, {
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
}