<?php
/**
 * Plugin core.
 *
 * @package HeadwallPageConditions
 */

namespace Headwall_Page_Conditions;

defined( 'ABSPATH' ) || die();

/**
 * The plugin's core: wires every hook and owns the shared instances.
 */
class Plugin {

	/**
	 * The single instance of the plugin.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * The Element edit screen meta box.
	 *
	 * @var Meta_Box|null
	 */
	private ?Meta_Box $meta_box = null;

	/**
	 * The display-time paging condition.
	 *
	 * @var Paging_Condition|null
	 */
	private ?Paging_Condition $paging_condition = null;

	/**
	 * Admin-area hook handlers.
	 *
	 * @var Admin_Hooks|null
	 */
	private ?Admin_Hooks $admin_hooks = null;

	/**
	 * Get the single instance of the plugin.
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the meta box handler, creating it on first use.
	 *
	 * @return Meta_Box
	 */
	public function get_meta_box(): Meta_Box {
		if ( null === $this->meta_box ) {
			$this->meta_box = new Meta_Box();
		}

		return $this->meta_box;
	}

	/**
	 * Get the paging condition, creating it on first use.
	 *
	 * @return Paging_Condition
	 */
	public function get_paging_condition(): Paging_Condition {
		if ( null === $this->paging_condition ) {
			$this->paging_condition = new Paging_Condition();
		}

		return $this->paging_condition;
	}

	/**
	 * Get the admin hook handlers, creating them on first use.
	 *
	 * @return Admin_Hooks
	 */
	public function get_admin_hooks(): Admin_Hooks {
		if ( null === $this->admin_hooks ) {
			$this->admin_hooks = new Admin_Hooks();
		}

		return $this->admin_hooks;
	}

	/**
	 * Register the plugin's hooks. Runs before WordPress has initialised.
	 *
	 * @return void
	 */
	public function run(): void {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this->get_meta_box(), 'register_meta' ) );
		add_action( 'add_meta_boxes_' . ELEMENTS_POST_TYPE, array( $this->get_meta_box(), 'register' ) );
		add_action( 'save_post_' . ELEMENTS_POST_TYPE, array( $this->get_meta_box(), 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this->get_admin_hooks(), 'enqueue_scripts' ), 10, 1 );
		add_action( 'admin_notices', array( $this, 'maybe_show_dependency_notice' ) );

		add_filter(
			ELEMENT_DISPLAY_FILTER,
			array( $this->get_paging_condition(), 'filter_element_display' ),
			ELEMENT_DISPLAY_PRIORITY,
			2
		);
	}

	/**
	 * Load the plugin's bundled translations.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			HWPC_NAME,
			false,
			trailingslashit( dirname( plugin_basename( HWPC_FILE ) ) ) . LANGUAGES_PATH
		);
	}

	/**
	 * Warn if GeneratePress Premium is not providing the Elements post type.
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
				esc_html(
					sprintf(
						/* translators: 1: this plugin's name, 2: the name of the plugin it depends on */
						__( '%1$s needs the Elements module of %2$s, which does not appear to be active. The plugin is doing nothing until it is.', 'gp-elements-page-conditions' ),
						PLUGIN_NAME,
						'GeneratePress Premium'
					)
				)
			);
		}
	}
}
