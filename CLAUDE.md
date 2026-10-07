# Waboot (corporate branch) - Notes for Claude Code

Waboot is a modular, general-purpose WordPress theme by the Waga team.
This checkout is the **`corporate` branch**: the variant meant for sites that do **not** use WooCommerce.
Theme version 4.1.0, repository `wagaweb/waboot` (default branch `master`; the WooCommerce-oriented variant lives on `main`).

For coding conventions - project structure, naming, PHP/JS standards, which class to prefer for a given job - follow [`docs/code-guidelines.md`](./docs/code-guidelines.md).
That file is the single source of truth for *how to write* code here; this file documents *how the theme actually works*, verified against the code in this working tree.

Local environment is ddev (project `waboot-corporate`, PHP 8.3, nginx-fpm, MariaDB 10.11).

## 1. Read this first if you also work on the ecommerce branch

The two branches share a name, a namespace and most of their file layout, but several load-bearing details differ.
Porting code between them without checking these will produce code that looks right and fails at runtime.

| | `corporate` (here) | `main` (ecommerce) |
|---|---|---|
| Asset build | gulp + browserify + babelify (`gulpfile.js`) | esbuild (`assets/bin/build-assets.mjs`) |
| Monolog | `^2.8`, resolved 2.10.0 | `^3.8` |
| `Theme::renderView()` | `(string $templateFile, array $vars = [], bool $clean = false)`, instantiates `new HTMLView()` directly | has an extra `bool $pathIsRelative = true` and goes through `ViewFactory` |
| Addon packages | `addons/packages/` does not exist, `loadAddons()` is commented out | several shipped addons, each with its own build |
| `inc/core/woocommerce/` | does not exist | present |
| `inc/core/EmailDisabler.php` | **does not exist**, although `helpers/mail.php` calls it | present |

Monolog 2 matters in particular: levels are plain integers, not the `Monolog\Level` enum, and the handler/formatter APIs are the 2.x ones.

## 2. Build

```
composer install
npm install          # or: yarn
npm run assets:build
```

`npm run assets:build` runs `npx gulp compile_js && npx gulp compile_css`.
`npm run assets:build:watch` runs bare `npx gulp`, which is the **default** task: it builds once and then stays watching.
`readme.md` tells you to run `gulp`, which is that watch task, not a one-shot build.

The `node_modules/` directory present on disk predates the current `package.json` and does not contain `sass` (it has the old `node-sass`), so `gulpfile.js:7` - `require("gulp-sass")(require("sass"))` - throws until you reinstall.
Always run `npm install` before the first build in a fresh checkout.
There is no `package-lock.json`; the lockfile is `yarn.lock`.

### Pipeline - `gulpfile.js`

Two independent tasks, no bundler config beyond this file.

**JS** (`compileJsBundle` then `minifyJs`, chained by `gulp.series`):

- Single entry `assets/src/js/main.js`.
- `browserify({insertGlobals: true, debug: true})` with the `babelify` transform (`@babel/preset-env`, no explicit targets) writes `assets/dist/js/main.pkg.js`.
  `debug: true` means the sourcemap is **inline** in that file, and the bundle is not minified.
- `main.pkg.js` is then read back, run through `gulp-uglify`, and written as `assets/dist/js/main.min.js` plus an external `main.min.js.map`.
  The minified build therefore depends on the unminified one having been produced first.

**CSS** (`compileCss`):

- Single entry `assets/src/sass/main.scss`.
- dart-sass -> `postcss([autoprefixer({browsers: ["last 1 version"]}), cssnano({zindex: false})])` -> `assets/dist/css/main.min.css` + external `.map`.
- There is **no `browserslist` field** in `package.json`.
  Autoprefixer's targets are the inline `browsers` option above, and Babel has no target configuration at all, so the two pipelines do not share a browser-support definition.

### Output filenames are load-bearing

`inc/hooks/assets.php` hardcodes these exact paths with no manifest and no content hash, so the build must keep producing them:

- `assets/dist/js/main.pkg.js` - enqueued when `WP_DEBUG` is true.
- `assets/dist/js/main.min.js` (+ `.map`) - enqueued when `WP_DEBUG` is false.
- `assets/dist/css/main.min.css` (+ `.map`) - always enqueued.
- `assets/dist/css/gutenberg.min.css` - loaded into the block editor through `add_editor_style()`.

### The Gutenberg editor stylesheet is not built

`assets/src/sass/backend/gutenberg.scss` exists, but **no gulp task compiles it** and neither `gulpfile.js` nor `package.json` mentions it.
`assets/dist/css/gutenberg.min.css` is hand-maintained and is the **only** build artifact committed to git: `.gitignore` excludes `main.min.css`, `main.min.css.map`, `main.min.js`, `main.min.js.map` and `main.pkg.js`, so a fresh clone has no frontend CSS or JS until you build.
If you need to change editor styles, edit `assets/dist/css/gutenberg.min.css` directly and commit it, or wire the source file into the build first.
As written, `gutenberg.scss` uses `$containerWidth` without importing `frontend/_variables.scss`, so it would not compile standalone.

### jQuery

`package.json` configures `browserify-shim` to map `jquery`, `backbone` and `underscore` to the corresponding globals.
In practice the shim is inert for jQuery: `assets/src/js/main.js` and its two modules never `import`/`require` jQuery, they use the `jQuery` global that WordPress enqueues.
Keep it that way.
`main-js` declares `jquery` as a dependency (`inc/hooks/assets.php:16`), and the jQuery plugins used here (`owlCarousel`, `venobox`) attach themselves to that single global instance; bundling a second copy would silently break them.

### Composer

```json
"config": { "platform": { "php": "8.3" } },
"require": {
    "monolog/monolog": "^2.8",
    "illuminate/database": "v8.83.27"
},
"autoload": { "psr-4": { "Waboot\\inc\\": "inc/" } }
```

