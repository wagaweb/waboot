// Used by build-assets.mjs's esbuild `alias` option to resolve every
// `import $ from 'jquery'` in assets/src/js/** to WordPress's own global
// jQuery instance, instead of bundling a second, separate copy of jQuery.
//
// This replaces the old Browserify-era config (see package.json history):
//   "browserify-shim": { "jquery": "global:jQuery" }
// which made Browserify skip bundling the real `jquery` npm package and
// substitute references to the global `window.jQuery` instead. Some files
// under assets/src/js/ (e.g. cart.js, slidein.js) never `import` jquery at
// all and just read the `jQuery` global directly — this shim makes both
// styles resolve to the exact same object at runtime, which matters
// because jQuery plugins used here (owlCarousel, venobox) attach
// themselves to whichever jQuery instance the page actually loads via
// WordPress's `jquery` script handle (see inc/hooks/assets.php).
export default typeof window !== "undefined" ? window.jQuery : undefined;
