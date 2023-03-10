<template>
  <div class="catalog" :class="`catalog--layout-${config.layoutMode}`">
    <div
        ref="sidebar"
        class="catalog-filters"
        :class="{ 'catalog-filters--opened': sidebarOpen }"
    >
      <a class="catalog-filters__close" @click="sidebarOpen = false">
        <i class="far fa-times"></i>
      </a>
      <div
          v-if="config.layoutMode === 'sidebar' || config.layoutMode === 'block'"
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
                  @change="onPriceRangeSliderChangeAndReload"
              ></PriceRangeSlider>
            </div>
          </div>
        </div>
        <template v-if="config.enableFilters" v-for="[tax, taxRef] in taxRefs">
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
                :toggle-cb="onFilterListToggleAndReload"
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
        <a  v-if="config.enableFilters" class="catalog-filters__apply btn" @click="sidebarOpen = false">
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
              @apply="onDdApplyPriceRange"
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
        <template v-if="config.enableFilters" v-for="[tax, taxRef] in taxRefs">
          <div
              v-if="taxRef.terms.length > 0"
              :key="tax"
              :class="`catalog__filter catalog__filter--${tax}`"
          >
            <Dropdown
                :ref="addDdRef"
                @toggle="onDdToggle"
                :title="taxRef.options.title"
                @apply="onDdApplyFilters"
            >
              <FilterList
                  v-if="taxRef.options.type === 'checkbox'"
                  :key="`${tax}-checkbox`"
                  :taxonomy="tax"
                  :terms="taxRef.terms"
                  :selected-terms="taxRef.selectedTerms"
                  :toggle-cb="onFilterListToggle"
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
    <div class="catalog__header">
      <button
          v-if="config.enableFilters"
          type="button"
          class="catalog-filters__button"
          @click="sidebarOpen = true"
      >
        <i class="fal fa-sliders-h"></i> {{ $t('filterFor') }}
      </button>
      <div v-if="config.enableOrder" class="catalog__ordering">
        <select
            :value="order"
            @change="onOrderSelectChange"
            name="order"
            id="order"
        >
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
    <div class="catalog__items-wrapper">
      <CircularSpinner v-show="loadingProducts" :size="50"></CircularSpinner>
      <div
          v-show="!loadingProducts && previousPage > 0"
          class="catalog__loadmore catalog__loadmore--less"
      >
        <CircularSpinner
            v-show="loadingMoreProducts"
            :size="25"
        ></CircularSpinner>
        <a v-show="!loadingMoreProducts" class="btn" @click="onLoadLessClick">
          {{ $t('showLess') }}
        </a>
      </div>
      <div
          v-show="!loadingProducts"
          class="catalog__items products"
          :class="`columns-${config.columns}`"
      >
        <CatalogItem
            v-for="(product, i) in products"
            :key="`product-${product.id}`"
            :host="config.baseUrl"
            :product="product"
            :lang="config.language"
            :price-formatter="priceFormatter"
            :tax-applier="taxApplier"
            :show-add-to-cart-btn="config.showAddToCartBtn"
            :show-quantity-input="config.showQuantityInput"
            @addToCart="addToCartHandle($event, i)"
            @viewDetails="viewDetailsHandle($event, i)"
            @addToWishlist="addToWishlistHandle($event, i)"
        ></CatalogItem>
      </div>
      <h4
          v-show="!loadingProducts && products.length === 0"
          class="products__not_found"
      >
        {{ $t('noProductsFound') }}
      </h4>
      <div
          v-show="!loadingProducts && numberOfPages > page"
          class="catalog__loadmore"
      >
        <CircularSpinner
            v-show="loadingMoreProducts"
            :size="25"
        ></CircularSpinner>
        <a v-show="!loadingMoreProducts" class="btn" @click="onLoadMoreClick">
          {{ $t('showMore') }}
        </a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {
  ComponentPublicInstance,
  defineComponent,
  onBeforeUpdate,
  onMounted,
  onUpdated,
  PropType,
  ref,
} from 'vue';
import CatalogItem from '@/components/CatalogItem.vue';
import FilterList from '@/components/FilterList.vue';
import Dropdown from '@/components/Dropdown.vue';
import CircularSpinner from '@/components/CircularSpinner.vue';
import PriceRangeSlider from '@/components/PriceRangeSlider.vue';
import { CatalogOrder, Term } from '@/services/api';
import PermalinkList from '@/components/PermalinkList.vue';
import { CatalogConfig, useCatalog } from '@/catalog';
import $ from 'jquery';

