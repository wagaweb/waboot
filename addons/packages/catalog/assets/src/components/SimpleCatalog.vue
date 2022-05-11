<template>
  <div class="catalog catalog--layout-single-item">
    <Spinner v-if="loading"></Spinner>
    <div
      v-else
      class="catalog__items products"
      :class="`columns-${config.columns}`"
    >
      <CatalogItem
        v-for="(p, i) in products"
        :key="`product-${p.id}`"
        :host="config.baseUrl"
        :product-permalink="config.productPermalink"
        :product="p"
        :show-add-to-cart-btn="config.showAddToCartBtn"
        @addToCart="gtagAddToCart($event, i)"
        @viewDetails="gtagSelectContent($event, i)"
      ></CatalogItem>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject, onMounted, PropType, Ref, ref } from 'vue';
import CatalogItem from '@/components/CatalogItem.vue';
import Spinner from '@/components/Spinner.vue';
import { CatalogOrder, CatalogQuery, Product, TaxFilter } from '@/services/api';
import { wcserviceClientKey } from '@/main';
import { getGtagCallbacks } from '@/gtag.utils';
import { CatalogConfig } from '@/catalog';

export default defineComponent({
  name: 'SimpleCatalog',
  props: {
    config: {
      type: Object as PropType<CatalogConfig>,
      required: true,
    },
  },
  components: {
    Spinner,
    CatalogItem,
  },
  setup(props) {
    const wcserviceClient = inject(wcserviceClientKey);
    if (wcserviceClient === undefined) {
      throw new Error('Cannot inject wcserviceClient');
    }

    const { gtagAddToCart, gtagSelectContent, gtagViewItemList } =
      getGtagCallbacks(props.config.gtag);

    const loading = ref(true);
    const products: Ref<Product[]> = ref([]);
    const loadProducts = async () => {
      loading.value = true;

      const taxonomies: Record<string, TaxFilter> = {};
      if (props.config.taxonomies.length > 0) {
        for (const tax of props.config.taxonomies) {
          taxonomies[tax.taxonomy] = { op: 'or', terms: tax.selectedTerms };
        }
      }

      products.value = await wcserviceClient.findProducts({
        limit: props.config.productIds.length,
        postMetaIn: ['_attribute_list', '_sku', '_wc_average_rating'],
        taxonomiesIn: ['product_cat', 'product_type', 'product_collection'],
        order: CatalogOrder.Alphabetic,
        query: {
          ids: props.config.productIds,
          taxonomies,
        },
      });

      gtagViewItemList(products.value, 0);
      loading.value = false;
    };

    onMounted(() => {
      loadProducts();
    });

    return {
      // refs
      loading,
      products,
      // methods
      gtagAddToCart,
      gtagSelectContent,
    };
  },
});
</script>
