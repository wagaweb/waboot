<template>
  <div class="catalog">
    <div
      ref="sidebar"
      class="catalog-filters"
      :class="{ 'catalog-filters--opened': sidebarOpen }"
    >
      <a class="catalog-filters__close" @click="sidebarOpen = false">
        <i class="far fa-times"></i>
      </a>
      <div class="catalog-filters__inner">
        <div v-if="config.enablePriceFilter ?? false" id="price-slider" class="price-slider catalog__filter"></div>
        <div
          v-for="[tax, taxRef] in taxRefs"
          :key="tax"
          :class="`catalog__filter catalog__filter--${tax}`"
        >
          <template v-if="taxRef.terms.length > 0">
            <FilterList
              v-if="taxRef.options.type === 'checkbox'"
              :key="`${tax}-checkbox`"
              :taxonomy="tax"
              :title="taxRef.options.title"
              :terms="taxRef.terms"
              :selected-terms="taxRef.selectedTerms"
              :toggle-cb="checkCallback"
            ></FilterList>
            <PermalinkList
              v-else-if="taxRef.options.type === 'permalink'"
              :key="`${tax}-permalink`"
              :taxonomy="tax"
              :title="taxRef.options.title"
              :terms="taxRef.terms"
              :base-url="config.baseUrl"
            ></PermalinkList>
            <DropdownFilter
              v-else-if="taxRef.options.type === 'dropdown'"
              :key="`${tax}-dropdown`"
              :taxonomy="tax"
              :title="taxRef.options.title"
              :terms="taxRef.terms"
              :selected-terms="taxRef.selectedTerms"
              :applyCb="applyCallback"
            ></DropdownFilter>
            <p v-else>{{ `Invalid filter type: ${taxRef.options.type}` }}</p>
          </template>
        </div>
      </div>
      <a class="catalog-filters__apply btn" @click="sidebarOpen = false"
        >Applica</a
      >
    </div>
    <div class="catalog__header">
      <button
        type="button"
        class="catalog-filters__button"
        @click="sidebarOpen = true"
      >
        <i class="fal fa-sliders-h"></i> Filtra per
      </button>
      <div v-if="config.enableOrder ?? false" class="catalog__ordering">
        <select v-model="order" name="order" id="order">
          <option value="alphabetic">Alfabetico</option>
          <option value="mostSold">Popolarità</option>
          <option value="mostRated">Con più recensioni</option>
          <option value="bestRated">Con voto più alto</option>
          <option value="priceHighToLow">Prezzo in ordine decrescente</option>
          <option value="priceLowToHigh">Prezzo in ordine crescente</option>
        </select>
      </div>
    </div>
    <div class="catalog__items products columns-4">
      <template v-if="!loadingProducts">
        <CatalogItem
          v-for="product in products"
          :key="`product-${product.id}`"
          :host="config.baseUrl"
          :product-permalink="config.productPermalink"
          :product="product"
        ></CatalogItem>
      </template>
    </div>
    <div class="catalog__loadmore loadmore">
      <p v-if="loadingMoreProducts"><i class="fas fa-spinner fa-spin"></i></p>
      <a
        class="loadmore__button btn"
        v-else
        v-show="showLoadMore"
        @click="loadMoreProducts"
      >
        Mostra successivi
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import {
  computed,
  defineComponent,
  inject,
  onMounted,
  PropType,
  reactive,
  ref,
  UnwrapRef,
  watch,
} from 'vue';
import CatalogItem from '@/components/CatalogItem.vue';
import FilterList from '@/components/FilterList.vue';
import DropdownFilter from '@/components/DropdownFilter.vue';
import {
  CatalogOrder,
  CatalogQuery,
  Product,
  ProductQuery,
  TaxFilter,
  Term,
} from '@/services/api';
import PermalinkList from './PermalinkList.vue';
import { CatalogConfig, TaxFilterOptions } from '@/catalog';
import { wcserviceClientKey } from '@/main';
import noUiSlider from 'nouislider';
import 'nouislider/dist/nouislider.css';

