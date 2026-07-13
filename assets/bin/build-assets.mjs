#!/usr/bin/env node
// Replacement for the old gulpfile.js (Gulp + Browserify + Babelify + gulp-terser).
// Produces the exact same output files as before — inc/hooks/assets.php hardcodes
// these paths/filenames with no manifest or content hash, so they must not change:
//   assets/dist/js/main.pkg.js   (unminified dev bundle, used when WP_DEBUG is true)
//   assets/dist/js/main.min.js   (+ .map — minified prod bundle, used otherwise)
//   assets/dist/css/main.min.css (+ .map — always enqueued)
//   assets/dist/css/gutenberg.min.css (loaded into the block editor via add_editor_style)
//
// Usage: node assets/bin/build-assets.mjs [--watch]

import { build as esbuildBuild, context as esbuildContext } from "esbuild";
import browserslistToEsbuild from "browserslist-to-esbuild";
import * as sass from "sass";
import postcss from "postcss";
import autoprefixer from "autoprefixer";
import cssnano from "cssnano";
import { fileURLToPath } from "node:url";
import path from "node:path";
import fs from "node:fs/promises";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const THEME_ROOT = path.resolve(__dirname, "..", "..");
const WATCH = process.argv.includes("--watch");

const PATHS = {
    jsEntry: path.join(THEME_ROOT, "assets/src/js/main.js"),
    jsOutDir: path.join(THEME_ROOT, "assets/dist/js"),
    jqueryShim: path.join(__dirname, "jquery-global-shim.js"),
    sassEntries: [
        { src: path.join(THEME_ROOT, "assets/src/sass/main.scss"), outName: "main.min.css" },
        { src: path.join(THEME_ROOT, "assets/src/sass/backend/gutenberg.scss"), outName: "gutenberg.min.css" },
    ],
    sassWatchDir: path.join(THEME_ROOT, "assets/src/sass"),
    cssOutDir: path.join(THEME_ROOT, "assets/dist/css"),
};

// esbuild's `target` option is the equivalent of what @babel/preset-env used to do:
// it tells esbuild which JS syntax it's safe to leave untouched vs. down-level for
// older browsers. Rather than hardcoding a target (which would silently drift out of
// sync over time), we derive it from the *same* `browserslist` field in package.json
// that autoprefixer already reads for CSS vendor-prefixing — one source of truth for
// both. browserslist-to-esbuild resolves package.json's `browserslist` field itself,
// no arguments needed.
const ESBUILD_TARGET = browserslistToEsbuild();

// Options shared by both JS builds below (dev + prod bundle).
const commonJsOptions = {
    entryPoints: [PATHS.jsEntry],
    bundle: true, // walk main.js's import graph and inline everything into one file, like Browserify did
    target: ESBUILD_TARGET,
    // `format: 'iife'` wraps the whole bundle in `(function(){ ... })()`. This matches
    // what Browserify produced: a plain, self-executing <script> with no module
    // exports for anything else to `import`. It's the right choice here (over 'esm' or
    // 'cjs') because WordPress enqueues this file as a normal classic script (no
    // `type="module"`, see inc/hooks/assets.php) — an ESM bundle would silently fail to
    // run since browsers refuse to execute `import`/`export` syntax outside a module
    // script tag.
    format: "iife",
    // Every `import $ from 'jquery'` in assets/src/js/** gets rewritten to import this
    // one-line local shim instead of the real npm `jquery` package. This is esbuild's
    // built-in module-resolution override (comparable to Webpack's `resolve.alias`) —
    // it's the direct replacement for the old Browserify `browserify-shim` config that
    // mapped `jquery` to `global:jQuery`. Without this, esbuild would bundle a full,
    // separate copy of jQuery, and the page would end up with two different jQuery
    // instances: WordPress's own (which owlCarousel/venobox attach themselves to) and
    // this bundle's private copy — breaking any code here that expects those plugins
    // to already be present on `$`.
    alias: { jquery: PATHS.jqueryShim },
    logLevel: "silent", // we print our own errors below instead of esbuild's default formatting
};

async function buildJsDev() {
    // Equivalent of the old `browserify(..., {debug: true})` step: unminified output
    // with the sourcemap embedded directly in the file as a base64 comment ("inline"),
    // so devtools can show original source without needing a separate .map request.
    await esbuildBuild({
        ...commonJsOptions,
        outfile: path.join(PATHS.jsOutDir, "main.pkg.js"),
        minify: false,
        sourcemap: "inline",
    });
}

async function buildJsProd() {
    // Equivalent of the old browserify bundle piped through gulp-terser: minified
    // output. `sourcemap: true` (as opposed to `'inline'` above) writes a *separate*
    // main.min.js.map file next to main.min.js and appends a
    // `//# sourceMappingURL=main.min.js.map` comment — matching gulp-sourcemaps'
    // `.write('.')` behavior, and keeping the shipped prod file itself smaller.
    await esbuildBuild({
        ...commonJsOptions,
        outfile: path.join(PATHS.jsOutDir, "main.min.js"),
        minify: true,
        sourcemap: true,
    });
}

