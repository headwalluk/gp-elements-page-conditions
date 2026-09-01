# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

GP Elements Page Conditions is a small WordPress plugin that adds a paging condition to GeneratePress Premium Elements, so an Element can be limited to page one of a paginated view, hidden from it, or restricted to specific page numbers.

- **Namespace:** `Headwall_Page_Conditions`
- **Text Domain:** `gp-elements-page-conditions`
- **PHP:** 8.0+ (do NOT use `declare(strict_types=1)` — breaks WordPress interop)
- **WordPress:** 6.0+, GeneratePress Premium 2.0+ (Elements module)
- **No build system** — no npm, no Composer, no bundler. Assets are plain CSS/JS.

## Commands

```bash
phpcs                  # Check WordPress coding standards compliance
phpcbf                 # Auto-fix coding standards violations
phpcs includes/        # Check specific directory
```

Always run `phpcs` before committing. The config is in `phpcs.xml` — WordPress standards with prefixes: `headwall_page_conditions`, `hwpc`, `Headwall_Page_Conditions`.

## Architecture

### Entry Point & Bootstrap

`gp-elements-page-conditions.php` defines globals (`HWPC_DIR`, `HWPC_URL`, `HWPC_VERSION`, `HWPC_ADMIN_TEMPLATES_DIR`, `HWPC_ASSETS_URL`), requires the class files, and creates the global `$hwpc_plugin` instance which calls `Plugin::run()`.

### Core Classes

- **`Plugin`** (`includes/class-plugin.php`) — Orchestrator. All hooks registered in `run()`. Owns the `Meta_Box`, `Paging_Condition` and `Admin_Hooks` instances, and renders the missing-dependency admin notice.
- **`Meta_Box`** (`includes/class-meta-box.php`) — Registers, renders and saves the Element edit screen meta box. `render()` includes the template; `save()` handles nonce, autosave and capability checks.
- **`Paging_Condition`** (`includes/class-paging-condition.php`) — The display-time decision. `filter_element_display()` is the `generate_element_display` handler; `get_current_page_number()` reads `paged` then `page`.
- **`Page_List`** (`includes/class-page-list.php`) — Parses the comma separated page list. `sanitise()` on save, `matches()` at display time. Pure, no WordPress calls, so it is the easy thing to test.
- **`Admin_Hooks`** (`includes/class-admin-hooks.php`) — Enqueues the admin CSS/JS, and only on the Element edit screen.

### GeneratePress Integration

GeneratePress Premium applies **one** filter, `generate_element_display`, from all four Element classes — `class-block.php`, `class-layout.php`, `class-hooks.php` and `class-hero.php`. Hooking that single filter covers Block, Layout, Hook and Hero Elements. Priority `20` (`ELEMENT_DISPLAY_PRIORITY`) so we run after GeneratePress has decided.

The filter must only ever take an Element **away**. If `$is_displayed` arrives false, return it untouched — never flip an Element back on that GeneratePress excluded.

### Constants

All magic strings, meta keys, condition values and admin identifiers are in `constants.php` under the `Headwall_Page_Conditions` namespace. Convention: `META_` for post meta keys, `CONDITION_` for the stored condition values.

The meta keys (`archive_paging_visibility`, `archive_paging_pages`) deliberately match the ACF implementation this plugin replaces — **do not rename them**, existing sites depend on them.

### Admin UI

The meta box template lives in `admin-templates/element-meta-box.php` and is `include`d from `Meta_Box::render()`, receiving `$current_condition` and `$current_pages` from the enclosing scope.

## Key Conventions

- Register all hooks in `Plugin::run()`, implement in respective classes
- Use constants from `constants.php` — never hardcode meta keys or condition values
- Templates must use `printf()`/`echo` — no inline HTML with PHP snippets
- Templates: variables the template *defines* (loop variables) must carry the `hwpc_` prefix or PHPCS flags them as unprefixed globals. Variables passed in from the enclosing scope do not.
- Security: nonce verification, `edit_post` capability check, input sanitisation, output escaping on the meta box form
- Store the default condition as *no meta row at all* — `Meta_Box::store_meta()` deletes on an empty value, so an Element that has never been touched looks the same as one that has been cleared

### Control flow

- Single entry, single exit, where reasonable
- **Never `return` from inside a loop.** Set the result, `break`, and return at the end
- Early `return` from a *function* is fine when short-circuiting for performance or security — a guard clause at the top. `Paging_Condition::filter_element_display()` and `Meta_Box::save()` are the two examples here
- No empty `if`/`else` branches — PHPCS rejects them (`Generic.CodeAnalysis.EmptyStatement`). Where a comment-only branch is tempting, a `match` expression usually reads better

### Naming

- No single-character or cryptic identifiers, in any language, including throwaway loop variables

## Commit Messages

```
type: brief description

- Detail 1
- Detail 2
```

Types: `feat:` `fix:` `refactor:` `chore:` `docs:` `style:` `test:`

## Reference Files

- `README.md` — what the plugin does, conditions, migration from the ACF version
- `CHANGELOG.md` — per-version release notes
- `readme.txt` — WordPress plugin header readme
