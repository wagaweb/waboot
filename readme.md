[Zones](#zones) | [Addons](#addons) | [Template system](#template-system)

# Waboot Theme

Waboot is a Wordpress theme aimed to ecommerce development with WooCommerce. The focus is on on speed, usability and modularity; it is aimed mainly to professionals and web agencies.

## Building

- `composer install`
- `npm install`
- `npm run assets:build && npm run checkout:build:prod && npm run catalog:build:prod`
 
## No more template overriding

Waboot feature a redesigned template hierarchy system which goal is to keep template overriding at minimum.

Learn more about [here](#template-system).

### Widget areas

Waboot features an easy way to register new widgets areas, edit the default ones and attach them to zones.

...

<span style="font-size:smaller"><a href="#waboot-theme">Back to top.</a></span>

# Addons
<a name="#addons"></a>
 
 ...
 
<span style="font-size:smaller"><a href="#waboot-theme">Back to top.</a></span> 

# Template system

With Waboot, we revisited some features of the WordPress template system with the following goals:

- To stay as [DRY](https://en.wikipedia.org/wiki/Don%27t_repeat_yourself) as possible.
- To make as much agile as possible the practice of templates overriding in child themes.
- To adhere to a "Convention over configuration" principle.
 
In particular:

- The first entrance point is the `index.php` file. In classic WordPress this file is used as last resort solution.

    We did that in order to keep the template files in the root folder at minimum and to avoid the repeating of get_header\get_footer\get_sidebar and layout wrappers among multiple files.
    
    `index.php` acts as a router and includes the correct template partial based on current request.
    
    We achieved that by rendering here a [zone](#zones) called "content", which has (as primary default hook) a [function](https://github.com/wagaweb/waboot/blob/master/inc/hooks/zones_std_hooks.php) that respond to requests and includes the correct partial.
    
- Classic WordPress template files can be found under `templates/`.

    These templates are stripped of the usual header, footer and sidebar includes, which are handled by `index.php`.
        
- Archive templates can be easily customized in child themes without many `archive-$posttype.php` or `taxonomy-$taxonomy.php` files in root folder.

    Waboot [`archive.php`](https://github.com/wagaweb/waboot/blob/master/templates/archive.php) file automatically includes the right `archive-$posttype.php` or `taxonomy-$taxonomy.php` file under `templates/archive` folder.

- Author templates follows a similar logic: Waboot [`author.php`](https://github.com/wagaweb/waboot/blob/master/templates/author.php) file automatically includes the right `author-$nicename.php` or `taxonomy-$id.php` file under `templates/author` folder.
    
- Custom templates (those that are selectable from the dashboard) can be treated as partials as well.

    Waboot automatically recognizes any file called `content-[a-z]+` under `templates/parts-tpl` as custom template partial and make it selectable from the dashboard.
    
    Then, [`page.php`](https://github.com/wagaweb/waboot/blob/master/templates/page.php) automatically includes the partial you selected.
    
- We used [WBF Views](https://github.com/wagaweb/wbf/tree/master/src/components/mvc) wherever possible. WBF Views has some advantages over classic `get_template_part()` function.

    Views can be found under: `templates/view-parts`.    
    
<span style="font-size:smaller"><a href="#waboot-theme">Back to top.</a></span>