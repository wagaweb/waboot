# Waboot Theme — Notes for Claude Code

Waboot is a WordPress theme for WooCommerce-based ecommerce sites, focused on speed, usability and modularity. See `readme.md` for the general overview (addons, build commands). This file documents the **build systems**, the **template system**, the **addons system**, the **database layer**, **logging**, the **view rendering system**, the **mail system** and the **alert system** in implementation detail — exact files, functions, hooks and fallback chains — as verified against the actual code.

For general coding guidelines (project structure conventions, coding principles, folder responsibilities) see [`.github/copilot-instructions.md`](./.github/copilot-instructions.md) — follow those when writing or modifying code in this theme.

## Build

```
composer install
npm install
npm run assets:build && npm run checkout:build:prod && npm run catalog:build:prod
```

There are **three independent build systems** in this repo — the main theme's own assets, and two addons (`checkout`, `catalog`) that each bundle their own frontend app. They use different tools, have separate `node_modules`/lockfiles, and produce output consumed by PHP in different ways (fixed filenames vs. glob-discovered hashed filenames). See "Build systems in detail" below for each one.

## Build systems in detail

### 1. Main theme assets — `assets/bin/build-assets.mjs` (esbuild + sass/postcss)

Root `package.json` scripts: `"assets:build": "node assets/bin/build-assets.mjs"` and `"assets:build:watch": "node assets/bin/build-assets.mjs --watch"`.

This replaced a Gulp + Browserify + Babelify + gulp-terser pipeline (the old `gulpfile.js`, removed) with a single, hand-written Node ESM script that calls esbuild and the `sass`/`postcss` JS APIs directly — no task runner. It has no CLI flags beyond `--watch`; everything else is hardcoded in `PATHS`/`commonJsOptions` inside the script.

**Why it looks the way it does — output filenames are load-bearing.** `inc/hooks/assets.php` hardcodes the exact output paths with no manifest/content-hash lookup, so the build script must keep producing these exact files:
- `assets/dist/js/main.pkg.js` — unminified JS bundle, inline sourcemap. Enqueued only when `WP_DEBUG` is true (`assets.php:13-14`).
- `assets/dist/js/main.min.js` (+ `main.min.js.map`) — minified JS bundle, external sourcemap. Enqueued when `WP_DEBUG` is false.
- `assets/dist/css/main.min.css` (+ `.map`) — always enqueued as `main-style` (`assets.php:38-39`).
- `assets/dist/css/gutenberg.min.css` — loaded into the block editor via `add_editor_style()` (`assets.php:69`). **This one file is actually committed to git** (`git ls-files assets/dist/` confirms it, unlike every other `assets/dist/*` path, which `.gitignore` excludes) — if you change `assets/src/sass/backend/gutenberg.scss`, remember to rebuild *and commit* this file, or the repo's copy silently drifts out of sync with the source (this already happened once before the esbuild migration).

**JS pipeline** (`buildJsDev()`/`buildJsProd()` in `build-assets.mjs`): a single esbuild entry point (`assets/src/js/main.js`, `bundle: true`, `format: 'iife'`) built twice — once unminified with `sourcemap: 'inline'` → `main.pkg.js`, once minified with `sourcemap: true` → `main.min.js`. `format: 'iife'` is required because WordPress enqueues this as a classic `<script>` (no `type="module"`); an ESM bundle would silently fail to execute.

**The jQuery alias — read this before touching JS deps.** `main.js` and 7 other files under `assets/src/js/` do `import $ from 'jquery'`; a handful of others (`cart.js`, `slidein.js`, `catalogFilters.js`) instead assume a bare global `let $ = jQuery`. **The real `jquery` npm package is not actually installed** — it never needs to be, because `commonJsOptions.alias` (`build-assets.mjs`) maps `jquery` → `assets/bin/jquery-global-shim.js`, a one-line module that does `export default window.jQuery`. This reproduces what the old Browserify build did via a `browserify-shim` config (`"jquery": "global:jQuery"`, now removed from `package.json`). Do **not** remove the alias or add `jquery` as a real devDependency: WordPress enqueues its own `jquery` handle as a dependency of `main-js` (`assets.php:22`), and jQuery plugins used here (`owlCarousel`, `venobox`) attach themselves to that global instance — bundling a second, separate copy of jQuery would silently break them (two different `$` instances on the page).

**CSS pipeline** (`compileSassEntry()`): for each of `assets/src/sass/main.scss` → `main.min.css` and `assets/src/sass/backend/gutenberg.scss` → `gutenberg.min.css`: `sass.compile()` (dart-sass, `style: 'expanded'`) → `postcss([autoprefixer(), cssnano({zindex: false})])`. Autoprefixer's targets, and esbuild's JS `target` (via the `browserslist-to-esbuild` package, replacing what `@babel/preset-env` used to do), both come from the **same** `browserslist` field in `package.json` — kept as one source of truth for both pipelines.

**Watch mode gotcha.** `chokidar` (v4) **dropped glob-string support** — `chokidar.watch('assets/src/sass/**/*.scss')` silently watches nothing (no error, no files). `build-assets.mjs` watches the whole `assets/src/sass` directory instead and filters to `.scss` paths inside the event handler (`watchMode()`, see the comment above the `chokidar.watch(...)` call). If you ever touch the watch logic, don't reintroduce a glob string — test it, since the failure mode is silent (watch mode starts fine, logs "Watching...", and simply never rebuilds CSS).

Dead/no-longer-needed npm scripts and config removed as part of the esbuild migration: the `"browserify"`/`"browserify-shim"` blocks in `package.json`, and all `gulp*`/`browserify*`/`babel*`/`vinyl-*`/`merge-stream` devDependencies.

### 2. Checkout addon — Vite + Vue 3 SPA (`addons/packages/checkout/assets/`)

Separate `node_modules`/`package-lock.json` from the theme root. Root scripts: `checkout:build:dev`, `checkout:build:watch`, `checkout:build:prod` (`package.json:19-21`) — each does `cd addons/packages/checkout/assets && npm i && npm run <script>`, so the addon's own deps get (re)installed every time these are run.

Addon-local scripts (`addons/packages/checkout/assets/package.json`): `dev`, `build: "vue-tsc -b && vite build"`, `build-dev: "vue-tsc -b && vite build --mode development"`, `build-dev:watch: "vue-tsc -b && vite build --mode development --watch"`. Type-checking (`vue-tsc -b`) always runs before the Vite build, even in dev.

Single entry point: `index.html` → `src/main.ts`, a full Vue 3 SPA (11 `.vue` SFCs under `src/components/`, including the recently added `BillingDataStep.vue`/`ShippingDataStep.vue`). `vite.config.ts` uses the `@vitejs/plugin-vue` plugin, `build.assetsDir: '.'` (flattens output instead of nesting under an `assets/` subfolder), `build.sourcemap: 'inline'`, and a `@` → `./src` resolve alias. No `manifest.json` is generated. `vite.config.ts` has commented-out `inject`/`rollupOptions.external` config for `jquery` — it's unused leftover, not a gap: components that need jQuery (`AddressesForm.vue`, `Pay.vue`, `OrderReview.vue`, `stores/checkoutData.ts`) read `window.jQuery` directly rather than `import`ing the `jquery` package, so there's nothing for Vite to alias/externalize in the first place. `jquery` is still listed in `package.json` dependencies but isn't actually imported anywhere.

