<?php
/**
 * Admin Hooks
 *
 * @package HeadwallPageConditions
 */

namespace Headwall_Page_Conditions;

defined( 'ABSPATH' ) || die();

/**
 * Handle various admin-area hooks.
 */
class Admin_Hooks {

	/**
	 * Enqueue our admin area assets.
	 *
	 * Only the Element edit screen carries our meta box, so nothing else needs
	 * to pay for the CSS or JS.
	 *
	 * @param string $current_page The current admin area page.
	 *
	 * @return void
	 */
	public function enqueue_scripts( string $current_page ): void {
		$is_edit_screen            = in_array( $current_page, array( 'post.php', 'post-new.php' ), true );
		$current_screen            = $is_edit_screen ? get_current_screen() : null;
		$are_admin_assets_required = null !== $current_screen && ELEMENTS_POST_TYPE === $current_screen->post_type;

		if ( $are_admin_assets_required ) {
			wp_enqueue_style(
				HWPC_NAME . '-admin',
				HWPC_ASSETS_URL . 'hwpc-admin.css',
				array(),
				HWPC_VERSION
			);

			wp_enqueue_script(
				HWPC_NAME . '-admin',
				HWPC_ASSETS_URL . 'hwpc-admin.js',
				array(),
				HWPC_VERSION,
				true
			);

			wp_localize_script(
				HWPC_NAME . '-admin',
				'hwpcData',
				array(
					'containerClass'     => CONTAINER_CLASS,
					'onlyPagesCondition' => CONDITION_ONLY_PAGES,
				)
			);
		}
	}
}
