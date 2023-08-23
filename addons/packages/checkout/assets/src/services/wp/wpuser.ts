import type {stepCheckoutBackendDataType, wpJsonResponse} from "../../../env";
import {getBackEndData} from "@/services/wp/backendData";

export class WPUser{
    backendData: stepCheckoutBackendDataType;
    constructor() {
        this.backendData = getBackEndData();
    }

    async checkIfCustomerIsLoggedIn(): Promise<boolean> {
        const data = new FormData();

        data.append('action','is_customer_logged_in');
        data.append( 'nonce', this.backendData.nonce );

        try{
            const res = await fetch(this.backendData.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                body: data
            });
            const resJson: wpJsonResponse = await res.json();
            if(resJson.success){
                return resJson.data.is_logged_in;
            }
            throw new Error('WP Ajax Error');
        }catch (err){
            console.log('[WPUser]');
            console.error(err);
            return false;
        }
    }

    async checkIfEmailIsRegistered(email: string): Promise<boolean> {
        const data = new FormData();

        data.append('action','is_email_registered');
        data.append('email',email);
        data.append( 'nonce', this.backendData.nonce );

        try{
            const res = await fetch(this.backendData.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                body: data
            });
            const resJson: wpJsonResponse = await res.json();
            if(resJson.success){
                return resJson.data.is_email_registered;
            }
            throw new Error('WP Ajax Error');
        }catch (err){
            console.log('[WPUser]');
            console.error(err);
            return false;
        }
    }
}