<?php
/**
 * Plugin-scope helper functions.
 *
 * @package HeadwallPageConditions
 */

namespace Headwall_Page_Conditions;

defined( 'ABSPATH' ) || die();

/**
 * The selectable paging conditions, keyed by the value stored in post meta.
 *
 * Order is the order they appear in the meta box. These are short radio button
 * labels, so each carries a translator context: "page" here always means a page
 * of a paginated archive, and "Pass through" is otherwise wide open.
 *
 * @return array<string, string> Condition value => human readable label.
 */
function get_condition_labels(): array {
	return array(
		CONDITION_DEFAULT        => _x( 'Pass through', 'paging condition; apply no page restriction', 'gp-elements-page-conditions' ),
		CONDITION_ONLY_PAGE_ONE  => _x( 'Only show on page one', 'paging condition', 'gp-elements-page-conditions' ),
		CONDITION_NEVER_PAGE_ONE => _x( 'Never show on page one', 'paging condition', 'gp-elements-page-conditions' ),
		CONDITION_ONLY_PAGES     => _x( 'Only show on pages…', 'paging condition; followed by a list of page numbers', 'gp-elements-page-conditions' ),
	);
}

/**
 * Whether a stored value is one of our known paging conditions.
 *
 * @param mixed $condition The value to test.
 *
 * @return bool
 */
function is_known_condition( $condition ): bool {
	return is_string( $condition ) && array_key_exists( $condition, get_condition_labels() );
}

/**
 * Whether the current request is a front-end page view.
 *
 * Admin screens, AJAX handlers and REST requests have no meaningful page
 * number to test an Element against, so the paging condition sits out.
 *
 * @return bool
 */
function is_front_end_page_view(): bool {
	$is_rest_request = defined( 'REST_REQUEST' ) && REST_REQUEST;

	return ! is_admin() && ! wp_doing_ajax() && ! $is_rest_request;
}
