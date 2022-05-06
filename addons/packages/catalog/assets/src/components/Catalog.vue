<template>
  <div class="catalog" :class="`catalog--layout-${config.layoutMode}`">
    <Spinner v-if="loadingCatalog"></Spinner>
    <template v-else-if="products.length > 0">
      <div
        ref="sidebar"
        class="catalog-filters"
        :class="{ 'catalog-filters--opened': sidebarOpen }"
      >
        <a class="catalog-filters__close" @click="sidebarOpen = false">
          <i class="far fa-times"></i>
        </a>
        <div
          v-if="config.layoutMode === 'sidebar'"
          class="catalog-filters__inner catalog-filters__inner--sidebar"
        >
          <div
            v-if="config.enablePriceFilter"
            class="catalog__filter catalog__filter--price"
          >
            <div class="filter">
              <h4 class="filter__title">
                {{ $t('price') }}
              </h4>
              <div class="filter__dropdown">
                <PriceRangeSlider
                  :min="priceRange.min"
                  :max="priceRange.max"
                  :selectedMin="selectedPriceRange?.min ?? priceRange.min"
                  :selectedMax="selectedPriceRange?.max ?? priceRange.max"
                  @change="priceRangeSliderChangeAndReload"
                ></PriceRangeSlider>
              </div>
            </div>
          </div>
          <template v-for="[tax, taxRef] in taxRefs">
            <div
              v-if="taxRef.terms.length > 0"
              :key="tax"
              :class="`catalog__filter catalog__filter--${tax}`"
            >
              <FilterList
                v-if="taxRef.options.type === 'checkbox'"
                :key="`${tax}-checkbox`"
                :taxonomy="tax"
                :title="taxRef.options.title"
                :terms="taxRef.terms"
                :selected-terms="taxRef.selectedTerms"
                :toggle-cb="checkAndReloadCallback"
                :max-depth="taxRef.options.maxDepth"
                :full-open="taxRef.options.fullOpen"
              ></FilterList>
              <PermalinkList
                v-else-if="taxRef.options.type === 'permalink'"
                :key="`${tax}-permalink`"
                :taxonomy="tax"
                :title="taxRef.options.title"
                :terms="taxRef.terms"
                :base-url="`${config.baseUrl}/${taxRef.options.rewrite}`"
                :max-depth="taxRef.options.maxDepth"
                :full-open="taxRef.options.fullOpen"
              ></PermalinkList>
              <p v-else>{{ `Invalid filter type: ${taxRef.options.type}` }}</p>
            </div>
          </template>
          <a class="catalog-filters__apply btn" @click="sidebarOpen = false">
            {{ $t('apply') }}
          </a>
        </div>
        <div
          v-if="config.layoutMode === 'header'"
          class="catalog-filters__inner catalog-filters__inner--header"
        >
          <div
            v-if="config.enablePriceFilter"
            class="catalog__filter catalog__filter--price"
          >
            <Dropdown
              :ref="addDdRef"
              @toggle="onDdToggle"
              :title="$t('price')"
              @apply="onPriceRangeSliderApply"
            >
              <div class="filter filter--price-slider">
                <PriceRangeSlider
                  :min="priceRange.min"
                  :max="priceRange.max"
                  :selectedMin="selectedPriceRange?.min ?? priceRange.min"
                  :selectedMax="selectedPriceRange?.max ?? priceRange.max"
                  @change="onPriceRangeSliderChange"
                ></PriceRangeSlider>
              </div>
            </Dropdown>
          </div>
          <template v-for="[tax, taxRef] in taxRefs">
            <div
              v-if="taxRef.terms.length > 0"
              :key="tax"
              :class="`catalog__filter catalog__filter--${tax}`"
            >
              <Dropdown
                :ref="addDdRef"
                @toggle="onDdToggle"
                :title="taxRef.options.title"
                @apply="toggleAndReload"
              >
                <FilterList
                  v-if="taxRef.options.type === 'checkbox'"
                  :key="`${tax}-checkbox`"
                  :taxonomy="tax"
                  :terms="taxRef.terms"
                  :selected-terms="taxRef.selectedTerms"
                  :toggle-cb="checkCallback"
                  :max-depth="taxRef.options.maxDepth"
                  :full-open="taxRef.options.fullOpen"
                ></FilterList>
                <PermalinkList
                  v-else-if="taxRef.options.type === 'permalink'"
                  :key="`${tax}-permalink`"
                  :taxonomy="tax"
                  :terms="taxRef.terms"
                  :base-url="`${config.baseUrl}/${taxRef.options.rewrite}`"
                  :max-depth="taxRef.options.maxDepth"
                  :full-open="taxRef.options.fullOpen"
                ></PermalinkList>
                <p v-else>
                  {{ `Invalid filter type: ${taxRef.options.type}` }}
                </p>
              </Dropdown>
            </div>
          </template>
        </div>
      </div>
      <div v-show="config.productIds.length === 0" class="catalog__header">
        <button
          type="button"
          class="catalog-filters__button"
          @click="sidebarOpen = true"
        >
          <i class="fal fa-sliders-h"></i> {{ $t('filterFor') }}
        </button>
        <div v-if="config.enableOrder" class="catalog__ordering">
          <select v-model="order" name="order" id="order">
            <option value="default">{{ $t('default') }}</option>
            <option value="alphabetic">{{ $t('alphabetic') }}</option>
            <option value="mostSold">{{ $t('popularity') }}</option>
            <!-- <option value="mostRated">Con più recensioni</option>
            <option value="bestRated">Con voto più alto</option> -->
            <option value="priceHighToLow">{{ $t('priceHighToLow') }}</option>
            <option value="priceLowToHigh">{{ $t('priceLowToHigh') }}</option>
          </select>
        </div>
      </div>
      <div class="catalog__items products" :class="`columns-${config.columns}`">
        <template v-if="!loadingProducts">
          <CatalogItem
            v-for="(product, i) in products"
            :key="`product-${product.id}`"
            :host="config.baseUrl"
            :product-permalink="config.productPermalink"
            :product="product"
            :show-add-to-cart-btn="config.showAddToCartBtn"
            @addToCart="gtagAddToCart($event, i)"
            @viewDetails="gtagSelectContent($event, i)"
          ></CatalogItem>
        </template>
      </div>
      <div class="catalog__loadmore loadmore">
        <Spinner v-if="loadingMoreProducts"></Spinner>
        <a
          class="loadmore__button btn"
          v-else
          v-show="showLoadMore"
          @click="loadMoreProducts"
        >
          {{ $t('showMore') }}
        </a>
      </div>
    </template>
    <h4 class="products__not_found" v-else>{{ $t('noProductsFound') }}</h4>
  </div>