There is no `require.php` constraint and no `require-dev` section; `config.platform.php` only pins what Composer resolves against.
`composer.complete.json` sitting next to it is a **stale, unused** second manifest (PHP 7.4, Guzzle 6, Laravel 7, Sentry) that nothing reads - do not treat it as the real dependency list.
Neither `sentry/sdk` nor `league/csv` is installed, which disables two code paths described below.

## 3. Assets and `AssetsManager`

`inc/core/AssetsManager.php` is the registration layer; `inc/hooks/assets.php` is its only consumer.

An asset is an associative array; the canonical key list is the `wp_parse_args()` default block at `AssetsManager.php:66-78`:

| Key | Default | Meaning |
|---|---|---|
| `uri` | `''` | Public URL |
| `path` | `''` | Filesystem path, used for existence check and `filemtime()` versioning |
| `version` | `false` | `false` derives from `filemtime`, a string is used literally, `null` omits the version |
| `deps` | `[]` | Handles this asset depends on |
| `i10n` | `[]` | `['name' => ..., 'params' => [...]]` for `wp_localize_script` |
| `type` | `''` | `js` or `css`; autodetected from the `path` extension when empty |
| `enqueue_callback` | `false` | Callable returning bool, gates the enqueue |
| `in_footer` | `false` | Scripts only |
| `loading_strategy` | `false` | `defer`/`async`, WordPress 6.3+ only |
| `enqueue` | `true` | `false` registers without enqueueing |
| `media` | `apply_filters('waboot/assets/styles/default_media', 'all')` | Stylesheet media |

`enqueue()` is a **two-pass** design: the first pass registers every asset with `wp_register_script`/`wp_register_style`, the second pass evaluates each `enqueue_callback` and enqueues.
That ordering is what lets assets inside the same array declare each other in `deps`.

Behaviour worth knowing:

- A `path` that does not exist produces an admin notice (`Utilities::addAdminNotice()`) or a `trigger_error()` on the frontend, and the asset is **skipped**, not fatal.
- An undeterminable `type` throws `\Exception("Unknow asset type for $name")`, which aborts the whole enqueue pass.
- A `uri` that does not contain `home_url()` is treated as external and gets no version string.
- The only filter the class applies is `waboot/assets/styles/default_media`.

`inc/hooks/assets.php` registers six assets - `main-js`, `owlcarousel-js`, `venobox-js`, `main-style`, `google-font`, `owlcarousel-css`, `venobox-css` - and is hooked at `wp_enqueue_scripts` priority **11**.
The debug switch uses **`WP_DEBUG`**, not the conventional `SCRIPT_DEBUG` (which the theme never references), and only for JS: CSS always loads `main.min.css`.
All `loading_strategy` entries are commented out, so the WordPress 6.3 defer/async branch in `AssetsManager` is dead in practice.
`assets/vendor/` ships `owlcarousel`, `venobox`, `fontawesome` and `swipebox` as pre-built files; `swipebox` is never enqueued.

## 4. Template system

### 4.1 Entry point

The theme root contains only five PHP files: `functions.php`, `header.php`, `footer.php`, `index.php`, `sidebar.php`.
There is no root `page.php`, `single.php`, `archive.php`, `search.php` or `404.php`, so the WordPress template hierarchy **always** collapses to `index.php`.

`index.php` is a fixed layout, not a router:

```php
get_header();
get_template_part('templates/wrapper', 'start');
do_action('waboot/layout/content');
get_template_part('templates/wrapper', 'end');
get_footer();
```

`get_sidebar()` is **not** called here.
It is called once, from `templates/wrapper-end.php:3`, so the sidebar is emitted inside `<main>`, after `.main__content` closes.

Wrapper chain: `wrapper-start.php` opens `<main role="main" id="main" class="main">`, fires `waboot/layout/main-top`, opens `.main__grid`, fires `waboot/layout/title`, opens `.main__content`.
`wrapper-end.php` closes `.main__content`, calls `get_sidebar()`, closes `.main__grid`, fires `waboot/layout/main-bottom`, closes `</main>`.

### 4.2 The routing action

Exactly one callback is hooked to `waboot/layout/content`: `\Waboot\inc\core\addMainContent()`, defined and registered in `inc/core/hooks.php:10-59`.

It classifies the request with `Utilities::getCurrentPageType()` and then dispatches:

| Page type | Condition | Template part |
|---|---|---|
| `default_home` | - | `templates/blog` |
| `static_home` | - | `templates/page` |
| `blog_page` | - | `templates/blog` |
| `common` | `is_attachment() && wp_attachment_is_image()` | `templates/image` |
| `common` | `$wp_query->is_single()` | `templates/single` |
| `common` | `$wp_query->is_page()` | `templates/page` |
| `common` | `$wp_query->is_author()` | `templates/archive` |
| `common` | `$wp_query->is_search()` | `templates/search` |
| `common` | `$wp_query->is_archive()` | `templates/archive` |
| `common` | `$wp_query->is_404()` | `templates/404` |
| `common` | none of the above | `throw new \Exception('Unrecognized content type')` |
| anything else | - | `throw new \Exception('Unrecognized page type')` |

Order matters: `is_single()` is tested before `is_page()`, and `is_author()` before `is_archive()` (an author archive satisfies both).

`templates/image.php` **does not exist** in this branch, so image attachment pages render an empty content region rather than erroring.

Page classification comes from the `Query` trait (`inc/core/utils/Query.php:11-25`), composed into `Utilities`, whose constants are at `inc/core/utils/Utilities.php:13-16`:

