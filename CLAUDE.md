# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Repository: public

Follows the **headwall-wp-plugin** skill, which holds the shared conventions: PHP style,
templates, logging, comments, public-contract rules, testing, release and the GitHub updater.
Load it before working on this plugin. This file holds only what is specific to
GP Elements Page Conditions.

## Project Overview

A small add-on that gives GeneratePress Premium Elements two extra conditions, both in the
plugin's own **Page Conditions** meta box: **paging** (page one only, never page one, or listed
page numbers) and **URL path** (only where the request path starts with a listed prefix, such as
`/docs/`). Both only ever hide an Element GeneratePress would show.

- **Namespace:** `Headwall_Page_Conditions` · **Text domain:** `gp-elements-page-conditions` · **Prefixes:** `hwpc_` (filters), `HWPC_` (global constants). The updater names its transients from the slug (`gp_elements_page_conditions_github_*`)
- **PHP / WordPress floor:** 8.2 / 6.9, the skill defaults, raised from 8.0 / 6.0 for 2.0.0
- **Depends on:** GeneratePress Premium 2.0+ with the Elements module. Without it the plugin is inert and shows an admin notice
- **No settings,** so there is no `Settings` class, activation hook or first-run check

### Replacing a legacy implementation

The plugin replaced an ACF field group plus a `generate_element_display` filter in a child
theme, and reads the data that setup stored:

- The meta keys `archive_paging_visibility` and `archive_paging_pages`, and the condition values,
  are the legacy strings. They carry no plugin prefix and never will; changing one silently
  drops every Element's condition (stop and ask)
- Nothing writes defaults. The default condition is stored as **no meta row at all**:
  `Meta_Box::store_meta()` deletes on an empty value, and the registered sanitiser reduces the
  default to `''`
- `uninstall.php` deletes only the updater's cache transients. The meta stays, so a reinstall or a
  rollback to the ACF setup finds it
- Keys added since the ACF replacement follow the prefix rule: `META_PREFIX` (`_hwpc_`), e.g.
  `META_PATH_PREFIXES`. The underscore keeps them out of the Custom Fields box, so they aren't
  registered with `register_post_meta()`; only the meta box writes them

## Architecture

`gp-elements-page-conditions.php` defines the `HWPC_*` path constants, requires the class files,
and starts `Plugin::instance()->run()` (also assigned to `global $hwpc_plugin`, kept for anything
that already reads it). On admin, cron and WP-CLI requests it also creates `Github_Updater`.

- **`Plugin`** — singleton; wires every hook in `run()`; lazy getters `get_meta_box()`,
  `get_paging_condition()`, `get_path_condition()`, `get_admin_hooks()`; the missing-dependency notice
- **`Meta_Box`** — `register_meta()` (on `init`), and the Element edit screen box: `register()`,
  `render()`, `save()`
- **`Paging_Condition`** — the display decision. `filter_element_display()` is the
  `generate_element_display` callback; `get_current_page_number()` reads `paged`, then `page`
- **`Page_List`** — parses the page list: `sanitise()` on save, `matches()` at display time.
  Pure, no WordPress calls. Static only and never instantiated
- **`Path_Condition`** — the URL path decision, a second `generate_element_display` callback;
  `get_current_path()` reads `$wp->request`
- **`Path_List`** — normalises and matches URL path prefixes, as `Page_List` does for pages.
  No WordPress state (it calls `wp_parse_url()`); static only
- **`Admin_Hooks`** — enqueues the admin CSS/JS, on the Element edit screen only
- **`Github_Updater`** — the skill's shared updater, unchanged apart from `namespace`/`@package`

The meta box template is `admin-templates/element-meta-box.php`, included from
`Meta_Box::render()`; its docblock lists the variables it reads. The meta box ID stays
`hwpc-paging-condition` (`META_BOX_ID`), though its title is now "Page Conditions": WordPress keys
each user's hidden and collapsed boxes on the ID.

### GeneratePress integration

GeneratePress Premium applies **one** filter, `generate_element_display`, from all four Element
classes (Block, Layout, Hook, Hero). The plugin hooks it at priority `20`
(`ELEMENT_DISPLAY_PRIORITY`), after GeneratePress has decided.

