interface ShopRule {
    id: number,
    name: string,
    order: number,
    enabled: boolean,
    from: string,
    to: string,
    //@see: https://attacomsian.com/blog/javascript-current-timezone#time-zone--offset
    timezone: string
    type: string,
    taxFilters: Array<any>,
    joinTaxonomy: {
        taxonomy: string,
        term: number
    },
    products: Array<{
        "id": string,
        "quantity": number
    }>,
    discount: {
        type: string;
        amount: number;
        label: string;
    };
    minOrderTotal: number,
    maxOrderTotal: number,
    calculatesTotalOnlyBetweenMatchedProducts: boolean,
    minMatchedProductCount: number,
    countMatchedProductsOnce: boolean,
    addToCartCriteria: string,
    maxNumberOfProductsToChoose: number,
    productPageMessageLayout: string,
    productPageMessage: string,
    choiceCriteria: string,
    allowedRole: Array<string>
    saleValue: number
    saleType: string,
    saleCriteria: string,
    giftLowerPricedMatchedProduct: boolean
}

interface EditingData{
    title: string|null,
    order: number
    enabled: boolean|null,
    type: string|null,
    //@see: https://attacomsian.com/blog/javascript-current-timezone#time-zone--offset
    timezone: string|null,
    from: string|null,
    to: string|null,
    taxFilters: Array<any>
    joinTaxonomy: {
        taxonomy: string,
        term: number|null
    },
    products: Array<{
        "id": string|null,
        "quantity": number
    }>,
    discount: {
        type: string;
        amount: number;
        label: string;
    };
    minOrderTotal: number|null,
    maxOrderTotal: number|null,
    calculatesTotalOnlyBetweenMatchedProducts: boolean|null,
    minMatchedProductCount: number|null,
    countMatchedProductsOnce: boolean|null,
    addToCartCriteria: string|null,
    maxNumberOfProductsToChoose: number|null,
    productPageMessageLayout: string|null,
    productPageMessage: string|null,
    choiceCriteria: string|null,
    allowedRole: Array<string>|null
    saleValue: number|null
    saleType: string|null,
    saleCriteria: string|null,
    giftLowerPricedMatchedProduct: boolean
}

interface EditRulePageData {
    taxonomies: Array<object>,
    terms: Array<object>,
    user_roles: Array<{
        slug: string,
        label: string
    }>,
    products: Array<{
        id: number,
        sku: string,
        type: string,
        parent: string|null
    }>,
    shop_rule_types: Array<{
        slug: string,
        label: string
    }>,
    add_to_cart_criteria: Array<{
        slug: string,
        label: string
    }>,
    product_page_message_layouts: Array<{
        slug: string,
        label: string
    }>,
    sale_criteria: Array<{
        slug: string,
        label: string
    }>,
    sale_type: Array<{
        slug: string,
        label: string
    }>,
    choice_criteria: Array<{
        slug: string,
        label: string
    }>
    discount_type: Array<{
        slug: string,
        label: string
    }>
}