- `is_front_page() && is_home()` -> `PAGE_TYPE_DEFAULT_HOME`
- `is_front_page()` only -> `PAGE_TYPE_STATIC_HOME`
- `is_home()` only -> `PAGE_TYPE_BLOG_PAGE`
- otherwise -> `PAGE_TYPE_COMMON`

### 4.3 The actual extension point

```php
// inc/core/hooks.php:53-57
if(isset($tpl_part)){
    $tpl_part = apply_filters('waboot/layout/content/template',$tpl_part,$page_type);
    get_template_part($tpl_part[0],$tpl_part[1]);
}
```

`waboot/layout/content/template` receives the `[slug, name]` pair chosen by the switch plus the page type, and runs immediately before `get_template_part()`.
This is where a child theme or plugin overrides routing without touching core.
Note the two `throw` statements execute **before** the filter, so it cannot rescue an unclassifiable request.

### 4.4 Archive fallback chain

`templates/archive.php` delegates:

```php
$tpl = \Waboot\inc\core\getArchiveTemplate();
if(!empty($tpl)) { \Waboot\inc\core\Waboot()->renderView($tpl,[]); }
else              { \Waboot\inc\core\Waboot()->renderView('templates/archive/archive.php',[]); }
```

`getArchiveTemplate()` (`inc/core/template-functions.php:25-62`) builds a candidate list rooted at `templates/archive/`, most specific first:

- Author archive: `author-{user_nicename}` -> `author-{ID}` -> `author`
- Category term: `category-{slug}` -> `category-{term_id}` -> `category`
- Other taxonomy term: `{taxonomy}-{slug}` -> `taxonomy-{taxonomy}-{slug}` -> `taxonomy-{taxonomy}` -> `taxonomy`
- Post type archive: `archive-{post_type}` only, no fallback list
- Date archive: `date`
- Otherwise: empty string

The list is resolved by `locateTemplate()` (`inc/core/template-functions.php:72-96`), a variant of `locate_template()` that returns the bare template name instead of a full path and checks, in order, the child theme (`STYLESHEETPATH`), the parent theme (`TEMPLATEPATH`), then `wp-includes/theme-compat/`.

**Only `templates/archive/archive.php` exists on disk.**
Every more specific override is an optional file a child theme or a project adds, so in a parent-only install the fallback branch always runs.

There is no `templates/author.php` and no `templates/author/` directory: since 3.1.0 author archives go through the generic archive chain.

### 4.5 Dashboard-selectable custom partials

`injectTemplates()` (`inc/core/hooks.php:70-88`) is hooked to `theme_page_templates` at priority **999**:

```php
$template_directory = get_stylesheet_directory(). '/templates/parts-tpl';
$template_directory = apply_filters('waboot/custom_template_parts_directory',$template_directory);
$tpls = glob($template_directory. '/content-*.php');
// slug extracted with preg_match('/^content-([a-z_-]+)/', $basename, $matches)
$page_templates[$name] = str_replace('_', ' ', ucfirst($name)) . ' ' . _x('(parts)', ...);
```

- The scanned directory is `get_stylesheet_directory()` based, and overridable through `waboot/custom_template_parts_directory`.
- The slug charset is `[a-z_-]+`: lowercase letters, underscore, hyphen.
  No digits, no uppercase.
  The regex is not right-anchored, so `content-tpl2.php` yields the truncated slug `tpl`, and `content-Foo.php` matches nothing and is skipped.
- The dropdown label comes from the **filename**, not from a `Template Name:` header: underscores become spaces, the first letter is uppercased, and a localized `" (parts)"` is appended.
- The array key stored in `_wp_page_template` is the bare slug, not a filename.

`templates/parts-tpl/` contains only `.gitkeep`; it is a scaffold to populate per project.

Consumption is in `templates/page.php`: if `_wp_page_template` contains `.php` it is coerced back to `'page'`, otherwise `templates/parts-tpl/content-{slug}.php` is loaded when it exists, falling back to `templates/parts/content-page.php`.

### 4.6 `templates/` layout

- Dispatch templates: `404.php`, `archive.php`, `blog.php`, `single.php`, `search.php`, `page.php`, `comments.php`, `wrapper-start.php`, `wrapper-end.php`
- `templates/archive/` - archive sub-templates, only `archive.php` ships
- `templates/parts/` - 12 `content-*.php` partials plus `meta.php`, loaded with `get_template_part()` and relying on loop globals
- `templates/parts-tpl/` - dashboard-selectable partials, empty scaffold
- `templates/view-parts/` - 13 partials rendered through the MVC layer with explicit variables, never with `get_template_part()`

That split between `parts/` and `view-parts/` is a real convention: keep loop-driven markup in `parts/`, and anything that takes explicit data in `view-parts/`.

`templates/comments.php` is substituted for the WordPress default by the `comments_template` filter at `inc/core/hooks.php:120-124`.
`templates/single.php` prefers `templates/parts/content-{post_type}.php` when it exists and falls back to `content-single`.
`templates/archive/archive.php` has no empty-state branch, unlike `blog.php` and `search.php`, which fall back to `content-none`.

## 5. View rendering (`inc/core/mvc/`)

Classes: `View` (abstract), `HTMLView`, `ViewInterface`, `ViewFactory`, `ViewException`.

`View::__construct(string $filePath, bool $isRelativePath = true)`:

- An empty path throws `ViewException`.
- A path with no extension gets `.php` appended, which is why `getArchiveTemplate()` can return bare names.
- With `$isRelativePath = true` the file is looked for in `get_stylesheet_directory()` **then** `get_template_directory()` - child theme wins - and `ViewException` is thrown if neither has it.
- With `$isRelativePath = false` the path is used verbatim.

