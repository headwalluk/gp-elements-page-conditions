# Getting started

## Requirements

- WordPress 6.9 or later
- PHP 8.2 or later
- GeneratePress Premium 2.0 or later, with the **Elements** module switched on (Appearance → GeneratePress → Elements)

Without the Elements module the plugin does nothing, and shows an admin notice saying so to anyone who can activate plugins.

## Install

1. Download `gp-elements-page-conditions.zip` from the [latest release](https://github.com/headwalluk/gp-elements-page-conditions/releases/latest)
2. WordPress admin → Plugins → Add New → Upload Plugin → choose the zip → Install Now → Activate
3. Edit any Element (Appearance → Elements) and look for the **Page Conditions** box

There are no settings screens. Everything is set per Element; see [Using page conditions](using-page-conditions.md).

The plugin is not on wordpress.org, so always install from the GitHub release zip. The **Code → Download ZIP** button on GitHub gives you a folder named after the branch, which WordPress installs as a separate plugin.

## Updates

From version 1.1.0 the plugin checks GitHub for new releases and offers them on the normal Dashboard → Updates screen, like any other plugin. It checks at most every 12 hours, and backs off for an hour if GitHub can't be reached. Each check asks `api.github.com` for the latest release, then reads that release's plugin header from `raw.githubusercontent.com` to learn the PHP and WordPress versions it needs. Both are ordinary WordPress HTTP requests, so like any other they carry WordPress's default user agent, which names the WordPress version and the site's address.

Version 2.0.0 raised the requirements to WordPress 6.9 and PHP 8.2. On an older site the update is still listed, but WordPress refuses to install it, naming the version it needs. Stay on 1.1.1 until the site or server is upgraded. From 2.1.0, the update check passes those requirements to WordPress, so an update the site can't run is marked as incompatible on the Updates screen and is never installed automatically.

Sites still on **1.0.0** have no updater. Install the latest release zip by hand once — WordPress offers to replace the existing copy — and updates are automatic from then on.

To turn update checks off, for example on a staging site or to hold a site on its current version, add this to a site-specific plugin or the child theme's `functions.php`:

```php
add_filter( 'hwpc_updater_enabled', '__return_false' );
```

## Uninstalling

Deleting the plugin leaves each Element's stored conditions in place, so reinstalling brings the settings back. Only the cached update check is removed. With the plugin gone the conditions are simply ignored, and every Element shows according to its GeneratePress display rules alone.

## Moving from the ACF version

Some Headwall sites used an earlier version of this feature built from an ACF field group and a `generate_element_display` filter in the child theme. The plugin reads the same stored values, so existing Elements keep their settings:

1. Install and activate this plugin
2. Delete the ACF field group that provided the `archive_paging_visibility` field
3. Remove the `generate_element_display` filter from the child theme's `functions.php`

Until the theme filter is removed, it keeps running alongside the plugin.

One behaviour changes. The theme version only acted on archives, so the blog index and search results were never affected. The plugin covers those too, and treats any unpaginated page as page one — so an Element set to *Never show on page one* with a broad display rule (say, *Entire Site*) will now also be hidden on ordinary pages and posts. Narrow that Element's display rules if that isn't what you want.
