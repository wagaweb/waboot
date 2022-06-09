export enum LayoutMode {
  Sidebar = 'sidebar',
  Header = 'header',
  Block = 'block',
}

export enum FilterType {
  Checkbox = 'checkbox',
  Permalink = 'permalink',
}

export class CatalogConfig {
  // basic configs
  baseUrl: string;
  apiBaseUrl: string;
  language: string;
  productPermalink: string;
  taxonomies: {
    taxonomy: string;
    rewrite: string;
    title?: string;
    enableFilter: boolean;
    type: FilterType;
    selectedTerms: string[];
    selectedParent?: string;
    exclude: string[];
    maxDepth?: number;
    fullOpen: boolean;
  }[];
  productIds: string[];
  searchString?: string;
  gtag: {
    enabled: boolean;
    listName?: string;
    brandFallback?: string;
  };
  // layout
  productsPerPage: number;
  columns: number;
  enableFilers: boolean;
  layoutMode: LayoutMode;
  teleportSidebar?: string;
  enableOrder: boolean;
  enablePriceFilter: boolean;
  showAddToCartBtn: boolean;

  constructor(data: any) {
    if (typeof data !== 'object' || data === null) {
      throw new Error('Invalid config format');
    }

    if (data.baseUrl === undefined) {
      throw new Error('`baseUrl`: parameter is required');
    }
    this.baseUrl = String(data.baseUrl);

    if (data.apiBaseUrl === undefined) {
      throw new Error('`apiBaseUrl`: parameter is required');
    }
    this.apiBaseUrl = String(data.apiBaseUrl);

    if (data.language === undefined) {
      throw new Error('`language`: parameter is required');
    }
    this.language = String(data.language);

    this.productPermalink = String(data.productPermalink ?? 'p');
    this.taxonomies = [];
    if (Array.isArray(data.taxonomies)) {
      for (const t of data.taxonomies) {
        if (typeof t !== 'object' || t === null) {
          throw new Error('`taxonomies[]`: invalid object');
        }

        if (t.taxonomy === undefined) {
          throw new Error('`taxonomies[].language`: parameter is required');
        }
        t.taxonomy = String(t.taxonomy);

        if (t.rewrite === undefined) {
          throw new Error('`taxonomies[].rewrite`: parameter is required');
        }
        t.rewrite = String(t.rewrite);

        if (t.title !== undefined) {
          t.title = String(t.title);
        }
        t.enableFiler = Boolean(t.enableFiler ?? true);
        t.type = String(t.type ?? FilterType.Checkbox) as FilterType;
        t.selectedTerms = Array.isArray(t.selectedTerms)
          ? t.selectedTerms.map((id: any) => String(id))
          : [];
        if (t.selectedParent !== undefined) {
          t.selectedParent = String(t.selectedParent);
        }
        t.exclude = Array.isArray(t.exclude)
          ? t.exclude.map((id: any) => String(id))
          : [];
        if (t.maxDepth !== undefined) {
          t.maxDepth = Number(t.maxDepth);
        }
        t.fullOpen = Boolean(t.fullOpen ?? false);

        this.taxonomies.push(t);
      }
    }

    this.productIds = Array.isArray(data.productIds)
      ? data.productIds.map((id: any) => String(id))
      : [];
    if (data.searchString !== undefined) {
      this.searchString = String(data.searchString);
    }

    this.gtag = { enabled: false };
    if (typeof data.gtag !== 'object' || data.gtag === null) {
      throw new Error('`gtag`: invalid object');
    } else if (data.gtag !== undefined) {
      if (data.gtag.enabled === undefined) {
        throw new Error('`gtag.enabled`: parameter is required');
      }
      this.gtag.enabled = Boolean(data.gtag);

      if (data.gtag.listName === undefined) {
        throw new Error(
          '`gtag.listName`: parameter is required when gtag is enabled',
        );
      }
      this.gtag.listName = String(data.gtag.listName);

      if (data.gtag.brandFallback === undefined) {
        throw new Error(
          '`gtag.brandFallback`: parameter is required when gtag is enabled',
        );
      }
      this.gtag.brandFallback = String(data.gtag.brandFallback);
    }

    this.productsPerPage = Number(data.productsPerPage ?? 24);
    this.columns = Number(data.columns ?? 4);
    this.enableFilers = Boolean(data.enableFilers ?? true);
    this.layoutMode = String(
      data.layoutMode ?? LayoutMode.Sidebar,
    ) as LayoutMode;
    if (data.teleportSidebar !== undefined) {
      this.teleportSidebar = String(data.teleportSidebar);
    }
    this.enableOrder = Boolean(data.enableOrder ?? true);
    this.enablePriceFilter = Boolean(data.enablePriceFilter ?? true);
    this.showAddToCartBtn = Boolean(data.showAddToCartBtn ?? true);
  }
}
