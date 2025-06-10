import type {stepCheckoutBackendDataType} from "../../../env";

export function getBackEndData(): stepCheckoutBackendDataType {
    //@ts-ignore
    if(typeof stepCheckoutBackendData !== 'undefined'){
        //@ts-ignore
        return stepCheckoutBackendData;
    }
    throw new Error('[getBackEndData()] Unable to find stepCheckoutBackendData');
}