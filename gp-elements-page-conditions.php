<?php
/**
 * Plugin Name:       GP Elements Page Conditions
 * Plugin URI:        https://headwall-hosting.com/
 * Description:       Adds a paging condition to GeneratePress Elements, so an Element can be shown only on page one of a paginated view, hidden from page one, or limited to specific page numbers.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Headwall Hosting
 * Author URI:        https://headwall-hosting.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gp-elements-page-conditions
 *
 * @package gp-elements-page-conditions
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds a "Paging" display condition to GeneratePress Elements.
 *
 * GeneratePress Premium runs every Element type (block, layout, hook and hero)
 * through the `generate_element_display` filter, so a single filter is enough
 * to cover all of them.
 */
class Headwall_GP_Elements_Page_Conditions {

	/**
	 * The GeneratePress Elements post type.
	 */
	const POST_TYPE = 'gp_elements';

	/**
	 * Meta key holding the chosen paging condition.
	 *
	 * Deliberately matches the key used by the ACF implementation this plugin
	 * replaces, so existing Elements keep working without a migration.
	 */
	const META_VISIBILITY = 'archive_paging_visibility';

	/**
	 * Meta key holding the page list used by the `only_pages` condition.
	 */
	const META_PAGES = 'archive_paging_pages';

	/**
	 * Nonce action and field name for the meta box.
	 */
	const NONCE_ACTION = 'headwall_gpepc_save';
	const NONCE_NAME   = 'headwall_gpepc_nonce';

