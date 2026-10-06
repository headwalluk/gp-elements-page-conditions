# Getting started

## Requirements

- WordPress 6.0 or later
- PHP 8.0 or later
- GeneratePress Premium 2.0 or later, with the **Elements** module switched on (Appearance → GeneratePress → Elements)

Without the Elements module the plugin does nothing, and shows an admin notice saying so to anyone who can activate plugins.

## Install

1. Download `gp-elements-page-conditions.zip` from the [latest release](https://github.com/headwalluk/gp-elements-page-conditions/releases/latest)
2. WordPress admin → Plugins → Add New → Upload Plugin → choose the zip → Install Now → Activate
3. Edit any Element (Appearance → Elements) and look for the **Paging Condition** box

There are no settings screens. Everything is set per Element; see [Using paging conditions](using-paging-conditions.md).

The plugin is not on wordpress.org, so always install from the GitHub release zip. The **Code → Download ZIP** button on GitHub gives you a folder named after the branch, which WordPress installs as a separate plugin.

## Updates

From version 1.1.0 the plugin checks GitHub for new releases and offers them on the normal Dashboard → Updates screen, like any other plugin. It checks at most every 12 hours, and backs off for an hour if GitHub can't be reached.

Sites still on **1.0.0** have no updater. Install the latest release zip by hand once — WordPress offers to replace the existing copy — and updates are automatic from then on.

To turn update checks off, for example on a staging site or to hold a site on its current version, add this to a site-specific plugin or the child theme's `functions.php`:

```php
add_filter( 'hwpc_updater_enabled', '__return_false' );
```

## Uninstalling

Deleting the plugin leaves each Element's stored condition in place, so reinstalling brings the settings back. With the plugin gone the conditions are simply ignored, and every Element shows according to its GeneratePress display rules alone.

## Moving from the ACF version

Some Headwall sites used an earlier version of this feature built from an ACF field group and a `generate_element_display` filter in the child theme. The plugin reads the same stored values, so existing Elements keep their settings:

1. Install and activate this plugin
2. Delete the ACF field group that provided the `archive_paging_visibility` field
3. Remove the `generate_element_display` filter from the child theme's `functions.php`

Until the theme filter is removed, it keeps running alongside the plugin.

One behaviour changes. The theme version only acted on archives, so the blog index and search results were never affected. The plugin covers those too, and treats any unpaginated page as page one — so an Element set to *Never show on page one* with a broad display rule (say, *Entire Site*) will now also be hidden on ordinary pages and posts. Narrow that Element's display rules if that isn't what you want.
