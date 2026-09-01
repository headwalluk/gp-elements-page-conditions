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
	 * Register the meta box on the Element edit screen.
	 *
	 * @return void
	 */
	public function register(): void {
		add_meta_box(
			META_BOX_ID,
			esc_html_x( 'Paging Condition', 'meta box title; pagination of an archive', 'gp-elements-page-conditions' ),
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
		$current_condition = get_post_meta( $element->ID, META_CONDITION, true );
		$current_pages     = (string) get_post_meta( $element->ID, META_PAGES, true );

		if ( ! is_known_condition( $current_condition ) ) {
			$current_condition = CONDITION_DEFAULT;
		}

		// Template variables: $current_condition, $current_pages.
		include HWPC_ADMIN_TEMPLATES_DIR . 'element-meta-box.php';
	}

	/**
	 * Persist the meta box values.
	 *
	 * @param int $element_id The Element being saved.
	 *
	 * @return void
	 */
	public function save( int $element_id ): void {
		// Security and performance short-circuits, before touching any input.
		if ( ! $this->is_our_save_request( $element_id ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in is_our_save_request().
		$submitted_condition = isset( $_POST[ META_CONDITION ] )
			? sanitize_text_field( wp_unslash( $_POST[ META_CONDITION ] ) )
			: CONDITION_DEFAULT;

		$submitted_pages = isset( $_POST[ META_PAGES ] )
			? sanitize_text_field( wp_unslash( $_POST[ META_PAGES ] ) )
			: '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( ! is_known_condition( $submitted_condition ) ) {
			$submitted_condition = CONDITION_DEFAULT;
		}

		$this->store_meta( $element_id, META_CONDITION, CONDITION_DEFAULT === $submitted_condition ? '' : $submitted_condition );
		$this->store_meta( $element_id, META_PAGES, Page_List::sanitise( $submitted_pages ) );
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

		$is_autosave = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;

		return ! $is_autosave
			&& wp_verify_nonce( $nonce, NONCE_ACTION )
			&& current_user_can( 'edit_post', $element_id );
	}

	/**
	 * Write a meta value, deleting the row outright when the value is empty.
	 *
	 * Keeps Elements using the default free of our meta, so an Element that has
	 * never been given a paging condition looks the same as one that has had it
	 * cleared.
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
