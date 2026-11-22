<?php
/**
 * Uninstaller.
 *
 * Runs when the plugin is deleted from the Plugins screen, not on deactivation.
 * Deletes only the updater's cache; the paging meta is legacy data and is kept
 * (docs/developers/how-it-works.md, Stored data).
 *
 * @package HeadwallPageConditions
 */

namespace Headwall_Page_Conditions;

// Bail out if not invoked by WordPress's uninstall handler.
defined( 'WP_UNINSTALL_PLUGIN' ) || die();

require_once __DIR__ . '/includes/class-github-updater.php';

Github_Updater::delete_cache( basename( __DIR__ ) );
