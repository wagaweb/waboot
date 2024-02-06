import type {fetchedCountries, stepCheckoutBackendDataType, wpJsonResponse} from "../../../env";
import {getBackEndData} from "@/services/wp/backendData";

export class WooCommerce{
    backendData: stepCheckoutBackendDataType;
    constructor() {
        this.backendData = getBackEndData();
    }

    async fetchCountries(): Promise<fetchedCountries> {
        const data = new FormData();

        data.append('action','fetch_store_countries');
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
}