export default defineComponent({
  name: 'Catalog',
  props: {
    config: {
      type: Object as PropType<CatalogConfig>,
      required: true,
    },
  },
  components: {
    CatalogItem,
    FilterList,
    PermalinkList,
    DropdownFilter,
  },
  setup(props) {
    const sidebar = ref<HTMLDivElement | null>(null);
    const page = ref(0);
    const loadingProducts = ref(false);
    const loadingMoreProducts = ref(false);
    const products = ref<Product[]>([]);
    const priceRange = ref<{ min: number; max: number }>({ min: 0, max: 0 });
    const selectedPriceRange = ref<{ min: number; max: number } | null>(null);
    const order = ref<CatalogOrder>(CatalogOrder.MostSold);
    const sidebarOpen = ref<boolean>(false);
    const taxRefs: Map<
      string,
      UnwrapRef<{
        options: TaxFilterOptions;
        terms: Term[];
        flatTerms: Map<Term['id'], Term>;
        selectedTerms: Set<Term['id']>;
        loading: boolean;
      }>
    > = new Map();
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

    // computed
    const showLoadMore = computed<boolean>(() => {
      return (
        products.value.length ===
        props.config.productsPerPage * (page.value + 1)
      );
    });

    const productQuery = (): ProductQuery => {
      const query: ProductQuery = {
        taxonomies: {},
        stockStatus: 'instock',
      };

      if (selectedPriceRange.value !== null) {
        query.minPrice = selectedPriceRange.value.min;
        query.maxPrice = selectedPriceRange.value.max;
      }

      if (props.config.searchString !== undefined) {
        query.title = props.config.searchString;
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
        metadataIn: [
          '_attribute_list',
          '_sku',
          '_wc_average_rating',
          'jdt_nome2',
        ],
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
      loadingProducts.value = false;
    };

    const loadPriceRange = async (): Promise<void> => {
      priceRange.value = await wcserviceClient.getPriceRange(catalogQuery());
      if (selectedPriceRange.value !== null) {
        selectedPriceRange.value.min = priceRange.value.min;
        selectedPriceRange.value.max = priceRange.value.max;
      }
    };

    const loadMoreProducts = async (): Promise<void> => {
      loadingMoreProducts.value = true;
      page.value++;
      products.value = products.value.concat(
        await wcserviceClient.findProducts(catalogQuery()),
      );
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

    const checkCallback = async (tax: string, term: Term, checked: boolean) => {
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

      await loadPriceRange();
      await Promise.all([loadAllTaxonomies([], true), loadProducts()]);
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

    watch(order, (newVal, oldVal) => {
      loadProducts();
    });

    watch(priceRange, (newVal, oldVal) => {
      const slider = document.getElementById('price-slider');
      const numFormatter = {
        from: (value: string): number => {
          return Number(value.replace('€ ', ''));
        },
        to: (value: number): string => {
          return `€ ${value.toFixed(0)}`;
        },
      };
      if (slider === null) {
        return;
      }

      // @ts-ignore
      if (slider.noUiSlider === undefined) {
        noUiSlider.create(slider, {
          start: [priceRange.value.min, priceRange.value.max],
          connect: true,
          range: {
            min: priceRange.value.min,
            max: priceRange.value.max,
          },
          step: 1,
          tooltips: true,
          format: numFormatter,
        });
        // @ts-ignore
        slider.noUiSlider.on('set', function (values: string[]) {
          selectedPriceRange.value = {
            min: numFormatter.from(values[0]),
            max: numFormatter.from(values[1]),
          };
          Promise.all([loadAllTaxonomies(), loadProducts()]);
        });
      } else {
        // @ts-ignore
        slider.noUiSlider.updateOptions(
          {
            start: [priceRange.value.min, priceRange.value.max],
            range: {
              min: priceRange.value.min,
              max: priceRange.value.max,
            },
          },
          false,
        );
      }
    });

    onMounted(() => {
      (async () => {
        await loadPriceRange();
        await Promise.all([loadAllTaxonomies(), loadProducts()]);
      })();

      (() => {
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
        }
      })();
    });

    return {
      sidebar,
      page,
      loadingProducts,
      loadingMoreProducts,
      products,
      priceRange,
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
      checkCallback,
      applyCallback,
    };
  },
});
</script>
