import { createApp, InjectionKey } from 'vue';
import Catalog from '@/components/Catalog.vue';
import { WcserviceClient } from '@/services/api';
import { CatalogConfig } from './catalog';
import { createI18n } from 'vue-i18n';
import messages, { AvailableLanguages } from '@/i18n';

import './sass/main.scss';

export const wcserviceClientKey: InjectionKey<WcserviceClient> =
  Symbol('wcserviceClient');

(() => {
  const entries = document.querySelectorAll('.vue-catalog');
  if (entries.length === 0) {
    console.warn(
      'No entry point element found for product catalog initialization',
    );
  }

  for (const entry of entries) {
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
    if (config.language !== undefined && config.language.length > 0) {
      wcServiceClient.setLanguage(config.language);
    }
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
