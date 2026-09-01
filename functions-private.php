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
 * Order is the order they appear in the meta box.
 *
 * @return array<string, string> Condition value => human readable label.
 */
function get_condition_labels(): array {
	return array(
		CONDITION_DEFAULT        => __( 'Pass through', 'gp-elements-page-conditions' ),
		CONDITION_ONLY_PAGE_ONE  => __( 'Only show on page one', 'gp-elements-page-conditions' ),
		CONDITION_NEVER_PAGE_ONE => __( 'Never show on page one', 'gp-elements-page-conditions' ),
		CONDITION_ONLY_PAGES     => __( 'Only show on pages…', 'gp-elements-page-conditions' ),
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
