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

export interface fetchedUserData{
    is_logged_in: boolean,
    profile_data: userProfileData
    shipping_data: userShippingData
    billing_data: userShippingData
}

export interface fetchedCountries{
    allowed_countries: fetchedCountry[],
    shipping_countries: fetchedCountry[]
}

export interface fetchedCountry{
    slug: string,
    label: string
}