Four predefined args are set in the constructor: `page_title` (`''`), `wrapper_class` (`''`), `wrapper_el` (`''`), `title_wrapper` (`'%s'`).
Because `wrapper_el` defaults to empty, front-end views render with **no** wrapper element.
`clean()` resets exactly those four and nothing else; `forDashboard()` fills them for a wp-admin styled wrapper.
Note `setArgs()` replaces the whole array and wipes the defaults, while `setVar()` and `ViewFactory` preserve them.

`HTMLView::display($vars = [])` merges with `wp_parse_args($vars, $this->args)`, exposes the result both as `extract()`ed locals and as `$GLOBALS['template_vars']`, then includes the template - wrapped in `<{wrapper_el} class="{wrapper_class}">` plus a `printf($title_wrapper, $page_title)` title when `wrapper_el` is non-empty.
`HTMLView::get($vars = [], $stripNewLines = true)` is the same captured through output buffering.

### Three entry points, three different error policies

| Entry point | Location | Returns | On `ViewException` |
|---|---|---|---|
| `Waboot()->renderView($file, $vars, $clean)` | `inc/core/Theme.php:67` | `void`, echoes | **echoes `$e->getMessage()` into the page** |
| `renderHtmlView($file, $args, $pathIsRelative)` | `inc/core/helpers/views.php:14` | `void`, echoes | swallowed silently |
| `getHtmlView($file, $args, $pathIsRelative)` | `inc/core/helpers/views.php:26` | `string` | returns `''` |

`Theme::renderView()` is the one used throughout the theme (`inc/hooks/layout.php`, `inc/hooks/posts-and-pages.php`, `inc/template-rendering.php`, `templates/archive.php`).
It instantiates `new HTMLView($templateFile)` **directly**, not through `ViewFactory`, and has **no** `$pathIsRelative` parameter: it can only render relative paths.
It also catches `\Exception`, not just `ViewException`, so an error raised inside the included template is printed as plain text mid-markup too.

`renderHtmlView()` and `getHtmlView()` go through `ViewFactory::createHtmlView()` and do accept `$pathIsRelative`, but neither is called anywhere in this branch today.
A third variant, `renderCustomHead()` (`inc/template-rendering.php:184-188`), instantiates `HTMLView` directly and reports through `trigger_error()`.

## 6. Addons system - present but dormant

Addons are self-contained mini-plugins under `addons/packages/<name>/`, each with its own `bootstrap.php`.

**In this branch the system is switched off.**
`addons/packages/` does not exist, it is listed in `.gitignore` (so addon packages are never committed to this repo and arrive as per-project drop-ins), and the call that starts the whole thing is commented out in `functions.php`:

```php
/*
add_filter('waboot/addons/disabled', function(){ return ['star_rating']; });
\Waboot\inc\loadAddons();
*/
```

The machinery that would run is still there:

- `\Waboot\inc\loadAddons()` (`inc/bootstrap.php:19-21`) requires `addons/bootstrap.php`.
- `addons/bootstrap.php` loads `functions.php`, `shared-functions.php` and `shared-hooks.php`, then requires each discovered addon's own `bootstrap.php` if present.
- `getAddons()` (`addons/functions.php:25-34`) `scandir()`s `addons/packages`, keeps directories, drops `.`/`..`, and drops anything returned by `getDisabledAddons()`, which is `apply_filters('waboot/addons/disabled', [])`.
  Comparison is strict, so disabled names must match exactly.
- Path helpers: `getAddonDirectory($addon)`, `getAddonDirectoryURI($addon)`.

There is no load-order mechanism and no dependency declaration: iteration follows `scandir()`.

To turn it on, create `addons/packages/`, then uncomment the `loadAddons()` call.
Enabling it while the directory is missing is a `TypeError`: `scandir()` returns `false` and `array_filter(false, ...)` is fatal on PHP 8.
When registering a `waboot/addons/disabled` callback, accept and merge the incoming array (`function(array $disabled): array { $disabled[] = '...'; return $disabled; }`) rather than returning a fresh one, so you do not discard other callbacks' contributions.

`addons/shared-functions.php` still contains `getWCProductFromCartData()` returning a `\WC_Product` - WooCommerce leftover, dead on this branch.

## 7. Core services

### 7.1 Bootstrap and the `Theme` object

`functions.php` defines `LANG_TEXTDOMAIN`, requires `vendor/autoload.php` and `inc/bootstrap.php`, calls `\Waboot\inc\initWaboot()`, then `safeRequireFiles()`s three extra files: `inc/multilanguage-functions.php`, `inc/hooks/gravityform/hooks.php`, `inc/cli.php`.
A fourth entry, `inc/hooks/woocommerce.php`, is commented out.

`initWaboot()` (`inc/bootstrap.php:7-17`) requires `inc/core/helpers/theme.php`, `inc/template-functions.php` and `inc/core/template-functions.php`, then calls `Waboot()->loadDependencies()`, which `safeRequireFiles()`s 16 files in a fixed order: the six `inc/core/helpers/*.php`, `inc/core/hooks.php`, the three `inc/template-*.php`, and six of the seven `inc/hooks/*.php`.

`Theme` (`inc/core/Theme.php`) is a thin service holder owning an `AssetsManager` and a `Layout`, plus four cross-cutting proxies: `renderView()`, `DB()`, `registerCommand()` and `logToFile()`.

`Waboot()`, `AssetsManager()` and `Layout()` are each **defined twice**: the real implementation in `Waboot\inc\core\helpers` (`inc/core/helpers/theme.php`) and a delegating alias in `Waboot\inc\core` (`inc/core/template-functions.php:101,110,119`).
Both namespaces are imported interchangeably across the codebase; either works.
All three declare non-nullable return types but `return false` on their failure path, so a missing class surfaces as a `TypeError` rather than a clear error.

`Theme::LOG_LEVEL_*` constants exist but are referenced nowhere; `logToFile()` switches on `MonologLoggingLevels::*` instead.

### 7.2 Database layer - available, currently unused

