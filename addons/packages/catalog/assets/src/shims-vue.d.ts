import {WcserviceClient} from "@/services/api";

declare module "*.vue" {
  import type { DefineComponent } from "vue";
  const component: DefineComponent<{}, {}, any>;
  export default component;
}

declare module '@vue/runtime-core' {
  export interface ComponentCustomProperties {
    $apiClient: WcserviceClient;
  }
}
