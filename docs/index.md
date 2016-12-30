---
title: Waboot Theme
---

Waboot aims to be a general purpose theme focused on development speed and flexibility; plus, it's component-based nature allows developers to use only the features they need and helps them to keep the source organized and easy to maintain.

A sample theme child is provided to get you started immediately.

## Usage

- Download the precompiled version [here](http://update.waboot.org/resource/get/theme/waboot) or download this repository.
- Copy `waboot-child` directory into `wp-content/` and rename it accordingly to your project.
- Follow the readme inside the child theme.

## Key concepts

Waboot is based on the concept of **zones** and is built through **components**. 

Components are self-contained micro-plugins that implement specific feature (like: breadcrumbs, lazyloading, different types of headers and footers...).

Zones are layout partials that, by default, are rendered blank (or not rendered at all) unless some component is attached to them. 

Components hook to zones with a specific weight. Components with lowest weight are rendered before components with higher weight.
 
You can move around components by edit their render zone and weight through WordPress dashboard.

Learn more about [zones]({{ site.baseurl }}/zones) and [components](#).

## No more template overriding

Waboot feature a revisited template hierarchy system which goal is to keep template overriding at minimum.

Learn more about [here](#).