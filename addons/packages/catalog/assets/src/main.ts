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
  const entry = document.querySelector('#vue-catalog');
  if (entry !== null) {
    const config: CatalogConfig | null = JSON.parse(
      entry.getAttribute('catalog-config') ?? 'null',
    );
    if (config === null) {
      console.error('catalog-config property not specified');

      return;
    }

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
  } else {
    console.warn(
      'No entry point element found for product catalog initialization',
    );
  }
})();
