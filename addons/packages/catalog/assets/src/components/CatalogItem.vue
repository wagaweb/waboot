<template>
  <div
    class="catalog__item product type-product instock sale product-type-simple"
  >
    <a
      :href="`${host}/${productPermalink}/${product.slug}`"
      class="woocommerce-LoopProduct-link woocommerce-loop-product__link"
      @click="$emit('view-details', product)"
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
      @click="$emit('add-to-wishlist', product)"
    >
      <span class="jvm_add_to_wishlist_heart"></span>
      <span class="jvm_add_to_wishlist_text_add">Add to wishlist</span>
      <span class="jvm_add_to_wishlist_text_remove">Remove from wishlist</span>
    </a>
    <!--<p class="woocommerce-loop-product__collection">{{ collection }}</p>-->
    <h2 class="woocommerce-loop-product__title">
      <a :href="`${host}/${productPermalink}/${product.slug}`">
        {{ product.name }}
      </a>
    </h2>
    <div class="star-rating" v-if="hasStarRating">
      <i
        v-for="(ratingClass, i) in ratingStarClasses"
        :key="i"
        :class="ratingClass"
      ></i>
    </div>
    <span class="price">
      <template v-if="product.stockStatus === 'instock'">
        <template v-if="product.hasPriceRange">
          <span>
            {{ $t('from') }}&nbsp;
            <del v-if="product.onSale" aria-hidden="true">
              <span class="woocommerce-Price-amount amount">
                {{ product.minBasePrice }} €
              </span>
            </del>
            <ins>
              <span class="woocommerce-Price-amount amount">
                {{ product.minPrice }} €
              </span>
              <span v-if="product.onSale" class="sale-percentage">
                -{{ salePercentage }}%
              </span>
            </ins>
          </span>
        </template>
        <span v-else>
          <del v-if="product.onSale" aria-hidden="true">
            <span class="woocommerce-Price-amount amount">
              {{ product.basePrice }} €
            </span>
          </del>
          <ins>
            <span class="woocommerce-Price-amount amount">
              {{ product.price }} €
            </span>
            <span v-if="product.onSale" class="sale-percentage">
              -{{ salePercentage }}%
            </span>
          </ins>
        </span>
      </template>
      <ins v-else-if="product.stockStatus === 'outofstock'">
        <span class="woocommerce-Price-amount amount">
          {{ $t('outOfStock') }}
        </span>
      </ins>
    </span>
    <div
      v-if="productType === 'variable' && attributeList !== undefined"
      class="variation-list"
    >
      <span
        v-for="v in attributeList.variations"
        :key="v.variation"
        :title="v.termName"
        class="variation-list__item"
        :class="{
          'variation-list__item--selected':
            v.variation.toString() === selectedId,
          'variation-list__item--outofstock': v.stockStatus === 'outofstock',
        }"
        @click="
          () => {
            if (v.stockStatus === 'instock')
              selectedId = v.variation.toString();
          }
        "
      >
        <span
          class="variation-list__image"
          v-if="attributeList.type === 'image'"
          :style="{
            'background-image': `url(${v.data.url})`,
          }"
        ></span>
        <span
          class="variation-list__color"
          v-else-if="attributeList.type === 'color'"
          :style="{ 'background-color': v.data }"
        >
        </span>
        <span
          class="variation-list__select"
          v-else-if="attributeList.type === 'select'"
          >{{ v.data }}</span
        >
      </span>
    </div>
    <span v-if="showAddToCartBtn && product.stockStatus === 'instock'">
      <a
        v-if="productType !== 'variable'"
        :href="`?add-to-cart=${selectedId}`"
        data-quantity="1"
        class="button product_type_simple add_to_cart_button ajax_add_to_cart"
        :data-product_id="selectedId"
        :data-product_sku="sku"
        :aria-label="$t('addProductToCart', [product.name])"
        @click="$emit('add-to-cart', product)"
      >
        {{ $t('addToCart') }}
      </a>
      <a
        v-else
        :href="`${host}/${productPermalink}/${product.slug}`"
        class="button"
      >
        {{ $t('showMore') }}
      </a>
    </span>
  </div>
</template>

