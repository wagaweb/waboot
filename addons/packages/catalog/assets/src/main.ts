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

    const vm = createApp(Catalog, {
      config,
    });

    const wcServiceClient = new WcserviceClient(config.apiBaseUrl);
    if (config.language !== undefined && config.language.length > 0) {
      wcServiceClient.setLanguage(config.language);
    }
    vm.provide(wcserviceClientKey, wcServiceClient);

    const i18n = createI18n({
      locale: config.language,
      fallbackLocale: AvailableLanguages.itIT,
      messages,
    });

    vm.use(i18n);

    vm.mount(entry);
  } else {
    console.error(
      'No entry point element found to initiate the product catalog',
    );
  }
})();
