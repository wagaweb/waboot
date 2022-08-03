<template>
    <div class="loading" v-if="loading">
        <h1>New Rule</h1>
        <p>Loading...</p>
    </div>
    <div id="edit-rule" v-else>
        <div class="shop-rule__title">
          <h1>New Rule</h1>
          <button class="button" v-on:click="goBack" :disabled="savingRule">Go Back</button>
          <button class="button button-primary" v-on:click="saveRule" :disabled="savingRule">Save</button>
        </div>
        <div class="shop-rule__success_notice mb notice notice-success" v-if="shopRuleSavedSuccessfully">
            <p>Shop Rule saved successfully!</p>
        </div>
        <div class="shop-rule__fail_notice mb notice notice-error" v-for="notice in failNotices">
            <p>{{ notice }}</p>
        </div>
        <div class="shop-rule__panel wp-filter">
            <div class="shop-rule__form">
                <label>
                    <span class="shop-rule__label">Title</span>
                    <input type="text" v-model="editingData.title" />
                </label>
                <label>
                    <span class="shop-rule__label">Enabled</span>
                    <input type="checkbox" v-model="editingData.enabled" />
                </label>
                <label>
                    <span class="shop-rule__label">Choose start and end date</span>
                    <Datepicker class="shop-rule__calendar" range v-model="dates"></Datepicker>
                </label>
                <label v-show="false">
                    <span class="shop-rule__label">Choose dates timezone</span>
                    <select v-model="editingData.timezone">
                        <option value="Europe/Rome">Europe/Rome</option>
                    </select>
                </label>
                <label>
                    <span class="shop-rule__label">Choose a rule type</span>
                    <select v-model="editingData.type">
                        <option value=""></option>
                        <option :value="t.slug" v-for="t in pageData.shop_rule_types">{{ t.label }}</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="shop-rule__panel wp-filter mb">
          <div class="shop-rule__panel-heading">
            <h2>Product Conditions</h2>
            <button class="button" v-on:click="addTaxonomyFilter">Add condition</button>
          </div>
            <div class="shop-rule__form shop-rule__form--stacked" :key="index" v-for="(taxFilter, index) in editingData.taxFilters">
                <label>
                    <span class="shop-rule__label">Taxonomy</span>

                    <select v-model="editingData.taxFilters[index].taxonomy" @change="onFilterTaxonomyChange(index)">
                        <option :value="taxonomy.name" v-for="taxonomy in pageData.taxonomies">{{ taxonomy.label }}</option>
                    </select>
                </label>

                <label>
                    <span class="shop-rule__label">Criteria</span>

                    <select v-model="editingData.taxFilters[index].criteria">
                        <option value="in">In</option>
                        <option value="not-in">Not in</option>
                    </select>
                </label>

                <label>
                    <span class="shop-rule__label">At least one?</span>
                    <input type="checkbox" v-model="editingData.taxFilters[index].atLeastOne" />
                </label>

                <label>
                  <span class="shop-rule__label">Terms</span>
                  <vSelect class="shop-rule__select" multiple :ref="'termSelector_'+index" label="name" :options="termSelectItems[index]" :reduce="term => term.code" :value="taxFilter.terms" v-model="editingData.taxFilters[index].terms"></vSelect>
                </label>
                <button class="button button-danger" v-on:click="removeTaxonomyFilter(index)">X</button>
            </div>
        </div>

        <template v-if="editingData.type === 'join-taxonomy'">
            <div class="shop-rule__panel wp-filter mb">
              <div class="shop-rule__panel-heading">
                <h2>Actions</h2>
              </div>

                <p>Select the term to assign to the products that met "Products Conditions".</p>

                <div class="shop-rule__form shop-rule__form--stacked">
                    <label>
                        <span class="shop-rule__label">Taxonomy</span>
                        <select v-model="editingData.joinTaxonomy.taxonomy" @change="onJoinTaxonomyChange()">
                            <option value="">Choose a taxonomy</option>
                            <option :value="taxonomy.name" v-for="taxonomy in pageData.taxonomies">{{ taxonomy.label }}</option>
                        </select>
                    </label>

                    <label>
                      <span class="shop-rule__label">Terms</span>
                      <vSelect v-show="editingData.joinTaxonomy.taxonomy !== ''" class="shop-rule__select shop-rule__select--searchable" ref="joinTaxonomyTermSelector" label="name" :options="joinTaxonomyItems" :reduce="term => term.code" v-model="editingData.joinTaxonomy.term"></vSelect>
                    </label>
                </div>
            </div>
        </template>

        <template v-if="editingData.type === 'buy-x-get-y'">
            <div class="shop-rule__panel wp-filter mb">
              <div class="shop-rule__panel-heading">
                <h2>Actions</h2>
                <button @click="addBuyXGetYProduct" class="button">Add product</button>
              </div>
                <p>Gift the product with the lower price</p>

                <input type="checkbox" v-model="editingData.giftLowerPricedMatchedProduct" />

                <p>Or</p>

                <p>Select one or more products to add to customer cart when "Products Conditions" are met by products in the cart and "Order Conditions" are met by the cart itself.</p>

                <template v-for="(product, productIndex) in editingData.products">
                    <div class="buyXGetYProduct shop-rule__form shop-rule__form--stacked">
                        <label v-show="!loadingProducts">
                            <span class="shop-rule__label">Select a product by SKU (start type for suggestions)</span>
                            <vSelect class="shop-rule__select" ref="buyXGetYProductsSelector" label="sku" :options="pageData.products" :reduce="product => product.id" v-model="editingData.products[productIndex].id" :dropdown-should-open="buyXGetYProductsSelectorShouldOpen"></vSelect>
                        </label>

                        <label>
                            <span class="shop-rule__label">Quantity</span>
                            <input type="number" v-model="editingData.products[productIndex].quantity">
                        </label>
                        <button class="button button-danger" v-on:click="editingData.products.splice(productIndex, 1)">X</button>
                    </div>
                </template>
            </div>
        </template>

        <template v-if="editingData.type === 'buy-x-get-y' || editingData.type === 'cart-adjustment'">
            <div class="shop-rule__panel wp-filter">
              <div class="shop-rule__panel-heading">
                <h2>Order Conditions</h2>
              </div>

                <div class="shop-rule__form">
                    <label>
                        <span class="shop-rule__label">Min. Order total</span>
                        <input type="number" v-model="editingData.minOrderTotal" />
                    </label>
                    <label>
                        <span class="shop-rule__label">Max. Order total</span>
                        <input type="number" v-model="editingData.maxOrderTotal" />
                        <small>Set value to 0 to ignore this setting</small>
                    </label>
                    <label>
                        <span class="shop-rule__label">Calculates the total only between matched products</span>
                        <input type="checkbox" v-model="editingData.calculatesTotalOnlyBetweenMatchedProducts" />
                    </label>
                    <label>
                        <span class="shop-rule__label">Min. Matched products</span>
                        <input type="number" v-model="editingData.minMatchedProductCount" />
                    </label>
                    <label>
                        <span class="shop-rule__label">Count Matched Products Once</span>
                        <input type="checkbox" v-model="editingData.countMatchedProductsOnce" />
                    </label>
                </div>
            </div>
        </template>
        <div class="shop-rule__panel wp-filter" v-if="mustShowRuleOptionsTab">
          <div class="shop-rule__panel-heading">
            <h2>Rule Options</h2>
          </div>
            <div class="shop-rule__form">
                <template v-if="editingData.type === 'buy-x-get-y'">
                    <label>
                        <span class="shop-rule__label">Required customer role</span>
                        <vSelect class="shop-rule__select" multiple ref="customer_role_selector" label="label" :options="pageData.user_roles" :reduce="role => role.label" v-model="editingData.allowedRole"></vSelect>
                    </label>
                    <label>
                        <span class="shop-rule__label">Choose the message to display</span>
                        <textarea v-model="editingData.productPageMessage">{{ editingData.productPageMessage || '' }}</textarea>
                    </label>
                    <label>
                        <span class="shop-rule__label">Choose the layout to render in single product page</span>
                        <select v-model="editingData.productPageMessageLayout">
                            <option :selected="editingData.productPageMessageLayout === layout.slug" :value="layout.slug" v-for="layout in pageData.product_page_message_layouts">{{ layout.label }}</option>
                        </select>
                    </label>
                    <label>
                        <span class="shop-rule__label">Choose an add to cart criteria</span>

                        <select v-model="editingData.addToCartCriteria">
                            <option :value="criteria.slug" v-for="criteria in pageData.add_to_cart_criteria">{{ criteria.label }}</option>
                        </select>
                    </label>
                    <label v-show="editingData.addToCartCriteria === 'choice'">
                        <span class="shop-rule__label">Maximum number of product customer is allowed to choose</span>
                        <input type="number" v-model="editingData.maxNumberOfProductsToChoose">
                    </label>
                    <label v-show="editingData.addToCartCriteria === 'choice'">
                        <span class="shop-rule__label">Recursive gifted product for matched products</span>
                        <select v-model="editingData.choiceCriteria">
                            <option :selected="editingData.choiceCriteria === choice_criteria.slug" :value="choice_criteria.slug" v-for="choice_criteria in pageData.choice_criteria">{{ choice_criteria.label }}</option>
                        </select>
                    </label>
                </template>
                <template v-if="editingData.type === 'sale'">
                    <label>
                        <span class="shop-rule__label">Sale value</span>
                        <input type="number" v-model="editingData.saleValue" />
                    </label>
                    <label>
                        <span class="shop-rule__label">Choose a sale type</span>

                        <select v-model="editingData.saleType">
                            <option :value="criteria.slug" v-for="criteria in pageData.sale_type">{{ criteria.label }}</option>
                        </select>
                    </label>
                    <label>
                        <span class="shop-rule__label">Choose a sale criteria</span>

                        <select v-model="editingData.saleCriteria">
                            <option :value="criteria.slug" v-for="criteria in pageData.sale_criteria">{{ criteria.label }}</option>
                        </select>
                    </label>
                </template>
                <template v-if="editingData.type === 'cart-adjustment'">
                    <label>
                        <span class="shop-rule__label">Discount type</span>
                        <select v-model="editingData.discount.type">
                            <option v-for="t in pageData.discount_type" :value="t.slug" >{{ t.label }}</option>
                        </select>
                    </label>
                    <label>
                        <span class="shop-rule__label">Discount amount</span>
                        <input type="number" v-model="editingData.discount.amount" />
                    </label>
                    <label>
                        <span class="shop-rule__label">Discount label</span>
                        <input type="text" v-model="editingData.discount.label">
                    </label>
                </template>
            </div>
        </div>
    </div>
