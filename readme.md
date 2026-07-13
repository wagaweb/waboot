# Waboot Theme

Waboot is a WordPress theme aimed at ecommerce development with WooCommerce. The focus is on speed, usability and modularity; it is aimed mainly at professionals and web agencies.

## Building

- `composer install`
- `npm install`
- `npm run assets:build && npm run checkout:build:prod && npm run catalog:build:prod`

# Addons
<a name="#addons"></a>

Addons are small, self-contained "mini plugins" dedicated to the theme, living under `addons/packages/<addon-name>/`. Each one is loaded by requiring its own `bootstrap.php`, if present.

Every addon found under `addons/packages/` is active by default. An addon can be disabled by hooking the `waboot/addons/disabled` filter and returning its directory name, e.g.:

```php
add_filter('waboot/addons/disabled', function($disabled){
    $disabled[] = 'sizeguide';
    return $disabled;
}, 20);
```

The theme itself disables the `invoicing` addon by default this way. See [CLAUDE.md](./CLAUDE.md) for the full loading mechanics and addon package conventions.

<span style="font-size:smaller"><a href="#waboot-theme">Back to top.</a></span> 

# Template system

With Waboot, we revisited some features of the WordPress template system with the following goals:

- To stay as [DRY](https://en.wikipedia.org/wiki/Don%27t_repeat_yourself) as possible.
- To make the practice of overriding templates in child themes as agile as possible.
- To adhere to a "Convention over configuration" principle.
 
In particular:

- The first entry point is the `index.php` file. In classic WordPress this file is used as a last-resort solution.

    We did that in order to keep the number of template files in the root folder to a minimum and to avoid repeating `get_header()`, `get_footer()`, `get_sidebar()` calls and layout wrappers across multiple files.
    
    `index.php` itself is a fixed layout (`get_header()`, wrapper start/end, `get_footer()`); in between it fires a single action, `waboot/layout/content`. The function hooked to that action is the actual router: it picks the right template part for the current request (front page, single, page, archive, search, 404...). Which template gets loaded is filterable via `waboot/layout/content/template`, so the routing decision can be overridden by a child theme/plugin without touching core code.
    
- Classic WordPress template files can be found under `templates/`.

    These templates are stripped of the usual header, footer and sidebar includes: header/footer are handled by `index.php`, the sidebar by the closing layout wrapper it includes.
        
- Archive templates (author archives included, since v3.1.0) can be easily customized in child themes without adding many `archive-$posttype.php`, `taxonomy-$taxonomy.php` or `author-$nicename.php` files to the root folder.

    Waboot's [`archive.php`](https://github.com/wagaweb/waboot/blob/master/templates/archive.php) file automatically includes the right sub-template under `templates/archive/`, trying in order (most specific first): `author-$nicename.php` → `author-$id.php` → `author.php` for author archives; `category-$slug.php` → `category-$id.php` → `category.php` for category archives; `$taxonomy-$slug.php` → `taxonomy-$taxonomy-$slug.php` → `taxonomy-$taxonomy.php` → `taxonomy.php` for other taxonomies; `archive-$posttype.php` for post type archives; `date.php` for date archives; falling back to the generic `archive.php` in that same folder otherwise.
    
- Custom templates (those that are selectable from the dashboard) can be treated as partials as well.

    Waboot automatically recognizes any file called `content-$slug.php` (`$slug` matching `[a-z_-]+`) under `templates/parts-tpl` as a custom template partial and makes it selectable from the dashboard. The label shown in the dashboard dropdown is derived from the filename itself, not from a `Template Name:` header comment.
    
    Then, [`page.php`](https://github.com/wagaweb/waboot/blob/master/templates/page.php) automatically includes the partial you selected, falling back to `templates/parts/content-page.php` if it can't be found.

For the full implementation details (exact hooks, filters and file/line references), see [CLAUDE.md](./CLAUDE.md).

<span style="font-size:smaller"><a href="#waboot-theme">Back to top.</a></span>

# Database

Waboot ships with [`illuminate/database`](https://github.com/illuminate/database) (Laravel's query builder) to run structured queries against the WordPress database, alongside the classic `$wpdb`. It's exposed through `Waboot()->DB()`, which lazily boots a `Capsule\Manager` connection using the same credentials and table prefix as WordPress, and through the `Query` facade for quick one-off queries:

```php
use Waboot\inc\core\facades\Query;

$rows = Query::on('my_table')->where('status', 'active')->get();
```

Only the query builder and schema builder are used — there's no Eloquent ORM, no migrations. See [CLAUDE.md](./CLAUDE.md) for connection details and usage patterns.

<span style="font-size:smaller"><a href="#waboot-theme">Back to top.</a></span>

# Logging

Waboot uses [Monolog](https://github.com/Seldaek/monolog) for file-based logging, wired through `Theme.php` and the helper functions in `inc/core/helpers/logs.php`. Every log call targets a named "channel" (e.g. `waboot-log`), which becomes a per-day log file under `wp-content/logs/`.

```php
use function Waboot\inc\core\helpers\logError;

logError('Something went wrong', 'my-feature-name');
```

Shorthand helpers (`logInfo`, `logWarning`, `logError`, `logException`, and their `*ToFile` variants) cover the common cases; see [CLAUDE.md](./CLAUDE.md) for the full mechanics, file locations and existing channels.

<span style="font-size:smaller"><a href="#waboot-theme">Back to top.</a></span>

# Rendering views

Templates are rendered through a small view layer under `inc/core/mvc/` (`View`/`HTMLView`, built via `ViewFactory`), exposed through three equivalent entry points:

```php
Waboot()->renderView('templates/view-parts/main-header.php', ['foo' => 'bar']);

use function Waboot\inc\core\helpers\{renderHtmlView, getHtmlView};
renderHtmlView('templates/view-parts/main-header.php', ['foo' => 'bar']); // echoes
$html = getHtmlView('templates/view-parts/main-header.php', ['foo' => 'bar']); // returns a string
```

All three resolve the template path the same way (child theme, then parent theme, unless an absolute path is requested) and support the same predefined wrapper variables. See [CLAUDE.md](./CLAUDE.md) for the class hierarchy and the differences between the three.

<span style="font-size:smaller"><a href="#waboot-theme">Back to top.</a></span>

# Sending emails

Waboot wraps `wp_mail()` in a small `Mail`/`MailAddress`/`MailAttachment`/`MailHeader` object model, exposed through the `sendMail()` helper:

```php
use function Waboot\inc\core\helpers\sendMail;

sendMail('Subject', 'Body', 'someone@example.com');
```

It accepts a single recipient or an array, optional custom headers/attachments, and sends as HTML by default. `preventEmails()`/`unblockEmails()` let you redirect all outgoing mail to a void address (handy on staging). See [CLAUDE.md](./CLAUDE.md) for the `Mail` class internals and known caveats.

<span style="font-size:smaller"><a href="#waboot-theme">Back to top.</a></span>

# Alert system

Alongside the Monolog-based logging above, Waboot has a separate **Alert System** (`inc/core/alert/`) for pushing operational notifications to a file, an email, or a Google Chat webhook — mainly used by WP-CLI commands to report stuck/failed scripts. Entry points are the `Alerts` facade and the helpers in `inc/core/helpers/alerts.php`:

```php
use Waboot\inc\core\facades\Alerts;

Alerts::dispatchEmailAlert('Something failed', 'Details here...', 'admin@example.com');
```

See [CLAUDE.md](./CLAUDE.md) for the dispatcher architecture (file/email/Google Chat) and a few known rough edges in this system.

<span style="font-size:smaller"><a href="#waboot-theme">Back to top.</a></span>