	/**
	 * Register the plugin's hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'add_meta_boxes_' . self::POST_TYPE, array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_meta_box' ) );
		add_filter( 'generate_element_display', array( __CLASS__, 'filter_element_display' ), 20, 2 );
		add_action( 'admin_notices', array( __CLASS__, 'render_dependency_notice' ) );
	}

	/**
	 * Warn if GeneratePress Premium's Elements module is not providing the post type.
	 *
	 * Without it this plugin is inert rather than broken, so this is a notice
	 * rather than a hard bail.
	 *
	 * @return void
	 */
	public static function render_dependency_notice() {
		if ( post_type_exists( self::POST_TYPE ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html__( 'GP Elements Page Conditions needs the Elements module of GeneratePress Premium, which does not appear to be active. The plugin is doing nothing until it is.', 'gp-elements-page-conditions' )
		);
	}

	/**
	 * The available paging conditions, keyed by stored meta value.
	 *
	 * @return array<string, string>
	 */
	public static function get_conditions() {
		return array(
			'default'        => __( 'Pass through', 'gp-elements-page-conditions' ),
			'only_page_one'  => __( 'Only show on page one', 'gp-elements-page-conditions' ),
			'never_page_one' => __( 'Never show on page one', 'gp-elements-page-conditions' ),
			'only_pages'     => __( 'Only show on pages…', 'gp-elements-page-conditions' ),
		);
	}

	/**
	 * Register the meta box on the Element edit screen.
	 *
	 * @return void
	 */
	public static function add_meta_box() {
		add_meta_box(
			'headwall-gpepc',
			esc_html__( 'Paging Condition', 'gp-elements-page-conditions' ),
			array( __CLASS__, 'render_meta_box' ),
			self::POST_TYPE,
			'normal',
			'default',
			array( '__block_editor_compatible_meta_box' => true )
		);
	}

	/**
	 * Render the meta box.
	 *
	 * @param WP_Post $post The Element being edited.
	 *
	 * @return void
	 */
	public static function render_meta_box( $post ) {
		$current_condition = get_post_meta( $post->ID, self::META_VISIBILITY, true );
		$current_pages     = get_post_meta( $post->ID, self::META_PAGES, true );

		if ( ! array_key_exists( $current_condition, self::get_conditions() ) ) {
			$current_condition = 'default';
		}

		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		?>
		<div class="headwall-gpepc">
			<p class="headwall-gpepc__intro">
				<?php esc_html_e( 'Applies on top of the display rules above, wherever pagination is in play: term and post type archives, the blog index, search results and multi-page posts.', 'gp-elements-page-conditions' ); ?>
			</p>

			<?php foreach ( self::get_conditions() as $condition_value => $condition_label ) : ?>
				<label class="headwall-gpepc__choice">
					<input
						type="radio"
						name="<?php echo esc_attr( self::META_VISIBILITY ); ?>"
						value="<?php echo esc_attr( $condition_value ); ?>"
						<?php checked( $condition_value, $current_condition ); ?>
					/>
					<span><?php echo esc_html( $condition_label ); ?></span>
				</label>
			<?php endforeach; ?>

			<p class="headwall-gpepc__pages" <?php echo 'only_pages' === $current_condition ? '' : 'hidden'; ?>>
				<label for="headwall-gpepc-pages">
					<?php esc_html_e( 'Page numbers', 'gp-elements-page-conditions' ); ?>
				</label>
				<input
					type="text"
					class="regular-text"
					id="headwall-gpepc-pages"
					name="<?php echo esc_attr( self::META_PAGES ); ?>"
					value="<?php echo esc_attr( $current_pages ); ?>"
					placeholder="2-5, 8"
				/>
				<span class="description">
					<?php esc_html_e( 'Comma separated. Accepts single pages (3), ranges (2-5) and open ranges (4- for page four onwards, -3 for up to page three). Leaving this empty passes through.', 'gp-elements-page-conditions' ); ?>
				</span>
			</p>
		</div>

		<style>
			.headwall-gpepc__intro { margin-top: 0; color: #50575e; }
			.headwall-gpepc__choice { display: block; margin: 0 0 6px; }
			.headwall-gpepc__pages { margin-top: 14px; }
			.headwall-gpepc__pages label { display: block; font-weight: 600; margin-bottom: 4px; }
			.headwall-gpepc__pages .description { display: block; margin-top: 4px; }
		</style>

		<script>
			( function () {
				var container = document.querySelector( '.headwall-gpepc' );

				if ( ! container ) {
					return;
				}

				var pagesRow = container.querySelector( '.headwall-gpepc__pages' );

				container.addEventListener( 'change', function ( event ) {
					if ( 'radio' === event.target.type ) {
						pagesRow.hidden = 'only_pages' !== event.target.value;
					}
				} );
			}() );
		</script>
		<?php
	}

	/**
	 * Persist the meta box values.
	 *
	 * @param int $post_id The Element being saved.
	 *
	 * @return void
	 */
	public static function save_meta_box( $post_id ) {
		$nonce = isset( $_POST[ self::NONCE_NAME ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			// Not our save, or the meta box was not rendered on this request.
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$submitted_condition = isset( $_POST[ self::META_VISIBILITY ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::META_VISIBILITY ] ) ) : 'default';
		$submitted_pages     = isset( $_POST[ self::META_PAGES ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::META_PAGES ] ) ) : '';

		if ( ! array_key_exists( $submitted_condition, self::get_conditions() ) ) {
			$submitted_condition = 'default';
		}

		$sanitised_pages = self::sanitise_page_list( $submitted_pages );

		if ( 'default' === $submitted_condition ) {
			delete_post_meta( $post_id, self::META_VISIBILITY );
		} else {
			update_post_meta( $post_id, self::META_VISIBILITY, $submitted_condition );
		}

		if ( '' === $sanitised_pages ) {
			delete_post_meta( $post_id, self::META_PAGES );
		} else {
			update_post_meta( $post_id, self::META_PAGES, $sanitised_pages );
		}
	}

	/**
	 * Apply the paging condition to an Element.
	 *
	 * @param bool $is_displayed Whether GeneratePress intends to display the Element.
	 * @param int  $element_id   The Element post ID.
	 *
	 * @return bool
	 */
	public static function filter_element_display( $is_displayed, $element_id ) {
		$condition = '';

		if ( ! $is_displayed ) {
			// Already hidden by GeneratePress; nothing to add.
		} elseif ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			// Not a front-end page view, so there is no page number to test.
		} elseif ( empty( ( $condition = get_post_meta( $element_id, self::META_VISIBILITY, true ) ) ) ) {
			// No paging condition set on this Element.
		} elseif ( 'only_page_one' === $condition && self::get_current_page_number() > 1 ) {
			$is_displayed = false;
		} elseif ( 'never_page_one' === $condition && 1 === self::get_current_page_number() ) {
			$is_displayed = false;
		} elseif ( 'only_pages' === $condition ) {
			$page_list = get_post_meta( $element_id, self::META_PAGES, true );

			if ( '' !== self::sanitise_page_list( $page_list ) && ! self::page_list_matches( self::get_current_page_number(), $page_list ) ) {
				$is_displayed = false;
			}
		} else {
			// Pass through.
		}

		return $is_displayed;
	}

	/**
	 * The page number currently being viewed.
	 *
	 * Covers both paginated queries (`paged`, used by archives, the blog index
	 * and search) and paginated single posts (`page`, set by <!--nextpage-->).
	 *
	 * @return int
	 */
	public static function get_current_page_number() {
		$page_number = (int) get_query_var( 'paged' );

		if ( $page_number < 1 ) {
			$page_number = (int) get_query_var( 'page' );
		}

		if ( $page_number < 1 ) {
			$page_number = 1;
		}

		return $page_number;
	}

	/**
	 * Normalise a user-entered page list, dropping anything unparseable.
	 *
	 * @param string $page_list Raw page list, e.g. "2-5, 8".
	 *
	 * @return string
	 */
	public static function sanitise_page_list( $page_list ) {
		$valid_segments = array();

		foreach ( self::split_page_list( $page_list ) as $segment ) {
			if ( null !== self::parse_page_segment( $segment ) ) {
				$valid_segments[] = $segment;
			}
		}

		return implode( ', ', $valid_segments );
	}

	/**
	 * Whether a page number falls inside a page list.
	 *
	 * @param int    $page_number The page being viewed.
	 * @param string $page_list   Raw page list, e.g. "2-5, 8".
	 *
	 * @return bool
	 */
	public static function page_list_matches( $page_number, $page_list ) {
		$is_matched = false;

		foreach ( self::split_page_list( $page_list ) as $segment ) {
			$range = self::parse_page_segment( $segment );

			if ( null !== $range && $page_number >= $range['first'] && $page_number <= $range['last'] ) {
				$is_matched = true;
				break;
			}
		}

		return $is_matched;
	}

	/**
	 * Split a raw page list into trimmed, non-empty segments.
	 *
	 * @param string $page_list Raw page list.
	 *
	 * @return string[]
	 */
	private static function split_page_list( $page_list ) {
		if ( ! is_string( $page_list ) || '' === trim( $page_list ) ) {
			return array();
		}

		$segments = array_map( 'trim', explode( ',', $page_list ) );

		return array_values( array_filter( $segments, 'strlen' ) );
	}

	/**
	 * Turn one page list segment into a first/last page range.
	 *
	 * Accepts "3", "2-5", "4-" (four onwards) and "-3" (up to three). Returns
	 * null for anything else, including reversed ranges.
	 *
	 * @param string $segment A single segment of a page list.
	 *
	 * @return array{first: int, last: int}|null
	 */
	private static function parse_page_segment( $segment ) {
		$range = null;

		if ( preg_match( '/^(\d+)$/', $segment, $matches ) ) {
			$range = array(
				'first' => (int) $matches[1],
				'last'  => (int) $matches[1],
			);
		} elseif ( preg_match( '/^(\d+)\s*-\s*(\d+)$/', $segment, $matches ) ) {
			$range = array(
				'first' => (int) $matches[1],
				'last'  => (int) $matches[2],
			);
		} elseif ( preg_match( '/^(\d+)\s*-$/', $segment, $matches ) ) {
			$range = array(
				'first' => (int) $matches[1],
				'last'  => PHP_INT_MAX,
			);
		} elseif ( preg_match( '/^-\s*(\d+)$/', $segment, $matches ) ) {
			$range = array(
				'first' => 1,
				'last'  => (int) $matches[1],
			);
		} else {
			// Unparseable segment.
		}

		if ( null !== $range && ( $range['first'] < 1 || $range['first'] > $range['last'] ) ) {
			$range = null;
		}

		return $range;
	}
}

Headwall_GP_Elements_Page_Conditions::init();
