# Changelog

All notable changes to GP Elements Page Conditions will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

---

## [2.1.0] - 2026-11-23

### Added
- **URL path condition.** A new *URL path starts with* field limits an Element to
  pages whose address starts with one of a list of paths, such as `/docs/`, so a
  whole section of a site can get its own sidebar, hero or hook without listing
  each page in the display rules. It matches whole path segments (`/docs/` covers
  `/docs/installation/` but not `/docsearch/`), ignores case and the query
  string, accepts pasted full URLs, and, like the paging condition, only ever
  hides an Element GeneratePress would show. Stored in the new `_hwpc_path_prefixes`
  meta key. The box explains that the display rules must already include the
  section's pages, and warns when the site uses plain permalinks, which have no
  path to match.

### Changed
- The meta box is now called **Page Conditions**, with *Paging* and *URL path*
  sections. Its ID is unchanged, so hidden or collapsed boxes stay that way.
- `docs/using-paging-conditions.md` is now `docs/using-page-conditions.md`, and
  covers both conditions, with a recipe for a section-wide sidebar.

### Fixed
- **Background auto-updates could install a release that needs a newer PHP than
  the server has** (since 1.1.0). The updater now reads `Requires PHP` and
  `Requires at least` from the released plugin header at the release tag and
  passes them to WordPress, which marks an incompatible update as such and
  doesn't auto-install it. *View details* shows the new release's requirements.
  A release whose header can't be read, or whose `Version:` disagrees with its
  tag, is rejected and logged. Taken unchanged from the shared updater.

---

## [2.0.0] - 2026-11-22

**Breaking: requires WordPress 6.9 and PHP 8.2** (previously 6.0 and 8.0). Sites
below either stay on 1.1.1; WordPress refuses the update until the site or
server is upgraded.

### Added
- **The paging meta keys are registered** with `register_post_meta()`, so a write
  from the Custom Fields box goes through the same sanitisers as the Paging
  Condition box: an unknown condition is blanked, an unparseable page list
  segment dropped, and editing needs `edit_post` on the Element. Neither key is
  exposed over REST.
- **`uninstall.php`** — deletes the updater's cached release lookup. Element
  meta is kept, as before.

### Changed
- **Minimum WordPress raised to 6.9 and PHP to 8.2.**
- **GitHub updater replaced with the shared Headwall updater.** The
  `hwpc_updater_enabled` filter is unchanged. Its cache transients are now
  named `gp_elements_page_conditions_github_release` and
  `gp_elements_page_conditions_github_failed`; the old `hwpc_github_*` ones
  expire on their own.
- The meta box save also skips autosaves and revisions detected by
  `wp_is_post_autosave()` and `wp_is_post_revision()`, and the save and asset
  callbacks tolerate a non-numeric post ID or a missing screen.
- `Plugin URI` now points at the plugin's page on headwall-hosting.com.
- **Documentation moved into `docs/`** — `getting-started.md` for site owners,
  `using-paging-conditions.md` for site builders, and `developers/` for
  developers (`how-it-works.md`, `hooks-and-filters.md`), indexed by
  `docs/README.md`. `README.md` is now a short introduction that links to them.
- The release workflow checks the plugin header and `HWPC_VERSION` only.

### Removed
- **`readme.txt`** — the plugin is distributed through GitHub Releases, not
  wordpress.org, so the file served no purpose and was a third place to keep the
  version in step.

---

## [1.1.1] - 2026-10-06

Re-release of 1.1.0 with no code changes. The `v1.1.0` tag did not trigger the
release workflow, so 1.1.0 has no GitHub Release; sites on 1.0.0 install 1.1.1
by hand once.

---

## [1.1.0] - 2026-10-06

### Added
- **Automatic updates from GitHub Releases** — a bundled updater checks the
  latest release of `headwalluk/gp-elements-page-conditions` and offers it
  through the normal WordPress update screens. Release lookups are cached for
  12 hours, failures for one hour, and failures are always written to the PHP
  error log. The `hwpc_updater_enabled` filter turns checks off. Sites on 1.0.0
  have no updater, so 1.1.1 must be installed by hand once.
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
