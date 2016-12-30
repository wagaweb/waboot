---
title: Waboot Layout Zones
---

Waboot provide flexibility through **zones**. Zones are layout partials to which components can attach. 

## Zones and components

Components can be easily moved between zones through WordPress dashboard:

!["Selecting a zone"]({{ site.baseurl }}/assets/images/zones_01.png )

Here you can change the component position (zone) and priority (weight). Like WordPress hook system, lower priority components are rendered before the higher ones.

## Predefined zones

Waboot feature some predefined zones that can be visualized here:

## Adavanced topics

Under the hood zones are just WordPress actions with standardized names. \Waboot\Layout provides a quick API to them.

### Create a new zone

Create a new zone is easy:

- Register the zone
    ```
    Waboot()->layout->create_zone(string <zone_name>, [\WBF\components\mvc\View|string|FALSE <zone_view>, array <zone_params>]);
    ```
    
    The only required param is the zone name. Zone name can be any [a-z-] string. 
    
    The view (when provided) can be a WBF View or a string. If a string is provided, the template will be retrieved via locate_template.

- Render the zone
    ```
    //If a template is provided you have to call this inside the view:
    Waboot()->layout->do_zone_action(<zone_name>);
    //If no template is provided, use this other one:
    Waboot()->layout->render_zone(<zone_name>);
    ```   

### Hook to a zone programmatically

...