</template>
<script lang="ts">
//@ts-ignore
import vSelect from 'vue-select';
import {createRule, fetchEditRuleData, fetchEditRuleProductsData, fetchRuleById, saveRule} from '@/services/api';
import { defineComponent, PropType } from 'vue';
//import { DatePicker } from 'v-calendar';
import Datepicker from '@vuepic/vue-datepicker';

export default defineComponent({
    name: 'NewRule',
    components: {
        vSelect,
        Datepicker
    },
    data(): {
        dates: Date[];
        editingData: EditingData;
        pageData: EditRulePageData;
        loading: boolean;
        loadingProducts: boolean;
        savingRule: boolean;
        shopRuleSavedSuccessfully: boolean;
        failNotices: Array<string>;
    } {
        return {
            dates: [],
            editingData: {
                title: null,
                order: 1,
                enabled: null,
                type: null,
                timezone: 'Europe/Rome',
                from: null,
                to: null,
                taxFilters: [],
                discount: {
                    type: 'flat',
                    amount: 0,
                    label: '',
                },
                joinTaxonomy: {
                    taxonomy: '',
                    term: null
                },
                products: [],
                minOrderTotal: 0,
                maxOrderTotal: 0,
                calculatesTotalOnlyBetweenMatchedProducts: false,
                minMatchedProductCount: 1,
                countMatchedProductsOnce: false,
                addToCartCriteria: 'auto',
                maxNumberOfProductsToChoose: 1,
                productPageMessageLayout: 'list',
                productPageMessage: '',
                choiceCriteria: 'per-order',
                allowedRole: [],
                saleCriteria: 'cumulative',
                saleType: 'flat',
                saleValue: 0,
                giftLowerPricedMatchedProduct: false
            },
            pageData: {
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
            },
            loading: false,
            savingRule: false,
            loadingProducts: false,
            shopRuleSavedSuccessfully: false,
            failNotices: []
        }
    },
    computed: {
        termSelectItems(): Array<any>{
            let result = [];
            for(let taxFilter of this.editingData.taxFilters){
                let taxonomy = taxFilter.taxonomy;
                //@ts-ignore
                if(typeof this.pageData.terms[''+taxonomy+''] !== 'undefined'){
                    //@ts-ignore
                    result.push(this.pageData.terms[''+taxonomy+''].map( (termObj: { name: string, term_id: number }) => {
                        return {
                            name: termObj.name,
                            code: termObj.term_id
                        }
                    }));
                }else{
                    result.push([]);
                }
            }
            return result;
        },
        joinTaxonomyItems(): Array<any>{
            let currentSelectedTaxonomy = this.editingData.joinTaxonomy.taxonomy;
            if(currentSelectedTaxonomy === ''){
                return [];
            }
            let results = [];
            //@ts-ignore
            if(typeof this.pageData.terms[''+currentSelectedTaxonomy+''] !== 'undefined'){
                //@ts-ignore
                for(let termObj of this.pageData.terms[''+currentSelectedTaxonomy+'']){
                    results.push(
                        {
                            name: termObj.name,
                            code: termObj.term_id
                        }
                    );
                }
            }
            return results;
        },
        mustShowRuleOptionsTab(): boolean {
            return this.editingData.type == 'buy-x-get-y' || this.editingData.type == 'sale' || this.editingData.type === 'cart-adjustment';
        },
    },
    async mounted() {
        this.loading = true;
        this.loadingProducts = true;
        this.pageData = await fetchEditRuleData();
        this.loading = false;
        this.pageData.products = await fetchEditRuleProductsData();
        this.loadingProducts = false;
    },
    methods: {
        addTaxonomyFilter(){
            let newFilter = {
                taxonomy: '',
                criteria: '',
                atLeastOne: false,
                terms: []
            };
            /*if(this.pageData.taxonomies){
                let firstTax = Object.keys(this.pageData.taxonomies)[0] ?? undefined;
                if(typeof firstTax !== 'undefined'){
                    newFilter = {
                        //@ts-ignore
                        taxonomy: firstTax,
                        criteria: 'in',
                        terms: []
                    };
                }
            }*/
            this.editingData.taxFilters.push(newFilter);
        },
        removeTaxonomyFilter(taxFilterIndex: number){
            let newTaxFilters = this.editingData.taxFilters;
            newTaxFilters.splice(taxFilterIndex,1);
            this.editingData.taxFilters = newTaxFilters;
        },
        addBuyXGetYProduct(){
            this.editingData.products.push({
                id: null,
                quantity: 0,
            });
        },
        onFilterTaxonomyChange(taxFilterIndex: number){
            if(typeof this.$refs['termSelector_'+taxFilterIndex] !== 'undefined'){
                //@ts-ignore
                let vSelectInstance = this.$refs['termSelector_'+taxFilterIndex][0];
                vSelectInstance.updateValue([]);
            }
        },
        onJoinTaxonomyChange(){
            if(typeof this.$refs['joinTaxonomyTermSelector'] !== 'undefined'){
                let vSelectInstance = this.$refs['joinTaxonomyTermSelector'];
                //@ts-ignore
                vSelectInstance.updateValue(null);
            }
        },
        getEditingDataByRuleData(ruleData: ShopRule): EditingData {
            let result = {
                title: ruleData?.name,
                order: ruleData?.order || 1,
                enabled: ruleData?.enabled,
                type: ruleData?.type,
                dates: [],
                dates_utc: [],
                timezone: 'Europe/Rome',
                from: ruleData?.from,
                to: ruleData?.to,
                taxFilters: [],
                joinTaxonomy: {
                    taxonomy: '',
                    term: null
                },
                discount: {
                    type: 'flat',
                    amount: 0,
                    label: '',
                },
                products: [],
                minOrderTotal: ruleData?.minOrderTotal,
                maxOrderTotal: ruleData?.maxOrderTotal,
                calculatesTotalOnlyBetweenMatchedProducts: ruleData?.calculatesTotalOnlyBetweenMatchedProducts,
                minMatchedProductCount: ruleData?.minMatchedProductCount,
                addToCartCriteria: ruleData?.addToCartCriteria,
                maxNumberOfProductsToChoose: ruleData?.maxNumberOfProductsToChoose,
                countMatchedProductsOnce: ruleData?.countMatchedProductsOnce,
                productPageMessageLayout: ruleData?.productPageMessageLayout,
                productPageMessage: ruleData?.productPageMessage,
                choiceCriteria: ruleData?.choiceCriteria,
                allowedRole: ruleData?.allowedRole || [],
                saleCriteria: ruleData?.saleCriteria,
                saleType: ruleData?.saleType,
                saleValue: ruleData?.saleValue,
                giftLowerPricedMatchedProduct: ruleData?.giftLowerPricedMatchedProduct
            };
            return result;
        },
        buyXGetYProductsSelectorShouldOpen(VueSelect: any): boolean {
            /*if (this.editingData.products.length > 0) {
                return VueSelect.open
            }
            return VueSelect.search.length !== 0 && VueSelect.open*/
            return VueSelect.search.length !== 0 && VueSelect.open;
        },
        async saveRule(){
            this.savingRule = true;
            try{
                this.editingData.from = this.dates[0]?.toISOString();
                this.editingData.to = this.dates[1]?.toISOString();
                let newId = await createRule(this.editingData);
                //this.shopRuleSavedSuccessfully = true;
                setTimeout(() => { this.shopRuleSavedSuccessfully = false; this.$store.commit('goToEditView', newId); }, 2000);
            }catch (e){
                this.failNotices.push('Error occurred during saving: '+e);
                setTimeout(() => { this.failNotices = [] }, 2000);
                this.savingRule = false;
            }
        },
        goBack(){
            this.$store.commit('goToListView');
        }
    }
});
</script>
