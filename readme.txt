=== GP Elements Page Conditions ===
Contributors: Headwall
Tags: generatepress, elements, archives, pagination, hero
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a paging condition to GeneratePress Elements, so an Element can be limited to page one of a paginated view, hidden from it, or restricted to specific page numbers.

== Description ==

GeneratePress Premium's Elements can target a taxonomy archive, but not a particular page of it. This plugin adds a "Paging Condition" meta box to every Element, letting you serve a large, SEO-heavy hero and a featured-post layout on page one of an archive, and a compact hero with a plain list on pages two onwards.

= Conditions =

* **Pass through** - no paging condition (the default)
* **Only show on page one** - hidden once the visitor pages past the first page
* **Never show on page one** - hidden on the first page, shown from page two on
* **Only show on pages...** - shown only on the page numbers you list

The page list is comma separated and accepts single pages (3), ranges (2-5), and open ranges (4- for page four onwards, -3 for up to page three). Anything unparseable is dropped on save. An empty list passes through rather than hiding the Element everywhere.

= Where it applies =

Anywhere a page number is meaningful: term and post type archives, the blog index, search results, and multi-page single posts split with the nextpage quicktag. On any unpaginated view the current page counts as page one.

The condition is applied on top of the Element's existing display rules - it can only hide an Element that GeneratePress was already going to show, never reveal one that was excluded.

= Implementation =

No dependencies beyond GeneratePress Premium. The condition is stored in the `archive_paging_visibility` post meta key (and `archive_paging_pages` for the page list) and applied through GeneratePress Premium's `generate_element_display` filter at priority 20, which covers block, layout, hook and hero Elements alike.

= Translations =

Ships with de_DE, el_GR, en_GB, es_ES, fr_FR, it_IT, nl_NL and pl_PL translations.

== Installation ==

1. Download `gp-elements-page-conditions.zip` from the [latest GitHub release](https://github.com/headwalluk/gp-elements-page-conditions/releases/latest).
2. WordPress admin → Plugins → Add New → Upload Plugin → choose the zip → Install Now → Activate.
3. Edit any GeneratePress Element and set its condition in the "Paging Condition" box.

The Elements module of GeneratePress Premium must be active. Without it the plugin does nothing and shows an admin notice.

The plugin receives future updates automatically via its bundled GitHub updater. The plugin is not listed on wordpress.org.

== Frequently Asked Questions ==

= Does this work with all Element types? =

Yes. Block, Layout, Hook and Hero Elements are all filtered through the same GeneratePress hook.

= Can it show an Element that GeneratePress had excluded? =

No. The condition only ever takes an Element away, so an Element's own display rules always come first.

= I used the ACF version of this on my theme. Will my settings carry over? =

Yes - the meta key is unchanged. Activate the plugin, then remove the ACF field group and the theme filter. Note that the theme version applied to archives only; this plugin also covers the blog index, search results and multi-page posts.

== Changelog ==

= 1.1.1 =
* Re-release of 1.1.0, whose tag produced no release build.

= 1.1.0 =
* Added: automatic updates from GitHub Releases.
* Fixed: an unexpected value (such as null) from another plugin on the GeneratePress display filter could cause a fatal error on the front end.

= 1.0.0 =
* Initial release.
