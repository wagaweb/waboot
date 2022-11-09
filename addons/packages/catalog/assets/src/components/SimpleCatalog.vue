<template>
  <div class="catalog catalog--layout-single-item">
    <CircularSpinner v-if="loadingProducts" :size="25"></CircularSpinner>
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
        :show-quantity-input="config.showQuantityInput"
        @addToCart="addToCart($event, i)"
        @viewDetails="viewDetails($event, i)"
      ></CatalogItem>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, PropType } from 'vue';
import CatalogItem from '@/components/CatalogItem.vue';
import CircularSpinner from '@/components/CircularSpinner.vue';
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
    CircularSpinner,
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
