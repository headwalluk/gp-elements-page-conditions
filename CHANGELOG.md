# Changelog

All notable changes to GP Elements Page Conditions will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.1.0] - 2026-10-06

### Added
- **Automatic updates from GitHub Releases** — a bundled updater checks the
  latest release of `headwalluk/gp-elements-page-conditions` and offers it
  through the normal WordPress update screens. Release lookups are cached for
  12 hours, failures for one hour, and failures are always written to the PHP
  error log. The `hwpc_updater_enabled` filter turns checks off. Sites on 1.0.0
  have no updater, so 1.1.0 must be installed by hand once.
- **Release pipeline** — pushing a `vX.Y.Z` tag builds the release zip and
  attaches it to a GitHub Release, refusing to build if the plugin header,
  `HWPC_VERSION` and the `readme.txt` stable tag disagree with the tag.

### Fixed
- **The display filter no longer fatals on an unexpected value** —
  `filter_element_display()` declared `bool` and `int` parameters, so another
  callback on `generate_element_display` returning `null` caused a `TypeError` on
  the front end. Both parameters now take `mixed`; anything that isn't a
  numeric Element ID passes through untouched. Introduced in 1.0.0.

### Changed
- `Plugin URI` now points at the GitHub repository.

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

### Internationalisation
- **Translations for eight locales** — `de_DE`, `el_GR`, `en_GB`, `es_ES`,
  `fr_FR`, `it_IT`, `nl_NL` and `pl_PL`, generated with
  [wp-translate](https://github.com/headwalluk/wp-translate-tool). All six short
  UI labels carry a translator context, since "page", "paging" and "pass
  through" are all ambiguous out of context.
- **Page list syntax examples are passed as `printf` placeholders**, so they
  never reach the translator. DeepL sets ranges with an en dash in German and
  Polish, which would have documented a format the parser rejects.

### Fixed
- **Typographic dashes in the page list are now accepted** — `Page_List` folds
  en dash, em dash, figure dash, non-breaking hyphen, horizontal bar and minus
  sign to an ASCII hyphen before parsing, and stores the canonical ASCII form.
  Without this, a range pasted from a word processor or typed from the German or
  Polish help text was silently discarded.

### Migration Notes
- The meta key is unchanged (`archive_paging_visibility`), so existing Elements
  keep their settings. Activate the plugin, then delete the ACF field group and
  remove the filter from the theme's `functions.php`.
- Because the scope is wider, an Element set to *Never show on page one* whose
  GeneratePress display rule is broad will now also be hidden on unpaginated
  views, which count as page one.
