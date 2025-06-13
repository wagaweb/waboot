/// <reference types="vite/client" />

export interface stepCheckoutBackendDataType{
    ajax_url: string,
    nonce: string,
    locale: string,
    current_language: string,
    /*
     * This controls which shipping address is used by default.
     * shipping = Default to customer shipping address (this enables "Ship to a different address?")
     * billing = Default to customer billing address (this enables "Ship to a different address?")
     * billing_only = Force shipping to the customer billing address (this HIDE "Ship to a different address?")
     */
    woocommerce_ship_to_destination: 'shipping' | 'billing' | 'billing_only',
    wc_checkout_registration_required: boolean,
    wc_checkout_registration_enabled: boolean,
    must_show_profile_step: boolean
}

export interface wpJsonResponse{
    data: wpJsonResponseError|any,
    success: boolean
}

export interface wpJsonResponseError{
    code?: string,
    message?: string
}

export interface userProfileData {
    email: string,
    profileType: ''|'company'|'private',
    birthday?: Date,
    fiscalCode?: string,
    company: string
    vatNumber?: string,
    sdiPec?: string
}

export interface userBillingData{
    firstName: string,
    lastName: string,
    phone?: string,
    country: string,
    address1: string,
    address2: string,
    postcode: string,
    city: string,
    state: string,
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
    firstName: string,
    first_name?: string, // Backward compatibility
    lastName: string,
    last_name?: string, // Backward compatibility
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
    profile_data: userProfileData
    shipping_data: addressData
    billing_data: addressData
}

export interface addressData{
    name?: string,
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

