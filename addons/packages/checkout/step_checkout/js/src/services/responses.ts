import { debugLog } from '@/utils/helpers/debug';
import { AxiosError } from 'axios';

export class APIError extends Error{
    source: string;
    data: Record<any, any>|null;

    constructor(message: string, source: string, data: Record<any, any>|null = null) {
        super(message);
        this.source = source;
        this.data = data;
    }
}

// https://engineering.udacity.com/handling-errors-like-a-pro-in-typescript-d7a314ad4991
export function handleError(error: any, source: string){
    debugLog(source+' ERROR', error);
    if(error instanceof Error){
        throw new APIError(error.message,source);
    }else if(error instanceof AxiosError){
        throw new APIError(error.message,source);
    }else{
        if(typeof error === 'object' && error.hasOwnProperty('code')){
            // This is a recognized error from API, can be something like:
            /*
            {
                "code": "invalid_credentials",
                "message": "Provided credentials are not valid",
                "data": null
            }
            */
            if(error.hasOwnProperty('data')){
                throw new APIError(error.code,source,error.data);
            }else{
                throw new APIError(error.code,source);
            }
        }
        throw new APIError(error,source);
    }
}

export function getErrorMessage(error: any){
    if(typeof error === 'string'){
        return error;
    }
    if(error instanceof Error || error instanceof AxiosError){
        return error.message;
    }else{
        if(typeof error === 'object' && error.hasOwnProperty('code')){
            // This is a recognized error from API, can be something like:
            /*
            {
                "code": "invalid_credentials",
                "message": "Provided credentials are not valid",
                "data": null
            }
            */
            if(error.hasOwnProperty('message')){
                return error.message;
            }else{
                return error.code;
            }
        }
    }
    return 'Unknow error message';
}

export function handleResponse(response: any, cache: boolean = false, cacheKey: string|null = null): object {
    // https://www.npmjs.com/package/axios#response-schema
    if(typeof response !== 'object'){
        debugLog('handleResponse() ERROR', response);
        throw new APIError('Unrecognized response', 'handleResponse()');
    }
    if(!response.hasOwnProperty('data')){
        debugLog('handleResponse() ERROR', response);
        throw new APIError('Unrecognized response (no data)', 'handleResponse()');
    }
    if(cache && cacheKey){
        debugLog('handleResponse() CACHING', response.data);
        sessionStorage.setItem(cacheKey,JSON.stringify(response.data));
    }
    const responseData = response.data;
    if(responseData.hasOwnProperty('success')){
        //Then it's a WP response
        if(!responseData.success){
            if(responseData.data.hasOwnProperty('message')){
                throw new APIError(responseData.data.message, 'handleResponse()');
            }
        }
        return responseData.data;
    }
    return response.data;
}