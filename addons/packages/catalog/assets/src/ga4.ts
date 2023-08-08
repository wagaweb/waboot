import { TaxApplier } from "./catalog";
import { Product } from "./services/api";

type Item = Record<string, any>;

export class GA4 {
  constructor(
    public listId: string,
    public listName: string,
    public taxApplier: TaxApplier,
    public enabled: boolean = true
  ) {}

  private convertProductIntoItem(
    p: Product,
    variation?: string,
    index: number = 0,
    quantity: number = 1
  ): Item {
    const item: Record<string, any> = {
      index: index,
      item_list_id: this.listId,
      item_list_name: this.listName,
      quantity: quantity,
    };

    let sku = p.sku;
    let name = p.name;
    let price = p.price;
    let basePrice = p.basePrice;
    let taxClass = p.taxClass;
    if (p.type === "variable") {
      if (
        p.catalogData?.variations &&
        p.catalogData.variations.products.length > 0
      ) {
        let v = p.catalogData.variations.products[0];
        if (variation) {
          v = p.catalogData.variations.products.find(
            (v) => v.id.toString() === variation
          )!;
        }

        sku = v.sku;
        name = v.name;
        price = v.price;
        basePrice = v.basePrice;
        taxClass = v.taxClass;

        item["item_variant"] = v.attributeTerm;
      }
    }

    basePrice = this.taxApplier(basePrice, taxClass);
    price = this.taxApplier(price, taxClass);
    item["item_id"] = sku;
    item["item_name"] = name;
    item["price"] = basePrice;
    item["discount"] = basePrice - price;
    item["curr_price"] = price;

    let cat = p.taxonomies?.["product_cat"]?.[0];
    let i = 0;
    while (cat) {
      if (i === 0) {
        item["item_category"] = cat.name;
      } else if (i > 5) {
        break;
      } else {
        item[`item_category${i + 1}`] = cat.name;
      }
      cat = cat.children[0];
      i++;
    }

    let brand =
      p.taxonomies?.["product_brand"]?.[0] ??
      p.taxonomies?.["brand_taxonomy"]?.[0];
    if (brand) {
      item["item_brand"] = brand.name;
    }

    return item;
  }

  private dataLayerPush(event: string, data: any): void {
    console.log({ event, data });
    if (!this.enabled) {
      return;
    }

    try {
      if (typeof dataLayer !== "object") {
        return;
      }

      data.event = event;
      dataLayer.push({ ecommerce: null });
      dataLayer.push(data);
    } catch (e) {
      console.error(e);
    }
  }

  viewItemList(prods: { p: Product; v?: string; idx: number }[]): void {
    this.dataLayerPush("view_item_list", {
      ecommerce: {
        items: prods.map((p) => this.convertProductIntoItem(p.p, p.v, p.idx)),
      },
    });
  }

  selectItem(p: Product, v?: string, idx: number = 0): void {
    this.dataLayerPush("select_item", {
      ecommerce: {
        items: [this.convertProductIntoItem(p, v, idx)],
      },
    });
  }

  addToCart(p: Product, v?: string, idx: number = 0, qty: number = 1): void {
    const item = this.convertProductIntoItem(p, v, idx, qty);

    this.dataLayerPush("add_to_cart", {
      currency: "EUR",
      value: item["curr_price"] ?? 0,
      ecommerce: {
        items: [item],
      },
    });
  }

  addToWishlist(p: Product, v?: string, idx: number = 0): void {
    const item = this.convertProductIntoItem(p, v, idx);

    this.dataLayerPush("add_to_wishlist", {
      currency: "EUR",
      value: item["curr_price"] ?? 0,
      ecommerce: {
        items: [item],
      },
    });
  }
}
