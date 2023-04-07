import cloneDeep from 'lodash/cloneDeep';
import { computed, reactive, ref, Ref, UnwrapRef } from 'vue';
import { GA4 } from './ga4';
import {
  CatalogOrder,
  CatalogQuery,
  Product,
  ProductQuery,
  TaxFilter,
  Term,
  WcserviceClient,
} from './services/api';

export enum LayoutMode {
  Sidebar = 'sidebar',
  Header = 'header',
  Block = 'block',
}

export enum FilterType {
  Checkbox = 'checkbox',
  Permalink = 'permalink',
}

export class CatalogConfig {
  // basic configs
  baseUrl: string;
  apiBaseUrl: string;
  language: string;
  taxonomies: {
    taxonomy: string;
    rewrite: string;
    title?: string;
    enableFilter: boolean;
    type: FilterType;
    selectedTerms: string[];
    selectedParent?: string;
    exclude: string[];
    maxDepth?: number;
    fullOpen: boolean;
  }[];
  productIds: string[];
  searchString?: string;
  ga4: {
    enabled: boolean;
    listId?: string;
    listName?: string;
  };
  // layout
  productsPerPage: number;
  columns: number;
  layoutMode: LayoutMode;
  teleportSidebar?: string;
  enableFilters: boolean;
  enableOrder: boolean;
  enablePriceFilter: boolean;
  showAddToCartBtn: boolean;
  showQuantityInput: boolean;
  pricesIncludeTax: boolean;

  constructor(data: any) {
    if (typeof data !== 'object' || data === null) {
      throw new Error('Invalid config format');
    }

    if (data.baseUrl === undefined) {
      throw new Error('`baseUrl`: parameter is required');
    }
    this.baseUrl = String(data.baseUrl);

    if (data.apiBaseUrl === undefined) {
      throw new Error('`apiBaseUrl`: parameter is required');
    }
    this.apiBaseUrl = String(data.apiBaseUrl);

    if (data.language === undefined) {
      throw new Error('`language`: parameter is required');
    }
    this.language = String(data.language);

    this.taxonomies = [];
    if (Array.isArray(data.taxonomies)) {
      for (const t of data.taxonomies) {
        if (typeof t !== 'object' || t === null) {
          throw new Error('`taxonomies[]`: invalid object');
        }

        if (t.taxonomy === undefined) {
          throw new Error('`taxonomies[].taxonomy`: parameter is required');
        }
        t.taxonomy = String(t.taxonomy);

        if (t.rewrite === undefined) {
          throw new Error('`taxonomies[].rewrite`: parameter is required');
        }
        t.rewrite = String(t.rewrite);

        if (t.title !== undefined) {
          t.title = String(t.title);
        }
        t.enableFilter = Boolean(t.enableFilter ?? true);
        t.type = String(t.type ?? FilterType.Checkbox) as FilterType;
        t.selectedTerms = Array.isArray(t.selectedTerms)
          ? t.selectedTerms.map((id: any) => String(id))
          : [];
        if (t.selectedParent !== undefined) {
          t.selectedParent = String(t.selectedParent);
        }
        t.exclude = Array.isArray(t.exclude)
          ? t.exclude.map((id: any) => String(id))
          : [];
        if (t.maxDepth !== undefined) {
          t.maxDepth = Number(t.maxDepth);
        }
        t.fullOpen = Boolean(t.fullOpen ?? false);

        this.taxonomies.push(t);
      }
    }

    this.productIds = Array.isArray(data.productIds)
      ? data.productIds.map((id: any) => String(id))
      : [];
    if (data.searchString !== undefined) {
      this.searchString = String(data.searchString);
    }

    this.ga4 = { enabled: false };
    if (typeof data.ga4 === 'object' && data.ga4 !== null) {
      if (data.ga4.enabled === undefined) {
        throw new Error('`ga4.enabled`: parameter is required');
      }
      this.ga4.enabled = Boolean(data.ga4.enabled);

      if (data.ga4.listId === undefined) {
        throw new Error(
          '`ga4.listId`: parameter is required when ga4 is enabled',
        );
      }
      this.ga4.listId = String(data.ga4.listId);

      if (data.ga4.listName === undefined) {
        throw new Error(
          '`ga4.listName`: parameter is required when ga4 is enabled',
        );
      }
      this.ga4.listName = String(data.ga4.listName);
    } else if (data.ga4 !== undefined) {
      throw new Error('`ga4`: invalid object');
    }

    this.productsPerPage = Number(data.productsPerPage ?? 24);
    this.columns = Number(data.columns ?? 4);
    this.enableFilters = Boolean(data.enableFilters ?? true);
    this.layoutMode = String(
      data.layoutMode ?? LayoutMode.Sidebar,
    ) as LayoutMode;
    if (data.teleportSidebar !== undefined) {
      this.teleportSidebar = String(data.teleportSidebar);
    }
    this.enableOrder = Boolean(data.enableOrder ?? true);
    this.enablePriceFilter = Boolean(data.enablePriceFilter ?? true);
    this.showAddToCartBtn = Boolean(data.showAddToCartBtn ?? false);
    this.showQuantityInput = Boolean(data.showQuantityInput ?? false);
    this.pricesIncludeTax = Boolean(data.pricesIncludeTax ?? true);
  }
}