`inc/core/DB.php` is a lazy singleton with a `protected` constructor.
On first use it checks for `\Illuminate\Database\Capsule\Manager` (throwing `DBUnavailableDependencyException` otherwise), builds a `mysql` connection from the `wp-config.php` constants `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, sets `'prefix' => $wpdb->prefix` so table names are written unprefixed, and calls `setAsGlobal()`.
`bootEloquent()` is **not** called, so only the query builder and schema builder work - there are no Eloquent models, migrations or seeders.
The connection is opened on the first `Waboot()->DB()` call, never eagerly at bootstrap.

Public API: `getInstance()`, `getQueryBuilder(): Manager`, `hasQueryBuilder(): bool`, `getSchemaBuilder(): Builder`, `getWPDB(): wpdb`, `getDBPrefix(): string`, `tableExists(string $tableName): bool`, `static queryTable(string $table)`.

The intended front door is the facade:

```php
\Waboot\inc\core\facades\Query::on($table); // -> \Illuminate\Database\Query\Builder
```

**Nothing in this branch calls it.**
`Query::on()`, `DB::queryTable()` and `AbstractRepository` have zero external call sites, and `AbstractRepository` has no subclasses.
`illuminate/database` is autoloaded on every request for nothing.
When you do add a query, prefer this layer over raw `$wpdb` (see `docs/code-guidelines.md`).

Beware the name collision: `inc/core/facades/Query.php` is the database facade, while `inc/core/utils/Query.php` is an unrelated trait for WordPress page-type detection.

### 7.3 Logging (Monolog 2)

Three layers: `LoggerFactory` creates raw loggers, `Theme::logToFile()` orchestrates channels and caching, `inc/core/helpers/logs.php` is the public API.

`LoggerFactory::create(string $name, string $logFileName, \DateTimeZone $tz = null): Logger` creates the directory (`wp_mkdir_p`) and file (`touch`) on demand, defaults the timezone to `Dates::getDefaultDateTimeZone()`, and pushes a single `Monolog\Handler\StreamHandler`.
No formatter is configured, so Monolog's default `LineFormatter` applies.
`createSentryLogger()` exists but `sentry/sdk` is not installed.

`Theme::logToFile(string $loggerIdentifier, string $logMessage, int $logLevel = MonologLoggingLevels::INFO, array $context = [], \DateTimeZone|null $dz = null)`:

- `$loggerIdentifier` is an arbitrary channel name chosen by the caller, and doubles as the log file prefix.
- Loggers are cached per request in `$registeredFileLoggers`.
- The path is always `WP_CONTENT_DIR.'/logs/{identifier}-{Y-m-d}.log'`, one file per channel per day.
  There is **no rotation**: the date is baked into the filename and nothing prunes old files.
- The whole method is wrapped in an empty `catch (\Exception | \Throwable $e){}`, so logging failures are completely silent.

Public helpers in `inc/core/helpers/logs.php`, all `void`:

```php
logToFile($loggerIdentifier, $logMessage, $logLevel = INFO, $context = [], $dz = null)
logInfoToFile($loggerIdentifier, $logMessage, $context = [], $dz = null)
logWarningToFile(...)   logErrorToFile(...)
logInfo($message, $source, $context = [], $fileName = 'waboot-log')
logWarning(...)   logError(...)
logException(\Exception|\Throwable $e, $source, $context = [], $fileName = 'waboot-log')
```

The four shorthands merge `$source` into `$context['source']`.
`logException()` logs only `$e->getMessage()`; the stack trace is dropped.

Levels are the integer constants on `MonologLoggingLevels` (`inc/core/helpers/MonologLoggingLevels.php`, `DEBUG=0` through `EMERGENCY=7`).
These are **not** Monolog's own values and exist only as a switch key.

Channels actually in use: `waboot-log` (the default, four call sites theme-wide), `waboot-mail-logger` (written by `Mail`, under `wp-content/mail-logs/`), `waboot-cli-command-logger` (written by `CommandLoggerTrait`, under `wp-content/cli-logs/{logDirName}/`).
Only channels created through `Theme::logToFile()` land in `wp-content/logs/`; Mail and CLI call `LoggerFactory::create()` directly and define their own paths.

### 7.4 Mail

`inc/core/mail/` holds `Mail`, `SESMail`, `MailAddress`, `MailHeader`, `MailAttachment` and two exceptions.

A `Mail` aggregates subject, body, one or more `MailAddress` recipients (plus optional from/cc/bcc), `MailHeader` pairs, `MailAttachment` entries and an HTML flag.
`MailAddress` validates with `is_email()` and throws `MailException`; `MailAttachment` validates with `is_file()` and throws `MailAttachmentException`.

`Mail::send()` registers a one-shot `wp_mail_failed` listener that logs into the `waboot-mail-logger` channel, folds From/Cc/Bcc into headers, filters `wp_mail_content_type` to `text/html` and `nl2br()`s the body when sending as HTML, then delegates to plain **`wp_mail()`**.
There is no external mail library and no template rendering: the caller passes an already-rendered body string.
No default sender is set, so WordPress's own default applies unless `setFrom()` is called.

`sendMail(string $subject, string $body, $to, array $customHeaders = [], array $attachments = [], bool $sendAsHtml = true): bool` (`inc/core/helpers/mail.php:29`) is the array-shaped convenience wrapper.
Malformed `$customHeaders`/`$attachments` entries are skipped silently.
It has no call sites in this branch.

`SESMail extends Mail` overrides `send()` to drive the global PHPMailer over AWS SES SMTP instead of `wp_mail()`.
It is never instantiated anywhere - treat it as an available but unexercised alternative driver.

**`preventEmails()` / `unblockEmails()` are broken here.**
`inc/core/helpers/mail.php` imports and calls `Waboot\inc\core\EmailDisabler`, and that class **does not exist in this branch**.
Calling either function is a guaranteed fatal error.
Nothing calls them today, which is why it has gone unnoticed.

### 7.5 Alert system

A notification mechanism (file / email / Google Chat / Sentry) for operational problems, independent of the Monolog logging above.

`Alert` is a DTO: `__construct(string $id, string $title, string $message, ?\DateTimeZone $tz = null)`, holding id, title, message and a construction-time timestamp.
There is **no severity field**.

`AlertDispatcherInterface` is `addAlert(Alert)`, `hasAlerts(): bool`, `dispatch(): void`.
`AbstractAlertDispatcher` accumulates alerts and leaves `dispatch()` abstract: **dispatch is always a batch operation**.

Timezones travel as objects throughout: every constructor in this namespace takes `?\DateTimeZone $tz = null` and resolves `$tz ?? Dates::getDefaultDateTimeZone()`.
Do not pass a timezone name string.

Concrete dispatchers (`inc/core/alert/dispatcher/`):

- `FileAlertDispatcher` - appends all accumulated alerts to `{dispatchTo}/{Y-m-d_H-i}_{sanitize_title(name)}.alerts`.
- `EmailAlertDispatcher` - one email, subject `"{name}: errors occurred"`, body of `###`-delimited blocks, sent through an injectable `setMailHandlerCallback()` or else `(new Mail(...))->send()`.
- `GoogleChatDispatcher` - posts each alert **individually** (not batched) to a webhook via `wp_remote_post()`.
  Failures are logged through `logError()`/`logException()` and `dispatch()` never throws, so they are invisible to callers.
