# GP Elements Page Conditions

[![Version](https://img.shields.io/github/v/release/headwalluk/gp-elements-page-conditions?label=version&color=blue)](https://github.com/headwalluk/gp-elements-page-conditions/releases/latest)
[![GeneratePress Premium](https://img.shields.io/badge/GeneratePress%20Premium-2.0+-1e3a8a.svg)](https://generatepress.com/premium/)
[![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)](https://www.php.net/)
[![WordPress](https://img.shields.io/badge/WordPress-6.0+-21759B.svg)](https://wordpress.org/)
[![License](https://img.shields.io/badge/license-GPL--2.0+-green.svg)](LICENSE)
[![Coding Standards](https://img.shields.io/badge/WordPress-Coding%20Standards-blue.svg)](https://github.com/WordPress/WordPress-Coding-Standards)

Adds a paging condition to GeneratePress Premium Elements.

GeneratePress Elements can target an archive, but not a particular *page* of it. This plugin adds a **Paging Condition** box to every Element, so page one of a category can carry a large hero with SEO copy and a featured-post layout, while pages two onwards get a compact hero and a plain list.

## What it does

- Four conditions per Element: *Pass through* (the default), *Only show on page one*, *Never show on page one*, and *Only show on pages…* with a list such as `2-5, 8`
- Works with every Element type — Block, Layout, Hook and Hero
- Applies wherever WordPress paginates: category, tag and custom post type archives, the blog index, search results and multi-page posts
- Only ever hides an Element that GeneratePress was already going to show; never reveals one its display rules exclude
- Updates itself from GitHub Releases through the normal WordPress update screens
- Translated into German, Greek, British English, Spanish, French, Italian, Dutch and Polish

## Documentation

- [Getting started](docs/getting-started.md) — requirements, installing, updates, and moving over from the old ACF setup
- [Using paging conditions](docs/using-paging-conditions.md) — for site builders: each condition, the page list format, and common layouts
- [Developer guide](docs/developers.md) — for developers and contributors: how it works, stored data, testing and releasing
- [Changelog](CHANGELOG.md)
