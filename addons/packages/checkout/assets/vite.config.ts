//import { fileURLToPath, URL } from 'url';

import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'
//import inject from "@rollup/plugin-inject";
import * as path from 'path'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        /*inject({
            $: 'jquery',
            jQuery: 'jquery'
        }),*/
        vue(),
    ],
    build: {
        assetsDir: '.',
        /*rollupOptions: {
            external: [
                "jquery",
            ]
        }*/
        sourcemap: 'inline',
    },
    resolve: {
        alias: {
            //'@': fileURLToPath(new URL('./src', import.meta.url)),
            "@": path.resolve(__dirname, "./src"),
            //vue: 'vue/dist/vue.esm-bundler.js'
        }
    }
})
