<?php
/**
 * Paging condition meta box on the GeneratePress Element edit screen.
 *
 * Template variables:
 *   string $current_condition The Element's stored condition, defaulted.
 *   string $current_pages     The Element's stored page list.
 *
 * @package HeadwallPageConditions
 */

namespace Headwall_Page_Conditions;

defined( 'ABSPATH' ) || die();

wp_nonce_field( NONCE_ACTION, NONCE_FIELD );

printf( '<div class="%s">', esc_attr( CONTAINER_CLASS ) );

printf(
	'<p class="hwpc-intro">%s</p>',
	esc_html__(
		'Applies on top of the display rules above, wherever pagination is in play: term and post type archives, the blog index, search results and multi-page posts.',
		'gp-elements-page-conditions'
	)
);

foreach ( get_condition_labels() as $hwpc_condition_value => $hwpc_condition_label ) {
	printf(
		'<label class="hwpc-choice"><input type="radio" name="%s" value="%s"%s /><span>%s</span></label>',
		esc_attr( META_CONDITION ),
		esc_attr( $hwpc_condition_value ),
		checked( $hwpc_condition_value, $current_condition, false ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- checked() returns a fixed literal.
		esc_html( $hwpc_condition_label )
	);
}

printf(
	'<p class="hwpc-pages"%s>',
	CONDITION_ONLY_PAGES === $current_condition ? '' : ' hidden'
);

printf(
	'<label for="%s">%s</label>',
	esc_attr( PAGES_FIELD_ID ),
	esc_html__( 'Page numbers', 'gp-elements-page-conditions' )
);

printf(
	'<input type="text" class="regular-text" id="%s" name="%s" value="%s" placeholder="%s" />',
	esc_attr( PAGES_FIELD_ID ),
	esc_attr( META_PAGES ),
	esc_attr( $current_pages ),
	esc_attr__( '2-5, 8', 'gp-elements-page-conditions' )
);

printf(
	'<span class="description">%s</span>',
	esc_html__(
		'Comma separated. Accepts single pages (3), ranges (2-5) and open ranges (4- for page four onwards, -3 for up to page three). Leaving this empty passes through.',
		'gp-elements-page-conditions'
	)
);

echo '</p>';
echo '</div>';
