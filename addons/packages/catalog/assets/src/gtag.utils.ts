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
