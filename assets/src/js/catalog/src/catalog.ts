export interface CatalogConfig {
  baseUrl: string;
  productPermalink: string;
  apiBaseUrl: string;
  productsPerPage: number;
  teleportSidebar?: string;
  searchString?: string;
  taxonomies: TaxFilterOptions[];
  language?: string;
  enableOrder?: boolean;
  enablePriceFilter?: boolean;
}

export interface TaxFilterOptions {
  taxonomy: string;
  title: string;
  enableFilter: boolean;
  type: FilterType;
  selectedParent?: string;
  exclude?: string[];
}

export enum FilterType {
  Checkbox = 'checkbox',
  Dropdown = 'dropdown',
  Permalink = 'permalink',
}
