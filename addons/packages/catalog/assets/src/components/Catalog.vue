<template>
  <div class="catalog" :class="`catalog--layout-${config.layoutMode}`">
    <Spinner v-if="loadingCatalog"></Spinner>
    <template v-else-if="count > 0">
      <div
        ref="sidebar"
        class="catalog-filters"
        :class="{ 'catalog-filters--opened': sidebarOpen }"
      >
        <a class="catalog-filters__close" @click="sidebarOpen = false">
          <i class="far fa-times"></i>
        </a>
        <div
          v-if="
            config.layoutMode === 'sidebar' || config.layoutMode === 'block'
          "
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
                  @change="v => onPriceRangeSliderChange(v, true)"
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
                :toggle-cb="
                  (tax, term, checked) =>
                    onFilterListToggle(tax, term, checked, true)
                "
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
            :product="product"
            :show-add-to-cart-btn="config.showAddToCartBtn"
            @addToCart="addToCart($event, i)"
            @viewDetails="viewDetails($event, i)"
          ></CatalogItem>
        </template>
      </div>
      <div class="catalog__loadmore loadmore">
        <Spinner v-if="loadingMoreProducts"></Spinner>
        <a
          class="loadmore__button btn"
          v-else
          v-show="numberOfPages > page"
          @click="onLoadMoreClick"
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
import Spinner from '@/components/Spinner.vue';
import PriceRangeSlider from '@/components/PriceRangeSlider.vue';
import { Term } from '@/services/api';
import PermalinkList from './PermalinkList.vue';
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
    Spinner,
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
      loadingCatalog,
      // computed
      numberOfPages,
      // methods
      getProductQuery,
      getCatalogQuery,
      loadProducts,
      loadProductCount,
      loadPriceRange,
      loadAllTaxonomies,
      initCatalog,
      toggleTerm,
      selectPriceRange,
      loadMoreProducts,
      addToCart,
      viewDetails,
    } = useCatalog(props.config);

    const sidebar = ref<HTMLDivElement | null>(null);
    const sidebarMoved = ref(false);
    const sidebarOpen = ref<boolean>(false);
    const ddSet = ref<Set<ComponentPublicInstance>>(new Set());

    const resetCatalogScroll = (): void => {
      const catalog = document.querySelector<HTMLDivElement>('.main__grid');
      const html = document.querySelector('html');
      if (html === null || catalog === null) {
        return;
      }
      html.scrollTop = catalog.offsetTop;
    };

    const onPriceRangeSliderChange = (values: number[], reload = false): void => {
      selectPriceRange(values[0], values[1], reload);

      if (reload) {
        resetCatalogScroll();
      }
    };

    const onFilterListToggle = (tax: string, term: Term, checked: boolean, reload = false): void => {
      toggleTerm(tax, term, checked, reload);

      if (reload) {
        resetCatalogScroll();
      }
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

    const onDdApplyPriceRange = (e: MouseEvent): void => {
      onDdToggle(e);
      sidebarOpen.value = false;
      if (selectedPriceRange.value === null) return;
      page.value = 1;
      const productQuery = getProductQuery();
      const catalogQuery = getCatalogQuery(productQuery);
      loadProducts(catalogQuery);
      loadProductCount(productQuery);
      loadAllTaxonomies(productQuery);
    };

    const onDdApplyFilters = async (e: MouseEvent): Promise<void> => {
      onDdToggle(e);
      sidebarOpen.value = false;
      page.value = 1;
      const productQuery = getProductQuery();
      const catalogQuery = getCatalogQuery(productQuery);
      loadProducts(catalogQuery);
      loadProductCount(productQuery);
      loadAllTaxonomies(productQuery, true);
      loadPriceRange(catalogQuery);
    };

    const onLoadMoreClick = (): void => {
      loadMoreProducts();
    };

    onBeforeUpdate(() => {
      ddSet.value = new Set();
    });

    onMounted(() => {
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
      // refs
      sidebarOpen,
      // methods
      addDdRef,
      onDdToggle,
      onDdApplyPriceRange,
      onDdApplyFilters,
      onFilterListToggle,
      onPriceRangeSliderChange,
      onLoadMoreClick,
      addToCart,
      viewDetails,
    };
  },
});
</script>
