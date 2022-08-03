import { createApp } from 'vue';
import App from '@/components/App.vue';
import { store } from '@/store';

import '../scss/main.scss';

const entry = document.querySelector('#vue-shop-rules');
if (entry !== null) {
    const vm = createApp(App, {});
    vm.use(store);
    vm.mount(entry);
} else {
    console.error('No entry point element found to initiate product rules');
}
