# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

GP Elements Page Conditions is a small WordPress plugin that adds a paging condition to GeneratePress Premium Elements, so an Element can be limited to page one of a paginated view, hidden from it, or restricted to specific page numbers.

- **Namespace:** `Headwall_Page_Conditions`
- **Text Domain:** `gp-elements-page-conditions`
- **PHP:** 8.0+ (do NOT use `declare(strict_types=1)` — breaks WordPress interop). `match` and `mixed` are why the floor is 8.0; check any newer syntax against it before using it
- **WordPress:** 6.0+, GeneratePress Premium 2.0+ (Elements module)
- **No build system** — no npm, no Composer, no bundler. Assets are plain CSS/JS.

Structure and conventions follow the maintainer's reference plugin, Quick 2FA. Where this file is silent, its `CLAUDE.md` is the standard.

This plugin is published publicly on GitHub (`headwalluk/gp-elements-page-conditions`). Tracked files must contain no client names, client URLs or client data of any kind — in code, comments, docs, fixtures or commit messages.

`dev-notes/` is **private and untracked** (`.gitignore`), and excluded from the release zip by `.distignore`. It is the right home for client-identifying material. Never copy content from it into a tracked file without scrubbing it, and never reference a `dev-notes/` path from a file that ships in the release zip.

## Commands

```bash
phpcs                  # Check WordPress coding standards compliance
phpcbf                 # Auto-fix coding standards violations
phpcs includes/        # Check specific directory
```

Always run `phpcs` before committing: no errors **and no warnings**. The config is in `phpcs.xml` — WordPress standards with prefixes: `headwall_page_conditions`, `hwpc`, `Headwall_Page_Conditions`. Every `phpcs:ignore` and `phpcs:disable` names the exact sniff and ends with `-- reason`.

```bash
wp-translate . --check-instructions   # Is the block at the end of this file still current?
wp-translate . --sync-instructions    # Update it; review the diff afterwards
```

Never hand-edit inside the `wp-translate:begin`/`end` markers — the block is hash-validated.

## Testing

There is no unit-test framework and none is wanted. Behaviour is exercised against the live dev site through WP-CLI:

```bash
# The display filter, for a given page
wp eval '$condition = new Headwall_Page_Conditions\Paging_Condition(); set_query_var( "paged", 2 ); var_dump( $condition->filter_element_display( true, 123 ) );'

# The page list parser
wp eval 'var_dump( Headwall_Page_Conditions\Page_List::sanitise( "3, 5-2, 2–5, -3, 4-" ) );'
```

WP-CLI counts as a front-end page view for `is_front_end_page_view()`, so the filter runs in full there. Reset any meta or transients you touch afterwards, and test the **defensive** path as well as the happy one — passing `null` or a non-numeric ID to `filter_element_display()` is what proves it tolerates a sloppy earlier callback.

## Architecture

### Entry Point & Bootstrap

`gp-elements-page-conditions.php` defines globals (`HWPC_FILE`, `HWPC_BASENAME`, `HWPC_DIR`, `HWPC_URL`, `HWPC_VERSION`, `HWPC_ADMIN_TEMPLATES_DIR`, `HWPC_ASSETS_URL`), requires the class files, and creates the global `$hwpc_plugin` instance which calls `Plugin::run()`. On admin, cron and WP-CLI requests only, it also loads and instantiates `Github_Updater`.

### Core Classes