**Output filenames are content-hashed** (Vite's default), e.g. `dist/index-SZ2YHUa4.js` / `dist/index-zAOh1Wmt.css` — unlike the main theme and the catalog addon, there is no fixed name to hardcode. PHP handles this by **globbing** at request time (`addons/packages/checkout/hooks/hooks.php:14-15,63`):
```php
$jsFiles = glob($assetsDir.'/index-*.js');
$mainJsFilePath = array_shift($jsFiles); // assumes exactly one match
```
If a build ever leaves more than one `index-*.js`/`index-*.css` in `dist/` (e.g. a stale file from a previous hash not cleaned up), this silently picks whichever `glob()` returns first — clean the `dist/` directory before rebuilding if that's a concern. The discovered script is forced to `type="module"` via a `script_loader_tag` filter keyed on the `step-checkout-main-js` handle (`hooks.php:80-86`), since Vite's output is an ES module. A second, non-bundled helper (`order-review-manager.js`) is enqueued by fixed path alongside it (`hooks.php:57-62`) — it isn't part of the Vite build at all.

### 3. Catalog addon — Webpack 5 + Vue 3 + TypeScript (`addons/packages/catalog/assets/`)

Also its own separate `node_modules`/lockfile. Root scripts: `catalog:build:dev` / `catalog:build:prod` (`package.json:17-18`), same `cd ... && npm i && npm run <script>` pattern. Addon-local scripts (`assets/package.json:7-8`): `dev: "webpack"`, `prod: "webpack --env prod"`.

Single entry `src/main.ts` (`webpack.config.js:10`), Vue SFCs via `vue-loader`/`@vue/compiler-sfc`, TS via `ts-loader`, CSS/Sass extracted via `MiniCssExtractPlugin`. `jquery` (and two globals, `gtag`, `JVMWooCommerceWishlist`) are marked `externals` (`webpack.config.js:21-25`) — same "don't bundle a second jQuery" concern as the main theme, solved via Webpack's native `externals` instead of an alias-to-shim.

**Output filenames are fixed, not hashed** — `env.prod` picks between them (`webpack.config.js:12,57`): `catalog.js`/`catalog.css` (dev) vs `catalog.min.js`/`catalog.min.css` (prod), both written to `dist/` (dev and prod artifacts can coexist there; nothing cleans one when building the other). Because names are fixed, PHP just branches on `SCRIPT_DEBUG` instead of globbing (`addons/packages/catalog/bootstrap.php:14-36`):
```php
'uri' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ?
    getAddonDirectoryURI('catalog') . '/assets/dist/catalog.js' :
    getAddonDirectoryURI('catalog') . '/assets/dist/catalog.min.js',
```
Assets are registered through `AssetsManager`, filterable via `apply_filters('catalog_addon_assets', $assets)` (`bootstrap.php:37`). Note: `catalog-style`'s `path` key (`bootstrap.php:32-33`) points at `catalog.js`/`catalog.min.js` instead of the `.css` file — a pre-existing copy/paste bug in the asset definition array (the `uri` key, which is what actually gets `<link>`-ed, is correct; only the secondary `path` value is wrong).

### 4. Comparison at a glance

| | Main theme | Checkout addon | Catalog addon |
|---|---|---|---|
| Tool | esbuild + sass/postcss (custom script) | Vite 6 | Webpack 5 |
| UI framework | none (plain JS) | Vue 3 (SPA) | Vue 3 + TypeScript |
| Output filenames | fixed | content-hashed | fixed |
| PHP asset discovery | hardcoded paths, `WP_DEBUG` branch | `glob()` at request time | hardcoded paths, `SCRIPT_DEBUG` branch |
| jQuery handling | esbuild `alias` → shim module | not needed — code reads `window.jQuery` directly, never `import`s it (the commented-out `inject`/`external` config in `vite.config.ts` is unused leftover) | Webpack `externals` |
| `node_modules`/lockfile | theme root | own (`addons/packages/checkout/assets/`) | own (`addons/packages/catalog/assets/`) |

## Template system — how it actually works

### 1. Entry point: `index.php`

`index.php` (theme root) is a **fixed layout**, not a router:

```php
get_header();
get_template_part('templates/wrapper', 'start');
do_action('waboot/layout/content');
get_template_part('templates/wrapper', 'end');
get_footer();
```

- `get_header()` / `get_footer()` are called directly here.
- `get_sidebar()` is **not** called here — it's called inside `templates/wrapper-end.php:3`, which this file includes via `get_template_part('templates/wrapper', 'end')`.
- There is no root-level `single.php`, `page.php`, `archive.php` or `home.php` — only `header.php`, `footer.php`, `sidebar.php`, `index.php`, `functions.php` live in the theme root. All classic template files live under `templates/`.

### 2. The routing action: `waboot/layout/content`

`index.php` only fires the action `waboot/layout/content`. Confirmed via a theme-wide search — exactly **one** callback is hooked to it:

- `Waboot\inc\core\addMainContent()` — `inc/core/hooks.php:10-59`, registered at line 59 with `add_action('waboot/layout/content', __NAMESPACE__.'\\addMainContent')`.

`addMainContent()` does the actual dispatch:

1. **Page classification** — `Utilities::getCurrentPageType()` (trait `Query`, `inc/core/utils/Query.php:11-25`):
   - `is_front_page() && is_home()` → `PAGE_TYPE_DEFAULT_HOME`
   - `is_front_page()` (only) → `PAGE_TYPE_STATIC_HOME`
   - `is_home()` (only) → `PAGE_TYPE_BLOG_PAGE`
   - else → `PAGE_TYPE_COMMON`

2. **Template dispatch** (`inc/core/hooks.php:18-51`):

   | Page type | Condition | Template part loaded |
   |---|---|---|
   | `default_home` | — | `templates/blog` |
   | `static_home` | — | `templates/page` |
   | `blog_page` | — | `templates/blog` |
   | `common` | `is_attachment() && wp_attachment_is_image()` | `templates/image` |
   | `common` | `$wp_query->is_single()` | `templates/single` |
   | `common` | `$wp_query->is_page()` | `templates/page` |
   | `common` | `$wp_query->is_author()` | `templates/archive` |
   | `common` | `$wp_query->is_search()` | `templates/search` |
   | `common` | `$wp_query->is_archive()` | `templates/archive` |
   | `common` | `$wp_query->is_404()` | `templates/404` |
   | `common` | none of the above | throws `Exception('Unrecognized content type')` |

3. **The actual "router" extension point** — the filter `waboot/layout/content/template` (`inc/core/hooks.php:55`):

   ```php
   $tpl_part = apply_filters('waboot/layout/content/template', $tpl_part, $page_type);
   get_template_part($tpl_part[0], $tpl_part[1]);
   ```

   `$tpl_part` is computed by the `if/elseif` chain above, then passed through this filter **before** `get_template_part()` is called. This is the pluggable point: a child theme or plugin can hook this filter to override which template part loads for a given `$page_type`, without editing core theme code. Do not confuse this with `addMainContent()` itself — that function only supplies the *default* decision.

### 3. Archive fallback chain

`templates/archive.php` (7 lines) delegates to a helper instead of containing its own naming logic:

```php
$tpl = \Waboot\inc\core\getArchiveTemplate();
if (!empty($tpl)) {
    \Waboot\inc\core\Waboot()->renderView($tpl, []);
} else {
    \Waboot\inc\core\Waboot()->renderView('templates/archive/archive.php', []);
}
```

`getArchiveTemplate()` — `inc/core/template-functions.php:25-62` — builds a fallback list (most specific first), rooted at `templates/archive/`:

- **Author archive** (`is_author()`): `author-{user_nicename}` → `author-{ID}` → `author`
- **Category term**: `category-{slug}` → `category-{term_id}` → `category`
- **Other taxonomy term**: `{taxonomy}-{slug}` → `taxonomy-{taxonomy}-{slug}` → `taxonomy-{taxonomy}` → `taxonomy`
- **Post type archive**: `archive-{post_type}` only (no fallback list)
- **Date archive** (`is_date()`): `date`
- Otherwise: empty string

The list is resolved by `locateTemplate()` (`inc/core/template-functions.php:72-96` — a custom variant of `locate_template()` that returns the bare template name instead of the full path), which checks, in order: child theme (`STYLESHEETPATH`) → parent theme (`TEMPLATEPATH`) → `wp-includes/theme-compat/`. If nothing resolves, `archive.php` falls back to the generic `templates/archive/archive.php`.

As of the checked-out code, **only `templates/archive/archive.php` actually exists** in that directory — all the more specific overrides (`author-*.php`, `category-*.php`, `taxonomy-*.php`, `archive-{posttype}.php`, `date.php`) are optional files a child theme/dev can add.

### 4. Author templates — removed in v3.1.0

There is **no** `templates/author.php` and **no** `templates/author/` directory. A comment in `inc/core/hooks.php:37` states explicitly:

```php
}elseif($wp_query->is_author()){
    $tpl_part = ['templates/archive',null]; //From 3.1.0 we do not use the author.php anymore
```

Author archives are routed straight into `templates/archive.php` and follow the same fallback chain as any other archive (§3), under `templates/archive/`, not a separate `templates/author/` folder.

### 5. Custom template partials (dashboard-selectable templates)

Registration — `injectTemplates()` in `inc/core/hooks.php:70-88`, hooked via `add_filter('theme_page_templates', __NAMESPACE__."\\injectTemplates", 999, 3)`:

```php
$template_directory = get_stylesheet_directory() . '/templates/parts-tpl';
$template_directory = apply_filters('waboot/custom_template_parts_directory', $template_directory);
$tpls = glob($template_directory . '/content-*.php');
foreach ($tpls as $tpl) {
    $basename = basename($tpl);
    preg_match('/^content-([a-z_-]+)/', $basename, $matches);
    $name = $matches[1] ?? null;
    if (!$name) continue;
    $page_templates[$name] = str_replace('_', ' ', ucfirst($name)) . ' ' . _x('(parts)', 'Waboot Template Partials', LANG_TEXTDOMAIN);
}
```

Key details:

- Matching is a **glob** (`content-*.php`) further filtered by the regex `^content-([a-z_-]+)` — the allowed slug charset is `[a-z_-]+` (lowercase letters, underscore, hyphen), not just `[a-z]+`.
- The directory is filterable via `waboot/custom_template_parts_directory` — it isn't hardcoded to `templates/parts-tpl`.
- The dashboard dropdown label is **derived from the filename**, not from a WordPress `Template Name:` header comment: underscores become spaces, first letter uppercased (`ucfirst`), suffixed with a localized `" (parts)"`. E.g. `content-my_tpl.php` → key `my_tpl` → label `"My tpl (parts)"`.
- As shipped, `templates/parts-tpl/` is empty except for a `.gitkeep` — it's a scaffold directory meant to be populated per-project.

Consumption — `templates/page.php` (16 lines):

```php
$required_tpl = get_post_meta(get_the_ID(), '_wp_page_template', true);
if (preg_match('/.php/', $required_tpl)) $required_tpl = 'page';

if (locate_template('templates/parts-tpl/content-' . $required_tpl . '.php', false, false) != '') {
    get_template_part('templates/parts-tpl/content', $required_tpl);
} else {
    get_template_part('templates/parts/content', 'page');
}
```

If the selected `_wp_page_template` value looks like a `.php` filename (i.e. it's a normal WP template file, not one of the injected slugs), it's forced back to `'page'`. Otherwise it checks whether `templates/parts-tpl/content-{slug}.php` exists and includes it; if not, it falls back to `templates/parts/content-page.php`.

### 6. `templates/` directory layout (as checked out)

- Root dispatch templates: `404.php`, `archive.php`, `blog.php`, `single.php`, `search.php`, `page.php`, `comments.php`, `wrapper-start.php`, `wrapper-end.php`
- `templates/archive/` — archive sub-templates (only `archive.php` present by default; see §3 for the naming convention of optional overrides)
- `templates/parts/` — generic content partials (e.g. `content-page.php`, used as the final fallback by `page.php`)
- `templates/parts-tpl/` — dashboard-selectable custom template partials (empty scaffold; see §5)
- `templates/view-parts/` — smaller view partials (header/footer/title/pagination, etc.)

### 7. Hooks/filters reference

| Hook | Type | Where fired | Purpose |
|---|---|---|---|
| `waboot/layout/content` | action | `index.php:9` | Triggers main content rendering; `addMainContent()` is the only hooked callback |
| `waboot/layout/content/template` | filter | `inc/core/hooks.php:55` | Overrides the `[template, name]` pair chosen by `addMainContent()` before `get_template_part()` runs — the actual routing override point |
| `theme_page_templates` | filter (WP core) | `inc/core/hooks.php:88` | `injectTemplates()` adds `templates/parts-tpl/content-*.php` files to the dashboard's page template dropdown |
| `waboot/custom_template_parts_directory` | filter | `inc/core/hooks.php:72` | Overrides the directory scanned for custom template partials (default `templates/parts-tpl`) |

## Addons system

Addons are small, self-contained "mini plugins" dedicated to the theme, living under `addons/packages/<addon-name>/`. Every subdirectory of `addons/packages/` is treated as an addon; each is active by default and can be disabled through a filter.

### 1. Loading chain

1. `functions.php` (theme root, lines 41-46) registers the default disabled-addons list and then calls `\Waboot\inc\loadAddons()`:
   ```php
   add_filter('waboot/addons/disabled', function(){
       return [
           'invoicing'
       ];
   });
   \Waboot\inc\loadAddons();
   ```
   The comment above it warns: *"Do not keep enabled 'invoicing' if step-checkout.php is enabled in 'checkout' addon bootstrap.php"* — the `invoicing` addon conflicts with the checkout addon's step-checkout feature, which is why it's disabled out of the box.

2. `loadAddons()` — `inc/bootstrap.php:19-21` — just requires `addons/bootstrap.php`.

3. `addons/bootstrap.php` does the actual work:
   ```php
   namespace Waboot\addons;

   require_once __DIR__.'/functions.php';
   require_once __DIR__.'/shared-functions.php';
   require_once __DIR__.'/shared-hooks.php';

   foreach (getAddons() as $addonName){
       $btf = getAddonDirectory($addonName).'/bootstrap.php';
       if(is_file($btf)){
           require_once $btf;
       }
   }
   ```
   It first loads the shared helpers/hooks common to every addon, then loops over the list returned by `getAddons()` and requires each addon's own `bootstrap.php`, if that file exists (an addon folder without a `bootstrap.php` is simply skipped — no error).

### 2. Enable/disable mechanics — `addons/functions.php`

```php
function getAddonDirectory($addon){
    return get_template_directory().'/addons/packages/'.$addon;
}

function getAddons(){
    $basedir = get_template_directory().'/addons/packages';
    $disabledAddons = getDisabledAddons();
    $addons = array_filter(scandir($basedir), function($filename) use($basedir, $disabledAddons){
        return is_dir($basedir.'/'.$filename) &&
            !\in_array($filename, ['.','..']) &&
            !in_array($filename, $disabledAddons, true);
    });
    return $addons;
}

function getDisabledAddons(): array{
    return apply_filters('waboot/addons/disabled', []);
}
```

- `getAddons()` simply `scandir()`s `addons/packages/`, keeps directories, drops `.`/`..`, and drops any name present in `getDisabledAddons()`.
- `getDisabledAddons()` is `apply_filters('waboot/addons/disabled', [])` — the default is an **empty array**, i.e. every addon folder is active unless something explicitly excludes it.
- There is no addon-load-order mechanism: iteration order follows whatever `scandir()` returns (filesystem-dependent, typically alphabetical), and addons cannot declare dependencies on one another.

### 3. How to disable an addon — `waboot/addons/disabled` filter

To disable an addon, hook `waboot/addons/disabled` and return the list of directory names (under `addons/packages/`) to exclude. This must run **before** `\Waboot\inc\loadAddons()` is called in `functions.php` — in practice this means from a child theme's `functions.php` (WordPress loads a child theme's `functions.php` before the parent's, so the filter is already registered by the time the parent calls `loadAddons()`), or from an early-loaded file required by it.

```php
add_filter('waboot/addons/disabled', function($disabled){
    $disabled[] = 'sizeguide';
    return $disabled;
});
```

**Gotcha — priority matters here.** The theme's own registration in `functions.php:41-45` uses a closure with **no parameters** (`function(){ return ['invoicing']; }`), so it ignores whatever value was passed in and unconditionally returns `['invoicing']`. Both that callback and a child theme's callback default to priority `10`, and WordPress runs same-priority callbacks in registration order; since the child theme's `functions.php` is required first, its callback runs *before* the parent's — meaning the parent's callback then discards the child's contribution and returns `['invoicing']` only, silently undoing any additional addon a child theme tried to disable at the default priority. To make additions stick, hook at a **lower priority number** so your callback runs before the parent's default and gets discarded anyway — no, that doesn't help either. The only reliable option is a **higher priority number** than the parent's default `10`, so your callback runs *after* it and receives `['invoicing']` as `$disabled`, which it can then extend:

```php
add_filter('waboot/addons/disabled', function($disabled){
    $disabled[] = 'sizeguide';
    return $disabled;
}, 20); // runs after the theme's own priority-10 callback
```

### 4. Anatomy of an addon package

Each folder under `addons/packages/` is a self-contained unit. Conventions observed across the shipped addons (`attributes`, `cart`, `catalog`, `checkout`, `clear_opcache`, `invoicing`, `manage_personal_data`, `product_gallery`, `sizeguide`, `star_rating`):

- `bootstrap.php` — entry point, auto-required by `addons/bootstrap.php` if present. Namespaced `Waboot\addons\packages\{addon_name}`.
- Optional `functions.php`, `hooks.php` or a `hooks/` folder (e.g. `checkout/hooks/{backend,hooks,coupons,fields,layout}.php`), `templates/`, `assets/`, `cli/` — same organizational pattern as the theme's own `inc/` folder, scoped to the addon.
- Simple addons can be a single `bootstrap.php` registering `add_action`/`add_filter` calls directly (e.g. `star_rating/bootstrap.php`). Larger ones (`checkout`, `invoicing`) split into multiple files and `require_once` them from `bootstrap.php`, typically via `getAddonDirectory('{addon}')` to build absolute paths, or `\Waboot\inc\core\safeRequireFiles()` with theme-relative paths (see `inc/core/template-functions.php:10-18` for that helper).
- Helpers available to every addon (namespace `Waboot\addons`, from `addons/functions.php` and `addons/shared-functions.php`): `getAddonDirectory($addon)`, `getAddonDirectoryURI($addon)`, `getAddons()`, `getDisabledAddons()`, plus cross-addon utilities such as `getWCProductFromCartData()`. `addons/shared-hooks.php` is a scaffold file (currently empty) meant for hooks shared by multiple addons.

### 5. Hooks/filters reference (addons)

| Hook | Type | Where fired | Purpose |
|---|---|---|---|
| `waboot/addons/disabled` | filter | `addons/functions.php:42` (`getDisabledAddons()`) | Returns the list of addon directory names to exclude from loading. Default `[]` (all enabled); the theme itself adds `invoicing` at default priority 10 |

## Database layer (`illuminate/database`)

Waboot bundles `illuminate/database` (Laravel's Capsule/query builder, pinned to `v8.83.27` in `composer.json`) alongside the classic `$wpdb`, to run structured queries with a fluent API against the *same* WordPress database/connection.

### 1. Connection bootstrap — `inc/core/DB.php`

`Waboot()->DB()` (`inc/core/Theme.php:89-92`) just returns `DB::getInstance()`:

```php
public function DB(): DB
{
    return DB::getInstance();
}
```

`DB` (`inc/core/DB.php`) is a **lazy singleton**:

```php
public static function getInstance(): ?DB
{
    static $instance = null;
    if (null === $instance) {
        $instance = new static();
    }
    return $instance;
}
```

The `protected` constructor (lines 32-55) runs only on the very first call. It:
1. Verifies `\Illuminate\Database\Capsule\Manager` exists, otherwise throws `DBUnavailableDependencyException`.
2. Instantiates `new Manager()` and calls `addConnection([...])` with driver `mysql` and credentials read **directly from the `wp-config.php` constants** `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` (charset `utf8`, collation `utf8_unicode_ci`) — no separate config file, no `.env`.
3. Sets `'prefix' => $wpdb->prefix` (global `$wpdb`), so Illuminate queries automatically share WordPress's table prefix (e.g. `wp_`).
4. Calls `$capsule->setAsGlobal()`, registering Capsule as the global static instance — this is what makes `Manager::table()` callable statically elsewhere (e.g. from the `Query` facade).

Nothing in `Theme::loadDependencies()` (`inc/core/Theme.php:39-65`) touches the DB — the connection is only opened on the **first** `Waboot()->DB()` call in a request, not eagerly at bootstrap.

### 2. `DB` public API

- `getQueryBuilder(): Manager` (lines 62-68) — returns the Capsule `Manager`; throws `DBException` if `$queryBuilder` isn't set (shouldn't normally happen since the constructor always sets it).
- `getSchemaBuilder(): Builder` (lines 82-85) — `$this->getQueryBuilder()->schema()`, Illuminate's Schema Builder for DDL (`create`, `dropIfExists`, `hasTable`, ...).
- `getWPDB(): wpdb` / `getDBPrefix(): string` (lines 90-102) — direct access to the classic global `$wpdb` and its `prefix`, for code that needs to mix both APIs.
- `tableExists(string $tableName): bool` (lines 108-115) — wraps `getSchemaBuilder()->hasTable()` in a try/catch, returning `false` on `DBException` instead of throwing.
- `static queryTable(string $table): \Illuminate\Database\Query\Builder` (lines 120-123) — shortcut equivalent to what the `Query` facade does (see below).

### 3. The `Query` facade — `inc/core/facades/Query.php`

```php
namespace Waboot\inc\core\facades;

class Query
{
    static function on(string $table): \Illuminate\Database\Query\Builder
    {
        return Waboot()->DB()->getQueryBuilder()::table($table);
    }
}
```

A single static method, `Query::on($table)`: syntactic sugar over `Waboot()->DB()->getQueryBuilder()::table($table)` (possible statically only because of the `setAsGlobal()` call above). It returns a plain `Illuminate\Database\Query\Builder`, ready for the full fluent API — `->select()`, `->where()`, `->get()`, `->first()`, `->insertGetId()`, `->updateOrInsert()`, `->delete()`, etc.

**Naming collision to be aware of:** there is a *second, unrelated* `Query` in `inc/core/utils/Query.php` — a trait (`Waboot\inc\core\utils\Query`) used for WP page-type detection (`getCurrentPageType()`, `isStaticHome()`...). It has nothing to do with the database; only the facade under `inc/core/facades/` is the DB one.

### 4. Usage patterns found in the codebase

- Fluent read (`inc/order_stats/functions.php:168-171`):
  ```php
  $existingRecord = Query::on(getOrderStatsTableName())->select('*')->where([
      ['product_id', '=', $productId],
      ['order_id', '=', $orderId]
  ])->get()->first();
  ```
- Upsert (`inc/order_stats/functions.php:227-230`):
  ```php
  Query::on(getOrderStatsTableName())->updateOrInsert(
      ['product_id' => $productId, 'order_id' => $orderId],
      $row
  );
  ```
- DDL via the Schema Builder, accessed through `Waboot()->DB()` directly rather than the `Query` facade (`inc/core/woocommerce/addresses/ShippingAddressRepository.php:34-49`):
  ```php
  Waboot()->DB()->getSchemaBuilder()->create(self::TABLE_NAME, function (Blueprint $table){
      $table->id();
      $table->integer('user_id');
      // ...
  });
  ```
- Repository base classes grab the `Manager` once in their constructor instead of calling the facade per-query (`inc/core/repositories/AbstractRepository.php:16-21`): `$this->db = Waboot()->DB()->getQueryBuilder();`.

### 5. What's *not* there

No Eloquent models, no migrations, no seeders — only the Query Builder and Schema Builder are used. Custom tables are created ad hoc inside repository classes (`ShippingAddressRepository`, `BillingAddressRepository`, `CustomerRepository` under `inc/core/woocommerce/...`) or CLI commands (`inc/cli/GenerateOrderStatsTable.php`), typically on-demand rather than through a versioned migration system.

## Logging (Monolog)

Waboot bundles `monolog/monolog` (`^3.8`) for structured, file-based logging, independent of PHP's error log and of the alert/notification system (see below). Three layers are involved: `LoggerFactory` (creates raw Monolog loggers), `Theme::logToFile()` (orchestrates channels/caching/dispatch), and `inc/core/helpers/logs.php` (the public, easy-to-call functions).

### 1. `LoggerFactory` — `inc/core/LoggerFactory.php`

The lowest layer; actually instantiates `Monolog\Logger`:

```php
public static function create(string $name, string $logFileName, \DateTimeZone $tz = null, array $params = []): Logger
{
    if(!self::monologExists()){
        throw new LoggerFactoryException('Monolog not installed');
    }
    $params = wp_parse_args($params, [
        'level' => Logger::DEBUG,
        'dateFormat' => 'Y-m-d\TH:i:s',
        'outputFormat' => "[%datetime%][%channel%][%level_name%]: %message% %context% %extra%\n",
        'formatter' => null,
    ]);
    // ...creates the log directory (wp_mkdir_p) and file (touch) if missing...
    $logger = new Logger($name,[],[],$tz);
    $stream = new StreamHandler($logFileName,$params['level']);
    $stream->setFormatter($params['formatter'] ?: new LineFormatter($params['outputFormat'], $params['dateFormat']));
    $logger->pushHandler($stream);
    return $logger;
}
```

Key points:
- Single handler: `Monolog\Handler\StreamHandler` writing straight to the given file path, with a custom `Monolog\Formatter\LineFormatter` (`[datetime][channel][level_name]: message context extra`).
- **No `RotatingFileHandler`** — Monolog itself doesn't rotate anything; "rotation" is achieved one layer up by generating a different file name per day (see next section).
- Creates the log directory/file on demand (`wp_mkdir_p` + `touch`), throwing `LoggerFactoryException` if either fails.
- `monologExists(): bool` — `class_exists('Monolog\Logger')` guard used before any of the above.

### 2. `Theme::logToFile()` — `inc/core/Theme.php:151-190`

The orchestration layer, called by every helper in `logs.php`:

```php
public function logToFile(string $loggerIdentifier, string $logMessage, int $logLevel = MonologLoggingLevels::INFO, array $context = [], \DateTimeZone|null $dz = null): void
{
    try{
        if($dz === null){ $dz = Dates::getDefaultDateTimeZone(); }
        $logger = $this->registeredFileLoggers[$loggerIdentifier] ?? null;
        if($logger === null){
            $logFile = WP_CONTENT_DIR.'/logs/'.$loggerIdentifier.'-'.(new \DateTime('now', $dz))->format('Y-m-d').'.log';
            $logger = LoggerFactory::create($loggerIdentifier, $logFile);
            $this->registeredFileLoggers[$loggerIdentifier] = $logger;
        }
        switch($logLevel){
            case MonologLoggingLevels::DEBUG: $logger->debug($logMessage,$context); break;
            case MonologLoggingLevels::INFO: $logger->info($logMessage,$context); break;
            // ...NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY...
        }
    }catch (\Exception | \Throwable $e){}
}
```

- `$loggerIdentifier` is an arbitrary **channel name**, chosen by the caller — it doubles as the log file's prefix.
- Loggers are cached per-request in `$this->registeredFileLoggers` (instance array, `inc/core/Theme.php:21`), so repeated calls with the same identifier reuse the same `Monolog\Logger`/`StreamHandler` instead of recreating it.
- The log file path is always **`WP_CONTENT_DIR.'/logs/{identifier}-{Y-m-d}.log'`** — i.e. `wp-content/logs/`, not inside the theme folder — with one file per channel **per day** (the date is baked into the filename, not handled by a Monolog rotating handler).
- The whole method is wrapped in a silent `try/catch (\Exception|\Throwable $e){}` (line 189) — **logging failures are swallowed**, never surfaced or thrown further.
- Log levels are the `MonologLoggingLevels` constants (`inc/core/helpers/MonologLoggingLevels.php:239-246`: `DEBUG=0, INFO=1, NOTICE=2, WARNING=3, ERROR=4, CRITICAL=5, ALERT=6, EMERGENCY=7`), duplicated as public constants on `Theme` itself (`LOG_LEVEL_*`, lines 11-18) for convenience.
- `Theme::logError(\Exception|\Throwable $e, string $source = '', array $context = [])` (lines 135-141) is an instance-level shortcut that always logs to the `waboot-log` channel at `ERROR` level.

### 3. Public helpers — `inc/core/helpers/logs.php`

All are namespaced functions (`Waboot\inc\core\helpers`) that ultimately call `Waboot()->logToFile()`:

- `logToFile($loggerIdentifier, $logMessage, $logLevel = INFO, $context = [], $dz = null)` — thin wrapper, full control over channel/level.
- `logInfoToFile`, `logWarningToFile`, `logErrorToFile($loggerIdentifier, $logMessage, $context = [], $dz = null)` — same, with the level fixed.
- `logInfo`, `logWarning`, `logError($message, $source, $context = [], $fileName = 'waboot-log')` — higher-level shortcuts: default channel is **`waboot-log`**, and if `$source` is non-empty it's merged into `$context['source']` before writing.
- `logException(\Exception|\Throwable $e, $source, $context = [], $fileName = 'waboot-log')` — same pattern as `logError`, logging `$e->getMessage()`.

All of them are `void` — fire-and-forget, no return value to check.

### 4. Usage patterns / existing channels

The channel name is entirely caller-defined; there's no fixed registry. Observed in the codebase:

- `waboot-log` — the default channel used by `logInfo`/`logWarning`/`logError`/`logException` when no `$fileName` is passed (e.g. `inc/order_stats/functions.php:177,233`).
- `waboot-debug` — used directly via `Waboot()->logToFile('waboot-debug', $e->getMessage())` in `inc/core/woocommerce/Customer.php:117,141,160`.
- `wawoo-multiaddress-debug` — a feature-specific channel, e.g. `inc/hooks/woocommerce/addresses.php:119,207`: `logException($e, 'woocommerce_checkout_order_created', [], 'wawoo-multiaddress-debug');`.
- `waboot-mail-logger` — the `Mail`/`SESMail` classes (`inc/core/mail/Mail.php:376`) call `LoggerFactory::create()` **directly**, bypassing `Theme::logToFile()` entirely, and manage their own log file path via `getLogFile()`.
- `waboot-cli-command-logger` — WP-CLI commands (`inc/core/cli/AbstractCommand.php`, `CommandLoggerTrait`) also call `LoggerFactory::create()` directly, but write under a **separate directory**, `WP_CONTENT_DIR.'/cli-logs/{logDirName}/{logFileName}-{Y-m-d}.log'`, rather than `wp-content/logs/`.

Every channel created through `Theme::logToFile()` lands in `wp-content/logs/{channel}-{date}.log`; channels created by calling `LoggerFactory::create()` directly (Mail, CLI) define their own path and aren't subject to that convention.

### 5. Relationship with the alert system (`inc/core/alert/`)

The classes under `inc/core/alert/` (`Alert`, `AlertDispatcher`, `AbstractAlertDispatcher`, `AlertDispatcherFactory`, and dispatchers `FileAlertDispatcher`, `EmailAlertDispatcher`, `GoogleChatDispatcher`) are a **separate, Monolog-independent notification system** — they push alerts to a file (`file_put_contents(..., FILE_APPEND)` on a `.alerts` file, `FileAlertDispatcher.php:27-50`), email, or Google Chat. The only overlap is incidental: dispatchers use `logError()`/`logException()` from `logs.php` to record their *own* internal failures (e.g. `GoogleChatDispatcher.php:40,49,54` when an outgoing HTTP call fails) — there's no Monolog handler feeding into alerts, nor vice versa.

## View rendering (`inc/core/mvc/`)

Waboot renders template files through a small MVC-flavored view layer (`View` → `HTMLView`), built and shared through `ViewFactory`. There are three public entry points: `Waboot()->renderView()` (instance method on `Theme`), and the standalone helpers `renderHtmlView()` / `getHtmlView()` (`inc/core/helpers/views.php`) — all three now go through the same factory.

### 1. Class hierarchy — `inc/core/mvc/`

- `View` (abstract, `View.php`) — resolves the template file path and holds `$args` (the variables that will be `extract()`-ed into the template) plus 4 predefined keys: `page_title`, `wrapper_class`, `wrapper_el`, `title_wrapper`. Its constructor (lines 20-58) either treats `$filePath` as an absolute path (`$isRelativePath = false`) or searches for it relative to `get_stylesheet_directory()` then `get_template_directory()` (child theme first, then parent) when `$isRelativePath = true` (the default) — throwing `ViewException` if the file isn't found in either location. Also provides `setVar()`, `setArgs()`, `clean()` (resets the 4 predefined keys to empty/neutral values) and `forDashboard()` (pre-fills them for a WP-admin-styled wrapper).
- `HTMLView extends View implements ViewInterface` (`HTMLView.php`) — adds `display($vars = [])` (echoes the rendered template, optionally wrapped in `<{$wrapper_el} class="{$wrapper_class}">` + a `title_wrapper`-formatted title, when `wrapper_el` is non-empty) and `get($vars = [], $stripNewLines = true)` (same as `display()` but captured via output buffering and returned as a string, with `\r\n\t` optionally stripped). Both merge the passed `$vars` over `$this->args` via `wp_parse_args($vars, $this->args)` before `extract()`-ing them into the include scope; the merged array is also exposed via `$GLOBALS['template_vars']`.
- `ViewFactory` (`ViewFactory.php`) — one static method, `createHtmlView(string $templateFile, array $args = [], bool $pathIsRelative = true): HTMLView`, that instantiates `HTMLView` and, if `$args` is non-empty, calls `setVar($k, $v)` for each entry before returning it.
- `ViewException extends \Exception` — the only exception type this layer throws (file-not-found, empty path).

### 2. The three entry points now share `ViewFactory`

**`Theme::renderView()`** (`inc/core/Theme.php:67-83`) — used to instantiate `HTMLView` directly; it was changed to go through `ViewFactory::createHtmlView()` instead, which was the point of this refactor:

```php
public function renderView(string $templateFile, array $vars = [], bool $clean = false, bool $pathIsRelative = true): void
{
    try{
        $v = ViewFactory::createHtmlView($templateFile, [], $pathIsRelative);
        if($clean){
            $v->clean();
        }
        $v->display($vars);
    }catch (\Exception $e){
        echo $e->getMessage();
    }
}
```

Notes on the refactor:
- Before, `renderView()` always called `new HTMLView($templateFile)` with the implicit default `$isRelativePath = true` — there was no way to render a view from an absolute path through `Theme::renderView()`. It now accepts an explicit `$pathIsRelative` parameter (default `true`, so all ~10 existing call sites across `inc/template-rendering.php`, `inc/hooks/layout.php`, `inc/hooks/posts-and-pages.php`, `inc/hooks/woocommerce/woocommerce.php`, `templates/archive.php` keep working unchanged), matching what `renderHtmlView()`/`getHtmlView()` already exposed.
- `$vars` is intentionally **not** passed into `ViewFactory::createHtmlView()`'s `$args` — it's passed to `display($vars)` instead, exactly as before the refactor. This matters for `$clean`: `clean()` must run on the view's default args (`page_title`, `wrapper_class`, `wrapper_el`, `title_wrapper`) *before* `$vars` is merged in by `display()`, so that if a caller explicitly sets e.g. `wrapper_el` in `$vars`, it still wins over the cleaned empty value (`wp_parse_args($vars, $this->args)` — `$vars` takes precedence). Pre-loading `$vars` into the factory's `setVar()` loop would have made `clean()` wipe out any of those 4 keys even when explicitly requested by the caller — a behavior regression — so the merge order was deliberately preserved.
- Exception handling is unchanged: any `\Exception` (including `ViewException` from a missing template file) is caught and its message is `echo`'d directly into the page output — this is a pre-existing behavior, not something the refactor addresses (see Gotchas below).

**`renderHtmlView(string $templateFile, array $args, bool $pathIsRelative = true): void`** and **`getHtmlView(string $templateFile, array $args, bool $pathIsRelative = true): string`** (`inc/core/helpers/views.php`) — both already used `ViewFactory::createHtmlView($templateFile, $args, $pathIsRelative)->display()` / `->get()` before this change; here `$args` *is* pushed through the factory's `setVar()` loop (there's no `$clean` option on these two, so the ordering caveat above doesn't apply). They catch `ViewException` specifically and fail silently — `renderHtmlView()` does nothing, `getHtmlView()` returns `''`.

### 3. Choosing between the three

| Entry point | Returns | Clean mode | Typical use |
|---|---|---|---|
| `Waboot()->renderView($file, $vars, $clean, $pathIsRelative)` | `void` (echoes) | yes (`$clean`) | Rendering layout partials/templates directly into the page (most common; used throughout `templates/`, `inc/hooks/`) |
| `renderHtmlView($file, $args, $pathIsRelative)` | `void` (echoes) | no | Same as above, callable without going through `Waboot()`, e.g. from contexts where only the helper is imported |
| `getHtmlView($file, $args, $pathIsRelative)` | `string` | no | When the rendered HTML needs to be captured (e.g. embedded into a larger string, an AJAX/REST response, or passed to another function) instead of echoed immediately |

All three ultimately build an `HTMLView` through the same `ViewFactory`, so template resolution (child theme → parent theme, or absolute path) and the predefined `page_title`/`wrapper_*` behavior are identical regardless of which one is used.

## Mail system (`inc/core/mail/`)

Waboot wraps WordPress's `wp_mail()` in a small object model (`Mail`, `MailAddress`, `MailAttachment`, `MailHeader`) plus a convenience helper, `sendMail()`.

### 1. `sendMail()` — `inc/core/helpers/mail.php:29-62`

```php
function sendMail(string $subject, string $body, $to, array $customHeaders = [], array $attachments = [], bool $sendAsHtml = true): bool {
    if(\is_array($to)){
        $to = array_map(fn($to) => new MailAddress($to), $to);
    }else{
        $to = new MailAddress($to);
    }
    $m = new Mail($subject, $body, $to);
    // ...builds MailHeader/MailAttachment from $customHeaders/$attachments...
    $m->setSendAsHTML($sendAsHtml);
    return $m->send();
}
```

- `$to` accepts a single email string or an array of strings; each is wrapped in a `MailAddress` (which validates with `is_email()` and throws `MailException` on an invalid address).
- `$customHeaders` is an array of `['name' => ..., 'value' => ...]` arrays, turned into `MailHeader` objects; malformed entries (missing `name`/`value`) are silently skipped.
- `$attachments` is an array of `['name' => ..., 'path' => ...]` arrays, turned into `MailAttachment` objects (which validate with `is_file()` and throw `MailAttachmentException` if the path doesn't exist); malformed entries are silently skipped too.
- Always delegates to the `Mail` class — there's no driver selection, no filter/constant to pick a different implementation. `sendAsHtml` defaults to `true` (unlike `Mail` itself, whose `$sendAsHTML` property defaults to `false` until `setSendAsHTML()` is called).
- Two related helpers in the same file: `preventEmails()` / `unblockEmails()` (lines 67-76) toggle `EmailDisabler::getInstance()->prevent()/allow()` — useful for staging environments, see §4.

`sendMail()` itself is not called anywhere else in the theme (grep confirms); the object model it wraps (`new Mail(...)->send()`) is what the alert system's `EmailAlertDispatcher` and the legacy `AlertDispatcher` actually use directly (see the Alert System section below).

### 2. The `Mail` class — `inc/core/mail/Mail.php`

Plain class (no interface/abstract), constructed as `new Mail(string $subject, string $body, MailAddress|MailAddress[] $to)`, with fluent setters for `from`, `cc`, `bcc`, `headers`, `attachments`, `sendAsHTML`. `send()` (lines 314-369):

1. Registers a `wp_mail_failed` action to log the WP_Error message via a Monolog logger.
2. Builds header strings from `From`/`Cc`/`Bcc`/custom `MailHeader`s.
3. If `isSendingAsHTML()`, filters `wp_mail_content_type` to `'text/html'` and runs the body through `nl2br()`.
4. Collects attachment paths.
5. Sends via plain **`wp_mail($this->getToAddresses(), $this->getSubject(), $body, $headers, $attachments)`** (line 368) — no external mail library, no HTML template rendering through the view system documented above; the caller must pass an already-rendered `$body` string.

There's no default "from" address set on the object — if `setFrom()` was never called, no `From` header is added and WordPress's own default sender (`wordpress@{site-domain}` / `admin_email`, depending on WP version/config) applies.

### 3. `SESMail` — unused, previously broken, now fixed

`inc/core/mail/SESMail.php` extends `Mail` and overrides `send()` to talk to AWS SES over SMTP by manipulating the global `$phpmailer` object directly (`isSMTP()`, `Port=587`, `SMTPSecure='tls'`, `SMTPAuth=true`) instead of calling `wp_mail()`. **It is never instantiated anywhere in the codebase** (confirmed via a theme-wide search for `SESMail`) — treat it as an available-but-unused alternative driver, not something exercised in production. It used to have a fatal bug (`$this->getTo()->getAddress()`, calling a method directly on the array that `Mail::getTo(): array` returns) which has been fixed to loop over all recipients (`foreach ($this->getTo() as $to){ $phpmailer->addAddress(...); }`); its namespace declaration has also been corrected from lowercase `waboot\inc\core\mail` to `Waboot\inc\core\mail`, matching the rest of the theme.

### 4. `EmailDisabler` — `inc/core/EmailDisabler.php`

A lazy singleton (same `static $instance` pattern as `DB`) used by `preventEmails()`/`unblockEmails()`. `prevent()` hooks three things at priority `9999`: filters `wp_mail` (`disableRecipients()` forces `$args['to'] = 'noone@void.void'`), short-circuits `pre_wp_mail` (`shortcutWpMail()` returns `true` without sending), and hooks `phpmailer_init` (`alterPHPMailerInit()` clears all recipients and re-adds `noone@void.void`) — belt-and-suspenders so no email actually leaves the server regardless of which code path triggers it. `allow()` removes all three.

### 5. Usage patterns found in the codebase

Nobody calls `sendMail()` directly; the actual send path used in practice is `new Mail($title, $body, new MailAddress($to))->send()`, from the alert system:

- `inc/core/alert/dispatcher/EmailAlertDispatcher.php:79`
- `inc/core/alert/AlertDispatcher.php:146` (legacy, `@deprecated`)

### 6. Bugs found and fixed

These were found while documenting this system and have since been fixed in `Mail.php`:

- **Broken failure-logging path (fixed)** — `Mail::send()` (line 316-321) wraps the error-logging in `catch (LoggerFactoryException $e){}` and calls `LoggerFactory::create()` inside `initLogger()`, but the file never imported `Waboot\inc\core\LoggerFactory`/`Waboot\inc\core\LoggerFactoryException`. Since `Mail.php`'s namespace is `Waboot\inc\core\mail`, both unqualified names resolved to the (non-existent) `Waboot\inc\core\mail\LoggerFactory(Exception)` — meaning a `wp_mail_failed` event would itself throw a fatal "Class not found" error instead of being logged. Fixed by adding `use Waboot\inc\core\LoggerFactory;` and `use Waboot\inc\core\LoggerFactoryException;` at the top of `Mail.php`.
- **Malformed mail log path (fixed)** — `Mail::getLogFile()` (line 407-415) used to concatenate `$this->getLogsDir() . 'Mail.php/' . $this->logFileName . ...` — no separator before `'Mail.php/'` and a stray literal `Mail.php` path segment, turning the intended `wp-content/mail-logs/waboot-mail/waboot-mail-{date}.log` into `wp-content/mail-logs/waboot-mailMail.php/waboot-mail-{date}.log`. Fixed to `$this->getLogsDir() . '/' . $this->logFileName . ...`.

## Alert System (`inc/core/alert/`)

A separate notification mechanism (file / email / Google Chat) for surfacing operational problems (e.g. from WP-CLI commands), independent of the Monolog logging documented above. Entry points: the `Alerts` facade (`inc/core/facades/Alerts.php`) and the helper `dispatchGoogleChatAlert()` (`inc/core/helpers/alerts.php`).

### 1. The `Alert` DTO — `inc/core/alert/Alert.php`

```php
new Alert(string $id, string $title, string $message, string $tz = null)
```

Holds `id`, `title`, `message`, and a `dateTime` stamped to "now" (in `$tz`, or the site's default timezone) at construction time — **there is no severity/level field**, unlike the Monolog channels documented earlier. `getTimeStamp(string $format = 'Y-m-d H:i:s')` formats the stored datetime.

### 2. Dispatcher architecture

`AlertDispatcherInterface` (`addAlert(Alert)`, `hasAlerts(): bool`, `dispatch(): void`) is implemented by `AbstractAlertDispatcher`, which accumulates `Alert`s in an array (`addAlert()`/`hasAlerts()`) and leaves `dispatch()` abstract — **dispatch is always a batch operation**: you add one or more alerts, then call `dispatch()` once to flush all of them together. Concrete dispatchers:

- **`FileAlertDispatcher`** (`dispatcher/FileAlertDispatcher.php`) — `dispatch()` concatenates all accumulated alerts into one text blob and appends it (`file_put_contents(..., FILE_APPEND)`) to a file named `{Y-m-d_H-i_}{sanitize_title($name)}.alerts` inside the `$dispatchTo` directory (created with `wp_mkdir_p()` if missing). The date format used to use a lowercase `h` (12-hour, no AM/PM marker, ambiguous between morning/evening) — fixed to uppercase `H` (24-hour).
- **`EmailAlertDispatcher`** (`dispatcher/EmailAlertDispatcher.php`) — `dispatch()` builds one email (subject `"{$name}: errors occurred"`, body = all alerts concatenated with `###`-delimited blocks) and sends it via `sendAlertMail()` (line 68), which either calls a custom `setMailHandlerCallback()` if one was set, or falls back to `(new Mail($title, $body, new MailAddress($to)))->send()` — i.e. the same `Mail` class documented above, **not** the `sendMail()` helper. Throws `AlertDispatcherException` if sending fails.
- **`GoogleChatDispatcher`** (`dispatcher/GoogleChatDispatcher.php`) — `dispatch()` posts each accumulated alert **individually** (not batched into one message, unlike the other two) via `wp_remote_post()` to a Google Chat webhook URL, with JSON body `{"text": alert.message}`. Errors (WP_Error, or a non-200 response with a JSON `error` field) are logged via `logError()`/`logException()` from `logs.php`, not thrown. Its namespace declaration used to be lowercase (`waboot\...`) instead of `Waboot\...`, inconsistent with the rest of the theme; it's been corrected.
- `AlertDispatcherFactory::createEmailDispatcher($name, $dispatchTo, $tz)` / `createFileDispatcher($name, $destFilePath, $tz)` — the only two factory helpers; **there is no factory method for `GoogleChatDispatcher`**, it must be instantiated manually.
- `AlertDispatcher` (`inc/core/alert/AlertDispatcher.php`) is a legacy, monolithic dispatcher (its docblock was tagged `@depecated`, a typo for `@deprecated`, now corrected) that bundles both email and file dispatch behind a `$dispatchMethod` constructor argument (`DISPATCH_METHOD_EMAIL` / `DISPATCH_METHOD_FILE`). It's still what the `Alerts` facade and `AbstractCommand` (CLI) actually use today, despite being marked deprecated.

There is no built-in way to fan a single `Alert` out to multiple dispatchers at once (e.g. file **and** email **and** Google Chat) — the caller creates and drives each dispatcher separately. No WordPress hook/filter exists to configure which dispatcher(s) are used globally.

### 3. The `Alerts` facade — `inc/core/facades/Alerts.php`

```php
Alerts::dispatchEmailAlert(string $title, string $message, string $recipient, \DateTimeZone $tz = null);
Alerts::dispatchGoogleChatAlert(string $message, string $url, \DateTimeZone $tz = null);
```

- `dispatchEmailAlert()` creates a legacy `AlertDispatcher('ad', AlertDispatcher::DISPATCH_METHOD_EMAIL, $recipient)`, wraps the message in an `Alert` (id = `base64_encode($title.$message.$recipient)`), and dispatches; failures are caught and sent to PHP's `error_log()` (not Monolog).
- `dispatchGoogleChatAlert()` converts the incoming `\DateTimeZone $tz` to its name (`$tz->getName()`) and does `new GoogleChatDispatcher('gd', $url, $tzName)`.

### 4. Bugs found and fixed

These were found while documenting this system and have since been fixed in `Alerts.php`:

- **Type mismatch risk (fixed)** — both `dispatchEmailAlert()` and `dispatchGoogleChatAlert()` accept `\DateTimeZone $tz = null`, but passed it straight into `new Alert($id, $title, $message, $tz)` — `Alert::__construct()`'s 4th parameter is typed `string $tz = null`, so passing an actual `\DateTimeZone` object would have thrown a `TypeError`. It never surfaced because every current call site omits `$tz` (defaults to `null`), but it was a live landmine for the first caller that passed one. Fixed by converting to `$tz?->getName()` before use.
- **Wrong constructor argument (fixed)** — `GoogleChatDispatcher`'s constructor is `__construct(string $name, string $dispatchToUrl, string $tz = null)` — the third parameter is a **timezone string**, not a dispatch method, but `dispatchGoogleChatAlert()` used to pass `AlertDispatcher::DISPATCH_METHOD_EMAIL` (the string `'email'`) there. It never errored, because `AbstractAlertDispatcher`'s constructor runs it through `Dates::getDateTimeZoneFromString('email')`, which fails `isValidTimezone()` and silently falls back to the site's default timezone — so the practical effect was just that the timezone parameter was always ignored for Google Chat alerts. Fixed to pass the (now correctly converted) `$tzName`.
- **Dead fallback path with a wrong file path (removed)** — a lazy-require fallback used to point to `.../inc/core/helpers/alert/dispatcher/GoogleChatDispatcher.php`, which never existed (the real file is under `inc/core/alert/dispatcher/`, not `inc/core/helpers/alert/dispatcher/`). It never ran in practice because Composer's PSR-4 autoloader already resolves `GoogleChatDispatcher` correctly via the `use` import at the top of the file, so the whole `class_exists()`/`require_once` block was dead code; it has been removed rather than fixed in place.

### 5. Usage patterns found in the codebase

- `inc/core/helpers/alerts.php:13-29` — `dispatchGoogleChatAlert(string|\Exception|\Throwable $e, string $source = '', ?string $url = null)`: resolves `$url` from the `GOOGLE_CHAT_ALERT_WEBHOOK` constant if not passed (returns silently if neither is available), prefixes the message with `$source`, calls `Alerts::dispatchGoogleChatAlert()`, and — if `$e` is an exception — sends a **second**, separate call with `$e->getTraceAsString()` as the message.
- `inc/core/cli/AbstractCommand.php:527` (`setupAlertDispatcher()`) — `AlertDispatcherFactory::createEmailDispatcher($this->logDirName, $this->defaultAlertDispatchEmail, $this->getTimeZone())`.
- `inc/core/cli/AbstractCommand.php:543-551` (`dispatchScriptStuckAlert()`) — builds an `Alert` ("Script seems stuck") and dispatches it by email when a long-running WP-CLI command exceeds its expected runtime.

Outside of the CLI infrastructure and the helper itself, nothing else in the theme currently calls into the alert system.

## Gotchas for future changes

- Do not assume `index.php` contains conditional routing logic — it never does; the dispatch lives entirely in `addMainContent()` and the `waboot/layout/content/template` filter.
- Do not reintroduce `templates/author.php` — author archives are intentionally unified with the generic archive fallback chain since v3.1.0.
- The custom-partial slug regex only allows `[a-z_-]+`; uppercase letters or digits in a `content-*.php` filename will be silently skipped by `injectTemplates()` (matched by `glob()` but rejected by the regex, so `continue`d).
- When disabling an extra addon via `waboot/addons/disabled` from a child theme, register the filter at a priority **higher than 10** (see Addons §3) — otherwise the theme's own default callback silently overwrites your addition.
- `Query::on()`/`Waboot()->DB()` share the *same* `Manager` instance process-wide (`setAsGlobal()`); don't assume per-call isolation, and remember credentials always come from `wp-config.php`, never from a separate DB config file.
- `Theme::logToFile()` swallows all exceptions silently (empty `catch` block) — a logging call that fails (e.g. unwritable `wp-content/logs/`) will not error out or warn anywhere; don't rely on log calls for anything that needs to be guaranteed to happen.
- Log files are **not** rotated by Monolog; they're simply named per-day (`{channel}-{Y-m-d}.log`) with no automatic cleanup — old log files accumulate under `wp-content/logs/` (and `wp-content/cli-logs/` for CLI commands) unless something else prunes them.
- `Theme::renderView()` echoes a caught exception's raw message directly into the page (`echo $e->getMessage();`) instead of logging it — a missing/misspelled template path will print a `ViewException` message inline on the page rather than failing silently or being logged.
- The alert system has no "fan out to multiple dispatchers" helper — dispatching the same `Alert` to file + email + Google Chat means creating and driving each dispatcher manually (see Alert System §2).
- `SESMail` is still unused in production (never instantiated anywhere in the theme) even though its known bugs are fixed — don't assume it's been exercised against a real AWS SES account.
- When passing a `\DateTimeZone` through `Alerts::dispatchEmailAlert()`/`dispatchGoogleChatAlert()`, remember `Alert`/`AbstractAlertDispatcher` want a timezone **name string**, not the object — convert with `->getName()` (see Alert System §3-4 for why this matters).
- `View::clean()` only resets the 4 predefined keys (`page_title`, `wrapper_class`, `wrapper_el`, `title_wrapper`); it does not clear any other custom vars already set on the view via `setVar()`.
