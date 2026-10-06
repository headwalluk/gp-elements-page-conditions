# Developer guide

For developers integrating with the plugin and for contributors. Coding conventions — control flow, templates, naming, logging — are in [`CLAUDE.md`](../CLAUDE.md), which is the standard for this codebase.

## How it works

GeneratePress Premium runs every Element — Block, Layout, Hook and Hero — through one filter, `generate_element_display`, passing whether it intends to show the Element and the Element's post ID. The plugin hooks that filter at priority **20**, after GeneratePress has made its decision:

1. If the Element is already hidden, return that unchanged. The plugin never shows an Element that GeneratePress excluded
2. If the request is in the admin, AJAX or REST, return it unchanged — there is no page number to test
3. Otherwise read the Element's condition and compare it with the current page number

The current page number is the `paged` query var (archives, the blog index, search), falling back to `page` (single posts split with a page break), and `1` when neither is set.

| File | Role |
| --- | --- |
| `gp-elements-page-conditions.php` | Bootstrap: constants, class loading, starts `Plugin` and, for admin/cron/WP-CLI requests, `Github_Updater` |
| `constants.php` | Every meta key, condition value and identifier |
| `includes/class-plugin.php` | Registers all hooks; the missing-dependency notice |
| `includes/class-paging-condition.php` | The `generate_element_display` callback |
| `includes/class-page-list.php` | Parses and sanitises the page list. No WordPress calls |
| `includes/class-meta-box.php` | The Element edit screen box: render and save |
| `includes/class-admin-hooks.php` | Admin CSS/JS, on the Element edit screen only |
| `includes/class-github-updater.php` | Update checks against GitHub Releases |
| `admin-templates/element-meta-box.php` | Meta box markup |

## Stored data

Two post meta keys on `gp_elements` posts. Their names match the ACF implementation the plugin replaced and will not change.

| Meta key | Values |
| --- | --- |
| `archive_paging_visibility` | `only_page_one`, `never_page_one` or `only_pages`. **No row** means *Pass through* — saving the default deletes the key |
| `archive_paging_pages` | The sanitised page list, e.g. `2-5, 8, 12-`. Only meaningful with `only_pages`; empty means pass through |

An unrecognised value in `archive_paging_visibility` is treated as *Pass through*. Treat both keys as read-only from outside the plugin; write them through the Element edit screen.

## Hooks

### `hwpc_updater_enabled`

Filter. Return `false` to stop the plugin checking GitHub for updates.

```php
add_filter( 'hwpc_updater_enabled', '__return_false' );
```

### Running after the paging condition

To make your own decision with the paging condition already applied, hook `generate_element_display` at a priority above 20:

```php
add_filter( 'generate_element_display', function ( $display, $element_id ) {
	// $display already reflects GeneratePress's rules and the paging condition.
	return $display;
}, 30, 2 );
```

Keep to the same rule the plugin follows — only take an Element away — and don't type the parameters: another callback may pass something other than a boolean.

## Testing

There is no unit-test framework. Behaviour is checked against a dev site with WP-CLI, which counts as a front-end request, so the display filter runs in full:

```bash
# The display filter for Element 123 on page 2
wp eval '$condition = new Headwall_Page_Conditions\Paging_Condition(); set_query_var( "paged", 2 ); var_dump( $condition->filter_element_display( true, 123 ) );'

# The page list parser
wp eval 'var_dump( Headwall_Page_Conditions\Page_List::sanitise( "3, 5-2, 2–5, -3, 4-" ) );'
```

Reset any meta or transients you change. Test the defensive paths too — `null` or a non-numeric ID passed to `filter_element_display()` must come back unchanged.

## Coding standards

```bash
phpcs     # WordPress Coding Standards, configured in phpcs.xml
phpcbf    # Auto-fix what it can
```

Commits must pass `phpcs` with no errors and no warnings. Commit messages use `type: description` — `feat:`, `fix:`, `refactor:`, `chore:`, `docs:`, `style:`, `test:`.

## Translations

`languages/` holds the `.pot` and `.po`/`.mo` files for `de_DE`, `el_GR`, `en_GB`, `es_ES`, `fr_FR`, `it_IT`, `nl_NL` and `pl_PL`, generated with [wp-translate](https://github.com/headwalluk/wp-translate-tool). After changing any user-facing string:

```bash
wp-translate .            # regenerate the .pot, translate new strings, compile the .mo files
wp-translate . --dry-run  # preview without calling DeepL or writing anything
```

The rules for writing translatable strings are at the end of `CLAUDE.md`. The one specific to this plugin: page numbers and ranges never go inside a translatable string — pass them as `printf` placeholders, or machine translation turns `2-5` into `2–5`.

## Releasing

1. Set the new version in `gp-elements-page-conditions.php` — both the `Version:` header and `HWPC_VERSION`
2. Move the `[Unreleased]` entries in `CHANGELOG.md` under the new version
3. Run `phpcs`, commit, and push `main`
4. Tag and push the tag: `git tag vX.Y.Z && git push origin vX.Y.Z`

Pushing the tag runs `.github/workflows/release.yml`. It stops if the header, the constant and the tag disagree, then builds the zip (the working tree minus `.distignore`) and attaches `gp-elements-page-conditions.zip` and `gp-elements-page-conditions-X.Y.Z.zip` to a new GitHub Release. Installed copies find it at their next update check.

Push the tag separately from the commit that changes the workflow: a tag pushed alongside the commit adding `release.yml` did not trigger a build for `v1.1.0`.
