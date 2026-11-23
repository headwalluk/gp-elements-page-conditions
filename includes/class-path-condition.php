<?php
/**
 * Element URL path condition.
 *
 * @package HeadwallPageConditions
 */

namespace Headwall_Page_Conditions;

defined( 'ABSPATH' ) || die();

/**
 * Applies an Element's stored URL path prefixes at display time.
 */
class Path_Condition {

	/**
	 * Hide an Element GeneratePress would show, when the URL path matches none of its prefixes.
	 *
	 * @param mixed $is_displayed Whether GeneratePress intends to display the Element.
	 * @param mixed $element_id   The Element post ID.
	 *
	 * @return mixed Whether the Element should be displayed, or $is_displayed
	 *               unchanged when there is nothing to decide.
	 */
	public function filter_element_display( mixed $is_displayed, mixed $element_id ): mixed {
		// Short-circuit before reading post meta, as Paging_Condition does.
		if ( ! $is_displayed || ! is_numeric( $element_id ) || ! is_front_end_page_view() ) {
			return $is_displayed;
		}

		$stored_list = get_post_meta( (int) $element_id, META_PATH_PREFIXES, true );

		// An empty or unusable list passes through rather than hiding the Element everywhere.
		if ( is_string( $stored_list ) && '' !== Path_List::sanitise( $stored_list ) ) {
			$is_displayed = Path_List::matches( $this->get_current_path(), $stored_list );
		}

		return $is_displayed;
	}

	/**
	 * The decoded path being viewed, relative to the home URL, without the query string.
	 *
	 * @return string The path, e.g. "docs/installation", or "" for the home page.
	 */
	public function get_current_path(): string {
		global $wp;

		$request_path = '';

		// $wp->request is set by WP::parse_request(), before GeneratePress sets up Elements on `wp`.
		if ( $wp instanceof \WP && is_string( $wp->request ) ) {
			$request_path = rawurldecode( $wp->request );
		}

		return $request_path;
	}
}
