# How it works

For developers and contributors: the request flow, stored data, testing, translations and releasing. The public extension surface is in [Hooks and filters](hooks-and-filters.md).

## The display decision

GeneratePress Premium runs every Element — Block, Layout, Hook and Hero — through one filter, `generate_element_display`, passing whether it intends to show the Element and the Element's post ID. The plugin hooks that filter at priority **20**, after GeneratePress has made its decision:

1. If the Element is already hidden, return that unchanged. The plugin never shows an Element that GeneratePress excluded
2. If the Element ID isn't numeric, return the display value unchanged. Another callback earlier on the filter may have passed something else
3. If the request is in the admin, AJAX or REST, return it unchanged — there is no page number to test
4. Otherwise read the Element's condition and compare it with the current page number

The current page number is the `paged` query var (archives, the blog index, search), falling back to `page` (single posts split with a page break), and `1` when neither is set.

| File | Role |
| --- | --- |
| `gp-elements-page-conditions.php` | Bootstrap: constants, class loading, starts `Plugin` and, for admin/cron/WP-CLI requests, `Github_Updater` |
| `constants.php` | Every meta key, condition value and identifier |
| `includes/class-plugin.php` | Singleton. Registers all hooks and owns the other instances; the missing-dependency notice |
| `includes/class-paging-condition.php` | The `generate_element_display` callback |
| `includes/class-page-list.php` | Parses and sanitises the page list. No WordPress calls |
| `includes/class-meta-box.php` | Meta registration, and the Element edit screen box: render and save |
| `includes/class-admin-hooks.php` | Admin CSS/JS, on the Element edit screen only |
| `includes/class-github-updater.php` | Update checks against GitHub Releases. Shared unchanged with other Headwall plugins |
| `admin-templates/element-meta-box.php` | Meta box markup |
| `uninstall.php` | Deletes the updater's cache, nothing else |

## Stored data

Two post meta keys on `gp_elements` posts. The plugin replaced an ACF field group with a `generate_element_display` filter in the child theme, and it reads the keys and values that setup stored, so existing Elements kept their settings without a migration. The key names and values will not change, and they don't carry the plugin's prefix for that reason.

| Meta key | Values |
| --- | --- |
| `archive_paging_visibility` | `only_page_one`, `never_page_one` or `only_pages`. **No row** means *Pass through* — saving the default deletes the key |
| `archive_paging_pages` | The sanitised page list, e.g. `2-5, 8, 12-`. Only meaningful with `only_pages`; empty means pass through |

Both keys are registered with `register_post_meta()`. The `gp_elements` post type supports custom fields, and the keys aren't `_`-prefixed, so without registration the Custom Fields box could write anything to them. The registered sanitisers reduce an unknown condition to an empty string and drop unparseable page list segments, and editing needs `edit_post` on the Element. Neither key is exposed over REST.

An unrecognised value in `archive_paging_visibility` (written before registration, or straight to the database) is still treated as *Pass through*. Treat both keys as read-only from outside the plugin.

Uninstalling deletes only the updater's two cache transients. The meta is left in place so a reinstall, or a return to the ACF setup, finds it.

## The page list format

Comma separated segments, each one of:

| Segment | Meaning |
| --- | --- |
| `3` | a single page |
| `2-5` | a closed range |
| `4-` | page four onwards |
| `-3` | up to and including page three |

Anything else, including a reversed range such as `5-2` or page `0`, is discarded on save and ignored at display time.

Typographic dashes (en dash, em dash, minus sign and the other Unicode hyphens) are folded to an ASCII hyphen before parsing. German and Polish set ranges with an en dash, so a translator, or DeepL, can render the help text's `2-5` as `2–5`; word processors and browsers autocorrect hyphens the same way. Without the fold, a segment copied from any of those would be dropped silently. The help text passes its examples in as `printf` placeholders for the same reason, so they never reach the `.po` files.

## Testing

There is no unit-test framework. Behaviour is checked against a dev site with WP-CLI, which counts as a front-end request, so the display filter runs in full:

```bash
# The display filter for Element 123 on page 2
wp eval '$condition = Headwall_Page_Conditions\Plugin::instance()->get_paging_condition(); set_query_var( "paged", 2 ); var_dump( $condition->filter_element_display( true, 123 ) );'

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

The rules for writing translatable strings are at the end of `CLAUDE.md`. The one specific to this plugin: page numbers and ranges never go inside a translatable string (see the page list format above).

The text domain is loaded on `init`, not `plugins_loaded`: since WordPress 6.7, loading one earlier triggers a `_doing_it_wrong()` notice, and nothing here needs a translated string before `init`.

## Releasing

1. Set the new version in `gp-elements-page-conditions.php` — both the `Version:` header and `HWPC_VERSION`
2. Move the `[Unreleased]` entries in `CHANGELOG.md` under the new version
3. Run `wp-translate .` and commit any changed language files with the release
4. Run `phpcs`, commit, and push `main`
5. Tag and push the tag: `git tag -a vX.Y.Z -m "vX.Y.Z" && git push origin vX.Y.Z`

Pushing the tag runs `.github/workflows/release.yml`. It stops if the header, the constant and the tag disagree, then builds the zip (the working tree minus `.distignore`) and attaches `gp-elements-page-conditions.zip` and `gp-elements-page-conditions-X.Y.Z.zip` to a new GitHub Release. Installed copies find it at their next update check.

Push the tag separately from the commit that changes the workflow: a tag pushed alongside the commit adding `release.yml` did not trigger a build for `v1.1.0`.