- `SentryAlertDispatcher` - always throws `AlertDispatcherException`, because `sentry/sdk` is not installed.

`AlertDispatcherFactory` covers only two of the four: `createEmailDispatcher()` and `createFileDispatcher()`.
Google Chat and Sentry must be instantiated with `new`.

`AlertDispatcher` (`inc/core/alert/AlertDispatcher.php`) is the legacy monolithic dispatcher bundling email and file behind `DISPATCH_METHOD_EMAIL`/`DISPATCH_METHOD_FILE`.
It is marked `@deprecated`, yet it is still what `Alerts::dispatchEmailAlert()` instantiates.

Entry points:

```php
Alerts::dispatchEmailAlert(string $title, string $message, string $recipient, ?\DateTimeZone $tz = null);
Alerts::dispatchGoogleChatAlert(string $message, string $url, ?\DateTimeZone $tz = null): void;
dispatchGoogleChatAlert(string|\Exception|\Throwable $e, string $source = '', string $url = null): void; // inc/core/helpers/alerts.php
```

The helper resolves `$url` from the `GOOGLE_CHAT_ALERT_WEBHOOK` constant when omitted, returns silently if neither is available, and sends the stack trace as a **second** message when given a throwable.
The two facade methods report their own failures inconsistently: the email path uses `error_log()`, the Google Chat path uses `logException()`.

There is no way to fan one `Alert` out to several dispatchers; the caller drives each one separately.

### 7.6 WP-CLI

`registerCommand(string $command, $callable, string $prefix = '', array $description = []): void` (`inc/core/helpers/cli.php:15`) delegates to `Theme::registerCommand()`, which returns early without `\WP_CLI`, joins `prefix:command`, and - when `$description` is empty - auto-discovers the synopsis from a static `getCommandDescription()` on the callable.

Commands are registered in `inc/cli.php`, which is loaded from `functions.php` and not from `loadDependencies()`:

```php
registerCommand('publish-missed-posts', PublishMissingArticles::class,'waboot');
registerCommand('generate-site-stat-file', GenerateSiteStatFile::class,'waboot');
```

giving `wp waboot:publish-missed-posts` and `wp waboot:generate-site-stat-file`.
The whole block is wrapped in a silent `try/catch`, so registration failures do not surface.

Base classes live in `inc/core/cli/`:

- `AbstractCommand` - despite the name it is declared `class`, not `abstract class`.
  `__invoke()` is the WP-CLI entry point: it sets the shared `--be-quiet`/`--dry-run` flags, calls `beginCommandExecution()`, the subclass's `run()`, then `endCommandExecution()`.
  Provides `log()`/`warning()`/`error()`/`success()` which write to Monolog and to `\WP_CLI` when verbose, progress-bar helpers, run-state tracking in `wp_options` keyed `cli_{slug}_*` with a stuck-detection window, and `setupAlertDispatcher()`/`dispatchScriptStuckAlert()`.
- `AbstractCSVParserCommand` - adds `--basepath`, `--file`, `--delimiter`, `--offset`, `--limit`, `--parse-all-files` and leaves `customInitialization()` and `parseCSVRow()` abstract.
  It imports `League\Csv\Reader`, and **`league/csv` is neither required in `composer.json` nor installed**, so it cannot be subclassed as-is: add the dependency first.
- `CommandLoggerTrait` - log file at `WP_CONTENT_DIR.'/cli-logs/{logDirName}/{logFileName}-{Y-m-d}.log'`.
  It depends on `$logDirName`/`$logFileName` existing on the host class, an implicit contract it does not declare, and memoizes the path in a `static` shared across all commands in the process.
- `CSVRow`, `CLIRuntimeException`.

The two shipped commands override none of the logging properties, so both write to `wp-content/cli-logs/common/common-{Y-m-d}.log`.

### 7.7 Utilities

`inc/core/utils/` holds four classes and five traits.
`Utilities` is the aggregator - `use Arrays, Paths, Query, Terms, WordPress;` - plus the `PAGE_TYPE_*` constants and a few helpers of its own, and is how the traits are meant to be consumed.