- **`Plugin`** (`includes/class-plugin.php`) — Orchestrator. All hooks registered in `run()`. Owns the `Meta_Box`, `Paging_Condition` and `Admin_Hooks` instances, and renders the missing-dependency admin notice.
- **`Meta_Box`** (`includes/class-meta-box.php`) — Registers, renders and saves the Element edit screen meta box. `render()` includes the template; `save()` handles nonce, autosave and capability checks.
- **`Paging_Condition`** (`includes/class-paging-condition.php`) — The display-time decision. `filter_element_display()` is the `generate_element_display` handler; `get_current_page_number()` reads `paged` then `page`.
- **`Page_List`** (`includes/class-page-list.php`) — Parses the comma separated page list. `sanitise()` on save, `matches()` at display time. Pure, no WordPress calls, so it is the easy thing to test.
- **`Admin_Hooks`** (`includes/class-admin-hooks.php`) — Enqueues the admin CSS/JS, and only on the Element edit screen.
- **`Github_Updater`** (`includes/class-github-updater.php`) — Checks the latest GitHub Release and feeds it into the WordPress update transient. Registers its own hooks in its constructor, the one exception to the `Plugin::run()` rule, matching Quick 2FA. Holds the `log()` / `log_error()` split described under **Logging**.

### GeneratePress Integration

GeneratePress Premium applies **one** filter, `generate_element_display`, from all four Element classes — `class-block.php`, `class-layout.php`, `class-hooks.php` and `class-hero.php`. Hooking that single filter covers Block, Layout, Hook and Hero Elements. Priority `20` (`ELEMENT_DISPLAY_PRIORITY`) so we run after GeneratePress has decided.

The filter must only ever take an Element **away**. If `$is_displayed` arrives falsy, return it untouched — never flip an Element back on that GeneratePress excluded.

### Constants

All magic strings, meta keys, condition values and admin identifiers are in `constants.php` under the `Headwall_Page_Conditions` namespace. Convention: `META_` for post meta keys, `CONDITION_` for the stored condition values, `UPDATER_` for the GitHub updater.

The meta keys (`archive_paging_visibility`, `archive_paging_pages`) deliberately match the ACF implementation this plugin replaces — **do not rename them**, existing sites depend on them. See **Public Contracts**.

### Internationalisation

`languages/` holds the `.pot` and the per-locale `.po`/`.mo` files, regenerated by `wp-translate` (see the conventions block at the end of this file). `Plugin::load_textdomain()` is hooked on **`init`**, not `plugins_loaded` — since WordPress 6.7 loading a text domain earlier triggers a `_doing_it_wrong()` notice, and nothing here needs a translated string before `init`.

Current locales: `de_DE`, `el_GR`, `en_GB`, `es_ES`, `fr_FR`, `it_IT`, `nl_NL`, `pl_PL`.

Note when testing: `switch_to_locale()` silently no-ops for a locale whose core language pack is not installed, so a label appearing in English proves nothing on its own. Check `wp language core list --status=installed` first, or read the `.mo` directly.

### Admin UI

The meta box template lives in `admin-templates/element-meta-box.php` and is `include`d from `Meta_Box::render()`, receiving `$current_condition` and `$current_pages` from the enclosing scope.

## Public Contracts

Code and data outside the plugin depend on these. Treat each as a contract:

| Contract | Examples | Breaks when |
|----------|----------|-------------|
| Stored meta keys and values | `archive_paging_visibility`, `archive_paging_pages`, `only_page_one` | a `META_` or `CONDITION_` constant's **value** changes. Elements on existing sites silently lose their condition |
| Filters | `hwpc_updater_enabled` | renamed or removed |
| Display filter priority | `generate_element_display` at `20` | lowered below GeneratePress's own decision, or the take-away-only rule is broken |
| Release asset name | `gp-elements-page-conditions.zip` on each GitHub Release | renamed in `release.yml`. Every installed copy stops finding updates |

Stored data has no migration path, so changing a stored key or value is a breaking change: stop and ask. Deprecate rather than rename a filter (`apply_filters_deprecated()`), and record any change here in `CHANGELOG.md`.

## Key Conventions