async function buildJs() {
    await fs.mkdir(PATHS.jsOutDir, { recursive: true });
    await Promise.all([buildJsDev(), buildJsProd()]);
}

// Same Sass -> PostCSS(autoprefixer, cssnano) pipeline the gulpfile used, just called
// directly through each tool's own JS API instead of through Gulp stream wrappers
// (gulp-sass / gulp-postcss / gulp-rename / gulp-sourcemaps).
async function compileSassEntry({ src, outName }) {
    const cssOutPath = path.join(PATHS.cssOutDir, outName);
    const mapOutPath = `${cssOutPath}.map`;

    const sassResult = sass.compile(src, {
        sourceMap: true,
        sourceMapIncludeSources: true,
        style: "expanded", // leave minification to cssnano below, same division of labor as before
    });

    const postcssResult = await postcss([
        // autoprefixer() reads the `browserslist` field from package.json automatically,
        // same as it always has — unchanged from the old gulpfile.
        autoprefixer(),
        cssnano({ zindex: false }), // zindex:false preserved from the original gulpfile config
    ]).process(sassResult.css, {
        from: src,
        to: cssOutPath,
        map: { prev: sassResult.sourceMap, inline: false, annotation: path.basename(mapOutPath) },
    });

    await fs.writeFile(cssOutPath, postcssResult.css, "utf8");
    if (postcssResult.map) {
        await fs.writeFile(mapOutPath, postcssResult.map.toString(), "utf8");
    }
}

async function buildCss() {
    await fs.mkdir(PATHS.cssOutDir, { recursive: true });
    await Promise.all(PATHS.sassEntries.map(compileSassEntry));
}

async function buildOnce() {
    await Promise.all([buildJs(), buildCss()]);
}

async function watchMode() {
    // esbuild's `context()` gives an incremental build handle: call `.watch()` once
    // and esbuild keeps rebuilding automatically whenever any file in the entry's
    // import graph changes, using its own dependency graph (a strict subset of the
    // old `assets/src/js/**/*.js` glob gulp.watch used — every file actually imported
    // by main.js, no need to list them by hand).
    //
    // We need two *different* output files (unminified+inline-map vs minified+external
    // map) from the same entry point, and esbuild's watch context is built around one
    // fixed set of BuildOptions producing one output. The simplest, most direct way to
    // get both is two separate contexts watching in parallel — this mirrors the old
    // `gulp.series(compileJsBundle, minifyJs)` (two runs of the same input, different
    // options) rather than trying to be clever with a single shared context.
    const [devCtx, prodCtx] = await Promise.all([
        esbuildContext({ ...commonJsOptions, outfile: path.join(PATHS.jsOutDir, "main.pkg.js"), minify: false, sourcemap: "inline" }),
        esbuildContext({ ...commonJsOptions, outfile: path.join(PATHS.jsOutDir, "main.min.js"), minify: true, sourcemap: true }),
    ]);
    await fs.mkdir(PATHS.jsOutDir, { recursive: true });
    await Promise.all([devCtx.watch(), prodCtx.watch()]);

    // Sass/PostCSS have no comparable built-in incremental-watch API worth wiring up
    // for a build this small, so we just watch the source tree with chokidar (a
    // lightweight, well-established file watcher) and re-run the whole CSS pipeline
    // on every change — same net effect as `gulp.watch(paths.styles, compileCss)`.
    //
    // Note: as of chokidar v4, glob strings (e.g. "**/*.scss") are no longer resolved
    // to a set of watched paths at all — chokidar 4 dropped its bundled glob support,
    // so passing one silently watches nothing. We watch the whole `assets/src/sass`
    // directory instead (chokidar recurses into subdirectories on its own) and filter
    // to `.scss` files ourselves inside the event handler.
    const { default: chokidar } = await import("chokidar");
    chokidar.watch(PATHS.sassWatchDir, { ignoreInitial: true }).on("all", (event, changedPath) => {
        if (!changedPath.endsWith(".scss")) return;
        buildCss().catch((err) => console.error("[build-assets] CSS rebuild failed:\n", err));
    });

    await buildCss(); // initial compile, so watch mode starts from a fresh build like `gulp.series(parallel(build), watch)` did
    console.log("[build-assets] Watching assets/src/js/** and assets/src/sass/** for changes...");
}

async function main() {
    if (WATCH) {
        await watchMode();
    } else {
        await buildOnce();
    }
}

main().catch((err) => {
    console.error("[build-assets] Build failed:\n", err);
    process.exitCode = 1;
});
