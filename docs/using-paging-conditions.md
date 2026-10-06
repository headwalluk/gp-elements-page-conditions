# Using paging conditions

This guide is for people building sites with GeneratePress and GenerateBlocks. It covers what each condition does and how to combine Elements to give page one of an archive a different design from the pages after it.

## How it fits with display rules

Every Element already has GeneratePress **Display Rules** — *Location*, *Exclude* and *Users*. The paging condition is checked **after** those, and can only narrow them:

- If the display rules say *show*, the paging condition decides whether it still shows on the current page
- If the display rules say *don't show*, the Element stays hidden whatever the paging condition says

So set up the display rules first (for example *Location: Post Category → All Categories*), then use the paging condition to pick which pages of that archive the Element appears on.

## The conditions

Find them in the **Paging Condition** box when editing an Element.

| Condition | Shows the Element… |
| --- | --- |
| **Pass through** | wherever the display rules say. This is the default and means no paging condition at all. |
| **Only show on page one** | on the first page only. Hidden from page two onwards. |
| **Never show on page one** | from page two onwards. Hidden on the first page. |
| **Only show on pages…** | only on the page numbers you list. |

### The page list

*Only show on pages…* reveals a **Page numbers** field. Separate entries with commas; each entry is one of:

| You type | Means |
| --- | --- |
| `3` | page three |
| `2-5` | pages two to five |
| `4-` | page four onwards |
| `-3` | pages one to three |

So `2-5, 8, 12-` shows the Element on pages 2, 3, 4, 5, 8, and 12 onwards.

- Spaces don't matter, and dashes pasted from a word processor (`2–5`) are accepted
- Anything the plugin can't read, such as `5-2` or `two`, is dropped when you save — check the field after saving to see what was kept
- An empty list behaves like *Pass through*, rather than hiding the Element everywhere

## Where it applies

Any page WordPress paginates:

- Category, tag and custom taxonomy archives
- Custom post type archives
- The blog index (*Posts page*)
- Search results
- Single posts split into pages with the **Page Break** block

Everywhere else — an ordinary page, a single post that isn't split — counts as **page one**. That means *Only show on page one* changes nothing on those views, and *Never show on page one* hides the Element there.

## Common layouts

### A full hero on page one, a compact one after

The original use case: page one of each category gets a large hero with an introduction written for search engines, and later pages get a slim title bar.

1. **Element A** — a Block Element with the *Page Hero* type. Display rules: *Post Category → All Categories*. Paging condition: **Only show on page one**
2. **Element B** — the compact version, with the same display rules. Paging condition: **Never show on page one**

Exactly one of them shows on every page of every category.

### A featured layout on page one only

Pair a Layout Element that sets the page-one design (no sidebar, full-width content) set to **Only show on page one** with a second Layout Element for the standard design set to **Never show on page one**.

### Something on a few specific pages

A Hook Element that drops a promotional block into `generate_before_main_content`, shown on pages 2–4 only: **Only show on pages…** with `2-4`.

## GenerateBlocks Query Loops

A Query Loop block with **Inherit query from template** turned on paginates the archive itself (`/category/news/page/2/`), and paging conditions follow it exactly.

A Query Loop running its **own** query keeps its page number in the address instead (`?query-abc123-page=2`). The plugin doesn't read that, so the archive still counts as page one however far through that loop a visitor goes. Use *Inherit query from template* when an Element needs to follow the loop's pages.

## Troubleshooting

**The Element isn't showing anywhere.** Set the paging condition to *Pass through* and check it appears. If it still doesn't, the display rules are the cause, not the paging condition.

**It shows on page one of an archive but not on a page or post I expected.** Ordinary pages count as page one. If the Element is set to *Never show on page one*, that's why — narrow the display rules instead.

**The Page numbers field is empty after saving.** Nothing in it could be read. Use digits, commas and dashes only — see [the page list](#the-page-list).

**There's no Paging Condition box.** It appears only on the Element edit screen. Check the plugin is active and the GeneratePress Elements module is switched on.
