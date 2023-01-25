<template>
  <div
    class="catalog__item product type-product instock sale product-type-simple"
  >
    <a
      :href="`${host}/?p=${product.id}`"
      class="woocommerce-LoopProduct-link woocommerce-loop-product__link"
      @click="$emit('viewDetails', { product })"
    >
      <img
        :src="image"
        class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail"
        :alt="product.name"
      />
    </a>
    <a
      v-if="jvmWishListExists"
      class="jvm_add_to_wishlist add-to-wishlist"
      :href="`?add_to_wishlist=${product.id}`"
      title="Add to wishlist"
      rel="nofollow"
      :data-product-title="product.name"
      :data-product-id="product.id"
      @click="$emit('addToWishlist', { product })"
    >
      <span class="jvm_add_to_wishlist_heart"></span>
      <span class="jvm_add_to_wishlist_text_add">Add to wishlist</span>
      <span class="jvm_add_to_wishlist_text_remove">Remove from wishlist</span>
    </a>
    <h2 class="woocommerce-loop-product__title">
      <a :href="`${host}/?p=${product.id}`">
        {{ product.name }}
      </a>
    </h2>
    <div class="star-rating" v-if="product.averageRating > 0">
      <i
        v-for="(ratingClass, i) in ratingStarClasses"
        :key="i"
        :class="ratingClass"
      ></i>
    </div>
    <span class="price">
      <template v-if="product.stockStatus === 'instock'">
        <span v-if="product.minPrice > product.maxPrice" class="price__from">
          {{ $t('from') }}&nbsp;
        </span>
        <template v-if="product.minPrice < product.minBasePrice">
          <del aria-hidden="true">
            <span class="woocommerce-Price-amount amount">
              {{ minBasePrice }} €
            </span>
          </del>
          <ins>
            <span
              v-if="product.price !== product.basePrice"
              class="sale-percentage"
            >
              -{{ minSalePercentage }}%
            </span>
            <span class="woocommerce-Price-amount amount">
              {{ minPrice }} €
            </span>
          </ins>
        </template>
        <span v-else class="woocommerce-Price-amount amount">
          {{ minPrice }} €
        </span>
      </template>
      <span
        v-else-if="product.stockStatus === 'outofstock'"
        class="woocommerce-Price-amount amount"
      >
        {{ $t('outOfStock') }}
      </span>
    </span>
    <template v-if="showAddToCartBtn && product.stockStatus === 'instock'">
      <div
        v-if="product.type === 'variable' && product.catalogData?.variations"
        class="variation-list"
      >
        <span
          v-for="v in product.catalogData.variations.products"
          :key="v.id"
          :title="v.name"
          class="variation-list__item"
          :class="{
            'variation-list__item--selected': v.id.toString() === selectedId,
            'variation-list__item--outofstock': v.stockStatus === 'outofstock',
          }"
          @click="
            () => {
              if (v.stockStatus === 'instock') selectedId = v.id.toString();
            }
          "
        >
          <span
            class="variation-list__image"
            v-if="product.catalogData.variations.type === 'image'"
            :style="{
              'background-image': `url(${v.data.url})`,
            }"
          ></span>
          <span
            v-else-if="product.catalogData.variations.type === 'color'"
            class="variation-list__color"
            :style="{ 'background-color': v.data }"
          >
          </span>
          <span
            class="variation-list__select"
            v-else-if="product.catalogData.variations.type === 'select'"
          >
            {{ v.data }}
          </span>
        </span>
      </div>
      <div v-if="showQuantityInput" class="quantity-input">
        <span @click="increaseQuantity(-1)">-</span>
        <input v-model="quantity" type="number" min="1" />
        <span @click="increaseQuantity(1)">+</span>
      </div>
      <a
        v-if="
          product.type !== 'variable' ||
          (product.type === 'variable' && product.catalogData?.variations)
        "
        :href="`?add-to-cart=${selectedId}`"
        :data-quantity="quantity"
        class="button product_type_simple add_to_cart_button ajax_add_to_cart"
        :data-product_id="selectedId"
        :data-product_sku="product.sku"
        :aria-label="$t('addProductToCart', [product.name])"
        @click="$emit('addToCart', { product, selectedId, quantity })"
      >
        {{ $t('addToCart') }}
      </a>
      <a v-else :href="`${host}/?p=${product.id}`" class="button">
        {{ $t('showProduct') }}
      </a>
    </template>
  </div>
