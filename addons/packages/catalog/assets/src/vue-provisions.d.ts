import {WcserviceClient} from "@/services/api";

// the wcservice client provided into vue 
declare module '@vue/runtime-core' {
  export interface ComponentCustomProperties {
    $apiClient: WcserviceClient;
  }
}