The filter must only ever take an Element **away**. If `$is_displayed` arrives falsy, return it
untouched; never show an Element GeneratePress excluded. `filter_element_display()` is the
plugin's `mixed`-callback pattern.

GeneratePress's own Location list has no filters (`get_conditions()`, `show_data()`), and its
Display Rules save strips slashes from a rule's object, so new conditions go in the plugin's meta
box, as restrictions, never into GeneratePress's box. See `docs/developers/how-it-works.md`.

### Translations

Locales: `de_DE`, `el_GR`, `en_GB`, `es_ES`, `fr_FR`, `it_IT`, `nl_NL`, `pl_PL`.

Page numbers, ranges and format examples never go inside a translatable string: DeepL sets
ranges with an en dash (`2–5`) in German and Polish, documenting a format `Page_List` would
otherwise reject. Pass them as `printf` placeholders. `Page_List::fold_dashes()` accepts every
dash variant anyway.

When testing a locale, `switch_to_locale()` silently no-ops if the core language pack isn't
installed, so English output proves nothing. Check
`wp language core list --status=installed`, or read the `.mo`.

## Testing

Harnesses in `dev-notes/testing/` (untracked):

- `test-page-list.php` — parser cases, dash folding
- `test-paging-condition.php` — every condition, the defensive path, the registered sanitiser.
  Creates and deletes a draft `gp_elements` post
- `test-path-list.php` — URL path normalising and matching
- `test-path-condition.php` — the path filter, combined with paging, and `Meta_Box::save()` with a
  real nonce. Sets `$GLOBALS['wp']->request`, which WP-CLI leaves empty, and restores it
- `test-github-updater.php` — ported from Quick 2FA; one live GitHub API call

Run each with `wp eval-file wp-content/plugins/gp-elements-page-conditions/dev-notes/testing/<file>.php`.
WP-CLI counts as a front-end page view for `is_front_end_page_view()`, so the filter runs in full.

## Public Contracts

On top of the skill's general table:

| Contract | Examples | Breaks when |
|----------|----------|-------------|
| Legacy meta keys and values | `archive_paging_visibility`, `archive_paging_pages`, `only_page_one` | a `META_` or `CONDITION_` constant's **value** changes |
| URL path meta key and format | `_hwpc_path_prefixes` holding `/docs/, /guides/` | the key or the normalised format changes |
| Updater filter | `hwpc_updater_enabled` | renamed or removed |
| Display filter priority | `generate_element_display` at `20` | lowered below GeneratePress's own decision, or the take-away-only rule is broken |
| Script and style handle | `gp-elements-page-conditions-admin` | renamed |
| Release asset name | `gp-elements-page-conditions.zip` | renamed in `release.yml` |

## Documentation

- `docs/getting-started.md` — site owners: requirements, install, updates, uninstalling, moving from the ACF version
- `docs/using-page-conditions.md` — site builders: both conditions, their formats, layouts, troubleshooting
- `docs/developers/how-it-works.md` — developers: the display decision, stored data, both list formats, testing, releasing
- `docs/developers/hooks-and-filters.md` — the public extension surface

A change to behaviour, stored data, a hook or the release process updates the matching doc in
the same commit.

<!-- wp-translate:begin v=1.2.0 hash=2ec561ecf0308d85e8424ce1a54e396c6d74d14223621c8a9e5ac21f5d66efa2 -->
## Translating this plugin (wp-translate conventions)

