import { createApp } from 'vue';
import Catalog from '@/components/Catalog.vue';
import SimpleCatalog from '@/components/SimpleCatalog.vue';
import { CatalogConfig } from './catalog';
import { createI18n } from 'vue-i18n';
import messages, { AvailableLanguages } from '@/i18n';

import './sass/main.scss';

function initCatalog(entry: Element): void {
  const rawConfig = JSON.parse(entry.getAttribute('catalog-config') ?? 'null');
  if (rawConfig === null) {
    console.error('catalog-config property not specified');
    return;
  }

  const config = new CatalogConfig(rawConfig);
  const app = createApp(Catalog, { config });

  const i18n = createI18n({
    locale: config.language,
    fallbackLocale: AvailableLanguages.itIT,
    messages,
  });
  app.use(i18n);

  app.mount(entry);
}

function initSimpleCatalog(entry: Element): void {
  const rawConfig = JSON.parse(entry.getAttribute('catalog-config') ?? 'null');
  if (rawConfig === null) {
    console.error('catalog-config property not specified');
    return;
  }

  const config = new CatalogConfig(rawConfig);
  const app = createApp(SimpleCatalog, { config });

  const i18n = createI18n({
    locale: config.language,
    fallbackLocale: AvailableLanguages.itIT,
    messages,
  });
  app.use(i18n);

  app.mount(entry);
}

const entry = document.querySelector('#vue-catalog');
if (entry) {
  initCatalog(entry);
}

for (const entry of document.querySelectorAll('.vue-simple-catalog')) {
  initSimpleCatalog(entry);
}
