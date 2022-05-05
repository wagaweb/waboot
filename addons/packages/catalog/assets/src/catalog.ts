export interface CatalogConfig {
  baseUrl: string;
  productPermalink: string;
  apiBaseUrl: string;
  productsPerPage: number;
  layoutMode: LayoutMode;
  teleportSidebar?: string;
  searchString?: string;
  taxonomies: TaxFilterOptions[];
  language?: string;
  enableOrder?: boolean;
  enablePriceFilter?: boolean;
  showAddToCartBtn?: boolean;
  gtag?: { enabled: boolean; listName: string; brandFallback?: string };
}

export interface TaxFilterOptions {
  taxonomy: string;
  rewrite: string;
  title: string;
  enableFilter: boolean;
  type: FilterType;
  selectedTerms?: string[];
  selectedParent?: string;
  exclude?: string[];
  maxDepth?: number;
  fullOpen?: boolean;
}

export enum LayoutMode {
  Sidebar = 'sidebar',
  Header = 'header',
}

export enum FilterType {
  Checkbox = 'checkbox',
  Permalink = 'permalink',
}
