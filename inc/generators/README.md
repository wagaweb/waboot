# Generators

Generators are a feature that allows developers to specify configurations in JSON files (_generators_), that can be later imported.

Generators are good to create different startup configurations for your child themes. In Waboot they are used to bootstrap different configurations of Components and Theme Options.

## Generator file

A generator file is a simple JSON file with specific values.

```
{
  "name": "Generator name",
  "description": "Generator description",
  "preview": "path/to/preview/file/relative/to/theme/root",
  "classname": "FoobarGenerator",
  "components": ["component_a","component_b"],
  "options": {
    "option_a": "value_a"
    "option_b": "value_b"
  }
  "pre_actions": ["method_a","method_b"],
  "actions": ["method_c"]
}
```

Only **name** in mandatory. **classname** is mandatory only if **pre_actions** or **actions** are used.

**pre_actions** and **actions** keys specify methods to execute on **classname** instance.

These methods can do every you can think of, but they have to return `true` or throw an `\Exception`.

## How it works

When the user select a generator from the interface, Waboot proceeds to apply the configuration specified in it by perform the operations in a specific order:

- If **pre_actions** are defined, a new instance of **classname** is created and the methods specified in **pre_actions** are executed in order.
- If **components** are defined, they are activated (and all other components are disabled)
- If **options** are defined, Waboot executes `update_option()` for each of them.
- If **actions** are defined, a new instance of **classname** is created and the methods specified in **actions** are executed in order.

## Developers notes

### Changing\adding generators directories

There is a filter available: `waboot/generators/directories` . It takes the current directories as the only argument.

### The preview field

**preview** is a relative path name to the preview image of the generator.

If the generator json file is in a theme, the base path for that preview will be the theme directory uri, otherwise the [`WBF\components\utils\Paths::path_to_url()`](https://github.com/wagaweb/wbf/blob/master/src/components/utils/Paths.php#L286) of the file will be used.

During `Waboot\Theme::get_generators()` execution the property `preview_basepath` is added to the properties of the generator.