- Register all hooks in `Plugin::run()`, implement in respective classes
- Use constants from `constants.php` — never hardcode meta keys or condition values
- Templates must use `printf()`/`echo` — no inline HTML with PHP snippets
- Templates: variables the template *defines* (loop variables) must carry the `hwpc_` prefix or PHPCS flags them as unprefixed globals. Variables passed in from the enclosing scope do not.
- Security: nonce verification, `edit_post` capability check, input sanitisation, output escaping on the meta box form
- **Callbacks on hooks the plugin doesn't own take `mixed`.** Any callback earlier in the chain can hand over the wrong type, and a typed parameter turns that into a `TypeError` on a page the plugin doesn't control. Check each value before use, and pass a value you can't use through unchanged. `Paging_Condition::filter_element_display()` is the pattern
- **Literal syntax must never sit inside a translatable string.** Page numbers, ranges and format examples go in as `printf` placeholders with a `translators:` comment. DeepL sets ranges with an en dash (`2–5`) in German and Polish, which would document a format `Page_List` rejects. For the same reason `Page_List::fold_dashes()` accepts every dash variant a user might paste
- Store the default condition as *no meta row at all* — `Meta_Box::store_meta()` deletes on an empty value, so an Element that has never been touched looks the same as one that has been cleared
- **CSS uses logical properties** for anything with a left or right (`margin-inline-start`, `text-align: start`), so the meta box mirrors correctly in right-to-left locales. Top and bottom stay physical
- **Plain JavaScript**, no jQuery dependency

### Control flow

- Single entry, single exit, where reasonable
- **Never `return` from inside a loop.** Set the result, `break`, and return at the end
- Early `return` from a *function* is fine when short-circuiting for performance or security — a guard clause at the top. `Paging_Condition::filter_element_display()`, `Meta_Box::save()` and `Github_Updater::plugin_info()` are the examples here
- **An `if` with one or more `elseif` branches ends in a plain `else`**, never an `elseif`, so every case is handled on purpose. A branch that does nothing is still written out, holding only a short comment — `Page_List::parse_segment()` is the pattern. `phpcs.xml` excludes the `if`/`elseif`/`else` codes of `Generic.CodeAnalysis.EmptyStatement` so these pass; empty `catch`, loop and `switch` bodies are still errors

### Logging

Two methods, deliberately split — see `Github_Updater::log()` / `log_error()`:

- `log_error()` — genuine failures (HTTP errors, malformed responses, missing assets). Logs **unconditionally**
- `log()` — routine flow tracing (cache hits, version comparisons). Logs only when `WP_DEBUG` is on

An error that only appears under `WP_DEBUG` is a silent failure in production. Never leave a `catch` that records nothing. `error_log()` with a `phpcs:ignore` is correct — do not add a logging library.

### Naming

- No single-character or cryptic identifiers, in any language, including throwaway loop variables

## Commit Messages

```
type: brief description

- Detail 1
- Detail 2
```

Types: `feat:` `fix:` `refactor:` `chore:` `docs:` `style:` `test:`

## Release Workflow

1. Update the version in `gp-elements-page-conditions.php` — **both** the `Version:` header and the `HWPC_VERSION` constant
2. Update `CHANGELOG.md`: move the `[Unreleased]` entries under the new version
3. Update the `readme.txt` stable tag and its `== Changelog ==`
4. Run `phpcs`
5. Tag `vX.Y.Z` and push the tag

Pushing the tag runs `.github/workflows/release.yml`, which refuses to build unless the header `Version:`, `HWPC_VERSION` and the `readme.txt` stable tag all match the tag. It then builds the zip from the working tree minus `.distignore`, and attaches `gp-elements-page-conditions.zip` and `gp-elements-page-conditions-X.Y.Z.zip` to a new GitHub Release — which is what `Github_Updater` reads.

**The version must always correspond to a real GitHub Release.** A version ahead of the latest Release makes the updater see nothing new; a header and constant that disagree produce a version-drift error on every check.

## Reference Files

- `README.md` — what the plugin does, conditions, installation and updates, migration from the ACF version
- `CHANGELOG.md` — per-version release notes
- `readme.txt` — WordPress plugin header readme
- `.distignore` — what is left out of the release zip

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
