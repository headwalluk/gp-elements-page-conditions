<?php
/**
 * Element paging condition meta box.
 *
 * @package HeadwallPageConditions
 */

namespace Headwall_Page_Conditions;

defined( 'ABSPATH' ) || die();

/**
 * Registers, renders and saves the paging condition meta box on the Element
 * edit screen.
 */
class Meta_Box {

	/**
	 * Register the paging meta, so every write path runs through the sanitisers.
	 *
	 * @return void
	 */
	public function register_meta(): void {
		register_post_meta(
			ELEMENTS_POST_TYPE,
			META_CONDITION,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => array( $this, 'sanitise_condition' ),
				'auth_callback'     => array( $this, 'can_edit_meta' ),
			)
		);

		register_post_meta(
			ELEMENTS_POST_TYPE,
			META_PAGES,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => array( $this, 'sanitise_pages' ),
				'auth_callback'     => array( $this, 'can_edit_meta' ),
			)
		);
	}

	/**
	 * Sanitise a stored paging condition, reducing anything unknown to an empty string.
	 *
	 * @param mixed $condition The value being written.
	 *
	 * @return string A known condition, or an empty string.
	 */
	public function sanitise_condition( mixed $condition ): string {
		$sanitised = '';

		if ( is_known_condition( $condition ) && CONDITION_DEFAULT !== $condition ) {
			$sanitised = $condition;
		}

		return $sanitised;
	}

	/**
	 * Sanitise a stored page list, dropping anything unparseable.
	 *
	 * @param mixed $pages The value being written.
	 *
	 * @return string The valid segments, comma separated.
	 */
	public function sanitise_pages( mixed $pages ): string {
		return is_string( $pages ) ? Page_List::sanitise( $pages ) : '';
	}

	/**
	 * Whether the user may edit the paging meta on this Element.
	 *
	 * @param mixed $is_allowed Whether core would allow the edit.
	 * @param mixed $meta_key   The meta key being edited.
	 * @param mixed $element_id The Element post ID.
	 * @param mixed $user_id    The user making the edit.
	 *
	 * @return bool
	 */
	public function can_edit_meta( mixed $is_allowed, mixed $meta_key, mixed $element_id, mixed $user_id ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed -- Required by the auth_callback signature.
		return is_numeric( $element_id )
			&& is_numeric( $user_id )
			&& user_can( (int) $user_id, 'edit_post', (int) $element_id );
	}

	/**
	 * Register the meta box on the Element edit screen.
	 *
	 * @return void
	 */
	public function register(): void {
		add_meta_box(
			META_BOX_ID,
			esc_html_x( 'Page Conditions', 'meta box title; extra rules for which pages an Element shows on', 'gp-elements-page-conditions' ),
			array( $this, 'render' ),
			ELEMENTS_POST_TYPE,
			'normal',
			'default',
			array( '__block_editor_compatible_meta_box' => true )
		);
	}

	/**
	 * Render the meta box.
	 *
	 * @param \WP_Post $element The Element being edited.
	 *
	 * @return void
	 */
	public function render( \WP_Post $element ): void {
		$current_condition   = get_post_meta( $element->ID, META_CONDITION, true );
		$current_pages       = (string) get_post_meta( $element->ID, META_PAGES, true );
		$stored_prefixes     = get_post_meta( $element->ID, META_PATH_PREFIXES, true );
		$current_prefixes    = is_string( $stored_prefixes ) ? $stored_prefixes : '';
		$is_plain_permalinks = '' === (string) get_option( 'permalink_structure' );

		if ( ! is_known_condition( $current_condition ) ) {
			$current_condition = CONDITION_DEFAULT;
		}

		// The template reads these values by name; its docblock lists them.
		include HWPC_ADMIN_TEMPLATES_DIR . 'element-meta-box.php';
	}

	/**
	 * Persist the meta box values.
	 *
	 * @param mixed $element_id The Element being saved.
	 *
	 * @return void
	 */
	public function save( mixed $element_id ): void {
		// Security and performance short-circuits, before touching any input.
		if ( ! is_numeric( $element_id ) || ! $this->is_our_save_request( (int) $element_id ) ) {
			return;
		}

		$element_id = (int) $element_id;

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in is_our_save_request().
		$submitted_condition = isset( $_POST[ META_CONDITION ] )
			? sanitize_text_field( wp_unslash( $_POST[ META_CONDITION ] ) )
			: CONDITION_DEFAULT;

		$submitted_pages = isset( $_POST[ META_PAGES ] )
			? sanitize_text_field( wp_unslash( $_POST[ META_PAGES ] ) )
			: '';

		// Decoded first: sanitize_text_field() strips percent-encoded octets, which would mangle non-ASCII slugs.
		$submitted_prefixes = isset( $_POST[ META_PATH_PREFIXES ] )
			? sanitize_text_field( rawurldecode( wp_unslash( $_POST[ META_PATH_PREFIXES ] ) ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitize_text_field() runs on the decoded value, then Path_List::sanitise().
			: '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( ! is_known_condition( $submitted_condition ) ) {
			$submitted_condition = CONDITION_DEFAULT;
		}

		$this->store_meta( $element_id, META_CONDITION, CONDITION_DEFAULT === $submitted_condition ? '' : $submitted_condition );
		$this->store_meta( $element_id, META_PAGES, Page_List::sanitise( $submitted_pages ) );
		$this->store_meta( $element_id, META_PATH_PREFIXES, Path_List::sanitise( $submitted_prefixes, $this->get_home_path() ) );
	}

	/**
	 * The path of the site's home URL, "/" unless WordPress is in a subdirectory.
	 *
	 * @return string
	 */
	private function get_home_path(): string {
		$home_path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );

		return is_string( $home_path ) ? $home_path : '/';
	}

	/**
	 * Whether this save request carries our meta box and may write to it.
	 *
	 * @param int $element_id The Element being saved.
	 *
	 * @return bool
	 */
	private function is_our_save_request( int $element_id ): bool {
		$nonce = isset( $_POST[ NONCE_FIELD ] )
			? sanitize_text_field( wp_unslash( $_POST[ NONCE_FIELD ] ) )
			: '';

		$is_autosave = ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			|| false !== wp_is_post_autosave( $element_id )
			|| false !== wp_is_post_revision( $element_id );

		return ! $is_autosave
			&& wp_verify_nonce( $nonce, NONCE_ACTION )
			&& current_user_can( 'edit_post', $element_id );
	}

	/**
	 * Write a meta value, deleting the row outright when the value is empty.
	 *
	 * @param int    $element_id The Element post ID.
	 * @param string $meta_key   The meta key to write.
	 * @param string $meta_value The value to store, or an empty string to delete.
	 *
	 * @return void
	 */
	private function store_meta( int $element_id, string $meta_key, string $meta_value ): void {
		if ( '' === $meta_value ) {
			delete_post_meta( $element_id, $meta_key );
		} else {
			update_post_meta( $element_id, $meta_key, $meta_value );
		}
	}
}
