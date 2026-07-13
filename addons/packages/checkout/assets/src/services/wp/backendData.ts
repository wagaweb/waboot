import type {stepCheckoutBackendDataType} from "../../../env";

export function getBackEndData(): stepCheckoutBackendDataType {
    // @ts-ignore
    if (typeof stepCheckoutBackendData === 'undefined') {
        throw new Error('[getBackEndData()] Unable to find stepCheckoutBackendData');
    }

    // @ts-ignore
    const rawData = typeof stepCheckoutBackendData === 'string' ? JSON.parse(stepCheckoutBackendData) : stepCheckoutBackendData;

    const parsedData = {};

    Object.entries(rawData).forEach(([key, value]) => {
        const processedValue = value === "true" ? true : value === "false" ? false : value;

        //@ts-ignore
        parsedData[key] = processedValue;
    });

    // @ts-ignore
    return parsedData;
}