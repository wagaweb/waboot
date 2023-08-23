/// <reference types="vite/client" />

export interface stepCheckoutBackendDataType{
    ajax_url: string,
    nonce: string
}
export interface wpJsonResponse{
    data: any,
    success: boolean
}

export interface userProfileData{
    'email': string,
    'firstName': string,
    'lastName': string,
    'birthDay': string,
    'phone': string,
}

export interface userShippingData{
    'country': string,
    'address': string,
    'postcode': string,
    'city': string,
    'state': string,
    'notes': string,
}

