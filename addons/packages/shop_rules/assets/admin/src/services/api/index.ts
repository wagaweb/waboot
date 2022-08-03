const axios = require('axios').default;
const qs = require('qs'); //https://stackoverflow.com/questions/51085574/wordpress-post-request-fails-with-xmlhttp-and-axios-but-works-with-jquery-what

function getApiEndPoint(): string {
    // @ts-ignore
    if(typeof shopRulesData !== 'undefined'){
        // @ts-ignore
        return shopRulesData.ajax_url;
    }
    return '/wp-admin/ajax-url';
}

export async function fetchRules(): Promise<ShopRule[]> {
    let data = {
        'action': 'get_rules'
    };
    let response = await axios.post(getApiEndPoint(),qs.stringify(data));
    if(response.status === 200){
        let result = response.data;
        if(result.success){
            let rules = result.data;
            return rules;
        }
    }
    return [];
}

export async function fetchEditRuleData(): Promise<EditRulePageData> {
    let results = {
        taxonomies: [],
        terms: [],
        products: [],
        user_roles: [],
        shop_rule_types: [],
        add_to_cart_criteria: [],
        product_page_message_layouts: [],
        sale_criteria: [],
        sale_type: [],
        choice_criteria: [],
        discount_type: [],
    };
    let response = await axios.post(getApiEndPoint(),qs.stringify({
        'action': 'get_edit_data'
    }));
    if(response.status === 200){
        let result = response.data;
        if(result.success){
            results = result.data;
        }
    }
    return results;
}

export async function fetchEditRuleProductsData(): Promise<Array<{id: number, sku: string, type: string, parent: string|null}>> {
    let response = await axios.post(getApiEndPoint(),qs.stringify({
        'action': 'get_products_edit_data'
    }));
    if(response.status === 200){
        let result = response.data;
        if(result.success){
            return result.data;
        }
    }
    return [];
}

/**
 * @param {number} ruleId
 */
export async function fetchRuleById(ruleId: number): Promise<ShopRule|null> {
    let data = {
        'action': 'get_rule',
        'rule_id': ruleId
    };
    let response = await axios.post(getApiEndPoint(),qs.stringify(data));
    if(response.status === 200){
        let result = response.data;
        if(result.success){
            let rule = result.data;
            return rule;
        }
    }
    return null;
}

/**
 *
 * @param ruleData
 * @param ruleId
 */
export async function saveRule(ruleData: EditingData, ruleId: number): Promise<boolean> {
    let data = {
        'action': 'save_rule',
        'rule_id': ruleId,
        'form_data': ruleData
    };
    let response = await axios.post(getApiEndPoint(),qs.stringify(data));
    if(response.status === 200){
        let result = response.data;
        if(result.success){
            return true;
        }
    }
    return false;
}

/**
 *
 * @param ruleData
 */
export async function createRule(ruleData: EditingData): Promise<number> {
    let data = {
        'action': 'create_rule',
        'form_data': ruleData
    };
    let response = await axios.post(getApiEndPoint(),qs.stringify(data));
    if(response.status === 200){
        let result = response.data;
        if(result.success){
            return result.data.rule_id;
        }
    }
    throw new Error('Rule not created');
}

/**
 *
 * @param ruleId
 */
export async function deleteRule(ruleId: number): Promise<boolean> {
    let data = {
        'action': 'delete_rule',
        'rule_id': ruleId
    };
    let response = await axios.post(getApiEndPoint(),qs.stringify(data));
    if(response.status === 200){
        let result = response.data;
        if(result.success){
            return true;
        }
    }
    throw new Error('Rule not deleted');
}