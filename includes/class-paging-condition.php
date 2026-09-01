<?php
/**
 * Element paging condition.
 *
 * @package HeadwallPageConditions
 */

namespace Headwall_Page_Conditions;

defined( 'ABSPATH' ) || die();

/**
 * Applies an Element's stored paging condition at display time.
 */
class Paging_Condition {

	/**
	 * Filter handler for GeneratePress Premium's element display decision.
	 *
	 * Only ever hides an Element that GeneratePress was already going to show;
	 * an Element excluded by its own display rules stays excluded.
	 *
	 * @param bool $is_displayed Whether GeneratePress intends to display the Element.
	 * @param int  $element_id   The Element post ID.
	 *
	 * @return bool Whether the Element should be displayed.
	 */
	public function filter_element_display( bool $is_displayed, int $element_id ): bool {
		// Short-circuit before reading post meta: GeneratePress has already
		// excluded this Element, or this is not a request with a page number.
		if ( ! $is_displayed || ! is_front_end_page_view() ) {
			return $is_displayed;
		}

		// CONDITION_DEFAULT, no condition at all, and any value we no longer
		// recognise all fall through to the display GeneratePress asked for.
		$is_displayed = match ( get_post_meta( $element_id, META_CONDITION, true ) ) {
			CONDITION_ONLY_PAGE_ONE  => $this->is_first_page(),
			CONDITION_NEVER_PAGE_ONE => ! $this->is_first_page(),
			CONDITION_ONLY_PAGES     => $this->matches_stored_page_list( $element_id ),
			default                  => $is_displayed,
		};

		return $is_displayed;
	}

	/**
	 * Whether the visitor is on the first page of whatever they are viewing.
	 *
	 * @return bool
	 */
	public function is_first_page(): bool {
		return FIRST_PAGE_NUMBER === $this->get_current_page_number();
	}

	/**
	 * The page number currently being viewed.
	 *
	 * Covers both paginated queries (`paged`, used by term and post type
	 * archives, the blog index and search) and paginated single posts (`page`,
	 * set by the <!--nextpage--> quicktag). Any unpaginated view counts as page
	 * one.
	 *
	 * @return int The current page number, never below FIRST_PAGE_NUMBER.
	 */
	public function get_current_page_number(): int {
		$page_number = (int) get_query_var( 'paged' );

		if ( $page_number < FIRST_PAGE_NUMBER ) {
			$page_number = (int) get_query_var( 'page' );
		}

		if ( $page_number < FIRST_PAGE_NUMBER ) {
			$page_number = FIRST_PAGE_NUMBER;
		}

		return $page_number;
	}

	/**
	 * Whether the current page satisfies an Element's stored page list.
	 *
	 * A list that is empty, or that holds nothing parseable, passes through
	 * rather than hiding the Element on every page.
	 *
	 * @param int $element_id The Element post ID.
	 *
	 * @return bool
	 */
	private function matches_stored_page_list( int $element_id ): bool {
		$stored_list = (string) get_post_meta( $element_id, META_PAGES, true );
		$is_matched  = true;

		if ( '' !== Page_List::sanitise( $stored_list ) ) {
			$is_matched = Page_List::matches( $this->get_current_page_number(), $stored_list );
		}

		return $is_matched;
	}
}
