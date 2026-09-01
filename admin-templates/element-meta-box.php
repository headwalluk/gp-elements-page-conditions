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
	esc_html_x( 'Page numbers', 'field label; pages of a paginated archive', 'gp-elements-page-conditions' )
);

// The placeholder is a format example, not prose. Page_List only parses ASCII
// digits separated by commas and hyphens, so it must not be translated.
printf(
	'<input type="text" class="regular-text" id="%s" name="%s" value="%s" placeholder="%s" />',
	esc_attr( PAGES_FIELD_ID ),
	esc_attr( META_PAGES ),
	esc_attr( $current_pages ),
	esc_attr( PAGES_FIELD_EXAMPLE )
);

// The four format examples are syntax, not prose. Passing them as placeholders
// keeps them out of the .po files: DeepL renders a range as "2–5" with an en
// dash in German and Polish, which would document a format we do not accept.
printf(
	'<span class="description">%s</span>',
	esc_html(
		sprintf(
			/* translators: 1: single page example, 2: closed range example, 3: open range example, 4: open range example. All four are literal syntax and must not be translated. */
			__( 'Comma separated. Accepts single pages (%1$s), ranges (%2$s) and open ranges (%3$s for page four onwards, %4$s for up to page three). Leaving this empty passes through.', 'gp-elements-page-conditions' ),
			'3',
			'2-5',
			'4-',
			'-3'
		)
	)
);

echo '</p>';
echo '</div>';
