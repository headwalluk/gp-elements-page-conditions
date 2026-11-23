# GP Elements Page Conditions

[![Version](https://img.shields.io/github/v/release/headwalluk/gp-elements-page-conditions?label=version&color=blue)](https://github.com/headwalluk/gp-elements-page-conditions/releases/latest)
[![PHP](https://img.shields.io/badge/PHP-8.2+-purple.svg)](https://www.php.net/supported-versions.php)
[![WordPress](https://img.shields.io/badge/WordPress-6.9+-21759B.svg)](https://wordpress.org/download/releases/)
[![GeneratePress Premium](https://img.shields.io/badge/GeneratePress%20Premium-2.0+-1e3a8a.svg)](https://generatepress.com/premium/)
[![License](https://img.shields.io/badge/license-GPL--2.0+-green.svg)](LICENSE)

GeneratePress Premium Elements can target an archive, but not a particular *page* of it, and they can target a page, but not a whole section of the site. This plugin adds a **Page Conditions** box to every Element with two extra conditions:

- **Paging**, so page one of a category can carry a large hero with SEO copy and a featured-post layout, while pages two onwards get a compact hero and a plain list
- **URL path**, so an Element can be limited to every page under `/docs/`, including ones added later, without listing each page in the display rules

It is for anyone building a GeneratePress site who needs that finer control. It works with every Element type, and only ever hides an Element that GeneratePress was already going to show.

It is an independent add-on, not made by the GeneratePress team.

## Documentation

**Site owners**

- [Getting started](docs/getting-started.md) — requirements, installing, updates, and moving over from the old ACF setup

**Site builders**

- [Using page conditions](docs/using-page-conditions.md) — the paging and URL path conditions, and common layouts

**Developers**

- [How it works](docs/developers/how-it-works.md) — the display decision, stored data, testing and releasing
- [Hooks and filters](docs/developers/hooks-and-filters.md) — the public extension surface
- [Changelog](CHANGELOG.md)

## Licence

GPLv2 or later. See [LICENSE](LICENSE).
