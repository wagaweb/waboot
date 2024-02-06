import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import {inject} from "vue";

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    inject({
      $: 'jquery',
      jQuery: 'jquery'
    }),
    vue(),
  ],
  build: {
    assetsDir: '.',
    rollupOptions: {
      external: [
        "jquery",
      ]
    }
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      //vue: 'vue/dist/vue.esm-bundler.js'
    }
  }
})
