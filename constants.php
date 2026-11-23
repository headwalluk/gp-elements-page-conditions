<?php
/**
 * Plugin-scope constants.
 *
 * @package HeadwallPageConditions
 */

namespace Headwall_Page_Conditions;

defined( 'ABSPATH' ) || die();

// ============================================================================
// Plugin
// ============================================================================

/**
 * The plugin's name, passed into UI strings as a placeholder so it is never translated.
 */
const PLUGIN_NAME = 'GP Elements Page Conditions';

// ============================================================================
// GeneratePress Premium Integration
// ============================================================================

/**
 * The Elements post type registered by GeneratePress Premium.
 */
const ELEMENTS_POST_TYPE = 'gp_elements';

/**
 * GeneratePress Premium filters every Element type (block, layout, hook and
 * hero) through this one filter, so it is all we need to hook.
 */
const ELEMENT_DISPLAY_FILTER = 'generate_element_display';

/**
 * Run after GeneratePress has decided whether to show the Element, so that we
 * only ever take an Element away, never add one back.
 */
const ELEMENT_DISPLAY_PRIORITY = 20;

// ============================================================================
// Post Meta Keys - prefix with META_
// ============================================================================

/**
 * The legacy ACF key strings, kept exactly (docs/developers/how-it-works.md, Stored data).
 */
const META_CONDITION = 'archive_paging_visibility';
const META_PAGES     = 'archive_paging_pages';

/**
 * Prefix for keys added since the ACF replacement. Underscored, so the Custom Fields box hides them.
 */
const META_PREFIX = '_hwpc_';

const META_PATH_PREFIXES = META_PREFIX . 'path_prefixes';

// ============================================================================
// Paging Conditions - prefix with CONDITION_
// ============================================================================

const CONDITION_DEFAULT        = 'default';
const CONDITION_ONLY_PAGE_ONE  = 'only_page_one';
const CONDITION_NEVER_PAGE_ONE = 'never_page_one';
const CONDITION_ONLY_PAGES     = 'only_pages';

// ============================================================================
// Admin
// ============================================================================

const META_BOX_ID     = 'hwpc-paging-condition';
const NONCE_ACTION    = 'hwpc_save_paging_condition';
const NONCE_FIELD     = 'hwpc_paging_condition_nonce';
const PAGES_FIELD_ID  = 'hwpc-pages';
const CONTAINER_CLASS = 'hwpc-paging-condition';

/**
 * Placeholder shown in the page list field. Literal syntax, so not translatable.
 */
const PAGES_FIELD_EXAMPLE = '2-5, 8';

const PATH_FIELD_ID = 'hwpc-path-prefixes';

/**
 * Path examples for the URL path field. Literal syntax, so not translatable.
 */
const PATH_FIELD_EXAMPLE       = '/docs/';
const PATH_FIELD_EXAMPLE_CHILD = '/docs/installation/';

/**
 * Where the plugin's .po/.mo files live, relative to the plugins directory.
 */
const LANGUAGES_PATH = 'languages';

// ============================================================================
// Page List Parsing
// ============================================================================

/**
 * The lowest page number a visitor can ever be on. Used as the implied start of
 * an open range such as "-3".
 */
const FIRST_PAGE_NUMBER = 1;

// ============================================================================
// GitHub Updater
// ============================================================================

const UPDATER_GITHUB_REPO    = 'headwalluk/gp-elements-page-conditions';
const UPDATER_ENABLED_FILTER = 'hwpc_updater_enabled';
