/// <reference types="vite/client" />

export interface stepCheckoutBackendDataType{
    ajax_url: string,
    nonce: string
}

export interface wpJsonResponse{
    data: any,
    success: boolean
}
