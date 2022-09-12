import { Product } from './services/api';

export class GA4 {
  constructor(
    public listId: string,
    public listName: string,
    public fallbackBrand: string = 'Uncategorized',
  ) {}

  private priceToFloat(price: string): number {
    return Number(price.replaceAll('.', '').replace(',', '.'));
  }

  private convertProductIntoItem(
    p: Product,
    index: number,
  ): Record<string, any> {
    let price = this.priceToFloat(p.basePrice);
    let discount = this.priceToFloat(p.basePrice) - this.priceToFloat(p.price);
    if (p.hasPriceRange) {
      price = this.priceToFloat(p.minBasePrice);
      discount =
        this.priceToFloat(p.minBasePrice) - this.priceToFloat(p.minPrice);
    }
    discount = Math.round(discount * 100) / 100;

    const item: Record<string, any> = {
      item_id: p.metadata?.['_sku'] ?? p.id,
      item_name: p.name,
      // affiliation: '',
      currency: 'EUR',
      discount,
      index,
      item_list_id: this.listId,
      item_list_name: this.listName,
      // item_variant: '',
      // location_id: '',
      price,
      quantity: 1,
    };

    for (const [i, cat] of (p.taxonomies?.['product_cat'] ?? []).entries()) {
      let key = 'item_category';
      if (i > 0) {
        key += i + 1;
      }

      item[key] = cat.name;
    }

    const brands = p.taxonomies?.['product_brand'] ?? [];
    if (brands.length === 0) {
      item['item_brand'] = this.fallbackBrand;
    } else {
      for (const [i, brand] of brands.entries()) {
        let key = 'item_brand';
        if (i > 0) {
          key += i + 1;
        }

        item[key] = brand.name;
      }
    }

    return item;
  }

  private dataLayerPush(event: string, data: any): void {
    try {
      if (typeof dataLayer !== 'object') {
        return;
      }

      data.event = event;
      dataLayer.push({ ecommerce: null });
      dataLayer.push(data);
    } catch (e) {
      console.error(e);
    }
  }

  viewItemList(products: Product[], startIndex: number): void {
    this.dataLayerPush('view_item_list', {
      ecommerce: {
        items: products.map((p, i) =>
          this.convertProductIntoItem(p, startIndex + i),
        ),
      },
    });
  }

  selectItem(product: Product, index: number): void {
    this.dataLayerPush('select_item', {
      ecommerce: {
        items: [this.convertProductIntoItem(product, index)],
      },
    });
  }

  addToCart(product: Product, index: number): void {
    this.dataLayerPush('add_to_cart', {
      ecommerce: {
        items: [this.convertProductIntoItem(product, index)],
      },
    });
  }

  addToWishlist(product: Product, index: number): void {
    this.dataLayerPush('add_to_wishlist', {
      ecommerce: {
        currency: 'EUR',
        value: product.hasPriceRange
          ? this.priceToFloat(product.minPrice)
          : this.priceToFloat(product.price),
        items: [this.convertProductIntoItem(product, index)],
      },
    });
  }
}