</template>

<script lang="ts">
import { TaxApplier } from '@/catalog';
import { Product } from '@/services/api';
import { defineComponent, PropType } from 'vue';

export default defineComponent({
  name: 'CatalogItem',
  emits: {
    addToCart: (payload: {
      product: Product;
      selectedId: string;
      quantity: number;
    }) => true,
    viewDetails: (payload: { product: Product }) => true,
    addToWishlist: (payload: { product: Product }) => true,
  },
  props: {
    host: {
      type: String,
      required: true,
    },
    product: {
      type: Object as PropType<Product>,
      required: true,
    },
    priceFormatter: {
      type: Object as PropType<Intl.NumberFormat>,
      required: true,
    },
    taxApplier: {
      type: Function as PropType<TaxApplier>,
      required: true,
    },
    showAddToCartBtn: {
      type: Boolean,
      default: true,
    },
    showQuantityInput: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      selectedId: this.product.id,
      quantity: 1,
    };
  },
  computed: {
    category(): string | undefined {
      let cat = this.product.taxonomies['product_cat']?.[0];
      if (!cat) {
        return undefined;
      }

      while (cat.children.length !== 0) {
        cat = cat.children[0];
      }

      return cat.name;
    },
    image(): string | undefined {
      return (
        this.product.image?.sizes['shop_catalog']?.link ??
        this.product.image?.link
      );
    },
    minPrice(): string {
      return this.stringifyPrice(this.product.minPrice, this.product.taxClass);
    },
    minBasePrice(): string {
      return this.stringifyPrice(
        this.product.minBasePrice,
        this.product.taxClass,
      );
    },
    maxPrice(): string {
      return this.stringifyPrice(this.product.maxPrice, this.product.taxClass);
    },
    maxBasePrice(): string {
      return this.stringifyPrice(
        this.product.maxBasePrice,
        this.product.taxClass,
      );
    },
    ratingStarClasses(): string[] {
      const classes: string[] = [];
      for (let i = 1; i <= 5; i++) {
        if (i <= this.product.averageRating) {
          classes.push('fas fa-star');
        } else if (i <= this.product.averageRating + 0.5) {
          classes.push('fas fa-star-half-alt');
        } else {
          classes.push('far fa-star');
        }
      }
      return classes;
    },
    minSalePercentage(): string {
      return Math.round(
        100 - (this.product.minPrice * 100) / this.product.minBasePrice,
      ).toString();
    },
    maxSalePercentage(): string {
      return Math.round(
        100 - (this.product.maxPrice * 100) / this.product.maxBasePrice,
      ).toString();
    },
    jvmWishListExists(): boolean {
      return typeof JVMWooCommerceWishlist === 'object';
    },
  },
  mounted(): void {
    // todo: this is not efficient
    if (this.jvmWishListExists) {
      JVMWooCommerceWishlist!.build();
    }

    if (
      this.product.type === 'variable' &&
      this.product.catalogData?.variations
    ) {
      for (const v of this.product.catalogData.variations.products) {
        if (v.stockStatus === 'instock') {
          this.selectedId = v.id.toString();
          break;
        }
      }
    }
  },
  methods: {
    stringifyPrice(price: number, taxClass: string): string {
      return this.priceFormatter.format(this.taxApplier(price, taxClass));
    },
    increaseQuantity(amount: number): void {
      this.quantity += amount;
      if (this.quantity < 1) {
        this.quantity = 1;
      }
    },
  },
});
</script>