<script lang="ts">
import { Product } from '@/services/api';
import { defineComponent, PropType } from 'vue';

export default defineComponent({
  name: 'CatalogItem',
  events: ['add-to-cart', 'view-details', 'add-to-wishlist'],
  props: {
    host: {
      type: String,
      required: true,
    },
    productPermalink: {
      type: String,
      required: true,
    },
    product: {
      type: Object as PropType<Product>,
      required: true,
    },
    showAddToCartBtn: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      selectedId: this.product.id,
    };
  },
  computed: {
    collection(): string {
      return this.product.taxonomies?.['product_collection']?.[0].name ?? '';
    },
    category(): string {
      if (this.product.taxonomies === undefined) return '';
      let cat = this.product.taxonomies?.['product_cat']?.[0];
      if (cat === undefined) {
        return '';
      }

      while (cat.children.length !== 0) {
        cat = cat.children[0];
      }

      return cat.name;
    },
    productType(): string {
      return this.product.taxonomies?.['product_type']?.[0].slug ?? '';
    },
    attributeList():
      | {
          product: number;
          attribute: string;
          type: string;
          variations: {
            variation: number;
            termId: number;
            termName: string;
            termSlug: string;
            stockStatus: 'instock' | 'outofstock';
            data: any;
          }[];
        }
      | undefined {
      const list = this.product?.metadata?.['_attribute_list'] ?? undefined;
      if (list === undefined) {
        return undefined;
      }
      if (Array.isArray(list)) {
        return undefined;
      }

      return JSON.parse(list);
    },
    image(): string | undefined {
      return this.product.image?.sizes['shop_catalog']?.link;
    },
    sku(): string {
      const sku = this.product?.metadata?.['_sku'] ?? '';
      if (Array.isArray(sku)) {
        return '';
      }

      return sku.replace(/_$/, '');
    },
    price(): string {
      //return this.taxPrice(this.product.price);
      return this.product.price;
    },
    basePrice(): string {
      //return this.taxPrice(this.product.basePrice);
      return this.product.basePrice;
    },
    minPrice(): string {
      //return this.taxPrice(this.product.minPrice);
      return this.product.minPrice;
    },
    starRating(): number {
      return Number(this.product?.metadata?.['_wc_average_rating'] ?? '0');
    },
    hasStarRating(): boolean {
      return this.starRating > 0;
    },
    ratingStarClasses(): string[] {
      let classes = [];
      for (let i = 1; i <= 5; i++) {
        if (i <= this.starRating) {
          classes.push('fas fa-star');
        } else if (i <= this.starRating + 0.5) {
          classes.push('fas fa-star-half-alt');
        } else {
          classes.push('far fa-star');
        }
      }
      return classes;
    },
    salePercentage(): string {
      if (this.product.onSale) {
        let reg = 0;
        let curr = 0;
        if (this.product.hasPriceRange) {
          reg = Number(this.product.minBasePrice.replace(',', '.'));
          curr = Number(this.product.minPrice.replace(',', '.'));
        } else {
          reg = Number(this.product.basePrice.replace(',', '.'));
          curr = Number(this.product.price.replace(',', '.'));
        }

        return Math.round(100 - (curr * 100) / reg).toString();
      }

      return '';
    },
    jvmWishListExists(): boolean {
      return typeof JVMWooCommerceWishlist === 'object';
    },
  },
  methods: {
    taxPrice(price: string): string {
      let taxValue = 0;
      const tax = this.product?.metadata?.['_tax_class'] ?? '';
      if (tax.length === 0 || tax === 'Standard') {
        taxValue = 1.22;
      } else {
        taxValue = 1.1;
      }
      const priceTaxed = Number(price ?? 0) * Number(taxValue);

      return (Math.round(priceTaxed * 100) / 100).toFixed(2);
    },
  },
  mounted(): void {
    if (this.jvmWishListExists) {
      JVMWooCommerceWishlist!.build();
    }

    if (this.productType === 'variable' && this.attributeList !== undefined) {
      for (const v of this.attributeList.variations) {
        if (v.stockStatus === 'instock') {
          this.selectedId = v.variation.toString();
          break;
        }
      }
    }
  },
});
</script>