export default defineComponent({
  name: 'Catalog',
  props: {
    config: {
      type: Object as PropType<CatalogConfig>,
      required: true,
    },
  },
  components: {
    CircularSpinner,
    CatalogItem,
    FilterList,
    PermalinkList,
    Dropdown,
    PriceRangeSlider,
  },
  setup(props) {
    const {
      // refs
      products,
      count,
      taxRefs,
      priceRange,
      selectedPriceRange,
      order,
      page,
      loadingProducts,
      loadingMoreProducts,
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
      loadAllTaxonomies,
      addToCartHandle,
      viewDetailsHandle,
      addToWishlistHandle,
      // data
      priceFormatter,
      taxApplier,
    } = useCatalog(props.config);

    const loadingCatalog = ref<boolean>(true);
    const sidebar = ref<HTMLDivElement | null>(null);
    const sidebarMoved = ref(false);
    const sidebarOpen = ref<boolean>(false);
    const ddSet = ref<Set<ComponentPublicInstance>>(new Set());

    const initCatalog = async (): Promise<void> => {
      const productQuery = getProductQuery();
      const catalogQuery = getCatalogQuery(productQuery);

      loadingCatalog.value = true;
      await Promise.all([
        loadProducts(catalogQuery),
        loadProductCount(productQuery),
        loadAllTaxonomies(productQuery),
        loadPriceRange(catalogQuery),
      ]);
      loadingCatalog.value = false;
    };

    const resetCatalogScroll = (): void => {
      const catalog = document.querySelector<HTMLDivElement>('.main__grid');
      const html = document.querySelector('html');
      if (html === null || catalog === null) {
        return;
      }
      html.scrollTop = catalog.offsetTop;
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

    const reloadOnLoadMore = async (): Promise<void> => {
      const productQuery = getProductQuery();
      const catalogQuery = getCatalogQuery(productQuery);
      await loadMoreProducts(catalogQuery);
      setQueryString();
    };

    const reloadOnLoadLess = async (): Promise<void> => {
      const productQuery = getProductQuery();
      const catalogQuery = getCatalogQuery(productQuery);
      await loadLessProducts(catalogQuery);
    };

    const reloadOnSelectTerm = async (): Promise<void> => {
      page.value = 1;
      selectedPriceRange.value = null;
      const productQuery = getProductQuery();
      const catalogQuery = getCatalogQuery(productQuery);
      await Promise.all([
        loadProducts(catalogQuery),
        loadProductCount(productQuery),
        loadAllTaxonomies(productQuery),
        loadPriceRange(catalogQuery),
      ]);
      setQueryString();
    };

    const reloadOnSelectPriceRange = async (): Promise<void> => {
      page.value = 1;
      const productQuery = getProductQuery();
      const catalogQuery = getCatalogQuery(productQuery);
      await Promise.all([
        loadProducts(catalogQuery),
        loadProductCount(productQuery),
        loadAllTaxonomies(productQuery),
      ]);
      setQueryString();
    };

    const reloadOnChangeOrder = async (): Promise<void> => {
      page.value = 1;
      const productQuery = getProductQuery();
      const catalogQuery = getCatalogQuery(productQuery);
      await loadProducts(catalogQuery);
      setQueryString();
    };

    const onLoadMoreClick = (): void => {
      page.value++;
      reloadOnLoadMore();
    };

    const onLoadLessClick = (): void => {
      reloadOnLoadLess();
    };

    const onFilterListToggle = (
        tax: string,
        term: Term,
        checked: boolean,
    ): void => {
      const taxRef = taxRefs.get(tax);
      if (taxRef === undefined) {
        console.warn(`Taxonomy \`${tax}\` does not exists`);
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

    const onFilterListToggleAndReload = async (
        tax: string,
        term: Term,
        checked: boolean,
    ): Promise<void> => {
      onFilterListToggle(tax, term, checked);
      resetCatalogScroll();
      reloadOnSelectTerm();
    };

    const onPriceRangeSliderChange = (values: number[]): void => {
      selectedPriceRange.value = { min: values[0], max: values[1] };
    };

    const onPriceRangeSliderChangeAndReload = async (
        values: number[],
    ): Promise<void> => {
      onPriceRangeSliderChange(values);
      resetCatalogScroll();
      reloadOnSelectPriceRange();
    };

    const onOrderSelectChange = async (e: Event): Promise<void> => {
      const select = e.target as HTMLSelectElement;
      order.value = select.value as CatalogOrder;
      reloadOnChangeOrder();
    };

    const onDdApplyPriceRange = (e: MouseEvent): void => {
      onDdToggle(e);
      sidebarOpen.value = false;
      if (selectedPriceRange.value === null) return;
      reloadOnSelectPriceRange();
    };

    const onDdApplyFilters = (e: MouseEvent): void => {
      onDdToggle(e);
      sidebarOpen.value = false;
      reloadOnSelectTerm();
    };

    onBeforeUpdate(() => {
      ddSet.value = new Set();
    });

    onMounted(() => {
      readQueryString();
      initCatalog();
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

    window.onpopstate = () => {
      readQueryString();
      initCatalog();
    };

    return {
      // catalog refs
      products,
      count,
      order,
      priceRange,
      selectedPriceRange,
      taxRefs,
      loadingCatalog,
      loadingProducts,
      loadingMoreProducts,
      page,
      // catalog computed
      numberOfPages,
      previousPage,
      // refs
      sidebar,
      sidebarOpen,
      // methods
      addDdRef,
      onDdToggle,
      onDdApplyPriceRange,
      onDdApplyFilters,
      onFilterListToggle,
      onFilterListToggleAndReload,
      onOrderSelectChange,
      onPriceRangeSliderChange,
      onPriceRangeSliderChangeAndReload,
      onLoadMoreClick,
      onLoadLessClick,
      addToCartHandle,
      viewDetailsHandle,
      addToWishlistHandle,
      // data
      priceFormatter,
      taxApplier,
    };
  },
});
</script>
