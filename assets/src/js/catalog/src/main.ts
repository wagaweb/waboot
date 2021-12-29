import { createApp, InjectionKey } from 'vue';
import Catalog from '@/components/Catalog.vue';
import { WcserviceClient } from '@/services/api';
import { CatalogConfig } from './catalog';

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

    vm.mount(entry);
  } else {
    console.error(
      'No entry point element found to initiate the product catalog',
    );
  }
})();

declare module '@vue/runtime-core' {
  export interface ComponentCustomProperties {
    $apiClient: WcserviceClient;
  }
}
