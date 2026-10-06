<?php
/**
 * Plugin Name:       GP Elements Page Conditions
 * Plugin URI:        https://github.com/headwalluk/gp-elements-page-conditions
 * Description:       Adds a paging condition to GeneratePress Elements, so an Element can be shown only on page one of a paginated view, hidden from page one, or limited to specific page numbers.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Paul Faulkner
 * Author URI:        https://headwall-hosting.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gp-elements-page-conditions
 * Domain Path:       /languages
 *
 * @package HeadwallPageConditions
 */

defined( 'ABSPATH' ) || die();

const HWPC_NAME    = 'gp-elements-page-conditions';
const HWPC_VERSION = '1.1.0';

define( 'HWPC_FILE', __FILE__ );
define( 'HWPC_BASENAME', plugin_basename( __FILE__ ) );
define( 'HWPC_DIR', plugin_dir_path( __FILE__ ) );
define( 'HWPC_URL', plugin_dir_url( __FILE__ ) );
define( 'HWPC_ADMIN_TEMPLATES_DIR', trailingslashit( HWPC_DIR . 'admin-templates' ) );
define( 'HWPC_ASSETS_URL', trailingslashit( HWPC_URL . 'assets' ) );

// Load constants and helper functions.
require_once HWPC_DIR . 'constants.php';
require_once HWPC_DIR . 'functions-private.php';

// Load plugin classes.
require_once HWPC_DIR . 'includes/class-page-list.php';
require_once HWPC_DIR . 'includes/class-paging-condition.php';
require_once HWPC_DIR . 'includes/class-meta-box.php';
require_once HWPC_DIR . 'includes/class-admin-hooks.php';

require_once HWPC_DIR . 'includes/class-plugin.php';

// GitHub updates are only checked from admin, cron and WP-CLI requests.
if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once HWPC_DIR . 'includes/class-github-updater.php';
	new Headwall_Page_Conditions\Github_Updater();
}

/**
 * Launch the plugin core.
 *
 * @return void
 */
function hwpc_plugin_run(): void {
	global $hwpc_plugin;

	$hwpc_plugin = new Headwall_Page_Conditions\Plugin();
	$hwpc_plugin->run();
}
hwpc_plugin_run();
