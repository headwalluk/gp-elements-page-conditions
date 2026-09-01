<?php
/**
 * Plugin core.
 *
 * @package HeadwallPageConditions
 */

namespace Headwall_Page_Conditions;

defined( 'ABSPATH' ) || die();

/**
 * The plugin's core functionality.
 */
class Plugin {

	/**
	 * The Element edit screen meta box.
	 *
	 * @var Meta_Box
	 */
	private Meta_Box $meta_box;

	/**
	 * The display-time paging condition.
	 *
	 * @var Paging_Condition
	 */
	private Paging_Condition $paging_condition;

	/**
	 * Admin-area hook handlers.
	 *
	 * @var Admin_Hooks
	 */
	private Admin_Hooks $admin_hooks;

	/**
	 * Set up the plugin's handlers. This is run before WP has initialised.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->meta_box         = new Meta_Box();
		$this->paging_condition = new Paging_Condition();
		$this->admin_hooks      = new Admin_Hooks();

		add_action( 'add_meta_boxes_' . ELEMENTS_POST_TYPE, array( $this->meta_box, 'register' ) );
		add_action( 'save_post_' . ELEMENTS_POST_TYPE, array( $this->meta_box, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this->admin_hooks, 'enqueue_scripts' ), 10, 1 );
		add_action( 'admin_notices', array( $this, 'maybe_show_dependency_notice' ) );

		add_filter(
			ELEMENT_DISPLAY_FILTER,
			array( $this->paging_condition, 'filter_element_display' ),
			ELEMENT_DISPLAY_PRIORITY,
			2
		);
	}

	/**
	 * Warn if GeneratePress Premium is not providing the Elements post type.
	 *
	 * Without it this plugin is inert rather than broken, so this is a notice
	 * rather than a hard bail.
	 *
	 * @return void
	 */
	public function maybe_show_dependency_notice(): void {
		// Only bother someone who could act on it.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( ! post_type_exists( ELEMENTS_POST_TYPE ) ) {
			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				esc_html__(
					'GP Elements Page Conditions needs the Elements module of GeneratePress Premium, which does not appear to be active. The plugin is doing nothing until it is.',
					'gp-elements-page-conditions'
				)
			);
		}
	}
}