| Name | Kind | Purpose |
|---|---|---|
| `Utilities` | class | Aggregator; page-type constants; `isJson`, `validate_url`, `getRandomString`, `remoteFileExists`, `addAdminNotice` entry |
| `Arrays` | trait | Associative array search/insert, recursive diff, JSON for HTML data attributes |
| `Paths` | trait | `deltree`, `listFolderFiles`, `mkpath`, `urlToPath`, `pathToUrl`, current-URL helpers |
| `Query` | trait | Page-type detection: `getCurrentPageType`, `isStaticHome`, `isBlogPage`, ... |
| `Terms` | trait | Term creation and hierarchical taxonomy data |
| `WordPress` | trait | The largest: post metas, thumbnails, `createAttachment`, `setFeaturedImageFromUrl`, maintenance mode, AJAX endpoints, sign-in, admin notices, plugin checks |
| `Dates` | class | `getToday`, `isValidTimezone`, `getDateTimeZoneFromString`, `getDefaultDateTimeZone` |
| `Posts` | class | Ancestors, children, tree-by-id, `getPostIdByMeta` |
| `Cache` | class | Transient wrapper: `setTransient`, `getTransient`, `canSetTransient` |

`Dates::getDefaultDateTimeZone()` is the theme-wide timezone source: `wp_timezone_string()`, falling back to `date_default_timezone_get()` then `UTC`.

### 7.8 Multilanguage

`inc/core/multilanguage/helpers/` contains three independent static adapters - `Polylang`, `WPML`, `TranslatePress` - with no loader, no interface and no abstraction over them.
It is not a pluggable layer; each class is a direct wrapper around one plugin, PSR-4 autoloaded on demand.

None of the three is referenced anywhere in this branch.
`inc/multilanguage-functions.php` ignores them entirely and returns `get_bloginfo('language')` / `get_locale()`, with the timezone name hardcoded to `Europe/Rome`.
`Polylang` is the most complete and the de-facto intended target.

Two latent faults: `Polylang.php:7` imports a `PolyLangWooCommerce` trait that does not exist here, and `TranslatePress.php:39` calls `getWPDbFromTRPQuery()` without `self::`.

### 7.9 Other `inc/` entries

| Path | Purpose |
|---|---|
| `inc/hooks/hooks.php` | Site hardening: trims REST endpoints, strips users from the sitemap, disables feeds and oembed data, generic login errors, `upload_mimes`, `map_meta_cap` rules |
| `inc/hooks/init.php` | `after_setup_theme` `setup()` (theme supports, text domain, image sizes), menus, head cleanup, a health-check endpoint, disables plugin auto-updates |
| `inc/hooks/layout.php` | Binds the `waboot/layout/*` zones to `view-parts` templates |
| `inc/hooks/posts-and-pages.php` | Title rendering and the whole `waboot/article/footer` chain |
| `inc/hooks/widget-areas.php` | Registers widget areas and attaches each to its declared `render_zone` |
| `inc/hooks/gravityform/hooks.php` | A single `gform_notification` filter, loaded from `functions.php` |
| `inc/hooks/woocommerce.php` | WooCommerce layout wrappers - **not loaded**, see below |
| `inc/template-functions.php` | 16 data-shaping functions (widget areas, page titles, trimmed excerpts, hierarchical data, logo) |
| `inc/template-rendering.php` | 5 echoing renderers (post navigation, gallery, widget area, comment, custom head) |
| `inc/template-tags.php` | 5 template-facing tags (`site_head`, `widgetArea`, `wrappedTitle`, `trimmedExcerpt`, `theLogo`) |
| `inc/services/` | External API clients; currently only `MailChimpService` |
| `inc/cli/` | The two concrete WP-CLI commands |

## 8. WooCommerce status

Routing and templates are genuinely WooCommerce-free: there is no `inc/core/woocommerce/`, no theme-level `woocommerce/` template directory, and `addMainContent()` has no WooCommerce branch.

Residue that is still in the tree:

- `add_theme_support('woocommerce')` is **live** at `inc/hooks/init.php:48`.
  Harmless without the plugin, but it is active code.
- `inc/hooks/woocommerce.php` is committed and contains working WooCommerce hooks, but it is commented out of `functions.php` and additionally self-guards on `function_exists('is_woocommerce')`.
- `addons/shared-functions.php` returns `\WC_Product`; `multilanguage/helpers/WPML.php` has product-oriented methods.
- The nine `assets/src/sass/frontend/woocommerce/*` partials still exist on disk, but their `@import` block in `main.scss` is commented out, so they are **not** compiled into `main.min.css`.

## 9. Hooks and filters reference

### Actions

| Hook | Fired at | Purpose |
|---|---|---|
| `waboot/layout/page-before` | `header.php:8` | Right after `<body>` opens |
| `waboot/layout/header` | `header.php:12` | Site header; bound in `inc/hooks/layout.php` |
| `waboot/layout/main-top` | `templates/wrapper-start.php:3` | Inside `<main>`, above `.main__grid` |
| `waboot/layout/title` | `templates/wrapper-start.php:7` | Main page title; bound in `inc/hooks/posts-and-pages.php` |
| `waboot/layout/content` | `index.php:9` | **The router.** Only `addMainContent()` is hooked |
| `waboot/layout/aside` | `sidebar.php:4` | Sidebar widget zone |
| `waboot/layout/main-bottom` | `templates/wrapper-end.php:7` | Inside `<main>`, after `.main__grid` |
| `waboot/layout/footer` | `footer.php:3` | Site footer |
| `waboot/layout/page-after` | `footer.php:7` | After `</footer>`, before `wp_footer()` |
| `waboot/layout/title/before` / `/after` | `templates/view-parts/main-title.php:2,4` | Around the `<h1>` |
| `waboot/head/start` / `waboot/head/end` | `inc/template-tags.php:13,15` | Around `wp_head()` |
| `waboot/head/meta` | `templates/parts/meta.php:11` | Extra meta tags |
| `waboot/widget_area/before` / `/after` | `inc/template-tags.php:20,24` | Around any widget area |
| `waboot/widget_area/{areaId}/before` / `/after` | `inc/template-tags.php:21,23` | Around one specific area |
| `waboot/article/footer` | the `templates/parts/content-*.php` single views | Post meta footer; 7 callbacks registered in `inc/hooks/posts-and-pages.php` |
| `waboot/article/list/footer` | the `templates/parts/content-*.php` list views | Same for archive context; no default callbacks |

