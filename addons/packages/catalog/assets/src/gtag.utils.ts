import { Product } from './services/api';

export function genGtagProductItem(
  p: Product,
  listName: string,
  extraData?: Record<string, any>,
  brandFallback: string = '',
): Record<string, any> {
  const item: Record<string, any> = {
    id: p.metadata?.['_sku'] ?? p.id,
    name: p.name,
    list_name: listName,
    brand: p.taxonomies?.['product_brand']?.[0]?.name ?? brandFallback,
    category: p.taxonomies?.['product_cat']?.[0]?.name ?? '',
  };

  if (extraData !== undefined) {
    Object.assign(item, extraData);
  }

  return item;
}

export function callGtag(event: string, data: Record<string, any>): void {
  try {
    if (typeof gtag !== 'function') {
      return;
    }

    gtag('event', event, data);
  } catch (e) {
    console.error(e);
  }
}

export function getGtagCallbacks({
  enabled,
  listName,
  brandFallback,
}: {
  enabled: boolean;
  listName?: string;
  brandFallback?: string;
}) {
  return {
    gtagAddToCart: (product: Product, itemIndex: number): void => {
      if (enabled) {
        return;
      }

      callGtag('add_to_cart', {
        items: [
          genGtagProductItem(
            product,
            listName!,
            {
              list_position: itemIndex + 1,
              quantity: 1,
            },
            brandFallback,
          ),
        ],
      });
    },
    gtagSelectContent: (product: Product, itemIndex: number): void => {
      if (enabled) {
        return;
      }

      callGtag('select_content', {
        content_type: 'product',
        items: [
          genGtagProductItem(
            product,
            listName!,
            {
              list_position: itemIndex + 1,
            },
            brandFallback,
          ),
        ],
      });
    },
    gtagViewItemList: (products: Product[], startIndex: number): void => {
      if (enabled) {
        return;
      }

      const items: Record<string, any>[] = [];
      for (const [i, p] of products.entries()) {
        items.push(
          genGtagProductItem(
            p,
            listName!,
            {
              list_position: startIndex + i + 1,
            },
            brandFallback,
          ),
        );
      }

      callGtag('view_item_list', { items });
    },
  };
}
