# Waboot Custom Checkout

Funzionamento:

- Il form originale viene wrappato in #original-form-wrapper
- L'app VUE viene renderizzata in #woocommerce-checkout-steps-app
- L'app VUE compila il form originale (tramite i watch() nello store checkoutData)
- Arrivati allo step di pagamento (gestito dal componente Pay), il form originale viene spostato all'interno dell'app VUE. L'unica parte visibile del form originale è la parte del pagamento.

## Funzionamento Order Review

Il form originale è wrappato in #original-form-wrapper. Il `onMounted()` del componente `OrderReview.vue` clona `.woocommerce-checkout-review-order-table` dal form originale e lo appende all'interno del componente stesso, nel div `<div data-order-review-wrapper></div>`.
Il componente inoltre si attacca al trigger `updated_checkout` di `document.body` lanciato da WooCommerce e ripete l'operazione di clone ogni volta che il trigger viene eseguito.
Il risultato è una versione sempre aggiornata dell'order review all'interno del componente.

## Funzionamento Guest Login

In `SignInLanding.vue` c'è il bottone per continuare come guest. Quando viene cliccato, setta `continueAsGuest` a `TRUE` e chiama la funzione checkEmail().
Quando `SignInLanding.vue` emette `emailSubmitted`, invia anche `continueAsGuest` ad `App.vue`.
In `App.vue` questo parametro viene settato nello store; nello store c'è un `watch()` su `isGuest` che cambia la checkbox `createaccount` del form originale.

## Funzionamento avanzamento tra step

Il componente `App.vue` contiene tutti gli step in `v-if`. Lo step corrente è salvato nello store come `currentStep`.
Il `currentStep` è una stringa che rappresenta lo slug dello step.

# vue-project

This template should help get you started developing with Vue 3 in Vite.

## Recommended IDE Setup

[VSCode](https://code.visualstudio.com/) + [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (and disable Vetur) + [TypeScript Vue Plugin (Volar)](https://marketplace.visualstudio.com/items?itemName=Vue.vscode-typescript-vue-plugin).

## Type Support for `.vue` Imports in TS

TypeScript cannot handle type information for `.vue` imports by default, so we replace the `tsc` CLI with `vue-tsc` for type checking. In editors, we need [TypeScript Vue Plugin (Volar)](https://marketplace.visualstudio.com/items?itemName=Vue.vscode-typescript-vue-plugin) to make the TypeScript language service aware of `.vue` types.

If the standalone TypeScript plugin doesn't feel fast enough to you, Volar has also implemented a [Take Over Mode](https://github.com/johnsoncodehk/volar/discussions/471#discussioncomment-1361669) that is more performant. You can enable it by the following steps:

1. Disable the built-in TypeScript Extension
   1) Run `Extensions: Show Built-in Extensions` from VSCode's command palette
   2) Find `TypeScript and JavaScript Language Features`, right click and select `Disable (Workspace)`
2. Reload the VSCode window by running `Developer: Reload Window` from the command palette.

## Customize configuration

See [Vite Configuration Reference](https://vitejs.dev/config/).

## Project Setup

```sh
npm install
```

### Compile and Hot-Reload for Development

```sh
npm run dev
```

### Type-Check, Compile and Minify for Production

```sh
npm run build
```
