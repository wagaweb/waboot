# Readme

## Basic usage

Simply copy-paste this directory to wp-content/themes and rename it.

Read the `functions.php` file comments to further customize the child theme.

## Advanced usage

A basic build system is provided for js and styles.

- Install dependencies with npm or [yarn](https://yarnpkg.com/) (recommended)
- Use the following commands or create your own: `gulp compile_css`, `gulp compile_js`, `gulp setup` (combines the two).

## Structure

- Use `assets/src/js/snippets.js` for all your quick js scripts. You must enable it by remove comments from `functions.php`
- Use `assets/src/js/main.js` as starting point for more complex applications. You must build it via gulp.