</template>

<script lang="ts">
import {
  ComponentPublicInstance,
  computed,
  defineComponent,
  inject,
  onBeforeUpdate,
  onMounted,
  onUpdated,
  PropType,
  reactive,
  ref,
  UnwrapRef,
  watch,
} from 'vue';
import CatalogItem from '@/components/CatalogItem.vue';
import FilterList from '@/components/FilterList.vue';
import Dropdown from '@/components/Dropdown.vue';
import Spinner from '@/components/Spinner.vue';
import PriceRangeSlider from '@/components/PriceRangeSlider.vue';
import {
  CatalogOrder,
  CatalogQuery,
  Product,
  ProductQuery,
  TaxFilter,
  Term,
} from '@/services/api';
import PermalinkList from './PermalinkList.vue';
import { CatalogConfig } from '@/catalog';
import { wcserviceClientKey } from '@/main';
import $ from 'jquery';
import { callGtag, genGtagProductItem } from '@/gtag.utils';

export default defineComponent({
  name: 'Catalog',
  props: {
    config: {
      type: Object as PropType<CatalogConfig>,
      required: true,
    },
  },
  components: {
    Spinner,
    CatalogItem,
    FilterList,
    PermalinkList,
    Dropdown,
    PriceRangeSlider,
  },
  setup(props) {
    const sidebar = ref<HTMLDivElement | null>(null);
    const sidebarMoved = ref(false);
    const page = ref(0);
    const loadingCatalog = ref(true);
    const loadingProducts = ref(false);
    const loadingMoreProducts = ref(false);
    const products = ref<Product[]>([]);
    const priceRange = ref<{ min: number; max: number }>({ min: 0, max: 0 });
    const selectedPriceRange = ref<{ min: number; max: number } | null>(null);
    const order = ref<CatalogOrder>(CatalogOrder.Default);
    const sidebarOpen = ref<boolean>(false);
    const ddSet = ref<Set<ComponentPublicInstance>>(new Set());
    const taxRefs: Map<
      string,
      UnwrapRef<{
        options: CatalogConfig['taxonomies'][0];
        terms: Term[];
        flatTerms: Map<Term['id'], Term>;
        selectedTerms: Set<Term['id']>;
        loading: boolean;
      }>
    > = new Map();
    const priceRangeOpen = ref<boolean>(false);
    for (const options of props.config.taxonomies) {
      taxRefs.set(
        options.taxonomy,
        reactive({
          options: options,
          terms: [],
          flatTerms: new Map(),
          selectedTerms: new Set(),
          loading: false,
        }),
      );

      const taxRef = taxRefs.get(options.taxonomy);
      // this is not possible
      if (taxRef === undefined) {
        continue;
      }

      if (taxRef.options.exclude && taxRef.options.exclude.length > 0) {
        taxRef.options.enableFilter = false;
      }
    }

    const wcserviceClient = inject(wcserviceClientKey);
    if (wcserviceClient === undefined) {
      throw new Error('Cannot inject wcserviceClient');
    }

    const gtagAddToCart = (product: Product, itemIndex: number): void => {
      if (props.config.gtag.enabled) {
        return;
      }

      callGtag('add_to_cart', {
        items: [
          genGtagProductItem(
            product,
            props.config.gtag.listName!,
            {
              list_position: itemIndex + 1,
              quantity: 1,
            },
            props.config.gtag.brandFallback,
          ),
        ],
      });
    };

    const gtagSelectContent = (product: Product, itemIndex: number): void => {
      if (props.config.gtag.enabled) {
        return;
      }

      callGtag('select_content', {
        content_type: 'product',
        items: [
          genGtagProductItem(
            product,
            props.config.gtag.listName!,
            {
              list_position: itemIndex + 1,
            },
            props.config.gtag.brandFallback,
          ),
        ],
      });
    };

    const gtagViewItemList = (
      products: Product[],
      startIndex: number,
    ): void => {
      if (props.config.gtag.enabled) {
        return;
      }

      const items: Record<string, any>[] = [];
      for (const [i, p] of products.entries()) {
        items.push(
          genGtagProductItem(
            p,
            props.config.gtag.listName!,
            {
              list_position: startIndex + i + 1,
            },
            props.config.gtag.brandFallback,
          ),
        );
      }

      callGtag('view_item_list', { items });
    };

    const showLoadMore = computed<boolean>(() => {
      if (props.config.productIds.length > 0) {
        return false;
      }

      return (
        products.value.length ===
        (props.config.productsPerPage ?? 24) * (page.value + 1)
      );
    });

    const productQuery = (): ProductQuery => {
      const query: ProductQuery = {
        taxonomies: {},
        stockStatus: 'instock',
      };

      if (props.config.productIds.length > 0) {
        query.ids = props.config.productIds;

        return query;
      }

      if (selectedPriceRange.value !== null) {
        query.minPrice = selectedPriceRange.value.min;
        query.maxPrice = selectedPriceRange.value.max;
      }

      if (props.config.searchString !== undefined) {
        query.title = props.config.searchString;
        query.searchLogic = 'or';
      }

      for (const [tax, taxRef] of taxRefs.entries()) {
        const filter: TaxFilter = { op: 'or', terms: [] };
        if (taxRef.options.exclude && taxRef.options.exclude.length > 0) {
          filter.op = 'not';
          filter.terms = taxRef.options.exclude;
          query.taxonomies![tax] = filter;
          continue;
        }

        if (taxRef.options.selectedParent) {
          filter.terms.push(taxRef.options.selectedParent);
        }

        // excluding parent terms from query
        const termsToExclude: string[] = [];
        for (const t of taxRef.selectedTerms.values()) {
          const term = taxRef.flatTerms.get(t);
          // this should never happens
          if (term === undefined) {
            continue;
          }

          filter.terms.push(term.id);
          if (term.parent !== '0') {
            termsToExclude.push(term.parent);
          }
        }

        filter.terms = filter.terms.filter(t => !termsToExclude.includes(t));
        if (filter.terms.length > 0) {
          query.taxonomies![tax] = filter;
        }
      }

      return query;
    };

    const catalogQuery = (): CatalogQuery => {
      const limit = props.config.productsPerPage;
      return {
        limit: limit,
        offset: limit * page.value,
        query: productQuery(),
        order: order.value,
        postMetaIn: ['_attribute_list', '_sku', '_wc_average_rating'],
        taxonomiesIn: ['product_cat', 'product_type', 'product_collection'],
      };
    };

    const loadTaxonomy = async (
      tax: string,
      omitSelf: boolean = false,
    ): Promise<void> => {
      const taxRef = taxRefs.get(tax);
      if (taxRef === undefined) {
        console.warn(
          `Taxonomy reload failed: taxonomy \`${tax}\` does not exists`,
        );

        return;
      }

      if (taxRef.options.enableFilter === false) {
        return;
      }

      taxRef.loading = true;
      const query = productQuery();
      if (omitSelf && query.taxonomies !== undefined) {
        delete query.taxonomies[tax];
      }

      taxRef.terms = await wcserviceClient.findTaxonomyTerms(tax, {
        productQuery: query,
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

    const loadAllTaxonomies = async (
      omit: string[] = [],
      omitSelf: boolean = false,
    ): Promise<void> => {
      const promises: Promise<any>[] = [];
      for (const [tax, ref] of taxRefs.entries()) {
        if (omit.includes(tax)) {
          continue;
        }

        promises.push(loadTaxonomy(tax, omitSelf));
      }

      await Promise.all(promises);
    };

    const loadProducts = async (): Promise<void> => {
      loadingProducts.value = true;
      page.value = 0;
      products.value = await wcserviceClient.findProducts(catalogQuery());
      gtagViewItemList(products.value, 0);
      loadingProducts.value = false;
    };

    const loadPriceRange = async (): Promise<void> => {
      const res = await wcserviceClient.getPriceRange(catalogQuery());
      priceRange.value.min = Math.floor(res.min);
      priceRange.value.max = Math.ceil(res.max);
      if (selectedPriceRange.value === null) {
        selectedPriceRange.value = {
          min: priceRange.value.min,
          max: priceRange.value.max,
        };
      }
    };

    const loadMoreProducts = async (): Promise<void> => {
      loadingMoreProducts.value = true;
      page.value++;
      const newProducts = await wcserviceClient.findProducts(catalogQuery());
      gtagViewItemList(newProducts, products.value.length);
      products.value = products.value.concat(newProducts);
      loadingMoreProducts.value = false;
    };

    const resetCatalogScroll = (): void => {
      const catalog = document.querySelector<HTMLDivElement>('.main__grid');
      const html = document.querySelector('html');
      if (html === null || catalog === null) {
        return;
      }
      html.scrollTop = catalog.offsetTop;
    };

    const reloadCatalog = async (
      resetScroll: boolean = false,
    ): Promise<void> => {
      await loadPriceRange();
      await Promise.all([loadAllTaxonomies([], true), loadProducts()]);

      if (resetScroll) {
        resetCatalogScroll();
      }
    };

    const checkCallback = (tax: string, term: Term, checked: boolean): void => {
      const taxRef = taxRefs.get(tax);
      if (taxRef === undefined) {
        console.warn(
          `Taxonomy reload failed: taxonomy \`${tax}\` does not exists`,
        );
        return;
      }

      if (checked) {
        taxRef.selectedTerms.add(term.id);
      } else {
        taxRef.selectedTerms.delete(term.id);
        // uncheck recursively its own children
        const uncheckChildren = (term: Term): void => {
          for (const c of term.children) {
            taxRef.selectedTerms.delete(c.id);
            if (c.children.length > 0) {
              uncheckChildren(c);
            }
          }
        };
        uncheckChildren(term);
      }
    };

    const checkAndReloadCallback = async (
      tax: string,
      term: Term,
      checked: boolean,
    ): Promise<void> => {
      checkCallback(tax, term, checked);
      reloadCatalog();
    };

    const applyCallback = async (
      tax: string,
      checkedTerms: Set<Term['id']>,
    ) => {
      const taxRef = taxRefs.get(tax);
      if (taxRef === undefined) {
        console.warn(
          `Taxonomy reload failed: taxonomy \`${tax}\` does not exists`,
        );

        return;
      }

      taxRef.selectedTerms = checkedTerms;

      await loadPriceRange();
      await Promise.all([loadAllTaxonomies([], true), loadProducts()]);
    };

    const onPriceRangeSliderChange = (
      values: number[],
      reload = false,
    ): void => {
      selectedPriceRange.value = {
        min: values[0],
        max: values[1],
      };
      if (reload) {
        Promise.all([loadAllTaxonomies(), loadProducts()]);
      }
    };

    const priceRangeSliderChangeAndReload = (values: number[]) => {
      onPriceRangeSliderChange(values, true);
    };

    const onPriceRangeSliderApply = (e: MouseEvent): void => {
      onDdToggle(e, false);
      Promise.all([loadAllTaxonomies(), loadProducts()]);
      sidebarOpen.value = false;
    };

    const addDdRef = (el: any): void => {
      ddSet.value.add(el);
    };

    const onDdToggle = (e: MouseEvent, toggle?: boolean): void => {
      const $dd = $(e.target as HTMLElement)
        .parent('.dropdown')
        .find('.dropdown__content');
      const previousState = $dd.css('display');

      for (const el of ddSet.value.values()) {
        $(el.$el).find('.dropdown__content').css('display', 'none');
      }

      if (toggle !== undefined) {
        $dd.css('display', toggle ? 'block' : 'none');
      } else {
        $dd.css('display', previousState === 'block' ? 'none' : 'block');
      }
    };

    const toggleAndReload = async (e: MouseEvent): Promise<void> => {
      onDdToggle(e);
      reloadCatalog();
      sidebarOpen.value = false;
    };

    watch(order, (newVal, oldVal) => {
      loadProducts();
    });

    onMounted(async () => {
      loadingCatalog.value = true;
      await loadPriceRange();
      await Promise.all([loadAllTaxonomies(), loadProducts()]);

      let reload = false;
      for (const [tax, ref] of taxRefs.entries()) {
        if (ref.options.selectedTerms === undefined) {
          continue;
        }

        for (const tId of ref.options.selectedTerms) {
          const term = ref.flatTerms.get(tId);
          if (term === undefined) {
            continue;
          }

          checkCallback(tax, term, true);
          reload = true;
        }
      }

      if (reload) {
        await reloadCatalog();
      }
      loadingCatalog.value = false;
    });

    onUpdated(() => {
      if (sidebarMoved.value) {
        return;
      }

      const tp = props.config.teleportSidebar;
      if (tp === undefined) {
        return;
      }

      const destination = document.querySelector(tp);
      if (destination === null) {
        console.warn(
          `Teleport destination element ${tp} does not exists. Skipping`,
        );

        return;
      }

      if (sidebar.value !== null) {
        destination.appendChild(sidebar.value);
        sidebarMoved.value = true;
      }
    });

    onBeforeUpdate(() => {
      ddSet.value = new Set();
    });

    return {
      sidebar,
      sidebarMoved,
      page,
      loadingCatalog,
      loadingProducts,
      loadingMoreProducts,
      products,
      priceRange,
      priceRangeOpen,
      selectedPriceRange,
      order,
      sidebarOpen,
      showLoadMore,
      productQuery,
      catalogQuery,
      taxRefs,
      loadTaxonomy,
      loadAllTaxonomies,
      loadProducts,
      loadMoreProducts,
      loadPriceRange,
      reloadCatalog,
      checkCallback,
      checkAndReloadCallback,
      applyCallback,
      onPriceRangeSliderChange,
      priceRangeSliderChangeAndReload,
      onPriceRangeSliderApply,
      addDdRef,
      onDdToggle,
      toggleAndReload,
      gtagAddToCart,
      gtagSelectContent,
      gtagViewItemList,
    };
  },
});
</script>
