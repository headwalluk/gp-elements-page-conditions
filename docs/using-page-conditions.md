# Using page conditions

This guide is for people building sites with GeneratePress and GenerateBlocks. It covers the two conditions the plugin adds to every Element — **paging** and **URL path** — and how to combine Elements with them: a different design for page one of an archive, or a sidebar for one section of the site.

## How it fits with display rules

Every Element already has GeneratePress **Display Rules** — *Location*, *Exclude* and *Users*. The plugin's conditions are checked **after** those, and can only narrow them:

- If the display rules say *show*, each condition decides whether it still shows on the current page
- If the display rules say *don't show*, the Element stays hidden whatever the conditions say

So set up the display rules first, broadly (for example *Location: Post Category Archive → All Categories*, or *Location: Page → All Pages*), then use a condition to narrow them down.

Both conditions are in the **Page Conditions** box when editing an Element. When both are set, the Element shows only where both allow it.

## Paging

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

### Where paging applies

Any page WordPress paginates:

- Category, tag and custom taxonomy archives
- Custom post type archives
- The blog index (*Posts page*)
- Search results
- Single posts split into pages with the **Page Break** block

Everywhere else — an ordinary page, a single post that isn't split — counts as **page one**. That means *Only show on page one* changes nothing on those views, and *Never show on page one* hides the Element there.

## URL path

**URL path starts with** limits the Element to pages whose address starts with a path you give. It's the way to target a whole section of a site, such as every page under `/docs/`, without listing each page in the display rules and remembering to add every new one.

| You type | Shows the Element on… | But not on… |
| --- | --- | --- |
| `/docs/` | `/docs/`, `/docs/installation/`, `/docs/api/hooks/` | `/docsearch/`, `/about/` |
| `/docs/api/` | `/docs/api/` and everything below it | `/docs/`, `/docs/installation/` |
| `/docs/, /guides/` | anything under either | |

- Paths are relative to the site address, so on `https://example.com/` type `/docs/`, not the full address. Pasting a full address works too; it is trimmed to its path when you save
- A path matches whole parts of the address, so `/docs` and `/docs/` mean the same and neither covers `/docs-old/`
- Capitals don't matter, and anything after a `?` or `#` is ignored, on the page and in what you type
- Separate several paths with commas or spaces. Anything that can't be part of a path is dropped when you save
- An empty field shows the Element on every path, as if the condition weren't there
- The display rules must already include every page the path covers. A Location of just the `/docs/` page, with a URL path of `/docs/`, shows on `/docs/` alone: GeneratePress has ruled the child pages out before the URL path is checked. For a section, use *Location: Page → All Pages*
- The condition needs pretty permalinks (*Settings → Permalinks*, anything but *Plain*). With plain permalinks every address is `/?page_id=…`, there's no path to match, and the box warns you

## Common layouts

### A sidebar for one section of the site

The site has no sidebar, except under `/docs/`, where every page should get one, including pages added later.

1. **A Layout Element** — *Sidebar Layout: Right Sidebar*. Display rules: *Location: Page → All Pages*. URL path: `/docs/`. This turns the sidebar on
2. **The sidebar's content** — either widgets in the *Right Sidebar* widget area, or a Block Element of the *Right Sidebar* type with the same display rules and URL path. The Block Element replaces the widgets wherever it shows, but doesn't turn the sidebar on by itself

A new page under `/docs/` gets the sidebar as soon as it's published. Use *Location: Entire Site* instead of *Page* if the section also holds posts or a custom post type.

### A full hero on page one, a compact one after

The original use case: page one of each category gets a large hero with an introduction written for search engines, and later pages get a slim title bar.

1. **Element A** — a Block Element with the *Page Hero* type. Display rules: *Post Category Archive → All Categories*. Paging: **Only show on page one**
2. **Element B** — the compact version, with the same display rules. Paging: **Never show on page one**

Exactly one of them shows on every page of every category.

### A featured layout on page one only

Pair a Layout Element that sets the page-one design (no sidebar, full-width content) set to **Only show on page one** with a second Layout Element for the standard design set to **Never show on page one**.

### Something on a few specific pages

A Hook Element that drops a promotional block into `generate_before_main_content`, shown on pages 2–4 only: **Only show on pages…** with `2-4`.

## GenerateBlocks Query Loops

A Query Loop block with **Inherit query from template** turned on paginates the archive itself (`/category/news/page/2/`), and paging conditions follow it exactly.

A Query Loop running its **own** query keeps its page number in the address instead (`?query-abc123-page=2`). The plugin doesn't read that, so the archive still counts as page one however far through that loop a visitor goes. Use *Inherit query from template* when an Element needs to follow the loop's pages.

## Troubleshooting

**The Element isn't showing anywhere.** Set paging to *Pass through*, empty the URL path field, and check it appears. If it still doesn't, the display rules are the cause, not the page conditions.

**It shows on page one of an archive but not on a page or post I expected.** Ordinary pages count as page one. If the Element is set to *Never show on page one*, that's why — narrow the display rules instead.

**The Page numbers field is empty after saving.** Nothing in it could be read. Use digits, commas and dashes only — see [the page list](#the-page-list).

**It shows on a section's top page but not the pages below it.** The display rules are too narrow. A Location naming one page covers that page only, whatever the URL path says; set it to *Page → All Pages* and let the URL path pick out the section.

**A URL path doesn't match a page I expected.** Check the page's address in the browser, and remember the path is relative to the site address. If WordPress is installed in a subdirectory (`https://example.com/blog/`), type `/docs/` for `https://example.com/blog/docs/`, not `/blog/docs/`.

**There's no Page Conditions box.** It appears only on the Element edit screen. Check the plugin is active and the GeneratePress Elements module is switched on. Before version 2.1.0 it was called *Paging Condition*.
