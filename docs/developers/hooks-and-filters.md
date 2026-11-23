# Hooks and filters

The plugin's public extension surface. Each entry here is a contract: it won't be renamed or removed without a deprecation period. How the plugin works internally is in [How it works](how-it-works.md).

## `hwpc_updater_enabled`

Filter. Return `false` to stop the plugin checking GitHub for updates. It affects this plugin only. Values such as `'0'`, `'no'` and `'off'` also count as false.

```php
add_filter( 'hwpc_updater_enabled', '__return_false' );
```

## Running after the page conditions

The plugin adds no filter of its own around the display decision; it hooks GeneratePress Premium's `generate_element_display` at priority **20**, once for the paging condition and once for the URL path. To make your own decision with both already applied, hook the same filter at a priority above 20:

```php
add_filter( 'generate_element_display', function ( $display, $element_id ) {
	// $display already reflects GeneratePress's rules and both page conditions.
	return $display;
}, 30, 2 );
```

Keep to the same rule the plugin follows — only take an Element away — and don't type the parameters: another callback may pass something other than a boolean.

## Stored data

The page conditions are stored in post meta under fixed keys, listed in [How it works › Stored data](how-it-works.md#stored-data). Read them if you need to, but write them only through the Element edit screen.
