import { createApp, InjectionKey } from 'vue';
import Catalog from '@/components/Catalog.vue';
import SimpleCatalog from '@/components/SimpleCatalog.vue';
import { WcserviceClient } from '@/services/api';
import { CatalogConfig } from './catalog';
import { createI18n } from 'vue-i18n';
import messages, { AvailableLanguages } from '@/i18n';

import './sass/main.scss';

export const wcserviceClientKey: InjectionKey<WcserviceClient> =
  Symbol('wcserviceClient');

(() => {
  for (const entry of document.querySelectorAll('.vue-catalog')) {
    const rawConfig = JSON.parse(
      entry.getAttribute('catalog-config') ?? 'null',
    );
    if (rawConfig === null) {
      console.error('catalog-config property not specified');

      continue;
    }

    const config = new CatalogConfig(rawConfig);
    const app = createApp(Catalog, {
      config,
    });

    const wcServiceClient = new WcserviceClient(config.apiBaseUrl);
    wcServiceClient.setLanguage(config.language);
    app.provide(wcserviceClientKey, wcServiceClient);

    const i18n = createI18n({
      locale: config.language,
      fallbackLocale: AvailableLanguages.itIT,
      messages,
    });

    app.use(i18n);

    app.mount(entry);
  }

  for (const entry of document.querySelectorAll('.vue-simple-catalog')) {
    const rawConfig = JSON.parse(
      entry.getAttribute('catalog-config') ?? 'null',
    );
    if (rawConfig === null) {
      console.error('catalog-config property not specified');

      continue;
    }

    const config = new CatalogConfig(rawConfig);
    const app = createApp(SimpleCatalog, {
      config,
    });

    const wcServiceClient = new WcserviceClient(config.apiBaseUrl);
    wcServiceClient.setLanguage(config.language);
    app.provide(wcserviceClientKey, wcServiceClient);

    const i18n = createI18n({
      locale: config.language,
      fallbackLocale: AvailableLanguages.itIT,
      messages,
    });

    app.use(i18n);

    app.mount(entry);
  }
})();
