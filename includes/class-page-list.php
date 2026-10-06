<?php
/**
 * Page list parsing.
 *
 * @package HeadwallPageConditions
 */

namespace Headwall_Page_Conditions;

defined( 'ABSPATH' ) || die();

/**
 * Parses the comma separated page list used by the "Only show on pages…"
 * condition.
 *
 * Each comma separated segment is one of:
 *
 *   3    a single page
 *   2-5  a closed range
 *   4-   an open range, page four onwards
 *   -3   an open range, up to and including page three
 *
 * Anything else, including a reversed range such as "5-2", is discarded.
 *
 * Typographic dashes are folded to an ASCII hyphen before parsing. German and
 * Polish set ranges with an en dash, so the translated help text for this field
 * says to type "2–5"; word processors and browsers autocorrect hyphens the same
 * way. Without the fold, a segment copied from any of those is silently dropped.
 */
class Page_List {

	/**
	 * Normalise a user-entered page list, dropping anything unparseable.
	 *
	 * Used on save so that only meaningful segments are ever stored, and at
	 * display time to tell a list that means nothing from one that is simply
	 * empty.
	 *
	 * @param string $raw_list Raw page list, e.g. "2-5, 8".
	 *
	 * @return string The valid segments, comma separated.
	 */
	public static function sanitise( string $raw_list ): string {
		$valid_segments = array();

		foreach ( self::split( $raw_list ) as $segment ) {
			if ( null !== self::parse_segment( $segment ) ) {
				$valid_segments[] = $segment;
			}
		}

		return implode( ', ', $valid_segments );
	}

	/**
	 * Whether a page number falls inside a page list.
	 *
	 * @param int    $page_number The page being viewed.
	 * @param string $raw_list    Raw page list, e.g. "2-5, 8".
	 *
	 * @return bool True if any segment of the list covers the page number.
	 */
	public static function matches( int $page_number, string $raw_list ): bool {
		$is_matched = false;

		foreach ( self::split( $raw_list ) as $segment ) {
			$range = self::parse_segment( $segment );

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
	 * @param string $raw_list Raw page list.
	 *
	 * @return string[] The segments, in the order they were written.
	 */
	private static function split( string $raw_list ): array {
		$segments = array();
		$ascii    = self::fold_dashes( $raw_list );

		if ( '' !== trim( $ascii ) ) {
			$segments = array_map( 'trim', explode( ',', $ascii ) );
			$segments = array_values( array_filter( $segments, 'strlen' ) );
		}

		return $segments;
	}

	/**
	 * Fold the dash characters a visitor might paste in down to ASCII.
	 *
	 * @param string $raw_list Raw page list.
	 *
	 * @return string The list with every dash variant as a hyphen-minus.
	 */
	private static function fold_dashes( string $raw_list ): string {
		$dash_variants = array(
			"\u{2010}", // Hyphen.
			"\u{2011}", // Non-breaking hyphen.
			"\u{2012}", // Figure dash.
			"\u{2013}", // En dash.
			"\u{2014}", // Em dash.
			"\u{2015}", // Horizontal bar.
			"\u{2212}", // Minus sign.
		);

		return str_replace( $dash_variants, '-', $raw_list );
	}

	/**
	 * Turn one page list segment into a first/last page range.
	 *
	 * @param string $segment A single segment of a page list.
	 *
	 * @return array{first: int, last: int}|null The range, or null if the
	 *                                           segment cannot be parsed.
	 */
	private static function parse_segment( string $segment ): ?array {
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
				'first' => FIRST_PAGE_NUMBER,
				'last'  => (int) $matches[1],
			);
		} else {
			// Unparseable segment; the range stays null.
		}

		$is_usable_range = null !== $range
			&& $range['first'] >= FIRST_PAGE_NUMBER
			&& $range['first'] <= $range['last'];

		if ( ! $is_usable_range ) {
			$range = null;
		}

		return $range;
	}
}