### Filters

| Hook | Fired at | Purpose |
|---|---|---|
| `waboot/layout/content/template` | `inc/core/hooks.php:55` | **Routing override**: swap the `[slug, name]` pair before `get_template_part()` |
| `waboot/custom_template_parts_directory` | `inc/core/hooks.php:72` | Relocate the `templates/parts-tpl` scan directory |
| `waboot/addons/disabled` | `addons/functions.php:42` | Addon directory names to skip (dormant here) |
| `waboot/assets/styles/default_media` | `inc/core/AssetsManager.php:77` | Default `media` for enqueued stylesheets |
| `waboot/layout/posts_wrapper/class` | `templates/blog.php:2`, `templates/archive/archive.php:4` | CSS class of the post-list wrapper |
| `waboot/main/title` | `inc/hooks/posts-and-pages.php:50` | Final main title string |
| `waboot/main/title/display_flag` | `inc/hooks/posts-and-pages.php:51` | Suppress the main title entirely |
| `waboot/main/title/tpl` / `tpl_args` | `inc/hooks/posts-and-pages.php:68,69` | Swap the title view or its variables |
| `waboot/main/title/prefix` / `suffix` | `inc/template-tags.php:38,39` | Wrap the title |
| `waboot/blog/title` | `inc/hooks/posts-and-pages.php:36` | Title for the default (non-static) home |
| `waboot/layout/post_navigation/display_pagination_flag` | `inc/template-rendering.php:35` | Toggle pagination output |
| `waboot/layout/comment_form_args` | `templates/comments.php:66` | `comment_form()` arguments |
| `waboot/navigation/main/class` | the three `view-parts` nav templates | `menu_class` for `wp_nav_menu()` |
| `waboot/head/use_custom_head` | `inc/template-tags.php:9` | Bypass `wp_head()` for a custom head view |
| `waboot/head/custom_head/tpl` / `args` | `inc/template-rendering.php:179,183` | Template and variables for that custom head |
| `waboot/logo` | `inc/template-functions.php:467` | Logo markup |
| `waboot/widget_areas/available` | `inc/template-functions.php:54` | Registerable widget-area definitions |

## 10. Gotchas for future changes

- Do not assume `index.php` routes anything.
  All dispatch lives in `addMainContent()` and the `waboot/layout/content/template` filter, and the two `throw`s run before that filter.
- Do not add a root-level `page.php`/`single.php`/`archive.php`.
  The theme relies on everything collapsing to `index.php`; adding one silently bypasses the whole routing layer.
- Do not reintroduce `templates/author.php`: author archives are unified with the generic archive chain.
- `templates/image.php` is referenced by the router but does not exist, so image attachment pages render empty.
  Add the file if you need those pages, rather than changing the router.
- The custom-partial slug regex accepts only `[a-z_-]+` and is not right-anchored, so digits truncate the slug and uppercase filenames are skipped silently.
- `Theme::renderView()` has **no** `$pathIsRelative` parameter here and does not use `ViewFactory`.
  Code copied from the ecommerce branch that passes a fourth argument will fail.
- `Theme::renderView()` echoes a caught exception's raw message straight into the page output.
  A misspelled template path prints a `ViewException` message inline in the markup instead of failing or logging.
- `preventEmails()` and `unblockEmails()` are **fatal**: `Waboot\inc\core\EmailDisabler` does not exist in this branch.
  Port the class from `main` before calling either.
- `AbstractCSVParserCommand` needs `league/csv`, which is not installed.
  Add it to `composer.json` before subclassing.
- `SentryAlertDispatcher` always throws, because `sentry/sdk` is not installed.
- `Theme::logToFile()` swallows every exception in an empty `catch`.
  Never rely on a log call as proof that something happened.
- `LoggerFactory::create()` passes a second argument to `pushHandler()`, which takes one, so the intended `INFO` minimum level is ignored and the handler logs at Monolog's default.
- Log files are not rotated, only named per day, and nothing prunes `wp-content/logs/`, `wp-content/mail-logs/` or `wp-content/cli-logs/`.
- `AbstractRepository` discards a `Manager` passed to its constructor and leaves the typed `$db` property uninitialized; only the no-argument form works.
- `Alerts::dispatchEmailAlert()` still instantiates the `@deprecated` `AlertDispatcher`.
  Prefer `AlertDispatcherFactory::createEmailDispatcher()` for new code.
- Alert constructors take a `\DateTimeZone` object, never a timezone name string.
- Enabling the addon system while `addons/packages/` is missing is a `TypeError`; create the directory first.
- `assets/dist/css/gutenberg.min.css` is committed but produced by no build task.
  Changing `assets/src/sass/backend/gutenberg.scss` has no effect until the file is wired into the build.
- Everything else under `assets/dist/` is gitignored, so a fresh clone serves no CSS or JS until `npm run assets:build` has run.
- There are no PHP enums anywhere, although `docs/code-guidelines.md` asks for them over repeated strings.
  The four candidates are `MonologLoggingLevels`, `Theme::LOG_LEVEL_*`, `AlertDispatcher::DISPATCH_METHOD_*` and `Utilities::PAGE_TYPE_*`.
- `composer.complete.json` is not the active manifest; `composer.json` is.
