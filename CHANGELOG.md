# Changelog

All notable changes to GP Elements Page Conditions will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] - 2026-09-01

Initial release. Extracted from the `headwall-hosting` child theme, where the
same behaviour was provided by an ACF field group plus a `generate_element_display`
filter in `functions.php`.

### Added
- **Paging Condition meta box on GeneratePress Elements** — a native WordPress
  meta box on the `gp_elements` edit screen with four conditions: *Pass through*,
  *Only show on page one*, *Never show on page one*, and *Only show on pages…*.
- **Page number targeting** — the *Only show on pages…* condition takes a comma
  separated list accepting single pages (`3`), ranges (`2-5`) and open ranges
  (`4-` for page four onwards, `-3` for up to page three). Unparseable segments
  are discarded on save; an empty list passes through rather than hiding the
  Element on every page.
- **Admin notice** when the Elements module of GeneratePress Premium is not
  active, since the plugin is inert without it.

### Changed from the child theme implementation
- **No ACF dependency** — the field is a native meta box, so the plugin drops
  into any site without requiring ACF.
- **Applies wherever pagination is meaningful** — term and post type archives,
  the blog index, search results and multi-page single posts. The theme version
  tested `is_archive()` only, which silently excluded the blog index and search.
  The `page` query var used by `<!--nextpage-->` is now honoured alongside `paged`.
- **Untitled Elements are no longer skipped** — the theme version used
  `empty( get_the_title( $element_id ) )` as an existence check, so an Element
  with no title quietly opted out of its own condition.

### Migration Notes
- The meta key is unchanged (`archive_paging_visibility`), so existing Elements
  keep their settings. Activate the plugin, then delete the ACF field group and
  remove the filter from the theme's `functions.php`.
- Because the scope is wider, an Element set to *Never show on page one* whose
  GeneratePress display rule is broad will now also be hidden on unpaginated
  views, which count as page one.
