import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createI18n } from 'vue-i18n'
import App from './App.vue'
import {getBackEndData} from "@/services/wp/backendData.ts";
import messages from '@/locale/messages';

const app = createApp(App)
app.use(createI18n({
    legacy: false,
    locale: getBackEndData().locale,
    fallbackLocale: 'it_IT',
    silentTranslationWarn: true,
    silentFallbackWarn: true,
    messages: messages
}))
app.use(createPinia())

app.mount('#woocommerce-checkout-steps-app')
