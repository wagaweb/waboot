<template>
  <div class="catalog catalog--layout-single-item">
    <Spinner v-if="loadingProducts"></Spinner>
    <div
      v-else
      class="catalog__items products"
      :class="`columns-${config.columns}`"
    >
      <CatalogItem
        v-for="(p, i) in products"
        :key="`product-${p.id}`"
        :host="config.baseUrl"
        :product="p"
        :show-add-to-cart-btn="config.showAddToCartBtn"
        @addToCart="addToCart($event, i)"
        @viewDetails="viewDetails($event, i)"
      ></CatalogItem>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, PropType } from 'vue';
import CatalogItem from '@/components/CatalogItem.vue';
import Spinner from '@/components/Spinner.vue';
import { CatalogConfig, useCatalog } from '@/catalog';

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
    const {
      products,
      loadingProducts,
      getProductQuery,
      getCatalogQuery,
      loadProducts,
      addToCart,
      viewDetails,
    } = useCatalog(props.config);

    onMounted(() => {
      const productQuery = getProductQuery();
      const catalogQuery = getCatalogQuery(productQuery);
      loadProducts(catalogQuery);
    });

    return {
      loadingProducts,
      products,
      addToCart,
      viewDetails,
    };
  },
});
</script>
