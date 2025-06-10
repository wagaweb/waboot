import type {fetchedCountries, fetchedCountry, stepCheckoutBackendDataType, wpJsonResponse} from "../../../env";
import {getBackEndData} from "@/services/wp/backendData";

export const wcAPI = {
    fetchCountries,
    fetchStates
}

const baseUrl = getBackEndData().ajax_url;

async function fetchStates(country: string): Promise<fetchedCountry[]> {
    const data = new FormData();

    data.append('action','fetch_store_states');
    data.append('country',country);
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
        throw new Error('fetchStates(): '+resJson.data.error);
    }
    throw new Error('fetchStates(): unrecognized WordPress response');
}

async function fetchCountries(): Promise<fetchedCountry[]> {
    const data = new FormData();

    data.append('action','fetch_store_countries');
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
        throw new Error('fetchCountries(): '+resJson.data.error);
    }
    throw new Error('fetchCountries(): unrecognized WordPress response');
}