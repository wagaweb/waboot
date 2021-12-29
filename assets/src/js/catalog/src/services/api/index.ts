export interface Image {
  id: string;
  title: string;
  mediaType: string;
  width: number;
  height: number;
  link: string;
  baseUrl: string;
  attachedFile: string;
  sizes: {
    [size: string]:
      | {
          fileName: string;
          link: string;
          mediaType: string;
          width: number;
          height: number;
        }
      | undefined;
  };
}

export interface Product {
  id: string;
  slug: string;
  name: string;
  description: string;
  excerpt: string;
  price: string;
  basePrice: string;
  hasPriceRange: boolean;
  minPrice: string;
  maxPrice: string;
  stockStatus: 'instock' | 'outofstock';
  onSale: boolean;
  link: string;
  type: string;
  image?: Image;
  gallery: Image[];
  metadata?: {
    [metaKey: string]: string | string[] | undefined;
  };
  taxonomies?: {
    [taxonomy: string]: Term[] | undefined;
  };
}

export interface Term {
  id: string;
  name: string;
  slug: string;
  description: string;
  taxonomy: string;
  parent: string;
  children: Term[];
}

export interface TaxFilter {
  op: 'or' | 'and' | 'not';
  terms: string[];
}

export interface ProductQuery {
  title?: string;
  minPrice?: number;
  maxPrice?: number;
  taxonomies?: {
    [taxonomy: string]: TaxFilter;
  };
  stockStatus?: 'instock' | 'outofstock';
}

export interface CatalogQuery {
  query?: ProductQuery;
  limit?: number;
  offset?: number;
  metadataIn?: string[];
  order?: CatalogOrder;
  taxonomiesIn?: string[];
}

export enum CatalogOrder {
  Alphabetic = 'alphabetic',
  MostSold = 'mostSold',
  BestRated = 'bestRated',
  MostRated = 'mostRated',
  PriceLowToHigh = 'priceLowToHigh',
  PriceHighToLow = 'priceHighToLow',
}

export interface TaxonomyQuery {
  parent?: string;
  productQuery?: ProductQuery;
  limit?: number;
  offset?: number;
}

export class WcserviceClient {
  headers: { [header: string]: string } = {};

  constructor(public baseUrl: string) {}

  setLanguage(language: string): void {
    this.headers['Accept-Language'] = language;
  }

  getHeaders(headers: { [header: string]: string }): {
    [header: string]: string;
  } {
    return Object.assign(this.headers, headers);
  }

  async findProducts(query?: CatalogQuery): Promise<Product[]> {
    const res = await fetch(`${this.baseUrl}/products/find`, {
      method: 'POST',
      headers: this.getHeaders({
        'Content-Type': 'application/json',
      }),
      body: JSON.stringify(query ?? {}),
    });

    return await res.json();
  }

  async getPriceRange(
    query?: CatalogQuery,
  ): Promise<{ min: number; max: number }> {
    const res = await fetch(`${this.baseUrl}/products/priceRange`, {
      method: 'POST',
      headers: this.getHeaders({
        'Content-Type': 'application/json',
      }),
      body: JSON.stringify(query ?? {}),
    });

    return await res.json();
  }

  async findTaxonomyTerms(
    taxonomy: string,
    query?: TaxonomyQuery,
  ): Promise<Term[]> {
    const res = await fetch(
      `${this.baseUrl}/taxonomies/${taxonomy}/terms/findHierarchical`,
      {
        method: 'POST',
        headers: this.getHeaders({
          'Content-Type': 'application/json',
        }),
        body: JSON.stringify(query ?? {}),
      },
    );

    return await res.json();
  }
}