type TaxRef = UnwrapRef<{
  options: CatalogConfig['taxonomies'][0];
  terms: Term[];
  flatTerms: Map<Term['id'], Term>;
  selectedTerms: Set<Term['id']>;
  loading: boolean;
}>;

// todo: this is inefficient solution
function getTaxRefSelectTermsExcludingParents(taxRef: TaxRef): string[] {
  const selected = Array.from(taxRef.selectedTerms);
  const parents = new Array<string>();
  for (const tId of selected) {
    const t = taxRef.flatTerms.get(tId);
    if (!t || t.parent === '0') {
      continue;
    }

    parents.push(t.parent);
  }

  return selected.filter(v => !parents.includes(v));
}

export type TaxApplier = (price: number, taxClass: string) => number;

function createTaxApplier(pricesIncludeTax: boolean): TaxApplier {
  if (pricesIncludeTax) {
    return (price: number, _taxClass: string): number => price;
  }

  return (price: number, taxClass: string): number => {
    switch (taxClass) {
      case '':
      case 'Standard':
        return price * 1.22;
      case 'iva-10':
        return price * 1.1;
      case 'iva-0':
      default:
        return price;
    }
  };
}

export function useCatalog(config: CatalogConfig) {
  const client: WcserviceClient = new WcserviceClient(config.apiBaseUrl);
  client.setLanguage(config.language);

  // todo: create tax applier based on configuration and current country
  const taxApplier = createTaxApplier(config.pricesIncludeTax);

  // todo: configure based on language and currency
  const priceFormatter = new Intl.NumberFormat('it-IT', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });

  const ga4 = new GA4(
    config.ga4.listId ?? '',
    config.ga4.listName ?? '',
    taxApplier,
    config.ga4.enabled,
  );

  const products: Ref<Product[]> = ref([]);
  const count: Ref<number> = ref(0);
  const taxRefs: Map<string, TaxRef> = new Map();
  const priceRange: Ref<{ min: number; max: number }> = ref({ min: 0, max: 0 });
  const selectedPriceRange: Ref<{ min: number; max: number } | null> =
    ref(null);
  const order: Ref<CatalogOrder> = ref(CatalogOrder.Default);
  const page: Ref<number> = ref(1);
  const loadingProducts: Ref<boolean> = ref(false);
  const loadingPriceRange: Ref<boolean> = ref(false);
  const loadingMoreProducts: Ref<boolean> = ref(false);
  const loadingCount: Ref<boolean> = ref(false);

  for (const options of config.taxonomies) {
    const taxRef: TaxRef = reactive({
      options: options,
      terms: [],
      flatTerms: new Map(),
      selectedTerms: new Set(),
      loading: false,
    });
    taxRefs.set(options.taxonomy, taxRef);

    if (taxRef.options.exclude && taxRef.options.exclude.length > 0) {
      taxRef.options.enableFilter = false;
    }

    if (
      taxRef.options.selectedTerms &&
      taxRef.options.selectedTerms.length > 0
    ) {
      taxRef.selectedTerms = new Set(taxRef.options.selectedTerms);
    }
  }

  const numberOfPages = computed<number>(() => {
    const n = count.value / config.productsPerPage;
    return isNaN(n) ? 1 : Math.ceil(n);
  });

  const previousPage = computed<number>(() => {
    return (
      page.value - Math.ceil(products.value.length / config.productsPerPage)
    );
  });

  const readQueryString = (): void => {
    const urlSearchParams = new URLSearchParams(window.location.search);
    const p = Number(urlSearchParams.get('page'));
    page.value = isNaN(p) || p <= 0 ? 1 : p;

    const o =
      (urlSearchParams.get('order') as CatalogOrder | null) ??
      CatalogOrder.Default;
    if (Object.values(CatalogOrder).includes(o as CatalogOrder)) {
      order.value = o;
    }

    for (const [tax, taxRef] of taxRefs.entries()) {
      let idsStr = urlSearchParams.get('filter-' + tax);
      if (idsStr === null || idsStr.length === 0) {
        taxRef.selectedTerms = new Set();
        continue;
      }

      const ids = idsStr.split(',');
      taxRef.selectedTerms = new Set(ids);
    }

    let min = Number(urlSearchParams.get('min'));
    min = isNaN(min) ? 0 : min;
    let max = Number(urlSearchParams.get('max'));
    max = isNaN(max) ? 0 : max;
    if (min > 0 && max > 0) {
      selectedPriceRange.value = { min, max };
    } else {
      selectedPriceRange.value = null;
    }
  };

  const setQueryString = (): void => {
    const l = window.location;
    const state: Record<string, any> = {};
    const url = new URL(l.origin + l.pathname);

    if (page.value > 1) {
      state.page = page.value;
      url.searchParams.set('page', page.value.toString());
    }

    if (order.value !== CatalogOrder.Default) {
      state.order = order.value;
      url.searchParams.set('order', order.value);
    }

    for (const [tax, taxRef] of taxRefs.entries()) {
      const ids = getTaxRefSelectTermsExcludingParents(taxRef);
      if (ids.length === 0) {
        continue;
      }

      state[tax] = ids;
      url.searchParams.set('filter-' + tax, ids.join(','));
    }

    const pr = selectedPriceRange.value;
    if (
      pr !== null &&
      (pr.min !== priceRange.value.min || pr.max !== priceRange.value.max)
    ) {
      state.min = pr.min;
      state.max = pr.max;
      url.searchParams.set('min', pr.min.toString());
      url.searchParams.set('max', pr.max.toString());
    }

    history.pushState(state, '', url);
  };

  const getProductQuery = (): ProductQuery => {
    const query: ProductQuery = {
      taxonomies: {},
    };

    if (config.productIds.length > 0) {
      query.ids = config.productIds;
    }

    if (selectedPriceRange.value !== null) {
      query.minPrice = selectedPriceRange.value.min;
      query.maxPrice = selectedPriceRange.value.max;
    }

    if (config.searchString !== undefined) {
      query.title = config.searchString;
      query.searchLogic = 'or';
    }

    for (const [tax, taxRef] of taxRefs.entries()) {
      const filter: TaxFilter = {
        op: 'or',
        terms: getTaxRefSelectTermsExcludingParents(taxRef),
      };
      if (taxRef.options.exclude && taxRef.options.exclude.length > 0) {
        filter.op = 'not';
        filter.terms = taxRef.options.exclude;
        query.taxonomies![tax] = filter;
        continue;
      }

      if (filter.terms.length === 0 && taxRef.options.selectedParent) {
        filter.terms.push(taxRef.options.selectedParent);
      }

      if (filter.terms.length > 0) {
        query.taxonomies![tax] = filter;
      }
    }

    return query;
  };

  const getCatalogQuery = (productQuery: ProductQuery): CatalogQuery => {
    const limit = config.productsPerPage;
    return {
      limit: limit,
      offset: limit * (page.value - 1),
      query: productQuery,
      order: order.value,
      postMetaIn: [],
      taxonomiesIn: ['product_cat'],
    };
  };

  const loadProductCount = async (query: ProductQuery): Promise<void> => {
    loadingCount.value = true;
    count.value = await client.getProductCount(query);
    loadingCount.value = false;
  };

  const loadProducts = async (query: CatalogQuery): Promise<void> => {
    loadingProducts.value = true;
    products.value = await client.findProducts(query);
    loadingProducts.value = false;

    ga4.viewItemList(
      Array.from(products.value.entries()).map(([idx, p]) => ({ idx, p })),
    );
  };

  const loadMoreProducts = async (query: CatalogQuery): Promise<void> => {
    loadingMoreProducts.value = true;
    const newProducts = await client.findProducts(query);
    products.value = products.value.concat(newProducts);
    loadingMoreProducts.value = false;

    ga4.viewItemList(
      Array.from(newProducts.entries()).map(([idx, p]) => ({
        idx: products.value.length + idx,
        p,
      })),
    );
  };

  const loadLessProducts = async (query: CatalogQuery): Promise<void> => {
    if (previousPage.value <= 0) {
      return;
    }

    query = cloneDeep(query);
    loadingMoreProducts.value = true;
    const startIndex = config.productsPerPage * (previousPage.value - 1);
    query.offset = startIndex;
    const newProducts = await client.findProducts(query);
    products.value.unshift(...newProducts);
    loadingMoreProducts.value = false;

    ga4.viewItemList(
      Array.from(newProducts.entries()).map(([idx, p]) => ({
        idx: startIndex + idx,
        p,
      })),
    );
  };

  const loadTaxonomy = async (
    tax: string,
    query: ProductQuery,
  ): Promise<void> => {
    const taxRef = taxRefs.get(tax);
    if (taxRef === undefined) {
      console.warn(`taxonomy \`${tax}\` does not exists`);
      return;
    }

    if (taxRef.options.enableFilter === false) {
      return;
    }

    taxRef.loading = true;
    const q = cloneDeep(query);
    if (q.taxonomies) {
      delete q.taxonomies[tax];
    }

    taxRef.terms = await client.findTaxonomyTermsHierarchically(tax, {
      productQuery: q,
      parent: taxRef.options.selectedParent,
    });

    // populate flat term map
    const populateFlatTermMap = (terms: Term[]) => {
      for (const t of terms) {
        taxRef.flatTerms.set(t.id, t);
        if (t.children.length > 0) {
          populateFlatTermMap(t.children);
        }
      }
    };
    populateFlatTermMap(taxRef.terms);

    taxRef.loading = false;
  };

  const loadAllTaxonomies = async (query: ProductQuery): Promise<void> => {
    const promises: Promise<any>[] = [];
    for (const [tax] of taxRefs.entries()) {
      promises.push(loadTaxonomy(tax, query));
    }

    await Promise.all(promises);
  };

  const loadPriceRange = async (query: CatalogQuery): Promise<void> => {
    loadingPriceRange.value = true;
    const res = await client.getPriceRange(query);
    priceRange.value.min = Math.floor(res.min);
    priceRange.value.max = Math.ceil(res.max);
    if (selectedPriceRange.value === null) {
      selectedPriceRange.value = {
        min: priceRange.value.min,
        max: priceRange.value.max,
      };
    }
    loadingPriceRange.value = false;
  };

  const addToCartHandle = (
    event: { product: Product; selectedId: string; quantity: number },
    index: number,
  ): void => {
    const p = event.product;
    const v = event.selectedId;
    const qty = event.quantity;
    if (p.type === 'variable' && p.id === v) return;
    ga4.addToCart(p, p.id === v ? undefined : v, index, qty);
  };

  const viewDetailsHandle = (
    event: { product: Product },
    index: number,
  ): void => {
    ga4.selectItem(event.product, undefined, index);
  };

  const addToWishlistHandle = (
    event: { product: Product },
    index: number,
  ): void => {
    ga4.addToWishlist(event.product, undefined, index);
  };

  return {
    // refs
    products,
    count,
    taxRefs,
    priceRange,
    selectedPriceRange,
    order,
    page,
    loadingProducts,
    loadingPriceRange,
    loadingMoreProducts,
    loadingCount,
    // computed
    numberOfPages,
    previousPage,
    // methods
    readQueryString,
    setQueryString,
    getProductQuery,
    getCatalogQuery,
    loadProducts,
    loadMoreProducts,
    loadLessProducts,
    loadProductCount,
    loadPriceRange,
    loadTaxonomy,
    loadAllTaxonomies,
    addToCartHandle,
    viewDetailsHandle,
    addToWishlistHandle,
    // data
    priceFormatter,
    taxApplier,
  };
}
