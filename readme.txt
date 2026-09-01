=== GP Elements Page Conditions ===
Contributors: headwallhosting
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a paging condition to GeneratePress Elements, so an Element can be limited to page one of a paginated view, hidden from it, or restricted to specific page numbers.

== Description ==

GeneratePress Premium's Elements let you target a taxonomy archive, but not a
particular *page* of it. This plugin adds a "Paging Condition" meta box to every
Element, letting you serve a large, SEO-heavy hero and a featured-post layout on
page one of an archive, and a compact hero with a plain list on pages two
onwards.

The condition is applied on top of the Element's existing display rules — it can
only hide an Element that GeneratePress was already going to show, never reveal
one that was excluded.

= Conditions =

* **Pass through** — no paging condition (the default).
* **Only show on page one** — hidden once the visitor pages past the first page.
* **Never show on page one** — hidden on the first page, shown from page two on.
* **Only show on pages…** — shown only on the page numbers you list.

The page list is comma separated and accepts single pages (`3`), ranges (`2-5`),
and open ranges (`4-` for page four onwards, `-3` for up to page three). Anything
unparseable is dropped on save. An empty list passes through rather than hiding
the Element everywhere.

= Where it applies =

Anywhere a page number is meaningful: term and post type archives, the blog
index, search results, and multi-page single posts split with `<!--nextpage-->`.
On any unpaginated view the current page counts as page one.

= Implementation =

No dependencies beyond GeneratePress Premium. The condition is stored in the
`archive_paging_visibility` post meta key (and `archive_paging_pages` for the
page list) and applied through GeneratePress Premium's `generate_element_display`
filter at priority 20, which covers block, layout, hook and hero Elements alike.

The meta key matches the ACF-based implementation this plugin replaces, so a site
migrating from that setup keeps its existing Element settings without a
migration step.

== Changelog ==

= 1.0.0 =
* Initial release.
