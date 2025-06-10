/// <reference types="vite/client" />

export interface stepCheckoutBackendDataType{
    ajax_url: string,
    nonce: string,
    locale: string,
    current_language: string
}

export interface wpJsonResponse{
    data: wpJsonResponseError|any,
    success: boolean
}

export interface wpJsonResponseError{
    code?: string,
    message?: string
}

export interface userBillingData{
    profileType: ''|'company'|'private',
    email: string,
    firstName: string,
    lastName: string,
    country: string,
    address1: string,
    address2: string,
    postcode: string,
    city: string,
    state: string,
    fiscalCode?: string,
    company: string,
    vatNumber?: string,
    sdiPec?: string,
    phone?: string,
    birthday?: Date,
}

export interface userShippingData{
    name: string,
    firstName: string,
    lastName: string,
    phone?: string,
    country: string,
    address1: string,
    address2: string,
    postcode: string,
    city: string,
    state: string,
    notes: string
}

export interface userShippingDataWP{
    name: string,
    first_name: string,
    last_name: string,
    phone?: string,
    country: string,
    address1: string,
    address2: string,
    postcode: string,
    city: string,
    state: string,
    notes: string
}

export interface fetchedUserData{
    is_logged_in: boolean,
    id: number,
    profile_data: userProfileData & {email: string}
    shipping_data: userShippingData
    billing_data: userBillingData
}

export interface addressData{
    name: string,
    firstName: string,
    lastName: string,
    phone?: string,
    country: string,
    address1: string,
    address2: string,
    postcode: string,
    city: string,
    state: string,
    notes?: string
}

export interface fetchedCountries{
    allowed_countries: fetchedCountry[],
    shipping_countries: fetchedCountry[]
}

export interface fetchedCountry{
    slug: string,
    label: string
}