This plugin's `.po`/`.mo` files are generated from source by
[wp-translate](https://github.com/headwalluk/wp-translate-tool), which
machine-translates strings with DeepL. Machine translation is only as good as
the strings you give it — follow these conventions when adding or editing
user-facing text.

### 1. Disambiguate short or ambiguous strings with `_x()`

DeepL handles full sentences well but guesses badly on short, context-free
labels. Give it context with `_x()` (or `esc_html_x()`, `_ex()`):

```php
// Ambiguous out of context — DeepL may read "Sent" as "late", "Folder" as "leaflet"
__( 'Sent', 'gp-elements-page-conditions' );

// Disambiguated — the context is passed to the translator and to DeepL
_x( 'Sent', 'email delivery status', 'gp-elements-page-conditions' );
_x( 'Folder', 'IMAP mailbox', 'gp-elements-page-conditions' );
_x( 'Open', 'verb; button label', 'gp-elements-page-conditions' );
```

The context (2nd argument) is never shown to users. Use it whenever a string is a
single word, a short label, or has more than one plausible meaning.

### 2. Use placeholders, never concatenation

Build dynamic text with `printf`/`sprintf` so the whole sentence translates as a
unit, and add a `translators:` comment to explain each placeholder:

```php
/* translators: %s is the user's display name */
printf( esc_html__( 'Welcome back, %s', 'gp-elements-page-conditions' ), $name );
```

Never split a sentence across multiple translation calls — word order differs
between languages.

### 3. Use `_n()` for anything that can be counted

Never build a count-dependent sentence by hand, and never settle for a single
form that reads correctly only for one number. Languages differ in how many
plural forms they have — English and German have two, French treats 0 as
singular, Polish and Russian have three, Japanese has one, Arabic has six — and
`_n()` is the only way to express that.

```php
// Wrong — "1 reviews", and untranslatable into languages with other forms
printf( esc_html__( '%d reviews', 'gp-elements-page-conditions' ), $count );

// Right — wp-translate fills every form the target locale needs
printf(
    esc_html( _n( '%d review', '%d reviews', $count, 'gp-elements-page-conditions' ) ),
    $count
);
```

Keep the placeholder in **both** forms, even when the singular reads fine
without it (`'%d review'`, not `'One review'`) — some locales use the singular
slot for other numbers too.

For a short or ambiguous countable noun, use `_nx()` — the plural equivalent of
`_x()` — so the context reaches DeepL:

```php
// "Review" alone is ambiguous: critique? opinion? inspection?
_nx( '%d review', '%d reviews', $count, 'customer feedback on a company', 'gp-elements-page-conditions' );
```

**Locales needing more than two forms will have their extra slots left empty for
a human translator.** DeepL supplies a singular and a plural; nobody can invent
Polish's third form from those, and wp-translate deliberately leaves it blank
rather than filling it with a plausible guess. Expect to see empty
`msgstr[2]` entries in `pl_PL` — that is correct behaviour, not a failure.

### 4. Acronyms and technical tokens

wp-translate keeps common acronyms (`TLS`, `API`, `SMTP`, `URL`, `ID`, `UTC`, …)
verbatim automatically. If you introduce an unusual acronym or product name that
must not be translated, keep it as its own standalone string so it is recognised,
or ask the maintainer to add it to the tool's acronym list.

### 5. Don't translate dates — let WordPress localise them

Never add month or day-of-week names (full or abbreviated) as translatable
strings. DeepL frequently mistranslates short forms like `Mon`, `Tue`, `Jan`,
`Feb` even with context hints. WordPress already ships locale-aware names — use
`$wp_locale`:

```php
global $wp_locale;
$wp_locale->get_month( $month_number );        // "January" (1-based)
$wp_locale->get_month_abbrev( $month_name );   // "Jan"
$wp_locale->get_weekday( $weekday_number );     // "Monday" (0 = Sunday)
$wp_locale->get_weekday_abbrev( $weekday_name ); // "Mon"
```

For formatted dates, prefer `wp_date()` / `date_i18n()`, which localise month and
day names automatically.

### 6. English source dialect

Write source strings in standard English. wp-translate handles English targets
locally (no DeepL): `en`/`en_US` use the source as-is, and `en_GB`/`en_AU`/… get
American spellings converted to British automatically (`color` → `colour`).

### Running wp-translate

After changing strings, regenerate translations:

```bash
wp-translate /path/to/this-plugin              # auto-detect locales from languages/
wp-translate /path/to/this-plugin en_GB,fr_FR  # explicit locales
wp-translate /path/to/this-plugin --dry-run    # preview; no API calls, no writes
```

Requires WP-CLI (`wp`) and a DeepL API key at `~/.config/deepl.env`. The tool
regenerates the `.pot` from source, translates new/changed strings for each
locale, and compiles the `.mo` files.
<!-- wp-translate:end -->
